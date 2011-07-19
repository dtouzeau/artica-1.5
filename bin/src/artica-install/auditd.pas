unit auditd;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tauditd=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     MEM_PID_PATH:string;
     MEM_INITD_PATH:string;
     artica_path:string;
     EnableAuditd:integer;
     binpath:string;
     function PID_PATH():string;
     function INITD_PATH():string;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    PID():string;
     function   VERSION():string;
     function BIN_PATH():string;

END;

implementation

constructor tauditd.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableAuditd'),EnableAuditd) then EnableAuditd:=1;

end;
//##############################################################################
procedure tauditd.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tauditd.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   servername:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Audit Daemon........: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID()) then begin
        writeln('Stopping Audit Daemon........: already Stopped');
        exit;
end;

   writeln('Stopping Audit Daemon........: ' + PID() + ' PID..');
   fpsystem(INITD_PATH() +' stop');
   pidstring:=PID();
   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping Audit Daemon........: kill pid '+ pidstring+' after timeout');
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
             writeln('Stopping Audit Daemon........: kill pid ',fpid);
             fpsystem('/bin/kill -9 '+ IntTOStr(fpid));
          end;
       end;
   end;
   end;

  if not SYS.PROCESS_EXIST(PID()) then  writeln('Stopping Audit Daemon........: stopped');
end;

 //##############################################################################

function tauditd.BIN_PATH():string;
var
   path:string;
begin
result:=SYS.LOCATE_GENERIC_BIN('auditd')
end;
 //##############################################################################
function tauditd.INITD_PATH():string;
var
   path:string;
begin
if length(MEM_INITD_PATH)>0 then begin
   result:=MEM_INITD_PATH;
   exit;
end;

if FileExists('/etc/init.d/auditd') then begin
   INITD_PATH:='/etc/init.d/auditd';
   exit('/etc/init.d/auditd');
end;


end;
 //##############################################################################
procedure tauditd.START();
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
   audispd:string;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: Audit Daemon not installed');
         exit;
   end;

   audispd:=SYS.LOCATE_GENERIC_BIN('audispd');
   if fileExists(audispd) then fpsystem('/bin/chmod 750 '+audispd);

if EnableAuditd=0 then begin
   logs.DebugLogs('Starting......:  Audit Daemon is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID()) then begin
   logs.DebugLogs('Starting......:  Audit Daemon Already running using PID ' +PID()+ '...');
   exit;
end;
   logs.DebugLogs('Starting......:  Audit Daemon...');
   cmd:=INITD_PATH() +' start';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID()) do begin
     sleep(150);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Audit Daemon. (timeout!!!)');
       logs.DebugLogs('Starting......: Audit Daemon "'+cmd+'"');
       break;
     end;
   end;


   if not SYS.PROCESS_EXIST(PID()) then begin
       logs.DebugLogs('Starting......: Audit Daemon. (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: Audit Daemon started with new PID '+PID());
   end;

end;
//##############################################################################
function tauditd.STATUS():string;
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --auditd >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tauditd.PID():string;
begin
  result:=SYS.GET_PID_FROM_PATH(PID_PATH());
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
 function tauditd.PID_PATH():string;
begin

if length(MEM_PID_PATH)>0 then begin
   result:=MEM_PID_PATH;
   exit;
end;

if FileExists('/var/run/auditd.pid') then begin
   MEM_PID_PATH:='/var/run/auditd.pid';
   exit('/var/run/auditd.pid');
end;

end;
 //##############################################################################

 function tauditd.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr,auditctl:string;
   cmd:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_AUDITD');
    auditctl:=SYS.LOCATE_GENERIC_BIN('auditctl');
    if length(result)>2 then exit;


    tmpstr:=logs.FILE_TEMP();
    cmd:=auditctl+' -v >'+tmpstr+' 2>&1';
    if SYS.verbosed then writeln('tauditd.VERSION():: '+cmd);
    fpsystem(cmd);
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='version\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_AUDITD',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
