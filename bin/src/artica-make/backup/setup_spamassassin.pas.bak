unit setup_spamassassin;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,postfix_class,zsystem,
  install_generic;

  type
  tspam=class


private
     libs:tlibs;
     distri:tdistriDetect;
     install:tinstall;
   source_folder,cmd:string;
   webserver_port:string;
   artica_admin:string;
   artica_password:string;
   ldap_suffix:string;
   mysql_server:string;
   mysql_admin:string;
   mysql_password:string;
   ldap_server:string;
   postfix:tpostfix;
   SYS:Tsystem;
   procedure DomainKeys();
   procedure MAIL_DKIM();
   procedure spamassassin_remove();



public
      constructor Create();
      procedure Free;
      procedure xinstall();

END;

implementation

constructor tspam.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure tspam.Free();
begin
  libs.Free;

end;

//#########################################################################################

procedure tspam.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   PERL_MODULES:string;
   REMOTE_SPAMASSASSIN_VER_STRING:string;
begin

    CODE_NAME:='APP_SPAMASSASSIN';



   SetCurrentDir('/root');
   install.INSTALL_STATUS(CODE_NAME,10);
   install.INSTALL_STATUS(CODE_NAME,30);
   install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');




   libs.PERL_GENERIC_INSTALL('Sys-Hostname-Long','Sys::Hostname::Long');
   libs.PERL_GENERIC_INSTALL('Net-DNS-Resolver-Programmable','Net::DNS::Resolver::Programmable');
   libs.PERL_GENERIC_INSTALL('NetAddr-IP','NetAddr::IP');
   libs.PERL_GENERIC_INSTALL('Net-CIDR-Lite','Net::CIDR::Lite');



   libs.PERL_GENERIC_INSTALL('Mail-SPF','Mail::SPF');
   libs.PERL_GENERIC_INSTALL('Mail-SPF-Query','Mail::SPF::Query');
   libs.PERL_GENERIC_INSTALL('IP-Country','IP::Country');
   libs.PERL_GENERIC_INSTALL('Net-Ident','Net::Ident');
   DomainKeys();
   libs.PERL_GENERIC_INSTALL('Encode-Detect','Encode::Detect');

   PERL_MODULES:=libs.CHECK_PERL_MODULES('Mail::SpamAssassin');
   REMOTE_SPAMASSASSIN_VER_STRING:=libs.COMPILE_VERSION_STRING('Mail-SpamAssassin');
   MAIL_DKIM();
   PERL_MODULES:=AnsiReplaceText(PERL_MODULES,'00','.');
   PERL_MODULES:=AnsiReplaceText(PERL_MODULES,'..','.');
   writeln('Local version: ',PERL_MODULES,'(',libs.VersionToInteger(PERL_MODULES),') remote version:', REMOTE_SPAMASSASSIN_VER_STRING,' (',libs.VersionToInteger(REMOTE_SPAMASSASSIN_VER_STRING),')');



   if libs.VersionToInteger(PERL_MODULES)>=libs.VersionToInteger(REMOTE_SPAMASSASSIN_VER_STRING) then begin
        install.INSTALL_STATUS(CODE_NAME,100);
        writeln('installed');
        install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
        install.INSTALL_STATUS(CODE_NAME,100);
        exit;
   end;
  writeln('Removing old installations');
  spamassassin_remove();


  writeln('Checking Artica repository');
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('Mail-SpamAssassin');


  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;

  SetCurrentDir(source_folder);
  libs.PERL_GENERIC_DISABLE_TESTS(source_folder);
  cmd:='perl Makefile.PL PREFIX=/usr CONTACT_ADDRESS=root@localhost PREFIX=/usr DATADIR=/usr/share/spamassassin LOCALSTATEDIR=/var/lib/spamassassin CONFDIR=/etc/spamassassin';
  writeln('Using ' + cmd);
  fpsystem(cmd);
  fpsystem('make && make install');
  SetCurrentDir('/root');

if(length(libs.CHECK_PERL_MODULES('Mail::SpamAssassin')))>0 then begin
        install.INSTALL_STATUS(CODE_NAME,100);
        writeln('installed');
        install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
        install.INSTALL_STATUS(CODE_NAME,100);
        exit;
   end;

  install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
  install.INSTALL_STATUS(CODE_NAME,110);
  exit;


end;
//#########################################################################################
procedure tspam.MAIL_DKIM();
var
  MAIL_DKIM_VER:string;
  PERL_MODULES:string;
begin

PERL_MODULES:=libs.CHECK_PERL_MODULES('Mail::DKIM');
MAIL_DKIM_VER:=libs.COMPILE_VERSION_STRING('Mail-DKIM');
 writeln('DKIM: Local version: ',PERL_MODULES,'(',libs.VersionToInteger(PERL_MODULES),') remote version:', MAIL_DKIM_VER,' (',libs.VersionToInteger(MAIL_DKIM_VER),')');
if libs.VersionToInteger(PERL_MODULES)>=libs.VersionToInteger(MAIL_DKIM_VER) then begin
   writeln('DKIM no update');
   exit;
   end;

 if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('Mail-DKIM');
  if not DirectoryExists(source_folder) then begin
     writeln('Install Mail-DKIM failed...');
     exit;
  end;

  SetCurrentDir(source_folder);
  cmd:='perl Makefile.PL';
  writeln('Using ' + cmd);
  fpsystem(cmd);
  fpsystem('make && make install');
  SetCurrentDir('/root');
  fpsystem('/bin/rm -rf '+source_folder);

end;
//#########################################################################################
procedure tspam.spamassassin_remove();
var
   i:integer;
   l:tstringlist;
begin

l:=Tstringlist.Create;
l.add('/usr/bin/sa-awl');
l.add('/usr/bin/sa-check_spamd');
l.add('/usr/bin/sa-compile');
l.add('/usr/bin/sa-learn');
l.add('/usr/bin/sa-update');
l.add('/usr/bin/spamassassin');
l.add('/usr/sbin/spamd');
l.add('/usr/share/perl5/Mail/SpamAssassin.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/AICache.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/ArchiveIterator.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/AsyncLoop.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/AutoWhitelist.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Bayes.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Bayes/CombineChi.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Bayes/CombineNaiveBayes.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/BayesStore.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/BayesStore/BDB.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/BayesStore/DBM.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/BayesStore/MySQL.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/BayesStore/PgSQL.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/BayesStore/SDBM.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/BayesStore/SQL.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Client.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Conf.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Conf/LDAP.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Conf/Parser.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Conf/SQL.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Constants.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/DBBasedAddrList.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Dns.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/DnsResolver.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/HTML.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Locales.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Locker.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Locker/Flock.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Locker/UnixNFSSafe.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Locker/Win32.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Logger.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Logger/File.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Logger/Stderr.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Logger/Syslog.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/MailingList.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Message.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Message/Metadata.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Message/Metadata/Received.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Message/Node.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/NetSet.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/PerMsgLearner.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/PerMsgStatus.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/PersistentAddrList.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/ASN.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/AWL.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/AccessDB.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/AntiVirus.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/AutoLearnThreshold.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Bayes.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/BodyEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/BodyRuleBaseExtractor.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Check.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/DCC.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/DKIM.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/DNSEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/FreeMail.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/HTMLEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/HTTPSMismatch.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Hashcash.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/HeaderEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/ImageInfo.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/MIMEEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/MIMEHeader.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/OneLineBodyRuleType.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/PhishTag.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Pyzor.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Razor2.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/RelayCountry.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/RelayEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/ReplaceTags.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Reuse.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Rule2XSBody.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/SPF.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Shortcircuit.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/SpamCop.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/Test.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/TextCat.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/URIDNSBL.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/URIDetail.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/URIEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/VBounce.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/WLBLEval.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Plugin/WhiteListSubject.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/PluginHandler.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Reporter.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/SQLBasedAddrList.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/SpamdForkScaling.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/SubProcBackChannel.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Timeout.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Util.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Util/DependencyInfo.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Util/Progress.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Util/RegistrarBoundaries.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Util/ScopedTimer.pm');
l.add('/usr/share/perl5/Mail/SpamAssassin/Util/TieOneStringHash.pm');
l.add('/usr/share/perl5/spamassassin-run.pod');

for i:=0 to l.Count-1 do begin
   if FileExists(l.Strings[i]) then begin
      writeln('removing '+l.Strings[i]);
      fpsystem('/bin/rm -f '+l.Strings[i]);
   end;
end;


end;


procedure tspam.DomainKeys();
var
 cmd:string;
begin
  if(length(libs.CHECK_PERL_MODULES('Mail::DomainKeys')))>0 then exit;
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('Mail-DomainKeys');

  if not DirectoryExists(source_folder) then begin
     writeln('Install Mail::DomainKeys failed...');
     exit;
  end;

  SetCurrentDir(source_folder);
  libs.PERL_GENERIC_DISABLE_TESTS(source_folder);
  cmd:='echo n|perl Makefile.PL';
  fpsystem(cmd);
  fpsystem('make && make install');
  SetCurrentDir('/root');
  fpsystem('/bin/rm -rf '+source_folder);

end;


end.
