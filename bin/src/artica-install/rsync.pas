unit rsync;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,
    RegExpr      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';



  type
  trsync=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     verbose:boolean;
     RsyncDaemonEnable:integer;
     RsyncEnableStunnel:integer;
     function RSYNC_PID():string;
     function WRITECONFIG():string;
     function RSYNC_STUNNEL_PID():string;
     procedure SSL_STOP();

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    VERSION():string;
    procedure   START();
    procedure   RELOAD();
    procedure   STOP();
    function    BIN_PATH():string;
    procedure   STUNNEL_START();
    function    STATUS_STUNNEL():string;
    function    STATUS():string;
END;

implementation

constructor trsync.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       verbose:=SYS.COMMANDLINE_PARAMETERS('--verbose');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if not TryStrToInt(SYS.GET_INFO('RsyncEnableStunnel'),RsyncEnableStunnel) then RsyncEnableStunnel:=0;
       if not TrystrtoInt(SYS.GET_INFO('RsyncDaemonEnable'),RsyncDaemonEnable) then RsyncDaemonEnable:=0;
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure trsync.free();
begin
    logs.Free;

end;
//##############################################################################
function trsync.BIN_PATH():string;
begin
   if FileExists('/usr/bin/rsync') then exit('/usr/bin/rsync');

end;
//##############################################################################

function trsync.RSYNC_PID():string;
var
   pid_path:string;
   pid:string;
begin

    pid_path:='/var/run/rsync/rsyncd.pid';

    pid:=SYS.GET_PID_FROM_PATH(pid_path);

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('RSYNC_PID: '+pid+' failed');
      result:=SYS.PIDOF_PATTERN(BIN_PATH()+' --daemon');
      if verbose then logs.Debuglogs('RSYNC_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;


end;
//##############################################################################
function trsync.RSYNC_STUNNEL_PID():string;
var
   pid_path:string;
   pid:string;
begin

    pid_path:='/var/run/rsync/stunnel.pid';

    pid:=SYS.GET_PID_FROM_PATH(pid_path);

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('RSYNC_STUNNEL_PID: '+pid+' failed');
      result:=SYS.PIDOF_PATTERN(SYS.LOCATE_STUNNEL()+' /etc/rsync/stunnel.conf');
      if verbose then logs.Debuglogs('RSYNC_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;


end;
//##############################################################################


function trsync.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_RSYNC');
if length(result)>0 then exit;

filetmp:=logs.FILE_TEMP();
if not FileExists(BIN_PATH()) then exit;
fpsystem(BIN_PATH()+' --version >'+filetmp+' 2>&1');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='version\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    if Not FileExists(filetmp) then exit;
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
             SYS.SET_CACHE_VERSION('APP_RSYNC',result);

end;
//#############################################################################
function trsync.WRITECONFIG():string;

var l:TstringList;
var stunnelT:Tstringlist;
var RsyncBwlimit:integer;
var RsyncPort,RsyncMaxConnections:integer;

var RsyncStoragePath:string;
var cmd:string;
var Stun:string;
var RsyncEnableStunnelPort:integer;

begin
Stun:='';
RsyncStoragePath:=trim(SYS.GET_INFO('RsyncStoragePath'));
 if not TryStrToInt(SYS.GET_INFO('RsyncBwlimit'),RsyncBwlimit) then RsyncBwlimit:=1000;
 if not TryStrToInt(SYS.GET_INFO('RsyncPort'),RsyncPort) then RsyncPort:=873;
 if not TryStrToInt(SYS.GET_INFO('RsyncEnableStunnelPort'),RsyncEnableStunnelPort) then RsyncEnableStunnelPort:=0;

 if length(RsyncStoragePath)=0 then RsyncStoragePath:='/var/spool/rsync';


logs.Debuglogs('Starting......: storage path:"'+RsyncStoragePath+'"');
forceDirectories(RsyncStoragePath);
forceDirectories('/etc/rsync');
ForceDirectories('/var/run/rsync');
forceDirectories('/var/log/rsync');


if(RsyncEnableStunnel=1) then begin
   if RsyncEnableStunnelPort>0 then begin
    Stun:=' --address=127.0.0.1 ';
    stunnelT:=Tstringlist.Create;
    stunnelT.Add('cert = /etc/rsync/lighttpd.pem');
    stunnelT.Add('client = no');
    stunnelT.Add('pid = /var/run/rsync/stunnel.pid');
    stunnelT.Add('foreground = no');
    stunnelT.Add('output = /var/log/rsync/stunnel.log');
    stunnelT.Add('[rsync]');
    stunnelT.Add('accept = '+intToStr(RsyncEnableStunnelPort));
    stunnelT.Add('connect = '+intToStr(RsyncPort));

    logs.OutputCmd('/bin/cp /opt/artica/ssl/certs/lighttpd.pem /etc/rsync/lighttpd.pem');
    logs.OutputCmd('/bin/chmod 600 /etc/rsync/lighttpd.pem');


    logs.WriteToFile(stunnelT.Text, '/etc/rsync/stunnel.conf');
  end;
end;


cmd:=BIN_PATH()+' -vvv --daemon '+Stun+' --bwlimit='+ intToStr(RsyncBwlimit)+' --port='+intToStr(RsyncPort) +' --config=/etc/rsync/rsyncd.conf --log-file=/var/log/rsync/rsyncd.log';


result:=cmd;
end;
//#############################################################################
procedure trsync.RELOAD();
var
   cmd:string;
   pid:string;
   count:integer;
begin
pid:=RSYNC_PID();
cmd:=WRITECONFIG();
if not  SYS.PROCESS_EXIST(pid) then begin
   START();
   exit;
end;

if RsyncDaemonEnable=0 then begin
   STOP();
   exit;
end;

logs.OutputCmd(SYS.EXEC_NICE()+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.rsyncd.conf.php --no-reboot');
fpsystem('/bin/kill -HUP '+pid);
sleep(1000);
START();



end;

//#############################################################################
function trsync.STATUS():string;
var ini:TstringList;
pid:string;
pid_path:string;
begin
ini:=TstringList.Create;
pid_path:='/var/run/rsync/rsyncd.pid';
if not FileExists(BIN_PATH()) then exit;

   ini.Add('[APP_RSYNC]');
   ini.Add('master_version=' + VERSION());
   ini.Add('service_name=APP_RSYNC');
   ini.Add('service_cmd=rsync');
   ini.Add('service_disabled='+ IntToStr(RsyncDaemonEnable));

   if RsyncDaemonEnable=0 then begin
        result:=ini.Text;
        ini.free;
        SYS.MONIT_DELETE('APP_RSYNC');
        exit;
   end;

   if SYS.MONIT_CONFIG('APP_RSYNC',pid_path,'rsync') then begin
        result:=ini.Text;
        ini.free;
        exit;
   end;

   pid:=RSYNC_PID();
   if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ pid);
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
   ini.Add('status='+SYS.PROCESS_STATUS(pid));
   ini.add('');


   result:=ini.Text;
   ini.free;

end;
//#########################################################################################
function trsync.STATUS_STUNNEL():string;
var ini:TstringList;
pid:string;
pid_path:string;
begin
ini:=TstringList.Create;
pid_path:='/var/run/rsync/rsyncd.pid';
if not FileExists(BIN_PATH()) then exit;

   ini.Add('[APP_RSYNC_STUNNEL]');
   ini.Add('master_version=' + VERSION());
   ini.Add('service_name=APP_RSYNC_STUNNEL');
   ini.Add('service_cmd=rsync');
   ini.Add('service_disabled='+ IntToStr(RsyncEnableStunnel));

   if RsyncEnableStunnel=0 then begin
        result:=ini.Text;
        ini.free;
        SYS.MONIT_DELETE('APP_RSYNC_STUNNEL');
        exit;
   end;

   if SYS.MONIT_CONFIG('APP_RSYNC_STUNNEL','/var/run/rsync/stunnel.pid','rsync') then begin
        result:=ini.Text;
        ini.free;
        exit;
   end;


   pid:=RSYNC_STUNNEL_PID();
   if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ pid);
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
   ini.Add('status='+SYS.PROCESS_STATUS(pid));
   result:=ini.Text;
   ini.free;

end;
//#########################################################################################



procedure trsync.STUNNEL_START();
var
   cmd:string;
   pid:string;
   count:integer;
begin


if not FileExists(BIN_PATH()) then begin
   logs.Debuglogs('Starting......: Rsync is not installed');
   exit;
end;

if RsyncEnableStunnel=0 then begin
   logs.Debuglogs('Starting......: Rsync SSL is not enabled');
   exit;
end;

pid:=RSYNC_STUNNEL_PID();
if SYS.PROCESS_EXIST(pid) then begin
    logs.Debuglogs('Starting......: Rsync SSL is already running pid '+pid);
    exit;
end;

logs.Debuglogs('Starting......: Rsync SSL Deamon');
cmd:=SYS.LOCATE_STUNNEL()+' /etc/rsync/stunnel.conf';

logs.OutputCmd(cmd);
pid:=RSYNC_STUNNEL_PID();
count:=0;
  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>30 then begin
                 logs.DebugLogs('Starting......: Rsync stunnel daemon (timeout!!!)');
                 break;
              end;

              pid:=RSYNC_STUNNEL_PID();
        end;


pid:=RSYNC_STUNNEL_PID();

    if not SYS.PROCESS_EXIST(pid) then begin

         logs.DebugLogs('Starting......: Rsync SSL daemon (failed!!!)');
    end else begin

         logs.DebugLogs('Starting......: Rsync SSL daemon Success with new PID '+pid);
    end;
end;
//#############################################################################



procedure trsync.START();
var
   cmd:string;
   pid:string;
   count:integer;
begin


if not FileExists(BIN_PATH()) then begin
   logs.Debuglogs('Starting......: Rsync is not installed');
   exit;
end;

if RsyncDaemonEnable=0 then begin
   logs.Debuglogs('Starting......: Rsync is not enabled');
   STOP();
   exit;
end;

pid:=RSYNC_PID();
if SYS.PROCESS_EXIST(pid) then begin
    logs.Debuglogs('Starting......: Rsync is already running pid '+pid);
    STUNNEL_START();
    exit;
end;

logs.Debuglogs('Starting......: Rsync Deamon');
logs.OutputCmd(SYS.EXEC_NICE()+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.rsyncd.conf.php --no-reboot');
cmd:=WRITECONFIG();
STUNNEL_START();


logs.OutputCmd(cmd);
pid:=RSYNC_PID();
count:=0;
  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>30 then begin

                 logs.DebugLogs('Starting......: Rsync Storage daemon (timeout!!!)');
                 break;
              end;

              pid:=RSYNC_PID();
        end;


pid:=RSYNC_PID();

    if not SYS.PROCESS_EXIST(pid) then begin

         logs.DebugLogs('Starting......: Rsync daemon (failed!!!)');
    end else begin

         logs.DebugLogs('Starting......: Rsync daemon Success with new PID '+pid);
    end;
end;
//#############################################################################
procedure trsync.STOP();
var
   pid:string;
   cmd:string;
   count:integer;
begin

if not FileExists(BIN_PATH()) then begin
   writeln('Stopping rsync...........: Not Installed');
   exit;
end;
pid:=RSYNC_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Rsync...........: Daemon PID '+pid);
   logs.OutputCmd('/bin/kill ' + pid);
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);

      inc(count);
       if count>50 then begin
            writeln('Stopping Rsync...........: Timeout while force stopping Daemon pid:'+pid);
            break;
       end;
       pid:=RSYNC_PID();
   end;
end else begin
   writeln('Stopping Rsync...........: Daemon Already stopped');
   SSL_STOP();
   exit;
end;

SSL_STOP();
pid:=RSYNC_PID();



end;
//#############################################################################
procedure trsync.SSL_STOP();
var
   pid:string;
   cmd:string;
   count:integer;
begin

if not FileExists(BIN_PATH()) then begin
   writeln('Stopping rsync...........: Not Installed');
   exit;
end;
pid:=RSYNC_STUNNEL_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Rsync SSL.......: Daemon PID '+pid);
   logs.OutputCmd('/bin/kill ' + pid);
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);

      inc(count);
       if count>50 then begin
          writeln('Stopping Rsync SSL.......: Timeout while force stopping Daemon pid:'+pid);
            break;
       end;
       pid:=RSYNC_STUNNEL_PID();
   end;
end else begin
   writeln('Stopping Rsync SSL.......: Daemon Already stopped');
end;

pid:=RSYNC_STUNNEL_PID();



end;
//#############################################################################




end.
