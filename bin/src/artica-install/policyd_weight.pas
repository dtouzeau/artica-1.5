unit policyd_weight;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  tpolicyd_weight=class


private
     LOGS:Tlogs;
     D:boolean;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     EnablePolicydWeight:integer;
    CONFIG_ARRAY:TstringList;
    EnablePostfixMultiInstance:integer;

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  BIN_PATH():string;
    function  CONFIG_PATH():string;
    function  VERSION():string;
    function  PID_NUM():string;
    procedure START();
    procedure STOP();
    function  STATUS():string;
    function  INIT_PATH():string;
   function GET_VALUE(key:string):string;
   procedure VERIFCONFIG();

END;

implementation

constructor tpolicyd_weight.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       CONFIG_ARRAY:=Tstringlist.Create;
       if FileExists(CONFIG_PATH()) then begin
          try
             CONFIG_ARRAY.LoadFromFile(CONFIG_PATH());
          except
          end;
       end;


       if not TryStrToInt(SYS.GET_INFO('EnablePolicydWeight'),EnablePolicydWeight) then begin
          EnablePolicydWeight:=0;
          SYS.set_INFO('EnablePolicydWeight','0');
       end;

       if not TryStrToInt(SYS.GET_INFO('EnablePostfixMultiInstance'),EnablePostfixMultiInstance) then EnablePostfixMultiInstance:=0;
       if EnablePostfixMultiInstance=1 then EnablePolicydWeight:=0;






       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tpolicyd_weight.free();
begin
    FreeAndNil(logs);
    FreeAndNil(CONFIG_ARRAY);
end;
//##############################################################################
function tpolicyd_weight.CONFIG_PATH():string;
var
   l:Tstringlist;
   i:integer;
begin
    l:=Tstringlist.Create;
    l.Add('/etc/policyd-weight.conf');
    l.Add('/etc/postfix/policyd-weight.cf');
    l.Add('/usr/local/etc/policyd-weight.conf');
    for i:=0 to l.Count-1 do begin
        if FileExists(l.Strings[i]) then begin
           result:=l.Strings[i];
           l.free;
           exit;
        end;
    end;

result:='/etc/policyd-weight.conf';
end;
//##############################################################################
function tpolicyd_weight.INIT_PATH():string;
var
   l:Tstringlist;
   i:integer;
begin
    l:=Tstringlist.Create;
    l.Add('/etc/init.d/policyd-weight');
    for i:=0 to l.Count-1 do begin
        if FileExists(l.Strings[i]) then begin
           result:=l.Strings[i];
           l.free;
           exit;
        end;
    end;
end;
//##############################################################################
function tpolicyd_weight.BIN_PATH():string;
begin
     if FileExists('/usr/sbin/policyd-weight') then exit('/usr/sbin/policyd-weight');
     if FileExists('/usr/local/sbin/policyd-weight') then exit('/usr/local/sbin/policyd-weight');
     if FileExists('/usr/share/artica-postfix/bin/policyd-weight') then exit('/usr/share/artica-postfix/bin/policyd-weight');
end;
//##############################################################################
function tpolicyd_weight.GET_VALUE(key:string):string;
var
   i:integer;
   RegExpr:TRegExpr;
begin
   if CONFIG_ARRAY.Count=0 then begin
      if FileExists(CONFIG_PATH()) then begin
         try
            CONFIG_ARRAY.LoadFromFile(CONFIG_PATH());
         except
               logs.Syslogs('tpolicyd_weight.GET_VALUE() fatal error while reading configuration file');
         end;
      end;
   end;

   if CONFIG_ARRAY.Count=0 then exit();

   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='\$'+key+'.*?=(.+?);';

   for i:=0 to CONFIG_ARRAY.Count-1 do begin
         if RegExpr.Exec(CONFIG_ARRAY.Strings[i]) then begin
            result:=RegExpr.Match[1];
            result:=AnsiReplaceText(result,';','');
            break;
         end;
   end;


end;
//##############################################################################

function tpolicyd_weight.VERSION():string;
  var
   RegExpr:TRegExpr;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin
 path:=BIN_PATH();
     if not FileExists(path) then begin
        logs.Debuglogs('tpolicyd_weight.VERSION():: policyd-weight is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_POLICYD_WEIGHT');
   if length(result)>0 then exit;
   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -v >'+tmpstr+' 2>&1');

     if not FileExists(tmpstr) then exit;
     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='policyd-weight version:.+?\s+([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            result:=trim(result);
            result:=AnsiReplaceText(result,'"','');
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_POLICYD_WEIGHT',result);
logs.Debuglogs('APP_POLICYD_WEIGHT:: -> ' + result);
end;
//#############################################################################
procedure tpolicyd_weight.START();
var
   pid:string;
   count:integer;
   LOCK_PATH:string;
   pid_path:string;
   configpath:string;
   binpath:string;
begin

    if not FileExists(BIN_PATH()) then begin
    logs.DebugLogs('Starting......: policyd-weight is not installed');
    exit;
    end;

    if EnablePolicydWeight=0 then begin
        logs.DebugLogs('Starting......: policyd-weight is disabled');
        exit;
    end;



  pid:=PID_NUM();

  LOCK_PATH:=GET_VALUE('LOCK_PATH');

if SYS.PROCESS_EXIST(pid) then begin
     logs.DebugLogs('Starting......: policyd-weight already running using PID ' +pid+ '...');
     exit;
end;

VERIFCONFIG();
configpath:=CONFIG_PATH();
binpath:=BIN_PATH();
pid_path:=GET_VALUE('PIDFILE');
logs.DebugLogs('Starting......: policyd-weight Pid path...:'+pid_path);
logs.DebugLogs('Starting......: policyd-weight lock path..:'+LOCK_PATH);
logs.DebugLogs('Starting......: policyd-weight config path:'+configpath);
logs.DebugLogs('Starting......: policyd-weight bin path...:'+binpath);
logs.DebugLogs('Starting......: policyd-weight PID......:'+pid);

 if FileExists(INIT_PATH()) then begin
    fpsystem(INIT_PATH()+' start');
 end else begin
    fpsystem(BIN_PATH()+' -k start');
 end;



 count:=0;
 while not SYS.PROCESS_EXIST(PID_NUM()) do begin
              sleep(150);
              inc(count);
              if count>20 then begin
                 logs.DebugLogs('Starting......: policyd-weight daemon. (timeout!!!)');
                 break;
              end;
        end;



if  not SYS.PROCESS_EXIST(PID_NUM()) then begin
    logs.DebugLogs('Starting......: policyd-weight daemon failed');
    exit;
end;

logs.DebugLogs('Starting......: policyd-weight daemon success with new PID ' + PID_NUM());



end;
//##############################################################################
procedure tpolicyd_weight.VERIFCONFIG();

var
   configpath:string;

begin
configpath:=CONFIG_PATH();
forceDirectories('/tmp/.policyd-weight');
fpsystem('/bin/chown postfix:postfix /tmp/.policyd-weight');


 if not FileExists('/etc/artica-postfix/settings/Daemons/PolicydWeightConfig') then begin
   fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.postfix.maincf.php --policyd-reconfigure');
end;

if FileExists('/etc/artica-postfix/settings/Daemons/PolicydWeightConfig') then begin
   fpsystem('/bin/cp -f /etc/artica-postfix/settings/Daemons/PolicydWeightConfig '+configpath);
end else begin
   logs.DebugLogs('Starting......: policyd-weight unable to stat PolicydWeightConfig artica config');
end;

ForceDirectories('/var/run/policyd-weight');
fpsystem('/bin/chown postfix:postfix /var/run/policyd-weight');
if FileExists(configpath) then fpsystem('/bin/chown postfix:postfix '+ configpath);
fpsystem('/bin/chmod 770 /var/run/policyd-weight');


end;


function tpolicyd_weight.PID_NUM():string;
var
   pid_path:string;

begin
     pid_path:=GET_VALUE('PIDFILE');
     if FileExists(pid_path) then begin
        result:=SYS.GET_PID_FROM_PATH(pid_path);
        if not SYS.PROCESS_EXIST(result) then result:='';
     end;



     if length(trim(result))=0 then result:=SYS.PIDOF('policyd-weight');
end;
//##############################################################################
procedure tpolicyd_weight.STOP();
var
   count:integer;
begin

    if not FileExists(BIN_PATH()) then begin
    writeln('Stopping policyd-weight......: Not installed');
    exit;
    end;

if  not SYS.PROCESS_EXIST(PID_NUM()) then begin
     writeln('Stopping policyd-weight......: Already stopped');
    exit;
end;

writeln('Stopping policyd-weight......:  ' + PID_NUM() + ' PID..');
fpsystem(BIN_PATH()+ ' -k stop');


  count:=0;
  while SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(100);
        count:=count+1;
        writeln('Stopping policyd-weight......:  ' + PID_NUM() + ' PID..');
        if count>20 then begin
            fpsystem('/bin/kill -9 ' + PID_NUM());
            break;
        end;
  end;

  count:=0;
  while SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(100);
        count:=count+1;
        writeln('Stopping policyd-weight......:  ' + PID_NUM() + ' PID..');
        if count>20 then begin
            fpsystem('/bin/kill -9 ' + PID_NUM());
            break;
        end;
  end;

if  not SYS.PROCESS_EXIST(PID_NUM()) then begin
    writeln('Stopping policyd-weight......:  success');
    VERIFCONFIG();
    exit;
end;

    VERIFCONFIG();
    writeln('Stopping policyd-weight......:  failed');

end;


//#############################################################################

function tpolicyd_weight.STATUS();
var
   pidpath:string;
begin
SYS.MONIT_DELETE('APP_POLICYD_WEIGHT');
if not FileExists(BIN_PATH()) then exit;
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --policydw >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//##############################################################################
end.
