unit fail2ban;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr in 'RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';


  type
  tfail2ban=class


private
     LOGS:Tlogs;
     artica_path:string;
     SYS:Tsystem;
     EnableFail2Ban:integer;
     memory_bin_path:string;


public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
      procedure START();
      procedure STOP();
      function  PID_NUM():string;
      function  STATUS():string;
      function  BIN_PATH():string;
      function  VERSION:string;


END;

implementation

constructor tfail2ban.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableFail2Ban:=0;
       if not TryStrToInt(SYS.GET_INFO('EnableFail2Ban'),EnableFail2Ban) then EnableFail2Ban:=0;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tfail2ban.free();
begin
    logs.Free;
end;
//##############################################################################

function tfail2ban.BIN_PATH():string;
begin
    if length(memory_bin_path)>0 then exit(memory_bin_path);
    exit(SYS.LOCATE_GENERIC_BIN('fail2ban-client'));
end;
//#############################################################################
function tfail2ban.VERSION:string;
var
    binPath:string;
    mem:TStringList;
    commandline:string;
    tmp_file:string;
    RegExpr:TRegExpr;
    i:integer;
begin
    binPath:=BIN_PATH;

     result:=SYS.GET_CACHE_VERSION('APP_FAIL2BAN');
     if length(result)>0 then exit;


    if not FileExists(binpath) then begin
       exit;
    end;



    tmp_file:=logs.FILE_TEMP();
    commandline:=binPath+' -V >'+tmp_file +' 2>&1';
    fpsystem(commandline);
    mem:=TStringList.Create;
    if not FileExists(tmp_file) then exit;
    try mem.LoadFromFile(tmp_file); except exit(); end;

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Fail2Ban v([0-9\.]+)';

     for i:=0 to mem.Count-1 do begin
       if RegExpr.Exec(mem.Strings[i]) then begin
          result:=RegExpr.Match[1];
          SYS.SET_CACHE_VERSION('APP_FAIL2BAN',result);
          break;
       end;

     end;
     mem.Free;
     RegExpr.Free;

end;
//#############################################################################
procedure tfail2ban.START();
var pid,cache,cachecmd:string;
begin

    if FileExists(BIN_PATH()) then exit;





end;
//##############################################################################
function tfail2ban.PID_NUM():string;
begin
result:='';
if not FileExists('/var/run/dnsmasq.pid') then exit();
result:=SYS.GET_PID_FROM_PATH('/var/run/dnsmasq.pid');
end;
//##############################################################################
procedure tfail2ban.STOP();
var bin_path,pid:string;
begin


end;
//##############################################################################
function tfail2ban.STATUS():string;
var  pidpath:string;
begin
result:='';
if not FileExists(BIN_PATH()) then begin
        SYS.MONIT_DELETE('APP_FAIL2BAN');
        exit;
end;




end;
//#########################################################################################
end.

