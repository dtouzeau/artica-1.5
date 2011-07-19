unit backuppc;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tbackuppc=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableBackupPc:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    PID():string;
     function   VERSION():string;
     function BIN_PATH():string;
     function CGI_BIN_PATH():string;
END;

implementation

constructor tbackuppc.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableBackupPc'),EnableBackupPc) then EnableBackupPc:=1;
       if Fileexists('/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE') then EnableBackupPc:=0;
end;
//##############################################################################
procedure tbackuppc.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tbackuppc.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   servername:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Backup-PC...........: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID()) then begin
        writeln('Stopping Backup-PC...........: already Stopped');
        exit;
end;



   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.backup-pc.php --init');
   writeln('Stopping Backup-PC...........: ' + PID() + ' PID..');
   fpsystem('/etc/init.d/backuppc stop');
   pidstring:=PID();
   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping Backup-PC...........: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
  end;



   pids:=Tstringlist.Create;
   pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(binpath));
   if pids.Count>1 then begin
   for i:=0 to pids.Count-1 do begin
       if TryStrToInt(pids.Strings[i],fpid) then begin
          if fpid>1 then begin
             writeln('Stopping Backup-PC...........: kill pid ',fpid);
             fpsystem('/bin/kill -9 '+ IntTOStr(fpid));
          end;
       end;
   end;
   end;

  if not SYS.PROCESS_EXIST(PID()) then writeln('Stopping Backup-PC...........: Stopped');
end;

 //##############################################################################

function tbackuppc.BIN_PATH():string;
begin
if FileExists('/usr/share/BackupPC/bin/BackupPC') then exit('/usr/share/BackupPC/bin/BackupPC');
if FileExists('/usr/share/backuppc/bin/BackupPC') then exit('/usr/share/backuppc/bin/BackupPC');
end;
function tbackuppc.CGI_BIN_PATH():string;
begin
if DirectoryExists('/usr/share/BackupPC/cgi-bin') then exit('/usr/share/BackupPC/cgi-bin');
if DirectoryExists('/usr/share/backuppc/cgi-bin') then exit('/usr/share/backuppc/cgi-bin');
end;

procedure tbackuppc.START();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   http_port:integer;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: Backup-PC not installed');
         exit;
   end;

if EnableBackupPc=0 then begin
   logs.DebugLogs('Starting......:  Backup-PC is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID()) then begin
   logs.DebugLogs('Starting......:  Backup-PC Already running using PID ' +PID()+ '...');
   exit;
end;

   logs.DebugLogs('Starting......:  writing init.d script');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.backup-pc.php --init');

   logs.DebugLogs('Starting......:  writing configuration file');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.backup-pc.php --config');
   cmd:='/etc/init.d/backuppc start &';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID()) do begin
     sleep(150);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Backup-PC. (timeout!!!)');
       logs.DebugLogs('Starting......: Backup-PC "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(PID()) then begin
       logs.DebugLogs('Starting......: Backup-PC. (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: Backup-PC started with new PID '+PID());
   end;

end;
//##############################################################################
function tbackuppc.STATUS():string;
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --backuppc >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tbackuppc.PID():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/backuppc/BackupPC.pid');
  logs.Debuglogs('/var/run/backuppc/BackupPC.pid ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
 function tbackuppc.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_BACKUPPC');
     if length(result)>2 then exit;


    tmpstr:=binpath;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Version\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_BACKUPPC',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
