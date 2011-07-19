unit vnstat;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tvnstat=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableVnStat:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    PID_NUM():string;
    function    BIN_PATH():string;

END;

implementation

constructor tvnstat.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableVnStat'),EnableVnStat) then EnableVnStat:=1;
end;
//##############################################################################
procedure tvnstat.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tvnstat.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping VnStat..............: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
      writeln('Stopping VnStat..............: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping VnStat..............: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping VnStat..............: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then  writeln('Stopping VnStat..............: Stopped');
end;

//##############################################################################
function tvnstat.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('vnstatd');
end;
//##############################################################################
procedure tvnstat.START();
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
         logs.DebugLogs('Starting......: VnStat not installed');
         exit;
   end;



if EnableVnStat=0 then begin
   logs.DebugLogs('Starting......:  VnStat is disabled (key:EnableVnStat)');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  VnStat Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   forceDirectories('/var/lib/vnstat');
   logs.DebugLogs('Starting......: VnStat building configuration');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.vnstat.php --build');
   cmd:=binpath+' --daemon --pidfile /var/run/vnstat.pid --config /etc/vnstat.conf';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: VnStat (timeout!!!)');
       logs.DebugLogs('Starting......: VnStat "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: VnStat (failed!!!)');
       logs.DebugLogs('Starting......: VnStat "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: VnStat started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tvnstat.STATUS():string;
var
pidpath:string;
begin
    if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --vnstat >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
function tvnstat.PID_NUM():string;
begin
     result:=SYS.GET_PID_FROM_PATH('/var/run/vnstat.pid');
     if length(result)=0 then exit(SYS.PIDOF_PATTERN(binpath));
end;
 //##############################################################################

end.
