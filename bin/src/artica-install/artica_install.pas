program artica_install;

{$mode objfpc}{$H+}

uses
Classes, SysUtils, class_install, zsystem, global_conf, postfix_addons,
process_infos, BaseUnix, unix, RegExpr in 'RegExpr.pas', dos, clamav, spamass,
logs, pureftpd, openldap, ntpd, samba, spfmilter, mimedefang, cyrus, squid,
stunnel4, dkimfilter, postfix_class, mailgraph_daemon, miltergreylist, lighttpd,
artica_tcp, roundcube, dansguardian, strutils, kav4samba, awstats, artica_menus,
debian_class, bind9, fdm, collectd, apache_artica,
kas3 in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kas3.pas',
dnsmasq in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/dnsmasq.pas',
cups,
amavisd_milter in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/amavisd_milter.pas',
artica_cron, kretranslator, isoqlog, obm, openvpn, jcheckmail, mailmanctl,
imapsync, dhcp_server, samba4, obm2, xapian, opengoo, dstat, rsync, tcpip,
nfsserver, lvm, assp, pdns, gluster, kav4proxy, zabbix, hamachi, kavmilter,
kavm4mls, postfilter, fetchmail, vmwaretools, zarafa_server, monit, squidguard,
wifi, fail2ban, mysql_daemon, saslauthd, xfce, emailrelay, mldonkey,
policyd_weight, backuppc, kav4fs, ocsi, ocsagent, sshd, auditd, squidguard_page,
dkfilter, ufdbguardd, dkimmilter, dropbox, articapolicy, virtualbox, tftpd,
crossroads, articastatus, articaexecutor, articabackground, pptpd, apt_mirror,
ddclient, cluebringer, apachesrc, toolsversions, sabnzbdplus, fusermount,
vnstat, munin,greyhole,autofs, iscsitarget, snort,greensql, amanda;

var
install:Tclass_install;
GLOBAL_INI:myconf;
deb:TDebian;
PRC:Tprocessinfos;
POSTFIX_ADDON:Tpostfix_addon;
zMENUS:Tmenus;
REGEX:TRegExpr;
s,tmpstr:string;
list:TstringList;
i:integer;
debug:boolean;

SYS:Tsystem;
zClam:Tclamav;
zSpamass:Tspamass;
zldap:tOpenldap;
zlogs:Tlogs;
zntpd:tntpd;

ccyrus:Tcyrus;
zmimedefang:Tmimedefang;
zsquid:Tsquid;

zdkim:tdkim;
zpostfix:Tpostfix;
mailgraph:tMailgraphClass;
zkav4Samba:Tkav4Samba;
zlighttpd:Tlighttpd;
tcp:ttcp;
zdansguardian:TDansguardian;
mypid:string;
zawstats:tawstats;
zbind9:tbind9;
zfdm:Tfdm;
zkas3:tkas3;
zdnsmasq:tdnsmasq;
zcollectd:tcollectd;
amavis:tamavis;
cron:tcron;
retrans:tkretranslator;
round:troundcube;
zisoqlog:tisoqlog;
zmailman:tmailman;
zobm:tobm;
zimapsync:timapsync;
dhcp3:tdhcp3;
samba3:tsamba;
zopenvpn:topenvpn;
zcups:tcups;
zobm2:tobm2;
zxapian:txapian;
xopengoo:topengoo;
stunnel:tstunnel;
zdstat:tdstat;
zrsync:trsync;
tcp_IP:ttcpip;
zapache_artica:tapache_artica;
znfs:tnfs;
zlvm:tlvm;
zassp:tassp;
zpdns:tpdns;
zgluster:tgluster;
zkav4proxy:tkav4proxy;
FileData:TstringList;
zhamachi:thamachi;
zkavmilter:tkavmilter;
zkavm4mls:tkavm4mls;
milter_greylist:tmilter_greylist;
zpureftpd:Tpureftpd;
zpostfilter:tpostfilter;
zfetchmail:tfetchmail;
ztvmtools:tvmtools;
zZarafa:tzarafa_server;
zmonit:tmonit;
zsquidguard:tsquidguard;
zwifi:twifi;
zfail2ban:tfail2ban;
zmysql:tmysql_daemon;
zsaslauthd:tsaslauthd;
zxfce:txfce;
zmldonkey:tmldonkey;
zpolicyd_weight:tpolicyd_weight;
zbackuppc:tbackuppc;
zkav4fs:tkav4fs;
zocsi:tocsi;
zocsagent:tocsagent;
zsshd:tsshd;
zauditd:tauditd;
zdkfilter:tdkfilter;
zufdbguardd:tufdbguardd;
zdkimmilter:tdkimmilter;
zdropbox:tdropbox;
zarticapolicy:tarticapolicy;
zvirtualbox:tvirtualbox;
ztftpd:ttftpd;
zcrossroads:tcrossroads;
zarticastatus:tarticastatus;
zarticaexecutor:tarticaexecutor;
zpptpd:tpptpd;
zapt_mirror:tapt_mirror;
zddclient:tddclient;
zcluebringer:tcluebringer;
ztoolsversions:ttoolsversions;
zsabnzbdplus:tsabnzbdplus;
zstunnel4:tstunnel;
zvnstat:tvnstat;
zmunin:tmunin;
zgreyhole:tgreyhole;
zautofs:tautofs;
ztapachesrc:tapachesrc;
ztiscsitarget:tiscsitarget;
zgreensql:tgreensql;
begin
debug:=false;
if ParamStr(1)='--verbose2' then debug:=true;
if ParamStr(2)='--verbose2' then debug:=true;
if ParamStr(3)='--verbose2' then debug:=true;
if ParamStr(4)='--verbose2' then debug:=true;
if ParamStr(5)='--verbose2' then debug:=true;

if debug then writeln('binary start -> loading classes');
if debug then writeln('binary start -> loading class SYS');
SYS:=Tsystem.Create;
if debug then writeln('binary start -> loading class zlogs');
zlogs:=Tlogs.create;
if debug then writeln('binary start -> loading class Zclam');
Zclam:=TClamav.Create;
if debug then writeln('binary start -> loading class zSpamass');
zSpamass:=Tspamass.Create(SYS);
if debug then writeln('binary start -> loading class zldap');
zldap:=Topenldap.Create;
if debug then writeln('binary start -> loading class zntpd');
zntpd:=tntpd.Create;
if debug then writeln('binary start -> loading class zmimedefang');
zmimedefang:=Tmimedefang.Create(SYS);
if debug then writeln('binary start -> loading class ccyrus');
ccyrus:=Tcyrus.Create(SYS);
if debug then writeln('binary start -> loading class zsquid');
zsquid:=Tsquid.Create;
if debug then writeln('binary start -> loading class zdkim');
zdkim:=tdkim.Create(SYS);
if debug then writeln('binary start -> loading class zpostfix');
zpostfix:=Tpostfix.Create(SYS);
if debug then writeln('binary start -> loading class mailgraph');
mailgraph:=tMailgraphClass.Create(SYS);
if debug then writeln('binary start -> loading class zlighttpd');
zlighttpd:=Tlighttpd.Create(SYS);
if debug then writeln('binary start -> loading class tcp');
tcp:=ttcp.Create;
if debug then writeln('binary start -> loading class deb');
deb:=Tdebian.Create;
if debug then writeln('binary start -> loading class zdansguardian');
zdansguardian:=TDansGuardian.Create(SYS);
if debug then writeln('binary start -> loading class REGEX');
REGEX:=TRegExpr.Create;
if debug then writeln('binary start -> loading class install');
install:=Tclass_install.Create();
if debug then writeln('binary start -> loading class zawstats');
zawstats:=tawstats.Create(SYS);
if debug then writeln('binary start -> loading class zbind9');
zbind9:=tbind9.Create(SYS);
if debug then writeln('binary start -> loading class zfdm');
zfdm:=tfdm.Create(SYS);
if debug then writeln('binary start -> loading class zdnsmasq');
zdnsmasq:=tdnsmasq.Create(SYS);

if debug then writeln('binary start -> loading classes finish');



s:='';
SetCurrentDir('/tmp');
zlogs.commandlog();

if ParamStr(1)='--mailflt3' then  begin
   SYS.verbosed:=true;
   SYS.AddUserToGroup('mailflt3','mailflt3','','');
   halt(0);
end;

if ParamStr(1)='--sys-mem' then  begin
   SYS.verbosed:=true;
   writeln(SYS.MEM_TOTAL_INSTALLEE());
   halt(0);
end;
if ParamStr(1)='--greensql-reload' then  begin
   zgreensql:=tgreensql.Create(SYS);
   zgreensql.RELOAD();
   halt(0);
end;
if ParamStr(1)='--ip-accounting' then  begin
   sys.verbosed:=true;
   IF SYS.IPTABLES_ACCOUNTING_EXISTS() then writeln('Installed...');
   halt(0);
end;

if ParamStr(1)='--change-initd' then  begin
   sys.verbosed:=true;
   GLOBAL_INI:=myconf.Create();
   install:=Tclass_install.Create;
   writeln('Modify init.d [artica]');
   install.install_init_d();

   writeln('Modify init.d [postfix]');
   zpostfix:=Tpostfix.Create(SYS);
   zpostfix.POSTFIX_INI_TD();

   writeln('Modify init.d [saslauthd]');
   zsaslauthd:=tsaslauthd.Create(SYS);
   zsaslauthd.CHANGE_INITD();

   writeln('Modify init.d [spamassassin]');
   zSpamass:=Tspamass.Create(SYS);
   zSpamass.CHANGE_INITD_MILTER();

   writeln('Modify init.d [boa]');
   GLOBAL_INI.BOA_TESTS_INIT_D();

   halt(0);
end;






if Paramstr(1)='--samba-add-computer' then begin
   zlogs.Debuglogs('ADDING COMPUTER '+Paramstr(2));
   zlogs.Debuglogs('ADDING COMPUTER '+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.add-samba-computer.php '+ Paramstr(2));
   zlogs.Syslogs(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.add-samba-computer.php '+ Paramstr(2));
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.add-samba-computer.php '+ Paramstr(2));
   sleep(1000);
   zlogs.Debuglogs('ADDING COMPUTER '+Paramstr(2)+' done');
   halt(1);
end;


if install.IsRoot()=false then begin
   writeln('This program must run as root');
   writeln('You need to be root to execute this program...');
   halt(0);
end;

if ParamStr(1)='--lighttpd-reload' then begin
   zlighttpd.LIGHTTPD_RELOAD();
   halt(0);
end;

if ParamStr(1)='--clamd-reload' then begin
   zClam:=Tclamav.Create;
   zClam.CLAMD_RELOAD();
   halt(0);
end;
if ParamStr(1)='--cyrus-db_config' then begin
   ccyrus.DB_CONFIG();
   halt(0);
end;
if ParamStr(1)='--apache-status' then begin
   ztapachesrc:=tapachesrc.Create(SYS);
   writeln('Site enabled...........: ',ztapachesrc.APACHE_DIR_SITES_ENABLED());
   writeln('Run as user............: ',ztapachesrc.APACHE_SRC_ACCOUNT());
   writeln('mod_proxy ?............: ',SYS.APACHE_IS_MOD_PROXY());



   halt(0);
end;


if ParamStr(1)='--greyhole-status' then begin
   zgreyhole:=tgreyhole.Create(SYS);
   writeln('version...........:',zgreyhole.VERSION());
   halt(0);
end;

if ParamStr(1)='--change-mysqldir' then begin
    zmysql:=tmysql_daemon.Create(SYS);
   zmysql.CHANGE_MYSQL_ROOT();
   halt(0);
end;





if ParamStr(1)='--artica-status-reload' then begin
   zarticastatus:=tarticastatus.Create(SYS);
   zarticastatus.RELOAD();
   halt(0);
end;

if ParamStr(1)='--artica-executor-reload' then begin
   zarticaexecutor:=tarticaexecutor.Create(SYS);
   zarticaexecutor.RELOAD();
   halt(0);
end;


if ParamStr(1)='--mem' then begin
   writeln(SYS.MEM_TOTAL_INSTALLEE());
   halt(0);
end;

if ParamStr(1)='--samba' then begin
   samba3:=tsamba.Create;
   writeln('Samba binary.............:',samba3.SMBD_PATH());
   writeln('Samba Main config path...:',samba3.smbconf_path());
   writeln('Samba version............:',samba3.SAMBA_VERSION());
   writeln('Samba vfs plugins dir....:',samba3.vfs_path());
   writeln('');
   writeln('Other command lines:');
   writeln('--nsswitch...............: Change nsswitch.conf');
   writeln('--smbd-pid...............: Geto pid of smbd');
   halt(0);
end;



if ParamStr(1)='--nsswitch' then begin
   samba3:=tsamba.Create;
   samba3.nsswitch_conf();
   samba3.pam_ldap_conf();
   halt(0);
end;

if ParamStr(1)='--smbd-pid' then begin
   samba3:=tsamba.Create;
   writeln(samba3.SMBD_PID());
   halt(0);
end;


if ParamStr(1)='--amavisd-check-perl' then begin
   amavis:=tamavis.Create(SYS);
   amavis.CHECK_MODULES();
   halt(0);
end;






mypid:=IntToStr(fpgetpid);


sys.cpulimit();

if ParamCount>0 then
begin
for i:=1 to ParamCount do
begin
s:=s  + ' ' +ParamStr(i);

end;
s:=trim(s);
end;


if ParamStr(1)='--change-ldap-settings' then
begin
zldap:=tOpenldap.Create;
zldap.ChangeSettings(ParamStr(2),ParamStr(3),ParamStr(5),ParamStr(6),ParamStr(4),ParamStr(7));
halt(0);
end;

if ParamStr(1)='--disk-change-label' then begin
   SYS.usb_change_disk_label(ParamStr(2),ParamStr(3));
   halt(0);
end;


 // zlogs.Debuglogs('************************************************************');
 // zlogs.Debuglogs(' "'+s+'"');
SYS.isoverloaded();
 // zlogs.Debuglogs('************************************************************');
debug:=false;
debug:=SYS.COMMANDLINE_PARAMETERS('--debug');

if ParamStr(1)='--read' then
begin
writeln(zlogs.ReadFromFile(ParamStr(2)));
halt(0);
end;

if ParamStr(1)='--local-dns' then
begin
GLOBAL_INI:=myconf.Create();
writeln(GLOBAL_INI.SYSTEM_GET_LOCAL_DNS());
halt(0);
end;

if ParamStr(1)='--dirlists' then
begin
GLOBAL_INI:=myconf.Create();
GLOBAL_INI.LISTDIRS_RECURSIVE(paramStr(2));
halt(0);
end;


if ParamStr(1)='--phpmyadmin' then
begin
GLOBAL_INI:=myconf.Create();
writeln('Version........:',GLOBAL_INI.PHPMYADMIN_VERSION());
halt(0);
end;

if ParamStr(1)='--change-hostname' then
begin
GLOBAL_INI:=myconf.Create();
GLOBAL_INI.SYSTEM_NETWORKS_SET_HOSTNAME(ParamStr(2));
halt(0);
end;

if ParamStr(1)='--hostname' then begin
   GLOBAL_INI:=myconf.Create();
   GLOBAL_INI.SYSTEM_FQDN();
   halt(0);
end;

if ParamStr(1)='--awstats-version' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.AWSTATS_VERSION());
   exit;
end;

if ParamStr(1)='--iptaccount-version' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.IPTACCOUNT_VERSION());
   exit;
end;
if ParamStr(1)='--pdns-version' then begin
   zpdns:=tpdns.Create(SYS);
   writeln(zpdns.VERSION());
   exit;
end;
if ParamStr(1)='--poweradmin-version' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.POWERADMIN_VERSION());
   exit;
end;

if ParamStr(1)='--vboxguest-version' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.VBOXGUEST_VERSION());
   exit;
end;
if ParamStr(1)='--greensql-version' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.GREENSQL_VERSION());
   exit;
end;

if ParamStr(1)='--install-status' then begin
   GLOBAL_INI:=myconf.Create();
   GLOBAL_INI.INSTALL_STATUS(ParamStr(2));
   halt(0);
end;


if ParamStr(1)='--reload-artica-policy' then begin
   zarticapolicy:=tarticapolicy.Create(SYS);
   zarticapolicy.RELOAD();
   zarticapolicy.free;
   halt(0);
end;


if ParamStr(1)='--nicstatus' then begin
       GLOBAL_INI:=myconf.Create();
       tmpstr:=GLOBAL_INI.SYSTEM_GET_LOCAL_IP(ParamStr(2));
       tmpstr:=tmpstr+';'+GLOBAL_INI.SYSTEM_GET_LOCAL_MAC(ParamStr(2));
       tmpstr:=tmpstr+';'+GLOBAL_INI.SYSTEM_GET_LOCAL_MASK(ParamStr(2));
       tmpstr:=tmpstr+';'+GLOBAL_INI.SYSTEM_GET_LOCAL_BROADCAST(ParamStr(2));
       tmpstr:=tmpstr+';'+GLOBAL_INI.SYSTEM_GET_LOCAL_GATEWAY(ParamStr(2));
       if GLOBAL_INI.IsWireless(ParamStr(2)) then tmpstr:=tmpstr+';yes' else tmpstr:=tmpstr+';no';
       if GLOBAL_INI.IsIfaceDown(ParamStr(2)) then tmpstr:=tmpstr+';yes' else tmpstr:=tmpstr+';no';
       writeln(tmpstr);
       halt(0);
end;

if ParamStr(1)='--nicinfos' then begin
       GLOBAL_INI:=myconf.Create();
       writeln(GLOBAL_INI.SYSTEM_NETWORK_INFO_NIC(ParamStr(2)));
       halt(0);
end;


if ParamStr(1)='--format-b-part' then
begin
writeln(SYS.disk_build_unique_partition(paramStr(2),paramStr(3)));
halt(0);
end;


if ParamStr(1)='--php-ini' then
begin
zlighttpd:=Tlighttpd.Create(SYS);
zlighttpd.LIGHTTPD_ADD_INCLUDE_PATH();
halt(0);
end;
if ParamStr(1)='--squid-version-bin' then
begin
zsquid:=Tsquid.Create;
zsquidguard:=Tsquidguard.Create(SYS);
      //ancienne 302000000000
      //nouvelle 310300000000
writeln('String version of SQUID.....: ',zsquid.SQUID_VERSION());
writeln('Binary version of SQUID.....: ',zsquid.SQUID_BIN_VERSION(zsquid.SQUID_VERSION()));
writeln('String version of SquidGuard: ',zsquidguard.VERSION());
writeln('Binary version of SquidGuard: ',zsquidguard.VERSIONNUM());
halt(0);
end;

if ParamStr(1)='--export-version' then
begin
GLOBAL_INI:=myconf.Create();
if ParamStr(2)='squid' then
begin
zsquid:=Tsquid.Create;
writeln(zsquid.SQUID_VERSION());
halt(0);
end;




if ParamStr(2)='artica' then
begin
writeln(trim(zlogs.ReadFromFile('/usr/share/artica-postfix/VERSION')));
halt(0);
end;

if ParamStr(2)='c-icap' then
begin
zdansguardian:=Tdansguardian.Create(SYS);
writeln(zdansguardian.C_ICAP_VERSION());
halt(0);
end;

if ParamStr(2)='dansguardian' then
begin
zdansguardian:=Tdansguardian.Create(SYS);
writeln(zdansguardian.DANSGUARDIAN_VERSION());
halt(0);
end;
if ParamStr(2)='kav4proxy' then
begin
zkav4proxy:=tkav4proxy.Create(SYS);
writeln(zkav4proxy.VERSION());
halt(0);
end;

if ParamStr(2)='wpa_suppliant' then
begin
zwifi:=twifi.Create(SYS);
writeln(zwifi.WPA_SUPPLIANT_VERSION());
halt(0);
end;

if ParamStr(2)='hostapd' then
begin
zwifi:=twifi.Create(SYS);
writeln(zwifi.HOSTAPD_VERSION());
halt(0);
end;

if ParamStr(2)='fetchmail' then
begin
zfetchmail:=tfetchmail.Create(SYS);
writeln(zfetchmail.FETCHMAIL_VERSION());
halt(0);
end;

if ParamStr(2)='milter-greylist' then
begin
milter_greylist:=tmilter_greylist.Create(SYS);
writeln(milter_greylist.VERSION());
halt(0);
end;

if ParamStr(2)='milter-greylist-pid' then
begin
milter_greylist:=tmilter_greylist.Create(SYS);
writeln(milter_greylist.MILTER_GREYLIST_PID_PATH());
halt(0);
end;

if ParamStr(2)='lighttpd' then
begin
zlighttpd:=Tlighttpd.Create(SYS);
writeln(zlighttpd.LIGHTTPD_VERSION());
halt(0);
end;

if ParamStr(2)='pdns' then
begin
zpdns:=tpdns.Create(SYS);
writeln(zpdns.VERSION());
halt(0);
end;

if ParamStr(2)='cyrus-imap' then
begin
ccyrus:=Tcyrus.Create(SYS);
writeln(ccyrus.CYRUS_VERSION());
halt(0);
end;


if ParamStr(2)='fail2ban' then
begin
zfail2ban:=tfail2ban.Create(SYS);
writeln(zfail2ban.VERSION());
halt(0);
end;

if ParamStr(2)='mysql-pid' then
begin
zmysql:=tmysql_daemon.Create(SYS);
writeln(zmysql.PID_PATH());
halt(0);
end;

if ParamStr(2)='mysql-ver' then
begin
zmysql:=tmysql_daemon.Create(SYS);
writeln(zmysql.VERSION());
halt(0);
end;

if ParamStr(2)='openldap' then
begin
zldap:=tOpenldap.Create();
writeln(zldap.LDAP_VERSION());
halt(0);
end;

if ParamStr(2)='openldap-pid' then
begin
zldap:=tOpenldap.Create();
writeln(zldap.PID_PATH());
halt(0);
end;

if ParamStr(2)='saslauthd' then
begin
zsaslauthd:=tsaslauthd.Create(SYS);
writeln(zsaslauthd.VERSION());
halt(0);
end;

if ParamStr(2)='saslauthd-pid' then
begin
zsaslauthd:=tsaslauthd.Create(SYS);
writeln(zsaslauthd.PID_PATH());
halt(0);
end;

if ParamStr(2)='amavis' then
begin
amavis:=tamavis.Create(SYS);
writeln(amavis.AMAVISD_VERSION());
halt(0);
end;


if ParamStr(2)='lighttpd-pid' then
begin
zlighttpd:=Tlighttpd.Create(SYS);
writeln(zlighttpd.LIGHTTPD_PID_PATH());
halt(0);
end;

if ParamStr(2)='lighttpd-version' then begin
   zlighttpd:=Tlighttpd.Create(SYS);
   writeln(zlighttpd.LIGHTTPD_VERSION());
   halt(0);
end;

if ParamStr(2)='spamassmilter-version' then begin
   zSpamass:=Tspamass.Create(SYS);
   writeln(zSpamass.MILTER_VERSION());
   halt(0);
end;

if ParamStr(2)='spamass' then begin
   zSpamass:=Tspamass.Create(SYS);
   writeln(zSpamass.SPAMASSASSIN_VERSION());
   halt(0);
end;

if ParamStr(2)='spamass-enabled' then begin
   zSpamass:=Tspamass.Create(SYS);
   writeln(zSpamass.IS_SPAMD_ENABLED());
   halt(0);
end;


if ParamStr(2)='postfix' then begin
zpostfix:=Tpostfix.Create(SYS);
writeln(zpostfix.POSTFIX_VERSION());
halt(0);
end;


if ParamStr(2)='fcron' then
begin
cron:=tcron.Create(SYS);
writeln(cron.FCRON_VERSION());
halt(0);
end;

if ParamStr(2)='clamd-pid' then
begin
zClam:=tclamav.Create;
writeln(zClam.CLAMD_GETINFO('PidFile'));
halt(0);
end;

if ParamStr(2)='clamav' then
begin
zClam:=tclamav.Create;
writeln(zClam.CLAMAV_VERSION());
halt(0);
end;

if ParamStr(2)='freshclam-pid' then begin
   zClam:=tclamav.Create;
   writeln(zClam.FRESHCLAM_GETINFO('PidFile'));
   halt(0);
end;

if ParamStr(2)='mailman-pid' then begin
   zmailman:=tmailman.Create(SYS);
   writeln(zmailman.PID_PATH());
   halt(0);
end;
if ParamStr(2)='mailman' then begin
   zmailman:=tmailman.Create(SYS);
   writeln(zmailman.VERSION());
   halt(0);
end;
if ParamStr(2)='kas3' then begin
   zkas3:=tkas3.Create(SYS);
   writeln(zkas3.VERSION());
   halt(0);
end;
if ParamStr(2)='samba' then begin
   samba3:=tsamba.Create();
   writeln(samba3.SAMBA_VERSION());
   halt(0);
end;
if ParamStr(2)='roundcube' then begin
   round:=troundcube.Create(SYS);
   writeln(round.VERSION());
   halt(0);
end;
if ParamStr(2)='cups' then begin
   zcups:=tcups.Create();
   writeln(zcups.VERSION());
   halt(0);
end;
 if ParamStr(2)='apache' then begin
   xopengoo:=topengoo.Create(SYS);
   writeln(xopengoo.APACHE_VERSION());
   halt(0);
end;
 if ParamStr(2)='gdm' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.GDM_VERSION());
   halt(0);
end;

 if ParamStr(2)='vboxguest' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.VBOXGUEST_VERSION());
   halt(0);
end;
 if ParamStr(2)='sabnzbdplus' then begin
   zsabnzbdplus:=tsabnzbdplus.Create(SYS);
   writeln(zsabnzbdplus.VERSION());
   halt(0);
end;
 if ParamStr(2)='munin' then begin
   zmunin:=tmunin.Create(SYS);
   writeln(zmunin.VERSION());
   halt(0);
end;
 if ParamStr(2)='greyhole' then begin
   zgreyhole:=tgreyhole.Create(SYS);
   writeln(zgreyhole.VERSION());
   halt(0);
end;
 if ParamStr(2)='dnsmasq' then begin
   zdnsmasq:=tdnsmasq.Create(SYS);
   writeln(zdnsmasq.DNSMASQ_VERSION);
   halt(0);
end;


 if ParamStr(2)='autofs' then begin
   zautofs:=tautofs.Create(SYS);
   writeln(zautofs.VERSION());
   halt(0);
end;

 if ParamStr(2)='ietd' then begin
   ztiscsitarget:=tiscsitarget.Create(SYS);
   writeln(ztiscsitarget.VERSION());
   halt(0);
end;




  if ParamStr(2)='vnstat' then begin
   ztoolsversions:=ttoolsversions.Create(SYS);
   writeln(ztoolsversions.VNSTAT());
   halt(0);
end;


 if ParamStr(2)='stunnel' then begin
   zstunnel4:=tstunnel.Create(SYS);
   writeln(zstunnel4.VERSION());
   halt(0);
end;


 if ParamStr(2)='xfce' then begin
   zxfce:=txfce.Create();
   writeln(zxfce.XFCE_VERSION());
   halt(0);
end;

 if ParamStr(2)='zarafa' then begin
   zZarafa:=tzarafa_server.Create(SYS);
   writeln(zZarafa.VERSION());
   halt(0);
end;
 if ParamStr(2)='vmtools' then begin
   ztvmtools:=tvmtools.Create(SYS);
   writeln(ztvmtools.VERSION());
   halt(0);
end;


 if ParamStr(2)='hamachi' then begin
   zhamachi:=thamachi.Create(SYS);
   writeln(zhamachi.VERSION());
   halt(0);
end;

 if ParamStr(2)='emailrelay' then begin

   writeln(GLOBAL_INI.EMAILRELAY_VERSION());
   halt(0);
end;

 if ParamStr(2)='dhcpd' then begin
   dhcp3:=Tdhcp3.Create(SYS);
   writeln(dhcp3.VERSION());
   halt(0);
end;


  if ParamStr(2)='pure-ftpd' then begin
   zpureftpd:=Tpureftpd.Create();
   writeln(zpureftpd.PURE_FTPD_VERSION());
   halt(0);
end;
  if ParamStr(2)='mldonkey' then begin
   zmldonkey:=tmldonkey.Create(SYS);
   writeln(zmldonkey.VERSION());
   halt(0);
end;
  if ParamStr(2)='policydw' then begin
   zpolicyd_weight:=tpolicyd_weight.Create(SYS);
   writeln(zpolicyd_weight.VERSION());
   halt(0);
end;
  if ParamStr(2)='backuppc' then begin
   zbackuppc:=tbackuppc.Create(SYS);
   writeln(zbackuppc.VERSION());
   halt(0);
end;
  if ParamStr(2)='kav4fs' then begin
   zkav4fs:=tkav4fs.Create(SYS);
   writeln(zkav4fs.VERSION());
   halt(0);
end;
  if ParamStr(2)='ocsi' then begin
   zocsi:=tocsi.Create(SYS);
   writeln(zocsi.VERSION());
   halt(0);
end;
  if ParamStr(2)='ocsagent' then begin
   zocsagent:=tocsagent.Create(SYS);
   writeln(zocsagent.VERSION());
   halt(0);
end;
  if ParamStr(2)='openssh' then begin
   zsshd:=tsshd.Create(SYS);
   writeln(zsshd.VERSION());
   halt(0);
end;
  if ParamStr(2)='gluster' then begin
   zgluster:=tgluster.Create(SYS);
   writeln(zgluster.VERSION());
   halt(0);
end;
if ParamStr(2)='auditd' then begin
   zauditd:=tauditd.Create(SYS);
   writeln(zauditd.VERSION());
   halt(0);
end;
if ParamStr(2)='dkfilter' then begin
   zdkfilter:=tdkfilter.Create(SYS);
   writeln(zdkfilter.VERSION());
   halt(0);
end;
if ParamStr(2)='opendkim' then begin
   zdkfilter:=tdkfilter.Create(SYS);
   writeln(zdkfilter.VERSION());
   halt(0);
end;
if ParamStr(2)='milterdkim' then begin
   zdkimmilter:=tdkimmilter.Create(SYS);
   writeln(zdkimmilter.VERSION());
   halt(0);
end;
if ParamStr(2)='dropbox' then begin
   zdropbox:=tdropbox.Create(SYS);
   writeln(zdropbox.VERSION());
   halt(0);
end;
if ParamStr(2)='virtualbox' then begin
   zvirtualbox:=tvirtualbox.Create(SYS);
   writeln(zvirtualbox.VERSION());
   halt(0);
end;
if ParamStr(2)='tftpd' then begin
   ztftpd:=ttftpd.Create(SYS);
   writeln(ztftpd.VERSION());
   halt(0);
end;
if ParamStr(2)='crossroads' then begin
   zcrossroads:=tcrossroads.Create(SYS);
   writeln(zcrossroads.VERSION());
   halt(0);
end;
if ParamStr(2)='pptpd' then begin
   zpptpd:=tpptpd.Create(SYS);
   writeln(zpptpd.VERSION());
   halt(0);
end;
 if ParamStr(2)='apt-mirror' then begin
   zapt_mirror:=tapt_mirror.Create(SYS);
   writeln(zapt_mirror.VERSION());
   halt(0);
end;
 if ParamStr(2)='ddclient' then begin
   zddclient:=tddclient.Create(SYS);
   writeln(zddclient.VERSION());
   halt(0);
end;
 if ParamStr(2)='cluebringer' then begin
   zcluebringer:=tcluebringer.Create(SYS);
   writeln(zcluebringer.VERSION());
   halt(0);
end;
 if ParamStr(2)='tc' then begin
   writeln(GLOBAL_INI.TC_VERSION());
   halt(0);
end;
 if ParamStr(2)='openvpn' then begin
   zopenvpn:=topenvpn.Create(SYS);
   writeln(zopenvpn.VERSION());
   halt(0);
end;






if ParamStr(2)='ufdbguardd' then begin
   zufdbguardd:=tufdbguardd.Create(SYS);
   writeln(zufdbguardd.VERSION());
   halt(0);
end;



writeln('help:');
writeln('--export-version squid');
writeln('--export-version c-icap');
writeln('--export-version dansguardian');
writeln('--export-version kav4proxy');
writeln('--export-version wpa_suppliant');
writeln('--export-version hostapd');
writeln('--export-version fetchmail');
writeln('--export-version milter-greylist');
writeln('--export-version milter-greylist-pid');
writeln('--export-version fail2ban');
writeln('--export-version lighttpd');
writeln('--export-version pdns');
writeln('--export-version vmtools');
writeln('--export-version hamachi');
writeln('--export-version emailrelay');
writeln('--export-version dhcpd');
writeln('--export-version pure-ftpd');
writeln('--export-version mldonkey');
writeln('--export-version backuppc');
writeln('--export-version kav4fs');
writeln('--export-version ocsi');
writeln('--export-version openssh');
writeln('--export-version auditd');
writeln('--export-version dkfilter');
writeln('--export-version tc');




halt(0);
end;



if ParamStr(1)='--reload-apache-groupware' then begin
   xopengoo:=Topengoo.Create(SYS);
   xopengoo.RELOAD();
   halt(0);
end;
if ParamStr(1)='--reload-dhcpd' then begin
   dhcp3:=Tdhcp3.Create(SYS);
   dhcp3.RELOAD();
   halt(0);
end;
 if ParamStr(1)='--dhcpd-find-nic' then begin
   dhcp3:=Tdhcp3.Create(SYS);
   writeln(dhcp3.FIND_NIC());
   halt(0);
end;

if ParamStr(1)='--reload-dansguardian' then begin
   zdansguardian.DANSGUARDIAN_RELOAD();
   halt(0);
end;

if ParamStr(1)='--monit-status' then begin
   zmonit:=tmonit.Create(SYS);
   zmonit.BuildStatus();
   zmonit.free;
   SYS.free;
   halt(0);
end;


if ParamStr(1)='--monit-check' then
begin
SYS.MONIT_CHECK_ALL();
halt(0);
end;


if ParamStr(1)='--mail-tail' then
begin
zpostfix:=tpostfix.Create(SYS);
zpostfix.MAILLOG_TAIL(ParamStr(2));
halt(0);
end;

if ParamStr(1)='--gen-cert' then
begin
install.GenerateCertificateFileName(ParamStr(2));
halt(0);
end;

if ParamStr(1)='--all-status' then
begin
GLOBAL_INI:=myconf.Create();
writeln(GLOBAL_INI.GLOBAL_STATUS());
halt(0);
end;

if ParamStr(1)='--zarafa-certificates' then
begin
zZarafa:=tzarafa_server.Create(SYS);
zZarafa.CERTIFICATES();
halt(0);
end;

if ParamStr(1)='--zarafa-apache-certificates' then
begin
zZarafa:=tzarafa_server.Create(SYS);
zZarafa.APACHE_CERTIFICATES();
halt(0);
end;

if ParamStr(1)='--zarafa-remove' then
begin
zZarafa:=tzarafa_server.Create(SYS);
fpsystem('/usr/share/artica-postfix/bin/artica-make APP_ZARAFA --remove');
zZarafa.REMOVE();
halt(0);
end;


if ParamStr(1)='--myos' then
begin
GLOBAL_INI:=myconf.Create();
writeln(install.LinuxInfosDistri()+ ';' + GLOBAL_INI.SYSTEM_KERNEL_VERSION() + ';' + GLOBAL_INI.SYSTEM_LIBC_VERSION());
halt(0);
end;

if ParamStr(1)='--zarafa-remove' then
begin
zZarafa:=tzarafa_server.Create(SYS);
zZarafa.REMOVE();
halt(0);
end;

if ParamStr(1)='--milter-grelist-remove' then
begin
milter_greylist:=tmilter_greylist.Create(SYS);
milter_greylist.REMOVE();
halt(0);
end;
if ParamStr(1)='--postfix-remove' then
begin
zpostfix:=tpostfix.Create(SYS);
zpostfix.REMOVE();
halt(0);
end;
if ParamStr(1)='--samba-remove' then
begin
samba3:=tsamba.Create();
samba3.REMOVE();
halt(0);
end;
if ParamStr(1)='--squid-remove' then begin
   zsquid:=tsquid.Create();
   zsquid.REMOVE();
   zkav4proxy:=tkav4proxy.Create(SYS);
   zkav4proxy.REMOVE();
   halt(0);
end;
if ParamStr(1)='--kav4Proxy-remove' then
begin
zkav4proxy:=tkav4proxy.Create(SYS);
zkav4proxy.REMOVE();
halt(0);
end;
if ParamStr(1)='--dansguardian-remove' then
begin
zdansguardian:=tdansguardian.Create(SYS);
zdansguardian.REMOVE();
halt(0);
end;
if ParamStr(1)='--kav4samba-remove' then
begin
zkav4Samba:=Tkav4Samba.Create;
zkav4Samba.REMOVE();
halt(0);
end;

if ParamStr(1)='--pureftpd-remove' then
begin
zpureftpd:=Tpureftpd.Create;
zpureftpd.REMOVE();
halt(0);
end;

if ParamStr(1)='--postfilter-remove' then
begin
zpostfilter:=Tpostfilter.Create(SYS);
zpostfilter.REMOVE();
halt(0);
end;

if ParamStr(1)='--kas3-remove' then
begin
zkas3:=tkas3.Create(SYS);
zkas3.REMOVE();
halt(0);
end;



if ParamStr(1)='--kavmilter-mem' then
begin
zkavmilter:=tkavmilter.Create(SYS);
writeln(SYS.PROCESS_MEMORY(zkavmilter.KAV_MILTER_PID()));
halt(0);
end;

if ParamStr(1)='--dansguardian-template' then
begin
zdansguardian:=Tdansguardian.Create(SYS);
zdansguardian.DANSGUARDIAN_TEMPLATE();
halt(0);
end;



if ParamStr(1)='--reconfigure-nic' then
begin
GLOBAL_INI:=myconf.Create;
GLOBAL_INI.SYSTEM_NETWORKS_SET_NIC(ParamStr(2),ParamStr(3),ParamStr(4),ParamStr(5),ParamStr(6),ParamStr(7));
halt(0);
end;


if ParamStr(1)='--reload-cyrus' then
begin
ccyrus.CYRUS_DAEMON_RELOAD();
halt(0);
end;

if ParamStr(1)='--fetchmail-status' then
begin
zfetchmail:=tfetchmail.Create(SYS);
writeln(zfetchmail.STATUS());
halt(0);
end;

if ParamStr(1)='--retranslator-status' then
begin
retrans:=tkretranslator.Create(SYS);
writeln(retrans.STATUS());
halt(0);
end;

if ParamStr(1)='--kavm4mls-info' then
begin
zkavm4mls:=tkavm4mls.Create(SYS);
zkavm4mls.COMPONENTS_INFOS();
halt(0);
end;

if ParamStr(1)='--kavm4mls-pattern' then
begin
zkavm4mls:=tkavm4mls.Create(SYS);
writeln(zkavm4mls.PATTERN_DATE());
halt(0);
end;

if ParamStr(1)='--kavmilter-pattern' then
begin
zkavmilter:=tkavmilter.Create(SYS);
writeln(zkavmilter.PATTERN_DATE());
halt(0);
end;


if ParamStr(1)='--reload-spamassassin' then
begin
zSpamass.SPAMASSASSIN_RELOAD();
halt(0);
end;

if ParamStr(1)='--reload-kav4proxy' then
begin
zkav4proxy:=tkav4proxy.Create(SYS);
zkav4proxy.KAV4PROXY_RELOAD();
halt(0);
end;


if ParamStr(1)='--reload-assp' then
begin
zassp:=Tassp.Create(SYS);
zassp.RELOAD();
halt(0);
end;

if ParamStr(1)='--mailboxes-domain' then
begin
list:=Tstringlist.Create;
list.AddStrings(ccyrus.LIST_MAILBOXES_DOMAIN(ParamStr(2)));
writeln(list.Text);
halt(0);
end;

if ParamStr(1)='--mailboxes-list' then
begin
list:=Tstringlist.Create;
list.AddStrings(ccyrus.LIST_MAILBOXES());
writeln(list.Text);
halt(0);
end;

if ParamStr(1)='--mailbox-delete' then
begin
ccyrus.DELETE_MAILBOXE(ParamStr(2));
halt(0);
end;

if ParamStr(1)='--remote-ressources' then
begin
writeln(SYS.GetRemoteComputerRessources(ParamStr(2),ParamStr(3),ParamStr(4)));
halt(0);
end;

if ParamStr(1)='--mysql-upgrade' then
begin
GLOBAL_INI:=myconf.Create;
GLOBAL_INI.MYSQL_UPGRADE();
halt(0);
end;

if ParamStr(1)='--vmtools-version' then
begin
ztvmtools:=tvmtools.Create(SYS);
writeln(ztvmtools.VERSION());
halt(0);
end;


if ParamStr(1)='--kaspersky-status' then
begin
zkas3:=tkas3.Create(SYS);
zkav4proxy:=tkav4proxy.Create(SYS);
zkav4Samba:=Tkav4Samba.Create;
zkavmilter:=tkavmilter.Create(SYS);
writeln(zkav4proxy.KAV4PROXY_STATUS());
writeln(zkav4Samba.STATUS());
writeln(zkas3.STATUS());
writeln(zkavmilter.STATUS());
halt(0);
end;




if ParamStr(1)='-isnetav' then
begin
tcp_IP:=ttcpip.Create;
writeln(tcp_IP.isNetAvailable);
halt(0);
end;

if ParamStr(1)='-hostname' then
begin
writeln(getHostname());
halt(0);
end;




if ParamStr(1)='--delete-mailbox' then
begin
ccyrus.DELETE_MAILBOXE(ParamStr(2));
halt(0);
end;

if ParamStr(1)='--reconfigure-cyrus' then begin
   if not FileExists(ccyrus.CYRUS_DAEMON_BIN_PATH()) then begin
      writeln('Cyrus is not installed...');
      halt(0);
   end;

   if FileExists(SYS.LOCATE_GENERIC_BIN('zarafa-server')) then begin
      writeln('Zarafa is installed, aborting');
      halt(0);
   end;

   ccyrus.CYRUS_DAEMON_STOP();
   ccyrus.WRITE_IMAPD_CONF();
   ccyrus.WRITE_CYRUS_CONF();
   ccyrus.CheckRightsAndConfig();
   ccyrus.CYRUS_DAEMON_START();
   halt(0);
end;

if ParamStr(1)='--cyrus-checkperms' then
begin
ccyrus.CYRUS_DAEMON_STOP();
ccyrus.CheckRightsAndConfig();
ccyrus.CYRUS_DAEMON_START();
halt(0);
end;

if ParamStr(1)='--vmhost' then
begin
writeln(SYS.VMWARE_HOST());
halt(0);
end;


if ParamStr(1)='--check-virus-logs' then
begin
if not SYS.PROCESS_EXIST(SYS.PIDOF('/usr/bin/clamscan')) then
begin
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.parse.virus.queue.php &');
end;
halt(0);
end;

if ParamStr(1)='--scan-cyrus' then
begin
ccyrus.ANTIVIRUS_SCAN();
halt(0);
end;


if ParamStr(1)='-shutdown' then
begin
install.SHUTDOWN();
fpsystem(Paramstr(0)+' --generate-status-forced &');
halt(0);
end;

if ParamStr(1)='-watchdog' then
begin
     install.START_SERVICES_PARAMETERS();
     halt(0);
end;


if ParamStr(1)='-awstats' then
begin install.PARAMETERS_AWSTATS() end;


if ParamStr(1)='--nfs-reload' then begin
   znfs:=tnfs.Create(SYS);
   znfs.RELOAD();
   halt(0);
end;

if ParamStr(1)='--assp-status' then
begin
zassp:=tassp.Create(SYS);
writeln(zassp.STATUS());
halt(0);
end;

if ParamStr(1)='--pdns-status' then
begin
zpdns:=tpdns.Create(SYS);
writeln(zpdns.STATUS());
halt(0);
end;

if ParamStr(1)='--gluster-status' then
begin
zgluster:=tgluster.Create(SYS);
writeln(zgluster.STATUS());
zgluster.free;
halt(0);
end;



if ParamStr(1)='--cluster-replicat-send-ldap' then
begin
ccyrus.CLUSTER_SEND_LDAP_DATABASE();
halt(0);
end;

if ParamStr(1)='--SAVE_SLAPD_CONF' then
begin
zldap:=topenldap.Create;
zldap.SAVE_SLAPD_CONF();
halt(0);
end;

if ParamStr(1)='--pvcreate-dev' then
begin
zlvm:=tlvm.Create(SYS);
zlvm.pvcreate_dev(ParamStr(2));
halt(0);
end;

if ParamStr(1)='--vgcreate-dev' then
begin
zlvm:=tlvm.Create(SYS);
zlvm.vgcreate_dev(ParamStr(2),ParamStr(3));
halt(0);
end;


if ParamStr(1)='--lvm-list' then
begin
zlvm:=tlvm.Create(SYS);
writeln(zlvm.SCAN_DISKS());
writeln(zlvm.SCAN_VG());
halt(0);
end;

if ParamStr(1)='--usb-scan-write' then
begin
zlvm:=tlvm.Create(SYS);
FileData:=Tstringlist.CReate;
GLOBAL_INI:=myconf.Create;
FileData.Add('<?php');
FileData.Add(GLOBAL_INI.SCAN_USB());
FileData.Add('');
FileData.Add('// Disks list...');
FileData.Add('');
FileData.Add(GLOBAL_INI.SCAN_DISK_PHP());
FileData.Add('');
FileData.Add('// lvm list...');
FileData.Add('');
FileData.Add(zlvm.SCAN_DISKS());
FileData.Add(zlvm.SCAN_DEV());
FileData.Add('');
FileData.Add('// lvm group list...');
FileData.Add('');
FileData.Add(zlvm.SCAN_VG());
FileData.Add('?>');
FileData.SaveToFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/usb.scan.inc');
zlogs.OutputCmd('/bin/chmod 755 '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/usb.scan.inc');
halt(0);


end;

if ParamStr(1)='--ntpd-status' then
begin
writeln(zntpd.NTPD_STATUS());
halt(0);
end;



if paramstr(1)='--format-disk-unix' then
begin
SYS.DISK_FORMAT_UNIX(paramstr(2));
halt(0);
end;


if ParamStr(1)='--urgency-start' then
begin
if fileExists('/etc/init.d/ssh') then
begin fpsystem('/etc/init.d/ssh start &') end;
if fileExists('/etc/init.d/sshd') then
begin fpsystem('/etc/init.d/sshd start &') end;
if fileExists('/etc/init.d/cron') then
begin fpsystem('/etc/init.d/cron start &') end;
fpsystem('/etc/init.d/artica-postfix start daemon &');
halt(0);
end;


if ParamStr(1)='--quarantines-schedule' then
begin
cron:=tcron.Create(SYS);
cron.quarantine_report_schedules();
halt(0);
end;

if ParamStr(1)='--quarantines-clean-disk' then
begin
GLOBAL_INI:=myconf.Create;
GLOBAL_INI.CLEAN_QUARANTINES();
halt(0);
end;

if paramStr(1)='--spamassassin-reload' then
begin
zSpamass:=Tspamass.Create(SYS);
zSpamass.SPAMASSASSIN_RELOAD();
halt(0);
end;

if paramStr(1)='--boa-status' then
begin
GLOBAL_INI:=myconf.Create;
writeln(GLOBAL_INI.BOA_BIN_PATH());
writeln(GLOBAL_INI.BOA_DAEMON_STATUS());
halt(0);
halt(0);
end;

 if paramStr(1)='--sarg' then begin
    zsquid:=Tsquid.Create;
    zsquid.SARG_EXECUTE();
    writeln('done...');
    halt(0);
end;

 if paramStr(1)='--sarg-version' then begin
    zsquid:=Tsquid.Create;
    writeln(zsquid.SARG_VERSION());
    halt(0);
end;


if paramStr(1)='--sarg-config' then
begin
zsquid:=Tsquid.Create;
zsquid.SARG_CONFIG();
writeln('done...');
halt(0);
end;

if paramStr(1)='--reload-rsync' then
begin
zrsync:=Trsync.Create(SYS);
zrsync.RELOAD();
writeln('done...');
halt(0);
end;

if paramStr(1)='--rsync-status' then
begin
zrsync:=Trsync.Create(SYS);
writeln(zrsync.STATUS());
halt(0);
end;
if paramStr(1)='--free-port' then
begin
writeln(SYS.FREE_PORT());
halt(0);
end;

if paramStr(1)='--sarg-scan' then
begin
zsquid:=Tsquid.Create;
writeln(zsquid.SARG_SCAN());
writeln('done...');
halt(0);
end;


if paramStr(1)='--apache-groupware-restart' then
begin
xopengoo:=Topengoo.Create(SYS);
xopengoo.RELOAD();
halt(0);
end;

if paramStr(1)='--apache-groupware-php' then
begin
xopengoo:=Topengoo.Create(SYS);
xopengoo.WritePhpConfig();
writeln('done..');
halt(0);
end;


if paramStr(1)='--hamachi-nets' then
begin
zhamachi:=thamachi.Create(SYS);
zhamachi.NETWORK_LIST();
writeln(zhamachi.NETLIST.Text);
halt(0);
end;


if paramStr(1)='--hamachi-status' then
begin
zhamachi:=thamachi.Create(SYS);
writeln(zhamachi.STATUS());
halt(0);
end;

if paramStr(1)='--apache-ssl-cert' then
begin
zapache_artica:=tapache_artica.Create(SYS);
zapache_artica.APACHE_ARTICA_SSL_KEY();
writeln('done..');
halt(0);
end;


if ParamStr(1)='--status' then
begin
if not SYS.BuildPids() then begin
   zlogs.Debuglogs('--status already instance running');
   halt(0);
end;

if SYS.isoverloadedTooMuch() then
begin halt(0) end;


GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.GLOBAL_STATUS());
halt(0);


end;

if paramStr(1)='--dstat-gen-mem' then
begin
zdstat:=tdstat.Create(SYS);
zdstat.GENERATE_MEMORY(paramStr(2));
halt(0);
end;

if paramStr(1)='--sarg' then
begin
zsquid:=Tsquid.Create;
zsquid.SARG_EXECUTE();
writeln('done...');
halt(0);
end;





if paramStr(1)='--imap-tests' then
begin
writeln(ccyrus.TestingMailBox(paramStr(2),paramStr(3)));
halt(0);
end;


if paramStr(1)='--xapian-status' then
begin
zxapian:=Txapian.Create(SYS);
writeln('[APP_XAPIAN] "' + zxapian.VERSION() + '"');
writeln('[APP_XAPIAN_OMEGA] "' + zxapian.OMINDEX_VERSION() + '"');
writeln('[APP_XAPIAN_PHP] "' + zxapian.PHP_VERSION() + '"');
writeln('[APP_XPDF] "' + zxapian.APP_XPDF_VERSION() + '"');
writeln('[APP_UNRTF] "' + zxapian.APP_UNRTF_VERSION() + '"');
writeln('[APP_UNZIP] "' + zxapian.APP_UNZIP_VERSION() + '"');
writeln('[APP_CATDOC] "' + zxapian.APP_CATDOC_VERSION() + '"');
writeln('[APP_ANTIWORD] "' + zxapian.APP_ANTIWORD_VERSION() + '"');
zxapian.free;
halt(0);
end;



if paramStr(1)='--find-pid' then
begin
writeln('artica:'+SYS.PidByProcessPath(ParamStr(2)));
writeln('pidof:'+SYS.PIDOF(ParamStr(2)));
writeln('pidof_pattern:'+SYS.PIDOF_PATTERN(ParamStr(2)));
writeln('Processes pattern number:'+IntTostr(SYS.PIDOF_PATTERN_PROCESS_NUMBER(ParamStr(2))));

halt(0);
end;

if ParamStr(1)='--pid-mem' then
begin
writeln('Global memory of '+ParamStr(2),' ',SYS.PROCESS_MEMORY(ParamStr(2)),' kb');
halt(0);
end;


if paramStr(1)='--obm2-version' then
begin
zobm2:=tobm2.Create(SYS);
writeln('OBM version...: '+zobm2.VERSION());
halt(0);
end;


if paramStr(1)='--devinfo' then
begin
GLOBAL_INI:=Myconf.Create;
writeln('dev: '+ ParamStr(2));
writeln('key: '+ ParamStr(3));
writeln(GLOBAL_INI.USB_DEV_INFO(ParamStr(2),ParamStr(3)));

halt(0);
end;





if paramStr(1)='--findallpid' then
begin
writeln(SYS.PidAllByProcessPath(ParamStr(2)));
halt(0);
end;

if paramStr(1)='--cups-drivers' then
begin
zcups:=Tcups.Create;
zcups.CHECK_DRIVERS();
halt(0);
end;


if paramStr(1)='--cups-config' then
begin
zcups:=Tcups.Create;
zcups.WRITE_CUPS_CONF();
halt(0);
end;



if paramStr(1)='--cups-delete-all-printers' then
begin
zcups:=Tcups.Create;
zcups.DeleteAllPrinters();
halt(0);
end;



if paramStr(1)='--cups-windows' then
begin
zcups:=Tcups.Create;
zcups.WINDOWS_DRIVERS();
halt(0);
end;



if paramStr(1)='--cups-gutenprint' then
begin
zcups:=Tcups.Create;
zcups.gutenprint_SCAN();
halt(0);
end;

if paramStr(1)='--is-ip-forward' then
begin
writeln(SYS.ip_forward_enabled());
halt(0);
end;

if paramStr(1)='--findpid' then
begin
writeln(SYS.PidByPatternInPath(ParamStr(2)));
halt(0);
end;

if paramStr(1)='--samba-status' then
begin
samba3:=Tsamba.Create;
writeln(samba3.SAMBA_STATUS());
halt(0);
end;



if paramStr(1)='--samba-reconfigure' then
begin
samba3:=Tsamba.Create;
samba3.Reconfigure(false);
halt(0);
end;

if paramStr(1)='--php-infos' then
begin
writeln('PHP binary................:'+SYS.LOCATE_PHP5_BIN());
writeln('PHP config binary.........:'+SYS.LOCATE_PHP5_CONFIG_BIN());
writeln('PHP ext conf..............:'+SYS.LOCATE_PHP5_EXTCONF_DIR());
writeln('PHP ext dir...............:'+SYS.LOCATE_PHP5_EXTENSION_DIR());
writeln('PHP session path..........:'+SYS.LOCATE_PHP5_SESSION_PATH());
halt(0);
end;



if ParamStr(1)='--set-loopback' then
begin
zbind9.ApplyLoopBack();
halt(0);
end;

if paramStr(1)='--pidtime' then
begin
writeln('Minutes of this process:',SYS.PROCCESS_TIME_MIN(SYS.PIDOF(ParamStr(2))));
halt(0);
end;


if paramStr(1)='--amavis-reload' then
begin
GLOBAL_INI:=myconf.Create;
amavis:=tamavis.Create(GLOBAL_INI.SYS);
amavis.AMAVISD_RELOAD();
halt(0);
end;

if paramStr(1)='--kavmilter-reload' then
begin
zkavmilter:=tkavmilter.Create(SYS);
zkavmilter.RELOAD();
halt(0);
end;





if ParamStr(1)='--recover-cyrus' then
begin
CCYRUS.REPAIR_CYRUS();
halt(0);
end;

if ParamStr(1)='--cyrus-checkconfig' then
begin
CCYRUS.CheckRightsAndConfig();
halt(0);
end;



if ParamStr(1)='--cyrus-ctl-cyrusdb' then begin
   CCYRUS.RECOVER_CYRUS_DB_SINGLE();
   halt(0);
end;
if ParamStr(1)='--cyrus-recoverdb' then
begin
CCYRUS.MASTER_RECOVER();
halt(0);
end;




if paramStr(1)='--cpuinfo' then
begin
writeln(SYS.CPU_MHZ());
halt(0);
end;

if paramStr(1)='--lgroup' then
begin
writeln(SYS.IsUserINGroup(ParamStr(2),ParamStr(3)));
halt(0);
end;

if paramStr(1)='--whereis-maillog' then
begin
writeln('maillog: ',SYS.MAILLOG_PATH());
writeln('auth...: ',SYS.LOCATE_AUTH_LOG());
halt(0);
end;

if paramStr(1)='--whereis-syslog' then
begin
writeln('SYSLOG:"'+SYS.LOCATE_SYSLOG_PATH(),'"');
writeln('AUTH..:"'+SYS.LOCATE_AUTHLOG_PATH,'"');
halt(0);
end;




if paramStr(1)='--dhcp3-status' then
begin
dhcp3:=tdhcp3.Create(SYS);
writeln(dhcp3.STATUS());
halt(0);
end;

if paramStr(1)='--dns-keygen' then
begin
zbind9:=Tbind9.Create(SYS);
zbind9.GenerateKey(paramStr(2));
halt(0);
end;

if paramStr(1)='--read-queue' then
begin
writeln(zpostfix.POSFTIX_READ_QUEUE(paramStr(2)));
halt(0);
end;

if paramStr(1)='--postfix-sasldb2' then
begin
zpostfix.POSTFIX_CHECK_SASLDB2();
halt(0);
end;




if paramStr(1)='--vacation-version' then
begin
writeln(zpostfix.gnarwl_VERSION());
halt(0);
end;

if paramStr(1)='--slpapindex' then
begin
zldap:=topenldap.Create;
zldap.SLAP_INDEX();
halt(0);
end;


if ParamStr(1)='--bind9-fw' then
begin
zbind9.ApplyForwarders(ParamStr(2));
halt(0);
end;

if paramStr(1)='--mailsync-status' then
begin
zimapsync:=timapsync.Create(SYS);
writeln(zimapsync.MAILSYNC_VERSION());
halt(0);
end;





if paramStr(1)='--kretranslator-status' then
begin
retrans:=tkretranslator.Create(SYS);
writeln(retrans.STATUS());
halt(0);
end;


if paramStr(1)='--regpid' then
begin
writeln(SYS.AllPidsByPatternInPath(ParamStr(2)));
halt(0);
end;

if paramStr(1)='--usbscan' then
begin
GLOBAL_INI:=myconf.Create();
writeln(GLOBAL_INI.SCAN_USB());
halt(0);
end;


if paramStr(1)='--diskscan' then
begin
GLOBAL_INI:=myconf.Create();
writeln(GLOBAL_INI.SCAN_DISK_PHP());
halt(0);
end;





if paramStr(1)='--start-minimum-daemons' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
halt(0);
end;

if paramStr(1)='--bindrrd' then begin
   zbind9.Generate_binrrd();
   halt(0);
end;

if paramStr(1)='--collectd' then
begin
zcollectd:=tcollectd.Create(SYS);
writeln(zcollectd.PHP_DATA_DIR());
halt(0);
end;

if paramStr(1)='--isoqlog' then
begin
zisoqlog:=tisoqlog.Create(SYS);
zisoqlog.performStatistics();
zisoqlog.free;
halt(0);
end;


if paramStr(1)='--retranslator' then
begin
if not SYS.BuildPids() then
begin
zlogs.Debuglogs('--retranslator already instance executed');
halt(0);
end;

retrans:=tkretranslator.Create(SYS);
GLOBAL_INI:=myconf.Create();
if(retrans.RetranslatorEnabled=1) then
begin
fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() +'/bin/artica-update --retranslator &');
halt(0);
end;
halt(0);
end;


if paramStr(1)='--clamav-status' then
begin
writeln(Zclam.CLAMAV_STATUS());
halt(0);
end;

if paramStr(1)='--mailman-status' then
begin
zmailman:=tmailman.Create(SYS);
writeln(zmailman.STATUS());
zmailman.free;
halt(0);
end;


if paramStr(1)='--squid-status' then
begin
zdansguardian:=tdansguardian.Create(SYS);
zkav4proxy:=tkav4proxy.Create(SYS);
writeln(zsquid.SQUID_STATUS());
writeln(zdansguardian.DANSGUARDIAN_STATUS());
writeln(zdansguardian.C_ICAP_STATUS());
writeln(zkav4proxy.KAV4PROXY_STATUS());

halt(0);
end;

if paramStr(1)='--sarg-scan' then
begin
writeln(zsquid.SARG_SCAN());
halt(0);
end;

if paramStr(1)='--obm-status' then
begin
zobm:=tobm.Create(SYS);
writeln(zobm.STATUS());
halt(0);
end;

if paramStr(1)='--get-local-ip' then
begin
writeln('eth0: ',GLOBAL_INI.SYSTEM_GET_LOCAL_IP('eth0'));
writeln('eth1: ',GLOBAL_INI.SYSTEM_GET_LOCAL_IP('eth1'));
writeln('eth2: ',GLOBAL_INI.SYSTEM_GET_LOCAL_IP('eth2'));
halt(0);
end;


if paramStr(1)='--ldap-reconfigure' then
begin
zMENUS:=Tmenus.Create();
zMENUS.reconfigure_ldap();
halt(0);
end;


if paramStr(1)='--verify-artica-iso' then
begin
deb.linuxlogo();
if SYS.croned_minutes2(30) then
begin
deb.ARTICA_CD_SOURCES_LIST();
end;
deb.linuxlogo();
deb.free;
halt(0);
end;


if paramStr(1)='--dansguardian-stats' then
begin
zdansguardian.DANSGUARDIAN_STATS();
halt(0);
end;

if paramStr(1)='--c-icap-reload' then
begin
zdansguardian.C_ICAP_RELOAD();
halt(0);
end;



if paramStr(1)='--dansguardian-status' then
begin
writeln(zdansguardian.DANSGUARDIAN_STATUS());
writeln(zdansguardian.DANSGUARDIAN_TAIL_STATUS());
halt(0);
end;

if paramStr(1)='--kaspersky' then
begin
install.ARTICA_CD_KASPERSKY();
halt(0);
end;

if paramStr(1)='--kav4samba-install' then
begin
install.KAV4SAMBA_INSTALL();
halt(0);
end;

if paramStr(1)='--squid-status' then
begin
writeln(zsquid.SQUID_STATUS());
halt(0);
end;


if ParamStr(1)='--php-include' then
begin
writeln('Starting change include path for php...');
zlighttpd.LIGHTTPD_ADD_INCLUDE_PATH();
writeln('done..');
halt(0);
end;



if paramStr(1)='--c-icap-configure' then
begin
zdansguardian.C_ICAP_CONFIGURE();
halt(0);
end;

if paramStr(1)='--spamd-status' then
begin

writeln(zSpamass.SPAMASSASSIN_STATUS());
halt(0);
end;


if paramStr(1)='start' then
begin
install.START_SERVICES_PARAMETERS();
halt(0);
end;


if paramStr(1)='stop' then
begin
install.SHUTDOWN();
halt(0);
end;


if paramStr(1)='--c-icap-configure' then
begin
zdansguardian.C_ICAP_CONFIGURE();
halt(0);
end;

if paramStr(1)='--fdm-status' then
begin
writeln('bin path........:' + zfdm.bin_path());
writeln('Cache features..:' , zfdm.CACHE_EXISTS());
halt(0);
end;



if paramStr(1)='--fdm-perform' then
begin
if SYS.BuildPids() then
begin
zfdm.START_PROCESS(paramStr(2));
end;
halt(0);
end;

if paramStr(1)='--change-mysqlroot' then begin
   global_ini:=myconf.Create();
   global_ini.MYSQL_CHANGE_ROOT_PASSWORD();
halt(0);
end;

if paramStr(1)='--avpattern-status' then
begin
global_ini:=myconf.Create();
writeln(global_ini.STATUS_PATTERN_DATABASES());
halt(0);
end;

if paramStr(1)='--awstats-generate' then
begin
if SYS.BuildPids() then
begin
zawstats:=tawstats.Create(SYS);
zawstats.AWSTATS_GENERATE();
end;
halt(0);
end;

if paramStr(1)='--ipstat' then
begin
global_ini:=myconf.Create();
//    writeln(global_ini.InterfacesList());
writeln('Local ip.......:',global_ini.SYSTEM_GET_LOCAL_IP(paramStr(2)));
writeln('Local Netmask..:',global_ini.SYSTEM_GET_LOCAL_MASK(paramStr(2)));
writeln('Local MAC......:',global_ini.SYSTEM_GET_LOCAL_MAC(paramStr(2)));
writeln('Local broadcast:',global_ini.SYSTEM_GET_LOCAL_BROADCAST(paramStr(2)));
writeln('Gateway........:',global_ini.SYSTEM_GET_LOCAL_GATEWAY(paramStr(2)));
writeln('Local DNS......:',global_ini.SYSTEM_GET_LOCAL_DNS());
writeln('Wireless.......:',global_ini.IsWireless(paramStr(2)));
writeln('UP.............:',global_ini.IsIfaceDown(paramStr(2)));
writeln('');
writeln('Config:');
writeln(global_ini.SYSTEM_NETWORK_INFO_NIC_DEBIAN(paramStr(2)));
halt(0);
end;
if paramStr(1)='--set-hostname' then
begin
global_ini:=myconf.Create();
global_ini.SYSTEM_NETWORKS_SET_HOSTNAME(paramStr(2));
halt(0);
end;


if  paramStr(1)='--setip' then
begin
writeln('Local nic......:',paramStr(2));
writeln('Local ip.......:',paramStr(3));
writeln('Local Netmask..:',paramStr(4));
writeln('Gateway........:',paramStr(5));
writeln('dhcp...........:',paramStr(6));
global_ini:=myconf.Create();
global_ini.SYSTEM_NETWORKS_SET_NIC(paramStr(2),paramStr(3),paramStr(4),paramStr(5),paramStr(6));
halt(0);
end;


if paramStr(1)='--change-certificate' then
begin
if SYS.BuildPids() then
begin
install.ChangeAllCertificates();
end;
halt(0);
end;

if Paramstr(1)='--openvpn-build-certificate' then
begin
zopenvpn:=topenvpn.Create(SYS);
zopenvpn.BuildCertificate();
halt(0);
end;

if paramStr(1)='--ldap-ssl' then
begin
zldap.CREATE_CERTIFICATE();
halt(0);
end;

if paramStr(1)='--change-postfix-certificate' then
begin
fpsystem('/bin/rm -rf /etc/ssl/certs/postfix/*');
zpostfix.GENERATE_CERTIFICATE();
halt(0);
end;

if ParamStr(1)='-lighttpd-cert' then
begin
GLOBAL_INI:=MyConf.Create();
zlighttpd.LIGHTTPD_CERTIFICATE();
writeln('');
writeln('');
halt(0);
end;


if paramStr(1)='--postfix-status' then
begin
writeln('postfix version........:',zpostfix.POSTFIX_VERSION());
writeln('postfix pid............:',zpostfix.POSTFIX_PID());
writeln('postfix LDAP...........:',zpostfix.POSTFIX_LDAP_COMPLIANCE());
writeln('postfix PCRE...........:',zpostfix.POSTFIX_PCRE_COMPLIANCE());

writeln(zpostfix.STATUS());

writeln('postfix-logger.........:');
writeln(zpostfix.MYSQMAIL_STATUS());
stunnel:=tstunnel.Create(SYS);
global_ini:=myconf.Create();
ztoolsversions:=ttoolsversions.Create(SYS);
writeln('stunnel version........:',stunnel.VERSION());
writeln('GnuPlot version........:',ztoolsversions.GNUPLOT_VERSION());
halt(0);




end;


if paramStr(1)='--postfix-reload' then
begin
zpostfix.POSTFIX_RELOAD();
halt(0);
end;



if paramStr(1)='--init-from-repos' then
begin
writeln('Installing, configuring Artica-postfix on this system.');
install.install_init_d();
install.PERL_LINUX_NET_DEV();
install.AWSTAT_RECONFIGURE();
fpsystem('/etc/init.d/artica-postfix restart ldap');
halt(0);
end;




if paramstr(1)='--perl-addons-repos' then
begin
install.PERL_LINUX_NET_DEV();
halt(0);
end;

if paramStr(1)='-awstats-reconfigure' then
begin
install.AWSTAT_RECONFIGURE();
halt(0);
end;

if paramStr(1)='--init-scripts' then
begin
install.install_init_d();
halt(0);
end;




if paramStr(1)='--fix-schemas' then
begin
zldap.FIX_ARTICA_SCHEMAS();
halt(0);
end;


if paramStr(1)='wget' then begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.WGET_DOWNLOAD_FILE(paramStr(2),paramStr(3));
halt(0);
end;


if paramStr(1)='username' then
begin
SYS:=TSystem.Create();
writeln(SYS.zGetUsername(paramStr(2)));
halt(0);
end;

if paramStr(1)='gpid' then
begin
SYS:=TSystem.Create();
writeln(SYS.IsGroupExists(paramStr(2)));
halt(0);
end;

if paramStr(1)='members' then
begin
SYS:=TSystem.Create();
list:=Tstringlist.Create;
SYS.MembersList(paramStr(2));
For i:=0 to list.Count-1 do
begin
writeln(list.Strings[i]);
end;

halt(0);
end;




if paramStr(1)='--sensors' then
begin
deb.sensors();
halt(0);
end;


if paramStr(1)='--mysql-reconfigure-db' then
begin
zlogs.Debuglogs('--mysql-reconfigure-db');
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.MYSQL_RECONFIGURE_DB();
halt(0);
end;


if paramStr(1)='--interfaces' then
begin
writeln(tcp.InterfacesList());
halt(0);
end;

//##############################################################################
if paramStr(1)='slapd' then
begin
GLOBAL_INI:=MyConf.Create();
if paramStr(2)='start' then
begin
zldap.LDAP_START();
halt(0);
end;

if paramStr(2)='stop' then
begin
zldap.LDAP_STOP();
halt(0);
end;

if paramStr(2)='restart' then
begin
writeln('restart slapd');
zldap.LDAP_STOP();
zldap.LDAP_START();
halt(0);
end;

writeln('Usage: ' + ExtractFilePath(ParamStr(0)) + 'artica-install slapd {start|stop|restart}');
halt(0);
end;
//##############################################################################

if ParamStr(1)='-postfix-service' then
begin
zpostfix.POSFTIX_VERIFY_MAINCF();
if ParamStr(2)='restart' then
begin
writeln('Restarting....: Postfix daemon...');
fpsystem('/usr/sbin/postfix stop >/dev/null 2>&1');
fpsystem('/usr/sbin/postfix start >/dev/null 2>&1');
halt(0);
end;
if ParamStr(2)='stop' then
begin writeln('stopping....: Postfix daemon...') end;
fpsystem('/usr/sbin/postfix ' + ParamStr(2) + ' >/dev/null 2>&1');
halt(0);
end;
//##############################################################################

if ParamStr(1)='-distri' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.LINUX_DISTRIBUTION());
halt(0);
end;

if ParamStr(1)='-exim-remove' then
begin
GLOBAL_INI:=MyConf.Create();
install.EXIM4_REMOVE();
halt(0);
end;

install.DetectDistribution();





if ParamStr(1)='-strip' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.set_FileStripDiezes(ParamStr(2));
GLOBAL_INI.Free;
halt(0);
end;

if ParamStr(1)='-upgrade' then
begin
fpsystem('/etc/init.d/artica-postfix stop');
install.InstallArtica;
fpsystem('/etc/init.d/artica-postfix start');
halt(0);
end;


if ParamStr(1)='-userq' then
begin
list:=TStringList.Create;
GLOBAL_INI:=MyConf.Create();
list.LoadFromStream(GLOBAL_INI.ExecStream(GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-filter lastten queue ' + trim(ParamStr(2)),false));
for i:=0 to list.Count -1 do
begin
writeln(list.Strings[i]);
end;
halt(0)
end;

if ParamStr(1)='-enable-services' then
begin
writeln('Enable services...');
install.EnableDaemonsAutoStart();
writeln('Enable services done...');
halt(0)
end;

if ParamStr(1)='-libc' then
begin
GLOBAL_INI:=myconf.Create;
writeln('LIBC version:'+ GLOBAL_INI.SYSTEM_LIBC_VERSION());
halt(0)
end;



if ParamStr(1)='-ip' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('IP:',GLOBAL_INI.GetIPInterface(ParamStr(2)));
halt(0);
end;

if ParamStr(1)='-UTC' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.debug:=True;
writeln('New:',GLOBAL_INI.RRDTOOL_SecondsBetween(s));
halt(0);
end;
if ParamStr(1)='-fdisk' then
begin
SYS:=Tsystem.Create;
writeln(SYS.DISKS_STATUS_DEV());
halt(0);
end;


if ParamStr(1)='-dd' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.debug:=True;
GLOBAL_INI.RRDTOOL_LOAD_AVERAGE();
GLOBAL_INI.RDDTOOL_LOAD_CPU_GENERATE();
GLOBAL_INI.RDDTOOL_LOAD_MEMORY_GENERATE();
halt(0);
end;

if ParamStr(1)='-distri' then
begin
writeln('Distribution: ',install.LinuxInfosDistri());
halt(0)
end;

if ParamStr(1)='-queuegraph' then
begin
mailgraph.MAILGRAPH_START();
halt(0)
end;

if ParamStr(1)='-yorel' then
begin
if ParamStr(2)='install' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.YOREL_RECONFIGURE('');
halt(0);
end;
end;



if ParamStr(1)='-inet' then
begin
postfix_addon:=Tpostfix_addon.Create();
GLOBAL_INI:=MyConf.Create();
writeln('Inet interfaces detected : ',GLOBAL_INI.get_LINUX_INET_INTERFACES());
postfix_addon.inet_interfaces();
GLOBAL_INI.Free;
halt(0);
end;
//##############################################################################
if ParamStr(1)='-tls' then
begin
postfix_addon:=Tpostfix_addon.Create();
postfix_addon.SetLogout();
postfix_addon.ConfigTLS();
halt(0);
end;
//##############################################################################
if ParamStr(1)='-restart' then
begin
PRC:=Tprocessinfos.Create;
PRC.AutoKill(false);
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
halt(0);
end;
//##############################################################################
if ParamStr(1)='-procmail' then
begin
if ParamStr(2)='conf' then
begin
postfix_addon:=Tpostfix_addon.Create();
postfix_addon.PROCMAIL_MASTER_CF;
postfix_addon.PROCMAIL_PROCMAILRC;
writeln('Done, please restart Postfix service');
halt(0);
end;

if ParamStr(2)='status' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.PROCMAIL_INSTALLED();
halt(0);
end;
end;

//##############################################################################


if ParamStr(1)='-autoremove' then
begin
zMENUS:=Tmenus.Create();
zMENUS.remove_Packages_addon(ParamStr(2));
halt(0);
end;

//##############################################################################

if ParamStr(1)='-roundcube' then
begin
if ParamStr(2)='plugins' then
begin
round:=troundcube.Create(SYS);
writeln(round.PluginsList());
halt(0);
end;


halt(0);
end;
//##############################################################################


if ParamStr(1)='-ldap' then
begin
GLOBAL_INI:=MyConf.Create();
REGEX.Expression:='status';
if REGEX.Exec(s) then
begin
install.LDAP_STATUS(false,true);
halt(0);
end;
REGEX.Expression:='setup';
if REGEX.Exec(s) then
begin
zMENUS:=Tmenus.Create;
zMenus.ldap_setup(true);
halt(0);
end;

REGEX.Expression:='verify';
if REGEX.Exec(s) then
begin
halt(0);
end;

REGEX.Expression:='cyrus';
if REGEX.Exec(s) then
begin install.LDAP_SET_CYRUS_ADM() end;
REGEX.Expression:='cyrus';
if REGEX.Exec(s) then
begin install.LDAP_SET_CYRUS_ADM() end;
REGEX.Expression:='fix-authd';
if REGEX.Exec(s) then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SASLAUTHD_TEST_INITD();
halt(0);
end;

halt(0);
end;

//##############################################################################
if ParamStr(1)='-mysql' then
begin
GLOBAL_INI:=MyConf.Create();


if ParamStr(2)='setadmin' then
begin
GLOBAL_INI.MYSQL_ACTION_CREATE_ADMIN(ParamStr(3),ParamStr(4));
halt(0);
end;

if ParamStr(2)='artica-filter' then
begin
install.INSTALL_FILTER_DATABASE;
halt(0);
end;


end;


if ParamStr(1)='artica-filter' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.ARTICA_FILTER_GET_ALL_PIDS());
halt(0);

end;

//############################# DNSMASQ #########################################
if ParamStr(1)='-dnsmasq' then
begin
if ParamStr(2)='version' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('dnsmasq version: "'+zdnsmasq.DNSMASQ_VERSION + '"');
halt(0);
end;


zMenus.HELP_DNSMASQ();
halt(0);

end;

//############################# POSTFIX #########################################



if ParamStr(1)='-postfix' then
begin
install.PARAMETERS_POSTFIX();
if length(ParamStr(2))>0 then
begin
writeln('Unable to determine your querie... see --help ');
zMENUS:=TMenus.Create();
ZMENUS.HELP_POSTFIX();
halt(0);
end;
halt(0);
end;

if ParamStr(1)='-send-subqueue'  then
begin
GLOBAL_INI:=MyConf.Create();
writeln('Artica-send subqueue: ' +  ParamStr(2) + '........',GLOBAL_INI.ARTICA_SEND_SUBQUEUE_NUMBER(ParamStr(2)));
end;
//################################## CYRUS #####################################
if ParamStr(1)='-cyrus'  then
begin
GLOBAL_INI:=MyConf.Create();
postfix_addon:=Tpostfix_addon.Create();

if ParamStr(2)='status' then
begin
writeln('Checking cyrus status....');
writeln(ccyrus.CYRUS_STATUS());
halt(0);
end;

if ParamStr(2)='uninstall' then begin
 install.CYRUS_UNINSTALL();
halt(0);
end;

if ParamStr(2)='ssl' then begin
   ccyrus.CYRUS_CERTIFICATE();
   halt(0);
end;

if ParamStr(2)='cyrus22' then begin
   GLOBAL_INI:=MyConf.Create();
   GLOBAL_INI.CYRUS_SET_V2('yes');
   zMENUS:=Tmenus.Create();
   zMENUS.install_Packages(true);
   install.LDAP_SET_CYRUS_ADM();
   halt(0);
end;

if ParamStr(2)='make' then  begin
   writeln('configure saslauthd and cyrus');
   install.CYRUS_IMAPD_CONFIGURE();
   halt(0);
end;
halt(0);
end;
//################################## aveserver##################################

if ParamStr(1)='-mailav'  then
begin

if ParamStr(2)='remove' then
begin
install.KAV_UNINSTALL();
halt(0);
end;


if ParamStr(2)='template' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.AVESERVER_GET_TEMPLATE_DATAS(ParamStr(3),ParamStr(4)));
halt(0);
end;
if ParamStr(2)='save_templates' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.AVESERVER_REPLICATE_TEMPLATES();
halt(0);
end;

if ParamStr(2)='help' then
begin
zMENUS.HELP_AVESERVER();
halt(0);
end;
if ParamStr(2)='pattern' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.AVESERVER_PATTERN_DATE());
halt(0);
end;

if ParamStr(2)='replicate' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.AVESERVER_REPLICATE_kav4mailservers(trim(ParamStr(3)));
halt(0);
end;



POSTFIX_ADDON:=Tpostfix_addon.Create;
POSTFIX_ADDON.install_kaspersky_mail_servers();
POSTFIX_ADDON.Free;
halt(0);
end;


if ParamStr(1)='-stat'  then
begin
GLOBAL_INI:=myconf.Create;

if ParamStr(2)='fetchmail' then
begin
GLOBAL_INI.debug:=True;
GLOBAL_INI.SYSTEM_DAEMONS_STATUS();
halt(0);
end;

if ParamStr(2)='amavis' then
begin
GLOBAL_INI:=MyConf.Create();
amavis:=tamavis.Create(GLOBAL_INI.SYS);
writeln(amavis.STATUS());
amavis.free;
GLOBAL_INI.Free;
halt(0);
end;

if ParamStr(2)='all' then
begin
GLOBAL_INI.debug:=True;
GLOBAL_INI.SYSTEM_DAEMONS_STATUS();
halt(0);
end;
halt(0);
end;






//######################## K Anti-spam #########################################
if ParamStr(1)='--kas3-status'  then
begin
zkas3:=tkas3.Create(SYS);
writeln(zkas3.STATUS());
halt(0);
end;



if ParamStr(1)='-kas'  then
begin
GLOBAL_INI:=myconf.Create;
if ParamStr(2)='remove' then
begin
install.KAS_UNINSTALL();
halt(0);
end;




install.KAS_INSTALL();
halt(0);
end;
//##############################################################################
if ParamStr(1)='-build'  then
begin
SYS:=Tsystem.Create();
SYS.ScanArticaFiles('');
SYS.BuildArticaFiles();
halt(0);
end;


if ParamStr(1)='-init'  then
begin
GLOBAL_INI:=MyConf.Create();
install.install_init_d();
halt(0);
end;


if ParamStr(1)='-quarantine' then
begin
GLOBAL_INI:=myconf.Create;
if ParamStr(2)='read' then
begin

writeln('"' + ParamStr(5) + '" user...........: ' + GLOBAL_INI.PROCMAIL_QUARANTINE_USER_FILE_NUMBER( ParamStr(5)) + ' messages number');
GLOBAL_INI.PROCMAIL_READ_QUARANTINE(StrToInt(ParamStr(3)),StrToInt(ParamStr(4)),ParamStr(5));
halt(0);

end;


writeln('"' + ParamStr(2) + '" user...........: ' + GLOBAL_INI.PROCMAIL_QUARANTINE_USER_FILE_NUMBER( ParamStr(2)) + ' messages number');


halt(0);
end;

//------------------------------------------------------------------------------

if ParamStr(1)='-selinux_off' then
begin
install.Disable_se_linux();
halt(0);
end;
//------------------------------------------------------------------------------
if ParamStr(1)='-iplocal' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_GET_ALL_LOCAL_IP;
halt(0);
end;


if ParamStr(1)='-nics' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_NETWORK_LIST_NICS;
halt(0);
end;

if ParamStr(1)='-nic-infos' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_NETWORK_INFO_NIC(ParamStr(2));
halt(0);
end;

if ParamStr(1)='-nic-configure' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_NETWORK_RECONFIGURE();
halt(0);
end;

if ParamStr(1)='-ifconfig' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.SYSTEM_NETWORK_IFCONFIG());
halt(0);
end;

if ParamStr(1)='-allips' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_ALL_IPS();
halt(0);
end;




//------------------------------------------------------------------------------
if ParamStr(1)='-cron' then
begin
if ParamStr(2)='list' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_CRON_TASKS();
halt(0);
end;

end;
//------------------------------------------------------------------------------

if ParamStr(1)='-userslist' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_USER_LIST();
halt(0);
end;

if ParamStr(1)='-replic_cron' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_CRON_REPLIC_CONFIGS();
halt(0);
end;

if ParamStr(1)='-ps' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_PROCESS_PS();
halt(0);
end;

if ParamStr(1)='--psm' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.SYSTEM_PROCESS_LIST_PID(ParamStr(2)));
halt(0);
end;

if ParamStr(1)='-pm' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(ParamStr(2) + '=',GLOBAL_INI.SYSTEM_PROCESS_MEMORY(ParamStr(2)));
halt(0);
end;



//------------------------------------------------------------------------------

if ParamStr(1)='-dspam' then
begin


writeln('unable to understand your query, type dspam not supported');
halt(0);
end;
//------------------------------------------------------------------------------
if ParamStr(1)='--lighttpd-status' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('config path........: ',zlighttpd.LIGHTTPD_CONF_PATH());
writeln('Pid path...........: ',zlighttpd.LIGHTTPD_PID());
writeln('init.d path........: ',zlighttpd.LIGHTTPD_INITD());
writeln('logs path..........: ',zlighttpd.LIGHTTPD_LOG_PATH());
writeln('socket path........: ',zlighttpd.LIGHTTPD_SOCKET_PATH());
writeln('Popup authenticate.: ',zlighttpd.IS_AUTH_LDAP());


halt(0);
end;



if  ParamStr(1)='-versions' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.CGI_ALL_APPLIS_INSTALLED();
writeln(GLOBAL_INI.ArrayList.Text);
halt(0);
end;

if  ParamStr(1)='--write-versions' then begin
    if not SYS.BuildPids() then begin halt(0) end;
    if debug then writeln('Start --write-versions');
    GLOBAL_INI:=MyConf.Create();
    if debug then writeln('Start CGI_ALL_APPLIS_INSTALLED()');
    GLOBAL_INI.CGI_ALL_APPLIS_INSTALLED();
    if debug then writeln('END CGI_ALL_APPLIS_INSTALLED()');
    zlogs.WriteToFile(GLOBAL_INI.ArrayList.Text,'/usr/share/artica-postfix/ressources/logs/global.versions.conf');
    zlogs.WriteToFile(GLOBAL_INI.ArrayList.Text,'/usr/share/artica-postfix/ressources/logs/web/global.versions.conf');
    fpsystem('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/global.versions.conf');
    fpsystem('/usr/share/artica-postfix/bin/process1 --force '+zlogs.DateTimeNowSQL()+' &');
    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --services &');
    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --setup-center &');
    halt(0);
end;


if  ParamStr(1)='-geoip-updates' then begin
    install.GEOIP_UPDATES();
    halt(0);
end;

if  ParamStr(1)='-proxy' then begin
    GLOBAL_INI:=MyConf.Create();
    GLOBAL_INI.SYSTEM_GET_HTTP_PROXY();
    halt(0);
end;

if  ParamStr(1)='-perl-upgrade' then
begin
install.PERL_UPGRADE();
halt(0);
end;

if  ParamStr(1)='-perl-patch' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.PATCHING_PERL_TO_ARTICA(ParamStr(2));
halt(0);
end;

if  ParamStr(1)='@INC' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('Perl INC folders...:');
GLOBAL_INI.PERL_INCFolders();
halt(0);
end;

if  ParamStr(1)='-mailman-check' then
begin
install.MAILMAN_CHECK_CONFIG;
halt(0)
end;

if  ParamStr(1)='-kav-install' then
begin
install.KAV_INSTALL();
halt(0)
end;


if  ParamStr(1)='-kav-milter-remove' then
begin
install.KAVMILTER_UNINSTALL();
writeln('done...');
halt(0)
end;

if  ParamStr(1)='-set-host' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.SYSTEM_SET_HOSTENAME(ParamStr(2));
writeln('Done... "'  + ParamStr(2) + '"');
halt(0)
end;






if  ParamStr(1)='-du' then
begin
SYS:=Tsystem.Create();
writeln('size of '+ ParamStr(2) + '=' + IntTostr((SYS.GetDirectoryList(ParamStr(2))div 1024)div 1000) + ' Mb');
halt(0)
end;


if  ParamStr(1)='-build-deb' then
begin
GLOBAL_INI.BuildDeb(ParamStr(2),ParamStr(3));
halt(0);
end;


if ParamStr(1)='-init-postfix' then
begin
install.POSTFIX_INIT();
halt(0);
end;


if ParamStr(1)='-init-cyrus' then
begin
install.CYRUS_IMPAD_INIT();
install.LDAP_SET_CYRUS_ADM();
halt(0);
end;

if ParamStr(1)='-squid-rrd' then
begin
zsquid.SQUID_RRD_INIT();
halt(0);
end;
if ParamStr(1)='-filestatus' then
begin
writeln('stat ' + ParamStr(2) + '...');
Writeln ('FileName      : ',ParamStr(2));
Writeln ('Has Name      : ',ExtractFileName(ParamStr(2)));
Writeln ('Has Path      : ',ExtractFilePath(ParamStr(2)));
Writeln ('Has Extension : ',ExtractFileExt(ParamStr(2)));
Writeln ('Has Directory : ',ExtractFileDir(ParamStr(2)));
Writeln ('Has Drive     : ',ExtractFileDrive(ParamStr(2)));
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.StatFile(ParamStr(2));
halt(0);
end;




if ParamStr(1)='-dkim-status' then
begin
writeln(zdkim.STATUS());
halt(0);
end;

if ParamStr(1)='-purge-bightml' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.WATCHDOG_PURGE_BIGHTML();
halt(0);
end;

if ParamStr(1)='--roundcube-permissions' then
begin
GLOBAL_INI:=myconf.Create;
round:=troundcube.Create(GLOBAL_INI.SYS);
round.SetPermissions();
halt(0);
end;

if ParamStr(1)='-balance-install' then
begin
install.BALANCE_INSTALL();
halt(0);
end;

if ParamStr(1)='-crossroads-install' then
begin
install.BALANCE_INSTALL();
halt(0);
end;

if paramStr(1)='--sqlexec' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.MYSQL_ACTION_IMPORT_DATABASE(ParamStr(2),ParamStr(3));
halt(0);
end;

if paramStr(1)='--sqlquery' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.MYSQL_ACTION_QUERY_DATABASE(ParamStr(2),ParamStr(3));
halt(0);
end;

if paramStr(1)='--collectd-status' then
begin
GLOBAL_INI:=MyConf.Create();
zcollectd:=tcollectd.Create(GLOBAL_INI.SYS);
writeln(zcollectd.STATUS());
halt(0);
end;

if paramStr(1)='--perl-f' then
begin
writeln('found:=',install.PERL_FIND_FILES(paramstr(2)));
halt(0);
end;

if ParamStr(1)='-pfqueue-install' then
begin
install.PFQUEUE_INSTALL();
halt(0);
end;


if ParamStr(1)='-squid-install' then
begin
fpsystem('/usr/share/artica-postfix/bin/artica-make APP_SQUID');
halt(0);
end;




if ParamStr(1)='-squid-status' then
begin
writeln(zsquid.SQUID_STATUS());
halt(0);
end;
if ParamStr(1)='--squid-reload' then
begin
zsquid.SQUID_RELOAD();
halt(0);
end;

if ParamStr(1)='--mailflt3' then
begin
zkas3:=tkas3.Create(SYS);
zkas3.mailflt3();
halt(0);
end;
if ParamStr(1)='--kas3-version' then
begin
zkas3:=tkas3.Create(SYS);
writeln(zkas3.VERSION()+';'+zkas3.PATTERN_DATE());
halt(0);
end;








if ParamStr(1)='-init-artica' then
begin
install.INIT_ARTICA();
halt(0);
end;

if ParamStr(1)='-dansguardian-install' then
begin
install.DANSGUARDIAN_INSTALL();
halt(0);
end;

if ParamStr(1)='linux-net-dev' then
begin
zMENUS:=Tmenus.Create();
install.PERL_LINUX_NET_DEV();
halt(0);
end;

if paramStr(1)='-dansguardian-mem' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('Dansguardian memory:',IntToStr(global_ini.SYSTEM_PROCESS_MEMORY(zdansguardian.DANSGUARDIAN_PID())));
halt(0);
end;

if paramStr(1)='-perl-addons' then
begin
zMENUS:=Tmenus.Create();
install.PERL_ADDONS();
halt(0);
end;

if paramStr(1)='-awstats-install' then
begin
install.AWSTATS_INSTALL();
halt(0);
end;


if paramStr(1)='-rrd-install' then
begin
zMENUS:=Tmenus.Create();
install.RRD_INSTALL();
halt(0);
end;

if ParamStr(1)='mysql-ps' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('SYSTEM_PROCESS_LIST_PID ->');
writeln(GLOBAL_INI.SYSTEM_PROCESS_LIST_PID('/opt/artica/libexec/mysqld'));
halt(0)
end;


if ParamStr(1)='--fromcdkav' then
begin
install.INSTALL_FROM_CD_KAV();
halt(0);
end;



if ParamStr(1)='--mirror' then
begin
install.Mirror();
halt(0);
end;

if ParamStr(1)='-perl-db-file' then
begin
install.PERL_DBD_FILE();
halt(0);
end;



if ParamStr(1)='-unrar-install' then
begin
install.UNRAR_INSTALL();
halt(0);
end;


if ParamStr(1)='-mem' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('proccess ',ParamStr(2),'=',GLOBAL_INI.SYSTEM_PROCESS_MEMORY(ParamStr(2)) , ' ko');
writeln('Status ',GLOBAL_INI.SYSTEM_PROCESS_STATUS(ParamStr(2)));
halt(0);
end;


if ParamStr(1)='--generate-status' then begin
   if SYS.isoverloaded() then begin
      zlogs.Debuglogs('WARNING !!!! --generate-status system overloaded');
      exit;
   end;

   if not SYS.BuildPids() then begin
      zlogs.Debuglogs('--generate-status already instance running');
      halt(0);
   end;

   if SYS.croned_seconds(20) then begin
      zlogs.Debuglogs('--generate-status too short time to execute this process');
      halt(0);
   end;
    GLOBAL_INI:=myconf.Create();
    GLOBAL_INI.WRITE_STATUS();
    halt(0);
end;

if ParamStr(1)='--generate-status-forced' then begin
   if SYS.isoverloadedTooMuch() then begin halt(0) end;
   if not SYS.BuildPids() then begin
      zlogs.Debuglogs('--generate-status-forced already instance running');
      halt(0);
   end;
   GLOBAL_INI:=myconf.Create();
   GLOBAL_INI.WRITE_STATUS();
   halt(0);
end;




if ParamStr(1)='--ls' then
begin
SYS:=TSystem.Create();
writeln('initialize : ' +  ParamStr(2));
sys.RecusiveListFiles(ParamStr(2));
writeln(sys.DirListFiles.Text);
halt(0);
end;



if ParamStr(1)='-patch-perlconf' then
begin
install.PATCHING_Config_heavy_pl();
halt(0);
end;

if ParamStr(1)='--mydiff' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.deb_files_extists_between(ParamStr(2),ParamStr(3));
halt(0);
end;

if ParamStr(1)='--saver' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('Spamassassin version:',  zSpamass.SPAMASSASSIN_VERSION(),' ',zSpamass.SPAMASSASSIN_PATTERN_VERSION());
halt(0);
end;

if ParamStr(1)='--clamver' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('Spamassassin version:',  zClam.CLAMAV_VERSION(),' Pattern:',zClam.CLAMAV_PATTERN_VERSION(),' bin:',zclam.CLAMAV_BINVERSION());
halt(0);
end;

if ParamStr(1)='-p0f-install' then
begin
zMenus:=Tmenus.Create();

install.POF_INSTALL();
halt(0);
end;


if ParamStr(1)='--find-pid' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('Found:',GLOBAL_INI.SYSTEM_PROCESS_LIST_PID(ParamStr(2)));
writeln('');
writeln('');
halt(0);
end;

if ParamStr(1)='-ntpd-status' then
begin
GLOBAL_INI:=MyConf.Create();
writeln('Found:',zntpd.NTPD_STATUS());
writeln('');
writeln('');
halt(0);
end;

if ParamStr(1)='-install-perl-cyrus' then
begin
GLOBAL_INI:=MyConf.Create();
zMenus:=Tmenus.Create();

install.PERL_CYRUS_IMAP();
writeln('');
writeln('');
halt(0);
end;


if ParamStr(1)='-ntpd-install' then
begin
GLOBAL_INI:=MyConf.Create();
zMenus:=Tmenus.Create();

install.NTPD_INSTALL();
writeln('');
writeln('');
halt(0);
end;

if ParamStr(1)='-listnics' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.IPTABLES_LIST_NICS());
writeln('');
writeln('');
halt(0);
end;


if ParamStr(1)='--backup' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.BACKUP_MYSQL();
writeln('');
writeln('');
halt(0);
end;





if ParamStr(1)='--syslog' then
begin
zlogs.Syslogs(paramstr(1));
writeln('');
writeln('');
halt(0);
end;

if ParamStr(1)='--stat' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.StatFile(ParamStr(2));
writeln('');
writeln('');
halt(0);
end;



if paramStr(1)='--usb-backup' then
begin
if SYS.isoverloaded() then
begin
zlogs.Debuglogs('--usb-backup exiting, system overload');
exit;
end;

if SYS.RotationSeconds(5) then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.BACKUP_USB();
end;
halt(0);
end;


if paramStr(1)='--graphdefang-gen' then
begin
if SYS.RotationSeconds(60) then
begin
zmimedefang.GRAPHDEFANG_GEN();
end;
halt(0);
end;


if ParamStr(1)='--startall' then
begin
GLOBAL_INI:=MyConf.Create();
if ParamStr(2)='--force' then
begin
GLOBAL_INI.START_ALL_DAEMONS();
GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
halt(0);
end;

if SYS.RotationSeconds(30) then
begin
if SYS.BuildPids() then
begin
zlogs.Debuglogs('#######################################');
zlogs.Debuglogs('######## WATCHDOG #####################');
zlogs.Debuglogs('(--startall)::Running watchdog process');
GLOBAL_INI.START_ALL_DAEMONS();
GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
zlogs.Debuglogs('#######################################');
zlogs.Debuglogs('(--startall):: End');
zlogs.Debuglogs('');
end
else
begin
zlogs.Debuglogs('(--startall):: SYS.BuildPid report false...');
end;
end;
halt(0);
end;

if ParamStr(1)='--ParseMyqlQueue' then
begin
if SYS.BuildPids() then
begin
if SYS.RotationSeconds(30) then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.ParseMyqlQueue();
zlogs.Debuglogs('');
zlogs.Debuglogs('');
end;
end;
halt(0);
end;

if ParamStr(1)='--ln' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.ln(paramStr(2),paramStr(3));
writeln('');
writeln('');
halt(0);
end;


if ParamStr(1)='--dir' then
begin
SYS:=TSystem.Create;
SYS.DirDir(ParamStr(2));
writeln(SYS.DirListFiles.Text);
writeln('');
halt(0);
end;

if ParamStr(1)='--eventsnum' then
begin
zlogs.SYS_EVENTS_ROWNUM();
writeln('');
halt(0);
end;


if ParamStr(1)='--split' then
begin
GLOBAL_INI:=MyConf.Create();
GLOBAL_INI.splitexample(ParamStr(2),ParamStr(3));
writeln('');
halt(0);
end;


if ParamStr(1)='--roundcube-status' then
begin
round:=troundcube.Create(SYS);
writeln(round.STATUS());
writeln('version ',round.VERSION());
writeln('int version ',round.INTVERSION(round.VERSION()));
halt(0);
end;


if ParamStr(1)='--patterns-versions' then
begin
GLOBAL_INI:=MyConf.Create();
writeln(GLOBAL_INI.STATUS_PATTERN_DATABASES());
halt(0);
end;


if ParamStr(1)='--help' then
begin
GLOBAL_INI:=MyConf.Create();
zMENUS:=TMenus.Create();
writeln(GLOBAL_INI.get_ARTICA_PHP_PATH());
writeln('');
writeln('');

writeln('Install applications :');
writeln('Execute external tool artica-make --help');
writeln('');

writeln('');
writeln(chr(9) +'Artica-postfix daemon commands:');
writeln(chr(9)+chr(9)+'-shutdown        : Stopping artica-postfix daemon');
writeln(chr(9)+chr(9)+'-shutdown force  : force killing artica-postfix daemon and all threads');
writeln(chr(9)+chr(9)+'-checkstatus     : Check status');
writeln(chr(9)+chr(9)+'-watchdog        : Check status and start daemon if down');




writeln('') ;
writeln(chr(9) +'Artica-install usages: -init');
writeln('');
writeln(chr(9) + 'artica usages:................................................');
writeln(chr(9)+chr(9)+'-init            : Configure artica-postfix daemon in init.d init scripts.');
writeln('');


writeln('');
writeln(chr(9) + 'Status usages:');
writeln(chr(9)+chr(9)+'-stat fetchmail...........: Fetchmail status.');
writeln(chr(9)+chr(9)+'--status..................: Get all status of supported processes');
writeln(chr(9)+chr(9)+'--squid-status............: Get squid status');
writeln(chr(9)+chr(9)+'--fdm-status..............: Get FDM status');
writeln(chr(9)+chr(9)+'--kretranslator-status....: Get status of Kaspersky retranslator services');

writeln(chr(9)+chr(9)+'--obm-status..............: Get status of OBM');
writeln(chr(9)+chr(9)+'--mailsync-status.........: Get mailsync status');
writeln(chr(9)+chr(9)+'--dhcp3-status............: Get DHCP Server status');
writeln(chr(9)+chr(9)+'--ntpd-status.............: Get NTPD Server status');



writeln('');
writeln(chr(9) + 'rsync usages:');
writeln(chr(9)+chr(9)+'--reload-rsync.................: reload rsyncd.');




writeln('');
writeln(chr(9) + 'webmail usages:,(see artica-make)');

writeln(chr(9)+chr(9)+'--roundcube-permissions.......: Set folder permission in RoundCube web folders');
writeln(chr(9)+chr(9)+'--roundcube-status............: Get status of WebMail roundcube');
writeln(chr(9)+chr(9)+'-roundcube plugins............: Get plugins list');
writeln('');
writeln('');
writeln(chr(9) + 'LDAP usages: -ldap install|cyrus|status|cyrus');
writeln(chr(9)+chr(9)+'slapd {start|stop}start/stop ldap if installed via compilation mode');
writeln(chr(9)+chr(9)+'--fix-schemas (on|off)..: Disable/Enable artica schema''s symbolic links ');
writeln(chr(9)+chr(9)+'install.................: Configure ldap settings');
writeln(chr(9)+chr(9)+'verify..................: verify mandatory settings before start LDAP');
writeln(chr(9)+chr(9)+'setup...................: Restart ldap part of setup ');
writeln(chr(9)+chr(9)+'status..................: Settings status');
writeln(chr(9)+chr(9)+'default.................: Create a new config file');
writeln(chr(9)+chr(9)+'cyrus...................: Set cyrus administrator');
writeln(chr(9)+chr(9)+'fix-authd...............: Parse init.d saslauthd to verify if ldap is enabled');
writeln(chr(9)+chr(9)+'--ldap-reconfigure......: Reconfigure ldap database');
writeln(chr(9)+chr(9)+'--slpapindex............: Index ldap database');


writeln('');
writeln(chr(9) + 'OBM2 usages:(see artica-make too)');
writeln(chr(9)+chr(9)+'--obm2-apache-config..........: Build apache configuration');
writeln(chr(9)+chr(9)+'--obm2-version................: Get current version');

writeln('');
writeln(chr(9) + 'Squid usages');
writeln(chr(9)+chr(9)+'--sarg-config.................: Build SARG configuration');
writeln(chr(9)+chr(9)+'--sarg........................: Execute SARG');
writeln(chr(9)+chr(9)+'--sarg-scan...................: List dates folders');

writeln('');
writeln(chr(9) + 'Dansguardian usages');
writeln(chr(9)+chr(9)+'--dansguardian-exceptions.....: Build exceptions list');
writeln(chr(9)+chr(9)+'--dansguardian-verif..........: Verif configuration files');
writeln(chr(9)+chr(9)+'--dansguardian-stats..........: Execute statistics');
writeln(chr(9)+chr(9)+'--dansguardian-status.........: Get service status');





writeln('');
writeln(chr(9) + 'Cyrus usages:................................................');
writeln(chr(9)+chr(9)+'--cyrus-checkconfig...........: Checking directories, rights and cyrus configuration file');
writeln(chr(9)+chr(9)+'--cyrus-recoverdb.............: Try to recover databases after DBERROR db4: PANIC');
writeln(chr(9)+chr(9)+'-ldap cyrus                   : Adding cyrus administrator in ldap server');
writeln(chr(9)+chr(9)+'-cyrus cyrus22                : install cyrus 2.2.x');
writeln(chr(9)+chr(9)+'-cyrus make                   : configure saslauthd and cyrus when compiling cyrus-imap (make way)');
writeln(chr(9)+chr(9)+'-cyrus ssl                    : Generate openssl key for crypted protocols');
writeln(chr(9)+chr(9)+'--imap-tests user password....: test imap user');
writeln(chr(9)+chr(9)+'--delete-mailbox..............: Delete mailbox by uid given');



writeln('');
writeln(chr(9) + 'procmail usages:(depreciated).................................');
writeln(chr(9)+chr(9)+'-procmail conf                : integrate procmail in mail process');
writeln(chr(9)+chr(9)+'-procmail status              : give status of procmail...');


writeln('');
writeln(chr(9) + 'openvpn usages:..............................................');
writeln(chr(9)+chr(9)+'--openvpn-build-certificate   : Create server certificates');


writeln('');
writeln(chr(9) + 'openldap usages:..............................................');
writeln(chr(9)+chr(9)+'--ldap-ssl                    : Create server certificates for openldap server');

writeln('');
writeln(chr(9) + 'php usages:....................................................');
writeln(chr(9)+chr(9)+'--php-infos                   : What infos artica needs ?');
writeln('');
writeln(chr(9) + 'Spamassassinn usages:..........................................');
writeln(chr(9)+chr(9)+'--spamassassin-reload         : reloading spamassassin');



writeln('');
writeln(chr(9) + 'SAMBA/Cups usages:............................................');
writeln(chr(9)+chr(9)+'--samba-reconfigure...........: Checking directories, rights, libnss and samba configuration file');
writeln(chr(9)+chr(9)+'--cups-drivers................: List cups drivers');
writeln(chr(9)+chr(9)+'--cups-windows................: Install windows drivers');
writeln(chr(9)+chr(9)+'--cups-gutenprint.............: Install/check gutenprint printers drivers');
writeln(chr(9)+chr(9)+'--cups-config.................: writing cups configuration');
writeln(chr(9)+chr(9)+'--cups-delete-all-printers....: Delete all installed printers');
writeln(chr(9)+chr(9)+'--samba-add-computer computer.: Adding computer into LDAP samba branch');

writeln('');


zMENUS.HELP_POSTFIX() ;

zMENUS.HELP_AVESERVER();

zMENUS.HELP_DSPAM();



writeln(chr(9)+chr(9)+'-kas             : Install and configure Kaspersky Anti-Spam gateway');
writeln(chr(9)+chr(9)+'-kas conf [path] : Apply rules stored in specific folder [path]');
writeln(chr(9)+chr(9)+'-kas reconfigure : reconfigure Kaspersky Anti-Spam gateway');
writeln(chr(9)+chr(9)+'-kas remove      : uninstall Kaspersky Anti-Spam gateway');
writeln(chr(9)+chr(9)+'-kas delete      : remove Kaspersky Anti-Spam gateway from master.cf');

writeln('');
writeln(chr(9) + 'Quarantine usages');
writeln(chr(9)+chr(9)+'-quarantine user : get infos from user quarantine');
writeln(chr(9)+chr(9)+'-quarantine read : read quarantine infos from user quarantine');
writeln(chr(9)+chr(9)+'                (from file number)');
writeln(chr(9)+chr(9)+'                (to file number)');
writeln(chr(9)+chr(9)+'                (user)');
writeln(chr(9)+chr(9)+'example -quarantine read 0 100 user1 ');
writeln(chr(9)+chr(9)+'--quarantines-schedule........: schedule quarantines HTML report generation in crontab');
writeln(chr(9)+chr(9)+'--quarantines-clean-disk......: Delete quarantined files stored on disk');



writeln('');
writeln(chr(9) + 'Mysql usages');

writeln(chr(9)+chr(9)+'--change-mysqlroot......: Change mysql root username and password');
writeln(chr(9)+chr(9)+'-mysql status...........: status of required settings');
writeln(chr(9)+chr(9)+'-mysql setadmin.........: create mysql administrator..');
writeln(chr(9)+chr(9)+ chr(9) + 'ex: -mysql setadmin administrator password');
writeln(chr(9)+chr(9)+'-mysql artica-filter....: Creating database for artica-filter');
writeln(chr(9)+chr(9)+'-mysql-install..........: Install Mysql server');
writeln(chr(9)+chr(9)+'-php-mysql..............: recompile artica php for mysql');
writeln(chr(9)+chr(9)+'--sqlexec filname database: Execute SQL commands for database to "file"');
writeln(chr(9)+chr(9)+'--sqlquery database ''command'' : query SQL command from "command" in "database"');


writeln('');
writeln(chr(9) + 'Web server apache 2 usages');
writeln(chr(9)+chr(9)+'--apache-ssl-cert......: Generate Apache certificates for the Administration console in apache mode');


writeln('');
writeln(chr(9) +'Network commands:');
writeln(chr(9)+chr(9)+'-ip NIC..........: get IP from NIC (eth0, eth1...)');
writeln(chr(9)+chr(9)+'-iplocal.........: Get local IP');
writeln(chr(9)+chr(9)+'-nics............: Get list of all eth* set on this computer');
writeln(chr(9)+chr(9)+'-nic-infos [nics]: Get parameters of the interface specified');
writeln(chr(9)+chr(9)+'-nic-configure...: apply settings saved from artica ');
writeln(chr(9)+chr(9)+'-ifconfig........: Get maximum informations of all nic');
writeln(chr(9)+chr(9)+'-allips..........: Get all ip on this computer');
writeln(chr(9)+chr(9)+'--ipstat [nic]...: Get IP Informations from NIC (eth0, eth1...)');
writeln(chr(9)+chr(9)+'--setip [nic]....: Save IP informations with');
writeln(chr(9)+chr(9)+'.................  IP netmask gateway dhcp(yes|no)');

writeln('');
writeln(chr(9) +'Install commands (compilation mode) use it for 1.1.x Artica versions :');
writeln(chr(9)+chr(9)+'--c-icap-configure........: Reconfigure c-icap');
writeln(chr(9)+chr(9)+'--bindrrd.................: Generate bindrrd statistics');
writeln(chr(9)+chr(9)+'--fdm-perform path........: Launch fdm fetchmails from config path for uid user');







writeln('');
writeln(chr(9) +'Artica-install others commands:');
writeln(chr(9)+chr(9)+'-UTC.............: test UTC conversion date');
writeln(chr(9)+chr(9)+'-UTC date........: test UTC conversion date input');
writeln(chr(9)+chr(9)+'-du [path].......: Get directory size of [path]');
writeln(chr(9)+chr(9)+'-mailgraph.......: installing mailgraph component');
writeln(chr(9)+chr(9)+'-distri..........: Get distribution name');
writeln(chr(9)+chr(9)+'-libc............: Get libc version');
writeln(chr(9)+chr(9)+'-check-repos.....: Testing required repostirories');
writeln(chr(9)+chr(9)+'-check-befor.....: Check settings before get repositories');
writeln(chr(9)+chr(9)+'-check-pack......: Check installed packages in debug mode');
writeln(chr(9)+chr(9)+'-selinux_off.....: Disabled systems armors securities');
writeln(chr(9)+chr(9)+'-enable-services.: Activate required service for full mail server');
writeln(chr(9)+chr(9)+'-cron list.......: List all cron task in xml "like" mode');
writeln(chr(9)+chr(9)+'-userslist.......: List all users stored in this system');
writeln(chr(9)+chr(9)+'-replic_cron.....: Replic cron settings from artica to crond.d');
writeln(chr(9)+chr(9)+'-ps..............: Parse system tasks');
writeln(chr(9)+chr(9)+'-pm PID..........: Get memory in Ko of a defined process pid');
writeln(chr(9)+chr(9)+'-watch-filter....: Execute watchdog feature on artica-filter process');
writeln(chr(9)+chr(9)+'-versions........: Get versions of all installed products');
writeln(chr(9)+chr(9)+'-proxy...........: Get proxy settings in env');
writeln(chr(9)+chr(9)+'-exim-remove.....: remove Exim4');
writeln(chr(9)+chr(9)+'-perl-upgrade....: Upgrade perl (if required)');
writeln(chr(9)+chr(9)+'@INC.............: Get perl folders');
writeln(chr(9)+chr(9)+'-set-host........: Change hostname');
writeln(chr(9)+chr(9)+'--set-hostname...: Change hostname');
writeln(chr(9)+chr(9)+'-mailman-check...: Check mailman config');
writeln(chr(9)+chr(9)+'--usb-backup.....: Perform backup on USB device (if set in interface)');
writeln(chr(9)+chr(9)+'--start-minimum-daemons: Start mandatories daemons to run Artica');
writeln(chr(9)+chr(9)+'--verify-artica-iso....: Check mandories settings if came From Artica-iso');
writeln(chr(9)+chr(9)+'--php-include..........: Check include paths in php.ini');

writeln(chr(9)+chr(9)+'--collectd-status.......: Show collectd status');
writeln(chr(9)+chr(9)+'--mailman-status........: Show Mailman status');


writeln(chr(9)+chr(9)+'-awstats-reconfigure....: reconfigure awstats');
writeln(chr(9)+chr(9)+'--awstats-generate......: Create statistics');

writeln(chr(9)+chr(9)+'-kav-milter-remove: remove Kaspersky for mail server (milter edition)');
writeln(chr(9)+chr(9)+'-web-configure...: Reconfigure artica-web');
writeln(chr(9)+chr(9)+'-init-postfix...........: Initialize postfix Installation after the debian package');
writeln(chr(9)+chr(9)+'-squid-rrd..............: Initialize Squid rrd tool for statistics');
writeln(chr(9)+chr(9)+'-filestatus file........: Freepascal stat file');
writeln(chr(9)+chr(9)+'-watch-queue............: Release blocked mails in artica-filter queue');
writeln(chr(9)+chr(9)+'-purge-bightml..........: Scan the bightml queue and delete old files and directories');
writeln(chr(9)+chr(9)+'-reconfigure-master.....: reconfigure /etc/postfix/master.cf');


writeln(chr(9)+chr(9)+'-init-roundcube.........: Reconfigure mysql and roundcube after unpack repositories');
writeln(chr(9)+chr(9)+'-pfqueue-install........: Install pfqueue tool');
writeln(chr(9)+chr(9)+'-perl-patch directory...: scan a directory and change perl interpreter path ');
writeln(chr(9)+chr(9)+'-cross-rq...............: Send slaves requests to master');
writeln(chr(9)+chr(9)+'--mirror [path].........: translate all packages from internet to disk');


writeln(chr(9)+chr(9)+'-mem [PID]..........: Get full memory used by PID and all fork processes');
writeln(chr(9)+chr(9)+'--status............: Get full status of services (in ini file structure)');
writeln(chr(9)+chr(9)+'-ntpd-status........: Get NTPD Status (in ini file structure)');
writeln(chr(9)+chr(9)+'--find-pid [pattern]: Found pids of a local process from specific "pattern" ');
writeln(chr(9)+chr(9)+'--mydiff path1 path2: Found files already exists in 2 paths specified');
writeln(chr(9)+chr(9)+'--backup............: Found files already exists in 2 paths specified');
writeln(chr(9)+chr(9)+'--stat file.........: artica stat file...');
writeln(chr(9)+chr(9)+'-lighttpd-cert......: Generate SSL certificate for lighttpd');
writeln(chr(9)+chr(9)+'--change-certificate: Generate All SSL certificate for installed products');
writeln(chr(9)+chr(9)+'--usbscan...........: Scan usb devices and output informations (--verbose supported)');

writeln(chr(9)+chr(9)+'username [uid]......: Get the username from uid number given');
writeln(chr(9)+chr(9)+'--whereis-maillog....: Determine where mail.log is located...');

writeln(chr(9)+chr(9)+'--patterns-versions..: Get antivirus/antispam patterns versions/date');
writeln(chr(9)+chr(9)+'--dns-keygen key domain --verbose: Generate a Bind9 DNS key');




halt(0);
exit;
end;




writeln('Your command line "' + ParamStr(1) + ' ' + ParamStr(2) + '" is not understood');
writeln('use --help to see orders');
install.Free;
halt(0);



end.

