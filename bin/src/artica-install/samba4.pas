unit samba4;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,logs,unix,RegExpr in 'RegExpr.pas',zsystem;



  type
  tsamba4=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     function PID_NUM():string;


public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
//    function    STATUS():string;
    function    BIN_PATH():string;
    function    VERSION():string;


END;

implementation

constructor tsamba4.Create(const zSYS:Tsystem);
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
procedure tsamba4.free();
begin
    logs.Free;

end;
//##############################################################################
function tsamba4.BIN_PATH():string;
begin
     if FileExists('/usr/sbin/samba') then exit('/usr/sbin/samba');
end;
//##############################################################################
function tsamba4.PID_NUM():string;
begin
     result:=SYS.PIDOF(BIN_PATH());
end;
//##############################################################################

procedure tsamba4.START();
var
   pid:string;
begin
  pid:=PID_NUM();
   if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: samba4 is already running using PID ' + pid + '...');
      exit;
   end;


end;

//##############################################################################
procedure tsamba4.STOP();
var
   pid:string;
   count:integer;
begin
pid:=PID_NUM();
count:=0;
if SYS.PROCESS_EXIST(PID_NUM()) then begin
   writeln('Stopping Samba4.......................: ' + pid + ' PID..');
   fpsystem('/bin/kill ' + pid);
end;
  while SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(100);
        count:=count+1;
        if count>20 then begin
            fpsystem('/bin/kill -9 ' + PID_NUM());
            break;
        end;
  end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.Syslogs('Stopping Samba4.......................: Success');
end;



end;
//#########################################################################################
function tsamba4.VERSION():string;
begin
   result:='';
   if not FileExists(BIN_PATH) then exit;
 //  l:=SYS.
end;
//#############################################################################




end.
