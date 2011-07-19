unit zabbix;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,
    RegExpr      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';



  type
  tzabbix=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     verbose:boolean;
     EnableZabbixServer:integer;
     EnableZabbixAgent:integer;
     INSTALLED:boolean;
     AGENT_INSTALLED:boolean;
     NotEngoughMemory:boolean;
     function  SERVER_PID():string;
     function  SERVER_PID_PATH():string;
     function  SERVER_INIT_D():string;
     procedure SERVER_START();
     procedure SERVER_STOP();

     function  AGENT_PID_PATH():string;
     function  AGENT_PID():string;
     procedure AGENT_START();
     procedure AGENT_STOP();
     function  AGENT_INIT_D():string;


     function WRITECONFIG():string;



public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    SERVER_VERSION():string;
    function    AGENT_VERSION():string;
    procedure   START();
    procedure   RELOAD();
    procedure   STOP();
    function    STATUS():string;
    function  STATUS_AGENT():string;
END;

implementation

constructor tzabbix.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       verbose:=SYS.COMMANDLINE_PARAMETERS('--verbose');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       INSTALLED:=false;
       AGENT_INSTALLED:=false;
       NotEngoughMemory:=false;


       if FileExists(SYS.LOCATE_ZABBIX_SERVER()) then INSTALLED:=true;
       if FileExists(SYS.LOCATE_ZABBIX_AGENT()) then AGENT_INSTALLED:=true;
       if not TryStrToInt(SYS.GET_INFO('EnableZabbixAgent'),EnableZabbixAgent) then EnableZabbixAgent:=1;
       if not TryStrToInt(SYS.GET_INFO('EnableZabbixServer'),EnableZabbixServer) then EnableZabbixServer:=1;

       if SYS.MEM_TOTAL_INSTALLEE()<716800 then begin
         NotEngoughMemory:=true;
         EnableZabbixAgent:=0;
         EnableZabbixServer:=0;
      end;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tzabbix.free();
begin
    logs.Free;

end;
//##############################################################################
function tzabbix.SERVER_INIT_D():string;
begin
    if not INSTALLED then exit;
    if FileExists('/etc/init.d/zabbix-server') then exit('/etc/init.d/zabbix-server');
    if FileExists('/etc/init.d/zabbix') then exit('/etc/init.d/zabbix');
end;
//##############################################################################
function tzabbix.AGENT_INIT_D():string;
begin
    if not AGENT_INSTALLED then exit;
    if FileExists('/etc/init.d/zabbix-agent') then exit('/etc/init.d/zabbix-agent');
end;
//##############################################################################
function tzabbix.SERVER_PID_PATH():string;
begin
if not INSTALLED then exit;
if FIleExists('/var/run/zabbix-server/zabbix_server.pid') then exit('/var/run/zabbix-server/zabbix_server.pid');
if FileExists('/var/run/zabbix.pid') then exit('/var/run/zabbix.pid');
end;
//##############################################################################
function tzabbix.AGENT_PID_PATH():string;
begin
if not AGENT_INSTALLED then exit;
if FIleExists('/var/run/zabbix-agent.pid') then exit('/var/run/zabbix-agent.pid');
if FileExists('/var/run/zabbix-agent/zabbix_agentd.pid') then exit('/var/run/zabbix-agent/zabbix_agentd.pid');
end;
//##############################################################################
function tzabbix.SERVER_PID():string;
var
   pid_path:string;
   pid:string;
begin
    if not INSTALLED then exit;
    pid_path:=SERVER_PID_PATH();
    pid:=SYS.GET_PID_FROM_PATH(pid_path);

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('SERVER_PID: '+pid+' failed');
      result:=SYS.PIDOF(SYS.LOCATE_ZABBIX_SERVER());
      if verbose then logs.Debuglogs('SERVER_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;
end;
//##############################################################################
function tzabbix.AGENT_PID():string;
var
   pid_path:string;
   pid:string;
begin
    if not INSTALLED then exit;
    pid_path:=AGENT_PID_PATH();
    pid:=SYS.GET_PID_FROM_PATH(pid_path);

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('AGENT_PID: '+pid+' failed');
      result:=SYS.PIDOF(SYS.LOCATE_ZABBIX_AGENT());
      if verbose then logs.Debuglogs('AGENT_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;
end;
//##############################################################################
function tzabbix.SERVER_VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin
if not INSTALLED then exit;
BinPath:=SYS.LOCATE_ZABBIX_SERVER();
result:=SYS.GET_CACHE_VERSION('APP_ZABBIX_SERVER');
if length(result)>0 then exit;

filetmp:=logs.FILE_TEMP();
fpsystem(BinPath+' -V >'+filetmp+' 2>&1');
if not FileExists(filetmp) then exit;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='v([0-9\.]+)';
    FileDatas:=TStringList.Create;
    try
       FileDatas.LoadFromFile(filetmp);

    except
    exit;
    end;
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
             SYS.SET_CACHE_VERSION('APP_ZABBIX_SERVER',result);

end;
//#############################################################################
function tzabbix.AGENT_VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin
if not AGENT_INSTALLED then exit;
BinPath:=SYS.LOCATE_ZABBIX_AGENT();
result:=SYS.GET_CACHE_VERSION('APP_ZABIX_AGENT');
if length(result)>0 then exit;

filetmp:=logs.FILE_TEMP();
fpsystem(BinPath+' -V >'+filetmp+' 2>&1');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='v([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
             SYS.SET_CACHE_VERSION('APP_ZABIX_AGENT',result);

end;
//#############################################################################
function tzabbix.WRITECONFIG():string;
begin
  result:='';
end;
//#############################################################################
procedure tzabbix.RELOAD();
var

   pid:string;
begin
pid:=SERVER_PID();
WRITECONFIG();
if not  SYS.PROCESS_EXIST(pid) then begin
   START();
   exit;
end;
fpsystem('/bin/kill -HUP '+pid);
end;

//#############################################################################
function tzabbix.STATUS():string;
var ini:TstringList;
pid:string;
begin
 pid:='';
 if not INSTALLED then exit;
ini:=TstringList.Create;
   ini.Add('[APP_ZABBIX_SERVER]');
   ini.Add('service_name=APP_ZABBIX_SERVER');
   ini.Add('service_cmd=zabbix');
   ini.Add('service_disabled='+IntToStr(EnableZabbixServer));
   ini.Add('master_version=' + SERVER_VERSION());

   if EnableZabbixServer=0 then begin
      result:=ini.Text;
      SYS.MONIT_DELETE('APP_ZABBIX_SERVER');
      ini.free;
      exit;
   end;

   if SYS.MONIT_CONFIG('APP_ZABBIX_SERVER',SERVER_PID_PATH(),'zabbix') then begin
      ini.Add('monit=1');
      result:=ini.Text;
      ini.free;
      exit;
   end;


   if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');

   pid:=SERVER_PID();
   ini.Add('master_pid='+ pid);
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
   ini.Add('status='+SYS.PROCESS_STATUS(pid));
   ini.add('');
   result:=ini.Text;
   ini.free;
   exit;
end;
//#########################################################################################
function tzabbix.STATUS_AGENT():string;
var ini:TstringList;
pid:string;
begin

 if not AGENT_INSTALLED then exit;
ini:=TstringList.Create;
   ini.Add('[APP_ZABBIX_AGENT]');
   ini.Add('service_name=APP_ZABBIX_AGENT');
   ini.Add('service_cmd=zabbix');
   ini.Add('service_disabled='+IntToStr(EnableZabbixAgent));

   if EnableZabbixAgent=0 then begin
      result:=ini.Text;
      SYS.MONIT_DELETE('APP_ZABBIX_AGENT');
      ini.free;
      exit;
   end;


   if SYS.MONIT_CONFIG('APP_ZABBIX_AGENT',AGENT_PID_PATH(),'zabbix') then begin
      ini.Add('monit=1');
      result:=ini.Text;
      ini.free;
      exit;
   end;


   pid:=AGENT_PID();

   if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ pid);
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
   ini.Add('master_version=' + AGENT_VERSION());
   ini.Add('status='+SYS.PROCESS_STATUS(pid));
   result:=ini.Text;
   ini.free;

end;
//#########################################################################################
procedure tzabbix.SERVER_START();
var
   cmd:string;
   pid:string;
   count:integer;
begin


if not INSTALLED then begin
   logs.Debuglogs('Starting......: Zabbix server is not installed');
   exit;
end;

 if NotEngoughMemory then logs.DebugLogs('Starting......: Warning !!! not enough memory !!!, node at least 750Mb installed on this computer');

if EnableZabbixServer=0 then begin
   SERVER_STOP();
   exit;
end;

pid:=SERVER_PID();
if SYS.PROCESS_EXIST(pid) then begin
    logs.Debuglogs('Starting......: Zabbix server is already running pid '+pid);
    exit;
end;

logs.Debuglogs('Starting......: Zabbix server Deamon');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.zabbix.php --db');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.zabbix.php --server-conf');

cmd:=SYS.LOCATE_GENERIC_BIN('su')+' -c "' +SYS.LOCATE_ZABBIX_SERVER()+' -c /etc/zabbix/zabbix_server.conf" root >/dev/null 2>&1';

logs.OutputCmd(cmd);
pid:=SERVER_PID();
count:=0;
  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>10 then begin
                 logs.DebugLogs('Starting......: Zabbix server daemon (timeout!!!)');
                 break;
              end;

              pid:=SERVER_PID();
        end;


pid:=SERVER_PID();

    if not SYS.PROCESS_EXIST(pid) then begin

         logs.DebugLogs('Starting......: Zabbix server daemon (failed!!!)');
    end else begin

         logs.DebugLogs('Starting......: Zabbix server Success with new PID '+pid);
    end;
end;
//#############################################################################
procedure tzabbix.AGENT_START();
var
   cmd:string;
   pid:string;
   count:integer;
begin


if not AGENT_INSTALLED then begin
   logs.Debuglogs('Starting......: Zabbix Agent is not installed');
   exit;
end;

if EnableZabbixAgent=0 then begin
   AGENT_STOP();
   exit;
end;

pid:=AGENT_PID();
if SYS.PROCESS_EXIST(pid) then begin
    logs.Debuglogs('Starting......: Zabbix Agent is already running pid '+pid);
    exit;
end;

logs.Debuglogs('Starting......: Zabbix Agent Deamon');
cmd:=AGENT_INIT_D()+' start >/dev/null 2>&1';

fpsystem(cmd);
pid:=AGENT_PID();
count:=0;
  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>10 then begin
                 logs.DebugLogs('Starting......: Zabbix Agent daemon (timeout!!!)');
                 break;
              end;

              pid:=AGENT_PID();
        end;


pid:=AGENT_PID();

    if not SYS.PROCESS_EXIST(pid) then begin

         logs.DebugLogs('Starting......: Zabbix Agent daemon (failed!!!)');
    end else begin

         logs.DebugLogs('Starting......: Zabbix Agent Success with new PID '+pid);
    end;
end;
//#############################################################################
procedure tzabbix.START();
begin
SERVER_START();
AGENT_START();
end;
//#############################################################################
procedure tzabbix.SERVER_STOP();
var
   pid:string;
   count:integer;
begin

if not INSTALLED then begin
   writeln('Stopping Zabbix server...: Not Installed');
   exit;
end;
pid:=SERVER_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Zabbix server...: Daemon PID '+pid);
    fpsystem('/bin/kill '+pid+' >/dev/null 2>&1');
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);
      if SYS.PROCESS_EXIST(pid) then fpsystem('/bin/kill '+pid+' >/dev/null 2>&1');
      inc(count);
       if count>50 then begin
            writeln('Stopping Zabbix server...: Timeout while force stopping Daemon pid:'+pid);
            break;
       end;
       pid:=SERVER_PID();
   end;
end else begin
   writeln('Stopping Zabbix server...: Daemon Already stopped');
   exit;
end;
end;
//#############################################################################
procedure tzabbix.AGENT_STOP();
var
   pid:string;
   count:integer;
begin

if not INSTALLED then begin
   writeln('Stopping Zabbix Agent....: Not Installed');
   exit;
end;
pid:=AGENT_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Zabbix Agent....: Daemon PID '+pid);
   fpsystem(AGENT_INIT_D()+' stop >/dev/null 2>&1');
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);
      inc(count);
       if count>50 then begin
            writeln('Stopping Zabbix Agent....: Timeout while force stopping Daemon pid:'+pid);
            break;
       end;
       pid:=AGENT_PID();
   end;
end else begin
   writeln('Stopping Zabbix Agent....: Daemon Already stopped');
   exit;
end;
end;
//#############################################################################
procedure tzabbix.STOP();
begin
SERVER_STOP();
AGENT_STOP();
end;
//#############################################################################
end.
