program artica_filter_smtp_out;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,mimedefang_hook;

  
  
var
   hook         :thook;
begin


  hook:=thook.Create();
  hook.ScanHeaders();







end.
