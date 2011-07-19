unit mailspy_milter;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';


  type
  tmailspy=class


private
     LOGS:Tlogs;
     artica_path:string;
     SYS:Tsystem;
     EnableMilterSpyDaemon:integer;
     function DAEMON_PID():string;


public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  STATUS():string;
    function  BIN_PATH():string;
    procedure START();
    procedure STOP();
    function  VERSION():string;


END;

implementation

constructor tmailspy.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableMilterSpyDaemon:=0;
       if not TryStrToInt(SYS.GET_INFO('EnableMilterSpyDaemon'),EnableMilterSpyDaemon) then begin
          EnableMilterSpyDaemon:=0;
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
procedure tmailspy.free();
begin
    logs.Free;
end;
//##############################################################################
function tmailspy.BIN_PATH():string;
begin
    if FileExists('/usr/local/bin/mailspy') then exit('/usr/local/bin/mailspy');
end;
//#############################################################################
function tmailspy.VERSION():string;
begin
if not FileExists(BIN_PATH()) then exit;
exit('0.4');
end;
//#############################################################################
function tmailspy.DAEMON_PID():string;
begin
result:=SYS.PidByProcessPath(BIN_PATH());
end;
//##############################################################################
procedure tmailspy.START();
var
   pid,cmd:string;
   count:integer;
begin
    count:=0;
    logs.DebugLogs('################# MAILSPY ######################');

    if not FileExists(BIN_PATH()) then begin
       logs.DebugLogs('Starting......: mailspy is not installed...');
       exit;
    end;
    
    if not fileExists(SYS.LOCATE_SU()) then begin
       logs.Syslogs('Starting......: mailspy unable to locate su tool !!');
       exit;
    end;
    if EnableMilterSpyDaemon=0 then begin
        logs.DebugLogs('Starting......: mailspy is disabled by Artica (EnableMilterSpyDaemon)');
        STOP();
        exit;
    end;

    pid:=DAEMON_PID();
    if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: mailspy already exists using pid ' + pid+ '...');
       if FileExists('/var/spool/postfix/var/run/mailspy/mailspy.sock') then logs.OutputCmd('/bin/chown postfix:postfix /var/spool/postfix/var/run/mailspy/mailspy.sock');
       exit;
    end;

    forceDirectories('/var/log/mailspy');
    logs.DebugLogs('Starting......: mailspy daemon...');
    forcedirectories('/var/spool/postfix/var/run/mailspy');
    forcedirectories('/var/log/mailspy');
    forcedirectories('/usr/local/httpd/htdocs/mailspy');
    
    fpsystem('/bin/chown -R mailspy:postfix /var/spool/postfix/var/run/mailspy');
    fpsystem('/bin/chown -R mailspy:postfix /var/log/mailspy');
    
    if FileExists('/var/spool/postfix/var/run/mailspy/mailspy.sock') then logs.DeleteFile('/var/spool/postfix/var/run/mailspy/mailspy.sock');
    if FileExists('/etc/init.d/mailspy') then logs.DeleteFile('/etc/init.d/mailspy');
    
    SYS.AddUserToGroup('mailspy','mailspy','','');
    SYS.AddUserToGroup('mailspy','postfix','','');
    SYS.AddUserToGroup('postfix','mailspy','','');
    SYS.AddShell('mailspy');

    

    //cmd:=SYS.LOCATE_SU() +' mailspy -c "'+BIN_PATH() +' -p /var/spool/postfix/var/run/mailspy/mailspy.sock -f /var/log/mailspy/mailspy.log';
    cmd:=BIN_PATH() +' -p /var/spool/postfix/var/run/mailspy/mailspy.sock -f /var/log/mailspy/mailspy';
    cmd:=cmd + ' -h /var/log/mailspy/mailspy-headers.log &';
    
    logs.Debuglogs(cmd);
    fpsystem(cmd);
    
        while not SYS.PROCESS_EXIST(DAEMON_PID()) do begin
              sleep(150);
              inc(count);
              if count>100 then begin
                 logs.DebugLogs('Starting......: mailspy daemon. (timeout!!!)');
                 break;
              end;
        end;

    if not SYS.PROCESS_EXIST(DAEMON_PID()) then begin
         logs.DebugLogs('Starting......: mailspy daemon. (failed!!!)');
    end else begin
         logs.DebugLogs('Starting......: mailspy daemon. PID '+DAEMON_PID());
         logs.WriteToFile(DAEMON_PID(),'/var/spool/postfix/var/run/mailspy/mailspy.pid');
    end;
    
    if FileExists('/var/spool/postfix/var/run/mailspy/mailspy.sock') then fpsystem('/bin/chown postfix:postfix /var/spool/postfix/var/run/mailspy/mailspy.sock');
    

end;
//##############################################################################

procedure tmailspy.STOP();
var pid:string;
begin

    if not FileExists(BIN_PATH()) then begin
       writeln('Stopping mailspy.........: not installed');
       exit;
    end;
    
    
    pid:=DAEMON_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping mailspy.........: Already stopped');
       exit;
    end;

    writeln('Stopping mailspy.........: ' + pid + ' PID');
    fpsystem('kill ' + pid);
    pid:=SYS.PidAllByProcessPath(BIN_PATH());
    if length(trim(pid))>0 then begin
       writeln('Stopping mailspy.........: ' + pid + ' PIDs');
       fpsystem('/bin/kill -9 ' + pid);
    end;
    SYS.MONIT_DELETE('APP_MAILSPY');
end;
//##############################################################################
function tmailspy.STATUS():string;
var
   ini:TstringList;
   pid:string;
   pid_path:string;
begin
   if not FileExists(BIN_PATH()) then exit;
   pid_path:='/var/spool/postfix/var/run/mailspy/mailspy.pid';
   ini:=TstringList.Create;
   ini.Add('[MAILSPY]');
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid_path)));
   ini.Add('service_name=APP_MAILSPY');
   ini.Add('service_cmd=mailspy');
   ini.Add('service_disabled='+IntToStr(EnableMilterSpyDaemon));
   ini.Add('master_version='+VERSION());

   if  EnableMilterSpyDaemon=0 then begin
      result:=ini.Text;
      ini.free;
      SYS.MONIT_DELETE('APP_MAILSPY');
      exit;
   end;

   if SYS.MONIT_CONFIG('APP_MAILSPY',pid_path,'mailspy') then begin
      result:=ini.Text;
      ini.free;
      exit;
   end;

   pid:=DAEMON_PID();
   if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ pid);
   ini.Add('status='+SYS.PROCESS_STATUS(pid));
   result:=ini.Text;
   ini.free;

end;
//#########################################################################################
end.

