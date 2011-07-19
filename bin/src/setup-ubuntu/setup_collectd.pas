unit setup_collectd;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distridetect,setup_suse_class,install_generic,setup_ubuntu_class,
  setup_fedora_class;

  type
  tsetup_collectd=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     procedure CheckSuseDepencies();
     procedure CheckUbuntuDepencies();


public
      constructor Create();
      procedure Free;
      procedure collectd_install();
      procedure Uninstall();
      procedure InstallCgibin();
      


END;

implementation

constructor tsetup_collectd.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_collectd.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_collectd.collectd_install();
var
   source_folder:string;
   cmd:string;
   libnetlink:string;
begin
  if FIleExists('/opt/collectd/sbin/collectd') then begin
       writeln('Success collectd is already installed');
       exit;
  end;
  
  libnetlink:='--enable-netlink';
  
  install.INSTALL_STATUS('APP_COLLECTD',10);
  if distri.DISTRINAME_CODE='SUSE' then begin
     CheckSuseDepencies();
     libnetlink:='';
  end;
  
  if distri.DISTRINAME_CODE='UBUNTU' then begin
     CheckUbuntuDepencies();
  end;

  install.INSTALL_STATUS('APP_COLLECTD',30);
  source_folder:=libs.COMPILE_GENERIC_APPS('collectd');
  if not DirectoryExists(source_folder) then begin
     writeln('Install collectd failed...');
     install.INSTALL_STATUS('APP_COLLECTD',110);
     exit;
  end;
  
  
  cmd:='./configure --enable-battery --enable-cpu --enable-cpufreq --enable-df ';
  cmd:=cmd + '--enable-disk --enable-dns --enable-email --enable-exec --enable-filecount --enable-hddtemp ';
  cmd:=cmd + '--enable-interface --enable-load --enable-logfile --enable-memcached --enable-memory --enable-mysql ';
  cmd:=cmd + libnetlink+'  --enable-network  --enable-nfs --enable-notify_email --enable-ntpd --enable-ping ';
  cmd:=cmd + '--enable-processes --enable-rrdtool --enable-sensors --enable-swap --enable-syslog --enable-tail ';
  cmd:=cmd + '--enable-tcpconns --enable-thermal --enable-unixsock --enable-users --enable-uuid --enable-wireless ';
  fpsystem('cd ' + source_folder + ' && ' + cmd);
  fpsystem('cd ' + source_folder + ' && make && make install');
  install.INSTALL_STATUS('APP_COLLECTD',40);
  if FileExists('/opt/collectd/sbin/collectd') then begin
     writeln('Success installing collectd');
  end else begin
     writeln('failed installing collectd');
     exit;
  end;
   writeln('Installing cgi-bin....');
   install.INSTALL_STATUS('APP_COLLECTD',50);
   ForceDirectories('/usr/lib/cgi-bin');
   writeln('Copy  ' +source_folder + '/contrib/collection3 in  /usr/lib/cgi-bin/collection3');
   cmd:='/bin/cp -rf ' + source_folder + '/contrib/collection3 /usr/lib/cgi-bin/collection3';
   fpsystem(cmd);

   install.INSTALL_STATUS('APP_COLLECTD',90);
   if DirectoryExists('/usr/lib/cgi-bin/collection3') then begin
      fpsystem('chmod -R 755 /usr/lib/cgi-bin/collection3');
      writeln('Installing cgi-bin....done.');
         install.INSTALL_STATUS('APP_COLLECTD',100);
   end else begin
      writeln('Installing cgi-bin....failed.');
     install.INSTALL_STATUS('APP_COLLECTD',110);
   end;
end;
//#########################################################################################
procedure tsetup_collectd.Uninstall();
var
l:TstringList;
i:integer;
begin
l:=TstringList.Create;
l.add('/opt/collectd');
l.add('/opt/collectd/etc');
l.add('/opt/collectd/sbin');
l.add('/opt/collectd/include');
l.add('/opt/collectd/lib');
l.add('/opt/collectd/share');
l.add('/opt/collectd/var');
l.add('/opt/collectd/bin');
l.add('/opt/collectd/man');
l.add('/usr/lib/cgi-bin/collection3');

for i:=0 to l.Count-1 do begin
    if DirectoryExists(l.Strings[i]) then begin
       writeln('Removing ' +  l.Strings[i]);
       fpsystem('/bin/rm -rf ' +  l.Strings[i]);
    end;
end;

writeln('Success uninstalling collectd');
end;
//#########################################################################################
procedure tsetup_collectd.InstallCgibin();
var
   l:TstringList;
   i:integer;
begin
   l:=TstringList.Create;
   l.AddStrings(libs.PERL_INCFolders);

   for i:=0 to l.Count-1 do begin
       writeln('Linking ');
   end;
end;

//#########################################################################################
procedure tsetup_collectd.CheckSuseDepencies();
var
   suse:tsuse;
   l:TstringList;
   stripped:string;
begin

suse:=Tsuse.Create();
l:=TstringList.Create;
l.Add('libcurl-devel');
l.Add('libesmtp-devel');
l.add('libpcap-devel');
l.add('libxml2-devel');
l.add('libupsclient1');
l.add('libnetapi-devel');
l.add('libsensors4-devel');
l.add('gcc-c++');
stripped:=suse.checkApps(l);
if length(stripped)>0 then begin
    writeln(stripped);
    suse.InstallPackageListsSilent(stripped);
end;
suse.free;
end;
//#########################################################################################
procedure tsetup_collectd.CheckUbuntuDepencies();
var
   ubuntu:tubuntu;
   stripped:string;
begin
  ubuntu:=tubuntu.Create;
  stripped:=ubuntu.CheckDevcollectd();
  if length(stripped)>0 then ubuntu.InstallPackageListssilent(stripped);
end;
//#########################################################################################


end.
