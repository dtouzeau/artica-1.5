unit setup_hostapd;
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
  tsetup_hostapd=class


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

constructor tsetup_hostapd.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure tsetup_hostapd.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_hostapd.xinstall();
var
   CODE_NAME:string;
   kernel_path:string;
   cmd:string;
   l:Tstringlist;
begin

l:=Tstringlist.CReate();
l.add('CONFIG_DRIVER_HOSTAP=y');
l.add('CONFIG_DRIVER_WIRED=y');
l.add('#CONFIG_DRIVER_MADWIFI=y');
l.add('#CFLAGS += -I../../madwifi # change to the madwifi source directory');
l.add('# Driver interface for Prism54 driver');
l.add('#CONFIG_DRIVER_PRISM54=y');
l.add('# Driver interface for drivers using the nl80211 kernel interface');
l.add('#CONFIG_DRIVER_NL80211=y');
l.add('#LIBNL=/usr/src/libnl');
l.add('#CFLAGS += -I$(LIBNL)/include');
l.add('#LIBS += -L$(LIBNL)/lib');
l.add('#CONFIG_DRIVER_BSD=y');
l.add('#CFLAGS += -I/usr/local/include');
l.add('#LIBS += -L/usr/local/lib');
l.add('#CONFIG_DRIVER_NONE=y');
l.add('CONFIG_IAPP=y');
l.add('CONFIG_RSN_PREAUTH=y');
l.add('CONFIG_PEERKEY=y');
l.add('#CONFIG_IEEE80211W=y');
l.add('CONFIG_EAP=y');
l.add('CONFIG_EAP_MD5=y');
l.add('CONFIG_EAP_TLS=y');
l.add('CONFIG_EAP_MSCHAPV2=y');
l.add('CONFIG_EAP_PEAP=y');
l.add('CONFIG_EAP_GTC=y');
l.add('CONFIG_EAP_TTLS=y');
l.add('#CONFIG_EAP_SIM=y');
l.add('#CONFIG_EAP_AKA=y');
l.add('#CONFIG_EAP_AKA_PRIME=y');
l.add('#CONFIG_EAP_PAX=y');
l.add('#CONFIG_EAP_PSK=y');
l.add('#CONFIG_EAP_SAKE=y');
l.add('#CONFIG_EAP_GPSK=y');
l.add('#CONFIG_EAP_GPSK_SHA256=y');
l.add('#CONFIG_EAP_FAST=y');
l.add('#CONFIG_WPS=y');
l.add('#CONFIG_WPS_UPNP=y');
l.add('#CONFIG_EAP_IKEV2=y');
l.add('#CONFIG_EAP_TNC=y');
l.add('CONFIG_PKCS12=y');
l.add('#CONFIG_RADIUS_SERVER=y');
l.add('CONFIG_IPV6=y');
l.add('#CONFIG_IEEE80211R=y');
l.add('#CONFIG_DRIVER_RADIUS_ACL=y');
l.add('#CONFIG_IEEE80211N=y');
l.add('#CONFIG_NO_STDOUT_DEBUG=y');

    CODE_NAME:='APP_HOSTAPD';
    SetCurrentDir('/root');



    install.INSTALL_STATUS(CODE_NAME,20);
    install.INSTALL_PROGRESS(CODE_NAME,'{checking}');



if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('hostapd');
  if not DirectoryExists(source_folder) then begin
     writeln('Install hostapd failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  writeln('Working directory was "'+source_folder+'"');

  if not DirectoryExists(source_folder+'/hostapd') then begin
    writeln('unable to stat '+source_folder+'/hostapd');
     writeln('Install hostapd failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  writeln('writing .config');
  try
     l.SaveToFile(source_folder+'/hostapd/.config');
  except
      writeln('FATAL ERROR !!!');
     writeln('Install hostapd failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(source_folder+'/hostapd');
  fpsystem('make');
  fpsystem('make install');
  if FileExists('/usr/local/bin/hostapd') then begin
     writeln('Install hostapd success...');
     install.INSTALL_STATUS(CODE_NAME,100);
     install.INSTALL_PROGRESS(CODE_NAME,'{success}');
  end else begin
     writeln('Install hostapd failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;



//http://doc.ubuntu-fr.org/wifi_liste_carte
//http://linux-wless.passys.nl/
//driver.h

end;
//#########################################################################################


end.
