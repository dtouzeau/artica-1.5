unit articapolicy;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tarticapolicy=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableArticaPolicyFilter:integer;
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

constructor tarticapolicy.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableArticaPolicyFilter'),EnableArticaPolicyFilter) then EnableArticaPolicyFilter:=0;

end;
//##############################################################################
procedure tarticapolicy.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tarticapolicy.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Artica-policy.......: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
      writeln('Stopping Artica-policy.......: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping Artica-policy.......: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                     writeln('Stopping Artica-policy.......: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  count:=0;
  pids:=Tstringlist.Create;
  pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(SYS.LOCATE_PHP5_BIN()+'\s+/usr/share/artica-postfix/exec.artica-filter-daemon.php'));
  writeln('Stopping Artica-policy.......: ',pids.Count,' childrens.');
  for i:=0 to pids.Count-1 do begin
        if not TryStrToInt(pids.Strings[i],fpid) then continue;
        if fpid>2 then begin
              writeln('Stopping Artica-policy.......: kill pid ',fpid);
              fpsystem('/bin/kill -9 '+ IntToStr(fpid));
        end;
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then    writeln('Stopping Artica-policy.......: success');
end;

//##############################################################################
function tarticapolicy.BIN_PATH():string;
begin
result:='/usr/share/artica-postfix/exec.artica-filter-daemon.php';
end;
//##############################################################################
procedure tarticapolicy.RELOAD();
var
   pid:string;
begin
pid:=PID_NUM();

if SYS.PROCESS_EXIST(pid) then begin
   logs.DebugLogs('Starting......:  Artica-policy reload PID ' +pid+ '...');
   fpsystem('/bin/kill -HUP '+ pid);
   exit;
end;
   START();

end;



procedure tarticapolicy.START();
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
         logs.DebugLogs('Starting......: Artica-policy is not installed');
         exit;
   end;

if EnableArticaPolicyFilter=0 then begin
   logs.DebugLogs('Starting......:  Artica-policy is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  Artica-policy Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   forceDirectories('/var/log/artica-filter');
   cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.artica-filter-daemon.php';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Artica-policy (timeout!!!)');
       logs.DebugLogs('Starting......: Artica-policy "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: Artica-policy (failed!!!)');
       logs.DebugLogs('Starting......: Artica-policy "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: Artica-policy started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tarticapolicy.STATUS():string;
var
pidpath:string;
begin

   if not FileExists(binpath) then exit;
   if EnableArticaPolicyFilter=0 then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --artica-policy >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tarticapolicy.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/exec.artica-filter-daemon.php.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
end;
 //##############################################################################
end.
