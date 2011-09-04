unit toolsversions;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,Geoip;



  type
  ttoolsversions=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableFreeWeb:integer;
     binpath:string;
     apache2ctl_bin:string;
public
    procedure    Free;
    constructor  Create(const zSYS:Tsystem);
    function     GEOIP_VERSION():string;
    function     NMAP_VERSION():string;
    function     GDM_VERSION():string;
    function     GNUPLOT_VERSION():string;
    function     DSTAT_VERSION():string;
    function     WINEXE_VERSION():string;
    function     ATMAIL_VERSION():string;
    function     MYSQLHOTCOPY_VERSION():string;
    function     LXC_VERSION():string;
    function     AWSTATS_VERSION():string;
    function     PIWIGO_VERSION():string;
    function     VBOXGUEST_VERSION():string;
    function     KASPERSKY_UPDATE_UTILITY_VERSION():string;
    function     HAMSTERDB_VERSION():string;
    function     VNSTAT():string;
    function     SNORT_VERSION():string;
    function     EYEOS_VERSION():string;
    function     ZPUSH_VERSION():string;
    function     GREENSQL_VERSION():string;
    function     POWERADMIN_VERSION():string;
    function     IPTACCOUNT_VERSION():string;
    function     AMANDA_VERSION():string;
    function     DRUPAL7_VERSION():string;
    function     DRUSH7_VERSION():string;
    function     APP_MSKTUTIL_VERSION():string;
    function     PIWIK_VERSION():string;
    function     JOOMLA17_VERSION():string;
    function     APP_MOD_PAGESPEED():string;
    function     APP_WORDPRESS():string;
END;

implementation

constructor ttoolsversions.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
end;
//##############################################################################
procedure ttoolsversions.free();
begin
    logs.Free;
end;
//##############################################################################
function ttoolsversions.MYSQLHOTCOPY_VERSION():string;
var
   RegExpr:TRegExpr;
   tmpstr:string;
   path:string;
   l:Tstringlist;
   i:integer;

begin

   result:=SYS.GET_CACHE_VERSION('APP_MYSQLHOTCOPY');

   if length(result)>2 then begin
      if SYS.verbosed then writeln('MYSQLHOTCOPY_VERSION():',result,' from memory');
      exit;
   end;

   path:=SYS.LOCATE_GENERIC_BIN('mysqlhotcopy');
   if length(path)<2 then exit;
   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' >'+tmpstr+' 2>&1');
   RegExpr:=TRegExpr.Create;
   l:=Tstringlist.Create;
   try
      l.LoadFromFile(tmpstr);
   except
      exit;
   end;
     logs.DeleteFile(tmpstr);
     RegExpr.Expression:='Ver\s+([0-9A-Z\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_MYSQLHOTCOPY',result);
end;
//#############################################################################

function ttoolsversions.GEOIP_VERSION():string;
var
   RegExpr:TRegExpr;
   database_path,tempstr:string;
   GeoIP:TGeoIP;
begin

   result:=SYS.GET_CACHE_VERSION('APP_GEOIP');

   if length(result)>2 then begin
      if SYS.verbosed then writeln('APP_GEOIP():',result,' from memory');
      exit;
   end;

   database_path:='/usr/local/share/GeoIP';
   ForceDirectories(database_path);
   RegExpr:=TRegExpr.Create;
   if FileExists(database_path + '/GeoIP.dat') then begin
      GeoIP := TGeoIP.Create(database_path + '/GeoIP.dat');
      tempstr:=GeoIP.GetDatabaseInfo;
      RegExpr.expression:='\s+([0-9]+)\s+';
      try
         if RegExpr.Exec(tempstr) then result:=RegExpr.Match[1];
      finally
      GeoIP.Free;
      RegExpr.free;
      end;
   end;
       SYS.SET_CACHE_VERSION('APP_GEOIP',result);
end;
//#############################################################################
function ttoolsversions.NMAP_VERSION():string;
  var
   RegExpr:TRegExpr;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin

     path:=SYS.LOCATE_GENERIC_BIN('nmap');
     if not FileExists(path) then begin
        logs.Debuglogs('myconf.NMAP_VERSION():: nmap is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_NMAP');
   if length(result)>0 then exit;
   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -V >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='version\s+([0-9A-Z\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_NMAP',result);
end;
//#############################################################################
function ttoolsversions.GDM_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    gdm_bin:string;
    SYS:Tsystem;
    LOGS:Tlogs;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  gdm_bin:=SYS.LOCATE_GENERIC_BIN('gdm');
  if length(gdm_bin)=0 then exit;

     if not FileExists(gdm_bin) then begin
        logs.Debuglogs('myconf.GDM_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_GDM');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  filetemp:=LOGS.FILE_TEMP();
  fpsystem(gdm_bin+' --version >' + filetemp + ' 2>&1');
  if not FileExists(filetemp) then exit;
  RegExpr.Expression:='GDM\s+([0-9\.]+)';
  RegExpr.Exec(LOGS.ReadFromFile(filetemp));
  logs.DeleteFile(filetemp);
  result:=RegExpr.Match[1];
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_GDM',result);

end;
//#########################################################################################
function ttoolsversions.GNUPLOT_VERSION():string;
  var
   RegExpr:TRegExpr;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
   d:boolean;
begin

     d:=false;
     d:=SYS.COMMANDLINE_PARAMETERS('--verbose');

     path:='/usr/bin/gnuplot';
     if not FileExists(path) then begin
        logs.Debuglogs('myconf.GNUPLOT_VERSION():: gnuplot is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_GNUPLOT');
   if length(result)>0 then exit;
   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -V >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='gnuplot\s+([0-9A-Z\.]+)\s+patchlevel\s+([0-9]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1]+'.'+RegExpr.Match[2];
            break;
         end else begin
          if d then writeln('Not match: "',l.Strings[i],'"');
         end;


     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_GNUPLOT',result);
logs.Debuglogs('APP_GNUPLOT:: -> ' + result);
end;
//#############################################################################
function ttoolsversions.DSTAT_VERSION():string;
  var
   RegExpr:TRegExpr;

   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/dstat';
     if not FileExists(path) then begin
        logs.Debuglogs('myconf.DSTAT_VERSION():: dstat is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_DSTAT');
   if length(result)>0 then exit;
   tmpstr:=path;



     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='VERSION\s+=.+?([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_DSTAT',result);
logs.Debuglogs('APP_DSTAT:: -> ' + result);
end;
//#############################################################################
function ttoolsversions.WINEXE_VERSION():string;
  var
   RegExpr:TRegExpr;

   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/winexe';
     if not FileExists(path) then begin
        logs.Debuglogs('myconf.WINEXE_VERSION():: dstat is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_WINEXE');
   if length(result)>1 then exit;
   tmpstr:=logs.FILE_TEMP();

   fpsystem(path+' -v >' + tmpstr+' 2>&1');



     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='winexe version\s+([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_WINEXE',result);
end;
//#############################################################################
function ttoolsversions.ATMAIL_VERSION():string;
 var
    RegExpr:TRegExpr;
    l:TstringList;
    i:integer;
    D:boolean;

begin
D:=false;
if paramStr(1)='--atopenmail' then D:=True;
  if Not FileExists('/usr/share/artica-postfix/mail/libs/Atmail/Config.php') then begin
     logs.Debuglogs('ATMAIL_VERSION() unable to stat /usr/share/artica-postfix/mail/libs/Atmail/Config.php');
     exit;
  end;
      result:=SYS.GET_CACHE_VERSION('APP_ATMAIL');
   if length(result)>1 then exit;

  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='version''\s+=>.+?([0-9\.]+)';
  if D then writeln('Pattern=' ,RegExpr.Expression);
  l:=TstringList.Create;
  L.LoadFromFile('/usr/share/artica-postfix/mail/libs/Atmail/Config.php');
  for i:=0 to l.Count-1 do begin

   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
  end;
  l.free;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_ATMAIL',result);
end;
//#############################################################################
function ttoolsversions.AWSTATS_VERSION():string;
 var
    RegExpr:TRegExpr;
    l:TstringList;
    i:integer;
    binpath:string;
    perlpath:string;
    tmpstr:string;
begin

perlpath:=SYS.LOCATE_PERL_BIN();
binpath:=sys.LOCATE_AWSTATS_BIN_PATH();
if length(binpath)=0 then exit;
result:=SYS.GET_CACHE_VERSION('APP_AWSTATS');
if length(result)>1 then exit;

  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='awstats\s+([0-9\.]+)\s+';
  tmpstr:=logs.FILE_TEMP();
  fpsystem(perlpath+' '+binpath+' -h >'+tmpstr+' 2>&1');

  l:=TstringList.Create;
  L.LoadFromFile(tmpstr);
  logs.DeleteFile(tmpstr);
  for i:=0 to l.Count-1 do begin

   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
  end;
  l.free;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_AWSTATS',result);
end;
//#############################################################################
function ttoolsversions.HAMSTERDB_VERSION():string;
 var
    RegExpr:TRegExpr;
    l:TstringList;
    i:integer;
    binpath:string;
    perlpath:string;
    tmpstr:string;
begin


binpath:=sys.LOCATE_GENERIC_BIN('ham_info');
if length(binpath)=0 then exit;
result:=SYS.GET_CACHE_VERSION('APP_HAMSTERDB');
if length(result)>1 then exit;

  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='hamsterdb\s+([0-9\.]+)\s+';
  tmpstr:=logs.FILE_TEMP();
  fpsystem(perlpath+' '+binpath+' -h >'+tmpstr+' 2>&1');

  l:=TstringList.Create;
  L.LoadFromFile(tmpstr);
  logs.DeleteFile(tmpstr);
  for i:=0 to l.Count-1 do begin

   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
  end;
  l.free;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_HAMSTERDB',result);
end;
//#############################################################################


function ttoolsversions.PIWIGO_VERSION():string;
 var
    RegExpr:TRegExpr;
    l:TstringList;
    i:integer;
    binpath:string;
    perlpath:string;
    tmpstr:string;
begin

perlpath:=SYS.LOCATE_PERL_BIN();
binpath:='/usr/local/share/artica/piwigo_src/include/constants.php';
if not FileExists(binpath) then exit;
result:=SYS.GET_CACHE_VERSION('APP_PIWIGO');
if length(result)>1 then exit;

  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='define.+?PHPWG_VERSION.+?([0-9\.]+)';


  l:=TstringList.Create;
  L.LoadFromFile(binpath);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
  end;
  l.free;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_PIWIGO',result);
end;
//############################################################################
function ttoolsversions.VBOXGUEST_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    gdm_bin:string;
    SYS:Tsystem;
    LOGS:Tlogs;

begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  gdm_bin:=SYS.LOCATE_GENERIC_BIN('VBoxService');
  if length(gdm_bin)=0 then exit;

     if not FileExists(gdm_bin) then begin
        logs.Debuglogs('ttoolsversions.VBOXGUEST_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_VBOXADDITIONS');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  filetemp:=LOGS.FILE_TEMP();
  fpsystem(gdm_bin+' -V >' + filetemp + ' 2>&1');
  if not FileExists(filetemp) then exit;
  RegExpr.Expression:='VBoxService:\s+([0-9\.]+)';
  RegExpr.Exec(LOGS.ReadFromFile(filetemp));
  logs.DeleteFile(filetemp);
  result:=RegExpr.Match[1];
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_VBOXADDITIONS',result);

end;
//#########################################################################################
function ttoolsversions.KASPERSKY_UPDATE_UTILITY_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:='/opt/kaspersky/UpdateUtility/UpdateUtility-Console';

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.KASPERSKY_UPDATE_UTILITY_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_KASPERSKY_UPDATE_UTILITY');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  filetemp:=LOGS.FILE_TEMP();
  fpsystem(binpath+' -h >' + filetemp + ' 2>&1');
  if not FileExists(filetemp) then exit;
  RegExpr.Expression:='Update utility v\.([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(binpath);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end;
  end;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_KASPERSKY_UPDATE_UTILITY',result);

end;
//#########################################################################################
function ttoolsversions.VNSTAT():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:=SYS.LOCATE_GENERIC_BIN('vnstatd');

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.VNSTAT():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_VNSTAT');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  filetemp:=LOGS.FILE_TEMP();
  fpsystem(binpath+' -v >' + filetemp + ' 2>&1');
  if not FileExists(filetemp) then exit;
  RegExpr.Expression:='vnStat daemon\s+([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(filetemp);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.VNSTAT():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_VNSTAT',result);

end;
//#########################################################################################
function ttoolsversions.LXC_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:=SYS.LOCATE_GENERIC_BIN('lxc-version');

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.LXC_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_LXC');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  filetemp:=LOGS.FILE_TEMP();
  fpsystem(binpath+' >' + filetemp + ' 2>&1');
  if not FileExists(filetemp) then exit;
  RegExpr.Expression:='\s+([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(filetemp);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.LXC_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_LXC',result);

end;
//#########################################################################################
function ttoolsversions.SNORT_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:=SYS.LOCATE_GENERIC_BIN('snort');

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.SNORT_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_SNORT');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  filetemp:=LOGS.FILE_TEMP();
  fpsystem(binpath+' -V >' + filetemp + ' 2>&1');
  if not FileExists(filetemp) then exit;
  RegExpr.Expression:='\s+Version\s+([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(filetemp);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.APP_SNORT():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  SYS.SET_CACHE_VERSION('APP_SNORT',result);

end;
//########################################################################################
function ttoolsversions.EYEOS_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:='/usr/local/share/artica/eyeos_src/install/steps/configuration.php';

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.EYEOS_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_EYEOS');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='EYE_VERSION.+?([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(binpath);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.EYEOS_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('APP_EYEOS',result);

end;
//########################################################################################
function ttoolsversions.ZPUSH_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:='/usr/share/z-push/version.php';

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.ZPUSH_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_Z_PUSH');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='zpush_version.+?([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(binpath);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.ZPUSH_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('APP_Z_PUSH',result);

end;
//########################################################################################
function ttoolsversions.POWERADMIN_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:='/usr/share/poweradmin/inc/version.inc.php';

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.POWERADMIN_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_POWERADMIN');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='VERSION.+?([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(binpath);
  for i:=0 to l.Count-1 do begin
   if length(trim(l.Strings[i]))=0 then continue;
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.POWERADMIN_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('APP_POWERADMIN',result);

end;
//########################################################################################
function ttoolsversions.PIWIK_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;
  binpath:='/usr/share/piwik/core/Version.php';

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.PIWIK_VERSION():: not installed');
        exit;
     end;

  result:=SYS.GET_CACHE_VERSION('APP_PIWIK');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='VERSION.+?([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile(binpath);
  for i:=0 to l.Count-1 do begin
   if length(trim(l.Strings[i]))=0 then continue;
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.PIWIK_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('APP_PIWIK',result);

end;
//########################################################################################

function ttoolsversions.GREENSQL_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;

begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;

  binpath:=SYS.LOCATE_GENERIC_BIN('greensql-fw');

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.GREENSQL_VERSION():: not installed');
        exit;
     end;
     if not FileExists('/usr/share/greensql-console/config.php') then begin
        logs.Debuglogs('ttoolsversions.GREENSQL_VERSION():: /usr/share/greensql-console/config.php no such file');
        exit;
     end;


  result:=SYS.GET_CACHE_VERSION('APP_GREENSQL');
  if length(result)>2 then exit;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='version.+?([0-9\.]+)';
    l:=TstringList.Create;
    L.LoadFromFile('/usr/share/greensql-console/config.php');
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.GREENSQL_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('APP_GREENSQL',result);

end;
//########################################################################################
function ttoolsversions.AMANDA_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;

begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;

  binpath:=SYS.LOCATE_GENERIC_BIN('amadmin');

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.AMANDA_VERSION():: not installed');
        exit;
     end;
     if not FileExists('/etc/amanda/DailySet1/amanda.conf') then begin
        fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.amanda.php --build');
     end;

  filetemp:=logs.FILE_TEMP();
  result:=SYS.GET_CACHE_VERSION('APP_AMANDA');
  if length(result)>2 then exit;
  fpsystem(binpath+' DailySet1 version >'+filetemp +' 2>&1');
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='VERSION=".+?([0-9\.]+)';
  l:=TstringList.Create;
  L.LoadFromFile(filetemp);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.AMANDA_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('AMANDA_VERSION',result);

end;
//########################################################################################
function ttoolsversions.APP_MSKTUTIL_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;

begin
  SYS:=Tsystem.Create();
  logs:=Tlogs.Create;

  binpath:=SYS.LOCATE_GENERIC_BIN('msktutil');

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.APP_MSKTUTIL_VERSION():: not installed');
        exit;
     end;
  filetemp:=logs.FILE_TEMP();
  result:=SYS.GET_CACHE_VERSION('APP_MSKTUTIL');
  if length(result)>2 then exit;
  fpsystem(binpath+' --version >'+filetemp +' 2>&1');
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='version.+?([0-9\.]+)';
  l:=TstringList.Create;
  L.LoadFromFile(filetemp);
  for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
      result:=RegExpr.Match[1];
      break;
   end else begin
      logs.Debuglogs('ttoolsversions.APP_MSKTUTIL_VERSION():: Not found "'+l.Strings[i]+'"');
   end;
  end;
  RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('APP_MSKTUTIL',result);

end;
//########################################################################################
function ttoolsversions.IPTACCOUNT_VERSION():string;
 var
    RegExpr:TRegExpr;
    filetemp:string;
    binpath:string;
    l:TstringList;
    i:integer;
    D:Boolean;
    cmd:string;
begin
 D:=false;
  SYS:=Tsystem.Create();
  if SYS.COMMANDLINE_PARAMETERS('--verbose') then D:=true;
  logs:=Tlogs.Create;
  binpath:=SYS.LOCATE_GENERIC_BIN('iptaccount');

     if not FileExists(binpath) then begin
        logs.Debuglogs('ttoolsversions.IPTACCOUNT_VERSION():: not installed');
        exit;
     end;
  result:=SYS.GET_CACHE_VERSION('APP_IPTACCOUNT');
  if not D then if length(trim(result))>2 then exit;

  filetemp:=logs.FILE_TEMP();
  cmd:=binpath+' -u >'+filetemp+' 2>&1';
  fpsystem(cmd);
  RegExpr:=TRegExpr.Create;

  l:=TstringList.Create;
  l.LoadFromFile(filetemp);
  logs.DeleteFile(filetemp);
  if D then writeln(cmd,' ',l.Count,' lines');
  for i:=0 to l.Count-1 do begin
   RegExpr.Expression:='accounting tool v([0-9\.]+)';
   if RegExpr.Exec(l.Strings[i]) then begin
      if SYS.verbosed then writeln(l.Strings[i],' FOUND');
      result:=RegExpr.Match[1];
   end else begin
       if D then writeln(l.Strings[i],' no match accounting tool v([0-9]+)');
   end;
   RegExpr.Expression:='get_handle_usage failed';
   if RegExpr.Exec(l.Strings[i]) then begin
         if D then writeln(l.Strings[i],' FOUND -> get_handle_usage failed -> version ',result,' will be null');
        result:='';
  end else begin
       if D then writeln(l.Strings[i],' no match accounting tool v([0-9]+)');
  end;
  end;
    RegExpr.free;
  l.free;
  SYS.SET_CACHE_VERSION('APP_IPTACCOUNT',result);

end;
//########################################################################################
function ttoolsversions.DRUPAL7_VERSION():string;
var
   l:Tstringlist;
   tmpstr:string;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
begin

     D:=false;
     result:=SYS.GET_CACHE_VERSION('APP_DRUPAL7');
     if length(result)>2 then exit;
     D:=SYS.COMMANDLINE_PARAMETERS('--drupal');
     tmpstr:='/usr/share/drupal7/modules/system/system.info';



     if not FileExists(tmpstr) then begin
        if D then writeln('Unable to stat '+tmpstr);
        exit;
     end;
l:=Tstringlist.Create;
l.LoadFromFile(tmpstr);

if D then writeln('File:',tmpstr);

RegExpr:=TRegExpr.Create;
if D then writeln('Lines:',l.Count);
RegExpr.Expression:='version = "([0-9\.]+)';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
    end;
end;
     SYS.SET_CACHE_VERSION('APP_DRUPAL7',result);
    l.free;
    RegExpr.free;
end;
//#####################################################################################
function ttoolsversions.DRUSH7_VERSION():string;
var
   l:Tstringlist;
   tmpstr,binpath:string;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
begin

     D:=false;
     result:=SYS.GET_CACHE_VERSION('APP_DRUSH7');
     if length(result)>2 then exit;
     binpath:=SYS.LOCATE_GENERIC_BIN('drush7');
     if not Fileexists(binpath) then exit;

     tmpstr:=logs.FILE_TEMP();
     fpsystem(binpath+' --version >'+tmpstr+' 2>&1');




     if not FileExists(tmpstr) then begin
        if D then writeln('Unable to stat '+tmpstr);
        exit;
     end;
l:=Tstringlist.Create;
l.LoadFromFile(tmpstr);

if D then writeln('File:',tmpstr);

RegExpr:=TRegExpr.Create;
if D then writeln('Lines:',l.Count);
RegExpr.Expression:='version\s+([0-9\.]+)';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
    end;
end;
     SYS.SET_CACHE_VERSION('APP_DRUSH7',result);
    l.free;
    RegExpr.free;
end;
//#####################################################################################
function ttoolsversions.JOOMLA17_VERSION():string;
var
   l:Tstringlist;
   tmpstr,binpath:string;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
begin
if not FileExists('/usr/local/share/artica/joomla17_src/includes/version.php') then exit;
result:=SYS.GET_CACHE_VERSION('APP_JOOMLA17');
if length(result)>2 then exit;
l:=Tstringlist.Create;
l.LoadFromFile('/usr/local/share/artica/joomla17_src/includes/version.php');
RegExpr:=TRegExpr.Create;
if D then writeln('Lines:',l.Count);
RegExpr.Expression:='public.+?RELEASE.+?([0-9\.]+)';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
    end;
end;
     SYS.SET_CACHE_VERSION('APP_JOOMLA17',result);
    l.free;
    RegExpr.free;
SYS.SET_CACHE_VERSION('APP_JOOMLA17',result);
logs.Debuglogs('APP_JOOMLA17:: -> ' + result);
end;
//##############################################################################
function ttoolsversions.APP_MOD_PAGESPEED():string;
var
   l:Tstringlist;
   tmpstr,binpath:string;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
begin
if not FileExists(SYS.LOCATE_APACHE_MODULES_PATH()+'/mod_pagespeed.so') then exit;
result:=SYS.GET_CACHE_VERSION('APP_MOD_PAGESPEED');
if length(result)>2 then exit;
tmpstr:=logs.FILE_TEMP();
RegExpr:=TRegExpr.Create;
if FileExists('/usr/bin/dpkg') then begin
   fpsystem('/usr/bin/dpkg -l >'+tmpstr+' 2>&1');
   RegExpr.Expression:='mod-pagespeed.*?\s+([0-9\.a-z\-]+)';

end else begin
    if FileExists('/bin/rpm') then begin
       fpsystem('/bin/rpm -qa >'+tmpstr+' 2>&1');
       RegExpr.Expression:='mod-pagespeed.*?-([0-9\.a-z\-]+)';
    end;
end;
if not FileExists(tmpstr) then exit;

l:=Tstringlist.Create;
l.LoadFromFile(tmpstr);

if D then writeln('Lines:',l.Count);

for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
    end;
end;
     SYS.SET_CACHE_VERSION('APP_MOD_PAGESPEED',result);
    l.free;
    RegExpr.free;
SYS.SET_CACHE_VERSION('APP_MOD_PAGESPEED',result);
logs.Debuglogs('APP_MOD_PAGESPEED:: -> ' + result);
end;
//##############################################################################
function ttoolsversions.APP_WORDPRESS():string;
var
   l:Tstringlist;
   tmpstr,binpath:string;
   RegExpr:TRegExpr;
   i:integer;
   D:boolean;
   fileToCheck:string;
begin
fileToCheck:='/usr/local/share/artica/wordpress_src/wp-includes/version.php';
if not FileExists(fileToCheck) then exit;
result:=SYS.GET_CACHE_VERSION('APP_WORDPRESS');
if length(result)>2 then exit;
l:=Tstringlist.Create;
l.LoadFromFile(fileToCheck);
RegExpr:=TRegExpr.Create;
if D then writeln('Lines:',l.Count);
RegExpr.Expression:='wp_version.+?([0-9\.]+)';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=RegExpr.Match[1];
       break;
    end;
end;
     SYS.SET_CACHE_VERSION('APP_WORDPRESS',result);
    l.free;
    RegExpr.free;
SYS.SET_CACHE_VERSION('APP_WORDPRESS',result);
logs.Debuglogs('APP_WORDPRESS:: -> ' + result);
end;
//##############################################################################


end.
