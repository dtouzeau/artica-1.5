unit hamachi;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  thamachi=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     CONFIG_ARRAY:Tinifile;
     EnableHamachi:integer;
public
    NETLIST:Tstringlist;
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  BIN_PATH():string;
    function  VERSION():string;
    function  PID_NUM():string;
    procedure START();
    procedure STOP();
    function  STATUS():string;
    function GET_VALUE(key:string):string;

    procedure NETWORK_LIST();

END;

implementation

constructor thamachi.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       EnableHamachi:=1;
       if not TryStrToInt(SYS.GET_INFO('EnableHamachi'),EnableHamachi) then EnableHamachi:=1;

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure thamachi.free();
begin
    FreeAndNil(logs);
    FreeAndNil(CONFIG_ARRAY);
end;
//##############################################################################
function thamachi.BIN_PATH():string;
begin
     if FileExists('/usr/bin/hamachi') then exit('/usr/bin/hamachi');
end;
//##############################################################################
function thamachi.GET_VALUE(key:string):string;
begin
 if FileExists('/etc/artica-postfix/settings/Daemons/HamashiSettings') then begin
          try
             CONFIG_ARRAY:=Tinifile.Create('/etc/artica-postfix/settings/Daemons/HamashiSettings');
             result:=CONFIG_ARRAY.ReadString('SETUP',key,'');
          except
          end;
       end;


end;
//##############################################################################

function thamachi.VERSION():string;
  var
   RegExpr:TRegExpr;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin
 path:=BIN_PATH();
     if not FileExists(path) then begin
        logs.Debuglogs('thamachi.VERSION():: hamachi is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_AMACHI');
   if length(result)>1 then exit;
   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -c /etc/hamachi >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='version.+?-([0-9\.\-]+)';
     for i:=0 to l.Count-1 do begin
         writeln(l.Strings[i]);
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            result:=trim(result);
            result:=AnsiReplaceText(result,'"','');
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_AMACHI',result);
logs.Debuglogs('APP_AMACHI:: -> ' + result);
end;
//#############################################################################
procedure thamachi.START();
var
   pid:string;
   count:integer;
   l:Tstringlist;


begin


    if not FileExists(BIN_PATH()) then begin
    logs.DebugLogs('Starting......: amachi is not installed');
    exit;
    end;

    if EnableHamachi=0 then begin
      logs.DebugLogs('Starting......: amachi is disabled');
      STOP();
      exit;
    end;

  pid:=PID_NUM();
  forceDirectories('/etc/hamachi');

if SYS.PROCESS_EXIST(pid) then begin
     logs.DebugLogs('Starting......: amachi already running using PID ' +pid+ '...');
     exit;
end;
fpsystem('/sbin/tuncfg >/dev/null 2>&1');

l:=Tstringlist.Create;
l.Add('#!/bin/sh');
l.Add('/sbin/tuncfg >/dev/null 2>&1');
l.Add(BIN_PATH()+' -c /etc/hamachi start ');
l.Add('exit 0');
l.SaveToFile('/etc/hamachi/start.sh');
fpsystem('/bin/chmod 777 /etc/hamachi/start.sh');
fpsystem('/etc/hamachi/start.sh &');

count:=0;
 while not SYS.PROCESS_EXIST(PID_NUM()) do begin
           writeln(count);
              sleep(150);
              inc(count);
              if count>20 then begin
                 logs.DebugLogs('Starting......: hamachi daemon. (timeout!!!)');
                 break;
              end;
        end;


pid:= PID_NUM();
if  not SYS.PROCESS_EXIST(pid) then begin
    logs.DebugLogs('Starting......: hamachi daemon failed');
    exit;
end;
logs.DebugLogs('Starting......: hamachi daemon success with new PID ' + pid);
SYS.cpulimit(pid);


fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.hamachi.php &');
end;
//##############################################################################
function thamachi.PID_NUM():string;
var
   RegExpr:TRegExpr;
   tmpstr:string;
   l:TstringList;
   i:integer;

begin
   tmpstr:=logs.FILE_TEMP();
   fpsystem(BIN_PATH()+' -c /etc/hamachi >'+tmpstr+' 2>&1');
   l:=Tstringlist.Create;

 l:=Tstringlist.Create;
 l.Add('#!/bin/sh');
 l.Add(BIN_PATH()+' -c /etc/hamachi >'+tmpstr+' 2>&1');
 l.SaveToFile('/etc/hamachi/pid.sh');
 fpsystem('/bin/chmod 777 /etc/hamachi/pid.sh');
 fpsystem('/etc/hamachi/pid.sh');
 l.Clear;
    try
    l.LoadFromFile(tmpstr);
    except
    exit;
    end;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='pid.+?([0-9]+)';
    for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
           result:=RegExpr.Match[1];
           break;
        end;
   end;

    l.free;
end;
//##############################################################################
procedure thamachi.STOP();
var
   count:integer;
   pid:string;
begin

    if not FileExists(BIN_PATH()) then begin
    writeln('Stopping amachi..............: Not installed');
    exit;
    end;
pid:=SYS.PIDOF(BIN_PATH());
if  not SYS.PROCESS_EXIST(pid) then begin
     writeln('Stopping amachi..............: Already stopped');
    exit;
end;

writeln('Stopping amachi..............: ' + pid + ' PID..');
fpsystem('/bin/kill '+ pid);
pid:=SYS.PIDOF(BIN_PATH());
  count:=0;
  while SYS.PROCESS_EXIST(pid) do begin
        sleep(500);
        count:=count+1;
        if count>50 then begin
            writeln('Stopping amachi..............: Force kill ' + pid + ' PID..');
            fpsystem('/bin/kill -9 ' + pid);
            break;
        end;
         pid:=SYS.PIDOF(BIN_PATH());
  end;
pid:=SYS.PIDOF(BIN_PATH());
if  not SYS.PROCESS_EXIST(pid) then begin
    writeln('Stopping amachi..............: success');
    exit;
end;
    writeln('Stopping amachi..............: failed');
end;


//#############################################################################

procedure thamachi.NETWORK_LIST();
var
   RegExpr:TRegExpr;
   tmpstr:string;
   l:TstringList;
   i:integer;
begin
   tmpstr:=logs.FILE_TEMP();
   NETLIST:=TStringlist.CReate;
 l:=Tstringlist.Create;

l.Add('#!/bin/sh');
l.Add(BIN_PATH()+' -c /etc/hamachi list >'+tmpstr+' 2>&1');
l.SaveToFile('/etc/hamachi/list.sh');
fpsystem('/bin/chmod 777 /etc/hamachi/list.sh');
fpsystem('/etc/hamachi/list.sh');

 l:=Tstringlist.Create;
    try
    l.LoadFromFile(tmpstr);
    except
    exit;
    end;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='\[(.+?)\]';
    for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
           NETLIST.Add(RegExpr.Match[1]);
        end;
   end;
    RegExpr.free;
    l.free;



end;
//#############################################################################
function thamachi.STATUS();
var
   pidpath:string;
begin
if not FileExists(BIN_PATH()) then exit;
pidpath:=logs.FILE_TEMP();
fpsystem(SYS.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.status.php --hamachi >'+pidpath +' 2>&1');
result:=logs.ReadFromFile(pidpath);
logs.DeleteFile(pidpath);
end;
//##############################################################################
end.
