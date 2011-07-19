unit global_conf;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,strutils,IniFiles,RegExpr in 'RegExpr.pas',oldlinux,libc,logs,dateutils,zsystem,uHashList,ldapsend,Geoip;
type
  TStringDynArray = array of string;
  
  type

  { MyConf }

  MyConf=class


private
       GLOBAL_INI:TIniFile;


       procedure killfile(path:string);
       function GetIPAddressOfInterface( if_name:ansistring):ansistring;
        procedure ShowScreen(line:string);
       LOGS:tlogs;
       notdebug2:boolean;


public

      function ARTICA_AutomaticConfig():boolean;
      function CGI_ALL_APPLIS_INSTALLED():string;
      
      procedure THREAD_COMMAND_SET(zcommands:string);
      function CheckInterface( if_name:string):boolean;
      function GetIPInterface( if_name:string):string;
       
      function  ReadFileIntoString(path:string):string;
      procedure set_INFOS(key:string;val:string);
      function  get_INFOS(key:string):string;
      procedure set_LDAP(key:string;val:string);
      function  get_LDAP(key:string):string;
      procedure ExecProcess(commandline:string);
      procedure MonShell(cmd:string;sh:boolean);
      
      function  KAS_INIT():string;
      function  KAS_GET_VALUE(key:string):string;
      procedure KAS_WRITE_VALUE(key:string;datas:string);
      function  KAS_STATUS():string;
      function  KAS_VERSION():string;
      procedure KAS_DELETE_VALUE(key:string);
      function  KAS_APPLY_RULES(path:string):boolean;
      FUNCTION  KAS_AP_SPF_PID():string;
      FUNCTION  KAS_AP_PROCESS_SERVER_PID():string;
      FUNCTION  KAS_LICENCE_PID():string;
      FUNCTION  KAS_THTTPD_PID():string;
      
      
      function LDAP_GET_CONF_PATH():string;
      function LDAP_READ_VALUE_KEY( key:string):string;
      function LDAP_READ_ADMIN_NAME():string;
      function LDAP_WRITE_VALUE_KEY( key:string;value:string):string;
      function LDAP_GET_INITD():string;
      function LDAP_GET_SCHEMA_PATH():string;
      function LDAP_READ_SCHEMA_POSTFIX_PATH():string;
      function LDAP_ADDSCHEMA( schema:string):string;
      function LDAP_VERSION():string;
      function LDAP_GET_DAEMON_USERNAME():string;
      function LDAP_USE_SUSE_SCHEMA():boolean;
      function LDAP_PID():string;
      
      function AWSTATS_GET_VALUE(key:string):string;
      function AWSTATS_SET_VALUE(key:string;value:string):string;
      function AWSTATS_SET_PLUGIN(value:string):string;
      function AWSTATS_MAILLOG_CONVERT_PATH_SOURCE():string;
      function AWSTATS_PATH():string;
      function AWSTATS_VERSION():string;
      procedure AWSTATS_GENERATE();
      
      function  CYRUS_VERSION():string;
      function  CYRUS_IMAPD_BIN_PATH():string;
      procedure CYRUS_SET_V2(val:string);
      function  CYRUS_GET_V2():string;

      function  RRDTOOL_SecondsBetween(longdate:string):string;
      function  RRDTOOL_VERSION():string;
      function  RRDTOOL_TIMESTAMP(longdate:string):string;
      function  RRDTOOL_LOAD_AVERAGE():string;
      function  RRDTOOL_LOAD_CPU(rddtool:boolean):string;
      
      function RRDTOOL_STAT_LOAD_AVERAGE_DATABASE_PATH():string;
      function RRDTOOL_STAT_LOAD_CPU_DATABASE_PATH():string;
      function RRDTOOL_STAT_LOAD_MEMORY_DATABASE_PATH():string;
      function RRDTOOL_STAT_POSTFIX_MAILS_SENT_DATABASE_PATH():string;

      procedure RDDTOOL_POSTFIX_MAILS_SENT_STATISTICS();
      procedure RDDTOOL_POSTFIX_MAILS_CREATE_DATABASE();
      
      procedure RDDTOOL_LOAD_AVERAGE_GENERATE();
      procedure RDDTOOL_LOAD_CPU_GENERATE();
      procedure RDDTOOL_LOAD_MEMORY_GENERATE();
      function  RRDTOOL_GRAPH_HEIGHT():string;
      function  RRDTOOL_GRAPH_WIDTH():string;
      procedure RDDTOOL_LOAD_CPU_TOOLTIP_GENERATE();
      function  RRDTOOL_LOAD_MEMORY(rddtool:boolean):string;
      procedure RDDTOOL_POSTFIX_MAILS_SENT_GENERATE();
      
      function  DSPAM_GET_PARAM(key:string):string;
      procedure DSPAM_EDIT_PARAM(key:string;value:string);
      function  DSPAM_IS_PARAM_EXISTS(key:string;value:string):boolean;
      procedure DSPAM_EDIT_PARAM_MULTI(key:string;value:string);
      procedure DSPAM_REMOVE_PARAM(key:string);
      function  DSPAM_BIN_PATH():string;
      
      function FETCHMAIL_VERSION():string;
      function FETCHMAIL_STATUS():string;
      function FETCHMAIL_DAEMON_POOL():string;
      function FETCHMAIL_DAEMON_POSTMASTER():string;
      function FETCHMAIL_BIN_PATH():string;
      function FETCHMAIL_START_DAEMON():boolean;
      function FETCHMAIL_PID():string;
      function RENATTACH_VERSION():string;
      function FETCHMAIL_SERVER_PARAMETERS(param:string):string;
      function FETCHMAIL_COUNT_SERVER():integer;
      function FETCHMAIL_DAEMON_STOP():string;
      function get_repositories_librrds_perl():boolean;
      
      function  CRON_CREATE_SCHEDULE(ProgrammedTime:string;Croncommand:string;name:string):boolean;
      function  CRON_PID():string;
      function  CROND_INIT_PATH():string;
      
      function CERTIFICATE_PASS(path:string):string;
      function CERTIFICATE_PATH(path:string):string;
      function CERTIFICATE_CA_FILENAME(path:string):string;
      function CERTIFICATE_KEY_FILENAME(path:string):string;
      function CERTIFICATE_CERT_FILENAME(path:string):string;
      
      function PROCMAIL_VERSION():string;
      function PROCMAIL_INSTALLED():boolean;
      function PROCMAIL_LOGS_PATH():string;
      function PROCMAIL_USER():string;
      function PROCMAIL_QUARANTINE_PATH():string;
      function PROCMAIL_QUARANTINE_SIZE(username:string):string;
      function PROCMAIL_QUARANTINE_USER_FILE_NUMBER(username:string):string;
      function PROCMAIL_READ_QUARANTINE(fromFileNumber:integer;tofilenumber:integer;username:string):TstringList;
      function PROCMAIL_READ_QUARANTINE_FILE(file_to_read:string):string;
      
      function  DNSMASQ_SET_VALUE(key:string;value:string):string;
      function  DNSMASQ_GET_VALUE(key:string):string;
      function  DNSMASQ_BIN_PATH():string;
      function  DNSMASQ_VERSION:string;
      procedure DNSMASQ_START_DAEMON();
      procedure DNSMASQ_STOP_DAEMON();
      function  DNSMASQ_PID():string;

      function OPENSSL_TOOL_PATH():string;
      function ROUNDCUBE_VERSION():string;
      
      function GetAllApplisInstalled():string;

      function  get_repositories_Checked():boolean;
      function  POSTFIX_PID_PATH():string;
      function  POSTFIX_PID():string;
      function  POSTFIX_STATUS():string;
      function  POSTFIX_VERSION():string;
      function  POSTFIX_HEADERS_CHECKS():string;
      procedure POSTFIX_CHECK_POSTMAP();
      function  POSTFIX_QUEUE_FILE_NUMBER(directory_name:string):string;
      function  POSFTIX_READ_QUEUE_FILE_LIST(fromFileNumber:integer;tofilenumber:integer;queuepath:string;include_source:boolean):TstringList;
      function  POSTFIX_READ_QUEUE_MESSAGE(MessageID:string):string;
      function  POSFTIX_CACHE_QUEUE_FILE_LIST(QueueName:string):boolean;
      function  POSFTIX_CACHE_QUEUE():boolean;
      function  POSFTIX_DELETE_FILE_FROM_CACHE(MessageID:string):boolean;
      procedure POSTFIX_REPLICATE_MAIN_CF(mainfile:string);
      procedure POSTFIX_RELOAD_DAEMON();
      procedure POSTFIX_RESTART_DAEMON();
      function  POSTFIX_EXPORT_LOGS():boolean;
      function  POSTFIX_LAST_ERRORS():string;
      function  POSTFIX_LDAP_COMPLIANCE():boolean;
      function  ExecPipe(commandline:string):string;

      function APACHE_GET_INITD_PATH:string;
      function APACHE2_PORTS_CONF():string;
      function APACHE2_SITES_AVAILABLE():string;
      function APACHE_ENABLE_ARTICA_SITE(port:string):boolean;
      function APACHE2_DirectoryAddOptions(Change:boolean;WichOption:string):string;
      function APACHE_PID():string;
      
      function QUEUEGRAPH_TEMP_PATH():string;
      procedure QUEUEGRAPH_IMAGES();
      
      function  AVESERVER_GET_VALUE(KEY:string;VALUE:string):string;
      function  AVESERVER_GET_PID():string;
      function  AVESERVER_GET_VERSION():string;
      function  AVESERVER_GET_LICENCE():string;
      function  AVESERVER_STATUS():string;
      function  AVESERVER_PATTERN_DATE():string;
      function  AVESERVER_GET_KEEPUP2DATE_LOGS_PATH():string;
      function  AVESERVER_SET_VALUE(KEY:string;VALUE:string;DATA:string):string;
      function  AVESERVER_GET_DAEMON_PORT():string;
      function  AVESERVER_GET_TEMPLATE_DATAS(family:string;ztype:string):string;
      procedure AVESERVER_REPLICATE_TEMPLATES();
      procedure AVESERVER_REPLICATE_kav4mailservers(mainfile:string);
      function  AVESERVER_GET_LOGS_PATH():string;
      
      
      function get_repositories_openssl():boolean;
      
      function  Cyrus_get_sasl_pwcheck_method:string;
      procedure Cyrus_set_sasl_pwcheck_method(val:string);
      function  Cyrus_get_servername:string;
      procedure Cyrus_set_value(info:string;val:string);
      function  Cyrus_get_admins:string;
      function  Cyrus_get_unixhierarchysep:string;
      function  Cyrus_get_virtdomain:string;
      function  Cyrus_get_adminpassword:string;
      function  Cyrus_get_admin_name():string;
      procedure Cyrus_set_admin_name(val:string);
      procedure Cyrus_set_adminpassword(val:string);
      function  Cyrus_get_lmtpsocket:string;
      function  Cyrus_get_value(value:string):string;
      function  CYRUS_REPLICATION_MINUTES():integer;
      function  CYRUS_LAST_REPLIC_TIME():integer;
      procedure CYRUS_RESET_REPLIC_TIME();
      function  CYRUS_GET_INITD_PATH:string;
      function  CYRUS_STATUS():string;
      function  CYRUS_DELIVER_BIN_PATH():string;
      function  CYRUS_IMAPD_CONF_GET_INFOS(value:string):string;
      
      function  KAV_LAST_REPLIC_TIME():integer;
      procedure KAV_RESET_REPLIC_TIME();
      function  KAV_REPLICATION_MINUTES():integer;
      
      procedure KEEPUP2DATE_RESET_REPLIC_TIME();
      function  KEEPUP2DATE_LAST_REPLIC_TIME():integer;
      function  KEEPUP2DATE_REPLICATION_MINUTES():integer;

      function  SYSTEM_GMT_SECONDS():string;
      function  SYSTEM_GET_ALL_LOCAL_IP():string;
      function  SYSTEM_GET_LOCAL_IP(ifname:string):string;
      function  SYSTEM_DAEMONS_STATUS():TstringList;
      function  SYSTEM_DAEMONS_STOP_START(APPS:string;mode:string;return_string:boolean):string;
      function  SYSTEM_START_ARTICA_DAEMON():boolean;
      function  SYSTEM_PROCESS_EXISTS(processname:string):boolean;
      function  SYSTEM_KERNEL_VERSION():string;
      function  SYSTEM_LIBC_VERSION():string;
      function  SYSTEM_LD_SO_CONF_ADD(path:string):string;
      function  SYSTEM_CRON_TASKS():TstringList;
      function  SYSTEM_USER_LIST():string;
      function  SYSTEM_CRON_REPLIC_CONFIGS():string;
      function  SYSTEM_ADD_NAMESERVER(nameserver:string):boolean;
      function  SYSTEM_NETWORK_INITD():string;
      function  SYSTEM_NETWORK_LIST_NICS():string;
      function  SYSTEM_NETWORK_INFO_NIC_DEBIAN(nicname:string):string;
      function  SYSTEM_NETWORK_INFO_NIC_REDHAT(nicname:string):string;
      function  SYSTEM_NETWORK_INFO_NIC(nicname:string):string;
      function  SYSTEM_NETWORK_IFCONFIG():string;
      function  SYSTEM_NETWORK_IFCONFIG_ETH(ETH:string):string;
      function  SYSTEM_NETWORK_RECONFIGURE():string;
      function  SYSTEM_PROCESS_PS():string;
      function  SYSTEM_PROCESS_INFO(PID:string):string;
      function  SYSTEM_ALL_IPS():string;
      function  SYSTEM_PROCESS_EXIST(pid:string):boolean;
      function  SYSTEM_PROCESS_MEMORY(PID:string):integer;
      function  SYSTEM_GET_PID(pidPath:string):string;
      function  SYSTEM_MAKE_PATH():string;
      function  SYSTEM_GCC_PATH():string;
      function  SYSTEM_ENV_PATHS():string;
      procedure SYSTEM_ENV_PATH_SET(path:string);
      function  SYSTEM_VERIFY_CRON_TASKS():string;
      function  SYSTEM_GET_SYS_DATE():string;
      function  SYSTEM_GET_HARD_DATE():string;
      
      function  SYSTEM_GET_HTTP_PROXY:string;
      function  SYSTEM_REMOVE_HTTP_PROXY:string;
      procedure SYSTEM_SET_HTTP_PROXY(proxy_string:string);

      function WGET_DOWNLOAD_FILE(uri:string;file_path:string):boolean;
      function BOA_SET_CONFIG():boolean;
      function BOA_DAEMON_GET_PID():string;
      
      function GEOIP_VERSION():string;

      function  SASLAUTHD_PATH_GET():string;
      function  SASLAUTHD_VALUE_GET(key:string):string;
      function  SASLAUTHD_TEST_INITD():boolean;
      



      function  postfix_get_virtual_mailboxes_maps():string;
      
      function  YOREL_RECONFIGURE(database_path:string):string;
      
      function get_MYSQL_INSTALLED():boolean;
      function get_POSTFIX_DATABASE():string;
      function get_POSTFIX_HASH_FOLDER():string;
      
      
      function APACHE2_VERIFY_SETTINGS():boolean;
      function get_www_root():string;
      function get_www_userGroup():string;
      function get_httpd_conf():string;
      
      function get_MANAGE_MAILBOXES():string;
      function get_MANAGE_MAILBOX_SERVER():string;

      function get_INSTALL_PATH():string;
      function get_DISTRI():string;
      function get_UPDATE_TOOLS():string;

      procedure set_FileStripDiezes(filepath:string);
      function set_repositories_checked(val:boolean):string;
      procedure set_MYSQL_INSTALLED(val:boolean);
      function set_POSTFIX_DATABASE(val:string):string;
      function set_POSTFIX_HASH_FOLDER(val:string):string;
      
      function set_MANAGE_MAILBOXES(val:string):string;
      procedure set_MANAGE_MAILBOX_SERVER(val:string);
      function get_MANAGE_SASL_TLS():boolean;
      procedure set_MANAGE_SASL_TLS(val:boolean);

      function set_INSTALL_PATH(val:string):string;
      function set_DISTRI(val:string):string;
      function set_UPDATE_TOOLS(val:string):string;
      
      procedure set_LINUX_DISTRI(val:string);
      
      
      function get_LINUX_DISTRI():string;
      function get_LINUX_MAILLOG_PATH():string;
      function get_LINUX_INET_INTERFACES():string;
      function get_LINUX_DOMAIN_NAME():string;
      function get_SELINUX_ENABLED():boolean;
      procedure set_SELINUX_DISABLED();
      
      function LINUX_GET_HOSTNAME:string;
      function LINUX_DISTRIBUTION():string;
      function LINUX_CONFIG_INFOS():string;
      function LINUX_APPLICATION_INFOS(inikey:string):string;
      function LINUX_INSTALL_INFOS(inikey:string):string;
      function LINUX_CONFIG_PATH():string;
      function LINUX_REPOSITORIES_INFOS(inikey:string):string;
      function LINUX_LDAP_INFOS(inikey:string):string;

      


      function LDAP_TESTS():string;


      function MYSQL_ACTION_TESTS_ADMIN():boolean;
      function MYSQL_ACTION_CREATE_ADMIN(username:string;password:string):boolean;
      function MYSQL_ACTION_IF_DATABASE_EXISTS(database_name:string):boolean;
      function MYSQL_ACTION_IMPORT_DATABASE(filenname:string;database:string):boolean;
      function MYSQL_ACTION_COUNT_TABLES(database_name:string):integer;
      function MYSQL_ACTION_QUERY(sql:string):boolean;
      function MYSQL_PASSWORD():string;
      function MYSQL_ROOT():string;
      function MYSQL_ENABLED:boolean;
      function MYSQL_SERVER():string;
      function MYSQL_VERSION:string;
      function MYSQL_BIN_PATH:string;
      function MYSQL_INIT_PATH:string;
      function MYSQL_MYCNF_PATH:string;
      function MYSQL_PID_PATH():string;
      function MYSQL_STATUS():string;

      function set_ARTICA_PHP_PATH(val:string):string;
      function set_ARTICA_DAEMON_LOG_MaxSizeLimit(val:integer):integer;
      function get_ARTICA_LISTEN_IP():string;
      function get_ARTICA_LOCAL_PORT():integer;
      procedure SET_ARTICA_LOCAL_SECOND_PORT(val:integer);
      function get_ARTICA_LOCAL_SECOND_PORT():integer;
      function ARTICA_MYSQL_INFOS(val:string):string;
      function ARTICA_MYSQL_SET_INFOS(val:string;value:string):boolean;
      function ARTICA_POLICY_GET_PID():string;
      
      
      function get_MAILGRAPH_TMP_PATH():string;
      function get_MAILGRAPH_BIN():string;
      function get_MAILGRAPH_RRD():string;
      procedure set_MAILGRAPH_RRD(rrd_path:string);
      procedure set_MAILGRAPH_RRD_VIRUS(rrd_path:string);
      function get_MAILGRAPH_RRD_VIRUS():string;
      function MAILGRAPH_VERSION():string;
      function MAILGRAPGH_STATUS():string;
      function MAILGRAPGH_PID_PATH():string;
      
      function  get_ARTICA_PHP_PATH():string;
      function  get_ARTICA_DAEMON_LOG_MaxSizeLimit():integer;
      function  get_DEBUG_DAEMON():boolean;
      
      
      function  ARTICA_DAEMON_GET_PID():string;
      function  ARTICA_FILTER_GET_PID():string;
      function  ARTICA_FILTER_GET_ALL_PIDS():string;
      procedure ARTICA_FILTER_WATCHDOG();
      function  ARTICA_SEND_QUEUE_PATH():string;
      function  ARTICA_SEND_SUBQUEUE_NUMBER(QueueNumber:string):integer;
      function  ARTICA_SEND_MAX_SUBQUEUE_NUMBER:integer;
      function  ARTICA_FILTER_CHECK_PERMISSIONS():string;

      function  ARTICA_FILTER_QUEUEPATH():string;
      procedure ARTICA_FILTER_CLEAN_QUEUE();
      function  ARTICA_SQL_QUEUE_NUMBER():integer;
      function  ARTICA_SQL_PID():string;
      function  ARTICA_VERSION():string;
      
      function EMAILRELAY_PID():string;
      function EMAILRELAY_VERSION():string;
      

      function ARTICA_FILTER_PID():string;
      function ARTICA_SEND_PID(QueueNumber:String):string;
      function ARTICA_SEND_QUEUE_NUMBER():integer;

      function  get_kaspersky_mailserver_smtpscanner_logs_path():string;
      function  ExecStream(commandline:string;ShowOut:boolean):TMemoryStream;
      function  GetMonthNumber(MonthName:string):integer;
      function  Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
      procedure StripDiezes(filepath:string);
      function  PHP5_INI_PATH:string;
      procedure PHP5_ENABLE_GD_LIBRARY();
      function  PHP5_INI_SET_EXTENSION(librari:string):string;
      
      
      function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
      function COMMANDLINE_EXTRACT_PARAMETERS(pattern:string):string;



      debug:boolean;
      echo_local:boolean;
      ArrayList:TStringList;
      constructor Create();
      destructor Destroy;virtual;

END;

implementation

constructor MyConf.Create();
var myFile:TextFile;
begin
       LOGS:=tlogs.Create;
       ArrayList:=TStringList.Create;
end;

destructor MyConf.Destroy;
begin
  LOGS.Free;
  inherited Destroy;
end;

function myconf.LDAP_TESTS():string;
var  ldap:TLDAPSend;
l:TStringList;
i:integer;
begin
     ldap :=  TLDAPSend.Create;
     ldap.TargetHost := '127.0.0.1';
     ldap.TargetPort := '389';
     ldap.UserName := 'admin';
     ldap.Password := '180872';
     ldap.Version := 3;
     ldap.FullSSL := false;
     if ldap.Login then begin;
     writeln('logged');
        ldap.Bind;
        writeln('binded');
     end;
     
    l:=TstringList.Create;
    l.Add('displayname');
    l.Add('description');
    l.Add('givenName');
    l.Add('*');
    ldap.Search('dc=nodomain', False, '(objectclass=*)', l);
    //writeln(LDAPResultdump(ldap.SearchResult));
     showScreen('count=' + IntToStr(ldap.SearchResult.Count));
     for i:=0 to ldap.SearchResult.Count -1 do begin
       showscreen( ldap.SearchResult.Items[i].ObjectName);
       showscreen( 'attributes:=' +IntToStr(ldap.SearchResult.Items[i].Attributes.Count));
     
     end;
     writeln('logout');
     
     ldap.Logout;
     ldap.Free;

end;

//##############################################################################
function myconf.SYSTEM_GCC_PATH():string;
 begin
     if FileExists('/usr/bin/gcc') then exit('/usr/bin/gcc');
 end;
//##############################################################################
function myconf.SYSTEM_MAKE_PATH():string;
 begin
     if FileExists('/usr/bin/make') then exit('/usr/bin/make');
 end;
//##############################################################################
function  myconf.SYSTEM_GET_HTTP_PROXY:string;
var
   l:TStringList;
   res:string;
   i:integer;
   RegExpr:TRegExpr;

 begin
  if not FileExists('/etc/environment') then begin
     writeln('Unable to find /etc/environment');
     exit;
  end;
  
  
  l:=TStringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='(http_proxy|HTTP_PROXY)=(.+)';
  
  l.LoadFromFile('/etc/environment');
  for i:=0 to l.Count -1 do begin
      if RegExpr.Exec(l.Strings[i]) then result:=RegExpr.Match[2];
  
  end;
 l.FRee;
 RegExpr.free;

end;
//##############################################################################
function  myconf.SYSTEM_REMOVE_HTTP_PROXY:string;
var
   l:TStringList;
   res:string;
   i:integer;
   RegExpr:TRegExpr;

 begin
  if not FileExists('/etc/environment') then begin
     writeln('Unable to find /etc/environment');
     exit;
  end;


  l:=TStringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='(http_proxy|HTTP_PROXY)=(.+)';

  l.LoadFromFile('/etc/environment');
  for i:=0 to l.Count -1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
          l.Delete(i);
          break;
      end;

  end;
  l.SaveToFile('/etc/environment');
  l.free;
  RegExpr.free;

end;
//##############################################################################

function myconf.WGET_DOWNLOAD_FILE(uri:string;file_path:string):boolean;
var
   l:TStringList;
   res:string;
   i:integer;
   RegExpr:TRegExpr;
   ProxyString:string;
   ProxyCommand:string;
   ProxyUser:string;
   ProxyPassword:string;
   ProxyName:string;
   commandline:string;
   D:boolean;
 begin
   D:=COMMANDLINE_PARAMETERS('debug');
 
   RegExpr:=TRegExpr.Create;
   ProxyString:=SYSTEM_GET_HTTP_PROXY();
   ProxyString:=AnsiReplaceStr(ProxyString,'"','');
   ProxyString:=AnsiReplaceStr(ProxyString,'http://','');
   
   
   
   if length(ProxyString)>0 then begin

       RegExpr.Expression:='(.+?):(.+?)@(.+)';
       if RegExpr.Exec(ProxyString) then begin
            ProxyUser:=RegExpr.Match[1];
            ProxyPassword:=RegExpr.Match[2];
            ProxyName:=RegExpr.Match[3];
       end;
       RegExpr.Expression:='(.+?)@(.+)';
       if RegExpr.Exec(ProxyString) then begin
           ProxyUser:=RegExpr.Match[1];
           ProxyName:=RegExpr.Match[3];
       end;
       
       if length(ProxyName)=0 then ProxyName:=ProxyString;
     writeln('wget (using proxy) ' + ProxyName);
     ProxyCommand:='--proxy=on ';
     if length(ProxyUser)>0 then ProxyCommand:=ProxyCommand + ' --proxy-user=' + ProxyUser;
     if length(ProxyPassword)>0 then ProxyCommand:=ProxyCommand + ' --proxy-passwd=' + ProxyPassword;
     commandline:=ExtractFilePath(ParamStr(0)) + 'artica-get  '+ uri + ' ' + ProxyCommand + ' -q --output-document=' + file_path;
     
   end;


if D then writeln(commandline);
Shell('/usr/bin/wget  '+ uri + '  -q --output-document=' + file_path);

end;
//##############################################################################


procedure  myconf.SYSTEM_SET_HTTP_PROXY(proxy_string:string);
var
   l:TStringList;
   res:string;
   i:integer;
   RegExpr:TRegExpr;

 begin
  if not FileExists('/etc/environment') then begin
     writeln('Unable to find /etc/environment');
     exit;
  end;
 SYSTEM_REMOVE_HTTP_PROXY();

  l:=TStringList.Create;
  l.LoadFromFile('/etc/environment');
  l.Add('http_proxy="'+ proxy_string + '"');
  l.SaveToFile('/etc/environment');
  writeln('export http_proxy="'+ proxy_string + '" --> done');
  shell('export http_proxy="'+ proxy_string + '"');
  writeln('env http_proxy='+ proxy_string + '" --> done');
  shell('env http_proxy='+ proxy_string);
  l.free;
end;
//##############################################################################


FUNCTION myconf.SYSTEM_ENV_PATHS():string;
var
   Path:string;
   res:string;

 begin
     if FileExists('/usr/bin/printenv') then Path:='/usr/bin/printenv';
     if length(Path)=0 then exit;
     res:=ExecPipe(Path + ' PATH');
     result:=res;
end;
//##############################################################################
procedure Myconf.SYSTEM_ENV_PATH_SET(path:string);
var
 Table:TStringDynArray;
 datas:string;
 i:integer;
 MusTAdd:boolean;
 newpath:string;
 LOGS:Tlogs;
begin
     LOGS:=Tlogs.Create;
     MusTAdd:=True;
     datas:=SYSTEM_ENV_PATHS();
     if length(datas)>1 then begin
        Table:=Explode(':',SYSTEM_ENV_PATHS());
        For i:=0 to Length(Table)-1 do begin
                 LOGS.logs('SYSTEM_ENV_PATH_SET -> ' + path + ' already exists in env');
                if Table[i]=path then exit;
        end;
     end;

    LOGS.logs('SYSTEM_ENV_PATH_SET -> ' + path);
    newpath:=SYSTEM_ENV_PATHS() + ':' + path;
    Shell('/usr/bin/env PATH=' + newpath + ' >/tmp/env.tmp');

end;
//##############################################################################


function myconf.SYSTEM_VERIFY_CRON_TASKS();
var
   l:Tstringlist;

begin
  l:=TStringList.Create;

  if Not FileExists('/etc/cron.d/artica-cron-quarantine') then begin
      writeln('Create quarantine maintenance task in background;...');
      l.Add('#{artica-cron-quarantine_text}');
      l.Add('0 3 * * *  root ' +get_ARTICA_PHP_PATH() +'/bin/artica-quarantine -maintenance >/dev/null');
      l.SaveToFile('/etc/cron.d/artica-cron-quarantine');
  end;
  
l.Free;
end;



function myconf.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:=false;
 if ParamCount>1 then begin
     for i:=2 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:=FoundWhatPattern;
   if RegExpr.Exec(s) then begin
      RegExpr.Free;
      result:=True;
   end;
     
     
end;
//##############################################################################
function myconf.COMMANDLINE_EXTRACT_PARAMETERS(pattern:string):string;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:='';
 if ParamCount>1 then begin
     for i:=2 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;

         RegExpr:=TRegExpr.Create;
         RegExpr.Expression:=pattern;
         RegExpr.Exec(s);
         Result:=RegExpr.Match[1];
         RegExpr.Free;
end;
//##############################################################################
procedure myconf.DSPAM_EDIT_PARAM(key:string;value:string);
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;
   l:TstringList;
   D:boolean;
begin
D:=COMMANDLINE_PARAMETERS('debug');
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TstringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');
s:=DSPAM_GET_PARAM(key);
   if length(s)=0 then begin
        if D then writeln('DSPAM_EDIT_PARAM:: Add the value "'+value+'"');
        l.Add(key + ' ' + value);
        l.SaveToFile('/etc/dspam/dspam.conf');
        l.free;
        exit();
   end;
   
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       l.Strings[i]:=key + ' ' + value;
       l.SaveToFile('/etc/dspam/dspam.conf');
       break;
   end;
end;
 RegExpr.Free;
 l.Free;

end;
//##############################################################################
function myconf.DSPAM_BIN_PATH():string;
begin
if FileExists('/usr/local/bin/dspam') then exit('/usr/local/bin/dspam');
if FileExists('/usr/bin/dspam') then exit('/usr/bin/dspam');
end;


//##############################################################################
procedure myconf.DSPAM_EDIT_PARAM_MULTI(key:string;value:string);
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;
   l:TstringList;
   D:boolean;
begin
D:=COMMANDLINE_PARAMETERS('debug');
if not FileExists('/etc/dspam/dspam.conf') then exit;
if DSPAM_IS_PARAM_EXISTS(key,value) then exit;
l:=TstringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');



   if length(s)=0 then begin
        if D then writeln('DSPAM_EDIT_PARAM:: Add the value "'+value+'"');
        l.Add(key + ' ' + value);
        l.SaveToFile('/etc/dspam/dspam.conf');
        l.free;
        exit();
   end;

 RegExpr.Free;
 l.Free;

end;

//##############################################################################
procedure myconf.DSPAM_REMOVE_PARAM(key:string);
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;
   l:TstringList;
   D:boolean;
begin
D:=COMMANDLINE_PARAMETERS('debug');
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TstringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');


RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      if D then writeln('remove line:',i);
       l.Delete(i);
       l.SaveToFile('/etc/dspam/dspam.conf');
       RegExpr.Free;
       l.Free;
       DSPAM_REMOVE_PARAM(key);
       exit;
   end;
end;


 l.SaveToFile('/etc/dspam/dspam.conf');
 RegExpr.Free;
 l.Free;

end;

//##############################################################################
function myconf.DSPAM_GET_PARAM(key:string):string;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;
   l:TStringList;

begin
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TStringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       result:=trim(RegExpr.Match[1]);
       break;
   end;

end;
 RegExpr.Free;
 l.Free;


end;
//##############################################################################
function myconf.DSPAM_IS_PARAM_EXISTS(key:string;value:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;
   l:TStringList;

begin
result:=false;
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TStringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       if value=RegExpr.Match[1] then begin
          result:=true;
          break;
       end;
   end;

end;
 RegExpr.Free;
 l.Free;


end;
//##############################################################################


function MyConf.get_SELINUX_ENABLED():boolean;
var value,filedatas:string;
RegExpr:TRegExpr;
begin
result:=false;
if not FileExists('/etc/selinux/config') then exit(False);
 filedatas:=ReadFileIntoString('/etc/selinux/config');
  RegExpr:=TRegExpr.create;
  RegExpr.Expression:='SELINUX=(enforcing|permissive|disabled)';
  if RegExpr.Exec(filedatas) then begin
         if RegExpr.Match[1]='permissive' then result:=True;
         if RegExpr.Match[1]='enforcing' then result:=True;
         if RegExpr.Match[1]='disabled' then result:=false;
       end
       else begin
          result:=False;
  end;
 end;
//##############################################################################
 
function MyConf.APACHE_ENABLE_ARTICA_SITE(port:string):boolean;
var RegExpr:TRegExpr;
     php_ini,apache_init:string;
     www:string;
     artica_path:string;
     apache2_sites_available_path,targetfile:string;
     logs:Tlogs;
     FileData:TStringList;
     i:integer;
     Found,Zdebug:boolean;
     

begin
   logs:=Tlogs.create;
   if length(port)=0 then begin
        logs.logsInstall('APACHE_ENABLE_PORT:: WARNING port parameters is not set');
        writeln('APACHE_ENABLE_PORT:: WARNING port parameters is not set');
        exit;
   end;
        apache_init:=APACHE_GET_INITD_PATH();
        
        if ParamStr(1)='-vhost' then Zdebug:=True;
        
        
        artica_path:=get_ARTICA_PHP_PATH();
        apache2_sites_available_path:=APACHE2_SITES_AVAILABLE();
        targetfile:=apache2_sites_available_path + '/VIRT_HOST_ARTICA-' + port + '.conf';
        
        if Zdebug then begin
           writeln('Artica path.........................:',artica_path);
           writeln('sites_available path................:',apache2_sites_available);
           writeln('New configuration file..............:',targetfile);
        end;
        
        
    FileData:=TStringList.Create;
    FileData.Add('Listen' + chr(9) + port);
    FileData.Add('NameVirtualHost *:' + port);
    FileData.Add('<VirtualHost *:' + port + '>');
    FileData.Add('DocumentRoot	' + get_www_root() + '/artica-postfix');
    FileData.Add('</virtualHost>');
    FileData.SaveToFile(targetfile);

    if FileExists('/usr/sbin/a2ensite') then begin
       Shell('/usr/sbin/a2ensite + VIRT_HOST_ARTICA-' + port + '.conf');
    end;
    // disbale a2dissite

    Shell(apache_init + ' restart');


end;
 
 
//#############################################################################
function Myconf.APACHE2_SITES_AVAILABLE():string;
var apache2_sites_available:string;
    f,Zdebug:boolean;
begin
 f:=true;
 
 if ParamStr(1)='-vhost' then Zdebug:=True;
 
 apache2_sites_available:=LINUX_APPLICATION_INFOS('apache2_sites_available');
 if length(apache2_sites_available)=0 then f:=false;
 if not DirectoryExists(apache2_sites_available) then f:=false;
 if f=false then begin

    if DirectoryExists('/etc/apache2/sites-available') then exit('/etc/apache2/sites-available');

    end else begin
    exit(apache2_sites_available);

 end;

end;
//#############################################################################
function Myconf.APACHE2_PORTS_CONF():string;
var apache2_port_conf:string;
    f:boolean;
begin
 f:=true;
 apache2_port_conf:=LINUX_APPLICATION_INFOS('apache2_port_conf');
 if length(apache2_port_conf)=0 then begin
      if fileExists('/etc/apache2/ports.conf') then exit('/etc/apache2/ports.conf');
 end;
end;
//#############################################################################
procedure Myconf.PHP5_ENABLE_GD_LIBRARY();
var
     RegExpr:TRegExpr;
     php_ini,apache_init:string;
     logs:Tlogs;
     FileData:TStringList;
     i:integer;
     Found:boolean;
begin
logs:=Tlogs.Create();
php_ini:=PHP5_INI_PATH();
apache_init:=APACHE_GET_INITD_PATH();
Found:=false;
if length(php_ini)=0 then begin
  logs.logsInstall('PHP5_ENABLE_GD_LIBRARY:: WARNING unable to locate php.ini file !!!');
  writeln('WARNING unable to locate php.ini file !!!');
  exit;
end;

if length(apache_init)=0 then begin
  logs.logsInstall('PHP5_ENABLE_GD_LIBRARY:: WARNING unable to locate apache init !!!');
  writeln('WARNING unable to locate apache init !!!');
  exit;
end;


    if debug then writeln('Enable GD Library for PHP');
    FileData:=TStringList.Create;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^extension=gd.so';
    
    if debug then begin
       writeln('Reading file "' + php_ini + '"');
       logs.logsInstall('PHP5_ENABLE_GD_LIBRARY::Reading file "' + php_ini + '"');
    end;
    
    FileData.LoadFromFile(php_ini);
    
    for i:=0 to FileData.Count -1 do begin
        if RegExpr.exec(FileData.Strings[i]) then begin
            logs.logsInstall('PHP5_ENABLE_GD_LIBRARY:: gd library is already set');
            writeln('GD Library already set...nothing to do');
            logs.Free;
            RegExpr.Free;
            FileData.Free;
            exit();
        end;
    end;
    
 logs.logsInstall('PHP5_ENABLE_GD_LIBRARY:: adding gd library');
 if debug then writeln('Set GD Library..');
 FileData.Add('extension=gd.so');
 FileData.SaveToFile(php_ini);
  if debug then writeln('Restarting apache');
  shell(apache_init + ' restart');
    

end;
//#############################################################################

procedure MyConf.set_SELINUX_DISABLED();
var list:TstringList;
begin

if fileExists('/etc/rc.d/boot.apparmor') then begin
      ShowScreen('set_SELINUX_DISABLED:: Disable AppArmor...');
      Shell('/etc/init.d/boot.apparmor stop');
      Shell('/sbin/chkconfig -d boot.apparmor');
end;

if fileExists('/sbin/SuSEfirewall2') then begin
   ShowScreen('set_SELINUX_DISABLED:: Disable SuSEfirewall2...');
   Shell('/sbin/SuSEfirewall2 off');
end;
if FileExists('/etc/selinux/config') then begin
   killfile('/etc/selinux/config');
   list:=TstringList.Create;
   list.Add('SELINUX=disabled');
   list.SaveToFile('/etc/selinux/config');
   list.Free;
end;
end;
//#############################################################################
function MyConf.ARTICA_MYSQL_INFOS(val:string);
var ini:TIniFile;
begin
if not FileExists('/etc/artica-postfix/artica-mysql.conf') then exit();
ini:=TIniFile.Create('/etc/artica-postfix/artica-mysql.conf');
result:=ini.ReadString('MYSQL',val,'');
ini.Free;
end;
//#############################################################################
function MyConf.MYSQL_INIT_PATH:string;
var path:string;
begin
  path:=LINUX_APPLICATION_INFOS('mysql_init');
  if length(path)>0 then begin
           if FileExists(path) then exit(path);
  end;
  
  if FileExists('/etc/init.d/mysql') then exit('/etc/init.d/mysql');
  if FileExists('/etc/init.d/mysqld') then exit('/etc/init.d/mysqld');
  
end;
//#############################################################################
function MyConf.MYSQL_MYCNF_PATH:string;
var path:string;
begin
  path:=LINUX_APPLICATION_INFOS('my_cnf');
  if length(path)>0 then begin
           if FileExists(path) then exit(path);
  end;

  if FileExists('/etc/mysql/my.cnf') then exit('/etc/mysql/my.cnf');
  if FileExists('/etc/my.cnf') then exit('/etc/my.cnf');

end;
//#############################################################################
function MyConf.MYSQL_BIN_PATH:string;
var path:string;
begin
  path:=LINUX_APPLICATION_INFOS('mysql_bin');
  if length(path)>0 then begin
           if FileExists(path) then exit(path);
  end;

  if FileExists('/usr/bin/mysql') then exit('/usr/bin/mysql');

end;
//#############################################################################
function MyConf.MYSQL_VERSION:string;
var mysql_bin,returned:string;
    RegExpr:TRegExpr;
begin
   mysql_bin:=MYSQL_BIN_PATH();
   returned:=ExecPipe(mysql_bin + ' -V');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='([0-9]+\.[0-9]+\.[0-9]+)';
   if RegExpr.Exec(returned) then result:=RegExpr.Match[1];
   RegExpr.Free;

end;
//#############################################################################
function MyConf.AWSTATS_MAILLOG_CONVERT_PATH_SOURCE():string;
begin
if FileExists('/usr/share/doc/awstats/examples/maillogconvert.pl') then exit('/usr/share/doc/awstats/examples/maillogconvert.pl');
if FileExists('/usr/share/awstats/tools/maillogconvert.pl') then exit('/usr/share/awstats/tools/maillogconvert.pl');
if FileExists('/usr/share/doc/packages/awstats/tools/maillogconvert.pl') then exit('/usr/share/doc/packages/awstats/tools/maillogconvert.pl');
end;
//#############################################################################
function MyConf.AWSTATS_PATH():string;
begin
if FileExists('/usr/lib/cgi-bin/awstats.pl') then exit('/usr/lib/cgi-bin/awstats.pl');
if FileExists('/srv/www/cgi-bin/awstats.pl') then exit('/srv/www/cgi-bin/awstats.pl');
if FileExists('/var/www/awstats/awstats.pl') then exit('/var/www/awstats/awstats.pl');
if FileExists('/usr/share/awstats/wwwroot/cgi-bin/awstats.pl') then exit('/usr/share/awstats/wwwroot/cgi-bin/awstats.pl');
end;

//#############################################################################
function MyConf.AWSTATS_GET_VALUE(key:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    ValueResulted:string;
begin
   if not FileExists('/etc/awstats/awstats.mail.conf') then  begin
      showscreen('AWSTATS_GET_VALUE:: unable to stat /etc/awstats/awstats.mail.conf');
      exit;
   end;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile('/etc/awstats/awstats.mail.conf');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              FileDatas.Free;
              ValueResulted:=RegExpr.Match[2];
              if ValueResulted='"' then ValueResulted:='';
              RegExpr.Free;
              exit(ValueResulted);
           end;
   
   end;
   FileDatas.Free;
   RegExpr.Free;

end;
//#############################################################################

function MyConf.AWSTATS_SET_VALUE(key:string;value:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    ValueResulted:string;
begin
   if not FileExists('/etc/awstats/awstats.mail.conf') then  begin
      showscreen('AWSTATS_GET_VALUE:: unable to stat /etc/awstats/awstats.mail.conf');
      exit;
   end;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile('/etc/awstats/awstats.mail.conf');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
                FileDatas.Strings[i]:=key + '=' + value;
                FileDatas.SaveToFile('/etc/awstats/awstats.mail.conf');
                FileDatas.Free;
                RegExpr.Free;
                exit;

           end;

   end;

  FileDatas.Add(key + '=' + value);
  FileDatas.SaveToFile('/etc/awstats/awstats.mail.conf');
  FileDatas.Free;
  RegExpr.Free;


end;
//#############################################################################
function MyConf.DNSMASQ_GET_VALUE(key:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    ValueResulted:string;
begin
   if not FileExists('/etc/dnsmasq.conf') then  begin
      showscreen('DNSMASQ_GET_VALUE:: /etc/dnsmasq.conf');
      exit;
   end;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile('/etc/dnsmasq.conf');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              FileDatas.Free;
              ValueResulted:=RegExpr.Match[2];
              if ValueResulted='"' then ValueResulted:='';
              RegExpr.Free;
              exit(ValueResulted);
           end;

   end;
   FileDatas.Free;
   RegExpr.Free;

end;
//#############################################################################
function MyConf.DNSMASQ_SET_VALUE(key:string;value:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    ValueResulted:string;
    FileToEdit:string;
begin
   FileToEdit:='/etc/dnsmasq.conf';
   if not FileExists(FileToEdit) then  begin
      showscreen('DNSMASQ_SET_VALUE:: unable to stat ' + FileToEdit);
      exit;
   end;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(FileToEdit);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
                FileDatas.Strings[i]:=key + '=' + value;
                FileDatas.SaveToFile(FileToEdit);
                FileDatas.Free;
                RegExpr.Free;
                exit;

           end;

   end;

  FileDatas.Add(key + '=' + value);
  FileDatas.SaveToFile(FileToEdit);
  FileDatas.Free;
  RegExpr.Free;


end;
//#############################################################################
function MyConf.SYSTEM_ADD_NAMESERVER(nameserver:string):boolean;
var
   FileDatas:Tstringlist;
   RegExpr:TRegExpr;
   FileToEdit:string;
   i:integer;
begin
   FileToEdit:='/etc/resolv.conf';
   if not FileExists(FileToEdit) then  begin
      showscreen('SYSTEM_ADD_NAMESERVER:: unable to stat ' + FileToEdit);
      exit(false);
   end;
   
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(FileToEdit);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^nameserver\s+' +nameserver;
   for i:=0 to FileDatas.Count -1 do begin
       if RegExpr.Exec(FileDatas.Strings[i]) then begin
          RegExpr.free;
          FileDatas.free;
          exit(true);
       end;
   end;
   
   FileDatas.Insert(0,'nameserver ' + nameserver);
   FileDatas.SaveToFile(FileToEdit);
   RegExpr.free;
   FileDatas.free;
   exit(true);
end;

//#############################################################################
function MyConf.DNSMASQ_BIN_PATH():string;
begin
    if FileExists('/usr/sbin/dnsmasq') then exit('/usr/sbin/dnsmasq');
    if FileExists('/usr/local/sbin/dnsmasq') then exit('/usr/local/sbin/dnsmasq');
end;
//#############################################################################
function MyConf.SYSTEM_LD_SO_CONF_ADD(path:string):string;
var
 FileDatas:TStringList;
 i:integer;
begin
     FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile('/etc/ld.so.conf');
    for i:=0 to FileDatas.Count -1 do begin
      if trim(FileDatas.Strings[i])=path then begin
         ShowScreen('SYSTEM_LD_SO_CONF_ADD:: "' + path + '" already added to /etc/ld.so.conf');
         FileDatas.Free;
         exit;
      end;
    end;
    
     FileDatas.Add(path);
     FileDatas.SaveToFile('/etc/ld.so.conf');
     FileDatas.Free;
     ShowScreen('SYSTEM_LD_SO_CONF_ADD:: -> ldconfig ... Please wait...');
     shell('ldconfig');
     
    
   
   

end;

//#############################################################################
function MyConf.AWSTATS_VERSION():string;
var
    RegExpr,RegExpr2:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    Major,minor,awstats_root:string;
    D:boolean;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
    awstats_root:=AWSTATS_PATH();

    
    
    if length(awstats_root)=0 then begin
       if D then ShowScreen('AWSTATS_VERSION::unable to locate awstats.pl');
      exit;
   end;
   
    if D then ShowScreen('AWSTATS_VERSION:: ->'+ awstats_root);
   
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(awstats_root);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^\$VERSION="([0-9\.]+)';
   
   RegExpr2:=TRegExpr.Create;
   RegExpr2.Expression:='^\$REVISION=''\$Revision:\s+([0-9\.]+)';
   
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              if D then ShowScreen('AWSTATS_VERSION:: found ->'+ FileDatas.Strings[i] + '(' +RegExpr.Match[1]  + ')' );
              Major:=RegExpr.Match[1];
           end;
           if RegExpr2.Exec(FileDatas.Strings[i]) then begin
              if D then ShowScreen('AWSTATS_VERSION:: found ->'+ FileDatas.Strings[i] + '(' +RegExpr2.Match[1]  + ')' );
              minor:=RegExpr2.Match[1];
           end;
           if length(Major)>0 then begin
                  if length(minor)>0 then begin
                  AWSTATS_VERSION:=major + ' rev ' + minor;
                  FileDatas.Free;
                  RegExpr.Free;
                  RegExpr2.Free;
                  exit;
                  end;
           end;

   end;
                  FileDatas.Free;
                  RegExpr.Free;
                  RegExpr2.Free;
                  AWSTATS_VERSION:=major;

end;

//#############################################################################

procedure MyConf.AWSTATS_GENERATE();
var maintool,artica_path:string;
 FileDatas:TStringList;
 D:boolean;
 i:integer;
 Zcommand,zConfig:string;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
    if not D then D:=COMMANDLINE_PARAMETERS('generate');
    if not D then D:=COMMANDLINE_PARAMETERS('reconfigure');
     
    artica_path:=get_INSTALL_PATH();
    maintool:=AWSTATS_PATH();
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(artica_path + '/ressources/databases/awstats.pages.db');
    
    if length(maintool)=0 then begin
       if D then ShowScreen('AWSTATS_GENERATE:: unable to locate awstats.pl');
       exit;
    end;

    shell(maintool + ' -update -config=mail');
    for i:=0 to FileDatas.Count -1 do begin
          zConfig:=trim(FileDatas.Strings[i]);
          if zConfig='index' then begin
             Zcommand:=maintool + ' -config=mail -staticlinks -output >' + artica_path + '/ressources/logs/awstats.' + zConfig + '.tmp';
          end else begin
              Zcommand:=maintool + ' -config=mail -output=' + zConfig + ' -staticlinks >' + artica_path + '/ressources/logs/awstats.' + zConfig + '.tmp';
          end;
          if D then ShowScreen('AWSTATS_GENERATE::' + Zcommand);
          shell(Zcommand);
    end;
   FileDatas.Free;

end;



//#############################################################################
function MyConf.AWSTATS_SET_PLUGIN(value:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    ValueResulted:string;
begin
   if not FileExists('/etc/awstats/awstats.mail.conf') then  begin
      showscreen('AWSTATS_SET_PLUGIN:: unable to stat /etc/awstats/awstats.mail.conf');
      exit;
   end;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile('/etc/awstats/awstats.mail.conf');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^LoadPlugin="' + value + '"';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
                ShowScreen('AWSTATS_SET_PLUGIN:: Plugin ' + value + ' already added');
                FileDatas.Free;
                RegExpr.Free;
                exit;

           end;

   end;
  ShowScreen('AWSTATS_SET_PLUGIN:: Add Plugin ' + value);
  FileDatas.Add('LoadPlugin="' + value + '"');
  FileDatas.SaveToFile('/etc/awstats/awstats.mail.conf');
  FileDatas.Free;
  RegExpr.Free;


end;

//#############################################################################


function MyConf.ARTICA_MYSQL_SET_INFOS(val:string;value:string);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-mysql.conf');
ini.WriteString('MYSQL',val,value);
ini.Free;
end;
//#############################################################################
function MyConf.MYSQL_ROOT():string;
begin
   result:=ARTICA_MYSQL_INFOS('database_admin');
   if length(result)=0 then result:='root';
end;
//#############################################################################
function MyConf.MYSQL_PASSWORD():string;
begin
   result:=ARTICA_MYSQL_INFOS('database_password');
   if length(result)=0 then result:='';
end;
//#############################################################################
function MyConf.MYSQL_SERVER():string;
begin
   result:=ARTICA_MYSQL_INFOS('mysql_server');
   if length(result)=0 then result:='localhost';
end;
//#############################################################################
function MyConf.MYSQL_ENABLED():boolean;
var s:string;
begin
   result:=true;
   s:=ARTICA_MYSQL_INFOS('use_mysql');
   s:=LowerCase(s);
   if s='yes' then result:=true;
   if s='no' then result:=false;
end;
//#############################################################################
function MyConf.ARTICA_VERSION():string;
var
   s,l:string;
   F:TstringList;
   
begin
   l:=get_ARTICA_PHP_PATH() + '/VERSION';
   if not FileExists(l) then exit('0.00');
   F:=TstringList.Create;
   F.LoadFromFile(l);
   result:=trim(F.Text);
   F.Free;
end;
//#############################################################################
function MyConf.MYSQL_ACTION_TESTS_ADMIN():boolean;
    var root,password,commandline,cmd_result:string;
begin
  root:=MYSQL_ROOT();
  password:=MYSQL_PASSWORD();
  if not fileExists('/usr/bin/mysql') then exit(false);
  if length(password)>0 then password:=' -p'+password;
  commandline:='/usr/bin/mysql -e ''select User,Password from user'' -u '+ root +password+' mysql';
  cmd_result:=ExecPipe(commandline);
  if length(cmd_result)>0 then exit(true) else exit(false);
end;
//#############################################################################
function MyConf.MYSQL_ACTION_COUNT_TABLES(database_name:string):integer;
    var root,commandline,password,cmd_result,pass:string;
    list:TStringList;
    i:integer;
    XDebug:boolean;
    RegExpr:TRegExpr;
    found:boolean;
    count:integer;
begin
  root:=MYSQL_ROOT();
  password:=MYSQL_PASSWORD();
  if length(password)>0 then password:=' -p'+password;
  if not fileExists('/usr/bin/mysql') then exit(0);
  commandline:='/usr/bin/mysql -N -s -X -e ''show tables'' -u '+ root +password + ' ' + database_name;
  if XDebug then ShowScreen('MYSQL_ACTION_COUNT_TABLES::'+commandline);
  list:=TStringList.Create;
  list.LoadFromStream(ExecStream(commandline,false));
  if list.Count<2 then begin
    list.free;
    exit(0);
  end;

RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='<field name="Tables_in_' +database_name + '">(.+)<\/field>';
  //ShowScreen('MYSQL_ACTION_COUNT_TABLES::'+RegExpr.Expression);
  for i:=0 to list.count-1 do begin
      if RegExpr.Exec(list.Strings[i]) then inc(count);

  end;
  
list.free;
RegExpr.free;
exit(count);

end;
//#############################################################################
function MyConf.MYSQL_ACTION_IF_DATABASE_EXISTS(database_name:string):boolean;
    var root,commandline,password,cmd_result,pass:string;
    list:TStringList;
    i:integer;
    XDebug:boolean;
    RegExpr:TRegExpr;
    found:boolean;
begin
  root:=MYSQL_ROOT();
  password:=MYSQL_PASSWORD();
  if length(password)>0 then password:=' -p'+password;
  if not fileExists('/usr/bin/mysql') then exit(false);
  commandline:='/usr/bin/mysql -N -s -X -e ''show databases'' -u '+ root +password;
  if XDebug then ShowScreen('MYSQL_ACTION_IF_DATABASE_EXISTS::' + commandline);
  list:=TStringList.Create;
  list.LoadFromStream(ExecStream(commandline,false));
  
RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='<field name="Database">(.+)<\/field>';
  for i:=0 to list.count-1 do begin
      if RegExpr.Exec(list.Strings[i]) then begin
          if RegExpr.Match[1]=database_name then begin
                RegExpr.free;
                list.free;
                if XDebug then ShowScreen('MYSQL_ACTION_IF_DATABASE_EXISTS::' + database_name + ' exists');
                exit(true);
          end;
      end;
  end;
if XDebug then ShowScreen('MYSQL_ACTION_IF_DATABASE_EXISTS::' + database_name + ' not exists');
exit(false);
  
end;
//#############################################################################
function MyConf.MYSQL_ACTION_IMPORT_DATABASE(filenname:string;database:string):boolean;
    var root,commandline,password,cmd_result,pass:string;
    i:integer;
    XDebug:boolean;
    RegExpr:TRegExpr;
    found:boolean;
begin
  root:=MYSQL_ROOT();
  password:=MYSQL_PASSWORD();
  if length(password)>0 then password:=' -p'+password;
  if not fileExists('/usr/bin/mysql') then begin
     ShowScreen('MYSQL_ACTION_IMPORT_DATABASE:: Unable to locate mysql binary (usually in /usr/bin/mysql)');
     exit(false);
  end;
  
  if not FileExists(filenname) then begin
     ShowScreen('MYSQL_ACTION_IMPORT_DATABASE:: Unable to stat ' +filenname);
     exit;
  end;
   ShowScreen(commandline);
  commandline:='/usr/bin/mysql -u '+ root +password + ' ' +database + ' <' + filenname ;

  Shell(commandline);
end;
//#############################################################################
function MyConf.MYSQL_ACTION_QUERY(sql:string):boolean;
    var root,commandline,password,cmd_result,pass:string;
    i:integer;
    XDebug:boolean;
    RegExpr:TRegExpr;
    found:boolean;
begin
  root:=MYSQL_ROOT();
  password:=MYSQL_PASSWORD();
  if length(password)>0 then password:=' -p'+password;
  if not fileExists('/usr/bin/mysql') then begin
     ShowScreen('MYSQL_ACTION_QUERY:: Unable to locate mysql binary (usually in /usr/bin/mysql)');
     exit(false);
  end;
  commandline:='/usr/bin/mysql -N -s -X -e ''' + sql + ''' -u '+ root +password;
   ShowScreen('MYSQL_ACTION_QUERY::'+commandline);
  Shell(commandline);
end;
//#############################################################################
function MyConf.MYSQL_ACTION_CREATE_ADMIN(username:string;password:string):boolean;
    var root,commandline,cmd_result,pass:string;
    list:TStringList;
    i:integer;
    XDebug:boolean;
    RegExpr:TRegExpr;
    found:boolean;
begin
  if length(password)=0 then begin
     writeln('please, set a password...');
     exit(false);
  end;
  pass:=password;
  found:=false;
  if ParamStr(2)='setadmin' then XDebug:=true;
  root:=MYSQL_ROOT();
  password:=MYSQL_PASSWORD();
   if not fileExists('/usr/bin/mysql') then begin
     ShowScreen('MYSQL_ACTION_IMPORT_DATABASE:: Unable to locate mysql binary (usually in  /usr/bin/mysql)');
     exit(false);
  end;
  if length(password)>0 then password:=' -p'+password;
  commandline:='/usr/bin/mysql -N -s -X -e ''select User from user'' -u '+ root +password+' mysql';
  if XDebug then ShowScreen(commandline);
  list:=TStringList.Create;
  list.LoadFromStream(ExecStream(commandline,false));
  if list.Count<2 then begin
    list.free;
    exit(false);
  end;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='<field name="User">(.+)<\/field>';
  for i:=0 to list.count-1 do begin
      if RegExpr.Exec(list.Strings[i]) then begin
          if RegExpr.Match[1]=username then found:=True;
      end;
  end;
  if found=true then begin
     ShowScreen('MYSQL_ACTION_CREATE_ADMIN:: updating ' + username + ' password');
     commandline:='/usr/bin/mysql -N -s -X -e ''UPDATE user SET Password=PASSWORD("' + pass + '") WHERE User="'+username+'"; FLUSH PRIVILEGES;'' -u '+ root +password+' mysql';
     if XDebug then ShowScreen('MYSQL_ACTION_CREATE_ADMIN::' + commandline);
     Shell(commandline);
  end else begin
  
  commandline:='/usr/bin/mysql -N -s -X -e ''INSERT INTO user';
  commandline:=commandline + ' (Host,User,Password,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv,Reload_priv,Shutdown_priv,Process_priv,File_priv,Grant_priv,References_priv,Index_priv,';
  commandline:=commandline + ' Alter_priv,Show_db_priv,Super_priv,Create_tmp_table_priv,Lock_tables_priv,Execute_priv,Repl_slave_priv,Repl_client_priv,Create_view_priv,Show_view_priv,Create_routine_priv,'; //11
  commandline:=commandline + ' Alter_routine_priv,Create_user_priv)';
  commandline:=commandline + ' VALUES("localhost","'+ username +'",PASSWORD("'+ pass+'"),';
  commandline:=commandline + '"Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y",';
  commandline:=commandline + '"Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y");FLUSH PRIVILEGES;'' -u '+ root +password+' mysql';
  if XDebug then ShowScreen('MYSQL_ACTION_CREATE_ADMIN::' + commandline);
  Shell(commandline);
  end;
  
  list.free;

end;
//#############################################################################

procedure MyConf.set_LINUX_DISTRI(val:string);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
ini.WriteString('LINUX','distribution-name',val);
ini.Free;
end;
function MyConf.OPENSSL_TOOL_PATH():string;
begin
if FileExists('/usr/bin/openssl') then exit('/usr/bin/openssl');
end;

//#############################################################################
function MyConf.CERTIFICATE_PASS(path:string):string;
var ini:TIniFile;
begin
ini:=TIniFile.Create(path);
result:=ini.ReadString('req','input_password','secret');
ini.Free;
end;
//#############################################################################
function MyConf.CERTIFICATE_PATH(path:string):string;
var ini:TIniFile;
begin
ini:=TIniFile.Create(path);
result:=ini.ReadString('default_db','dir','/etc/postfix/certificates');
ini.Free;
end;
//#############################################################################
function MyConf.CERTIFICATE_CA_FILENAME(path:string):string;
var ini:TIniFile;
begin
ini:=TIniFile.Create(path);
result:=ini.ReadString('postfix','smtpd_tls_CAfile','cacert.pem');
ini.Free;
end;
//#############################################################################
function MyConf.CERTIFICATE_KEY_FILENAME(path:string):string;
var ini:TIniFile;
begin
ini:=TIniFile.Create(path);
result:=ini.ReadString('postfix','smtpd_tls_key_file','smtpd.key');
ini.Free;
end;
//#############################################################################
function MyConf.CERTIFICATE_CERT_FILENAME(path:string):string;
var ini:TIniFile;
begin
ini:=TIniFile.Create(path);
result:=ini.ReadString('postfix','smtpd_tls_cert_file','smtpd.crt');
ini.Free;
end;
//#############################################################################
function MyConf.PROCMAIL_QUARANTINE_PATH():string;
var ini:TIniFile;
begin
if not fileExists('/etc/artica-postfix/artica-procmail.conf') then begin
   result:='/var/quarantines/procmail';
   exit;
end;
ini:=TIniFile.Create('/etc/artica-postfix/artica-procmail.conf');
result:=ini.ReadString('path','quarantine_path','/var/quarantines/procmail');
ini.Free;
end;

//#############################################################################
procedure MyConf.set_INFOS(key:string;val:string);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
ini.WriteString('INFOS',key,val);
ini.Free;
end;
//#############################################################################
procedure MyConf.set_LDAP(key:string;val:string);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix-ldap.conf');
ini.WriteString('LDAP',key,val);
ini.Free;
end;
//#############################################################################
function MyConf.get_LDAP(key:string):string;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix-ldap.conf');
value:=GLOBAL_INI.ReadString('LDAP',key,'');
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.ARTICA_FILTER_QUEUEPATH():string;
var ini:TIniFile;
begin
 ini:=TIniFile.Create('/etc/artica-postfix/artica-filter.conf');
 result:=ini.ReadString('INFOS','QueuePath','');
 if length(trim(result))=0 then result:='/usr/share/artica-filter';
end;
//##############################################################################


function MyConf.get_INFOS(key:string):string;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('INFOS',key,'');
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.RRDTOOL_STAT_LOAD_AVERAGE_DATABASE_PATH():string;
var value,phppath,path:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=ini.ReadString('ARTICA','STAT_LOAD_PATH','');
if length(value)=0 then  begin
   if debug then writeln('STAT_LOAD_PATH is not set in ini path');
   phppath:=get_ARTICA_PHP_PATH();
   path:=phppath+'/ressources/rrd/process.rdd';
   if debug then writeln('set STAT_LOAD_PATH to '+path);
   value:=path;
   ini.WriteString('ARTICA','STAT_LOAD_PATH',path);
   if debug then writeln('done..'+path);
end;
result:=value;
ini.Free;
end;
//#############################################################################
function MyConf.ARTICA_SEND_MAX_SUBQUEUE_NUMBER:integer;
var value:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-filter.conf');
result:=ini.ReadInteger('INFOS','MAX_QUEUE_NUMBER',5);
ini.free;
end;
//#############################################################################


//#############################################################################
function MyConf.ARTICA_SEND_SUBQUEUE_NUMBER(QueueNumber:string):integer;
var
   QueuePath:string;
   SYS:TSystem;
   i:Integer;
   Count:Integer;
   NumbersIntoQueue:integer;
   D:boolean;
begin
  result:=0;
  NumbersIntoQueue:=0;
  SYS:=TSystem.Create;
  D:=COMMANDLINE_PARAMETERS('debug');
  QueuePath:=ARTICA_FILTER_QUEUEPATH() + '/queue';
  if D then writeln('ARTICA_SEND_SUBQUEUE_NUMBER: QueuePath=' + QueuePath);
     if DirectoryExists(QueuePath + '/' +QueueNumber) then begin
        SYS.DirFiles(QueuePath + '/' + QueueNumber,'*.queue');
        NumbersIntoQueue:=SYS.DirListFiles.Count;
     end;
  if D then writeln('ARTICA_SEND_SUBQUEUE_NUMBER: Number=' + IntToStr(NumbersIntoQueue) + ' Objects');
  //logs.logs('ARTICA_SEND_SUBQUEUE_NUMBER:: NumbersIntoQueue:=' + IntToStr(NumbersIntoQueue));
  SYS.Free;
  exit(NumbersIntoQueue);
end;
//#############################################################################
function MyConf.ARTICA_SEND_QUEUE_NUMBER():integer;
var
   QueuePath:string;
   SYS:TSystem;
   i:Integer;
   Count:Integer;
   QueueNumber:integer;
begin
  result:=0;
  Count:=0;
  SYS:=TSystem.Create;

  if FileExists('/usr/local/sbin/emailrelay') then begin
     QueuePath:=ARTICA_FILTER_QUEUEPATH();
     if DirectoryExists(QueuePath) then SYS.DirFiles(QueuePath , '*.envelope.new');
     exit(SYS.DirListFiles.Count);
  end;
  

  QueuePath:=ARTICA_FILTER_QUEUEPATH() + '/queue';
  For i:=0 to 99 do begin
     if DirectoryExists(QueuePath + '/' + IntToStr(i)) then begin
        SYS.DirFiles(QueuePath + '/' + IntToStr(i),'*.queue');
        QueueNumber:=SYS.DirListFiles.Count;
        Count:=Count + QueueNumber;
     end;
  end;
  
  result:=Count;
//  SYS.Free;
  exit;
end;
//#############################################################################
function MyConf.ARTICA_SQL_QUEUE_NUMBER():integer;
var
   QueuePath:string;
   SYS:TSystem;
begin
  QueuePath:=ARTICA_FILTER_QUEUEPATH() + '/sql_queue';
  SYS:=TSystem.Create;
  SYS.DirFiles(QueuePath,'*.sql');
  result:=SYS.DirListFiles.Count;
  SYS.Free;
  exit;
end;
//#############################################################################
procedure MyConf.ARTICA_FILTER_CLEAN_QUEUE();
var
   QueuePath:string;
   SourceFile:string;
   DestFile:string;
   SYS:TSystem;
   i:integer;
   D:boolean;
   pid,body:string;
   mailpid:string;
   RegExpr:TRegExpr;
   Strpos:integer;
   DeleteFile:boolean;
begin
   D:=COMMANDLINE_PARAMETERS('--verbose');
   QueuePath:=ARTICA_FILTER_QUEUEPATH();
   pid:=EMAILRELAY_PID();
   SYS:=TSystem.Create;
   SYS.DirFiles(QueuePath,'*.new');
   LOGS:=Tlogs.Create;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='emailrelay\.([0-9]+)\.[0-9]+\.[0-9]+\.envelope';
   for i:=0 to SYS.DirListFiles.Count-1 do begin
        SourceFile:=QueuePath + '/' + SYS.DirListFiles.Strings[i];
        if RegExpr.Exec(SourceFile) then mailpid:=RegExpr.Match[1];

        Strpos:=pos('.new',SourceFile);
        DestFile:=Copy(SourceFile,0,Strpos-1);
        if D then writeln('ARTICA_FILTER_CLEAN_QUEUE: "' + DestFile + '" saved by process number ' + mailpid + '->(' + pid+')');
        if pid<>mailpid then begin
           LOGS.logs('ARTICA_FILTER_CLEAN_QUEUE:: Flush ' + DestFile + ' in new mode');
           if D then writeln('ARTICA_FILTER_CLEAN_QUEUE:  Flush ' + DestFile + ' in new mode');
           shell('/bin/mv ' + SourceFile + ' ' + DestFile);
        end;

   end;
   SYS.DirListFiles.Clear;
   SYS.DirFiles(QueuePath,'*.busy');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='emailrelay\.([0-9]+)\.[0-9]+\.[0-9]+\.envelope';
   for i:=0 to SYS.DirListFiles.Count-1 do begin
        SourceFile:=QueuePath + '/' + SYS.DirListFiles.Strings[i];
        if RegExpr.Exec(SourceFile) then mailpid:=RegExpr.Match[1];

        Strpos:=pos('.busy',SourceFile);
        DestFile:=Copy(SourceFile,0,Strpos-1);
        if D then writeln('ARTICA_FILTER_CLEAN_QUEUE: "' + DestFile + '" saved by process number ' + mailpid + '->(' + pid+')');
        if pid<>mailpid then begin
           LOGS.logs('ARTICA_FILTER_CLEAN_QUEUE:: Flush ' + DestFile + ' in busy mode');
           if D then writeln('ARTICA_FILTER_CLEAN_QUEUE:  Flush ' + DestFile + ' in busy mode');
           shell('/bin/mv ' + SourceFile + ' ' + DestFile);
        end;

   end;

     LOGS.FREE;
   RegExpr.Free;
   SYS.free;
   exit;

      
   SYS.DirListFiles.Clear;
   SYS.DirFiles(QueuePath,'*.content');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='emailrelay\.([0-9\.]+)\.content';
   for i:=0 to SYS.DirListFiles.Count-1 do begin
        SourceFile:=QueuePath + '/' + SYS.DirListFiles.Strings[i];
        if RegExpr.Exec(SourceFile) then begin
           body:=RegExpr.Match[1];
           DeleteFile:=true;
           if FileExists(QueuePath + '/' + 'emailrelay.' + body + '.envelope') then DeleteFile:=false;
           if FileExists(QueuePath + '/' + 'emailrelay.' + body + '.envelope.new') then DeleteFile:=false;
           if FileExists(QueuePath + '/' + 'emailrelay.' + body + '.envelope.busy') then DeleteFile:=false;
           if FileExists(QueuePath + '/' + 'emailrelay.' + body + '.envelope.bad') then DeleteFile:=false;
           if FileExists(QueuePath + '/' + 'emailrelay.' + body + '.envelope.local') then DeleteFile:=false;

        

        if DeleteFile then begin
           if D then writeln('ARTICA_FILTER_CLEAN_QUEUE: Delete ' + SourceFile);
           LOGS.logs('ARTICA_FILTER_CLEAN_QUEUE:: Delete old file ' + SourceFile);
           shell('/bin/rm ' + SourceFile);
        end;
       end;

   end;
   
   LOGS.FREE;
   RegExpr.Free;
   SYS.free;
end;
//#############################################################################



function MyConf.SYSTEM_PROCESS_EXIST(pid:string):boolean;
begin
  result:=false;
  if pid='0' then exit(false);
  
  if not fileExists('/proc/' + pid + '/exe') then begin
     exit(false)
  end else begin
      exit(true);
  end;
end;


//#############################################################################
function MyConf.SYSTEM_GET_PID(pidPath:string):string;
var
   mDatas:string;
   RegExpr:TRegExpr;
   Files:TStringList;
begin
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='([0-9]+)';
result:='0';
if not FileExists(pidPath) then exit;
Files:=TStringList.Create;
Files.LoadFromFile(pidPath);

if RegExpr.Exec(Files.Strings[0]) then result:=RegExpr.Match[1];
RegExpr.Free;
Files.Free;
end;
//#############################################################################
function MyConf.ARTICA_FILTER_PID():string;
begin
result:=SYSTEM_GET_PID('/etc/artica-postfix/artica-filter.pid');
exit;
end;
//#############################################################################
function MyConf.ARTICA_SEND_PID(QueueNumber:String):string;
begin
result:=SYSTEM_GET_PID('/etc/artica-postfix/artica-send.' + QueueNumber + 'pid');
exit;
end;
//#############################################################################
function MyConf.ARTICA_SQL_PID():string;
begin
result:=SYSTEM_GET_PID('/etc/artica-postfix/artica-sql.pid');
exit;
end;
//############################################################################# #
function MyConf.EMAILRELAY_PID():string;
begin
result:=SYSTEM_GET_PID('/etc/artica-postfix/emailrelay.pid');
exit;
end;
//############################################################################# #

function MyConf.ARTICA_SEND_QUEUE_PATH():string;
var
   value:string;
   S_INI:TIniFile;
begin
S_INI:=TIniFile.Create('/etc/artica-postfix/artica-send.conf');
value:=S_INI.ReadString('QUEUE','QueuePath','/usr/share/artica-filter/queue');
if length(value)=0 then value:='/usr/share/artica-filter/queue';
result:=value;
S_INI.Free;
end;
//#############################################################################

procedure MyConf.ARTICA_FILTER_WATCHDOG();
var
   i:integer;
   XLogs:TLogs;
   D:boolean;
   Articafilter_pid,articapolicy_pid,dnsmasqpid:string;
   fetchmailpid:string;
   damon_path,damon_path2,dnsmasqbin:string;
   LOGS2:TLogs;
   P:TProcess;
begin
    damon_path:=get_ARTICA_PHP_PATH() + '/bin/artica-filter';
    damon_path2:=get_ARTICA_PHP_PATH() + '/bin/artica-policy';
    D:=COMMANDLINE_PARAMETERS('debug');
    LOGS2:=Tlogs.Create;
    dnsmasqbin:=DNSMASQ_BIN_PATH();
    Articafilter_pid:=ARTICA_FILTER_PID();
    articapolicy_pid:=ARTICA_POLICY_GET_PID();
    fetchmailpid:=FETCHMAIL_PID();

    if D then writeln('fetchmail pid='  +fetchmailpid);
    if FileExists(FETCHMAIL_BIN_PATH()) then begin
           if not SYSTEM_PROCESS_EXIST(fetchmailpid) then begin
            LOGS2.logs('ARTICA_FILTER_WATCHDOG:: running fetchmail process (' + fetchmailpid + ') doesn''t exists');

            FETCHMAIL_START_DAEMON();
            fetchmailpid:=FETCHMAIL_PID();
            LOGS2.logs('ARTICA_FILTER_WATCHDOG:: New PID is (' + fetchmailpid + ')');
           end;
    
    end;

    
    if not SYSTEM_PROCESS_EXIST(articapolicy_pid) then begin
          LOGS2.logs('ARTICA_FILTER_WATCHDOG:: running artica-policy process ({' + articapolicy_pid + '} "' + damon_path2 + '") doesn''t exists');
          try
             P:=TProcess.Create(nil);
             P.CommandLine:=damon_path2;
             P.Execute;
          Except
             LOGS2.logs('ARTICA_FILTER_WATCHDOG:: FATAL ERROR WHILE RUNNING ' +  damon_path2);
          end;
          Select(0,nil,nil,nil,10*500);
    end;
    
    if fileexists(dnsmasqbin) then begin
          dnsmasqpid:=DNSMASQ_PID();
          if not SYSTEM_PROCESS_EXIST(dnsmasqpid) then begin
             LOGS2.logs('ARTICA_FILTER_WATCHDOG:: running dnsmasq process ({' + dnsmasqpid + '} "' + dnsmasqbin + '") doesn''t exists');
             DNSMASQ_START_DAEMON();
          end;
    end;

    
    

   LOGS2.Free;
end;
//##############################################################################
function MyConf.CGI_ALL_APPLIS_INSTALLED():string;
var AVE_VER,KASVER:string;

begin
    AVE_VER:=AVESERVER_GET_VERSION();
    KASVER:=KAS_VERSION();
    ArrayList.Clear;

    ArrayList.Add('<SECURITY_MODULES>');
    ArrayList.Add('[APP_AVESERVER] "' + AVE_VER + '"');
    ArrayList.Add('[APP_KAS3] "' + KASVER + '"');
    ArrayList.Add('</SECURITY_MODULES>');

    ArrayList.Add('<CORE_MODULES>');
    ArrayList.Add('[APP_POSTFIX] "' + POSTFIX_VERSION() + '"');
    ArrayList.Add('[APP_LDAP] "' + LDAP_VERSION() + '"');
    ArrayList.Add('[APP_RENATTACH] "' + RENATTACH_VERSION() + '"');
    ArrayList.Add('[APP_GEOIP] "' + GEOIP_VERSION() + '"');
    

    ArrayList.Add('</CORE_MODULES>');
    
    ArrayList.Add('<STAT_MODULES>');
    ArrayList.Add('[APP_RRDTOOL] "' + RRDTOOL_VERSION() + '"');
    ArrayList.Add('[APP_AWSTATS] "' + AWSTATS_VERSION() + '"');
    ArrayList.Add('[APP_MAILGRAPH] "' + MAILGRAPH_VERSION() + '"');
    ArrayList.Add('</STAT_MODULES>');
    
    ArrayList.Add('<MAIL_MODULES>');
    ArrayList.Add('[APP_DNSMASQ] "' + DNSMASQ_VERSION() + '"');
    ArrayList.Add('[APP_CYRUS] "' + CYRUS_VERSION() + '"');
    ArrayList.Add('[APP_FETCHMAIL] "' + FETCHMAIL_VERSION() + '"');
    ArrayList.Add('[APP_PROCMAIL] "' + PROCMAIL_VERSION() + '"');
    ArrayList.Add('[APP_ROUNDCUBE] "' + ROUNDCUBE_VERSION() + '"');
    ArrayList.Add('[APP_MYSQL] "' + MYSQL_VERSION() + '"');
    ArrayList.Add('</MAIL_MODULES>');

 end;
 //#############################################################################
function MyConf.GEOIP_VERSION():string;
var
   RegExpr:TRegExpr;
   database_path,tempstr:string;
   GeoIP:TGeoIP;
   version:string;
begin
 database_path:='/usr/local/share/GeoIP';
   ForceDirectories(database_path);
   RegExpr:=TRegExpr.Create;
   LOGS:=Tlogs.create;
   if FileExists(database_path + '/GeoIP.dat') then begin
      GeoIP := TGeoIP.Create(database_path + '/GeoIP.dat');
      tempstr:=GeoIP.GetDatabaseInfo;
      RegExpr.expression:='\s+([0-9]+)\s+';
      try
         if RegExpr.Exec(tempstr) then result:=RegExpr.Match[1];
      finally
      GeoIP.Free;
      RegExpr.free;
      end;
   end;

end;
//#############################################################################
//#############################################################################
function MyConf.EMAILRELAY_VERSION():string;
var
   RegExpr:TRegExpr;
   TMP:string;
begin
   if not FileExists('/usr/local/sbin/emailrelay') then exit('0.0.0');
   TMP:=ExecPipe('/usr/local/sbin/emailrelay -V');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='E-MailRelay V([0-9\.]+)';
   if RegExpr.Exec(TMP) then result:=RegExpr.Match[1];
   RegExpr.free;
   
end;
//#############################################################################
function MyConf.RENATTACH_VERSION():string;
var
   RegExpr:TRegExpr;
   TMP:string;
begin
   if not FileExists(get_ARTICA_PHP_PATH() + '/bin/renattach') then exit('0.0.0');
   TMP:=ExecPipe(get_ARTICA_PHP_PATH() + '/bin/renattach -V');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='renattach\s+([0-9\.]+)';
   if RegExpr.Exec(TMP) then result:=RegExpr.Match[1];
   RegExpr.free;

end;
//#############################################################################


function MyConf.GetAllApplisInstalled():string;
begin
 CGI_ALL_APPLIS_INSTALLED();
 writeln(ArrayList.Text);
 end;
//#############################################################################
function MyConf.ROUNDCUBE_VERSION():string;
var
   filepath:string;
   RegExpr:TRegExpr;
   List:TstringList;
   i:integer;
   D:boolean;
begin
      D:=COMMANDLINE_PARAMETERS('debug');
     if not DirectoryExists('/usr/share/roundcube') then begin
        if D then showScreen('ROUNDCUBE_VERSION:: /usr/share/roundcube');
        exit('');
     end;
     

      filepath:='/usr/share/roundcube/index.php';
     if not fileExists(filepath) then begin
        if D then showScreen('ROUNDCUBE_VERSION:: unable to locate ' + filepath);
        exit('');
     end;
     
     
     List:=TstringList.Create;
     List.LoadFromFile(filepath);
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='define\(''RCMAIL_VERSION[\s,'']+([0-9\-\.]+)';
     for i:=0 to List.Count-1 do begin
          if RegExpr.Exec(list.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
          end;
     
     end;

          list.Free;
          RegExpr.free;
end;
//#############################################################################


function MyConf.FETCHMAIL_DAEMON_POOL():string;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('ARTICA','fetchmail_daemon_pool','600');
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.PHP5_INI_PATH():string;
var value:string;
begin
if fileExists('/etc/php5/apache2/php.ini') then exit('/etc/php5/apache2/php.ini');
if fileExists('/etc/php.ini') then exit('/etc/php.ini');
end;
//#############################################################################
function MyConf.PHP5_INI_SET_EXTENSION(librari:string):string;
var
   php_path:string;
   RegExpr:TRegExpr;
   D:Boolean;
   F:TstringList;
   I:integer;
begin
   D:=COMMANDLINE_PARAMETERS('debug');
   php_path:=PHP5_INI_PATH();
   if not FileExists(php_path) then begin
       if D then writeln('Unable to stat ' + php_path);
       exit;
   end;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^extension=' + librari;
    F:=TstringList.Create;
    F.LoadFromFile(php_path);
    for i:=0 to F.Count -1 do begin
       if RegExpr.Exec(f.Strings[i]) then begin
          if D then writeln('Already updated.. : ' + php_path);
           f.Free;
           RegExpr.Free;
           exit;
       end;
    end;
   f.Add('extension=' + librari);
   f.SaveToFile(php_path);
   f.free;
   RegExpr.free;
   
end;
//#############################################################################


function MyConf.FETCHMAIL_DAEMON_POSTMASTER():string;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('ARTICA','fetchmail_daemon_postmaster','root');
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.FETCHMAIL_BIN_PATH():string;
var value:string;
begin
    if FileExists('/usr/bin/fetchmail') then exit('/usr/bin/fetchmail');
    if FileExists('/usr/local/bin/fetchmail') then exit('/usr/local/bin/fetchmail');

end;
//#############################################################################
function MyConf.FETCHMAIL_DAEMON_STOP():string;
begin

    shell(FETCHMAIL_BIN_PATH() + ' -q');
end;
//#############################################################################
function MyConf.PROCMAIL_INSTALLED():boolean;
var
    procmail_bin:string;
    mem:TStringList;
    commandline,res:string;
     RegExpr:TRegExpr;
     i:integer;
     xzedebug:boolean;
begin
     xzedebug:=false;
     if ParamStr(2)='status' then xzedebug:=true;
     
     if xzedebug then writeln('Version............:',PROCMAIL_VERSION());
     
     procmail_bin:=LINUX_APPLICATION_INFOS('procmail_bin');
     if length(procmail_bin)=0 then procmail_bin:='/usr/bin/procmail';
     if not FileExists(procmail_bin) then begin
        if xzedebug then writeln('Path...............:','unable to locate');
        exit(false);
      end;

     if xzedebug then writeln('Path...............:',procmail_bin);
     if xzedebug then writeln('logs Path..........:',PROCMAIL_LOGS_PATH());
     if xzedebug then writeln('user...............:',PROCMAIL_USER());
     if xzedebug then writeln('quarantine path....: ',PROCMAIL_QUARANTINE_PATH());
     if xzedebug then writeln('quarantine size....: ',PROCMAIL_QUARANTINE_SIZE(''));
     if xzedebug then writeln('cyrdeliver path....: ',CYRUS_DELIVER_BIN_PATH());

     mem:=TStringList.Create;
     mem.LoadFromFile('/etc/postfix/master.cf');
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='procmail\s+unix.*pipe';
     for i:=0 to mem.Count-1 do begin
         if RegExpr.Exec(mem.Strings[i]) then begin
             mem.Free;
             RegExpr.free;
             if xzedebug then writeln('master.cf..........:','yes');
             exit(true);
         end;
     end;
     exit(false);

end;

 //#############################################################################
function MyConf.PROCMAIL_READ_QUARANTINE(fromFileNumber:integer;tofilenumber:integer;username:string):TstringList;
const
READ_BYTES = 2048;

Var Info  : TSearchRec;
    Count : Longint;
    path  :string;
    Line:TstringList;
    return_line:string;

Begin
  Count:=0;
  Line:=TstringList.Create;
  if tofilenumber=0 then tofilenumber:=100;
if length(username)=0 then  exit(line);
     if length(username)>0  then path:=PROCMAIL_QUARANTINE_PATH() + '/' + username + '/new';
     
  If FindFirst (path+'/*',faAnyFile and faDirectory,Info)=0 then
    begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin
              Inc(Count);
              if Count>=fromFileNumber then begin
                 return_line:='<file>'+Info.name+'</file>' +  PROCMAIL_READ_QUARANTINE_FILE(path + '/' + info.name);
                 Line.Add(return_line);
                 if ParamStr(1)='-quarantine' then writeln(return_line);
              end;
              if count>=tofilenumber then break;
              //Writeln (Info.Name:40,Info.Size:15);
         end;
      end;

    Until FindNext(info)<>0;
    end;
  FindClose(Info);
  exit(line);
end;
//#############################################################################
function MyConf.PROCMAIL_READ_QUARANTINE_FILE(file_to_read:string):string;
var

    mem:TStringList;
    from,subj,tim:string;
     RegExpr,RegExpr2,RegExpr3:TRegExpr;
     i:integer;
     xzedebug:boolean;
     path:string;
begin
    mem:=TStringList.Create;
    mem.LoadFromFile(file_to_read);
    RegExpr:=TRegExpr.Create;
    RegExpr2:=TRegExpr.Create;
    RegExpr3:=TRegExpr.Create;
    RegExpr.Expression:='^From:\s+(.+)';
    RegExpr2.expression:='Subject:\s+(.+)';
    RegExpr3.expression:='Date:\s+(.+)';
    for i:=0 to mem.Count -1 do begin
        if RegExpr.Exec(mem.Strings[i]) then from:=RegExpr.Match[1];
        if RegExpr2.Exec(mem.Strings[i]) then subj:=RegExpr2.Match[1];
        if RegExpr3.Exec(mem.Strings[i]) then tim:=RegExpr3.Match[1];
        if length(from)+length(subj)+length(tim)>length(from)+length(subj) then break;
    
    end;
    
    RegExpr.free;
    RegExpr2.free;
    mem.free;
    result:='<from>' + from + '</from><time>' + tim + '</time><subject>' + subj + '</subject>';

end;





//#############################################################################
function MyConf.PROCMAIL_QUARANTINE_SIZE(username:string):string;
var
    procmail_bin:string;
    mem:TStringList;
    commandline,res:string;
     RegExpr:TRegExpr;
     i:integer;
     xzedebug:boolean;
     path:string;
begin
     if not fileexists('/usr/bin/du') then begin
        writeln('warning, unable to locate /usr/bin/du tool');
        exit;
     end;
     if length(username)=0 then  path:=PROCMAIL_QUARANTINE_PATH();
     if length(username)>0  then path:=PROCMAIL_QUARANTINE_PATH() + '/' + username;
     
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='([0-9]+)';
     if RegExpr.Exec(trim(ExecPipe('/usr/bin/du -s ' + path))) then begin
     result:=RegExpr.Match[1];
     RegExpr.free;
     exit();
     end;
end;

//#############################################################################
function MyConf.PROCMAIL_QUARANTINE_USER_FILE_NUMBER(username:string):string;
var
   sys:Tsystem;
   count:integer;
   path:string;
begin
     sys:=Tsystem.Create;
     if length(username)=0 then  exit('0');
     if length(username)>0  then path:=PROCMAIL_QUARANTINE_PATH() + '/' + username + '/new';
     count:=sys.DirectoryCountFiles(path);
     sys.free;
     exit(intTostr(count));

end;
//#############################################################################
function MyConf.PROCMAIL_LOGS_PATH():string;
var
    procmail_bin:string;
    mem:TStringList;
    commandline,res:string;
     RegExpr:TRegExpr;
     i:integer;
     xzedebug:boolean;
begin

     if not fileExists('/etc/procmailrc') then exit;
     mem:=TStringList.Create;
      mem.LoadFromFile('/etc/procmailrc');
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='LOGFILE=("|\s|)([a-z\.\/]+)';

     for i:=0 to mem.Count-1 do begin

         if RegExpr.Exec(mem.Strings[i]) then begin
            result:=regExpr.Match[2];
            break;
         end;
     
     end;
      
     regExpr.Free;
     mem.Free;
end;
//#############################################################################
function MyConf.PROCMAIL_USER():string;
var
    procmail_bin:string;
    mem:TStringList;
    commandline,res:string;
     RegExpr:TRegExpr;
     i:integer;
     xzedebug:boolean;
begin
   mem:=TStringList.Create;
   mem.LoadFromFile('/etc/postfix/master.cf');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='flags=([A-Za-z]+)\s+user=([a-zA-Z]+)\s+argv=.+procmail.+';
   for i:=0 to mem.Count-1 do begin
       if RegExpr.Exec(mem.Strings[i]) then begin
          result:=RegExpr.Match[2];
          break;
       end;

     end;
     mem.Free;
     RegExpr.Free;

end;
//#############################################################################
function Myconf.PROCMAIL_VERSION():string;
var
    procmail_bin:string;
    mem:TStringList;
    commandline,res:string;
     RegExpr:TRegExpr;
     i:integer;
begin

     procmail_bin:=LINUX_APPLICATION_INFOS('procmail_bin');
     if length(procmail_bin)=0 then procmail_bin:='/usr/bin/procmail';
     if not FileExists(procmail_bin) then exit;


     mem:=TStringList.Create;
     commandline:='/bin/cat -v ' +procmail_bin ;

     mem.LoadFromStream(ExecStream(commandline,false));
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='v([0-9\.]+)\s+[0-9]{1,4}';

     for i:=0 to mem.Count-1 do begin
       if RegExpr.Exec(mem.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;
     
     end;
     mem.Free;
     RegExpr.Free;
end;
//#############################################################################
function MyConf.DNSMASQ_VERSION:string;
var
   binPath,txt:string;
    mem:TStringList;
    commandline,res:string;
    RegExpr:TRegExpr;
    i:integer;
    D:boolean;
begin
    D:=COMMANDLINE_PARAMETERS('debug');
    binPath:=DNSMASQ_BIN_PATH;

    if not FileExists(binpath) then begin
       if D then ShowScreen('DNSMASQ_VERSION:: unable to stat '+binpath);
       exit;
    end;
    
    commandline:='/bin/cat -v ' +binPath;
    mem:=TStringList.Create;
    mem.LoadFromStream(ExecStream(commandline,false));
    
    
    if D then ShowScreen('DNSMASQ_VERSION:: receive ' + IntToStr(mem.Count) + ' lines');
    
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='dnsmasq-([0-9\.]+)';

     for i:=0 to mem.Count-1 do begin
//     ShowScreen(mem.Strings[i]);
       if RegExpr.Exec(mem.Strings[i]) then begin
          if D then ShowScreen('DNSMASQ_VERSION:: dnsmasq-([0-9\.]+) => ' + RegExpr.Match[1]);
          result:=RegExpr.Match[1];
          break;
       end;

     end;
     mem.Free;
     RegExpr.Free;

end;
//#############################################################################

function Myconf.FETCHMAIL_VERSION():string;
var
    path:string;
    RegExpr:TRegExpr;
    FileData:TStringList;
    i:integer;
begin
     path:=FETCHMAIL_BIN_PATH();
     if not FileExists(path) then exit;
     Shell('/bin/cat -v ' + path + '|grep ''This is fetchmail'' >/tmp/ftech_ver');

     
     FileData:=TStringList.Create;
     RegExpr:=TRegExpr.Create;
     FileData.LoadFromFile('/tmp/ftech_ver');
     RegExpr.Expression:='([0-9\.]+)';
     for i:=0 to FileData.Count -1 do begin
          if RegExpr.Exec(FileData.Strings[i]) then  begin
            result:=RegExpr.Match[1];
            FileData.Free;
            RegExpr.Free;
            exit;
          end;
     end;
end;

//#############################################################################
function Myconf.RRDTOOL_VERSION():string;
var
    path:string;
    RegExpr:TRegExpr;
    FileData:TStringList;
    i:integer;
    D:boolean;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
     path:='/usr/bin/rrdtool';
     if not FileExists(path) then begin
        if D then ShowScreen('RRDTOOL_VERSION:: Unable to stat ' + path);
        exit;
     end;
     FileData:=TStringList.Create;
     FileData.LoadFromStream(ExecStream('/usr/bin/rrdtool',false));
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='([0-9\.]+)';
     if RegExpr.Exec(FileData.Strings[0]) then result:=RegExpr.Match[1];
      RegExpr.Free;
      FileData.Free;
end;
//#############################################################################
function Myconf.SYSTEM_GMT_SECONDS():string;
var value:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=ini.ReadString('ARTICA','GMT_TIME','');
if length(value)=0 then begin
   value:=trim(ExecPipe('/bin/date +%:::z'));
   ini.WriteString('ARTICA','GMT_TIME',value);
end;
result:=value;
ini.Free;
end;
//#############################################################################
function Myconf.SYSTEM_GET_SYS_DATE():string;
var
   value:string;
begin
   value:=trim(ExecPipe('/bin/date +"%Y-%m-%d;%H:%M:%S"'));
   result:=value;
end;
//#############################################################################
function Myconf.SYSTEM_GET_HARD_DATE():string;
var
   value:string;
begin
   value:=trim(ExecPipe('/sbin/hwclock --show'));
   result:=value;
end;
//#############################################################################


//#############################################################################
function Myconf.RRDTOOL_TIMESTAMP(longdate:string):string;
Begin
result:=RRDTOOL_SecondsBetween(longdate);
End ;
//#############################################################################

function Myconf.RRDTOOL_SecondsBetween(longdate:string):string;
var ANow,AThen : TDateTime;
 gmt,commut:string;
 RegExpr:TRegExpr;
 second,seconds:integer;
 parsed:boolean;
 
begin
     gmt:=SYSTEM_GMT_SECONDS();
     parsed:=False;
     //([0-9]+)[\/\-]([0-9]+)[\/\-]([0-9]+) ([0-9]+)\:([0-9]+)\:([0-9]+)
     if notdebug2=false then if debug then writeln('gmt:',gmt);
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='(\+|\-)([0-9]+)';
     RegExpr.Exec(gmt);
     second:=StrToInt(RegExpr.Match[2]);
     seconds:=(second*60)*60;
     if notdebug2=false then begin
        if debug then writeln('GMT seconds:',seconds);
        if debug then writeln('GMT (+-) :('+ RegExpr.Match[1]+ ')');
        if debug then writeln('LONG DATE:('+ longdate+ ')');
     end;
     commut:=RegExpr.Match[1];
     
    if length(longdate)=0 then ANow:=now;
    
    
    
    if length(longdate)>0 then begin
        RegExpr.Expression:='([0-9]+)[\/\-]([0-9]+)[\/\-]([0-9]+)\s+([0-9]+)\:([0-9]+)\:([0-9]+)';
        if RegExpr.exec(longdate) then begin
           if notdebug2=false then if debug then writeln('parse (1): Year (' + RegExpr.Match[1] + ') month(' + RegExpr.Match[2] + ') day(' + RegExpr.Match[3] + ') time: ' +RegExpr.Match[4] + '-' + RegExpr.Match[5] + '-' + RegExpr.Match[6]);
           ANow:=EncodeDateTime(StrToInt(RegExpr.Match[1]), StrToInt(RegExpr.Match[2]), StrToInt(RegExpr.Match[3]), StrToInt(RegExpr.Match[4]), StrToInt(RegExpr.Match[5]), StrToInt(RegExpr.Match[6]), 0);
           parsed:=true;
        end;

        if parsed=false then begin
           RegExpr.Expression:='([0-9]+)[\/\-]([0-9]+)[\/\-]([0-9]+)';
               if RegExpr.exec(longdate) then begin
                  if notdebug2=false then if debug then writeln('parse (2): ' + RegExpr.Match[1] + '-' + RegExpr.Match[2] + '-' + RegExpr.Match[3]);
                  ANow:=EncodeDateTime(StrToInt(RegExpr.Match[1]), StrToInt(RegExpr.Match[2]), StrToInt(RegExpr.Match[3]), 0, 0, 0, 0);
                  parsed:=true;
               end;
       end;
        if parsed=false then begin
           writeln('ERROR : unable to determine date : ' + longdate + ' must be yyyy/mm/dd hh:ii:ss');
           exit;
        end;
      end;
       
       
      AThen:=EncodeDateTime(1970, 1, 1, 0, 0, 0, 0);
      if commut='-' then begin
         if notdebug2=false then if debug then writeln('(-)' + DateTostr(Anow) + ' <> ' + DateTostr(AThen) );
         result:=IntTostr(SecondsBetween(ANow,AThen)+seconds);
      end;

      if commut='+' then begin
         if notdebug2=false then if debug then writeln('(+)' + DateTostr(Anow) + ' <> s' + DateTostr(AThen) );
         result:=IntTostr(SecondsBetween(ANow,AThen)-seconds);
      end;
      if notdebug2=false then if debug then writeln('result:',result);

end;
//#############################################################################

function myconf.ARTICA_FILTER_GET_ALL_PIDS():string;
var
   ps:TStringList;
   articafilter_path,commandline:string;
   i:integer;
   RegExpr:TRegExpr;
   D:boolean;
begin
   ps:=TStringList.CReate;
   D:=COMMANDLINE_PARAMETERS('debug');
articafilter_path:=get_ARTICA_PHP_PATH() + '/bin/artica-filter';
commandline:='/bin/ps -aux';
if D then writeln('ARTICA_FILTER_GET_ALL_PIDS::' +commandline);
   ps.LoadFromStream(ExecStream(commandline,false));
   if ps.Count>0 then begin
       RegExpr:=TRegExpr.Create;
       RegExpr.Expression:='([a-z0-9A-Z]+)\s+([0-9]+).+?'+articafilter_path;
       for i:=0 to ps.count-1 do begin
             //if D then writeln('ARTICA_FILTER_GET_ALL_PIDS::' +ps.Strings[i]);
             if RegExpr.Exec(ps.Strings[i]) then result:=result + RegExpr.Match[2] + ' ';

       end;
       RegExpr.FRee;
   end;
    ps.Free;
end;
//#############################################################################

function Myconf.RRDTOOL_LOAD_AVERAGE():string;
 var filedatas:string;
  RegExpr:TRegExpr;
 Begin
      RegExpr:=TRegExpr.Create;
      
      RegExpr.Expression:='([0-9]+)\.([0-9]+)\s+([0-9]+)\.([0-9]+)\s+([0-9]+)\.([0-9]+)';
      filedatas:=ReadFileIntoString('/proc/loadavg');
      if RegExpr.Exec(filedatas) then begin
         if debug then writeln('RRDTOOL_LOAD_AVERAGE:',RegExpr.Match[1]+RegExpr.Match[2]+';' +RegExpr.Match[3]+RegExpr.Match[4] + ';' +RegExpr.Match[5]+RegExpr.Match[6]);
          result:=RegExpr.Match[1]+RegExpr.Match[2]+';' +RegExpr.Match[3]+RegExpr.Match[4] + ';' +RegExpr.Match[5]+RegExpr.Match[6];
      
      end;
      RegExpr.Free;


end;
//#############################################################################
function Myconf.RRDTOOL_LOAD_CPU(rddtool:boolean):string;
 var filedatas:TstringList;
  RegExpr:TRegExpr;
  user:single;
  nice:single;
  system:single;
  rddpath,comma:string;
 Begin
 
 
       if debug then writeln('###################################################');
      if debug then writeln('############# RRDTOOL_LOAD_CPU #################');
      if debug then writeln('###################################################');
 
      RegExpr:=TRegExpr.Create;
      filedatas:=TstringList.Create;
      RegExpr.Expression:='cpu\s+([0-9]+)\s+([0-9]+)\s+([0-9]+).+';
      filedatas.LoadFromFile('/proc/stat');
      
      if debug then writeln('RRDTOOL_LOAD_CPU:/proc/stat "' + filedatas.Strings[0] + '"');
      
      if RegExpr.Exec(filedatas.Strings[0]) then begin
          user:=StrToInt(RegExpr.Match[1]);
          user:=int((user+nice)/100);

          
          
          
          nice:=StrToInt(RegExpr.Match[2]);
          nice:=int(nice/100);
          system:=StrToInt(RegExpr.Match[3]);
          system:=int(system/100);
          if debug then begin
             writeln('RRDTOOL_LOAD_CPU:user:',user);
             writeln('RRDTOOL_LOAD_CPU:nice:',nice);
             writeln('RRDTOOL_LOAD_CPU:system:',system);
          end;
           result:=FloatToStr(user) + ';' + FloatToStr(nice)  +';'+ FloatToStr(system);
            if debug then writeln('RRDTOOL_LOAD_CPU:',result);
      end;
      
      if rddtool then begin
         rddpath:=RRDTOOL_STAT_LOAD_CPU_DATABASE_PATH();
         comma:='/usr/bin/rrdtool update ' + rddpath + ' ' + RRDTOOL_TIMESTAMP('')+ ':' + FloatToStr(user) + ':' + FloatToStr(nice) +':'+ FloatToStr(system) + ' >/tmp/rrd.generate.dustbin' ;
         if debug then writeln(comma);
         shell(comma);
      end;
      RegExpr.Free;
      filedatas.free;
       if debug then writeln('###################################################');
end;
//#############################################################################

function Myconf.RRDTOOL_LOAD_MEMORY(rddtool:boolean):string;
 var filedatas:TstringList;
  RegExpr:TRegExpr;
  MemFree:integer;
  Cached:integer;
  Buffer:integer;
  mem_ram_libre:integer;
  mem_ram_util:integer;
  mem_virtu_libre:integer;
  mem_virtu_util:integer;
  MemTotal:integer;
  SwapFree:integer;
  SwapTotal:integer;
  i:integer;
  rddpath,comma:string;
 Begin
      RegExpr:=TRegExpr.Create;
      filedatas:=TstringList.Create;
      if debug then writeln('###################################################');
      if debug then writeln('############# RRDTOOL_LOAD_MEMORY #################');
      if debug then writeln('###################################################');
      
      
      
      rddpath:=RRDTOOL_STAT_LOAD_MEMORY_DATABASE_PATH();
      if not fileexists(rddpath) then begin
           if debug then writeln('Generating ' + rddpath + ' database');
           shell('/usr/bin/rrdtool create ' + rddpath +' DS:mem_ram_libre:GAUGE:576:0:U DS:mem_ram_util:GAUGE:576:0:U DS:mem_virtu_libre:GAUGE:576:0:U DS:mem_virtu_util:GAUGE:576:0:U RRA:AVERAGE:0.5:1:576 RRA:AVERAGE:0.5:12:168 RRA:AVERAGE:0.5:144:62 RRA:AVERAGE:0.5:288:366');
      end;
      
      

      filedatas.LoadFromFile('/proc/meminfo');
      for i:=0 to  filedatas.Count -1 do begin
          RegExpr.Expression:='MemFree\:\s+([0-9]+)';
          if RegExpr.exec(filedatas.Strings[i]) then MemFree:=strToInt(RegExpr.Match[1]);
          RegExpr.Expression:='Cached\:\s+([0-9]+)';
          if RegExpr.exec(filedatas.Strings[i]) then Cached:=strToInt(RegExpr.Match[1]);
          RegExpr.Expression:='Buffers\:\s+([0-9]+)';
          if RegExpr.exec(filedatas.Strings[i]) then Buffer:=strToInt(RegExpr.Match[1]);
          RegExpr.Expression:='MemTotal\:\s+([0-9]+)';
          if RegExpr.exec(filedatas.Strings[i]) then MemTotal:=strToInt(RegExpr.Match[1]);

          RegExpr.Expression:='MemFree\:\s+([0-9]+)';
          if RegExpr.exec(filedatas.Strings[i]) then MemFree:=strToInt(RegExpr.Match[1]);
          
          RegExpr.Expression:='SwapFree\:\s+([0-9]+)';
          if RegExpr.exec(filedatas.Strings[i]) then SwapFree:=strToInt(RegExpr.Match[1]);
          
          RegExpr.Expression:='SwapTotal\:\s+([0-9]+)';
          if RegExpr.exec(filedatas.Strings[i]) then SwapTotal:=strToInt(RegExpr.Match[1]);

          mem_ram_util:=MemTotal-(MemFree-Cached-Buffer);
          mem_ram_libre:= MemFree+Cached+Buffer;
          mem_virtu_libre:=SwapFree;
          mem_virtu_util:=SwapTotal-SwapFree;
          
          
          
      end;
       comma:='/usr/bin/rrdtool update ' + rddpath + ' ' +  RRDTOOL_TIMESTAMP('') + ':'+IntToStr(mem_ram_libre) + ':' + IntToStr(mem_ram_util) + ':' + IntToStr(mem_virtu_libre) +':' + IntTostr(mem_virtu_util);
       if debug then writeln(comma);
       Shell(comma);

      RegExpr.Free;
      filedatas.free;
      if debug then writeln('###################################################');

end;

//#############################################################################
procedure Myconf.QUEUEGRAPH_IMAGES();
var debugC:boolean;
temp_path,cgi_path:string;

begin
debugC:=false;
if ParamStr(1)='-queuegraph' then debugC:=true;
cgi_path:=get_ARTICA_PHP_PATH() + '/bin/queuegraph/queuegraph1.cgi';
temp_path:=QUEUEGRAPH_TEMP_PATH();
if not fileExists(temp_path) then begin
      if debugC then ShowScreen('QUEUEGRAPH_IMAGES::Error::Unable to locate cgi temp file...');
      exit;
end;

if not fileExists(cgi_path) then begin
      if debugC then ShowScreen('QUEUEGRAPH_IMAGES::Error::Unable to locate cgi executable file...');
      exit;
end;
if debugC then ShowScreen('QUEUEGRAPH_IMAGES::Shell "'+cgi_path+'"');
shell(cgi_path);
cgi_path:='/bin/mv ' + temp_path+'/* ' +get_ARTICA_PHP_PATH() + '/img/';
if debugC then ShowScreen('QUEUEGRAPH_IMAGES::Shell "'+cgi_path+'"');
shell(cgi_path);
cgi_path:='/bin/chmod 655 ' + get_ARTICA_PHP_PATH() + '/img/queuegraph*';
if debugC then ShowScreen('QUEUEGRAPH_IMAGES::Shell "'+cgi_path+'"');
shell(cgi_path);

end;
//#############################################################################
function Myconf.YOREL_RECONFIGURE(database_path:string):string;
var      artica_path,create_path,upd_path,image_path,du_path,cron_command,andalemono_path:string;
         list:TStringList;
         RegExpr:TRegExpr;
         i:integer;
         sys:Tsystem;
begin

   if not FileExists('/usr/bin/rrdtool') then begin
         ShowScreen('YOREL_RECONFIGURE:: WARNING !!! unable to locate rrdtool : usually in /usr/bin/rrdtool, process cannot continue...');
         exit;
   end;

 artica_path:=get_ARTICA_PHP_PATH() + '/bin/yorhel-rrd';
 image_path:=get_ARTICA_PHP_PATH() + '/img';
 andalemono_path:=artica_path + '/rrds/andalemono';
 create_path:=artica_path + '/create';
 upd_path:=artica_path+'/upd';
 du_path:='/usr/bin/du';
 if length(database_path)=0 then database_path:='/var/rrd_database';
 if not DirectoryExists(artica_path) then begin
      ShowScreen('YOREL_RECONFIGURE::Unable to stat ' + artica_path);
      exit;
 end;
  if not DirectoryExists(database_path) then begin
      ShowScreen('YOREL_RECONFIGURE::Create ' + database_path);
      ForceDirectories(database_path);
 end;
 
  if not FileExists(andalemono_path) then begin
      ShowScreen('YOREL_RECONFIGURE::Unable to stat ' + andalemono_path);
      exit;
 end;
 
  if not FileExists(create_path) then begin
      ShowScreen('YOREL_RECONFIGURE::Unable to stat ' + create_path);
      exit;
 end;
 
   if not FileExists(du_path) then begin
      ShowScreen('YOREL_RECONFIGURE::Unable to stat ' + du_path);
      exit;
 end;
 
   if not FileExists(upd_path) then begin
      ShowScreen('YOREL_RECONFIGURE::Unable to stat ' + upd_path);
      exit;
 end;
 
   ShowScreen('YOREL_RECONFIGURE::Change path in "create" perl file');
   list:=TStringList.create;
   RegExpr:=TRegExpr.Create;
   
   list.LoadFromFile(create_path);
   RegExpr.Expression:='my \$path[\s= ]+';
   for i:=0 to  list.Count do begin
      if RegExpr.Exec(list.Strings[i]) then begin
         ShowScreen('YOREL_RECONFIGURE::Change path in to "' + database_path + '"');
         list.Strings[i]:='my $path=''' +  database_path + ''';';
         list.SaveToFile(create_path);
         break;
      end;
   end;
  ShowScreen('YOREL_RECONFIGURE::Change path in "upd" perl file ->' + database_path);
  
   
   RegExpr.Expression:='^my \$rdir';
    list.LoadFromFile(upd_path);
       for i:=0 to  list.Count do begin
      if RegExpr.Exec(list.Strings[i]) then begin
         ShowScreen('YOREL_RECONFIGURE::Change path in to "' + database_path + '"');
         list.Strings[i]:='my $rdir=''' +  database_path + ''';';
         list.SaveToFile(upd_path);
         break;
      end;
   end;
  ShowScreen('YOREL_RECONFIGURE::Change path in "upd" perl file ->' + image_path);


   RegExpr.Expression:='^my \$gdir';
    list.LoadFromFile(upd_path);
       for i:=0 to  list.Count do begin
           if RegExpr.Exec(list.Strings[i]) then begin
              ShowScreen('YOREL_RECONFIGURE::Change path in to "' + image_path + '"');
              list.Strings[i]:='my $gdir=''' +  image_path + ''';';
              list.SaveToFile(upd_path);
              break;
           end;
   end;

     RegExpr.Free;
     list.free;
   
   sys:=Tsystem.Create();
   if sys.DirectoryCountFiles(database_path)=0 then begin
       ShowScreen('YOREL_RECONFIGURE::Create rrd databases in "' + database_path + '"');
       Shell(create_path);
   
   end;
  if sys.DirectoryCountFiles(database_path)=0 then begin
       sys.Free;
       ShowScreen('YOREL_RECONFIGURE::Error, there was a problem while creating rrd databases in "' + database_path + '"');
       exit;
  end;

  ShowScreen('YOREL_RECONFIGURE::Creating the cron script in order automically generate statistics');
  list:=TstringList.Create;
  list.Add('#!/bin/bash');
  list.Add('');
  list.Add('# HDD usage is collected with the following command,');
  list.Add('#  which can only be run as root');
  list.Add(du_path+' -sb /var/spool /var/log /usr/share >'+database_path+'/hddusage');
  list.Add('/bin/chmod 644 '+database_path+'/hddusage');
  list.Add(upd_path);
  list.Add('/bin/rm -f '+ database_path+'/hddusage');
  list.SaveToFile(database_path + '/yorel_cron');
  Shell('/bin/chmod 777 ' + database_path + '/yorel_cron');
  Shell('/bin/cp ' + andalemono_path + ' ' + database_path+'/andalemono');
  list.free;
  
  cron_command:='1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41,43,45,47,49,51,53,55,57,59 * * * *' + chr(9) + 'root' + chr(9) + database_path + '/yorel_cron >/dev/null';
if DirectoryExists('/etc/cron.d') then begin
     list:=TstringList.Create;
     list.Add(cron_command);
     list.SaveToFile('/etc/cron.d/artica_yorel');
     list.Free;
end;
  Shell('/etc/init.d/cron restart');
  ShowScreen('YOREL_RECONFIGURE::Done...');
     
end;

//#############################################################################
function Myconf.QUEUEGRAPH_TEMP_PATH():string;
var debugC:boolean;
list:TStringList;
cgi_path:string;
  RegExpr:TRegExpr;
  i:integer;
begin
debugC:=false;
if ParamStr(1)='-queuegraph' then debugC:=true;
cgi_path:=get_ARTICA_PHP_PATH() + '/bin/queuegraph/queuegraph1.cgi';

if not FileExists(cgi_path) then begin
   if debugC then ShowScreen('QUEUEGRAPH_TEMP_PATH::unable to locate ' + cgi_path);
   exit;
end;
list:=TStringList.Create;
list.LoadFromFile(cgi_path);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='my \$tmp_dir[=''"\s+]+([a-zA-Z\/_\-0-9]+)';
  for i:=0 to list.Count-1 do begin
        if RegExpr.Exec(list.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
  
  end;
  if debugC then ShowScreen('QUEUEGRAPH_TEMP_PATH:: Path="' + result + '"');
  list.free;
  RegExpr.free;
end;
//#############################################################################
procedure Myconf.RDDTOOL_POSTFIX_MAILS_CREATE_DATABASE();
var
   database_path,date,command:string;
   sday:integer;
begin
     database_path:=RRDTOOL_STAT_POSTFIX_MAILS_SENT_DATABASE_PATH();
     if debug then writeln('RDDTOOL_POSTFIX_MAILS_CREATE_DATABASE');
     if debug then writeln('Testing database "' + database_path + '"');

     if not fileexists(database_path) then begin
        sday:=DayOf(now);
        sday:=sday-2;
        date:=IntTostr(YearOf(now)) + '-' +IntToStr(MonthOf(now)) + '-' + intTostr(sday) + ' 00:00:00';
                if debug then writeln('Creating database..start yesterday ' + date);
        date:=RRDTOOL_SecondsBetween(date);
        command:='/usr/bin/rrdtool create ' + database_path + ' --start ' + date + ' DS:mails:ABSOLUTE:60:0:U RRA:AVERAGE:0.5:1:60';
        if debug then writeln(command);
        shell(command);

        if debug then writeln('Creating database..done..');
     end;
end;


//#############################################################################
procedure Myconf.RDDTOOL_POSTFIX_MAILS_SENT_STATISTICS();
  var filedatas:TstringList;
  var maillog_path,rdd_sent_path,formated_date,new_formated_date,mem_formated_date:string;
  logs:Tlogs;
  RegExpr:TRegExpr;
  i:integer;
  month,year,countlines:integer;
  
begin
     logs:=Tlogs.Create;
     maillog_path:=get_LINUX_MAILLOG_PATH();
     if length(maillog_path)=0  then begin
           logs.logs('RDDTOOL_POSTFIX_MAILS_SENT_STATISTICS:: unable to stat maillog...aborting');
           if debug then writeln('unable to locate maillog path');
           logs.Free;
           exit;
     end;
     notdebug2:=true;
     if debug then writeln('reading ' +  maillog_path);
     rdd_sent_path:=RRDTOOL_STAT_POSTFIX_MAILS_SENT_DATABASE_PATH();
     countlines:=1;
     year:=YearOf(now);
     RegExpr:=TRegExpr.Create;
     filedatas:=TstringList.Create;
     filedatas.LoadFromFile(maillog_path);
     if debug then writeln('starting parsing lines number ',filedatas.Count);
     RegExpr.Expression:='([a-zA-Z]+)\s+([0-9]+)\s+([0-9\:]+).+postfix/(smtp|lmtp).+to=<(.+)>,\s+relay=(.+),.+status=sent.+';
     
     
     for i:=0 to filedatas.Count -1 do begin
         if RegExpr.Exec(filedatas.Strings[i]) then begin
               month:=GetMonthNumber(RegExpr.Match[1]);
               if debug then writeln(filedatas.Strings[i]);
                formated_date:=intTostr(year) + '-' + intTostr(month) + '-' + RegExpr.Match[2] + ' ' + RegExpr.Match[3];
                new_formated_date:=RRDTOOL_SecondsBetween(formated_date);
                if debug then writeln( new_formated_date + '/' +  mem_formated_date);
                if mem_formated_date=new_formated_date then begin
                    countlines:=countlines+1;
                    if debug then writeln( formated_date +  ' increment 1 ('+IntToStr(countlines)+')');
                end else begin
                    if debug then writeln( formated_date +' ' + new_formated_date + ' ' + RegExpr.Match[5] +  '('+IntToStr(countlines)+')->ADD');
                    shell('/usr/bin/rrdtool update ' + rdd_sent_path + ' ' + new_formated_date+ ':' + IntToStr(countlines));
                    mem_formated_date:=new_formated_date;
                    countlines:=1;
                end;
                
         end;
     
     end;
     RegExpr.Free;
     filedatas.Free;
     logs.Free;
     

end;
//#############################################################################

procedure Myconf.RDDTOOL_POSTFIX_MAILS_SENT_GENERATE();
var
   commandline:string;
   database_path:string;
   php_path,gif_path,gwidth,gheight:string;
begin
  php_path:=get_ARTICA_PHP_PATH();
  gwidth:=RRDTOOL_GRAPH_WIDTH();
  gheight:=RRDTOOL_GRAPH_HEIGHT();
  database_path:=RRDTOOL_STAT_POSTFIX_MAILS_SENT_DATABASE_PATH();

  gif_path:=php_path + '/img/LOAD_MAIL-SENT-1.gif';
commandline:='/usr/bin/rrdtool graph ' + gif_path + ' -t "Mails sent pear day" -v "Mails number" -w '+gwidth+' -h '+gheight+' --start -1day ';
commandline:=commandline + 'DEF:mem_ram_libre='+database_path+':mem_ram_libre:AVERAGE  ';
///usr/bin/rrdtool graph /home/touzeau/developpement/artica-postfix/img/LOAD_MAIL-SENT-1.gif -t "Mails sent pear day" -v "Mails number" -w 550 -h 550 --start -1day DEF:mails=/home/touzeau/developpement/artica-postfix/ressources/rrd/postfix-mails-sent.rdd:mails:AVERAGE LINE1:mails\#FFFF00:"Emails number"
           if debug then writeln(commandline);

Shell(commandline + ' >/tmp/rrd.generate.dustbin');
  if FileExists(gif_path) then shell('/bin/chmod 755 ' + gif_path);

end;

//###########################################################################



//#############################################################################
function Myconf.GetMonthNumber(MonthName:string):integer;
begin
 if MonthName='Jan' then exit(1);
 if MonthName='Feb' then exit(2);
 if MonthName='Mar' then exit(3);
 if MonthName='Apr' then exit(4);
 if MonthName='May' then exit(5);
 if MonthName='Jun' then exit(6);
 if MonthName='Jul' then exit(7);
 if MonthName='Aug' then exit(8);
 if MonthName='Sep' then exit(9);
 if MonthName='Oct' then exit(10);
 if MonthName='Nov'  then exit(11);
 if MonthName='Dec'  then exit(12);
 if MonthName='jan' then exit(1);
 if MonthName='feb' then exit(2);
 if MonthName='mar' then exit(3);
 if MonthName='apr' then exit(4);
 if MonthName='may' then exit(5);
 if MonthName='jun' then exit(6);
 if MonthName='jul' then exit(7);
 if MonthName='aug' then exit(8);
 if MonthName='sep' then exit(9);
 if MonthName='oct' then exit(10);
 if MonthName='nov'  then exit(11);
 if MonthName='dec'  then exit(12);
end;
//#############################################################################


procedure Myconf.RDDTOOL_LOAD_MEMORY_GENERATE();
var
   commandline:string;
   database_path:string;
   php_path,gif_path,gwidth,gheight:string;
begin
  php_path:=get_ARTICA_PHP_PATH();
  gwidth:=RRDTOOL_GRAPH_WIDTH();
  gheight:=RRDTOOL_GRAPH_HEIGHT();
  database_path:=RRDTOOL_STAT_LOAD_MEMORY_DATABASE_PATH();

  gif_path:=php_path + '/img/LOAD_MEMORY-1.gif';
commandline:='/usr/bin/rrdtool graph ' + gif_path + ' -t "SYSTEM memory pear day" -v "memory bytes" -w '+gwidth+' -h '+gheight+' --start -1day ';
commandline:=commandline + 'DEF:mem_ram_libre='+database_path+':mem_ram_libre:AVERAGE  ';
commandline:=commandline + 'DEF:mem_ram_util='+database_path+':mem_ram_util:AVERAGE  ';
commandline:=commandline + 'DEF:mem_virtu_libre='+database_path+':mem_virtu_libre:AVERAGE  ';
commandline:=commandline + 'DEF:mem_virtu_util='+database_path+':mem_virtu_util:AVERAGE ';
commandline:=commandline + 'CDEF:mem_virtu_libre_tt=mem_virtu_util,mem_virtu_libre,+,1024,* ';
commandline:=commandline + 'CDEF:mem_virtu_util_tt=mem_virtu_util,1024,* ';
commandline:=commandline + 'CDEF:mem_ram_tt=mem_ram_util,mem_ram_libre,+,1024,* ';
commandline:=commandline + 'CDEF:mem_ram_util_tt=mem_ram_util,1024,* ';
commandline:=commandline + 'LINE3:mem_ram_util_tt\#FFFF00:"RAM used" ';
commandline:=commandline + 'LINE2:mem_virtu_util_tt\#FF0000:"Virtual RAM used\n" ';
commandline:=commandline + 'GPRINT:mem_ram_tt:LAST:"RAM  Free %.2lf %s |" ';
commandline:=commandline + 'GPRINT:mem_ram_util_tt:MAX:"RAM  MAX used %.2lf %s |" ';
commandline:=commandline + 'GPRINT:mem_ram_util_tt:AVERAGE:"RAM average util %.2lf %s |" ';
commandline:=commandline + 'GPRINT:mem_ram_util_tt:LAST:"RAM  CUR util %.2lf %s\n" ';
commandline:=commandline + 'GPRINT:mem_virtu_libre_tt:LAST:"Swap Free %.2lf %s |" ';
commandline:=commandline + 'GPRINT:mem_virtu_util_tt:MAX:"Swap MAX used %.2lf %s |" ';
commandline:=commandline + 'GPRINT:mem_virtu_util_tt:AVERAGE:"Swap AVERAGE used %.2lf %s |" \';
commandline:=commandline + 'GPRINT:mem_virtu_util_tt:LAST:"Swap Current used %.2lf %s"';
           if debug then writeln(commandline);

Shell(commandline + ' >/tmp/rrd.generate.dustbin');
  if FileExists(gif_path) then shell('/bin/chmod 755 ' + gif_path);

end;

//#############################################################################




//#############################################################################
procedure Myconf.RDDTOOL_LOAD_AVERAGE_GENERATE();
var
   commandline:string;
   database_path:string;
   php_path,gif_path,gwidth,gheight:string;
begin
  php_path:=get_ARTICA_PHP_PATH();
  gwidth:=RRDTOOL_GRAPH_WIDTH();
  gheight:=RRDTOOL_GRAPH_HEIGHT();
  database_path:=RRDTOOL_STAT_LOAD_AVERAGE_DATABASE_PATH();
  
  gif_path:=php_path + '/img/LOAD_AVERAGE-1.gif';
  commandline:='/usr/bin/rrdtool graph ' + gif_path + ' -t "SYSTEM LOAD pear day" -v "Charge x 100" -w '+gwidth+' -h '+gheight+' --start -1day ';
  commandline:=commandline + 'DEF:charge_1min=' + database_path + ':charge_1min:AVERAGE ';
  commandline:=commandline + 'DEF:charge_5min=' + database_path + ':charge_5min:AVERAGE ';
  commandline:=commandline + 'DEF:charge_15min=' + database_path + ':charge_15min:AVERAGE ';
  commandline:=commandline + 'LINE2:charge_1min\#FF0000:"Load 1 minute" ';
  commandline:=commandline + 'LINE2:charge_5min\#00FF00:"load 5 minute" ';
  commandline:=commandline + 'LINE2:charge_15min\#0000FF:"load 15 minute \n" ';
  commandline:=commandline + 'GPRINT:charge_1min:MAX:"System load  1 minute  \: MAX %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:charge_1min:AVERAGE:"AVERAGE %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:charge_1min:LAST:"CUR %.2lf %s \n" ';
  commandline:=commandline + 'GPRINT:charge_5min:MAX:"System load  5 minutes \: MAX %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:charge_5min:AVERAGE:"AVERAGE %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:charge_5min:LAST:"CUR %.2lf %s \n" ';
  commandline:=commandline + 'GPRINT:charge_15min:MAX:"System Load 15 minutes \: MAX %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:charge_15min:AVERAGE:"AVERAGE %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:charge_15min:LAST:"CUR %.2lf %s \n"';
  Shell(commandline + ' >/tmp/rrd.generate.dustbin');
  if FileExists(gif_path) then shell('/bin/chmod 755 ' + gif_path);

end;

//#############################################################################
procedure Myconf.RDDTOOL_LOAD_CPU_GENERATE();
var
   commandline:string;
   database_path:string;
   php_path,gif_path,gwidth,gheight:string;
begin
  php_path:=get_ARTICA_PHP_PATH();
  gwidth:=RRDTOOL_GRAPH_WIDTH();
  gheight:=RRDTOOL_GRAPH_HEIGHT();
  database_path:=RRDTOOL_STAT_LOAD_CPU_DATABASE_PATH();
  gif_path:=php_path + '/img/LOAD_CPU-1.gif';
  
commandline:='/usr/bin/rrdtool graph ' + gif_path + ' -t "CPU on day" -v "Util CPU 1/100 Seconds" -w '+gwidth+' -h '+gheight+' --start -1day ';
  commandline:=commandline + 'DEF:utilisateur='+ database_path+':utilisateur:AVERAGE ';
  commandline:=commandline + 'DEF:nice='+ database_path+':nice:AVERAGE ';
  commandline:=commandline + 'DEF:systeme='+ database_path+':systeme:AVERAGE ';
  commandline:=commandline + 'CDEF:vtotale=utilisateur,systeme,+ ';
  commandline:=commandline + 'CDEF:vutilisateur=vtotale,1,GT,0,utilisateur,IF ';
  commandline:=commandline + 'CDEF:vnice=vtotale,1,GT,0,nice,IF ';
  commandline:=commandline + 'CDEF:vsysteme=vtotale,1,GT,0,systeme,IF ';
  commandline:=commandline + 'CDEF:vtotalectrl=vtotale,1,GT,0,vtotale,IF ';
  commandline:=commandline + 'LINE2:vutilisateur\#FF0000:"User" ';
  commandline:=commandline + 'LINE2:vnice\#0000FF:"Nice" ';
  commandline:=commandline + 'LINE2:vsysteme\#00FF00:"system" ';
  commandline:=commandline + 'LINE2:vtotalectrl\#FFFF00:"sum \n" ';
  commandline:=commandline + 'GPRINT:vutilisateur:MAX:"CPU user \: MAX %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vutilisateur:AVERAGE:"AVERAGE %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vutilisateur:LAST:"CUR %.2lf %s \n" ';
  commandline:=commandline + 'GPRINT:vnice:MAX:"CPU nice  \: MAX %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vnice:AVERAGE:"AVERAGE %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vnice:LAST:"CUR %.2lf %s \n" ';
  commandline:=commandline + 'GPRINT:vsysteme:MAX:"CPU  system   \: MAX %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vsysteme:AVERAGE:"AVERAGE %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vsysteme:LAST:"CUR %.2lf %s \n" ';
  commandline:=commandline + 'GPRINT:vtotalectrl:MAX:"Total  CPU    \: MAX %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vtotalectrl:AVERAGE:"AVERAGE %.2lf %s |" ';
  commandline:=commandline + 'GPRINT:vtotalectrl:LAST:"CUR %.2lf %s \n"';

  if debug then writeln(commandline);
  Shell(commandline + ' >/tmp/rrd.generate.dustbin');

  if FileExists(gif_path) then shell('/bin/chmod 755 ' + gif_path);
end;
//#############################################################################

procedure Myconf.RDDTOOL_LOAD_CPU_TOOLTIP_GENERATE();
var
   commandline:string;
   database_path:string;
   php_path,gif_path,gwidth,gheight:string;
begin
  php_path:=get_ARTICA_PHP_PATH();
  gwidth:=RRDTOOL_GRAPH_WIDTH();
  gheight:=RRDTOOL_GRAPH_HEIGHT();
  database_path:=RRDTOOL_STAT_LOAD_CPU_DATABASE_PATH();
  gif_path:=php_path + '/img/LOAD_CPU-1-0.gif';

commandline:='/usr/bin/rrdtool graph ' + gif_path + ' -w 50 -h 50 -A -g -j --start -1day ';
  commandline:=commandline + 'DEF:utilisateur='+ database_path+':utilisateur:AVERAGE ';
  commandline:=commandline + 'DEF:nice='+ database_path+':nice:AVERAGE ';
  commandline:=commandline + 'DEF:systeme='+ database_path+':systeme:AVERAGE ';
  commandline:=commandline + 'CDEF:vtotale=utilisateur,systeme,+ ';
  commandline:=commandline + 'CDEF:vutilisateur=vtotale,1,GT,0,utilisateur,IF ';
  commandline:=commandline + 'CDEF:vnice=vtotale,1,GT,0,nice,IF ';
  commandline:=commandline + 'CDEF:vsysteme=vtotale,1,GT,0,systeme,IF ';
  commandline:=commandline + 'CDEF:vtotalectrl=vtotale,1,GT,0,vtotale,IF ';
  commandline:=commandline + 'LINE2:vutilisateur\#FF0000:"" ';


  if debug then writeln(commandline);
  Shell(commandline+' >/tmp/rrd.generate.dustbin');

  if FileExists(gif_path) then shell('/bin/chmod 755 ' + gif_path);
end;
//#############################################################################

function Myconf.RRDTOOL_GRAPH_WIDTH():string;
var value,phppath,path:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix-rdd.conf');
value:=ini.ReadString('ARTICA','RRDTOOL_GRAPH_WIDTH','');
if length(value)=0 then  begin
   if debug then writeln('RRDTOOL_GRAPH_WIDTH is not set in ini');
   if debug then writeln('set RRDTOOL_GRAPH_WIDTH to 450');
   value:='550';
   ini.WriteString('ARTICA','RRDTOOL_GRAPH_WIDTH','450');
end;
result:=value;
ini.Free;
end;
//#############################################################################
function Myconf.RRDTOOL_GRAPH_HEIGHT():string;
var value,phppath,path:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix-rdd.conf');
value:=ini.ReadString('ARTICA','RRDTOOL_GRAPH_HEIGHT','');
if length(value)=0 then  begin
   if debug then writeln('RRDTOOL_GRAPH_WIDTH is not set in ini');
   if debug then writeln('set RRDTOOL_GRAPH_HEIGHT to 170');
   value:='550';
   ini.WriteString('ARTICA','RRDTOOL_GRAPH_HEIGHT','170');
end;
result:=value;
ini.Free;
end;
//#############################################################################
function MyConf.RRDTOOL_STAT_LOAD_CPU_DATABASE_PATH():string;
var value,phppath,path:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix-rdd.conf');
value:=ini.ReadString('ARTICA','STAT_CPU_PATH','');
if length(value)=0 then  begin
   if debug then writeln('STAT_LOAD_PATH is not set in ini path');
   phppath:=get_ARTICA_PHP_PATH();
   path:=phppath+'/ressources/rrd/cpu.rdd';
   if debug then writeln('set STAT_CPU_PATH to '+path);
   value:=path;
   ini.WriteString('ARTICA','STAT_CPU_PATH',path);
   if debug then writeln('done..'+path);
end;
result:=value;
ini.Free;
end;
//#############################################################################
function MyConf.RRDTOOL_STAT_LOAD_MEMORY_DATABASE_PATH():string;
var value,phppath,path:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix-rdd.conf');
value:=ini.ReadString('ARTICA','STAT_MEM_PATH','');
if length(value)=0 then  begin
   if debug then writeln('STAT_LOAD_PATH is not set in ini path');
   phppath:=get_ARTICA_PHP_PATH();
   path:=phppath+'/ressources/rrd/mem.rdd';
   if debug then writeln('set STAT_MEM_PATH to '+path);
   value:=path;
   ini.WriteString('ARTICA','STAT_MEM_PATH',path);
   if debug then writeln('done..'+path);
end;
result:=value;
ini.Free;
end;
//#############################################################################
function MyConf.RRDTOOL_STAT_POSTFIX_MAILS_SENT_DATABASE_PATH():string;
var value,phppath,path:string;
ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix-rdd.conf');
value:=ini.ReadString('ARTICA','STAT_MAIL_SENT_PATH','');
if length(value)=0 then  begin
   if debug then writeln('STAT_MAIL_PATH is not set in ini path');
   phppath:=get_ARTICA_PHP_PATH();
   path:=phppath+'/ressources/rrd/postfix-mails-sent.rdd';
   if debug then writeln('set STAT_MAIL_SENT_PATH to '+path);
   value:=path;
   ini.WriteString('ARTICA','STAT_MAIL_SENT_PATH',path);
   if debug then writeln('done..'+path);
end;
result:=value;
ini.Free;
end;
//#############################################################################
function Myconf.KAS_VERSION():string;
var
    path:string;
    RegExpr:TRegExpr;
    FileData:TStringList;
    i:integer;
begin
     path:='/usr/local/ap-mailfilter3/bin/curvers';
     if not FileExists('/usr/local/ap-mailfilter3/bin/curvers') then exit;
     FileData:=TStringList.Create;
     RegExpr:=TRegExpr.Create;
     FileData.LoadFromFile(path);
     RegExpr.Expression:='CUR_PRODUCT_VERSION="([0-9\.]+)"';
     for i:=0 to FileData.Count -1 do begin
          if RegExpr.Exec(FileData.Strings[i]) then  begin
            result:=RegExpr.Match[1];
            FileData.Free;
            RegExpr.Free;
            exit;
          end;
     end;
end;

//#############################################################################
function Myconf.POSTFIX_VERSION():string;
var
    path,ver:string;
begin
   path:='/usr/sbin/postconf';
   if not FileExists(path) then exit;
   ver:=ExecPipe(path + ' -h mail_version');
   exit(trim(ver));
   
end;

//#############################################################################
function Myconf.LDAP_VERSION():string;
var
    path,ver:string;
    RegExpr:TRegExpr;
    commandline:string;
    D:Boolean;
begin
   D:=COMMANDLINE_PARAMETERS('debug');
   
   if fileExists('/usr/sbin/slapd') then path:='/usr/sbin/slapd';
   if FileExists('/usr/lib/openldap/slapd') then path:='/usr/lib/openldap/slapd';
   if not FileExists(path) then begin
      if D then ShowScreen('LDAP_VERSION:: Unable to locate slapd bin');
      exit;
   end;
   
   commandline:='/bin/cat -v ' + path + '|grep ''$OpenLDAP:'' >/tmp/ldap_ver';
   if D then ShowScreen('LDAP_VERSION:: ' + commandline);
   
   Shell(commandline);
   ver:=ReadFileIntoString('/tmp/ldap_ver');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='\$OpenLDAP:\s+slapd\s+([0-9\.]+)';
   if RegExpr.Exec(ver) then begin
      ver:=RegExpr.Match[1];
      RegExpr.Free;
      exit(ver);
   end;

end;
//#############################################################################
function Myconf.CYRUS_IMAPD_BIN_PATH():string;
begin
    if FileExists('/usr/lib/cyrus-imapd/imapd') then exit('/usr/lib/cyrus-imapd/imapd');
    if FileExists('/usr/lib/cyrus/bin/imapd') then exit('/usr/lib/cyrus/bin/imapd');
end;
//#############################################################################
function Myconf.CYRUS_DELIVER_BIN_PATH():string;
var path:string;
begin
    path:=LINUX_APPLICATION_INFOS('cyrus_deliver_bin');
    if length(path)>0 then exit(path);
    if FileExists('/usr/bin/cyrus/bin/deliver') then exit('/usr/bin/cyrus/bin/deliver');
    if FileExists('/usr/sbin/cyrdeliver') then exit('/usr/sbin/cyrdeliver');
    if FileExists('/usr/lib/cyrus/bin/deliver') then exit('/usr/lib/cyrus/bin/deliver');
end;
//#############################################################################
function MyConf.POSFTIX_DELETE_FILE_FROM_CACHE(MessageID:string):boolean;
var FileSource,FileDatas:TStringList;
    php_path,commandline:string;
    RegExpr:TRegExpr;
    D:boolean;
    i:integer;
begin
   D:=COMMANDLINE_PARAMETERS('debug');
  FileSource:=TStringList.Create;
  php_path:=get_ARTICA_PHP_PATH() +'/ressources/databases/*.cache';
  commandline:='/bin/grep -l ' + MessageID + ' ' + php_path;
  if D then ShowScreen('POSFTIX_DELETE_FILE_FROM_CACHE:: EXEC -> ' + commandLine);
  //grep -l 8680973402E /home/touzeau/developpement/artica-postfix/ressources/databases/*.cache
  Shell(commandline + ' >/tmp/artica_tmp');
  FileSource.LoadFromFile('/tmp/artica_tmp');
  
  if FileSource.Count>0 then begin
     if D then ShowScreen('POSFTIX_DELETE_FILE_FROM_CACHE:: Found file : ' +FileSource.Strings[0]);
  end else begin
           if D then ShowScreen('POSFTIX_DELETE_FILE_FROM_CACHE:: no Found file : ');
            FileSource.Free;
            exit(false);
  end;
  FileDatas:=TStringList.Create;
  FileDatas.LoadFromFile(trim(FileSource.Strings[0]));
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:=MessageID;
  for i:=0 to FileDatas.Count-1 do begin
       if RegExpr.Exec(FileDatas.Strings[i]) then begin
             if D then ShowScreen('POSFTIX_DELETE_FILE_FROM_CACHE:: Pattern found line : ' + IntToStr(i));
            FileDatas.Delete(i);
       
       end;
       if i>=FileDatas.Count-1 then break;
  end;
  FileDatas.SaveToFile(trim(FileSource.Strings[0]));
  RegExpr.free;
  FileDatas.free;
  FileSource.Free;
  exit(true);
end;




//#############################################################################
function MyConf.POSFTIX_CACHE_QUEUE():boolean;
var
   Conf:TiniFile;
   FileFiles:TStringList;
   D:boolean;
   QueuePath:string;
   fef:boolean;
   directory_name:string;
   logs:Tlogs;
   RegExpr:TRegExpr;
begin

    D:=COMMANDLINE_PARAMETERS('debug');
    if COMMANDLINE_PARAMETERS('queue=') then begin
           if D then ShowScreen('POSFTIX_CACHE_QUEUE:: Extract a single queue ->' + COMMANDLINE_EXTRACT_PARAMETERS('queue=([a-z]+)'));
           POSFTIX_CACHE_QUEUE_FILE_LIST(COMMANDLINE_EXTRACT_PARAMETERS('queue=([a-z]+)'));
           exit(true);
    
    end;

    logs:=Tlogs.Create;
    logs.logs('POSFTIX_CACHE_QUEUE:: Starting to cache queues directories');

    POSFTIX_CACHE_QUEUE_FILE_LIST('incoming');
    POSFTIX_CACHE_QUEUE_FILE_LIST('active');
    POSFTIX_CACHE_QUEUE_FILE_LIST('deferred');
    POSFTIX_CACHE_QUEUE_FILE_LIST('bounce');
    POSFTIX_CACHE_QUEUE_FILE_LIST('defer');
    POSFTIX_CACHE_QUEUE_FILE_LIST('trace');
    POSFTIX_CACHE_QUEUE_FILE_LIST('maildrop');
    logs.Free;

end;
//#############################################################################
function MyConf.POSFTIX_CACHE_QUEUE_FILE_LIST(QueueName:string):boolean;
var
   Conf:TiniFile;
   ConfPath,php_path:string;
   FileFiles:TStringList;
   D:boolean;
   QueuePath,WritePath:string;
   FilesNumber:integer;
   FilesNumberCache:integer;
   PagesNumber,start:integer;
   i:integer;

begin
    D:=COMMANDLINE_PARAMETERS('debug');
    if D then ShowScreen('POSFTIX_CACHE_QUEUE_FILE_LIST:: Starting to cache "' + QueueName + '" folder');
    logs.logs('POSFTIX_CACHE_QUEUE_FILE_LIST:: Starting to cache "' + QueueName + '" folder');
    php_path:=get_ARTICA_PHP_PATH();
    ConfPath:=php_path + '/ressources/databases/postfix-queue-cache.conf';
    Conf:=TiniFile.Create(ConfPath);

    if COMMANDLINE_PARAMETERS('flush') then begin
      if D then ShowScreen('POSFTIX_CACHE_QUEUE_FILE_LIST:: flush the cache');
      Conf.WriteInteger(QueueName,'FileNumber',0);
       
    end;

    FilesNumber:=StrToInt(POSTFIX_QUEUE_FILE_NUMBER(QueueName));
    if D then ShowScreen('POSFTIX_CACHE_QUEUE_FILE_LIST:: ' + QueueName + '="' + IntToStr(FilesNumber) +'"');
    
    
    

    
    if FilesNumber=0 then begin
       if D then ShowScreen('POSFTIX_CACHE_QUEUE_FILE_LIST:: no files for '+QueueName);
       exit(true);
    end;
    
    FilesNumberCache:=Conf.ReadInteger(QueueName,'FileNumber',0);
    if FilesNumber=FilesNumberCache then begin
       if D then ShowScreen('Number of files didn''t changed..');
       exit(true);
    end;


    PagesNumber:= FilesNumber div 250;
    if D then ShowScreen('POSFTIX_CACHE_QUEUE_FILE_LIST::Pages number: ' + IntToStr(PagesNumber));
    Conf.WriteInteger(QueueName,'FileNumber',FilesNumber);
    Conf.WriteInteger(QueueName,'PagesNumber',PagesNumber);
    
    
    start:=0;
    for i:=0 to  PagesNumber do begin
        FileFiles:=TStringList.Create;
        FileFiles.AddStrings(POSFTIX_READ_QUEUE_FILE_LIST(start,start+250,QueueName,true));
        WritePath:=php_path + '/ressources/databases/queue.list.'+ IntToStr(i) +'.'+ QueueName + '.cache';
        if D then ShowScreen('POSFTIX_CACHE_QUEUE_FILE_LIST::writing page cache in : ' + WritePath);
        FileFiles.SaveToFile(WritePath);
        FileFiles.Free;
        shell('/bin/chmod 755 ' + WritePath);
        start:=start+300;
    
    end;
    
   Conf.Free;



end;





//#############################################################################
function MyConf.POSFTIX_READ_QUEUE_FILE_LIST(fromFileNumber:integer;tofilenumber:integer;queuepath:string;include_source:boolean):TstringList;
const
READ_BYTES = 2048;

Var Info  : TSearchRec;
    Count : Longint;
    path  :string;
    Line:TstringList;
    return_line,queue_source_path:string;
    D:boolean;
    Logs:Tlogs;



Begin

  queue_source_path:=trim(ExecPipe('/usr/sbin/postconf -h queue_directory'));
  Count:=0;
  Line:=TstringList.Create;
  Logs:=TLogs.Create;
  D:=COMMANDLINE_PARAMETERS('debug');
  
  if tofilenumber-fromFileNumber>500 then begin
     if D then ShowScreen('POSFTIX_READ_QUEUE_FILE_LIST::eMail number is too large reduce it to 300');
      Logs.logs('POSFTIX_READ_QUEUE_FILE_LIST::eMail number is too large reduce it to 300');
      tofilenumber:=300;
  end;
  

  if tofilenumber=0 then tofilenumber:=100;
  if length(queuepath)=0 then  begin
     Logs.logs('POSFTIX_READ_QUEUE_FILE_LIST::Queue path is null');
     if D then ShowScreen('POSFTIX_READ_QUEUE_FILE_LIST::Queue path is null');
     Logs.free;
     exit(line);
  end;


  if include_source then begin
    if length(queuepath)>0  then path:=queue_source_path + '/' + queuepath;
  end else begin

         path:=queuepath;
  end;

  if D then ShowScreen('POSFTIX_READ_QUEUE_FILE_LIST:: ' + queuepath + '::-> ' +path + '/*' );
  Logs.logs('POSFTIX_READ_QUEUE_FILE_LIST::-> ' + queuepath + ':: '+path + ' Read from file number ' + IntTostr(fromFileNumber) + ' to file number ' + IntToStr(tofilenumber) );
  If FindFirst (path+'/*',faAnyFile and faDirectory,Info)=0 then
    begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin

              if Info.Attr=48 then begin
                 if D then ShowScreen(' -> ' +path + '/' +Info.Name );
                 Line.AddStrings(POSFTIX_READ_QUEUE_FILE_LIST(fromFileNumber,tofilenumber,path + '/' +Info.Name,false));
                 count:=count + Line.Count;
              end;
              
              if Info.Attr=16 then begin
                 if D then ShowScreen(' -> ' +path + '/' +Info.Name );
                 Line.AddStrings(POSFTIX_READ_QUEUE_FILE_LIST(fromFileNumber,tofilenumber,path + '/' +Info.Name,false));
                 count:=count + Line.Count;
              end;

              if Info.Attr=32 then begin
                 Inc(Count);
                 if Count>=fromFileNumber then begin
                    return_line:='<file>'+Info.name+'</file><path>' +path + '/' +Info.Name + '</path>' + POSTFIX_READ_QUEUE_MESSAGE(info.name);
                    if ParamStr(2)='queuelist' then begin
                       if length(ParamStr(6))=0 then ShowScreen(return_line);
                    end;
                    Line.Add(return_line);
                 end;
              end;
              if count>=tofilenumber then break;
              //Writeln (Info.Name:40,Info.Size:15);   postcat -q 3C7F17340B1
         end;
      end;

    Until FindNext(info)<>0;
    end;
    
  FindClose(Info);
  Logs.logs('POSFTIX_READ_QUEUE_FILE_LIST:: ' + queuepath + ':: ->'  +IntToStr(line.Count) + ' line(s)');
  Logs.free;
  exit(line);
end;
//#############################################################################


function myConf.POSTFIX_READ_QUEUE_MESSAGE(MessageID:string):string;
var
    RegExpr,RegExpr2,RegExpr3,RegExpr4,RegExpr5:TRegExpr;
    FileData:TStringList;
    i:integer;
    m_Time,named_attribute,sender,recipient,Subject:string;
     Logs:Tlogs;
begin
   Logs:=Tlogs.Create;
   if not fileExists('/usr/sbin/postcat') then begin
      logs.logs('POSTFIX_READ_QUEUE_MESSAGE:: unable to stat /usr/sbin/postcat');
      logs.Free;
      exit;
   end;
   

   Shell('/usr/sbin/postcat -q ' + MessageID + ' >/tmp/' + MessageID + '.tmp');

   if not fileExists('/tmp/' + MessageID + '.tmp') then begin
       logs.logs('unable to stat ' + '/tmp/' + MessageID + '.tmp');
       exit;
   end;
   FileData:=TStringList.Create;
   FileData.LoadFromFile('/tmp/' + MessageID + '.tmp');
   RegExpr:=TRegExpr.Create;
   RegExpr2:=TRegExpr.Create;
   RegExpr3:=TRegExpr.Create;
   RegExpr4:=TRegExpr.Create;
   RegExpr5:=TRegExpr.Create;
   RegExpr.Expression:='message_arrival_time: (.+)';
   RegExpr2.Expression:='named_attribute: (.+)';
   RegExpr3.Expression:='sender: ([a-zA-Z0-9\.@\-_]+)';
   RegExpr4.Expression:='recipient: ([a-zA-Z0-9\.@\-_]+)';
   RegExpr5.Expression:='Subject: (.+)';
   For i:=0 to FileData.Count-1 do begin
        if RegExpr.Exec(FileData.Strings[i]) then m_Time:=RegExpr.Match[1];
        if RegExpr2.Exec(FileData.Strings[i]) then named_attribute:=RegExpr2.Match[1];
        if RegExpr3.Exec(FileData.Strings[i]) then sender:=RegExpr3.Match[1];
        if RegExpr4.Exec(FileData.Strings[i]) then recipient:=RegExpr4.Match[1];
        if RegExpr5.Exec(FileData.Strings[i]) then Subject:=RegExpr5.Match[1];

        if length(m_Time)>0 then begin
           if  length(named_attribute)>0 then begin
               if length(sender)>0 then begin
                  if length(recipient)>0 then begin
                     if length(subject)>0 then begin
                        break
                     end;
                  end;
               end;
           end;
        end;
        
            
   
   end;
   shell('/bin/rm /tmp/' + MessageID + '.tmp');
   RegExpr.Free;
   RegExpr2.Free;
   RegExpr3.Free;
   RegExpr4.Free;
   RegExpr5.Free;
   FileData.Free;
   
  exit('<time>' + m_Time + '</time><named_attr>' + named_attribute + '</named_attr><sender>' + sender + '</sender><recipient>' + recipient + '</recipient><subject>' + subject + '</subject>');
   


end;
//#############################################################################
function myconf.POSTFIX_EXPORT_LOGS():boolean;
 var maillog,PHP_PATH:string;
 D:boolean;
 A:boolean;
 begin
   D:=COMMANDLINE_PARAMETERS('debug');
   A:=COMMANDLINE_PARAMETERS('alllogs');
   maillog:=get_LINUX_MAILLOG_PATH();
   PHP_PATH:=get_ARTICA_PHP_PATH();
  if  COMMANDLINE_PARAMETERS('silent') then begin
      A:=false;D:=false;
  end;
   
   if D then Showscreen('POSTFIX_EXPORT_LOGS:: -> receive command to parse logs :"' + maillog + '"');
   if length(maillog)=0 then begin
        Showscreen('POSTFIX_EXPORT_LOGS -> Error, unable to obtain maillog path :"' + maillog + '"');
        exit(true);
   end;

         if not FileExists(maillog) then exit(true);

   if D OR A then Showscreen('POSTFIX_EXPORT_LOGS:: -> get ' + '/usr/bin/tail '+ maillog + ' -n 100' +PHP_PATH + '/ressources/logs/postfix-all-events.log');
   Shell('/usr/bin/tail '+ maillog + ' -n 100 >' + PHP_PATH + '/ressources/logs/postfix-all-events.log');
   Shell('/usr/bin/tail '+ maillog + ' -n 100|grep postfix >' + PHP_PATH + '/ressources/logs/postfix-events.log');
   shell('/bin/chmod 0755 '+PHP_PATH + '/ressources/logs/postfix*');
   
end;


//#############################################################################
function Myconf.POSTFIX_QUEUE_FILE_NUMBER(directory_name:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;

var filepath:string;
system:Tsystem;
sCount:integer;
count_incoming,count_active,count_deferred,count_bounce,count_defer,count_trace,count_maildrop:integer;
fef:boolean;
PHP_PATH:string;
begin
fef:=false;
    directory_name:=trim(directory_name);
    PHP_PATH:=get_ARTICA_PHP_PATH();
    if length(directory_name)=0 then fef:=false;
    if directory_name='incoming' then fef:=true;
    if directory_name='active' then fef:=true;
    if directory_name='deferred' then fef:=true;
    if directory_name='bounce' then fef:=true;
    if directory_name='defer' then fef:=true;
    if directory_name='trace' then fef:=true;
    if directory_name='maildrop' then fef:=true;
    if directory_name='all' then fef:=true;


  if fef=false then begin
     writeln('must third parameters muste be: all or specific: incoming,active,deferred,bounce,trace,defer or maildrop');
     exit('0');
  end;




    system:=Tsystem.Create;
    filepath:=trim(ExecPipe('/usr/sbin/postconf -h queue_directory'));

    
    if directory_name='all' then begin
        count_incoming:=system.DirectoryCountFiles(filepath + '/incoming');
        count_active:=system.DirectoryCountFiles(filepath + '/active');
        count_deferred:=system.DirectoryCountFiles(filepath + '/deferred');
        count_bounce:=system.DirectoryCountFiles(filepath + '/bounce');
        count_defer:=system.DirectoryCountFiles(filepath + '/defer');
        count_trace:=system.DirectoryCountFiles(filepath + '/trace');
        count_maildrop:=system.DirectoryCountFiles(filepath + '/maildrop');


        result:='incoming:' + IntToStr(count_incoming) + CRLF;
        result:=result +  'active:' + IntToStr(count_active) + CRLF;
        result:=result +  'deferred:' + IntToStr(count_deferred) + CRLF;
        result:=result +  'bounce:' + IntToStr(count_bounce) + CRLF;
        result:=result +  'defer:' + IntToStr(count_defer) + CRLF;
        result:=result +  'trace:' + IntToStr(count_trace) + CRLF;
        result:=result +  'maildrop:' + IntToStr(count_maildrop) + CRLF;
        system.free;
        exit();
    
    end;
    
    
    sCount:=system.DirectoryCountFiles(filepath + '/'+directory_name);
    system.Free;
    exit(IntTostr(sCount));
end;

//#############################################################################
function Myconf.CYRUS_VERSION():string;
var
    path,ver,netcat:string;
    RegExpr:TRegExpr;
    D:boolean;
    zini:TStringList;
    cyrver:string;
    cyrcount:integer;
begin
   path:=CYRUS_IMAPD_BIN_PATH();
   D:=COMMANDLINE_PARAMETERS('debug');
   if D then ShowScreen('CYRUS_VERSION:: Imapd bin path is ' + path);
   cyrcount:=0;

   
   if not FileExists(path) then begin
      if D then ShowScreen('CYRUS_VERSION::Unable to stat path');
      exit;
   end;
   
   zini:=TStringList.Create;
   if FileExists('/etc/artica-postfix/cyrusversion.conf') then begin
   zini.LoadFromFile('/etc/artica-postfix/cyrusversion.conf');
   cyrver:=zini.Strings[0];
   if length(zini.Strings[1])>0 then cyrcount:=StrToInt(zini.Strings[1]);
   
   
   if D then ShowScreen('CYRUS_VERSION:: CONF= ' + cyrver);
   
   if length(cyrver)>0 then begin
      cyrcount:=cyrcount+1;
      if cyrcount>20 then begin
         zini.Strings[1]:='0';
         zini.SaveToFile('/etc/artica-postfix/cyrusversion.conf');
         result:=cyrver;
         zini.Free;
         exit;
      end;
      result:=cyrver;
      zini.Strings[1]:=IntTostr(cyrcount);
      zini.SaveToFile('/etc/artica-postfix/cyrusversion.conf');
      zini.Free;
      exit;
   
   end;
   end;
   
   netcat:='/bin/nc';
   if FileExists('/usr/bin/netcat') then netcat:='/usr/bin/netcat';
   if FileExists('/usr/bin/nc') then  netcat:='/usr/bin/nc';
   
   if zini.Count=0 then begin
      zini.Add(netcat);
      zini.Add('0');
   end;
   

   if D then ShowScreen('CYRUS_VERSION:: netcat is "' + netcat + '"');

   if D then ShowScreen('CYRUS_VERSION::/bin/echo . logout|'+netcat+' localhost 143|grep server >/tmp/cyrus_ver');
   Shell('/bin/echo . logout|'+netcat+' localhost 143|grep server >/tmp/cyrus_ver');
   ver:=ReadFileIntoString('/tmp/cyrus_ver');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='v([0-9A-Za-z\.\-]+)';
   if RegExpr.Exec(ver) then begin
      if D then ShowScreen('CYRUS_VERSION:: -> ' + RegExpr.Match[1]);
      ver:=RegExpr.Match[1];
      RegExpr.Free;
      cyrcount:=cyrcount+1;
       zini.Strings[1]:=IntTostr(cyrcount);
       zini.Strings[0]:=ver;
       zini.SaveToFile('/etc/artica-postfix/cyrusversion.conf');
       zini.Free;
      exit(ver);
   end;
   RegExpr.Free;
   
   zini.Strings[1]:=IntTostr(cyrcount);
   zini.Strings[0]:=result;
   zini.SaveToFile('/etc/artica-postfix/cyrusversion.conf');
   zini.Free;
   result:='0.0.0';
   if D then ShowScreen('CYRUS_VERSION:: -> ' + result);

   
end;

//#############################################################################
function MyConf.get_LINUX_DISTRI():string;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('LINUX','distribution-name','');
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.get_MANAGE_MAILBOX_SERVER():string;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('COURIER','server-type','cyrus');
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
procedure MyConf.set_MANAGE_MAILBOX_SERVER(val:string);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
ini.WriteString('COURIER','server-type',val);
ini.Free;
end;
//#############################################################################
function MyConf.get_DEBUG_DAEMON():boolean;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('LOGS','Debug','0');
if value='0' then result:=False;
if value='1' then result:=True;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.CYRUS_REPLICATION_MINUTES():integer;
var value:integer;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadInteger('CYRUS','REPLICATE_MIN',0);
if value=0 then begin
   result:=5;
   GLOBAL_INI.WriteInteger('CYRUS','REPLICATE_MIN',5);
end;
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.CYRUS_LAST_REPLIC_TIME():integer;
var tDate,tdate2:TDateTime;
value:string;
begin
tdate2:=Now;
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('CYRUS','CYRUS_LAST_REPLIC_TIME','');
if length(value)=0 then begin
        tDate:=Now;
        value:=DateTimeToStr(tdate);
        GLOBAL_INI.WriteDateTime('CYRUS','CYRUS_LAST_REPLIC_TIME',tDate);
end;
if length(value)>0 then begin
   tDate:=StrToDateTime(value);
   result:=Round(MinuteSpan(tDate,tdate2));
end;
   GLOBAL_INI.Free;
end;
//#############################################################################
procedure myconf.CYRUS_RESET_REPLIC_TIME();
var tDate:TDateTime;
begin
   tDate:=now;
   GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
   GLOBAL_INI.WriteDateTime('CYRUS','CYRUS_LAST_REPLIC_TIME',tDate);
   GLOBAL_INI.Free;
end;
//#############################################################################
procedure myconf.KEEPUP2DATE_RESET_REPLIC_TIME();
var tDate:TDateTime;
begin
   tDate:=now;
   GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
   GLOBAL_INI.WriteDateTime('KAV','KEEPUP2DATE_LAST_REPLIC_TIME',tDate);
   GLOBAL_INI.Free;
end;
//#############################################################################
procedure myconf.KAV_RESET_REPLIC_TIME();
var tDate:TDateTime;
begin
   tDate:=now;
   GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
   GLOBAL_INI.WriteDateTime('KAV','LAST_REPLIC_TIME',tDate);
   GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.KEEPUP2DATE_LAST_REPLIC_TIME():integer;
var tDate,tdate2:TDateTime;
value:string;
begin
tdate2:=Now;
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('KAV','KEEPUP2DATE_LAST_REPLIC_TIME','');
if length(value)=0 then begin
        tDate:=Now;
        value:=DateTimeToStr(tdate);
        GLOBAL_INI.WriteDateTime('KAV','KEEPUP2DATE_LAST_REPLIC_TIME',tDate);
end;
if length(value)>0 then begin
   tDate:=StrToDateTime(value);
   result:=Round(MinuteSpan(tDate,tdate2));
end;
   GLOBAL_INI.Free;
end;
//#############################################################################

function MyConf.KAV_LAST_REPLIC_TIME():integer;
var tDate,tdate2:TDateTime;
value:string;
begin
tdate2:=Now;
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('KAV','LAST_REPLIC_TIME','');
if length(value)=0 then begin
        tDate:=Now;
        value:=DateTimeToStr(tdate);
        GLOBAL_INI.WriteDateTime('KAV','LAST_REPLIC_TIME',tDate);
end;
if length(value)>0 then begin
   tDate:=StrToDateTime(value);
   result:=Round(MinuteSpan(tDate,tdate2));
end;
   GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.KEEPUP2DATE_REPLICATION_MINUTES():integer;
var value:integer;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadInteger('KAV','KEEPUP2DATE_REPLICATE_MIN',0);
if value=0 then begin
   result:=60;
   GLOBAL_INI.WriteInteger('KAV','KEEPUP2DATE_REPLICATE_MIN',60);
end;
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.KAV_REPLICATION_MINUTES():integer;
var value:integer;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadInteger('KAV','REPLICATE_MIN',0);
if value=0 then begin
   result:=5;
   GLOBAL_INI.WriteInteger('KAV','REPLICATE_MIN',5);
end;
result:=value;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.get_MANAGE_SASL_TLS():boolean;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('POSTFIX','sasl-tls','0');
if value='0' then result:=False;
if value='1' then result:=True;
GLOBAL_INI.Free;
end;
//#############################################################################
procedure MyConf.set_MANAGE_SASL_TLS(val:boolean);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
if val=True then ini.WriteString('POSTFIX','sasl-tls','1');
if val=False then ini.WriteString('POSTFIX','sasl-tls','0');
ini.Free;
end;
//#############################################################################
function MyConf.get_repositories_librrds_perl():boolean;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('REPOSITORIES','librrds-perl','0');
if value='0' then result:=False;
if value='1' then result:=True;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.ARTICA_AutomaticConfig():boolean;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('ARTICA','AutomaticConfig','no');
if value='no' then result:=False;
if value='yes' then result:=True;
GLOBAL_INI.Free;
end;
//#############################################################################


function MyConf.get_repositories_openssl():boolean;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('REPOSITORIES','openssl','0');
if value='0' then result:=False;
if value='1' then result:=True;
GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.AVESERVER_GET_VALUE(KEY:string;VALUE:string):string;
begin
  if not FileExists('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf') then exit;
  GLOBAL_INI:=TIniFile.Create('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf');
  result:=GLOBAL_INI.ReadString(KEY,VALUE,'');
  GLOBAL_INI.Free;
end;

//#############################################################################
function MyConf.AVESERVER_SET_VALUE(KEY:string;VALUE:string;DATA:string):string;
begin
  if not FileExists('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf') then exit;
  GLOBAL_INI:=TIniFile.Create('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf');
  GLOBAL_INI.WriteString(KEY,VALUE,DATA);
  GLOBAL_INI.Free;
end;
//#############################################################################
function MyConf.CROND_INIT_PATH():string;
begin
   if FileExists('/etc/init.d/crond') then exit('/etc/init.d/crond');
   if FileExists('/etc/init.d/cron') then exit('/etc/init.d/cron');
end;

function MyConf.AVESERVER_GET_TEMPLATE_DATAS(family:string;ztype:string):string;
var
   key_name:string;
   file_name:string;
   template:string;
   subject:string;
   xf:TstringList;
begin
  if not FileExists('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf') then exit;
  
  key_name:='smtpscan.notify.' + ztype + '.' + family;
  GLOBAL_INI:=TIniFile.Create('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf');
  file_name:=GLOBAL_INI.ReadString(key_name,'Template','');
  subject:=GLOBAL_INI.ReadString(key_name,'Subject','');
  
  if not FileExists(file_name) then exit;


  template:=ReadFileIntoString(file_name);
  
  
  
  result:='<subject>' + subject + '</subject><template>' + template + '</template>';
  
  
end;
 //#############################################################################

procedure MyConf.AVESERVER_REPLICATE_TEMPLATES();
var phpath,ressources_path:string;
Files:string;
SYS:TSystem;
i:integer;
D:boolean;
RegExpr:TRegExpr;
DirFile:string;
key:string;
begin
  D:=COMMANDLINE_PARAMETERS('debug');
  phpath:=get_ARTICA_PHP_PATH();
  SYS:=TSystem.Create;
  ressources_path:=phpath + '/ressources/conf';
  SYS.DirFiles(ressources_path,'notify_*');
  if SYS.DirListFiles.Count=0 then begin
     SYS.Free;
     exit;
  end;
  
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='notify_([a-z]+)_([a-z]+)';
  For i:=0 to SYS.DirListFiles.Count -1 do begin
     if RegExpr.Exec(SYS.DirListFiles.Strings[i]) then begin;
        key:='smtpscan.notify.' + RegExpr.Match[2] + '.' +  RegExpr.Match[1];
        DirFile:=AVESERVER_GET_VALUE(key,'Template');
        Files:=ressources_path + '/' + SYS.DirListFiles.Strings[i];
        if length(DirFile)>0 then begin
           if D then ShowScreen('AVESERVER_REPLICATE_TEMPLATES:: replicate ' + Files + ' to "'+ DirFile + '"');
           shell('/bin/mv ' + Files + ' ' + DirFile);
        end;
     end;
  
  end;
 RegExpr.Free;
 SYS.Free;

 
end;
 //#############################################################################
 

function MyConf.AVESERVER_GET_KEEPUP2DATE_LOGS_PATH():string;
begin
  result:=AVESERVER_GET_VALUE('updater.report','ReportFileName');
end;
function MyConf.AVESERVER_GET_LOGS_PATH():string;
begin
  result:=AVESERVER_GET_VALUE('aveserver.report','ReportFileName');
end;


 //#############################################################################
function MyConf.AVESERVER_GET_DAEMON_PORT():string;
var
   master_cf:Tstringlist;
   RegExpr:TRegExpr;
   i:integer;
   master_line:string;
begin
    master_cf:=TStringList.create;
    master_cf.LoadFromFile('/etc/postfix/master.cf');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='user=kluser\s+argv=\/opt\/kav\/.+';
    for i:=0 to master_cf.Count-1 do begin
        if RegExpr.Exec(master_cf.Strings[i]) then begin
                   master_line:=master_cf.Strings[i-1];
        end;
    end;
    
    RegExpr.Expression:='^.+:([0-9]+)\s+inet';
    if RegExpr.Exec(master_line) then result:=RegExpr.Match[1];
    RegExpr.Free;
    master_cf.free;
    

end;
 //#############################################################################
 
function MyConf.AVESERVER_GET_PID():string;
var pidpath:string;
begin
  pidpath:=AVESERVER_GET_VALUE('path','AVSpidPATH');
  if length(pidpath)=0 then exit;
  result:=trim(ReadFileIntoString(pidpath));
end;
//#############################################################################
function MyConf.AVESERVER_GET_VERSION():string;
var licensemanager,artica_path,datas:string;
   RegExpr:TRegExpr;
begin
    if not FileExists('/etc/init.d/aveserver') then exit;
    licensemanager:='/opt/kav/5.5/kav4mailservers/bin/licensemanager';
    if not FileExists(licensemanager) then exit;
    datas:=ExecPipe('/opt/kav/5.5/kav4mailservers/bin/aveserver -v');
    RegExpr:=TRegExpr.Create();
    RegExpr.expression:='([0-9\.]+).+RELEASE.+build.+#([0-9]+)';

    if RegExpr.Exec(datas) then begin
       if Debug=true then LOGS.logs('MyConf.ExportLicenceInfos -> ' + RegExpr.Match[1] + ' build ' + RegExpr.Match[2]);
        result:=RegExpr.Match[1] + ' build ' + RegExpr.Match[2];
     end;

     if not RegExpr.Exec(datas) then begin
         if Debug=true then LOGS.logs('MyConf.ExportLicenceInfos -> unable to catch version');
    end;
     RegExpr.Free;

end;
//##############################################################################
function MyConf.AVESERVER_GET_LICENCE():string;
var licensemanager,artica_path,datas:string;
begin
    if not FileExists('/etc/init.d/aveserver') then exit;
    licensemanager:='/opt/kav/5.5/kav4mailservers/bin/licensemanager';
    if not FileExists(licensemanager) then exit;
    result:=ExecPipe(licensemanager + ' -s');
end;
//##############################################################################


function MyConf.get_repositories_Checked():boolean;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('REPOSITORIES','Checked','0');
if value='0' then result:=False;
if value='1' then result:=True;
GLOBAL_INI.Free;
end;

//#############################################################################
function MyConf.set_repositories_checked(val:boolean):string;
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
if val=True then ini.WriteString('REPOSITORIES','Checked','1');
if val=False then ini.WriteString('REPOSITORIES','Checked','0');
ini.Free;
end;
//#############################################################################
function MyConf.get_kaspersky_mailserver_smtpscanner_logs_path():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf');
result:=GLOBAL_INI.ReadString('smtpscan.report','ReportFileName','/var/log/kav/5.5/kav4mailservers/smtpscanner.log');
GLOBAL_INI.Free;
end;
//#############################################################################

procedure MyConf.set_MYSQL_INSTALLED(val:boolean);
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
if val=True then GLOBAL_INI.WriteString('LINUX','MYSQL_INSTALLED','1');
if val=False then GLOBAL_INI.WriteString('LINUX','MYSQL_INSTALLED','0');
GLOBAL_INI.Free;
end;
function MyConf.get_MYSQL_INSTALLED():boolean;
var value:string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('LINUX','MYSQL_INSTALLED','0');
if value='0' then result:=False;
if value='1' then result:=True;
GLOBAL_INI.Free;
end;
function MyConf.get_POSTFIX_DATABASE():string;
var xres:string;
begin
result:='ldap';
exit;
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
xres:=GLOBAL_INI.ReadString('INSTALL','POSTFIX_DATABASE','hash');
if length(xres)=0 then xres:='ldap';
result:='ldap';
GLOBAL_INI.Free;
end;
function MyConf.get_MANAGE_MAILBOXES():string;
begin

if not fileExists('/etc/artica-postfix/artica-postfix.conf') then begin
    if debug then writeln('unable to stat /etc/artica-postfix/artica-postfix.conf');
    exit;
end;
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('ARTICA','MANAGE_MAILBOXES','');
result:=trim(result);
if length(result)=0 then begin
    result:=GLOBAL_INI.ReadString('INSTALL','MANAGE_MAILBOXES','');
    if length(result)>0 then GLOBAL_INI.WriteString('ARTICA','MANAGE_MAILBOXES',result);
end;
result:=trim(result);
if length(result)=0 then result:='no';
if result='FALSE' then result:='no';
if result='TRUE' then result:='yes';
if debug then writeln('get_MANAGE_MAILBOXES=' + result);
GLOBAL_INI.Free;
end;
function MyConf.set_POSTFIX_DATABASE(val:string):string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
 GLOBAL_INI.WriteString('INSTALL','POSTFIX_DATABASE',val);
GLOBAL_INI.Free;
end;
function MyConf.set_MANAGE_MAILBOXES(val:string):string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
 GLOBAL_INI.WriteString('ARTICA','MANAGE_MAILBOXES',val);
GLOBAL_INI.Free;
end;

function MyConf.set_INSTALL_PATH(val:string):string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
 GLOBAL_INI.WriteString('INSTALL','INSTALL_PATH',val);
GLOBAL_INI.Free;
end;
function MyConf.get_INSTALL_PATH():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('INSTALL','INSTALL_PATH','');
GLOBAL_INI.Free;
end;


function MyConf.set_DISTRI(val:string):string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
 GLOBAL_INI.WriteString('LINUX','DISTRI',val);
GLOBAL_INI.Free;
end;
function MyConf.get_DISTRI():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('LINUX','DISTRI','');
GLOBAL_INI.Free;
end;
function MyConf.get_UPDATE_TOOLS():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('LINUX','UPDATE_TOOLS','');
GLOBAL_INI.Free;
end;
function MyConf.set_UPDATE_TOOLS(val:string):string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
 GLOBAL_INI.WriteString('LINUX','UPDATE_TOOLS',val);
GLOBAL_INI.Free;
end;

//##################################################################################
function MyConf.get_ARTICA_PHP_PATH():string;
var path:string;
begin
  if not DirectoryExists('/usr/share/artica-postfix') then begin
  path:=ParamStr(0);
  path:=ExtractFilePath(path);
  path:=AnsiReplaceText(path,'/bin/','');
  exit(path);
  end else begin
  exit('/usr/share/artica-postfix');
  end;
  
end;
//##################################################################################
function MyConf.set_ARTICA_PHP_PATH(val:string):string;
begin
if length(val)=0 then exit;
TRY
   GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
   GLOBAL_INI.WriteString('ARTICA','PHP_PATH',val);
   GLOBAL_INI.Free;
EXCEPT
  writeln('FATAL ERROR set_ARTICA_PHP_PATH function !!!');
end;
end;
//##################################################################################
function MyConf.get_ARTICA_LOCAL_PORT():integer;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadInteger('ARTICA','LOCALPORT',0);

if result=0 then begin
   result:=47979;
   GLOBAL_INI.WriteInteger('ARTICA','LOCALPORT',47979);
end;

    GLOBAL_INI.Free

end;
function MyConf.get_ARTICA_LOCAL_SECOND_PORT():integer;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadInteger('ARTICA','SECOND_LOCAL_PORT',0);
GLOBAL_INI.Free;
end;
procedure MyConf.SET_ARTICA_LOCAL_SECOND_PORT(val:integer);
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
GLOBAL_INI.WriteInteger('ARTICA','SECOND_LOCAL_PORT',val);
GLOBAL_INI.Free;
end;
function MyConf.get_ARTICA_LISTEN_IP():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('ARTICA','LISTEN_IP','0.0.0.0');
GLOBAL_INI.Free;
end;


function MyConf.get_ARTICA_DAEMON_LOG_MaxSizeLimit():integer;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadInteger('ARTICA','DAEMON_LOG_MAX_SIZE',1000);
GLOBAL_INI.Free;
end;
function MyConf.set_ARTICA_DAEMON_LOG_MaxSizeLimit(val:integer):integer;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
 GLOBAL_INI.WriteInteger('ARTICA','DAEMON_LOG_MAX_SIZE',val);
GLOBAL_INI.Free;
end;


function MyConf.get_POSTFIX_HASH_FOLDER():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('POSTFIX','HASH_FOLDER','/etc/postfix/hash_files');
GLOBAL_INI.Free;
end;
function MyConf.set_POSTFIX_HASH_FOLDER(val:string):string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
 GLOBAL_INI.WriteString('POSTFIX','HASH_FOLDER',val);
GLOBAL_INI.Free;
end;




//##############################################################################
procedure MyConf.CYRUS_SET_V2(val:string);
begin
     GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
     GLOBAL_INI.WriteString('CYRUS','CYRUS_SET_V2',val);
     GLOBAL_INI.Free;
end;
//##############################################################################
function MyConf.CYRUS_GET_V2():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('CYRUS','CYRUS_SET_V2','no');
GLOBAL_INI.Free;
end;
//##############################################################################

function Myconf.KAS_GET_VALUE(key:string):string;
var
   RegExpr,RegExpr2:TRegExpr;
   filter_conf:TstringList;
   i:integer;
begin
  if not fileexists('/usr/local/ap-mailfilter3/etc/filter.conf') then exit;
  filter_conf:=TstringList.Create;
  filter_conf.LoadFromFile('/usr/local/ap-mailfilter3/etc/filter.conf');
  RegExpr:=TRegExpr.Create;
  RegExpr2:=TRegExpr.Create;
  RegExpr2.Expression:='#';
  RegExpr.Expression:=key + '(.+)';
  for i:=0 to filter_conf.Count -1 do begin
        if not RegExpr2.Exec(filter_conf.Strings[i]) then begin
            if  RegExpr.Exec(filter_conf.Strings[i]) then begin
                result:=trim(RegExpr.Match[1]);
                break;
            end;
        end;
  end;
  
  RegExpr.Free;
  RegExpr2.Free;
  filter_conf.Free;

end;
//##############################################################################
function CYRUS_CYRDELIVER_PATH():string;
begin


end;
//##############################################################################
procedure Myconf.KAS_DELETE_VALUE(key:string);
var
   RegExpr,RegExpr2:TRegExpr;
   filter_conf:TstringList;
   i:integer;
   found:boolean;
begin
    if not fileexists('/usr/local/ap-mailfilter3/etc/filter.conf') then exit;
  filter_conf:=TstringList.Create;
  filter_conf.LoadFromFile('/usr/local/ap-mailfilter3/etc/filter.conf');
  RegExpr:=TRegExpr.Create;
  RegExpr2:=TRegExpr.Create;
  RegExpr2.Expression:='#';
  RegExpr.Expression:=key + '(.+)';
 for i:=0 to filter_conf.Count -1 do begin
        if not RegExpr2.Exec(filter_conf.Strings[i]) then begin
            if  RegExpr.Exec(filter_conf.Strings[i]) then begin
                filter_conf.Delete(i);
                filter_conf.SaveToFile('/usr/local/ap-mailfilter3/etc/filter.conf');
                found:=True;
                break;
            end;
        end;
  end;
  filter_conf.Free;
  RegExpr2.Free;
  RegExpr.free;

end;


//##############################################################################
procedure Myconf.KAS_WRITE_VALUE(key:string;datas:string);
var
   RegExpr,RegExpr2:TRegExpr;
   filter_conf:TstringList;
   i:integer;
   found:boolean;
begin
  found:=false;
  if not fileexists('/usr/local/ap-mailfilter3/etc/filter.conf') then exit;
  filter_conf:=TstringList.Create;
  filter_conf.LoadFromFile('/usr/local/ap-mailfilter3/etc/filter.conf');
  RegExpr:=TRegExpr.Create;
  RegExpr2:=TRegExpr.Create;
  RegExpr2.Expression:='#';
  RegExpr.Expression:=key + '(.+)';
  for i:=0 to filter_conf.Count -1 do begin
        if not RegExpr2.Exec(filter_conf.Strings[i]) then begin
            if  RegExpr.Exec(filter_conf.Strings[i]) then begin
                filter_conf.Strings[i]:=key + ' ' + datas;
                filter_conf.SaveToFile('/usr/local/ap-mailfilter3/etc/filter.conf');
                found:=True;
                break;
            end;
        end;
  end;
  
  if found=false then begin
          filter_conf.Add(key + ' ' + datas);
          filter_conf.SaveToFile('/usr/local/ap-mailfilter3/etc/filter.conf');
  end;
  

  RegExpr.Free;
  RegExpr2.Free;
  filter_conf.Free;

end;

//##############################################################################
function MyConf.MAILGRAPH_VERSION():string;
var
   RegExpr:TRegExpr;
   php_path,cgi_path:string;
   FileDatas:TStringList;
   i:integer;
begin
  php_path:=get_ARTICA_PHP_PATH();
  cgi_path:=php_path + '/bin/mailgraph/mailgraph1.cgi';
  if not FileExists(cgi_path) then exit;
  if not FileExists('/etc/init.d/mailgraph-init') then exit;

  RegExpr:=TRegExpr.create;
  RegExpr.expression:='my\s+\$VERSION[\s=''"]+([0-9\.]+).+;';
  FileDatas:=TStringList.Create;
  
  FileDatas.LoadFromFile(cgi_path);
  for i:=0 to FileDatas.Count-1 do begin
      if  RegExpr.Exec(filedatas.Strings[i]) then begin
          result:=RegExpr.Match[1];
          RegExpr.Free;
          exit;
      end;
  end;

  RegExpr.Free;
  
end;


//##############################################################################
function MyConf.get_MAILGRAPH_TMP_PATH():string;
var
   RegExpr:TRegExpr;
   php_path,cgi_path,filedatas:string;
begin

 php_path:=get_ARTICA_PHP_PATH();
 cgi_path:=php_path + '/bin/mailgraph/mailgraph1.cgi';
 if not FileExists(cgi_path) then exit;
 RegExpr:=TRegExpr.create;
  RegExpr.expression:='my \$tmp_dir[|=| ]+[''|"]([a-zA-Z0-9\/\.]+)[''|"];';
 filedatas:=ReadFileIntoString(cgi_path);
  if  RegExpr.Exec(filedatas) then begin
  result:=RegExpr.Match[1];
  end;
  RegExpr.Free;
end;


//##############################################################################
function MyConf.get_MAILGRAPH_BIN():string;
var
   php_path:string;
begin

 php_path:=get_ARTICA_PHP_PATH();
 result:=php_path + '/bin/mailgraph/mailgraph1.cgi';

end;
//##############################################################################
function myconf.LDAP_GET_DAEMON_USERNAME():string;
   var get_ldap_user,get_ldap_user_regex:string;
   RegExpr:TRegExpr;
   FileDatas:TStringList;
   i:integer;
begin
       get_ldap_user_regex:=LINUX_LDAP_INFOS('get_ldap_user_regex');
       get_ldap_user:=LINUX_LDAP_INFOS('get_ldap_user');
       
       if length(get_ldap_user)=0 then begin
           writeln('LDAP_GET_USERNAME::unable to give infos from get_ldap_user key in infos.conf');
           exit;
       end;
       
       if length(get_ldap_user_regex)=0 then begin
           writeln('LDAP_GET_USERNAME::unable to give infos from get_ldap_user_regex key in infos.conf');
           exit;
       end;
       
       if not FileExists(get_ldap_user) then begin
          writeln('LDAP_GET_USERNAME::There is a problem to stat ',get_ldap_user);
          exit;
       end;
      FileDatas:=TStringList.Create;
      RegExpr:=TRegExpr.Create;
      RegExpr.Expression:=get_ldap_user_regex;
      FileDatas.LoadFromFile(get_ldap_user);
      for i:=0 to FileDatas.Count-1 do begin
          if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             RegExpr.Free;
             FileDatas.free;
             exit;
          end;
      
      end;
end;
//##############################################################################


//##############################################################################
function myconf.LDAP_GET_CONF_PATH():string;
begin

   if FileExists('/etc/ldap/slapd.conf') then result:='/etc/ldap/slapd.conf';
   if FileExists('/etc/openldap/slapd.conf') then result:='/etc/openldap/slapd.conf';
end;
//##############################################################################
function myconf.LDAP_GET_SCHEMA_PATH():string;
begin

   if FileExists('/etc/ldap/schema') then result:='/etc/ldap/schema';
   if FileExists('/etc/openldap/schema') then result:='/etc/openldap/schema';
end;
//##############################################################################
function myconf.LDAP_USE_SUSE_SCHEMA():boolean;
var schema_path:string;
begin
  schema_path:=LDAP_GET_SCHEMA_PATH();
  if not DirectoryExists(schema_path) then exit(false);
  schema_path:=schema_path + '/rfc2307bis.schema';
  if not fileExists(schema_path) then exit(false);
  exit(true);
end;
//##############################################################################



function myconf.LDAP_GET_INITD():string;
begin

   if FileExists('/etc/init.d/ldap') then result:='/etc/init.d/ldap';
   if FileExists('/etc/init.d/slapd') then result:='/etc/init.d/slapd';
end;
//##############################################################################
function MyConf.SASLAUTHD_PATH_GET():string;
begin

    if FileExists('/etc/default/saslauthd') then result:='/etc/default/saslauthd';
    if FileExists('/etc/sysconfig/saslauthd') then  result:='/etc/sysconfig/saslauthd';
    if Debug then ShowScreen('SASLAUTHD_PATH_GET -> "' + result + '"');
end;
//##############################################################################
function MyConf.SASLAUTHD_VALUE_GET(key:string):string;
var Msaslauthd_path,mdatas:string;
   RegExpr:TRegExpr;
begin
Msaslauthd_path:=SASLAUTHD_PATH_GET();
    if length(Msaslauthd_path)=0 then begin
        if Debug then writeln('SASLAUTHD_VALUE_GET -> NULL!!!');
        exit;
    end;
    
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:=key + '=[\s"]+([a-z\/]+)(?)';
     if Debug then writeln('SASLAUTHD_VALUE_GET -> Read ' + Msaslauthd_path);
     mdatas:=ReadFileIntoString(Msaslauthd_path);

     if RegExpr.Exec(mdatas) then begin
        result:=RegExpr.Match[1];
        if Debug then writeln('SASLAUTHD_VALUE_GET -> regex ' + result);
     end;
     RegExpr.Free;
end;
//##############################################################################
function myconf.SASLAUTHD_TEST_INITD():boolean;
var List:TStringList;
   RegExpr:TRegExpr;
   i:integer;
begin
   ShowScreen('SASLAUTHD_TEST_INITD:: Prevent false mechanism in init.d for saslauthd');
   if not fileExists('/etc/init.d/saslauthd') then begin
      showScreen('SASLAUTHD_TEST_INITD:: Error stat etc/init.d/saslauthd');
   end;
     List:=TStringList.Create;
     List.LoadFromFile('/etc/init.d/saslauthd');
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='SASLAUTHD_AUTHMECH=([a-z]+)';
     for i:=0 to List.Count-1 do begin
          if RegExpr.Exec(list.Strings[i]) then begin
             showScreen('SASLAUTHD_TEST_INITD:: Read: "' + RegExpr.Match[1]+'"');
             if  RegExpr.Match[1]<>'ldap' then begin
                  showScreen('SASLAUTHD_TEST_INITD:: change to "ldap" mode');
                  list.Strings[i]:='SASLAUTHD_AUTHMECH=ldap';
                  list.SaveToFile('/etc/init.d/saslauthd');
                  showScreen('SASLAUTHD_TEST_INITD:: done..');
                  Shell('/etc/init.d/saslauthd restart');
                  list.Free;
                  RegExpr.free;
                  exit(true);
             end;
          end;
     
     end;
 showScreen('SASLAUTHD_TEST_INITD:: nothing to change...');
 list.Free;
 RegExpr.free;
 exit(true);
end;
//##############################################################################
function MyConf.BOA_SET_CONFIG();
var
   List:TstringList;
   LocalPort:integer;
   LOGS:Tlogs;
begin
LocalPort:=get_ARTICA_LOCAL_PORT();
forcedirectories(get_ARTICA_PHP_PATH() + '/bin/null');
List:=TstringList.Create;
LOGS:=TLOGS.Create;
logs.logs('Writing httpd.conf for artica-postfix listener on ' + IntToStr(LocalPort) + ' port');
List.Add('Port ' + IntToStr(LocalPort));
List.Add('Listen 127.0.0.1');
List.Add('User root');
List.Add('Group root');
List.Add('PidFile /etc/artica-postfix/boa.pid');
List.Add('ErrorLog /var/log/artica-postfix/boa_error.log');
List.Add('AccessLog /var/log/artica-postfix/boa_access_log');
List.Add('CGILog /var/log/artica-postfix/boa_cgi_log');
List.Add('DocumentRoot '+ get_ARTICA_PHP_PATH() + '/bin/null');
List.Add('DirectoryIndex index.html');
List.Add('#DirectoryMaker /usr/lib/boa/boa_indexer');
List.Add('KeepAliveMax 1000');
List.Add('KeepAliveTimeout 5');
List.Add('#MimeTypes /etc/mime.types');
List.Add('DefaultType text/plain');
List.Add('CGIPath /bin:/usr/bin:/usr/local/bin:/usr/local/sbin:/usr/sbin:/sbin:/sbin:/bin:/usr/X11R6/bin');
List.Add('AddType application/x-executable cgi');
List.Add('ScriptAlias /cgi/ ' + get_ARTICA_PHP_PATH() + '/bin/');
list.SaveToFile('/etc/artica-postfix/httpd.conf');
list.Free;
logs.free;
end;
//##############################################################################
function MyConf.SYSTEM_START_ARTICA_DAEMON();
var
   Rootpath,ArticaPath,BoaPath, boasinglePath,ContentFilterPath,PolicyFilterPath:string;
   LOGS:Tlogs;
   D:boolean;
   boa_pid,emailrelaycmd,artica_filter_pid,artica_pid,apache_pidn,ldap_pidn,postfix_pidn,crond_pid,artica_policy_pid,email_relay_pid,queue_path,fetchmailpid,fetchmailpath:string;
   fetchmail_daemon_pool:string;
   fetchmail_count:integer;
   
begin

     LOGS:=Tlogs.Create;
     D:=COMMANDLINE_PARAMETERS('debug');
     Rootpath:=get_ARTICA_PHP_PATH();
     articaPath:=Rootpath + '/bin/artica-postfix';
     ContentFilterPath:=Rootpath + '/bin/artica-filter';
     PolicyFilterPath:=Rootpath+ '/bin/artica-policy';
     BoaPath:=Rootpath + '/bin/boa -c /etc/artica-postfix -f /etc/artica-postfix/httpd.conf -l 4';
     boasinglePath:=Rootpath + '/bin/boa';


     shell('/bin/chmod -R 777 /etc/cron.d/');

     artica_filter_pid:=ARTICA_FILTER_GET_PID();
     artica_policy_pid:=ARTICA_POLICY_GET_PID();
     boa_pid:=BOA_DAEMON_GET_PID();
     artica_pid:=ARTICA_DAEMON_GET_PID();
     apache_pidn:=APACHE_PID();
     ldap_pidn:=LDAP_PID();
     postfix_pidn:=POSTFIX_PID();
     crond_pid:=CRON_PID();
     email_relay_pid:=EMAILRELAY_PID();
     

     SYSTEM_VERIFY_CRON_TASKS();

     if FileExists('/etc/artica-postfix/shutdown') then begin
        if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: remove /etc/artica-postfix/shutdown');
        shell('/bin/rm /etc/artica-postfix/shutdown');
     end;
     
     if not SYSTEM_PROCESS_EXIST(crond_pid) then begin
        writeln('Starting......: Cron daemon...');
        if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: Start cron service server "' + CROND_INIT_PATH()+'"');
        MonShell(CROND_INIT_PATH() + ' start',true);
      end else begin
        writeln('Starting......: crond daemon is already running using PID ' + crond_pid + '...');
     end;

     if not SYSTEM_PROCESS_EXIST(apache_pidn) then begin
        writeln('Starting......: Apache daemon...');
        if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: Start apache service server "' + APACHE_GET_INITD_PATH() + '"');
        MonShell(APACHE_GET_INITD_PATH() + ' start',true);
      end else begin
        writeln('Starting......: Apache daemon is already running using PID ' + apache_pidn + '...');
     end;
     
     if not SYSTEM_PROCESS_EXIST(ldap_pidn) then begin
        writeln('Starting......: LDAP daemon...');
        if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: Start LDAP service server "' + LDAP_GET_INITD() + '"');
        MonShell(LDAP_GET_INITD() + ' start',true);
      end else begin
        writeln('Starting......: LDAP daemon is already running using PID ' + ldap_pidn + '...');
     end;
     
     if not SYSTEM_PROCESS_EXIST(postfix_pidn) then begin
        writeln('Starting......: LDAP daemon...');
        if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: Start POSTFIX service server ');
        MonShell('/etc/init.d/postfix start',true);
      end else begin
        writeln('Starting......: Postfix daemon is already running using PID ' + postfix_pidn + '...');
     end;
     
     ARTICA_FILTER_CHECK_PERMISSIONS();
     FETCHMAIL_START_DAEMON();
     DNSMASQ_START_DAEMON();
     shell('/usr/share/artica-postfix/bin/artica-dbf -install');

     
     if not SYSTEM_PROCESS_EXIST(artica_policy_pid) then begin
        writeln('Starting......: artica-policy daemon...');
        MonShell(PolicyFilterPath,false);
     end else begin
        writeln('Starting......: artica-policy daemon is already running using PID ' + artica_policy_pid + '...');

     end;
     
     if not SYSTEM_PROCESS_EXIST(artica_pid) then begin
        writeln('Starting......: artica-postfix daemon...');
        if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: Start artica service server "' + articaPath + '"');
        MonShell(articaPath,false);
      end else begin
        writeln('Starting......: artica-postfix daemon is already running using PID ' + artica_pid + '...');
     end;
     
     if not SYSTEM_PROCESS_EXIST(boa_pid) then begin
        BOA_SET_CONFIG();
        writeln('Starting......: BOA daemon...');
        if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: Start boa http server "' + BoaPath + '"');
        Shell(BoaPath +' >/dev/null 2>&1');
     end else begin
        writeln('Starting......: BOA daemon is already running using PID ' + boa_pid+ '...');
     end;

end;



//##############################################################################
function MyConf.ARTICA_FILTER_CHECK_PERMISSIONS():string;
var
   queuePath:string;
   ZSYS:TSystem;
begin
     ZSYS:=TSystem.Create();
     QueuePath:=ARTICA_FILTER_QUEUEPATH();
     if not ZSYS.IsUserExists('artica') then begin
        ZSYS.CreateGroup('artica');
        ZSYS.AddUserToGroup('artica','artica','','');
     end;
     forcedirectories('/var/log/artica-postfix');
     forcedirectories('/usr/share/artica-postfix/LocalDatabases');
     forcedirectories('/var/quarantines');
     forcedirectories(QueuePath);
     
    shell('/bin/chmod 666 /var/log/artica-postfix');
    shell('/bin/chown -R artica:root /var/log/artica-postfix');
    shell('/bin/chown -R artica:root /usr/share/artica-postfix/LocalDatabases');
    shell('/bin/chown -R artica:root /var/quarantines');
    shell('/bin/chown -R artica:root ' + QueuePath);
     

     



end;


function MyConf.FETCHMAIL_START_DAEMON():boolean;
var
 fetchmail_daemon_pool,fetchmailpid,fetchmailpath:string;
 fetchmail_count:integer;
 D:boolean;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
     fetchmailpid:=FETCHMAIL_PID();
     fetchmailpath:=FETCHMAIL_BIN_PATH();
     fetchmail_daemon_pool:=FETCHMAIL_SERVER_PARAMETERS('daemon');
     fetchmail_count:=FETCHMAIL_COUNT_SERVER();

    if fetchmail_count>0 then begin
     if length(fetchmailpath)>0 then begin
        if length(fetchmail_daemon_pool)>0 then begin
           if not SYSTEM_PROCESS_EXIST(fetchmailpid) then begin
              writeln('Starting......: fetchmail daemon...');
              if D then showscreen('SYSTEM_START_ARTICA_DAEMON:: Start FETCHMAIL service server ' + IntToStr(fetchmail_count) + ' server(s)');
              MonShell(fetchmailpath + ' --daemon ' + fetchmail_daemon_pool + ' --pidfile /var/run/fetchmail.pid --fetchmailrc /etc/fetchmailrc > /dev/null 2>&1' ,true);
           end else begin
               writeln('Starting......: fetchmail is already running using PID ' + fetchmailpid + '...');
           end;
        end;
     end;
    end;
end;
//##############################################################################

procedure myConf.DNSMASQ_START_DAEMON();
var bin_path,pid,cache,cachecmd:string;
begin
    cache:=DNSMASQ_GET_VALUE('cache-size');
    bin_path:=DNSMASQ_BIN_PATH();
    if not FileExists(bin_path) then begin
       writeln('Starting......: dnsmasq is not installed ('+bin_path+')...');
       exit;
    end;
    pid:=DNSMASQ_PID();
    if SYSTEM_PROCESS_EXIST(pid) then begin
       writeln('Starting......: dnsmasq already exists using pid ' + pid+ '...');
       exit;
    end;
    
    if FileExists('/etc/init.d/dnsmasq') then begin
       shell('/etc/init.d/dnsmasq start');
       exit;
    end;
    
    if length(cache)=0 then begin
       cachecmd:=' --cache-size=1000';
    end;
    forceDirectories('/var/log/dnsmasq');
    writeln('Starting......: dnsmasq daemon...');
    shell(bin_path + ' --pid-file=/var/run/dnsmasq.pid --conf-file=/etc/dnsmasq.conf --log-facility=/var/log/dnsmasq/dnsmasq.log' + cachecmd);
end;
//##############################################################################
function myConf.DNSMASQ_PID():string;
var
   RegExpr:TRegExpr;
   filedatas:TStringList;
   i:Integer;
begin
     if not FileExists('/var/run/dnsmasq.pid') then exit();
     filedatas:=TStringList.Create;
     filedatas.LoadFromFile('/var/run/dnsmasq.pid');
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='([0-9]+)';
     For i:=0 to filedatas.Count-1 do begin
         if RegExpr.Exec(filedatas.Strings[i]) then begin
               result:=RegExpr.Match[1];
               break;
         end;
     
     end;
     
    RegExpr.Free;
    filedatas.Free;

end;
//##############################################################################
procedure myConf.DNSMASQ_STOP_DAEMON();
var bin_path,pid:string;
begin

    bin_path:=DNSMASQ_BIN_PATH();
    if not FileExists(bin_path) then exit;
    pid:=DNSMASQ_PID();
    if not SYSTEM_PROCESS_EXIST(pid) then begin
       writeln('stopping......: dnsmasq already shutdown...');
       exit;
    end;

    if FileExists('/etc/init.d/dnsmasq') then begin
       shell('/etc/init.d/dnsmasq stop');
       exit;
    end;
    writeln('stopping......: dnsmasq daemon...');
    shell('kill ' + pid);
end;
//##############################################################################
function MyConf.POSTFIX_LDAP_COMPLIANCE():boolean;
var
   LIST:TstringList;
   i:integer;
begin
 result:=false;
 shell('postconf -m >/tmp/postconfm.txt');
 LIST:=TStringList.Create;
 LIST.LoadFromFile('/tmp/postconfm.txt');
 for i:=0 to LIST.Count -1 do begin
     if trim(list.Strings[i])='ldap' then begin
        result:=true;
        list.free;
        exit;
     end;
 
 end;
end;
//##############################################################################
function MyConf.FETCHMAIL_SERVER_PARAMETERS(param:string):string;
var
   RegExpr:TRegExpr;
   filedatas:TStringList;
   i:integer;
begin
  if not FileExists('/etc/fetchmailrc') then exit;
  filedatas:=TStringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^set\s+' + param + '\s+(.+)';
  filedatas.LoadFromFile('/etc/fetchmailrc');
   for i:=0 to filedatas.Count -1 do begin
      if RegExpr.Exec(filedatas.Strings[i]) then begin
         result:=trim(RegExpr.Match[1]);
         break;
      end;
   end;
   
   RegExpr.Free;
   filedatas.free;
end;
//##############################################################################
function MyConf.FETCHMAIL_COUNT_SERVER():integer;
var
   RegExpr:TRegExpr;
   filedatas:TStringList;
   i:integer;
begin
  result:=0;
  if not FileExists('/etc/fetchmailrc') then exit;
  filedatas:=TStringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^poll\s+(.+)';
  filedatas.LoadFromFile('/etc/fetchmailrc');
   for i:=0 to filedatas.Count -1 do begin
      if RegExpr.Exec(filedatas.Strings[i]) then begin
         inc(result);
         break;
      end;
   end;

   RegExpr.Free;
   filedatas.free;
end;
//##############################################################################


function MyConf.get_MAILGRAPH_RRD():string;
var
   RegExpr:TRegExpr;
   php_path,cgi_path,filedatas:string;
begin

 php_path:=get_ARTICA_PHP_PATH();
 cgi_path:=php_path + '/bin/mailgraph/mailgraph1.cgi';
 if not FileExists(cgi_path) then exit;
 RegExpr:=TRegExpr.create;
 RegExpr.expression:='my \$rrd[|=| ]+[''|"]([\/a-zA-Z0-9-\._]+)[''|"];';
 filedatas:=ReadFileIntoString(cgi_path);
  if  RegExpr.Exec(filedatas) then begin
  result:=RegExpr.Match[1];
  end;
  RegExpr.Free;
end;
//##############################################################################
function MyConf.get_LINUX_DOMAIN_NAME():string;
var data:string;
begin
if not FileExists('/bin/hostname') then exit;
Shell('/bin/hostname -d >/tmp/hostname.txt');
data:=ReadFileIntoString('tmp/hostname.txt');
result:=trim(data);
end;

//##############################################################################
function MyConf.get_MAILGRAPH_RRD_VIRUS():string;
var
   RegExpr:TRegExpr;
   php_path,cgi_path,filedatas:string;
begin

 php_path:=get_ARTICA_PHP_PATH();
 cgi_path:=php_path + '/bin/mailgraph/mailgraph1.cgi';
 if not FileExists(cgi_path) then exit;
 RegExpr:=TRegExpr.create;
 RegExpr.expression:='my \$rrd_virus[|=| ]+[''|"]([\/a-zA-Z0-9-\._]+)[''|"];';
 filedatas:=ReadFileIntoString(cgi_path);
  if  RegExpr.Exec(filedatas) then begin
  result:=RegExpr.Match[1];
  end;
  RegExpr.Free;
end;
//##############################################################################
procedure MyConf.set_MAILGRAPH_RRD(rrd_path:string);
var
list:TstringList;
i:integer;
RegExpr:TRegExpr;
mailgraph_pl,mailgraph_rrd,mailgraph_cgi,images_path:string;
begin
   list:=TstringList.Create;
   if length(rrd_path)=0 then rrd_path:=get_ARTICA_PHP_PATH() + '/bin/mailgraph/mailgraph.rrd';
   mailgraph_rrd:=rrd_path;
   mailgraph_pl:=get_ARTICA_PHP_PATH() + '/bin/mailgraph/mailgraph.pl';
   mailgraph_cgi:=get_MAILGRAPH_BIN();
   images_path:=get_ARTICA_PHP_PATH() + '/img';
   

    if not fileexists(mailgraph_cgi) then begin
       writeln('WARNING !!! unable to stat ' + mailgraph_cgi);
       exit;
    end;
   writeln('------------------------------------------------------------------');
   writeln('modify the content of ' +mailgraph_cgi);
   list.LoadFromFile(mailgraph_cgi);
   RegExpr:=TRegExpr.create;

   if list.Count<10 then exit;
   For i:=0 to  list.Count-1 do begin

       RegExpr.expression:='my \$rrd_virus';
       if  RegExpr.Exec(list.Strings[i]) then begin
           ShowScreen('Set mailgraph rrd database path path to ' +mailgraph_rrd);
           list[i]:='my $rrd_virus = ''' + mailgraph_rrd+''';';
           continue;
       end;

      RegExpr.expression:='my\s+\$tmp_dir';
      if  RegExpr.Exec(list.Strings[i]) then begin
          ShowScreen('Set mailgraph images path path to ' +images_path);
          list[i]:='my $tmp_dir = ''' + images_path+''';';
      end;

       
   end;

           list.SaveToFile(mailgraph_cgi);
           Shell('/bin/chmod 755 ' + mailgraph_cgi);

   writeln('------------------------------------------------------------------');


    if not fileexists(mailgraph_pl) then begin
       ShowScreen('WARNING !!! unable to stat ' + mailgraph_pl);
       exit;
    end;

    ShowScreen('------------------------------------------------------------------');
    ShowScreen('Open ' + mailgraph_pl + ' in order to change $rrd settings');
    ShowScreen('to ' + mailgraph_rrd);


    list.LoadFromFile(mailgraph_pl);
    RegExpr.Expression:='my\s+\$rrd\s+=["\s]+.+";';
    For i:=0 to  list.Count-1 do begin
        if  RegExpr.Exec(list.Strings[i]) then begin
          list[i]:='my $rrd ="' + mailgraph_rrd+'";';
          ShowScreen('CHANGE ! Save file  ' + mailgraph_pl);
          list.SaveToFile(mailgraph_pl);
          Shell('/bin/chmod 755 ' + mailgraph_pl);
        end
    end;

    ShowScreen('------------------------------------------------------------------');


    list.Free;
    RegExpr.Free;
    exit;
   

end;
//##############################################################################
procedure MyConf.set_MAILGRAPH_RRD_VIRUS(rrd_path:string);
var
list:TstringList;
i:integer;
RegExpr:TRegExpr;
mailgraph_pl,mailgraph_virus_rrd:string;
begin
   list:=TstringList.Create;
   RegExpr:=TRegExpr.create;
   
   RegExpr.expression:='my \$rrd_virus';
   if length(rrd_path)=0 then rrd_path:=get_ARTICA_PHP_PATH() + '/bin/mailgraph/mailgraph_virus.rrd';
   mailgraph_virus_rrd:=rrd_path;
   
   list.LoadFromFile(get_MAILGRAPH_BIN());
   if list.Count<10 then exit;
   ShowScreen('------------------------------------------------------------------');
   ShowScreen('Open ' + get_MAILGRAPH_BIN() + ' in order to change $rrd_virus settings ');
   ShowScreen('to ' + mailgraph_virus_rrd);

   For i:=0 to  list.Count-1 do begin
       if  RegExpr.Exec(list.Strings[i]) then begin
           list[i]:='my $rrd_virus = ''' + mailgraph_virus_rrd+''';';
           ShowScreen('CHANGE ! Save file  ' + get_MAILGRAPH_BIN());
           list.SaveToFile(get_MAILGRAPH_BIN());
           Shell('/bin/chmod 755 ' + get_MAILGRAPH_BIN());
       end;
   end;
   
    mailgraph_pl:=get_ARTICA_PHP_PATH() + '/bin/mailgraph/mailgraph.pl';
    if not fileexists(mailgraph_pl) then begin
       ShowScreen('WARNING !!! unable to stat ' + mailgraph_pl);
       exit;
    end;
    
    ShowScreen('------------------------------------------------------------------');
    ShowScreen('Open ' + mailgraph_pl + ' in order to change $rrd_virus settings');
    ShowScreen('to ' + mailgraph_virus_rrd);
    
    
    list.LoadFromFile(mailgraph_pl);
    RegExpr.Expression:='my\s+\$rrd_virus\s+=["\s]+.+";';
    For i:=0 to  list.Count-1 do begin
        if  RegExpr.Exec(list.Strings[i]) then begin
          list[i]:='my $rrd_virus ="' + mailgraph_virus_rrd+'";';
          writeln('CHANGE ! Save file  ' + mailgraph_pl);
          list.SaveToFile(mailgraph_pl);
          Shell('/bin/chmod 755 ' + mailgraph_pl);
        end
    end;
       
    ShowScreen('------------------------------------------------------------------');
    
    
    list.Free;
    RegExpr.Free;
    exit;
    


end;
//##############################################################################
procedure MyConf.StripDiezes(filepath:string);
begin
    set_FileStripDiezes(filepath);
end;
procedure MyConf.set_FileStripDiezes(filepath:string);
var
list,list2:TstringList;
i,n:integer;
line:string;
RegExpr:TRegExpr;
begin
 RegExpr:=TRegExpr.create;
 RegExpr.expression:='#';
    if not FileExists(filepath) then exit;
    list:=TstringList.Create();
    list2:=TstringList.Create();
    list.LoadFromFile(filepath);
    n:=-1;
    For i:=0 to  list.Count-1 do begin
        n:=n+1;
         line:=list.Strings[i];
         if length(line)>0 then begin

            if not RegExpr.Exec(list.Strings[i])  then begin
               list2.Add(list.Strings[i]);
            end;
         end;
    end;

     killfile(filepath);
     list2.SaveToFile(filepath);

    RegExpr.Free;
    list2.Free;
    list.Free;
end;
 //##############################################################################
function MyConf.get_httpd_conf():string;
var
mpath,user,group:string;
begin

    if FileExists('/etc/apache2/apache2.conf') then exit('/etc/apache2/apache2.conf');
    if FileExists('/etc/apache/httpd.conf') then exit('/etc/apache/httpd.conf');
    if FileExists('/etc/httpd/conf/httpd.conf') then exit('/etc/httpd/conf/httpd.conf');
    if FileExists('/etc/apache2/default-server.conf') then exit('/etc/apache2/default-server.conf');
    result:=mpath;

end;
 //##############################################################################
 
function MyConf.APACHE2_VERIFY_SETTINGS():boolean;
var fileTmp:TstringList;
initd:string;
begin
  if not fileexists('/etc/default/apache2') then exit;
  filetmp:=TstringList.Create;
  filetmp.Add('# 0 = start on boot; 1 = don''t start on boot');
  filetmp.Add('NO_START=0');
  filetmp.SaveToFile('/etc/default/apache2');
  initd:=APACHE_GET_INITD_PATH();
  
  
  if FileExists('/etc/init.d/apache') then begin
     writeln('Stop old apache...');
     shell('/etc/init.d/apache stop');
  end;
   writeln('restarting apache 2');
   shell(initd + ' restart');
  

end;
 //##############################################################################
function MyConf.APACHE_PID():string;
var
   httpdconf:string;
   RegExpr:TRegExpr;
   FileData:TStringList;
   PidFile:string;
   i:integer;
begin
  result:='0';
  httpdconf:=get_httpd_conf();
  if not FileExists(httpdconf) then exit('0');
  RegExpr:=TRegExpr.Create;
  FileData:=TStringList.Create;
  FileData.LoadFromFile(httpdconf);
  RegExpr.Expression:='PidFile\s+(.+)';
  For i:=0 TO FileData.Count -1 do begin
      if RegExpr.Exec(FileData.Strings[i]) then begin
           PidFile:=RegExpr.Match[1];
           break;
      end;
  end;
  if not FileExists(PidFile) then exit('0');
  FileData.LoadFromFile(PiDFile);
  RegExpr.Expression:='([0-9]+)';
  if RegExpr.exec(FileData.Text) then result:=RegExpr.Match[1];
  
  FileData.Free;
  RegExpr.Free;
end;
 //##############################################################################
 function MyConf.POSTFIX_PID():string;
var
   conffile:string;
   RegExpr:TRegExpr;
   FileData:TStringList;
   PidFile:string;
   i:integer;
begin
   result:='0';
  conffile:=POSTFIX_PID_PATH();
  if not FileExists(conffile) then exit('0');
  RegExpr:=TRegExpr.Create;
  FileData:=TStringList.Create;
  FileData.LoadFromFile(conffile);
  RegExpr.Expression:='([0-9]+)';
  For i:=0 TO FileData.Count -1 do begin
      if RegExpr.Exec(FileData.Strings[i]) then begin
           result:=RegExpr.Match[1];
           break;
      end;
  end;
  FileData.Free;
  RegExpr.Free;
end;
 //##############################################################################
 function MyConf.CRON_PID():string;
var
   conffile:string;
   RegExpr:TRegExpr;
   FileData:TStringList;
   PidFile:string;
   i:integer;
begin
   result:='0';
  conffile:='/var/run/crond.pid';
  if not FileExists(conffile) then exit('0');
  RegExpr:=TRegExpr.Create;
  FileData:=TStringList.Create;
  FileData.LoadFromFile(conffile);
  RegExpr.Expression:='([0-9]+)';
  For i:=0 TO FileData.Count -1 do begin
      if RegExpr.Exec(FileData.Strings[i]) then begin
           result:=RegExpr.Match[1];
           break;
      end;
  end;
  FileData.Free;
  RegExpr.Free;
end;
 //##############################################################################
function MyConf.LDAP_PID():string;
var
   conffile:string;
   RegExpr:TRegExpr;
   FileData:TStringList;
   PidFile:string;
   i:integer;
begin
  result:='0';
  conffile:=LDAP_GET_CONF_PATH();
  if not FileExists(conffile) then exit('0');
  RegExpr:=TRegExpr.Create;
  FileData:=TStringList.Create;
  FileData.LoadFromFile(conffile);
  RegExpr.Expression:='pidfile\s+(.+)';
  For i:=0 TO FileData.Count -1 do begin
      if RegExpr.Exec(FileData.Strings[i]) then begin
           PidFile:=RegExpr.Match[1];
           break;
      end;
  end;
  
  if not FileExists(PidFile) then exit('0');
  FileData.LoadFromFile(PiDFile);
  RegExpr.Expression:='([0-9]+)';
  if RegExpr.exec(FileData.Text) then result:=RegExpr.Match[1];
  
  
  FileData.Free;
  RegExpr.Free;
  // pidfile         /var/run/slapd/slapd.pid
end;
 //##############################################################################
 
 

 
procedure MyConf.Cyrus_set_sasl_pwcheck_method(val:string);
var RegExpr:TRegExpr;
list:TstringList;
i:integer;
begin
 RegExpr:=TRegExpr.create;
    list:=TstringList.Create();
    list.LoadFromFile('/etc/imapd.conf');
    for i:=0 to list.Count-1 do begin
          RegExpr.expression:='sasl_pwcheck_method';
          if RegExpr.Exec(list.Strings[i]) then begin
               list.Strings[i]:='sasl_pwcheck_method: ' + val;
          end;
          
    end;
   list.SaveToFile('/etc/imapd.conf');
   list.Free;
   RegExpr.Free;
end;
 //##############################################################################
function MyConf.CYRUS_IMAPD_CONF_GET_INFOS(value:string):string;
var RegExpr:TRegExpr;
datas:string;
list:TstringList;
i:integer;
D:boolean;
begin
D:=COMMANDLINE_PARAMETERS('debug');
 if not FileExists('/etc/imapd.conf') then begin
      if D then ShowScreen('IMAPD_CONF_GET_INFOS::Unable to locate /etc/imapd.conf');
      exit;
 end;

 RegExpr:=TRegExpr.create;
 RegExpr.expression:=value+'[:\s]+([a-z]+)';
 list:=TstringList.Create;
 for i:=0 to list.Count -1 do begin
       if RegExpr.exec(list.Strings[i]) then begin
              result:=Trim(RegExpr.Match[1]);
              if D then ShowScreen('IMAPD_CONF_GET_INFOS::found ' +list.Strings[i] + ' -> ' + result);
              break;
       end;
 end;
 List.Free;
 RegExpr.Free;
end;
 //##############################################################################
function MyConf.Cyrus_get_sasl_pwcheck_method;
var RegExpr:TRegExpr;
datas:string;
begin
 RegExpr:=TRegExpr.create;
 datas:=ReadFileIntoString('/etc/imapd.conf');
 RegExpr.expression:='sasl_pwcheck_method[:\s]+([a-z]+)';
 if RegExpr.Exec(datas) then begin
     result:=Trim(RegExpr.Match[1]);
 end;
 RegExpr.Free;
end;
 //##############################################################################
function MyConf.Cyrus_get_lmtpsocket;
var RegExpr:TRegExpr;
datas:string;
begin
 RegExpr:=TRegExpr.create;
 datas:=ReadFileIntoString('/etc/imapd.conf');
 RegExpr.expression:='lmtpsocket[:\s]+([a-z\/]+)';
 if RegExpr.Exec(datas) then begin
     result:=Trim(RegExpr.Match[1]);
 end;
 RegExpr.Free;
end;
 //##############################################################################

function MyConf.Cyrus_get_admins:string;
var RegExpr:TRegExpr;
datas:string;
begin
 RegExpr:=TRegExpr.create;
 datas:=ReadFileIntoString('/etc/imapd.conf');
 RegExpr.expression:='admins[:\s]+([a-z\.\-_0-9]+)';
 if RegExpr.Exec(datas) then begin
     result:=Trim(RegExpr.Match[1]);
 end;
 RegExpr.Free;
end;
 //##############################################################################
function MyConf.Cyrus_get_value(value:string):string;
var RegExpr:TRegExpr;
datas:string;
begin
 RegExpr:=TRegExpr.create;
 datas:=ReadFileIntoString('/etc/imapd.conf');
 RegExpr.expression:=value+'[:\s]+([a-z\.\-_0-9\s]+)';
 if RegExpr.Exec(datas) then begin
     result:=Trim(RegExpr.Match[1]);
 end;
 RegExpr.Free;
end;
 //##############################################################################
function MyConf.Cyrus_get_adminpassword():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('CYRUS','ADMIN_PASSWORD','');
GLOBAL_INI.Free;
end;
 //##############################################################################
procedure MyConf.Cyrus_set_adminpassword(val:string);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
ini.WriteString('CYRUS','ADMIN_PASSWORD',val);
ini.Free;
end;
//#############################################################################
function MyConf.Cyrus_get_admin_name():string;
begin
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
result:=GLOBAL_INI.ReadString('CYRUS','ADMIN','');
GLOBAL_INI.Free;
end;
 //##############################################################################
procedure MyConf.Cyrus_set_admin_name(val:string);
var ini:TIniFile;
begin
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
ini.WriteString('CYRUS','ADMIN',val);
ini.Free;
end;
 //##############################################################################
function MyConf.KAS_INIT():string;
begin
if FileExists('/etc/init.d/kas3') then result:='/etc/init.d/kas3';
end;
 //##############################################################################

function myConf.KAS_APPLY_RULES(path:string):boolean;
var commands:string;
begin
result:=false;
if fileExists(path) then begin
           LOGS.logs('KAS_APPLY_RULES:: -> replicate Kaspersky Anti-Spam rules files : ' +path);
           commands:='/bin/mv ' + path + '/* /usr/local/ap-mailfilter3/conf/def/group/';
           LOGS.logs('KAS_APPLY_RULES:: ' +commands);
           Shell(commands);
           commands:='/usr/local/ap-mailfilter3/bin/sfupdates -s -f';
           LOGS.logs('KAS_APPLY_RULES:: ' +commands);
           ExecProcess(commands);
           result:=true;
        end;
end;



//#############################################################################
procedure MyConf.Cyrus_set_value(info:string;val:string);
var RegExpr:TRegExpr;
list:TstringList;
i:integer;
added:boolean;
begin
LOGS.Enable_echo:=echo_local;
if length(val)=0 then exit;
added:=false;
 RegExpr:=TRegExpr.create;
    list:=TstringList.Create();
    list.LoadFromFile('/etc/imapd.conf');
    for i:=0 to list.Count-1 do begin
          RegExpr.expression:=info;
          if RegExpr.Exec(list.Strings[i]) then begin
               if Debug then LOGS.logs('MyConf.Cyrus_set_value -> found "' + info + '"');
               if Debug then LOGS.logs('MyConf.Cyrus_set_value -> set line "' + IntTostr(i) + '" to "' + val + '"');
               list.Strings[i]:=info+ ': ' + val;
               added:=True;
          end;

    end;
    if added=False then begin
      list.Add(info+ ': ' +val);
    
    end;

   list.SaveToFile('/etc/imapd.conf');
   list.Free;
   RegExpr.Free;
end;
 //##############################################################################

function MyConf.Cyrus_get_servername:string;
var RegExpr:TRegExpr;
datas:string;
begin
 RegExpr:=TRegExpr.create;
 datas:=ReadFileIntoString('/etc/imapd.conf');
 RegExpr.expression:='servername[:\s]+([a-z\.\-_]+)';
 if RegExpr.Exec(datas) then begin
     result:=Trim(RegExpr.Match[1]);
 end;
 RegExpr.Free;
end;
 //#############################################################################
 
function MyConf.Cyrus_get_unixhierarchysep:string;
var RegExpr:TRegExpr;
datas:string;
begin
 RegExpr:=TRegExpr.create;
 datas:=ReadFileIntoString('/etc/imapd.conf');
 RegExpr.expression:='unixhierarchysep[:\s]+([a-z\.\-_]+)';
 if RegExpr.Exec(datas) then begin
     result:=Trim(RegExpr.Match[1]);
 end;
 RegExpr.Free;
end;
 //#############################################################################
function MyConf.Cyrus_get_virtdomain:string;
var RegExpr:TRegExpr;
datas:string;
begin
 RegExpr:=TRegExpr.create;
 datas:=ReadFileIntoString('/etc/imapd.conf');
 RegExpr.expression:='virtdomains[:\s]+([a-z\.\-_]+)';
 if RegExpr.Exec(datas) then begin
     result:=Trim(RegExpr.Match[1]);
 end;
 RegExpr.Free;
end;
 //#############################################################################
function MyConf.LINUX_GET_HOSTNAME:string;
var datas:string;
begin
 Shell('/bin/hostname >/tmp/hostname.txt');
 datas:=ReadFileIntoString('/tmp/hostname.txt');
 result:=Trim(datas);
end;
 //#############################################################################
function MyConf.CYRUS_GET_INITD_PATH:string;
begin
   if FileExists('/etc/init.d/cyrus') then result:='/etc/init.d/cyrus';
   if FileExists('/etc/init.d/cyrus-imapd') then result:='/etc/init.d/cyrus-imapd';
   if FileExists('/etc/init.d/cyrus21') then result:='/etc/init.d/cyrus21';
   if FileExists('/etc/init.d/cyrus2.2') then result:='/etc/init.d/cyrus2.2';
end;
 //#############################################################################

function MyConf.APACHE_GET_INITD_PATH:string;
begin
   if FileExists('/etc/init.d/apache2') then exit('/etc/init.d/apache2');
   if FileExists('/etc/init.d/apache') then exit('/etc/init.d/apache');
   if FileExists('/etc/init.d/httpd') then exit('/etc/init.d/httpd');

end;
 //#############################################################################
function MyConf.APACHE2_DirectoryAddOptions(Change:boolean;WichOption:string):string;

var
   httpd_path,user,group:string;
   RegExpr:TRegExpr;
   RegExpr2:TRegExpr;
   D,start,rend, LineISFound:Boolean;
   list:TstringList;
   i,Start_line,FoundLine:integer;
begin

     D:=COMMANDLINE_PARAMETERS('debug');
     LineISFound:=false;

     httpd_path:=get_httpd_conf();
     if D then showScreen('APACHE2_DirectoryAddOptions: Load file "' +  httpd_path + '"');
     list:=TstringList.Create();
     list.LoadFromFile(httpd_path);

     RegExpr2:=TRegExpr.create;
     RegExpr:=TRegExpr.Create;
     
     RegExpr2.expression:='#';
     start:=False;

         RegExpr.Expression:='<Directory "' + get_www_root + '">';
         if D then showScreen('APACHE2_DirectoryAddOptions: try to found line <Directory "' + get_www_root + '">');
         if D then showScreen('APACHE2_DirectoryAddOptions: file "' +  IntToStr(list.Count) + '" lines..');
         For i:=0 to  list.Count-1 do begin
             if not RegExpr2.Exec(list.Strings[i]) then begin
                if RegExpr.Exec(list.Strings[i]) then begin
                   if start=false then begin
                      start:=True;
                      Start_line:=i;
                      if D then ShowScreen('APACHE2_DirectoryAddOption:: Found start line ' + IntToStr(i));
                      RegExpr.Expression:='Options(.+)';
                   end;
                end;
                if RegExpr.Exec(list.Strings[i]) then begin
                   if Start=true then
                       FoundLine:=i;
                       LineISFound:=True;
                       if D then ShowScreen('APACHE2_DirectoryAddOption:: Found Options in line ' + IntToStr(i));
                       if D then ShowScreen('APACHE2_DirectoryAddOption:: ' + trim(RegExpr.Match[1]));
                       break;
                   end;
                end;
                      
         end;

      if LineISFound=false then begin
          if D then ShowScreen('Unable to found matched pattern');
          exit();
      end;
      if trim(RegExpr.Match[1])='None' then begin
           list.Strings[FoundLine]:=chr(9)+chr(9)+ 'Options ' + WichOption;
           result:='no';
      end;
      
      if trim(RegExpr.Match[1])='none' then begin
         list.Strings[FoundLine]:=chr(9)+chr(9)+ 'Options ' + WichOption;
         result:='no';
      end;
      
      if D then ShowScreen('APACHE2_DirectoryAddOption:: FoundLine=' + IntToStr(FoundLine));
      RegExpr.Expression:=WichOption;
      if not RegExpr.Exec(list.Strings[FoundLine]) then begin
            result:='no';
            list.Strings[FoundLine]:=list.Strings[FoundLine]+ ' ' + WichOption;
      end;

      if Change then list.SaveToFile(httpd_path);
      list.Free;
      

         
         
end;

 //#############################################################################


function MyConf.get_www_userGroup():string;
var
mDatas,user,group:string;
RegExpr:TRegExpr;
RegExpr2:TRegExpr;
list:TstringList;
i:integer;
httpd_path:string;
begin

          httpd_path:=get_httpd_conf();
         if FileExists('/etc/apache2/uid.conf') then httpd_path:='/etc/apache2/uid.conf';
         if length(httpd_path)=0 then exit();

         list:=TstringList.Create();
         list.LoadFromFile(httpd_path);
         
          RegExpr2:=TRegExpr.create;
          RegExpr2.expression:='#';
         
         RegExpr:=TRegExpr.Create;

         
         For i:=0 to  list.Count-1 do begin
             if not RegExpr2.Exec(list.Strings[i])  then begin

                RegExpr.Expression:='Group\s+([a-zA-Z0-9\.\-_]+)';
                if RegExpr.Exec(list.Strings[i]) then begin
                   group:=RegExpr.Match[1];
                end;

                RegExpr.Expression:='User\s+([a-zA-Z0-9\.\-_]+)';
                if RegExpr.Exec(list.Strings[i]) then begin
                   user:=RegExpr.Match[1];
                end;
             
             end;
        end;
         
         RegExpr.Free;
         RegExpr2.Free;
         list.Free;
         result:=user +':' + group;
         
    end;
//##############################################################################
function MyConf.get_www_root():string;
var
mDatas:string;
RegExpr:TRegExpr;
begin


    mDatas:=ReadFileIntoString(get_httpd_conf());
    if FileExists('/etc/apache2/sites-enabled/000-default') then mDatas:=ReadFileIntoString('/etc/apache2/sites-enabled/000-default');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='DocumentRoot[''|"|\s]+([a-zA-Z0-9\/\.]+)["|''|\s|\n]+';
    if RegExpr.Exec(mDatas) then begin
       Result:=RegExpr.Match[1];
       if Result[length(Result)]='/' then Result:=Copy(Result,0,length(Result)-1);
       RegExpr.free;
       exit;
    end;
end;

//##############################################################################
function MyConf.postfix_get_virtual_mailboxes_maps():string;
var
mDatas:string;
RegExpr:TRegExpr;
begin
    mDatas:=ReadFileIntoString('/etc/postfix/main.cf');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='virtual_mailbox_maps.+(hash:|mysql:)([0-9a-zA-Z\.\-_/]+)';
    if RegExpr.Exec(mDatas) then begin
       Result:=RegExpr.Match[2];
       RegExpr.free;
       exit;
    end;
end;
//##############################################################################
function MyConf.POSTFIX_HEADERS_CHECKS():string;
var
mDatas:string;
RegExpr:TRegExpr;
begin
    mDatas:=ExecPipe('/usr/sbin/postconf -h header_checks');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='regexp:([0-9a-zA-Z\.\-_/]+)';
    if RegExpr.Exec(mDatas) then begin
       Result:=RegExpr.Match[1];
       RegExpr.free;
       exit;
    end;
end;
//##############################################################################
procedure MyConf.POSTFIX_CHECK_POSTMAP();
var
mDatas:TstringList;
RegExpr:TRegExpr;
local_path,FilePathName, FilePathNameTO:string;
i:integer;
xLOGS:Tlogs;
begin
    xLOGS:=Tlogs.Create;
    if not FileExists('/etc/postfix/main.cf') then begin
       xLOGS.logs('MYCONF::POSTFIX_CHECK_POSTMAP:: /etc/postfix/main.cf doesn''t exists !!!???');
       exit;
    end;
    
    local_path:=get_ARTICA_PHP_PATH() + '/ressources/conf';
    if debug then writeln('Use ' + local_path + ' as detected config ');
    xLOGS.logs('MYCONF::POSTFIX_CHECK_POSTMAP:: Use ' + local_path + ' as detected config');
    
    mDatas:=TstringList.Create;
    mDatas.LoadFromFile('/etc/postfix/main.cf');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='hash:([0-9a-zA-Z\.\-_/]+)';
    xLOGS.logs('MYCONF::POSTFIX_CHECK_POSTMAP:: FIND hash:([0-9a-zA-Z\.\-_/]+)');
    for i:=0 to  mDatas.Count -1 do begin
         if RegExpr.Exec(mDatas.Strings[i]) then begin
            FilePathName:=local_path + '/' +  ExtractFileName(RegExpr.Match[1]);
            FilePathNameTO:=RegExpr.Match[1];
            xLOGS.logs('MYCONF::POSTFIX_CHECK_POSTMAP:: Found "' + RegExpr.Match[1] + '" => "' +FilePathName + '" => "'+ FilePathNameTO + '"');
            
            
            if fileExists(local_path + '/' +  ExtractFileName(RegExpr.Match[1])) then begin
                    if debug then writeln('Update ' +ExtractFileName(RegExpr.Match[1]));
                    xLOGS.logs('MYCONF::POSTFIX_CHECK_POSTMAP:: /bin/mv ' + FilePathName + ' ' + FilePathNameTO);
                    Shell('/bin/mv ' + FilePathName + ' ' + FilePathNameTO);
                     if debug then writeln('postmap ' +FilePathNameTO);

                     xLOGS.logs('MYCONF::POSTFIX_CHECK_POSTMAP:: /bin/chmod 640 ' + FilePathNameTO);
                     shell('/bin/chmod 640 ' + FilePathNameTO);
                     shell('/bin/chown root ' + FilePathNameTO);
                     xLOGS.logs('MYCONF::POSTFIX_CHECK_POSTMAP:: /usr/sbin/postmap ' + FilePathNameTO);
                     Shell('/usr/sbin/postmap ' + FilePathNameTO);
                     
                     
            end else begin
                 if debug then writeln('No update operation for ' + RegExpr.Match[1] + ' (' + ExtractFileName(RegExpr.Match[1]) + ')');
            end;
            
            
         end;
    end;
    RegExpr.Free;
    mDatas.Free;
end;
//##############################################################################
function MyConf.ARTICA_DAEMON_GET_PID():string;
begin
    result:=SYSTEM_GET_PID('/etc/artica-postfix/artica-agent.pid');
end;
//##############################################################################
function MyConf.ARTICA_FILTER_GET_PID():string;
begin
     result:=SYSTEM_GET_PID('/etc/artica-postfix/artica-filter.pid');
end;
//##############################################################################
function MyConf.BOA_DAEMON_GET_PID():string;
begin
     result:=SYSTEM_GET_PID('/etc/artica-postfix/boa.pid');
end;
//##############################################################################
function MyConf.ARTICA_POLICY_GET_PID():string;
begin
     result:=SYSTEM_GET_PID('/etc/artica-postfix/artica-policy.pid');
end;
//##############################################################################
function MyConf.ReadFileIntoString(path:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:text;
   i:integer;
   datas:string;
   datas_file:string;
   D:boolean;
   Logs:Tlogs;
begin
      D:=false;
      D:=COMMANDLINE_PARAMETERS('debug');

      if not FileExists(path) then begin
        exit;
      end;


      TRY
     assign(Afile,path);
     reset(Afile);
     while not EOF(Afile) do
           begin
           readln(Afile,datas);
           datas_file:=datas_file + datas +CRLF;
           end;

close(Afile);
             EXCEPT

           end;
result:=datas_file;


end;
//##############################################################################
procedure MyConf.killfile(path:string);
Var F : Text;
begin

 if not FileExists(path) then exit;
 if Debug then LOGS.logs('MyConf.killfile -> remove "' + path + '"');
TRY
 Assign (F,path);
 Erase (f);
 EXCEPT
 end;
end;
//##############################################################################
function MyConf.get_LINUX_MAILLOG_PATH():string;
var filedatas,logconfig,ExpressionGrep:string;
D:boolean;
RegExpr:TRegExpr;
begin
 D:=COMMANDLINE_PARAMETERS('debug');
if FileExists('/etc/syslog.conf') then logconfig:='/etc/syslog.conf';
if FileExists('/etc/syslog-ng/syslog-ng.conf') then logconfig:='/etc/syslog-ng/syslog-ng.conf';
if FileExists('/etc/rsyslog.conf') then logconfig:='/etc/rsyslog.conf';

if D then ShowScreen('');
if D then ShowScreen('get_LINUX_MAILLOG_PATH:: Master config is :"'+logconfig+'"');

filedatas:=ReadFileIntoString(logconfig);
   ExpressionGrep:='mail\.=info.+?-([\/a-zA-Z_0-9\.]+)?';
   RegExpr:=TRegExpr.create;
   RegExpr.ModifierI:=True;
   RegExpr.expression:=ExpressionGrep;
   if RegExpr.Exec(filedatas) then  begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     exit;
   end;


   ExpressionGrep:='mail\.\*.+?-([\/a-zA-Z_0-9\.]+)?';
   RegExpr.expression:=ExpressionGrep;
   if RegExpr.Exec(filedatas) then   begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     exit;
   end;
   
   ExpressionGrep:='destination mailinfo[\s\{a-z]+\("(.+?)"';
   RegExpr.expression:=ExpressionGrep;
   if RegExpr.Exec(filedatas) then   begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     exit;
   end;

  RegExpr.Free;
end;
//##############################################################################
function MyConf.POSTFIX_LAST_ERRORS():string;
var filedatas,logPath,cmdline:string;
D,A:boolean;
RegExpr:TRegExpr;
FileData:TstringList;
i:integer;
begin
  logPath:=get_LINUX_MAILLOG_PATH();
  if not FileExists(logpath) then begin
     if D then ShowScreen('POSTFIX_LAST_ERRORS:: Error unable to stat "' + logPath + '"');
     exit;
  end;
  A:=COMMANDLINE_PARAMETERS('errors');
  D:=COMMANDLINE_PARAMETERS('debug');
   RegExpr:=TRegExpr.Create;
   FileData:=TstringList.CReate;
   ArrayList:=TstringList.CReate;
   RegExpr.Expression:='(fatal|failed|failure|deferred|Connection timed out|expired|rejected|warning)';
   cmdline:='/usr/bin/tail -n 2000 ' + logPath;
   if D then ShowScreen('POSTFIX_LAST_ERRORS:: "'+cmdline+'"');
   FileData.LoadFromStream(ExecStream(cmdline,false));
   if D then ShowScreen('POSTFIX_LAST_ERRORS:: tail -> ' + IntToStr(FileData.count) + ' lines');
   For i:=0 to FileData.count-1 do begin
       RegExpr.Expression:='(postfix\/|cyrus\/)';
       if RegExpr.Exec(FileData.Strings[i]) then begin
          RegExpr.Expression:='(fatal|failed|failure|deferred|Connection timed out|expired|rejected)';
            if RegExpr.Exec(FileData.Strings[i]) then begin
               if A then writeln(FileData.Strings[i]);
               ArrayList.Add(FileData.Strings[i]);
            end;
       end;
   
   end;

   RegExpr.free;
   FileData.Free;
   

end;
//##############################################################################
function MyConf.GetIPAddressOfInterface( if_name:ansistring):ansistring;
var
 ifr : ifreq;
 sock : longint;
 p:pChar;

begin
 Result:='0.0.0.0';
 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(if_name), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFADDR, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;
end;
//##############################################################################
function MyConf.CheckInterface( if_name:string):boolean;
var
RegExpr:TRegExpr;
 datas : string;
begin
 Result:=False;
 if not FileExists('/sbin/ifconfig') then exit;
 
     Shell('/sbin/ifconfig ' + if_name + ' >/tmp/ifconfig_' + if_name);
     datas:= ReadFileIntoString('/tmp/ifconfig_' + if_name);
     RegExpr:=TRegExpr.create;
     RegExpr.expression:='adr\:([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)';
     if RegExpr.Exec(datas) then begin
       RegExpr.Free;
       Result:=True;
       exit;
   end;
end;
//##############################################################################
function MyConf.GetIPInterface( if_name:string):string;
var
RegExpr:TRegExpr;
 datas : string;
begin

 if not FileExists('/sbin/ifconfig') then exit;

     Shell('/sbin/ifconfig ' + if_name + ' >/tmp/ifconfig_' + if_name);
     datas:= ReadFileIntoString('/tmp/ifconfig_' + if_name);
     RegExpr:=TRegExpr.create;
     RegExpr.expression:='adr\:([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)';
     if RegExpr.Exec(datas) then begin
       RegExpr.Free;
       if length(RegExpr.Match[1])>0 then Result:=RegExpr.Match[1];
       exit;
   end;
end;
//##############################################################################




function MyConf.LDAP_READ_VALUE_KEY( key:string):string;
var
RegExpr:TRegExpr;
RegExprD:TRegExpr;
 datas : TstringList;
 path:string;
 i:integer;
begin
     path:=LDAP_GET_CONF_PATH();
     if length(path)=0 then exit;
     datas:=Tstringlist.Create;
     datas.LoadFromFile(path);
     RegExpr:=TRegExpr.create;
     RegExprD:=TRegExpr.create;
     
     RegExpr.expression:=key + '["\s]+([a-z0-9\.=,\-]+)';
     RegExprD.expression:='#';

       for i:=0 to datas.count-1 do begin
           if not RegExprD.Exec(datas.Strings[i]) then begin
                 if RegExpr.Exec(datas.Strings[i]) then begin
                      result:=RegExpr.Match[1];
                      break;
                 end;
           
           end;
       end;
       RegExprD.Free;
       RegExpr.Free;
       datas.Free;
       exit;

end;
//##############################################################################
function MyConf.LDAP_READ_SCHEMA_POSTFIX_PATH():string;
var
RegExpr:TRegExpr;
RegExprD:TRegExpr;
 schema:string;
 datas : TstringList;
 path:string;
 i:integer;
begin
     path:=LDAP_GET_CONF_PATH();
     if not fileExists(path) then begin
        writeln('unable to stat ' + path);
        exit;
     end;
     datas:=Tstringlist.Create;

     datas.LoadFromFile(path);
     RegExpr:=TRegExpr.create;
     RegExprD:=TRegExpr.create;

     RegExpr.expression:='include\s+([a-z\/0-9\-_]+postfix.schema)';
     RegExprD.expression:='#';

       for i:=0 to datas.count-1 do begin
           if not RegExprD.Exec(datas.Strings[i]) then begin
                 if RegExpr.Exec(datas.Strings[i]) then begin
                      result:=RegExpr.Match[1];
                      break;
                 end;

           end;
       end;
       RegExprD.Free;
       RegExpr.Free;
       datas.Free;
       exit;

end;
//##############################################################################
procedure MyConf.POSTFIX_REPLICATE_MAIN_CF(mainfile:string);
var
   conf_path,bounce_template_cf:string;
begin

     if not fileExists(mainfile) then begin
       ShowScreen('POSTFIX_REPLICATE_MAIN_CF:: Unable to stat ' + mainfile);
       exit;
     end;

        conf_path:=ExtractFilePath(mainfile);
        ShowScreen('POSTFIX_REPLICATE_MAIN_CF:: conf directory=' + conf_path);
        bounce_template_cf:=conf_path+ '/bounce.template.cf';
        ShowScreen('POSTFIX_REPLICATE_MAIN_CF:: -> ' + bounce_template_cf + ' ?');
        if FileExists(bounce_template_cf) then begin
            shell('/bin/mv ' + bounce_template_cf + ' /etc/postfix');
            ShowScreen('POSTFIX_REPLICATE_MAIN_CF::  move ' + bounce_template_cf + ' (ok)');
        end;


        shell('/bin/mv ' + mainfile + ' /etc/postfix');
        POSTFIX_CHECK_POSTMAP();
        POSTFIX_RESTART_DAEMON();
end;
//#####################################################################################
procedure MyConf.POSTFIX_RELOAD_DAEMON();
var pid:string;
begin
pid:=POSTFIX_PID();
Showscreen('POSTFIX_RELOAD_DAEMON:: -> MASTER PID ' + pid );
if FileExists('/proc/' + pid + '/exe') then begin
   Showscreen('POSTFIX_RELOAD_DAEMON:: -> Reload postfix  ');
   shell('/etc/init.d/postfix reload 2>&1');
   end
   else begin
       Showscreen('POSTFIX_RELOAD_DAEMON:: -> start postfix  ');
       shell('/etc/init.d/postfix start 2>&1');
end;

end;
//#####################################################################################
procedure MyConf.POSTFIX_RESTART_DAEMON();
var pid:string;
begin
pid:=POSTFIX_PID();
Showscreen('POSTFIX_RELOAD_DAEMON:: -> MASTER PID ' + pid );
if FileExists('/proc/' + pid + '/exe') then begin
   Showscreen('POSTFIX_RELOAD_DAEMON:: -> Reload postfix  ');
   shell('/etc/init.d/postfix stop 2>&1');
   shell('/etc/init.d/postfix start 2>&1');
   end
   else begin
       Showscreen('POSTFIX_RELOAD_DAEMON:: -> start postfix  ');
       shell('/etc/init.d/postfix start 2>&1');
end;

end;
//#####################################################################################


function MyConf.LDAP_ADDSCHEMA( schema:string):string;
var
RegExpr:TRegExpr;
RegExprD:TRegExpr;
 datas : TstringList;
 path:string;
 i:integer;
 sfound:boolean;
 schema_path,value:string;
begin
     path:=LDAP_GET_CONF_PATH();
     sfound:=False;
     datas:=Tstringlist.Create;
     datas.LoadFromFile(path);
     RegExpr:=TRegExpr.create;
     RegExprD:=TRegExpr.create;
     schema_path:=LDAP_GET_SCHEMA_PATH();
     RegExpr.expression:='include\s+' + schema_path + '/' +schema;
     RegExprD.expression:='#';
     value:='include' + chr(9) +  schema_path + '/' + schema;
     
 for i:=0 to datas.count-1 do begin
           if not RegExprD.Exec(datas.Strings[i]) then begin
                 if RegExpr.Exec(datas.Strings[i]) then begin
                      datas.Strings[i]:=value;
                      sfound:=True;
                      break;
                 end;

           end;
       end;
       if sfound=False then datas.Add(value);
       datas.SaveToFile(path);
       RegExprD.Free;
       RegExpr.Free;
       datas.Free;
       exit;
end;


//##############################################################################




function MyConf.LDAP_WRITE_VALUE_KEY( key:string;value:string):string;
var
RegExpr:TRegExpr;
RegExprD:TRegExpr;
 datas : TstringList;
 path:string;
 i:integer;
 sfound:boolean;
begin
     path:=LDAP_GET_CONF_PATH();
     sfound:=False;
     datas:=Tstringlist.Create;
     datas.LoadFromFile(path);
     RegExpr:=TRegExpr.create;
     RegExprD:=TRegExpr.create;

     RegExpr.expression:=key;
     RegExprD.expression:='#';

       for i:=0 to datas.count-1 do begin
           if not RegExprD.Exec(datas.Strings[i]) then begin
                 if RegExpr.Exec(datas.Strings[i]) then begin
                      datas.Strings[i]:=key + ' ' + value;
                      sfound:=True;
                      break;
                 end;

           end;
       end;
       if sfound=False then datas.Add(key+ ' ' + value);
       
       datas.SaveToFile(path);
       RegExprD.Free;
       RegExpr.Free;
       datas.Free;
       exit;

end;
//##############################################################################
function MyConf.LDAP_READ_ADMIN_NAME():string;
var
RegExpr:TRegExpr;
RegExprD:TRegExpr;
 datas :string;
 path:string;
 i:integer;
 dc:string;
begin
     path:=LDAP_GET_CONF_PATH();
     datas:=ReadFileIntoString(path);
     RegExpr:=TRegExpr.create;
     RegExpr.Expression:='rootdn[\s]+"cn=([a-zA-Z0-9\-_]+),';
     RegExpr.Exec(datas);
     result:=RegExpr.Match[1];
     RegExpr.Free;


end;
//##############################################################################



procedure MyConf.THREAD_COMMAND_SET(zcommands:string);
var  FileDataCommand:TstringList;
begin
  FileDataCommand:=TstringList.Create;
  if fileExists('/etc/artica-postfix/background') then FileDataCommand.LoadFromFile('/etc/artica-postfix/background');
  FileDataCommand.Add(zcommands);
  FileDataCommand.SaveToFile('/etc/artica-postfix/background');
  FileDataCommand.Free;
  
end;




function MyConf.get_LINUX_INET_INTERFACES():string;
var
 s:shortstring;
 f:text;
 p:LongInt;
 xInterfaces:string;
begin
 assign(f,'/proc/net/dev');
 reset(f);
 while not eof(f) do begin
   readln(f,s);
   p:=pos(':',s);
   if ( p > 0 ) then begin
     delete(s, p, 255);
     while ( s <> '' ) and (s[1]=#32) do delete(s,1,1);
       if CheckInterface(s) then xInterfaces:=xInterfaces + ';'+ s + ':[' + GetIPAddressOfInterface(s) + ']';
   end;
 end;
 exit(xInterfaces);
 close(f);
 end;
//##############################################################################

function MyConf.POSTFIX_PID_PATH():string;
var queue:string;
begin
   queue:=trim(ExecPipe('/usr/sbin/postconf -h queue_directory'));
   result:=queue+'/pid/master.pid';
end;
//##############################################################################
function MyConf.CYRUS_STATUS():string;
var path,pid,res,init:string;D:boolean;
begin
    D:=COMMANDLINE_PARAMETERS('debug');

   init:=CYRUS_GET_INITD_PATH();
   if length(init)=0 then begin
       if D then ShowScreen('CYRUS_STATUS:: no init.d path probably not installed');
       result:='-1;0.0.0;0';
       exit;
   end;
   res:='0';

   if FileExists('/var/run/cyrus-master.pid') then path:='/var/run/cyrus-master.pid';
   if FileExists('/var/run/cyrmaster.pid') then path:='/var/run/cyrmaster.pid';
   if FileExists('/var/run/cyrus.pid') then path:='/var/run/cyrus.pid';
   
   
   
   if D then ShowScreen('CYRUS_STATUS:: pid path is "' + path + '"');
   
   if length(path)=0 then begin
         if D then ShowScreen('CYRUS_STATUS:: No pid path, probably stopped...');
         result:='0;0.0.0;0';
         exit;
   end;
   res:='0';
   pid:=ReadFileIntoString(path);
   pid:=trim(pid);
   if length(pid)=0 then exit('0;' +   CYRUS_VERSION() + ';0');
   if FileExists('/proc/' + pid + '/exe') then res:='1' ;
   result:=res + ';' + CYRUS_VERSION() + ';' +pid


end;
//##############################################################################
function MyConf.MAILGRAPGH_STATUS():string;
var path,pid,pid_path,res,status:string;D:boolean;
begin

   D:=COMMANDLINE_PARAMETERS('debug');
   if not FileExists('/etc/init.d/mailgraph-init') then begin
      status:='-1;0;0';
      exit(status);
      end else begin
          status:='0;'+MAILGRAPH_VERSION() +';0';
      end;
      

   pid_path:=MAILGRAPGH_PID_PATH();
   //ShowScreen('MAILGRAPGH_STATUS::pid_path->' + pid_path);
   if length(pid_path)=0 then begin
      status:='-1;0;0';
      exit(status);
   end;

   pid:=ReadFileIntoString(pid_path);
   pid:=trim(pid);
   if FileExists('/proc/' + pid + '/exe') then status:='1' ;
   result:=status + ';' + MAILGRAPH_VERSION() + ';' +pid;
   exit(result);


end;
//##############################################################################
function MyConf.SYSTEM_NETWORK_INITD():string;
var logs:Tlogs;
begin

if FileExists('/etc/init.d/networking') then exit('/etc/init.d/networking');
if FileExists('/etc/init.d/network') then exit('/etc/init.d/network');
logs:=Tlogs.Create;
logs.logs('SYSTEM_NETWORK_INITD:: unable to locate init.d daemon');
logs.Free;
end;



//##############################################################################
function MyConf.MAILGRAPGH_PID_PATH():string;
var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
begin
if not FileExists('/etc/init.d/mailgraph-init') then exit('');
list:=TstringList.Create;
 RegExpr:=TRegExpr.create;
 RegExpr.Expression:='^PID_FILE[\s''"=]([a-z0-9\-\/\.]+)';
 list.LoadFromFile('/etc/init.d/mailgraph-init');
 for i:=0 to list.Count-1 do begin
     if RegExpr.Exec(list.Strings[i]) then begin
         result:=RegExpr.Match[1];
         RegExpr.free;
         list.free;
         exit;
     end;
 end;
end;
//##############################################################################
function MyConf.SYSTEM_CRON_TASKS():TstringList;
const
  CR = #$0d;
  LF = #$0a;
  CRLF = CR + LF;

var
   RegExpr:TRegExpr;
   list:TstringList;
   ListDatas:TstringList;
   LineDatas:string;
   i:integer;
   SYS:TSystem;
   D:boolean;
   C:boolean;
begin
   D:=COMMANDLINE_PARAMETERS('debug');
   C:=COMMANDLINE_PARAMETERS('list');
   SYS:=TSystem.Create;
   list:=TstringList.Create;
   list.AddStrings(SYS.DirFiles('/etc/cron.d','*'));
   ArrayList:=TstringList.Create;
    for i:=0 to list.Count-1 do begin
          if D then ShowScreen('SYSTEM_CRON_TASKS:: File [' + list.Strings[i] + ']');
          LineDatas:='<cron>' + CRLF  +'<filename>/etc/cron.d/' + list.Strings[i] + '</filename>' + CRLF + '<filedatas>' + ReadFileIntoString('/etc/cron.d/' + list.Strings[i])+CRLF + '</filedatas>' + CRLF + '</cron>';
           ArrayList.Add(LineDatas);
           if C then showscreen(CRLF+'------------------------------------------------------------' + CRLF+LineDatas+CRLF + '------------------------------------------------------------'+CRLF);
          
    end;
   Result:=ArrayList;
   list.free;
   SYS.free;
   
end;
//##############################################################################
function MyConf.FETCHMAIL_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;

begin
 D:=COMMANDLINE_PARAMETERS('debug');
  if not fileExists('/etc/init.d/fetchmail') then begin
      if D then writeln('FETCHMAIL_PID:: not fileExists=/etc/init.d/fetchmail assign it by default on /var/run/fetchmail.pid');
      result:=SYSTEM_GET_PID('/var/run/fetchmail.pid');
      exit;
  end;
  list:=TstringList.Create;
  list.LoadFromFile('/etc/init.d/fetchmail');
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='PIDFILE="(.+?)"';
  for i:=0 to list.Count-1 do begin
       if RegExpr.Exec(list.Strings[i]) then begin
          PidPath:=RegExpr.Match[1];
          break;
       end;
  end;

  list.Free;
  if D then writeln('FETCHMAIL_PID:: PidPath=' + PidPath);
  RegExpr.Free;
  result:=SYSTEM_GET_PID(PidPath);
end;
//##############################################################################


function MyConf.FETCHMAIL_STATUS():string;
var path,pid,res,filedatas,version,firstStat,pidnum:string;
RegExpr:TRegExpr;
d:boolean;
binpath,fetchmailpid:string;
begin

       binpath:=FETCHMAIL_BIN_PATH();
       d:=COMMANDLINE_PARAMETERS('debug');
       if d then ShowScreen('FETCHMAIL_STATUS::-> Reading /etc/init.d/fetchmail script');
       firstStat:='-1';
       
       if length(binpath)=0 then begin
          result:=firstStat+';0.0.0;0';
          exit;
       end;

       version:=FETCHMAIL_VERSION();
       if length(version)>0 then firstStat:='0';
       
       fetchmailpid:=FETCHMAIL_PID();
       if not SYSTEM_PROCESS_EXIST(fetchmailpid) then begin
          exit(firstStat+';' + version + ';0');
       end else begin
           res:='1' ;
           result:=res + ';' + version + ';' +fetchmailpid;
       end;
           if d then ShowScreen('FETCHMAIL_STATUS::Result -> was status;version;pid ->[' + result + ']');


end;




//##############################################################################
function MyConf.POSTFIX_STATUS():string;
var pid,mail_version:string;
begin
pid:=POSTFIX_PID();
if not FileExists('/etc/init.d/postfix') then begin
   result:='-1;0.0.0;' ;
   exit;
end;

if FileExists('/proc/' + pid + '/exe') then result:='1' else result:='0';
mail_version:=trim(ExecPipe('/usr/sbin/postconf -h mail_version'));
result:=result + ';' + mail_version + ';' +pid
end;
//##############################################################################
function MyConf.MYSQL_STATUS():string;
var mysql_init,pid_path,pid,status:string;
D:boolean;

begin
      D:=COMMANDLINE_PARAMETERS('debug');
      pid:='0';
      pid_path:=MYSQL_PID_PATH();
      pid:=SYSTEM_GET_PID(pid_path);
      mysql_init:=MYSQL_INIT_PATH();
      if D then logs.logs('pid_path=' + pid_path + ' mysql_init=' + mysql_init + ' pid=' + pid);
      if length(mysql_init)=0 then begin
         result:='-1;0;0';
      end else begin
          if length(pid_path)=0 then result:='-1;0;0';
          if length(pid)=0 then result:='0;' + MYSQL_VERSION() + ';0';
          if SYSTEM_PROCESS_EXIST(pid) then begin
              status:='1';
              end else begin
                  status:='0';
              end;
       end;
              
      result:=status + ';' + MYSQL_VERSION() + ';' +pid;
            if D then  logs.logs('Mysql result=' + result);

end;
//##############################################################################

function MyConf.MYSQL_PID_PATH():string;
var
   mycnf_path:string;
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   D:boolean;
begin
  D:=COMMANDLINE_PARAMETERS('debug');
  
  mycnf_path:=MYSQL_MYCNF_PATH();
  if D then ShowScreen('MYSQL_PID_PATH::mycnf_path->' + mycnf_path);
  if length(mycnf_path)=0 then exit('');
  list:=TstringList.create;
  list.LoadFromFile(mycnf_path);
  RegExpr:=TRegExpr.create;
  RegExpr.Expression:='pid-file[\s=]+([\/a-z\.A-Z0-9]+)';
  for i:=0 to list.Count-1 do begin
          if RegExpr.Exec(list.Strings[i]) then begin
                result:=RegExpr.Match[1];
                list.Free;
                RegExpr.Free;
                if D then ShowScreen('MYSQL_PID_PATH::success->' + result);
                exit;
          end;
  end;
  if D then ShowScreen('MYSQL_PID_PATH::failed->');
end;
//##############################################################################
function MyConf.SYSTEM_USER_LIST():string;
var RegExpr:TRegExpr;
mstr:string;
list:TstringList;
i:integer;
D:boolean;
begin
   RegExpr:=TRegExpr.Create;
   list:=TstringList.Create;
   ArrayList:=TstringList.Create;
   list.LoadFromFile('/etc/shadow');
   if ParamStr(1)='-userslist' then D:=true;

   RegExpr.Expression:='([a-zA-Z0-9\.\-\_\s]+):';
   for i:=0 to list.Count-1 do begin
         if D then ShowScreen('USER:' + RegExpr.Match[1]);
         if RegExpr.Exec(trim(list.Strings[i])) then begin
             if length(trim(RegExpr.Match[1]))>0 then ArrayList.Add(RegExpr.Match[1]);
         end;
   
   
   end;
list.free;
RegExpr.free;
end;


//##############################################################################
procedure MyConf.AVESERVER_REPLICATE_kav4mailservers(mainfile:string);
var pid,ForwardMailer:string;
stat:integer;
begin
pid:=AVESERVER_GET_PID();
     if not FileExists('/etc/init.d/aveserver') then begin
        lOGS.logs('AVESERVER_REPLICATE_kav4mailservers:: unable to stat /etc/init.d/aveserver');
        exit;
     end;

     if FileExists('/proc/' + pid + '/exe') then stat:=1 else stat:=0;

     if fileExists(mainfile) then begin
        shell('/bin/mv ' + mainfile + ' /etc/kav/5.5/kav4mailservers/kav4mailservers.conf');
        if FileExists('/etc/init.d/kas3') then begin
                 lOGS.logs('AVESERVER_REPLICATE_kav4mailservers:: Kaspersky anti-spam exists in this system..');
                 ForwardMailer:=AVESERVER_GET_VALUE('smtpscan.general','ForwardMailer');
                 if ForwardMailer<>'smtp:127.0.0.1:9025' then begin
                    AVESERVER_SET_VALUE('smtpscan.general','ForwardMailer','smtp:127.0.0.1:9025');
                    AVESERVER_SET_VALUE('smtpscan.general','Protocol','smtp');
                 end;
        end;
        LOGS.logs('AVESERVER_REPLICATE_kav4mailservers::  -> AVESERVER_REPLICATE_TEMPLATES()');
        AVESERVER_REPLICATE_TEMPLATES();

        if stat=0 then shell('/etc/init.d/aveserver start 2>&1');
        if stat=1 then shell('/etc/init.d/aveserver reload 2>&1');
     end
        else begin
          LOGS.logs('AVESERVER_REPLICATE_kav4mailservers::  -> ' + mainfile + ' does not exists');
     end;

end;


//##############################################################################
function MyConf.SYSTEM_CRON_REPLIC_CONFIGS():string;
var RegExpr:TRegExpr;
CronTaskPath:string;
CronTaskkDelete:string;
FileToDelete:string;
list:TstringList;
i:integer;
D:boolean;
SYS:Tsystem;
FileCount:integer;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
     if ParamStr(1)='-replic_cron'then D:=true;
     
     
    CronTaskPath:=get_ARTICA_PHP_PATH() + '/ressources/conf/cron';
    SYS:=Tsystem.Create;
    FileCount:=SYS.DirectoryCountFiles(CronTaskPath);
    if D then ShowScreen('SYSTEM_CRON_REPLIC_CONFIGS: ' + CronTaskPath + ' store ' + IntTOStr(FileCount) + ' files' );
    lOGS.logs('SYSTEM_CRON_REPLIC_CONFIGS:: ' + CronTaskPath + ' store ' + IntTOStr(FileCount) + ' files');
    if FileCount=0 then begin
       SYS.Free;
       exit;
    end;

 

 CronTaskkDelete:=CronTaskPath+ '/CrontaskToDelete';
 if FileExists(CronTaskkDelete) then begin
       list:=TstringList.Create;
       list.LoadFromFile(CronTaskkDelete);
       if D then ShowScreen('SYSTEM_CRON_REPLIC_CONFIGS: ' + IntToStr(list.Count) + ' files to delete');

       
       for i:=0 to list.Count -1 do begin
            FileToDelete:='/etc/cron.d/' + trim(list.Strings[i]);
             if D then ShowScreen('SYSTEM_CRON_REPLIC_CONFIGS: "'+ FileToDelete + '"');
             lOGS.logs('SYSTEM_CRON_REPLIC_CONFIGS:: Delete "'+ FileToDelete + '"');
             if fileExists(FileToDelete) then begin
                  if D then ShowScreen('SYSTEM_CRON_REPLIC_CONFIGS: delete: ' + FileToDelete );
                  shell('/bin/rm ' + FileToDelete);
             end;
       end;
  if D then ShowScreen('SYSTEM_CRON_REPLIC_CONFIGS: delete: ' + CronTaskkDelete );
  shell('/bin/rm ' + CronTaskkDelete);

 end;
  if FileExists(CronTaskPath + '/artica.cron.kasupdate') then shell('/usr/local/ap-mailfilter3/bin/enable-updates.sh');

   shell('/bin/mv '  + CronTaskPath + '/* ' + '/etc/cron.d/');
   shell('/bin/chown root:root /etc/cron.d/*');
   shell('/etc/init.d/cron reload');
   if D then ShowScreen('SYSTEM_CRON_REPLIC_CONFIGS: Done...' );
  lOGS.logs('SYSTEM_CRON_REPLIC_CONFIGS:: Replicate cron task list done...');
end;
//##############################################################################
function MyConf.SYSTEM_DAEMONS_STATUS():TstringList;
var RegExpr:TRegExpr;
mstr:string;
list:TstringList;
i:integer;
D:boolean;
begin
  RegExpr:=TRegExpr.Create;
  list:=TstringList.Create;
  mstr:=KAS_STATUS();
  D:=COMMANDLINE_PARAMETERS('debug');
  
  
  RegExpr.Expression:='([0-9\-]+)-([0-9\-]+);([0-9\-]+)-([0-9\-]+);([0-9\-]+)-([0-9\-]+);([0-9\-]+)-([0-9\-]+)';
  if RegExpr.Exec(mstr) then begin
      list.Add('[APP_KAS3]');
      list.Add('ap-process-server='+RegExpr.Match[1]+ ';' +RegExpr.Match[2]);
      list.Add('ap-spfd='+RegExpr.Match[3]+ ';' +RegExpr.Match[4]);
      list.Add('kas-license='+RegExpr.Match[5]+ ';' +RegExpr.Match[6]);
      list.Add('kas-thttpd='+RegExpr.Match[7]+ ';' +RegExpr.Match[8]);
  end;
   list.Add('');
   mstr:=POSTFIX_STATUS();
   RegExpr.Expression:='([0-9\-]+);([0-9\.]+);([0-9\-]+)';
   if RegExpr.Exec(mstr) then begin
      list.Add('[APP_POSTFIX]');
      list.Add('postfix='+RegExpr.Match[3]+ ';' +RegExpr.Match[1]);
   end;



   list.Add('');
   mstr:=AVESERVER_STATUS();
   RegExpr.Expression:='([0-9\-]+);([0-9\.\sa-zA-Z]+);([0-9\-]+);([0-9\-]+)';
   if RegExpr.Exec(mstr) then begin
      list.Add('[APP_AVESERVER]');
      list.Add('aveserver='+RegExpr.Match[3]+ ';' +RegExpr.Match[1]);
//      list.Add('patternDate='+RegExpr.Match[4]);
   end;

   list.Add('');
   mstr:=FETCHMAIL_STATUS();
   if D then ShowScreen('SYSTEM_DAEMONS_STATUS:: FETCHMAIL=' + mstr);
   RegExpr.Expression:='([0-9\-]+);([0-9\.\sa-zA-Z]+);([0-9\-]+)';
   if RegExpr.Exec(mstr) then begin
      list.Add('[APP_FETCHMAIL]');
      list.Add('fetchmail='+RegExpr.Match[3]+ ';' +RegExpr.Match[1]);
   end;

   list.Add('');
   mstr:=CYRUS_STATUS();
   RegExpr.Expression:='([0-9\-]+);([0-9\.\sa-zA-Z]+);([0-9\-]+)';
   if RegExpr.Exec(mstr) then begin
      list.Add('[APP_CYRUS]');
      list.Add('cyrmaster='+RegExpr.Match[3]+ ';' +RegExpr.Match[1]);
   end;

   list.Add('');
   mstr:=MAILGRAPGH_STATUS();
   RegExpr.Expression:='([0-9\-]+);([0-9\.\sa-zA-Z]+);([0-9\-]+)';
   if RegExpr.Exec(mstr) then begin
      list.Add('[APP_MAILGRAPH]');
      list.Add('mailgraph='+RegExpr.Match[3]+ ';' +RegExpr.Match[1]);
   end;

   list.Add('');
   mstr:=MYSQL_STATUS();
   if D then ShowScreen('SYSTEM_DAEMONS_STATUS:: MYSQL_STATUS=' + mstr);
   RegExpr.Expression:='([0-9\-]+);([0-9\.\sa-zA-Z]+);([0-9\-]+)';
   if RegExpr.Exec(mstr) then begin
      list.Add('[APP_MYSQL]');
      list.Add('mysqld='+RegExpr.Match[3]+ ';' +RegExpr.Match[1]);
   end;



    if ParamStr(2)='all' then begin
          for i:=0 to list.Count-1 do begin
              ShowScreen(list.Strings[i]);

          end;

    end;

    RegExpr.free;
    exit(list);
    list.free;


end;
FUNCTION myConf.KAS_AP_PROCESS_SERVER_PID():string;
begin
  result:=SYSTEM_GET_PID('/usr/local/ap-mailfilter3/run/ap-process-server.pid');
end;
FUNCTION myConf.KAS_AP_SPF_PID():string;
begin
  result:=SYSTEM_GET_PID('/usr/local/ap-mailfilter3/run/ap-spfd.pid');
end;
FUNCTION myConf.KAS_LICENCE_PID():string;
begin
  result:=SYSTEM_GET_PID('/usr/local/ap-mailfilter3/run/kas-license.pid');
end;
FUNCTION myConf.KAS_THTTPD_PID():string;
begin
  result:=SYSTEM_GET_PID('/usr/local/ap-mailfilter3/run/kas-thttpd.pid');
end;


//##############################################################################
function MyConf.KAS_STATUS():string;
var
   pid,one,two,three,four:string;
begin
   pid:=KAS_AP_PROCESS_SERVER_PID();
   if length(pid)=0 then one:='0-0';
   if FileExists('/proc/' + pid + '/exe') then one:=pid+'-1' else one:=pid+'-0';
   
   pid:=KAS_AP_SPF_PID();
   if length(pid)=0 then two:='0-0';
   if FileExists('/proc/' + pid + '/exe') then two:=pid+'-1' else two:=pid+'-0';

   pid:=KAS_LICENCE_PID();
   if length(pid)=0 then three:='0-0';
   if FileExists('/proc/' + pid + '/exe') then three:=pid+'-1' else three:=pid+'-0';

   pid:=KAS_THTTPD_PID();
   if length(pid)=0 then four:='0-0';
   if FileExists('/proc/' + pid + '/exe') then four:=pid+'-1' else four:=pid+'-0';

   result:=one + ';' + two + ';' + three + ';' + four;
end;
//##############################################################################
function MyConf.AVESERVER_STATUS():string;
var pid,pid2:string;
begin
   pid:=AVESERVER_GET_PID();

   if length(pid)=0 then begin
       if FileExists('/etc/init.d/aveserver') then begin
          result:='0;'+ AVESERVER_GET_VERSION()+ ';0;0';
          exit;
       end;
       result:='-1;' + AVESERVER_GET_VERSION() + ';' + pid + ';' + AVESERVER_PATTERN_DATE();
       exit;
   end;
   if FileExists('/proc/' + pid + '/exe') then begin
      result:='1;' + AVESERVER_GET_VERSION() + ';' + pid + ';' + AVESERVER_PATTERN_DATE();
      exit;
   end;

end;
//##############################################################################


function MyConf.AVESERVER_PATTERN_DATE():string;
var
   BasesPath:string;
   xml:string;
   RegExpr:TRegExpr;
begin
//#UpdateDate="([0-9]+)\s+([0-9]+)"#
 BasesPath:=AVESERVER_GET_VALUE('path','BasesPath');
 if not FileExists(BasesPath + '/master.xml') then exit;
 xml:=ReadFileIntoString(BasesPath + '/master.xml');
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='UpdateDate="([0-9]+)\s+([0-9]+)"';
 if RegExpr.Exec(xml) then begin
 
 //date --date "$dte 3 days 5 hours 10 sec ago"
 
    result:=RegExpr.Match[1] + ';' + RegExpr.Match[2];
 end;
 RegExpr.Free;
end;
//##############################################################################
procedure MyConf.ExecProcess(commandline:string);
var
  P: TProcess;
 begin

  P := TProcess.Create(nil);
  P.CommandLine := commandline  + ' &';
  if debug then LOGS.Logs('MyConf.ExecProcess -> ' + commandline);
  P.Execute;
  P.Free;
end;
//##############################################################################
procedure MyConf.MonShell(cmd:string;sh:boolean);
var
  AProcess: TProcess;
 begin
      if sh then cmd:='sh -c "' + cmd + '"';
 
      try
        AProcess := TProcess.Create(nil);
        AProcess.CommandLine := cmd;
        AProcess.Execute;
     finally
        AProcess.Free;
     end;
end;
//##############################################################################
function MyConf.ExecPipe(commandline:string):string;
const
  READ_BYTES = 2048;
  CR = #$0d;
  LF = #$0a;
  CRLF = CR + LF;

var
  S: TStringList;
  M: TMemoryStream;
  P: TProcess;
  n: LongInt;
  BytesRead: LongInt;
  xRes:string;

begin
  // writeln(commandline);
  M := TMemoryStream.Create;
  BytesRead := 0;
  P := TProcess.Create(nil);
  P.CommandLine := commandline;
  P.Options := [poUsePipes];
  if debug then LOGS.Logs('MyConf.ExecPipe -> ' + commandline);

  P.Execute;
  while P.Running do begin
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end
    else begin
      Sleep(100);
    end;

  end;

  repeat
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end;
  until n <= 0;
  M.SetSize(BytesRead);
  S := TStringList.Create;
  S.LoadFromStream(M);
  if debug then LOGS.Logs('Tprocessinfos.ExecPipe -> ' + IntTostr(S.Count) + ' lines');
  for n := 0 to S.Count - 1 do
  begin
    if length(S[n])>1 then begin

      xRes:=xRes + S[n] +CRLF;
    end;
  end;
  if debug then LOGS.Logs('Tprocessinfos.ExecPipe -> exit');
  S.Free;
  P.Free;
  M.Free;
  exit( xRes);
end;
//##############################################################################
function MyConf.SYSTEM_PROCESS_MEMORY(PID:string):integer;
var
   S:string;
   RegExpr:TRegExpr;
   MA,MB,MC,MD,ME,MF,MG,MT:Integer;
begin
     if not FileExists('/proc/' + trim(PID) + '/statm') then exit(0);
     S:=ReadFileIntoString('/proc/' + trim(PID) + '/statm');
     S:=trim(S);
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)';
     if RegExpr.Exec(S) then begin
        MA:=StrToInt(RegExpr.Match[1]);
        MB:=StrToInt(RegExpr.Match[2]);
        MC:=StrToInt(RegExpr.Match[3]);
        ME:=StrToInt(RegExpr.Match[4]);
        MF:=StrToInt(RegExpr.Match[5]);
        MG:=StrToInt(RegExpr.Match[6]);
        MT:=MA+MB+MC+ME+MF+MG;
        result:=MT div 1024;
     end;
end;
//##############################################################################
function MyConf.ExecStream(commandline:string;ShowOut:boolean):TMemoryStream;
const
  READ_BYTES = 2048;
  CR = #$0d;
  LF = #$0a;
  CRLF = CR + LF;

var
  S: TStringList;
  M: TMemoryStream;
  P: TProcess;
  n: LongInt;
  BytesRead: LongInt;
  xRes:string;

begin

  M := TMemoryStream.Create;
  BytesRead := 0;
  P := TProcess.Create(nil);
  P.CommandLine := commandline;
  P.Options := [poUsePipes];
  if ShowOut then WriteLn('-- executing ' + commandline + ' --');
  if debug then LOGS.Logs('Tprocessinfos.ExecPipe -> ' + commandline);
  TRY
     P.Execute;
     while P.Running do begin
           M.SetSize(BytesRead + READ_BYTES);
           n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
           if n > 0 then begin
              Inc(BytesRead, n);
              end else begin
              Sleep(100);
           end;
     end;
  EXCEPT
        P.Free;
        exit;
  end;
  

  repeat
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end;
  until n <= 0;
  M.SetSize(BytesRead);
  exit(M);
end;

//##############################################################################
function MyConf.LINUX_REPOSITORIES_INFOS(inikey:string):string;
var ConfFile:string;
ini:TiniFile;
begin

  ConfFile:=LINUX_CONFIG_INFOS();
  if length(ConfFile)=0 then exit;
  ini:=TIniFile.Create(ConfFile);
  result:=ini.ReadString('REPOSITORIES',inikey,'');
  ini.Free;
end;

//##############################################################################
function MyConf.LINUX_APPLICATION_INFOS(inikey:string):string;
var ConfFile:string;
ini:TiniFile;
begin

  ConfFile:=LINUX_CONFIG_INFOS();
  if length(ConfFile)=0 then exit;
  ini:=TIniFile.Create(ConfFile);
  result:=ini.ReadString('APPLICATIONS',inikey,'');
  ini.Free;
end;
//##############################################################################
function MyConf.LINUX_CONFIG_PATH():string;
var Distri,path,fullPath:string;
begin
   Distri:=LINUX_DISTRIBUTION();
   path:=ExtractFileDir(ParamStr(0));
   fullPath:=path + '/install/distributions/' + Distri;
   if not DirectoryExists(fullpath) then begin
      writeln('Unable to locate necessary folder:"' + fullPath + '"');
      exit();
   end;
   result:=fullpath;
end;
//##############################################################################
function MyConf.LINUX_CONFIG_INFOS():string;
var
   Distri,path,fullPath,include:string;
   sini:TiniFile;

begin
   Distri:=LINUX_DISTRIBUTION();
   path:=ExtractFileDir(ParamStr(0));
   fullPath:=path + '/install/distributions/' + Distri + '/infos.conf';
   if not FileExists(fullpath) then begin
      writeln('Unable to locate necessary file:"' + fullPath + '"');
      exit();
   end;
    sini:=TiniFile.Create(fullPath);
    include:=sini.ReadString('INCLUDE','config','');
    sini.Free;
    if length(include)>0 then begin
          fullPath:=path + '/install/distributions/' + include + '/infos.conf';
          if not FileExists(fullpath) then begin
             writeln('Unable to locate include file:"' + fullPath + '"');
             exit();
          end;
    
    end;

   
   
   result:=fullpath;
end;
//##############################################################################
function Myconf.SYSTEM_DAEMONS_STOP_START(APPS:string;mode:string;return_string:boolean):string;
var commandline:string;log:Tlogs;
begin
     if APPS='APP_POSTFIX' then commandline:='/etc/init.d/postfix '+mode;
     if APPS='APP_AVESERVER' then CommandLine:='/etc/init.d/aveserver '+mode;
     if APPS='APP_KAS3' then CommandLine:='/etc/init.d/kas3 ' +mode;
     if APPS='APP_FETCHMAIL' then CommandLine:='/etc/init.d/fetchmail ' +mode;
     if APPS='APP_CYRUS' then CommandLine:=CYRUS_GET_INITD_PATH() + ' '+mode;
     if APPS='APP_MAILGRAPH' then CommandLine:='/etc/init.d/mailgraph-init ' + mode;
     if APPS='APP_MYSQL' then CommandLine:=MYSQL_INIT_PATH() + ' '+mode;
     if return_string=true then exit(CommandLine);
     log:=Tlogs.Create;
     log.logs('SYSTEM_DAEMONS_STOP_START::Perform operation ' + CommandLine);
     Shell(CommandLine);
     
end;
//##############################################################################
function MyConf.CRON_CREATE_SCHEDULE(ProgrammedTime:string;Croncommand:string;name:string):boolean;
 var FileDatas:TstringList;
begin
  FileDatas:=TstringList.Create;
  FileDatas.Add(ProgrammedTime + ' ' + ' root ' + Croncommand + ' >/dev/null');
  ShowScreen('CRON_CREATE_SCHEDULE:: saving /etc/cron.d/artica.'+name + '.scheduled');
  FileDatas.SaveToFile('/etc/cron.d/artica.'+name + '.scheduled');
  FileDatas.free;
  

end;




function MyConf.LINUX_INSTALL_INFOS(inikey:string):string;
var ConfFile:string;
ini:TiniFile;
D:boolean;
begin
  D:=COMMANDLINE_PARAMETERS('debug');
  ConfFile:=LINUX_CONFIG_INFOS();
  if D then ShowScreen('LINUX_INSTALL_INFOS:: ConfFile="' + ConfFile + '"');
  
  if length(ConfFile)=0 then begin
     ShowScreen('LINUX_INSTALL_INFOS(' + inikey + ') unable to get configuration file path');
     exit;
  end;
  ini:=TIniFile.Create(ConfFile);
  result:=ini.ReadString('INSTALL',inikey,'');
  if length(result)=0 then ShowScreen('LINUX_INSTALL_INFOS([INSTALL]::' + inikey + ') this key has no datas');
  ini.Free;
  exit(result);
end;
//##############################################################################
function MyConf.LINUX_LDAP_INFOS(inikey:string):string;
var ConfFile:string;
ini:TiniFile;
begin

  ConfFile:=LINUX_CONFIG_INFOS();
  if length(ConfFile)=0 then begin
     writeln('LINUX_LDAP_INFOS(' + inikey + ') unable to get configuration file path');
     exit;
  end;
  ini:=TIniFile.Create(ConfFile);
  result:=ini.ReadString('LDAP',inikey,'');
  ini.Free;
  exit(result);
end;
//##############################################################################


//##############################################################################
function MyConf.LINUX_DISTRIBUTION():string;
var
   RegExpr:TRegExpr;
   Filedatas:TstringList;
   i:integer;
   distri_name,distri_ver,distri_provider:string;
begin

  RegExpr:=TRegExpr.Create;
  if FileExists('/etc/lsb-release') then begin
      Filedatas:=TstringList.Create;
      Filedatas.LoadFromFile('/etc/lsb-release');
      for i:=0 to  Filedatas.Count-1 do begin
           RegExpr.Expression:='DISTRIB_ID=(.+)';
           if RegExpr.Exec(Filedatas.Strings[i]) then distri_provider:=RegExpr.Match[1];
           RegExpr.Expression:='DISTRIB_RELEASE=([0-9\.]+)';
           if RegExpr.Exec(Filedatas.Strings[i]) then distri_ver:=RegExpr.Match[1];
           RegExpr.Expression:='DISTRIB_CODENAME=(.+)';
           if RegExpr.Exec(Filedatas.Strings[i]) then distri_name:=RegExpr.Match[1];
      end;


   result:=distri_provider + ' ' +  distri_ver + ' ' +  distri_name;
   RegExpr.Free;
   Filedatas.Free;
   exit();
  end;

  if FileExists('/etc/debian_version') then begin
       Filedatas:=TstringList.Create;
       Filedatas.LoadFromFile('/etc/debian_version');
       RegExpr.Expression:='([0-9\.]+)';
       if RegExpr.Exec(Filedatas.Strings[0]) then begin
          result:='Debian ' + RegExpr.Match[1] +' Gnu-linux';
          RegExpr.Free;
          Filedatas.Free;
          exit();
       end;
  end;
  //Fedora
  if FileExists('/etc/redhat-release') then begin
     Filedatas:=TstringList.Create;
     Filedatas.LoadFromFile('/etc/redhat-release');
     RegExpr.Expression:='Fedora Core release\s+([0-9]+)';
     if RegExpr.Exec(Filedatas.Strings[0]) then begin
          result:='Fedora Core release ' + RegExpr.Match[1];
          RegExpr.Free;
          Filedatas.Free;
          exit();
       end;
      RegExpr.Expression:='Fedora release\s+([0-9]+)';
      if RegExpr.Exec(Filedatas.Strings[0]) then begin
         result:='Fedora release ' + RegExpr.Match[1];
         RegExpr.Free;
         Filedatas.Free;
         exit();
      end;
      //Mandriva
      RegExpr.Expression:='Mandriva Linux release\s+([0-9]+)';
      if RegExpr.Exec(Filedatas.Strings[0]) then begin
         result:='Mandriva Linux release ' + RegExpr.Match[1];
         RegExpr.Free;
         Filedatas.Free;
         exit();
      end;
      //CentOS
      RegExpr.Expression:='CentOS release\s+([0-9]+)';
      if RegExpr.Exec(Filedatas.Strings[0]) then begin
         result:='CentOS release ' + RegExpr.Match[1];
         RegExpr.Free;
         Filedatas.Free;
         exit();
      end;
       
    end;
    
   //Suse
   if FileExists('/etc/SuSE-release') then begin
       Filedatas:=TstringList.Create;
       Filedatas.LoadFromFile('/etc/SuSE-release');
       result:=trim(Filedatas.Strings[0]);
       Filedatas.Free;
       exit;
   end;
    
    

end;
//##############################################################################
procedure MyConf.ShowScreen(line:string);
 var  logs:Tlogs;
 begin
    logs:=Tlogs.Create();
    logs.Enable_echo:=True;
    logs.logs('MYCONF::' + line);
    logs.free;
 
 END;
//##############################################################################
function MyConf.SYSTEM_KERNEL_VERSION():string;
begin
    exit(ExecPipe('/bin/uname -r'));
end;
//##############################################################################
function MyConf.SYSTEM_LIBC_VERSION():string;
var
   head,returned,command:string;
   D:boolean;
   RegExpr:TRegExpr;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
     if FileExists('/usr/bin/head') then head:='/usr/bin/head';
     if length(head)=0 then begin
        if D then ShowScreen('SYSTEM_LIBC_VERSION:: unable to locate head tool');
        exit;
     end;

     if not fileExists('/lib/libc.so.6') then begin
        if D then ShowScreen('SYSTEM_LIBC_VERSION:: unable to stat /lib/libc.so.6');
        exit;
     end;
 command:='/lib/libc.so.6 | ' + head + ' -1';
 if D then ShowScreen('SYSTEM_LIBC_VERSION:: command="'+ command + '"');
 returned:=ExecPipe('/lib/libc.so.6 | ' + head + ' -1');
 if D then ShowScreen('SYSTEM_LIBC_VERSION:: returned="'+ returned + '"');
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='version ([0-9\.]+)';
 if RegExpr.Exec(returned) then SYSTEM_LIBC_VERSION:=RegExpr.Match[1] else begin
      if D then ShowScreen('SYSTEM_LIBC_VERSION:: unable to match pattern');
      exit;
      end;
end;
 
//##############################################################################
function MyConf.SYSTEM_NETWORK_LIST_NICS():string;
var
   list:TStringList;
   hash: THashStringList;
   RegExpr,RegExprH,RegExprF,RegExprG:TRegExpr;
   i:integer;
   D,A:boolean;
   virgule:string;
begin
   A:=false;
   D:=COMMANDLINE_PARAMETERS('debug');
   if ParamStr(1)='-nics' then A:=true;

   list:=TStringList.Create;
   ArrayList:=TStringList.Create;
   list.LoadFromStream(ExecStream('/sbin/ifconfig -a',false));
      RegExpr:=TRegExpr.Create;
      RegExprH:=TRegExpr.Create;
      RegExprG:=TRegExpr.Create;
      RegExprF:=TRegExpr.Create;
      RegExpr.Expression:='^([a-z0-9\:]+)\s+';
      RegExprF.Expression:='^vmnet([0-9\:]+)';
      RegExprG.Expression:='^sit([0-9\:]+)';
      RegExprH.Expression:='^([a-zA-Z0-9]+):avah';

      for i:=0 to list.Count -1 do begin
        if D then ShowScreen('SYSTEM_NETWORK_LIST_NICS::"'+ list.Strings[i] + '"');
        if RegExpr.Exec(list.Strings[i]) then begin
           if not RegExprF.Exec(RegExpr.Match[1]) then begin
              if not RegExprH.Exec(RegExpr.Match[1]) then begin
                 if not RegExprG.Exec(RegExpr.Match[1]) then begin
                    if RegExpr.Match[1]<>'lo' then begin
                       if D then ShowScreen('SYSTEM_NETWORK_LIST_NICS:: ^([a-z0-9\:]+)\s+=>"'+ list.Strings[i] + '"');
                       ArrayList.Add(RegExpr.Match[1]);
                       if A then writeln(RegExpr.Match[1]);
                    end;
                 end;
              end;
           end;
        end;
   end;
   
    List.Free;
    RegExpr.free;
    RegExprF.free;
    RegExprH.free;
    RegExprG.free;

end;
 
//##############################################################################
function MyConf.SYSTEM_NETWORK_INFO_NIC(nicname:string):string;
var      D,A:boolean;
begin

     D:=COMMANDLINE_PARAMETERS('debug');
     if FileExists('/etc/network/interfaces') then begin
         if D then ShowScreen('SYSTEM_NETWORK_INFO_NIC :: Debian system');
         SYSTEM_NETWORK_INFO_NIC_DEBIAN(nicname);
         exit;
     end;
     
     if DirectoryExists('/etc/sysconfig/network-scripts') then begin
      if D then ShowScreen('SYSTEM_NETWORK_INFO_NIC :: redhat system');
      SYSTEM_NETWORK_INFO_NIC_REDHAT(nicname);
      exit;
     end;
      

end;
//##############################################################################
function MyConf.SYSTEM_NETWORK_INFO_NIC_REDHAT(nicname:string):string;
var
   CatchList:TstringList;
   list:Tstringlist;
   i:Integer;
begin

  CatchList:=TStringList.create;
  CatchList.Add('METHOD=redhat');
  list:=TStringList.Create;
  if fileExists('/etc/sysconfig/network-scripts/ifcfg-' + nicname) then begin
        list.LoadFromFile('/etc/sysconfig/network-scripts/ifcfg-' + nicname);
        for i:=0 to list.Count-1 do begin
             CatchList.Add(list.Strings[i]);
        
        end;
  
  end;
 ArrayList:=TStringList.create;
 for i:=0 to CatchList.Count-1 do begin
         if ParamStr(1)='-nic-info' then  writeln(CatchList.Strings[i]);
          ArrayList.Add(CatchList.Strings[i]);
    end;
  CatchList.free;
  list.free;


end;

//##############################################################################
function MyConf.SYSTEM_NETWORK_IFCONFIG():string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;

var
   D,A:boolean;
   expression,key,resultats:string;
   i:integer;
begin
 SYSTEM_NETWORK_LIST_NICS();
 D:=COMMANDLINE_PARAMETERS('debug');
 
 
 for i:=0 to ArrayList.Count-1 do begin
    if D then ShowScreen('SYSTEM_NETWORK_IFCONFIG:: Parse ' + ArrayList.Strings[i]);
       resultats:=resultats + '[' + ArrayList.Strings[i] + ']'+CRLF;
       resultats:=resultats + SYSTEM_NETWORK_IFCONFIG_ETH(ArrayList.Strings[i]) + CRLF;
 end;
   exit(resultats);

end;
//#############################################################################
function MyConf.SYSTEM_ALL_IPS():string;
var
   A,D:boolean;
   LIST:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   LINE:String;

begin
   A:=False;
   D:=False;
   D:=COMMANDLINE_PARAMETERS('debug');
   if ParamStr(1)='-allips' then A:=True;
   LIST:=TstringList.Create;
   ArrayList:=TstringList.Create;


   list.LoadFromStream(ExecStream('/sbin/ifconfig -a',false));
   if D then ShowScreen('SYSTEM_ALL_IPS:: return '+ IntToStr(list.Count) + ' lines');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='inet (adr|addr):([0-9\.]+)';
   for i:=0 to list.Count-1 do begin
           if RegExpr.Exec(list.Strings[i]) then begin
              LINE:=RegExpr.Match[2];
              IF A then writeln(LINE);
              ArrayList.Add(LINE);
           end;
    end;
    RegExpr.free;
    LIST.Free;
end;
//#############################################################################
function MyConf.SYSTEM_PROCESS_PS():string;
var
   A,D:boolean;
   LIST:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   LINE:String;

begin
   A:=False;
   D:=False;
   D:=COMMANDLINE_PARAMETERS('debug');
   if ParamStr(1)='-ps' then A:=True;
   LIST:=TstringList.Create;
   ArrayList:=TstringList.Create;
   
   
   list.LoadFromStream(ExecStream('/bin/ps --no-heading -eo user:80,pid,pcpu,vsz,nice,etime,time,stime,args',false));
   if D then ShowScreen('SYSTEM_PROCESS_PS:: return '+ IntToStr(list.Count) + ' lines');
   RegExpr:=TRegExpr.Create;
   RegExpr.expression:='^(.+?)\s+(.+?)\s+(.+?)\s+(.+?)\s+(.+?)\s+(.+?)\s+(.+?)\s+(.+?)\s+(.+)';
   for i:=0 to list.Count-1 do begin
           if RegExpr.Exec(list.Strings[i]) then begin
              LINE:=RegExpr.Match[1]+';'+RegExpr.Match[2]+';'+RegExpr.Match[3]+';'+RegExpr.Match[4]+';'+RegExpr.Match[5]+';'+RegExpr.Match[6]+';'+RegExpr.Match[7]+';'+RegExpr.Match[8]+';'+RegExpr.Match[9] + ';'+SYSTEM_PROCESS_INFO(RegExpr.Match[2]);
              IF A then writeln(LINE);
              ArrayList.Add(LINE);
           end;
    end;
    RegExpr.free;
    LIST.Free;
end;



//#############################################################################
function MyConf.SYSTEM_PROCESS_INFO(PID:string):string;
var
   A,D:boolean;
   LIST:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   LINE:String;
   Resultats:string;
begin

 if not FileExists('/proc/' + trim(PID) + '/status') then exit;
 LIST:=TstringList.Create;
 LIST.LoadFromFile('/proc/' + trim(PID) + '/status');
   RegExpr:=TRegExpr.Create;
   RegExpr.expression:='(.+?):\s+(.+)';
 for i:=0 to list.Count-1 do begin
     if RegExpr.Exec(list.Strings[i]) then begin
       Resultats:=Resultats +trim(RegExpr.Match[1])+'=' + trim(RegExpr.Match[2])+',';
     end;
 end;
     RegExpr.free;
    LIST.Free;
 exit(resultats);
end;
//#############################################################################

function MyConf.SYSTEM_NETWORK_IFCONFIG_ETH(ETH:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;

var
   D,A:boolean;
   RegExpr:TRegExpr;
   list:Tstringlist;
   CatchList:TstringList;
   expression,key,resultats:string;
   i:integer;
begin
 D:=COMMANDLINE_PARAMETERS('debug');
 list:=TstringList.Create;
 list.LoadFromStream(ExecStream('/sbin/ifconfig -a ' + ETH,false));
 RegExpr:=TRegExpr.Create;
 
 for i:=0 to list.Count-1 do begin
    if D then ShowScreen('SYSTEM_NETWORK_IFCONFIG_ETH:: '+ ETH + 'parse '  + list.Strings[i]);
    RegExpr.Expression:='HWaddr\s+([0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2})';
    if RegExpr.Exec(list.Strings[i]) then resultats:=resultats + 'MAC='+ RegExpr.Match[1] + CRLF;
    
    RegExpr.Expression:='(Masque|Mask):([0-9\.]+)';
    if RegExpr.Exec(list.Strings[i]) then resultats:=resultats + 'NETMASK='+ RegExpr.Match[2] + CRLF;
    
    RegExpr.Expression:='inet (adr|addr):([0-9\.]+)';
    if RegExpr.Exec(list.Strings[i]) then resultats:=resultats + 'IPADDR='+ RegExpr.Match[2] + CRLF;
    
 end;
 if not FileExists('/usr/sbin/ethtool') then ShowScreen('SYSTEM_NETWORK_IFCONFIG_ETH:: unable to stat /usr/sbin/ethtool');
 list.LoadFromStream(ExecStream('/usr/sbin/ethtool ' + ETH,false));
 if D then ShowScreen('SYSTEM_NETWORK_IFCONFIG_ETH:: ' + ETH + ' ethtool report ' + IntToStr(list.Count) + ' lines');
 RegExpr.Expression:='\s+([a-zA-Z0-9\s+]+):\s+(.+)';
  for i:=0 to list.Count-1 do begin
       if RegExpr.Exec(list.Strings[i]) then resultats:= resultats+ RegExpr.Match[1] + '='+ RegExpr.Match[2] + CRLF;
  end;

 exit(resultats);
end;
//#############################################################################
function MyConf.SYSTEM_NETWORK_RECONFIGURE():string;
var
    D,A:boolean;
    list:Tstringlist;
    i:integer;
begin
   D:=COMMANDLINE_PARAMETERS('debug');
   
   if FileExists('/etc/network/interfaces') then begin
        if D Then ShowScreen('SYSTEM_NETWORK_RECONFIGURE:: SYSTEM DEBIAN');
        if not FileExists(get_ARTICA_PHP_PATH() + '/ressources/conf/debian.interfaces') then begin
              if D Then ShowScreen('SYSTEM_NETWORK_RECONFIGURE:: WARNING !!! unable to stat ' + get_ARTICA_PHP_PATH() + '/ressources/conf/debian.interfaces');
        end;
        
        Shell('/bin/mv  ' + get_ARTICA_PHP_PATH() + '/ressources/conf/debian.interfaces /etc/network/interfaces');
        Shell('/etc/init.d/networking force-reload');
        
   end;
   
   if DirectoryExists('/etc/sysconfig/network-scripts') then begin
      if D Then ShowScreen('SYSTEM_NETWORK_RECONFIGURE:: SYSTEM REDHAT');
      if not FileExists(get_ARTICA_PHP_PATH() + '/ressources/conf/eth.list') then begin
         if D Then ShowScreen('SYSTEM_NETWORK_RECONFIGURE:: WARNING !! unable to stat "'+ get_ARTICA_PHP_PATH() + '/ressources/conf/eth.list"');
      end;
      
      list:=Tstringlist.Create;
      List.LoadFromFile(get_ARTICA_PHP_PATH() + '/ressources/conf/eth.list');
      for i:=0 to list.Count-1 do begin
           if D Then ShowScreen('SYSTEM_NETWORK_RECONFIGURE:: -> Modifyl/add ' +list.Strings[i]);
           shell('/bin/mv ' + get_ARTICA_PHP_PATH() + '/ressources/conf/' + list.Strings[i] + ' /etc/sysconfig/network-scripts/');
      
      end;
      Shell('/bin/rm ' + get_ARTICA_PHP_PATH() + '/ressources/conf/eth.list');
      
      if FileExists(get_ARTICA_PHP_PATH() + '/ressources/conf/eth.del') then begin
          List.LoadFromFile(get_ARTICA_PHP_PATH() + '/ressources/conf/eth.del');
         for i:=0 to list.Count-1 do begin
             if D Then ShowScreen('SYSTEM_NETWORK_RECONFIGURE:: -> Delete ' +list.Strings[i]);
             if FileExists('/etc/sysconfig/network-scripts/' + list.Strings[i]) then Shell('/bin/rm /etc/sysconfig/network-scripts/' + list.Strings[i]);
         end;
         Shell('/bin/rm ' + get_ARTICA_PHP_PATH() + '/ressources/conf/eth.del');
      end;
      

      Shell('/etc/init.d/network restart');
   end;
   
end;
//#############################################################################




function MyConf.SYSTEM_NETWORK_INFO_NIC_DEBIAN(nicname:string):string;
var
   D,A:boolean;
   RegExpr:TRegExpr;
   RegExprEnd:TRegExpr;
   RegExprValues:TRegExpr;
   list:Tstringlist;
   CatchList:TstringList;
   expression,key:string;
   i:integer;
begin
        D:=COMMANDLINE_PARAMETERS('debug');
        list:=TStringList.Create;
        CatchList:=TStringList.create;
        RegExprValues:=TRegExpr.Create;
        ArrayList:=TStringList.create;
        
        RegExpr:=TRegExpr.Create;
        RegExprEnd:=TRegExpr.Create;
        expression:='iface\s+'+nicname+'\s+inet\s+(static|dhcp)';
        RegExprEnd.Expression:='^iface';
        RegExprValues.Expression:='^([a-zA-Z\-\_0-9\:]+)\s+(.+)';
        RegExpr.Expression:=expression;

        list.LoadFromFile('/etc/network/interfaces');
        A:=false;
        for i:=0 to list.Count -1 do begin
           if RegExpr.Exec(list.Strings[i]) then begin
              A:=true;
              if D then ShowScreen('SYSTEM_NETWORK_INFO_NIC_DEBIAN:: detect ' + expression + '=' + list.Strings[i] + ' "' + RegExpr.Match[1] +'"');
              list.Strings[i]:='';
              CatchList.Add('BOOTPROTO=' +  RegExpr.Match[1]);
              CatchList.Add('METHOD=debian');
              CatchList.Add('DEVICE='+nicname);
           
           end;
           
           if A=true then begin
              if not RegExprEnd.Exec(list.Strings[i]) then begin
                 if length(trim(list.Strings[i]))>0 then begin
                    if RegExprValues.Exec(list.Strings[i]) then begin
                       key:=RegExprValues.Match[1];
                       if key='address' then key:='IPADDR';
                       if key='netmask' then key:='NETMASK';
                       if key='gateway' then key:='GATEWAY';
                       if key='broadcast' then key:='BROADCAST';
                       if key='network' then key:='NETWORK';
                       if key='metric' then key:='METRIC';
                       CatchList.Add(key + '=' + RegExprValues.Match[2]);
                    end;
                 end;
                 end else begin
                  break;
              end;
           end;
           
        
        end;
    for i:=0 to CatchList.Count-1 do begin
         if ParamStr(1)='-nic-infos' then  writeln(CatchList.Strings[i]);
          ArrayList.Add(CatchList.Strings[i]);
    end;
    RegExpr.free;
    RegExprEnd.free;
    RegExprValues.free;
    
    CatchList.free;
    list.free;

end;
//##############################################################################


function MyConf.SYSTEM_GET_ALL_LOCAL_IP():string;
var
   list:TStringList;
   hash: THashStringList;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
   virgule:string;
begin


   D:=COMMANDLINE_PARAMETERS('debug');
   list:=TStringList.Create;
   list.LoadFromStream(ExecStream('/sbin/ifconfig -a',false));
   hash:=  THashStringList.Create;
   for i:=1 to list.Count -1 do begin
      RegExpr:=TRegExpr.Create;
      RegExpr.Expression:='^([a-z0-9\:]+)\s+';
      if RegExpr.Exec(list.Strings[i]) then begin
         if D then ShowScreen('SYSTEM_GET_ALL_LOCAL_IP:: Found NIC "' + RegExpr.Match[1] + '"');
         hash[RegExpr.Match[1]] :=SYSTEM_GET_LOCAL_IP(RegExpr.Match[1]);
      end;
      RegExpr.Free;
   
   end;

    list.free;
    for i:=0 to hash.Count-1 do begin

        if length(hash[hash.HashCodes[i]])>0 then begin
           if ParamStr(1)='-iplocal' then writeln('NIC -> ',hash.HashCodes[i] + ':' + hash[hash.HashCodes[i]] + ':',i);
           virgule:=',';
           result:=result + hash[hash.HashCodes[i]] + virgule;
        end;

    end;

  if Copy(result,length(result),1)=',' then begin
     result:=Copy(result,1,length(result)-1);
  end;
  hash.Free;

end;
//##############################################################################
function MyConf.SYSTEM_GET_LOCAL_IP(ifname:string):string;
 const
 IP_NAMESIZE = 16;
type
    ipstr = array[0..IP_NAMESIZE-1] of char;

var
 ifr : ifreq;
 sock : longint;
 p:pChar;
 if_name:string;

begin
 Result:='';

 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFADDR, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;
end;
//##############################################################################
function MyConf.SYSTEM_PROCESS_EXISTS(processname:string):boolean;
var
   S:TStringList;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
     if not fileexists('/bin/ps') then begin
        writeln('Unable to locate /bin/ps');
        end;

     RegExpr:=TRegExpr.create;
     S:=TstringList.Create;
     if D then showscreen('SYSTEM_PROCESS_EXISTS:: /bin/ps -eww -orss,vsz,comm');
    // S.LoadFromStream(ExecStream('/bin/ps -eww -orss,vsz,comm',false));
     S.LoadFromStream(ExecStream('/bin/ps -x',false));
     RegExpr.expression:=processname;
     for i:=0 to S.Count -1 do begin
         if RegExpr.Exec(S.Strings[i]) then begin
                if D then showscreen('SYSTEM_PROCESS_EXISTS:: ' + processname + '=' + S.Strings[i]);
                RegExpr.Free;
                S.free;
                exit(true);
         end;

     end;

RegExpr.Free;
S.free;
exit(false);
end;
//##############################################################################
function MyConf.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
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
//####################################################################################



end.
