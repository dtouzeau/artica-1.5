unit pptpd;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tpptpd=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnablePPTPDVPN:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
    function    VERSION():string;
    procedure   RELOAD();
    procedure   CLIENTS_STOP();
    procedure   CLIENTS_START();


END;

implementation

constructor tpptpd.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnablePPTPDVPN'),EnablePPTPDVPN) then EnablePPTPDVPN:=0;

end;
//##############################################################################
procedure tpptpd.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tpptpd.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping PPTP VPN............: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
   writeln('Stopping PPTP VPN............: Already Stopped');
   exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping PPTP VPN............: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping PPTP VPN............: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  count:=0;
  pids:=Tstringlist.Create;
  pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(binpath));
   writeln('Stopping PPTP VPN............: ',pids.Count,' children.');
  for i:=0 to pids.Count-1 do begin
        if not TryStrToInt(pids.Strings[i],fpid) then continue;
        if fpid>2 then begin
              writeln('Stopping PPTP VPN............: kill pid ',fpid);
              fpsystem('/bin/kill -9 '+ IntToStr(fpid));
        end;
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then  writeln('Stopping PPTP VPN............: success');
end;

//##############################################################################
function tpptpd.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('pptpd');
end;
//##############################################################################
procedure tpptpd.RELOAD();
var
   pid:string;
begin
pid:=PID_NUM();

if SYS.PROCESS_EXIST(pid) then begin
   logs.DebugLogs('Starting......:  PPTP VPN reload PID ' +pid+ '...');
   fpsystem('/bin/kill -HUP '+ pid);
   exit;
end;
   START();

end;
//##############################################################################
procedure tpptpd.CLIENTS_START();
begin
if not FileExists(SYS.LOCATE_GENERIC_BIN('pptp')) then begin
        logs.DebugLogs('Starting......: PPTP VPN Client not installed');
        exit;
end;
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.pptpd.php --clients-start');
end;
//##############################################################################
procedure tpptpd.CLIENTS_STOP();
begin
if not FileExists(SYS.LOCATE_GENERIC_BIN('pptp')) then begin
       writeln('Stopping PPTP VPN Client.....: Not installed');
       exit;
end;

fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.pptpd.php --clients-start');
end;
//##############################################################################
procedure tpptpd.START();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   cmdline:string;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: PPTP VPN is not installed');
         exit;
   end;

if EnablePPTPDVPN=0 then begin
   logs.DebugLogs('Starting......: PPTP VPN is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......: PPTP VPN Already running using PID ' +PID_NUM()+ '...');
   exit;
end;
   logs.DebugLogs('Starting......: PPTP VPN building settings');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.pptpd.php --build');
   cmd:=binpath+' --conf /etc/pptpd.conf --pidfile /var/run/pptpd.pid';
      logs.DebugLogs('Starting......: PPTP VPN service...');
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: PPTP VPN (timeout!!!)');
       logs.DebugLogs('Starting......: PPTP VPN "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: PPTP VPN (failed!!!)');
       logs.DebugLogs('Starting......: PPTP VPN "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: PPTP VPN started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tpptpd.STATUS():string;
var
pidpath:string;
begin

   if not FileExists(binpath) then exit;
   if EnablePPTPDVPN=0 then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --pptpd >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tpptpd.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/pptpd.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
end;
 //##############################################################################
 function tpptpd.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_PPTPD');
   if length(result)>2 then begin
      if SYS.verbosed then writeln('APP_PPTPD():',result,' from memory');
      exit;
   end;


    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath+' -v >'+tmpstr+' 2>&1');
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='v([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;

 if SYS.verbosed then writeln('APP_PPTPD(): "',result,'"');
 SYS.SET_CACHE_VERSION('APP_PPTPD',result);
l.free;
RegExpr.free;
end;
//##############################################################################
end.
