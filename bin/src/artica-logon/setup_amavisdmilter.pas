unit setup_amavisdmilter;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,
  setup_suse_class,
  install_generic,
  setup_ubuntu_class,zsystem,
  postfix_class;

  type
  amavisd=class


private
     libs:tlibs;
     distri:tdistriDetect;
     postfix:tpostfix;
     install:tinstall;
     SYS:Tsystem;
   source_folder,cmd:string;
   webserver_port:string;
   artica_admin:string;
   artica_password:string;
   ldap_suffix:string;
   mysql_server:string;
   mysql_admin:string;
   mysql_password:string;
   ldap_server:string;
   function InstallGeoIP():boolean;



public
      constructor Create();
      procedure Free;
      procedure xinstall();
      function xinstallamavis():boolean;
      procedure install_amavis_stat();
      procedure dspam_install();
      procedure altermime_install();
END;

implementation

constructor amavisd.Create();
begin
  SetCurrentDir('/tmp');
SYS:=Tsystem.Create;
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
postfix:=tpostfix.Create(SYS);
source_folder:='';
webserver_port:=install.lighttpd.LIGHTTPD_LISTEN_PORT();
   artica_admin:=install.openldap.get_LDAP('admin');
   artica_password:=install.openldap.get_LDAP('password');
   ldap_suffix:=install.openldap.get_LDAP('suffix');
   ldap_server:=install.openldap.get_LDAP('server');
   mysql_server:=install.SYS.MYSQL_INFOS('mysql_server');
   mysql_admin:=install.SYS.MYSQL_INFOS('database_admin');
   mysql_password:=install.SYS.MYSQL_INFOS('password');
end;
//#########################################################################################
procedure amavisd.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure amavisd.dspam_install();
var
   compile,source_folder,include:string;
begin



   if FileExists('/usr/include/mysql/mysql.h') then begin
          include:='/usr/include/mysql';
   end;
   
   if length(include)=0 then begin
      writeln('unable to stat mysql.h');
      writeln('If your are on Ubuntu, debian do "apt-get update libmysqlclient15-dev"');
      writeln('If your are on Fedora do "yum install mysql++-devel"');
      exit;
   end;

   source_folder:=libs.COMPILE_GENERIC_APPS('dspam');
   compile:='';
   compile:=compile +' --sysconfdir=/usr/local/etc/dspam';
   compile:=compile +' --with-dspam-home-owner=postfix';
   compile:=compile +' --with-dspam-home-group=postfix';
   compile:=compile +' --with-storage-driver=mysql_drv';
   compile:=compile +' --enable-preferences-extension';
   compile:=compile +' --with-mysql-includes=/usr/include/mysql';
   compile:=compile +' --with-mysql-libraries=/usr/lib/mysql';
   compile:=compile +' --with-dspam-home=/var/amavis/dspam';
   compile:=compile +' --without-delivery-agent';
   compile:=compile +' --without-quarantine-agent';
   compile:=compile +' --with-dspam-owner=postfix';
   compile:=compile +' --with-dspam-group=postfix';
   compile:=compile +' --with-logdir=/var/log/dspam';
   compile:=compile +' --with-logfile=/var/log/dspam/dspam.log';
   //compile:=compile +' --enable-daemon';
   compile:=compile +' --enable-syslog --enable-preferences-extension --enable-long-usernames --enable-signature-headers --enable-large-scale --enable-virtual-users';

  if not DirectoryExists(source_folder) then begin
     writeln('Install dspam failed...');
     exit;
  end;
   
    SetCurrentDir(source_folder);
    fpsystem('./configure ' + compile);
    fpsystem('make && make install');
    if FIleExists('/usr/local/bin/dspam') then begin
       forceDirectories('/var/amavis/dspam/txt');
       if FileExists(source_folder + '/txt/firstrun.txt') then fpsystem('cp -vf '+source_folder + '/txt/*.txt /var/amavis/dspam/txt/');
       writeln('success');
    end;

end;

//#########################################################################################
procedure amavisd.altermime_install();
var
   LocalPath:string;
   source_folder:string;
begin
LocalPath:='/usr/share/artica-postfix/bin/install/amavis/altermime-0.3.9.tar.gz';
source_folder:='';



install.INSTALL_STATUS('APP_ALTERMIME',35);
  install.INSTALL_PROGRESS('APP_ALTERMIME','{downloading}');
if FileExists('/usr/local/bin/altermime') then begin
      writeln('Already installed');
      install.INSTALL_STATUS('APP_ALTERMIME',100);
      exit;
end;

if ParamStr(2)='--local' then begin
   if FileExists(LocalPath) then begin
      writeln('choose localPath '+LocalPath);
      forceDirectories('/tmp/install');
      fpsystem('tar -xf '+ LocalPath + ' -C /tmp/install/');
      if DirectoryExists('/tmp/install/altermime-0.3.9') then source_folder:='/tmp/install/altermime-0.3.9';
   end;
end;

if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('altermime');

  if not DirectoryExists(source_folder) then begin
     writeln('Install altermime failed... (unable to stat '+source_folder+')');
     install.INSTALL_STATUS('APP_ALTERMIME',110);
     exit;
  end;
  install.INSTALL_PROGRESS('APP_ALTERMIME','{compiling}');
SetCurrentDir(source_folder);
fpsystem('make');
fpsystem('make install');

if FileExists('/usr/local/bin/altermime') then begin
      writeln('altermime success');
       install.INSTALL_PROGRESS('APP_ALTERMIME','{installed}');
      install.INSTALL_STATUS('APP_ALTERMIME',100);
      exit;
end;

install.INSTALL_PROGRESS('APP_ALTERMIME','{done}');
end;
//#########################################################################################
procedure amavisd.xinstall();

var
configurelcc:string;
LOCAL:Boolean;
LocalPath:string;

begin
if not FileExists(postfix.POSFTIX_POSTCONF_PATH()) then begin
   writeln('Postfix is not installed');
   exit;
end;

LocalPath:='/usr/share/artica-postfix/bin/install/amavisd-milter-1.4.0';

if FileExists('/usr/local/sbin/amavisd-milter') then begin
      writeln('Already installed');
      install.INSTALL_STATUS('APP_AMAVISD_MILTER',100);
      exit;
end;

if ParamStr(2)='--local' then begin
   if DirectoryExists(LocalPath) then begin
      writeln('choose localPath '+LocalPath);
      source_folder:=LocalPath;
   end;
end;



   install.INSTALL_STATUS('APP_AMAVISD_MILTER',10);
   install.INSTALL_STATUS('APP_AMAVISD_MILTER',30);

    install.INSTALL_PROGRESS('APP_AMAVISD_MILTER','{downloading}');
if not FileExists('/usr/local/sbin/amavisd-milter') then begin
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('amavisd-milter');
  if not DirectoryExists(source_folder) then begin
     writeln('Install amavisd-milter failed... (unable to stat '+source_folder+')');
     install.INSTALL_STATUS('APP_AMAVISD_MILTER',110);
     exit;
  end;
  writeln('Install simple amavisd-milter extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_AMAVISD_MILTER',50);
  install.INSTALL_PROGRESS('APP_AMAVISD_MILTER','{compiling}');
  

       configurelcc:=' LD_LIBRARY_PATH="/lib:/usr/local/lib:/usr/lib/libmilter:/usr/lib" ';
       configurelcc:=configurelcc+'CPPFLAGS="-I/usr/include/libmilter -I/usr/include -I/usr/local/include" ';
       configurelcc:=configurelcc + 'LDFLAGS="-L/lib -L/usr/local/lib -L/usr/lib/libmilter -L/usr/lib" ';



  fpsystem('cd ' + source_folder + ' && ./configure' + configurelcc);
  fpsystem('cd ' + source_folder + '&& make && make install');
  install.INSTALL_PROGRESS('APP_AMAVISD_MILTER','{installing}');
  install_amavis_stat();
end;

  install.INSTALL_STATUS('APP_AMAVISD_MILTER',55);
  
  if ParamStr(2)='--local' then exit;

  if FileExists('/usr/local/sbin/amavisd-milter') then begin

      if xinstallamavis() then begin
         writeln('Success');
         install.INSTALL_PROGRESS('APP_AMAVISD_MILTER','{APP_DSPAM} {installing}');
         dspam_install();
         install.INSTALL_STATUS('APP_AMAVISD_MILTER',100);
         fpsystem('/etc/init.d/artica-postfix restart amavis');
         install.INSTALL_PROGRESS('APP_AMAVISD_MILTER','{installed}');
      end else begin
          writeln('failed');
          install.INSTALL_PROGRESS('APP_AMAVISD_MILTER','{failed}');
      end;
      

  end else begin
      install.INSTALL_PROGRESS('APP_AMAVISD_MILTER','{failed}');
      install.INSTALL_STATUS('APP_AMAVISD_MILTER',110);
      writeln('Failed');
      writeln('Make sure you have installed the package:');
      writeln('libmilter-dev on Ubuntu or Debian');
      writeln('sendmail-devel on Fedora');
  end;

end;
//#########################################################################################
procedure amavisd.install_amavis_stat();
var
  LocalPath:string;
begin
LocalPath:='/usr/share/artica-postfix/bin/install/amavis-stats-0.1.22';


if FileExists('/usr/local/sbin/amavis-stats') then begin
   writeln('amavis-stats already installed');
   writeln('');
   writeln('');
   exit;
end;

if ParamStr(2)='--local' then begin
   if DirectoryExists(LocalPath) then source_folder:=LocalPath;
end;

if not FileExists('/bin/gzcat') then begin
   if FileExists('/bin/zcat') then fpsystem('/bin/ln -s /bin/zcat /bin/gzcat');

end;

if not FileExists('/bin/gzcat') then begin
   writeln('Unable to stat gzcat, abort');
   exit;
end;

if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('amavis-stats');


if not DirectoryExists(source_folder) then begin
   writeln('Amavis-stats failed......');
   writeln('');
   writeln('');
   exit;
end;

SetCurrentDir(source_folder);
fpsystem('./configure --enable-id-check --with-log-file=/var/log/amavis/amavis.log --with-user=postfix --with-group=postfix');
fpsystem('make');
fpsystem('make install');

if FileExists('/usr/local/sbin/amavis-stats') then begin
   writeln('amavis-stats successfully installed');
   writeln('');
   writeln('');
   exit;
end;

end;

//#########################################################################################



function amavisd.xinstallamavis():boolean;
begin
if not FileExists(postfix.POSFTIX_POSTCONF_PATH()) then begin
   writeln('Postfix is not installed');
   exit;
end;
result:=false;

install.INSTALL_STATUS('APP_AMAVISD_NEW',40);
install.INSTALL_PROGRESS('APP_AMAVISD_NEW','{checking}');
if not InstallGeoIP() then exit;

  if not libs.PERL_GENERIC_INSTALL('Crypt-OpenSSL-Random','Crypt::OpenSSL::Random')then exit;
  if not libs.PERL_GENERIC_INSTALL('Crypt-OpenSSL-RSA','Crypt::OpenSSL::RSA')then exit;
  if not libs.PERL_GENERIC_INSTALL('MailTools','Mail::Address')then exit;
  if not libs.PERL_GENERIC_INSTALL('Mail-DKIM','Mail::DKIM') then exit;
  if not libs.PERL_GENERIC_INSTALL('Digest-SHA','Digest::SHA')then exit;
  if not libs.PERL_GENERIC_INSTALL('Digest-SHA1','Digest::SHA1')then exit;
  if not libs.PERL_GENERIC_INSTALL('IO-stringy','IO::Stringy') then exit;
  if not libs.PERL_GENERIC_INSTALL('Unix-Syslog','Unix::Syslog')then exit;
  if not libs.PERL_GENERIC_INSTALL('MIME-tools','MIME::Words') then exit;
  if not libs.PERL_GENERIC_INSTALL('Net-Server','Net::Server') then exit;
  if not libs.PERL_GENERIC_INSTALL('BerkeleyDB','BerkeleyDB') then begin
     writeln('If your are on ubuntu, you can execute "apt-get install libdb4.6-dev" in order to fix this error');
     writeln('If your are on Fedora, you can execute "yum install perl-BerkeleyDB" in order to fix this error');
     install.INSTALL_STATUS('APP_AMAVISD_NEW',110);
     install.INSTALL_PROGRESS('APP_AMAVISD_NEW','{failed}');
     exit;
  end;
  
  
  if not libs.PERL_GENERIC_INSTALL('GSSAPI','GSSAPI') then begin
      writeln('If your are on ubuntu, you can execute "apt-get install libgssapi-perl" in order to fix this error');
      writeln('If your are on OpenSuse, you can execute "zypper install krb5-devel libgssglue-devel" in order to fix this error');
     install.INSTALL_STATUS('APP_AMAVISD_NEW',110);
     install.INSTALL_PROGRESS('APP_AMAVISD_NEW','{failed}');
     exit;
  end;
  
  if not libs.PERL_GENERIC_INSTALL('Authen-SASL','Authen::SASL') then exit;
  if not libs.PERL_GENERIC_INSTALL('DBI','DBI') then exit;
  if not libs.PERL_GENERIC_INSTALL('XML-NamespaceSupport','XML::NamespaceSupport') then exit;
  if not libs.PERL_GENERIC_INSTALL('XML-SAX','XML::SAX') then exit;
  if not libs.PERL_GENERIC_INSTALL('XML-Filter-BufferText','XML::Filter::BufferText') then exit;
  if not libs.PERL_GENERIC_INSTALL('Test-Simple','Test::More') then exit;
  if not libs.PERL_GENERIC_INSTALL('Text-Iconv','Text::Iconv') then exit;
  if not libs.PERL_GENERIC_INSTALL('XML-SAX-Writer','XML::SAX::Writer') then exit;
  if not libs.PERL_GENERIC_INSTALL('Convert-ASN1','Convert::ASN1') then exit;
  if not libs.PERL_GENERIC_INSTALL('Convert-UUlib','Convert::UUlib') then exit;
  


  
  if not libs.PERL_GENERIC_INSTALL('perl-ldap','Net::LDAP') then exit;
  if not libs.PERL_GENERIC_INSTALL('Config-IniFiles','Config::IniFiles') then exit;
  if not libs.PERL_GENERIC_INSTALL('Geo-IP','Geo::IP') then exit;

     install.INSTALL_STATUS('APP_AMAVISD_NEW',70);
     install.INSTALL_PROGRESS('APP_AMAVISD_NEW','{downloading}');

  source_folder:=libs.COMPILE_GENERIC_APPS('amavisd-new');
  if not DirectoryExists(source_folder) then begin
     writeln('Install amavisd-new failed...');
     install.INSTALL_STATUS('APP_AMAVISD_NEW',110);
     install.INSTALL_PROGRESS('APP_AMAVISD_NEW','{failed}');
     exit;
  end;

     install.INSTALL_STATUS('APP_AMAVISD_NEW',80);
     install.INSTALL_PROGRESS('APP_AMAVISD_NEW','{installing}');
    writeln('Install simple amavisd_new extracted on "'+source_folder+'"');
  forceDirectories('/usr/local/sbin');
  forceDirectories('/usr/local/etc/');
  forceDirectories('/var/virusmails');
  forceDirectories('/var/amavis/tmp');
  forceDirectories('/var/amavis/var');
  forceDirectories('/var/amavis/db');
  forceDirectories('/var/amavis/home');
  fpsystem('/bin/chown -R postfix:postfix /var/amavis');
  fpsystem('/bin/cp ' + source_folder + '/amavisd /usr/local/sbin/');
  fpsystem('/bin/chown root /usr/local/sbin/amavisd');
  fpsystem('/bin/chmod 755  /usr/local/sbin/amavisd');
  fpsystem('/bin/cp ' + source_folder + '/amavisd.conf /usr/local/etc/');
  fpsystem('/bin/chown root:postfix /usr/local/etc/amavisd.conf');
  fpsystem('/bin/chmod 640 /usr/local/etc/amavisd.conf');
  fpsystem('/bin/chown postfix:postfix /var/virusmails');
  fpsystem('/bin/chmod 750 /var/virusmails');
  dspam_install();
  install.INSTALL_STATUS('APP_AMAVISD_NEW',100);
  install.INSTALL_PROGRESS('APP_AMAVISD_NEW','{installed}');
  result:=true;

end;
//#########################################################################################
function amavisd.InstallGeoIP():boolean;
var source_folder:string;
begin

if not FileExists(postfix.POSFTIX_POSTCONF_PATH()) then begin
   writeln('Postfix is not installed');
   exit;
end;

result:=false;
if FileExists('/usr/local/lib/libGeoIP.so') then begin
   writeln('libGeoIP looks good');
   exit(true);
end;

source_folder:=libs.COMPILE_GENERIC_APPS('GeoIP');
if length(source_folder)=0 then begin
      writeln('Install GeoIP failed...');
      exit;
end;

SetCurrentDir(source_folder);
writeln('Current Directory:'+GetCurrentDir);
fpsystem('./configure');
fpsystem('make');
fpsystem('make install');

if FileExists('/usr/local/lib/libGeoIP.so') then begin
   writeln('libGeoIP looks good');
   exit(true);
end;

writeln('Install GeoIP failed...');
writeln('On Fedora make sure you have installed zlib "yum install zlib-devel"');
end;
//#########################################################################################
end.
