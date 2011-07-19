unit setup_backuppc;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,setup_libs,distridetect,install_generic,zsystem;

  type
  tsetup_backuppc=class


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

constructor tsetup_backuppc.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_backuppc.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_backuppc.xinstall();
var
   source_folder,cmd:string;
   APP_NAME:string;
begin
APP_NAME:='APP_BACKUPPC';
install.INSTALL_STATUS(APP_NAME,10);
install.INSTALL_PROGRESS(APP_NAME,'{downloading}');
source_folder:=libs.COMPILE_GENERIC_APPS('BackupPC');

if length(trim(source_folder))=0 then begin
     writeln('Install BackupPC failed...');
     install.INSTALL_STATUS(APP_NAME,110);
     exit;
end;
    install.INSTALL_PROGRESS(APP_NAME,'{configure}');
    install.INSTALL_STATUS(APP_NAME,20);
    writeln('Installing BackupPC from "',source_folder,'"');
    SetCurrentDir(source_folder);

    fpsystem('./configure.pl --batch --backuppc-user=backuppc --config-dir=/etc/backuppc --cgi-dir=/usr/share/backuppc/cgi-bin --data-dir=/var/lib/backuppc --html-dir=/usr/share/backuppc/image --install-dir=/usr/share/backuppc --html-dir-url /backuppc');
    install.INSTALL_STATUS(APP_NAME,40);
    install.INSTALL_STATUS(APP_NAME,70);

    SetCurrentDir('/root');
    if not FileExists('/usr/share/backuppc/bin/BackupPC') then begin
       install.INSTALL_PROGRESS(APP_NAME,'{failed}');
       install.INSTALL_STATUS(APP_NAME,110);
       exit;
    end;

    if FIleExists('/usr/share/backuppc/bin/cgi-bin/BackupPC_Admin') then fpsystem('/bin/mv /usr/share/backuppc/bin/cgi-bin/BackupPC_Admin /usr/share/backuppc/bin/cgi-bin/index.cgi');
    install.INSTALL_STATUS(APP_NAME,90);
    install.INSTALL_PROGRESS(APP_NAME,'{restarting}');
    fpsystem('/etc/init.d/artica-postfix restart backuppc');
    fpsystem('/etc/init.d/artica-postfix restart backuppc');
    fpsystem('/etc/init.d/artica-postfix restart apache');

    install.INSTALL_STATUS(APP_NAME,100);
    install.INSTALL_PROGRESS(APP_NAME,'{success}');
end;
//#########################################################################################
procedure tsetup_backuppc.xremove();
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
