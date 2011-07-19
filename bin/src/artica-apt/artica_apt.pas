program artica_apt;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils
  { you can add units after this }, apt_class,zsystem,logs;
  
  var
     SYS     :Tsystem;
     zLOGS    :Tlogs;
     apt     :tapt;

begin


  
  SYS:=Tsystem.Create();
  zLOGS:=tlogs.Create;
  
  if length(Paramstr(1))>0 then begin
  if length(SYS.DEBIAN_VERSION())=0 then begin
      zlogs.Debuglogs('This is not a debian system, aborting...');
      halt(0);
  end;
   if not SYS.BuildPids() then begin
       zlogs.Debuglogs('already instance executed');
      exit;
   end;

     zlogs.Debuglogs('Executing "'+Paramstr(1)+'" command');
   
   apt:=tapt.Create;
   if Paramstr(1)='--apt-caches' then begin
          apt.INSERT_DEB_PACKAGES();
          halt(0);
   end;
   
   if Paramstr(1)='--check' then begin
          if not apt.CheckTableEmpty() then  apt.INSERT_DEB_PACKAGES();
          halt(0);
   end;
  
   if Paramstr(1)='--uninstall' then begin
          apt.UNSTALL_MARK();
          apt.INSTALL_MARK();
          halt(0);
   end;
  
   if Paramstr(1)='--find' then begin
          apt.find(Paramstr(2));
          halt(0);
   end;
   
   if Paramstr(1)='--info-install' then begin
          apt.INFO(Paramstr(2));
          halt(0);
   end;
   
   if Paramstr(1)='--update' then begin
          apt.Check();
          halt(0);
   end;


   if Paramstr(1)='--upgrade' then begin
          apt.upgrade();
          halt(0);
   end;
  
  end;

  zlogs.Debuglogs('Unable to understand '+Paramstr(1)+' display help...');

  writeln('usage:');
  writeln('--apt-caches.......................: Build packages into mysql database');
  writeln('--check............................: used by the deamon in order to see if table must be fill');
  writeln('--uninstall........................: uninstall/install package marked by artica');
  writeln('--find.............................: find packages');
  writeln('--info-install.....................: get infos from package name');
  writeln('--update...........................: Check updates');
  writeln('--upgrade..........................: perform upgrade');
  
  halt(0);

end.

