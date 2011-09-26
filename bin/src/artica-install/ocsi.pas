unit ocsi;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,apache_artica,lighttpd;

  type
  tocsi=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     apachebinary_path,user,group,userfull:string;
     apache:tapache_artica;
     ocswebservernameEnabled:integer;
     OCSNGEnabled:integer;
     NoEngoughMemory:boolean;
     UseFusionInventoryAgents:integer;
     APACHES_MODULES:string;
     procedure  STOP_MAIN();
     procedure  START_MAIN();
     function   PID_NUM():string;
     procedure  STOP_APACHE_DOWNLOAD();
     procedure  START_APACHE_DOWNLOAD();
     function   PID_NUM_DOWNLOAD():string;
     function   GET_SERVER_NAME_IN_CSR():string;
     procedure  WRITE_APACHE_CONFIG();
     procedure  WRITE_APACHE_CONFIG_DOWNLOAD();
     function   CHECK_CERTIFICATES():boolean;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  VERSION():string;
    procedure START();
    procedure STOP();
    function  STATUS():string;
    procedure WRITE_CONFIG();
    procedure RELOAD();
    procedure WritePhpConfig();

END;

implementation

constructor tocsi.Create(const zSYS:Tsystem);
var
   mem:integer;
   RegExpr:TRegExpr;
   lighttpd:Tlighttpd;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       NoEngoughMemory:=false;

       if not TryStrToInt(SYS.GET_INFO('ocswebservernameEnabled'),ocswebservernameEnabled) then ocswebservernameEnabled:=1;
       if not TryStrToInt(SYS.GET_INFO('OCSNGEnabled'),OCSNGEnabled) then OCSNGEnabled:=1;
       if not TryStrToInt(SYS.GET_INFO('UseFusionInventoryAgents'),UseFusionInventoryAgents) then UseFusionInventoryAgents:=1;




       apachebinary_path:=SYS.LOCATE_APACHE_BIN_PATH();
       apache:=tapache_artica.Create(SYS);
       mem:=SYS.MEM_TOTAL_INSTALLEE();
       if mem<526300 then begin
          NoEngoughMemory:=true;
          OCSNGEnabled:=0;
       end;

       lighttpd:=Tlighttpd.Create(SYS);
       userfull:=lighttpd.LIGHTTPD_GET_USER();
       RegExpr:=TRegExpr.Create;
       RegExpr.Expression:='(.+?):(.+)';
       RegExpr.Exec(userfull);
       user:=RegExpr.Match[1];
       group:=RegExpr.Match[2];
       lighttpd.free;
       RegExpr.free;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tocsi.free();
begin
    FreeAndNil(logs);
end;
//##############################################################################
procedure tocsi.STOP();
begin
   STOP_MAIN();
   STOP_APACHE_DOWNLOAD()
end;
//##############################################################################
procedure tocsi.START();
begin
   START_MAIN();
   if UseFusionInventoryAgents=0 then START_APACHE_DOWNLOAD();
end;
//##############################################################################
procedure tocsi.START_APACHE_DOWNLOAD();
var
   pid:string;
   count:integer;
begin

   if not FileExists(apachebinary_path) then begin
     logs.DebugLogs('Starting......: Apache (ocs web Engine) is not installed');
     exit;
   end;

   if not FileExists('/usr/share/ocsinventory-reports/ocsreports/index.php') then begin
          logs.DebugLogs('Starting......: OCS web Engine OCS is not installed');
          logs.DebugLogs('Starting......: /usr/share/ocsinventory-reports/ocsreports/index.php no such file');
          exit;
   end;

if NoEngoughMemory then begin
    logs.DebugLogs('Starting......: Apache Need more than 512Mb memory installed to run');
    SYS.set_INFO('OCSNGEnabled','0');
    STOP();
    exit;
end;

if OCSNGEnabled=0 then begin
   logs.DebugLogs('Starting......: OCS web Engine daemon is disabled');
   STOP();
   exit;
end;
if SYS.isoverloadedTooMuch() then begin
   logs.DebugLogs('Starting......: System is overloaded');
   exit;
end;

if not CHECK_CERTIFICATES() then begin
   logs.DebugLogs('Starting......: Certificate problem');
   exit;
end;


  pid:=PID_NUM_DOWNLOAD();

if SYS.PROCESS_EXIST(pid) then begin
     logs.DebugLogs('Starting......: OCS web Engine daemon [download] already running using PID ' +pid+ '...');
     exit;
end;
    logs.DebugLogs('tocsi.START():: -> WRITE_APACHE_CONFIG() ');
    WRITE_APACHE_CONFIG_DOWNLOAD();
    logs.DebugLogs('tocsi.START():: exec() -> '+apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb-download.conf');
    fpsystem(apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb-download.conf');

 count:=0;
 while not SYS.PROCESS_EXIST(PID_NUM_DOWNLOAD()) do begin
              sleep(150);
              inc(count);
              if count>20 then begin
                 logs.DebugLogs('Starting......: OCS web Engine daemon [download]. (timeout!!!)');
                 logs.DebugLogs('Starting......: '+apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb-download.conf');
                 break;
              end;
        end;



if  not SYS.PROCESS_EXIST(PID_NUM_DOWNLOAD()) then begin
    logs.DebugLogs('Starting......: OCS web Engine daemon [download] failed');
    exit;
end;

logs.DebugLogs('Starting......: OCS web Engine daemon [download] success with new PID ' + PID_NUM_DOWNLOAD());



end;
//##############################################################################
procedure tocsi.START_MAIN();
var
   pid:string;
   count:integer;
begin

   if not FileExists(apachebinary_path) then begin
     logs.DebugLogs('Starting......: Apache (ocs web Engine) is not installed (apache not such binary)');
     exit;
   end;

   if not FileExists('/usr/share/ocsinventory-reports/ocsreports/index.php') then begin
          logs.DebugLogs('Starting......: OCS web Engine OCS is not installed (index.php not such file)');
          exit;
   end;

if NoEngoughMemory then begin
    logs.DebugLogs('Starting......: Apache Need more than 512Mb memory installed to run');
    SYS.set_INFO('OCSNGEnabled','0');
    STOP();
    exit;
end;

if OCSNGEnabled=0 then begin
   logs.DebugLogs('Starting......: OCS web Engine is disabled');
   STOP();
   exit;
end;
if SYS.isoverloadedTooMuch() then begin
   logs.DebugLogs('Starting......: System is overloaded');
   exit;
end;



  pid:=PID_NUM();

if SYS.PROCESS_EXIST(pid) then begin
     logs.DebugLogs('Starting......: OCS web Engine already running using PID ' +pid+ '...');
     exit;
end;
    logs.DebugLogs('tocsi.START():: -> WRITE_APACHE_CONFIG() ');
    WRITE_APACHE_CONFIG();
    logs.DebugLogs('tocsi.START():: -> WritePhpConfig() ');
    WritePhpConfig();
    logs.DebugLogs('tocsi.START():: Write configs done..');
    logs.DebugLogs('tocsi.START():: exec() -> '+apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb.conf');


    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.ocsweb.php --mysql');
    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.ocsweb.php --builddbinc');
    fpsystem(apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb.conf');

 count:=0;
 while not SYS.PROCESS_EXIST(PID_NUM()) do begin
              sleep(150);
              inc(count);
              if count>20 then begin
                 logs.DebugLogs('Starting......: OCS web Engine daemon. (timeout!!!)');
                 logs.DebugLogs('Starting......: '+apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb.conf');
                 break;
              end;
        end;



if  not SYS.PROCESS_EXIST(PID_NUM()) then begin
    logs.DebugLogs('Starting......: OCS web Engine daemon failed');
    exit;
end;

logs.DebugLogs('Starting......: OCS web Engine daemon success with new PID ' + PID_NUM());



end;
//##############################################################################
function tocsi.PID_NUM():string;
var pid:string;
begin
pid:=SYS.GET_PID_FROM_PATH('/var/run/apache-ocs/httpd.pid');
if length(pid)>0 then begin
   if SYS.PROCESS_EXIST(pid) then exit(pid);
end;
result:=SYS.PIDOF_PATTERN(apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb.conf');
end;
//##############################################################################
function tocsi.PID_NUM_DOWNLOAD():string;
var pid:string;
begin
pid:=SYS.GET_PID_FROM_PATH('/var/run/apache-ocs/httpd-download.pid');
if length(pid)>0 then begin
   if SYS.PROCESS_EXIST(pid) then exit(pid);
end;
result:=SYS.PIDOF_PATTERN(apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb-download.conf');
end;
//##############################################################################
procedure tocsi.RELOAD();
var
pid:string;
APACHECTL:string;
begin

  pid:=PID_NUM();

if not SYS.PROCESS_EXIST(pid) then begin
     START();
     exit;
end;

WRITE_APACHE_CONFIG();
if UseFusionInventoryAgents=0 then WRITE_APACHE_CONFIG_DOWNLOAD();
WritePhpConfig();

 pid:=PID_NUM();
if not SYS.PROCESS_EXIST(pid) then begin
     START();
     exit;
end;

APACHECTL:=SYS.LOCATE_APACHECTL();
if FileExists(APACHECTL) then begin
   logs.OutputCmd(SYS.LOCATE_APACHECTL() +' -f /etc/artica-postfix/apache-ocsweb.conf -k restart');
   if UseFusionInventoryAgents=0 then logs.OutputCmd(SYS.LOCATE_APACHECTL() +' -f /etc/artica-postfix/apache-ocsweb-download.conf -k restart');
   exit;
end;

STOP();
START();
end;
//##############################################################################
procedure tocsi.STOP_MAIN();
var
   count,pidInt,i:integer;
   pid:string;
   pids:Tstringlist;
begin

    if not FileExists(apachebinary_path) then begin
    writeln('Stopping OCS web Engine....: Not installed');
    exit;
    end;
    pid:=PID_NUM();
    if SYS.PROCESS_EXIST(pid) then begin
        writeln('Stopping OCS web Engine....: ' +pid + ' PID..');
       fpsystem('/bin/kill '+ pid);
    end;

    if FileExists(SYS.LOCATE_APACHECTL()) then begin
       logs.OutputCmd(SYS.LOCATE_APACHECTL() +' -f /etc/artica-postfix/apache-ocsweb.conf -k stop');
    end else begin
       writeln('Stopping Apache Daemon.......: failed to stat apachectl');
    end;
 pid:=PID_NUM();
 count:=0;
 while SYS.PROCESS_EXIST(pid) do begin
              sleep(150);
              inc(count);
              if count>20 then begin
                 writeln('Stopping OCS web Engine....: ' + pid + ' PID.. (timeout)');
                 fpsystem('/bin/kill -9 ' + pid);
                 break;
              end;

              pid:=PID_NUM();
        end;

 count:=0;
 pids:=Tstringlist.Create;
 try
 pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb.conf'));
 writeln('Stopping OCS web Engine....: ',pids.Count,' childs');
  for i:=0 to pids.Count-1 do begin
              if TryStrToInt(pids.Strings[i],pidInt) then begin
                if pidInt>1 then begin
                   writeln('Stopping OCS web Engine....: PID ',pidInt,' pid child');
                   fpsystem('/bin/kill -9 '+intTostr(pidInt));
                end;
              end;
        end;
 finally
 end;


pid:=PID_NUM();
if  not SYS.PROCESS_EXIST(pid) then begin
    writeln('Stopping OCS web Engine....: success');
    exit;
end;
    writeln('Stopping OCS web Engine....: failed');

end;
//#############################################################################
procedure tocsi.STOP_APACHE_DOWNLOAD();
var
   count,pidInt,i:integer;
   pid:string;
   pids:Tstringlist;
begin

    if not FileExists(apachebinary_path) then begin
    writeln('Stopping OCS web Engine....: [download]: Not installed');
    exit;
    end;
    pid:=PID_NUM_DOWNLOAD();
    if SYS.PROCESS_EXIST(pid) then begin
        writeln('Stopping OCS web Engine....: [download]: ' +pid + ' PID..');
       fpsystem('/bin/kill '+ pid);
    end;

    if FileExists(SYS.LOCATE_APACHECTL()) then begin
       logs.OutputCmd(SYS.LOCATE_APACHECTL() +' -f /etc/artica-postfix/apache-ocsweb-download.conf -k stop');
    end else begin
       writeln('Stopping Apache Daemon.......: [download]: failed to stat apachectl');
    end;
 pid:=PID_NUM_DOWNLOAD();
 count:=0;
 while SYS.PROCESS_EXIST(pid) do begin
              sleep(150);
              inc(count);
              if count>20 then begin
                 writeln('Stopping OCS web Engine....: [download]: ' + pid + ' PID.. (timeout)');
                 fpsystem('/bin/kill -9 ' + pid);
                 break;
              end;

              pid:=PID_NUM_DOWNLOAD();
        end;

 count:=0;
 pids:=Tstringlist.Create;
 try
 pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(apachebinary_path+' -f /etc/artica-postfix/apache-ocsweb-download.conf'));
 writeln('Stopping OCS web Engine....: [download]: ',pids.Count,' childs');
  for i:=0 to pids.Count-1 do begin
              if TryStrToInt(pids.Strings[i],pidInt) then begin
                if pidInt>1 then begin
                   writeln('Stopping OCS web Engine....: [download]: PID ',pidInt,' pid child');
                   fpsystem('/bin/kill -9 '+intTostr(pidInt));
                end;
              end;
        end;
 finally
 end;


 pid:=PID_NUM_DOWNLOAD();
if  not SYS.PROCESS_EXIST(pid) then begin
    writeln('Stopping OCS web Engine....: [download]: success');
    exit;
end;
    writeln('Stopping OCS web Engine....: [download]: failed');

end;
//#############################################################################
procedure tocsi.WRITE_APACHE_CONFIG();
var
   l:Tstringlist;
   RegExpr:TRegExpr;
   OCSWebPort:integer;
   OCSWebPortSSL:integer;
   ocswebservername:string;
   LOCATE_MIME_TYPES:string;
   OCSWebSSL:integer;
   COMPTE_BASE,PSWD_BASE,SERVEUR_SQL,SERVEUR_SQL_PORT:string;
   SSLStrictSNIVHostCheck:integer;
begin
WRITE_CONFIG();
if not TryStrToInt(SYS.GET_INFO('SSLStrictSNIVHostCheck'),SSLStrictSNIVHostCheck) then SSLStrictSNIVHostCheck:=0;

if not TryStrToInt(SYS.GET_INFO('OCSWebPort'),OCSWebPort) then begin
    OCSWebPort:=9088;
    SYS.set_INFO('OCSWebPort','9088');
end;

if not TryStrToInt(SYS.GET_INFO('OCSWebPortSSL'),OCSWebPortSSL) then begin
   OCSWebPortSSL:=OCSWebPort+50;
   SYS.set_INFO('OCSWebPortSSL',IntTOStr(OCSWebPortSSL));
end;
ocswebservername:=GET_SERVER_NAME_IN_CSR();
if length(ocswebservername)=0 then ocswebservername:=SYS.GET_INFO('ocswebservername');
if length(ocswebservername)=0 then ocswebservername:=sys.HOSTNAME_g();

l:=Tstringlist.Create;
l.Add('ServerRoot "/usr/share/ocsinventory-reports"');
if UseFusionInventoryAgents=0 then l.Add('Listen '+IntTostr(OCSWebPort)) else l.Add('Listen '+IntTostr(OCSWebPortSSL));
l.Add('');
if length(APACHES_MODULES)=0 then APACHES_MODULES:=apache.SET_MODULES();
l.add(APACHES_MODULES);
if UseFusionInventoryAgents=0 then logs.DebugLogs('Starting......: OCS web Engine will run on port: [' + IntToStr(OCSWebPort)+'] SSL Port: [' + IntToStr(OCSWebPortSSL)+'] user:'+user+' Server:'+ocswebservername);
if UseFusionInventoryAgents=1 then logs.DebugLogs('Starting......: OCS web Engine will run on port: SSL Port: [' + IntToStr(OCSWebPortSSL)+'] user:'+user+' Server:'+ocswebservername);
forceDirectories('/var/log/ocsinventory-server');
ForceDirectories('/var/run/apache-ocs');


    if not FileExists('/etc/artica-postfix/mime.types') then begin
       LOCATE_MIME_TYPES:=SYS.LOCATE_MIME_TYPES;
       if not FileExists(LOCATE_MIME_TYPES) then begin
          logs.Debuglogs('Starting......: OCS web Engine fatal error while try to find mime.types');
          exit;
       end;
       logs.OutputCmd('/bin/cp '+LOCATE_MIME_TYPES+' /etc/artica-postfix/mime.types');
    end;

logs.OutputCmd('/bin/chown -R '+userfull+' /usr/share/ocsinventory-reports/ocsreports');
logs.OutputCmd('/bin/chown -R '+userfull+' /var/lib/ocsinventory-reports/download');
logs.OutputCmd('/bin/chown -R '+userfull+' /var/log/ocsinventory-server');
logs.OutputCmd('/bin/chown -R '+userfull+' /var/run/apache-ocs');

if UseFusionInventoryAgents=1 then begin
   l.Add('SSLCACertificateFile /etc/ocs/cert/cacert.pem');
   l.Add('SSLCertificateFile /etc/ocs/cert/server.crt');
   l.Add('SSLCertificateKeyFile /etc/ocs/cert/server.key');
   l.Add('SSLProtocol all');
   l.Add('SSLOptions +StdEnvVars');
   l.Add('SSLEngine on');
   l.Add('SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL ');
   if SSLStrictSNIVHostCheck=1 then l.Add('SSLStrictSNIVHostCheck off');
   l.Add('ServerSignature Off');
   l.Add('SetEnvIf User-Agent ".*MSIE.*" nokeepalive ssl-unclean-shutdown');
end;

l.Add('User '+user);
l.Add('Group '+group);
l.Add('PidFile /var/run/apache-ocs/httpd.pid');
l.Add('<IfModule !mpm_netware_module>');
l.Add('          <IfModule !mpm_winnt_module>');
l.Add('             User '+user);
l.Add('             Group '+group);
l.Add('          </IfModule>');
l.Add('</IfModule>');
l.Add('');
l.Add('ServerAdmin you@example.com');
l.Add('ServerName ' + ocswebservername);
l.Add('DocumentRoot "/usr/share/ocsinventory-reports"');
l.Add('');
l.Add('<Directory />');
l.Add('    Options FollowSymLinks');
l.Add('    AllowOverride None');
l.Add('    Order deny,allow');
l.Add('    Deny from all');
l.Add('</Directory>');
l.Add('');
l.Add('');
l.Add('<Directory "/usr/share/artica-groupware">');
l.Add('    DirectoryIndex index.php');
l.Add('    AddDefaultCharset ISO-8859-15');
l.Add('    Options Indexes FollowSymLinks');
l.Add('    AllowOverride None');
l.Add('    Order allow,deny');
l.Add('    Allow from all');
l.Add('</Directory>');
l.Add('');
l.Add('<IfModule dir_module>');
l.Add('    DirectoryIndex index.php');
l.Add('</IfModule>');
l.Add('');
l.Add('');
l.add('<IfModule mod_perl.c>');

   COMPTE_BASE:=SYS.MYSQL_INFOS('root');
   PSWD_BASE:=SYS.MYSQL_INFOS('database_password');
   SERVEUR_SQL:=SYS.MYSQL_INFOS('mysql_server');
   SERVEUR_SQL_PORT:=SYS.MYSQL_INFOS('port');

l.add('  PerlSetEnv OCS_MODPERL_VERSION 2');
l.add('  PerlSetEnv OCS_DB_HOST '+SERVEUR_SQL);
l.add('  PerlSetEnv OCS_DB_PORT '+SERVEUR_SQL_PORT);
l.add('  PerlSetEnv OCS_DB_NAME ocsweb');
l.add('  PerlSetEnv OCS_DB_LOCAL ocsweb');
l.add('  PerlSetEnv OCS_DB_USER '+COMPTE_BASE);
l.add('  PerlSetVar OCS_DB_PWD '+PSWD_BASE);
l.add('  PerlSetEnv OCS_OPT_LOGPATH "/var/log/ocsinventory-server"');
l.add('  PerlSetEnv OCS_OPT_DBI_PRINT_ERROR 1 ');
l.add('  PerlSetEnv OCS_OPT_UNICODE_SUPPORT 1');
l.add('  PerlAddVar OCS_OPT_TRUSTED_IP 127.0.0.1');
l.add('  PerlSetEnv OCS_OPT_WEB_SERVICE_ENABLED 0');
l.add('  PerlSetEnv OCS_OPT_WEB_SERVICE_RESULTS_LIMIT 100');
l.add('  PerlSetEnv OCS_OPT_OPTIONS_NOT_OVERLOADED 0');
l.add('  PerlSetEnv OCS_OPT_COMPRESS_TRY_OTHERS 1');
l.add('  PerlSetEnv OCS_OPT_LOGLEVEL 2');
l.add('  PerlSetEnv OCS_OPT_PROLOG_FREQ 12');
l.add('  PerlSetEnv OCS_OPT_AUTO_DUPLICATE_LVL 15');
l.add('  PerlSetEnv OCS_OPT_SECURITY_LEVEL 0');
l.add('  PerlSetEnv OCS_OPT_LOCK_REUSE_TIME 600');
l.add('  PerlSetEnv OCS_OPT_TRACE_DELETED 0');
l.add('  PerlSetEnv OCS_OPT_FREQUENCY 0  ');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_DIFF 1');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_TRANSACTION 1');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_WRITE_DIFF 1');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_CACHE_ENABLED 1');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_CACHE_REVALIDATE 7');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_CACHE_KEEP 1');
l.add('  PerlSetEnv OCS_OPT_DOWNLOAD 1');
l.add('  PerlSetEnv OCS_OPT_DOWNLOAD_PERIOD_LENGTH 10');
l.add('  PerlSetEnv OCS_OPT_DOWNLOAD_CYCLE_LATENCY 60');
l.add('  PerlSetEnv OCS_OPT_DOWNLOAD_FRAG_LATENCY 60');
l.add('  PerlSetEnv OCS_OPT_DOWNLOAD_GROUPS_TRACE_EVENTS 1');
l.add('  PerlSetEnv OCS_OPT_DOWNLOAD_PERIOD_LATENCY 60');
l.add('  PerlSetEnv OCS_OPT_DOWNLOAD_TIMEOUT 7');
l.add('  PerlSetEnv OCS_OPT_DEPLOY 1');
l.add('  PerlSetEnv OCS_OPT_ENABLE_GROUPS 1');
l.add('  PerlSetEnv OCS_OPT_GROUPS_CACHE_OFFSET 600');
l.add('  PerlSetEnv OCS_OPT_GROUPS_CACHE_REVALIDATE 600');
l.add('  PerlSetEnv OCS_OPT_IPDISCOVER 2');
l.add('  PerlSetEnv OCS_OPT_IPDISCOVER_BETTER_THRESHOLD 1');
l.add('  PerlSetEnv OCS_OPT_IPDISCOVER_LATENCY 100');
l.add('  PerlSetEnv OCS_OPT_IPDISCOVER_MAX_ALIVE 14');
l.add('  PerlSetEnv OCS_OPT_IPDISCOVER_NO_POSTPONE 0');
l.add('  PerlSetEnv OCS_OPT_IPDISCOVER_USE_GROUPS 1');
l.add('  PerlSetEnv OCS_OPT_GENERATE_OCS_FILES 0');
l.add('  PerlSetEnv OCS_OPT_OCS_FILES_FORMAT OCS');
l.add('  PerlSetEnv OCS_OPT_OCS_FILES_OVERWRITE 0');
l.add('  PerlSetEnv OCS_OPT_OCS_FILES_PATH /tmp');
l.add('  PerlSetEnv OCS_OPT_PROLOG_FILTER_ON 0');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_FILTER_ENABLED 0');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_FILTER_FLOOD_IP 0');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_FILTER_FLOOD_IP_CACHE_TIME 300');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_FILTER_ON 0');
l.add('  PerlSetEnv OCS_OPT_REGISTRY 1');
l.add('  PerlSetEnv OCS_OPT_SESSION_VALIDITY_TIME 600');
l.add('  PerlSetEnv OCS_OPT_SESSION_CLEAN_TIME 86400');
l.add('  PerlSetEnv OCS_OPT_INVENTORY_SESSION_ONLY 0');
l.add('  PerlSetEnv OCS_OPT_ACCEPT_TAG_UPDATE_FROM_CLIENT 0');
l.add('  PerlSetEnv OCS_OPT_PROXY_REVALIDATE_DELAY 3600');
l.add('  PerlSetEnv OCS_OPT_UPDATE 1');
l.add('  PerlModule Apache::DBI');
l.add('  PerlModule Compress::Zlib');
l.add('  PerlModule XML::Simple');
l.add('  PerlModule Apache::Ocsinventory');
l.add('  PerlModule Apache::Ocsinventory::Server::Constants');
l.add('  PerlModule Apache::Ocsinventory::Server::System');
l.add('  PerlModule Apache::Ocsinventory::Server::Communication');
l.add('  PerlModule Apache::Ocsinventory::Server::Inventory');
l.add('  PerlModule Apache::Ocsinventory::Server::Duplicate');
l.add('  PerlModule Apache::Ocsinventory::Server::Capacities::Registry');
l.add('  PerlModule Apache::Ocsinventory::Server::Capacities::Update');
l.add('  PerlModule Apache::Ocsinventory::Server::Capacities::Ipdiscover');
l.add('  PerlModule Apache::Ocsinventory::Server::Capacities::Download');
l.add('  PerlModule Apache::Ocsinventory::Server::Capacities::Notify');
l.add('  PerlModule Apache::Ocsinventory::SOAP');
l.add('  ');
l.add('<Location /ocsinventory>');
l.add('        order deny,allow');
l.add('        allow from all');
l.add('        Satisfy Any');
l.add('        SetHandler perl-script');
l.add('        PerlHandler Apache::Ocsinventory');
l.add('</Location>');


l.add('<location /ocsinterface>');
l.add('    SetHandler perl-script');
l.add('    PerlHandler "Apache::Ocsinventory::SOAP"');
l.add('    Order deny,allow');
l.add('    Allow from all');
l.add('  </location>');
l.add('</IfModule>');
l.Add('');
l.Add('');
l.Add('Alias /ocsreports /usr/share/ocsinventory-reports/ocsreports');
l.Add('<Directory /usr/share/ocsinventory-reports/ocsreports>');
l.Add('    Order deny,allow');
l.Add('    Allow from all');
l.Add('    Options Indexes FollowSymLinks');
l.Add('    DirectoryIndex index.php');
l.Add('    AllowOverride Options');
l.Add('    AddType application/x-httpd-php .php');
l.Add('    php_flag file_uploads           on');
l.Add('    php_value post_max_size         9m');
l.Add('    php_value upload_max_filesize   8m');
l.Add('');
l.Add('</Directory>');
l.Add('');

l.Add('Alias /download /var/lib/ocsinventory-reports/download');
l.Add('<Directory /var/lib/ocsinventory-reports/download>');
l.Add('    Order deny,allow');
l.Add('    Allow from all');
l.Add('    Options Indexes FollowSymLinks');
l.Add('    DirectoryIndex index.php');
l.Add('    AllowOverride Options');
l.Add('    AddType application/x-httpd-php .php');
l.Add('    php_flag file_uploads           on');
l.Add('    php_value post_max_size         400m');
l.Add('    php_value upload_max_filesize   380m');
l.Add('    LimitRequestBody 10000000');
l.Add('</Directory>');

if FileExists('/usr/share/wpkg/wpkg.js') then begin
l.Add('Alias /wpkg /usr/share/wpkg');
logs.DebugLogs('Starting......: OCS web Engine WPKG is installed');
logs.OutputCmd('/bin/chown -R '+userfull+' /usr/share/wpkg');
l.add('	DavLockDB "/usr/share/wpkg/DavLock"');
l.add('	BrowserMatch "Microsoft Data Access Internet Publishing Provider" redirect-carefully');
l.add('	BrowserMatch "MS FrontPage" redirect-carefully');
l.add('	BrowserMatch "^WebDrive" redirect-carefully');
l.add('	BrowserMatch "^WebDAVFS/1.[0123]" redirect-carefully');
l.add('	BrowserMatch "^gnome-vfs/1.0" redirect-carefully');
l.add('	BrowserMatch "^XML Spy" redirect-carefully');
l.add('	BrowserMatch "^Dreamweaver-WebDAV-SCM1" redirect-carefully');
l.add('	<Directory "/usr/share/wpkg">');
l.add('	Options Indexes FollowSymLinks Includes MultiViews');
l.add('		AllowOverride None');
l.add('		Order allow,deny');
l.add('		Allow from all');
l.add('		DAV On');
l.add('	</Directory>');
end;

l.Add('<Directory "/var/lib/ocsinventory-reports">');
l.Add('    DirectoryIndex index.php');
l.Add('    AddDefaultCharset ISO-8859-15');
l.Add('    Options Indexes FollowSymLinks');
l.Add('    AllowOverride None');
l.Add('    Order allow,deny');
l.Add('    Allow from all');
l.Add('</Directory>');


l.Add('');
l.Add('');
l.Add('ErrorLog "/var/log/ocsinventory-server/apache-error.log"');
l.Add('LogLevel debug');
l.Add('');
l.Add('<IfModule log_config_module>');
l.Add('    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %V" combinedv');
l.Add('    LogFormat "%h %l %u %t \"%r\" %>s %b" common');
l.Add('');
l.Add('    <IfModule logio_module>');
l.Add('      LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio');
l.Add('    </IfModule>');
l.Add('');
l.Add('LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined');
l.Add('LogFormat "%h %l %u %t \"%r\" %>s %b" common');
l.Add('LogFormat "%{Referer}i -> %U" referer');
l.Add('LogFormat "%{User-agent}i" agent');
l.Add('CustomLog /var/log/ocsinventory-server/apache-access.log combined');
l.Add('</IfModule>');
l.Add('');
l.Add('');
l.Add('<IfModule mime_module>');
l.Add('   ');
l.Add('    TypesConfig /etc/artica-postfix/mime.types');
l.Add('    #AddType application/x-gzip .tgz');
l.Add('    AddType application/x-compress .Z');
l.Add('    AddType application/x-gzip .gz .tgz');
l.add('    AddType application/x-httpd-php .php .phtml');
l.Add('    #AddHandler cgi-script .cgi');
l.Add('    #AddHandler type-map var');
l.Add('    #AddType text/html .shtml');
l.Add('    #AddOutputFilter INCLUDES .shtml');
l.Add('</IfModule>');
l.Add('   ');


forceDirectories('/usr/local/apache-groupware/php5/lib/php');
logs.Debuglogs('Starting......: OCS web Engine daemon writing apache-groupware.conf');
logs.WriteToFile(l.Text,'/etc/artica-postfix/apache-ocsweb.conf');

l.clear;
forceDirectories('/usr/local/apache-groupware/php5/lib');
WRITE_CONFIG();
RegExpr.free;
l.free;

end;
//#############################################################################
function tocsi.CHECK_CERTIFICATES():boolean;
var
 l:Tstringlist;
 i:integer;
begin
   result:=true;
   l:=Tstringlist.Create;
   l.Add('cacert.pem');
   l.Add('server.crt');
   l.Add('server.key');
   for i:=0 to l.Count-1 do begin
       if not FileExists('/etc/ocs/cert/'+l.Strings[i]) then begin
           logs.DebugLogs('Starting......: OCS web Engine Download /etc/ocs/cert/'+l.Strings[i]+' no such file');
           result:=false;
           l.free;
           exit;
       end;
   end;
  l.free;
end;
//#############################################################################

procedure tocsi.WRITE_APACHE_CONFIG_DOWNLOAD();
var
   l:Tstringlist;
   RegExpr:TRegExpr;
   OCSWebPort:integer;
   OCSWebPortSSL:integer;
   ocswebservername:string;
   LOCATE_MIME_TYPES:string;
   SSLStrictSNIVHostCheck:integer;
   ApacheCertificatesLocations:string;
   OCSWebSSL:integer;


begin
WRITE_CONFIG();

if not TryStrToInt(SYS.GET_INFO('OCSWebPort'),OCSWebPort) then begin
    OCSWebPort:=9088;
    SYS.set_INFO('OCSWebPort','9088');
end;

if not TryStrToInt(SYS.GET_INFO('OCSWebPortSSL'),OCSWebPortSSL) then OCSWebPortSSL:=OCSWebPort+50;

ocswebservername:=GET_SERVER_NAME_IN_CSR();
if length(ocswebservername)=0 then ocswebservername:=SYS.GET_INFO('ocswebservername');
if length(ocswebservername)=0 then ocswebservername:=sys.HOSTNAME_g();
if not TryStrToInt(SYS.GET_INFO('SSLStrictSNIVHostCheck'),SSLStrictSNIVHostCheck) then SSLStrictSNIVHostCheck:=0;
if not TryStrToInt(SYS.GET_INFO('OCSWebSSL'),OCSWebSSL) then OCSWebSSL:=0;
ApacheCertificatesLocations:=SYS.GET_INFO('ApacheCertificatesLocations');


l:=Tstringlist.Create;
l.Add('ServerRoot "/var/lib/ocsinventory-reports"');
l.Add('Listen '+IntTostr(OCSWebPortSSL));
l.Add('');
if length(APACHES_MODULES)=0 then APACHES_MODULES:=apache.SET_MODULES();
l.add(APACHES_MODULES);


logs.DebugLogs('Starting......: OCS web Engine Download will run on SSL port: [' + IntToStr(OCSWebPortSSL)+'] user:'+user);
forceDirectories('/var/log/ocsinventory-server');
ForceDirectories('/var/run/apache-ocs');


    if not FileExists('/etc/artica-postfix/mime.types') then begin
       LOCATE_MIME_TYPES:=SYS.LOCATE_MIME_TYPES;
       if not FileExists(LOCATE_MIME_TYPES) then begin
          logs.Debuglogs('Starting......: OCS web Engine Download fatal error while try to find mime.types');
          exit;
       end;
       logs.OutputCmd('/bin/cp '+LOCATE_MIME_TYPES+' /etc/artica-postfix/mime.types');
    end;


logs.OutputCmd('/bin/chown -R '+userfull+' /var/lib/ocsinventory-reports');


l.Add('User '+user);
l.Add('Group '+group);
l.Add('PidFile /var/run/apache-ocs/httpd-download.pid');
l.Add('<IfModule !mpm_netware_module>');
l.Add('          <IfModule !mpm_winnt_module>');
l.Add('             User '+user);
l.Add('             Group '+group);
l.Add('          </IfModule>');
l.Add('</IfModule>');
l.Add('');

l.Add('ServerAdmin you@example.com');
l.Add('ServerName ' + ocswebservername);
l.Add('DocumentRoot "/var/lib/ocsinventory-reports"');



l.Add('SSLCACertificateFile /etc/ocs/cert/cacert.pem');
l.Add('SSLCertificateFile /etc/ocs/cert/server.crt');
l.Add('SSLCertificateKeyFile /etc/ocs/cert/server.key');
l.Add('SSLProtocol all');
l.Add('SSLOptions +StdEnvVars');
l.Add('SSLEngine on');
l.Add('SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL ');
if SSLStrictSNIVHostCheck=1 then l.Add('SSLStrictSNIVHostCheck off');
l.Add('');
l.Add('<Directory />');
l.Add('    Options FollowSymLinks');
l.Add('    AllowOverride None');
l.Add('    Order deny,allow');
l.Add('    Deny from all');
l.Add('</Directory>');
l.Add('');
l.Add('');
l.Add('<Directory "/var/lib/ocsinventory-reports">');
l.Add('    DirectoryIndex index.php');
l.Add('    AddDefaultCharset ISO-8859-15');
l.Add('    Options Indexes FollowSymLinks');
l.Add('    AllowOverride None');
l.Add('    Order allow,deny');
l.Add('    Allow from all');
l.Add('</Directory>');
l.Add('');
l.Add('<IfModule dir_module>');
l.Add('    DirectoryIndex index.php');
l.Add('</IfModule>');
l.Add('');
l.Add('');
l.Add('ErrorLog /var/log/ocsinventory-server/apache-download-error.log');
l.Add('LogLevel debug');
l.Add('<IfModule log_config_module>');
l.Add('    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %V" combinedv');
l.Add('    LogFormat "%h %l %u %t \"%r\" %>s %b" common');
l.Add('');
l.Add('    <IfModule logio_module>');
l.Add('      LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio');
l.Add('    </IfModule>');
l.Add('');
l.Add('LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined');
l.Add('LogFormat "%h %l %u %t \"%r\" %>s %b" common');
l.Add('LogFormat "%{Referer}i -> %U" referer');
l.Add('LogFormat "%{User-agent}i" agent');
l.Add('CustomLog /var/log/ocsinventory-server/apache-download-access.log combined');
l.Add('</IfModule>');
l.Add('ServerSignature Off');
l.Add('SetEnvIf User-Agent ".*MSIE.*" nokeepalive ssl-unclean-shutdown');

l.Add('');
l.Add('<IfModule mime_module>');
l.Add('    TypesConfig /etc/artica-postfix/mime.types');
l.Add('    #AddType application/x-gzip .tgz');
l.Add('    AddType application/x-compress .Z');
l.Add('    AddType application/x-gzip .gz .tgz');
l.add('    AddType application/x-httpd-php .php .phtml');
l.Add('    #AddHandler cgi-script .cgi');
l.Add('    #AddHandler type-map var');
l.Add('    #AddType text/html .shtml');
l.Add('    #AddOutputFilter INCLUDES .shtml');
l.Add('</IfModule>');

forceDirectories('/usr/local/apache-groupware/php5/lib/php');
logs.Debuglogs('Starting......: OCS web Engine Download daemon writing  conf');
logs.WriteToFile(l.Text,'/etc/artica-postfix/apache-ocsweb-download.conf');

l.clear;
RegExpr.free;
l.free;

end;
//#############################################################################
function tocsi.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_OCSI');
if length(result)>0 then exit;
filetmp:='/usr/share/ocsinventory-reports/ocsreports/header.php';
if not FileExists(filetmp) then exit;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Ver\.\s+([0-9\.]+)';
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
SYS.SET_CACHE_VERSION('APP_OCSI',result);

end;
//#############################################################################
procedure tocsi.WRITE_CONFIG();
var
   l:tstringlist;
   COMPTE_BASE:string;
   SERVEUR_SQL:string;
   PSWD_BASE:string;
begin
if not FileExists('/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php') then exit;
l:=Tstringlist.Create;
   COMPTE_BASE:=SYS.MYSQL_INFOS('root');
   PSWD_BASE:=SYS.MYSQL_INFOS('database_password');
   SERVEUR_SQL:=SYS.MYSQL_INFOS('mysql_server')+':'+SYS.MYSQL_INFOS('port');


l.add('<?php');
l.add('$_SESSION["SERVEUR_SQL"]="'+SERVEUR_SQL+'";');
l.add('$_SESSION["COMPTE_BASE"]="'+COMPTE_BASE+'";');
l.add('$_SESSION["PSWD_BASE"]="'+PSWD_BASE+'";');
l.add('?>');
logs.WriteToFile(l.Text,'/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php');
logs.DebugLogs('Starting......: OCS web Engine updating dbconfig.inc.php done');
l.free;
end;
//##############################################################################
function tocsi.GET_SERVER_NAME_IN_CSR():string;
var
    RegExpr:TRegExpr;
    l:TStringList;
    i:integer;
    filetemp:string;

begin
     filetemp:=logs.FILE_TEMP();
     fpsystem(SYS.LOCATE_GENERIC_BIN('openssl')+' req -text -noout -in /etc/ocs/cert/server.csr >'+filetemp+' 2>&1');
     if not FileExists(filetemp) then exit;

     l:=Tstringlist.Create;
     l.LoadFromFile(filetemp);
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='CN=(.+?)\/emailAddress';


     for i:=0 to l.Count-1 do begin
           if RegExpr.Exec(l.Strings[i]) then begin
              result:=RegExpr.Match[1];
              RegExpr.free;
              l.free;
              exit;
           end;
     end;

     RegExpr.free;
     l.free;

end;
//##############################################################################


function tocsi.STATUS();
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --ocsweb >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;

//##############################################################################
procedure tocsi.WritePhpConfig();
var
   lighttpd:Tlighttpd;
begin
if not FileExists('/usr/local/apache-groupware/php5/lib/php.ini') then begin
   logs.Debuglogs('OCS web Engine unable to stat /usr/local/apache-groupware/php5/lib/php.ini');

end;
forcedirectories('/usr/local/apache-groupware/php5/sessions');
logs.OutputCmd('/bin/chmod 755 /usr/local/apache-groupware/php5/sessions');
logs.OutputCmd('/bin/chown -R www-data:www-data /usr/local/apache-groupware/php5/sessions');
lighttpd:=Tlighttpd.Create(SYS);
lighttpd.LIGHTTPD_ADD_INCLUDE_PATH();
end;
//##############################################################################


end.

