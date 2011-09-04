unit zarafa_server;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,openldap,apache_artica;

  type
  tzarafa_server=class


private
     LOGS:Tlogs;
     D:boolean;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
    ldap:topenldap;
     procedure server_cfg();

    function  SPOOLER_BIN_PATH():string;
    function  INDEXER_BIN_PATH():string;
    function  MONITOR_BIN_PATH():string;
    function  DAGENT_BIN_PATH():string;
    function  LICENSED_BIN_PATH():string;
    procedure VERIFY_MAPI_SO_PATH();
    function  MONITOR_GET_PID():string;
    function  SPOOLER_GET_PID():string;
    function  GATEWAY_GET_PID():string;
    function  SERVER_GET_PID():string;
    function  DAGENT_GET_PID():string;
    function  INDEXER_GET_PID():string;
    function  LICENSED_GET_PID():string;
    function  ICAL_GET_PID():string;
    function  LIGHTTPD_PID():string;
    procedure LICENSED_START();

    function  APACHE_FOUND_ERROR():boolean;

    procedure CHECK_CYRUS_CONFIG();
    function  GATEWAY_BIN_PATH():string;
    function  ICAL_BIN_PATH():string;
    procedure GATEWAY_START();
    procedure SPOOLER_START();
    procedure MONITOR_START();

    procedure LIGHTTPD_START();
    procedure APACHE_CONFIG();
    procedure INDEXER_START();
    procedure ICAL_START();
    function  ZARAFA_WEB_PID_NUM():string;
    procedure lighttpd_config();


    procedure SPOOLER_STOP();
    procedure MONITOR_STOP();
    procedure GATEWAY_STOP();

    procedure LICENSED_STOP();
    procedure INDEXER_STOP();
    procedure ICAL_STOP();
    function PLUGIN_PATH():string;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure START();
    procedure DAGENT_START();
    procedure DAGENT_STOP();
    function  VERSION(nocache:boolean=false):string;
    function  SERVER_BIN_PATH():string;
    procedure WEB_ACCESS_CONFIG();
    function  STATUS():string;
    procedure STOP();
    procedure REMOVE();
    procedure APACHE_START();
    procedure APACHE_STOP();
    procedure CERTIFICATES();
    procedure APACHE_CERTIFICATES();
    function  BIN_PATH():string;
    procedure SERVER_START();
    procedure SERVER_STOP();

END;

implementation

constructor tzarafa_server.Create(const zSYS:Tsystem);
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
procedure tzarafa_server.free();
begin
    FreeAndNil(logs);
    FreeAndNil(ldap);
end;
//##############################################################################
function tzarafa_server.BIN_PATH():string;
begin
result:=SERVER_BIN_PATH();
end;
//##############################################################################
function tzarafa_server.SERVER_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-server');
end;
//##############################################################################
function tzarafa_server.SPOOLER_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-spooler');
end;
//##############################################################################
function tzarafa_server.MONITOR_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-monitor');
end;
//##############################################################################
function tzarafa_server.GATEWAY_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-gateway');
end;
//##############################################################################
function tzarafa_server.INDEXER_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-indexer');
end;
//##############################################################################
function tzarafa_server.LICENSED_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-licensed');
end;
//##############################################################################
function tzarafa_server.DAGENT_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-dagent');
end;
//##############################################################################
function tzarafa_server.ICAL_BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('zarafa-ical');
end;
//##############################################################################
function tzarafa_server.SERVER_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-server.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-server.pid');
     end;

     if length(result)>2 then exit;
     result:=SYS.PIDOF(SERVER_BIN_PATH());
end;
//##############################################################################
function tzarafa_server.INDEXER_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-indexer.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-indexer.pid');
     end;

     if length(result)>2 then exit;
     result:=SYS.PIDOF(INDEXER_BIN_PATH());
end;
//##############################################################################


function tzarafa_server.ICAL_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-ical.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-ical.pid');
     end;

     if length(result)>2 then exit;
     result:=SYS.PIDOF(ICAL_BIN_PATH());
end;
//##############################################################################

function tzarafa_server.SPOOLER_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-spooler.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-spooler.pid');
     end;

     if length(result)>2 then exit;
     result:=SYS.PIDOF(SPOOLER_BIN_PATH());
end;
//##############################################################################
function tzarafa_server.DAGENT_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-dagent.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-dagent.pid');
     end;

     if length(result)>2 then exit;
     result:=SYS.PIDOF(DAGENT_BIN_PATH());
end;
//##############################################################################


function tzarafa_server.MONITOR_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-monitor.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-monitor.pid');
     end;

     if length(result)>2 then exit;
     result:=SYS.PIDOF(MONITOR_BIN_PATH());
end;
//##############################################################################
function tzarafa_server.GATEWAY_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-gateway.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-gateway.pid');
     end;

     if length(result)>2 then begin
        if SYS.PROCESS_EXIST(result) then exit;
     end;
     result:=SYS.PIDOF(GATEWAY_BIN_PATH());
end;
//##############################################################################
function tzarafa_server.LICENSED_GET_PID():string;
Var
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   PidPath:string;
   D:boolean;
begin
     if FileExists('/var/run/zarafa-licensed.pid') then begin
        result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-licensed.pid');
     end;

     if length(result)>2 then begin
        if SYS.PROCESS_EXIST(result) then exit;
     end;
     result:=SYS.PIDOF(LICENSED_BIN_PATH());
end;
//##############################################################################
procedure tzarafa_server.server_cfg();
var
   l:TStringList;
   ZarafaiCalPort:Integer;
   ZarafaUserSafeMode:integer;
   user_safe_mode:string;
   innodb_file_per_table:integer;
   pluginpath:string;
   ZarafaServerListenIP:string;
   ZarafaStoreOutside:integer;
   ZarafaStoreOutsidePath:string;
   attachment_storage:string;
   ZarafaStoreCompressionLevel:integer;
   ZarafaServerSMTPPORT,EnableZarafaIndexer,ZarafaIndexerInterval,ZarafaIndexerThreads,ZarafaPop3Enable,ZarafaPop3sEnable,ZarafaIMAPEnable:integer;
   ZarafaIMAPsEnable,ZarafaPop3Port,ZarafaIMAPPort,ZarafaPop3sPort,ZarafaIMAPsPort,ZarafaWebNTLM:integer;
   ZarafaServerSMTPIP,APACHE_SRC_ACCOUNT:string;
   CyrusToAD:integer;

begin
attachment_storage:='database';
if not TryStrToInt(SYS.GET_INFO('ZarafaUserSafeMode'),ZarafaUserSafeMode) then ZarafaUserSafeMode:=0;
if not TryStrToInt(SYS.GET_INFO('ZarafaStoreOutside'),ZarafaStoreOutside) then ZarafaStoreOutside:=0;
if not TryStrToInt(SYS.GET_INFO('ZarafaStoreCompressionLevel'),ZarafaStoreCompressionLevel) then ZarafaStoreCompressionLevel:=6;
if not TryStrToInt(SYS.GET_INFO('CyrusToAD'),CyrusToAD) then CyrusToAD:=0;
if not TryStrToInt(SYS.GET_INFO('ZarafaServerSMTPPORT'),ZarafaServerSMTPPORT) then ZarafaServerSMTPPORT:=25;
if not TryStrToInt(SYS.GET_INFO('EnableZarafaIndexer'),EnableZarafaIndexer) then EnableZarafaIndexer:=0;
if not TryStrToInt(SYS.GET_INFO('ZarafaIndexerInterval'),ZarafaIndexerInterval) then ZarafaIndexerInterval:=60;
if not TryStrToInt(SYS.GET_INFO('ZarafaIndexerThreads'),ZarafaIndexerThreads) then ZarafaIndexerThreads:=2;

if not TryStrToInt(SYS.GET_INFO('ZarafaPop3Enable'),ZarafaPop3Enable) then ZarafaPop3Enable:=1;
if not TryStrToInt(SYS.GET_INFO('ZarafaPop3sEnable'),ZarafaPop3sEnable) then ZarafaPop3sEnable:=0;
if not TryStrToInt(SYS.GET_INFO('ZarafaIMAPEnable'),ZarafaIMAPEnable) then ZarafaIMAPEnable:=1;
if not TryStrToInt(SYS.GET_INFO('ZarafaIMAPsEnable'),ZarafaIMAPsEnable) then ZarafaIMAPsEnable:=0;


if not TryStrToInt(SYS.GET_INFO('ZarafaPop3Port'),ZarafaPop3Port) then ZarafaPop3Port:=110;
if not TryStrToInt(SYS.GET_INFO('ZarafaIMAPPort'),ZarafaIMAPPort) then ZarafaIMAPPort:=143;
if not TryStrToInt(SYS.GET_INFO('ZarafaPop3sPort'),ZarafaPop3sPort) then ZarafaPop3sPort:=995;
if not TryStrToInt(SYS.GET_INFO('ZarafaIMAPsPort'),ZarafaIMAPsPort) then ZarafaIMAPsPort:=993;
if not TryStrToInt(SYS.GET_INFO('ZarafaWebNTLM'),ZarafaWebNTLM) then ZarafaWebNTLM:=0;




ZarafaServerSMTPIP:=trim(SYS.GET_INFO('ZarafaServerSMTPIP'));
if length(ZarafaServerSMTPIP)=0 then ZarafaServerSMTPIP:='127.0.0.1';
ZarafaStoreOutsidePath:=trim(SYS.GET_INFO('ZarafaStoreOutsidePath'));


if not TryStrToInt(SYS.GET_INFO('innodb_file_per_table'),innodb_file_per_table) then begin
   SYS.set_INFO('innodb_file_per_table','1');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mysql.build.php');
   fpsystem('/etc/init.d/artica-postfix restart mysql');
end;

if length(ZarafaStoreOutsidePath)<3 then ZarafaStoreOutsidePath:='/var/lib/zarafa';


ZarafaServerListenIP:=SYS.GET_INFO('ZarafaServerListenIP');
if length(trim(ZarafaServerListenIP))=0 then ZarafaServerListenIP:='127.0.0.1';



forceDirectories('/var/log/zarafa');

if ZarafaUserSafeMode=1 then user_safe_mode:='yes' else user_safe_mode:='no';

logs.DebugLogs('Starting zarafa..............: Zarafa-server user safe mode:'+user_safe_mode);
if(ZarafaStoreOutside=1) then begin
  attachment_storage:='files';
  logs.DebugLogs('Starting zarafa..............: Zarafa-server store attachments in "'+ZarafaStoreOutsidePath+'"');
  try ForceDirectories('ZarafaStoreOutsidePath') except logs.DebugLogs('Starting zarafa..............: Fatal error while creating Zarafa attachments path') end;
end;
   fpsystem(SYS.LOCATE_PHP5_BIN()+ ' /usr/share/artica-postfix/exec.freeweb.php --apache-user');
   APACHE_SRC_ACCOUNT:=SYS.GET_INFO('APACHE_SRC_ACCOUNT');

l:=Tstringlist.Create;
l.add('server_bind		= '+ZarafaServerListenIP);
l.add('server_hostname          = '+SYS.HOSTNAME_g());
l.add('server_tcp_enabled	= yes');
l.add('server_tcp_port		= 236');
l.add('server_pipe_enabled	= yes');
l.add('server_pipe_name	= /var/run/zarafa');
l.add('server_name = Zarafa');
l.add('database_engine		= mysql');
l.add('allow_local_users	= yes');
l.add('local_admin_users	= root vmail mail '+APACHE_SRC_ACCOUNT);
l.add('system_email_address	= postmaster@localhost');
l.add('run_as_user		= ');
l.add('run_as_group		= ');
l.add('pid_file		= /var/run/zarafa-server.pid');
l.add('running_path = /');
l.add('session_timeout		= 300');
l.add('license_socket		= /var/run/zarafa-licensed');
l.add('log_method		= syslog');
l.add('log_file		= /var/log/zarafa/server.log');
l.add('log_level		= 2');
l.add('log_timestamp		= 1');
l.add('mysql_host		= '+SYS.MYSQL_INFOS('mysql_server'));
l.add('mysql_port		= '+SYS.MYSQL_INFOS('port'));
l.add('mysql_user		= '+SYS.MYSQL_INFOS('database_admin'));
l.add('mysql_password		= '+SYS.MYSQL_INFOS('database_password'));
l.add('mysql_database		= zarafa');
l.add('attachment_storage	= '+attachment_storage);
l.add('attachment_path		= '+ZarafaStoreOutsidePath);
l.add('attachment_compression	= '+IntTOStr(ZarafaStoreCompressionLevel));
if EnableZarafaIndexer=1 then begin
   l.add('index_services_enabled = yes');
   l.add('index_services_path = file://var/run/zarafa-indexer');
end;

if ZarafaWebNTLM=1 then begin
   logs.DebugLogs('Starting zarafa..............: Zarafa-server NTLM enabled, Apache user: '+APACHE_SRC_ACCOUNT);
   l.add('enable_sso_ntlmauth      = yes');
end;
l.add('server_ssl_enabled	= no');
l.add('server_ssl_port		= 237');
l.add('server_ssl_key_file	= /etc/zarafa/ssl/server.pem');
l.add('server_ssl_key_pass	=  '+ ldap.ldap_settings.password);
l.add('server_ssl_ca_file	= /etc/zarafa/ssl/cacert.pem');
l.add('server_ssl_ca_path	=');
l.add('sslkeys_path		= /etc/ssl/certs/zarafa');
l.add('softdelete_lifetime	= 30');
l.add('sync_lifetime		= 365');
l.add('sync_log_all_changes = yes');
l.add('enable_sso_ntlmauth = no');
l.add('enable_gab = yes');
l.add('auth_method = plugin');
l.add('pam_service = passwd');
l.add('cache_cell_size			= 16777216');
l.add('cache_object_size		= 5242880');
l.add('cache_indexedobject_size	= 16777216');
l.add('cache_quota_lifetime		= 1');
l.add('cache_userdetails_lifetime	= 5');
l.add('thread_stacksize = 512');
l.add('quota_warn		= 0');
l.add('quota_soft		= 0');
l.add('quota_hard		= 0');
l.add('companyquota_warn      = 0');
l.add('user_plugin		= ldap');
l.add('user_plugin_config	= /etc/zarafa/ldap.openldap.cfg');
l.add('# Multi-tenancy configurations');

pluginpath:=PLUGIN_PATH();
logs.DebugLogs('Starting zarafa..............: Zarafa-server plugin path: '+plugin_path);


l.add('# Multi-tenancy configurations');
l.add('enable_hosted_zarafa     = yes');

l.add('createuser_script	=	/etc/zarafa/userscripts/createuser');
l.add('deleteuser_script	=	/etc/zarafa/userscripts/deleteuser');
l.add('creategroup_script	=	/etc/zarafa/userscripts/creategroup');
l.add('deletegroup_script	=	/etc/zarafa/userscripts/deletegroup');
l.add('createcompany_script	=	/etc/zarafa/userscripts/createcompany');
l.add('deletecompany_script	=	/etc/zarafa/userscripts/deletecompany');

l.add('enable_distributed_zarafa = false');
l.add('storename_format = %f');
l.add('loginname_format = %u');
l.add('client_update_enabled = true');
l.add('client_update_path = /var/lib/zarafa/client');
l.add('hide_everyone = no');
l.add('plugin_path		= '+pluginpath);
l.add('user_safe_mode = '+user_safe_mode);
forceDirectories('/etc/zarafa');
logs.WriteToFile(l.Text,'/etc/zarafa/server.cfg');
l.clear;
l.free;



fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.zarafa.build.stores.php --ldap-config');

// /etc/zarafa/spooler.cfg
if(ZarafaServerSMTPPORT>0) then if ZarafaServerSMTPPORT <>25 then ZarafaServerSMTPIP:=ZarafaServerSMTPIP+':'+IntToStr(ZarafaServerSMTPPORT);
l:=Tstringlist.Create;
l.add('smtp_server	=	'+ZarafaServerSMTPIP);
l.add('server_socket	=	file:///var/run/zarafa');
l.add('run_as_user = ');
l.add('run_as_group = ');
l.add('pid_file = /var/run/zarafa-spooler.pid');
l.add('running_path = /');
l.add('log_method	=	syslog');
l.add('log_level	=	3');
l.add('log_file	=	/var/log/zarafa/spooler.log');
l.add('log_timestamp	=	1');
l.add('max_threads = 5');
l.add('fax_domain = fax.local');
l.add('fax_international = 00');
l.add('always_send_delegates = no');
l.add('allow_redirect_spoofing = no');
l.add('copy_delegate_mails = yes');
l.add('always_send_tnef = yes');
logs.WriteToFile(l.Text,'/etc/zarafa/spooler.cfg');
l.clear;
l.free;

 // /etc/zarafa/indexer.cfg
l:=Tstringlist.Create;
l.add('index_path          =   /var/lib/zarafa/index/');
l.add('run_as_user         =');
l.add('run_as_group        =');
l.add('pid_file            =   /var/run/zarafa-indexer.pid');
l.add('running_path        =   /');
l.add('cleanup_lockfiles	=	no');
l.add('server_socket   =   file:///var/run/zarafa');
l.add('server_bind_name   =   file:///var/run/zarafa-indexer');
l.add('log_method          =   syslog');
l.add('log_level           =   2');
l.add('log_timestamp       =   1');
l.add('index_sync_stream	= yes');
l.add('index_interval      =   '+IntToStr(ZarafaIndexerInterval));
l.add('index_threads       =   '+IntToStr(ZarafaIndexerThreads));
l.add('index_max_field_length  = 10000');
l.add('index_merge_factor      = 10');
l.add('index_max_buffered_docs	= 10');
l.add('index_min_merge_docs    = 10');
l.add('index_max_merge_docs    = 2147483647');
l.add('index_term_interval		= 128');
l.add('index_cache_timeout		= 0');
l.add('index_attachments	= yes');
l.add('index_attachment_max_size = 5120');
l.add('index_attachment_parser = /etc/zarafa/indexerscripts/attachments_parser');
l.add('index_attachment_parser_max_memory = 0 ');
l.add('index_attachment_parser_max_cputime = 0');
l.add('index_block_users		=');
l.add('index_block_companies	= ');
l.add('index_allow_servers		=');
logs.WriteToFile(l.Text,'/etc/zarafa/indexer.cfg');
l.clear;
l.free;


 // /etc/zarafa/gateway.cfg
l:=Tstringlist.Create;
l.add('server_bind	=	0.0.0.0');
l.add('server_socket	=	http://localhost:236/zarafa');
l.add('run_as_user = root');
l.add('run_as_group = root');
l.add('pid_file = /var/run/zarafa-gateway.pid');
l.add('running_path = /');
if ZarafaPop3Enable=1 then l.add('pop3_enable	=	yes') else l.add('pop3_enable	=	no');
l.add('pop3_port	=	'+IntToStr(ZarafaPop3Port));
if ZarafaPop3sEnable=1 then l.add('pop3s_enable	=	yes') else l.add('pop3s_enable	=	no');
l.add('pop3s_port	=	'+IntTostr(ZarafaPop3sPort));
if ZarafaIMAPEnable=1 then l.add('imap_enable	=	yes') else l.add('imap_enable	=	no');
l.add('imap_port	=	'+IntToStr(ZarafaIMAPPort));
if ZarafaIMAPsEnable=1 then l.add('imaps_enable	=	yes') else l.add('imaps_enable	=	no');
l.add('imaps_port	=	'+IntToStr(ZarafaIMAPsPort));
l.add('');
l.add('');
l.add('imap_only_mailfolders	=	no');
l.add('imap_public_folders	=	yes');
l.add('imap_capability_idle = yes');
l.add('ssl_private_key_file	=	/etc/ssl/certs/postfix/ca.key');
l.add('ssl_certificate_file	=	/etc/ssl/certs/postfix/ca.crt');
l.add('ssl_verify_client	=	no');
l.add('ssl_verify_file		=	');
l.add('ssl_verify_path		=');
l.add('log_method	=	syslog');
l.add('log_level	=	2');
l.add('log_file	=	/var/log/zarafa/gateway.log');
l.add('log_timestamp	=	1');
logs.WriteToFile(l.Text,'/etc/zarafa/gateway.cfg');
l.clear;
l.free;


 // /etc/zarafa/dagent.cfg
l:=Tstringlist.Create;
l.add('server_bind	=	127.0.0.1 ');
l.add('server_socket	=	file:///var/run/zarafa');
l.add('run_as_user = root');
l.add('run_as_group = root');
l.add('pid_file = /var/run/zarafa-dagent.pid');
l.add('lmtp_max_threads = 20');
l.add('lmtp_port  = 2003');
l.add('log_method	=	syslog');
logs.WriteToFile(l.Text,'/etc/zarafa/dagent.cfg');
l.clear;
l.free;

// /etc/zarafa/monitor.cfg
l:=Tstringlist.Create;
l.add('server_socket	=	file:///var/run/zarafa');
l.add('run_as_user = ');
l.add('run_as_group = ');
l.add('pid_file = /var/run/zarafa-monitor.pid');
l.add('running_path = /');
l.add('log_method	=	syslog');
l.add('log_level	=	2');
l.add('log_file	=	/var/log/zarafa/monitor.log');
l.add('log_timestamp	=	1');
l.add('sslkey_file = /etc/zarafa/ssl/monitor.pem');
l.add('sslkey_pass = replace-with-monitor-cert-password');
l.add('mailquota_resend_interval = 1');
l.add('userquota_warning_template  =   /etc/zarafa/quotamail/userwarning.mail');
l.add('userquota_soft_template     =   /etc/zarafa/quotamail/usersoft.mail');
l.add('userquota_hard_template     =   /etc/zarafa/quotamail/userhard.mail');
l.add('companyquota_warning_template   =   /etc/zarafa/quotamail/companywarning.mail');
l.add('companyquota_soft_template      =   /etc/zarafa/quotamail/companysoft.mail');
l.add('companyquota_hard_template      =   /etc/zarafa/quotamail/companyhard.mail');
logs.WriteToFile(l.Text,'/etc/zarafa/monitor.cfg');
l.clear;
l.free;

// /etc/zarafa/ical.cfg
if not TryStrToInt(SYS.GET_INFO('ZarafaiCalPort'),ZarafaiCalPort) then ZarafaiCalPort:=8088;
l:=Tstringlist.Create;
l.add('server_bind	=	0.0.0.0');
l.add('run_as_user      =       root');
l.add('run_as_group     =       root');
l.add('ical_port	=	'+IntToStr(ZarafaiCalPort));
l.add('ical_enable      =       yes');
l.add('server_socket	=	file:///var/run/zarafa');
l.add('pid_file         =	/var/run/zarafa-ical.pid');
l.add('log_method	=	syslog');
l.add('log_level	=	2');
l.add('log_file	        =	/var/log/zarafa/ical.log');
l.add('log_timestamp	=	1');
logs.WriteToFile(l.Text,'/etc/zarafa/ical.cfg');
l.clear;
l.free;

// /etc/zarafa/licensed.cfg
l:=Tstringlist.Create;
l.add('server_pipe_name =       /var/run/zarafa-licensed');
l.add('server_socket	=	file:///var/run/zarafa');
l.add('license_path	=       /etc/zarafa/license');
l.add('run_as_user	=');
l.add('run_as_group	=');
l.add('pid_file		=       /var/run/zarafa-licensed.pid');
l.add('running_path     =       /');
l.add('log_method	=       syslog');
l.add('log_file		=       -');
l.add('log_level	=       2');
l.add('log_timestamp	=       1');
logs.WriteToFile(l.Text,'/etc/zarafa/licensed.cfg');
l.clear;
l.free;
end;
//#############################################################################
function tzarafa_server.PLUGIN_PATH():string;
begin
    if FileExists('/usr/lib/zarafa/ldapplugin.so') then exit('/usr/lib/zarafa');
    if FileExists('/usr/lib64/zarafa/ldapplugin.so') then exit('/usr/lib64/zarafa');
    if FileExists('/usr/local/lib/zarafa/ldapplugin.so') then exit('/usr/local/lib/zarafa');
    if FileExists('/usr/local/lib64/zarafa/ldapplugin.so') then exit('/usr/local/lib64/zarafa');
end;
//#############################################################################
function tzarafa_server.VERSION(nocache:boolean):string;
var
    path:string;
    RegExpr:TRegExpr;
    FileData:TStringList;
    i:integer;
    D:Boolean;
    tmpstr:string;
begin
     path:=SYS.LOCATE_GENERIC_BIN('zarafa-server');
     if not FileExists(path) then begin
        exit;
     end;


     result:=SYS.GET_CACHE_VERSION('APP_ZARAFA');
     if nocache then result:='';
     if length(result)>1 then exit;
     tmpstr:=logs.FILE_TEMP();
     FileData:=TStringList.Create;
     RegExpr:=TRegExpr.Create;
     fpsystem(path+' -V >'+ tmpstr + ' 2>&1');
     FileData.LoadFromFile(tmpstr);
 RegExpr.Expression:='version:\s+([0-9]+),([0-9]+),([0-9]+)';
  for i:=0 to FileData.Count -1 do begin
          if RegExpr.Exec(FileData.Strings[i]) then  begin
            result:=RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3];
            if length(result)>1 then begin
               SYS.SET_CACHE_VERSION('APP_ZARAFA',result);
               FileData.Free;
               RegExpr.Free;
               exit;
            end;
            end;
          end;


end;
//#############################################################################
procedure tzarafa_server.START();
var zbin:string;
begin

    zbin:=SERVER_BIN_PATH();
    if not FileExists(zbin) then begin
       writeln('Stopping Zarafa..............: Not installed');
       if FileExists('/home/artica/packages/ZARAFA/zarafa.tar') then begin
          fpsystem('/bin/tar -xvf /home/artica/packages/ZARAFA/zarafa.tar -C /');
          fpsystem('/bin/rm /home/artica/packages/ZARAFA/zarafa.tar');
          START();
          exit;
       end;
       exit;
    end;



  server_cfg();
  WEB_ACCESS_CONFIG();
  SERVER_START();
  LICENSED_START();
  SPOOLER_START();
  MONITOR_START();
  GATEWAY_START();
  DAGENT_START();
  ICAL_START();
  INDEXER_START();
  APACHE_START();
end;
//#############################################################################
procedure tzarafa_server.STOP();
var zbin:string;
begin

    zbin:=SERVER_BIN_PATH();
    if not FileExists(zbin) then begin
       logs.DebugLogs('Starting zarafa..............: Zarafa-server not installed');
       exit;
    end;

  SERVER_STOP();
  SPOOLER_STOP();
  MONITOR_STOP();
  GATEWAY_STOP();
  DAGENT_STOP();
  ICAL_STOP();
  INDEXER_STOP();
  LICENSED_STOP();
end;
//#############################################################################
procedure tzarafa_server.SERVER_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   attach:string;
   count:integer;
   ZarafaStoreOutside:integer;
   conflict:string;
   tmpstr:string;
   RegExpr:TRegExpr;
   FileData:TStringList;
   i:integer;
begin
     zbin:=SERVER_BIN_PATH();
     attach:='';
     if not SYS.BuildPids() then begin
         logs.DebugLogs('Starting zarafa..............: artica starter is already running...');
         exit;
     end;

    if not FileExists(zbin) then begin
       logs.DebugLogs('Starting zarafa..............: Zarafa-server not installed');
       exit;
    end;

pid:=SERVER_GET_PID();

if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-server already running PID '+ pid);
      exit;
end;
    if not FileExists('/usr/lib/libicui18n.so.40') then begin
       if FileExists('/usr/lib/libicui18n.so.44') then fpsystem('/bin/ln -s  /usr/lib/libicui18n.so.44 /usr/lib/libicui18n.so.40');
    end;

     if not FileExists('/usr/lib/libicuuc.so.40') then begin
        if FileExists('/usr/lib/libicuuc.so.44') then fpsystem('/bin/ln -s  /usr/lib/libicuuc.so.44 /usr/lib/libicuuc.so.40');
     end;

     if not FileExists('/usr/lib/libicudata.so.40') then begin
        if FileExists('/usr/lib/libicudata.so.44') then fpsystem('/bin/ln -s  /usr/lib/libicudata.so.44 /usr/lib/libicudata.so.40');
     end;



     if not TryStrToInt(SYS.GET_INFO('ZarafaStoreOutside'),ZarafaStoreOutside) then ZarafaStoreOutside:=0;
     if ZarafaStoreOutside=1 then begin
        attach:=' --ignore-attachment-storage-conflict';
        logs.DebugLogs('Starting zarafa..............: Zarafa-server ignore attachments storage conflicts is enabled');
     end;

if FileExists('/etc/artica-postfix/zarafa-ignore-database-version-conflict') then  begin
      conflict:=' --ignore-database-version-conflict';
      logs.DebugLogs('Starting zarafa..............: Zarafa-server ignore Database conflict is enabled');
end;
tmpstr:=logs.FILE_TEMP();
cmd:=zbin+' -c /etc/zarafa/server.cfg'+conflict+attach+' >'+tmpstr+' 2>&1';
logs.DebugLogs('Starting zarafa..............: Zarafa-server config "/etc/zarafa/server.cfg"');
fpsystem(cmd);
FileData:=Tstringlist.Create;
FileData.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
RegExpr:=TRegExpr.Create;
for i:=0 to FileData.Count-1 Do begin
    logs.DebugLogs('Starting zarafa..............: '+FileData.Strings[i]);
end;




pid:=SERVER_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-server (timeout)');
           break;
        end;
        pid:=SERVER_GET_PID();
  end;
pid:=SERVER_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-server success running PID '+ pid);
      if FileExists('/usr/share/doc/zarafa/zarafa7-upgrade') then begin
            if not FileExists('/etc/artica-postfix/zarafa7-upgrade') then begin
                logs.DebugLogs('Starting zarafa..............: Zarafa-server upgrading Zarafa...');
                fpsystem(SYS.LOCATE_GENERIC_BIN('touch')+ ' /etc/artica-postfix/zarafa7-upgrade');
                fpsystem(SYS.LOCATE_GENERIC_BIN('nohup')+ ' '+SYS.LOCATE_GENERIC_BIN('python') +' /usr/share/doc/zarafa/zarafa7-upgrade >/dev/null 2>&1 &');
            end;
      end;
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa-server failed');


end;
//##############################################################################
function tzarafa_server.STATUS():string;
var
pidpath:string;
begin
SYS.MONIT_DELETE('APP_ZARAFA_WEB');
SYS.MONIT_DELETE('APP_ZARAFA_ICAL');
SYS.MONIT_DELETE('APP_ZARAFA_DAGENT');
SYS.MONIT_DELETE('APP_ZARAFA_MONITOR');
SYS.MONIT_DELETE('APP_ZARAFA_GATEWAY');
SYS.MONIT_DELETE('APP_ZARAFA_SPOOLER');
SYS.MONIT_DELETE('APP_ZARAFA_SERVER');
if not FileExists(SERVER_BIN_PATH()) then  exit;

 pidpath:=logs.FILE_TEMP();
 fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --zarafa >'+pidpath +' 2>&1');
 result:=logs.ReadFromFile(pidpath);
 logs.DeleteFile(pidpath);
end;
//##############################################################################
procedure tzarafa_server.SPOOLER_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   count:integer;
begin
     zbin:=SPOOLER_BIN_PATH();
    if not FileExists(zbin) then begin
       exit;
    end;

pid:=SPOOLER_GET_PID();

if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-spooler already running PID '+ pid);
      exit;
end;


logs.DebugLogs('Starting zarafa..............: Zarafa-spooler config "/etc/zarafa/spooler.cfg"');
cmd:=zbin+' -c /etc/zarafa/spooler.cfg';
fpsystem(cmd);

pid:=SPOOLER_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-spooler (timeout)');
           break;
        end;
        pid:=SPOOLER_GET_PID();
  end;
pid:=SPOOLER_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-spooler success running PID '+ pid);
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa-spooler failed "'+cmd+'"');


end;
//##############################################################################
procedure tzarafa_server.GATEWAY_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   count:integer;
begin
     zbin:=GATEWAY_BIN_PATH();
    if not FileExists(zbin) then begin
       exit;
    end;

pid:=GATEWAY_GET_PID();

if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-gateway already running PID '+ pid);
      exit;
end;


logs.DebugLogs('Starting zarafa..............: Zarafa-gateway config "/etc/zarafa/gateway.cfg"');
CHECK_CYRUS_CONFIG();
CERTIFICATES();
cmd:=zbin+' -c /etc/zarafa/gateway.cfg';
fpsystem(cmd);

pid:=GATEWAY_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-gateway (timeout)');
           break;
        end;
        pid:=GATEWAY_GET_PID();
  end;
pid:=GATEWAY_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-gateway success running PID '+ pid);
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa-gateway failed "'+cmd+'"');


end;
//##############################################################################
procedure tzarafa_server.INDEXER_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   count,EnableZarafaIndexer:integer;
begin
     zbin:=INDEXER_BIN_PATH();
    if not FileExists(zbin) then begin
       exit;
    end;
    if not TryStrToInt(SYS.GET_INFO('EnableZarafaIndexer'),EnableZarafaIndexer) then EnableZarafaIndexer:=0;

pid:=INDEXER_GET_PID();


if sys.PROCESS_EXIST(pid) then begin
      if EnableZarafaIndexer=0 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-indexer is disabled, stop it');
           INDEXER_STOP();
           exit;
      end;
      logs.DebugLogs('Starting zarafa..............: Zarafa-indexer already running PID '+ pid);
      exit;
end;

if EnableZarafaIndexer=0 then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-indexer is disabled');
      exit;
end;



logs.DebugLogs('Starting zarafa..............: Zarafa-indexer config "/etc/zarafa/indexer.cfg"');
cmd:=zbin+' -c /etc/zarafa/indexer.cfg';
fpsystem(cmd);

pid:=INDEXER_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-indexer (timeout)');
           break;
        end;
        pid:=INDEXER_GET_PID();
  end;
pid:=INDEXER_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-indexer success running PID '+ pid);
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa-indexer failed "'+cmd+'"');


end;
//##############################################################################
procedure tzarafa_server.INDEXER_STOP();
var
   zbin:string;
   pid:string;
   count:integer;
begin
     zbin:=INDEXER_BIN_PATH();
    if not FileExists(zbin) then begin
       writeln('Stopping Zarafa-indexer......: Not installed');
       exit;
    end;
   pid:=INDEXER_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-indexer......: Already stopped');
       exit;
   end;
   writeln('Stopping Zarafa-indexer......: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
       writeln('Stopping Zarafa-indexer......: time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=INDEXER_GET_PID();
  end;
pid:=INDEXER_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-indexer......: stopped');
       exit;
   end;
       writeln('Stopping Zarafa-indexer......: failed');
end;
//##############################################################################



procedure tzarafa_server.LICENSED_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   count:integer;
begin
     zbin:=LICENSED_BIN_PATH();
    if not FileExists(zbin) then begin
       exit;
    end;

    if not FileExists('/etc/zarafa/license/base') then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-licensed license, no such file');
      exit;
    end;
pid:=LICENSED_GET_PID();

if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-licensed already running PID '+ pid);
      exit;
end;


logs.DebugLogs('Starting zarafa..............: Zarafa-licensed config "/etc/zarafa/licensed.cfg"');
cmd:=zbin+' -c /etc/zarafa/licensed.cfg';
fpsystem(cmd);

pid:=LICENSED_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-licensed (timeout)');
           break;
        end;
        pid:=LICENSED_GET_PID();
  end;
pid:=LICENSED_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-licensed success running PID '+ pid);
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa-licensed failed "'+cmd+'"');


end;
//##############################################################################
procedure tzarafa_server.LICENSED_STOP();
var
   zbin:string;
   pid:string;
   count:integer;
begin
     zbin:=LICENSED_BIN_PATH();
    if not FileExists(zbin) then begin
       writeln('Stopping Zarafa License......: Not installed');
       exit;
    end;
   pid:=LICENSED_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa License......: Already stopped');
       exit;
   end;
       writeln('Stopping Zarafa License......: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
       writeln('Stopping Zarafa License......: Time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=LICENSED_GET_PID();
  end;
pid:=LICENSED_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa License......: stopped');
       exit;
   end;
       writeln('Stopping Zarafa License......: failed');
end;
//##############################################################################
procedure tzarafa_server.MONITOR_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   count:integer;
begin
     zbin:=MONITOR_BIN_PATH();
    if not FileExists(zbin) then begin
       exit;
    end;

pid:=MONITOR_GET_PID();

if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-monitor already running PID '+ pid);
      exit;
end;

logs.DebugLogs('Starting zarafa..............: Zarafa-monitor config "/etc/zarafa/monitor.cfg"');
cmd:=zbin+' -c /etc/zarafa/monitor.cfg';
fpsystem(cmd);

pid:=MONITOR_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-monitor (timeout)');
           break;
        end;
        pid:=MONITOR_GET_PID();
  end;
pid:=MONITOR_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-monitor success running PID '+ pid);
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa-monitor failed');


end;
//##############################################################################
procedure tzarafa_server.DAGENT_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   count:integer;
   tmpstr:string;
   RegExpr:TRegExpr;
   list:TstringList;
   i:integer;
   ZarafaAllowToReinstall:integer;
   fileInstallTime:integer;
begin
     zbin:=DAGENT_BIN_PATH();
    if not FileExists(zbin) then begin
       exit;
    end;

pid:=DAGENT_GET_PID();

if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP already running PID '+ pid);
      exit;
end;
tmpstr:=logs.FILE_TEMP();
logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP config "/etc/zarafa/dagent.cfg"');
cmd:=zbin+' -d -c /etc/zarafa/dagent.cfg >'+tmpstr+' 2>&1';
fpsystem(cmd);

pid:=DAGENT_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP (timeout)');
           break;
        end;
        pid:=DAGENT_GET_PID();
  end;
pid:=DAGENT_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP success running PID '+ pid);
      logs.NOTIFICATION('Zarafa DAgent (LTMP) successfully started pid '+pid,'Zarafa Dagent was successfully started "'+logs.ReadFromFile(tmpstr)+'"','mailbox');
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP failed');
list:=TStringlist.Create;
list.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
RegExpr:=TRegExpr.Create;
if not TryStrToInt(SYS.GET_INFO('ZarafaAllowToReinstall'),ZarafaAllowToReinstall) then ZarafaAllowToReinstall:=1;

for i:=0 to list.Count-1 do begin
     logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP '+list.Strings[i]);
     RegExpr.Expression:='libinetmapi.+?undefined symbol';
     if RegExpr.Exec(list.Strings[i]) then begin
           if ZarafaAllowToReinstall=0 then begin
              logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP you should re-install zarafa !');
              logs.DebugLogs('Starting zarafa..............: Zarafa-LMTP Please run artica-make APP_ZARAFA');
              logs.NOTIFICATION('Please re-install Zarafa','Zarafa Dagent claim "'+list.Strings[i]+'" to fix this issue, please re-install zarafa trough setup-center','mailbox');
           end;

          if ZarafaAllowToReinstall=1 then begin
               fileInstallTime:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/daemon.reinstall.zarafa.time');
               tmpstr:=SYS.LOCATE_GENERIC_BIN('nohup');
               if fileInstallTime<15 then begin
                  logs.NOTIFICATION('Zarafa was automatically re-installed','Zarafa Dagent claim "'+list.Strings[i]+'" to fix this issue, artica has will re-install Zarafa','mailbox');
                  fpsystem(trim(tmpstr+' /usr/share/artica-postfix/bin/artica-make APP_ZARAFA >/dev/null 2>&1 &'));
                  logs.WriteToFile('#','/etc/artica-postfix/daemon.reinstall.zarafa.time');
                  halt(0);
               end;
           end;

     end;

end;
  list.free;
  RegExpr.free;

end;
//##############################################################################

procedure tzarafa_server.ICAL_START();
var
   zbin:string;
   cmd:string;
   pid:string;
   count:integer;
   ZarafaiCalEnable:integer;
begin
    zbin:=ICAL_BIN_PATH();
    if not FileExists(zbin) then exit;
    pid:=ICAL_GET_PID();
    if not TryStrToINt(SYS.GET_INFO('ZarafaiCalEnable'),ZarafaiCalEnable) then ZarafaiCalEnable:=0;





if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa iCal/CalDAV gateway already running PID '+ pid);
      if ZarafaiCalEnable=0 then ICAL_STOP();
      exit;
end;

if ZarafaiCalEnable=0 then begin
   logs.DebugLogs('Starting zarafa..............: Zarafa iCal/CalDAV gateway is disabled by Artica');
   exit;
end;

logs.DebugLogs('Starting zarafa..............: Zarafa iCal/CalDAV gateway config "/etc/zarafa/zarafa-ical.cfg"');
cmd:=zbin+' -c /etc/zarafa/ical.cfg';
fpsystem(cmd);

pid:=ICAL_GET_PID();
count:=0;
 while not SYS.PROCESS_EXIST(pid) do begin
        sleep(100);
        inc(count);
        if count>40 then begin
           logs.DebugLogs('Starting zarafa..............: Zarafa iCal/CalDAV gateway (timeout)');
           break;
        end;
        pid:=ICAL_GET_PID();
  end;
pid:=ICAL_GET_PID();
if sys.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Zarafa iCal/CalDAV gateway success running PID '+ pid);
      exit;
end;
logs.DebugLogs('Starting zarafa..............: Zarafa iCal/CalDAV gateway failed "'+cmd+'"');
end;
//##############################################################################
procedure tzarafa_server.ICAL_STOP();
var
   zbin:string;
   pid:string;
   count:integer;
begin
     zbin:=ICAL_BIN_PATH();
    if not FileExists(zbin) then begin
       writeln('Stopping Zarafa iCal/CalDAV..: Not installed');
       exit;
    end;
   pid:=ICAL_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa iCal/CalDAV..: Already stopped');
       exit;
   end;
       writeln('Stopping Zarafa iCal/CalDAV..: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
       writeln('Stopping Zarafa iCal/CalDAV..: Time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=ICAL_GET_PID();
  end;
pid:=ICAL_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa iCal/CalDAV..: stopped');
       exit;
   end;
       writeln('Zarafa iCal/CalDAV gateway...: failed');
end;
//##############################################################################

procedure tzarafa_server.DAGENT_STOP();
var
   zbin:string;
   pid:string;
   count:integer;
begin
     zbin:=SERVER_BIN_PATH();
    if not FileExists(zbin) then begin
       writeln('Stopping Zarafa-LMTP.........: Not installed');
       exit;
    end;
   pid:=DAGENT_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-LMTP.........: Already stopped');
       exit;
   end;

   writeln('Stopping Zarafa-LMTP.........: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
       writeln('Stopping Zarafa-LMTP.........: time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=DAGENT_GET_PID();
  end;
pid:=DAGENT_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-LMTP.........: stopped');
       logs.NOTIFICATION('Zarafa DAgent (LTMP) PID '+pid+' successfully stopped','Zarafa Dagent was successfully stopped','mailbox');
       exit;
   end;
       writeln('Stopping Zarafa-LMTP.........: failed');
end;
//##############################################################################



procedure tzarafa_server.SERVER_STOP();
var
   zbin:string;
   pid:string;
   count,i,t:integer;
   pids:tstringlist;
begin
     zbin:=SERVER_BIN_PATH();
    if not FileExists(zbin) then begin
       writeln('Stopping Zarafa-server.......: Not installed');
       exit;
    end;
   pid:=SERVER_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-server.......: Already stopped');
       exit;
   end;
   writeln('Stopping Zarafa-server.......: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      if sys.PROCESS_EXIST(pid) then fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
         writeln('Stopping Zarafa-server.......: time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=SERVER_GET_PID();
  end;

    pids:=Tstringlist.Create;
    pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST(zbin));
    for i:=0 to  pids.Count -1 do begin
          if length(pids.Strings[i])=0 then continue;
          if not TryStrToInt(pids.Strings[i],t) then continue;
          if t<3 then continue;
          writeln('Stopping Zarafa-server.......: killing orphan pid: ',t);
          fpsystem('/bin/kill -9 '+intToStr(t));
    end;

    pid:=SERVER_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-server.......: stopped');
       exit;
   end;
    writeln('Stopping Zarafa-server.......: failed');
end;
//##############################################################################
procedure tzarafa_server.GATEWAY_STOP();
var
   zbin:string;
   pid:string;
   count:integer;
begin
     zbin:=GATEWAY_BIN_PATH();
    if not FileExists(zbin) then begin
       writeln('Stopping Zarafa-gateway......: Not installed');
       exit;
    end;
   pid:=GATEWAY_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-gateway......: Already stopped');
       exit;
   end;
   writeln('Stopping Zarafa-gateway......: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
         writeln('Stopping Zarafa-gateway......: time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=GATEWAY_GET_PID();
  end;
pid:=GATEWAY_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-gateway......: stopped');
       exit;
   end;
    writeln('Stopping Zarafa-gateway......: failed');
end;
//##############################################################################
procedure tzarafa_server.SPOOLER_STOP();
var
   zbin:string;
   pid:string;
   count:integer;
begin
   pid:=SPOOLER_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-spooler......: Already stopped');
       exit;
   end;
   writeln('Stopping Zarafa-spooler......: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
         writeln('Stopping Zarafa-spooler......: time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=SPOOLER_GET_PID();
  end;
pid:=SPOOLER_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-spooler......: stopped');
       exit;
   end;
    writeln('Stopping Zarafa-spooler......: failed');
end;
//##############################################################################
procedure tzarafa_server.MONITOR_STOP();
var
   zbin:string;
   pid:string;
   count:integer;
begin
   pid:=MONITOR_GET_PID();

   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-monitor......: Already stopped');
       exit;
   end;
   writeln('Stopping Zarafa-monitor......: PID '+pid);
   fpsystem('/bin/kill '+ pid);
  while sys.PROCESS_EXIST(pid) do begin
      sleep(100);
      fpsystem('/bin/kill '+ pid);
      inc(count);
      if count>50 then begin
         writeln('Stopping Zarafa-monitor......: time-out');
         logs.OutputCmd('/bin/kill -9 ' + pid);
         break;
      end;
      pid:=MONITOR_GET_PID();
  end;
pid:=MONITOR_GET_PID();
   if not sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping Zarafa-monitor......: stopped');
       exit;
   end;
    writeln('Stopping Zarafa-monitor......: failed');
end;
//##############################################################################
procedure tzarafa_server.REMOVE();
var
   l:Tstringlist;
   i:integer;
   path:string;
begin
l:=Tstringlist.Create;
STOP();
l.add('/usr/local/lib/libical.a');
l.add('/usr/local/lib/libical.la');
l.add('/usr/local/lib/libicalmapi.la');
l.add('/usr/local/lib/libicalmapi.so');
l.add('/usr/local/lib/libicalmapi.so.1');
l.add('/usr/local/lib/libicalmapi.so.1.0.0');
l.add('/usr/local/lib/libical.so');
l.add('/usr/local/lib/libical.so.0');
l.add('/usr/local/lib/libical.so.0.44.0');
l.add('/usr/local/lib/libicalss.a');
l.add('/usr/local/lib/libicalss.la');
l.add('/usr/local/lib/libicalss.so');
l.add('/usr/local/lib/libicalss.so.0');
l.add('/usr/local/lib/libicalss.so.0.44.0');
l.add('/usr/local/lib/libicalvcal.a');
l.add('/usr/local/lib/libicalvcal.la');
l.add('/usr/local/lib/libicalvcal.so');
l.add('/usr/local/lib/libicalvcal.so.0');
l.add('/usr/local/lib/libicalvcal.so.0.44.0');
l.add('/usr/local/lib/libinetmapi.la');
l.add('/usr/local/lib/libinetmapi.so');
l.add('/usr/local/lib/libinetmapi.so.1');
l.add('/usr/local/lib/libinetmapi.so.1.0.0');
l.add('/usr/local/lib/libmapi.la');
l.add('/usr/local/lib/libmapi.so');
l.add('/usr/local/lib/libmapi.so.0');
l.add('/usr/local/lib/libmapi.so.0.0.0');
l.add('/usr/local/lib/libvmime.a');
l.add('/usr/local/lib/libvmime.la');
l.add('/usr/local/lib/libvmime.so');
l.add('/usr/local/lib/libvmime.so.0');
l.add('/usr/local/lib/libvmime.so.0.7.1');
l.add('/usr/local/lib/libzarafaclient.la');
l.add('/usr/local/lib/libzarafaclient.so');
l.add('/usr/local/bin/zarafa-admin');
l.add('/usr/local/bin/zarafa-autorespond');
l.add('/usr/local/bin/zarafa-dagent');
l.add('/usr/local/bin/zarafa-fsck');
l.add('/usr/local/bin/zarafa-gateway');
l.add('/usr/local/bin/zarafa-ical');
l.add('/usr/local/bin/zarafa-monitor');
l.add('/usr/local/bin/zarafa-passwd');
l.add('/usr/local/bin/zarafa-server');
l.add('/usr/local/bin/zarafa-spooler');
l.add('/usr/local/bin/zarafa-stats');




if DirectoryExists('/usr/local/lib/zarafa') then begin
   writeln('Remove directory /usr/local/lib/zarafa');
   fpsystem('/bin/rm -rf /usr/local/lib/zarafa');
end;


for i:=0 TO l.Count-1 do begin
    if FileExists(l.Strings[i]) then begin
       writeln('Remove file '+l.Strings[i]);
       fpsystem('/bin/rm '+ l.Strings[i]);
    end else begin
       writeln('file '+l.Strings[i]+' Already removed');
    end;
end;

l.free;
l:=Tstringlist.Create;
l.add('zarafa-admin');
L.add('zarafa-cfgchecker');
L.add('zarafa-dagent');
L.add('zarafa-fsck');
L.add('zarafa-gateway');
L.add('zarafa-ical');
L.add('zarafa-indexer');
L.add('zarafa-monitor');
L.add('zarafa-passwd');
L.add('zarafa-server');
L.add('zarafa-spooler');
L.add('zarafa-stats');

for i:=0 TO l.Count-1 do begin
    path:=SYS.LOCATE_GENERIC_BIN(l.Strings[i]);
    if FileExists(path) then begin
       writeln('Remove file '+path);
       fpsystem('/bin/rm '+ path);
    end else begin
       writeln('file '+l.Strings[i]+' Already removed');
    end;
end;

l.free;
fpsystem('/usr/share/artica-postfix/bin/process1 --force');
fpsystem('/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus --without-zarafa');
fpsystem('/etc/init.d/artica-postfix restart postfix');
fpsystem('/etc/init.d/artica-postfix restart apache');

writeln('done.');
end;

//##############################################################################
procedure tzarafa_server.WEB_ACCESS_CONFIG();
begin
     fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.zarafa.build.stores.php --config');
end;
//##############################################################################
procedure tzarafa_server.VERIFY_MAPI_SO_PATH();
var
   standard_path:string;
   ext_path:string;
   source_ext_dir:string;
   next_path:string;
   i:integer;
begin
   standard_path:=SYS.LOCATE_MAPI_SO();
   if FileExists(standard_path) then begin
        logs.DebugLogs('Starting zarafa..............: Apache found mapi:'+standard_path);
        exit;
   end;


        logs.DebugLogs('Starting zarafa..............: Apache unable to stat mapi.so');
        source_ext_dir:=SYS.LOCATE_PHP5_EXTENSION_DIR();
        ext_path:=ExtractFilePath(source_ext_dir);
        if Copy(ext_path,length(ext_path),1)='/' then ext_path:=Copy(ext_path,1,length(ext_path)-1);
        logs.DebugLogs('Starting zarafa..............: Apache Search location directory in '+ext_path);
        SYS.DirDir(ext_path);
        for i:=0 to SYS.DirListFiles.Count-1 do begin
             next_path:=ext_path+'/'+SYS.DirListFiles.Strings[i]+'/mapi.so';
             if FileExists(next_path) then begin
                  logs.DebugLogs('Starting zarafa..............: Apache found '+next_path+' link it to right path');
                  logs.DebugLogs('Starting zarafa..............: linking '+next_path+' -> '+source_ext_dir+'/mapi.so');
                  fpsystem('/bin/cp '+next_path+' '+source_ext_dir+'/mapi.so');
                  fpsystem('/bin/cp '+next_path+'/'+SYS.DirListFiles.Strings[i]+'/mapi.la '+source_ext_dir+'/mapi.la');
                  break;
             end;
        end;

end;
//##############################################################################


procedure tzarafa_server.APACHE_CONFIG();
var
   apache:tapache_artica;
   apache_bin_path:string;
   modules:string;
   l:TStringlist;
   ZarafaApachePort,ZarafaApacheSSL,LighttpdArticaDisableSSLv2,ZarafaWebNTLM:integer;
   LighttpdUserAndGroup,username,group,ZarafaApacheServerName:string;
   RegExpr:TRegExpr;
begin
apache:=tapache_artica.Create(SYS);
modules:=apache.SET_MODULES();
LighttpdUserAndGroup:=SYS.LIGHTTPD_GET_USER();
ZarafaApacheSSL:=0;
LighttpdArticaDisableSSLv2:=0;
ZarafaWebNTLM:=0;


if not TryStrToInt(SYS.GET_INFO('ZarafaApachePort'),ZarafaApachePort) then ZarafaApachePort:=9010;
if not TryStrToInt(SYS.GET_INFO('ZarafaApacheSSL'),ZarafaApacheSSL) then ZarafaApacheSSL:=0;
if not TryStrToInt(SYS.GET_INFO('LighttpdArticaDisableSSLv2'),LighttpdArticaDisableSSLv2) then LighttpdArticaDisableSSLv2:=0;
if not TryStrToInt(SYS.GET_INFO('ZarafaWebNTLM'),ZarafaWebNTLM) then ZarafaWebNTLM:=0;

ZarafaApacheServerName:=SYS.GET_INFO('ZarafaApacheServerName');
if length(trim(ZarafaApacheServerName))=0 then ZarafaApacheServerName:=SYS.HOSTNAME_g();
logs.DebugLogs('Starting zarafa..............: Server name: '+ZarafaApacheServerName);
logs.DebugLogs('Starting zarafa..............: Port: '+INtToStr(ZarafaApachePort));
logs.DebugLogs('Starting zarafa..............: username:group "'+LighttpdUserAndGroup+'"');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^(.+?):(.+)';
if not RegExpr.Exec(LighttpdUserAndGroup) then begin
 logs.DebugLogs('Starting zarafa..............: Apache daemon unable to stat username and group !');
 exit;
end;

if not DirectoryExists('/usr/share/php/mapi') then begin
   if DirectoryExists('/usr/local/share/php/mapi') then begin
       logs.DebugLogs('Starting zarafa..............: Apache Create a symbolic link from /usr/local/share/php/mapi');
       ForceDirectories('/usr/share/php');
      fpsystem('/bin/ln -s /usr/local/share/php/mapi /usr/share/php/mapi');
   end;
end;

fpsystem('/bin/cp /usr/share/artica-postfix/img/zarafa-login.jpg /usr/share/zarafa-webaccess/client/layout/img/login.jpg');

username:=RegExpr.Match[1];
group:=RegExpr.Match[2];
if length(LighttpdUserAndGroup)=0 then LighttpdUserAndGroup:='www-data:www-data';

forceDirectories('/var/run/zarafa-web');
ForceDirectories('/var/log/apache-zarafa');
ForceDirectories('/var/lib/zarafa-webaccess/tmp');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /var/run/zarafa-web');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /var/log/apache-zarafa');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /var/lib/zarafa-webaccess');
fpsystem('/bin/chmod 777 /var/lib/zarafa-webaccess/tmp');
l:=Tstringlist.Create;
l.add('ServerRoot "/usr/share/zarafa-webaccess"');
l.add('Listen '+INtToStr(ZarafaApachePort));
l.add('User '+username);
l.add('Group '+group);
l.add('PidFile /var/run/zarafa-web/httpd.pid');
l.add(modules);
if ZarafaApacheSSL=1 then begin
 logs.DebugLogs('Starting zarafa..............: Apache daemon SSL enabled');
 l.add('SSLEngine on');
 l.add('SSLCertificateFile /etc/ssl/certs/zarafa/apache.crt.nopass.cert');
 l.add('SSLCertificateKeyFile /etc/ssl/certs/zarafa/apache-ca.key.nopass.key');
 if LighttpdArticaDisableSSLv2=1 then begin
     logs.DebugLogs('Starting zarafa..............: Apache daemon SSLv2 is disabled');
     l.add('SSLProtocol -ALL +SSLv3 +TLSv1');
     l.add('SSLCipherSuite ALL:!aNULL:!ADH:!eNULL:!LOW:!EXP:RC4+RSA:+HIGH:+MEDIUM');
 end;

end else begin
 logs.DebugLogs('Starting zarafa..............: Apache daemon SSL disabled');
end;
l.add('<IfModule !mpm_netware_module>');
l.add('          <IfModule !mpm_winnt_module>');
l.add('             User '+username);
l.add('             Group '+username);
l.add('          </IfModule>');
l.add('</IfModule>');
l.add('ServerAdmin you@example.com');
l.add('ServerName '+ ZarafaApacheServerName);
l.add('DocumentRoot "/usr/share/zarafa-webaccess"');
l.add('<Directory /usr/share/zarafa-webaccess/>');
if ZarafaWebNTLM=1 then begin
l.add('    AuthName "Zarafa logon.."');
l.add('    AuthType Basic');
l.add('    AuthLDAPURL ldap://'+ldap.ldap_settings.servername+':'+ldap.ldap_settings.Port+'/dc=organizations,'+ldap.ldap_settings.suffix+'?uid');
l.add('    AuthLDAPBindDN cn='+ldap.ldap_settings.admin+','+ldap.ldap_settings.suffix);
l.add('    AuthLDAPBindPassword '+ldap.ldap_settings.password);
l.add('    AuthLDAPGroupAttribute memberUid');
l.add('    AuthBasicProvider ldap');
l.add('    AuthzLDAPAuthoritative off');
l.add('    require valid-user');
end;
l.add('    php_value magic_quotes_gpc off');
l.add('    php_flag register_globals off');
l.add('    php_flag magic_quotes_gpc off');
l.add('    php_flag magic_quotes_runtime off');
l.add('    php_value post_max_size 31M');
l.add('    php_value include_path  ".:/usr/share/php:/usr/share/php5:/usr/local/share/php"');
l.add('    php_value upload_max_filesize 30M');
l.add('    php_flag short_open_tag on');
l.add('    php_flag log_errors on');
l.add('    php_value  error_log  "/var/log/apache-zarafa/php.log"');
l.add('    DirectoryIndex index.php');
l.add('    Options -Indexes +FollowSymLinks');
l.add('    AllowOverride Options');
l.add('    Order allow,deny');
l.add('    Allow from all');
l.add('</Directory>');
l.add('<IfModule dir_module>');
l.add('    DirectoryIndex index.php');
l.add('</IfModule>');
l.add('');
l.add('');
l.add('<FilesMatch "^\.ht">');
l.add('    Order allow,deny');
l.add('    Deny from all');
l.add('    Satisfy All');
l.add('</FilesMatch>');
l.add('');
l.add('');
l.add('ErrorLog "/var/log/apache-zarafa/error.log"');
l.add('LogLevel warn');
l.add('');
l.add('<IfModule log_config_module>');
l.add('    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %V" combinedv');
l.add('    LogFormat "%h %l %u %t \"%r\" %>s %b" common');
l.add('');
l.add('    <IfModule logio_module>');
l.add('      LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio');
l.add('    </IfModule>');
l.add('');
l.add('    CustomLog "/var/log/apache-zarafa/access.log" combined');
l.add('    #CustomLog "logs/access_log" combined');
l.add('</IfModule>');
l.add('');
l.add('<IfModule alias_module>');
l.add('    ScriptAlias /cgi-bin/ "/usr/local/apache-groupware/data/cgi-bin/"');
l.add('    Alias /images /usr/share/obm2/resources');
l.add('');
l.add('</IfModule>');
l.add('');
l.add('<IfModule cgid_module>');
l.add('');
l.add('</IfModule>');
l.add('');
l.add('');
l.add('<Directory "/usr/local/apache-groupware/data/cgi-bin">');
l.add('    AllowOverride None');
l.add('    Options None');
l.add('    Order allow,deny');
l.add('    Allow from all');
l.add('</Directory>');
l.add('');
l.add('');
l.add('DefaultType text/plain');
l.add('');
l.add('<IfModule mime_module>');
l.add('   ');
l.add('    TypesConfig /etc/mime.types');
l.add('    #AddType application/x-gzip .tgz');
l.add('    AddType application/x-compress .Z');
l.add('    AddType application/x-gzip .gz .tgz');
l.add('    AddType application/x-httpd-php .php .phtml');
l.add('    #AddHandler cgi-script .cgi');
l.add('    #AddHandler type-map var');
l.add('    #AddType text/html .shtml');
l.add('    #AddOutputFilter INCLUDES .shtml');
l.add('</IfModule>');
l.add('');
l.add('<IfModule ssl_module>');
l.add('SSLRandomSeed startup builtin');
l.add('SSLRandomSeed connect builtin');
l.add('</IfModule>');
logs.WriteToFile(l.Text,'/etc/zarafa/httpd.conf');
l.free;
end;
//##############################################################################
function tzarafa_server.LIGHTTPD_PID():string;
begin
   result:=SYS.GET_PID_FROM_PATH('/var/run/zarafa-web/httpd.pid');
   if length(result)>0 then exit;
   result:=SYS.PIDOF_PATTERN(SYS.LOCATE_GENERIC_BIN('lighttpd') + ' -f /etc/zarafa/lighttpd.conf');


end;
//##############################################################################
procedure tzarafa_server.LIGHTTPD_START();
var
  pid:string;
begin


     pid:=LIGHTTPD_PID();


   if SYS.PROCESS_EXIST(pid) then begin
      logs.Debuglogs('Starting zarafa..............: lighttpd daemon is already running using PID ' + pid + '...');
      exit();
   end;

    lighttpd_config();
    logs.OutputCmd(SYS.LOCATE_GENERIC_BIN('lighttpd')+ ' -f /etc/zarafa/lighttpd.conf');


   if not SYS.PROCESS_EXIST(LIGHTTPD_PID()) then begin
      logs.Debuglogs('Starting zarafa..............: lighttpd Failed "' + SYS.LOCATE_GENERIC_BIN('lighttpd')+ ' -f /etc/zarafa/lighttpd.conf"');
    end else begin
      logs.Debuglogs('Starting zarafa..............: lighttpd Success (PID ' + LIGHTTPD_PID() + ')');
   end;

end;
//##############################################################################

procedure tzarafa_server.lighttpd_config();
var
   apache:tapache_artica;
   apache_bin_path:string;
   modules:string;
   l:TStringlist;
   ZarafaApachePort,ZarafaApacheSSL:integer;
   LighttpdUserAndGroup,username,group,ZarafaApacheServerName:string;
   RegExpr:TRegExpr;

begin
apache:=tapache_artica.Create(SYS);
modules:=apache.SET_MODULES();
LighttpdUserAndGroup:=SYS.LIGHTTPD_GET_USER();
ZarafaApacheSSL:=0;

SYS.set_INFO('php5DisableMagicQuotesGpc','1');


if not TryStrToInt(SYS.GET_INFO('ZarafaApachePort'),ZarafaApachePort) then ZarafaApachePort:=9010;
if not TryStrToInt(SYS.GET_INFO('ZarafaApacheSSL'),ZarafaApacheSSL) then ZarafaApacheSSL:=0;

ZarafaApacheServerName:=SYS.GET_INFO('ZarafaApacheServerName');
if length(ZarafaApacheServerName)=0 then ZarafaApacheServerName:=SYS.HOSTNAME_g();

logs.DebugLogs('Starting zarafa..............: Port: '+INtToStr(ZarafaApachePort));
logs.DebugLogs('Starting zarafa..............: username:group "'+LighttpdUserAndGroup+'"');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^(.+?):(.+)';
if not RegExpr.Exec(LighttpdUserAndGroup) then begin
 logs.DebugLogs('Starting zarafa..............: Apache daemon unable to stat username and group !');
 exit;
end;

if not DirectoryExists('/usr/share/php/mapi') then begin
   if DirectoryExists('/usr/local/share/php/mapi') then begin
       logs.DebugLogs('Starting zarafa..............: Apache Create a symbolic link from /usr/local/share/php/mapi');
      fpsystem('/bin/ln -s /usr/local/share/php/mapi /usr/share/php/mapi');
   end;
end;

fpsystem('/bin/cp /usr/share/artica-postfix/img/zarafa-login.jpg /usr/share/zarafa-webaccess/client/layout/img/login.jpg');

username:=RegExpr.Match[1];
group:=RegExpr.Match[2];
if length(LighttpdUserAndGroup)=0 then LighttpdUserAndGroup:='www-data:www-data';

forceDirectories('/var/run/zarafa-web');
ForceDirectories('/var/log/apache-zarafa');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /var/run/zarafa-web');
fpsystem('/bin/chown '+LighttpdUserAndGroup+' /var/log/apache-zarafa');
l:=Tstringlist.Create;



l.add('#artica-postfix saved by artica lighttpd.conf');
l.add('');
l.add('server.modules = (');
l.add('        "mod_alias",');
l.add('        "mod_access",');
l.add('        "mod_accesslog",');
l.add('        "mod_compress",');
l.add('        "mod_fastcgi",');
l.add('        "mod_cgi",');
l.add('	       "mod_status",');
l.add('	       "mod_auth"');
l.add(')');
l.add('');
l.add('server.document-root        = "/usr/share/zarafa-webaccess"');
l.add('server.username = "'+username+'"');
l.add('server.groupname = "'+group+'"');
l.add('server.errorlog             = "/var/log/lighttpd/zarafa-webaccess-error.log"');
l.add('index-file.names            = ( "index.php")');
l.add('');
l.add('mimetype.assign             = (');
l.add('  ".pdf"          =>      "application/pdf",');
l.add('  ".sig"          =>      "application/pgp-signature",');
l.add('  ".spl"          =>      "application/futuresplash",');
l.add('  ".class"        =>      "application/octet-stream",');
l.add('  ".ps"           =>      "application/postscript",');
l.add('  ".torrent"      =>      "application/x-bittorrent",');
l.add('  ".dvi"          =>      "application/x-dvi",');
l.add('  ".gz"           =>      "application/x-gzip",');
l.add('  ".pac"          =>      "application/x-ns-proxy-autoconfig",');
l.add('  ".swf"          =>      "application/x-shockwave-flash",');
l.add('  ".tar.gz"       =>      "application/x-tgz",');
l.add('  ".tgz"          =>      "application/x-tgz",');
l.add('  ".tar"          =>      "application/x-tar",');
l.add('  ".zip"          =>      "application/zip",');
l.add('  ".mp3"          =>      "audio/mpeg",');
l.add('  ".m3u"          =>      "audio/x-mpegurl",');
l.add('  ".wma"          =>      "audio/x-ms-wma",');
l.add('  ".wax"          =>      "audio/x-ms-wax",');
l.add('  ".ogg"          =>      "application/ogg",');
l.add('  ".wav"          =>      "audio/x-wav",');
l.add('  ".gif"          =>      "image/gif",');
l.add('  ".jar"          =>      "application/x-java-archive",');
l.add('  ".jpg"          =>      "image/jpeg",');
l.add('  ".jpeg"         =>      "image/jpeg",');
l.add('  ".png"          =>      "image/png",');
l.add('  ".xbm"          =>      "image/x-xbitmap",');
l.add('  ".xpm"          =>      "image/x-xpixmap",');
l.add('  ".xwd"          =>      "image/x-xwindowdump",');
l.add('  ".css"          =>      "text/css",');
l.add('  ".html"         =>      "text/html",');
l.add('  ".htm"          =>      "text/html",');
l.add('  ".js"           =>      "text/javascript",');
l.add('  ".asc"          =>      "text/plain",');
l.add('  ".c"            =>      "text/plain",');
l.add('  ".cpp"          =>      "text/plain",');
l.add('  ".log"          =>      "text/plain",');
l.add('  ".conf"         =>      "text/plain",');
l.add('  ".text"         =>      "text/plain",');
l.add('  ".txt"          =>      "text/plain",');
l.add('  ".dtd"          =>      "text/xml",');
l.add('  ".xml"          =>      "text/xml",');
l.add('  ".mpeg"         =>      "video/mpeg",');
l.add('  ".mpg"          =>      "video/mpeg",');
l.add('  ".mov"          =>      "video/quicktime",');
l.add('  ".qt"           =>      "video/quicktime",');
l.add('  ".avi"          =>      "video/x-msvideo",');
l.add('  ".asf"          =>      "video/x-ms-asf",');
l.add('  ".asx"          =>      "video/x-ms-asf",');
l.add('  ".wmv"          =>      "video/x-ms-wmv",');
l.add('  ".bz2"          =>      "application/x-bzip",');
l.add('  ".tbz"          =>      "application/x-bzip-compressed-tar",');
l.add('  ".tar.bz2"      =>      "application/x-bzip-compressed-tar",');
l.add('  ""              =>      "application/octet-stream",');
l.add(' )');
l.add('');
l.add('');
l.add('accesslog.filename          = "/var/log/lighttpd/zarafa-webaccess-access.log"');
l.add('url.access-deny             = ( "~", ".inc" )');
l.add('');
l.add('static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )');
l.add('server.port                 = '+IntTOStr(ZarafaApachePort));
l.add('#server.bind                = "127.0.0.1"');
l.add('#server.error-handler-404   = "/error-handler.html"');
l.add('#server.error-handler-404   = "/error-handler.php"');
l.add('server.pid-file             = "/var/run/zarafa-web/httpd.pid"');
l.add('server.max-fds 		    = 2048');
l.add('');
l.add('fastcgi.server = ( ".php" =>((');
l.add('                "bin-path" => "/usr/bin/php-cgi",');
l.add('                "socket" => "/var/run/lighttpd/php.socket",');
l.add('		       "min-procs" => 1,');
l.add('                "max-procs" => 2,');
l.add('	               	"max-load-per-proc" => 2,');
l.add('                "idle-timeout" => 10,');
l.add('                "bin-environment" => (');
l.add('                        "PHP_FCGI_CHILDREN" => "4",');
l.add('                        "PHP_FCGI_MAX_REQUESTS" => "100"');
l.add('                ),');
l.add('                "bin-copy-environment" => (');
l.add('                        "PATH", "SHELL", "USER"');
l.add('                ),');
l.add('                "broken-scriptfilename" => "enable"');
l.add('        ))');
l.add(')');
l.add('ssl.engine                 = "enable"');
l.add('ssl.pemfile                = "/opt/artica/ssl/certs/lighttpd.pem"');
l.add('status.status-url          = "/server-status"');
l.add('status.config-url          = "/server-config"');
l.add('alias.url += (	"/webmail" 			 => "/usr/share/roundcube")');
l.add('$HTTP["url"] =~ "^/webmail" {');
l.add('	server.follow-symlink = "enable"');
l.add('}');
l.add('$HTTP["url"] =~ "^/webmail/config|/webmail/temp|/webmail/logs" { url.access-deny = ( "" )}');
l.add('alias.url +=("/monitorix"  => "/var/www/monitorix/")');
l.add('alias.url += ("/blocked_attachments"=> "/var/spool/artica-filter/bightml")');
l.add('alias.url += ("/awstats"=> "/usr/share/awstats")');
l.add('alias.url += ("/pipermail/" => "/var/lib/mailman/archives/public/")');
l.add('alias.url += ( "/cgi-bin/" => "/usr/lib/cgi-bin/" )');
l.add('');
l.add('cgi.assign= (');
l.add('	".pl"  => "/usr/bin/perl",');
l.add('	".php" => "/usr/bin/php-cgi",');
l.add('	".py"  => "/usr/bin/python",');
l.add('	".cgi"  => "/usr/bin/perl",');
l.add('	"/admin" => "",');
l.add('	"/admindb" => "",');
l.add('	"/confirm" => "",');
l.add('	"/create" => "",');
l.add('	"/edithtml" => "",');
l.add('	"/listinfo" => "",');
l.add('	"/options" => "",');
l.add('	"/private" => "",');
l.add('	"/rmlist" => "",');
l.add('	"/roster" => "",');
l.add('	"/subscribe" => ""');
l.add(')');
logs.WriteToFile(l.Text,'/etc/zarafa/lighttpd.conf');
l.free;
end;

procedure tzarafa_server.APACHE_START();
var
   apache:tapache_artica;
   apache_bin_path:string;
   start_command:string;
   pid:string;
   count:integer;
begin
    pid:=ZARAFA_WEB_PID_NUM();
    if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Apache daemon. already running PID '+pid);
      exit;
    end;
   apache:=tapache_artica.Create(SYS);
   apache_bin_path:=apache.BIN_PATH();
   start_command:=apache_bin_path+' -f /etc/zarafa/httpd.conf';

   if not FileExists(apache_bin_path) then begin
        logs.DebugLogs('Starting zarafa..............: Apache daemon. unable to stat apache web server');
        exit;
   end;

     if not FileExists('/etc/ssl/certs/apache/server.key') then apache.APACHE_ARTICA_SSL_KEY();
     if not FileExists('/etc/ssl/certs/apache/server.crt') then apache.APACHE_ARTICA_SSL_KEY();

     APACHE_CERTIFICATES();
     VERIFY_MAPI_SO_PATH();
     APACHE_CONFIG();
     WEB_ACCESS_CONFIG();
     logs.DeleteFile('/var/log/apache-zarafa/error.log');
     logs.Debuglogs(start_command);
     fpsystem(start_command);

 count:=0;
 while not SYS.PROCESS_EXIST(ZARAFA_WEB_PID_NUM()) do begin
              sleep(150);
              inc(count);
              if count>50 then begin
                 logs.DebugLogs('Starting zarafa..............: Apache daemon. (timeout!!!)');
                 logs.DebugLogs('Starting zarafa..............: Apache daemon. "'+start_command+'" failed');
                 if APACHE_FOUND_ERROR() then begin
                    sleep(500);
                    APACHE_START();
                    exit;
                 end;
                 break;
              end;
        end;

 pid:=ZARAFA_WEB_PID_NUM();
 if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting zarafa..............: Apache daemon with new PID '+pid);
 end;

end;
//##############################################################################
function tzarafa_server.APACHE_FOUND_ERROR():boolean;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:Integer;
begin
  result:=false;
  logs.DebugLogs('Starting zarafa..............: Apache daemon. try to investigate');
  if not FIleExists('/var/log/apache-zarafa/error.log') then begin
     logs.DebugLogs('Starting zarafa..............: Apache daemon. unable to stat /var/log/apache-zarafa/error.log');
     exit;
  end;

  l:=Tstringlist.Create;
  RegExpr:=TRegExpr.Create;
  l.LoadFromFile('/var/log/apache-zarafa/error.log');
  for i:=0 to l.COunt-1 do begin
       RegExpr.Expression:='RSA server certificate CommonName\s+\(CN\)\s+`(.+?)''\s+does NOT match server name';
       if RegExpr.Exec(l.Strings[i]) then begin
           logs.DebugLogs('Starting zarafa..............: Apache daemon. Change the Apache server name to '+RegExpr.Match[1]);
           SYS.set_INFO('ZarafaApacheServerName',RegExpr.Match[1]);
           RegExpr.free;
           result:=true;
           l.free;
           exit;
       end;
       logs.DebugLogs('Starting zarafa..............: '+l.Strings[i]);
  end;
           RegExpr.free;
           l.free;



end;
//##############################################################################
function tzarafa_server.ZARAFA_WEB_PID_NUM():string;
var
   pid_path:string;

begin
     pid_path:='/var/run/zarafa-web/httpd.pid';
     if FileExists(pid_path) then begin
        result:=SYS.GET_PID_FROM_PATH(pid_path);
        if not SYS.PROCESS_EXIST(result) then result:='';
     end;
end;
//##############################################################################
procedure tzarafa_server.APACHE_STOP();
var
   count:integer;
   pid:string;
   apache:tapache_artica;
begin
    apache:=tapache_artica.Create(SYS);
    if not FileExists(apache.BIN_PATH()) then begin
    writeln('Stopping Apache Daemon.......: Not installed');
    exit;
    end;
    pid:=ZARAFA_WEB_PID_NUM();
if  not SYS.PROCESS_EXIST(pid) then begin
    writeln('Stopping Apache Daemon.......: Already stopped');
    exit;
end;

    writeln('Stopping Apache Daemon.......: ' + pid + ' PID..');
    fpsystem('/bin/kill '+ pid);
    pid:=ZARAFA_WEB_PID_NUM();
    if FileExists(SYS.LOCATE_APACHECTL()) then begin
       logs.OutputCmd(SYS.LOCATE_APACHECTL() +' -f /etc/zarafa/httpd.conf -k stop');
    end else begin
       writeln('Stopping Apache Daemon.......: failed to stat apachectl');
    end;

  while SYS.PROCESS_EXIST(pid) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(pid)>0 then begin
               if SYS.PROCESS_EXIST(pid) then begin
                  writeln('Stopping Apache Daemon.......: kill pid '+ pid+' after timeout');
                  fpsystem('/bin/kill -9 ' + pid);
               end;
            end;
            break;
        end;
        pid:=ZARAFA_WEB_PID_NUM();
  end;

if  not SYS.PROCESS_EXIST(ZARAFA_WEB_PID_NUM()) then begin
    writeln('Stopping Apache Daemon.......: success');
    exit;
end;
    writeln('Stopping Apache Daemon.......: failed');
end;


//#############################################################################
procedure tzarafa_server.CHECK_CYRUS_CONFIG();
var
   cyrsconf:TstringList;
   i:integer;
   RegExpr:TRegExpr;
begin

if not FileExists('/etc/cyrus.conf') then exit;
cyrsconf:=Tstringlist.Create;
cyrsconf.LoadFromFile('/etc/cyrus.conf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='imap\s+cmd=.+?listen="imap';
for i:=0 to cyrsconf.Count-1 do begin
   if RegExpr.Exec(cyrsconf.Strings[i]) then begin
         logs.DebugLogs('Starting zarafa..............: Zarafa-gateway cyrus-imap is installed');
         logs.DebugLogs('Starting zarafa..............: Zarafa-gateway Change cyrus-imap configuration ports');
         fpsystem('/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus');
         break;
   end;
end;
cyrsconf.free;
RegExpr.free;
end;
//#############################################################################
procedure tzarafa_server.CERTIFICATES();
var
   CertificateIniFile:string;
   certini:Tinifile;
   tmpstr,cmd:string;
   smtpd_tls_key_file:string;
   smtpd_tls_cert_file:string;
   smtpd_tls_CAfile:string;
   openssl_path:string;
   POSFTIX_POSTCONF:string;
   l:TstringList;
   generate:boolean;
   i:integer;
   CertificateMaxDays:string;
   input_password,passfile,extensions:string;
   ldap:topenldap;

begin

  ldap:=topenldap.Create;
  if not FileExists(SERVER_BIN_PATH()) then begin
     logs.DebugLogs('Starting zarafa..............: Unable to stat zarafa-server path');
     exit;
  end;

  l:=TstringList.Create;
  l.Add('server.key');
  l.Add('ca.key');
  l.add('ca.csr');
  l.add('ca.crt');

  if SYS.COMMANDLINE_PARAMETERS('--zarafa-certificates') then begin
       for i:=0 to l.Count-1 do begin
           logs.DeleteFile('/etc/ssl/certs/zarafa/' +l.Strings[i]);
       end;
  end;


  forceDirectories('/etc/ssl/certs/zarafa');
    CertificateMaxDays:=SYS.GET_INFO('CertificateMaxDays');
    if length(CertificateMaxDays)=0 then CertificateMaxDays:='730';
    if length(SYS.OPENSSL_CERTIFCATE_HOSTS())>0 then extensions:=' -extensions HOSTS_ADDONS ';

  generate:=false;
  for i:=0 to l.Count-1 do begin
       if not FileExists('/etc/ssl/certs/zarafa/' +l.Strings[i]) then begin
          generate:=true;
          break;
       end else begin
          logs.DebugLogs('Starting zarafa..............: Zarafa-gateway /etc/ssl/certs/zarafa/' +l.Strings[i] + ' OK');
       end;
  end;

  if generate then begin
     SYS.OPENSSL_CERTIFCATE_CONFIG();
     CertificateIniFile:=SYS.OPENSSL_CONFIGURATION_PATH();
     if not FileExists(CertificateIniFile) then begin
        logs.Syslogs('tzarafa_server.GENERATE_CERTIFICATE():: FATAL ERROR, unable to find any ssl configuration path');
        exit;
     end;

     if not fileExists(SYS.LOCATE_OPENSSL_TOOL_PATH()) then begin
        logs.Syslogs('tzarafa_server.GENERATE_CERTIFICATE():: FATAL ERROR, unable to stat openssl');
        exit;
     end;

     tmpstr:=LOGS.FILE_TEMP();
     fpsystem('/bin/cp '+CertificateIniFile+' '+tmpstr);
     certini:=TiniFile.Create(tmpstr);
     input_password:=certini.ReadString('req','input_password',ldap.ldap_settings.password);
     if length(input_password)=0 then input_password:=ldap.ldap_settings.password;

     logs.Debuglogs('Settings certificate file...');
     certini.WriteString('req_distinguished_name','organizationalUnitName_default','Mailserver');
     certini.UpdateFile;
     logs.Debuglogs('Generate server certificate');
     logs.Debuglogs('extensions:"'+extensions+'"');


     cmd:=SYS.LOCATE_OPENSSL_TOOL_PATH()+' genrsa -out /etc/ssl/certs/zarafa/server.key 1024';
     writeln(cmd);
     fpsystem(cmd);

     cmd:=SYS.LOCATE_OPENSSL_TOOL_PATH()+' req -new -key /etc/ssl/certs/zarafa/server.key -batch -config '+tmpstr+extensions+' -out /etc/ssl/certs/zarafa/server.csr';
     writeln(cmd);
     fpsystem(cmd);
     cmd:=SYS.LOCATE_OPENSSL_TOOL_PATH()+' genrsa -out /etc/ssl/certs/zarafa/ca.key 1024 -batch -config '+tmpstr+extensions;

     writeln(cmd);
     fpsystem(cmd);
     cmd:=SYS.LOCATE_OPENSSL_TOOL_PATH()+' req -new -x509 -days '+CertificateMaxDays+' -key /etc/ssl/certs/zarafa/ca.key -batch -config '+tmpstr+extensions+' -out /etc/ssl/certs/zarafa/ca.csr';

     writeln(cmd);
     fpsystem(cmd);
     cmd:=SYS.LOCATE_OPENSSL_TOOL_PATH()+' x509 -extfile '+tmpstr+extensions+' -x509toreq -days '+CertificateMaxDays+' -in /etc/ssl/certs/zarafa/ca.csr -signkey /etc/ssl/certs/zarafa/ca.key -out /etc/ssl/certs/zarafa/ca.req';

     writeln(cmd);
     fpsystem(cmd);
     cmd:=SYS.LOCATE_OPENSSL_TOOL_PATH()+' x509 -extfile '+tmpstr+extensions+' -req -days '+CertificateMaxDays+' -in /etc/ssl/certs/zarafa/ca.req -signkey /etc/ssl/certs/zarafa/ca.key -out /etc/ssl/certs/zarafa/ca.crt';
     //cmd:=SYS.LOCATE_OPENSSL_TOOL_PATH()+' x509 -req -extfile '+tmpstr+extensions+' -req -days '+CertificateMaxDays+' -CA /etc/ssl/certs/zarafa/server.csr -CAkey /etc/ssl/certs/zarafa/server.key -CAcreateserial -CAserial /etc/ssl/certs/zarafa/ca.srl -in /etc/ssl/certs/zarafa/ca.csr -out /etc/ssl/certs/zarafa/ca.crt';
     writeln(cmd);
     fpsystem(cmd);
  end;
end;
//#############################################################################
procedure tzarafa_server.APACHE_CERTIFICATES();
var
   CertificateIniFile:string;
   certini:Tinifile;
   tmpstr,cmd:string;
   smtpd_tls_key_file:string;
   smtpd_tls_cert_file:string;
   smtpd_tls_CAfile:string;
   POSFTIX_POSTCONF:string;
   l:TstringList;
   generate:boolean;
   i:integer;
   CertificateMaxDays:string;
   input_password,passfile,extensions:string;
   ldap:topenldap;
   CertificatePassword:string;
   openssl_path:string;

   private_ca_key:string;
   private_apache_key:string;
   certificate_request_path:string;
   certificate_path:string;
   x509_ca_crt:string;



begin

  ldap:=topenldap.Create;
  if not FileExists(SERVER_BIN_PATH()) then begin
     logs.DebugLogs('Starting zarafa..............: Unable to stat zarafa-server path');
     exit;
  end;

  l:=TstringList.Create;
  l.Add('internal-ca.key');
  l.Add('internal-ca.crt');
  l.add('apache-ca.key');
  l.add('apache.csr');
  l.add('apache.crt');
  l.add('apache-ca.key.nopass.key');
  l.add('apache.crt.nopass.cert');

  if SYS.COMMANDLINE_PARAMETERS('--zarafa-apache-certificates') then begin
       for i:=0 to l.Count-1 do begin
           logs.DebugLogs('Starting zarafa..............: Apache removing file /etc/ssl/certs/zarafa/' +l.Strings[i]);
           logs.DeleteFile('/etc/ssl/certs/zarafa/' +l.Strings[i]);
       end;
  end;

  generate:=false;
  for i:=0 to l.Count-1 do begin
       if not FileExists('/etc/ssl/certs/zarafa/' +l.Strings[i]) then begin
          generate:=true;
          break;
       end else begin
          logs.DebugLogs('Starting zarafa..............: Apache /etc/ssl/certs/zarafa/' +l.Strings[i] + ' OK');
       end;
  end;
  if not generate then exit;

     SYS.OPENSSL_CERTIFCATE_CONFIG();
     CertificateIniFile:=SYS.OPENSSL_CONFIGURATION_PATH();
     if not FileExists(CertificateIniFile) then begin
        logs.Syslogs('tzarafa_server.APACHE_CERTIFICATES():: FATAL ERROR, unable to find any ssl configuration path');
        exit;
     end;


     private_ca_key:='/etc/ssl/certs/zarafa/internal-ca.key';
     x509_ca_crt:='/etc/ssl/certs/zarafa/internal-ca.crt';
     private_apache_key:='/etc/ssl/certs/zarafa/apache-ca.key';
     certificate_request_path:='/etc/ssl/certs/zarafa/apache.csr';
     certificate_path:='/etc/ssl/certs/zarafa/apache.crt';

    forceDirectories('/etc/ssl/certs/zarafa');
    CertificateMaxDays:=SYS.GET_INFO('CertificateMaxDays');
    openssl_path:=SYS.LOCATE_OPENSSL_TOOL_PATH();
    if length(CertificateMaxDays)=0 then CertificateMaxDays:='730';
    if length(SYS.OPENSSL_CERTIFCATE_HOSTS())>0 then extensions:=' -extensions HOSTS_ADDONS ';
    logs.Debuglogs('Generate server certificate');
    logs.Debuglogs('extensions:"'+extensions+'"');

     tmpstr:=LOGS.FILE_TEMP();
     fpsystem('/bin/cp '+CertificateIniFile+' '+tmpstr);


    CertificatePassword:=ldap.ldap_settings.password;
    logs.DebugLogs('Starting zarafa..............: Apache generate private CA key and private CA X.509 certificate');

    writeln('STEP (1) ----------------------------');
    cmd:=openssl_path+' genrsa -des3 -passout pass:'+CertificatePassword+' -out '+private_ca_key+' 2048';
    writeln(cmd);
    fpsystem(cmd);


    writeln('STEP (2) ----------------------------');
    cmd:=openssl_path+' req -new -x509 -config '+CertificateIniFile+extensions+' -days '+CertificateMaxDays+' -passin pass:'+CertificatePassword+' -key '+private_ca_key+' -out '+x509_ca_crt;
    writeln(cmd);
    fpsystem(cmd);
    logs.DebugLogs('Starting zarafa..............: Apache generate key and a certificate request');

    writeln('STEP (3) ----------------------------');
    cmd:=openssl_path+' genrsa -des3 -passout pass:'+CertificatePassword+' -out '+private_apache_key+' 2048';
    writeln(cmd);
    fpsystem(cmd);

    writeln('STEP (4) ----------------------------');
    cmd:=openssl_path+' req -new -key '+private_apache_key+' -out '+certificate_request_path+' -passin pass:'+CertificatePassword+' -config '+CertificateIniFile+extensions;
    writeln(cmd);
    fpsystem(cmd);

    writeln('STEP (5) ----------------------------');
    cmd:=openssl_path+' x509 -req -in '+certificate_request_path+' -out '+certificate_path+' -passin pass:'+CertificatePassword+' -sha1 -CA '+x509_ca_crt+' -CAkey '+private_ca_key+' -CAcreateserial -days '+CertificateMaxDays;
    writeln(cmd);
    fpsystem(cmd);

    writeln('STEP (6) ----------------------------');
    cmd:=openssl_path+' rsa -in '+private_apache_key+' -passin pass:'+CertificatePassword+' -out '+private_apache_key+'.nopass.key';
    writeln(cmd);
    fpsystem(cmd);

    writeln('STEP (7) ----------------------------');
    cmd:=openssl_path+' x509 -in '+certificate_request_path+' -out '+certificate_path+'.nopass.cert -req -signkey '+private_apache_key+'.nopass.key -days '+CertificateMaxDays;
    writeln(cmd);
    fpsystem(cmd);



    fpsystem('/bin/chmod 0400 /etc/ssl/certs/zarafa/*.key');




end;

end.


