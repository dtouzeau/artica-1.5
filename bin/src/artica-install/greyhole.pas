unit greyhole;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  tgreyhole=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableGreyhole:integer;
     daemon_bin:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    DAEMON_BIN_PATH():string;
    procedure   START();
    procedure   STOP();
    function    VERSION():string;
    function    PID_PATH():string;
    function    PID_NUM():string;
END;

implementation

constructor tgreyhole.Create(const zSYS:Tsystem);
begin
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if not TryStrToInt(SYS.GET_INFO('EnableGreyhole'),EnableGreyhole) then EnableGreyhole:=1;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tgreyhole.free();
begin
    logs.Free;
end;
//##############################################################################
function tgreyhole.DAEMON_BIN_PATH():string;
begin
    if length(daemon_bin)>0 then exit(daemon_bin);
    daemon_bin:=SYS.LOCATE_GENERIC_BIN('greyhole');
   if length(daemon_bin)>0 then exit(daemon_bin);
end;
//##############################################################################
function tgreyhole.PID_PATH():string;
begin
   if FileExists('/var/run/greyhole.pid') then exit('/var/run/greyhole.pid');
   exit('/var/run/greyhole.pid');
end;
//##############################################################################
function tgreyhole.PID_NUM():string;
begin
result:=SYS.GET_PID_FROM_PATH(PID_PATH());
if SYS.PROCESS_EXIST(result) then exit(result);
SYS:=Tsystem.Create;
try result:=SYS.PIDOF_PATTERN(DAEMON_BIN_PATH()) finally end;
end;
//##############################################################################
function tgreyhole.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    tmpstr:string;
begin



 if not FileExists(DAEMON_BIN_PATH()) then exit;

 tmpstr:=logs.FILE_TEMP();

result:=SYS.GET_CACHE_VERSION('APP_GREYHOLE');
   if length(result)>2 then exit;


 FileDatas:=TstringList.Create;
 fpsystem(DAEMON_BIN_PATH() + ' -? >'+tmpstr+' 2>&1');
 if not FileExists(tmpstr) then exit;
 try FileDatas.LoadFromFile(tmpstr) except exit; end;
 logs.DeleteFile(tmpstr);
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='greyhole.+?version.+?([0-9\.]+)';
 for i:=0 to FileDatas.Count-1 do begin
     if RegExpr.Exec(FileDatas.Strings[i]) then begin
         result:=RegExpr.Match[1];
         break;
     end;

 end;
         FileDatas.Free;
         RegExpr.Free;
 SYS.SET_CACHE_VERSION('APP_GREYHOLE',result);
end;
//##############################################################################
procedure tgreyhole.START();
var
   pid:string;
   count:integer;
   nice,cmd,nohup:string;
begin
    if not FileExists(DAEMON_BIN_PATH()) then begin
       logs.Debuglogs('Starting......: greyhole daemon is not installed');
       exit;
    end;



    pid:=PID_NUM();

    if SYS.PROCESS_EXIST(pid) then begin
       logs.Debuglogs('Starting......: greyhole daemon already running PID '+pid);
       if EnableGreyhole=0 then begin
          logs.Syslogs('Stopping greyhole daemon because it was disabled by artica "1"');
          STOP();
          exit;
       end;


       exit;
    end;

    if EnableGreyhole=0 then begin
       logs.Debuglogs('Starting......: greyhole is disabled by artica');
       exit;
    end;

    nice:=sys.EXEC_NICE();

    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.greyhole.php --build');
    if not FileExists('/etc/greyhole.conf') then begin
      logs.Debuglogs('Starting......: greyhole no pools set or misconfigured settings');
      exit;
    end;
    nohup:=sys.LOCATE_GENERIC_BIN('nohup');
    cmd:=trim(nohup+' '+nice+DAEMON_BIN_PATH()+' -D >/dev/null 2>&1 &');
    logs.Debuglogs('Starting......: greyhole daemon using '+cmd+' ');


    fpsystem(cmd);
    pid:=PID_NUM();
    count:=0;


  while not SYS.PROCESS_EXIST(pid) do begin

        sleep(500);
        count:=count+1;
        if count>20 then begin
            writeln('');
            logs.DebugLogs('Starting......: greyhole daemon timeout...');
            logs.DebugLogs('Starting......: "'+cmd+'"');
            break;
        end;
        pid:=PID_NUM();
  end;


    PID:=PID_NUM();
    if not SYS.PROCESS_EXIST(PID) then begin
        logs.Debuglogs('Starting......: Failed to start greyhole');
        exit;
    end else begin
        logs.Debuglogs('Starting......: greyhole success with new PID '+PID);
    end;
end;
//##############################################################################
procedure tgreyhole.STOP();
var
   count:integer;
   PID:string;
begin
    if not FileExists(DAEMON_BIN_PATH()) then begin
     writeln('Stopping greyhole daemon......: not installed');
     exit;
    end;

    if not SYS.PROCESS_EXIST(PID_NUM()) then begin
     writeln('Stopping greyhole daemon......: Already stopped');
    end;

    PID:=PID_NUM();
    if length(PID)>0 then begin
     writeln('Stopping greyhole daemon......: ' + PID + ' PID');
      fpsystem('/bin/kill ' + PID);
    end else begin
       exit;
    end;

count:=0;
 while SYS.PROCESS_EXIST(PID) do begin
        fpsystem('/bin/kill -9 ' + PID +' >/dev/null 2>&1');
        sleep(200);
        inc(count);
        if count>20 then begin
             writeln('Stopping greyhole daemon......: time-out');
           break;
        end;
        pid:=PID_NUM();
  end;


    PID:=PID_NUM();
    if not SYS.PROCESS_EXIST(PID) then sleep(900);


    PID:=PID_NUM();
    if not SYS.PROCESS_EXIST(PID) then begin
     writeln('Stopping greyhole daemon......: stopped');
     exit;
    end;


    PID:=SYS.AllPidsByPatternInPath(DAEMON_BIN_PATH());
       if length(PID)>0 then begin
         writeln('Stopping greyhole daemon......: ' + PID + ' PIDs');
         fpsystem('/bin/kill -9 ' + PID);
       end;

    if SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping greyhole daemon......: ' + PID_NUM() + ' PID (failed to stop)');
    end;


end;
//##############################################################################


end.
