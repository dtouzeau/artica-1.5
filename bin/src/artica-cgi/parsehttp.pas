unit parsehttp;

{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,variants, Process,strutils,BaseUnix,unix,logs,common,process_infos,class_install,
RegExpr in 'RegExpr.pas',zSystem, clamav,spamass,ntpd,mimedefang,cyrus,stunnel4,dkimfilter,
global_conf in 'global_conf.pas',miltergreylist,postfix_class,artica_tcp,roundcube,dansguardian,kav4proxy,kav4samba,IniFiles,cups,
cgi_actions in 'cgi_actions.pas',bind9,obm,rdiffbackup, openvpn,pureftpd,obm2,opengoo,dstat,sugarcrm,rsync,autofs,lvm,
kas3 in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kas3.pas',
kavmilter in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kavmilter.pas',
mysql_daemon in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mysql_daemon.pas',
artica_cron  in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/artica_cron.pas',
collectd        in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/collectd.pas',
mailspy_milter   in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mailspy_milter.pas',
amavisd_milter   in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/amavisd_milter.pas',
openldap   in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/openldap.pas',
kretranslator    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kretranslator.pas',
dotclear         in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/dotclear.pas';


  type
  Tparsehttp=class


private
     openvpn:topenvpn;
     zobm2:tobm2;
     opengoo:topengoo;
     dstat:tdstat;
     sugarcrm:tsugarcrm;
     rsync:trsync;
     autofs:tautofs;
     Explosed:TStringDynArray;
     GLOBAL_INI:myconf;
     LOGS:Tlogs;
     COMMON:Tcommon;
     SYS:Tsystem;
     PROC:Tprocessinfos;
     install:Tclass_install;
     maillog:string;
     ntpd:Tntpd;
     clamav:Tclamav;
     mimedef:Tmimedefang;
     tempstr,cmd,filePath,TempDatasLogs:string;
     ccyrus:Tcyrus;
     stunnel:Tstunnel;
     dkim:Tdkim;
     postfix:Tpostfix;
     miltergreylist:tmilter_greylist;
     roundcube:Troundcube;
     dansguardian:Tdansguardian;
     tcp:ttcp;
     options:string;
     kav4proxy:Tkav4proxy;
     kav4samba:Tkav4samba;
     bind9:Tbind9;
     rdiffbackup:trdiffbackup;
     obm:tobm;
     kavmilter:tkavmilter;
     mysql:tmysql_daemon;
     collectd:tcollectd;
     artica_cron:tcron;
     kas3:tkas3;
     mailspy:tmailspy;
     amavis:tamavis;
     ldap:topenldap;
     dotclear:tdotclear;
     TmpINI:TiniFile;
     retranslator:tkretranslator;
     cups_server:tcups;
     pureftpdClass:tpureftpd;
     procedure tail_postfix_logs_filter(filter:string);
     procedure tail_smtpscanner_logs();
     procedure tail_keeup2datelog_kav_logs();
     procedure ReloadPostfix();
     procedure avestatus();
     function Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
     procedure QuarantineQueryPattern(userid:string;Patterns:string);


public
      Debug:boolean;
      Enable_echo:boolean;
      FileData:TstringList;
      I,n,a : longint;
      constructor Create;
      procedure Free;


      function ParseUri(uri:string):boolean;
END;

implementation
//#####################################################################################
constructor Tparsehttp.Create;
begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=myconf.Create();
       LOGS:=Tlogs.Create;
       LOGS.Debug:=Debug;
       LOGS.Enable_echo:=Enable_echo;
       COMMON:=Tcommon.Create;
       COMMON.debug:=Debug;
       PROC:=Tprocessinfos.Create;
       SYS:=Tsystem.Create();

       FileData:=TstringList.Create;
       install:=Tclass_install.Create;
       clamav:=Tclamav.Create();
       ntpd:=Tntpd.Create;
       mimedef:=Tmimedefang.Create(GLOBAL_INI.SYS);
       ccyrus:=Tcyrus.Create(GLOBAL_INI.SYS);
       stunnel:=Tstunnel.Create(GLOBAL_INI.SYS);
       dkim:=tdkim.Create(GLOBAL_INI.SYS);
       miltergreylist:=tmilter_greylist.Create(GLOBAL_INI.SYS);
       postfix:=tpostfix.Create(GLOBAL_INI.SYS);
       tcp:=ttcp.Create;
       roundcube:=TroundCube.Create(GLOBAL_INI.SYS);
       dansguardian:=Tdansguardian.Create(GLOBAL_INI.SYS);
       kav4proxy:=Tkav4proxy.Create(GLOBAL_INI.SYS);
       kav4samba:=Tkav4samba.Create;
       bind9:=Tbind9.Create(GLOBAL_INI.SYS);
       obm:=tobm.Create(GLOBAL_INI.SYS);
       kavmilter:=tkavmilter.Create(GLOBAL_INI.SYS);
       kas3:=tkas3.Create(GLOBAL_INI.SYS);
       collectd:=tcollectd.Create(GLOBAL_INI.SYS);
       mailspy:=tmailspy.Create(GLOBAL_INI.SYS);
end;
procedure Tparsehttp.Free();
begin
   GLOBAL_INI.Free;
   LOGS.Free;
   COMMON.Free;
   PROC.Free;
   FileData.Free;
   //SYS.Free;
   kavmilter.Free;
   kas3.free;
   collectd.free;
end;

//#####################################################################################
function Tparsehttp.ParseUri(uri:string):boolean;
const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   RegExpr:TRegExpr;
   path,command_line,tmpstr:string;
   classInstall:Tclass_install;
   cgiOp:Tcgi_actions;
   spamass:Tspamass;
   i:integer;

begin

uri:=AnsiReplaceText(uri,'%20',' ');
  LOGS.logs('Tparsehttp: parsing uri {' + uri + '}');
  LOGS.Debuglogs('Tparsehttp: parsing uri {' + uri + '}');
  RegExpr:=TRegExpr.create;
  classInstall:=Tclass_install.Create;
  cgiOp:=Tcgi_actions.Create;
  spamass:=Tspamass.Create(GLOBAL_INI.SYS);
  SYS:=GLOBAL_INI.SYS;
  result:=false;


RegExpr.expression:='pvcreate:(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();                                                                                                 
   cmd:='/usr/share/artica-postfix/bin/artica-install --pvcreate-dev '+RegExpr.Match[1] +' --verbose >'+tmpstr+' 2>&1';
   logs.Debuglogs(cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.DeleteFile(tmpstr);
   exit(true);
end;
RegExpr.expression:='vgcreate:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/share/artica-postfix/bin/artica-install --vgcreate-dev '+RegExpr.Match[1] +' "' + RegExpr.Match[2]+'" --verbose >'+tmpstr+' 2>&1';
   logs.Debuglogs(cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.DeleteFile(tmpstr);
   exit(true);
end;








RegExpr.expression:='CheckDaemon';
 if RegExpr.Exec(uri) then begin
      artica_cron:=tcron.Create(GLOBAL_INI.SYS);
      artica_cron.START();
      artica_cron.Free;
      result:=true;
      exit;
   end;

RegExpr.expression:='^pureftpwho';
  if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       cmd:='/usr/sbin/pure-ftpwho -x -s -W >' + tmpstr+' 2>&1';
       logs.Debuglogs('pureftpwho: running '+cmd);
       fpsystem(cmd);
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(tmpstr);
       exit(true);
  end;

RegExpr.expression:='^obm2:(.+)';
  if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' '+ GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.obm.synchro.php --user='+RegExpr.Match[1]);
      exit(true);
  end;

RegExpr.expression:='^OBMContactExport:(.+)';
  if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' '+ GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.obm.contacts.php --user='+RegExpr.Match[1]);
      exit(true);
  end;

RegExpr.expression:='^OBMContactDelete:(.+)';
  if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' '+ GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.obm.contacts.php --delete='+RegExpr.Match[1]);
      exit(true);
  end;

RegExpr.expression:='^SetComputerBackupSchedule';
  if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' '+ GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.rsync.events.php --computers-schedule');
      exit(true);
  end;


RegExpr.expression:='^FreshClamStartDebug';
  if RegExpr.Exec(uri) then begin
      clamav:=tclamav.Create;
      tmpstr:=clamav.FRESHCLAM_PATH() +' --config-file=' + clamav.FRESHCLAM_CONF_PATH()+ ' --stdout --debug >/var/log/clamav/freshclam.debug';
      GLOBAL_INI.THREAD_COMMAND_SET(tmpstr);
      clamav.Free;
      exit(true);
  end;


RegExpr.expression:='^FreshClamStartLoadDebug';
  if RegExpr.Exec(uri) then begin
      FIleData.Add(logs.ReadFromFile('/var/log/clamav/freshclam.debug'));
      exit(true);
  end;

RegExpr.expression:='^autofsStatus';
  if RegExpr.Exec(uri) then begin
      logs.Debuglogs('autofsStatus: query status');
      autofs:=tautofs.Create(SYS);
      tmpstr:=autofs.STATUS();
      logs.Debuglogs('Status:'+tmpstr);
      autofs.free;
      FileData.Add(tmpstr);
      exit(true);
  end;


RegExpr.expression:='^Obm2Status';
  if RegExpr.Exec(uri) then begin
      logs.Debuglogs('Obm2Status: query status');
      zobm2:=Tobm2.Create(SYS);

      logs.Debuglogs('Status:'+tmpstr);
      zobm2.free;
      FileData.Add(tmpstr);
      exit(true);
  end;

RegExpr.expression:='^Obm2restart';
  if RegExpr.Exec(uri) then begin
     fpsystem('/etc/init.d/artica-postfix restart obm');
     exit(true);
  end;


RegExpr.expression:='^NFSReload';
  if RegExpr.Exec(uri) then begin
     sys.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --nfs-reload');
     exit(true);
  end;



RegExpr.expression:='^CertificateConfigFile';
  if RegExpr.Exec(uri) then begin
       if fileExists('/etc/artica-postfix/ssl.certificate.conf') then begin
          FileData.LoadFromFile('/etc/artica-postfix/ssl.certificate.conf');
       end else begin
        FileData.LoadFromFile(global_ini.get_ARTICA_PHP_PATH()+'/ressources/databases/DEFAULT-CERTIFICATE-DB.txt');
       end;

       exit(true);
  end;


RegExpr.expression:='^DebugImapMbx:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.TestingMailBox(RegExpr.Match[1],RegExpr.Match[2]));
       exit(true);
  end;

RegExpr.expression:='^MurderTestBackend';
  if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.MURDER_TEST_BACKEND());
       exit(true);
  end;

RegExpr.expression:='^MurderBeABackend';
  if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.MURDER_SEND_BACKEND());
       exit(true);
  end;

RegExpr.expression:='^ClusterDisableReplica';
  if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.CLUSTER_SEND_COMMAND('delete-replicat=yes'));
       exit(true);
  end;


RegExpr.expression:='^CyrusNotifyReplica';
  if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.CLUSTER_NOTIFY_REPLICA());
       SYS.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install --cluster-replicat-send-ldap');
       exit(true);
  end;

RegExpr.expression:='^ClusterDisableSlave';
  if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.CLUSTER_DISABLE_MASTER());
       exit(true);
  end;



RegExpr.expression:='^LaunchRemoteInstall';
  if RegExpr.Exec(uri) then begin
       SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.remote-install.php');
       exit(true);
  end;


RegExpr.expression:='^ReplicateLDAP:(.+)';
  if RegExpr.Exec(uri) then begin
       SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-backup --instant-ldap-recover '+RegExpr.Match[1]);
       exit(true);
  end;

RegExpr.expression:='^CyrusMurderChangeLDAPConfig';
  if RegExpr.Exec(uri) then begin
       tmpstr:=ccyrus.MURDER_CHANGE_LDAP();
       FileData.Add(tmpstr);
       SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.services.change.ldap.php');
       exit(true);
  end;




RegExpr.expression:='CyrusMasterSyncClient';
  if RegExpr.Exec(uri) then begin
       logs.DeleteFile('/usr/share/artica-postfix/ressources/logs/sync_client.log');
       GLOBAL_INI.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN() +' /usr/share/artica-postfix/exec.cyrus.sync.client.php --silent');
       exit(true);
  end;



RegExpr.expression:='restartDebugCyrusImapConf';
  if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       fpsystem('/etc/init.d/artica-postfix restart imap --verbose >'+tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(tmpstr);
       exit(true);
  end;

RegExpr.expression:='ComputerRemoteRessources:(.+?);(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       FileData.Add(SYS.GetRemoteComputerRessources(RegExpr.Match[1],RegExpr.Match[2],RegExpr.Match[3]));
       exit(true);
  end;



RegExpr.expression:='ComputerScanForViruses:(.+)';
  if RegExpr.Exec(uri) then begin
       SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.computer.scan.php '+RegExpr.Match[1]);
       exit(true);
  end;

RegExpr.expression:='^OpenVPNBuildCertificate';
  if RegExpr.Exec(uri) then begin
       GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install --openvpn-build-certificate');
       exit(true);
  end;

RegExpr.expression:='^ClamavRecompile';
  if RegExpr.Exec(uri) then begin
       tempstr:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install/APP_CLAMAV.dbg';
       logs.Debuglogs('Set the log file in' + tempstr);
       logs.INSTALL_STATUS('APP_CLAMAV',3);
       logs.INSTALL_PROGRESS('APP_CLAMAV','{scheduled}');
       tmpstr:='/usr/share/artica-postfix/bin/artica-make APP_CLAMAV --force >'+tempstr +' 2>&1';
       logs.Debuglogs('Thread -> '+tmpstr);
       GLOBAL_INI.THREAD_COMMAND_SET(tmpstr);
      exit(true);
  end;




RegExpr.expression:='^RoundRobinHosts';
  if RegExpr.Exec(uri) then begin
      tmpstr:=logs.FILE_TEMP();
      fpsystem(SYS.LOCATE_PHP5_BIN()+' ' +GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.etc-hosts.php >' +tmpstr + ' 2>&1');
      logs.Debuglogs('RoundRobinHosts:: '+logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
  end;

RegExpr.expression:='^MysqlRepairDatabase';
  if RegExpr.Exec(uri) then begin
      tmpstr:=logs.FILE_TEMP();
      fpsystem('/usr/share/artica-postfix/bin/artica-backup --repair-database --verbose >'+tmpstr+' 2>&1');
      logs.Debuglogs('MysqlRepairDatabase:: '+logs.ReadFromFile(tmpstr));
      FileData.Add(logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
  end;


RegExpr.expression:='^statfile:(.+)';
  if RegExpr.Exec(uri) then begin
     if FileExists(trim(RegExpr.Match[1])) then FileData.Add('SUCCESS');
     exit(true);
  end;

RegExpr.expression:='^homeDirectoryBinded:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
     tmpstr:=logs.FILE_TEMP();
     FilePath:=RegExpr.Match[1] +'/'+ExtractFileName(RegExpr.Match[2]);
     if not DirectoryExists(FilePath) then ForceDirectories(FilePath);
     cmd:='/bin/mount --bind '+RegExpr.Match[2]+' '+FilePath +' >'+tmpstr+' 2>&1';
     logs.Debuglogs(cmd);
     fpsystem(cmd);
     FileData.Add(logs.ReadFromFile(tmpstr));
     logs.Debuglogs(logs.ReadFromFile(tmpstr));
     logs.DeleteFile(tmpstr);
     exit(true);
  end;

RegExpr.expression:='^homeDirectoryUBinded:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
     tmpstr:=logs.FILE_TEMP();
     FilePath:=RegExpr.Match[1] +'/'+ExtractFileName(RegExpr.Match[2]);
     cmd:='/bin/umount '+FilePath +' >'+tmpstr+' 2>&1';
     logs.Debuglogs(cmd);
     fpsystem(cmd);
     FileData.Add(logs.ReadFromFile(tmpstr));
     logs.Debuglogs(logs.ReadFromFile(tmpstr));
     logs.DeleteFile(tmpstr);
     if(SYS.DirectoryCountFiles(FilePath))=0 then fpsystem('/bin/rmdir '+FilePath);
     exit(true);
  end;
RegExpr.expression:='^InstantSearchEnableCrawl';
  if RegExpr.Exec(uri) then begin
     FileData.Add('scanning disk ->{scheduled}');
     FileData.Add('Refresh web page in few seconds...');
     fpsystem('/etc/init.d/artica-postfix start daemon');
     FileData.SaveToFile('/usr/share/artica-postfix/ressources/logs/InstantCrawl.log');
     fpsystem('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/InstantCrawl.log');
     SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.xapian.index.php >/usr/share/artica-postfix/ressources/logs/InstantCrawl.log 2>&1');
     exit(true);
  end;


RegExpr.expression:='^RestartApacheGroupware';
  if RegExpr.Exec(uri) then begin
     fpsystem('/etc/init.d/artica-postfix restart apache-groupware');
     exit(true);
 end;

RegExpr.expression:='^ApacheChangePortStandard:(.+)';
  if RegExpr.Exec(uri) then begin
     SYS.APACHE_STANDARD_PORT_CHANGE(RegExpr.Match[1]);
     exit(true);
 end;

RegExpr.expression:='^opengoouid:(.+)';
  if RegExpr.Exec(uri) then begin
     logs.Debuglogs('Updateing opengoo user ' + RegExpr.Match[1]);
     SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN() +' /usr/share/artica-postfix/exec.opengoo.php --user='+RegExpr.Match[1]);
     exit(true);
  end;

//-----------------------------------------------------------------------------------
RegExpr.expression:='^PostfixCorruptedMove';
  if RegExpr.Exec(uri) then begin
     postfix.POSTFIX_MOVE_CORRUPTED_QUEUE();
     FileData.Add('Success');
     exit(true);
  end;
//-----------------------------------------------------------------------------------
RegExpr.expression:='^dmidecode';
  if RegExpr.Exec(uri) then begin
  if not FileExists('/usr/sbin/dmidecode') then begin
      FileData.Add('{failed}: unable to stat /usr/sbin/dmidecode');
      exit(true);
  end;
  tmpstr:=logs.FILE_TEMP();
  fpsystem('/usr/sbin/dmidecode -q >'+tmpstr+' 2>&1');
  FileData.Add(logs.ReadFromFile(tmpstr));
  logs.DeleteFile(tmpstr);
  exit(true);
end;
//-----------------------------------------------------------------------------------
RegExpr.expression:='^PostfixAutoBlockCompile';
  if RegExpr.Exec(uri) then begin
       TmpINI:=TiniFIle.Create('/usr/share/artica-postfix/ressources/logs/compile.iptables.progress');
       TmpINI.WriteString('PROGRESS','pourc','5');
       TmpINI.WriteString('PROGRESS','text','{scheduled} {please_wait}');
       TmpINI.UpdateFile;
       fpsystem('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/compile.iptables.progress');
       SYS.THREAD_COMMAND_SET(SYS.EXEC_NICE()+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfix.iptables.php --compile');
       exit(true);
  end;
//-----------------------------------------------------------------------------------
RegExpr.expression:='^ApacheGroupWareRestart';
  if RegExpr.Exec(uri) then begin
       logs.Debuglogs('Reloading Apache-groupware.........................................');
       SYS.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart apache-groupware');
       exit(true);
  end;
//-----------------------------------------------------------------------------------

RegExpr.expression:='^PostfixReload';
  if RegExpr.Exec(uri) then begin
       logs.Debuglogs('Reloading postfix..................................................');
       fpsystem('/etc/init.d/artica-postfix start daemon &');
       fpsystem('/bin/cp /etc/artica-postfix/settings/Daemons/PostfixMainCfFile /etc/postfix/main.cf');
       SYS.THREAD_COMMAND_SET(SYS.EXEC_NICE()+'/usr/share/artica-postfix/bin/artica-install --postfix-reload');
       FileData.Add('OK');
       exit(true);
  end;
//-----------------------------------------------------------------------------------
RegExpr.expression:='^LaunchRTMMail';
  if RegExpr.Exec(uri) then begin
       logs.Debuglogs('Reloading Realtime monitor............................................');
       SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN() +' /usr/share/artica-postfix/exec.last.100.mails.php');
       FileData.Add('OK');
       exit(true);
  end;
//-----------------------------------------------------------------------------------
 RegExpr.expression:='^SASLDB2:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       cgiop.SASLDB2(RegExpr.Match[1],RegExpr.Match[2]);
       exit(true);
  end;
//-----------------------------------------------------------------------------------
 RegExpr.expression:='^ReloadRsyncServer';
  if RegExpr.Exec(uri) then begin
       SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --reload-rsync');
       exit(true);
  end;
//-----------------------------------------------------------------------------------
 RegExpr.expression:='^rsyncstatus';
  if RegExpr.Exec(uri) then begin
       rsync:=trsync.Create(SYS);
       FileData.Add(rsync.STATUS());
       rsync.free;
       exit(true);
  end;
//-----------------------------------------------------------------------------------

 RegExpr.expression:='^smbclientStartBrowseComputer:(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.smbclient.browse.php --browse='+RegExpr.Match[1]+' --filew='+tmpstr+ ' -- >/dev/null 2>&1';
       logs.Debuglogs(cmd);
       fpsystem(cmd);
       fpsystem('/bin/chmod 755 '+tmpstr);
       FileData.Add(tmpstr);
       exit(true);
  end;
//-----------------------------------------------------------------------------------
 RegExpr.expression:='^smbclientBrowseComputer:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();

       filePath:=RegExpr.Match[2];
       filePath:=AnsireplaceText(filePath,'Menu D  Mes documents','Mes documents');
       cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.smbclient.browse.php --browse='+RegExpr.Match[1]+' --listf="'+filePath+'" --filew='+tmpstr+' -- >/dev/null 2>&1';
       logs.Debuglogs(cmd);
       fpsystem(cmd);
       fpsystem('/bin/chmod 755 '+tmpstr);
       FileData.Add(tmpstr);
       exit(true);
  end;
//-----------------------------------------------------------------------------------
 RegExpr.expression:='^smb_scan';
  if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       cmd:='/usr/share/artica-postfix/bin/findsmb.pl >'+ tmpstr +' 2>&1';
       logs.Debuglogs(cmd);
       fpsystem(cmd);
       FileData.Add(logs.ReadFromFile(tmpstr));
       exit(true);
  end;
//-----------------------------------------------------------------------------------
 RegExpr.expression:='^LaunchExportConfiguration';
  if RegExpr.Exec(uri) then begin
       TmpINI:=TiniFile.Create('/usr/share/artica-postfix/ressources/logs/export.status.conf');
       TmpINI.WriteString('STATUS','progress','5');
       TmpINI.WriteString('STATUS','text','{scheduled}');
       TmpINI.UpdateFile;
       fpsystem('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/export.status.conf');
       cmd:='/usr/share/artica-postfix/bin/artica-backup --export-config';
       logs.DeleteFile('/usr/share/artica-postfix/ressources/logs/export-config.debug');
       logs.Debuglogs(cmd);
       SYS.THREAD_COMMAND_SET(cmd);
       exit(true);
  end;
//-----------------------------------------------------------------------------------
 RegExpr.expression:='^LaunchImportConfiguration';
  if RegExpr.Exec(uri) then begin
       TmpINI:=TiniFile.Create('/usr/share/artica-postfix/ressources/logs/export.status.conf');
       TmpINI.WriteString('STATUS','progress','5');
       TmpINI.WriteString('STATUS','text','{scheduled}');
       TmpINI.UpdateFile;
       fpsystem('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/export.status.conf');
       cmd:='/usr/share/artica-postfix/bin/artica-backup --import-config';
       logs.DeleteFile('/usr/share/artica-postfix/ressources/logs/export-config.debug');
       logs.Debuglogs(cmd);
       SYS.THREAD_COMMAND_SET(cmd);
       exit(true);
  end;
//-----------------------------------------------------------------------------------




//------------------------------------------------------------------------------
RegExpr.expression:='^smartclinfos:(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/sbin/smartctl -i ' + RegExpr.Match[1]+ ' >'+tmpstr+' 2>&1';
   logs.Debuglogs('Running:'+cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.Debuglogs('in memory :' +intTostr(length(FileData.Text)) +' bytes');
   logs.DeleteFile(tmpstr);
   exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^smartcltests:(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/sbin/smartctl -l selftest ' + RegExpr.Match[1]+ ' >'+tmpstr+' 2>&1';
   logs.Debuglogs('Running:'+cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.Debuglogs('in memory :' +intTostr(length(FileData.Text)) +' bytes');
   logs.DeleteFile(tmpstr);
   exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^SMARTDSlefttestShort:(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/sbin/smartctl -t short ' + RegExpr.Match[1]+ ' >'+tmpstr+' 2>&1';
   logs.Debuglogs('Running:'+cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.Debuglogs('in memory :' +intTostr(length(FileData.Text)) +' bytes');
   logs.DeleteFile(tmpstr);
   exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^smartcltAttributes:(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/sbin/smartctl -A ' + RegExpr.Match[1]+ ' >'+tmpstr+' 2>&1';
   logs.Debuglogs('Running:'+cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.Debuglogs('in memory :' +intTostr(length(FileData.Text)) +' bytes');
   logs.DeleteFile(tmpstr);
   exit(true);
end;
 //------------------------------------------------------------------------------
RegExpr.expression:='^SMARTDEnable:(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/sbin/smartctl --smart=on --offlineauto=on --saveauto=on ' + RegExpr.Match[1]+ ' >'+tmpstr+' 2>&1';
   logs.Debuglogs('Running:'+cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.Debuglogs('in memory :' +intTostr(length(FileData.Text)) +' bytes');
   logs.DeleteFile(tmpstr);
   exit(true);
end;
 //------------------------------------------------------------------------------
RegExpr.expression:='^SMARTDDisable:(.+)';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/sbin/smartctl --smart=off --offlineauto=off --saveauto=off ' + RegExpr.Match[1]+ ' >'+tmpstr+' 2>&1';
   logs.Debuglogs('Running:'+cmd);
   fpsystem(cmd);
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.Debuglogs('in memory :' +intTostr(length(FileData.Text)) +' bytes');
   logs.DeleteFile(tmpstr);
   exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^PrinterPPDName:(.+)';
if RegExpr.Exec(uri) then begin
     cups_server:=tcups.Create;
     logs.Debuglogs('get printer info from PPD ' + RegExpr.Match[1]);
     FileData.Add(cups_server.PPD_MODEL_NAME(RegExpr.Match[1]));
     cups_server.Free;
     exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^lpstatPrinterInfos:(.+)';
if RegExpr.Exec(uri) then begin
     logs.Debuglogs('get printer info from ' + RegExpr.Match[1]);
     tmpstr:=logs.FILE_TEMP();
     fpsystem('/usr/bin/lpstat -l -p ' + RegExpr.Match[1] +' >'+tmpstr+' 2>&1');
     FileData.Add(logs.ReadFromFile(tmpstr));
     logs.DeleteFile(tmpstr);
     exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^lpstatDriverInfos:(.+)';
if RegExpr.Exec(uri) then begin
     logs.Debuglogs('get printer drvier path from ' + RegExpr.Match[1]);
     cups_server:=tcups.Create;
     tmpstr:=cups_server.PPD_PATH(RegExpr.Match[1]);
     FileData.Add(tmpstr +';' + cups_server.PPD_MODEL_NAME(tmpstr));
     exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^lpShared:(.+)';
if RegExpr.Exec(uri) then begin
      FileData.Add('username = administrator');
      FileData.Add('password = '+RegExpr.Match[1]);
      logs.WriteToFile(FileData.Text,'/tmp/passd');
      FileData.clear;
      tmpstr:=logs.FILE_TEMP();
      cmd:='/usr/bin/rpcclient localhost -N -A /tmp/passd -c "enumprinters" >'+tmpstr+' 2>&1';
      fpsystem(cmd);
      logs.Debuglogs(cmd);
      FileData.Add(logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^lpDriverSharedName:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
      cups_server:=tcups.Create;
      FileData.Add(cups_server.SHARED_PRINTER_DRIVER(RegExpr.Match[1],RegExpr.Match[2]));
      exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^DeleteSharedPrinter:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
      FileData.Add('username = administrator');
      FileData.Add('password = '+RegExpr.Match[2]);
      logs.WriteToFile(FileData.Text,'/tmp/passd');
      FileData.clear;
      tmpstr:=logs.FILE_TEMP();
      cmd:='/usr/bin/rpcclient localhost -N -A /tmp/passd -c "deldriver '+RegExpr.Match[1]+'" >'+tmpstr+' 2>&1';
      fpsystem(cmd);
      logs.Debuglogs(cmd);
      FileData.Add(logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^DeleteAllSharedPrinter';
if RegExpr.Exec(uri) then begin
      logs.OutputCmd('/etc/init.d/artica-postfix stop samba');
      logs.OutputCmd('/etc/init.d/cups stop');
      logs.OutputCmd('/bin/rm -f /var/cache/samba/printing/*.tbd');
      logs.OutputCmd('/bin/rm -f /var/lib/samba/ntdrivers.tdb');
      logs.OutputCmd('/bin/rm -f /var/lib/samba/ntprinters.tdb');
      logs.OutputCmd('/etc/init.d/artica-postfix start samba');
      logs.OutputCmd('/etc/init.d/cups start');
   exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^InstallPrinterShare:(.+?);(.+?);(.+)';
if RegExpr.Exec(uri) then begin
   logs.Debuglogs('Add printer '+RegExpr.Match[1]+'into samba, ppd='+RegExpr.Match[2]);
   cups_server:=tcups.Create;
   FileData.Add(cups_server.ADD_SHARED_PRINTER(RegExpr.Match[1],RegExpr.Match[2],RegExpr.Match[3]));
   exit(true);
end;
//------------------------------------------------------------------------------
RegExpr.expression:='^lpinfo';
  if RegExpr.Exec(uri) then begin
        if not FileExists('/usr/sbin/lpinfo') then begin
           logs.Syslogs('unable to stat /usr/sbin/lpinfo');
           exit;
        end;

        tmpstr:=logs.FILE_TEMP();
        fpsystem('/usr/sbin/lpinfo -v >'+tmpstr+' 2>&1');
        FileData.Add(logs.ReadFromFile(tmpstr));
        logs.DeleteFile(tmpstr);
        exit(true);
   end;


RegExpr.expression:='^lpstat';
  if RegExpr.Exec(uri) then begin
     tmpstr:=logs.FILE_TEMP();
     if not FileExists('/usr/bin/lpstat') then begin
          FileData.Add('unable_to_stat_lpstat_! error');
          exit;
     end;

     fpsystem('/usr/bin/lpstat -a >'+tmpstr+' 2>&1');
     FileData.Add(logs.ReadFromFile(tmpstr));
     logs.DeleteFile(tmpstr);
     exit(true);
  end;





RegExpr.expression:='^lpGendrv';
  if RegExpr.Exec(uri) then begin
     if FileExists('/usr/share/artica-postfix/ressources/logs/gutenprint.log') then logs.DeleteFile('/usr/share/artica-postfix/ressources/logs/gutenprint.log');
     logs.WriteToFile('Programming driver build operation','/usr/share/artica-postfix/ressources/logs/gutenprint.log');
     fpsystem('/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/gutenprint.log');
     SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cups-drivers --force --verbose >>/usr/share/artica-postfix/ressources/logs/gutenprint.log 2>&1');
     exit(true);
  end;

RegExpr.expression:='^CupsDrvLogs';
  if RegExpr.Exec(uri) then begin
      if FileExists('/usr/share/artica-postfix/ressources/logs/gutenprint.log') then begin
         tmpstr:=logs.FILE_TEMP();
         fpsystem('/usr/bin/tail -n 500 /usr/share/artica-postfix/ressources/logs/gutenprint.log >'+tmpstr);
         FileData.Add(logs.ReadFromFile(tmpstr));
         logs.DeleteFile(tmpstr);
      END;
    exit(true);
  end;



RegExpr.expression:='^OpenVPNStatus';
   if RegExpr.Exec(uri) then begin
      openvpn:=Topenvpn.Create(SYS);
      FileData.Add(openvpn.STATUS());
      openvpn.free;
      exit(true);
   end;

RegExpr.expression:='^RestartOpenVPNServer';
  if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       fpsystem('/etc/init.d/artica-postfix restart openvpn --verbose >'+tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(tmpstr);
       exit(true);
  end;

  RegExpr.expression:='^ForceStatus';
  if RegExpr.Exec(uri) then begin
       logs.DeleteFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/global.status.ini');
       exit(true);
  end;


  RegExpr.expression:='^pflogsummSend';
  if RegExpr.Exec(uri) then begin
     SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.postfix.reports.php');
     exit(true);
  end;



  RegExpr.expression:='^ResendSendMail:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
     cmd:='/usr/sbin/sendmail -bm -f "'+trim(RegExpr.Match[2])+'" '+ '"'+trim(RegExpr.Match[2])+'"  <'+RegExpr.Match[1];
     logs.Debuglogs(cmd);
     fpsystem(cmd);
     exit(true);
  end;

  RegExpr.expression:='^INFOS:(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=trim(SYS.GET_INFO(RegExpr.Match[1]));
       if length(tmpstr)>10 then TempDatasLogs:=IntToStr(length(tmpstr))+' bytes' else TempDatasLogs:=tmpstr;
       logs.Debuglogs('INFOS::'+RegExpr.Match[1]+'='+TempDatasLogs);
       FileData.Add(tmpstr);
       exit(true);
  end;

  RegExpr.expression:='^Bind9Key:(.+)';
  if RegExpr.Exec(uri) then begin
     bind9:=tbind9.Create(SYS);
     FileData.Add(bind9.GenerateKey(RegExpr.Match[1]));
     exit(true);
  end;

  RegExpr.expression:='^Bind9ListZones';
  if RegExpr.Exec(uri) then begin
     FileData.AddStrings(SYS.DirFiles('/etc/artica-postfix/settings/Daemons','Bind9Zone.*.zone.hosts'));
     exit(true);
  end;




  RegExpr.expression:='^SET_INFOS:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=trim(RegExpr.Match[1]);
       logs.Debuglogs('SET_INFOS::'+RegExpr.Match[1]+'='+RegExpr.Match[2]);
       SYS.set_INFO(trim(RegExpr.Match[1]),trim(RegExpr.Match[2]));
       exit(true);
  end;

  RegExpr.expression:='^slapindex';
  if RegExpr.Exec(uri) then begin
       tmpstr:=LOGS.FILE_TEMP();
       logs.Debuglogs('slapindex:: launch slapindex procedure');
       fpsystem(global_ini.get_ARTICA_PHP_PATH()+'/bin/artica-install --slpapindex --verbose >'+ tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(tmpstr);
       exit(true);
  end;



  RegExpr.expression:='^MSQLINFOS:(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=trim(SYS.MYSQL_INFOS(RegExpr.Match[1]));
       if length(tmpstr)>10 then TempDatasLogs:=IntToStr(length(tmpstr))+' bytes' else TempDatasLogs:=tmpstr;
       logs.Debuglogs('MSQLINFOS::'+RegExpr.Match[1]+'='+TempDatasLogs);
       FileData.Add(tmpstr);
       exit(true);
  end;

  RegExpr.expression:='^SET_MSQLINFOS:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=trim(RegExpr.Match[1]);
       logs.Debuglogs('SET_MSQLINFOS:: Save mysql information:: '+RegExpr.Match[1]+'='+RegExpr.Match[2]);
       SYS.set_MYSQL(trim(RegExpr.Match[1]),trim(RegExpr.Match[2]));
       exit(true);
  end;

RegExpr.expression:='^SaveConfigFile:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=trim(RegExpr.Match[1]);
       filePath:=global_ini.get_ARTICA_PHP_PATH()+'/ressources/conf/'+RegExpr.Match[1];
       if FileExists(filePath) then begin
          logs.Debuglogs('SET_INFOS:: content of '+filePath+'='+RegExpr.Match[2]);
          SYS.set_INFO(trim(RegExpr.Match[2]),logs.ReadFromFile(filePath));
       end;
       exit(true);
  end;


RegExpr.expression:='^SaveDansGuardianFile:(.+?);(.+)';
  if RegExpr.Exec(uri) then begin
       tmpstr:=trim(RegExpr.Match[1]);
       filePath:=global_ini.get_ARTICA_PHP_PATH()+'/ressources/conf/'+RegExpr.Match[1];
       if FileExists(filePath) then begin
          logs.Debuglogs('DansGuardian:: move content of '+filePath+' to '+RegExpr.Match[2]);
          logs.OutputCmd('/bin/mv '+filePath+' /etc/dansguardian/lists/'+ RegExpr.Match[2]);
          dansguardian.DANSGUARDIAN_RELOAD();
       end;
       exit(true);
  end;


RegExpr.expression:='^DansGuardianLogSize';
  if RegExpr.Exec(uri) then begin
       FileData.Add(IntTOstr(SYS.FileSize_ko('/var/log/dansguardian/access.log')));
       exit(true);
  end;


RegExpr.expression:='^DansGuardianRotateLogs';
  if RegExpr.Exec(uri) then begin
       TempDatasLogs:=FormatDateTime('yyyy-mm-dd-hhnn', Now);
       tmpstr:=logs.FILE_TEMP();
       filePath:='/var/log/dansguardian/access-'+TempDatasLogs+'.log';
       FileData.Add('Copy file to ' + filePath );
       fpsystem('/bin/cp /var/log/dansguardian/access.log '+filePath);
       FileData.Add('compressing file to/var/log/dansguardian/access-'+TempDatasLogs+'.tar.gz');
       fpsystem('/bin/tar -cf /var/log/dansguardian/access-'+TempDatasLogs+'.tar.gz '+filePath + ' >'+tmpstr + ' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(filePath);
       logs.DeleteFile(tmpstr);
       logs.DeleteFile('/var/log/dansguardian/access.log');
       FileData.Add('restarting DansGuardian');
       fpsystem('/etc/init.d/artica-postfix restart dansguardian >'+tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(tmpstr);
       exit(true);
  end;




RegExpr.expression:='^SargFile:(.+)';
  if RegExpr.Exec(uri) then begin
      tmpstr:='/opt/artica/share/www/squid/sarg/'+RegExpr.Match[1];
      if not FileExists(tmpstr) then begin
         logs.Debuglogs('unable to stat'+tmpstr);
         FileData.Add(RegExpr.Match[1]+' not found');
         exit(true);
      end;
      logs.Debuglogs('Loading '+tmpstr);
      FileData.LoadFromFile(tmpstr);
      exit(true);
  end;


RegExpr.expression:='^SetupIndexFile';
  if RegExpr.Exec(uri) then begin
      FileData.Add('Deleting index.ini');
      if FileExists('/usr/share/artica-postfix/ressources/index.ini') then logs.DeleteFile('/usr/share/artica-postfix/ressources/index.ini');
      tmpstr:=logs.FILE_TEMP();
      fpsystem('/usr/share/artica-postfix/bin/artica-update --index --verbose >'+tmpstr +' 2>&1');
      FileData.Add(logs.ReadFromFile(tmpstr) );
      logs.DeleteFile(tmpstr);
      exit(true);
  end;


RegExpr.expression:='^DansGuardianReload';
  if RegExpr.Exec(uri) then begin
      dansguardian.DANSGUARDIAN_RELOAD();
      exit(true);
  end;


RegExpr.expression:='^DansguardianRebuildStatsSites';
  if RegExpr.Exec(uri) then begin
     GLOBAL_INI.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+ ' ' + GLOBAL_INI.get_ARTICA_PHP_PATH()+'/cron.dansguardian.php --rebuild-sites');
     exit(true);
  end;

RegExpr.expression:='^DansGuardianCompileStatistics';
 if RegExpr.Exec(uri) then begin
     logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install --dansguardian-stats');
     exit(true);
 end;

RegExpr.expression:='^BackupFileList';
if RegExpr.Exec(uri) then begin
      path:=SYS.GET_INFO('ArticaBackupTargetLocalPath');
      SYS.DirFiles(path,'*.tar.gz');
      for i:=0 to SYS.DirListFiles.Count-1 do begin
           FileData.Add('file:'+SYS.DirListFiles.Strings[i]+';'+inttostr(SYS.FileSize_ko(path+'/'+SYS.DirListFiles.Strings[i])));
      end;
exit(true);
end;


RegExpr.expression:='^ReleaseMailPath:(.+)';
     if RegExpr.Exec(uri) then begin
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-mailarchive --release "' + RegExpr.Match[1]+'"';
        logs.Debuglogs(command_line);
        fpsystem(command_line+' &');
        exit(true);
    end;


{RegExpr.expression:='^MailManListInfo:(.+)';
if RegExpr.Exec(uri) then begin
     mailman:=tmailman.Create(SYS);
     FileData.Add(mailman.ListInfo(RegExpr.Match[1]));
     mailman.free;
     exit(true);
end;}


RegExpr.expression:='^dotclear_status';
  if RegExpr.Exec(uri) then begin
      dotclear:=tdotclear.CReate(SYS);
       FileData.Add(dotclear.STATUS());
       exit(true);
  end;

RegExpr.expression:='^DotClearRestart';
  if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart dotclear');
      exit(true);
  end;

RegExpr.expression:='^DHCPDLogs';
  if RegExpr.Exec(uri) then begin
      if not FIleExists(SYS.LOCATE_SYSLOG_PATH()) then begin
         FileData.Add('Error, unable to stat syslog...');
         exit(true);
      end;
      tmpstr:=logs.FILE_TEMP();
      fpsystem('/usr/bin/tail -n 1500 '+SYS.LOCATE_SYSLOG_PATH()+'|grep dhcpd: >'+tmpstr+' 2>&1');
      FileData.Add(logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
  end;




RegExpr.expression:='^OpenLdapRestart';
  if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart ldap');
      exit(true);
  end;


RegExpr.expression:='^DotClearLdap';
  if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET(global_ini.get_ARTICA_PHP_PATH()+'/bin/artica-ldap --dotclear');
      exit(true);
  end;

RegExpr.expression:='^DotClearRebuildTables';
  if RegExpr.Exec(uri) then begin
      logs.EXECUTE_SQL_FILE(global_ini.get_ARTICA_PHP_PATH()+'/bin/install/dotclear.sql','artica_backup');
      GLOBAL_INI.THREAD_COMMAND_SET(global_ini.get_ARTICA_PHP_PATH()+'/bin/artica-ldap --dotclear');
      exit(true);
  end;








  RegExpr.expression:='^RestartDaemon';
  if RegExpr.Exec(uri) then begin
     GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart daemon');
     exit(true);
  end;

RegExpr.expression:='^WBLReplicNow';
  if RegExpr.Exec(uri) then begin
     fpsystem('/etc/init.d/artica-postfix start daemon');
     GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-ldap --wbld');
     exit(true);
  end;





  RegExpr.expression:='^EmergencyStart:(.+)';
  if RegExpr.Exec(uri) then begin
     tmpstr:=logs.FILE_TEMP();
     cmd:='/etc/init.d/artica-postfix start '+RegExpr.Match[1] + ' --verbose >'+tmpstr+' 2>&1';
     logs.Debuglogs(cmd);
     fpsystem(cmd);
     if FileExists(tmpstr) then begin
        FileData.LoadFromFile(tmpstr);
        logs.Debuglogs('Success get logs from "'+RegExpr.Match[1]+'" command RECEIVE ' + IntTOStr(FileData.Count)+' lines numbers' );
     end else begin
        logs.Debuglogs('Unable to stat ' +tmpstr);
     end;
     logs.DeleteFile(global_ini.get_ARTICA_PHP_PATH()+'/ressources/logs/global.status.ini');
     logs.DeleteFile(tmpstr);
     exit(true);
  end;







  RegExpr.expression:='^SynchronizeRsyncDaemon';
  if RegExpr.Exec(uri) then begin
      logs.Debuglogs('Schedule '+SYS.LOCATE_PHP5_BIN() + ' '+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/exec.rsyncd.conf.php');
      GLOBAL_INI.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN() + ' '+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/exec.rsyncd.conf.php');
      exit(true);
  end;

  RegExpr.expression:='^RsyncdEvents';
  if RegExpr.Exec(uri) then begin
      tmpstr:=logs.FILE_TEMP();
      fpsystem('/usr/bin/tail -n 200 /var/log/rsync/rsyncd.log >'+tmpstr+' 2>&1');
      FileData.Add(logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
  end;



RegExpr.expression:='^MailBoxRemoteSync:(.+)';
  if RegExpr.Exec(uri) then begin
   logs.Debuglogs('Starting synchronize external mailbox for ' + RegExpr.Match[1]);
   GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-ldap --imapsync_import '+ RegExpr.Match[1]);
   exit(true);
  end;



   RegExpr.expression:='MailBoxLocalSync:F=(.+?);T=(.+?);D=([0-9]+)';
   if RegExpr.Exec(uri) then begin
     logs.Debuglogs('Starting export mailbox from ' + RegExpr.Match[1] + ' to ' + RegExpr.Match[2] + ' delete messages=' +RegExpr.Match[3] );
     tmpstr:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/imap_export/'+RegExpr.Match[1]+'-' +RegExpr.Match[2]+'.log';
     if FileExists(tmpstr) then logs.DeleteFile(tmpstr);
     logs.LogGeneric('Starting export mailbox from ' + RegExpr.Match[1] + ' to ' + RegExpr.Match[2] + ' delete messages=' +RegExpr.Match[3],tmpstr);
     logs.LogGeneric('Please waiting few minutes',tmpstr);
     fpsystem('/bin/chmod -R 755 '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/imap_export');
     GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-ldap --imapsync '+RegExpr.Match[1]+' '+RegExpr.Match[2]+' '+ RegExpr.Match[3]);
   end;

    RegExpr.expression:='MailBoxLocalSyncLogs:F=(.+?);T=(.+)';
    if RegExpr.Exec(uri) then begin
       logs.Debuglogs('Starting show logs mailbox from ' + RegExpr.Match[1] + ' to ' + RegExpr.Match[2]);
       tmpstr:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/imap_export/'+RegExpr.Match[1]+'-' +RegExpr.Match[2]+'.log';
       tempstr:=logs.FILE_TEMP();
       fpsystem('/usr/bin/tail -n 100 ' + tmpstr + ' >' + tempstr + ' 2>&1');
       if FileExists(tempstr) then begin
           FileData.LoadFromFile(tempstr);
           logs.DeleteFile(tempstr);

       end;
       exit(true);

    end;








   RegExpr.expression:='^AppliCenterTailDebugInfos:(.+)';
   if RegExpr.Exec(uri) then begin
         cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install/' +RegExpr.Match[1] + '.dbg';
         tempstr:=Logs.FILE_TEMP();
         if FileExists(cmd) then begin
            fpsystem('tail -n 3 '+cmd+' >'+tempstr+ ' 2>&1');
            FileData.Add(logs.ReadFromFile(tempstr));
            logs.DeleteFile(tempstr);
         end;
         exit(true);
   end;





 RegExpr.expression:='ARTICA_ALL_STATUS';
   if RegExpr.Exec(uri) then begin
      cgiop.ARTICA_ALL_STATUS();
      FileData.AddStrings(cgiop.FA);
      result:=true;
      exit;
   end;

   RegExpr.expression:='dirdir:(.+)';
   if RegExpr.Exec(uri) then begin
       SYS:=Tsystem.Create();
       SYS.DirDir(RegExpr.Match[1]);
       FileData.AddStrings(SYS.DirListFiles);
       result:=true;
       exit;
   end;

   RegExpr.expression:='^SaveJchkmailConfig';
   if RegExpr.Exec(uri) then begin
     GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-ldap --jckmail');
     exit(true);
   end;

   RegExpr.expression:='^GetjchkMailOrgConfig:(.+)';
   if RegExpr.Exec(uri) then begin
     if FileExists('/var/jchkmail/cdb/'+RegExpr.Match[1]) then begin
        FileData.LoadFromFile('/var/jchkmail/cdb/'+RegExpr.Match[1]);
     end else begin
        if FileExists('/etc/mail/jchkmail/'+ RegExpr.Match[1]) then FileData.LoadFromFile('/etc/mail/jchkmail/'+RegExpr.Match[1]);
     end;
     exit(true);
   end;





   RegExpr.expression:='systemtemp';
  if RegExpr.Exec(uri) then begin
     FileData.Add(SYS.materiel_get_temperature());
     exit(true);
   end;

   // SHARED FOLDERS
  RegExpr.expression:='bind9status';
   if RegExpr.Exec(uri) then begin
      FileData.Add(bind9.STATUS());
      exit(true);
   end;

  RegExpr.expression:='mailspystatus';
   if RegExpr.Exec(uri) then begin
      FileData.Add(mailspy.STATUS());
      exit(true);
   end;

RegExpr.expression:='amavisstatus';
   if RegExpr.Exec(uri) then begin
      amavis:=tamavis.Create(SYS);
      FileData.Add(amavis.STATUS());
      FileData.Add(spamass.SPAMASSASSIN_STATUS());
      FileData.Add(clamav.CLAMAV_STATUS());
      amavis.Free;
      exit(true);
   end;

RegExpr.expression:='saveamavis';
   if RegExpr.Exec(uri) then begin
       GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-ldap -amavis');
   end;

RegExpr.expression:='^ApplyAmavis';
   if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-ldap -amavis --without-restart --verbose >'+tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
   end;
RegExpr.expression:='^STOP_AMAVISDNEW';
   if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       fpsystem('/etc/init.d/artica-postfix stop amavis >'+tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
   end;
RegExpr.expression:='^START_AMAVISDNEW';
   if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       fpsystem('/etc/init.d/artica-postfix start amavis >'+tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
   end;


RegExpr.expression:='^amavisevents';
   if RegExpr.Exec(uri) then begin
       if not FileExists('/var/log/amavis/amavis.log') then begin
            FileData.Add('Unable to stat /var/log/amavis/amavis.log');
            exit(true);
       end;
      tmpstr:=logs.FILE_TEMP();
      fpsystem('/usr/bin/tail -n 900 /var/log/amavis/amavis.log >'+tmpstr+' 2>&1');
      FileData.LoadFromFile(tmpstr);
      logs.DeleteFile(tmpstr);
      exit(true);
   end;




  RegExpr.expression:='mailspyreports:(.+)';
   if RegExpr.Exec(uri) then begin
      path:='/usr/local/httpd/htdocs/mailspy/all/' + RegExpr.Match[1];
      tmpstr:=RegExpr.Match[1];
      RegExpr.Expression:='([a-zA-Z\-\_\.]+)?/([a-zA-Z\-\_\.]+)?/';
      RegExpr.Exec(tmpstr);
      logs.Debuglogs('Load report '+path + ' directory "' + RegExpr.Match[1]+'"');

      if not FileExists(path) then begin
           logs.Debuglogs('1) unable to stat report '+path + ' replace ' + RegExpr.Match[2]);
           path:=AnsiReplaceText(path,RegExpr.Match[2]+'/','');
           if not FileExists(path) then begin
             logs.Debuglogs('2) unable to stat report '+path );
              tmpstr:=ExtractFileName(path);
              path:='/usr/local/httpd/htdocs/mailspy/all/current/' + tmpstr;
              if not FileExists(path) then begin
                logs.Debuglogs('3) unable to stat report '+path );
                exit;
              end;
           end;
       end;
      FileData.LoadFromFile(path);
      exit(true);

   end;

  RegExpr.expression:='mailspylogs';
   if RegExpr.Exec(uri) then begin
      path:=logs.FILE_TEMP();
      fpsystem('/usr/bin/tail -n 200 /var/log/mailspy/mailspy >' + path + ' 2>&1');
      if FileExists(path) then begin
         FileData.LoadFromFile(path);
         logs.DeleteFile(path);
      end;
      exit(true);
   end;

  RegExpr.expression:='collectdstatus';
   if RegExpr.Exec(uri) then begin
      FileData.Add(collectd.STATUS());
      exit(true);
   end;

  RegExpr.expression:='articamakestatus';
   if RegExpr.Exec(uri) then begin
      FileData.Add(GLOBAL_INI.ARTICA_MAKE_STATUS());
      exit(true);
   end;

  RegExpr.expression:='mysqlstatus';
   if RegExpr.Exec(uri) then begin
      mysql:=tmysql_daemon.Create(GLOBAL_INI.SYS);
      FileData.Add(mysql.STATUS());
      exit(true);
   end;

  RegExpr.expression:='restartmysql';
   if RegExpr.Exec(uri) then begin
      mysql:=tmysql_daemon.Create(GLOBAL_INI.SYS);
      tmpstr:=logs.FILE_TEMP();
      logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -modules');
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install -shutdown mysql >'+tmpstr +' 2>&1');
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install -watchdog mysql >>'+tmpstr +' 2>&1');
      FileData.LoadFromFile(tmpstr);
      logs.DeleteFile(tmpstr);
      exit(true);
   end;


  RegExpr.expression:='Bind9Compile';
   if RegExpr.Exec(uri) then begin
      GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart bind9');
      exit(true);
   end;

   RegExpr.expression:='SharedFolderConf';
   if RegExpr.Exec(uri) then begin
      SYS.THREAD_COMMAND_SET('/etc/init.d/autofs reload');
      SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.AutoFS.php');
      exit(true);
   end;

   RegExpr.expression:='SharedConfDeleteFolder:(.+)';
   if RegExpr.Exec(uri) then begin
       SYS.THREAD_COMMAND_SET('/etc/init.d/autofs reload');
       SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.AutoFS.php');
      exit(true);
   end;

   RegExpr.expression:='SharedConfDeleteFolderAll';
   if RegExpr.Exec(uri) then begin
       SYS.THREAD_COMMAND_SET('/etc/init.d/autofs reload');
       SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.AutoFS.php');
      exit(true);
   end;

   //repository
RegExpr.expression:='reposuninstall';
   if RegExpr.Exec(uri) then begin
      logs.Debuglogs(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-apt --uninstall');
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-apt --uninstall &');
      exit(true);
   end;



RegExpr.expression:='reposinfo:(.+)';
   if RegExpr.Exec(uri) then begin
      tmpstr:=LOGS.FILE_TEMP();
      logs.Debuglogs('reposinfo::' + cmd);
      cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-apt --info-install '+RegExpr.Match[1]+' >'+tmpstr + ' 2>&1';

      fpsystem(cmd);
      if not FileExists(tmpstr) then begin
           logs.Debuglogs('reposinfo:: unable to stat ' + tmpstr);
           exit(true);
       end;
       FileData.LoadFromFile(tmpstr);
       logs.DeleteFile(tmpstr);
       exit(true);
   end;


RegExpr.expression:='reposfind:(.+)';
   if RegExpr.Exec(uri) then begin
      tmpstr:=LOGS.FILE_TEMP();
      logs.Debuglogs('reposfind::' + cmd);
      cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-apt --find '+RegExpr.Match[1]+' >'+tmpstr + ' 2>&1';

      fpsystem(cmd);
      if not FileExists(tmpstr) then begin
           logs.Debuglogs('reposfind:: unable to stat ' + tmpstr);
           exit(true);
       end;
       FileData.LoadFromFile(tmpstr);
       logs.DeleteFile(tmpstr);
       exit(true);
   end;

RegExpr.expression:='reposuninstall';
   if RegExpr.Exec(uri) then begin
      logs.Debuglogs(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-apt --uninstall');
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-apt --uninstall &');
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='aptupgrade';
   if RegExpr.Exec(uri) then begin
      cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-apt --upgrade';
      logs.Debuglogs(cmd);
      //fpsystem(cmd +' &');
      SYS.THREAD_COMMAND_SET(cmd);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='aptarticaevents';
   if RegExpr.Exec(uri) then begin
      FileData.Add(logs.ReadFromFile('/var/log/artica-postfix/artica-apt.debug'));
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^ChangeMysqlLocalRoot:(.+?);(.+)';
   if RegExpr.Exec(uri) then begin
      tmpstr:=logs.FILE_TEMP();
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install --change-mysqlroot --inline '+RegExpr.Match[1]+' '+RegExpr.Match[2]+ '>'+tmpstr+' 2>&1');
      FileData.Add(logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------





   //Active Directory
   RegExpr.expression:='TestingADConnection:(.+)';
   if RegExpr.Exec(uri) then begin
       tmpstr:=LOGS.FILE_TEMP()+'_AD';
       cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ad --test-connection ' + RegExpr.Match[1]+' >' + tmpstr + ' 2>&1';
       logs.Debuglogs('TestingADConnection::' + cmd);
       fpsystem(cmd);
       if not FileExists(tmpstr) then begin
           logs.Debuglogs('TestingADConnection:: unable to stat ' + tmpstr);
           exit(true);
       end;
       FileData.LoadFromFile(tmpstr);
       logs.Debuglogs('TestingADConnection:: result='+FileData.Text);
       logs.DeleteFile(tmpstr);
       exit(true);
   end;



   //SAMBA #########################################################################################################################################################

RegExpr.expression:='^NetUsePrivs:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
   logs.Debuglogs('Parsing users system privileges for "'+RegExpr.Match[2]+'"' );
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/bin/net -U administrator%'+RegExpr.Match[1]+' rpc rights list '+RegExpr.Match[2]+' >'+tmpstr+' 2>&1';
   logs.Debuglogs(cmd);
   fpsystem(cmd);
   try
         FileData.LoadFromFile(tmpstr);
      except
         logs.logs('NetUsePrivs fatal error');
         FileData.Add('FATAL ERROR');
         exit();
      end;
    logs.DeleteFile(tmpstr);
    exit(true);
end;

RegExpr.expression:='^SetNetUsePrivs:(.+?);(.+?);(.+?);(.+)';
if RegExpr.Exec(uri) then begin
   logs.Debuglogs(RegExpr.Match[4]+' users system privileges "'+RegExpr.Match[2]+'" for "'+RegExpr.Match[3]+'"' );
   tmpstr:=logs.FILE_TEMP();
   cmd:='/usr/bin/net -U administrator%'+RegExpr.Match[1]+' rpc rights '+RegExpr.Match[4]+' "'+RegExpr.Match[3]+'" '+RegExpr.Match[2]+' >'+tmpstr+' 2>&1';
   logs.Debuglogs(cmd);
   fpsystem(cmd);
   try
         FileData.LoadFromFile(tmpstr);
      except
         logs.logs('NetUsePrivs fatal error');
         FileData.Add('FATAL ERROR');
         exit();
      end;
    logs.DeleteFile(tmpstr);
    exit(true);
end;

RegExpr.expression:='^LdapRebuildDatabases';
if RegExpr.Exec(uri) then begin
   tmpstr:=logs.FILE_TEMP();
   fpsystem('/usr/share/artica-postfix/bin/artica-backup --rebuild-ldap --verbose >'+ tmpstr +' 2>&1');
   FileData.Add(logs.ReadFromFile(tmpstr));
   logs.DeleteFile(tmpstr);
   exit(true);
end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^autofsReload';
if RegExpr.Exec(uri) then begin
   SYS.THREAD_COMMAND_SET('/etc/init.d/autofs reload');
   exit(true);
end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^ServiceAutofsRestart';
if RegExpr.Exec(uri) then begin
   logs.DeleteFile('/usr/share/artica-postfix/ressources/logs/autofs.restart.log');
   logs.WriteToFile('{scheduled}: {please_wait}','/usr/share/artica-postfix/ressources/logs/autofs.restart.log');
   logs.OutputCmd('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/autofs.restart.log');
   SYS.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart autofs >/usr/share/artica-postfix/ressources/logs/autofs.restart.log 2>&1');
   exit(true);
end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^chmodRWU:(.+)';
if RegExpr.Exec(uri) then begin
   fpsystem('/bin/chmod 775 '+ RegExpr.Match[1]);
   exit(true);
end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='nmapmem';
   if RegExpr.Exec(uri) then begin
      FileData.Add(SYS.PROCESS_LIST_PID(SYS.LOCATE_NMAP()));
      exit(true);
   end;
 RegExpr.expression:='NmapScanNow';
   if RegExpr.Exec(uri) then begin
      logs.DeleteFile('/etc/artica-postfix/nmap.touch');
      GLOBAL_INI.ADD_PROCESS_QUEUE(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -nmap --force ');
      exit(true);
   end;



 RegExpr.expression:='^homesFoldersList';
   if RegExpr.Exec(uri) then begin
      try
         FileData.AddStrings(SYS.DirDir('/home'));
      except
         logs.Syslogs('Fatal error while executing command "homesFoldersList"');
         exit;
      end;
      exit(true);
   end;


//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='RoundCubeStatus';
   if RegExpr.Exec(uri) then begin
      FileData.Add(roundcube.STATUS());
      logs.Debuglogs('RoundCubeStatus: '+IntToStr(length(FileData.Text))+' '+FileData.Text);
      exit(true);
   end;

 RegExpr.expression:='RoundCubeInstall';
   if RegExpr.Exec(uri) then begin
      logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-roundcube --install');
      exit(true);
   end;

 RegExpr.expression:='RoundCubeRemove';
   if RegExpr.Exec(uri) then begin
      logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-roundcube --uninstall');
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='ForceRefreshLeft';
   if RegExpr.Exec(uri) then begin
      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.admin.status.postfix.flow.php');
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------




 RegExpr.expression:='IsJoomlaInstalled:(.+)';
   if RegExpr.Exec(uri) then begin
      tmpstr:=RegExpr.Match[1];
      tmpstr:=AnsiReplaceText(tmpstr,'.','_');
      if DirectoryExists('/usr/share/artica-groupware/domains/joomla/'+tmpstr) then begin
          FileData.Add('1');
      end else begin
          FileData.Add('0');
      end;
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='IsSugarCRMInstalled:(.+)';
   if RegExpr.Exec(uri) then begin
      tmpstr:=RegExpr.Match[1];
      tmpstr:=AnsiReplaceText(tmpstr,'.','_');
      if FileExists('/usr/share/artica-groupware/domains/sugarcrm/'+tmpstr+'/sugar_version.php') then begin
          FileData.Add('1');
      end else begin
          logs.Debuglogs('unable to stat /usr/share/artica-groupware/domains/sugarcrm/'+tmpstr+'/sugar_version.php');
          FileData.Add('0');
      end;
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------

 RegExpr.expression:='JoomlaInstall:(.+)';
   if RegExpr.Exec(uri) then begin
     tmpstr:=logs.FILE_TEMP();
     fpsystem('/usr/share/artica-postfix/bin/artica-make APP_JOOMLA org='+ RegExpr.Match[1]+' -- >'+tmpstr+' 2>&1');
     logs.Debuglogs(logs.ReadFromFile(tmpstr));
     FileData.Add(logs.ReadFromFile(tmpstr));
     logs.DeleteFile(tmpstr);
     exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='sugarInstall:(.+)';
   if RegExpr.Exec(uri) then begin
     tmpstr:=logs.FILE_TEMP();
     fpsystem('/usr/share/artica-postfix/bin/artica-make APP_SUGARCRM org='+ RegExpr.Match[1]+' -- >'+tmpstr+' 2>&1');
     SYS.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart apache-groupware');
     logs.Debuglogs(logs.ReadFromFile(tmpstr));
     FileData.Add(logs.ReadFromFile(tmpstr));
     logs.DeleteFile(tmpstr);
     exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='SugarCRMOuVersion:(.+)';
   if RegExpr.Exec(uri) then begin
     sugarcrm:=tsugarcrm.Create(SYS);
     TempDatasLogs:=RegExpr.Match[1];
     TempDatasLogs:=AnsiReplaceText(TempDatasLogs,'.','_');
     tmpstr:='/usr/share/artica-groupware/domains/sugarcrm/'+TempDatasLogs;
     FileData.Add(sugarcrm.VERSION(tmpstr));
     sugarcrm.Free;
     exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='JoomlaOuVersion:(.+)';
   if RegExpr.Exec(uri) then begin
     opengoo:=Topengoo.Create(SYS);
     TempDatasLogs:=RegExpr.Match[1];
     TempDatasLogs:=AnsiReplaceText(TempDatasLogs,'.','_');
     tmpstr:='/usr/share/artica-groupware/domains/joomla/'+TempDatasLogs+'/libraries/joomla/version.php';
     FileData.Add(opengoo.GetJoomlaVersion(tmpstr));
     opengoo.Free;
     exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='JoomlaReload';
   if RegExpr.Exec(uri) then begin
     SYS.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart apache-groupware');
     exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------



//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='mkdirp:(.+)';
   if RegExpr.Exec(uri) then begin
      logs.logs('Create folder "' +RegExpr.Match[1]+'"');
      forcedirectories(RegExpr.Match[1]);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='RecoveryLdapList';
   if RegExpr.Exec(uri) then begin
       sys.DirFiles('/opt/artica/ldap-backup','*.gz');
       for i:=0 to sys.DirListFiles.Count-1 do begin
          FileData.Add(sys.DirListFiles.Strings[i]+';'+IntTOstr(sys.FileSize_ko('/opt/artica/ldap-backup/'+sys.DirListFiles.Strings[i])));
       end;
       exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='dstatmem:(.+)';
   if RegExpr.Exec(uri) then begin
      dstat:=tdstat.Create(SYS);
      dstat.GENERATE_MEMORY(RegExpr.Match[1]);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='dstatcpu:(.+)';
   if RegExpr.Exec(uri) then begin
      dstat:=tdstat.Create(SYS);
      dstat.GENERATE_CPU(RegExpr.Match[1]);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='dstatpostfix:(.+)';
   if RegExpr.Exec(uri) then begin
      dstat:=tdstat.Create(SYS);
      dstat.GENERATE_POSTFIX(RegExpr.Match[1]);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
 RegExpr.expression:='dstatlastcpu';
   if RegExpr.Exec(uri) then begin
      tmpstr:=logs.FILE_TEMP();
      fpsystem('/usr/bin/tail -n 1 /var/log/artica-postfix/dstat_cpu.csv >'+tmpstr +' 2>&1');
      FileData.Add(logs.ReadFromFile(tmpstr));
      logs.DeleteFile(tmpstr);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------



 RegExpr.expression:='dkimstatus';
   if RegExpr.Exec(uri) then begin
      FileData.Add(dkim.STATUS());
      exit(true);
   end;

 RegExpr.expression:='dkimfiltersave';
   if RegExpr.Exec(uri) then begin
      cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -dkimfilter html >/opt/artica/tmp/dkimfilter.tmp 2>&1';
      logs.logs('->' + cmd);
      fpsystem(cmd);
      if FileExists('/opt/artica/tmp/dkimfilter.tmp') then begin
         FileData.LoadFromFile('/opt/artica/tmp/dkimfilter.tmp');
         logs.DeleteFile('/opt/artica/tmp/dkimfilter.tmp');
      end;
      exit(true);
   end;


 RegExpr.expression:='rmdirp:(.+)';
   if RegExpr.Exec(uri) then begin
      path:=trim(RegExpr.Match[1]);
      if length(path)=0 then exit(true);
      if not DirectoryExists(path) then exit(true);
      path:=AnsiReplaceText(path,' ','\ ');

      fpsystem('/bin/rm -rf ' +  path + ' >/opt/artica/logs/rm.tmp 2>&1');
      logs.logs('delete folder "' +path+'" (' + trim(GLOBAL_INI.ReadFileIntoString('/opt/artica/logs/rm.tmp')) + ')');
      exit(true);
   end;

// Kaspersky Anti-spam ########################################################################################################################################

   RegExpr.expression:='KasCompileGroups';
   if RegExpr.Exec(uri) then begin
      logs.logs('executing : ' +GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -kasgroups');
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -kasgroups');
       exit(true);
   end;

RegExpr.expression:='mimedefangstatus';
   if RegExpr.Exec(uri) then begin
      logs.logs('executing : mimedefang status');
      try
       FileData.Add(mimedef.MIMEDEFANG_STATUS());
       except
       logs.Debuglogs('mimedefangstatus:: fatal error');
      end;
       exit(true);
   end;


RegExpr.expression:='mimedefangsave';
   if RegExpr.Exec(uri) then begin
      logs.logs('executing : ' +GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -mimedefang');
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -mimedefang');
       exit(true);
   end;


// SpamAssassin ########################################################################################################################################



   RegExpr.expression:='CORE_SPAMASS_STATUS';
   if RegExpr.Exec(uri) then begin
         FileData.Add(spamass.SPAMASSASSIN_STATUS());
         exit(true);
   end;

    RegExpr.expression:='spamassassin_status';
     if RegExpr.Exec(uri) then begin
       FileData.Add(spamass.SPAMASSASSIN_STATUS());
        logs.logs(' ParseUri:: spamassassin_status status ' + IntToStr(FileData.Count) + ' lines');
        exit(true);
    end;





    RegExpr.expression:='^SpamAssassinReload';
    if RegExpr.Exec(uri) then begin
       spamass.SPAMASSASSIN_RELOAD();
       exit(true);
    end;




   RegExpr.Expression:='SET_ARTICA_FILTER:(.+?)=(.+)';
   if RegExpr.Exec(uri) then begin
         cgiop.SET_PARAMETERS_ARTICA_FILTER(RegExpr.Match[1],RegExpr.Match[2]);
         exit(true);
   end;

   RegExpr.Expression:='MAILMAN_SINGLE:(.+)';
   if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-mailman -single ' + RegExpr.Match[1]);
         if FileExists('/opt/artica/logs/mailman.txt') then FileData.LoadFromFile('/opt/artica/logs/mailman.txt');
         exit(true);
   end;


  RegExpr.Expression:='CLOCKS';
  if RegExpr.Exec(uri) then begin
      FileData.Add(GLOBAL_INI.SYSTEM_GET_SYS_DATE()+'|' + GLOBAL_INI.SYSTEM_GET_HARD_DATE());
      exit(true);
  end;

  RegExpr.Expression:='SetSystemTime:([0-9]+)';
  if RegExpr.Exec(uri) then begin
       LOGS.logs('Tparsehttp:: set time to ' + RegExpr.Match[1]);
       fpsystem('/bin/date ' +  RegExpr.Match[1]);
       exit(true);
  end;
 RegExpr.Expression:='ConvertTimeSystemToHard';
  if RegExpr.Exec(uri) then begin
    LOGS.logs('Tparsehttp::  /sbin/hwclock --systohc --utc');
     fpsystem('/sbin/hwclock --systohc --utc');
     exit(true);
  end;


RegExpr.Expression:='ChangeUserAdmin:(.+?)ChangeUserPassword:(.+)';
   if RegExpr.Exec(uri) then begin
          LOGS.logs('Change global administrator account...' + RegExpr.Match[1]);
          cgiop.CHANGE_SUPERUSER(RegExpr.Match[1],RegExpr.Match[2]);
         exit(true);
   end;


RegExpr.Expression:='printenvpath';
   if RegExpr.Exec(uri) then begin
          FileData.Add(GLOBAL_INI.SYSTEM_ENV_PATHS());
         exit(true);
   end;

RegExpr.Expression:='AUTOINSTALL:(.+)';
   if RegExpr.Exec(uri) then begin
         cgiop.APP_AUTOINSTALL(trim(RegExpr.Match[1]));
         FileData.AddStrings(cgiop.FA);
         exit(true);
   end;
RegExpr.Expression:='AUTOREMOVE:(.+)';
   if RegExpr.Exec(uri) then begin
         cgiop.APP_AUTOREMOVE(trim(RegExpr.Match[1]));
         FileData.AddStrings(cgiop.FA);
         exit(true);
   end;

RegExpr.Expression:='changemysqlpassword';
   if RegExpr.Exec(uri) then begin
         tempstr:='/opt/artica/tmp/ChangeMysqlPassword';
         LOGS.logs('ParseUri -> Change mysql password ' + GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install --change-mysqlroot >' + tempstr +' 2>&1');
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install --change-mysqlroot >' + tempstr +' 2>&1');
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/process1 --force');
         try
         if not FileExists(tempstr) then begin
             LOGS.logs('ParseUri -> unable to stat '+tempstr);
             exit(true);
         end;
         FileData.LoadFromFile(tempstr);
         logs.DeleteFile(tempstr);
         except
          LOGS.logs('ParseUri -> fatal error');
         end;
         exit(true);
   end;

RegExpr.Expression:='restartmysql';
   if RegExpr.Exec(uri) then begin
         cmd:='/etc/init.d/artica-postfix restart mysql';
         LOGS.OutputCmd(cmd);
         exit(true);
   end;

RegExpr.Expression:='restartmimedefang';
   if RegExpr.Exec(uri) then begin
         cmd:='/etc/init.d/artica-postfix restart mimedefang';
         LOGS.OutputCmd(cmd);
         exit(true);
   end;

RegExpr.Expression:='restartsyslogng';
   if RegExpr.Exec(uri) then begin
         cmd:='/etc/init.d/artica-postfix restart syslogng';
         LOGS.OutputCmd(cmd);
         exit(true);
   end;

RegExpr.Expression:='restartmysqldependencies';
   if RegExpr.Exec(uri) then begin
         logs.Debuglogs('restartmysqldependencies detected');
         fpsystem('/etc/init.d/artica-postfix start daemon');
         GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart syslogng');
         GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart roundcube');
         GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart collectd');
         exit(true);
   end;

RegExpr.Expression:='restartcyrus';
   if RegExpr.Exec(uri) then begin
         logs.Debuglogs('restartcyrus detected');
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -syncmodules');
         fpsystem('/etc/init.d/artica-postfix start daemon');
         GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
         exit(true);
   end;


   RegExpr.expression:='getMyConf';
   if RegExpr.Exec(uri) then begin
      FileData.LoadFromFile('/etc/artica-postfix/artica-postfix.conf');
      result:=true;
   end;


   RegExpr.expression:='PostFixChangeAutoInterface:([a-z0-9]+)';
   if RegExpr.Exec(uri) then begin
      LOGS.logs('ParseUri -> Save artica conf ChangeAutoInterface set to yes and follow nic : ' +trim(RegExpr.Match[1]));
      GLOBAL_INI.set_INFOS('ChangeAutoInterface',trim(RegExpr.Match[1]));
      GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install -postfix inet');
      result:=true;
   end;

   RegExpr.expression:='postfixtesttls';
if RegExpr.Exec(uri) then begin

      LOGS.logs('ParseUri ->echo QUIT|'+SYS.LOCATE_OPENSSL_TOOL_PATH()+' s_client -connect 127.0.0.1:25 -starttls smtp');
      fpsystem('echo QUIT|'+SYS.LOCATE_OPENSSL_TOOL_PATH()+' s_client -connect 127.0.0.1:25 -starttls smtp >/opt/artica/logs/test-tls.tmp 2>&1 &');
      tmpstr:=GLOBAL_INI.SYSTEM_PROCESS_LIST_PID(SYS.LOCATE_OPENSSL_TOOL_PATH());
      sleep(1000);
      if length(tmpstr)>0 then begin
          LOGS.logs('ParseUri ->Kill processes '+tmpstr);
          fpsystem('/bin/kill -9 ' + tmpstr);
      end;
      if FileExists('/opt/artica/logs/test-tls.tmp') then begin
         FileData.LoadFromFile('/opt/artica/logs/test-tls.tmp');
         GLOBAL_INI.DeleteFile('/opt/artica/logs/test-tls.tmp');
      end else begin
         LOGS.logs('ParseUri ->unable to stat /opt/artica/logs/test-tls.tmp');
      end;
      result:=true;
   end;

   RegExpr.expression:='getMyLdapConf';
   if RegExpr.Exec(uri) then begin
      if fileexists('/etc/artica-postfix/artica-postfix.conf') then FileData.LoadFromFile('/etc/artica-postfix/artica-postfix.conf');
      result:=true;
   end;

   RegExpr.expression:='SaveMyConf:(.+)';
   if RegExpr.Exec(uri) then begin
      logs.logs('ParseUri::  -> Save artica conf file : ' +trim(RegExpr.Match[1]));
      fpsystem('/bin/mv ' +  trim(RegExpr.Match[1]) + ' /etc/artica-postfix/');
      exit(true);
   end;

   RegExpr.expression:='restart_http_engine';
   if RegExpr.Exec(uri) then begin
      logs.logs('ParseUri::  -> Apply http engine settings and restart apache or lighttpd');
      logs.logs(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -apply-httpd');
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -apply-httpd');
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/process1 --force');
      GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart dotclear');
      GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart roundcube');
      GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart obm');
      exit(true);
   end;


    RegExpr.expression:='SavePostfixHeaderCheck';
    if RegExpr.Exec(uri) then begin
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -maincf');
      exit(true);
   end;

  RegExpr.expression:='StartPostfix';
    if RegExpr.Exec(uri) then begin
          cgiop.SYSTEM_START_STOP_SERVICES('APP_POSTFIX',true);
          fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/process1');
    end;

RegExpr.expression:='CrossRoadsSyncSlaves';
   if RegExpr.Exec(uri) then begin
       logs.logs('ParseUri::  execute "'+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -crossroads sync"');
       GLOBAL_INI.ADD_PROCESS_QUEUE(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -crossroads sync');
       result:=true;
       exit;
   end;

RegExpr.expression:='process1';
   if RegExpr.Exec(uri) then begin
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/process1 --force');
       result:=true;
       exit;
   end;

RegExpr.expression:='CrossRoadsApply:(.+)';
   if RegExpr.Exec(uri) then begin
       logs.logs('ParseUri::  Apply CrossRoads configuration ' + RegExpr.Match[1]);
       logs.logs('ParseUri::  execute "'+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -crossroads apply ' +RegExpr.Match[1]);
       GLOBAL_INI.ADD_PROCESS_QUEUE(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -crossroads apply ' +RegExpr.Match[1]);
       result:=true;
       exit();
   end;

RegExpr.expression:='FileExists:(.+)';
   if RegExpr.Exec(uri) then begin
       logs.logs('ParseUri::  testing file ' + RegExpr.Match[1]);
       if FileExists(RegExpr.Match[1]) then begin
          FileData.Add('{TRUE}') end
       else begin
          FileData.Add('{FALSE}');
       end;
       result:=true;
       exit();
   end;

   RegExpr.expression:='pid';
   if RegExpr.Exec(uri) then begin
      FileData.Add(intToStr(fpgetpid));
     result:=true;
   end;

    RegExpr.expression:='psprocesses';
    if RegExpr.Exec(uri) then begin
         FileData.LoadFromStream(PROC.ExecStream('/bin/ps -eww -orss,vsz,comm',false));
         result:=true;
    end;

    RegExpr.expression:='getMainCF';
    if RegExpr.Exec(uri) then begin
         FileData.LoadFromFile('/etc/postfix/main.cf');
         result:=true;
    end;

    RegExpr.expression:='avestatus';
    if RegExpr.Exec(uri) then begin
         avestatus();
         result:=true;
    end;
// ########################################### KavMilter ####################################"



RegExpr.expression:='kavmilter_logs';
    if RegExpr.Exec(uri) then begin
         FileData.Add(kavmilter.GET_LASTLOGS());
         logs.logs('ParseUri:: kavmilter_logs:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;
RegExpr.expression:='kavmilter_start';
    if RegExpr.Exec(uri) then begin
         kavmilter.START();
         exit(true);
    end;

RegExpr.expression:='kavmilter_saveconf';
    if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-kavmilterd -save');
         exit(true);
    end;

RegExpr.expression:='kavmilter_stats';
    if RegExpr.Exec(uri) then begin
         fpsystem('/opt/kav/5.6/kavmilter/bin/kavmilter -r stats');
         logs.logs('ParseUri:: stats path=' + kavmilter.GET_VALUE('kavmilter.statistics','DataFile'));
         if FileExists(kavmilter.GET_VALUE('kavmilter.statistics','DataFile')) then begin
            FileData.LoadFromFile(kavmilter.GET_VALUE('kavmilter.statistics','DataFile'));
            logs.logs('ParseUri:: stats =' + IntTOStr(FileData.Count) + ' lines');
            exit(true);
         end;
    end;

RegExpr.expression:='kav4proxy_events';
    if RegExpr.Exec(uri) then begin
         fpsystem('/usr/bin/tail -n 200 /var/log/kaspersky/kav4proxy/av.stats >/opt/artica/logs/kav4proxy.events');
         FileData.LoadFromFile('/opt/artica/logs/kav4proxy.events');
         logs.logs('ParseUri:: events =' + IntTOStr(FileData.Count) + ' lines');
         exit(true);
    end;

RegExpr.expression:='^kav4proxy_stats';
    if RegExpr.Exec(uri) then begin
         cgiop.KAV4PORXY_GENERATE_STATS();
         sleep(500);
         if FileExists('/var/log/kaspersky/kav4proxy/counter.stats') then FileData.LoadFromFile('/var/log/kaspersky/kav4proxy/counter.stats');
         logs.logs('ParseUri:: kav4proxy_stats =' + IntTOStr(FileData.Count) + ' lines');
         exit(true);
    end;




RegExpr.expression:='^Kav4ProxyDaemonEvents';
    if RegExpr.Exec(uri) then begin
         FileData.Add(SYS.ExecPipe('/usr/bin/tail -n 300 /var/log/kaspersky/kav4proxy/kavicapserver.log'));
         exit(true);
    end;


RegExpr.expression:='^Kav4ProxyUpdateEvents';
    if RegExpr.Exec(uri) then begin
         FileData.Add(SYS.ExecPipe('/usr/bin/tail -n 300 /var/log/kaspersky/kav4proxy/keepup2date.log'));
         exit(true);
    end;





RegExpr.expression:='^RecoveryLdapFile:(.+)';
    if RegExpr.Exec(uri) then begin
         tmpstr:=logs.FILE_TEMP();
         cmd:='/usr/share/artica-postfix/bin/artica-backup --instant-ldap-recover '+ RegExpr.Match[1] +' --verbose >'+tmpstr+' 2>&1';
         logs.Debuglogs(cmd);
         fpsystem(cmd);
         FileData.Add(logs.ReadFromFile(tmpstr));
         logs.DeleteFile(tmpstr);
         exit(true);
    end;


RegExpr.expression:='^SpamassassinReload';
    if RegExpr.Exec(uri) then begin
       logs.OutputCmd('/usr/share/artica-postfix/bin/artica-install --spamassassin-reload');
       exit(true);
    end;

RegExpr.expression:='^SpamassassinSablackListCount';
    if RegExpr.Exec(uri) then begin
       tmpstr:=extractFilePath(spamass.SPAMASSASSIN_LOCAL_CF())+'sa-blacklist.work';
       if not FileExists(tmpstr) then begin
          FileData.Add('0 {servers}');
          exit(true);
       end;
       cmd:='/bin/cat '+tmpstr+'|/usr/bin/wc -l';
       tmpstr:=logs.FILE_TEMP();
       cmd:=cmd+' >'+tmpstr+' 2>&1';
       fpsystem(cmd);
       FileData.Add(logs.ReadFromFile(tmpstr)+' {servers} ' + sys.GET_INFO('SpamassassinBlackListeTag'));
       logs.DeleteFile(tmpstr);
       exit(true);
    end;

RegExpr.Expression:='PostfixSMTPDCountProcesses';
    if RegExpr.Exec(uri) then begin
        FileData.Add('process_smtpd;'+IntToStr(sys.PROCESS_NUMBER('/usr/libexec/postfix/smtpd')));
        FileData.Add('process_pickup;'+IntToStr(sys.PROCESS_NUMBER('/usr/libexec/postfix/pickup')));
        FileData.Add('process_cleanup;'+IntToStr(sys.PROCESS_NUMBER('/usr/libexec/postfix/cleanup')));
        FileData.Add('process_amavisd;'+IntToStr(sys.PROCESS_NUMBER('/usr/local/sbin/amavisd')));
        FileData.Add('process_amavisd-milter;'+IntToStr(sys.PROCESS_NUMBER('/usr/local/sbin/amavisd-milter')));
        FileData.Add('process_trivial-rewrite;'+IntToStr(sys.PROCESS_NUMBER('/usr/libexec/postfix/trivial-rewrite')));
        FileData.Add('process_spamd;'+IntToStr(sys.PROCESS_NUMBER('/usr/sbin/spamd')));
        exit(true);
    end;

RegExpr.expression:='dstattopressourcescpu';
    if RegExpr.Exec(uri) then begin
           SYS.DirFiles('/usr/share/artica-postfix/ressources/logs','dstat.topcpu.*.png');
           FileData.Add(SYS.DirListFiles.Text);
           exit(true);
        end;
RegExpr.expression:='dstattopressourcesmem';
    if RegExpr.Exec(uri) then begin
           SYS.DirFiles('/usr/share/artica-postfix/ressources/logs','dstat.topmem.*.png');
           FileData.Add(SYS.DirListFiles.Text);
           exit(true);
        end;

RegExpr.expression:='kav4proxy_license';
    if RegExpr.Exec(uri) then begin

         FileData.LoadFromStream(GLOBAL_INI.ExecStream('/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager -s',false));
         exit(true);
    end;


RegExpr.expression:='kav4sambaevents';
    if RegExpr.Exec(uri) then begin
         FileData.LoadFromStream(GLOBAL_INI.ExecStream('tail -n 100 /var/log/kaspersky/kav4samba/kavsamba.log',false));
         exit(true);
    end;

RegExpr.expression:='kav4sambasave';
    if RegExpr.Exec(uri) then begin
         logs.logs(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -kav4samba');
         logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -kav4samba');
         exit(true);
    end;

RegExpr.expression:='^kav4sambalicense';
    if RegExpr.Exec(uri) then begin
         path:=logs.FILE_TEMP();
         fpsystem('/opt/kaspersky/kav4samba/bin/kav4samba-licensemanager -s >'+path+' 2>&1');
         if fileExists(path) then FileData.LoadFromFile(path);
         logs.DeleteFile(path);
         exit(true);
    end;

RegExpr.expression:='^kav4sambaPushLicense:(.+)';
    if RegExpr.Exec(uri) then begin
         tmpstr:=logs.FILE_TEMP();
         path:=RegExpr.Match[1];
         logs.Debuglogs(uri);
         cmd:='/opt/kaspersky/kav4samba/bin/kav4samba-licensemanager -a '+path+' >'+tmpstr+' 2>&1';
         logs.Debuglogs(cmd);
         fpsystem(cmd);
         if fileExists(tmpstr) then FileData.LoadFromFile(tmpstr);
         logs.DeleteFile(tmpstr);
         logs.DeleteFile(path);
         GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart kav4samba >/dev/null 2>&1');
         exit(true);
    end;




RegExpr.expression:='kav4proxy_licencemanager:(.+)';
    if RegExpr.Exec(uri) then begin
        fpsystem('/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager -a  ' +  RegExpr.Match[1] + ' >/opt/artica/logs/KAV4PROXY.licence.uploaded 2>&1');
        logs.logs('ParseUri:: /opt/kav/5.6/kavmilter/bin/licensemanager -a  ' +  RegExpr.Match[1] + ' >/opt/artica/logs/KAV4PROXY.licence.uploaded 2>&1');
        FileData.LoadFromFile('/opt/artica/logs/KAV4PROXY.licence.uploaded');
        logs.logs('ParseUri:: ' + trim(FileData.Text));
        fpsystem('/bin/rm ' +  RegExpr.Match[1]);
        RegExpr.Free;
        result:=true;
        exit;
    end;

RegExpr.expression:='kav4proxy_saveconf';
    if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -kav4proxy');
         exit(true);
    end;

RegExpr.expression:='kav4proxy_service:(.+)';
    if RegExpr.Exec(uri) then begin
        if RegExpr.Match[1]='start' then kav4proxy.KAV4PROXY_START();
        if RegExpr.Match[1]='stop' then kav4proxy.KAV4PROXY_STOP();
        logs.logs('ParseUri:: kav4proxy service action:'+ RegExpr.Match[1]);
        exit(true);
    end;


RegExpr.expression:='kavmilter_licencemanager:(.+)';
     if RegExpr.Exec(uri) then begin
        if FileExists('/opt/artica/license.expired.conf') then fpsystem('/bin/rm /opt/artica/license.expired.conf');
        fpsystem('/opt/kav/5.6/kavmilter/bin/licensemanager -a  ' +  RegExpr.Match[1] + ' >/opt/artica/logs/kavmilterd.licence.uploaded 2>&1');
        logs.logs('ParseUri:: /opt/kav/5.6/kavmilter/bin/licensemanager -a  ' +  RegExpr.Match[1] + ' >/opt/artica/logs/kavmilterd.licence.uploaded 2>&1');
        FileData.LoadFromFile('/opt/artica/logs/kavmilterd.licence.uploaded');
        logs.logs('ParseUri:: ' + trim(FileData.Text));
        fpsystem('/bin/rm ' +  RegExpr.Match[1]);
        RegExpr.Free;
        result:=true;
        exit;
    end;
 // ########################################### pureftpd ####################################"

    RegExpr.expression:='pureftpd_saveconf:(.+)';
     if RegExpr.Exec(uri) then begin
        fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -ftp-users ' + RegExpr.Match[1]);
        logs.logs('ParseUri:: pure-ftpd save configuration from root ('+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -ftp-users ' + RegExpr.Match[1]+')' );
        exit(true);
    end;






RegExpr.expression:='pureftpd_logs';
  if RegExpr.Exec(uri) then begin
    tmpstr:=logs.FILE_TEMP();
    maillog:=SYS.LOCATE_SYSLOG_PATH();

    if not FileExists(maillog) then begin
       FileData.Add('Unable to locate syslog file...');
       exit(true);
    end;

    fpsystem('/usr/bin/tail -n 1000 '+maillog+'|/bin/grep pure-ftpd >'+tmpstr);
    FileData.Add(logs.ReadFromFile(tmpstr));
    pureftpdClass:=tpureftpd.Create;
    FileData.Add('Running PID number ' + pureftpdClass.PURE_FTPD_PID() +' - memory:'+ IntTostr(SYS.PROCESS_MEMORY(pureftpdClass.PURE_FTPD_PID()))+' Ko') ;
    pureftpdClass.free;
    exit(true);
  end;

    RegExpr.expression:='dansguardian_status';
     if RegExpr.Exec(uri) then begin
        FileData.Add(dansguardian.DANSGUARDIAN_STATUS());
        logs.logs('ParseUri:: dansguardian_status status');
        exit(true);
    end;





    RegExpr.expression:='pureftd_status';
     if RegExpr.Exec(uri) then begin
       FileData.Add(cgiop.SQUID_STATUS());
        logs.logs('ParseUri:: pureftpd_status status');
        exit(true);
    end;

    RegExpr.expression:='iptables_status';
     if RegExpr.Exec(uri) then begin
       FileData.Add(GLOBAL_INI.IPTABLES_STATUS());
        logs.logs('ParseUri:: iptables_status status');
        exit(true);
    end;

    RegExpr.expression:='iptables_cururles';
     if RegExpr.Exec(uri) then begin
        FileData.Add(GLOBAL_INI.IPTABLES_CURRENT_RULES());
        logs.logs('ParseUri:: iptables_cururles status ' + IntToStr(length(FileData.Text)) + ' lenght');
        exit(true);
    end;

    RegExpr.expression:='iptables_events';
     if RegExpr.Exec(uri) then begin
        FileData.Add(GLOBAL_INI.IPTABLES_EVENTS());
        logs.logs('ParseUri:: iptables_events status ' + IntToStr(length(FileData.Text)) + ' lenght');
        exit(true);
    end;



    RegExpr.expression:='refresh_installation';
    if RegExpr.Exec(uri) then begin
    fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-update -refresh-index');
    exit(true);
    end;


    RegExpr.expression:='daemons_status';
     if RegExpr.Exec(uri) then begin
        if FileExists(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/global.status.ini') then begin
           FileData.Add(logs.ReadFromFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/global.status.ini'));
           logs.Debuglogs('ParseUri:: daemons_status end ('+INtTOstr(length(FileData.Text)) + ' bytes)');
        end;

        SYS.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install --status >'+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/global.status.ini 2>&1');

    end;

// CYRUS CYRUS CYRUS ===========================================================

    RegExpr.expression:='cyrus_imap_status';
     if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.CYRUS_STATUS());
        logs.logs('ParseUri:: cyrus_imap_status status');
        exit(true);
    end;



    RegExpr.expression:='cyrquota';
     if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.cyrquota());
        logs.logs('ParseUri:: cyrquota status');
        exit(true);
    end;

    RegExpr.expression:='^FreshClamSave';
    if RegExpr.Exec(uri) then begin
        GLOBAL_INI.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart freshclam');
        exit(true);
    end;



    RegExpr.expression:='MbxStat:(.+)';
     if RegExpr.Exec(uri) then begin

        if(ccyrus.MAILBOX_EXISTS(RegExpr.Match[1])) then begin
           logs.Debuglogs('ParseUri:: status of ' +RegExpr.Match[1]+' ->TRUE');
           FileData.Add('TRUE');
        end else begin
           logs.Debuglogs('ParseUri:: status of ' +RegExpr.Match[1]+' ->FALSE');
           FileData.Add('FALSE');
        end;
      exit(true);
    end;





 RegExpr.expression:='AutoMountReload';
    if RegExpr.Exec(uri) then begin
        SYS.THREAD_COMMAND_SET('/etc/init.d/autofs reload');
        SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.AutoFS.php');
        exit(true);
    end;


RegExpr.expression:='mailboxc:(.+)';
    if RegExpr.Exec(uri) then begin
        FileData.Add(ccyrus.CREATE_USER(RegExpr.Match[1]));
        exit(true);
    end;

RegExpr.expression:='MailboxExists:(.+)';
    if RegExpr.Exec(uri) then begin
      FileData.Add(ccyrus.MAILBOX_EXISTS_CGI(RegExpr.Match[1]));
    end;

RegExpr.expression:='MailboxInfos:(.+)';
    if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.UserInfos(RegExpr.Match[1]));
       exit(true);
    end;

RegExpr.expression:='MailboxQuota:(.+)';
    if RegExpr.Exec(uri) then begin
       FileData.Add(ccyrus.MAILBOX_QUOTA(RegExpr.Match[1]));
    end;

RegExpr.expression:='cyrusconf';
    if RegExpr.Exec(uri) then begin
        cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -cyrus';
        logs.logs('ParseUri::  -> "' + cmd+'"');
        SYS.THREAD_COMMAND_SET(cmd);
        result:=true;
        exit;
    end;

RegExpr.expression:='autoinstall:(.+)';
    if RegExpr.Exec(uri) then begin
        logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-update -startinstall '+RegExpr.Match[1]);
        result:=true;
        exit;
    end;
RegExpr.expression:='delete_installation_logs:(.+)';
    if RegExpr.Exec(uri) then begin
        logs.DeleteFile(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/install/'+RegExpr.Match[1]+'.install');
        logs.DeleteFile(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/install/'+RegExpr.Match[1]+'.log');
        logs.DeleteFile(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/logs/'+RegExpr.Match[1]+'/install.log');
        result:=true;
        exit;
    end;

//Force to Change SSL certificate.





// CYRUS CYRUS CYRUS ===========================================================

RegExpr.expression:='obmapachestatus';
     if RegExpr.Exec(uri) then begin
       FileData.Add(obm.STATUS());
        logs.logs('ParseUri:: OBM_APACHE_STATUS status');
        exit(true);
    end;


    RegExpr.expression:='ntpdstatus';
     if RegExpr.Exec(uri) then begin
       FileData.Add(ntpd.NTPD_STATUS());
        logs.logs('ParseUri:: NTPD_STATUS status');
        exit(true);
    end;







    RegExpr.expression:='cyrus_events';
    if RegExpr.Exec(uri) then begin
         cgiop.CYRUS_MAILLOG();
         FileData.AddStrings(cgiop.FA);
         logs.logs('ParseUri:: cyrus_events:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;

RegExpr.expression:='obmapacheevents';
    if RegExpr.Exec(uri) then begin
         cgiop.OBM_APACHE_MAILLOG();
         FileData.AddStrings(cgiop.FA);
         logs.logs('ParseUri:: obmapacheevents:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;


RegExpr.expression:='BACKUP_ARTICA_STATUS';
    if RegExpr.Exec(uri) then begin
        tmpstr:=GLOBAL_INI.BACKUP_ARTICA_STATUS();
        FileData.Add(tmpstr);
        logs.logs('ParseUri:: BACKUP_ARTICA_STATUS:: ' +tmpstr + ' ' + IntTOstr(FileData.Count) + ' lines');
        exit(true);
    end;

RegExpr.expression:='darstatus';
    if RegExpr.Exec(uri) then begin
        rdiffbackup:=Trdiffbackup.Create;
        FileData.Add(rdiffbackup.DAR_STATUS());
        rdiffbackup.Free;
        logs.logs('ParseUri:: darstatus:: ' +tmpstr + ' ' + IntTOstr(FileData.Count) + ' lines');
        exit(true);
    end;

RegExpr.expression:='^DarSaveCron';
    if RegExpr.Exec(uri) then begin
        logs.DeleteFile('/etc/cron.d/artica.cron.increment');
        TmpINI:=Tinifile.Create('/etc/artica-postfix/settings/Daemons/DarBackupConfig');
        tmpstr:=TmpINi.ReadString('GLOBAL','cron','');
        if length(tmpstr)=0 then begin
            logs.Debuglogs('ParseUri:: DarSaveCron:: no cron lines found.');
            logs.DeleteFile('/etc/cron.d/artica-cron-increment');
            TmpINi.free;
            exit(true);
        end;
        FileData.Clear;
        FileData.Add(tmpstr);
        logs.WriteToFile(FileData.Text,'/etc/cron.d/artica-cron-increment');
        logs.logs('ParseUri:: DarSaveCron:: ' +tmpstr + ' >/etc/cron.d/artica-cron-increment');
        logs.OutputCmd('/bin/chmod 0640 /etc/cron.d/artica-cron-increment');
        TmpINi.free;
        exit(true);
    end;

RegExpr.expression:='^darmount';
    if RegExpr.Exec(uri) then begin
        tmpstr:=logs.FILE_TEMP();
        logs.Debuglogs(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --mount-dar --verbose >'+tmpstr);
        fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --mount-dar --verbose >'+tmpstr);
        if not FileExists(tmpstr) then logs.Debuglogs('ParseUri:: darmount:: shell error on '+tmpstr);
        logs.Debuglogs(logs.ReadFromFile(tmpstr));
        FileData.Add(logs.ReadFromFile(tmpstr));
        logs.DeleteFile(tmpstr);
        logs.Debuglogs('ParseUri:: darmount:: ' +tmpstr + ' ' + IntTOstr(FileData.Count) + ' lines');
        exit(true);
    end;
RegExpr.expression:='^dar_manager_status';
if RegExpr.Exec(uri) then begin
      tmpstr:=SYS.PIDOF('/usr/bin/dar');
      logs.Debuglogs('pidof /usr/bin/dar=' +tmpstr);
      FileData.Add('[DAR]');
      FileData.Add('pid='+tmpstr);
      if SYS.PROCESS_EXIST(tmpstr) then FileData.Add('mem='+IntToStr(SYS.PROCESS_MEMORY(tmpstr)));
      FileData.Add('[DAR_MANAGER]');
      tmpstr:=SYS.PIDOF('/usr/bin/dar_manager');
      FileData.Add('pid='+tmpstr);
      if SYS.PROCESS_EXIST(tmpstr) then FileData.Add('mem='+IntToStr(SYS.PROCESS_MEMORY(tmpstr)));
      FileData.Add('[ARTICA_BACKUP]');
      tmpstr:=SYS.PIDOF('artica-backup');
      FileData.Add('pid='+tmpstr);
      if SYS.PROCESS_EXIST(tmpstr) then FileData.Add('mem='+IntToStr(SYS.PROCESS_MEMORY(tmpstr)));
      exit(true);
end;
RegExpr.expression:='^darRebuild';
if RegExpr.Exec(uri) then begin
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --rebuild-collection &');
       logs.Debuglogs('darRebuild:: lauched rebuild collection');
       exit(true);
end;
RegExpr.expression:='^DarListCollection';
if RegExpr.Exec(uri) then begin
       if FileExists('/usr/share/artica-postfix/ressources/logs/collections.dmd') then exit;
       tmpstr:=logs.FILE_TEMP();

       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --list-collection >'+tmpstr+' 2>&1');
       logs.Debuglogs(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --list-collection >'+tmpstr+' 2>&1');
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(tmpstr);
       exit(true);
end;

RegExpr.expression:='^DarRefreshCache';
if RegExpr.Exec(uri) then begin
   logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --dar-cache');
   exit(true);
end;

RegExpr.expression:='^DarSearchFile:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
       tmpstr:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/dar_collection/'+ RegExpr.Match[2];
       TempDatasLogs:=logs.FILE_TEMP();
       logs.Debuglogs('darRebuild:: lauched query collection on '+RegExpr.Match[1] + ' for collection ' + RegExpr.Match[2]);
       cmd:='grep -H -m 50 -i -E --regexp="name=\"'+RegExpr.Match[1] +'" '+tmpstr+'/*.xml >'+TempDatasLogs+' 2>&1';
       logs.Debuglogs(cmd);
       fpsystem(cmd);
       try
          FileData.Add(logs.ReadFromFile(TempDatasLogs));
       except
             logs.Debuglogs('FATAL ERROR');
             exit;
       end;
       logs.Debuglogs('Result: ' +IntToStr(length(FileData.Text)) + ' bytes');
       logs.DeleteFile(TempDatasLogs);
       exit(true);
end;
RegExpr.expression:='^DarBrowser:(.+)';
if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --darxml '+ RegExpr.Match[1]+' >'+tmpstr+' 2>&1');
       logs.Debuglogs('DarBrowser:: lauched query collection on '+RegExpr.Match[1]);
       FileData.Add(logs.ReadFromFile(tmpstr));
       logs.DeleteFile(tmpstr);
       exit(true);
end;


RegExpr.expression:='^DarRestorePath:(.+?);(.+);(.+)';
if RegExpr.Exec(uri) then begin
      artica_cron:=tcron.Create(GLOBAL_INI.SYS);
      artica_cron.START();
      artica_cron.Free;
      tmpstr:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --restore-full "'+RegExpr.Match[1]+'" '+RegExpr.Match[2]+ ' ' + RegExpr.Match[3];
      logs.Debuglogs('DarBrowser:: Background ->"'+tmpstr+'"');
      GLOBAL_INI.THREAD_COMMAND_SET(tmpstr);
      exit(true);
end;

RegExpr.expression:='^DarRestoreFile:(.+?);(.+);(.+?);(.+)';
if RegExpr.Exec(uri) then begin
      artica_cron:=tcron.Create(GLOBAL_INI.SYS);
      artica_cron.START();
      artica_cron.Free;
      logs.Debuglogs('DarRestoreFile: original file in database='+RegExpr.Match[1]);
      logs.Debuglogs('DarRestoreFile: Database collection='+RegExpr.Match[2]);
      logs.Debuglogs('DarRestoreFile: collection folder='+RegExpr.Match[3]);
      logs.Debuglogs('DarRestoreFile: Local directory='+RegExpr.Match[4]);
      tmpstr:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --restore-file "'+RegExpr.Match[1]+'" '+RegExpr.Match[2]+ ' ' + RegExpr.Match[3]+ ' "'+RegExpr.Match[4]+'" &';
      logs.Debuglogs('DarBrowser:: Background ->"'+tmpstr+'"');
      GLOBAL_INI.THREAD_COMMAND_SET(tmpstr);
      exit(true);
end;

RegExpr.expression:='^DarDeletePersoScheduleMin';
if RegExpr.Exec(uri) then begin
    SYS.DirFiles('/etc/cron.d','artica*-dar');
    logs.Debuglogs('DarDeletePersoScheduleMin: ' + IntToStr(SYS.DirListFiles.Count)+' files detected in /etc/cron.d');
    for i:=0 to SYS.DirListFiles.Count-1 do begin
       logs.DeleteFile('/etc/cron.d/'+SYS.DirListFiles.Strings[i]);
       logs.Debuglogs('DarDeletePersoScheduleMin: '+'/etc/cron.d/'+SYS.DirListFiles.Strings[i]+' deleted..');
    end;
    if FileExists('/etc/init.d/cron') then fpsystem('/etc/init.d/cron reload &');
    exit(true);

end;

RegExpr.expression:='^ArticaBackupLogsList';
if RegExpr.Exec(uri) then begin
       SYS.DirFiles('/var/log/artica-postfix','artica-backup*.debug');
       logs.Debuglogs('ArticaBackupLogsList: ' + IntToStr(SYS.DirListFiles.Count)+' files detected in /var/log/artica-postfix');
       FileData.AddStrings(SYS.DirListFiles);
       exit(true);
end;
RegExpr.expression:='^ArticaBackupLastLogsList:(.+?);([0-9]+)';
if RegExpr.Exec(uri) then begin
       if FileExists('/var/log/artica-postfix/'+RegExpr.Match[1]) then begin
          tmpstr:=logs.FILE_TEMP();
          fpsystem('/usr/bin/tail -n ' +RegExpr.Match[2]+' /var/log/artica-postfix/'+RegExpr.Match[1] + ' >' + tmpstr+' 2>&1');
          FileData.Add(logs.ReadFromFile(tmpstr));
          logs.DeleteFile(tmpstr);
          exit(true);
       end;
end;





RegExpr.expression:='^DarPersoScheduleMin:(.+?);(.+?);(.+)';
if RegExpr.Exec(uri) then begin
      FileData.Clear;
      tmpstr:=RegExpr.Match[2]+' * * * * root '+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --dar-backup-single-path "'+ RegExpr.Match[3]+'" >/dev/null';
      FileData.Add(tmpstr);
      logs.Debuglogs('Save cron schedule for '+ RegExpr.Match[1]);
      logs.Debuglogs('Save cron schedule line '+tmpstr);
      logs.WriteToFile(FileData.Text,'/etc/cron.d/artica-'+RegExpr.Match[1]+'-dar');
      logs.OutputCmd('/bin/chmod 0640 /etc/cron.d/artica-'+RegExpr.Match[1]+'-dar');
      if FileExists('/etc/init.d/cron') then fpsystem('/etc/init.d/cron reload &');
      exit(true);
end;



RegExpr.expression:='DarCountFiles:(.+)';
    if RegExpr.Exec(uri) then begin
        rdiffbackup:=Trdiffbackup.Create;
        FileData.Add(rdiffbackup.DAR_DATABASE_COUNT_FILES(RegExpr.Match[1]));
        rdiffbackup.Free;
        logs.logs('ParseUri:: DarCountFiles:: ' +tmpstr + ' ' + IntTOstr(FileData.Count) + ' lines');
        exit(true);
    end;

RegExpr.expression:='DarDatabaseCountFiles:(.+?);(.+)';
    if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri:: DarDatabaseCountFiles::'+RegExpr.Match[1]+','+RegExpr.Match[2]);
        rdiffbackup:=Trdiffbackup.Create;
        FileData.Add(rdiffbackup.DAR_DATABASE_COUNT_FILES_SINGLE(RegExpr.Match[1],RegExpr.Match[2]));
        rdiffbackup.Free;
        logs.logs('ParseUri:: DarDatabaseCountFiles:: ' +tmpstr + ' ' + IntTOstr(FileData.Count) + ' lines');
        exit(true);
    end;


RegExpr.expression:='DarSearchFiles:(.+?);(.+)';
    if RegExpr.Exec(uri) then begin
        logs.Debuglogs('ParseUri:: DarSearchFiles:: '+uri);
        rdiffbackup:=Trdiffbackup.Create;
        FileData.Add(rdiffbackup.DAR_DATABASE_SEARCH_FILES(RegExpr.Match[2],RegExpr.Match[1]));
        FileData.SaveToFile(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/logs/dar.query.inc');
        logs.OutputCmd('/bin/chmod 777 '+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/logs/dar.query.inc');
        rdiffbackup.Free;
        exit(true);
    end;

RegExpr.expression:='DarFileInfo:(.+?);(.+)';
    if RegExpr.Exec(uri) then begin
        logs.Debuglogs('ParseUri:: DarSearchFiles:: '+uri);
        rdiffbackup:=Trdiffbackup.Create;
        FileData.Add(rdiffbackup.DAR_DATABASE_FILE_INFO(RegExpr.Match[2],RegExpr.Match[1]));
        rdiffbackup.Free;
        exit(true);
    end;

RegExpr.expression:='DarRestoreFile:(.+?):(.+?):(.+)';
    if RegExpr.Exec(uri) then begin
        tmpstr:=logs.FILE_TEMP();
        cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --dar-restore-file "'+ RegExpr.Match[1]+'" '+RegExpr.Match[2]+' '+RegExpr.Match[3]+' '+tmpstr;
        fpsystem(cmd);
        FileData.Clear;
        logs.Debuglogs('ParseUri:: DarRestoreFile:: '+cmd);


        if FileExists(tmpstr) then begin
          logs.Debuglogs('ParseUri:: DarRestoreFile:: reading '+tmpstr);
          try
          FileData.LoadFromFile(tmpstr);
          except
          FileData.Add('Error while reading '+tmpstr);
          end;
        logs.DeleteFile(tmpstr);
        end else begin
            logs.Debuglogs('ParseUri:: DarRestoreFile:: error stat '+tmpstr);
        end;
        logs.logs('ParseUri:: DarRestoreFile:: ' + IntTOstr(FileData.Count) + ' lines');

        exit(true);
    end;

RegExpr.expression:='DarRestoreFull:(.+?):(.+?):(.+)';
    if RegExpr.Exec(uri) then begin
        tmpstr:=logs.FILE_TEMP();
        cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup --dar-restore-full '+ RegExpr.Match[1]+' '+RegExpr.Match[2]+' "'+RegExpr.Match[3]+'" '+ tmpstr;
        logs.Debuglogs('ParseUri:: DarRestoreFull:: '+cmd);
        fpsystem(cmd);
        logs.Debuglogs('ParseUri:: Executed...');


        if FileExists(tmpstr) then begin
          logs.Debuglogs('ParseUri:: DarRestoreFull:: reading '+tmpstr);
          logs.Debuglogs('ParseUri:: '+logs.ReadFromFile(tmpstr));
          FileData.Add(logs.ReadFromFile(tmpstr));
          logs.Debuglogs('ParseUri:: DarRestoreFull:: Deleting '+tmpstr);
          logs.DeleteFile(tmpstr);

        end else begin
            logs.Debuglogs('ParseUri:: DarRestoreFull:: error stat '+tmpstr);
        end;
         logs.Debuglogs('ParseUri:: DarRestoreFull:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^DarFindFiles:(.+)';
   if RegExpr.Exec(uri) then begin
      cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dar.find.php '+ RegExpr.Match[1];
      logs.Debuglogs(cmd);
      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dar.find.php '+ RegExpr.Match[1]);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^DarRestorefile:(.+?);(.+?);([0-9]+)';
   if RegExpr.Exec(uri) then begin
      cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dar.find.php --restore '+ RegExpr.Match[1]+' "'+RegExpr.Match[2]+'" "'+RegExpr.Match[3]+'"' ;
      logs.Debuglogs(cmd);
      SYS.THREAD_COMMAND_SET(cmd);
      TmpINI:=TiniFIle.Create('/usr/share/artica-postfix/ressources/logs/exec.dar.find.restore.ini');
      TmpINI.WriteString('STATUS','progress','10');
      TmpINI.WriteString('STATUS','text','{scheduled}');
      TmpINI.UpdateFile;
      TmpINI.Free;
      logs.OutputCmd('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/exec.dar.find.restore.ini');
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^DarRestoreDirectory:(.+?);(.+?);([0-9]+)';
   if RegExpr.Exec(uri) then begin
      cmd:=SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dar.find.php --restore '+ RegExpr.Match[1]+' "'+RegExpr.Match[2]+'" "'+RegExpr.Match[3]+'" --directory' ;
      logs.Debuglogs(cmd);
      SYS.THREAD_COMMAND_SET(cmd);
      TmpINI:=TiniFIle.Create('/usr/share/artica-postfix/ressources/logs/exec.dar.find.restore.ini');
      TmpINI.WriteString('STATUS','progress','10');
      TmpINI.WriteString('STATUS','text','{scheduled}');
      TmpINI.UpdateFile;
      TmpINI.Free;
      logs.OutputCmd('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/exec.dar.find.restore.ini');
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^DarPopulate:([0-9]+)';
   if RegExpr.Exec(uri) then begin
      cmd:='/usr/share/artica-postfix/bin/artica-backup --dar-populate ' + RegExpr.Match[1];
      logs.Debuglogs(cmd);
      SYS.THREAD_COMMAND_SET(cmd);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^SendMailingToOrgs';
   if RegExpr.Exec(uri) then begin
      cmd:=SYS.LOCATE_PHP5_BIN()+ ' /usr/share/artica-postfix/exec.emailing-organizations.php';
      logs.Debuglogs(cmd);
      SYS.THREAD_COMMAND_SET(cmd);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^SendMailingSingleOrgs:(.+)';
   if RegExpr.Exec(uri) then begin
      cmd:=SYS.LOCATE_PHP5_BIN()+ ' /usr/share/artica-postfix/exec.emailing-organizations.php '+RegExpr.Match[1];
      logs.Debuglogs(cmd);
      SYS.THREAD_COMMAND_SET(cmd);
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------
RegExpr.expression:='^PushNTconfig:(.+)';
   if RegExpr.Exec(uri) then begin
      logs.Debuglogs('Push NT config to samba server..');
      logs.OutputCmd('/bin/cp -f '+RegExpr.Match[1]+' /home/netlogon/NTconfig.pol');
      logs.OutputCmd('/bin/chown root:root /home/netlogon/NTconfig.pol');
      exit(true);
   end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------



RegExpr.expression:='^dar-run-backup';
if RegExpr.Exec(uri) then begin
    cmd:='/usr/share/artica-postfix/bin/artica-backup --incremental';
    logs.Debuglogs(cmd);
    SYS.THREAD_COMMAND_SET(cmd);
    exit(true);
end;
//----------------------------------------------------------------------------------------------------------------------------------------------------------------


RegExpr.expression:='ismounted:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
   SYS:=Tsystem.Create();
   logs.Debuglogs('ParseUri:: DISK_USB_IS_MOUNTED('+RegExpr.Match[1]+','+RegExpr.Match[2]+')');
   if SYS.DISK_USB_IS_MOUNTED(RegExpr.Match[1],RegExpr.Match[2]) then begin
   FileData.Add('TRUE');
   exit(true);
   end;
   FileData.Add('FALSE');
   exit(true);
end;


RegExpr.expression:='ChangeUSBLabel:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
   SYS:=Tsystem.Create();
   logs.Debuglogs('ParseUri:: usb_change_label('+RegExpr.Match[1]+','+RegExpr.Match[2]+')');
   SYS.usb_change_label(RegExpr.Match[1],RegExpr.Match[2]);
   exit(true);
end;


RegExpr.expression:='BuildUniquePartition:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
   SYS:=Tsystem.Create();
   logs.Debuglogs('ParseUri:: disk_build_unique_partition('+RegExpr.Match[1]+','+RegExpr.Match[2]+')');
   FileData.Add(SYS.disk_build_unique_partition(RegExpr.Match[1],RegExpr.Match[2]));
   exit(true);
end;






RegExpr.expression:='^FormatDevice:(.+?);(.+)';
if RegExpr.Exec(uri) then begin
     logs.Debuglogs('ParseUri:: FormatDevice('+RegExpr.Match[1]+','+RegExpr.Match[2]+')');

     if length(trim(RegExpr.Match[1]))<5 then begin
          FileData.Add(RegExpr.Match[1] + ' ERROR !');
          exit(true);
     end;

     if not FileExists('/sbin/mkfs.' +RegExpr.Match[2]) then begin
       FileData.Add('Unable to stat /sbin/mkfs.' +RegExpr.Match[2] );
       exit(true);
     end;
       cmd:='/sbin/mkfs.'+RegExpr.Match[2]+' '+RegExpr.Match[1];

     if RegExpr.Match[2]='vfat' then begin
        cmd:='/sbin/mkfs.vfat -F 32 '+RegExpr.Match[1];
     end;

     filePath:=GLOBAL_INI.usb_mount_point(RegExpr.Match[1]);
     if length(filePath)>0 then begin
        logs.OutputCmd('/bin/umount -f ' + filePath);
     end;


     tmpstr:=logs.FILE_TEMP();
     logs.Debuglogs(cmd);
     fpsystem(cmd + ' >>'+ tmpstr+' 2>&1');
     logs.Debuglogs(logs.ReadFromFile(tmpstr));
     FileData.Add(logs.ReadFromFile(tmpstr));
     logs.DeleteFile(tmpstr);
     exit(true);

end;


RegExpr.expression:='BackupShareConnect:([0-9]+)';
if RegExpr.Exec(uri) then begin
   cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-backup-share --mount '+ RegExpr.Match[1];
   logs.Debuglogs('ParseUri:: BackupShareConnect:: '+cmd);
   fpsystem(cmd);
   exit(true);
end;

RegExpr.expression:='BackupShareDisconnect:(.+)';
if RegExpr.Exec(uri) then begin
   cmd:='umount -f /opt/artica/'+RegExpr.Match[1];
   logs.Debuglogs('ParseUri:: BackupShareDisconnect:: '+cmd);
   fpsystem(cmd);
   exit(true);
end;

RegExpr.expression:='cyrus_imapconf';
    if RegExpr.Exec(uri) then begin
         if FileExists('/etc/imapd.conf') then begin
         FileData.LoadFromFile('/etc/imapd.conf');
         end;
         logs.logs('ParseUri:: cyrus_imapconf:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;

 // ########################################### sqlgrey ####################################"

    RegExpr.expression:='sqlgrey_saveconf:(.+)';
     if RegExpr.Exec(uri) then begin
        fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -sqlgrey ' + RegExpr.Match[1]);
        logs.logs('ParseUri:: sqlgrey save configuration from root ('+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -sqlgrey ' + RegExpr.Match[1]+')' );
        exit(true);
    end;


    RegExpr.expression:='ArticaBackupSaveConf';
     if RegExpr.Exec(uri) then begin
        fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -backups restart');
        logs.logs('ParseUri:: artica backup save configuration from root ('+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -backups restart)' );
        exit(true);
    end;

   RegExpr.expression:='PerformBackupArtica';
     if RegExpr.Exec(uri) then begin
        GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install --backup');
        logs.logs('ParseUri:: artica backup execute backup...' );
        exit(true);
    end;


    RegExpr.expression:='ArticaBackupLogs';
  if RegExpr.Exec(uri) then begin
        if FileExists('/var/log/artica-postfix/artica-backup.debug') then begin
           FileData.LoadFromFile('/var/log/artica-postfix/artica-backup.debug');
           logs.logs('ParseUri:: get /var/log/artica-postfix/artica-backup.debug datas' );
        end;
        exit(true);
    end;



RegExpr.expression:='synchronizeModules';
     if RegExpr.Exec(uri) then begin
        GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -syncmodules');
        logs.Debuglogs('ParseUri::  execute synchronize module in background...' );
        exit(true);
    end;


 RegExpr.expression:='myhostname';
 if RegExpr.Exec(uri) then begin
     FileData.add(sys.HOSTNAME_g());
     exit(true);
 end;






    RegExpr.expression:='sqlgrey_logs';
    if RegExpr.Exec(uri) then begin
         cgiop.SQLGREY_MAILLOG();
         FileData.AddStrings(cgiop.FA);
         logs.logs('ParseUri:: sqlgrey_logs:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;


    RegExpr.expression:='amavis_logs';
    if RegExpr.Exec(uri) then begin
         cgiop.AMAVIS_MAILLOG();
         FileData.AddStrings(cgiop.FA);
         logs.logs('ParseUri:: amavis_logs:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;

    RegExpr.expression:='freshclam_logs';
    if RegExpr.Exec(uri) then begin
         cgiop.FRESHCLAM_MAILLOG();
         FileData.AddStrings(cgiop.FA);
         logs.logs('ParseUri:: freshclam_logs:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;

    RegExpr.expression:='amavis_settings';
    if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -amavis');
         logs.logs('ParseUri:: amavis save configuration from root ('+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -amavis)' );
         exit(true);
    end;

    RegExpr.expression:='mailfromd_settings';
    if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -mailfromd');
         logs.logs('ParseUri:: mailfromd save configuration from root ('+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -mailfromd)' );
         exit(true);
    end;

    RegExpr.expression:='obm_settings';
    if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -obm-sys');
         logs.logs('ParseUri:: amavis save configuration from root ('+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -obm-sys)' );
         exit(true);
    end;

    RegExpr.expression:='iptables_settings';
    if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -iptables');
         logs.logs('ParseUri:: iptables save configuration from root ('+GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -iptables)' );
         exit(true);
    end;

 // ########################################### Squid & others ####################################"


RegExpr.expression:='^cicapevents';
if RegExpr.Exec(uri) then begin
     FileData.Add(SYS.ExecPipe('tail -n 300 /var/log/squid/c-icap_access.log'));
     logs.logs('ParseUri:: cicapevents: '+intToStr(FileData.Count)+' rows');
     exit(true);
end;



RegExpr.expression:='squid_daemon_failed';
     if RegExpr.Exec(uri) then begin
        if FileExists('/opt/artica/logs/squid.start.daemon') then FileData.LoadFromFile('/opt/artica/logs/squid.start.daemon');
        logs.logs('ParseUri:: squid squid_daemon_failed');
        exit(true);
    end;

RegExpr.expression:='^DansguardianDaemonEvents';
     if RegExpr.Exec(uri) then begin
        FileData.Add(SYS.ExecPipe('/usr/bin/tail -n 300 /var/log/dansguardian/access.log'));
        exit(true);
    end;

RegExpr.expression:='^dansguardianLoadDefaultRule:(.+)';
     if RegExpr.Exec(uri) then begin
        if FileExists('/etc/dansguardian/lists/'+RegExpr.Match[1]) then begin
           FileData.Add(logs.ReadFromFile('/etc/dansguardian/lists/'+RegExpr.Match[1]));
           exit(true);
        end else begin
            logs.Debuglogs('Unable to stat /etc/dansguardian/lists/'+RegExpr.Match[1]);
        end;
     end;

RegExpr.expression:='foldersize:(.+)';
     if RegExpr.Exec(uri) then begin
        FileData.Add(GLOBAL_INI.SYSTEM_GET_FOLDERSIZE(RegExpr.Match[1]));
        exit(true);
    end;



RegExpr.expression:='cache_squid_tail';
   if RegExpr.Exec(uri) then begin
      FileData.Add(cgiop.SQUID_START_LOGS());
      exit(true);
   end;

RegExpr.expression:='dansguardian_service:(.+)';
    if RegExpr.Exec(uri) then begin
        if RegExpr.Match[1]='start' then dansguardian.DANSGUARDIAN_START();
        if RegExpr.Match[1]='stop' then dansguardian.DANSGUARDIAN_STOP();
        logs.logs('ParseUri:: dansguardian service action:'+ RegExpr.Match[1]);
        exit(true);
    end;



// ########################################### Postfix Logs ####################################"



    RegExpr.expression:='dnsmasqlogs';
    if RegExpr.Exec(uri) then begin
         cgiop.DNSMASQ_LOGS();
         FileData.AddStrings(cgiop.FA);
         exit(true);
    end;

     RegExpr.expression:='PostfixErrorsLogs';
     if RegExpr.Exec(uri) then begin
          GLOBAL_INI.POSTFIX_LAST_ERRORS();
          FileData.AddStrings(GLOBAL_INI.ArrayList);
          logs.logs('ParseUri:: PostfixErrorsLogs:: ' + IntTOstr(FileData.Count) + ' lines');
          exit(true);
     end;

     RegExpr.expression:='mailmanevents';
    if RegExpr.Exec(uri) then begin
         if FileExists('/opt/artica/var/mailman/logs/smtp') then FileData.LoadFromFile('/opt/artica/var/mailman/logs/smtp');
         logs.logs('ParseUri:: mailmanevents:: ' + IntTOstr(FileData.Count) + ' lines');
         exit(true);
    end;



// ############################################################################################



    RegExpr.expression:='maillog:(.+)';
    if RegExpr.Exec(uri) then begin
         tail_postfix_logs_filter(RegExpr.Match[1]);
         result:=true;
         RegExpr.Free;
         exit;
    end;

 RegExpr.expression:='mailloghistory:(.+)';
    if RegExpr.Exec(uri) then begin
         cgiop.POSTFIX_MAILLOG_HISTORY(RegExpr.Match[1]);
         FileData.AddStrings(cgiop.FA);
         result:=true;
         RegExpr.Free;
         exit;
    end;

    RegExpr.expression:='TaskManager';
    if RegExpr.Exec(uri) then begin
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ps ' +GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/psps.inc');
//       GLOBAL_INI.SYSTEM_PROCESS_PS();
  //      FileData.AddStrings(GLOBAL_INI.ArrayList);
        result:=true;
        exit;
    end;
    RegExpr.expression:='KillProcessByid:([0-9]+)';
    if RegExpr.Exec(uri) then begin
        if Not FileExists('/bin/kill') then begin
           logs.logs('ParseUri::  -> unable to stat /bin/kill');
           result:=true;
           exit;
        end;
        logs.logs('ParseUri::  -> /bin/kill -9 ' + RegExpr.Match[1]);
        FileData.LoadFromStream(PROC.ExecStream('/bin/kill -9 ' + RegExpr.Match[1],false));
        result:=true;
        exit;
    end;

    RegExpr.expression:='artica_version';
    if RegExpr.Exec(uri) then begin
        FileData.Add(GLOBAL_INI.ARTICA_VERSION());
        result:=true;
        exit;
    end;



    RegExpr.expression:='cyrreconstruct';
    if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri::  -> cyrreconstruct ');
        if FileExists(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct') then logs.DeleteFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
        FileData.Add(logs.DateTimeNowSQL() + ' Starting send order to middleware');
        FileData.SaveToFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
        logs.OutputCmd('/bin/chmod 755 '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
        GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap --cyrreconstruct >>' + GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
        result:=true;
        exit;
    end;


    RegExpr.expression:='^ctlcyrusdb';
    if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri::  -> ctlcyrusdb ');
        if FileExists(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/ctlcyrusdb') then logs.DeleteFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/ctlcyrusdb');
        FileData.Add(logs.DateTimeNowSQL() + ' Starting send order to middleware');
        FileData.SaveToFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/ctlcyrusdb');
        logs.OutputCmd('/bin/chmod 755 '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/ctlcyrusdb');
        GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap --cyrrepair --verbose >>' + GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/ctlcyrusdb');
        result:=true;
        exit;
    end;


RegExpr.expression:='MILTER_GREYLIST_STATUS';
    if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri::  -> milter-greylist MILTER_GREYLIST_STATUS');
        FileData.Add(miltergreylist.STATUS());
        result:=true;
        exit;
    end;

RegExpr.expression:='MILTER_GREYLIST_EVENTS';
    if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri::  -> milter-greylist MILTERGREYLIST_MAILLOG');
        cgiop.MILTERGREYLIST_MAILLOG();
        FileData.AddStrings(cgiop.FA);
        result:=true;
        exit;
    end;

RegExpr.expression:='awstats_generate';
    if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri::  -> awstats_generate');
        fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install -awstats generate >/opt/artica/logs/exec.tmp 2>&1');
        if FileExists('/opt/artica/logs/exec.tmp') then begin
            FileData.LoadFromFile('/opt/artica/logs/exec.tmp');
            GLOBAL_INI.DeleteFile('/opt/artica/logs/exec.tmp');
        end;
        result:=true;
        exit;
    end;


//--------------------------------------- Network configurations ----------------------------------


    RegExpr.expression:='usb_umount:(.+)';
    if RegExpr.Exec(uri) then begin
        tmpstr:=logs.FILE_TEMP();
        logs.debuglogs(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/hmount -v -u ' + RegExpr.Match[1] + ' >' + tmpstr + ' 2>&1');
        fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/hmount -v -u ' + RegExpr.Match[1] + ' >' + tmpstr + ' 2>&1');
        fpsystem('umount -v ' + RegExpr.Match[1]+ ' >>' + tmpstr + ' 2>&1');
        if fileExists(tmpstr) then FileData.LoadFromFile(tmpstr);
        logs.DeleteFile(tmpstr);
        exit(true);
    end;

RegExpr.expression:='usb_mount:(.+?);(.+)';
    if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();
       filepath:='/media/disk-'+logs.MD5FromString(RegExpr.Match[1]);
       forceDirectories(filepath);
       if RegExpr.Match[2]='vfat' then options:='rw,nosuid,nodev,uhelper=hal,shortname=mixed,uid=1001,gid=100,umask=077,iocharset=utf8';
          cmd:='/bin/mount -t ' + RegExpr.Match[2] + ' ' + RegExpr.Match[1] +' ' + filepath + ' --verbose >'+tmpstr + ' 2>&1';
          FileData.Add(cmd);
          logs.debuglogs(cmd);
          fpsystem(cmd);
          if fileExists(tmpstr) then FileData.Add(logs.ReadFromFile(tmpstr));
          logs.DeleteFile(tmpstr);
          exit(true);
    end;

    RegExpr.expression:='NetWorkCardsInfos';
    if RegExpr.Exec(uri) then begin
        FileData.Add(GLOBAL_INI.SYSTEM_NETWORK_IFCONFIG());
        exit(true);
    end;

    RegExpr.expression:='netcardsinfo';
    if RegExpr.Exec(uri) then begin
        FileData.Add(tcp.InterfacesList());
        exit(true);
    end;



    RegExpr.expression:='SystemNetworkUse';
    if RegExpr.Exec(uri) then begin
        if FileExists('/etc/network/interfaces') then FileData.Add('DEBIAN');
        if DirectoryExists('/etc/sysconfig/network-scripts') then FileData.Add('REDHAT');
        result:=true;
    end;

    RegExpr.expression:='nic-list';
    if RegExpr.Exec(uri) then begin
       GLOBAL_INI.SYSTEM_NETWORK_LIST_NICS();
        FileData.AddStrings(GLOBAL_INI.ArrayList);
        result:=true;
        exit;
    end;

    RegExpr.expression:='InetInfos:(.+)';
    if RegExpr.Exec(uri) then begin
        FileData.LoadFromStream(PROC.ExecStream('/sbin/ifconfig ' + RegExpr.Match[1],false));
        result:=true;
        exit;
    end;






   RegExpr.expression:='SystemGetFolderSize';
   if RegExpr.Exec(uri) then begin
      logs.logs('ParseUri::  -> get content of /etc/artica-postfix/FoldersSize.conf');
      if not FileExists('/etc/artica-postfix/FoldersSize.conf') then begin
            fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap --maintenance &');
      end;

       if FileExists('/etc/artica-postfix/FoldersSize.conf') then begin
          FileData.LoadFromFile('/etc/artica-postfix/FoldersSize.conf');
       end;

       result:=true;
       exit;
   end;
   RegExpr.expression:='DeleteFolderSizeMon';
   if RegExpr.Exec(uri) then begin
       if FileExists('/etc/artica-postfix/FoldersSize.conf') then GLOBAL_INI.DeleteFile('/etc/artica-postfix/FoldersSize.conf');
       result:=true;
       exit;
   end;

RegExpr.expression:='FollowFolderSize:(.+)';
   if RegExpr.Exec(uri) then begin
       if not FileExists(SYS.DU_PATH()) then begin
          logs.logs('ParseUri::  -> unable to locate DU tool!');
          exit;
       end;
       path:='/opt/artica/logs/'+GLOBAL_INI.MD5FromString(RegExpr.Match[1]);
       logs.logs('ParseUri::  -> '+path);
       cmd:=SYS.DU_PATH() + ' --max-depth=2 ' + RegExpr.Match[1] + ' >'+ path + ' 2>&1';
       logs.logs('ParseUri::  -> '+cmd);
       fpsystem(cmd);
       if FileExists(path) then begin
          FileData.LoadFromFile(path);
          GLOBAL_INI.DeleteFile(path);
          result:=true;
          exit;
       end else begin
          logs.logs('ParseUri::  -> unable to stat '+path);
       end;
       exit;
   end;




    RegExpr.expression:='SaveNicsInfos';
    if RegExpr.Exec(uri) then begin
        path:=RegExpr.Match[1];
        logs.logs('ParseUri::  -> Replicate NIC Configuration');
        logs.logs('ParseUri::  -> invoking replicate configuration nic ');
        GLOBAL_INI.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install -nic-configure');
        result:=true;
        exit;
    end;


//---------------------------------------//---------------------------------------//----------------

    RegExpr.expression:='PerformAutoRemove:(.+)';
    if RegExpr.Exec(uri) then begin
         if trim(RegExpr.Match[1])='APP_MAILGRAPH' then begin
             classInstall.MAILGRAPH_REMOVE();
         end;
         if trim(RegExpr.Match[1])='APP_QUEUEGRAPH' then begin
             classInstall.QUEUEGRAPH_REMOVE();
         end;
         if trim(RegExpr.Match[1])='APP_YOREL' then begin
             classInstall.YOREL_REMOVE();
         end;
         result:=true;
    end;




    RegExpr.expression:='PerformAutoInstall:(.+)';
    if RegExpr.Exec(uri) then begin
         path:=GLOBAL_INI.get_ARTICA_PHP_PATH();


         if(RegExpr.Match[1]='APP_CYRUS') then begin
             FileData.Add('{you must execute this operation manually}\nartica-install -autoinstall ' + RegExpr.Match[1]);
             exit(true);
         end;

         logs.logs('ParseUri::  -> invoking auto install : ' +RegExpr.Match[1]);
         fpsystem(path + '/bin/artica-install -autoinstall ' + RegExpr.Match[1]);
         LOGS.logs('Installation process could take long time, running installation in background mode...');
         //FileData.Add('{install_wait_few_minutes}');
         result:=true;
         exit();
     end;


   RegExpr.expression:='DeleteTheMainFilePostfixSettings:(.+)';
    if RegExpr.Exec(uri) then begin
        path:=RegExpr.Match[1];
        fpsystem('/etc/init.d/postfix stop 2>&1');
        fpsystem('/bin/rm ' + path);
        fpsystem('/etc/init.d/postfix start 2>&1');
        result:=true;
    end;

    RegExpr.expression:='SaveProcMailRules:(.+)';
    if RegExpr.Exec(uri) then begin
        path:=RegExpr.Match[1];
        fpsystem('/bin/mv ' + path + ' /etc/procmailrc');
         LOGS.logs('saving procmail settings username will be ' + GLOBAL_INI.PROCMAIL_USER());
        fpsystem('/bin/chown ' +GLOBAL_INI.PROCMAIL_USER() + ' /etc/procmailrc');
        fpsystem('/bin/chown ' + GLOBAL_INI.PROCMAIL_USER() +' ' +  GLOBAL_INI.PROCMAIL_QUARANTINE_PATH());
        result:=true;
    end;

     RegExpr.expression:='kav4mailservers\.conf';
     if RegExpr.Exec(uri) then begin
        FileData.LoadFromFile('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf');
        result:=true;
    end;

    //AveTemplate
   RegExpr.expression:='AveTemplate:([a-zA-Z]+)_([a-zA-Z]+)';
   if RegExpr.Exec(uri) then begin
       logs.logs('ParseUri::  -> Get template  : "' +RegExpr.Match[1] + '" "' +RegExpr.Match[2] + '"');
       FileData.Add(GLOBAL_INI.AVESERVER_GET_TEMPLATE_DATAS(RegExpr.Match[1],RegExpr.Match[2]));
       result:=true;

   end;

   RegExpr.expression:='filter\.conf';
     if RegExpr.Exec(uri) then begin
        FileData.Add(install.LinuxInfosDistri());
        result:=true;
        exit;
    end;

 //------------------------------------------------------------------------------
   RegExpr.expression:='CronDatas';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' Get tasks saved in cron.d path');
        GLOBAL_INI.SYSTEM_CRON_TASKS();
        LOGS.logs(' ' +  IntToStr(GLOBAL_INI.ArrayList.Count) + ' lines');
        FileData.AddStrings(GLOBAL_INI.ArrayList);
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------
   RegExpr.expression:='^delcron:(.+)';
     if RegExpr.Exec(uri) then begin
        tmpstr:='/etc/cron.d/'+RegExpr.Match[1];
        if FileExists(tmpstr) then begin
           LOGS.Debuglogs('delcron:: '+tmpstr);
           logs.DeleteFile(tmpstr);
        end;

        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------

   RegExpr.expression:='^addcron:(.+?);(.+)';
     if RegExpr.Exec(uri) then begin
        tmpstr:='/etc/cron.d/'+RegExpr.Match[2];
        LOGS.Debuglogs('addcron:: '+tmpstr);
        logs.WriteToFile(RegExpr.Match[1]+CRLF,tmpstr);
        logs.OutputCmd('/bin/chmod 0640 '+tmpstr);
        if FileExists('/etc/init.d/cron') then logs.OutputCmd('/etc/init.d/cron reload');
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------



 //------------------------------------------------------------------------------
   RegExpr.expression:='SaveResolve:(.+?);(.+)';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' DNS: From "' +  RegExpr.Match[1] + '" to "'   +RegExpr.Match[2]+'"');
        fpsystem('/bin/mv ' + RegExpr.Match[1] + ' ' +  RegExpr.Match[2]);
        fpsystem('/bin/chown root ' + RegExpr.Match[2]);
        result:=true;
        LOGS.logs(' REPLICATE DNS Done..');
        fpsystem(GLOBAL_INI.SYSTEM_NETWORK_INITD() + ' reload');
        exit;
    end;
 //------------------------------------------------------------------------------


 //------------------------------------------------------------------------------
   RegExpr.expression:='dnsmasq\.conf';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' get /etc/dnsmasq.conf datas');

        if not FileExists('/etc/dnsmasq.conf') then begin
           LOGS.logs(' unable to stat /etc/dnsmasq.conf');
           exit;
        end;


        FileData.LoadFromFile('/etc/dnsmasq.conf');
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------

   RegExpr.expression:='perform_inadyn';
   if RegExpr.Exec(uri) then begin
      LOGS.logs(' launch inadyn restarting');
      fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap -inadyn');
      exit(true);
   end;

RegExpr.expression:='logs_inadyn';
   if RegExpr.Exec(uri) then begin
      LOGS.logs(' get inadyn logs ');
      fpsystem('/usr/bin/tail -n 100 /opt/artica/logs/inadyn.log >/opt/artica/logs/tail.inadyn.log');
      LOGS.logs('/bin/tail -n 100 /opt/artica/logs/inadyn.log >/opt/artica/logs/tail.inadyn.log');
      if FileExists('/opt/artica/logs/tail.inadyn.log') then FileData.LoadFromFile('/opt/artica/logs/tail.inadyn.log');
      exit(true);
   end;

RegExpr.expression:='pids_inadyn';
if RegExpr.Exec(uri) then begin
    FileData.Add(GLOBAL_INI.INADYN_PID());
    exit(true);
end;


  //------------------------------------------------------------------------------
   RegExpr.expression:='fetchmailrc';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' get /etc/fetchmailrc datas');

        if not FileExists('/etc/fetchmailrc') then begin
           LOGS.logs(' unable to stat /etc/fetchmailrc');
           exit;
        end;


        FileData.LoadFromFile('/etc/fetchmailrc');
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------

 //------------------------------------------------------------------------------
   RegExpr.expression:='ReplicateCronTask';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' replicate tasks in order to save them  in cron.d path');
        GLOBAL_INI.SYSTEM_CRON_REPLIC_CONFIGS();
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------
   RegExpr.expression:='SavednsmasqConfigurationFile';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' replicate dnsmasq.conf');
        if FileExists(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/conf/dnsmasq.conf') then begin
           fpsystem('/bin/mv ' + GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/conf/dnsmasq.conf /etc/dnsmasq.conf');
           fpsystem('/bin/chown root /etc/dnsmasq.conf');
           fpsystem('/etc/init.d/dnsmasq restart');
        end;
        result:=true;
        exit;
    end;
   RegExpr.expression:='copyresolv:(.+)';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' replicate resolv.conf');
        ForceDirectories(ExtractFilePath(RegExpr.Match[1]));
        fpsystem('/bin/cp -f /etc/resolv.conf '+RegExpr.Match[1]);
        fpsystem('/bin/chown root '+RegExpr.Match[1]);
        result:=true;
        exit;
    end;




  //------------------------------------------------------------------------------
   RegExpr.expression:='GetUsersList';
     if RegExpr.Exec(uri) then begin
        LOGS.logs(' Get users list on this system');
        GLOBAL_INI.SYSTEM_USER_LIST();
        LOGS.logs(' ' +  IntToStr(GLOBAL_INI.ArrayList.Count) + ' lines');
        FileData.AddStrings(GLOBAL_INI.ArrayList);
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------

   RegExpr.expression:='MyKernel';
     if RegExpr.Exec(uri) then begin
        FileData.Add(GLOBAL_INI.SYSTEM_KERNEL_VERSION());
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------

   RegExpr.expression:='^dfmoinsh$';
     if RegExpr.Exec(uri) then begin
        FileData.LoadFromStream(GLOBAL_INI.ExecStream('/bin/df -h',false));
        result:=true;
        exit;
    end;

   RegExpr.expression:='^dfmoinshdev:(.+)';
     if RegExpr.Exec(uri) then begin
        FileData.LoadFromStream(GLOBAL_INI.ExecStream('/bin/df -h '+ RegExpr.Match[1],false));
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------


    RegExpr.expression:='^fstablist';
     if RegExpr.Exec(uri) then begin
        logs.Debuglogs('Understand, list lines in fstab...');
        try
           FileData.Add(logs.ReadFromFile('/etc/fstab'));

        except
           logs.Debuglogs('FATAL ERROR...');
        end;
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------
    RegExpr.expression:='^mountlist';
     if RegExpr.Exec(uri) then begin
        logs.Debuglogs('Understand, list lines in mount...');
        tmpstr:=logs.FILE_TEMP();
        try
           fpsystem('/bin/mount -l >'+tmpstr+' 2>&1');
           FileData.Add(logs.ReadFromFile(tmpstr));
           logs.DeleteFile(tmpstr)

        except
           logs.Debuglogs('FATAL ERROR...');
        end;
        result:=true;
        exit;
    end;
 //------------------------------------------------------------------------------
    RegExpr.expression:='^fstabapply';
     if RegExpr.Exec(uri) then begin
        logs.Debuglogs('Understand, save lines in fstab...');
        if not FileExists('/etc/artica-postfix/settings/Daemons/SystemFSTAB') then exit(true);
        try
           tmpstr:=logs.ReadFromFile('/etc/artica-postfix/settings/Daemons/SystemFSTAB');
           logs.WriteToFile(tmpstr,'/etc/fstab')
        except
           logs.Debuglogs('FATAL ERROR...');
        end;
        result:=true;
        exit;
    end;

      RegExpr.expression:='^lvcreate:(.+?);(.+?);(.+)';
     if RegExpr.Exec(uri) then begin
        logs.Debuglogs('Understand, lvcreate '+RegExpr.Match[1]+' from LVM...');
        tmpstr:=logs.FILE_TEMP();
        fpsystem(SYS.LOCATE_LVRCREATE()+' -n '+RegExpr.Match[2]+' -L ' + RegExpr.Match[3]+'g ' + RegExpr.Match[1]+' >'+tmpstr+' 2>&1');
        FileData.Add(logs.ReadFromFile(tmpstr));
        result:=true;
        exit;
    end;
     RegExpr.expression:='master\.cf';
     if RegExpr.Exec(uri) then begin
        FileData.LoadFromFile('/etc/postfix/master.cf');
        result:=true;
        exit;
    end;

    RegExpr.expression:='resolv\.conf';
  if RegExpr.Exec(uri) then begin
        FileData.LoadFromFile('/etc/resolv.conf');
        result:=true;
    end;

     RegExpr.expression:='aveserver_version';
     if RegExpr.Exec(uri) then begin
        FileData.Add(kavmilter.VERSION());
        result:=true;
    end;
     RegExpr.expression:='aveserver_licence';
     if RegExpr.Exec(uri) then begin
        FileData.Add(GLOBAL_INI.AVESERVER_GET_LICENCE());
        result:=true;
    end;

     RegExpr.expression:='aveserver_infos';
     if RegExpr.Exec(uri) then begin
        FileData.Add(cgiop.KAV_GET_DAEMON_INFOS());
        result:=true;
    end;

     RegExpr.expression:='aveserver_licencemanager:(.+)';
     if RegExpr.Exec(uri) then begin
        FileData.LoadFromStream(PROC.ExecStream('/opt/kav/5.5/kav4mailservers/bin/licensemanager -a ' +  RegExpr.Match[1],false));
        fpsystem('/bin/rm ' +  RegExpr.Match[1]);
        RegExpr.Free;
        result:=true;
        exit;
    end;

    RegExpr.expression:='aveserver_licencemanager_remove:(.+)';
     if RegExpr.Exec(uri) then begin
        FileData.LoadFromStream(PROC.ExecStream('/opt/kav/5.5/kav4mailservers/bin/licensemanager -d ' +  RegExpr.Match[1],false));
        RegExpr.Free;
        result:=true;
        exit;
    end;

     RegExpr.expression:='aveserver_licence_extra';
     if RegExpr.Exec(uri) then begin
        FileData.LoadFromStream(PROC.ExecStream('/opt/kav/5.5/kav4mailservers/bin/licensemanager -i',false));
        RegExpr.Free;
        result:=true;
        exit;
    end;

    RegExpr.expression:='smtpscanner\.log';
     if RegExpr.Exec(uri) then begin
        tail_smtpscanner_logs();
        result:=true;
    end;

    RegExpr.expression:='articafilter\.log';
     if RegExpr.Exec(uri) then begin
        if FileExists('/var/log/artica-postfix/mail.log') then FileData.LoadFromFile('/var/log/artica-postfix/mail.log');
        result:=true;
    end;


    RegExpr.expression:='^miltergreylistlogs';
     if RegExpr.Exec(uri) then begin
        tmpstr:= SYS.MAILLOG_PATH();
        logs.Debuglogs('miltergreylistlogs:: '+tmpstr );
        tmpstr:=logs.FILE_TEMP();
        cmd:='/usr/bin/tail -n 500 '+SYS.MAILLOG_PATH()+'|grep milter-greylist >'+tmpstr+' 2>&1';
        logs.Debuglogs('miltergreylistlogs:: "'+cmd+'"');
        fpsystem(cmd);
        FileData.Add(logs.ReadFromFile(tmpstr));
        logs.DeleteFile(tmpstr);
        exit(true);
     end;




    RegExpr.expression:='keeup2datelog_kav';
     if RegExpr.Exec(uri) then begin
        tail_keeup2datelog_kav_logs();
        exit(true);
    end;
//........................................... QUARANTINE ORDERS ......................................................................................

RegExpr.expression:='quaresend:(.+?);(.+)';
     if RegExpr.Exec(uri) then begin
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-resend quarantine "' + RegExpr.Match[1] + '" "' + RegExpr.Match[2]+'" >/opt/artica/tmp/rsend.tmp 2>&1';
        logs.logs('ParseUri::  ->' +command_line);
        fpsystem(command_line);
        if FileExists('/opt/artica/tmp/rsend.tmp') then FileData.LoadFromFile('/opt/artica/tmp/rsend.tmp');
        exit(true);
    end;


RegExpr.expression:='^DeleteQuarantineMessage:(.+)';
     if RegExpr.Exec(uri) then begin
        tmpstr:=RegExpr.Match[1];
        if ExtractFileExt(tmpstr)='.eml' then begin
           if FileExists(tmpstr) then begin
              logs.DeleteFile(tmpstr);
           end;
           exit(true);
        end else begin
            logs.Debuglogs('Unable to find eml file in your command : '+ExtractFileExt(tmpstr));
        end;
     end;




RegExpr.expression:='QuarantineMessageDelete:::(.+?):::(.+)';
     if RegExpr.Exec(uri) then begin
          if FileExists('/var/quarantines/procmail/' + RegExpr.Match[1] + '/new/' + RegExpr.Match[2]) then begin
             command_line:='/bin/rm /var/quarantines/procmail/' + RegExpr.Match[1] + '/new/' + RegExpr.Match[2];
             logs.logs('ParseUri::  ->' +command_line);
             fpsystem(command_line);

          end else begin
            logs.logs('ParseUri::  Unable to stat ' +'/var/quarantines/procmail/' + RegExpr.Match[1] + '/new/' + RegExpr.Match[2]);
          end;
          exit(true);
     end;

RegExpr.expression:='QuarantineDeletePattern:(.+)';
     if RegExpr.Exec(uri) then begin
      logs.logs('ParseUri::  QuarantineDeletePattern ->' +RegExpr.Match[1]);
      if cgiOp.QuarantineDeletePattern(RegExpr.Match[1])=true then begin
          FileData.Add('OK');
          exit(true);
      end;
     exit(true);
    end;
//...............................................................................................;
RegExpr.expression:='QuarantineQueryPattern:::(.+?):::(.+)';
     if RegExpr.Exec(uri) then begin
           logs.logs('ParseUri::  ->' +RegExpr.Match[2]);
          QuarantineQueryPattern(RegExpr.Match[1],RegExpr.Match[2]);
           FileData.Add('OK');
          exit(true);
    end;




RegExpr.expression:='QuarantineShowEmailFile:(.+)';
    if RegExpr.Exec(uri) then begin
        FileData.Clear;
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime parse ' + trim(RegExpr.Match[1]) + '';
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        logs.logs('ParseUri::  ->' +command_line);
        logs.logs('ParseUri:: receive ' +IntToStr(FileData.Count) + ' lines');
        exit(true);
    end;


RegExpr.expression:='deleteallmailfrommailtoother:(.+)';
 if RegExpr.Exec(uri) then begin
        FileData.Clear;
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-quarantine deleteallmailfrommailtoother ' + trim(RegExpr.Match[1]);
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        logs.logs('ParseUri::  ->' +command_line);
        exit(true)
     end;

RegExpr.expression:='deleteallmailfrommailtoyesterday:(.+)';
 if RegExpr.Exec(uri) then begin
        FileData.Clear;
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-quarantine deleteallmailfrommailtoyesterday ' + trim(RegExpr.Match[1]);
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        logs.logs('ParseUri::  ->' +command_line);
        exit(true)
     end;

RegExpr.expression:='releasemailmd5:(.+)';
     if RegExpr.Exec(uri) then begin
        FileData.Clear;
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime releasemailmd5 ' + trim(RegExpr.Match[1]);
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        logs.logs('ParseUri::  ->' +command_line);
        exit(true)
     end;

RegExpr.expression:='releaseallmailfrommd5:(.+)';
     if RegExpr.Exec(uri) then begin
        FileData.Clear;
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime releaseallmailfrommd5 ' + trim(RegExpr.Match[1]);
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        logs.logs('ParseUri::  ->' +command_line);
        exit(true)
     end;



RegExpr.expression:='QuarantineReleaseMail:(.+?):(.+)';
     if RegExpr.Exec(uri) then begin
        FileData.Clear;
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime send ' + trim(RegExpr.Match[2]) + ' ' +trim(RegExpr.Match[1]) + ' delete';
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        logs.logs('ParseUri::  ->' +command_line);
        logs.logs('ParseUri:: receive ' +IntToStr(FileData.Count) + ' lines');
        exit(true)
     end;

RegExpr.expression:='lastquanrantine:(.+)';
    if RegExpr.Exec(uri) then begin
        FileData.Clear;

        if not FileExists(GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime') then begin
           LOGS.logs(' unable to stat ' + GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime');
           exit(false);
        end;

        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime lasttenQuarFiles ' + trim(RegExpr.Match[1]);
        logs.logs('ParseUri::  ->' +command_line);
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        logs.logs('ParseUri:: receive ' +IntToStr(FileData.Count) + ' lines');
        exit(true);
    end;

    RegExpr.expression:='quarantine_list:([0-9]+)-([0-9]+)-(.+)';
     if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri::  -> Get file list from : ' +RegExpr.Match[1] + ' to ' + RegExpr.Match[2] + ' from ' + RegExpr.Match[3]  + ' user');
        FileData:=GLOBAL_INI.PROCMAIL_READ_QUARANTINE(StrToInt(RegExpr.Match[1]),StrToInt(RegExpr.Match[2]),RegExpr.Match[3]);
        exit(true);
    end;

    RegExpr.expression:='queue_list:([0-9]+)-([0-9]+)-(.+?)&file=(.+)';
     if RegExpr.Exec(uri) then begin
        logs.logs('ParseUri::  -> execute queue list from : ' +RegExpr.Match[1] + ' to ' + RegExpr.Match[2] + ' from ' + RegExpr.Match[3]  + ' queue and save if to '+RegExpr.Match[4] );
         path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
         fpsystem(path + '/bin/artica-install -postfix queuelist '+RegExpr.Match[1]+ ' ' +RegExpr.Match[2]+ ' ' +RegExpr.Match[3] + ' ' + RegExpr.Match[4] );
         exit(true);
    end;


     RegExpr.expression:='quarantine_delete_file:(.+):::(.+)';
  if RegExpr.Exec(uri) then begin
        path:=GLOBAL_INI.PROCMAIL_QUARANTINE_PATH() + '/' + RegExpr.Match[2] + '/new/' + RegExpr.Match[1];

        logs.logs('ParseUri::  -> delete file ' + path);
        if fileExists(path) then begin
        fpsystem('/bin/rm -f ' + path);
        end else begin
          logs.logs('ParseUri::  -> ' + path + ' does not exists');
        end;
        FileData.Add('ok');
        exit(true);
    end;

     RegExpr.expression:='quarantine_delete_all:([a-zA-Z0-9\.\-_\@]+)';
  if RegExpr.Exec(uri) then begin
        path:=GLOBAL_INI.PROCMAIL_QUARANTINE_PATH();
        if length(path)=0 then exit(false);
        path:=GLOBAL_INI.PROCMAIL_QUARANTINE_PATH() + '/' + RegExpr.Match[1] + '/new';
        logs.logs('ParseUri::  -> delete folder ' + path);
        if DirectoryExists(path) then begin
        fpsystem('/bin/rm -rf ' + path);
        end else begin
          logs.logs('ParseUri::  -> ' + path + ' does not exists');
        end;
        FileData.Add('ok');
        exit(true);
    end;



    RegExpr.expression:='view_queue_file:(.+)';
    if RegExpr.Exec(uri) then begin
         logs.logs('ParseUri::  -> load queue file ' + RegExpr.Match[1]);
         FileData.Add(SYS.ExecPipe('/usr/sbin/postcat -q ' + RegExpr.Match[1]));
         exit(true);
    end;

    RegExpr.expression:='postsuper_d:([A-Za-z0-9]+)';
    if RegExpr.Exec(uri) then begin
         logs.logs('ParseUri::  -> delete queue file ' + RegExpr.Match[1]);
         logs.logs('ParseUri::  -> EXEC /usr/sbin/postsuper -d  ' + RegExpr.Match[1]);
         fpsystem('/usr/sbin/postsuper -d  ' + RegExpr.Match[1]);
         logs.logs('ParseUri::  -> DELETE FILE FROM CACHE');
         GLOBAL_INI.POSFTIX_DELETE_FILE_FROM_CACHE(RegExpr.Match[1]);
         exit(true);
    end;

    RegExpr.expression:='postqueue_f';
    if RegExpr.Exec(uri) then begin
         fpsystem('/usr/sbin/postqueue -f &');
         exit(true);
    end;

    RegExpr.expression:='PostfixDeleteMailsQeue:([a-z]+)';
    if RegExpr.Exec(uri) then begin
         logs.logs('ParseUri::  -> delete queue files ' + RegExpr.Match[1]);
         logs.logs('ParseUri::  -> EXEC /usr/sbin/postsuper -d  ALL ' + RegExpr.Match[1]);
         fpsystem('/usr/sbin/postsuper -d  ALL ' + RegExpr.Match[1]);
         exit(true);
    end;


 //##################### Apply Kas configuration ##########################################





   RegExpr.expression:='KasTrapUpdatesErrors';
   if RegExpr.Exec(uri) then begin
      FileData.Add(cgiOp.KAS_TRAP_UPDATES_ERROR(''));
      exit(true);
   end;

   RegExpr.expression:='KasForceUpdatesFromErrors';
   if RegExpr.Exec(uri) then begin
     cgiOp.KAS_FORCE_UPDATES_ERROR();
     exit(true);
   end;


   RegExpr.expression:='KasUpdatesPatternNow';
   if RegExpr.Exec(uri) then begin
     cgiOp.KAS_FORCE_UPDATES_NOW();
     exit(true);
   end;

  RegExpr.expression:='KasTrapUpdatesSuccess';
   if RegExpr.Exec(uri) then begin
     FileData.Add(cgiOp.KAS_TRAP_UPDATES_SUCCESS(''));
     exit(true);
   end;

  RegExpr.expression:='KasGetCronTask';
   if RegExpr.Exec(uri) then begin
     FileData.Add(trim(cgiOp.KAS_GET_CRON_TASK_UPDATE()));
     exit(true);
   end;



RegExpr.expression:='kasStatus';
    if RegExpr.Exec(uri) then begin
        cgiOp.KAS_STATUS();
        FileData.AddStrings(cgiop.FA);
        result:=true;
    end;

    RegExpr.expression:='kasUpdaterConf:(.+)';
    if RegExpr.Exec(uri) then begin
        path:=RegExpr.Match[1];
        if fileExists(path) then begin
           logs.logs('ParseUri::  -> replicate Kaspersky Anti-Spam updater conf file : ' +path);
           fpsystem('/bin/mv ' + path + ' /usr/local/ap-mailfilter3/etc/keepup2date.conf');
           exit(true);
        end;
    end;

RegExpr.expression:='KasperskyAntispamRulesDef';
if RegExpr.Exec(uri) then begin
       FileData.Add(logs.ReadFromFile('/usr/local/ap-mailfilter3/conf/def/group/00000000-rule.def'));
       exit(true);
end;



//####################################################################################

    RegExpr.expression:='aveserver_updates_errors';
    if RegExpr.Exec(uri) then begin
         FileData.Add(cgiOp.KAV_TRAP_UPDATES_ERROR());
         exit(true);
       end;
    RegExpr.expression:='aveserver_updates_success';
    if RegExpr.Exec(uri) then begin
         FileData.Add(cgiOp.KAV_TRAP_UPDATES_SUCCESS());
         exit(true);
       end;

    RegExpr.expression:='aveserver_daemon_error';
    if RegExpr.Exec(uri) then begin
         FileData.Add(cgiOp.KAV_TRAP_DAEMON_ERROR());
         exit(true);
       end;

   RegExpr.expression:='aveserver_daemon_lastlogs';
    if RegExpr.Exec(uri) then begin
         cgiOp.KAV_TRAP_DAEMON_EVENTS();
         FileData:=cgiOp.FA;
         exit(true);
       end;

    RegExpr.expression:='aveserver_perform_udpates';
    if RegExpr.Exec(uri) then begin
         cgiOp.KAV_PERFORM_UPDATE();
         exit(true);
       end;

  RegExpr.expression:='KavGetCronTask';
   if RegExpr.Exec(uri) then begin
     FileData.Add(trim(cgiOp.KAV_GET_CRON_TASK_UPDATE()));
     exit(true);
   end;


    RegExpr.expression:='procmail_logs';
       if RegExpr.Exec(uri) then begin
         FileData.LoadFromStream(PROC.ExecStream('/usr/bin/tail '+ GLOBAL_INI.PROCMAIL_LOGS_PATH()+ ' -n 100',false));
          result:=true;
          exit;
       end;

    RegExpr.expression:='kav4mailservers:(.+)';
    if RegExpr.Exec(uri) then begin
        path:=RegExpr.Match[1];
        logs.logs('ParseUri::  -> replicate kav4mailservers.conf file : ');
        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-install -mailav replicate '+ path;
        logs.logs('ParseUri::  -> "'+ command_line + '"');
        GLOBAL_INI.THREAD_COMMAND_SET(command_line);
        result:=true;
    end;

//--------------------------------------- Services & dameons ----------------------------------
    RegExpr.expression:='start_service:(.+)';
    if RegExpr.Exec(uri) then begin
        cgiop.SYSTEM_START_STOP_SERVICES(RegExpr.Match[1],true);
        exit(true);
    end;


    RegExpr.expression:='svc;([0-9]);([a-z0-9]+)';
    if RegExpr.Exec(uri) then begin
       tmpstr:=logs.FILE_TEMP();


        if RegExpr.Match[1]='0' then begin
           command_line:='/etc/init.d/artica-postfix stop ' + RegExpr.Match[2] + ' --verbose >'+tmpstr+' 2>&1';
           fpsystem(command_line);
           logs.Debuglogs('ParseUri::Services & dameons  -> "'+ command_line + '"');
           logs.DeleteFile('/etc/artica-postfix/cache.global.status');
           if FileExists(tmpstr) then begin
              FileData.LoadFromFile(tmpstr);
              logs.DeleteFile(tmpstr);
              logs.Debuglogs('ParseUri::Services & dameons  ->' + FileData.Text);
           end else begin
               logs.Debuglogs('ParseUri::could not stat '+tmpstr);

           end;

           exit(true);
        end;

        if RegExpr.Match[1]='1' then begin

           command_line:='/etc/init.d/artica-postfix start ' + RegExpr.Match[2] + ' --verbose >'+tmpstr+' 2>&1';
           logs.Debuglogs('ParseUri::Services & dameons  -> "'+ command_line + '"');
           fpsystem(command_line);
           logs.DeleteFile('/etc/artica-postfix/cache.global.status');
           if FileExists(tmpstr) then begin
              FileData.LoadFromFile(tmpstr);
              logs.DeleteFile(tmpstr);
              logs.Debuglogs('ParseUri::Services & dameons  ->' + FileData.Text);
           end else begin
               logs.Debuglogs('ParseUri::could not stat '+tmpstr);

           end;
           exit(true);
        end;

    end;

    RegExpr.expression:='stop_service:(.+)';
    if RegExpr.Exec(uri) then begin
        cgiop.SYSTEM_START_STOP_SERVICES(RegExpr.Match[1],false);
        exit(true);
    end;

    RegExpr.expression:='keepup2date';
    if RegExpr.Exec(uri) then begin
        GLOBAL_INI.THREAD_COMMAND_SET('/opt/kav/5.5/kav4mailservers/bin/keepup2date -q &');
        result:=true;
    end;

    RegExpr.expression:='kas3ViewUpdateConf';
    if RegExpr.Exec(uri) then begin
        if fileExists('/usr/local/ap-mailfilter3/etc/keepup2date.conf') then begin
           logs.logs('ParseUri:: kas3keepup2dateconf -> Parsing /usr/local/ap-mailfilter3/etc/keepup2date.conf');
           FileData.LoadFromFile('/usr/local/ap-mailfilter3/etc/keepup2date.conf');
           exit(true);
        end;
    end;

    RegExpr.expression:='kas3ViewLicenceInfos';
     if RegExpr.Exec(uri) then begin
        if fileExists('/usr/local/ap-mailfilter3/bin/kas-show-license') then begin
           LOGS.logs('kas3ViewLicenceInfos -> Load datas for /usr/local/ap-mailfilter3/bin/kas-show-license');
           FileData.LoadFromStream(PROC.ExecStream('/usr/local/ap-mailfilter3/bin/kas-show-license',false));
           exit(true);
        end;
     end;


     RegExpr.expression:='kas_licencemanager:(.+)';
     if RegExpr.Exec(uri) then begin
        if fileExists('/usr/local/ap-mailfilter3/bin/kas-show-license') then begin
           LOGS.logs(' -> apply licence '+RegExpr.Match[1] );
           if FileExists(RegExpr.Match[1]) then FileData.LoadFromStream(PROC.ExecStream('/usr/local/ap-mailfilter3/bin/install-key ' + RegExpr.Match[1],false));
           exit(true);
        end;
     end;



  LOGS.logs('Unable to understood ' + uri);

 RegExpr.Free;

end;



//#####################################################################################
procedure Tparsehttp.tail_keeup2datelog_kav_logs();
begin
     maillog:=GLOBAL_INI.AVESERVER_GET_KEEPUP2DATE_LOGS_PATH();
     FileData.LoadFromStream(PROC.ExecStream('/usr/bin/tail '+ maillog + ' -n 100',false));
end;

//#####################################################################################
procedure Tparsehttp.tail_postfix_logs_filter(filter:string);

var mycmd:string;
begin
     maillog:=GLOBAL_INI.get_LINUX_MAILLOG_PATH();
     mycmd:='/usr/bin/tail '+ maillog + ' -n 100|grep '+ filter+' >/opt/artica/logs/grep_' + filter;
     logs.logs('tail_postfix_logs_filter->"' + filter + '"');
     logs.logs('tail_postfix_logs_filter->"' + mycmd + '"');
     fpsystem(mycmd);
     FileData.LoadFromFile('/opt/artica/logs/grep_' + filter);
end;
//#####################################################################################


procedure Tparsehttp.tail_smtpscanner_logs();

begin
     maillog:=GLOBAL_INI.AVESERVER_GET_VALUE('smtpscan.report','ReportFileName');
     FileData.LoadFromStream(PROC.ExecStream('/usr/bin/tail '+ maillog + ' -n 100',false));
end;





//#####################################################################################
procedure Tparsehttp.avestatus();
var pid:string;
begin
   pid:=GLOBAL_INI.AVESERVER_GET_PID();
   if length(pid)=0 then begin
       if FileExists('/etc/init.d/aveserver') then begin
          FileData.Add('0');
          exit;
       end;
       FileData.Add('-1');
       exit;
   end;
   if FileExists('/proc/' + pid + '/exe') then begin
      FileData.Add('1');
      exit;
   end;
 FileData.Add('0');

end;
//#####################################################################################
procedure Tparsehttp.ReloadPostfix();
var pid:string;
begin
pid:=postfix.POSTFIX_PID();
LOGS.logs('ReloadPostfix -> MASTER PID ' + pid );
if FileExists('/proc/' + pid + '/exe') then begin
   LOGS.logs('replicateMaincf -> Reload postfix  ');
   fpsystem('/etc/init.d/postfix reload 2>&1');
   end
   else begin
       LOGS.logs('replicateMaincf -> start postfix  ');
       fpsystem('/etc/init.d/postfix start 2>&1');
end;

end;

procedure Tparsehttp.QuarantineQueryPattern(userid:string;Patterns:string);
var
   regex,command_line,tempfile:string;
   z:integer;
begin
 i:=0;
 regex:='';
 LOGS.logs('QuarantineQueryPattern:: userid:' + userid + ' ' + Patterns);
 tempfile:='/opt/artica/logs/list.'+ userid;
         Explosed:=Explode(':::',Patterns);
         LOGS.logs('QuarantineQueryPattern:: REGEX NUMBER ' + intToStr(length(Explosed)));
         if length(Explosed)>0 then begin
            for z:=0 to length(Explosed)-1 do begin
               if length(Explosed[z])>0 then begin
                  regex:=regex + ' -e''' +Explosed[z] + '''';

               end;
             end;
         end;
         LOGS.logs('QuarantineQueryPattern:: REGEX:' + regex);
         if FileExists(tempfile) then fpsystem('/bin/rm ' + tempfile);
         command_line:='/bin/egrep -r -l ' + regex + ' /var/quarantines/procmail/' + userid + '/quarantine >' + tempfile;
         GLOBAL_INI.THREAD_COMMAND_SET(command_line);

         repeat
            LOGS.logs('QuarantineQueryPattern:: ParseUri:: WAIT ->' + tempfile);
            if FileExists(tempfile) then break;
            sleep(100)
         until not FileExists(tempfile);

        command_line:=GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-mime listMails ' + tempfile;
        FileData.LoadFromStream(GLOBAL_INI.ExecStream(command_line,false));
        LOGS.logs('QuarantineQueryPattern:: ParseUri::  ->' +command_line);
        LOGS.logs('QuarantineQueryPattern:: ParseUri:: receive ' +IntToStr(FileData.Count) + ' lines');


end;


//#####################################################################################
function Tparsehttp.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
var
  SepLen       : Integer;
  F, P         : PChar;
  ALen, Index  : Integer;
begin
  SetLength(Result, 0);
  if (S = '') or (Limit < 0) then
    Exit;
  if Separator = '' then
  begin
    SetLength(Result, 1);
    Result[0] := S;
    Exit;
  end;
  SepLen := Length(Separator);
  ALen := Limit;
  SetLength(Result, ALen);

  Index := 0;
  P := PChar(S);
  while P^ <> #0 do
  begin
    F := P;
    P := StrPos(P, PChar(Separator));
    if (P = nil) or ((Limit > 0) and (Index = Limit - 1)) then
      P := StrEnd(F);
    if Index >= ALen then
    begin
      Inc(ALen, 5); // mehrere auf einmal um schneller arbeiten zu können
      SetLength(Result, ALen);
    end;
    SetString(Result[Index], F, P - F);
    Inc(Index);
    if P^ <> #0 then
      Inc(P, SepLen);
  end;
  if Index < ALen then
    SetLength(Result, Index); // wirkliche Länge festlegen
end;
//##############################################################################
end.

