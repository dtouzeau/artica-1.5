unit setup_amanda;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,rdiffbackup,zsystem,
  install_generic;

  type
  amanda=class


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
   darClass:trdiffbackup;
   SYS:Tsystem;


public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor amanda.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
end;
//#########################################################################################
procedure amanda.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure amanda.xinstall();
var
local_int_version:integer;
remote_int_version:integer;
remote_str_version:string;
cmd:string;
smbclient:string;
begin


  install.INSTALL_PROGRESS('APP_AMANDA','{checking}');



  install.INSTALL_PROGRESS('APP_AMANDA','{downloading}');
  install.INSTALL_STATUS('APP_AMANDA',30);
  SetCurrentDir('/root');


  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('amanda');
  if not DirectoryExists(source_folder) then begin
     writeln('Install amanda failed...');
     install.INSTALL_PROGRESS('APP_AMANDA','{failed}');
     install.INSTALL_STATUS('APP_AMANDA',110);
     exit;
  end;

  smbclient:=SYS.LOCATE_GENERIC_BIN('smbclient');
  if length(smbclient)>0 then smbclient:='--with-smbclient='+smbclient;

  writeln('Install amanda extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_AMANDA',50);
  install.INSTALL_PROGRESS('APP_AMANDA','{compiling}');
  SetCurrentDir(source_folder);
  cmd:='./configure --prefix=/usr --bindir=/usr/sbin --libexecdir=/usr/lib/amanda --without-amlibexecdir --without-amperldir --sysconfdir=/etc --sharedstatedir=/var/lib --localstatedir=/var/lib';
  cmd:=cmd + ' --with-user=root --with-group=root --with-tcpportrange=50000,50100 --with-udpportrange=840,860 --with-debugging=/var/log/amanda --with-gnutar-listdir=/var/lib/amanda/gnutar-lists';
  cmd:=cmd + ' --with-index-server=localhost --with-bsd-security '+smbclient+' --with-amandahosts --with-ssh-security --with-bsdtcp-security --with-bsdudp-security --enable-s3-device';
  writeln(cmd);
  fpsystem(cmd);
  fpsystem('make');
  install.INSTALL_STATUS('APP_AMANDA',90);
  install.INSTALL_PROGRESS('APP_AMANDA','{installing}');
  fpsystem('make install');
  SetCurrentDir('/root');

  if FileExists('/usr/sbin/amadmin') then begin
     install.INSTALL_STATUS('APP_AMANDA',100);
     install.INSTALL_PROGRESS('APP_AMANDA','{installed}');
     fpsystem('/etc/init.d/artica-postfix restart amanda');
     exit;
  end;

  install.INSTALL_STATUS('APP_AMANDA',110);
  install.INSTALL_PROGRESS('APP_AMANDA','{failed}');

end;
//#########################################################################################


end.
