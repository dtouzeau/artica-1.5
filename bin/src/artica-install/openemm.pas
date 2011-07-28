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
    function    VERSION():string;
    function    BIN_PATH():string;
    function    JAVA_HOME_GET():string;


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
   su:string;
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
   writeln('Stopping OpenEMM server......: ' + pidstring + ' PID..');

   su:=SYS.LOCATE_GENERIC_BIN('su');
   tmpstr:=logs.FILE_TEMP();
   cmd:='su -m -c "'+binpath +' stop" --login openemm >'+tmpstr+' 2>&1';
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
  if FileExists('/usr/share/artica-postfix/exec.openemm.php') then fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.openemm.php --build');
   su:=SYS.LOCATE_GENERIC_BIN('su');
   tmpstr:=logs.FILE_TEMP();
   cmd:='su -m -c "'+binpath +' start" --login openemm >'+tmpstr+' 2>&1';
   fpsystem(cmd);
   pids:=TStringlist.Create;
   pids.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   for i:=0 to pids.Count-1 do begin
       logs.DebugLogs('Starting......: OpenEMM '+pids.Strings[i]);
   end;


if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  OpenEMM server started with new PID ' +PID_NUM()+ '...');
   exit;
end else begin
       logs.DebugLogs('Starting......: OpenEMM server (failed!!!)');
       logs.DebugLogs('Starting......: OpenEMM server "'+cmd+'"');
end;

end;
//##############################################################################

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
end.
