unit virtualbox;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tvirtualbox=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableVirtualBox:integer;
     binpath:string;
     VBoxManage:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;



END;

implementation

constructor tvirtualbox.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       VBoxManage:=SYS.LOCATE_GENERIC_BIN('VBoxManage');
       if not TryStrToInt(SYS.GET_INFO('EnableVirtualBox'),EnableVirtualBox) then EnableVirtualBox:=1;
       //http://www.howtoforge.com/postfix-dkim-with-dkim-milter-centos5.1
end;
//##############################################################################
procedure tvirtualbox.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tvirtualbox.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping VirtualBox Web service: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping VirtualBox Web service: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping VirtualBox Web service: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping VirtualBox Web service: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then writeln('Stopping VirtualBox Web service: Stopped');
end;

//##############################################################################
function tvirtualbox.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('vboxwebsrv');
end;
//##############################################################################
procedure tvirtualbox.START();
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
         logs.DebugLogs('Starting......: VirtualBox Web service is not installed');
         exit;
   end;

if EnableVirtualBox=0 then begin
   logs.DebugLogs('Starting......:  VirtualBox Web service is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  VirtualBox Web service Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   forceDirectories('/var/run/virtualbox');
   forceDirectories('/var/log/virtualbox');
   forceDirectories('/home/VirtualBox/Machines');

   fpsystem(VBoxManage+' setproperty websrvauthlibrary null >/dev/null 2>&1');
   fpsystem(VBoxManage+' setproperty hdfolder /home/VirtualBox  >/dev/null 2>&1');
   fpsystem(VBoxManage+' setproperty machinefolder /home/VirtualBox/Machines  >/dev/null 2>&1');



   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.virtualbox.php --build');
   if FileExists('/var/run/virtualbox/vboxwebsrv.pid') then logs.DeleteFile('/var/run/virtualbox/vboxwebsrv.pid');
   cmd:=binpath+' --background --host 127.0.0.1 --port 18083 --pidfile /var/run/virtualbox/vboxwebsrv.pid --logfile /var/log/virtualbox/vboxwebsrv.log';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: VirtualBox Web service (timeout!!!)');
       logs.DebugLogs('Starting......: VirtualBox Web service "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: VirtualBox Web service (failed!!!)');
       logs.DebugLogs('Starting......: VirtualBox Web service "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: VirtualBox Web service started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tvirtualbox.STATUS():string;
var
pidpath:string;
begin
   if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --vboxwebsrv >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tvirtualbox.PID_NUM():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/virtualbox/vboxwebsrv.pid');
  if sys.verbosed then logs.Debuglogs(' ->'+result);
  if length(result)=0 then result:=SYS.PIDOF_PATTERN(binpath);
  if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF_PATTERN(binpath);
end;
 //##############################################################################
 function tvirtualbox.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_VIRTUALBOX');
     if length(result)>2 then exit;
     if not FileExists(binpath) then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath +' -h >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='web service version\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_VIRTUALBOX',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
