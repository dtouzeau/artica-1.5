unit setup_clamav;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distridetect,setup_suse_class,install_generic,clamav,postfix_class,zsystem;

  type
  tsetup_clamav=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     procedure CheckSuseDepencies();


public
      libclam:TClamav;
      postfix:tpostfix;
      SYS:Tsystem;
      constructor Create();
      procedure Free;
      procedure install_clamav();

END;

implementation

constructor tsetup_clamav.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
SYS:=Tsystem.Create();
postfix:=tpostfix.Create(SYS);
end;
//#########################################################################################
procedure tsetup_clamav.Free();
begin
  postfix.free;
  libs.Free;
end;
//#########################################################################################
procedure tsetup_clamav.install_clamav();
var
   source_folder,cmd:string;
   libclam:TClamav;
   IntVersion:integer;
   remoteversion:integer;
   libs2:tlibs;
   milter_token:string;
   postfix_installed:boolean;
   NoVerif:boolean;
   tommath:string;
   configurelcc:string;
   zdate:string;
   smbsources:string;
   CODE_NAME:string;
begin
NoVerif:=false;
SetCurrentDir('/root');
libclam:=Tclamav.Create;

if not FileExists(SYS.LOCATE_GENERIC_BIN('clamd')) then NoVerif:=true;
IntVersion:=libclam.CLAMAV_BINVERSION();
writeln('Check versions...');
remoteversion:=libs.COMPILE_VERSION('clamav');
postfix_installed:=false;

if FIleExists(postfix.POSFTIX_POSTCONF_PATH()) then postfix_installed:=true;
writeln('Local version...........: ',IntVersion,' as ',IntVersion);
writeln('Remote version..........: ',remoteversion,' as ',remoteversion);

 if remoteversion=0 then begin
    writeln('Failed to obtain informations from repository server');
    exit;
 end;

if not NoVerif then begin
 if ParamStr(2)<>'--force' then begin
    if IntVersion>=remoteversion then begin
          writeln('No changes..........: Success');
          if postfix_installed then install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{installed}');
          install.INSTALL_STATUS('APP_CLAMAV_MILTER',100);
          install.INSTALL_PROGRESS('APP_CLAMAV','{installed}');
          install.INSTALL_STATUS('APP_CLAMAV',100);
          exit();
       end;
 end;
end;

if FileExists('/usr/local/sbin/clamd') then fpsystem('/bin/rm -f /usr/local/sbin/clamd');
if FileExists('/usr/local/bin/clamd') then fpsystem('/bin/rm -f /usr/local/bin/clamd');

if FileExists('/usr/local/bin/clamav-config') then fpsystem('/bin/rm -f /usr/local/bin/clamav-config');
if FileExists('/usr/local/sbin/clamav-config') then fpsystem('/bin/rm -f /usr/local/sbin/clamav-config');

if FileExists('/usr/local/bin/clamscan') then fpsystem('/bin/rm -f /usr/local/bin/clamscan');
if FileExists('/usr/local/sbin/clamscan') then fpsystem('/bin/rm -f /usr/local/sbin/clamscan');

if FileExists('/usr/local/bin/clamdscan') then fpsystem('/bin/rm -f /usr/local/bin/clamdscan');
if FileExists('/usr/local/sbin/clamdscan') then fpsystem('/bin/rm -f /usr/local/sbin/clamdscan');

if FileExists('/usr/local/bin/freshclam') then fpsystem('/bin/rm -f /usr/local/bin/freshclam');
if FileExists('/usr/local/sbin/freshclam') then fpsystem('/bin/rm -f /usr/local/sbin/freshclam');


if postfix_installed then install.INSTALL_STATUS('APP_CLAMAV_MILTER',10);
install.INSTALL_STATUS('APP_CLAMAV',10);
  if distri.DISTRINAME_CODE='SUSE' then begin
     CheckSuseDepencies();
  end;

if postfix_installed then install.INSTALL_STATUS('APP_CLAMAV_MILTER',30);
if postfix_installed then  install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{downloading}');
CODE_NAME:='APP_CLAMAV';
install.INSTALL_STATUS('APP_CLAMAV',30);
install.INSTALL_PROGRESS('APP_CLAMAV','{downloading}');

  writeln('Downloading clamav from repostiory server');
  libs2:=tlibs.Create;

  try
     source_folder:=libs2.COMPILE_GENERIC_APPS('clamav');
  except
   writeln('FATAL error while load internal library tlib line 95 of setup_clamav class');
   writeln('Install clamav failed...');
   if postfix_installed then install.INSTALL_STATUS('APP_CLAMAV_MILTER',110);
   if postfix_installed then install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{failed}');
   install.INSTALL_STATUS('APP_CLAMAV',110);
   install.INSTALL_PROGRESS('APP_CLAMAV','{failed}');

  end;


  if not DirectoryExists(source_folder) then begin
     writeln('Install clamav failed...');
     if postfix_installed then install.INSTALL_STATUS('APP_CLAMAV_MILTER',110);
     if postfix_installed then install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{failed}');
     install.INSTALL_STATUS('APP_CLAMAV',110);
     install.INSTALL_PROGRESS('APP_CLAMAV','{failed}');

     exit;
  end;
     if postfix_installed then  install.INSTALL_STATUS('APP_CLAMAV_MILTER',50);
     if postfix_installed then install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{compiling}');

  install.INSTALL_STATUS('APP_CLAMAV',50);
  install.INSTALL_PROGRESS('APP_CLAMAV','{compiling}');

   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-sources-'+zdate;

 writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
   SetCurrentDir(smbsources);


  fpsystem('/usr/sbin/useradd clamav');

if postfix_installed then  milter_token:='--enable-milter';

if FileExists('/usr/include/tommath/tommath.h') then tommath:=' --with-system-tommath';


       configurelcc:=' LD_LIBRARY_PATH="/lib:/usr/local/lib:/usr/lib/libmilter:/usr/lib" ';
       configurelcc:=configurelcc+'CPPFLAGS="-I/usr/include/libmilter -I/usr/include -I/usr/local/include -I/usr/include/tommath" ';
       configurelcc:=configurelcc + 'LDFLAGS="-L/lib -L/usr/local/lib -L/usr/lib/libmilter -L/usr/lib" ';


  cmd:='./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info ';
  cmd:=cmd+ ' --with-dbdir=/var/lib/clamav/ --sysconfdir=/etc/clamav '+milter_token;
  cmd:=cmd + ' --disable-clamuko --with-gnu-ld --enable-dns-fix';
  cmd:=cmd + ' --libdir=\${prefix}/lib'+tommath+' --with-ltdl-include=/usr/include --with-ltdl-lib=/usr/lib '+configurelcc;
  SYS.AddUserToGroup('clamav','clamav','','');


  writeln('');
  writeln('');
  writeln('');
  writeln('');
  writeln(cmd);
  writeln('');
  writeln('');
  writeln('');
  writeln('');

  fpsystem(cmd);
  fpsystem('make && make install');
  SetCurrentDir('/root');

 if postfix_installed then   install.INSTALL_STATUS('APP_CLAMAV_MILTER',70);
  install.INSTALL_STATUS('APP_CLAMAV',70);



  if FIleExists(postfix.POSFTIX_POSTCONF_PATH()) then begin
  if FileExists('/usr/local/sbin/clamav-milter') then begin
      if postfix_installed then  install.INSTALL_STATUS('APP_CLAMAV_MILTER',100);
      install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{installed}');
      fpsystem('/bin/rm -rf '+smbsources);
  end else begin
     if postfix_installed then  install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{failed}');
     install.INSTALL_STATUS('APP_CLAMAV_MILTER',110);
      fpsystem('/bin/rm -rf '+smbsources);
  end;
  end;

  if FileExists('/usr/sbin/clamd') then begin
       install.INSTALL_STATUS('APP_CLAMAV',100);
       install.INSTALL_PROGRESS('APP_CLAMAV','{installed}');
      fpsystem('/bin/rm -rf '+smbsources);
  end else begin
       install.INSTALL_STATUS('APP_CLAMAV',110);
       install.INSTALL_PROGRESS('APP_CLAMAV','{failed}');
      fpsystem('/bin/rm -rf '+smbsources);
      exit;
  end;

  writeln('success');
  fpsystem('/etc/init.d/artica-postfix restart dansguardian &');
  if postfix_installed then  fpsystem('/etc/init.d/artica-postfix restart postfix &');
  fpsystem('/etc/init.d/artica-postfix restart samba &');

end;
//#########################################################################################
procedure tsetup_clamav.CheckSuseDepencies();
var
   suse:tsuse;
   l:TstringList;
   stripped:string;
begin

suse:=Tsuse.Create();
l:=TstringList.Create;
l.Add('sendmail-devel');
l.add('gcc-c++');
stripped:=suse.checkApps(l);
if length(stripped)>0 then begin
    writeln(stripped);
    suse.InstallPackageListsSilent(stripped);
end;
suse.free;
end;
//#########################################################################################
end.
