<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}

if($argv[1]=='--config'){buildconfig();die();}

if($argv[1]=="--init"){build_init();die();}

if($argv[1]=="--affect"){buid_affectation();die();}


function locate_config_pl(){
	if(is_file("/etc/backuppc/config.pl")){return "/etc/backuppc/config.pl";}
	if(is_file("/etc/BackupPC/config.pl")){return "/etc/BackupPC/config.pl";}
	
}
function locate_backuppc_share(){
	if(is_file("/usr/share/BackupPC/bin/BackupPC")){return "/usr/share/BackupPC";}
	if(is_file("/usr/share/backuppc/bin/BackupPC")){return "/usr/share/backuppc";}
	
}
function locate_backuppc_TopDir(){
	if(is_dir("/var/lib/backuppc")){return "/var/lib/backuppc";}
	if(is_dir("/var/lib/BackupPC")){return "/var/lib/BackupPC";}
	return "/var/lib/backuppc";
	
}

function build_init(){
	$users=new usersMenus();
	if($users->LinuxDistriCode=="DEBIAN"){buil_init_debian();}
	if($users->LinuxDistriCode=="UBUNTU"){buil_init_debian();}
	if($users->LinuxDistriCode=="CENTOS"){build_init_redhat();}
	if($users->LinuxDistriCode=="FEDORA"){build_init_redhat();}
}

function build_init_redhat(){
	$unix=new unix();
	
	$share=locate_backuppc_share();
	
$conf[]="#!/bin/sh";
$conf[]="#";
$conf[]="# DESCRIPTION";
$conf[]="#";
$conf[]="#   Startup init script for BackupPC on Redhat linux.";
$conf[]="#";
$conf[]="# Distributed with BackupPC version 3.1.0, released 25 Nov 2007.";
$conf[]="#";
$conf[]="# chkconfig: - 91 35";
$conf[]="# description: Starts and stops the BackupPC server";
$conf[]="";
$conf[]="# Source function library.";
$conf[]="if [ -f /etc/init.d/functions ] ; then";
$conf[]="  . /etc/init.d/functions";
$conf[]="elif [ -f /etc/rc.d/init.d/functions ] ; then";
$conf[]="  . /etc/rc.d/init.d/functions";
$conf[]="else";
$conf[]="  exit 0";
$conf[]="fi";
$conf[]="";
$conf[]="RETVAL=0";
$conf[]="";
$conf[]="start() {";
$conf[]="    #";
$conf[]="    # You can set the SMB share password here is you wish.  Otherwise";
$conf[]="    # you should put it in the config.pl script.";
$conf[]="    # If you put it here make sure this file has no read permissions";
$conf[]="    # for normal users!  See the documentation for more information.";
$conf[]="    #";
$conf[]="    # Replace the daemon line below with this:";
$conf[]="    #   ";
$conf[]="    #  daemon --user backuppc /usr/bin/env BPC_SMB_PASSWD=xxxxx \\";
$conf[]="    #				$share/bin/BackupPC -d";
$conf[]="    #   ";
$conf[]="    echo -n \"Starting BackupPC: \"";
$conf[]="    daemon --user backuppc $share/bin/BackupPC -d";
$conf[]="    RETVAL=\$?";
$conf[]="    echo";
$conf[]="    [ \$RETVAL -eq 0 ] && touch /var/lock/subsys/backuppc || \\";
$conf[]="       RETVAL=1";
$conf[]="    return \$RETVAL";
$conf[]="}	";
$conf[]="";
$conf[]="stop() {";
$conf[]="    echo -n \"Shutting down BackupPC: \"";
$conf[]="    killproc $share/bin/BackupPC";
$conf[]="    RETVAL=\$?";
$conf[]="    [ \$RETVAL -eq 0 ] && rm -f /var/lock/subsys/backuppc";
$conf[]="    echo \"\"";
$conf[]="    return \$RETVAL";
$conf[]="}	";
$conf[]="";
$conf[]="restart() {";
$conf[]="    stop";
$conf[]="    start";
$conf[]="}	";
$conf[]="";
$conf[]="reload() {";
$conf[]="    echo -n \"Reloading config.pl file: \"";
$conf[]="    killproc $share/BackupPC -HUP";
$conf[]="    RETVAL=\$?";
$conf[]="    echo";
$conf[]="    return \$RETVAL";
$conf[]="}	";
$conf[]="";
$conf[]="rhstatus() {";
$conf[]="    status $share/bin/BackupPC";
$conf[]="}";
$conf[]="";
$conf[]="case \"\$1\" in";
$conf[]="  start)";
$conf[]="  	start";
$conf[]="	;;";
$conf[]="  stop)";
$conf[]="  	stop";
$conf[]="	;;";
$conf[]="  restart)";
$conf[]="  	restart";
$conf[]="	;;";
$conf[]="  reload)";
$conf[]="  	reload";
$conf[]="	;;";
$conf[]="  status)";
$conf[]="  	rhstatus";
$conf[]="	;;";
$conf[]="  *)";
$conf[]="	echo \"Usage: \$0 {start|stop|restart|reload|status}\"";
$conf[]="	exit 1";
$conf[]="esac";
$conf[]="";
$conf[]="exit \$?";
$conf[]="";	
@file_put_contents("/etc/init.d/backuppc",@implode("\n",$conf));	
@chmod("/etc/init.d/backuppc",0755);
}

function buil_init_debian(){
$share=locate_backuppc_share();	
$conf[]="#! /bin/sh";
$conf[]="# /etc/init.d/backuppc";
$conf[]="#";
$conf[]="# BackupPC Debian init script";
$conf[]="#";
$conf[]="### BEGIN INIT INFO";
$conf[]="# Provides:          backuppc";
$conf[]="# Required-Start:    \$syslog \$network";
$conf[]="# Required-Stop:     \$syslog \$network";
$conf[]="# Should-Start:      \$local_fs";
$conf[]="# Should-Stop:       \$local_fs";
$conf[]="# Default-Start:     2 3 4 5";
$conf[]="# Default-Stop:      1";
$conf[]="# Short-Description: Launch backuppc server";
$conf[]="# Description:       Launch backuppc server, a high-performance, ";
$conf[]="#		     enterprise-grade system for backing up PCs.";
$conf[]="### END INIT INFO";
$conf[]="";
$conf[]="set -e";
$conf[]="";
$conf[]="# Do not change the values below ! Read /usr/share/doc/backuppc/README.Debian !";
$conf[]="BINDIR=$share/bin";
$conf[]="DATADIR=/var/lib/backuppc";
$conf[]="USER=backuppc";
$conf[]="NICE=0";
$conf[]="#";
$conf[]="NAME=backuppc";
$conf[]="DAEMON=BackupPC";
$conf[]="";
$conf[]="test -x \$BINDIR/\$DAEMON || exit 0";
$conf[]=". /lib/lsb/init-functions";
$conf[]="[ -r /etc/default/rcS ] && . /etc/default/rcS";
$conf[]="";
$conf[]="if [ ! -d /var/run/backuppc ]; then";
$conf[]="    mkdir /var/run/backuppc";
$conf[]="    chown backuppc:backuppc /var/run/backuppc";
$conf[]="fi";
$conf[]="";
$conf[]="# Check for incompatible old config files";
$conf[]="check_old_config()";
$conf[]="{";
$conf[]="    BAD=0";
$conf[]="    CONF=/etc/backuppc/config.pl";
$conf[]="    ";
$conf[]="    grep -q IncrLevel \$CONF || BAD=1";
$conf[]="    ";
$conf[]="    if [ \"\$BAD\" = \"1\" ]; then";
$conf[]="	echo \"BackupPC cannot be started because important parameters are missing from config.pl.\"";
$conf[]="	echo \"If you just upgraded BackupPC, please update /etc/backuppc/config.pl.\"";
$conf[]="	exit 1";
$conf[]="    fi";
$conf[]="}";
$conf[]="";
$conf[]="";
$conf[]="case \"\$1\" in";
$conf[]="  start)";
$conf[]="    log_begin_msg \"Starting \$NAME...\"";
$conf[]="    check_old_config";
$conf[]="    start-stop-daemon --start --pidfile /var/run/backuppc/BackupPC.pid \\";
$conf[]="			    --nicelevel \$NICE -c \$USER --exec \$BINDIR/\$DAEMON -- -d";
$conf[]="    log_end_msg \$?";
$conf[]="    ;;";
$conf[]="  stop)";
$conf[]="    log_begin_msg \"Stopping \$NAME...\"";
$conf[]="    start-stop-daemon --stop --pidfile /var/run/backuppc/BackupPC.pid -u \$USER \\";
$conf[]="			    --oknodo --retry 30";
$conf[]="    log_end_msg \$?";
$conf[]="      ;;";
$conf[]="  restart)";
$conf[]="    log_begin_msg \"Restarting \$NAME...\"";
$conf[]="    start-stop-daemon --stop --pidfile /var/run/backuppc/BackupPC.pid -u \$USER \\";
$conf[]="			    --oknodo --retry 30";
$conf[]="    check_old_config";
$conf[]="    start-stop-daemon --start --pidfile /var/run/backuppc/BackupPC.pid \\";
$conf[]="			    --nicelevel \$NICE -c \$USER --exec \$BINDIR/\$DAEMON -- -d";
$conf[]="    log_end_msg \$?";
$conf[]="    ;;";
$conf[]="  reload|force-reload)";
$conf[]="    log_begin_msg \"Reloading \$NAME configuration files...\"";
$conf[]="    start-stop-daemon --stop --pidfile /var/run/backuppc/BackupPC.pid \\";
$conf[]="			    --signal 1";
$conf[]="    log_end_msg \$?";
$conf[]="    ;;";
$conf[]="  *)";
$conf[]="    log_success_msg \"Usage: /etc/init.d/\$NAME {start|stop|restart|reload}\"";
$conf[]="    exit 1";
$conf[]="    ;;";
$conf[]="esac";
$conf[]="";
$conf[]="exit 0";
@file_put_contents("/etc/init.d/backuppc",@implode("\n",$conf));	
@chmod("/etc/init.d/backuppc",0755);
}


function buildconfig(){
	$unix=new unix();
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("BackupPCGeneralConfig")));
	$configfile=locate_config_pl();
	$users=new usersMenus();
	
$wwuser=$unix->APACHE_GROUPWARE_ACCOUNT();
if(preg_match("#(.+?):#",$wwuser,$re)){$wwuser=$re[1];}	
	
if($array["Language"]==null){$array["Language"]="en";}
if($array["MaxBackups"]==null){$array["MaxBackups"]="4";}
if($array["MaxBackupPCNightlyJobs"]==null){$array["MaxBackupPCNightlyJobs"]="2";}
if($array["BackupPCNightlyPeriod"]==null){$array["BackupPCNightlyPeriod"]="1";}
if($array["MaxOldLogFiles"]==null){$array["MaxOldLogFiles"]="14";}

$ConfDir=dirname($configfile);
$InstallDir=locate_backuppc_share();
if(trim($array["TopDir"])==null){$array["TopDir"]=locate_backuppc_TopDir();}	


$hostname=$users->hostname;
echo "Starting......:  Backup-PC config dir: $ConfDir\n";
echo "Starting......:  Backup-PC www user..: $wwuser\n";
echo "Starting......:  Backup-PC Top dir...: {$array["TopDir"]}\n";

@mkdir($ConfDir,0755,true);
@mkdir($array["TopDir"],0750,true);
//system("/bin/chown -R backuppc:backuppc {$array["TopDir"]} &");
//system("/bin/chown -R backuppc:$wwuser $ConfDir &");

$par=$unix->find_program("par2");
if($par==null){$par="undef";}else{$par="'$par'";}

$conf[]="\$ENV{'PATH'} = '/bin:/usr/bin:/usr/local/bin/:user/sbin:/usr/local/sbin:/sbin';";
$conf[]="delete @ENV{'IFS', 'CDPATH', 'ENV', 'BASH_ENV'};";
$conf[]="\$Conf{ServerHost} = '127.0.0.1';";
$conf[]="chomp(\$Conf{ServerHost});";
$conf[]="\$Conf{ServerPort} = '9669';";
$conf[]="\$Conf{ServerMesgSecret} = '';";
$conf[]="\$Conf{MyPath} = '/bin';";
$conf[]="\$Conf{UmaskMode} = '23';";
$conf[]="\$Conf{Language} = '{$array["Language"]}';";
$conf[]="\$Conf{WakeupSchedule} = [";
$conf[]="  '1',";
$conf[]="  '2',";
$conf[]="  '3',";
$conf[]="  '4',";
$conf[]="  '5',";
$conf[]="  '6',";
$conf[]="  '7',";
$conf[]="  '8',";
$conf[]="  '9',";
$conf[]="  '10',";
$conf[]="  '11',";
$conf[]="  '12',";
$conf[]="  '13',";
$conf[]="  '14',";
$conf[]="  '15',";
$conf[]="  '16',";
$conf[]="  '17',";
$conf[]="  '18',";
$conf[]="  '19',";
$conf[]="  '20',";
$conf[]="  '21',";
$conf[]="  '22',";
$conf[]="  '23'";
$conf[]="];";
$conf[]="\$Conf{MaxBackups} = '{$array["MaxBackups"]}';";
$conf[]="\$Conf{MaxUserBackups} = '4';";
$conf[]="\$Conf{MaxPendingCmds} = '10';";
$conf[]="\$Conf{MaxBackupPCNightlyJobs} = '{$array["MaxBackupPCNightlyJobs"]}';";
$conf[]="\$Conf{BackupPCNightlyPeriod} = '{$array["BackupPCNightlyPeriod"]}';";
$conf[]="\$Conf{MaxOldLogFiles} = '{$array["MaxOldLogFiles"]}';";
$conf[]="\$Conf{DfPath} = '/bin/df';";
$conf[]="\$Conf{DfCmd} = '\$dfPath \$topDir';";
$conf[]="\$Conf{SplitPath} = '/usr/bin/split';";
$conf[]="\$Conf{ParPath}   = $par;";
$conf[]="\$Conf{CatPath}   = '/bin/cat';";
$conf[]="\$Conf{GzipPath}  = '/bin/gzip';";
$conf[]="\$Conf{Bzip2Path} = '/bin/bzip2';";
$conf[]="\$Conf{DfMaxUsagePct} = '95';";
$conf[]="\$Conf{TrashCleanSleepSec} = '300';";
$conf[]="\$Conf{DHCPAddressRanges} = [];";
$conf[]="\$Conf{BackupPCUser} = 'backuppc';";
$conf[]="\$Conf{TopDir}      = '{$array["TopDir"]}';";
$conf[]="\$Conf{ConfDir}     = '$ConfDir';";
$conf[]="\$Conf{LogDir}      = '';";
$conf[]="\$Conf{InstallDir}  = '$InstallDir';";
$conf[]="\$Conf{CgiDir}      = '$InstallDir/cgi-bin';";
$conf[]="\$Conf{BackupPCUserVerify} = '1';";
$conf[]="\$Conf{HardLinkMax} = '31999';";
$conf[]="\$Conf{PerlModuleLoad}     = undef;";
$conf[]="\$Conf{ServerInitdPath} = '';";
$conf[]="\$Conf{ServerInitdStartCmd} = '';";
$conf[]="\$Conf{FullPeriod} = '6.97';";
$conf[]="\$Conf{IncrPeriod} = '0.97';";
$conf[]="\$Conf{FullKeepCnt} =  [";
$conf[]="  '1'";
$conf[]="];";
$conf[]="\$Conf{FullKeepCntMin} ='1';";
$conf[]="\$Conf{FullAgeMax}     = '90';";
$conf[]="\$Conf{IncrKeepCnt} = '6';";
$conf[]="\$Conf{IncrKeepCntMin} ='1';";
$conf[]="\$Conf{IncrAgeMax}     = '30';";
$conf[]="\$Conf{IncrLevels} =  [";
$conf[]="  '1'";
$conf[]="];";
$conf[]="\$Conf{BackupsDisable} = '0';";
$conf[]="\$Conf{PartialAgeMax} = '3';";
$conf[]="\$Conf{IncrFill} = '0';";
$conf[]="\$Conf{RestoreInfoKeepCnt} = '10';";
$conf[]="\$Conf{ArchiveInfoKeepCnt} = '10';";
$conf[]="\$Conf{BackupFilesOnly} = undef;";
$conf[]="\$Conf{BackupFilesExclude} = undef;";
$conf[]="\$Conf{BlackoutBadPingLimit} = '3';";
$conf[]="\$Conf{BlackoutGoodCnt}      = '7';";
$conf[]="\$Conf{BlackoutPeriods} =[ { hourBegin => 7.0, hourEnd => 12.0, weekDays => [1, 2, 3, 4, 5], }];";
$conf[]="\$Conf{BackupZeroFilesIsFatal} = '1';";
$conf[]="\$Conf{XferMethod} = 'smb';";
$conf[]="\$Conf{XferLogLevel} = '1';";
$conf[]="\$Conf{ClientCharset} = '';";
$conf[]="\$Conf{ClientCharsetLegacy} = 'iso-8859-1';";
$conf[]="\$Conf{SmbShareName} = 'C\$';";
$conf[]="\$Conf{SmbShareUserName} = '';";
$conf[]="\$Conf{SmbSharePasswd} = '';";
$conf[]="\$Conf{SmbClientPath} = '/usr/bin/smbclient';";
$conf[]="\$Conf{SmbClientFullCmd} = '\$smbClientPath \\\\\\\\\$host\\\\\$shareName \$I_option -U \$userName -E -d 1 -c tarmode\\\\ full -Tc\$X_option - \$fileList';";
$conf[]="\$Conf{SmbClientIncrCmd} = '\$smbClientPath \\\\\\\\\$host\\\\\$shareName \$I_option -U \$userName -E -d 1 -c tarmode\\\\ full -TcN\$X_option \$timeStampFile - \$fileList';";
$conf[]="\$Conf{SmbClientRestoreCmd} = '\$smbClientPath \\\\\\\\\$host\\\\\$shareName \$I_option -U \$userName -E -d 1 -c tarmode\\\\ full -Tx -';";
$conf[]="";
$conf[]="\$Conf{TarShareName} = '/';";
$conf[]="\$Conf{TarClientCmd} = '\$sshPath -q -x -n -l root \$host env LC_ALL=C \$tarPath -c -v -f - -C \$shareName+ --totals';";
$conf[]="\$Conf{TarFullArgs} = '\$fileList+';";
$conf[]="\$Conf{TarIncrArgs} = '--newer=\$incrDate+ \$fileList+';";
$conf[]="\$Conf{TarClientRestoreCmd} = '\$sshPath -q -x -l root \$host env LC_ALL=C \$tarPath -x -p --numeric-owner --same-owner -v -f - -C \$shareName+';";
$conf[]="\$Conf{TarClientPath} = '/bin/tar';";
$conf[]="\$Conf{RsyncClientPath} = '/usr/bin/rsync';";
$conf[]="\$Conf{RsyncClientCmd} = '\$sshPath -q -x -l root \$host \$rsyncPath \$argList+';";
$conf[]="\$Conf{RsyncClientRestoreCmd} = '\$sshPath -q -x -l root \$host \$rsyncPath \$argList+';";
$conf[]="\$Conf{RsyncShareName} = '/';";
$conf[]="\$Conf{RsyncdClientPort} = '873';";
$conf[]="\$Conf{RsyncdUserName} = '';";
$conf[]="\$Conf{RsyncdPasswd} = '';";
$conf[]="\$Conf{RsyncdAuthRequired} = 1;";
$conf[]="\$Conf{RsyncCsumCacheVerifyProb} = '0.01';";
$conf[]="\$Conf{RsyncArgs} = [";
$conf[]="            '--numeric-ids',";
$conf[]="            '--perms',";
$conf[]="            '--owner',";
$conf[]="            '--group',";
$conf[]="            '-D',";
$conf[]="            '--links',";
$conf[]="            '--hard-links',";
$conf[]="            '--times',";
$conf[]="            '--block-size=2048',";
$conf[]="            '--recursive',";
$conf[]="];";
$conf[]="";
$conf[]="\$Conf{RsyncRestoreArgs} = [";
$conf[]="	    '--numeric-ids',";
$conf[]="	    '--perms',";
$conf[]="	    '--owner',";
$conf[]="	    '--group',";
$conf[]="	    '-D',";
$conf[]="	    '--links',";
$conf[]="            '--hard-links',";
$conf[]="	    '--times',";
$conf[]="	    '--block-size=2048',";
$conf[]="	    '--relative',";
$conf[]="	    '--ignore-times',";
$conf[]="	    '--recursive',";
$conf[]="];";
$conf[]="";

$conf[]="\$Conf{BackupPCdShareName} = '/';";
$conf[]="\$Conf{BackupPCdPath} = '';";
$conf[]="\$Conf{BackupPCdCmd} = '\$bpcdPath \$host \$shareName \$poolDir XXXX \$poolCompress \$topDir/pc/\$client/new';";
$conf[]="\$Conf{BackupPCdRestoreCmd} = '\$bpcdPath TODO';";
$conf[]="\$Conf{ArchiveDest} = '/tmp';";
$conf[]="\$Conf{ArchiveComp} = 'gzip';";
$conf[]="\$Conf{ArchivePar} = 0;";
$conf[]="\$Conf{ArchiveSplit} = 0;";
$conf[]="\$Conf{ArchiveClientCmd} = '\$Installdir/bin/BackupPC_archiveHost \$tarCreatePath \$splitpath \$parpath \$host \$backupnumber \$compression \$compext \$splitsize \$archiveloc \$parfile *';";
$conf[]="\$Conf{SshPath} = '/usr/bin/ssh' if -x '/usr/bin/ssh';";
$conf[]="\$Conf{NmbLookupPath} = '/usr/bin/nmblookup';";
$conf[]="\$Conf{NmbLookupCmd} = '\$nmbLookupPath -A \$host';";
$conf[]="\$Conf{NmbLookupFindHostCmd} = '\$nmbLookupPath \$host';";
$conf[]="\$Conf{FixedIPNetBiosNameCheck} = 0;";
$conf[]="\$Conf{PingPath} = '/bin/ping';";
$conf[]="\$Conf{PingCmd} = '\$pingPath -c 1 \$host';";
$conf[]="\$Conf{PingMaxMsec} = 20;";
$conf[]="\$Conf{CompressLevel} = 3;";
$conf[]="\$Conf{ClientTimeout} = 72000;";
$conf[]="\$Conf{MaxOldPerPCLogFiles} = 12;";
$conf[]="\$Conf{DumpPreUserCmd}     = undef;";
$conf[]="\$Conf{DumpPostUserCmd}    = undef;";
$conf[]="\$Conf{DumpPreShareCmd}    = undef;";
$conf[]="\$Conf{DumpPostShareCmd}   = undef;";
$conf[]="\$Conf{RestorePreUserCmd}  = undef;";
$conf[]="\$Conf{RestorePostUserCmd} = undef;";
$conf[]="\$Conf{ArchivePreUserCmd}  = undef;";
$conf[]="\$Conf{ArchivePostUserCmd} = undef;";
$conf[]="\$Conf{UserCmdCheckStatus} = 0;";
$conf[]="\$Conf{ClientNameAlias} = undef;";
$conf[]="\$Conf{SendmailPath} = '/usr/sbin/sendmail';";
$conf[]="\$Conf{EMailNotifyMinDays} = 2.5;";
$conf[]="\$Conf{EMailFromUserName} = 'backuppc';";
$conf[]="\$Conf{EMailAdminUserName} = 'backuppc';";
$conf[]="\$Conf{EMailUserDestDomain} = '';";
$conf[]="\$Conf{EMailNoBackupEverSubj} = undef;";
$conf[]="\$Conf{EMailNoBackupEverMesg} = undef;";
$conf[]="\$Conf{EMailNotifyOldBackupDays} = 7.0;";
$conf[]="\$Conf{EMailNoBackupRecentSubj} = undef;";
$conf[]="\$Conf{EMailNoBackupRecentMesg} = undef;";
$conf[]="\$Conf{EMailNotifyOldOutlookDays} = 5.0;";
$conf[]="\$Conf{EMailOutlookBackupSubj} = undef;";
$conf[]="\$Conf{EMailOutlookBackupMesg} = undef;";
$conf[]="\$Conf{EMailHeaders} ='MIME-Version: 1.0";
$conf[]="Content-Type: text/plain; charset=\"iso-8859-1\"";
$conf[]="';";
$conf[]="\$Conf{CgiAdminUserGroup} = 'backuppc';";
$conf[]="\$Conf{CgiAdminUsers}     = 'backuppc';";
$conf[]="\$Conf{CgiURL} = 'http://$hostname/backuppc/index.cgi';";


$conf[]="\$Conf{CgiUserHomePageCheck} = '';";
$conf[]="\$Conf{CgiUserUrlCreate}     = 'mailto:%s';";
$conf[]="\$Conf{CgiDateFormatMMDD} = 1;";
$conf[]="\$Conf{CgiNavBarAdminAllHosts} = 1;";
$conf[]="\$Conf{CgiSearchBoxEnable} = 1;";
$conf[]="\$Conf{CgiNavBarLinks} = undef;";
$conf[]="";
$conf[]="\$Conf{CgiStatusHilightColor} = {";
$conf[]="    Reason_backup_failed           => '#ffcccc',";
$conf[]="    Reason_backup_done             => '#ccffcc',";
$conf[]="    Reason_no_ping                 => '#ffff99',";
$conf[]="    Reason_backup_canceled_by_user => '#ff9900',";
$conf[]="    Status_backup_in_progress      => '#66cc99',";
$conf[]="    Disabled_OnlyManualBackups     => '#d1d1d1',   ";
$conf[]="    Disabled_AllBackupsDisabled    => '#d1d1d1',          ";
$conf[]="};";
$conf[]="\$Conf{CgiHeaders} = '<meta http-equiv=\"pragma\" content=\"no-cache\">';";
$conf[]="\$Conf{CgiImageDir} = '/usr/share/backuppc/image';";
$conf[]="\$Conf{CgiExt2ContentType} = {};";
$conf[]="\$Conf{CgiImageDirURL} = '/backuppc/image';";
$conf[]="\$Conf{CgiCSSFile} = 'BackupPC_stnd.css';";
$conf[]="\$Conf{CgiUserConfigEditEnable} = '1';";
$conf[]="\$Conf{CgiUserConfigEdit} = {";
$conf[]="  'EMailOutlookBackupSubj' => '1',";
$conf[]="  'ClientCharset' => '1',";
$conf[]="  'TarFullArgs' => '1',";
$conf[]="  'RsyncdPasswd' => '1',";
$conf[]="  'IncrKeepCnt' => '1',";
$conf[]="  'PartialAgeMax' => '1',";
$conf[]="  'FixedIPNetBiosNameCheck' => '1',";
$conf[]="  'SmbShareUserName' => '1',";
$conf[]="  'EMailFromUserName' => '1',";
$conf[]="  'ArchivePreUserCmd' => '0',";
$conf[]="  'PingCmd' => '0',";
$conf[]="  'FullAgeMax' => '1',";
$conf[]="  'PingMaxMsec' => '1',";
$conf[]="  'CompressLevel' => '1',";
$conf[]="  'DumpPreShareCmd' => '0',";
$conf[]="  'BackupFilesOnly' => '1',";
$conf[]="  'EMailNotifyOldBackupDays' => '1',";
$conf[]="  'EMailAdminUserName' => '1',";
$conf[]="  'RsyncCsumCacheVerifyProb' => '1',";
$conf[]="  'BlackoutPeriods' => '1',";
$conf[]="  'NmbLookupFindHostCmd' => '0',";
$conf[]="  'MaxOldPerPCLogFiles' => '1',";
$conf[]="  'TarClientCmd' => '0',";
$conf[]="  'EMailNotifyOldOutlookDays' => '1',";
$conf[]="  'SmbSharePasswd' => '1',";
$conf[]="  'SmbClientIncrCmd' => '0',";
$conf[]="  'FullKeepCntMin' => '1',";
$conf[]="  'RsyncArgs' => '1',";
$conf[]="  'ArchiveComp' => '1',";
$conf[]="  'TarIncrArgs' => '1',";
$conf[]="  'EMailUserDestDomain' => '1',";
$conf[]="  'TarClientPath' => '0',";
$conf[]="  'RsyncClientCmd' => '0',";
$conf[]="  'IncrFill' => '1',";
$conf[]="  'RestoreInfoKeepCnt' => '1',";
$conf[]="  'UserCmdCheckStatus' => '0',";
$conf[]="  'RsyncdClientPort' => '1',";
$conf[]="  'IncrAgeMax' => '1',";
$conf[]="  'RsyncdUserName' => '1',";
$conf[]="  'RsyncRestoreArgs' => '1',";
$conf[]="  'ClientCharsetLegacy' => '1',";
$conf[]="  'SmbClientFullCmd' => '0',";
$conf[]="  'ArchiveInfoKeepCnt' => '1',";
$conf[]="  'BackupZeroFilesIsFatal' => '1',";
$conf[]="  'EMailNoBackupRecentMesg' => '1',";
$conf[]="  'FullKeepCnt' => '1',";
$conf[]="  'TarShareName' => '1',";
$conf[]="  'EMailNoBackupEverSubj' => '1',";
$conf[]="  'TarClientRestoreCmd' => '0',";
$conf[]="  'EMailNoBackupRecentSubj' => '1',";
$conf[]="  'ArchivePar' => '1',";
$conf[]="  'XferLogLevel' => '1',";
$conf[]="  'ArchiveDest' => '1',";
$conf[]="  'ClientTimeout' => '1',";
$conf[]="  'EMailNotifyMinDays' => '1',";
$conf[]="  'RsyncdAuthRequired' => '1',";
$conf[]="  'SmbClientRestoreCmd' => '0',";
$conf[]="  'ClientNameAlias' => '1',";
$conf[]="  'DumpPostShareCmd' => '0',";
$conf[]="  'IncrLevels' => '1',";
$conf[]="  'EMailOutlookBackupMesg' => '1',";
$conf[]="  'BlackoutBadPingLimit' => '1',";
$conf[]="  'BackupFilesExclude' => '1',";
$conf[]="  'FullPeriod' => '1',";
$conf[]="  'ArchivePostUserCmd' => '0',";
$conf[]="  'RsyncClientRestoreCmd' => '0',";
$conf[]="  'IncrPeriod' => '1',";
$conf[]="  'RsyncShareName' => '1',";
$conf[]="  'RestorePostUserCmd' => '0',";
$conf[]="  'BlackoutGoodCnt' => '1',";
$conf[]="  'ArchiveClientCmd' => '0',";
$conf[]="  'ArchiveSplit' => '1',";
$conf[]="  'XferMethod' => '1',";
$conf[]="  'NmbLookupCmd' => '0',";
$conf[]="  'BackupsDisable' => '1',";
$conf[]="  'SmbShareName' => '1',";
$conf[]="  'RestorePreUserCmd' => '0',";
$conf[]="  'IncrKeepCntMin' => '1',";
$conf[]="  'EMailNoBackupEverMesg' => '1',";
$conf[]="  'EMailHeaders' => '1',";
$conf[]="  'DumpPreUserCmd' => '0',";
$conf[]="  'RsyncClientPath' => '0',";
$conf[]="  'DumpPostUserCmd' => '0'";
$conf[]="};";
$conf[]="";

if(is_file("/usr/share/artica-postfix/bin/install/backuppc/BackupPC_stnd.css")){
	@copy("/usr/share/artica-postfix/bin/install/backuppc/BackupPC_stnd.css","/usr/share/backuppc/image/BackupPC_stnd.css");
}

if(is_file("/usr/share/artica-postfix/bin/install/backuppc/logo.gif")){
	@copy("/usr/share/artica-postfix/bin/install/backuppc/logo.gif","/usr/share/backuppc/image/logo.gif");
}

@file_put_contents($configfile,@implode("\n",$conf));

}


function buid_affectation(){
	
	$ldap=new clladp();
	$pattern="(&(objectClass=ComputerAfectation)(cn=*))";
	$suffix=$ldap->suffix;
	$attr=array();
	$sr=@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if($hash["count"]==0){return;}
	for($i=0;$i<$hash["count"];$i++){
			$uid=$hash[$i]["uid"][0];
			$dn=$hash[$i]["dn"];
			echo $dn."\n";
			if(preg_match("#cn=hosts,cn=(.+?),ou=users#",$dn,$re)){
				$comp[$uid][]=$re[1];
			}
			
			
	}
	
	$f[]="host        dhcp    user    moreUsers     # <--- do not edit this line";
	
	if(!is_array($comp)){return;}
	while (list ($compuid, $users) = each ($comp) ){
		$cp=new computers($compuid);
		$computer_name=$cp->ComputerRealName;
		$VolatileIPAddress=$cp->VolatileIPAddress;
		$dhcpfixed=$cp->dhcpfixed;
		if($VolatileIPAddress==1){
			$DHCP=1;
			if($dhcpfixed==1){$DHCP=0;}
		}else{
			$DHCP=0;
		}
		
		$firstuser=$users[0];
		unset($users[0]);
		$f[]="$computer_name\t$DHCP\t$firstuser\t".@implode(",",$users);
		
		
		
	}
	
	file_put_contents("/etc/backuppc/hosts", @implode("\n",$f));
	
	
	
}

?>