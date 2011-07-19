unit setup_suse_class;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distriDetect;
type
  TStringDynArray = array of string;
  type
  tsuse=class


private
       libs:tlibs;
       ArchStruct:integer;
       function OpenSuseVersion():string;
       function CheckCyrus():string;
       function CheckDevcollectd():string;
       function Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
       procedure OpenSuse11specific();
       procedure pyzor();



public
      distri:tdistriDetect;
      constructor Create();
      function CheckBaseSystem():string;
      procedure Free;
      function InstallPackageLists(list:string):boolean;
      procedure Show_Welcome;
      function checkSamba():string;
      function checkApps(l:tstringlist):string;
      function InstallPackageListsSilent(list:string):boolean;
      function CheckPostfix():string;
      function checkSQuid():string;
      function CheckBasePHP():string;
      function CheckPDNS():string;
      function lxc():string;
END;

implementation

constructor tsuse.Create();
begin

libs:=tlibs.Create;
ArchStruct:=libs.ArchStruct();   ;
end;
//#########################################################################################
procedure tsuse.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tsuse.Show_Welcome;
var
   base,postfix,u,cyrus,samba,squid,pdns:string;
begin



    if not FileExists('/usr/bin/zypper') then begin
      writeln('Your system does not store /usr/bin/zypper utils, this program must be closed...');
      exit;
    end;
    if not FileExists('/tmp/zypper-update') then begin
       writeln('Checking.............: start updating repositories...');
       fpsystem('touch /tmp/zypper-update');
       fpsystem('/usr/bin/zypper ref');
    end;



    writeln('Checking.............: system...');
    writeln('Checking.............: Base system...');
    base:=CheckBaseSystem();
    writeln('Checking.............: Postfix system...');
    postfix:=trim(CheckPostfix());
    writeln('Checking.............: Cyrus system...');
    cyrus:=trim(CheckCyrus());
    writeln('Checking.............: Files Sharing system...');
    samba:=checkSamba();
    writeln('Checking.............: Squid proxy and securities...');
    squid:=checkSQuid();
    writeln('Checking.............: PowerDNS System...');
    pdns:=CheckPDNS();
     u:=libs.INTRODUCTION(base,postfix,cyrus,samba,squid);

    writeln('You have selected the option : ' + u);

    if length(u)=0 then begin
        if length(base)>0 then u:='B';
    end;

    if u='B' then begin
        InstallPackageLists(base);
        Show_Welcome();
        exit;
    end;

    if length(u)=0 then begin
       Show_Welcome();
        exit;
    end;

    if lowercase(u)='a' then begin
       InstallPackageLists(base + ' ' + postfix+' '+cyrus+' '+samba+' '+squid);
       pyzor();
       fpsystem('/usr/share/artica-postfix/bin/artica-make APP_ROUNDCUBE3');
       Show_Welcome();
       exit;
    end;


    if u='1' then begin
          InstallPackageLists(postfix);
          pyzor();
          fpsystem('/usr/share/artica-postfix/bin/artica-make APP_ISOQLOG');
          Show_Welcome;
          exit;
    end;

   if u='2' then begin
          OpenSuse11specific();
          InstallPackageLists(cyrus);
          fpsystem('/usr/share/artica-postfix/bin/artica-make APP_ROUNDCUBE3');
          fpsystem('/usr/share/artica-postfix/bin/artica-make APP_DOTCLEAR');
          Show_Welcome;
          exit;
    end;

   if u='3' then begin
          InstallPackageLists(samba);
          if FileExists('/etc/init.d/artica-postfix') then  begin
             fpsystem('/etc/init.d/artica-postfix restart samba >/dev/null 2>&1 &');
             fpsystem('/usr/share/artica-postfix/bin/artica-install --nsswitch');
          end;
          Show_Welcome;
          exit;
    end;

   if u='4' then begin
          InstallPackageLists(squid);
          Show_Welcome;
          exit;
    end;

    if length(u)=0 then begin
       if length(base)=0 then begin
          InstallPackageLists(postfix+' '+cyrus+' '+samba+' '+squid);
          libs.InstallArtica();
       end;
       Show_Welcome;
       exit;
    end;




end;
//#########################################################################################
function tsuse.InstallPackageLists(list:string):boolean;
var
   cmd:string;
   u  :string;
   i  :integer;
   ll :TStringDynArray;
   fulllist:string;
begin
if length(trim(list))=0 then exit;
result:=false;

writeln('');
writeln('The following package(s) must be installed in order to perform continue setup');
writeln('');
writeln('-----------------------------------------------------------------------------');
writeln('"',list,'"');
writeln('-----------------------------------------------------------------------------');
writeln('');
writeln('Do you allow install these packages? [Y]');

if not libs.COMMANDLINE_PARAMETERS('--silent') then begin
   readln(u);
end else begin
    u:='y';
end;


if length(u)=0 then u:='y';

if LowerCase(u)<>'y' then exit;


   ll:=Explode(',',list);
   for i:=0 to length(ll)-1 do begin
       if length(trim(ll[i]))>0 then begin
        writeln('');
        writeln('');
        writeln('Installing ', trim(ll[i]),' package number ',i,'/',length(ll));
        writeln('/usr/bin/zypper install -y ' + trim(ll[i]));
        writeln('');
        writeln('');
        fpsystem('/usr/bin/zypper install -y ' + trim(ll[i]));
       end;
   end;

   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   result:=true;


end;
//#########################################################################################
function tsuse.InstallPackageListsSilent(list:string):boolean;
var
   cmd:string;
   u  :string;
   i  :integer;
   ll :TStringDynArray;
   fulllist:string;
begin
if length(trim(list))=0 then exit;
result:=false;

   ll:=Explode(',',list);
   for i:=0 to length(ll)-1 do begin
       if length(trim(ll[i]))>0 then begin
          fulllist:=fulllist + ' ' +  trim(ll[i]);
       end;
   end;

   fpsystem('/usr/bin/zypper -n install ' + fulllist);

   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   result:=true;


end;
//#########################################################################################
function tsuse.CheckBaseSystem():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;
   SUZE_MAJOR:integer;
begin
   distri:=tdistriDetect.Create();
   SUZE_MAJOR:=distri.DISTRI_MAJOR;
   writeln('Checking.............: Base: Code SUSE ('+distri.DISTRINAME_VERSION+') MAJOR='+IntToStr(SUZE_MAJOR));

f:='';
l:=TstringList.Create;
l.Add('hal');
l.Add('cron');
l.Add('file');
l.Add('hdparm');
l.Add('less');
l.Add('rdate');
l.Add('rsync');
l.Add('rsh');
l.Add('openssh');
l.Add('strace');
l.Add('sysfsutils');
l.Add('tcsh');
l.Add('time');
l.Add('eject');
l.Add('glibc-locale');
l.add('gcc');
l.add('findutils-locate');

l.Add('pciutils ');
l.Add('usbutils');
l.Add('openldap2-client');
l.Add('openldap2');
l.Add('openssl');

//openvpn
l.add('bridge-utils');
l.add('openvpn');
l.Add('libmcrypt');
l.add('php5');
l.Add('php5-fastcgi');
l.Add('php5-ldap ');
l.Add('php5-mysql');
l.Add('php5-imap ');
l.Add('php5-pear');
l.add('php5-gd');
l.add('php5-posix');
l.add('php5-gettext');
l.add('php5-curl');
l.add('php5-fileinfo');
l.add('php5-ftp');
l.add('php5-zip');
l.add('php5-odbc');
l.add('php5-soap');
l.add('php5-sockets');
l.add('php5-pcntl');
//l.add('php5-APC');
//l.Add('php5-pear-log');
//l.Add('php5-pear-mail_mime');
//l.add('php5-pear-Net-Sieve');
l.Add('php5-mbstring');
l.Add('php5-mcrypt');
l.add('yast2-autofs');
l.add('curlftpfs');
l.add('monit');
l.add('scons');
l.add('dnsmasq');
L.add('wdfs');
l.add('iscsitarget');
l.add('open-iscsi');
l.add('libicu');


//NFS
l.add('nfs-client');
l.add('nfs-kernel-server');
l.add('cryptsetup');

l.add('apache2');
l.add('apache2-devel');
l.add('apache2-mod_php5');
l.add('apache2-mod_security2');
l.Add('lighttpd ');


if SUZE_MAJOR<11 then l.Add('mysql');
if SUZE_MAJOR>10 then l.add('mariadb');
if SUZE_MAJOR>10 then l.add('mariadb-client');
if SUZE_MAJOR>10 then l.add('libopenssl0_9_8');
l.Add('rrdtool');
l.Add('rrdtool-devel');
l.Add('perl-File-Tail');
l.Add('perl-libwww-perl');

//amavis
l.Add('perl-Convert-ASN1');
l.Add('perl-Convert-BinHex');
l.Add('perl-Crypt-OpenSSL-RSA');
l.Add('perl-IO-Multiplex');
l.Add('perl-IO-stringy');
l.Add('perl-ldap');
l.Add('perl-Mail-DKIM');
l.Add('perl-MailTools');
l.Add('perl-MIME-tools');
l.Add('perl-Unix-Syslog');

//OCS
l.Add('perl-Net-Server');
L.Add('perl-SOAP-Lite');
l.add('perl-Net-IP');
l.add('perl-XML-Simple');
//l.add('perl-Compress-Zlib');
l.add('perl-DBI');
L.add('perl-DBD-mysql');
L.add('apache2-mod_perl');
L.add('perl-Apache-DBI');
l.add('perl-Tie-IxHash');
l.add('perl-Socket6');
l.add('perl-IO-Socket-INET6');

l.Add('libmysqlclient-devel');


l.Add('cyrus-sasl-saslauthd');
l.Add('cyrus-sasl');
l.Add('cyrus-sasl-plain');
l.add('cyrus-sasl-md5');
l.Add('perl-Authen-SASL-Cyrus');
l.add('perl-Authen-SASL');

l.Add('sudo');
l.Add('gcc ');
l.Add('make');

l.Add('libexpat-devel'); //for squid;
l.Add('libxml2-devel');  //for squid
l.Add('pcre-devel');  //for squid
l.add('openldap2-devel'); //for squid
l.add('gdbm-devel');
l.add('krb5-devel');
l.Add('cyrus-sasl-gssapi');
l.add('unixODBC-devel');
l.add('unixODBC');
l.add('php5-devel');
l.add('freetype2-devel');
l.add('t1lib-devel');
l.add('libzip-devel');
l.add('aspell-devel');
l.add('libcurl-devel');
l.add('e2fsprogs-devel');
l.add('glibc-devel');
l.add('keyutils-devel');
l.add('krb5-devel');
l.add('libgcc');
l.add('libidn-devel');
l.add('libjpeg-devel');
l.add('libaio-devel');
l.add('libattr-devel');
l.add('libacl-devel');
l.add('zlib-devel');
l.add('libbz2-devel');
L.add('libtool');
l.add('mhash-devel'); //mhash.h -> lessfs
l.add('libcap-devel');

if SUZE_MAJOR<11 then l.add('libpng-devel');
if SUZE_MAJOR>10 then l.add('libpng12-devel');
if SUZE_MAJOR>10 then l.add('libGeoIP-devel');

l.add('libselinux-devel');
l.add('libsepol-devel');
l.add('libstdc++-devel');
l.add('xorg-x11-libX11-devel');
l.add('xorg-x11-libXau-devel');
l.add('xorg-x11-libXdmcp-devel');
l.add('xorg-x11-libXpm-devel');
l.add('net-snmp-devel');
l.add('openldap2-devel');
l.add('libopenssl-devel');
l.add('zlib-devel');
l.add('gd-devel');
L.add('sysstat');
l.add('GeoIP');
l.add('readline-devel');
l.add('libdb-4_5-devel');
l.add('bison');
l.add('gcc-c++');
//DHCP
l.Add('dhcp-server');

//PDNS
l.add('pdns');
l.add('pdns-backend-ldap');
l.add('pdns-recursor');

l.Add('ntp');
l.Add('iproute');
l.add('xtables-addons');

l.add('libusb-compat-devel'); //chagnged for 11.2
l.Add('perl-Inline');
l.Add('libcdio');
l.Add('libconfuse-devel');
l.Add('curl');
l.Add('sensors');

l.Add('bzip2');
l.Add('unrar');
l.add('unzip');
l.Add('unarj');
l.Add('zoo');
l.Add('htop');
l.Add('telnet');
l.Add('lsof');
l.Add('dar');
l.Add('preload');
l.Add('nmap');
fpsystem('/bin/rm -rf /tmp/packages.list');

for i:=0 to l.Count-1 do begin
    if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;

if not FileExists('/etc/init.d/ldap') then f:=f+',openldap2';

 result:=f;
end;
//#########################################################################################
function tsuse.CheckPDNS():string;
var
   l:TstringList;
   f:string;
   i:integer;
begin
l:=Tstringlist.Create;
l.add('pdns');
l.add('pdns-backend-ldap');
l.add('pdns-recursor');

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;

end;
//#########################################################################################
function tsuse.lxc():string;
 var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;
   UbuntuIntVer:integer;
   libs:tlibs;

begin
f:='';
l:=TstringList.Create;
distri:=tdistriDetect.Create();
libs:=tlibs.Create;

l.add('lxc');
l.add('bridge-utils');
l.add('iputils');
l.add('screen');
l.add('inotify-tools');
l.add('kernel-default');
end;





function tsuse.CheckBasePHP():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;
   UbuntuIntVer:integer;
   libs:tlibs;

begin
f:='';
l:=TstringList.Create;
distri:=tdistriDetect.Create();
libs:=tlibs.Create;

l.add('apache2-devel');

L.add('openldap2-devel');
l.add('libexpat-devel'); //expat.h
l.add('freetype2-devel'); // ftconfig.h
l.add('libgcrypt-devel'); //gcrypt.h
l.add('gd-devel'); //gdcache.h
l.add('gmp-devel'); //gmp.h
//l.add('libgdal1-debugsource');
//jpegint.h match pas
l.add('krb5-devel'); //gssapi_krb5.h
l.add('libmcrypt-devel'); //mcrypt.h
l.add('mhash-devel'); //mhash.h
l.add('libmysqlclient-devel'); //mysql.h
l.add('ncurses-devel'); //curses.h
l.add('pam-devel'); //pam_ext.h
l.add('pcre-devel'); //pcre.h
l.add('libpng-devel'); //png.h
//postgresql/c.h match pas
l.add('aspell-devel'); //pspell.h
l.add('recode-devel'); //recode.h
l.add('cyrus-sasl-devel'); //sasl.h
l.add('sqlite-devel'); //sqlite.h
l.add('libopenssl-devel'); //libcrypto.a
l.add('t1lib-devel'); //t1lib.h
l.add('libtidy-devel');//libtidy.a ,tify.h match pas
l.add('libtool'); //libtool
l.add('tcpd-devel'); //libwrap.a ,tcpd.h
//libxmlparse.a,libxmlparse.so ,xmlparse.h
l.add('libxml2-devel'); //libxml2.a,libxml2.a
l.add('libxslt-devel'); //libexslt.a
//bin/quilt match pas
l.add('re2c');//bin/re2c
l.add('unixODBC-devel');//sql.h
l.add('zlib-devel');//zlib.h
L.add('chrpath'); //bin/chrpath
//l.add('freetds-devel'); //sybdb.h
l.add('imap-devel');//c-client/smtp.h
l.add('curl-devel'); //curl.h
l.add('net-snmp-devel');//agent_callbacks.h

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
end;
//#########################################################################################

function tsuse.CheckPostfix():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('perl-razor-agents');
l.Add('razor-agents');
//l.Add('pyzor');

l.Add('perl-Crypt-SSLeay');
l.add('perl-Net-SSLeay');
l.Add('perl-Convert-TNEF');
l.Add('perl-HTML-Parser');
l.Add('perl-Archive-Zip');
l.Add('free-ttf-fonts');



l.add('gd');
l.Add('wv');
l.Add('postfix');
l.Add('clamav');
l.Add('spamassassin');
l.Add('perl-BerkeleyDB');
l.add('perl-Convert-UUlib');
l.add('krb5-devel');
l.add('libgssglue-devel');
l.Add('mailman');
l.Add('sendmail-devel');


//FuzzyOCR
l.add('netpbm');
//l.add('gifsicle');
l.add('giflib');
l.add('giflib-prog');
l.add('giflib-devel');
l.add('gocr');
l.add('ocrad');
l.add('ImageMagick');
//l.add('tesseract');
//l.add('perl-String-Approx');
l.add('perl-MLDBM');
l.add('perl-MLDBM-Sync');


//l.add('perl-IO-Compress');//Bzip2.pm
//l.add('perl-Email-Valid');// */Email/Valid.pm
//l.add('perl-File-ReadBackwards'); // **/File/ReadBackwards.pm
//l.add('perl-Mail-SPF');// Mail/SPF.pm
//l.add('perl-Email-MIME');// Email/MIME.pm
//l.add('perl-Email-MIME-Modifier'); // MIME/Modifier.pm
//l.add('perl-Mail-SRS'); // Mail/SRS.pm
l.add('perl-Net-DNS'); // Net/DNS.pm
// Sys/Syslog.pm
l.add('perl-ldap');// Net/LDAP.pm
//l.add('perl-Email-Send');// Email/Send.pm
l.add('perl-IO-Socket-SSL'); // IO/Socket/SSL.pm



fpsystem('/bin/rm -rf /tmp/packages.list');

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;

if not FileExists('/usr/bin/python') then f:=f + ',python';

 result:=f;
end;
//#########################################################################################
function tsuse.CheckCyrus():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('cyrus-imapd');
l.Add('mailsync');

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//#########################################################################################
function tsuse.checkSamba():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
fpsystem('touch /etc/artica-postfix/samba.check.time');
f:='';
l:=TstringList.Create;
l.Add('nss_ldap');
l.Add('samba');
l.Add('samba-client');
l.Add('libsmbclient0');
l.Add('pam_ldap');
l.Add('pam_smb');
l.Add('nmap');
l.add('cups-devel');
l.add('gutenprint');
l.Add('gutenprint-devel');
l.add('libjpeg-devel');
l.add('libtiff-devel');
l.add('e2fsprogs-devel');
l.add('pam-devel');
l.add('audit');
for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//#########################################################################################
function tsuse.CheckDevcollectd():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('iproute-dev');
l.Add('xfslibs-dev');
l.Add('librrd2-dev');
l.Add('libsensors-dev');
l.Add('libmysqlclient15-dev');
l.Add('libperl5.8');
L.add('xmms-dev');
L.add('xmms2-dev');
l.add('libesmtp-dev');
l.add('libnotify-dev');
l.add('libxml2-dev');
l.add('libpcap-dev');
l.add('hddtemp');
l.add('mbmon');
l.add('libconfig-general-perl');
l.Add('memcached');
for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;



function tsuse.checkSQuid():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('squid3');
l.add('sarg');
for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//########################################################################################
function tsuse.checkApps(l:tstringlist):string;
var
   f:string;
   i:integer;

begin
f:='';
for i:=0 to l.Count-1 do begin
    if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//########################################################################################
function tsuse.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
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
//#########################################################################################

procedure tsuse.pyzor();
var build:string;

begin
   if ArchStruct=64 then exit;
   build:=OpenSuseVersion();
   if not libs.RPM_is_application_installed('pyzor') then begin
     if build='11.0' then fpsystem('rpm -iv http://www.artica.fr/download/opensuse/11.0/pyzor-0.4.0-4.4.i586.rpm');
     if build='10.3' then fpsystem('rpm -iv http://www.artica.fr/download/opensuse/10.3/pyzor-0.4.0-4.3.i586.rpm');
   end;

end;
//#########################################################################################
function tsuse.OpenSuseVersion():string;
var
   RegExpr:TRegExpr;
begin
 distri:=tdistriDetect.Create;
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='.+?\s+([0-9\.]+)';
 
if RegExpr.Exec(distri.DISTRINAME) then begin
    result:=RegExpr.Match[1];
end;
 
   RegExpr.free;
 
end;
//#########################################################################################



procedure tsuse.OpenSuse11specific();
var
RegExpr:TRegExpr;
OpenSuzeVersion:string;
goodversion:boolean;
begin
if ArchStruct=64 then exit;
fpsystem('/bin/rpm -qa >/tmp/packages.list');
RegExpr:=TRegExpr.Create;
distri:=tdistriDetect.Create;
goodversion:=false;
RegExpr.Expression:='.+?\s+([0-9\.]+)';

if RegExpr.Exec(distri.DISTRINAME) then begin
    OpenSuzeVersion:=RegExpr.Match[1];
end;
writeln('Installing php5-mailparse, php5-mcrypt,pyzor,awstats...');
writeln('Distribution version ' + OpenSuzeVersion);
if OpenSuzeVersion='11.0' then begin
   goodversion:=true;
   if not libs.RPM_is_application_installed('php5-mailparse') then begin
      fpsystem('rpm -iv http://download.opensuse.org/repositories/server:/php:/extensions/openSUSE_11.0/i586/php5-mailparse-2.1.4-1.13.i586.rpm');
   end;
   if not libs.RPM_is_application_installed('php5-mcrypt') then begin
      fpsystem('rpm -iv http://download.opensuse.org/repositories/openSUSE:/11.0/standard/i586/php5-mcrypt-5.2.5-66.1.i586.rpm');
   end;
   if FIleExists('/usr/bin/python') then begin
      if not libs.RPM_is_application_installed('pyzor') then begin
         fpsystem('rpm -iv http://download.opensuse.org/repositories/server:/mail/openSUSE_11.0/i586/pyzor-0.4.0-4.4.i586.rpm');
       end;
   end;
  if not libs.RPM_is_application_installed('awstats') then begin
     fpsystem('rpm -iv http://download.opensuse.org/repositories/network:/utilities/openSUSE_11.0/noarch/awstats-6.9-5.1.noarch.rpm');
  end;
   
end;

if OpenSuzeVersion='11.1' then begin
   goodversion:=true;
   if not libs.RPM_is_application_installed('php5-mailparse') then begin
      fpsystem('rpm -iv http://download.opensuse.org/repositories/server:/php:/extensions/openSUSE_11.1/i586/php5-mailparse-2.1.4-1.13.i586.rpm');
   end;
   if not libs.RPM_is_application_installed('php5-mcrypt') then begin
      fpsystem('rpm -iv http://download.opensuse.org/distribution/11.1/repo/oss/suse/i586/php5-mcrypt-5.2.6-49.11.i586.rpm');
   end;
   if FIleExists('/usr/bin/python') then begin
      if not libs.RPM_is_application_installed('pyzor') then begin
         fpsystem('rpm -iv http://download.opensuse.org/repositories/server:/mail/openSUSE_11.1/i586/pyzor-0.4.0-6.1.i586.rpm');
       end;
   end;
  if not libs.RPM_is_application_installed('awstats') then begin
     fpsystem('rpm -iv http://download.opensuse.org/repositories/network:/utilities/openSUSE_11.1/noarch/awstats-6.9-9.1.noarch.rpm');
  end;

end;






if not goodversion then begin
   writeln('This setup currently not support your version : ' +  OpenSuzeVersion);
end;

fpsystem('/bin/rpm -qa >/tmp/packages.list');
 if not libs.RPM_is_application_installed('php5-mailparse') then begin
   writeln('Unable to install php5-mailparse !!!');
end;

if not libs.RPM_is_application_installed('php5-mcrypt') then begin
   writeln('Unable to install php5-mcrypt !!!');
end;

end;

end.
