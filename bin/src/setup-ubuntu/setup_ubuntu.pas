program setup_ubuntu;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils
  { you can add units after this }, setup_ubuntu_class, distriDetect,
  setup_suse_class, setup_fedora_class, setup_centos_class,
  setup_mandrake_class, setup_libs, setup_archlinux_class, unix;

  var
     install:tubuntu;
     suse:tsuse;
     fedora:tfedora;
     centos:tcentos;
     mandrake:tmandrake;
     archlinux:tarchlinux;
     distri:tdistriDetect;
     libs:tlibs;
begin
     distri:=tdistriDetect.Create;
     libs:=tlibs.Create;
     try
        libs.EXPORT_PATH();
     except
     writeln('ERROR while exporting PATH');
     end;

     ForceDirectories('/etc/artica-postfix/settings/Daemons');
     fpsystem('/bin/echo "'+distri.DISTRINAME_CODE+'" >/etc/artica-postfix/settings/Daemons/LinuxDistributionCodeName');
     fpsystem('/bin/echo "'+distri.DISTRINAME+'" >/etc/artica-postfix/settings/Daemons/LinuxDistributionFullName');
     if ParamStr(1)='--kill' then halt(0);


     if ParamStr(1)='--remove' then begin
        distri.removePackage(ParamStr(2));
        halt(0);
     end;

     if ParamStr(1)='--distri' then begin
            writeln('CODE:'+distri.DISTRINAME_CODE);
            writeln('NAME:'+distri.DISTRINAME);
            writeln('VERSION:'+distri.DISTRINAME_VERSION);
            writeln('MAJOR:',distri.DISTRI_MAJOR);
            writeln('MINOR:',distri.DISTRI_MINOR);
            fpsystem('/bin/echo "'+distri.DISTRINAME_CODE+'" >/etc/artica-postfix/settings/Daemons/LinuxDistributionCodeName');
            fpsystem('/bin/echo "'+distri.DISTRINAME+'" >/etc/artica-postfix/settings/Daemons/LinuxDistributionFullName');
            halt(0);
     end;

     if ParamStr(1)='--help' then begin
        writeln('You can by pass some package using');
        writeln('--without-podlators : bypass podlators-perl');
        writeln('--without-lvm       : bypass LVM packages');
        halt(0);
     end;


      if ParamStr(1)='--check-base-php' then begin

          writeln('check-base-php Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE +' Kernel version: '+libs.KERNEL_VERSION() );

         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckBasePHP());
            halt(0);
         end;
         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckBasePHP());
            halt(0);
         end;
         if distri.DISTRINAME_CODE='SUSE' then begin
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.CheckBasePHP());
            halt(0);
         end;
         if distri.DISTRINAME_CODE='CENTOS' then begin
            centos:=tcentos.Create;
            centos.InstallPackageListsSilent(centos.CheckBasePHP());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
            fedora:=tfedora.Create();
            fedora.InstallPackageListsSilent(fedora.CheckBasePHP());
            halt(0);
         end;
      end;





     if ParamStr(1)='--check-base-system' then begin
         writeln('check-base-system Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckBaseSystem());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            fpsystem('/usr/sbin/php5 /usr/share/artica-postfix/exec.apt-get.php --sources-list >/dev/null 2>&1');
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckBaseSystem());
            halt(0);
         end;




         if distri.DISTRINAME_CODE='SUSE' then begin
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.CheckBaseSystem());
            halt(0);
         end;



         if distri.DISTRINAME_CODE='FEDORA' then begin
           fedora:=tfedora.Create();
           fedora.InstallPackageListsSilent(fedora.CheckBaseSystem());
           halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            writeln('check-base-system Starting CENTOS...');
            centos:=tcentos.Create;
            centos.DennouRuby();
            centos.InstallPackageListsSilent(centos.CheckBaseSystem());
            writeln('check-base-system on CENTOS done...');
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
          mandrake:=tmandrake.Create;
          mandrake.InstallPackageListsSilent(mandrake.CheckBaseSystem());
          halt(0);
         end;

         writeln('check-base-system Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;


     if ParamStr(1)='--check-fuppes' then begin
         writeln('check-postfix Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckFuppes());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckFuppes());
            halt(0);
         end;

         writeln('check-base-system Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;


     if ParamStr(1)='--check-postfix' then begin
         writeln('check-postfix Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );

         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckPostfix());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            fpsystem('/usr/sbin/php5 /usr/share/artica-postfix/exec.apt-get.php --sources-list >/dev/null 2>&1');
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckPostfix());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='SUSE' then begin
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.CheckPostfix());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
           fedora:=tfedora.Create();
           fedora.InstallPackageListsSilent(fedora.CheckPostfix());
           halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            centos:=tcentos.Create;
            centos.InstallPackageListsSilent(centos.CheckPostfix());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
          mandrake:=tmandrake.Create;
          mandrake.InstallPackageListsSilent(mandrake.CheckPostfix());
          halt(0);
         end;

         writeln('check-base-system Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;




     if ParamStr(1)='--check-samba' then begin
         writeln('check-samba Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.checkSamba());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            fpsystem('/usr/sbin/php5 /usr/share/artica-postfix/exec.apt-get.php --sources-list >/dev/null 2>&1');
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.checkSamba());
            halt(0);
         end;




         if distri.DISTRINAME_CODE='SUSE' then begin
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.checkSamba());
            halt(0);
         end;



         if distri.DISTRINAME_CODE='FEDORA' then begin
           fedora:=tfedora.Create();
           fedora.InstallPackageListsSilent(fedora.checkSamba());
           halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            centos:=tcentos.Create;
            centos.InstallPackageListsSilent(centos.checkSamba());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
          mandrake:=tmandrake.Create;
          mandrake.InstallPackageListsSilent(mandrake.checkSamba());
          halt(0);
         end;

         writeln('check-samba Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;

     if ParamStr(1)='--check-squid' then begin
         writeln('check-squid Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            fpsystem('/usr/sbin/php5 /usr/share/artica-postfix/exec.apt-get.php --sources-list >/dev/null 2>&1');
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='SUSE' then begin
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
           fedora:=tfedora.Create();
           fedora.InstallPackageListsSilent(fedora.checkSQuid());
           halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            centos:=tcentos.Create;
            centos.InstallPackageListsSilent(centos.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
          mandrake:=tmandrake.Create;
          mandrake.InstallPackageListsSilent(mandrake.checkSQuid());
          halt(0);
         end;

         writeln('check-samba Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;


     if ParamStr(1)='--check-pdns' then begin
         writeln('check-pdns Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckPDNS());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            fpsystem('/usr/sbin/php5 /usr/share/artica-postfix/exec.apt-get.php --sources-list >/dev/null 2>&1');
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckPDNS());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='SUSE' then begin
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.CheckPDNS());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
           fedora:=tfedora.Create();
           fedora.InstallPackageListsSilent(fedora.CheckPDNS());
           halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            centos:=tcentos.Create;
            centos.InstallPackageListsSilent(centos.CheckPDNS());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
          mandrake:=tmandrake.Create;
          mandrake.InstallPackageListsSilent(mandrake.CheckPDNS());
          halt(0);
         end;

         writeln('check-pdns Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;



     if ParamStr(1)='--check-squid' then begin
         writeln('check-squid Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='SUSE' then begin
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
           fedora:=tfedora.Create();
           fedora.InstallPackageListsSilent(fedora.checkSQuid());
           halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            centos:=tcentos.Create;
            centos.InstallPackageListsSilent(centos.checkSQuid());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
          mandrake:=tmandrake.Create;
          mandrake.InstallPackageListsSilent(mandrake.checkSQuid());
          halt(0);
         end;

         writeln('check-squid Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;

     if ParamStr(1)='--check-virtualbox' then begin
         writeln('check-squid Starting checking base system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallVirtualBox();
            fpsystem('/usr/share/artica-postfix/bin/process1 --force --debug --true-vdi');
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            install:=tubuntu.Create;
            install.InstallVirtualBox();
            fpsystem('/usr/share/artica-postfix/bin/process1 --force --debug --true-vdi');
            halt(0);
         end;

         if distri.DISTRINAME_CODE='SUSE' then begin
            writeln('check-virtualbox Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
            writeln('check-virtualbox Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            writeln('check-virtualbox Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
            writeln('check-virtualbox Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         writeln('check-virtualbox Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;


     if ParamStr(1)='--check-openvpn' then begin
         writeln('check-squid Starting checking openvpn system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckOpenVPN());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckOpenVPN());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='SUSE' then begin
            writeln('check-openvpn is supported by  --check-base-system: ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.CheckBaseSystem());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
            writeln('check-openvpn Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin
            centos:=tcentos.Create;
            centos.InstallPackageListsSilent(centos.CheckOpenVPN());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
            writeln('check-openvpn Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         writeln('check-openvpn Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;

     if ParamStr(1)='--check-amanda' then begin
         writeln('check-squid Starting checking openvpn system on ' +distri.DISTRINAME + ' ' + distri.DISTRINAME_VERSION+' CODE=' +distri.DISTRINAME_CODE );
         if distri.DISTRINAME_CODE='UBUNTU' then begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckAmanda());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='DEBIAN' then  begin
            install:=tubuntu.Create;
            install.InstallPackageListssilent(install.CheckAmanda());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='SUSE' then begin
            writeln('check-amanda is supported by  --check-base-system: ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            suse:=tsuse.Create();
            suse.InstallPackageListsSilent(suse.CheckBaseSystem());
            halt(0);
         end;

         if distri.DISTRINAME_CODE='FEDORA' then begin
            writeln('check-amanda Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         if distri.DISTRINAME_CODE='CENTOS' then begin

            halt(0);
         end;

         if distri.DISTRINAME_CODE='MANDRAKE' then begin
            writeln('check-openvpn Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
            halt(0);
         end;

         writeln('check-amanda Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
         halt(0);
     end;




if length(paramstr(1))>0 then begin
     if paramstr(1)<>'--verbose' then begin
        writeln('Unable to understand ',paramstr(1));
        halt(0);
     end;
end;
writeln('initialize... ');
writeln('Detected:',distri.DISTRINAME_CODE+' "' + distri.DISTRINAME_VERSION+'" Major version:', distri.DISTRI_MAJOR,' Minor:', distri.DISTRI_MINOR,' Arch:',libs.ArchStruct(),'bits kernel: "'+libs.KERNEL_VERSION()+'"');

if FileExists('/etc/init.d/zimbra') then begin
   writeln('It seems that Zimbra is installed on this computer.');
   writeln('Artica did not support server with Zimbra installed');
   writeln('Choose a fresh server instead');
   halt(0);
end;

     if distri.DISTRINAME_CODE='UBUNTU' then begin
        install:=tubuntu.Create;

        install.Show_Welcome();
        halt(0);
     end;

     if distri.DISTRINAME_CODE='DEBIAN' then begin
        install:=tubuntu.Create;
        install.Show_Welcome();
        halt(0);
     end;


    if distri.DISTRINAME_CODE='SUSE' then begin
        suse:=tsuse.Create();
        suse.Show_Welcome();
        halt(0);
     end;
     
     if distri.DISTRINAME_CODE='FEDORA' then begin
        fedora:=tfedora.Create;
        fedora.Show_Welcome();
        halt(0);
     end;
     
     if distri.DISTRINAME_CODE='CENTOS' then begin
        centos:=tcentos.Create;
        centos.Show_Welcome();
        halt(0);
     end;
     
     if distri.DISTRINAME_CODE='MANDRAKE' then begin
        mandrake:=tmandrake.Create;
        mandrake.Show_Welcome();
        halt(0);
     end;

     if distri.DISTRINAME_CODE='ARCHLINUX' then begin
        archlinux:=tarchlinux.Create;
        archlinux.Show_Welcome();
        halt(0);
     end;





     
    writeln('Not supported ' + distri.DISTRINAME+ '/' + distri.DISTRINAME_CODE);
end.

