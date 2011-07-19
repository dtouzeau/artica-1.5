unit monit;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr,zsystem;



  type
  tmonit=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
    procedure   WRITE_INITD();
    function   INIT_D_PATH():string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   CONFIG_DEFAULT();

    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
    function    VERSIONNUM():integer;
    procedure   START();
    procedure   STOP();
    function    STATUS:string;
    procedure   RELOAD();
    procedure   BuildStatus();
    procedure    wakeup();
END;

implementation

constructor tmonit.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tmonit.free();
begin
    logs.Free;
end;
//##############################################################################
function tmonit.BIN_PATH():string;
begin
   if FileExists(SYS.LOCATE_GENERIC_BIN('monit')) then exit(SYS.LOCATE_GENERIC_BIN('monit'));
   exit('/usr/share/artica-postfix/bin/artica-monit');
end;
//##############################################################################
function tmonit.INIT_D_PATH():string;
begin
   if FileExists('/etc/init.d/monit') then exit('/etc/init.d/monit');
   exit('/etc/init.d/monit');

end;
//##############################################################################
function tmonit.PID_NUM():string;
begin
    if not FIleExists(BIN_PATH()) then exit;
    result:=sys.GET_PID_FROM_PATH('/var/run/monit/monit.pid');
end;
//##############################################################################
procedure tmonit.wakeup();
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    filetmp:string;
    monitored:integer;
begin
filetmp:=logs.FILE_TEMP();

fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid -s /var/run/monit/monit.state status >'+filetmp+' 2>&1');
IF NOT FileExists(filetmp) then exit;
FileDatas:=TStringList.Create;
FileDatas.LoadFromFile(filetmp);
logs.DeleteFile(filetmp);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='status\s+not monitored';
monitored:=0;
for i:=0 to FileDatas.Count-1 do begin
      if RegExpr.Exec(FileDatas.Strings[i]) then begin
         inc(monitored);
         break;
      end;
end;

 if monitored>0 then  fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid -s /var/run/monit/monit.state monitor all >/dev/null 2>&1');

end;
 //##############################################################################



function tmonit.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_MONIT');
if length(result)>2 then exit;

filetmp:=logs.FILE_TEMP();
if not FileExists(BIN_PATH()) then begin
   logs.Debuglogs('unable to find monit');
   exit;
end;

logs.Debuglogs(BIN_PATH()+' -V >'+filetmp+' 2>&1');
fpsystem(BIN_PATH()+' -V >'+filetmp+' 2>&1');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='version\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;

SYS.SET_CACHE_VERSION('APP_MONIT',result);

end;
//#############################################################################
procedure tmonit.WRITE_INITD();
var
   l:TstringList;
   initPath:string;
begin
initPath:=INIT_D_PATH();
l:=TstringList.Create;
l.add('#! /bin/sh');
l.add('#');
l.add('# monit		Startup script for the System monitor');
l.add('#');
l.add('#');
l.add('### BEGIN INIT INFO');
l.add('# Provides:          pdns');
l.add('# Required-Start:    $local_fs $network');
l.add('# Required-Stop:     $local_fs $network');
l.add('# Should-Start:      $named');
l.add('# Should-Stop:       $named');
l.add('# Default-Start:     2 3 4 5');
l.add('# Default-Stop:      0 1 6');
l.add('# Short-Description: PowerDNS');
l.add('### END INIT INFO');
l.add('');
l.add('PATH=/bin:/usr/bin:/sbin:/usr/sbin');
l.add('');
l.add('');
l.add('start () {');
l.add('	/etc/init.d/artica-postfix start monit');
l.add('}');
l.add('');
l.add('stop () {');
l.add('      /etc/init.d/artica-postfix stop monit');
l.add('}');
l.add('');
l.add('case "$1" in');
l.add('    start)');
l.add('	/etc/init.d/artica-postfix start monit');
l.add('	;;');
l.add('    stop)');
l.add('	/etc/init.d/artica-postfix stop monit');
l.add('	;;');
l.add('    reload|force-reload)');
l.add('	/etc/init.d/artica-postfix stop monit');
l.add('	/etc/init.d/artica-postfix start monit');
l.add('	;;');
l.add('    restart)');
l.add('	/etc/init.d/artica-postfix stop monit');
l.add('	/etc/init.d/artica-postfix start monit');
l.add('	;;');
l.add('    *)');
l.add('	echo "Usage: '+initPath+' {start|stop|reload|force-reload|restart}"');
l.add('	exit 3');
l.add('	;;');
l.add('esac');
l.add('');
l.add('exit 0');

l.SaveToFile(initPath);
fpsystem('/bin/chmod 755 '+initPath);
l.free;


end;

//#############################################################################
procedure tmonit.START();
var
   count:integer;
   pid:string;


begin
    pid:=PID_NUM();

    IF sys.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: daemon monitor Already running PID '+ pid);
       exit;
    end;

    if not FIleExists(BIN_PATH()) then begin
       logs.DebugLogs('Starting......: daemon monitor is not installed');
       exit;
    end;


    WRITE_INITD();
    CONFIG_DEFAULT();
    fpsystem('/bin/chmod 600 /etc/monit/monitrc >/dev/null 2>&1');
    logs.DebugLogs('Starting......: daemon monitor...');
    logs.Debuglogs(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid -s /var/run/monit/monit.state >/dev/null 2>&1 &');
    fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid -s /var/run/monit/monit.state >/dev/null 2>&1 &');

 count:=0;
 while not SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(100);
        inc(count);
        if count>10 then begin
           logs.DebugLogs('Starting......: daemon monitor (timeout)');
           break;
        end;
        logs.Debuglogs('count:'+intTostr(count));
  end;

pid:=PID_NUM();
    IF sys.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: daemon monitor successfully started and running PID '+ pid);
       exit;
    end;

logs.DebugLogs('Starting......: daemon monitor failed');

end;


//#############################################################################
procedure tmonit.RELOAD();
begin
fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid reload');
end;
//#############################################################################

procedure tmonit.STOP();
var
   count:integer;
   pid:string;
   i:integer;



begin


    if not FIleExists(BIN_PATH()) then begin
       writeln('Stopping system monitor......: not installed');
       exit;
    end;

    pid:=PID_NUM();

    IF not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping system monitor......: Already stopped');
       fpsystem('/bin/chmod 600 /etc/monit/monitrc >/dev/null 2>&1');
       fpsystem('/bin/rm -f /etc/monit/conf.d/*');
       exit;
    end;

    sys.DirFiles('/usr/sbin','APP_*.sh');
    for i:=0 to SYS.DirListFiles.Count-1 do begin
        if FileExists('/usr/sbin/'+SYS.DirListFiles.Strings[i]) then begin
            writeln('Stopping system monitor......: deleting '+SYS.DirListFiles.Strings[i]);
            logs.DeleteFile('/usr/sbin/'+SYS.DirListFiles.Strings[i]);
        end;

    end;



    fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid unmonitor all >/dev/null 2>&1');

    writeln('Stopping system monitor......: Stopping Smoothly PID '+pid);
    count:=0;


    pid:=PID_NUM();
    logs.OutputCmd('/bin/kill '+pid);
    while SYS.PROCESS_EXIST(pid) do begin
            sleep(200);
            inc(count);

        if count>20 then begin
           writeln('Stopping system monitor......: timed-out');
           break;
        end;

        pid:=PID_NUM()
    end;


pid:=PID_NUM();
IF not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping system monitor......: Successfully stopped');
       fpsystem('/bin/rm -f /etc/monit/conf.d/*');
       exit;
    end;

    writeln('Stopping system monitor......: Stopping PID '+pid);
       logs.OutputCmd('/bin/kill '+pid);
       count:=0;
       pid:=PID_NUM();
       while SYS.PROCESS_EXIST(pid) do begin
            sleep(100);
            inc(count);
        if count>10 then begin
           writeln('Stopping system monitor......: timed-out');
           logs.OutputCmd('/bin/kill -9 '+pid);
           break;
        end;
         logs.OutputCmd('/bin/kill -9 '+pid);
         pid:=PID_NUM();
     end;


pid:=PID_NUM();
IF not sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping system monitor......: Successfully stopped');
   fpsystem('/bin/rm -f /etc/monit/conf.d/*');
   exit;
end;

writeln('Stopping system monitor......: Failed');

end;


//#############################################################################
function tmonit.STATUS:string;
var
ini:TstringList;
pid:string;
begin
   if not fileExists(BIN_PATH()) then exit;
   ini:=TstringList.Create;
   ini.Add('[APP_MONIT]');
      ini.Add('service_name=APP_MONIT');
      ini.Add('service_cmd=monit');
      ini.Add('service_disabled=1');
      ini.Add('application_installed=0');
      ini.Add('service_disabled=0');
      ini.Add('master_version='+VERSION());


   if SYS.MONIT_CONFIG('APP_MONIT','/var/run/monit/monit.pid','monit') then begin
      ini.Add('monit=1');
      result:=ini.Text;
      ini.free;
      logs.Debuglogs('tmonit.STATUS(): done.');
      exit;
   end;

      pid:=PID_NUM();
      if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('application_enabled=1');
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
      ini.Add('status='+SYS.PROCESS_STATUS(pid));
      ini.Add('pid_path=/var/run/monit/monit.pid');
      result:=ini.Text;
      ini.free;

end;
//##############################################################################
function tmonit.VERSIONNUM():integer;
var
   zversion:string;
begin
    zversion:=VERSION();
    zversion:=AnsiReplaceText(zversion,'.','');
    if length(zversion)=3 then zversion:=zversion+'0';
    TryStrToInt(zversion,result);

end;

procedure tmonit.BuildStatus();
begin
    fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid status');
end;
//##############################################################################
procedure tmonit.CONFIG_DEFAULT();
var
   l:Tstringlist;
   i:integer;
   cpunum:integer;
   normal:integer;
   normal2:integer;
   busy:integer;
   notif:TiniFile;
   EnableNotifs:integer;
   smtp_server:string;
   smtp_server_port:string;
   smtp_sender:string;
   smtp_dest:string;
   smtp_auth_user:string;
   smtp_auth_passwd:string;
   tls_enabled:integer;
   recipients:Tstringlist;
   bcc:Tstringlist;
   myversion:integer;
   monit_not_on:string;
begin
ForceDirectories('/var/monit');
ForceDirectories('/var/run/monit');
ForceDirectories('/etc/monit/conf.d');
myversion:=VERSIONNUM();
l:=Tstringlist.Create;
logs.DebugLogs('Starting......: daemon monitor version '+ INtTOstr(myversion));

notif:=TiniFile.Create('/etc/artica-postfix/smtpnotif.conf');
EnableNotifs:=notif.ReadInteger('SMTP','monit',0);
tls_enabled:=notif.ReadInteger('SMTP','tls_enabled',0);


smtp_server:=notif.ReadString('SMTP','smtp_server_name','');
smtp_server_port:=notif.ReadString('SMTP','smtp_server_port','25');
smtp_dest:=notif.ReadString('SMTP','smtp_dest','');
smtp_sender:=notif.ReadString('SMTP','smtp_sender','');
smtp_auth_user:=trim(notif.ReadString('SMTP','smtp_auth_user',''));
smtp_auth_passwd:=notif.ReadString('SMTP','smtp_auth_passwd','');
recipients:=Tstringlist.Create;
if length(smtp_dest)>0 then recipients.Add(smtp_dest);
bcc:=Tstringlist.Create;
if FIleExists('/etc/artica-postfix/settings/Daemons/SmtpNotificationConfigCC') then begin
   bcc.LoadFromFile('/etc/artica-postfix/settings/Daemons/SmtpNotificationConfigCC');
   for i:=0 to bcc.Count-1 do begin
       if length(trim(bcc.Strings[i]))>0 then recipients.Add(trim(bcc.Strings[i]));
   end;
end;
bcc.free;
smtp_server:=trim(smtp_server);
if length(smtp_server)=0 then  EnableNotifs:=0;
if length(smtp_sender)=0 then  EnableNotifs:=0;
if recipients.Count=0 then EnableNotifs:=0;
if length(trim(smtp_server_port))=0 then smtp_server_port:='25';
monit_not_on:='instance,action';
if myversion<5000 then begin
  l.add('set daemon 60');
  monit_not_on:='instance';
end else begin
  l.add('set daemon 60 with start delay 20');
  l.add('set idfile /var/run/monit/monit.id');
end;

cpunum:=SYS.CPU_NUMBER();
normal:=(cpunum*2)+1;
normal2:=cpunum*2;
busy:=cpunum*4;


l.add('set logfile syslog facility log_daemon');

l.add('set statefile /var/run/monit/monit.state');
l.add('');
if EnableNotifs=1 then begin
    l.add('set mailserver '+smtp_server+' PORT '+smtp_server_port);
    if length(smtp_auth_user)>0  then L.Add(chr(9)+'USERNAME "'+smtp_auth_user+'" PASSWORD "'+smtp_auth_passwd+'"');
    if tls_enabled=1 then L.Add(chr(9)+'using TLSV1');
    l.add('set eventqueue');
    l.add('     basedir /var/monit');
    l.add('     slots 100');

   l.add('set mail-format {');
   l.add('   from: '+smtp_sender);
   l.add('   subject: Artica service monitor: $SERVICE $EVENT');
   l.add('   message: Artica service monitor $ACTION $SERVICE at $DATE on $HOST: $DESCRIPTION.');
   l.add('}');
   l.add('');
   for i:=0 to recipients.Count-1 do begin
       l.add('set alert '+recipients.Strings[i]+' but not on {'+monit_not_on+'}');
   end;
   l.add('');
end;
l.add('set httpd port 2874 and use address localhost allow localhost');
l.add('');
l.add('check system '+SYS.HOSTNAME_g());
l.add('    if loadavg (1min) > '+IntToStr(busy)+' then exec "' +SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --loadavg"');
l.add('    if loadavg (5min) > '+IntToStr(normal)+' then exec "' +SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --loadavg"');
l.add('    if loadavg (15min) > '+IntToStr(normal2)+' then exec "'+ SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --loadavg"');
l.add('    if memory usage > 75% then exec "'+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --mem"');
l.add('    if cpu usage (user) > 80% then exec "'+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --cpu"');
l.add('    if cpu usage (system) > 80% then exec "'+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.watchdog.php --cpu"');
l.add('');
l.add('include /etc/monit/conf.d/*');
logs.WriteToFile(l.Text,'/etc/monit/monitrc');
logs.DebugLogs('Starting......: daemon monitor succes writing configuration file');
fpsystem('/bin/chmod -R 600 /etc/monit/monitrc');
logs.DebugLogs('Starting......: Launching status and build monitor configurations files');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.first.settings.php --monit >/dev/null 2>&1');
fpsystem('/usr/share/artica-postfix/bin/artica-install --status >/dev/null 2>&1');
logs.DebugLogs('Starting......: done');
l.free;
end;


end.
