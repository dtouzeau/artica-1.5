unit mldonkey;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tmldonkey=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableMLDonKey:integer;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    PID():string;
     function   VERSION():string;
END;

implementation

constructor tmldonkey.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if not TryStrToInt(SYS.GET_INFO('EnableMLDonKey'),EnableMLDonKey) then EnableMLDonKey:=1;

end;
//##############################################################################
procedure tmldonkey.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tmldonkey.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   servername:string;
begin
if not FileExists(SYS.LOCATE_GENERIC_BIN('mlnet')) then begin
   writeln('Stopping MLDonkey............: Not installed');
   exit;
end;
if SYS.PROCESS_EXIST(PID()) then begin
   writeln('Stopping MLDonkey............: ' + PID() + ' PID..');
   servername:=SYS.HOSTNAME_g();
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^(.+?)\.';
   if RegExpr.Exec(servername) then servername:=RegExpr.Match[1];
   //logs.DebugLogs('Stopping MLDonkey............: MLDonkey identity "'+servername+'"');
   forceDirectories('/root/.mldonkey');
   //fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mldonkey.php --build --server='+servername+' --');

   fpsystem('/bin/kill ' + PID());
      count:=0;
     while SYS.PROCESS_EXIST(PID()) do begin
        sleep(200);
        count:=count+1;
        if count>20 then begin
            if length(PID())>0 then begin
               if SYS.PROCESS_EXIST(PID()) then begin
                  writeln('Stopping MLDonkey............: kill pid '+ PID()+' after timeout');
                  fpsystem('/bin/kill -9 ' + PID());
               end;
            end;
            break;
        end;
  end;


end else begin
   writeln('Stopping MLDonkey............: Already stopped');
end;
end;
 //##############################################################################

procedure tmldonkey.START();
var
   count:integer;
   cmd:string;
   binpath,su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   servername:string;
   tmpfile:string;
   http_port:integer;
begin
   binpath:=SYS.LOCATE_GENERIC_BIN('mlnet');


   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: MLDonkey not installed');
         exit;
   end;

if EnableMLDonKey=0 then begin
   logs.DebugLogs('Starting......: MLDonkey is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID()) then begin
   logs.DebugLogs('Starting......: MLDonkey Already running using PID ' +PID()+ '...');
   exit;
end;

   if not TryStrToInt(SYS.GET_INFO('MldonkeyHTTPPort'),http_port) then http_port:=0;
   if http_port>0 then logs.DebugLogs('Starting......: MLDonkey HTTP Port:'+ IntTostr(http_port)+ '...');
   su:=SYS.LOCATE_GENERIC_BIN('su');
   nohup:=SYS.LOCATE_GENERIC_BIN('nohup');
   servername:=SYS.HOSTNAME_g();
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^(.+?)\.';
   if RegExpr.Exec(servername) then servername:=RegExpr.Match[1];
   logs.DebugLogs('Starting......: MLDonkey identity "'+servername+'"');
   fpsystem('/bin/rm /root/.mldonkey/*.tmp >/dev/null 2>&1');

forceDirectories('/root/.mldonkey');
//fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mldonkey.php --build --server='+servername+' --');

   tmpfile:=logs.FILE_TEMP();
   cmd:=su +' root -c "'+nohup+' '+ binpath+' -pid /var/run -allowed_ips "127.0.0.0/8" gui_port 0 -http_port '+intToStr(http_port)+' -telnet_port 4000 -telnet_bind_addr 127.0.0.1 -improved_telnet true -log_to_syslog true &> '+tmpfile+' 2>&1" &>/dev/null 2>&1';
   logs.OutputCmd(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID()) do begin
     sleep(150);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: MLDonkey. (timeout!!!)');
       logs.DebugLogs('Starting......: MLDonkey "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID()) then begin
       logs.DebugLogs('Starting......: MLDonkey. (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: MLDonkey. PID '+PID());
   end;

end;
//##############################################################################
function tmldonkey.STATUS():string;
var
pidpath:string;
begin
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --mldonkey >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tmldonkey.PID():string;
begin
  result:=SYS.GET_PID_FROM_PATH('/var/run/mlnet.pid');
end;
 //##############################################################################
 function tmldonkey.VERSION():string;
var
   l:TstringList;
   i:integer;
   binpath,tmpstr:string;
   RegExpr:TRegExpr;
begin
    binpath:=SYS.LOCATE_GENERIC_BIN('mlnet');
    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_MLDONKEY');
   if length(result)>2 then exit;
    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath+' -v >'+tmpstr+' 2>&1');
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='MLDonkey\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_MLDONKEY',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
