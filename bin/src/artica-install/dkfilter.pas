unit dkfilter;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tdkfilter=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableDKFilter:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    PID_NUM():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_PATH():string;
END;

implementation

constructor tdkfilter.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableDKFilter'),EnableDKFilter) then EnableDKFilter:=0;

end;
//##############################################################################
procedure tdkfilter.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tdkfilter.STOP();
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
function tdkfilter.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('opendkim');
end;
//##############################################################################
procedure tdkfilter.START();
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
         logs.DebugLogs('Starting......: opendkim not installed');
         exit;
   end;

if EnableDKFilter=0 then begin
   logs.DebugLogs('Starting......:  opendkim is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  opendkim Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   forceDirectories('/var/run/opendkim');
   fpsystem('/bin/chown -R postfix:postfix /var/run/opendkim');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.opendkim.php --build');
   logs.DebugLogs('Starting......: opendkim server...');
   cmd:=binpath+' -p /var/run/opendkim/opendkim.sock -x /etc/opendkim.conf -u postfix';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: opendkim (timeout!!!)');
       logs.DebugLogs('Starting......: opendkim "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: opendkim (failed!!!)');
       logs.DebugLogs('Starting......: opendkim "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: opendkim started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tdkfilter.STATUS():string;
var
pidpath:string;
begin
   if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --opendkim >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tdkfilter.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH(PID_PATH());
  if sys.verbosed then logs.Debuglogs(' ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
function tdkfilter.PID_PATH():string;
begin
     exit('/var/run/opendkim/opendkim.pid');
end;
 //##############################################################################
 function tdkfilter.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_OPENDKIM');
     if length(result)>2 then exit;
     if not FileExists(binpath) then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath +' -V >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='v([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_OPENDKIM',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
