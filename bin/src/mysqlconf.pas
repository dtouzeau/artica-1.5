unit mysqlconf;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,strutils,IniFiles,oldlinux,BaseUnix,dos,confiles,global_conf,RegExpr in 'RegExpr.pas',zsystem;

  type
  Tmysqlconf=class


private
       GLOBAL_INI:myconf;
       function CheckCourierConfig():string;
      procedure Mysql_postfix();
       zSystem:Tsystem;

public
      procedure MysqlSettings;
      function install_courier_config():boolean;
      constructor Create();
      procedure Free;

END;

implementation

constructor Tmysqlconf.Create();
begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=myconf.Create();
       zSystem:=Tsystem.Create();
end;
PROCEDURE Tmysqlconf.Free();
begin
   GLOBAL_INI.Free;
end;

//#####################################################################################################################
PROCEDURE Tmysqlconf.MysqlSettings();
var
  webpath:string;yesno,user,password,base,server,usemysql,mysql_socket_path:string;

  MYSQL_INSTALLED:boolean;
begin

Shell('/usr/bin/clear');
forcedirectories('/etc/artica-postfix');

   server:=GLOBAL_INI.get_MYSQL_SERVER();
   user:=GLOBAL_INI.get_MYSQL_USERNAME();
   password:=GLOBAL_INI.get_MYSQL_PASSWORD();
   base:=GLOBAL_INI.get_MYSQL_DBNAME();

       writeln('Give the server name to connect/create to the server [' + GLOBAL_INI.get_MYSQL_SERVER() + ']:');
       readln(server);
       if length(server)=0 then server:=GLOBAL_INI.get_MYSQL_SERVER();
       GLOBAL_INI.set_MYSQL_SERVER(server);

       writeln('Give the username to connect to the server [' + user + ']');
       readln(user);
       if length(user)=0 then user:=GLOBAL_INI.get_MYSQL_USERNAME();
       GLOBAL_INI.set_MYSQL_USERNAME(user);

       writeln('Give the password to connect to the server [' + password + ']:');
       readln(password);
        if length(password)=0 then user:=GLOBAL_INI.get_MYSQL_PASSWORD();
       GLOBAL_INI.set_MYSQL_PASSWORD(password);

       writeln('Give the database name to connect/create to the server:[' + base + ']');
       readln(base);
        if length(base)=0 then base:=GLOBAL_INI.get_MYSQL_DBNAME();
       GLOBAL_INI.set_MYSQL_DBNAME(base);

    Mysql_postfix();
 end;
//#####################################################################################################################
procedure Tmysqlconf.Mysql_postfix();
   var
   username,password,base,server,dummy1,mailbox_path,install_source_path,mysql_init,mysql_socket_path:string;
   createtables:string;
   myFile:TextFile;

begin

    writeln('creating config file for PostFix mysql_virtual_alias_maps...');
   server:=GLOBAL_INI.get_MYSQL_SERVER();
   username:=GLOBAL_INI.get_MYSQL_USERNAME();
   password:=GLOBAL_INI.get_MYSQL_PASSWORD();
   base:=GLOBAL_INI.get_MYSQL_DBNAME();
   mysql_init:=GLOBAL_INI.get_MYSQL_INIT();
   
   
   createtables:='yes';
   install_source_path:=GLOBAL_INI.get_INSTALL_PATH();
   dummy1:='' ;
     writeln('Do you want to create database and tables first ?:(yes/no) [yes]');
     readln(createtables);
     if length(createtables)=0 then createtables:='yes';
     if createtables='yes' then begin
        Shell(mysql_init + ' start');
        Shell(install_source_path + '/bin/install-sql');
     end;



    AssignFile(myFile, '/etc/postfix/mysql_virtual_alias_maps.cf');
    ReWrite(myFile);
    WriteLn(myFile,'user =' + username);
    WriteLn(myFile,'password =' +  password);
    WriteLn(myFile,'hosts = ' +server);
    WriteLn(myFile,'dbname = ' +base);
    WriteLn(myFile,'query = SELECT goto FROM alias WHERE address=''%s'' AND active = 1');
    CloseFile(myFile);


    writeln('creating config file  for PostFix mysql_virtual_domains_maps...');
    AssignFile(myFile, '/etc/postfix/mysql_virtual_domains_maps.cf');
    ReWrite(myFile);
    WriteLn(myFile,'user =' + username);
    WriteLn(myFile,'password =' +  password);
    WriteLn(myFile,'hosts = ' +server);
    WriteLn(myFile,'dbname = ' +base);
    Writeln(myFile,'query = SELECT domain FROM domain WHERE domain=''%s''');
    CloseFile(myFile);

    writeln('creating config file  for PostFix mysql_virtual_mailbox_maps...');
    AssignFile(myFile, '/etc/postfix/mysql_virtual_mailbox_maps.cf');
    ReWrite(myFile);
    WriteLn(myFile,'user =' + username);
    WriteLn(myFile,'password =' +  password);
    WriteLn(myFile,'hosts = ' +server);
    WriteLn(myFile,'dbname = ' +base);
    Writeln(myFile,'query = SELECT maildir FROM mailbox WHERE username=''%s'' AND active = 1');
    CloseFile(myFile);

    writeln('creating config file  for PostFix mysql_virtual_mailbox_limit_maps...');
    AssignFile(myFile, '/etc/postfix/mysql_virtual_mailbox_limit_maps.cf');
    ReWrite(myFile);
    WriteLn(myFile,'user =' + username);
    WriteLn(myFile,'password =' +  password);
    WriteLn(myFile,'hosts = ' +server);
    WriteLn(myFile,'dbname = ' +base);
    Writeln(myFile,'query = SELECT quota FROM mailbox WHERE username=''%s''');
    CloseFile(myFile);

    writeln('creating config file  for PostFix mysql_relay_domains_maps...');
    AssignFile(myFile, '/etc/postfix/mysql_relay_domains_maps.cf');
    ReWrite(myFile);
    WriteLn(myFile,'user =' + username);
    WriteLn(myFile,'password =' +  password);
    WriteLn(myFile,'hosts = ' +server);
    WriteLn(myFile,'dbname = ' +base);
    Writeln(myFile,'query = SELECT domain FROM domain WHERE domain=''%s'' and backupmx = ''1''');
    CloseFile(myFile);

    writeln('creating config file  for PostFix mysql_transport_maps...');
    AssignFile(myFile, '/etc/postfix/mysql_transport_maps.cf');
    ReWrite(myFile);
    WriteLn(myFile,'user =' + username);
    WriteLn(myFile,'password =' +  password);
    WriteLn(myFile,'hosts = ' +server);
    WriteLn(myFile,'dbname = ' +base);
    Writeln(myFile,'query = SELECT goto FROM transport_maps WHERE domain=''%s''');
    CloseFile(myFile);

    mysql_socket_path:=GLOBAL_INI.get_MY_CNF();
    if length(mysql_socket_path)>0 then begin
        writeln('apply security to allow compatibility PostFix/Mysql');
        Shell('/bin/chmod 640 /etc/postfix/*.cf');
        Shell('/bin/mkdir -p /var/spool/postfix/var/run/mysqld');
        Shell('/bin/chown mysql /var/spool/postfix/var/run/mysqld');
        if not FileExists('/var/spool/postfix/var/run/mysqld/mysqld.sock') then begin
               Shell('/bin/ln -s ' + mysql_socket_path + ' /var/spool/postfix/var/run/mysqld/mysqld.sock');
        end;
    end;




    writeln('Change postfix settings for accessing mysql database');
    Shell('/usr/sbin/postconf -e virtual_alias_maps=mysql:/etc/postfix/mysql_virtual_alias_maps.cf');
    Shell('/usr/sbin/postconf -e transport_maps=mysql:/etc/postfix/mysql_transport_maps.cf');
    Shell('/usr/sbin/postconf -e virtual_transport=virtual');
    Shell('/usr/sbin/postconf -e relay_domains=mysql:/etc/postfix/mysql_relay_domains_maps.cf');
    Shell('/usr/sbin/postconf -e config_directory=/etc/postfix');
    writeln('Restarting postfix server');
    Shell('/etc/init.d/postfix restart >/tmp/postfix_restart.txt');
    
    if GLOBAL_INI.get_MANAGE_MAILBOXES()='yes' then CheckCourierConfig();
    
    
    
end;
//#####################################################################################################################
function Tmysqlconf.CheckCourierConfig():string;
var
  authdaemonrc_path:string;
  enable_mailboxes:string;
  enablemb:boolean;
  mailbox_path,dummy1:string;
  use_mysql:boolean;
  group_id:string;
  user_id:string;
begin

    mailbox_path:=GLOBAL_INI.get_COURIER_MAILBOX_PATH();
    writeln('Wich folder your want to store IMAP/POP3 mailboxes on this server ?: [' + mailbox_path + ']');
    readln(mailbox_path);
    if length(mailbox_path)=0 then mailbox_path:=GLOBAL_INI.get_COURIER_MAILBOX_PATH();

    GLOBAL_INI.set_COURIER_MAILBOX_PATH(mailbox_path);

    writeln('Installing your mailbox path..');


    if zsystem.IsGroupExists('vmail')=false then begin
         writeln('add default "vmail" group...');
          Shell('/usr/sbin/groupadd -g 5000 vmail');
          group_id:='5000';
    end
          else begin
             group_id:=zsystem.SystemGroupID('vmail');
             writeln('default "vmail" group already set to id ' + group_id);
    end;


    if zsystem.IsUserExists('vmail')=false then begin
      writeln('add default "vmail" user...');
      Shell('/usr/sbin/useradd -g vmail -u 5000 vmail -d ' + mailbox_path + ' -m');
      user_id:='5000';
      end
         else begin
              user_id:=zsystem.SystemUserID('vmail');
              writeln('default "vmail" user alrady set to id ' + user_id);
    end;

    if FileExists(mailbox_path) then begin
        if zsystem.DirectoryGroupOwner(mailbox_path)<>'vmail' then begin
                writeln('Be carreful !!! installer will change security access for ' + mailbox_path +' user vmail, group vmail');
                Shell('/bin/chown vmail:vmail ' + mailbox_path);
                writeln('Security modified');
        end;
    end
       else begin
       writeln('mailbox_path already exists...');
    end;

    if not FileExists(mailbox_path) then begin
        forceDirectories(mailbox_path);
        writeln('Checking security for ' + mailbox_path +' user vmail, group vmail');
        Shell('/bin/chown vmail:vmail ' + mailbox_path);
    end;

    writeln('saving gid/uid numbers for artica daemon ' + group_id + ':' + user_id);
    GLOBAL_INI.set_COURIER_MAILBOX_PATH_GID(group_id);
    GLOBAL_INI.set_COURIER_MAILBOX_PATH_UID(user_id);

    writeln('Adding settings in Postfix.');
    Shell('/usr/sbin/postconf -e virtual_gid_maps=static:' + group_id);
    Shell('/usr/sbin/postconf -e virtual_mailbox_base=' + mailbox_path);
    Shell('/usr/sbin/postconf -e virtual_mailbox_limit=51200000');
    Shell('/usr/sbin/postconf -e virtual_minimum_uid='+ user_id);
    Shell('/usr/sbin/postconf -e virtual_uid_maps=static:'+ user_id);
    Shell('/usr/sbin/postconf -e virtual_create_maildirsize=yes');
    Shell('/usr/sbin/postconf -e virtual_mailbox_limit_override=yes');
    Shell('/usr/sbin/postconf -e virtual_mailbox_extended=yes');
    Shell('/usr/sbin/postconf -e virtual_maildir_limit_message=Mailbox_is_full');
    Shell('/usr/sbin/postconf -e virtual_overquota_bounce=yes');

   if FileExists('/etc/authlib/authdaemonrc') then authdaemonrc_path:='/etc/authlib/authdaemonrc';
     if FileExists('/etc/courier/authdaemonrc') then authdaemonrc_path:='/etc/courier/authdaemonrc';
     if length(authdaemonrc_path)=0 then begin
        writeln('Unable to locate authdaemonrc file !!!');
        exit;
     end;
     GLOBAL_INI.set_COURIER_AUTHDAEMON_PATH(authdaemonrc_path);
      Shell('/usr/sbin/postconf -e virtual_mailbox_maps=mysql:/etc/postfix/mysql_virtual_mailbox_maps.cf');
      Shell('/usr/sbin/postconf -e virtual_mailbox_limit_maps=mysql:/etc/postfix/mysql_virtual_mailbox_limit_maps.cf');
      install_courier_config();
end;
//#####################################################################################################################
function Tmysqlconf.install_courier_config():boolean;
var
   myFile:TextFile;
   NewIni:TConfFiles;
   authdaemonrc_path,method:string;

begin
 authdaemonrc_path:=ExtractFilePath(GLOBAL_INI.get_COURIER_AUTHDAEMON_PATH());
writeln('Settings auth method...');
 NewIni:=TConfFiles.Create(GLOBAL_INI.get_COURIER_AUTHDAEMON_PATH());
 method:=NewIni.GetValue('authmodulelist');
 
 writeln('Method = ' + method);
 
   if method<>'authmysql' then begin
      GLOBAL_INI.set_COURIER_AUTHDAEMON_METHOD('authmysql');
      NewIni.EditValue('authmodulelist','authmysql');
  end;


 
 AssignFile(myFile,authdaemonrc_path + '/authmysqlrc');
   ReWrite(myFile);
   WriteLn(myFile,'MYSQL_SERVER            ' + GLOBAL_INI.get_MYSQL_SERVER() );
   WriteLn(myFile,'MYSQL_USERNAME          ' + GLOBAL_INI.get_MYSQL_USERNAME());
   WriteLn(myFile,'MYSQL_PASSWORD          ' + GLOBAL_INI.get_MYSQL_PASSWORD());
   WriteLn(myFile,'#MYSQL_SOCKET           /var/lib/mysql/mysql.sock');
   WriteLn(myFile,'MYSQL_PORT              0');
   WriteLn(myFile,'MYSQL_OPT               0');
   WriteLn(myFile,'MYSQL_DATABASE          ' + GLOBAL_INI.get_MYSQL_DBNAME());
   WriteLn(myFile,'MYSQL_USER_TABLE        mailbox');
   WriteLn(myFile,'MYSQL_CLEAR_PWFIELD     password');
   WriteLn(myFile,'#DEFAULT_DOMAIN         domain.tld');
   WriteLn(myFile,'MYSQL_UID_FIELD         ' + GLOBAL_INI.get_COURIER_MAILBOX_PATH_UID());
   WriteLn(myFile,'MYSQL_GID_FIELD         ' + GLOBAL_INI.get_COURIER_MAILBOX_PATH_GID());
   WriteLn(myFile,'MYSQL_LOGIN_FIELD       username');
   WriteLn(myFile,'MYSQL_HOME_FIELD        "' + GLOBAL_INI.get_COURIER_MAILBOX_PATH() + '"');
   WriteLn(myFile,'MYSQL_NAME_FIELD        name');
   WriteLn(myFile,'MYSQL_MAILDIR_FIELD     maildir');
   WriteLn(myFile,'MYSQL_QUOTA_FIELD       quota');
   WriteLn(myFile,'#MYSQL_WHERE_CLAUSE     server=''exemple.domain.tld''');
   CloseFile(myFile);
   writeln('settings courier mysql done ');
   if FileExists('/etc/init.d/courier-authdaemon') then shell('/etc/init.d/courier-authdaemon restart');

end;


end.



