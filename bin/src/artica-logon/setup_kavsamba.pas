unit setup_kavsamba;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distridetect,setup_suse_class,install_generic;

  type
  tsetup_kavsamba=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor tsetup_kavsamba.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_kavsamba.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_kavsamba.xinstall();
var
   source_folder,cmd:string;
   autoanswers_conf:TstringList;

begin
install.INSTALL_STATUS('APP_KAV4SAMBA',10);
source_folder:=libs.COMPILE_GENERIC_APPS('kav4samba');


          install.INSTALL_PROGRESS('APP_KAV4SAMBA','{downloading}');
if length(trim(source_folder))=0 then begin
     writeln('Install Kaspersky For samba failed...');
     install.INSTALL_STATUS('APP_KAS3',110);
     exit;
end;
  if DirectoryExists(source_folder) then fpsystem('/bin/chown -R root:root '+source_folder);
  sleep(100);

          install.INSTALL_PROGRESS('APP_KAV4SAMBA','{installing}');
install.INSTALL_STATUS('APP_KAV4SAMBA',20);
writeln('Installing Kaspersky For Samba...');
fpsystem('/bin/cp -rf '+source_folder+'/etc/* /etc/');
fpsystem('/bin/cp -rf '+source_folder+'/opt/* /opt/');
fpsystem('/bin/cp -rf '+source_folder+'/var/* /var/');

  if not FileExists('/opt/kaspersky/kav4samba/bin/kav4samba-kavscanner') then begin
         install.INSTALL_STATUS('APP_KAV4SAMBA',110);
         install.INSTALL_PROGRESS('APP_KAV4SAMBA','{failed}');
         writeln('Unable to install the software... Aborting...');
         exit;
    end;

         autoanswers_conf:=TStringList.Create;
         autoanswers_conf.Add('CONFIGURE_ENTER_KEY_PATH=/usr/share/artica-postfix/bin/install');
         autoanswers_conf.Add('CONFIGURE_KEEPUP2DATE_ASKPROXY=no');
         autoanswers_conf.Add('CONFIGURE_RUN_KEEPUP2DATE=no');
         autoanswers_conf.Add('CONFIGURE_WEBMIN_ASKCFGPATH=');
         autoanswers_conf.Add('SETUP_SAMBA_CONFIRM_DEFAULTS=Y');
         autoanswers_conf.Add('SETUP_SAMBA_ASK_CONFIGURE_SHARES=S');
         autoanswers_conf.SaveToFile('/opt/kaspersky/kav4samba/lib/bin/setup/autoanswers.conf');

SetCurrentDir('/opt/kaspersky/kav4samba/lib/bin/setup');
fpsystem('./postinstall.pl');
fpsystem('./kavsamba_setup.pl');

if FileExists('/usr/sbin/update-rc.d') then fpsystem('/usr/sbin/update-rc.d kav4samba start 15 2 3 4 5  . stop 25 0 1 6 .');

  if FileExists('/sbin/chkconfig') then begin
     fpsystem('/sbin/chkconfig --add kav4samba >/dev/null');
     fpsystem('/sbin/chkconfig --level 2345 kav4samba on');
  end;

fpsystem('/etc/init.d/kav4samba restart');
fpsystem('/opt/kaspersky/kav4samba/bin/kav4samba-keepup2date -q &');
install.INSTALL_STATUS('APP_KAS3',100);
install.INSTALL_PROGRESS('APP_KAV4SAMBA','{installed}');


end;
//#########################################################################################
end.
