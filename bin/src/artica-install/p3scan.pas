unit p3scan;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tp3scan=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    function    P3SCAN_READ_VALUE(key:string):string;
    function    DEAMON_BIN_PATH():string;
    function    DEAMON_CONF_PATH():string;
    function    P3SCAN_PID():string;
    function    INITD_PATH():string;
    FUNCTION    IPTABLES():string;
    function    VERSION():string;
    FUNCTION    STATUS():string;
    procedure   STOP();


END;

implementation

constructor tp3scan.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tp3scan.free();
begin
    logs.Free;
end;
//##############################################################################
function tp3scan.DEAMON_BIN_PATH():string;
begin
  if FileExists('/usr/sbin/p3scan') then exit('/usr/sbin/p3scan');
end;
//##############################################################################
function tp3scan.DEAMON_CONF_PATH():string;
begin
  if FileExists('/etc/p3scan/p3scan.conf') then exit('/etc/p3scan/p3scan.conf');
end;
//##############################################################################
function tp3scan.INITD_PATH():string;
begin
  if FileExists('/etc/init.d/p3scan') then exit('/etc/init.d/p3scan');
end;
//##############################################################################
function tp3scan.P3SCAN_PID():string;
var
   pid_path:string;
begin
 pid_path:=P3SCAN_READ_VALUE('pidfile');
 if FileExists(pid_path) then begin
    result:=SYS.GET_PID_FROM_PATH(pid_path);
    exit;
 end;
 
 result:=SYS.PidByProcessPath(DEAMON_BIN_PATH());
end;
//##############################################################################
function tp3scan.P3SCAN_READ_VALUE(key:string):string;
var
   RegExpr        :TRegExpr;
   l              :TstringList;
   i              :integer;
begin
  if not FileExists(DEAMON_CONF_PATH()) then exit;
  l:=TstringList.Create;
  l.LoadFromFile(DEAMON_CONF_PATH());
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^'+key+'[\s=]+(.+)';
  For i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
         result:=RegExpr.Match[1];
         break;
      end;
  
  end;
  RegExpr.free;
  l.free;

end;
//##############################################################################


procedure tp3scan.START();
 var
    logs       :Tlogs;
    FileTemp   :string;
begin

     logs:=Tlogs.Create;
      FileTemp:=artica_path+'/ressources/logs/p3scan.start.daemon';
     if not FileExists(DEAMON_BIN_PATH()) then begin
        logs.Debuglogs('tp3scan.START():: P3scan is not installed');
        exit;
     end;


     if not FileExists(DEAMON_CONF_PATH()) then begin
        logs.Debuglogs('tp3scan.START():: not configured');
        exit;
     end;


     if SYS.GET_INFO('P3ScanEnabled')<>'1' then begin
        Logs.DebugLogs('Starting......: P3scan is not enabled aborting...');
        exit;
     end;

 if SYS.PROCESS_EXIST(P3SCAN_PID()) then begin
        logs.DebugLogs('tp3scan.START():: p3scan daemon is already running using PID ' + P3SCAN_PID() + '...');
        exit;
 end;


  if FileExists(INITD_PATH()) then begin
     logs.DebugLogs('Starting......: P3scan ' + INITD_PATH());
     IPTABLES();
     fpsystem(INITD_PATH() + ' start >'+ FileTemp+' 2>&1');
      if not SYS.PROCESS_EXIST(P3SCAN_PID()) then begin

        logs.DebugLogs('Starting......: P3scan Failed ! ' + logs.ReadFromFile(FileTemp));
        exit;
      end;
   logs.DebugLogs('Starting......: P3scan daemon started with new PID ' + P3SCAN_PID() + '...');

   exit;
  end;

end;
//##############################################################################
function tp3scan.VERSION():string;
var
  RegExpr:TRegExpr;
  l:TstringList;
  i:integer;
  FileTemp:string;
begin

     if not FileExists(DEAMON_BIN_PATH()) then begin
        exit;
     end;

     FileTemp:=LOGS.FILE_TEMP();
     fpsystem(DEAMON_BIN_PATH()+' -v >'+FileTemp+' 2>&1');


     if not FileExists(FileTemp) then exit;

     l:=TstringList.Create;
     l.LoadFromFile(FileTemp);
     logs.DeleteFile(FileTemp);

     RegExpr:=tRegExpr.Create;
     RegExpr.Expression:='P3Scan\s+([0-9\.a-zA-Z]+)';

     for i:=0 to l.Count-1 do begin
            if RegExpr.Exec(l.Strings[i]) then begin
               result:=RegExpr.Match[1];
               break;
            end else begin

            end;
    end;

    l.free;
    RegExpr.free;
end;
//##############################################################################
FUNCTION tp3scan.IPTABLES():string;
var
   user_id:string;

begin
result:='';
  if not FileExists('/sbin/iptables') then begin
     Logs.DebugLogs('Starting......: P3scan iptables is not installed in this system');
     exit;
  end;
  
   user_id:=SYS.SystemUserID('p3scan');
   if length(user_id)=0 then begin
       Logs.DebugLogs('Starting......: P3scan Unable to determine the user p3scan');
       exit;
   end;
   
Logs.DebugLogs('Starting......: P3scan redirect rule into nat PREROUTING chain...');
logs.OutputCmd('/sbin/iptables -t nat -I PREROUTING -p tcp -i eth0 --dport pop3 -j REDIRECT --to 8110 -m comment --comment "P3SCAN_PREROUTING"');

Logs.DebugLogs('Starting......: P3scan Inserting p3scan reditect rule into nat OUTPUT chain...');
logs.OutputCmd('/sbin/iptables -t nat -I OUTPUT -p tcp --dport pop3 -j REDIRECT --to 8110 -m comment --comment "P3SCAN_OUTPUT"');

Logs.DebugLogs('Starting......: P3scan Inserting p3scan accept rule into nat OUTPUT chain...');
logs.OutputCmd('/sbin/iptables -t nat -I OUTPUT -p tcp --dport pop3 -m owner --uid-owner '+user_id+' -j ACCEPT -m comment --comment "P3SCAN_ACCEPT"');

//pour lister : iptables -t nat --line-numbers -n -L PREROUTING
//pour lister : iptables -t nat --line-numbers -n -L
//pour deleter :iptables -t nat -D PREROUTING  1

end;
//##############################################################################

FUNCTION tp3scan.STATUS():string;
var
   ini:TstringList;
   pid     :string;
begin


     if not FileExists(DEAMON_BIN_PATH()) then begin
        logs.Debuglogs('tp3scan.STATUS():: Unable to stat p3scan');
        exit;
     end;


ini:=TstringList.Create;
pid:=P3SCAN_PID();
  ini.Add('[P3SCAN]');
  if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('application_enabled=' +SYS.GET_INFO('P3ScanEnabled'));
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
      ini.Add('master_version=' + VERSION());
      ini.Add('status='+SYS.PROCESS_STATUS(pid));
      ini.Add('service_name=APP_P3SCAN');
      ini.Add('service_cmd=p3scan');
      ini.Add('start_logs=p3scan.start.daemon');
result:=ini.Text;
ini.free
end;
//#########################################################################################
procedure tp3scan.STOP();
 var
    count      :integer;
begin

     count:=0;


     if SYS.PROCESS_EXIST(P3SCAN_PID()) then begin
        writeln('Stopping p3scan..............: ' + P3SCAN_PID() + ' PID..');

        if FileExists(INITD_PATH()) then begin
              fpsystem(INITD_PATH() + ' stop');
              exit;
        end;

        fpsystem('/bin/kill ' + P3SCAN_PID());
        while sys.PROCESS_EXIST(P3SCAN_PID()) do begin
              sleep(100);
              inc(count);
              if count>100 then begin
                 writeln('Stopping p3scan..............: Failed');
                 exit;
              end;
        end;

      end else begin
        writeln('Stopping p3scan..............: Already stopped');
     end;

end;
//##############################################################################

end.
