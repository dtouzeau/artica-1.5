unit DaemonThread4;

interface
uses
  Libc, Classes,logs,SysUtils,global_conf,IniFiles,RegExpr,zsystem,unix;
type
  TSampleThread4 = class(TThread)
  private
    tid          : integer;
    logs         :tlogs;
    GLOBAL_INI   :myconf;
    SYS          :Tsystem;
    regexfile    :TstringList;
    infected_file:TstringList;
    mini         :TiniFile;
    artica_path  :string;

  protected
    procedure Execute; override;
  public
        TimeToLive:TDateTime;
    constructor Create(startSuspended: boolean; newID: integer);
  end;




var
  thingCount: integer;
  thingCountCS: TRTLCriticalSection;

implementation
//##############################################################################
procedure TSampleThread4.Execute;
begin
     GLOBAL_INI   :=myconf.Create;
     SYS          :=Tsystem.Create;
     artica_path  :=GLOBAL_INI.get_ARTICA_PHP_PATH();
     logs         :=tlogs.Create;


  logs.Debuglogs('DaemonThread4:: Starting...');


  while not Terminated do begin
    EnterCriticalSection(thingCountCS);
    inc(thingCount);
    TimeToLive:=Now;
    Try
       logs.Debuglogs('DaemonThread4:: EXEC-> '+GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/process1 --orders-queue & ');
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/process1 --orders-queue &');
       logs.Debuglogs('DaemonThread4:: EXEC-> '+GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-thread-back &');
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-thread-back &');
    except
       logs.Debuglogs('DaemonThread4:: Fatal error');
       logs.Syslogs('DaemonThread4:: Fatal error on exectue process1 or ');
    end;



    LeaveCriticalSection(thingCountCS);

    __sleep(5);
  end;

  logs.Debuglogs('DaemonThread4::Execute:: -> end');
end;
//##############################################################################

constructor TSampleThread4.Create(startSuspended: boolean;
  newID: integer);
begin
  inherited Create(startSuspended);
  tid := newID;
end;

//##############################################################################
end.


