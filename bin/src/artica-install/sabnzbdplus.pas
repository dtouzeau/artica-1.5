unit sabnzbdplus;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tsabnzbdplus=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableSabnZbdPlus:integer;
     binpath:string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    PID_NUM():string;
    function    VERSION():string;
    function    BIN_PATH():string;

END;

implementation

constructor tsabnzbdplus.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableSabnZbdPlus'),EnableSabnZbdPlus) then EnableSabnZbdPlus:=0;
end;
//##############################################################################
procedure tsabnzbdplus.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tsabnzbdplus.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin

   writeln('Stopping sabnzbdplus.........: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
        writeln('Stopping sabnzbdplus.........: already Stopped');
        exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping sabnzbdplus.........: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping sabnzbdplus.........: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then  writeln('Stopping sabnzbdplus.........: Stopped');
end;

//##############################################################################
function tsabnzbdplus.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('sabnzbdplus');
end;
//##############################################################################
procedure tsabnzbdplus.START();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   cmdline:string;
   sabnzbdplusDir:string;
   sabnzbdplusIpAddr:string;
   sabnzbdplusPort:integer;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: sabnzbdplus not installed');
         exit;
   end;



if EnableSabnZbdPlus=0 then begin
   logs.DebugLogs('Starting......:  sabnzbdplus is disabled (key:EnableSabnZbdPlus)');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......:  sabnzbdplus Already running using PID ' +PID_NUM()+ '...');
   exit;
end;

   sabnzbdplusDir:=SYS.GET_INFO('sabnzbdplusDir');
   sabnzbdplusIpAddr:=SYS.GET_INFO('sabnzbdplusIpAddr');
   if not TryStrToInt(SYS.GET_INFO('sabnzbdplusPort'),sabnzbdplusPort) then sabnzbdplusPort:=9666;
   if length(sabnzbdplusDir)=0 then  sabnzbdplusDir:='/home/sabnzbdplus';
   if length(sabnzbdplusIpAddr)=0 then  sabnzbdplusIpAddr:='0.0.0.0';


   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.sabnzbdplus.php --patch');
   if not FIleExists('/usr/share/sabnzbdplus/SABnzbd.py') then logs.DebugLogs('Starting......: sabnzbdplus "'+binpath+'"');
   logs.DebugLogs('Starting......: sabnzbdplus server listen on '+sabnzbdplusIpAddr+':'+IntToStr(sabnzbdplusPort));
   logs.DebugLogs('Starting......: sabnzbdplus directory is "'+sabnzbdplusDir+'"');
   forceDirectories(sabnzbdplusDir);

   cmd:='cd /usr/share/sabnzbdplus && '+binpath+' -t /usr/share/sabnzbdplus/interfaces/smpl -f '+sabnzbdplusDir+'/sabnzbd.ini -d -s '+sabnzbdplusIpAddr+':'+IntToStr(sabnzbdplusPort);
   if FileExists('/usr/share/sabnzbdplus/SABnzbd.py') then begin
          cmd:='cd /usr/share/sabnzbdplus && ./SABnzbd.py -t /usr/share/sabnzbdplus/interfaces/smpl -f '+sabnzbdplusDir+'/sabnzbd.ini -d -s '+sabnzbdplusIpAddr+':'+IntToStr(sabnzbdplusPort);
   end;
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: sabnzbdplus (timeout!!!)');
       logs.DebugLogs('Starting......: sabnzbdplus "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: sabnzbdplus (failed!!!)');
       logs.DebugLogs('Starting......: sabnzbdplus "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: sabnzbdplus started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tsabnzbdplus.STATUS():string;
var
pidpath:string;
begin
    if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --sabnzbdplus >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tsabnzbdplus.PID_NUM():string;
begin
if FileExists('/usr/share/sabnzbdplus/SABnzbd.py') then exit(SYS.PIDOF_PATTERN('SABnzbd.py'));
 exit(SYS.PIDOF_PATTERN(binpath));
end;
 //##############################################################################
 function tsabnzbdplus.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_SABNZBDPLUS');
     if length(result)>2 then exit;
     if not FileExists(binpath) then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath +' --version >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='sabnzbdplus-([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_SABNZBDPLUS',result);
l.free;
RegExpr.free;
end;
//##############################################################################
// ufdbguardd
end.
