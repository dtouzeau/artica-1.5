unit class_install;
     {$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,Process,strutils,IniFiles,RegExpr in 'RegExpr.pas',artica_cron,opengoo,
  BaseUnix,unix,global_conf,zsystem,logs,geoip,debian,spamass,openldap,clamav,cyrus,squid,postfix_class,samba,awstats,process_infos,pureftpd,ntpd,spfmilter,
  mailgraph_daemon, miltergreylist,lighttpd, roundcube,dansguardian,kav4samba,mimedefang,stunnel4,dkimfilter,kav4proxy,bind9,obm,mysql_daemon,p3scan,syslogng,openvpn,cups,
  jcheckmail,dhcp_server,dstat,rsync,smartd,tcpip,policyd_weight,apache_artica,autofs,assp,pdns,gluster,nfsserver,zabbix,hamachi,postfilter, vmwaretools,zarafa_server,monit,wifi,
  emailrelay,mldonkey,backuppc,kav4fs,ocsi,ocsagent,sshd,auditd,squidguard_page,dkfilter,ufdbguardd,squidguard,framework,dkimmilter,dropbox,articapolicy,virtualbox,tftpd,crossroads,articastatus,articaexecutor,articabackground,pptpd,
  apt_mirror,cluebringer,apachesrc,sabnzbdplus,fusermount,vnstat,munin,greyhole,iscsitarget,postfwd2,snort,greensql,amanda,
  mailarchiver in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mailarchiver.pas',ddclient,tomcat,openemm,
  kas3         in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kas3.pas',
  kavmilter    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kavmilter.pas',
  dnsmasq      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/dnsmasq.pas',
  collectd     in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/collectd.pas',
  fetchmail    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/fetchmail.pas',
  mailspy_milter in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mailspy_milter.pas',
  amavisd_milter in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/amavisd_milter.pas',
  bogom,saslauthd,
  kretranslator    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kretranslator.pas',
  dotclear    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/dotclear.pas',
  mailmanctl     in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mailmanctl.pas';

  type
  Tclass_install=class

  
private
       GLOBALINI:myconf;
       CHEK_LOCAL_VERSION_BEFORE:integer;
       LD_LIBRARY_PATH:string;
       CPPFLAGS:string;
       spamass:Tspamass;
       postfix:tPostfix;
       ldap:Topenldap;
       CCYRUS:Tcyrus;
       openssl_path:string;
       zsquid:Tsquid;
       samba:Tsamba;
       awstats:Tawstats;
       SYS           :Tsystem;
       zmysql:tmysql_daemon;
       zmailman:tmailman;
       zopenvpn:topenvpn;
       BIGDEBUG:boolean;
       function LinuxRPMDEB():string;
       procedure ARTICA_WEB_CONFIG();
       procedure DCC_INSTALL();

       function mail_log_path():string;
       procedure ExecuteNoWait(cmd_local:string);
       function AnalyseDebianRepositoriesPackages(application_requires:string):string;
       procedure ShowScreen(line:string);
       procedure REPLACE_CONFIG(regex_string:string;LineToReplace:string;FileSource:string);
       function chkconfig(service_name:string;onOrOff:string):boolean;
       function COMPILE_GENERIC_APPS(APPS:string;package_name:string;package_name_suffix:string):string;
       function COMPILE_GENERIC_APPS_LOCALE(tarball:string;package_name:string;DirectoryExtracted:string):string;
       function COMPILE_CHECK_VERSION(APPS:string;package_name:string;local_version:string):boolean;
       procedure LIBNCURSES_INSTALL();
       procedure CONFIGURE_CPAN();


public


      debug:boolean;
      mquery : string;
      sql_server,sql_password,sql_database,sql_user:string;
      host, user, passwd, Query,database : Pchar;
      OsDistri:string;
      UpdateTool_system:string;
      default_install:boolean;
      constructor Create;
      procedure Execute(cmd_local:string);
      PROCEDURE backup_config();
      procedure install_init_d();
      function  LinuxDistrReal():string;
      function  WichUpdateTools():boolean;
      function  DetectPostfix():boolean;
      PROCEDURE InstallArticaDaemon();
      PROCEDURE logs(zText:string);
      procedure InstallArtica();
      function  GetParentFolder(folder:string):string;
      procedure DetectDistribution();
      function  LinuxInfosDistri():string;
      function  ReadFileIntoString(path:string):string;
      function  IsRoot():boolean;
      function  EnableDaemonsAutoStart():boolean;
      function  TESTING_INTERNET():boolean;
      procedure INSTALL_FROM_CD_KAV();
      procedure ChangeAllCertificates();

      function  LDAP_STATUS(Continue:boolean;show:boolean):boolean;
      procedure          INSTALL_HOTWAYD();





      function           LDAP_SET_CYRUS_ADM():boolean;


      procedure ARTICA_CD_KASPERSKY();
      procedure PHP_INI();
      procedure PERL_UPGRADE();
      procedure PERL_LINUX_NET_DEV();
      procedure PERL_HTML_PARSER();
      procedure PERL_COMPRESS_ZLIB();
      procedure PERL_HTML_TAGSET();
      procedure PERL_URI();
      procedure PERL_LIBWWW();
      procedure PERL_ADDONS();
      procedure PERL_GENERIC_INSTALL(indexWeb:string;whatToCheck:string);
      procedure PERL_DBD_SQLITE();
      procedure PERL_DBD_FILE();
      procedure PERL_NET_DNS();
      procedure PERL_MAIL_DomainKeys();
      procedure PERL_CRYPT_OPENSSL();
      procedure PERL_BERKELEYDB();
      procedure PERL_CYRUS_IMAP();
      function  PERL_FIND_FILES(filename:string):boolean;
      procedure UNRAR_INSTALL();
      procedure PERL_LDAP();
      procedure PATCHING_Config_heavy_pl();
      procedure PERL_DBD_MYSQL();
      
      

      function  NETCAT_INSTALL():boolean;
      procedure INSTALL_AWSTATS();

      function  DNSMASQ_RECONFIGURE():boolean;
      procedure DNSMASQ_INSTALL();
      procedure LIB_PCRE_INSTALL();
      procedure DANSGUARDIAN_INSTALL();
      procedure SPAMASSASSIN_INSTALL();

      
      function  INSTALL_FILTER_DATABASE:boolean;
      procedure SET_DEFAULT_CONFIG();
      procedure RestartAllservices;
      procedure KAS_INSTALL();
      procedure AWSTATS_INSTALL();
      procedure AWSTAT_RECONFIGURE();
      procedure KAS_UNINSTALL();
      procedure KAV_UNINSTALL();
      procedure KAV_INSTALL();
      procedure KAV_CONFIGURE();
      procedure KAVMILTER_UNINSTALL();
      procedure MAILUTILS_INSTALL();

      procedure POF_INSTALL();
      

      procedure MAILGRAPH_REMOVE();

      procedure GEOIP_UPDATES();
      PROCEDURE GEOIP_PERL_INSTALL();
      PROCEDURE GEOIP_LIB_INSTALL();
      
      procedure YOREL_REMOVE();
      procedure QUEUEGRAPH_REMOVE();
      procedure QUEUEGRAPH_INSTALL();
      procedure KERBEROS_INSTALL();
      procedure MAILMAN_CHECK_CONFIG;


      procedure MYSQL_INSTALL();
      procedure FDM_INSTALL();

      //Kav4samba
      procedure KAV4SAMBA_INSTALL();

      procedure               CYRUS_UNINSTALL();
      function                CYRUS_IMAPD_CONFIGURE():boolean;
      procedure               CYRUS_IMPAD_INIT();
      procedure               CYRUS_CHECK();
      
      procedure               FETCHMAIL_AS_DAEMON();

      procedure               LDAP_CREATE_DATABASE();
      procedure               CONFIGURE_HOTWAYD();
      
      function  CommandLineIsExists(regex:string):boolean;
      function  MYSQL_ADMIN():boolean;
      procedure NTPD_INSTALL();
      
      
      function GenerateCertificateFileName(PathToConfigFile:string):string;
      function  POSTFIX_SET_SASL():boolean;
      procedure POSTFIX_CONFIGURE_MAIN_CF();
      procedure POSTFIX_INIT();
      
      
      PROCEDURE Disable_se_linux();
      procedure BALANCE_INSTALL();
      procedure CHANGE_MAKE_DEF(path:string;key:string;value:string);


      procedure GSL_INSTALL();
      procedure LIBSSL_INSTALL();
      procedure LIBICONV_INSTALL();
      procedure LIB_ART_LGPL();
      procedure LIB_PNG();
      procedure libspf2_INSTALL();
      procedure LIBDBI_INSTALL();
      procedure LIBCAP_INSTALL();
      procedure LIBZ_INSTALL();
      procedure LIFREETYPE_INSTALL();
      procedure CLAMAV_RECONFIGURE();

      procedure               BOGOFILTER_INSTALL();
      procedure               RRD_INSTALL();
      procedure               BERKLEY_INSTALL();
      procedure               MYSQL_RECONFIGURE();
      procedure               PFQUEUE_INSTALL();
      procedure               INIT_ARTICA();
      
      procedure EXIM4_REMOVE();
      procedure SENDMAIL_REMOVE();
      PROCEDURE Mirror();
      procedure Free;
      PROCEDURE SHUTDOWN();
      
      
      PROCEDURE PARAMETERS_POSTFIX();
      PROCEDURE PARAMETERS_AWSTATS();
      procedure START_SERVICES_PARAMETERS();
      procedure OMA_INSTALL();

END;

implementation

constructor Tclass_install.Create;
    var
       currentpath:string;
       sysclass:Tsystem;
begin
       BIGDEBUG:=false;
     if ParamStr(1)='--verbose2' then BIGDEBUG:=true;
     if ParamStr(2)='--verbose2' then BIGDEBUG:=true;
     if ParamStr(3)='--verbose2' then BIGDEBUG:=true;
     if ParamStr(4)='--verbose2' then BIGDEBUG:=true;
     if ParamStr(5)='--verbose2' then BIGDEBUG:=true;


      if BIGDEBUG then writeln('Tclass_install.Create:: -> forcedirectories');
       forcedirectories('/etc/artica-postfix');

       CHEK_LOCAL_VERSION_BEFORE:=0;
       if BIGDEBUG then writeln('Tclass_install.Create:: -> Loading class GLOBALINI');
       GLOBALINI:=myconf.Create;
       if BIGDEBUG then writeln('Tclass_install.Create:: -> Loading class SYS');
       SYS:=GLOBALINI.SYS;
       LD_LIBRARY_PATH:='LD_LIBRARY_PATH="/opt/artica/lib:/opt/artica/db/lib:/opt/artica/mysql/lib/mysql" ';
       CPPFLAGS:=' CPPFLAGS="-I/opt/artica/include/ -I/opt/artica/include/libart-2.0/ -I/opt/artica/include/libpng12 ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/include/freetype2/ ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/include/ncurses/ ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/include/dbi/ ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/mysql/include/mysql ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/include/libxml2 ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/db/include ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/include/sasl ';
       CPPFLAGS:=CPPFLAGS + '-I/opt/artica/include/openssl" ';
       CPPFLAGS:=CPPFLAGS + 'LDFLAGS="-L/opt/artica/lib -L/opt/artica/db/lib -L/opt/artica/mysql/lib/mysql" ';
       CPPFLAGS:=CPPFLAGS + 'LDFLAGS="-L/opt/artica/lib -L/opt/artica/db/lib -L/opt/artica/mysql/lib/mysql"';


       sysclass:=GLOBALINI.SYS;
       if BIGDEBUG then writeln('Tclass_install.Create:: -> LOCATE_OPENSSL_TOOL_PATH()');
       openssl_path:=sysclass.LOCATE_OPENSSL_TOOL_PATH();

       if BIGDEBUG then writeln('Tclass_install.Create:: -> Loading class spamass');
       spamass:=Tspamass.Create(GLOBALINI.SYS);
       if BIGDEBUG then writeln('Tclass_install.Create:: -> Loading class ldap');
       ldap:=Topenldap.Create;
       if BIGDEBUG then writeln('Tclass_install.Create:: -> Loading class ccyrus');
       ccyrus:=Tcyrus.Create(GLOBALINI.SYS);
       postfix:=Tpostfix.Create(GLOBALINI.SYS);

       samba:=Tsamba.Create;
       awstats:=Tawstats.Create(GLOBALINI.SYS);
       zmysql:=tmysql_daemon.Create(GLOBALINI.SYS);
       GLOBALINI.Free;
end;
//##############################################################################
procedure Tclass_install.Free;
begin

end;
//##############################################################################
function Tclass_install.IsRoot():boolean;
begin
if fpgeteuid=0 then exit(true);
exit(false);
end;
//##############################################################################
function Tclass_install.DetectPostfix():boolean;
begin
  if FileExists('/etc/init.d/postfix') then exit(true);
  exit(false);

end;

//##############################################################################
function Tclass_install.WichUpdateTools():boolean;
begin
    if FileExists('/usr/bin/apt-get') then begin
       UpdateTool_system:='APT';
       exit(true);
    end;
    
    if FileExists('/bin/rpm') then begin
         UpdateTool_system:='RPM';
         exit(true);
    end;
    

end;
//##############################################################################
PROCEDURE Tclass_install.PARAMETERS_AWSTATS();
var
   logs:Tlogs;
begin

            if ParamStr(2)='reconfigure' then begin
               INSTALL_AWSTATS();
               exit();
            end;
            if ParamStr(2)='generate' then begin
               logs:=Tlogs.Create;
               if FileExists('/etc/artica-postfix/awstats.lock') then begin
                  logs.Syslogs('Skipping awstats another process is executing');
                  exit();
               end;
               logs.Syslogs('Starting generate awstats');
               fpsystem('/bin/touch /etc/artica-postfix/awstats.lock');
               awstats.START_SERVICE();
               awstats.AWSTATS_GENERATE();
               logs.DeleteFile('/etc/artica-postfix/awstats.lock');
               logs.Syslogs('Finish generate awstats');
               exit();
            end;
            
     writeln('');
     writeln(chr(9) + 'awstats usages:..............................................');
     writeln(chr(9)+chr(9)+'-awstats reconfigure.....: reconfigure awstats');
     writeln(chr(9)+chr(9)+'-awstats generate........: generate config files in artica www folder');
     exit();

end;
//##############################################################################


PROCEDURE Tclass_install.PARAMETERS_POSTFIX();
var
GLOBAL_INI:myconf;
zpostfix:tpostfix;
begin
 GLOBAL_INI:=MyConf.Create();
zpostfix:=Tpostfix.Create(GLOBAL_INI.SYS);

      if ParamStr(2)='conf' then begin
          writeln('Postfix version..........:',zpostfix.POSTFIX_VERSION());
          writeln('Headers checks (regexp)..:',GLOBAL_INI.POSTFIX_HEADERS_CHECKS());
          writeln('Logs path................:',GLOBAL_INI.get_LINUX_MAILLOG_PATH());
          writeln('Ldap compliance..........:',zpostfix.POSTFIX_LDAP_COMPLIANCE());
          writeln('master.cf path...........:',zpostfix.POSFTIX_MASTER_CF_PATH());
          writeln('Queue path...............:',zpostfix.POSTFIX_QUEUE_DIRECTORY());
          exit();
      end;

      if ParamStr(2)='alllogs' then begin
         exit();
      end;

       if ParamStr(2)='fix-sasl' then begin
         POSTFIX_SET_SASL();
         exit();
      end;

       if ParamStr(2)='errors' then begin
          GLOBAL_INI.POSTFIX_LAST_ERRORS();
          exit();
       end;



      if ParamStr(2)='cert' then begin
         debug:=True;
         writeln('Result ',GenerateCertificateFileName(''));
         exit();
      end;


      if ParamStr(2)='check-config' then begin
             GLOBAL_INI.POSTFIX_REPLICATE_MAIN_CF(ParamStr(3));
             exit();
      end;




      if ParamStr(2)='rrd' then begin
         GLOBAL_INI.debug:=True;
         GLOBAL_INI.RDDTOOL_POSTFIX_MAILS_CREATE_DATABASE();
         GLOBAL_INI.RDDTOOL_POSTFIX_MAILS_SENT_STATISTICS();
         GLOBAL_INI.RDDTOOL_POSTFIX_MAILS_SENT_GENERATE();
         exit();
      end;

       if ParamStr(2)='postmap' then begin
          GLOBAL_INI.debug:=True;
          GLOBAL_INI.POSTFIX_CHECK_POSTMAP();
          exit();
       end;


      exit();
end;


PROCEDURE Tclass_install.Mirror();
var
   GLOBAL_INI                           :myconf;
   auto:TiniFile;
   FILE_EXT:string;
   package_version:string;
   www_prefix:string;
   uri_download:string;
   target_file:string;
   RegExpr:TRegExpr;
   FileNamePrefix                       :string;
   KeyList                              :TstringList;
   KeyList2                             :TstringList;
   i:Integer                            ;
   uri_recipt                           :string;
begin
    GLOBAL_INI:=myconf.Create();
    RegExpr:=TRegExpr.Create;

    if length(ParamStr(2))=0 then begin
       writeln('Please specify a path for mirroring the artica server');
       exit;
    end;

    if Not DirectoryExists(ParamStr(2)) then ForceDirectories(ParamStr(2));
    KeyList:=TstringList.Create;
    KeyList2:=TstringList.Create;


    GLOBAL_INI.WGET_DOWNLOAD_FILE('http://www.artica.fr/auto.update.php',ParamStr(2) + '/autoupdate.ini');
    auto:=TIniFile.Create(ParamStr(2)+ '/autoupdate.ini');

    auto.ReadSection('NEXT',KeyList2);
    
    For i:=0 to KeyList2.Count-1 do begin
         package_version:=auto.ReadString('NEXT',KeyList2.Strings[i],'');
         if Pos('_prefix',KeyList2.Strings[i])= 0 then begin
            if Pos('_ext',KeyList2.Strings[i])= 0 then begin
               if length(trim(KeyList2.Strings[i]))>0 then begin
               //writeln('Found ' + KeyList2.Strings[i] + ' version ' +package_version);
               KeyList.Add(KeyList2.Strings[i]);
               end;
            end;
         end;
    end;
    KeyList2.free;
    writeln('#################################################################################');
    writeln('#################################################################################');
    writeln('Mirror : Read ',KeyList.Count, ' programs to download');
    writeln('#################################################################################');
    writeln('#################################################################################');
    writeln('');
    writeln('');
    RegExpr.Expression:='(.+)=(.+)';
    
    
    For i:=0 to KeyList.Count-1 do begin
    
           RegExpr.Expression:='(.+)=(.+)';


           if RegExpr.Exec(KeyList.Strings[i]) then begin
              RegExpr.Expression:='^(.+)_ext';
              if RegExpr.Exec(KeyList.Strings[i]) then continue;
                 KeyList.Strings[i]:=RegExpr.Match[1];
           end;

           FILE_EXT:=auto.ReadString('NEXT',KeyList.Strings[i] + '_ext','tar.gz');
           www_prefix:=auto.ReadString('NEXT',KeyList.Strings[i] + '_prefix','');
           FileNamePrefix:=auto.ReadString('NEXT',KeyList.Strings[i] + '_filename_prefix',KeyList.Strings[i]  + '-');
           package_version:=auto.ReadString('NEXT',KeyList.Strings[i],'');
           target_file:=FileNamePrefix + package_version + '.' + FILE_EXT;
           
           uri_download:='http://www.artica.fr/download/' + target_file;
           uri_recipt:=ParamStr(2)  + '/' + target_file;
           
           if length(www_prefix)>0 then begin
              uri_download:='http://www.artica.fr/download/' + www_prefix + '/' + target_file;
              ForceDirectories(ParamStr(2)  + '/' + www_prefix);
              uri_recipt:=ParamStr(2)  + '/' + www_prefix + '/' + target_file
           end;
              

           writeln('Mirror  program :"' + KeyList.Strings[i]+ ' ' + package_version + '"');
           if not FileExists(uri_recipt) then begin
              GLOBAL_INI.WGET_DOWNLOAD_FILE(uri_download,uri_recipt);
           writeln('');
           writeln('');
           writeln('#################################################################################');
           writeln(chr(9)+'version..............:"' +package_version+'"');
           writeln(chr(9)+'extension............:"' +FILE_EXT+'"');
           writeln(chr(9)+'prefix...............:"' +www_prefix+'"');
           writeln(chr(9)+'FileName Prefix......:"' +FileNamePrefix+'"');
           writeln(chr(9)+'Target file..........:"' +target_file+'"');
           writeln(chr(9)+'uri..................:"' +uri_download + '"');
           writeln('');
           writeln('');

           end;
           

    end;
    
    auto.Free;
end;


//##############################################################################
PROCEDURE Tclass_install.backup_config();
var
zDate,cmd:string;

begin
zDate:=DateToStr(Date)+'-'+TimeToStr(Time);
zDate:=AnsiReplaceText(zDate,':','-');
ShowScreen('Backup your old config in /etc/postfix/backup.' + zDate + '.tar');
cmd:='/bin/tar -cf /etc/postfix/backup.' + zDate + '.tar /etc/postfix/master.cf /etc/postfix/main.cf';
Execute(cmd);


end;
//##############################################################################
PROCEDURE Tclass_install.SHUTDOWN();
var

   GLOBAL_INI:myconf;

   PRC:Tprocessinfos;


   zClam:Tclamav;
   zSpamass:Tspamass;
   zPureftpd:TPureftpd;
   zldap:tOpenldap;
   zntpd:tntpd;
   zsamba:tsamba;
   zspf:tspf;
   ccyrus:Tcyrus;
   zmimedefang:Tmimedefang;
   zsquid:Tsquid;
   zstunnel:Tstunnel;
   zdkim:tdkim;
   zpostfix:Tpostfix;
   mailgraph:tMailgraphClass;
   zmiltergreylist:tmilter_greylist;

   zkav4samba:Tkav4samba;
   zroundcube:Troundcube;
   zdansguardian:TDansguardian;
   kav4proxy:Tkav4proxy;
   bind9:Tbind9;
   obm:tobm;
   p3scan:Tp3scan;
   syslogng:Tsyslogng;
   mailarchive:tmailarchive;
   kavmilter:TkavMilter;
   kas3:tKas3;
   dnsmasq:tdnsmasq;
   bogom:tbogom;
   saslauthd:tsaslauthd;
   collectd:tcollectd;
   fetchmail:tfetchmail;
   mailspy:tmailspy;
   amavis:tamavis;
   retranslator:tkretranslator;
   cron:tcron;
   dotclear:tdotclear;
   jcheckmail:tjcheckmail;
   dhcp3:tdhcp3;
   logs:tlogs;
   opengoo:topengoo;
   cups:tcups;
   dtstat:tdstat;
   rsync:trsync;
   smartd:tsmartd;
   policydw:tpolicyd_weight;
   autofs:tautofs;
   mysql:tmysql_daemon;
   assp:tassp;
   pdns:tpdns;
   gluster:tgluster;
   nfs:tnfs;
   zabbix:tzabbix;
   hamachi:thamachi;
   zpostfilter:tpostfilter;
   zvmwaretools:tvmtools;
   zarafa:tzarafa_server;
   monit:tmonit;
   zwifi:twifi;
   emailrelay:Temailrelay;
   mldonkey:tmldonkey;
   backuppc:tbackuppc;
   kav4fs:tkav4fs;
   ocsi:tocsi;
   ocsagent:tocsagent;
   sshd:tsshd;
   auditd:tauditd;
   squidguard_page:tsquidguard_page;
   dkfilter:tdkfilter;
   ufdbguardd:tufdbguardd;
   squidguard:tsquidguard;
   framework:tframework;
   dkimmilter:tdkimmilter;
   dropbox:tdropbox;
   articapolicy:tarticapolicy;
   virtualbox:tvirtualbox;
   tftpd:ttftpd;
   crossroads:tcrossroads;
   articastatus:tarticastatus;
   articaexecutor:tarticaexecutor;
   articabackground:tarticabackground;
   pptpd:tpptpd;
   zapt_mirror:tapt_mirror;
   zddclient:tddclient;
   cluebringer:tcluebringer;
   apachesrc:tapachesrc;
   sabnzbdplus:tsabnzbdplus;
   fusermount:tfusermount;
   vnstat:tvnstat;
   munin:tmunin;
   greyhole:tgreyhole;
   iscsitarget:tiscsitarget;
   postfwd2:tpostfwd2;
   snort:tsnort;
   greensql:tgreensql;
   amanda:tamanda;
   tomcat:ttomcat;
   openemm:topenemm;
begin
    GLOBAL_INI:=myconf.Create;
    Zclam:=TClamav.Create;
    zSpamass:=Tspamass.Create(GLOBAL_INI.SYS);
    zPureftpd:=Tpureftpd.create;
    zldap:=Topenldap.Create;
    zntpd:=tntpd.Create;
    zsamba:=Tsamba.Create;
    zspf:=tspf.Create;
    zmimedefang:=Tmimedefang.Create(GLOBAL_INI.SYS);
    ccyrus:=Tcyrus.Create(GLOBAL_INI.SYS);
    zsquid:=Tsquid.Create;
    zstunnel:=Tstunnel.Create(GLOBAL_INI.SYS);
    zdkim:=tdkim.Create(GLOBAL_INI.SYS);
    zpostfix:=Tpostfix.Create(GLOBAL_INI.SYS);
    mailgraph:=tMailgraphClass.Create(GLOBAL_INI.SYS);
    zmiltergreylist:=tmilter_greylist.Create(GLOBAL_INI.SYS);
    kas3:=tkas3.Create(GLOBAL_INI.SYS);
    dnsmasq:=tdnsmasq.CReate(GLOBAL_INI.SYS);
    fetchmail:=tfetchmail.Create(GLOBAL_INI.SYS);
    logs:=tlogs.Create;


    zroundcube:=troundcube.Create(GLOBAL_INI.SYS);
    zdansguardian:=TDansGuardian.Create(GLOBAL_INI.SYS);
    SYS:=GLOBAL_INI.SYS;

    zkav4samba:=Tkav4samba.Create;
    kav4proxy:=Tkav4proxy.Create(GLOBAL_INI.SYS);
    bind9:=Tbind9.Create(SYS);
    p3scan:=Tp3scan.Create(SYS);
    syslogng:=tsyslogng.Create(SYS);
    monit:=tmonit.Create(SYS);


            PRC:=Tprocessinfos.Create;

            writeln('Shutdown '+ParamStr(2)+' daemon...');
            if ParamStr(2)='force' then begin
               writeln('Shutdown artica-postfix daemon...');
               monit.STOP();
               GLOBAL_INI.ARTICA_STOP();
               GLOBAL_INI.ARTICA_STOP();
               GLOBAL_INI.ARTICA_STOP();
               PRC.AutoKill(true);
               sleep(100);
               GLOBAL_INI.ARTICA_STOP();
               PRC.AutoKill(true);
               exit();
            end;
            
            if ParamStr(2)='watchdog' then begin
               cron:=tcron.Create(GLOBAL_INI.SYS);
               cron.STOP_WATCHDOG();
               monit.STOP();
               logs.DeleteFile('/etc/cron.d/artica-cron');
               logs.DeleteFile('/etc/cron.d/artica-process1');
               cron.free;
               exit();
            end;
           if ParamStr(2)='sabnzbdplus' then begin
               sabnzbdplus:=tsabnzbdplus.Create(GLOBAL_INI.SYS);
               sabnzbdplus.STOP();
               sabnzbdplus.free;
               exit();
            end;

           if ParamStr(2)='fuse' then begin
               fusermount:=tfusermount.Create(GLOBAL_INI.SYS);
               fusermount.STOP();
               fusermount.free;
               exit();
            end;

           if ParamStr(2)='vnstat' then begin
               vnstat:=tvnstat.Create(GLOBAL_INI.SYS);
               vnstat.STOP();
               vnstat.free;
               exit();
            end;

           if ParamStr(2)='munin' then begin
               munin:=tmunin.Create(GLOBAL_INI.SYS);
               munin.STOP();
               munin.free;
               exit();
            end;

           if ParamStr(2)='greyhole' then begin
               greyhole:=tgreyhole.Create(GLOBAL_INI.SYS);
               greyhole.STOP();
               greyhole.free;
               exit();
            end;


           if ParamStr(2)='iscsi' then begin
               iscsitarget:=tiscsitarget.Create(GLOBAL_INI.SYS);
               iscsitarget.STOP();
               iscsitarget.free;
               exit();
            end;

           if ParamStr(2)='postfwd2' then begin
               postfwd2:=tpostfwd2.Create(GLOBAL_INI.SYS);
               postfwd2.STOP();
               postfwd2.free;
               exit();
            end;
           if ParamStr(2)='snort' then begin
               snort:=tsnort.Create(GLOBAL_INI.SYS);
               snort.STOP();
               snort.free;
               exit();
            end;
            if ParamStr(2)='greensql' then begin
               greensql:=tgreensql.Create(GLOBAL_INI.SYS);
               greensql.STOP();
               greensql.free;
               exit();
            end;
            if ParamStr(2)='amanda' then begin
               amanda:=tamanda.Create(GLOBAL_INI.SYS);
               amanda.STOP();
               amanda.free;
               exit();
            end;
            if ParamStr(2)='tomcat' then begin
               tomcat:=ttomcat.Create(GLOBAL_INI.SYS);
               tomcat.STOP();
               tomcat.free;
               exit();
            end;
            if ParamStr(2)='openemm' then begin
               openemm:=topenemm.Create(GLOBAL_INI.SYS);
               openemm.STOP();
               openemm.free;
               exit();
            end;

             if ParamStr(2)='openemm-sendmail' then begin
               openemm:=topenemm.Create(GLOBAL_INI.SYS);
               openemm.SENDMAIL_STOP();
               openemm.free;
               exit();
            end;


           if ParamStr(2)='fcron' then begin
               cron:=tcron.Create(GLOBAL_INI.SYS);
               cron.STOP();
               cron.free;
               exit();
            end;

              if ParamStr(2)='ldap' then begin
                 zldap.LDAP_STOP();
                 exit();
              end;

              if ParamStr(2)='sysloger' then begin
                 GLOBAL_INI.SYSLOGER_STOP();
                 exit();
              end;

              if ParamStr(2)='rsync' then begin
                 rsync:=trsync.Create(SYS);
                 rsync.STOP();
                 exit();
              end;

              if ParamStr(2)='zarafa' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.STOP();
                 exit();
              end;

              if ParamStr(2)='zarafa-lmtp' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.DAGENT_STOP();
                 exit();
              end;

              if ParamStr(2)='zarafa-server' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.SERVER_STOP();
                 exit();
              end;

              if ParamStr(2)='zarafa-web' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.APACHE_STOP();
                 exit();
              end;

              if ParamStr(2)='monit' then begin
                 monit.STOP();
                 exit();
              end;

              if ParamStr(2)='artica-notifier' then begin
                 emailrelay:=temailrelay.Create(SYS);
                 emailrelay.STOP();
                 exit();
              end;




              if ParamStr(2)='smartd' then begin
                 smartd:=tsmartd.Create(SYS);
                 smartd.STOP();
                 exit();
              end;

              if ParamStr(2)='autofs' then begin
                 autofs:=tautofs.Create(SYS);
                 autofs.STOP();
                 exit();
              end;

              if ParamStr(2)='hamachi' then begin
                 hamachi:=thamachi.Create(SYS);
                 hamachi.STOP();
                 exit();
              end;

              if ParamStr(2)='amachi' then begin
                 hamachi:=thamachi.Create(SYS);
                 hamachi.STOP();
                 exit();
              end;

              if ParamStr(2)='mysql-cluster' then begin
                 mysql:=tmysql_daemon.Create(SYS);
                 mysql.CLUSTER_MANAGEMENT_STOP();
                 mysql.CLUSTER_REPLICA_STOP();
                 exit();
              end;

              if ParamStr(2)='zabbix' then begin
                 zabbix:=tzabbix.Create(SYS);
                 zabbix.STOP();
                 exit();
              end;

              if ParamStr(2)='vmtools' then begin
                   zvmwaretools:=tvmtools.Create(SYS);
                   zvmwaretools.STOP();
                   exit();
              end;

              if ParamStr(2)='vboxguest' then begin
                   zvmwaretools:=tvmtools.Create(SYS);
                   zvmwaretools.VIRBOX_STOP();
                   exit();
              end;

              if ParamStr(2)='mldonkey' then begin
                   SYS:=Tsystem.Create;
                   mldonkey:=tmldonkey.Create(SYS);
                   mldonkey.STOP();
                   exit();
              end;

              if ParamStr(2)='fetchmail-logger' then begin
                   fetchmail.FETCHMAIL_LOGGER_STOP();
                   exit();
              end;



              if ParamStr(2)='backuppc' then begin
                   SYS:=Tsystem.Create;
                   backuppc:=tbackuppc.Create(SYS);
                   backuppc.STOP();
                   exit();
              end;

             if ParamStr(2)='kav4fs' then begin
                   SYS:=Tsystem.Create;
                   kav4fs:=tkav4fs.Create(SYS);
                   kav4fs.STOP();
                   exit();
              end;

             if ParamStr(2)='ocsweb' then begin
                   SYS:=Tsystem.Create;
                   ocsi:=tocsi.Create(SYS);
                   ocsi.STOP();
                   exit();
              end;

             if ParamStr(2)='ufdb' then begin
                   SYS:=Tsystem.Create;
                   ufdbguardd:=tufdbguardd.Create(SYS);
                   ufdbguardd.STOP();
                   exit();
              end;

             if ParamStr(2)='ufdb-tail' then begin
                   SYS:=Tsystem.Create;
                   ufdbguardd:=tufdbguardd.Create(SYS);
                   ufdbguardd.TAIL_STOP();
                   exit();
              end;

             if ParamStr(2)='squidguard-tail' then begin
                   SYS:=Tsystem.Create;
                   squidguard:=tsquidguard.Create(SYS);
                   squidguard.STOP();
                   exit();
              end;

             if ParamStr(2)='framework' then begin
                   SYS:=Tsystem.Create;
                   framework:=tframework.Create(SYS);
                   framework.STOP();
                   exit();
              end;

             if ParamStr(2)='dkim-milter' then begin
                   SYS:=Tsystem.Create;
                   dkimmilter:=tdkimmilter.Create(SYS);
                   dkimmilter.STOP();
                   exit();
              end;

             if ParamStr(2)='dropbox' then begin
                   SYS:=Tsystem.Create;
                   dropbox:=tdropbox.Create(SYS);
                   dropbox.STOP();
                   exit();
              end;

             if ParamStr(2)='artica-policy' then begin
                   SYS:=Tsystem.Create;
                   articapolicy:=tarticapolicy.Create(SYS);
                   articapolicy.STOP();
                   exit();
              end;

              if ParamStr(2)='virtualbox-web' then begin
                   SYS:=Tsystem.Create;
                   virtualbox:=tvirtualbox.Create(SYS);
                   virtualbox.STOP();
                   exit();
              end;

              if ParamStr(2)='crossroads' then begin
                   SYS:=Tsystem.Create;
                   crossroads:=tcrossroads.Create(SYS);
                   crossroads.STOP();
                   exit();
              end;

              if ParamStr(2)='crossroad-multiple' then begin
                   SYS:=Tsystem.Create;
                   crossroads:=tcrossroads.Create(SYS);
                   crossroads.STOP_MULTIPLE();
                   exit();
              end;

              if ParamStr(2)='artica-status' then begin
                   SYS:=Tsystem.Create;
                   articastatus:=tarticastatus.Create(SYS);
                   articastatus.STOP();
                   exit();
              end;

              if ParamStr(2)='artica-exec' then begin
                   SYS:=Tsystem.Create;
                   articaexecutor:=tarticaexecutor.Create(SYS);
                   articaexecutor.STOP();
                   exit();
              end;

              if ParamStr(2)='artica-back' then begin
                   SYS:=Tsystem.Create;
                   articabackground:=tarticabackground.Create(SYS);
                   articabackground.STOP();
                   exit();
              end;

              if ParamStr(2)='pptpd' then begin
                   SYS:=Tsystem.Create;
                   pptpd:=tpptpd.Create(SYS);
                   pptpd.STOP();
                   exit();
              end;

              if ParamStr(2)='cluebringer' then begin
                   SYS:=Tsystem.Create;
                   cluebringer:=tcluebringer.Create(SYS);
                   cluebringer.STOP();
                   exit();
              end;

              if ParamStr(2)='apachesrc' then begin
                   SYS:=Tsystem.Create;
                   apachesrc:=tapachesrc.Create(SYS);
                   apachesrc.STOP();
                   exit();
              end;



              if ParamStr(2)='apt-mirror' then begin
                   SYS:=Tsystem.Create;
                   zapt_mirror:=tapt_mirror.Create(SYS);
                   zapt_mirror.STOP();
                   exit();
              end;

              if ParamStr(2)='ddclient' then begin
                   SYS:=Tsystem.Create;
                   zddclient:=tddclient.Create(SYS);
                   zddclient.STOP();
                   exit();
              end;





             if ParamStr(2)='pptpd-clients' then begin
                  SYS:=Tsystem.Create();
                  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.pptpd.php --clients-stop');
                  halt(0);
             end;


               if ParamStr(2)='tftpd' then begin
                   SYS:=Tsystem.Create;
                   tftpd:=ttftpd.Create(SYS);
                   tftpd.STOP();
                   exit();
              end;


             if ParamStr(2)='ocsagent' then begin
                   SYS:=Tsystem.Create;
                   ocsagent:=tocsagent.Create(SYS);
                   ocsagent.STOP();
                   exit();
              end;

             if ParamStr(2)='openssh' then begin
                   SYS:=Tsystem.Create;
                   sshd:=tsshd.Create(SYS);
                   sshd.STOP();
                   exit();
              end;

             if ParamStr(2)='auth-logger' then begin
                   SYS:=Tsystem.Create;
                   sshd:=tsshd.Create(SYS);
                   sshd.STOP_LOGGER();
                   exit();
              end;

             if ParamStr(2)='dkfilter' then begin
                   SYS:=Tsystem.Create;
                   dkfilter:=tdkfilter.Create(SYS);
                   dkfilter.STOP();
                   exit();
              end;



            if ParamStr(2)='auditd' then begin
                   SYS:=Tsystem.Create;
                   auditd:=tauditd.Create(SYS);
                   auditd.STOP();
                   exit();
              end;

             if ParamStr(2)='squidguard-http' then begin
                   SYS:=Tsystem.Create;
                   squidguard_page:=tsquidguard_page.Create(SYS);
                   squidguard_page.STOP();
                   exit();
              end;



              if ParamStr(2)='postfix-single' then begin
                  zpostfix.POSTFIX_STOP();
                  exit();
              end;



              if ParamStr(2)='mysqmail' then begin
                 zpostfix.MYSQMAIL_STOP();
                 exit();
              end;

              if ParamStr(2)='postfix-heavy' then begin
                 zpostfix.POSTFIX_STOP();
                 kas3.STOP();
                 zspf.SPF_MILTER_STOP();
                 zmiltergreylist.MILTER_GREYLIST_STOP();
                 zmimedefang.MIMEDEFANG_STOP();
                 zSpamass.SPAMASSASSIN_STOP();
                 zSpamass.MILTER_STOP();

                 policydw:=tpolicyd_weight.Create(SYS);
                 policydw.STOP();
                 policydw.Free;

                 assp:=Tassp.Create(SYS);
                 assp.STOP();
                 assp.free;
                 exit();
              end;

              if ParamStr(2)='postfix' then begin
                 zpostfix.POSTFIX_STOP();
                 kas3.STOP();
                 zspf.SPF_MILTER_STOP();
                 zmiltergreylist.MILTER_GREYLIST_STOP();
                 zmimedefang.MIMEDEFANG_STOP();
                 zSpamass.SPAMASSASSIN_STOP();
                 zSpamass.MILTER_STOP();

                 policydw:=tpolicyd_weight.Create(SYS);
                 policydw.STOP();
                 policydw.Free;

                 assp:=Tassp.Create(SYS);
                 assp.STOP();
                 assp.free;
                 exit();
              end;


              if ParamStr(2)='policydw' then begin
                 policydw:=tpolicyd_weight.Create(SYS);
                 policydw.STOP();
                 policydw.Free;
                 exit();
              end;

              if ParamStr(2)='wifi' then begin
                 zwifi:=twifi.Create(SYS);
                 zwifi.STOP();
                 exit();
              end;

              if ParamStr(2)='mgreylist' then begin
                 writeln('stopping milter-greylist');
                 zmiltergreylist.MILTER_GREYLIST_STOP();
                 exit();
              end;

              if ParamStr(2)='ntpd' then begin
                 zntpd.NTPD_STOP();
                 exit();
              end;

              if ParamStr(2)='pdns' then begin
                 pdns:=tpdns.Create(SYS);
                 pdns.STOP();
                 exit();
              end;

              if ParamStr(2)='gluster' then begin
                 gluster:=tgluster.Create(SYS);
                 gluster.STOP();
                 exit();
              end;




              if ParamStr(2)='dhcp' then begin
                 dhcp3:=tdhcp3.CReate(SYS);
                 dhcp3.STOP();
                 exit();
              end;

              if ParamStr(2)='openvpn' then begin
                 zopenvpn:=topenvpn.CReate(SYS);
                 zopenvpn.STOP();
                 exit();
              end;


              if ParamStr(2)='dstat' then begin
                 dtstat:=tdstat.CReate(SYS);
                 dtstat.STOP();
                 exit();
              end;

              if ParamStr(2)='dstat-top-mem' then begin
                 dtstat:=tdstat.CReate(SYS);
                 dtstat.STOP_TOP_MEMORY();
                 exit();
              end;

              if ParamStr(2)='dstat-top-cpu' then begin
                 dtstat:=tdstat.CReate(SYS);
                 dtstat.STOP_TOP_CPU();
                 exit();
              end;

              if ParamStr(2)='nfs' then begin
                 nfs:=tnfs.CReate(SYS);
                 nfs.STOP();
                 exit();
              end;



              if ParamStr(2)='postfix-logger' then begin
                 zpostfix.MYSQMAIL_STOP();
                 exit();
              end;


              
              if ParamStr(2)='fetchmail' then begin
                 fetchmail.FETCHMAIL_DAEMON_STOP();
                 exit();
              end;

              if ParamStr(2)='apache-groupware' then begin
                 opengoo:=topengoo.Create(SYS);
                 opengoo.STOP();
                 opengoo.free;
                 exit();
              end;

              if ParamStr(2)='assp' then begin
                 assp:=tassp.Create(SYS);
                 assp.STOP();
                 assp.free;
                 exit();
              end;



             if ParamStr(2)='imap' then begin
                 saslauthd:=tsaslauthd.Create(GLOBAL_INI.SYS);
                 saslauthd.STOP();
                 ccyrus.CYRUS_DAEMON_STOP();
                 exit();
              end;
              
             if ParamStr(2)='mailspy' then begin
                 mailspy:=tmailspy.Create(GLOBAL_INI.SYS);
                 mailspy.STOP();
                 exit();
              end;


               if ParamStr(2)='cups' then begin
                 cups:=tcups.Create();
                 cups.STOP();
                 exit();
              end;


              if ParamStr(2)='postfilter' then begin
                 zpostfilter:=tpostfilter.Create(SYS);
                 zpostfilter.STOP();
                 halt(0);
              end;

              
              
           if ParamStr(2)='retranslator' then begin
                 retranslator:=tkretranslator.Create(GLOBAL_INI.SYS);
                 retranslator.STOP();
                 retranslator.Free;
                 exit();
              end;

           if ParamStr(2)='retranslator-tsk' then begin
                 retranslator:=tkretranslator.Create(GLOBAL_INI.SYS);
                 retranslator.KILL();
                 retranslator.Free;
                 exit();
           end;


           if ParamStr(2)='jcheckmail' then begin
                 jcheckmail:=tjcheckmail.Create(GLOBAL_INI.SYS);
                 jcheckmail.STOP();
                 jcheckmail.Free;
                 exit();
              end;
              
           if ParamStr(2)='mailman' then begin
                 zmailman:=tmailman.Create(GLOBAL_INI.SYS);
                 zmailman.STOP();
                 zmailman.Free;
                 exit();
              end;



            if ParamStr(2)='kav6' then begin
                 kavmilter:=tkavmilter.Create(SYS);
                 kavmilter.STOP();
                 kavmilter.free;
                 exit();
              end;

            if ParamStr(2)='kavmilter' then begin
                 kavmilter:=tkavmilter.Create(SYS);
                 kavmilter.STOP();
                 kavmilter.free;
                 exit();
              end;

            if ParamStr(2)='squid' then begin
                 zsquid.SQUID_STOP();
                 zsquid.TAIL_SQUIDCLAMAV_STOP();
                 zdansguardian.C_ICAP_STOP();
                 zdansguardian.DANSGUARDIAN_STOP();
                 kav4proxy.KAV4PROXY_STOP();
                 zClam.CLAMD_STOP();
                 ufdbguardd:=tufdbguardd.Create(SYS);
                 ufdbguardd.STOP();
                 squidguard:=tsquidguard.Create(SYS);
                 squidguard.STOP();
                 exit();
              end;

            if ParamStr(2)='squid-tail' then begin
               zsquid.TAIL_STOP();
               halt(0);
            end;

            if ParamStr(2)='squidclamav-tail' then begin
               zsquid.TAIL_SQUIDCLAMAV_STOP();
               halt(0);
            end;


            if ParamStr(2)='squid-cache' then begin
                 zsquid.SQUID_STOP();
                 exit();
              end;

            if ParamStr(2)='proxy-pac' then begin
                 zsquid.PROXY_PAC_STOP();
                 exit();
              end;



           if ParamStr(2)='kav4proxy' then begin
                 kav4proxy.KAV4PROXY_STOP();
                 exit();
              end;


            if ParamStr(2)='dansguardian-tail' then begin
                 zdansguardian.DANSGUARDIAN_TAIL_STOP();
                 exit();
            end;

            if ParamStr(2)='cicap' then begin
                 zdansguardian.C_ICAP_STOP();
                 exit();
              end;


            if ParamStr(2)='roundcube' then begin
                 zroundcube.ROUNDCUBE_STOP_SERVICE();
                 exit();
              end;


            if ParamStr(2)='stunnel' then begin
                  zstunnel.STUNNEL_STOP();
                  exit();
            end;
            
          if ParamStr(2)='p3scan' then begin
                  p3scan.STOP();
                  exit();
            end;

            if ParamStr(2)='dkim' then begin
                  zdkim.DKIM_FILTER_STOP();
                  exit();
            end;

            if ParamStr(2)='dansgardian' then begin
                 zClam.CLAMD_STOP();
                 zdansguardian.DANSGUARDIAN_STOP();
                 exit();
              end;

            if ParamStr(2)='dansguardian' then begin
                 zClam.CLAMD_STOP();
                 zdansguardian.DANSGUARDIAN_STOP();
                 exit();
              end;

            if ParamStr(2)='boa' then begin
                 GLOBAL_INI.BOA_STOP();
                 exit();
              end;

            if ParamStr(2)='ftp' then begin
                 zPureftpd.PURE_FTPD_STOP();
                 exit();
            end;

            if ParamStr(2)='samba' then begin
                 zsamba.SAMBA_STOP();
                 cups:=tcups.CReate();
                 cups.STOP();
                 exit();
            end;

           if ParamStr(2)='winbindd' then begin
                 zsamba.WINBIND_STOP();
                 exit();
            end;
            
            if ParamStr(2)='collectd' then begin
               collectd:=tcollectd.Create(GLOBAL_INI.SYS);
               collectd.STOP();
               collectd.free;
               exit();
            end;



            if ParamStr(2)='daemon' then begin
                 GLOBAL_INI.ARTICA_STOP();
                 exit();
            end;
            
            if ParamStr(2)='bogom' then begin
                 bogom:=Tbogom.Create(GLOBAL_INI.SYS);
                 bogom.STOP();
                 bogom.FRee;
                 exit();
            end;


            if ParamStr(2)='mysql' then begin
                 zmysql.SERVICE_STOP();
                 syslogng.STOP();
                 exit();
              end;

             if ParamStr(2)='apache' then begin
                 writeln('Stopping apache....');
                 obm:=tobm.Create(GLOBAL_INI.SYS);
                 GLOBAL_INI.APACHE_ARTICA_STOP();
                 zroundcube.ROUNDCUBE_STOP_SERVICE();
                 obm.SERVICE_STOP();
                 exit();
              end;

             if ParamStr(2)='spamd' then begin
                 zSpamass.SPAMASSASSIN_STOP();
                 zSpamass.MILTER_STOP();
                 exit();
              end;


             if ParamStr(2)='clamd' then begin
                 zClam.CLAMD_STOP();
                 exit();
              end;

             if ParamStr(2)='freshclam' then begin
                 zClam.FRESHCLAM_STOP();
                 exit();
              end;

             if ParamStr(2)='clammilter' then begin
                 zClam.MILTER_STOP();
                 zClam.CLAMD_STOP();
                 zClam.FRESHCLAM_STOP();
                 exit();
              end;


             if ParamStr(2)='saslauthd' then begin
                 saslauthd:=tsaslauthd.Create(GLOBAL_INI.SYS);
                 saslauthd.STOP();
                 saslauthd.free;
                 exit();
              end;


             if ParamStr(2)='dnsmasq' then begin
                 dnsmasq.DNSMASQ_STOP_DAEMON();
                 exit();
              end;

             if ParamStr(2)='postfix' then begin
                 zpostfix.POSTFIX_STOP();
                 exit();
              end;

             if ParamStr(2)='mailgraph' then begin
                 mailgraph.MAILGRAPH_STOP();
                 exit();
              end;

               if ParamStr(2)='mimedefang' then begin
                 zmimedefang.MIMEDEFANG_STOP();
                 exit();
              end;

               if ParamStr(2)='kav4samba' then begin
                 zkav4samba.SERVICE_STOP();
                 exit();
              end;

               if ParamStr(2)='bind9' then begin
                 bind9.STOP();
                 exit();
              end;
              

               if ParamStr(2)='obm' then begin
                 obm:=tobm.Create(GLOBAL_INI.SYS);
                 obm.SERVICE_STOP();
                 exit();
              end;


              
              if ParamStr(2)='syslogng' then begin
                 syslogng.STOP();
                 exit();
              end;
              
              if ParamStr(2)='amavis' then begin
                 amavis:=Tamavis.Create(GLOBAL_INI.SYS);
                 amavis.STOP();
                 exit();
              end;

              if ParamStr(2)='amavis-milter' then begin
                 amavis:=Tamavis.Create(GLOBAL_INI.SYS);
                 amavis.STOP_MILTER();
                 exit();
              end;
              
              
              if ParamStr(2)='mailarchiver' then begin
                 mailarchive:=tmailarchive.Create(GLOBAL_INI.SYS);
                 mailarchive.STOP();
                 exit();
              end;
              
             if ParamStr(2)='dotclear' then begin
                 dotclear:=tdotclear.Create(GLOBAL_INI.SYS);
                 dotclear.STOP();
                 exit();
              end;

              if ParamStr(2)='kas3' then begin
                 kas3.STOP();
                 exit();
              end;
              


            if length(ParamStr(2))>0 then begin
               writeln('Usage:');
               writeln('/etc/init.d/artica-postfix stop ldap|imap|kav6|squid|squid-cache|squid-tail|dansgardian|boa|ftp|mysql|apache|spamd|clamd|freshclam|mgreylist|ntpd');
               writeln('|tail|daemon|clammilter|dnsmasq|stunnel|dkim|postfix|mailgraph|mimedefang|roundcube|kav4samba|bind9|obm|p3scan|syslogng|mailarchiver|bogom');
               writeln('|collectd|fetchmail|mailspy|amavis|retranslator|retranslator-tsk|watchdog|dotclear|jcheckmail|mailman|kas3|dhcp|cicap|openvpn|postfix-logger');
               writeln('|dansguardian-tail|cups|dstat|dstat-top-mem|dstat-top-cpu|rsync|smartd|policydw|mysql-cluster|assp|pdns|gluster|gluster-cli|sysloger');
               writeln('|zabbix|hamachi|kav4proxy|postfilter|vmtools|zarafa|zarafa-web|monit|wifi[proxy-pac|artica-notifier|mldonkey|backuppc|kav4fs|ocsweb|ocsagent');
               writeln('|openssh|auditd|squidguard-http|fetchmail-logger|dkfilter|ufdb|ufdb-tail|squidguard-tail|framework|dkim-milter|dropbox|artica-policy|virtualbox-web');
               writeln('|tftpd|crossroads|artica-status|artica-exec|artica-back|pptpd|pptpd-clients|apt-mirror|squidclamav-tail|ddclient|cluebringer|apachesrc');
               writeln('|sabnzbdplus|fcron|fuse|vnstat|winbindd|munin|greyhole|amavis-milter|iscsi|auth-logger|snort|greensql|amanda|zarafa-lmtp|tomcat|openemm');
               writeln('|sendmail-openemm');
               exit();
            end;




           logs:=tlogs.Create;
           logs.Syslogs('Artica will be stopped by command '+ParamStr(0)+' ' +ParamStr(1));
            GLOBAL_INI.ARTICA_STOP();
            sleep(100);
            GLOBAL_INI.ARTICA_STOP();
            sleep(100);
            PRC.AutoKill(false);
            GLOBAL_INI.ARTICA_STOP();
            sleep(100);
            exit();

     end;

procedure tclass_install.START_SERVICES_PARAMETERS();
var
   TimeInt:integer;
   GLOBAL_INI:myconf;
   zClam:Tclamav;
   zSpamass:Tspamass;
   zPureftpd:TPureftpd;
   zldap:tOpenldap;
   zntpd:tntpd;
   zsamba:tsamba;
   zspf:tspf;
   ccyrus:Tcyrus;
   zmimedefang:Tmimedefang;
   zsquid:Tsquid;
   zstunnel:Tstunnel;
   zdkim:tdkim;
   zpostfix:Tpostfix;
   mailgraph:tMailgraphClass;
   zmiltergreylist:tmilter_greylist;


   zkav4samba:Tkav4samba;
   zroundcube:Troundcube;
   zdansguardian:TDansguardian;
   kav4proxy:Tkav4proxy;

   bind9:Tbind9;
   obm:Tobm;
   p3scan:Tp3scan;
   syslogng:tsyslogng;
   mailarchive:tmailarchive;
   zkavmilter:Tkavmilter;
   kas3:tkas3;
   dnsmasq:tdnsmasq;
   bogom:tbogom;
   saslauthd:tsaslauthd;
   collectd:tcollectd;
   fetchmail:tfetchmail;
   mailspy:tmailspy;
   amavis:tamavis;
   retranslator:tkretranslator;
   spfm:tspf;
   dotclear:tdotclear;
   jcheckmail:tjcheckmail;
   dhcp3:tdhcp3;
   cron:tcron;
   lighttpd:Tlighttpd;
   opengoo:topengoo;
   cups:tcups;
   dstat:tdstat;
   rsync:trsync;
   smartd:tsmartd;
   tcp:ttcpip;
   policydw:tpolicyd_weight;
   autofs:tautofs;
   mysql:tmysql_daemon;

   NetWorkAvailable:boolean;
   NoBootWithoutIP:integer;
   assp:tassp;
   pdns:tpdns;
   gluster:tgluster;
   nfs:tnfs;
   zabbix:tzabbix;
   hamachi:thamachi;
   zpostfilter:tpostfilter;
   zvmwaretools:tvmtools;
   zarafa:tzarafa_server;
   monit:tmonit;
   zwifi:twifi;
   emailrelay:temailrelay;
   mldonkey:tmldonkey;
   backuppc:Tbackuppc;
   kav4fs:tkav4fs;
   ocsi:tocsi;
   ocsagent:tocsagent;
   sshd:tsshd;
   auditd:tauditd;
   squidguard_page:tsquidguard_page;
   dkfilter:tdkfilter;
   ufdbguardd:tufdbguardd;
   squidguard:tsquidguard;
   framework:tframework;
   dkimmilter:tdkimmilter;
   dropbox:tdropbox;
   articapolicy:tarticapolicy;
   virtualbox:tvirtualbox;
   tftpd:ttftpd;
   crossroads:tcrossroads;
   articastatus:tarticastatus;
   articaexecutor:tarticaexecutor;
   articabackground:tarticabackground;
   pptpd:tpptpd;
   zapt_mirror:tapt_mirror;
   zddclient:tddclient;
   cluebringer:tcluebringer;
   apachesrc:tapachesrc;
   sabnzbdplus:tsabnzbdplus;
   fusermount:tfusermount;
   vnstat:tvnstat;
   munin:tmunin;
   greyhole:tgreyhole;
   iscsitarget:tiscsitarget;
   postfwd2:tpostfwd2;
   snort:tsnort;
   greensql:tgreensql;
   amanda:tamanda;
   tomcat:ttomcat;
   openemm:topenemm;
begin
    GLOBAL_INI:=myconf.Create;
    SYS:=Tsystem.Create;
    Zclam:=TClamav.Create;
    zSpamass:=Tspamass.Create(GLOBAL_INI.SYS);
    zPureftpd:=Tpureftpd.create;
    zldap:=Topenldap.Create;
    zntpd:=tntpd.Create;
    zsamba:=Tsamba.Create;
    zspf:=tspf.Create;
    zmimedefang:=Tmimedefang.Create(GLOBAL_INI.SYS);
    ccyrus:=Tcyrus.Create(GLOBAL_INI.SYS);
    zsquid:=Tsquid.Create;
    zstunnel:=Tstunnel.Create(GLOBAL_INI.SYS);
    zdkim:=tdkim.Create(GLOBAL_INI.SYS);
    zpostfix:=Tpostfix.Create(GLOBAL_INI.SYS);
    mailgraph:=tMailgraphClass.Create(GLOBAL_INI.SYS);
    zmiltergreylist:=tmilter_greylist.Create(GLOBAL_INI.SYS);
    dnsmasq:=tdnsmasq.Create(GLOBAL_INI.SYS);
    fetchmail:=tfetchmail.Create(GLOBAL_INI.SYS);


    zroundcube:=troundcube.Create(GLOBAL_INI.SYS);
    zdansguardian:=TDansGuardian.Create(GLOBAL_INI.SYS);


    zkav4samba:=Tkav4samba.Create;
    kav4proxy:=Tkav4proxy.Create(GLOBAL_INI.SYS);

    bind9:=Tbind9.Create(GLOBAL_INI.SYS);
    p3scan:=Tp3scan.Create(GLOBAL_INI.SYS);
    syslogng:=Tsyslogng.Create(GLOBAL_INI.SYS);
    kas3:=tkas3.Create(GLOBAL_INI.SYS);
    tcp:=ttcpip.Create;
    NetWorkAvailable:=tcp.isNetAvailable;
    monit:=tmonit.Create(SYS);


    tcp.free;
    NoBootWithoutIP:=0;
    if not TryStrToInt(SYS.GET_PERFS('NoBootWithoutIP'),NoBootWithoutIP) then NoBootWithoutIP:=0;
    if NoBootWithoutIP=0 then NetWorkAvailable:=true;


    if not NetWorkAvailable then begin
       writeln('Warning, Network is unreachable, some services could not be start...');
    end;

    
             if ParamStr(2)='ldap' then begin
                 zldap.LDAP_START();
                 exit();
              end;
              
              if ParamStr(2)='postfix-single' then begin
                  if NetWorkAvailable then zpostfix.POSTFIX_START_LIMITED();
                  exit();
              end;

              if ParamStr(2)='postfix' then begin
                 if not NetWorkAvailable then exit();
                 zspf.SPFMILTER_START();
                 zmiltergreylist.MILTER_GREYLIST_START();
                 zmimedefang.MIMEDEFANG_START();
                 zSpamass.SPAMASSASSIN_START();
                 zSpamass.MILTER_START();
                 kas3.START();

                 policydw:=tpolicyd_weight.Create(SYS);
                 policydw.START();
                 policydw.Free;

                 assp:=Tassp.Create(SYS);
                 assp.START();
                 assp.free;
                 zpostfix.POSTFIX_START_LIMITED();
                 zpostfix.MYSQMAIL_START();
                 exit();
              end;

             if ParamStr(2)='postfix-heavy' then begin
                     zpostfix.POSTFIX_START();
                     zpostfix.MYSQMAIL_START();
                     exit();
             end;

              if ParamStr(2)='policydw' then begin
                 policydw:=tpolicyd_weight.Create(SYS);
                 policydw.START();
                 policydw.Free;
                 exit();
              end;

              if ParamStr(2)='postfix-logger' then begin
                 zpostfix.MYSQMAIL_START();
                 exit();
              end;


              if ParamStr(2)='sysloger' then begin
                 GLOBAL_INI.SYSLOGER_START();
                 exit();
              end;

              if ParamStr(2)='rsync' then begin
                 if not NetWorkAvailable then exit();
                 rsync:=trsync.Create(SYS);
                 rsync.START();
                 exit();
              end;

              if ParamStr(2)='amachi' then begin
                hamachi:=thamachi.Create(SYS);
                hamachi.START();
                exit();
              end;

              if ParamStr(2)='hamachi' then begin
                 hamachi:=thamachi.Create(SYS);
                 hamachi.START();
                 exit();
              end;

              if ParamStr(2)='wifi' then begin
                 zwifi:=twifi.Create(SYS);
                 zwifi.START();
                 exit();
              end;


              if ParamStr(2)='vmtools' then begin
                  zvmwaretools:=tvmtools.Create(SYS);
                 zvmwaretools.START();
                 exit();
              end;

              if ParamStr(2)='vboxguest' then begin
                   zvmwaretools:=tvmtools.Create(SYS);
                   zvmwaretools.VIRBOX_START();
                   exit();
              end;

              if ParamStr(2)='artica-notifier' then begin
                 emailrelay:=temailrelay.Create(SYS);
                 emailrelay.START();
                 exit();
              end;


              if ParamStr(2)='mysql-cluster' then begin
                 if not NetWorkAvailable then exit();
                 mysql:=tmysql_daemon.Create(SYS);
                 mysql.CLUSTER_MANAGEMENT_START();
                 mysql.CLUSTER_REPLICA_START();
                 exit();
              end;


              if ParamStr(2)='smartd' then begin
                 smartd:=tsmartd.Create(SYS);
                 smartd.START();
                 exit();
              end;

              if ParamStr(2)='zarafa' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.START();
                 exit();
              end;

              if ParamStr(2)='zarafa-web' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.APACHE_START();
                 exit();
              end;

              if ParamStr(2)='zarafa-server' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.SERVER_START();
                 exit();
              end;

              if ParamStr(2)='zarafa-lmtp' then begin
                 zarafa:=tzarafa_server.Create(SYS);
                 zarafa.DAGENT_START();
                 exit();
              end;



              if ParamStr(2)='mldonkey' then begin
                 mldonkey:=tmldonkey.Create(SYS);
                 mldonkey.START();
                 exit();
              end;
              if ParamStr(2)='backuppc' then begin
                 backuppc:=tbackuppc.Create(SYS);
                 backuppc.START();
                 exit();
              end;
              if ParamStr(2)='kav4fs' then begin
                 kav4fs:=tkav4fs.Create(SYS);
                 kav4fs.START();
                 exit();
              end;


              if ParamStr(2)='ocsweb' then begin
                   SYS:=Tsystem.Create;
                   ocsi:=tocsi.Create(SYS);
                   ocsi.START();
                   exit();
              end;

              if ParamStr(2)='ufdb' then begin
                   SYS:=Tsystem.Create;
                   ufdbguardd:=tufdbguardd.Create(SYS);
                   ufdbguardd.START();
                   exit();
              end;

              if ParamStr(2)='ufdb-tail' then begin
                   SYS:=Tsystem.Create;
                   ufdbguardd:=tufdbguardd.Create(SYS);
                   ufdbguardd.TAIL_START();
                   exit();
              end;

              if ParamStr(2)='squidguard-tail' then begin
                   SYS:=Tsystem.Create;
                   squidguard:=tsquidguard.Create(SYS);
                   squidguard.START();
                   exit();
              end;

              if ParamStr(2)='framework' then begin
                   SYS:=Tsystem.Create;
                   framework:=tframework.Create(SYS);
                   framework.START();
                   exit();
              end;
              if ParamStr(2)='virtualbox-web' then begin
                   SYS:=Tsystem.Create;
                   virtualbox:=tvirtualbox.Create(SYS);
                   virtualbox.START();
                   exit();
              end;
              if ParamStr(2)='crossroads' then begin
                   SYS:=Tsystem.Create;
                   crossroads:=tcrossroads.Create(SYS);
                   crossroads.START();
                   exit();
              end;

              if ParamStr(2)='crossroad-multiple' then begin
                   SYS:=Tsystem.Create;
                   crossroads:=tcrossroads.Create(SYS);
                   crossroads.START();
                   exit();
              end;

              if ParamStr(2)='pptpd' then begin
                   SYS:=Tsystem.Create;
                   pptpd:=tpptpd.Create(SYS);
                   pptpd.START();
                   exit();
              end;
               if ParamStr(2)='cluebringer' then begin
                   SYS:=Tsystem.Create;
                   cluebringer:=tcluebringer.Create(SYS);
                   cluebringer.START();
                   exit();
              end;

                if ParamStr(2)='apachesrc' then begin
                   SYS:=Tsystem.Create;
                   apachesrc:=tapachesrc.Create(SYS);
                   apachesrc.START();
                   exit();
              end;

                if ParamStr(2)='sabnzbdplus' then begin
                   SYS:=Tsystem.Create;
                   sabnzbdplus:=tsabnzbdplus.Create(SYS);
                   sabnzbdplus.START();
                   exit();
              end;

                if ParamStr(2)='fuse' then begin
                   SYS:=Tsystem.Create;
                   fusermount:=tfusermount.Create(SYS);
                   fusermount.START();
                   exit();
              end;

                if ParamStr(2)='vnstat' then begin
                   SYS:=Tsystem.Create;
                   vnstat:=tvnstat.Create(SYS);
                   vnstat.START();
                   exit();
              end;

                if ParamStr(2)='munin' then begin
                   SYS:=Tsystem.Create;
                   munin:=tmunin.Create(SYS);
                   munin.START();
                   exit();
              end;

                if ParamStr(2)='greyhole' then begin
                   SYS:=Tsystem.Create;
                   greyhole:=tgreyhole.Create(SYS);
                   greyhole.START();
                   exit();
              end;

                if ParamStr(2)='iscsi' then begin
                   SYS:=Tsystem.Create;
                   iscsitarget:=tiscsitarget.Create(SYS);
                   iscsitarget.START();
                   exit();
              end;

                if ParamStr(2)='postfwd2' then begin
                   SYS:=Tsystem.Create;
                   postfwd2:=tpostfwd2.Create(SYS);
                   postfwd2.START();
                   exit();
              end;
                if ParamStr(2)='snort' then begin
                   SYS:=Tsystem.Create;
                   snort:=tsnort.Create(SYS);
                   snort.START();
                   exit();
              end;
                if ParamStr(2)='greensql' then begin
                   SYS:=Tsystem.Create;
                   greensql:=tgreensql.Create(SYS);
                   greensql.START();
                   exit();
              end;
            if ParamStr(2)='amanda' then begin
               amanda:=tamanda.Create(GLOBAL_INI.SYS);
               amanda.START();
               amanda.free;
               exit();
            end;

            if ParamStr(2)='tomcat' then begin
               tomcat:=ttomcat.Create(GLOBAL_INI.SYS);
               tomcat.START();
               tomcat.free;
               exit();
            end;
            if ParamStr(2)='openemm' then begin
               openemm:=topenemm.Create(GLOBAL_INI.SYS);
               openemm.START();
               openemm.free;
               exit();
            end;
            if ParamStr(2)='openemm-sendmail' then begin
               openemm:=topenemm.Create(GLOBAL_INI.SYS);
               openemm.SENDMAIL_START();
               openemm.free;
               exit();
            end;



            if ParamStr(2)='apt-mirror' then begin
                   SYS:=Tsystem.Create;
                   zapt_mirror:=tapt_mirror.Create(SYS);
                   zapt_mirror.START();
                   exit();
              end;

            if ParamStr(2)='ddclient' then begin
                   SYS:=Tsystem.Create;
                   zddclient:=tddclient.Create(SYS);
                   zddclient.START();
                   exit();
              end;

              if ParamStr(2)='artica-status' then begin
                   SYS:=Tsystem.Create;
                   articastatus:=tarticastatus.Create(SYS);
                   articastatus.START();
                   exit();
              end;

              if ParamStr(2)='artica-exec' then begin
                   SYS:=Tsystem.Create;
                   articaexecutor:=tarticaexecutor.Create(SYS);
                   articaexecutor.START();
                   exit();
              end;

              if ParamStr(2)='artica-back' then begin
                   SYS:=Tsystem.Create;
                   articabackground:=tarticabackground.Create(SYS);
                   articabackground.START();
                   exit();
              end;



              if ParamStr(2)='tftpd' then begin
                   SYS:=Tsystem.Create;
                   tftpd:=ttftpd.Create(SYS);
                   tftpd.START();
                   exit();
              end;


              if ParamStr(2)='dropbox' then begin
                   SYS:=Tsystem.Create;
                   dropbox:=tdropbox.Create(SYS);
                   dropbox.START();
                   exit();
              end;

              if ParamStr(2)='artica-policy' then begin
                   SYS:=Tsystem.Create;
                   articapolicy:=tarticapolicy.Create(SYS);
                   articapolicy.START();
                   exit();
              end;




              if ParamStr(2)='dkim-milter' then begin
                   SYS:=Tsystem.Create;
                   dkimmilter:=tdkimmilter.Create(SYS);
                   dkimmilter.START();
                   exit();
              end;



             if ParamStr(2)='ocsagent' then begin
                   SYS:=Tsystem.Create;
                   ocsagent:=tocsagent.Create(SYS);
                   ocsagent.START();
                   exit();
              end;

             if ParamStr(2)='openssh' then begin
                   SYS:=Tsystem.Create;
                   sshd:=tsshd.Create(SYS);
                   sshd.START();
                   exit();
              end;
             if ParamStr(2)='auth-logger' then begin
                   SYS:=Tsystem.Create;
                   sshd:=tsshd.Create(SYS);
                   sshd.START_LOGGER();
                   exit();
              end;

             if ParamStr(2)='dkfilter' then begin
                   SYS:=Tsystem.Create;
                   dkfilter:=tdkfilter.Create(SYS);
                   dkfilter.START();
                   exit();
              end;



             if ParamStr(2)='auditd' then begin
                   SYS:=Tsystem.Create;
                   auditd:=tauditd.Create(SYS);
                   auditd.START();
                   exit();
              end;

             if ParamStr(2)='squidguard-http' then begin
                   SYS:=Tsystem.Create;
                   squidguard_page:=tsquidguard_page.Create(SYS);
                   squidguard_page.START();
                   exit();
              end;

            if ParamStr(2)='fetchmail-logger' then begin
                   fetchmail.FETCHMAIL_LOGGER_START();
                   exit();
              end;




              if ParamStr(2)='gluster' then begin
                 gluster:=tgluster.Create(SYS);
                 gluster.START();
                 exit();
              end;

              if ParamStr(2)='zabbix' then begin
                 zabbix:=tzabbix.Create(SYS);
                 zabbix.START();
                 exit();
              end;

              if ParamStr(2)='gluster-cli' then begin
                 gluster:=tgluster.Create(SYS);
                 gluster.START_CLIENT();
                 exit();
              end;
              
              if ParamStr(2)='watchdog' then begin
               cron:=tcron.Create(GLOBAL_INI.SYS);
               cron.START();
               monit.START();
               cron.free;
               exit();
            end;

              if ParamStr(2)='fcron' then begin
               cron:=tcron.Create(GLOBAL_INI.SYS);
               cron.START();
               cron.free;
               exit();
            end;

              if ParamStr(2)='mgreylist' then begin
                 if not NetWorkAvailable then exit();
                 zmiltergreylist.MILTER_GREYLIST_START();
                 exit();
              end;

              if ParamStr(2)='ntpd' then begin
                 if not NetWorkAvailable then exit();
                 zntpd.NTPD_START();
                 exit();
              end;

              if ParamStr(2)='dstat' then begin
                 dstat:=tdstat.Create(SYS);
                 dstat.START();
                 dstat.START_TOP_MEMORY();
                 exit();
              end;

              if ParamStr(2)='dstat-top-mem' then begin
                 dstat:=tdstat.Create(SYS);
                 dstat.START_TOP_MEMORY();
                 exit();
              end;

              if ParamStr(2)='pdns' then begin
                 pdns:=tpdns.Create(SYS);
                 pdns.START();
                 exit();
              end;

              if ParamStr(2)='dstat-top-cpu' then begin
                 dstat:=tdstat.Create(SYS);
                 dstat.START_TOP_CPU();
                 exit();
              end;

              if ParamStr(2)='pommo' then begin
                 lighttpd:=Tlighttpd.Create(SYS);
                 lighttpd.POMMO_ALIASES();
                 exit();
              end;

             if ParamStr(2)='imap' then begin
                 saslauthd:=tsaslauthd.Create(SYS);
                 saslauthd.START();
                 saslauthd.Free;
                 ccyrus.CYRUS_DAEMON_START();
                 exit();
              end;
              
             if ParamStr(2)='spfmilter' then begin
                 spfm:=tspf.Create();
                 spfm.SPFMILTER_START();
                 spfm.free;
                 exit();
              end;
              

           if ParamStr(2)='kas3' then begin
                 if not NetWorkAvailable then exit();
                 kas3.START();
                 exit();
              end;


           if ParamStr(2)='postfilter' then begin
              zpostfilter:=tpostfilter.Create(SYS);
              zpostfilter.START();
              halt(0);
           end;

             if ParamStr(2)='dhcp' then begin
                 if not NetWorkAvailable then exit();
                dhcp3:=tdhcp3.Create(SYS);
                dhcp3.START();
                exit();
             end;

            if ParamStr(2)='openvpn' then begin
                 if not NetWorkAvailable then exit();
                 zopenvpn:=topenvpn.CReate(SYS);
                 zopenvpn.START();
                 exit();
              end;


            if ParamStr(2)='cups' then begin
                 cups:=tcups.CReate();
                 cups.START();
                 exit();
              end;
             


             if ParamStr(2)='kav6' then begin
                 if not NetWorkAvailable then exit();
                 zkavmilter:=tkavmilter.Create(SYS);
                 zkavmilter.START();
                 zkavmilter.free;
                 exit();
              end;

            if ParamStr(2)='kavmilter' then begin
                 if not NetWorkAvailable then exit();
                 zkavmilter:=tkavmilter.Create(SYS);
                 zkavmilter.START();
                 zkavmilter.free;
                 exit();
              end;

             if ParamStr(2)='fetchmail' then begin
                 if not NetWorkAvailable then exit();
                 fetchmail.FETCHMAIL_START_DAEMON();
                 exit();
              end;


             if ParamStr(2)='apache-groupware' then begin
                 if not NetWorkAvailable then exit();
                 opengoo:=topengoo.Create(SYS);
                 opengoo.START();
                 exit();
              end;
              
             if ParamStr(2)='mailspy' then begin
                 mailspy:=tmailspy.Create(GLOBAL_INI.SYS);
                 mailspy.START();
                 exit();
              end;

              if ParamStr(2)='retranslator' then begin
                 retranslator:=tkretranslator.Create(GLOBAL_INI.SYS);
                 retranslator.START();
                 exit();
              end;

              if ParamStr(2)='retranslator-tsk' then begin
                 retranslator:=tkretranslator.Create(GLOBAL_INI.SYS);
                 retranslator.START();
                 exit();
              end;




             if ParamStr(2)='squid' then begin
                 if not NetWorkAvailable then exit();
                 zClam.CLAMD_START();
                 zdansguardian.C_ICAP_START();
                 kav4proxy.KAV4PROXY_START();
                 zsquid.SQUID_START();
                 zdansguardian.DANSGUARDIAN_START();
                 ufdbguardd:=tufdbguardd.Create(SYS);
                 ufdbguardd.START();

                 squidguard:=tsquidguard.Create(SYS);
                 squidguard.START();


                 exit();
              end;

              if ParamStr(2)='kav4proxy' then begin
                 kav4proxy.KAV4PROXY_START();
                 exit();
              end;

              if ParamStr(2)='squid-cache' then begin
                 zsquid.START_SIMPLE();
                 exit();
              end;

              if ParamStr(2)='proxy-pac' then begin
                 zsquid.PROXY_PAC_START();
                 exit();
              end;

              if ParamStr(2)='squid-tail' then begin
                 zsquid.TAIL_START();
                 exit();
              end;

              if ParamStr(2)='squidclamav-tail' then begin
                 zsquid.TAIL_SQUIDCLAMAV_START();
                 exit();
              end;


            if ParamStr(2)='cicap' then begin
                 if not NetWorkAvailable then exit();
                 zdansguardian.C_ICAP_START();
                 exit();
              end;



              if ParamStr(2)='stunnel' then begin
                 if not NetWorkAvailable then exit();
                 zstunnel.STUNNEL_START();
                 exit();
              end;

              if ParamStr(2)='dkim' then begin
                 if not NetWorkAvailable then exit();
                 zdkim.DKIM_FILTER_START();
                 exit();
              end;

             if ParamStr(2)='boa' then begin
                 GLOBAL_INI.BOA_START();
                 exit();
              end;

             if ParamStr(2)='dansguardian' then begin
                 if not NetWorkAvailable then exit();
                 zdansguardian.DANSGUARDIAN_START();
                 zClam.CLAMD_START();
                 exit();
              end;

             if ParamStr(2)='dansgardian' then begin
                 if not NetWorkAvailable then exit();
                 zdansguardian.DANSGUARDIAN_START();
                 zClam.CLAMD_START();
                 exit();
              end;

              if ParamStr(2)='mysql' then begin
                 zmysql.SERVICE_START();
                 syslogng.START();
                 exit();
              end;
              
              if ParamStr(2)='p3scan' then begin
                 p3scan.START();
                 exit();
              end;
              
              if ParamStr(2)='bogom' then begin
                 if not NetWorkAvailable then exit();
                 bogom:=tbogom.Create(GLOBAL_INI.SYS);
                 bogom.START();
                 bogom.FRee;

                 exit();
              end;
              

             if ParamStr(2)='collectd' then begin
                 collectd:=tcollectd.Create(GLOBAL_INI.SYS);
                 collectd.START();
                 collectd.FRee;

                 exit();
              end;

           if ParamStr(2)='amavis' then begin
                 if not NetWorkAvailable then exit();
                 amavis:=tamavis.Create(GLOBAL_INI.SYS);
                 amavis.START();
                 exit();
              end;

              if ParamStr(2)='amavis-milter' then begin
                 amavis:=Tamavis.Create(GLOBAL_INI.SYS);
                 amavis.START_MILTER();
                 exit();
              end;


            if ParamStr(2)='dansguardian-tail' then begin
                 zdansguardian.DANSGUARDIAN_TAIL_START();
                 exit();
            end;

             if ParamStr(2)='pptpd-clients' then begin
                  SYS:=Tsystem.Create();
                  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.pptpd.php --clients-start');
                  halt(0);
             end;

              if ParamStr(2)='apache' then begin
                 GLOBAL_INI.APACHE_ARTICA_START();
                 zroundcube.ROUNDCUBE_START_SERVICE();
                 obm:=tobm.Create(GLOBAL_INI.SYS);
                 obm.SERVICE_START();
                 exit();
              end;

              if ParamStr(2)='ftp' then begin
                 if not NetWorkAvailable then exit();
                 zPureftpd.PURE_FTPD_START();
                 exit();
              end;

              if ParamStr(2)='samba' then begin
                 if not NetWorkAvailable then exit();
                 zsamba.SAMBA_START();
                 cups:=tcups.CReate();
                 cups.START();
                 exit();
              end;

             if ParamStr(2)='winbindd' then begin
                 zsamba.SAMBA_WINBINDD_START();
                 exit();
            end;

             if ParamStr(2)='daemons' then begin
                 GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
                 exit();
            end;


              if ParamStr(2)='daemon' then begin
                 if not SYS.BuildPids() then exit();
                 forcedirectories('/etc/artica-postfix/pids');
                 TimeInt:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/cron.2/watchdog-daemon');
                 if TimeInt=0 then exit;
                 forcedirectories('/etc/artica-postfix/cron.1');
                 forcedirectories('/etc/artica-postfix/cron.2');
                 forcedirectories('/etc/artica-postfix/cron.2');
                 fpsystem('/bin/rm /etc/artica-postfix/cron.2/watchdog-daemon');
                 fpsystem('/bin/touch /etc/artica-postfix/cron.2/watchdog-daemon');
                 GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
                 monit.START();
                 exit();
              end;

              if ParamStr(2)='monit' then begin
                 monit.START();
                 exit();
              end;

              if ParamStr(2)='assp' then begin
                 assp:=tassp.Create(GLOBAL_INI.SYS);
                 assp.START();
                 assp.free;
                 exit();
              end;





              if ParamStr(2)='spamd' then begin
                 if not NetWorkAvailable then exit();
                 zSpamass.SPAMASSASSIN_START();
                 zSpamass.MILTER_START();
                 exit();
              end;

              if ParamStr(2)='clamd' then begin
                 if not NetWorkAvailable then exit();
                 zClam.CLAMD_START();
                 exit();
              end;

              if ParamStr(2)='freshclam' then begin
                 if not NetWorkAvailable then exit();
                 zClam.FRESHCLAM_START();
                 exit();
              end;

              if ParamStr(2)='clammilter' then begin
                 if not NetWorkAvailable then exit();
                 zClam.CLAMD_START();
                 zClam.MILTER_START();
                 zClam.FRESHCLAM_START();
                 exit();
              end;

              if ParamStr(2)='saslauthd' then begin
                 saslauthd:=tsaslauthd.Create(GLOBAL_INI.SYS);
                 saslauthd.START();

                 saslauthd.FRee;
                 exit();
              end;


              if ParamStr(2)='mysqmail' then begin
                 zpostfix.MYSQMAIL_START();
                 exit();
              end;


              if ParamStr(2)='dnsmasq' then begin
                 if not NetWorkAvailable then exit();
                 dnsmasq.DNSMASQ_START_DAEMON();

                 exit();
              end;

              if ParamStr(2)='postfix' then begin
                 if not NetWorkAvailable then exit();
                 zpostfix.POSTFIX_START_LIMITED();

                 exit();
              end;

              if ParamStr(2)='mailgraph' then begin
                 mailgraph.MAILGRAPH_START();

                 exit();
              end;

              if ParamStr(2)='mimedefang' then begin
                 if not NetWorkAvailable then exit();
                 zmimedefang.MIMEDEFANG_START();

                 exit();
              end;
              if ParamStr(2)='roundcube' then begin
                 zroundcube.ROUNDCUBE_START_SERVICE();

                 exit();
              end;

              if ParamStr(2)='kav4samba' then begin
                 if not NetWorkAvailable then exit();
                 zkav4samba.SERVICE_START();

                 exit();
              end;

              if ParamStr(2)='bind9' then begin
                 if not NetWorkAvailable then exit();
                 bind9.START();

                 exit();
              end;
              
              if ParamStr(2)='obm' then begin
                 obm:=tobm.Create(GLOBAL_INI.SYS);
                 obm.SERVICE_START();

                 exit();
              end;
              
              if ParamStr(2)='yorel' then begin
                 GLOBAL_INI.YOREL_VERIFY_START();
                 exit();
              end;
              
              if ParamStr(2)='syslogng' then begin
                 syslogng.START();
                 exit();
              end;
              
              if ParamStr(2)='mailarchiver' then begin
                 mailarchive:=tmailarchive.Create(GLOBAL_INI.SYS);
                 mailarchive.START();
                 mailarchive.free;
                 exit();
              end;
              
               if ParamStr(2)='dotclear' then begin
                 dotclear:=tdotclear.Create(GLOBAL_INI.SYS);
                 dotclear.START();
                 dotclear.free;
                 exit();
              end;
              

           if ParamStr(2)='jcheckmail' then begin
                 jcheckmail:=tjcheckmail.Create(GLOBAL_INI.SYS);
                 jcheckmail.START();
                 jcheckmail.Free;
                 exit();
              end;

           if ParamStr(2)='nfs' then begin
                 nfs:=tnfs.Create(GLOBAL_INI.SYS);
                 nfs.START();
                 nfs.Free;
                 exit();
              end;


           if ParamStr(2)='mailman' then begin
                 if not NetWorkAvailable then exit();
                 zmailman:=tmailman.Create(GLOBAL_INI.SYS);
                 zmailman.START();
                 zmailman.Free;
                 exit();
              end;

           if ParamStr(2)='autofs' then begin
                 autofs:=tautofs.Create(GLOBAL_INI.SYS);
                 autofs.START();
                 autofs.Free;
                 exit();
              end;


           if ParamStr(2)='all' then begin
                fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/process1 --force &');
                GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
                GLOBAL_INI.START_ALL_DAEMONS();
                fpsystem(SYS.LOCATE_PHP5_BIN() +' ' + GLOBAL_INI.get_ARTICA_PHP_PATH() +'/exec.ldap.rebuild.php >/dev/null &');
                exit();
              end;



        if length(ParamStr(2))>0 then begin
               writeln('Your parameter: "' +ParamStr(2)+'" is not understood...');
               writeln('Usage:');
               writeln('/etc/init.d/artica-postfix start all|ldap|saslauthd|imap|kav6|squid|squid-cache|squid-tail|dansgardian|boa|ftp|mysql|apache|spamd|clamd|freshclam|mgreylist');
               writeln('|daemon|clammilter|dnsmasq|stunnel|postfix|mailgraph|mimedefang|roundcube|kav4samba|bind9|obm|yorel|p3scan|syslogng|mailarchive|bogom');
               writeln('|collectd|mysql|fetchmail|mailspy|amavis|retranslator|spfmilter|dotclear|jcheckmail|mailman|kas3|dhcp|cicap[openvpn|postfix-logger');
               writeln('|dansguardian-tail|apache-groupware|cups|dstat|dstat-top-mem|dstat-top-cpu|rsync|policydw|autofs|mysql-cluster|assp|pdns|gluster|gluster-cli');
               writeln('|sysloger|zabbix|kav4proxy|postfilter|vmtools|zarafa|zarafa-web|monit|wifi|proxy-pac|artica-notifier|mldonkey|backuppc|kav4fs|ocsweb|ocsagent');
               writeln('|openssh|auditd|squidguard-http|fetchmail-logger|dkfilter|ufdb|ufdb-tail|squidguard-tail|framework|dkim-milter|dropbox|artica-policy|virtualbox-web');
               writeln('|tftpd|crossroads|artica-status|artica-exec|artica-back|pptpd|pptpd-clients|apt-mirror|squidclamav-tail|ddclient|cluebringer|apachesrc');
               writeln('|sabnzbdplus|fcron|fuse|vnstat|winbindd|munin|greyhole|amavis-milter|iscsi|auth-logger|snort|greensql|amanda|zarafa-lmtp|tomcat|openemm');
               writeln('|sendmail-openemm');
               exit();
            end;

            

              SYS.THREAD_COMMAND_SET(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/process1 --force');
              GLOBAL_INI.SYSTEM_START_ARTICA_DAEMON();
              fpsystem(SYS.LOCATE_PHP5_BIN() +' ' + GLOBAL_INI.get_ARTICA_PHP_PATH() +'/exec.ldap.rebuild.php >/dev/null &');
              writeln('');
              writeln('');
              exit();
end;
procedure Tclass_install.ARTICA_CD_KASPERSKY();
begin
  if FileExists('/home/artica/packages/artica-postfix-security-1.0.deb') then begin;
     if FileExists(postfix.POSFTIX_POSTCONF_PATH()) then begin
        if FileeXISTS(SYS.LOCATE_APT_GET())  THEN begin
         if Not FileExists('/usr/local/ap-mailfilter3/bin/ap-mailfilter') then fpsystem('dpkg -i /home/artica/packages/artica-postfix-security-1.0.deb');
        end;
     end;
  end;
  
  if FileExists('/home/artica/packages/artica-samba-security-1.0.deb') then begin;
     if FileExists(samba.SMBD_PATH()) then begin
        if FileeXISTS(SYS.LOCATE_APT_GET())  THEN begin
         if not FileExists('/opt/kaspersky/kav4samba/bin/kav4samba-kavscanner') then begin
            fpsystem('dpkg -i /home/artica/packages/artica-samba-security-1.0.deb');
            fpsystem('/etc/init.d/artica-postfix restart samba');
         end;
        end;
     end;
  end

  
end;
//##############################################################################



procedure Tclass_install.INIT_ARTICA();
var GLOBAL_INI:myconf;
begin
  if Not DirectoryExists('/opt/artica/lib/perl/5.8.8') then begin
     if FileExists('/opt/artica/lib/perl/5.8.8.tar.gz') then begin
           writeln('Installing artica perl');
           fpsystem('tar -C /opt/artica/lib/perl -xf /opt/artica/lib/perl/5.8.8.tar.gz');
     end;
  end;
  
  GLOBAL_INI:=myconf.Create;
  writeln('Installing artica core.........: Remove old files....');
  if DirectoryExists(GLOBAL_INI.get_ARTICA_PHP_PATH() +'/ressources/logs') then fpsystem('/bin/rm -rf ' + GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/logs');
  
     
     if not FileExists('/etc/init.d/artica-postfix') then begin
          writeln('Installing artica core.........: Creating init.d scripts');
          install_init_d();
          fpsystem('/etc/init.d/artica-postfix restart');
     end;
     

     MYSQL_RECONFIGURE();
     MYSQL_RECONFIGURE();
     
     

  
//usr/share/artica-postfix/bin/sqlgrey-logstats.pl -w </var/log/syslog

end;
//##############################################################################
function Tclass_install.TESTING_INTERNET():boolean;
var
   GLOBAL_INI:myconf;
   M:TiniFile;
   version:string;
begin
writeln('');
writeln('');
writeln('***********************************************************************');
GLOBAL_INI:=myconf.Create;

   result:=false;
   if not FileExists('/usr/bin/wget') then begin
       writeln('Error could not find /usr/bin/wget');
       writeln('Please try to install this tool before installing artica');
       writeln('***********************************************************************');
       exit;
   end;
    if fileExists('/tmp/auto.update.cf') then fpsystem('/bin/rm /tmp/auto.update.cf');
    GLOBAL_INI.WGET_DOWNLOAD_FILE('http://www.artica.fr/auto.update.php','/tmp/auto.update.cf');
    if not FileExists('/tmp/auto.update.cf') then begin
        writeln('Error while testing http://www.artica.fr/auto.update.php');
        writeln('Please make sure your connection trought Internet is OK');
        writeln('***********************************************************************');
        exit;
    end;
    
    M:=TiniFile.Create('/tmp/auto.update.cf');
    version:=M.ReadString('NEXT','artica','');
    writeln('Current version of artica in web server is : ' + version);
    if length(version)=0 then begin
       writeln('Error while testing http://www.artica.fr/auto.update.php');
       writeln('Please make sure your connection trought Internet is OK');
       writeln('***********************************************************************');
       exit(false);
    end;
writeln('***********************************************************************');
writeln('');
writeln('');
exit(true);
end;




procedure Tclass_install.GEOIP_UPDATES();
var
   database_path,appname:string;
   GeoIP:TGeoIP;
   geoip_date:integer;
   geoip_little_date:integer;
   geoip_new_date:integer;
   geoip_little_server_date:integer;
   GeoLiteCity_date:integer;
   GeoIPASNum_date:integer;
   tempstr,monthz,tempdir:string;
   TMP:TstringList;
   zRegExpr:TRegExpr;
   LOGS:TLogs;
   D:Boolean;
   i:Integer;
   MD:TDateTime;
   myYear,myMonth,myDay:word;
   GLOBAL_INI:Myconf;
   GLOBAL_URI:string;
   download_geolitecity:boolean;
   download_geoIp:boolean;
   download_GeoIPASNum:boolean;
   gunzip_files:string;
begin
  GLOBAL_INI:=MyConf.Create;
   myYear:=0;
   myMonth:=0;
   myDay:=0;
   tempdir:='/tmp/'+ GLOBAL_INI.TEMP_SEC();
   GLOBAL_URI:='http://geolite.maxmind.com/download/geoip/database/';
   download_geolitecity:=false;
   download_GeoIPASNum:=false;
   database_path:='/usr/local/share/GeoIP';
   ForceDirectories(database_path);
   zRegExpr:=TRegExpr.Create;
   appname:='APP_GEOIP';
   LOGS:=Tlogs.create;
   D:=LOGS.COMMANDLINE_PARAMETERS('--verbose');
   zRegExpr.expression:='\s+([0-9]+)\s+';
   forcedirectories(tempdir);
   
   if FileExists(database_path + '/GeoIP.dat') then begin
      GeoIP := TGeoIP.Create(database_path + '/GeoIP.dat');
      tempstr:=GeoIP.GetDatabaseInfo;
      try
      if zRegExpr.Exec(tempstr) then begin
         geoip_date:=StrToInt(zRegExpr.Match[1]);
         geoip_little_date:=StrToInt(Copy(zRegExpr.Match[1],0,length(zRegExpr.Match[1])-2));
         end;
      finally
      GeoIP.Free;
      end;
      
       if D then writeln('GeoIP Database version: ',geoip_date);
   end else begin

       if D then writeln('GeoIP Database no such file');
   end;



   if FileExists(database_path + '/GeoLiteCity.dat') then begin
       GeoIP := TGeoIP.Create(database_path + '/GeoLiteCity.dat');
       tempstr:=GeoIP.GetDatabaseInfo;
       try
       if zRegExpr.Exec(tempstr) then GeoLiteCity_date:=StrToInt(zRegExpr.Match[1]);
      finally
      GeoIP.Free;
      end;
      if D then writeln('Geo Lite City Database version: ',GeoLiteCity_date);
   end else begin
      if D then writeln('Geo Lite City Database no such file');
      download_geolitecity:=true;
   end;




   if FileExists(database_path + '/GeoIPASNum.dat') then begin
       GeoIP := TGeoIP.Create(database_path + '/GeoIPASNum.dat');
       tempstr:=GeoIP.GetDatabaseInfo;
       try
       if zRegExpr.Exec(tempstr) then GeoIPASNum_date:=StrToInt(zRegExpr.Match[1]);
      finally
      GeoIP.Free;
      end;
      if D then writeln('Geo IP AS number ISP - organisation with the IP Database version: ',GeoIPASNum_date);
   end else begin
      if D then writeln('Geo IP AS number ISP no such file');
      download_GeoIPASNum:=true;
   end;
    GLOBAL_INI.WGET_DOWNLOAD_FILE(GLOBAL_URI,'/tmp/geo-update.html');


    if not FileExists('/tmp/geo-update.html') then begin
            LOGS.INSTALL_MODULES(appname,'Unable to stat temp file...');
            if D then writeln('Failed  stat temp file');
            exit;
    end;

    zRegExpr.expression:='GeoLiteCity\.dat\.gz.+?([0-9\-a-zA-Z]+)\s+';
    TMP:=TstringList.Create;
    TMP.LoadFromFile('/tmp/geo-update.html');
    For i:=0 to TMP.Count-1 do begin
        if zRegExpr.Exec(TMP.Strings[i]) then begin
           logs.Debuglogs('GeoIP Database version on remote server:'+zRegExpr.Match[1]);
           try
            tempstr:=LOGS.TRANSFORM_DATE_MONTH(zRegExpr.Match[1]);
            if D then writeln('Converting...:',tempstr);
            MD:=StrToDate(tempstr);
            DecodeDate(MD, myYear, myMonth, myDay);
            monthz:=IntToStr(myMonth);
            if length(monthz)=1 then monthz:='0' + monthz;
            tempstr:=IntToStr(myDay);
            if length(tempstr)=1 then tempstr:='0' + tempstr;
            tempstr:=IntToStr(myYear) + monthz+tempstr;
            geoip_new_date:=StrToInt(tempstr);
            geoip_little_server_date:=StrToInt(Copy(tempstr,0,length(tempstr)-2));
            if D then writeln('GeoIP Database version on remote server:',geoip_new_date);
           finally
           
           end;
        end;
    
    end;
    
    if geoip_little_server_date=geoip_little_date then begin
         LOGS.syslogs('Geoip databases...............:OK');
         LOGS.syslogs('www.maxmind.com...............:'+IntToStr(geoip_new_date));
         LOGS.syslogs('GeoIP.........................:'+IntToStr(geoip_date));
         LOGS.syslogs('GeoIPASNum....................:'+IntToStr(GeoIPASNum_date));
         LOGS.syslogs('GeoLiteCity...................:'+IntToStr(GeoLiteCity_date));
         LOGS.syslogs('no new updates available for GeoIP');
         exit();
    end;

 download_geoIp:=true;

 if geoip_new_date=geoip_date then begin
       LOGS.syslogs('no new updates available for GeoIP');
       download_geoIp:=false;
   end;
    
   if geoip_new_date<geoip_date then begin
        LOGS.syslogs('no new updates available for GeoIP');
        download_geoIp:=false;
   end;

     if download_geoIp=true then begin
        LOGS.syslogs('Downloading updates...GeoIP.dat.gz');
        GLOBAL_INI.WGET_DOWNLOAD_FILE(GLOBAL_URI+'GeoLiteCountry/GeoIP.dat.gz',tempdir+'/GeoIP.dat.gz');
        download_geolitecity:=true;
        download_GeoIPASNum:=true;
     end;

     if download_geolitecity=true then begin
        LOGS.syslogs('Downloading updates...GeoLiteCity.dat.gz');
        GLOBAL_INI.WGET_DOWNLOAD_FILE(GLOBAL_URI+'GeoLiteCity.dat.gz',tempdir+'/GeoLiteCity.dat.gz');
     end;

     if download_GeoIPASNum=true then begin
        LOGS.syslogs('Downloading updates...GeoIPASNum.dat.gz');
        GLOBAL_INI.WGET_DOWNLOAD_FILE(GLOBAL_URI+'asnum/GeoIPASNum.dat.gz',tempdir+'/GeoIPASNum.dat.gz');
     end;
     ForceDirectories('/var/lib/GeoIP');
     if FileExists(tempdir+'/GeoIPASNum.dat.gz') then gunzip_files:=gunzip_files+' GeoIPASNum.dat.gz' else LOGS.syslogs('GeoIPASNum.dat.gz no such file');
     if FileExists(tempdir+'/GeoIP.dat.gz') then gunzip_files:=gunzip_files+' GeoIP.dat.gz' else LOGS.syslogs('GeoIP.dat.gz no such file');
     if FileExists(tempdir+'/GeoLiteCity.dat.gz') then gunzip_files:=gunzip_files+' GeoLiteCity.dat.gz' else LOGS.syslogs('GeoLiteCity.dat.gz no such file');
     gunzip_files:=trim(gunzip_files);
     LOGS.syslogs('Downloading updates...Finish gunbzip files="'+gunzip_files+'"');
     if length(trim(gunzip_files))>0 then begin
        logs.Debuglogs('cd '+tempdir+' && /bin/gunzip -f -q '+gunzip_files);
        fpsystem('cd '+tempdir+' && /bin/gunzip -f -q '+gunzip_files);
        logs.Debuglogs('Installing databases...');
        if FileExists(tempdir+'/GeoIP.dat') then fpsystem('/bin/mv '+tempdir+'/GeoIP.dat ' + database_path + '/GeoIP.dat');
        if FileExists(tempdir+'/GeoIPASNum.dat') then fpsystem('/bin/mv '+tempdir+'/GeoIPASNum.dat ' + database_path + '/GeoIPASNum.dat');
        if FileExists(tempdir+'/GeoLiteCity.dat') then fpsystem('/bin/mv '+tempdir+'/GeoLiteCity.dat ' + database_path + '/GeoLiteCity.dat');
     end else begin
         LOGS.syslogs('Downloading updates... Failed');
     end;
     ForceDirectories('/usr/share/GeoIP');
     logs.OutputCmd('/bin/cp -f /usr/local/share/GeoIP/* /usr/share/GeoIP/');
     if FileExists('/usr/local/share/GeoIP/GeoLiteCity.dat') then begin
       logs.OutputCmd('/bin/cp -f /usr/local/share/GeoIP/GeoLiteCity.dat /usr/share/GeoIP/GeoIPCity.dat');
       logs.OutputCmd('/bin/cp -f /usr/local/share/GeoIP/GeoLiteCity.dat /usr/local/share/GeoIP/GeoIPCity.dat');
       logs.OutputCmd('/bin/cp -f /usr/local/share/GeoIP/GeoLiteCity.dat /var/lib/GeoIP/GeoIPCity.dat');
     end;






     if D then writeln('Updating...Finish');
     LOGS.syslogs('updates...Finish -> Send notifications');
     logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update new GeoIP version ' + IntToStr(geoip_new_date),'your server is now up-to-date with the new GeoIP version ' + IntToStr(geoip_new_date),'update');
     LOGS.syslogs('end...');

end;

//##############################################################################
procedure Tclass_install.AWSTATS_INSTALL();begin INSTALL_AWSTATS();end;


//##############################################################################

procedure Tclass_install.CYRUS_IMPAD_INIT();
var
   SYS:Tsystem;
   LOGS:Tlogs;

begin
   LOGS:=Tlogs.Create;
   SYS:=Tsystem.Create();
   LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize cyrus-imapd first..:Creating cyrus user');
   SYS.AddUserToGroup('cyrus','cyrus','','');
   SYS.AddUserToGroup('mail','mail','','');
   
   forcedirectories('/var/lib/cyrus/db');
   fpsystem('/bin/chmod -R 0755 /var/lib/cyrus');
   fpsystem('/bin/chown -R cyrus:mail /var/lib/cyrus');
   CYRUS_CHECK();
   
end;
//##############################################################################
procedure Tclass_install.PFQUEUE_INSTALL();
var
   LOG        :Tlogs;
   postcompile:string;
   source     :string;
begin
    LOG:=TLogs.Create;
    if FileExists('/opt/artica/lib/libpfq_postfix2.la') then begin
       LOG.INSTALL_MODULES('APP_POSTFIX','pfqueue.......................:OK');
       exit;
    end;

    LIBNCURSES_INSTALL();
    LOG:=Tlogs.Create;
    if not FileExists('/opt/artica/lib/libncurses.a') then begin
      LOG.INSTALL_MODULES('APP_POSTFIX','pfqueue.......................:Failed to stat ncurses');
      exit;
    end;
    
    source:=COMPILE_GENERIC_APPS('APP_POSTFIX','pfqueue','pfqueue');
    if not DirectoryExists(source) then begin
       LOG.INSTALL_MODULES('APP_POSTFIX','pfqueue.......................:Failed extract sources');
       exit;
    end;
    
      postcompile:='cd ' + source + '&& ./configure --prefix=/opt/artica ';
      postcompile:=postcompile  + LD_LIBRARY_PATH + ' ' + CPPFLAGS;
      postcompile:=postcompile + ' && make && make install';
      LOG.INSTALL_MODULES('APP_POSTFIX',postcompile);
      fpsystem(postcompile);
    if FileExists('/opt/artica/lib/libpfq_postfix2.la') then begin
       LOG.INSTALL_MODULES('APP_POSTFIX','pfqueue.......................:OK');
       exit;
    end

end;

procedure Tclass_install.POSTFIX_INIT();
var
   SYS:Tsystem;
   LOGS:Tlogs;

begin
   LOGS:=Tlogs.Create;
   SYS:=Tsystem.Create();
   LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize postfix first......:Creating postfix and postdrop users');
   SYS.AddUserToGroup('postfix','postfix','','');
   SYS.AddUserToGroup('postdrop','postdrop','','');
   if DirectoryExists('/etc/postfix') then fpsystem('/bin/chown -R root:root /etc/postfix');
   if FileExists('/etc/postfix/post-install') then begin
      fpsystem('/etc/postfix/post-install create-missing');
   end else begin
       if FileExists('/usr/lib/postfix/post-install') then fpsystem('/usr/lib/postfix/post-install');
   end;
   LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize postfix first......:Force creating spool folder & security');
   postfix.POSTFIX_INITIALIZE_FOLDERS();
   LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize postfix first......:running other parameters');
   postfix.POSTFIX_INI_TD();
   LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize postfix first......:Set main.cf');
   POSTFIX_CONFIGURE_MAIN_CF();
   LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize postfix first......:Set sasl');
   POSTFIX_SET_SASL();
   LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize postfix first......:Set awsasts');
   AWSTAT_RECONFIGURE();
   SYS.free;
end;
//##############################################################################
procedure Tclass_install.FDM_INSTALL();
var
   source:string;
   LOGS:Tlogs;
   tmp:string;
begin
    LOGS:=TLOGS.Create;
if FileExists('/usr/local/bin/fdm') then begin
    LOGS.INSTALL_MODULES('APP_ARTICA','fdm is already installed');
    exit;
end;

LOGS:=TLogs.Create;
source:=COMPILE_GENERIC_APPS('APP_ARTICA','fdm','fdm');
tmp:=LOGS.FILE_TEMP();
if DirectoryExists(source) then begin
   LOGS.INSTALL_MODULES('APP_ARTICA','compiling source stored in "' + source + '"');
   LOGS.INSTALL_MODULES('APP_ARTICA','cd ' + source + '&& make >'+tmp + ' 2>&1');
   fpsystem('cd ' + source + '&& make >'+tmp + ' 2>&1');
   LOGS.INSTALL_MODULES('APP_ARTICA',logs.ReadFromFile(tmp));
   logs.DeleteFile(tmp);
   
   LOGS.INSTALL_MODULES('APP_ARTICA','cd ' + source + ' && make install >'+tmp + ' 2>&1');
   fpsystem('cd ' + source + ' && make install >'+tmp + ' 2>&1');
   LOGS.INSTALL_MODULES('APP_ARTICA',logs.ReadFromFile(tmp));
   logs.DeleteFile(tmp);
end;


if FileExists('/usr/local/bin/fdm') then begin
    LOGS.INSTALL_MODULES('APP_ARTICA','success install FDM');
    exit;
end else begin
  LOGS.INSTALL_MODULES('APP_ARTICA','failed install FDM');
end;


end;
//##############################################################################

procedure Tclass_install.ChangeAllCertificates();
var

   LOGS:Tlogs;
   postfix:tpostfix;
   lighttpd:Tlighttpd;
   cyrus:Tcyrus;
//   gbini:myconf;
   apache_artica:tapache_artica;
   squid:Tsquid;
   zmysql:tmysql_daemon;
begin
  LOGS:=Tlogs.Create;
  zmysql:=tmysql_daemon.Create(SYS);
  logs.Debuglogs('Change ssl certificate...');
  logs.Debuglogs('Generate configuration file');
  SYS.OPENSSL_CERTIFCATE_CONFIG();

  lighttpd:=tlighttpd.Create(SYS);
  logs.Debuglogs('Change lighttpd certificate');
  lighttpd.LIGHTTPD_CERTIFICATE();
  lighttpd.LIGHTTPD_STOP();
  lighttpd.LIGHTTPD_START();
  
  cyrus:=Tcyrus.Create(SYS);
  logs.DeleteFile('/etc/ssl/certs/cyrus.pem');
  logs.Debuglogs('Change cyrus certificate');
  cyrus.CYRUS_CERTIFICATE();
  cyrus.CYRUS_DAEMON_STOP();
  cyrus.CYRUS_DAEMON_START();
  
  logs.Debuglogs('Change MySQL certificate');
  zmysql.SSL_KEY();
  zmysql.TUNE_MYSQL();
  zmysql.SERVICE_STOP();
  zmysql.SERVICE_START();



  postfix:=tpostfix.Create(SYS);
  logs.Debuglogs('Change Postfix certificate');
  fpsystem('rm -rf /etc/ssl/certs/postfix/*');
  postfix.GENERATE_CERTIFICATE();
  postfix.POSTFIX_RELOAD();

  fpsystem('/bin/rm -rf /etc/ssl/certs/apache/*');
  fpsystem('/etc/init.d/artica-postfix restart apache-groupware');
  fpsystem('/etc/init.d/artica-postfix restart ocsweb');


  logs.Debuglogs('Change Apache certificate');
  apache_artica:=tapache_artica.Create(SYS);
  apache_artica.APACHE_ARTICA_SSL_KEY();

  logs.Debuglogs('Change OpenLDAP certificate');
  fpsystem('rm -rf /etc/ssl/certs/openldap/*');
  fpsystem('/etc/init.d/artica-postfix restart ldap &');


  squid:=tsquid.Create;
  if FileExists(squid.SQUID_BIN_PATH()) then begin
       logs.Debuglogs('Change squid certificate');
       fpsystem('rm -rf /etc/squid3/ssl/*');
       fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.squid.php --certificate');
  end;
  squid.free;





  

end;


procedure tclass_install.CLAMAV_RECONFIGURE();
var
   l:TstringList;
   CLlogs:Tlogs;

   clamav:Tclamav;

begin
    l:=TstringList.Create;
    CLlogs:=Tlogs.Create;
    clamav:=Tclamav.Create;
    
if not FileExists('/opt/artica/sbin/clamd') then begin
        CLlogs.INSTALL_MODULES('APP_ARTICA','clamav........................:Unable to stat /opt/artica/sbin/clamd !, stop');
        exit;
    end;

    
clamav.CLAMD_STOP();
    
l.Add('LogFile /opt/artica/logs/clamav/clamd.log');
l.Add('#LogFileUnlock yes');
l.Add('LogFileMaxSize 2M');
l.Add('LogTime yes');
l.Add('LogClean yes');
l.Add('LogSyslog yes');
l.Add('LogFacility LOG_MAIL');
l.Add('#LogVerbose yes');
l.Add('PidFile /var/run/clamav/artica-clamd.pid');
l.Add('#TemporaryDirectory /var/tmp');
l.Add('#DatabaseDirectory /var/lib/clamav');
l.Add('LocalSocket /opt/artica/clamav/clamd.socket');
l.Add('FixStaleSocket yes');
l.Add('#TCPSocket 3310');
l.Add('#TCPAddr 127.0.0.1');
l.Add('MaxConnectionQueueLength 30');
l.Add('StreamMaxLength 20M');
l.Add('StreamMinPort 30000');
l.Add('StreamMaxPort 32000');
l.Add('MaxThreads 20');
l.Add('ReadTimeout 300');
l.Add('IdleTimeout 60');
l.Add('MaxDirectoryRecursion 20');
l.Add('FollowDirectorySymlinks yes');
l.Add('FollowFileSymlinks yes');
l.Add('SelfCheck 600');
l.Add('#VirusEvent /usr/local/bin/send_sms 123456789 "VIRUS ALERT: %v"');
l.Add('User clamav');
l.Add('#AllowSupplementaryGroups no');
l.Add('ExitOnOOM yes');
l.Add('Foreground no');
l.Add('#Debug yes');
l.Add('#LeaveTemporaryFiles yes');
l.Add('#DetectPUA yes');
l.Add('AlgorithmicDetection yes');
l.Add('ScanPE yes');
l.Add('ScanELF yes');
l.Add('DetectBrOKenExecutables yes');
l.Add('ScanOLE2 yes');
l.Add('#ScanPDF yes');
l.Add('ScanMail yes');
l.Add('#MailFollowURLs no');
l.Add('PhishingSignatures yes');
l.Add('PhishingScanURLs yes');
l.Add('#PhishingAlwaysBlockSSLMismatch no');
l.Add('#PhishingAlwaysBlockCloak no');
l.Add('ScanHTML yes');
l.Add('ScanArchive yes');
l.Add('ArchiveLimitMemoryUsage yes');
l.Add('#ArchiveBlockEncrypted no');
l.Add('MaxScanSize 50M');
l.Add('MaxFileSize 5M');
l.Add('MaxRecursion 10');
l.Add('MaxFiles 15000');
l.Add('#ClamukoScanOnAccess yes');
l.Add('#ClamukoScanOnOpen yes');
l.Add('#ClamukoScanOnClose yes');
l.Add('#ClamukoScanOnExec yes');
l.Add('#ClamukoIncludePath /home');
l.Add('#ClamukoIncludePath /students');
l.Add('#ClamukoExcludePath /home/bofh');
l.Add('# Default: 5M');
l.Add('#ClamukoMaxFileSize 10M');
CLlogs.INSTALL_MODULES('APP_ARTICA','clamav........................: saving /opt/artica/etc/clamd.conf');
l.SaveToFile('/opt/artica/etc/clamd.conf');
l.Clear;
l.Add('UpdateLogFile /opt/artica/logs/clamav/update.log');
l.Add('LogFileMaxSize 2M');
l.Add('LogTime yes');
l.Add('LogSyslog yes');
l.Add('LogFacility LOG_MAIL');
l.Add('PidFile /var/run/clamav/artica-freshclam.pid');
l.Add('DNSDatabaseInfo current.cvd.clamav.net');
l.Add('# code. See http://www.iana.org/cctld/cctld-whois.htm for the full list.');
l.Add('#DatabaseMirror db.XY.clamav.net');
l.Add('DatabaseMirror database.clamav.net');
l.Add('MaxAttempts 5');
l.Add('ScriptedUpdates yes');
l.Add('#CompressLocalDatabase no');
l.Add('Checks 12');
l.Add('#HTTPProxyServer myproxy.com');
l.Add('#HTTPProxyPort 1234');
l.Add('#HTTPProxyUsername myusername');
l.Add('#HTTPProxyPassword mypass');
l.Add('#HTTPUserAgent SomeUserAgentIdString');
l.Add('#LocalIPAddress aaa.bbb.ccc.ddd');
l.Add('NotifyClamd /opt/artica/etc/clamd.conf');
l.Add('#OnUpdateExecute command');
l.Add('#OnErrorExecute command');
l.Add('#OnOutdatedExecute command');
l.Add('Foreground yes');
l.Add('#Debug yes');
l.Add('ConnectTimeout 30');
l.Add('ReceiveTimeout 30');
CLlogs.INSTALL_MODULES('APP_ARTICA','clamav........................: saving /opt/artica/etc/freshclam.conf');
l.SaveToFile('/opt/artica/etc/freshclam.conf');

CLlogs.INSTALL_MODULES('APP_ARTICA','clamav........................: done');
l.free;
CLlogs.free;
clamav.CLAMD_START();
sleep(500);
clamav.CLAMD_STOP();

end;




procedure Tclass_install.RRD_INSTALL();
var
   source:string;
   LOGS:Tlogs;
   GLOBAL_INI:myconf;
begin
  LOGS:=Tlogs.Create;
  GLOBAL_INI:=myconf.Create();
  if ParamStr(2)<>'--force' then begin
     if FileExists(GLOBAL_INI.RRDTOOL_BIN_PATH()) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','rrd tools.....................:OK');
        exit;
     end;
  end;
  
  LIB_ART_LGPL();
  LIB_PNG();
  LIFREETYPE_INSTALL();
  
  
  if not FileExists('/opt/artica/lib/libart_lgpl_2.so') then begin
    LOGS.INSTALL_MODULES('APP_ARTICA','rrd tools.....................:failed stat /opt/artica/lib/libart_lgpl_2.so');
    exit;
  end;
  
   if not FileExists('/opt/artica/lib/libpng12.a') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','rrd tools.....................:failed stat /opt/artica/lib/libpng12.a');
      exit;
   end;
  
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           RRD STAT TOOL                        xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
     LOGS:=TLogs.Create;
     source:=COMPILE_GENERIC_APPS('APP_ARTICA','rrd','rrd');
     if DirectoryExists(source) then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','compiling source stored in "' + source + '"');


      fpsystem('cd ' + source + '&& ./configure --prefix=/opt/artica '+ LD_LIBRARY_PATH + ' ' + CPPFLAGS);
      fpsystem('cd ' + source + '&& make');
      fpsystem('cd ' + source + '&& make install');
      
      if FileExists(source + '/bindings/perl-shared') then begin
              fpsystem('cd ' + source + '/bindings/perl-shared && make clean && /opt/artica/bin/perl Makefile.PL PREFIX=/opt/artica RPATH=/opt/artica/lib && make && make install');
      end else begin
            LOGS.INSTALL_MODULES('APP_ARTICA','WARINING !!!!! unable to stat ' +source + '/bindings/perl-shared');
      end;
      
      
      LOGS.INSTALL_MODULES('APP_ARTICA','DONE...');
     end else begin
          LOGS.INSTALL_MODULES('APP_ARTICA','rrd tools.....................:failed');
     end;



end;
//##############################################################################
procedure Tclass_install.BALANCE_INSTALL();
var
   source:string;
   LOGS:Tlogs;
   cmd:string;
   folder:string;

begin
  LOGS:=Tlogs.Create;
  
  if ParamStr(2)<>'--force' then begin
  if FileExists('/opt/artica/bin/crossroads') then begin
     LOGS.INSTALL_MODULES('APP_ARTICA','crossroads....................:OK');
     exit;
  end;
  end;
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                  crossroads, load balancing                    xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();


LOGS:=TLogs.Create;
     source:=COMPILE_GENERIC_APPS('APP_ARTICA','crossroads','crossroads');
     if DirectoryExists(source) then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','compiling source stored in "' + source + '"');
                  folder:=source + '/etc/Makefile.def';
      CHANGE_MAKE_DEF(folder,'DEFAULT_CONF','/opt/artica/crossroads.conf');
      CHANGE_MAKE_DEF(folder,'PREFIX','/opt/artica');
      cmd:='cd ' + source + ' && make && make install';
       LOGS.INSTALL_MODULES('APP_ARTICA',cmd);
       fpsystem(cmd);

     end else begin
            LOGS.INSTALL_MODULES('APP_ARTICA','crossroads....................:failed');
     end;
     
 if FileExists('/opt/artica/bin/crossroads') then begin
     LOGS.INSTALL_MODULES('APP_ARTICA','crossroads....................:OK');
     exit;
  end;

end;
//##############################################################################
procedure Tclass_install.CHANGE_MAKE_DEF(path:string;key:string;value:string);
var
RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   LOGSM:tLogs;
begin
   LOGSM:=Tlogs.Create;
   if not FileExists(path) then begin
      LOGSM.INSTALL_MODULES('APP_ARTICA','CHANGE_MAKE_DEF unable to stat ' +path);
      exit;
   end;
  list:=TStringList.Create;
  list.LoadFromFile(path);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^'+key+'[\s=]+(.*)';
  for i:=0 to list.Count-1 do begin
       if RegExpr.Exec(list.Strings[i]) then begin
           list.Strings[i]:=key + ' = ' + value;
           LOGSM.INSTALL_MODULES('APP_ARTICA','crossroads: change ' + key + ' value to ' + value);
           list.SaveToFile(path);
           list.Free;
           LOGSM.Free;
           exit;
       end;
  end;
   

end;



//##############################################################################

procedure Tclass_install.INSTALL_FROM_CD_KAV();
var
   GLOBAL_INI:myconf;
   preinst:string;
   postinst:string;
begin

    GLOBAL_INI:=myconf.Create();
    if GLOBAL_INI.COMMANDLINE_PARAMETERS('--test') then begin
              writeln('OK');
              exit;
    end;
    
    preinst:=global_INI.get_ARTICA_PHP_PATH() + '/bin/install/kavgroup/preinst';
    postinst:=global_INI.get_ARTICA_PHP_PATH() + '/bin/install/kavgroup/postinst';
    INIT_ARTICA();
    POSTFIX_INIT();
    fpsystem('/bin/chmod -R 777 '+ global_INI.get_ARTICA_PHP_PATH() + '/bin/install/kavgroup/*');
    fpsystem(preinst);
    fpsystem(postinst);
    KAV_CONFIGURE();
    fpsystem('/bin/rm -rf /home/artica');
    fpsystem('update-rc.d -f artica-cd remove');
    fpsystem('reboot');
end;



procedure Tclass_install.LIB_ART_LGPL();
var
   source:string;
   LOGS:Tlogs;

begin
  LOGS:=Tlogs.Create;
  if FileExists('/opt/artica/lib/libart_lgpl_2.so') then begin
     LOGS.INSTALL_MODULES('APP_ARTICA','libart_lgpl_2.................:OK');
     exit;
  end;
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           LIB ART GPL                          xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  
  
LOGS:=TLogs.Create;
     source:=COMPILE_GENERIC_APPS('APP_ARTICA','libart_lgpl','libart_lgpl');
     if DirectoryExists(source) then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','compiling source stored in "' + source + '"');


      fpsystem('cd ' + source + '&& ./configure --prefix=/opt/artica');
      fpsystem('cd ' + source + '&& make');
      fpsystem('cd ' + source + '&& make install');
      LOGS.INSTALL_MODULES('APP_ARTICA','DONE...');
     end else begin
          LOGS.INSTALL_MODULES('APP_ARTICA','libart_lgpl_2.................:failed');
     end;
  
end;
//##############################################################################
procedure Tclass_install.LIBZ_INSTALL();
var
   source:string;
   compile_string:string;
   LOGS:Tlogs;

begin
  LOGS:=Tlogs.Create;
  if FileExists('/opt/artica/lib/libz.a') then begin
     LOGS.INSTALL_MODULES('APP_ARTICA','libz..........................:OK');
     exit;
  end;

     source:=COMPILE_GENERIC_APPS('APP_ARTICA','zlib','zlib');
     if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','libz Error extracting');
        exit;
     end;
     compile_string:='&& ./configure --libdir=/opt/artica/lib --includedir=/opt/artica/include ';
     compile_string:=compile_string + ' --prefix=/opt/artica --shared && make && make install';
     LOGS.INSTALL_MODULES('APP_ARTICA',compile_string);
     fpsystem('cd ' + source + compile_string);

 if not FileExists('/opt/artica/lib/libz.a') then begin
       LOGS.INSTALL_MODULES('APP_ARTICA','libz..........................: failed');
       exit;
   end;

   LOGS.INSTALL_MODULES('APP_ARTICA','libz..........................:OK');
  
end;
//##############################################################################

procedure Tclass_install.LIB_PNG();
var
   source:string;
   LOGS2:Tlogs;
   compile_string:string;
begin
  LOGS2:=Tlogs.Create;
  if FileExists('/opt/artica/lib/libpng12.a') then begin
     LOGS2.INSTALL_MODULES('APP_ARTICA','libpng........................:OK');
     exit;
  end;
  
  if not FileExists('/opt/artica/lib/libz.a') then LIBZ_INSTALL();
  
   if not FileExists('/opt/artica/lib/libz.a') then begin
       LOGS2.INSTALL_MODULES('APP_ARTICA','libpng........................:unable to stat /opt/artica/lib/libz.a');
   end;
  
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           LIB PNG                              xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();



     source:=COMPILE_GENERIC_APPS('APP_ARTICA','libpng','libpng');
     if DirectoryExists(source) then begin
      LOGS2.INSTALL_MODULES('APP_ARTICA','compiling source stored in "' + source + '"');
      compile_string:='cd ' + source + '&& ./configure --prefix=/opt/artica ' + LD_LIBRARY_PATH + ' ' + CPPFLAGS;
      LOGS2.INSTALL_MODULES('APP_ARTICA', compile_string);
      fpsystem(compile_string);
      fpsystem('cd ' + source + '&& make');
      fpsystem('cd ' + source + '&& make install');
      LOGS2.INSTALL_MODULES('APP_ARTICA','DONE...');
     end else begin
               LOGS2.INSTALL_MODULES('APP_ARTICA','libpng........................:failed');
     end;

end;
//##############################################################################
procedure Tclass_install.INSTALL_AWSTATS();

var source:string;
    GLOBAL_INI        :myconf;
    LOGS3             :TLogs;

begin
GLOBAL_INI:=myconf.Create;
LOGS3:=TLogs.Create;

   if ParamStr(2)<>'--force' then begin
    if FileExists('/opt/artica/awstats/wwwroot/cgi-bin/awstats.pl') then begin
      LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:OK');
      exit;
    end;
   end;

    

  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           awstats                              xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();


    if not directoryExists('/opt/artica/awstats') then forcedirectories('/opt/artica/awstats');
    if not directoryExists('/opt/etc/awstats') then forcedirectories('/opt/etc/awstats');
    
    source:=COMPILE_GENERIC_APPS('APP_ARTICA','awstats','awstats');
     if not directoryExists(source) then begin
        LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Failed extracting source');
        exit;
     end;

     fpsystem('/bin/cp -rfv ' + source + '/* /opt/artica/awstats');
     if not FileExists('/opt/artica/awstats/wwwroot/cgi-bin/awstats.pl') then begin
         LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Failed installing sources');
         exit;
     end;


    //hostname:=GLOBAL_INI.LINUX_GET_HOSTNAME;
    //converttool:=GLOBAL_INI.AWSTATS_MAILLOG_CONVERT_PATH_SOURCE();

    awstats.START_SERVICE();


    
    




   LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Install cron schedule..');
   LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Install GeoIP databases...');
   if not directoryExists('/usr/local/share/GeoIP') then ForceDirectories('/usr/local/share/GeoIP');
   GEOIP_UPDATES();
   GEOIP_LIB_INSTALL();
   GEOIP_PERL_INSTALL();
   AWSTAT_RECONFIGURE();
     


   LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Adding plugins into configuration file...');
   awstats.AWSTATS_SET_PLUGIN('geoip GEOIP_STANDARD /usr/local/share/GeoIP/GeoIP.dat',awstats.AWSTATS_ETC_PATH() +'/awstats.mail.conf');
   awstats.AWSTATS_SET_PLUGIN('geoip_city_maxmind GEOIP_STANDARD /usr/local/share/GeoIP/GeoLiteCity.dat',awstats.AWSTATS_ETC_PATH() +'/awstats.mail.conf');
   awstats.AWSTATS_SET_PLUGIN('geoip_org_maxmind GEOIP_STANDARD /usr/local/share/GeoIP/GeoIPASNum.dat',awstats.AWSTATS_ETC_PATH() +'/awstats.mail.conf');


   LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Update the logs database...');
   awstats.AWSTATS_GENERATE();
   GLOBAL_INI.Free;
end;
//##############################################################################
procedure Tclass_install.AWSTAT_RECONFIGURE();
var artica_path:string;
    GLOBAL_INI        :myconf;
begin
   GLOBAL_INI:=myconf.Create;
   artica_path:=global_ini.get_ARTICA_PHP_PATH();
   awstats.START_SERVICE();


       if FileExists(awstats.AWSTATS_MAILLOG_CONVERT_PATH_SOURCE()) then begin
          fpsystem('/bin/chmod 777 ' + awstats.AWSTATS_MAILLOG_CONVERT_PATH_SOURCE());
       end;


    if not FileExists('/usr/bin/mlc') then begin
       fpsystem('/bin/cp ' + artica_path + '/bin/install/awstats/mlc' + ' /usr/bin/mlc');
       fpsystem('/bin/chmod 777 /usr/bin/mlc');
    end;
end;

//##############################################################################
PROCEDURE Tclass_install.GEOIP_PERL_INSTALL();

var source            :string;
    LOGS3             :TLogs;
    cmd               :string;

begin
LOGS3:=TLogs.Create;

if ParamStr(2)<>'--force' then begin
 if FileExists('/opt/artica/lib/perl/5.8.8/auto/Geo/IP/IP.so') then begin
     LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................: GeoIP perl program OK');
     exit;
  end;
end;

LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Install GeoIP perl program');
  source:=COMPILE_GENERIC_APPS('APP_AWSTATS','Geo-IP','Geo-IP');
  if DirectoryExists(source) then begin
  cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL  LIBS="-L/opt/artica/lib" INC="-I/opt/artica/include" && make && make install';
  LOGS3.INSTALL_MODULES('APP_ARTICA',cmd);
  fpsystem(cmd);
  end;
  
  
  if FileExists('/opt/artica/lib/perl/5.8.8/auto/Geo/IP/IP.so') then begin
     LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................: GeoIP perl program OK');
  end else begin
      LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................: GeoIP perl program Failed');
  end;
     
end;
//##############################################################################
PROCEDURE Tclass_install.GEOIP_LIB_INSTALL();
var source            :string;
    LOGS3             :TLogs;
    cmd               :string;

begin
LOGS3:=TLogs.Create;

if ParamStr(2)<>'--force' then begin
 if FileExists('/opt/artica/lib/libGeoIP.so') then begin
     LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................: GeoIP library OK');
     exit;
  end;
end;

   LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Install GeoIP library');
   source:=COMPILE_GENERIC_APPS('APP_AWSTATS','GeoIP','GeoIP');
   if DirectoryExists(source) then begin
      LOGS3.INSTALL_MODULES('APP_ARTICA','compiling source stored in "' + source + '"');
      LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................:Compile');
      cmd:='cd ' + source + '&& ./configure --prefix=/opt/artica ' + LD_LIBRARY_PATH + ' ' + CPPFLAGS;
      cmd:= cmd + '&& make && make install';
      LOGS3.INSTALL_MODULES('APP_ARTICA',cmd);
      fpsystem(cmd);
   end;
   
   
if FileExists('/opt/artica/lib/libGeoIP.so') then begin
     LOGS3.INSTALL_MODULES('APP_ARTICA','awstats.......................: GeoIP library OK');
     exit;
  end;
   
end;
//##############################################################################


FUNCTION Tclass_install.INSTALL_FILTER_DATABASE():boolean;
var
GLOBAL_INI:MyConf;
logs:Tlogs;
mysql_root,artica_path,mysql_password,mysql_init_path:string;
begin

GLOBAL_INI:=MyConf.CReate;
mysql_init_path:=GLOBAL_INI.MYSQL_INIT_PATH();
logs:=Tlogs.Create;

fpsystem(mysql_init_path + ' restart');

artica_path:=GLOBAL_INI.get_INSTALL_PATH();

if length(mysql_init_path)=0 then begin
   ShowScreen('INSTALL_FILTER_DATABASE:: unable to detect mysql installation');
   exit(false);
end;

if GLOBAL_INI.MYSQL_ACTION_TESTS_ADMIN()=false then begin
    showscreen('INSTALL_FILTER_DATABASE:: unable to detect the right username/account for Mysql Database');
    writeln('');
    writeln('');
    writeln('GIVE THE USERNAME ADMIN OF MYSQL: ');
    readln(mysql_root);
    writeln('');
    writeln('');
    writeln('GIVE THE PASSWORD ADMIN OF MYSQL: ');
    readln(mysql_password);
    if GLOBAL_INI.MYSQL_ACTION_TESTS_ADMIN()=true then begin
         GLOBAL_INI.ARTICA_MYSQL_SET_INFOS('database_admin',mysql_root);
         GLOBAL_INI.ARTICA_MYSQL_SET_INFOS('database_password',mysql_password);
    end else begin
       ShowScreen('INSTALL_FILTER_DATABASE:: unable to detect the right username/account for Mysql Database');
       INSTALL_FILTER_DATABASE();
    end;
end;


if logs.IF_DATABASE_EXISTS('artica_filter')=false then begin
   ShowScreen('INSTALL_FILTER_DATABASE::CREATING ARTICA-FILTER DATABASE....');
   logs.QUERY_SQL(pChar('CREATE DATABASE `artica_filter` ;'),'');

end;
 GLOBAL_INI.MYSQL_ACTION_IMPORT_DATABASE(artica_path + '/bin/install/artica-filter-mysql.sql','artica_filter');
 result:=logs.IF_DATABASE_EXISTS('artica_filter');
 GLOBAL_INI.Free;
exit(result);

end;


//##############################################################################
procedure Tclass_install.InstallArtica();
var
   directory,cmd,ParentFolder,www:string;
   CONF:MyConf;
   LOG:Tlogs;
begin
 CONF:=myconf.Create;
 LOG:=Tlogs.Create;
 www:='';

 
 log.INSTALL_MODULES('APP_ARTICA','Installing artica.............:Install artica-postfix on /usr/share/artica-postfix');
 
 directory:='/usr/share/artica-postfix';
 forcedirectories('/usr/share/artica-postfix');
 CONF.set_ARTICA_PHP_PATH('/usr/share/artica-postfix');
 ParentFolder:=CONF.get_INSTALL_PATH();
 
 
     while not FileExists(ParentFolder + '/artica.loc') do begin
           ShowScreen('Give the path to artica source installation files: [' + ParentFolder + ']');
           readln(ParentFolder);
     end;
 
 
 if ExtractFilePath(ParamStr(0))<>'/usr/share/artica-postfix/' then begin
    cmd:='/bin/cp -rf ' + ParentFolder + '/* ' + directory + ' >/dev/null 2>&1';
    log.INSTALL_MODULES('APP_ARTICA','Installing artica.............:' + cmd);
    fpsystem(cmd);
 end;


 log.INSTALL_MODULES('APP_ARTICA','Installing artica.............: delete some files....');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/ressources/settings.inc');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/01cpu*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/02loadavg*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/03mem*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/04hddio*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/05hdd*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/06proc*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/10net*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/img/mailgraph_*.png');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/ressources/databases/postfix-queue-cache.conf');
 fpsystem('/bin/rm -rf /usr/share/artica-postfix/ressources/databases/queue.list.*.cache');
 

 if ParamStr(1)='-upgrade' then begin
       ShowScreen('Stopping ldap');
       fpsystem(ldap.INITD_PATH() + ' stop');
       ShowScreen('Upgrading schema');
       fpsystem(ldap.INITD_PATH()+ ' start');
 end;
 
 LOG.INSTALL_MODULES('APP_ARTICA','artica php sources files......:OK ('+www + '/artica-postfix)');
 
end;

 //##############################################################################
function Tclass_install.GetParentFolder(folder:string):string;
var

newfolder:string;
arr:TStringList;
i:integer;
begin
newfolder:='';
arr:=TStringList.create;
arr.Delimiter := '/';
arr.DelimitedText:=folder;

 for i:=arr.Count-2 downto  0 do begin
       newfolder:=arr.Strings[i] + '/' + newfolder;
 end;
 
     if newfolder[length(newfolder)]='/' then newfolder:=Copy(newfolder,0,length(newfolder)-1);
 
  exit(newfolder);

end;


 //##############################################################################
PROCEDURE Tclass_install.RestartAllservices();
var
ldap_init,postfix_init:string;
GLOBAL_INI:myconf;
begin
 GLOBAL_INI:=myconf.Create();
 ldap_init:=ldap.INITD_PATH();
 postfix_init:='/etc/init.d/postfix';
 ShowScreen('RESTART ALL REQUIRED SERVICES');
 fpsystem(ldap_init + ' restart');
 fpsystem(postfix_init + ' restart');
 GLOBAL_INI.Free;
end;
 
 
PROCEDURE Tclass_install.InstallArticaDaemon();
var   webpath:string;
begin
     ShowScreen('Starting installing artica daemon...');
     webpath:=GLOBALINI.get_ARTICA_PHP_PATH();

     while not FileExists(webpath + '/img/content.css') do begin
           ShowScreen('Give the path to artica web root:[' + webpath + '] doesn''t seems to be right path :');
           readln(webpath);
     end;

     ShowScreen('Writing initial config directory done..');
     GLOBALINI.set_ARTICA_PHP_PATH(webpath);

;

     install_init_d();
     ShowScreen('Install temp folders...');
     forcedirectories(webpath + '/ressources/logs');
     forcedirectories(webpath + '/ressources/conf');
     Execute('/bin/chmod 0777 ' + webpath + '/ressources/logs');
     Execute('/bin/chmod 0777 ' + webpath + '/ressources/conf');
     ShowScreen('Installing artica-postfix done...');

end;
procedure Tclass_install.ExecuteNoWait(cmd_local:string);
    var
  AProcess: TProcess;
begin
    AProcess := TProcess.Create(nil);
    AProcess.CommandLine := cmd_local;
   // AProcess.Options := AProcess.Options + [poWaitOnExit];
    AProcess.Execute;
    AProcess.Free;
end;



procedure Tclass_install.Execute(cmd_local:string);
    var
  AProcess: TProcess;
begin
    AProcess := TProcess.Create(nil);
    AProcess.CommandLine := cmd_local;
    AProcess.Options := AProcess.Options + [poWaitOnExit];
    AProcess.Execute;
    AProcess.Free;
end;

//##############################################################################
procedure Tclass_install.install_init_d();
var
   myFile : TStringList;
   GLOBALINI:myconf;
   D:boolean;
   LOG:Tlogs;
begin

 GLOBALINI:=myconf.create;
 LOG:=Tlogs.create;
 D:=GLOBALINI.COMMANDLINE_PARAMETERS('debug');
 if D then ShowScreen('Creating startup scripts');
 if not fileExists('/usr/share/artica-postfix') then begin
    ShowScreen('Unable to stat /usr/share/artica-postfix/bin/artica-install');
    exit;
 end;

myFile:=TstringList.Create;
myFile.Add('#!/bin/sh');
myFile.add('### BEGIN INIT INFO');
myFile.Add('# Provides:          Artica Main service');
myFile.Add('# Required-Start:    $local_fs $remote_fs $syslog $named $network $time');
myFile.Add('# Required-Stop:     $local_fs $remote_fs $syslog $named $network');
myFile.Add('# Should-Start:');
myFile.Add('# Should-Stop:');
myFile.Add('# Default-Start:     2 3 4 5');
myFile.Add('# Default-Stop:      0 1 6');
myFile.Add('# Short-Description: Start Artica main daemon');
myFile.Add('# chkconfig: 2345 11 89');
myFile.Add('# description: artica-postfix Daemon');
myFile.add('### END INIT INFO');
myFile.Add('case "$1" in');
myFile.Add(' start)');
myFile.Add('    /usr/share/artica-postfix/bin/artica-install -watchdog $2 $3');
myFile.Add('    ;;');
myFile.Add('');
myFile.Add('  stop)');
myFile.Add('    /usr/share/artica-postfix/bin/artica-install -shutdown $2 $3');
myFile.Add('    ;;');
myFile.Add('');
myFile.Add(' restart)');
myFile.Add('     /usr/share/artica-postfix/bin/artica-install -shutdown $2 $3');
myFile.Add('     sleep 3');
myFile.Add('     /usr/share/artica-postfix/bin/artica-install -watchdog $2 $3');
myFile.Add('    ;;');
myFile.Add('');
myFile.Add('  *)');
myFile.Add('    echo "Usage: $0 {start|stop|restart} {ldap|} (+ ''debug'' for more infos)"');
myFile.Add('    exit 1');
myFile.Add('    ;;');
myFile.Add('esac');
myFile.Add('exit 0');
ForceDirectories('/etc/init.d');

if DirectoryExists('/usr/local/etc/rc.d') then begin
   myfile.SaveToFile('/usr/local/etc/rc.d/artica-postfix');
   fpsystem('/bin/ln -s /usr/local/etc/rc.d/artica-postfix /etc/init.d/artica-postfix');
end else begin
   myfile.SaveToFile('/etc/init.d/artica-postfix');
end;


LOG.INSTALL_MODULES('APP_ARTICA','install init.d scripts........:Adding startup scripts to the system');
 fpsystem('/bin/chmod +x /etc/init.d/artica-postfix >/dev/null 2>&1');
 
 if FileExists('/usr/sbin/update-rc.d') then begin
    fpsystem('/usr/sbin/update-rc.d -f artica-postfix defaults >/dev/null 2>&1');
 end;

  if FileExists('/sbin/chkconfig') then begin
     fpsystem('/sbin/chkconfig --add artica-postfix >/dev/null 2>&1');
     fpsystem('/sbin/chkconfig --level 2345 artica-postfix on >/dev/null 2>&1');
  end;
  
   LOG.INSTALL_MODULES('APP_ARTICA','install init.d scripts........:OK (/etc/init.d/artica-postfix {start,stop,restart})');
  
 myFile.free;
   SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/setup-ubuntu --check-base-system');

end;
//##############################################################################
procedure Tclass_install.MAILGRAPH_REMOVE();

begin
   ShowScreen('');
   ShowScreen('');
   ShowScreen('-------------------------------------------');
   ShowScreen('-------REMOVE MAILGRAPH APPLICATION--------');
   ShowScreen('-------------------------------------------');
   
   if fileExists('/etc/init.d/mailgraph-init') then begin
     ShowScreen('Stopping mailgraph system');
     fpsystem('/etc/init.d/mailgraph-init stop');
   end;
   
   ShowScreen('removing service');
   if FileExists('/usr/sbin/update-rc.d') then fpsystem('/usr/sbin/update-rc.d -f mailgraph-init remove');
   if FileExists('/sbin/chkconfig') then  fpsystem('/sbin/chkconfig --del mailgraph-init');
   fpsystem('/bin/rm /etc/init.d/mailgraph-init');
   if FileExists('/etc/cron.d/artica_mailgraph') then begin
      fpsystem('/bin/rm /etc/cron.d/artica_mailgraph');
      fpsystem('/etc/init.d/cron restart');
   end;
   ShowScreen('removing mailgraph done...');

end;
//##############################################################################
procedure Tclass_install.YOREL_REMOVE();

begin
   ShowScreen('');
   ShowScreen('');
   ShowScreen('-------------------------------------------');
   ShowScreen('-------REMOVE YOREL APPLICATION--------');
   ShowScreen('-------------------------------------------');

   if FileExists('/etc/cron.d/artica_yorel') then begin
      fpsystem('/bin/rm /etc/cron.d/artica_yorel');
      fpsystem('/etc/init.d/cron restart');
   end;
   ShowScreen('removing yorel done...');

end;
//##############################################################################
procedure Tclass_install.QUEUEGRAPH_INSTALL();
var myPath:string;
list:TStringList;
begin
   ShowScreen('');
   ShowScreen('');
   ShowScreen('-------------------------------------------');
   ShowScreen('-------INSTALL QUEUEGRAPH APPLICATION------');
   ShowScreen('-------------------------------------------');

   if not FileExists('/usr/bin/rrdtool') then begin
         ShowScreen('WARNING !!! unable to locate rrdtool : usually in /usr/bin/rrdtool');
         exit;
   end;
   
   myPath:=GLOBALINI.get_ARTICA_PHP_PATH() + '/bin/queuegraph/queuegraph-rrd.sh';
   if not FileExists(mypath) then begin
      ShowScreen('QUEUEGRAPH_INSTALL::Error::Unable to locate ' +myPath);
      exit();
   end;
   
 list:=TStringList.Create;
 list.Add('* * * * *    root    ' + myPath);
 list.SaveToFile('/etc/cron.d/artica_queuegraph');
 fpsystem('/etc/init.d/cron restart');
end;

//##############################################################################
procedure Tclass_install.QUEUEGRAPH_REMOVE();
begin

   ShowScreen('');
   ShowScreen('');
   ShowScreen('-------------------------------------------');
   ShowScreen('-------INSTALL QUEUEGRAPH APPLICATION------');
   ShowScreen('-------------------------------------------');
   if FileExists('/etc/cron.d/artica_queuegraph') then begin
      fpsystem('/bin/rm /etc/cron.d/artica.cron.queuegraph');
      fpsystem('/etc/init.d/cron restart');
   end;
   ShowScreen('Done...')
   
end;
//##############################################################################
function Tclass_install.LinuxInfosDistri():string;
var
   ini:myconf;
begin
   ini:=myconf.Create();
   exit(ini.LINUX_DISTRIBUTION());
end;
//##############################################################################
function Tclass_install.EnableDaemonsAutoStart():boolean;
var cyrus_service,ldap_service:string;
 GLOBALINI:myconf;
 cyr:Tcyrus;
 D:boolean;
begin
 cyr:=Tcyrus.Create(SYS);
 result:=true;
 GLOBALINI:=myconf.create;
 D:=GLOBALINI.COMMANDLINE_PARAMETERS('debug');
 
if FileExists('/sbin/chkconfig') then begin
   fpsystem('/sbin/chkconfig --add mailgraph-init');
   fpsystem('/sbin/chkconfig --level 2345 mailgraph-init on');
   cyrus_service:=ExtractFileName(cyr.CYRUS_GET_INITD_PATH());
   ldap_service:=ExtractFileName(ldap.INITD_PATH());
   chkconfig(cyrus_service,'on');
   chkconfig(ldap_service,'on');

   
end else begin
     if D then ShowScreen('EnableDaemonsAutoStart:: unable to stat /sbin/chkconfig');
end;


end;

//##############################################################################
function Tclass_install.chkconfig(service_name:string;onOrOff:string):boolean;
var GLOBALINI:myconf;
D:boolean;
begin
 result:=true;
 GLOBALINI:=myconf.create;
 D:=GLOBALINI.COMMANDLINE_PARAMETERS('debug');
 if D then ShowScreen('chkconfig:: enable (' + onOrOff + ') service name "' + service_name + '"');
if length(service_name)=0 then exit;
if FileExists('/sbin/chkconfig') then begin
    if D then ShowScreen('chkconfig:: /sbin/chkconfig --add '+ service_name);
   fpsystem('/sbin/chkconfig --add '+ service_name);
    if D then ShowScreen('chkconfig:: /sbin/chkconfig --level 2345 ' + service_name + ' ' + onOrOff);
   fpsystem('/sbin/chkconfig --level 2345 ' + service_name + ' ' + onOrOff);
end;

end;

//##############################################################################
function Tclass_install.LinuxDistrReal():string;
begin
if FileExists('/etc/mandrake-release') then exit('Mandrake');
if FileExists('/etc/redhat-release') then exit('Red Hat');
if FileExists('/etc/gentoo-release') then exit('Gentoo');
if FileExists('/etc/debian_version') then exit('Debian');
if FileExists('/etc/slackware-version') then exit('Slackware');
if FileExists('/etc/SuSE-release') then exit('SuSE');
if FileExists('/etc/aurox-release') then exit('Aurox');
if FileExists('/etc/annvix-release') then exit('Annvix');
if FileExists('/etc/arch-release') then exit('Arch Linux');
if FileExists('/etc/arklinux-release') then exit('Arklinux');
if FileExists('/etc/aurox-release') then exit('Aurox Linux');
if FileExists('/etc/blackcat-release') then exit('BlackCat');
if FileExists('/etc/cobalt-release') then exit('Cobalt');
if FileExists('/etc/conectiva-release') then exit('Conectiva');
if FileExists('/etc/debian_version') then exit('Debian');
if FileExists('/etc/debian_release') then exit('Debian');
if FileExists('/etc/fedora-release') then exit('Fedora Core');
if FileExists('/etc/gentoo-release') then exit('Gentoo Linux');
if FileExists('/etc/immunix-release') then exit('Immunix');
if FileExists('/etc/lfs-release') then exit('Linux-From-Scratch');
if FileExists('/etc/linuxppc-release') then exit('Linux-PPC');
if FileExists('/etc/mandrake-release') then exit('Mandrake');
if FileExists('/etc/mandriva-release') then exit('Mandriva/Mandrake Linux');
if fileExists('/etc/mandrake-release') then exit('Mandrake');
if FileExists('/etc/mandakelinux-release') then exit('Mandrake');
if FileExists('/etc/mklinux-release') then exit('MkLinux');
if FileExists('/etc/nld-release') then exit('Novell Linux Desktop');
if FileExists('/etc/pld-release') then exit('PLD Linux');
if FileExists('/etc/redhat-release') then exit('Red Hat');
if FileExists('/etc/redhat_version') then exit('Red Hat');
if FileExists('/etc/slackware-version') then exit('Slackware');
if FileExists('/etc/slackware-release') then exit('Slackware');
if FileExists('/etc/e-smith-release') then exit('SME Server (Formerly E-Smith)');
if FileExists('/etc/release') then exit('Solaris SPARC');
if FileExists('/etc/sun-release') then exit('Sun JDS');
if FileExists('/etc/SuSE-release') then exit('SUSE Linux');
if FileExists('/etc/novell-release') then exit('SUSE Linux');
if FileExists('/etc/sles-release') then exit('SUSE Linux ES9');
if FileExists('/etc/tinysofa-release') then exit('Tiny Sofa');
if FileExists('/etc/turbolinux-release') then exit('TurboLinux');
if FileExists('/etc/lsb-release') then exit('Ubuntu Linux');
if FileExists('/etc/ultrapenguin-release') then exit('UltraPenguin');
if FileExists('/etc/UnitedLinux-release') then exit('UnitedLinux');
if FileExists('/etc/va-release') then exit('VA-Linux/RH-VALE');
if FileExists('/etc/yellowdog-release') then exit('Yellow Dog');
end;
function Tclass_install.LinuxRPMDEB():string;
begin
if FileExists('/etc/mandrake-release') then exit('rpm');
if FileExists('/etc/redhat-release') then exit('rpm');
if FileExists('/etc/gentoo-release') then exit('Gentoo');
if FileExists('/etc/debian_version') then exit('apt');
if FileExists('/etc/slackware-version') then exit('Slackware');
if FileExists('/etc/SuSE-release') then exit('SuSE');
if FileExists('/etc/aurox-release') then exit('Aurox');
if FileExists('/etc/annvix-release') then exit('Annvix');
if FileExists('/etc/arch-release') then exit('Arch Linux');
if FileExists('/etc/arklinux-release') then exit('Arklinux');
if FileExists('/etc/aurox-release') then exit('Aurox Linux');
if FileExists('/etc/blackcat-release') then exit('BlackCat');
if FileExists('/etc/cobalt-release') then exit('Cobalt');
if FileExists('/etc/conectiva-release') then exit('Conectiva');
if FileExists('/etc/debian_version') then exit('apt');
if FileExists('/etc/debian_release') then exit('apt');
if FileExists('/etc/fedora-release') then exit('rpm');
if FileExists('/etc/gentoo-release') then exit('Gentoo Linux');
if FileExists('/etc/immunix-release') then exit('Immunix');
if FileExists('/etc/lfs-release') then exit('Linux-From-Scratch');
if FileExists('/etc/linuxppc-release') then exit('Linux-PPC');
if FileExists('/etc/mandrake-release') then exit('rpm');
if FileExists('/etc/mandriva-release') then exit('rpm');
if fileExists('/etc/mandrake-release') then exit('rpm');
if FileExists('/etc/mandakelinux-release') then exit('rpm');
if FileExists('/etc/mklinux-release') then exit('MkLinux');
if FileExists('/etc/nld-release') then exit('Novell Linux Desktop');
if FileExists('/etc/pld-release') then exit('PLD Linux');
if FileExists('/etc/redhat-release') then exit('rpm');
if FileExists('/etc/redhat_version') then exit('rpm');
if FileExists('/etc/slackware-version') then exit('Slackware');
if FileExists('/etc/slackware-release') then exit('Slackware');
if FileExists('/etc/e-smith-release') then exit('SME Server (Formerly E-Smith)');
if FileExists('/etc/release') then exit('Solaris SPARC');
if FileExists('/etc/sun-release') then exit('Sun JDS');
if FileExists('/etc/SuSE-release') then exit('SUSE Linux');
if FileExists('/etc/novell-release') then exit('SUSE Linux');
if FileExists('/etc/sles-release') then exit('SUSE Linux ES9');
if FileExists('/etc/tinysofa-release') then exit('Tiny Sofa');
if FileExists('/etc/turbolinux-release') then exit('TurboLinux');
if FileExists('/etc/lsb-release') then exit('deb');
if FileExists('/etc/ultrapenguin-release') then exit('UltraPenguin');
if FileExists('/etc/UnitedLinux-release') then exit('UnitedLinux');
if FileExists('/etc/va-release') then exit('VA-Linux/RH-VALE');
if FileExists('/etc/yellowdog-release') then exit('rpm');
end;
//##############################################################################

procedure Tclass_install.DetectDistribution();
var
   datafile,regex:string;
   RegExpr:TRegExpr;
   D:boolean;
begin
   D:=CommandLineIsExists('debug');
   if d then logs('Tclass_install.DetectDistribution() -> /etc/redhat-release ?');
   if FileExists('/etc/redhat-release') then begin
      datafile:=ReadFileIntoString('/etc/redhat-release');
      regex:='[Ff]edora [Cc]ore [Rr]elease\s+([0-9])';
      RegExpr:=TRegExpr.create;
      RegExpr.expression:=regex;
      if RegExpr.Exec(datafile) then begin
         if d then logs('Tclass_install.DetectDistribution() ->' + RegExpr.Match[0]);
         try
            GLOBALINI.set_LINUX_DISTRI('FEDORA_CORE');
         except
         end;
      end;
   end;
      
      
   if FileExists('/etc/debian_version') then begin
      if d then logs('Tclass_install.DetectDistribution() ->This is a debian distribution');
      try
         GLOBALINI.set_LINUX_DISTRI('DEBIAN');
      except
      end;
   end;

end;
//##############################################################################
procedure Tclass_install.FETCHMAIL_AS_DAEMON();
var Text,Text2:TstringList;

begin
ShowScreen('Creating configuration file...');
text:=TstringList.Create;
text.Add('export LC_ALL=C');
text.Add('START_DAEMON=yes');
text.SaveToFile('/etc/default/fetchmail');
Text2:=TstringList.Create;
text2.Add('set daemon 600');
text2.Add('set postmaster "root"');
text2.SaveToFile('/etc/fetchmailrc');

ShowScreen('Starting fetchmail as daemon mode...');
fpsystem('/etc/init.d/fetchmail start');


end;
//##############################################################################
procedure Tclass_install.libspf2_INSTALL();
var
   source:string;
   LOG:Tlogs;
   cmd:string;
begin
    LOG:=Tlogs.Create;
if ParamStr(2)<>'--force' then begin
    if FileExists('/opt/artica/lib/libspf2.a') then begin
     LOG.INSTALL_MODULES('APP_ARTICA','libspf2.......................: OK');
     exit;
  end;
end;


forcedirectories('/opt/artica/install/sources');
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                              libspf2                           xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  source:=COMPILE_GENERIC_APPS('APP_ARTICA','libspf2','libspf2');
  

 if length(source)=0 then begin
       LOG.INSTALL_MODULES('APP_ARTICA','libspf2.......................: FAILED while extract source tarball');
       readln();
       exit;
 end;
 
LOG.INSTALL_MODULES('APP_ARTICA','libspf2.......................: Starting installation in ' + source);
  cmd:='cd ' + source + ' && ./configure --prefix=/opt/artica ';
  cmd:=cmd + ' ' + CPPFLAGS + ' '+ LD_LIBRARY_PATH;
  cmd:=cmd + ' && make && make install';
  LOG.INSTALL_MODULES('APP_ARTICA',cmd);
  fpsystem(cmd);

  if FileExists('/opt/artica/lib/libspf2.a') then begin
     LOG.INSTALL_MODULES('APP_ARTICA','libspf2.......................: OK');
     cmd:='/bin/cp ' + source + '/src/include/spf_dns_internal.h /opt/artica/include/spf2/spf_dns_internal.h';
     LOG.INSTALL_MODULES('APP_ARTICA',cmd);
     fpsystem(cmd);
     
  end else begin
     LOG.INSTALL_MODULES('APP_ARTICA','libspf2.......................: Failed to install');
     readln();
  end;

end;



procedure Tclass_install.MAILMAN_CHECK_CONFIG;
var
   GLOBALINI:myconf;
   def_conf,hostname:string;
   l:TstringList;
   RegExpr:TRegExpr;
   LOG:Tlogs;
   i:integer;
   D:boolean;
   root:string;
begin
  def_conf:='/opt/artica/mailman/Mailman/Defaults.py';
  if not FileExists(def_conf) then exit;

  GLOBALINI:=myconf.Create;
  D:=GLOBALINI.COMMANDLINE_PARAMETERS('debug');
  LOG:=Tlogs.create;
  hostname:=GLOBALINI.SYSTEM_FQDN();
  
  l:=Tstringlist.Create;
  RegExpr:=TRegExpr.Create;

  l.LoadFromFile(def_conf);
  RegExpr.Expression:='^DEFAULT_EMAIL_HOST';
  for i:=0 to l.Count- 1 do begin

      if RegExpr.Exec(l.Strings[i]) then begin
         LOG.INSTALL_MODULES('APP_MAILMAN','Mailman.......................:DEFAULT_EMAIL_HOST OK');
         l.Strings[i]:='DEFAULT_EMAIL_HOST = '+ ''''+ hostname + '''';
         if D then writeln('DEFAULT_EMAIL_HOST ' + hostname + ' OK');
         break;
      end else begin
          //if D then writeln(l.Strings[i]+'->failed');
      end;
  end;

  RegExpr.Expression:='^DEFAULT_URL_HOST';
  for i:=0 to l.Count- 1 do begin

      if RegExpr.Exec(l.Strings[i]) then begin
         if D then writeln('DEFAULT_URL_HOST ' + hostname + ' OK');
         LOG.INSTALL_MODULES('APP_MAILMAN','Mailman.......................:DEFAULT_URL_HOST OK');
         l.Strings[i]:='DEFAULT_URL_HOST = '+ ''''+ hostname + '''';
         break;
      end;
  end;
  
RegExpr.Expression:='^MTA';
  for i:=0 to l.Count- 1 do begin

      if RegExpr.Exec(l.Strings[i]) then begin
         if D then writeln('MTA Postfix OK');
         LOG.INSTALL_MODULES('APP_MAILMAN','Mailman.......................:MTA OK');
         l.Strings[i]:='MTA = '+ ''''+ 'Postfix' + '''';
         break;
      end;
  end;
  

  
  fpsystem('/opt/artica/mailman/bin/check_perms -f >/dev/null 2>&1');
  fpsystem('/opt/artica/mailman/bin/newlist --quiet mailman root@localhost.localdomain 123 >/dev/null 2>&1');

   root:=ExtractFilePath(ParamStr(0));
   fpsystem(root + '/artica-mailman -css-patch');
   fpsystem(root + '/artica-mailman -replicate');
   fpsystem(root + '/artica-mailman -gen');
   
  

  l.SaveToFile(def_conf);
  l.free;
  RegExpr.free;
  LOG.free;

end;
//##############################################################################
function Tclass_install.NETCAT_INSTALL():boolean;
var
   source:string;
   LOG:Tlogs;
begin




    LOG:=Tlogs.Create;
    if fileExists('/bin/nc') then begin
      LOG.INSTALL_MODULES('APP_ARTICA','netcat........................:OK');
      LOG.Free;
      exit(true);
    end;

  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                              netcat                            xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

      source:=COMPILE_GENERIC_APPS('APP_ARTICA','netcat','netcat');
     if not DirectoryExists(source) then exit;

     LOG:=TLogs.Create;


     LOG.INSTALL_MODULES('APP_ARTICA','compiling source stored in "' + source + '"');
     fpsystem('cd ' + source + '&& make linux');
     if fileExists('/usr/bin/nc') then fpsystem('ln -s /usr/bin/nc bin/nc');
     
     
     if fileExists('/bin/nc') then begin
        LOG.INSTALL_MODULES('APP_ARTICA','netcat DONE...');
     end else begin
        LOG.INSTALL_MODULES('APP_ARTICA','netcat failed !!!!...');
     end;
     result:=true;
end;




//##############################################################################
function Tclass_install.COMPILE_GENERIC_APPS_LOCALE(tarball:string;package_name:string;DirectoryExtracted:string):string;
var
   LOG                                  :Tlogs;
   gcc_path,make_path,compile_source    :string;
   GLOBALINI                            :myconf;
   sys                                  :Tsystem;
   FILE_TEMP                            :TstringList;
   DECOMPRESS_OPT                       :string;
   APPS                                 :string;
   LocalPath                            :string;
   label myEnd;
begin
    LOG:=Tlogs.Create;
    GLOBALINI:=myconf.Create();
    sys:=Tsystem.Create();
    FILE_TEMP:=TStringList.Create;
    APPS:=package_name;
    DECOMPRESS_OPT:='xzf';
    gcc_path:=GLOBALINI.SYSTEM_GCC_PATH();
    make_path:=GLOBALINI.SYSTEM_MAKE_PATH();

    LOG.INSTALL_MODULES(APPS,'Starting installation of ' + package_name + ' application...');
    LOG.INSTALL_MODULES(APPS,'Checking required compilation tools as gcc and make');

    if length(make_path)=0 then begin
        LOG.INSTALL_MODULES(APPS,'ERROR:: unable to locate make...');
        goto MyEnd;
    end;

    if length(gcc_path)=0 then begin
        LOG.INSTALL_MODULES(APPS,'ERROR:: unable to locate gcc...');
        goto MyEnd;
    end;


    LocalPath:=ExtractFilePath(ParamStr(0)) + 'install/' + tarball;
    LOG.INSTALL_MODULES(APPS,'Get Local Path -->' +LocalPath);
    forcedirectories('/opt/artica/install/sources');
    if DirectoryExists('/opt/artica/install/sources/' + DirectoryExtracted) then begin
        LOG.INSTALL_MODULES(APPS,'Remove /opt/artica/install/sources/' + DirectoryExtracted );
       fpsystem('/bin -rm -rf /opt/artica/install/sources/' + DirectoryExtracted);
    end;
    LOG.INSTALL_MODULES(APPS,'Copy ' + LocalPath + ' to /opt/artica/install/sources/' + tarball);
    fpsystem('/bin/cp ' + LocalPath + ' /opt/artica/install/sources/' + tarball);

    forceDirectories('/opt/artica/install/sources/' + DirectoryExtracted);
    LOG.INSTALL_MODULES(APPS,'tar -' + DECOMPRESS_OPT + ' /opt/artica/install/sources/' + tarball + ' -C /opt/artica/install/sources/' + DirectoryExtracted);
    fpsystem('tar -' + DECOMPRESS_OPT + ' /opt/artica/install/sources/' + tarball + ' -C /opt/artica/install/sources/' + DirectoryExtracted);
    sys.DirDir('/opt/artica/install/sources/' + DirectoryExtracted);

    if sys.DirListFiles.Count=0 then begin
       LOG.INSTALL_MODULES(package_name,'.COMPILE_GENERIC_APPS::ERROR:: Bad repository format !!!');
       fpsystem('/bin/rm -rf /opt/artica/install/sources/'+DirectoryExtracted);
       goto myEnd;
    end;
    
    if sys.DirListFiles.Count>1 then begin
      result:='/opt/artica/install/sources/' + DirectoryExtracted;
      goto myEnd;
    end;
    
    compile_source:='/opt/artica/install/sources/' + DirectoryExtracted + '/' + sys.DirListFiles.Strings[0];
    LOG.INSTALL_MODULES(APPS,'SUCCESS: "' + compile_source + '"');
    result:=compile_source;

 goto myEnd;

myEnd:
    LOG.Free;
    GLOBALINI.Free;
    sys.Free;
    FILE_TEMP.free;

end;




//##############################################################################
function Tclass_install.COMPILE_CHECK_VERSION(APPS:string;package_name:string;local_version:string):boolean;
var
   LOG:Tlogs;
   GLOBAL_INI:myconf;
   ver,ver1:int64;
   auto:TIniFile;
   package_version:string;
begin
    result:=false;
    GLOBAL_INI:=myconf.Create();
    LOG:=Tlogs.Create;
    local_version:=AnsiReplaceText(local_version,'.','');
    local_version:=AnsiReplaceText(local_version,'-','');
    forcedirectories('/opt/artica/install/sources');
    LOG.INSTALL_MODULES(APPS,'Checking last supported version of ' + package_name);
    GLOBAL_INI.WGET_DOWNLOAD_FILE('http://www.artica.fr/auto.update.php','/opt/artica/install/sources/autoupdate.ini');
    if length(local_version)=0 then local_version:='0';

    auto:=TIniFile.Create('/opt/artica/install/sources/autoupdate.ini');
    package_version:=auto.ReadString('NEXT',package_name,'');
    auto.Free;
    if not FileExists('/opt/artica/install/sources/autoupdate.ini') then exit;
    if length(package_version)=0 then exit;
    
    
    package_version:=trim(AnsiReplaceText(package_version,'.',''));
    package_version:=trim(AnsiReplaceText(package_version,'-',''));
    
    LOG.INSTALL_MODULES(APPS,package_version + '>' + local_version + ' ?');

    try
      ver:=StrToInt64(local_version);
      ver1:=StrToInt64(package_version);
  except
         LOG.INSTALL_MODULES(APPS,'EXCEPTION ERROR ' + local_version  + ' ??? ' + package_version);
          exit;
    end;
    
    if ver1>ver then begin
       LOG.INSTALL_MODULES(APPS,package_name + ' must be updated');
       exit(true);
    end;
    
    

end;
//##############################################################################
function Tclass_install.COMPILE_GENERIC_APPS(APPS:string;package_name:string;package_name_suffix:string):string;
var
   LOG                                  :Tlogs;
   gcc_path,make_path,wget_path,compile_source:string;
   GLOBALINI,GLOBAL_INI                 :myconf;
   auto:TiniFile;
   tmp:string;
   sys:Tsystem;
   FILE_TEMP:TstringList;
   FILE_EXT:string;
   package_version:string;
   DECOMPRESS_OPT:string;
   www_prefix:string;
   uri_download:string;
   target_file:string;
   RegExpr:TRegExpr;
   int_version                          :integer;
   FileNamePrefix                       :string;
   local_folder                         :string;
   autoupdate_path                      :string;
   remote_uri                           :string;
   index_file                           :string;
   i                                    :integer;
   label                                 myEnd;



begin




    local_folder:='';
    remote_uri:='http://www.artica.fr/download';
    index_file:='http://www.artica.fr/auto.update.php';
    LOG:=Tlogs.Create;
    GLOBALINI:=myconf.Create();
    GLOBAL_INI:=myconf.Create();
    sys:=Tsystem.Create();
    FILE_TEMP:=TStringList.Create;
    RegExpr:=TRegExpr.Create;


 if ParamCount>0 then begin
     for i:=0 to ParamCount do begin
       RegExpr.Expression:='--remote-path=(.+)';
       if RegExpr.Exec(ParamStr(i)) then begin
           remote_uri:=RegExpr.Match[1];
       end;

       RegExpr.Expression:='--remote-index=(.+)';
       if RegExpr.Exec(ParamStr(i)) then begin
           index_file:=RegExpr.Match[1];
       end;

       RegExpr.Expression:='--folder=(.+)';
       if RegExpr.Exec(ParamStr(i)) then begin
          local_folder:=RegExpr.Match[1];
          LOG.INSTALL_MODULES(APPS,'Starting installation of ' + package_name + ' application using local folder ...'+local_folder);
       end;

    end;
 end;

    fpsystem('cd ' + ExtractFilePath(ParamStr(0)));

    gcc_path:=GLOBALINI.SYSTEM_GCC_PATH();
    make_path:=GLOBALINI.SYSTEM_MAKE_PATH();
    wget_path:='/usr/bin/wget';
    forcedirectories('/opt/artica/install/sources');
    if FileExists('/opt/artica/install/sources/' + package_name) then fpsystem('/bin/rm -rf /opt/artica/install/sources/' + package_name);


    LOG.INSTALL_MODULES(APPS,'Checking required compilation tools as gcc and make');
    if length(make_path)=0 then begin
        LOG.INSTALL_MODULES(APPS,'ERROR:: unable to locate make...');
        goto MyEnd;
    end;

    if length(gcc_path)=0 then begin
        LOG.INSTALL_MODULES(APPS,'ERROR:: unable to locate gcc...');
        goto MyEnd;
    end;

    if not FileExists(wget_path) then begin
        LOG.INSTALL_MODULES(APPS,'ERROR:: unable to stat ' + wget_path);
        goto MyEnd;
    end;


    LOG.INSTALL_MODULES(APPS,'Checking last supported version of ' + package_name + ' from ' + remote_uri+'/'+index_file);

    if local_folder='' then GLOBAL_INI.WGET_DOWNLOAD_FILE(index_file,'/opt/artica/install/sources/autoupdate.ini');
    if local_folder='' then begin
       autoupdate_path:='/opt/artica/install/sources/autoupdate.ini';
    end else begin
        autoupdate_path:=local_folder + '/autoupdate.ini';
        if not FileExists(autoupdate_path) then begin
             LOG.INSTALL_MODULES(APPS,'unable to stat ' + autoupdate_path);
             exit;
        end;
    end;
    auto:=TIniFile.Create(autoupdate_path);

    FILE_EXT:=auto.ReadString('NEXT',package_name + '_ext','tar.gz');
    www_prefix:=auto.ReadString('NEXT',package_name + '_prefix','');
    FileNamePrefix:=auto.ReadString('NEXT',package_name + '_filename_prefix',package_name  + '-');



    package_version:=auto.ReadString('NEXT',package_name,'');
    target_file:=FileNamePrefix + package_version + '.' + FILE_EXT;



    auto.Free;

    if local_folder='' then begin
       uri_download:=remote_uri + '/' + target_file;
       if length(www_prefix)>0 then uri_download:=remote_uri+'/' + www_prefix + '/' + target_file;
    end else begin
       uri_download:=local_folder + '/' + target_file;
       if length(www_prefix)>0 then uri_download:=local_folder + '/' + www_prefix + '/' + target_file;
    end;

    LOG.INSTALL_MODULES(APPS,'');
    LOG.INSTALL_MODULES(APPS,'');
    LOG.INSTALL_MODULES(APPS,'#################################################################################');
    LOG.INSTALL_MODULES(APPS,chr(9)+'version..............:"' +package_version+'"');
    LOG.INSTALL_MODULES(APPS,chr(9)+'extension............:"' +FILE_EXT+'"');
    LOG.INSTALL_MODULES(APPS,chr(9)+'prefix...............:"' +www_prefix+'"');
    LOG.INSTALL_MODULES(APPS,chr(9)+'FileName Prefix......:"' +FileNamePrefix+'"');
    LOG.INSTALL_MODULES(APPS,chr(9)+'Target file..........:"' +target_file+'"');
    LOG.INSTALL_MODULES(APPS,chr(9)+'uri..................:"' +uri_download + '"');





    if length(package_version)=0 then begin
         LOG.INSTALL_MODULES(APPS,'http source problem [NEXT]\' + package_name +  ' is null...aborting');
         exit;
    end;

    if CHEK_LOCAL_VERSION_BEFORE>0 then begin
       RegExpr.Expression:='([0-9\.]+)';
       if RegExpr.Exec(package_version) then begin
              tmp:=AnsiReplaceText(RegExpr.Match[1],'.','');
              int_version:=StrToInt(tmp);
              LOG.INSTALL_MODULES(APPS,chr(9)+'Check version........:remote=' +IntToStr(int_version) + '<> local=' + IntToStr(CHEK_LOCAL_VERSION_BEFORE));
       end else begin
            exit;
       end;

       if CHEK_LOCAL_VERSION_BEFORE>=int_version then begin
          LOG.INSTALL_MODULES(APPS,chr(9)+'Checked..............:updated, nothing to do');
          exit;
       end;
    end;

    LOG.INSTALL_MODULES(APPS,'#################################################################################');
    LOG.INSTALL_MODULES(APPS,'');
    LOG.INSTALL_MODULES(APPS,'');

    if FILE_EXT='tar.bz2' then DECOMPRESS_OPT:='xjf' else DECOMPRESS_OPT:='xzf';

     if DirectoryExists('/opt/artica/install/sources/' + package_name_suffix) then fpsystem('/bin -rm -rf /opt/artica/install/sources/' + package_name);
     LOG.INSTALL_MODULES(APPS,'Creating directory ' + '/opt/artica/install/sources/' + package_name);
     forcedirectories('/opt/artica/install/sources/' + package_name);

    writeln('');
    writeln('');
    LOG.INSTALL_MODULES(APPS,'Get: ' + uri_download);

    if local_folder='' then begin
       GLOBAL_INI.WGET_DOWNLOAD_FILE(uri_download,'/opt/artica/install/sources/' + target_file);
    end else begin
        fpsystem('/bin/cp -fv ' + uri_download + ' ' +  '/opt/artica/install/sources/' + target_file);
    end;
    writeln('');
    writeln('');
    if not FileExists('/opt/artica/install/sources/' + target_file) then begin
        LOG.INSTALL_MODULES(APPS,'Unable to stat /opt/artica/install/sources/' + target_file);
        exit;
    end;

    LOG.INSTALL_MODULES(APPS,'Uncompress the package...');
    LOG.INSTALL_MODULES(APPS,'tar -' + DECOMPRESS_OPT + ' /opt/artica/install/sources/' + target_file + ' -C /opt/artica/install/sources/' + package_name);
    fpsystem('tar -' + DECOMPRESS_OPT + ' /opt/artica/install/sources/' + target_file + ' -C /opt/artica/install/sources/' + package_name);
    sys.DirDir('/opt/artica/install/sources/' + package_name);

    if sys.DirListFiles.Count=0 then begin
       LOG.INSTALL_MODULES('APP_SQLITE','ERROR:: Bad repository format !!!');
       fpsystem('/bin/rm -rf /opt/artica/install/sources/'+package_name);
       fpsystem('/bin/rm /opt/artica/install/sources/'+target_file);
       goto myEnd;
    end;
    compile_source:='/opt/artica/install/sources/' + package_name + '/' + sys.DirListFiles.Strings[0];
    LOG.INSTALL_MODULES(APPS,'SUCCESS: "' + compile_source + '"');
    result:=compile_source;
 goto myEnd;

myEnd:
    LOG.Free;
    GLOBALINI.Free;
    sys.Free;
    FILE_TEMP.free;


end;
procedure Tclass_install.DNSMASQ_INSTALL();
var
   source:string;
   LOG:Tlogs;



begin
  CHEK_LOCAL_VERSION_BEFORE:=0;
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           DNSMASQ                              xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

     source:=COMPILE_GENERIC_APPS('APP_DNSMASQ','dnsmasq','dnsmasq');
     if not DirectoryExists(source) then exit;
     LOG:=TLogs.Create;
     GLOBALINI:=myConf.Create;

    LOG.INSTALL_MODULES('APP_DNSMASQ','compile from ' + source +' directory');
    fpsystem('cd ' + source + '&& make ');
    LOG.INSTALL_MODULES('APP_DNSMASQ','Install from ' + source +' directory');
    fpsystem('cd ' + source + '&& make install');
    DNSMASQ_RECONFIGURE();
     
end;
//##############################################################################
procedure Tclass_install.CYRUS_CHECK();
begin
   CYRUS_IMAPD_CONFIGURE();
   PERL_CYRUS_IMAP();
   ForceDirectories('/var/lib/cyrus/db');
   
end;
//##############################################################################


procedure Tclass_install.BERKLEY_INSTALL();
var
   source:string;
   LOG:Tlogs;
   sys:TSystem;



begin

 sys:=Tsystem.Create();
 LOG:=TLogs.Create;
 CHEK_LOCAL_VERSION_BEFORE:=0;


  if  DirectoryExists('/opt/artica/db') then begin
    LOG.INSTALL_MODULES('APP_BOGOFILTER','BERKLEY:......................:OK');
    exit();
  end;

   writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           DB LIBRARIES                         xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

  if not FileExists('/opt/artica/install/sources/db') then begin
         source:=COMPILE_GENERIC_APPS('APP_BOGOFILTER','db','db');
  end else begin
      sys.DirDir('/opt/artica/install/sources/db');
      if sys.DirListFiles.Count=0 then source:=COMPILE_GENERIC_APPS('APP_BOGOFILTER','db','db') else source:='/opt/artica/install/sources/db/' + sys.DirListFiles.Strings[0];
  end;
  




  LOG.INSTALL_MODULES('APP_BOGOFILTER','BERKLEY: source path is ' + source);
  
  if not DirectoryExists(source) then exit;


  if not DirectoryExists(source + '/build_unix') then begin
     LOG.INSTALL_MODULES('APP_BOGOFILTER','BERKLEY: unable to stat ' + source + '/build_unix');
     exit;
  end;
  LOG.INSTALL_MODULES('APP_BOGOFILTER','BERKLEY: Starting installation of Berkley database engine');
  LOG.INSTALL_MODULES('APP_BOGOFILTER','BERKLEY: turn into ' + source + '/build_unix');
  fpsystem('cd ' + source + '/build_unix && ../dist/configure --prefix=/opt/artica/db && make && make install');

  
  if DirectoryExists('/opt/artica/db') then begin
      LOG.INSTALL_MODULES('APP_BOGOFILTER','BERKLEY: Success');
      {GLOBALINI.SYSTEM_LD_SO_CONF_ADD('/opt/artica/db/lib');
      forcedirectories('/usr/include/db');}
      fpsystem('/bin/rm -rf ' + source);
  end;
  
  
end;
//##############################################################################
procedure Tclass_install.GSL_INSTALL();
var
   source:string;
   LOG:Tlogs;

begin
     CHEK_LOCAL_VERSION_BEFORE:=0;

  LOG:=TLogs.Create;
  if FileExists('/opt/artica/lib/libgsl.so') then begin
      LOG.INSTALL_MODULES('APP_BOGOFILTER','GSL Libraries.................:OK');
      exit();
  end;

  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                        GSL LIBRARIES                           xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  if paramStr(1)='-install-web-artica' then begin
     writeln('skip it');
     exit;
  end;
  
  source:=COMPILE_GENERIC_APPS('APP_BOGOFILTER','gsl','gsl');
  if not DirectoryExists(source) then exit;


  fpsystem('cd ' + source + ' && ./configure --prefix=/opt/artica && make && make install');
  fpsystem('/sbin/ldconfig');
end;

function Tclass_install.CYRUS_IMAPD_CONFIGURE():boolean;

begin
   result:=false;
   ccyrus.WRITE_IMAPD_CONF();
   ccyrus.WRITE_CYRUS_CONF();
end;
//##############################################################################


procedure Tclass_install.LIBSSL_INSTALL();
var
   source:string;
   LOG:Tlogs;
   mt:string;
begin

  CHEK_LOCAL_VERSION_BEFORE:=0;
  LOG:=TLogs.Create;
  GLOBALINI:=myConf.Create;
  if FileExists('/opt/artica/lib/libcrypto.a') then begin
      log.INSTALL_MODULES('APP_OPENLDAP','OpenSSL.......................:OK');
      exit();
  end;
  
  mt:='-m32';
  
  if ParamStr(2)='no-force-64' then mt:='';
  
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                        OPENSSL LIBRARIES                       xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

 source:=COMPILE_GENERIC_APPS('APP_OPENLDAP','openssl','openssl');
  if not DirectoryExists(source) then begin
     LOG.INSTALL_MODULES('APP_OPENLDAP','Unable to stat source "' + source + '"');
     exit;
  end;

  {if fileExists('/usr/lib/libssl.so.0') then fpsystem('/bin/rm -rf /usr/lib/libssl.so.0');
  if fileExists('/usr/lib/libssl.so') then fpsystem('/bin/rm -rf  /usr/lib/libssl.so') ;
  if fileExists('/lib/libssl.so.2')then fpsystem('/bin/rm  -rf /lib/libssl.so.2') ;
  if fileExists('/usr/lib/libcrypto.so.0') then fpsystem('/bin/rm  -rf /usr/lib/libcrypto.so.0');}
  
  //-m32 pour 64 bits
  
  fpsystem('cd ' + source + ' && ./Configure ' + mt + ' linux-elf --prefix=/opt/artica --openssldir=/opt/artica/ssl shared && make && make install');

  if FileExists('/opt/artica/lib/libcrypto.a') then begin
  {if FileExists('/lib/libssl.so.4') then fpsystem('/bin/rm -f /lib/libssl.so.4');
  if FileExists('/usr/lib/libcrypto.so.0') then fpsystem('/bin/rm -f /usr/lib/libcrypto.so.0');
  if FileExists('/usr/local/ssl/lib/libssl.so.0.9.8') then fpsystem('ln -s /usr/local/ssl/lib/libssl.so.0.9.8 /lib/libssl.so.4 >/dev/null 2>&1');
  if FileExists('/usr/lib/libcrypto.so.0.9.8') then fpsystem('ln -s /usr/lib/libcrypto.so.0.9.8 /usr/lib/libcrypto.so.0 >/dev/null 2>&1');}
  log.INSTALL_MODULES('APP_OPENLDAP','OpenSSL.......................:OK');
  if not FileExists('/usr/local/include/openssl') then begin
      fpsystem('/bin/ln -s /opt/artica/include/openssl /usr/local/include/openssl');
      log.INSTALL_MODULES('APP_OPENLDAP','OpenSSL create symbolic link..:OK');
  end;


   end;


end;
//##############################################################################
procedure Tclass_install.LIBICONV_INSTALL();
var
   source:string;
   LOG:Tlogs;
begin
    LOG:=TLogs.Create;
  //*******************************************************************************
  if not FileExists('/opt/artica/lib/libiconv.so') then begin
     source:=COMPILE_GENERIC_APPS('APP_ARTICA','libiconv','libiconv');
     if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','Error extracting');
        exit;
     end;

     fpsystem('cd ' + source + ' && ./configure --enable-extra-encodings --prefix=/opt/artica && make && make install');
     if not FileExists('/opt/artica/lib/libiconv.so') then begin
        log.INSTALL_MODULES('APP_ARTICA','Error installing libiconv');
        exit;
     end;
     log.INSTALL_MODULES('APP_ARTICA','libiconv......................:OK');
  end else begin
     log.INSTALL_MODULES('APP_ARTICA','libiconv......................:OK');
  end;

end;
//##############################################################################
procedure Tclass_install.UNRAR_INSTALL();
var
   source:string;
   LOG:Tlogs;
begin
    LOG:=TLogs.Create;
  //*******************************************************************************
  if FileExists('/opt/artica/bin/unrar') then begin
      log.INSTALL_MODULES('APP_ARTICA','unrar.........................:OK');
      exit;
  end;
     source:=COMPILE_GENERIC_APPS('APP_ARTICA','unrarsrc','unrarsrc');
     if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','unrar.........................:Error extracting');
        exit;
     end;
     fpsystem('cp ' + source + '/makefile.unix ' + source + '/makefile');
     fpsystem('cd ' + source + ' && make');
     if not FileExists(source + '/unrar') then begin
        log.INSTALL_MODULES('APP_ARTICA','unrar.........................:Error installing unrar');
        exit;
     end;
     
     fpsystem('/bin/cp '+source + '/unrar' + ' /opt/artica/bin/');
 if FileExists('/opt/artica/bin/unrar') then begin
      log.INSTALL_MODULES('APP_ARTICA','unrar.........................:OK');
      exit;
  end else begin
  log.INSTALL_MODULES('APP_ARTICA','unrar.........................:Error installing unrar');
  end;
end;

procedure Tclass_install.MAILUTILS_INSTALL();
var
   source:string;
   LOG:Tlogs;
   source_compile:string;
   RegExpr:TRegExpr;
   i:integer;
   l:TstringList;
   l2:TstringList;
   j:integer;
   found:boolean;
begin

    LOG:=TLogs.Create;
    found:=false;
if ParamStr(2)<>'--force' then begin
   if FileExists('/opt/artica/bin/mailutils-config') then begin
      log.INSTALL_MODULES('APP_ARTICA','MailUtils....................:OK');
      exit;
   end;
end;
   source:=COMPILE_GENERIC_APPS('APP_ARTICA','mailutils','mailutils');

if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','mailutils....................:Error extracting source');
        exit;
     end;


     source_compile:='cd ' + source + ' && ./configure --prefix=/opt/artica --with-berkeley-db -with-libiconv-prefix=/opt/artica --with-log-facility=facility ';
     source_compile:=source_compile + LD_LIBRARY_PATH +' ' + CPPFLAGS;
     source_compile:= source_compile + '&& make';

     log.INSTALL_MODULES('APP_ARTICA',source_compile);
     fpsystem(source_compile);

     
     if not FileExists(source + '/mail.local/Makefile') then begin
         log.INSTALL_MODULES('APP_ARTICA','mailutils.....................: Unable to stat '+ source + '/mail.local/Makefile');
         readln();
         exit;
     end;
     l:=TstringList.Create;
     l2:=TStringList.Create;
     l2.Add('${MU_LIB_AUTH}\');
     l2.Add('${MU_LIB_POP}\');
     l2.Add('${MU_LIB_IMAP}\');
     l2.Add('${MU_LIB_MH}\');
     l2.Add('${MU_LIB_MAILDIR}\');
     l2.Add('${MU_LIB_NNTP}\');
     
     log.INSTALL_MODULES('APP_ARTICA','mailutils.....................: Patching mail.local/Makefile see ref http://article.gmane.org/gmane.comp.gnu.mailutils.bugs/1037');
     l.LoadFromFile(source + '/mail.local/Makefile');
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='\$\{MU_LIB_AUTH\}';
     
     for i:=0 to l.Count-1 do begin
          if RegExpr.Exec(l.Strings[i]) then begin
             found:=true;
             log.INSTALL_MODULES('APP_ARTICA','mailutils.....................: patching line ' + IntToStr(i));
             j:=i+1;
             l.Insert(j,'${MU_LIB_NNTP}\');
             l.Insert(j,'${MU_LIB_MAILDIR}\');
             l.Insert(j,'${MU_LIB_MH}\');
             l.Insert(j,'${MU_LIB_IMAP}\');
             l.Insert(j,'${MU_LIB_POP}\');
             break;
          end;
     end;
     
     if found=false then begin
         log.INSTALL_MODULES('APP_ARTICA','mailutils.....................: unable to find MU_LIB_AUTH in file...');
         readln();
         exit;
     end;
     
     l.SaveToFile(source + '/mail.local/Makefile');
     source_compile:='cd ' + source + ' && make && make install';
     log.INSTALL_MODULES('APP_ARTICA',source_compile);
     fpsystem(source_compile);
     


if not FileExists('/opt/artica/bin/mailutils-config') then begin
      log.INSTALL_MODULES('APP_ARTICA','mailutils.....................: Failed');
      readln();
      exit;
   end;

   log.INSTALL_MODULES('APP_ARTICA','mailutils.....................: OK');

end;
//##############################################################################

//##############################################################################
procedure Tclass_install.DCC_INSTALL();
var
   source:string;
   LOG:Tlogs;
   source_compile:string;
begin

    LOG:=TLogs.Create;

if ParamStr(2)<>'--force' then begin
   if FileExists('/opt/artica/bin/dccproc') then begin
      log.INSTALL_MODULES('APP_ARTICA','DCC for AS....................:OK');
      exit;
   end;
end;
   source:=COMPILE_GENERIC_APPS('APP_ARTICA','dcc','dcc');

if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','DCC for AS...................:Error extracting source');
        exit;
     end;


source_compile:='cd ' + source + ' && ./configure --bindir=/opt/artica/bin  --libexecdir=/opt/artica/libexec --homedir=/opt/artica/dcc --mandir=/opt/artica/man ';
source_compile:= source_compile + '&& make && make install && make && make install ';
log.INSTALL_MODULES('APP_ARTICA',source_compile);
fpsystem(source_compile);



if not FileExists('/opt/artica/bin/dccproc') then begin
      log.INSTALL_MODULES('APP_ARTICA','DCC for AS....................:Failed');
      exit;
   end;
fpsystem('/bin/touch /opt/artica/dcc/map');
source_compile:='cd ' + source + ' && make install';
log.INSTALL_MODULES('APP_ARTICA',source_compile);
fpsystem(source_compile);

if not FileExists('/opt/artica/bin/dccproc') then begin
      log.INSTALL_MODULES('APP_ARTICA','DCC for AS....................:Failed');
      exit;
   end;
   
  if FileExists('/opt/artica/bin/dccproc') then begin
      log.INSTALL_MODULES('APP_ARTICA','DCC for AS....................:OK');
      exit;
   end;

end;
//##############################################################################



procedure Tclass_install.LIFREETYPE_INSTALL();
var
   source:string;
   LOG:Tlogs;
   source_compile:string;
begin

    LOG:=TLogs.Create;

  if FileExists('/opt/artica/lib/libfreetype.so') then begin
     log.INSTALL_MODULES('APP_ARTICA','libfreetype...................:OK');
     exit();
  end;
  

  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           FREETYPE LIBRARY                     xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  
     source:=COMPILE_GENERIC_APPS('APP_ARTICA','freetype','freetype');
     if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','libfreetype Error extracting');
        exit;
     end;
     source_compile:='cd ' + source + ' && ./configure  --prefix=/opt/artica ' + LD_LIBRARY_PATH + ' ' + CPPFLAGS + ' && make && make install';
     fpsystem(source_compile);

     if not FileExists('/opt/artica/lib/libfreetype.so') then begin
        log.INSTALL_MODULES('APP_ARTICA','libfreetype...................:Error installing libfreetype');
        exit;
     end;



     log.INSTALL_MODULES('APP_ARTICA','libfreetype...................:OK');

end;
//##############################################################################
procedure Tclass_install.LIBDBI_INSTALL();
var
   source:string;
   LOG:Tlogs;
   source_compile:string;
begin

    LOG:=TLogs.Create;

  if FileExists('/opt/artica/lib/libdbi.so') then begin
     log.INSTALL_MODULES('APP_ARTICA','libdbi........................:OK');
     exit();
  end;


  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           LIBDBI LIBRARY                       xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

     source:=COMPILE_GENERIC_APPS('APP_ARTICA','libdbi','libdbi');
     if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','libfreetype Error extracting');
        exit;
     end;
     source_compile:='cd ' + source + ' && ./configure  --prefix=/opt/artica ' + LD_LIBRARY_PATH + ' ' + CPPFLAGS + ' && make && make install';
     log.INSTALL_MODULES('APP_ARTICA',source_compile);
     fpsystem(source_compile);

     if not FileExists('/opt/artica/lib/libdbi.so') then begin
        log.INSTALL_MODULES('APP_ARTICA','libdbi........................:Error installing libdbi');
        exit;
     end;


     log.INSTALL_MODULES('APP_ARTICA','libdbi........................:OK');
end;
//##############################################################################
procedure Tclass_install.NTPD_INSTALL();
var
   source:string;
   LOG:Tlogs;
   source_compile:string;
begin

LOG:=TLogs.Create;
if Paramstr(2)<>'--force' then begin
  if FileExists('/opt/artica/bin/ntpd') then begin
     log.INSTALL_MODULES('APP_ARTICA','ntpd..........................: OK');
     exit();
  end;
end;



  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           NTPD TIME DAEMON                     xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

     source:=COMPILE_GENERIC_APPS('APP_ARTICA','ntp','ntp');
     if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','ntpd..........................: Error extracting');
        exit;
     end;
     source_compile:='cd ' + source + ' && ./configure  --prefix=/opt/artica ' + LD_LIBRARY_PATH + ' ' + CPPFLAGS + ' && make && make install';
     log.INSTALL_MODULES('APP_ARTICA',source_compile);
     fpsystem(source_compile);

     if not FileExists('/opt/artica/bin/ntpd') then begin
        log.INSTALL_MODULES('APP_ARTICA','ntpd..........................: Error installing ntpd');
        exit;
     end;


     log.INSTALL_MODULES('APP_ARTICA','ntpd..........................: OK');
end;

procedure Tclass_install.LIBNCURSES_INSTALL();
var
   source:string;
   LOG:Tlogs;
begin

    LOG:=TLogs.Create;
    if not FileExists('/opt/artica/lib/libncurses.a') then begin
       source:=COMPILE_GENERIC_APPS('APP_MYSQL','ncurses','ncurses');
       if not DirectoryExists(source) then begin
          log.INSTALL_MODULES('APP_ARTICA','libncurses....................: failed (error extracting)');
          exit;
       end;
       fpsystem('cd ' + source + ' && ./configure --prefix=/opt/artica && make && make install');
       if not FileExists('/opt/artica/lib/libncurses.a') then begin
          log.INSTALL_MODULES('APP_ARTICA','libncurses....................: failed (error compilation)');
          log.INSTALL_MODULES('APP_ARTICA','Error installing libncurses');
       end;
    end;


  log.INSTALL_MODULES('APP_ARTICA','libncurses....................:OK');
       
       
end;


//##############################################################################

procedure Tclass_install.MYSQL_INSTALL();
var
   source    :string;
   LOG       :Tlogs;
   compile   :string;
begin
     LOG:=TLogs.Create;


   if FileExists('/opt/artica/mysql/libexec/mysqlmanager') then begin
      log.INSTALL_MODULES('APP_ARTICA','mysql server for ARTICA.......:OK');
      exit;
   end;

  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                        Mysql Server artica                     xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();



     if not FileExists('/opt/artica/lib/libncurses.a') then LIBNCURSES_INSTALL();
     if not FileExists('/opt/artica/lib/libncurses.a') then begin
        log.INSTALL_MODULES('APP_ARTICA','mysql server..................:Failed (unable to stat /opt/artica/lib/libncurses.a)');
        exit;
     end;
     source:=COMPILE_GENERIC_APPS('APP_MYSQL','mysql','mysql');
     if not DirectoryExists(source) then begin
        log.INSTALL_MODULES('APP_ARTICA','mysql server..................:Failed (unable to extract sources)');
     end;

     compile:='cd ' + source;
     compile:=compile + ' && ./configure';
     compile:=compile + ' --localstatedir=/opt/artica/mysql/mysql/data';
     compile:=compile + ' --prefix=/opt/artica/mysql';
     compile:=compile + ' --with-tcp-port=47910';
     compile:=compile + ' --with-mysqld-user=mysql';
     compile:=compile + ' --sysconfdir=/opt/artica/mysql/etc';


     compile:=compile + ' --without-man';
     compile:=compile + ' --enable-assembler ';
     compile:=compile + ' --disable-shared ';
     compile:=compile + ' --with-mysqld-ldflags="-all-static" ';
     compile:=compile + ' --with-client-ldflags="-all-static" ';
     compile:=compile + ' --without-bench ';
     compile:=compile + ' --without-docs ';
     compile:=compile + ' --without-extra-tools ';
     compile:=compile + ' --without-debug ';
     compile:=compile + ' --with-charset=utf8 ';
     compile:=compile + ' --with-collation=utf8_general_ci ';
     compile:=compile + ' --enable-thread-safe-client ';
     compile:=compile + ' --with-innodb ';
     compile:=compile + ' --enable-local-infile';
     compile:=compile + ' ' + LD_LIBRARY_PATH;
     compile:=compile + ' ' + CPPFLAGS;
     compile:=compile + ' && make && make install';

     log.INSTALL_MODULES('APP_ARTICA',compile);
     fpsystem(compile);


   if not FileExists('/opt/artica/mysql/libexec/mysqlmanager') then begin
      log.INSTALL_MODULES('APP_ARTICA','mysql server..................:Failed (error compiling)');
      log.INSTALL_MODULES('APP_ARTICA','mysql server..................:Failed (unable to stat /opt/artica/mysql/libexec/mysqlmanager)');
      exit;
   end;
   PERL_DBD_MYSQL();
   MYSQL_RECONFIGURE();
end;
//##############################################################################
procedure Tclass_install.MYSQL_RECONFIGURE();
var
   log:Tlogs;
   SYS:Tsystem;
   GLOBAL_INI:Myconf;
   conf:Tstringlist;
   PID:string;
   D:boolean;
   cmd:string;
   tmp_pid:string;
   l:TstringLIst;
   i:Integer;
   continue:boolean;
begin
   log:=Tlogs.Create;
   SYS:=Tsystem.create;
   GLOBAL_INI:=Myconf.Create;
   D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
   l:=Tstringlist.Create;

   tmp_pid:='/var/run/artica-sql/grant-mysqld.pid';
   forcedirectories('/opt/artica/mysql/etc');
   forcedirectories('/var/run/artica-sql');
   forcedirectories('/opt/artica/logs/artica-sql');
   forcedirectories('/opt/artica/mysql/mysql/data');
   forcedirectories('/opt/artica/mysql/mysql-data');
   forcedirectories('/var/run/artica-sql');
   continue:=false;
            if not FileExists('/opt/artica/mysql/etc/my.cnf') then begin
                   conf:=TstringList.Create;

                   conf.Add('[client]');
                   conf.Add('port		= 47910');
                   conf.Add('socket		= /var/run/artica-sql/mysqld.sock');
                   conf.Add('');
                   conf.Add('[mysqld_safe]');
                   conf.Add('socket		= /var/run/artica-sql/mysqld-safe.sock');
                   conf.Add('nice		= 0');
                   conf.Add('');
                   conf.Add('[mysqld]');
                   conf.Add('user		= artica');
                   conf.Add('pid-file	= /var/run/artica-sql/mysqld.pid');
                   conf.Add('socket		= /var/run/artica-sql/mysqld.sock');
                   conf.Add('port		= 47910');
                   conf.Add('basedir		= /opt/artica/mysql');
                   conf.Add('datadir		= /opt/artica/mysql/mysql-data');
                   conf.Add('tmpdir		= /tmp');
                   conf.Add('language	= /opt/artica/mysql/share/mysql/english');
                   conf.Add('skip-external-locking');
                   conf.Add('bind-address	= 127.0.0.1');
                   conf.Add('key_buffer		= 16M');
                   conf.Add('max_allowed_packet	= 16M');
                   conf.Add('thread_stack	= 128K');
                   conf.Add('thread_cache_size	= 8');
                   conf.Add('query_cache_limit  = 1M');
                   conf.Add('query_cache_size   = 16M');
                   conf.Add('log_bin		= /opt/artica/logs/artica-sql/mysql-bin.log');
                   conf.Add('expire_logs_days	= 10');
                   conf.Add('max_binlog_size    = 20M');
                   conf.Add('');
                   conf.Add('[mysqldump]');
                   conf.Add('quick');
                   conf.Add('quote-names');
                   conf.Add('max_allowed_packet	= 16M');
                   conf.Add('');
                   conf.Add('[mysql]');
                   conf.Add('#no-auto-rehash	# faster start of mysql but no tab completition');
                   conf.Add('');
                   conf.Add('[isamchk]');
                   conf.Add('key_buffer		= 16M');
                   conf.SaveToFile('/opt/artica/mysql/etc/my.cnf');
                   if D then writeln('-> /opt/artica/mysql/etc/my.cnf :');
                   if D then writeln(conf.Text);
                   conf.free;
            end else begin
                 log.INSTALL_MODULES('APP_ARTICA','mysql server..................: OK /opt/artica/mysql/etc/my.cnf');
            end;


            l.AddStrings(GLOBAL_INI.MYSQL_DATABASE_CHECK_LIST(GLOBAL_INI.MYSQL_SERVER_PARAMETERS_CF('datadir')));
            for i:=0 to l.Count-1 do begin
                 if not FileExists(l.Strings[i]) then begin
                    log.INSTALL_MODULES('APP_ARTICA','mysql server..................: File '  + l.Strings[i] + ' doesn''t exists, must create database');
                    continue:=true;
                 end else begin
                    log.INSTALL_MODULES('APP_ARTICA','mysql server..................: File '  + l.Strings[i] + ' OK');
                 end;
            end;


    if continue=false then begin
         log.INSTALL_MODULES('APP_ARTICA','mysql server..................:OK First mandatories Databases for Mysql Engine.');
         GLOBAL_INI.MYSQL_RECONFIGURE_DB();
         exit;
    end;

          
zmysql.SERVICE_STOP();
   if FileExists('/etc/mysql/debian.cnf') then begin
      if D then writeln('/etc/mysql/debian.cnf exists, rename it');
      fpsystem('/bin/mv /etc/mysql/debian.cnf /etc/mysql/debian.artica.cnf');
   end;

   if FileExists('/etc/mysql/my.cnf') then begin
      if D then writeln('/etc/mysql/my.cnf exists, rename it');
      fpsystem('/bin/mv /etc/mysql/my.cnf /etc/mysql/my.artica.cnf');
   end;

   if FileExists('/etc/my.cnf') then begin
       if D then writeln('/etc/my.cnf exists, rename it');
       fpsystem('/bin/mv /etc/my.cnf /etc/my.artica.cnf');
   end;



     if ParamStr(2)='--rebuild' then begin
      log.INSTALL_MODULES('APP_ARTICA','mysql server..................: Rebuild Databases');
      fpsystem('/bin/rm -rf /opt/artica/mysql/mysql-data');
   end;

   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: Creating user artica and set securites in folders');
   sys:=Tsystem.Create();
   sys.AddUserToGroup('artica','artica','','');
   forcedirectories('/opt/artica/logs/artica-sql');
   forcedirectories('/opt/artica/mysql/mysql/data');
   forcedirectories('/opt/artica/mysql/mysql-data');
   forcedirectories('/var/run/artica-sql');



   fpsystem('/bin/chown -R artica:artica /opt/artica/mysql');
   fpsystem('/bin/chmod -R 0755 /opt/artica/logs/artica-sql');
   fpsystem('/bin/chown -R artica:artica /opt/artica/logs/mysql-amavis');
   fpsystem('/bin/chown artica:artica /opt/artica/amavis/mysql-data');
   fpsystem('/bin/chmod 0755 /var/run/mysqld');
   fpsystem('/bin/chown artica:artica /var/run/artica-sql');


   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: Creating default database');
   
   cmd:='/opt/artica/mysql/bin/my_print_defaults --defaults-file=/opt/artica/mysql/etc/my.cnf client mysql';
   
   if D then writeln(cmd);
   fpsystem(cmd);

   if D then writeln('Stopping mysql id it run in background');


   cmd:='/opt/artica/mysql/libexec/mysqld --defaults-file=/opt/artica/mysql/etc/my.cnf --skip-grant --user=artica --basedir=/opt/artica/mysql --datadir=/opt/artica/mysql/mysql-data';
   cmd:=cmd + ' --pid-file=' + tmp_pid;
   cmd:=cmd + ' --port=47901';
   cmd:=cmd + ' &';
   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: Execute:');
   log.INSTALL_MODULES('APP_ARTICA',cmd);
   if D then writeln(cmd);
   fpsystem(cmd);
   PID:=SYS.GET_PID_FROM_PATH(tmp_pid);

   if D then writeln('sleep 5000....');
   sleep(5000);



   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: sqkip-grant PID :'+PID);
   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: INSTALL DATABASES NOW');
   cmd:='/opt/artica/mysql/bin/mysql_install_db --user=artica --ldata=/opt/artica/mysql/mysql-data --basedir=/opt/artica/mysql';
   
   if D then writeln(cmd);
   log.INSTALL_MODULES('APP_ARTICA',cmd);
   fpsystem(cmd);
   if D then writeln('sleep 5000....');
   sleep(5000);


   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: OK Is time to shutdown skip-grant server now...');
  zmysql.SERVICE_STOP();
   PID:=SYS.GET_PID_FROM_PATH(tmp_pid);
   if D then writeln('Stopping mysql id it run in background (' + tmp_pid + ' ' + PID+')');
   if trim(PID)='0' then PID:='';
   if length(trim(PID))>0 then fpsystem('/bin/kill ' + PID);



   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: reset my.cnf in default folders');
   if FileExists('/etc/mysql/debian.artica.cnf') then begin
      if D then writeln('/etc/mysql/debian.artica.cnf exists, rename it');
      fpsystem('/bin/mv /etc/mysql/debian.artica.cnf /etc/mysql/debian.cnf');
   end;

   if FileExists('/etc/mysql/my.artica.cnf') then begin
      if D then writeln('/etc/mysql/my.artica.cnf exists, rename it');
      fpsystem('/bin/mv /etc/mysql/my.artica.cnf /etc/mysql/my.cnf');
   end;

   if FileExists('/etc/my.artica.cnf') then begin
       if D then writeln('/etc/my.artica.cnf exists, rename it');
       fpsystem('/bin/mv /etc/my.artica.cnf /etc/my.cnf');
   end;

   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: Now i will restart 3 times the server');
   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: To ensure that all tables are created...');
   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: restart 1/3');
   zmysql.SERVICE_STOP();
   zmysql.SERVICE_START();
   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: restart 2/3');
   zmysql.SERVICE_STOP();
   zmysql.SERVICE_START();
   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: restart 3/3');
   zmysql.SERVICE_STOP();
   zmysql.SERVICE_START();
   log.INSTALL_MODULES('APP_ARTICA','mysql server..................: done');

   
   
   //reset root password http://lists.mysql.com/mysql/171366

end;
//##############################################################################



procedure Tclass_install.ARTICA_WEB_CONFIG();
var
   List:TStringList;
   mailm:TstringList;
   GLOBALINI:MyConf;
   HostName:string;
    LOG:Tlogs;
begin
GLOBALINI:=myConf.Create;
 LOG:=Tlogs.Create;
HostName:=GLOBALINI.LINUX_GET_HOSTNAME;
log.INSTALL_MODULES('APP_ARTICA','Hostname......................:'+HostName);
list:=tstringList.Create;

List.Add('ServerRoot "/opt/artica"');
List.Add('Listen 9000');
List.Add('SSLPassPhraseDialog  builtin');
List.Add('SSLSessionCache        "shmcb:/opt/artica/logs/ssl_scache(512000)"');
List.Add('SSLSessionCacheTimeout  300');
List.Add('SSLMutex  "file:/opt/artica/logs/ssl_mutex"');
List.Add('SSLEngine on');
List.Add('SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL');
List.Add('SSLCertificateFile "/opt/artica/ssl/certs/server.crt"');
List.Add('SSLCertificateKeyFile "/opt/artica/ssl/certs/server.key"');
List.Add('');
List.Add('<IfModule !mpm_netware_module>');
List.Add('User daemon');
List.Add('Group daemon');
List.Add('ServerName ' + Hostname);
List.Add('</IfModule>');
List.Add('');
List.Add('ServerAdmin you@example.com');
List.Add('DocumentRoot "/usr/share/artica-postfix"');
List.Add('');
List.Add('<Directory />');
List.Add('    SSLOptions +StdEnvVars');
List.Add('    Options FollowSymLinks');
List.Add('    AllowOverride None');
List.Add('    Order deny,allow');
List.Add('    Deny from all');
List.Add('</Directory>');
List.Add('');
List.Add('');
List.Add('<Directory "/usr/share/artica-postfix">');
List.Add('    Options Indexes FollowSymLinks');
List.Add('    AllowOverride None');
List.Add('    Order allow,deny');
List.Add('    Allow from all');
List.Add('');
List.Add('</Directory>');
List.Add('');
List.Add('<IfModule dir_module>');
List.Add('    DirectoryIndex index.php');
List.Add('</IfModule>');
List.Add('');
List.Add('<FilesMatch "^\.ht">');
List.Add('    Order allow,deny');
List.Add('    Deny from all');
List.Add('    Satisfy All');
List.Add('</FilesMatch>');
List.Add('');
List.Add('ErrorLog logs/error_log');
List.Add('');
List.Add('LogLevel warn');
List.Add('');
List.Add('<IfModule log_config_module>');
List.Add('    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined');
List.Add('    LogFormat "%h %l %u %t \"%r\" %>s %b" common');
List.Add('');
List.Add('    <IfModule logio_module>');
List.Add('      LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio');
List.Add('    </IfModule>');
List.Add('');
List.Add('    CustomLog logs/access_log common');
List.Add('</IfModule>');
List.Add('');
List.Add('<IfModule alias_module>');
List.Add('    ScriptAlias /cgi-bin/ "/opt/artica/cgi-bin/"');
List.Add('');
List.Add('</IfModule>');
List.Add('');
List.Add('<IfModule cgid_module>');
List.Add('</IfModule>');
List.Add('');
List.Add('<Directory "/opt/artica/cgi-bin">');
List.Add('    AllowOverride None');
List.Add('    Options None');
List.Add('    Order allow,deny');
List.Add('    Allow from all');
List.Add('</Directory>');
List.Add('');
List.Add('DefaultType text/plain');
List.Add('');
List.Add('<IfModule mime_module>');
List.Add('    TypesConfig conf/mime.types');
List.Add('');
List.Add('    #AddType application/x-gzip .tgz');
List.Add('    #AddEncoding x-compress .Z');
List.Add('    #AddEncoding x-gzip .gz .tgz');
List.Add('    AddType application/x-compress .Z');
List.Add('    AddType application/x-gzip .gz .tgz');
List.Add('    AddType application/x-httpd-php .php .phtml');
List.Add('    AddType application/x-httpd-php-source .phps');
List.Add('    #AddHandler cgi-script .cgi');
List.Add('    #AddType text/html .shtml');
List.Add('    #AddOutputFilter INCLUDES .shtml');
List.Add('</IfModule>');
List.Add('');
List.Add('#MIMEMagicFile conf/magic');
List.Add('#ErrorDocument 500 "The server made a boo boo."');
List.Add('#ErrorDocument 404 /missing.html');
List.Add('#ErrorDocument 404 "/cgi-bin/missing_handler.pl"');
List.Add('#ErrorDocument 402 http://www.example.com/subscription_info.html');
List.Add('#EnableMMAP off');
List.Add('#EnableSendfile off');
List.Add('');
List.Add('<IfModule ssl_module>');
List.Add('SSLRandomSeed startup builtin');
List.Add('SSLRandomSeed connect builtin');
List.Add('</IfModule>');
List.Add('');
List.Add('LoadModule php5_module        modules/libphp5.so');
List.Add('');
if FileExists('/opt/artica/mailman/mail/mailman') then begin
   List.Add('include conf/mailman.httpd.conf');
   if not FileExists('/opt/artica/conf/mailman.httpd.conf') then begin
           log.INSTALL_MODULES('APP_ARTICA','Mailman.......................:/opt/artica/conf/mailman.httpd.conf');
           mailm:=TStringList.Create;
           mailm.Add('ScriptAlias /mailman /opt/artica/mailman/cgi-bin/');
           mailm.Add('Alias /pipermail/ /opt/artica/mailman/archives/public/');
           mailm.Add('<Directory /opt/artica/mailman/cgi-bin/>');
           mailm.Add('	AllowOverride None');
           mailm.Add(' 	Options ExecCGI FollowSymLinks');
           mailm.Add(' 	Order allow,deny');
           mailm.Add(' 	Allow from all');
           mailm.Add('</Directory>');
           mailm.SaveToFile('/opt/artica/conf/mailman.httpd.conf');
           
   end;
end;



List.Add('');
list.SaveToFile('/opt/artica/conf/artica-www.conf');
list.free;
end;

//##############################################################################

procedure tclass_install.PHP_INI();
var
   list:TStringList;
begin

if not DirectoryExists('/opt/artica') then exit;

ForceDirectories('/opt/artica/php');

list:=TStringList.Create;

List.Add('[PHP]');
List.Add('engine = On');
List.Add('zend.ze1_compatibility_mode = Off');
List.Add('short_open_tag = On');
List.Add('asp_tags = Off');
List.Add('precision    =  12');
List.Add('y2k_compliance = On');
List.Add('output_buffering = Off');
List.Add('zlib.output_compression = Off');
List.Add('implicit_flush = Off');
List.Add('unserialize_callback_func=');
List.Add('serialize_precision = 100');
List.Add('allow_call_time_pass_reference = On');
List.Add('safe_mode = Off');
List.Add('safe_mode_gid = Off');
List.Add('safe_mode_include_dir =');
List.Add('safe_mode_exec_dir =');
List.Add('safe_mode_allowed_env_vars = PHP_');
List.Add('safe_mode_protected_env_vars = LD_LIBRARY_PATH');
List.Add('disable_functions =');
List.Add('disable_classes =');
List.Add('expose_php = On');
List.Add('max_execution_time = 90');
List.Add('max_input_time = 60');
List.Add('memory_limit = 128M');
List.Add('error_reporting  =  E_ALL & ~E_NOTICE');
List.Add('display_errors = On');
List.Add('display_startup_errors = On');
List.Add('log_errors = On');
List.Add('log_errors_max_len = 1024');
List.Add('ignore_repeated_errors = Off');
List.Add('ignore_repeated_source = Off');
List.Add('report_memleaks = On');
List.Add('track_errors = Off');
List.Add('eror_prepend_string = "<p style=''font-size:15px;font-weight:bold;color:red;padding:5px;margin:5px;border:1px solid red''>"');
List.Add('error_append_string = "</p>"');
List.Add('error_log = syslog');
List.Add('variables_order = "EGPCS"');
List.Add('register_globals = Off');
List.Add('register_long_arrays = On');
List.Add('register_argc_argv = On');
List.Add('auto_globals_jit = On');
List.Add('post_max_size = 20M');
List.Add('magic_quotes_gpc = Off');
List.Add('magic_quotes_runtime = Off');
List.Add('magic_quotes_sybase = Off');
List.Add('auto_prepend_file =');
List.Add('auto_append_file =');
List.Add('default_mimetype = "text/html"');
List.Add(';default_charset = "iso-8859-1"');
List.Add('doc_root =');
List.Add('user_dir =');
List.Add('enable_dl = On');
List.Add('file_uploads = On');
List.Add('upload_max_filesize = 10M');
List.Add('allow_url_fopen = On');
List.Add('allow_url_include = Off');
List.Add('default_socket_timeout = 60');
List.Add('[Date]');
List.Add('[filter]');
List.Add('[iconv]');
List.Add('[sqlite]');
List.Add('[xmlrpc]');
List.Add('[Pcre]');
List.Add('[Syslog]');
List.Add('define_syslog_variables  = Off');
List.Add('[mail function]');
List.Add('SMTP = localhost');
List.Add('smtp_port = 25');
List.Add('[SQL]');
List.Add('sql.safe_mode = Off');
List.Add('[ODBC]');
List.Add('odbc.allow_persistent = On');
List.Add('odbc.check_persistent = On');
List.Add('odbc.max_persistent = -1');
List.Add('odbc.max_links = -1');
List.Add('odbc.defaultlrl = 4096');
List.Add('odbc.defaultbinmode = 1');
List.Add('[MySQL]');
List.Add('mysql.allow_persistent = On');
List.Add('mysql.max_persistent = -1');
List.Add('mysql.max_links = -1');
List.Add('mysql.default_port =');
List.Add('mysql.default_socket =');
List.Add('mysql.default_host =');
List.Add('mysql.default_user =');
List.Add('mysql.default_password =');
List.Add('mysql.connect_timeout = 60');
List.Add('mysql.trace_mode = Off');
List.Add('[MySQLi]');
List.Add('mysqli.max_links = -1');
List.Add('mysqli.default_port = 3306');
List.Add('mysqli.default_socket =');
List.Add('mysqli.default_host =');
List.Add('mysqli.default_user =');
List.Add('mysqli.default_pw =');
List.Add('mysqli.reconnect = Off');
List.Add('[mSQL]');
List.Add('msql.allow_persistent = On');
List.Add('msql.max_persistent = -1');
List.Add('msql.max_links = -1');
List.Add('[OCI8]');
List.Add('[PostgresSQL]');
List.Add('pgsql.allow_persistent = On');
List.Add('pgsql.auto_reset_persistent = Off');
List.Add('pgsql.max_persistent = -1');
List.Add('pgsql.max_links = -1');
List.Add('pgsql.ignore_notice = 0');
List.Add('pgsql.log_notice = 0');
List.Add('[Sybase]');
List.Add('sybase.allow_persistent = On');
List.Add('sybase.max_persistent = -1');
List.Add('sybase.max_links = -1');
List.Add('sybase.min_error_severity = 10');
List.Add('sybase.min_message_severity = 10');
List.Add('sybase.compatability_mode = Off');
List.Add('[Sybase-CT]');
List.Add('sybct.allow_persistent = On');
List.Add('sybct.max_persistent = -1');
List.Add('sybct.max_links = -1');
List.Add('sybct.min_server_severity = 10');
List.Add('sybct.min_client_severity = 10');
List.Add('[bcmath]');
List.Add('bcmath.scale = 0');
List.Add('[browscap]');
List.Add('[Informix]');
List.Add('ifx.default_host =');
List.Add('ifx.default_user =');
List.Add('ifx.default_password =');
List.Add('ifx.allow_persistent = On');
List.Add('ifx.max_persistent = -1');
List.Add('ifx.max_links = -1');
List.Add('ifx.textasvarchar = 0');
List.Add('ifx.byteasvarchar = 0');
List.Add('ifx.charasvarchar = 0');
List.Add('ifx.blobinfile = 0');
List.Add('ifx.nullformat = 0');
List.Add('[Session]');
List.Add('session.save_handler = files');
List.Add('session.use_coOKies = 1');
List.Add('session.name = PHPSESSID');
List.Add('session.auto_start = 0');
List.Add('session.coOKie_lifetime = 0');
List.Add('session.coOKie_path = /');
List.Add('session.coOKie_domain =');
List.Add('session.coOKie_httponly = ');
List.Add('session.serialize_handler = php');
List.Add('session.gc_divisor     = 100');
List.Add('session.bug_compat_42 = 1');
List.Add('session.bug_compat_warn = 1');
List.Add('session.referer_check =');
List.Add('session.entropy_length = 0');
List.Add('session.entropy_file =');
List.Add('session.cache_limiter = nocache');
List.Add('session.cache_expire = 180');
List.Add('session.use_trans_sid = 0');
List.Add('session.hash_function = 0');
List.Add('session.hash_bits_per_character = 4');
List.Add('url_rewriter.tags = "a=href,area=href,frame=src,input=src,form=,fieldset="');
List.Add('[MSSQL]');
List.Add('mssql.allow_persistent = On');
List.Add('mssql.max_persistent = -1');
List.Add('mssql.max_links = -1');
List.Add('mssql.min_error_severity = 10');
List.Add('mssql.min_message_severity = 10');
List.Add('mssql.compatability_mode = Off');
List.Add('[Assertion]');
List.Add('[COM]');
List.Add('[mbstring]');
List.Add('[FrontBase]');
List.Add('[gd]');
List.Add('[exif]');
List.Add('[Tidy]');
List.Add('tidy.clean_output = Off');
List.Add('[soap]');
List.Add('soap.wsdl_cache_enabled=1');
List.Add('soap.wsdl_cache_dir="/tmp"');
List.Add('soap.wsdl_cache_ttl=86400');
List.Add(';extension_dir = "/opt/artica/lib"');
List.Add(';extension=imap.so');
List.Add(';extension=mcrypt.so');
List.Add(';extension=pdo.so');
List.Add(';extension=pdo_sqlite.so');
List.Add('extension=sqlite3.so');

list.SaveToFile('/opt/artica/php/php.ini');

end;




procedure Tclass_install.PERL_ADDONS();
var
   tl:TstringList;
   i:integer;
   GLOBALINI:myconf;
   SLOGS:TLogs;
   Force:Boolean;
begin
   Force:=false;
   tl:=Tstringlist.Create;
   GLOBALINI:=myconf.Create;
   SLOGS:=Tlogs.Create;
   CONFIGURE_CPAN();
   tl.Add('/opt/artica/lib/perl/5.8.8/File/Tail.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/i486-linux-gnu-thread-multi/auto/Compress/Raw/Zlib/Zlib.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/IO/Uncompress/Base.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/IO/Uncompress/Unzip.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Net/Server.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/IO/Multiplex.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/i486-linux-gnu-thread-multi/auto/DBI/DBI.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/DBI.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/DBD/SQLite/SQLite.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/Sub/Uplevel.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Test/Exception.pm');
   tl.add('/opt/artica/lib/perl/5.8.8/Carp/Clan.pm');
   tl.add('/opt/artica/lib/perl/5.8.8/auto/Bit/Vector/Vector.so');
   tl.add('/opt/artica/lib/perl/5.8.8/auto/Date/Calc/Calc.so');
   tl.add('/opt/artica/lib/perl/5.8.8/Test/Simple.pm');
   tl.add('/opt/artica/lib/perl/5.8.8/Compress/Zlib.pm');
   tl.add('/opt/artica/lib/perl/5.8.8/HTML/Tagset.pm');
   tl.add('/opt/artica/lib/perl/5.8.8/LWP.pm');
   tl.add('/opt/artica/lib/perl/5.8.8/auto/Digest/SHA1/SHA1.so');
   tl.add('/opt/artica/lib/perl/5.8.8/auto/HTML/Parser/Parser.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/DB_File/DB_File.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Socket6/Socket6.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/IO/Socket/INET6.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Net/Ident.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Digest/HMAC.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Net/IP.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Net/DNS/DNS.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/Net/DNS/Resolver/Programmable.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/NetAddr/IP/Util/Util.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/Error.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Mail/SPF.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Net/CIDR/Lite.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Sys/Hostname/Long.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Mail/SPF/Query.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/Random/Random.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/RSA/RSA.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/Date/Format.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Test/Pod.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Mail/Address.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Mail/DKIM.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Encode/Detect/Detector/Detector.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/IP/Country.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Term/ReadKey/ReadKey.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/IO/Wrap.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Unix/Syslog/Syslog.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/MIME/Words.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Convert/TNEF.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Convert/UUlib/UUlib.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/Image/Info.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/BerkeleyDB/BerkeleyDB.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Razor2/Preproc/deHTMLxs/deHTMLxs.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/Bignum/Bignum.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/Convert/ASN1.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/GSSAPI/GSSAPI.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/Authen/SASL.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Array/Compare.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/IO/Socket/SSL.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Text/Iconv/Iconv.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/XML/NamespaceSupport.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/XML/SAX.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/XML/Filter/BufferText.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/XML/SAX/Writer.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Net/LDAP.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Test/Warn.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Net/SSLeay/SSLeay.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/DBD/mysql/mysql.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/auto/Cyrus/IMAP/IMAP.so');
   tl.Add('/opt/artica/lib/perl/5.8.8/IMAP/Admin.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Config/IniFiles.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Net/Telnet.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Jcode.pm');
   tl.Add('/opt/artica/lib/perl/5.8.8/Crypt/SmbHash.pm');
   
   if ParamStr(2)='--recompile' then Force:=true;
   if ParamStr(2)='--force' then Force:=true;


   if force=true then begin
          SLOGS.INSTALL_MODULES('APP_PERL','perl addons.......................: restart installation');
          for i:=0 to tl.Count-1 do begin
                if FileExists(tl.Strings[i]) then begin
                   SLOGS.INSTALL_MODULES('APP_PERL','perl addons.......................: Delete '+ ExtractFileName(tl.Strings[i]));
                   GLOBALINI.DeleteFile(tl.Strings[i]);
                end;
          end;
   end;

    PERL_LINUX_NET_DEV();
    PERL_HTML_PARSER();
    PERL_GENERIC_INSTALL('File-Tail','/opt/artica/lib/perl/5.8.8/File/Tail.pm');
    PERL_GENERIC_INSTALL('TermReadKey','/opt/artica/lib/perl/5.8.8/auto/Term/ReadKey/ReadKey.so');
    PERL_GENERIC_INSTALL('Compress-Raw-Zlib','/opt/artica/lib/perl/5.8.8/auto/Compress/Raw/Zlib/Zlib.so');
    PERL_GENERIC_INSTALL('IO-Compress-Base','/opt/artica/lib/perl/5.8.8/IO/Uncompress/Base.pm');
    PERL_GENERIC_INSTALL('IO-Compress-Zlib','/opt/artica/lib/perl/5.8.8/IO/Uncompress/Unzip.pm');
    PERL_GENERIC_INSTALL('Net-Server','/opt/artica/lib/perl/5.8.8/Net/Server.pm');
    PERL_GENERIC_INSTALL('IO-Multiplex','/opt/artica/lib/perl/5.8.8/IO/Multiplex.pm');
    PERL_GENERIC_INSTALL('DBI','/opt/artica/lib/perl/5.8.8/auto/DBI/DBI.so');
    PERL_GENERIC_INSTALL('Digest-SHA1','/opt/artica/lib/perl/5.8.8/auto/Digest/SHA1/SHA1.so');
    PERL_GENERIC_INSTALL('HTML-Parser','/opt/artica/lib/perl/5.8.8/auto/HTML/Parser/Parser.so');
    PERL_GENERIC_INSTALL('Socket6','/opt/artica/lib/perl/5.8.8/auto/Socket6/Socket6.so');
    PERL_GENERIC_INSTALL('IO-Socket-INET6','/opt/artica/lib/perl/5.8.8/IO/Socket/INET6.pm');
    PERL_GENERIC_INSTALL('Net-Ident','/opt/artica/lib/perl/5.8.8/Net/Ident.pm');
    PERL_GENERIC_INSTALL('Digest-HMAC','/opt/artica/lib/perl/5.8.8/Digest/HMAC.pm');
    PERL_GENERIC_INSTALL('Net-IP','/opt/artica/lib/perl/5.8.8/Net/IP.pm');
    PERL_GENERIC_INSTALL('TimeDate','/opt/artica/lib/perl/5.8.8/Date/Format.pm');
    PERL_NET_DNS();
    PERL_GENERIC_INSTALL('Net-DNS-Resolver-Programmable','/opt/artica/lib/perl/5.8.8/Net/DNS/Resolver/Programmable.pm');
    PERL_GENERIC_INSTALL('NetAddr-IP','/opt/artica/lib/perl/5.8.8/auto/NetAddr/IP/Util/Util.so');
    PERL_GENERIC_INSTALL('Net-CIDR-Lite','/opt/artica/lib/perl/5.8.8/Net/CIDR/Lite.pm');
    PERL_GENERIC_INSTALL('Error','/opt/artica/lib/perl/5.8.8/Error.pm');
    PERL_GENERIC_INSTALL('Mail-SPF','/opt/artica/lib/perl/5.8.8/Mail/SPF.pm');
    PERL_GENERIC_INSTALL('Sys-Hostname-Long','/opt/artica/lib/perl/5.8.8/Sys/Hostname/Long.pm');
    PERL_GENERIC_INSTALL('Mail-SPF-Query','/opt/artica/lib/perl/5.8.8/Mail/SPF/Query.pm');
    //PERL_CRYPT_OPENSSL();
    

    
    PERL_GENERIC_INSTALL('Test-Pod','/opt/artica/lib/perl/5.8.8/Test/Pod.pm');
    PERL_GENERIC_INSTALL('MailTools','/opt/artica/lib/perl/5.8.8/Mail/Address.pm');
    PERL_MAIL_DomainKeys();
    PERL_GENERIC_INSTALL('Mail-DKIM','/opt/artica/lib/perl/5.8.8/Mail/DKIM.pm');
    PERL_GENERIC_INSTALL('Encode-Detect','/opt/artica/lib/perl/5.8.8/auto/Encode/Detect/Detector/Detector.so');
    PERL_GENERIC_INSTALL('IP-Country','/opt/artica/lib/perl/5.8.8/IP/Country.pm');
    PERL_GENERIC_INSTALL('IO-stringy','/opt/artica/lib/perl/5.8.8/IO/Wrap.pm');
    PERL_GENERIC_INSTALL('Unix-Syslog','/opt/artica/lib/perl/5.8.8/auto/Unix/Syslog/Syslog.so');
    PERL_GENERIC_INSTALL('MIME-tools','/opt/artica/lib/perl/5.8.8/MIME/Words.pm');
    PERL_GENERIC_INSTALL('Convert-TNEF','/opt/artica/lib/perl/5.8.8/Convert/TNEF.pm');
    PERL_GENERIC_INSTALL('Convert-UUlib','/opt/artica/lib/perl/5.8.8/auto/Convert/UUlib/UUlib.so');
    PERL_GENERIC_INSTALL('Archive-Zip','/opt/artica/lib/perl/5.8.8/Archive/Zip.pm');
    PERL_GENERIC_INSTALL('Image-Info','/opt/artica/lib/perl/5.8.8/Image/Info.pm');
    PERL_GENERIC_INSTALL('Array-Compare','/opt/artica/lib/perl/5.8.8/Array/Compare.pm');
    PERL_GENERIC_INSTALL('Tree-DAG_Node','/opt/artica/lib/perl/5.8.8/Tree/DAG_Node.pm');
    PERL_GENERIC_INSTALL('Test-Warn','/opt/artica/lib/perl/5.8.8/Test/Warn.pm');


    PERL_DBD_FILE();
    PERL_COMPRESS_ZLIB();
    PERL_HTML_TAGSET();
    PERL_URI();
    PERL_LIBWWW();
    PERL_GENERIC_INSTALL('DBI','/opt/artica/lib/perl/5.8.8/DBI.pm');
    PERL_BERKELEYDB();
    PERL_DBD_SQLITE();
    PERL_GENERIC_INSTALL('razor-agents','/opt/artica/lib/perl/5.8.8/auto/Razor2/Preproc/deHTMLxs/deHTMLxs.so');
    RRD_INSTALL();
    
    PERL_LDAP();

    
    PERL_GENERIC_INSTALL('Sub-Uplevel','/opt/artica/lib/perl/5.8.8/Sub/Uplevel.pm');
    if FileExists('/opt/artica/lib/perl/5.8.8/Sub/Uplevel.pm') then begin
    PERL_GENERIC_INSTALL('Test-Exception','/opt/artica/lib/perl/5.8.8/Test/Exception.pm');
       if FileExists('/opt/artica/lib/perl/5.8.8/Test/Exception.pm') then begin
          PERL_GENERIC_INSTALL('Test-Simple','/opt/artica/lib/perl/5.8.8/Test/Simple.pm');
          PERL_GENERIC_INSTALL('Carp-Clan','/opt/artica/lib/perl/5.8.8/Carp/Clan.pm');
          if FileExists('/opt/artica/lib/perl/5.8.8/Carp/Clan.pm') then begin
             PERL_GENERIC_INSTALL('Bit-Vector','/opt/artica/lib/perl/5.8.8/auto/Bit/Vector/Vector.so');
             if FileExists('/opt/artica/lib/perl/5.8.8/auto/Bit/Vector/Vector.so') then begin
                PERL_GENERIC_INSTALL('Date-Calc','/opt/artica/lib/perl/5.8.8/auto/Date/Calc/Calc.so');
             end;
          end;
       end;
    end;
    

   KERBEROS_INSTALL();
   PERL_DBD_MYSQL();
   PERL_CYRUS_IMAP();
   PERL_GENERIC_INSTALL('IMAP-Admin','/opt/artica/lib/perl/5.8.8/IMAP/Admin.pm');
   PERL_GENERIC_INSTALL('Config-IniFiles','/opt/artica/lib/perl/5.8.8/Config/IniFiles.pm');
   PERL_GENERIC_INSTALL('Net-Telnet','/opt/artica/lib/perl/5.8.8/Net/Telnet.pm');
   PERL_GENERIC_INSTALL('Jcode','/opt/artica/lib/perl/5.8.8/Jcode.pm');
   
   PERL_GENERIC_INSTALL('Unicode-Map','/opt/artica/lib/perl/5.8.8/auto/Unicode/Map/Map.so');
   PERL_GENERIC_INSTALL('Unicode-String','/opt/artica/lib/perl/5.8.8/auto/Unicode/String/String.so');
   PERL_GENERIC_INSTALL('Unicode-Map8','/opt/artica/lib/perl/5.8.8/auto/Unicode/Map8/Map8.so');
   PERL_GENERIC_INSTALL('Unicode-MapUTF8','/opt/artica/lib/perl/5.8.8/Unicode/MapUTF8.pm');
   PERL_GENERIC_INSTALL('Crypt-SmbHash','/opt/artica/lib/perl/5.8.8/Crypt/SmbHash.pm');
   
   





   GLOBALINI.PATCHING_PERL_TO_ARTICA('/opt/artica/lib/perl/5.8.8');
   

   
    
    
              ///opt/artica/bin/perl Makefile.PL DEFINE="-L/opt/artica/lib" INC="-I/opt/artica/include"
    

end;
//##############################################################################
procedure Tclass_install.PATCHING_Config_heavy_pl();
var
   l:Tstringlist;
   RegExpr:TRegExpr;
   i:integer;
begin
    if not FileExists('/opt/artica/lib/perl/5.8.8/Config_heavy.pl') then begin
       writeln('hug !!!! could not find /opt/artica/lib/perl/5.8.8/Config_heavy.pl');
       readln();
       exit();
    end;
    
    fpsystem('/bin/chmod 644 /opt/artica/lib/perl/5.8.8/Config_heavy.pl');
    l:=TstringList.Create;
    l.LoadFromFile('/opt/artica/lib/perl/5.8.8/Config_heavy.pl');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='libpth=.+';

    for i:=0 to l.Count-1 do begin
            if pos('-I/usr/local/include',l.Strings[i])>0 then begin
             if pos('-I/opt/artica/include',l.Strings[i])=0 then begin

                   l.Strings[i]:=AnsiReplaceText(l.Strings[i],'-I/usr/local/include','-I/usr/local/include -I/opt/artica/include/dbi -I/opt/artica/db/include -I/opt/artica/include/sasl -I/opt/artica/include/openssl -I/opt/artica/include/libxml2');
                   writeln('Patching line ',i, ' ',l.Strings[i]);
             end;
            end;
            
            
            if pos('/usr/local/lib',l.Strings[i])>0 then begin

                l.Strings[i]:=AnsiReplaceText(l.Strings[i],'/usr/local/lib','/opt/artica/lib');
                writeln('Patching line ',i, ' ',l.Strings[i]);
            end;
            

            RegExpr.Expression:='locincpth=.*';
           if RegExpr.Exec(l.Strings[i]) then begin
              writeln('Patching: locincpth');
              l.Strings[i]:='locincpth=''/usr/local/include /opt/local/include /usr/gnu/include /opt/gnu/include /opt/artica/include /opt/artica/include/openssl''';
           end;

            
            
            RegExpr.Expression:='libspath=.*';
            if RegExpr.Exec(l.Strings[i]) then begin
               writeln('Patching: libspath');
               l.Strings[i]:='libspath=''/opt/artica/lib /lib /usr/lib /usr/local/lib''';
            end;
            
            RegExpr.Expression:='libpth=.*';
            if RegExpr.Exec(l.Strings[i]) then begin
               writeln('Patching: libpth');
               l.Strings[i]:='libpth=''/usr/local/lib /lib /usr/lib /opt/artica/lib''';
            end;

            RegExpr.Expression:='libsdirs=.*';
            if RegExpr.Exec(l.Strings[i]) then begin
               writeln('Patching: libsdirs');
               l.Strings[i]:='libsdirs=''/usr/local/lib /lib /usr/lib /opt/artica/lib''';
            end;


            
    end;
    
   l.SaveToFile('/opt/artica/lib/perl/5.8.8/Config_heavy.pl');
   l.free;

end;
//##############################################################################




procedure Tclass_install.PERL_LDAP();
var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;
begin
    LOG_PERL:=Tlogs.Create;
    KERBEROS_INSTALL();

if not FileExists('/opt/artica/lib/libkdb5.so') then begin
    LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................:Failed unable to stat /opt/artica/lib/libkdb5.so');
    readln();
    exit;
end;

if not FileExists('/opt/artica/lib/perl/5.8.8/auto/GSSAPI/GSSAPI.so') then begin
    LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: installing GSSAPI');
    source:=COMPILE_GENERIC_APPS('APP_PERL','GSSAPI','GSSAPI');
    if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' &&  /opt/artica/bin/perl Makefile.PL --gssapiimpl=/opt/artica && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
    end;
end;

if not FileExists('/opt/artica/lib/perl/5.8.8/auto/GSSAPI/GSSAPI.so') then begin
    LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile GSSAPI');
    readln();
    exit;
end;
   LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP....................:OK GSSAPI');
   PERL_GENERIC_INSTALL('Authen-SASL','/opt/artica/lib/perl/5.8.8/Authen/SASL.pm');

if not FileExists('/opt/artica/lib/perl/5.8.8/Authen/SASL.pm') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile Authen::SASL');
     readln();
     exit;
end;

if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Text/Iconv/Iconv.so') then begin
        source:=COMPILE_GENERIC_APPS('APP_PERL','Text-Iconv','Text-Iconv');
        if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' &&  /opt/artica/bin/perl Makefile.PL LIBS="-L/opt/artica/lib" INC="-I/opt/artica/include" && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
        end;
end;

if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Text/Iconv/Iconv.so') then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile Text::Iconv');
        readln();
        exit;
end;

LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP....................:OK Text::Iconv ');

PERL_GENERIC_INSTALL('XML-NamespaceSupport','/opt/artica/lib/perl/5.8.8/XML/NamespaceSupport.pm');
if not FileExists('/opt/artica/lib/perl/5.8.8/XML/NamespaceSupport.pm') then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile XML::NamespaceSupport');
        readln();
        exit;
end;
LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP....................:OK XML::NamespaceSupport');

PERL_GENERIC_INSTALL('XML-SAX','/opt/artica/lib/perl/5.8.8/XML/SAX.pm');
if not FileExists('/opt/artica/lib/perl/5.8.8/XML/SAX.pm') then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile XML::SAX');
        readln();
        exit;
end;
LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP....................:OK XML::SAX');

PERL_GENERIC_INSTALL('XML-Filter-BufferText','/opt/artica/lib/perl/5.8.8/XML/Filter/BufferText.pm');
if not FileExists('/opt/artica/lib/perl/5.8.8/XML/Filter/BufferText.pm') then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile XML::Filter::BufferText');
        readln();
        exit;
end;
LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP....................:OK XML::Filter::BufferText');

PERL_GENERIC_INSTALL('XML-SAX-Writer','/opt/artica/lib/perl/5.8.8/XML/SAX/Writer.pm');
if not FileExists('/opt/artica/lib/perl/5.8.8/XML/SAX/Writer.pm') then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile XML::SAX::Writer');
        readln();
        exit;
end;
LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP....................:OK XML::SAX::Writer');

PERL_GENERIC_INSTALL('Convert-ASN1','/opt/artica/lib/perl/5.8.8/Convert/ASN1.pm');

if not FileExists('/opt/artica/lib/perl/5.8.8/Net/LDAP.pm') then begin
        source:=COMPILE_GENERIC_APPS('APP_PERL','perl-ldap','perl-ldap');
        if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' &&  ./install-nomake -s /opt/artica/lib/perl/5.8.8';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
        end;
end;



if not FileExists('/opt/artica/lib/perl/5.8.8/Net/LDAP.pm') then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Perl::LDAP...................: Failed compile Net::LDAP');
        readln();
        exit;
end;


end;

procedure Tclass_install.PERL_CRYPT_OPENSSL();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;

begin

  LOG_PERL:=Tlogs.Create;
  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/Random/Random.so') then begin
      source:=COMPILE_GENERIC_APPS('APP_PERL','Crypt-OpenSSL-Random','Crypt-OpenSSL-Random');
      if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL DEFINE="-L/opt/artica/lib" INC="-I/opt/artica/include" && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
      end;
  end;

  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/Random/Random.so') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','Crypt:OpenSSL:Random.........:Failed to install perl module...');
      writeln();
      exit();
  end;
  LOG_PERL.INSTALL_MODULES('APP_PERL','Crypt:OpenSSL:Random..........:OK');
  
  
if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/RSA/RSA.so') then begin
      source:=COMPILE_GENERIC_APPS('APP_PERL','Crypt-OpenSSL-RSA','Crypt-OpenSSL-RSA');
      if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL DEFINE="-L/opt/artica/lib" INC="-I/opt/artica/include" && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
      end;
  end;
  
 if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/RSA/RSA.so') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','Crypt:OpenSSL:RSA............:Failed to install perl module...');
      writeln();
      exit();
  end;
  
  
if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/Bignum/Bignum.so') then begin
      source:=COMPILE_GENERIC_APPS('APP_PERL','Crypt-OpenSSL-Bignum','Crypt-OpenSSL-Bignum');
      if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL LIBS="-L/opt/artica/lib" INC="-I/opt/artica/include" && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
      end;
  end;

  if FileExists('/opt/artica/lib/perl/5.8.8/auto/Crypt/OpenSSL/Bignum/Bignum.so') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Crypt:OpenSSL:Bignum..........:OK');
  end else begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Crypt:OpenSSL:Bignum.........:Failed to compile...');
     readln();
  end;
  
  PERL_GENERIC_INSTALL('IO-Socket-SSL','/opt/artica/lib/perl/5.8.8/IO/Socket/SSL.pm');
  

  

end;
//##############################################################################
procedure Tclass_install.CONFIGURE_CPAN();
var
l:TstringList;
begin
l:=TstringList.Create;
l.Add('$CPAN::Config = {');
l.Add('  "auto_commit" => q[0],');
l.Add('  "build_cache" => q[100],');
l.Add('  "build_dir" => q[/opt/artica/perl/cpan/build],');
l.Add('  "cache_metadata" => q[1],');
l.Add('  "commandnumber_in_prompt" => q[1],');
l.Add('  "cpan_home" => q[/opt/artica/perl/cpan],');
l.Add('  "ftp_passive" => q[1],');
l.Add('  "ftp_proxy" => q[],');
l.Add('  "http_proxy" => q[],');
l.Add('  "inactivity_timeout" => q[0],');
l.Add('  "index_expire" => q[1],');
l.Add('  "inhibit_startup_message" => q[0],');
l.Add('  "keep_source_where" => q[/opt/artica/install/sources/cpan/sources],');
l.Add('  "make_arg" => q[],');
l.Add('  "make_install_arg" => q[],');
l.Add('  "make_install_make_command" => q[],');
l.Add('  "makepl_arg" => q[PREFIX=/opt/artica],');
l.Add('  "mbuild_arg" => q[--extra_linker_flags -L/opt/artica/lib],');
l.Add('  "mbuild_install_arg" => q[],');
l.Add('  "mbuild_install_build_command" => q[./Build],');
l.Add('  "mbuildpl_arg" => q[],');
l.Add('  "no_proxy" => q[],');
l.Add('  "prerequisites_policy" => q[ask],');
l.Add('  "scan_cache" => q[atstart],');
l.Add('  "show_upload_date" => q[0],');
l.Add('  "term_ornaments" => q[1],');
l.Add('  "urllist" => [q[ftp://ftp.inria.fr/pub/CPAN/], q[ftp://ftp.oleane.net/pub/CPAN/], q[ftp://ftp.u-strasbg.fr/CPAN], q[http://cpan.enstimac.fr/]],');
l.Add('  "use_sqlite" => q[0],');
l.Add('};');
l.Add('1;');
l.Add('__END__');
l.SaveToFile('/opt/artica/lib/perl/5.8.8/CPAN/Config.pm');


end;


procedure Tclass_install.PERL_NET_DNS();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;

begin

  LOG_PERL:=Tlogs.Create;
  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Net/DNS/DNS.so') then begin
      source:=COMPILE_GENERIC_APPS('APP_PERL','Net-DNS','Net-DNS');
      if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' && echo "n"|/opt/artica/bin/perl Makefile.PL && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
      end;
  end;

  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Net/DNS/DNS.so') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','Net:DNS......................:Failed to install perl module...');
      writeln();
      exit();
  end;

  LOG_PERL.INSTALL_MODULES('APP_PERL','Net:DNS.......................:OK');

end;
//##############################################################################
procedure Tclass_install.PERL_MAIL_DomainKeys();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;

begin

  LOG_PERL:=Tlogs.Create;
  if not FileExists('/opt/artica/lib/perl/5.8.8/Mail/DomainKeys.pm') then begin
      source:=COMPILE_GENERIC_APPS('APP_PERL','Mail-DomainKeys','Mail-DomainKeys');
      if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' && echo "n"|/opt/artica/bin/perl Makefile.PL && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
      end;
  end;

  if not FileExists('/opt/artica/lib/perl/5.8.8/Mail/DomainKeys.pm') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','Mail:DomainKeys..............:Failed to install perl module...');
      writeln();
      exit();
  end;

  LOG_PERL.INSTALL_MODULES('APP_PERL','Mail:DomainKeys...............: OK');

end;
//##############################################################################
procedure Tclass_install.PERL_CYRUS_IMAP();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;
      l:TstringList;
      RegExpr:TRegExpr;
      MakePath:string;
      i:integer;
      libcyrus:string;
      libcyrus_min:string;

begin

  LOG_PERL:=Tlogs.Create;
   if FileExists('/opt/artica/cyrus/lib/libcyrus.a') then begin
      libcyrus:='/opt/artica/cyrus/lib/libcyrus.a';
      libcyrus_min:='/opt/artica/cyrus/lib/libcyrus_min.a';
   end;
      
   if FileExists('/opt/artica/lib/libcyrus.a') then begin
      libcyrus:='/opt/artica/lib/libcyrus.a';
      libcyrus_min:='/opt/artica/lib/libcyrus_min.a';
   end;
  
  
  if not FileExists(libcyrus) then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: could not stat libcyrus.a, Cyrus-imap is not installed yet');
     exit;
  end;

  if Paramstr(2)<>'--force' then begin
  if FileExists('/opt/artica/lib/perl/5.8.8/auto/Cyrus/IMAP/IMAP.so') then begin
       LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: OK');
       exit;
  end;
  end;




      source:=COMPILE_GENERIC_APPS('APP_PERL','perl-cyrus-imap','perl-cyrus-imap');
      if not DirectoryExists(source) then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: FAILED Extract tarball');
        readln();
        exit;
      end;
      MakePath:=source +'/Makefile.PL';
       
     if not FileExists(MakePath) then begin
        LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: FAILED stat '+MakePath);
        readln();
        exit;
      end;
       
        l:=TstringList.Create;
        RegExpr:=TRegExpr.Create;

        l.LoadFromFile(MakePath);
        for i:=0 to l.Count-1 do begin
            RegExpr.Expression:='''MYEXTLIB''\s+=>';
             if RegExpr.Exec(l.Strings[i]) then begin
                LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: PATCHING MYEXTLIB in '+MakePath);
                l.Strings[i]:='''MYEXTLIB''  => '''+libcyrus +' ' + libcyrus_min+ ''',';
             end;
             
             
            RegExpr.Expression:='''INC''\s+=>';
            if RegExpr.Exec(l.Strings[i]) then begin
               LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: PATCHING INC in '+MakePath);
               l.Strings[i]:='''INC''  => ''-I/opt/artica/lib -I' +source+'/lib -I/opt/artica/include -I/opt/artica/cyrus/include -I/opt/artica/cyrus/lib'',';
            end;
            

            

            
        end;

            l.SaveToFile(MakePath);
            
            cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL';
            LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
            fpsystem(cmd);

            MakePath:=source +'/Makefile';
            l.LoadFromFile(MakePath);
            for i:=0 to l.Count-1 do begin
            RegExpr.Expression:='^CCFLAGS';
               if RegExpr.Exec(l.Strings[i]) then begin
                   LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: PATCHING CCFLAGS in '+MakePath);
                   l.Strings[i]:='CCFLAGS = -D_REENTRANT -D_GNU_SOURCE -DDEBIAN -fno-strict-aliasing -pipe -I/opt/artica/include ';
                   l.Strings[i]:=l.Strings[i] + '-I/opt/artica/include/sasl2 -I/opt/artica/include -I/opt/artica/db/include -I/opt/artica/cyrus/include -I/opt/artica/db -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64';
               end;
               
               RegExpr.Expression:='^LD_RUN_PATH';
               if RegExpr.Exec(l.Strings[i]) then begin
                   LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: PATCHING LD_RUN_PATH in '+MakePath);
                   l.Strings[i]:='LD_RUN_PATH = /usr/lib:/lib:/opt/artica/lib:/opt/artica/cyrus/lib';
               end;
               
               RegExpr.Expression:='^LDDLFLAGS';
               if RegExpr.Exec(l.Strings[i]) then begin
                   LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: PATCHING LDDLFLAGS in '+MakePath);
                   l.Strings[i]:='LDDLFLAGS = -shared -O2 -L/opt/artica/lib -L/opt/artica/cyrus/lib';
               end;
               
               RegExpr.Expression:='^LDFLAGS';
               if RegExpr.Exec(l.Strings[i]) then begin
                   LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: PATCHING LDFLAGS in '+MakePath);
                   l.Strings[i]:='LDFLAGS = -L/opt/artica/lib -L/opt/artica/cyrus/lib';
               end;
            end;

l.SaveToFile(MakePath);




      cmd:='cd ' + source + ' && make && make install';
      LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
      fpsystem(cmd);


  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/Cyrus/IMAP/IMAP.so') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','Cyrus::IMAP...................: FAILED to install Cyrus::IMAP perl module...');
      writeln();
      exit();
  end;
      fpsystem('chmod 777 '+source + '/clean-cyrus.sh');
      fpsystem(source + '/clean-cyrus.sh');
      
      
      l.Clear;
      l.Add('#/bin/sh');
      l.Add('case "x$BASH_VERSION" in');
      l.Add('x) exec /opt/artica/bin/perl -MCyrus::IMAP::Shell -e shell -- ${1+"$@"} ;;');
      l.Add('*) exec /opt/artica/bin/perl -MCyrus::IMAP::Shell -e shell -- "$@" ;;');
      l.Add('esac');
      l.Add('echo "$0: how did I get here?" >&2');
      l.Add('exit 1');
      l.SaveToFile('/opt/artica/bin/cyradm');
      fpsystem('/bin/chmod 777 /opt/artica/bin/cyradm');


      
   l.Free;

end;
//##############################################################################

procedure Tclass_install.PERL_DBD_FILE();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;
      l:TstringList;

begin

  LOG_PERL:=Tlogs.Create;
  
  if Paramstr(2)<>'--force' then begin
  if FileExists('/opt/artica/lib/perl/5.8.8/auto/DB_File/DB_File.so') then begin
       LOG_PERL.INSTALL_MODULES('APP_PERL','DBD:File......................:OK');
       exit;
  end;
  end;
  

  

      source:=COMPILE_GENERIC_APPS('APP_PERL','DB_File','DB_File');
      if DirectoryExists(source) then begin
      
        l:=TstringList.Create;
        l.Add('INCLUDE  = /opt/artica/db/include');
        l.Add('LIB	= /opt/artica/db/lib');
        l.Add('PREFIX	=	size_t');
        l.Add('HASH	=	u_int32_t');
        l.SaveToFile(source + '/config.in');
        LOG_PERL.INSTALL_MODULES('APP_PERL','DBD:File......................:Saving '+source + '/config.in');
      
          cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
      end;


  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/DB_File/DB_File.so') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','DBD:File......................:Failed to install DBI perl module...');
      writeln();
      exit();
  end;



end;
//##############################################################################
procedure Tclass_install.PERL_BERKELEYDB();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;
      l:TstringList;

begin

  LOG_PERL:=Tlogs.Create;

  if Paramstr(2)<>'--force' then begin
  if FileExists('/opt/artica/lib/perl/5.8.8/auto/BerkeleyDB/BerkeleyDB.so') then begin
       LOG_PERL.INSTALL_MODULES('APP_PERL','PERL:BerkeleyDB...............:OK');
       exit;
  end;
  end;




      source:=COMPILE_GENERIC_APPS('APP_PERL','BerkeleyDB','BerkeleyDB');
      if DirectoryExists(source) then begin

        l:=TstringList.Create;
        l.Add('INCLUDE  = /opt/artica/db/include');
        l.Add('LIB	= /opt/artica/db/lib');
        l.SaveToFile(source + '/config.in');
        LOG_PERL.INSTALL_MODULES('APP_PERL','PERL:BerkeleyDB...............:Saving '+source + '/config.in');

          cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
          fpsystem(cmd);
      end;


  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/BerkeleyDB/BerkeleyDB.so') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','PERL:BerkeleyDB...............:Failed to compile');
      writeln();
      exit();
  end;



end;
//##############################################################################
procedure tclass_install.SPAMASSASSIN_INSTALL();
var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;
begin
  LOG_PERL:=TLOgs.Create;
  if Paramstr(2)<>'--force' then begin
  if FileExists('/opt/artica/bin/spamassassin') then begin
       LOG_PERL.INSTALL_MODULES('APP_ARTICA','spamassassin..................:OK');
       exit;
  end;
  end;
   source:=COMPILE_GENERIC_APPS('APP_ARTICA','Mail-SpamAssassin','Mail-SpamAssassin');

if DirectoryExists(source) then begin
          cmd:='cd ' + source + ' && /opt/artica/bin/perl Makefile.PL PREFIX=/opt/artica ';
          cmd:=cmd + 'PERL_BIN=/opt/artica/bin/perl SYSCONFDIR=/opt/artica/etc ';
          cmd:=cmd + ' LOCALRULESDIR=/opt/artica/etc/spamassassin ';
          cmd:=cmd + 'CONTACT_ADDRESS=root@localhost.localdomain LOCALSTATEDIR=/opt/artica/spamassassin DEFRULESDIR=/opt/artica/spamassassin/data && make && make install';
          LOG_PERL.INSTALL_MODULES('APP_ARTICA',cmd);
          fpsystem(cmd);
      end;
      
  if FileExists('/opt/artica/bin/spamassassin') then begin
       LOG_PERL.INSTALL_MODULES('APP_ARTICA','spamassassin..................:OK');
       exit;
  end;

end;
//##############################################################################

procedure Tclass_install.PERL_DBD_SQLITE();

var
      LOG_PERL:TLogs;
      source:string;

begin

  LOG_PERL:=Tlogs.Create;
  if not FileExists('/opt/artica/lib/perl/5.8.8/DBI.pm') then begin
      source:=COMPILE_GENERIC_APPS('APP_PERL','DBI','DBI');
      if DirectoryExists(source) then begin
          LOG_PERL.INSTALL_MODULES('APP_PERL','cd ' + source + ' && /opt/artica/bin/perl Makefile.PL && make && make install');
          fpsystem('cd ' + source + ' && /opt/artica/bin/perl Makefile.PL && make && make install');
      end;
  end;
  
  if not FileExists('/opt/artica/lib/perl/5.8.8/DBI.pm') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','DBD:SQLite....................:Failed to install DBI perl module...');
      writeln();
      exit();
  end;
      
  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/DBD/SQLite/SQLite.so') then begin
      source:=COMPILE_GENERIC_APPS('APP_PERL','DBD-SQLite','DBD-SQLite');
      if DirectoryExists(source) then begin
         LOG_PERL.INSTALL_MODULES('APP_PERL','cd ' + source + ' && /opt/artica/bin/perl Makefile.PL SQLITE_LOCATION=/opt/artica && make && make install');
          fpsystem('cd ' + source + ' && /opt/artica/bin/perl Makefile.PL SQLITE_LOCATION=/opt/artica && make && make install');
      end;
  end;
  if not FileExists('/opt/artica/lib/perl/5.8.8/auto/DBD/SQLite/SQLite.so') then begin
    LOG_PERL.INSTALL_MODULES('APP_PERL','DBD:SQLite....................:Failed to install...');
    exit;
  end;
  
   LOG_PERL.INSTALL_MODULES('APP_PERL','DBD:SQLite....................:OK');

end;
//##############################################################################
procedure Tclass_install.KERBEROS_INSTALL();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;

begin

  LOG_PERL:=Tlogs.Create;
  if FileExists('/opt/artica/lib/libkdb5.so') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Kerberos 5....................:OK');
     exit;
  end;
  
  if not FileExists('/opt/artica/lib/libncurses.a') then LIBNCURSES_INSTALL();

  if not FileExists('/opt/artica/lib/libncurses.a') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Kerberos 5....................: Failed to stat libncurses');
     readln();
     exit;
  end;
  
  
      source:=COMPILE_GENERIC_APPS('APP_ARTICA','krb5','krb5');
      if not DirectoryExists(source) then begin
             LOG_PERL.INSTALL_MODULES('APP_PERL','Kerberos 5....................: Unable to extract sources');
             readln();
             exit;
      end;
      
      cmd:='cd ' + source + '/src && ';
      cmd:=cmd + ' ./configure --prefix=/opt/artica --without-krb4 --without-system-db --without-ldap --without-tcl --without-system-ss --without-edirectory ';
      cmd:=cmd+' ' + LD_LIBRARY_PATH + ' ' + CPPFLAGS;
      cmd:=cmd + ' && make && make install ';

      LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
      fpsystem(cmd);


 if FileExists('/opt/artica/lib/libkdb5.so') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Kerberos 5....................:OK');
     exit;
  end else begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','Kerberos 5....................: Unable to compile sources');
      readln();
      exit;
  end;
              

end;
//##############################################################################
procedure Tclass_install.LIBCAP_INSTALL();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;

begin
LOG_PERL:=Tlogs.Create;
if FileExists('/opt/artica/include/pcap.h') then begin
   if FileExists('/opt/artica/lib/libpcap.a') then begin
    LOG_PERL.INSTALL_MODULES('APP_PERL','libpcap.......................:OK');
    exit;
  end;
end;
  source:=COMPILE_GENERIC_APPS('APP_ARTICA','libpcap','libpcap');
 if not DirectoryExists(source) then begin
             LOG_PERL.INSTALL_MODULES('APP_PERL','libpcap.......................:Failed extract sources...');
             readln();
             exit;
      end;

      cmd:='cd ' + source ;
      cmd:=cmd + ' && ./configure --prefix=/opt/artica ';
      cmd:=cmd + ' && make && make install ';

  LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
  fpsystem(cmd);
  
  
 if FileExists('/opt/artica/lib/libpcap.a') then begin
    LOG_PERL.INSTALL_MODULES('APP_PERL','libpcap.......................:OK');
    exit;
  end else begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','libpcap.......................:Unable to compile libpcap');
      readln();
      
  end;
  
end;

//##############################################################################
procedure Tclass_install.POF_INSTALL();

var
      LOG_PERL:TLogs;
      source:string;
      cmd:string;

begin

  LOG_PERL:=Tlogs.Create;
  LIBCAP_INSTALL();
  if not FileExists('/opt/artica/include/pcap.h') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','p0f..........................:Failed to stat libcap');
     readln();
     exit;
  end;
  
  
  if FileExists('/opt/artica/sbin/p0f') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','p0f...........................:OK');
     exit;
  end;
  
      source:=COMPILE_GENERIC_APPS('APP_ARTICA','p0f','p0f');
      if not DirectoryExists(source) then begin
             LOG_PERL.INSTALL_MODULES('APP_PERL','p0f..........................:Failed to extract sources');
             readln();
             exit;
      end;
      forcedirectories('/usr/man/man1');
      forcedirectories('/usr/local/man/man1');
      cmd:='cd ' + source + ' && make && make install';


      LOG_PERL.INSTALL_MODULES('APP_PERL',cmd);
      fpsystem(cmd);


 if FileExists('/opt/artica/sbin/p0f') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','p0f...........................:OK');
     exit;
 end;

      LOG_PERL.INSTALL_MODULES('APP_PERL','p0f..........................:Failed');
      readln();
      exit;

end;
//##############################################################################

procedure Tclass_install.PERL_UPGRADE();
var
   globalini:myconf;
   source:string;
   compile_string:string;
   LOG_PERL:TLogs;
begin


   LOG_PERL:=TLogs.Create;
   globalini:=Myconf.Create;
if ParamStr(2)<>'--force' then begin
  if FileExists('/opt/artica/bin/perl') then begin
      LOG_PERL.INSTALL_MODULES('APP_PERL','perl checking.................:OK');
      PERL_ADDONS();
      exit;
  end;
end;

   source:=COMPILE_GENERIC_APPS('APP_PERL','perl','perl');


   
   compile_string:='cd ' + source + ' && rm -f config.sh Policy.sh && sh Configure -Dusethreads -Duselargefiles -Dccflags=-DDEBIAN -Dcccdlflags=-fPIC -Darchname=i486-linux-gnu ';
   compile_string:=compile_string + '-Dprefix=/opt/artica -Dprivlib=/opt/artica/lib/perl/5.8.8 -Darchlib=/opt/artica/lib/perl/5.8.8 ';
   compile_string:=compile_string + ' -Dvendorprefix=/opt/artica -Dvendorlib=/opt/artica/lib/perl/5.8.8';
   compile_string:=compile_string + ' -Dvendorarch=/opt/artica/lib/perl/5.8.8 ';
   compile_string:=compile_string + ' -Dsiteprefix=/opt/artica';
   compile_string:=compile_string + ' -Dsitelib=/opt/artica/lib/perl/5.8.8';
   compile_string:=compile_string + ' -Dsitearch=/opt/artica/lib/perl/5.8.8';
   compile_string:=compile_string + ' -Dman1dir=/opt/artica/man';
   compile_string:=compile_string + ' -Dman3dir=/opt/artica/man/man3';
   compile_string:=compile_string + ' -Dsiteman1dir=/usr/local/man/man1';
   compile_string:=compile_string + ' -Dsiteman3dir=/usr/local/man/man3';
   compile_string:=compile_string + ' -Dman1ext=1 -Dman3ext=3perl';
   compile_string:=compile_string + ' -Dpager=/usr/bin/sensible-pager';
   compile_string:=compile_string + ' -Uafs -Ud_csh -Uusesfio -Uusenm -Duseshrplib -Dd_dosuid -des';
   compile_string:=compile_string + ' && make && make install';
   LOG_PERL.INSTALL_MODULES('APP_PERL','Checking source in ' +source);
   LOG_PERL.INSTALL_MODULES('APP_PERL',compile_string);
   
   if Not DirectoryExists(source) then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','Error while extracting sources (needed in "' + source + '")');
     LOG_PERL.INSTALL_MODULES('APP_PERL','Downloading failed of perl sources');
     LOG_PERL.INSTALL_MODULES('APP_PERL','You can try get perl 5.8.8 yourself and execute the following command :');
     LOG_PERL.INSTALL_MODULES('APP_PERL',compile_string);
     readln();
     exit();
   end;
   
   
   fpsystem(compile_string);
   LOG_PERL.INSTALL_MODULES('APP_PERL','perl checking.................:done version(' + globalini.PERL_VERSION() + ')');
   
 if not FileExists('/opt/artica/bin/perl') then begin
     LOG_PERL.INSTALL_MODULES('APP_PERL','perl checking.................:Failed');
     LOG_PERL.INSTALL_MODULES('APP_PERL','Error while compiling sources (needed in "' + source + '")');
     LOG_PERL.INSTALL_MODULES('APP_PERL','Downloading failed of perl sources');
     LOG_PERL.INSTALL_MODULES('APP_PERL','You can try get perl 5.8.8/5.10 yourself and execute the following command :');
     LOG_PERL.INSTALL_MODULES('APP_PERL',compile_string);
     readln();
     exit();
   end;
   fpsystem('/bin/rm -rf /opt/artica/man');
    PERL_ADDONS();
end;
//##############################################################################

procedure Tclass_install.INSTALL_HOTWAYD();
var
   suffix_command_line   :string;
   updater               :string;
   prefix_command_line   :string;
   repos                 :string;
   com                   :string;
   source                :string;
   debian                :TDebian;
   LOG                   :TLogs;
   D                     :boolean;
begin

     LOG:=Tlogs.Create;
     D:=GLOBALINI.COMMANDLINE_PARAMETERS('debug');
     
     if not FileExists(GLOBALINI.XINETD_BIN()) then begin
       LOG.INSTALL_MODULES('hotwayd','Unable to stat binary of xinted, install it by the repository manager');
       suffix_command_line:=GLOBALINI.LINUX_REPOSITORIES_INFOS('suffix_command_line');
       prefix_command_line:=GLOBALINI.LINUX_REPOSITORIES_INFOS('prefix_command_line');
       updater:=GLOBALINI.LINUX_REPOSITORIES_INFOS('updater');
       if D then LOG.INSTALL_MODULES('hotwayd','suffix_command_line ->' + suffix_command_line);
       if D then LOG.INSTALL_MODULES('hotwayd','prefix_command_line ->' + prefix_command_line);
       if D then LOG.INSTALL_MODULES('hotwayd','updater             ->' + updater);
       
       
       debian:=tdebian.Create();
       repos:=trim(debian.AnalyseRequiredPackages('xinetd'));
       if length(repos)>0 then begin
              com:=updater +' ' + prefix_command_line+ ' ' + repos + ' '+ suffix_command_line;
              LOG.INSTALL_MODULES('hotwayd',com);
              fpsystem(com);
       end;
     end;

     if not FileExists(GLOBALINI.XINETD_BIN()) then begin
        LOG.INSTALL_MODULES('APP_HOTWAYD','Failed to install xinetd..aborting');
        exit;
     end;
     
     if FileExists('/opt/artica/sbin/hotwayd') then begin
         log.INSTALL_MODULES('APP_HOTWAYD','hotwayd.......................:OK');
         CONFIGURE_HOTWAYD();
         exit;
     end;
     
     source:=COMPILE_GENERIC_APPS('APP_HOTWAYD','hotwayd','hotwayd');
     if not DirectoryExists(source) then begin
        LOG.INSTALL_MODULES('APP_HOTWAYD','Failed to install from source ' + source);
     end;
     if not FileExists('/usr/local/bin/xml2-config') then fpsystem('/bin/ln -s /opt/artica/bin/xml2-config /usr/local/bin/xml2-config');
     if not FileExists('/usr/lib/libiconv.so.2') then fpsystem('/bin/ln -s /opt/artica/lib/libiconv.so.2 /usr/lib/libiconv.so.2');
     
     com:='cd ' + source + ' && ./configure --prefix=/opt/artica && make && make install';
     LOG.INSTALL_MODULES('APP_HOTWAYD',com);
     fpsystem(com);
     
    if FileExists('/opt/artica/sbin/hotwayd') then begin
         log.INSTALL_MODULES('APP_HOTWAYD','hotwayd.......................:OK');
         exit;
     end else begin
      log.INSTALL_MODULES('APP_HOTWAYD','hotwayd.......................:Failed');
     
    end;
end;
//##############################################################################
procedure Tclass_install.CONFIGURE_HOTWAYD();
var
   tmpf                  :TStringList;
   LOG                   :TLogs;
begin
  tmpf:=TStringList.Create;
tmpf.Add('service hotwayd');
tmpf.Add('{');
tmpf.Add('#only_from = 127.0.0.0/24');
tmpf.Add('disable = no');
tmpf.Add('type = unlisted');
tmpf.Add('socket_type = stream');
tmpf.Add('protocol = tcp');
tmpf.Add('wait = no');
tmpf.Add('user = nobody');
tmpf.Add('groups = yes');
tmpf.Add('server = /opt/artica/sbin/hotwayd');
tmpf.Add('server_args = -l 2');
tmpf.Add('#server_args = -p http://proxy:8080 -u proxy_user -q');
tmpf.Add('#proxy_password');
tmpf.Add('port = 113');
tmpf.Add('}');
tmpf.SaveToFile('/etc/xinetd.d/hotwayd');
fpsystem('/etc/init.d/xinetd restart');
LOG:=Tlogs.Create;
log.INSTALL_MODULES('APP_HOTWAYD','hotwayd reconfigure...........:OK');
end;







//##############################################################################
procedure Tclass_install.SENDMAIL_REMOVE();
var
   list:TstringList;
   i:integer;
begin
if FileExists('/etc/init.d/sendmail') then begin
       fpsystem('/etc/init.d/sendmail stop');
       if FileExists('/usr/bin/apt-get') then fpsystem('/usr/bin/apt-get remove sendmail --purge -y');
       if FileExists('/bin/rpm') then fpsystem('/bin/rpm ev sendmail --nodeps');
       list:=TstringList.Create;
       list.Add('/etc/rc.d/init.d/sendmail');
       list.Add('/etc/rc.d/rc0.d/K30sendmail');
       list.Add('/etc/rc.d/rc1.d/K30sendmail');
       list.Add('/etc/rc.d/rc2.d/S80sendmail');
       list.Add('/etc/rc.d/rc3.d/S80sendmail');
       list.Add('/etc/rc.d/rc4.d/S80sendmail');
       list.Add('/etc/rc.d/rc5.d/S80sendmail');
       list.Add('/etc/rc.d/rc6.d/K30sendmail');

       for i:=0 to list.Count -1 do begin
           if FileExists(list.Strings[i]) then begin
              writeln('remove '+ list.Strings[i]);
              fpsystem('/bin/rm ' + list.Strings[i]);
           end;
       end;
   end;



end;


//##############################################################################
procedure Tclass_install.EXIM4_REMOVE();
var

   T:TstringList;
   GLOBALINI:myconf;
   LOG:Tlogs;
begin

    GLOBALINI:=MyConf.Create();
    LOG:=TLOGS.CReate;
    
    if FileExists('/var/run/exim4/exim.pid') then begin
     T:=TStringList.Create;
     T.LoadFromFile('/var/run/exim4/exim.pid');
     if GLOBALINI.SYSTEM_PROCESS_EXIST(trim(T.Text)) then begin
        LOG.INSTALL_MODULES('APP_POSTFIX','killing process exim4' );
        fpsystem('/bin/kill -9 ' + trim(T.Text));
     end;
        
    end;
    if FileExists('/usr/bin/apt-get') then fpsystem('/usr/bin/apt-get remove exim4 --purge -y');

    LOG.INSTALL_MODULES('APP_POSTFIX','Destroy Exim4 folders and files' );
    if FileExists('/var/spool/exim4') then fpsystem('/bin/rm -rf /var/spool/exim4');
    if FileExists('/etc/exim4') then fpsystem('/bin/rm -rf /etc/exim4');
    
If FileExists('/etc/init.d/exim4') then fpsystem('/bin/rm /etc/init.d/exim4');
If FileExists('/etc/cron.daily/exim4-base') then fpsystem('/bin/rm /etc/cron.daily/exim4-base');
If FileExists('/etc/logrotate.d/exim4-base') then fpsystem('/bin/rm /etc/logrotate.d/exim4-base');
If FileExists('/etc/ppp/ip-up.d/exim4') then fpsystem('/bin/rm /etc/ppp/ip-up.d/exim4');
If FileExists('/etc/rc0.d/K20exim4') then fpsystem('/bin/rm /etc/rc0.d/K20exim4');
If FileExists('/etc/rc1.d/K20exim4') then fpsystem('/bin/rm /etc/rc1.d/K20exim4');
If FileExists('/etc/rc2.d/S20exim4') then fpsystem('/bin/rm /etc/rc2.d/S20exim4');
If FileExists('/etc/rc3.d/S20exim4') then fpsystem('/bin/rm /etc/rc3.d/S20exim4');
If FileExists('/etc/rc4.d/S20exim4') then fpsystem('/bin/rm /etc/rc4.d/S20exim4');
If FileExists('/etc/rc5.d/S20exim4') then fpsystem('/bin/rm /etc/rc5.d/S20exim4');
If FileExists('/etc/rc6.d/K20exim4') then fpsystem('/bin/rm /etc/rc6.d/K20exim4');
If FileExists('/etc/rc0.d/K20exim4') then fpsystem('/bin/rm /etc/rc0.d/K20exim4');
If FileExists('/usr/lib/exim4') then fpsystem('/bin/rm -rf /usr/lib/exim4');
If FileExists('/usr/sbin/exim') then fpsystem('/bin/rm /usr/sbin/exim');
If FileExists('/usr/sbin/exim4') then fpsystem('/bin/rm /usr/sbin/exim4');
If FileExists('/usr/sbin/exim_checkaccess') then fpsystem('/bin/rm /usr/sbin/exim_checkaccess');
If FileExists('/usr/sbin/exim_convert4r4') then fpsystem('/bin/rm /usr/sbin/exim_convert4r4');
If FileExists('/usr/sbin/exim_dbmbuild') then fpsystem('/bin/rm /usr/sbin/exim_dbmbuild');
If FileExists('/usr/sbin/exim_dumpdb') then fpsystem('/bin/rm /usr/sbin/exim_dumpdb');
If FileExists('/usr/sbin/exim_fixdb') then fpsystem('/bin/rm /usr/sbin/exim_fixdb');
If FileExists('/usr/sbin/exim_lock') then fpsystem('/bin/rm /usr/sbin/exim_lock');
If FileExists('/usr/sbin/eximstats') then fpsystem('/bin/rm /usr/sbin/eximstats');
If FileExists('/usr/sbin/exim_tidydb') then fpsystem('/bin/rm /usr/sbin/exim_tidydb');
If FileExists('/usr/sbin/syslog2eximlog') then fpsystem('/bin/rm /usr/sbin/syslog2eximlog');
If FileExists('/usr/sbin/update-exim4.conf') then fpsystem('/bin/rm /usr/sbin/update-exim4.conf');
If FileExists('/usr/sbin/update-exim4.conf.template') then fpsystem('/bin/rm /usr/sbin/update-exim4.conf.template');
If FileExists('/usr/sbin/update-exim4defaults') then fpsystem('/bin/rm /usr/sbin/update-exim4defaults');
If FileExists('/usr/share/doc/exim4') then fpsystem('/bin/rm /usr/share/doc/exim4');
If FileExists('/usr/share/doc/exim4-base') then fpsystem('/bin/rm -rf /usr/share/doc/exim4-base');
If FileExists('/var/lib/exim4') then fpsystem('/bin/rm -rf /var/lib/exim4');
If FileExists('/var/log/exim4') then fpsystem('/bin/rm -rf /var/log/exim4');
If FileExists('/var/run/exim4') then fpsystem('/bin/rm -rf /var/run/exim4');
If FileExists('/usr/share/exim4') then fpsystem('/bin/rm -rf /usr/share/exim4');
LOG.INSTALL_MODULES('APP_POSTFIX','Destroy Exim4 folders and files done...' );
end;


//##############################################################################
procedure Tclass_install.BOGOFILTER_INSTALL();
var
   source:string;
   LOG:Tlogs;
begin
LOG:=TLogs.Create;



BERKLEY_INSTALL();
GSL_INSTALL();
  if not FileExists('/opt/artica/lib/libgsl.so') then begin
     writeln('Need gsl libraries... aborting');
     LOG.INSTALL_MODULES('APP_BOGOFILTER','unable to stat /opt/artica/lib/libgsl.so');
     exit;
  end;
  
  if not DirectoryExists('/opt/artica/db') then begin
     writeln('Need BerkeleyDB libraries... aborting');
     LOG.INSTALL_MODULES('APP_BOGOFILTER',' unable to stat /opt/artica/bdb');
     exit;
  end;
  
  if FileExists('/usr/local/bin/bogofilter') then begin
        log.INSTALL_MODULES('APP_BOGOFILTER','BogoFilter....................:OK');
        exit
  end;
  
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           BOGOFILTER                           xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();
  source:=COMPILE_GENERIC_APPS('APP_BOGOFILTER','bogofilter','bogofilter');
  if not DirectoryExists(source) then exit;
  LOG:=TLogs.Create;
  fpsystem('cd ' + source + ' && ./configure --with-libdb-prefix=/opt/artica/db --with-libsqlite3-prefix=/opt/artica/lib  --sysconfdir=/opt/artica/etc/bogofilter --with-libiconv-prefix=/opt/artica --with-included-gsl && make && make install');

end;
//##############################################################################
function Tclass_install.ReadFileIntoString(path:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:text;
   datas:string;
   datas_file:string;
begin
      datas_file:='';
      if not FileExists(path) then begin
        ShowScreen('Error:thProcThread.ReadFileIntoString -> file not found (' + path + ')');
        exit;

      end;
      TRY
     assign(Afile,path);
     reset(Afile);
     while not EOF(Afile) do
           begin
           readln(Afile,datas);
           datas_file:=datas_file + datas +CRLF;
           end;

close(Afile);
             EXCEPT
              ShowScreen('Error:thProcThread.ReadFileIntoString -> unable to read (' + path + ')');
           end;
result:=datas_file;


end;
//##############################################################################
PROCEDURE Tclass_install.logs(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
      BEGIN

        TargetPath:='/var/log/artica-postfix/artica-install.log';

        forcedirectories('/var/log/artica-postfix');
        zDate:=DateToStr(Date)+ chr(32)+TimeToStr(Time);
        xText:=zDate + ' ' + zText;


        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            writeln(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             ShowScreen(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################
function Tclass_install.mail_log_path():string;
var filedatas,ExpressionGrep:string;
RegExpr:TRegExpr;
begin

if not FileExists('/etc/syslog.conf') then exit;
filedatas:=ReadFileIntoString('/etc/syslog.conf');
   ExpressionGrep:='mail\.=info.+?-([\/a-zA-Z_0-9\.]+)?';
   RegExpr:=TRegExpr.create;
   RegExpr.ModifierI:=True;
   RegExpr.expression:=ExpressionGrep;
   if RegExpr.Exec(filedatas) then  begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     exit;
   end;


   ExpressionGrep:='mail\.\*.+?-([\/a-zA-Z_0-9\.]+)?';
   RegExpr.expression:=ExpressionGrep;
   if RegExpr.Exec(filedatas) then   begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     exit;
   end;

  RegExpr.Free;
///usr/bin/rrdtool
end;
//###############################################################################
procedure Tclass_install.SET_DEFAULT_CONFIG();
var
 GLOBAL_INI:myconf;
begin
 GLOBAL_INI:=myconf.Create();
 ShowScreen('Set default configuration for all settings');
 GLOBAL_INI.set_LDAP('cyrus_admin','cyrus');
 GLOBAL_INI.set_LDAP('cyrus_password','admin');
 GLOBAL_INI.set_LDAP('servername','127.0.0.1');
 GLOBAL_INI.Free;

end;


//###############################################################################
function Tclass_install.LDAP_STATUS(Continue:boolean;show:boolean):boolean;
var ldap_conf,suffix,admin,password,init,schema_path,schemaPostfix,servername,cyrus_admin,cyrus_password,daemon_username,ldap_use_suse_schema:string;
 GLOBAL_INI:myconf;
begin
result:=True;
     GLOBAL_INI:=myconf.Create();
     admin:=GLOBAL_INI.get_LDAP('admin');
     password:=GLOBAL_INI.get_LDAP('password');
     suffix:=GLOBAL_INI.get_LDAP('suffix');
     init:=ldap.INITD_PATH();
     schema_path:=ldap.SCHEMA_PATH();
     ldap_conf:=ldap.SLAPD_CONF_PATH();
     servername:=GLOBAL_INI.get_LDAP('servername');
     cyrus_admin:=GLOBAL_INI.get_LDAP('cyrus_admin');
     cyrus_password:=GLOBAL_INI.get_LDAP('cyrus_password');
     daemon_username:=GLOBAL_INI.LDAP_GET_DAEMON_USERNAME();
     schemaPostfix:='';
     
     if length(ldap_conf)=0 then begin
        ldap_conf:='(Unable to locate)';
        result:=false;
     end;
     
     if length(admin)=0 then begin
        admin:='(not set)';
        result:=false;
     end;
     
     if length(password)=0 then begin
        password:='(not set)';
        result:=false;
     end;
     
    if length(suffix)=0 then begin
        suffix:='(not set)';
        result:=false;
     end;
     
    if length(suffix)=0 then begin
        suffix:='(not set)';
        result:=false;
     end;
     
    if length(servername)=0 then begin
        servername:='(not set 127.0.0.1 by default)';
        result:=false;
     end;
     
    if length(schemaPostfix)=0 then begin
        schemaPostfix:='(not set)';
        result:=false;
     end;
     
     ldap_use_suse_schema:='no';
     if ldap.USE_SUSE_SCHEMA()=true then ldap_use_suse_schema:='yes';
     
     
     if show=True then begin
           ShowScreen('Artica Status - LDAP Settings : ');
           ShowScreen('******************************');
           ShowScreen('Server name.................:' + servername);
           ShowScreen('Daemon username.............:' + daemon_username);
           ShowScreen('binary path.................:' + GLOBAL_INI.LDAP_GET_BIN_PATH);
           ShowScreen('ldap config path............:' + ldap_conf);
           ShowScreen('Schema path.................:' + schema_path);
           ShowScreen('Postfix Schema path.........:' + schemaPostfix);
           ShowScreen('Use SuSe Schema.............:' + ldap_use_suse_schema);
           ShowScreen('init........................:' + init);
           ShowScreen('suffix (root database)......:' + suffix);
           ShowScreen('admin.......................:' + admin);
           ShowScreen('password....................:' + password);
           ShowScreen('Cyrus administrator.........:' + cyrus_admin);
           ShowScreen('Cyrus administrator password:' + cyrus_password);
           ShowScreen('');
           
           if result=false then begin
            ShowScreen('Some components are not set, run settings command in order');
            ShowScreen('to configure ldap server and artica');
           end;
           
           
     end;
     GLOBAL_INI.Free;
     
     if Continue<>True then begin
        exit;
     end;

end;
//###############################################################################
function Tclass_install.LDAP_SET_CYRUS_ADM():boolean;
var
   ladap_password:string;
   GLOBAL_INI:myconf;
   LOGS:Tlogs;
begin
      GLOBAL_INI:=myconf.Create();
      LOGS:=Tlogs.Create;
      if not FileExists(GLOBAL_INI.CYRUS_DELIVER_BIN_PATH()) then begin
      LOGS.INSTALL_MODULES('APP_POSTFIX','Initialize cyrus admin........:Unable to stat cyrdeliver path ');
      exit;
      end;
      ladap_password:=GLOBAL_INI.get_LDAP('password');
      GLOBAL_INI.set_LDAP('cyrus_admin','cyrus');
      GLOBAL_INI.set_LDAP('cyrus_password',ladap_password);
      fpsystem(ExtractFilePath(ParamStr(0)) + '/artica-ldap -cyrus-restore');
      exit(true);
end;
//###############################################################################
procedure Tclass_install.LDAP_CREATE_DATABASE();
   var
   suffix:string;
   tf:TstringList;
   RegExpr:TRegExpr;
   cyrus_admin,cyrus_password,admin,password,cmdline,init:string;
   GLOBAL_INI:myconf;

begin
     GLOBAL_INI:=myconf.Create;
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='dc=([A-Za-z0-9\_\-]+)';
     tf:=TstringList.Create;
     suffix:=GLOBAL_INI.Get_LDAP('suffix');
     cyrus_admin:=GLOBAL_INI.get_LDAP('cyrus_admin');
     cyrus_password:=GLOBAL_INI.get_LDAP('cyrus_password');
     admin:=GLOBAL_INI.Get_LDAP('admin');
     password:=GLOBAL_INI.Get_LDAP('password');
     init:=ldap.INITD_PATH();
     RegExpr.Exec(suffix);
    ShowScreen('Creating suffix for database ' + suffix);

    ShowScreen('Writing ldif file');
    tf.add('dn:' + suffix);
    tf.add('objectclass: dcObject');
    tf.add('objectclass: organization');
    tf.add('o: Artica-postfix');
    tf.add('dc:' + RegExpr.Match[1]);
    tf.add('');


tf.add('');
tf.add('dn: cn=' + cyrus_admin + ',' + suffix);
tf.add('cn:' + cyrus_admin);
tf.add('sn:' + cyrus_admin);
tf.add('userPassword:' +  cyrus_password);
tf.add('objectClass: inetOrgPerson');
tf.add('objectClass: top');
tf.add('uid:' + cyrus_admin);

tf.add('');
tf.add('dn: cn=' + admin + ',' + suffix);
tf.add('objectClass: simpleSecurityObject');
tf.add('objectClass: organizationalRole');
tf.add('cn:'+ admin);
tf.add('description: LDAP administrator');
tf.add('userPassword:' + password);

tf.SaveToFile('/tmp/default.ldif');
tf.Free;

ShowScreen('Stopping ldap server');
fpsystem(init + ' stop');
ShowScreen('Adding default database...');
cmdline:='/usr/sbin/slapadd -l /tmp/default.ldif';
ShowScreen(cmdline);
fpsystem(cmdline);
ShowScreen('starting ldap server');
fpsystem(init + ' start');

end;
//###############################################################################
procedure Tclass_install.KAV_UNINSTALL();
begin


     
     ShowScreen('Removing ave software...');
     if DirectoryExists('/var/db/kav') then fpsystem('/bin/rm -rf /var/db/kav');
     if DirectoryExists('/opt/kav') then fpsystem('/bin/rm -rf /opt/kav');
     if DirectoryExists('/etc/kav') then fpsystem('/bin/rm -rf /etc/kav');
     if FileExists('/etc/init.d/aveserver') then begin
        if FileExists('/usr/sbin/update-rc.d') then begin
          fpsystem('/usr/sbin/update-rc.d -f aveserver remove');
          fpsystem('/bin/rm /etc/init.d/aveserver');
       end;
     end;
     

    fpsystem('/etc/init.d/postfix restart');

end;




//###############################################################################
procedure Tclass_install.KAV_INSTALL();
  var
     LOG:Tlogs;
     www_link:string;
     GLOBAL_INI:myConf;
     Target_path:string;
     cmd_line:string;
     sys:TSystem;
     isCommanded:boolean;
     kavmilter:Tkavmilter;
begin

LOG:=Tlogs.Create;
GLOBAL_INI:=myconf.Create();
kavmilter:=Tkavmilter.create(GLOBAL_INI.SYS);
isCommanded:=GLOBAL_INI.COMMANDLINE_PARAMETERS('-kav-install');
if not isCommanded then begin
  if FileExists('/opt/kav/5.6/kavmilter/bin/licensemanager') then begin
      kavmilter.STOP();
      LOG.INSTALL_MODULES('APP_KAV','Kaspersky Antivirus...........:OK');
      KAV_CONFIGURE();
      exit;
  end;
end;

  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                       Kaspersky Anti-virus                     xxx');
  writeln(chr(9) + 'xxx                          For Mail server                       xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

www_link:='http://www.artica.fr/download/kav4milter-5.6.20.2.tgz';

forceDirectories('/opt/artica/install/sources/kav4milter');
Target_path:='/opt/artica/install/sources/kav4milter/' + ExtractFileName(www_link);
  
  LOG.INSTALL_MODULES('APP_KAV','downloading  '+ExtractFileName(www_link));
  LOG.INSTALL_MODULES('APP_KAV','Store package into  '+Target_path);
  if not FileExists(Target_path) then GLOBAL_INI.WGET_DOWNLOAD_FILE(www_link,Target_path);

  if not FileExists(Target_path) then begin
      LOG.INSTALL_MODULES('APP_KAV','Failed ERROR DOWNLOADING');
  end;

   cmd_line:='/bin/tar -xf ' + Target_path + ' -C /opt/artica/install/sources/kav4milter';
   LOG.INSTALL_MODULES('APP_KAV',cmd_line);
   fpsystem(cmd_line);

   LOG.INSTALL_MODULES('APP_KAV','Creating user and group kav');
   sys:=TSystem.Create();
   sys.CreateGroup('kav');
   sys.AddUserToGroup('kav','kav','','');
   
   LOG.INSTALL_MODULES('APP_KAV','Installing files');
   fpsystem('/bin/cp -rf /opt/artica/install/sources/kav4milter/etc/kav /etc/');
   fpsystem('/bin/cp -rf /opt/artica/install/sources/kav4milter/opt/kav /opt/');
   fpsystem('/bin/cp -rf /opt/artica/install/sources/kav4milter/opt/kav /opt/');
   fpsystem('/bin/cp -rf /opt/artica/install/sources/kav4milter/usr/lib/* /usr/lib/');
   fpsystem('/bin/cp -rf /opt/artica/install/sources/kav4milter/var/db /var/');
   fpsystem('/bin/cp -rf /opt/artica/install/sources/kav4milter/var/log /var/');
   

   
   LOG.INSTALL_MODULES('APP_KAV','Running installation.....');
   fpsystem('/opt/artica/install/sources/kav4milter/install/doinst.sh');
   
   
  
   if not FileExists('/opt/kav/5.6/kavmilter/bin/licensemanager') then begin
        LOG.INSTALL_MODULES('APP_KAV','Failed installing from packager');
        exit;
   end;

   KAV_CONFIGURE();


end;
//###############################################################################
procedure Tclass_install.KAV_CONFIGURE();
var
   licence_file:string;
   LOG:Tlogs;
   kavmilter:Tkavmilter;
begin
  LOG:=Tlogs.Create;

  licence_file:=ExtractFilePath(ParamStr(0)) + 'install/MLX_00BD2B2E_081231.key';
  if FileExists(licence_file) then begin
     fpsystem('/opt/kav/5.6/kavmilter/bin/licensemanager -a ' + licence_file + ' >/dev/null 2>&1');
     LOG.INSTALL_MODULES('APP_KAV','Kaspersky Antivirus licence...:OK');
  end;
  if FileExists('/opt/kav/5.6/kavmilter/bin/keepup2date.sh') then begin
   fpsystem('/opt/kav/5.6/kavmilter/bin/keepup2date.sh -install >/dev/null 2>&1');
   fpsystem('/opt/kav/5.6/kavmilter/bin/keepup2date.sh -run &');
   LOG.INSTALL_MODULES('APP_KAV','Kaspersky Antivirus update....:OK');
  end;
  
  
  if FileExists(ExtractFilePath(ParamStr(0)) + 'install/kavmilter.conf') then fpsystem('/bin/cp ' + ExtractFilePath(ParamStr(0)) + 'install/kavmilter.conf' + ' /etc/kav/5.6/kavmilter/kavmilter.conf');
  if FileExists(ExtractFilePath(ParamStr(0)) + 'install/kavmilter.default.conf') then fpsystem('/bin/cp ' + ExtractFilePath(ParamStr(0)) + 'install/kavmilter.default.conf' + ' /etc/kav/5.6/kavmilter/groups.d/default.conf');
  LOG.INSTALL_MODULES('APP_KAV','Kaspersky default settings....:OK');


   ForceDirectories('/var/db/kav/5.6/kavmilter/backup');
   forceDirectories('/var/log/kav/5.6/kavmilter');
   fpsystem('/bin/chown -R kav:kav /var/log/kav/5.6/kavmilter >/dev/null 2>&1');
   fpsystem('/bin/chown -R kav:kav /var/db/kav/5.6/kavmilter/backup >/dev/null 2>&1');
  
  kavmilter:=tkavmilter.Create(SYS);
  kavmilter.START();
  LOG.INSTALL_MODULES('APP_KAV','Restarting milter service.....:OK');
  POSTFIX_CONFIGURE_MAIN_CF();
  

end;
//###############################################################################
procedure Tclass_install.KAVMILTER_UNINSTALL();
var

   LOG:Tlogs;

begin
  LOG:=Tlogs.Create;
  fpsystem('/opt/kav/5.6/kavmilter/bin/kavmilter-setup.sh -del-service');
  fpsystem('/opt/kav/5.6/kavmilter/bin/kavmilter-setup.sh -del-filter 1> /dev/null 2> /dev/null');
  fpsystem('/opt/kav/5.6/kavmilter/bin/kavmilter-setup.sh -del-product 1> /dev/null 2> /dev/null');
  fpsystem('/opt/kav/5.6/kavmilter/bin/kavmilter-setup.sh -del-webmin-module 1> /dev/null 2> /dev/null');
  fpsystem('/opt/kav/5.6/kavmilter/bin/keepup2date.sh -uninstall 1> /dev/null 2> /dev/null');
  fpsystem('/opt/kav/5.6/kavmilter/bin/backup-sweeper.sh -uninstall 1> /dev/null 2> /dev/null');
  fpsystem('/bin/rm -fr /var/db/kav/5.6/kavmilter/licenses');
  fpsystem('/bin/rm -f /usr/share/man/man5/kav*');
  fpsystem('/bin/rm -f /usr/share/man/man5/backup-sweeper*');
  fpsystem('/bin/rm -f /usr/share/man/man5/keepup2date*');
  fpsystem('/bin/rm -f /usr/share/man/man5/troubleshooter*');
  fpsystem('/bin/rm -f /usr/share/man/man8/keepup2date*');
  fpsystem('/bin/rm -f /usr/share/man/man8/licensemanager*');
  fpsystem('/bin/rm -rf /opt/kav/5.6');
  fpsystem('/bin/rm -rf /etc/kav/5.6');
  fpsystem('/bin/rm -fr /var/db/kav/5.6');
  LOG.INSTALL_MODULES('APP_KAV','remove Kaspersky antivirus....:OK');
  POSTFIX_CONFIGURE_MAIN_CF();


end;
//###############################################################################



procedure Tclass_install.KAS_INSTALL();
var

 GLOBAL_INI:myconf;
 Install_path:string;
 Ini:TiniFile;
 FileToDownload:string;
 LOG:Tlogs;
begin
     LOG:=Tlogs.Create;
     LOG.INSTALL_MODULES('APP_KAS3','Starting installation of Kaspersky Anti-spam enterprise edition');
     GLOBAL_INI:=myconf.Create();
     Install_path:=GLOBAL_INI.get_INSTALL_PATH();

     if not FileExists('/usr/bin/wget') then begin
             ShowScreen('Unable to locate wget program !!, aborting...');
             exit;
     end;

     LOG.INSTALL_MODULES('APP_KAS3','Get the latest software');

if FileExists('/bin/rpm') then GLOBAL_INI.WGET_DOWNLOAD_FILE('http://www.artica.fr/auto.kas.php?repos=rpm','/tmp/kaslink.ini');
if FileExists('/usr/bin/dpkg') then GLOBAL_INI.WGET_DOWNLOAD_FILE('http://www.artica.fr/auto.kas.php?repos=deb' ,'/tmp/kaslink.ini');
  if not FileExists('/tmp/kaslink.ini') then begin
     LOG.INSTALL_MODULES('APP_KAS3','Unable to get the latest software... aborting');
     ShowScreen('Unable to get the latest software... aborting');
     exit;
  end;
            
ini:=TiniFile.Create('/tmp/kaslink.ini');
FileToDownload:=ini.ReadString('NEXT','file','');
if length(FileToDownload)=0 then begin
     LOG.INSTALL_MODULES('APP_KAS3','Unable to get the latest software... aborting');
     ShowScreen('Unable to get the latest software version... aborting');
     exit;
end;

forcedirectories('/opt/artica/install/sources');
if not FileExists('/opt/artica/install/sources/' +FileToDownload) then begin
   LOG.INSTALL_MODULES('APP_KAS3','download http://www.artica.fr/download/' + FileToDownload);
   GLOBAL_INI.WGET_DOWNLOAD_FILE('http://www.artica.fr/download/' + FileToDownload,'/opt/artica/install/sources/' +FileToDownload);
   if not FileExists('/opt/artica/install/sources/' +FileToDownload) then begin
      ShowScreen('Unable to download the latest software version... aborting');
      exit;
   end;
end;
   ShowScreen('Get the latest software "' + FileToDownload + '"');
      LOG.INSTALL_MODULES('APP_KAS3','Get the latest software "' + FileToDownload + '"');
   
   
   LOG.INSTALL_MODULES('APP_KAS3','installing /opt/artica/install/sources/' +FileToDownload);
   if FileExists('/bin/rpm') then fpsystem('/bin/rpm -i /opt/artica/install/sources/' +FileToDownload);
   if FileExists('/usr/bin/dpkg') then fpsystem('/usr/bin/dpkg -i /opt/artica/install/sources/' +FileToDownload);


    if not DirectoryExists('/usr/local/ap-mailfilter3') then begin
         LOG.INSTALL_MODULES('APP_KAS3','Unable to install the software... Aborting...');
         ShowScreen('Unable to install the software... Aborting...');
         fpsystem('/bin/rm /opt/artica/install/sources/' +FileToDownload);
    end;
         LOG.INSTALL_MODULES('APP_KAS3','Installing license key');
ShowScreen('Installing license key');
fpsystem('/usr/local/ap-mailfilter3/bin/install-key ' + Install_path + '/bin/install/ASGW_00BD2AE7_081231.key');
ShowScreen('Enable updates...');
fpsystem('/usr/local/ap-mailfilter3/bin/enable-updates.sh');
if not FileExists('/usr/local/ap-mailfilter3/etc/filter.conf') then fpsystem('/bin/cp '+ ExtractFilePath(ParamStr(0)) + 'install/filter.conf /usr/local/ap-mailfilter3/etc/filter.conf');
if not FileExists('/usr/local/ap-mailfilter3/etc/keepup2date.conf') then fpsystem('/bin/cp '+ ExtractFilePath(ParamStr(0)) + 'install/kas.keepup2date.conf /usr/local/ap-mailfilter3/etc/keepup2date.conf');




end;
//###############################################################################
procedure Tclass_install.KAS_UNINSTALL();

var
   master:Tstringlist;
   filename:string;
begin
  ShowScreen('uninstalling Kaspersky Anti-Spam software...');

  if not DirectoryExists('/usr/local/ap-mailfilter3/bin/scripts') then begin
         ShowScreen('Kaspersky Anti-Spam software is not installed...');
  end;

  ShowScreen('uninstall the software...');
  if FileExists('/bin/rpm') then begin
      fpsystem('/bin/rpm -qa|grep kas- >/tmp/qalist');
      if not FileExists('/tmp/qalist') then begin
         ShowScreen('Fatal error');
         exit;
     end;
  master:=Tstringlist.Create;
  master.LoadFromFile('/tmp/qalist');
  filename:=trim(master.Text);
  fpsystem('/bin/rpm -ev ' + filename);
  
  end;
  
  if FileExists('/usr/bin/apt-get') then fpsystem('/usr/bin/apt-get remove kas-3 --purge');
  if not FileExists('/etc/init.d/kas3') then fpsystem('/bin/rm -rf /usr/local/ap-mailfilter3');
  



  

end;
//##############################################################################################
procedure Tclass_install.CYRUS_UNINSTALL();
var
   distri:string;
   GLOBAL_INI:myconf;
   repos:string;

begin
   GLOBAL_INI:=myconf.Create();
   distri:=GLOBAL_INI.get_LINUX_DISTRI();

   if distri<>'DEBIAN' then begin
      ShowScreen('not debian system...');
      GLOBAL_INI.Free;
      exit;
   end;

   ShowScreen('detecting previous installation...');
   if fileExists('/etc/init.d/cyrus21') then begin
         ShowScreen('Cyrus 2.1 detected');
         ShowScreen('Get repositories...');
         repos:=AnalyseDebianRepositoriesPackages('cyrus');
         fpsystem('apt-get autoremove --yes');
         fpsystem('/usr/bin/apt-get remove ' + repos + ' --purge --yes');

   end;


end;
//##############################################################################################
procedure Tclass_install.POSTFIX_CONFIGURE_MAIN_CF();
var
  GLOBAL_INI:MyConf;
  LOG:Tlogs;
begin
     GLOBAL_INI:=MyConf.Create;
     LOG:=Tlogs.Create;
     GLOBAL_INI.POSTFIX_CONFIGURE_MAIN_CF();
     LOG.INSTALL_MODULES('APP_POSTFIX','reconfigure main.cf...........:OK');
end;
//##############################################################################################

function Tclass_install.AnalyseDebianRepositoriesPackages(application_requires:string):string;
var  path,datas:string;
RegExpr:TRegExpr;
mRes:string;
   GLOBAL_INI:myconf;

begin
   GLOBAL_INI:=myconf.Create();
   mRes:='';
 path:=GLOBAL_INI.get_INSTALL_PATH() + '/bin/install/repos-debian.txt';
     if not FileExists(path) then begin
       ShowScreen('Error:Tclass_install.AnalyseDebianRepositoriesPackages -> file not found (' + path + ')');
       exit;
     end;
   datas:=ReadFileIntoString(path);

   RegExpr:=TRegExpr.create;
   RegExpr.Expression:='([a-zA-Z0-9\-\.]+)->' + application_requires + ';';
     if RegExpr.Exec(datas) then  repeat
              mRes:=mRes + ' ' + RegExpr.Match[1];
     until not RegExpr.ExecNext;
    if length(mRes)>0 then mRes:=' ' + mRes;
    Result:=mRes;

end;
//##############################################################################################
function Tclass_install.MYSQL_ADMIN():boolean;
var
  GLOBAL_INI:myconf;
  mysql_admin,mysql_password:string;
begin
   GLOBAL_INI:=myconf.Create();
  while not GLOBAL_INI.MYSQL_ACTION_TESTS_ADMIN() do begin
       ShowScreen('Please give the Mysql administrator account: ');
       readln(mysql_admin);
       ShowScreen('Please give the Mysql administrator password: ');
       readln(mysql_password);
       GLOBAL_INI.ARTICA_MYSQL_SET_INFOS('database_admin',mysql_admin);
       GLOBAL_INI.ARTICA_MYSQL_SET_INFOS('database_password',mysql_password);
  end;

  exit(true);
   
   
end;
//##############################################################################################
function Tclass_install.CommandLineIsExists(regex:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;
begin
s:='';
     for i:=2 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);

     end;
     
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:=regex;
     if RegExpr.Exec(s) then result:=true else result:=false;
     RegExpr.Free;
     
end;
//##############################################################################################
procedure Tclass_install.REPLACE_CONFIG(regex_string:string;LineToReplace:string;FileSource:string);
var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
begin
 if not FileExists(FileSource) then begin
    ShowScreen('REPLACE_CONFIG::Unable to locate  ' + FileSource);
    exit;
 
 end;
 ShowScreen('REPLACE_CONFIG:: ' + regex_string + ' -> ' + LineToReplace + ' in ' + FileSource);
  RegExpr:=TRegExpr.create;
  RegExpr.expression:=regex_string;
  list:=TstringList.Create;
  list.LoadFromFile(FileSource);
  for i:=0 to list.Count-1 do begin
      if RegExpr.exec(list.Strings[i]) then begin
           ShowScreen('REPLACE_CONFIG:: sucess change ->' + LineToReplace);
           list.Strings[i]:=LineToReplace;
           break;
      end;

  end;

   list.SaveToFile(FileSource);
   ShowScreen('REPLACE_CONFIG:: End, save file');
   list.Free;
   RegExpr.Free;

end;
//##############################################################################################




function Tclass_install.GenerateCertificateFileName(PathToConfigFile:string):string;
var
GLOBAL_INI:myconf;
openssl:string;
command:string;
pass,cerpath,smtpd_key,cacertFileName,smtpd_csr,smtpd_crt:string;
logs:Tlogs;
begin
     GLOBAL_INI:=myconf.Create();
     logs:=Tlogs.create;
     if length(PathToConfigFile)=0 then begin
      PathToConfigFile:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/databases/DEFAULT-CERTIFICATE-DB.txt';
      if debug then ShowScreen('using default configuration file ' + PathToConfigFile);
     end;
      


     openssl:=GLOBAL_INI.OPENSSL_TOOL_PATH();
     if length(openssl)=0 then exit('unable to locate openssl tool');
     if not FileExists(PathToConfigFile) then exit( 'unable to locate ' +PathToConfigFile + ' tool');
     
     
     pass:=GLOBAL_INI.CERTIFICATE_PASS(PathToConfigFile);
     cerpath:=GLOBAL_INI.CERTIFICATE_PATH(PathToConfigFile);
     cacertFileName:=cerpath + '/' + GLOBAL_INI.CERTIFICATE_CA_FILENAME(PathToConfigFile);
     smtpd_key:=cerpath + '/' + GLOBAL_INI.CERTIFICATE_KEY_FILENAME(PathToConfigFile);
     smtpd_crt:=cerpath + '/' + GLOBAL_INI.CERTIFICATE_CERT_FILENAME(PathToConfigFile);
     smtpd_csr:=cerpath + '/smtpd.csr';
     forcedirectories(cerpath);


     //openssl genrsa -des3 -rand /etc/hosts -out smtpd.key 1024
     //openssl x509 -req -days 3650 -in smtpd.csr -signkey smtpd.key -out smtpd.crt
     //openssl req -new -key smtpd.key -out smtpd.csr
     
     //openssl req  -new -keyout ./demoCA/private/cakey.pem -out ./demoCA/careq.pem
     
     //openssl

     //
     
     
     command:=openssl + ' genrsa -des3 -rand /etc/hosts -passout pass:'+ pass + ' -out ' + smtpd_key + ' 1024';
     logs.logs(command);
     if debug then begin
        ShowScreen('**************************************************************************************');
        ShowScreen(command);

     end;

     fpsystem(command);


     command:=openssl + ' req -new -passin pass:'+ pass + ' -batch -config '+ PathToConfigFile + ' -key ' +smtpd_key + ' -out ' + smtpd_csr;
     logs.logs(command);
     if debug then begin
        ShowScreen('**************************************************************************************');
        ShowScreen(command);

     end;
     fpsystem(command);

     
     
     command:=openssl + ' x509 -passin pass:' + pass + ' -req -days 3650 -in ' + smtpd_csr + ' -signkey ' + smtpd_key + ' -out ' + smtpd_crt;
     logs.logs(command);
     if debug then begin
        ShowScreen('**************************************************************************************');
        ShowScreen(command);

     end;

     fpsystem(command);
     
     
     command:=openssl + ' rsa -passin pass:' + pass + ' -in ' + smtpd_key + ' -out ' + smtpd_key + '.unencrypted';
     logs.logs(command);
     if debug then begin
        ShowScreen('**************************************************************************************');
        ShowScreen(command);

     end;

     fpsystem(command);
     
     command:='/bin/mv -f ' + smtpd_key + '.unencrypted ' + smtpd_key;
     logs.logs(command);
     if debug then begin
        ShowScreen('**************************************************************************************');
        ShowScreen(command);
        ShowScreen('**************************************************************************************');
     end;

     fpsystem(command);

     command:=openssl + ' req -new -x509  -extensions v3_ca -days 3650 -batch -config '+ PathToConfigFile + ' -keyout '+ cerpath + '/ca.pm -out ' + cacertFileName;
     logs.logs(command);
     if debug then begin
        ShowScreen('**************************************************************************************');
        ShowScreen(command);
        ShowScreen('**************************************************************************************');
     end;
     fpsystem(command);
     fpsystem('chmod 600 ' + cerpath);
     fpsystem('chmod 600 ' + cerpath + '/*');

end;
//##############################################################################################
procedure Tclass_install.ShowScreen(line:string);
 var
 myFile : TextFile;
 TargetPath:string;
 BEGIN

        writeln('Tclass_install::' + line);
        TargetPath:='/var/log/artica-postfix/artica-install.log';
        forcedirectories('/var/log/artica-postfix');



        TRY
           EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            writeln(myFile, line);
            CloseFile(myFile);
        EXCEPT

          END;
 END;
 
//##############################################################################################
PROCEDURE Tclass_install.Disable_se_linux();
var
   disable_selinux:string;
   GLOBAL_INI:myconf;
begin
    GLOBAL_INI:=myconf.Create;
    disable_selinux:=GLOBAL_INI.LINUX_INSTALL_INFOS('disable_selinux');
    if ParamStr(1)='-selinux_off' then disable_selinux:='yes';

    if disable_selinux='yes' then begin
       ShowScreen('**********************************************************************');
       ShowScreen('Disable_se_linux:: Disable armor protections if installed to prevent bad intergrations...');
       writeln('Disable_se_linux:: Disable selinux/AppArmor,will be disable after rebooting.....');
       ShowScreen('**********************************************************************');
       GLOBAL_INI.set_SELINUX_DISABLED();
    end;
     GLOBAL_INI.Free;
end;
//##############################################################################################
function Tclass_install.POSTFIX_SET_SASL():boolean;
var zfile:TstringList;
    GLOBAL_INI:myconf;
    LOGS:TLOGS;
    saslauthd:tsaslauthd;
begin
result:=false;
LOGS:=TLogs.Create;
GLOBAL_INI:=myconf.Create;
forcedirectories('/etc/postfix/sasl');

LOGS.INSTALL_MODULES('APP_POSTFIX','Set sasl......................:Writing smtpd.conf for enabling postfix and saslauthd');
 zfile:=TstringList.Create;
 zfile.Add('pwcheck_method: saslauthd');
 zfile.Add('mech_list: PLAIN LOGIN');
 zfile.Add('minimum_layer: 0');
 zfile.Add('saslauthd_path: /var/run/mux');
 
 zfile.SaveToFile('/etc/postfix/sasl/smtpd.conf');

 ccyrus.CYRUS_DAEMON_STOP();
 ldap.LDAP_STOP();
 
 saslauthd:=tsaslauthd.Create(SYS);
 saslauthd.STOP();
 
 LOGS.INSTALL_MODULES('APP_POSTFIX','Set sasl......................:Checking users and groups');
 fpsystem('groupadd sasl >/dev/null 2>&1');
 fpsystem('mkdir -p /var/spool/postfix/var >/dev/null 2>&1');
 forcedirectories('/var/spool/postfix/var');
 fpsystem('ln -s --force /var/run /var/spool/postfix/var/run  >/dev/null 2>&1');
 fpsystem('chgrp sasl /var/spool/postfix/var/run/saslauthd >/dev/null 2>&1');
 fpsystem('useradd postfix -g sasl >/dev/null 2>&1');
 fpsystem('/bin/ln -s --force /etc/postfix/sasl/smtpd.conf /usr/lib/sasl2/smtpd.conf');
 forceDirectories('/usr/lib/sasl2');
 fpsystem('/bin/ln -s --force /etc/postfix/sasl/smtpd.conf /usr/lib/sasl2/smtpd.conf');
 LOGS.INSTALL_MODULES('APP_POSTFIX','Set sasl......................:done...');
 zFile.Free;
 postfix.POSTFIX_CHECK_SASLDB2();
 GLOBAL_INI.FRee;
 result:=true;

end;
//##############################################################################################
function Tclass_install.DNSMASQ_RECONFIGURE():boolean;
Var ListenAddress,ResolvFile, cacheSize:string;
GLOBAL_INI:myconf;
LOGS:Tlogs;
dnsmasq:tdnsmasq;
begin
    LOGS:=Tlogs.create;
    dnsmasq:=tdnsmasq.Create(SYS);
    
    
    if not FileExists('/etc/init.d/dnsmasq') then begin
       if not FileExists('/usr/local/sbin/dnsmasq') then begin
       LOGS.INSTALL_MODULES('APP_DNSMASQ','Unable to locate /etc/init.d/dnsmasq or /usr/local/sbin/dnsmasq, dnsmasq seems to not be installed...');
       showScreen('DNSMASQ_RECONFIGURE:: Unable to locate /etc/init.d/dnsmasq or /usr/local/sbin/dnsmasq, dnsmasq seems to not be installed...');
       exit(false);
    end;
    end;
    GLOBAL_INI:=myconf.Create;
    ListenAddress:=trim(dnsmasq.DNSMASQ_GET_VALUE('listen-address'));
    ResolvFile:=trim(dnsmasq.DNSMASQ_GET_VALUE('resolv-file'));
    cacheSize:=dnsmasq.DNSMASQ_GET_VALUE('cache-size');
    
LOGS.INSTALL_MODULES('APP_DNSMASQ','DNSMASQ_RECONFIGURE:: **********************************************************************');
LOGS.INSTALL_MODULES('APP_DNSMASQ','DNSMASQ_RECONFIGURE:: reconfigure dnsmasq old values are :');
LOGS.INSTALL_MODULES('APP_DNSMASQ','DNSMASQ_RECONFIGURE:: listen-address..................:"' + ListenAddress + '"');
LOGS.INSTALL_MODULES('APP_DNSMASQ','DNSMASQ_RECONFIGURE:: resolv-file.....................:"' + ResolvFile+ '"');
LOGS.INSTALL_MODULES('APP_DNSMASQ','DNSMASQ_RECONFIGURE:: cache-size......................:"' + cacheSize+ '"');
LOGS.INSTALL_MODULES('APP_DNSMASQ','DNSMASQ_RECONFIGURE:: **********************************************************************');
    
    if ListenAddress='' then dnsmasq.DNSMASQ_SET_VALUE('listen-address','127.0.0.1');
    if ResolvFile='' then dnsmasq.DNSMASQ_SET_VALUE('resolv-file','/etc/dnsmasq.resolv.conf');
    if cacheSize<>'250' then  dnsmasq.DNSMASQ_SET_VALUE('cache-size','250');
    if not FileExists('/etc/dnsmasq.resolv.conf') then fpsystem('cat /etc/resolv.conf >/etc/dnsmasq.resolv.conf');
    GLOBAL_INI.SYSTEM_ADD_NAMESERVER('127.0.0.1');
    LOGS.INSTALL_MODULES('APP_DNSMASQ','DNSMASQ_RECONFIGURE:: restarting dnsmasq');
    GLOBAL_INI.free;
    LOGS.FRee;

end;
//##############################################################################################

procedure Tclass_install.PERL_LINUX_NET_DEV();
var
   source:string;
   LOGS:Tlogs;
   compile:string;
   GLOBAL_INI:myconf;
begin
   GLOBAL_INI:=myconf.Create;
   LOGS:=Tlogs.Create;
   if PERL_FIND_FILES('Linux/net/dev.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','linux:net:dev.................:OK');
      exit;
   end;
   if not FileExists(GLOBAL_INI.PERL_BIN_PATH()) then PERL_UPGRADE();
   if not FileExists(GLOBAL_INI.PERL_BIN_PATH()) then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','linux:net:dev.................:failed (perl is not installed)');
      exit;
   end;
   
   if FileExists('/usr/share/artica-postfix/bin/install/Linux-net-dev-1.00/dev.pm') then begin
       source:='/usr/share/artica-postfix/bin/install/Linux-net-dev-1.00';
   end else begin
    source:=COMPILE_GENERIC_APPS('APP_SQUID','Linux-net-dev','Linux-net-dev');
   end;
   
   
    if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','linux:net:dev.................:failed (error extracting)');
        exit;
    end;
    
     compile:='cd ' + source + '&& ' + GLOBAL_INI.PERL_BIN_PATH()+ ' ' + source + '/Makefile.PL  && make && make install';
     fpsystem(compile);

   if PERL_FIND_FILES('Linux/net/dev.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','linux:net:dev.................:OK');
      exit;
   end;

end;
//##############################################################################################

procedure Tclass_install.KAV4SAMBA_INSTALL();
var
 LOGS:Tlogs;
 autoanswers_conf:TstringList;
begin
  LOGS:=Tlogs.Create;
  writeln('Starting configuration of Kaspersky For Samba server...');
  
if not FileExists('/opt/kaspersky/kav4samba/lib/bin/setup/postinstall.pl') then begin
   logs.Debuglogs('KAV4SAMBA_INSTALL():: unable to stat /opt/kaspersky/kav4samba/lib/bin/setup/postinstall.pl');
   writeln('Unable to stat /opt/kaspersky/kav4samba/lib/bin/setup/postinstall.pl are you sure you have installed the package?');
   writeln('Aborting...');
   exit;
end;
         writeln('Writing auto-configuration...');
         autoanswers_conf:=TStringList.Create;
         autoanswers_conf.Add('CONFIGURE_ENTER_KEY_PATH=' + GLOBALINI.get_INSTALL_PATH() + '/bin/install');
         autoanswers_conf.Add('KAVMS_SETUP_LICENSE_DOMAINS=*');
         autoanswers_conf.Add('CONFIGURE_KEEPUP2DATE_ASKPROXY=no');
         autoanswers_conf.Add('CONFIGURE_RUN_KEEPUP2DATE=no');
         autoanswers_conf.Add('CONFIGURE_WEBMIN_ASKCFGPATH=');
         autoanswers_conf.Add('SETUP_SAMBA_CONFIRM_DEFAULTS=Y');
         autoanswers_conf.Add('SETUP_SAMBA_ASK_CONFIGURE_SHARES=S');
         autoanswers_conf.Add('KAV4PROXY_SETUP_TYPE=3');
         autoanswers_conf.Add('KAV4PROXY_SETUP_LISTENADDRESS=127.0.0.1:1344');
         autoanswers_conf.Add('KAV4PROXY_SETUP_CONFPATH='+zsquid.SQUID_CONFIG_PATH());
         autoanswers_conf.Add('KAV4PROXY_SETUP_BINPATH='+zsquid.SQUID_BIN_PATH());
         autoanswers_conf.Add('KAV4PROXY_CONFIRM_FOUND=Y');
         autoanswers_conf.SaveToFile('/opt/kaspersky/kav4samba/lib/bin/setup/autoanswers.conf');
         autoanswers_conf.Free;
         writeln('Writing auto-configuration done...');

         writeln('Executing post-scripts installation');
         fpsystem('cd /opt/kaspersky/kav4samba/lib/bin/setup && perl ./postinstall.pl');
         fpsystem('cd /opt/kaspersky/kav4samba/lib/bin/setup && perl ./kavsamba_setup.pl');
         writeln('Executing Update in Background');
         fpsystem('/opt/kaspersky/kav4samba/bin/kav4samba-keepup2date -q &');
         writeln('Done...');

end;
//##############################################################################################


function Tclass_install.PERL_FIND_FILES(filename:string):boolean;
  var
  GLOBAL_INI:myconf;
  l:Tstringlist;
  i:integer;
begin
    result:=false;
    l:=TstringList.Create;
    GLOBAL_INI:=myconf.Create;
   l.AddStrings(GLOBAL_INI.PERL_INCFolders());
   for i:=0 to l.Count-1 do begin
      if fileExists(l.Strings[i] + '/' + filename) then begin
          result:=true;
          break;
      end;
   
   end;
   
             l.free;

end;
//##############################################################################################

procedure Tclass_install.PERL_HTML_PARSER();
var
   source:string;
   LOGS:Tlogs;
   compile:string;
begin
   LOGS:=Tlogs.Create;
   if FileExists('/opt/artica/lib/perl/5.8.8/i486-linux-gnu-thread-multi/HTML/Parser.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','HTML:parser...................:OK');
      exit;
   end;
   if not FileExists('/opt/artica/bin/perl') then PERL_UPGRADE();
   if not FileExists('/opt/artica/bin/perl') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','..............................:failed (perl is not installed)');
      exit;
   end;

    source:=COMPILE_GENERIC_APPS('APP_SQUID','HTML-Parser','HTML-Parser');
    if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','............................:failed (error extracting)');
        exit;
    end;

     compile:='cd ' + source + '&& /opt/artica/bin/perl ' + source + '/Makefile.PL LIB=/opt/artica/lib/perl/5.8.8 && make && make install';
     fpsystem(compile);

end;
//#############################################################################################
procedure Tclass_install.PERL_GENERIC_INSTALL(indexWeb:string;whatToCheck:string);
var
   source:string;
   LOGS:Tlogs;
   compile:string;
begin

   LOGS:=Tlogs.Create;

   if FileExists(whatToCheck) then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','Perl addons...................:OK ' + indexWeb + '(' + whatToCheck + ')');
      exit;
   end;
   if not FileExists('/opt/artica/bin/perl') then PERL_UPGRADE();
   if not FileExists('/opt/artica/bin/perl') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','..............................:failed (perl is not installed)');
      exit;
   end;

    source:=COMPILE_GENERIC_APPS('APP_SQUID',indexWeb,indexWeb);
    if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','............................:failed (error extracting)');
        exit;
    end;

     compile:='cd ' + source + '&& /opt/artica/bin/perl ' + source + '/Makefile.PL && make && make install';
     fpsystem(compile);
     

   if FileExists(whatToCheck) then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','Perl addons...................:Failed ' + indexWeb + '(' + whatToCheck + ')');
      exit;
   end;

end;
//#############################################################################################
procedure Tclass_install.PERL_DBD_MYSQL();
  var
  mysql_path:string;
  compile,source:string;
  LOGS:Tlogs;
begin
     LOGS:=Tlogs.Create;
     if FileExists('/opt/artica/lib/perl/5.8.8/auto/DBD/mysql/mysql.so') then begin
         LOGS.INSTALL_MODULES('APP_ARTICA','DBD:MYSQL.....................:OK');
         exit;
     end;
     if FileExists('/opt/artica/bin/mysql_config') then mysql_path:='/opt/artica/bin/mysql_config';
     if FileExists('/opt/artica/amavis/bin/mysql_config') then mysql_path:='/opt/artica/amavis/bin/mysql_config';
     if FileExists('/opt/artica/mysql/bin/mysql_config') then mysql_path:='/opt/artica/mysql/bin/mysql_config';
     
     if length(mysql_path)=0 then begin
          LOGS.INSTALL_MODULES('APP_ARTICA','DBD:MYSQL.....................: Failed to stat mysql_config');
          exit;
     end;
     source:=COMPILE_GENERIC_APPS('APP_ARTICA','DBD-mysql','DBD-mysql');
     
if not DirectoryExists(source) then begin
          LOGS.INSTALL_MODULES('APP_ARTICA','DBD:MYSQL.....................:failed (error extracting)');
        exit;
    end;

     compile:='cd ' + source + '&& /opt/artica/bin/perl Makefile.PL --mysql_config=' + mysql_path +' && make && make install';
     LOGS.INSTALL_MODULES('APP_ARTICA',compile);
     fpsystem(compile);

 if FileExists('/opt/artica/lib/perl/5.8.8/auto/DBD/mysql/mysql.so') then begin
         LOGS.INSTALL_MODULES('APP_ARTICA','DBD:MYSQL.....................:SUCCESS');
         exit;
     end;


end;
//#############################################################################################
procedure Tclass_install.PERL_COMPRESS_ZLIB();
var
   source:string;
   LOGS:Tlogs;
   compile:string;
begin
   LOGS:=Tlogs.Create;
   if FileExists('/opt/artica/lib/perl/5.8.8/Compress/Zlib.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','Compress:zlib.................:OK');
      exit;
   end;
   if not FileExists('/opt/artica/bin/perl') then PERL_UPGRADE();
   if not FileExists('/opt/artica/bin/perl') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','..............................:failed (perl is not installed)');
      exit;
   end;

    source:=COMPILE_GENERIC_APPS('APP_SQUID','Compress-Zlib','Compress-Zlib');
    if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','............................:failed (error extracting)');
        exit;
    end;

     compile:='cd ' + source + '&& /opt/artica/bin/perl -I/opt/artica/lib/perl/5.8.8/i486-linux-gnu-thread-multi ' + source + '/Makefile.PL LIB=/opt/artica/lib/perl/5.8.8 && make && make install';
     fpsystem(compile);

end;
//#############################################################################################
procedure Tclass_install.PERL_HTML_TAGSET();
var
   source:string;
   LOGS:Tlogs;
   compile:string;
begin
   LOGS:=Tlogs.Create;
   if FileExists('/opt/artica/lib/perl/5.8.8/HTML/Tagset.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','HTML:Tagset...................:OK');
      exit;
   end;
   if not FileExists('/opt/artica/bin/perl') then PERL_UPGRADE();
   if not FileExists('/opt/artica/bin/perl') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','..............................:failed (perl is not installed)');
      exit;
   end;

    source:=COMPILE_GENERIC_APPS('APP_SQUID','HTML-Tagset','HTML-Tagset');
    if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','............................:failed (error extracting)');
        exit;
    end;

     compile:='cd ' + source + '&& /opt/artica/bin/perl -I/opt/artica/lib/perl/5.8.8/i486-linux-gnu-thread-multi ' + source + '/Makefile.PL LIB=/opt/artica/lib/perl/5.8.8 && make && make install';
     fpsystem(compile);

end;
//#############################################################################################
procedure Tclass_install.PERL_URI();
var
   source:string;
   LOGS:Tlogs;
   compile:string;
begin
   LOGS:=Tlogs.Create;
   if FileExists('/opt/artica/lib/perl/5.8.8/URI.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','URI...........................:OK');
      exit;
   end;
   if not FileExists('/opt/artica/bin/perl') then PERL_UPGRADE();
   if not FileExists('/opt/artica/bin/perl') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','..............................:failed (perl is not installed)');
      exit;
   end;

    source:=COMPILE_GENERIC_APPS('APP_SQUID','URI','URI');
    if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','............................:failed (error extracting)');
        exit;
    end;

     compile:='cd ' + source + '&& /opt/artica/bin/perl -I/opt/artica/lib/perl/5.8.8/i486-linux-gnu-thread-multi ' + source + '/Makefile.PL LIB=/opt/artica/lib/perl/5.8.8 && make && make install';
     fpsystem(compile);
     
 if FileExists('/opt/artica/lib/perl/5.8.8/URI.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','URI...........................:OK');
      exit;
   end;

end;
//#############################################################################################

procedure Tclass_install.PERL_LIBWWW();
var
   source:string;
   LOGS:Tlogs;
   compile:string;
begin
   LOGS:=Tlogs.Create;
   if FileExists('/opt/artica/lib/perl/5.8.8/LWP.pm') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','Perl:libwww...................:OK');
      exit;
   end;
   if not FileExists('/opt/artica/bin/perl') then PERL_UPGRADE();
   if not FileExists('/opt/artica/bin/perl') then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','..............................:failed (perl is not installed)');
      exit;
   end;

    source:=COMPILE_GENERIC_APPS('APP_SQUID','libwww-perl','libwww-perl');
    if not DirectoryExists(source) then begin
        LOGS.INSTALL_MODULES('APP_ARTICA','............................:failed (error extracting)');
        exit;
    end;

     compile:='cd ' + source + '&& /opt/artica/bin/perl -I/opt/artica/lib/perl/5.8.8/i486-linux-gnu-thread-multi ' + source + '/Makefile.PL LIB=/opt/artica/lib/perl/5.8.8 && make && make install';
     fpsystem(compile);

end;



//##############################################################################################
procedure Tclass_install.LIB_PCRE_INSTALL();
var
   source:string;
   LOGS:Tlogs;

begin
  LOGS:=Tlogs.Create;
  if FileExists('/opt/artica/include/pcre.h') then begin
     LOGS.INSTALL_MODULES('APP_SQUID','PCRE..........................:OK');
     exit;
  end;
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           LIB PCRE                             xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

     source:=COMPILE_GENERIC_APPS('APP_SQUID','pcre','pcre');
     if DirectoryExists(source) then begin
      LOGS.INSTALL_MODULES('APP_SQUID','compiling source stored in "' + source + '"');


      fpsystem('cd ' + source + '&& ./configure --prefix=/opt/artica ' + LD_LIBRARY_PATH + ' ' +CPPFLAGS );
      fpsystem('cd ' + source + '&& make');
      fpsystem('cd ' + source + '&& make install');
      LOGS.INSTALL_MODULES('APP_ARTICA','DONE...');
     end else begin
     LOGS.INSTALL_MODULES('APP_SQUID','PCRE..........................:DONE...');
     end;
end;
//##############################################################################################
procedure Tclass_install.DANSGUARDIAN_INSTALL();
var
   source:string;
   LOGSY:Tlogs;
   compile:string;
   LinkIng:boolean;
begin


  LOGSY:=Tlogs.Create;
  LinkIng:=false;
  
  if FileExists('/opt/artica/sbin/dansguardian') then begin
      LOGSY.INSTALL_MODULES('APP_SQUID','dansguardian..................:OK...');
      exit;
  end;
  
  if not FileExists('/opt/artica/include/pcre.h') then LIB_PCRE_INSTALL();

  if not FileExists('/opt/artica/include/pcre.h') then begin
         LOGSY.INSTALL_MODULES('APP_SQUID','Unable to stat  /opt/artica/include/pcre.h');
         exit;
  end;
    
  if FileExists('/opt/artica/bin/pcre-config') then begin
       if not FileExists('/bin/pcre-config') then begin
            LinkIng:=true;
            LOGSY.INSTALL_MODULES('APP_SQUID','/bin/ln -s /opt/artica/bin/pcre-config /bin/pcre-config');
            fpsystem('/bin/ln -s /opt/artica/bin/pcre-config /bin/pcre-config');
       end;
  
  end else begin
      LOGSY.INSTALL_MODULES('APP_SQUID','Unable to stat  /opt/artica/bin/pcre-config');
      exit;
  
  end;
    
  writeln();
  writeln();
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxx                           dansGuardian                         xxx');
  writeln(chr(9) + 'xxx                                                                xxx');
  writeln(chr(9) + 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

     source:=COMPILE_GENERIC_APPS('APP_SQUID','dansguardian','dansguardian');
     if DirectoryExists(source) then begin
      LOGSY.INSTALL_MODULES('APP_SQUID','compiling source stored in "' + source + '"');

      compile:='cd ' + source + '&& ./configure --prefix=/opt/artica';
      compile:=compile + ' --with-pcre-config=/opt/artica/bin/pcre-config';
      compile:=compile + ' --prefix=/opt/artica';
      compile:=compile + ' --enable-icap';
      compile:=compile + ' --with-piddir=/var/run/dansguardian';
      compile:=compile + ' --with-logdir=/opt/artica/var/logs/squid';
      compile:=compile + ' --with-pcre=/opt/artica';
      compile:=compile + ' --with-proxyuser=squid';
      compile:=compile + ' --with-proxygroup=squid';
      compile:=compile + ' --with-zlib=/opt/artica';
      compile:=compile + ' ' + LD_LIBRARY_PATH + ' ' +CPPFLAGS ;
      LOGSY.INSTALL_MODULES('APP_SQUID',compile);
      fpsystem(compile);
      fpsystem('cd ' + source + '&& make');
      fpsystem('cd ' + source + '&& make install');
      if LinkIng then fpsystem('/bin/rm -rf /bin/pcre-config');
      
      

     end else begin
     LOGSY.INSTALL_MODULES('APP_SQUID','dansguardian..................:failed');
     end;
     
  if FileExists('/opt/artica/sbin/dansguardian') then begin
      LOGSY.INSTALL_MODULES('APP_SQUID','dansguardian..................:OK...');
      exit;
  end;
     
LOGSY.INSTALL_MODULES('APP_SQUID','dansguardian..................:failed');
end;
//##############################################################################################
procedure Tclass_install.OMA_INSTALL();
var
   SYS:Tsystem;
   LOGS:Tlogs;
   FileTmp:string;
begin
   LOGS:=Tlogs.Create;
   SYS:=Tsystem.Create();
   if FileExists(SYS.LOCATE_MAILPARSE_SO()) then begin
       LOGS.INSTALL_MODULES('APP_ARTICA','OMA...........: mailparse.so already installed...');
       exit;
   end;
   
   if not FileExists(SYS.LOCATE_PECL()) then begin
      LOGS.INSTALL_MODULES('APP_ARTICA','OMA...........: Unable to stat pecl, aborting...');
      exit;
   end;
   FileTmp:=LOGS.FILE_TEMP();
   Fpsystem(SYS.LOCATE_PECL()+ ' install mailparse >'+FileTmp +' 2>&1');
   LOGS.INSTALL_MODULES('APP_ARTICA',logs.ReadFromFile(FileTmp));
   LOGS.DeleteFile(FileTmp);
end;






end.

