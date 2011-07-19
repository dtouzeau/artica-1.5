unit obm;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,lighttpd;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tobm=class


private
     LOGS:Tlogs;
     D:boolean;
     SYS:TSystem;
     artica_path:string;
    OBMEnabled:integer;
     procedure   INDEX_PHP();


public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    www_path():string;
    function    OBM_PID():string;
    function    VERSION():string;
    procedure   SERVICE_START();
    procedure   SERVICE_STOP();
    FUNCTION    STATUS():string;
    procedure   LIGHTTPD_CONF();
    procedure   MYSQL_SETTING();


END;

implementation

constructor tobm.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       SYS:=zSYS;
       LOGS:=tlogs.Create();
       D:=LOGS.COMMANDLINE_PARAMETERS('debug');
       
       if not TryStrToInt(SYS.GET_INFO('OBMEnabled'),OBMEnabled) then OBMEnabled:=0;

       if D then logs.Debuglogs('tobm.Create():: debug=true');
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tobm.free();
begin
    logs.Free;
end;
//##############################################################################
function tobm.www_path():string;
begin
result:=SYS.LOCATE_OBM_SHARE();
end;
//##############################################################################

function tobm.OBM_PID():string;
var
   pidpath:string;
   pid:string;
begin
     pidpath:='/var/run/lighttpd/lighttpd-obm.pid';
     pid:=SYS.GET_PID_FROM_PATH(pidpath);
     if length(pid)=0 then PID:=trim(SYS.ExecPipe('/usr/bin/pgrep -f "'+sys.LOCATE_LIGHTTPD_BIN_PATH()+ ' -f /etc/artica-postfix/lighttpd-obm.conf"'));
     result:=pid;
end;
//##############################################################################
procedure tobm.INDEX_PHP();
var
l:TstringList;
begin
  if FileExists(www_path()+'/php/index.php') then exit;
  l:=TstringList.Create;
  l.Add('<?php');
  l.Add('header("location:obm.php");');
  l.Add('?>');
  try
     l.SaveToFile(www_path()+'/php/index.php');
  except
    logs.syslogs('tobm.INDEX_PHP():: FATAL ERROR WHILE WRITING "'+www_path()+'/php/index.php"');
    exit;
  end;
l.Free;
end;
//##############################################################################


procedure tobm.SERVICE_STOP();
var
   conf_path:string;
   lighttpd:Tlighttpd;
   pid:string;
begin
  conf_path:='/etc/artica-postfix/lighttpd-obm.conf';

     if not FileExists(conf_path) then begin
          //logs.Debuglogs('tobm.SERVICE_STOP():: Unable to stat /etc/artica-postfix/lighttpd-obm.conf');
          exit;
     end;

     if Not DirectoryExists(www_path())then begin
        logs.Debuglogs('tobm.SERVICE_STOP():: OBM is not installed');
        exit;
     end;
     lighttpd:=Tlighttpd.Create(SYS);
     if not FileExists(lighttpd.LIGHTTPD_BIN_PATH()) then begin
        logs.Debuglogs('tobm.SERVICE_STOP():: lighttpd is not installed');
        exit;
     end;

pid:=OBM_PID();
     if not SYS.PROCESS_EXIST(pid) then begin
        writeln('Stopping obm service.........: Already stopped');
        exit;
     end;

     writeln('Stopping obm service.........: ' + pid + ' PID');
     fpsystem('/bin/kill ' +pid);
     exit;
end;
//##################################################################################

procedure tobm.SERVICE_START();
var
   conf_path:string;
   lighttpd:Tlighttpd;
   pid:string;
begin
     conf_path:='/etc/artica-postfix/lighttpd-obm.conf';
     if not FileExists(conf_path) then begin
          logs.Debuglogs('tobm.SERVICE_STOP():: Unable to stat /etc/artica-postfix/lighttpd-obm.conf');
          exit;
     end;

     if Not DirectoryExists(www_path())then begin
        logs.Debuglogs('tobm.SERVICE_START():: obm is not installed');
        exit;
     end;
     lighttpd:=Tlighttpd.Create(SYS);
     if not FileExists(lighttpd.LIGHTTPD_BIN_PATH()) then begin
        logs.Debuglogs('tobm.SERVICE_START():: lighttpd is not installed');
        exit;
     end;


     if OBMEnabled=0  then begin
         logs.Debuglogs('Starting obm.................: obm is installed but "disabled"');
         exit;
     end;
     

     
     pid:=OBM_PID();
     if not SYS.PROCESS_EXIST(pid) then begin
         MYSQL_SETTING();
         LIGHTTPD_CONF();
         SYS.SET_PHP_INI('Session','session.bug_compat_42','on');
         SYS.SET_PHP_INI('Session','session.bug_compat_warn','off');
         INDEX_PHP();
         logs.OutputCmd('/bin/chown -R '+lighttpd.LIGHTTPD_GET_USER()+' '+www_path());
         logs.OutputCmd(lighttpd.LIGHTTPD_BIN_PATH() + ' -f ' + '/etc/artica-postfix/lighttpd-obm.conf');
         pid:=OBM_PID();

         if not SYS.PROCESS_EXIST(pid) then begin
            logs.Debuglogs('Starting......: obm Failed to start obm http engine');
            exit;
          end  else begin
           logs.Debuglogs('Starting......: obm Success starting lighttpd for OBM pid number ' + pid);
           end;
     end else begin
         logs.Debuglogs('Starting......: obm lighttpd for OBM already started with PID ' + pid);
         exit;
     end;
end;
//##############################################################################
function tobm.VERSION():string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin
if not FileExists(www_path()+'/obminclude/global.inc') then begin
   logs.Debuglogs('Unable to stat '+www_path()+'obminclude/global.inc obm seems not be installed');
   exit;
end;
     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(www_path()+'/obminclude/global.inc');
     RegExpr.Expression:='\$obm_version.+?([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;


end;
//##############################################################################
FUNCTION tobm.STATUS():string;
var
   ini:TstringList;
   pid:string;
begin

  if not FileExists(www_path()) then exit;
  ini:=TstringList.Create;
  pid:=OBM_PID();

  ini.Add('[OBM_APACHE]');
  if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
  ini.Add('application_installed=1');
  ini.Add('application_enabled=1');
  ini.Add('master_pid='+ pid);
  ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
  ini.Add('master_version=' +VERSION());
  ini.Add('status='+SYS.PROCESS_STATUS(pid));
  ini.Add('service_name=APP_OBM_APACHE');
  ini.Add('service_cmd=obm');
  result:=ini.Text;
  ini.free;
end;
//##############################################################################
procedure tobm.MYSQL_SETTING();
var
inif:TiniFile;
l:TstringList;
i:integer;
begin

if not FileExists(www_path()+'/conf/obm_conf.ini') then begin
   logs.Debuglogs('Starting......: obm MYSQL_SETTING() ERR unable to stat '+www_path()+'/conf/obm_conf.ini');
   exit;
end;
inif:=TiniFile.Create(www_path()+'/conf/obm_conf.ini');
inif.WriteString('global','host', SYS.MYSQL_INFOS('mysql_server'));
inif.WriteString('global','dbtype', 'MYSQL');
inif.WriteString('global','db', 'obm');
inif.WriteString('global','user', SYS.MYSQL_INFOS('database_admin'));
inif.WriteString('global','password', '"'+SYS.MYSQL_INFOS('database_password')+'"');
inif.UpdateFile;
inif.Free;


if logs.TABLE_ROWNUM('UserObm','obm')>0 then begin
    logs.Debuglogs('Starting......: obm database and tables seems installed...');
    exit;
end;

if FileExists(www_path()+'/scripts/2.1/create_obmdb_2.1.mysql.sql') then begin
   if logs.EXECUTE_SQL_FILE(www_path()+'/scripts/2.1/create_obmdb_2.1.mysql.sql','obm') then begin
    logs.Debuglogs('Starting......: obm Success creating database and tables');
   end else begin
    logs.Debuglogs('Starting......: obm Failed !!! creating database and tables');
    exit;
   end;
end else begin
    logs.Debuglogs('Starting......: obm Failed !!! unable to stat '+www_path()+'/scripts/2.1/create_obmdb_2.1.mysql.sql');
    exit;

end;
//##############################################################################



l:=TstringList.Create;
l.Add('2.1/obmdb_prefs_values_2.1.sql');
l.Add('2.1/data-fr/obmdb_nafcode_2.1.sql');
l.Add('2.1/obmdb_test_values_2.1.sql');
l.Add('2.1/obmdb_default_values_2.1.sql');

for i:=0 to l.Count-1 do begin

if FileExists(www_path()+'/scripts/'+l.Strings[i]) then begin
   if logs.EXECUTE_SQL_FILE(www_path()+'/scripts/'+l.Strings[i],'obm') then begin
   end else begin
    logs.Debuglogs('Starting......: obm Failed !!! update database and tables');
   end;
end else begin
    logs.Debuglogs('Starting......: obm Failed !!! unable to stat '+www_path()+'/scripts/'+l.Strings[i]);

end;
end;
l.free;

end;
//#############################################################################
procedure tobm.LIGHTTPD_CONF();
var
   l:TstringList;
   OBMListenPort:integer;
   group,name,user:string;
   org_lighttpd:Tlighttpd;
   RegExpr:TRegExpr;
begin
org_lighttpd:=Tlighttpd.Create(SYS);
user:=org_lighttpd.LIGHTTPD_GET_USER();
if length(user)=0 then user:=SYS.GET_INFO('LighttpdUserAndGroup');
if length(user)=0 then begin
   user:='www-data:www-data';
   SYS.set_INFO('LighttpdUserAndGroup',user);
end;

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='(.+?):(.+)';
RegExpr.Exec(user);
name:=RegExpr.Match[1];
group:=RegExpr.Match[2];

if not TryStrToInt(SYS.GET_INFO('OBMListenPort'),OBMListenPort) then OBMListenPort:=447;

l:=TstringList.Create;
l.Add('');
l.Add('server.modules = (');
l.Add('	"mod_alias",');
l.Add('	"mod_access",');
l.Add('	"mod_accesslog",');
l.Add('	"mod_compress",');
l.Add('	"mod_fastcgi",');
l.Add('	"mod_status",');
l.Add('	"mod_setenv" )');
l.Add('');
l.Add('server.document-root        = "/usr/share/obm/php"');
l.Add('server.username = "'+name+'"');
l.Add('server.groupname = "'+group+'"');
l.Add('server.errorlog             = "/var/log/lighttpd/obm-error.log"');
l.Add('index-file.names            = ( "index.php")');
l.Add('');
l.Add('mimetype.assign             = (');
l.Add('	".pdf"          =>      "application/pdf",');
l.Add('	".sig"          =>      "application/pgp-signature",');
l.Add('	".spl"          =>      "application/futuresplash",');
l.Add('	".class"        =>      "application/octet-stream",');
l.Add('	".ps"           =>      "application/postscript",');
l.Add('	".torrent"      =>      "application/x-bittorrent",');
l.Add('	".dvi"          =>      "application/x-dvi",');
l.Add('	".gz"           =>      "application/x-gzip",');
l.Add('	".pac"          =>      "application/x-ns-proxy-autoconfig",');
l.Add('	".swf"          =>      "application/x-shockwave-flash",');
l.Add('	".tar.gz"       =>      "application/x-tgz",');
l.Add('	".tgz"          =>      "application/x-tgz",');
l.Add('	".tar"          =>      "application/x-tar",');
l.Add('	".zip"          =>      "application/zip",');
l.Add('	".mp3"          =>      "audio/mpeg",');
l.Add('	".m3u"          =>      "audio/x-mpegurl",');
l.Add('	".wma"          =>      "audio/x-ms-wma",');
l.Add('	".wax"          =>      "audio/x-ms-wax",');
l.Add('	".ogg"          =>      "application/ogg",');
l.Add('	".wav"          =>      "audio/x-wav",');
l.Add('	".gif"          =>      "image/gif",');
l.Add('	".jar"          =>      "application/x-java-archive",');
l.Add('	".jpg"          =>      "image/jpeg",');
l.Add('	".jpeg"         =>      "image/jpeg",');
l.Add('	".png"          =>      "image/png",');
l.Add('	".xbm"          =>      "image/x-xbitmap",');
l.Add('	".xpm"          =>      "image/x-xpixmap",');
l.Add('	".xwd"          =>      "image/x-xwindowdump",');
l.Add('	".css"          =>      "text/css",');
l.Add('	".html"         =>      "text/html",');
l.Add('	".htm"          =>      "text/html",');
l.Add('	".js"           =>      "text/javascript",');
l.Add('	".asc"          =>      "text/plain",');
l.Add('	".c"            =>      "text/plain",');
l.Add('	".cpp"          =>      "text/plain",');
l.Add('	".log"          =>      "text/plain",');
l.Add('	".conf"         =>      "text/plain",');
l.Add('	".text"         =>      "text/plain",');
l.Add('	".txt"          =>      "text/plain",');
l.Add('	".dtd"          =>      "text/xml",');
l.Add('	".xml"          =>      "text/xml",');
l.Add('	".mpeg"         =>      "video/mpeg",');
l.Add('	".mpg"          =>      "video/mpeg",');
l.Add('	".mov"          =>      "video/quicktime",');
l.Add('	".qt"           =>      "video/quicktime",');
l.Add('	".avi"          =>      "video/x-msvideo",');
l.Add('	".asf"          =>      "video/x-ms-asf",');
l.Add('	".asx"          =>      "video/x-ms-asf",');
l.Add('	".wmv"          =>      "video/x-ms-wmv",');
l.Add('	".bz2"          =>      "application/x-bzip",');
l.Add('	".tbz"          =>      "application/x-bzip-compressed-tar",');
l.Add('	".tar.bz2"      =>      "application/x-bzip-compressed-tar",');
l.Add('	""              =>      "application/octet-stream",');
l.Add(' )');
l.Add('');
l.Add('');
l.Add('accesslog.filename          = "/var/log/lighttpd/obm-access.log"');
l.Add('url.access-deny             = ( "~", ".inc" )');
l.Add('');
l.Add('setenv.add-environment = ( "OBM_INCLUDE_VAR"=>"obminclude")');
l.Add('#php_value include_path ".:/usr/share/obm"');
l.Add('#php_value session.bug_compat_42 1');
l.Add('#php_value session.bug_compat_warn 0');
l.Add('');
l.Add('static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )');
l.Add('server.port                 = '+IntToStr(OBMListenPort));
l.Add('#server.bind                = "127.0.0.1"');
l.Add('#server.error-handler-404   = "/error-handler.html"');
l.Add('#server.error-handler-404   = "/error-handler.php"');
l.Add('server.pid-file             = "/var/run/lighttpd/lighttpd-obm.pid"');
l.Add('server.max-fds 		    = 2048');
l.Add('');
l.Add('fastcgi.server = ( ".php" =>((');
l.Add('		"bin-path" => "'+SYS.LOCATE_PHP5_CGI()+'",');
l.Add('		"socket" => "/var/run/lighttpd/php.socket",');
l.Add('');
l.Add('		"min-procs" => 1,');
l.Add('		"max-procs" => 2,');
l.Add('		"max-load-per-proc" => 2,');
l.Add('		"idle-timeout" => 10,');
l.Add('		 "bin-environment" => (');
l.Add('			"PHP_FCGI_CHILDREN" => "40",');
l.Add('			"PHP_FCGI_MAX_REQUESTS" => "100"');
l.Add('		),');
l.Add('		"bin-copy-environment" => (');
l.Add('			"PATH", "SHELL", "USER"');
l.Add('		),');
l.Add('		"broken-scriptfilename" => "enable"');
l.Add('	))');
l.Add(')');
l.Add('ssl.engine                 = "enable"');
l.Add('ssl.pemfile                = "/opt/artica/ssl/certs/lighttpd.pem"');
l.Add('status.status-url          = "/server-status"');
l.Add('status.config-url          = "/server-config"');
l.Add('');
l.Add('');
l.Add('alias.url += ( "/images/" => "/usr/share/obm/resources/" )');
l.Add('alias.url += ( "/cgi-bin/" => "/usr/lib/cgi-bin/" )');
l.SaveToFile('/etc/artica-postfix/lighttpd-obm.conf');
l.free;
end;



end.
