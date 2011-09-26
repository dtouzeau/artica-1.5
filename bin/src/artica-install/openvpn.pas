unit openvpn;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,tcpip;



  type
  topenvpn=class


private
     LOGS:Tlogs;
     D:boolean;
     SYS:TSystem;
     artica_path:string;
     EnableOPenVPNServerMode:integer;
     DEV_TYPE:string;
     function PID_NUM():string;
     function BuildCommandLIneTUN():string;
     function INVESTIGATE():boolean;
     procedure CHANGE_CERTIFICATE_CONFIG(CertificatePath:string;key:string;value:string);
     procedure CHANGE_ADD_CERTIFICATE_CONFIG(CertificatePath:string;key:string;value:string;master_key:string);
     function  GET_CERTIFICATE_INFO(key:string):string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START(noinvestigate:boolean=false);
    procedure   STOP();
    function    BIN_PATH():string;
    function    VERSION():string;
    function    DirRessources:string;
    procedure   VerifConfig();
    procedure   BuildCertificate();
    procedure   CREATE_BRIDGE();
    procedure   ChangeCommonName(commonname:string);
    FUNCTION    STATUS():string;


END;

implementation

constructor topenvpn.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableOPenVPNServerMode:=0;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;

      if not TryStrtoInt(SYS.GET_INFO('EnableOPenVPNServerMode'),EnableOPenVPNServerMode) then EnableOPenVPNServerMode:=0;

end;
//##############################################################################
procedure topenvpn.free();
begin
    logs.Free;

end;
//##############################################################################
function topenvpn.BIN_PATH():string;
begin
     if FileExists('/usr/sbin/openvpn') then exit('/usr/sbin/openvpn');

end;
//##############################################################################
function topenvpn.PID_NUM():string;
begin
     result:=SYS.GET_PID_FROM_PATH('/var/run/openvpn/openvpn-server.pid');
end;
//##############################################################################
function topenvpn.DirRessources:string;
begin
     if DirectoryExists('/usr/share/doc/openvpn-2.0.9/easy-rsa/2.0') then exit('/usr/share/doc/openvpn-2.0.9/easy-rsa/2.0');
     if DirectoryExists('/usr/share/doc/openvpn/examples/easy-rsa/2.0') then exit('/usr/share/doc/openvpn/examples/easy-rsa/2.0');
     exit('/usr/share/artica-postfix/bin/install/openvpn');

end;
//##############################################################################

procedure topenvpn.START(noinvestigate:boolean);
var
   l:TstringList;
   pid:string;
   parms:string;
   count:integer;
   cmd:string;
   mini:TiniFile;
   server_name:string;
   RegExpr:TRegExpr;
   IP_START:string;
   IP_START_NETMASK:string;
   articaconfig:string;


begin
  pid:=PID_NUM();
  count:=0;
  articaconfig:='/etc/artica-postfix/settings/Daemons/ArticaOpenVPNSettings';
  if FileExists('/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE') then begin
      logs.DebugLogs('Starting......: OpenVPN Kaspersky Web Appliance, aborting...');
      exit;
  end;

   if not FileExists(BIN_PATH()) then begin
      logs.DebugLogs('Starting......: OpenVPN is not installed. expected "/usr/sbin/openvpn"');
      exit;
   end;

if SYS.isoverloadedTooMuch() then begin
   logs.DebugLogs('Starting......: System is overloaded');
   exit;
end;

   if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: OpenVPN is already running PID ' + pid + '...');
      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --client-start');
      exit;
   end;

   if EnableOPenVPNServerMode=0 then begin
       logs.DebugLogs('Starting......: OpenVPN server mode is disabled');
       fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --client-start');
       exit;
   end;

   mini:=TiniFile.Create(articaconfig);
   DEV_TYPE:=mini.ReadString('GLOBAL','DEV_TYPE','tun');
   forceDirectories('/etc/openvpn');
   forceDirectories('/var/log/openvpn');

   if FileExists(articaconfig) then begin
      logs.OutputCmd('/bin/cp '+articaconfig+' /etc/openvpn/server.conf');
   end else begin
       logs.Debuglogs('Starting......: OpenVPN "'+articaconfig+'" no such file...');
   end;



   if not FileExists('/etc/openvpn/server.conf') then begin
      logs.Debuglogs('Starting......: OpenVPN unable to stat /etc/openvpn/server.conf');
      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --client-start');
      exit;
   end;

   logs.DebugLogs('Starting......: OpenVPN tunnel type...: "' + DEV_TYPE + '"...');
   logs.DebugLogs('Starting......: OpenVPN ressources....: ' + DirRessources() + '...');
   logs.DebugLogs('Starting......: OpenVPN version.......: ' + VERSION() + '...');

   if not SYS.ip_forward_enabled() then begin
         logs.Syslogs('Starting......:  OpenVPN Enable IP Forwarding');
         fpsystem('sysctl -w net.ipv4.ip_forward=1');
   end else begin
        logs.Syslogs('Starting......: OpenVPN IP Forwarding is enabled');
   end;

   VerifConfig();

  server_name:=UpperCase(SYS.HOSTNAME_g());
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='(.+?)\.';
  if RegExpr.Exec(server_name) then begin
     server_name:=Uppercase(RegExpr.Match[1]);
  end else begin
     server_name:=Uppercase(server_name);
  end;


   logs.Debuglogs('Starting......: OpenVPN server verify key "vpn-server.key" in /etc/artica-postfix/openvpn/keys directory ' );
                                                                              
   if not FileExists('/etc/artica-postfix/openvpn/keys/vpn-server.key') then BuildCertificate();
   if not FileExists('/etc/artica-postfix/openvpn/keys/dh1024.pem') then BuildCertificate();

      if not FileExists('/etc/artica-postfix/openvpn/keys/vpn-server.key') then begin
           logs.Syslogs('Starting......: OpenVPN server unable to build certificates');
           exit;
      end;


   forceDirectories('/var/run/openvpn');
   forceDirectories('/var/log/openvpn');
   logs.DeleteFile('/var/log/openvpn/openvpn.log');

    cmd:=BIN_PATH()+' '+BuildCommandLIneTUN();
    logs.OutputCmd(cmd);
    count:=0;
  while not SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(700);
        inc(count);

        if count>50 then begin
              logs.DebugLogs('Starting......: OpenVPN Failed after wait 50*700 milliseconds ');
              logs.DebugLogs('Starting......: '+cmd);
              break;
        end;
  end;


  //Cannot load certificate file /etc/artica-postfix/openvpn/keys/vpn-server.crt: error:02001002:system library:fopen:No such file or directory

  pid:=PID_NUM();
  if SYS.PROCESS_EXIST(pid) then begin
      logs.DebugLogs('Starting......: OpenVPN Success with PID number '+ pid);
      logs.NOTIFICATION('OpenVPN server was successfully started PID '+pid,'','VPN');

      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --iptables-server');
  end else begin
      if not noinvestigate then begin
         logs.DebugLogs('Starting......: OpenVPN Failed,try to investigate');
          if INVESTIGATE() then START(true);
      end;
  end;

  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --client-start');

mini.free;

end;

//##############################################################################
function topenvpn.INVESTIGATE():boolean;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
begin
    result:=false;
    if not FileExists('/var/log/openvpn/openvpn.log') then exit;

    l:=Tstringlist.Create;
    l.LoadFromFile('/var/log/openvpn/openvpn.log');
    RegExpr:=TRegExpr.Create;
    for i:=0 to l.Count-1 do begin
        RegExpr.Expression:='Cannot load certificate file.+?fopen:No such file or directory';
        if RegExpr.Exec(l.Strings[i]) then begin
           logs.DebugLogs('Starting......: OpenVPN Certificate missing, try to build a new one');
           fpsystem('/bin/rm /etc/artica-postfix/openvpn/keys/*');
           BuildCertificate();
           exit(true);
        end;

        RegExpr.Expression:='Note: Cannot open TUN.+?TAP dev.+?';
        if RegExpr.Exec(l.Strings[i]) then begin
           logs.DebugLogs('Starting......: OpenVPN fatal error !!');
           logs.DebugLogs('Starting......: OpenVPN "'+ l.Strings[i]+'"');
           logs.DebugLogs('Starting......: Disable OpenVPN service...');
           logs.NOTIFICATION('Fatal Error: Could not start OpenVPN service',l.Strings[i]+' the service was disabled','VPN');
           SYS.set_INFO('EnableOPenVPNServerMode','0');
        end;
    end;

 l.free;
  RegExpr.free;
 logs.DebugLogs('Starting......: OpenVPN no common error found');

end;
//##############################################################################
function topenvpn.BuildCommandLIneTUN():string;
begin
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --server-conf');
result:=trim(logs.ReadFromFile('/etc/openvpn/cmdline.conf'));
if Copy(result,length(result),1)='?' then result:=Copy(result,1,length(result)-1);
end;
//############################### TAP ###############################################
procedure topenvpn.VerifConfig();
begin
        ForceDirectories('/etc/artica-postfix/openvpn');
    if not DirectoryExists(DirRessources()) then begin
       logs.DebugLogs('Starting......: OpenVPN Unable to stat ressources dir');
       exit();
    end;

    if not FIleExists('/etc/artica-postfix/openvpn/clean-all') then logs.OutputCmd('/bin/cp -rfv '+DirRessources+'/* /etc/artica-postfix/openvpn/');



end;
//##############################################################################
procedure topenvpn.BuildCertificate();
var
l:TstringList;
server_name,cmd:string;
RegExpr:TRegExpr;
i:integer;
pass:string;
OpenVPNConfigFile:String;
KEY_COUNTRY:string;
KEY_PROVINCE,KEY_CITY,KEY_ORG,KEY_EMAIL:string;
begin
  VerifConfig();

  l:=TstringList.Create;
  server_name:=UpperCase(SYS.HOSTNAME_g());
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='(.+?)\.';
  if RegExpr.Exec(server_name) then server_name:=RegExpr.Match[1];
  RegExpr.free;

  if not FileExists('/etc/artica-postfix/openvpn/whichopensslcnf') then begin
     logs.Debuglogs('WARNING !!! unable to stat /etc/artica-postfix/openvpn/whichopensslcnf');
     exit;
  end;

  if SYS.COMMANDLINE_PARAMETERS('--rebuild') then fpsystem('/bin/rm /etc/artica-postfix/openvpn/keys/*');

SetCurrentDir('/etc/artica-postfix/openvpn');
KEY_COUNTRY:=GET_CERTIFICATE_INFO('countryName_value');
KEY_PROVINCE:=GET_CERTIFICATE_INFO('stateOrProvinceName_value');
KEY_CITY:=GET_CERTIFICATE_INFO('localityName_value');
KEY_ORG:=GET_CERTIFICATE_INFO('organizationName_value');
KEY_EMAIL:=GET_CERTIFICATE_INFO('emailAddress_value');



if length(KEY_PROVINCE)=0 then KEY_PROVINCE:='CA';
if length(KEY_COUNTRY)=0 then KEY_COUNTRY:='US';
if length(KEY_CITY)=0 then KEY_CITY:='SanFrancisco';
if length(KEY_ORG)=0 then KEY_ORG:='Fort-fuston';
if length(KEY_EMAIL)=0 then KEY_EMAIL:='me@localhost.localdomain';

l.add('export EASY_RSA="/etc/artica-postfix/openvpn"');
l.add('export OPENSSL="openssl"');
l.add('export PKCS11TOOL="pkcs11-tool"');
l.add('export GREP="grep"');
l.add('export KEY_CONFIG=`/etc/artica-postfix/openvpn/whichopensslcnf /etc/artica-postfix/openvpn`');
l.add('export KEY_DIR="$EASY_RSA/keys"');
l.add('export PKCS11_MODULE_PATH="dummy"');
l.add('export PKCS11_PIN="dummy"');
l.add('export KEY_SIZE=1024');
l.add('export CA_EXPIRE=3650');
l.add('export KEY_EXPIRE=3650');
l.add('export KEY_COUNTRY="'+ KEY_COUNTRY+'"');
l.add('export KEY_PROVINCE="'+ KEY_PROVINCE+'"');
l.add('export KEY_CITY="'+KEY_CITY+'"');
l.add('export KEY_ORG="'+KEY_ORG+'"');
l.add('export KEY_EMAIL="'+ KEY_EMAIL+'"');
try
l.SaveToFile('/etc/artica-postfix/openvpn/vars');
except
 logs.Syslogs('BuildCertificate():: Unable to save file /etc/artica-postfix/openvpn/vars');
 exit;
end;

for i:=0 to l.Count-1 do begin
   logs.OutputCmd(l.Strings[i]);
end;

forceDirectories('/etc/artica-postfix/openvpn/keys');




l.Clear;
l.add('# For use with easy-rsa version 2.0');
l.add('HOME='+chr(9)+' .');
l.add('RANDFILE='+chr(9)+' /root/.rnd');
l.add('openssl_conf='+chr(9)+' openssl_init');
l.add('');
l.add('[ openssl_init ]');
l.add('oid_section='+chr(9)+'new_oids');
l.add('engines                ='+chr(9)+'engine_section');
l.add('[ new_oids ]');
l.add('[ ca ]');
l.add('default_ca='+chr(9)+'CA_default		');
l.add('[ CA_default ]');
l.add('');
l.add('dir='+chr(9)+'/etc/artica-postfix/openvpn/keys');
l.add('certs='+chr(9)+'/etc/artica-postfix/openvpn/keys');
l.add('crl_dir='+chr(9)+'/etc/artica-postfix/openvpn/keys');
l.add('database='+chr(9)+'/etc/artica-postfix/openvpn/keys/index.txt');
l.add('new_certs_dir='+chr(9)+'/etc/artica-postfix/openvpn/keys');
l.add('certificate='+chr(9)+'/etc/artica-postfix/openvpn/keys/ca.crt');
l.add('serial='+chr(9)+'/etc/artica-postfix/openvpn/keys/serial');
l.add('crl='+chr(9)+'/etc/artica-postfix/openvpn/keys/crl.pem');
l.add('private_key='+chr(9)+'/etc/artica-postfix/openvpn/keys/ca.key');
l.add('RANDFILE='+chr(9)+'/etc/artica-postfix/openvpn/keys/.rand');
l.add('');
l.add('x509_extensions='+chr(9)+'usr_cert		# The extentions to add to the cert');
l.add('default_days='+chr(9)+'3650			# how long to certify for');
l.add('default_crl_days= 30			# how long before next CRL');
l.add('default_md='+chr(9)+'md5			# which md to use.');
l.add('preserve='+chr(9)+'no			# keep passed DN ordering');
l.add('policy='+chr(9)+'policy_anything');
l.add('');
l.add('[ policy_match ]');
l.add('countryName='+chr(9)+'match');
l.add('stateOrProvinceName='+chr(9)+'match');
l.add('organizationName='+chr(9)+'match');
l.add('organizationalUnitName='+chr(9)+'optional');
l.add('commonName='+chr(9)+'supplied');
l.add('emailAddress='+chr(9)+'optional');
l.add('[ policy_anything ]');
l.add('countryName='+chr(9)+'optional');
l.add('stateOrProvinceName='+chr(9)+'optional');
l.add('localityName='+chr(9)+'optional');
l.add('organizationName='+chr(9)+'optional');
l.add('organizationalUnitName='+chr(9)+'optional');
l.add('commonName='+chr(9)+'supplied');
l.add('emailAddress='+chr(9)+'optional');
l.add('[ req ]');
l.add('default_bits='+chr(9)+'1024');
l.add('default_keyfile ='+chr(9)+'privkey.pem');
l.add('distinguished_name='+chr(9)+'req_distinguished_name');
l.add('attributes='+chr(9)+'req_attributes');
l.add('x509_extensions='+chr(9)+'v3_ca');
l.add('');
l.add('# Passwords for private keys if not present they will be prompted for');
l.add('# input_password='+chr(9)+'secret');
l.add('# output_password='+chr(9)+'secret');
l.add('string_mask='+chr(9)+'nombstr');
l.add('');
l.add('[ req_distinguished_name ]');
l.add('countryName='+chr(9)+'Country Name (2 letter code)');
l.add('countryName_default='+chr(9)+KEY_COUNTRY);
l.add('countryName_min='+chr(9)+'2');
l.add('countryName_max='+chr(9)+'2');
l.add('stateOrProvinceName='+chr(9)+'State or Province Name (full name)');
l.add('stateOrProvinceName_default='+chr(9)+KEY_PROVINCE);
l.add('localityName='+chr(9)+'Locality Name (eg, city)');
l.add('localityName_default='+chr(9)+KEY_CITY);
l.add('0.organizationName='+chr(9)+'Organization Name (eg, company)');
l.add('0.organizationName_default='+chr(9)+KEY_ORG);
l.add('organizationalUnitName='+chr(9)+'Organizational Unit Name (eg, section)');
l.add('commonName='+chr(9)+'Common Name (eg, your name or your server\''s hostname)');
l.add('commonName_max='+chr(9)+'64');
l.add('emailAddress='+chr(9)+'Email Address');
l.add('emailAddress_default='+chr(9)+KEY_EMAIL);
l.add('emailAddress_max='+chr(9)+'40');
l.add('organizationalUnitName_default='+chr(9)+KEY_ORG);
l.add('commonName_default='+chr(9)+KEY_ORG);
l.add('[ req_attributes ]');
l.add('challengePassword='+chr(9)+'A challenge password');
l.add('challengePassword_min='+chr(9)+'4');
l.add('challengePassword_max='+chr(9)+'20');
l.add('unstructuredName='+chr(9)+'An optional company name');
l.add('');
l.add('[ usr_cert ]');
l.add('basicConstraints=CA:FALSE');
l.add('nsComment='+chr(9)+'"Easy-RSA Generated Certificate"');
l.add('subjectKeyIdentifier=hash');
l.add('authorityKeyIdentifier=keyid,issuer:always');
l.add('extendedKeyUsage=clientAuth');
l.add('keyUsage='+chr(9)+'digitalSignature');
l.add('[ server ]');
l.add('basicConstraints=CA:FALSE');
l.add('nsCertType='+chr(9)+'server');
l.add('nsComment='+chr(9)+'"Easy-RSA Generated Server Certificate"');
l.add('subjectKeyIdentifier=hash');
l.add('authorityKeyIdentifier=keyid,issuer:always');
l.add('extendedKeyUsage=serverAuth');
l.add('keyUsage='+chr(9)+'digitalSignature, keyEncipherment');
l.add('');
l.add('[ v3_req ]');
l.add('basicConstraints='+chr(9)+'CA:FALSE');
l.add('keyUsage='+chr(9)+'nonRepudiation, digitalSignature, keyEncipherment');
l.add('[ v3_ca ]');
l.add('subjectKeyIdentifier=hash');
l.add('authorityKeyIdentifier=keyid:always,issuer:always');
l.add('basicConstraints='+chr(9)+'CA:true');
l.add('[ crl_ext ]');
l.add('authorityKeyIdentifier=keyid:always,issuer:always');
l.add('');
l.add('[ engine_section ]');
l.add('[ pkcs11_section ]');
l.add('engine_id='+chr(9)+'pkcs11');
l.add('dynamic_path='+chr(9)+'/usr/lib/engines/engine_pkcs11.so');
l.add('MODULE_PATH='+chr(9)+'dummy');
l.add('PIN='+chr(9)+'dummy');
l.add('init='+chr(9)+'0');
try
   l.SaveToFile('/etc/artica-postfix/openvpn/openssl.cnf');
except
logs.Syslogs('BuildCertificate():: Unable to save file /etc/artica-postfix/openvpn/openssl.cnf');
 exit;
end;
SetCurrentDir('/etc/artica-postfix/openvpn');
logs.OutputCmd('/bin/chmod 777 /etc/artica-postfix/openvpn/vars');
if ParamStr(2)='--rebuild' then begin
   SetCurrentDir('/etc/artica-postfix/openvpn');
   fpsystem('. ./vars');
   fpsystem('./clean-all');
end;

pass:=trim(SYS.GET_INFO('OpenVpnPasswordCert'));
if length(pass)=0 then pass:='MyKey';


if not FileExists('/etc/artica-postfix/openvpn/keys/index.txt') then begin
   logs.WriteToFile(' ','/etc/artica-postfix/openvpn/keys/index.txt');
end else begin
    logs.DebugLogs('Starting......: OpenVPN index.txt OK');
end;

if not FileExists('/etc/artica-postfix/openvpn/keys/serial') then begin
   logs.WriteToFile('01','/etc/artica-postfix/openvpn/keys/serial');
end else begin
    logs.DebugLogs('Starting......: OpenVPN serial OK');
end;

OpenVPNConfigFile:='/etc/artica-postfix/openvpn/openssl.cnf';





if not FileExists('/etc/artica-postfix/openvpn/keys/ca.key') then begin
   //openssl req -new -x509 -keyout ca.key -out ca.crt -config /etc/artica-postfix/openvpn/openssl.cnf
   //old 1:openssl req -batch -days 3650 -nodes -new -newkey rsa:1024 -sha1 -x509 -keyout "/etc/artica-postfix/openvpn/keys/ca.key" -out "/etc/artica-postfix/openvpn/keys/ca.crt" -config /etc/artica-postfix/openvpn/openssl.cnf
   // example :openssl req -days 3650 -nodes -new -keyout $1.key -out $1.csr -extensions server -config

   cmd:='openssl req -new -x509 -keyout /etc/artica-postfix/openvpn/keys/ca.key -out /etc/artica-postfix/openvpn/keys/ca.crt -config '+OpenVPNConfigFile+' -passout pass:'+pass+' -batch -days 3650';
   fpsystem(cmd);
   logs.Debuglogs(cmd);
end else begin
    logs.DebugLogs('Starting......: OpenVPN /etc/artica-postfix/openvpn/keys/ca.key OK');
end;

if not FileExists('/etc/artica-postfix/openvpn/keys/openvpn-ca.key') then begin
   cmd:='openssl req -new -batch -keyout /etc/artica-postfix/openvpn/keys/openvpn-ca.key -out /etc/artica-postfix/openvpn/keys/openvpn-ca.csr -config '+OpenVPNConfigFile+' -passout pass:'+pass;
   logs.Debuglogs(cmd);
   fpsystem(cmd);


end else begin
    logs.DebugLogs('Starting......: OpenVPN /etc/artica-postfix/openvpn/keys/openvpn-ca.key OK');
end;

if not FileExists('/etc/artica-postfix/openvpn/keys/openvpn-ca.crt') then begin
   cmd:='openssl ca -extensions v3_ca -days 3650 -out /etc/artica-postfix/openvpn/keys/openvpn-ca.crt -in /etc/artica-postfix/openvpn/keys/openvpn-ca.csr -batch -config '+OpenVPNConfigFile+' -passin pass:'+pass;
   logs.Debuglogs(cmd);
   fpsystem(cmd);

end else begin
    logs.DebugLogs('Starting......: OpenVPN /etc/artica-postfix/openvpn/keys/openvpn-ca.crt OK');
end;

fpsystem('/bin/cat /etc/artica-postfix/openvpn/keys/ca.crt /etc/artica-postfix/openvpn/keys/openvpn-ca.crt > /etc/artica-postfix/openvpn/keys/allca.crt');

if not FileExists('/etc/artica-postfix/openvpn/keys/vpn-server.key') then begin
   cmd:='openssl req -nodes -new -keyout /etc/artica-postfix/openvpn/keys/vpn-server.key -out /etc/artica-postfix/openvpn/keys/vpn-server.csr -batch -config '+OpenVPNConfigFile;
   logs.Debuglogs(cmd);
   fpsystem(cmd);

end else begin
    logs.DebugLogs('Starting......: OpenVPN /etc/artica-postfix/openvpn/keys/vpn-server.key OK');
end;

if not FileExists('/etc/artica-postfix/openvpn/keys/vpn-server.crt') then begin
   fpsystem('/bin/rm /etc/artica-postfix/openvpn/keys/index.txt');
   fpsystem('/bin/touch /etc/artica-postfix/openvpn/keys/index.txt');
   cmd:='openssl ca -keyfile /etc/artica-postfix/openvpn/keys/openvpn-ca.key -cert /etc/artica-postfix/openvpn/keys/openvpn-ca.crt -out /etc/artica-postfix/openvpn/keys/vpn-server.crt';
   cmd:=cmd+' -in /etc/artica-postfix/openvpn/keys/vpn-server.csr -extensions server -batch -config '+OpenVPNConfigFile+' -passin pass:'+pass;
   logs.Debuglogs(cmd);
   fpsystem(cmd);

end else begin
    logs.DebugLogs('Starting......: OpenVPN /etc/artica-postfix/openvpn/keys/vpn-server.crt OK');
end;

fpsystem('/bin/chmod 0600 /etc/artica-postfix/openvpn/keys/vpn-server.key');
logs.WriteToFile(pass,'/etc/artica-postfix/openvpn/keys/password');


{if not FileExists('/etc/artica-postfix/openvpn/keys/'+server_name+'.csr') then begin
   cmd:='openssl req -batch -days 3650 -nodes -new -newkey rsa:1024 -keyout "/etc/artica-postfix/openvpn/keys/'+server_name+'.key"';
   cmd:=cmd +' -out "/etc/artica-postfix/openvpn/keys/'+server_name+'.csr" -extensions server -config "/etc/artica-postfix/openvpn/openssl.cnf"';
   logs.OutputCmd(cmd);
end else begin
        logs.DebugLogs('Starting......: OpenVPN '+server_name+'.csr OK');
end;

if not FileExists('/etc/artica-postfix/openvpn/keys/'+server_name+'.crt') then begin
cmd:='openssl ca -batch -days 3650 -out "/etc/artica-postfix/openvpn/keys/'+server_name+'.crt"';
cmd:=cmd+ ' -in "/etc/artica-postfix/openvpn/keys/'+server_name+'.csr" -extensions server -md sha1 -config "/etc/artica-postfix/openvpn/openssl.cnf"';
logs.OutputCmd(cmd);
end else begin
        logs.DebugLogs('Starting......: OpenVPN '+server_name+'.crt OK');
end;
 }


if not FileExists('/etc/artica-postfix/openvpn/keys/dh1024.pem') then begin
   if length(SYS.PIDOF_PATTERN(SYS.LOCATE_OPENSSL_TOOL_PATH()+' dhparam -out'))=0 then begin
      logs.Debuglogs(SYS.LOCATE_OPENSSL_TOOL_PATH() +' dhparam -out /etc/artica-postfix/openvpn/keys/dh1024.pem 1024');
      fpsystem(SYS.LOCATE_OPENSSL_TOOL_PATH() +' dhparam -out /etc/artica-postfix/openvpn/keys/dh1024.pem 1024 &');
   end;
end else begin
    logs.DebugLogs('Starting......: OpenVPN dh1024.pem OK');
end;

logs.OutputCmd('/bin/chmod 0600 /etc/artica-postfix/openvpn/keys/*');

l.free;
end;

//#########################################################################################
procedure topenvpn.STOP();
var
   pid:string;
   count:integer;
mini:TiniFile;
eth:string;
begin
pid:=PID_NUM();
    mini:=TiniFile.Create('/etc/artica-postfix/settings/Daemons/ArticaOpenVPNSettings');
    eth:=mini.ReadString('GLOBAL','BRIDGE_ETH','');
    DEV_TYPE:=mini.ReadString('GLOBAL','DEV_TYPE','tun');
count:=0;
if SYS.PROCESS_EXIST(PID_NUM()) then begin
   writeln('Stopping OpenVPN......................: ' + pid + ' PID..');
   fpsystem('/bin/kill ' + pid);
end;
  while SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(100);
        count:=count+1;
        if count>20 then begin
            fpsystem('/bin/kill -9 ' + PID_NUM());
            break;
        end;
  end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.Syslogs('Stopping OpenVPN......................: Success');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --server-stop');
end;
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --iptables-delete');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openvpn.php --client-stop');
end;

//#########################################################################################
function topenvpn.GET_CERTIFICATE_INFO(key:string):string;
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
   chg:boolean;
begin
    if not FileExists('/etc/artica-postfix/ssl.certificate.conf') then exit;
    l:=Tstringlist.Create;
    l.LoadFromFile('/etc/artica-postfix/ssl.certificate.conf');
    chg:=false;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^'+key+'.*?=(.+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
             logs.DebugLogs('Starting......: OpenVPN '+key+' '+RegExpr.Match[1]+' in line '+IntTostr(i));
             result:=trim(RegExpr.Match[1]);
             break;
         end;

    end;


    l.free;
    RegExpr.free;
end;
//#########################################################################################
procedure topenvpn.CHANGE_CERTIFICATE_CONFIG(CertificatePath:string;key:string;value:string);
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
   chg:boolean;
begin
    l:=Tstringlist.Create;
    l.LoadFromFile(CertificatePath);
    chg:=false;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^'+key+'=';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
              l.Strings[i]:=key+'='+value;
             logs.DebugLogs('Starting......: OpenVPN change value '+key+' in line '+IntTostr(i));
             chg:=true;
         end;

    end;

    if chg then logs.WriteToFile(l.Text,CertificatePath);
    l.free;
    RegExpr.free;

end;
//#########################################################################################
procedure topenvpn.CHANGE_ADD_CERTIFICATE_CONFIG(CertificatePath:string;key:string;value:string;master_key:string);
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
   mKeyF:boolean;
   chg:boolean;
begin
    l:=Tstringlist.Create;
    l.LoadFromFile(CertificatePath);
    chg:=false;
    RegExpr:=TRegExpr.Create;
    mKeyF:=false;
    for i:=0 to l.Count-1 do begin
         RegExpr.Expression:='\[.*?'+master_key;

         if RegExpr.Exec(l.Strings[i]) then begin
           mKeyF:=true;
           continue;
         end;

          if mKeyF then begin
              RegExpr.Expression:='\[';
              if RegExpr.Exec(l.Strings[i]) then begin
                 logs.DebugLogs('Starting......: OpenVPN add value '+key+' in line '+IntTostr(i-1));
                 l.Insert(i-1,key+'='+value);
                 chg:=true;
                 break;
              end;
          end;

         if mKeyF then begin
            RegExpr.Expression:='^'+key+'=';
            if RegExpr.Exec(l.Strings[i]) then begin
              l.Strings[i]:=key+'='+value;
              logs.DebugLogs('Starting......: OpenVPN change value '+key+' in line '+IntTostr(i));
              chg:=true;
              break;
            end;
         end;
    end;

    if chg then logs.WriteToFile(l.Text,CertificatePath);
    l.free;
    RegExpr.free;

end;
//#########################################################################################



procedure topenvpn.CREATE_BRIDGE();
var
brctl:string;
l:TstringList;
RegExpr:TRegExpr;
gw:boolean;
i:integer;
t:TstringList;
mini:TiniFile;
eth:string;
eth_ip:string;
eth_broadcast:string;
eth_gateway:string;
tcp:ttcpip;
settcp:string;
network,netmask:string;

begin
    brctl:=SYS.LOCATE_BRCTL();
    tcp:=ttcpip.Create;
    if not FileExists(brctl) then begin
       logs.Syslogs('Starting......: OpenVPN (Bridge) Unable to locate brctl tool');
       exit;
    end;

    mini:=TiniFile.Create('/etc/artica-postfix/settings/Daemons/ArticaOpenVPNSettings');
    DEV_TYPE:=mini.ReadString('GLOBAL','DEV_TYPE','tun');
    if DEV_TYPE<>'tap0' then begin
           logs.Debuglogs('Starting......: OpenVPN (Bridge) tap is not set...');
           exit;
    end;

    settcp:=tcp.IP_ADDRESS_INTERFACE('br0');
    if settcp='0.0.0.0' then settcp:='';
    if length(settcp)>0 then begin
          logs.Debuglogs('Starting......: OpenVPN (Bridge) Already br0 bridged for '+tcp.IP_ADDRESS_INTERFACE('br0'));
          exit;
    end;

    mini:=TiniFile.Create('/etc/artica-postfix/settings/Daemons/ArticaOpenVPNSettings');
    eth:=mini.ReadString('GLOBAL','BRIDGE_ETH','eth0');
    network:=mini.ReadString('GLOBAL','IP_START','');
    netmask:=mini.ReadString('GLOBAL','NETMASK','');


    if length(eth)=0 then begin
       logs.Debuglogs('Starting......: OpenVPN (Bridge) failed to determine which NIC to bridge');
         exit;
    end;

    if length(network)=0 then begin
       logs.Debuglogs('Starting......: OpenVPN (Bridge) failed to determine which IP to route');
         exit;
    end;

    if length(netmask)=0 then begin
       logs.Debuglogs('Starting......: OpenVPN (Bridge) failed to determine which Mask to route');
         exit;
    end;


    logs.Debuglogs('Starting......: OpenVPN (Bridge) set ' + eth + ' information');

    eth_ip:=tcp.IP_ADDRESS_INTERFACE(eth);
    eth_broadcast:=tcp.IP_BROADCAST_INTERFACE(eth);
    eth_gateway:=tcp.IP_LOCAL_GATEWAY(eth);

if length(eth_ip)=0 then begin
       logs.Debuglogs('Starting......: OpenVPN (Bridge) failed to determine ip addr of ' + eth);
         exit;
    end;

if length(eth_broadcast)=0 then begin
       logs.Debuglogs('Starting......: OpenVPN (Bridge) failed to determine broadcast ' + eth);
         exit;
    end;

if length(eth_gateway)=0 then begin
       logs.Debuglogs('Starting......: OpenVPN (Bridge) failed to determine gateway of ' + eth);
         exit;
    end;

logs.Debuglogs('Starting......: OpenVPN (Bridge) set br0: '+eth_ip+' netmask '+netmask+': broadcast: '+eth_broadcast+' gateway: '+eth_gateway);

logs.OutputCmd('brctl addbr br0');
logs.OutputCmd('brctl addif br0 tap0');
logs.OutputCmd('brctl addif br0 '+eth);
logs.OutputCmd('ifconfig '+eth+' 0.0.0.0 promisc up');
logs.OutputCmd('ifconfig tap0 0.0.0.0 promisc up');
logs.OutputCmd('ifconfig br0 '+eth_ip+' netmask '+netmask+' broadcast '+eth_broadcast);
logs.OutputCmd('route add default gw '+eth_gateway);
logs.OutputCmd('route add -net '+network+' netmask '+netmask+' gw '+eth_gateway);







end;
//#########################################################################################
procedure topenvpn.ChangeCommonName(commonname:string);
var
     RegExpr:TRegExpr;
     l:tstringlist;
     i:integer;
begin

if not FileExists('/etc/artica-postfix/openvpn/openssl.cnf') then begin
    logs.Syslogs('topenvpn.ChangeCommonName():: Unable to stat /etc/artica-postfix/openvpn/openssl.cnf' );
    exit;
end;

l:=TstringList.Create;
l.LoadFromFile('/etc/artica-postfix/openvpn/openssl.cnf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^commonName_default';
for i:=0 to l.Count-1 do begin
    if RegExpr.exec(l.strings[i]) then begin
       logs.Debuglogs('Change commonName_default to ' + commonname);
       l.Strings[i]:='commonName_default='+chr(9)+commonname;
       try
          l.SaveToFile('/etc/artica-postfix/openvpn/openssl.cnf');
       except
         logs.Syslogs('topenvpn.ChangeCommonName():: Unable to save /etc/artica-postfix/openvpn/openssl.cnf' );
         exit;
       end;
    end;
end;

l.free;
RegExpr.free;
end;
//#########################################################################################



function topenvpn.VERSION():string;
var
   l:string;
   RegExpr:TRegExpr;
begin

   if not FileExists(BIN_PATH) then exit;
   result:=SYS.GET_CACHE_VERSION('APP_OPENVPN');
   if length(result)>0 then exit;


   l:=SYS.ExecPipe(BIN_PATH() + ' --version');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^OpenVPN\s+(.+?)\s+';
   RegExpr.Exec(l);
   result:=RegExpr.Match[1];
   RegExpr.free;
   SYS.SET_CACHE_VERSION('APP_OPENVPN',result);
end;
//#############################################################################
FUNCTION topenvpn.STATUS():string;
var
   pidpath:string;
begin
   if not FileExists(BIN_PATH) then exit;
   SYS.MONIT_DELETE('APP_OPENVPN');
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --openvpn >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################





end.
