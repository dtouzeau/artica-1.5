unit ntpd;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tntpd=class


private
     LOGS:Tlogs;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     NTPDEnabled:integer;
     NTPDServerEnabled:integer;
     function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
     function get_INFOS(key:string):string;
     function ReadFileIntoString(path:string):string;
     function RUNASUSER():string;

public
    procedure   Free;
    constructor Create;
    procedure   NTPD_START();
    function    DEAMON_BIN_PATH():string;
    function    DEAMON_CONF_PATH():string;
    function    NTPD_PID():string;
    function    INITD_PATH():string;
    function    NTPD_VERSION():string;
    FUNCTION    NTPD_STATUS():string;
    procedure   NTPD_STOP();


END;

implementation

constructor tntpd.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       if not TryStrToInt(SYS.GET_INFO('NTPDEnabled'),NTPDEnabled) then NTPDEnabled:=0;
       if not TryStrToInt(SYS.GET_INFO('NTPDServerEnabled'),NTPDServerEnabled) then NTPDServerEnabled:=0;

       if NTPDEnabled=0 then begin
          if NTPDServerEnabled=1 then NTPDEnabled:=1;
       end;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tntpd.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
function tntpd.DEAMON_BIN_PATH():string;
begin
  if FileExists('/usr/sbin/ntpd') then exit('/usr/sbin/ntpd');
end;
//##############################################################################
function tntpd.DEAMON_CONF_PATH():string;
begin
  if FileExists('/etc/ntp.conf') then exit('/etc/ntp.conf');
end;
//##############################################################################
function tntpd.INITD_PATH():string;
begin
  if FileExists('/etc/init.d/ntp') then exit('/etc/init.d/ntp');

  if FileExists('/etc/init.d/ntpd') then exit('/etc/init.d/ntpd')

end;
//##############################################################################
function tntpd.NTPD_PID():string;
var pid:string;
begin

if FileExists('/var/run/ntpd.pid') then pid:=trim(SYS.GET_PID_FROM_PATH('/var/run/ntpd.pid'));
if length(pid)=0 then begin
   logs.Debuglogs('Unable to stat /var/run/ntpd.pid search bin');
   pid:=SYS.PIDOF(DEAMON_BIN_PATH());
end;

result:=pid;

end;
//##############################################################################
procedure tntpd.NTPD_START();
 var
    count      :integer;
    cmdline    :string;
    logs       :Tlogs;
    datas      :string;
    user       :string;
begin
     count:=0;
     logs:=Tlogs.Create;
     logs.Debuglogs('NTPD_START()');
     if not FileExists(DEAMON_BIN_PATH()) then begin
        logs.Debuglogs('NTPD:: not installed');
        exit;
     end;
     
     
     if not FileExists(DEAMON_CONF_PATH()) then begin
        logs.Debuglogs('NTPD:: not configured');
        NTPDEnabled:=0;
        exit;
     end;
     

     if NTPDEnabled<>1 then begin
        logs.Debuglogs('NTPD:: not enabled');
        NTPD_STOP();
        exit;
     end;
     
 if SYS.PROCESS_EXIST(NTPD_PID()) then begin
        logs.DebugLogs('NTPD:: daemon is already running using PID ' + NTPD_PID() + '...');
        exit;
 end;
 
 
  if FileExists(INITD_PATH()) then begin
     cmdline:=INITD_PATH() + ' start &';
  end else begin
     cmdline:=DEAMON_BIN_PATH()+ ' -c '+DEAMON_CONF_PATH()+' -p /var/run/ntpd.pid';

  end;
        user:=RUNASUSER();
        ForceDirectories('/var/lib/ntp');
        ForceDirectories('/var/log/ntpstats');
        logs.OutputCmd('/bin/chown -R '+user+':'+user+' /var/log/ntpstats');
        logs.OutputCmd('/bin/chown -R '+user+':'+user+' /var/lib/ntp');
        logs.OutputCmd('/bin/chmod 775 /var/log/ntpstats');
        logs.OutputCmd('/bin/chmod 775 /var/lib/ntp');

        if FIleExists('/etc/artica-postfix/settings/Daemons/NTPDConf') then begin
           logs.DebugLogs('Starting......: Replicate configuration file');
           if FileExists('/usr/bin/tr') then begin
                fpsystem('/bin/cat /etc/artica-postfix/settings/Daemons/NTPDConf | /usr/bin/tr "\r" " " > '+DEAMON_CONF_PATH());
           end else begin
               datas:=logs.ReadFromFile('/etc/artica-postfix/settings/Daemons/NTPDConf');
               logs.WriteToFile(datas,DEAMON_CONF_PATH());
           end;
        end;


        logs.DebugLogs('Starting......: Starting NTPD service');
        logs.DebugLogs(cmdline);
        fpsystem(cmdline);


        while not SYS.PROCESS_EXIST(NTPD_PID()) do begin
              sleep(100);
              inc(count);
              if count>100 then begin
                 logs.DebugLogs('Starting......: NTPD daemon... (failed!!!)');
                 logs.Debuglogs('Failed starting NTPD Daemon');
                 exit;
              end;
        end;



     logs.DebugLogs('Starting......: NTPD daemon with new PID ' + NTPD_PID() + '...');


end;
//##############################################################################
function tntpd.RUNASUSER():string;
var
  RegExpr:TRegExpr;
  l:TstringList;
  i:integer;
  tmpstr:string;
begin
tmpstr:=INITD_PATH();
if not FileExists(tmpstr) then exit('root');
l:=Tstringlist.Create;
l.LOadFromFile(tmpstr);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='RUNASUSER=(.+)';
     for i:=0 to l.Count-1 do begin
            if RegExpr.Exec(l.Strings[i]) then begin
               result:=trim(RegExpr.Match[1]);
               break;
            end;
     end;
     RegExpr.free;
     l.free;


end;
//##############################################################################
function tntpd.NTPD_VERSION():string;
var
  RegExpr:TRegExpr;
  l:TstringList;
  i:integer;
  tmpstr:string;
begin

     if not FileExists(DEAMON_BIN_PATH()) then exit;


   result:=SYS.GET_CACHE_VERSION('APP_NTPD');
   if length(result)>2 then exit;

     tmpstr:=logs.FILE_TEMP();
     fpsystem(DEAMON_BIN_PATH()+' -v >'+tmpstr+' 2>&1');


     if not FileExists(tmpstr) then exit;

     l:=TstringList.Create;
     l.LoadFromFile(tmpstr);

     RegExpr:=tRegExpr.Create;
     RegExpr.Expression:='Ver\.\s+([0-9\.a-z]+)';

     for i:=0 to l.Count-1 do begin
            if RegExpr.Exec(l.Strings[i]) then begin
               result:=RegExpr.Match[1];
               break;
            end else begin

            end;
    end;

    l.free;
     RegExpr.free;
     SYS.SET_CACHE_VERSION('APP_APACHE_ARTICA',result);
     DeleteFile(tmpstr);
end;
//##############################################################################
FUNCTION tntpd.NTPD_STATUS():string;
var
   ini:TstringList;
   pid     :string;
begin

     if not FileExists(DEAMON_BIN_PATH()) then exit;
    ini:=TstringList.Create;
    ini.Add('[NTPD]');
      ini.Add('service_name=APP_NTPD');
      ini.Add('service_disabled='+ IntToStr(NTPDEnabled));
      ini.Add('service_cmd=ntpd');
      ini.Add('master_version=' + NTPD_VERSION());

     if NTPDEnabled=0 then begin
         result:=ini.Text;
         ini.free;
         SYS.MONIT_DELETE('APP_NTPD');
         exit;
     end;

      if SYS.MONIT_CONFIG('APP_NTPD','/var/run/ntpd.pid','ntpd') then begin
         ini.Add('monit=1');
         result:=ini.Text;
         ini.free;
         exit;
      end;
     

pid:=NTPD_PID();

  if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));

      ini.Add('status='+SYS.PROCESS_STATUS(pid));
      ini.Add('pid_path=/var/run/ntpd.pid');


result:=ini.Text;
ini.free
end;
//#########################################################################################
function tntpd.ReadFileIntoString(path:string):string;
var
   List:TstringList;
begin

      if not FileExists(path) then begin
        exit;
      end;

      List:=Tstringlist.Create;
      List.LoadFromFile(path);
      result:=trim(List.Text);
      List.Free;
end;
//##############################################################################
procedure tntpd.NTPD_STOP();
 var
    count      :integer;
begin

     count:=0;


     if SYS.PROCESS_EXIST(NTPD_PID()) then begin
        writeln('Stopping NTPD................: ' + NTPD_PID() + ' PID..');
        logs.logs('Stopping NTPD service');
        
        
        if FileExists(INITD_PATH()) then begin
              fpsystem(INITD_PATH() + ' stop');
              exit;
        end;
        
        fpsystem('/bin/kill ' + NTPD_PID());
        while sys.PROCESS_EXIST(NTPD_PID()) do begin
              sleep(100);
              inc(count);
              if count>100 then begin
                 writeln('Stopping NTPD................: Failed');
                 exit;
              end;
        end;

      end else begin
        writeln('Stopping NTPD................: Already stopped');
     end;

end;
//##############################################################################
function tntpd.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:=false;
 s:='';
 if ParamCount>1 then begin
     for i:=2 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:=FoundWhatPattern;
   if RegExpr.Exec(s) then begin
      RegExpr.Free;
      result:=True;
   end;


end;
//##############################################################################
function tntpd.get_INFOS(key:string):string;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('INFOS',key,'');
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################

end.
