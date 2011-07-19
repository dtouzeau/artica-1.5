unit setup_cyrus;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,postfix_class,zsystem,
  install_generic;

  type
  tcyrus_install=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
   source_folder,cmd:string;
   webserver_port:string;
   artica_admin:string;
   artica_password:string;
   ldap_suffix:string;
   mysql_server:string;
   mysql_admin:string;
   mysql_password:string;
   ldap_server:string;
   SYS:Tsystem;
   function LOCATE_DB_H():string;
   procedure remove();
   function CYRUS_DAEMON_BIN_PATH():string;
   procedure configuration();
   procedure PatchMakeFile(path:string;dbver:string);



public
      constructor Create();
      procedure Free;
      function install_cyrus():boolean;
      function GET_DB_VERSION(path:string=''):string;
      procedure PatchdebVer(path:string);

END;

implementation

constructor tcyrus_install.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);

end;
//#########################################################################################
procedure tcyrus_install.Free();
begin
  libs.Free;

end;

//#########################################################################################
function tcyrus_install.CYRUS_DAEMON_BIN_PATH():string;
begin
    if FileExists('/usr/sbin/cyrmaster') then exit('/usr/sbin/cyrmaster');
    if FIleExists('/usr/lib/cyrus-imapd/cyrus-master') then exit('/usr/lib/cyrus-imapd/cyrus-master');
    if FIleExists('/usr/lib/cyrus/bin/master') then exit('/usr/lib/cyrus/bin/master');
    if FileExists('/opt/artica/cyrus/bin/master') then exit('/opt/artica/cyrus/bin/master');
end;
//#############################################################################

function tcyrus_install.install_cyrus():boolean;
var
   CODE_NAME:string;
   cmd:string;
begin


   CODE_NAME:='APP_CYRUS_IMAP';
   install.INSTALL_PROGRESS(CODE_NAME,'{checking}');
   SetCurrentDir('/root');
   install.INSTALL_STATUS(CODE_NAME,10);
   install.INSTALL_STATUS(CODE_NAME,30);
   install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
   if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('cyrus-imapd');

   if not DirectoryExists(source_folder) then begin
       install.INSTALL_STATUS(CODE_NAME,110);
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       exit;
   end;

   writeln('remove old version');
   remove();
   forceDirectories('/root/cyrus-imapd-builder');
   fpsystem('/bin/cp -rf '+source_folder+'/* /root/cyrus-imapd-builder');

   SetCurrentDir('/root/cyrus-imapd-builder');
   install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
   install.INSTALL_STATUS(CODE_NAME,50);
fpsystem('DEFS="-DHAVE_CONFIG_H -DOPENSSL_NO_KRB5"');
fpsystem('export DEFS="-DHAVE_CONFIG_H -DOPENSSL_NO_KRB5"');
cmd:='./configure DEFS="-DHAVE_CONFIG_H -DOPENSSL_NO_KRB5"';
cmd:=cmd+' --prefix=/usr/share';
cmd:=cmd+' --exec-prefix=/usr';
cmd:=cmd+' --libexecdir=/usr/sbin';
cmd:=cmd+' --bindir=/usr/sbin --sbindir=/usr/sbin';
cmd:=cmd+' --includedir=/usr/include/cyrus';
cmd:=cmd+' --datadir=/usr/share/cyrus --sysconfdir=/etc';
cmd:=cmd+' --sharedstatedir=/usr/share/cyrus';
cmd:=cmd+' --localstatedir=/var/lib/cyrus';
cmd:=cmd+' --mandir=/usr/share/man';
cmd:=cmd+' --with-cyrus-prefix=/usr/lib/cyrus --with-lock=fcntl';
cmd:=cmd+' --with-perl=/usr/bin/perl';
cmd:=cmd+' --with-openssl=/usr';
cmd:=cmd+' --enable-murder';
cmd:=cmd+' --enable-nntp';
cmd:=cmd+' --disable-listext';
cmd:=cmd+' --with-sasl=/usr';
cmd:=cmd+' --with-cyrus-user=cyrus';
cmd:=cmd+' --with-cyrus-group=mail';
cmd:=cmd+' --with-com_err=/usr';
cmd:=cmd+' --with-pidfile=/var/run/cyrmaster.pid';
cmd:=cmd+' --with-syslogfacility=MAIL';
//cmd:=cmd+' --enable-gssapi';
//cmd:=cmd+' --with-gss_impl=heimdal';
cmd:=cmd+' --with-ucdsnmp=/usr';
cmd:=cmd+' --disable-krb5afspts';
cmd:=cmd+' --with-krb=/usr/include';
cmd:=cmd+' --enable-replication';
cmd:=cmd+' --without-krbdes --with-bdb-incdir=/usr/include';

//LIBSO_LIBS="-lpthread" LIBXSO_LIBS="-lpthread"

  writeln(cmd);
  fpsystem(cmd);
  PatchdebVer('/root/cyrus-imapd-builder');

  fpsystem('make depend');

  fpsystem('make all CFLAGS=-O LIBSO_LIBS="-lpthread" LIBXSO_LIBS="-lpthread"');

  install.INSTALL_STATUS(CODE_NAME,70);
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  fpsystem('make install');
  fpsystem('make');
  fpsystem('make install');
  SetCurrentDir('/root');
  if FileExists(CYRUS_DAEMON_BIN_PATH()) then begin
      install.INSTALL_STATUS(CODE_NAME,100);
      install.INSTALL_PROGRESS(CODE_NAME,'{success}');
      configuration();
      fpsystem('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
      fpsystem('/etc/init.d/artica-postfix restart imap');
      fpsystem('/bin/rm -rf /root/cyrus-imapd-builder');
      exit;
  end else begin
      install.INSTALL_STATUS(CODE_NAME,110);
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      fpsystem('/bin/rm -rf /root/cyrus-imapd-builder');

  end;



end;
//#########################################################################################
procedure tcyrus_install.configuration();
var
   l:TstringList;

begin
   l:=TstringList.Create;

if not FileExists('/etc/imapd.conf') then begin
   l.add('configdirectory:/var/lib/cyrus');
   l.add('defaultpartition:default');
   l.add('partition-default:/var/spool/cyrus/mail');
   l.add('partition-news:/var/spool/cyrus/news');
   l.add('newsspool:/var/spool/cyrus/news');
   l.add('altnamespace:no');
   l.add('unixhierarchysep:yes');
   l.add('lmtp_downcase_rcpt:yes');
   l.add('allowanonymouslogin:no');
   l.add('popminpoll:1');
   l.add('autocreatequota:0');
   l.add('umask:077');
   l.add('sieveusehomedir:false');
   l.add('sievedir:/var/spool/sieve');
   l.add('hashimapspool:true');
   l.add('allowplaintext:yes');
   l.add('sasl_pwcheck_method:saslauthd');
   l.add('sasl_auto_transition:no');
   l.add('lmtpsocket:/var/run/cyrus/socket/lmtp');
   l.add('idlemethod:poll');
   l.add('idlesocket:/var/run/cyrus/socket/idle');
   l.add('notifysocket:/var/run/cyrus/socket/notify');
   l.add('syslog_prefix:cyrus');
   l.add('srvtab:/var/lib/cyrus/srvtab');
   l.add('sieve_maxscriptsize:1024');
   l.add('virtdomains:no');
   l.add('sasl_mech_list:PLAIN LOGIN');
   l.add('admins:cyrus');
   l.add('username_tolower:1');
   l.add('ldap_uri:ldap://127.0.0.1:389');
   l.add('sasl_saslauthd_path:/var/run/saslauthd/mux');
   l.SaveToFile('/etc/imapd.conf');
end;
if not FileExists('/var/lib/cyrus') then forceDirectories('/var/lib/cyrus');
if not FileExists('/var/spool/cyrus/mail') then forceDirectories('/var/spool/cyrus/mail');
if not FileExists('/var/spool/cyrus/news') then forceDirectories('/var/spool/cyrus/news');
if not FileExists('/var/run/cyrus/socket') then forceDirectories('/var/run/cyrus/socket');

if not FileExists('/etc/cyrus.conf') then begin
l.add('#  Debian defaults for Cyrus IMAP server/cluster implementation');
l.add('# see cyrus.conf(5) for more information');
l.add('#');
l.add('# All the tcp services are tcpd-wrapped. see hosts_access(5)');
l.add('# $Id: cyrus.conf 567 2006-08-14 18:19:32Z sven $');
l.add('');
l.add('START {');
l.add('	# do not delete this entry!');
l.add('	recover		cmd="/usr/sbin/ctl_cyrusdb -r"');
l.add('  ');
l.add('	# this is only necessary if idlemethod is set to "idled" in imapd.conf');
l.add('	#idled		cmd="idled"');
l.add('');
l.add('	# this is useful on backend nodes of a Murder cluster');
l.add('	# it causes the backend to syncronize its mailbox list with');
l.add('	# the mupdate master upon startup');
l.add('	#mupdatepush   cmd="/usr/sbin/ctl_mboxlist -m"');
l.add('');
l.add('	# this is recommended if using duplicate delivery suppression');
l.add('	delprune	cmd="/usr/sbin/cyr_expire -E 3"');
l.add('	# this is recommended if caching TLS sessions');
l.add('	tlsprune	cmd="/usr/sbin/tls_prune"');
l.add('}');
l.add('');
l.add('# UNIX sockets start with a slash and are absolute paths');
l.add('# you can use a maxchild=# to limit the maximum number of forks of a service');
l.add('# you can use babysit=true and maxforkrate=# to keep tight tabs on the service');
l.add('# most services also accept -U (limit number of reuses) and -T (timeout)');
l.add('SERVICES {');
l.add('	# --- Normal cyrus spool, or Murder backends ---');
l.add('	# add or remove based on preferences');
l.add('	imap		cmd="imapd -U 30" listen="imap" prefork=0 maxchild=100');
l.add('	#imaps		cmd="imapd -s -U 30" listen="imaps" prefork=0 maxchild=100');
l.add('	pop3		cmd="pop3d -U 30" listen="pop3" prefork=0 maxchild=50');
l.add('	#pop3s		cmd="pop3d -s -U 30" listen="pop3s" prefork=0 maxchild=50');
l.add('	nntp		cmd="nntpd -U 30" listen="nntp" prefork=0 maxchild=100');
l.add('	#nntps		cmd="nntpd -s -U 30" listen="nntps" prefork=0 maxchild=100');
l.add('');
l.add('	# At least one form of LMTP is required for delivery');
l.add('	# (you must keep the Unix socket name in sync with imap.conf)');
l.add('	#lmtp		cmd="lmtpd" listen="localhost:lmtp" prefork=0 maxchild=20');
l.add('	lmtpunix	cmd="lmtpd" listen="/var/run/cyrus/socket/lmtp" prefork=0 maxchild=20');
l.add('	# ----------------------------------------------');
l.add('');
l.add('	# useful if you need to give users remote access to sieve');
l.add('	# by default, we limit this to localhost in Debian');
l.add('  	sieve		cmd="timsieved" listen="localhost:sieve" prefork=0 maxchild=100');
l.add('');
l.add('	# this one is needed for the notification services');
l.add('	notify		cmd="notifyd" listen="/var/run/cyrus/socket/notify" proto="udp" prefork=1');
l.add('');
l.add('	# --- Murder frontends -------------------------');
l.add('	# enable these and disable the matching services above, ');
l.add('	# except for sieve (which deals automatically with Murder)');
l.add('');
l.add('	# mupdate database service - must prefork at least 1');
l.add('	# (mupdate slaves)');
l.add('	#mupdate       cmd="mupdate" listen=3905 prefork=1');
l.add('	# (mupdate master, only one in the entire cluster)');
l.add('	#mupdate       cmd="mupdate -m" listen=3905 prefork=1');
l.add('');
l.add('	# proxies that will connect to the backends');
l.add('	#imap		cmd="proxyd" listen="imap" prefork=0 maxchild=100');
l.add('	#imaps		cmd="proxyd -s" listen="imaps" prefork=0 maxchild=100');
l.add('	#pop3		cmd="pop3proxyd" listen="pop3" prefork=0 maxchild=50');
l.add('	#pop3s		cmd="pop3proxyd -s" listen="pop3s" prefork=0 maxchild=50');
l.add('	#lmtp		cmd="lmtpproxyd" listen="lmtp" prefork=1 maxchild=20');
l.add('	# ----------------------------------------------');
l.add('}');
l.add('');
l.add('EVENTS {');
l.add('	# this is required');
l.add('	checkpoint	cmd="/usr/sbin/ctl_cyrusdb -c" period=30');
l.add('');
l.add('	# this is only necessary if using duplicate delivery suppression');
l.add('	delprune	cmd="/usr/sbin/cyr_expire -E 3" at=0401');
l.add('');
l.add('	# this is only necessary if caching TLS sessions');
l.add('	tlsprune	cmd="/usr/sbin/tls_prune" at=0401');
l.add('	');
l.add('	# indexing of mailboxs for server side fulltext searches');
l.add('');
l.add('	# reindex changed mailboxes (fulltext) approximately every other hour');
l.add('	#squatter_1	cmd="/usr/bin/nice -n 19 /usr/sbin/squatter -s" period=120');
l.add('');
l.add('	# reindex all mailboxes (fulltext) daily');
l.add('	#squatter_a	cmd="/usr/sbin/squatter" at=0517');
l.add('}');
l.add('');
l.saveToFile('/etc/cyrus.conf');
end;





end;

procedure tcyrus_install.remove();
var
   l:Tstringlist;
   i:integer;
begin
l:=Tstringlist.Create;
l.add('/usr/lib/cyrus/bin/idled');
l.add('/usr/lib/cyrus/bin/imapd');
l.add('/usr/sbin/squatter');
l.add('/usr/lib/cyrus/bin/fud');
l.add('/usr/lib/cyrus/bin/lmtpd');
l.add('/usr/lib/cyrus/bin/mbexamine');
l.add('/usr/lib/cyrus/bin/notifyd');
l.add('/usr/lib/cyrus/bin/quota') ;
l.add('/usr/lib/cyrus/bin/reconstruct');
l.add('/usr/lib/cyrus/bin/sievec');
l.add('/usr/lib/cyrus/bin/smmapd');
l.add('/usr/lib/cyrus/bin/timsieved');
l.add('/usr/lib/cyrus/cyrus-db-types.txt');
l.add('/usr/lib/cyrus/cyrus-hardwired-config.txt');
l.add('/usr/lib/cyrus/get-backtrace.gdb');
l.add('/usr/lib/cyrus/upgrade/convert-sieve.pl');
l.add('/usr/lib/cyrus/upgrade/cyrus-db-types.upgrading_from_1.5.txt');
l.add('/usr/lib/cyrus/upgrade/dohash');
l.add('/usr/lib/cyrus/upgrade/masssievec');
l.add('/usr/lib/cyrus/upgrade/rehash');
l.add('/usr/lib/cyrus/upgrade/translatesieve');
l.add('/usr/lib/cyrus/upgrade/undohash');
l.add('/usr/lib/cyrus/upgrade/upgradesieve');
l.add('/usr/sbin/arbitron');
l.add('/usr/sbin/arbitronsort');
l.add('/usr/sbin/chk_cyrus');
l.add('/usr/sbin/ctl_cyrusdb');
l.add('/usr/sbin/ctl_deliver');
l.add('/usr/sbin/ctl_mboxlist');
l.add('/usr/sbin/cvt_cyrusdb');
l.add('/usr/sbin/cyr_expire');
l.add('/usr/sbin/cyrdeliver');
l.add('/usr/sbin/cyrdump');
l.add('/usr/sbin/cyrmaster');
l.add('/usr/sbin/cyrquota');
l.add('/usr/sbin/cyrreconstruct');
l.add('/usr/sbin/cyrus-makedirs');
l.add('/usr/sbin/ipurge');
l.add('/usr/sbin/mbpath');
l.add('/usr/sbin/tls_prune');
l.add('/usr/lib/cyrus/bin/lmtpproxyd');
l.add('/usr/lib/cyrus/bin/mupdate');
l.add('/usr/lib/cyrus/bin/pop3proxyd');
l.add('/usr/lib/cyrus/bin/proxyd');
l.add('/usr/lib/cyrus/bin/pop3d');
l.add('/usr/lib/cyrus/bin/fetchnews');
l.add('/usr/lib/cyrus/bin/nntpd');
l.add('/usr/lib/cyrus/tools/mkimap');
l.add('/usr/lib/cyrus/tools/not-mkdep');
l.add('/usr/lib/cyrus/tools/arbitronsort.pl');
l.add('/usr/lib/cyrus/tools/translatesieve');
l.add('/usr/lib/cyrus/tools/mknewsgroups');
l.add('/usr/lib/cyrus/tools/convert-sieve.pl');
l.add('/usr/lib/cyrus/tools/masssievec');
l.add('/usr/lib/cyrus/tools/upgradesieve');
l.add('/usr/lib/cyrus/tools/mupdate-loadgen.pl');
l.add('/usr/lib/cyrus/tools/rehash');
l.add('/usr/lib/cyrus/tools/undohash');
l.add('/usr/lib/cyrus/tools/migrate-metadata');
l.add('/usr/lib/cyrus/tools/dohash');
l.add('/usr/lib/cyrus/bin/pop3proxyd');
l.add('/usr/lib/cyrus/bin/make_md5');
l.add('/usr/lib/cyrus/bin/fud');
l.add('/usr/lib/cyrus/bin/sync_reset');
l.add('/usr/lib/cyrus/bin/compile_sieve');
l.add('/usr/lib/cyrus/bin/ctl_deliver');
l.add('/usr/lib/cyrus/bin/sync_server');
l.add('/usr/lib/cyrus/bin/proxyd');
l.add('/usr/lib/cyrus/bin/mbexamine');
l.add('/usr/lib/cyrus/bin/lmtpproxyd');
l.add('/usr/lib/cyrus/bin/cyr_expire');
l.add('/usr/lib/cyrus/bin/cvt_cyrusdb');
l.add('/usr/lib/cyrus/bin/master');
l.add('/usr/lib/cyrus/bin/idled');
l.add('/usr/lib/cyrus/bin/mupdate');
l.add('/usr/lib/cyrus/bin/unexpunge');
l.add('/usr/lib/cyrus/bin/notifyd');
l.add('/usr/lib/cyrus/bin/pop3d');
l.add('/usr/lib/cyrus/bin/ctl_mboxlist');
l.add('/usr/lib/cyrus/bin/cyrdump');
l.add('/usr/lib/cyrus/bin/squatter');
l.add('/usr/lib/cyrus/bin/nntpd');
l.add('/usr/lib/cyrus/bin/timsieved');
l.add('/usr/lib/cyrus/bin/sievec');
l.add('/usr/lib/cyrus/bin/smmapd');
l.add('/usr/lib/cyrus/bin/quota');
l.add('/usr/lib/cyrus/bin/ipurge');
l.add('/usr/lib/cyrus/bin/lmtpd');
l.add('/usr/lib/cyrus/bin/cyr_dbtool');
l.add('/usr/lib/cyrus/bin/imapd');
l.add('/usr/lib/cyrus/bin/reconstruct');
l.add('/usr/lib/cyrus/bin/sync_client');
l.add('/usr/lib/cyrus/bin/arbitron');
l.add('/usr/lib/cyrus/bin/fetchnews');
l.add('/usr/lib/cyrus/bin/tls_prune');
l.add('/usr/lib/cyrus/bin/deliver');
l.add('/usr/lib/cyrus/bin/chk_cyrus');
l.add('/usr/lib/cyrus/bin/mbpath');
l.add('/usr/lib/cyrus/bin/ctl_cyrusdb');
l.add('/usr/bin/smtptest');
l.add('/usr/bin/nntptest');
l.add('/usr/bin/sivtest');
l.add('/usr/bin/lmtptest');
l.add('/usr/bin/imtest');
l.add('/usr/bin/pop3test');
l.add('/usr/bin/synctest');
l.add('/usr/bin/sieveshell');
l.add('/usr/bin/mupdatetest');
l.add('/usr/bin/cyradm');
l.add('/usr/bin/installsieve');
l.add('/sbin/rccyrus');
l.add('/usr/lib/cyrus-imapd/mbexamine');
l.add('/usr/lib/cyrus-imapd/mkimap');
l.add('/usr/lib/cyrus-imapd/sync_client');
l.add('/usr/lib/cyrus-imapd/chk_cyrus');
l.add('/usr/lib/cyrus-imapd/ptexpire');
l.add('/usr/lib/cyrus-imapd/arbitron');
l.add('/usr/lib/cyrus-imapd/mupdate');
l.add('/usr/lib/cyrus-imapd/tls_prune');
l.add('/usr/lib/cyrus-imapd/convert-sieve.pl');
l.add('/usr/lib/cyrus-imapd/sievec');
l.add('/usr/lib/cyrus-imapd/notifyd');
l.add('/usr/lib/cyrus-imapd/squatter');
l.add('/usr/lib/cyrus-imapd/arbitronsort.pl');
l.add('/usr/lib/cyrus-imapd/reconstruct');
l.add('/usr/lib/cyrus-imapd/masssievec');
l.add('/usr/lib/cyrus-imapd/undohash');
l.add('/usr/lib/cyrus-imapd/timsieved');
l.add('/usr/lib/cyrus-imapd/cyrfetchnews');
l.add('/usr/lib/cyrus-imapd/ptloader');
l.add('/usr/lib/cyrus-imapd/nntpd');
l.add('/usr/lib/cyrus-imapd/ptdump');
l.add('/usr/lib/cyrus-imapd/deliver');
l.add('/usr/lib/cyrus-imapd/cyrus-master');
l.add('/usr/lib/cyrus-imapd/pop3d');
l.add('/usr/lib/cyrus-imapd/unexpunge');
l.add('/usr/lib/cyrus-imapd/sync_reset');
l.add('/usr/lib/cyrus-imapd/rpm_set_permissions');
l.add('/usr/lib/cyrus-imapd/cyr_expire');
l.add('/usr/lib/cyrus-imapd/smmapd');
l.add('/usr/lib/cyrus-imapd/rehash');
l.add('/usr/lib/cyrus-imapd/idled');
l.add('/usr/lib/cyrus-imapd/mknewsgroups');
l.add('/usr/lib/cyrus-imapd/ctl_mboxlist');
l.add('/usr/lib/cyrus-imapd/lmtpd');
l.add('/usr/lib/cyrus-imapd/mbpath');
l.add('/usr/lib/cyrus-imapd/fud');
l.add('/usr/lib/cyrus-imapd/upd_groupcache');
l.add('/usr/lib/cyrus-imapd/translatesieve');
l.add('/usr/lib/cyrus-imapd/ipurge');
l.add('/usr/lib/cyrus-imapd/lmtpproxyd');
l.add('/usr/lib/cyrus-imapd/ctl_cyrusdb');
l.add('/usr/lib/cyrus-imapd/proxyd');
l.add('/usr/lib/cyrus-imapd/mupdate-loadgen.pl');
l.add('/usr/lib/cyrus-imapd/quota');
l.add('/usr/lib/cyrus-imapd/compile_sieve');
l.add('/usr/lib/cyrus-imapd/migrate-metadata');
l.add('/usr/lib/cyrus-imapd/ctl_deliver');
l.add('/usr/lib/cyrus-imapd/make_md5');
l.add('/usr/lib/cyrus-imapd/sync_server');
l.add('/usr/lib/cyrus-imapd/upgradesieve');
l.add('/usr/lib/cyrus-imapd/cvt_cyrusdb_all');
l.add('/usr/lib/cyrus-imapd/dohash');
l.add('/usr/lib/cyrus-imapd/cvt_cyrusdb');
l.add('/usr/lib/cyrus-imapd/imapd');
l.add('/usr/lib/cyrus-imapd/cyrdump');

for i:=0 to l.Count-1 do begin
    if FIleExists(l.Strings[i]) then begin
       writeln('Remove '+l.Strings[i]);
       fpsystem('/bin/rm '+l.Strings[i]);
    end;
end;

end;

function tcyrus_install.GET_DB_VERSION(path:string):string;
var
   RegExpr:TRegExpr;
   l:Tstringlist;
   i:integer;
   DB_VERSION_MAJOR:string;
   DB_VERSION_MINOR:string;
   DB_VERSION_PATCH:string;
begin
   if length(path)=0 then path:=LOCATE_DB_H();
   if not FileExists(path) then begin
      writeln('unable to stat db.h');
      exit;
   end;

   l:=Tstringlist.Create;
   l.LoadFromFile(path);
   RegExpr:=TRegExpr.Create;
   for i:=0 to l.Count-1 do begin
       RegExpr.Expression:='include <(.+?)\/db\.h>';
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=GET_DB_VERSION(extractFilePath(path)+RegExpr.Match[1]+'/db.h');
          break;
       end;

      RegExpr.Expression:='DB_VERSION_MAJOR\s+([0-9]+)';
      if RegExpr.Exec(l.Strings[i]) then begin
         DB_VERSION_MAJOR:=RegExpr.Match[1];
         continue;
      end;
      RegExpr.Expression:='DB_VERSION_MINOR\s+([0-9]+)';
      if RegExpr.Exec(l.Strings[i]) then begin
         DB_VERSION_MINOR:=RegExpr.Match[1];
         continue;
      end;

      RegExpr.Expression:='DB_VERSION_PATCH\s+([0-9]+)';
       if RegExpr.Exec(l.Strings[i]) then begin
         DB_VERSION_PATCH:=RegExpr.Match[1];
         continue;
      end;

      if length(DB_VERSION_MAJOR)>0 then begin
         if  length(DB_VERSION_MINOR)>0 then begin
           if  length(DB_VERSION_PATCH)>0 then begin
               break;
           end;
         end;
      end;
  end;

   if length(DB_VERSION_MAJOR)=0 then begin
         RegExpr.free;
         l.free;
         exit;
   end else begin
       result:=DB_VERSION_MAJOR;
   end;

   if length(DB_VERSION_MINOR)=0 then begin
         RegExpr.free;
         l.free;
         exit;
   end else begin
      result:=DB_VERSION_MAJOR+'.'+DB_VERSION_MINOR;
   end;

  { if length(DB_VERSION_PATCH)=0 then begin
         RegExpr.free;
         l.free;
         exit;
   end else begin
     result:=DB_VERSION_MAJOR+'.'+DB_VERSION_MINOR+'.'+DB_VERSION_PATCH;
   end;  }

 RegExpr.free;
 l.free;

// #define	DB_VERSION_MAJOR	4
//#define	DB_VERSION_MINOR	6
//#define	DB_VERSION_PATCH	21


end;


function tcyrus_install.LOCATE_DB_H():string;
begin
  if FileExists('/usr/include/db.h') then exit('/usr/include/db.h');
end;


procedure tcyrus_install.PatchdebVer(path:string);
var
   l:Tstringlist;
   i:integer;
   versiondb:string;
begin
       versiondb:=GET_DB_VERSION();
       if length(versiondb)=0 then begin
          writeln('Cannot stat cyrus version');
          exit;
       end;
l:=Tstringlist.Create;
L.Add('imap/Makefile');
L.Add('imtest/Makefile');
L.Add('lib/Makefile');
L.Add('imap/Makefile');
L.Add('master/Makefile');
L.Add('netnews/Makefile');
L.Add('notifyd/Makefile');
L.Add('perl/Makefile');
L.Add('perl/sieve/Makefile');
L.Add('perl/sieve/lib/Makefile');
L.Add('sieve/Makefile');
L.Add('timsieved/Makefile');

  for i:=0 to l.Count-1 do begin
     if not FileExists(path+'/'+ l.Strings[i]) then begin
        writeln('Unable to stat '+path+'/'+ l.Strings[i]);
        continue;
     end;
     PatchMakeFile(path+'/'+ l.Strings[i],versiondb);
  end;

end;


procedure tcyrus_install.PatchMakeFile(path:string;dbver:string);
var
   RegExpr:TRegExpr;
   l:Tstringlist;
   i:integer;
   ldbsource:string;
   ldbsourceTo:string;
begin

l:=Tstringlist.Create;
l.LoadFromFile(path);
writeln('Loading ',path,' ',l.Count,' lines');
RegExpr:=tRegExpr.Create;
for i:=0 to l.Count-1 do begin
     RegExpr.Expression:='BDB_LIB=.*?-ldb-([0-9\.a-z]+)';
     if RegExpr.Exec(l.Strings[i]) then begin
        RegExpr.Expression:='BDB_INC';
        if not RegExpr.Exec(l.Strings[i]) then begin
           writeln('1)Patching line ',i,' ',l.Strings[i],' = ', dbver);
           l.Strings[i]:='BDB_LIB= -ldb-'+dbver;
        end;
         continue;
     end;
     RegExpr.Expression:='-ldb-(.+?)\s+';
     if RegExpr.Exec(l.Strings[i]) then begin
           ldbsource:='-ldb-'+RegExpr.Match[1];
           ldbsourceTo:='-ldb-'+dbver;
           writeln('2)Patching line ',i,' ',ldbsource,' = ', ldbsourceTo);
           l.Strings[i]:=AnsiReplaceText(l.Strings[i],ldbsource,ldbsourceTo);
           continue;
     end;

end;
writeln('saving ',path);
l.SaveToFile(path);
l.free;
RegExpr.free;


end;



end.
