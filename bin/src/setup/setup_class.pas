unit setup_class;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,debian_class;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tsetup=class


private
     LOGS:Tlogs;
     D:boolean;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     debian:tdebian;
     function IsRoot():boolean;

public
    procedure   Free;
    constructor Create;

END;

implementation

constructor tsetup.Create;
begin
       if not IsRoot() then begin
          writeln('This program must be executed as root, abort...');
          halt(0);
       end;
       
       
       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       debian:=tdebian.create;
end;
//##############################################################################
procedure tsetup.free();
begin
    FreeAndNil(logs);
    FreeAndNil(SYS);
end;
//##############################################################################
function tsetup.IsRoot():boolean;
begin
if fpgeteuid=0 then exit(true);
exit(false);
end;
//##############################################################################
procedure tsetup.Welcome();

begin

writeln('Welcome on artica-postfix setup !');
writeln('This program will help you to install artica on your system.');
writeln('');
writeln('Be carrefull, this setup will change your system behavior');
writeln('It


end;




end.
