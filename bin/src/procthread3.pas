unit ProcThread3;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils,variants, Process,Linux,IniFiles,oldlinux,strutils,logs,dateutils,
  RegExpr,
  confiles in 'confiles.pas',
  global_conf in 'global_conf.pas',
  postfix_standard;
type
  thProcThread3=class(TThread)
  private


    debug:boolean;
    PHP_PATH:string;
  protected
    procedure Execute; override;



  public

    constructor Create;
    end;

implementation
//##############################################################################
procedure thProcThread3.Execute;
var
   P:TProcess;
   LOGS:Tlogs;

begin
  LOGS:=Tlogs.Create;
  logs.logsStart('artica-daemon:: ThProcThread[3] Execute...');
  logs.logsStart('artica-daemon:: ThProcThread[3] PID ' +IntTOStr(getpid));
  FreeOnTerminate := True;
        P := TProcess.Create(nil);
        P.CommandLine := PHP_PATH + '/bin/process3';
        P.Options := [poWaitOnExit];
   while not terminated do begin
        Select(0,nil,nil,nil,10*500);
        P.Execute;

    end;
    logs.logsStart('artica-daemon:: ThProcThread[2] FINISH...');

end;

//##############################################################################
constructor thProcThread3.Create;
var
   GLOBAL_INI:myconf;
begin
   inherited Create(False);
   forcedirectories('/etc/artica-postfix');
   FreeOnTerminate := True;
   GLOBAL_INI:=myConf.Create();
   PHP_PATH:=GLOBAL_INI.get_ARTICA_PHP_PATH();
   GLOBAL_INI.Free;


end;

//##############################################################################
end.

