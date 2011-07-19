unit dhcp_server;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,tcpip,
    RegExpr in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';


  type
  tdhcp3=class


private
     LOGS:Tlogs;
     artica_path:string;
     SYS:Tsystem;
     EnableDHCPServer:integer;
     function DAEMON_PID():string;
     function READ_PID():string;
     function PID_PATH():string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  STATUS():string;
    function  BIN_PATH():string;
    procedure START();
    procedure STOP();
    function  VERSION():string;
    function  INIT_PATH():string;
    function  DEFAULT_PATH():string;
    function  CONF_PATH():string;
    procedure ApplyConf();
    procedure RELOAD();
    function FIND_NIC():string;
END;

implementation

constructor tdhcp3.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       EnableDHCPServer:=0;
       SYS:=zSYS;
       
       if not TryStrToInt(SYS.GET_INFO('EnableDHCPServer'),EnableDHCPServer) then EnableDHCPServer:=0;
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tdhcp3.free();
begin
    logs.Free;
end;
//##############################################################################
function tdhcp3.BIN_PATH():string;
begin
    if FileExists('/usr/sbin/dhcpd') then exit('/usr/sbin/dhcpd');
    if FileExists('/usr/sbin/dhcpd3') then exit('/usr/sbin/dhcpd3');
end;
//#############################################################################
function tdhcp3.INIT_PATH():string;
begin
    if FileExists('/etc/init.d/dhcpd') then exit('/etc/init.d/dhcpd');
    if FileExists('/etc/init.d/dhcp3-server') then exit('/etc/init.d/dhcp3-server');
//etc/sysconfig/dhcpd
///etc/default/dhcp3-server
end;
//#############################################################################
function tdhcp3.CONF_PATH():string;
begin
    if FIleExists('/etc/dhcp3/dhcpd.conf') then exit('/etc/dhcp3/dhcpd.conf');
    if FIleExists('/etc/dhcpd.conf') then exit('/etc/dhcpd.conf');
    if FIleExists('/etc/dhcpd/dhcpd.conf') then exit('/etc/dhcpd/dhcpd.conf');
    result:='/etc/dhcp3/dhcpd.conf';
end;
//#############################################################################
function tdhcp3.DEFAULT_PATH():string;
begin
    if FIleExists('/etc/default/dhcp3-server') then exit('/etc/default/dhcp3-server');
    if FIleExists('/etc/sysconfig/dhcpd') then exit('/etc/sysconfig/dhcpd');
end;
//#############################################################################
function tdhcp3.VERSION():string;
var
   i:integer;
   l:Tstringlist;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
   if not FileExists(BIN_PATH()) then exit;
   
   result:=SYS.GET_CACHE_VERSION('APP_DHCP');
   if length(result)>0 then exit;
   
   RegExpr:=TRegExpr.Create;
   tmpstr:=LOGS.FILE_TEMP();
   fpsystem(BIN_PATH() + ' -h >'+tmpstr+' 2>&1');
   if not FileExists(tmpstr) then exit;
   l:=TstringList.Create;
   l.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   for i:=0 to l.Count-1 do begin
       RegExpr.Expression:='V([0-9\.]+)';
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;

       RegExpr.Expression:='Internet Systems Consortium DHCP Server\s+([0-9\.a-z]+)';
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;



   end;
   
   l.Free;
   RegExpr.Free;
   SYS.SET_CACHE_VERSION('APP_DHCP',result);
   
end;
//#############################################################################
function tdhcp3.DAEMON_PID():string;
var
   pid:string;
begin
   pid:=READ_PID();
   if length(pid)=0 then pid:=SYS.PIDOF(BIN_PATH());
   exit(pid);
end;
//##############################################################################
function tdhcp3.PID_PATH():string;
begin
if FileExists('/var/run/dhcpd.pid') then exit('/var/run/dhcpd.pid');
if FileExists('/var/run/dhcpd/dhcpd.pid') then exit('/var/run/dhcpd/dhcpd.pid');
if FileExists('/var/run/dhcp3-server/dhcpd.pid') then exit('/var/run/dhcp3-server/dhcpd.pid');
result:='/var/run/dhcp3-server/dhcpd.pid';
end;
function tdhcp3.READ_PID():string;
begin
exit(SYS.GET_PID_FROM_PATH(PID_PATH()));
end;
//##############################################################################
procedure tdhcp3.RELOAD();
var
   pid,cmd:string;
   count:integer;
begin
    pid:=DAEMON_PID();
    logs.DebugLogs('Starting......: DHCP Server daemon reloading PID:'+pid);
    logs.Syslogs('Reloading DHCP server PID:'+pid);
    if EnableDHCPServer=0 then begin
       if SYS.PROCESS_EXIST(pid) then begin
          STOP();
          exit;
       end;
    end;

    ApplyConf();

    if SYS.PROCESS_EXIST(pid) then begin
       fpsystem(SYS.LOCATE_GENERIC_BIN('kill')+' -HUP '+pid);
       exit;
    end;

    START();

end;
//##############################################################################

procedure tdhcp3.START();
var
   pid,cmd:string;
   count:integer;
begin
    count:=0;
    logs.DebugLogs('################# DHCP SERVER ######################');

    if not FileExists(BIN_PATH()) then begin
       logs.DebugLogs('Starting......: DHCP server is not installed...');
       exit;
    end;

    if EnableDHCPServer=0 then begin
        logs.DebugLogs('Starting......: DHCP server is disabled...');
        STOP();
        exit;
    end;

    pid:=DAEMON_PID();
    if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: DHCP server already exists using pid ' + pid+ '...');
       exit;
    end;
    ApplyConf();
    forceDirectories('/var/run/dhcp3-server');
    forceDirectories('/var/lib/dhcp3');
    if Not FileExists('/var/lib/dhcp3/dhcpd.other') then logs.WriteToFile('#','/var/lib/dhcp3/dhcpd.other');
    if Not FileExists('/var/lib/dhcp3/dhcpd.leases') then logs.WriteToFile('#','/var/lib/dhcp3/dhcpd.leases');

    fpsystem('/bin/chown dhcpd:dhcpd /var/run/dhcp3-server >/dev/null 2>&1');
    if FileExists('/var/lib/dhcp3/dhcpd.leases~') then  fpsystem('/bin/chown dhcpd:dhcpd /var/lib/dhcp3/dhcpd.leases~ >/dev/null 2>&1' );
    cmd:=BIN_PATH()+' -q -pf '+PID_PATH()+' -cf '+CONF_PATH()+' -lf /var/lib/dhcp3/dhcpd.leases';
    logs.OutputCmd(cmd);

        while not SYS.PROCESS_EXIST(DAEMON_PID()) do begin
              sleep(150);
              inc(count);
              if count>100 then begin
                 logs.DebugLogs('Starting......: DHCP Server daemon. (timeout!!!)');
                 logs.DebugLogs('Starting......: DHCP server daemon.'+cmd);
                 break;
              end;
        end;

    if not SYS.PROCESS_EXIST(DAEMON_PID()) then begin
         logs.DebugLogs('Starting......: DHCP server daemon. (failed!!!)');
         logs.DebugLogs('Starting......: DHCP server daemon.'+cmd);
    end else begin
         logs.DebugLogs('Starting......: DHCP server daemon. PID '+DAEMON_PID());

    end;
end;
//##############################################################################

procedure tdhcp3.STOP();
var
   pid:string;
   count:integer;
begin

    if not FileExists(BIN_PATH()) then begin
       writeln('Stopping DHCP Server.....: not installed');
       exit;
    end;


    pid:=DAEMON_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping DHCP Server.....: Already stopped');
       exit;
    end;
    writeln('Stopping DHCP Server.....: ' + pid + ' PID');
    if FileExists(INIT_PATH) then begin
       logs.OutputCmd(INIT_PATH+' stop');
    end else begin
        fpsystem('/bin/kill '+pid);
    end;


     pid:=DAEMON_PID();
     count:=0;
     while SYS.PROCESS_EXIST(pid) do begin
           fpsystem('/bin/kill '+pid);
           Inc(count);
           sleep(800);
           if count>20 then begin
                   writeln('Stopping DHCP Server.....: ' + pid+ ' PID (timeout)');
                  fpsystem('/bin/kill -9 ' + pid);
                  break;
           end;
            pid:=DAEMON_PID();
     end;
     pid:=DAEMON_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping DHCP Server.....: Success');
       exit;
    end;
    writeln('Stopping DHCP Server.....: Failed');


end;
//##############################################################################
function tdhcp3.STATUS():string;
var
   ini:TstringList;
   pidpath:string;
begin
SYS.MONIT_DELETE('APP_DHCP');
if not FileExists(BIN_PATH()) then exit;
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --dhcpd >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
ini:=TstringList.Create;
end;
//#########################################################################################
function tdhcp3.FIND_NIC():string;
var
   l:Tstringlist;
   tcp:ttcpip;
   i:integer;
   eth,ip:string;
begin
   l:=TstringList.Create;
   tcp:=ttcpip.Create;
   l.AddStrings(tcp.LIST_NICS());
   for i:=0 to l.Count-1 do begin
       eth:=l.Strings[i] ;
       ip:=tcp.IP_ADDRESS_INTERFACE(eth);
       if ip='0.0.0.0' then continue;
       logs.DebugLogs('Starting......: DHCP server found "'+eth+'"="'+ip+'"');
       result:=eth;
       l.free;
       tcp.free;
       exit;
   end;
end;
//#########################################################################################

procedure tdhcp3.ApplyConf();
var
   l:TstringList;
   DHCP3ConfigurationFile:string;
   DHCP3ListenNIC:string;
   tcp:ttcpip;
   ipAddr:string;
   defpath:string;
begin

logs.DebugLogs('Starting......: DHCP server Building configuration...');
DHCP3ListenNIC:=trim(SYS.GET_INFO('DHCP3ListenNIC'));
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dhcpd.compile.php');
logs.DebugLogs('Starting......: DHCP server listen NIC "'+DHCP3ListenNIC+'"');

if length(DHCP3ListenNIC)=0 then begin
   logs.DebugLogs('Starting......: DHCP No listen NIC specified, try to find a good one');
   DHCP3ListenNIC:=FIND_NIC();
   if length(DHCP3ListenNIC)>0 then begin
      SYS.set_INFO('DHCP3ListenNIC',DHCP3ListenNIC);
      fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.dhcpd.compile.php');
   end;
end;
if length(DHCP3ListenNIC)=0 then begin
   logs.DebugLogs('Starting......: DHCP No listen NIC specified...');
   exit;
end;


if EnableDHCPServer=1 then begin
   logs.DebugLogs('Starting......: DHCP server trying to find ip address of '+DHCP3ListenNIC);
   tcp:=ttcpip.Create;
   ipAddr:=tcp.IP_ADDRESS_INTERFACE(DHCP3ListenNIC);
   logs.DebugLogs('Starting......: DHCP server '+DHCP3ListenNIC+' "'+ipAddr+'"');
   if ipAddr='0.0.0.0' then begin
      logs.DebugLogs('Starting......: DHCP server testing if '+DHCP3ListenNIC+' in not linked to br0');
      ipAddr:=tcp.IP_ADDRESS_INTERFACE('br0');
      if ipAddr<>'0.0.0.0' then begin
          logs.DebugLogs('Starting......: DHCP server change '+DHCP3ListenNIC+' to br0 for this instance...');
          DHCP3ListenNIC:='br0';
      end;
   end;

   l:=Tstringlist.Create;
   defpath:=DEFAULT_PATH();
   logs.DebugLogs('Starting......: DHCP server changing "'+defpath+'"');
   l.Add('INTERFACES='+DHCP3ListenNIC);
   l.Add('DHCPDARGS="'+DHCP3ListenNIC+'"');
   logs.WriteToFile(l.Text,DEFAULT_PATH());
end;





end;
//#########################################################################################





end.

