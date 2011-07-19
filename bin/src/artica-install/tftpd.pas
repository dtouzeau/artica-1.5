unit tftpd;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  ttftpd=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableTFTPD:integer;
     binpath:string;
     D:boolean;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;



END;

implementation

constructor ttftpd.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       D:=SYS.verbosed;
       if not TryStrToInt(SYS.GET_INFO('EnableTFTPD'),EnableTFTPD) then EnableTFTPD:=1;
end;
//##############################################################################
procedure ttftpd.free();
begin
    logs.Free;
end;
//##############################################################################

procedure ttftpd.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping TFTP Daemon.........: Not installed');
   exit;
end;

        writeln('Stopping TFTP Daemon.........: remove from inetd');
        fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.tftpd.php --remove-inetd');
        writeln('Stopping TFTP Daemon.........: Stopped');
end;

//##############################################################################
function ttftpd.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('in.tftpd');
end;
//##############################################################################
procedure ttftpd.START();
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

begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: TFTP Daemon is not installed');
         exit;
   end;

if EnableTFTPD=0 then begin
   logs.DebugLogs('Starting......: TFTP Daemon service is disabled');
   STOP();
   exit;
end;

   logs.DebugLogs('Starting......: TFTP Daemon will use /var/lib/tftpboot');
   forcedirectories('/var/lib/tftpboot');
   logs.DebugLogs('Starting......: TFTP Daemon enable it from inetd..');
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.tftpd.php --add-inetd');
   logs.DebugLogs('Starting......: TFTP Daemon started');

end;
//##############################################################################
function ttftpd.STATUS():string;
var
pidpath:string;
begin
   if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --tftpd >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function ttftpd.PID_NUM():string;
var inetd:string;
begin
 inetd:=SYS.LOCATE_GENERIC_BIN('inetd');
 if Not FileExists(inetd) then inetd:=SYS.LOCATE_GENERIC_BIN('xinetd');
 result:=SYS.PIDOF_PATTERN(SYS.LOCATE_GENERIC_BIN('inetd'));
end;
 //##############################################################################
 function ttftpd.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_TFTPD');
     if length(result)>3 then exit;
     if not FileExists(binpath) then exit;

    tmpstr:=logs.FILE_TEMP();
    if D then writeln(binpath +' -h >'+tmpstr +' 2>&1');
    fpsystem(binpath +' -h >'+tmpstr +' 2>&1');

    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin

            result:=RegExpr.Match[1];
            if D then writeln(result,' ->OK ',l.Strings[i]);
            RegExpr.Expression:='invalid option';
            if not RegExpr.Exec(l.Strings[i]) then break;
            result:='';
         end;
    end;

    if length(result)=0 then begin
         if D then writeln('-> /usr/bin/dpkg');
         if fileExists('/usr/bin/dpkg') then begin
             fpsystem('/usr/bin/dpkg -l|grep tftpd >'+tmpstr +' 2>&1');
             if not FileExists(tmpstr) then exit;
             l:=TstringList.Create;
             l.LoadFromFile(tmpstr);
             logs.DeleteFile(tmpstr);
             RegExpr.Expression:='tftpd.*?\s+([0-9\-\.a-z]+)';
             for i:=0 to l.Count-1 do begin
                 if RegExpr.Exec(l.Strings[i]) then begin
                    result:=RegExpr.Match[1];
                    if D then writeln(result,'"'+l.Strings[i]+'"');
                    break;
                end else begin
                    if D then writeln('-> not found ',l.Strings[i]);
                end;
             end;
         end;
    end;

 SYS.SET_CACHE_VERSION('APP_TFTPD',result);
l.free;
RegExpr.free;
end;
//##############################################################################

end.
