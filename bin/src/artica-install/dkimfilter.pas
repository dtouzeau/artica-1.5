unit dkimfilter;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  tdkim=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
    dbg:boolean;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   ETC_DEFAULT();
    procedure   SAVE_CERTIFICATE();
    function    READ_CONF(key:string):string;
    function    DAEMON_BIN_PATH():string;
    procedure   DKIM_FILTER_START();
    procedure   DKIM_FILTER_STOP();
    function    VERSION():string;
    function    STATUS():string;
    function    INITD():string;
    function    PID_NUM():string;
    function    PID_PATH():string;
    function    MAIN_CONF_PATH():string;
    function    WRITE_CONF(key:string;value:string):string;
    procedure   DKIM_FILTER_CHANGE_OWNER();
    function    SOCKET_PATH():string;
END;

implementation

constructor tdkim.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       forcedirectories('/opt/artica/tmp');
       LOGS:=tlogs.Create();
       SYS:=ZSys;
       dbg:=LOGS.COMMANDLINE_PARAMETERS('debug');

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tdkim.free();
begin
    logs.Free;
end;
//##############################################################################
function tdkim.INITD():string;
begin
   if FileExists('/etc/init.d/dkim-filter') then exit('/etc/init.d/dkim-filter');
end;
//##############################################################################
function tdkim.DAEMON_BIN_PATH():string;
begin
   if FileExists('/usr/sbin/dkim-filter') then exit('/usr/sbin/dkim-filter');
end;
//##############################################################################
function tdkim.PID_PATH():string;
begin
    if FileExists('/var/run/dkim-filter/dkim-filter.pid') then exit('/var/run/dkim-filter/dkim-filter.pid');
end;
//##############################################################################
function tdkim.PID_NUM():string;
var
   path:string;

begin
    path:=PID_PATH();
    if FileExists(path) then exit(SYS.GET_PID_FROM_PATH(path));
end;
//##############################################################################
procedure tdkim.ETC_DEFAULT();
var
l:TstringList;
begin

if not FileExists('/etc/default/dkim-filter') then exit;
l:=TstringList.Create;

l.Add('# Command-line options specified here will override the contents of');
l.Add('# /etc/dkim-filter.conf. See dkim-filter(8) for a complete list of options.');
l.Add('#DAEMON_OPTS=""');
l.Add('#');
l.Add('# Uncomment to specify an alternate socket');
l.Add('# Note that setting this will override any Socket value in dkim-filter.conf');
l.Add('SOCKET="local:/var/run/dkim-filter/dkim-filter.sock" # Debian default');
l.Add('#SOCKET="inet:54321" # listen on all interfaces on port 54321');
l.Add('#SOCKET="inet:8891@localhost" # Ubuntu default - listen on loopback on port 8891');
l.Add('#SOCKET="inet:12345@192.0.2.1" # listen on 192.0.2.1 on port 12345');
l.SaveToFile('/etc/default/dkim-filter');
l.free;
end;
//##############################################################################
function tdkim.MAIN_CONF_PATH():string;
begin
    if FileExists('/etc/dkim-filter.conf') then exit('/etc/dkim-filter.conf');
    if FileExists('/etc/mail/dkim-filter.conf') then exit('/etc/mail/dkim-filter.conf');

end;
//##############################################################################

function tdkim.READ_CONF(key:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
begin
 if not FileExists(MAIN_CONF_PATH()) then exit;
 FileDatas:=TstringList.Create;
 FileDatas.LoadFromFile(MAIN_CONF_PATH());
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='^'+key+'\s+(.+)';
 for i:=0 to FileDatas.Count-1 do begin
     if RegExpr.Exec(FileDatas.Strings[i]) then begin
         result:=RegExpr.Match[1];
         break;
     end;

 end;
         FileDatas.Free;
         RegExpr.Free;

end;
//##############################################################################
function tdkim.SOCKET_PATH():string;
begin
exit('/var/run/dkim-filter/dkim-filter.sock');
end;
//##############################################################################
function tdkim.WRITE_CONF(key:string;value:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    found:boolean;
    main_path:string;
begin
 result:='';
 found:=false;
 main_path:=MAIN_CONF_PATH();
 if not FileExists(main_path) then exit;
 FileDatas:=TstringList.Create;
 FileDatas.LoadFromFile(main_path);
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='^'+key+'\s+(.+)';
 for i:=0 to FileDatas.Count-1 do begin
     if RegExpr.Exec(FileDatas.Strings[i]) then begin
         FileDatas.Strings[i]:=key+chr(9)+value;
         FileDatas.SaveToFile(main_path);
         found:=true;
         break;
     end;

 end;

         if not found then begin
            FileDatas.Add(key+chr(9)+value);
            FileDatas.SaveToFile(main_path);
         end;
            

         FileDatas.Free;
         RegExpr.Free;

end;
//##############################################################################
function tdkim.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    cmd:string;

begin
 if not FileExists(DAEMON_BIN_PATH()) then begin
    if dbg then writeln('tdkim.VERSION() could not stat binary path');
    exit;
 end;
 
result:=SYS.GET_CACHE_VERSION('APP_DKIM');
if length(result)>0 then exit;
 FileDatas:=TstringList.Create;

 cmd:=DAEMON_BIN_PATH() + ' -V >/opt/artica/tmp/dkim.ver 2>&1';
 if dbg then writeln('tdkim.VERSION() ' + cmd);
 fpsystem(cmd);
 
 if not FileExists('/opt/artica/tmp/dkim.ver') then begin
    if dbg then writeln('tdkim.VERSION() could not stat binary /opt/artica/tmp/dkim.ver');
    exit;
 end;
 FileDatas.LoadFromFile('/opt/artica/tmp/dkim.ver');
 logs.DeleteFile('/opt/artica/tmp/dkim.ver');
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='Filter v([0-9\.]+)';
 for i:=0 to FileDatas.Count-1 do begin

     if RegExpr.Exec(FileDatas.Strings[i]) then begin
         result:=RegExpr.Match[1];
         break;
     end else begin
        if dbg then writeln('tdkim.VERSION() could not regex line ', FileDatas.Strings[i], ' for ',RegExpr.Expression);
     
     end;

 end;
         FileDatas.Free;
         RegExpr.Free;
           SYS.SET_CACHE_VERSION('APP_DKIM',result);

end;
//##############################################################################
procedure tdkim.SAVE_CERTIFICATE();
var
   cert:string;

begin
    forcedirectories('/etc/mail');
    WRITE_CONF('PidFile','/var/run/dkim-filter/dkim-filter.pid');
    WRITE_CONF('Socket','local:/var/run/dkim-filter/dkim-filter.sock');
    WRITE_CONF('KeyFile','/etc/mail/mail.filter.private');
    WRITE_CONF('Domain','/etc/mail/localdomains.txt');
    WRITE_CONF('Selector','mail');
    WRITE_CONF('Syslog','yes');
    WRITE_CONF('AutoRestart','yes');
    WRITE_CONF('X-Header','yes');
    WRITE_CONF('SendReports','yes');
    WRITE_CONF('InternalHosts','/etc/mail/localNetworks.txt');



    fpsystem(artica_path + '/bin/artica-ldap -localdomains /etc/mail/localdomains.txt');
    fpsystem(artica_path + '/bin/artica-ldap -pnetworks /etc/mail/localNetworks.txt');
    
    fpsystem('/bin/chown postfix:postfix /etc/mail/localdomains.txt'+ ' >/dev/null 2>&1');
    fpsystem('/bin/chown postfix:postfix /etc/mail/localNetworks.txt'+ ' >/dev/null 2>&1');

    cert:=READ_CONF('KeyFile');
    
    logs.Debuglogs('SAVE_CERTIFICATE():: /bin/cp /opt/artica/ssl/certs/lighttpd.pem '  + cert);
    fpsystem('/bin/cp /opt/artica/ssl/certs/lighttpd.pem '  + cert + ' >/dev/null 2>&1');
    fpsystem('/bin/chmod 600 ' + cert + ' >/dev/null 2>&1');
    fpsystem('/bin/chown postfix:postfix ' +cert+ ' >/dev/null 2>&1');
    
end;
//##############################################################################

function tdkim.STATUS:string;
var
ini:TstringList;
pid:string;
DkimFilterEnabled:string;
begin
   DkimFilterEnabled:=SYS.GET_INFO('DkimFilterEnabled');
   ini:=TstringList.Create;
   ini.Add('[DKIM_FILTER]');
   if FileExists(DAEMON_BIN_PATH()) then  begin
      pid:=PID_NUM();
      if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
      ini.Add('master_version='+VERSION());
      ini.Add('status='+SYS.PROCESS_STATUS(pid));
      ini.Add('service_name=APP_DKIM_FILTER');
      ini.Add('service_cmd=dkim');
      ini.Add('service_disabled='+DkimFilterEnabled);
   end;

   result:=ini.Text;
   ini.free;

end;
//##############################################################################
procedure tdkim.DKIM_FILTER_START();
var
   pid:string;
   initd_path:string;
   DkimFilterEnabled:string;
begin
    if not FileExists(DAEMON_BIN_PATH()) then exit;
    if not FileExists(INITD()) then exit;
    DkimFilterEnabled:=SYS.GET_INFO('DkimFilterEnabled');
    if DkimFilterEnabled<>'1' then begin
       logs.Debuglogs('DKIM_START:: dkim-filter is disabled DkimFilterEnabled='+DkimFilterEnabled);
       exit;
    end;
    

    
    
    pid:=PID_NUM();
    initd_path:=INITD();

    if SYS.PROCESS_EXIST(pid) then begin
       logs.Debuglogs('DKIM_START:: dkim-filter already running  PID '+pid);
       exit;
    end;

    logs.Output('DKIM_START:: Starting dkim-filter');
    ETC_DEFAULT();
    SAVE_CERTIFICATE();
    DKIM_FILTER_CHANGE_OWNER();
    SYS.FILE_CHOWN('postfix','postfix','/var/run/dkim-filter');


    logs.OutputCmd(initd_path + ' start');

    if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        logs.Debuglogs('DKIM_START:: Failed to start dkim-filter');
        exit;
    end;
end;
//##############################################################################
procedure tdkim.DKIM_FILTER_CHANGE_OWNER();
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
begin
    if not FileExists(INITD()) then exit;
    
 FileDatas:=TstringList.Create;
 FileDatas.LoadFromFile(INITD());
 RegExpr:=TRegExpr.Create;

 for i:=0 to FileDatas.Count-1 do begin
     RegExpr.Expression:='^USER=';
     if RegExpr.Exec(FileDatas.Strings[i]) then FileDatas.Strings[i]:='USER=postfix';
     
     RegExpr.Expression:='^GROUP=';
     if RegExpr.Exec(FileDatas.Strings[i]) then FileDatas.Strings[i]:='GROUP=postfix';
 end;
         FileDatas.SaveToFile(INITD());
         FileDatas.Free;
         RegExpr.Free;

end;
//##############################################################################


procedure tdkim.DKIM_FILTER_STOP();
var
   D:boolean;
   initd_path:string;
begin
    if not FileExists(DAEMON_BIN_PATH()) then exit;
    if not FileExists(INITD()) then exit;
    D:=logs.COMMANDLINE_PARAMETERS('html');
    initd_path:=INITD();
    if D then writeln('<tr><td width=1% valign="top"><img src="img/icon_mini_info.gif"><td valign="top" width=99%>Pid path:'+PID_PATH()+'</td></tr>');



    if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       if D then  if D then begin
          writeln('<tr><td width=1% valign="top"><img src="img/icon-mini-ok.gif"><td valign="top" width=99%>Already stopped</td></tr>');
       end else begin
           writeln('Stopping dkim-filter daemon......: Already stopped');
       end;

       exit;
    end;

       if D then begin
          writeln('<tr><td width=1% valign="top"><img src="img/icon_mini_info.gif"><td valign="top" width=99%>stopping dkim-filter '+ PID_NUM() + ' PID</td></tr>');
       end else begin
          writeln('Stopping dkim-filter............: ' + PID_NUM() + ' PID');
       end;


    fpsystem(initd_path + ' stop >/opt/artica/tmp/dkim.start 2>&1');
    logs.Debuglogs(SYS.ReadFileIntoString('/opt/artica/tmp/dkim.start'));
    logs.DeleteFile('/opt/artica/tmp/dkim.start');

    if SYS.PROCESS_EXIST(PID_NUM()) then begin
       if D then  if D then begin
          writeln('<tr><td width=1% valign="top"><img src="img/icon_mini_off.gif"><td valign="top" width=99%>stopping dkim-filter failed to stop</td></tr>');
       end else begin
        writeln('Stopping dkim-filter  daemon ' + PID_NUM() + ' PID (failed to stop)');
       end;
       exit;
    end;
end;
//##############################################################################


end.
