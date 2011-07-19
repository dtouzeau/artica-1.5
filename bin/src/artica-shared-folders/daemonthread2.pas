unit DaemonThread2;

interface
uses
  Libc, Classes,logs,SysUtils,global_conf,IniFiles,RegExpr,zsystem,unix;
type
  TSampleThread2 = class(TThread)
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
procedure TSampleThread2.Execute;
begin
     GLOBAL_INI   :=myconf.Create;
     SYS          :=Tsystem.Create;
     artica_path  :=GLOBAL_INI.get_ARTICA_PHP_PATH();
     logs         :=tlogs.Create;


  logs.Debuglogs('DaemonThread2:: Starting...');


  



  while not Terminated do begin
    EnterCriticalSection(thingCountCS);
    inc(thingCount);
    TimeToLive:=Now;
    Try

    except
       logs.Debuglogs('DaemonThread2:: Fatal error');
       logs.Syslogs('DaemonThread2:: Fatal error on ExecuteProcess1()');
    end;
    
    LeaveCriticalSection(thingCountCS);
    
    __sleep(20);
  end;

  logs.Debuglogs('DaemonThread2::Execute:: -> end');
end;
//##############################################################################

constructor TSampleThread2.Create(startSuspended: boolean;
  newID: integer);
begin
  inherited Create(startSuspended);
  tid := newID;
end;

//##############################################################################
end.


