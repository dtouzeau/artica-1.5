unit DaemonThread3;

interface
uses
  Libc, Classes,logs,SysUtils,global_conf,IniFiles,RegExpr,zsystem,unix;
type
  TSampleThread3 = class(TThread)
  private
    tid          : integer;
    logs         :tlogs;
    GLOBAL_INI   :myconf;
    SYS          :Tsystem;
    regexfile    :TstringList;
    infected_file:TstringList;
    mini         :TiniFile;
    artica_path  :string;
    procedure ParseMyqlQueue();



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
procedure TSampleThread3.Execute;
begin
     GLOBAL_INI   :=myconf.Create;
     SYS          :=Tsystem.Create;
     artica_path  :=GLOBAL_INI.get_ARTICA_PHP_PATH();
     logs         :=tlogs.Create;


  logs.Debuglogs('DaemonThread3:: Starting...');
  __sleep(30);





  while not Terminated do begin
    EnterCriticalSection(thingCountCS);
    inc(thingCount);
    TimeToLive:=Now;
    Try
       logs.Debuglogs('DaemonThread3:: -- > ParseMyqlQueue()...');
       ParseMyqlQueue();
    except
       logs.Debuglogs('DaemonThread3:: Fatal error');
       logs.Syslogs('DaemonThread3:: Fatal error on ParseMyqlQueue()');
    end;



    LeaveCriticalSection(thingCountCS);

    __sleep(5);
  end;

  logs.Debuglogs('DaemonThread3::Execute:: -> end');
end;
//##############################################################################

constructor TSampleThread3.Create(startSuspended: boolean;
  newID: integer);
begin
  inherited Create(startSuspended);
  tid := newID;
end;

//##############################################################################
procedure TSampleThread3.ParseMyqlQueue();
begin
  logs.Debuglogs('DaemonThread3::' + GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-install --ParseMyqlQueue &');
  fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-install --ParseMyqlQueue &');
end;


end.


