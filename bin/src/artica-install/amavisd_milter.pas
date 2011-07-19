unit amavisd_milter;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas',
    dspam            in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/dspam.pas',
    spamass      in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/spamass.pas',IniFiles;


  type
  tamavis=class


private
     LOGS:Tlogs;
     artica_path:string;
     SYS:Tsystem;
     EnableAmavisDaemon:integer;
     JCheckMailEnabled:integer;
     dspam:tdspam;
     spamassassin:Tspamass;
     EnableAmavisInMasterCF:integer;
     EnablePostfixMultiInstance:integer;
     mem_installee:integer;
     AmavisMemoryInRAM:integer;
     slogs:Tstringlist;

     procedure AMAVISD_SETCONFIG(key:string;value:string);
     procedure include_config_files();

     procedure STOP_AMAVISD();
     procedure PatchPerlT();
     function AMAVISD_INIT_PATH():string;
     function MILTER_INIT_PATH():string;
     procedure WRITE_INITD();
     procedure CheckUnixSocketName();
     procedure CheckMyHostname();
     procedure CheckLogfile();
     procedure SaveConfig();
     procedure CleanCacheDatabases();
     function  IS_AMAVIS_MOUNTED_IN_RAM():boolean;
     function  AMAVISD_MILTER_STATUS():string;
     function  AMAVISD_STATUS():string;


public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    STATUS():string;
    function    MILTER_BIN_PATH():string;
    function    MILTER_PID_PATH():string;
    function    MILTER_PID():string;
    function    MILTER_VERSION():string;
    procedure   START_MILTER();
    procedure   STOP_MILTER();
    procedure   START();
    procedure   STOP();

    function    AMAVISD_VERSION():string;
    function    AMAVISD_BIN_PATH():string;
    function    AMAVISD_PID():string;
    function    AMAVISD_PID_PATH():string;
    procedure   AMAVISD_RELOAD();
    procedure   START_AMAVISD();
    function    AMAVISD_CONF_PATH():string;
    function    CHECK_MODULES():boolean;
    function    QUARANTINEDIR():string;
    
    function    AMAVIS_STAT_BIN_PATH():string;
    procedure   AMAVIS_STAT_WRITE_CONF();
    function    AMAVIS_STAT_PID_PATH():string;
    function    AMAVIS_STAT_PID():string;
    function    AMAVIS_STAT_VERSION():string;
    procedure   AMAVIS_LOGWATCH();
    procedure   START_AMAVIS_STAT();
    procedure   STOP_AMAVIS_STAT();
   function     altermime_version():string;
   function     altermime_bin_path():string;
END;

implementation

constructor tamavis.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableAmavisDaemon:=0;
       dspam:=tdspam.Create(SYS);
       spamassassin:=Tspamass.Create(SYS);
       AmavisMemoryInRAM:=0;
       slogs:=Tstringlist.Create;
       slogs.Add('');
       mem_installee:=SYS.MEM_TOTAL_INSTALLEE();
       if not TryStrToInt(SYS.GET_INFO('EnableAmavisDaemon'),EnableAmavisDaemon) then EnableAmavisDaemon:=0;
       if not TryStrToInt(SYS.GET_INFO('EnableAmavisInMasterCF'),EnableAmavisInMasterCF) then EnableAmavisInMasterCF:=0;
       if not TryStrToInt(SYS.GET_INFO('AmavisMemoryInRAM'),AmavisMemoryInRAM) then AmavisMemoryInRAM:=0;
       if not TryStrToInt(SYS.GET_INFO('EnablePostfixMultiInstance'),EnablePostfixMultiInstance) then EnablePostfixMultiInstance:=0;


       if(EnablePostfixMultiInstance=1) then begin
         if SYS.ISMemoryHiger1G() then EnableAmavisDaemon:=1;
       end;

       if EnableAmavisDaemon=1 then begin
          if not SYS.ISMemoryHiger1G() then begin
             logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+') Warning: your memory is under 1GB ['+IntToStr(mem_installee)+'], amavis is now disabled','Amavis is disabled because your computer is not "memory compliance" tamavis.Create() line 106','system');
             SYS.set_INFO('EnableAmavisDaemon','0');
             SYS.set_INFO('AmavisMemoryInRAM','0');
             SYS.set_INFO('EnableAmavisInMasterCF','0');
             EnableAmavisDaemon:=0;
             SYS.THREAD_COMMAND_SET(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfix.maincf.php --maincf');
          end;
       end;


       
       if TryStrToInt(SYS.GET_INFO('JCheckMailEnabled'),JCheckMailEnabled) then begin
          if JCheckMailEnabled=1 then begin
             if EnableAmavisDaemon=1 then begin
                   EnableAmavisDaemon:=0;
                   SYS.set_INFO('EnableAmavisDaemon','0');
                   logs.Syslogs('Amavis was disabled because j-checkmail is enabled... unable to start 2 same services...');
             end;
          end;
       end;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tamavis.free();
begin
    logs.Free;
end;
//##############################################################################
function tamavis.MILTER_BIN_PATH():string;
begin
    if FileExists('/usr/local/sbin/amavisd-milter') then exit('/usr/local/sbin/amavisd-milter');
    if FileExists('/usr/sbin/amavis-milter') then exit('/usr/sbin/amavis-milter');
end;
//#############################################################################
function tamavis.AMAVISD_BIN_PATH():string;
begin
    if FileExists('/usr/local/sbin/amavisd') then exit('/usr/local/sbin/amavisd');
    if FileExists('/usr/sbin/amavisd-new') then exit('/usr/sbin/amavisd-new');
end;
//#############################################################################
function tamavis.AMAVIS_STAT_BIN_PATH():string;
begin
    if FileExists('/usr/local/sbin/amavis-stats') then exit('/usr/local/sbin/amavis-stats');
end;
//#############################################################################
function tamavis.AMAVISD_INIT_PATH():string;
begin
    if FileExists('/etc/init.d/amavis') then exit('/etc/init.d/amavis');
end;
//#############################################################################
function tamavis.MILTER_PID_PATH():string;
begin
    if FileExists('/var/spool/postfix/var/run/amavisd-milter/amavisd-milter.pid') then exit('/var/spool/postfix/var/run/amavisd-milter/amavisd-milter.pid');
    if FileExists('/var/run/amavis/amavisd-new-milter.pid') then exit('/var/run/amavis/amavisd-new-milter.pid');
end;
//#############################################################################
function tamavis.MILTER_INIT_PATH():string;
begin
    if FileExists('/etc/init.d/amavisd-new-milter') then exit('/etc/init.d/amavisd-new-milter');
end;
//#############################################################################
function tamavis.AMAVIS_STAT_PID_PATH():string;
begin
    if FileExists('/usr/local/var/lib/amavis-stats/amavis-stats.pid') then exit('/usr/local/var/lib/amavis-stats/amavis-stats.pid');
end;
//#############################################################################
function tamavis.AMAVISD_PID_PATH():string;
begin
    if FileExists('/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid') then exit('/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid');
    slogs.Add('/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid no such file');
    if FileExists('/var/run/amavis/amavisd.pid') then exit('/var/run/amavis/amavisd.pid');
    slogs.Add('/var/run/amavis/amavisd.pid no such file');
end;
//#############################################################################
function tamavis.AMAVISD_CONF_PATH():string;
begin
  if FileExists('/usr/local/etc/amavisd.conf') then exit('/usr/local/etc/amavisd.conf');
  fpsystem('/bin/cp /usr/share/artica-postfix/bin/install/amavis/amavisd.conf /usr/local/etc/amavisd.conf');
  exit('/usr/local/etc/amavisd.conf');
end;
//#############################################################################
function tamavis.MILTER_PID():string;
var
   pid:string;
begin
   result:='';
   pid:=SYS.GET_PID_FROM_PATH(MILTER_PID_PATH());
   if pid='0' then pid:='';
   if length(pid)=0 then pid:=SYS.PidByProcessPath(MILTER_BIN_PATH());
   result:=pid;
end;
//##############################################################################
function tamavis.AMAVISD_PID():string;
var
   pid:string;
   pgrep,ps,grep,tmpstr,cmd:string;
   l:Tstringlist;
   RegExpr:tRegExpr;
   i:integer;
   xAMAVISD_PID_PATH:string;
   psTable:Tstringlist;
begin
   xAMAVISD_PID_PATH:=AMAVISD_PID_PATH();
   slogs.Add('AMAVISD_PID():: Pid path: '+xAMAVISD_PID_PATH );
   RegExpr:=TRegExpr.Create;


   if FileExists(xAMAVISD_PID_PATH) then begin
      pid:=SYS.GET_PID_FROM_PATH(xAMAVISD_PID_PATH);
   end else begin
       slogs.Add('AMAVISD_PID() "'+xAMAVISD_PID_PATH+'" no such pid file');
   end;
   slogs.Add('AMAVISD_PID():: Pid path: return '+pid );
   if pid='0' then pid:='';
   if not SYS.PROCESS_EXIST(pid) then pid:='';
      if length(pid)=0 then begin
         pgrep:=SYS.LOCATE_GENERIC_BIN('pgrep');
         ps:=SYS.LOCATE_GENERIC_BIN('ps');
         grep:=SYS.LOCATE_GENERIC_BIN('grep');
         tmpstr:=logs.FILE_TEMP();
         fpsystem(ps+' aux|'+grep+' amavis >'+tmpstr+' 2>&1 ');
         psTable:=Tstringlist.Create;
         slogs.Add('AMAVISD_PID():: '+ps+' aux|'+grep+' amavis' );
         psTable.LoadFromFile(tmpstr);
         RegExpr.Expression:='amavisd.+?amavisd\.conf reload';
         for i:=0 to psTable.Count -1 do begin
               slogs.Add('AMAVISD_PID():: '+psTable.Strings[i] );
               if RegExpr.Exec(psTable.Strings[i]) then begin
                    logs.DebugLogs('Starting......: amavisd-new under reload task, aborting');
                    logs.NOTIFICATION('Amavis, is not running but under reload task, aborting watchdog',slogs.Text,'postfix');
                    halt(0);
               end;
         end;



         logs.DeleteFile(tmpstr);

         slogs.Add('AMAVISD_PID():: pgrep: return '+pgrep );
         if not FileExists(pgrep) then begin
            logs.NOTIFICATION('Amavis, unable to locate pgrep binary..','Artica could not test if amavis is on memory without this tool','postfix');
            exit;
         end;
         tmpstr:=logs.FILE_TEMP();
         cmd:=pgrep+ ' -l -f "amavisd.+?master" >'+tmpstr+' 2>&1';
         slogs.Add('AMAVISD_PID()::  '+cmd );
         fpsystem(cmd);
         l:=Tstringlist.Create;
         l.LoadFromFile(tmpstr);
         logs.DeleteFile(tmpstr);
         slogs.Add('AMAVISD_PID()::  '+tmpstr+' ' +IntTostr(l.Count)+' rows');
         RegExpr.Expression:='([0-9]+)\s+amavis';
         for i:=0 to l.Count-1 do begin
          slogs.Add('AMAVISD_PID():: line '+ intTostr(i)+'  '+l.Strings[i] );
          if RegExpr.Exec(l.Strings[i]) then begin
              slogs.Add('AMAVISD_PID():: pgrep found -> '+RegExpr.Match[1]);
              pid:=RegExpr.Match[1];
              if length(xAMAVISD_PID_PATH)=0 then xAMAVISD_PID_PATH:='/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid';
              slogs.Add('AMAVISD_PID():: rewrite  -> "'+xAMAVISD_PID_PATH+'"');
              logs.WriteToFile(pid,xAMAVISD_PID_PATH);
              slogs.Add('AMAVISD_PID():: returned  -> "'+pid+'"');
              result:=pid;
              exit(result);
          end;
      end;


   end;
   slogs.Add('AMAVISD_PID():: returned  -> "'+pid+'"');
   result:=pid;
   exit(pid);
end;
//##############################################################################
function tamavis.AMAVIS_STAT_PID():string;
var
   pid:string;
begin
   pid:=SYS.GET_PID_FROM_PATH(AMAVIS_STAT_PID_PATH());
   if pid='0' then pid:='';
   if length(pid)=0 then pid:=SYS.PIDOF('Amavis-Stats');
   result:=pid;
end;
//##############################################################################
procedure tamavis.AMAVISD_RELOAD();
var
pid:string;
cmd:string;
begin
pid:=AMAVISD_PID();
if not SYS.PROCESS_EXIST(pid) then begin
     START_AMAVISD();
     exit;
end;

if AmavisMemoryInRAM>0 then begin
    STOP_AMAVISD();
    START_AMAVISD();
    exit;
end;


SaveConfig();
START_MILTER();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.amavis.php');
cmd:=AMAVISD_BIN_PATH() + ' -c /usr/local/etc/amavisd.conf reload &';
logs.NOTIFICATION('Reloading amavisd daemon pid '+pid+' was reloaded','using '+cmd,'postfix' );
logs.Debuglogs(cmd);
fpsystem(cmd);
end;
//##############################################################################

function tamavis.QUARANTINEDIR():string;
var
i:integer;
RegExpr:tRegExpr;
l:TstringList;
quar:string;

begin
result:='';
  if not FileExists(AMAVISD_CONF_PATH()) then begin
     logs.DebugLogs('tamavis.QUARANTINEDIR():: unable to stat amavisd.conf');
     exit;
  end;
  
  l:=TstringList.Create;
  l.LOadFromFile(AMAVISD_CONF_PATH());
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='^\$QUARANTINEDIR(.+?);';
  for i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
      quar:=RegExpr.Match[1];
      break;
      end;
  end;
  
   if length(quar)>0 then begin
       quar:=AnsiReplaceText(quar,'''','');
       quar:=AnsiReplaceText(quar,'"','');
       quar:=AnsiReplaceText(quar,'=','');
       quar:=trim(quar);
       result:=quar;
   end;

end;
//##############################################################################
procedure tamavis.include_config_files();
var
FileDatas:TstringList;
i:integer;
RegExpr:tRegExpr;
l:TstringList;

begin
  if not FileExists(AMAVISD_CONF_PATH())then exit;

  FileDatas:=TstringList.Create;
  FileDatas.LoadFromFile(AMAVISD_CONF_PATH());
  l:=TstringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='include_config_files\(''(.+?)''';
for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
            l.Add(RegExpr.Match[1]);
        end;
end;


for i:=0 to l.Count-1 do begin
    logs.DebugLogs('Starting......: amavisd-new Check permissions on include file: "'+ ExtractFilename(l.Strings[i])+'"');
    fpsystem('/bin/chown root:root ' + l.Strings[i]);
end;


end;


//##############################################################################
function tamavis.MILTER_VERSION():string;
var
tmpstr:string;
FileDatas:TstringList;
i:integer;
RegExpr:tRegExpr;

begin

result:=SYS.GET_CACHE_VERSION('APP_AMAVISD_MILTER');
   if length(result)>0 then exit;

tmpstr:=LOGS.FILE_TEMP();
if not FileExists(MILTER_BIN_PATH()) then exit;
fpsystem(MILTER_BIN_PATH()+' -v >'+tmpstr+' 2>&1');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^amavisd-milter\s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);
    
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
             SYS.SET_CACHE_VERSION('APP_AMAVISD_MILTER',result);

end;
//##############################################################################
function tamavis.AMAVISD_VERSION():string;
var
tmpstr:string;
FileDatas:TstringList;
i:integer;
RegExpr:tRegExpr;
D:boolean;
begin

result:=SYS.GET_CACHE_VERSION('APP_AMAVISD');
   if length(result)>0 then exit;

D:=false;
D:=SYS.COMMANDLINE_PARAMETERS('--verbose');
tmpstr:=LOGS.FILE_TEMP();
if not FileExists(AMAVISD_BIN_PATH()) then exit;
fpsystem(AMAVISD_BIN_PATH()+' -V >'+tmpstr+' 2>&1');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^amavisd-new-([0-9\.]+).+?([0-9]+)';
    if D then writeln('RegExpr.Expression=',RegExpr.Expression);
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    for i:=0 to FileDatas.Count-1 do begin
        if D then writeln(i,') ',FileDatas.Strings[i],' > ', RegExpr.Expression);
        if RegExpr.Exec(FileDatas.Strings[i]) then begin

             result:=RegExpr.Match[1] + ' '+RegExpr.Match[2];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
              SYS.SET_CACHE_VERSION('APP_AMAVISD',result);

end;
//##############################################################################
function tamavis.altermime_bin_path():string;
begin
if FileExists('/usr/local/bin/altermime') then exit('/usr/local/bin/altermime');
end;
//##############################################################################
procedure tamavis.AMAVIS_LOGWATCH();
var
   l:Tstringlist;
begin

l:=Tstringlist.Create;
l.add('Title = "Amavisd-new"');
l.add('LogFile = /var/log/amavis/amavis.log');
l.add('#Detail = 10');
l.add('*OnlyService = (amavis|dccproc)');
l.add('*RemoveHeaders');
l.add('$amavis_Syslog_Name = "(?:amavis|dccproc)"');
l.add('$amavis_Max_Report_Width = 100');
l.add('$amavis_Line_Style = Truncate');
l.add('$amavis_Show_Sect_Vars = No');
l.add('$amavis_Show_First_Recip_Only = No');
l.add('$amavis_Show_By_Ccat_Summary = Yes');
l.add('$amavis_Timings_Percentiles = "0 5 25 50 75 95 100"');
l.add('$amavis_Timings = 95');
l.add('$amavis_SA_Timings_Percentiles = "0 5 25 50 75 95 100"');
l.add('$amavis_SA_Timings = 100');
l.add('$amavis_Score_Percentiles = "0 50 90 95 98 100"');
l.add('$amavis_Score_Frequencies = "-10 -5 0 5 10 20 30"');
l.add('$amavis_SARules  = "20 20"');
l.add('$amavis_Show_Autolearn = Yes');
l.add('$amavis_Show_StartInfo = Yes');
l.add('$amavis_CleanPassed = 0');
l.add('$amavis_CleanBlocked = 10');
l.add('$amavis_SpamPassed = 10');
l.add('$amavis_SpamBlocked = 10');
l.add('$amavis_SpammyPassed = 10');
l.add('$amavis_SpammyBlocked = 10');
l.add('$amavis_MalwarePassed = 10');
l.add('$amavis_MalwareBlocked = 10');
l.add('$amavis_BannedNamePassed = 10');
l.add('$amavis_BannedNameBlocked = 10');
l.add('$amavis_BadHeaderPassed = 10');
l.add('$amavis_BadHeaderBlocked = 10');
l.add('$amavis_MTABlocked = 10');
l.add('$amavis_OversizedBlocked = 10');
l.add('$amavis_OtherBlocked = 10');
l.add('$amavis_AVConnectFailure = 10');
l.add('$amavis_AVTimeout = 10');
l.add('$amavis_ArchiveExtract = 10');
l.add('$amavis_BadHeaderSupp = 10');
l.add('$amavis_Bayes = 10');
l.add('$amavis_Blacklisted = 10');
l.add('$amavis_BounceKilled = 10');
l.add('$amavis_BounceRescued = 10');
l.add('$amavis_BounceUnverifiable = 10');
l.add('$amavis_ContentType = 10');
l.add('$amavis_DccError = 10');
l.add('$amavis_DefangError = 10');
l.add('$amavis_Defanged = 10');
l.add('$amavis_DsnNotification = 10');
l.add('$amavis_DsnSuppressed = 10');
l.add('$amavis_ExtraModules = 10');
l.add('$amavis_FakeSender = 10');
l.add('$amavis_LocalDeliverySkipped = 10');
l.add('$amavis_MalwareByScanner = 10');
l.add('$amavis_MalwareToSpam = 10');
l.add('$amavis_MimeError = 10');
l.add('$amavis_p0f = 2');
l.add('$amavis_Released = 10');
l.add('$amavis_SADiags = 10');
l.add('$amavis_SmtpResponse = 10');
l.add('$amavis_TmpPreserved = 10');
l.add('$amavis_VirusScanSkipped = 1');
l.add('$amavis_Warning = 10');
l.add('$amavis_WarningAddressModified = 2');
l.add('$amavis_WarningNoQuarantineID = 1');
l.add('$amavis_WarningSecurity = 10');
l.add('$amavis_WarningSmtpShutdown = 10');
l.add('$amavis_WarningSQL = 10');
l.add('$amavis_Whitelisted = 10');

ForceDirectories('/usr/local/etc');
logs.WriteToFile(l.Text,'/usr/local/etc/amavis-logwatch.conf');




end;

function tamavis.altermime_version():string;
var
l:TstringList;
i:integer;
RegExpr:tRegExpr;
tmpstr:string;
begin

if not FileExists(altermime_bin_path()) then exit;

result:=SYS.GET_CACHE_VERSION('APP_ALTERMIME');
if length(result)>0 then exit;
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='alterMIME v([0-9\.]+)';
tmpstr:=logs.FILE_TEMP();
fpsystem(altermime_bin_path() + ' --version >'+tmpstr+ ' 2>&1');
l:=TstringList.Create;
try
   l.LoadFromFile(tmpstr);
except
      logs.Syslogs('tamavis.altermime_version() FATAL ERROR');
      exit;
end;

logs.DeleteFile(tmpstr);
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
   end;
end;

SYS.SET_CACHE_VERSION('APP_ALTERMIME',result);
l.free;
RegExpr.free;
end;
//##############################################################################
function tamavis.AMAVIS_STAT_VERSION():string;
var
FileDatas:TstringList;
i:integer;
RegExpr:tRegExpr;
D:boolean;
begin

result:=SYS.GET_CACHE_VERSION('APP_AMAVIS_STAT');
if length(result)>0 then exit;
D:=false;
D:=SYS.COMMANDLINE_PARAMETERS('--verbose');

if not FileExists(AMAVIS_STAT_BIN_PATH()) then exit;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^\$myversion_id\s+(.+?)([0-9\.]+)';
    if D then writeln('RegExpr.Expression=',RegExpr.Expression);
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(AMAVIS_STAT_BIN_PATH());


    for i:=0 to FileDatas.Count-1 do begin
        if D then writeln(i,') ',FileDatas.Strings[i],' > ', RegExpr.Expression);
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
              SYS.SET_CACHE_VERSION('APP_AMAVIS_STAT',result);

end;
//##############################################################################
procedure tamavis.START_MILTER();
var
   pid,cmd:string;
   count:integer;
   AmavisGlobalConfiguration:TiniFile;
   max_conns:string;
begin

max_conns:='5';
if not FileExists(MILTER_BIN_PATH()) then begin
   logs.DebugLogs('Starting......: amavisd-milter is not installed...');
   exit;
end;

if EnablePostfixMultiInstance=1 then begin
   logs.Debuglogs('Starting......: multiples postfix instances enabled, method is set in post-queue mode');
   exit;
end;

if not fileExists(SYS.LOCATE_SU()) then begin
   logs.Syslogs('Starting......: amavisd-milter to locate su tool !!');
   exit;
end;


if EnableAmavisInMasterCF=1 then begin
    logs.Debuglogs('Starting......: method is set in post-queue mode..exiting starting amavis-milter');
    exit;
end;

if not SYS.PROCESS_EXIST(AMAVISD_PID()) then begin
    logs.Syslogs('Starting......: amavisd-milter amavisd-new is not started');
    START_AMAVISD();
end;


if not SYS.PROCESS_EXIST(AMAVISD_PID()) then begin
    logs.Syslogs('Starting......: amavisd-milter amavisd-new is not started');
    exit;
end;

forceDirectories('/var/spool/postfix/var/run/amavisd-milter');
forceDirectories('/var/amavisd-milter');
forceDirectories('/tmp/savemail');
forceDirectories('/usr/local/etc/amavis');

fpsystem('/bin/chown -R postfix:postfix /var/spool/postfix/var/run/amavisd-milter');
fpsystem('/bin/chown -R postfix:postfix /var/amavis');

forceDirectories('/usr/local/etc/amavis');
fpsystem('/bin/chown postfix:postfix /usr/local/etc/amavis');
fpsystem('/bin/chown -R postfix:postfix /usr/local/etc/amavis/*');

fpsystem('/bin/chmod 770 /var/amavis');
fpsystem('/bin/chmod 770 /tmp/savemail');
fpsystem('/bin/chown root /usr/share/artica-postfix/bin/install/amavis/check-external-users.conf >/dev/null 2>&1');

if FileExists('/etc/artica-postfix/settings/Daemons/AmavisGlobalConfiguration') then begin
     AmavisGlobalConfiguration:=TiniFile.Create('/etc/artica-postfix/settings/Daemons/AmavisGlobalConfiguration');
     max_conns:=AmavisGlobalConfiguration.ReadString('BEHAVIORS','max_servers','5');
end;


pid:=MILTER_PID();

    if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: amavisd-milter already exists using pid ' + pid+ '...');
       if FileExists('amavisd-new-milter') then logs.OutputCmd('/bin/chown postfix:postfix /var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock');
       exit;
    end;
logs.Debuglogs('Starting......: amavisd-milter EnableAmavisInMasterCF='+ IntToStr(EnableAmavisInMasterCF));
    
PatchPerlT();
cmd:=MILTER_BIN_PATH()+' ';
cmd:=cmd + '-f ';
cmd:=cmd + '-m '+max_conns+' ';
cmd:=cmd + '-p /var/spool/postfix/var/run/amavisd-milter/amavisd-milter.pid ';
cmd:=cmd + '-s /var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock ';
cmd:=cmd + '-S /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock ';
cmd:=cmd + '-t 300 ';
cmd:=cmd + '-T 600 ';
cmd:=cmd + '-w /var/amavis ';

cmd:=SYS.LOCATE_SU() + ' postfix -c "'+cmd+'" >/dev/null 2>&1 &';
logs.OutputCmd(cmd);
logs.Debuglogs('Starting......: amavisd-milter');
fpsystem(cmd);
logs.NOTIFICATION('starting amavisd-milter daemon','Artica starting the Amavis-milter daemon with cmdline:'+cmd,'postfix' );
  count:=0;
  while not SYS.PROCESS_EXIST(MILTER_PID()) do begin
              sleep(150);
              inc(count);
              if count>30 then begin
                 writeln('');
                 logs.DebugLogs('Starting......: amavisd-milter (timeout!!!)');
                 break;
              end;
              write('.');
        end;
        
        
if not SYS.PROCESS_EXIST(MILTER_PID()) then begin
       logs.Syslogs('Starting......: amavisd-milter in an old way');
       fpsystem(MILTER_BIN_PATH()+' -D -p /var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock');
       while not SYS.PROCESS_EXIST(MILTER_PID()) do begin
              sleep(150);
              inc(count);
              if count>20 then begin
                 logs.DebugLogs('Starting......: amavisd-milter (timeout!!!)');
                 logs.DebugLogs('Starting......: '+MILTER_BIN_PATH()+' -D -p /var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock');
                 break;
              end;
       end;
       logs.OutputCmd('/bin/chown postfix:postfix /var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock');
end;



    if not SYS.PROCESS_EXIST(MILTER_PID()) then begin
         logs.DebugLogs('Starting......: amavisd-milter (failed!!!)');
    end else begin
         logs.DebugLogs('Starting......: amavisd-milter PID '+MILTER_PID());

    end


end;
//##############################################################################
procedure tamavis.CleanCacheDatabases();
var
l:Tstringlist;
i:integer;

begin

l:=Tstringlist.Create;
l.Add('/var/amavis/db/cache.db');
l.Add('/var/amavis/db/cache-expiry.db');
l.Add('/var/amavis/db/__db.001');
l.Add('/var/amavis/db/__db.002');
l.Add('/var/amavis/db/__db.003');
l.Add('/var/amavis/db/__db.004');
l.Add('/var/amavis/db/nanny.db');
l.Add('/var/amavis/db/snmp.db');

for i:=0 to l.Count-1 do begin
    if length(trim(l.Strings[i]))=0 then continue;
    if FIleExists(l.Strings[i]) then begin
       logs.DebugLogs('Starting......: amavisd-new deleting db cache file "'+ExtractFilename(l.Strings[i])+'"');
       logs.DeleteFile(l.Strings[i]);
    end;
end;

end;
//##############################################################################



procedure tamavis.START_AMAVIS_STAT();
var
   pid,cmd:string;
   count:integer;
begin

 if FileExists('/etc/cron.d/amavis-stats') then logs.DeleteFile('/etc/cron.d/amavis-stats');


 if not FileExists(AMAVIS_STAT_BIN_PATH()) then begin;
   logs.DebugLogs('Starting......: amavis-stat is not installed...');
   exit;
end;

if EnableAmavisDaemon=0 then begin
    logs.DebugLogs('Starting......: amavisd-new is disabled by EnableAmavisDaemon file key');
    exit;
end;

pid:=AMAVIS_STAT_PID();

    if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: amavisd-stat already exists using pid ' + pid+ '...');
       exit;
    end;




forceDirectories('/usr/local/var/lib/amavis-stats');
forceDirectories('/usr/local/var/cache/amavis-stats');
logs.OutputCmd('/bin/chown -R postfix:postfix /usr/local/var/lib/amavis-stats');
logs.OutputCmd('/bin/chown -R postfix:postfix /usr/local/var/cache/amavis-stats');
fpsystem('/bin/chown root /usr/share/artica-postfix/bin/install/amavis/check-external-users.conf >/dev/null 2>&1');




AMAVIS_STAT_WRITE_CONF();
cmd:=AMAVIS_STAT_BIN_PATH() + ' -u postfix -g postfix -c /usr/local/etc/amavis-stats.conf';
logs.Debuglogs(cmd);
logs.NOTIFICATION('starting amavisd-stat daemon','Artica starting the Amavisd-stat daemon with cmdline:'+cmd,'postfix' );
fpsystem(cmd);
  count:=0;
  while not SYS.PROCESS_EXIST(AMAVIS_STAT_PID()) do begin
              sleep(150);
              inc(count);
              if count>30 then begin
                 writeln('');
                 logs.DebugLogs('Starting......: amavisd-stat (timeout!!!)');
                 break;
              end;
              write('.');
        end;
writeln('');
    if not SYS.PROCESS_EXIST(AMAVISD_PID()) then begin
         logs.DebugLogs('Starting......: amavisd-stat (failed!!!)');
    end else begin
         logs.DebugLogs('Starting......: amavisd-stat PID '+AMAVIS_STAT_PID());

    end;


end;
//##############################################################################
procedure tamavis.STOP_AMAVIS_STAT();
var pid:string;
begin

    if not FileExists(AMAVIS_STAT_BIN_PATH()) then begin
       writeln('Stopping amavisd-stat....: not installed');
       exit;
    end;


    pid:=AMAVIS_STAT_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping amavisd-stat....: Already stopped');
       exit;
    end;

    writeln('Stopping amavisd-stat....: ' + pid + ' PID');
    fpsystem('kill ' + pid);
    pid:=SYS.PidAllByProcessPath(AMAVIS_STAT_BIN_PATH());

    if length(trim(pid))>0 then begin
       writeln('Stopping amavisd-stat....: ' + pid + ' PIDs');
       fpsystem('/bin/kill -9 ' + pid);
    end;

end;
//##############################################################################
procedure tamavis.START_AMAVISD();
var
   pid,cmd:string;
   count:integer;
   EnableMysql:integer;
   CopyToDomainSpool:string;
   EnableAmavisInMasterCF:Integer;

begin
EnableMysql:=0;

if not FileExists(AMAVISD_BIN_PATH()) then begin
   logs.DebugLogs('Starting......: amavisd-new is not installed...');
   exit;
end;

if EnableAmavisDaemon=0 then begin
    logs.DebugLogs('Starting......: amavisd-new is disabled by EnableAmavisDaemon file key');
    logs.DebugLogs('Starting......: Server memory: '+IntToStr(mem_installee)+' K');

    exit;
end;

EnableAmavisInMasterCF:=0;
if not TryStrToInt(SYS.GET_INFO('EnableAmavisInMasterCF'),EnableAmavisInMasterCF) then EnableAmavisInMasterCF:=0;

pid:=AMAVISD_PID();
slogs.Add('START_AMAVISD(): pid returned by AMAVISD_PID(): "'+pid+'"');

    if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: amavisd-new already exists using pid ' + pid+ '...');
       logs.DebugLogs('Starting......: Server memory: '+IntToStr(mem_installee)+' K');
       if EnablePostfixMultiInstance=0 then begin
          if EnableAmavisInMasterCF=0 then begin
             if FileExists('/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock') then begin
               logs.OutputCmd('/bin/chown postfix:postfix /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock');
             end else begin
                slogs.Add('START_AMAVISD(): /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock no such file');
                slogs.Add('START_AMAVISD(): -> CheckUnixSocketName()');
                CheckUnixSocketName();
                slogs.Add('START_AMAVISD(): -> STOP_AMAVISD()');
                logs.NOTIFICATION('amavisd-new daemon ...amavisd-new.sock No such file','/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock did not exists, amavis will be stopped '+slogs.Text ,'postfix' );
                STOP_AMAVISD();
                exit;
             end;
           end;
         end;
         exit;
       end;
if FileExists('/etc/init.d/amavis') then SYS.DeleteService('amavis');
if FileExists('/etc/init.d/amavisd-new-milter') then SYS.DeleteService('amavisd-new-milter');

logs.DebugLogs('Starting......: amavisd-new cleaning /var/amavis directory');
logs.DebugLogs('Starting......: Server memory: '+IntToStr(mem_installee)+' K');
slogs.Add('Server memory:'+IntToStr(mem_installee)+' K');

if IS_AMAVIS_MOUNTED_IN_RAM() then begin
   logs.DebugLogs('Starting......: amavisd-new dismount RAM for amavis');
   logs.OutputCmd('/bin/umount -f /var/amavis');
   sleep(300);
end;

logs.DebugLogs('Starting......: amavisd-new using RAM for working directory ? ('+IntToStr(AmavisMemoryInRAM)+'M)');
if DirectoryExists('/var/amavis') then logs.OutputCmd('/bin/rm -rf /var/amavis');
forceDirectories('/var/amavis');
if AmavisMemoryInRAM>0 then begin
   if not IS_AMAVIS_MOUNTED_IN_RAM() then begin
      logs.DebugLogs('Starting......: amavisd-new mounting /var/amavis directory in memory for '+IntToStr(AmavisMemoryInRAM)+'M');
      logs.OutputCmd('/bin/mount -t tmpfs -o size='+IntToStr(AmavisMemoryInRAM)+'M tmpfs /var/amavis');
      sleep(300);
    end;

   if IS_AMAVIS_MOUNTED_IN_RAM() then begin
     logs.DebugLogs('Starting......: amavisd-new using now RAM for working directory');
   end else begin
     logs.DebugLogs('Starting......: amavisd-new failed to mount memory...(using disk)');
   end;
end;


if FileExists('/etc/artica-postfix/settings/Daemons/AmavisConfigFile') then begin
   logs.DebugLogs('Starting......: amavisd-new Replicate original configuration (/usr/local/etc/amavisd.conf)');
   fpsystem('/bin/cp /etc/artica-postfix/settings/Daemons/AmavisConfigFile /usr/local/etc/amavisd.conf');
end;




forceDirectories('/var/amavis-plugins');
forceDirectories('/var/amavis/tmp');
forceDirectories('/var/amavis/db');
forceDirectories('/var/amavis/var');
forceDirectories('/var/amavis/run');
forceDirectories('/var/amavis/dspam');
forceDirectories('/var/virusmails');
forceDirectories('/var/log/amavis');
forceDirectories('/var/log/artica-postfix/RTM');
forceDirectories('/etc/amavis/dkim');

forceDirectories('/tmp/savemail');
fpsystem('/bin/chmod 777 /tmp/savemail');


if not FileExists('/var/log/amavis/amavis.log') then begin
   fpsystem('/bin/touch /var/log/amavis/amavis.log');
   fpsystem('/bin/chown postfix:postfix /var/log/amavis/amavis.log');
end;

fpsystem('/bin/cp /usr/share/artica-postfix/bin/install/amavis/check-external-users.conf /var/amavis-plugins/check-external-users.conf');



forceDirectories('/usr/local/etc/amavis');
fpsystem('/bin/chown postfix:postfix /usr/local/etc/amavis');
fpsystem('/bin/chown -R postfix:postfix /usr/local/etc/amavis/*');
fpsystem('/bin/chmod -R 755 /usr/local/etc/amavis');


 if not TryStrToInt(SYS.GET_INFO('EnableMysqlFeatures'),EnableMysql) then EnableMysql:=0;

 CopyToDomainSpool:=SYS.GET_INFO('CopyToDomainSpool');
 if length(CopyToDomainSpool)=0 then CopyToDomainSpool:='/var/spool/artica/copy-to-domain';
 forceDirectories(CopyToDomainSpool);
 logs.OutputCmd('/bin/chown -R postfix:postfix '+CopyToDomainSpool);
 

 
 
if not FileExists('/usr/local/etc/sender_scores_sitewide') then fpsystem('/bin/touch /usr/local/etc/sender_scores_sitewide');
fpsystem('/bin/chmod 755 /usr/local/etc/sender_scores_sitewide');

include_config_files();

//if FileExists('/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid') then logs.DeleteFile('/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid');
//if fileExists('/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock') then logs.DeleteFile('/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock');
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.amavis.php');

forceDirectories('/var/amavis/.spamassassin');
forceDirectories('/etc/amavis/dkim');

fpsystem('/bin/chown -R postfix:postfix /var/amavis/.spamassassin');
fpsystem('/bin/chmod 755 /var/amavis/.spamassassin');
forceDirectories('/var/spool/postfix/var/run/amavisd-new');
fpsystem('/bin/chown -R postfix:postfix /var/amavis');
fpsystem('/bin/chown -R postfix:postfix /var/virusmails');
fpsystem('/bin/chown -R postfix:postfix /var/spool/postfix/var/run/amavisd-new');
fpsystem('/bin/chown -R postfix:postfix /var/log/amavis');
fpsystem('/bin/chown -R postfix:postfix /etc/amavis/dkim');
fpsystem('/bin/chmod 777 /var/log/artica-postfix/RTM');
fpsystem('/bin/chmod 755 /etc/amavis/dkim');
fpsystem('/bin/chmod -R 755 /etc/amavis/dkim/* >/dev/null 2>&1');
fpsystem('/bin/chown postfix:root /var/log/artica-postfix/RTM');


    if not FileExists('/etc/artica-postfix/amavis.modules.checked') then begin
       if not CHECK_MODULES() then begin
         logs.Syslogs('Starting......: amavisd: Checking perl modules failed');
         logs.Syslogs('Starting......: amavisd: Please run "artica-make APP_AMAVISD_MILTER"');
         logs.Syslogs('Starting......: amavisd: In order to install components');
         logs.Syslogs('Starting......: amavisd: stopping start procedure');
         exit;
       end else begin
        logs.WriteToFile('#','/etc/artica-postfix/amavis.modules.checked');
        logs.Syslogs('Starting......: amavisd: Checking perl modules success');
      end;
   end else begin
      logs.Debuglogs('Starting......: amavisd: Modules are already been checked');
      if SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/amavis.modules.checked')>240 then logs.DeleteFile('/etc/artica-postfix/amavis.modules.checked');
   end;


if fileExists(dspam.BIN_PATH()) then begin
      logs.Debuglogs('Starting......: amavisd: Dspam is detected');
      if EnableMysql=1 then begin
         AMAVISD_SETCONFIG('dspam',SYS.LOCATE_DSPAM());
         spamassassin.DSPAM_PATCH();
         spamassassin.RAZOR_INIT();

         dspam.SET_CONFIG();
         fpsystem('chmod u-s,a+rx ' + SYS.LOCATE_DSPAM());
      end;
      
      if EnableMysql=0 then begin
         AMAVISD_SETCONFIG('dspam',' ');
         logs.Debuglogs('Starting......: Dspam is detected but disabled, EnableMysqlFeatures=0');
      end;
end else begin
   logs.Debuglogs('Starting......: Dspam is not detected');

end;

spamassassin.DEFAULT_SETTINGS();
if not FileExists( spamassassin.SPAMASSASSIN_BIN_PATH()) then begin
   fpsystem('/usr/share/artica-postfix/bin/artica-make APP_SPAMASSASSIN');
end;

logs.OutputCmd('/bin/chmod 755 /usr/local/etc/amavisd.conf');
CleanCacheDatabases();

cmd:=AMAVISD_BIN_PATH()+' ';
cmd:=cmd + '-u postfix ';
cmd:=cmd + '-g postfix ';
cmd:=cmd + '-H /var/amavis ';
cmd:=cmd + '-T /var/amavis/tmp ';
cmd:=cmd + '-Q /var/virusmails ';
cmd:=cmd + '-D /var/amavis/db ';
cmd:=cmd + '-S /var/amavis/var ';
cmd:=cmd + '-L /var/amavis/run/amavis.lock ';
cmd:=cmd + '-P /var/spool/postfix/var/run/amavisd-new/amavisd-new.pid ';
cmd:=cmd + '-c /usr/local/etc/amavisd.conf ';

fpsystem('/usr/share/artica-postfix/bin/artica-make APP_COMPRESS_ROW_ZLIB >/dev/null 2>&1 &');

if EnablePostfixMultiInstance=0 then begin
   if EnableAmavisInMasterCF=0 then begin
      cmd:=cmd + '-p /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock';
   end else begin
    logs.Debuglogs('Starting......: amavisd-new using port 10024');
    cmd:=cmd + '-p 10024';
   end;
end;

cmd:=cmd+' start &';
logs.NOTIFICATION('starting amavisd-new daemon','Artica starting the Amavisd-new daemon with cmdline:'+cmd +' '+slogs.Text ,'postfix' );
logs.Debuglogs('Starting......: amavisd: using '+cmd);

SaveConfig();
logs.Syslogs('Starting......: amavisd-new...');

logs.Debuglogs(cmd);


fpsystem('/bin/chmod 644 /var/amavis-plugins/check-external-users.conf');
fpsystem('/bin/chmod 755 /var/amavis-plugins');
fpsystem('/bin/chown root:root /var/amavis-plugins');
fpsystem('/bin/chown root:root /var/amavis-plugins/check-external-users.conf');
fpsystem(cmd);


count:=0;
  while not SYS.PROCESS_EXIST(AMAVISD_PID()) do begin
              sleep(100);
              inc(count);
              if count>30 then begin
                 writeln('');
                 logs.DebugLogs('Starting......: amavisd-new (timeout!!!)');
                 break;
              end;
              write('.');
        end;

 // fetch_modules: error loading optional module Encode/Detect.pm

    if not SYS.PROCESS_EXIST(AMAVISD_PID()) then begin
         writeln('');
         logs.DebugLogs('Starting......: amavisd-new (failed!!!)');
         logs.DebugLogs(cmd);
    end else begin
         writeln('');
         logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+') Amavis has been successfully started PID '+AMAVISD_PID(),'this is an information...','system');
         logs.DebugLogs('Starting......: amavisd-new PID '+AMAVISD_PID());
    end;

    if FileExists('/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock') then fpsystem('/bin/chown postfix:postfix /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock');
end;
//##############################################################################
procedure tamavis.START();
begin
    logs.DebugLogs('################# AMAVIS ######################');
    

    
    if EnableAmavisDaemon=0 then begin
       logs.Debuglogs('Starting......: amavis components are disabled by EnableAmavisDaemon parameter');
       STOP();
       exit;
    end;
    WRITE_INITD();


    logs.Debuglogs('Starting......: amavis components');
    START_AMAVISD();
    START_MILTER();
    START_AMAVIS_STAT();

end;
//##############################################################################
function tamavis.IS_AMAVIS_MOUNTED_IN_RAM():boolean;
var
   tmpstr:string;
   l:TstringList;
   RegExpr:tRegExpr;
   i:Integer;
begin
   tmpstr:=logs.FILE_TEMP();
   fpsystem('/bin/mount >'+tmpstr+' 2>&1');
   if not FileExists(tmpstr) then exit;
   result:=false;
   l:=TStringList.Create;
   l.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^tmpfs.+?amavis';
   for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=true;
          break;
       end;

   end;

RegExpr.free;
l.free;

end;
//##############################################################################
procedure tamavis.SaveConfig();
begin


if FileExists('/etc/artica-postfix/settings/Daemons/AmavisConfigFile') then begin
     if SYS.FILE_TIME_BETWEEN_MIN('/usr/local/etc/amavisd.conf')>0 then begin
        logs.DebugLogs('Starting......: replicate amavis configuration file...');
        logs.OutputCmd('/bin/cp /etc/artica-postfix/settings/Daemons/AmavisConfigFile /usr/local/etc/amavisd.conf');
     end;
end;
     CheckMyHostname();
     CheckUnixSocketName();
     CheckLogfile();
     spamassassin.DEFAULT_SETTINGS();
     AMAVISD_SETCONFIG('daemon_user','postfix');
     AMAVISD_SETCONFIG('daemon_group','postfix');
end;
//##############################################################################

procedure tamavis.CheckUnixSocketName();
var
l:TstringList;
RegExpr:tRegExpr;
i:Integer;
begin
    if EnablePostfixMultiInstance=1 then exit;
    if not FileExists('/usr/local/etc/amavisd.conf') then exit;
    l:=TstringList.Create;
    l.LoadFromFile('/usr/local/etc/amavisd.conf');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^\$unix_socketname[\s=]+"(.+?)"';
    for i:=0 to l.Count-1 do begin
             if RegExpr.Exec(l.Strings[i]) then begin
                if trim(RegExpr.Match[1])<>'/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock' then begin
                   logs.Debuglogs('Starting......: amavis change socket path to "/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock"');
                   l.Strings[i]:='$unix_socketname = "/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock";';
                   try
                      l.SaveToFile('/usr/local/etc/amavisd.conf');
                   except
                      logs.Debuglogs('CheckUnixSocketName:: FATAL ERROR WHILE SAVING /usr/local/etc/amavisd.conf');
                      exit;
                   end;
                   l.free;
                   RegExpr.free;
                   exit;
                end;
             end;

    end;
                   l.free;
                   RegExpr.free;


end;
//##############################################################################
procedure tamavis.CheckMyHostname();
var
l:TstringList;
RegExpr:tRegExpr;
i:Integer;
myhostname:string;
amavishostname:string;
begin
  myhostname:=SYS.HOSTNAME_g();
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='(.+)\.(.+)';
  if not RegExpr.Exec(myhostname) then myhostname:=myhostname+'.local';
  l:=TstringList.Create;
  if not FileExists('/usr/local/etc/amavisd.conf') then logs.WriteToFile('#','/usr/local/etc/amavisd.conf');
  l.LoadFromFile('/usr/local/etc/amavisd.conf');
  RegExpr.Expression:='\$myhostname(.+?);';
  for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            amavishostname:=RegExpr.Match[1];
             logs.Debuglogs('amavishostname=' +amavishostname);
             amavishostname:=AnsiReplaceText(amavishostname,'"','');
             amavishostname:=AnsiReplaceText(amavishostname,' ','');
             amavishostname:=AnsiReplaceText(amavishostname,'''','');
             amavishostname:=AnsiReplaceText(amavishostname,'=','');
             amavishostname:=AnsiReplaceText(amavishostname,';','');
             amavishostname:=AnsiReplaceText(amavishostname,'.(none)','');
             amavishostname:=trim(amavishostname);
             RegExpr.Expression:='(.+)\.(.+)';
             if not RegExpr.Exec(amavishostname) then begin
                   logs.Debuglogs('Starting......: amavis invalid server name "'+amavishostname+'" change to "'+myhostname+'"');
                   l.Strings[i]:='$myhostname = "'+myhostname+'";';
                   try
                      l.SaveToFile('/usr/local/etc/amavisd.conf');
                   except
                      logs.Syslogs('Starting......: amavis tamavis.CheckMyHostname() fatal error while saving /usr/local/etc/amavisd.conf');
                   end;
             end;
               l.free;
               RegExpr.free;
               exit;
         end;

  end;
  logs.Debuglogs('Starting......: amavis invalid server name "null" change to "'+myhostname+'"');
  l.Add('$myhostname = "'+myhostname+'";');
  try
    l.SaveToFile('/usr/local/etc/amavisd.conf');
  except
   logs.Syslogs('Starting......: amavis tamavis.CheckMyHostname() fatal error while saving /usr/local/etc/amavisd.conf');
  end;
  logs.Debuglogs('Starting......: amavis server name "'+amavishostname+'"');
  l.free;
  RegExpr.free;
end;
//##############################################################################
procedure tamavis.CheckLogfile();
var
l:TstringList;
RegExpr:tRegExpr;
i:Integer;
patternfound:string;
begin

  l:=TstringList.Create;
  if not FileExists('/usr/local/etc/amavisd.conf') then logs.WriteToFile('#','/usr/local/etc/amavisd.conf');
  l.LoadFromFile('/usr/local/etc/amavisd.conf');
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='\$LOGFILE(.+)';
  for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
             patternfound:=RegExpr.Match[1];
             patternfound:=AnsiReplaceText(patternfound,'"','');
             patternfound:=AnsiReplaceText(patternfound,' ','');
             patternfound:=AnsiReplaceText(patternfound,'''','');
             patternfound:=AnsiReplaceText(patternfound,'=','');
             patternfound:=AnsiReplaceText(patternfound,';','');
             patternfound:=trim(patternfound);
             if patternfound<>'/var/log/amavis/amavis.log' then begin
                   logs.Debuglogs('Starting......: amavis invalid log file "'+patternfound+'" change to "/var/log/amavis/amavis.log"');
                   l.Strings[i]:='$LOGFILE = "/var/log/amavis/amavis.log";';
                   try
                      l.SaveToFile('/usr/local/etc/amavisd.conf');
                   except
                      logs.Syslogs('Starting......: amavis tamavis.CheckMyHostname() fatal error while saving /usr/local/etc/amavisd.conf');
                   end;
             end;
               l.free;
               RegExpr.free;
               exit;
         end;

  end;
  logs.Debuglogs('Starting......: amavis invalid log file "null" change to "/var/log/amavis/amavis.log"');
  l.Add('$LOGFILE = "/var/log/amavis/amavis.log";');
  try
    l.SaveToFile('/usr/local/etc/amavisd.conf');
  except
   logs.Syslogs('Starting......: amavis tamavis.CheckMyHostname() fatal error while saving /usr/local/etc/amavisd.conf');
  end;

  l.free;
  RegExpr.free;
end;
//##############################################################################
procedure tamavis.PatchPerlT();
var
l:TstringList;
begin
if not FileExists(AMAVISD_BIN_PATH()) then exit;
l:=TstringList.Create;
l.LoadFromFile(AMAVISD_BIN_PATH());
if trim(l.Strings[0])<>'#!/usr/bin/perl' then begin
    l.Strings[0]:='#!/usr/bin/perl';
    l.SaveToFile(AMAVISD_BIN_PATH());
end;

l.free;
end;
//##############################################################################

function tamavis.CHECK_MODULES():boolean;
var
   l:TstringList;
   i:integer;
   version:string;
   versionbin:integer;
begin
result:=true;
l:=TstringList.Create;
l.Add('Crypt::OpenSSL::RSA');
l.Add('Mail::Address');
l.Add('Mail::DKIM');
l.Add('Digest::SHA1');
l.Add('IO::Stringy');
l.Add('Unix::Syslog');
l.Add('MIME::Words');
l.Add('Net::Server');
L.add('BerkeleyDB');
L.add('GSSAPI');
L.Add('Authen::SASL');
L.add('XML::NamespaceSupport');
L.add('XML::SAX');
L.Add('XML::Filter::BufferText');
L.Add('XML::SAX::Writer');
L.Add('Net::LDAP');
L.Add('Config::IniFiles');
L.Add('Geo::IP');
L.Add('Convert::UUlib');
L.Add('DBI');
     for i:=0 to l.Count-1 do begin
         if not SYS.CHECK_PERL_MODULES(l.Strings[i]) then begin
              logs.Debuglogs('Starting......: amavis '+l.Strings[i]+' is not installed,False');
              exit(false);
         end else begin
              logs.Debuglogs('Starting......: amavis '+l.Strings[i]+' is installed,True');
         end;
     end;

version:=SYS.CHECK_PERL_MODULES_VERSION('Mail::DKIM');


logs.Debuglogs('Starting......: amavis Mail::DKIM has version '+version);
version:=AnsiReplaceText(version,'.','');
if TryStrToInt(version,versionbin) then begin
   if versionbin<30 then begin
       logs.Debuglogs('Starting......: amavis Mail::DKIM version is too old, try to install new one');
       fpsystem('/usr/share/artica-postfix/bin/artica-make APP_MAIL_DKIM');
       exit(false);
   end;
end;
version:=SYS.CHECK_PERL_MODULES_VERSION('Compress::Raw::Zlib');
logs.Debuglogs('Starting......: amavis Compress::Raw::Zlib has version '+version);

if version='2.008' then begin
     logs.Debuglogs('Starting......: amavis Compress::Raw::Zlib is too old, try to install new one');
     fpsystem('/usr/share/artica-postfix/bin/artica-make APP_COMPRESS_ROW_ZLIB');
     exit(false);
end;



exit(true);


end;
//##############################################################################


procedure tamavis.AMAVISD_SETCONFIG(key:string;value:string);
var
   l:TstringList;
   i:integer;
   RegExpr:tRegExpr;
   line:string;
   found:boolean;
begin

l:=Tstringlist.Create;
l.LoadFromFile('/usr/local/etc/amavisd.conf');
line:='$'+key+' = '''+value+''';';
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='\$'+key+'.+';

for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       found:=true;
       l.Strings[i]:=line;
       break;
    end;
end;

if not found then l.Add(line);
l.SaveToFile('/usr/local/etc/amavisd.conf');
l.free;
RegExpr.free;
end;
//##############################################################################
procedure tamavis.STOP_AMAVISD();
var
   pid:string;
   count:integer;
begin

    if not FileExists(AMAVISD_BIN_PATH()) then begin
       writeln('Stopping amavisd-new.....: not installed');
       exit;
    end;


    pid:=AMAVISD_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping amavisd-new.....: Already stopped');
       exit;
    end;



    writeln('Stopping amavisd-new.....: ' + pid + ' PID');
    logs.NOTIFICATION('stopping amavisd-new daemon ' + pid + ' PID','Artica stopping the Amavisd-new daemon','postfix' );
    logs.OutputCmd('/bin/kill ' + pid);

sleep(100);
count:=0;
  while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);
      write('.');
      inc(count);
       if count>30 then begin
            writeln('Stopping amavisd-new.....: Timeout while stopping '+pid);
            break;
       end;
  end;

if SYS.PROCESS_EXIST(pid) then begin
   writeln('Stopping amavisd-new.....: Force stopping '+pid);
   logs.OutputCmd('/bin/kill -9 ' + pid);
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);
      write('.');
      inc(count);
       if count>50 then begin
            writeln('Stopping amavisd-new.....: Timeout while force stopping '+pid);
            break;
       end;
   end;
end;



  pid:=SYS.PIDOF(AMAVISD_BIN_PATH());
  if sys.PROCESS_EXIST(pid) then begin
       writeln('Stopping amavisd-new.....: force stopping childs '+ sys.PROCESSES_LIST(AMAVISD_BIN_PATH()));
       logs.OutputCmd('/bin/kill -9 '+sys.PROCESSES_LIST(AMAVISD_BIN_PATH()));
       count:=0;

       while SYS.PROCESS_NUMBER(AMAVISD_BIN_PATH())>0 do begin
             sleep(500);
             write('.');
             inc(count);
             if count>10 then begin
                writeln('Stopping amavisd-new.....: Timeout while force stopping childs ');
                break;
             end;
       end;

   end;

pid:=SYS.PIDOF(AMAVISD_BIN_PATH());
  if sys.PROCESS_EXIST(pid) then begin
      writeln('Stopping amavisd-new.....: Failed to stop amavisd-new ');
  end else begin
      writeln('Stopping amavisd-new.....: Success stop amavisd-new ');
      logs.NOTIFICATION('[ARTICA]: ('+SYS.HOSTNAME_g()+') Amavis has been successfully sopped','this is an information...','system');
  end;


end;
//##############################################################################
procedure tamavis.STOP_MILTER();
var pid:string;
begin

    if not FileExists(MILTER_BIN_PATH()) then begin
       writeln('Stopping amavisd-milter..: not installed');
       exit;
    end;


    pid:=MILTER_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping amavisd-milter..: Already stopped');
       exit;
    end;

    writeln('Stopping amavisd-milter..: ' + pid + ' PID');
    logs.NOTIFICATION('stopping amavisd-milter daemon ' + pid + ' PID','Artica stopping the amavisd-milter daemon','postfix' );
    logs.Debuglogs('Stopping amavisd-milter..: ' + pid + ' PID');
    fpsystem('kill ' + pid);
    pid:=SYS.PidAllByProcessPath(MILTER_BIN_PATH());
    if length(trim(pid))>0 then begin
    writeln('Stopping amavisd-milter..: ' + pid + ' PIDs');
    logs.Debuglogs('Stopping amavisd-milter..: ' + pid + ' PIDs');
     fpsystem('/bin/kill -9 ' + pid);
    end;

end;
//##############################################################################


procedure tamavis.STOP();
begin
    STOP_MILTER();
    STOP_AMAVISD();
    STOP_AMAVIS_STAT();
end;
//##############################################################################
function tamavis.STATUS():string;
begin
result:=AMAVISD_STATUS()+AMAVISD_MILTER_STATUS();
end;
//#########################################################################################
function tamavis.AMAVISD_STATUS():string;
var pidpath:string;
begin
   SYS.MONIT_DELETE('APP_AMAVISD_NEW');
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --amavis >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
function tamavis.AMAVISD_MILTER_STATUS():string;
var pidpath:string;
begin
   SYS.MONIT_DELETE('APP_AMAVISD_MILTER');
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --amavis-milter >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
procedure tamavis.AMAVIS_STAT_WRITE_CONF();
var
l:TstringList;
begin

l:=TstringList.Create;
l.Add('use strict;');
l.Add('$MYHOME				= "/usr/local/var/lib/amavis-stats";');
l.Add('$MYCACHE				= "/usr/local/var/cache/amavis-stats";');
l.Add('$daemon_user			= "postfix";');
l.Add('$daemon_group			= "postfix";');
l.Add('$pid_file			= "$MYHOME/amavis-stats.pid";');
l.Add('$scan_logfile			= "/var/log/amavis/amavis.log";');
l.Add('$scan_time			= undef;');
l.Add('$path				= "/usr/local/sbin:/usr/local/bin:/usr/sbin:/sbin:/usr/bin:/bin";');
l.Add('$DO_SYSLOG 			= 1;');
l.Add('$SYSLOG_LEVEL			= "mail.info";');
l.Add('$LOGFILE				= undef; ');
l.Add('$DEBUG				= 0;');
l.Add('1;');
try
   l.SaveToFile('/usr/local/etc/amavis-stats.conf');
except
   logs.Syslogs('tamavis.AMAVIS_STAT_WRITE_CONF():: Unable to save /usr/local/etc/amavis-stats.conf');
end;
end;
//#########################################################################################

procedure tamavis.WRITE_INITD();
var
   l:TstringList;
begin

l:=TstringList.Create;
l.add('#! /bin/sh');
l.add('#');
l.add('# amavisd-new		Startup script for the amavisd.');
l.add('#');
l.add('#');
l.add('### BEGIN INIT INFO');
l.add('# Provides:          amavisd');
l.add('# Required-Start:    $local_fs $network');
l.add('# Required-Stop:     $local_fs $network');
l.add('# Should-Start:      $named');
l.add('# Should-Stop:       $named');
l.add('# Default-Start:     2 3 4 5');
l.add('# Default-Stop:      0 1 6');
l.add('# Short-Description: amavisd');
l.add('### END INIT INFO');
l.add('');
l.add('PATH=/bin:/usr/bin:/sbin:/usr/sbin');
l.add('');
l.add('');
l.add('start () {');
l.add('	/etc/init.d/artica-postfix start amavis');
l.add('}');
l.add('');
l.add('stop () {');
l.add('      /etc/init.d/artica-postfix stop amavis');
l.add('}');
l.add('');
l.add('case "$1" in');
l.add('    start)');
l.add('	/etc/init.d/artica-postfix start amavis');
l.add('	;;');
l.add('    stop)');
l.add('	/etc/init.d/artica-postfix stop amavis');
l.add('	;;');
l.add('    reload|force-reload)');
l.add('	/etc/init.d/artica-postfix stop amavis');
l.add('	/etc/init.d/artica-postfix start amavis');
l.add('	;;');
l.add('    restart)');
l.add('	/etc/init.d/artica-postfix stop amavis');
l.add('	/etc/init.d/artica-postfix start amavis');
l.add('	;;');
l.add('    *)');
l.add('	echo "Usage:  {start|stop|reload|force-reload|restart}"');
l.add('	exit 3');
l.add('	;;');
l.add('esac');
l.add('');
l.add('exit 0');
try
if FileExists(AMAVISD_INIT_PATH()) then l.SaveToFile(AMAVISD_INIT_PATH());
if FileExists(MILTER_INIT_PATH()) then l.SaveToFile(MILTER_INIT_PATH());
except
exit;
end;
l.free;


end;
//#############################################################################

end.

