unit fusermount;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,IniFiles;



  type
  tfusermount=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     binpath:string;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    procedure   STOP();
    function    STATUS():string;
    function    VERSION():string;
    function    BIN_PATH():string;
    function    ISLOADED():boolean;
    function    ZFS_PID_NUM():string;
    procedure   ZFS_START();
    procedure   ZFS_STOP();
    function    ZFS_VERSION():string;
    procedure   modprobed();
   function     LESSFS_VERSION():string;
   function     TOKYOCABINET_VERSION():string;
END;

implementation

constructor tfusermount.Create(const zSYS:Tsystem);
begin

       LOGS:=tlogs.Create();
       SYS:=zSYS;
       binpath:=BIN_PATH();

end;
//##############################################################################
procedure tfusermount.free();
begin
    logs.Free;
end;
//##############################################################################

procedure tfusermount.STOP();
var
   count:integer;
   RegExpr:TRegExpr;
   cmd:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
   modprobe,umount,mount:string;
begin
if not FileExists(binpath) then begin
   writeln('Stopping Fuse filesystem.....: Not installed');
   exit;
end;

if not ISLOADED() then begin
        writeln('Stopping Fuse filesystem.....: already unloaded');
        exit;
end;

    if FileExists('/etc/init.d/fuse') then begin
       fpsystem('/etc/init.d/fuse stop');
       ZFS_STOP();
       exit;
    end;

     umount:=SYS.LOCATE_GENERIC_BIN('umount');
     fpsystem('{ if grep -q " /sys/fs/fuse/connections " /proc/mounts; then '+umount+' /sys/fs/fuse/connections; fi }');
     modprobe:=SYS.LOCATE_GENERIC_BIN('modprobe');
     writeln('Stopping Fuse filesystem.....: unloading module');
     cmd:=modprobe+' -r --ignore-remove fuse';
     fpsystem(cmd);

  if not ISLOADED() then  begin
     writeln('Stopping Fuse filesystem.....: Stopped');
     ZFS_STOP();
     exit;
  end else begin
     mount:=SYS.LOCATE_GENERIC_BIN('mount');
     writeln('Stopping Fuse filesystem.....: failed remount fusectl');
     fpsystem('if grep -q fusectl /proc/filesystems; then '+mount+' -t fusectl fusectl /sys/fs/fuse/connections; fi ; : ;');
  end;
end;

//##############################################################################
function tfusermount.BIN_PATH():string;
begin
result:=SYS.LOCATE_GENERIC_BIN('fusermount');
end;
//##############################################################################
function tfusermount.ISLOADED():boolean;
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:integer;
begin
   l:=TstringList.CReate;
   l.LoadFromFile('/proc/filesystems');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='nodev\s+fuse';
   result:=false;
   for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
            result:=true;
            break;
        end;
   end;

   RegExpr.free;
   l.free;
end;
//##############################################################################
procedure tfusermount.modprobed();
var
   l:TstringList;
   modprobe:string;
   mount:string;
  umount:string;
begin
  if FileExists('/etc/modprobe.d/fuse') then exit;
  modprobe:=SYS.LOCATE_GENERIC_BIN('modprobe');
  mount:=SYS.LOCATE_GENERIC_BIN('mount');
  umount:=SYS.LOCATE_GENERIC_BIN('umount');
  logs.DebugLogs('Starting......: Fuse filesystem modprobe: "'+modprobe+'"');
  logs.DebugLogs('Starting......: Fuse filesystem mount...: "'+mount+'"');
  l:=TstringList.CReate;
  l.Add('install fuse '+modprobe+' --ignore-install fuse && \');
  l.Add('        { if grep -q fusectl /proc/filesystems; then '+mount+' -t fusectl fusectl /sys/fs/fuse/connections; fi ; : ; }');
  l.Add('remove fuse { if grep -q " /sys/fs/fuse/connections " /proc/mounts; then '+umount+' /sys/fs/fuse/connections; fi } && \');
  l.Add('        /sbin/modprobe -r --ignore-remove fuse');
  logs.WriteToFile(l.Text,'/etc/modprobe.d/fuse');
  logs.DebugLogs('Starting......: Fuse filesystem /etc/modprobe.d/fuse success');

end;
//##############################################################################
function tfusermount.ZFS_PID_NUM():string;
var
   pid:string;
   zfs_binpath:string;
begin

     pid:=SYS.GET_PID_FROM_PATH('/var/run/zfs.pid');
     if not SYS.PROCESS_EXIST(pid) then begin
        zfs_binpath:=SYS.LOCATE_GENERIC_BIN('zfs-fuse');
        exit(SYS.PIDOF(zfs_binpath));
     end;
     result:=pid;

end;
//##############################################################################
function tfusermount.ZFS_VERSION():string;
begin
   if not FileExists(SYS.LOCATE_GENERIC_BIN('zfs-fuse')) then exit;
   result:='Not Applicable';
end;
//##############################################################################
function tfusermount.TOKYOCABINET_VERSION():string;
begin
   if not FileExists('/usr/lib/libtokyocabinet.so') then exit;
   result:='Not Applicable';
end;
//##############################################################################

procedure tfusermount.ZFS_START();
var
   count:integer;
   cmd:string;
   su,nohup:string;
   conf:TiniFile;
   enabled:integer;
   RegExpr:TRegExpr;
   zfs_binpath:string;
   tmpfile:string;
   cmdline:string;
   sabnzbdplusDir:string;
   sabnzbdplusIpAddr:string;
   sabnzbdplusPort:integer;
begin

  zfs_binpath:=SYS.LOCATE_GENERIC_BIN('zfs-fuse');

   if not FileExists(zfs_binpath) then begin
         logs.DebugLogs('Starting......: ZFS Fuse filesystem not installed');
         exit;
   end;

if SYS.PROCESS_EXIST(ZFS_PID_NUM()) then begin
   logs.DebugLogs('Starting......:  ZFS Fuse filesystem Already running using PID ' +ZFS_PID_NUM()+ '...');
   exit;
end;

   cmd:=zfs_binpath+' -p /var/run/zfs.pid';
   fpsystem(cmd);
   count:=0;
   while not SYS.PROCESS_EXIST(ZFS_PID_NUM()) do begin
     sleep(300);
     inc(count);
     if count>50 then begin
       logs.DebugLogs('Starting......: ZFS Fuse filesystem (timeout!!!)');
       logs.DebugLogs('Starting......: ZFS Fuse filesystem "'+cmd+'"');
       break;
     end;
   end;




   if not SYS.PROCESS_EXIST(ZFS_PID_NUM()) then begin
       logs.DebugLogs('Starting......: ZFS Fuse filesystem (failed!!!)');
       logs.DebugLogs('Starting......: ZFS Fuse filesystem "'+cmd+'"');
   end else begin
       logs.DebugLogs('Starting......: ZFS Fuse filesystem started with new PID '+ZFS_PID_NUM());
   end;

end;
//##############################################################################
procedure tfusermount.ZFS_STOP();
var
   count:integer;
   cmd,zfs_binpath:string;
   pids:Tstringlist;
   pidstring:string;
   fpid,i:integer;
begin

  zfs_binpath:=SYS.LOCATE_GENERIC_BIN('zfs-fuse');

if not FileExists(zfs_binpath) then begin
   writeln('Stopping ZFS Fuse filesystem.: Not installed');
   exit;
end;

   pidstring:=ZFS_PID_NUM();
if not SYS.PROCESS_EXIST(ZFS_PID_NUM()) then begin
        writeln('Stopping ZFS Fuse filesystem.: already Stopped');
        exit;
end;

   writeln('Stopping ZFS Fuse filesystem.: ' + pidstring + ' PID..');
   cmd:=SYS.LOCATE_GENERIC_BIN('kill')+' '+pidstring+' >/dev/null 2>&1';
   fpsystem(cmd);

   count:=0;
   while SYS.PROCESS_EXIST(pidstring) do begin
        sleep(200);
        count:=count+1;
        if count>50 then begin
            if length(pidstring)>0 then begin
               if SYS.PROCESS_EXIST(pidstring) then begin
                  writeln('Stopping ZFS Fuse filesystem.: kill pid '+ pidstring+' after timeout');
                  fpsystem('/bin/kill -9 ' + pidstring);
               end;
            end;
            break;
        end;
        pidstring:=ZFS_PID_NUM();
  end;

  if not SYS.PROCESS_EXIST(ZFS_PID_NUM()) then  writeln('Stopping ZFS Fuse filesystem.: Stopped');
end;

//##############################################################################





procedure tfusermount.START();
var
   cmd:string;
   servername:string;
   tmpfile:string;
   cmdline:string;
   modprobe:string;
   mount:string;
begin

   if not FileExists(binpath) then begin
         logs.DebugLogs('Starting......: Fuse filesystem not installed');
         exit;
   end;

   if not DirectoryExists('/sys/fs/fuse/connections') then begin
      forceDirectories('/sys/fs/fuse/connections');
      fpsystem('/bin/chmod 755 /sys/fs/fuse/connections');
   end;

   modprobed();

if ISLOADED() then begin
   logs.DebugLogs('Starting......: Fuse filesystem module version '+VERSION()+' Already loaded...');
   ZFS_START();
   exit;
end;
   logs.DebugLogs('Starting......: Fuse filesystem loading module fuse version '+VERSION());

    if FileExists('/etc/init.d/fuse') then begin
       fpsystem('/etc/init.d/fuse start');
       ZFS_START();
       exit;
    end;


   modprobe:=SYS.LOCATE_GENERIC_BIN('modprobe');
   mount:=SYS.LOCATE_GENERIC_BIN('mount');
   logs.DebugLogs('Starting......: Fuse filesystem loading module fuse using '+modprobe);

   fpsystem(modprobe+' --ignore-install fuse');

   if not ISLOADED() then begin
       logs.DebugLogs('Starting......: Fuse filesystem (failed!!!)');
   end else begin
       logs.DebugLogs('Starting......: Fuse filesystem loaded');
       fpsystem('if grep -q fusectl /proc/filesystems; then '+mount+' -t fusectl fusectl /sys/fs/fuse/connections; fi ; : ;');
       ZFS_START();
   end;

end;
//##############################################################################
function tfusermount.STATUS():string;
var
pidpath:string;
begin
    if not FileExists(binpath) then exit;
   pidpath:=logs.FILE_TEMP();
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --fuse >'+pidpath +' 2>&1');
   result:=logs.ReadFromFile(pidpath);
   logs.DeleteFile(pidpath);
end;
//#########################################################################################
 function tfusermount.VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin

    if length(binpath)=0 then exit;
    if Not Fileexists(binpath) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_FUSE');
     if length(result)>2 then exit;
     if not FileExists(binpath) then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem(binpath +' -V >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='fusermount version:\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_FUSE',result);
l.free;
RegExpr.free;
end;
//##############################################################################
 function tfusermount.LESSFS_VERSION():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
   lessfs_bin:string;
begin
    lessfs_bin:=SYS.LOCATE_GENERIC_BIN('lessfs');
    if length(lessfs_bin)=0 then exit;
    if Not Fileexists(lessfs_bin) then exit;
    result:=SYS.GET_CACHE_VERSION('APP_LESSFS');
    if length(result)>2 then exit;

    tmpstr:=logs.FILE_TEMP();
    fpsystem(lessfs_bin +' -V >'+tmpstr +' 2>&1');
    if not FileExists(tmpstr) then exit;
    l:=TstringList.Create;
    l.LoadFromFile(tmpstr);
    logs.DeleteFile(tmpstr);

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='lessfs\s+([0-9\.]+)';
    for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
    end;
 SYS.SET_CACHE_VERSION('APP_LESSFS',result);
l.free;
RegExpr.free;
end;
//##############################################################################
// ufdbguardd
end.
