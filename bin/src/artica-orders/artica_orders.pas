program artica_orders;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,RegExpr,unix,baseUnix, principale,global_conf,logs,zSystem,monitorix;
var
   SYS:Tsystem;
   orders:torders;
//##############################################################################
begin
  SYS:=Tsystem.Create();
  if SYS.BuildPids() then begin
      orders:=torders.Create;
      halt(0);
  end;
  
  halt(0);
end.

