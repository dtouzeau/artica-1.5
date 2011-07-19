unit setup_acxdrv;
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
  tacx=class


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

constructor tacx.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure tacx.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tacx.xinstall();
var
   CODE_NAME:string;
   kernel_path:string;
   cmd:string;
   firmware_path:string;
begin

    CODE_NAME:='APP_ACX_DRIVERS';
    SetCurrentDir('/root');

    ///lib/modules/2.6.18-128.7.1.el5/extra/acx.ko

    if FileExists('/usr/src/acx-drivers/acx.ko') then begin
       writeln('/usr/src/acx-drivers/acx.ko is installed, upgrade it');
    end;

    // /lib/modules/2.6.18-128.7.1.el5/extra/acx.ko
//     http://acx100.sourceforge.net/wiki/ACX
// yum wireless-tools
//http://acx100.sourceforge.net/wiki/Firmware
//ifup iwlan0
//http://blog.theclimber.be/?post/2007/05/29/Faire-un-relais-wifi-sur-Ubuntu
//http://www.site-sans-nom.org/rc2/info/rsl_2007-09-03_madwifi.html
//hostapd !!
//http://www.lea-linux.org/documentations/index.php/Cr%C3%A9er_un_point_d%27acc%C3%A8s_s%C3%A9curis%C3%A9_avec_hostAPd

    install.INSTALL_STATUS(CODE_NAME,20);
    install.INSTALL_PROGRESS(CODE_NAME,'{checking}');

    kernel_path:=libs.KERNEL_SOURCES_PATH();
    firmware_path:=libs.GET_FIRMWARE_PATH();
    writeln('kernel_path='+kernel_path);
    writeln('firmware_path='+firmware_path);

    if not DirectoryExists(kernel_path) then begin
       install.INSTALL_STATUS(CODE_NAME,110);
        install.INSTALL_PROGRESS(CODE_NAME,'{failed} Firmware path');
        writeln('Failed to get Firmware source path');
        exit;
    end;

  if not DirectoryExists(kernel_path) then begin
     libs.CheckReposKernel();
     kernel_path:=libs.KERNEL_SOURCES_PATH();
  end;

    if not DirectoryExists(kernel_path) then begin
       install.INSTALL_STATUS(CODE_NAME,110);
        install.INSTALL_PROGRESS(CODE_NAME,'{failed} kernel sources');
        writeln('Failed to get kernel source path');
        exit;
    end;

if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('acx-driver');
  if not DirectoryExists(source_folder) then begin
     writeln('Install ACX drivers failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;


  writeln('Install ACX drivers extracted on "'+source_folder+'"');
  writeln('Install ACX drivers Kernel source is "'+kernel_path+'"');

  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  forceDirectories('/usr/src/acx-drivers');
  fpsystem('/bin/cp -rf '+source_folder+'/* /usr/src/acx-drivers/');

   SetCurrentDir('/usr/src/acx-drivers');
   cmd:='make -C '+kernel_path+' M=/usr/src/acx-drivers';
   fpsystem(cmd);
   writeln(cmd);
   cmd:='make -C '+kernel_path+' M=/usr/src/acx-drivers modules_install';
   fpsystem(cmd);
   writeln(cmd);
   cmd:='/bin/cp /usr/src/acx-drivers/fw/* '+firmware_path;
   writeln(cmd);
   fpsystem(cmd);
install.INSTALL_STATUS(CODE_NAME,100);
     install.INSTALL_PROGRESS(CODE_NAME,'{success}');
     fpsystem('rmmod acx');
     fpsystem('modprobe acx');

end;
//#########################################################################################


end.
