unit setup_archlinux_class;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',unix,IniFiles,setup_libs,distridetect;
type
  TStringDynArray = array of string;
  type
  tarchlinux=class


private
       libs:tlibs;
       function is_application_installed(appname:string):boolean;
       function InstallPackageLists(list:string):boolean;
       function CheckCyrus():string;
       function CheckAmavisPerl():string;
       function Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;






public
      constructor Create();
      procedure Free;
      procedure Show_Welcome;
      function CheckDevcollectd():string;
      function checkSamba():string;
      function InstallPackageListssilent(list:string):boolean;
      function checkApps(l:tstringlist):string;
      function CheckBaseSystem():string;
      function CheckPostfix():string;
      function checkSQuid():string;
END;

implementation

constructor tarchlinux.Create();
begin

libs:=tlibs.Create;
   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');


     if length(Paramstr(1))>0 then begin
         writeln('you can use --silent in order to install packages without human intercation');
     end;
end;
//#########################################################################################
procedure tarchlinux.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure tarchlinux.Show_Welcome;
var
   base,postfix,u,cyrus,samba,squid:string;
begin
    if not FileExists('/usr/bin/pacman') then begin
      writeln('Your system does not store pacman utils, this program must be closed...');
      exit;
    end;

    fpsystem('/usr/bin/pacman -Syu');

    writeln('Checking.............: Base system...');
    base:=CheckBaseSystem();
    writeln('Checking.............: Postfix system...');
    postfix:=CheckPostfix();
    writeln('Checking.............: Cyrus system...');
    cyrus:=CheckCyrus();
    writeln('Checking.............: Files Sharing system...');
    samba:=checkSamba();
    writeln('Checking.............: Squid proxy and securities...');
    squid:=checkSQuid();

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
       InstallPackageLists(postfix+' '+cyrus+' '+samba+' '+squid);
       fpsystem('/usr/bin/pacman -Syu');
       libs.InstallArtica();
          fpsystem('/usr/share/artica-postfix/bin/artica-make APP_ROUNDCUBE3');
          fpsystem('/etc/init.d/artica-postfix restart postfix');
       Show_Welcome;
       exit;
    end;

    if u='1' then begin
              fpsystem('/usr/bin/pacman -Syu');
          InstallPackageLists(postfix);
          if FileExists('/etc/init.d/artica-postfix') then fpsystem('/etc/init.d/artica-postfix restart postfix');
          Show_Welcome;
          exit;
    end;


    if u='2' then begin
          fpsystem('/usr/bin/pacman -Syu');
          InstallPackageLists(cyrus);
          if FileExists('/etc/init.d/artica-postfix') then fpsystem('/etc/init.d/artica-postfix restart imap');
          fpsystem('/usr/share/artica-postfix/bin/artica-roundcube --install --verbose');
          if FileExists('/etc/init.d/artica-postfix') then fpsystem('/etc/init.d/artica-postfix restart');
          Show_Welcome;
          exit;
    end;

    if u='3' then begin
          fpsystem('/usr/bin/pacman -Syu');
          InstallPackageLists(samba);
          if FileExists('/etc/init.d/artica-postfix') then fpsystem('/etc/init.d/artica-postfix restart samba');
          Show_Welcome;
          exit;
    end;

    if u='4' then begin
          InstallPackageLists(squid);
          if FileExists('/etc/init.d/artica-postfix') then fpsystem('/etc/init.d/artica-postfix restart squid');
          Show_Welcome;
          exit;
    end;

    if u='5' then begin
          libs.InstallArtica();
          Show_Welcome;
          exit;
    end;


    if lowercase(u)='c' then begin
          fpsystem('/usr/share/artica-postfix/bin/artica-make APP_AMAVISD_MILTER');
          Show_Welcome;
          exit;
    end;


end;
//#########################################################################################
function tarchlinux.InstallPackageLists(list:string):boolean;
var
   cmd:string;
   u  :string;
   i  :integer;
   ll :TStringDynArray;
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
   list:=AnsiReplaceText(list,',' ,' ');
   cmd:='/usr/bin/pacman -S -q --noconfirm '+list;
   writeln(cmd);
   fpsystem(cmd);
   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   exit;

   ll:=Explode(',',list);
   for i:=0 to length(ll)-1 do begin
       if length(trim(ll[i]))>0 then begin
          cmd:='/usr/bin/pacman -S -q --noconfirm ' + ll[i];
          fpsystem(cmd);
       end;
   end;



   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   result:=true;


end;
//#########################################################################################
function tarchlinux.InstallPackageListssilent(list:string):boolean;
var
   cmd:string;
   u  :string;
   i  :integer;
   ll :TStringDynArray;
begin
if length(trim(list))=0 then exit;
result:=false;

   list:=AnsiReplaceText(list,',' ,' ');

   cmd:='/usr/bin/pacman -S -q --noconfirm '+list;
   writeln(cmd);
   fpsystem(cmd);
   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   exit;

   ll:=Explode(',',list);
   for i:=0 to length(ll)-1 do begin
       if length(trim(ll[i]))>0 then begin
          cmd:='/usr/bin/pacman -S -q --noconfirm ' + ll[i];
          fpsystem(cmd);
       end;
   end;



   if FileExists('/tmp/packages.list') then fpsystem('/bin/rm -f /tmp/packages.list');
   result:=true;


end;
//#########################################################################################
function tarchlinux.is_application_installed(appname:string):boolean;
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
begin
    D:=false;
    result:=false;
    appname:=trim(appname);
    D:=libs.COMMANDLINE_PARAMETERS('--verbose');
    if not FileExists('/tmp/packages.list') then begin
       fpsystem('/usr/bin/pacman -qQ >/tmp/packages.list');
    end;



    l:=TstringList.Create;

    try
       l.LoadFromFile('/tmp/packages.list');
    except
       writeln('is_application_installed(',appname,') fatal error on /tmp/packages.list');
       is_application_installed(appname);
       exit;
    end;


    if l.Count<10 then begin
       fpsystem('/bin/rm -rf /tmp/packages.list');
       result:=is_application_installed(appname);
       exit;
    end;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^(.+?)\s+';

     if D then writeln('Search ',appname,' in ',l.Count,' packages list');

    for i:=0 to l.Count-1 do begin
           if lowercase(trim(l.Strings[i]))=trim(lowercase(appname)) then begin
           result:=true;
           break;
           end;
    end;


    if D then writeln('Search ',l.Strings[i],' failed');
    l.free;
    RegExpr.free;

end;

//#########################################################################################
function tarchlinux.CheckBaseSystem():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;
   UbuntuIntVer:integer;


begin
f:='';
UbuntuIntVer:=9;
l:=TstringList.Create;
distri:=tdistriDetect.Create();

l.Add('hal');
l.Add('dhcp');
l.Add('dcron');
l.Add('file');
l.Add('hdparm');
l.Add('less');
l.Add('nfs-utils');
l.Add('rsync');
l.Add('openssh');
l.Add('strace');
l.add('mtools');
l.add('re2c');
l.add('bridge-utils');
l.add('inetutils');
l.add('sudo');
l.add('iproute2');
l.add('curl');
l.add('bind');
l.add('lm_sensors');
l.add('hddtemp');


l.Add('gcc');
l.Add('make');
l.Add('flex');
l.Add('tcsh');
l.Add('time');
l.Add('eject');
l.Add('pciutils ');
l.Add('usbutils');
l.Add('openldap');
l.Add('openldap-clients');
l.Add('openssl ');
l.Add('strace');
l.Add('tcsh');
l.Add('time');
l.Add('eject');
l.Add('pciutils');
l.Add('usbutils');
L.add('sysstat');
l.add('bzip2');
l.add('gdbm');
l.add('libpng');
l.add('libjpeg');
l.add('libmcrypt');
l.add('libtool');
l.Add('libmysqlclient');
l.add('net-snmp');
l.add('mhash');
l.add('aspell');
l.add('openssl');
l.add('tidyhtml');
l.add('aspell');
l.add('unixodbc');
l.add('libxslt');
l.add('mhash');
l.add('gmp');
l.add('libldap');
l.Add('mysql');
l.Add('rrdtool');
l.add('apache');
l.add('geoip');
l.add('lighttpd');
l.add('libsasl');
l.add('cyrus-sasl-plugins');
l.add('perl-authen-sasl');
l.add('perl-term-readkey');
l.add('perl-dbd-mysql');
l.add('perl-libwww');
l.Add('sudo');
l.Add('ntp');

l.add('unrar');
l.add('arj');
l.add('zip');
l.add('preload');
l.add('re2c');

l.Add('dhclient');
l.Add('dhcp');
l.add('mtools');
l.add('dar');
l.add('rsync');
l.add('stunnel');
l.Add('curl');
l.Add('hddtemp');
l.Add('bzip2');
l.add('unzip');
l.Add('arj');
l.Add('htop');
l.Add('lsof');
fpsystem('/bin/rm -rf /tmp/packages.list');

for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
end;
//#########################################################################################
function tarchlinux.CheckPostfix():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;
begin
f:='';
l:=TstringList.Create;
distri:=tdistriDetect.Create();
l.Add('razor');
//l.Add('pyzor');
//l.Add('queuegraph');
//l.Add('mailgraph');
l.Add('perl-io-socket-ssl');
l.Add('perl-crypt-ssleay');
l.Add('libytnef');
l.Add('perl-html-parser');
l.Add('perl-archive-zip');
l.Add('font-bh-ttf');
l.add('postfix');
l.Add('spamassassin');
l.Add('mailman');
l.add('wv');
//l.add('libmilter-dev');

fpsystem('/bin/rm -rf /tmp/packages.list');

for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
end;
//#########################################################################################

function tarchlinux.CheckAmavisPerl():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;

begin
f:='';
distri:=tdistriDetect.Create();
l:=TstringList.Create;
l.Add('libcrypt-openssl-rsa-perl');
l.Add('libmail-dkim-perl');

for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;

InstallPackageListssilent(f);
l.free;

end;
//#########################################################################################
function tarchlinux.CheckCyrus():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;

begin
f:='';
distri:=tdistriDetect.Create();
l:=TstringList.Create;
l.Add('cyrus-imapd-2.2');
l.Add('cyrus-admin-2.2');
l.Add('sasl2-bin');
l.Add('cyrus-pop3d-2.2');
l.Add('cyrus-murder-2.2');
if distri.DISTRINAME_CODE='UBUNTU' then begin
   l.Add('fdm');
end;

for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//#########################################################################################
function tarchlinux.checkSamba():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('libnss-ldap');
l.Add('samba');
l.Add('smbldap-tools');
l.Add('smbclient ');
l.Add('libpam-ldap');
l.Add('libpam-smbpass');
l.Add('nscd');
l.Add('nmap');
l.add('libcups2-dev');
l.add('libcupsimage2-dev');
l.add('cups-driver-gutenprint');
l.add('foomatic-db-gutenprint');
l.add('libgtk2.0-dev');
l.add('libtiff4-dev');
l.add('libjpeg62-dev');
l.add('libpam0g-dev');
l.add('uuid-dev');
l.add('smbfs');
for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//#########################################################################################
function tarchlinux.CheckDevcollectd():string;
var
   l:TstringList;
   f:string;
   i:integer;
   distri:tdistriDetect;

begin
f:='';
distri:=tdistriDetect.Create;
l:=TstringList.Create;
l.Add('iproute-dev');
l.Add('xfslibs-dev');
l.Add('librrd2-dev');
l.Add('libsensors-dev');
l.Add('libmysqlclient15-dev');
if distri.DISTRINAME_CODE='UBUNTU' then l.Add('libperl-dev') else l.Add('libperl5.8');
l.add('libesmtp-dev');
l.add('libnotify-dev');
l.add('libxml2-dev');
l.add('libpcap-dev');
l.add('hddtemp');
l.add('mbmon');
l.add('libconfig-general-perl');
l.Add('memcached');
L.add('libcurl4-openssl-dev');
for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;


//#########################################################################################
function tarchlinux.checkSQuid():string;
var
   l:TstringList;
   f:string;
   i:integer;

begin
f:='';
l:=TstringList.Create;
l.Add('squid3');
l.Add('squidclient');
l.Add('dansguardian');
l.add('sarg');
for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;

//#########################################################################################
function tarchlinux.checkApps(l:tstringlist):string;
var
   f:string;
   i:integer;

begin
f:='';
for i:=0 to l.Count-1 do begin
     if not is_application_installed(l.Strings[i]) then begin
          f:=f + ',' + l.Strings[i];
     end;
end;
 result:=f;
 l.free;
end;
//########################################################################################
function tarchlinux.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
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
end.
