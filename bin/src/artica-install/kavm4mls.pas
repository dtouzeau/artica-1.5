unit kavm4mls;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tkavm4mls=class


private
     LOGS:Tlogs;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     procedure SET_VALUE(KEY:string;VALUE:string;data:string);
     procedure FixDefaultConf();

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    INITD_PATH():string;
    function    VERSION():string;
    function    CONF_PATH():string;
    FUNCTION    KAV_MILTER_PID():string;
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    GET_VALUE(KEY:string;VALUE:string):string;
    function    PATTERN_DATE():string;
    function    PERFORM_UPDATE():string;
    function    LOGS_PATH():string;
    function    GET_LASTLOGS():string;
    function    BIN_PATH():string;
    procedure   VERIFY_CONFIG();
    procedure   VerifyGroupConfiguration();
    procedure   COMPONENTS_INFOS();
    procedure   RELOAD();
END;

implementation

constructor tkavm4mls.Create(const zSYS:Tsystem);
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
procedure tkavm4mls.free();
begin
    logs.Free;
end;
//##############################################################################
function tkavm4mls.INITD_PATH():string;
begin
   if FileExists('/etc/init.d/kav4lms') then exit('/etc/init.d/kav4lms');
end;
//##############################################################################
function tkavm4mls.KAV_MILTER_PID():string;
begin
  if not FileExists('/opt/kav/5.6/kavmilter/bin/kavmilter') then exit();
  result:=SYS.PIDOF('/opt/kav/5.6/kavmilter/bin/kavmilter');
end;
//##############################################################################
function tkavm4mls.BIN_PATH():string;
begin
 if FileExists('/opt/kav/5.6/kavmilter/bin/kavmilter') then exit('/opt/kav/5.6/kavmilter/bin/kavmilter');
end;
//##############################################################################

function tkavm4mls.CONF_PATH():string;
begin
  if FileExists('/etc/kav/5.6/kavmilter/kavmilter.conf') then exit('/etc/kav/5.6/kavmilter/kavmilter.conf');
end;
//#############################################################################
function tkavm4mls.LOGS_PATH():string;
         var path:string;
begin
  path:=GET_VALUE('kavmilter.log','LogFacility');
  if path='syslog' then begin
     if FileExists('/var/log/syslog') then exit('/var/log/syslog');
     exit;
  end;

  exit(GET_VALUE('kavmilter.log','LogFilepath'));
end;
 //#############################################################################
 function tkavm4mls.GET_LASTLOGS():string;
var
   cmd,grep:string;
begin
  grep:='';
  if GET_VALUE('kavmilter.log','LogFacility')='syslog' then grep:='|grep -E "kavmilter\[[0-9]+\]"';
  cmd:='/usr/bin/tail -n 500 ' + LOGS_PATH() + grep + ' '+' >/opt/artica/logs/kavmilter.last.logs';
  fpsystem(cmd);
  result:=logs.ReadFromFile('/opt/artica/logs/kavmilter.last.logs');

end;


 //#############################################################################

procedure tkavm4mls.RELOAD();
var
    pidlists:string;
begin
  pidlists:=KAV_MILTER_PID();
  if length(pidlists)=0 then begin
     START();
     exit;
  end;

  logs.Syslogs('Reloading kavmilter...');
  VERIFY_CONFIG();
  VerifyGroupConfiguration();
  FixDefaultConf();
  fpsystem(BIN_PATH() + ' -r reload');



end;
 //#############################################################################
procedure tkavm4mls.VERIFY_CONFIG();
var
   user:string;
   group:string;
   fuser:string;
   conf:TiniFile;
   l:TstringList;
   i:integer;
begin
     conf:=TiniFile.Create('/etc/kav/5.6/kavmilter/kavmilter.conf');
     user:=conf.ReadString('kavmilter.global','RunAsUid','kav');
     group:=conf.ReadString('kavmilter.global','RunAsGid','kav');

     SYS.AddUserToGroup(user,group,'','');

     fuser:=user+':'+group;
     logs.Debuglogs('Starting......: Kaspersky Mail server will run has "'+fuser+'"');
    ForceDirectories('/var/db/kav/5.6/kavmilter/backup');
    forceDirectories('/var/db/kav/5.6/kavmilter/run');

    l:=TStringList.Create;
    l.Add('/var/db/kav/5.6/kavmilter/tmp');
    l.Add('/var/db/kav/5.6/kavmilter/backup');
    l.Add('/var/db/kav/5.6/kavmilter/run');
    l.add('/var/db/kav/5.6/kavmilter/licenses');
    l.add('/etc/kav/5.6/kavmilter/groups.d');

    for i:=0 to l.Count -1 do begin
    logs.OutputCmd('/bin/chown -R '+fuser+' '+l.Strings[i]);
    logs.OutputCmd('/bin/chmod -R 755 '+l.Strings[i]);
   end;


  l.free;

if not FileExists('/etc/kav/5.6/kavmilter/groups.d/default.conf') then begin
    logs.DebugLogs('Starting......: Kaspersky Mail server already default.conf not exists, create a new one');
    if FileExists('/usr/share/artica-postfix/bin/install/kavmilter.default.conf') then begin
       logs.OutputCmd('/bin/cp /usr/share/artica-postfix/bin/install/kavmilter.default.conf /etc/kav/5.6/kavmilter/groups.d/default.conf');
    end;
end;
end;

 //#############################################################################

procedure tkavm4mls.COMPONENTS_INFOS();
var
   ini:TiniFile;
begin

logs.Debuglogs('updating /var/opt/kaspersky/applications.setup');
ini:=TiniFile.Create('/var/opt/kaspersky/applications.setup');
ini.WriteString('1043','DEFAULTCONFIG','/etc/opt/kaspersky/kav4lms.conf');
ini.WriteString('1043','FILE','/opt/kaspersky/kav4lms/sbin/kavmd');
ini.WriteString('1043','ID','1043');
ini.WriteString('1043','INSTROOT','/opt/kaspersky/kav4lms');
ini.WriteString('1043','NAME','Kaspersky Anti-Virus for Linux Mail Server');
ini.WriteString('1043','PATH_BASES','/var/opt/kaspersky/kav4lms/bases');
ini.WriteString('1043','PATH_LICENSES','/var/opt/kaspersky/kav4lms/licenses');
ini.WriteString('1043','FILTER_SERVICE','true');
ini.WriteString('1043','FILTER_PROGRAM','kav4lms-milter');
ini.WriteString('1043','INITD_kav4lms','true');
ini.WriteString('1043','INITD_kav4lms_filters','true');
ini.WriteString('1043','PATH_LICENSES','/var/opt/kaspersky/kav4lms/licenses');
ini.WriteString('1043','USED_PKGMGR','deb');
ini.WriteString('1043','VERSION','5.6.42');
ini.UpdateFile;
ini.Free;
end;
 //#############################################################################

procedure tkavm4mls.START();
var
    pidlists:string;
    count:integer;
begin


logs.Debuglogs('###################### kav4mls ######################');

if not FileExists('/opt/kaspersky/kav4lms/bin/kav4lms-cmd') then begin
   logs.DebugLogs('Starting......: Kaspersky Mail server not installed');
   exit;
end;



if trim(SYS.get_INFO('kavmilterEnable'))='yes' then SYS.set_INFO('kavmilterEnable','1');

pidlists:=KAV_MILTER_PID();

if SYS.get_INFO('kavmilterEnable')<>'1' then begin
   logs.DebugLogs('Starting......: Kaspersky Mail server is disabled...');
   if length(pidlists)>0 then begin

      logs.Syslogs('Stopping Kavmilter this software is disabled by artica');
      STOP();
   end;
   exit;
end;

forceDirectories('/var/run/kav4lms');
forcedirectories('/var/opt/kaspersky/kav4lms/bases');

logs.OutputCmd('/bin/chown -R postfix:postfix /etc/opt/kaspersky/kav4lms');
logs.OutputCmd('/bin/chown -R postfix:postfix /var/run/kav4lms');
logs.OutputCmd('/bin/chown -R postfix:postfix /var/opt/kaspersky/kav4lms/bases');
COMPONENTS_INFOS();



    if length(pidlists)>0 then begin
       logs.DebugLogs('Starting......: Kaspersky Mail server already started running using pid ' + pidlists+ '...');
       exit;
   end;

   logs.DebugLogs('Starting......: Kaspersky Mail server verify configuration...');
   VERIFY_CONFIG();
   logs.DebugLogs('Starting......: Kaspersky Mail server verify group configuration...');
   VerifyGroupConfiguration();
   logs.DebugLogs('Starting......: Fixing default configuration');
   FixDefaultConf();
   logs.DebugLogs('Starting......: Sarting daemon');
   fpsystem('/etc/init.d/kavmilterd start &');
   pidlists:=KAV_MILTER_PID();
   count:=0;


 while length(pidlists)>0 do begin

        sleep(200);
        inc(count);
        write('.');
        if count>30 then begin
           writeln('');
           logs.DebugLogs('Starting......: kavmilterd (failed)');
           exit;
        end;
        pidlists:=KAV_MILTER_PID();
  end;

      writeln('');
      if length(trim(KAV_MILTER_PID()))>0 then begin
         logs.DebugLogs('Starting......: Kaspersky Mail server success with pids ' + KAV_MILTER_PID());
         writeln('');
      end else begin
          logs.DebugLogs('Starting......: Kaspersky Mail server failed');
          writeln('');
      end;



end;
//##############################################################################
function tkavm4mls.GET_VALUE(KEY:string;VALUE:string):string;
var path:string;
begin
  path:=CONF_PATH();
  if not FileExists(path) then begin
     logs.Debuglogs('tkavm4mls.GET_VALUE():: unable to stat configuration file !!!');
     exit;
  end;
  GLOBAL_INI:=TIniFile.Create(path);
  result:=GLOBAL_INI.ReadString(KEY,VALUE,'');
  GLOBAL_INI.Free;
end;
//#############################################################################
procedure tkavm4mls.SET_VALUE(KEY:string;VALUE:string;data:string);
var path:string;
begin
  path:=CONF_PATH();
  if not FileExists(path) then exit;
  logs.Debuglogs('Starting......: KavMilter set '+VALUE+' to "'+data+'"');
  GLOBAL_INI:=TIniFile.Create(path);
  GLOBAL_INI.WriteString(KEY,VALUE,data);
  GLOBAL_INI.UpdateFile;
  GLOBAL_INI.Free;
end;
//#############################################################################
function tkavm4mls.PATTERN_DATE():string;
var
   BasesPath:string;
   xml:string;
   RegExpr:TRegExpr;
begin
 BasesPath:=GET_VALUE('path','BasesPath');
 if not FileExists(BasesPath + '/master.xml') then exit;
 xml:=logs.ReadFromFile(BasesPath + '/master.xml');
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='UpdateDate="([0-9]+)\s+([0-9]+)"';
 if RegExpr.Exec(xml) then begin
    result:=RegExpr.Match[1] + ';' + RegExpr.Match[2];
 end;
 RegExpr.Free;
end;
//##############################################################################
procedure tkavm4mls.STOP();
 var
    pid:string;
    count:integer;
begin
count:=0;
  if not FileExists('/opt/kav/5.6/kavmilter/bin/kavmilter') then exit();
  pid:=KAV_MILTER_PID();
  if not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Kav4Milter..........: Already stopped');
     exit;
  end;


  writeln('Stopping Kav4Milter..........: ' + pid + ' PID');
  fpsystem(BIN_PATH() + ' -r stop');

  while SYS.PROCESS_EXIST(KAV_MILTER_PID()) do begin
        sleep(100);
        inc(count);
        if count>30 then break;
  end;

  pid:=KAV_MILTER_PID();
  if not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping Kav4Milter..........: stopped');
     exit;
  end;

  logs.OutputCmd(INITD_PATH() + ' stop');

  while SYS.PROCESS_EXIST(KAV_MILTER_PID()) do begin
        sleep(100);
        inc(count);
        if count>30 then break;
  end;

  if not SYS.PROCESS_EXIST(KAV_MILTER_PID()) then begin
     writeln('Stopping Kav4Milter..........: stopped');
  end else begin
      logs.OutputCmd('/bin/kill -9 ' +KAV_MILTER_PID());
  end;
end;
//##############################################################################
FUNCTION tkavm4mls.STATUS():string;
var
   ini:TstringList;
   kavmilterEnable:string;
begin
ini:=TstringList.Create;
kavmilterEnable:=SYS.get_INFO('kavmilterEnable');
if length(kavmilterEnable)=0 then begin
   kavmilterEnable:='0';
   SYS.set_INFO('kavmilterEnable','0');
end;


   ini.Add('[KAVMILTER]');
   if FileExists('/opt/kav/5.6/kavmilter/bin/kavmilter') then  begin
      if SYS.PROCESS_EXIST(KAV_MILTER_PID()) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('master_pid='+ KAV_MILTER_PID());
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(KAV_MILTER_PID())));
      ini.Add('master_version=' + VERSION());
      ini.Add('pattern_date=' + PATTERN_DATE());
      ini.Add('pattern_version=' +PATTERN_DATE());
      ini.Add('status='+SYS.PROCESS_STATUS(KAV_MILTER_PID()));
      ini.Add('service_name=APP_KAVMILTER');
      ini.Add('start_logs=/opt/artica/logs/kav6.start');
      ini.Add('service_disabled='+kavmilterEnable);
      ini.Add('service_cmd=kavmilter');
   end;
result:=ini.Text;
ini.free
end;
//##############################################################################
function tkavm4mls.VERSION():string;
var
   RegExpr:TRegExpr;
   tmp:string;
   l:TstringList;
   i:Integer;
begin
result:=SYS.GET_CACHE_VERSION('APP_KAVMILTER');
   if length(result)>0 then exit;
   tmp:=logs.FILE_TEMP();
   if not FileExists('/opt/kav/5.6/kavmilter/bin/kavmilter') then exit;
   fpsystem('/opt/kav/5.6/kavmilter/bin/kavmilter -v >'+tmp+' 2>&1');
   if not FileExists(tmp) then exit;
   l:=TstringList.Create;
   l.LoadFromFile(tmp);
   logs.DeleteFile(tmp);
   RegExpr:=TRegExpr.Create();
   RegExpr.expression:='([0-9\.]+)';

For i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
    end;
end;


   RegExpr.Free;
     SYS.SET_CACHE_VERSION('APP_KAVMILTER',result);
end;
//##############################################################################


function tkavm4mls.PERFORM_UPDATE():string;
var
tmp:string;
RegExpr:TRegExpr;
l:TstringList;
i:Integer;
spattern_date:string;
pids:string;
ini:TiniFile;
begin
result:='';
if not FileExists('/opt/kav/5.6/kavmilter/bin/keepup2date') then exit;
logs.Debuglogs('tkavm4mls.PERFORM_UPDATE() kavmilterEnable='+SYS.GET_INFO('kavmilterEnable'));
if SYS.GET_INFO('kavmilterEnable')<>'1' then begin
   logs.Syslogs('tkavm4mls.PERFORM_UPDATE():: KavMilter is disabled.. .Skip it`s update');
   exit;
end;

pids:=SYS.PIDOF('/opt/kav/5.6/kavmilter/bin/keepup2date');
if length(pids)>0 then begin
   logs.Syslogs('tkavm4mls.PERFORM_UPDATE():: Already keepup2date instance exist ('+pids+')');
   exit;
end;

forceDirectories('/var/db/kav');
if not FileExists('/var/db/kav/applications.setup') then begin
   logs.Debuglogs('/var/db/kav/applications.setup not exists, try to create it');
   logs.OutputCmd('/bin/touch /var/db/kav/applications.setup');
end;

logs.Debuglogs('updating /var/db/kav/applications.setup');
ini:=TiniFile.Create('/var/db/kav/applications.setup');
ini.WriteString('1126','DEFAULTCONFIG','/etc/kav/5.6/kavmilter/kavmilter.conf');
ini.WriteString('1126','FILE','/opt/kav/5.6/kavmilter/bin/kavmilter');
ini.WriteString('1126','ID','1126');
ini.WriteString('1126','INSTROOT','/opt/kav/5.6/kavmilter');
ini.WriteString('1126','NAME','Kaspersky Anti-Virus for Sendmail Milter');
ini.WriteString('1126','PATH_BASES','/var/db/kav/5.6/kavmilter/bases/');
ini.WriteString('1126','PATH_LICENSES','/var/db/kav/5.6/kavmilter/licenses/');
ini.WriteString('1126','USED_PKGMGR','rpm');
ini.WriteString('1126','VERSION','5.6.20.2');
ini.UpdateFile;
ini.Free;



tmp:=logs.FILE_TEMP();
logs.Syslogs('PERFORM_UPDATE():: Starting updating Kaspersky For SendMail milter...');
logs.Debuglogs('tkavm4mls.PERFORM_UPDATE() /opt/kav/5.6/kavmilter/bin/keepup2date >' + tmp + ' 2>&1');
fpsystem(SYS.EXEC_NICE()+ '/opt/kav/5.6/kavmilter/bin/keepup2date >' + tmp + ' 2>&1');
logs.Syslogs('PERFORM_UPDATE():: updating Kaspersky For SendMail milter done...');
if not FileExists(tmp) then exit;
RegExpr:=TRegExpr.Create;
l:=TstringList.Create;
logs.Debuglogs('tkavm4mls.PERFORM_UPDATE() loading '+tmp);
logs.Debuglogs(logs.ReadFromFile(tmp));
l.LoadFromFile(tmp);
logs.DeleteFile(tmp);
For i:=0 to l.Count-1 do begin
    RegExpr.Expression:='^Error loading license: The trial license is expired';
    if RegExpr.Exec(l.Strings[i]) then begin
       logs.NOTIFICATION('[ARTICA]: ('+ SYS.HOSTNAME_g()+') Failed to update Kaspersky Milter Edition Pattern file','Your license is expired, you need to update it...'+l.Text,'update');
       break;
    end;

 RegExpr.Expression:='^Update.+?completed successfully';
    if RegExpr.Exec(l.Strings[i]) then begin
         spattern_date:=PATTERN_DATE();
         logs.NOTIFICATION('[ARTICA]: ('+ SYS.HOSTNAME_g()+') Success update Kaspersky Milter Edition Pattern file '+spattern_date,l.Text,'update');
         break;
    end;

 RegExpr.Expression:='^Failed to signal.+?No such processCommand';
    if RegExpr.Exec(l.Strings[i]) then begin
       STOP();
       START();
       spattern_date:=PATTERN_DATE();
       logs.NOTIFICATION('[ARTICA]: ('+ SYS.HOSTNAME_g()+') Success update Kaspersky Milter Edition Pattern file '+spattern_date,l.Text,'update');
       break;
    end;


end;

RegExpr.free;
l.free;
end;
//##############################################################################
procedure tkavm4mls.VerifyGroupConfiguration();
var
   i:integer;
   ini:TiniFile;
   FileName:string;
   FilePath:string;
   groupname:string;
   recipients:string;
begin
  logs.Syslogs('kavmilter_settings:: List groups in /etc/kav/5.6/kavmilter/groups.d');
  SYS.DirListFiles.Clear;
  SYS.DirFiles('/etc/kav/5.6/kavmilter/groups.d','*.conf');
  for i:=0 to SYS.DirListFiles.Count-1 do begin
        FileName:=SYS.DirListFiles.Strings[i];
        FilePath:='/etc/kav/5.6/kavmilter/groups.d/'+ FileName;
        if not FileExists(FilePath) then continue;
        ini:=TiniFile.Create(FilePath);
        groupname:=ini.ReadString('group.definition','GroupName','');
        recipients:=ini.ReadString('group.definition','Recipients','');
        if length(GroupName)=0 then begin
           logs.DebugLogs('Starting......: Kaspersky Mail server group "'+ GroupName +'" corrupted, delete it');
           logs.DeleteFile(FilePath);
           continue;
       end;

       if groupname='Default' then begin
             logs.DebugLogs('Starting......: Kaspersky Mail server group "'+ GroupName +'" skipped');
             continue;
       end;


       if length(recipients)=0 then begin
           logs.DebugLogs('Starting......: Kaspersky Mail server group "'+ GroupName +'" has no recipients..delete it');
           logs.DeleteFile(FilePath);
           continue;
       end;


  end;



end;
//##############################################################################
procedure tkavm4mls.FixDefaultConf();
var ini:TiniFile;
begin
   logs.DebugLogs('Starting......: Kaspersky Mail server Checking group "Default"');
   ini:=TiniFile.Create('/etc/kav/5.6/kavmilter/groups.d/default.conf');
   ini.WriteString('group.backup','BackupDir','/var/db/kav/5.6/kavmilter/backup/');
   ini.WriteString('group.definition','Priority','0');
   ini.WriteString('group.definition','GroupName','Default');
   ini.UpdateFile;
   ini.free;

end;
//##############################################################################




end.
