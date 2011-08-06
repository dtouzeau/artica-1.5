unit sshd;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tsshd=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableSSHD:integer;
     binpath:string;
     TAIL_STARTUP:string;
    function    TAIL_PID():string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    PID_NUM():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_PATH():string;
    function    INITD_PATH():string;
    procedure   START_LOGGER();
    procedure   STOP_LOGGER();

END;

implementation

constructor tsshd.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       TAIL_STARTUP:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.auth-tail.php';
       if not TryStrToInt(SYS.GET_INFO('EnableSSHD'),EnableSSHD) then EnableSSHD:=1;

end;
//##############################################################################
procedure tsshd.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tsshd.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping OpenSSH.............: Not installed');
   exit;
end;
 writeln('Stopping OpenSSH.............: apply permissions on /root');
fpsystem('/bin/chown -R root:root /root');
fpsystem('/bin/chmod go-w /root');
if not DirectoryExists('/root/.ssh') then ForceDirectories('/root/.ssh');
fpsystem('/bin/chmod 700 /root/.ssh');
if FIleExists('/root/.ssh/authorized_keys') then fpsystem('/bin/chmod 600 /root/.ssh/authorized_keys');

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping OpenSSH.............: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping OpenSSH.............: ' + pidstring + ' PID..');
   cmd:=INITD_PATH()+' stop';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping OpenSSH.............: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then writeln('Stopping OpenSSH.............: Stopped');
end;

 //##############################################################################

function tsshd.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('sshd');
end;
procedure tsshd.START();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   http_port:integer;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: OpenSSH not installed');
         exit;
   end;

if EnableSSHD=0 then begin
   logs.DebugLogs('Starting......:  OpenSSH is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  OpenSSH Already running using PID ' +PID_NUM()+ '...');
   START_LOGGER();
   exit;
end;
logs.DebugLogs('Starting......: OpenSSH server apply permissions on /root');
fpsystem('/bin/chown -R root:root /root');
fpsystem('/bin/chmod go-w /root');

if not DirectoryExists('/root/.ssh') then ForceDirectories('/root/.ssh');
logs.DebugLogs('Starting......: OpenSSH server apply 700 permissions on /root/.ssh');
fpsystem('/bin/chmod 700 /root/.ssh');

if FIleExists('/root/.ssh/authorized_keys') then begin
   logs.DebugLogs('Starting......: OpenSSH server apply 600 permissions on /root/.ssh/authorized_keys');
   fpsystem('/bin/chmod 600 /root/.ssh/authorized_keys');
end;



   logs.DebugLogs('Starting......: OpenSSH server...');
   cmd:=INITD_PATH()+' start &';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: OpenSSH (timeout!!!)');
       logs.DebugLogs('Starting......: OpenSSH "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: OpenSSH (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: OpenSSH started with new PID '+PID_NUM());
       START_LOGGER();
   end;

end;
//##############################################################################
procedure tsshd.START_LOGGER();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   authlog:string;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: OpenSSH not installed');
         exit;
   end;



if EnableSSHD=0 then begin
   logs.DebugLogs('Starting......:  OpenSSH is disabled');
   STOP();
   exit;
end;




if SYS.PROCESS_EXIST(TAIL_PID()) then begin
   logs.DebugLogs('Starting......:  OpenSSH watchdog Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   authlog:=SYS.LOCATE_AUTHLOG_PATH();
   if not FileExists(authlog) then begin
      logs.DebugLogs('Starting......: OpenSSH watchdog server unable to stat auth.log');
      exit;
   end;
   logs.DebugLogs('Starting......: OpenSSH watchdog server watch "'+authlog+'"...');
   cmd:='/usr/bin/tail -f -n 0 '+authlog+'|'+TAIL_STARTUP+' >>/var/log/artica-postfix/auth-logger-start.log 2>&1 &';
   logs.DebugLogs('Starting......: OpenSSH watchdog server...');

   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(TAIL_PID()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: OpenSSH watchdog (timeout!!!)');
       logs.DebugLogs('Starting......: OpenSSH watchdog "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(TAIL_PID()) then begin
       logs.DebugLogs('Starting......: OpenSSH watchdog (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: OpenSSH watchdog started with new PID '+TAIL_PID());
   end;

end;
//##############################################################################
procedure tsshd.STOP_LOGGER();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   CountTail:Tstringlist;
   pidstring,pid:string;
   fpid,i,pidint:integer;
   authlog:string;
begin
if not FileExists(binpath) then begin
   writeln('Stopping OpenSSH watchdog....: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(TAIL_PID()) then begin
   writeln('Stopping OpenSSH watchdog....: Already Stopped');
   exit;
end;
   pidstring:=TAIL_PID();
   writeln('Stopping OpenSSH watchdog....: ' + pidstring + ' PID..');
   cmd:='/bin/kill '+pidstring;
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping OpenSSH watchdog....: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=TAIL_PID();
  end;


CountTail:=Tstringlist.Create;
authlog:=SYS.LOCATE_AUTHLOG_PATH();
CountTail.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST('/usr/bin/tail -f -n 0 '+authlog));
writeln('Stopping OpenSSH watchdog....: Tail processe(s) number '+IntToStr(CountTail.Count));

     count:=0;
     for i:=0 to CountTail.Count-1 do begin;
          pid:=CountTail.Strings[i];
          if count>100 then break;
          if not TryStrToInt(pid,pidint) then continue;
          writeln('Stopping OpenSSH watchdog....: Stop tail pid '+pid);
          if pidint>0 then  fpsystem('/bin/kill '+pid);
          sleep(100);
          inc(count);
      end;

   if not SYS.PROCESS_EXIST(TAIL_PID()) then  writeln('Stopping OpenSSH watchdog....: Stopped');
end;

 //##############################################################################

function tsshd.TAIL_PID():string;
var
   pid:string;
begin

if FileExists('/etc/artica-postfix/exec.auth-tail.php.pid') then begin
   pid:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/exec.auth-tail.php.pid');
   logs.Debuglogs('TAIL_PID /etc/artica-postfix/exec.auth-tail.php.pid='+pid);
   if SYS.PROCESS_EXIST(pid) then result:=pid;
   exit;
end;


result:=SYS.PIDOF_PATTERN(TAIL_STARTUP);
logs.Debuglogs(TAIL_STARTUP+' pid='+pid);
end;
//#####################################################################################

function tsshd.STATUS():string;
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --openssh >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tsshd.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH(PID_PATH());
  logs.Debuglogs(' ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
function tsshd.PID_PATH():string;
begin
     if FileExists('/var/run/sshd.pid') then exit('/var/run/sshd.pid');
     if FileExists('/var/run/sshd.init.pid') then exit('/var/run/sshd.init.pid');
end;
 //##############################################################################
function tsshd.INITD_PATH():string;
begin
     if FileExists('/etc/init.d/sshd') then exit('/etc/init.d/sshd');
     if FileExists('/etc/init.d/ssh') then exit('/etc/init.d/ssh');
end;
 //##############################################################################


 function tsshd.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_OPENSSH');
     if length(result)>2 then exit;
     if not FileExists(binpath) then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath +' -h >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='OpenSSH_([0-9a-z\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_OPENSSH',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
