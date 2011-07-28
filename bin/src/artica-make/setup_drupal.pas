unit setup_drupal;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,RegExpr in 'RegExpr.pas',
  unix,setup_libs,distridetect,
  install_generic,logs,obm,zsystem;

  type
  tsetup_drupal=class


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
   PROJECT_NAME:string;




public
      constructor Create();
      procedure Free;
      procedure xinstall();

END;

implementation

constructor tsetup_drupal.Create();
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
   PROJECT_NAME:='APP_DRUPAL';
end;
//#########################################################################################
procedure tsetup_drupal.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_drupal.xinstall();
var
source_folder:string;
logs:Tlogs;
SYS:TSystem;
zobm:tobm;
begin

 logs:=Tlogs.Create;
 SYS:=Tsystem.Create();
 source_folder:='';


if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS(PROJECT_NAME,10);

install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading}');

if not FileExists(SYS.LOCATE_GENERIC_BIN('drush')) then begin
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('drush');
    install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');

    install.INSTALL_STATUS(PROJECT_NAME,15);
    forceDirectories('/usr/share/drush');
    fpsystem('/bin/cp -rf '+source_folder+'/* /usr/share/drush/');
    if not fileExists('/usr/share/drush/drush') then begin
         writeln('Install drush failed unable to stat /usr/share/drush/drush...');
         install.INSTALL_STATUS(PROJECT_NAME,110);
         exit;
   end;

   fpsystem('/bin/ln -s /usr/share/drush/drush /usr/bin/drush');
end;

if not FileExists(SYS.LOCATE_GENERIC_BIN('drush')) then begin
  writeln('Install drush failed unable to stat /usr/share/drush/drush...');
  install.INSTALL_STATUS(PROJECT_NAME,110);
  exit;
end;

    source_folder:='';
    install.INSTALL_STATUS(PROJECT_NAME,30);
    install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading}');
    source_folder:=libs.COMPILE_GENERIC_APPS('drupal');

   if not DirectoryExists(source_folder) then begin
     writeln('Install drupal failed...');
     install.INSTALL_STATUS(PROJECT_NAME,110);
     exit;
   end;

   install.INSTALL_STATUS(PROJECT_NAME,40);
   install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');
   forceDirectories('/usr/share/drupal');
   fpsystem('/bin/cp -rf '+source_folder+'/* /usr/share/drupal/');
   if not FileExists('/usr/share/drupal/index.php') then begin
      writeln('Install drupal failed unable to stat /usr/share/drupal/index.php...');
      install.INSTALL_STATUS(PROJECT_NAME,110);
      exit;
   end;
    install.INSTALL_STATUS(PROJECT_NAME,50);
    source_folder:='';
    install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading}');
    install.INSTALL_STATUS(PROJECT_NAME,55);
    source_folder:=libs.COMPILE_GENERIC_APPS('drupalfr');
    if length(source_folder)>0 then begin
         install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');
         writeln('Extracting '+ExtractFilePath(source_folder)+'* /usr/share/drupal/');
         fpsystem('/bin/cp -rf '+ExtractFilePath(source_folder)+'* /usr/share/drupal/');
    end;


  if not FileExists('/usr/share/drupal/sites/all') then begin
         writeln('Install drupal failed unable to stat /usr/share/drupal/sites/all');
        install.INSTALL_STATUS(PROJECT_NAME,110);
        exit;
  end;

  install.INSTALL_STATUS(PROJECT_NAME,60);
  forceDirectories('/usr/share/drupal/sites/all/modules');
  source_folder:='';
  source_folder:=libs.COMPILE_GENERIC_APPS('drupaladm');
  if DirectoryExists(source_folder) then begin
       writeln('admin module sources in ',source_folder);
       fpsystem('/bin/cp -rf '+ExtractFilePath(source_folder)+'* /usr/share/drupal/sites/all/modules/');
  end;

  install.INSTALL_STATUS(PROJECT_NAME,70);
  source_folder:='';
  forceDirectories('/usr/share/drupal/sites/all/themes');
  source_folder:=libs.COMPILE_GENERIC_APPS('drupalzen');
  if DirectoryExists(source_folder) then begin
       writeln('zen module sources in ',source_folder);
       fpsystem('/bin/cp -rf '+ExtractFilePath(source_folder)+'* /usr/share/drupal/sites/all/themes/');
  end;

  install.INSTALL_STATUS(PROJECT_NAME,100);
  fpsystem('/etc/init.d/artica-postfix restart daemon');
  install.INSTALL_PROGRESS(PROJECT_NAME,'{installed}');
end;
//#########################################################################################






end.
