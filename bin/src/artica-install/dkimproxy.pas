unit dkimproxy;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tdkimproxy=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableDkimProxy:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_NUM_IN():string;
    function    PID_NUM_OUT():string;


END;

implementation

constructor tdkimproxy.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableDkimProxy'),EnableDkimProxy) then EnableDkimProxy:=0;

end;
//##############################################################################
procedure tdkimproxy.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tdkimproxy.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping opendkim............: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping opendkim............: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping opendkim............: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping opendkim............: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then writeln('Stopping dkfilter............: Stopped');
end;

//##############################################################################
function tdkimproxy.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('dkimproxy.in');
end;
//##############################################################################
procedure tdkimproxy.START_IN();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   cmdline:string;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: dkimproxy is not installed');
         exit;
   end;

if EnableDkimProxy=0 then begin
   logs.DebugLogs('Starting......:  dkimproxy is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM_IN()) then begin
   logs.DebugLogs('Starting......:  dkimproxy [IN] Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   forceDirectories('/var/run/dkimproxy');
   fpsystem('/bin/chown -R postfix:postfix /var/run/dkimproxy');
//   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dkimproxy.php --build');
   logs.DebugLogs('Starting......: dkimproxy server inbound mode...');
   cmd:=binpath+' --listen=127.0.0.1:  --reject-fail --daemonize --user=postfix --group=postfix --pidfile=/var/run/dkimproxy/dkimproxy.in.pid';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM_IN()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: dkimproxy [IN] (timeout!!!)');
       logs.DebugLogs('Starting......: dkimproxy [IN] "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM_IN()) then begin
       logs.DebugLogs('Starting......: dkimproxy [IN] (failed!!!)');
       logs.DebugLogs('Starting......: dkimproxy [IN] "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: dkimproxy [IN] started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tdkimproxy.STATUS():string;
var
pidpath:string;
begin
   if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --dkimproxy >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tdkimproxy.PID_NUM_IN():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/dkimproxy/dkimproxy.in.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
 function tdkimproxy.PID_NUM_OUT():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/dkimproxy/dkimproxy.out.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(SYS.LOCATE_GENERIC_BIN('dkimproxy.out'));
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(SYS.LOCATE_GENERIC_BIN('dkimproxy.out'));
end;
 //##############################################################################
 function tdkimproxy.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
   if not FileExists(binpath) then exit;
   exit('1.3');

end;
//##############################################################################

end.
