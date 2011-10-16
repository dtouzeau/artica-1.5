unit logon;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,lighttpd,tcpip,openldap;



  type
  tlogon=class


private
     LOGS:Tlogs;
     D:boolean;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;                      
     ldap:Topenldap;
public
    procedure   Free;
    constructor Create();
    procedure Menu();
    procedure webaccess();
    procedure credentials();
    procedure ChangeIP();
    procedure ChangeRootpwd();

END;

implementation

constructor tlogon.Create();
begin
       forcedirectories('/etc/artica-postfix');
       SYS:=Tsystem.Create;
       LOGS:=tlogs.Create();
       D:=LOGS.COMMANDLINE_PARAMETERS('debug');




       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tlogon.free();
begin
    logs.Free;
end;
//##############################################################################
procedure tlogon.Menu();
var
   a:string;
   lighttp:Tlighttpd;
   lightstatus:string;
   port,uris:string;
   slighttpd:Tlighttpd;
   SYSURIS:Tsystem;
   ISOCanDisplayUserNamePassword,ISOCanChangeIP,ISOCanReboot,ISOCanShutDown,ISOCanChangeRootPWD,ISOCanChangeLanguage:integer;
begin

logs.Debuglogs('Initialize menu....');
SYS:=Tsystem.Create;
if not TryStrToInt(SYS.GET_INFO('ISOCanDisplayUserNamePassword'),ISOCanDisplayUserNamePassword) then ISOCanDisplayUserNamePassword:=1;
if not TryStrToInt(SYS.GET_INFO('ISOCanChangeIP'),ISOCanChangeIP) then ISOCanChangeIP:=1;
if not TryStrToInt(SYS.GET_INFO('ISOCanReboot'),ISOCanReboot) then ISOCanReboot:=1;
if not TryStrToInt(SYS.GET_INFO('ISOCanShutDown'),ISOCanShutDown) then ISOCanShutDown:=1;
if not TryStrToInt(SYS.GET_INFO('ISOCanChangeRootPWD'),ISOCanChangeRootPWD) then ISOCanChangeRootPWD:=1;
if not TryStrToInt(SYS.GET_INFO('ISOCanChangeLanguage'),ISOCanChangeLanguage) then ISOCanChangeLanguage:=1;
logs.Debuglogs('Initialize menu done....');
fpsystem('clear');
try
   lighttp:=Tlighttpd.Create(SYS);
except
   logs.Debuglogs(' Tlighttpd.Create crashed');
end;
writeln('########################################################');
writeln('###                                                  ###');
writeln('###             Artica version ' + SYS.ARTICA_VERSION()+'');
writeln('###                                                  ###');
writeln('########################################################');
writeln('');

   if not SYS.PROCESS_EXIST(lighttp.LIGHTTPD_PID()) then begin
      lightstatus:='WARNING !!! lighttpd is stopped !';
      fpsystem('/etc/init.d/artica-postfix start apache >/dev/null 2>&1 &');

   end;
//     lightstatus:='lighttpd daemon is running using '+IntToStr(SYS.PROCESS_MEMORY(lighttp.LIGHTTPD_PID()))+' Kb memory';
       slighttpd:=Tlighttpd.Create(SYS);
       port:=slighttpd.LIGHTTPD_LISTEN_PORT();

       SYSURIS:=Tsystem.Create();
       try
       uris:=SYSURIS.txt_uris(port);
      if not SYS.COMMANDLINE_PARAMETERS('--verbose') then begin
         if length(uris)<5 then begin
            ChangeIP();
            SYSURIS:=Tsystem.Create();
            uris:=SYSURIS.txt_uris(port);
       end;
      end;

       except
       writeln('SYSURIS.txt_uris crashed');
       logs.Debuglogs('SYSURIS.txt_uris crashed');
       end;
       writeln(uris);
       writeln('');
logs.Debuglogs('display menu....');

writeln('Access to the Artica Web interface');
writeln('**********************************************');
writeln('');
writeln('Here it is uris you can type on your web browser in order');
writeln('to access to the front-end.');
writeln('');
writeln(uris);
writeln('');
writeln('Menu :');
writeln('');
writeln('[W]..... How to access to the Artica Web interface ?');
writeln('[U]..... Global Administrator Username  & password');
writeln('[N]..... Modify eth0 interface');
writeln('[L]..... Modify console language');
writeln('[P]..... Modify root password');
writeln('[C]..... Synchronize settings & remove cache');
writeln('[R]..... Reboot');
writeln('[S]..... Shutdown');


if FileExists('/usr/sbin/dpkg-reconfigure') then begin
   // writeln('[L]..... Configure the system language');
end;

writeln('[Q]..... Exit and enter to the system');
writeln('');
writeln('');
writeln(lightstatus);
writeln('Your command: ');
readln(a);

a:=UpperCase(a);

if a='L' then begin
   if ISOCanChangeLanguage=0 then begin
      Writeln('Operation not permitted');
      Menu();
      exit;
   end;
   fpsystem('/usr/sbin/dpkg-reconfigure locales');
   Menu();
   exit;
end;

if a='N' then begin
   if ISOCanReboot=0 then begin
      Writeln('Operation not permitted');
      Menu();
      exit;
   end;
   ChangeIP();
   Menu();
   exit;
end;

if a='P' then begin
   if ISOCanChangeRootPWD=0 then begin
      Writeln('Operation not permitted');
      Menu();
      exit;
   end;
   ChangeRootpwd();
   Menu();
   exit;
end;


if a='W' then begin
   webaccess();
   Menu();
   exit;
end;

if a='R' then begin
   if ISOCanChangeIP=0 then begin
      Writeln('Operation not permitted');
      Menu();
      exit;
   end;

  fpsystem(sys.LOCATE_GENERIC_BIN('reboot'));
  exit;
end;


if a='U' then begin
   if ISOCanDisplayUserNamePassword=0 then begin
      Writeln('Operation not permitted');
      Menu();
      exit;
   end;
   credentials();
   Menu();
   exit;
end;

if a='S' then begin
   if ISOCanShutDown=0 then begin
      Writeln('Operation not permitted');
      Menu();
      exit;
   end;
   fpsystem('init 0');
   exit;
end;

if a='L' then begin
   fpsystem('sudo /usr/sbin/dpkg-reconfigure locales');
   fpsystem('sudo dpkg-reconfigure console-data');
   Menu();
   exit;
end;

if a='C' then begin
   fpsystem('/usr/share/artica-postfix/bin/process1 --force');
   fpsystem('/bin/rm -f /usr/share/artica-postfix/ressources/logs/cache/*');
   fpsystem('/bin/rm -rf /usr/share/artica-postfix/ressources/logs/web/cache/*');
   fpsystem('/bin/rm -f /usr/share/artica-postfix/ressources/logs/jGrowl-new-versions.txt');
   fpsystem('/bin/rm -f /etc/artica-postfix/versions.cache');
   fpsystem('/bin/rm -f /usr/share/artica-postfix/ressources/logs/global.versions.conf');
   fpsystem('/usr/share/artica-postfix/bin/artica-install --write-versions');
   fpsystem('/usr/share/artica-postfix/bin/process1 --force fsjfklshkjfhkfsh');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.shm.php --remove');
   fpsystem('/etc/init.d/artica-postfix restart artica-status &');
   fpsystem('rm -rf /usr/share/artica-postfix/ressources/web/logs/*.cache');
   Menu();
   exit;
end;

if a='Q' then begin
   fpsystem('/bin/login.old');
   halt(0);
end;

 Menu();

end;
//##############################################################################
procedure tlogon.webaccess();
var
   slighttpd:Tlighttpd;
   ip:ttcpip;
   port,uris:string;
begin

   slighttpd:=Tlighttpd.Create(SYS);
   port:=slighttpd.LIGHTTPD_LISTEN_PORT();
   uris:=SYS.txt_uris(port);
   fpsystem('clear');
   writeln('Access to the Artica Web interface');
   writeln('**********************************************');
   writeln('');
   writeln('Here it is uris you can type on your web browser in order');
   writeln('to access to the front-end.');
   writeln('');
   writeln(uris);
   writeln('[Enter] key to Exit');
   readln();
end;

//##############################################################################
procedure tlogon.credentials();
var
   slighttpd:Tlighttpd;
   port,uris:string;
begin
   ldap:=Topenldap.Create;


   fpsystem('clear');
   writeln('Access to the Artica Web interface');
   writeln('**********************************************');
   writeln('');
   writeln('Once connected to the web front-end, use');
   writeln('following parameters');
   writeln('');
   writeln('Username..................:'+ldap.ldap_settings.admin);
   writeln('Password..................:'+ldap.ldap_settings.password);
   writeln('[Enter] key to Exit');
   readln();
end;
//##############################################################################
procedure tlogon.ChangeRootpwd();
var
   pass1,pass2:string;
begin



   fpsystem('clear');
   writeln('Change the root password');
   writeln('**********************************************');
   writeln('Give the password:');
   readln(pass1);
   if length(pass1)=0 then begin
      writeln('Null root password is not allowed, exiting..');
      writeln('[Enter] key to Exit');
      readln(pass1);
      exit;
   end;

    writeln('retype the password:');
    readln(pass2);

    if pass1<>pass2 then begin
         writeln('Passwords did not match');
         writeln('Press [ENTER] to restart');
         readln(pass2);
         ChangeRootpwd();
         exit;
    end;

    fpsystem('echo "root:'+pass2+'" | chpasswd 2>&1');
    writeln('Updated..');
    writeln('[Enter] key to Exit');
    readln(pass2);
    exit;

end;
//#############################################################################



procedure tlogon.ChangeIP();
var
   IP:string;
   Gateway:string;
   DNS,answer:string;
   NETMASK:string;
   iptcp:ttcpip;
   Gayteway:string;
   perform:string;
   l:Tstringlist;
begin

    iptcp:=ttcpip.Create;
    IP:=iptcp.IP_ADDRESS_INTERFACE('eth0');
    NETMASK:=iptcp.IP_MASK_INTERFACE('eth0');
    Gayteway:=iptcp.IP_LOCAL_GATEWAY('eth0');
    perform:='o';

    if(IP='0.0.0.0') then IP:='172.16.14.135';
    if(Gayteway='0.0.0.0') then Gayteway:='172.16.14.2';
    if length(NETMASK)=0 then NETMASK:='255.255.255.0';
    if length(Gayteway)=0 then Gayteway:='172.16.14.2';

    fpsystem('clear');
    writeln('Network configurator v1.1');
    writeln('By default, the Artica server is set on DHCP Mode');
    writeln('You will change eth0 network settings using static mode');
    writeln('Remember that you can change IP setting trough the web interface');
    writeln('');
    writeln('Give the network address IP of this computer: ['+IP+']');
    readln(answer);
    if length(trim(answer))>3 then begin
       IP:=answer;

    end else begin
       if length(IP)=0 then begin
        ChangeIP();
        exit;
        end;
    end;


    writeln('Give the netmask of this computer:['+NETMASK+']');
    readln(answer);
    if length(trim(answer))>0 then NETMASK:=answer else answer:=NETMASK;


    writeln('Give the gateway ip address for this computer:['+Gayteway+']');
    readln(answer);
    if length(trim(answer))>0 then Gayteway:=answer;


    writeln('Give the First DNS ip address for this computer:['+Gayteway+']');
    readln(answer);
    if length(trim(answer))>0 then DNS:=answer else DNS:=Gayteway;


    writeln('Perform this operation ?(Y/N)');
    readln(answer);

    if length(trim(answer))>0 then perform:=UpperCase(answer) else perform:='Y';

    if perform<>'Y' then begin
        writeln('Choose "'+perform+'"');
        writeln('Operation aborted...[Enter] key to Exit');
        readln(answer);
        exit;
    end;



l:=Tstringlist.Create;
l.add('auto lo');
l.add('iface lo inet loopback');
l.add('auto eth0');
l.add('iface eth0 inet static');
l.add(chr(9)+'address '+IP);
l.add(chr(9)+'gateway '+Gayteway);
l.add(chr(9)+'netmask '+netmask);
if length(DNS)>0 then l.add(chr(9)+'dns-nameservers '+DNS);
writeln('Saving /etc/network/interfaces');
l.SaveToFile('/etc/network/interfaces');
writeln('Shutdown eth0...');
fpsystem('ifdown eth0');
writeln('Starting eth0...');
fpsystem('ifup eth0');
fpsystem('/sbin/ifconfig eth0 '+IP+' netmask '+NETMASK +' up');
     IP:=iptcp.IP_ADDRESS_INTERFACE('eth0');
    NETMASK:=iptcp.IP_MASK_INTERFACE('eth0');
    Gayteway:=iptcp.IP_LOCAL_GATEWAY('eth0');

writeln('New configuration done '+IP+'/'+NETMASK +' Gateway:'+Gayteway);

writeln('Starting Web services....');
fpsystem('/etc/init.d/artica-postfix start apache >/dev/null 2>&1');
writeln('[Enter] key to Exit');
readln(answer);
exit;


end;








end.
