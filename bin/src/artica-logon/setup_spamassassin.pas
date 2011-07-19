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


    writeln('PERL_MODULES='+PERL_MODULES);
   if length(PERL_MODULES)>0 then begin
        install.INSTALL_STATUS(CODE_NAME,100);
        writeln('installed');
        install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
        install.INSTALL_STATUS(CODE_NAME,100);
        exit;
   end;

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

end;


end.
