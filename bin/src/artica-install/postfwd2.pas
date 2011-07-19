unit postfwd2;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr in 'RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas',
    bind9   in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/bind9.pas';

  type
  tpostfwd2=class


private
     LOGS:Tlogs;
     artica_path:string;
     EnablePostfwd2:integer;
     daemonbin:string;
     SYS:Tsystem;
     function initd_path():string;
     function PID_PATH():string;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
      function  DAEMON_BIN_PATH():string;
      function  VERSION:string;
      procedure START();
      procedure STOP();
      function  PID_NUM():string;




END;

implementation

constructor tpostfwd2.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if Not TryStrToInt(SYS.GET_INFO('EnablePostfwd2'),EnablePostfwd2) then EnablePostfwd2:=0;
       daemonbin:=DAEMON_BIN_PATH();
       if Not FileExists(daemonbin) then EnablePostfwd2:=0;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tpostfwd2.free();
begin
    logs.Free;
end;
//##############################################################################
function tpostfwd2.DAEMON_BIN_PATH():string;
begin
    exit('/usr/share/artica-postfix/bin/postfwd2.pl');
end;
//#############################################################################
function tpostfwd2.VERSION:string;
var
   binPath:string;
    mem:TStringList;
    commandline:string;
    tmp_file:string;
    RegExpr:TRegExpr;
    i:integer;
begin
    binPath:=DAEMON_BIN_PATH();

    if not FileExists(binpath) then begin
       exit;
    end;
    result:=trim(SYS.GET_CACHE_VERSION('APP_POSTFWD2'));
    if length(result)>2 then exit();
    tmp_file:=logs.FILE_TEMP();
    commandline:=binPath+' -V >'+tmp_file +' 2>&1';
    fpsystem(commandline);
    mem:=TStringList.Create;
    if not FileExists(tmp_file) then exit;
    mem.LoadFromFile(tmp_file);
    logs.DeleteFile(tmp_file);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='postfwd2\s+([0-9\.]+)';

     for i:=0 to mem.Count-1 do begin
       if RegExpr.Exec(mem.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;

     end;
     SYS.SET_CACHE_VERSION('APP_POSTFWD2',result);
     mem.Free;
     RegExpr.Free;

end;
//#############################################################################
procedure tpostfwd2.START();
var
   bin_path,pid,cache,cachecmd:string;
   init:string;
   logs_path:string;

begin

    bin_path:=daemonbin;
    if not FileExists(bin_path) then begin
       logs.DebugLogs('Starting......: postfwd2 is not installed...');
       exit;
    end;
    logs_path:=logs.FILE_TEMP();
    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfwd2.php --start >'+logs_path+' 2>&1');
    writeln(logs.ReadFromFile(logs_path));
    logs.NOTIFICATION('postfwd2 start service report','Here it is the report when starting postfwd2 service:'+logs.ReadFromFile(logs_path),'postfix');
    logs.DeleteFile(logs_path);

end;
//##############################################################################
function tpostfwd2.PID_NUM():string;
begin
result:=SYS.GET_PID_FROM_PATH(PID_PATH());

end;
//##############################################################################
function tpostfwd2.PID_PATH():string;
begin
     if FileExists('/var/run/postfwd2.pid') then exit('/var/run/postfwd2.pid');
end;
//##############################################################################
function tpostfwd2.initd_path():string;
begin
     if FileExists('/etc/init.d/iscsitarget') then exit('/etc/init.d/iscsitarget');
end;
//##############################################################################
procedure tpostfwd2.STOP();
var bin_path,pid,init:string;
count:integer;
begin
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfwd2.php --stop');

end;
//##############################################################################
end.

