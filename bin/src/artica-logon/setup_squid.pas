unit setup_squid;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,
  install_generic,logs,squid,zsystem,dansguardian;

  type
  tsetup_squid=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     source_folder,cmd:string;

public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure dansgardian_install();
      procedure kav4proxy_install();
END;

implementation

constructor tsetup_squid.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure tsetup_squid.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_squid.xinstall();
var
source_folder:string;
logs:Tlogs;
SYS:TSystem;
squid:tsquid;
localversion:string;
remoteversion:string;
remoteBinVersion:int64;
LocalBinVersion:int64;
begin
 squid:=tsquid.Create;
 logs:=Tlogs.Create;
 SYS:=Tsystem.Create();
 install.INSTALL_STATUS('APP_SQUID',10);
 localversion:=squid.SQUID_VERSION();
 LocalBinVersion:=squid.SQUID_BIN_VERSION(localversion);

 if ParamStr(2)<>'--reconfigure' then begin
 writeln('Check versions...');
 remoteversion:=libs.COMPILE_VERSION_STRING('squid3');
 remoteBinVersion:=squid.SQUID_BIN_VERSION(remoteversion);
 writeln('Local version...........: ',LocalBinVersion,' as ',localversion);
 writeln('Remote version..........: ',remoteBinVersion,' as ',remoteversion);
 
 if LocalBinVersion>=remoteBinVersion then begin
     writeln('No changes..........: Success');
     install.INSTALL_PROGRESS('APP_SQUID','{installed}');
     install.INSTALL_STATUS('APP_SQUID',100);
     exit();
 end;
 end;
 writeln('Prepare installation or upgrade....');
          install.INSTALL_PROGRESS('APP_SQUID','{downloading}');
 
  source_folder:=libs.COMPILE_GENERIC_APPS('squid3');
  if not DirectoryExists(source_folder) then begin
     writeln('Install squid3 failed...');
     install.INSTALL_STATUS('APP_SQUID',110);
     exit;
  end;
  SetCurrentDir(source_folder);
            install.INSTALL_PROGRESS('APP_SQUID','{compiling}');
 

       cmd:='./configure ';
       cmd:=cmd+' --prefix=/usr ';
       cmd:=cmd+' --includedir=${prefix}/include ';
       cmd:=cmd+' --mandir=${prefix}/share/man ';
       cmd:=cmd+' --infodir=${prefix}/share/info ';
       cmd:=cmd+' --sysconfdir=/etc ';
       cmd:=cmd+' --localstatedir=/var ';
       cmd:=cmd+' --libexecdir=${prefix}/lib/squid3 ';
       cmd:=cmd+' --disable-maintainer-mode ';
       cmd:=cmd+' --disable-dependency-tracking ';
       cmd:=cmd+' --srcdir=. ';
       cmd:=cmd+' --datadir=/usr/share/squid3';
       cmd:=cmd+' --sysconfdir=/etc/squid3';
       cmd:=cmd+' --mandir=/usr/share/man';
       cmd:=cmd+' --enable-gnuregex ';
       cmd:=cmd+' --enable-removal-policy=heap';
       cmd:=cmd+' --enable-follow-x-forwarded-for';
       cmd:=cmd+' --enable-cache-digests ';
       cmd:=cmd+' --with-maxfd=32000 ';
       cmd:=cmd+' --with-large-files ';
       cmd:=cmd+' --disable-dlmalloc ';
       cmd:=cmd+' --with-pthreads ';
       cmd:=cmd+' --enable-esi';
       cmd:=cmd+' --enable-storeio=aufs,diskd,ufs';
       cmd:=cmd+' --with-aufs-threads=10';
       cmd:=cmd+' --with-maxfd=16384';
       cmd:=cmd+' --enable-useragent-log ';
       cmd:=cmd+' --enable-referer-log ';
       cmd:=cmd+' --enable-ssl ';
       cmd:=cmd+' --enable-x-accelerator-vary ';
       cmd:=cmd+' --with-dl ';
       cmd:=cmd+' --enable-basic-auth-helpers=LDAP';
       cmd:=cmd+' --enable-truncate';
       cmd:=cmd+' --enable-linux-netfilter';
       cmd:=cmd+' --enable-auth=basic,digest';
       cmd:=cmd+' --enable-digest-auth-helpers=ldap,password';
       cmd:=cmd+' --enable-external-acl-helpers=ip_user,ldap_group,unix_group,wbinfo_group';
       cmd:=cmd+' --with-default-user=squid ';
       cmd:=cmd+' --enable-icap-client';
       cmd:=cmd+' --enable-cache-digests';
       cmd:=cmd+' --enable-icap-support';
       cmd:=cmd+' --enable-poll ';
       cmd:=cmd+' --enable-epoll ';
       cmd:=cmd+' --enable-async-io ';
       cmd:=cmd+' --enable-delay-pools ';
       cmd:=cmd+' --enable-ssl';
       cmd:=cmd+' CFLAGS="-DNUMTHREADS=60 -O3 -pipe -fomit-frame-pointer -funroll-loops -ffast-math -fno-exceptions"';
       writeln(cmd);
fpsystem(cmd);
install.INSTALL_STATUS('APP_SQUID',60);
fpsystem('make && make install');
install.INSTALL_STATUS('APP_SQUID',80);

       if not FileExists(squid.SQUID_BIN_PATH()) then begin
          writeln('Compilation failed....');
          writeln('');
          install.INSTALL_STATUS('APP_SQUID',110);
          install.INSTALL_PROGRESS('APP_SQUID','{failed}');
          exit;
       end;


       if not FileExists(source_folder + '/helpers/digest_auth/ldap/digest_ldap_auth') then begin
          writeln('Compilation failed....' +source_folder + '/helpers/digest_auth/ldap/digest_ldap_auth does not exists');
          writeln('');
          install.INSTALL_STATUS('APP_SQUID',110);
          install.INSTALL_PROGRESS('APP_SQUID','{failed}');
          SetCurrentDir('/root');
          exit;
       end;

  install.INSTALL_PROGRESS('APP_SQUID','{installing}');
  fpsystem('/bin/cp -rfv ' + source_folder + '/helpers/digest_auth/ldap/digest_ldap_auth /usr/lib/squid3/');
  if FileExists('/usr/sbin/squid3') then fpsystem('/bin/cp /usr/sbin/squid /usr/sbin/squid3');
  logs.DeleteFile('/etc/artica-postfix/versions.cache');
  install.INSTALL_STATUS('APP_SQUID',90);
  fpsystem('/usr/share/artica-postfix/bin/process1 --force');
  fpsystem('/etc/init.d/artica-postfix restart squid');
  install.INSTALL_STATUS('APP_SQUID',100);
  install.INSTALL_PROGRESS('APP_SQUID','{installed}');
  SetCurrentDir('/root');
  writeln('success');
end;
//#########################################################################################
procedure tsetup_squid.dansgardian_install();
var
source_folder:string;
logs:Tlogs;
SYS:TSystem;
squid:tsquid;
localversion,cmd:string;
remoteversion:string;
remoteBinVersion:int64;
LocalBinVersion:int64;
dans:tdansguardian;
begin
 squid:=tsquid.Create;
 logs:=Tlogs.Create;
 SYS:=Tsystem.Create();
 install.INSTALL_STATUS('APP_DANSGUARDIAN',10);
 install.INSTALL_PROGRESS('APP_DANSGUARDIAN','{checking}');
 dans:=tdansguardian.Create(SYS);
 SetCurrentDir('/root');

 localversion:=dans.DANSGUARDIAN_VERSION;
 LocalBinVersion:=dans.DANSGUARDIAN_BIN_VERSION(localversion);

remoteversion:=libs.COMPILE_VERSION_STRING('dansguardian');
remoteBinVersion:=dans.DANSGUARDIAN_BIN_VERSION(remoteversion);

 if LocalBinVersion>=remoteBinVersion then begin
     writeln('No changes..........: Success ( remote=',remoteBinVersion,' local=',LocalBinVersion,')');
     install.INSTALL_PROGRESS('APP_DANSGUARDIAN','{installed}');
     install.INSTALL_STATUS('APP_DANSGUARDIAN',100);
     exit();
 end;
 writeln('Prepare installation or upgrade....');
 install.INSTALL_PROGRESS('APP_DANSGUARDIAN','{downloading}');

  source_folder:=libs.COMPILE_GENERIC_APPS('dansguardian');
  if not DirectoryExists(source_folder) then begin
     writeln('Install dansguardian failed...');
     install.INSTALL_STATUS('APP_DANSGUARDIAN',110);
     install.INSTALL_PROGRESS('APP_DANSGUARDIAN','{failed}');
     exit;
  end;

cmd:='./configure';
cmd:=cmd+' --mandir=/usr/share/man/';
cmd:=cmd+' --enable-clamd=yes';
cmd:=cmd+' --with-proxyuser=squid';
cmd:=cmd+' --with-proxygroup=squid';
cmd:=cmd+' --prefix=/usr';
cmd:=cmd+' --mandir=\${prefix}/share/man';
cmd:=cmd+' --infodir=\${prefix}/share/info';
cmd:=cmd+' --sysconfdir=/etc';
cmd:=cmd+' --localstatedir=/var';
cmd:=cmd+' --enable-icap=yes';
cmd:=cmd+' --enable-commandline=yes';
cmd:=cmd+' --enable-trickledm=yes';
cmd:=cmd+' --enable-email=yes';
cmd:=cmd+' --enable-ntlm=yes';
  SetCurrentDir(source_folder);
  install.INSTALL_PROGRESS('APP_DANSGUARDIAN','{compiling}');
fpsystem(cmd);
fpsystem('make && make install');
SetCurrentDir('/root');

localversion:=dans.DANSGUARDIAN_VERSION;
LocalBinVersion:=dans.DANSGUARDIAN_BIN_VERSION(localversion);

 remoteversion:=libs.COMPILE_VERSION_STRING('dansguardian');
 remoteBinVersion:=dans.DANSGUARDIAN_BIN_VERSION(remoteversion);
     fpsystem('/etc/init.d/artica-postfix restart dansguardian');
     logs.DeleteFile('/etc/artica-postfix/versions.cache');
install.INSTALL_STATUS('APP_DANSGUARDIAN',100);
     install.INSTALL_PROGRESS('APP_DANSGUARDIAN','{installed}');
if LocalBinVersion=remoteBinVersion then begin
     writeln('success "',LocalBinVersion,'"');


end else begin
    SetCurrentDir('/root');
end;

end;
//#########################################################################################
procedure tsetup_squid.kav4proxy_install();
var
source_folder:string;
SYS:Tsystem;
autoanswers_conf:TstringList;
zsquid:Tsquid;
begin
   if Paramstr(2)<>'--force' then begin
   if FileExists('/opt/kaspersky/kav4proxy/sbin/kav4proxy-kavicapserver') then begin
       writeln('Already installed');
       install.INSTALL_STATUS('APP_KAV4PROXY',100);
       install.INSTALL_PROGRESS('APP_KAV4PROXY','{installed}');
       exit;
   end;
   end;

 writeln('Prepare installation or upgrade....');
 install.INSTALL_PROGRESS('APP_KAV4PROXY','{downloading}');
 source_folder:=libs.COMPILE_GENERIC_APPS('kav4proxy');

if not DirectoryExists(source_folder) then begin
     writeln('Install Kav4Proxy failed...');
     install.INSTALL_STATUS('APP_KAV4PROXY',110);
     install.INSTALL_PROGRESS('APP_KAV4PROXY','{failed}');
     exit;
  end;


forceDirectories('/opt/kaspersky/kav4proxy/sbin');
forceDirectories('/etc/opt/kaspersky');

  install.INSTALL_PROGRESS('APP_KAV4PROXY','{installing}');
  install.INSTALL_STATUS('APP_KAV4PROXY',50);

fpsystem('cp -rfv ' + source_folder+'/opt /');
fpsystem('cp -rfv ' + source_folder+'/etc /');
fpsystem('cp -rfv ' + source_folder+'/var /');

   if not FileExists('/opt/kaspersky/kav4proxy/sbin/kav4proxy-kavicapserver') then begin
       install.INSTALL_STATUS('APP_KAV4PROXY',110);
       install.INSTALL_PROGRESS('APP_KAV4PROXY','{failed}');
       exit;
   end;

install.INSTALL_PROGRESS('APP_KAV4PROXY','{compiling}');
install.INSTALL_STATUS('APP_KAV4PROXY',70);


 fpsystem('ln -s --force /opt/kaspersky/kav4proxy/lib/bin/kav4proxy /etc/init.d/kav4proxy');
 install.INSTALL_SERVICE('kav4proxy');
 fpsystem('/usr/share/artica-postfix/bin/install/kavgroup/kav4prox_predoinst.sh');
 SYS:=TSystem.Create();
 SYS.CreateGroup('klusers');
 SYS.AddUserToGroup('kluser','klusers','','');
 writeln('creating klusers:kluser account OK');
 fpsystem('/bin/chown -R kluser:klusers /var/log/kaspersky/kav4proxy');
 fpsystem('/bin/chown -R kluser:klusers /var/opt/kaspersky/kav4proxy');
 fpsystem('/bin/chown -R kluser:klusers /var/run/kav4proxy');
 fpsystem('/bin/chmod 0755 /var/opt/kaspersky/kav4proxy');



         zsquid:=Tsquid.Create();
         autoanswers_conf:=TStringList.Create;
         autoanswers_conf.Add('CONFIGURE_ENTER_KEY_PATH=/usr/share/artica-postfix/bin/install');
         autoanswers_conf.Add('KAVMS_SETUP_LICENSE_DOMAINS=*');
         autoanswers_conf.Add('CONFIGURE_KEEPUP2DATE_ASKPROXY=no');
         autoanswers_conf.Add('CONFIGURE_RUN_KEEPUP2DATE=no');
         autoanswers_conf.Add('CONFIGURE_WEBMIN_ASKCFGPATH=');
         autoanswers_conf.Add('KAV4PROXY_SETUP_TYPE=3');
         autoanswers_conf.Add('KAV4PROXY_SETUP_LISTENADDRESS=127.0.0.1:1344');
         autoanswers_conf.Add('KAV4PROXY_SETUP_CONFPATH='+zsquid.SQUID_CONFIG_PATH());
         autoanswers_conf.Add('KAV4PROXY_SETUP_BINPATH='+zsquid.SQUID_BIN_PATH());
         autoanswers_conf.Add('KAV4PROXY_CONFIRM_FOUND=Y');
         autoanswers_conf.Add('KAVICAP_SETUP_NONICAPCFG=Y');
         autoanswers_conf.SaveToFile('/opt/kaspersky/kav4proxy/lib/bin/setup/autoanswers.conf');
         autoanswers_conf.Free;

 install.INSTALL_PROGRESS('APP_KAV4PROXY','{installing}');
 install.INSTALL_STATUS('APP_KAV4PROXY',90);
         SetCurrentDir('/opt/kaspersky/kav4proxy/lib/bin/setup');
         fpsystem('./postinstall.pl');
         fpSystem('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date -q -d /var/run/kav4proxy/keeup2date.pid &');

 sleep(500);
 writeln('running updates OK');
 install.INSTALL_PROGRESS('APP_KAV4PROXY','{installed}');
 install.INSTALL_STATUS('APP_KAV4PROXY',100);
 SetCurrentDir('/root');
 fpsystem('/etc/init.d/artica-postfix restart squid');




end;
//#########################################################################################



end.
