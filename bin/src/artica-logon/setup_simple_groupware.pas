unit setup_simple_groupware;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,
  setup_suse_class,
  install_generic,
  setup_ubuntu_class;

  type
  tsetup_simple_groupware=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     procedure CheckDebianDependencies();
     procedure PatchingLigghtd();
     function SetConfig(key:string;value:string):boolean;


public
      constructor Create();
      procedure Free;
      procedure install_groupware();
END;

implementation

constructor tsetup_simple_groupware.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_simple_groupware.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_simple_groupware.install_groupware();
var
   source_folder,cmd:string;
   webserver_port:string;
   artica_admin:string;
   artica_password:string;
   ldap_suffix:string;
   mysql_server:string;
   mysql_admin:string;
   mysql_password:string;
   ldap_server:string;
begin
source_folder:='';
webserver_port:=install.lighttpd.LIGHTTPD_LISTEN_PORT();
   artica_admin:=install.openldap.get_LDAP('admin');
   artica_password:=install.openldap.get_LDAP('password');
   ldap_suffix:=install.openldap.get_LDAP('suffix');
   ldap_server:=install.openldap.get_LDAP('server');
   mysql_server:=install.SYS.MYSQL_INFOS('mysql_server');
   mysql_admin:=install.SYS.MYSQL_INFOS('database_admin');
   mysql_password:=install.SYS.MYSQL_INFOS('password');
   if length(mysql_password)=0 then mysql_password:='_';

if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',10);
  if distri.DISTRINAME_CODE='UBUNTU' then begin
     writeln('Check ubuntu dependencies...');
     CheckDebianDependencies();
  end;
  
  if distri.DISTRINAME_CODE='DEBIAN' then begin
     writeln('Check Debian dependencies...');
     CheckDebianDependencies();
  end;

  install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',30);
  
  
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('SimpleGroupware');
  if not DirectoryExists(source_folder) then begin
     writeln('Install simple groupware failed...');
     install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
     exit;
  end;
  writeln('Install simple groupware extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',50);
  
  writeln('Installing in /usr/share/sgs ....');
  ForceDirectories('/usr/share/sgs');

  fpsystem('/bin/cp -rf ' + source_folder + '/* /usr/share/sgs/');

  if not FileExists('/usr/share/sgs/bin/index.php') then begin
     writeln('Installing failed to stat /usr/share/sgs/bin/index.php');
     install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
     exit;
  end;
  
  fpsystem('/bin/chown -R www-data:www-data /usr/share/sgs');
  fpsystem('/bin/chmod -R 766 /usr/share/sgs/simple_cache');
  fpsystem('/bin/chmod -R 766 /usr/share/sgs/simple_store');
  fpsystem('/bin/chmod -R 766 /usr/share/sgs/bin');
  fpsystem('/bin/chmod -R 766 /usr/share/sgs/old');
  fpsystem('/bin/chmod -R 744 /usr/share/sgs/src');
  fpsystem('/bin/chmod -R 744 /usr/share/sgs/lang');
  fpsystem('/bin/chmod -R 744 /usr/share/sgs/import');
  if not DirectoryExists('/usr/share/artica-postfix/groupware') then fpsystem('/bin/ln -s --force /usr/share/sgs /usr/share/artica-postfix/groupware');
  PatchingLigghtd();
  
  
//  ligne 81 of setup.php
  cmd:='php -d register_argc_argv=1 -q /usr/share/artica-postfix/groupware/src/ext/install_unattended.php.txt ';
  cmd:=cmd + 'https://localhost:'+webserver_port+'/groupware/src/index.php ';
  cmd:=cmd + 'en ';
  cmd:=cmd + LowerCase(artica_admin)+' ';
  cmd:=cmd + artica_password+' ';
  cmd:=cmd + 'mysql ';
  cmd:=cmd + mysql_server+' ';
  cmd:=cmd + 'SimpleGroupware ';
  cmd:=cmd + mysql_admin+' ';
  cmd:=cmd + mysql_password+' ';
  fpsystem(cmd);
  writeln('');
  install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',70);
  
  if not SetConfig('SETUP_AUTH','ldap') then begin
       install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
       exit;
  end;
  
  if not SetConfig('SETUP_AUTH_HOSTNAME_LDAP','ldap') then begin
       install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
       exit;
  end;
  
  if not SetConfig('SETUP_AUTH_BASE_DN',ldap_suffix) then begin
       install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
       exit;
  end;

  if not SetConfig('SETUP_AUTH_LDAP_USER','cn='+artica_admin+','+ldap_suffix) then begin
       install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
       exit;
  end;
  
  if not SetConfig('SETUP_AUTH_LDAP_PW',artica_password) then begin
       install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
       exit;
  end;
  
  if not SetConfig('SETUP_AUTH_LDAP_UID','uid') then begin
       install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',110);
       exit;
  end;
  writeln('done');
  install.INSTALL_STATUS('APP_SIMPLE_GROUPEWARE',100);

end;
//#########################################################################################
procedure tsetup_simple_groupware.PatchingLigghtd();
var
   targetfile:string;
   l:TstringList;
   i:integer;
   found:boolean;
begin
   writeln('Patching for lighttpd compliance');
   targetfile:='/usr/share/sgs/src/core/setup.php';
   found:=false;
   if not FileExists(targetfile) then begin
      writeln('unable to sat '+targetfile);
      exit;
   end;
   l:=TstringList.Create;
   l.LoadFromFile(targetfile);
   For i:=0 to l.Count-1 do begin
     if pos(',"Apache"',l.Strings[i])>0 then begin
           writeln('Patching line ' + IntToStr(i));
           l.Strings[i]:='if (!empty($_SERVER["SERVER_SOFTWARE"]) and !strpos("@".$_SERVER["SERVER_SOFTWARE"],"Apache") and !strpos($_SERVER["SERVER_SOFTWARE"],"IIS") and !strpos("@".$_SERVER["SERVER_SOFTWARE"],"lighttpd")) {';
           found:=true;
           break;
     end;
   end;
   
    if found then l.SaveToFile(targetfile);
    l.free;
end;

procedure tsetup_simple_groupware.CheckDebianDependencies();
var
   ubuntu:tubuntu;
   l:TstringList;
   stripped:string;
begin

ubuntu:=Tubuntu.Create();
l:=TstringList.Create;
l.Add('ppthtml');
l.Add('catdoc');
l.Add('imagemagick');
l.Add('unzip');
l.Add('xpdf-utils');
l.add('mp3info');
l.add('exiv2');

writeln('Check '+ IntToStr(l.Count)+ ' packages');
stripped:=ubuntu.checkApps(l);
if length(stripped)>0 then begin
    writeln(stripped);
    ubuntu.InstallPackageListsSilent(stripped);
end;
ubuntu.free;
end;
//#########################################################################################
function tsetup_simple_groupware.SetConfig(key:string;value:string):boolean;
var
   targetfile:string;
   l:TstringList;
   i:integer;
   found:boolean;
   RegExpr:TRegExpr;
   newConfig:string;
begin
result:=false;
found:=false;

if not FileExists('/usr/share/sgs/simple_store/config.php') then exit;
l:=TstringList.Create;
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^define\('''+key;
newConfig:='define('''+key+''','''+value+''');';
writeln(newConfig);
l.LoadFromFile('/usr/share/sgs/simple_store/config.php');
For i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       l.Strings[i]:=newConfig;
       found:=true;
       break;
    end;

end;

if found then begin
   l.SaveToFile('/usr/share/sgs/simple_store/config.php');
end else begin
   l.Add(newConfig);
   l.SaveToFile('/usr/share/sgs/simple_store/config.php');
end;

 l.free;
 RegExpr.free;
 result:=true;
end;
//#########################################################################################
end.
