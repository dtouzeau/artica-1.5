unit squidguard;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr,zsystem;



  type
  tsquidguard=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     TAIL_STARTUP:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   CONFIG_DEFAULT();

    function    VERSION():string;
    function    BIN_PATH():string;
    function    SQUIDCLAMAV_VERSION():string;
    function    VERSIONNUM():integer;
    procedure   START();
    function    STATUS():string;
    procedure   RELOAD();
    function    TAIL_PID():string;
    procedure   BuildStatus();
    procedure   STOP();
END;

implementation

constructor tsquidguard.Create(const zSYS:Tsystem);
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

      TAIL_STARTUP:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.squidguard-tail.php';

end;
//##############################################################################
procedure tsquidguard.free();
begin
    logs.Free;
end;
//##############################################################################
function tsquidguard.BIN_PATH():string;
begin
   if FileExists(SYS.LOCATE_GENERIC_BIN('squidGuard')) then exit(SYS.LOCATE_GENERIC_BIN('squidGuard'));
end;
//##############################################################################
function tsquidguard.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    filetmp:string;
    nocache:boolean;
begin
nocache:=false;
if ParamStr(1)='--squid-version-bin' then nocache:=true;

if not nocache then begin
   result:=SYS.GET_CACHE_VERSION('APP_SQUIDGUARD');
   if length(result)>2 then exit;
end;
filetmp:=logs.FILE_TEMP();
if not FileExists(BIN_PATH()) then begin
   logs.Debuglogs('unable to find squidGuard');
   exit;
end;

logs.Debuglogs(BIN_PATH()+' -v >'+filetmp+' 2>&1');
fpsystem(BIN_PATH()+' -v >'+filetmp+' 2>&1');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='SquidGuard:\s+([0-9\.]+)';
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

SYS.SET_CACHE_VERSION('APP_SQUIDGUARD',result);

end;
//#############################################################################
function tsquidguard.SQUIDCLAMAV_VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    filetmp:string;
    nocache:boolean;
    squid_clambin:string;
begin
nocache:=false;

if not nocache then begin
   result:=SYS.GET_CACHE_VERSION('APP_SQUIDCLAMAV');
   if length(result)>2 then exit;
end;

squid_clambin:=SYS.LOCATE_GENERIC_BIN('squidclamav');

filetmp:=logs.FILE_TEMP();
if not FileExists(squid_clambin) then begin
   logs.Debuglogs('unable to find squid_clambin');
   exit;
end;

logs.Debuglogs(squid_clambin+' -v >'+filetmp+' 2>&1');
fpsystem(squid_clambin+' -v >'+filetmp+' 2>&1');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='SquidClamav v([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end else begin
            logs.Debuglogs('SquidClamav: Not found in "'+FileDatas.Strings[i]+'"');
        end;
    end;
             RegExpr.free;
             FileDatas.Free;

SYS.SET_CACHE_VERSION('APP_SQUIDCLAMAV',result);

end;
//#############################################################################
procedure tsquidguard.START();
var
   pid:string;
   pidint:integer;
   log_path:string;
   count:integer;
   cmd:string;
   CountTail:Tstringlist;
begin

if not FileExists(BIN_PATH()) then begin
   logs.Debuglogs('Starting......: squidGuard RealTime log squidGuard is not installed');
   exit;
end;


pid:=TAIL_PID();
if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: squidGuard RealTime log already running with pid '+pid);
      CountTail:=Tstringlist.Create;
      CountTail.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST('/usr/bin/tail -f -n 0 /var/log/squid/squidGuard.log'));
      logs.DebugLogs('Starting......: squidGuard RealTime log process number:'+IntToStr(CountTail.Count));
      if CountTail.Count>3 then fpsystem('/etc/init.d/artica-postfix restart squidguard-tail');
      CountTail.free;
      exit;
end;
log_path:='/var/log/squid/squidGuard.log';

if not FileExists(log_path) then begin
   fpsystem('/bin/touch '+log_path);
   fpsystem('/bin/chown squid:squid '+ log_path);
   fpsystem('/bin/chmod 644 '+ log_path);
end;

STOP();
logs.DebugLogs('Starting......: squidGuard RealTime log path: '+log_path);

pid:=SYS.PIDOF_PATTERN('/usr/bin/tail -f -n 0 '+log_path);
count:=0;
pidint:=0;
      while SYS.PROCESS_EXIST(pid) do begin
          if count>0 then break;
          if not TryStrToInt(pid,pidint) then continue;
          logs.DebugLogs('Starting......: squidGuard RealTime log stop tail pid '+pid);
          if pidint>0 then  fpsystem('/bin/kill '+pid);
          sleep(200);
          pid:=SYS.PIDOF_PATTERN('/usr/bin/tail -f -n 0 '+log_path);
          inc(count);
      end;

cmd:='/usr/bin/tail -f -n 0 '+log_path+'|'+TAIL_STARTUP+' >>/var/log/artica-postfix/squidguard-logger-start.log 2>&1 &';
logs.Debuglogs(cmd);
fpsystem(cmd);
pid:=TAIL_PID();
count:=0;
while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting......: squidGuard RealTime log (timeout)');
           break;
        end;
        pid:=TAIL_PID();
  end;

pid:=TAIL_PID();

if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: squidGuard RealTime log success with pid '+pid);
      exit;
end else begin
    logs.DebugLogs('Starting......: squidGuard RealTime log failed');
end;
end;
//#############################################################################
function tsquidguard.TAIL_PID():string;
var
   pid:string;
begin

if FileExists('/etc/artica-postfix/exec.squidguard-tail.php.pid') then begin
   pid:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/exec.squidguard-tail.php.pid');
   logs.Debuglogs('TAIL_PID /etc/artica-postfixexec.squidguard-tail.php.pid='+pid);
   if SYS.PROCESS_EXIST(pid) then result:=pid;
   exit;
end;


result:=SYS.PIDOF_PATTERN(TAIL_STARTUP);
logs.Debuglogs(TAIL_STARTUP+' pid='+pid);
end;
//#####################################################################################

procedure tsquidguard.STOP();
var
   pid:string;
   pidint,i:integer;
   count:integer;
   CountTail:Tstringlist;
begin
pid:=TAIL_PID();
if not SYS.PROCESS_EXIST(pid) then begin
      writeln('Stopping SquidGuard tail.....: Already stopped');
      CountTail:=Tstringlist.Create;
      try
         CountTail.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST('/usr/bin/tail -f -n 0 /var/log/squid/squidGuard.log'));
         writeln('Stopping SquidGuard tail.....: Tail processe(s) number '+IntToStr(CountTail.Count));
      except
       writeln('Stopping SquidGuard tail.....: fatal error on SYS.PIDOF_PATTERN_PROCESS_LIST() function');
      end;

      count:=0;
     for i:=0 to CountTail.Count-1 do begin;
          pid:=CountTail.Strings[i];
          if count>100 then break;
          if not TryStrToInt(pid,pidint) then continue;
          writeln('Stopping SquidGuard tail.....: Stop tail pid '+pid);
          if pidint>0 then  fpsystem('/bin/kill '+pid);
          sleep(100);
          inc(count);
      end;
      exit;
end;

writeln('Stopping SquidGuard tail.....: Stopping pid '+pid);
fpsystem('/bin/kill '+pid);

pid:=TAIL_PID();
if not SYS.PROCESS_EXIST(pid) then begin
      writeln('Stopping SquidGuard tail.....: Stopped');
end;


CountTail:=Tstringlist.Create;
CountTail.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST('/usr/bin/tail -f -n 0 /var/log/squid/access.log'));
writeln('Stopping SquidGuard tail.....: Tail processe(s) number '+IntToStr(CountTail.Count));
count:=0;
     for i:=0 to CountTail.Count-1 do begin;
          pid:=CountTail.Strings[i];
          if count>100 then break;
          if not TryStrToInt(pid,pidint) then continue;
          writeln('Stopping SquidGuard tail.....: Stop tail pid '+pid);
          if pidint>0 then  fpsystem('/bin/kill '+pid);
          sleep(100);
          inc(count);
      end;


end;
//####################################################################################

//#############################################################################
procedure tsquidguard.RELOAD();
begin
fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid reload');
end;
//#############################################################################

function tsquidguard.STATUS:string;
var
pidpath:string;
begin
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --squidguard-tail >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//##############################################################################
function tsquidguard.VERSIONNUM():integer;
var
   zversion:string;
begin
    zversion:=VERSION();
    zversion:=AnsiReplaceText(zversion,'.','');
    if length(zversion)=3 then zversion:=zversion+'0';
    TryStrToInt(zversion,result);

end;

procedure tsquidguard.BuildStatus();
begin
    fpsystem(BIN_PATH()+' -c /etc/monit/monitrc -p /var/run/monit/monit.pid status');
end;
//##############################################################################
procedure tsquidguard.CONFIG_DEFAULT();
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

if myversion<5000 then begin
  l.add('set daemon 60');

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
       l.add('set alert '+recipients.Strings[i]+' but not on { instance,action}');
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
