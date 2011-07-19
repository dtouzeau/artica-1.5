unit setup_dropbox;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,setup_libs,distridetect,install_generic,zsystem;

  type
  tsetup_dropbox=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     SYS:Tsystem;
public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure thinclient();
      procedure KasperskyUpdateUtility();
END;

implementation

constructor tsetup_dropbox.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
SYS:=tsystem.Create();

end;
//#########################################################################################
procedure tsetup_dropbox.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_dropbox.xinstall();
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
APP_NAME:='APP_DROPBOX';

CPUARCH:=libs.ArchStruct();
PackagerType:=distri.RpmOrDeb();
writeln('Architecture: ',CPUARCH,'bits; packager: ',PackagerType);
install.INSTALL_STATUS(APP_NAME,10);
install.INSTALL_PROGRESS(APP_NAME,'{downloading}');


 if CPUARCH=32 then begin
     source_folder:=libs.COMPILE_GENERIC_APPS('dropbox-32');
 end;

 if CPUARCH=64 then begin
     source_folder:=libs.COMPILE_GENERIC_APPS('dropbox-64');
 end;

if length(trim(source_folder))=0 then begin
     writeln('Install dropbox failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    install.INSTALL_PROGRESS(APP_NAME,'{installing}');
    install.INSTALL_STATUS(APP_NAME,20);
    writeln('Installing dropbox from "',source_folder,'"');


if not FileExists(source_folder) then begin
     writeln('Install dropbox failed...unable to stat ',source_folder);
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    forcedirectories('/root/.dropbox-dist');
    fpsystem('/bin/cp -rf '+source_folder+'/* /root/.dropbox-dist/');

    if not FileExists('/root/.dropbox-dist/dropbox') then begin
     writeln('Install dropbox failed... unable to stat /root/.dropbox-dist/dropbox');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
    end;

    install.INSTALL_STATUS(APP_NAME,100);
    install.INSTALL_PROGRESS(APP_NAME,'{success}');
end;
//#########################################################################################
procedure tsetup_dropbox.thinclient();
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
   APP_NAME:='APP_THINSTATION';
   install.INSTALL_STATUS(APP_NAME,10);
   install.INSTALL_PROGRESS(APP_NAME,'{downloading}');
   source_folder:=libs.COMPILE_GENERIC_APPS('Thinstation');

 if length(trim(source_folder))=0 then begin
     writeln('Install thinstation failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    install.INSTALL_PROGRESS(APP_NAME,'{installing}');
    install.INSTALL_STATUS(APP_NAME,20);
    writeln('Installing thinstation from "',source_folder,'"');
    forcedirectories('/opt/thinstation');
    fpsystem('/bin/cp -rf '+source_folder+'/* /opt/thinstation/');

    if not FileExists('/opt/thinstation/build') then begin
     writeln('Install thinstation failed... unable to stat /opt/thinstation/build');
     install.INSTALL_STATUS(APP_NAME,110);
     fpsystem('/bin/rm -rf '+source_folder);
     exit;
    end;

    install.INSTALL_STATUS(APP_NAME,100);
    install.INSTALL_PROGRESS(APP_NAME,'{success}');
    fpsystem('/bin/rm -rf '+source_folder);


end;
//#########################################################################################
procedure tsetup_dropbox.KasperskyUpdateUtility();
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
   APP_NAME:='APP_KUPDATE_UTILITY';
   install.INSTALL_STATUS(APP_NAME,10);
   install.INSTALL_PROGRESS(APP_NAME,'{downloading}');
   source_folder:=libs.COMPILE_GENERIC_APPS('UpdateUtility');

 if length(trim(source_folder))=0 then begin
     writeln('Install Kaspersky Update Utility failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    install.INSTALL_PROGRESS(APP_NAME,'{installing}');
    install.INSTALL_STATUS(APP_NAME,20);
    writeln('Installing Kaspersky Update Utility from "',source_folder,'"');
    forcedirectories('/opt/kaspersky/UpdateUtility');
    fpsystem('/bin/cp -rf '+source_folder+'/../* /opt/kaspersky/UpdateUtility/');

    if not FileExists('/opt/kaspersky/UpdateUtility/UpdateUtility-Console') then begin
     writeln('Install thinstation failed... unable to stat /opt/kaspersky/UpdateUtility/UpdateUtility-Console');
     install.INSTALL_STATUS(APP_NAME,110);
     fpsystem('/bin/rm -rf '+source_folder);
     exit;
    end;

    install.INSTALL_STATUS(APP_NAME,100);
    install.INSTALL_PROGRESS(APP_NAME,'{success}');
    fpsystem('/bin/rm -rf '+source_folder);


end;
//#########################################################################################


end.
