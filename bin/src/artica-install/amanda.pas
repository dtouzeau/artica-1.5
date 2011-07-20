unit amanda;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tamanda=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    BIN_PATH():string;
    function    PID_NUM():string;
   procedure    RELOAD();


END;

implementation

constructor tamanda.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();


end;
//##############################################################################
procedure tamanda.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tamanda.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Amanda server.......: Not installed');
   exit;
end;

writeln('Stopping Amanda server.......: xinetd -> not applicable');
exit;
end;
//##############################################################################
function tamanda.BIN_PATH():string;
begin
if FileExists('/usr/lib/amanda/amandad') then exit('/usr/lib/amanda/amandad');

end;
//##############################################################################
procedure tamanda.RELOAD();
var
   pid:string;
begin
pid:=PID_NUM();

if SYS.PROCESS_EXIST(pid) then begin
   logs.DebugLogs('Starting......:  Artica-status reload PID ' +pid+ '...');
   fpsystem('/bin/kill -HUP '+ pid);
   exit;
end;
   START();

end;



procedure tamanda.START();
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
         logs.DebugLogs('Starting......: Amanda server is not installed');
         exit;
   end;


   cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.amanda.php --build';
   fpsystem(cmd);

end;
//##############################################################################
 function tamanda.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/exec.status.php.pid');
  if sys.verbosed then logs.Debuglogs('PID_NUM():: exec.status.php.pid  ->'+result);
end;
 //##############################################################################
end.
