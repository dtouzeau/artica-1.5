unit setup_winexe;
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
  tsetup_winexe=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     source_folder,cmd:string;




public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor tsetup_winexe.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure tsetup_winexe.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_winexe.xinstall();
var
   CODE_NAME:string;
   cmd:string;
begin

CODE_NAME:='APP_WINEXE';
SetCurrentDir('/root');
    install.INSTALL_STATUS(CODE_NAME,20);
    install.INSTALL_PROGRESS(CODE_NAME,'{checking}');
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('winexe-static');
  if not DirectoryExists(source_folder) then begin
     writeln('Install winexe-static failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  writeln('Working directory was "'+source_folder+'"');
  if not FileExists(source_folder+'/winexe')  then begin
     writeln('Install winexe-static failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     exit;
  end;

  fpsystem('/bin/cp  '+source_folder+'/winexe /usr/bin/');
  fpsystem('/bin/chmod 755 /usr/bin/winexe');
  fpsystem('/usr/share/artica-postfix/bin/process1 --force '+CODE_NAME);
  install.INSTALL_STATUS(CODE_NAME,100);
  install.INSTALL_PROGRESS(CODE_NAME,'{installed}');

  end;
//#########################################################################################


end.
