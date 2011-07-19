unit wthread5;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
interface
uses
  Libc, Classes,logs,SysUtils,global_conf,IniFiles,RegExpr,systemlog,zsystem,unix,artica_mysql,postfix_class;
type
  TSampleThread5 = class(TThread)
  private
    tid          : integer;
    logs         :tlogs;
    maillog_path :string;
    fileSize_mem :integer;
    GLOBAL_INI   :myconf;
    memvals      :TiniFile;
    artica_path  :string;
    procedure    Monitor_postfix;
    SYS:Tsystem;
    postfix:tpostfix;
  protected
    procedure Execute; override;
  public
    procedure start;
    Ended:boolean;
    constructor Create(startSuspended: boolean);
  end;

implementation
//##############################################################################
procedure TSampleThread5.Execute;
begin
     while not Terminated do begin
           start;
           logs.Debuglogs('wthread5:[' + IntToStr(tid)+']: Sleeping 10 seconds...');
           __sleep(10);
    end;
end;

//##############################################################################
constructor TSampleThread5.Create(startSuspended: boolean);
begin
  inherited Create(startSuspended);
  FreeOnTerminate := True;
  GLOBAL_INI:=myconf.Create;
  tid:=ThreadID;
  artica_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
  logs:=tlogs.Create;
  postfix:=Tpostfix.Create;

end;
//##############################################################################

procedure TSampleThread5.start;

begin
   Monitor_postfix;

end;

//##############################################################################
procedure TSampleThread5.Monitor_postfix;
var master_path,master_pid,master_cmdline,pid_monitor:string;
begin
    if not FileExists(postfix.POSFTIX_POSTCONF_PATH()) then exit;
    forceDirectories(artica_path+'/ressources/logs/monitor');
    if not FileExists(artica_path+'/ressources/logs/monitor/master.log') then fpsystem('/bin/touch '+artica_path+'/ressources/logs/monitor/master.log');
    if SYS.FileSize_ko(artica_path+'/ressources/logs/monitor/master.log')>1000  then logs.DeleteFile(artica_path+'/ressources/logs/monitor/master.log');
    master_path:=postfix.master_path;
    master_pid:=postfix.POSTFIX_PID();
    if SYS.PROCESS_EXIST(master_pid) then begin
        master_cmdline:=artica_path+'/bin/monitor -i 500000 ' +master_pid;
        pid_monitor:=SYS.PidByProcessPath(master_cmdline);
        if length(pid_monitor)=0 then begin
          Logs.Debuglogs('wthread5:[' + IntToStr(tid)+']: Starting execution ' + master_cmdline);
          fpsystem(master_cmdline +' >>'+artica_path+'/ressources/logs/monitor/master.log &');
        end else  begin
            if SYS.FILE_TIME_BETWEEN_SEC(artica_path+'/ressources/logs/monitor/master.log')>3 then begin
               Logs.Debuglogs('wthread5:[' + IntToStr(tid)+']:Stopping monitor pid ' + pid_monitor + ' and starting it');
               fpsystem('/bin/kill ' +pid_monitor);
               fpsystem(master_cmdline +' >>'+artica_path+'/ressources/logs/monitor/master.log &');
            end;
        end;
        

    end;
end;

end.


