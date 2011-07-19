unit nfsserver;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr,zsystem,openldap;



  type
  tnfs=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     zldap:Topenldap;
     procedure   nfs_kernel_default();
     procedure   nfs_common_default();

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function    VERSION():string;
    function    BIN_PATH():string;
    function    PID_NUM():string;
    procedure   START();
    procedure   STOP();
    function    STATUS:string;
    function    INITD_PATH():string;
    procedure   RELOAD();
END;

implementation

constructor tnfs.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       zldap:=Topenldap.Create;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tnfs.free();
begin
    logs.Free;
    zldap.Free;
end;
//##############################################################################
function tnfs.BIN_PATH():string;
begin
   if FileExists('/usr/sbin/rpc.nfsd') then exit('/usr/sbin/rpc.nfsd');
end;
//##############################################################################
function tnfs.INITD_PATH():string;
begin
if FileExists('/etc/init.d/nfs-kernel-server') then exit('/etc/init.d/nfs-kernel-server');
if FileExists('/etc/init.d/nfs-server') then exit('/etc/init.d/nfs-server');
if FileExists('/etc/init.d/nfsserver') then exit('/etc/init.d/nfsserver');
if FileExists('/etc/init.d/nfs') then exit('/etc/init.d/nfs');
end;
//##############################################################################
function tnfs.PID_NUM():string;
begin
    if not FIleExists(BIN_PATH()) then exit;
    result:=SYS.PIDOF('nfsd');
end;
//##############################################################################
function tnfs.VERSION():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    BinPath:string;
    filetmp:string;
begin

result:=SYS.GET_CACHE_VERSION('APP_NFS');
if length(result)>0 then exit;

filetmp:=logs.FILE_TEMP();
if not FileExists(BIN_PATH()) then exit;
   logs.Debuglogs(BIN_PATH()+' -v >'+filetmp+' 2>&1');
   fpsystem(BIN_PATH()+' -v >'+filetmp+' 2>&1');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='s+([0-9\.]+)';
    FileDatas:=TStringList.Create;
    FileDatas.LoadFromFile(filetmp);
    logs.DeleteFile(filetmp);
    for i:=0 to FileDatas.Count-1 do begin
        if RegExpr.Exec(FileDatas.Strings[i]) then begin
             result:=RegExpr.Match[1];
             break;
        end;
    end;
             RegExpr.free;
             FileDatas.Free;
SYS.SET_CACHE_VERSION('APP_NFS',result);

end;
//#############################################################################
procedure tnfs.RELOAD();
var
   count:integer;
   pid:string;
   exportfs:string;
begin
    if not FileExists('/etc/artica-postfix/settings/Daemons/NFSExportConfig') then exit;
    fpsystem('/bin/cp /etc/artica-postfix/settings/Daemons/NFSExportConfig /etc/exports');
    nfs_common_default();
    nfs_kernel_default();

    pid:=PID_NUM();
    IF not sys.PROCESS_EXIST(pid) then begin
        START();
        exit;
    end;

    exportfs:=SYS.LOCATE_GENERIC_BIN('exportfs');
    if FileExists('exportfs') then begin
       fpsystem(exportfs+' -rav');
       exit;
    end;

    fpsystem(INITD_PATH()+' reload');
    
end;
//#############################################################################
procedure tnfs.START();
var
   count:integer;
   pid:string;
begin
    pid:=PID_NUM();
    IF sys.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: NFS server Already running PID '+ pid);
       exit;
    end;

    if not FileExists(INITD_PATH()) then begin
       logs.DebugLogs('Starting......: NFS server is not installed');
       exit;
    end;

    nfs_common_default();
    nfs_kernel_default();
    if FileExists('/etc/artica-postfix/settings/Daemons/NFSExportConfig') then begin
     logs.DebugLogs('Starting......: NFS server replicate configuration file done.');
     fpsystem('/bin/cp /etc/artica-postfix/settings/Daemons/NFSExportConfig /etc/exports');
    end;


    fpsystem(INITD_PATH()+' start &');
    if FileExists('/etc/init.d/nfs-common') then fpsystem('/etc/init.d/nfs-common start &');

count:=0;
 while not SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(500);
        inc(count);
        if count>10 then begin
           logs.DebugLogs('Starting......: NFS server (timeout)');
           break;
        end;
  end;

pid:=PID_NUM();
    IF sys.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: NFS server successfully started and running PID '+ pid);
       exit;
    end;

logs.DebugLogs('Starting......: NFS server failed');

end;


//#############################################################################
procedure tnfs.STOP();
var
   count:integer;
   pid:string;
   tmp:string;
   l:Tstringlist;
   i:integer;
   tt:integer;
   path:string;
    RegExpr:TRegExpr;
begin
  fpsystem(INITD_PATH()+' stop');
  if FileExists('/etc/init.d/nfs-common') then fpsystem('/etc/init.d/nfs-common stop');


end;


//#############################################################################
procedure tnfs.nfs_common_default();
var
   l:tstringlist;
begin
if not FileExists('/etc/default/nfs-common') then exit;
l:=Tstringlist.Create;
l.Add('NEED_STATD=');
l.Add('STATDOPTS=');
l.Add('NEED_IDMAPD=yes');
l.Add('NEED_GSSD=no');
logs.WriteToFile(l.Text,'/etc/default/nfs-common');
logs.DebugLogs('Starting......: NFS updating /etc/default/nfs-common done');
l.free;
end;
//#############################################################################
procedure tnfs.nfs_kernel_default();
var
   l:tstringlist;
begin
if not FileExists('/etc/default/nfs-kernel-server') then exit;
l:=Tstringlist.Create;
l.Add('RPCNFSDCOUNT=8');
l.Add('RPCNFSDPRIORITY=0');
l.Add('RPCMOUNTDOPTS=');
l.Add('NEED_SVCGSSD=no');
l.Add('RPCSVCGSSDOPTS=');
 logs.WriteToFile(l.Text,'/etc/default/nfs-kernel-server');
logs.DebugLogs('Starting......: NFS updating /etc/default/nfs-kernel-server done');
l.free;
end;
//#############################################################################

function tnfs.STATUS:string;
var
ini:TstringList;
pid:string;
begin
   ini:=TstringList.Create;
   ini.Add('[APP_NFS]');
   if not fileExists(BIN_PATH()) then begin
      ini.Add('application_installed=0');
      ini.Add('service_disabled=0');
      result:=ini.Text;
      ini.free;
      exit;
   end;


   if fileExists(BIN_PATH()) then begin
      pid:=PID_NUM();
      if SYS.PROCESS_EXIST(pid) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('application_enabled=1');
      ini.Add('master_pid='+ pid);
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(pid)));
      ini.Add('master_version='+VERSION());
      ini.Add('status='+SYS.PROCESS_STATUS(pid));
      ini.Add('service_name=APP_NFS');
      ini.Add('service_cmd=nfs');
      ini.Add('service_disabled=1');
   end;

   result:=ini.Text;
   ini.free;

end;
//##############################################################################


end.
