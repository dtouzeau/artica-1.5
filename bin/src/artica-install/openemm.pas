unit openemm;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  topenemm=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     TomcatEnable:integer;
     OpenEMMEnable:integer;
     binpath:string;

     procedure JAVA_HOME_SET(path:string);
     procedure JDKSET();
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    PID_NUM():string;
    function    PID_SENDMAIL_NUM():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    JAVA_HOME_GET():string;
    procedure   SENDMAIL_START();
    procedure   SENDMAIL_STOP();
    procedure   SENDMAIL_INI_TD();
    function    SENDMAIL_VERSION():string;

END;

implementation

constructor topenemm.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:='/home/openemm/bin/openemm.sh';
       if not TryStrToInt(SYS.GET_INFO('OpenEMMEnable'),OpenEMMEnable) then OpenEMMEnable:=1;



end;
//##############################################################################
procedure topenemm.free();
begin
    logs.Free;
end;
//##############################################################################

procedure topenemm.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
   cmdline,tmpstr,env:string;
   su,distri:string;
begin
if not FileExists(binpath) then begin
   writeln('Stopping OpenEMM server......: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping OpenEMM server......: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   distri:=SYS.DISTRIBUTION_CODE();
   writeln('Stopping OpenEMM server......: ' + pidstring + ' PID.. "',distri,'"');

   su:=SYS.LOCATE_GENERIC_BIN('su');
   tmpstr:=logs.FILE_TEMP();
   if distri='SUSE' then cmd:=su+' -m -c "'+binpath +' stop" --login openemm >'+tmpstr+' 2>&1';
   if length(cmd)=0 then cmd:=su+' -c "'+binpath +' stop" --login openemm >'+tmpstr+' 2>&1';
   fpsystem(cmd);
   pids:=TStringlist.Create;
   pids.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   for i:=0 to pids.Count-1 do begin
       writeln('Stopping OpenEMM server......: '+pids.Strings[i]);
   end;

   pids.free;


end;

//##############################################################################
procedure topenemm.SENDMAIL_START();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
   cmdline,tmpstr,env:string;
   su:string;
   distri:string;
begin

   if not FileExists('/home/openemm/sendmail/sbin/sendmail') then begin
         logs.DebugLogs('Starting......: Sendmail for OpenEMM server not installed');
         exit;
   end;

if OpenEMMEnable=0 then begin
   logs.DebugLogs('Starting......: OpenEMM server is disabled');
   SENDMAIL_STOP();
   exit;
end;

pidstring:=PID_SENDMAIL_NUM();
if SYS.PROCESS_EXIST(pidstring) then begin
   logs.DebugLogs('Starting......: Sendmail for OpenEMM server Already running using PID ' +pidstring+ '...');
   exit;
end;
distri:=SYS.DISTRIBUTION_CODE();
  if FileExists('/usr/share/artica-postfix/exec.openemm.php') then fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openemm.php --sendmail');
   su:=SYS.LOCATE_GENERIC_BIN('su');
   tmpstr:=logs.FILE_TEMP();
   if distri='SUSE' then cmd:=su+' -m -c "/home/openemm/sendmail/sbin/sendmail -C/home/openemm/sendmail/etc/sendmail.cf -bd -q15m" --login openemm >'+tmpstr+' 2>&1';
   if length(cmd)=0 then cmd:=su+' -c "/home/openemm/sendmail/sbin/sendmail -C/home/openemm/sendmail/etc/sendmail.cf -bd -q15m" --login openemm >'+tmpstr+' 2>&1';

   fpsystem(cmd);
   pids:=TStringlist.Create;
   pids.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   for i:=0 to pids.Count-1 do begin
       logs.DebugLogs('Starting......: Sendmail for OpenEMM server '+pids.Strings[i]);
   end;

pidstring:=PID_SENDMAIL_NUM();
if SYS.PROCESS_EXIST(pidstring) then begin
   logs.DebugLogs('Starting......: Sendmail for OpenEMM server started with new PID ' +pidstring+ '...');
   exit;
end else begin
       logs.DebugLogs('Starting......: Sendmail for OpenEMM server (failed!!!) ['+pidstring+']');
       logs.DebugLogs('Starting......: Sendmail for OpenEMM server "'+cmd+'"');
end;

end;
//##############################################################################

//##############################################################################
procedure topenemm.SENDMAIL_STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring,pid:string;
   fpid,i:integer;
begin
if not FileExists('/home/openemm/sendmail/sbin/sendmail') then begin
   writeln('Stopping Sendmail for OpenEMM: Not installed');
   exit;
end;

if SYS.PROCESS_EXIST(PID_SENDMAIL_NUM()) then begin
    writeln('Stopping Sendmail for OpenEMM: ' + PID_SENDMAIL_NUM() + ' PID..');
    if SYS.PROCESS_EXIST(PID_SENDMAIL_NUM()) then fpsystem('/bin/kill ' + PID_SENDMAIL_NUM());
    count:=0;
    pid:=PID_SENDMAIL_NUM();
     while SYS.PROCESS_EXIST(pid) do begin
        writeln('Stopping Sendmail for OpenEMM: '+pid);
        if SYS.PROCESS_EXIST(pid) then fpsystem('/bin/kill '+pid);
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(pid)>0 then begin
               if SYS.PROCESS_EXIST(pid) then begin
                  writeln('Stopping Sendmail for OpenEMM: kill pid '+ PID_SENDMAIL_NUM()+' after timeout');
                  fpsystem('/bin/kill -9 ' + PID_SENDMAIL_NUM());
               end;
            end;
            break;
        end;
        pid:=PID_SENDMAIL_NUM();
  end;


end else begin
   writeln('Stopping Sendmail for OpenEMM: Already stopped');
end;




end;
//##############################################################################
function topenemm.BIN_PATH():string;
begin
result:='/home/openemm/bin/openemm.sh';
end;
//##############################################################################
procedure topenemm.START();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
   cmdline,tmpstr,env:string;
   su:string;
   distri:string;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: OpenEMM server not installed');
         exit;
   end;

if OpenEMMEnable=0 then begin
   logs.DebugLogs('Starting......: OpenEMM server is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......: OpenEMM server Already running using PID ' +PID_NUM()+ '...');
   exit;
end;
distri:=SYS.DISTRIBUTION_CODE();
  if FileExists('/usr/share/artica-postfix/exec.openemm.php') then fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openemm.php --build');
  if FileExists('/usr/share/artica-postfix/exec.openemm.php') then fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openemm.php --sendmail');
   SENDMAIL_INI_TD();
   su:=SYS.LOCATE_GENERIC_BIN('su');
   tmpstr:=logs.FILE_TEMP();
   if distri='SUSE' then cmd:=su+' -m -c "'+binpath +' start" --login openemm >'+tmpstr+' 2>&1';
   if length(cmd)=0 then cmd:=su+' -c "'+binpath +' start" --login openemm >'+tmpstr+' 2>&1';

   fpsystem(cmd);
   count:=0;
     while not SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            logs.DebugLogs('Starting......: OpenEMM server (timeout)');
            break;
        end;


     end;


   pids:=TStringlist.Create;
   pids.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   for i:=0 to pids.Count-1 do begin
       logs.DebugLogs('Starting......: OpenEMM '+pids.Strings[i]);
   end;


if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......: OpenEMM server started with new PID ' +PID_NUM()+ '...');
   SENDMAIL_STOP();
   SENDMAIL_START();
   exit;
end else begin
       logs.DebugLogs('Starting......: OpenEMM server (failed!!!)');
       logs.DebugLogs('Starting......: OpenEMM server "'+cmd+'"');
end;

end;
 function topenemm.PID_NUM():string;
 var
    grep,ps,awk:string;
    tmpstr:string;
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   cmd:string;
begin
result:='';
grep:=SYS.LOCATE_GENERIC_BIN('grep');
ps:=SYS.LOCATE_GENERIC_BIN('ps');
awk:=SYS.LOCATE_GENERIC_BIN('awk');
tmpstr:=LOGS.FILE_TEMP();
cmd:=ps+' -eo pid,command|'+grep+' -E "\/home\/openemm.*?org\.apache\.catalina"|'+grep+' -v grep|'+awk+' ''{print $1}'' >'+tmpstr+' 2>&1';
fpsystem(cmd);
if SYS.verbosed then writeln(cmd);
if not FileExists(tmpstr) then exit;
result:=trim(logs.ReadFromFile(tmpstr));
if SYS.verbosed then writeln('PID:',result);
logs.DeleteFile(tmpstr);

end;
 //##############################################################################
  function topenemm.PID_SENDMAIL_NUM():string;
 var
    grep,ps,awk:string;
    tmpstr:string;
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   cmd:string;
begin
result:='';
result:=SYS.GET_PID_FROM_PATH('/home/openemm/sendmail/run/sendmail.pid');
if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF('/home/openemm/sendmail/sbin/sendmail');

end;
 //##############################################################################
 function topenemm.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
   env:string;
   java:string;
   cmd:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_OPENEMM');
     if length(result)>2 then exit;
     if not FileExists('/home/openemm/webapps/openemm/index.html') then begin
        if SYS.verbosed then writeln('/home/openemm/webapps/openemm/index.html',' no such file');
         exit;
     end;



    l:=TstringList.Create;
    l.LoadFromFile('/home/openemm/webapps/openemm/index.html');


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='<title>.+?OpenEMM\s+([0-9\.\-]+)</title';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end else begin
             if sys.verbosed  then writeln(l.Strings[i],' no match');
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_OPENEMM',result);
l.free;
RegExpr.free;
end;
//#########################################################################################
 function topenemm.SENDMAIL_VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
   env:string;
   java:string;
   cmd:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_OPENEMM_SENDMAIL');
     if length(result)>2 then exit;
     if not FileExists('/home/openemm/sendmail/sbin/sendmail') then begin
        if SYS.verbosed then writeln('/home/openemm/sendmail/sbin/sendmail',' no such file');
         exit;
     end;

    tmpstr:=logs.FILE_TEMP();
    cmd:=SYS.LOCATE_GENERIC_BIN('echo')+' \$Z | /home/openemm/sendmail/sbin/sendmail -bt -d0 >'+tmpstr+' 2>&1';
    if SYS.verbosed then writeln(cmd);
    fpsystem(cmd);
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Version\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end else begin
             if sys.verbosed  then writeln(l.Strings[i],' no match');
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_OPENEMM_SENDMAIL',result);
l.free;
RegExpr.free;
end;
//#########################################################################################
function topenemm.JAVA_HOME_GET():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
begin
result:='';
l:=TstringList.Create;
l.LoadFromFile('/etc/environment');

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='JAVA_HOME=(.+)';
 for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
         result:=RegExpr.Match[1];
         result:=AnsiReplaceText(result,'"','');
         break;
       end;
    end;

 l.free;
 RegExpr.free;

end;
//#########################################################################################
procedure topenemm.JAVA_HOME_SET(path:string);
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
begin

l:=TstringList.Create;
l.LoadFromFile('/etc/environment');

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='JAVA_HOME=(.+)';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
        logs.DebugLogs('Starting......: Tomcat, Setting JAVA_HOME="'+path+'" line: '+IntTostr(i));
         l.Strings[i]:='JAVA_HOME="'+path+'"';
         logs.WriteToFile(l.Text,'/etc/environment');
         l.free;
         RegExpr.free;
         exit;
       end;
    end;
    logs.DebugLogs('Starting......: Tomcat, Setting new environnment JAVA_HOME="'+path+'"');
    l.Add('JAVA_HOME="'+path+'"');
    logs.WriteToFile(l.Text,'/etc/environment');

 l.free;
 RegExpr.free;

 //env JAVA_HOME="/opt/openemm/jdk1.6.0_26" /opt/openemm/tomcat/bin/startup.sh

end;
//#########################################################################################
procedure topenemm.JDKSET();
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   sJAVA_HOME_GET:string;
begin

sJAVA_HOME_GET:=JAVA_HOME_GET();
if DirectoryExists(sJAVA_HOME_GET) then begin
   if FileExists(sJAVA_HOME_GET+'/bin/java')then begin
      logs.DebugLogs('Starting......: Tomcat, java path="'+sJAVA_HOME_GET+'"');
      exit;
   end;
end;


l:=TstringList.Create;
l.AddStrings(SYS.DirDir('/opt/openemm'));
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='jdk[0-9\.\_]+';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       if FileExists('/opt/openemm/'+l.Strings[i]+'/bin/java') then begin
         JAVA_HOME_SET('/opt/openemm/'+l.Strings[i]);
       end;
    end;
end;
 l.free;
 RegExpr.free;

end;
//#########################################################################################
procedure topenemm.SENDMAIL_INI_TD();
var
   myFile : TStringList;
begin
 if not FileExists('/home/openemm/sendmail/sbin/sendmail') then begin
    logs.DebugLogs('Starting......: OpenEMM server /home/openemm/sendmail/sbin/sendmail no such file');
    exit;
 end;
  myFile:=TstringList.Create;
  myFile.Add('#!/bin/sh');
  myFile.Add('#Begin /etc/init.d/sendmail');


    myFile.Add('### BEGIN INIT INFO');
    myFile.Add('# Provides:          Sendmail for OpenEMM SMTP MTA');
    myFile.Add('# Required-Start:    $local_fs $remote_fs $syslog $named $network $time');
    myFile.Add('# Required-Stop:     $local_fs $remote_fs $syslog $named $network');
    myFile.Add('# Should-Start:');
    myFile.Add('# Should-Stop:');
    myFile.Add('# Default-Start:     2 3 4 5');
    myFile.Add('# Default-Stop:      0 1 6');
    myFile.Add('# Short-Description: Start sendmail daemon for openEMM');
    myFile.Add('# chkconfig: 2345 11 89');
    myFile.Add('# description: Postfix Daemon');
    myFile.Add('### END INIT INFO');
    myFile.Add('');

    myFile.Add('case "$1" in');
    myFile.Add(' start)');
    myFile.Add('    /usr/share/artica-postfix/bin/artica-install -watchdog openemm-sendmail $2 $3');
    myFile.Add('    ;;');
    myFile.Add('');
    myFile.Add('  stop)');
    myFile.Add('    /usr/share/artica-postfix/bin/artica-install -shutdown openemm-sendmail $2 $3');
    myFile.Add('    ;;');
    myFile.Add('');
    myFile.Add(' restart)');
    myFile.Add('     /usr/share/artica-postfix/bin/artica-install -shutdown openemm-sendmail $2 $3');
    myFile.Add('     sleep 3');
    myFile.Add('     /usr/share/artica-postfix/bin/artica-install -watchdog openemm-sendmail $2 $3');
    myFile.Add('    ;;');
    myFile.Add('');
    myFile.Add(' reload)');
    myFile.Add('     /usr/share/artica-postfix/bin/artica-install --openemm-sendmail-reload $2 $3');
    myFile.Add('    ;;');
    myFile.Add('');
    myFile.Add('  *)');
    myFile.Add('    echo "Usage: $0 {start|stop|restart|reload} (+ debug or --verbose for more infos)"');
    myFile.Add('    exit 1');
    myFile.Add('    ;;');
    myFile.Add('esac');
    myFile.Add('exit 0');


    logs.WriteToFile(myfile.Text,'/home/openemm/bin/sendmail-init');
    myFile.free;
  logs.DebugLogs('Starting......: OpenEMM server install sendmail init.d scripts OK');
  logs.DebugLogs('Starting......: OpenEMM server Adding startup scripts to the system OK');
  fpsystem('/bin/chmod +x /home/openemm/bin/sendmail-init');
  if FileExists('/etc/init.d/sendmail') then logs.DeleteFile('/etc/init.d/sendmail');
  fpsystem('/bin/ln -s /home/openemm/bin/sendmail-init /etc/init.d/sendmail >/dev/null 2>&1');

 if FileExists('/usr/sbin/update-rc.d') then begin
    fpsystem('/usr/sbin/update-rc.d -f sendmail defaults >/dev/null 2>&1');
 end;

  if FileExists('/sbin/chkconfig') then begin
     fpsystem('/sbin/chkconfig --add sendmail >/dev/null 2>&1');
     fpsystem('/sbin/chkconfig --level 2345 sendmail on >/dev/null 2>&1');
  end;
   logs.DebugLogs('Starting......: OpenEMM server install sendmail init.d scripts DONE');

end;
end.
