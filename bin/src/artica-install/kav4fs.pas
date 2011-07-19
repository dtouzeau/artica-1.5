unit kav4fs;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tkav4fs=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableKav4FS:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    SUPERVISOR_PID():string;
     function   VERSION():string;
     function BIN_PATH():string;
END;

implementation

constructor tkav4fs.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableKav4FS'),EnableKav4FS) then EnableKav4FS:=1;

end;
//##############################################################################
procedure tkav4fs.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tkav4fs.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   servername:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping kav4fs-supervisor...: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(SUPERVISOR_PID()) then begin
       writeln('Stopping kav4fs-supervisor...: already Stopped');
        exit;
end;




   writeln('Stopping kav4fs-supervisor...: ' + SUPERVISOR_PID() + ' PID..');
   fpsystem('/etc/init.d/kav4fs-supervisor stop');
   pidstring:=SUPERVISOR_PID();
   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping kav4fs-supervisor...: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
  end;



   pids:=Tstringlist.Create;
   pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(binpath));
   if pids.Count>1 then begin
   for i:=0 to pids.Count-1 do begin
       if TryStrToInt(pids.Strings[i],fpid) then begin
          if fpid>1 then begin
             writeln('Stopping kav4fs-supervisor...: kill pid ',fpid);
             fpsystem('/bin/kill -9 '+ IntTOStr(fpid));
          end;
       end;
   end;
   end;

  if not SYS.PROCESS_EXIST(SUPERVISOR_PID()) then writeln('Stopping kav4fs-supervisor...: Stopped');
end;

 //##############################################################################

function tkav4fs.BIN_PATH():string;
begin
if FileExists('/opt/kaspersky/kav4fs/sbin/kav4fs-supervisor') then exit('/opt/kaspersky/kav4fs/sbin/kav4fs-supervisor');
end;
procedure tkav4fs.START();
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
         logs.DebugLogs('Starting......: kav4fs-supervisor not installed');
         exit;
   end;

if EnableKav4FS=0 then begin
   logs.DebugLogs('Starting......:  kav4fs-supervisor is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(SUPERVISOR_PID()) then begin
   logs.DebugLogs('Starting......:  kav4fs-supervisor Already running using PID ' +SUPERVISOR_PID()+ '...');
   exit;
end;


   cmd:='/etc/init.d/kav4fs-supervisor start &';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(SUPERVISOR_PID()) do begin
     sleep(150);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: kav4fs-supervisor (timeout!!!)');
       logs.DebugLogs('Starting......: kav4fs-supervisor "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(SUPERVISOR_PID()) then begin
       logs.DebugLogs('Starting......: kav4fs-supervisor (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: kav4fs-supervisor started with new PID '+SUPERVISOR_PID());
   end;

end;
//##############################################################################
function tkav4fs.STATUS():string;
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --kav4fs >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tkav4fs.SUPERVISOR_PID():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/kav4fs/supervisor.pid');
  logs.Debuglogs('/var/run/kav4fs/supervisor.pid ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
 function tkav4fs.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_KAV4FS');
     if length(result)>2 then exit;
     if not FileExists('/opt/kaspersky/kav4fs/bin/kav4fs-control') then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem('/opt/kaspersky/kav4fs/bin/kav4fs-control --app-info >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Version:\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_KAV4FS',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
