unit setup_cups;
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
  tcups_install=class


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
      procedure cupsdrivers();
      function gutenprint():boolean;
      function foo2zjs():boolean;
      procedure cupsBrother();
      procedure hpinlinux();


END;

implementation

constructor tcups_install.Create();
begin
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
SYS:=Tsystem.Create();
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);

end;
//#########################################################################################
procedure tcups_install.Free();
begin
  libs.Free;

end;

//#########################################################################################
procedure tcups_install.cupsBrother();
var
   CODE_NAME:string;
   cmd:string;
   i:integer;
   path:string;
begin
   ///home/dtouzeau/Bureau/cups-drivers
   CODE_NAME:='APP_CUPS_BROTHER';
   SetCurrentDir('/root');
   install.INSTALL_STATUS(CODE_NAME,1);
   install.INSTALL_STATUS(CODE_NAME,2);
   install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');


   fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-samba');
   if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('brother-drivers');
      install.INSTALL_STATUS(CODE_NAME,30);

    SYS.DirDir(source_folder+'/lpr');
    for i:=0 to SYS.DirListFiles.Count-1 do begin
       path:=source_folder+'/lpr/'+SYS.DirListFiles.Strings[i];
       writeln(path);
       if directoryExists(path+'/var') then fpsystem('/bin/cp -rf '+path+'/var/* /var/');
       if directoryExists(path+'/usr') then fpsystem('/bin/cp -rf '+path+'/usr/* /usr/');
       if FileExists(path+'/control/postinst') then begin
           fpsystem('/bin/chmod 777 '+path+'/control/postinst');
           fpsystem(path+'/control/postinst');
       end;
    end;

   SYS.DirDir(source_folder);
   install.INSTALL_STATUS(CODE_NAME,50);
   install.INSTALL_PROGRESS(CODE_NAME,'{installing}');
   for i:=0 to SYS.DirListFiles.Count-1 do begin
         path:=source_folder+'/'+SYS.DirListFiles.Strings[i];
         install.INSTALL_PROGRESS(CODE_NAME,ExtractFileName(SYS.DirListFiles.Strings[i]));

         if DirectoryExists(path+'/usr') then begin
            fpsystem('/bin/cp -rf '+path+'/usr/* /usr/');
         end;

         if FileExists(path+'/control/postinst') then begin
            fpsystem('/bin/chmod 777 '+path+'/control/postinst');
            fpsystem(path+'/control/postinst');
         end;

   end;


   fpsystem('/bin/cp '+source_folder+'/VERSION /usr/local/Brother/VERSION');
   install.INSTALL_STATUS(CODE_NAME,100);
   install.INSTALL_PROGRESS(CODE_NAME,'{installed}');

   fpsystem('/usr/share/artica-postfix/bin/artica-install --cups-delete-all-printers');
   fpsystem('/usr/share/artica-postfix/bin/artica-install --cups-drivers --force &');

end;
//#########################################################################################


procedure tcups_install.cupsdrivers();
var
   CODE_NAME:string;
   cmd:string;
begin

   CODE_NAME:='APP_CUPS_DRV';
   SetCurrentDir('/root');
   install.INSTALL_STATUS(CODE_NAME,10);
   install.INSTALL_STATUS(CODE_NAME,30);
   install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');

   fpsystem('/usr/share/artica-postfix/bin/setup-ubuntu --check-samba');

   if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('cups-drv');




  if not DirectoryExists(source_folder+'/model/foomatic') then begin
     writeln('Install '+CODE_NAME+' failed...');
     install.INSTALL_STATUS(CODE_NAME,110);
     exit;
  end;

  writeln('Install '+CODE_NAME+' extracted on "'+source_folder+'"');
  install.INSTALL_STATUS(CODE_NAME,50);
  forceDirectories('/usr/share/ppd');
  install.INSTALL_PROGRESS(CODE_NAME,'{installing}');


  fpsystem('/bin/cp -rfv '+source_folder+'/model/foomatic /usr/share/ppd/');
  fpsystem('/bin/cp '+source_folder+'/VERSION /usr/share/ppd/foomatic/');
  writeln('testing  gutenprint');
  if not gutenprint() then begin
      install.INSTALL_PROGRESS(CODE_NAME,'{failed}');
      install.INSTALL_STATUS(CODE_NAME,110);
      exit;
  end;
  writeln('testing  foo2zjs');
  foo2zjs();


   install.INSTALL_STATUS(CODE_NAME,90);

  install.INSTALL_PROGRESS(CODE_NAME,'{installed}');
  install.INSTALL_STATUS(CODE_NAME,100);

  writeln('Restarting cpus');
  fpsystem('/etc/init.d/cups restart');
  SetCurrentDir('/root');
  exit;


end;
//#########################################################################################
function tcups_install.gutenprint():boolean;
var
   CODE_NAME:string;
   cmd:string;
begin

    result:=false;
    if FileExists('/usr/share/gutenprint/5.2/xml/printers.xml') then exit;

    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('gutenprint');

  if not DirectoryExists(source_folder) then begin
     writeln('Install gutenprint failed...');
     exit;
  end;

  SetCurrentDir(source_folder);



  cmd:='./configure';
  cmd:=cmd + ' --prefix=/usr';
  cmd:=cmd + ' --mandir=\${prefix}/share/man';
  cmd:=cmd + ' --disable-static';
  cmd:=cmd + ' --enable-shared';
  cmd:=cmd + ' --disable-rpath';
  cmd:=cmd + ' --disable-static-genppd';
  cmd:=cmd + ' --with-modules=dlopen';
  cmd:=cmd + ' --enable-libgutenprintui2';
  cmd:=cmd + ' --without-gimp2';
  cmd:=cmd + ' --with-cups';
  cmd:=cmd + ' --enable-cups-level3-ppds';
  cmd:=cmd + ' --enable-globalized-cups-ppds';
  cmd:=cmd + ' --with-ijs';
  cmd:=cmd + ' --with-foomatic';
  cmd:=cmd + ' --with-foomatic3';
  cmd:=cmd + ' --disable-test';

  writeln(cmd);
  fpsystem(cmd);
  fpsystem('make && make install');

  SetCurrentDir('/root');

  if FileExists('/usr/share/gutenprint/5.2/xml/printers.xml') then begin
     writeln('Gutenprint is installed');
     result:=true;
  end;


end;
//#########################################################################################
function tcups_install.foo2zjs():boolean;
var
   CODE_NAME:string;
   cmd:string;
begin



    result:=false;

    if not libs.COMMANDLINE_PARAMETERS('--force') then begin
       if FileExists('/usr/bin/foo2zjs') then begin
          writeln('foo2zjs already installed');
          result:=true;
          exit;
       end;
    end;

    Writeln('Installing foo2zjs package...');
    if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('foo2zjs');

  if not DirectoryExists(source_folder) then begin
     writeln('Install foo2zjs failed...');
     exit;
  end;

  SetCurrentDir(source_folder);


Writeln('compiling foo2zjs package...');
  cmd:='./make';
  fpsystem(cmd);

  cmd:='./getweb 2600n';
  fpsystem(cmd);
  fpsystem('make install');

  SetCurrentDir('/root');

  if FileExists('/usr/bin/foo2zjs') then begin
     result:=true;
     fpsystem('/etc/init.d/cups restart');
     exit;
  end;

  writeln('Failed to install foo2zjs');


end;
//#########################################################################################
procedure tcups_install.hpinlinux();
var
   CODE_NAME:string;
   cmd:string;
   dirname:string;
   l:Tstringlist;
   i:integer;
begin

   CODE_NAME:='APP_HPINLINUX';
   SetCurrentDir('/root');



   install.INSTALL_STATUS(CODE_NAME,10);

   if not  foo2zjs() then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     writeln('Install foo2zjs failed...');
     exit;
   end;
   install.INSTALL_STATUS(CODE_NAME,30);
   install.INSTALL_PROGRESS(CODE_NAME,'{downloading}');
   if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('hpinlinux');

  if not DirectoryExists(source_folder) then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     writeln('Install hpinlinux failed...');
     exit;
  end;

  writeln('using sources in ',source_folder);
  ForceDirectories('/usr/local/share/hpinlinux');
  fpsystem('cp -rf '+source_folder+'/* /usr/local/share/hpinlinux/');

  if not FileExists('/usr/local/share/hpinlinux/getweb.in') then begin
     install.INSTALL_STATUS(CODE_NAME,110);
     writeln('Install hpinlinux failed...');
     exit;
  end;
   install.INSTALL_STATUS(CODE_NAME,40);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
  SetCurrentDir('/usr/local/share/hpinlinux');
  fpsystem('make');
   install.INSTALL_STATUS(CODE_NAME,50);
  install.INSTALL_PROGRESS(CODE_NAME,'{updating}');
  fpsystem('echo y|getweb update');
  fpsystem('getweb all');
  l:=Tstringlist.Create;
l.add('1215');
l.add('1500');
l.add('1600');
l.add('2600n');
l.add('1600w');
l.add('1680');
l.add('1690');
l.add('2480');
l.add('2490');
l.add('2530');
l.add('4690');
l.add('6115');
l.add('cpwl');
l.add('2200');
l.add('2300');
l.add('2430');
l.add('300');
l.add('315');
l.add('600');
l.add('610');
l.add('2160');
l.add('3160');
l.add('6110');
l.add('500');
l.add('3200');
l.add('3300');
l.add('3400');
l.add('3530');
l.add('5100');
l.add('5200');
l.add('5500');
l.add('5600');
l.add('5800');
l.add('1000');
l.add('1005');
l.add('1018');
l.add('1020');
l.add('P1005');
l.add('P1006');
l.add('P1007');
l.add('P1008');
l.add('P1505');
   install.INSTALL_STATUS(CODE_NAME,70);
  install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
fpsystem('make install');
install.INSTALL_STATUS(CODE_NAME,80);
install.INSTALL_PROGRESS(CODE_NAME,'{compiling}');
fpsystem('make install-hotplug');
fpsystem('make cups');
install.INSTALL_STATUS(CODE_NAME,100);
install.INSTALL_PROGRESS(CODE_NAME,'{success}');
l.free;
end;
//#########################################################################################



end.
