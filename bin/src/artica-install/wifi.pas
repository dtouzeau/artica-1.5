unit wifi;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  twifi=class


private
     LOGS:Tlogs;
     ethw:string;
     SYS:TSystem;
     artica_path:string;
     HOSTAPD_BIN_PATH:string;
     wpa_supplicant_bin_path:string;
     procedure CHECK_CONFIG();
     function  wpa_supplicant_bin():string;
     function  WPA_SUPPLIANT_PID():string;
     function  HOSTAPD_PID():string;
     procedure HOSTAPD_CHECK_CONFIG();
public
    WpaSuppliantEnabled:integer;
    WifiAPEnable:integer;
    HostApdEnabled:integer;
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    procedure    WPA_SUPPLIANT_STOP();
    function     STATUS():string;
    function     WPA_SUPPLIANT_VERSION():string;
   procedure     WPA_SUPPLIANT_START();

   procedure     HOSTAPD_START();
   procedure     HOSTAPD_STOP();
   function      HOSTAPD_VERSION():string;
   function      HOSTAPD_BINVER():integer;
   function      hostapd_bin():string;



END;

implementation

constructor twifi.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       ethw:=SYS.WIRELESS_CARD();
       WpaSuppliantEnabled:=0;
       WifiAPEnable:=0;
       HostApdEnabled:=0;

       if not TryStrToInt(SYS.GET_INFO('WpaSuppliantEnabled'),WpaSuppliantEnabled) then WpaSuppliantEnabled:=1;
       if not TryStrToInt(SYS.GET_INFO('WifiAPEnable'),WifiAPEnable) then WifiAPEnable:=0;
       if not TryStrToInt(SYS.GET_INFO('HostApdEnabled'),HostApdEnabled) then HostApdEnabled:=0;



       if length(ethw)=0 then WpaSuppliantEnabled:=0;
       if WifiAPEnable=0 then WpaSuppliantEnabled:=0;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure twifi.free();
begin
    logs.Free;
end;
//##############################################################################
function twifi.wpa_supplicant_bin():string;
var
   path:string;
begin
    if length(wpa_supplicant_bin_path)>0 then exit(wpa_supplicant_bin_path);
    path:=SYS.LOCATE_GENERIC_BIN('wpa_supplicant');
    if FileExists(path) then begin
       wpa_supplicant_bin_path:=path;
       exit(path);
    end;

end;
//##############################################################################
function twifi.hostapd_bin():string;
var
   path:string;
begin
if length(HOSTAPD_BIN_PATH)>0 then exit(HOSTAPD_BIN_PATH);
    path:=SYS.LOCATE_GENERIC_BIN('hostapd');
    if FileExists(path) then exit(path);
end;
//##############################################################################
function twifi.STATUS():string;
var
pidpath:string;
begin

   if not FileExists(wpa_supplicant_bin()) then  begin
      SYS.MONIT_DELETE('APP_WPA_SUPPLIANT');
      exit;
   end;

 pidpath:=logs.FILE_TEMP();
 fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --wifi >'+pidpath +' 2>&1');
 result:=logs.ReadFromFile(pidpath);
 logs.DeleteFile(pidpath);

 if WpaSuppliantEnabled=0 then begin
    SYS.MONIT_DELETE('APP_WPA_SUPPLIANT');
    exit;
end;

SYS.MONIT_CONFIG('APP_WPA_SUPPLIANT','/var/run/wpa_supplicant.'+trim(ethw)+'.pid','wifi');
end;
//##############################################################################
function twifi.WPA_SUPPLIANT_VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
    if Not Fileexists(wpa_supplicant_bin()) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_WPA_SUPPLIANT');
    if length(result)>2 then exit;
    tmpstr:=logs.FILE_TEMP();
    fpsystem(wpa_supplicant_bin()+' -v >'+tmpstr);
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='wpa_supplicant v([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_WPA_SUPPLIANT',result);
l.free;
RegExpr.free;
end;
//##############################################################################
procedure twifi.CHECK_CONFIG();
var
l:Tstringlist;

begin

if not FileExists('/etc/wpa_supplicant.conf') then begin
   l:=Tstringlist.Create;
   l.Add('ctrl_interface=/var/run/wpa_supplicant');
   l.Add('ctrl_interface_group=0');
   l.Add('update_config=1');
   logs.Debuglogs('Starting......: WPA Supplicant writing first config');
   logs.WriteToFile(l.Text,'/etc/wpa_supplicant.conf');
end;


end;

//##############################################################################
function twifi.WPA_SUPPLIANT_PID():string;
var
   pid_path:string;
begin
   pid_path:='/var/run/wpa_supplicant.'+trim(ethw)+'.pid';
   if FileExists(pid_path) then result:=SYS.GET_PID_FROM_PATH(pid_path);
end;
//##############################################################################
function twifi.HOSTAPD_PID():string;
var
   pid_path:string;
begin
   pid_path:='/var/run/hostapd.pid';
   if FileExists(pid_path) then result:=SYS.GET_PID_FROM_PATH(pid_path);
end;
//##############################################################################
procedure twifi.START();
begin
WPA_SUPPLIANT_START();
HOSTAPD_START();
end;
//##############################################################################
procedure twifi.STOP();
begin
WPA_SUPPLIANT_STOP();
HOSTAPD_STOP();
end;
//##############################################################################
procedure twifi.WPA_SUPPLIANT_START();
 var
    count      :integer;
    pid:string;
    bin_path,pid_path:string;
    cmd:string;
begin
   bin_path:=wpa_supplicant_bin();
  if Not Fileexists(bin_path) then begin
       logs.Debuglogs('Starting......: WPA Supplicant is not installed');
       exit;
  end;

   if WpaSuppliantEnabled=0 then begin
       logs.Debuglogs('Starting......: WPA Supplicant is disabled');
       if FIleexists('/etc/wpa_supplicant.conf') then logs.DeleteFile('/etc/wpa_supplicant.conf');
       SYS.MONIT_DELETE('APP_WPA_SUPPLIANT');
       WPA_SUPPLIANT_STOP();
       exit;
   end;

   pid:=WPA_SUPPLIANT_PID();
   if SYS.PROCESS_EXIST(pid) then begin
       logs.Debuglogs('Starting......: WPA Supplicant already running PID '+pid);
       exit;
  end;

   CHECK_CONFIG();
   pid_path:='/var/run/wpa_supplicant.'+trim(ethw)+'.pid';
   logs.Debuglogs('Starting......: WPA Supplicant Network Card:'+ethw);
   logs.Debuglogs('Starting......: WPA Supplicant PID:'+pid_path);
   logs.Debuglogs('Starting......: WPA Supplicant Network starting service...');
   cmd:='/usr/bin/nohup '+ bin_path+' -s -B -P '+pid_path+' -i '+ethw+' -c /etc/wpa_supplicant.conf >/dev/null 2>&1 &';
   fpsystem(cmd);
   pid:=WPA_SUPPLIANT_PID();
   count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>30 then begin
           logs.DebugLogs('Starting......: WPA Supplicant (failed)');
           logs.DebugLogs('Starting......: '+cmd);
           exit;
        end;
        pid:=WPA_SUPPLIANT_PID();
  end;
   pid:=WPA_SUPPLIANT_PID();
  logs.DebugLogs('Starting......: WPA Supplicant success with PID '+pid);
end;
//##############################################################################
procedure twifi.WPA_SUPPLIANT_STOP();
 var
    pid:string;
    count:integer;
begin
count:=0;
  if not FileExists(wpa_supplicant_bin()) then exit;
  pid:=WPA_SUPPLIANT_PID();

  if not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping WPA Supplicant......: Already stopped');
     exit;
  end;


   writeln('Stopping WPA Supplicant......: ' + pid + ' PID');

while SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        fpsystem('/bin/kill '+ pid);
        if count>30 then break;
        pid:=WPA_SUPPLIANT_PID();
end;

   pid:=WPA_SUPPLIANT_PID();


  if SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping WPA Supplicant......: ' + pid + ' PID');
     fpsystem('/bin/kill '+pid);
     sleep(500);
  end else begin
     writeln('Stopping WPA Supplicant......: stopped');
     SYS.MONIT_DELETE('APP_WPA_SUPPLIANT');
  end;
end;
//##############################################################################
procedure twifi.HOSTAPD_CHECK_CONFIG();
begin
ForceDirectories('/etc/hostapd');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.hostapd.php');
end;

//##############################################################################
procedure twifi.HOSTAPD_START();
var
count:integer;
pid,cmd,nohup:string;
begin
if not FileExists(hostapd_bin()) then begin
  logs.Debuglogs('Starting......: Advanced IEEE 802.11 management is not installed');
  exit;
end;

   if HostApdEnabled=0 then begin
       logs.Debuglogs('Starting......: Advanced IEEE 802.11 management is disabled');
       SYS.MONIT_DELETE('APP_HOSTAPD');
       HOSTAPD_STOP();
       exit;
   end;

  pid:=HOSTAPD_PID();
   if SYS.PROCESS_EXIST(pid) then begin
       logs.Debuglogs('Starting......: Advanced IEEE 802.11 management already running PID '+pid);
       exit;
  end;
 count:=0;
 nohup:=SYS.LOCATE_GENERIC_BIN('nohup');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.hostapd.php --build');
cmd:=nohup+' '+hostapd_bin()+' -B -P /var/run/hostapd.pid /etc/hostapd.conf >/dev/null 2>&1';
logs.Debuglogs('HOSTAPD_START:: running "'+cmd+'"');
fpsystem(cmd);
 logs.Debuglogs('HOSTAPD_START:: START LOOP');
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>30 then begin
           logs.DebugLogs('Starting......:  Advanced IEEE 802.11 management (failed)');
           logs.DebugLogs('Starting......: '+cmd);
           exit;
        end;
        pid:=HOSTAPD_PID();
        logs.Debuglogs('HOSTAPD_START:: WAIT ->'+intTostr(count)+' PID "'+pid+'"');
  end;

 pid:=HOSTAPD_PID();
 logs.DebugLogs('Starting......: Advanced IEEE 802.11 management success with PID '+pid);
end;

//##############################################################################
procedure twifi.HOSTAPD_STOP();
 var
    pid:string;
    count:integer;
begin
count:=0;
  if not FileExists(hostapd_bin()) then exit;
  pid:=HOSTAPD_PID();

  if not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Advanced IEEE 802.11.: Already stopped');
     exit;
  end;


   writeln('Stopping Advanced IEEE 802.11.: ' + pid + ' PID');

while SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        fpsystem('/bin/kill '+ pid);
        if count>30 then break;
        pid:=HOSTAPD_PID();
end;

   pid:=HOSTAPD_PID();
  if SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Advanced IEEE 802.11.: ' + pid + ' PID');
     fpsystem('/bin/kill '+pid);
     sleep(500);
  end else begin
     writeln('Stopping Advanced IEEE 802.11.: stopped');
     SYS.MONIT_DELETE('APP_HOSTAPD');
  end;
end;
//##############################################################################
function twifi.HOSTAPD_VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
    if Not Fileexists(hostapd_bin()) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_HOSTAPD');
    if length(result)>2 then exit;
    tmpstr:=logs.FILE_TEMP();
    fpsystem(hostapd_bin()+' -v >'+tmpstr+' 2>&1');
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='hostapd v([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_HOSTAPD',result);
l.free;
RegExpr.free;
end;
//##############################################################################
function twifi.HOSTAPD_BINVER():integer;
var
   ver:string;
begin
 ver:=HOSTAPD_VERSION();
 ver:=AnsiReplaceText(ver,'.','');
 tryStrToInt(ver,result);
end;


end.
