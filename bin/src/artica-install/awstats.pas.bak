unit awstats;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,squid;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tawstats=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     squid:Tsquid;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    AWSTATS_GET_VALUE(key:string;path:string):string;
    function    AWSTATS_SET_VALUE(key:string;value:string;path:string):string;
    function    AWSTATS_SET_PLUGIN(value:string;path:string):string;
    function    AWSTATS_MAILLOG_CONVERT_PATH_SOURCE():string;
    procedure   CreateAwstats_mail();
    function    AWSTATS_ETC_PATH():string;
    function    AWSTATS_PATH():string;
    function    AWSTATS_VERSION():string;
    function    MLC_PATH():string;
    procedure   AWSTATS_GENERATE();
    function    AWSTATS_www_root():string;
    procedure   START_SERVICE();
    function    awstats_conf():string;
END;

implementation

constructor tawstats.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tawstats.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
function tawstats.AWSTATS_PATH():string;
begin
if FileExists('/opt/artica/awstats/wwwroot/cgi-bin/awstats.pl') then exit('/opt/artica/awstats/wwwroot/cgi-bin/awstats.pl');
if FileExists('/usr/lib/cgi-bin/awstats.pl') then exit('/usr/lib/cgi-bin/awstats.pl');
if FileExists('/srv/www/cgi-bin/awstats.pl') then exit('/srv/www/cgi-bin/awstats.pl');
if FileExists('/var/www/awstats/awstats.pl') then exit('/var/www/awstats/awstats.pl');
if FileExists('/usr/share/awstats/wwwroot/cgi-bin/awstats.pl') then exit('/usr/share/awstats/wwwroot/cgi-bin/awstats.pl');
end;

//#############################################################################
procedure tawstats.START_SERVICE();
begin

logs.Debuglogs('START_SERVICE:: starting settings awstats configurations files...');
CreateAwstats_mail();

logs.OutputCmd('/bin/ln -s --force ' +artica_path+'/css/images/logo.gif ' + AWSTATS_www_root+'/icon/other/logo.gif');

SYS.CRON_CREATE_SCHEDULE('0,10,20,30,40,50,55,59 * * * *',artica_path + '/bin/artica-install -awstats generate','awstats');
end;
//#############################################################################
function tawstats.AWSTATS_www_root():string;
begin
if DirectoryExists('/usr/share/awstats/icon') then exit('/usr/share/awstats');
if DirectoryExists('/var/www/awstats/icon')  then exit('/var/www/awstats');
end;
//#############################################################################
function tawstats.AWSTATS_ETC_PATH():string;
begin
if DirectoryExists('/etc/awstats') then exit('/etc/awstats');
if DirectoryExists('/opt/artica/etc/awstats') then exit('/opt/artica/etc/awstats');
end;
//#############################################################################
function tawstats.MLC_PATH():string;
begin
if FileExists('/usr/bin/mlc') then exit('/usr/bin/mlc');
if FileExists('/usr/share/artica-postfix/bin/install/awstats/mlc') then exit('/usr/share/artica-postfix/bin/install/awstats/mlc');
end;
//#############################################################################
function tawstats.awstats_conf():string;
begin
if FileExists(AWSTATS_ETC_PATH()+'/awstats.localhost.localdomain.conf') then exit(AWSTATS_ETC_PATH()+'/awstats.localhost.localdomain.conf');
if FileExists(AWSTATS_ETC_PATH()+'/awstats.conf') then exit(AWSTATS_ETC_PATH()+'/awstats.conf');
end;
//#############################################################################

function tawstats.AWSTATS_GET_VALUE(key:string;path:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    ValueResulted:string;
begin

   if not FileExists(path) then  begin
      logs.Debuglogs('AWSTATS_GET_VALUE:: unable to stat '+path);
      exit;
   end;

   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(path);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              FileDatas.Free;
              ValueResulted:=RegExpr.Match[2];
              if ValueResulted='"' then ValueResulted:='';
              RegExpr.Free;
              exit(ValueResulted);
           end;

   end;
   FileDatas.Free;
   RegExpr.Free;
end;
//#############################################################################
function tawstats.AWSTATS_SET_VALUE(key:string;value:string;path:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
begin
   if not FileExists(path) then  begin
      logs.Debuglogs('AWSTATS_GET_VALUE:: unable to stat '+path);
      exit;
   end;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(path);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
                FileDatas.Strings[i]:=key + '=' + value;
                FileDatas.SaveToFile(path);
                FileDatas.Free;
                RegExpr.Free;
                exit;

           end;

   end;

  FileDatas.Add(key + '=' + value);
  FileDatas.SaveToFile(path);
  FileDatas.Free;
  RegExpr.Free;
  result:='';

end;
//#############################################################################
procedure tawstats.CreateAwstats_mail();

var
   servername:string;
   l:TstringList;
   maillog:string;
begin

if not FileExists(AWSTATS_PATH()) then begin
   logs.Debuglogs('CreateAwstats_mail():Unable to stat awstats.pl');
   exit;
end;

if not FileExists(awstats_conf()) then begin
   logs.Debuglogs('CreateAwstats_mail():Unable to stat awstats config');
   exit;
end;

if not DirectoryExists('/var/lib/awstats') then begin
   ForceDirectories('/var/lib/awstats');
   logs.OutputCmd('/bin/chmod 750 /var/lib/awstats');
end;

servername:=SYS.HOSTNAME_g();
AWSTATS_SET_VALUE('SiteDomain',servername,awstats_conf());

l:=TstringList.Create;
if Fileexists('/usr/sbin/postfix') then begin
   logs.Debuglogs('CreateAwstats_mail():create stat postfix');
   maillog:=SYS.MAILLOG_PATH();
   if length(maillog)=0 then maillog:='/var/log/mail.log';

// cat `ls -tr /var/log/postfix/*` | /usr/local/bin/mlc 2> /var/log/maillogconvert.err |
l.Add('LogFile="/bin/cat '+SYS.MAILLOG_PATH()+'|'+MLC_PATH()+' 2>/tmp/tmp.txt|"');
l.Add('LogType=M');
l.Add('LogFormat="%time2 %email %email_r %host %host_r %method %url %code %bytesd"');
l.Add('LogSeparator=" "');
l.Add('SiteDomain='+servername);
l.Add('HostAliases="localhost localhost.localdomain 127.0.0.1"');
l.Add('DNSLookup=2');
l.Add('DirData="/var/lib/awstats"');
l.Add('DirCgi="/cgi-bin"');
l.Add('DirIcons="/awstats/icon"');
l.Add('AllowToUpdateStatsFromBrowser=1');
l.Add('AllowFullYearView=2');
l.Add('EnableLockForUpdate=0');
l.Add('DNSStaticCacheFile="dnscache.txt"');
l.Add('DNSLastUpdateCacheFile="dnscachelastupdate.txt"');
l.Add('SkipDNSLookupFor=""');
l.Add('AllowAccessFromWebToAuthenticatedUsersOnly=0');
l.Add('AllowAccessFromWebToFollowingAuthenticatedUsers=""');
l.Add('AllowAccessFromWebToFollowingIPAddresses=""');
l.Add('CreateDirDataIfNotExists=0');
l.Add('BuildHistoryFormat=text');
l.Add('BuildReportFormat=html');
l.Add('SaveDatabaseFilesWithPermissionsForEveryone=0');
l.Add('PurgeLogFile=0');
l.Add('ArchiveLogRecords=0');
l.Add('KeepBackupOfHistoricFiles=0');
l.Add('DefaultFile="index.html"');
l.Add('SkipHosts=""');
l.Add('SkipUserAgents=""');
l.Add('SkipFiles=""');
l.Add('SkipReferrersBlackList=""');
l.Add('OnlyHosts=""');
l.Add('OnlyUserAgents=""');
l.Add('OnlyFiles=""');
l.Add('NotPageList="css js class gif jpg jpeg png bmp ico swf"');
l.Add('ValidHTTPCodes="200 304"');
l.Add('ValidSMTPCodes="1 250"');
l.Add('AuthenticatedUsersNotCaseSensitive=0');
l.Add('URLNotCaseSensitive=0');
l.Add('URLWithAnchor=0');
l.Add('URLQuerySeparators="?;"');
l.Add('URLWithQuery=0');
l.Add('URLWithQueryWithOnlyFollowingParameters=""');
l.Add('URLWithQueryWithoutFollowingParameters=""');
l.Add('URLReferrerWithQuery=0');
l.Add('WarningMessages=1');
l.Add('ErrorMessages=""');
l.Add('DebugMessages=0');
l.Add('NbOfLinesForCorruptedLog=50');
l.Add('WrapperScript=""');
l.Add('DecodeUA=0');
l.Add('MiscTrackerUrl="/js/awstats_misc_tracker.js"');
l.Add('LevelForBrowsersDetection=0');
l.Add('LevelForOSDetection=0');
l.Add('LevelForRefererAnalyze=0');
l.Add('LevelForRobotsDetection=0');
l.Add('LevelForSearchEnginesDetection=0');
l.Add('LevelForFileTypesDetection=0');
l.Add('LevelForWormsDetection=0');
l.Add('UseFramesWhenCGI=1');
l.Add('DetailedReportsOnNewWindows=1');
l.Add('Expires=0');
l.Add('MaxRowsInHTMLOutput=1000');
l.Add('Lang="auto"');
l.Add('DirLang="./lang"');
l.Add('ShowMenu=1');
l.Add('ShowSummary=HB');
l.Add('ShowMonthStats=HB');
l.Add('ShowDaysOfMonthStats=HB');
l.Add('ShowDaysOfWeekStats=HB');
l.Add('ShowHoursStats=HB');
l.Add('ShowDomainsStats=1');
l.Add('ShowHostsStats=HBL');
l.Add('ShowAuthenticatedUsers=0');
l.Add('ShowRobotsStats=0');
l.Add('ShowWormsStats=0');
l.Add('ShowEMailSenders=HBML');
l.Add('ShowEMailReceivers=HBML');
l.Add('ShowSessionsStats=0');
l.Add('ShowPagesStats=0');
l.Add('ShowFileTypesStats=0');
l.Add('ShowFileSizesStats=0');
l.Add('ShowOSStats=0');
l.Add('ShowBrowsersStats=0');
l.Add('ShowScreenSizeStats=0');
l.Add('ShowOriginStats=0');
l.Add('ShowKeyphrasesStats=0');
l.Add('ShowKeywordsStats=0');
l.Add('ShowMiscStats=0');
l.Add('ShowHTTPErrorsStats=0');
l.Add('ShowSMTPErrorsStats=1');
l.Add('ShowClusterStats=0');
l.Add('AddDataArrayMonthStats=1');
l.Add('AddDataArrayShowDaysOfMonthStats=1');
l.Add('AddDataArrayShowDaysOfWeekStats=1');
l.Add('AddDataArrayShowHoursStats=1');
l.Add('IncludeInternalLinksInOriginSection=0');
l.Add('MaxNbOfDomain = 10');
l.Add('MinHitDomain  = 1');
l.Add('MaxNbOfHostsShown = 10');
l.Add('MinHitHost    = 1');
l.Add('MaxNbOfLoginShown = 10');
l.Add('MinHitLogin   = 1');
l.Add('MaxNbOfRobotShown = 10');
l.Add('MinHitRobot   = 1');
l.Add('MaxNbOfPageShown = 10');
l.Add('MinHitFile    = 1');
l.Add('MaxNbOfOsShown = 10');
l.Add('MinHitOs      = 1');
l.Add('MaxNbOfBrowsersShown = 10');
l.Add('MinHitBrowser = 1');
l.Add('MaxNbOfScreenSizesShown = 5');
l.Add('MinHitScreenSize = 1');
l.Add('MaxNbOfWindowSizesShown = 5');
l.Add('MinHitWindowSize = 1');
l.Add('MaxNbOfRefererShown = 10');
l.Add('MinHitRefer   = 1');
l.Add('MaxNbOfKeyphrasesShown = 10');
l.Add('MinHitKeyphrase = 1');
l.Add('MaxNbOfKeywordsShown = 10');
l.Add('MinHitKeyword = 1');
l.Add('MaxNbOfEMailsShown = 20');
l.Add('MinHitEMail   = 1');
l.Add('FirstDayOfWeek=1');
l.Add('ShowFlagLinks=""');
l.Add('ShowLinksOnUrl=1');
l.Add('UseHTTPSLinkForUrl=""');
l.Add('MaxLengthOfShownURL=64');
l.Add('HTMLHeadSection="<page_start>"');
l.Add('HTMLEndSection="<page_end>"');
l.Add('Logo="logo.gif"');
l.Add('LogoLink="/statistics.index.php"');
l.Add('BarWidth   = 260');
l.Add('BarHeight  = 90');
l.Add('StyleSheet=""');
l.Add('ExtraTrackedRowsLimit=500');
l.Add('DNSLoOKup=1');
try
logs.Debuglogs('CreateAwstats_mail:: Saving '+AWSTATS_ETC_PATH() +'/awstats.mail.conf');
l.SaveToFile(AWSTATS_ETC_PATH() +'/awstats.mail.conf');
except
logs.Debuglogs('CreateAwstats_mail:: fatal  error while saving '+AWSTATS_ETC_PATH() +'/awstats.mail.conf');
exit;
end;
end;


if fileExists(squid.SQUID_BIN_PATH()) then begin

l.Clear;
l.Add('LogFile='+squid.SQUID_GET_CONFIG('access_log'));
l.Add('LogType=W');
l.Add('LogFormat="%host %other %logname %time1 %methodurl %code %bytesd %other"');
l.Add('LogSeparator=" "');
l.Add('SiteDomain='+servername);
l.Add('HostAliases="localhost localhost.localdomain 127.0.0.1"');
l.Add('DNSLookup=2');
l.Add('DirData="/var/lib/awstats"');
l.Add('DirCgi="/cgi-bin"');
l.Add('DirIcons="/awstats/icon"');
l.Add('AllowToUpdateStatsFromBrowser=1');
l.Add('AllowFullYearView=2');
l.Add('EnableLockForUpdate=0');
l.Add('DNSStaticCacheFile="dnscache.txt"');
l.Add('DNSLastUpdateCacheFile="dnscachelastupdate.txt"');
l.Add('SkipDNSLookupFor=""');
l.Add('AllowAccessFromWebToAuthenticatedUsersOnly=0');
l.Add('AllowAccessFromWebToFollowingAuthenticatedUsers=""');
l.Add('AllowAccessFromWebToFollowingIPAddresses=""');
l.Add('CreateDirDataIfNotExists=0');
l.Add('BuildHistoryFormat=text');
l.Add('BuildReportFormat=html');
l.Add('SaveDatabaseFilesWithPermissionsForEveryone=0');
l.Add('PurgeLogFile=0');
l.Add('ArchiveLogRecords=0');
l.Add('KeepBackupOfHistoricFiles=0');
l.Add('DefaultFile="index.html"');
l.Add('SkipHosts=""');
l.Add('SkipUserAgents=""');
l.Add('SkipFiles=""');
l.Add('SkipReferrersBlackList=""');
l.Add('OnlyHosts=""');
l.Add('OnlyUserAgents=""');
l.Add('OnlyFiles=""');
l.Add('NotPageList="css js class gif jpg jpeg png bmp ico swf"');
l.Add('ValidHTTPCodes="200 304"');
l.Add('ValidSMTPCodes="1 250"');
l.Add('AuthenticatedUsersNotCaseSensitive=0');
l.Add('URLNotCaseSensitive=0');
l.Add('URLWithAnchor=0');
l.Add('URLQuerySeparators="?;"');
l.Add('URLWithQuery=0');
l.Add('URLWithQueryWithOnlyFollowingParameters=""');
l.Add('URLWithQueryWithoutFollowingParameters=""');
l.Add('URLReferrerWithQuery=0');
l.Add('WarningMessages=1');
l.Add('ErrorMessages=""');
l.Add('DebugMessages=0');
l.Add('NbOfLinesForCorruptedLog=50');
l.Add('WrapperScript=""');
l.Add('DecodeUA=0');
l.Add('MiscTrackerUrl="/js/awstats_misc_tracker.js"');
l.Add('UseFramesWhenCGI=1');
l.Add('DetailedReportsOnNewWindows=1');
l.Add('Expires=0');
l.Add('MaxRowsInHTMLOutput=1000');
l.Add('Lang="auto"');
l.Add('DirLang="/usr/share/awstats/lang"');
l.Add('ShowMenu=1');
l.Add('ShowSummary=UVPHB');
l.Add('ShowMonthStats=UVPHB');
l.Add('ShowDaysOfMonthStats=VPHB');
l.Add('ShowDaysOfWeekStats=PHB');
l.Add('ShowHoursStats=PHB');
l.Add('ShowDomainsStats=PHB');
l.Add('ShowHostsStats=PHBL');
l.Add('ShowAuthenticatedUsers=0');
l.Add('ShowRobotsStats=HBL');
l.Add('ShowWormsStats=0');
l.Add('ShowEMailSenders=0');
l.Add('ShowEMailReceivers=0');
l.Add('ShowSessionsStats=1');
l.Add('ShowPagesStats=PBEX');
l.Add('ShowFileTypesStats=HB');
l.Add('ShowFileSizesStats=0');
l.Add('ShowOSStats=1');
l.Add('ShowBrowsersStats=1');
l.Add('ShowScreenSizeStats=0');
l.Add('ShowOriginStats=PH');
l.Add('ShowKeyphrasesStats=1');
l.Add('ShowKeywordsStats=1');
l.Add('ShowMiscStats=a');
l.Add('ShowHTTPErrorsStats=1');
l.Add('ShowSMTPErrorsStats=0');
l.Add('ShowClusterStats=0');
l.Add('AddDataArrayMonthStats=1');
l.Add('AddDataArrayShowDaysOfMonthStats=1');
l.Add('AddDataArrayShowDaysOfWeekStats=1');
l.Add('AddDataArrayShowHoursStats=1');
l.Add('IncludeInternalLinksInOriginSection=0');
l.Add('MaxNbOfDomain = 10');
l.Add('MinHitDomain  = 1');
l.Add('MaxNbOfHostsShown = 10');
l.Add('MinHitHost    = 1');
l.Add('MaxNbOfLoginShown = 10');
l.Add('MinHitLogin   = 1');
l.Add('MaxNbOfRobotShown = 10');
l.Add('MinHitRobot   = 1');
l.Add('MaxNbOfPageShown = 10');
l.Add('MinHitFile    = 1');
l.Add('MaxNbOfOsShown = 10');
l.Add('MinHitOs      = 1');
l.Add('MaxNbOfBrowsersShown = 10');
l.Add('MinHitBrowser = 1');
l.Add('MaxNbOfScreenSizesShown = 5');
l.Add('MinHitScreenSize = 1');
l.Add('MaxNbOfWindowSizesShown = 5');
l.Add('MinHitWindowSize = 1');
l.Add('MaxNbOfRefererShown = 10');
l.Add('MinHitRefer   = 1');
l.Add('MaxNbOfKeyphrasesShown = 10');
l.Add('MinHitKeyphrase = 1');
l.Add('MaxNbOfKeywordsShown = 10');
l.Add('MinHitKeyword = 1');
l.Add('MaxNbOfEMailsShown = 20');
l.Add('MinHitEMail   = 1');
l.Add('FirstDayOfWeek=1');
l.Add('ShowFlagLinks=""');
l.Add('ShowLinksOnUrl=1');
l.Add('UseHTTPSLinkForUrl=""');
l.Add('MaxLengthOfShownURL=64');
l.Add('HTMLHeadSection=""');
l.Add('HTMLEndSection=""');
l.Add('Logo="logo.gif"');
l.Add('LogoLink="/statistics.index.php"');
l.Add('BarWidth   = 260');
l.Add('BarHeight  = 90');
l.Add('StyleSheet=""');
l.Add('ExtraTrackedRowsLimit=500');
l.Add('DNSLoOKup=1');
try
logs.Debuglogs('CreateAwstats_mail:: Saving '+AWSTATS_ETC_PATH() +'/awstats.squid.conf');
l.SaveToFile(AWSTATS_ETC_PATH() +'/awstats.squid.conf');
except
logs.Debuglogs('CreateAwstats_mail:: fatal  error while saving '+AWSTATS_ETC_PATH() +'/awstats.squid.conf');
exit;
end;
end;


end;
//#############################################################################
function tawstats.AWSTATS_VERSION():string;
var
    RegExpr,RegExpr2:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    Major,minor,awstats_root:string;
begin
    awstats_root:=AWSTATS_PATH();



    if length(awstats_root)=0 then begin
      logs.Debuglogs('AWSTATS_VERSION::unable to locate awstats.pl');
      exit;
   end;
   
   result:=SYS.GET_CACHE_VERSION('APP_AWSTATS');
if length(result)>0 then exit;
    logs.Debuglogs('AWSTATS_VERSION:: ->'+ awstats_root);

   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(awstats_root);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^\$VERSION="([0-9\.]+)';

   RegExpr2:=TRegExpr.Create;
   RegExpr2.Expression:='^\$REVISION=''\$Revision:\s+([0-9\.]+)';

   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              logs.Debuglogs('AWSTATS_VERSION:: found ->'+ FileDatas.Strings[i] + '(' +RegExpr.Match[1]  + ')' );
              Major:=RegExpr.Match[1];
           end;
           if RegExpr2.Exec(FileDatas.Strings[i]) then begin
              logs.Debuglogs('AWSTATS_VERSION:: found ->'+ FileDatas.Strings[i] + '(' +RegExpr2.Match[1]  + ')' );
              minor:=RegExpr2.Match[1];
           end;
           if length(Major)>0 then begin
                  if length(minor)>0 then begin
                  SYS.SET_CACHE_VERSION('APP_AWSTATS',major + ' rev ' + minor);
                  AWSTATS_VERSION:=major + ' rev ' + minor;
                  FileDatas.Free;
                  RegExpr.Free;
                  RegExpr2.Free;
                  exit;
                  end;
           end;

   end;
                  FileDatas.Free;
                  RegExpr.Free;
                  RegExpr2.Free;
                  SYS.SET_CACHE_VERSION('APP_AWSTATS',major);
                  AWSTATS_VERSION:=major;

end;

//#############################################################################
procedure tawstats.AWSTATS_GENERATE();
var maintool:string;
 EnableAwstatMail:integer;

begin

    maintool:=AWSTATS_PATH();

    if not TryStrToInt(SYS.GET_INFO('EnableAwstatMail'),EnableAwstatMail) then EnableAwstatMail:=0;


   if not  FileExists(maintool) then begin
       logs.Debuglogs('AWSTATS_GENERATE:: unable to locate awstats.pl');
       exit;
   end;


   if EnableAwstatMail>0 then begin
      CreateAwstats_mail();
      if FileExists(AWSTATS_ETC_PATH() +'/awstats.mail.conf') then begin
       logs.Syslogs('awstats was launched for Postfix statistics...');
       logs.OutputCmd(maintool + ' -update -config=mail');
      end;
   end else begin
       logs.Syslogs('Awstats is disabled for Postfix engine, skip it');
   end;
    
    
    if FileExists(AWSTATS_ETC_PATH() +'/awstats.squid.conf') then begin
       logs.OutputCmd(maintool + ' -update -config=squid');
       logs.Syslogs('awstats was launched for squid statistics...');
    end;
       


end;
//#############################################################################
function tawstats.AWSTATS_MAILLOG_CONVERT_PATH_SOURCE():string;
begin
if FileExists('/opt/artica/awstats/tools/maillogconvert.pl') then exit('/opt/artica/awstats/tools/maillogconvert.pl');
if FileExists('/usr/share/doc/awstats/examples/maillogconvert.pl') then exit('/usr/share/doc/awstats/examples/maillogconvert.pl');
if FileExists('/usr/share/awstats/tools/maillogconvert.pl') then exit('/usr/share/awstats/tools/maillogconvert.pl');
if FileExists('/usr/share/doc/packages/awstats/tools/maillogconvert.pl') then exit('/usr/share/doc/packages/awstats/tools/maillogconvert.pl');
end;
//#############################################################################
function tawstats.AWSTATS_SET_PLUGIN(value:string;path:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
begin
   result:='';
   AWSTATS_SET_PLUGIN:='';
   if not FileExists(path) then  begin
      logs.Debuglogs('AWSTATS_SET_PLUGIN:: unable to stat '+path);
      exit;
   end;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(AWSTATS_ETC_PATH());
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^LoadPlugin="' + value + '"';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
                logs.Debuglogs('AWSTATS_SET_PLUGIN:: Plugin ' + value + ' already added in '+path);
                FileDatas.Free;
                RegExpr.Free;
                exit;

           end;

   end;
  logs.Debuglogs('AWSTATS_SET_PLUGIN:: Add Plugin ' + value);
  FileDatas.Add('LoadPlugin="' + value + '"');
  FileDatas.SaveToFile(path);
  FileDatas.Free;
  RegExpr.Free;


end;

//#############################################################################

end.
