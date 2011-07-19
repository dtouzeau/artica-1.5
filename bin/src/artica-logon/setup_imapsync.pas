unit setup_imapsync;
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
  setup_ubuntu_class;

  type
  imapsync=class


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
   function xperl():boolean;
   function get_openssl_directorysources():string;
   function InstallImapcClient():boolean;



public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure install_mailsync();
END;

implementation

constructor imapsync.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
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
procedure imapsync.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure imapsync.xinstall();
begin



if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS('APP_IMAPSYNC',10);
install.INSTALL_STATUS('APP_IMAPSYNC',30);
     xperl();

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('imapsync');
  if not DirectoryExists(source_folder) then begin
     writeln('Install imapsync failed...');
     install.INSTALL_STATUS('APP_IMAPSYNC',110);
     exit;
  end;
  
  fpsystem('cd ' + source_folder + ' && make && make install');
  if FileExists('/usr/bin/imapsync') then begin
       install.INSTALL_STATUS('APP_IMAPSYNC',100);
  end else begin
       install.INSTALL_STATUS('APP_IMAPSYNC',110);
       writeln('failed');
  end;



end;
//#########################################################################################
procedure imapsync.install_mailsync();
begin

    install.INSTALL_STATUS('APP_MAILSYNC',30);
    if not InstallImapcClient() then begin
       writeln('Failed install mailsync');
       exit;
    end;
    
    source_folder:='';
    
    
    // /usr/local/bin/mailsync
    
    install.INSTALL_STATUS('APP_MAILSYNC',50);
    source_folder:=libs.COMPILE_GENERIC_APPS('mailsync');
    
  if not DirectoryExists(source_folder) then begin
     writeln('Install mailsync failed...');
     install.INSTALL_STATUS('APP_MAILSYNC',110);
     exit;
  end;
  
  fpsystem('cd ' + source_folder + ' && ./configure --with-c-client=/usr/local/include/c-client');
  install.INSTALL_STATUS('APP_MAILSYNC',60);
  fpsystem('cd ' + source_folder + ' && make');
  install.INSTALL_STATUS('APP_MAILSYNC',70);
  fpsystem('cd ' + source_folder + ' && make install');
  install.INSTALL_STATUS('APP_MAILSYNC',90);
  if FileExists('/usr/local/bin/mailsync') then begin
       install.INSTALL_STATUS('APP_MAILSYNC',100);
       writeln('success');
  end else begin
       install.INSTALL_STATUS('APP_MAILSYNC',110);
       writeln('failed');
  end;


end;
//#########################################################################################


function imapsync.xperl():boolean;
begin

result:=false;

install.INSTALL_STATUS('APP_IMAPSYNC',60);
  libs.PERL_GENERIC_INSTALL('Date-Manip','Date::Manip');
  libs.PERL_GENERIC_INSTALL('Mail-IMAPClient','Mail::IMAPClient');
  libs.PERL_GENERIC_INSTALL('Digest-MD5','Digest::MD5');
  libs.PERL_GENERIC_INSTALL('TermReadKey','Term::ReadKey');
  libs.PERL_GENERIC_INSTALL('IO-Socket-SSL','IO::Socket::SSL');
end;
//#########################################################################################
function imapsync.InstallImapcClient():boolean;
var
   openssl_src_dir:string;
   SPECIALS:string;
   compile_string:string;

begin
result:=false;
openssl_src_dir:=get_openssl_directorysources();


if FileExists('/usr/local/include/c-client/c-client.a') then begin
      writeln('/usr/local/include/c-client/c-client.a exists, Install c-client already installed');
      exit(true);
end;


if length(openssl_src_dir)=0 then begin
    writeln('Unable to locate dir for openssl/crypto.h');
    install.INSTALL_STATUS('APP_MAILSYNC',110);
    exit;
end;


source_folder:=libs.COMPILE_GENERIC_APPS('imap');

  if not DirectoryExists(source_folder) then begin
     writeln('Install c-client failed...');
     install.INSTALL_STATUS('APP_MAILSYNC',110);
     exit;
  end;


  SPECIALS:='SPECIALS="SSLINCLUDE='+openssl_src_dir+' SSLLIB=/usr/lib SSLCERTS=/etc/ssl/certs SSLKEYS=/etc/ssl/private"';
  compile_string:='cd ' + source_folder + ' && make slx ' + SPECIALS;
  fpsystem(compile_string);
  
  

         if not FileExists(source_folder + '/c-client/c-client.a') then begin
            install.INSTALL_STATUS('APP_MAILSYNC',110);
            writeln('Error installing c-client (unable to stat ' + source_folder + '/c-client/c-client.a)');
            readln();
            exit;
         end;


         if not FileExists(source_folder + '/c-client/os_lnx.h') then begin
            install.INSTALL_STATUS('APP_MAILSYNC',110);
            writeln('Error installing c-client (unable to stat ' + source_folder + '/c-client/os_lnx.h)');
            readln();
            exit;
         end;


         ForceDirectories('/usr/local/include/c-client');
         
         writeln('Installing c-client.a and h files....');
         fpsystem('/bin/cp -rf ' + source_folder + '/c-client/* /usr/local/include/c-client/');

      if FileExists('/usr/local/include/c-client/c-client.a') then begin
         writeln('/usr/local/include/c-client/c-client.a exists, Install c-client is installed');
         exit(true);
      end else begin
          writeln('Unable to stat /usr/local/include/c-client/c-client.a');
          exit(false);
      end;


 result:=true;
end;
//#########################################################################################

function imapsync.get_openssl_directorysources():string;
begin
    if FileExists('/usr/include/openssl/crypto.h') then exit('/usr/include/openssl');
end;
//#########################################################################################




end.
