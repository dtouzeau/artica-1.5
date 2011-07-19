unit CyrBackup;
{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,cyrus,openldap,mysql_daemon,dateutils;

  type
  Tcyrback=class


private
     LOGS:Tlogs;
     iniev:TiniFIle;
     SYS:Tsystem;
     cyrus:tCyrus;
     partitiondefault:string;
     configdirectory:string;
     rsyncpath:string;
     source_dir:string;
     date_start:string;
     logs_path:string;
     notif:Tstringlist;
     databases_list:Tstringlist;
     COuntFiles:integer;
     cp_path:string;
     cp_commands:string;
     source_mount:string;
     no_stop_cyrus:boolean;
     ItIsRsyncProto:boolean;
     rsync_verbose:boolean;
     folder_recursive:boolean;
     procedure IncrementNotifs(PathLogs:string;title:string);
     function  rsync_protocol(DestinationPath:string):string;
     procedure GetMysqlDatabases();
     procedure rsync_LDAP();
     procedure rsync_MYSQL();
     procedure rsync_Artica();
public
      procedure   Free;
      constructor Create;

      procedure rsync_local_start(local_mount:string);
      procedure rsync_local();


      procedure rsync_cyrus(DestinationPath:string);
      procedure rsync_OPENLDAP(DestinationPath:string);
      procedure rsync_MYSQL_DATABASES(DestinationPath:string);
      procedure rsync_Artica_settings(DestinationPath:string);
      procedure rsync_folder(source:string;DestinationPath:string);
end;

implementation

constructor Tcyrback.Create;
begin

       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       cyrus:=tcyrus.Create(SYS);
       partitiondefault:=cyrus.IMAPD_GET('partition-default');
       configdirectory:=cyrus.IMAPD_GET('configdirectory');
       rsyncpath:='/usr/bin/rsync';
       date_start:=logs.DateTimeNowSQL();
       logs_path:=logs.FILE_TEMP();
       notif:=Tstringlist.Create;
       databases_list:=Tstringlist.Create;
       cp_path:=SYS.LOCATE_GENERIC_BIN('rsync');
       ItIsRsyncProto:=false;
       no_stop_cyrus:=false;
       folder_recursive:=false;


       rsync_verbose:=logs.COMMANDLINE_PARAMETERS('--verbose');
       no_stop_cyrus:=logs.COMMANDLINE_PARAMETERS('--no-cyrus-stop');
       folder_recursive:=logs.COMMANDLINE_PARAMETERS('--recursive');


       cp_commands:=' -ar ';
       if not FileExists(cp_path) then begin
          cp_path:=SYS.LOCATE_GENERIC_BIN('cp');
          cp_commands:=' -u -r ';
       end;

       if logs.COMMANDLINE_PARAMETERS('--rsync-folder') then begin
          cp_commands:=' -a ';
          if not FileExists(cp_path) then begin
             cp_path:=SYS.LOCATE_GENERIC_BIN('cp');
             cp_commands:=' -u ';
          end;
       end;
end;
//##############################################################################
procedure Tcyrback.free();
begin
    logs.Free;
    SYS.Free;
    notif.free;
    databases_list.free;
end;
//##############################################################################
procedure Tcyrback.rsync_local_start(local_mount:string);
var
   ini:TiniFile;
   CONTAINER:string;
   MAX_CONTAINERS:integer;
   BACKUP_MAILBOXES:integer;
   BACKUP_DATABASES:Integer;
   BACKUP_ARTICA:integer;
   MyDate:string;
   Size:string;
begin
source_mount:=local_mount;
    if not FileExists('/etc/artica-postfix/settings/Daemons/CyrusBackupRessource') then begin
       logs.Debuglogs('rsync_local_start:: unable to stat configuration file');
       exit;
    end;



if not FileExists(rsyncpath) then begin
   logs.Debuglogs('rsync_local_start:: Unable to locate rsync bin path' );
   logs.BACKUP_EVENTS('Unable to locate rsync bin path','ALL',local_mount,0);
   exit;
end;

logs.BACKUP_EVENTS('Starting backup...','ALL',local_mount,0);

ini:=TiniFile.Create('/etc/artica-postfix/settings/Daemons/CyrusBackupRessource');
CONTAINER:=ini.ReadString(local_mount,'CONTAINER','D');
MAX_CONTAINERS:=ini.ReadInteger(local_mount,'MAX_CONTAINERS',3);
BACKUP_MAILBOXES:=ini.ReadInteger(local_mount,'BACKUP_MAILBOXES',1);
BACKUP_DATABASES:=ini.ReadInteger(local_mount,'BACKUP_DATABASES',1);
BACKUP_ARTICA:=ini.ReadInteger(local_mount,'BACKUP_ARTICA',1);

if CONTAINER='D' then begin
   logs.Debuglogs('rsync_local_start:: Daily backup enabled');
   MyDate:='backup.'+FormatDateTime('yyyy-mm-dd', Now);
end;

if CONTAINER='W' then begin
   logs.Debuglogs('rsync_local_start:: Weekly backup enabled');
   MyDate:='backup.'+FormatDateTime('yyyy', Now)+'-'+IntTOStr(WeekOfTheYear(Now));
end;

source_dir:='/automounts/'+local_mount+'/'+Mydate+'/'+SYS.HOSTNAME_g();
logs.Debuglogs('rsync_local_start:: Base Path: '+source_dir);

try
   logs.Debuglogs('rsync_local_start:: testing '+source_dir);
   forceDirectories(source_dir);
except
 logs.Debuglogs('rsync_local_start:: Unable to Create directory : "'+source_dir+'"');
 logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): backup Unable to Create directory','Unable to Create directory : "'+source_dir+'"','system');
 logs.BACKUP_EVENTS('Unable to Create directory : "'+source_dir+'"','ALL',local_mount,0);

 exit;
end;


if not DirectoryExists(source_dir) then begin
     logs.Debuglogs('rsync_local_start:: Unable to Create directory : "'+source_dir+'"');
     logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): backup Unable to Create directory','Unable to Create directory : "'+source_dir+'"','system');
     logs.BACKUP_EVENTS('Unable to Create directory : "'+source_dir+'"','ALL',local_mount,0);
     exit;
end;


if BACKUP_MAILBOXES=1 then begin
     logs.Debuglogs('rsync_local_start:: -> rsync_local (backup mailboxes)');
     rsync_local();
     logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): '+ IntTostr(COuntFiles) +' Mailboxes files backuped',notif.Text,'system');
      logs.BACKUP_EVENTS(notif.Text,'MAILBOXES',local_mount,1);
     COuntFiles:=0;
     notif.Clear;
end;

if BACKUP_DATABASES=1 then begin
     logs.Debuglogs('rsync_local_start:: -> rsync_LDAP');
     rsync_LDAP();
     logs.Debuglogs('rsync_local_start:: -> rsync_MYSQL');
     rsync_MYSQL();
     COuntFiles:=0;
     notif.Clear;
end;


if BACKUP_ARTICA=1 then begin
     logs.Debuglogs('rsync_local_start:: -> rsync_Artica (backup artica settings)');
     rsync_Artica();
      logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): '+ IntTostr(COuntFiles) +' Artica configurations files backuped',notif.Text,'system');
      logs.BACKUP_EVENTS(notif.Text,'ARTICA',local_mount,1);
     COuntFiles:=0;
     notif.Clear;
end;



size:=SYS.FOLDER_SIZE_HUMAN(source_dir);
logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): backup finish:'+size,'#','system');
logs.BACKUP_EVENTS('backup terminated :'+size,'ALL',local_mount,0);


end;
//##############################################################################
function Tcyrback.rsync_protocol(DestinationPath:string):string;
var
   RegExpr:TRegExpr;
   md:string;
   cmd,verbose_cmd:string;
   username,password,server,module,path:string;
begin
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^rsync:\/\/';
  if not RegExpr.Exec(DestinationPath) then begin
     RegExpr.free;
     result:=DestinationPath;
     exit;
  end;
  if rsync_verbose then verbose_cmd:=' -v --progress ';
  ItIsRsyncProto:=true;
  RegExpr.Expression:='^rsync:\/\/(.+?):(.+?)@(.+?)\/(.+?)\/(.+)';
  if RegExpr.Exec(DestinationPath) then begin
       ForceDirectories('/opt/artica/passwords');
       username:=RegExpr.Match[1];
       password:=RegExpr.Match[2];
       server:=RegExpr.Match[3];
       module:=RegExpr.Match[4];
       path:=RegExpr.Match[5];
       logs.Debuglogs('rsync username.........:'+username);
       logs.Debuglogs('rsync server...........:'+server);
       logs.Debuglogs('rsync module...........:'+module);
       logs.Debuglogs('rsync remote path......:'+path);


       md:=logs.MD5FromString(password);
       logs.WriteToFile(password,'/opt/artica/passwords/'+md);
       fpsystem('/bin/chmod 600 /opt/artica/passwords/'+md);
       DestinationPath:=verbose_cmd+'--password-file=/opt/artica/passwords/'+md+' rsync://'+username+'@'+server+'/'+module+'/'+path;
       RegExpr.free;
       result:=DestinationPath;
       forceDirectories('/opt/artica/backup/rsynctmp/'+path);
       cmd:=cp_path+cp_commands+verbose_cmd+' "/opt/artica/backup/rsynctmp/" --password-file=/opt/artica/passwords/'+md+' rsync://'+username+'@'+server+'/'+module+'/';
       logs.Debuglogs(cmd);
       fpsystem(cmd);
       fpsystem('/bin/rm -rf /opt/artica/backup/rsynctmp');
      exit;
  end;

  RegExpr.Expression:='^rsync:\/\/(.+?)\/(.+?)\/(.+)';
  if RegExpr.Exec(DestinationPath) then begin
       ForceDirectories('/opt/artica/passwords');
       server:=RegExpr.Match[1];
       module:=RegExpr.Match[2];
       path:=RegExpr.Match[3];
       logs.Debuglogs('rsync server...........:'+server);
       logs.Debuglogs('rsync module...........:'+module);
       logs.Debuglogs('rsync remote path......:'+path);
       DestinationPath:=verbose_cmd+' rsync://'+server+'/'+module+'/'+path;
       RegExpr.free;
       result:=DestinationPath;
       forceDirectories('/opt/artica/backup/rsynctmp/'+path);
       cmd:=cp_path+cp_commands+verbose_cmd+' "/opt/artica/backup/rsynctmp/" --password-file=/opt/artica/passwords/'+md+' rsync://'+username+'@'+server+'/'+module+'/';
       logs.Debuglogs(cmd);
       fpsystem(cmd);
       fpsystem('/bin/rm -rf /opt/artica/backup/rsynctmp');
      exit;
  end;






RegExpr.free;
result:=DestinationPath;
end;
//##############################################################################
procedure Tcyrback.rsync_folder(source:string;DestinationPath:string);
var
   date_start,date_stop:string;
   commandlines,internal_DestinationPath,recursive:string;
   logs_path:string;
   RegExpr:TRegExpr;
   l:Tstringlist;
   i:integer;
   pid:string;
   quotes:string;
begin
date_start:=logs.DateTimeNowSQL();
internal_DestinationPath:=rsync_protocol(DestinationPath+'/folders'+source);
logs.Debuglogs('rsync_folder:: resource is: '+internal_DestinationPath);
if not ItIsRsyncProto then forceDirectories(internal_DestinationPath);


   logs.Debuglogs('#############################');
   logs.Debuglogs('##                         ##');
   logs.Debuglogs(source);
   logs.Debuglogs('##                         ##');
   logs.Debuglogs('#############################');

logs_path:='/root/artica-logs';
if folder_recursive then  begin
   recursive:=' -r ';
   logs.Debuglogs('rsync_folder:: recursive mode is enabled');
end;

quotes:='"';
if ItIsRsyncProto then quotes:='';

commandlines:=cp_path+cp_commands+recursive+'"'+source+'/" '+quotes+internal_DestinationPath+quotes+' >'+logs_path+' 2>&1';
logs.Debuglogs(commandlines);
pid:=SYS.PIDOF_PATTERN(cp_path+'.+?'+internal_DestinationPath);
if not SYS.PROCESS_EXIST(pid) then begin
   fpsystem(commandlines);
   if rsync_verbose then writeln(logs.ReadFromFile(logs_path));
   IncrementNotifs(logs_path,source+':');
end else begin
    logs.BACKUP_EVENTS('Folder already task running pid  :' +pid+' SKIP',source,DestinationPath,0);
    exit;
end;

date_stop:=logs.DateTimeNowSQL();
try
   notif.Add('--------------------------------------------------------');
   notif.Add('Started aT : '+date_start+' end at : '+date_stop);
   notif.Add('--------------------------------------------------------');
   logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Backuped Directory '+source,notif.Text,'system');
finally
end;
logs.BACKUP_EVENTS('Folder Backup terminated',source,DestinationPath,1);


end;
//##############################################################################

procedure Tcyrback.rsync_cyrus(DestinationPath:string);
var
   date_start,date_stop:string;
   commandlines,internal_DestinationPath:string;
   logs_path:string;
   RegExpr:TRegExpr;
   l:Tstringlist;
   i:integer;
   pid:string;
   quotes:string;
begin
date_start:=logs.DateTimeNowSQL();
internal_DestinationPath:=rsync_protocol(DestinationPath+'/cyrus-imap');
if not ItIsRsyncProto then forceDirectories(internal_DestinationPath);
logs.Debuglogs('rsync_cyrus:: resource is: '+internal_DestinationPath);
logs_path:='/root/artica-logs';
quotes:='"';
if ItIsRsyncProto then quotes:='';

if not DirectoryExists(partitiondefault) then begin
  logs.Debuglogs('rsync_cyrus:: Unable to stat partition default: "'+partitiondefault+'"');
  exit;
end;

if not DirectoryExists(configdirectory) then begin
   logs.Debuglogs('rsync_cyrus:: Unable to stat config-directory default: "'+configdirectory+'"');
   logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Cyrus backup Unable to stat config-directory ','Unable to stat directory : "'+configdirectory+'"','system');
   exit;
end;


if not ItIsRsyncProto then begin
   try
     forceDirectories(internal_DestinationPath+'/partitiondefault');
   except
   end;

   if not DirectoryExists(internal_DestinationPath+'/partitiondefault') then begin
       logs.Debuglogs('rsync_cyrus:: Unable to create "'+internal_DestinationPath+'/partitiondefault"');
       logs.BACKUP_EVENTS('Mailboxes: Unable to create remote directory',partitiondefault,internal_DestinationPath,1);
       logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Cyrus backup Unable to create directory ','Unable to create directory : "'+internal_DestinationPath+'/partitiondefault"','system');
       exit;
   end;
end;

if not ItIsRsyncProto then begin
   try
      forceDirectories(internal_DestinationPath+'/configdirectory');
      except
      end;
      if not DirectoryExists(internal_DestinationPath+'/configdirectory') then begin
         logs.Debuglogs('rsync_backup:: Unable to create "'+internal_DestinationPath+'/configdirectory"');
         logs.BACKUP_EVENTS('Mailboxes: Unable to create remote directory',configdirectory,internal_DestinationPath,1);
         logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Cyrus backup Unable to create directory ','Unable to create directory : "'+internal_DestinationPath+'/configdirectory"','system');
         exit;
      end;
end;


if not no_stop_cyrus then begin
   logs.Debuglogs('rsync_backup:: stopping cyrus-imap');
   cyrus.CYRUS_DAEMON_STOP();
end;

commandlines:=cp_path+cp_commands+'"'+partitiondefault+'" '+quotes+internal_DestinationPath+'/partitiondefault'+quotes+' >'+logs_path+' 2>&1';
logs.Debuglogs(commandlines);
pid:=SYS.PIDOF_PATTERN(cp_path+'.+?'+partitiondefault);
if not SYS.PROCESS_EXIST(pid) then begin
   fpsystem(commandlines);
   IncrementNotifs(logs_path,'Partition default backup:');
   logs.BACKUP_EVENTS('Mailboxes: mailboxes backup terminated',partitiondefault,internal_DestinationPath,1);
end else begin
    logs.BACKUP_EVENTS('mailboxes backup already running pid  :' +pid,'MAILBOXES',source_mount,0);
    exit;
end;

commandlines:=cp_path+cp_commands+'"'+configdirectory+'" '+quotes+internal_DestinationPath+'/configdirectory'+quotes+' >'+logs_path+' 2>&1';


pid:=SYS.PIDOF_PATTERN(cp_path+'.+?'+configdirectory);
if not SYS.PROCESS_EXIST(pid) then begin
   fpsystem(commandlines);
   IncrementNotifs(logs_path,'Config directory backup:');
   logs.BACKUP_EVENTS('Mailboxes: mailboxes indexes backup terminated',configdirectory,internal_DestinationPath,1);
end else begin
    logs.BACKUP_EVENTS('mailboxes backup already running pid  :' +pid,'MAILBOXES',source_mount,0);
    exit;
end;


cyrus.CYRUS_DAEMON_START();

if not ItIsRsyncProto then begin
   fpsystem('su - cyrus -c "'+SYS.LOCATE_ctl_mboxlist()+' -d" >'+internal_DestinationPath+'/mailboxlist.txt');
end else begin
   fpsystem('su - cyrus -c "'+SYS.LOCATE_ctl_mboxlist()+' -d" >/tmp/mailboxlist.txt');
   commandlines:=cp_path+cp_commands+'"/tmp/mailboxlist.txt" '+quotes+internal_DestinationPath+'/'+quotes+' >'+logs_path+' 2>&1';
   logs.DeleteFile('/tmp/mailboxlist.txt');
end;
logs.BACKUP_EVENTS('Mailboxes: mailboxes list text backup terminated','mailboxlist.txt',internal_DestinationPath,1);
date_stop:=logs.DateTimeNowSQL();
try
   notif.Add('--------------------------------------------------------');
   notif.Add('Started aT : '+date_start+' end at : '+date_stop);
   notif.Add('--------------------------------------------------------');
   logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Backup Mailboxes success',notif.Text,'system');
finally
end;

end;
//##############################################################################
procedure Tcyrback.rsync_local();
var
   date_start,date_stop:string;
   commandlines,internal_source_dir:string;
   logs_path:string;
   RegExpr:TRegExpr;
   l:Tstringlist;
   i:integer;
   pid:string;
begin

internal_source_dir:=source_dir+'/cyrus-imap';
forceDirectories(internal_source_dir);
logs.Debuglogs('rsync_backup:: resource is: '+internal_source_dir);
logs_path:='/root/artica-logs';
if not DirectoryExists(partitiondefault) then begin
   logs.Debuglogs('rsync_backup:: Unable to stat partition default: "'+partitiondefault+'"');
   exit;
end;

if not DirectoryExists(configdirectory) then begin
   logs.Debuglogs('rsync_backup:: Unable to stat config-directory default: "'+configdirectory+'"');
   logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Cyrus backup Unable to stat config-directory ','Unable to stat directory : "'+configdirectory+'"','system');
   exit;
end;

try
   forceDirectories(internal_source_dir+'/partitiondefault');
except
end;

if not DirectoryExists(internal_source_dir+'/partitiondefault') then begin
   logs.Debuglogs('rsync_backup:: Unable to create "'+internal_source_dir+'/partitiondefault"');
   logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Cyrus backup Unable to create directory ','Unable to create directory : "'+internal_source_dir+'/partitiondefault"','system');
   exit;
end;

try
   forceDirectories(internal_source_dir+'/configdirectory');
except
end;

if not DirectoryExists(internal_source_dir+'/configdirectory') then begin
   logs.Debuglogs('rsync_backup:: Unable to create "'+internal_source_dir+'/configdirectory"');
   logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Cyrus backup Unable to create directory ','Unable to create directory : "'+internal_source_dir+'/configdirectory"','system');
   exit;
end;

logs.Debuglogs('rsync_backup:: stopping cyrus-imap');
cyrus.CYRUS_DAEMON_STOP();

commandlines:=cp_path+cp_commands+'"'+partitiondefault+'" "'+internal_source_dir+'/partitiondefault" >'+logs_path+' 2>&1';
logs.Debuglogs(commandlines);
pid:=SYS.PIDOF_PATTERN(cp_path+'.+?'+partitiondefault);
if not SYS.PROCESS_EXIST(pid) then begin
   fpsystem(commandlines);
   IncrementNotifs(logs_path,'Partition default backup:');
end else begin
    logs.BACKUP_EVENTS('mailboxes backup already running pid  :' +pid,'MAILBOXES',source_mount,0);
    exit;
end;




commandlines:=cp_path+cp_commands+'"'+configdirectory+'" "'+internal_source_dir+'/configdirectory" >'+logs_path+' 2>&1';

pid:=SYS.PIDOF_PATTERN(cp_path+'.+?'+configdirectory);
if not SYS.PROCESS_EXIST(pid) then begin
   fpsystem(commandlines);
   IncrementNotifs(logs_path,'Config directory backup:');
end else begin
    logs.BACKUP_EVENTS('mailboxes backup already running pid  :' +pid,'MAILBOXES',source_mount,0);
    exit;
end;

date_stop:=logs.DateTimeNowSQL();
cyrus.CYRUS_DAEMON_START();
fpsystem('su - cyrus -c "'+SYS.LOCATE_ctl_mboxlist()+' -d" >'+internal_source_dir+'/mailboxlist.txt');
logs.BACKUP_EVENTS('mailboxes backup terminated :'+SYS.FOLDER_SIZE_HUMAN(internal_source_dir),'MAILBOXES',source_mount,0);




end;
//##############################################################################
procedure Tcyrback.rsync_Artica();
var
   date_start,date_stop:string;
   commandlines,internal_source_dir:string;
   logs_path:string;
begin

internal_source_dir:=source_dir+'/etc-artica-postfix';
forceDirectories(internal_source_dir);
logs.Debuglogs('rsync_Artica:: resource is: '+internal_source_dir);
logs_path:=logs.FILE_TEMP();
commandlines:=cp_path+cp_commands+'"/etc/artica-postfix" "'+internal_source_dir+'" >'+logs_path+' 2>&1';
logs.Debuglogs(commandlines);
fpsystem(commandlines);
IncrementNotifs(logs_path,'Artica configurations files:');
end;
//##############################################################################
procedure Tcyrback.rsync_LDAP();
var
   cmd:string;
   ldap:Topenldap;
   suffix:string;
   internal_source_dir:string;
   size:integer;
begin
   logs.Debuglogs('#############################');
   logs.Debuglogs('##                         ##');
   logs.Debuglogs('##          LDAP           ##');
   logs.Debuglogs('##                         ##');
   logs.Debuglogs('#############################');

   ldap:=topenldap.Create;
   suffix:=ldap.get_LDAP('suffix');
   internal_source_dir:=source_dir+'/ldap_backup';
   forceDirectories(internal_source_dir);

   fpsystem(SYS.LOCATE_SLAPCAT()+' -l '+internal_source_dir+'/ldap.ldif');
   fpsystem('/bin/cp '+ ldap.SLAPD_CONF_PATH()+' '+internal_source_dir+'/slapd.conf');
   logs.Debuglogs('Saving suffix ' + suffix);
   logs.WriteToFile(suffix,internal_source_dir+'/ldap.suffix');
   size:=SYS.FileSize_ko(internal_source_dir+'/ldap.ldif');
   logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): LDAP Database backuped '+IntToStr(size)+'Ko file','Saved in '+internal_source_dir+'/ldap.ldif','system');


end;
//##############################################################################
procedure Tcyrback.rsync_Artica_settings(DestinationPath:string);
var
   date_start,date_stop:string;
   commandlines,internal_DestinationPath:string;
   logs_path:string;
begin

internal_DestinationPath:=rsync_protocol(DestinationPath+'/etc-artica-postfix');
if not ItIsRsyncProto then forceDirectories(internal_DestinationPath);
logs.Debuglogs('rsync_Artica:: resource is: '+internal_DestinationPath);
logs_path:=logs.FILE_TEMP();
commandlines:=cp_path+cp_commands+'"/etc/artica-postfix" "'+internal_DestinationPath+'" >'+logs_path+' 2>&1';
logs.Debuglogs(commandlines);
fpsystem(commandlines);
logs.BACKUP_EVENTS('Artica settings backup terminated','/etc/artica-postfix',internal_DestinationPath,1);
IncrementNotifs(logs_path,'Artica configurations files:');
end;
//##############################################################################
procedure Tcyrback.rsync_OPENLDAP(DestinationPath:string);
var
   cmd:string;
   ldap:Topenldap;
   suffix:string;
   internal_DestinationPath,logs_path,commandlines:string;
   size:integer;
   quotes:string;
begin
   logs.Debuglogs('#############################');
   logs.Debuglogs('##                         ##');
   logs.Debuglogs('##          LDAP           ##');
   logs.Debuglogs('##                         ##');
   logs.Debuglogs('#############################');
   quotes:='"';

   ldap:=topenldap.Create;
   suffix:=ldap.get_LDAP('suffix');
   internal_DestinationPath:=rsync_protocol(DestinationPath+'/ldap_backup');
   if not ItIsRsyncProto then begin
      forceDirectories(internal_DestinationPath);
      fpsystem(SYS.LOCATE_SLAPCAT()+' -l '+internal_DestinationPath+'/ldap.ldif');
      fpsystem('/bin/cp '+ ldap.SLAPD_CONF_PATH()+' '+internal_DestinationPath+'/slapd.conf');
      logs.Debuglogs('Saving suffix ' + suffix);
      logs.WriteToFile(suffix,internal_DestinationPath+'/ldap.suffix');
      size:=SYS.FileSize_ko(internal_DestinationPath+'/ldap.ldif');
      logs.BACKUP_EVENTS('LDAP DATABASES backup terminated','LDAP DB',internal_DestinationPath,1);
      logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): LDAP Database backuped '+IntToStr(size)+'Ko file','Saved in '+internal_DestinationPath+'/ldap.ldif','system');
   end else begin
       forceDirectories('/opt/artica/tmp-ldap');
       fpsystem(SYS.LOCATE_SLAPCAT()+' -l /opt/artica/tmp-ldap/ldap.ldif');
       fpsystem('/bin/cp '+ ldap.SLAPD_CONF_PATH()+' /opt/artica/tmp-ldap/slapd.conf');
       logs.WriteToFile(suffix,'/opt/artica/tmp-ldap/ldap.suffix');
       logs_path:=logs.FILE_TEMP();
       commandlines:=cp_path+cp_commands+'"/opt/artica/tmp-ldap/" '+internal_DestinationPath+' >'+logs_path+' 2>&1';
       logs.Debuglogs(commandlines);
       fpsystem(commandlines);
       logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): LDAP Database backuped',logs.ReadFromFile(logs_path),'system');
       logs.BACKUP_EVENTS('LDAP DATABASES backup terminated','LDAP DB',internal_DestinationPath,1);
       logs.DeleteFile(logs_path);
   end;



end;
//##############################################################################
procedure Tcyrback.IncrementNotifs(PathLogs:string;title:string);
var
   l:Tstringlist;
   RegExpr:TRegExpr;
   i:integer;
begin

l:=Tstringlist.Create;
l.LoadFromFile(PathLogs);
RegExpr:=TRegExpr.Create;

notif.add('');
notif.Add('-----------------------------------------------------');
notif.Add(title);
for i:=0 to l.Count-1 do begin
  RegExpr.Expression:='^(Number|Total|sent)';
  logs.Debuglogs(l.Strings[i]);
  if RegExpr.Exec(l.Strings[i]) then notif.Add(l.Strings[i]);
  RegExpr.Expression:='Number of files transferred: ([0-9]+)';
  if RegExpr.Exec(l.Strings[i]) then COuntFiles:=StrToInt(RegExpr.Match[1]);
end;
 logs.DeleteFile(PathLogs);
 RegExpr.free;
 l.free;
end;
//##############################################################################
procedure Tcyrback.GetMysqlDatabases();
var
   mysql:tmysql_daemon;
   DataDir:string;
   i:integer;
begin

mysql:=tmysql_daemon.Create(SYS);
DataDir:=mysql.SERVER_PARAMETERS('datadir');
SYS.DirDir(DataDir);
for i:=0 to SYS.DirListFiles.Count-1 do begin
    databases_list.Add(SYS.DirListFiles.Strings[i]);

end;

end;
//##############################################################################
procedure Tcyrback.rsync_MYSQL_DATABASES(DestinationPath:string);
var
   mysql_password:string;
   mysql_user:string;
   internal_DestinationPath,db,cmd,final_size,logs_path,commandlines:string;
   temporarySourceDir:string;
   i:Integer;
begin


if not FileExists(SYS.LOCATE_MYSHOTCOPY()) then begin
      logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Unable to find mysqlHotCopy tool','Mysql databases backup could not be performed','system');
      logs.BACKUP_EVENTS('Unable to find mysqlHotCopy tool','MYSQL DB',source_mount,0);
      exit;
end;

        internal_DestinationPath:=rsync_protocol(DestinationPath+'/mysql_backup');
        if not ItIsRsyncProto then forceDirectories(internal_DestinationPath);

        GetMysqlDatabases();
        mysql_password:=SYS.MYSQL_INFOS('database_password');
        mysql_user:=SYS.MYSQL_INFOS('database_admin');
        if length(mysql_password)>0 then mysql_password:=' --password='+mysql_password;
        temporarySourceDir:='/home/mysqlhotcopy';
        ForceDirectories(temporarySourceDir);
        for i:=0 to databases_list.Count-1 do begin
            db:=databases_list.Strings[i];
            cmd:=SYS.LOCATE_MYSHOTCOPY()+' --quiet --addtodest --user='+mysql_user+mysql_password +' '+db+' '+temporarySourceDir;
            logs.OutputCmd(cmd);
        end;

        final_size:=SYS.FOLDER_SIZE_HUMAN(temporarySourceDir);

         if not ItIsRsyncProto then begin
            SetCurrentDir(temporarySourceDir);
            cmd:='tar -cjf '+internal_DestinationPath+'/mysql-databases.tgz *';
            logs.OutputCmd(cmd);
         end else begin
             logs_path:=logs.FILE_TEMP();
             commandlines:=cp_path+cp_commands+'"'+temporarySourceDir+'/" '+internal_DestinationPath+' >'+logs_path+' 2>&1';
             logs.Debuglogs(commandlines);
             fpsystem(commandlines);
             if rsync_verbose then writeln(logs.ReadFromFile(logs_path));
             logs.DeleteFile(logs_path);
         end;

         fpsystem('/bin/rm -rf '+temporarySourceDir+'/*');
         logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): '+IntToStr(databases_list.Count)+' Mysql Databases backuped ','Databases: '+databases_list.Text,'system');
         logs.BACKUP_EVENTS('Mysql backup terminated :'+final_size,'MYSQL DB',source_mount,1);

end;
//##############################################################################
procedure Tcyrback.rsync_MYSQL();
var
   mysql_password:string;
   mysql_user:string;
   internal_source_dir,db,cmd:string;
   temporarySourceDir:string;
   i:Integer;
begin


if not FileExists(SYS.LOCATE_MYSHOTCOPY()) then begin
      logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): Unable to find mysqlHotCopy tool','Mysql databases backup could not be performed','system');
      exit;
end;
        internal_source_dir:=source_dir+'/mysql_backup';
        forceDirectories(internal_source_dir);

        GetMysqlDatabases();
        mysql_password:=SYS.MYSQL_INFOS('database_password');
        mysql_user:=SYS.MYSQL_INFOS('database_admin');
        if length(mysql_password)>0 then mysql_password:=' --password='+mysql_password;
        temporarySourceDir:='/home/mysqlhotcopy';
        ForceDirectories(temporarySourceDir);
        for i:=0 to databases_list.Count-1 do begin
            db:=databases_list.Strings[i];
            cmd:=SYS.LOCATE_MYSHOTCOPY()+' --quiet --addtodest --user='+mysql_user+mysql_password +' '+db+' '+temporarySourceDir;
            logs.OutputCmd(cmd);
        end;

        SetCurrentDir(temporarySourceDir);
        cmd:='tar -cjf '+internal_source_dir+'/mysql-databases.tgz *';
        logs.OutputCmd(cmd);
        fpsystem('/bin/rm -rf '+temporarySourceDir+'/*');


        logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+'): '+IntToStr(databases_list.Count)+' Mysql Databases backuped ','Databases: '+databases_list.Text,'system');
        logs.BACKUP_EVENTS('Mysql backup terminated :'+SYS.FOLDER_SIZE_HUMAN(internal_source_dir),'MAILBOXES',source_mount,0);

end;
//##############################################################################



end.

