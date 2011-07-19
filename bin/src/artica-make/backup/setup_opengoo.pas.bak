unit setup_opengoo;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,postfix_class,zsystem,logs,
  install_generic;

  type
  setupopengoo=class


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
   postfix:tpostfix;
   SYS:Tsystem;
   LOGS:Tlogs;
   CountInstall:integer;
   function LOCATE_APXS2():string;
   function BuildPhpCOnfigure(withapache:boolean):string;


public
      constructor Create();
      procedure Free;
      procedure xinstall();
      function apacheinstall():boolean;
      function phpinstall():boolean;
      function CC_CLIENT_INSTALL():boolean;
      procedure PHP_STANDARD_INSTALL();
      procedure GROUPOFFICE_INSTALL();
      function APP_AMACHI_INSTALL():boolean;
END;

implementation

constructor setupopengoo.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
logs:=Tlogs.CReate;
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
CountInstall:=0;
end;
//#########################################################################################
procedure setupopengoo.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure setupopengoo.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   mysql_server,root,password:string;
begin

    CODE_NAME:='APP_OPENGOO';

  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
   install.INSTALL_STATUS(CODE_NAME,30);
   install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
 if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('opengoo');
  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;


   install.INSTALL_STATUS(CODE_NAME,50);
   install.INSTALL_PROGRESS(CODE_NAME,'{installing}');       

   forceDirectories('/usr/local/share/artica/opengoo');
   fpsystem('/bin/cp -rfv '+source_folder+'/* /usr/local/share/artica/opengoo/');

   if FileExists('/usr/local/share/artica/opengoo/version.php') then begin
     writeln('Install '+CODE_NAME+' success...');
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
   end else begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     install.INSTALL_STATUS(CODE_NAME,110);
   end;


end;
//#########################################################################################
procedure setupopengoo.GROUPOFFICE_INSTALL();
var
   CODE_NAME:string;
   cmd:string;
   mysql_server,root,password:string;
begin

    CODE_NAME:='APP_GROUPOFFICE';

  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS('APP_GROUPOFFICE','{downloading}');
 if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('groupoffice-com');
  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;


   install.INSTALL_STATUS(CODE_NAME,50);
   install.INSTALL_PROGRESS(CODE_NAME,'{installing}');

   forceDirectories('/usr/local/share/artica/group-office');
   fpsystem('/bin/cp -rfv '+source_folder+'/* /usr/local/share/artica/group-office/');

   if FileExists('/usr/local/share/artica/group-office/about.php') then begin
     writeln('Install '+CODE_NAME+' success...');
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
   end else begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     install.INSTALL_STATUS(CODE_NAME,110);
   end;


end;
//#########################################################################################

function setupopengoo.apacheinstall():boolean;
var
   CODE_NAME:string;
   cmd:string;
begin

result:=false;
CODE_NAME:='APP_GROUPWARE_APACHE';
if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('httpd');
  if not DirectoryExists(source_folder) then begin
     writeln('Install apache failed...');
     exit;
  end;
   install.INSTALL_STATUS(CODE_NAME,40);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  writeln('Install apache extracted on "'+source_folder+'"');
  SetCurrentDir(source_folder);

  cmd:='./configure --prefix=/usr/local/apache-groupware --datadir=/usr/local/apache-groupware/data --htmldir=/usr/local/apache-groupware/doc --with-port=8081 --with-sslport=8082 --with-program-name=apache-groupware';
  cmd:=cmd+' --enable-modules=all --enable-mods-shared=all';
  writeln(cmd);
  install.INSTALL_STATUS(CODE_NAME,45);
  fpsystem(cmd);
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  fpsystem('make && make install');

  if FileExists('/usr/local/apache-groupware/bin/apache-groupware') then begin
     install.INSTALL_STATUS(CODE_NAME,100);
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     result:=true;
  end else begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     result:=true;

  end; 

end;
//#########################################################################################
function setupopengoo.APP_AMACHI_INSTALL():boolean;
var
   CODE_NAME:string;
   cmd:string;
begin
result:=false;
CODE_NAME:='APP_AMACHI';

if not FIleExists('/dev/net/tun') then begin
     forceDirectories('/dev/net');
     fpsystem('mknod /dev/net/tun c 10 200');
end;

if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('hamachi');
  if not DirectoryExists(source_folder) then begin
     writeln('Install hamachi failed...');
     exit;
  end;

  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  writeln('Install hamachi extracted on "'+source_folder+'"');
  SetCurrentDir(source_folder);
  fpsystem('make install');
  if not FileExists('/usr/bin/hamachi') then begin
    install.INSTALL_STATUS(CODE_NAME,110);
    install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
    result:=false;
    exit;
  end;

  if FileExists('/usr/bin/upx') then fpsystem('/usr/bin/upx -d /usr/bin/hamachi');
  fpsystem('/sbin/tuncfg');
  fpsystem('/usr/bin/hamachi-init -c /etc/hamachi');
  fpsystem('/etc/init.d/artica-postfix start hamachi');


end;




function setupopengoo.phpinstall():boolean;
var
   CODE_NAME:string;
   cmd:string;
begin

result:=false;
CODE_NAME:='APP_GROUPWARE_PHP';

fpsystem('/bin/touch /usr/share/artica-postfix/ressources/install/APP_PHP.time');
if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('php');
  if not DirectoryExists(source_folder) then begin
     writeln('Install php5 failed...');
     exit;
  end;


  if not FileExists('/usr/include/c-client/c-client.h') then begin
      if not CC_CLIENT_INSTALL() then begin
         install.INSTALL_STATUS(CODE_NAME,110);
         install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
         result:=false;
         exit;
      end;
  end;


  if not FileExists('/usr/lib/libc-client.a') then begin
      if not CC_CLIENT_INSTALL() then begin
         install.INSTALL_STATUS(CODE_NAME,110);
         install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
         result:=false;
         exit;
      end;
  end;




  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  writeln('Install php extracted on "'+source_folder+'"');
  SetCurrentDir(source_folder);

  //--with-imap=/usr/local/imap-2007


  cmd:='./configure --prefix=/usr/local/apache-groupware/php5';
  cmd:=cmd+' --with-mysql=/usr/bin/mysql_config';
  cmd:=cmd+' --with-mysqli=/usr/bin/mysql_config';
  cmd:=cmd+' --with-png-dir=/usr';
  cmd:=cmd+' --with-gd';
  cmd:=cmd+' --enable-gd-native-ttf';
  cmd:=cmd+' --with-ttf';
  cmd:=cmd+' --enable-safe-mode';
  cmd:=cmd+' --enable-magic-quotes';
  cmd:=cmd+' --with-pspell';
  cmd:=cmd+' --with-gettext';
  cmd:=cmd+' --with-jpeg-dir=/usr';
  cmd:=cmd+' --with-zlib';
  cmd:=cmd+' --with-curl';
  cmd:=cmd+' --enable-soap';
  cmd:=cmd+' --with-ldap=/usr';
  cmd:=cmd+' --enable-sockets';
  cmd:=cmd+' --with-openssl';
  cmd:=cmd+' --enable-mbregex';
  cmd:=cmd+' --enable-mbstring';
  cmd:=cmd+' --enable-shmop';
  cmd:=cmd+' --enable-sysvsem';
  cmd:=cmd+' --enable-sysvshm';
  cmd:=cmd+' --with-freetype-dir=/usr';
  cmd:=cmd+' --enable-cli';
  cmd:=cmd+' --with-xpm-dir=/usr';
  cmd:=cmd+' --with-imap=/usr --with-imap-ssl';
  cmd:=cmd+' --with-kerberos';
  cmd:=cmd+' --with-apxs2=/usr/local/apache-groupware/bin/apxs';
  writeln(cmd);
  install.INSTALL_STATUS(CODE_NAME,60);
  fpsystem(cmd);
  install.INSTALL_STATUS(CODE_NAME,65);
  fpsystem('make && make install');

  if FileExists('/usr/local/apache-groupware/php5/bin/php') then begin
     install.INSTALL_STATUS(CODE_NAME,100);
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     result:=true;
  end else begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     result:=false;
  end;

end;
//##############################################################################
function setupopengoo.LOCATE_APXS2():string;
begin
if FileExists('/usr/bin/apxs2') then exit('/usr/bin/apxs2');
if FileExists('/usr/sbin/apxs2') then exit('/usr/sbin/apxs2');
if FileExists('/usr/local/sbin/apxs2') then exit('/usr/local/sbin/apxs2');
if FileExists('/usr/local/bin/apxs2') then exit('/usr/local/bin/apxs2');
if FIleExists('/usr/sbin/apxs') then exit('/usr/sbin/apxs');
end;
//##############################################################################
procedure setupopengoo.PHP_STANDARD_INSTALL();
var
   CODE_NAME:string;
   cmd:string;
   apxs2:string;
   sdate:string;
   l:Tstringlist;
   i:integer;
begin
  CODE_NAME:='APP_PHP';
  writeln('Install php with '+ cmd);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');


  cmd:=BuildPhpCOnfigure(false);
  writeln(cmd);
  if length(cmd)=0 then begin
     writeln('error no configuration..');
     exit;
  end;

if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('php');
  if not DirectoryExists(source_folder) then begin
     writeln('Install php5 failed...');
     exit;
  end;

  ForceDirectories('/root/php-install');
  fpsystem('/bin/cp -rf '+source_folder+'/* /root/php-install');

if Not FIleExists('/usr/share/file/magic.mime') then begin
   forceDirectories('/usr/share/file');
   fpsystem('/bin/cp /usr/share/artica-postfix/bin/install/magic.mime /usr/share/file/magic.mime');
end;

if Not FIleExists('/usr/share/file/magic.mime') then begin
   writeln('unable to stat /usr/share/file/magic.mime');
        install.INSTALL_STATUS(CODE_NAME,110);
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       exit;
end;


  SetCurrentDir('/root/php-install');
  fpsystem(cmd);
  fpsystem('make && make install');
  fpsystem('/etc/init.d/artica-postfix restart apache');

     install.INSTALL_STATUS(CODE_NAME,100);
     install.INSTALL_PROGRESS(CODE_NAME,'{compiled}');
     fpsystem('/bin/touch /usr/share/artica-postfix/ressources/install/APP_PHP.time');

end;
//##############################################################################
function setupopengoo.BuildPhpCOnfigure(withapache:boolean):string;
var
   CODE_NAME:string;
   cmd:string;
   apxs2:string;
   sdate:string;
   l:Tstringlist;
   i:integer;
   configurelcc:string;
begin
configurelcc:='';
CODE_NAME:='APP_PHP';
  install.INSTALL_STATUS(CODE_NAME,40);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

if withapache then begin
   apxs2:=LOCATE_APXS2();

   if FIleExists('/usr/local/apache-groupware/conf/apache-groupware.conf') then begin
      ForceDirectories('/etc/apache2/httpd.conf');
      fpsystem('/bin/cp /usr/local/apache-groupware/conf/apache-groupware.conf /etc/apache2/httpd.conf');
   end;

   if not FileExists(apxs2) then begin
      writeln('Unable to stat apxs2');
      fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-base-php');
      apxs2:=LOCATE_APXS2();
   end;
   if not FileExists(apxs2) then begin
      install.INSTALL_STATUS(CODE_NAME,110);
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      exit;
   end;
end else begin
    fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-base-php');
end;



l:=Tstringlist.Create;

l.Clear;

if Not FIleExists('/usr/share/file/magic.mime') then begin
   forceDirectories('/usr/share/file');
   fpsystem('/usr/share/artica-postfix/bin/install/magic.mime /usr/share/file/magic.mime');
end;

 if FIleExists('/usr/lib/mysql/libmysqlclient.so') then begin
       // configurelcc:=' LD_LIBRARY_PATH="/lib:/usr/local/lib:/usr/lib/libmilter:/usr/lib:/usr/lib/mysql" ';
      // configurelcc:=configurelcc+'CPPFLAGS="-I/usr/include/libmilter -I/usr/include -I/usr/local/include" ';
      // configurelcc:=configurelcc + 'LDFLAGS="-L/lib -L/usr/local/lib -L/usr/lib/libmilter -L/usr/lib -L/usr/lib/mysql" ';
      if not FIleExists('/usr/lib/libmysqlclient.so') then fpsystem('ln -s /usr/lib/mysql/libmysqlclient.so /usr/lib/libmysqlclient.so');
 end;

l.add('./configure --prefix=/usr');
if withapache then begin
   l.add(' --with-apxs2='+apxs2);
   l.add(' --with-config-file-path=/etc/php5/apache2');
   l.add(' --with-config-file-scan-dir=/etc/php5/apache2/conf.d');
end else begin
    writeln('Build configuration for php-cgi');
    l.add(' --enable-force-cgi-redirect');
    l.add(' --enable-fastcgi');
end;
        

l.add(' --sysconfdir=/etc');
l.add(' --mandir=/usr/share/man');
l.add(' --disable-debug');
l.add(' --with-regex=php');
l.add(' --disable-rpath');
l.add(' --disable-static');
l.add(' --with-pic');
l.add(' --with-layout=GNU');
l.add(' --with-pear=/usr/share/php');
l.add(' --enable-calendar');
l.add(' --enable-sysvsem');
l.add(' --enable-sysvshm');
l.add(' --enable-sysvmsg');
l.add(' --enable-bcmath');
l.add(' --with-bz2');
l.add(' --enable-ctype');
l.add(' --with-db4');
l.add(' --without-gdbm');
l.add(' --with-iconv');
l.add(' --enable-exif');
l.add(' --enable-ftp');
l.add(' --with-gettext');
l.add(' --enable-mbstring');
l.add(' --with-pcre-regex=/usr');
l.add(' --enable-shmop');
l.add(' --enable-sockets');
l.add(' --enable-wddx');
l.add(' --with-libxml-dir=/usr');
l.add(' --with-zlib');
l.add(' --with-kerberos=/usr');
l.add(' --with-openssl=/usr');
l.add(' --with-ldap');
l.add(' --enable-soap');
l.add(' --enable-zip');
l.add(' --with-mime-magic=/usr/share/file/magic.mime');
l.add(' --with-exec-dir=/usr/lib/php5/libexec');
l.add(' --with-system-tzdata');
l.add(' --without-mm');
l.add(' --with-curl=/usr');
l.add(' --with-zlib-dir=/usr');
l.add(' --with-gd=/usr');
l.add(' --enable-gd-native-ttf');
l.add(' --with-gmp=/usr');
l.add(' --with-jpeg-dir=/usr');
l.add(' --with-xpm-dir=/usr/X11R6');
l.add(' --with-png-dir=/usr');
l.add(' --with-freetype-dir=/usr');
l.add(' --with-ttf=/usr');
l.add(' --with-t1lib=/usr');
l.add(' --with-ldap=/usr');
l.add(' --with-ldap-sasl=/usr');
l.add(' --with-mhash=/usr');
l.add(' --with-mysql=/usr');
l.add(' --with-mysqli=/usr/bin/mysql_config');
l.add(' --with-pspell=/usr');
l.add(' --with-unixODBC=/usr');
//l.add(' --with-recode=shared,/usr');
l.add(' --with-xsl=/usr');
l.add(' --with-snmp=/usr');
//l.add(' --with-sqlite=/usr');
//l.add(' --with-mssql=shared,/usr');
l.add(' --with-tidy=/usr');
l.add(' --with-xmlrpc=shared');
l.add(' --with-pgsql=/usr');
l.add(' '+configurelcc);

for i:=0 to l.Count-1 do begin
  cmd:=cmd+l.Strings[i]
end;

result:=cmd;

end;


function setupopengoo.CC_CLIENT_INSTALL():boolean;

var
   CODE_NAME:string;
   cmd:string;
   l:Tstringlist;
   i:integer;

begin





CODE_NAME:='APP_GROUPWARE_PHP';
install.INSTALL_STATUS(CODE_NAME,40);
install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
if length(source_folder)=0 then begin
   libs:=Tlibs.Create();
   writeln('Artica version:',libs.ARTICA_VERSION());
   source_folder:=libs.COMPILE_GENERIC_APPS('imap');
end;


  if not DirectoryExists(source_folder) then begin
     writeln('Install imap library failed...');
     exit;
  end;

  install.INSTALL_STATUS(CODE_NAME,45);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  writeln('Install imap extracted on "'+source_folder+'"');
  SetCurrentDir(source_folder);

writeln('Install imap Settings make  on "'+source_folder+'"');

if not FileExists(source_folder+'/src/osdep/unix/Makefile') then begin
   writeln('Install imap unable to stat '+source_folder+'/src/osdep/unix/Makefile');
   exit;
end;

libs.CHANGE_MAKE_CONFIG('SSLDIR','/etc/ssl',source_folder+'/src/osdep/unix/Makefile');
libs.CHANGE_MAKE_CONFIG('SSLCERTS','$(SSLDIR)/certs',source_folder+'/src/osdep/unix/Makefile');
libs.CHANGE_MAKE_CONFIG('SSLKEYS','$(SSLCERTS)',source_folder+'/src/osdep/unix/Makefile');
libs.CHANGE_MAKE_CONFIG('SSLINCLUDE','/usr/include/openssl',source_folder+'/src/osdep/unix/Makefile');
libs.CHANGE_MAKE_CONFIG('SSLLIB','/usr/lib',source_folder+'/src/osdep/unix/Makefile');

fpsystem('make slx');


if not FileExists(source_folder+'/c-client/c-client.a') then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     writeln('Compilation failed');
end;

fpsystem('/bin/cp -v '+source_folder+'/c-client/c-client.a /usr/lib/libc-client.a');
fpsystem('/bin/cp -v '+source_folder+'/c-client/c-client.a /lib/libc-client.a');
fpsystem('/bin/cp -v '+source_folder+'/c-client/c-client.a /usr/lib/c-client.a');

l:=tstringlist.Create;
l.add('auths.c');
l.add('c-client.h');
l.add('dummy.h');
l.add('env.h');
l.add('env_unix.h');
l.add('fdstring.h');
l.add('flockcyg.h');
l.add('flocksim.h');
l.add('flstring.h');
l.add('fs.h');
l.add('ftl.h');
l.add('imap4r1.h');
l.add('linkage.c');
l.add('linkage.h');
l.add('mail.h');
l.add('maildir.h');
l.add('misc.h');
l.add('netmsg.h');
l.add('newsrc.h');
l.add('nl.h');
l.add('nntp.h');
l.add('os_a32.h');
l.add('os_a41.h');
l.add('os_aix.h');
l.add('os_aos.h');
l.add('os_art.h');
l.add('os_asv.h');
l.add('os_aux.h');
l.add('os_bsd.h');
l.add('os_bsf.h');
l.add('os_bsi.h');
l.add('os_cvx.h');
l.add('os_cyg.h');
l.add('os_d-g.h');
l.add('os_do4.h');
l.add('os_drs.h');
l.add('os_dyn.h');
l.add('os_hpp.h');
l.add('os_isc.h');
l.add('os_lnx.h');
l.add('os_lyn.h');
l.add('os_mct.h');
l.add('os_mnt.h');
l.add('os_nto.h');
l.add('os_nxt.h');
l.add('os_os4.h');
l.add('os_osf.h');
l.add('os_osx.h');
l.add('os_ptx.h');
l.add('os_pyr.h');
l.add('os_qnx.h');
l.add('os_s40.h');
l.add('os_sc5.h');
l.add('os_sco.h');
l.add('os_sgi.h');
l.add('os_shp.h');
l.add('os_slx.h');
l.add('os_soln.h');
l.add('os_solo.h');
l.add('os_sos.h');
l.add('os_sua.h');
l.add('os_sun.h');
l.add('os_sv2.h');
l.add('os_sv4.h');
l.add('os_ult.h');
l.add('os_vu2.h');
l.add('osdep.h');
l.add('pseudo.h');
l.add('rfc822.h');
l.add('smtp.h');
l.add('sslio.h');
l.add('tcp.h');
l.add('tcp_unix.h');
l.add('unix.h');
l.add('utf8.h');
l.add('utf8aux.h');

  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');

forceDirectories('/usr/include/c-client');

for i:=0 to l.Count-1 do begin
    if FileExists(source_folder+'/c-client/'+l.Strings[i]) then begin
       writeln('Installing '+l.Strings[i]);
       fpsystem('/bin/cp '+source_folder+'/c-client/'+l.Strings[i]+' /usr/include/c-client/'+l.Strings[i]);
    end else begin
       writeln('Unable to stat  '+source_folder+'/c-client/'+l.Strings[i]);
    end;
end;

if FileExists('/sbin/ldconfig') then fpsystem('/sbin/ldconfig');
fpsystem('/etc/init.d/artica-postfix restart apache-groupware');
result:=true;


{cd /path/to/imap/source/
make <system type> (ldb, lnx, etc)
mkdir include
mkdir lib

2. Make links in IMAP source directory:

cd /path/to/imap/source/c-client
cp *.h ../include/
cp *.c ../lib/
cp c-client.a ../lib/libc-client.a

3. Compile PHP with SSL support,  --with-imap=/path/to/imap/source/ . If SSL support fails, you'll get a configure-time error that IMAP doesn't work. This is a lie, you just need to get SSL support working in PHP. On certain linux systems, with OpenSSL 0.9.7, this means adding --with-openssl=/usr (if the OpenSSL files are in /usr/include/openssl/)
instead of the proper directory containing the OpenSSL files.
For some reason, giving a parent directory makes PHP able to find the OpenSSL include files.

http://fr2.php.net/manual/fr/ref.imap.php}


end;





end.
