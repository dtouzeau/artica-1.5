unit setup_postfilter;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distridetect,setup_suse_class,install_generic;

  type
  tsetup_postfilter=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
public
      constructor Create();
      procedure Free;
      procedure xinstall();
END;

implementation

constructor tsetup_postfilter.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
end;
//#########################################################################################
procedure tsetup_postfilter.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsetup_postfilter.xinstall();
var

   source_folder,cmd:string;
   l:Tstringlist;

begin



install.INSTALL_STATUS('APP_POSTFILTER',10);
install.INSTALL_PROGRESS('APP_POSTFILTER','{downloading}');
source_folder:=libs.COMPILE_GENERIC_APPS('postfilter');

if length(trim(source_folder))=0 then begin
     writeln('Install postfilter failed...');
     install.INSTALL_STATUS('APP_POSTFILTER',110);
     exit;
end;
    install.INSTALL_PROGRESS('APP_POSTFILTER','{installing}');
    install.INSTALL_STATUS('APP_POSTFILTER',20);
writeln('Installing postfilter from "',source_folder,'"');

SetCurrentDir(source_folder);

l:=Tstringlist.Create;
l.Add('root_directory=/usr/share/postfilter');
l.Add('run_directory=/var/run/postfilter');
l.Add('postfilter_owner=postfix');
l.Add('postfilter_group=postfix');
forcedirectories('/etc/postfilter');
l.SaveToFile('/etc/postfilter/install.cf');
l.free;
cmd:='./postfilter-install -non-interactive install_root=/ tempdir='+source_folder+' config_directory=/etc/postfilter daemon_directory=/usr/libexec/postfix command_directory=/usr/sbin html_directory=/usr/share/postfilter mail_owner=postfix';
writeln(cmd);
fpsystem(cmd);

end;
//#########################################################################################
end.
