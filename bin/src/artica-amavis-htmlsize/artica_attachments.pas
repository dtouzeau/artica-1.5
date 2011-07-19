program artica_attachments;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,logs,global_conf,zsystem, htmlsizeHook;

  
  
var
   GLOBAL_INI   :myconf;
   hook         :ThtmlsizeHook;
begin


  hook:=ThtmlsizeHook.Create();
//  hook.ScanHeaders();







end.
