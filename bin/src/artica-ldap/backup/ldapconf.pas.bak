unit ldapconf;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, articaldap,SysUtils,strutils,Process,unix,global_conf,Logs,RegExpr in 'RegExpr.pas',IniFiles,BaseUnix,
zsystem,cyrus,clamav,spamass,pureftpd,openldap,ntpd,samba,mimedefang,squid,stunnel4,dkimfilter,postfix_class,miltergreylist,lighttpd,jcheckmail,kav4samba,bind9,obm,artica_cron,dotclear,
mailarchiver in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mailarchiver.pas',
kav4proxy    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kav4proxy.pas',
kas3         in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kas3.pas',
mysql_daemon in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mysql_daemon.pas',
bogom,
kavmilter,
mailspy_milter   in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/mailspy_milter.pas',
amavisd_milter,
imapsync         in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/imapsync.pas',
mailmanctl;


  type
  tldapconf=class


private
       GLOBAL_INI:myconf;
       cyr:Tcyrus;
       clamav:Tclamav;
       mldap:Topenldap;
       LDAP:Tarticaldap;
       zntpd:tntpd;
       LOGS:Tlogs;
       SYS:Tsystem;
       stunnel:Tstunnel;
       zpostfix:tpostfix;
       miltergreylist:tmilter_greylist;
       lighttpd:Tlighttpd;

       bind9:Tbind9;
       zobm:Tobm;
       zcyrus:Tcyrus;
       www_user:string;

       PROCEDURE mailboxes_log(user:string;tmpfile:string);
       function  bind_import_file(FileTable:TstringList;Zone:string):string;
       // Kaspersky Anti-spam
       function       KasperskyASGroupsParseFile(datas:string):string;
       procedure      KasperskyAsBuildProfile();
       function       KasperskyAsParseProfile(key:string;path:string):string;
       procedure      KasperskyASGroupDeleteFiles();
       procedure      KasperskyASParseError();
       PROCEDURE      notifications_apply();
       function       mailboxes_acl(ini_content:string):string;


       //milter-greylist



public
      constructor Create();
      procedure Free;

      PROCEDURE      Squid(servername:string);
      PROCEDURE      FtpUsers();
      PROCEDURE      SqlGrey(servername:string);
      PROCEDURE      maincf();
      PROCEDURE      crossroads_sync();
      PROCEDURE      crossroads_apply(path:string);
      PROCEDURE      maintenance();
      PROCEDURE      Amavis();
      PROCEDURE      mailboxes_sync();
      PROCEDURE      mailbox_sync(user:string);
      PROCEDURE      milter_greylist();
      PROCEDURE      Enable_postfixmodules();
      PROCEDURE      OBM_OPERATIONS();
      PROCEDURE      iptables();
      PROCEDURE      backup_apply();
      PROCEDURE      SharedFolders();
      procedure      SpamAssassin();
      PROCEDURE      mimedefang();
      procedure      cyrusconfig();
      procedure      cyrrepair();
      // Kaspersky Anti-spam
      PROCEDURE      KasperskyASGroups();
      
      
      //kavmilter

      PROCEDURE      localdomains();
      PROCEDURE      pnetworks();
      PROCEDURE      dkimfilter();
      procedure      NMAP();
      procedure      NMAP_scan_results(path:string);
      procedure      NMAP_SINGLE(uid:string);
      PROCEDURE      Reconfigure_lighttpd();
      procedure      kav4samba_save();
      PROCEDURE      bind_import();
      PROCEDURE      Bind_Compile();
      PROCEDURE      fdm_exec();
      PROCEDURE      Testldap_cmdline();
      procedure      cyrreconstruct();
      procedure      dtoclear_users();
      PROCEDURE      jckmail();
      
      //export mailboxes.
      PROCEDURE      imapsync_export(user_from:string;user_to:string;delete:string);
      PROCEDURE      imapsync_import(user:string);
      PROCEDURE      sa_learn();
      FUNCTION       UNDERSTAND_fetchmailrc():TstringList;
      procedure      ldap_search_command_line(pattern:string);
      
END;

implementation

constructor tldapconf.Create();
begin
  GLOBAL_INI:=myconf.Create;
  SYS:=GLOBAL_INI.SYS;
  ldap:=tarticaldap.Create();
  LOGS:=Tlogs.Create;
  Cyr:=Tcyrus.Create(SYS);
  clamav:=Tclamav.Create;
  mldap:=Topenldap.Create;
  zntpd:=tntpd.Create;

  stunnel:=Tstunnel.Create(SYS);
  zpostfix:=Tpostfix.Create(SYS);
  miltergreylist:=Tmilter_greylist.Create(SYS);
  lighttpd:=Tlighttpd.Create(SYS);

  bind9:=Tbind9.Create(SYS);
  zobm:=Tobm.Create(SYS);
  www_user:=lighttpd.LIGHTTPD_GET_USER();
end;
//#############################################################################
PROCEDURE tldapconf.Free();
begin
  ldap.free;
  cyr.FRee;
end;
//#############################################################################
procedure tldapconf.ldap_search_command_line(pattern:string);
var
   ldap:topenldap;
   cmd:string;
begin
   ldap:=topenldap.CReate();

   cmd:='ldapsearch -b '+ ldap.ldap_settings.suffix+' -D cn='+ldap.ldap_settings.admin+','+ldap.ldap_settings.suffix;
   cmd:=cmd+' -w ' + ldap.ldap_settings.password +' -p '+ldap.ldap_settings.Port+' -h '+ldap.ldap_settings.servername;
   cmd:=cmd+' "'+pattern+'"';
  // writeln(cmd);
   fpsystem(cmd);
end;
//#############################################################################


PROCEDURE tldapconf.Squid(servername:string);
 var
    FileS:TstringList;
    squid:Tsquid;
begin
  squid:=Tsquid.Create;
  if not FileExists(squid.SQUID_CONFIG_PATH()) then begin
     writeln('Could not stat squid.conf');
     exit;
  end;
  Files:=TstringList.Create;
  FileS.Add(ldap.Load_squid_settings(servername));
  LOGS.logs('Squid:: save squid config file in ' + squid.SQUID_CONFIG_PATH());
  files.SaveToFile(squid.SQUID_CONFIG_PATH());
  LOGS.logs('Squid:: restart squid process');
  squid.SQUID_STOP();
  squid.SQUID_START();
  squid.free;
end;

//#############################################################################
PROCEDURE tldapconf.Testldap_cmdline();
 var
    SETTINGS:ActiveDirectoryServer;

begin

    SETTINGS.server:=ParamStr(2);
    SETTINGS.server_port:=ParamStr(3);
    SETTINGS.dn_admin:=ParamStr(4);
    SETTINGS.password:=ParamStr(5);
    SETTINGS.suffix:=ParamStr(6);;
    ldap.TestingADConnection(SETTINGS);
    

end;

//#############################################################################
PROCEDURE tldapconf.jckmail();
var
   xjckmail:tjcheckmail;
   jcheckMailPolicyTXT:string;
   jcheckMailConf:string;
   l:TstringList;
begin
  jcheckMailPolicyTXT:=SYS.GET_INFO('jcheckMailPolicyTXT');
  jcheckMailConf:=SYS.GET_INFO('jcheckMailConf');
  l:=TstringList.Create;
  xjckmail:=tjcheckmail.Create(SYS);
  if length(jcheckMailPolicyTXT)>0 then begin
     l.Add(jcheckMailPolicyTXT);
     l.SaveToFile('/var/jchkmail/cdb/j-policy.txt');
     SetCurrentDir('/var/jchkmail/cdb');
     logs.Debuglogs('jckmail()::Compiling jchkmail database');
     fpsystem('make');
  end;
  
  l.Clear;
  if length(jcheckMailConf)>0 then begin
     l.Add(jcheckMailConf);
     l.SaveToFile(xjckmail.CONFIG_PATH());
     logs.Debuglogs('jckmail()::Reloading jchkmail');
     fpsystem('kill -HUP '+xjckmail.PID_NUM());
  end;
  
   xjckmail.START();
   xjckmail.free;
end;
//#############################################################################
PROCEDURE tldapconf.Reconfigure_lighttpd();
var
   config:string;
begin
 if length(ParamStr(2))>0 then begin
  if FileExists(ParamStr(2)) then begin
      writeln('Reconfigure_lighttpd():: Loadind datas from ' + ParamStr(2));
      config:=logs.ReadFromFile(ParamStr(2));
  end else begin
      writeln('Reconfigure_lighttpd():: Loading default config ('+ParamStr(2)+') doesn''t exists');
      config:=lighttpd.DEFAULT_CONF();
  end;
  end;
  if length(config)=0 then begin
     writeln('Reconfigure_lighttpd():: no datas here...');
     exit;
  end;
  writeln('Reconfigure_lighttpd():: Saving configuration into LDAP DATABASE');
  ldap.lighttpd_modify_config(config);
  writeln('Reconfigure_lighttpd():: done');
  Enable_postfixmodules();
  writeln('Reconfigure_lighttpd():: Restarting server');
  lighttpd.LIGHTTPD_STOP();
  lighttpd.LIGHTTPD_START();
end;
//#############################################################################
PROCEDURE tldapconf.Bind_Compile();
var
   f                      :bind9_settings;
   RegExpr                :TRegExpr;
   l                      :TstringList;
   zones                  :TstringList;
   zone_name              :string;
   zone_file              :string;
   Zone_data              :TstringList;
   i:integer;
   PostfixEnabledInBind9:integer;
   bind:tbind9;
   conffile:string;
begin

  if not FileExists(bind9.bin_path()) then begin
       logs.Debuglogs('Starting......: Bind9 is not installed');
       exit;
  end;

  logs.Debuglogs('Bind_Compile:: Restarting smoothly bind9...');
  bind9.FIX_PERMISSIONS();
  bind9.ApplyForwarders('/etc/artica-postfix/settings/Daemons/PostfixBind9DNSList');
  bind9.ApplyLoopBack();
  logs.OutputCmd(bind9.rndc_path() +' reload');

end;
//#############################################################################
PROCEDURE tldapconf.fdm_exec();
var
   f               :FDM_settings;
   i               :integer;
   filename        :string;
   l               :TstringList;
begin

if SYS.GET_INFO('EnableFDMFetch')<>'1' then begin
   logs.Debuglogs('fdm_exec():: FDM is disabled');
   exit;
end;



f:=ldap.load_FDM_settings();
l:=TstringList.Create;
forceDirectories('/etc/fdm');
for i:=0 to f.FDMConf.Count-1 do begin
    l.Add(f.FDMConf.Strings[i]);
    filename:= '/etc/fdm/fdm.'+intTostr(i)+'.conf';
    logs.Debuglogs('fdm_exec():: saving '+filename);
    l.SaveToFile(filename);
    fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install --fdm-perform '+ filename + ' &');
    l.Clear;
end;
l.free;
end;



//#############################################################################
PROCEDURE tldapconf.bind_import();
var
   f                      :bind9_settings;
   RegExpr                :TRegExpr;
   l                      :TstringList;
   zones                  :TstringList;
   i                      :integer;
   force                  :boolean;
begin
  if not FileExists(bind9.bin_path()) then begin
       logs.Debuglogs('Starting......: Bind9 is not installed');
       exit;
  end;

  f:=ldap.load_bind9_settings();
  force:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--force');
  
  if not force then begin
     if length(f.NamedConf)>0 then exit;
  end;
  
  
  logs.Debuglogs('Starting......: Bind9 importing bind9 databases....');
  logs.Debuglogs('Starting......: Bind9 merging includes files....');
  
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^include "(.+?)"';
   l:=TstringList.Create;
   l.LoadFromFile('/etc/bind/named.conf');
   for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
            logs.Debuglogs('Starting......: Bind9 merging '+ RegExpr.Match[1]);
            l.Strings[i]:=logs.ReadFromFile(RegExpr.Match[1]);
        end;
   end;
   
   l.SaveToFile('/etc/bind/named-artica.import');
   l.Clear;
   zones:=TstringList.Create;
   l.LoadFromFile('/etc/bind/named-artica.import');
   logs.DeleteFile('/etc/bind/named-artica.import');
   RegExpr.Expression:='^zone\s+"(.+?)"';
   for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
             zones.Add(RegExpr.Match[1]+';'+bind_import_file(l,RegExpr.Match[1]));
        end;
   
   end;

  if not ldap.bind9_Create_master_branch(l.Text) then begin
      logs.Debuglogs('Starting......: Unable to create bind9 branch in ldap database...');
      exit;
  end;
     
  RegExpr.Expression:='(.+?);(.+)';
  for i:=0 to zones.Count-1 do begin
       RegExpr.Exec(zones.Strings[i]);
       if not ldap.bind9_Create_zone_branch(RegExpr.Match[1],RegExpr.Match[2]) then begin
          logs.Debuglogs('Starting......: Bind9 unable to store zone "'+ RegExpr.Match[1] +' (in file ' + RegExpr.Match[2]+')');
       end else begin
          logs.Debuglogs('Starting......: Bind9 success store content of "' + RegExpr.Match[1] + '" zone');
       end;
  end;
  
  
  

end;
//#############################################################################
function tldapconf.bind_import_file(FileTable:TstringList;Zone:string):string;
var
   RegExpr                :TRegExpr;
   start                  :boolean;
   i:integer;
begin
start:=false;
For i:=0 to FileTable.Count-1 do begin
      RegExpr:=TRegExpr.Create;
      if not start then begin
         RegExpr.Expression :='zone\s+"'+Zone;
         if RegExpr.Exec(FileTable.Strings[i]) then  start:=true;
      end;
      
      if start then begin
           RegExpr.Expression:='file "(.+?)"';
           if RegExpr.Exec(FileTable.Strings[i]) then begin
              result:=RegExpr.Match[1];
              break;
           end;
           RegExpr.Expression:='};';
           if RegExpr.Exec(FileTable.Strings[i]) then begin
              break;
           end;
      
      end;
end;

RegExpr.free;



end;
//#############################################################################
PROCEDURE tldapconf.maincf();
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Confd                  :postfix_settings;
   l                      :TstringList;
   template_bounce        :TstringList;
   restart                :boolean;
   LocalPostfixTimeCode   :string;
   header_check_path      :string;
   D                      :boolean;
   RegExpr                :TRegExpr;
   spamass                :Tspamass;
   dkim                   :Tdkim;
   html                   :boolean;
   mailarchive            :tmailarchive;
   bogom                  :tbogom;
   kas3                   :tkas3;
   kavmilter              :tkavmilter;
   mailspy                :tmailspy;
   cron                   :tcron;
   jcheckmail             :tjcheckmail;
   mailman                :tmailman;
   PostfixEnableMasterCfSSL:integer;
   PostfixEnableMasterCfSubmission:integer;
   EnableAmavisInMasterCF  :integer;
   PostfixEnabledInBind9  :integer;
   smtpd_tls_CAfile       :string;
   PostfixBounceTemplatesFileContent:string;
   PostfixMainCfFile:string;
   ByFlatFile:boolean;
   
begin
      PostfixEnableMasterCfSSL:=0;
      EnableAmavisInMasterCF:=0;
      PostfixEnableMasterCfSubmission:=0;

      if FIleExists('/etc/artica-postfix/settings/Daemons/PostfixMainCfFile') then begin
         logs.OutputCmd('/bin/cp /etc/artica-postfix/settings/Daemons/PostfixMainCfFile /etc/postfix/main.cf');
         logs.Syslogs('tldapconf.maincf():: reloading postfix');
         logs.OutputCmd('/usr/share/artica-postfix/bin/artica-install --postfix-reload');
         restart:=true;
      end;

         logs.OutputCmd('/bin/chmod 644 /etc/postfix/main.cf');
         logs.OutputCmd('/bin/chown root:root /etc/postfix/main.cf');

   if not ldap.Logged then begin
      logs.Syslogs('tldapconf.maincf():: Unable to logon to the ldap server postfix settings stay unchanged!!!');
      exit;
   end;

   if not FileExists(zpostfix.POSFTIX_POSTCONF_PATH()) then begin
      logs.Debuglogs('maincf:: POSFTIX_POSTCONF_PATH() return a corrupted path or Postfix is not installed...');
      exit;
   end;
   
   if not TryStrToInt(SYS.GET_INFO('EnableAmavisInMasterCF'),EnableAmavisInMasterCF) then EnableAmavisInMasterCF:=0;
   if not TryStrToInt(SYS.GET_INFO('PostfixEnableMasterCfSubmission'),PostfixEnableMasterCfSubmission) then PostfixEnableMasterCfSubmission:=0;

   Enable_postfixmodules();
   PostfixBounceTemplatesFileContent:=SYS.GET_INFO('PostfixBounceTemplatesFileContent');

if length(PostfixBounceTemplatesFileContent)>0 then begin
      template_bounce:=TstringList.Create;
      template_bounce.LoadFromFile('/etc/artica-postfix/settings/Daemons/PostfixBounceTemplatesFileContent');
      LOGS.logs('maincf:: Save "/etc/postfix/bounce.template.cf"');
      try
         template_bounce.SaveToFile('/etc/postfix/bounce.template.cf');
      except
         logs.Syslogs('FATAL ERROR WHILE SAVING template_bounce');
      end;
      template_bounce.Free;
      fpsystem('/bin/chown root:root /etc/postfix/bounce.template.cf >/dev/null 2>&1');
      restart:=true;
      logs.Debuglogs('Saving bounce.template.cf from settings success');
   end else begin
    LOGS.Debuglogs('maincf:: bounce_template disabled...');
end;
   
   
if GLOBAL_INI.get_INFOS('MasterCFEnabled')='1' then begin
   if length(confd.PostfixMasterCfFile)>0 then begin
      LOGS.Debuglogs('maincf:: Save "/etc/postfix/master.cf"');
      l:=TstringList.Create;
      l.Add(confd.PostfixMasterCfFile);
      l.SaveToFile('/etc/postfix/master.cf');
      logs.OutputCmd('/bin/chown root:root /etc/postfix/master.cf');
      logs.OutputCmd('/bin/chmod 644 /etc/postfix/master.cf');
      l.Free;
      restart:=true;
   end;
end;

mailman:=tmailman.Create(SYS);
if FileExists(mailman.PostFixToMailManPath()) then begin
   if not mailman.IS_MAILMAN_IN_MASTER() then begin
      if mailman.ADD_MAILMAN_IN_MASTER() then restart:=true;
   end;
end;
mailman.free;
zpostfix.FIX_RETRY_DAEMON();
if not TryStrToInt(SYS.GET_INFO('PostfixEnableMasterCfSSL'),PostfixEnableMasterCfSSL) then PostfixEnableMasterCfSSL:=0;
if not TryStrToInt(SYS.GET_INFO('PostfixEnabledInBind9'),PostfixEnabledInBind9) then PostfixEnabledInBind9:=1;

if PostfixEnableMasterCfSSL=1 then begin
   logs.Syslogs('Enable SSL in master.cf');
   zpostfix.ENABLE_SSL(true)
end else  begin
    logs.Syslogs('Disable SSL in master.cf');
    zpostfix.ENABLE_SSL(false);
end;


if PostfixEnableMasterCfSubmission=1 then begin
   logs.Syslogs('Enable submission in master.cf');
   zpostfix.ENABLE_SUBMISSION(true);
end else  begin
    logs.Syslogs('Disable submission in master.cf');
    zpostfix.ENABLE_SUBMISSION(false);
end;

logs.Debuglogs('maincf:: Execute '+SYS.LOCATE_PHP5_BIN()+' ' +GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.etc-hosts.php');
fpsystem(SYS.LOCATE_PHP5_BIN()+' ' +GLOBAL_INI.get_ARTICA_PHP_PATH()+'/exec.etc-hosts.php >/dev/null &');



if PostfixEnabledInBind9=1 then begin
      if FileExists('/etc/artica-postfix/settings/Daemons/PostfixBind9DNSList') then begin
         logs.Syslogs('Apply DNS nameservers in bind9');
         bind9.ApplyForwarders('/etc/artica-postfix/settings/Daemons/PostfixBind9DNSList');
         bind9.ApplyLoopBack();
         logs.Syslogs('Restart bind9');
         bind9.STOP();
         bind9.START();
         logs.Syslogs('Bind9 OK');
      end;
end;

// Certificate ---------------------------------------------------------
logs.Debuglogs('maincf:: Checking certificate....');
smtpd_tls_CAfile:=zpostfix.POSTFIX_EXTRACT_MAINCF('smtpd_tls_CAfile');
if length(smtpd_tls_CAfile)>0 then begin
    if not FileExists(smtpd_tls_CAfile) then begin
       zpostfix.GENERATE_CERTIFICATE();
    end;
end;

logs.Debuglogs('maincf:: Checking Header checks');
ForceDirectories('/etc/postfix/hash_files');
if not FileExists('/etc/postfix/hash_files/header_checks.cf') then logs.OutputCmd('/bin/touch /etc/postfix/hash_files/header_checks.cf');

logs.Debuglogs('maincf:: Checking Spamassassin white and black lists');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.white-black-central.php');

logs.Debuglogs('maincf:: Checking Spamassassin finish..');

logs.Debuglogs('maincf:: Checking Milter-greylist');
milter_greylist();
logs.Debuglogs('maincf:: Checking Milter-greylist finish..');



   if restart then begin
         logs.Syslogs('Restarting all messaging SMTP daemons....');
         kas3:=tkas3.Create(SYS);
         if GLOBAL_INI.get_INFOS('KasxFilterEnabled')='1' then begin
             logs.Debuglogs('Restarting Kaspersky Anti-Spam...');
             kas3.RESTART();
             logs.Debuglogs('Restarting Kaspersky Anti-Spam success');
         end;
         
         mailspy:=tmailspy.Create(SYS);
         mailspy.STOP();
         mailspy.START();
         mailspy.free;
         
         mailarchive:=tmailarchive.Create(SYS);
         mailarchive.STOP();
         mailarchive.START();
         mailarchive.Free;

         fpsystem('/etc/init.d/artica-postfix restart mgreylist');

         clamav.MILTER_STOP();
         clamav.MILTER_START();

         kavmilter_settings();
         
         spamass:=Tspamass.Create(SYS);
         spamass.MILTER_STOP();
         spamass.MILTER_START();
         spamass.free;
         
         dkim:=Tdkim.Create(SYS);
         dkim.DKIM_FILTER_STOP();
         dkim.DKIM_FILTER_START();
         dkim.free;
         
         bogom:=tbogom.Create(SYS);
         bogom.STOP();
         bogom.START();
         bogom.Free;
         
         Amavis();

         stunnel.STUNNEL_STOP();
         stunnel.STUNNEL_START();
         
         jcheckmail:=Tjcheckmail.Create(SYS);
         jcheckmail.STOP();
         jcheckmail.START();
         jcheckmail.Free;

         fpsystem('/etc/init.d/postfix reload');

         cron:=tcron.Create(SYS);
         cron.STOP();
         cron.START();
         cron.free;
         if FileExists('/usr/share/artica-postfix/ressources/logs/global.status.ini') then logs.DeleteFile('/usr/share/artica-postfix/ressources/logs/global.status.ini');

   end;
end;



//#############################################################################
PROCEDURE tldapconf.sa_learn();
begin
exit;
fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-learn &');

end;
//#############################################################################
FUNCTION tldapconf.UNDERSTAND_fetchmailrc():TstringList;
var
   RegExpr                :TRegExpr;
   l                      :TstringList;
   s                      :TstringList;
   i                      :integer;
   server                 :string;
   pass                   :string;
   protocol               :string;
   user                   :string;
   uid                    :string;
   foundpoll              :boolean;
   keep                   :string;
   t                      :integer;
begin
s:=TstringList.Create();


if not FileExists('/etc/fetchmailrc') then begin
   logs.Debuglogs('UNDERSTAND_fetchmailrc:: unable to stat /etc/fetchmailrc');
   exit;
end;
t:=0;
l:=TstringList.Create;
l.LoadFromFile('/etc/fetchmailrc');
foundpoll:=false;
RegExpr:=TRegExpr.Create;
      for i:=0 to l.Count-1 do begin
          RegExpr.Expression:='(poll\s+|server|skip|defaults)';
          if RegExpr.Exec(l.Strings[i]) then begin
             s.Add('');
             t:=s.Count-1;
          end;
          RegExpr.Expression:='poll\s+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'server='+RegExpr.Match[1]+';';
          RegExpr.Expression:='proto[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'protocol='+Lowercase(RegExpr.Match[1])+';';
          RegExpr.Expression:='user[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'user='+RegExpr.Match[1]+';';
          RegExpr.Expression:='pass[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'pass='+RegExpr.Match[1]+';';
          RegExpr.Expression:='is[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'mail='+RegExpr.Match[1]+';';
          RegExpr.Expression:='ssl';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'ssl=1;';
      end;
result:=s;
end;



//#############################################################################
PROCEDURE tldapconf.localdomains();
var
  f:TstringList;
begin

   if ldap.Logged=false then begin
    logs.logs('localdomains:: Logged is false;aborting...');
    exit;
 end;
   f:=TstringList.Create;
   f.AddStrings(ldap.Allowed_domains());
   try
   if length(ParamStr(2))>0 then begin

          f.SaveToFile(ParamStr(2));

   end else begin
      writeln(f.Text);
   end;
     except
              f.free;
             exit;
       end;
   
end;


//#############################################################################
PROCEDURE tldapconf.pnetworks();
var
  f:TstringList;
  i:integer;
begin

   if ldap.Logged=false then begin
    logs.logs('pnetworks:: Logged is false;aborting...');
    exit;
 end;
   f:=TstringList.Create;
   f.AddStrings(ldap.postfix_networks());
   for i:=0 to f.Count-1 do begin
       f.Strings[i]:=trim(f.Strings[i]);
   end;
   
   try
   if length(ParamStr(2))>0 then begin

          f.SaveToFile(ParamStr(2));

   end else begin
      writeln(f.Text);
   end;
     except
              f.free;
             exit;
       end;

end;


//############################################################################
PROCEDURE tldapconf.dkimfilter();
var
  f:dkimfilter_settings;
  conf:string;
  mime:Tdkim;
  l:TstringList;
begin
 Enable_postfixmodules();
 if ldap.Logged=false then begin
    logs.logs('dkimfilter:: Logged is false;aborting...');
    exit;
 end;
 
 f:=ldap.load_dkim_filter();
 if length(f.DkimFilterConf)=0 then begin
    logs.logs('dkimfilter:: DkimFilterConf is null;aborting...');
    exit;
 end;
 
 mime:=tdkim.Create(SYS);
 l:=Tstringlist.Create;
 l.Add(f.DkimFilterConf);
 l.SaveToFile(mime.MAIN_CONF_PATH());
 logs.logs('dkimfilter:: Saving new configuration done');
 l.free;
 mime.DKIM_FILTER_STOP();
 mime.DKIM_FILTER_START();
 mime.free;
end;
//############################################################################


PROCEDURE tldapconf.mimedefang();
var
  f:mimedefang_settings;
  mime:Tmimedefang;
  l:TstringList;
begin

   mime:=Tmimedefang.Create(SYS);
   if ldap.Logged=false then begin
    logs.logs('mimedefang:: Logged is false;aborting...');
    exit;
 end;

 if not FileExists(mime.CONF_PATH()) then begin
    logs.Logs('mimedefang:: unable to stat configuration file...');
    exit;
 end;
 
 f:=ldap.Load_mimedefang();
 if length(f.MimeDefangFilter)>3 then begin
        l:=TstringList.Create;
        l.Add(f.MimeDefangFilter);
        l.SaveToFile(mime.CONF_PATH());
        l.free;
        logs.logs('mimedefang:: Apply settings OK restart daemon');
       if FileExists(mime.INITD()) then fpsystem(mime.INITD() + ' restart');
 
 end else begin
   logs.Logs('mimedefang:: MimeDefangFilter is nil ??!!');
 
 end;
 

end;
//#############################################################################
procedure tldapconf.kav4samba_save();
var
   conf:kav4sambaSettings;
   kav:Tkav4samba;
   l:TstringList;
   D:boolean;
begin
D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
  if ldap.Logged=false then begin
   logs.logs('kav4samba_save:: Logged is false;aborting...');
    exit;
 end;
  kav:=Tkav4samba.Create;
  conf:=ldap.load_Kav4Samba();
  l:=TstringList.Create;

  if length(conf.kav4sambaConf)>0 then begin
        if FileExists(kav.conf_path()) then begin
               l.Add(conf.kav4sambaConf);
               logs.logs('kav4samba_save:: Editing '+kav.conf_path());
               l.SaveToFile(kav.conf_path());
               logs.logs('kav4samba_save:: Restart kaspersky services');
               kav.SERVICE_STOP();
               kav.SERVICE_START();
               logs.logs('kav4samba_save:: Done...');
               exit;
        end;
  end else begin
       logs.logs('kav4samba_save:: kav4sambaConf is null');

  end;


end;
//#############################################################################

procedure tldapconf.KasperskyASGroups();
var
       Groups    :TstringList;
       D         :Boolean;
       i         :integer;
       f         :kas_groups;
       tmpfile   :string;
       l         :TstringList;
       uid       :string;
begin

 D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
 if ldap.Logged=false then begin
   logs.logs('KasperskyASGroups:: Logged is false;aborting...');
    exit;
 end;
KasperskyASGroupDeleteFiles();
Groups:=TstringList.Create;
l:=TstringList.Create;
Groups.AddStrings(ldap.Load_KasGroupsList());
forceDirectories('/usr/local/ap-mailfilter3/conf/def/group');
For i:=0 to Groups.Count-1 do begin
    l.Clear;
    if D then  writeln('KasperskyASGroups:: ',Groups.Strings[i]);
    f:=ldap.Load_KasGroupDatas(Groups.Strings[i]);
    uid:=f.KasHexGroupName;
   logs.logs('KasperskyASGroups:: -> UID=' + uid);

    tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + KasperskyASGroupsParseFile(f.kasallowxml);
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasallowxml);
    l.SaveToFile(tmpfile);
    l.Clear;

tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + KasperskyASGroupsParseFile(f.kasdenyxml);
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasdenyxml);
    l.SaveToFile(tmpfile);
    l.Clear;
    
tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + KasperskyASGroupsParseFile(f.kasipallowxml);
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasipallowxml);
    l.SaveToFile(tmpfile);
    l.Clear;
    
tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + KasperskyASGroupsParseFile(f.kasipdenyxml);
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasipdenyxml);
    l.SaveToFile(tmpfile);
    l.Clear;
    
tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + KasperskyASGroupsParseFile(f.kasmembersxml);
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasmembersxml);
    l.SaveToFile(tmpfile);
    l.Clear;
    
tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + KasperskyASGroupsParseFile(f.kasprofilexml);
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasprofilexml);
    l.SaveToFile(tmpfile);
    l.Clear;
    
tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + uid + '-rule.def';
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasruledef);
    l.SaveToFile(tmpfile);
    l.Clear;
    
tmpfile:='/usr/local/ap-mailfilter3/conf/def/group/' + uid + '-action.def';
   logs.logs('KasperskyASGroups:: -> File=' +tmpfile);
    l.Add(f.kasactiondef);
    l.SaveToFile(tmpfile);
    l.Clear;

end;

KasperskyAsBuildProfile();
fpsystem('/bin/chown mailflt3:mailflt3 /usr/local/ap-mailfilter3/conf/def/group/*');

fpsystem('/bin/chmod 755 ' +GLOBAL_INI.get_ARTICA_PHP_PATH() +'/bin/install/kavgroup/kas-compile-artica.pl');
logs.OutputCmd('/bin/chown -R mailflt3:mailflt3 /usr/local/ap-mailfilter3/conf');
fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH() +'/bin/install/kavgroup/kas-compile-artica.pl >/opt/artica/logs/kas.compile.tmp 2>&1');

KasperskyASParseError();
///usr/local/ap-mailfilter3/bin/sfupdates -s -f

end;
//#############################################################################
procedure tldapconf.KasperskyASParseError();
var
   RegExpr                :TRegExpr;
   l:tStringList;
   i:integer;
   error:string;
   error_text:string;
begin
  if not FileExists('/opt/artica/logs/kas.compile.tmp') then exit;
  
  l:=TstringList.Create;
  l.LoadFromFile('/opt/artica/logs/kas.compile.tmp');
  RegExpr:=TRegExpr.Create;

  RegExpr.Expression:='<h1 class=error>(.+?)</h1>';
  for i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
         error:=RegExpr.Match[1];
        logs.logs('Error: ' + error);
         break;
      end;
  
  end;
  
  if length(error)=0 then begin
      RegExpr.Expression:='<h1 class=table_title>(.+?)</h1>';
      for i:=0 to l.Count-1 do begin
          if RegExpr.Exec(l.Strings[i]) then begin
             error:=RegExpr.Match[1];
            logs.logs('Error: ' + error);
             break;
          end;
      end;
  end;

  RegExpr.Expression:='successfully';
  if RegExpr.Exec(error) then begin
       logs.Syslogs('Kaspersky anti-spam group configuration:' + error);
  end;
  
 RegExpr.Expression:='STSConfig API(.+?)</td>';
 if RegExpr.Exec(l.Text) then begin
     error_text:=GLOBAL_INI.HtmlToText(RegExpr.Match[1]);

    logs.logs(error_text);
     logs.Syslogs('Kaspersky anti-spam group configuration:' + error + ' ' +error_text);
 end;
 fpsystem('/bin/rm -f /opt/artica/logs/kas.compile.tmp');
 RegExpr.free;
 l.free;
  

end;

//#############################################################################
procedure tldapconf.KasperskyASGroupDeleteFiles();
var
   RegExpr                :TRegExpr;
   z:Tsystem;
   l:tStringList;
   i:integer;

begin
   RegExpr:=TRegExpr.Create;
   z:=Tsystem.Create;
   l:=TstringList.Create;
   RegExpr.Expression:='([0-9]+)-.+';
   l.AddStrings(z.DirFiles('/usr/local/ap-mailfilter3/conf/def/group','*'));
   for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
           if RegExpr.Match[1]<>'00000000' then begin
             logs.logs('Delete '+l.Strings[i]);
              GLOBAL_INI.DeleteFile('/usr/local/ap-mailfilter3/conf/def/group/' + l.Strings[i]);
           end;
        end;

   end;
   RegExpr.free;
end;
//#############################################################################
procedure tldapconf.cyrusconfig();
begin
 logs.Debuglogs('cyrusconfig():: Restart daemons');
 logs.Debuglogs('cyrusconfig():: Check configuration');
 logs.OutputCmd('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
 fpsystem('/etc/init.d/artica-postfix restart imap');
end;
//#############################################################################
function tldapconf.KasperskyASGroupsParseFile(datas:string):string;
var
   RegExpr                :TRegExpr;
begin
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='FILENAME="(.+?)"';
   if RegExpr.Exec(datas) then result:=RegExpr.Match[1];
   RegExpr.free;
end;
//#############################################################################
procedure tldapconf.KasperskyAsBuildProfile();
var
   RegExpr                :TRegExpr;
   Z:Tsystem;
   l:TstringList;
   t:TstringList;
   emailxml:TstringList;
   iplistxml:TstringList;
   i:Integer;
   path:string;
begin
   z:=Tsystem.Create;
   l:=TstringList.Create;
   emailxml:=TstringList.Create;
   iplistxml:=TstringList.Create;
   t:=TstringList.Create;
   l.AddStrings(z.DirFiles('/usr/local/ap-mailfilter3/conf/def/group','*-action.def'));

   
t.Add('<?xml version="1.0" encoding="utf-8"?>');
t.Add('#include <base/profiles.xml.macro>');
t.Add('BEGIN_PROFILE_REF_LIST');
t.Add('PROFILE_REF(0x00000000,"00000000-profile.xml","All",", ")');


   emailxml.Add('<?xml version="1.0" encoding="utf-8"?>');
   emailxml.Add('#include <base/emails.xml.macro>');
   emailxml.Add('BEGIN_EMAIL_REF_LIST');
   emailxml.Add('EMAIL_REF(0x00000000,"00000000-allow.xml","00000000-deny.xml","00000000-members.xml")');


   iplistxml.Add('<?xml version="1.0" encoding="utf-8"?>');
   iplistxml.Add('#include <base/iplists.xml.macro>');
   iplistxml.Add('BEGIN_IP_REF_LIST');
   iplistxml.Add('IP_REF(0x00000000,"00000000-ipallow.xml","00000000-ipdeny.xml")');



RegExpr:=TRegExpr.Create;
RegExpr.Expression:='([0-9]+)-action\.def';
   for i:=0 to l.Count-1 do begin
       if l.Strings[i]<>'00000000-action.def' then begin
          path:='/usr/local/ap-mailfilter3/conf/def/group/'+l.Strings[i];
         logs.logs('Profile.xml: Parse def  -> ' +path);
          RegExpr.Exec(l.Strings[i]);
          
         emailxml.Add('EMAIL_REF(0x'+RegExpr.Match[1]+',"'+RegExpr.Match[1]+'-allow.xml","'+RegExpr.Match[1]+'-deny.xml","'+RegExpr.Match[1]+'-members.xml")');
        iplistxml.Add('IP_REF(0x'+RegExpr.Match[1]+',"'+RegExpr.Match[1]+'-ipallow.xml","'+RegExpr.Match[1]+'-ipdeny.xml")');
          
          t.Add('PROFILE_REF(0x'+RegExpr.Match[1]+',"'+RegExpr.Match[1]+'-profile.xml","'+KasperskyAsParseProfile('_GROUP_NAME',path)+'","'+KasperskyAsParseProfile('_GROUP_MEMO',path)+'")');
       end;
   end;
   
t.Add('END_PROFILE_REF_LIST');
emailxml.Add('END_EMAIL_REF_LIST');
iplistxml.Add('END_IP_REF_LIST');

t.SaveToFile('/usr/local/ap-mailfilter3/conf/def/group/profiles.xml');
emailxml.SaveToFile('/usr/local/ap-mailfilter3/conf/def/group/emails.xml');
iplistxml.SaveToFile('/usr/local/ap-mailfilter3/conf/def/group/iplists.xml');


emailxml.free;
iplistxml.free;
t.free;
RegExpr.free;
end;
//#############################################################################
function tldapconf.KasperskyAsParseProfile(key:string;path:string):string;
var
   RegExpr                :TRegExpr;
   l:TstringList;
   i:integer;
begin

if not FileExists(path) then exit;
l:=TstringList.Create;
l.LoadFromFile(path);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='#define\s+'+key+'\s+"(.+?)"';
For i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
    result:=RegExpr.Match[1];
    break;
    end;

end;


RegExpr.Free;
l.free;
end;
//#############################################################################
procedure tldapconf.dtoclear_users();
var
users:TstringList;
i:integer;
userd:users_datas;
f:tstringList;
homeDirectory:string;
dotclear:tdotclear;
begin
 if ldap.Logged=false then begin
    logs.Debuglogs('dtoclear_users():: Logged is false;aborting...');
    exit;
 end;

 users:=TstringList.Create;
 users.AddStrings(ldap.DotClearUsers());
 dotclear:=tdotclear.Create(SYS);
 f:=TstringList.Create;
 for i:=0 to users.Count-1 do begin

     userd:=ldap.Load_userasdatas(users.Strings[i]);
     homeDirectory:=userd.homeDirectory;
     logs.Debuglogs('dtoclear_users():: Parsing ' + users.Strings[i]+' :' +homeDirectory);
     if length(homeDirectory)=0 then continue;
     f.Add(users.Strings[i]+';'+homeDirectory);
     
 end;

 forceDirectories('/etc/artica-postfix/settings/DotClear');
 f.SaveToFile('/etc/artica-postfix/settings/DotClear/users.conf');
 users.free;
 f.free;
 dotclear.STOP();
 dotclear.START();

end;

PROCEDURE tldapconf.FtpUsers();
begin

fpsystem('/etc/init.d/artica-postfix restart ftp');

end;

//#############################################################################
PROCEDURE tldapconf.maintenance();
var

L:TstringList;
Z:Tsystem;
F:artica_settings;
Execute:boolean;
touch_path:string;
i:integer;
begin
Execute:=false;
Z:=Tsystem.Create();
l:=TstringList.Create;
F:=ldap.Load_artica_main_settings();

if length(f.ArticaMaxTempLogFilesDay)=0 then f.ArticaMaxTempLogFilesDay:='3';

if GLOBAL_INI.COMMANDLINE_PARAMETERS('--delete') then GLOBAL_INI.DeleteFile('/etc/artica-postfix/cron.maintenance');

if FileExists('/bin/touch') then touch_path:='/bin/touch';
if FileExists('/usr/bin/touch') then touch_path:='/usr/bin/touch';
if Length(touch_path)=0 then begin
   LOGS.logs('maintenance():: unable to find touch tool');
   exit;
end;
if Not FileExists('/etc/artica-postfix/cron.maintenance') then begin
   Execute:=true;

end;

if not Execute then begin
  logs.logs('maintenance():: cron.maintenance='+IntToStr(GLOBAL_INI.SYSTEM_FILE_MIN_BETWEEN_NOW('/etc/artica-postfix/cron.maintenance')));
   if GLOBAL_INI.SYSTEM_FILE_MIN_BETWEEN_NOW('/etc/artica-postfix/cron.maintenance')>10 then begin
        Execute:=true;
   end;
end;

if Execute=false then exit;
   GLOBAL_INI.DeleteFile('/etc/artica-postfix/cron.maintenance');
   l.AddStrings(z.RecusiveListFiles('/var/log'));
   l.AddStrings(z.RecusiveListFiles('/opt/artica/logs'));
   
   for i:=0 to l.Count -1 do begin
     if FileExists(l.Strings[i]) then begin
       if  GLOBAL_INI.SYSTEM_FILE_DAYS_BETWEEN_NOW(l.Strings[i])>StrToInt(f.ArticaMaxTempLogFilesDay) then begin
          logs.logs(l.Strings[i]+ ' Days=Delete ' + IntToStr(GLOBAL_INI.SYSTEM_FILE_DAYS_BETWEEN_NOW(l.Strings[i])));
           if not GLOBAL_INI.PathIsDirectory(l.Strings[i]) then begin
              logs.Syslogs('Delete ' + l.Strings[i]);
              GLOBAL_INI.DeleteFile(l.Strings[i]);
           end;
              
       end;
     end;

   end;
   
   
fpsystem(touch_path + ' /etc/artica-postfix/cron.maintenance');

end;
//#############################################################################

PROCEDURE tldapconf.crossroads_apply(path:string);
begin
  if not FileExists(path) then exit;
  logs.logs('crossroads_apply() -> move ' + path + ' to /etc/artica-postfix');
  fpsystem('/bin/mv ' + path + ' /etc/artica-postfix');
  GLOBAL_INI.ARTICA_STOP();
  mldap.LDAP_STOP();
  mldap.LDAP_START();
  GLOBAL_INI.ARTICA_START();
end;
//#############################################################################
PROCEDURE tldapconf.SharedFolders();
begin
 fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.SharedFolderConfBuilder.php');
end;

//#############################################################################
PROCEDURE tldapconf.Enable_postfixmodules();
var
   artica:artica_settings;
   sqlgrey:sqlgrey_settings;
   backup:backup_settings;
   mgrey:miltergreylist_settings;
   RegExpr:TRegExpr;
   mgreye:string;
   servername:string;
   l:TstringList;
   mailboxinf:mailboxesinfos;
   nmap:nmap_settings;
   mysql:tmysql_daemon;
   PostfixSSLCert:string;

begin
   servername:=GLOBAL_INI.SYSTEM_FQDN();
   RegExpr:=TRegExpr.Create;
   artica:=ldap.Load_artica_main_settings();
   if not ldap.Logged then exit;
   sqlgrey:=ldap.Load_sqlgrey_settings(servername);
   mgrey:=ldap.Load_miltergreylist();
   backup:=ldap.Load_backup_settings();
   mailboxinf:=ldap.LoadMailboxes(false);
   nmap:=ldap.load_nmap_settings();
   
   
   logs.logs('Enable_postfixmodules():: starting function...');
   ldap.admin_modify();
   
   if mgrey.MilterGreyListEnabled='TRUE' then mgreye:='1';
   if mgrey.MilterGreyListEnabled<>'TRUE' then mgreye:='0';
   
   if length(artica.ArticaPolicyEnabled)=0 then artica.ArticaPolicyEnabled:='0';
   if length(artica.ArticaFilterEnabled)=0 then artica.ArticaFilterEnabled:='0';
   if length(artica.NTPDEnabled)=0 then artica.NTPDEnabled:='0';
   if length(artica.MysqlMaxEventsLogs)=0 then artica.MysqlMaxEventsLogs:='200000';
   if length(artica.spfmilterEnabled)=0 then artica.spfmilterEnabled:='0';
   if length(artica.EnableSyslogMysql)=0 then artica.EnableSyslogMysql:='1';
   if length(artica.DkimFilterEnabled)=0 then artica.DkimFilterEnabled:='0';
   if length(artica.ArticaUsbBackupKeyID)=0 then artica.ArticaUsbBackupKeyID:='NONE';
   if length(nmap.NmapRotateMinutes)=0 then nmap.NmapRotateMinutes:='60';
   if length(artica.EnableFDMFetch)=0 then artica.EnableFDMFetch:='0';
   if length(artica.MasterCFEnabled)=0 then artica.MasterCFEnabled:='0';
   if length(artica.P3ScanEnabled)=0 then artica.P3ScanEnabled:='0';
   if length(artica.EnableMilterBogom)=0 then artica.EnableMilterBogom:='0';
   if length(artica.MysqlServerName)=0 then artica.MysqlServerName:='127.0.0.1';
   if length(artica.EnableCollectdDaemon)=0 then artica.EnableCollectdDaemon:='0';
 //  if length(artica.EnableVirtualDomainsInMailBoxes)=0 then artica.EnableVirtualDomainsInMailBoxes:='0';


   


   
   if length(artica.EnableMysqlFeatures)=0 then begin
      mysql:=tmysql_daemon.Create(SYS);
      if not FileExists(mysql.daemon_bin_path()) then artica.EnableMysqlFeatures:='0' else artica.EnableMysqlFeatures:='1';
   end;
      
   
   sys.SET_INFO('ApacheArticaEnabled',artica.ApacheArticaEnabled);
   sys.SET_INFO('SqlGreyEnabled',IntToStr(sqlgrey.SqlGreyEnabled));
   sys.SET_INFO('ArticaPolicyEnabled',artica.ArticaPolicyEnabled);
   sys.SET_INFO('ArticaFilterEnabled',artica.ArticaFilterEnabled);
   sys.SET_INFO('NTPDEnabled',artica.NTPDEnabled);
   sys.SET_INFO('MysqlMaxEventsLogs',artica.MysqlMaxEventsLogs);
   sys.SET_INFO('spfmilterEnabled',artica.spfmilterEnabled);
   sys.SET_INFO('EnableSyslogMysql',artica.EnableSyslogMysql);
   sys.SET_INFO('MailfromdStop','0');
   sys.SET_INFO('DkimFilterEnabled',artica.DkimFilterEnabled);
   sys.SET_INFO('ArticaUsbBackupKeyID',artica.ArticaUsbBackupKeyID);
   sys.SET_INFO('NmapScanEnabled',artica.NmapScanEnabled);
   sys.SET_INFO('NmapRotateMinutes',nmap.NmapRotateMinutes);
   sys.SET_INFO('EnableFDMFetch',artica.EnableFDMFetch);
   sys.SET_INFO('MasterCFEnabled',artica.MasterCFEnabled);
   sys.SET_INFO('P3ScanEnabled',artica.P3ScanEnabled);
   sys.SET_INFO('MaxTempLogFilesDay',artica.ArticaMaxTempLogFilesDay);
   sys.SET_INFO('EnableMilterBogom',artica.EnableMilterBogom);
   sys.SET_INFO('EnableMysqlFeatures',artica.EnableMysqlFeatures);
   sys.SET_INFO('EnableCollectdDaemon',artica.EnableCollectdDaemon);
   //sys.SET_INFO('EnableVirtualDomainsInMailBoxes',artica.EnableVirtualDomainsInMailBoxes);


   

   

   backup_apply();
   l:=Tstringlist.Create;
   
   try
   nmap.NmapNetworkIP.SaveToFile('/etc/artica-postfix/nmap.networks.conf');
   except
      logs.logs('Failed to save "/etc/artica-postfix/nmap.networks.conf"');
   end;
   
   GLOBAL_INI.set_LDAP('cyrus_admin','cyrus');
   GLOBAL_INI.set_LDAP('cyrus_password',mailboxinf.Cyrus_password);

   logs.Syslogs('Writing artica-postfix master configuration done');

   if FileExists('/etc/artica-postfix/settings/Daemons/SmtpNotificationConfig') then begin
      fpsystem('/bin/cp /etc/artica-postfix/settings/Daemons/SmtpNotificationConfig /etc/artica-postfix/smtpnotif.conf');
   end;
   l.Clear;

    if FileExists('/usr/share/atmailopen/libs/Atmail/Config.php') then begin
       fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-make APP_ATOPENMAIL --config &');
    end;

    l.AddStrings(ldap.Allowed_domains());
    logs.Debuglogs('Enable_postfixmodules:: '+ IntToStr(l.Count) + ' local domain(s)');
    l.SaveToFile('/etc/artica-postfix/LocalDomains.conf');
    l.Clear;
   
   
   if length(artica.lighttpConfig)>0 then begin
       forcedirectories('/opt/artica/logs/lighttpd');
       logs.logs('Enable_postfixmodules:: Save ' + lighttpd.LIGHTTPD_CONF_PATH());
       l.Add(artica.lighttpConfig);
       l.SaveToFile(lighttpd.LIGHTTPD_CONF_PATH());
       l.Clear;
   end else begin
       logs.Debuglogs('Enable_postfixmodules:: artica.lighttpConfig is null, abort');
   end;
   
   PostfixSSLCert:=SYS.GET_INFO('PostfixSSLCert');
   if length(PostfixSSLCert)>0 then begin
       forcedirectories('/etc/artica-postfix');
       logs.Debuglogs('Enable_postfixmodules:: Save /etc/artica-postfix/ssl.certificate.conf');
       l.Add(PostfixSSLCert);
       l.SaveToFile('/etc/artica-postfix/ssl.certificate.conf');
       l.Clear;
   end else begin
       logs.Debuglogs('Enable_postfixmodules:: artica.PostfixSSLCert is null, abort');
   end;

   

   

   if length(artica.ApacheConfig)>0 then begin
       forceDirectories('/opt/artica/conf');
       logs.Debuglogs('Enable_postfixmodules:: Save /opt/artica/conf/artica-www.conf');
       l.Add(artica.ApacheConfig);
       l.SaveToFile('/opt/artica/conf/artica-www.conf');
       l.Clear;
   end;
   l.Free;

end;
//#############################################################################
PROCEDURE tldapconf.notifications_apply();
var
   ini_org:tinifile;
   l:TstringList;
   mailfrom,domain:string;
   RegExpr              :TRegExpr;
begin
    if not FileExists('/etc/artica-postfix/smtpnotif.conf') then exit;
    ini_org:=TiniFile.Create('/etc/artica-postfix/smtpnotif.conf');
    if ini_org.ReadString('SMTP','enabled','0')='0' then exit;
    mailfrom:=ini_org.ReadString('SMTP','smtp_sender','root@localhost');
    
l:=Tstringlist.Create;
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='.+?@(.+)';
if RegExpr.Exec(mailfrom) then domain:=RegExpr.Match[1];
l.Add('defaults');
l.Add('account default');
l.Add('host '+ ini_org.ReadString('SMTP','smtp_server_name','127.0.0.1'));
l.Add('port '+ ini_org.ReadString('SMTP','smtp_server_port','25'));
l.Add('timeout off');
l.Add('protocol smtp');
l.Add('domain '+domain);
     if length(ini_org.ReadString('SMTP','smtp_auth_user',''))>0 then begin
        l.Add('auth plain');
        l.Add('user '+ini_org.ReadString('SMTP','smtp_auth_user',''));
        l.Add('password '+ini_org.ReadString('SMTP','smtp_auth_passwd',''));
     end;
     
l.Add('from ' +mailfrom);
l.Add('maildomain =');
if ini_org.ReadString('SMTP','tls_enabled','0')='1' then begin
   l.Add('tls on');
   l.Add('tls_starttls on');
   l.Add('tls_certcheck off');
   l.Add('tls_key_file /opt/artica/ssl/certs/lighttpd.pem');
   l.Add('tls_cert_file /opt/artica/ssl/certs/lighttpd.pem');
end;


  try
      l.SaveToFile('/etc/artica-postfix/msmtprc');
   except
       logs.logs('Failed to save "/etc/artica-postfix/msmtprc"');
   end;
    

end;


//#############################################################################
PROCEDURE tldapconf.backup_apply();
var
backup    :backup_settings;
l         :TstringList;
ini       :TIniFile;
cmd       :string;
cron      :tcron;

begin
  if not ldap.Logged then begin
     logs.logs('backup_apply:: unable to logon inot ldap');
     exit;
  end;


  if GLOBAL_INI.get_INFOS('ArticaBackupEnabled')<>'1' then begin
      if FileExists('/etc/cron.d/artica-cron-backup') then begin
          GLOBAL_INI.DeleteFile('/etc/cron.d/artica-cron-backups');
          logs.mysql_logs('6','1','Success disable backup task');
          logs.Debuglogs('backup_apply:: feature is disabled');
          exit;
      end;
  end;

  backup:=ldap.Load_backup_settings();
  if length(backup.ArticaBackupConf)>0 then begin
       l:=TstringList.Create;
       l.Add(backup.ArticaBackupConf);
       l.SaveToFile('/etc/artica-postfix/artica-backup.conf');
       logs.Debuglogs('backup_apply:: /etc/artica-postfix/artica-backup.conf success');
       l.Free;
  end;
  
  
  
  
l:=TstringList.Create;
l.Add(backup.HdBackupConfig);
l.SaveToFile('/etc/artica-postfix/artica-hd-backup.conf');
logs.Debuglogs('backup_apply:: /etc/artica-postfix/artica-hd-backup success');
l.Free;
  
  
l:=TstringList.Create;
l.Add(backup.MountBackupConfig);
l.SaveToFile('/etc/artica-postfix/artica-netshares-backup.conf');
logs.Debuglogs('backup_apply:: /etc/artica-postfix/artica-netshares-backup.conf');
l.Free;
  
 if not FileExists('/etc/artica-postfix/artica-backup.conf') then begin
    logs.Syslogs('Backup process was not defined.. you need to set it, browse artica.backup.index.php web page');
    exit;
 end;
 
 cron:=tcron.Create(SYS);
 ini:=TIniFile.Create('/etc/artica-postfix/artica-backup.conf');
 cmd:=ini.ReadString('backup','cron_cmd','0 3 * * * root ' + GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-backup --backup');
 l:=TstringList.Create;
 l.Add(cmd);
 l.SaveToFile('/etc/cron.d/artica-cron-backup');
 logs.Debuglogs('backup_apply:: /etc/cron.d/artica.cron.backups notification success');
 fpsystem('/bin/chmod 0644 /etc/cron.d/artica-cron-backup');
 
 if ParamStr(2)='restart' then begin
     cron:=Tcron.Create(SYS);
     cron.STOP();
     cron.START();
     logs.Syslogs('artica-cron was restarted...');
 end;

 

end;


//#############################################################################
PROCEDURE tldapconf.OBM_OPERATIONS();
var
OBMApacheFile:string;
l         :TstringList;
OBMEnabled:Integer;
OBMConfIni:string;
OBMConfInc:string;
cron:tcron;

begin
 logs.Debuglogs('OBM_OPERATIONS:: Loading settings...');
 if not TryStrToInt(SYS.GET_INFO('OBMEnabled'),OBMEnabled) then OBMEnabled:=0;


 
 if OBMEnabled=0 then begin
      logs.Debuglogs('OBM_OPERATIONS:: OBM is disabled in this context');
      exit;
 end;
 
 if Not directoryExists('/usr/share/obm') then begin
     logs.Debuglogs('OBM_OPERATIONS:: OBM is not installed in this context');
     exit;
 end;

 OBMApacheFile:=SYS.GET_INFO('OBMApacheFile');
 OBMConfIni:=SYS.GET_INFO('OBMConfIni');
 OBMConfInc:=SYS.GET_INFO('OBMConfInc');

 
 if length(OBMApacheFile)=0 then begin
     logs.Debuglogs('OBM_OPERATIONS:: OBMApacheFile is empty in this context');
     exit;
 end;
 
 if length(OBMConfIni)>0 then begin
      l:=TstringList.Create;
      l.Add(OBMConfIni);
      forcedirectories('/usr/share/obm/conf');
      logs.Debuglogs('OBM_OPERATIONS:: updateting /usr/share/obm/conf/obm_conf.ini');
      l.SaveToFile('/usr/share/obm/conf/obm_conf.ini');
 end;

 if length(OBMConfInc)>0 then begin
      l:=TstringList.Create;
      l.Add(OBMConfInc);
      forcedirectories('/usr/share/obm/conf');
      logs.Debuglogs('OBM_OPERATIONS:: updateting /usr/share/obm/conf/obm_conf.inc');
      l.SaveToFile('/usr/share/obm/conf/obm_conf.inc');
 end;


 
 
 l:=TstringList.Create;
 l.Add(OBMApacheFile);
 try
    l.SaveToFile('/etc/artica-postfix/lighttpd-obm.conf');
 except
 logs.Syslogs('OBM_OPERATIONS:: FATAL ERROR WHITE SAVING /etc/artica-postfix/lighttpd-obm.conf');
 exit;
 end;
 
 
 l.free;
 logs.Debuglogs('OBM_OPERATIONS:: /etc/artica-postfix/lighttpd-obm.conf ok, restart lighttpd for OBM');
 zobm.SERVICE_STOP();
 zobm.SERVICE_START();
 cron:=tcron.Create(SYS);
 cron.STOP();
 cron.START();
end;
//#############################################################################
procedure tldapconf.iptables();
var
   l:TstringList;
   i:integer;
   f:iptables_settings;
   path:string;
   RegExpr                :TRegExpr;
begin
if ParamStr(2)<>'--start' then Enable_postfixmodules();

  
 path:=GLOBAL_INI.IPTABLES_PATH();
 if not FileExists(path) then begin
    writeln('Starting......: iptables is not installed...');
    exit;
 end;
  
  if GLOBAL_INI.Get_INFOS('IptablesEnabled')<>'1' then begin
      writeln('Starting......: iptables is disabled...');
      logs.logs('iptables:: disabled...');
      exit;
  end;
  l:=TstringList.Create;
  logs.logs('iptables:: Loading settings stored in database...');
  f:=ldap.Load_iptables_settings();
 if length(f.iptablesFile)=0 then begin
       if not FIleExists('/opt/artica/logs/iptables.rules') then begin
          writeln('Starting......: iptables no rules...');
          logs.logs('iptables:: No rules....aborting');
          exit;
       end else begin
          l.LoadFromFile('/etc/artica-postfix/iptables.rules');
          f.iptablesFile:=l.Text;
          l.Clear;
          l.Free;
          l:=TstringList.Create;
       end;
 end;

 
 fpsystem(path + ' -F');
 fpsystem(path + ' -X');
 


 l.Add(f.iptablesFile);
 l.SaveToFile('/etc/artica-postfix/iptables.rules');
 l.LoadFromFile('/etc/artica-postfix/iptables.rules');
 RegExpr:=TRegExpr.Create;
 for i:=0 to l.Count-1 do begin
     RegExpr.Expression:='^iptables';
     if RegExpr.Exec(l.Strings[i]) then begin
          writeln('Starting......: ',l.Strings[i]);
          logs.logs('iptables:: '+ExtractFilePath(path)+l.Strings[i]);
          fpsystem(ExtractFilePath(path)+l.Strings[i]);
     end;
 end;
end;
//#############################################################################


PROCEDURE tldapconf.crossroads_sync();
var
 confs                :crossroads_settings;
 D                    :boolean;
 i                    :integer;
 IsIsMaster           :boolean;
 order                :TIniFile;
 ldap_admin           :string;
 ldap_suffix          :string;
 ldap_password        :string;
 uri                  :string;
 cmdline              :string;
 
 
begin
   D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
   logs.logs('Loading settings stored in database...');
   confs:=ldap.Load_crossroads_main_settings();
   IsIsMaster:=False;
   if D then begin

       
       writeln('crossroads_sync():: CrossRoadsBalancingServerIP........',confs.CrossRoadsBalancingServerIP);
       writeln('crossroads_sync():: PostfixMasterServerIdentity........',confs.PostfixMasterServerIdentity);
       writeln('crossroads_sync():: CrossRoadsBalancingServerName......',confs.CrossRoadsBalancingServerName);
       for i:=0 to confs.PostfixSlaveServersIdentity.Count-1 do begin
       
       writeln('Slave..............................[',i,']: ',confs.PostfixSlaveServersIdentity.Strings[i]);
       end;
   
   end;
   logs.logs('crossroads_sync():: CrossRoadsBalancingServerIP........'+confs.CrossRoadsBalancingServerIP);
   logs.logs('crossroads_sync():: PostfixMasterServerIdentity........'+confs.PostfixMasterServerIdentity);
   logs.logs('crossroads_sync():: CrossRoadsBalancingServerName......'+confs.CrossRoadsBalancingServerName);
   
   


   IsIsMaster:=GLOBAL_INI.SYSTEM_ISIP_LOCAL(confs.PostfixMasterServerIdentity);


   if not IsIsMaster then begin
        logs.logs('crossroads_sync():: This computer is not a master');
       logs.logs('crossroads_sync():: This computer is not a master ');
        exit;
   end;
   
   
     ldap_admin:=trim(GLOBAL_INI.get_LDAP('admin'));
     ldap_suffix:=trim(GLOBAL_INI.get_LDAP('suffix'));
     ldap_password:=trim(GLOBAL_INI.get_LDAP('password'));

     logs.logs('crossroads_sync() this computer is a master ip (' + confs.PostfixMasterServerIdentity + ')');
     logs.logs('Creating file configuration....:/opt/artica/etc/crossroads.indentities.conf');

   
for i:=0 to confs.PostfixSlaveServersIdentity.Count-1 do begin
       uri:='https://'+confs.PostfixSlaveServersIdentity.Strings[i] +':9000/listener.balance.php';
       
       order:=TIniFile.Create('/opt/artica/etc/crossroads.indentities.conf');
      logs.logs('Creating file configuration....:/opt/artica/etc/crossroads.indentities.conf');
       order.WriteString('INFOS','suffix',ldap_suffix);
       order.WriteString('INFOS','admin',ldap_admin);
       order.WriteString('INFOS','password',ldap_password);
       order.WriteString('INFOS','mastr_ip',confs.PostfixMasterServerIdentity);
       order.WriteString('INFOS','master_name',confs.CrossRoadsBalancingServerName);
       order.WriteString('INFOS','slave_ip',confs.PostfixSlaveServersIdentity.Strings[i]);
       order.WriteString('INFOS','pol_time', confs.CrossRoadsPoolingTime);
       order.UpdateFile;
       order.Free;

       cmdline:='/opt/artica/bin/curl -k -A artica --connect-timeout 5  -F "crossroads=@/opt/artica/etc/crossroads.indentities.conf" ' + uri;
       logs.logs(cmdline);
       writeln('Send requests to ' + confs.PostfixSlaveServersIdentity.Strings[i]);
      logs.logs(cmdline);
       fpsystem(cmdline);
       end;
   
   
   

end;
//#############################################################################
PROCEDURE tldapconf.imapsync_import(user:string);
var
   mailbox_to:users_datas;
   admin_name,admin_password:string;
   imapsy:timapsync;
   logfile:string;
   cmd:string;
   todelete:string;
   ConfLdap:topenldap;
   D:boolean;
   l:TstringList;
   TEMP_CONF:string;
   RemoteParametersPath:string;
   RemoteConfig:TIniFile;
   sslStr:string;
begin
   d:=false;
   if length(user)=0 then exit;
   d:=LOGS.COMMANDLINE_PARAMETERS('--verbose');
   forceDirectories(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/imap_import');
   fpsystem('/bin/chown -R '+www_user+' '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs');
   
   logfile:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/imap_import/'+user+'.log';
   RemoteParametersPath:='/etc/artica-postfix/settings/Daemons/'+user+'ImportMailBoxData';
   
   
   
   imapsy:=timapsync.Create(SYS);


   if Not FileExists(imapsy.MAILSYNC_BIN_PATH()) then begin
      logs.LogGeneric('WARNING, Unable to stat mailsync path',logfile);
      logs.Syslogs('WARNING, Unable to stat mailsync path');
      exit;
   end;

   if not ldap.Logged then begin
       logs.LogGeneric('Retreive infos from LDAP failed',logfile);
       exit;
   end;
   
   
   if not ldap.Logged then begin
       logs.LogGeneric('Retreive infos from '+RemoteParametersPath+' failed',logfile);
       exit;
   end;
   
   ConfLdap:=topenldap.Create;
   admin_name:=ConfLdap.get_LDAP('cyrus_admin');
   admin_password:=ConfLdap.get_LDAP('cyrus_password');
   ConfLdap.free;
   mailbox_to:=ldap.Load_userasdatas(user);
   RemoteConfig:=TiniFile.Create(RemoteParametersPath);
   
   if RemoteConfig.ReadString('INFO','use_ssl','no')='yes' then  sslStr:='/ssl/novalidate-cert';
   

   
   logs.LogGeneric('Checking '+RemoteConfig.ReadString('INFO','remote_imap_server','')+sslStr,logfile);



l:=TstringList.Create;
l.Add('	store remote {');
l.Add('	server	{'+RemoteConfig.ReadString('INFO','remote_imap_server','')+'/user='+ RemoteConfig.ReadString('INFO','remote_imap_username','') + sslStr+'}');
l.Add('	ref	{'+RemoteConfig.ReadString('INFO','remote_imap_server','')+'}');
l.Add('	passwd	'+RemoteConfig.ReadString('INFO','remote_imap_password',''));
l.Add('	pat	*');
l.Add('}');
l.Add('store artica {');
l.Add('	server	{127.0.0.1:143/user='+user+'/imap/novalidate-cert}');
l.Add('	ref	{127.0.0.1:143}');
l.Add('	passwd	'+mailbox_to.userPassword);
l.Add('	pat	*');
l.Add('}');
l.Add('');
l.Add('channel sync  remote artica {');
l.Add('	msinfo	{127.0.0.1:143/user='+user+'/imap/novalidate-cert}msinfo');
l.Add('	passwd	'+mailbox_to.userPassword);
l.Add('}');

TEMP_CONF:=logs.FILE_TEMP();
logs.Syslogs('Saving mailsync configuration file into temp ' +TEMP_CONF);
try
   l.SaveToFile(TEMP_CONF);
except
logs.Syslogs('WARNING: FATAL ERROR WHILE Saving ' +TEMP_CONF);
exit;
end;

cmd:=imapsy.MAILSYNC_BIN_PATH() + ' -d -M -f '+TEMP_CONF+' sync >>' + logfile + ' 2>&1 &';
logs.Debuglogs('Execute "'+ cmd+'"');
fpsystem(cmd);

end;
//#############################################################################



//#############################################################################
PROCEDURE tldapconf.imapsync_export(user_from:string;user_to:string;delete:string);
var
   mailbox_From:users_datas;
   mailbox_to:users_datas;
   admin_name,admin_password:string;
   imapsy:timapsync;
   logfile:string;
   cmd:string;
   todelete:string;
   ConfLdap:topenldap;
   D:boolean;
   l:TstringList;
   TEMP_CONF:string;
begin
   d:=false;
   d:=LOGS.COMMANDLINE_PARAMETERS('--verbose');
   logfile:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/imap_export/'+user_from+'-'+user_to+'.log';
   imapsy:=timapsync.Create(SYS);
   
   
   if Not FileExists(imapsy.MAILSYNC_BIN_PATH()) then begin
      logs.Syslogs('WARNING, Unable to stat mailsync path');
      exit;
   end;

   if not ldap.Logged then begin
       logs.LogGeneric('Retreive infos from LDAP failed',logfile);
       exit;
   end;
   ConfLdap:=topenldap.Create;
   admin_name:=ConfLdap.get_LDAP('cyrus_admin');
   admin_password:=ConfLdap.get_LDAP('cyrus_password');
   ConfLdap.free;
   
   
   if trim(delete)='0' then todelete:=' -n ';
   mailbox_From:=ldap.Load_userasdatas(user_from);
   mailbox_to:=ldap.Load_userasdatas(user_to);

  
l:=TstringList.Create;
l.Add('	store cyrus-inbox1 {');
l.Add('	server	{127.0.0.1/user='+ user_from + '/ssl/novalidate-cert}');
l.Add('	ref	{127.0.0.1}');
l.Add('	passwd	'+mailbox_From.userPassword);
l.Add('	pat	*');
l.Add('}');
l.Add('store cyrus-inbox2 {');
l.Add('	server	{127.0.0.1/user='+user_to+'/ssl/novalidate-cert}');
l.Add('	ref	{127.0.0.1}');
l.Add('	passwd	'+mailbox_to.userPassword);
l.Add('	pat	*');
l.Add('}');
l.Add('');
l.Add('channel sync  cyrus-inbox1 cyrus-inbox2 {');
l.Add('	msinfo	{127.0.0.1/user='+user_to+'/ssl/novalidate-cert}');
l.Add('	passwd	'+mailbox_to.userPassword);
l.Add('}');

TEMP_CONF:=logs.FILE_TEMP();
logs.Syslogs('Saving mailsync configuration file into temp ' +TEMP_CONF);
try
   l.SaveToFile(TEMP_CONF);
except
logs.Syslogs('WARNING: FATAL ERROR WHILE Saving ' +TEMP_CONF);
exit;
end;

cmd:=imapsy.MAILSYNC_BIN_PATH() + ' -M '+todelete+'-f '+TEMP_CONF+' sync >>' + logfile + ' 2>&1 &';
logs.Debuglogs('Execute "'+ cmd+'"');
fpsystem(cmd);

end;
//#############################################################################
PROCEDURE tldapconf.mailbox_sync(user:string);
   var
   confs                :mailboxesinfos;
   tmpfile              :string;
   RegExpr              :TRegExpr;
   perltoolpath         :string;
   cmd                  :string;
   U                    :users_datas;
   acl                  :string;
   cyrus_admin_name     :string;
   CyrusEnableImapMurderedFrontEnd:integer;

begin
  if not tryStrToint(SYS.GET_INFO('CyrusEnableImapMurderedFrontEnd'),CyrusEnableImapMurderedFrontEnd) then CyrusEnableImapMurderedFrontEnd:=0;
  cyr.CheckRightsAndConfig();
  logs.Debuglogs('mailbox_sync:: parsing user ' + user+'...');
  logs.Debuglogs('mailbox_sync:: CyrusEnableImapMurderedFrontEnd=' + SYS.GET_INFO('CyrusEnableImapMurderedFrontEnd'));

  if CyrusEnableImapMurderedFrontEnd=1 then begin
      logs.Debuglogs('mailbox_sync:: ask to the backend...');
      cmd:=cyr.MURDER_SEND_CREATE_MBX(user);
      tmpfile:='/opt/artica/logs/' + GLOBAL_INI.MD5FromString(user+'mailbox');
      logs.WriteToFile(cmd,tmpfile);
      mailboxes_log(user,tmpfile);
      exit;
  end;




  confs:=ldap.LoadMailboxes(false);
  u:=ldap.Load_userasdatas(user);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='{[A-Z]+}';
  
  if not FileExists(Cyr.CYRADM_PATH()) then begin
         writeln('Unable to stat cyradm tool !');
         logs.Syslogs('Unable to stat cyradm tool');
         logs.mysql_logs('4','0',user+ ':: Unable to stat cyradm tool');
         exit;
  end;
  
     perltoolpath:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/cyrus-admin.pl';
     
     
  if not GLOBAL_INI.SYSTEM_PROCESS_EXIST(cyr.CYRUS_PID()) then begin
      writeln('cyrus imap daemon is not started !');
      logs.Syslogs('cyrus imap daemon is not started !');
      logs.mysql_logs('4','0',user+ ':: cyrus imap daemon is not started');
      exit;
  end;
  
  if not FileExists(perltoolpath) then begin
         writeln('Unable to stat ' + perltoolpath);
         logs.Syslogs('Unable to stat ' + perltoolpath);
         logs.mysql_logs('4','0',user+ ':: Unable to stat ' + perltoolpath);
         exit;
  end;
  

  if pos('MD5}',confs.Cyrus_password)>0 then begin
      ldap.DeleteCyrusUser();
      writeln(user+':: Rebuild cyrus administrator ');
      logs.mysql_logs('4','2',user+':: Rebuild cyrus administrator ');
      ldap.CreateCyrusUser();
      confs:=ldap.LoadMailboxes(false);
  end;
  
  tmpfile:='/opt/artica/logs/' + GLOBAL_INI.MD5FromString(user+'mailbox');


  
  acl:=mailboxes_acl(u.MailboxSecurityParameters);

  RegExpr.Expression:='(.+?)@(.+)';
  cyrus_admin_name:='cyrus';


  if RegExpr.Exec(user) then begin
     cyrus_admin_name:='cyrus@'+RegExpr.Match[2];
     user:=RegExpr.Match[1];
     fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.check-cyrus-account.php ' + cyrus_admin_name+' >/dev/null 2>&1');
  end;

  
  if length(U.MailBoxMaxSize)>0 then begin
     cmd:=perltoolpath + ' -u '+cyrus_admin_name+' -p ' + confs.Cyrus_password + ' -m '  + user + ' -q ' + U.MailBoxMaxSize + ' -a ' + acl+' >' + tmpfile + ' 2>&1';
  end else begin
      cmd:=perltoolpath + ' -u '+cyrus_admin_name+' -p ' + confs.Cyrus_password + ' -m '  + user+  ' -a ' + acl+' >' + tmpfile + ' 2>&1';
  end;
  
  logs.Debuglogs('mailbox_sync::' + cmd);
  fpsystem(cmd);
  writeln(logs.ReadFromFile(tmpfile));
  logs.Debuglogs('mailbox_sync::' + logs.ReadFromFile(tmpfile));
  mailboxes_log(user,tmpfile);
  if not FileExists(tmpfile) then exit;
  logs.DeleteFile(tmpfile);
  
  
end;

//#############################################################################
function tldapconf.mailboxes_acl(ini_content:string):string;
var
   sini:TiniFile;
   l:TstringList;
   tmp:string;
   value:string;
   resultat:string;
   i:integer;
begin

if length(ini_content)=0 then begin
   writeln('mailboxes_acl:: Unable to get content of the configuration sended!!  assume lrswipkxtecd, check LDAP connection');
   logs.Syslogs('mailboxes_acl:: Unable to get content of the configuration sended assume lrswipkxtecd check LDAP connection');
   exit('lrswipkxtecd');
end;


tmp:=LOGS.FILE_TEMP();
l:=TstringList.Create;
l.Add(ini_content);
l.SaveToFile(tmp);
l.Clear;



if not FileExists(tmp) then begin
   writeln('mailboxes_acl:: Unable to stat temp file ' + tmp+ ' assume lrswipkxtecda');
   logs.Syslogs('mailboxes_acl:: Unable to stat temp file ' + tmp+ ' assume lrswipkxtecda');
   exit('lrswipkxtecda');
end;

resultat:='';
sini:=TiniFile.Create(tmp);
sini.ReadSection('mailbox',l);
for i:=0 to l.Count-1 do begin
    value:=sini.ReadString('mailbox',l.Strings[i],'0');
    if value='1' then begin
         logs.Debuglogs('mailboxes_acl:: found acl '+l.Strings[i]);
         resultat:=resultat+l.Strings[i];
    end;
end;
logs.DeleteFile(tmp);
result:=resultat;
end;
//#############################################################################



PROCEDURE tldapconf.mailboxes_log(user:string;tmpfile:string);
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
begin

   if not FileExists(tmpfile) then begin
       logs.mysql_logs('4','0',user+':: Unable to stat results ' + tmpfile);
       exit;
   end;

   l:=TstringList.Create;
   l.LoadFromFile(tmpfile);
   RegExpr:=TRegExpr.Create;
   for i:=0 to l.Count-1 do begin
         RegExpr.Expression:='Error(.+?)Please login first';
         if RegExpr.Exec(l.Strings[i]) then begin
          logs.mysql_logs('4','0',user+':: Connection failed err.1, cyrus admin has bad password set in artica');
          exit;
         end;
          RegExpr.Expression:='Mailbox already exists';
         if RegExpr.Exec(l.Strings[i]) then exit;

          RegExpr.Expression:='Created Mailbox';
         if RegExpr.Exec(l.Strings[i]) then begin
          logs.mysql_logs('4','1',user+':: success created mailbox');
          exit;
         end;

         RegExpr.Expression:='Setting Quota';
         if RegExpr.Exec(l.Strings[i]) then begin
          logs.mysql_logs('4','1',user+':: success setting Quota');
          exit;
         end;
          

   end;
   logs.mysql_logs('4','0',user+':: '+l.Text);


end;
//#############################################################################
PROCEDURE tldapconf.mailboxes_sync();
   var
   confs                :mailboxesinfos;
   i                    :integer;
   RegExpr              :TRegExpr;
   perltoolpath         :string;
   cmd                  :string;

begin


  confs:=ldap.LoadMailboxes(true);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='{[A-Z]+}';
  perltoolpath:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/cyrus-admin.pl';
  
  if not FileExists(perltoolpath) then begin
         logs.mysql_logs('4','0','Unable to stat ' + perltoolpath);
         exit;
  end;
  
  
  if pos('MD5}',confs.Cyrus_password)>0 then begin
      ldap.DeleteCyrusUser();
      logs.mysql_logs('4','2','Rebuild cyrus administrator ');
      ldap.CreateCyrusUser();
      confs:=ldap.LoadMailboxes(true);
  end;
  
  for i:=0 to confs.Users.Count-1 do begin
     logs.logs('user:.....'+confs.Users.Strings[i]);
      cmd:=perltoolpath + ' -u cyrus -p ' + confs.Cyrus_password + ' -m '  + confs.Users.Strings[i];
      LOGS.Debuglogs(cmd);
      fpsystem(perltoolpath + ' -u cyrus -p ' + confs.Cyrus_password + ' -m '  + confs.Users.Strings[i]);
  end;
  
  logs.logs('Cyrus password:.....'+confs.Cyrus_password);
  
end;
//#############################################################################
PROCEDURE tldapconf.SqlGrey(servername:string);
var

   l                    :TStringList;
   confs                :sqlgrey_settings;
   LocalTimeCode        :string;

begin

  LOGS.logs('SqlGrey:: load SqlGrey main settings');
  confs:=ldap.Load_sqlgrey_settings(servername);
  GLOBAL_INI.set_INFOS('SqlGreyIsActive',IntToStr(confs.SqlGreyEnabled));


  if length(confs.SqlGreyConf)>0 then begin
   LocalTimeCode:=GLOBAL_INI.get_INFOS('SqlGreyTimeCode');
  logs.logs('SqlGrey:: SqlGreyTimeCode :"' + confs.SqlGreyTimeCode + '"<>"' + LocalTimeCode + '"');

   if length(LocalTimeCode)=0 then LocalTimeCode:='0';
   if length(confs.SqlGreyTimeCode)=0 then confs.SqlGreyTimeCode:='0';


   if confs.SqlGreyTimeCode=LocalTimeCode then begin
      logs.logs('SqlGrey:: SqlGreyTimeCodeimeCode is the same as local LocalTimeCode, aborting...');
       exit;
   end;
  LOGS.logs('SqlGrey:: SqlGreyTimeCode :' + confs.SqlGreyTimeCode + '<>' + LocalTimeCode);
  
      //GLOBAL_INI.SQLGREY_STOP();
      l:=TStringList.Create;
      l.Add(confs.SqlGreyConf);
      l.SaveToFile('/opt/artica/etc/sqlgrey.conf');
      l.free;
      GLOBAL_INI.set_INFOS('SqlGreyTimeCode',confs.SqlGreyTimeCode);

  if confs.SqlGreyEnabled=1 then begin
         //GLOBAL_INI.SQLGREY_START();
     end else begin
         //GLOBAL_INI.SQLGREY_STOP();
   end;
  
  end;

end;

//#############################################################################
PROCEDURE tldapconf.Amavis();
var
   amavis:tamavis;
   l:TstringList;
   confpath:string;
   confdata:string;
   AlterMimeTXTDisclaimer:string;
   AlterMimeHTMLDisclaimer:string;
   EnableAmavisInMasterCF  :integer;
begin
     amavis:=Tamavis.Create(SYS);
     AlterMimeTXTDisclaimer:=SYS.GET_INFO('AlterMimeTXTDisclaimer');
     if length(AlterMimeTXTDisclaimer)>0 then logs.WriteToFile(AlterMimeTXTDisclaimer,'/usr/local/etc/altermime-disclaimer.txt');

     AlterMimeHTMLDisclaimer:=SYS.GET_INFO('AlterMimeHTMLDisclaimer');
     if length(AlterMimeHTMLDisclaimer)>0 then logs.WriteToFile(AlterMimeHTMLDisclaimer,'/usr/local/etc/altermime-disclaimer.html');
     
     if not TryStrToInt(SYS.GET_INFO('EnableAmavisInMasterCF'),EnableAmavisInMasterCF) then EnableAmavisInMasterCF:=0;
     
     if EnableAmavisInMasterCF=1 then begin
             logs.Syslogs('tldapconf.Amavis():: Change amavis to post-queue method...');
             amavis.AMAVIS_TO_MASTERCF();
     end else begin
           logs.Syslogs('tldapconf.Amavis():: Change amavis to pre-queue method...');
           amavis.MASTER_CF_DELETE_AMAVIS();
     end;

     if ParamStr(2)='--without-restart' then begin
          logs.Debuglogs('tldapconf.Amavis():: --without-restart invoked, stopping new');
          amavis.FRee;
          exit;
     end;
     logs.Syslogs('tldapconf.Amavis():: Restart amavis...');
     amavis.AMAVISD_RELOAD();
     logs.Syslogs('tldapconf.Amavis():: success Restart amavis...');
     amavis.FRee;
     
end;

 //#############################################################################
procedure tldapconf.NMAP();

var
   verbose:boolean;
   nmap:nmap_settings;
   cdir:string;
   i:integer;
   cmd:string;
   s,mypid:string;
   NmapRotateMinutes:string;
   NmapRotate:Integer;
   Start:boolean;
   NmapMinutes:integer;
begin
   mypid:=intTostr(fpgetpid);
   cdir:='';
   Start:=false;
   verbose:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
   if GLOBAL_INI.COMMANDLINE_PARAMETERS('--force') then begin
      logs.nmap('NMAP()::['+mypid+'] --force has been used...');
      verbose:=true;
   end;

   
   if not FileExists(SYS.LOCATE_NMAP()) then begin
       if verbose then logs.nmap('NMAP()::['+mypid+'] unable to stat nmap tool');
       exit;
   end;
   
   
    if not ldap.Logged then begin
       if verbose then logs.nmap('NMAP()::['+mypid+'] unable to log into ldap database...aborting');
       exit;
    end;
   
   if GLOBAL_INI.get_INFOS('NmapScanEnabled')<>'1' then begin
      if verbose then logs.nmap('NMAP()::['+mypid+'] NmapScanEnabled is disabled, aborting...');
      exit;
   end;
   
    s:=SYS.PidByProcessPath(SYS.LOCATE_NMAP());
    if length(s)>0 then logs.nmap('NMAP()::['+mypid+'] an Nmap process running "'+s+'"');

  if length(s)>0 then begin
      logs.nmap('NMAP()::['+mypid+'] already instances ('+s+') exists...aborting');
      exit;
   end;
   
   
   NmapRotateMinutes:=GLOBAL_INI.get_INFOS('NmapRotateMinutes');
   if length(NmapRotateMinutes)=0 then NmapRotateMinutes:='60';
   NmapRotate:=StrToInt(NmapRotateMinutes);
   
   if not GLOBAL_INI.COMMANDLINE_PARAMETERS('--force') then begin
      if Not FileExists('/etc/artica-postfix/nmap.touch') then begin
             logs.nmap('NMAP()::['+mypid+'] counter /etc/artica-postfix/nmap.touch doesn''t Exists start=true');
             Start:=true;
         end else begin
             NmapMinutes:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/nmap.touch');
             if NmapMinutes>NmapRotate then Start:=True;
      end;
   end else begin
       start:=true;
   end;
   
   
   
    if not Start then exit;
    logs.nmap('NMAP()::['+mypid+'] Reset counter');
    logs.OutputCmd('/bin/touch /etc/artica-postfix/nmap.touch');
    logs.nmap('NMAP()::['+mypid+'] Rotation every ' + IntToStr(NmapRotate) + ' min Current status is ' + IntToStr(NmapMinutes) + ' min');


   
   nmap:=ldap.load_nmap_settings;
   for i:=0 to nmap.NmapNetworkIP.Count-1 do begin
       if length(trim(nmap.NmapNetworkIP.Strings[i]))>0 then begin
          cdir:=cdir +trim(nmap.NmapNetworkIP.Strings[i]) + ' ';
       end;
   end;
   logs.nmap('NMAP()::['+mypid+'] cdir(s)='+cdir);

   
   // -F  Fast mode - Scan fewer ports than the default scan
   
   cmd:=SYS.LOCATE_NMAP()+' -O '+cdir +' -oN /etc/artica-postfix/nmap.map --system-dns -p1';
   logs.nmap('NMAP()::['+mypid+'] Running nmap scanner with following options');
   logs.nmap(cmd);
   logs.OutputCmd('/bin/touch /etc/artica-postfix/nmap1.touch');
   logs.OutputCmd(cmd);

   
   NMAP_scan_results('/etc/artica-postfix/nmap.map');
   logs.OutputCmd('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/nmap.log');
   logs.nmap('NMAP()::['+mypid+'] Executed...in '+IntToStr(SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/nmap1.touch'))+' minutes');
   logs.nmap('---------------------------------------------------');
   

end;
 //#############################################################################
 procedure tldapconf.NMAP_SINGLE(uid:string);

var
   verbose:boolean;
   cdir:string;
   cmd:string;
   mypid:string;
   f:users_datas;
begin
   f:=ldap.Load_userasdatas(uid);
   mypid:='';

   if  f.ComputerInfos.computerip='0.0.0.0'  then f.ComputerInfos.computerip:='';
   
   if length(f.ComputerInfos.computerip)>0 then begin
       cdir:=f.ComputerInfos.computerip;
   end else begin
       cdir:=uid;
       cdir:=AnsiReplaceText(cdir,'$','');
   end;
   
   
   verbose:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');

   if not FileExists(SYS.LOCATE_NMAP()) then begin
       if verbose then writeln('NMAP()::['+mypid+'] unable to stat nmap tool');
       exit;
   end;


    if not ldap.Logged then begin
       if verbose then writeln('NMAP()::['+mypid+'] unable to log into ldap database...aborting');
       exit;
    end;


   logs.Debuglogs('NMAP()::['+mypid+'] cdir(s)='+cdir);
   cmd:=SYS.LOCATE_NMAP()+' -v -F -O '+cdir +' -oN /etc/artica-postfix/'+cdir+'.map --system-dns --version-light';
   logs.OutputCmd(cmd);
   logs.Debuglogs('NMAP()::['+mypid+'] Executed...');
    NMAP_scan_results('/etc/artica-postfix/'+cdir+'.map');
   
end;
 //############################################################################
procedure tldapconf.NMAP_scan_results(path:string);
const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   verbose              :boolean;
   RegExpr              :TRegExpr;
   l                    :TstringList;
   i                    :integer;
   A                    :boolean;
   comp                 :computer_infos;
   mypid                :string;

begin
  verbose:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
  if not FileExists(path) then exit;
  
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^Interesting ports on';
  A:=false;
  l:=TstringList.Create;
  l.LoadFromFile(path);
  mypid:=intTostr(fpgetpid);
  if verbose then writeln('scanning ',l.Count,' lines');
  
  for i:=0 to l.count-1 do begin
      RegExpr.Expression:='^Interesting ports on';

      if not A then begin
         if RegExpr.Exec(l.Strings[i]) then begin
            A:=True;
            comp.computername:='';
            comp.computerip:='';
            comp.computer_ports:='';
            comp.running:='';
            comp.OS:='';
            comp.uptime:='';
            comp.hop:='';
            comp.comput_type:='';
            comp.mac:='';
         
         
            RegExpr.Expression:='^Interesting ports on\s+(.+?)(\.home|\.local)\s+\(([0-9\.]+)';
            if RegExpr.Exec(l.Strings[i]) then begin
              comp.computername:=RegExpr.Match[1];
              comp.computerip:=RegExpr.Match[3];
            end;

            RegExpr.Expression:='^Interesting ports on\s+([0-9\.]+):';
            if RegExpr.Exec(l.Strings[i]) then begin
               comp.computerip:=RegExpr.Match[1];
            end;
         
            if verbose then begin
               if length(comp.computerip)=0 then writeln('failed for ' + l.Strings[i]);
            end;
         end;
      end;

      
      
      if A=true then begin
         RegExpr.Expression:='^[0-9]+/.+';
         if RegExpr.Exec(l.Strings[i]) then comp.computer_ports:=comp.computer_ports+ l.Strings[i] + CRLF;
         RegExpr.Expression:='^Running:\s+(.+)';
         if RegExpr.Exec(l.Strings[i]) then comp.Running:=RegExpr.Match[1];
         RegExpr.Expression:='^OS details:\s+(.+)';
         if RegExpr.Exec(l.Strings[i]) then comp.OS:=RegExpr.Match[1];
         RegExpr.Expression:='^Uptime:\s+(.+)';
         if RegExpr.Exec(l.Strings[i]) then comp.uptime:=RegExpr.Match[1];

         if length(comp.OS)=0 then begin
            RegExpr.Expression:='^Aggressive OS guesses:\s+(.+?)\(9[0-9]+';
            if RegExpr.Exec(l.Strings[i]) then comp.OS:=trim(RegExpr.Match[1]);
         end;


         
         RegExpr.Expression:='^MAC Address:\s+(.+?)\s+\((.+?)\)';
         if RegExpr.Exec(l.Strings[i]) then begin
             comp.mac:=RegExpr.Match[1];
             comp.comput_type:=RegExpr.Match[2];
         end;
         
         RegExpr.Expression:='^Network Distance:\s+(.+)';
         

         
         if RegExpr.Exec(l.Strings[i]) then begin
            comp.hop:=RegExpr.Match[1];
            A:=false;
            if verbose then begin
            
               writeln('');
               writeln('');
               writeln('computerip...........:',comp.computerip);
               writeln('mac..................:',comp.mac);
               writeln('comput_type..........:',comp.comput_type);
               writeln('computername.........:',comp.computername);
               writeln('computer_ports.......:',comp.computer_ports);
               writeln('running..............:',comp.running);
               writeln('OS...................:',comp.OS);
               writeln('Uptime...............:',comp.Uptime);
               writeln('Hop..................:',comp.hop);
            end;
            logs.nmap('NMAP()::['+mypid+'] Adding computer '+comp.computerip + '(' + comp.computername + ')');
            ldap.AddScannerComputer(comp);
            
         end;
         
      end;
    end;
    logs.DeleteFile(path);
  end;
   
 //############################################################################
procedure tldapconf.cyrreconstruct();
var l:TstringList;

begin

l:=TstringList.Create;
if FileExists(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct') then l.LoadFromFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
l.Add(logs.DateTimeNowSQL()+ ' starting reconstruct mailboxes');
l.SaveToFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');


  if ParamStr(1)='--cyrreconstruct' then begin
        if not FileExists(SYS.LOCATE_SU()) then begin
           writeln('unable to locate "su"');
           halt(0);
        end;
        if not FileExists(SYS.LOCATE_cyrreconstruct()) then begin
             writeln('unable to locate "cyrreconstruct"');
             halt(0);
        end;
       fpsystem('/bin/touch /etc/artica-postfix/cyrus-stop');
       l.Add(logs.DateTimeNowSQL()+ ' Reconstruct mailboxes... please waiting few minutes...');
       fpsystem(SYS.LOCATE_SU() + ' cyrus -c ' + SYS.LOCATE_cyrreconstruct()+' >>'+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
       fpsystem('echo "'+logs.DateTimeNowSQL()+' END...." >>'+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
       logs.DeleteFile('/etc/artica-postfix/cyrus-stop');
       halt(0);
  end;

l.Add(logs.DateTimeNowSQL()+ ' reconstruct mailboxes end');
l.SaveToFile(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyrreconstruct');
l.free;
end;
 //############################################################################
procedure tldapconf.cyrrepair();
begin
     cyr:=Tcyrus.Create(SYS);
     cyr.REPAIR_CYRUS();
     halt(0);
end;
end.

