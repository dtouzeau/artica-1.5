unit gluster;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr,zsystem,cyrus;



  type
  tgluster=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     binpath:string;
     EnableGluster:integer;
     cyrus        :Tcyrus;
     function PID_NUM():string;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    VERSION():string;
    function    BIN_PATH():string;
    procedure   START();
    procedure   STOP();
    FUNCTION    STATUS():string;
    function    RELOAD():string;
    procedure    START_CLIENT();


END;

implementation

constructor tgluster.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableGluster:=1;
       cyrus:=Tcyrus.Create(SYS);
       if not FIleExists('/etc/artica-cluster/glusterfs-server.vol') then EnableGluster:=0;
       binpath:=SYS.LOCATE_GENERIC_BIN('glusterfsd');

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tgluster.free();
begin
    logs.Free;
end;
//##############################################################################
function tgluster.BIN_PATH():string;
begin
    exit(binpath);
end;
//##############################################################################

function tgluster.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    filetmp:string;
    debug:boolean;
begin
if not FileExists(BIN_PATH()) then exit;
   debug:=false;
   debug:=SYS.COMMANDLINE_PARAMETERS('--debug');
   result:=SYS.GET_CACHE_VERSION('APP_GLUSTER');
if length(result)>0 then begin
   if debug then writeln('GET_CACHE_VERSION ->',result);
   exit;
end;


filetmp:=logs.FILE_TEMP();
if debug then writeln('/usr/sbin/glusterfsd -V >'+filetmp+' 2>&1');
fpsystem('/usr/sbin/glusterfsd -V >'+filetmp+' 2>&1');


if not FileExists(filetmp) then exit;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='glusterfs\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
RegExpr.free;
FileDatas.Free;
SYS.SET_CACHE_VERSION('APP_GLUSTER',result);

end;
//#############################################################################
function tgluster.PID_NUM():string;
var
   pid:string;
begin
    pid:=SYS.GET_PID_FROM_PATH('/var/run/glusterfsd');
    if not SYS.PROCESS_EXIST(pid) then pid:=SYS.PIDOF_PATTERN(BIN_PATH());
    result:=pid;
end;


//#############################################################################
function tgluster.RELOAD():string;
var
   pid:string;
begin
   result:='';
   pid:=PID_NUM();
   if not SYS.PROCESS_EXIST(pid) then begin
      START();
      exit;
   end;
   logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.gluster.php --conf');
   logs.OutputCmd('/bin/kill -HUP '+pid);

end;


//#############################################################################
procedure tgluster.START();
var
   pid:string;
   ck:integer;
   cmd:string;
begin

   if not FileExists(BIN_PATH()) then begin
      logs.DebugLogs('Starting......: Gluster Not installed');
      exit;
   end;

   if not FIleExists('/etc/artica-cluster/glusterfs-server.vol') then begin
        logs.Debuglogs('Starting......: Gluster Daemon building first configuration');
        logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.gluster.php --conf');

   end;

   if not FIleExists('/etc/artica-cluster/glusterfs-server.vol') then begin
        logs.Debuglogs('Starting......: Gluster Daemon /etc/artica-cluster/glusterfs-server.vol no such file');
        logs.DebugLogs('Starting......: Gluster Daemon Not notified/configured');
        START_CLIENT();
        exit;
   end;

   pid:=PID_NUM();
   if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: Gluster Daemon Already running using PID '+pid);
      START_CLIENT();
      exit;
   end;

   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.gluster.php --conf');
   cmd:=BIN_PATH() +' -f /etc/artica-cluster/glusterfs-server.vol -l /var/log/glusterfs/glusterfs.log --pid-file=/var/run/glusterfsd';
   logs.DebugLogs('Starting......: Gluster Daemon server mode '+ cmd);
   fpsystem(cmd);

   pid:=PID_NUM();
       ck:=0;
       while not SYS.PROCESS_EXIST(pid) do begin
           pid:=PID_NUM();
           sleep(100);
           inc(ck);
           if ck>40 then begin
                logs.DebugLogs('Starting......: Gluster Daemon server timeout...');
                break;
           end;
       end;

    pid:=PID_NUM();
    if not SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: Gluster Daemon server failed...');

    end else begin
        logs.DebugLogs('Starting......: Gluster Daemon server success PID '+pid);
    end;
    START_CLIENT();




end;
//#############################################################################
procedure tgluster.STOP();
var
 pid:string;
 count:integer;
begin

  pid:=PID_NUM();
  count:=0;

   if not FileExists(BIN_PATH()) then begin
      writeln('Stopping Gluster server......: Not installed');
      exit;
   end;   


  if not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Gluster server......: Already stopped');
     exit;
  end;

      writeln('Stopping Gluster server......: Stopping PID '+pid);
     fpsystem('/bin/kill ' + pid);
     while SYS.PROCESS_EXIST(pid) do begin
           Inc(count);
           sleep(100);
           if count>50 then begin
              writeln('Stopping Gluster server......: ' + pid + ' PID (timeout)');
              fpsystem('/bin/kill -9 ' + pid);
              break;
           end;
           pid:=PID_NUM();
     end;
     pid:=PID_NUM();
     if SYS.PROCESS_EXIST(pid) then begin
           writeln('Stopping Gluster server......: ' + pid + '  failed already exists PID '+ pid);
           exit;
     end;

       writeln('Stopping Gluster server......: success');




end;
//#############################################################################

procedure tgluster.START_CLIENT();
begin

  fpsystem(SYS.LOCATE_PHP5_BIN() +' /usr/share/artica-postfix/exec.gluster.php --mount');

end;
//#############################################################################
FUNCTION tgluster.STATUS():string;
var
   pidpath:string;
begin

 SYS.MONIT_DELETE('APP_GLUSTER');
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --gluster >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//#########################################################################################

end.
