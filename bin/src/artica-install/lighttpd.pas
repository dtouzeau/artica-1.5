unit lighttpd;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface
                                                              
uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,awstats,mailmanctl,tcpip,mysql_daemon,zarafa_server,backuppc;

type
  TStringDynArray = array of string;

  type
  Tlighttpd=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     awstats:tawstats;
     pid_root_path:string;
     mem_pid:string;
     lighttpd_modules:Tstringlist;
     mem_binpath:string;
    procedure   LIGHTTPD_DEFAULT_CONF_SAVE();
    function    APACHE_ARTICA_ENABLED():string;
    function    Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
    function    _IS_INCLUDE_EXISTS(include_value:string;needed:string):boolean;
    procedure   CHECK_SUBFOLDER();
    function    SET_PHP_CGI_BINPATH():boolean;
    function    ActiveIP():string;
    function    APACHE_ENABLED():string;
    procedure   IS_CGI_SPAWNED();
    function    isModule(modulename:string):boolean;
    procedure   LOAD_MODULES();
    function    roundcube_main_folder():string;
    function    lighttpd_modules_path():string;

public
    EnableLighttpd:integer;
    InsufficentRessources:boolean;
    DisableEaccelerator:integer;
    procedure   Free;

    constructor Create(const zSYS:Tsystem);
    procedure   LIGHTTPD_START(notroubleshoot:boolean=false);
    function    LIGHTTPD_BIN_PATH():string;
    function    LIGHTTPD_INITD():string;
    function    LIGHTTPD_LOG_PATH():string;
    function    LIGHTTPD_SOCKET_PATH():string;
    function    LIGHTTPD_PID():string;
    function    LIGHTTPD_GET_USER():string;
    function    LIGHTTPD_CONF_PATH:string;
    procedure   LIGHTTPD_CERTIFICATE();
    function    LIGHTTPD_PID_PATH():string;
    procedure   LIGHTTPD_STOP();
    function    LIGHTTPD_VERSION():string;
    procedure   LIGHTTPD_ADD_INCLUDE_PATH();
    procedure   LIGHTTPD_VERIF_CONFIG();
    procedure   CLEAN_PHP5_SESSIONS();
    procedure   TROUBLESHOTLIGHTTPD();
    function    lighttpd_server_key(key:string):string;
    procedure   LIGHTTPD_RELOAD();
    procedure    POMMO_ALIASES();
    function    POMMO_VERSION():string;

    procedure   PHPMYADMIN();


    FUNCTION    PHP5_CHECK_EXTENSIONS():string;
    FUNCTION    STATUS():string;
    function    PHP5_CGI_BIN_PATH():string;
    function    CACHE_STATUS:string;
    function    LIGHTTPD_LISTEN_PORT():string;
    function    LIGHTTPD_CERTIFICATE_PATH():string;
    function    DEFAULT_CONF():string;
    procedure   CHANGE_INIT();
    function    IS_AUTH_LDAP():boolean;
    FUNCTION    IS_IPTABLES_INPUT_RULES():boolean;
    procedure   CreateWebFolders();
    function    MON():string;

END;

implementation

constructor tlighttpd.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       forcedirectories('/opt/artica/tmp');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableLighttpd:=1;
       awstats:=tawstats.Create(SYS);
       InsufficentRessources:=SYS.ISMemoryHiger1G();
       DisableEaccelerator:=0;
       lighttpd_modules:=Tstringlist.Create;
       if APACHE_ARTICA_ENABLED()='1' then EnableLighttpd:=0;

       if not TryStrToInt(SYS.GET_INFO('DisableEaccelerator'),DisableEaccelerator) then DisableEaccelerator:=0;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tlighttpd.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
function Tlighttpd.LIGHTTPD_BIN_PATH():string;
begin
if length(mem_binpath)>2 then exit(mem_binpath);
result:=SYS.LOCATE_LIGHTTPD_BIN_PATH();
mem_binpath:=result;
end;
//##############################################################################
function Tlighttpd.PHP5_CGI_BIN_PATH():string;
begin
   if FileExists('/usr/bin/php-fcgi') then exit('/usr/bin/php-fcgi');
   if FileExists('/usr/bin/php-cgi') then exit('/usr/bin/php-cgi');
   if FileExists('/usr/local/bin/php-cgi') then exit('/usr/local/bin/php-cgi');
end;
//##############################################################################
function Tlighttpd.LIGHTTPD_INITD():string;
begin
    if FileExists('/etc/init.d/lighttpd') then exit('/etc/init.d/lighttpd');
    if FileExists('/usr/local/etc/rc.d/lighttpd') then exit('/usr/local/etc/rc.d/lighttpd');
    if FileExists('/etc/rc.d/lighttpd') then exit('/etc/rc.d/lighttpd');
end;

//##############################################################################
function Tlighttpd.LIGHTTPD_CONF_PATH:string;
begin
  if FileExists('/etc/lighttpd/lighttpd.conf') then exit('/etc/lighttpd/lighttpd.conf');
  if FileExists('/etc/lighttpd/lighttpd.conf') then exit('/etc/lighttpd/lighttpd.conf');
  if FileExists('/opt/artica/conf/lighttpd.conf') then exit('/opt/artica/conf/lighttpd.conf');
  if FileExists('/usr/local/etc/lighttpd.conf') then exit('/usr/local/etc/lighttpd.conf');
end;
//##############################################################################
function Tlighttpd.APACHE_ENABLED():string;
begin
if not FileExists(SYS.LOCATE_APACHE_BIN_PATH()) then exit('0');
if not FileExists(SYS.LOCATE_APACHE_LIBPHP5()) then exit('0');
if not FileExists(SYS.LOCATE_APACHE_MODSSLSO()) then exit('0');
if not FileExists(LIGHTTPD_BIN_PATH()) then exit('1');
result:=SYS.GET_INFO('ApacheArticaEnabled');
end;
//##############################################################################
function Tlighttpd.lighttpd_server_key(key:string):string;
var
   sourcefile:string;
   RegExpr:TRegExpr;
   l:Tstringlist;
   i:integer;
begin

sourcefile:=LIGHTTPD_CONF_PATH();
if not FileExists(sourcefile) then exit;
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='server\.'+key+'.*?=.*?"(.+?)"';
l:=Tstringlist.Create;
l.LoadFromFile(sourcefile);
For i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
end;

l.free;
RegExpr.free;
end;
//##############################################################################
procedure Tlighttpd.CLEAN_PHP5_SESSIONS();
var
   i:integer;
   php_path:string;
begin
 exit;
 php_path:=SYS.LOCATE_PHP5_SESSION_PATH();
 if not DirectoryExists(php_path) then exit;
      logs.Debuglogs('Starting......: lighttpd: Cleaning php sessions');
      SYS.DirFiles(php_path,'sess_*');
      logs.Debuglogs('Starting......: lighttpd: '+ INtTOstr(SYS.DirListFiles.Count)+' files to clean');
      for i:=0 to SYS.DirListFiles.Count-1 do begin
          logs.DeleteFile(php_path+'/'+SYS.DirListFiles.Strings[i]);
      end;



end;
//##############################################################################

function Tlighttpd.ActiveIP():string;
var
   ip:string;
   sip:ttcpip;
begin
    sip:=ttcpip.Create;
    ip:=sip.LOCAL_IP_FROM_NIC('eth0');
    if length(ip)>0 then begin
       result:=ip;
       exit;
    end;

    ip:=sip.LOCAL_IP_FROM_NIC('eth1');
    if length(ip)>0 then begin
       result:=ip;
       exit;
    end;

    ip:=sip.LOCAL_IP_FROM_NIC('eth2');
    if length(ip)>0 then begin
       result:=ip;
       exit;
    end;
end;
//##############################################################################

function Tlighttpd.LIGHTTPD_PID_PATH():string;
var
RegExpr:TRegExpr;
l:TStringList;
i:integer;
begin

if length(pid_root_path)>0 then exit(pid_root_path);

if not FileExists(LIGHTTPD_CONF_PATH()) then begin
   logs.Debuglogs('Tlighttpd.LIGHTTPD_PID_PATH:: unable to stat lighttpd.conf ' + LIGHTTPD_CONF_PATH());
   exit;
end;
l:=TstringList.Create;
l.LoadFromFile(LIGHTTPD_CONF_PATH());
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^server\.pid-file.+?"(.+?)"';
for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
    result:=RegExpr.Match[1];
    break;
   end;
end;
   pid_root_path:=result;
   l.Free;
   RegExpr.free;
end;
//##############################################################################
function Tlighttpd.LIGHTTPD_GET_USER():string;
var
     l:TstringList;
     RegExpr:TRegExpr;
     i:integer;
     user,group:string;
begin

  user:=SYS.GET_INFO('LighttpdUserAndGroup');
  logs.Debuglogs('LIGHTTPD_GET_USER: user="'+user+'" (LighttpdUserAndGroup)');
  if length(user)>0 then begin
     user:=AnsireplaceText(user,'lighttpd:lighttpd:lighttpd','lighttpd:lighttpd');
     user:=AnsireplaceText(user,'www-data:www-data:www-data','www-data:www-data');
     result:=user;
     exit(user);
  end;

  if not FileExists(LIGHTTPD_CONF_PATH()) then exit;
  l:=TstringList.Create;
  RegExpr:=TRegExpr.Create;
  l.LoadFromFile(LIGHTTPD_CONF_PATH());
  for i:=0 to l.Count-1 do begin
    RegExpr.Expression:='^server\.username.+?"(.+?)"';
    if RegExpr.Exec(l.Strings[i]) then user:=RegExpr.Match[1];
    RegExpr.Expression:='^server\.groupname.+?"(.+?)"';
    if RegExpr.Exec(l.Strings[i]) then group:=RegExpr.Match[1];
  end;
  if length(user)>0 then result:=user+':'+group;
  SYS.set_INFO('LighttpdUserAndGroup',result);
  RegExpr.free;
  l.free;
end;
//##############################################################################
procedure Tlighttpd.CreateWebFolders();
var
user:string;
begin
user:=LIGHTTPD_GET_USER();
forceDirectories('/opt/artica/share/www/jpegPhoto');
logs.OutputCmd('/bin/chown -R ' + user + ' /opt/artica/share/www/jpegPhoto');
logs.OutputCmd('/bin/chmod -R 777 /opt/artica/share/www/jpegPhoto');
end;
//##############################################################################
function Tlighttpd.CACHE_STATUS:string;
var
   sini:TiniFile;
   f:TstringList;
   run:string;
   cache:string;
begin

f:=TstringList.Create;
cache:='/etc/artica-postfix/cache.lighttpd.status';
f.Add(STATUS());
f.SaveToFile(cache);
f.free;
sini:=TiniFile.Create(cache);

run:=sini.ReadString('LIGHTTPD','running','0');

if run='1' then begin
   result:='Running...' + sini.ReadString('LIGHTTPD','master_memory','0') + ' kb mem';
end else begin
result:='Stopped...';

end;
sini.free;
end;
//##############################################################################
procedure Tlighttpd.LIGHTTPD_VERIF_CONFIG();
var
   user:string;
   group:string;
   logs_path:string;
   RegExpr:TRegExpr;

begin

    logs.Debuglogs('LIGHTTPD_VERIF_CONFIG():: Creating user www-data if does not exists');
    SYS.AddUserToGroup('www-data','www-data','','');
    CHANGE_INIT();
    logs.DeleteFile('/etc/artica-postfix/cache.global.status');


   logs_path:=LIGHTTPD_LOG_PATH();
   user:=LIGHTTPD_GET_USER();
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='(.+?):(.+)';
   if RegExpr.Exec(user) then begin
       user:=RegExpr.Match[1];
       group:=RegExpr.Match[2];
   end;
   if RegExpr.Exec(group) then group:=RegExpr.Match[1];
   forcedirectories('/opt/artica/ssl/certs');
   forcedirectories('/var/lib/php/session');
   ForceDirectories('/var/lighttpd/upload');

   logs.OutputCmd('/bin/chown -R '+user+':'+group+' /var/lib/php/session');
   logs.OutputCmd('/bin/chown -R '+user+':'+group+' /var/run/lighttpd');
   logs.OutputCmd('/bin/chown -R '+user+':'+group+' /var/lighttpd');
   logs.OutputCmd('/bin/chown -R '+user+':'+group+' /usr/share/artica-postfix');
   logs.OutputCmd('/bin/chown -R root:root /usr/share/artica-postfix/bin');




   logs.OutputCmd('/bin/chmod 755 /var/lib/php/session');
   logs.OutputCmd('/bin/chmod 755 /var/lighttpd/upload');
   LIGHTTPD_DEFAULT_CONF_SAVE();
   LIGHTTPD_ADD_INCLUDE_PATH();
   CHECK_SUBFOLDER();
   if not FileExists(LIGHTTPD_CERTIFICATE_PATH()) then begin
      logs.Debuglogs('LIGHTTPD_VERIF_CONFIG() -> LIGHTTPD_CERTIFICATE()');
      LIGHTTPD_CERTIFICATE();
   end;

          if not SET_PHP_CGI_BINPATH() then begin
             logs.Debuglogs('Starting......: lighttpd:  fatal error while setting lighttpd.conf');
             exit;
          end;

          logs.Debuglogs('Starting......: lighttpd:  Checking pommo aliases');
          POMMO_ALIASES();
          forcedirectories('/var/run/lighttpd');
          if length(logs_path)>0 then forcedirectories(logs_path);
          logs.Debuglogs('Starting......: lighttpd:  Checking securities on '+user+':'+group);
          logs.OutputCmd('/bin/chown -R '+user+':'+group+' /var/run/lighttpd');
          logs.OutputCmd('/bin/chown -R '+user+':'+group+' '+ logs_path);


end;

//##############################################################################
procedure Tlighttpd.LIGHTTPD_START(notroubleshoot:boolean);
var
   pid:string;
   user:string;
   group:string;
   daemon:boolean;
   RegExpr:TRegExpr;
   LighttpdArticaDisabled:integer;
begin
   daemon:=LOGS.COMMANDLINE_PARAMETERS('--daemon');
   if not TryStrToInt(SYS.GET_INFO('LighttpdArticaDisabled'),LighttpdArticaDisabled) then LighttpdArticaDisabled:=0;
logs.Debuglogs('###################### LIGHTTPD #####################');

   if not FileExists(LIGHTTPD_BIN_PATH()) then begin
       logs.Debuglogs('LIGHTTPD_START():: it seems that lighttpd is not installed... Aborting');
       exit;
   end;

   if not FileExists('/etc/lighttpd/lighttpd.conf') then DEFAULT_CONF();

   pid:=LIGHTTPD_PID();
   if pid='0' then pid:=SYS.PROCESS_LIST_PID(LIGHTTPD_BIN_PATH());

   if SYS.PROCESS_EXIST(pid) then begin
      logs.Debuglogs('Starting......: lighttpd daemon is already running using PID ' + LIGHTTPD_PID() + '...');
      if LighttpdArticaDisabled=1 then begin
            logs.Debuglogs('Starting......: lighttpd daemon is already but lighttpd is disabled for Artica...');
            LIGHTTPD_STOP();
            exit;
      end;
      logs.Debuglogs('LIGHTTPD_START():: lighttpd already running with PID number ' + pid);
      exit();
   end;


   logs.Debuglogs('Starting......: lighttpd: lighttpd launching process1 for writing settings');
   fpsystem('/usr/share/artica-postfix/bin/process1 --force &');
   logs.DeleteFile('/var/log/lighttpd/error.log');

   user:=LIGHTTPD_GET_USER();
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='(.+?):(.+)';
   if RegExpr.Exec(user) then begin
       user:=RegExpr.Match[1];
       group:=RegExpr.Match[2];
   end;

   SYS.AddUserToGroup(user,group,'','');

   if RegExpr.Exec(group) then group:=RegExpr.Match[1];
   CLEAN_PHP5_SESSIONS();
   ForceDirectories('/usr/share/artica-postfix/ressources/sessions');
   ForceDirectories('/usr/share/artica-postfix/user-backup/ressources/conf');
   ForceDirectories('/usr/share/artica-postfix/user-backup/ressources/databases');
   ForceDirectories('/usr/share/artica-postfix/ressources/conf/upload');
   fpsystem('/bin/cp -rf /usr/share/artica-postfix/ressources/databases/* /usr/share/artica-postfix/user-backup/ressources/databases/');
   fpsystem('/bin/chmod 755 /usr/share/artica-postfix/ressources/sessions');
   fpsystem('/bin/chmod 777 /usr/share/artica-postfix/user-backup/ressources/conf');


   forceDirectories('/var/lib/php5');
   forceDirectories('/usr/share/artica-postfix/ressources/logs/web/queue/sessions');
   fpsystem('/bin/chmod -R 755 /usr/share/artica-postfix');
   fpsystem('/bin/chown root:root /usr/share/artica-postfix/bin/install/amavis');
   fpsystem('/bin/chmod 644 /usr/share/artica-postfix/bin/install/amavis/check-external-users.conf');
   fpsystem('/bin/chown -R '+user+':'+group+' /var/lib/php5');
   fpsystem('/bin/chown -R '+user+':'+group+' /usr/share/artica-postfix/ressources/sessions');
   fpsystem('/bin/chown -R '+user+':'+group+' /usr/share/artica-postfix/ressources');
   fpsystem('/bin/chown -R '+user+':'+group+' /usr/share/artica-postfix/user-backup/ressources');
   fpsystem('/bin/chown -R '+user+':'+group+' /usr/share/artica-postfix/ressources/logs/web/queue &');

   if DirectoryExists('/usr/share/zarafa-webaccess') then begin
        logs.Debuglogs('Starting......: lighttpd: fixing permissions on Zarafa');
        ForceDirectories('/var/lib/zarafa-webaccess/tmp');
        fpsystem('/bin/chmod -R 755 /usr/share/zarafa-webaccess');
        fpsystem('/bin/chmod -R 755 /var/lib/zarafa-webaccess/tmp');
        fpsystem('/bin/chown -R '+user+':'+group+' /usr/share/zarafa-webaccess');
        fpsystem('/bin/chown -R '+user+':'+group+' /var/lib/zarafa-webaccess/tmp');

   end;

   PHPMYADMIN();
   if DirectoryExists('/usr/share/phpmyadmin') then fpsystem('/bin/chown -R '+user+':'+group+' /usr/share/phpmyadmin');
   if LighttpdArticaDisabled=1 then begin
      logs.Debuglogs('Starting......: lighttpd daemon is already but lighttpd is disabled for Artica...');
      exit;
   end;
   if FileExists(LIGHTTPD_INITD()) then begin
       if not SYS.PROCESS_EXIST(pid) then begin
          LIGHTTPD_VERIF_CONFIG();
          logs.Debuglogs('Starting......: lighttpd: user.........:'+user);
          logs.Debuglogs('Starting......: lighttpd: group........:'+group);
          logs.Debuglogs('Starting......: lighttpd: pid..........:'+pid);
          logs.Debuglogs('Starting......: lighttpd: Port.........:' + LIGHTTPD_LISTEN_PORT());
          logs.Debuglogs('Starting......: lighttpd: logs path....:'+LIGHTTPD_LOG_PATH());
          logs.Debuglogs('Starting......: lighttpd: Socket path..:'+LIGHTTPD_SOCKET_PATH());
          logs.Debuglogs('Starting......: lighttpd: php5-cgi path:'+PHP5_CGI_BIN_PATH());
          logs.Debuglogs('Starting......: lighttpd: php client...:' + SYS.LOCATE_PHP5_BIN());
          logs.Debuglogs('Starting......: lighttpd: certificate..:'+LIGHTTPD_CERTIFICATE_PATH());
          logs.Debuglogs('Starting......: lighttpd: php ext dir..:' + SYS.LOCATE_PHP5_EXTENSION_DIR());
          logs.Debuglogs('Starting......: lighttpd: php ext conf.:' + SYS.LOCATE_PHP5_EXTCONF_DIR());
          logs.Debuglogs('Starting......: lighttpd: php session.:' + SYS.LOCATE_PHP5_SESSION_PATH());


          logs.OutputCmd(LIGHTTPD_BIN_PATH()+ ' -f /etc/lighttpd/lighttpd.conf');
          logs.Debuglogs('Starting......: lighttpd: Deleting SystemV5 Shared memory');
          logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.shm.php --remove');
          logs.Debuglogs('Starting......: lighttpd: Deleting icons cache...');
          fpsystem('/bin/rm /usr/share/artica-postfix/ressources/logs/web/*.cache >/dev/null 2>&1');



       end else begin
          if daemon then writeln('Starting......: lighttpd daemon is already running using PID ' + LIGHTTPD_PID() + '...');
          logs.DebugLogs('Starting......: lighttpd daemon is already running using PID ' + LIGHTTPD_PID() + '...');
       end;



   if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
      logs.Debuglogs('Starting......: lighttpd: Failed');
      logs.Debuglogs('Starting......: lighttpd: using "'+LIGHTTPD_BIN_PATH()+ ' -f /etc/lighttpd/lighttpd.conf"');
      if not notroubleshoot then TROUBLESHOTLIGHTTPD();
      end else begin
      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.apc.compile.php');
      logs.Debuglogs('Starting......: lighttpd: Success (PID ' + LIGHTTPD_PID() + ')');
      IS_CGI_SPAWNED();

      end;
   end;


end;

//##############################################################################
procedure Tlighttpd.PHPMYADMIN();
var l:Tstringlist;
configtoadd:TstringList;
begin
 if not FileExists('/usr/share/phpmyadmin/index.php') then exit;
 configtoadd:=TStringlist.Create;
if FileExists('/etc/artica-postfix/phpmyadmin_config.txt') then configtoadd.LoadFromFile('/etc/artica-postfix/phpmyadmin_config.txt');
forceDirectories('/usr/share/phpmyadmin/config');
l:=Tstringlist.Create;
l.add('<?php');
l.add('/* Servers configuration */');
l.add('$i = 0;');
l.add('');
l.add('/* Server: Artica Mysql [1] */');
l.add('$i++;');
l.add('$cfg["Servers"][$i]["verbose"] = "Artica Mysql";');
l.add('$cfg["Servers"][$i]["host"] = "'+ SYS.MYSQL_INFOS('server')+'";');
l.add('$cfg["Servers"][$i]["port"] = '+ SYS.MYSQL_INFOS('port')+';');
l.add('$cfg["Servers"][$i]["socket"] = "";');
l.add('$cfg["Servers"][$i]["connect_type"] = "tcp";');
l.add('$cfg["Servers"][$i]["extension"] = "mysql";');
l.add('$cfg["Servers"][$i]["auth_type"] = "cookie";');
l.add('$cfg["Servers"][$i]["user"] = "'+ SYS.MYSQL_INFOS('database_admin')+'";');
l.add('$cfg["Servers"][$i]["password"] = "'+SYS.MYSQL_INFOS('database_password')+'";');
l.add('');
l.add(configtoadd.Text);
l.add('/* End of servers configuration */');
l.add('');
l.add('$cfg["blowfish_secret"] = "4bf112360c9db0.66618545";');
l.add('$cfg["DefaultLang"] = "en-utf-8";');
l.add('$cfg["ServerDefault"] = 1;');
l.add('$cfg["UploadDir"] = "";');
l.add('$cfg["SaveDir"] = "";');
l.add('?>');
logs.WriteToFile(l.Text,'/usr/share/phpmyadmin/config.inc.php');
l.free;
configtoadd.free;
logs.Debuglogs('Starting......: lighttpd: Success writing phpmyadmin configuration');
if DirectoryExists('/usr/share/phpmyadmin/setup') then fpsystem('/bin/rm -rf /usr/share/phpmyadmin/setup');
if DirectoryExists('/usr/share/phpmyadmin/config') then fpsystem('/bin/rm -rf /usr/share/phpmyadmin/config');



end;
//##############################################################################



procedure Tlighttpd.IS_CGI_SPAWNED();
var

   tmpstr:string;
   l:Tstringlist;
   RegExpr:TRegExpr;
   i:integer;
begin
    if not FileExists('/var/log/lighttpd/error.log') then begin
       logs.Debuglogs('Starting......: lighttpd: unable to stat /var/log/lighttpd/error.log (line 454)');
       exit;
    end;
    sleep(1000);
    tmpstr:=logs.FILE_TEMP();
    fpsystem('tail -n 2 /var/log/lighttpd/error.log >'+tmpstr +' 2>&1');
    if not fileExists(tmpstr) then begin
       logs.Debuglogs('Starting......: lighttpd: unable to stat '+tmpstr+' (line 461)');
       exit;
    end;

    logs.Debuglogs('Starting......: lighttpd: testing if cgi is spawned');

    l:=Tstringlist.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='mod_fastcgi.+?spawning fcgi failed';
    for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
            logs.Debuglogs('Starting......: lighttpd: spawning fcgi failed !!');
            logs.Debuglogs('Starting......: lighttpd: '+l.Strings[i]);
            if SYS.PROCESS_EXIST(SYS.PIDOF('artica-make')) then begin
               logs.Debuglogs('Starting......: lighttpd: stopping artica-make already running');
               exit;
            end;
            if FIleExists('/usr/share/artica-postfix/ressources/install/APP_PHP.time') then begin
               if SYS.FILE_TIME_BETWEEN_MIN('/usr/share/artica-postfix/ressources/install/APP_PHP.time')<120 then begin
                    logs.Debuglogs('Starting......: lighttpd: need more than 60mn to restart operation');
                    exit;
               end;
            end;

            logs.NOTIFICATION('spawning fcgi failed!','lighttpd could not start.It seems that fcgi is not properly installed, Artica will try to install php5 using compilation mode','system');
            logs.DeleteFile('/usr/share/artica-postfix/ressources/install/APP_PHP.time');
            fpsystem('/usr/share/artica-postfix/bin/artica-make APP_PHP &');
            halt(0);
        end;
    end;
    l.free;
    RegExpr.Free;

end;

//##############################################################################
procedure Tlighttpd.TROUBLESHOTLIGHTTPD();
var
   cmd:string;
   tmpstr,port:string;
   l:Tstringlist;
   RegExpr:TRegExpr;
   i:integer;
begin
logs.Debuglogs('Starting......: lighttpd: Try to understand why is doesn''t start');
tmpstr:=logs.FILE_TEMP();
cmd:=LIGHTTPD_BIN_PATH()+ ' -f /etc/lighttpd/lighttpd.conf >' +tmpstr +' 2>&1';
fpsystem(cmd);
// SSL: Private key does not match the certificate public key
if not FileExists(tmpstr) then begin
        logs.Debuglogs('Starting......: lighttpd: could not stat '+ tmpstr);
        exit;
end;

l:=Tstringlist.Create;
l.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
RegExpr:=TRegExpr.Create;
for i:=0 to l.Count-1 do begin
    RegExpr.Expression:='SSL.+?Private key does not match the certificate public';

    if RegExpr.Exec(l.Strings[i]) then begin
        logs.Debuglogs('Starting......: lighttpd: detecting SSL key error generate new certificat');
        LIGHTTPD_CERTIFICATE();
        LIGHTTPD_START(true);
        break;
    end;

    RegExpr.Expression:='can.+?find username\s+';
    if RegExpr.Exec(l.Strings[i]) then begin
        logs.Debuglogs('Starting......: lighttpd: detecting username error generate new configuration file');
        LIGHTTPD_DEFAULT_CONF_SAVE();
        LIGHTTPD_START(true);
        break;
    end;

    RegExpr.Expression:='can.+?t bind to port:\s+([0-9]+)\s+Address already in use';
    if RegExpr.Exec(l.Strings[i]) then begin
       port:=RegExpr.Match[1];
       tmpstr:=SYS.WHO_LISTEN_PORT(port);
       logs.Debuglogs('Starting......: lighttpd: Another process already using Port: "' + port+'" ('+tmpstr+')');
       RegExpr.Expression:='Pid:([0-9]+);';
       if  RegExpr.Exec(tmpstr) then begin
           logs.Debuglogs('Starting......: lighttpd: kill process Pid:'+tmpstr);
           fpsystem('/bin/kill -9 '+RegExpr.Match[1]);
            LIGHTTPD_START(true);
            break;
       end;

    end;

       RegExpr.Expression:='network.+?SSL.+?error';
       if  RegExpr.Exec(l.Strings[i]) then begin
           logs.Debuglogs('Starting......: lighttpd: FATAL Bug in lighttpd (especially in CentOS 5.4), turn to Apache mode');
           logs.Debuglogs('Starting......: lighttpd: '+l.Strings[i]);
           SYS.set_INFO('ApacheArticaEnabled','1');
           halt(0);
           break;
       end;





    logs.Debuglogs('Starting......: lighttpd: no error found in "'+l.Strings[i]+'"');

end;

 RegExpr.free;
 l.free;


end;
//##############################################################################


function Tlighttpd.MON():string;
var
l:TstringList;
begin
l:=TstringList.Create;
l.ADD('check process '+ExtractFileName(LIGHTTPD_BIN_PATH())+' with pidfile '+LIGHTTPD_PID_PATH());
l.ADD('group lighttpd');
l.ADD('start program = "/etc/init.d/artica-postfix start apache"');
l.ADD('stop program = "/etc/init.d/artica-postfix stop apache"');
l.ADD('if 5 restarts within 5 cycles then timeout');
result:=l.Text;
l.free;
end;
//##############################################################################
procedure Tlighttpd.LIGHTTPD_RELOAD();
 var
    pid      :string;
begin
   pid:=LIGHTTPD_PID();
   if pid='0' then pid:=SYS.PROCESS_LIST_PID(LIGHTTPD_BIN_PATH());
   if not SYS.PROCESS_EXIST(pid) then begin
      LIGHTTPD_START();
      exit;
   end;

  LIGHTTPD_DEFAULT_CONF_SAVE();

  fpsystem('/bin/kill -HUP '+pid);
end;

procedure Tlighttpd.LIGHTTPD_STOP();
 var
    count      :integer;
begin

     count:=0;

     logs.DeleteFile('/etc/artica-postfix/cache.global.status');
     if SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
        writeln('Stopping lighttpd: ' + LIGHTTPD_PID() + ' PID..');
        logs.OutputCmd('/bin/kill ' + LIGHTTPD_PID());
        while SYS.PROCESS_EXIST(LIGHTTPD_PID()) do begin
              sleep(100);
              inc(count);
              if count>100 then begin
                 writeln('Stopping lighttpd: Failed force kill');
                 logs.OutputCmd('/bin/kill -9 '+LIGHTTPD_PID());
                 exit;
              end;
        end;


        sleep(1000);
        if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
           writeln('Stopping lighttpd: success');
        end;

      end else begin
        writeln('Stopping lighttpd: Already stopped');
     end;

end;
//##############################################################################
procedure Tlighttpd.CHANGE_INIT();
var
l:TstringList;
begin
l:=TstringList.Create;
if not fileExists(LIGHTTPD_INITD()) then exit;
l.Add('#!/bin/sh');
l.Add('### BEGIN INIT INFO');
l.Add('# Provides:          lighttpd');
l.Add('# Required-Start:    networking');
l.Add('# Required-Stop:     networking');
l.Add('# Default-Start:     2 3 4 5');
l.Add('# Default-Stop:      0 1 6');
l.Add('# Short-Description: Start the lighttpd web server.');
l.Add('### END INIT INFO');
l.Add('');
l.Add('');
l.Add('DAEMON_OPTS="-f /etc/lighttpd/lighttpd.conf"');
l.Add('');
l.Add('');
l.Add('case "$1" in');
l.Add('  start)');
l.Add('	/etc/init.d/artica-postfix start apache --daemon');
l.Add('    ;;');
l.Add('  stop)');
l.Add('	/etc/init.d/artica-postfix stop apache --daemon');
l.Add('	;;');
l.Add('  reload)');
l.Add('	/etc/init.d/artica-postfix stop apache --daemon');
l.Add('	/etc/init.d/artica-postfix start apache --daemon');
l.Add('  ;;');
l.Add('  restart|force-reload)');
l.Add('	/etc/init.d/artica-postfix stop apache --daemon');
l.Add('	/etc/init.d/artica-postfix start apache --daemon');
l.Add('	;;');
l.Add('  *)');
l.Add('	echo "Usage: {start|stop|restart|reload|force-reload}" >&2');
l.Add('	exit 1');
l.Add('	;;');
l.Add('esac');
l.Add('');
l.Add('exit 0');
l.SaveToFile(LIGHTTPD_INITD());
end;
//##############################################################################
FUNCTION Tlighttpd.IS_IPTABLES_INPUT_RULES():boolean;
var
   tmpstr:string;
     l:TstringList;
     RegExpr:TRegExpr;
     i:integer;
begin
    result:=false;
    if not FileExists(SYS.LOCATE_IPTABLES()) then begin
         logs.Debuglogs('Starting......: lighttpd: IpTables is not installed');
         exit;
    end;
tmpstr:=LOGS.FILE_TEMP();
fpsystem(SYS.LOCATE_IPTABLES() + ' -L INPUT >'+tmpstr+' 2>&1');
if not FileExists(tmpstr) then exit;
l:=TstringList.Create;
l.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^REJECT\s+';
for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=true;
      break;
   end;
end;
RegExpr.free;
l.free;
end;
//##############################################################################
FUNCTION Tlighttpd.STATUS():string;
var
   pidpath:string;
begin
SYS.MONIT_DELETE('APP_LIGHTTPD');
if not FileExists(LIGHTTPD_BIN_PATH()) then exit;
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --lighttpd >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//#########################################################################################
procedure Tlighttpd.LIGHTTPD_CERTIFICATE();
var
   cmd:string;
   openssl_path:string;
   CertificateMaxDays:string;
   extensions:string;
begin
openssl_path:=SYS.LOCATE_OPENSSL_TOOL_PATH();
SYS.OPENSSL_CERTIFCATE_CONFIG();

    CertificateMaxDays:=SYS.GET_INFO('CertificateMaxDays');
    if length(CertificateMaxDays)=0 then CertificateMaxDays:='730';

if Not FileExists('/etc/artica-postfix/ssl.certificate.conf') then begin
   logs.Debuglogs('LIGHTTPD_CERTIFICATE():: unable to stat /etc/artica-postfix/ssl.certificate.conf');
   logs.Debuglogs('Starting......: lighttpd: unable to stat default certificate infos');
   exit;
end;
if length(SYS.OPENSSL_CERTIFCATE_HOSTS())>0 then extensions:=' -extensions HOSTS_ADDONS ';



logs.Debuglogs('Starting......: lighttpd: Creating certificate using /etc/artica-postfix/ssl.certificate.conf');
forcedirectories('/opt/artica/ssl/certs');
cmd:=openssl_path+' req -new -passin pass:artica -x509 -batch -config /etc/artica-postfix/ssl.certificate.conf '+extensions+'-keyout /opt/artica/ssl/certs/lighttpd.pem -out /opt/artica/ssl/certs/lighttpd.pem -days '+CertificateMaxDays+' -nodes';
logs.OutputCmd(cmd);

//openssl genrsa -des3 -passout  pass:artica -out www.domain.ext.key 1024

//openssl req -new -passin pass:artica -batch -config /etc/artica-postfix/ssl.certificate.conf -key www.domain.ext.key -out www.domain.ext.csr

end;

//#########################################################################################
function Tlighttpd.LIGHTTPD_VERSION():string;
var
     l:TstringList;
     RegExpr:TRegExpr;
     i:integer;
     tmpstr:string;
     D:boolean;
     cmd:string;
begin
    if not FileExists(LIGHTTPD_BIN_PATH()) then exit;
    D:=SYS.COMMANDLINE_PARAMETERS('--verbose');
    result:=SYS.GET_CACHE_VERSION('APP_LIGHTTPD');
    if length(result)>2 then exit;
    tmpstr:=logs.FILE_TEMP();
    cmd:=LIGHTTPD_BIN_PATH()+' -v >'+tmpstr+' 2>&1';
    if D then writeln(cmd);

    fpsystem(cmd);
    if not FileExists(tmpstr) then exit;
    l:=TStringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);
    RegExpr:=TRegExpr.Create;

    For i:=0 to l.Count-1 do begin
        RegExpr.Expression:='lighttpd-([0-9\.]+)';
        if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            logs.Debuglogs('LIGHTTPD_VERSION:: ' + result);
        end;

        RegExpr.Expression:='lighttpd\/([0-9\.]+)';
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


function Tlighttpd.LIGHTTPD_LOG_PATH():string;
var
RegExpr:TRegExpr;
l:TStringList;
i:integer;
begin


if not FileExists(LIGHTTPD_CONF_PATH()) then begin
   logs.Debuglogs('LIGHTTPD_LOG_PATH:: unable to stat lighttpd.conf');
   exit;
end;
l:=TstringList.Create;
try
   l.LoadFromFile(LIGHTTPD_CONF_PATH());
except
   result:='/var/log/lighttpd';
   exit;
end;
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^server\.errorlog.+?"(.+?)"';

for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
    result:=RegExpr.Match[1];
    break;
   end;
end;

   result:=ExtractFilePath(result);
   if Copy(result,length(result),1)='/' then result:=Copy(result,1,length(result)-1);
   l.Free;
   RegExpr.free;

end;
//##############################################################################
function Tlighttpd.LIGHTTPD_CERTIFICATE_PATH():string;
var
RegExpr:TRegExpr;
l:TStringList;
i:integer;
begin


if not FileExists(LIGHTTPD_CONF_PATH()) then begin
   logs.Debuglogs('LIGHTTPD_LOG_PATH:: unable to stat lighttpd.conf');
   exit;
end;
l:=TstringList.Create;
l.LoadFromFile(LIGHTTPD_CONF_PATH());
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^ssl\.pemfile.+?"(.+?)"';

for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
    result:=RegExpr.Match[1];
    break;
   end;
end;
end;
//##############################################################################


function Tlighttpd.LIGHTTPD_LISTEN_PORT():string;
var
RegExpr:TRegExpr;
l:TStringList;
i:integer;
begin
if not FileExists(LIGHTTPD_CONF_PATH()) then begin
   logs.logs('LIGHTTPD_LISTEN_PORT:: unable to stat lighttpd.conf');
   exit;
end;
l:=TstringList.Create;
l.LoadFromFile(LIGHTTPD_CONF_PATH());
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^server\.port.+?=.+?([0-9]+)';
for i:=0 to l.Count-1 do begin

   if RegExpr.Exec(l.Strings[i]) then begin
   result:=RegExpr.Match[1];
   break;
   end;
end;

   RegExpr.Free;
   l.free;

end;


function Tlighttpd.POMMO_VERSION():string;
var
l:TstringList;
i:integer;
RegExpr:TRegExpr;

begin
  if not FileExists('/usr/share/pommo/docs/RELEASE') then exit;

result:=SYS.GET_CACHE_VERSION('APP_POMMO');
if length(result)>0 then exit;

  l:=TstringList.Create;
  l.LoadFromFile('/usr/share/pommo/docs/RELEASE');
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='poMMo\s+Aardvark.+?([0-9\.]+)';

for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
end;
 RegExpr.free;
 l.free;
 SYS.SET_CACHE_VERSION('APP_AMAVIS_STAT',result);

end;
//##############################################################################
procedure Tlighttpd.POMMO_ALIASES();
var
RegExpr:TRegExpr;
l:TStringList;
f:boolean;
i:integer;
begin

if not DirectoryExists('/usr/share/pommo') then begin
      Logs.Debuglogs('Starting......: lighttpd: PoMMo is not installed, skipping aliases');
      exit;
end;

fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.pommo.php');

if not FileExists(LIGHTTPD_CONF_PATH()) then begin
   logs.Syslogs('POMMO_ALIASES:: unable to stat lighttpd.conf');
   exit;
end;

RegExpr:=TRegExpr.Create;
f:=false;
RegExpr.Expression:='^alias\.url.+?\/usr\/share\/pommo';
l:=TstringList.Create;
l.LoadFromFile(LIGHTTPD_CONF_PATH());
For i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      f:=true;
      break;
   end;
end;

if not f then begin
   Logs.Debuglogs('Starting......: lighttpd: PoMMo '+POMMO_VERSION()+' is installed, adding aliases');
   l.Add('alias.url +=("/mailing"  => "/usr/share/pommo/")');
   try
      l.SaveToFile(LIGHTTPD_CONF_PATH());
   except
      Logs.Syslogs('Starting......: lighttpd: PoMMo FATAL ERROR !');
      exit;
   end;
end else begin
   Logs.Debuglogs('Starting......: lighttpd: PoMMo is installed, aliases already added');
end;
 l.free;
 RegExpr.Free;

end;
//##############################################################################





function Tlighttpd.LIGHTTPD_PID():string;
begin

if length(mem_pid)>0 then exit(mem_pid);

if not FileExists(LIGHTTPD_PID_PATH()) then begin
   result:=SYS.PidByProcessPath(LIGHTTPD_BIN_PATH() + ' -f /etc/lighttpd/lighttpd.conf');
   mem_pid:=result;
   exit;
end;

result:=SYS.GET_PID_FROM_PATH(LIGHTTPD_PID_PATH());
result:=trim(result);
if result='0' then result:='';

if length(trim(result))<2 then  begin
   logs.Debuglogs('LIGHTTPD_PID:: unable to read '+LIGHTTPD_PID_PATH());
   result:=SYS.PidByProcessPath(LIGHTTPD_BIN_PATH() + ' -f /etc/lighttpd/lighttpd.conf');
   mem_pid:=result;
   exit;
end;
end;
//##############################################################################
function Tlighttpd.IS_AUTH_LDAP():boolean;
var

RegExpr:TRegExpr;
l:TStringList;
i:integer;

begin

if not FileExists(LIGHTTPD_CONF_PATH()) then begin
   logs.Debuglogs('LIGHTTPD_SOCKET_PATH:: unable to stat lighttpd.conf');
   exit;
end;
result:=false;
l:=TstringList.Create;
l.LoadFromFile(LIGHTTPD_CONF_PATH());
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='auth.backend[\s+=]+"(.+?)"';

for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
    result:=true;
    break;
   end;
end;
   l.Free;
   RegExpr.free;

end;
//##############################################################################




function Tlighttpd.LIGHTTPD_SOCKET_PATH():string;
var

RegExpr:TRegExpr;
l:TStringList;
i:integer;

begin

if not FileExists(LIGHTTPD_CONF_PATH()) then begin
   logs.Debuglogs('LIGHTTPD_SOCKET_PATH:: unable to stat lighttpd.conf');
   exit;
end;
l:=TstringList.Create;
l.LoadFromFile(LIGHTTPD_CONF_PATH());
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='\s+"socket".+?"(.+?)"';
for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
    result:=RegExpr.Match[1];
    break;
   end;
end;
   result:=ExtractFilePath(result);
   if Copy(result,length(result),1)='/' then result:=Copy(result,1,length(result)-1);
   l.Free;
   RegExpr.free;

end;
//##############################################################################
procedure Tlighttpd.LIGHTTPD_ADD_INCLUDE_PATH();
var
   l  :TstringList;
   t  :Tstringlist;
   i:integer;
   timezone:string;
   mysql:tmysql_daemon;
   mysql_socket:string;
   php5FuncOverloadSeven:integer;
   php5DisableMagicQuotesGpc:integer;
   php5UploadMaxFileSize:integer;
   ApcEnabledInPhp:integer;
   php5DefaultCharset:string;
   zarafa:tzarafa_server;
   UseSamePHPMysqlCredentials,PHPDefaultMysqlserverPort,ZarafaSessionTime:integer;
   PHPDefaultMysqlserver,PHPDefaultMysqlRoot,PHPDefaultMysqlPass:string;

begin

if not TryStrToInt(SYS.GET_INFO('php5DisableMagicQuotesGpc'),php5DisableMagicQuotesGpc) then php5DisableMagicQuotesGpc:=0;
if not TryStrToInt(SYS.GET_INFO('php5FuncOverloadSeven'),php5FuncOverloadSeven) then php5FuncOverloadSeven:=0;
if not TryStrToInt(sys.GET_INFO('ApcEnabledInPhp'),ApcEnabledInPhp) then ApcEnabledInPhp:=0;
if not TryStrToInt(sys.GET_INFO('UseSamePHPMysqlCredentials'),UseSamePHPMysqlCredentials) then UseSamePHPMysqlCredentials:=1;
if not TryStrToInt(sys.GET_INFO('PHPDefaultMysqlserverPort'),PHPDefaultMysqlserverPort) then PHPDefaultMysqlserverPort:=3306;
if not TryStrToInt(sys.GET_INFO('ZarafaSessionTime'),ZarafaSessionTime) then ZarafaSessionTime:=1440;


PHPDefaultMysqlRoot:=SYS.GET_INFO('PHPDefaultMysqlRoot');
PHPDefaultMysqlserver:=SYS.GET_INFO('PHPDefaultMysqlserver');
if PHPDefaultMysqlserver='localhost' then PHPDefaultMysqlserver:='127.0.0.1';
if length(PHPDefaultMysqlserver)=0 then PHPDefaultMysqlserver:='127.0.0.1';
PHPDefaultMysqlPass:=SYS.GET_INFO('PHPDefaultMysqlPass');

php5DefaultCharset:=trim(sys.GET_INFO('php5DefaultCharset'));
if length(php5DefaultCharset)=0 then php5DefaultCharset:='utf-8';

  forceDirectories('/var/lib/php5');
  mysql:=tmysql_daemon.Create(SYS);
  mysql_socket:=mysql.SERVER_PARAMETERS('socket');
  l:=Tstringlist.Create;
  timezone:=SYS.GET_INFO('timezones');
  if length(trim(timezone))=0 then timezone:='Europe/Berlin';
l.Add('[PHP]');
l.Add('safe_mode = Off');
l.Add('safe_mode_gid = Off');
l.Add('engine = On');
l.Add('precision    =  12');
l.Add('y2k_compliance = On');
l.Add('output_buffering = On');
l.Add('serialize_precision = 100');
l.Add('disable_functions =');
l.Add('disable_classes =');
l.Add('expose_php = Off');
l.Add('max_execution_time = 3600');
l.Add('max_input_time = 3600');
l.Add('memory_limit = 500M');
l.Add('error_reporting  =  E_ALL & ~E_NOTICE');
l.Add('display_errors = Off');
l.Add('display_startup_errors = Off');
l.Add('log_errors = On');
l.Add('log_errors_max_len = 2048');
l.Add('ignore_repeated_errors = Off');
l.Add('ignore_repeated_source = Off');
l.Add('report_memleaks = On');
l.Add('track_errors = Off');
l.Add('error_prepend_string = "<font color=ff0000><code style=''font-size:12px''>"');
l.Add('error_append_string = "</code></font><br>"');
l.Add('html_errors = false');
l.Add('error_log = /usr/share/artica-postfix/ressources/logs/php.log');
l.Add('variables_order = "EGPCS"');
l.Add('register_argc_argv = On');
l.Add('auto_globals_jit = On');
l.Add('post_max_size = 128M');
l.Add('auto_prepend_file =');
l.Add('auto_append_file =');
l.Add('default_mimetype = "text/html"');
l.Add('default_charset = "'+php5DefaultCharset+'"');
l.Add('unicode.semantics = off');
l.Add('unicode.runtime_encoding = utf-8');
l.Add('unicode.script_encoding = utf-8');
l.Add('unicode.output_encoding = utf-8');
l.Add('unicode.from_error_mode = U_INVALID_SUBSTITUTE');
l.Add('unicode.from_error_subst_char = 3f');
l.Add('include_path = ".:/usr/share/php:/usr/share/obm:/usr/share/php5:/usr/share/obm2:/usr/local/share/php:/usr/share/artica-postfix/ressources/externals:/usr/share/artica-postfix/ressources/externals/Gdata:/usr/share/php5/PEAR:/usr/share/pear"');
l.Add('doc_root =');
l.Add('user_dir =');
l.Add('extension_dir = "'+ SYS.LOCATE_PHP5_EXTENSION_DIR()+'"');
l.Add('cgi.force_redirect = 1');
l.Add('cgi.fix_pathinfo = 1');
l.Add('file_uploads = On');
l.Add('upload_tmp_dir =/var/lighttpd/upload');


if not tryStrToint(SYS.GET_INFO('php5UploadMaxFileSize'),php5UploadMaxFileSize) then begin
   php5UploadMaxFileSize:=256;
   SYS.set_INFO('php5UploadMaxFileSize','256');
end;

logs.Debuglogs('Starting......: lighttpd: Max upload size set to '+IntToStr(php5UploadMaxFileSize)+'M');

if FileExists('/proc/sys/vm/overcommit_memory') then begin
    logs.Debuglogs('Starting......: lighttpd: enable kernel overcommit_memory ');
    logs.WriteToFile('1','/proc/sys/vm/overcommit_memory');
end;




l.Add('upload_max_filesize = '+IntToStr(php5UploadMaxFileSize)+'M');
l.Add('allow_url_fopen = On');
l.Add('allow_url_include = Off');
l.Add('from="anonymous@anonymous.com"');
l.Add('default_socket_timeout = 60');
l.Add('safe_mode = Off');
if php5FuncOverloadSeven=1 then begin
   if DirectoryExists(roundcube_main_folder()) then begin
      logs.Debuglogs('Starting......: lighttpd: Warning, mbstring.func_overload is enabled to 7');
      logs.Debuglogs('Starting......: lighttpd: But RoundCube require 0, switch to 0');
      l.Add('mbstring.func_overload = 0');
   end else begin
      l.add('mbstring.func_overload = 7');
   end;
end;
if php5DisableMagicQuotesGpc=1 then begin
   l.add('magic_quotes_gpc = Off');
end;

if FileExists('/usr/local/ioncube/ioncube_loader_lin_5.2.so') then begin
l.add('zend_extension=/usr/local/ioncube/ioncube_loader_lin_5.2.so');
end;

l.Add('');
l.Add('[Date]');
l.add('date.timezone = "'+timezone+'"');
l.Add('');
l.Add('[filter]');
l.Add('[iconv]');
l.Add('iconv.input_encoding = utf-8');
l.Add('iconv.internal_encoding = utf-8');
l.Add('iconv.output_encoding = utf-8');
l.Add('[Syslog]');
l.Add('define_syslog_variables  = Off');
l.Add('');
l.Add('[mail function]');
l.Add('[SQL]');
l.Add('sql.safe_mode = Off');
l.Add('');
l.Add('[ODBC]');
l.Add('odbc.allow_persistent = On');
l.Add('odbc.check_persistent = On');
l.Add('odbc.max_persistent = -1');
l.Add('odbc.max_links = -1');
l.Add('odbc.defaultlrl = 4096');
l.Add('odbc.defaultbinmode = 1');
l.Add('');

if UseSamePHPMysqlCredentials=1 then begin
   if not TryStrToInt(SYS.GET_MYSQL('port'),PHPDefaultMysqlserverPort) then PHPDefaultMysqlserverPort:=3306;
   PHPDefaultMysqlserver:=SYS.GET_MYSQL('mysql_server');
   PHPDefaultMysqlRoot:=SYS.GET_MYSQL('database_admin');
   PHPDefaultMysqlPass:=SYS.GET_MYSQL('database_password');
end;

logs.Debuglogs('Starting......: lighttpd: Default mysql settings to "'+PHPDefaultMysqlRoot+'@'+PHPDefaultMysqlserver+':'+intToStr(PHPDefaultMysqlserverPort));

l.Add('[MySQL]');
l.Add('mysql.allow_persistent = On');
l.Add('mysql.max_persistent = -1');
l.Add('mysql.max_links = -1');
l.Add('mysql.default_port ='+IntToStr(PHPDefaultMysqlserverPort));
l.Add('mysql.default_socket ="'+mysql_socket+'"');
l.Add('mysql.default_host ='+PHPDefaultMysqlserver);
l.Add('mysql.default_user ='+PHPDefaultMysqlRoot);
l.Add('mysql.default_password ="'+PHPDefaultMysqlPass+'"');
l.Add('mysql.connect_timeout = 60');
l.Add('mysql.trace_mode = Off');
l.Add('[LDAP]');
l.Add('ldap.max_links = -1');
l.Add('ldap.allow_persistent = On');
l.Add('ldap.check_persistent = On');
l.Add('');
l.Add('[MySQLi]');
l.Add('mysqli.max_links = -1');
l.Add('mysqli.default_port = '+IntToStr(PHPDefaultMysqlserverPort));
l.Add('mysqli.default_socket ="'+mysql_socket+'"');
l.Add('mysqli.default_host ='+PHPDefaultMysqlserver);
l.Add('mysqli.default_user ='+PHPDefaultMysqlRoot);
l.Add('mysqli.default_pw ="'+PHPDefaultMysqlPass+'"');
l.Add('mysqli.reconnect = Off');
l.Add('');
l.Add('[mSQL]');
l.Add('msql.allow_persistent = On');
l.Add('msql.max_persistent = -1');
l.Add('msql.max_links = -1');
l.Add('');
l.Add('[OCI8]');
l.Add('[PostgresSQL]');
l.Add('[Sybase]');
l.Add('[Sybase-CT]');
l.Add('[bcmath]');
l.Add('[browscap]');
l.Add('[Informix]');
l.Add('[Session]');
l.Add('session.save_handler = files');
l.Add('session.save_path = "/var/lib/php5"');
l.Add('session.use_cookies = 1');
l.Add('session.use_only_cookies = 1');
l.Add('session.name = PHPSESSID');
l.Add('session.auto_start = 0');
l.Add('session.cookie_lifetime = 0');
l.Add('session.cookie_path = /');
l.Add('session.cookie_domain =');
l.Add('session.cookie_httponly =');
l.Add('session.serialize_handler = php');
l.Add('session.gc_probability = 1');
l.Add('session.gc_divisor     = 100');

l.Add('session.gc_maxlifetime = '+IntToStr(ZarafaSessionTime));
l.Add('session.referer_check =');
l.Add('session.entropy_length = 0');
l.Add('session.entropy_file =');
l.Add('session.cache_limiter = nocache');
l.Add('session.cache_expire = 420');
l.Add('session.use_trans_sid = 0');
l.Add('session.hash_function = 0');
l.Add('session.bug_compat_warn = Off');
l.Add('session.hash_bits_per_character = 4');
l.Add('url_rewriter.tags = "a=href,area=href,frame=src,input=src,form=,fieldset="');
l.Add('');
l.Add('[MSSQL]');
l.Add('mssql.allow_persistent = On');
l.Add('mssql.max_persistent = -1');
l.Add('mssql.max_links = -1');
l.Add('mssql.min_error_severity = 10');
l.Add('mssql.min_message_severity = 10');
l.Add('mssql.compatability_mode = Off');
l.Add('mssql.connect_timeout = 5');
l.Add('mssql.timeout = 60');
l.Add('mssql.textlimit = 4096');
l.Add('mssql.textsize = 4096');
l.Add('mssql.batchsize = 0');
l.Add('mssql.datetimeconvert = On');
l.Add('mssql.secure_connection = Off');
l.Add('mssql.max_procs = -1');
l.Add('mssql.charset = "ISO-8859-1"');
l.Add('');
l.Add('[Assertion]');
l.Add('[COM]');
l.Add('[mbstring]');
l.Add('[FrontBase]');
l.Add('[gd]');
l.Add('[exif]');
l.Add('[Tidy]');
l.Add('tidy.clean_output = Off');
l.Add('');
l.Add('[soap]');
l.Add('soap.wsdl_cache_ttl=86400');
if DisableEaccelerator=0 then begin
   if fileExists(SYS.LOCATE_EACCELERATOR_SO()) then begin
      logs.DebugLogs('Starting......: Apache groupware eaccelerator.so detected');
      forceDirectories('/tmp/eaccelerator2');
      fpsystem('/bin/chmod 700 /tmp/eaccelerator2');
      fpsystem('/bin/chown www-data:www-data /tmp/eaccelerator2');
      l.add('extension="eaccelerator.so"');
      l.Add('eaccelerator.shm_size="16"');
      l.Add('eaccelerator.cache_dir="/tmp/eaccelerator2"');
      l.Add('eaccelerator.enable="1"');
      l.Add('eaccelerator.optimizer="1"');
      l.Add('eaccelerator.check_mtime="1"');
      l.Add('eaccelerator.debug="0"');
      l.Add('eaccelerator.filter=""');
      l.Add('eaccelerator.shm_max="0"');
      l.Add('eaccelerator.shm_ttl="0"');
      l.Add('eaccelerator.shm_prune_period="0"');
      l.Add('eaccelerator.shm_only="0"');
      l.Add('eaccelerator.compress="1"');
      l.Add('eaccelerator.compress_level="9"');
   end;
end else begin
    logs.Debuglogs('Starting......: lighttpd: php.ini key eaccelerator is disabled');
end;

if FileExists(SYS.LOCATE_APC_SO()) then begin
   if ApcEnabledInPhp=1 then begin
      logs.Debuglogs('Starting......: lighttpd: php.ini enable APC client');
      l.Add('');
      l.Add('extension=apc.so');
      l.Add('[APC]');
      l.Add('apc.enable_cli="1"');
      l.Add('apc.stat ="0"');
      l.add('apc.include_once_override="0"');
      l.add('apc.cache_by_default="0"');
      l.add('apc.filters = "-(\.php|\.inc)"');
      l.Add('');
   end else begin
      logs.Debuglogs('Starting......: lighttpd: php.ini disable APC client');
   end;
end;

l.Add(PHP5_CHECK_EXTENSIONS());

zarafa:=tzarafa_server.Create(SYS);
if FileExists(zarafa.BIN_PATH()) then begin
   if FileExists(SYS.LOCATE_MAPI_SO()) then begin
     logs.Debuglogs('Starting......: lighttpd: register mapi.so');
     l.Add('extension=mapi.so');
   end else begin
      logs.Debuglogs('Starting......: lighttpd: mapi.so no such file !!!');
   end;
end;

if FileExists('/etc/artica-postfix/php.include.ini') then begin
      logs.Debuglogs('Starting......: lighttpd: Adding user defined values');
      l.Add(logs.ReadFromFile('/etc/artica-postfix/php.include.ini'));
end;

  t:=Tstringlist.Create;
  t.add('/etc/php.ini');
  t.Add('/etc/php5/cli/php.ini');
  t.Add('/etc/php5/cgi/php.ini');
  t.add('/etc/php5/apache2/php.ini');
  t.add('/etc/php/php.ini');
  t.add('/etc/php-cgi-fcgi.ini');
  t.add('/etc/php5/fastcgi/php.ini');

  for i:=0 to t.Count-1 do begin
      if FileExists(t.Strings[i]) then begin
         logs.Debuglogs('Starting......: lighttpd: registers key in '+t.Strings[i]);
         logs.WriteToFile(l.Text,t.Strings[i]);
      end;
  end;

  forceDirectories('/etc/artica-postfix/roundcube');
  logs.WriteToFile(l.Text,'/etc/artica-postfix/roundcube/php.ini');

  t.free;
  l.free;
  ForceDirectories('/usr/share/artica-postfix/ressources/profiles');


  fpsystem('/bin/chmod 755 /usr/share/artica-postfix/ressources/profiles');
  logs.Debuglogs('Starting......: lighttpd: Compile languages');
  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.shm.php --parse-langs');


  end;
//##############################################################################
function Tlighttpd.roundcube_main_folder():string;
begin

if FileExists('/usr/share/roundcubemail/index.php') then exit('/usr/share/roundcubemail');
if FileExists('/usr/share/roundcube/index.php') then exit('/usr/share/roundcube');
if FileExists('/var/lib/roundcube/index.php') then exit('/var/lib/roundcube');
end;
//##############################################################################





function Tlighttpd._IS_INCLUDE_EXISTS(include_value:string;needed:string):boolean;
var
 l:TStringDynArray;
 i:integer;
begin
   result:=false;
   if length(include_value)=0 then exit(false);
   l:=Explode(':',include_value);
   for i:=0 to length(l)-1 do begin
       if l[i]=needed then exit(true);
   end;
end;
//##############################################################################
function Tlighttpd.PHP5_CHECK_EXTENSIONS:string;
var
l:TstringList;
z:Tstringlist;
t:Tstringlist;
confdir:string;
i:integer;
sofile:string;
soname:string;
libdir:string;
LOCATE_PHP5_EXTENSION_DIR:string;
NoPHPMcrypt:integer;
begin



confdir:=SYS.LOCATE_PHP5_EXTCONF_DIR();
LOCATE_PHP5_EXTENSION_DIR:=sys.LOCATE_PHP5_EXTENSION_DIR();
if not DirectoryExists(confdir) then begin
    logs.Debuglogs('Starting......: lighttpd: Unable to stat php5 additional ini files path');
    exit;
end;


sys.DirFiles(confdir,'*.ini');

logs.Debuglogs('Starting......: lighttpd: Ext dir: '+confdir +'('+ intToSTr(sys.DirListFiles.Count)+' ini files)');
for i:=0 to sys.DirListFiles.Count-1 do begin
    logs.DeleteFile(confdir+'/'+sys.DirListFiles.Strings[i]);

end;

if not TryStrToInt(SYS.GET_INFO('NoPHPMcrypt'),NoPHPMcrypt) then NoPHPMcrypt:=0;

t:=Tstringlist.Create;
t.Add('/etc/php5/conf.d/mcrypt.ini');
t.add(confdir+'/z-mailparse.ini');
t.add(confdir+'/mcrypt.ini');
t.add(confdir+'/eaccelerator.ini');
t.add('/etc/php5/conf.d/mcrypt.ini');
t.add('/etc/php5/conf.d/eaccelerator.ini');
t.add('/etc/php5/cli/conf.d/eaccelerator.ini');
t.add('/etc/php5/conf.d/eaccelerator.so.ini');
t.add('/etc/php.d/28_ldap.ini');
t.add('/etc/php.d/29_mbstring.ini');
t.add('/etc/php.d/30_mcrypt.ini');
t.add('/etc/php.d/36_mysql.ini');
t.add('/etc/php.d/23_gd.ini');
t.add('/etc/php.d/82_json.ini');
t.add('/etc/php.d/43_posix.ini');
t.add('/etc/php.d/47_session.ini');
t.add('/etc/php.d/27_imap.ini');
t.add('/etc/php.d/13_curl.ini');
t.add('/etc/php.d/A12_mailparse.ini');
t.add('/etc/php.d/33_apc.ini');
t.add('/etc/php5/cli/conf.d/ming.ini');


for i:=0 to t.Count -1 do begin
    if FIleExists(t.Strings[i]) then logs.DeleteFile(t.Strings[i]);
end;

t.free;


if DirectoryExists('/etc/php5/cli/conf.d') then begin
    fpsystem('rm -f /etc/php5/cli/conf.d/*.ini');
    fpsystem('rm -f /etc/php5/cli/conf.d/*.so.ini');
end;

if DirectoryExists('/etc/php5/cgi/conf.d') then begin
    fpsystem('rm -f /etc/php5/cgi/conf.d/*.ini');
    fpsystem('rm -f /etc/php5/cgi/conf.d/*.so.ini');
end;





fpsystem('/bin/ln -s /usr/share/artica-postfix/ressources/logs/php.log /var/log/php.log >/dev/null 2>&1');

l:=Tstringlist.Create;
z:=Tstringlist.Create;

z.add('ctype.so');
z.add('pcntl.so');
z.add('curl.so');
z.add('openssl.so');
z.add('fileinfo.so');
z.add('dom.so');
z.add('ftp.so');
z.add('gd.so');
z.add('iconv.so');
z.add('imap.so');
z.add('ldap.so');
z.add('mysql.so');
z.add('readline.so');
z.add('hash.so');
z.add('xml.so');
z.add('sockets.so');
//z.add('xmlreader.so');
z.add('xmlwriter.so');
z.add('filter.so');
z.add('phpcups.so');
z.add('mysqli.so');
z.add('pdo.so');
z.add('pdo_mysql.so');
z.add('pdo_sqlite.so');
z.add('sqlite.so');
z.add('posix.so');
z.add('zip.so');
z.add('xapian.so');
z.add('geoip.so');
z.add('zlib.so');
z.add('tokenizer.so');
z.add('mailparse.so');
z.add('json.so');
z.add('uploadprogress.so');
z.add('xmlrpc.so');
z.add('session.so');
z.add('gettext.so');
z.add('mbstring.so');
z.add('ssh2.so');
z.add('pspell.so');

if NoPHPMcrypt=0 then begin
   z.add('mcrypt.so');
   z.add('ming.so');
end else begin
  logs.Debuglogs('Starting......: lighttpd: mcrypt is disabled');
end;


if DisableEaccelerator=1 then begin
    logs.Debuglogs('Starting......: lighttpd: eAccelerator is disabled');
end else begin
   z.add('eaccelerator.so');
end;


for i:=0 to z.Count-1 do begin
     sofile:=LOCATE_PHP5_EXTENSION_DIR+'/'+z.Strings[i];
     soname:=IntToStr(i)+'_'+z.Strings[i]+'.ini';
     soname:=AnsiReplaceText(soname,'.so','');

     if not FileExists(sofile) then begin
        if FIleExists('/usr/lib/php/modules/'+z.Strings[i]) then begin
           logs.Debuglogs('Starting......: lighttpd: linking '+z.Strings[i]+' from /usr/lib/php/modules');
           fpsystem('/bin/ln -s /usr/lib/php/modules/'+z.Strings[i] +' '+LOCATE_PHP5_EXTENSION_DIR+'/'+z.Strings[i]);
        end;
     end;

     if FileExists(sofile)  then begin
        logs.Debuglogs('Starting......: lighttpd including extension '+z.Strings[i]);
        l.Add('extension='+z.Strings[i]);
     end else begin
     logs.Debuglogs('Starting......: lighttpd excluding extension '+z.Strings[i]+' no such file');
     logs.Debuglogs('lighttpd: '+sofile+' didn''t exists..');
     end;
end;



//             open_basedir

if DisableEaccelerator=0 then begin
forcedirectories('/tmp/eaccelerator');
if FileExists(SYS.LOCATE_EACCELERATOR_SO()) then begin
   if not FileExists(confdir+'/eaccelerator.so.ini') then begin
      l.Add('# configuration for php eaccelerator');
      l.Add('extension=eaccelerator.so');
      l.Add('eaccelerator.shm_size="0"');
      l.Add('eaccelerator.cache_dir="/tmp/eaccelerator"');
      l.Add('eaccelerator.enable="1"');
      l.Add('eaccelerator.optimizer="1"');
      l.Add('eaccelerator.check_mtime="1"');
      l.Add('eaccelerator.debug="0"');
      l.Add('eaccelerator.filter=""');
      l.Add('eaccelerator.shm_max="0"');
      l.Add('eaccelerator.shm_ttl="0"');
      l.Add('eaccelerator.shm_prune_period="0"');
      l.Add('eaccelerator.shm_only="0"');
      l.Add('eaccelerator.compress="1"');
      l.Add('eaccelerator.compress_level="9"');
      l.SaveToFile(confdir+'/eaccelerator.ini');
      l.Clear;
   end;
   end;
end;

result:=L.Text;
FreeAndNil(l);

end;
//##############################################################################
function Tlighttpd.lighttpd_modules_path():string;
begin
if fileExists('/usr/lib64/lighttpd/mod_alias.so') then exit('/usr/lib64/lighttpd');
if fileExists('/usr/local/lib64/lighttpd/mod_alias.so') then exit('/usr/local/lib64/lighttpd');
if FileExists('/usr/lib/lighttpd/mod_alias.so') then exit('/usr/lib/lighttpd');
if FileExists('/usr/local/lib/lighttpd/mod_alias.so') then exit('/usr/local/lib/lighttpd');
end;
//##############################################################################
procedure Tlighttpd.CHECK_SUBFOLDER();
begin
    if DirectoryExists('/usr/share/oma') then begin
       logs.OutputCmd('/bin/ln -s --force /usr/share/oma /usr/share/artica-postfix/oma');
    end;

    if DirectoryExists('/usr/share/roundcube') then begin
       logs.OutputCmd('/bin/ln -s --force /usr/share/roundcube /usr/share/artica-postfix/webmail');
       logs.OutputCmd('/bin/ln -s --force /usr/share/roundcube /usr/share/artica-postfix/roundcube');
    end;

end;
//##############################################################################
function Tlighttpd.SET_PHP_CGI_BINPATH():boolean;
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
   php_cgi:string;
   found:boolean;
   line_f:integer;
begin
result:=true;
   if not FileExists(LIGHTTPD_CONF_PATH()) then begin
      logs.Debuglogs('Starting......: lighttpd: unable to locate lighttpd.conf');
      exit;
   end;

   php_cgi:=PHP5_CGI_BIN_PATH();

   if length(php_cgi)=0 then begin
      logs.Debuglogs('Starting......: lighttpd: unable to stat php-cgi,php5-cgi...');
      exit;
   end;


l:=TstringList.Create;
l.LoadFromFile(LIGHTTPD_CONF_PATH());
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='"bin-path"';
found:=false;

for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       l.Strings[i]:='                "bin-path" => "'+php_cgi+'",';
       found:=true;
       result:=true;
       line_f:=i;
       break;
    end;
end;

if found then begin
       logs.Debuglogs('Starting......: lighttpd: set ' + php_cgi + ' in line ('+IntToStr(line_f)+')');
       l.SaveToFile(LIGHTTPD_CONF_PATH());
       l.free;
       RegExpr.free;
       exit(true);
end;




end;




procedure Tlighttpd.LIGHTTPD_DEFAULT_CONF_SAVE();
var
l:TStringList;
begin
l:=TstringList.Create;
logs.logs('LIGHTTPD_DEFAULT_CONF_SAVE:: Saving file ' +LIGHTTPD_CONF_PATH());
l.Add(DEFAULT_CONF());
logs.WriteToFile(L.Text,LIGHTTPD_CONF_PATH());
l.free;
end;
//##############################################################################
function tlighttpd.DEFAULT_CONF():string;
var
l:TstringList;
mailman:tmailman;
user:string;
RegExpr:TRegExpr;
group,name:string;
PHP_FCGI_CHILDREN:integer;
PHP_FCGI_MAX_REQUESTS:integer;
max_procs:integer;
roundcube_folder:string;
zarafa:tzarafa_server;
ArticaHttpsPort:integer;
backuppcCgiBin:string;
perlbin:string;
backuppc:tbackuppc;
LighttpdEnableSSLV2:integer;
ldap_admin,ldap_password,ldap_server,ldap_port,ldap_suffix:string;
mod_auth:boolean;
NoLDAPInLighttpdd:integer;
ArticaHttpUseSSL:integer;
LighttpdUseUnixSocket:integer;
lighttpdPhpPort:integer;
DenyMiniWebFromStandardPort:integer;
LighttpdArticaDisableSSLv2:integer;
LighttpdArticaMaxProcs:integer;
LighttpdArticaMaxChildren:integer;
LighttpdRunAsminimal:integer;
begin
ArticaHttpsPort:=9000;
NoLDAPInLighttpdd:=0;
ArticaHttpUseSSL:=1;

if not tryStrToInt(SYS.GET_INFO('ArticaHttpUseSSL'),ArticaHttpUseSSL) then ArticaHttpUseSSL:=1;
if not tryStrToInt(sys.GET_INFO('NoLDAPInLighttpdd'),NoLDAPInLighttpdd) then NoLDAPInLighttpdd:=0;
if not tryStrToInt(sys.GET_INFO('LighttpdUseUnixSocket'),LighttpdUseUnixSocket) then LighttpdUseUnixSocket:=0;
if not tryStrToInt(sys.GET_INFO('lighttpdPhpPort'),lighttpdPhpPort) then lighttpdPhpPort:=1808;
if not tryStrToInt(sys.GET_INFO('DenyMiniWebFromStandardPort'),DenyMiniWebFromStandardPort) then DenyMiniWebFromStandardPort:=0;
if not tryStrToInt(sys.GET_INFO('LighttpdArticaDisableSSLv2'),LighttpdArticaDisableSSLv2) then LighttpdArticaDisableSSLv2:=0;
if not tryStrToInt(sys.GET_INFO('LighttpdArticaMaxProcs'),LighttpdArticaMaxProcs) then LighttpdArticaMaxProcs:=0;
if not tryStrToInt(sys.GET_INFO('LighttpdArticaMaxChildren'),LighttpdArticaMaxChildren) then LighttpdArticaMaxChildren:=0;
if not tryStrToInt(sys.GET_INFO('LighttpdRunAsminimal'),LighttpdRunAsminimal) then LighttpdRunAsminimal:=0;



user:=LIGHTTPD_GET_USER();
LOAD_MODULES();
mod_auth:=isModule('mod_auth');
if length(user)=0 then user:=SYS.GET_INFO('LighttpdUserAndGroup');
if length(user)=0 then begin
   user:='www-data:www-data';
   SYS.set_INFO('LighttpdUserAndGroup',user);
end;

if not TrYStrToInt(SYS.GET_INFO('ArticaHttpsPort'),ArticaHttpsPort) then ArticaHttpsPort:=9000;
logs.Debuglogs('Starting......: lighttpd: (config) running on port '+IntToStr(ArticaHttpsPort));




RegExpr:=TRegExpr.Create;
RegExpr.Expression:='(.+?):(.+)';
RegExpr.Exec(user);
name:=RegExpr.Match[1];
group:=RegExpr.Match[2];
if RegExpr.Exec(name) then name:=RegExpr.Match[1];



roundcube_folder:=SYS.locate_roundcube_main_folder();

if RegExpr.Exec(name) then name:=RegExpr.Match[1];

name:=AnsireplaceText(name,'www-data:www-data','www-data');
name:=AnsireplaceText(name,'lighttpd:lighttpd:','lighttpd');

PHP_FCGI_CHILDREN:=3;
PHP_FCGI_MAX_REQUESTS:=500;
max_procs:=2;
if not InsufficentRessources then begin
     PHP_FCGI_CHILDREN:=2;
     PHP_FCGI_MAX_REQUESTS:=1000;
     max_procs:=1;
end;

if LighttpdArticaMaxProcs>0 then max_procs:=LighttpdArticaMaxProcs;
if LighttpdArticaMaxChildren>0 then PHP_FCGI_CHILDREN:=LighttpdArticaMaxChildren;

if LighttpdRunAsminimal=1 then begin
       max_procs:=1;
       PHP_FCGI_CHILDREN:=1;
end;

l:=TstringList.Create;
l.Add('#artica-postfix saved by artica lighttpd.conf');
l.Add('');
l.Add('server.modules = (');
l.Add('        "mod_alias",');
l.Add('        "mod_access",');
l.Add('        "mod_accesslog",');
l.Add('        "mod_compress",');
l.Add('        "mod_fastcgi",');
l.Add('        "mod_cgi",');
l.Add('	       "mod_status",');
if NoLDAPInLighttpdd=1 then begin
     logs.Debuglogs('Starting......: lighttpd: (config) LDAP Mode is disabled');
end;

if mod_auth then l.Add('	       "mod_auth"') else logs.Debuglogs('Starting......: lighttpd: (config) mod_auth module does not exists (should be a security issue !!!)');

logs.Debuglogs('Starting......: lighttpd: user:"'+name+'"');

l.Add(')');
l.Add('');
l.Add('server.document-root        = "/usr/share/artica-postfix"');
l.Add('server.username = "'+name+'"');
l.Add('server.groupname = "'+group+'"');
l.Add('server.errorlog             = "/var/log/lighttpd/error.log"');
l.Add('index-file.names            = ( "index.php","index.cgi")');
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
l.Add('accesslog.filename          = "/var/log/lighttpd/access.log"');
l.Add('url.access-deny             = ( "~", ".inc",".log",".ini" )');
l.Add('');
l.Add('static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )');
l.Add('server.port                 = '+IntToStr(ArticaHttpsPort));
l.Add('#server.bind                = "127.0.0.1"');
l.Add('server.pid-file             = "/var/run/lighttpd/lighttpd.pid"');
l.Add('server.max-fds 		   = 2048');
l.Add('server.network-backend      = "write"');
l.Add('server.error-handler-404    = "404.php"');

fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.lighttpd.nets.php');
if FileExists('/etc/artica-postfix/lighttpd_nets') then l.Add(logs.ReadFromFile('/etc/artica-postfix/lighttpd_nets'));

l.Add('');
l.Add('fastcgi.server = ( ".php" =>((');
l.Add('         "bin-path" => "/usr/bin/php-cgi",');
if LighttpdUseUnixSocket=1 then begin
   logs.Debuglogs('Starting......: lighttpd: Fast-cgi server unix socket mode');
   l.Add('         "socket" => "/var/run/lighttpd/php.socket",');
end else begin
   logs.Debuglogs('Starting......: lighttpd: Fast-cgi server socket 127.0.0.1:'+IntTostr(lighttpdPhpPort));
    l.add('         "host" => "127.0.0.1","port" =>'+intToStr(lighttpdPhpPort)+',');
end;
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
if ArticaHttpUseSSL=1 then begin
   l.Add('ssl.engine                 = "enable"');
   l.Add('ssl.pemfile                = "/opt/artica/ssl/certs/lighttpd.pem"');

   if LighttpdArticaDisableSSLv2=1 then begin
      logs.Debuglogs('Starting......: lighttpd: disable SSLv2 and weak ssl cipher');
      l.Add('ssl.use-sslv2              = "disable"');
      l.Add('ssl.cipher-list            = "TLSv1+HIGH !SSLv2 RC4+MEDIUM !aNULL !eNULL !3DES @STRENGTH"');
   end else begin
      l.Add('ssl.use-sslv2              = "enable"');
      l.Add('ssl.cipher-list            = "TLSv1+HIGH RC4+MEDIUM !SSLv2 !3DES !aNULL @STRENGTH"');
   end;

end;
if NoLDAPInLighttpdd=0 then begin
if mod_auth then begin
   l.Add('status.status-url          = "/server-status"');
   l.Add('status.config-url          = "/server-config"');
end;
end;
l.Add('server.upload-dirs         = ( "/var/lighttpd/upload" )');


forceDirectories('/var/lighttpd/upload');
fpsystem('/bin/chown -R '+name+' /var/lighttpd');

if DirectoryExists(roundcube_folder) then begin
logs.Debuglogs('Starting......: lighttpd: (config) roundcube is installed on '+roundcube_folder);
fpsystem(SYS.LOCATE_GENERIC_BIN('nohup')+' '+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.roundcube.php --databases >/dev/null 2>&1 &');
l.Add('alias.url += (	"/webmail" 			 => "'+roundcube_folder+'")');
l.Add('$HTTP["url"] =~ "^/webmail/config|/webmail/temp|/webmail/logs" { url.access-deny = ( "" )}');
end;

zarafa:=tzarafa_server.Create(SYS);
if FileExists(zarafa.SERVER_BIN_PATH()) then begin
logs.Debuglogs('Starting......: lighttpd: (config) zarafa is installed');
l.Add('alias.url += (	"/webaccess" 			 => "/usr/share/zarafa-webaccess")');
l.Add('alias.url += (	"/webmail" 			 => "/usr/share/zarafa-webaccess")');
zarafa.WEB_ACCESS_CONFIG();
//l.Add('$HTTP["url"] =~ "^/webmail/config|/webmail/temp|/webmail/logs" { url.access-deny = ( "" )}');
end;


l.Add('	server.follow-symlink = "enable"');
l.Add('alias.url +=("/monitorix"  => "/var/www/monitorix/")');
l.Add('alias.url += ("/blocked_attachments"=> "/var/spool/artica-filter/bightml")');

if DenyMiniWebFromStandardPort=1 then begin
   l.Add('$HTTP["url"] =~ "^/miniadm.*|/computers|/user-backup" { url.access-deny = ( "" )}');
end;

if DirectoryExists(awstats.AWSTATS_www_root()) then l.Add('alias.url += ( "/awstats" => "'+awstats.AWSTATS_www_root()+'" )');
if FileExists('/usr/share/poweradmin/index.php') then begin
   l.Add('alias.url += ( "/powerdns" => "/usr/share/poweradmin" )');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.pdns.php --poweradmin');
end;
perlbin:=SYS.LOCATE_GENERIC_BIN('perl');
l.Add('alias.url += ( "/cgi-bin/" => "/usr/lib/cgi-bin/" )');

ldap_admin:=SYS.get_LDAP('admin');
ldap_password:=SYS.get_LDAP('password');
ldap_server:=SYS.get_LDAP('server');
ldap_port:=SYS.get_LDAP('port');
ldap_suffix:=SYS.get_LDAP('suffix');
if length(trim(ldap_server))=0 then ldap_server:='127.0.0.1';

if NoLDAPInLighttpdd=0 then begin
   if ldap_server='127.0.0.1' then if not fileExists(SYS.LOCATE_SLAPD()) then mod_auth:=false;

if mod_auth then begin
   // LDAP AUTH ---------------------------------------------------------------------
   l.Add('');
   logs.Debuglogs('Starting......: lighttpd: (config) auth plain');
end;
end;
// BackuPPC ---------------------------------------------------------------------



backuppc:=tbackuppc.Create(SYS);
backuppcCgiBin:=backuppc.CGI_BIN_PATH();
logs.Debuglogs('Starting......: lighttpd: (config) BackupPC "'+backuppcCgiBin+'"');
   if DirectoryExists(backuppcCgiBin) then begin
      logs.Debuglogs('Starting......: lighttpd: (config) BackupPC is installed');
      l.Add('alias.url  += ( "/backuppc" => "'+backuppcCgiBin+'")');
    end else begin
    logs.Debuglogs('Starting......: lighttpd: (config) BackupPC is not installed');
end;

l.Add('');
l.Add('cgi.assign= (');
l.Add('	".pl"  => "'+perlbin+'",');
l.Add('	".php" => "/usr/bin/php-cgi",');
l.Add('	".py"  => "/usr/bin/python",');
l.Add('	".cgi"  => "'+perlbin+'",');

mailman:=tmailman.Create(SYS);

if FileExists(mailman.BIN_PATH()) then begin
   l.Add('"/admin" => "",');
   l.Add('"/admindb" => "",');
   l.Add('"/confirm" => "",');
   l.Add('"/create" => "",');
   l.Add('"/edithtml" => "",');
   l.Add('"/listinfo" => "",');
   l.Add('"/options" => "",');
   l.Add('"/private" => "",');
   l.Add('"/rmlist" => "",');
   l.Add('"/roster" => "",');
   l.Add('"/subscribe" => ""');
end;
   l.Add(')');
   l.Add('');
if mod_auth then begin
l.Add('auth.debug = 2');
l.Add('$HTTP["url"] =~ "^/cgi-bin/" {');
l.Add('auth.backend = "plain"');
l.Add('auth.backend.plain.userfile = "/etc/lighttpd/.lighttpdpassword" ');
l.Add('auth.require = ("/cgi-bin/" => (');
l.Add('     "method"  => "basic",');
l.Add('     "realm"   => "awstats Statistics",');
l.Add('     "require" => "valid-user"');
l.Add('  ))');
l.Add('}');
l.Add('');

l.Add('$HTTP["url"] =~ "^/server-status" {');
l.Add('auth.backend = "plain"');
l.Add('auth.backend.plain.userfile = "/etc/lighttpd/.lighttpdpassword" ');
l.Add('auth.require = ("/server-status" => (');
l.Add('     "method"  => "basic",');
l.Add('     "realm"   => "Lighttpd config - status",');
l.Add('     "require" => "valid-user"');
l.Add('  ))');
l.Add('}');
l.Add('');

l.Add('$HTTP["url"] =~ "^/server-config" {');
l.Add('auth.backend = "plain"');
l.Add('auth.backend.plain.userfile = "/etc/lighttpd/.lighttpdpassword" ');
l.Add('auth.require = ("/server-config" => (');
l.Add('     "method"  => "basic",');
l.Add('     "realm"   => "Lighttpd config - status",');
l.Add('     "require" => "valid-user"');
l.Add('  ))');
l.Add('}');
l.Add('');

l.Add('$HTTP["url"] =~ "^/squid/" {');
l.Add('auth.backend = "plain"');
l.Add('auth.debug = 2');
l.Add('auth.backend.plain.userfile = "/etc/lighttpd/squid-users.passwd" ');
l.Add('auth.require = ("/squid/" => (');
l.Add('     "method"  => "basic",');
l.Add('     "realm"   => "Squid Statistics",');
l.Add('     "require" => "valid-user"');
l.Add('  ))');
l.Add('}');
l.Add('');

l.Add('$HTTP["url"] =~ "^/cluebringer/" {');
l.Add('auth.backend = "plain"');
l.Add('auth.debug = 2');
l.Add('auth.backend.plain.userfile = "/etc/lighttpd/cluebringer.passwd" ');
l.Add('auth.require = ("/cluebringer/" => (');
l.Add('     "method"  => "basic",');
l.Add('     "realm"   => "ClueBringer (Policyd V2) administration",');
l.Add('     "require" => "valid-user"');
l.Add('  ))');
l.Add('}');
l.Add('');


end;



if Not FileExists('/etc/lighttpd/lighttpd.conf') then begin
   forceDirectories('/etc/lighttpd');
   logs.Debuglogs('Starting......: lighttpd: (config) save /etc/lighttpd/lighttpd.conf');
   logs.WriteToFile(l.Text,'/etc/lighttpd/lighttpd.conf');
end;
result:=l.text;
l.free;
end;
//##############################################################################
procedure tlighttpd.LOAD_MODULES();
var
   tmpstr:string;
   l:tstringlist;
   RegExpr:TRegExpr;
   i:integer;
begin
if not FileExists(LIGHTTPD_BIN_PATH()) then exit;
if lighttpd_modules.Count>1 then exit;
tmpstr:=logs.FILE_TEMP();
fpsystem(LIGHTTPD_BIN_PATH() +' -V >'+tmpstr+' 2>&1');

RegExpr:=TRegExpr.Create;
l:=Tstringlist.Create;
RegExpr.Expression:='\+\s+(.+?)\s+support';
try
   l.LoadFromFile(tmpstr);
   for i:=0 to l.Count-1 do begin
          if RegExpr.Exec(l.Strings[i]) then begin
             logs.Debuglogs('Starting......: lighttpd: Available module "'+ RegExpr.Match[1]+'"');
             lighttpd_modules.Add(RegExpr.Match[1]);
          end;
   end;
finally
   logs.DeleteFile(tmpstr);
   l.free;
end;
 RegExpr.free;
end;
//##############################################################################
function tlighttpd.isModule(modulename:string):boolean;
var
i:integer;
libdir:string;
begin
  LOAD_MODULES();
  libdir:=lighttpd_modules_path();
  result:=false;
  for i:=0 to lighttpd_modules.Count-1 do begin
      if LowerCase(modulename)=LowerCase(lighttpd_modules.Strings[i]) then begin
         result:=true;
         exit;
      end;
  end;

if FileExists(libdir+'/'+modulename+'.so') then exit(true);

end;
//##############################################################################


function Tlighttpd.APACHE_ARTICA_ENABLED():string;
var
   s:string;
begin

if not FileExists(SYS.LOCATE_APACHE_INITD_PATH()) then exit('0');

if not FileExists(LIGHTTPD_BIN_PATH()) then begin
   result:='1';
   exit;
end;
s:=SYS.GET_INFO('ApacheArticaEnabled');
if length(s)=0 then exit('0');
exit(s);
end;
//##############################################################################
function Tlighttpd.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
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

