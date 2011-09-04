unit setup_sabnzbdplus;
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
  install_sabnzbdplus=class


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
      procedure xinstall_fuppes();
END;

implementation

constructor install_sabnzbdplus.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure install_sabnzbdplus.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_sabnzbdplus.xinstall();
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

    CODE_NAME:='APP_SABNZBDPLUS';


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,40);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('sabnzbd');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;


  forceDirectories('/usr/share/sabnzbdplus');

   writeln('copy source files in  /usr/share/sabnzbdplus');
   fpsystem('/bin/cp -rf '+source_folder+'/* /usr/share/sabnzbdplus/');
   writeln('copy source files in  /usr/share/sabnzbdplus done');
   install.INSTALL_STATUS(CODE_NAME,50);
   install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
   install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
   install.INSTALL_STATUS(CODE_NAME,80);
   fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
   SetCurrentDir('/root');



  if not FileExists('/usr/share/sabnzbdplus/SABnzbd.py') then begin
     writeln('Install '+CODE_NAME+' failed... /usr/share/sabnzbdplus/SABnzbd.py no such file');
     install.INSTALL_STATUS(CODE_NAME,110);
  end;

     fpsystem('/bin/cp -f /usr/share/sabnzbdplus/SABnzbd.py /usr/bin/sabnzbdplus');
     fpsystem('/bin/chmod 755 /usr/bin/sabnzbdplus');
     fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.sabnzbdplus.php --patch');
     SYS.set_INFO('EnableSabnZbdPlus','1');
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);

end;
//#########################################################################################
procedure install_sabnzbdplus.xinstall_fuppes();
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

  CODE_NAME:='APP_FUPPES';


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,40);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('fuppes');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(source_folder);
  cmd:='./configure --prefix=/usr --enable-transcoder-ffmpeg --enable-lame --enable-twolame --enable-vorbis  --enable-magickwand --enable-mad --enable-faad --disable-ffmpegthumbnailer';
  fpsystem(cmd);
  install.INSTALL_STATUS(CODE_NAME,70);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  fpsystem('make');
  install.INSTALL_STATUS(CODE_NAME,80);
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  fpsystem('make install');


   SetCurrentDir('/root');



  if not FileExists(SYS.LOCATE_GENERIC_BIN('fuppesd')) then begin
     writeln('Install '+CODE_NAME+' failed... fuppesd no such file');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;
     SYS.set_INFO('EnableFuppes','1');
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);

end;
//#########################################################################################
end.
