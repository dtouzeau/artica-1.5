unit setup_openldap;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,
  setup_suse_class,postfix_class,zsystem,
  install_generic;

  type
  tsetup_openldap=class


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



public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor tsetup_openldap.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
end;
//#########################################################################################
procedure tsetup_openldap.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_openldap.xinstall();
var
   CODE_NAME:string;
   intversion:integer;
   remoteversion:string;
   remoteint:integer;
   include_openssl:string;
   include_sasl:string;
   include_cdb:string;
begin

    CODE_NAME:='APP_OPENLDAP';

  SetCurrentDir('/root');



  if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);

  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('openldap');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;





  writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

  SetCurrentDir(source_folder);

  cmd:='CFLAGS="-Wall -g -D_FILE_OFFSET_BITS=64 -D_GNU_SOURCE -O2"';
  cmd:=cmd+' ./configure --prefix=/usr --sysconfdir=/etc --libexecdir=/usr/sbin';
  cmd:=cmd+' --localstatedir=/var --mandir=''${prefix}/share/man'' --enable-debug --enable-dynamic';
  cmd:=cmd+' --enable-syslog --enable-proctitle --enable-ipv6 --enable-local --enable-slapd';
  cmd:=cmd+' --enable-dynacl --enable-aci --enable-cleartext --enable-crypt --disable-lmpasswd';
  cmd:=cmd+' --enable-spasswd --enable-modules --enable-rewrite --enable-rlookups --enable-slapi';
  cmd:=cmd+' --enable-slp --enable-wrappers --enable-backends=mod --disable-ndb --enable-overlays=mod --with-subdir=ldap';
  cmd:=cmd+' --with-cyrus-sasl --with-threads --with-tls=gnutls';
  writeln(cmd);
  fpsystem(cmd);

  fpsystem('make');

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make install');

  fpsystem('/etc/init.d/artica-postfix restart ldap');

  if FileExists(SYS.LOCATE_GENERIC_BIN('slpad')) then begin
     install.INSTALL_STATUS(CODE_NAME,100);
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     if FileExists(SYS.LOCATE_GENERIC_BIN('postfix')) then SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-make APP_POSTFIX');


  if FileExists(SYS.LOCATE_GENERIC_BIN('smbd')) then begin
     SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-make APP_SAMBA');
  end;

  if FileExists(SYS.LOCATE_GENERIC_BIN('squid')) then begin
     SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-make APP_SQUID');
     exit;
  end;

  if FileExists(SYS.LOCATE_GENERIC_BIN('squid3')) then begin
     SYS.THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-make APP_SQUID');
     exit;
  end;

  end else begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
  end;
  SetCurrentDir('/root');


end;
//#########################################################################################


end.
