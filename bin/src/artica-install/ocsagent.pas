unit ocsagent;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tocsagent=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableOCSAgent:integer;
     OcsServerDest:string;
     OcsServerDestPort:integer;
     binpath:string;
     procedure CONFIG();
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    SUPERVISOR_PID():string;
     function   VERSION():string;
     function BIN_PATH():string;
END;

implementation

constructor tocsagent.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableOCSAgent'),EnableOCSAgent) then EnableOCSAgent:=1;
       if not TryStrToInt(SYS.GET_INFO('OcsServerDestPort'),OcsServerDestPort) then OcsServerDestPort:=0;
       OcsServerDest:=SYS.GET_INFO('OcsServerDest');

end;
//##############################################################################
procedure tocsagent.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tocsagent.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   servername:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Ocsinventory unif...: Not Installed');
   exit;
end;

if not SYS.PROCESS_EXIST(SUPERVISOR_PID()) then begin
       writeln('Stopping Ocsinventory unif...: already Stopped');
       exit;
end;




   writeln('Stopping Ocsinventory unif...: ' + SUPERVISOR_PID() + ' PID..');
   pidstring:=SUPERVISOR_PID();
   fpsystem('/bin/kill '+ pidstring);
   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping Ocsinventory unif...: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
  end;

   CONFIG();

   pids:=Tstringlist.Create;
   pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(binpath));
   if pids.Count>1 then begin
   for i:=0 to pids.Count-1 do begin
       if TryStrToInt(pids.Strings[i],fpid) then begin
          if fpid>1 then begin
             writeln('Stopping Ocsinventory unif...: kill pid ',fpid);
             fpsystem('/bin/kill -9 '+ IntTOStr(fpid));
          end;
       end;
   end;
   end;

  if not SYS.PROCESS_EXIST(SUPERVISOR_PID()) then writeln('Stopping Ocsinventory unif...: Stopped');
end;

 //##############################################################################

function tocsagent.BIN_PATH():string;
begin
if FileExists('/usr/local/bin/ocsinventory-agent') then exit('/usr/local/bin/ocsinventory-agent');
end;
procedure tocsagent.START();
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
         logs.DebugLogs('Starting......: Ocsinventory unified agent for UNIX not installed');
         exit;
   end;

if EnableOCSAgent=0 then begin
   logs.DebugLogs('Starting......:  Ocsinventory unified agent for UNIX is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(SUPERVISOR_PID()) then begin
   logs.DebugLogs('Starting......:  Ocsinventory unified agent for UNIX Already running using PID ' +SUPERVISOR_PID()+ '...');
   exit;
end;

   logs.DebugLogs('Starting......:  Ocsinventory unified agent for UNIX ....');
   ForceDirectories('/var/log/ocsinventory-agent');
   if FileExists('/var/log/ocsinventory-agent/ocsinventory-agent.log') then logs.DeleteFile('/var/log/ocsinventory-agent/ocsinventory-agent.log');

   CONFIG();
   cmd:=BIN_PATH()+' --daemon --logfile=/var/log/ocsinventory-agent/ocsinventory-agent.log';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(SUPERVISOR_PID()) do begin
     sleep(150);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Ocsinventory unified agent for UNIX (timeout!!!)');
       logs.DebugLogs('Starting......: Ocsinventory unified agent for UNIX "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(SUPERVISOR_PID()) then begin
       logs.DebugLogs('Starting......: Ocsinventory unified agent for UNIX (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: Ocsinventory unified agent for UNIX started with new PID '+SUPERVISOR_PID());
   end;

end;
//##############################################################################
procedure tocsagent.CONFIG();
var
   l:Tstringlist;
begin

if length(OcsServerDest)=0 then exit;
if OcsServerDestPort<5 then exit;

   l:=Tstringlist.Create;

l.add('basevardir=/var/lib/ocsinventory-agent');
l.add('server=http://'+OcsServerDest+':'+IntToStr(OcsServerDestPort)+'/ocsinventory');
l.add('');
ForceDirectories('/etc/ocsinventory-agent');
logs.WriteToFile(l.Text,'/etc/ocsinventory-agent/ocsinventory-agent.cfg');
l.free;
l:=Tstringlist.Create;
l.add('# #perl modules.conf');
l.add('use Ocsinventory::Agent::Option::Download;');
l.add('');
l.add('# DO NOT REMOVE THE 1;');
l.add('1;');
l.add('');
logs.WriteToFile(l.Text,'/etc/ocsinventory-agent/modules.conf');
l.free;
end;
//##############################################################################


function tocsagent.STATUS():string;
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --ocsagent >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tocsagent.SUPERVISOR_PID():string;
begin
 result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
 function tocsagent.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_OCSI_LINUX_CLIENT');
     if length(result)>2 then exit;


    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath +' --version >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='\s+\(([0-9\.]+)\)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_OCSI_LINUX_CLIENT',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
