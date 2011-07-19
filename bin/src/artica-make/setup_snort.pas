unit setup_snort;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,zsystem,
  install_generic;

  type
  install_snort=class


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
   ccflags_mem:string;
   SYS:Tsystem;
   function  version():string;





public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure rules();
      procedure daq();
      procedure install_dnet();
      procedure libpcap();
      procedure iptaccount();
END;

implementation

constructor install_snort.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
ccflags_mem:=' CXXFLAGS=-fPIC CFLAGS=-fPIC CPPFLAGS=-fPIC ';
end;
//#########################################################################################
procedure install_snort.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_snort.iptaccount();
var
   CODE_NAME:string;
   cmd:string;
   PERL_MODULES:string;
   configure_scripts:string;
   LOCAL_INTEGER:integer;
   REMOTE_INTEGER:integer;
   t:tstringlist;
   l:Tstringlist;
   i:integer;
   Arch:Integer;
   pkg:string;
   continues:boolean;
   php5bin:string;

begin
LOCAL_INTEGER:=0;
REMOTE_INTEGER:=0;
CODE_NAME:='APP_IPTACCOUNT';
continues:=false;
distri:=tdistriDetect.Create;
writeln('RESULT.................: Installing/Upgrading');
Arch:=libs.ArchStruct();
writeln('RESULT.................: Architecture : ',Arch);
writeln('RESULT.................: Distribution : ',distri.DISTRINAME,' (DISTRINAME)');
writeln('RESULT.................: Major version: ',distri.DISTRI_MAJOR,' (DISTRI_MAJOR)');
writeln('RESULT.................: Artica Code  : ',distri.DISTRINAME_CODE,' (DISTRINAME_CODE)');
if FileExists('/tmp/iptaccount_version') then fpsystem('/bin/rm -f /tmp/iptaccount_version');
if  distri.DISTRINAME_CODE='UBUNTU' then  continues:=true;
if  distri.DISTRINAME_CODE='DEBIAN' then  continues:=true;

if not continues then begin
       install.INSTALL_STATUS(CODE_NAME,110);
       writeln(distri.DISTRINAME_CODE, ' Not supported');
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       exit;
end;

install.INSTALL_STATUS(CODE_NAME,35);
writeln(distri.DISTRINAME_CODE, '{downloading}');
fpsystem('module-assistant prepare -i');
install.INSTALL_STATUS(CODE_NAME,50);
writeln(distri.DISTRINAME_CODE, '{compiling}');
fpsystem('module-assistant -i -f build xtables-addons');
install.INSTALL_STATUS(CODE_NAME,80);
writeln(distri.DISTRINAME_CODE, '{installing}');
fpsystem('module-assistant -i -f install xtables-addons');
writeln(distri.DISTRINAME_CODE, '{checking}');
install.INSTALL_STATUS(CODE_NAME,90);


php5bin:=SYS.LOCATE_PHP5_BIN();
fpsystem(php5bin+' /usr/share/artica-postfix/exec.xtables.php --account-module');
fpsystem('/usr/share/artica-postfix/bin/artica-install --iptaccount-version >/tmp/iptaccount_version 2>&1');
l:=Tstringlist.Create;
l.LoadFromFile('/tmp/iptaccount_version');
php5bin:=l.Strings[0];
writeln('RESULT.................: Version  : '+php5bin);

if length(trim(php5bin))>0 then begin
         writeln('RESULT.................: Success  : '+php5bin);
         install.INSTALL_STATUS(CODE_NAME,100);
        install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
        exit;
end;
         writeln('RESULT.................: Failed  : '+php5bin);
    install.INSTALL_STATUS(CODE_NAME,110);
    install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
end;
//#########################################################################################

procedure install_snort.install_dnet();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   libdir:string;
   ccflags:string;
begin
   CODE_NAME:='APP_LIBDNET';
   source_folder:=libs.COMPILE_GENERIC_APPS('libdnet');
   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-sources-'+zdate;
   if Not DirectoryExists(source_folder) then begin
      writeln('Failed');
   end;

   if length(source_folder)=0 then begin
      writeln('Failed');
      exit;
   end;

   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

  forceDirectories(smbsources);
  writeln('copy source files in  '+smbsources);
  fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
  writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(smbsources);
  if DirectoryExists('/usr/lib64') then begin
     libdir:=' --libdir=/usr/lib64 ';
     ccflags:=ccflags_mem;
  end;
  cmd:='./configure --prefix=/usr '+libdir+'--includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var'+ccflags;
  writeln(cmd);
  fpsystem(cmd);
  fpsystem('make && make install');

end;
//#########################################################################################
procedure install_snort.rules();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   ver:string;
   sourceDir:string;

begin
   CODE_NAME:='APP_RULES';
   ver:=version();
   writeln('Snort version = ',ver);
   if length(ver)=0 then begin
      writeln('Unable to get Snort version');
      exit

   end;
   source_folder:=libs.COMPILE_GENERIC_APPS('snortrules-snapshot'+ver);
   writeln('Sources was detected in : ',source_folder);

   if Not DirectoryExists(source_folder) then begin
      writeln('Failed');
   end;

   if length(source_folder)=0 then begin
      writeln('Failed');
      exit;
   end;


   sourceDir:=ExtractFileName(source_folder);
   writeln('Subdirectory............: ',sourceDir);
   source_folder:=ExtractFilePath(source_folder);
   writeln('New path................: ',source_folder);
   forceDirectories('/etc/snort/rules');
   forceDirectories('/etc/snort/preproc_rules');
   forceDirectories('/etc/snort/so_rules');
   if DirectoryExists('/etc/snort/so_rules') then fpsystem('/bin/rm -rf /etc/snort/so_rules/*');
   if DirectoryExists('/etc/snort/preproc_rules') then fpsystem('/bin/rm -rf /etc/snort/preproc_rules/*');

   if DirectoryExists(source_folder+'etc') then begin
       writeln('/bin/cp -rf '+source_folder+'etc/* /etc/snort/');
       fpsystem('/bin/cp -rf '+source_folder+'etc/* /etc/snort/');
   end;

   if DirectoryExists(source_folder+'rules') then begin
       fpsystem('/bin/rm -rf /etc/snort/rules/*');
       writeln('/bin/cp -rf '+source_folder+'rules/* /etc/snort/rules/');
       fpsystem('/bin/cp -rf '+source_folder+'rules/* /etc/snort/rules/');
   end;

   if DirectoryExists(source_folder+'preproc_rules') then begin
       fpsystem('/bin/rm -rf /etc/snort/preproc_rules/*');
       writeln('/bin/cp -rf '+source_folder+'preproc_rules/* /etc/snort/preproc_rules/');
       fpsystem('/bin/cp -rf '+source_folder+'preproc_rules/* /etc/snort/preproc_rules/');
   end;
   if DirectoryExists(source_folder+'so_rules') then begin
       fpsystem('/bin/rm -rf /etc/snort/so_rules/*');
       writeln('/bin/cp -rf '+source_folder+'so_rules/* /etc/snort/so_rules/');
       fpsystem('/bin/cp -rf '+source_folder+'so_rules/* /etc/snort/so_rules/');
   end;

   if DirectoryExists(source_folder) then begin
      writeln('Cleaning ',source_folder);
      fpsystem('/bin/rm -rf '+source_folder);
   end;
   SetCurrentDir('/root');

end;
//#########################################################################################
//#########################################################################################
function  install_snort.version():string;
var
   l:TstringList;
   RegExpr:TRegExpr;
   tmpstr:string;
   snortpath:string;
   i:integer;
   D:boolean;
begin
 D:=false;
 if ParamStr(2)='--verbose' then D:=true;

  tmpstr:='/tmp/snort.version';
  snortpath:=SYS.LOCATE_GENERIC_BIN('snort');
  if not fileExists(snortpath) then exit;
  fpsystem(snortpath+' -V >'+tmpstr+' 2>&1');
  l:=TstringList.create;
  l.LoadFromFile(tmpstr);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='Version\s+([0-9]+)\.([0-9]+)\.([0-9]+)';
  for i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
         result:=RegExpr.Match[1]+RegExpr.Match[2]+RegExpr.Match[3];
         RegExpr.free;
         l.free;
         exit(result);
      end else begin
          if D then writeln(l.Strings[i],' no match');
      end;
  end;
          RegExpr.free;
         l.free;


end;
procedure install_snort.libpcap();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   libdir:string;
   ccflags:string;
begin
   CODE_NAME:='APP_LIBPCAP';
   source_folder:=libs.COMPILE_GENERIC_APPS('libpcap');


   if Not DirectoryExists(source_folder) then begin
      writeln('Failed');
   end;

   if length(source_folder)=0 then begin
      writeln('Failed');
      exit;
   end;

   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-sources-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

  forceDirectories(smbsources);
  writeln('copy source files in  '+smbsources);
  fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
  writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(smbsources);
  if DirectoryExists('/usr/lib64') then begin
     libdir:=' --libdir=/usr/lib64 ';
      ccflags:=ccflags_mem;
  end;
  cmd:='./configure --prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var '+libdir+ccflags;
  writeln(cmd);
  fpsystem(cmd);
  fpsystem('make && make install');

end;
//#########################################################################################




procedure install_snort.daq();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   libdir:string;
   ccflags:string;
   compilestring:string;
begin
   CODE_NAME:='APP_DAQ';
   source_folder:=libs.COMPILE_GENERIC_APPS('daq');


   if Not DirectoryExists(source_folder) then begin
      writeln('Failed');
   end;

   if length(source_folder)=0 then begin
      writeln('Failed');
      exit;
   end;

   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-sources-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

  forceDirectories(smbsources);
  writeln('copy source files in  '+smbsources);
  fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
  writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(smbsources);
  if DirectoryExists('/usr/lib64') then begin
     libdir:=' --with-libpcap-libraries=/usr/lib64 --libdir=/usr/lib64 ';
     ccflags:=ccflags_mem;
  end;
  cmd:='./configure --prefix=/usr '+libdir+' --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var'+ccflags;
  compilestring:=cmd;
  writeln(cmd);
  fpsystem(cmd);
  fpsystem('make && make install');
  writeln('Was compiled with: ');
  writeln(compilestring);
  writeln('');

end;
//#########################################################################################
procedure install_snort.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   libdir:string;
   ccflags:string;
begin

    CODE_NAME:='APP_SNORT';


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);

  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,40);


  writeln('Install libpcap...');
  libpcap();
  writeln('Install DNET...');
  install_dnet();
  writeln('Install DAQ...');
  daq();


    source_folder:=libs.COMPILE_GENERIC_APPS('snort');

   if Not DirectoryExists(source_folder) then begin
      writeln('Failed');
      install.INSTALL_STATUS(CODE_NAME,110);
   end;

   if length(source_folder)=0 then begin
      writeln('Failed');
      install.INSTALL_STATUS(CODE_NAME,110);
      exit;
   end;


  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;


   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-sources-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir(smbsources);
// fpsystem('./autogen.sh');


 if DirectoryExists('/usr/lib64') then begin
    libdir:=' --with-libpcap-libraries=/usr/lib64 --libdir=/usr/lib64 --with-dnet-libraries=/usr/lib64 --with-daq-libraries=/usr/lib64 ';
    ccflags:=ccflags_mem;
 end;
 cmd:='./configure --prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var ';
 cmd:=cmd+libdir+' --enable-prelude --enable-reload --enable-ipv6 --with-mysql'+ccflags;
 writeln(cmd);
 fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make OPTIMIZE="-Wall -g -O2" && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');
  forceDirectories('/etc/snort');

  if FileExists(SYS.LOCATE_GENERIC_BIN('snort')) then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     fpsystem(paramstr(0)+' APP_SNORT_RULES &');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/bin/exec.snort.php --build');
     exit;
  end;
  writeln('Install '+CODE_NAME+' failed...');
  install.INSTALL_STATUS(CODE_NAME,110);
  exit;



end;
//#########################################################################################







end.
