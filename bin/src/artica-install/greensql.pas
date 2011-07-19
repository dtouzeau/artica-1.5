unit greensql;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tgreensql=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableGreenSQL:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
   procedure    RELOAD();


END;

implementation

constructor tgreensql.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableGreenSQL'),EnableGreenSQL) then EnableGreenSQL:=1;

end;
//##############################################################################
procedure tgreensql.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tgreensql.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping GreenSQL............: Not installed');
   exit;
end;



if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping GreenSQL............: Already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping GreenSQL............:  ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping GreenSQL............: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  count:=0;
  if not SYS.PROCESS_EXIST(PID_NUM()) then  writeln('Stopping greensql............: success');
end;

//##############################################################################
function tgreensql.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('greensql-fw');
end;
//##############################################################################
procedure tgreensql.RELOAD();
var
   pid,cmd:string;
begin
pid:=PID_NUM();

if SYS.PROCESS_EXIST(pid) then begin
   cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.greensql.php --build';
   fpsystem(cmd);
   logs.DebugLogs('Starting......:  GreenSQL reload PID ' +pid+ '...');
   fpsystem('/bin/kill -HUP '+ pid);
   exit;
end;
   START();

end;



procedure tgreensql.START();
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
         logs.DebugLogs('Starting......: GreenSQL is not installed');
         exit;
   end;


if EnableGreenSQL=0 then begin
   logs.DebugLogs('Starting......:  GreenSQL is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  GreenSQL Already running using PID ' +PID_NUM()+ '...');
   exit;
end;


   cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.greensql.php --build';
   fpsystem(cmd);
   count:=0;

   cmd:=binpath+' -p /etc/greensql >/dev/null 2>&1 &';
   fpsystem(cmd);

   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: GreenSQL (timeout!!!)');
       logs.DebugLogs('Starting......: GreenSQL "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: GreenSQL (failed!!!)');
       logs.DebugLogs('Starting......: GreenSQL "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: GreenSQL started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tgreensql.STATUS():string;
var
pidpath:string;
begin

   if not FileExists(binpath) then exit;
   if EnableGreenSQL=0 then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --greensql >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tgreensql.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/greensql-fw.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
end;
 //##############################################################################
end.
