unit fetchmail;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  tfetchmail=class


private
     LOGS:Tlogs;
     D:boolean;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     EnableFetchmail:integer;
     EnablePostfixMultiInstance:integer;
     procedure FETCHMAIL_APPLY_GETLIVE_PARAMETERS(account_id:string);
     procedure FETCHMAIL_LOGGER_KILLTAIL();

public
    FETCHMAIL_LOGGER_STARTUP:string;
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  FETCHMAIL_VERSION(nocache:boolean=false):string;
    function  FETCHMAIL_DAEMON_POOL():string;
    function  FETCHMAIL_START_DAEMON():boolean;
    function  FETCHMAIL_BIN_PATH():string;
    procedure FETCHMAIL_APPLY_CONF(conf_datas:string);
    function  FETCHMAIL_DAEMON_STOP(nologger:boolean=false):string;
    function  FETCHMAIL_PID():string;
    procedure FETCHMAIL_APPLY_GETLIVE_CONF();
    procedure FETCHMAIL_APPLY_GETLIVE();
    function  FETCHMAIL_COUNT_SERVER():integer;
    function  FETCHMAIL_SERVER_PARAMETERS(param:string):string;
    function  FETCHMAIL_DAEMON_POSTMASTER():string;
    function  FETCHMAIL_RELOAD():string;
    function  STATUS():string;


    procedure FETCHMAIL_LOGGER_STOP();
    function  FETCHMAIL_LOGGER_PID():string;
    procedure FETCHMAIL_LOGGER_START();

END;

implementation

constructor tfetchmail.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       FETCHMAIL_LOGGER_STARTUP:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.fetmaillog.php';
       if not TryStrToInt(SYS.GET_INFO('EnableFetchmail'),EnableFetchmail) then EnableFetchmail:=0;
       if not TryStrToInt(SYS.GET_INFO('EnablePostfixMultiInstance'),EnablePostfixMultiInstance) then EnablePostfixMultiInstance:=0;
       if EnablePostfixMultiInstance=1 then EnableFetchmail:=1;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tfetchmail.free();
begin
    FreeAndNil(logs);
end;
//##############################################################################
function tfetchmail.FETCHMAIL_LOGGER_PID():string;
var
   pid:string;
begin

if FileExists('/etc/artica-postfix/exec.fetmaillog.php.pid') then begin
   pid:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/exec.fetmaillog.php.pid');
   logs.Debuglogs('FETCHMAIL_LOGGER_PID /etc/artica-postfix/exec.fetmaillog.php.pid='+pid);
   if SYS.PROCESS_EXIST(pid) then result:=pid;
   exit;
end;
result:=SYS.PIDOF_PATTERN(FETCHMAIL_LOGGER_STARTUP);
logs.Debuglogs(FETCHMAIL_LOGGER_STARTUP+' pid='+pid);
end;
//#####################################################################################
procedure tfetchmail.FETCHMAIL_LOGGER_STOP();
var
   pid:string;
   log_path:string;
begin
pid:=FETCHMAIL_LOGGER_PID();

if SYS.PROCESS_EXIST(pid) then begin
      writeln('artica-postfix realtime logs.: (fetchmail) Stopping pid '+pid);
      while SYS.PROCESS_EXIST(pid) do begin
           fpsystem('/bin/kill '+pid);
           sleep(100);
           pid:=FETCHMAIL_LOGGER_PID();
      end;
end else begin
      writeln('artica-postfix realtime logs.: (fetchmail) Already stopped');
      FETCHMAIL_LOGGER_KILLTAIL();
      exit;
end;

pid:=SYS.PIDOF_PATTERN(FETCHMAIL_LOGGER_STARTUP);
if not SYS.PROCESS_EXIST(pid) then begin
      writeln('artica-postfix realtime logs.: (fetchmail) stopped');
      FETCHMAIL_LOGGER_KILLTAIL();
end else begin
      writeln('artica-postfix realtime logs.: (fetchmail) Failed to stop');
end;

end;
//#####################################################################################
procedure tfetchmail.FETCHMAIL_LOGGER_START();
var
   pid:string;
   log_path:string;
   count:integer;
   cmd:string;
   stime:string;
   EnableFetchmail:integer;
begin



if not TryStrToInt(SYS.GET_INFO('EnableFetchmail'),EnableFetchmail) then EnableFetchmail:=0;

if EnableFetchmail=0 then begin
    logs.DebugLogs('Starting......: artica-postfix realtime logs (fetchmail) disabled');
    exit;
end;


log_path:='/var/log/fetchmail.log';


pid:=FETCHMAIL_LOGGER_PID();
if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: artica-postfix realtime logs (fetchmail) already running with pid '+pid);
      exit;
end;

FETCHMAIL_LOGGER_KILLTAIL();

if not FileExists(log_path) then begin
   logs.Syslogs('Starting......: artica-postfix realtime logs (fetchmail) unable to locate mail logs !!');
   exit;
end;
logs.DebugLogs('Starting......: artica-postfix realtime logs (fetchmail) path: '+log_path);



stime:=logs.DateTimeNowSQL();
stime:=AnsiReplaceText(stime,' ','-');
stime:=AnsiReplaceText(stime,':','-');
cmd:='/usr/bin/tail -f -n 0 '+log_path+'|'+FETCHMAIL_LOGGER_STARTUP+' >>/var/log/artica-postfix/postfix-logger-start.log 2>&1 &';
logs.Debuglogs(cmd);
fpsystem(cmd);
pid:=SYS.PIDOF_PATTERN(FETCHMAIL_LOGGER_STARTUP);
count:=0;
while not SYS.PROCESS_EXIST(pid) do begin

        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting......: artica-postfix realtime logs (fetchmail) (timeout)');
           break;
        end;
        pid:=SYS.PIDOF_PATTERN(FETCHMAIL_LOGGER_STARTUP);
  end;


  pid:=SYS.PIDOF_PATTERN(FETCHMAIL_LOGGER_STARTUP);
if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: artica-postfix realtime logs (fetchmail) success with pid '+pid);
      exit;
end else begin
    logs.DebugLogs('Starting......: artica-postfix realtime logs (fetchmail) failed');
end;
end;
//#####################################################################################
procedure tfetchmail.FETCHMAIL_LOGGER_KILLTAIL();
var l:Tstringlist;
log_path,cmd:string;
pids:integer;
i:integer;
begin
 log_path:='/var/log/fetchmail.log';
  cmd:='/usr/bin/tail -f -n 0 '+log_path;
  l:=Tstringlist.Create;
  l.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(cmd));
  for i:=0 to l.Count-1 do begin
     if not TryStrToInt(trim(l.Strings[i]),pids) then pids:=0;
     if pids<5 then continue;
     writeln('artica-postfix realtime logs.: (fetchmail) kill pid '+IntTOstr(pids));
     fpsystem('/bin/kill -9 '+IntTOstr(pids));
  end;

end;

function tfetchmail.FETCHMAIL_VERSION(nocache:boolean):string;
var
    path:string;
    RegExpr:TRegExpr;
    FileData:TStringList;
    i:integer;
    D:Boolean;
    tmpstr:string;
begin
     path:=FETCHMAIL_BIN_PATH();
     if not FileExists(path) then begin
        logs.Debuglogs('FETCHMAIL_VERSION:: ftechmail is not installed');
        exit;
     end;


     result:=SYS.GET_CACHE_VERSION('APP_FETCHMAIL');
     if nocache then result:='';
     if ParamStr(1)='--fetchmail-status' then  result:='' ;


      if length(result)>2 then exit;
     tmpstr:=logs.FILE_TEMP();



     FileData:=TStringList.Create;
     RegExpr:=TRegExpr.Create;
    fpsystem(path+' -V >'+ tmpstr + ' 2>&1');
    FileData.LoadFromFile(tmpstr);
 RegExpr.Expression:='fetchmail,.+?([0-9\.]+)';
  for i:=0 to FileData.Count -1 do begin
          if RegExpr.Exec(FileData.Strings[i]) then  begin
            result:=RegExpr.Match[1];
            if length(result)>1 then begin
               SYS.SET_CACHE_VERSION('APP_FETCHMAIL',result);
               FileData.Free;
               RegExpr.Free;
               exit;
            end;
            end;
          end;


end;
//#############################################################################
function tfetchmail.FETCHMAIL_DAEMON_POOL():string;
begin
result:=SYS.GET_INFO('FetchmailDaemonPool');
end;
//#############################################################################
function tfetchmail.FETCHMAIL_START_DAEMON():boolean;
var
 fetchmail_daemon_pool,fetchmailpid,fetchmailpath:string;
 fetchmail_count:integer;
 D:boolean;
 EnableFetchmail:integer;
begin
     result:=true;
     EnableFetchmail:=0;



     if not TryStrToInt(SYS.GET_INFO('EnableFetchmail'),EnableFetchmail) then EnableFetchmail:=0;

if EnablePostfixMultiInstance=1 then begin
   logs.DebugLogs('Starting......: multi-postfix instances enabled, switch to artica-cron.');
   FETCHMAIL_DAEMON_STOP(true);
   FETCHMAIL_LOGGER_START();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.fetchmail.php');
   exit;
end;

     fetchmailpid:=FETCHMAIL_PID();


     if SYS.PROCESS_EXIST(fetchmailpid) then begin
         if EnableFetchmail=0 then begin
             logs.DebugLogs('Starting......: fetchmail is disabled by Artica with "EnableFetchmail" parameter');
             FETCHMAIL_DAEMON_STOP();
             if FileExists('/etc/fetchmailrc') then logs.DeleteFile('/etc/fetchmailrc');
             exit;
         end;
         logs.DebugLogs('Starting......: fetchmail is already running using PID ' + fetchmailpid + '...');
         FETCHMAIL_LOGGER_START();
         exit;
     end;

     if EnableFetchmail=0 then begin
          logs.DebugLogs('Starting......: fetchmail is disabled by Artica with "EnableFetchmail" parameter');
          if FileExists('/etc/fetchmailrc') then logs.DeleteFile('/etc/fetchmailrc');
          exit;
     end;
     logs.DebugLogs('Starting......: fetchmail start fetchmail-logger');
     FETCHMAIL_LOGGER_START();
     fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.fetchmail.php');
     fetchmailpath:=FETCHMAIL_BIN_PATH();
     fetchmail_daemon_pool:=FETCHMAIL_SERVER_PARAMETERS('daemon');
     fetchmail_count:=FETCHMAIL_COUNT_SERVER();


     LOGS.logs('FETCHMAIL_START_DAEMON:: PID=' +fetchmailpid + ';Path='+fetchmailpath+';Pool='+ fetchmail_daemon_pool+';Servers Count=' + INtTOStr(fetchmail_count));

     if FileExists('/opt/artica/logs/fetchmail.daemon.started') then DeleteFile('/opt/artica/logs/fetchmail.daemon.started');
     if length(fetchmail_daemon_pool)=0 then logs.Debuglogs('Artica...No config saved /etc/fetchmailrc');
     if FileExists('/etc/fetchmailrc') then begin
        logs.OutputCmd('/bin/chown root:root /etc/fetchmailrc');
        logs.OutputCmd('/bin/chmod 600 /etc/fetchmailrc');
     end;


    if fetchmail_count>0 then begin
     if length(fetchmailpath)>0 then begin
        if length(fetchmail_daemon_pool)>0 then begin
           if not SYS.PROCESS_EXIST(fetchmailpid) then begin
              logs.DebugLogs('Starting......: fetchmail daemon...: Enable....: '+IntToStr(EnableFetchmail));
              logs.DebugLogs('Starting......: fetchmail daemon...: Path......: '+fetchmailpath);
              logs.Debuglogs('SYSTEM_START_ARTICA_DAEMON:: Start FETCHMAIL service server ' + IntToStr(fetchmail_count) + ' server(s)');
              if FileExists('/opt/artica/logs/fetchmail.daemon.started') then DeleteFile('/opt/artica/logs/fetchmail.daemon.started');
              logs.Debuglogs(fetchmailpath + ' --daemon ' + fetchmail_daemon_pool + ' --pidfile /var/run/fetchmail.pid --fetchmailrc /etc/fetchmailrc > /opt/artica/logs/fetchmail.daemon.started 2>&1');
              logs.Syslogs('Starting fecthmail daemon...');
              fpsystem(fetchmailpath + ' --daemon ' + fetchmail_daemon_pool + ' --pidfile /var/run/fetchmail.pid --fetchmailrc /etc/fetchmailrc > /opt/artica/logs/fetchmail.daemon.started 2>&1');
           end else begin
               logs.DebugLogs('Starting......: fetchmail is already running using PID ' + fetchmailpid + '...');
           end;
        end;
     end;
    end else begin
        logs.DebugLogs('Starting......: fetchmail no server has been set, aborting');
    end;


end;
//##############################################################################


function tfetchmail.FETCHMAIL_DAEMON_POSTMASTER():string;
begin
result:=SYS.GET_INFO('FetchMailDaemonPostmaster');
end;
//#############################################################################
function tfetchmail.FETCHMAIL_BIN_PATH():string;
var
   path:string;
begin
    if FileExists('/opt/artica/bin/fetchmail') then exit('/opt/artica/bin/fetchmail');
    if FileExists('/usr/local/bin/fetchmail') then exit('/usr/local/bin/fetchmail');
    if FileExists('/usr/bin/fetchmail') then exit('/usr/bin/fetchmail');
    if FileExists('/usr/local/bin/fetchmail') then exit('/usr/local/bin/fetchmail');
    path:=SYS.LOCATE_GENERIC_BIN('fetchmail');
    if FileExists(path) then exit(path);
end;
//#############################################################################
function tfetchmail.FETCHMAIL_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/fetchmail.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/fetchmail.pid');
     end;

     if length(result)>2 then exit;
     result:=SYS.PidByProcessPath(FETCHMAIL_BIN_PATH());
end;
//##############################################################################
procedure tfetchmail.FETCHMAIL_APPLY_CONF(conf_datas:string);
var value:TstringList;
begin
   if SYS.get_INFO('EnableFetchmail')<>'1' then exit;
   if length(conf_datas)=0 then exit;
   if not fileexists(FETCHMAIL_BIN_PATH) then exit;
   value:=TstringList.Create;
   value.Add(conf_datas);
   value.SaveToFile('/etc/fetchmailrc');
   value.free;
   fpsystem('/bin/chown root:root /etc/fetchmailrc');
   fpsystem('/bin/chmod 0710 /etc/fetchmailrc');
   FETCHMAIL_RELOAD();
   FETCHMAIL_APPLY_GETLIVE_CONF();

end;
//#############################################################################
function tfetchmail.FETCHMAIL_DAEMON_STOP(nologger:boolean):string;
var
   pid:string;
   binpath:string;
   count:integer;
   pidnum:integer;
begin
    result:='';
    binpath:=FETCHMAIL_BIN_PATH();
    if not FileExists(binpath) then begin
    writeln('Stopping fetchmail...........: Not installed');
    exit;
    end;

    if SYS.PROCESS_EXIST(FETCHMAIL_PID()) then begin
       writeln('Stopping fetchmail...........: ' + FETCHMAIL_PID() + ' PID..');
       fpsystem(binpath + ' -q');
    end;

  pid:=SYS.PIDOF(binpath);
  while SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if TryStrToInt(pid,pidnum) then begin
           if pidnum >1 then fpsystem('/bin/kill '+pid);
        end;

        if count>50 then begin
           writeln('Stopping fetchmail...........:' + pid + ' PID (timeout) kill it');
           logs.OutputCmd('/bin/kill -9 ' + pid);
           break;
        end;
        pid:=SYS.PIDOF(binpath);
  end;

    if not nologger then FETCHMAIL_LOGGER_STOP();


end;
//#############################################################################
function tfetchmail.FETCHMAIL_RELOAD():string;
var pid:string;
begin
    result:='';
    if not FileExists(FETCHMAIL_BIN_PATH()) then begin
    exit;
    end;
    pid:=FETCHMAIL_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
          FETCHMAIL_START_DAEMON();
          exit;
    end;
    logs.Syslogs('Reloading fetchmail');
    logs.OutputCmd('/bin/kill -HUP ' + pid);
end;
//#############################################################################
procedure tfetchmail.FETCHMAIL_APPLY_GETLIVE_CONF();
var
   RegExpr       :TRegExpr;
   TmpFile       :TstringList;
   i             :integer;
   DaemonPool    :integer;
   Hour          :integer;
   list          :string;
   D             :Boolean;
begin

    if not FileExists('/etc/fetchmailrc') then begin
       if FileExists('/etc/cron.d/GetLive') then DeleteFile('/etc/cron.d/GetLive');
       exit;
    end;
    DaemonPool:=0;
    TmpFile:=TstringList.Create;
    TmpFile.LoadFromFile('/etc/fetchmailrc');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='set daemon\s+([0-9]+)';
    for i:=0 to TmpFile.Count -1 do begin
        if RegExpr.Exec(TmpFile.Strings[i]) then begin
           DaemonPool:=StrToInt(RegExpr.Match[1]);
           break;
        end;

    end;
    TmpFile.Clear;
    list:='';
    Hour:=0;
    if DaemonPool=0 then exit;
    DaemonPool:=DaemonPool div 60;
    if DaemonPool>60 then DaemonPool:=60;
    writeln('pool=' + IntToStr(DaemonPool));
    for i:=1 to 60 do begin
      Hour:=Hour+DaemonPool;
      if(Hour>60) then break;
      list:=list + IntToStr(Hour) + ',';
    end;
    writeln('copy='+Copy(list,length(list),1));
    if Copy(list,length(list),1)=',' then list:=Copy(list,0,length(list)-1);



   list:=list+' * * * * root ' + artica_path + '/bin/artica-ldap -getlive >>/var/log/fetchmail.log 2>&1';
   if D then  writeln('FETCHMAIL_APPLY_GETLIVE_CONF:: cron=' + list);
   TmpFile.Add(list);
   TmpFile.SaveToFile('/etc/cron.d/GetLive');
   if D then  writeln('FETCHMAIL_APPLY_GETLIVE_CONF:: /etc/cron.d/GetLive -> saved');

end;
//#############################################################################
procedure tfetchmail.FETCHMAIL_APPLY_GETLIVE_PARAMETERS(account_id:string);
var
iniF          :TMemIniFile;
final         :TStringList;
targetedfile  :string;
CommandLine   :string;
begin
    iniF:=TMemIniFile.Create('/etc/artica-postfix/settings/Daemons/GetLiveAccounts');
    
    
    
    forceDirectories('/etc/artica-postfix/settings/GetLive');
    forceDirectories(artica_path+'/ressources/logs/GetLive');
    final:=TStringList.Create();
    targetedfile:='/etc/artica-postfix/settings/GetLive/'+account_id+'.conf';
     final.Add('UserName=' + iniF.ReadString(account_id,'UserName',''));
     final.Add('Password=' + iniF.ReadString(account_id,'Password',''));
     final.Add('Domain=' + iniF.ReadString(account_id,'Domain',''));
     final.Add('Downloaded='+iniF.ReadString(account_id,'Downloaded','/etc/artica-postfix/settings/GetLive/'+account_id+'.cache'));
     final.Add('CurlBin=' +SYS.LOCATE_CURL()+' -k');
     final.Add('Processor=/usr/sbin/sendmail -i ' + iniF.ReadString(account_id,'user','root@localhost.localdomain'));
     final.SaveToFile(targetedfile);
     CommandLine:=artica_path + '/bin/GetLive.pl --config-file '+targetedfile+' --verbosity 100 >'+artica_path+'/ressources/logs/GetLive/'+account_id+'.log 2>&1 &';
     logs.Debuglogs(CommandLine);
     fpsystem(CommandLine);
     final.free;
    
    

end;


procedure tfetchmail.FETCHMAIL_APPLY_GETLIVE();
var
   value         :TstringList;
   iniF          :TMemIniFile;
   RegExpr       :TRegExpr;
   RegExpr2      :TRegExpr;
   i             :integer;
   Config        :TstringList;
   GetLiveCf     :TstringList;
   RemoteMail    :string;
   user          :string;
   domain        :string;
   Password      :string;
   SendMailuser  :string;
   CommandLine   :string;
   Accounts      :TstringList;
   AccountEnabled:integer;

begin
   if not FileExists('/etc/artica-postfix/settings/Daemons/GetLiveAccounts') then begin
      logs.DebugLogs('Starting......: GetLive no accounts...');
      exit;
   end;
   
   if not FileExists(SYS.LOCATE_CURL()) then begin
       logs.DebugLogs('Starting......: Unable to stat curl');
       exit;
   end;
   
   if not SYS.CHECK_PERL_MODULES('File::Spec') then begin
      logs.Syslogs('Error, missing File::Spec for GetLive');
      exit;
   end;
   
   if not SYS.CHECK_PERL_MODULES('URI::Escape') then begin
      logs.Syslogs('Error, missing URI::Escape for GetLive');
      exit;
   end;
   
   ForceDirectories('/etc/artica-postfix/settings/GetLive');
   iniF:=TMemIniFile.Create('/etc/artica-postfix/settings/Daemons/GetLiveAccounts');
   Accounts:=TStringList.Create;
   iniF.ReadSections(Accounts);
   logs.Debuglogs('Loading ' + IntToStr(Accounts.Count)+' hotmail/getlive account(s)');
   if Accounts.Count=0 then exit;
   
   for i:=0 to Accounts.Count-1 do begin
         if not TryStrToInt(iniF.ReadString(Accounts.Strings[i],'enabled','0'),AccountEnabled) then AccountEnabled:=0;
         if AccountEnabled=0 then continue;
         FETCHMAIL_APPLY_GETLIVE_PARAMETERS(Accounts.Strings[i]);
         
   end;

end;
//#############################################################################
function tfetchmail.FETCHMAIL_SERVER_PARAMETERS(param:string):string;
var
   RegExpr:TRegExpr;
   filedatas:TStringList;
   i:integer;
begin
  if not FileExists('/etc/fetchmailrc') then exit;
  filedatas:=TStringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^set\s+' + param + '\s+(.+)';
  filedatas.LoadFromFile('/etc/fetchmailrc');
   for i:=0 to filedatas.Count -1 do begin
      if RegExpr.Exec(filedatas.Strings[i]) then begin
         result:=trim(RegExpr.Match[1]);
         break;
      end;
   end;

   RegExpr.Free;
   filedatas.free;
end;
//##############################################################################
function tfetchmail.FETCHMAIL_COUNT_SERVER():integer;
var
   RegExpr:TRegExpr;
   filedatas:TStringList;
   i:integer;
begin
  result:=0;
  if not FileExists('/etc/fetchmailrc') then exit;
  filedatas:=TStringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^poll\s+(.+)';
  filedatas.LoadFromFile('/etc/fetchmailrc');
   for i:=0 to filedatas.Count -1 do begin
      if RegExpr.Exec(filedatas.Strings[i]) then begin
         inc(result);
         break;
      end;
   end;

   RegExpr.Free;
   filedatas.free;
end;
//##############################################################################
function tfetchmail.STATUS();
var
pidpath:string;
pid:string;
begin
SYS.MONIT_DELETE('APP_FETCHMAIL');
SYS.MONIT_DELETE('APP_FETCHMAIL_LOGGER');
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --fetchmail >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//##############################################################################

end.
