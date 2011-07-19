program artica_logon;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,zsystem, logon;


  var
     SYS:Tsystem;
     zlogon:tlogon;

begin


  zlogon:=tlogon.Create();
  zlogon.Menu();
  halt(0);







end.

