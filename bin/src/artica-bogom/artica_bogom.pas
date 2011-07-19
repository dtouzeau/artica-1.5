program artica_bogom;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,
  bogom_parse,
  zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas',
  logs in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/logs.pas';

{$IFDEF WINDOWS}{$R artica_mailarchive.rc}{$ENDIF}

var
arch:   tbogom_parse;
SYS:    Tsystem;
zlogs   :tlogs;

begin

SYS:=Tsystem.Create;
zlogs:=Tlogs.Create;
if not SYS.BuildPids() then begin
     zlogs.Debuglogs('Other instance running...');
     halt(0);
end;
     
arch:=tbogom_parse.Create;
arch.ParseQueue();
end.

