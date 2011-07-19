program artica_get;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,wget
  {,global_conf in '../global_conf.pas'}
  { add your units here };


   var swget:Twget;
begin

  
  swget:=Twget.Create();
  swget.GetFile();
  
  
  swget.Free;

















end.

