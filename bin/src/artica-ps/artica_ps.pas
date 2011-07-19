program artica_ps;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils
  { you can add units after this }, ps;

var
zps:tps;

begin

zps:=tps.Create;
zps.load_proc();

end.

