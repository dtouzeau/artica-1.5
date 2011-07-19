unit ddclient;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tddclient=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableDDClient:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
    function    VERSION():string;
   procedure    RELOAD();


END;

implementation

constructor tddclient.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableDDClient'),EnableDDClient) then EnableDDClient:=0;

end;
//##############################################################################
procedure tddclient.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tddclient.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping DDClient............: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
   writeln('Stopping DDClient............: Already Stopped');
   exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping DDClient............: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping DDClient............: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then  writeln('Stopping DDClient............: success');
end;

//##############################################################################
function tddclient.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('ddclient');
if not FileExists(result) then result:='/usr/share/artica-postfix/bin/ddclient';
end;
//##############################################################################
procedure tddclient.RELOAD();
var
   pid:string;
begin
pid:=PID_NUM();

if SYS.PROCESS_EXIST(pid) then begin
   logs.DebugLogs('Starting......:  DDCLient reload PID ' +pid+ '...');
   fpsystem('/bin/kill -HUP '+ pid);
   exit;
end;
   START();

end;
//##############################################################################
procedure tddclient.START();
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
         logs.DebugLogs('Starting......: DDClient is not installed');
         exit;
   end;

if EnableDDClient=0 then begin
   logs.DebugLogs('Starting......: DDClient is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......: DDClient Already running using PID ' +PID_NUM()+ '...');
   exit;
end;


   cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.ddclient.php --build';
   fpsystem(cmd);
   forceDirectories('/var/cache/ddclient');
   forceDirectories('/etc/ddclient');

   cmd:=binpath +' -cache /var/cache/ddclient/ddclient.cache -pid /var/run/ddclient.pid -daemon 600 -file /etc/ddclient/ddclient.conf -ssl -syslog';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: DDClient (timeout!!!)');
       logs.DebugLogs('Starting......: DDClient "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: DDClient (failed!!!)');
       logs.DebugLogs('Starting......: DDClient "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: DDClient started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tddclient.STATUS():string;
var
pidpath:string;
begin

   if not FileExists(binpath) then exit;
   if EnableDDClient=0 then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --ddclient >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tddclient.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/ddclient.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
end;
 //##############################################################################
  function tddclient.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_DDCLIENT');
     if length(result)>2 then exit;
     if not FileExists(binpath) then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath +' --help >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='ddclient version\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_DDCLIENT',result);
l.free;
RegExpr.free;
end;
//##############################################################################
end.
