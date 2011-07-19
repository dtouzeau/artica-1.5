unit setup_miltergreylist;
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
  miltergreylist=class


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

constructor miltergreylist.Create();
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
procedure miltergreylist.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure miltergreylist.xinstall();
begin



if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS('APP_MILTERGREYLIST',10);



  install.INSTALL_STATUS('APP_MILTERGREYLIST',30);

  install.INSTALL_PROGRESS('APP_MILTERGREYLIST','{downloading}');
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('milter-greylist');
  if not DirectoryExists(source_folder) then begin
     writeln('Install milter-greylist failed...');
     install.INSTALL_STATUS('APP_MILTERGREYLIST',110);
     install.INSTALL_PROGRESS('APP_MILTERGREYLIST','{failed}');
     exit;
  end;
  writeln('Install simple milter-greylist extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_MILTERGREYLIST',50);
  
  if FileExists('/usr/lib/libmilter/libmilter.a') then fpsystem('ln -s /usr/lib/libmilter/libmilter.a /lib/libmilter.a');
    install.INSTALL_PROGRESS('APP_MILTERGREYLIST','{compiling}');
  fpsystem('cd ' + source_folder + ' && ./configure CFLAGS="-L/usr/lib/libmilter -L/lib -L/usr/lib -L/usr/local/lib" --enable-postfix && make && make install');
  if not FileExists('/usr/local/bin/milter-greylist') then begin
     install.INSTALL_STATUS('APP_MILTERGREYLIST',110);
    install.INSTALL_PROGRESS('APP_MILTERGREYLIST','{failed}');
     writeln('Failed...');
     exit;
  end else begin
     install.INSTALL_STATUS('APP_MILTERGREYLIST',100);
     install.INSTALL_PROGRESS('APP_MILTERGREYLIST','{installed}');

     fpsystem('/etc/init.d/artica-postfix restart mgreylist');
  end;
     
end;
//#########################################################################################




end.
