unit iscsitarget;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr in 'RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas',
    bind9   in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/bind9.pas';

  type
  tiscsitarget=class


private
     LOGS:Tlogs;
     artica_path:string;
     EnableISCSI:integer;
     daemonbin:string;
     SYS:Tsystem;
     function initd_path():string;
     function PID_PATH():string;
     procedure ETC_DEFAULT();
public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
      function  DAEMON_BIN_PATH():string;
      function  VERSION:string;
      procedure START();
      procedure STOP();
      function  PID_NUM():string;




END;

implementation

constructor tiscsitarget.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if Not TryStrToInt(SYS.GET_INFO('EnableISCSI'),EnableISCSI) then EnableISCSI:=0;
       daemonbin:=SYS.LOCATE_GENERIC_BIN('ietd');
       if Not FileExists(daemonbin) then EnableISCSI:=0;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tiscsitarget.free();
begin
    logs.Free;
end;
//##############################################################################
function tiscsitarget.DAEMON_BIN_PATH():string;
begin
    exit(daemonbin);
end;
//#############################################################################
function tiscsitarget.VERSION:string;
var
   binPath:string;
    mem:TStringList;
    commandline:string;
    tmp_file:string;
    RegExpr:TRegExpr;
    i:integer;
begin
    binPath:=SYS.LOCATE_GENERIC_BIN('ietadm');

    if not FileExists(binpath) then begin
       exit;
    end;
    result:=trim(SYS.GET_CACHE_VERSION('APP_IETD'));
    if length(result)>2 then exit();
    tmp_file:=logs.FILE_TEMP();
    commandline:=binPath+' --version >'+tmp_file +' 2>&1';
    fpsystem(commandline);
    mem:=TStringList.Create;
    if not FileExists(tmp_file) then exit;
    mem.LoadFromFile(tmp_file);
    logs.DeleteFile(tmp_file);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='version\s+([0-9\.]+)';

     for i:=0 to mem.Count-1 do begin
       if RegExpr.Exec(mem.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;

     end;
     SYS.SET_CACHE_VERSION('APP_IETD',result);
     mem.Free;
     RegExpr.Free;

end;
//#############################################################################
procedure tiscsitarget.START();
var
   bin_path,pid,cache,cachecmd:string;
   init:string;

begin

    bin_path:=daemonbin;
    if not FileExists(bin_path) then begin
       logs.DebugLogs('Starting......: ietd is not installed...');
       exit;
    end;



    pid:=PID_NUM();

     if SYS.PROCESS_EXIST(pid) then begin
        if EnableISCSI=0 then begin
           logs.DebugLogs('Starting......: ietd is disabled, shutdown');
           STOP();
           exit;
        end;
         logs.DebugLogs('Starting......: ietd already running PID '+pid);
     end;

     ETC_DEFAULT();

      if EnableISCSI=0 then begin
         logs.DebugLogs('Starting......: ietd is disabled, aborting');
         exit;
      end;

     init:=initd_path();
     if not FileExists(init) then begin
        logs.Debuglogs('Starting......: ietd init.d no such file');
        exit;
     end;

     fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.iscsi.php --build');

     fpsystem(init+' start');
     pid:=PID_NUM();
      if SYS.PROCESS_EXIST(pid) then begin
           logs.DebugLogs('Starting......: ietd success PID '+ pid);
           exit;
      end;

     logs.DebugLogs('Starting......: ietd failed '+ init);

end;
//##############################################################################
function tiscsitarget.PID_NUM():string;
begin
result:=SYS.GET_PID_FROM_PATH(PID_PATH());
if not SYS.PROCESS_EXIST(result) then result:=SYS.GET_PID_FROM_PATH(daemonbin);
end;
//##############################################################################
function tiscsitarget.PID_PATH():string;
begin
     if FileExists('/var/run/ietd.pid') then exit('/var/run/ietd.pid');
     if FileExists('/var/run/iscsi_trgt.pid') then exit('/var/run/iscsi_trgt.pid');
end;
//##############################################################################
function tiscsitarget.initd_path():string;
begin
     if FileExists('/etc/init.d/iscsitarget') then exit('/etc/init.d/iscsitarget');
end;
//##############################################################################
procedure tiscsitarget.ETC_DEFAULT();
var
   l:Tstringlist;
begin
     l:=Tstringlist.Create;

   if EnableISCSI=0 then begin
      l.Add('ISCSITARGET_ENABLE=false');
   end else begin
       l.Add('ISCSITARGET_ENABLE=true');
   end;

logs.WriteToFile(l.Text,'/etc/default/iscsitarget');
l.free;

end;



procedure tiscsitarget.STOP();
var bin_path,pid,init:string;
count:integer;
begin

    bin_path:=daemonbin;
    if not FileExists(bin_path) then exit;
    pid:=PID_NUM();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping ietd............: Already stopped');
       exit;
    end;

    init:=initd_path();
     if not FileExists(init) then begin
        writeln('Stopping ietd............: ietd init.d no such file');
        exit;
     end;

 fpsystem(init+' stop');
     pid:=PID_NUM();
      if SYS.PROCESS_EXIST(pid) then begin
            writeln('Stopping ietd............: failed ',init);
           exit;
      end;

     writeln('Stopping ietd............: Success');

end;
//##############################################################################
end.

