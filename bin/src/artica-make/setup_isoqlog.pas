unit setup_isoqlog;
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
  isoqlog=class


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

public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor isoqlog.Create();
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
procedure isoqlog.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure isoqlog.xinstall();
begin

  SetCurrentDir('/tmp');

if FileExists('/usr/local/bin/isoqlog') then begin
   install.INSTALL_STATUS('APP_ISOQLOG',100);
   writeln('Install isoqlog already installed...');
   exit;
end;
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS('APP_ISOQLOG',10);
install.INSTALL_STATUS('APP_ISOQLOG',30);

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('isoqlog');
  if not DirectoryExists(source_folder) then begin
     writeln('Install isoqlog failed...');
     install.INSTALL_STATUS('APP_ISOQLOG',110);
     exit;
  end;
  SetCurrentDir(source_folder);
  fpsystem('./configure && make && make install');
  if FileExists('/usr/local/bin/isoqlog') then begin
       install.INSTALL_STATUS('APP_ISOQLOG',100);
  end else begin
       install.INSTALL_STATUS('APP_ISOQLOG',110);
       writeln('failed');
  end;



end;
//#########################################################################################


end.
