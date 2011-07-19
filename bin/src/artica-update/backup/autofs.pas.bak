unit autofs;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr,zsystem,openldap;



  type
  tautofs=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     zldap:Topenldap;
     function mount_count():integer;

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
    function    STATUS:string;
END;

implementation

constructor tautofs.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       zldap:=Topenldap.Create;


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
   if FileExists('/usr/sbin/automount') then exit('/usr/sbin/automount');

end;
//##############################################################################

function tautofs.PID_NUM():string;
begin
    if not FIleExists(BIN_PATH()) then exit;
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

    nss_switch();
    ETC_DEFAULT();
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
function tautofs.STATUS:string;
var
ini:TstringList;
pid:string;
begin
   ini:=TstringList.Create;
   ini.Add('[APP_AUTOFS]');
   if not fileExists('/etc/init.d/autofs') then begin
      ini.Add('application_installed=0');
      ini.Add('service_disabled=0');
      result:=ini.Text;
      ini.free;
      logs.Debuglogs('tautofs.STATUS DONE');
      exit;
   end;

   if mount_count()=0 then begin
      ini.Add('application_installed=1');
      ini.Add('service_disabled=0');
      result:=ini.Text;
      ini.free;
      logs.Debuglogs('tautofs.STATUS DONE');
      exit;
   end;

   if fileExists('/etc/init.d/autofs') then begin
      pid:=PID_NUM();
      if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('application_enabled=1');
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
      ini.Add('master_version='+VERSION());
      ini.Add('status='+SYS.PROCESS_STATUS(pid));
      ini.Add('service_name=APP_AUTOFS');
      ini.Add('service_cmd=autofs');
      ini.Add('service_disabled=1');
   end;
   logs.Debuglogs('tautofs.STATUS DONE');
   result:=ini.Text;
   ini.free;

end;
//##############################################################################


procedure tautofs.ETC_DEFAULT();
var l:Tstringlist;
begin

if not FileExists('/etc/default/autofs') then begin
      logs.DebugLogs('Starting......: AutoFS /etc/default/autofs did not exists');
      exit;
end;

l:=Tstringlist.Create;
l.add('TIMEOUT=60');
l.add('DISABLE_DIRECT=1');
l.add('LDAPURI=ldap://'+zldap.ldap_settings.servername+'/');
l.add('LDAPBASE=ou=auto.master,ou=mounts,'+zldap.ldap_settings.suffix);
l.add('AUTOFS_DONT_RESTART_ON_UPGRADES=1');
L.add('DEFAULT_AUTH_CONF_FILE="etc/autofs_ldap_auth.conf"');
logs.WriteToFile(l.Text,'/etc/default/autofs');
logs.DebugLogs('Starting......: AutoFS updating /etc/default/autofs done...');
l.free;
end;
//#############################################################################
procedure tautofs.ETC_SYSCONFIG_DEFAULT();
var l:Tstringlist;
begin
                                                   
if not FIleExists('/etc/sysconfig/autofs') then begin
 logs.DebugLogs('Starting......: AutoFS /etc/sysconfig/autofs did not exists');
      exit;
end;
l:=Tstringlist.Create;
l.add('AUTOFS_OPTIONS=""');
l.add('LOCAL_OPTIONS=""');
l.add('APPEND_OPTIONS="yes"');
l.add('DEFAULT_MASTER_MAP_NAME="auto.master"');
l.add('DEFAULT_TIMEOUT=600');
l.add('DEFAULT_BROWSE_MODE="yes"');
l.add('DEFAULT_LOGGING="none"');
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
l.add('#');
l.add('#DEFAULT_MAP_OBJECT_CLASS="automountMap"');
l.add('#DEFAULT_ENTRY_OBJECT_CLASS="automount"');
l.add('#DEFAULT_MAP_ATTRIBUTE="automountMapName"');
l.add('#DEFAULT_ENTRY_ATTRIBUTE="automountKey"');
l.add('#DEFAULT_VALUE_ATTRIBUTE="automountInformation"');
l.add('DEFAULT_AUTH_CONF_FILE="/etc/autofs_ldap_auth.conf"');
l.add('');
logs.WriteToFile(l.Text,'/etc/sysconfig/autofs');
logs.DebugLogs('Starting......: AutoFS updating /etc/sysconfig/autofs done...');
l.free;
end;
//#############################################################################
procedure tautofs.autofs_ldap_auth_conf();
var l:Tstringlist;
begin

l:=Tstringlist.Create;
l.Add('<autofs_ldap_sasl_conf');
l.Add('	usetls="no"');
l.Add('	tlsrequired="no"');
l.Add('	authrequired="autodetect"');
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
