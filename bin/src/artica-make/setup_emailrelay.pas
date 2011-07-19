unit setup_emailrelay;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,setup_libs,distridetect,install_generic,zsystem;

  type
  tsetup_emailrelay=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;

public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure xremove();
END;

implementation

constructor tsetup_emailrelay.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_emailrelay.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_emailrelay.xinstall();
var
   source_folder,cmd:string;
   APP_NAME:string;
begin
APP_NAME:='APP_EMAILRELAY';
install.INSTALL_STATUS(APP_NAME,10);
install.INSTALL_PROGRESS(APP_NAME,'{downloading}');
source_folder:=libs.COMPILE_GENERIC_APPS('emailrelay');

if length(trim(source_folder))=0 then begin
     writeln('Install email-relay failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    install.INSTALL_PROGRESS(APP_NAME,'{configure}');
    install.INSTALL_STATUS(APP_NAME,20);
    writeln('Installing email-relay from "',source_folder,'"');

    forceDirectories('/root/emailrelay-compile');
    fpsystem('/bin/cp -rf '+source_folder+'/* /root/emailrelay-compile/');

    SetCurrentDir('/root/emailrelay-compile');
    fpsystem('./configure --prefix=/usr --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var --libexecdir="\${prefix}/lib" --with-openssl --without-doxygen');
    install.INSTALL_STATUS(APP_NAME,40);
    fpsystem('make');
    install.INSTALL_STATUS(APP_NAME,70);
    fpsystem('make install');
    SetCurrentDir('/root');
    if not FileExists('/usr/sbin/emailrelay') then begin
       fpsystem('/bin/touch /etc/artica-postfix/make-email-relay-failed');
       install.INSTALL_PROGRESS(APP_NAME,'{failed}');
       install.INSTALL_STATUS(APP_NAME,110);
       exit;
    end;
    install.INSTALL_STATUS(APP_NAME,100);
    install.INSTALL_PROGRESS(APP_NAME,'{success}');
end;
//#########################################################################################
procedure tsetup_emailrelay.xremove();
var
   l:Tstringlist;
   i:integer;
begin
   l:=Tstringlist.Create;
   l.Add('/usr/share/doc/emailrelay');
   l.Add('/var/spool/emailrelay');
   l.add('/usr/lib/emailrelay');

   for i:=0 to l.Count-1 do begin
       if length(l.Strings[i])>0 then begin
          if DirectoryExists(l.Strings[i]) then begin
             writeln('Removing directory '+l.Strings[i]);
             fpsystem('/bin/rm -rfv '+l.Strings[i]+'/*');
             fpsystem('/bin/rmdir '+l.Strings[i]);
          end;
       end;
   end;
   l.free;
   l:=Tstringlist.Create;
   l.add('/usr/sbin/emailrelay');
   l.add('/usr/sbin/emailrelay-submit');
   l.add('/usr/sbin/emailrelay-passwd');
   l.add('/usr/sbin/emailrelay-gui');
   l.add('/usr/sbin/emailrelay-gui.real');

   for i:=0 to l.Count-1 do begin
       if length(l.Strings[i])>0 then begin
          if FileExists(l.Strings[i]) then begin
             writeln('Removing File '+l.Strings[i]);
             fpsystem('/bin/rm '+l.Strings[i]);
          end;
       end;
   end;


end;
//#########################################################################################
end.
