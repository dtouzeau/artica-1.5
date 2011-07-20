unit setup_dhcpd;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,
  install_generic,zsystem;

  type
  tdhcpd=class


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




public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor tdhcpd.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
SYS:=Tsystem.Create();
source_folder:='';
end;
//#########################################################################################
procedure tdhcpd.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tdhcpd.xinstall();
var
local_int_version:integer;
remote_int_version:integer;
patchf:Tstringlist;
cmd:string;
i:integer;
begin


  install.INSTALL_PROGRESS('APP_DHCP','{checking}');
  install.INSTALL_PROGRESS('APP_DHCP','{downloading}');
  install.INSTALL_STATUS('APP_DHCP',30);
  SetCurrentDir('/root');


  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('dhcp');
  if not DirectoryExists(source_folder) then begin
     writeln('Install dhcp failed...');
     install.INSTALL_PROGRESS('APP_DHCP','{failed}');
     install.INSTALL_STATUS('APP_DHCP',110);
     exit;
  end;


  writeln('Install dhcp extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_DHCP',50);
  install.INSTALL_PROGRESS('APP_DHCP','{compiling}');
  SetCurrentDir(source_folder);
  cmd:='./configure --prefix=/usr --sysconfdir=/etc/dhcp --with-srv-lease-file=/var/lib/dhcp/dhcpd.leases';
  cmd:=cmd+' --with-srv6-lease-file=/var/lib/dhcp/dhcpd6.leases --with-cli-lease-file=/var/lib/dhcp/dhclient.leases';
  cmd:=cmd+' --with-cli6-lease-file=/var/lib/dhcp/dhclient6.leases --enable-ldap-conf --with-ldap';
  fpsystem(cmd);
  fpsystem('make');
  install.INSTALL_STATUS('APP_DHCP',70);
  install.INSTALL_PROGRESS('APP_DHCP','{installing}');
  fpsystem('make install');
  if FileExists(source_folder+'/dhcpctl/Makefile') then begin
      patchf:=Tstringlist.Create;
      writeln('Install dhcp Patching on "dhcpctl/Makefile"');
      patchf.LoadFromFile(source_folder+'/dhcpctl/Makefile');
      for i:=0 to patchf.Count-1 do begin
          patchf.Strings[i]:=AnsiReplaceText(patchf.Strings[i],'-Werror','');
      end;
       patchf.SaveToFile(source_folder+'/dhcpctl/Makefile');
         install.INSTALL_STATUS('APP_DHCP',80);
       fpsystem('make');
       install.INSTALL_STATUS('APP_DHCP',90);
       fpsystem('make install');
  end else begin
      writeln(source_folder+'/dhcpctl/Makefile no such file !');
  end;
  SetCurrentDir('/root');

  if not FileExists(SYS.LOCATE_GENERIC_BIN('dhcpd'))  then begin
      install.INSTALL_PROGRESS('APP_DHCP','{failed}');
      install.INSTALL_STATUS('APP_DHCP',110);
      exit;
  end;
       install.INSTALL_PROGRESS('APP_DHCP','{success}');
      install.INSTALL_STATUS('APP_DHCP',100);
end;
//#########################################################################################


end.
