unit setup_clamav;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distridetect,setup_suse_class,install_generic,clamav;

  type
  tsetup_clamav=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     procedure CheckSuseDepencies();


public
      constructor Create();
      procedure Free;
      procedure install_clamav();
      libclam:TClamav;
END;

implementation

constructor tsetup_clamav.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_clamav.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_clamav.install_clamav();
var
   source_folder,cmd:string;
   libclam:TClamav;
   IntVersion:integer;
   remoteversion:integer;

begin

SetCurrentDir('/root');
libclam:=Tclamav.Create;
IntVersion:=libclam.CLAMAV_BINVERSION();
writeln('Check versions...');
remoteversion:=libs.COMPILE_VERSION('clamav');

 writeln('Local version...........: ',IntVersion,' as ',IntVersion);
 writeln('Remote version..........: ',remoteversion,' as ',remoteversion);

 if remoteversion=0 then begin
    writeln('Failed to obtain informations from repository server');
    exit;
 end;

if ParamStr(2)<>'--force' then begin
 if IntVersion>=remoteversion then begin
     writeln('No changes..........: Success');
     install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{installed}');
     install.INSTALL_STATUS('APP_CLAMAV_MILTER',100);
     install.INSTALL_PROGRESS('APP_CLAMAV','{installed}');
     install.INSTALL_STATUS('APP_CLAMAV',100);
     exit();
 end;
end;


install.INSTALL_STATUS('APP_CLAMAV_MILTER',10);
install.INSTALL_STATUS('APP_CLAMAV',10);
  if distri.DISTRINAME_CODE='SUSE' then begin
     CheckSuseDepencies();
  end;

install.INSTALL_STATUS('APP_CLAMAV_MILTER',30);
install.INSTALL_STATUS('APP_CLAMAV',30);
install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{downloading}');
install.INSTALL_PROGRESS('APP_CLAMAV','{downloading}');


  source_folder:=libs.COMPILE_GENERIC_APPS('clamav');
  if not DirectoryExists(source_folder) then begin
     writeln('Install clamav failed...');
     install.INSTALL_STATUS('APP_CLAMAV_MILTER',110);
     install.INSTALL_STATUS('APP_CLAMAV',110);
     install.INSTALL_PROGRESS('APP_CLAMAV','{failed}');
     install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{failed}');
     exit;
  end;
  install.INSTALL_STATUS('APP_CLAMAV_MILTER',50);
  install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{compiling}');

  install.INSTALL_STATUS('APP_CLAMAV',50);
  install.INSTALL_PROGRESS('APP_CLAMAV','{compiling}');

  SetCurrentDir(source_folder);

  cmd:='./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info ';
  cmd:=cmd+ ' --with-dbdir=/var/lib/clamav/ --sysconfdir=/etc/clamav --enable-milter';
  cmd:=cmd + ' --disable-clamuko --with-gnu-ld --enable-dns-fix';
  cmd:=cmd + ' --libdir=\${prefix}/lib --with-system-tommath  --with-ltdl-include=/usr/include --with-ltdl-lib=/usr/lib';



  fpsystem(cmd);
  fpsystem('make && make install');
  SetCurrentDir('/root');

  install.INSTALL_STATUS('APP_CLAMAV_MILTER',70);
  install.INSTALL_STATUS('APP_CLAMAV',70);

  if FileExists('/usr/local/sbin/clamav-milter') then begin
      install.INSTALL_STATUS('APP_CLAMAV_MILTER',100);
      install.INSTALL_STATUS('APP_CLAMAV',100);
      writeln('success');
      install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{installed}');
      install.INSTALL_PROGRESS('APP_CLAMAV','{installed}');
  end else begin
      install.INSTALL_STATUS('APP_CLAMAV_MILTER',110);
      install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{failed}');
      install.INSTALL_PROGRESS('APP_CLAMAV','{failed}');
      install.INSTALL_STATUS('APP_CLAMAV',110);
      writeln('failed');
  end;
  install.INSTALL_PROGRESS('APP_CLAMAV_MILTER','{done}');
  install.INSTALL_PROGRESS('APP_CLAMAV','{done}');
  fpsystem('/etc/init.d/artica-postfix restart clamd &');

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
