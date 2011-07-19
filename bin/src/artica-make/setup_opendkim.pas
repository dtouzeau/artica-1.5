unit setup_opendkim;
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
  install_opendkim=class


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
      procedure dkmilter_install();
END;

implementation

constructor install_opendkim.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure install_opendkim.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_opendkim.xinstall();
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

    CODE_NAME:='APP_OPENDKIM';


    if not FileExists('/usr/lib/libdk.a') then dkmilter_install();


 if not FileExists('/usr/lib/libdk.a') then begin
     writeln('Install '+CODE_NAME+' failed...unable to stat /usr/lib/libdk.a');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;

  if not FileExists('/usr/include/sm/os/sm_os.h') then begin
     if FileExists('/usr/include/sm/os/sm_os_freebsd.h') then fpsystem('/bin/cp /usr/include/sm/os/sm_os_freebsd.h /usr/include/sm/os/sm_os.h');
  end;
  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);

  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,40);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('opendkim');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/opendkim-sources-'+zdate;
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
// fpsystem('./autogen.sh');


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
 cmd:='./configure -prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var --with-domainkeys --with-openssl --with-db --with-milter --enable-query_cache '+configurelcc;
 writeln(cmd);
 fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/sbin/opendkim') then begin
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
procedure install_opendkim.dkmilter_install();
var
   source,smbsources,zdate:string;
   CODE_NAME:string;
   i:integer;
   RegExpr:TRegExpr;
   dk_dir:string;
begin
    CODE_NAME:='APP_OPENDKIM';
    source:=libs.COMPILE_GENERIC_APPS('dk-milter');

 if not DirectoryExists(source) then begin
     writeln('Install dk-milter libraries failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;

   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+source);
   fpsystem('/bin/cp -rf '+source+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');

   if not DirectoryExists(smbsources+'/libdk') then begin
     writeln('Install dk-milter libraries failed ...'+smbsources+'/libdk no such directory');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
   end;


   install.INSTALL_STATUS(CODE_NAME,50);
   install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
   SetCurrentDir(smbsources+'/libdk');
   fpsystem('make');

   writeln('Get the linux obj directory');
   SYS.DirDir(smbsources);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='obj\.Linux\.';
   for i:=0 to SYS.DirListFiles.Count -1 do begin
       if RegExpr.Exec(SYS.DirListFiles.Strings[i]) then begin
              writeln('Found : ',SYS.DirListFiles.Strings[i]);
              dk_dir:=smbsources+'/'+SYS.DirListFiles.Strings[i]+'/libdk';
              break;
       end;
   end;

   if length(dk_dir)=0 then begin
     writeln('Install dk-milter libraries failed ...unable to compile dkmilter no such directory');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
   end;

   if not FileExists(dk_dir+'/libdk.a') then begin
      writeln('Install dk-milter libraries failed ...'+dk_dir+'/libdk.a no such file');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
   end;
   // libmilter-dev
   fpsystem('/bin/cp '+dk_dir+'/libdk.a /usr/lib/');
   fpsystem('/bin/cp '+dk_dir+'/dk.h /usr/include/');
   ForceDirectories('/usr/include/sm');
   fpsystem('/bin/cp -rf '+smbsources+'/include/sm/* /usr/include/sm/');
   fpsystem('/binc/cp '+smbsources+'/include/sm/os/sm_os_freebsd.h /usr/include/sm/os/sm_os.h');


end;








end.
