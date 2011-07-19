unit oma;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',openldap,artica_ip,lighttpd,zsystem;

  type
  toma=class


private
     LOGS:Tlogs;
     sys:tsystem;
     D:boolean;
     artica_path:string;
     inif:TiniFile;
     lighttpd:Tlighttpd;
     procedure OMA_CONFIGURE();
     procedure Create_init_d_debian();
     procedure Create_init_d_redhat();
     function  OMA_PID():string;


public
    procedure   Free;
    constructor Create;
    function    OMA_bin_path():string;
    function    init_d():string;
    function    conf_path():string;
    procedure   START();

END;

implementation

constructor toma.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       sys:=tsystem.Create;
       lighttpd:=tlighttpd.Create;
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure toma.free();
begin
    FreeAndNil(lighttpd);
    FreeAndNil(logs);
    FreeAndNil(sys);
end;
//##############################################################################
function toma.OMA_bin_path():string;
begin
  if FileExists('/usr/local/oma/bin/oma_d.php') then exit('/usr/local/oma/bin/oma_d.php');
end;
//##############################################################################
function toma.OMA_PID():string;
begin
  if FileExists('/var/run/omad.pid') then result:=SYS.GET_PID_FROM_PATH('/var/run/omad.pid');
end;
//#############################################################################
function toma.init_d():string;
begin
  if FileExists('/etc/init.d/omad') then exit('/etc/init.d/omad');
end;
//#############################################################################
function toma.conf_path():string;
begin
  if FileExists('/usr/local/oma/etc/oma.conf') then exit('/usr/local/oma/etc/oma.conf');
end;
//#############################################################################
procedure toma.START();
var
   pid:string;
   FileTemp:string;
   count:integer;
   log_dir:string;
   conf:TiniFile;
begin
count:=0;
if not FileExists(OMA_bin_path()) then exit;
pid:=OMA_PID();
if SYS.PROCESS_EXIST(pid) then begin
   logs.Debuglogs('Starting......: oma Already running PID '+pid);
   exit;
end;

   if not FileExists(conf_path()) then begin
    logs.DebugLogs('Starting......: oma (failed) unable to stat conf file');
    exit;
   end;
   conf:=TiniFile.Create(conf_path());
   log_dir:=conf.ReadString('DIRECTORIES','log_dir','');
   if length(log_dir)>0 then begin
         ForceDirectories(log_dir);
         logs.OutputCmd('/bin/chmod -R 777 ' + log_dir);
   end;
      
   
   

   Create_init_d_redhat();
   Create_init_d_debian();
   OMA_CONFIGURE();
   FileTemp:=artica_path+'/ressources/logs/oma.start.daemon';


if FileExists(init_d()) then begin
   fpsystem(init_d() + ' start >' + artica_path + ' ' + FileTemp+' 2>&1');
    while not SYS.PROCESS_EXIST(OMA_PID()) do begin
        sleep(100);
        inc(count);
        if count>30 then begin
           logs.DebugLogs('Starting......: oma (failed)');
           logs.DebugLogs('Starting......: '+ logs.ReadFromFile(FileTemp));
           exit;
        end;
    end;
end;

logs.DebugLogs('Starting......: oma with new PID ' + OMA_PID());

end;
//#############################################################################
procedure toma.OMA_CONFIGURE();
var
  oma_conf:TStringList;
  RegExpr:TRegExpr;
  i:integer;
  xldap:topenldap;
  ip:tip;
  local_ip,www_url_oma,listen_port:string;
begin
  if not FileExists(OMA_bin_path()) then exit;
  if not FileExists('/usr/local/oma/etc/oma.conf') then exit;
  oma_conf:=TStringList.Create;
  oma_conf.LoadFromFile('/usr/local/oma/etc/oma.conf');
  listen_port:=lighttpd.LIGHTTPD_LISTEN_PORT();
  ip:=tip.Create;

  www_url_oma:=SYS.GET_INFO('www_url_oma');
  if length(www_url_oma)=0 then begin
      for i:=0 to 3 do begin
          if length(ip.LOCAL_IP('eth'+IntTostr(i)))>0 then begin
             local_ip:=ip.LOCAL_IP('eth'+IntTostr(i));
             break;
          end;
      end;

    www_url_oma:='https://' + local_ip + ':' + listen_port+'/oma';

  end;


  RegExpr:=TRegExpr.Create;
  For i:=0 to oma_conf.Count-1 do begin
       RegExpr.Expression:='^auth_plugin';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='auth_plugin = "ldap"';
       RegExpr.Expression:='^list_auth_plugin_web';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='list_auth_plugin_web = "password_file,ldap"';
       RegExpr.Expression:='^www_url';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='www_url = "'+www_url_oma+'"';
       RegExpr.Expression:='^sgbd_name';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='sgbd_name = "Mysql"';
       RegExpr.Expression:='dbport[\s+=]';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='dbport = "'+SYS.MYSQL_INFOS('port')+'"';
       RegExpr.Expression:='^dbhost';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='dbhost = "'+SYS.MYSQL_INFOS('mysql_server')+'"';
       RegExpr.Expression:='^dbuser';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='dbuser = "'+SYS.MYSQL_INFOS('database_admin')+'"';
       RegExpr.Expression:='^dbpass';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='dbpass = "'+SYS.MYSQL_INFOS('database_password')+'"';
       RegExpr.Expression:='^server_host';
       if RegExpr.Exec(oma_conf.Strings[i]) then oma_conf.Strings[i]:='server_host = "127.0.0.1"';
  end;

  oma_conf.SaveToFile('/usr/local/oma/etc/oma.conf');
  oma_conf.Clear;
  xldap:=topenldap.Create;
  oma_conf.Add('ldap_host = "ldap://'+xldap.ldap_settings.servername+'"');
  oma_conf.Add('ldap_port = "'+xldap.ldap_settings.Port+'"');
  oma_conf.Add('ldap_rdn = "cn='+xldap.ldap_settings.admin+','+xldap.ldap_settings.suffix+'"');
  oma_conf.Add('ldap_filter = "(&(uid=%username)(objectClass=userAccount))"');
  oma_conf.Add('ldap_base_dn = "'+xldap.ldap_settings.suffix+'"');
  oma_conf.Add('ldap_base_dn_admin = "'+xldap.ldap_settings.suffix+'"');
  oma_conf.Add('ldap_filter_admin = "(&(cn=%username)(objectClass=simpleSecurityObject))"');
  oma_conf.Add('bind_manager = "1"');
  oma_conf.Add('manager_password = "'+xldap.ldap_settings.password+'"');
  oma_conf.Add('password_crypt = "clear"');
  oma_conf.Add('password = "'+xldap.ldap_settings.password+'"');
  oma_conf.Add('username = "uid"');
  oma_conf.Add('email = "mail"');
  oma_conf.Add('admin = "member"');

  if FileExists('/usr/local/oma/lib/plugins/auth/ldap/ldap.conf') then oma_conf.SaveToFile('/usr/local/oma/lib/plugins/auth/ldap/ldap.conf');
  if FileExists('/usr/local/oma/lib/plugin/auth/ldap/ldap.conf') then oma_conf.SaveToFile('/usr/local/oma/lib/plugin/auth/ldap/ldap.conf');



  FreeAndnil(oma_conf);
  FreeAndnil(RegExpr);


end;
//#########################################################################################

procedure toma.Create_init_d_debian();
var l:TstringList;
begin

if not FileExists('/etc/debian_version') then exit;
if FIleExists('/etc/init.d/omad') then exit;
l:=TstringList.Create;

l.Add('#!/bin/sh');
l.Add('# Start/stop oma daemon.');
l.Add('#');
l.Add('');
l.Add('. /lib/lsb/init-functions');
l.Add('');
l.Add('BASE_OMA_DIR=/usr/local/oma');
l.Add('');
l.Add('test -f $BASE_OMA_DIR/bin/omad.php || exit 0');
l.Add('test -f $BASE_OMA_DIR/bin/oma_d.php || exit 0');
l.Add('');
l.Add('');
l.Add('');
l.Add('');
l.Add('case "$1" in');
l.Add('start)');
l.Add('	log_daemon_msg "Starting omad server" "omad"');
l.Add('');
l.Add('         start-stop-daemon -c oma -b --start -m  /var/run/omad.pid --pidfile /var/run/omad.pid --name omad.php --startas $BASE_OMA_DIR/bin/omad.php');
l.Add('         sleep 1');
l.Add('	 log_end_msg $?');
l.Add('	 log_daemon_msg "Starting omad subproccess" "oma_d"');
l.Add('         start-stop-daemon -c oma -b --start -m  /var/run/oma_d.pid --pidfile /var/run/oma_d.pid --name oma_d.php --startas $BASE_OMA_DIR/bin/oma_d.php');
l.Add('	 log_end_msg $?');
l.Add('        ;;');
l.Add('stop)');
l.Add('        log_daemon_msg "Stopping omad server" "omad"');
l.Add('	start-stop-daemon --stop  --pidfile /var/run/omad.pid --name omad.php');
l.Add('	log_end_msg $?');
l.Add('        rm /var/run/omad.pid');
l.Add('	log_daemon_msg "Stopping omad subproccess" "oma_d"');
l.Add('        start-stop-daemon --stop  --pidfile /var/run/oma_d.pid --name oma_d.php');
l.Add('        log_end_msg $?');
l.Add('	rm /var/run/oma_d.pid');
l.Add('        ;;');
l.Add('restart)');
l.Add('	/etc/init.d/oma stop');
l.Add('	/etc/init.d/oma start');
l.Add('	;;');
l.Add('*)');
l.Add('        echo "usage:oma start|stop|restart"');
l.Add('esac');
l.Add('exit 0');
l.SaveToFile('/etc/init.d/omad');
logs.OutputCmd('/bin/chmod 777 /etc/init.d/omad');

 if FileExists('/usr/sbin/update-rc.d') then begin
    logs.OutputCmd('/usr/sbin/update-rc.d -f omad defaults');
 end;
   FreeAndnil(l);
end;

//#########################################################################################
procedure toma.Create_init_d_redhat();
var
l:Tstringlist;
begin

if not FileExists('/etc/redhat-release') then exit;
if FIleExists('/etc/init.d/omad') then exit;
l:=TstringList.Create;

l.Add('#!/bin/sh');
l.Add('# Start/stop oma daemon.');
l.Add('#');
l.Add('');
l.Add('. /lib/lsb/init-functions');
l.Add('');
l.Add('BASE_OMA_DIR=/usr/local/oma');
l.Add('');
l.Add('test -f $BASE_OMA_DIR/bin/omad.php || exit 0');
l.Add('test -f $BASE_OMA_DIR/bin/oma_d.php || exit 0');
l.Add('');
l.Add('');
l.Add('');
l.Add('');
l.Add('case "$1" in');
l.Add('start)');
l.Add('	log_daemon_msg "Starting omad server" "omad"');
l.Add('');
l.Add('         start-stop-daemon -c oma -b --start -m  /var/run/omad.pid --pidfile /var/run/omad.pid --name omad.php --startas $BASE_OMA_DIR/bin/omad.php');
l.Add('         sleep 1');
l.Add('	 log_end_msg $?');
l.Add('	 log_daemon_msg "Starting omad subproccess" "oma_d"');
l.Add('         start-stop-daemon -c oma -b --start -m  /var/run/oma_d.pid --pidfile /var/run/oma_d.pid --name oma_d.php --startas $BASE_OMA_DIR/bin/oma_d.php');
l.Add('	 log_end_msg $?');
l.Add('        ;;');
l.Add('stop)');
l.Add('        log_daemon_msg "Stopping omad server" "omad"');
l.Add('	start-stop-daemon --stop  --pidfile /var/run/omad.pid --name omad.php');
l.Add('	log_end_msg $?');
l.Add('        rm /var/run/omad.pid');
l.Add('	log_daemon_msg "Stopping omad subproccess" "oma_d"');
l.Add('        start-stop-daemon --stop  --pidfile /var/run/oma_d.pid --name oma_d.php');
l.Add('        log_end_msg $?');
l.Add('	rm /var/run/oma_d.pid');
l.Add('        ;;');
l.Add('restart)');
l.Add('	/etc/init.d/oma stop');
l.Add('	/etc/init.d/oma start');
l.Add('	;;');
l.Add('*)');
l.Add('        echo "usage:oma start|stop|restart"');
l.Add('esac');
l.Add('exit 0');
l.SaveToFile('/etc/init.d/omad');
logs.OutputCmd('/bin/chmod 777 /etc/init.d/omad');

  if FileExists('/sbin/chkconfig') then begin
     logs.OutputCmd('/sbin/chkconfig --add omad');
     logs.OutputCmd('/sbin/chkconfig --level 2345 omad on');
  end;
  
  FreeAndnil(l);

end;




end.

