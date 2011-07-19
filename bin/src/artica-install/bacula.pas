unit bacula;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,openldap,
    RegExpr      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';



  type
  tbacula=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     FDport:string;
     DIRport:string;
     SDPort:string;
     Password_console:string;
     EnableBacula:integer;
     ldap:topenldap;
     procedure BACULA_FILE_START();
     function  BACULA_FILE_PID():string;
     function  BACULA_FD_GET_STRING(keyname:string):string;

     function  BACULA_DIRECTOR_PID():string;
     procedure DIRECTOR_CREATE_CONFIG();
     function  DIRECTOR_BIN_PATH():string;
     procedure DIRECTOR_WRITE_CONF();
     procedure BACULA_DIRECTOR_START();
     function  BACULA_DIRECTOR_GET_STRING(keyname:string):string;


     function STORAGE_BIN_PATH():string;
     function BACULA_STORAGE_GET_STRING(keyname:string):string;
     function BACULA_STORAGE_PID():string;
     procedure BACULA_STORAGE_START();



     verbose:boolean;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    VERSION():string;
    function    FD_BIN_PATH():string;
    procedure   START();
    procedure   STOP();
    procedure   GENERATE_CERTIFICATE();
END;

implementation

constructor tbacula.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       verbose:=SYS.COMMANDLINE_PARAMETERS('--verbose');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       FDport:=BACULA_FD_GET_STRING('FDport');
       DIRport:=BACULA_DIRECTOR_GET_STRING('DIRport');
       SDPort:=BACULA_STORAGE_GET_STRING('SDPort');
       ldap:=topenldap.Create;
       if not TrystrtoInt(SYS.GET_INFO('BaculaEnable'),EnableBacula) then EnableBacula:=0;
       Password_console:=SYS.GET_INFO('BaculaPasswordConsole');
       DIRport:=SYS.GET_INFO('BaculaDirectorPort');


       if length(Password_console)=0 then Password_console:=ldap.ldap_settings.password;
       if length(DIRport)=0 then DIRport:='9103';


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tbacula.free();
begin
    logs.Free;
    ldap.free;
end;
//##############################################################################
function tbacula.FD_BIN_PATH():string;
begin
   if FileExists('/usr/sbin/bacula-fd') then exit('/usr/sbin/bacula-fd');

end;
//##############################################################################
function tbacula.STORAGE_BIN_PATH():string;
begin
   if FileExists('/usr/sbin/bacula-sd') then exit('/usr/sbin/bacula-sd');

end;
//##############################################################################
function tbacula.DIRECTOR_BIN_PATH():string;
begin
   if FileExists('/usr/sbin/bacula-dir') then exit('/usr/sbin/bacula-dir');

end;
//##############################################################################
function tbacula.BACULA_FILE_PID():string;
var
   pid_path:string;
   pid:string;
begin

   if length(FDport)>0 then begin
         pid_path:='/var/run/bacula/bacula-fd.'+FDport+'.pid';
         if verbose then logs.Debuglogs('BACULA_FILE_PID: '+pid_path);
         pid:=SYS.GET_PID_FROM_PATH(pid_path);
   end;

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('BACULA_FILE_PID: '+pid+' failed');
      result:=SYS.PIDOF(FD_BIN_PATH());
      if verbose then logs.Debuglogs('BACULA_FILE_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;


end;
//##############################################################################
function tbacula.BACULA_STORAGE_PID():string;
var
   pid_path:string;
   pid:string;
begin

   if length(FDport)>0 then begin
         pid_path:='/var/run/bacula/bacula-sd.'+FDport+'.pid';
         if verbose then logs.Debuglogs('BACULA_STORAGE_PID: '+pid_path);
         pid:=SYS.GET_PID_FROM_PATH(pid_path);
   end;

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('BACULA_STORAGE_PID: '+pid+' failed');
      result:=SYS.PIDOF(STORAGE_BIN_PATH());
      if verbose then logs.Debuglogs('BACULA_STORAGE_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;


end;
//##############################################################################
function tbacula.BACULA_DIRECTOR_PID():string;
var
   pid_path:string;
   pid:string;
begin

   if length(FDport)>0 then begin
         pid_path:='/var/run/bacula/bacula-dir.'+FDport+'.pid';
         if verbose then logs.Debuglogs('BACULA_DIRECTOR_PID: '+pid_path);
         pid:=SYS.GET_PID_FROM_PATH(pid_path);
   end;

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('BACULA_DIRECTOR_PID: '+pid+' failed');
      result:=SYS.PIDOF(DIRECTOR_BIN_PATH());
      if verbose then logs.Debuglogs('BACULA_DIRECTOR_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;


end;
//##############################################################################

function tbacula.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_BACULA_FD');
if length(result)>0 then exit;

filetmp:=logs.FILE_TEMP();
if not FileExists(FD_BIN_PATH()) then exit;
fpsystem(FD_BIN_PATH()+' -V >'+filetmp+' 2>&1');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Version\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
             SYS.SET_CACHE_VERSION('APP_BACULA_FD',result);

end;
//#############################################################################

function tbacula.BACULA_FD_GET_STRING(keyname:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

if not FileExists(FD_BIN_PATH()) then exit;

 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:=keyname+'[=\s]+(.+?)\s+';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile('/etc/bacula/bacula-fd.conf');
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=trim(RegExpr.Match[1]);
             break;
        end;
    end;
    RegExpr.free;
    FileDatas.Free;


end;
//#############################################################################
function tbacula.BACULA_DIRECTOR_GET_STRING(keyname:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

if not FileExists(FD_BIN_PATH()) then exit;

 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:=keyname+'[=\s]+(.+?)\s+';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile('/etc/bacula/bacula-dir.conf');
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=trim(RegExpr.Match[1]);
             break;
        end;
    end;
    RegExpr.free;
    FileDatas.Free;


end;
//#############################################################################
function tbacula.BACULA_STORAGE_GET_STRING(keyname:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

if not FileExists(FD_BIN_PATH()) then exit;

 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:=keyname+'[=\s]+(.+?)\s+';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile('/etc/bacula/bacula-sd.conf');
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=trim(RegExpr.Match[1]);
             break;
        end;
    end;
    RegExpr.free;
    FileDatas.Free;


end;
//#############################################################################

procedure tbacula.START();
begin

if not FileExists(FD_BIN_PATH()) then begin
   logs.Debuglogs('Starting......: Bacula is not installed');
   exit;
end;

if EnableBacula=0 then begin
   logs.Debuglogs('Starting......: Bacula is not enabled');
   exit;
end;

forceDirectories('/etc/bacula/director');
forceDirectories('/etc/bacula/storage');
forceDirectories('/etc/bacula/file');
forceDirectories('/var/log/bacula');

DIRECTOR_WRITE_CONF();
DIRECTOR_CREATE_CONFIG();
BACULA_STORAGE_START();
BACULA_FILE_START();
BACULA_DIRECTOR_START();


end;
//#############################################################################
procedure tbacula.STOP();
var
   pid:string;
   cmd:string;
   count:integer;
begin

if not FileExists(FD_BIN_PATH()) then begin
   writeln('Stopping Bacula..........: Not Installed');
   exit;
end;
pid:=BACULA_FILE_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Bacula..........: File Daemon PID '+pid);
   logs.OutputCmd('/bin/kill ' + pid);
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);

      inc(count);
       if count>50 then begin
            writeln('Stopping Bacula..........: Timeout while force stopping File Daemon pid:'+pid);
            break;
       end;
       pid:=BACULA_FILE_PID();
   end;
end else begin
   writeln('Stopping Bacula..........: File Daemon Already stopped');
end;

pid:=BACULA_STORAGE_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Bacula..........: Storage Daemon PID '+pid);
   logs.OutputCmd('/bin/kill ' + pid);
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);

      inc(count);
       if count>50 then begin
            writeln('Stopping Bacula..........: Timeout while force stopping Storage Daemon pid:'+pid);
            break;
       end;
       pid:=BACULA_STORAGE_PID();
   end;
end else begin
   writeln('Stopping Bacula..........: Storage Daemon Already stopped');
end;



pid:=BACULA_DIRECTOR_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Bacula..........: Director Daemon PID '+pid);
   logs.OutputCmd('/bin/kill ' + pid);
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);

      inc(count);
       if count>50 then begin
            writeln('Stopping Bacula..........: Timeout while force stopping Director Daemon pid:'+pid);
            break;
       end;
       pid:=BACULA_DIRECTOR_PID();
   end;
end else begin
   writeln('Stopping Bacula..........: Director Daemon Already stopped');
end;

end;
//#############################################################################
procedure tbacula.BACULA_FILE_START();
var
   pid:string;
   cmd:string;
   count:integer;

begin
  forceDirectories('/var/run/bacula');
  pid:=BACULA_FILE_PID();
  if SYS.PROCESS_EXIST(pid) then begin
     logs.Debuglogs('Starting......: Bacula File daemon is already running PID '+ pid+' on port '+FDport);
     exit;
  end;

pid:=BACULA_FILE_PID();
logs.Debuglogs('Starting......: Bacula File daemon on port '+ FDport);
cmd:=FD_BIN_PATH()+' -v -c /etc/bacula/bacula-fd.conf';
logs.Debuglogs(cmd);
fpsystem(cmd+' &');

  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>30 then begin

                 logs.DebugLogs('Starting......: Bacula File daemon (timeout!!!)');
                 break;
              end;

              pid:=BACULA_FILE_PID();
        end;


pid:=BACULA_FILE_PID();

    if not SYS.PROCESS_EXIST(pid) then begin

         logs.DebugLogs('Starting......: Bacula File daemon (failed!!!)');
    end else begin

         logs.DebugLogs('Starting......: Bacula File daemon Success with new PID '+pid);
    end;



end;
//#############################################################################
procedure tbacula.BACULA_STORAGE_START();
var
   pid:string;
   cmd:string;
   count:integer;

begin
  forceDirectories('/var/run/bacula');
  pid:=BACULA_STORAGE_PID();
  if SYS.PROCESS_EXIST(pid) then begin
     logs.Debuglogs('Starting......: Bacula Storage daemon is already running PID '+ pid+' Director port '+SDPort);
     exit;
  end;

pid:=BACULA_STORAGE_PID();
logs.Debuglogs('Starting......: Bacula Storage daemon Director port '+ SDPort);
cmd:=STORAGE_BIN_PATH()+' -v -c /etc/bacula/bacula-sd.conf';
logs.Debuglogs(cmd);
fpsystem(cmd+' &');

  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>30 then begin

                 logs.DebugLogs('Starting......: Bacula Storage daemon (timeout!!!)');
                 break;
              end;

              pid:=BACULA_STORAGE_PID();
        end;


pid:=BACULA_STORAGE_PID();

    if not SYS.PROCESS_EXIST(pid) then begin

         logs.DebugLogs('Starting......: Bacula Storage daemon (failed!!!)');
    end else begin

         logs.DebugLogs('Starting......: Bacula Storage daemon Success with new PID '+pid);
    end;



end;
//#############################################################################
procedure tbacula.BACULA_DIRECTOR_START();
var
   pid:string;
   cmd:string;
   count:integer;

begin
  forceDirectories('/var/run/bacula');
  pid:=BACULA_DIRECTOR_PID();
  if SYS.PROCESS_EXIST(pid) then begin
     logs.Debuglogs('Starting......: Bacula Director daemon is already running PID '+ pid+' on port '+DIRport);
     exit;
  end;

pid:=BACULA_DIRECTOR_PID();
logs.Debuglogs('Starting......: Bacula Director daemon on port '+ DIRport);
cmd:=DIRECTOR_BIN_PATH()+' -v -c /etc/bacula/bacula-dir.conf';
logs.Debuglogs(cmd);
fpsystem(cmd+' &');

  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>30 then begin

                 logs.DebugLogs('Starting......: Bacula Director daemon (timeout!!!)');
                 break;
              end;
              pid:=BACULA_DIRECTOR_PID();
        end;


pid:=BACULA_DIRECTOR_PID();

    if not SYS.PROCESS_EXIST(pid) then begin
         logs.DebugLogs('Starting......: Bacula Director daemon (failed!!!)');
    end else begin
         logs.DebugLogs('Starting......: Bacula Director daemon Success with new PID '+pid);
    end;



end;
//#############################################################################
procedure tbacula.DIRECTOR_CREATE_CONFIG();
var
l:Tstringlist;
BaculaTlsEnable:integer;

begin

forceDirectories('/etc/bacula/director');
forceDirectories('/etc/bacula/storage');
forceDirectories('/etc/bacula/file');
forceDirectories('/var/log/bacula');

if not logs.IF_DATABASE_EXISTS('bacula') then begin
       logs.DebugLogs('Starting......: Creating database Bacula');
       logs.QUERY_SQL(pChar('CREATE DATABASE bacula'),'');
end;

if not logs.IF_TABLE_EXISTS('Storage','bacula') then begin
       logs.DebugLogs('Starting......: Creating tables for Bacula');
       logs.OutputCmd('/etc/bacula/scripts/make_mysql_tables bacula');
end;


if not tryStrToInt(SYS.GET_INFO('BaculaTlsEnable'),BaculaTlsEnable) then BaculaTlsEnable:=0;

l:=TstringList.Create;

l.Add('Storage {                             # definition of myself');
l.Add('  Name = '+SYS.HOSTNAME_g()+'-sd');
l.Add('  SDPort = '+DirPort);
l.Add('  WorkingDirectory = "/var/lib/bacula"');
l.Add('  Pid Directory = "/var/run/bacula"');
l.Add('  Maximum Concurrent Jobs = 20');
l.Add('}');

l.Add('#');
l.Add('# List Directors who are permitted to contact Storage daemon');
l.Add('# ');
l.Add('Director {');
l.Add('  Name = '+SYS.HOSTNAME_g()+'-dir');
l.Add('  Password = "'+Password_console+'" ');
l.Add('}');
logs.WriteToFile(l.Text,'/etc/bacula/storage/global.conf');
L.clear;



l.Add('Director {');
l.Add('  Name = '+SYS.HOSTNAME_g()+'-dir');
l.Add('  Password = "'+Password_console+'"');
l.Add('}');
logs.WriteToFile(l.Text,'/etc/bacula/file/global.conf');
L.clear;


l.Add('Director {');
l.Add('  Name = '+SYS.HOSTNAME_g()+'-dir');
l.Add('  DIRport = '+DirPort);
l.Add('  address = '+SYS.HOSTNAME_g());
l.Add('  Password = "'+Password_console+'"');
l.Add('}');

logs.WriteToFile(l.Text,'/etc/bacula/bconsole.conf');

end;
//#############################################################################


procedure tbacula.DIRECTOR_WRITE_CONF();
var
l:Tstringlist;
BaculaTlsEnable:integer;
root,password,port,server:string;
begin

  root    :=SYS.MYSQL_INFOS('database_admin');
  password:=SYS.MYSQL_INFOS('database_password');
  port    :=SYS.MYSQL_INFOS('port');
  server  :=SYS.MYSQL_INFOS('mysql_server');



l:=TstringList.Create;

if not logs.IF_DATABASE_EXISTS('bacula') then begin
       logs.DebugLogs('Starting......: Creating database Bacula');
       logs.QUERY_SQL(pChar('CREATE DATABASE bacula'),'');
end;

if not logs.IF_TABLE_EXISTS('Storage','bacula') then begin
       logs.DebugLogs('Starting......: Creating tables for Bacula');
       logs.OutputCmd('/etc/bacula/scripts/make_mysql_tables bacula');
end;


if not tryStrToInt(SYS.GET_INFO('BaculaTlsEnable'),BaculaTlsEnable) then BaculaTlsEnable:=0;

l:=TstringList.Create;
l.Add('Director {                            # define myself');
l.Add('  Name = '+SYS.HOSTNAME_g()+'-dir');
l.Add('  DIRport = '+DirPort+'                # where we listen for UA connections');
l.Add('  QueryFile = "/etc/bacula/scripts/query.sql"');
l.Add('  WorkingDirectory = "/var/lib/bacula"');
l.Add('  PidDirectory = "/var/run/bacula"');
l.Add('  Maximum Concurrent Jobs = 5');
l.Add('  Password = "'+Password_console+'" # Console password');
l.Add('  Messages = Daemon');
if BaculaTlsEnable=1 then begin
   l.Add('  TLS Enable = yes');
   l.Add('  TLS Require = yes');
   l.Add('  TLS Verify Peer = yes');
   l.Add('  TLS Allowed CN = "artica-postfix"');
   l.Add('  TLS CA Certificate File = /etc/bacula/certs/cacert.pem');
   l.Add('  TLS Certificate = /etc/bacula/certs/bacula.crt');
   l.Add('  TLS Key = /etc/bacula/certs/bacula.key');
end;
l.Add('}');
l.Add('');
l.Add('Catalog {');
l.Add('  Name = MySQL');
l.Add('  dbname = bacula');
l.Add('  user = '+root);
l.Add('  password = "'+password+'"');
l.Add('  DB Address = '+server);
l.Add('  DB Port = '+port);
l.Add('}');
l.Add('');
l.Add('');
l.Add('Client {');
l.Add('  Name = '+SYS.HOSTNAME_g()+'-fd');
l.Add('  Address = '+SYS.HOSTNAME_g());
l.Add('  FDPort = 9102');
l.Add('  Catalog = MySQL');
l.Add('  Password = "'+Password_console+'"          # password for FileDaemon');
l.Add('  File Retention = 30 days            # 30 days');
l.Add('  Job Retention = 6 months            # six months');
l.Add('  AutoPrune = yes                     # Prune expired Jobs/Files');
l.Add('}');
l.Add('');
l.Add('Messages { ');
l.Add('  Name = Daemon');
l.Add('  mailcommand = "/usr/sbin/bsmtp -h localhost -f \"\(Bacula\) \<%r\>\" -s \"Bacula daemon message\" %r"');
l.Add('  mail = root@localhost = all, !skipped');
l.Add('  console = all, !skipped, !saved');
l.Add('  append = "/var/log/bacula/director.log" = all, !skipped');
l.Add('}');
logs.WriteToFile(l.Text,'/etc/bacula/director/global.conf');
L.free;
end;






procedure tbacula.GENERATE_CERTIFICATE();
var
   openssl:string;
   cf_path:string;
   cmd,CertificateMaxDays:string;
   tmpstr:string;
begin


CertificateMaxDays:=SYS.GET_INFO('CertificateMaxDays');
if length(CertificateMaxDays)=0 then CertificateMaxDays:='730';



 forcedirectories('/etc/ssl/certs/bacula');
 forcedirectories('/opt/artica/tmp');


 if FileExists('/etc/ssl/certs/bacula/bacula.key') then begin
    logs.Debuglogs('/etc/ssl/certs/bacula/bacula.key OK... finish');
    exit;
 end;

  SYS.OPENSSL_CERTIFCATE_CONFIG();
  openssl:=SYS.OPENSSL_TOOL_PATH();
  cf_path:=SYS.OPENSSL_CONFIGURATION_PATH();


 if not FileExists(openssl) then begin
    logs.logs('GENERATE_CERTIFICATE():: FATAL ERROR, Unable to stat openssl ');
    exit;
 end;

 if not FileExists(FD_BIN_PATH()) then begin
    logs.logs('GENERATE_CERTIFICATE():: bacula is not installed...');
    exit;
 end;


 if not FileExists(cf_path) then begin
    logs.logs('GENERATE_CERTIFICATE():: FATAL ERROR, Unable to stat configuration file ');
    exit;
 end;


 tmpstr:=logs.FILE_TEMP();
 logs.WriteToFile(ldap.ldap_settings.password,tmpstr);


 ///etc/artica-postfix/ssl.certificate.conf



  cmd:=openssl +' req -nodes -new -x509 -config '+cf_path+' -keyout /opt/artica/tmp/aaron-ca.key -out /opt/artica/tmp/aaron-ca.crt';
  fpsystem(cmd);

  cmd:=openssl +' req -nodes -new -config '+cf_path+' -keyout /opt/artica/tmp/aaron.key -out /opt/artica/tmp/aaron.csr';
  fpsystem(cmd);

  cmd:=openssl +' ca -batch -config '+cf_path+' -cert /opt/artica/tmp/aaron-ca.crt -keyfile /opt/artica/tmp/aaron-ca.key -out /opt/artica/tmp/aaron.crt -in /opt/artica/tmp/aaron.csr';
  fpsystem(cmd);

  forceDirectories('/etc/bacula/certs');
  if FileExists('/etc/ssl/certs/new/01.pem') then fpsystem('/bin/cp /etc/ssl/certs/new/01.pem /etc/bacula/certs/cacert.pem');
  if FileExists('/opt/artica/tmp/aaron.crt') then fpsystem('/bin/cp /opt/artica/tmp/aaron.crt /etc/bacula/certs/bacula.crt');
  if FileExists('/opt/artica/tmp/aaron.key') then fpsystem('/bin/cp /opt/artica/tmp/aaron.key /etc/bacula/certs/bacula.key');
  fpsystem('/bin/rm -rf /opt/artica/tmp');
end;
//#############################################################################



end.
