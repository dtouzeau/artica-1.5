unit setup_mysqlnd;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,zsystem,
  install_generic;

  type
  tsetup_mysqlnd=class


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




public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor tsetup_mysqlnd.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.create;
end;
//#########################################################################################
procedure tsetup_mysqlnd.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_mysqlnd.xinstall();
var
local_int_version:integer;
Arch:integer;
apt_get_path,sedpath,phpizebin,cmd:string;

begin


install.INSTALL_PROGRESS('APP_PHP5_MYSQLND','{checking}');
Arch:=libs.ArchStruct();
writeln('RESULT.................: Architecture : ',Arch);
writeln('RESULT.................: Distribution : ',distri.DISTRINAME,' (DISTRINAME)');
writeln('RESULT.................: Major version: ',distri.DISTRI_MAJOR,' (DISTRI_MAJOR)');
writeln('RESULT.................: Artica Code  : ',distri.DISTRINAME_CODE,' (DISTRINAME_CODE)');
apt_get_path:=SYS.LOCATE_GENERIC_BIN('apt-get');
sedpath:=SYS.LOCATE_GENERIC_BIN('sed');
phpizebin:=SYS.LOCATE_GENERIC_BIN('phpize');
if not FIleExists(apt_get_path) then begin
     writeln('Distribution ', distri.DISTRINAME,' not supported');
     install.INSTALL_PROGRESS('APP_PHP5_MYSQLND','{failed}');
     install.INSTALL_STATUS('APP_PHP5_MYSQLND',110);
     exit;
end;
if not FIleExists(sedpath) then begin
     writeln('Distribution sed not such binary');
     install.INSTALL_PROGRESS('APP_PHP5_MYSQLND','{failed}');
     install.INSTALL_STATUS('APP_PHP5_MYSQLND',110);
     exit;
end;
if not FIleExists(phpizebin) then begin
     writeln('Distribution phpize not such binary');
     install.INSTALL_PROGRESS('APP_PHP5_MYSQLND','{failed}');
     install.INSTALL_STATUS('APP_PHP5_MYSQLND',110);
     exit;
end;

  install.INSTALL_PROGRESS('APP_PHP5_MYSQLND','{downloading}');
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',35);
  if DirectoryExists('/root/php5-install') then begin
     writeln('Cleaning working directory...');
     fpsystem('/bin/rm -rf /root/php5-install');
  end;
  forceDirectories('/root/php5-install');
  SetCurrentDir('/root/php5-install');
  fpsystem('apt-get source php5');
  libs.DirDir('/root/php5-install');
  if libs.DirListFiles.Count=0 then begin
     writeln('/root/php5-install no such directories');
     install.INSTALL_PROGRESS('APP_PHP5_MYSQLND','{failed}');
     install.INSTALL_STATUS('APP_PHP5_MYSQLND',110);
     exit;
  end;

  source_folder:='/root/php5-install/'+libs.DirListFiles.Strings[0];
  writeln('Working directory on '+source_folder);
  if FileExists(source_folder+'/ext/mysqlnd/config9.m4') then fpsystem('/bin/mv '+source_folder+'/ext/mysqlnd/config9.m4 '+source_folder+'/ext/mysqlnd/config.m4');
  SetCurrentDir(source_folder+'/ext/mysqlnd');
  fpsystem(sedpath+' -ie "s{ext/mysqlnd/php_mysqlnd_config.h{config.h{" mysqlnd_portability.h');
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',42);
  install.INSTALL_PROGRESS('APP_PHP5_MYSQLND','{compiling}');
  writeln(' *** PHPIZE IN /ext/mysqlnd ***');
  fpsystem(phpizebin);
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',43);
   writeln(' *** CONFIGURE IN /ext/mysqlnd ***');
  fpsystem('./configure');
  SetCurrentDir(source_folder+'/ext/mysql');
  fpsystem(phpizebin);
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',44);
  cmd:='./configure --with-mysql='+source_folder+'/ext/mysqlnd';
  writeln('*** ' + cmd +' ***');
  fpsystem(cmd);
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',45);
  fpsystem('make && make install');
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',50);
  writeln('*** chdir ' + source_folder+'/ext/mysqli ***');
  SetCurrentDir(source_folder+'/ext/mysqli');
  fpsystem(phpizebin);
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',55);
  cmd:='./configure --with-mysqli=mysqlnd CPPFLAGS="-I'+source_folder+'"';
  writeln('*** ' + cmd +' ***');
  fpsystem(cmd);
  install.INSTALL_STATUS('APP_PHP5_MYSQLND',60);
  fpsystem('make && make install');
  writeln('*** chdir ' + source_folder+' ***');
  writeln('An what next ????');

  SetCurrentDir(source_folder);
 // cmd:='./configure --with-mysql=mysqlnd --with-mysqli=mysqlnd --with-pdo-mysql=mysqlnd CPPFLAGS="-I'+source_folder+'/ext/mysqlnd"';
 // writeln('*** ' + cmd +' ***');
 // fpsystem('make && make install');


end;
//#########################################################################################


end.
