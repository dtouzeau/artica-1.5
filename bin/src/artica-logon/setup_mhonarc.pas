unit setup_mhonarc;
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
  install_generic;

  type
  mhonarcisnt=class


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

constructor mhonarcisnt.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
end;
//#########################################################################################
procedure mhonarcisnt.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure mhonarcisnt.xinstall();
var
   CODE_NAME:string;

begin

    CODE_NAME:='APP_MHONARC';
    SetCurrentDir('/root');

    if FileExists('/usr/bin/mhonarc') then begin
      install.INSTALL_STATUS(CODE_NAME,100);
      writeln('Mhonarc already installed...');
      exit;
    end;

if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS(CODE_NAME,10);



  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('mhonarc');
  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;
  writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

  SetCurrentDir(source_folder);
  cmd:='./install.me -batch -perl /usr/bin/perl -binpath /usr/bin';
  writeln('Using ' + cmd);
  fpsystem(cmd);

  if FileExists('/usr/bin/mhonarc') then begin
      install.INSTALL_STATUS(CODE_NAME,100);
      writeln('Success');
  end else begin
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      install.INSTALL_STATUS(CODE_NAME,110);
      writeln('Failed');
  end;

  SetCurrentDir('/root');

end;
//#########################################################################################


end.
