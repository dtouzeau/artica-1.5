unit setup_samba;
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
  install_samba=class


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
   function SMBD_PATH():string;

   function talloc():boolean;
   procedure uninstall();
   function SAMBA_VERSION():string;
   function SAMBA_LIBDIR():string;
   function vfsDir(mainpath:string):string;
   function sourceDir(mainpath:string):string;
public
      constructor Create();
      procedure Free;
      procedure xinstall();
    procedure pdnsinstall();
    procedure scannedonly();
END;

implementation

constructor install_samba.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
end;
//#########################################################################################
procedure install_samba.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure install_samba.pdnsinstall();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
begin
  CODE_NAME:='APP_PDNS';
 install.INSTALL_STATUS(CODE_NAME,30);
 install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
 install.INSTALL_STATUS(CODE_NAME,35);
 fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-pdns');
 install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
 install.INSTALL_STATUS(CODE_NAME,80);
  if fileexists(SYS.LOCATE_PDNS_BIN()) then begin
    install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
    install.INSTALL_STATUS(CODE_NAME,100);
    fpsystem('/etc/init.d/artica-postfix restart pdns');
    exit;
  end;

 install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
    install.INSTALL_STATUS(CODE_NAME,110);

end;
//##############################################################################
procedure install_samba.scannedonly();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   LOCAL_VERSION,LIBDIR,REMOTE_VERSION,REMOTE_VERSION_SOURCE:string;
   LOCAL_VERSION_INT,REMOTE_VERSION_INT:integer;
begin
  CODE_NAME:='APP_SCANNED_ONLY';
  SetCurrentDir('/root');
  install.INSTALL_STATUS(CODE_NAME,10);
  LOCAL_VERSION:=SAMBA_VERSION();
  smbsources:='/usr/local/share/artica/samba/'+LOCAL_VERSION;

  if not DirectoryExists(smbsources) then begin
         writeln('unable to stat ',smbsources,' upgrade your samba server trough setup center');
         install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
         install.INSTALL_STATUS(CODE_NAME,110);
         exit;
    end;


    smbsources:=sourceDir(smbsources);

if not DirectoryExists(smbsources) then begin
         writeln('unable to stat sources',smbsources);
         install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
         install.INSTALL_STATUS(CODE_NAME,110);
         exit;
end;

 writeln('using ',smbsources ,' sources directory');
 LIBDIR:=vfsDir(SAMBA_LIBDIR());
 writeln('using ',LIBDIR ,' vfs directory');
 if not DirectoryExists(LIBDIR) then begin
         writeln('unable to stat vfs dir',LIBDIR);
         install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
         install.INSTALL_STATUS(CODE_NAME,110);
         exit;
 end;


 source_folder:=libs.COMPILE_GENERIC_APPS('scannedonly');

  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;
install.INSTALL_STATUS(CODE_NAME,50);
install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
 SetCurrentDir(source_folder);
 writeln('using source dir',source_folder);
 fpsystem('./configure --with-samba-source='+smbsources+' --with-samba-vfs-dir='+LIBDIR+' --prefix=/usr');
 fpsystem('make && make install');

 if not FileExists('/usr/sbin/scannedonlyd_clamav') then begin
     install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
 end;

install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
install.INSTALL_STATUS(CODE_NAME,100);



end;

//##############################################################################
function install_samba.sourceDir(mainpath:string):string;
begin
    if FIleExists(mainpath+'/source3/configure') then exit(mainpath+'/source3');
    if FIleExists(mainpath+'/source/configure') then exit(mainpath+'/source');


end;
//##############################################################################
function install_samba.vfsDir(mainpath:string):string;
begin
    if DirectoryExists(mainpath+'/vfs') then exit(mainpath+'/vfs');
    if DirectoryExists(mainpath+'/samba/vfs') then exit(mainpath+'/samba/vfs');
end;
//##############################################################################
procedure install_samba.xinstall();
var
   CODE_NAME:string;
   cmd:string;
   zdate:string;
   smbsources:string;
   l:Tstringlist;
   i:integer;
   LOCAL_VERSION,LOCAL_VERSION_SOURCE,REMOTE_VERSION,REMOTE_VERSION_SOURCE:string;
   LOCAL_VERSION_INT,REMOTE_VERSION_INT:integer;
begin

    CODE_NAME:='APP_SAMBA';
    SetCurrentDir('/root');
    install.INSTALL_STATUS(CODE_NAME,10);

    REMOTE_VERSION:=libs.COMPILE_VERSION_STRING('samba');
    REMOTE_VERSION_SOURCE:=REMOTE_VERSION;

    LOCAL_VERSION:=SAMBA_VERSION();
    LOCAL_VERSION_SOURCE:=LOCAL_VERSION;
    writeln('Install local version: ',LOCAL_VERSION,' remote version: ',REMOTE_VERSION);

if FileExists('/usr/sbin/scannedonlyd_clamav') then begin
    if LOCAL_VERSION=REMOTE_VERSION then begin
           writeln('No upgrade needed...');
           install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
           install.INSTALL_STATUS(CODE_NAME,100);
           exit;
    end;

    LOCAL_VERSION:=AnsiReplaceText(LOCAL_VERSION,'.','');
    REMOTE_VERSION:=AnsiReplaceText(REMOTE_VERSION,'.','');

    if not TryStrToInt(LOCAL_VERSION,LOCAL_VERSION_INT) then begin
       writeln('There is a problem to understand version string ',LOCAL_VERSION);
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       install.INSTALL_STATUS(CODE_NAME,110);
    end;

    if not TryStrToInt(REMOTE_VERSION,REMOTE_VERSION_INT) then begin
       writeln('There is a problem to understand version string ',REMOTE_VERSION);
       install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
       install.INSTALL_STATUS(CODE_NAME,110);
    end;


    if LOCAL_VERSION_INT>REMOTE_VERSION_INT then begin
           writeln('No upgrade needed...');
           install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
           install.INSTALL_STATUS(CODE_NAME,100);
           exit;
    end;

    if LOCAL_VERSION_INT=REMOTE_VERSION_INT then begin
           writeln('No upgrade needed...');
           install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
           install.INSTALL_STATUS(CODE_NAME,100);
           exit;
    end;
end;

  if not talloc() then begin
     writeln('Install talloc failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;
  SetCurrentDir('/root');
  uninstall();

  install.INSTALL_STATUS(CODE_NAME,30);
  install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
  install.INSTALL_STATUS(CODE_NAME,35);
  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('samba');



  if not DirectoryExists(source_folder) then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;




   smbsources:='/usr/local/share/artica/samba/'+REMOTE_VERSION_SOURCE;


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

  if DirectoryExists(smbsources+'/source3') then SetCurrentDir(smbsources+'/source3');
  if DirectoryExists(smbsources+'/source') then SetCurrentDir(smbsources+'/source');

  writeln('using source files in  '+GetCurrentDir());


 fpsystem('./autogen.sh');

cmd:='./configure';
cmd:=cmd+' --with-fhs';
cmd:=cmd+' --enable-shared';
cmd:=cmd+' --enable-static';
cmd:=cmd+' --disable-pie';
cmd:=cmd+' --prefix=/usr';
cmd:=cmd+' --sysconfdir=/etc';
cmd:=cmd+' --libdir=/usr/lib';
cmd:=cmd+' --with-privatedir=/etc/samba';
cmd:=cmd+' --with-piddir=/var/run/samba';
cmd:=cmd+' --localstatedir=/var';
cmd:=cmd+' --with-rootsbindir=/sbin';
cmd:=cmd+' --with-pammodulesdir=/lib/security';
cmd:=cmd+' --with-pam';
cmd:=cmd+' --with-syslog';
cmd:=cmd+' --with-utmp';
cmd:=cmd+' --with-readline';
cmd:=cmd+' --with-pam_smbpass';
cmd:=cmd+' --with-libsmbclient';
cmd:=cmd+' --with-winbind';
cmd:=cmd+' --with-shared-modules=idmap_rid,idmap_ad';
cmd:=cmd+' --with-automount';
cmd:=cmd+' --with-ldap';
cmd:=cmd+' --with-ads';
cmd:=cmd+' --with-dnsupdate';
cmd:=cmd+' --with-smbmount';
cmd:=cmd+' --with-cifsmount';
cmd:=cmd+' --with-acl-support';
cmd:=cmd+' --with-dnsupdate';
cmd:=cmd+' --with-quotas';
cmd:=cmd+' --with-automount';

  writeln(cmd);
  fpsystem(cmd);

  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
  install.INSTALL_STATUS(CODE_NAME,80);
  fpsystem('make && make install');
  fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
  SetCurrentDir('/root');

  if FileExists('/usr/sbin/smbd') then begin
     LOCAL_VERSION:=SAMBA_VERSION();
     writeln('New local version: '+LOCAL_VERSION);
     install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
     install.INSTALL_STATUS(CODE_NAME,100);

     if FIleExists('/usr/lib/libtalloc.so.1.3.0') then begin
      writeln('Install linking /usr/lib/libtalloc.so.1.3.0 to /usr/lib/libtalloc.so.1');
      fpsystem('/bin/ln -s /usr/lib/libtalloc.so.1.3.0 /usr/lib/libtalloc.so.1');
    end;
     scannedonly();
     exit;
  end;

  if FIleExists('/usr/lib/libtalloc.so.1.3.0') then begin
     writeln('Install linking /usr/lib/libtalloc.so.1.3.0 to /usr/lib/libtalloc.so.1');
     fpsystem('/bin/ln -s /usr/lib/libtalloc.so.1.3.0 /usr/lib/libtalloc.so.1');
  end;

     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;



end;
//#########################################################################################
function install_samba.SMBD_PATH():string;
begin
 if FileExists('/usr/sbin/smbd') then exit('/usr/sbin/smbd');
end;
//##############################################################################
function install_samba.SAMBA_VERSION():string;
var
   RegExpr:TRegExpr;
   x:string;
begin

fpsystem(SMBD_PATH() + ' -V >/tmp/samba.version');
x:=libs.ReadFromFile('/tmp/samba.version');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='Version\s+([0-9a-z\.]+)';
if RegExpr.Exec(x) then result:=trim(RegExpr.Match[1]);
end;
//##############################################################################
function install_samba.SAMBA_LIBDIR():string;
var
   RegExpr:TRegExpr;
   x:TstringList;
   i:Integer;
begin

fpsystem(SMBD_PATH() + ' -b >/tmp/samba.infos 2>&1');
x:=Tstringlist.Create;
x.LoadFromFile('/tmp/samba.infos');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='LIBDIR:\s+(.+)';
for i:=0 to x.Count-1 do begin
     if RegExpr.Exec(x.Strings[i]) then begin
        result:=trim(RegExpr.Match[1]);
        break;
     end;

end;
x.free;
RegExpr.free;
end;
//##############################################################################


function install_samba.talloc():boolean;
var
   l:Tstringlist;
   i:integer;
begin

if FileExists('/usr/lib/libtalloc.so.1.3.0') then begin
   SetCurrentDir('/root');
   writeln('libtalloc 1.3 ok');
   exit(true);
end;

 result:=false;

 l:=Tstringlist.Create;
 l.Add('libtalloc.a');
 l.Add('libtalloc.so');
 l.add('libtalloc.so.1');
 l.add('libtalloc.so.1.2.0');
 for i:=0 to l.Count-1 do begin
     if FIleExists('/usr/lib/'+ l.Strings[i]) then begin
        writeln('removing /usr/lib/'+ l.Strings[i]);
        fpsystem('/bin/rm -f /usr/lib/'+ l.Strings[i]);

     end;

     if FileExists('/etc/samba/'+ l.Strings[i]) then begin
        writeln('removing /etc/samba/'+ l.Strings[i]);
        fpsystem('/bin/rm -f /etc/samba/'+ l.Strings[i]);
     end;
 end;
 source_folder:=libs.COMPILE_GENERIC_APPS('talloc');
 if not DirectoryExists(source_folder) then begin
     writeln('Install talloc failed !!!...');
     source_folder:='';
     exit;
 end;
 SetCurrentDir(source_folder);
 fpsystem('./configure --prefix=/usr');
 fpsystem('make && make install');
 if FileExists('/usr/lib/libtalloc.so.1.3.0') then begin
    SetCurrentDir('/root');
    writeln('libtalloc 1.3 ok');
    source_folder:='';
    exit(true);
 end;
 source_folder:='';

end;


procedure install_samba.uninstall();
var
   l:Tstringlist;
   i:integer;
begin
exit;
l:=Tstringlist.Create;
l.add('/etc/samba/libtalloc.a');
l.add('/etc/samba/gdbcommands');
l.add('/etc/samba/samba/ja.msg');
l.add('/etc/samba/samba/upcase.dat');
l.add('/etc/samba/samba/tr.msg');
l.add('/etc/samba/samba/idmap/rid.so');
l.add('/etc/samba/samba/idmap/ad.so.old');
l.add('/etc/samba/samba/idmap/ad.so');
l.add('/etc/samba/samba/idmap/rid.so.old');
l.add('/etc/samba/samba/auth/script.so.old');
l.add('/etc/samba/samba/auth/script.so');
l.add('/etc/samba/samba/charset/CP437.so.old');
l.add('/etc/samba/samba/charset/CP850.so.old');
l.add('/etc/samba/samba/charset/CP850.so');
l.add('/etc/samba/samba/charset/CP437.so');
l.add('/etc/samba/samba/en.msg');
l.add('/etc/samba/samba/vfs/netatalk.so.old');
l.add('/etc/samba/samba/vfs/syncops.so.old');
l.add('/etc/samba/samba/vfs/default_quota.so');
l.add('/etc/samba/samba/vfs/default_quota.so.old');
l.add('/etc/samba/samba/vfs/readahead.so.old');
l.add('/etc/samba/samba/vfs/shadow_copy.so.old');
l.add('/etc/samba/samba/vfs/fileid.so.old');
l.add('/etc/samba/samba/vfs/extd_audit.so.old');
l.add('/etc/samba/samba/vfs/netatalk.so');
l.add('/etc/samba/samba/vfs/shadow_copy2.so');
l.add('/etc/samba/samba/vfs/streams_depot.so.old');
l.add('/etc/samba/samba/vfs/shadow_copy2.so.old');
l.add('/etc/samba/samba/vfs/smb_traffic_analyzer.so.old');
l.add('/etc/samba/samba/vfs/recycle.so.old');
l.add('/etc/samba/samba/vfs/readonly.so');
l.add('/etc/samba/samba/vfs/readonly.so.old');
l.add('/etc/samba/samba/vfs/dirsort.so.old');
l.add('/etc/samba/samba/vfs/full_audit.so');
l.add('/etc/samba/samba/vfs/streams_xattr.so');
l.add('/etc/samba/samba/vfs/acl_xattr.so.old');
l.add('/etc/samba/samba/vfs/cap.so.old');
l.add('/etc/samba/samba/vfs/readahead.so');
l.add('/etc/samba/samba/vfs/streams_xattr.so.old');
l.add('/etc/samba/samba/vfs/preopen.so');
l.add('/etc/samba/samba/vfs/expand_msdfs.so');
l.add('/etc/samba/samba/vfs/acl_tdb.so.old');
l.add('/etc/samba/samba/vfs/xattr_tdb.so.old');
l.add('/etc/samba/samba/vfs/preopen.so.old');
l.add('/etc/samba/samba/vfs/acl_tdb.so');
l.add('/etc/samba/samba/vfs/smb_traffic_analyzer.so');
l.add('/etc/samba/samba/vfs/shadow_copy.so');
l.add('/etc/samba/samba/vfs/acl_xattr.so');
l.add('/etc/samba/samba/vfs/audit.so.old');
l.add('/etc/samba/samba/vfs/audit.so');
l.add('/etc/samba/samba/vfs/streams_depot.so');
l.add('/etc/samba/samba/vfs/dirsort.so');
l.add('/etc/samba/samba/vfs/fake_perms.so');
l.add('/etc/samba/samba/vfs/full_audit.so.old');
l.add('/etc/samba/samba/vfs/fake_perms.so.old');
l.add('/etc/samba/samba/vfs/syncops.so');
l.add('/etc/samba/samba/vfs/expand_msdfs.so.old');
l.add('/etc/samba/samba/vfs/fileid.so');
l.add('/etc/samba/samba/vfs/extd_audit.so');
l.add('/etc/samba/samba/vfs/cap.so');
l.add('/etc/samba/samba/vfs/xattr_tdb.so');
l.add('/etc/samba/samba/vfs/recycle.so');
l.add('/etc/samba/samba/nss_info/rfc2307.so');
l.add('/etc/samba/samba/nss_info/sfu.so');
l.add('/etc/samba/samba/nss_info/sfu20.so');
l.add('/etc/samba/samba/fr.msg');
l.add('/etc/samba/samba/lowcase.dat');
l.add('/etc/samba/samba/ru.msg');
l.add('/etc/samba/samba/fi.msg');
l.add('/etc/samba/samba/it.msg');
l.add('/etc/samba/samba/valid.dat');
l.add('/etc/samba/samba/de.msg');
l.add('/etc/samba/samba/pl.msg');
l.add('/etc/samba/samba/nl.msg');
l.add('/etc/samba/libwbclient.so');
l.add('/etc/samba/libnetapi.so.0');
l.add('/etc/samba/libsmbsharemodes.so');
l.add('/etc/samba/libsmbclient.a');
l.add('/etc/samba/libnetapi.a');
l.add('/etc/samba/libsmbsharemodes.so.0');
l.add('/etc/samba/pkgconfig/talloc.pc');
l.add('/etc/samba/libnetapi.so');
l.add('/etc/samba/smb.conf.ucf-dist');
l.add('/etc/samba/libtalloc.so.1.3.0');
l.add('/etc/samba/libtdb.so');
l.add('/etc/samba/libsmbsharemodes.a');
l.add('/etc/samba/libtdb.so.1');
l.add('/etc/samba/libsmbclient.so');
if DirectoryExists('/usr/lib/samba') then fpsystem('/bin/rm -rf /usr/lib/samba');

 for i:=0 to l.Count-1 do begin
     if FIleExists(l.Strings[i]) then begin
        writeln('removing '+ l.Strings[i]);
        fpsystem('/bin/rm -f '+ l.Strings[i]);
     end;
 end;

end;

end.
