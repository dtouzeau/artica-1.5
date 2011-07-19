program artica_shared_folders;

uses
  linux,BaseUnix,unix,Dos,Classes,SysUtils,SharedFolders,dateutils,zSystem,debian_class,
  global_conf  in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/global_conf.pas',
  samba_usb;

var
 Shared         :tSharedFolder;
 SYS            :Tsystem;
 zsamba         :TsambaUsb;



function IsRoot():boolean;
begin
if FpGetEUid=0 then exit(true);
exit(false);
end;

//##############################################################################
begin

      if not IsRoot() then begin
         writeln('This program must run has root mode');
         halt(0);
      end;
      
      
      SYS:=Tsystem.Create;
      if not SYS.BuildPids() then exit;
      
      zsamba:=TsambaUsb.Create;
      zsamba.ParseUsbShares();
      
      
      Shared:=TSharedFolder.Create;
      halt(0);
   
   
   
end.

