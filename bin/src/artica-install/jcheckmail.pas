unit jcheckmail;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';



  type
  tjcheckmail=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     JCheckMailEnabled:integer;
     function PID_PATH():string;
     procedure REWRITE_INITD();
     procedure SET_CONFIG(KEY:string;value:string);




public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    function    PID_NUM():string;
    procedure   STOP();
    function    STATUS():string;
    function    CONFIG_PATH():string;
    function    SOCK_PATH():string;
    function    BIN_PATH():string;
    function    VERSION():string;
    function    INT_VERSION():integer;
    


END;

implementation

constructor tjcheckmail.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
        if not TryStrToInt(SYS.GET_INFO('JCheckMailEnabled'),JCheckMailEnabled)  then begin
           if FileExists(BIN_PATH()) then begin
                 SYS.set_INFO('JCheckMailEnabled','1');
                 JCheckMailEnabled:=1;
           end else begin
                 SYS.set_INFO('JCheckMailEnabled','0');
                 JCheckMailEnabled:=0;
           end;
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
procedure tjcheckmail.free();
begin
    logs.Free;
end;
//##############################################################################
function tjcheckmail.PID_NUM():string;
var pid:string;
begin
pid :=SYS.GET_PID_FROM_PATH(PID_PATH());
if length(pid)=0 then pid:=SYS.PIDOF(BIN_PATH());
result:=pid;
end;
//##############################################################################
function tjcheckmail.PID_PATH():string;
begin
if FileExists('/var/run/jchkmail/j-chkmail.pid') then exit('/var/run/jchkmail/j-chkmail.pid');
end;
//##############################################################################
function tjcheckmail.BIN_PATH():string;
begin
if FileExists('/usr/sbin/j-chkmail') then exit('/usr/sbin/j-chkmail');
end;
//##############################################################################
function tjcheckmail.SOCK_PATH():string;
begin
exit('/var/spool/postfix/var/run/jchkmail/j-ckmail.sock');
end;
//##############################################################################
function tjcheckmail.CONFIG_PATH():string;
begin
exit('/etc/mail/jchkmail/j-chkmail.cf');
end;
//##############################################################################
procedure tjcheckmail.START();
var

   pid:string;
   parms:string;
   count:integer;
   SocketPath:string;
begin


  count:=0;
  logs.DebugLogs('############## j-ckmail #######################');
  
  if not FileExists(BIN_PATH()) then begin
     logs.Syslogs('Starting......: j-ckmail is not installed');
     exit;
  end;

  if JCheckMailEnabled=0 then begin
      logs.Syslogs('Starting......: j-ckmail is disabled by JCheckMailEnabled value');
      STOP();
      exit;
  end;


  pid:=PID_NUM();
  logs.DebugLogs('tjcheckmail.START(): PID report "' + PID_NUM()+'"');


   if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: j-ckmail daemon is already running using PID ' + pid + '...');
      exit;
   end;
  SocketPath:=ExtractFilePath(SOCK_PATH);
  logs.DebugLogs('Starting......: j-ckmail daemon cleaning...');
  if FileExists(PID_PATH()) then logs.DeleteFile(PID_PATH);
  if FileExists(SOCK_PATH()) then logs.DeleteFile(SOCK_PATH());
  
  forceDirectories(SocketPath);
  forceDirectories('/var/spool/postfix/var/run/jchkmail');
  forceDirectories('/var/jchkmail/files');
  forceDirectories('/var/spool/jchkmail');
  forceDirectories('/var/run/jchkmail');

  fpsystem('/bin/chown -R postfix:postfix /var/spool/postfix/var/run/jchkmail');
  fpsystem('/bin/chown -R postfix:postfix /var/jchkmail');
  fpsystem('/bin/chown -R postfix:postfix /var/spool/jchkmail');
  fpsystem('/bin/chown -R postfix:postfix /var/run/jchkmail');
  fpsystem('/bin/chown -R postfix:postfix '+SocketPath);
  REWRITE_INITD();
  SET_CONFIG('USER','postfix');
  SET_CONFIG('GROUP','postfix');
  SET_CONFIG('SOCKET','local:' + SOCK_PATH());
  SET_CONFIG('CONFDIR','/etc/mail/jchkmail');

  
  logs.DebugLogs('Starting......: j-ckmail daemon socket path: '+SocketPath);
  parms:=BIN_PATH() + ' -q -p local:' + SOCK_PATH() +' -l 10';
  logs.Syslogs('Starting......: j-ckmail daemon');
  logs.OutputCmd(parms);


  pid:=PID_NUM();
  while not SYS.PROCESS_EXIST(pid) do begin

        sleep(500);
        count:=count+1;
        logs.DebugLogs('tjcheckmail.START(): wait sequence ' + intToStr(count) + ' PID=' + pid);
        if count>20 then begin
            logs.DebugLogs('Starting......: j-ckmail daemon failed...');
            exit;
        end;
        pid:=PID_NUM();
  end;
  logs.Syslogs('Success starting jcheckmail daemon...');
  logs.DebugLogs('Starting......: j-ckmail daemon success...');
end;
//##############################################################################
procedure tjcheckmail.STOP();
var
   pid:string;
   count:integer;
begin


  if not FileExists(BIN_PATH()) then begin
     writeln('Stopping j-ckmail.........: not installed');
     exit;
  end;

pid:=PID_NUM();
count:=0;

if SYS.PROCESS_EXIST(pid) then begin
   writeln('Stopping j-ckmail.........: ' + pid + ' PID..');
   fpsystem('/bin/kill ' + pid);
end else begin
    writeln('Stopping j-ckmail.........: Already stopped');
    exit;
end;

  pid:=PID_NUM();
  while SYS.PROCESS_EXIST(pid) do begin
        pid:=PID_NUM();
        sleep(100);
        count:=count+1;
        if count>20 then begin
            writeln('Stopping j-ckmail.........: timeout');
            fpsystem('/bin/kill -9 ' + pid);
        end;
  end;

pid:=PID_NUM();
if not SYS.PROCESS_EXIST(pid) then writeln('Stopping j-ckmail.........: success');



end;

//##############################################################################
procedure tjcheckmail.REWRITE_INITD();
var
l:TstringList;
initpath:string;
begin
  if not FileExists('/etc/init.d/jchkmail') then exit;
  initpath:='/etc/init.d/jchkmail';
  l:=TstringList.Create;


l.Add('#!/bin/sh');
l.Add('#Begin ' + initpath);

 if fileExists('/sbin/chkconfig') then begin
    l.Add('# chkconfig: 2345 11 89');
    l.Add('# description: Artica-postfix Daemon');
 end;

l.Add('case "$1" in');
l.Add(' start)');
l.Add('    /usr/share/artica-postfix/bin/artica-install -watchdog jcheckmail');
l.Add('    ;;');
l.Add('');
l.Add('  stop)');
l.Add('    /usr/share/artica-postfix/bin/artica-install -shutdown jcheckmail');
l.Add('    ;;');
l.Add('');
l.Add(' restart)');
l.Add('     /usr/share/artica-postfix/bin/artica-install -shutdown jcheckmail');
l.Add('     sleep 3');
l.Add('     /usr/share/artica-postfix/bin/artica-install -watchdog jcheckmail');
l.Add('    ;;');
l.Add('');
l.Add('  *)');
l.Add('    echo "Usage: $0 {start|stop|restart}"');
l.Add('    exit 1');
l.Add('    ;;');
l.Add('esac');
l.Add('exit 0');
l.SaveToFile(initpath);
l.free;

end;
//##############################################################################
procedure tjcheckmail.SET_CONFIG(KEY:string;value:string);
var
   RegExpr        :TRegExpr;
   F              :TstringList;
   i              :integer;
   D              :boolean;
begin
  D:=false;
  F:=TstringList.Create;
  
  if FileExists(CONFIG_PATH()) then F.LoadFromFile(CONFIG_PATH());
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^'+KEY;
  
  For i:=0 to F.Count-1 do begin
     if RegExpr.Exec(F.Strings[i]) then begin
        F.Strings[i]:=KEY+chr(9)+value;
        D:=True;
        break;
     end;
  end;
  
if not D then F.Add(KEY+chr(9)+value);
F.SaveToFile(CONFIG_PATH());
F.free;
RegExpr.Free;
end;
//##############################################################################
function tjcheckmail.INT_VERSION():integer;
var package_version:string;
begin
    package_version:=VERSION();
    package_version:=AnsiReplaceText(package_version,'-','');
    package_version:=AnsiReplaceText(package_version,'.','');
    package_version:=AnsiReplaceText(package_version,'_','');
    if not TryStrToInt(package_version,result) then result:=0;

end;

//##############################################################################
function tjcheckmail.VERSION():string;
var
   RegExpr        :TRegExpr;
   F              :TstringList;
   i              :integer;
   tmpstr:string;
begin

  if not FileExists(BIN_PATH()) then begin
     exit;
  end;
  
result:=SYS.GET_CACHE_VERSION('APP_jCHKMAIL');
if length(result)>0 then exit;


  tmpstr:=LOGS.FILE_TEMP();
  fpsystem(BIN_PATH() +' -v >'+tmpstr +' 2>&1');
  F:=TstringList.Create;

  if FileExists(tmpstr) then F.LoadFromFile(tmpstr);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='VERSION\s+v([0-9\.-]+)';

  For i:=0 to F.Count-1 do begin
     if RegExpr.Exec(F.Strings[i]) then begin
        result:=RegExpr.Match[1];
        break;
     end;
  end;
F.free;
RegExpr.Free;
SYS.SET_CACHE_VERSION('APP_jCHKMAIL',result);
end;
//##############################################################################
function tjcheckmail.STATUS():string;
var ini:TstringList;
begin
ini:=TstringList.Create;

   ini.Add('[JCHECKMAIL]');
   if not FileExists(BIN_PATH()) then begin
         ini.Add('application_installed=0');
         result:=ini.Text;
         ini.free;
         exit;
   end;
   
   if SYS.PROCESS_EXIST(PID_NUM()) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ PID_NUM());
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(PID_NUM())));
   ini.Add('master_version='+VERSION);
   ini.Add('status='+SYS.PROCESS_STATUS(PID_NUM()));
   ini.Add('service_name=APP_JCHECKMAIL');
   ini.Add('service_cmd=jcheckmail');
   ini.Add('service_disabled='+ IntToStr(JCheckMailEnabled));
   result:=ini.Text;
   ini.free;

end;
//#########################################################################################


end.
