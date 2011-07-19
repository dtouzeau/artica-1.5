unit setup_gnuplot;
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
  install_gnuplot=class


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

constructor install_gnuplot.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure install_gnuplot.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_gnuplot.xinstall();
var
   CODE_NAME:string;
   cmd:string;
begin

    CODE_NAME:='APP_GNUPLOT';



  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('gnuplot');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;





  writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

  SetCurrentDir(source_folder);

cmd:='./configure ';
cmd:=cmd+'--prefix=/usr ';
cmd:=cmd+'--mandir=${prefix}/share/man ';
cmd:=cmd+'--infodir=${prefix}/share/info ';
cmd:=cmd+'--libexecdir=${prefix}/lib/gnuplot ';
cmd:=cmd+'--datadir=${prefix}/share/gnuplot ';
cmd:=cmd+'--with-gihdir=${prefix}/share/gnuplot ';
cmd:=cmd+'--without-lasergnu ';
cmd:=cmd+'--with-png --with-gd --without-lisp-files ';
cmd:=cmd+'--without-linux-vga ';
cmd:=cmd+'--with-readline=bsd ';
cmd:=cmd+'--without-x --disable-wxwidgets';

writeln(cmd);
fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make');
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,90);
  fpsystem('make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');

  SetCurrentDir('/root');

  if FileExists('/usr/bin/gnuplot') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     exit;
  end;


  writeln('Install '+CODE_NAME+' failed...');
  install.INSTALL_STATUS(CODE_NAME,110);
  exit;



end;
//#########################################################################################


end.
