unit setup_ocs;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,zsystem,
  setup_suse_class,
  install_generic,
  setup_ubuntu_class;

  type
  tsetup_ocs=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     source_folder,cmd:string;
     SYS:Tsystem;



public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure xclient_install();
      procedure xclient_linux_install();
      procedure xfusionclient_install();
      procedure xwpkg_server_install();
END;

implementation

constructor tsetup_ocs.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure tsetup_ocs.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_ocs.xclient_install();
var
   CODE_NAME:string;
   cmd:string;
begin
     SYS:=Tsystem.Create;
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('OCSNG_WINDOWS_AGENT');
    if FileExists(source_folder) then begin
       writeln('importing windows package to repository');
       fpsystem(SYS.LOCATE_PHP5_BIN() +' /usr/share/artica-postfix/exec.ocsweb.install.php --install-client '+source_folder);
    end;
end;
//#########################################################################################
procedure tsetup_ocs.xfusionclient_install();
var
   CODE_NAME:string;
   cmd:string;
   basename:string;
begin
     SYS:=Tsystem.Create;
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('fusioninventory-agent_windows-i386');

    if FileExists(source_folder) then begin
      basename:=ExtractFileName(source_folder);
       writeln('importing Fusion inventory ',basename,' windows package to repository');
       ForceDirectories('/opt/artica/install/sources/fusioninventory');
       if FileExists('/opt/artica/install/sources/fusioninventory/'+basename) then fpsystem('/bin/rm /opt/artica/install/sources/fusioninventory/'+basename);
       fpsystem('/bin/cp  '+source_folder+' /opt/artica/install/sources/fusioninventory/');
    end;
end;
//#########################################################################################
procedure tsetup_ocs.xwpkg_server_install();
var
   CODE_NAME:string;
   cmd:string;
   basename:string;
begin
     SYS:=Tsystem.Create;
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('wpkg');

    if DirectoryExists(source_folder) then begin

       writeln('Installing wpkg source was ',source_folder);
       writeln('/bin/cp  -rfv '+source_folder+'/* /usr/share/wpkg/');
       ForceDirectories('/usr/share/wpkg');
       fpsystem('/bin/cp  -rv '+source_folder+'/* /usr/share/wpkg/');
    end;
end;
//#########################################################################################
procedure tsetup_ocs.xinstall();
var
   CODE_NAME:string;
   cmd:string;
begin

CODE_NAME:='APP_OCSI';
SetCurrentDir('/root');
install.INSTALL_STATUS(CODE_NAME,20);

 if not libs.PERL_GENERIC_INSTALL('XML-Entities','XML::Entities') then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
 end;


    install.INSTALL_PROGRESS(CODE_NAME,'{checking}');
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('OCSNG_UNIX_SERVER');
  if not DirectoryExists(source_folder) then begin
     writeln('Install ocs failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  writeln('Working directory was "'+source_folder+'"');
  if not FileExists(source_folder+'/setup.sh')  then begin
     writeln('Install ocs failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  SetCurrentDir(source_folder+'/Apache');
  fpsystem('perl Makefile.PL');
  fpsystem('make');
  fpsystem('make install');
  SetCurrentDir(source_folder);

  forceDirectories('/usr/share/ocsinventory-reports/ocsreports');
  forceDirectories('/var/lib/ocsinventory-reports/ipd');
  ForceDirectories('/var/lib/ocsinventory-reports/download');
  fpsystem('/bin/cp -rf '+source_folder+'/ocsreports/* /usr/share/ocsinventory-reports/ocsreports/');
  fpsystem('/bin/chmod -R go-w /usr/share/ocsinventory-reports');
  fpsystem('/bin/cp '+ source_folder+'/binutils/ipdiscover-util.pl /usr/share/ocsinventory-reports/ocsreports/ipdiscover-util.pl');
  fpsystem('/etc/init.d/artica-postfix restart ocsweb');
  install.INSTALL_STATUS(CODE_NAME,100);
  install.INSTALL_PROGRESS(CODE_NAME,'{installed}');

  end;
//#########################################################################################
procedure tsetup_ocs.xclient_linux_install();
var
   CODE_NAME:string;
   cmd:string;
begin

CODE_NAME:='APP_OCSI_LINUX_CLIENT';
SetCurrentDir('/root');
install.INSTALL_STATUS(CODE_NAME,20);

     install.INSTALL_PROGRESS(CODE_NAME,'{checking}');

 if not libs.PERL_GENERIC_INSTALL('XML-Entities','XML::Entities') then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
 end;

 if not libs.PERL_GENERIC_INSTALL('Proc-Daemon','Proc::Daemon') then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
 end;

  if not libs.PERL_GENERIC_INSTALL('Proc-PID-File','Proc::PID::File') then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
 end;

 libs.PERL_GENERIC_INSTALL('Net-CUPS','Net::CUPS');


if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('OCSNG_LINUX_AGENT');
  if not DirectoryExists(source_folder) then begin
     writeln('Install ocs failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  writeln('Working directory was "'+source_folder+'"');
  SetCurrentDir(source_folder);
  if FileExists(source_folder+'/run-postinst') then fpsystem('/bin/mv '+source_folder+'/run-postinst /tmp');
  if FileExists(source_folder+'/postinst.pl') then fpsystem('/bin/mv '+source_folder+'/postinst.pl /tmp');
  fpsystem('PERL_AUTOINSTALL=1  perl Makefile.PL');
  fpsystem('make');
  fpsystem('make install');

  if FileExists('/usr/local/bin/ocsinventory-agent') then begin
     ForceDirectories('/etc/ocsinventory-agent');
     ForceDirectories('/var/lib/ocsinventory-agent');
      writeln('Install ocs success...');
     install.INSTALL_STATUS(CODE_NAME,100);
     install.INSTALL_PROGRESS(CODE_NAME,'{success}');
     exit;
  end;

  install.INSTALL_STATUS(CODE_NAME,110);
  install.INSTALL_PROGRESS(CODE_NAME,'{failed}');

end;


end.
