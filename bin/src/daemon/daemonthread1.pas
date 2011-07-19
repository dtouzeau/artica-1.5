unit DaemonThread1;

interface
uses
    libc,Classes,logs,SysUtils,IniFiles,RegExpr,unix,dateutils,strutils,zSystem;
type
  TSampleThread = class(TThread)
  private
    tid          : integer;
    logs         :tlogs;
    SYS          :Tsystem;
    Nice         :Integer;
    tmp          :string;
    perf         :TiniFile;
    artica_path :string;
    cmd_prepend :string;
    procedure    SaveProcesses();

  protected
    procedure Execute; override;
    
  public
    TimeToLive:TDateTime;
    constructor Create(startSuspended: boolean; newID: integer);
  end;




implementation

procedure TSampleThread.Execute;
begin
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;

     logs         :=tlogs.Create;
     SYS          :=Tsystem.Create();
     cmd_prepend:=SYS.EXEC_NICE();
     
     
     
     
     logs.Debuglogs('DaemonThread1:: Starting...');
     SaveProcesses();
     fpsystem(cmd_prepend+artica_path+'/bin/artica-install --verify-artica-iso');
     fpsystem(cmd_prepend+artica_path+'/bin/artica-install --start-minimum-daemons');
     writeln('Starting......: Daemon Thread 1 Sleeping 10 seconds');
     __sleep(10);
     TimeToLive:=Now;
     logs.Debuglogs('DaemonThread1:: START');
     

     
     
  while true do begin
     try
      TimeToLive:=Now;
      
      if not FileExists('/etc/init.d/artica-croned') then SaveProcesses();
         tmp:=SYS.PidByProcessPath('/etc/init.d/artica-croned');
         if length(tmp)>0 then begin
            logs.Debuglogs('/etc/init.d/artica-croned Already executed with pid ' +tmp);
            continue;
         end;
         fpsystem(cmd_prepend+'/etc/init.d/artica-croned &');
         __sleep(5);
     except
     logs.Debuglogs('DaemonThread1:: fatal error while executing loop');
    end;
     logs.Debuglogs('DaemonThread1:: NOOP');
    
  end;

  logs.Debuglogs('DaemonThread1:: End');
end;


constructor TSampleThread.Create(startSuspended: boolean;
  newID: integer);
begin
  inherited Create(startSuspended);
  tid := newID;
  

  
end;

//#########################################################################################
procedure TSampleThread.SaveProcesses();
var l:TstringList;

begin
      l:=TstringList.Create;
      l.Add('#!/bin/sh'); ;
      l.Add(cmd_prepend+artica_path+'/bin/artica-install --startall');
      l.Add(cmd_prepend+artica_path+'/bin/artica-update');
      l.Add(cmd_prepend+artica_path+'/bin/process1');
      l.Add(cmd_prepend+artica_path+'/bin/artica-install --graphdefang-gen');
      l.Add(cmd_prepend+artica_path+'/bin/artica-install --usb-backup');
      l.Add(cmd_prepend+artica_path+'/bin/artica-install --ParseMyqlQueue');
      l.Add(cmd_prepend+artica_path+'/bin/process1 --orders-queue');
      l.Add(cmd_prepend+artica_path+'/bin/artica-sharedfolders');
      l.Add(cmd_prepend+artica_path+'/bin/artica-thread-back');
      l.Add(cmd_prepend+artica_path+'/bin/artica-apt --update');
      l.Add(cmd_prepend+artica_path+'/bin/artica-notif');
      try
      l.SaveToFile('/etc/init.d/artica-croned');
      logs.Debuglogs('Saving /etc/init.d/artica-croned');
      except
         logs.Debuglogs('Saving /etc/init.d/artica-croned failed');
      end;
      l.free;
      logs.OutputCmd('/bin/chmod 777 /etc/init.d/artica-croned');
end;
//#########################################################################################
end.


