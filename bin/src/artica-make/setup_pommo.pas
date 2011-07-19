unit setup_pommo;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,
  install_generic,logs,obm,zsystem;

  type
  tpommo=class


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

constructor tpommo.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure tpommo.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tpommo.xinstall();
var
source_folder:string;
logs:Tlogs;
SYS:TSystem;
zobm:tobm;
begin

 logs:=Tlogs.Create;
 SYS:=Tsystem.Create();



if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS('APP_POMMO',10);
  install.INSTALL_PROGRESS('APP_POMMO','{downloading}');
source_folder:=libs.COMPILE_GENERIC_APPS('pommo');
  if not DirectoryExists(source_folder) then begin
     writeln('Install pommo failed...');
     install.INSTALL_STATUS('APP_POMMO',110);
     exit;
  end;
  install.INSTALL_STATUS('APP_POMMO',30);
  writeln('Install pommo extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_POMMO',50);
  forcedirectories('/usr/share/pommo');
  install.INSTALL_PROGRESS('APP_POMMO','{installing}');
  fpsystem('/bin/cp -rfv ' + source_folder + '/* /usr/share/pommo');
  fpsystem('/etc/init.d/artica-postfix restart apache');
  install.INSTALL_STATUS('APP_POMMO',100);
  install.INSTALL_PROGRESS('APP_POMMO','{installed}');




end;
//#########################################################################################


end.
