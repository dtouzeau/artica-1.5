unit setup_mldonkey;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,setup_libs,distridetect,install_generic,zsystem;

  type
  tsetup_mldonkey=class


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

constructor tsetup_mldonkey.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_mldonkey.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_mldonkey.xinstall();
var
   source_folder,cmd:string;
   APP_NAME:string;
begin
APP_NAME:='APP_MLDONKEY';
install.INSTALL_STATUS(APP_NAME,10);
install.INSTALL_PROGRESS(APP_NAME,'{downloading}');
source_folder:=libs.COMPILE_GENERIC_APPS('mldonkey');

if length(trim(source_folder))=0 then begin
     writeln('Install mldonkey failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    install.INSTALL_PROGRESS(APP_NAME,'{configure}');
    install.INSTALL_STATUS(APP_NAME,20);
    writeln('Installing mldonkey from "',source_folder,'"');
    SetCurrentDir(source_folder);
    fpsystem('./configure --prefix=/usr --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var --libexecdir="\${prefix}/lib" --enable-profile --disable-gui --enable-batch');
    install.INSTALL_STATUS(APP_NAME,40);
    fpsystem('make');
    install.INSTALL_STATUS(APP_NAME,70);
    fpsystem('make install');
    SetCurrentDir('/root');
    if not FileExists('/usr/bin/mlnet') then begin
        writeln('Installing mldonkey from "',source_folder,'" failed');
       install.INSTALL_PROGRESS(APP_NAME,'{failed}');
       install.INSTALL_STATUS(APP_NAME,110);
       exit;
    end;
    install.INSTALL_STATUS(APP_NAME,100);
    install.INSTALL_PROGRESS(APP_NAME,'{success}');
    writeln('Installing mldonkey from "',source_folder,'" success');
end;
//#########################################################################################
procedure tsetup_mldonkey.xremove();
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
