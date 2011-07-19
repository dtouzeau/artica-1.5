unit update;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,strutils,Process,unix,global_conf,Logs,RegExpr in 'RegExpr.pas',IniFiles,dateutils,zsystem,spamass,kav4samba,kretranslator,clamav,dansguardian,cyrus,squid,squidguard,
kavmilter    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kavmilter.pas',
kas3         in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kas3.pas',
kav4proxy    in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kav4proxy.pas';
  type
  tupdate=class


private
       RetranslatorEnabled:integer;
       D:boolean;
       FORCED:boolean;
       GLOBAL_INI:myconf;
       LOGS:Tlogs;
       spamassassin:Tspamass;
       SYS:Tsystem;
       MasterIndexFile:string;
       SQUIDEnable:integer;
       SambaEnabled:integer;
       EnableKav4Samba:integer;
       kavicapserverEnabled:integer;
       EnableMalwarePatrol:integer;
       uriplus:string;
       function perform_apt_install(c:string;FilePath:string):boolean;
       procedure CheckAndInstall_verify(FilePath:string);
       function StripNumber(MyNumber:string):integer;
       function IfPatchExists(PatchNumber:integer;Artica_version:string):boolean;
       function ApplyPatch(patchNumber:integer;artica_version:string):boolean;
       procedure SetPatch(PatchNumber:integer;Artica_version:string);
       procedure CleanKasperskyRollBack();
       function CheckUserCount():string;
       Procedure test_sa_update(tmpstr:string);
       function TestsErrorInInfile():boolean;
public
      constructor Create();
      procedure Free;
      PROCEDURE perform_update();
      function  GetLatestVersion():integer;
      function  GetCurrentVersion():integer;
      function  remote_file():string;
      procedure update_Kav4Samba();
      procedure update_Kav4Proxy();
      function  Install_new_version(lastestfile:string):boolean;
      PROCEDURE Initialize_installation();
      function  CheckAndInstall_install(FilePath:string):boolean;
      PROCEDURE CheckAndInstall();
      PROCEDURE CheckIndex();
      procedure ApplyPatchs();
      function  KasperskyRetranslation():boolean;
      PROCEDURE perform_clamav_updates();
      PROCEDURE update_spamassasin();
      Procedure update_dansguardian();
      procedure indexini();
      procedure NightlyBuild();
      PROCEDURE perform_update_nightly();
      procedure clamav_engine_update();
      procedure backup_ldap_database();
      Procedure update_spamassassin_blacklist();
      Procedure update_spamassasin_sought_rules_yerp_org();
      Procedure update_spamassasin_saupdates_openprotect_com();
      Procedure update_spamassasin_scanmail();
      procedure MalwarePatrol();
      Procedure update_squidguard();
      Procedure update_webfilterplus();
      function CheckSYSTEMID():string;
      procedure ipblocks();
END;

implementation

constructor tupdate.Create();
var

   xMEM_TOTAL_INSTALLEE:integer;
   xSYSTEMID:string;
   LinuxDistributionFullName:string;
begin
  GLOBAL_INI:=myconf.Create;
  D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
  FORCED:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--force');
  LOGS:=Tlogs.Create;
  SYS:=Tsystem.Create();
  spamassassin:=Tspamass.Create(SYS);
  MasterIndexFile:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/index.ini';
   if not TryStrToInt(SYS.GET_INFO('SQUIDEnable'),SQUIDEnable) then SQUIDEnable:=1;
   if not TryStrToInt(SYS.GET_INFO('SambaEnabled'),SambaEnabled) then SambaEnabled:=1;
   if not TryStrToInt(SYS.GET_INFO('EnableKav4Samba'),EnableKav4Samba) then EnableKav4Samba:=1;
   if not TryStrToInt(SYS.GET_INFO('kavicapserverEnabled'),kavicapserverEnabled) then kavicapserverEnabled:=0;
   if not TryStrToInt(SYS.GET_INFO('EnableMalwarePatrol'),EnableMalwarePatrol) then EnableMalwarePatrol:=0;
   if not TryStrToint(SYS.GET_INFO('RetranslatorEnabled'),RetranslatorEnabled) then RetranslatorEnabled:=0;

  xMEM_TOTAL_INSTALLEE:=SYS.MEM_TOTAL_INSTALLEE();
  LinuxDistributionFullName:=SYS.GET_INFO('LinuxDistributionFullName');
  if length(LinuxDistributionFullName)=0 then LinuxDistributionFullName:='Linux default';


  uriplus:=CheckSYSTEMID()+';'+ IntTostr(xMEM_TOTAL_INSTALLEE)+';'+IntTOstr(SYS.CPU_NUMBER())+';'+LinuxDistributionFullName+';'+trim(SYS.ARTICA_VERSION())+';'+trim(SYS.HOSTNAME_g())+';'+CheckUserCount();
  uriplus:=AnsiReplaceText(uriplus,' ','%20');

  if D then writeln(uriplus);
  //logs.Debuglogs('indexini() ->"'+uriplus+'"');
   if SambaEnabled=0 then  EnableKav4Samba:=0;
   if SQUIDEnable=0 then kavicapserverEnabled:=0;


end;
PROCEDURE tupdate.Free();
begin
  GLOBAL_INI.free;
end;
//#############################################################################
function tupdate.CheckSYSTEMID():string;
var
   blkid:string;
   RegExpr:TRegExpr;
   l:Tstringlist;
   i:integer;
   tmpstr:string;
begin
  result:=SYS.GET_INFO('SYSTEMID');
  if length(result)>3 then exit();
  blkid:=SYS.LOCATE_GENERIC_BIN('blkid');
  result:=SYS.MD5FromString(logs.DateTimeNowSQL());
  SYS.set_INFO('SYSTEMID',result);

  if length(blkid)=0 then begin
     result:=logs.MD5FromString(logs.DateTimeNowSQL());
     SYS.set_INFO('SYSTEMID',result);
     exit;
  end;

  tmpstr:=logs.FILE_TEMP();
  fpsystem(blkid+' >'+tmpstr+' 2>&1');
  if not fileExists(tmpstr) then exit;
  l:=tstringlist.Create;
  l.LoadFromFile(tmpstr);
  RegExpr:=TRegExpr.Create;
  logs.DeleteFile(tmpstr);
  RegExpr.Expression:='UUID="(.+?)"';
  for i:=0 to l.Count -1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
         result:=RegExpr.Match[1];
         SYS.set_INFO('SYSTEMID',result);
         break;
      end;
  end;

  if length(result)=0 then begin
     result:=logs.MD5FromString(logs.DateTimeNowSQL());
     SYS.set_INFO('SYSTEMID',result);
  end;
  l.free;
  RegExpr.free;


end;
//#############################################################################


function tupdate.CheckUserCount():string;
begin
if not FileExists('/etc/artica-postfix/UsersNumber') then fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.samba.php --users');
if SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/UsersNumber')>3600 then begin
   logs.DeleteFile('/etc/artica-postfix/UsersNumber');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.samba.php --users');
end;
result:=trim(logs.ReadFromFile('/etc/artica-postfix/UsersNumber'));
end;
//#############################################################################


PROCEDURE tupdate.CheckIndex();
var
 basePath:string;
 mustupdate:boolean;
 TimeFile:integer;
 tmpfile,tmpfile2:string;
 testini:TiniFile;
 testdata:string;
begin
   basePath:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/index.ini';
   tmpfile:=logs.FILE_TEMP();

   if ParamStr(1)='-refresh-index' then mustupdate:=true;
   logs.OutputCmd('/bin/rm /usr/share/artica-postfix/ressources/logs/setup.index.*.html');
   
   mustupdate:=false;
   if not FileExists(basePath) then mustupdate:=true;
   
   if not mustupdate then begin
      TimeFile:=SYS.FILE_TIME_BETWEEN_MIN(basePath);
      if TimeFile>1439 then begin
         logs.Debuglogs('Checkindex: TimeFile>1439, it is now necessary to update index file...');
         mustupdate:=true;
      end;
   end;
   
   if not mustupdate then exit;
   logs.Debuglogs('Checkindex: refresh index.ini file...');



   SYS.WGET_DOWNLOAD_FILE('http://www.artica.fr/auto.update.php?datas='+uriplus,tmpfile);
   tmpfile2:=logs.FILE_TEMP();
   SYS.WGET_DOWNLOAD_FILE('http://www.artica.fr/routers.inject.php',tmpfile2,true);
   logs.DeleteFile(tmpfile2);

   logs.Debuglogs('Checkindex: Testing index file...');
   try
   testini:=TiniFile.Create(tmpfile);
   testdata:=testini.ReadString('NEXT','artica','');


   except
    logs.Debuglogs('Checkindex: FATAL ERROR, ABorting');
    exit;
   end;

   if length(testdata)=0 then begin
        logs.Debuglogs('Checkindex: Testing index file failed...');
        testini.free;
        exit;
   end;

   fpsystem('/bin/cp -f '+tmpfile+' ' + basePath);
   logs.OutputCmd('/bin/chmod 755 '+basePath);
   testini.free;

end;

//#############################################################################
PROCEDURE tupdate.perform_clamav_updates();
var
   clamav:tclamav;
   EnableScanSecurity:Integer;
   EnableClamavUnofficial:integer;
   clamuser:string;
   UpdateLogFile:string;
   UpdateLogFilePath:string;
begin
clamav:=Tclamav.Create;
if not FileExists(clamav.CLAMD_BIN_PATH()) then exit;
if not TryStrToInt(SYS.GET_INFO('EnableScanSecurity'),EnableScanSecurity) then EnableScanSecurity:=0;
if not TryStrToInt(SYS.GET_INFO('EnableClamavUnofficial'),EnableClamavUnofficial) then EnableClamavUnofficial:=0;
if FileExists('/etc/artica-postfix/KASPER_MAIL_APP') then exit;
if FileExists('/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE') then exit;

if EnableScanSecurity=1 then begin
   logs.Syslogs('EnableScanSecurity is on, downloading patterns from http://www.sanesecurity.net');
   clamav.SCAMP_CONF();
   logs.Syslogs('Writing configuration done...');
   logs.Syslogs('Executing scamp...');

      ForceDirectories('/root/.gnupg');
      if SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/sanesecurity_time')>420 then begin
         fpsystem('/bin/sh -c /usr/share/artica-postfix/bin/scamp.sh &');
         logs.Syslogs('Executing scamp done...');
         logs.DeleteFile('/etc/artica-postfix/sanesecurity_time');
         logs.OutputCmd('/bin/touch /etc/artica-postfix/sanesecurity_time');
      end else begin
         logs.Debuglogs('Too short time to update from sanesecurity, i will come back later');
      end;

end else begin
   logs.Syslogs('EnableScanSecurity is off, abort downloading patterns from http://www.sanesecurity.net');
end;

if EnableClamavUnofficial=1 then begin
   logs.Syslogs('EnableClamavUnofficial is on, downloading unofficial patterns');
   clamav.CLAMAV_UNOFICIAL();
   logs.Syslogs('Writing configuration done...');
   logs.Syslogs('Executing...');
   if FileExists('/etc/artica-postfix/ClamavUnofficial_time') then begin
      if SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/ClamavUnofficial_time')>60 then begin
         fpsystem('/bin/sh -c /usr/share/artica-postfix/bin/clamav-unofficial-sigs.sh -c /etc/clamav-unofficial-sigs.conf &');
         logs.Syslogs('Executing clamav-unofficial-sigs.sh done...');
         logs.DeleteFile('/etc/artica-postfix/ClamavUnofficial_time');
         logs.OutputCmd('/bin/touch /etc/artica-postfix/ClamavUnofficial_time');
      end else begin
         logs.Debuglogs('Too short time to update from clamav-unofficial, i will come back later');
      end;
   end;
end else begin
   logs.Syslogs('EnableClamavUnofficial is off, abort downloading unofficial patterns');
end;


if FileExists(clamav.FRESHCLAM_PATH()) then begin
         clamuser:=clamav.CLAMD_GETINFO('User');
         UpdateLogFilePath:=clamav.FRESHCLAM_GETINFO('UpdateLogFile');
         UpdateLogFile:=ExtractFilePath(UpdateLogFilePath);
         logs.Debuglogs('perform_clamav_updates:: user='+clamuser + ' UpdateLogFile='+UpdateLogFile); ;
         if length(UpdateLogFile)=0 then begin
              logs.Debuglogs('perform_clamav_updates:: could not stat UpdateLogFile');
              exit;
         end;
         ForceDirectories(UpdateLogFile);
         if not FileExists(UpdateLogFilePath) then logs.OutputCmd('/bin/touch '+UpdateLogFilePath);
         forceDirectories('/var/lib/clamav');
         logs.OutputCmd('/bin/chown -R '+clamuser+':'+clamuser + ' /var/lib/clamav');
         logs.Syslogs('Executing freshclam...');
         logs.OutputCmd('/bin/chown -R '+clamuser+':'+clamuser + ' '+UpdateLogFile);

         logs.OutputCmd(clamav.FRESHCLAM_PATH()+' --config-file='+clamav.FRESHCLAM_CONF_PATH());
end;

end;
//#############################################################################
procedure tupdate.clamav_engine_update();
var
   clamav:tclamav;
   clamav_version:string;
   clamav_version_a:integer;

   clamav_remove_version:string;
   clamav_version_b:integer;
   clamav_remote_version:string;
   MasterIndexFileIni:TiniFile;
   Test:boolean;
begin
  clamav:=Tclamav.Create;
  Test:=false;
  if not FileExists(clamav.CLAMD_BIN_PATH()) then begin
     logs.Debuglogs('tupdate.clamav_engine_update():: Clamav is not installed...');
     exit;
  end;

  if(SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/clamav.update.check')<1440) then begin
     logs.Debuglogs('tupdate.clamav_engine_update():: not early to check updates, waiting 1440 minutes');
     exit;
  end;

  logs.DeleteFile('/etc/artica-postfix/clamav.update.check');

  clamav_version:=clamav.CLAMAV_VERSION();
  MasterIndexFileIni:=TiniFile.Create(MasterIndexFile);
  clamav_remove_version:=MasterIndexFileIni.ReadString('NEXT','clamav','0');
  clamav_remote_version:=clamav_remove_version;
  logs.Debuglogs('tupdate.clamav_engine_update():: Current version is.........: "'+clamav_version+'"');
  logs.Debuglogs('tupdate.clamav_engine_update():: Remote version available is: "'+clamav_remove_version+'"');

  clamav_version:=AnsiReplaceText(clamav_version,'.','');
  clamav_remove_version:=AnsiReplaceText(clamav_remove_version,'.','');
  if not TryStrToInt(clamav_version,clamav_version_a) then begin
       logs.Debuglogs('tupdate.clamav_engine_update():: FATAL ERROR INT clamav_version_a');
       exit;
  end;

  if not TryStrToInt(clamav_remove_version,clamav_version_b) then begin
       logs.Debuglogs('tupdate.clamav_engine_update():: FATAL ERROR INT clamav_version_b');
       exit;
  end;
   if SYS.COMMANDLINE_PARAMETERS('--check') then  Test:=true;


  if  length(IntTostr(clamav_version_a))=2 then  TryStrToInt(IntTostr(clamav_version_a)+'0',clamav_version_a);
  if(Test) then begin
           writeln('clamav_version_a=',clamav_version_a);
           writeln('clamav_version_b=',clamav_version_b);
           exit;
  end;
  if clamav_version_a<clamav_version_b then begin
       logs.Debuglogs('tupdate.clamav_engine_update()::Need to upgrade in '+clamav_remote_version);
       fpsystem('/usr/share/artica-postfix/bin/artica-make APP_CLAMAV');
       clamav_version:=clamav.CLAMAV_VERSION();
       clamav_version:=AnsiReplaceText(clamav_version,'.','');
       if not TryStrToInt(clamav_version,clamav_version_a) then begin
          logs.Debuglogs('tupdate.clamav_engine_update():: FATAL ERROR INT clamav_version_a');
          exit;
       end;
       if clamav_version_a=clamav_version_b then begin
          logs.NOTIFICATION('Clamav is updating to ' + clamav_remote_version,'Your system has been upgraded to the latest clamav engine version '+clamav_remote_version,'update');
          exit;
       end;
  end;

  fpsystem('/bin/touch /etc/artica-postfix/clamav.update.check');

end;
//#############################################################################


PROCEDURE tupdate.Initialize_installation();
var
 basePath,target_path:string;
 mustupdate:boolean;
 TimeFile:integer;
 product_name:string;
 zdate:string;
 order:TiniFile;
 index:TiniFile;
 logsfile:string;
begin
//startinstall
   product_name:=ParamStr(2);
   logsfile:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/'+product_name+'/install.log';
   
   zdate:=logs.DateTimeNowSQL();
   logs.Debuglogs('Initialize_installation:: initialize ' + product_name + ' for installation...');
   basePath:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/index.ini';
   index:=TiniFile.Create(basePath);
   forceDirectories(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install');
   logs.set_INFOS('CurrentInstallProduct',product_name);

   if FileExists(logsfile) then begin
      order:=TiniFile.Create(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install/'+product_name+'.install');
      logs.WriteInstallLogs('{order_already_launched}');
      order.UpdateFile;
      order.free;
      index.free;
      halt(0);
   end;
   


   order:=TiniFile.Create(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install/'+product_name+'.install');
   order.WriteInteger('INSTALL','status_number',1);
   logs.WriteInstallLogs('{order_launched}');
   fpsystem('/bin/chmod -R 755 '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install &');
   logs.Debuglogs('Initialize_installation:: end');
   halt(0);

end;
//#############################################################################

PROCEDURE tupdate.CheckAndInstall();
var
 basePath:string;
 FolderBase:string;
 l:TstringList;
 i:integer;



begin

  basePath:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/index.ini';
  FolderBase:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install';

  l:=TstringList.Create;
  l.AddStrings(SYS.DirFiles(FolderBase,'*.*'));
  logs.Debuglogs('CheckAndInstall:: ' + IntTostr(l.Count) + ' Files in queue');
  for i:=0 to l.Count-1 do begin
       logs.Debuglogs('CheckAndInstall:: checking:' + l.Strings[i]+' -' + IntToStr(i));
       CheckAndInstall_verify(FolderBase+ '/'+l.Strings[i]);
  end;
  
  logs.OutputCmd('/bin/chmod -R 755 '+FolderBase);
end;
//#############################################################################
procedure tupdate.CheckAndInstall_verify(FilePath:string);
var
 RegExpr:TRegExpr;
 basePath:string;
 BaseName:string;
 order:TiniFile;
 zdate:string;
begin

  basePath:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/index.ini';
  if not FileExists(FilePath) then begin
     logs.Debuglogs('CheckAndInstall_verify:: ' + FilePath +' does not exists');
     exit;
  end;
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='(.+?)\.install';
 
 if not RegExpr.Exec(FilePath) then begin
    logs.Debuglogs('CheckAndInstall_verify:: ' + FilePath + ' no match has installation order');
    RegExpr.Free;
    exit;
 end;
 
 if SYS.FILE_TIME_BETWEEN_SEC(FilePath)<5 then exit;


 logs.Debuglogs('CheckAndInstall_verify:: Open '+FilePath);
 order:=TiniFile.Create(FilePath);
 if order.ReadInteger('INSTALL','status_number',0)>1 then begin
    logs.Debuglogs('CheckAndInstall_verify:: order already executed');
    exit;
 end;
 
 zdate:=logs.DateTimeNowSQL();
 BaseName:=ExtractFileName(FilePath)+'.install';
 if CheckAndInstall_install(FilePath) then begin
    order.WriteInteger('INSTALL','status_number',2);
    order.free;
    halt(0);
 end;
 
 order.WriteInteger('INSTALL','status_number',3);
 order.free;
  logs.Debuglogs('CheckAndInstall_verify:: End ');
 halt(0);
  

end;
//#############################################################################


function tupdate.CheckAndInstall_install(FilePath:string):boolean;
var
 basePath,target_path:string;
 downloadfile,key:string;
 zdate:string;
 order:TiniFile;
 index:TiniFile;
 l:TstringList;
 RegExpr:TRegExpr;
 DirectoryP:string;
 apt_commands:string;
 exec_line_commands:string;
 filetemp:string;
begin
   result:=false;
   zdate:=logs.DateTimeNowSQL();

   basePath:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/index.ini';

   DirectoryP:= GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install';

   if Not FileExists(FilePath) then exit;
   

   order:=TiniFile.Create(FilePath);
   filetemp:=logs.FILE_TEMP();
   
   
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='(.+?)\.install';
   RegExpr.Exec(ExtractFileName(FilePath));
   logs.Debuglogs('CheckAndInstall:: '+ExtractFileName(FilePath)+' match "' + RegExpr.Match[1]+'"');
   key:= RegExpr.Match[1];
   logs.set_INFOS('CurrentInstallProduct',key);
   DirectoryP:=DirectoryP+'/'+ key+'.log';
   

   
   if not FileExists(SYS.LOCATE_APT_GET()) then begin
       logs.WriteInstallLogs('{only_debian_supported} (apt-get)');
       exit;
   end;
   

   if not FileExists(SYS.LOCATE_DPKG()) then begin
        logs.WriteInstallLogs('{only_debian_supported} (dpkg)');
        exit;
   end;
   


   
   logs.Debuglogs('CheckAndInstall_install:: events will be stored on ' + DirectoryP);
   
   if not RegExpr.Exec(FilePath) then  begin
      logs.WriteInstallLogs('{failed_parsing_file}');
      exit;
   end;

    logs.Debuglogs('CheckAndInstall_install:: reading key '+key+ ' in ' + basePath);
    index:=TiniFile.Create(basePath);
    downloadfile:=index.ReadString(key,'deb_filename','');
    apt_commands:=index.ReadString(key,'apt_backports','');
    exec_line_commands:=index.ReadString(key,'compile_cmd','');
    
    
    if length(exec_line_commands)>0 then begin
         logs.Debuglogs('this command seems to execute artica-install "'+exec_line_commands+'"');
         logs.WriteInstallLogs('execute compilation '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install ' +  exec_line_commands);
         fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/artica-install ' +  exec_line_commands);
         exit(true);
    end;
    
    
    if length(apt_commands)>0 then begin
         logs.Debuglogs('this command seems an apt-get command..."'+apt_commands+'"');
         result:=perform_apt_install(apt_commands,FilePath);
         order.UpdateFile;
         order.free;
         exit;
    end;
    
    
    
         
    
    
    if length(downloadfile)=0 then begin
        logs.WriteInstallLogs('{failed_parsing_file}index.ini');
        exit;
    end;
    
   logs.WriteInstallLogs('{check_installer}');
   fpsystem(SYS.LOCATE_APT_GET() +' update >/dev/null 2>&1');
   

   
   fpsystem(SYS.LOCATE_APT_GET() +' -f install --yes --force-yes >>'+filetemp +' 2>&1');
    logs.WriteInstallLogs(logs.ReadFromFile(filetemp));
    logs.DeleteFile(filetemp);

    
    logs.WriteInstallLogs('{downloading_packages}');
    order.UpdateFile;

    forcedirectories('/opt/artica/install/sources');

    SYS.WGET_DOWNLOAD_FILE('http://www.artica.fr/download/deb/'+downloadfile,'/opt/artica/install/sources/'+downloadfile);
    zdate:=logs.DateTimeNowSQL();
    if not FileExists('/opt/artica/install/sources/'+downloadfile) then begin
        logs.WriteInstallLogs('{failed_downloading_file} '+downloadfile);
        exit;
    end;

    logs.WriteInstallLogs('{perform_installation}');
    fpsystem(SYS.LOCATE_DPKG() + ' -i /opt/artica/install/sources/'+downloadfile +' >>'+filetemp +' 2>&1');
    logs.WriteInstallLogs(logs.ReadFromFile(filetemp));
    logs.DeleteFile(filetemp);
    logs.DeleteFile('/opt/artica/install/sources/'+downloadfile);
    result:=True;
end;
//#############################################################################



function tupdate.perform_apt_install(c:string;FilePath:string):boolean;
var
 basePath:string;
 key:string;
 zdate:string;
 order:TiniFile;
 RegExpr:TRegExpr;
 DirectoryP:string;
 apt_commands:string;
 filetemp:string;
begin
   zdate:=logs.DateTimeNowSQL();
   basePath:=GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/index.ini';
   DirectoryP:= GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install';
   
   
   logs.Debuglogs('perform_apt_install:: '+FilePath + ' (' + c + ')');
   
   order:=TiniFile.Create(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/install/'+ FilePath);

   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='(.+?)\.install';
   RegExpr.Exec(FilePath);
   key:= RegExpr.Match[1];
   DirectoryP:=DirectoryP+'/'+ key+'.log';
   filetemp:=logs.FILE_TEMP();

    logs.WriteInstallLogs('{check_installer}');
    fpsystem(SYS.LOCATE_APT_GET() +' update >/dev/null 2>&1');
    fpsystem(SYS.LOCATE_APT_GET() +' -f install --yes --force-yes >>'+filetemp +' 2>&1');
    
    logs.WriteInstallLogs(logs.ReadFromFile(filetemp));
    logs.DeleteFile(filetemp);
    

    if SYS.backports_Exists() then begin
       apt_commands:='-t etch-backports install ' + c + ' --yes --force-yes';
    end else begin
       apt_commands:='install ' + c + ' --yes --force-yes' ;
    end;
    logs.WriteInstallLogs('perform_apt_install:: '+apt_commands);
    fpsystem('apt-get ' + apt_commands + ' >>'+filetemp +' 2>&1');
    logs.WriteInstallLogs(logs.ReadFromFile(filetemp));
    logs.DeleteFile(filetemp);
    result:=true;
end;
//#############################################################################
PROCEDURE tupdate.perform_update_nightly();
var
CONF       :TiniFile;
begin

  CONF:=TiniFile.Create('/etc/artica-postfix/artica-update.conf');
  if Lowercase(CONF.ReadString('AUTOUPDATE','nightlybuild','no'))<>'yes' then begin
     logs.Debuglogs('perform_update_nightly():: nightly builds feature is disabled');
     CONF.Free;
     exit;
  end;

  CONF.Free;
  if FileExists('/etc/artica-postfix/croned.1/nightly') then begin
     if SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/croned.1/nightly')<120 then begin
        logs.Debuglogs('perform_update_nightly():: nightly builds feature (too short time, require 120mn)');
        exit;
     end;
  end;

  NightlyBuild();
  logs.DeleteFile('/etc/artica-postfix/croned.1/nightly');
  logs.OutputCmd('/bin/touch /etc/artica-postfix/croned.1/nightly');
end;
//#############################################################################

PROCEDURE tupdate.perform_update();
 var
 FileS      :TstringList;
 tmp        :string;
 mini       :TIniFile;
 CONF       :TiniFile;
 CurrentDate:TdateTime;
 CureDate   :TdateTime;
 StoredDate :TDateTime;
 Beetwen    :Integer;
 MaxMin     :Integer;
 D          :Boolean;
 lastest    :integer;
 current    :integer;
 lastestfile:string;
 tmpdate    :string;
 Maintenant : TdateTime;
 uri        :string;
 l          :tstringlist;
 PerformUpd :boolean;
 OperationStarted:boolean;
 logs       :Tlogs;
 kav4samba  :Tkav4samba;
 autoinstall:boolean;
 kas3:Tkas3;
 kavmilter:TkavMilter;
 Kav4proxy:Tkav4proxy;
 clamav:Tclamav;
 zcyrus:Tcyrus;
 ArticaAutoUpdateConfig:string;
 dansguardian:Tdansguardian;
 DansGuardianEnabled:integer;
 squidGuardEnabled:integer;
 squidguard:tsquidguard;

begin
  CheckIndex();
  Files:=TstringList.Create;
  logs:=Tlogs.Create;
  OperationStarted:=false;
  CurrentDate:=Now;
  PerformUpd:=false;
  kav4samba:=Tkav4samba.Create;
  dansguardian:=Tdansguardian.Create(SYS);
  zcyrus:=Tcyrus.Create(SYS);
  squidGuardEnabled:=0;
  if not TryStrToInt(SYS.GET_INFO('DansGuardianEnabled'),DansGuardianEnabled) then DansGuardianEnabled:=0;
  if not TryStrToInt(SYS.GET_INFO('squidGuardEnabled'),squidGuardEnabled) then squidGuardEnabled:=0;

  D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
  if not D then D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--screen');

  if not FileExists('/etc/artica-postfix/artica-update.conf') then begin
        logs.Syslogs('perform_update():: unable to stat /etc/artica-postfix/artica-update.conf');
        halt(0);
  end;

     forcedirectories('/etc/artica-postfix');



  indexini();
  backup_ldap_database();
  
  kas3:=Tkas3.Create(SYS);
  kavmilter:=tkavmilter.Create(SYS);
  Kav4proxy:=tKav4proxy.Create(SYS);
  clamav:=Tclamav.Create;

  if FileExists(clamav.CLAMD_BIN_PATH()) then begin
     logs.Debuglogs('perform_update() -> Updating clamav');
     perform_clamav_updates();
     clamav_engine_update();
  end;

//Kaspersky update
  logs.Debuglogs('perform_update() -> Updating kaspersky Anti-spam');
  CleanKasperskyRollBack();
  kas3.PERFORM_UPDATE();
  logs.Debuglogs('perform_update() -> Updating kaspersky Anti-virus milter');
  CleanKasperskyRollBack();
  kavmilter.PERFORM_UPDATE();
  logs.Debuglogs('perform_update() -> Updating kaspersky Anti-virus For Squid');
  CleanKasperskyRollBack();
  kav4Proxy.KAV4PROXY_PERFORM_UPDATE();
  logs.Debuglogs('perform_update() -> Updating kaspersky *.* done');
  MalwarePatrol();
  logs.Debuglogs('perform_update() -> Updating Malware Patrol done');




  kas3.free;
  kavmilter.free;
  kav4Proxy.free;

  //Dansguardian
  if FileExists(dansguardian.BIN_PATH()) then begin
     if DansGuardianEnabled=1 then update_dansguardian();
  end;
  //squidguard
  squidguard:=Tsquidguard.Create(SYS);
    if FileExists(squidguard.BIN_PATH()) then begin
     if squidGuardEnabled=1 then update_squidguard();
  end;
   update_webfilterplus();
  
  if ParamStr(1)='--package' then begin
      if FileExists('/opt/artica/sources/auto-update/'+ParamStr(2)) then begin
         fpsystem('tar -xf /opt/artica/sources/auto-update/'+ParamStr(2) +' -C /usr/share/');
         logs.Syslogs('perform_update() artica-postfix will be restarted');
         fpsystem('/etc/init.d/artica-postfix restart');
         fpsystem('/etc/init.d/artica-postfix restart ldap');
         GLOBAL_INI.DeleteFile('/opt/artica/sources/auto-update/'+ParamStr(2));

         halt(0);
      end;
  end;
  
  ArticaAutoUpdateConfig:=SYS.GET_INFO('ArticaAutoUpdateConfig');

  if length(ArticaAutoUpdateConfig)=0 then begin
     if D then writeln('perform_update() -> Set default config');
      logs.Debuglogs('perform_update() -> Set default config');
      Files.Add('[AUTOUPDATE]');
      Files.Add('enabled=yes');
      Files.Add('autoinstall=yes');
      Files.Add('CheckEveryMinutes=120');
      Files.Add('enable=yes');
      Files.Add('nightlybuild=no');
      Files.Add('uri=http://www.artica.fr/auto.update.php');
      ArticaAutoUpdateConfig:=files.Text
  end;
   if D then writeln('perform_update() -> Save to /etc/artica-postfix/artica-update.conf');
   logs.WriteToFile(ArticaAutoUpdateConfig,'/etc/artica-postfix/artica-update.conf');
   CONF:=TiniFile.Create('/etc/artica-postfix/artica-update.conf');

  if ParamStr(1)<>'--update' then begin
     if not GLOBAL_INI.COMMANDLINE_PARAMETERS('--force') then begin
      if CONF.ReadString('AUTOUPDATE','enabled','yes')<>'yes' then begin
        if D then writeln('perform_update() -> This feature is disabled aborting...');
        exit;
      end;
     end;
  end;
  
  
  if CONF.ReadString('AUTOUPDATE','autoinstall','yes')='yes' then begin
     PerformUpd:=true;
     autoinstall:=true;
  end;
  
  if GLOBAL_INI.COMMANDLINE_PARAMETERS('--force') then begin
     logs.Debuglogs('perform_update() --force token detected');
     PerformUpd:=true;
  end;


  if GLOBAL_INI.COMMANDLINE_PARAMETERS('--force') then begin
     if D then writeln('perform_update() -> force=yes');
     logs.Debuglogs('perform_update() -> force=yes delete /etc/artica-postfix/artica-update-schedule.conf');
     GLOBAL_INI.DeleteFile('/etc/artica-postfix/artica-update-schedule.conf');

  end;
     
  uri:=CONF.ReadString('AUTOUPDATE','uri','http://www.artica.fr/auto.update.php');
  mini:=TiniFile.Create('/etc/artica-postfix/artica-update-schedule.conf');
  try
     StoredDate:=mini.ReadDateTime('UPDATE','time',StoredDate);
  except
     logs.Debuglogs('perform_update() -> FATAL ERROR While Read date UPDATE/TIME FROM /etc/artica-postfix/artica-update-schedule.conf');
     StoredDate:=now();
  end;

  mini.WriteDateTime('UPDATE','Curdate',Now);
  CurrentDate:=mini.ReadDateTime('UPDATE','Curdate',CurrentDate);
  
  try
     MaxMin:=CONF.ReadInteger('AUTOUPDATE','CheckEveryMinutes',60);
  except
     logs.Debuglogs('perform_update() -> FATAL ERROR While Read date AUTOUPDATE/CheckEveryMinutes FROM /etc/artica-postfix/artica-update-schedule.conf');
     CONF.WriteString('AUTOUPDATE','CheckEveryMinutes','60');
     MaxMin:=60;
  end;


  try
     Beetwen:=MinutesBetween(CurrentDate,StoredDate);
  except
   logs.Debuglogs('perform_update() -> FATAL ERROR While calculate MinutesBetween() try second method');
   if not FileExists('/etc/artica-postfix/artica-update-schedule.time') then logs.OutputCmd('/bin/touch /etc/artica-postfix/artica-update-schedule.time');
   Beetwen:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/artica-update-schedule.time');
  end;

  if D then writeln('perform_update() -> Wakeup each minutes.....:',MaxMin);
  if D then writeln('perform_update() -> MinutesBetween..........:',Beetwen);
  
  logs.Debuglogs('perform_update() -> Try update each ' + IntTOStr(MaxMin) + ' minutes,currently '+IntTOStr(Beetwen)+' minutes since last update');

  if GLOBAL_INI.COMMANDLINE_PARAMETERS('--force') then begin
       logs.Debuglogs('perform_update() -> --force token detected , fake minutes');
       Beetwen:=100;
       maxmin:=1;
  end;

  if Beetwen>=MaxMin then begin

    
    OperationStarted:=true;
    logs.Debuglogs('perform_update() -> start operation...'+inttoStr(Beetwen)+'>=' + inttoStr(MaxMin));
    lastest:=GetLatestVersion();
    current:=GetCurrentVersion();
    lastestfile:=remote_file();
    Maintenant:=Now;
    TryStrToDate(tmpdate,Maintenant);
    logs.Debuglogs('perform_update() -> stamp file to ' + tmpdate);
    mini.WriteDateTime('UPDATE','time',Now);
    mini.UpdateFile;
    uri:=ExtractFilePath(uri) + 'download/'+lastestfile;
    if D then Writeln('perform_update() -> RUN.....................:','yes');
    if D then Writeln('perform_update() -> Latest version on update:',lastest);
    if D then Writeln('perform_update() -> Current version.........:',current);
    if D then Writeln('perform_update() -> Remote file.............:',lastestfile);
    if D then Writeln('perform_update() -> URI.....................:',uri);
    if D then Writeln('perform_update() -> Autoupdate..............:',PerformUpd);
    
    logs.Debuglogs('perform_update() -> Latest version on update:'+IntToStr(lastest));
    logs.Debuglogs('perform_update() -> Current version.........:'+IntToStr(current));
    logs.Debuglogs('perform_update() -> Remote file.............:'+lastestfile);
    logs.Debuglogs('perform_update() -> URI.....................:'+uri);
    logs.Debuglogs('perform_update() -> Updating GEOIP....'+uri);
    logs.OutputCmd(GLOBAL_INI.get_ARTICA_PHP_PATH()+ '/bin/artica-install -geoip-updates');
    logs.Debuglogs('perform_update() -> Updating GEOIP done....'+uri);
    
    if autoinstall then begin
        logs.Debuglogs('perform_update() -> autoinstall is enabled...');
   end  else begin
        logs.Debuglogs('perform_update() -> autoinstall is disabled...');
   end;
    
    if FileExists(SYS.LOCATE_GENERIC_BIN('sa-update')) then begin
       logs.Syslogs('perform_update() -> Spamassassin...');
       update_spamassasin();
       update_spamassasin_sought_rules_yerp_org();
       update_spamassasin_saupdates_openprotect_com();
       update_spamassasin_scanmail();
       update_spamassassin_blacklist();
       logs.Syslogs('perform_update() -> Spamassassin done.');
    end else begin
       logs.Debuglogs('perform_update() -> Spamassassin failed, sa-update no such file');
    end;
    update_Kav4Samba();
    update_Kav4Proxy();
    if fileExists(zcyrus.CYRUS_DAEMON_BIN_PATH()) then fpsystem('/usr/share/artica-postfix/bin/artica-make APP_IMAPSYNC >/dev/null 2>&1 &');


    
    if current=0 then begin
         logs.Syslogs('perform_update() -> WARNING, Current version is corrupted "' + IntToStr(current)+'"' );
         exit;
    end;


    perform_update_nightly();

    logs.Debuglogs('perform_update() -> testing if "' + IntToStr(lastest) + '" > "' + IntToStr(current)+'"' );
    if lastest>current then begin
       logs.Debuglogs('perform_update() new version detected...');
       
       
       if FileExists('/opt/artica/sources/auto-update/'+lastestfile) then begin
           if autoinstall then begin
              if SYS.FILE_TIME_BETWEEN_MIN('/opt/artica/sources/auto-update/'+lastestfile)>60 then begin
                 logs.Debuglogs('perform_update() -> deleting '+lastestfile+' too old');
                 logs.DeleteFile('/opt/artica/sources/auto-update/'+lastestfile);
              end;
           end;
       end;
       
       
       if not FileExists('/opt/artica/sources/auto-update/'+lastestfile) then begin
         forcedirectories('/opt/artica/sources/auto-update');
         fpsystem('/bin/rm -rf /opt/artica/sources/auto-update/*');

         logs.Debuglogs('perform_update() -> downloading to /opt/artica/sources/auto-update/'+lastestfile);


         GLOBAL_INI.WGET_DOWNLOAD_FILE(uri,'/opt/artica/sources/auto-update/'+lastestfile);

         if autoinstall then begin
                  Install_new_version(lastestfile);
         end else begin

          logs.Debuglogs('perform_update() -> success store update package '+lastestfile + ' waiting installation order');
         end;
       end else begin
         logs.Debuglogs('perform_update() ->/opt/artica/sources/auto-update/'+lastestfile+ ' Already exists...');
         if autoinstall then begin
            Install_new_version(lastestfile);
         end else begin
             logs.Debuglogs('perform_update() -> success store update package '+lastestfile + ' waiting installation order');
         end;
    end;
      
    
    end else begin
    logs.Debuglogs('perform_update() -> Result..................:nothing to do latest: '+IntToStr(lastest)+'<'+IntToStr(current));
    if D then Writeln('perform_update() -> Result..................:','nothing to do latest: ',lastest,'<',current,' ',current-lastest);
    if GLOBAL_INI.COMMANDLINE_PARAMETERS('--force') then writeln('nothing to do ');
    end;
    



  
  end;
    if OperationStarted then begin
       logs.Debuglogs('perform_update() ->stamp date');
       logs.DeleteFile('/etc/artica-postfix/artica-update-schedule.time');
       logs.WriteToFile('#','/etc/artica-postfix/artica-update-schedule.time');
       mini.WriteDateTime('UPDATE','time',Now);
       mini.UpdateFile;
    
    end;
    if FileExists('/etc/artica-postfix/update.lck') then GLOBAL_INI.DeleteFile('/etc/artica-postfix/update.lck');
    mini.Free;
    CONF.Free;
    logs.Debuglogs('perform_update() -> stopping function...');
    halt(0);
  
end;
//#############################################################################


procedure tupdate.MalwarePatrol();
var
   squid:tsquid;
   Filetime:integer;

begin

     squid:=Tsquid.Create;
    if not FileExists(squid.SQUID_BIN_PATH()) then begin
          logs.Debuglogs('MalwarePatrol() ->Squid is not installed');
          exit;
    end;

    if EnableMalwarePatrol=0 then begin
          logs.Debuglogs('MalwarePatrol() ->EnableMalwarePatrol is disabled');
          exit;
    end;


    if SQUIDEnable=0 then begin
      logs.Debuglogs('MalwarePatrol() ->SQUIDEnable is disabled');
      exit;
    end;
    if not FileExists('/etc/squid3/malwares.acl') then begin
        Filetime:=54654646;
    end else begin
        Filetime:=SYS.FILE_TIME_BETWEEN_MIN('/etc/squid3/malwares.acl');
    end;


    logs.Debuglogs('MalwarePatrol() ->Minutes: '+intToStr(Filetime)+'Mn');
    if FileTime<60 then exit;

   SYS.WGET_DOWNLOAD_FILE('http://malware.hiperlinks.com.br/cgi/submit-agressive?action=list_squid&type=agressive','/etc/squid3/malwares.acl');
end;

//#############################################################################
procedure tupdate.NightlyBuild();
var
   MyCurrentVersion:integer;
   LastestBuild:integer;
   Lastest:string;
   tmpstr:string;
   CONF:TiniFile;
   uri:string;
   ArticaFileTemp:string;
begin

MyCurrentVersion:=GetCurrentVersion();
logs.DeleteFile(MasterIndexFile);
CheckIndex();
CONF:=TiniFile.Create(MasterIndexFile);
Lastest:=CONF.ReadString('NEXT','artica-nightly','');
    logs.Debuglogs('NightlyBuild():: latest nightly build available version:'+Lastest);
if length(Lastest)=0 then logs.Debuglogs('NightlyBuild():: No nightly build available.(artica-nightly key is null)');
LastestBuild:=StripNumber(Lastest);
if LastestBuild=MyCurrentVersion then begin
    logs.Debuglogs('NightlyBuild():: up-to-date... aborting ('+IntTostr(LastestBuild)+'='+IntTostr(MyCurrentVersion)+')');
    exit;
end;

if LastestBuild<MyCurrentVersion then begin
    logs.Debuglogs('NightlyBuild():: up-to-date... aborting ('+IntTostr(LastestBuild)+'<'+IntTostr(MyCurrentVersion)+')');
    exit;
end;
  logs.Debuglogs('NightlyBuild():: Downloading new version ' + Lastest);
  uri:='http://www.artica.fr/nightbuilds/artica-'+Lastest+'.tgz';
  forceDirectories('/tmp/'+Lastest);

  ArticaFileTemp:='/tmp/'+Lastest+'/'+Lastest+'.tgz';
  logs.Debuglogs('NightlyBuild():: downloading....');
  GLOBAL_INI.WGET_DOWNLOAD_FILE(uri,ArticaFileTemp);
  logs.Debuglogs('NightlyBuild():: Stopping artica...');
  fpsystem('/etc/init.d/artica-postfix stop');
  logs.Debuglogs('NightlyBuild():: Stopping artica twice ...');
  fpsystem('/etc/init.d/artica-postfix stop');
  logs.Debuglogs('NightlyBuild():: decompressing '+ArticaFileTemp );


  if not FileExists(ArticaFileTemp) then begin
     logs.Debuglogs('NightlyBuild():: Corrupted package '+ArticaFileTemp);
     exit;
  end;

  writeln('NightlyBuild():: Extracting package, please wait...');
  fpsystem('killall artica-install >/dev/null 2>&1');
  logs.OutputCmd('/bin/tar -xf '+ArticaFileTemp +' -C /usr/share/');


  logs.Debuglogs('Starting artica....');
  fpsystem('/etc/init.d/artica-postfix start &');
  SYS.THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart ldap');

  MyCurrentVersion:=GetCurrentVersion();

  logs.OutputCmd('/bin/rm -rf /tmp/'+Lastest);

 if LastestBuild=MyCurrentVersion then begin
    logs.Debuglogs('NightlyBuild():: NightlyBuild():: success');
    logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update new artica nightly build version ' + GLOBAL_INI.ARTICA_VERSION(),'your server is now up-to-date with the new version ' + GLOBAL_INI.ARTICA_VERSION(),'update');
    fpsystem('/usr/share/artica-postfix/bin/artica-make --empty-cache &');
    exit;
end;


end;
//#############################################################################
function tupdate.Install_new_version(lastestfile:string):boolean;
var
   logs:Tlogs;
   D:boolean;
   lastest,current:integer;
begin
  result:=true;
  logs:=Tlogs.Create;
  D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('--verbose');
  lastest:=GetLatestVersion();

  if D then Writeln('Install_new_version() -> Uncompress...');
  logs.Debuglogs('Install_new_version() -> uncompress file '+lastestfile);
  current:=GetCurrentVersion();

  logs.OutputCmd('/etc/init.d/artica-postfix stop');
  if length(SYS.AllPidsByPatternInPath('tar -xf /opt/artica/sources/auto-update/'+lastestfile))>0 then begin
      logs.Debuglogs('Install_new_version() -> already decompressing task exists...');
      halt(0);
  end;
    fpsystem('killall artica-install >/dev/null 2>&1');
  logs.OutputCmd('tar -xf /opt/artica/sources/auto-update/'+lastestfile +' -C /usr/share/');
  logs.DeleteFile('/opt/artica/sources/auto-update/'+lastestfile);
  
  
  current:=GetCurrentVersion();
          if lastest=current then begin

              logs.syslogs('Install_new_version() -> /etc/init.d/artica-postfix restart');

              logs.Debuglogs('Install_new_version() -> success updated to version '+GLOBAL_INI.ARTICA_VERSION());

              if D then writeln('Success ! -> remove file');
              GLOBAL_INI.DeleteFile('/opt/artica/sources/auto-update/'+lastestfile);

              logs.Syslogs('success update artica...');
              fpsystem('/bin/rm -f /usr/share/artica-postfix/PATCH');
              fpsystem('/bin/rm -f /usr/share/artica-postfix/PATCHS_HISTORY');
              logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update new artica version ' + GLOBAL_INI.ARTICA_VERSION(),'your server is now up-to-date with the new version ' + GLOBAL_INI.ARTICA_VERSION(),'update');
           end else begin

              logs.Debuglogs('Install_new_version() -> Failed extract '+lastestfile);
              GLOBAL_INI.DeleteFile('/opt/artica/sources/auto-update/'+lastestfile);
           end;
fpsystem('/usr/share/artica-postfix/bin/artica-make --empty-cache &');
fpsystem('/usr/share/artica-postfix/bin/artica-make APP_MONIT');
logs.OutputCmd('/etc/init.d/artica-postfix restart');
logs.OutputCmd('/etc/init.d/artica-postfix restart ldap');
end;

//#############################################################################
procedure tupdate.update_Kav4Samba();
begin
     if EnableKav4Samba=0 then begin
          logs.Debuglogs('update_Kav4Samba():: EnableKav4Samba is disabled, skip');
          exit;
     end;




     CleanKasperskyRollBack();
     if  FileExists('/opt/kaspersky/kav4samba/bin/kav4samba-keepup2date') then begin
         if not SYS.PROCESS_EXISTS_BY_NAME('kav4samba-keepup2date') then begin
          Logs.Debuglogs('perform_update() -> Updating kav4samba...');
          fpsystem('/opt/kaspersky/kav4samba/bin/kav4samba-keepup2date -q &');
         end;
    end;
end;
//#############################################################################
procedure tupdate.update_Kav4Proxy();
var
   tmp,pid:string;
begin
     IF kavicapserverEnabled=0 then begin
        logs.Debuglogs('update_Kav4Proxy:: Squid family is disabled');
        exit;
     end;

     if  not FileExists('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date') then exit;

     if RetranslatorEnabled=1 then begin
          Logs.Debuglogs('perform_update() -> retranslator is enabled,');
           tmp:=logs.FileTimeName();
          fpsystem('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date -g /var/db/kav/databases >/var/log/artica-postfix/kaspersky/kav4proxy/'+tmp+' 2>&1');
          exit;
     end;

     CleanKasperskyRollBack();
     if  FileExists('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date') then begin
         if not SYS.PROCESS_EXISTS_BY_NAME('kav4proxy-keepup2date') then begin
          Logs.Debuglogs('perform_update() -> Updating Kav4proxy...');
          ForceDirectories('/var/log/artica-postfix/kaspersky/kav4proxy');
          tmp:=logs.FileTimeName();
          fpsystem('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date >/var/log/artica-postfix/kaspersky/kav4proxy/'+tmp+' 2>&1 &');
          sleep(500);
          pid:=SYS.PIDOF('/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date');
          if SYS.PROCESS_EXIST(pid) then SYS.cpulimit(pid);
         end;
    end;
end;
//#############################################################################

procedure tupdate.CleanKasperskyRollBack();
var
   l:TstringList;
   i:integer;
begin
l:=TstringList.Create;
l.add('/usr/local/ap-mailfilter3/cfdata/bases.backup/rollback.conf');
l.add('/var/db/kav/5.5/kav4mailservers/bases.backup/rollback.conf');
l.add('/var/db/kav/5.6/kavmilter/bases/backup/rollback.conf');
l.add('/var/opt/kaspersky/kav4proxy/bases.backup/data/rollback.conf');
l.add('/var/opt/kaspersky/kav4samba/bases.backup/data/rollback.conf');

for i:=0 to l.Count-1 do begin
   if FileExists(l.Strings[i]) then logs.DeleteFile(l.Strings[i]);

end;
end;
//#############################################################################
function tupdate.GetLatestVersion():integer;
var
  CONF       :TiniFile;
  autoupdate :TIniFile;
  tmpstr     :string;
  uri        :string;
begin
   result:=0;
   if not FileExists('/etc/artica-postfix/artica-update.conf') then perform_update();
   CONF:=TiniFile.Create('/etc/artica-postfix/artica-update.conf');
   uri:=CONF.ReadString('AUTOUPDATE','uri','http://www.artica.fr/auto.update.php');
   GLOBAL_INI.WGET_DOWNLOAD_FILE(uri,'/etc/artica-postfix/autoupdate.conf');
   if not FileExists('/etc/artica-postfix/autoupdate.conf') then exit;
   autoupdate:=TiniFile.Create('/etc/artica-postfix/autoupdate.conf');
   tmpstr:=autoupdate.ReadString('NEXT','artica','0');
   tmpstr:=AnsiReplaceText(tmpstr,'.','');
   result:=StrToInt(tmpstr);
end;
//#############################################################################
function tupdate.remote_file():string;
var

  autoupdate :TIniFile;
  tmpstr     :string;
  uri        :string;
begin
   result:='';
   if not FileExists('/etc/artica-postfix/artica-update.conf') then perform_update();
   if not FileExists('/etc/artica-postfix/autoupdate.conf') then exit;
   autoupdate:=TiniFile.Create('/etc/artica-postfix/autoupdate.conf');
   tmpstr:='artica-'+autoupdate.ReadString('NEXT','artica','0')+ '.tgz';
   result:=tmpstr;
end;
//#############################################################################
function tupdate.GetCurrentVersion():integer;
var
  tmpstr     :string;
  uri        :string;
  version    :integer;
begin
   result:=0;
   tmpstr:=trim(GLOBAL_INI.ARTICA_VERSION());
   if length(tmpstr)=0 then begin
        tmpstr:=logs.ReadFromFile('/usr/share/artica-postfix/ressources/VERSION');
        logs.WriteToFile(tmpstr,'/usr/share/artica-postfix/VERSION');
   end;

   if length(tmpstr)=0 then begin
        exit;
   end;

   result:=StripNumber(tmpstr);
end;
//#############################################################################
function tupdate.StripNumber(MyNumber:string):integer;
var
  tmpstr     :string;
  uri        :string;
begin
   result:=0;
   tmpstr:=MyNumber;
   tmpstr:=AnsiReplaceText(tmpstr,'.','');
   If Not TryStrToInt(tmpstr,result) then result:=0;
end;
//#############################################################################
procedure tupdate.indexini();
var
  CONF       :TiniFile;
  uri        :string;
  localFile  :string;
  minutestimed:integer;

begin


  CONF:=TiniFile.Create('/etc/artica-postfix/artica-update.conf');
  uri:=CONF.ReadString('AUTOUPDATE','uri','http://www.artica.fr/auto.update.php');
  localFile:='/usr/share/artica-postfix/ressources/index.ini';
  IF D then writeln('localFile..........:',localFile);
  IF D then writeln('uri................:',uri);

  logs.Debuglogs('using "'+ uri+'"');



   if FileExists(localFile) then begin
         minutestimed:=SYS.FILE_TIME_BETWEEN_MIN(localFile);
         logs.Debuglogs('indexini() ->"' + localFile + '"=' + intToStr(minutestimed)+' minute(s) live');
         if FORCED then begin
          logs.Debuglogs('indexini() ->"--forced detected, skip minutes checking ');
          minutestimed:=5897;
         end;
      if minutestimed>60 then begin
         logs.Debuglogs('indexini() -> deleting /usr/share/artica-postfix/ressources/index.ini too old and download it');
         logs.DeleteFile('/usr/share/artica-postfix/ressources/index.ini');
         GLOBAL_INI.WGET_DOWNLOAD_FILE(uri+'?datas='+uriplus,localFile);
      end;
   end else begin
        GLOBAL_INI.WGET_DOWNLOAD_FILE(uri+'?datas='+uriplus,localFile);
   end;
   logs.OutputCmd('/bin/rm -rf /usr/share/artica-postfix/ressources/install/*.ini');
   logs.OutputCmd('chmod 755 /usr/share/artica-postfix/ressources/index.ini');
   if FileExists(localFile) then begin
      if D then writeln(localFile, ' exists...');
   end else begin
       if D then writeln(localFile, ' no such file...');
       exit;
   end;

   if TestsErrorInInfile() then begin
       logs.DeleteFile('/usr/share/artica-postfix/ressources/index.ini');
       GLOBAL_INI.WGET_DOWNLOAD_FILE(uri,localFile);
       logs.OutputCmd('chmod 755 /usr/share/artica-postfix/ressources/index.ini');
   end;

   if TestsErrorInInfile() then begin
       logs.DeleteFile('/usr/share/artica-postfix/ressources/index.ini');
       GLOBAL_INI.WGET_DOWNLOAD_FILE(uri,localFile,true);
       logs.OutputCmd('chmod 755 /usr/share/artica-postfix/ressources/index.ini');
   end;

CONF.free;

end;
//#############################################################################
function tupdate.TestsErrorInInfile():boolean;
var
  l          :Tstringlist;
  i          :integer;
  RegExpr    :TRegExpr;
begin
    result:=false;
    l:=Tstringlist.Create;
    l.LoadFromFile('/usr/share/artica-postfix/ressources/index.ini');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Access control configuration prevents your request from being allowed at this time';
    for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
            if D then writeln(l.Strings[i]);
            logs.NOTIFICATION('Unable to download artica repository index file','Artica has encountered error: Access control configuration prevents your request from being allowed at this time. Please check you proxy configuration !','system');
            result:=true;
            break;
        end;

    end;

    l.free;
    RegExpr.free;
end;
//#############################################################################
procedure tupdate.backup_ldap_database();
var
   sdate:string;
   filepath:string;
   i:integer;
   ccyrus:Tcyrus;
begin

if not FileExists(SYS.LOCATE_SLAPCAT()) then begin
   logs.Debuglogs('backup_ldap_database():: Backuping LDAP Database unable to stat slapcat');
   exit;
end;


ccyrus:=Tcyrus.Create(SYS);
ccyrus.CLUSTER_SEND_LDAP_DATABASE();
ccyrus.Free;

sdate:=FormatDateTime('yyyy-mm-dd-hh', Now);
forceDirectories('/opt/artica/ldap-backup');
if FileExists('/opt/artica/ldap-backup/'+sdate+'.tar.gz') then begin
   logs.Debuglogs('backup_ldap_database():: Backuping LDAP Database "/opt/artica/ldap-backup/'+sdate+'.tar.gz" already exists');
   exit;
end;

logs.OutputCmd(SYS.LOCATE_SLAPCAT() + ' -l /opt/artica/ldap-backup/'+sdate+'.ldif');

logs.Debuglogs('Backuped file store '+ IntTostr(sys.FileSize_ko('/opt/artica/ldap-backup/'+sdate+'.ldif'))+' ko');

if not FileExists('/opt/artica/ldap-backup/'+sdate+'.ldif') then begin
   logs.Debuglogs('backup_ldap_database():: Unable to stat /opt/artica/ldap-backup/'+sdate+'.ldif after exporting datas');
   exit;
end;

SetCurrentDir('/opt/artica/ldap-backup');
fpsystem('/bin/tar -cf '+sdate+'.tar.gz '+ sdate+'.ldif');

if not FileExists('/opt/artica/ldap-backup/'+sdate+'.tar.gz') then begin
   logs.Debuglogs('backup_ldap_database():: Unable to compress /opt/artica/ldap-backup/'+sdate+'.ldif after exporting datas');
   exit;
end;

logs.DeleteFile('/opt/artica/ldap-backup/'+sdate+'.ldif');
logs.Debuglogs('backup_ldap_database():: Success compress /opt/artica/ldap-backup/'+sdate+'.tar.gz after exporting datas');
logs.Debuglogs('backup_ldap_database():: backup is finish...');

sys.DirFiles('/opt/artica/ldap-backup','*.gz');
for i:=0 to sys.DirListFiles.Count-1 do begin
       filepath:='/opt/artica/ldap-backup/'+sys.DirListFiles.Strings[i];
       if SYS.FILE_TIME_BETWEEN_MIN(filepath)>10080 then begin
          logs.Debuglogs('backup_ldap_database():: Deleting old LDAP backup database "'+filepath+'"');
          logs.DeleteFile(filepath);
       end;
end;



end;
//#############################################################################

procedure tupdate.ApplyPatchs();
var
  tmpstr     :string;
  uri        :string;
  CONF       :TiniFile;
  localFile  :string;
  PatchList  :Tstringlist;
  i          :integer;
  RegExpr    :TRegExpr;
  MyCurrentVersion:integer;
  PatchArticaVersion:integer;
begin
  CONF:=TiniFile.Create('/etc/artica-postfix/artica-update.conf');
  uri:=CONF.ReadString('AUTOUPDATE','uri','http://www.artica.fr/auto.update.php');
  localFile:='/usr/share/artica-postfix/ressources/index.ini';
  indexini();
 if CONF.ReadString('AUTOUPDATE','autoinstall','yes')<>'yes' then begin
    logs.Debuglogs('autoinstall key disable auto-patch in /etc/artica-postfix/artica-update.conf');
    exit;
 end;
 CONF.Free;

if not FileExists(localFile) then begin
   logs.Syslogs('Unable to download/stat '+localFile);
   exit;
end;
MyCurrentVersion:=GetCurrentVersion();
PatchList:=TstringList.Create ;
CONF:=TiniFile.Create(localFile);
CONF.ReadSectionValues('PATCH_ARTICA',PatchList);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='([0-9]+)=([0-9\.]+)';
for i:=0 to PatchList.Count-1 do begin
      if RegExpr.Exec(PatchList.Strings[i]) then begin
          logs.Debuglogs('ApplyPatchs() -> Found patch number ' + RegExpr.Match[1] + ' For Version ' + RegExpr.Match[2] + '(' + IntToStr(StripNumber(RegExpr.Match[2]))+')');
          PatchArticaVersion:=StripNumber(RegExpr.Match[2]);
          if PatchArticaVersion<>MyCurrentVersion then continue;
          if IfPatchExists(StrToInt(RegExpr.Match[1]),RegExpr.Match[2]) then continue;
          logs.Debuglogs('ApplyPatchs() -> Patch ' + RegExpr.Match[1] + ' is not installed');
          if not ApplyPatch(StrToInt(RegExpr.Match[1]),RegExpr.Match[2]) then begin
             logs.Syslogs('Failed apply patch '+ RegExpr.Match[1] + ' For Version ' + RegExpr.Match[2]);
             exit;
          end;
      end;
end;


end;
//#############################################################################
function tupdate.IfPatchExists(PatchNumber:integer;Artica_version:string):boolean;
var  CONF:TiniFile;
begin
   result:=false;
   if not FileExists('/usr/share/artica-postfix/PATCHS_HISTORY') then exit;
   CONF:=TiniFile.Create('/usr/share/artica-postfix/PATCHS_HISTORY');
   result:=CONF.ReadBool(Artica_version,'Patch_'+IntToStr(PatchNumber),false);
   CONF.FREE;

end;
//#############################################################################
procedure tupdate.SetPatch(PatchNumber:integer;Artica_version:string);
var  CONF:TiniFile;
     l:TstringList;
begin
   CONF:=TiniFile.Create('/usr/share/artica-postfix/PATCHS_HISTORY');
   conf.WriteBool(Artica_version,'Patch_'+IntToStr(PatchNumber),true);
   conf.free;
   l:=TstringList.Create;
   l.Add('P' +IntToStr(PatchNumber) );
   l.SaveToFile('/usr/share/artica-postfix/PATCH');
   l.free;

end;
//#############################################################################
function tupdate.ApplyPatch(patchNumber:integer;artica_version:string):boolean;
var
   DirectoryName:string;
   CONF:TiniFile;
   uri:string;
   WebFolder:string;
   dev:boolean;
   targetfolder:string;
begin
result:=false;
dev:=false;
DirectoryName:=artica_version+'_P' + IntToStr(patchNumber);
CONF:=TiniFile.Create('/etc/artica-postfix/artica-update.conf');
uri:=CONF.ReadString('AUTOUPDATE','uri','http://www.artica.fr/auto.update.php');
targetfolder:='/usr/share/artica-postfix/';
WebFolder:=ExtractFilePath(uri)+'download/patch/'+DirectoryName+'/'+DirectoryName+'.tar';
CONF.free;
if DirectoRyExists('/home/dtouzeau/developpement/artica-postfix') then begin
   logs.Debuglogs('This is a developpement plate-form');
   dev:=true;
   targetfolder:='/usr/share/artica-postfix2/';
end;


logs.Debuglogs('ApplyPatchs() -> '+WebFolder);
forceDirectories('/tmp/patchs/'+DirectoryName);
forceDirectories(targetfolder);
GLOBAL_INI.WGET_DOWNLOAD_FILE(WebFolder,'/tmp/patchs/'+DirectoryName+'/'+DirectoryName+'.tar');
logs.Debuglogs('Extracting '+DirectoryName+'.tar');
fpsystem('tar -xf /tmp/patchs/'+DirectoryName+'/'+DirectoryName+'.tar -C /tmp/patchs/'+DirectoryName);
if DirectoryExists('/tmp/patchs/'+DirectoryName+'/artica-postfix') then begin
     logs.Debuglogs('Installing new patch '+IntToStr(patchNumber));
     fpsystem('/bin/cp -rf /tmp/patchs/'+DirectoryName+'/artica-postfix/* '+targetfolder);
     logs.Debuglogs('Installing new patch '+IntToStr(patchNumber)+' done..');
     SetPatch(patchNumber,artica_version);
     logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update new artica patch version ' + IntToStr(patchNumber),'your server is now up-to-date with the new version ' + GLOBAL_INI.ARTICA_VERSION()+' Patch ' + IntToStr(patchNumber),'update');
     exit(true);
end;


end;
//#############################################################################
function tupdate.KasperskyRetranslation():boolean;
var
   retranslator:tkretranslator;
   patterndate:string;
   patterndate2:string;
   ldate:string;
   l:Tstringlist;
begin

   if RetranslatorEnabled=0 then begin
      logs.Syslogs('tupdate.KasperskyRetranslation(): disabled by RetranslatorEnabled');
      exit;
   end;

   retranslator:=Tkretranslator.Create(SYS);
   if SYS.PROCESS_EXIST(retranslator.PID_NUM()) then begin
      logs.Syslogs('KasperskyRetranslation:: process already exists...');
      exit;
   end;
   ldate:=logs.DateTimeNowSQL();
   ldate:=AnsiReplaceText(ldate,' ','_');
   ldate:=AnsiReplaceText(ldate,':','-');

   patterndate:=retranslator.PATTERN_DATE();
   logs.Syslogs('Executing Kaspersky retranslation... current version : '+patterndate);
   l:=Tstringlist.Create;
   l.Add('#!/bin/sh');
   l.add('/usr/share/artica-postfix/bin/retranslator.bin -c /etc/kretranslator/retranslator.conf -l /var/log/kretranslator/retranslator-'+ldate+'.log >/var/log/kretranslator/retranslator-'+ldate+'.debug 2>&1');
   l.add('');

   logs.WriteToFile(l.Text,'/tmp/exec.retranslator.sh');
   fpsystem('/bin/chmod 777  /tmp/exec.retranslator.sh');
   fpsystem('/tmp/exec.retranslator.sh');

   l.free;
//   fpsystem(GLOBAL_INI.get_ARTICA_PHP_PATH()+'/bin/retranslator.bin -c /etc/kretranslator/retranslator.conf -l /var/log/kretranslator/retranslator-'+ldate+'.log >/var/log/kretranslator/retranslator-'+ldate+'.debug 2>&1');

       
end;

//#############################################################################
PROCEDURE tupdate.update_spamassasin();
var
   pid:string;
   sa_compile:string;
   timemin:integer;
   tmpstr:string;
begin

if not FileExists(spamassassin.SA_UPDATE_PATH()) then begin
   logs.Debuglogs('update_spamassasin:: unable to stat sa-update');
   exit;
end;

tmpstr:=logs.FILE_TEMP();

if spamassassin.SpamdEnabled=0 then begin
   logs.Debuglogs('update_spamassasin:: Spamassin daemon is not enabled');
   exit;
end;

if not FileExists('/usr/share/artica-postfix/ressources/logs/sa.update.dbg') then begin
   fpsystem(spamassassin.SA_UPDATE_PATH()+ ' --checkonly -D >/usr/share/artica-postfix/ressources/logs/sa.update.dbg 2>&1');
   fpsystem('/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/sa.update.dbg');
end;


if not SYS.COMMANDLINE_PARAMETERS('--force') then begin
   timemin:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/cron.1/sa_compile');
   if timemin < 479 then begin
     logs.Debuglogs('update_spamassasin:: need more than 479 minutes current is '+ IntTostr(timemin)+' Mn');
     exit;
   end;
end;

if FileExists('/usr/share/artica-postfix/bin/install/amavis/SA-GPG.KEY') then begin
  logs.OutputCmd(spamassassin.SA_UPDATE_PATH()+' --import /usr/share/artica-postfix/bin/install/amavis/SA-GPG.KEY');
end;

if FileExists('/usr/share/artica-postfix/bin/install/amavis/sa-channels.conf') then begin
   logs.OutputCmd(spamassassin.SA_UPDATE_PATH()+' --channelfile /usr/share/artica-postfix/bin/install/amavis/sa-channels.conf --gpgkey 856AA88A -D -v --channel updates.spamassassin.org >'+tmpstr +' 2>&1');
end else begin
     logs.OutputCmd(spamassassin.SA_UPDATE_PATH()+' -D -v --channel updates.spamassassin.org --nogpg >'+tmpstr +' 2>&1');
end;


test_sa_update(tmpstr);
logs.DeleteFile(tmpstr);
update_spamassasin_saupdates_openprotect_com();
update_spamassasin_sought_rules_yerp_org();
fpsystem(spamassassin.SA_UPDATE_PATH()+ ' --checkonly -D >/usr/share/artica-postfix/ressources/logs/sa.update.dbg 2>&1');
fpsystem('/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/sa.update.dbg');

 if FileExists('/usr/bin/re2c') then begin
     sa_compile:=SYS.LOCATE_GENERIC_BIN('sa-compile');
     if not FIleExists(sa_compile) then exit;
 end;

 if FileExists('/etc/artica-postfix/cron.1/sa_compile') then logs.DeleteFile('/etc/artica-postfix/cron.1/sa_compile');
 logs.WriteToFile('#','/etc/artica-postfix/cron.1/sa_compile');

pid:=SYS.PIDOF(sa_compile);
if not SYS.PROCESS_EXIST(pid) then logs.OutputCmd(SYS.EXEC_NICE()+'/usr/bin/sa-compile -D');
end;
//#############################################################################
PROCEDURE tupdate.update_spamassasin_saupdates_openprotect_com();
var
   timemin:integer;
   tmpstr:string;
begin

if not FileExists(spamassassin.SA_UPDATE_PATH()) then begin
   logs.Debuglogs('update_spamassasin_saupdates_openprotect_com:: unable to stat sa-update');
   exit;
end;
if not SYS.COMMANDLINE_PARAMETERS('--force') then begin
   timemin:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/cron.1/saupdates.openprotect.com');
   if timemin < 60 then begin
     logs.Debuglogs('update_spamassasin_saupdates_openprotect_com:: need more than 60 minutes current is '+ IntTostr(timemin)+' Mn');
     exit;
   end;
end;
 tmpstr:=logs.FILE_TEMP();
 logs.OutputCmd(spamassassin.SA_UPDATE_PATH()+' -D -v --channel saupdates.openprotect.com --nogpg >'+tmpstr +' 2>&1');
 logs.WriteToFile('#','/etc/artica-postfix/cron.1/saupdates.openprotect.com');
 test_sa_update(tmpstr);
 logs.DeleteFile(tmpstr);

end;
//#############################################################################
PROCEDURE tupdate.update_spamassasin_sought_rules_yerp_org();
var
   timemin:integer;
   tmpstr:string;
begin

if not FileExists(spamassassin.SA_UPDATE_PATH()) then begin
   logs.Debuglogs('update_spamassasin_saupdates_openprotect_com:: unable to stat sa-update');
   exit;
end;
if not SYS.COMMANDLINE_PARAMETERS('--force') then begin
   timemin:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/cron.1/sought.rules.yerp.org');
   if timemin < 240 then begin
     logs.Debuglogs('update_spamassasin_sought_rules_yerp_org:: need more than 240 minutes current is '+ IntTostr(timemin)+' Mn');
     exit;
   end;
end;

   tmpstr:=logs.FILE_TEMP();
   logs.OutputCmd(spamassassin.SA_UPDATE_PATH()+' -D -v --channel sought.rules.yerp.org --nogpg >'+tmpstr +' 2>&1');
   logs.WriteToFile('#','/etc/artica-postfix/cron.1/sought.rules.yerp.org');
test_sa_update(tmpstr);
logs.DeleteFile(tmpstr);
end;
//#############################################################################
PROCEDURE tupdate.update_spamassasin_scanmail();
var
   timemin:integer;
   tmpstr:string;
begin

if not FileExists(spamassassin.SA_UPDATE_PATH()) then begin
   logs.Debuglogs('update_spamassasin_scanmail:: unable to stat sa-update');
   exit;
end;
if not SYS.COMMANDLINE_PARAMETERS('--force') then begin
   timemin:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/cron.1/update_spamassasin_scanmail');
   if timemin < 60 then begin
     logs.Debuglogs('update_spamassasin_scanmail:: need more than 60 minutes current is '+ IntTostr(timemin)+' Mn');
     exit;
   end;
end;

   tmpstr:=logs.FILE_TEMP();
   logs.OutputCmd(SYS.LOCATE_GENERIC_BIN('perl')+' /usr/share/artica-postfix/bin/install/spamassassin/ScamNailer.pl >'+tmpstr +' 2>&1');
   logs.WriteToFile('#','/etc/artica-postfix/cron.1/update_spamassasin_scanmail');
   logs.DeleteFile(tmpstr);
end;
//#############################################################################


Procedure tupdate.test_sa_update(tmpstr:string);
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin

if not FileExists(tmpstr) then exit;

l:=Tstringlist.Create;
try l.LoadFromFile(tmpstr) except exit; end;
RegExpr:=TRegExpr.CReate;
for i:=0 to l.Count-1 do begin
   RegExpr.Expression:='dns:\s+query failed:\s+(.+?)\s+.+?SERVFAIL';
   if RegExpr.Exec(l.Strings[i]) then begin
      logs.NOTIFICATION('spamassassin failed updating from '+RegExpr.Match[1],l.Strings[i],'update');
   end;
end;

RegExpr.Free;
l.free;
end;
//#############################################################################
Procedure tupdate.update_spamassassin_blacklist();
var
   RegExpr:TRegExpr;
   l:TstringList;
   Spamconf:string;
   Spamconf_path:string;
   CurrentlistSize:integer;
   ETag_server:string;
   ETag_Client:string;
   i:integer;
   s:Tstringlist;
   TargetFileSize:integer;
   OriginalWorkNumber:integer;
   EnableSaBlackListUpdate:integer;
begin

  if not TryStrToInt(SYS.GET_INFO('EnableSaBlackListUpdate'),EnableSaBlackListUpdate) then EnableSaBlackListUpdate:=0;
  if EnableSaBlackListUpdate=0 then begin
        logs.Debuglogs('tupdate.update_spamassassin_blacklist():: disabled, skiping');
        exit;
  end;

  Spamconf:=spamassassin.SPAMASSASSIN_LOCAL_CF();

  if not FileExists(SYS.LOCATE_CURL()) then begin
     logs.Syslogs('tupdate.update_spamassassin_blacklist():: Unable to stat /usr/bin/curl');
     logs.NOTIFICATION('missing /usr/bin/curl','Artica could not perform spamassassin blacklist update without this tool','update');
     exit;
  end;

  if not FileExists(Spamconf) then begin
     logs.Debuglogs('tupdate.update_spamassassin_blacklist():: Unable to stat local.cf configuration file');
     exit;
  end;
  Spamconf_path:=ExtractFilePath(Spamconf);

  if not FileExists(Spamconf_path+'sa-blacklist.current') then begin
        CurrentlistSize:=0;
  end else begin
      CurrentlistSize:=SYS.FileSize_bytes(Spamconf_path+'sa-blacklist.current');
  end;


   ETag_Client:=SYS.GET_INFO('SpamassassinBlackListeTag');

   if not FileExists(Spamconf_path+'sa-blacklist.current') then ETag_Client:='';

   logs.Debuglogs('tupdate.update_spamassassin_blacklist():: working directory is "'+Spamconf_path+'" Current size:'+IntToStr(CurrentlistSize));
   logs.Debuglogs('tupdate.update_spamassassin_blacklist():: Download blacklist header from www.sa-blacklist.stearns.org');
   SYS.WGET_DOWNLOAD_FILE('http://www.sa-blacklist.stearns.org/sa-blacklist/sa-blacklist.current',Spamconf_path+'sa-blacklist.current.header',true);

   l:=Tstringlist.Create;
   l.LoadFromFile(Spamconf_path+'sa-blacklist.current.header');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='Last-Modified:(.+)';
   for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
           ETag_server:=trim(RegExpr.Match[1]);
           ETag_server:=AnsiReplaceText(ETag_server,'"','');
           break;
       end;
   end;
   l.clear;

   if length(ETag_server)=0 then begin
        logs.Syslogs('tupdate.update_spamassassin_blacklist():: Last-Modified is null !');
        logs.NOTIFICATION('Missing ETag for updating Spamassassin black lists','Artica could not detect Last-Modified from target server. Please refer to artica forum http://www.artica.fr','update');
        exit;
   end;

   logs.Debuglogs('tupdate.update_spamassassin_blacklist():: eTag ' + ETag_server+'<>'+ETag_Client);

   if FIleExists(Spamconf_path+'sa-blacklist.work') then begin
      l.LoadFromFile(Spamconf_path+'sa-blacklist.work');
      OriginalWorkNumber:=l.Count;
      l.Clear;
   end;

   if ETag_server<>ETag_Client then begin
   logs.Debuglogs('tupdate.update_spamassassin_blacklist():: Downloading pattern file');
   SYS.WGET_DOWNLOAD_FILE('http://www.sa-blacklist.stearns.org/sa-blacklist/sa-blacklist.current',Spamconf_path+'sa-blacklist.current');


   l.LoadFromFile(Spamconf_path+'sa-blacklist.current');
   RegExpr.Expression:='^blacklist_from';
   s:=TstringList.Create;

   for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
            s.Add(l.Strings[i]);
       end;
   end;

   if s.Count>0 then begin
      if s.Count>OriginalWorkNumber then begin
       s.SaveToFile(Spamconf_path+'sa-blacklist.work');
       SYS.set_INFO('SpamassassinBlackListeTag',ETag_server);
       TargetFileSize:=sys.FileSize_ko(Spamconf_path+'sa-blacklist.work');
       logs.Syslogs('Success updating sa-blacklist with '+IntToStr(s.Count)+' blacklisted server ('+IntTOStr(TargetFileSize)+'ko)');
       logs.NOTIFICATION('Success updating sa-blacklist with '+IntToStr(s.Count) +' blacklisted server ('+IntTOStr(TargetFileSize)+'ko)','Success updatign sa-blacklist with '+IntToStr(s.Count) +' blacklisted server ('+IntToStr(TargetFileSize)+'ko)','update');
       fpsystem('/usr/share/artica-postfix/bin/artica-install spamassassin-reload');
      end;
   end;


   end;



end;
//#############################################################################

procedure tupdate.ipblocks();
begin
  logs.Debuglogs('ipblocks:: checking www.ipdeny.com');
  SYS.WGET_DOWNLOAD_FILE('http://www.ipdeny.com/ipblocks/data/countries/all-zones.tar.gz','/tmp/all-zones.tar.gz');
  if not FileExists('/tmp/all-zones.tar.gz') then begin
        logs.Debuglogs('ipblocks:: checking www.ipdeny.com failed');
        exit;
  end;

  forceDirectories('/var/log/artica-postfix/ipblocks');
  fpsystem(SYS.LOCATE_GENERIC_BIN('tar')+' -xf /tmp/all-zones.tar.gz -C  /var/log/artica-postfix/ipblocks/');
  logs.Debuglogs('ipblocks:: checking www.ipdeny.com success');
  fpsystem(SYS.LOCATE_GENERIC_BIN('nohup')+' '+SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfix.iptables.php --ipdeny >/dev/null 2>&1 &');


end;
//#############################################################################
Procedure tupdate.update_dansguardian();
var
   RegExpr:TRegExpr;
   l:TstringList;
   d:TstringList;
   e:TstringList;
   i:Integer;
   md5sum:string;
   dans:tdansguardian;
   DansGuardianEnabled:integer;
   DisableUpdateDansguardian:integer;
begin
 if SQUIDEnable=0 then begin
    logs.Debuglogs('update_dansguardian:: Proxy family is disabled');
    exit;
 end;


dans:=Tdansguardian.Create(SYS);
if not FileExists(dans.BIN_PATH()) then begin
    logs.Debuglogs('Dansguardian is not installed');
    exit;
end;

if not TryStrToInt(SYS.GET_INFO('DansGuardianEnabled'),DansGuardianEnabled) then DansGuardianEnabled:=0;
if not TryStrToInt(SYS.GET_INFO('DisableUpdateDansguardian'),DisableUpdateDansguardian) then DisableUpdateDansguardian:=0;

if DisableUpdateDansguardian=1 then exit;

if Not FileExists('/usr/bin/md5sum') then begin
   logs.Syslogs('update_dansguardian:: warning unable to stat /usr/bin/md5sum !!');
   exit;
end;


RegExpr:=TRegExpr.Create;
logs.Debuglogs('update_dansguardian:: Getting index file');
ForceDirectories('/etc/dansguardian/updates');
SYS.WGET_DOWNLOAD_FILE('ftp://ftp.univ-tlse1.fr/pub/reseau/cache/squidguard_contrib/MD5SUM.LST','/etc/dansguardian/updates/MD5SUM.LST');
l:=TstringList.Create;
if not FileExists('/etc/dansguardian/updates/MD5SUM.LST') then begin
   logs.Debuglogs('update_dansguardian:: Failed to download index file');
   exit;
end;
d:=TstringList.Create;
l:=Tstringlist.Create;
l.LoadFromFile('/etc/dansguardian/updates/MD5SUM.LST');
RegExpr.Expression:='(.+)\s+(.+?)\.tar.gz';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
        logs.Debuglogs('update_dansguardian:: Checking database "'+RegExpr.Match[2]+'"');

        if not DirectoryExists('/etc/dansguardian/lists/blacklists') then begin
           d.Add(RegExpr.Match[2]+'.tar.gz');
           continue;
        end;


        if not FileExists('/etc/dansguardian/updates/'+RegExpr.Match[2]+'.tar.gz') then begin
           d.Add(RegExpr.Match[2]+'.tar.gz');
           continue;
        end;


        md5sum:=SYS.MD5Sum('/etc/dansguardian/updates/'+RegExpr.Match[2]+'.tar.gz');
        logs.Debuglogs(RegExpr.Match[2]+'.tar.gz :'+md5sum);
        if trim(md5sum)<>trim(RegExpr.Match[1]) then d.Add(RegExpr.Match[2]+'.tar.gz');

    end;
end;



if d.Count=0 then begin
    fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dansguardian.compile.php  --patterns');
    logs.Debuglogs('update_dansguardian:: no update');
    RegExpr.free;
    d.free;
    l.free;
    exit;
end;

e:=TstringList.Create;
for i:=0 to d.Count-1 do begin
     SYS.WGET_DOWNLOAD_FILE('ftp://ftp.univ-tlse1.fr/pub/reseau/cache/squidguard_contrib/'+d.Strings[i],'/etc/dansguardian/updates/'+d.Strings[i]);
     if FileExists('/etc/dansguardian/updates/'+d.Strings[i]) then begin
        ForceDirectories('/etc/dansguardian/lists/blacklists');
        logs.OutputCmd('/bin/tar -xf /etc/dansguardian/updates/'+d.Strings[i]+' -C /etc/dansguardian/lists/blacklists/');
        e.Add(d.Strings[i]);
     end;

end;

        fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dansguardian.compile.php  --patterns');

if DansGuardianEnabled=1 then logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update ' + IntToStr(e.Count)+ ' URLs databases' ,e.Text,'update');
    RegExpr.free;
    d.free;
    l.free;
    e.free;
end;
//#############################################################################
Procedure tupdate.update_squidguard();
var
   RegExpr:TRegExpr;
   l:TstringList;
   d:TstringList;
   e:TstringList;
   i:Integer;
   md5sum:string;
   dans:tdansguardian;
   squidGuardEnabled:integer;
   squidguard:Tsquidguard;
   EnableUfdbGuard:integer;
begin
 if SQUIDEnable=0 then begin
    logs.Debuglogs('update_squidguard:: Proxy family is disabled');
    exit;
 end;


squidguard:=tsquidguard.Create(SYS);
if not FileExists(squidguard.BIN_PATH()) then begin
    if not FileExists(SYS.LOCATE_GENERIC_BIN('ufdbgclient')) then begin
       logs.Debuglogs('update_squidguard/ufdbgclient is not installed');
       exit;
    end;
end;

if not TryStrToInt(SYS.GET_INFO('squidGuardEnabled'),squidGuardEnabled) then squidGuardEnabled:=0;
if not TryStrToInt(SYS.GET_INFO('EnableUfdbGuard'),EnableUfdbGuard) then EnableUfdbGuard:=0;

if EnableUfdbGuard=1 then squidGuardEnabled:=1;

if Not FileExists('/usr/bin/md5sum') then begin
   logs.Syslogs('update_squidguard:: warning unable to stat /usr/bin/md5sum !!');
   exit;
end;


RegExpr:=TRegExpr.Create;
logs.Debuglogs('update_squidguard:: Getting index file');
ForceDirectories('/etc/squid/squidGuard/updates');
SYS.WGET_DOWNLOAD_FILE('ftp://ftp.univ-tlse1.fr/pub/reseau/cache/squidguard_contrib/MD5SUM.LST','/etc/squid/squidGuard/updates/MD5SUM.LST');
l:=TstringList.Create;
if not FileExists('/etc/squid/squidGuard/updates/MD5SUM.LST') then begin
   logs.Debuglogs('update_squidguard:: Failed to download index file');
   exit;
end;
d:=TstringList.Create;
l:=Tstringlist.Create;
l.LoadFromFile('/etc/squid/squidGuard/updates/MD5SUM.LST');
RegExpr.Expression:='(.+)\s+(.+?)\.tar.gz';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
        logs.Debuglogs('update_squidguard:: Checking database "'+RegExpr.Match[2]+'"');

        if not DirectoryExists('/var/lib/squidguard') then begin
           d.Add(RegExpr.Match[2]+'.tar.gz');
           continue;
        end;


        if not FileExists('/etc/squid/squidGuard/updates/'+RegExpr.Match[2]+'.tar.gz') then begin
           d.Add(RegExpr.Match[2]+'.tar.gz');
           continue;
        end;


        md5sum:=SYS.MD5Sum('/etc/squid/squidGuard/updates/'+RegExpr.Match[2]+'.tar.gz');
        logs.Debuglogs(RegExpr.Match[2]+'.tar.gz :'+md5sum);
        if trim(md5sum)<>trim(RegExpr.Match[1]) then d.Add(RegExpr.Match[2]+'.tar.gz');

    end;
end;



if d.Count=0 then begin
   logs.Debuglogs('update_squidguard:: no update');
    RegExpr.free;
    d.free;
    l.free;
    exit;
end;

e:=TstringList.Create;
for i:=0 to d.Count-1 do begin
     SYS.WGET_DOWNLOAD_FILE('ftp://ftp.univ-tlse1.fr/pub/reseau/cache/squidguard_contrib/'+d.Strings[i],'/etc/squid/squidGuard/updates/'+d.Strings[i]);
     if FileExists('/etc/squid/squidGuard/updates/'+d.Strings[i]) then begin
        ForceDirectories('/var/lib/squidguard');
        logs.OutputCmd('/bin/tar -xf /etc/squid/squidGuard/updates/'+d.Strings[i]+' -C /var/lib/squidguard/');

        e.Add(d.Strings[i]);
     end;

end;

fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.squidguard.php --build');
if FileExists(squidguard.BIN_PATH()) then fpsystem(squidguard.BIN_PATH()+' -P -C all &');
if FileExists(SYS.LOCATE_GENERIC_BIN('ufdbgclient')) then  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.squidguard.php --ufdbguard-dbs &');

if squidGuardEnabled=1 then logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update  squidGuard/ufdbguard ' + IntToStr(e.Count)+ ' URLs databases' ,e.Text,'update');
    RegExpr.free;
    d.free;
    l.free;
    e.free;
end;

//#############################################################################
Procedure tupdate.update_webfilterplus();
var
   md5_in:string;
   md5_out:string;
   RegExpr:TRegExpr;
   SYSTEMID:string;
   CONF:TiniFile;
   LICENSE:string;
   tmp:string;
   checked:boolean;
   l:Tstringlist;
   i:integer;
   squidGuardEnabled:integer;
   DansGuardianEnabled:integer;
   squidguard:tsquidguard;
   dans:Tdansguardian;
   filetimemin:integer;
   EnableUfdbGuard:integer;
begin
checked:=false;

 if SQUIDEnable=0 then begin
    logs.Debuglogs('update_webfilterplus:: Proxy family is disabled');
    exit;
 end;

if not TryStrToInt(SYS.GET_INFO('squidGuardEnabled'),squidGuardEnabled) then squidGuardEnabled:=0;
if not TryStrToInt(SYS.GET_INFO('DansGuardianEnabled'),DansGuardianEnabled) then DansGuardianEnabled:=0;
if not TryStrToInt(SYS.GET_INFO('EnableUfdbGuard'),EnableUfdbGuard) then EnableUfdbGuard:=0;
if EnableUfdbGuard=1 then squidGuardEnabled:=1;
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.web-community-filter.php');

if not FileExists('/etc/artica-postfix/settings/Daemons/SYSTEMID') then begin
    logs.Debuglogs('update_webfilterplus:: no SYSTEMID, aborting');
    exit;
end;

if not FileExists('/etc/artica-postfix/settings/Daemons/shallalistLicense') then begin
    logs.Debuglogs('update_webfilterplus:: no license information');
    exit;
end;

if not SYS.COMMANDLINE_PARAMETERS('--force') then begin
if FileExists('/etc/artica-postfix/filters.plus.md5') then begin
   filetimemin:=SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/filters.plus.md5');
   if SYS.FILE_TIME_BETWEEN_MIN('/etc/artica-postfix/filters.plus.md5')<1440 then begin
     logs.Debuglogs('update_webfilterplus:: '+INtToStr(filetimemin) +' need more that 1440 minutes, aborting');
     exit;
   end;
end;
end;


SYSTEMID:=SYS.GET_INFO('SYSTEMID');
CONF:=TiniFile.Create('/etc/artica-postfix/settings/Daemons/shallalistLicense');
LICENSE:=CONF.ReadString('SHALLA','LICENSE','');
if length(LICENSE)=0 then begin
   logs.Debuglogs('update_webfilterplus:: no license information');
end;
tmp:=logs.FILE_TEMP();
RegExpr:=TRegExpr.Create;
SYS.WGET_DOWNLOAD_FILE('http://www.artica.fr/shalla-orders.php?check=yes&lic='+LICENSE+'&uuid='+SYSTEMID,tmp);
l:=Tstringlist.Create;
l.LoadFromFile(tmp);
logs.DeleteFile(tmp);
RegExpr.Expression:='<ANSWER>OK</ANSWER>';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
      logs.Debuglogs('update_webfilterplus:: License OK (line '+ IntToStr(i)+')');
      checked:=true;
      break;
    end;
end;


if not checked then begin
   logs.Debuglogs('update_webfilterplus:: License FAILED');
   exit;
end;
tmp:=logs.FILE_TEMP();
SYS.WGET_DOWNLOAD_FILE('http://www.artica.fr/download/shalla/shallalist.tar.gz.md5',tmp);
RegExpr.Expression:='(.+?)\s+';
if RegExpr.Exec(logs.ReadFromFile(tmp)) then md5_out:=trim(RegExpr.Match[1]);
logs.DeleteFile(tmp);
if RegExpr.Exec(logs.ReadFromFile('/etc/artica-postfix/filters.plus.md5')) then md5_in:=trim(RegExpr.Match[1]);
logs.Debuglogs('update_webfilterplus:: remote: "'+md5_out+'" local: "'+md5_in+'"');
if md5_in<>md5_out then begin
    tmp:=logs.FILE_TEMP();
    SYS.WGET_DOWNLOAD_FILE('http://www.artica.fr/download/shalla/shallalist.tar.gz',tmp+'.tar.gz');
    if not FileExists(tmp+'.tar.gz') then begin
      logs.Debuglogs('update_webfilterplus:: Extract failed (file does not exists)');
      exit;
    end;

    if SYS.FileSize_ko(tmp+'.tar.gz')<1000 then begin
       logs.Debuglogs('update_webfilterplus:: Extract failed (file seems corrupted)');
       exit;
    end;



squidguard:=tsquidguard.Create(SYS);
    if squidGuardEnabled=1 then begin
       logs.Debuglogs('update_webfilterplus:: SquidGuard is enabled, starting compilation');
       ForceDirectories('/var/lib/squidguard/web-filter-plus');
       logs.Debuglogs('update_webfilterplus:: Extracting...');
       fpsystem(SYS.LOCATE_GENERIC_BIN('tar')+' -xf '+tmp+'.tar.gz -C /var/lib/squidguard/web-filter-plus/');
       logs.Debuglogs('update_webfilterplus:: Extracting success');
       logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update HTTP Black list licensed database' ,'Success update HTTP Black list licensed database','update');
       fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.squidguard.php --build');
       if FIleExists(squidguard.BIN_PATH()) then fpsystem(squidguard.BIN_PATH()+' -P -C all &');
       if FileExists(SYS.LOCATE_GENERIC_BIN('ufdbgclient')) then  fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.squidguard.php --ufdbguard-dbs &');
    end;

squidguard.free;
if SYS.COMMANDLINE_PARAMETERS('--force-dansguardian') then DansGuardianEnabled:=1;
dans:=Tdansguardian.Create(SYS);
if FileExists(dans.BIN_PATH()) then begin
    logs.Debuglogs('update_webfilterplus:: DansGuardian is installed');
    if DansGuardianEnabled=1 then begin

       logs.Debuglogs('update_webfilterplus:: DansGuardian is enabled, starting compilation');
       ForceDirectories('/etc/dansguardian/lists/web-filter-plus');
       logs.Debuglogs('update_webfilterplus:: Extracting...');
       fpsystem(SYS.LOCATE_GENERIC_BIN('tar')+' -xf '+tmp+'.tar.gz -C /etc/dansguardian/lists/web-filter-plus/');
       logs.Debuglogs('update_webfilterplus:: Extracting success');
       logs.NOTIFICATION('[ARTICA]:('+sys.HOSTNAME_g()+') success update HTTP Black list licensed database' ,'Success update HTTP Black list licensed database','update');
       fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.web-community-filter.php');
       fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dansguardian.compile.php');
    end;
end;
 logs.DeleteFile(tmp+'.tar.gz');
end;




end;





end.
