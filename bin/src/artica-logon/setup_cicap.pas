unit setup_cicap;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,RegExpr in 'RegExpr.pas',
  unix,setup_libs,distridetect,
  install_generic,squid,dansguardian,zsystem;

  type
  cicap=class


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
      procedure configure();
END;

implementation

constructor cicap.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create;
end;
//#########################################################################################
procedure cicap.Free();
begin
  libs.Free;
end;
//#########################################################################################      e
procedure cicap.configure();
var
dans:tdansguardian;
begin
 dans:=tdansguardian.Create(SYS);
 dans.C_ICAP_STOP();

 dans.C_ICAP_CONFIGURE();
 dans.C_ICAP_START();
end;
//#########################################################################################

procedure cicap.xinstall();
var
local_int_version:integer;
remote_int_version:integer;
remote_str_version:string;
squid:tsquid;
dans:tdansguardian;
cmd:string;
begin

  squid:=Tsquid.Create;
  dans:=tdansguardian.Create(SYS);
  install.INSTALL_PROGRESS('C-ICAP','{checking}');


    if not FileExists(squid.SQUID_BIN_PATH()) then begin
         install.INSTALL_PROGRESS('C-ICAP','{failed}');
         install.INSTALL_STATUS('C-ICAP',110);
         writeln('Unable to stat squid');
         exit;
    end;




  install.INSTALL_PROGRESS('C-ICAP','{downloading}');
  install.INSTALL_STATUS('C-ICAP',30);
  SetCurrentDir('/root');


  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('c-icap');
  if not DirectoryExists(source_folder) then begin
     writeln('Install c-icap failed...');
     install.INSTALL_PROGRESS('C-ICAP','{failed}');
     install.INSTALL_STATUS('C-ICAP',110);
     exit;
  end;


  writeln('Install c-icap extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('C-ICAP',50);
  install.INSTALL_PROGRESS('C-ICAP','{compiling}');
  SetCurrentDir(source_folder);
  cmd:='./configure --enable-static --with-clamav --prefix=/usr --includedir="\${prefix}/include"';
  cmd:=cmd+' --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var --libexecdir="\${prefix}/lib/c-icap"';
  fpsystem(cmd);
  fpsystem('make');
  SYS.AddUserToGroup('nobody','nobody','','');
  fpsystem('make install');
  SetCurrentDir('/root');

if not FileExists(dans.C_ICAP_BIN_PATH()) then begin
     writeln('Install c-icap failed...');
     install.INSTALL_PROGRESS('C-ICAP','{failed}');
     install.INSTALL_STATUS('C-ICAP',110);
     exit;
end;


     writeln('Install c-icap success...');
     install.INSTALL_PROGRESS('C-ICAP','{installed}');
     install.INSTALL_STATUS('C-ICAP',110);
     configure();

end;
//#########################################################################################


end.
