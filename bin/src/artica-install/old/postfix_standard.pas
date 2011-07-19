unit postfix_standard;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,Unix,RegExpr in 'RegExpr.pas',zsystem,global_conf;

  type
  Tpostfix_standard=class


private
       GLOBAL_INI:MyConf;
       zSystem:Tsystem;




public
      constructor Create();
      procedure Free();
      debug:boolean;
END;

implementation

constructor Tpostfix_standard.Create();
begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=MyConf.Create();
       zSystem:=Tsystem.Create();
end;

procedure Tpostfix_standard.Free();
begin
  zSystem.Free;
end;
end.

