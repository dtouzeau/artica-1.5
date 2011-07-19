unit samba_usb;

{$mode objfpc}{$H+}
interface

uses
Classes, SysUtils,variants,strutils,unix,dateUtils,
zsystem      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas',
logs         in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/logs.pas',
RegExpr      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
samba        in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/samba.pas';


  type
   usb_params=record
   maxoff               :integer;
   uuid                 :string;
   name                 :string;
   time_disconnect      :string;
   Scanned_params       :boolean;
   dev_source           :string;
   target_mount         :string;
   end;



  type
  TsambaUsb=class


private
     LOGS:Tlogs;
     artica_path:string;
     function ParseUsbSharesDisconnect(params:usb_params):boolean;
     function UsbDisconnect(dev_source:string;uuid:string;target_mount:string):boolean;
     function UsCreateLockFile(uuid:string):boolean;
     function ScanParams(line:string):usb_params;
     SYS:TSystem;
     samba:tsamba;

public
    procedure Free;
    constructor Create;
    procedure ParseUsbShares();





END;

implementation

constructor TsambaUsb.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       samba:=Tsamba.Create;
       SYS:=Tsystem.CReate;
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
  artica_path:='/usr/share/artica-postfix';
  end;



end;
//##############################################################################
procedure TsambaUsb.free();
begin
    logs.Free;
    SYS.free;
    samba.Free;
end;
//##############################################################################
procedure TsambaUsb.ParseUsbShares();
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
   cmd:string;
   ftmp:string;
   params:usb_params;

begin
   if not FileExists('/etc/artica-postfix/samba.usb.conf') then exit;
   if not FileExists(samba.SMBD_PATH()) then exit;
   if not FileExists(SYS.LOCATE_MOUNT()) then exit;
   l:=TstringList.Create;
   l.LoadFromFile('/etc/artica-postfix/samba.usb.conf');
   for i:=0 to l.Count-1 do begin
        params:=ScanParams(l.Strings[i]);
        if not params.Scanned_params then continue;

         logs.Debuglogs('Tsamba.ParseUsbShares(): scanning device '+params.uuid+'...');

           if not SYS.DISK_USB_EXISTS(params.uuid) then continue;

            params.target_mount:='/opt/artica/usb_mount/'+ params.uuid;
            params.dev_source:=SYS.DISK_USB_DEV_SOURCE(params.uuid);

           if SYS.DISK_USB_IS_MOUNTED(params.dev_source,params.target_mount) then begin
               logs.Debuglogs('Tsamba.ParseUsbShares(): device '+params.uuid+' is already mounted on '+params.target_mount + ' test disconnection..');
               ParseUsbSharesDisconnect(params);
               continue;
           end;

           if ParseUsbSharesDisconnect(params) then begin
              logs.Debuglogs('Tsamba.ParseUsbShares(): device stay unmounted... continue');
              continue;
           end;

           fpsystem('/bin/rmdir -f '+params.target_mount);
           forceDirectories(params.target_mount);

           logs.Debuglogs('Tsamba.ParseUsbShares(): mount device '+params.uuid+' on '+params.target_mount);
           ftmp:=LOGS.FILE_TEMP();
           cmd:=SYS.LOCATE_MOUNT() +' -t auto ' + params.dev_source + ' ' + params.target_mount + ' >' + ftmp + ' 2>&1';
           logs.Debuglogs(cmd);
           fpsystem(cmd);
           if SYS.DISK_USB_IS_MOUNTED(params.dev_source,params.target_mount) then begin
              if fileExists(params.target_mount+'/disconnect') then logs.DeleteFile(params.target_mount+'/disconnect');
              logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+') Success mount USB device name "'+params.name+'"','This device is Shared on the computer, you can browse it by the local network:'+logs.ReadFromFile(ftmp),'system');
           end else begin
               logs.Debuglogs('Tsamba.ParseUsbShares(): failed mount device '+params.uuid+ ' ' + logs.ReadFromFile(ftmp));
               logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+') Failed mount USB device name "'+params.name+'"','This device is plugged, but failed to mount it...'+logs.ReadFromFile(ftmp),'system');

           end;

       end;




end;
//##############################################################################

function TsambaUsb.ParseUsbSharesDisconnect(params:usb_params):boolean;
var
lockfile:string;
tmpdate:string;
T1,T2:TDateTime;
minutes:integer;
minutes_now:integer;
RegExpr:TRegExpr;
begin
result:=false;
lockfile:='/etc/artica-postfix/SharedFolers/smb_sub_tmp/'+params.uuid;
minutes_now:=0;
minutes:=0;


  if fileExists(params.target_mount+'/disconnect') then begin
     if not UsbDisconnect(params.dev_source,params.uuid,params.target_mount) then begin
           logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+') unable to disconnect USB device shared "'+params.target_mount+'"','','system');
           exit;
        end;
     UsCreateLockFile(params.uuid);
     exit;
  end;

  if length(params.time_disconnect)=0 then begin
     logs.Debuglogs('Tsamba.ParseUsbSharesDisconnect(): No disconnect time..');
     exit(false);
  end;


  if params.time_disconnect='0' then begin
     logs.Debuglogs('Tsamba.ParseUsbSharesDisconnect(): Disconnect time disabled..');
     exit(false);
  end;


T1 := Now;

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='([0-9]+):([0-9]+)';
RegExpr.Exec(params.time_disconnect);

if not TryStrToInt(RegExpr.Match[2],minutes) then begin
    logs.syslogs('FATAL Error while calculate the end time of '+RegExpr.Match[2]);
    exit(false);
end;


if not TryStrToInt(FormatDateTime('nn', T1),minutes_now) then begin
    logs.syslogs('FATAL Error while calculate the now time "mn" of '+params.uuid);
    exit(false);
end;



tmpdate:=FormatDateTime('dd-mm-yyyy',T1) + ' ' + params.time_disconnect + ':'+FormatDateTime('ss', T1);
//logs.Debuglogs('Tsamba.ParseUsbSharesDisconnect(): '+FormatDateTime('dd-mm-yyyy hh:nn:ss', T1) + '<>' + tmpdate);


if not TryStrToDateTime(tmpdate,T2) then begin
       logs.Debuglogs('Tsamba.ParseUsbSharesDisconnect(): unable to format ' + tmpdate);
       exit;
end;



  if HoursBetween(T1,T2)=0 then begin
     if (minutes_now>minutes) and (minutes_now<(minutes+params.maxoff)) then begin
        logs.Debuglogs('Tsamba.ParseUsbSharesDisconnect() it is now time off..');
        logs.Debuglogs('Tsamba.ParseUsbSharesDisconnect(): ' + intToStr(HoursBetween(T1,T2)) + 'h and '+ intToStr(MinutesBetween(T1,T2))+'mn between for a maximal '+IntToStr(params.maxoff) + 'mn off');
        if not UsbDisconnect(params.dev_source,params.uuid,params.target_mount) then begin
           logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+') unable to disconnect USB device shared "'+params.target_mount+'"','','system');
           exit;
        end;
        UsCreateLockFile(params.uuid);
        exit(true);
     end;
  end;






end;
//##############################################################################
function TsambaUsb.UsbDisconnect(dev_source:string;uuid:string;target_mount:string):boolean;
var
cmd:string;

begin

result:=false;

  if not SYS.DISK_USB_IS_MOUNTED(dev_source,target_mount) then begin
      logs.Debuglogs('Tsamba.UsbDisconnect() '+ uuid + ' already disconnected from ' + target_mount);
      exit(true);
  end;

  logs.Debuglogs('Tsamba.UsbDisconnect() disconnect '+ uuid + ' mounted on ' + target_mount);
  cmd:='/bin/umount ' + target_mount;
  logs.Debuglogs(cmd);
  fpsystem(cmd);

  if SYS.DISK_USB_IS_MOUNTED(dev_source,target_mount) then begin
     logs.Debuglogs('Tsamba.UsbDisconnect() '+ uuid + ' always mounted on ' + target_mount+' Force umount');
     fpsystem('/bin/umount -f ' + target_mount);
  end;


 if SYS.DISK_USB_IS_MOUNTED(dev_source,target_mount) then begin
      logs.Debuglogs('Tsamba.UsbDisconnect() '+ uuid + ' always mounted on ' + target_mount+' aborting');
      exit(false);
 end;

exit(true);
end;
//##############################################################################
function TsambaUsb.UsCreateLockFile(uuid:string):boolean;
var
   lockfile:string;

begin
result:=true;
  lockfile:='/etc/artica-postfix/SharedFolers/smb_sub_tmp/'+uuid;
  ForceDirectories('/etc/artica-postfix/SharedFolers/smb_sub_tmp');
  if FileExists(lockfile) then logs.DeleteFile(lockfile);
  fpsystem('/bin/touch ' + lockfile);
end;
//##############################################################################
function TsambaUsb.ScanParams(line:string):usb_params;
var
   a:usb_params;
   RegExpr:TRegExpr;
begin

a.Scanned_params:=false;;
result:=a;
if length(line)=0 then exit;

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='<uuid>(.+?)</uuid><name>(.+?)</name><umounttime>([0-9\:]+)</umounttime><maxoff>([0-9]+)</maxoff>';
if not RegExpr.Exec(line) then begin
    logs.Debuglogs('TsambaUsb.ScanParams(): Unable to scan this line: ' + line);
    exit;
end;
           a.uuid:=RegExpr.Match[1];
           a.name:=RegExpr.Match[2];
           a.time_disconnect:=RegExpr.Match[3];
           a.maxoff:=StrToInt(RegExpr.Match[4]);
           a.Scanned_params:=True;
result:=a;
end;
//##############################################################################





end.
