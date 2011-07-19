unit mailarchiver;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,amavisd_milter,
    RegExpr      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';



  type
  tmailarchive=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     MailArchiverEnabled:integer;
     amavis:Tamavis;



public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    function    PID_NUM():string;
    procedure   STOP();
    function    STATUS():string;


END;

implementation

constructor tmailarchive.Create(const zSYS:Tsystem);
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

  amavis:=tamavis.Create(SYS);
  if not TryStrToInt(SYS.GET_INFO('MailArchiverEnabled'),MailArchiverEnabled)  then MailArchiverEnabled:=0;
  if fileExists(amavis.AMAVISD_BIN_PATH()) then begin
        if MailArchiverEnabled=1 then begin
           SYS.set_INFO('EnableAmavisBackup','1');
           SYS.set_INFO('MailArchiverEnabled','0');
        end;
     MailArchiverEnabled:=0;
  end;


end;
//##############################################################################
procedure tmailarchive.free();
begin
    logs.Free;
    amavis.free;
end;
//##############################################################################
function tmailarchive.PID_NUM():string;
begin
     result:=SYS.PIDOF(artica_path + '/bin/mail-dump');
end;
//##############################################################################


procedure tmailarchive.START();
var

   pid:string;
   parms:string;
   count:integer;
begin


  count:=0;
  logs.DebugLogs('############## MAILARCHIVER #######################');

        

  
  if MailArchiverEnabled=0 then begin
      logs.DebugLogs('Starting......: mailArchiver is disabled by MailArchiverEnabled value or Amavis is installed');
      logs.DebugLogs('Starting......: If amavis is installed, this feature was automatically disabled to prevent double operations');
      STOP();
      exit;
  end;
  
  
  pid:=PID_NUM();
  logs.DebugLogs('tmailarchive.START(): PID report "' + PID_NUM()+'"');
  
  
   if SYS.PROCESS_EXIST(pid) then begin
       logs.WriteToFile(pid,'/var/run/maildump/maildump.pid');
      logs.DebugLogs('Starting......: mailArchiver daemon is already running using PID ' + pid + '...');
      exit;
   end;
   

   
   
  if not FileExists(SYS.LOCATE_SU()) then begin
      logs.DebugLogs('Starting......: mailArchiver failed, unable to stat "su" tool');
      exit;
  end;
   
  fpsystem('/bin/rm -rf /var/run/maildump');
  forceDirectories('/var/run/maildump');
  forceDirectories('/tmp/savemail');



  fpsystem('/bin/chown -R postfix:postfix /var/run/maildump');
  fpsystem('/bin/chown -R postfix:postfix /tmp/savemail');

  parms:=SYS.LOCATE_SU() +' postfix -c "'+artica_path + '/bin/mail-dump -p local:/var/run/maildump/maildump.socket" &';
  logs.DebugLogs('Starting......: mailArchiver daemon');
  fpsystem(parms);
  logs.Debuglogs(parms);

  pid:=PID_NUM();
  while not SYS.PROCESS_EXIST(pid) do begin
        sleep(500);
        count:=count+1;
        logs.DebugLogs('tmailarchive.START(): wait sequence ' + intToStr(count) + ' PID=' + pid);
        if count>20 then begin
            logs.DebugLogs('Starting......: mailArchiver timed-out...');
           break;
        end;
        pid:=PID_NUM();
  end;
  pid:=PID_NUM();

  if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: mailArchiver daemon success PID '+pid);
       logs.WriteToFile(pid,'/var/run/maildump/maildump.pid');
  end;




end;
//##############################################################################
procedure tmailarchive.STOP();
var
   pid:string;
   count:integer;
begin
pid:=PID_NUM();
count:=0;
if SYS.PROCESS_EXIST(pid) then begin
   writeln('Stopping mailArchiver........: ' + pid + ' PID..');
   fpsystem('/bin/kill ' + pid);
end else begin
    exit;
end;

pid:=SYS.AllPidsByPatternInPath('/bin/mail-dump');
if length(pid)>0 then begin
   writeln('Stopping mailArchiver........: '+ pid + '...');
   fpsystem('/bin/kill ' + pid);
end;
  pid:=PID_NUM();
  while SYS.PROCESS_EXIST(pid) do begin
        pid:=PID_NUM();
        sleep(100);
        count:=count+1;
        if count>20 then begin
            writeln('Stopping mailArchiver........: timeout ('+pid+')...');
            fpsystem('/bin/kill -9 ' + pid);
        end;
  end;



writeln('Stopping mailArchiver........: success...');


end;

//##############################################################################
function tmailarchive.STATUS():string;
var ini:TstringList;
begin
   ini:=TstringList.Create;
   ini.Add('[MAILARCHIVER]');
   ini.Add('service_name=APP_MAILARCHIVER');
   ini.Add('service_cmd=mailarchiver');
   ini.Add('service_disabled='+ IntToStr(MailArchiverEnabled));
   ini.Add('master_version=1.0.20080707');

   if MailArchiverEnabled=0 then begin
         result:=ini.Text;
         ini.free;
         SYS.MONIT_DELETE('APP_MAILARCHIVER');
         exit;
     end;

      if SYS.MONIT_CONFIG('APP_MAILARCHIVER','/var/run/maildump/maildump.pid','mailarchiver') then begin
         ini.Add('monit=1');
         result:=ini.Text;
         ini.free;
         exit;
      end;



   if SYS.PROCESS_EXIST(PID_NUM()) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ PID_NUM());
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(PID_NUM())));
   ini.Add('status='+SYS.PROCESS_STATUS(PID_NUM()));
   result:=ini.Text;
   ini.free;

end;
//#########################################################################################


end.
