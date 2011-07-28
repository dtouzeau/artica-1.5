unit setup_drupal7;
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
  tsetup_drupal7=class


private
     libs:tlibs;
     distri:tdistriDetect;
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
   PROJECT_NAME:string;




public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure APP_UPLOAD_PROGRESS();
      procedure APP_DRUSH();
      procedure langupack();
END;

implementation

constructor tsetup_drupal7.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
SYS:=Tsystem.Create();
source_folder:='';
webserver_port:=install.lighttpd.LIGHTTPD_LISTEN_PORT();
   artica_admin:=install.openldap.get_LDAP('admin');
   artica_password:=install.openldap.get_LDAP('password');
   ldap_suffix:=install.openldap.get_LDAP('suffix');
   ldap_server:=install.openldap.get_LDAP('server');
   mysql_server:=install.SYS.MYSQL_INFOS('mysql_server');
   mysql_admin:=install.SYS.MYSQL_INFOS('database_admin');
   mysql_password:=install.SYS.MYSQL_INFOS('password');
   PROJECT_NAME:='APP_DRUPAL7';
end;
//#########################################################################################
procedure tsetup_drupal7.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_drupal7.xinstall();
var
source_folder:string;
logs:Tlogs;
SYS:TSystem;
cmd:string;
begin

 logs:=Tlogs.Create;
 SYS:=Tsystem.Create();
 source_folder:='';


if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS(PROJECT_NAME,10);

    install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading}');
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('drush7');
    writeln('directory extracted on "',source_folder,'"');
    install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');
    install.INSTALL_STATUS(PROJECT_NAME,15);
    forceDirectories('/usr/share/drush7');
    cmd:='/bin/cp -rf '+source_folder+'/* /usr/share/drush7/';
    writeln(cmd);
    fpsystem(cmd);
    if not fileExists('/usr/share/drush7/drush') then begin
       writeln('Install drush failed unable to stat "/usr/share/drush7/drush" ..');
       install.INSTALL_STATUS(PROJECT_NAME,110);
       exit;
    end;
    fpsystem('/bin/ln -s /usr/share/drush7/drush /usr/bin/drush7');

if not FileExists(SYS.LOCATE_GENERIC_BIN('drush7')) then begin
  writeln('Install drush failed unable to stat "drush7" binary file');
  install.INSTALL_STATUS(PROJECT_NAME,110);
  exit;
end;

    source_folder:='';
    install.INSTALL_STATUS(PROJECT_NAME,30);
    install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading}');
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('drupal7');

   if not DirectoryExists(source_folder) then begin
     writeln('Install drupal failed...');
     install.INSTALL_STATUS(PROJECT_NAME,110);
     exit;
   end;

   install.INSTALL_STATUS(PROJECT_NAME,40);
   install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');
   forceDirectories('/usr/share/drupal7');
   fpsystem('/bin/cp -rf '+source_folder+'/* /usr/share/drupal7/');
   if not FileExists('/usr/share/drupal7/index.php') then begin
      writeln('Install drupal failed unable to stat /usr/share/drupal7/index.php...');
      install.INSTALL_STATUS(PROJECT_NAME,110);
      exit;
   end;

  install.INSTALL_PROGRESS(PROJECT_NAME,'{installed}');
  install.INSTALL_STATUS(PROJECT_NAME,100);
  langupack();
  APP_UPLOAD_PROGRESS();
  fpsystem('/etc/init.d/artica-postfix restart daemon');

end;

//#########################################################################################
procedure tsetup_drupal7.APP_DRUSH();
var
local_int_version:integer;
remote_int_version:integer;
phpize:string;
cmd:string;
begin
 source_folder:='';
 PROJECT_NAME:='APP_DRUSH7';

if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS(PROJECT_NAME,10);

    install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading}');
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('drush7');
    writeln('directory extracted on "',source_folder,'"');
    install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');
    install.INSTALL_STATUS(PROJECT_NAME,15);
    forceDirectories('/usr/share/drush7');
    cmd:='/bin/cp -rf '+source_folder+'/* /usr/share/drush7/';
    writeln(cmd);
    fpsystem(cmd);
    if not fileExists('/usr/share/drush7/drush') then begin
       writeln('Install drush failed unable to stat "/usr/share/drush7/drush" ..');
        install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
       install.INSTALL_STATUS(PROJECT_NAME,110);
       exit;
    end;
    fpsystem('/bin/ln -s /usr/share/drush7/drush /usr/bin/drush7 >/dev/null 2>&1');
  install.INSTALL_STATUS(PROJECT_NAME,100);
  install.INSTALL_PROGRESS(PROJECT_NAME,'{installed}');
end;

//#########################################################################################
procedure tsetup_drupal7.APP_UPLOAD_PROGRESS();
var
local_int_version:integer;
remote_int_version:integer;
phpize:string;
cmd:string;
begin


  install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{checking}');
  phpize:=SYS.LOCATE_GENERIC_BIN('phpize');

  if not FileExists(phpize) then begin
   writeln('Install phpize no such binary failed...');
     install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{failed}');
     install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',110);
     exit;
  end;

  install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{downloading}');
  install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',30);
  SetCurrentDir('/root');


  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('uploadprogress');
  if not DirectoryExists(source_folder) then begin
     writeln('Install uploadprogress failed...');
     install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{failed}');
     install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',110);
     exit;
  end;


  writeln('Install uploadprogress extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',50);
  install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{compiling}');
  SetCurrentDir(source_folder);

  cmd:=phpize;
  writeln(cmd);
  fpsystem(cmd);
  install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',60);
  install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{compiling}');

  cmd:='./configure';
  writeln(cmd);
  fpsystem(cmd);
  install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',70);
  install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{compiling}');
  fpsystem('make');
  install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',80);
  install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{installing}');
  fpsystem('make install');
  SetCurrentDir('/root');

  install.INSTALL_PROGRESS('APP_UPLOAD_PROGRESS','{success}');
  install.INSTALL_STATUS('APP_UPLOAD_PROGRESS',100);
  fpsystem('/etc/init.d/artica-postfix restart apache');
  fpsystem('/etc/init.d/artica-postfix restart apachesrc');


end;
//#########################################################################################
procedure tsetup_drupal7.langupack();
begin


if not DirectoryExists('/usr/share/drupal/profiles/standard/translations') then begin
   writeln('/usr/share/drupal/profiles/standard/translations no such dirctory');
   exit;
end;
  source_folder:=libs.COMPILE_GENERIC_APPS('drupal-langpack');
  if length(source_folder)>0 then begin
     writeln('Extracting '+source_folder+'/* /usr/share/drupal/profiles/standard/translations/');
     fpsystem('/bin/cp -rf '+source_folder+'* /usr/share/drupal/profiles/standard/translations/');
  end;


end;
//#########################################################################################
end.
