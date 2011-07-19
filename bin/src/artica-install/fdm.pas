unit fdm;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tfdm=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    function    bin_path():string;
    function    STATUS:string;
    function    VERSION():string;
    function    CACHE_EXISTS():boolean;
    procedure   START_PROCESS(ConfigurationFile:String);

    //http://fdm.sourceforge.net/


END;

implementation

constructor tfdm.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tfdm.free();
begin
    logs.Free;
end;
//##############################################################################
procedure tfdm.START();
begin

   if not FileExists(bin_path()) then begin
      logs.Debuglogs('tfdm.START():: unable to stat fdm, (not installed) abort..;' );
      exit;
   end;
end;

//##############################################################################
function tfdm.STATUS:string;
var
ini:TstringList;
begin
   ini:=TstringList.Create;
   ini.Add('[FDM]');
   if FileExists(BIN_PATH()) then  begin
      ini.Add('running=1');
      ini.Add('application_installed=1');
      ini.Add('master_pid=0');
      ini.Add('master_memory=0');
      ini.Add('master_version='+VERSION());
      ini.Add('status=croned');
      ini.Add('service_name=APP_FDM');
   end;

   result:=ini.Text;
   ini.free;

end;
//##############################################################################
function tfdm.VERSION():string;
var
path:string;
 RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;

begin
    path:=logs.FILE_TEMP();
    fpsystem(bin_path()+ ' -vn -f /dev/null >'+ path+' 2>&1');
    if not FileExists(path) then exit;
    FileDatas:=TstringList.Create;
    FileDatas.LoadFromFile(path);
    logs.DeleteFile(path);
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='version is:\s+([0-9\.]+)';
    
    For i:=0 to FileDatas.Count-1 do begin
       if RegExpr.Exec(FileDatas.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;
    end;
    
    RegExpr.free;
    FileDatas.free;
end;
//##############################################################################
function tfdm.CACHE_EXISTS():boolean;
var
path:string;
 RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;

begin
    path:=logs.FILE_TEMP();
    result:=false;
    fpsystem(bin_path()+ ' -v >'+ path+' 2>&1');
    if not FileExists(path) then exit;
    FileDatas:=TstringList.Create;
    FileDatas.LoadFromFile(path);
    logs.DeleteFile(path);
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='cache\]';

    For i:=0 to FileDatas.Count-1 do begin
       if RegExpr.Exec(FileDatas.Strings[i]) then begin
          result:=true;
          break;
       end;
    end;

    RegExpr.free;
    FileDatas.free;
end;
//##############################################################################
function tfdm.bin_path():string;
begin
if FileExists('/usr/local/bin/fdm') then exit('/usr/local/bin/fdm');
if FileExists('/usr/bin/fdm') then exit('/usr/bin/fdm');
end;
//##############################################################################
procedure tfdm.START_PROCESS(ConfigurationFile:String);
var cmd:string;
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    uid:string;
    logfile:string;
begin




if not FileExists(bin_path()) then begin
   logs.Debuglogs('tfdm.START_PROCESS() FDM is not installed...');
   exit;
end;




if SYS.GET_INFO('EnableFDMFetch')<>'1' then begin
   logs.Debuglogs('tfdm.START_PROCESS() FDM is not enabled...');
   exit;
end;

 if not CACHE_EXISTS() then begin
    logs.syslogs('FDM cache is not enabled it is better for perfomance to use FDM has cache enabled...');
 end;

logs.Debuglogs('tfdm.START_PROCESS() FDM using binary '+bin_path());
 
 if not FileExists(ConfigurationFile) then begin
  logs.Debuglogs('tfdm.START_PROCESS() unable to stat ' + ConfigurationFile);
  exit;
 end;
 FileDatas:=TStringList.Create;
 FileDatas.LoadFromFile(ConfigurationFile);
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='uid=(.+)';
 for i:=0 to FileDatas.Count-1 do begin
     if RegExpr.Exec(FileDatas.Strings[i]) then begin
        uid:= RegExpr.Match[1];
     end;
 end;
 
 FileDatas.free;
 RegExpr.free;
 
 if length(uid)=0 then begin
      logs.Debuglogs('tfdm.START_PROCESS() unable to determine uid');
      exit;
 end;
 logfile:=artica_path+'/ressources/logs/fdm.'+uid+'.log';
 
 if FIleExists(logfile) then logs.DeleteFile(logfile);
 
 forcedirectories('/etc/fdm');
 SYS.AddUserToGroup('_fdm','_fdm','','');
 logs.OutputCmd('/bin/chown -R _fdm /etc/fdm');
 
 cmd:=bin_path() + ' -u root -vf '+ConfigurationFile+' fetch >>'+logfile+' 2>&1';
 logs.Debuglogs(cmd);
 logs.Syslogs('Starting FDM with configuration file ' + ConfigurationFile);
 logs.Syslogs('Starting saving FDM logs in '+logfile);
 fpsystem(cmd);
end;
//##############################################################################




end.
