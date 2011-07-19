unit squidguard_page;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,awstats;

type
  TStringDynArray = array of string;

  type
  tsquidguard_page=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     awstats:tawstats;
     mem_pid:string;
     EnableSquidGuardHTTPService:integer;
     squidGuardEnabled:integer;
     SQUIDEnable:integer;
     EnableUfdbGuard:integer;
     EnableSquidClamav:integer;
    function    Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;

public
EnableLighttpd:integer;
    InsufficentRessources:Boolean;
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    function    LIGHTTPD_BIN_PATH():string;
    function    LIGHTTPD_PID():string;
    procedure   STOP();
    function    LIGHTTPD_VERSION():string;
    FUNCTION    STATUS():string;
    function    PHP5_CGI_BIN_PATH():string;
    function    DEFAULT_CONF():string;


END;

implementation

constructor tsquidguard_page.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       forcedirectories('/opt/artica/tmp');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if Not TryStrToInt(SYS.GET_INFO('EnableSquidGuardHTTPService'),EnableSquidGuardHTTPService) then EnableSquidGuardHTTPService:=1;
       if Not TryStrToInt(SYS.GET_INFO('squidGuardEnabled'),squidGuardEnabled) then squidGuardEnabled:=0;
       if Not TryStrToInt(SYS.GET_INFO('EnableUfdbGuard'),EnableUfdbGuard) then EnableUfdbGuard:=0;
       if Not TryStrToInt(SYS.GET_INFO('EnableSquidClamav'),EnableSquidClamav) then EnableSquidClamav:=0;




       if not TryStrToInt(SYS.GET_INFO('SQUIDEnable'),SQUIDEnable) then SQUIDEnable:=1;
       if EnableSquidClamav=1 then squidGuardEnabled:=1;
       if EnableUfdbGuard=1 then  squidGuardEnabled:=1;
       if squidGuardEnabled=0 then EnableSquidGuardHTTPService:=0;
       if SQUIDEnable=0 then EnableSquidGuardHTTPService:=0;




       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tsquidguard_page.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
function tsquidguard_page.LIGHTTPD_BIN_PATH():string;
begin
exit(SYS.LOCATE_LIGHTTPD_BIN_PATH());
end;
//##############################################################################
function tsquidguard_page.PHP5_CGI_BIN_PATH():string;
begin
   if FileExists('/usr/bin/php-fcgi') then exit('/usr/bin/php-fcgi');
   if FileExists('/usr/bin/php-cgi') then exit('/usr/bin/php-cgi');
   if FileExists('/usr/local/bin/php-cgi') then exit('/usr/local/bin/php-cgi');
end;
//##############################################################################
procedure tsquidguard_page.START();
var
  pid:string;
  count:integer;
begin


logs.Debuglogs('###################### SQUID GUARD WEB #####################');

if EnableSquidGuardHTTPService=0 then begin
    logs.Debuglogs('Starting......: squidguard-lighttpd (disabled)');
    STOP();
end;


   if not FileExists(LIGHTTPD_BIN_PATH()) then begin
       logs.Debuglogs('START():: it seems that lighttpd is not installed... Aborting');
       exit;
   end;

    if FileExists('/var/log/artica-postfix/squidguard-lighttpd.log') then logs.DeleteFile('/var/log/artica-postfix/squidguard-lighttpd.log');

   pid:=LIGHTTPD_PID();


   if SYS.PROCESS_EXIST(pid) then begin
      logs.Debuglogs('Starting......: squidguard-lighttpd daemon is already running using PID ' + LIGHTTPD_PID() + '...');
      logs.Debuglogs('START():: squidguard-lighttpd already running with PID number ' + pid);
      exit();
   end;

    DEFAULT_CONF();
    logs.OutputCmd(LIGHTTPD_BIN_PATH()+ ' -f /etc/artica-postfix/squidguard-lighttpd.conf');
    mem_pid:='';

  count:=0;
  while not SYS.PROCESS_EXIST(LIGHTTPD_PID()) do begin
     sleep(100);
     inc(count);
     if count>100 then break;
  end;


   if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
      logs.Debuglogs('Starting......: squidguard-lighttpd.: Failed "' + LIGHTTPD_BIN_PATH()+ ' -f /etc/artica-postfix/squidguard-lighttpd.conf"');
    end else begin
      logs.Debuglogs('Starting......: squidguard-lighttpd.: Success (PID ' + LIGHTTPD_PID() + ')');
   end;

end;
//##############################################################################
procedure tsquidguard_page.STOP();
 var
    count      :integer;
begin

     count:=0;
    if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
       writeln('Stopping squidguard-lighttpd..: Already stopped');
       exit;
    end;



  writeln('Stopping squidguard-lighttpd..: ' + LIGHTTPD_PID() + ' PID..');
  logs.OutputCmd('/bin/kill ' + LIGHTTPD_PID());
  count:=0;
  while SYS.PROCESS_EXIST(LIGHTTPD_PID()) do begin
     sleep(100);
     inc(count);
     if count>100 then begin
        writeln('Stopping squidguard-lighttpd..: Failed force kill');
        logs.OutputCmd('/bin/kill -9 '+LIGHTTPD_PID());
        exit;
     end;
  end;

  mem_pid:='';
   if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
       writeln('Stopping squidguard-lighttpd..: Stopped');
       exit;
    end;

  count:=0;
  while SYS.PROCESS_EXIST(LIGHTTPD_PID()) do begin
     sleep(100);
     inc(count);
     if count>100 then begin
        writeln('Stopping squidguard-lighttpd..: Failed force kill');
        logs.OutputCmd('/bin/kill -9 '+LIGHTTPD_PID());
        exit;
     end;
  end;

   if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
       writeln('Stopping squidguard-lighttpd..: Stopped');
       exit;
    end;
     writeln('Stopping squidguard-lighttpd..: failed');

end;
//##############################################################################
FUNCTION tsquidguard_page.STATUS():string;
var
pidpath:string;
begin
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --squidguard-http >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//#########################################################################################
function tsquidguard_page.LIGHTTPD_VERSION():string;
var
     l:TstringList;
     RegExpr:TRegExpr;
     i:integer;
     tmpstr:string;
begin
    if not FileExists(LIGHTTPD_BIN_PATH()) then exit;

    result:=SYS.GET_CACHE_VERSION('APP_LIGHTTPD');
    if length(result)>0 then exit;
    tmpstr:=logs.FILE_TEMP();

    fpsystem(LIGHTTPD_BIN_PATH()+' -v >'+tmpstr+' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TStringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='lighttpd-([0-9\.]+)';
    For i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            logs.Debuglogs('LIGHTTPD_VERSION:: ' + result);
        end;
    end;

    SYS.SET_CACHE_VERSION('APP_LIGHTTPD',result);

    l.free;
    RegExpr.Free;
end;
//##############################################################################
function tsquidguard_page.LIGHTTPD_PID():string;
var
   lighttpd_bin:string;
   pid:string;
begin

   pid:=SYS.GET_PID_FROM_PATH('/var/run/lighttpd/squidguard-lighttpd.pid');
   if SYS.PROCESS_EXIST(pid) then begin
        result:=pid;
        mem_pid:=pid;
       exit;
   end;
   exit;
   lighttpd_bin:=LIGHTTPD_BIN_PATH();
   if FileExists(lighttpd_bin) then begin
      result:=SYS.PIDOF_PATTERN(lighttpd_bin + ' -f /etc/artica-postfix/squidguard-lighttpd.conf');
      mem_pid:=result;
      exit;
   end;

end;
//##############################################################################
function tsquidguard_page.DEFAULT_CONF():string;
var
l:TstringList;
PHP_FCGI_CHILDREN:Integer;
PHP_FCGI_MAX_REQUESTS:integer;
max_procs:integer;
RegExpr:TRegExpr;
LighttpdUserAndGroup,username,group:string;
SquidGuardApachePort:integer;
begin
result:='';
l:=TstringList.Create;

PHP_FCGI_CHILDREN:=3;
max_procs:=2;
PHP_FCGI_MAX_REQUESTS:=500;
if not InsufficentRessources then begin
     PHP_FCGI_CHILDREN:=2;
     PHP_FCGI_MAX_REQUESTS:=1000;
     max_procs:=1;
end;

   LighttpdUserAndGroup:=SYS.LIGHTTPD_GET_USER();
   logs.Debuglogs('Starting......: squidguard http server username:group "'+LighttpdUserAndGroup+'"');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^(.+?):(.+)';
   if length(LighttpdUserAndGroup)=0 then LighttpdUserAndGroup:='www-data:www-data';
   if not RegExpr.Exec(LighttpdUserAndGroup) then begin
      logs.Debuglogs('Starting......: Apache daemon unable to stat username and group !');
      exit;
   end;
username:=RegExpr.Match[1];
group:=RegExpr.Match[2];
if not TryStrToInt(sys.GET_INFO('SquidGuardApachePort'),SquidGuardApachePort) then SquidGuardApachePort:=9020;
if not FileExists('/var/log/artica-postfix/squidguard-lighttpd.log') then logs.WriteToFile('#','/var/log/artica-postfix/squidguard-lighttpd.log');
if not FileExists('/var/log/artica-postfix/squidguard-lighttpd-error.log') then logs.WriteToFile('#','/var/log/artica-postfix/squidguard-lighttpd-error.log');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /var/log/artica-postfix/squidguard-lighttpd.log');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /var/log/artica-postfix/squidguard-lighttpd-error.log');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /usr/share/artica-postfix/bin/install/squid/adzap/zaps/*');


l.Add('#artica-postfix saved by artica lighttpd.conf');
l.Add('');
l.Add('server.modules = (');
l.Add('        "mod_alias",');
l.Add('        "mod_access",');
l.Add('        "mod_accesslog",');
l.Add('        "mod_compress",');
l.Add('        "mod_fastcgi",');
l.Add('        "mod_cgi",');
l.Add('	       "mod_status"');
l.Add(')');
l.Add('');
l.Add('server.document-root        = "/usr/share/artica-postfix"');
l.Add('server.username = "'+username+'"');
l.Add('server.groupname = "'+group+'"');
l.Add('server.errorlog             = "/var/log/artica-postfix/squidguard-lighttpd-error.log"');
l.Add('index-file.names            = ( "exec.squidguard.php")');
l.Add('');
l.Add('mimetype.assign             = (');
l.Add('  ".pdf"          =>      "application/pdf",');
l.Add('  ".sig"          =>      "application/pgp-signature",');
l.Add('  ".spl"          =>      "application/futuresplash",');
l.Add('  ".class"        =>      "application/octet-stream",');
l.Add('  ".ps"           =>      "application/postscript",');
l.Add('  ".torrent"      =>      "application/x-bittorrent",');
l.Add('  ".dvi"          =>      "application/x-dvi",');
l.Add('  ".gz"           =>      "application/x-gzip",');
l.Add('  ".pac"          =>      "application/x-ns-proxy-autoconfig",');
l.Add('  ".swf"          =>      "application/x-shockwave-flash",');
l.Add('  ".tar.gz"       =>      "application/x-tgz",');
l.Add('  ".tgz"          =>      "application/x-tgz",');
l.Add('  ".tar"          =>      "application/x-tar",');
l.Add('  ".zip"          =>      "application/zip",');
l.Add('  ".mp3"          =>      "audio/mpeg",');
l.Add('  ".m3u"          =>      "audio/x-mpegurl",');
l.Add('  ".wma"          =>      "audio/x-ms-wma",');
l.Add('  ".wax"          =>      "audio/x-ms-wax",');
l.Add('  ".ogg"          =>      "application/ogg",');
l.Add('  ".wav"          =>      "audio/x-wav",');
l.Add('  ".gif"          =>      "image/gif",');
l.Add('  ".jar"          =>      "application/x-java-archive",');
l.Add('  ".jpg"          =>      "image/jpeg",');
l.Add('  ".jpeg"         =>      "image/jpeg",');
l.Add('  ".png"          =>      "image/png",');
l.Add('  ".xbm"          =>      "image/x-xbitmap",');
l.Add('  ".xpm"          =>      "image/x-xpixmap",');
l.Add('  ".xwd"          =>      "image/x-xwindowdump",');
l.Add('  ".css"          =>      "text/css",');
l.Add('  ".html"         =>      "text/html",');
l.Add('  ".htm"          =>      "text/html",');
l.Add('  ".js"           =>      "text/javascript",');
l.Add('  ".asc"          =>      "text/plain",');
l.Add('  ".c"            =>      "text/plain",');
l.Add('  ".cpp"          =>      "text/plain",');
l.Add('  ".log"          =>      "text/plain",');
l.Add('  ".conf"         =>      "text/plain",');
l.Add('  ".text"         =>      "text/plain",');
l.Add('  ".txt"          =>      "text/plain",');
l.Add('  ".dtd"          =>      "text/xml",');
l.Add('  ".xml"          =>      "text/xml",');
l.Add('  ".mpeg"         =>      "video/mpeg",');
l.Add('  ".mpg"          =>      "video/mpeg",');
l.Add('  ".mov"          =>      "video/quicktime",');
l.Add('  ".qt"           =>      "video/quicktime",');
l.Add('  ".avi"          =>      "video/x-msvideo",');
l.Add('  ".asf"          =>      "video/x-ms-asf",');
l.Add('  ".asx"          =>      "video/x-ms-asf",');
l.Add('  ".wmv"          =>      "video/x-ms-wmv",');
l.Add('  ".bz2"          =>      "application/x-bzip",');
l.Add('  ".tbz"          =>      "application/x-bzip-compressed-tar",');
l.Add('  ".tar.bz2"      =>      "application/x-bzip-compressed-tar",');
l.Add('  ""              =>      "application/octet-stream",');
l.Add(' )');
l.Add('');
l.Add('');
l.Add('accesslog.filename          = "/var/log/artica-postfix/squidguard-lighttpd.log"');
l.Add('url.access-deny             = ( "~", ".inc",".log",".ini","ressources","computers","user-backup","logon.php","index.php")');
l.Add('');
l.Add('static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )');
l.Add('server.port                 = '+IntToStr(SquidGuardApachePort));
l.Add('#server.bind                = "127.0.0.1"');
l.Add('#server.error-handler-404   = "/error-handler.html"');
l.Add('#server.error-handler-404   = "/error-handler.php"');
l.Add('server.pid-file             = "/var/run/lighttpd/squidguard-lighttpd.pid"');
l.Add('server.max-fds 		   = 2048');
l.Add('server.network-backend      = "write"');
l.Add('server.follow-symlink = "enable"');
l.Add('');
l.Add('');
l.Add('fastcgi.server = ( ".php" =>((');
l.Add('         "bin-path" => "/usr/bin/php-cgi",');
l.Add('         "socket" => "/var/run/lighttpd/php.squidguard.socket",');
l.Add('		"min-procs" => 1,');
l.Add('         "max-procs" => '+IntToStr(max_procs)+',');
l.Add('		"max-load-per-proc" => 4,');
l.Add('         "idle-timeout" => 10,');
l.Add('         "bin-environment" => (');
l.Add('             "PHP_FCGI_CHILDREN" => "'+IntToStr(PHP_FCGI_CHILDREN)+'",');
l.Add('             "PHP_FCGI_MAX_REQUESTS" => "'+intToStr(PHP_FCGI_MAX_REQUESTS)+'"');
l.Add('          ),');
l.Add('          "bin-copy-environment" => (');
l.Add('            "PATH", "SHELL", "USER"');
l.Add('           ),');
l.Add('          "broken-scriptfilename" => "enable"');
l.Add('        ))');
l.Add(')');

l.Add('alias.url += ( "/css/" => "/usr/share/artica-postfix/css/" )');
l.Add('alias.url += ( "/img/" => "/usr/share/artica-postfix/img/" )');
l.Add('alias.url += ( "/js/" => "/usr/share/artica-postfix/js/" )');
l.Add('alias.url += ( "/zaps/" => "/usr/share/artica-postfix/bin/install/squid/adzap/zaps/" )');
l.Add('');
l.Add('cgi.assign= (');
l.Add('	".pl"  => "/usr/bin/perl",');
l.Add('	".php" => "/usr/bin/php-cgi",');
l.Add('	".py"  => "/usr/bin/python",');
l.Add('	".cgi"  => "/usr/bin/perl",');
l.Add(')');
logs.WriteToFile(l.Text,'/etc/artica-postfix/squidguard-lighttpd.conf');
l.free;
end;
//##############################################################################
function tsquidguard_page.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
var
  SepLen       : Integer;
  F, P         : PChar;
  ALen, Index  : Integer;
begin
  SetLength(Result, 0);
  if (S = '') or (Limit < 0) then
    Exit;
  if Separator = '' then
  begin
    SetLength(Result, 1);
    Result[0] := S;
    Exit;
  end;
  SepLen := Length(Separator);
  ALen := Limit;
  SetLength(Result, ALen);

  Index := 0;
  P := PChar(S);
  while P^ <> #0 do
  begin
    F := P;
    P := StrPos(P, PChar(Separator));
    if (P = nil) or ((Limit > 0) and (Index = Limit - 1)) then
      P := StrEnd(F);
    if Index >= ALen then
    begin
      Inc(ALen, 5); // mehrere auf einmal um schneller arbeiten zu können
      SetLength(Result, ALen);
    end;
    SetString(Result[Index], F, P - F);
    Inc(Index);
    if P^ <> #0 then
      Inc(P, SepLen);
  end;
  if Index < ALen then
    SetLength(Result, Index); // wirkliche Länge festlegen
end;

end.

