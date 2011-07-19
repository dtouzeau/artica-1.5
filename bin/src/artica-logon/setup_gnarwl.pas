unit setup_gnarwl;
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
  gnarwl=class


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

constructor gnarwl.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure gnarwl.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure gnarwl.xinstall();
var
   CODE_NAME:string;

begin

    CODE_NAME:='APP_GNARWL';
    SetCurrentDir('/root');

if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS('APP_GNARWL',10);


  fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-base-system');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('gnarwl');
  if not DirectoryExists(source_folder) then begin
     writeln('Install gnrawl failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;
  writeln('Install gnarwl extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
    install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

  SetCurrentDir(source_folder);
  cmd:='./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info ';
  cmd:=cmd + ' --with-docdir=/usr/share/doc/gnarwl';
  cmd:=cmd + ' --with-homedir=/var/lib/gnarwl';
  cmd:=cmd + ' --sysconfdir=/etc';
  cmd:=cmd + ' --with-mta=/usr/sbin/sendmail';

  writeln('Using ' + cmd);
  fpsystem(cmd);
  fpsystem('make');
  fpsystem('make install');


  if FileExists('/usr/bin/gnarwl') then begin
      install.INSTALL_STATUS(CODE_NAME,100);
      writeln('Success');
    install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
    fpsystem('/etc/init.d/artica-postfix restart postfix');
    fpsystem('/etc/init.d/artica-postfix restart ldap');
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
