unit debian_class;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,artica_tcp,squid;

type

  networks_settings=record
        ip:string;
        netmask:string;
        gateway:string;
        dns1:string;
        dns2:string;
  end;

  type
  tdebian=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     squid:Tsquid;
     function ARTICA_VERSION():string;

public
    procedure   Free;
    constructor Create;
    procedure ARTICA_CD_SOURCES_LIST();
    function  LOAD_INTERFACES():networks_settings;
    procedure remove_bip();
    procedure linuxlogo();
    function  keyboard_language():string;
    procedure change_xorg_option(key:string;value:string);
    procedure KeyboardTofr();
    procedure sensors();
    function  ISDebian():boolean;
END;

implementation

constructor tdebian.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       squid:=Tsquid.Create;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tdebian.free();
begin
    logs.Free;
    SYS.Free;
    squid.free;
end;
//##############################################################################
procedure tdebian.ARTICA_CD_SOURCES_LIST();
var
   l:TstringList;
begin

if not FileExists('/etc/artica-postfix/FROM_ISO') then begin
   logs.Debuglogs('ARTICA_CD_SOURCES_LIST():: /etc/artica-postfix/FROM_ISO does not exists, this is not a system from Artica-ISO');
   exit;
end;


if FileExists('/etc/artica-postfix/sources.list.upddated') then begin
   logs.Debuglogs('ARTICA_CD_SOURCES_LIST():: /etc/artica-postfix/sources.list.upddated exists... Check keys');
   exit;
end;

l:=TstringList.Create;
l.Add('deb http://ftp.debian.org/debian/ lenny main');
l.Add('deb-src http://ftp.debian.org/debian/ lenny main');
l.Add('');
l.Add('deb http://security.debian.org/ lenny/updates main contrib');
l.Add('deb-src http://security.debian.org/ lenny/updates main contrib');
try
logs.Debuglogs('ARTICA_CD_SOURCES_LIST():: Modify sources.list');
l.SaveToFile('/etc/apt/sources.list');
l.SaveToFile('/etc/artica-postfix/sources.list.upddated');
finally
l.free;
end;
//fpsystem('DEBIAN_FRONTEND=noninteractive /usr/bin/apt-get -o Dpkg::Options::="--force-confnew" update');
linuxlogo();
remove_bip();
//fpsystem('DEBIAN_FRONTEND=noninteractive /usr/bin/apt-get -o Dpkg::Options::="--force-confnew" install -f --yes --force-yes');
//fpsystem('DEBIAN_FRONTEND=noninteractive /usr/bin/apt-get -o Dpkg::Options::="--force-confnew" install awstats --yes --force-yes');


end;
//##############################################################################
function tdebian.LOAD_INTERFACES():networks_settings;
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
   b:boolean;
   t:networks_settings;
   s:TstringList;
begin
   if not FileExists('/etc/network/interfaces') then exit;
   l:=TstringList.Create;
   l.LoadFromFile('/etc/network/interfaces');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^iface eth0 inet static';
   
   b:=false;
   
   for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then b:=true;
        if b then begin
             RegExpr.Expression:='address\s+([0-9\.]+)';
             if RegExpr.Exec(l.Strings[i]) then t.ip:=RegExpr.Match[1];
             RegExpr.Expression:='netmask\s+([0-9\.]+)';
             if RegExpr.Exec(l.Strings[i]) then t.netmask:=RegExpr.Match[1];
             RegExpr.Expression:='gateway\s+([0-9\.]+)';
             if RegExpr.Exec(l.Strings[i]) then t.gateway:=RegExpr.Match[1];
             RegExpr.Expression:='iface';
             if RegExpr.Exec(l.Strings[i]) then break;
        end;
        
   end;
   
   l.Clear;
   if FileExists('/etc/resolv.conf') then begin
       l.LoadFromFile('/etc/resolv.conf');
       s:=TstringList.Create;
       RegExpr.Expression:='^nameserver\s+(.+)';
       for i:=0 to l.Count-1 do begin
           if RegExpr.Exec(l.Strings[i]) then s.Add(RegExpr.Match[1]);
       end;
   end;
   
   if s.Count>0 then t.dns1:=s.Strings[0];
   if s.Count>1 then t.dns2:=s.Strings[1];
   
    s.free;
    result:=t;
    RegExpr.free;
    l.free;
end;
//##############################################################################
procedure tdebian.remove_bip();
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin
  if Fileexists('/etc/artica-postfix/remove.the.bip') then exit;
  if not FileExists('/etc/modprobe.d/blacklist') then exit;
  l:=TstringList.Create;
  l.LoadFromFile('/etc/modprobe.d/blacklist');
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='blacklist\s+pcspkr';
  
  for i:=0 to l.Count-1 do begin
     if RegExpr.Exec(l.Strings[i]) then begin
         l.free;
         RegExpr.free;
         exit;
     end;
  
  end;
  
  l.Add('blacklist pcspkr');
  l.SaveToFile('/etc/modprobe.d/blacklist');
  logs.Debuglogs('remove_bip():: success remove the biiiiiiiiip !');
  fpsystem('touch /etc/artica-postfix/remove.the.bip');
  l.free;
  RegExpr.free;
end;
//#############################################################################
procedure tdebian.linuxlogo();
var
tcp:ttcp;
l:TstringList;
begin
tcp:=ttcp.Create;
l:=TstringList.Create;
if not FileExists('/etc/artica-postfix/FROM_ISO') then exit;
if not FileExists('/var/run/linuxlogo/issue.linuxlogo') then exit;
l.Add('Welcome on artica-postfix version ' + ARTICA_VERSION() + ' (installed by artica-iso)');
l.Add('if you seen this screen, you should enter "root" as login and "artica" or "artica" as password');
l.Add('For your information, you can access to the web Interface on :');
l.add('');
l.add('https://' + tcp.GetIPAddressOfInterface('eth0') + ':9000');
l.add('Just after login...');
l.add('');
l.add('Have fun....');
l.add('                  David Touzeau');
l.add('');
l.add('');
try
l.SaveToFile('/var/run/linuxlogo/issue.linuxlogo');
l.SaveToFile('/var/run/linuxlogo/issue.linuxlogo.ascii');
finally
l.free;
tcp.free;
end;
end;
//#############################################################################
function tdebian.ARTICA_VERSION():string;
var
   l:string;
   F:TstringList;

begin
   l:=artica_path + '/VERSION';
   if not FileExists(l) then exit('0.00');
   F:=TstringList.Create;
   F.LoadFromFile(l);
   result:=trim(F.Text);
   F.Free;
end;
//#############################################################################
function tdebian.keyboard_language();
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin
    if not FileExists('/etc/X11/xorg.conf') then exit();
    l:=TstringList.Create;
    l.LoadFromFile('/etc/X11/xorg.conf');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Option\s+"XkbLayout"\s+"(.+?)"';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
                result:=RegExpr.Match[1];
                break;
         end;
    end;
    
    l.free;
    RegExpr.free;

end;
//#############################################################################
procedure tdebian.keyboardTofr();
begin
change_xorg_option('XkbModel','pc105');
change_xorg_option('XkbLayout','fr');
change_xorg_option('Xkbvariant','latin9');
end;
//#############################################################################
procedure tdebian.change_xorg_option(key:string;value:string);
var
   RegExpr:TRegExpr;
   l:TstringList;
   i:integer;
begin
    if not FileExists('/etc/X11/xorg.conf') then exit();
    l:=TstringList.Create;
    l.LoadFromFile('/etc/X11/xorg.conf');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Option\s+"'+key+'"\s+"';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
                l.Strings[i]:='Option'+chr(9)+'"'+key+'"'+chr(9)+'"'+value+'"';
                try
                l.SaveToFile('/etc/X11/xorg.conf');
                finally

                end;
                break;
         end;
    end;

    l.free;
    RegExpr.free;

end;
//#############################################################################
procedure tdebian.sensors();
var
   tempfile:string;
   i:integer;
   RegExpr:TRegExpr;
   l:TstringList;
   fa,D:boolean;
   s:TstringList;
begin
if not FileExists(SYS.LOCATE_sensors_detect()) then begin
   logs.Debuglogs('sensors:: Unable to locate sensors-detect...');
   exit;
end;
tempfile:=logs.FILE_TEMP();
fa:=false;
D:=logs.COMMANDLINE_PARAMETERS('--debug');
if d then writeln('Export to ' + tempfile);
fpsystem('/bin/echo "YES"|'+SYS.LOCATE_sensors_detect() +' >'+tempfile +' 2>&1');
if not FileExists(tempfile) then exit;
   l:=TstringList.Create;
   l.LoadFromFile(tempfile);
   logs.DeleteFile(tempfile);
   s:=TstringList.Create;
   RegExpr:=TRegExpr.Create;

   for i:=0 to l.Count-1 do begin
       RegExpr.Expression:='#----cut here----';
       if not fa then begin
          RegExpr.Expression:='#----cut here----';
          if RegExpr.Exec(l.Strings[i]) then begin
             fa:=true;
             if D then writeln('found ' + RegExpr.Expression);
          end;
       end;

       if fa then begin
           RegExpr.Expression:='^Do you want';
             if RegExpr.Exec(l.Strings[i]) then break;


            RegExpr.Expression:='^([a-zA-Z0-9\.\-_]+)';
            if RegExpr.Exec(l.Strings[i]) then begin
               s.Add(RegExpr.Match[1]);
               if D then writeln('found ' + RegExpr.Match[1]);
            end else begin
                if D then writeln('not found ' + l.Strings[i]);
            end;



       end;
   end;

for i:=0 to s.Count -1 do begin
     sys.SYSTEM_ADD_MODULE(s.Strings[i]);
     fpsystem('/sbin/modprobe ' + s.Strings[i]);
end;

end;
//##############################################################################
function tdebian.ISDebian():boolean;
begin
  if not FileExists('/etc/debian_version') then exit(false);
  exit(true);
end;




end.
