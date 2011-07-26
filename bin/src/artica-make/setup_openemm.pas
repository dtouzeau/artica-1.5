unit setup_openemm;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,RegExpr in 'RegExpr.pas',
  unix,setup_libs,distridetect,
  install_generic,logs,obm,zsystem,strutils;

  type
  tsetup_openemm=class


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
   PROJECT_NAME:string;
   Arch:integer;
   SYS:Tsystem;
   function isJdk():boolean;
   function JAVA_HOME_GET():string;
   procedure JAVA_HOME_SET(path:string);
   procedure JDKSET();

public
      constructor Create();
      procedure Free;
      procedure openemm_install();
      procedure tomcat();
      procedure tomcat6();

END;

implementation

constructor tsetup_openemm.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
webserver_port:=install.lighttpd.LIGHTTPD_LISTEN_PORT();
   artica_admin:=install.openldap.get_LDAP('admin');
   artica_password:=install.openldap.get_LDAP('password');
   ldap_suffix:=install.openldap.get_LDAP('suffix');
   ldap_server:=install.openldap.get_LDAP('server');
   mysql_server:=install.SYS.MYSQL_INFOS('mysql_server');
   mysql_admin:=install.SYS.MYSQL_INFOS('database_admin');
   mysql_password:=install.SYS.MYSQL_INFOS('password');
   PROJECT_NAME:='APP_OPENEMM';
   SYS:=Tsystem.Create();
 distri:=tdistriDetect.Create;
writeln('RESULT.................: Installing/Upgrading');

Arch:=libs.ArchStruct();
writeln('RESULT.................: Architecture : ',Arch);
writeln('RESULT.................: Distribution : ',distri.DISTRINAME,' (DISTRINAME)');
writeln('RESULT.................: Major version: ',distri.DISTRI_MAJOR,' (DISTRI_MAJOR)');
writeln('RESULT.................: Artica Code  : ',distri.DISTRINAME_CODE,' (DISTRINAME_CODE)');

end;
//#########################################################################################
procedure tsetup_openemm.Free();
begin
  libs.Free;
end;
//#########################################################################################

procedure tsetup_openemm.tomcat6();
var
source_folder:string;
logs:Tlogs;
SYS:TSystem;
jdkver,cmd:string;
begin

 logs:=Tlogs.Create;
 SYS:=Tsystem.Create();
 source_folder:='';
PROJECT_NAME:='APP_TOMCAT6';
install.INSTALL_STATUS(PROJECT_NAME,10);

if not isJdk() then begin
   install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading} JDK');
   install.INSTALL_STATUS(PROJECT_NAME,20);
   if Arch=32 then source_folder:=libs.COMPILE_GENERIC_APPS('jdk-i386');
   if Arch=64 then source_folder:=libs.COMPILE_GENERIC_APPS('jdk-x64');
   install.INSTALL_STATUS(PROJECT_NAME,30);
   install.INSTALL_PROGRESS(PROJECT_NAME,'{installing} JDK');
   ForceDirectories('/opt/openemm');

   if Not DirectoryExists(source_folder) then begin
     writeln('Install JDK failed...');
     install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
     install.INSTALL_STATUS(PROJECT_NAME,110);
     exit;
   end;

   jdkver:=ExtractFileName(source_folder);
   writeln('Install JDK under /opt/openemm/'+jdkver);
   forceDirectories('/opt/openemm/'+jdkver);
   writeln('/bin/cp -rf '+source_folder+'/*  /opt/openemm/'+jdkver+'/');
   fpsystem('/bin/cp -rf '+source_folder+'/*  /opt/openemm/'+jdkver+'/');
   install.INSTALL_STATUS(PROJECT_NAME,40);
end;


if not FileExists('/opt/openemm/tomcat6/bin/startup.sh') then begin
   install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading} tomcat6');
   install.INSTALL_STATUS(PROJECT_NAME,45);
   source_folder:=libs.COMPILE_GENERIC_APPS('apache-tomcat6');
   if Not DirectoryExists(source_folder) then begin
     writeln('Install apache-tomecat6 failed...');
      install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
     install.INSTALL_STATUS(PROJECT_NAME,110);
     exit;
   end;

   install.INSTALL_STATUS(PROJECT_NAME,50);

   ForceDirectories('/opt/openemm/tomcat6');
   writeln('Install tomcat under /opt/openemm/tomcat6');
   writeln('/bin/cp -rf '+source_folder+'/*  /opt/openemm/tomcat6/');
   fpsystem('/bin/cp -rf '+source_folder+'/*  /opt/openemm/tomcat6/');
   install.INSTALL_STATUS(PROJECT_NAME,50);

   if not FileExists('/opt/openemm/tomcat6/bin/startup.sh') then begin
      writeln('Install tomcat failed to stat /opt/openemm/tomcat6/bin/startup.sh...');
      install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
      install.INSTALL_STATUS(PROJECT_NAME,110);
      exit;
   end;
end;
end;
//#########################################################################################

procedure tsetup_openemm.tomcat();
var
source_folder:string;
logs:Tlogs;
SYS:TSystem;
jdkver,cmd:string;
begin

 logs:=Tlogs.Create;
 SYS:=Tsystem.Create();
 source_folder:='';
PROJECT_NAME:='APP_TOMCAT';
install.INSTALL_STATUS(PROJECT_NAME,10);

if not isJdk() then begin
   install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading} JDK');
   install.INSTALL_STATUS(PROJECT_NAME,20);
   if Arch=32 then source_folder:=libs.COMPILE_GENERIC_APPS('jdk-i386');
   if Arch=64 then source_folder:=libs.COMPILE_GENERIC_APPS('jdk-x64');
   install.INSTALL_STATUS(PROJECT_NAME,30);
   install.INSTALL_PROGRESS(PROJECT_NAME,'{installing} JDK');
   ForceDirectories('/opt/openemm');

   if Not DirectoryExists(source_folder) then begin
     writeln('Install JDK failed...');
     install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
     install.INSTALL_STATUS(PROJECT_NAME,110);
     exit;
   end;

   jdkver:=ExtractFileName(source_folder);
   writeln('Install JDK under /opt/openemm/'+jdkver);
   forceDirectories('/opt/openemm/'+jdkver);
   writeln('/bin/cp -rf '+source_folder+'/*  /opt/openemm/'+jdkver+'/');
   fpsystem('/bin/cp -rf '+source_folder+'/*  /opt/openemm/'+jdkver+'/');
   install.INSTALL_STATUS(PROJECT_NAME,40);
end;


if not FileExists('/opt/openemm/tomcat/bin/startup.sh') then begin
   install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading} tomcat');
   install.INSTALL_STATUS(PROJECT_NAME,45);
   source_folder:=libs.COMPILE_GENERIC_APPS('apache-tomcat');
   if Not DirectoryExists(source_folder) then begin
     writeln('Install apache-tomecat failed...');
      install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
     install.INSTALL_STATUS(PROJECT_NAME,110);
     exit;
   end;

   install.INSTALL_STATUS(PROJECT_NAME,50);

   ForceDirectories('/opt/openemm/tomcat');
   writeln('Install tomcat under /opt/openemm/tomcat');
   writeln('/bin/cp -rf '+source_folder+'/*  /opt/openemm/tomcat/');
   fpsystem('/bin/cp -rf '+source_folder+'/*  /opt/openemm/tomcat/');
   install.INSTALL_STATUS(PROJECT_NAME,50);

   if not FileExists('/opt/openemm/tomcat/bin/startup.sh') then begin
      writeln('Install tomcat failed to stat /opt/openemm/tomcat/bin/startup.sh...');
      install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
      install.INSTALL_STATUS(PROJECT_NAME,110);
      exit;
   end;
end;
end;
//#########################################################################################
procedure tsetup_openemm.openemm_install();
begin

     if not FileExists('/opt/openemm/tomcat6/bin/startup.sh') then begin
          tomcat6();
     end;

     PROJECT_NAME:='APP_OPENEMM';

     if not FileExists('/opt/openemm/tomcat6/bin/startup.sh') then begin
       writeln('unable to install tomcat');
       install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
       install.INSTALL_STATUS(PROJECT_NAME,110);
     end;


  JDKSET();
  install.INSTALL_PROGRESS(PROJECT_NAME,'{downloading}');
  install.INSTALL_STATUS(PROJECT_NAME,20);
  source_folder:=libs.COMPILE_GENERIC_APPS('OpenEMM');
   if not DirectoryExists(source_folder) then begin
     writeln('Install OpenEMM failed...');
     install.INSTALL_STATUS(PROJECT_NAME,110);
     exit;
   end;
   install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');
   install.INSTALL_STATUS(PROJECT_NAME,90);
   forceDirectories('/home/openemm');
   cmd:='/bin/cp -rfp '+ExtractFilePath(source_folder)+'* /home/openemm/';
   writeln(cmd);
   fpsystem(cmd);


  install.INSTALL_PROGRESS(PROJECT_NAME,'{installing}');
  if FileExists('/home/openemm/bin/openemm.sh') then begin
   install.INSTALL_STATUS(PROJECT_NAME,100);
   install.INSTALL_PROGRESS(PROJECT_NAME,'{installed}');
   fpsystem('/etc/init.d/artica-postfix restart openemm');
   exit;
  end;

   install.INSTALL_PROGRESS(PROJECT_NAME,'{failed}');
   install.INSTALL_STATUS(PROJECT_NAME,110);


end;



function tsetup_openemm.isJdk():boolean;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   sJAVA_HOME_GET:string;
begin

sJAVA_HOME_GET:=JAVA_HOME_GET();
if DirectoryExists(sJAVA_HOME_GET) then begin
   if FileExists(sJAVA_HOME_GET+'/bin/java')then begin
      writeln('Found jdk  : ',sJAVA_HOME_GET);
      result:=true;
      exit;
   end;
end;

result:=false;
l:=TstringList.Create;
l.AddStrings(SYS.DirDir('/opt/openemm'));
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='jdk[0-9\.\_]+';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       if FileExists('/opt/openemm/'+l.Strings[i]+'/bin/java') then begin
         writeln('Found jdk  : ',l.Strings[i]);

         result:=True;
       end;
    end;
end;
 l.free;
 RegExpr.free;

end;
//#########################################################################################
function tsetup_openemm.JAVA_HOME_GET():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
begin
result:='';
l:=TstringList.Create;
l.LoadFromFile('/etc/environment');

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='JAVA_HOME=(.+)';
 for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
         writeln('found ',RegExpr.Match[1]);
         result:=RegExpr.Match[1];
         result:=AnsiReplaceText(result,'"','');
         break;
       end;
    end;

 l.free;
 RegExpr.free;

end;
//#########################################################################################
procedure tsetup_openemm.JAVA_HOME_SET(path:string);
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
begin

l:=TstringList.Create;
l.LoadFromFile('/etc/environment');

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='JAVA_HOME=(.+)';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
         writeln('found ',RegExpr.Match[1]);
         writeln('Setting JAVA_HOME="'+path+'" line: ',i);
         l.Strings[i]:='JAVA_HOME="'+path+'"';
         l.SaveToFile('/etc/environment');
         l.free;
         RegExpr.free;
         exit;
       end;
    end;
writeln('Setting JAVA_HOME="'+path+'"');
 l.Add('JAVA_HOME="'+path+'"');
 l.SaveToFile('/etc/environment');
 l.free;
 RegExpr.free;

 //env JAVA_HOME="/opt/openemm/jdk1.6.0_26" /opt/openemm/tomcat/bin/startup.sh

end;
//#########################################################################################
procedure tsetup_openemm.JDKSET();
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   sJAVA_HOME_GET:string;
begin

sJAVA_HOME_GET:=JAVA_HOME_GET();
if DirectoryExists(sJAVA_HOME_GET) then begin
   if FileExists(sJAVA_HOME_GET+'/bin/java')then begin
      writeln('Found jdk  : ',sJAVA_HOME_GET);
      exit;
   end;
end;


l:=TstringList.Create;
l.AddStrings(SYS.DirDir('/opt/openemm'));
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='jdk[0-9\.\_]+';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       if FileExists('/opt/openemm/'+l.Strings[i]+'/bin/java') then begin
         JAVA_HOME_SET('/opt/openemm/'+l.Strings[i]);
       end;
    end;
end;
 l.free;
 RegExpr.free;

end;
//#########################################################################################




end.
