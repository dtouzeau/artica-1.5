unit autofs;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr,zsystem,openldap;



  type
  tautofs=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     zldap:Topenldap;
     bin_path_memory:string;
     EnableAutoFSDebug:integer;
     function mount_count():integer;
     function nss_initgroups_ignoreusers():string;
     procedure ldap_conf();
     procedure set_curfltpfs();
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   ETC_DEFAULT();
    procedure   ETC_SYSCONFIG_DEFAULT();
    procedure   autofs_ldap_auth_conf();
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
    procedure   nss_switch();
    procedure   START();
    procedure   STOP();
END;

implementation

constructor tautofs.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       zldap:=Topenldap.Create;
       if not TryStrToInt(SYS.GET_INFO('EnableAutoFSDebug'),EnableAutoFSDebug) then EnableAutoFSDebug:=1;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tautofs.free();
begin
    logs.Free;
    zldap.Free;
end;
//##############################################################################
function tautofs.BIN_PATH():string;
begin
if length(bin_path_memory)>0 then exit(bin_path_memory);
result:=SYS.LOCATE_GENERIC_BIN('automount');
bin_path_memory:=result;
end;
//##############################################################################

function tautofs.PID_NUM():string;
begin
    if not FIleExists(BIN_PATH()) then exit;

   if FileExists('/var/run/autofs-running') then begin
       result:=SYS.GET_PID_FROM_PATH('/var/run/autofs-running');
       if SYS.PROCESS_EXIST(result) then exit;
   end;
   result:=SYS.PIDOF(BIN_PATH());
end;
//##############################################################################
function tautofs.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_AUTOFS');
if length(result)>0 then exit;

filetmp:=logs.FILE_TEMP();
if not FileExists(BIN_PATH()) then exit;
   logs.Debuglogs(BIN_PATH()+' -V >'+filetmp+' 2>&1');
   fpsystem(BIN_PATH()+' -V >'+filetmp+' 2>&1');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='version\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
SYS.SET_CACHE_VERSION('APP_AUTOFS',result);

end;
//#############################################################################
procedure tautofs.START();
var
   count:integer;
   AutoFSCountDirs:integer;
   pid:string;
begin
    pid:=PID_NUM();
    IF sys.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: AutoFS Already running PID '+ pid);
       exit;
    end;

    if not FileExists('/etc/init.d/autofs') then begin
       logs.DebugLogs('Starting......: AutoFS is not installed');
       exit;
    end;

    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.AutoFS.php --count');
    if FileExists(SYS.LOCATE_GENERIC_BIN('mount.davfs')) then  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.AutoFS.php --davfs --no-reload');


    if not TryStrToInt(SYS.GET_INFO('AutoFSCountDirs'),AutoFSCountDirs) then AutoFSCountDirs:=0;
    if AutoFSCountDirs=0 then begin
         logs.DebugLogs('Starting......: AutoFS not mounted scheduled directories');
         exit;
    end;

    nss_switch();
    set_curfltpfs();
    ETC_DEFAULT();
    ldap_conf();
    ETC_SYSCONFIG_DEFAULT();
    autofs_ldap_auth_conf();



    fpsystem('/etc/init.d/autofs start');


 while not SYS.PROCESS_EXIST(PID_NUM()) do begin

        sleep(100);
        inc(count);
        if count>10 then begin
           logs.DebugLogs('Starting......: AutoFS (timeout)');
           break;
        end;
  end;

pid:=PID_NUM();
    IF sys.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: AutoFS successfully started and running PID '+ pid);
       exit;
    end;

logs.DebugLogs('Starting......: AutoFS failed');

end;


//#############################################################################
procedure tautofs.STOP();
var
   count:integer;
   pid:string;
   tmp:string;
   l:Tstringlist;
   i:integer;
   tt:integer;
   path:string;
    RegExpr:TRegExpr;
begin
  fpsystem('/etc/init.d/autofs stop');
  tmp:=logs.FILE_TEMP();
  fpsystem('/bin/mount >'+tmp+' 2>&1');
  l:=Tstringlist.Create;
  l.LoadFromFile(tmp);
  RegExpr:=TRegExpr.Create;


   RegExpr.Expression:='.+?on\s+\/automounts\/(.+?)\s+type';
   for i:=0 to l.Count -1 do begin
     if RegExpr.Exec(l.Strings[i]) then begin
        writeln('Stopping automount...........: umount /automounts/'+ RegExpr.Match[1]);
        fpsystem('/bin/umount -l /automounts/'+RegExpr.Match[1]);
     end;
   end;



  RegExpr.Expression:='automount\(pid([0-9]+).+?on\s+(.+?)\s+';
  for i:=0 to l.Count -1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
         pid:=RegExpr.Match[1];
         path:=RegExpr.Match[2];
         if sys.PROCESS_EXIST(pid) then begin
            tt:=0;
            writeln('Stopping automount...........: Stop pid '+pid);
            while SYS.PROCESS_EXIST(pid) do begin
                  fpsystem('/bin/kill -9 '+pid);
                  inc(tt);
                  sleep(150);
                  if tt>10 then break;
            end;
         end;
         writeln('Stopping automount...........: umount '+path);
         fpsystem('/bin/umount -l '+path);


      end;
  end;








end;


//#############################################################################
procedure tautofs.ETC_DEFAULT();
var l:Tstringlist;
debugmode:string;
begin

if not DirectoryExists('/etc/default') then begin
      logs.DebugLogs('Starting......: AutoFS /etc/default no such directory');
      exit;
end;

l:=Tstringlist.Create;
l.add('TIMEOUT=300');
l.add('DISABLE_DIRECT=0');
l.add('LDAPURI=ldap://'+zldap.ldap_settings.servername+'');
l.add('LDAPBASE=ou=mounts,'+zldap.ldap_settings.suffix);
l.add('AUTOFS_DONT_RESTART_ON_UPGRADES=1');
L.add('DEFAULT_AUTH_CONF_FILE="/etc/autofs_ldap_auth.conf"');
if EnableAutoFSDebug=1 then begin
   logs.DebugLogs('Starting......: AutoFS debug mode enabled');
   L.add('LOGGING="verbose"');
end;
L.add('MASTER_MAP_NAME="ou=auto.master,ou=mounts,'+zldap.ldap_settings.suffix+'"');
L.add('BROWSE_MODE="yes"');
L.add('LDAP_URI="ldap://'+zldap.ldap_settings.servername+'"');
L.add('SEARCH_BASE="ou=mounts,'+zldap.ldap_settings.suffix+'"');
L.add('USE_MISC_DEVICE="yes"');


logs.WriteToFile(l.Text,'/etc/default/autofs');
logs.DebugLogs('Starting......: AutoFS updating /etc/default/autofs done...');
l.free;
end;
//#############################################################################
procedure tautofs.ETC_SYSCONFIG_DEFAULT();
var l:Tstringlist;
debugmode:string;
begin
l:=Tstringlist.Create;
debugmode:='no';
if EnableAutoFSDebug=1 then begin
   logs.DebugLogs('Starting......: AutoFS debug mode enabled');
   debugmode:='yes';
end;
l.add('AUTOFS_OPTIONS=""');
l.add('LOCAL_OPTIONS=""');
l.add('APPEND_OPTIONS="yes"');
l.add('DEFAULT_MASTER_MAP_NAME="auto.master"');
l.add('DEFAULT_TIMEOUT=600');
l.add('UMOUNT_WAIT=600');
l.add('DEFAULT_BROWSE_MODE="yes"');
l.add('BROWSE_MODE="yes"');
if EnableAutoFSDebug=1 then l.add('LOGGING="debug"');
if EnableAutoFSDebug=1 then l.add('DEFAULT_LOGGING="debug"');
l.add('#DEFAULT_MAP_OBJECT_CLASS="nisMap"');
l.add('#DEFAULT_ENTRY_OBJECT_CLASS="nisObject"');
l.add('#DEFAULT_MAP_ATTRIBUTE="nisMapName"');
l.add('#DEFAULT_ENTRY_ATTRIBUTE="cn"');
l.add('#DEFAULT_VALUE_ATTRIBUTE="nisMapEntry"');
l.add('');
l.add('DEFAULT_MAP_OBJECT_CLASS="automountMap"');
l.add('DEFAULT_ENTRY_OBJECT_CLASS="automount"');
l.add('DEFAULT_MAP_ATTRIBUTE="ou"');
l.add('DEFAULT_ENTRY_ATTRIBUTE="cn"');
l.add('DEFAULT_VALUE_ATTRIBUTE="automountInformation"');
l.add('SEARCH_BASE="ou=auto.master,ou=mounts,'+zldap.ldap_settings.suffix+'"');
l.add('LDAP_URI="ldap://'+zldap.ldap_settings.servername+'"');
l.add('#');
l.add('#DEFAULT_MAP_OBJECT_CLASS="automountMap"');
l.add('#DEFAULT_ENTRY_OBJECT_CLASS="automount"');
l.add('#DEFAULT_MAP_ATTRIBUTE="automountMapName"');
l.add('#DEFAULT_ENTRY_ATTRIBUTE="automountKey"');
l.add('#DEFAULT_VALUE_ATTRIBUTE="automountInformation"');
l.add('DEFAULT_AUTH_CONF_FILE="/etc/autofs_ldap_auth.conf"');
l.add('USE_MISC_DEVICE="yes"');
l.add('');

if DirectoryExists('/etc/sysconfig') then begin
    logs.DebugLogs('Starting......: AutoFS updating /etc/sysconfig/autofs');
    logs.WriteToFile(l.Text,'/etc/sysconfig/autofs');
end;


if DirectoryExists('/usr/share/autofs5/conffiles') then begin
   logs.DebugLogs('Starting......: AutoFS updating /usr/share/autofs5/conffiles/default.autofs5 done...');
   logs.WriteToFile(l.Text,'/usr/share/autofs5/conffiles/default.autofs5');
end;


l.free;
end;
//#############################################################################
procedure tautofs.set_curfltpfs();
var
l:TstringList;
RegExpr:TRegExpr;
r:string;
i:integer;
begin

if not FileExists(SYS.LOCATE_GENERIC_BIN('curlftpfs')) then begin
   logs.DebugLogs('Starting......: AutoFS curlftps is not installed');
   exit;
end;
l:=TstringList.create;
l.add('#!/bin/bash');
l.add(SYS.LOCATE_GENERIC_BIN('curlftpfs')+' $1 $2 -o allow_other,disable_eprt');
l.add('');
logs.WriteToFile(l.Text,'/sbin/mount.curl');
logs.DebugLogs('Starting......: AutoFS updating /sbin/mount.curl');
fpsystem('/bin/chmod 755 /sbin/mount.curl');
l.clear;

l.add('#!/bin/bash');
l.add(SYS.LOCATE_GENERIC_BIN('fusermount')+' -u $1');
l.add('');
logs.WriteToFile(l.Text,'/sbin/umount.curl');
logs.DebugLogs('Starting......: AutoFS updating /sbin/umount.curl');
fpsystem('/bin/chmod 755 /sbin/umount.curl');
l.clear;
l.free;

end;
//##############################################################################



function tautofs.nss_initgroups_ignoreusers():string;
var
l:TstringList;
RegExpr:TRegExpr;
r:string;
i:integer;
begin

l:=TstringList.create;
RegExpr:=TRegExpr.create;
RegExpr.Expression:='^(.+?):';
try
   l.LoadFromFile('/etc/passwd');
except
   logs.Syslogs('nss_initgroups_ignoreusers:: fatal error !');
   exit;
end;


for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
          r:=r+ RegExpr.Match[1]+',';
    end;
end;


l.free;
RegExpr.free;
r:=r+'postfix,cyrus,mail';
if Copy(r,length(r),1)=',' then r:=Copy(r,0,length(r)-1);
result:=r;
end;
//##############################################################################

procedure tautofs.ldap_conf();
var
  ldap_conf:TstringList;
  server,port,admin,password:string;
  initgroups_ignoreusers:string;
begin

  server:=zldap.ldap_settings.servername;
  port:=zldap.ldap_settings.Port;
  admin:='cn='+zldap.ldap_settings.admin+','+zldap.ldap_settings.suffix;
  password:=zldap.ldap_settings.password;

  ldap_conf:=Tstringlist.Create;
  ldap_conf.Add('host '+server);
  ldap_conf.Add('port '+port);
  ldap_conf.Add('uri ldap://'+server+':'+port);
  ldap_conf.Add('ldap_version 3');
  ldap_conf.Add('binddn '+admin);
  ldap_conf.Add('rootbinddn '+admin);
  ldap_conf.Add('bindpw '+zldap.ldap_settings.password);
  ldap_conf.Add('bind_policy soft');
  ldap_conf.Add('scope sub');
  ldap_conf.Add('base '+zldap.ldap_settings.suffix);
  ldap_conf.Add('pam_password clear');
  ldap_conf.Add('pam_lookup_policy yes');
  ldap_conf.Add('pam_filter objectclass=posixAccount');
  ldap_conf.Add('pam_login_attribute uid');
  ldap_conf.Add('nss_reconnect_maxconntries 5');
  ldap_conf.Add('idle_timelimit 3600');
  ldap_conf.Add('nss_base_group '+zldap.ldap_settings.suffix+'?sub');
  ldap_conf.Add('nss_base_passwd '+zldap.ldap_settings.suffix+'?sub');
  ldap_conf.Add('nss_base_shadow '+zldap.ldap_settings.suffix+'?sub');

  initgroups_ignoreusers:=nss_initgroups_ignoreusers();
  if length(initgroups_ignoreusers)>0 then ldap_conf.Add('nss_initgroups_ignoreusers '+initgroups_ignoreusers);

  logs.WriteToFile(ldap_conf.Text,'/etc/ldap.conf');
  logs.Debuglogs('Starting......: AutoFS /etc/ldap.conf done');
  logs.WriteToFile(password,'/etc/ldap.secret');
  logs.Debuglogs('Starting......: AutoFS /etc/ldap.secret done');

end;

procedure tautofs.autofs_ldap_auth_conf();
var l:Tstringlist;
begin

l:=Tstringlist.Create;
l.Add('<autofs_ldap_sasl_conf');
l.Add('	usetls="no"');
l.Add('	tlsrequired="no"');
l.Add('	authrequired="yes"');
l.Add('	authtype="PLAIN"');
l.Add('	user="dn:cn='+zldap.ldap_settings.admin+','+zldap.ldap_settings.suffix+'"');
l.Add(' secret="'+zldap.ldap_settings.password+'"');
l.Add('/>');
logs.WriteToFile(l.Text,'/etc/autofs_ldap_auth.conf');
logs.DebugLogs('Starting......: AutoFS updating /etc/autofs_ldap_auth.conf done...');
l.free;
end;
//#############################################################################
procedure tautofs.nss_switch();
var
   l:Tstringlist;
   i:Integer;
   RegExpr:TRegExpr;
begin
if not FileExists(BIN_PATH()) then begin
   logs.DebugLogs('Starting......: AutoFS is not installed');
   exit;
end;

if not FileExists('/etc/nsswitch.conf') then begin
  logs.DebugLogs('Starting......: AutoFS unable to stat /etc/nsswitch.conf');
  exit;
end;

l:=Tstringlist.Create;
l.LoadFromFile('/etc/nsswitch.conf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^automount:\s+';
for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       logs.DebugLogs('Starting......: AutoFS nsswitch.conf already set');
       RegExpr.free;
       l.free;
       exit;
   end;
end;

l.Add('automount:'+chr(9)+'ldap files');
logs.WriteToFile(l.Text,'/etc/nsswitch.conf');
logs.DebugLogs('Starting......: AutoFS updating nsswitch.conf done...');
RegExpr.free;
l.free;
end;
//#############################################################################
function tautofs.mount_count():integer;
var
   tmpstr:string;
   l:Tstringlist;
   i:Integer;
   RegExpr:TRegExpr;
begin
result:=0;
tmpstr:=logs.FILE_TEMP();
if not fileExists('/etc/init.d/autofs') then exit;
logs.Debuglogs('/etc/init.d/autofs status >'+tmpstr+' 2>&1');
fpsystem('/etc/init.d/autofs status >'+tmpstr+' 2>&1');
if not fileExists(tmpstr) then exit;
RegExpr:=TRegExpr.Create;
l:=tStringlist.Create;
l.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
     RegExpr.Expression:='automount\s+.+';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then inc(result);
end;

RegExpr.free;
l.free;
end;
end.
