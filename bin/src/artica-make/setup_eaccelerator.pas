unit setup_eaccelerator;
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
  tsetup_eacc=class


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
      procedure groupwareinstall();

END;

implementation

constructor tsetup_eacc.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure tsetup_eacc.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure tsetup_eacc.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   phpize:string;
   phpconfig:string;
   phplibraryPath:string;
begin

    CODE_NAME:='APP_EACCELERATOR';


    phpize:='/usr/bin/phpize';
    phpconfig:=SYS.LOCATE_PHP5_CONFIG_BIN();

    if not FileExists(phpize) then begin
       writeln('Unable to stat phpize');
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       install.INSTALL_STATUS(CODE_NAME,110);
       exit;
    end;

    if not FileExists(phpconfig) then begin
       writeln('Unable to stat php-config');
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       install.INSTALL_STATUS(CODE_NAME,110);
       exit;
    end;


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('eaccelerator');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;


  writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(source_folder);

  cmd:='./configure --with-eaccelerator-shared-memory --with-php-config='+phpconfig;

  writeln(cmd);
  fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists(SYS.LOCATE_EACCELERATOR_SO()) then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     writeln('Success');
     fpsystem('/etc/init.d/artica-postfix restart apache &');
     exit;
  end;


writeln('Install '+CODE_NAME+' failed...');
install.INSTALL_STATUS(CODE_NAME,110);
exit;



end;
//#########################################################################################

//

procedure tsetup_eacc.groupwareinstall();
var
   CODE_NAME:string;
   cmd:string;
   phpize:string;
   phpconfig:string;
   phplibraryPath:string;
begin

    CODE_NAME:='APP_EACCELERATOR';
    if not FileExists('/usr/local/apache-groupware/php5/bin/php-config') then exit;

    if fileExists(SYS.LOCATE_EACCELERATOR_SO_GW()) then begin
         install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
         install.INSTALL_STATUS(CODE_NAME,100);
         exit;
    end;

    phpconfig:='/usr/local/apache-groupware/php5/bin/php-config';

    if not FileExists(phpconfig) then begin
       writeln('Unable to stat php-config');
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       install.INSTALL_STATUS(CODE_NAME,110);
       exit;
    end;


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('eaccelerator');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;


  writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(source_folder);

  cmd:='./configure --with-eaccelerator-shared-memory --with-php-config='+phpconfig;

  writeln(cmd);
  fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists(SYS.LOCATE_EACCELERATOR_SO_GW()) then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     writeln('Success');
     fpsystem('/etc/init.d/artica-postfix restart apache-groupware &');
     exit;
  end;


writeln('Install '+CODE_NAME+' failed...');
install.INSTALL_STATUS(CODE_NAME,110);
exit;



end;
//#########################################################################################

end.
