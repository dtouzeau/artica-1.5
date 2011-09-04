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
     ArticaEnableKav4ProxyInSquid:integer;
     procedure KAV4PROXY_SET_VALUE(KEY:string;VALUE:string;data:string);
     procedure CHECK_RIGHT_VALUES();
     function  KAV4PROXY_CHECKLICENSE():string;
     procedure WRITE_INIT_D_DEBIAN();

     FUNCTION KAV4PROXY_PID_PATH():string;
public
    LICENSE_ERROR_TEXT:string;
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
    function    LICENSE_ERROR():string;
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
       if not TryStrToInt(SYS.GET_INFO('ArticaEnableKav4ProxyInSquid'),ArticaEnableKav4ProxyInSquid) then ArticaEnableKav4ProxyInSquid:=0;






       if FileExists('/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE') then begin
          if kavicapserverEnabled=0 then SYS.set_INFO('kavicapserverEnabled','1');
          if SQUIDEnable=1 then begin
             if ArticaEnableKav4ProxyInSquid=0 then begin
                SYS.set_INFO('ArticaEnableKav4ProxyInSquid','1');
                ArticaEnableKav4ProxyInSquid:=1;
             end;
          end;
          kavicapserverEnabled:=1;

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
procedure tkav4proxy.WRITE_INIT_D_DEBIAN();
var
l:Tstringlist;
begin
l:=Tstringlist.Create;
l.add('#!/bin/sh');
l.add('');
l.add('### BEGIN INIT INFO');
l.add('# Provides:          kav4proxy');
l.add('# Required-Start:    $network $syslog');
l.add('# Required-Stop:     $network $syslog');
l.add('# Default-Start:     2 3 4 5');
l.add('# Default-Stop:      0 1 6');
l.add('# Short-Description: Kaspersky Anti-Virus for Proxy Server');
l.add('# Description:  Kaspersky Anti-Virus for Proxy Server provides');
l.add('#		anti-virus protection for network traffic routed through proxy');
l.add('#		servers which support the Internet Content Adaptation Protocol (ICAP).');
l.add('### END INIT INFO');
l.add('');
l.add('# Basic support for IRIX style chkconfig');
l.add('# chkconfig: 2345 50 20');
l.add('# description: Kaspersky Anti-Virus for Proxy Server');
l.add('');
l.add('KL_APP=kav4proxy');
l.add('KL_SERVICE_TITLE="Kaspersky Anti-Virus ICAP server"');
l.add('KL_SERVICE_NAME=kav4proxy');
l.add('KL_SERVICE_BIN=/opt/kaspersky/kav4proxy/sbin/kav4proxy-kavicapserver');
l.add('KL_SERVICE_CONFIG=/etc/opt/kaspersky/kav4proxy.conf');
l.add('');
l.add('KAV4PROXY_MAXFD=10000');
l.add('');
l.add('. /opt/kaspersky/$KL_APP/lib/rcfunctions.sh');
l.add('');
l.add('# recreate PID-files directory');
l.add('PID_PATH=/var/run/$KL_APP');
l.add('if [ ! -d "$PID_PATH" ] ;  then');
l.add('  mkdir -p "$PID_PATH"');
l.add('  chown kluser:klusers "$PID_PATH"');
l.add('  chmod 0770 "$PID_PATH"');
l.add('fi');
l.add('');
l.add('KL_SERVICE_PIDFILE=$PID_PATH/kavicapserver.pid');
l.add('');
l.add('# Reset status of this service');
l.add('kl_reset');
l.add('');
l.add('case "$1" in');
l.add('    start)');
l.add('        kl_action "Starting"');
l.add('        if [ -n "$KAV4PROXY_MAXFD" ] ; then');
l.add('            ulimit -n "$KAV4PROXY_MAXFD"');
l.add('        fi');
l.add('        kl_start_daemon -p $KL_SERVICE_PIDFILE $KL_SERVICE_BIN -C "$KL_SERVICE_CONFIG"');
l.add('        kl_status -v');
l.add('        ;;');
l.add('');
l.add('    stop)');
l.add('        kl_action "Shutting down"');
l.add('        kl_killproc -p $KL_SERVICE_PIDFILE $KL_SERVICE_BIN');
l.add('        kl_status -v');
l.add('        ;;');
l.add('');
l.add('    try-restart|condrestart)');
l.add('        # Do a restart only if the service was active before.');
l.add('        $0 status >/dev/null');
l.add('        if test $? = 0; then');
l.add('                $0 restart');
l.add('        else');
l.add('                kl_reset # Not running is not a failure.');
l.add('        fi');
l.add('');
l.add('        # Remember status and be quiet');
l.add('        kl_status');
l.add('        ;;');
l.add('');
l.add('    restart)');
l.add('        ## Stop the service and regardless of whether it was');
l.add('        ## running or not, start it again.');
l.add('        $0 stop');
l.add('        $0 start');
l.add('');
l.add('        # Remember status and be quiet');
l.add('        kl_status');
l.add('        ;;');
l.add('');
l.add('    force-reload)');
l.add('        ## Signal the daemon to reload its config. Most daemons do this');
l.add('        ## on signal 1 (SIGHUP).');
l.add('        ## If it does not support it, restart the service if it is running.');
l.add('');
l.add('        ## if it supports it:');
l.add('        kl_action "Reload service"');
l.add('        kl_killproc -p $KL_SERVICE_PIDFILE $KL_SERVICE_BIN -HUP');
l.add('        kl_status -v');
l.add('');
l.add('        ## Otherwise:');
l.add('        #$0 try-restart');
l.add('        #kl_status');
l.add('        ;;');
l.add('');
l.add('    reload)');
l.add('        ## Like force-reload, but if daemon does not support');
l.add('        ## signaling, do nothing (!)');
l.add('');
l.add('        # If it supports signaling:');
l.add('        kl_action "Reload service"');
l.add('        kl_killproc -p $KL_SERVICE_PIDFILE $KL_SERVICE_BIN -HUP');
l.add('        kl_status -v');
l.add('');
l.add('        ## Otherwise if it does not support reload:');
l.add('        #if [ "$KL_OS" = ''suse'' ] ; then');
l.add('        #    rc_failed 3');
l.add('        #    kl_status -v');
l.add('        #else');
l.add('        #    $0 restart');
l.add('        #fi');
l.add('        ;;');
l.add('');
l.add('    status)');
l.add('        kl_action "Checking for service"');
l.add('        kl_checkproc -p $KL_SERVICE_PIDFILE $KL_SERVICE_BIN');
l.add('        kl_status -v');
l.add('        ;;');
l.add('');
l.add('    reload_avbase)');
l.add('        kl_action "Reload Anti-Virus bases in"');
l.add('        kl_killproc -p $KL_SERVICE_PIDFILE $KL_SERVICE_BIN -USR1');
l.add('        kl_status -v');
l.add('        ;;');
l.add('');
l.add('    stats)');
l.add('        kl_action "Update statistics files of"');
l.add('        kl_killproc -p $KL_SERVICE_PIDFILE $KL_SERVICE_BIN -USR2');
l.add('        kl_status -v');
l.add('        ;;');
l.add('');
l.add('    *)');
l.add('        echo "Usage: $0 {start|stop|status|try-restart|restart|force-reload|reload|reload_avbase|stats}"');
l.add('        exit 1');
l.add('        ;;');
l.add('esac');
l.add('');
l.add('kl_exit');
l.add('');
logs.WriteToFile(l.Text,'/etc/init.d/kav4proxy');
logs.DebugLogs('Starting......: Kav4Proxy init.d for debian done');
l.free;
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

 if FileExists('/usr/bin/apt-get') then WRITE_INIT_D_DEBIAN();

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
      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.kav4proxy.php --umount');
     SYS.MONIT_DELETE('APP_KAV4PROXY');
  end;

end;
//##############################################################################

function tkav4proxy.KAV4PROXY_STATUS():string;
var
pidpath:string;
begin
SYS.MONIT_DELETE('APP_KAV4PROXY');
if not FileExists(BIN_PATH()) then  exit;

 pidpath:=logs.FILE_TEMP();
 fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --kav4proxy >'+pidpath +' 2>&1');
 result:=logs.ReadFromFile(pidpath);
 logs.DeleteFile(pidpath);
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

   if SYS.FILE_TIME_BETWEEN_MIN(cachefile)<2880 then exit;
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
D:boolean;
cmd:string;

begin
result:='';
D:=false;
D:=SYS.COMMANDLINE_PARAMETERS('--verbose');
if not FileExists('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date') then exit;
if ArticaEnableKav4ProxyInSquid<>1 then begin
   if D then writeln('KAV4PROXY_PERFORM_UPDATE::  ArticaEnableKav4ProxyInSquid is not enabled, aborting...');
   exit;;
end;


tmp:='/var/log/artica-postfix/kaspersky/kav4proxy/'+logs.FileTimeName();
if RetranslatorEnabled=1 then Retranslator_g:=' -g /var/db/kav/databases';
 logs.OutputCmd('/bin/chown -R kluser:klusers /var/opt/kaspersky/kav4proxy');
 cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.kav4proxy.php';
 if D then writeln(cmd);
 fpsystem(cmd);
 cmd:='/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date'+Retranslator_g+' >' + tmp + ' 2>&1';
 if D then writeln(cmd);
 fpsystem(cmd);
 if not FileExists(tmp) then exit;
 RegExpr:=TRegExpr.Create;
 l:=TstringList.Create;
 l.LoadFromFile(tmp);

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

function tkav4proxy.LICENSE_ERROR():string;
var
tmp:string;
RegExpr:TRegExpr;
l:TstringList;
i:Integer;
begin
result:='False';
if not FileExists('/etc/artica-postfix/kav4proxy-licensemanager') then fpsystem('/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager -s >/etc/artica-postfix/kav4proxy-licensemanager 2>&1');
l:=Tstringlist.Create;
l.LoadFromFile('/etc/artica-postfix/kav4proxy-licensemanager');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='Error\s+';
for i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
         result:='True';
         LICENSE_ERROR_TEXT:=l.Strings[i];
         RegExpr.free;
         l.free;
         exit;
      end;
end;
         RegExpr.free;
         l.free;
end;
//##############################################################################






end.
