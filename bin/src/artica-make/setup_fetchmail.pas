unit setup_fetchmail;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,fetchmail,zsystem,
  install_generic;

  type
  install_fetchmail=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
     source_folder,cmd:string;
     SYS:Tsystem;




public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure VNSTAT();
END;

implementation

constructor install_fetchmail.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
sys:=Tsystem.Create();
end;
//#########################################################################################
procedure install_fetchmail.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure install_fetchmail.xinstall();
var
   ftech:tfetchmail;
begin



if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
install.INSTALL_STATUS('APP_FETCHMAIL',10);


  SetCurrentDir('/root');
  install.INSTALL_STATUS('APP_FETCHMAIL',30);
  install.INSTALL_PROGRESS('APP_FETCHMAIL','{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('fetchmail');
  if not DirectoryExists(source_folder) then begin
     writeln('Install fetchmail failed...');
     install.INSTALL_STATUS('APP_FETCHMAIL',110);
     exit;
  end;
  writeln('Install fetchmail extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_FETCHMAIL',50);
    install.INSTALL_PROGRESS('APP_FETCHMAIL','{compiling}');
    SetCurrentDir(source_folder);
    fpsystem('./configure  --prefix=/usr --enable-nls --enable-fallback=no  --with-ssl=/usr --with-gssapi=/usr');
    fpsystem('make');


    install.INSTALL_PROGRESS('APP_FETCHMAIL','{installing}');
    fpsystem('make install');

    if FileExists('/usr/bin/fetchmail') then begin
         install.INSTALL_STATUS('APP_FETCHMAIL',100);
         install.INSTALL_PROGRESS('APP_FETCHMAIL','{installed}');
         ftech:=tfetchmail.Create(sys);
         ftech.FETCHMAIL_DAEMON_STOP();
         ftech.FETCHMAIL_START_DAEMON();
         ftech.Free;
         SYS.Free;
    end else begin
         install.INSTALL_PROGRESS('APP_FETCHMAIL','{failed}');
         install.INSTALL_STATUS('APP_FETCHMAIL',110);
    end;

    SetCurrentDir('/root');

end;
//#########################################################################################
procedure install_fetchmail.VNSTAT();
var
   APP:string;

begin
     APP:='APP_VNSTAT';
     install.INSTALL_STATUS(APP,10);
     SetCurrentDir('/root');
     install.INSTALL_STATUS(APP,30);
     install.INSTALL_PROGRESS(APP,'{downloading}');
     if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('vnstat');
     if not DirectoryExists(source_folder) then begin
        writeln('Install '+APP+' failed...');
        install.INSTALL_STATUS(APP,110);
        exit;
     end;
    fpsystem('/bin/touch /etc/artica-postfix/vmstat.ordered.cache');
    writeln('Install '+APP+' extracted on "'+source_folder+'"');
    install.INSTALL_STATUS(APP,50);
    install.INSTALL_PROGRESS(APP,'{compiling}');
    SetCurrentDir(source_folder);
    fpsystem('make all');

    if not Fileexists(source_folder+'/src/vnstat') then begin
        writeln('Install '+APP+' failed...'+source_folder+'/src/vnstat'+' no such file');
        install.INSTALL_STATUS(APP,110);
        exit;

    end;

    if not Fileexists(source_folder+'/src/vnstatd') then begin
        writeln('Install '+APP+' failed...'+source_folder+'/src/vnstatd'+' no such file');
        install.INSTALL_STATUS(APP,110);
        exit;
    end;

    if not Fileexists(source_folder+'/src/vnstati') then begin
        writeln('Install '+APP+' failed...'+source_folder+'/src/vnstati'+' no such file');
        install.INSTALL_STATUS(APP,110);
        exit;
    end;

    install.INSTALL_STATUS(APP,80);
    install.INSTALL_PROGRESS(APP,'{installing}');
    SetCurrentDir('/root');
    fpsystem('/bin/cp '+source_folder+'/src/vnstatd /usr/sbin/');
    fpsystem('/bin/cp '+source_folder+'/src/vnstat /usr/bin/');
    fpsystem('/bin/cp '+source_folder+'/src/vnstati /usr/bin/');
    fpsystem('/bin/cp '+source_folder+'/man/vnstat.1 /usr/share/man/man1');
    fpsystem('/bin/cp '+source_folder+'/man/vnstatd.1 /usr/share/man/man1');
    fpsystem('/bin/cp '+source_folder+'/man/vnstat.conf.5 /usr/share/man/man5');
    fpsystem('/bin/cp '+source_folder+'/man/vnstati.1 /usr/share/man/man1');

    if FileExists('/usr/sbin/vnstatd') then begin
         fpsystem('/etc/init.d/artica-postfix restart vnstat');
         install.INSTALL_STATUS(APP,100);
         install.INSTALL_PROGRESS(APP,'{installed}');

    end else begin
         install.INSTALL_PROGRESS(APP,'{failed}');
         install.INSTALL_STATUS(APP,110);
    end;
end;
//#########################################################################################





end.
