unit snort;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tsnort=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableSnort:integer;
     binpath:string;
     D:boolean;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;



END;

implementation

constructor tsnort.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       D:=SYS.verbosed;
       if not TryStrToInt(SYS.GET_INFO('EnableSnort'),EnableSnort) then EnableSnort:=0;
end;
//##############################################################################
procedure tsnort.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tsnort.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Snort Daemon........: Not installed');
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   writeln('Stopping Snort Daemon........: ' + PID_NUM() + ' PID..');
   fpsystem('/bin/kill ' + PID_NUM());
      count:=0;
     while SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(PID_NUM())>0 then begin
               if SYS.PROCESS_EXIST(PID_NUM()) then begin
                  writeln('Stopping Snort Daemon........: kill pid '+ PID_NUM()+' after timeout');
                  fpsystem('/bin/kill -9 ' + PID_NUM());
               end;
            end;
            break;
        end;
  end;


end else begin
   writeln('Stopping Crossroads Daemon...: Already stopped');
end;




end;


//##############################################################################
function tsnort.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('snort');
end;
//##############################################################################
procedure tsnort.START();
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
         logs.DebugLogs('Starting......: Snort Daemon is not installed');
         exit;
   end;

if EnableSnort=0 then begin
   logs.DebugLogs('Starting......: Snort Daemon service is disabled');
   STOP();
   exit;
end;

   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.snort.php --build');
   if FileExists('/etc/snort/db-pending-config') then logs.DeleteFile('/etc/snort/db-pending-config');


   logs.DebugLogs('Starting......: Crossroads Daemon writing settings');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.crossroads.php --build');
   if not FileExists('/etc/artica-postfix/croassroads.cmdline') then begin
         logs.DebugLogs('Starting......: Crossroads Daemon waiting better settings..aborting');
         exit;
   end;


   logs.DebugLogs('Starting......: Crossroads Daemon....');
   cmd:=binpath+' '+trim(trim(logs.ReadFromFile('/etc/artica-postfix/croassroads.cmdline'))+' -v -W 127.0.0.1:18501 >/var/log/crossroads.log 2>&1 &');
   fpsystem(cmd);
   count:=0;

   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(100);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Crossroads Daemon. (timeout!!!)');
       logs.DebugLogs('Starting......: Crossroads Daemon "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: Crossroads Daemon. (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: Crossroads Daemon. PID '+PID_NUM());
   end;



end;
//##############################################################################
function tsnort.STATUS():string;
var
pidpath:string;
begin
   if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --crossroads >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tsnort.PID_NUM():string;
begin
 result:=SYS.PIDOF_PATTERN(binpath+'.+?127.0.0.1:18501');
end;
 //##############################################################################
 function tsnort.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_CROSSROADS');
    if length(result)>3 then exit;

    tmpstr:=logs.FILE_TEMP();
    if D then writeln(binpath +' --version >'+tmpstr +' 2>&1');
    fpsystem(binpath +' --version >'+tmpstr +' 2>&1');

    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='XR version\s+:\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            if D then writeln(result,' ->OK ',l.Strings[i]);
            break;
         end;
    end;


 SYS.SET_CACHE_VERSION('APP_CROSSROADS',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
