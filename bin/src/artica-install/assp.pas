unit assp;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr,zsystem,openldap,tcpip;



type
tassp=class


private
LOGS:Tlogs;
SYS:TSystem;
artica_path:string;
zldap:Topenldap;
EnableASSP:integer;
EnableASSPBackup:integer;
NotASSPRemovePass:integer;
EnablePostfixMultiInstance:integer;

function PID_NUM():string;
procedure REMOVEPASSWORD();
procedure allowAdminConnectionsFrom();
procedure smtpDestination();
procedure LDAP_CONFIG();
procedure SET_CONFIG(key:string;value:string);
procedure InternalsIPs();
public
procedure   Free;
constructor Create(const zSYS:Tsystem);
function    VERSION():string;
procedure   START();
procedure   STOP();
procedure   CHECK_POSTFIX();
FUNCTION    STATUS():string;
procedure    RELOAD();
END;

implementation

constructor tassp.Create(const zSYS:Tsystem);
begin
forcedirectories('/etc/artica-postfix');
LOGS:=tlogs.Create();
SYS:=zSYS;
if not TryStrToInt(SYS.GET_INFO('EnableASSP'),EnableASSP) then
begin EnableASSP:=0 end;
if not TryStrToInt(SYS.GET_INFO('NotASSPRemovePass'),NotASSPRemovePass) then
begin NotASSPRemovePass:=0 end;
if not TryStrToInt(SYS.GET_INFO('EnableASSPBackup'),EnableASSPBackup) then
begin EnableASSPBackup:=0 end;
if not TryStrToInt(SYS.GET_INFO('EnablePostfixMultiInstance'),EnablePostfixMultiInstance) then
begin EnablePostfixMultiInstance:=0 end;

if EnablePostfixMultiInstance=1 then
begin
EnableASSP:=0;
end;


if not DirectoryExists('/usr/share/artica-postfix') then
begin
artica_path:=ParamStr(0);
artica_path:=ExtractFilePath(artica_path);
artica_path:=AnsiReplaceText(artica_path,'/bin/','');

end
else
begin
artica_path:='/usr/share/artica-postfix';
end;
end;
//##############################################################################
procedure tassp.free();
begin
logs.Free;
end;
//##############################################################################
function tassp.VERSION():string;
var
RegExpr:TRegExpr;
FileDatas:TStringList;
i:integer;
BinPath:string;
filetmp:string;
debug:boolean;
begin
if not FileExists('/usr/share/assp/assp.pl') then
begin exit end;

result:=SYS.GET_CACHE_VERSION('APP_ASSP');
if length(result)>0 then
begin exit end;
debug:=SYS.COMMANDLINE_PARAMETERS('--verbose');
fpsystem('/bin/chmod 755 /usr/share/assp/assp.pl');
filetmp:='/usr/share/assp/assp.pl';
if not FileExists(filetmp) then exit;
RegExpr:=TRegExpr.Create;
FileDatas:=TStringList.Create;
FileDatas.LoadFromFile(filetmp);
for i:=0 to FileDatas.Count-1 do begin
    RegExpr.Expression:='our\s+\$version[\s=]+.+?([0-9\.]+)';
    if RegExpr.Exec(FileDatas.Strings[i]) then begin
       if debug then writeln('Found "',FileDatas.Strings[i],'" => "',RegExpr.Match[1],'" for ',RegExpr.Expression);
       result:=RegExpr.Match[1];
       break;
    end;

    RegExpr.Expression:='our.*?\$version\s+.*?([0-9\.]+)';
    if RegExpr.Exec(FileDatas.Strings[i]) then begin
       if debug then writeln('Found "',FileDatas.Strings[i],'" => "',RegExpr.Match[1],'" for ',RegExpr.Expression);
       result:=RegExpr.Match[1];
       break;
    end;

end;

RegExpr.free;
FileDatas.Free;
SYS.SET_CACHE_VERSION('APP_ASSP',result);

end;
//#############################################################################
function tassp.PID_NUM():string;
begin
result:=SYS.PIDOF_PATTERN(SYS.LOCATE_PERL_BIN()+'.+?assp\.pl');
end;


//#############################################################################
procedure tassp.RELOAD();
var
pid:string;
begin
pid:=PID_NUM();
if not SYS.PROCESS_EXIST(pid) then
begin
START();
exit;
end;

LDAP_CONFIG();
CHECK_POSTFIX();
logs.OutputCmd('/bin/kill -HUP '+pid);

end;


//#############################################################################
procedure tassp.LDAP_CONFIG();
var
ASSPDoLDAP:integer;
GreyListConfig:TiniFile;
ASSPEnableDelaying:integer;
MessageScoringLowerLimit:integer;
MessageScoringUpperLimit:integer;
begin
if not TryStrToInt(SYS.GET_INFO('ASSPDoLDAP'),ASSPDoLDAP) then
begin ASSPDoLDAP:=0 end;
if not TryStrToInt(SYS.GET_INFO('ASSPEnableDelaying'),ASSPEnableDelaying) then
begin ASSPEnableDelaying:=0 end;
if not TryStrToInt(SYS.GET_INFO('ASSPMessageScoringLowerLimit'),MessageScoringLowerLimit) then
begin MessageScoringLowerLimit:=50 end;
if not TryStrToInt(SYS.GET_INFO('ASSPMessageScoringUpperLimit'),MessageScoringUpperLimit) then
begin MessageScoringUpperLimit:=60 end;

zldap:=topenldap.Create;
SET_CONFIG('LDAPLogin','cn='+zldap.ldap_settings.admin+','+ zldap.ldap_settings.suffix);
SET_CONFIG('LDAPHost',zldap.ldap_settings.servername+':'+zldap.ldap_settings.Port);
SET_CONFIG('LDAPPassword',zldap.ldap_settings.password);
SET_CONFIG('LDAPRoot',zldap.ldap_settings.suffix);
SET_CONFIG('ldLDAPFilter','(|(&(objectclass=domainRelatedObject)(associatedDomain=DOMAIN))(&(objectclass=transportTable)(cn=DOMAIN)))');
SET_CONFIG('LDAPFilter','(&(objectclass=userAccount)(|(mailAlias=EMAILADDRESS)(mail=EMAILADDRESS)(mozillaSecondEmail=EMAILADDRESS)(FetchMailMatchAddresses=EMAILADDRESS)))');
SET_CONFIG('LDAPcrossCheckInterval','24');
SET_CONFIG('LDAPShowDB','file:ldaplistdb');
SET_CONFIG('MaxLDAPlistDays','90');
SET_CONFIG('DoLDAP',IntToStr(ASSPDoLDAP));
SET_CONFIG('ldLDAP','1');
SET_CONFIG('localDomains','file:files/localdomains.txt');
SET_CONFIG('NoValidRecipient','550 5.1.1 User unknown: EMAILADDRESS');
//SET_CONFIG('defaultLocalHost','file:files/localdomains.txt');
SET_CONFIG('incomingOkMail','okmail');
SET_CONFIG('maillogExt','.eml');
SET_CONFIG('sysLog','1');
SET_CONFIG('sysLogPort','514');
SET_CONFIG('sysLogIp','127.0.0.1');
SET_CONFIG('EnableDelaying',IntToStr(ASSPEnableDelaying));
SET_CONFIG('noDelay','file:files/nodelay.txt');

SET_CONFIG('MessageScoringLowerLimit',IntToStr(MessageScoringLowerLimit));
SET_CONFIG('MessageScoringUpperLimit',IntToStr(MessageScoringUpperLimit));
SET_CONFIG('MessageScoringLowerLimitTag',SYS.GET_INFO('ASSPMessageScoringLowerLimitTag'));
InternalsIPs();



if EnableASSPBackup=0 then
begin
SET_CONFIG('incomingOkMail','');
end
else
begin
SET_CONFIG('incomingOkMail','okmail');
end;




if FileExists('/etc/artica-postfix/settings/Daemons/ASSPDelayingConfig') then
begin
GreyListConfig:=TiniFile.Create('/etc/artica-postfix/settings/Daemons/ASSPDelayingConfig');
SET_CONFIG('DelayGripvalue',GreyListConfig.ReadString('CONF','DelayGripvalue','0.4'));
SET_CONFIG('DelaySSL',GreyListConfig.ReadString('CONF','DelaySSL','1'));
SET_CONFIG('DelayEmbargoTime',GreyListConfig.ReadString('CONF','DelayEmbargoTime','5'));
SET_CONFIG('DelayWaitTime',GreyListConfig.ReadString('CONF','DelayWaitTime','28'));
SET_CONFIG('DelayExpiryTime',GreyListConfig.ReadString('CONF','DelayExpiryTime','36'));
end;



logs.OutputCmd(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfix.maincf.php --assp');
end;
//#############################################################################
procedure tassp.InternalsIPs();
var
tcp:ttcpip;
RegExpr:TRegExpr;
noSpoofingCheckIP:string;
z,t:Tstringlist;
i:integer;
ipstr:string;
mynets:string;
matchedIp:string;
begin
noSpoofingCheckIP:='';
mynets:='';
tcp:=ttcpip.Create;
z:=Tstringlist.Create;
z.AddStrings(tcp.InterfacesStringList());
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfix.maincf.php --networks');
t:=Tstringlist.Create;

t.Add('127.0.0.1');

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='([0-9]+)\.([0-9]+)\.([0-9]+)';
for i:=0 to z.Count-1 do
begin
if(length(z.Strings[i]))=0 then
begin continue end;
ipstr:=tcp.IP_ADDRESS_INTERFACE(z.Strings[i]);
if ipstr='0.0.0.0' then
begin continue end;
if not RegExpr.Exec(ipstr) then
begin continue end;
t.Add(RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]);
noSpoofingCheckIP:=noSpoofingCheckIP+ RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'|';
mynets:=mynets+ RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'|';
end;



if FileExists('/etc/artica-postfix/mynetworks') then
begin
z.LoadFromFile('/etc/artica-postfix/mynetworks');
for i:=0 to z.Count-1 do
begin
if(length(z.Strings[i]))=0 then
begin continue end;
if z.Strings[i]='0.0.0.0' then
begin continue end;
if not RegExpr.Exec(z.Strings[i]) then
begin
logs.DebugLogs('tassp.InternalsIPs() reject mynetworks ='+z.Strings[i]);
continue;
end;

if SYS.ArrayAlreadyUsed(t,RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]) then
begin continue end;
logs.DebugLogs('tassp.InternalsIPs() math mynetworks ='+z.Strings[i]);
matchedIp:=RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3];
noSpoofingCheckIP:=noSpoofingCheckIP+ matchedIp+'|';
mynets:=mynets+matchedIp+'|';
t.Add(matchedIp);
logs.DebugLogs('tassp.InternalsIPs() mynets='+mynets);
end;
end;

if Copy(noSpoofingCheckIP,length(noSpoofingCheckIP),1)='|' then
begin
noSpoofingCheckIP:=Copy(noSpoofingCheckIP,1,length(noSpoofingCheckIP)-1);
end;

logs.DebugLogs('tassp.InternalsIPs() mynets='+mynets);
if Copy(mynets,length(mynets),1)='|' then
begin
mynets:=Copy(mynets,1,length(mynets)-1);
end;

logs.DebugLogs('Starting......: ASSP local net:'+ noSpoofingCheckIP);
logs.DebugLogs('Starting......: ASSP whitelist:'+ mynets);
SET_CONFIG('noSpoofingCheckIP',noSpoofingCheckIP);
SET_CONFIG('noBlockingIPs',noSpoofingCheckIP);
SET_CONFIG('noMsgID',noSpoofingCheckIP);
SET_CONFIG('noHelo',noSpoofingCheckIP);
SET_CONFIG('myServerRe',mynets);
SET_CONFIG('whiteListedIPs',mynets);



z.free;
t.free;
RegExpr.free;

end;
//#############################################################################



procedure tassp.START();
var
pid:string;
ck:integer;
cmd:string;
begin

if not FileExists('/usr/share/assp/assp.pl') then begin
   logs.DebugLogs('Starting......: ASSP Not installed');
   EnableASSP:=0;
   CHECK_POSTFIX();
   exit;
end;

pid:=PID_NUM();
if SYS.PROCESS_EXIST(pid) then
begin
logs.DebugLogs('Starting......: ASSP Already running using PID '+pid);
if EnableASSP=0 then
begin
logs.DebugLogs('Starting......: ASSP disabled');
CHECK_POSTFIX();
STOP();
exit;
end;
exit;
end;

if EnableASSP=0 then
begin
logs.DebugLogs('Starting......: ASSP is disabled');
CHECK_POSTFIX();
exit;
end;

fpsystem('/bin/chmod 755 /usr/share/assp/assp.pl');
CHECK_POSTFIX();
REMOVEPASSWORD();
LDAP_CONFIG();
allowAdminConnectionsFrom();
smtpDestination();
logs.DebugLogs('Starting......: ASSP ');
SetCurrentDir('/usr/share/assp');
cmd:='/usr/share/assp/assp.pl >/dev/null 2>&1 &';
logs.DebugLogs('Starting......: ASSP '+ cmd);
fpsystem(cmd);
ck:=0;
pid:=PID_NUM();
while not SYS.PROCESS_EXIST(pid) do
begin
pid:=PID_NUM();
sleep(100);
inc(ck);
if ck>40 then
begin
logs.DebugLogs('Starting......: ASSP server timeout...');
break;
end;
end;

pid:=PID_NUM();
if not SYS.PROCESS_EXIST(pid) then
begin
logs.DebugLogs('Starting......: ASSP server failed...');
exit;
end;

logs.DebugLogs('Starting......: ASSP server success PID '+pid);

end;
//#############################################################################
procedure tassp.STOP();
var
pid:string;
count,i:integer;
pids:Tstringlist;
begin

pid:=PID_NUM();
count:=0;

if not FileExists('/usr/share/assp/assp.pl') then
begin
writeln('Stopping ASSP server.........: Not installed');
exit;
end;

if not SYS.PROCESS_EXIST(pid) then
begin
writeln('Stopping ASSP server.........: Already stopped');
CHECK_POSTFIX();
exit;
end;

writeln('Stopping ASSP server.........: Stopping PID '+pid);
fpsystem('/bin/kill ' + pid);
while SYS.PROCESS_EXIST(PID_NUM()) do
begin
Inc(count);
sleep(100);
if count>50 then
begin
writeln('Stopping ASSP server.........: ' + PID_NUM() + ' PID (timeout)');
fpsystem('/bin/kill -9 ' + PID_NUM());
break;
end;
end;
pids:=Tstringlist.Create;
pids.AddStrings(SYS.PIDOF_PATTERN_PROCESS_LIST('/usr/share/assp/assp.pl'));
for i:=0 to pids.Count-1 do
begin
if length(trim(pids.Strings[i]))=0 then
begin continue end;
if SYS.PROCESS_EXIST(pids.Strings[i]) then
begin
writeln('Stopping ASSP server.........: Stopping PID '+pids.Strings[i]);
fpsystem('/bin/kill -9 ' + pids.Strings[i]);
sleep(100);
end;
end;



if SYS.PROCESS_EXIST(PID_NUM()) then
begin
writeln('Stopping ASSP server.........: ' + PID_NUM() + '  failed');
exit;
end;

writeln('Stopping ASSP server.........: success');
end;
//#############################################################################
FUNCTION tassp.STATUS():string;
var
ini:TstringList;
pid:string;
begin
if not FileExists('/usr/share/assp/assp.pl') then
begin exit end;
ini:=TstringList.Create;
ini.Add('[ASSP]');
ini.Add('service_name=APP_ASSP');
ini.Add('service_cmd=assp');
ini.Add('master_version=' +VERSION());
ini.Add('service_disabled='+ IntToStr(EnableASSP));

if EnableASSP=0 then
begin
result:=ini.Text;
ini.free;
SYS.MONIT_DELETE('APP_ASSP');
exit;
end;

if SYS.MONIT_CONFIG('APP_ASSP','/usr/share/assp/pid','assp') then
begin
ini.Add('monit=1');
result:=ini.Text;
ini.free;
exit;
end;

pid:=PID_NUM();
if SYS.PROCESS_EXIST(pid) then
begin ini.Add('running=1') end
else
begin ini.Add('running=0') end;
ini.Add('application_installed=1');
ini.Add('master_pid='+ pid);
ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
ini.Add('status='+SYS.PROCESS_STATUS(pid));

result:=ini.Text;
ini.free;
end;
//#########################################################################################
procedure tassp.REMOVEPASSWORD();
var
RegExpr:TRegExpr;
FileDatas:TStringList;
i:integer;
BinPath:string;
filetmp:string;
found:boolean;
begin
if NotASSPRemovePass=1 then
begin exit end;
if not FileExists('/usr/share/assp/assp.cfg') then
begin exit end;
FileDatas:=TStringList.Create;
FileDatas.LoadFromFile('/usr/share/assp/assp.cfg');
RegExpr:=TRegExpr.Create;
found:=false;
RegExpr.Expression:='^webAdminPassword';
for i:=0 to FileDatas.Count-1 do
begin
if RegExpr.Exec(FileDatas.Strings[i]) then
begin
FileDatas.Strings[i]:='webAdminPassword:=';
found:=true;
break;
end;
end;

if found then
begin
logs.WriteToFile(FileDatas.Text,'/usr/share/assp/assp.cfg');
end;
FileDatas.free;
RegExpr.free;
end;
//#############################################################################
procedure tassp.SET_CONFIG(key:string;value:string);
var
RegExpr:TRegExpr;
FileDatas:TStringList;
i:integer;
BinPath:string;
filetmp:string;
found:boolean;
begin
if not FileExists('/usr/share/assp/assp.cfg') then
begin exit end;
FileDatas:=TStringList.Create;
FileDatas.LoadFromFile('/usr/share/assp/assp.cfg');
RegExpr:=TRegExpr.Create;
found:=false;
RegExpr.Expression:='^'+key;
for i:=0 to FileDatas.Count-1 do
begin
if RegExpr.Exec(FileDatas.Strings[i]) then
begin
FileDatas.Strings[i]:=key+':='+value;
found:=true;
break;
end;
end;

if found then
begin
logs.WriteToFile(FileDatas.Text,'/usr/share/assp/assp.cfg');
end
else
begin
FileDatas.Add(key+':='+value);
logs.WriteToFile(FileDatas.Text,'/usr/share/assp/assp.cfg');
end;


FileDatas.free;
RegExpr.free;
end;
//#############################################################################



procedure tassp.allowAdminConnectionsFrom();
var
RegExpr:TRegExpr;
FileDatas:TStringList;
i:integer;
BinPath:string;
filetmp:string;
found:boolean;
begin
if NotASSPRemovePass=1 then
begin exit end;
if not FileExists('/usr/share/assp/assp.cfg') then
begin exit end;
FileDatas:=TStringList.Create;
FileDatas.LoadFromFile('/usr/share/assp/assp.cfg');
RegExpr:=TRegExpr.Create;
found:=false;
RegExpr.Expression:='^allowAdminConnectionsFrom';
for i:=0 to FileDatas.Count-1 do
begin
if RegExpr.Exec(FileDatas.Strings[i]) then
begin
FileDatas.Strings[i]:='allowAdminConnectionsFrom:=';
found:=true;
break;
end;
end;

if found then
begin
logs.WriteToFile(FileDatas.Text,'/usr/share/assp/assp.cfg');
end;
FileDatas.free;
RegExpr.free;
end;
//#############################################################################
procedure tassp.smtpDestination();
var
RegExpr:TRegExpr;
FileDatas:TStringList;
i:integer;
BinPath:string;
filetmp:string;
found:boolean;
begin

if not FileExists('/usr/share/assp/assp.cfg') then
begin exit end;
FileDatas:=TStringList.Create;
FileDatas.LoadFromFile('/usr/share/assp/assp.cfg');
RegExpr:=TRegExpr.Create;
found:=false;
RegExpr.Expression:='^smtpDestination';
for i:=0 to FileDatas.Count-1 do
begin
if RegExpr.Exec(FileDatas.Strings[i]) then
begin
FileDatas.Strings[i]:='smtpDestination:=127.0.0.1:6000';
found:=true;
break;
end;
end;

if found then
begin
logs.WriteToFile(FileDatas.Text,'/usr/share/assp/assp.cfg');
end;
FileDatas.free;
RegExpr.free;
end;
//#############################################################################





procedure tassp.CHECK_POSTFIX();
var
RegExpr:TRegExpr;
RegExpr2:TRegExpr;
FileDatas:TStringList;
i:integer;
BinPath:string;
filetmp:string;
found:boolean;
EnableAmavisInMasterCF:integer;
EnableArticaSMTPFilter:integer;
content_filter:string;
begin

logs.Debuglogs('Starting......: ASSP function depreciated line 672 assp.pas');
exit;

found:=false;
if not FileExists('/etc/postfix/master.cf') then exit;
if not TryStrToInt(SYS.GET_INFO('EnableArticaSMTPFilter'),EnableArticaSMTPFilter) then EnableArticaSMTPFilter:=0;
if not TryStrToInt(SYS.GET_INFO('EnableAmavisInMasterCF'),EnableAmavisInMasterCF) then EnableAmavisInMasterCF:=0;
if not FileExists('/usr/share/assp/assp.pl') then EnableASSP:=0;

if EnableAmavisInMasterCF=0 then begin
   if  EnableArticaSMTPFilter=1 then content_filter:=' -o content_filter=artica-filter:';
end;

RegExpr:=TRegExpr.Create;
RegExpr2:=TRegExpr.Create;
RegExpr.Expression:='inet\s+.+?smtpd';
RegExpr2.Expression:='^(.+?)\s+';
FileDatas:=Tstringlist.Create;
FileDatas.LoadFromFile('/etc/postfix/master.cf');
for i:=0 to FileDatas.Count-1 do begin
    if RegExpr.Exec(FileDatas.Strings[i]) then begin
       if EnableASSP=1 then begin
          found:=true;
          logs.Debuglogs('Starting......: ASSP Change Postfix port to 6000 line '+intToStr(i));
          RegExpr2.Exec(FileDatas.Strings[i]);
          if trim(RegExpr2.Match[1])='6000' then begin
             logs.Debuglogs('Starting......: Already done.');
             found:=false;
             break;
          end;

          FileDatas.Strings[i]:='127.0.0.1:6000	inet	n	-	n	-	-	smtpd'+content_filter;

          end else begin
              logs.Debuglogs('Starting......: ASSP Change Postfix port to 25 line '+intToStr(i));
              RegExpr2.Exec(FileDatas.Strings[i]);
              found:=true;
              if trim(RegExpr2.Match[1])='smtp' then begin
                 logs.Debuglogs('Starting......: Already done.');
                 found:=false;
                 break;
              end;
              FileDatas.Strings[i]:='smtp	inet	n	-	n	-	-	smtpd'+content_filter;
          end;
          break;
       end;
end;


if found then begin
   logs.WriteToFile(FileDatas.Text,'/etc/postfix/master.cf');
   logs.Debuglogs('Starting......: ASSP modify master.cf done');
   fpsystem('postfix stop && postfix start ');
end;

FileDatas.free;
RegExpr2.free;
RegExpr.free;
end;
//#############################################################################




end.

