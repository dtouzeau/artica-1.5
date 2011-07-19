unit setup_crossroads;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,zsystem,
  install_generic;

  type
  install_crossroads=class


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
   SYS:Tsystem;



public
      constructor Create();
      procedure Free;
      procedure xinstall();

END;

implementation

constructor install_crossroads.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure install_crossroads.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_crossroads.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   CC:string;
   configurelcc:string;
begin

    CODE_NAME:='APP_CROSSROADS';

   SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,40);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('crossroads');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



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
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(smbsources);


  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  install.INSTALL_STATUS(CODE_NAME,80);
  SetCurrentDir(smbsources);
  cmd:='make install';
 writeln(cmd);
 fpsystem(cmd);
   install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  install.INSTALL_STATUS(CODE_NAME,90);

  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');



  if FileExists('/usr/sbin/xr') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;
  end;



     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;



end;
//#########################################################################################









end.
