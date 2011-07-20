unit setup_mysqlserver;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,rdiffbackup,
  install_generic,zsystem;

  type
  mysql_server=class


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
   darClass:trdiffbackup;



public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor mysql_server.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
sys:=Tsystem.Create();
end;
//#########################################################################################
procedure mysql_server.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure mysql_server.xinstall();
var
local_int_version:integer;
remote_int_version:integer;
remote_str_version:string;
cmd:string;
begin


  install.INSTALL_PROGRESS('APP_MYSQL','{checking}');
  install.INSTALL_PROGRESS('APP_MYSQL','{downloading}');
  install.INSTALL_STATUS('APP_MYSQL',30);
  SetCurrentDir('/root');


  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('mysql-server');
  if not DirectoryExists(source_folder) then begin
     writeln('Install Mysql-server failed...');
     install.INSTALL_PROGRESS('APP_MYSQL','{failed}');
     install.INSTALL_STATUS('APP_MYSQL',110);
     exit;
  end;

  if not FileExists(SYS.LOCATE_GENERIC_BIN('cmake')) then begin
     writeln('Install Mysql-server failed... unable to stat cmake binary');
     install.INSTALL_PROGRESS('APP_MYSQL','{failed}');
     install.INSTALL_STATUS('APP_MYSQL',110);
     exit;
  end;
  writeln('Install mysql-server extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_MYSQL',50);
  install.INSTALL_PROGRESS('APP_MYSQL','{compiling}');
  SetCurrentDir(source_folder);
  forceDirectories('/var/run/mysqld');
  ForceDirectories('/var/lib/mysql');
  cmd:='cmake . -DMYSQL_DATADIR=/var/lib/mysql/ -DCMAKE_INSTALL_PREFIX=/usr -DINSTALL_LAYOUT=STANDALONE -DENABLED_PROFILING=ON';
  cmd:=cmd + ' -DMYSQL_MAINTAINER_MODE=OFF -DWITH_DEBUG=OFF -DINSTALL_LIBDIR=/usr/lib/ -DINSTALL_BINDIR=/usr/bin/ -DWITH_COMMENT=''(Artica)''';
  cmd:=cmd + ' -DINSTALL_SBINDIR=/usr/sbin/ -DINSTALL_SHAREDIR=share/mysql -DINSTALL_MYSQLSHAREDIR=share/mysql -DINSTALL_LAYOUT=STANDALONE -DENABLED_PROFILING=ON -DSYSCONFDIR=/etc/';
  cmd:=cmd + ' -DMYSQL_MAINTAINER_MODE=OFF -DWITH_DEBUG=OFF -DINSTALL_MANDIR=/usr/man/ -DWITH_ARCHIVE_STORAGE_ENGINE=1 -DMYSQL_USER=mysql ';
  cmd:=cmd + ' -DWITH_INNOBASE_STORAGE_ENGINE=1 -DDEFAULT_CHARSET=utf8 -DDEFAULT_COLLATION=utf8_general_ci -DMYSQL_UNIX_ADDR=/var/run/mysqld/mysqld.sock';
  cmd:=cmd + ' -DWITH_SSL=yes -DINSTALL_INFODIR=/usr/info/ -DENABLED_LOCAL_INFILE=1 -DWITH_EXTRA_CHARSETS=complex';

  writeln('********************************');
  writeln('');
  writeln(cmd);
  writeln('');
  writeln('********************************');
  fpsystem(cmd);
  fpsystem('make');
  install.INSTALL_STATUS('APP_MYSQL',80);
  install.INSTALL_PROGRESS('APP_MYSQL','{installing}');
  fpsystem('make install');
  SetCurrentDir('/root');
  install.INSTALL_STATUS('APP_MYSQL',90);
  install.INSTALL_PROGRESS('APP_MYSQL','{checking}');

if not FileExists(SYS.LOCATE_GENERIC_BIN('mysqld')) then begin
       install.INSTALL_PROGRESS('APP_MYSQL','{failed}');
       install.INSTALL_STATUS('APP_MYSQL',110);
       exit;
end;

install.INSTALL_PROGRESS('APP_MYSQL','{success}');
install.INSTALL_STATUS('APP_MYSQL',100);
forceDirectories('/var/run/mysqld');
ForceDirectories('/var/lib/mysql');

fpsystem('/etc/init.d/artica-postfix restart mysql');
fpsystem('/etc/init.d/artica-postfix restart mysql');

end;
//#########################################################################################


end.
