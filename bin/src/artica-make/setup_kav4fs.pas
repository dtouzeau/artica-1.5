unit setup_kav4fs;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,setup_libs,distridetect,install_generic,zsystem;

  type
  tsetup_kav4fs=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     SYS:Tsystem;
     function vfsDir(mainpath:string):string;
     function SAMBA_LIBDIR():string;
     function SAMBA_VERSION():string;
     function GET_MODULE_VERSION():string;
     function KERNEL_SOURCES_PATH():string;
public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure xremove();

END;

implementation

constructor tsetup_kav4fs.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
SYS:=tsystem.Create();
end;
//#########################################################################################
procedure tsetup_kav4fs.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_kav4fs.xinstall();
var
   source_folder,cmd:string;
   APP_NAME:string;
   CPUARCH:integer;
   PackagerType:string;
   aptget:string;
   l:Tstringlist;
   LIBDIR:string;
   kavfs_module:string;
begin
APP_NAME:='APP_KAV4FS';

CPUARCH:=libs.ArchStruct();
 PackagerType:=distri.RpmOrDeb();
 writeln('Architecture: ',CPUARCH,'bits; packager: ',PackagerType);
 if CPUARCH<>32 then begin
     writeln('Install Kav4Fs failed...',CPUARCH,'bits not supported');
     writeln('Install Kav4Fs failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
 end;


 if length(PackagerType)=0 then begin
     writeln('Install Kav4Fs failed..."',PackagerType,'" operating system not supported');
     writeln('Install Kav4Fs failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;

 end;

install.INSTALL_STATUS(APP_NAME,10);
install.INSTALL_PROGRESS(APP_NAME,'{downloading}');

if paramstr(2)<>'--setup' then begin
   if PackagerType='deb' then begin
      source_folder:=libs.COMPILE_GENERIC_APPS('kav4fsi386Deb');
   end;
   if PackagerType='rpm' then begin
      source_folder:=libs.COMPILE_GENERIC_APPS('kav4fsi386RPM');
    end;


if length(trim(source_folder))=0 then begin
     writeln('Install Kav4Fs failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    install.INSTALL_PROGRESS(APP_NAME,'{configure}');
    install.INSTALL_STATUS(APP_NAME,20);
    writeln('Installing Kav4Fs from "',source_folder,'"');


if not FileExists(source_folder) then begin
     writeln('Install Kav4Fs failed...unable to stat ',source_folder);
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;

    if PackagerType='deb' then begin
        fpsystem('apt-get remove kav4fs --purge -y -q');
        aptget:=SYS.LOCATE_GENERIC_BIN('dpkg');
        fpsystem('DEBIAN_FRONTEND=noninteractive '+aptget+' -i '+source_folder);
    end;

    if PackagerType='rpm' then begin
        if FileExists('/usr/bin/yum') then fpsystem('/usr/bin/yum remove -y kav4fs');
        fpsystem('rpm -i '+source_folder);
    end;

end;

if not FileExists('/opt/kaspersky/kav4fs/bin/kav4fs-setup.pl') then begin
     writeln('Install Kav4Fs failed...unable to stat /opt/kaspersky/kav4fs/bin/kav4fs-setup.pl');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;


l:=Tstringlist.Create;
LIBDIR:=vfsDir(SAMBA_LIBDIR());
kavfs_module:='/opt/kaspersky/kav4fs/lib/samba/'+GET_MODULE_VERSION();
writeln('Install Kav4Fs samba libdir=',LIBDIR,' kavfs_module='+kavfs_module);
if  FileExists(kavfs_module) then begin
    if not DirectoryExists(LIBDIR) then begin
       l.add('RTP_SAMBA_ENABLE=no');
    end else begin
     l.add('RTP_SAMBA_ENABLE=yes');
     l.add('RTP_SAMBA_CONF=/etc/samba/smb.conf');
     l.add('RTP_SAMBA_VFS='+LIBDIR);
     l.add('RTP_SAMBA_VFS_MODULE='+kavfs_module);
    end;

end else begin
    writeln('Install Kav4Fs samba failed to found '+kavfs_module);
    l.add('RTP_SAMBA_ENABLE=no');
end;

l.Add('EULA_AGREED=yes');
l.Add('SERVICE_LOCALE='+SYS.GET_LANGUAGE_LOCAL());
l.Add('UPDATER_SOURCE=KLServers');
l.add('UPDATER_PROXY=no');
l.add('UPDATER_EXECUTE=no');
l.add('UPDATER_ENABLE_AUTO=no');
l.add('RTP_BUILD_KERNEL_MODULE=yes');
l.add('RTP_BUILD_KERNEL_SRCS=auto');
l.add('RTP_START=no');

l.SaveToFile('/tmp/autoinstall.conf');
writeln('Install Kav4Fs creating autoinstall.conf success');
writeln('Install Kav4Fs excuting post-installation program');
fpsystem('/opt/kaspersky/kav4fs/bin/kav4fs-setup.pl --auto-install=/tmp/autoinstall.conf');

///opt/kaspersky/kav4fs/bin/kav4fs-wmconsole-passwd





    install.INSTALL_STATUS(APP_NAME,100);
    install.INSTALL_PROGRESS(APP_NAME,'{success}');
end;
//#########################################################################################
procedure tsetup_kav4fs.xremove();
var
   l:Tstringlist;
   i:integer;
begin
   l:=Tstringlist.Create;
   l.Add('/usr/share/doc/emailrelay');
   l.Add('/var/spool/emailrelay');
   l.add('/usr/lib/emailrelay');

   for i:=0 to l.Count-1 do begin
       if length(l.Strings[i])>0 then begin
          if DirectoryExists(l.Strings[i]) then begin
             writeln('Removing directory '+l.Strings[i]);
             fpsystem('/bin/rm -rfv '+l.Strings[i]+'/*');
             fpsystem('/bin/rmdir '+l.Strings[i]);
          end;
       end;
   end;
   l.free;
   l:=Tstringlist.Create;
   l.add('/usr/sbin/emailrelay');
   l.add('/usr/sbin/emailrelay-submit');
   l.add('/usr/sbin/emailrelay-passwd');
   l.add('/usr/sbin/emailrelay-gui');
   l.add('/usr/sbin/emailrelay-gui.real');

   for i:=0 to l.Count-1 do begin
       if length(l.Strings[i])>0 then begin
          if FileExists(l.Strings[i]) then begin
             writeln('Removing File '+l.Strings[i]);
             fpsystem('/bin/rm '+l.Strings[i]);
          end;
       end;
   end;


end;
//#########################################################################################
function tsetup_kav4fs.vfsDir(mainpath:string):string;
begin
    if DirectoryExists(mainpath+'/vfs') then exit(mainpath+'/vfs');
    if DirectoryExists(mainpath+'/samba/vfs') then exit(mainpath+'/samba/vfs');
end;
//##############################################################################
function tsetup_kav4fs.KERNEL_SOURCES_PATH():string;
var
    kernelver:string;
begin
    kernelver:=libs.KERNEL_VERSION();
    writeln('Install Kav4Fs samba kernel version:',kernelver);
    if DirectoryExists('/lib/modules/'+kernelver+'/build' ) then begin
       result:='/lib/modules/'+kernelver+'/build';
       exit;
    end;

   if DirectoryExists('/usr/src/linux-headers-'+kernelver) then begin
      result:='/usr/src/linux-headers-'+kernelver;
      exit;
   end;

end;
//##############################################################################
function tsetup_kav4fs.GET_MODULE_VERSION():string;
var
   smbver:string;
   RegExpr:TRegExpr;
begin

     smbver:=SAMBA_VERSION();
     writeln('Install Kav4Fs Samba version='+smbver);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='3\.5\.';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs27.so';
   exit;
end;

RegExpr.Expression:='3\.4\.';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs25.so';
   exit;
end;

RegExpr.Expression:='3\.3\.';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs24.so';
   exit;
end;

RegExpr.Expression:='3\.2\.';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs22.so';
   exit;
end;

RegExpr.Expression:='3\.0\.25';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs21.so';
   exit;
end;


RegExpr.Expression:='3\.0\.23';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs16.so';
   exit;
end;

RegExpr.Expression:='3\.0\.21';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs15.so';
   exit;
end;

RegExpr.Expression:='3\.0\.20a';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs14.so';
   exit;
end;

RegExpr.Expression:='3\.0\.20';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs13.so';
   exit;
end;

RegExpr.Expression:='3\.0\.11';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs11.so';
   exit;
end;

RegExpr.Expression:='3\.0\.2a';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs10a.so';
   exit;
end;

RegExpr.Expression:='3\.0\.2';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs10.so';
   exit;
end;
RegExpr.Expression:='3\.0\.0';
if RegExpr.Exec(smbver) then begin
   result:='kav4fs-smb-vfs09.so';
   exit;
end;
end;
function tsetup_kav4fs.SAMBA_VERSION():string;
var
   RegExpr:TRegExpr;
   x:TstringList;
   i:Integer;
begin

fpsystem(SYS.LOCATE_GENERIC_BIN('smbd') + ' -V >/tmp/samba.infos2 2>&1');
x:=Tstringlist.Create;
x.LoadFromFile('/tmp/samba.infos2');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='Version\s+([0-9\.]+)';
for i:=0 to x.Count-1 do begin
     if RegExpr.Exec(x.Strings[i]) then begin
        result:=trim(RegExpr.Match[1]);
        break;
     end;

end;
x.free;
RegExpr.free;

end;
//##############################################################################
function tsetup_kav4fs.SAMBA_LIBDIR():string;
var
   RegExpr:TRegExpr;
   x:TstringList;
   i:Integer;
begin

fpsystem(SYS.LOCATE_GENERIC_BIN('smbd') + ' -b >/tmp/samba.infos 2>&1');
x:=Tstringlist.Create;
x.LoadFromFile('/tmp/samba.infos');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='LIBDIR:\s+(.+)';
for i:=0 to x.Count-1 do begin
     if RegExpr.Exec(x.Strings[i]) then begin
        result:=trim(RegExpr.Match[1]);
        break;
     end;

end;
x.free;
RegExpr.free;
end;
//##############################################################################
end.
