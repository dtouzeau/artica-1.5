unit setup_msmtp;
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
  tmsmtp=class


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
      procedure ligsasl();
END;

implementation

constructor tmsmtp.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure tmsmtp.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure tmsmtp.ligsasl();
var cmd:string;
begin
   if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('libgsasl');
   if not DirectoryExists(source_folder) then exit;
   ForceDirectories('/root/libgasl-install');
   fpsystem('/bin/cp -rf '+ source_folder+'/* /root/libgasl-install/');
   SetCurrentDir('/root/libgasl-install');
   cmd:='./configure  --prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man"';
   cmd:=cmd+' --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var';
   cmd:=cmd+' --libexecdir="\${prefix}/lib/gsasl"  --disable-dependency-tracking';

   writeln(cmd);
   fpsystem(cmd);
   fpsystem('make && make install');
   SetCurrentDir('/root');
   fpsystem('/bin/rm -rf /root/libgasl-install');

end;


//#########################################################################################
procedure tmsmtp.xinstall();
var
   CODE_NAME:string;
   cmd:string;
begin

    CODE_NAME:='APP_MSMTP';
    postfix:=Tpostfix.Create(SYS);

    if not FileExists('/usr/lib/libgsasl.a') then ligsasl();
    if not FileExists('/usr/lib/libgsasl.a') then begin
       writeln('Failed to find libgsasl.a');
       install.INSTALL_STATUS(CODE_NAME,110);
        install.INSTALL_PROGRESS(CODE_NAME,'{failed} libgsasl');
     exit;
  end;

  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('msmtp');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     fpsystem(Paramstr(0)+' APP_MSMTP');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;





  writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

  SetCurrentDir(source_folder);



  cmd:='./configure --with-ssl=openssl --with-libgsasl --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info';

  writeln(cmd);
  fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/bin/msmtp') then begin
     fpsystem('/bin/cp /usr/bin/msmtp /usr/share/artica-postfix/bin/artica-msmtp');
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     exit;
  end else begin
      writeln('Unable to stat /usr/bin/msmtp');
  end;


     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;



end;
//#########################################################################################


end.
