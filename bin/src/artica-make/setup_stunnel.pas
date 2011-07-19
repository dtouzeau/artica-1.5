unit setup_stunnel;
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
  tsetup_stunnel=class


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

constructor tsetup_stunnel.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure tsetup_stunnel.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_stunnel.xinstall();
var
   CODE_NAME:string;

begin

    CODE_NAME:='APP_STUNNEL';
    SetCurrentDir('/root');

if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS(CODE_NAME,10);


  fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-base-system');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('stunnel');
  if not DirectoryExists(source_folder) then begin
     writeln('Install stunnel failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;
  writeln('Install stunnel extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
    install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

  SetCurrentDir(source_folder);
  cmd:='./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info ';
  cmd:=cmd + ' --localstatedir=/var --enable-ssllib-cs --sysconfdir=/etc';
  cmd:=cmd + ' --with-cert-dir=/etc/ssl/certs --with-pem-dir=/etc/ssl/certs';
  cmd:=cmd + ' --enable-ipv6 --with-threads=pthread';


  writeln('Using ' + cmd);
  fpsystem(cmd);
  fpsystem('make');

  fpsystem('/bin/rm -f '+ source_folder+'/tools/Makefile');

  fpsystem('make install');


  if FileExists('/usr/bin/stunnel') then begin
      install.INSTALL_STATUS(CODE_NAME,100);
      writeln('Success');
    install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
  end else begin
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      install.INSTALL_STATUS(CODE_NAME,110);
      writeln('Failed');
  end;

  SetCurrentDir('/root');

end;
//#########################################################################################


end.
