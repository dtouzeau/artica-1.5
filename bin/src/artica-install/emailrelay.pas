unit emailrelay;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  temailrelay=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;


public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    EMAILRELAY_PID():string;
END;

implementation

constructor temailrelay.Create(const zSYS:Tsystem);
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
procedure temailrelay.free();
begin
    logs.Free;
end;
//##############################################################################

procedure temailrelay.STOP();
var
   count:integer;
begin
if not FileExists(SYS.LOCATE_GENERIC_BIN('emailrelay') ) then begin
   writeln('Stopping Artica notifier.....: Not installed (emailrelay)');
   exit;
end;
if SYS.PROCESS_EXIST(EMAILRELAY_PID()) then begin
   writeln('Stopping Artica notifier.....: ' + EMAILRELAY_PID() + ' PID..');
   fpsystem('/bin/kill ' + EMAILRELAY_PID());
      count:=0;
     while SYS.PROCESS_EXIST(EMAILRELAY_PID()) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(EMAILRELAY_PID())>0 then begin
               if SYS.PROCESS_EXIST(EMAILRELAY_PID()) then begin
                  writeln('Stopping Artica notifier.....: kill pid '+ EMAILRELAY_PID()+' after timeout');
                  fpsystem('/bin/kill -9 ' + EMAILRELAY_PID());
               end;
            end;
            break;
        end;
  end;


end else begin
   writeln('Stopping Artica notifier.....: Already stopped');
end;
end;
 //##############################################################################

procedure temailrelay.START();
var
   count:integer;
   cmd:string;
   binpath:string;
   conf:TiniFile;
   enabled:integer;
begin
   binpath:=SYS.LOCATE_GENERIC_BIN('emailrelay');

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: Artica notifier not installed');
         exit;
   end;

   logs.DebugLogs('Starting......: Artica notifier....');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' exec.emailrelay.php --start &');
   if not FileExists('/etc/artica-postfix/smtpnotif.conf') then begin
         logs.DebugLogs('Starting......: Artica notifier notifications are not enabled');
         exit;
   end;

   conf:=TiniFile.Create('/etc/artica-postfix/smtpnotif.conf');
   enabled:=conf.ReadInteger('SMTP','enabled',0);
   if enabled=0 then begin
      logs.DebugLogs('Starting......: Artica notifier notifications are not enabled');
      conf.free;
      exit;
   end;

  conf.free;


if SYS.PROCESS_EXIST(EMAILRELAY_PID()) then begin
   logs.DebugLogs('Starting......: Artica notifier running using PID ' +EMAILRELAY_PID()+ '...');
   exit;
end;
   forceDirectories('/var/spool/artica-notifier');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.emailrelay.php');
   if not FileExists('/etc/artica-postfix/emailrelay.cmd') then begin
      logs.DebugLogs('Starting......: Artica notifier notifications are not enabled');
      exit;
   end;


   cmd:=binpath+' '+trim(SYS.ReadFileIntoString('/etc/artica-postfix/emailrelay.cmd'));
   logs.OutputCmd(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(EMAILRELAY_PID()) do begin
     sleep(150);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Artica notifier. (timeout!!!)');
       logs.DebugLogs('Starting......: Artica notifier "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(EMAILRELAY_PID()) then begin
       logs.DebugLogs('Starting......: Artica notifier. (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: Artica notifier. PID '+EMAILRELAY_PID());
   end;

end;
//##############################################################################
function temailrelay.STATUS():string;
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --artica-notifier >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function temailrelay.EMAILRELAY_PID():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/artica-notifier.pid');
end;
 //##############################################################################

end.
