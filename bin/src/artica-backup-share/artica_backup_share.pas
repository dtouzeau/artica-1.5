program artica_backup_share;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils
  { you can add units after this },class_backup_share;


var bck:tbackupshare;
begin

bck:=tbackupshare.Create;


if ParamStr(1)='--mount' then begin
   bck.SigleMount(ParamStr(2));
   halt(0)
end;



bck.ParsingRemoteFolders();
halt(0);


end.

