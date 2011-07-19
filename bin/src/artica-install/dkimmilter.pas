unit dkimmilter;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tdkimmilter=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableDkimMilter:integer;
     binpath:string;
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

constructor tdkimmilter.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableDkimMilter'),EnableDkimMilter) then EnableDkimMilter:=0;
       //http://www.howtoforge.com/postfix-dkim-with-dkim-milter-centos5.1
end;
//##############################################################################
procedure tdkimmilter.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tdkimmilter.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping milter-dkim.........: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping milter-dkim.........: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping milter-dkim.........: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping milter-dkim.........: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then writeln('Stopping milter-dkim.........: Stopped');
end;

//##############################################################################
function tdkimmilter.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('dkim-filter');
end;
//##############################################################################
procedure tdkimmilter.START();
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
         logs.DebugLogs('Starting......: dkim-milter is not installed');
         exit;
   end;

if EnableDkimMilter=0 then begin
   logs.DebugLogs('Starting......:  dkim-milter is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  dkim-milter Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   forceDirectories('/var/run/dkim-milter');
   fpsystem('/bin/chown -R postfix:postfix /var/run/dkim-milter');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dkim-milter.php --build');
   cmd:=binpath+' -x /etc/dkim-milter/dkim-milter.conf -P /var/run/dkim-milter/dkim-milter.pid';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: dkim-milter (timeout!!!)');
       logs.DebugLogs('Starting......: dkim-milter "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: dkim-milter (failed!!!)');
       logs.DebugLogs('Starting......: dkim-milter "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: dkim-milter started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tdkimmilter.STATUS():string;
var
pidpath:string;
begin
   if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --dkim-milter >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tdkimmilter.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/dkim-milter/dkim-milter.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
 function tdkimmilter.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_MILTERDKIM');
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
 SYS.SET_CACHE_VERSION('APP_MILTERDKIM',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
