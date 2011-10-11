unit articastatus;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tarticastatus=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableArticaStatus:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
   procedure    RELOAD();
   procedure    INSTALL_INIT_D();


END;

implementation

constructor tarticastatus.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableArticaStatus'),EnableArticaStatus) then EnableArticaStatus:=1;

end;
//##############################################################################
procedure tarticastatus.free();
begin
    logs.Free;
end;
//##############################################################################
procedure tarticastatus.INSTALL_INIT_D();
var
   l:Tstringlist;
begin
l:=Tstringlist.Create;
l.add('#!/bin/sh');
 if fileExists('/sbin/chkconfig') then begin
    l.Add('# chkconfig: 2345 11 89');
    l.Add('# description: Artica-status Daemon');
 end;
l.add('### BEGIN INIT INFO');
l.add('# Provides:          Artica-status ');
l.add('# Required-Start:    $local_fs');
l.add('# Required-Stop:     $local_fs');
l.add('# Should-Start:');
l.add('# Should-Stop:');
l.add('# Default-Start:     2 3 4 5');
l.add('# Default-Stop:      0 1 6');
l.add('# Short-Description: Start Artica status daemon');
l.add('# chkconfig: 2345 11 89');
l.add('# description: Artica status Daemon');
l.add('### END INIT INFO');
l.add('');
l.add('case "$1" in');
l.add(' start)');
l.add('    /usr/share/artica-postfix/bin/artica-install -watchdog artica-status $2');
l.add('    ;;');
l.add('');
l.add('  stop)');
l.add('    /usr/share/artica-postfix/bin/artica-install -shutdown artica-status $2');
l.add('    ;;');
l.add('');
l.add(' restart)');
l.add('     /usr/share/artica-postfix/bin/artica-install -shutdown artica-status $2');
l.add('     sleep 3');
l.add('     /usr/share/artica-postfix/bin/artica-install -watchdog artica-status $2');
l.add('    ;;');
l.add('');
l.add('  *)');
l.add('    echo "Usage: $0 {start|stop|restart}"');
l.add('    exit 1');
l.add('    ;;');
l.add('esac');
l.add('exit 0');

logs.WriteToFile(l.Text,'/etc/init.d/artica-status');
 fpsystem('/bin/chmod +x /etc/init.d/artica-status >/dev/null 2>&1');

 if FileExists('/usr/sbin/update-rc.d') then begin
    fpsystem('/usr/sbin/update-rc.d -f artica-status defaults >/dev/null 2>&1');
 end;

  if FileExists('/sbin/chkconfig') then begin
     fpsystem('/sbin/chkconfig --add artica-status >/dev/null 2>&1');
     fpsystem('/sbin/chkconfig --level 2345 artica-status on >/dev/null 2>&1');
  end;

   LOGS.Debuglogs('Starting......: artica-status install init.d scripts........:OK (/etc/init.d/artica-status {start,stop,restart})');



end;

procedure tarticastatus.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Artica-status.......: Not installed');
   exit;
end;

if SYS.MEM_TOTAL_INSTALLEE()<400000 then begin
       writeln('Stopping Artica-status.......: No engouh memory');
       exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
      writeln('Stopping Artica-status.......: Already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping Artica-status.......: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' -9 '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                     writeln('Stopping Artica-status.......: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  count:=0;
  pids:=Tstringlist.Create;
  pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(SYS.LOCATE_PHP5_BIN()+'\s+/usr/share/artica-postfix/exec.status.php'));
  writeln('Stopping Artica-status.......: ',pids.Count,' childrens.');
  for i:=0 to pids.Count-1 do begin
        if not TryStrToInt(pids.Strings[i],fpid) then continue;
        if fpid>2 then begin
              writeln('Stopping Artica-status.......: kill pid ',fpid);
              fpsystem('/bin/kill -9 '+ IntToStr(fpid));
        end;
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then    writeln('Stopping Artica-status.......: success');
end;

//##############################################################################
function tarticastatus.BIN_PATH():string;
begin
result:='/usr/share/artica-postfix/exec.status.php';
end;
//##############################################################################
procedure tarticastatus.RELOAD();
var
   pid:string;
begin
pid:=PID_NUM();

if SYS.PROCESS_EXIST(pid) then begin
   logs.DebugLogs('Starting......:  Artica-status reload PID ' +pid+ '...');
   fpsystem('/bin/kill -HUP '+ pid);
   exit;
end;
   START();

end;



procedure tarticastatus.START();
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
         logs.DebugLogs('Starting......: Artica-status is not installed');
         exit;
   end;

   if not FileExists('/etc/init.d/artica-status') then  INSTALL_INIT_D();

if SYS.MEM_TOTAL_INSTALLEE()<400000 then begin
        writeln('Stopping Artica-status.......: No engouh memory');
        exit;
end;

if EnableArticaStatus=0 then begin
   logs.DebugLogs('Starting......:  Artica-status is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  Artica-status Already running using PID ' +PID_NUM()+ '...');
   exit;
end;


   cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Artica-status (timeout!!!)');
       logs.DebugLogs('Starting......: Artica-status "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: Artica-status (failed!!!)');
       logs.DebugLogs('Starting......: Artica-status "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: Artica-status started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tarticastatus.STATUS():string;
var
pidpath:string;
begin

   if not FileExists(binpath) then exit;
   if EnableArticaStatus=0 then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --artica-status >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tarticastatus.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/exec.status.php.pid');
  if sys.verbosed then logs.Debuglogs('PID_NUM():: exec.status.php.pid  ->'+result);
end;
 //##############################################################################
end.
