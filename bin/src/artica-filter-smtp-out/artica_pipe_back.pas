program artica_pipe_back;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,logs,global_conf,zsystem, mimedefhook;

  
  
var
   GLOBAL_INI   :myconf;
   hook         :TmimedefHook;
begin


  hook:=TmimedefHook.Create();
  hook.ScanHeaders();







end.
