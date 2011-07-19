unit setup_pflogsumm;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,postfix_class,zsystem,
  install_generic;

  type
  tpflogsumm=class


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
   postfix:tpostfix;
   SYS:Tsystem;



public
      constructor Create();
      procedure Free;
      procedure xinstall();

END;

implementation

constructor tpflogsumm.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure tpflogsumm.Free();
begin
  libs.Free;

end;

//#########################################################################################

procedure tpflogsumm.xinstall();
var
   CODE_NAME:string;
   cmd:string;
begin

    CODE_NAME:='APP_PFLOGSUMM';
    postfix:=Tpostfix.Create(SYS);
   SetCurrentDir('/root');
   install.INSTALL_STATUS(CODE_NAME,10);
   install.INSTALL_STATUS(CODE_NAME,30);
   install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

   if not libs.PERL_GENERIC_INSTALL('Date-Calc','Date::Calc') then begin
      writeln('Unable to stat Date::Calc perl module !');
      exit;
   end;


   if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('pflogsumm');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;


   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');

  SetCurrentDir(source_folder);
  fpsystem('/bin/rm -f /usr/sbin/pflogsumm');
  fpsystem('/bin/rm -f /usr/sbin/pflogsumm.pl');
  fpsystem('/bin/cp -f ' + source_folder + '/pflogsumm.pl /usr/sbin/');
  fpsystem('/bin/ln -s --force /usr/sbin/pflogsumm.pl /usr/sbin/pflogsumm');
  fpsystem('/bin/chmod 755 /usr/sbin/pflogsumm.pl');
  install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
  install.INSTALL_STATUS(CODE_NAME,100);
  SetCurrentDir('/root');
  exit;


end;
//#########################################################################################


end.
