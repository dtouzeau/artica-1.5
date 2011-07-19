unit cups;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,unix,libc,RegExpr in 'RegExpr.pas',zsystem,logs,tcpip,baseunix;

  type
  tcups=class


private
   SYS:Tsystem;
   LOGS:Tlogs;
   artica_path:string;
   function PID_NUM():string;
   function DRIVER_SCAN(path:string):string;
   function PID_PATH():string;
public
    function Daemon_bin_path():string;
    procedure   Free;
    constructor Create;
    function  STATUS():string;
    function  cups_config_path():string;
    procedure START();
    procedure STOP();
    function  VERSION():string;
    function  DRIVERS_LIST():string;
    function  DRIVER_VERSION():string;
    function  genppd_path():string;
    procedure WINDOWS_DRIVERS();
    procedure CHECK_DRIVERS();
    function  gutenprint_SCAN():string;
    function  PPD_MODEL_NAME(ppd_path:string):string;
    function  PPD_PATH(printer_name:string):string;
    function  SHARED_PRINTER_DRIVER(printer_name:string;password:string):string;
    function  PPD_PCFileName(ppd_path:string):string;
    function  PPD_StpDriverName(ppd_path:string):string;
    function  ADD_SHARED_PRINTER(printer_name:string;ppd_path:string;password:string):string;
    function  BROTHER_DRIVER_VERSION():string;
    function  DeleteAllPrinters():string;
    procedure WRITE_CUPS_CONF();
END;

implementation

constructor tcups.Create;
begin
 forcedirectories('/etc/artica-postfix');
       SYS:=Tsystem.Create;
       LOGS:=tlogs.Create();
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;



end;
//##############################################################################
procedure tcups.free();
begin

end;
//##############################################################################
function tcups.cups_config_path():string;
begin
     if FileExists('/usr/bin/cups-config') then exit('/usr/bin/cups-config');
end;
//##############################################################################
function tcups.Daemon_bin_path():string;
begin
     if FileExists('/usr/sbin/cupsd') then exit('/usr/sbin/cupsd');
     if FileExists(SYS.LOCATE_GENERIC_BIN('cupsd')) then exit(SYS.LOCATE_GENERIC_BIN('cupsd'));

end;
//##############################################################################
function tcups.PID_NUM():string;
begin
result:=SYS.PIDOF(Daemon_bin_path());
end;
//##############################################################################
function tcups.genppd_path():string;
begin
     if FileExists('/usr/sbin/cups-genppd.5.2') then exit('/usr/sbin/cups-genppd.5.2');
end;
//##############################################################################
function tcups.PID_PATH():string;
begin
if FileExists('/var/run/cups/cupsd.pid') then exit('/var/run/cups/cupsd.pid');
end;
//##############################################################################

function tcups.DeleteAllPrinters():string;
var
      RegExpr:TRegExpr;
      l:Tstringlist;
      tmp:string;
      i:integer;
begin

     tmp:=logs.FILE_TEMP();
     fpsystem('/usr/bin/lpstat -a >'+tmp + ' 2>&1');
     l:=Tstringlist.Create;
     l.LoadFromFile(tmp);
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='^(.+?)\s+';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
         logs.OutputCmd('/usr/sbin/lpadmin -x ' + RegExpr.Match[1]);
         end;
     end;

end;
//##############################################################################

procedure tcups.WRITE_CUPS_CONF();
var
l:Tstringlist;
stcp:ttcpip;
eth0,eth1,eth2,eth3,eth4:string;
limit:string;
RegExpr:TRegExpr;
s:tstringlist;
a:tstringlist;
begin

stcp:=ttcpip.Create;
eth0:=stcp.IP_ADDRESS_INTERFACE('eth0');
eth1:=stcp.IP_ADDRESS_INTERFACE('eth1');
eth2:=stcp.IP_ADDRESS_INTERFACE('eth2');
eth2:=stcp.IP_ADDRESS_INTERFACE('eth3');
eth4:=stcp.IP_ADDRESS_INTERFACE('eth4');


a:=Tstringlist.Create;

limit:='Limit Pause-Printer Resume-Printer Enable-Printer Disable-Printer Pause-Printer-After-Current-Job';
limit:=limit+' Hold-New-Jobs Release-Held-New-Jobs Deactivate-Printer Activate-Printer Restart-Printer Shutdown-Printer';
limit:=limit+' Startup-Printer Promote-Job Schedule-Job-After CUPS-Accept-Jobs CUPS-Reject-Jobs';

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='([0-9]+)\.([0-9]+)\.([0-9]+)\.';
s:=tstringlist.Create;
if eth0<>'0.0.0.0' then begin
     if RegExpr.Exec(eth0) then begin
        s.Add('Allow From '+RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'.');
        a.Add('Listen '+ eth0+':631');
     end;
end;

if eth1<>'0.0.0.0' then begin
     if RegExpr.Exec(eth1) then begin
        s.Add('Allow From '+RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'.');
        a.Add('Listen '+ eth1+':631');
     end;
end;

if eth2<>'0.0.0.0' then begin
     if RegExpr.Exec(eth2) then begin
        s.Add('Allow From '+RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'.');
        a.Add('Listen '+ eth2+':631');
     end;
end;


if eth3<>'0.0.0.0' then begin
     if RegExpr.Exec(eth3) then begin
        s.Add('Allow From '+RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'.');
        a.Add('Listen '+ eth3+':631');
     end;
end;


if eth4<>'0.0.0.0' then begin
     if RegExpr.Exec(eth4) then begin
        s.Add('Allow From '+RegExpr.Match[1]+'.'+RegExpr.Match[2]+'.'+RegExpr.Match[3]+'.');
        a.Add('Listen '+ eth4+':631');
     end;
end;

  RegExpr.free;


l:=Tstringlist.Create;
l.add('# Show troubleshooting information in error_log.');
l.add('LogLevel debug2');
l.add('SystemGroup lpadmin');
l.add('port 631');
l.add('Listen /var/run/cups/cups.sock');
l.add('Browsing Off');
l.add('BrowseOrder allow,deny');
l.add('BrowseAllow all');
l.add('BrowseAddress @LOCAL');
l.add('DefaultAuthType Basic');
l.add('Encryption Never');
l.add('<Location />');
l.add(s.Text);
l.add('  Order allow,deny');
l.add('</Location>');
l.add('<Location /admin>');
l.add(s.Text);
l.add('  Order allow,deny');
l.add('</Location>');
l.add('<Location /admin/conf>');
l.add(s.Text);
l.add('  AuthType Default');
l.add('  Require user @SYSTEM');
l.add('  Order allow,deny');
l.add('</Location>');
l.add('<Policy default>');
l.add('  <Limit Send-Document Send-URI Hold-Job Release-Job Restart-Job Purge-Jobs Set-Job-Attributes Create-Job-Subscription Renew-Subscription Cancel-Subscription Get-Notifications Reprocess-Job Cancel-Current-Job Suspend-Current-Job Resume-Job CUPS-Move-Job>');
l.add('    Require user @OWNER @SYSTEM');
l.add('    Order deny,allow');
l.add('  </Limit>');
l.add('  <Limit CUPS-Add-Modify-Printer CUPS-Delete-Printer CUPS-Add-Modify-Class CUPS-Delete-Class CUPS-Set-Default>');
l.add('    AuthType Default');
l.add('    Require user @SYSTEM');
l.add('    Order deny,allow');
l.add('  </Limit>');
l.add('  <'+limit+'>');
l.add('    AuthType Default');
l.add('    Require user @SYSTEM');
l.add('    Order deny,allow');
l.add('  </Limit>');
l.add('  <Limit Cancel-Job CUPS-Authenticate-Job>');
l.add('    Require user @OWNER @SYSTEM');
l.add('    Order deny,allow');
l.add('  </Limit>');
l.add('  <Limit All>');
l.add('    Order deny,allow');
l.add('  </Limit>');
l.add('</Policy>');

logs.WriteToFile(l.Text,'/etc/cups/cupsd.conf');
l.free;

end;

function tcups.VERSION():string;
var
      RegExpr:TRegExpr;
      l:Tstringlist;
      tmp:string;
      i:integer;
begin

result:=SYS.GET_CACHE_VERSION('APP_CUPS');
if length(result)>1 then exit(result);

tmp:=logs.FILE_TEMP();
fpsystem(cups_config_path() +' --version >'+tmp+' 2>&1');
if not FileExists(tmp) then exit;
l:=Tstringlist.Create;
l.LoadFromFile(tmp);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='([0-9\.]+)';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
    end;
end;

logs.DeleteFile(tmp);
SYS.SET_CACHE_VERSION('APP_CUPS',result);
l.free;
RegExpr.free;
end;
//##############################################################################
function tcups.DRIVER_VERSION():string;
begin
     result:=trim(logs.ReadFromFile('/usr/share/ppd/foomatic/VERSION'));
end;
//##############################################################################
function tcups.BROTHER_DRIVER_VERSION():string;
begin
     result:=trim(logs.ReadFromFile('/usr/local/Brother/VERSION'));
end;
//##############################################################################



procedure tcups.START();
var
   pid:string;
   count:integer;
begin

logs.Debuglogs('###################### CUPS ######################');

if not FileExists(Daemon_bin_path()) then begin
      logs.DebugLogs('Starting......: CUPS not installed');
      exit;
end;


if not FileExists(cups_config_path()) then begin
   logs.Debuglogs('CUPS server is not installed, need cups-devel package or libcups2-dev');
   exit;
end;
 pid:=PID_NUM();
if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......: CUPS already running with PID ' + pid);
   exit;
end;


  if not FileExists('/etc/artica-postfix/cups.check.time') then CHECK_DRIVERS();
  WRITE_CUPS_CONF();
  if FileExists('/etc/init.d/cups') then begin
     fpsystem('/etc/init.d/cups start');
  end else begin
    logs.DebugLogs('Starting......: CUPS (failed) unable to stat /etc/init.d/cups');
    exit;
  end; 

  count:=0;
 while not SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(100);
        inc(count);
        if count>10 then begin
           logs.DebugLogs('Starting......: CUPS (failed)');
           break;
        end;
  end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......: CUPS success with PID ' + pid);
   exit;
end;

end;
//##############################################################################
procedure tcups.STOP();
var
   pid:string;
begin

if not FileExists(Daemon_bin_path()) then begin
      logs.DebugLogs('Stopping CUPS................: not installed');
      exit;
end;

 pid:=PID_NUM();
if not SYS.PROCESS_EXIST(PID_NUM()) then begin
   writeln('Stopping CUPS................: Already stopped');
   exit;
end;

  if FileExists('/etc/init.d/cups') then begin
     fpsystem('/etc/init.d/cups stop');
  end else begin
    writeln('Stopping CUPS................:  (failed) unable to stat /etc/init.d/cups');
    exit;
  end;
end;
//##############################################################################
function tcups.STATUS():string;
var
pidpath:string;
begin
   if not FileExists(Daemon_bin_path()) then exit;
   if not FileExists(cups_config_path()) then  exit;
   SYS.MONIT_DELETE('APP_CUPS');
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --cups >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//##############################################################################
procedure tcups.WINDOWS_DRIVERS();

var
l:TstringList;
i:Integer;
begin
l:=TstringList.Create;
l.add('cupsui6.dll');
l.add('pscript5.dll');
l.add('ps5ui.dll');
l.add('cups6.inf');
l.add('pscript.hlp');
l.add('cupsps6.dll');
l.add('cups6.ini');
l.add('pscript.ntf');
l.add('cupsdrvr.dll');

 ForceDirectories('/usr/share/cups/drivers');

 for i:=0 to l.Count-1 do begin
     if not FileExists('/usr/share/cups/drivers/'+ l.Strings[i]) then begin
        if FileExists('/usr/share/artica-postfix/bin/install/cups/drivers/'+ l.Strings[i]) then begin
           logs.DebugLogs('Starting......: CUPS installing i386 file '+ l.Strings[i] );
           fpsystem('/bin/cp /usr/share/artica-postfix/bin/install/cups/drivers/'+ l.Strings[i] +' /usr/share/cups/drivers/'+l.Strings[i]);
         end;
     end;
 end;

 forceDirectories('/var/lib/samba/printers/W32X86');
 forceDirectories('/etc/samba/printer_drivers/W32X86');

 for i:=0 to l.Count-1 do begin
     if FileExists('/usr/share/cups/drivers/'+ l.Strings[i]) then begin
           if not FileExists('/var/lib/samba/printers/W32X86/' +  l.Strings[i]) then begin
              logs.DebugLogs('Starting......: CUPS installing i386 samba driver '+ l.Strings[i] );
              logs.OutputCmd('/bin/cp /usr/share/cups/drivers/'+ l.Strings[i] +' /var/lib/samba/printers/W32X86/'+l.Strings[i]);
           end;

           if not FileExists('/etc/samba/printer_drivers/W32X86/' +  l.Strings[i]) then begin
              logs.DebugLogs('Starting......: CUPS installing samba i386 driver /etc/samba/printer_drivers/W32X86/'+ l.Strings[i] );
              logs.OutputCmd('/bin/cp /usr/share/cups/drivers/'+ l.Strings[i] +' /etc/samba/printer_drivers/W32X86/'+l.Strings[i]);
           end;
     end;




 end;


end;
//##############################################################################
procedure tcups.CHECK_DRIVERS();
var pid:string;
begin

  if SYS.COMMANDLINE_PARAMETERS('--force') then logs.DeleteFile('/etc/artica-postfix/cups.check.time');

  pid:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/cronded.2/cupd.chk.pid');
  if SYS.PROCESS_EXIST(pid) then begin
       logs.Debuglogs('Starting......: checking drivers already running pid '+ pid);
       exit;
  end;

  logs.WriteToFile(intTostr(fpgetpid),'/etc/artica-postfix/cronded.2/cupd.chk.pid');

  if FileExists('/etc/artica-postfix/cups.check.time') then begin
     if SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/cups.check.time')<10 then begin
        logs.Debuglogs('Starting......: checking drivers cannot be performed under 10 minutes interval');
        exit;
     end;
  end;
    forceDirectories('/usr/share/ppd/gutenprint/5.2');
    WINDOWS_DRIVERS();
    DRIVERS_LIST();

    logs.DeleteFile('/etc/artica-postfix/cups.check.time');
    logs.WriteToFile('#','/etc/artica-postfix/cups.check.time');

end;
//##############################################################################



function tcups.DRIVERS_LIST():string;
var
l:TstringList;
i:integer;
tmpstr:string;
FileInc:TstringList;
gutentprint:string;
source_path,dest_path,ext:string;
begin
  if not FileExists(cups_config_path()) then begin
     logs.Debuglogs('cups.DRIVERS_LIST():: Unable to stat cups-config... Aborting');
     exit;
  end;

  if length(DRIVER_VERSION)=0 then logs.OutputCmd(artica_path + '/bin/artica-make APP_CUPS_DRV');
  WINDOWS_DRIVERS();
  if SYS.COMMANDLINE_PARAMETERS('--force') then logs.DeleteFile('/usr/share/artica-postfix/ressources/scan.printers.drivers.inc');

  if FileExists('/usr/share/artica-postfix/ressources/scan.printers.drivers.inc') then begin
     if SYS.FILE_TIME_BETWEEN_MIN('/usr/share/artica-postfix/ressources/scan.printers.drivers.inc')<120 then begin
        logs.Debuglogs('cups.DRIVERS_LIST():: too short time for generating drivers list');
     end;
  end;

  gutenprint_SCAN();

  tmpstr:=LOGS.FILE_TEMP();

  fpsystem('/usr/bin/find -L /usr/share/ppd -iregex ".+?\.ppd.*" >'+tmpstr+' 2>&1');

  l:=Tstringlist.Create;
  l.LoadFromFile(tmpstr);
  FileInc:=TStringList.Create;
  FileInc.Add('<?php');
  logs.Debuglogs('cups.DRIVERS_LIST()::Scanning ' +IntToStr(l.Count) + ' file(s)...');
  for i:=0 to l.Count-1 do begin
       source_path:=l.Strings[i];
       ext:=ExtractFileExt(source_path);
       if ext='.gz' then begin
           dest_path:=AnsiReplaceText(source_path,'.gz','');
           if not FileExists(dest_path) then begin
              writeln('Extracting '+source_path);
              fpsystem('/bin/gunzip -d -c '+source_path+' >'+dest_path);
              logs.DeleteFile(source_path);
           end;

           if FileExists(dest_path) then begin
              if fileExists(source_path) then logs.DeleteFile(source_path);
              source_path:=dest_path;
           end;
       end;
       FileInc.Add(DRIVER_SCAN(source_path));


  end;



  FileInc.Add('?>');

  logs.DeleteFile('/usr/share/artica-postfix/ressources/scan.printers.drivers.inc');

  try
     FileInc.SaveToFile('/usr/share/artica-postfix/ressources/scan.printers.drivers.inc');
  except
        logs.Syslogs('Warning tcups.DRIVERS_LIST(), unable to write /usr/share/artica-postfix/ressources/scan.printers.drivers.inc');
        exit;
  end;
  logs.Debuglogs('Success Generate the new printers drivers list');
  FileInc.Free;
  l.free;
end;
//##############################################################################
function tcups.DRIVER_SCAN(path:string):string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
   final:TstringList;
   Manufacturer,ModelName,LanguageVersion:string;
begin

  final:=TstringList.Create;

  l:=TstringList.Create;
  l.LoadFromFile(path);
  RegExpr:=TRegExpr.Create;
  for i:=0 to l.Count-1 do begin
    RegExpr.Expression:='Manufacturer.+?"(.+)"';
    if RegExpr.Exec(l.Strings[i]) then begin
         Manufacturer:=RegExpr.Match[1];
         Manufacturer:=UpperCase(Manufacturer);
         Manufacturer:=ansiReplaceText(Manufacturer,'"','');
    end;

    RegExpr.Expression:='ModelName.+?"(.+)"';
    if RegExpr.Exec(l.Strings[i]) then begin
         ModelName:=RegExpr.Match[1];
         ModelName:=ansiReplaceText(ModelName,'"','');
    end;

    RegExpr.Expression:='LanguageVersion:\s+(.+)';
    if RegExpr.Exec(l.Strings[i]) then begin
         LanguageVersion:=RegExpr.Match[1];
         LanguageVersion:=ansiReplaceText(LanguageVersion,'"','');
    end;



    if length(ModelName)>0 then begin
       if length(Manufacturer)>0 then begin
          if length(LanguageVersion)>0 then break;
       end;
    end;


  end;
  logs.Debuglogs('Found driver for '+ModelName +' ('+LanguageVersion+') from ' + ModelName);
  result:='$GLOBAL_PRINTERS["'+Manufacturer+'"]["'+ModelName+'"]["'+LanguageVersion+'"]="'+path+'";';
  l.free;
  RegExpr.free;




end;
//##############################################################################
function tcups.SHARED_PRINTER_DRIVER(printer_name:string;password:string):string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
   tmpstr:string;
   cmd:string;
begin

 tmpstr:=logs.FILE_TEMP();
 l:=Tstringlist.Create;
 l.Add('username = administrator');
 l.Add('password = '+password);
 logs.WriteToFile(L.Text,'/tmp/passd');
 l.Clear;

cmd:='/usr/bin/rpcclient localhost -N -A /tmp/passd -c "getprinter '+printer_name+' 2" >'+tmpstr + ' 2>&1';
logs.Debuglogs('SHARED_PRINTER_DRIVER:: '+cmd);
fpsystem(cmd);
if not fileExists(tmpstr) then exit;

l.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='drivername:\[(.+?)\]';

for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
   end;
end;

l.free;
RegExpr.free;
end;

//##############################################################################



function tcups.ADD_SHARED_PRINTER(printer_name:string;ppd_path:string;password:string):string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
   tmpstr:string;
   cmd:string;
   PCFileName:string;
   DriverName:string;

begin

   WINDOWS_DRIVERS();

 l:=Tstringlist.Create;
 l.Add('username = administrator');
 l.Add('password = '+password);
 logs.WriteToFile(L.Text,'/tmp/passd');
 l.Clear;

   if not FileExists(ppd_path) then begin
      logs.Debuglogs('Unable to stat '+ppd_path +' ppd file');
      exit;
   end;

   if not FileExists('/usr/bin/rpcclient') then begin
      logs.Debuglogs('Unable to stat /usr/bin/rpcclient');
      exit;
   end;


   PCFileName:=PPD_PCFileName(ppd_path);
   logs.Debuglogs('Original filename was "'+PCFileName+'"');
   if length(PCFileName)=0 then PCFileName:=ExtractFileName(ppd_path);
   forceDirectories('/etc/samba/printer_drivers/W32X86');
   logs.OutputCmd('/bin/chmod -R 777 /etc/samba/printer_drivers');
   logs.OutputCmd('/bin/chmod 777 /var/spool/samba');
   logs.OutputCmd('/bin/chmod o+t /var/spool/samba');
   logs.OutputCmd('/bin/cp -f '+ppd_path+' /etc/samba/printer_drivers/W32X86/'+PCFileName);

   DriverName:=trim(PPD_StpDriverName(ppd_path));
   if length(DriverName)=0 then DriverName:=AnsiReplaceText(PCFileName,'.ppd','');


   cmd:='/usr/bin/rpcclient localhost -N -A /tmp/passd -c "adddriver \"Windows NT x86\"';
   cmd:=cmd+' \"'+DriverName+':pscript5.dll:'+PCFileName+':ps5ui.dll:pscript.hlp:NULL:RAW:pscript5.dll,'+PCFileName+',ps5ui.dll,pscript.hlp,pscript.ntf,cups6.ini,cupsps6.dll,cupsui6.dll\""';
   logs.OutputCmd(cmd);

   logs.OutputCmd('/etc/init.d/artica-postfix restart samba');

   cmd:='/usr/bin/rpcclient localhost -N -A /tmp/passd -c "setdriver '+printer_name+' '+DriverName+'"';
   logs.OutputCmd(cmd);





end;
//##############################################################################


function tcups.PPD_PATH(printer_name:string):string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
   tmpstr:string;
   cmd:string;
begin

 tmpstr:=logs.FILE_TEMP();


cmd:='/usr/bin/lpstat -l -p ' + printer_name+' >'+tmpstr + ' 2>&1';
logs.Debuglogs('PPD_PATH:: '+cmd);
fpsystem(cmd);
if not fileExists(tmpstr) then exit;
l:=Tstringlist.Create;
l.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='.+?:\s+\/(.+?)\.ppd';

for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
          result:='/'+RegExpr.Match[1]+'.ppd';
          break;
   end;
end;

l.free;
RegExpr.free;
end;
//##############################################################################
function tcups.PPD_MODEL_NAME(ppd_path:string):string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin
   if not FileExists(ppd_path) then begin
      logs.Debuglogs('PPD_MODEL_NAME:: Unable to stat ' + ppd_path);
      exit;
   end;

  l:=TstringList.Create;
  l.LoadFromFile(ppd_path);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='ModelName.+?"(.+)"';
  for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
         logs.Debuglogs('PPD_MODEL_NAME:: found ' + l.Strings[i]+' :'+RegExpr.Match[1]);
         result:=RegExpr.Match[1];
         result:=ansiReplaceText(result,'"','');
         break;
    end;
  end;

  l.free;
  RegExpr.free;
end;
//##############################################################################
function tcups.PPD_PCFileName(ppd_path:string):string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin
   if not FileExists(ppd_path) then begin
      logs.Debuglogs('PPD_PCFileName:: Unable to stat ' + ppd_path);
      exit;
   end;

  l:=TstringList.Create;
  l.LoadFromFile(ppd_path);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='PCFileName.+?"(.+)"';
  for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
         logs.Debuglogs('PPD_PCFileName:: found ' + l.Strings[i]+' :'+RegExpr.Match[1]);
         result:=RegExpr.Match[1];
         result:=ansiReplaceText(result,'"','');
         break;
    end;
  end;

  l.free;
  RegExpr.free;
end;
//##############################################################################
function tcups.PPD_StpDriverName(ppd_path:string):string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin
   if not FileExists(ppd_path) then begin
      logs.Debuglogs('PPD_PCFileName:: Unable to stat ' + ppd_path);
      exit;
   end;

  l:=TstringList.Create;
  l.LoadFromFile(ppd_path);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='StpDriverName.+?"(.+)"';
  for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
         logs.Debuglogs('StpDriverName:: found ' + l.Strings[i]+' :'+RegExpr.Match[1]);
         result:=RegExpr.Match[1];
         result:=ansiReplaceText(result,'"','');
         break;
    end;
  end;

  l.free;
  RegExpr.free;
end;
//##############################################################################







function tcups.gutenprint_SCAN():string;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
   final:TstringList;
   Manufacturer,ModelName,LanguageVersion:string;
   driver:string;
begin

  final:=TstringList.Create;

  if not FileExists('/usr/share/gutenprint/5.2/xml/printers.xml') then begin
     logs.Debuglogs('gutenprint_SCAN():: Installing Gutenprint additionals drivers...');
     fpsystem(artica_path+'/bin/artica-make APP_GUTENPRINT');
  end else begin
      logs.Debuglogs('/usr/share/gutenprint/5.2/xml/printers.xml =>OK');
  end;

  if not FileExists('/usr/share/gutenprint/5.2/xml/printers.xml') then begin
     logs.Syslogs('gutenprint_SCAN():: Unable to stat /usr/share/gutenprint/5.2/xml/printers.xml');
     exit;
  end;


  if not FileExists(genppd_path()) then begin
     logs.Syslogs('gutenprint_SCAN()::  Unable to stat genppd path');
     exit;
  end;



  forceDirectories('/usr/share/ppd/gutenprint/5.2');

  l:=TstringList.Create;
  l.LoadFromFile('/usr/share/gutenprint/5.2/xml/printers.xml');
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='<printer translate="name" name="(.+?)" driver="(.+?)".+?manufacturer="(.+?)"';
  for i:=0 to l.Count-1 do begin

    if RegExpr.Exec(l.Strings[i]) then begin
         Manufacturer:=RegExpr.Match[3];
         Manufacturer:=UpperCase(Manufacturer);
         ModelName:=RegExpr.Match[1];
         driver:=RegExpr.Match[2];
         Manufacturer:=ansiReplaceText(Manufacturer,'"','');
         ModelName:=ansiReplaceText(ModelName,'"','');
         driver:=ansiReplaceText(driver,'"','');

         if length(driver)>0 then begin
            if fileExists('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd') then begin
               if SYS.FileSize_bytes('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd')=0 then logs.DeleteFile('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd');
            end;

            if not FileExists('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd') then begin
               logs.Debuglogs('gutenprint_SCAN():: could not find /usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd');
               logs.Syslogs('gutenprint_SCAN():: Generating PPD for '+ModelName+' from ' +Manufacturer );
               logs.OutputCmd(genppd_path+' -v ' + driver+' -p /usr/share/ppd/gutenprint/5.2');

               if FileExists('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.gz') then begin
                  logs.Debuglogs('gutenprint_SCAN():: Extracting /usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.gz');
                  fpsystem('gunzip /usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.gz -c >/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd');
               end;

                if SYS.FileSize_bytes('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd')=0 then begin
                   logs.Debuglogs('gutenprint_SCAN()::  Failed to stat PPD file '+driver);
                   logs.DeleteFile('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.gz');
                   logs.DeleteFile('/usr/share/ppd/gutenprint/5.2/stp-'+driver+'.5.2.ppd');
                end;

            end;
         end;

         LanguageVersion:='English';
//         final.Add('$GLOBAL_PRINTERS["'+Manufacturer+'"]["'+ModelName+'"]["'+LanguageVersion+'"]="+driver:'+RegExpr.Match[2]+'";');
    end;



  end;

 // result:=final.Text;
  l.free;
  final.free;
  RegExpr.free;


end;
//##############################################################################


end.
