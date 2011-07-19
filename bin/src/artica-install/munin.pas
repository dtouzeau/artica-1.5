unit munin;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  tmunin=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     MuninDisabled:integer;
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

constructor tmunin.Create(const zSYS:Tsystem);
begin
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if not TryStrToInt(SYS.GET_INFO('MuninDisabled'),MuninDisabled) then MuninDisabled:=0;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tmunin.free();
begin
    logs.Free;
end;
//##############################################################################
function tmunin.DAEMON_BIN_PATH():string;
begin
    if length(daemon_bin)>0 then exit(daemon_bin);
    daemon_bin:=SYS.LOCATE_GENERIC_BIN('munin-node');
   if length(daemon_bin)>0 then exit(daemon_bin);
end;
//##############################################################################
function tmunin.PID_PATH():string;
begin
   if FileExists('/var/run/munin/munin-node.pid') then exit('/var/run/munin/munin-node.pid');
   exit('/var/run/munin/munin-node.pid');
end;
//##############################################################################
function tmunin.PID_NUM():string;
begin
result:=SYS.GET_PID_FROM_PATH(PID_PATH());
if SYS.PROCESS_EXIST(result) then exit(result);
SYS:=Tsystem.Create;
try result:=SYS.PIDOF(DAEMON_BIN_PATH()) finally end;
end;
//##############################################################################
function tmunin.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    tmpstr:string;
begin



 if not FileExists(DAEMON_BIN_PATH()) then exit;

 tmpstr:=logs.FILE_TEMP();

result:=SYS.GET_CACHE_VERSION('APP_MUNIN');
   if length(result)>0 then exit;


 FileDatas:=TstringList.Create;
 fpsystem(DAEMON_BIN_PATH() + ' --version >'+tmpstr+' 2>&1');
 if not FileExists(tmpstr) then exit;
 try FileDatas.LoadFromFile(tmpstr) except exit; end;
 logs.DeleteFile(tmpstr);
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='munin-node.+?version\s+([0-9\.])\.';
 for i:=0 to FileDatas.Count-1 do begin
     if RegExpr.Exec(FileDatas.Strings[i]) then begin
         result:=RegExpr.Match[1];
         break;
     end;

 end;
         FileDatas.Free;
         RegExpr.Free;
 SYS.SET_CACHE_VERSION('APP_MUNIN',result);
end;
//##############################################################################
procedure tmunin.START();
var
   pid:string;
   count:integer;
begin
    if not FileExists(DAEMON_BIN_PATH()) then begin
       logs.Debuglogs('Starting......: munin-node is not installed');
       exit;
    end;



    pid:=PID_NUM();

    if SYS.PROCESS_EXIST(pid) then begin
       logs.Debuglogs('Starting......: munin-node already running PID '+pid);
       if MuninDisabled=1 then begin
          logs.Syslogs('Stopping munin-node because it was disabled by artica "1"');
          STOP();
          exit;
       end;


       exit;
    end;

    if MuninDisabled=1 then begin
       logs.Debuglogs('Starting......: munin-node is disabled by artica');
       exit;
    end;


    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.munin.php --server');
    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.munin.php --node');

    logs.Debuglogs('Starting......: munin-node');


    fpsystem(DAEMON_BIN_PATH()+' --config /etc/munin/munin-node.conf');
    pid:=PID_NUM();
    count:=0;


  while not SYS.PROCESS_EXIST(pid) do begin

        sleep(500);
        count:=count+1;
        write('.');
        if count>50 then begin
            writeln('');
            logs.DebugLogs('Starting......: munin-node daemon timeout...');
            break;
        end;
        pid:=PID_NUM();
  end;


    PID:=PID_NUM();
    if not SYS.PROCESS_EXIST(PID) then begin
        logs.Debuglogs('Starting......: Failed to start munin-node');
        exit;
    end else begin
        logs.Debuglogs('Starting......: munin-node success with new PID '+PID);
    end;
end;
//##############################################################################
procedure tmunin.STOP();
var
   count:integer;
   PID:string;
begin
    if not FileExists(DAEMON_BIN_PATH()) then begin
     writeln('Stopping munin daemon.........: not installed');
     exit;
    end;

    if not SYS.PROCESS_EXIST(PID_NUM()) then begin
     writeln('Stopping munin daemon.........: Already stopped');
    end;

    PID:=PID_NUM();
    if length(PID)>0 then begin
     writeln('Stopping munin daemon.........: ' + PID + ' PID');
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
           writeln('Stopping munin daemon.........: time-out');
           break;
        end;
        pid:=PID_NUM();
  end;

    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.munin.php --server');
    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.munin.php --node');

    PID:=PID_NUM();
    if not SYS.PROCESS_EXIST(PID) then sleep(900);


    PID:=PID_NUM();
    if not SYS.PROCESS_EXIST(PID) then begin
     writeln('Stopping munin daemon.........: stopped');
     exit;
    end;


    PID:=SYS.AllPidsByPatternInPath(DAEMON_BIN_PATH());
       if length(PID)>0 then begin
    writeln('Stopping munin daemon.........: ' + PID + ' PIDs');
         fpsystem('/bin/kill -9 ' + PID);
       end;

    if SYS.PROCESS_EXIST(PID_NUM()) then begin
    writeln('Stopping munin daemon.........: ' + PID_NUM() + ' PID (failed to stop)');
    end;


end;
//##############################################################################


end.
