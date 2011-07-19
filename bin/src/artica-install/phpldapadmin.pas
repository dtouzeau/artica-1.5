unit phpldapadmin;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr,zsystem,openldap;



  type
  tphpldapadmin=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     ldap:topenldap;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    VERSION():string;
    function    BIN_PATH():string;
    procedure   CONFIG();
END;

implementation

constructor tphpldapadmin.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       ldap:=topenldap.Create;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tphpldapadmin.free();
begin
    logs.Free;

end;
//##############################################################################
function tphpldapadmin.BIN_PATH():string;
begin
   if FileExists('/usr/share/phpldapadmin/index.php') then exit('/usr/share/phpldapadmin/index.php');
end;
//##############################################################################
function tphpldapadmin.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_PHPLDAPADMIN');
if length(result)>2 then exit;

filetmp:='/usr/share/phpldapadmin/VERSION';
if not FileExists(BIN_PATH()) then exit;
if not FileExists(filetmp) then exit;

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='RELEASE-([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
SYS.SET_CACHE_VERSION('APP_PHPLDAPADMIN',result);

end;
//#############################################################################
procedure tphpldapadmin.CONFIG();
var
   l:Tstringlist;
begin

if not FileExists(BIN_PATH()) then begin
   logs.Debuglogs('Starting lighttpd............: phpldapadmin is not installed');
   exit;
end;

fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.phpldapadmin.php --build');
end;
//#############################################################################


end.
