unit dnsmasq;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr in 'RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas',
    bind9   in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/bind9.pas';

  type
  tdnsmasq=class


private
     LOGS:Tlogs;
     artica_path:string;
     EnableDNSMASQ:integer;
     EnablePDNS:integer;
     SYS:Tsystem;
     bind9:Tbind9;
     function IsLoadedAsuser():string;
     function DNSMASQ_PID_PATH():string;


public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
      function  DNSMASQ_SET_VALUE(key:string;value:string):string;
      function  DNSMASQ_GET_VALUE(key:string):string;
      function  DNSMASQ_BIN_PATH():string;
      function  DNSMASQ_VERSION:string;
      procedure DNSMASQ_START_DAEMON();
      procedure DNSMASQ_STOP_DAEMON();
      function  DNSMASQ_PID():string;
      function  Forwarders():string;



END;

implementation

constructor tdnsmasq.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;
       if Not TryStrToInt(SYS.GET_INFO('EnablePDNS'),EnablePDNS) then EnablePDNS:=1;
       if Not TryStrToInt(SYS.GET_INFO('EnableDNSMASQ'),EnableDNSMASQ) then EnableDNSMASQ:=1;



       if Not FileExists(SYS.LOCATE_PDNS_BIN()) then EnablePDNS:=0;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tdnsmasq.free();
begin
    logs.Free;
end;
//##############################################################################
function tdnsmasq.DNSMASQ_GET_VALUE(key:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    ValueResulted:string;
begin
   if not FileExists('/etc/dnsmasq.conf') then  exit;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile('/etc/dnsmasq.conf');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              FileDatas.Free;
              ValueResulted:=RegExpr.Match[2];
              if ValueResulted='"' then ValueResulted:='';
              RegExpr.Free;
              exit(ValueResulted);
           end;

   end;
   FileDatas.Free;
   RegExpr.Free;

end;
//#############################################################################
function tdnsmasq.Forwarders():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
begin
   if not FileExists('/etc/dnsmasq.resolv.conf') then  exit;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile('/etc/dnsmasq.resolv.conf');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^nameserver\s+(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              result:=result + RegExpr.Match[1]+';';
           end;

   end;
   FileDatas.Free;
   RegExpr.Free;

end;
//#############################################################################
function tdnsmasq.IsLoadedAsuser():string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
begin
   if not FileExists('/etc/init.d/dnsmasq') then  exit;
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile('/etc/init.d/dnsmasq');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='startproc.+?-u\s+(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
              result:=RegExpr.Match[1];
              RegExpr.Expression:='(.+?)\s+';
               if RegExpr.Exec(result) then result:=RegExpr.Match[1];
              break;
           end;

   end;
   FileDatas.Free;
   RegExpr.Free;

end;
//#############################################################################



function tdnsmasq.DNSMASQ_SET_VALUE(key:string;value:string):string;
var
    RegExpr:TRegExpr;
    FileDatas:TStringList;
    i:integer;
    FileToEdit:string;
begin
   FileToEdit:='/etc/dnsmasq.conf';
   if not FileExists(FileToEdit) then  fpsystem('/bin/touch ' + FileToEdit);
   FileDatas:=TStringList.Create;
   FileDatas.LoadFromFile(FileToEdit);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='^'+key+'([="''\s]+)(.+)';
   for i:=0 to FileDatas.Count -1 do begin
           if RegExpr.Exec(FileDatas.Strings[i]) then begin
                FileDatas.Strings[i]:=key + '=' + value;
                FileDatas.SaveToFile(FileToEdit);
                FileDatas.Free;
                RegExpr.Free;
                exit;

           end;

   end;

  FileDatas.Add(key + '=' + value);
  FileDatas.SaveToFile(FileToEdit);
  FileDatas.Free;
  RegExpr.Free;
  result:='';

end;
//#############################################################################
function tdnsmasq.DNSMASQ_BIN_PATH():string;
begin
    exit(SYS.LOCATE_GENERIC_BIN('dnsmasq'));
end;
//#############################################################################
function tdnsmasq.DNSMASQ_VERSION:string;
var
   binPath:string;
    mem:TStringList;
    commandline:string;
    tmp_file:string;
    RegExpr:TRegExpr;
    i:integer;
begin
    binPath:=DNSMASQ_BIN_PATH();

    if not FileExists(binpath) then begin
       exit;
    end;


    result:=trim(SYS.GET_CACHE_VERSION('APP_DNSMASQ'));
    if length(result)>2 then exit();

    if not FIleExists('/etc/dnsmasq.conf') then exit;

    tmp_file:=logs.FILE_TEMP();
    commandline:=binPath+' -v >'+tmp_file +' 2>&1';
    fpsystem(commandline);
    mem:=TStringList.Create;
    if not FileExists(tmp_file) then exit;
    mem.LoadFromFile(tmp_file);
    logs.DeleteFile(tmp_file);


    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='Dnsmasq version\s+([0-9\.]+)';

     for i:=0 to mem.Count-1 do begin
       if RegExpr.Exec(mem.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;

     end;
     SYS.SET_CACHE_VERSION('APP_DNSMASQ',result);
     mem.Free;
     RegExpr.Free;

end;
//#############################################################################
procedure tdnsmasq.DNSMASQ_START_DAEMON();
var
   bin_path,pid,cache,cachecmd:string;
   DnsMasqConfigurationFile:string;
   DnsMasqConfigurationFileLength:integer;
   user:string;
begin

    bin_path:=DNSMASQ_BIN_PATH();
    if not FileExists(bin_path) then begin
       logs.DebugLogs('Starting......: dnsmasq is not installed...');
       exit;
    end;

    bind9:=tbind9.Create(SYS);
    if FileExists(bind9.bin_path()) then begin
       logs.DebugLogs('Starting......: dnsmasq bind9 exists and replace dnsmasq features...');
       DNSMASQ_STOP_DAEMON();
       exit;
    end;

    pid:=DNSMASQ_PID();

     if SYS.PROCESS_EXIST(pid) then begin
        if EnablePDNS=1 then begin
           logs.DebugLogs('Starting......: dnsmasq PowerDNS is enabled, shutdown');
           DNSMASQ_STOP_DAEMON();
           exit;
        end;
        if EnableDNSMASQ=0 then begin
           logs.DebugLogs('Starting......: dnsmasq is disabled, shutdown');
           DNSMASQ_STOP_DAEMON();
           exit;
        end;
     end;

      if EnablePDNS=1 then begin
         logs.DebugLogs('Starting......: dnsmasq PowerDNS is enabled, aborting');
         exit;
      end;

      if EnableDNSMASQ=0 then begin
         logs.DebugLogs('Starting......: dnsmasq is disabled, aborting');
         exit;
      end;


     DnsMasqConfigurationFile:=SYS.GET_INFO('DnsMasqConfigurationFile');
     DnsMasqConfigurationFileLength:=length(DnsMasqConfigurationFile);
     if length(DnsMasqConfigurationFile)>50 then begin
        logs.WriteToFile(DnsMasqConfigurationFile,'/etc/dnsmasq.conf');
        logs.DebugLogs('Starting......: dnsmasq saving /etc/dnsmasq.conf done...');
     end else begin
        logs.DebugLogs('Starting......: dnsmasq is not yet set by Artica ('+intTostr(DnsMasqConfigurationFileLength)+') bytes');
     end;

     if not FileExists('/etc/dnsmasq.resolv.conf') then fpsystem('/bin/cp -f /etc/resolv.conf /etc/dnsmasq.resolv.conf');

     user:=IsLoadedAsuser();
     if length(user)>2 then begin
         logs.DebugLogs('Starting......: dnsmasq running has "'+user+'"');
         if FileExists('/etc/dnsmasq.resolv.conf') then fpsystem('/bin/chown '+user+' /etc/dnsmasq.resolv.conf');
     end;
    if SYS.PROCESS_EXIST(pid) then begin
       logs.DebugLogs('Starting......: dnsmasq already exists using pid ' + pid+ '...');
       exit;
    end;
     if not FIleExists('/etc/dnsmasq.conf') then exit;
    if FileExists('/etc/init.d/dnsmasq') then begin
       fpsystem('/etc/init.d/dnsmasq start');
       exit;

    end;


    cache:=DNSMASQ_GET_VALUE('cache-size');

    if length(cache)=0 then begin
       cachecmd:=' --cache-size=1000';
    end;
    forceDirectories('/var/log/dnsmasq');
    logs.DebugLogs('Starting......: dnsmasq daemon...');
    fpsystem(bin_path + ' --pid-file=/var/run/dnsmasq.pid --conf-file=/etc/dnsmasq.conf --user=root --log-facility=/var/log/dnsmasq/dnsmasq.log' + cachecmd);
end;
//##############################################################################
function tdnsmasq.DNSMASQ_PID():string;
begin
result:='';
result:=SYS.GET_PID_FROM_PATH(DNSMASQ_PID_PATH());
if not SYS.PROCESS_EXIST(result) then result:=SYS.PIDOF(DNSMASQ_BIN_PATH());
end;
//##############################################################################
function tdnsmasq.DNSMASQ_PID_PATH():string;
begin
     if FileExists('/var/run/dnsmasq/dnsmasq.pid') then exit('/var/run/dnsmasq/dnsmasq.pid');
     if FileExists('/var/run/dnsmasq.pid') then exit('/var/run/dnsmasq.pid');
end;

procedure tdnsmasq.DNSMASQ_STOP_DAEMON();
var bin_path,pid:string;
count:integer;
begin

    bin_path:=DNSMASQ_BIN_PATH();
    if not FileExists(bin_path) then exit;
    pid:=DNSMASQ_PID();
    if not SYS.PROCESS_EXIST(pid) then begin
       writeln('Stopping dnsmasq.........: Already stopped');
       exit;
    end;

    if FileExists('/etc/init.d/dnsmasq') then begin
       fpsystem('/etc/init.d/dnsmasq stop');
    end;

    pid:=SYS.PIDOF(DNSMASQ_BIN_PATH());
    count:=0;
  while SYS.PROCESS_EXIST(pid) do begin
      writeln('Stopping dnsmasq.........: PID '+pid);
      fpsystem('/bin/kill '+pid);
      sleep(500);
      inc(count);
       if count>30 then begin
            writeln('Stopping dnsmasq.........: Timeout while stopping '+pid);
            break;
       end;
       pid:=SYS.PIDOF(DNSMASQ_BIN_PATH());
  end;
    pid:=SYS.PIDOF(DNSMASQ_BIN_PATH());
    if SYS.PROCESS_EXIST(pid) then begin
        writeln('Stopping dnsmasq.........: ' + pid + ' PID failed');
    end else begin
        if Fileexists(DNSMASQ_PID_PATH()) then logs.DeleteFile(DNSMASQ_PID_PATH());
    end;
end;
//##############################################################################
end.

