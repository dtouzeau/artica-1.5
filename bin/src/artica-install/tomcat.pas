unit tomcat;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  ttomcat=class


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
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_PATH():string;
    function    JAVA_HOME_GET():string;
    function    ROOT_DIR():string;

END;

implementation

constructor ttomcat.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('TomcatEnable'),TomcatEnable) then TomcatEnable:=1;
       if not TryStrToInt(SYS.GET_INFO('OpenEMMEnable'),OpenEMMEnable) then OpenEMMEnable:=1;
       if FileExists('/home/openemm/bin/openemm.sh') then if OpenEMMEnable=1 then TomcatEnable:=0;


end;
//##############################################################################
procedure ttomcat.free();
begin
    logs.Free;
end;
//##############################################################################

procedure ttomcat.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
   cmdline,javaHome,env:string;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Tomcat server.......: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping Tomcat server.......: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
  writeln('Stopping Tomcat server.......:  ' + pidstring + ' PID..');

  javaHome:=JAVA_HOME_GET();
  if not DirectoryExists(javaHome) then begin
      writeln('Stopping Tomcat server.......:  Tomcat server unable to stat java home directory, aborting..');
      exit;
  end;
  env:=SYS.LOCATE_GENERIC_BIN('env');
  writeln('Stopping Tomcat server.......: Tomcat server PID:'+pidstring+' Java:'+javaHome);
  cmd:=ExtractFilePath(binpath)+'shutdown.sh';
  fpsystem(cmd);
  pidstring:=PID_NUM();
  count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
         sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping Tomcat server.......: Tomcat server PID:'+pidstring+' failed with "'+cmd+'" force it to be killed');
                 break;
               end;
            end;
            break;
        end;
  end;

   pidstring:=PID_NUM();
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping Tomcat server.......: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then writeln('Stopping Tomcat server.......: Stopped');

end;

//##############################################################################
function ttomcat.BIN_PATH():string;
begin
result:='/opt/openemm/tomcat/bin/startup.sh';
end;
//##############################################################################
function ttomcat.ROOT_DIR():string;
begin
if FileExists('/opt/openemm/tomcat/bin/startup.sh') then exit('/opt/openemm/tomcat');
end;
//##############################################################################

procedure ttomcat.START();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   cmdline,javaHome,env:string;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: Tomcat server not installed');
         exit;
   end;

if TomcatEnable=0 then begin
   logs.DebugLogs('Starting......:  Tomcat server is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  Tomcat server Already running using PID ' +PID_NUM()+ '...');
   exit;
end;
  if FileExists('/usr/share/artica-postfix/exec.tomcat.php') then fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.tomcat.php --build');
  javaHome:=JAVA_HOME_GET();
  if not DirectoryExists(javaHome) then begin
      logs.DebugLogs('Starting......: Tomcat server unable to stat java home directory, aborting..');
      exit;
  end;
  env:=SYS.LOCATE_GENERIC_BIN('env');
  logs.DebugLogs('Starting......: Tomcat server '+javaHome);
  cmd:=env+' JAVA_HOME="'+javaHome+'" CATALINA_PID="/opt/openemm/tomcat/temp/tomcat.pid" '+binpath;

   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: Tomcat server (timeout!!!)');
       logs.DebugLogs('Starting......: Tomcat server "'+cmd+'"');
       break;
     end;
   end;


if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  Tomcat server started with new PID ' +PID_NUM()+ '...');
   exit;
end else begin
       logs.DebugLogs('Starting......: Tomcat server (failed!!!)');
       logs.DebugLogs('Starting......: Tomcat server "'+cmd+'"');
end;

end;
//##############################################################################

 function ttomcat.PID_NUM():string;
 var
    pgrep:string;
    tmpstr:string;
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
begin
result:='';

if FileExists('/opt/openemm/tomcat/temp/tomcat.pid') then result:=SYS.GET_PID_FROM_PATH('/opt/openemm/tomcat/temp/tomcat.pid');
if SYS.PROCESS_EXIST(result) then exit;

pgrep:=SYS.LOCATE_GENERIC_BIN('pgrep');
tmpstr:=LOGS.FILE_TEMP();
fpsystem(pgrep+' -l -f "/opt/openemm/tomcat/bin/bootstrap.jar" >'+tmpstr+' 2>&1');
if not FileExists(tmpstr) then exit;
l:=Tstringlist.Create;
l.LoadFromFile(tmpstr);
logs.DeleteFile(tmpstr);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^([0-9]+)\s+.+?java';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then result:=RegExpr.Match[1];

end;
 l.free;
 RegExpr.free;
end;
 //##############################################################################
function ttomcat.PID_PATH():string;
begin
     exit('/var/tmp/ufdbguardd.pid');
end;
 //##############################################################################
 function ttomcat.VERSION():string;
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
    result:=SYS.GET_CACHE_VERSION('APP_TOMCAT');
     if length(result)>2 then exit;
     if not FileExists(binpath) then begin
        if SYS.verbosed then writeln(binpath,' no such file');
         exit;
     end;


    tmpstr:=logs.FILE_TEMP();
    env:=SYS.LOCATE_GENERIC_BIN('env');
    java:=JAVA_HOME_GET();
    cmd:=env +' JAVA_HOME="'+java+'"  /opt/openemm/tomcat/bin/version.sh >'+tmpstr +' 2>&1';
    if SYS.verbosed then writeln(cmd);
    fpsystem(cmd);
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Apache Tomcat.+?([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end else begin
             if sys.verbosed  then writeln(l.Strings[i],' no match');
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_TOMCAT',result);
l.free;
RegExpr.free;
end;
//#########################################################################################
function ttomcat.JAVA_HOME_GET():string;
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
procedure ttomcat.JAVA_HOME_SET(path:string);
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
procedure ttomcat.JDKSET();
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
end.
