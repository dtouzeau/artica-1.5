program Artica_learn;

{$mode objfpc}{$H+}

uses
  Classes,logs,unix,BaseUnix,SysUtils,ldaplearn;

var
XSETS                   :tldaplearn;
//##############################################################################

begin
  XSETS:=tldaplearn.Create();

  if ParamStr(1)='--clean' then begin
       XSETS.CleanCacheFile();
       halt(0);
  end;

  XSETS.sa_learn();
  XSETS.free;
  Halt(0);

end.
