unit base_unix;


{$mode objfpc}{$H+}
interface

uses
Classes, SysUtils,variants, Linux,BaseUnix,IniFiles,strutils;

  type
  Tunix=class


private


public
    procedure Free;
    constructor Create;
    function get_file_permission(path:string):string;

END;

implementation

constructor Tunix.Create;
begin

end;
//##############################################################################
procedure Tunix.Free;
begin

end;

function Tunix.get_file_permission(path:string):string;
var
   s:string;
   info : stat;
   i:Integer;
begin
fpstat (path,info);
writeln;


  writeln ('Result of fstat on file');
  writeln ('Inode   : ',info.st_ino);
  writeln ('Mode    : ',info.st_mode);
  writeln ('nlink   : ',info.st_nlink);
  writeln ('uid     : ',info.st_uid);
  writeln ('gid     : ',info.st_gid);
  writeln ('rdev    : ',info.st_rdev);
  writeln ('Size    : ',info.st_size);
  writeln ('Blksize : ',info.st_blksize);
  writeln ('Blocks  : ',info.st_blocks);
  writeln ('atime   : ',info.st_atime);
  writeln ('mtime   : ',info.st_mtime);
  writeln ('ctime   : ',info.st_ctime);
end;
//##############################################################################


end.

