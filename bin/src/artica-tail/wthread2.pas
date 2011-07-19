unit wthread2;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
interface
uses
  Libc, Classes,logs,SysUtils,global_conf,IniFiles,RegExpr,systemlog,mimedefang,unix,zsystem;
type
  TSampleThread2 = class(TThread)
  private
    tid          : integer;
    logs         :tlogs;
    maillog_path :string;
    fileSize_mem :integer;
    GLOBAL_INI   :myconf;
    memvals      :TiniFile;
    artica_path  :string;
    mem_year     :string;
    SYS          :Tsystem;
    procedure graphdefang();


  protected
    procedure Execute; override;
  public

    constructor Create(startSuspended: boolean);
  end;
implementation
//##############################################################################
procedure TSampleThread2.Execute;
begin
GLOBAL_INI:=myconf.Create;
maillog_path:=GLOBAL_INI.SYSTEM_GET_SYSLOG_PATH();
artica_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
SYS:=Tsystem.Create();
logs:=tlogs.Create;
  logs.Debuglogs('wthread2:[' + IntToStr(tid)+']: Start');

  while not Terminated do begin

    graphdefang();
    logs.Debuglogs('wthread2:[' + IntToStr(tid)+']: Sleeping 30 seconds...');
    __sleep(30);
  end;

  logs.Debuglogs('wthread2:[' + IntToStr(tid)+']: end');
end;

//##############################################################################
constructor TSampleThread2.Create(startSuspended: boolean);
begin
  inherited Create(startSuspended);
  tid:=ThreadID;
end;
//##############################################################################
procedure TSampleThread2.graphdefang();
var
   mime:Tmimedefang;
   pid:String;
begin
   mime:=Tmimedefang.Create;
   if not FileExists(mime.Graphdefang_path()) then exit;
   fpsystem(mime.Graphdefang_path() + ' >/opt/artica/logs/graphdefang 2>&1');
   pid:=SYS.PidByProcessPath(mime.Graphdefang_path());
   logs.Debuglogs('wthread2:[' + IntToStr(tid)+']: graphdefang() :: PID="'+pid+'"');
   logs.Debuglogs('wthread2:[' + IntToStr(tid)+']: graphdefang() :: ' + GLOBAL_INI.ReadFileIntoString('/opt/artica/logs/graphdefang'));
   mime.free;

end;

end.


