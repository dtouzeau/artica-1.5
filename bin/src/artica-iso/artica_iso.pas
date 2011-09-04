program artica_iso;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,debian_class,unix
  { you can add units after this };

  var deb:tdebian;
  var interfaces:Tstringlist;
begin



if not FileExists('/etc/artica-postfix/FROM_ISO') then halt(0);
deb:=tdebian.Create;
deb.ARTICA_CD_SOURCES_LIST();
deb.remove_bip();
deb.linuxlogo();

if FIleExists('/etc/artica-postfix/ARTICA_ISO.lock') then begin
   if deb.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/ARTICA_ISO.lock') < 5 then halt(0);
end;

fpsystem('/bin/touch /etc/artica-postfix/ARTICA_ISO.lock');

if FileExists('/etc/artica-postfix/artica-iso-first-reboot') then begin
       if not FileExists('/etc/artica-postfix/artica-iso-make-launched') then begin
          fpsystem('/bin/touch /etc/artica-postfix/artica-iso-setup-launched');
          //fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-base-system >/dev/null');
       end;
end;


if FileExists('/etc/artica-postfix/KASPER_INSTALL') then begin
     fpsystem('/usr/share/artica-postfix/bin/artica-make APP_KAS3');
     fpsystem('/usr/share/artica-postfix/bin/artica-make APP_KAVMILTER');
     fpsystem('/bin/rm -f /etc/artica-postfix/KASPER_INSTALL');
end;

       if FileExists('/home/artica/packages/kav4proxy-5.5-62.tar.gz') then begin
          fpsystem('/usr/share/artica-postfix/bin/artica-make APP_KAV4PROXY');
       end;

 // TextColor(White);
 // TextBackground(Black);

if not FileExists('/etc/artica-postfix/artica-iso-first-reboot') then begin

    writeln('Checking sources.list...');
    fpsystem('/usr/bin/php5 /usr/share/artica-postfix/exec.apt-get.php --sources-list');


    writeln('Moving old login binary...');
    if fileExists('/usr/share/artica-postfix/bin/artica-logon') then begin
        fpsystem('/bin/mv /bin/login /bin/login.old');
        fpsystem('dpkg-divert --divert /bin/login.old /bin/login');
        fpsystem('/bin/mv /usr/share/artica-postfix/bin/artica-logon /bin/login');
        fpsystem('/bin/chmod 777 /bin/login');
    end else begin
       writeln('/usr/share/artica-postfix/bin/artica-logon no such file !!');
    end;

    if FileExists('/home/artica/packages/ZARAFA/zarafa.tar') then begin
       writeln('artica-cd... Installing zarafa');
       fpsystem('/bin/tar -xf /home/artica/packages/ZARAFA/zarafa.tar -C /');
       fpsystem('/etc/init.d/artica-postfix restart zarafa');
       fpsystem('/bin/rm -rf /home/artica/ZARAFA');
    end else begin
        writeln('artica-cd... no zarafa package');
    end;



    interfaces:=Tstringlist.Create;
    interfaces.Add('auto lo');
    interfaces.Add('iface lo inet loopback');
    interfaces.Add('# The primary network interface');
    interfaces.Add('iface eth0 inet dhcp');
    interfaces.Add('');
    try
        interfaces.SaveToFile('/etc/network/interfaces');
    finally
    end;

    if FileExists('/etc/init.d/lighttpd') then begin
         writeln('artica-cd... removing lighttpd original instance...');
         fpsystem('/etc/init.d/lighttpd stop');
         fpsystem('update-rc.d -f lighttpd remove');
         fpsystem('/bin/mv -f /etc/lighttpd/lighttpd.conf /etc/lighttpd/lighttpd.conf.org');
         fpsystem('/bin/touch  /etc/lighttpd/init.d');
         fpsystem('dpkg-divert --divert /etc/lighttpd/lighttpd.conf.org /etc/lighttpd/lighttpd.conf');
         fpsystem('dpkg-divert --divert /etc/lighttpd/init.d /etc/init.d/lighttpd');
         writeln('artica-cd... removing lighttpd original instance done...');
    end else begin
         writeln('artica-cd... /etc/init.d/lighttpd no such file..');
    end;



    writeln('artica-cd... Creating Artica configuration by process1, please wait...');
    fpsystem('/usr/share/artica-postfix/bin/process1 --force --yes-from-iso');
    writeln('artica-cd... remove init boot');
    fpsystem('update-rc.d -f artica-cd remove');
    fpsystem('/bin/rm -f /etc/init.d/artica-cd');
    fpsystem('/bin/rm -f /etc/cron.d/artica-boot-first >/dev/null 2>&1');
    fpsystem('/bin/rm -f /etc/artica-postfix/ARTICA_ISO.lock');
    fpsystem('/bin/touch /etc/artica-postfix/artica-iso-first-reboot');
    writeln('artica-cd... system will reboot....');
    fpsystem('reboot');
end else begin
     if FileExists('/etc/init.d/artica-cd') then begin
        fpsystem('update-rc.d -f artica-cd remove');
        fpsystem('/bin/rm -f /etc/init.d/artica-cd');
    end;

end;

halt(0);


end.

