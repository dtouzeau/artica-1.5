unit setup_vmtools;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distridetect,setup_suse_class,install_generic,zsystem;

  type
  tsetup_vmtools=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     SYS:Tsystem;
     packageSource:string;
public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure VirtualBoxAdditions();
END;

implementation

constructor tsetup_vmtools.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
SYS:=Tsystem.Create();
if FileExists(ParamStr(2)) then packageSource:=ParamStr(2);
end;
//#########################################################################################
procedure tsetup_vmtools.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_vmtools.xinstall();
var

   source_folder,cmd:string;
   l:Tstringlist;

begin
install.INSTALL_STATUS('APP_VMTOOLS',10);
install.INSTALL_PROGRESS('APP_VMTOOLS','{downloading}');

if FileExists(packageSource) then begin
   writeln('Extracting from local file ' +packageSource);
   source_folder:=libs.ExtractLocalPackage(packageSource);
   writeln('source folder:',source_folder);
end;


if not DirectoryExists(source_folder) then source_folder:=libs.COMPILE_GENERIC_APPS('VMwareTools');

if length(trim(source_folder))=0 then begin
     writeln('Install VMwareTools failed...');
     install.INSTALL_STATUS('APP_VMTOOLS',110);
     exit;
end;
    install.INSTALL_PROGRESS('APP_VMTOOLS','{installing}');
    install.INSTALL_STATUS('APP_VMTOOLS',70);
writeln('Installing VMwareTools from "',source_folder,'"');
SetCurrentDir(source_folder);
fpsystem('./vmware-install.pl --default');
SetCurrentDir('/root');
fpsystem('/bin/rm -rf '+source_folder);
if FIleExists('/usr/sbin/vmware-guestd') then begin
       install.INSTALL_STATUS('APP_VMTOOLS',100);
       install.INSTALL_PROGRESS('APP_VMTOOLS','{installed}');
  end else begin
       install.INSTALL_STATUS('APP_VMTOOLS',110);
       install.INSTALL_PROGRESS('APP_VMTOOLS','{failed}');
  end;

end;
//#########################################################################################
procedure tsetup_vmtools.VirtualBoxAdditions();
var

   source_folder,cmd:string;
   l:Tstringlist;
   Arch:integer;
   TargetFile:string;
   CODE_NAME:string;
begin

distri:=tdistriDetect.Create();
Arch:=libs.ArchStruct();

writeln('RESULT.................: Architecture : ',Arch);
writeln('RESULT.................: Distribution : ',distri.DISTRINAME,' (DISTRINAME)');
writeln('RESULT.................: Major version: ',distri.DISTRI_MAJOR,' (DISTRI_MAJOR)');
writeln('RESULT.................: Artica Code  : ',distri.DISTRINAME_CODE,' (DISTRINAME_CODE)');

      CODE_NAME:='APP_VBOXADDITIONS';
      SetCurrentDir('/root');
      install.INSTALL_STATUS(CODE_NAME,5);
       if not FileExists('/usr/bin/apt-get') then begin
         writeln('Install VirtualBox additions failed /usr/bin/apt-get no such file ',distri.DISTRINAME,' not supported yet...');
         install.INSTALL_STATUS('APP_VBOXADDITIONS',110);
         exit;
      end;
      install.INSTALL_STATUS('APP_VBOXADDITIONS',10);
      fpsystem('DEBIAN_FRONTEND=noninteractive /usr/bin/apt-get -o Dpkg::Options::="--force-confnew" --force-yes -fuy install linux-headers-`/bin/uname -r`');
      install.INSTALL_PROGRESS('APP_VBOXADDITIONS','{downloading}');

if FileExists(packageSource) then begin
   writeln('Extracting from local file ' +packageSource);
   source_folder:=libs.ExtractLocalPackage(packageSource);
   writeln('source folder:',source_folder);
end;


if not DirectoryExists(source_folder) then source_folder:=libs.COMPILE_GENERIC_APPS('VBoxLinuxAdditions-'+IntToStr(Arch));

if length(trim(source_folder))=0 then begin
     writeln('Install VirtualBox additions failed...');
     install.INSTALL_STATUS('APP_VBOXADDITIONS',110);
     exit;
end;
    install.INSTALL_PROGRESS('APP_VBOXADDITIONS','{installing}');
    install.INSTALL_STATUS('APP_VBOXADDITIONS',70);
    writeln('Installing VirtualBox additions from "',source_folder,'"');
    SetCurrentDir(source_folder);

if Arch=32 then TargetFile:=source_folder+'/VBoxLinuxAdditions-x86.run';
if Arch=64 then TargetFile:=source_folder+'/VBoxLinuxAdditions-amd64.run';

if not FileExists(TargetFile) then begin
     writeln('Install VirtualBox additions failed...',TargetFile,' no such file');
     install.INSTALL_STATUS('APP_VBOXADDITIONS',110);
     exit;
end;

fpsystem('/bin/chmod 777 '+TargetFile);
fpsystem(TargetFile+' -- install force');


if FIleExists(SYS.LOCATE_GENERIC_BIN('VBoxService')) then begin
       install.INSTALL_STATUS('APP_VBOXADDITIONS',100);
       install.INSTALL_PROGRESS('APP_VBOXADDITIONS','{installed}');
  end else begin
       install.INSTALL_STATUS('APP_VBOXADDITIONS',110);
       install.INSTALL_PROGRESS('APP_VBOXADDITIONS','{failed}');
  end;

end;
//#########################################################################################



end.
