unit setup_dkimproxy;
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
  install_dkimproxy=class


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

constructor install_dkimproxy.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure install_dkimproxy.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_dkimproxy.xinstall();
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

    CODE_NAME:='APP_DKIMPROXY';

    if not libs.PERL_GENERIC_INSTALL('Mail-DKIM','Mail::DKIM') then begin
        writeln('Install '+CODE_NAME+' failed...unable to install Mail::DKIM perl module');
        install.INSTALL_STATUS(CODE_NAME,110);
        exit;
    end;

    if not libs.PERL_GENERIC_INSTALL('Mail-DomainKeys','Mail::DomainKeys') then begin
        writeln('Install '+CODE_NAME+' failed...unable to install Mail::DomainKeys perl module');
        install.INSTALL_STATUS(CODE_NAME,110);
        exit;
    end;


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,40);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('dkimproxy');



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



CC:=sys.LOCATE_GENERIC_BIN('gcc');
if not FileExists(CC) then begin
   writeln('Install '+CODE_NAME+' failed...');
   install.INSTALL_STATUS(CODE_NAME,110);
   writeln('unable to stat GCC');
   exit;
end;

       configurelcc:=' LD_LIBRARY_PATH="/lib:/usr/local/lib:/usr/lib/libmilter:/usr/lib" ';
       configurelcc:=configurelcc+'CPPFLAGS="-I/usr/include/libmilter -I/usr/include -I/usr/local/include -I/usr/include/sm/os" ';
       configurelcc:=configurelcc+'LDFLAGS="-L/lib -L/usr/local/lib -L/usr/lib/libmilter -L/usr/lib" ';
       configurelcc:=configurelcc+' CC='+CC;


 SetCurrentDir(smbsources);
 cmd:='./configure -prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var '+configurelcc;
 writeln(cmd);
 fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');



  if FileExists('/usr/bin/dkimproxy.in') then begin
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
