unit setup_glusterfs;
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
  install_glusterfs=class


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
   SYS:Tsystem;




public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure fuseinstall();
      procedure fuse();
      procedure zfsfuseinstall();
      procedure tokyocabinet();
      procedure lessfs();
      procedure mhash();
      procedure hamsterdb();
END;

implementation

constructor install_glusterfs.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure install_glusterfs.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_glusterfs.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
begin

    CODE_NAME:='APP_GLUSTERFS';


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  fuseinstall();
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,40);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('glusterfs');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/gluster-sources-'+zdate;
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

 cmd:='./configure -prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info" --sysconfdir=/etc --localstatedir=/var --libexecdir="\${prefix}/lib/glusterfs"';
 writeln(cmd);
 fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/sbin/glusterfsd') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;
  end;



     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;



end;
//#########################################################################################
procedure install_glusterfs.fuse();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   ham_info:string;
begin
    // fusermount
    CODE_NAME:='APP_FUSE';
  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,15);
  install.INSTALL_STATUS(CODE_NAME,20);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,25);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('fuse');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/gluster-sources-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{configure}');
 SetCurrentDir(smbsources);
// fpsystem('./autogen.sh');



 cmd:='CFLAGS="-g -O2 -Wall -g -O2" ./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info --disable-example';
 writeln(cmd);
 fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  install.INSTALL_STATUS(CODE_NAME,50);
  fpsystem('make');
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make install');

  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists(SYS.LOCATE_GENERIC_BIN('fusermount'))then begin
     if FileExists('/usr/lib/libfuse.so.2') then fpsystem('ln -s -f /usr/lib/libfuse.so.2 /lib/libfuse.so.2');
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,95);
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     install.INSTALL_STATUS(CODE_NAME,100);
     fpsystem('/usr/share/artica-postfix/bin/artica-install --write-versions &');
     fpsystem('/etc/init.d/artica-postfix restart fuse');
     exit;
  end;

  install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
  writeln('Install '+CODE_NAME+' failed...');
  install.INSTALL_STATUS(CODE_NAME,110);
  if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
end;



//#########################################################################################
procedure install_glusterfs.fuseinstall();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
begin

    CODE_NAME:='APP_GLUSTERFS';
    if FIleExists('/usr/bin/fusermount') then exit;
    if FileExists('/bin/fusermount') then exit;

  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,15);
  install.INSTALL_STATUS(CODE_NAME,20);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,25);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('glfsfuse');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/gluster-sources-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
 SetCurrentDir(smbsources);
// fpsystem('./autogen.sh');

 cmd:='./configure -prefix=/usr';
 writeln(cmd);
 fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,35);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/bin/fusermount') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;
  end;



     writeln('Install '+CODE_NAME+' failed...');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;



end;
//#########################################################################################
procedure install_glusterfs.zfsfuseinstall();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
begin

    CODE_NAME:='APP_ZFS_FUSE';
    if not FIleExists('/usr/bin/fusermount') then  begin
      writeln('Install '+CODE_NAME+' failed fusermount no such file...');
      install.INSTALL_STATUS(CODE_NAME,110);
      exit;
    end;


  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,15);
  install.INSTALL_STATUS(CODE_NAME,20);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,25);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('zfs-fuse');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
   install.INSTALL_STATUS(CODE_NAME,30);
   install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
   SetCurrentDir(smbsources+'/src');

   cmd:='scons';
   writeln(cmd);
   install.INSTALL_STATUS(CODE_NAME,50);
   fpsystem(cmd);

 cmd:='scons install install_dir=/usr/sbin';
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
 install.INSTALL_STATUS(CODE_NAME,70);
 writeln(cmd);
 fpsystem(cmd);



  install.INSTALL_STATUS(CODE_NAME,90);
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/sbin/zfs-fuse') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;
  end;


     install.INSTALL_STATUS(CODE_NAME,110);
     writeln('Install '+CODE_NAME+' failed...');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;



end;
//#########################################################################################
procedure install_glusterfs.tokyocabinet();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
begin

    CODE_NAME:='APP_TOKYOCABINET';
    SetCurrentDir('/root');
    install.INSTALL_STATUS(CODE_NAME,15);
    install.INSTALL_STATUS(CODE_NAME,20);
    install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
    install.INSTALL_STATUS(CODE_NAME,25);
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('tokyocabinet');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{checking}');
 SetCurrentDir(smbsources);
// fpsystem('./autogen.sh');

 cmd:='./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info --enable-devel --enable-off64 --enable-swab CFLAGS="-g -Wall -Wextra -O2" LDFLAGS="-Wl,-z,defs"';
  writeln(cmd);
  fpsystem(cmd);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  install.INSTALL_STATUS(CODE_NAME,75);
  cmd:='make';
  writeln(cmd);
  fpsystem(cmd);
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,90);
  fpsystem('make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/lib/libtokyocabinet.so') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     fpsystem('/usr/share/artica-postfix/bin/artica-install --write-versions &');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;
  end;


     install.INSTALL_STATUS(CODE_NAME,110);
     writeln('Install '+CODE_NAME+' failed...');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;



end;

//#########################################################################################
procedure install_glusterfs.hamsterdb();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
begin

    CODE_NAME:='APP_HAMSTERDB';
    SetCurrentDir('/root');
    install.INSTALL_STATUS(CODE_NAME,15);
    install.INSTALL_STATUS(CODE_NAME,20);
    install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
    install.INSTALL_STATUS(CODE_NAME,25);
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('hamsterdb');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{checking}');
 SetCurrentDir(smbsources);
// fpsystem('./autogen.sh');

 cmd:='./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info --disable-remote';
  writeln(cmd);
  fpsystem(cmd);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  install.INSTALL_STATUS(CODE_NAME,75);
  cmd:='make';
  writeln(cmd);
  fpsystem(cmd);
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,90);
  fpsystem('make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/bin/ham_info') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     fpsystem('/usr/share/artica-postfix/bin/artica-install --write-versions &');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;
  end;


     install.INSTALL_STATUS(CODE_NAME,110);
     writeln('Install '+CODE_NAME+' failed...');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;



end;

//#########################################################################################



procedure install_glusterfs.lessfs();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   ham_info:string;
begin

    CODE_NAME:='APP_LESSFS';
    SetCurrentDir('/root');
    install.INSTALL_STATUS(CODE_NAME,15);
    install.INSTALL_STATUS(CODE_NAME,20);
    install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
    install.INSTALL_STATUS(CODE_NAME,25);
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('lessfs');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-'+zdate;
   writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
   if DirectoryExists(smbsources) then begin
       writeln('Install '+CODE_NAME+' removing old sources');
       fpsystem('/bin/rm -rf '+smbsources);
   end;

   forceDirectories(smbsources);
   writeln('copy source files in  '+smbsources);
   fpsystem('/bin/cp -rf '+source_folder+'/* '+smbsources+'/');
   writeln('copy source files in  '+smbsources +' done');
  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');

// fpsystem('./autogen.sh');

if not FileExists('/usr/include/mutils/mglobal.h') then mhash();

//CFLAGS="-g -Wall -Wextra -O2" LDFLAGS="-Wl,-z,defs"
 SetCurrentDir(smbsources);
    if FileExists(SYS.LOCATE_GENERIC_BIN('ham_info')) then ham_info:=' --with-hamsterdb';
 cmd:='./configure --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info --with-crypto'+ham_info;
 writeln(cmd);
 fpsystem(cmd);
 install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
 install.INSTALL_STATUS(CODE_NAME,75);
 cmd:='make';
 writeln(cmd);
 fpsystem(cmd);

 install.INSTALL_STATUS(CODE_NAME,90);
 install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
 cmd:='make install';
 writeln(cmd);
 fpsystem(cmd);

 fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
 SetCurrentDir('/root');

 if FileExists('/usr/bin/lessfs') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);
     fpsystem('/usr/share/artica-postfix/bin/process1 --force &');
     if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
     exit;
  end;


 install.INSTALL_STATUS(CODE_NAME,110);
 writeln('Install '+CODE_NAME+' failed...');
 if DirectoryExists(smbsources) then fpsystem('/bin/rm -rf '+smbsources);
 exit;



end;

//#########################################################################################
procedure install_glusterfs.mhash();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
begin

    CODE_NAME:='APP_LESSFS';
    SetCurrentDir('/root');
    install.INSTALL_STATUS(CODE_NAME,35);
    install.INSTALL_STATUS(CODE_NAME,40);
    install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
    install.INSTALL_STATUS(CODE_NAME,45);
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('mhash');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     exit;
  end;



   zdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
   smbsources:='/root/'+CODE_NAME+'-'+zdate;
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

//CFLAGS="-g -Wall -Wextra -O2" LDFLAGS="-Wl,-z,defs"
 cmd:='./configure ';
cmd:=cmd+' --program-prefix=';
cmd:=cmd+' --prefix=/usr';
cmd:=cmd+' --exec-prefix=/usr';
cmd:=cmd+' --bindir=/usr/bin';
cmd:=cmd+' --sbindir=/usr/sbin';
cmd:=cmd+' --sysconfdir=/etc';
cmd:=cmd+' --datadir=/usr/share';
cmd:=cmd+' --includedir=/usr/include';
cmd:=cmd+' --libdir=/usr/lib';
cmd:=cmd+' --libexecdir=/usr/libexec';
cmd:=cmd+' --localstatedir=/var';
cmd:=cmd+' --sharedstatedir=/usr/com';
cmd:=cmd+' --mandir=/usr/share/man';
cmd:=cmd+' --infodir=/usr/share/info';
cmd:=cmd+' --disable-dependency-tracking';
cmd:=cmd+' --enable-static';
cmd:=cmd+' --enable-shared';

 writeln(cmd);
 fpsystem(cmd);
 install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
 install.INSTALL_STATUS(CODE_NAME,60);
 cmd:='make && make install';
 writeln(cmd);
 fpsystem(cmd);
 fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
end;
end.
