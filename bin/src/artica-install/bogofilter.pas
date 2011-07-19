unit bogofilter;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;


  type
  Tbogofilter=class


private
     LOGS:Tlogs;
     SYS:TSystem;


public
    procedure   Free;
    constructor Create;
    function    DEAMON_BIN_PATH():string;
    function    Build_CommandLine(message_path:string;userdir:string):string;
    function    VERSION():string;

END;

implementation

constructor Tbogofilter.Create;
begin
     LOGS:=tlogs.Create();
     SYS:=Tsystem.Create;

end;
//##############################################################################
procedure Tbogofilter.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
function Tbogofilter.DEAMON_BIN_PATH():string;
begin
  if FileExists('/usr/bin/bogofilter') then exit('/usr/bin/bogofilter');
  if FileExists('/usr/local/bin/bogofilter') then exit('/usr/local/bin/bogofilter');
end;
//##############################################################################
function Tbogofilter.Build_CommandLine(message_path:string;userdir:string):string;
var command_line:string;
begin

    command_line:=DEAMON_BIN_PATH()+' --passthrough';
    command_line:=command_line+ ' --ham-true';
    command_line:=command_line+ ' --no-config-file ';
    command_line:=command_line+ ' --bogofilter-dir=' + userdir;
    command_line:=command_line+ ' --update-as-scored';
    command_line:=command_line+ ' --use-syslog';
    command_line:=command_line+ ' --no-header-tags';
    //command_line:=command_line + ' --robx=0.52 --min_dev=0.375 --robs=0.0178 --spam-cutoff=0.99 --ham-cutoff=0';
    command_line:=command_line + ' --input-file=' + message_path;
    command_line:=command_line + ' --output-file=' + message_path + '.bogo';
    result:=command_line;

end;
//##############################################################################
function Tbogofilter.VERSION():string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin

 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='version\s+([0-9\.])';
 if not FileExists('/opt/artica/tmp/bogover') then exit;
 l:=TstringList.Create;
 
 l.LoadFromFile('/opt/artica/tmp/bogover');
 
 for i:=0 to l.Count-1 do begin
     if RegExpr.Exec(l.Strings[i]) then begin
        result:=RegExpr.Match[1];
        break;
     end;
 end;
 
 RegExpr.free;
 l.free;
 
end;
//##############################################################################

end.
