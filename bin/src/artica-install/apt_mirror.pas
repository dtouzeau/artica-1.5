unit apt_mirror;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tapt_mirror=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     EnableAptMirror:integer;
     binpath:string;
     function    base_path():string;
     function    WGET_PROCESSES():string;
    function     STOP_WGET_PROCESSES():string;
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
    function    VERSION():string;

   procedure    RELOAD();


END;

implementation

constructor tapt_mirror.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();
       if not TryStrToInt(SYS.GET_INFO('EnableAptMirror'),EnableAptMirror) then EnableAptMirror:=0;

end;
//##############################################################################
procedure tapt_mirror.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tapt_mirror.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin
if not FileExists(binpath) then begin
   writeln('Stopping apt-mirror..........: Not installed');
   exit;
end;

if not SYS.PROCESS_EXIST(PID_NUM()) then begin
   writeln('Stopping apt-mirror..........: Already Stopped');
   STOP_WGET_PROCESSES();
   exit;
end;
   pidstring:=PID_NUM();
   writeln('Stopping apt-mirror..........: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(100);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping apt-mirror..........: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(PID_NUM()) then  begin
     writeln('Stopping apt-mirror..........: success');
     STOP_WGET_PROCESSES();
     exit;
  end;


   pidstring:=PID_NUM();
   if SYS.PROCESS_EXIST(PID_NUM()) then begin
      writeln('Stopping apt-mirror..........: ' + pidstring + ' PID..');
      cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' -9 '+pidstring+' >/dev/null 2>&1';
      fpsystem(cmd);
   end;
   if not SYS.PROCESS_EXIST(PID_NUM()) then  writeln('Stopping apt-mirror..........: success') else writeln('Stopping apt-mirror..........: Failed');
   STOP_WGET_PROCESSES();
end;

//##############################################################################
function tapt_mirror.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('apt-mirror');
end;
//##############################################################################
procedure tapt_mirror.RELOAD();
var
   pid:string;
begin
logs.DebugLogs('Starting......:  apt-mirror reload  (not implemented)');
exit;
pid:=PID_NUM();
end;
//##############################################################################
procedure tapt_mirror.START();
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
         logs.DebugLogs('Starting......: apt-mirror is not installed');
         exit;
   end;

if EnableAptMirror=0 then begin
   logs.DebugLogs('Starting......: apt-mirror is disabled');
   STOP();
   exit;
end;

if SYS.PROCESS_EXIST(PID_NUM()) then begin
   logs.DebugLogs('Starting......: apt-mirror Already running using PID ' +PID_NUM()+ '...');
   exit;
end;


   cmd:=SYS.LOCATE_GENERIC_BIN('nohup')+' '+ SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.apt-mirror.php --perform >/dev/null 2>&1 &';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(PID_NUM()) do begin
     sleep(100);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: apt-mirror (timeout!!!)');
       logs.DebugLogs('Starting......: apt-mirror "'+cmd+'"');
       break;
     end;
   end;

   if not SYS.PROCESS_EXIST(PID_NUM()) then begin
       logs.DebugLogs('Starting......: apt-mirror (failed!!!)');
       logs.DebugLogs('Starting......: apt-mirror "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: apt-mirror started with new PID '+PID_NUM());
   end;

end;
//##############################################################################
function tapt_mirror.STATUS():string;
var
pidpath:string;
begin

   if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --apt-mirror >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tapt_mirror.PID_NUM():string;
begin
  result:=SYS.PIDOF_PATTERN(binpath);
  if sys.verbosed then logs.Debuglogs(' ->'+result);
end;
 //##############################################################################
  function tapt_mirror.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
   binpath:string;
   dpkg_bin:string;
begin
    binpath:=SYS.LOCATE_GENERIC_BIN('apt-mirror');
    dpkg_bin:=SYS.LOCATE_GENERIC_BIN('dpkg');
    if Not FileExists(binpath) then exit;
    if Not FileExists(dpkg_bin) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_APT_MIRROR');
   if length(result)>2 then begin
      if SYS.verbosed then writeln('APT_MIRROR_VERSION():',result,' from memory');
      exit;
   end;


    tmpstr:=logs.FILE_TEMP();
    fpsystem(dpkg_bin+' -l |grep apt-mirror >'+tmpstr+' 2>&1');
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^ii\s+.+?\s+([0-9\-\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;

 if SYS.verbosed then writeln('APT_MIRROR_VERSION(): "',result,'"');
 SYS.SET_CACHE_VERSION('APP_APT_MIRROR',result);
l.free;
RegExpr.free;
end;
//##############################################################################
 function tapt_mirror.base_path():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
   binpath:string;
   dpkg_bin:string;
begin
   if not FIleExists('/etc/apt/mirror.list') then exit;
   l:=Tstringlist.Create;
   l.LoadFromFile('/etc/apt/mirror.list');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='set base_path\s+(.+)';
   for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=trim(RegExpr.Match[1]);
          break;
       end;

   end;

   l.free;
   RegExpr.free;
end;
//##############################################################################
function tapt_mirror.STOP_WGET_PROCESSES():string;
var
   pids:string;
   count:integer;
begin
   pids:=trim(WGET_PROCESSES());
   if length(trim(pids))=0 then begin
       writeln('Stopping apt-mirror..........: checking wget processes done');
       exit;
   end;

   writeln('Stopping apt-mirror..........: wget processes: '+pids);
   fpsystem(SYS.LOCATE_GENERIC_BIN('kill')+' '+pids+' >/dev/null 2>&1');
   count:=0;
   pids:=WGET_PROCESSES();

 while length(trim(pids))>0 do begin
   sleep(200);
   count:=count+1;
   if count>50 then break;
   pids:=WGET_PROCESSES();
   if length(trim(pids))>0 then fpsystem(SYS.LOCATE_GENERIC_BIN('kill')+' '+pids+' >/dev/null 2>&1');
  end;
     writeln('Stopping apt-mirror..........: checking wget processes done');

end;
//##############################################################################
function tapt_mirror.WGET_PROCESSES():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
   binpath:string;
   dpkg_bin:string;
   path:string;
begin
     path:=base_path();
     if not DirectoryExists(path) then begin
        writeln('Stopping apt-mirror..........: unable to stat base_path');
        exit;
     end;
     tmpstr:=logs.FILE_TEMP();
     fpsystem(SYS.LOCATE_GENERIC_BIN('pgrep')+' -l -f "'+base_path+'/var/.+?log" >'+tmpstr+' 2>&1');
     l:=Tstringlist.Create;
     l.LoadFromFile(tmpstr);
     logs.DeleteFile(tmpstr);
     RegExpr:=TRegExpr.Create;

     for i:=0 to l.Count-1 do begin
         RegExpr.Expression:='pgrep';
         if RegExpr.Exec(l.Strings[i]) then continue;

         RegExpr.Expression:='^([0-9]+)\s+';
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=result+' '+RegExpr.Match[1];
         end;

     end;

     RegExpr.free;
     l.free;
end;
//##############################################################################



end.
