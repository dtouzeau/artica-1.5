unit setup_mandrake_class;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distriDetect;
type
  TStringDynArray = array of string;
  type
  tmandrake=class


private
       libs:tlibs;
       function CheckDevcollectd():string;
       function CheckSelinux():string;
       function DisableSeLinux():string;
       function Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
       function URPMI2008():boolean;
       function mandrake_release():string;







public
      distri:tdistriDetect;
      constructor Create();
       function CheckBaseSystem():string;
      procedure Free;
      function InstallPackageLists(list:string):boolean;
      procedure Show_Welcome;
      function checkSamba():string;
      function checkApps(l:tstringlist):string;
      function CheckPostfix():string;
      function InstallPackageListsSilent(list:string):boolean;
       function CheckCyrus():string;
       function checkSQuid():string;
       function CheckPDNS():string;
       function CheckZabbix():string;
       function CheckOpenVPN():string;

END;

implementation

constructor tmandrake.Create();
begin

libs:=tlibs.Create;
end;
//#########################################################################################
procedure tmandrake.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tmandrake.Show_Welcome;
var
   base,postfix,u,cyrus,samba,squid,selinux,release,pdns,zabbix,openvpn:string;
begin

   if not FileExists('/usr/sbin/urpmi') then begin
      writeln('Your system does not store /usr/sbin/urpmi utils, this program must be closed...');
      exit;
    end;
    if not FileExists('/tmp/zypper-update') then begin
       fpsystem('touch /tmp/zypper-update');
       release:=mandrake_release();
       if release='2008.0' then URPMI2008();
       fpsystem('/usr/sbin/urpmi.update');
    end;



    writeln('Checking.............: system...');
    writeln('Checking.............: SeLinux...');

    selinux:=trim(CheckSelinux());
    if selinux='y' then begin
        writeln('Artica is not compliance with SeLinux installed on your system...');
        writeln('Do you want to uninstall it ? [Y]');
        readln(u);
        if length(u)=0 then u:='Y';
        if u='Y' then begin
           DisableSeLinux();
           exit;
        end;
        halt(0);
    end;


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
    writeln('Checking.............: Zabbix System...');
    zabbix:=CheckPDNS();
    openvpn:=CheckOpenVPN();
    u:=libs.INTRODUCTION(base,postfix,cyrus,samba,squid,openvpn);

    writeln('You have selected the option : ' + u);

    if length(u)=0 then begin
        if length(base)>0 then u:='B';
    end;

    if u='B' then begin
        InstallPackageListsSilent('make,imake');
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
       fpsystem('/usr/share/artica-postfix/bin/artica-roundcube --install --verbose');
       fpsystem('/etc/init.d/artica-postfix restart');
       Show_Welcome();
       exit;
    end;

   if lowercase(u)='c' then begin
       fpsystem('/usr/share/artica-postfix/bin/artica-make APP_AMAVISD_NEW');
       fpsystem('/etc/init.d/artica-postfix restart postfix');
       Show_Welcome();
       exit;
    end;


    if u='1' then begin
          InstallPackageLists(postfix);
          Show_Welcome;
          exit;
    end;

   if u='2' then begin
          InstallPackageLists(cyrus);
          fpsystem('/usr/share/artica-postfix/bin/artica-roundcube --install --verbose');
          fpsystem('/etc/init.d/artica-postfix restart');
          Show_Welcome;
          exit;
    end;

   if u='3' then begin
          InstallPackageLists(samba);
          Show_Welcome;
          exit;
    end;

   if u='4' then begin
          InstallPackageLists(squid);
          Show_Welcome;
          exit;
    end;

   if u='8' then begin
          InstallPackageLists(zabbix);
          Show_Welcome;
          exit;
    end;

   if u='9' then begin
          InstallPackageLists(openvpn);
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
function tmandrake.InstallPackageLists(list:string):boolean;
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
          fulllist:=fulllist + ' ' +  trim(ll[i]);
       end;
   end;

   fpsystem('/usr/sbin/urpmi --auto --force ' + fulllist);

   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   result:=true;


end;
//#########################################################################################
function tmandrake.InstallPackageListsSilent(list:string):boolean;
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

   fpsystem('/usr/sbin/urpmi --auto --force ' + fulllist);

   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   result:=true;


end;
//#########################################################################################
function tmandrake.CheckSelinux():string;
var
   l:TstringList;
   f:string;
   i:integer;
   RegExpr:TRegExpr;
begin
result:='';
if not FileExists('/etc/selinux/config') then exit();
l:=TstringList.Create;
l.LoadFromFile('/etc/selinux/config');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='SELINUX=(.+)';
for i:=0 to l.Count-1 do begin
     if RegExpr.Exec(l.Strings[i]) then begin
         if trim(RegExpr.Match[1])<>'disabled' then begin
            result:='y';
            break;
         end;
     end;
end;
RegExpr.Free;
l.Free;
end;
//#########################################################################################
function tmandrake.DisableSeLinux():string;
var
   l:TstringList;
begin
if not FileExists('/etc/selinux/config') then exit();
l:=TstringList.Create;
l.Add('SELINUX=disabled');
l.Add('SELINUXTYPE=targeted');
l.SaveToFile('/etc/selinux/config');
l.free;
Writeln('You need to reboot your computer.....');
Writeln('after rebooting , launch the command');
writeln('"'+paramStr(0)+'"');
halt(0);
end;
//#########################################################################################

function tmandrake.CheckBaseSystem():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;
begin
f:='';
l:=TstringList.Create;
distri:=tdistriDetect.Create();
l.Add('hal');
l.Add('cron');
l.Add('file');
l.Add('hdparm');
l.Add('less');
l.Add('nscd');
l.Add('rdate');
l.Add('rsync');
l.Add('rsh');
l.Add('openssh');
l.Add('strace');
l.Add('sysfsutils');
l.Add('tcsh');
l.Add('time');
l.Add('eject');
//DHCP /usr/sbin/dhcpd3
l.Add('dhcp-server');


l.Add('pciutils ');
l.Add('usbutils');
l.add('wv');
//LDAP
l.Add('openldap-servers');
l.Add('openldap-clients');

l.Add('openssl');

//PHP+LIGHTTPD
l.Add('libmcrypt');
if distri.DISTRI_MAJOR<2010 then l.Add('php-cgi');
l.Add('php-fcgi');
l.Add('php-ldap ');
l.Add('php-mysql');
l.Add('php-imap ');
l.Add('php-pear');
l.add('php-gd');
l.add('php-curl');
l.add('xapian-bindings-php');
l.add('php-geoip');

//l.Add('php-pear-Log');
l.Add('php-mailparse');
if distri.DISTRI_MAJOR<2010 then l.add('php-mime_magic');
l.Add('php-mbstring');
l.Add('php-mcrypt');
l.Add('lighttpd');
l.add('lighttpd-mod_compress');

l.Add('rrdtool');
//l.Add('rrdtool-devel');
l.Add('perl-File-Tail');
l.add('mhonarc');
l.add('autofs');

//l.Add('mysql-devel');
l.Add('mysql');
l.Add('perl-libwww-perl');

l.Add('libsasl2-plug-ldapdb');
l.Add('cyrus-sasl');
l.add('perl-Authen-SASL');

l.Add('sudo');
l.add('stunnel');
if distri.DISTRI_MAJOR<2010 then l.add('portmap');
l.add('nfs-utils');
l.add('nfs-utils-clients');
l.add('cpulimit');
//DEVEL
l.Add('gcc');
l.Add('make');
l.add('byacc');
l.add('flex');
l.add('makedepend');
l.add('imake');
l.add('gcc-c++');

//xapian
l.add('catdoc');
l.add('antiword');
l.add('xapian-bindings-php');

l.add('libexpat1-devel'); //for squid
l.add('libxml2-devel'); //for squid
l.add('libpcre-devel'); //for squid
l.add('libunixODBC1');//for php5
l.add('unixODBC');// For php5
l.add('php-devel');
l.add('freetype-devel');
l.add('libt1lib-devel');
l.add('libpaper-devel');
l.add('libbzip2-devel');
l.add('libaspell-devel');
l.add('libcurl-devel');
l.add('libext2fs-devel');
l.add('freetype-devel');
l.add('libxapian-devel');
l.add('glibc-devel');
l.add('libkrb53-devel');
l.add('libgcc');
l.add('libidn-devel');
l.add('libjpeg62-devel');
l.add('libpng-devel');
l.add('libselinux-devel');
l.add('libsepol-devel');
l.add('libstdc++-devel');
l.add('libxpm-devel');
l.add('libnet-snmp-devel');
l.add('libldap2.4_2-devel');
l.add('libopenssl0.9.8-devel');
l.add('libreadline5');
l.add('libltdl3');
l.add('zlib1-devel');
l.add('tcp_wrappers');
L.add('sysstat');
l.add('geoip');
l.add('libgeoip-devel');
l.add('apache-mpm-prefork');
l.add('libclamav-devel');
l.add('libmysql-devel');
l.add('libmysql-static-devel');
l.add('monit');
l.add('libncurses-devel'); // curses.h -> ClamAV
l.add('libltdl-devel'); //clamav

l.add('lvm2');

l.add('tcp_wrappers');
l.add('rsync');

//PDNS
l.add('pdns');
l.add('pdns-backend-ldap');
l.add('pdns-recursor');


//perl
l.Add('perl-Module-Build');
l.add('perl-BerkeleyDB');
l.add('perl-Convert-ASN1');
l.add('perl-rrdtool');
l.Add('perl-Crypt-SSLeay');
l.add('perl-Net-SSLeay');
l.Add('perl-Convert-TNEF');
l.Add('perl-HTML-Parser');
l.Add('perl-Archive-Zip');
l.Add('perl-Font-TTF');
l.Add('perl-Net-DNS-Resolver-Programmable');
l.add('perl-Crypt-OpenSSL-Random');
l.add('perl-Unix-Syslog');
l.add('perl-Net-Server');
l.add('perl-GSSAPI');
l.add('perl-XML-Filter-BufferText');
l.add('perl-Text-Iconv');
l.add('perl-XML-SAX-Writer');
l.add('perl-Convert-UUlib');
l.add('perl-ldap');
l.add('perl-Geo-IP');

l.Add('ntp');
l.Add('iproute');
l.add('hostapd');
l.Add('libusb-compat0.1-static-devel');
l.Add('perl-Inline');
l.Add('libcdio');
l.Add('curl');
l.add('cryptsetup');

l.add('udisks');




//DB
l.add('libdb4.7-devel');

//sensors
if distri.DISTRI_MAJOR<2010 then l.Add('liblm_sensors3-devel');
if distri.DISTRI_MAJOR<2010 then l.Add('liblm_sensors3-static-devel');
if distri.DISTRI_MAJOR>2009 then l.Add('liblm_sensors4-devel');
l.Add('lm_sensors');
l.Add('bzip2');
l.Add('arj');
L.Add('zip');
L.Add('unzip');
l.Add('htop');
l.Add('telnet');
l.Add('lsof');
//l.Add('dar');
//l.Add('preload');
fpsystem('/bin/rm -rf /tmp/packages.list');

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;



 result:=f;
end;
//#########################################################################################
function tmandrake.CheckPostfix():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('perl-Razor-Agent');
l.Add('pyzor');
l.add('sendmail-devel');

//Zarafa
l.add('libgsasl-devel');

//l.add('gd');
l.Add('wv');
l.Add('postfix');
l.add('postfix-ldap');
l.Add('clamav');
l.add('dspam');
//l.add('clamav-milter');
//l.add('dkim-milter');
//l.add('spamass-milter');
l.add('sendmail-devel');
//l.add('milter-greylist');
//l.add('mimedefang');
l.Add('spamassassin');
fpsystem('/bin/rm -rf /tmp/packages.list');

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;

if not FIleExists('/usr/bin/python') then f:=f + ',python';

 result:=f;
end;
//#########################################################################################
function tmandrake.CheckOpenVPN():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('pptp-linux');
l.Add('openvpn');
l.Add('bridge-utils');

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//#########################################################################################



function tmandrake.CheckCyrus():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('cyrus-imapd');
l.Add('cyrus-imapd-devel');
l.Add('cyrus-imapd-murder');
l.Add('cyrus-imapd-utils');



for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//#########################################################################################
function tmandrake.CheckZabbix():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.add('zabbix');
l.add('zabbix-web');
l.add('zabbix-agent');
fpsystem('/bin/rm -rf /tmp/packages.list');

for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;

if not FIleExists('/usr/bin/python') then f:=f + ',python';

 result:=f;

end;
//#########################################################################################
function tmandrake.checkSamba():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
fpsystem('touch /etc/artica-postfix/samba.check.time');
l:=TstringList.Create;
l.Add('nss_ldap');
l.Add('samba-server');
l.Add('samba-client');
l.Add('pam_smb');
l.Add('nscd');
l.Add('nmap');
for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//#########################################################################################
function tmandrake.CheckPDNS():string;
var
   l:TstringList;
   f:string;
   i:integer;
begin
f:='';
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
function tmandrake.CheckDevcollectd():string;
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



function tmandrake.checkSQuid():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('squid');
l.Add('awstats');
l.add('sarg');
l.add('e2fsprogs-devel');
l.add('pam-devel');
l.add('libclamav-devel');
l.add('dansguardian');
l.add('clamd');
l.add('libcap2');
//l.add('libcap2-dev');


for i:=0 to l.Count-1 do begin
     if not libs.RPM_is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//########################################################################################
function tmandrake.checkApps(l:tstringlist):string;
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

//##############################################################################
function tmandrake.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
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
function tmandrake.mandrake_release():string;
var
Filedatas:TstringList;
RegExpr:tRegExpr;
begin
  RegExpr:=TRegExpr.Create;
  if FileExists('/etc/redhat-release') then begin
     Filedatas:=TstringList.Create;
     Filedatas.LoadFromFile('/etc/redhat-release');
      RegExpr.Expression:='Mandriva Linux release\s+([0-9\.]+)';
      if RegExpr.Exec(Filedatas.Strings[0]) then begin
         result:=RegExpr.Match[1];
         RegExpr.Free;
         Filedatas.Free;
         exit();
      end;
  end;
  
end;
//#########################################################################################

function tmandrake.URPMI2008():boolean;
var u:string;

begin
fpsystem('urpmi.addmedia main ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2008.0/i586/media/main/release with media_info/hdlist.cz');
fpsystem('urpmi.addmedia --update main_updates ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2008.0/i586/media/main/updates with media_info/hdlist.cz');
fpsystem('urpmi.addmedia main_backports ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2008.0/i586/media/main/backports with media_info/hdlist.cz');
fpsystem('urpmi.addmedia contrib ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2008.0/i586/media/contrib/release with media_info/hdlist.cz');
fpsystem('urpmi.addmedia --update contrib_updates ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2008.0/i586/media/contrib/updates with media_info/hdlist.cz');
end;
//#########################################################################################



end.
