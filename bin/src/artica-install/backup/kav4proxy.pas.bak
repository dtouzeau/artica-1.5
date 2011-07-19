unit kav4proxy;

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
  tkav4proxy=class


private
     LOGS:Tlogs;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     kavicapserverEnabled:integer;
     SQUIDEnable:integer;
     RetranslatorEnabled:integer;
     procedure KAV4PROXY_SET_VALUE(KEY:string;VALUE:string;data:string);
     procedure CHECK_RIGHT_VALUES();
     function  KAV4PROXY_CHECKLICENSE():string;

     FUNCTION KAV4PROXY_PID_PATH():string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    INITD_PATH():string;
    function    BIN_PATH():string;
    function    VERSION():string;
    function    CONF_PATH():string;
    FUNCTION    KAV4PROXY_PID():string;
    procedure   KAV4PROXY_START();
    procedure   KAV4PROXY_STOP();
    function    KAV4PROXY_STATUS():string;
    function    KAV4PROXY_GET_VALUE(KEY:string;VALUE:string):string;
    function    PATTERN_DATE():string;
    function    KAV4PROXY_PERFORM_UPDATE():string;
    procedure   KAV4PROXY_RELOAD();
    procedure   REMOVE();
END;

implementation

constructor tkav4proxy.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       kavicapserverEnabled:=0;
       if not TryStrToInt(SYS.GET_INFO('kavicapserverEnabled'),kavicapserverEnabled) then kavicapserverEnabled:=0;
       if not TryStrToint(SYS.GET_INFO('RetranslatorEnabled'),RetranslatorEnabled) then RetranslatorEnabled:=0;
       if not TryStrToInt(SYS.GET_INFO('SQUIDEnable'),SQUIDEnable) then SQUIDEnable:=1;

       if SQUIDEnable=0 then kavicapserverEnabled:=0;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tkav4proxy.free();
begin
    logs.Free;
end;
//##############################################################################
function tkav4proxy.INITD_PATH():string;
begin
   if FileExists('/etc/init.d/kav4proxy') then exit('/etc/init.d/kav4proxy');
end;
//##############################################################################
function tkav4proxy.BIN_PATH():string;
begin
    if FileExists('/opt/kaspersky/kav4proxy/sbin/kav4proxy-kavicapserver') then exit('/opt/kaspersky/kav4proxy/sbin/kav4proxy-kavicapserver');
end;
//##############################################################################
function tkav4proxy.CONF_PATH():string;
begin
if FileExists('/opt/kaspersky/kav4proxy/etc/opt/kaspersky/kav4proxy.conf') then exit('/opt/kaspersky/kav4proxy/etc/opt/kaspersky/kav4proxy.conf');
if FileExists('/etc/opt/kaspersky/kav4proxy.conf') then exit('/etc/opt/kaspersky/kav4proxy.conf');

end;
//##############################################################################
procedure tkav4proxy.REMOVE();
begin
writeln('Uninstall Kaspersky For Linux Proxy server');
if FIleExists('/opt/kaspersky/kav4proxy/lib/bin/uninstall.pl') then logs.OutputCmd('/opt/kaspersky/kav4proxy/lib/bin/uninstall.pl');
   logs.DeleteFile('/etc/artica-postfix/versions.cache');
   logs.OutputCmd('/usr/share/artica-postfix/bin/artica-install --write-versions');
   logs.OutputCmd('/usr/share/artica-postfix/bin/process1 --force');
   logs.OutputCmd('/bin/rm -rf /opt/kaspersky/kav4proxy');
   logs.DeleteFile('/etc/init.d/kav4proxy');
   logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.squid.php --reconfigure');
writeln('Uninstall Kaspersky For Linux Proxy server DONE');
   end;
//##############################################################################

function tkav4proxy.KAV4PROXY_GET_VALUE(KEY:string;VALUE:string):string;
var path:string;
begin
  path:=CONF_PATH();
  if not FileExists(path) then begin
     logs.Debuglogs('tkav4proxy.KAV4PROXY_GET_VALUE():: unable to stat configuration file !!!');
     exit;
  end;
  

  GLOBAL_INI:=TIniFile.Create(path);
  result:=GLOBAL_INI.ReadString(KEY,VALUE,'');
  logs.Debuglogs('tkav4proxy.KAV4PROXY_GET_VALUE():: ['+ KEY + '] ' + VALUE+'='+result);
  GLOBAL_INI.Free;
end;
//#############################################################################
procedure tkav4proxy.KAV4PROXY_SET_VALUE(KEY:string;VALUE:string;data:string);
var path:string;
begin
  path:=CONF_PATH();
  if not FileExists(path) then exit;
  logs.Debuglogs('Starting......: Kav4Proxy set '+VALUE+' to "'+data+'"');
  GLOBAL_INI:=TIniFile.Create(path);
  GLOBAL_INI.WriteString(KEY,VALUE,data);
  GLOBAL_INI.UpdateFile;
  GLOBAL_INI.Free;
end;
//#############################################################################
procedure tkav4proxy.CHECK_RIGHT_VALUES();
var
   UseAVbasesSet:string;
   l:Tstringlist;
begin
    UseAVbasesSet:=KAV4PROXY_GET_VALUE('icapserver.engine.options','UseAVbasesSet');
    logs.Debuglogs('tkav4proxy.CHECK_RIGHT_VALUES()::UseAVbasesSet='+UseAVbasesSet );
    if UseAVbasesSet<>'standard' then begin
         if UseAVbasesSet<>'extended' then begin
            KAV4PROXY_SET_VALUE('icapserver.engine.options','UseAVbasesSet','extended');
         end;
    end;

l:=Tstringlist.Create;
l.Add('#!/bin/sh');
l.Add('action="%ACTION%"');
l.Add('verdict="%VERDICT%"');
l.Add('uri="%URL%"');
l.Add('ip="%CLIENT_ADDR%"');
l.Add('infected="%VIRUS_LIST%"');
l.Add('date="%DATE%"');
l.Add(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.Kav4ProxyEvents.php "$action" "$verdict" "$uri" "$ip" "$infected" "$date"');
l.Add('exit(0)');
logs.WriteToFile(l.Text,'/opt/kaspersky/kav4proxy/share/examples/artica.sh');
fpsystem('/bin/chmod 777 /opt/kaspersky/kav4proxy/share/examples/artica.sh');
KAV4PROXY_SET_VALUE('icapserver.notify','NotifyScript','/opt/kaspersky/kav4proxy/share/examples/artica.sh');
l.free;



end;
//#############################################################################

function tkav4proxy.PATTERN_DATE():string;
var
   BasesPath:string;
   xml:string;
   RegExpr:TRegExpr;
begin
//#UpdateDate="([0-9]+)\s+([0-9]+)"#
 BasesPath:=KAV4PROXY_GET_VALUE('path','BasesPath');
 if not FileExists(BasesPath + '/master.xml') then exit;
 xml:=logs.ReadFromFile(BasesPath + '/master.xml');
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='UpdateDate="([0-9]+)\s+([0-9]+)"';
 if RegExpr.Exec(xml) then begin

 //date --date "$dte 3 days 5 hours 10 sec ago"

    result:=RegExpr.Match[1] + ';' + RegExpr.Match[2];
 end;
 RegExpr.Free;
end;
//##############################################################################
function tkav4proxy.VERSION():string;
var
   RegExpr        :TRegExpr;
   F              :TstringList;
   T              :string;
   i              :integer;
begin
   result:='';
   if not FileExists(BIN_PATH()) then begin
      logs.Debuglogs('tkav4proxy.VERSION() -> unable to stat kav4proxy.conf');
      exit;
   end;
   t:=logs.FILE_TEMP();
   fpsystem(BIN_PATH()+' -v >'+t+' 2>&1');
   if not FileExists(t) then exit;
   f:=TstringList.Create;
   f.LoadFromFile(t);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='version\s+([0-9\.]+)/';
   For i:=0 to f.Count-1 do begin

   if RegExpr.Exec(f.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
   end;

   RegExpr.Free;
   f.free;
end;
//#############################################################################
FUNCTION tkav4proxy.KAV4PROXY_PID_PATH():string;
var
   cf:TinifIle;
begin
try
cf:=Tinifile.Create(CONF_PATH());
result:=cf.ReadString('icapserver.path','PidFile','/var/run/kav4proxy/kavicapserver.pid');
cf.free;
except
end;

end;
//#############################################################################
FUNCTION tkav4proxy.KAV4PROXY_PID():string;
begin
result:=SYS.GET_PID_FROM_PATH(KAV4PROXY_PID_PATH());
if length(trim(result))=0 then result:=SYS.PIDOF('/opt/kaspersky/kav4proxy/sbin/kav4proxy-kavicapserver');
end;

//##############################################################################
procedure tkav4proxy.KAV4PROXY_RELOAD();
var  pid:string;
begin
if kavicapserverEnabled=0 then begin
   KAV4PROXY_STOP();
   exit;
end;
  pid:=KAV4PROXY_PID();
  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.kav4proxy.php');
  if SYS.PROCESS_EXIST(pid) then begin
     fpsystem(INITD_PATH()+' reload');
     exit;
  end;
  KAV4PROXY_START();
end;
//##############################################################################



procedure tkav4proxy.KAV4PROXY_START();
var
   count:integer;
   pid:string;
   FileTemp:string;
begin
count:=0;
if not FileExists(BIN_PATH()) then exit;
FileTemp:=artica_path+'/ressources/logs/kav4proxy.start';

if kavicapserverEnabled=0 then begin
   KAV4PROXY_STOP();
   exit;
end;

pid:=KAV4PROXY_PID();

  if length(pid)=0 then begin
     pid:=SYS.PROCESS_LIST_PID(BIN_PATH());
     if length(pid)>0 then begin
         logs.DebugLogs('Starting......: Kav4Proxy kill all bad pids ' + pid);
         fpsystem('/bin/kill -9 ' + pid);
     end;
  end;

 logs.Debuglogs('KAV4PROXY_START() -> PID='+ KAV4PROXY_PID());
 if SYS.PROCESS_EXIST(KAV4PROXY_PID()) then begin
    logs.DebugLogs('Starting......: Kav4proxy already running using pid ' + KAV4PROXY_PID()+ '...');
    exit;
 end;

 if not SYS.IsUserExists('kluser') then begin
       logs.DebugLogs('Starting......: creating new user kluser');
       SYS.AddUserToGroup('kluser','klusers','/bin/sh','/home/kluser');
end;


 forceDirectories('/var/run/kav4proxy');
 ForceDirectories('/var/log/kaspersky/kav4proxy');
 logs.OutputCmd('/bin/chown -R kluser:klusers /var/run/kav4proxy');
 logs.OutputCmd('/bin/chown -R kluser:klusers /var/log/kaspersky/kav4proxy');
 logs.OutputCmd('/bin/chown -R kluser:klusers /var/opt/kaspersky/kav4proxy');
 logs.OutputCmd('/bin/chmod -R 755 /var/log/kaspersky/kav4proxy');
 CHECK_RIGHT_VALUES();
 fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.kav4proxy.php');
 logs.DebugLogs('Starting......: Kav4proxy...');
 fpsystem(INITD_PATH() + ' start >'+FileTemp+' 2>&1');
 logs.Debuglogs('Starting......: Kav4proxy Results: ' + logs.ReadFromFile(FileTemp));



 while not SYS.PROCESS_EXIST(KAV4PROXY_PID()) do begin

        sleep(100);
        inc(count);
        if count>30 then begin
           logs.DebugLogs('Starting......: Kav4proxy (failed)');
           exit;
        end;
  end;

 logs.DebugLogs('Starting......: Kav4proxy started with new pid ' + KAV4PROXY_PID());



end;
//##############################################################################
procedure tkav4proxy.KAV4PROXY_STOP();
 var
    pid:string;
    count:integer;
begin
count:=0;
  if not FileExists(BIN_PATH()) then exit;
  pid:=KAV4PROXY_PID();

  if not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Kav4Proxy...........: Already stopped');
     exit;
  end;


   writeln('Stopping Kav4Proxy...........: ' + pid + ' PID');

while SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        fpsystem('/bin/kill '+ pid);
        if count>30 then break;
        pid:=KAV4PROXY_PID();
end;

   pid:=KAV4PROXY_PID();


  if SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Kav4Proxy...........: ' + pid + ' PID');
     logs.OutputCmd(INITD_PATH() + ' stop');
  end else begin
     writeln('Stopping Kav4Proxy...........: stopped');
     SYS.MONIT_DELETE('APP_KAV4PROXY');
  end;

end;
//##############################################################################

function tkav4proxy.KAV4PROXY_STATUS():string;
var
pidpath:string;
begin
if not FileExists(BIN_PATH()) then  exit;

 pidpath:=logs.FILE_TEMP();
 fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --kav4proxy >'+pidpath +' 2>&1');
 result:=logs.ReadFromFile(pidpath);
 logs.DeleteFile(pidpath);

if(kavicapserverEnabled=0) then begin
    SYS.MONIT_DELETE('APP_KAV4PROXY');
    exit;
end;
SYS.MONIT_CONFIG('APP_KAV4PROXY',KAV4PROXY_PID_PATH(),'kav4proxy');
end;
//##############################################################################
function tkav4proxy.KAV4PROXY_CHECKLICENSE():string;
var
RegExpr:TRegExpr;
l:TstringList;
i:Integer;
cachefile:string;
begin
   if not FileExists('/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager') then exit;
   cachefile:='/etc/artica-postfix/kav4proxy-licensemanager';

   if SYS.FILE_TIME_BETWEEN_MIN(cachefile)<1440 then exit;
   fpsystem('/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager -i >'+cachefile + ' 2>&1');
    logs.OutputCmd('/bin/chown -R kluser:klusers /var/opt/kaspersky/kav4proxy');
l:=TstringList.Create;
l.LoadFromFile(cachefile);
logs.DeleteFile(cachefile);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^Error loading license';

For i:=0 to l.Count-1 do begin
     if RegExpr.Exec(l.Strings[i]) then begin
        SYS.JGrowl('{APP_KAV4PROXY}: Error loading license',l.Strings[i]);
        result:=trim(l.Strings[i]);
        break;
     end;
end;

l.free;
RegExpr.free;
end;
//##############################################################################



function tkav4proxy.KAV4PROXY_PERFORM_UPDATE():string;
var
tmp:string;
RegExpr:TRegExpr;
l:TstringList;
i:Integer;
spattern_date:string;
Retranslator_g:string;
begin
result:='';
if not FileExists('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date') then exit;
if SYS.GET_INFO('ArticaEnableKav4ProxyInSquid')<>'1' then exit;;

tmp:=logs.FILE_TEMP();
if RetranslatorEnabled=1 then Retranslator_g:=' -g /var/db/kav/databases';
 logs.OutputCmd('/bin/chown -R kluser:klusers /var/opt/kaspersky/kav4proxy');
fpsystem('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date'+Retranslator_g+' >' + tmp + ' 2>&1');
if not FileExists(tmp) then exit;
RegExpr:=TRegExpr.Create;
l:=TstringList.Create;
l.LoadFromFile(tmp);
logs.DeleteFile(tmp);
For i:=0 to l.Count-1 do begin
    RegExpr.Expression:='^Error loading license: The trial license is expired';
    if RegExpr.Exec(l.Strings[i]) then begin
       logs.NOTIFICATION('[ARTICA]: ('+ SYS.HOSTNAME_g()+') Failed to update Kaspersky For Proxy server Pattern files','Your license is expired, you need to update it...'+l.Text,'update');
       break;
    end;
    RegExpr.Expression:='^Update.+?completed successfully';
    if RegExpr.Exec(l.Strings[i]) then begin
         spattern_date:=PATTERN_DATE();
         logs.NOTIFICATION('[ARTICA]: ('+ SYS.HOSTNAME_g()+') Success update Kaspersky For Proxy server Pattern files '+spattern_date,l.Text,'update');
         break;
    end;
    
 RegExpr.Expression:='^Failed to signal.+?No such processCommand';
    if RegExpr.Exec(l.Strings[i]) then begin
       KAV4PROXY_STOP();
       KAV4PROXY_START();
       spattern_date:=PATTERN_DATE();
       logs.NOTIFICATION('[ARTICA]: ('+ SYS.HOSTNAME_g()+') Success update Kaspersky For proxy server Pattern file '+spattern_date,l.Text,'update');
       break;
    end;
    
end;

RegExpr.free;
l.free;
end;
//##############################################################################










end.
