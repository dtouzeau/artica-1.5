program setup_freebsd;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils
  { you can add units after this }, setup_freebsd_class;

  var
     install:tubuntu;
begin

  install:=tubuntu.Create;

end.

