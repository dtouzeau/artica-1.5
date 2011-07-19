unit bogom;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;



  type
  tbogom=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;
     procedure SET_BOGOFILTER_CONF(key:string;value:string);
     procedure DUMP_START();
     procedure DUMP_STOP();
     function  DUMP_PID_NUM():string;
     procedure BOGOM_CONF();

public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    procedure   START();
    function    PID_NUM():string;
    procedure   STOP();
    function    VERSION():string;
    function    STATUS():string;
    function    BOGOFILTER_BIN_PATH():string;
    function    BOGOFILTER_STATUS():string;
    function    BOGOFILTER_VERSION():string;
    function    Learn_spam(mailpath:string;spam:boolean):boolean;


END;

implementation

constructor tbogom.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tbogom.free();
begin
    logs.Free;
end;
//##############################################################################
function tbogom.PID_NUM():string;
begin
     result:=SYS.GET_PID_FROM_PATH('/var/run/bogom/bogom.pid');
end;
//##############################################################################
 function tbogom.BOGOFILTER_BIN_PATH():string;
begin
 if FileExists('/usr/bin/bogofilter') then exit('/usr/bin/bogofilter');
 if FileExists('/usr/local/bin/bogofilter') then exit('/usr/local/bin/bogofilter');
end;
//#############################################################################
 function tbogom.BOGOFILTER_VERSION():string;
var
   RegExpr:TRegExpr;
   path:string;
   tmp:string;
begin
 path:=BOGOFILTER_BIN_PATH();
 if length(path)=0 then exit;
 
   tmp:=logs.FILE_TEMP();

   fpsystem(path+' -V >'+ tmp + ' 2>&1');
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='bogofilter version ([0-9\.]+)';
   if RegExpr.Exec(logs.ReadFromFile(tmp)) then begin
      result:=RegExpr.Match[1];
   end;

   RegExpr.Free;
   logs.DeleteFile(tmp);
end;
//#############################################################################
procedure tbogom.START();
var
   pid:string;
   parms:string;
   count:integer;
begin

  if SYS.GET_INFO('EnableMilterBogom')<>'1' then exit;

  if not FileExists(BOGOFILTER_BIN_PATH()) then begin
     logs.Syslogs('Could not start milter-bogom...Unable to stat bogofilter binary path..aborting');
     exit;
  end;

  pid:=PID_NUM();
  count:=0;
   if SYS.PROCESS_EXIST(pid) then begin
      if SYS.GET_INFO('EnableMilterBogom')<>'1' then begin
         logs.Syslogs('Shutdown milter-bogom, this service is disabled in Artica...');
         STOP();
         exit;
      end;
      
      
      logs.DebugLogs('Starting......: milter-bogom daemon is already running using PID ' + pid + '...');
      DUMP_START();
      exit;
   end;
   
   


  if not FileExists('/usr/lib/libmilter.so.0') then begin
     if not FileExists('/usr/lib/libmilter.so.1') then begin
        logs.Syslogs('Could not start milter-bogom... Unable to stat /usr/lib/libmilter.so.1');
        exit;
     end;
     logs.OutputCmd('/bin/ln -s /usr/lib/libmilter.so.1 /usr/lib/libmilter.so.0');
  end;
  
  

  ForceDirectories('/var/run/bogom');
  ForceDirectories('/var/spool/postfix/bogofilter');
  ForceDirectories('/var/spool/postfix/bogom');
  ForceDirectories('/var/db/bogofilter');
  ForceDirectories('/opt/artica/bogofilter');
  
  logs.OutputCmd('/bin/chown -R postfix:postfix /var/run/bogom');
  logs.OutputCmd('/bin/chown -R postfix:postfix /var/spool/postfix/bogofilter');
  logs.OutputCmd('/bin/chown -R postfix:postfix /var/spool/postfix/bogom');
  logs.OutputCmd('/bin/chown -R postfix:postfix /var/db/bogofilter');
  logs.OutputCmd('/bin/chown -R postfix:postfix /opt/artica/bogofilter');
  if FileExists('/var/run/bogom/bogom.pid') then logs.DeleteFile('/var/run/bogom/bogom.pid');
  SET_BOGOFILTER_CONF('bogofilter_dir','/var/db/bogofilter');
  SET_BOGOFILTER_CONF('min_dev','0.375');
  SET_BOGOFILTER_CONF('robs','0.0178');
  SET_BOGOFILTER_CONF('robx','0.52');
  SET_BOGOFILTER_CONF('ham_cutoff','0.45');
  SET_BOGOFILTER_CONF('spam_cutoff','0.99');
  SET_BOGOFILTER_CONF('ns_esf','1.000');
  SET_BOGOFILTER_CONF('sp_esf','1.000');
  SET_BOGOFILTER_CONF('thresh_update','0.01');
  
  SET_BOGOFILTER_CONF('spamicity_tags','Yes, No');
  SET_BOGOFILTER_CONF('spamicity_formats','%0.6f, %0.6f');



  parms:=artica_path + '/bin/bogom -c /etc/bogom.conf -p /var/run/bogom/bogom.pid -s unix:/var/spool/postfix/bogom/bogom.sock -u postfix -b ' + BOGOFILTER_BIN_PATH() + ' -S';
  logs.DebugLogs('Starting......: milter-bogom daemon from http://www.usebox.net/jjm/bogom');
  logs.OutputCmd(parms);


  while not SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(500);
        count:=count+1;
        logs.DebugLogs('tbogom.START(): wait sequence ' + intToStr(count));
        if count>20 then begin
            logs.syslogs('Starting......: milter-bogom daemon failed...');
            exit;
        end;
  end;

  logs.DebugLogs('Starting......: milter-bogom success...');
  DUMP_START();
end;
//##############################################################################
function tbogom.DUMP_PID_NUM():string;
begin
     result:=SYS.PidByPatternInPath('/bin/bogo-dump -p');
end;
//##############################################################################
procedure tbogom.STOP();
var
   pid:string;
   count:integer;
begin
pid:=PID_NUM();
count:=0;
if SYS.PROCESS_EXIST(pid) then begin
   writeln('Stopping milter-bogom........: ' + pid + ' PID..');
   fpsystem('/bin/kill ' + pid);
end;

  while SYS.PROCESS_EXIST(PID_NUM()) do begin
        sleep(300);
        count:=count+1;
        if count>10 then begin
            writeln('Stopping milter-bogom........: timeout...');
            fpsystem('/bin/kill -9 ' + pid);
        end;
  end;
pid:=SYS.AllPidsByPatternInPath('bin/bogom');
if length(pid)>0 then begin
   writeln('Stopping milter-bogom........: '+ pid + '...');
   fpsystem('/bin/kill ' + pid);
end;


writeln('Stopping milter-bogom........: success...');
DUMP_STOP();

end;

//#########################################################################################
procedure tbogom.DUMP_START();
var

   pid:string;
   parms:string;
   count:integer;
   EnableMilterBogom:integer;
begin

  pid:=DUMP_PID_NUM();
  count:=0;
  logs.DebugLogs('tbogom.DUMP_START: PID report "' + DUMP_PID_NUM()+'"');


if not TryStrToInt(SYS.get_INFO('EnableMilterBogom'),EnableMilterBogom) then begin
   logs.DebugLogs('tsyslogng.START():: unable to understand "EnableMilterBogom" parameter');
   exit;
end;

   if SYS.PROCESS_EXIST(pid) then begin
      if EnableMilterBogom=0 then DUMP_STOP();
      logs.DebugLogs('Starting......: bogo-dump daemon is already running using PID ' + pid + '...');
      exit;
   end;




  if not FileExists(SYS.LOCATE_SU()) then begin
      logs.Syslogs('Starting......: bogo-dump failed, unable to stat "su" tool');
      exit;
  end;

  fpsystem('/bin/rm -rf /var/run/bogo-dump');
  forceDirectories('/var/run/bogo-dump');
  forceDirectories('/opt/artica/bogo-dump');



  fpsystem('/bin/chown -R postfix:postfix /var/run/bogo-dump');
  fpsystem('/bin/chown -R postfix:postfix /opt/artica/bogo-dump');

  parms:=SYS.LOCATE_SU() +' postfix -c "'+artica_path + '/bin/bogo-dump -p local:/var/run/bogo-dump/bogo-dump.socket" &';
  logs.Syslogs('Starting......: bogo-dump daemon');
  fpsystem(parms);
  logs.Debuglogs(parms);

  pid:=DUMP_PID_NUM();
  while not SYS.PROCESS_EXIST(pid) do begin

        sleep(500);
        count:=count+1;
        if count>20 then begin
            logs.DebugLogs('Starting......: bogo-dump failed...');
            exit;
        end;
        pid:=DUMP_PID_NUM();
  end;
  logs.Syslogs('Success starting bogo-dump daemon...');
  logs.DebugLogs('Starting......: bogo-dump daemon success...');
end;
//##############################################################################
procedure tbogom.DUMP_STOP();
var
   pid:string;
   count:integer;
begin
pid:=DUMP_PID_NUM();
count:=0;
if SYS.PROCESS_EXIST(pid) then begin
   writeln('Stopping bogo-dump...........: ' + pid + ' PID..');
   fpsystem('/bin/kill ' + pid);
end else begin
    writeln('Stopping bogo-dump...........: Already stopped');
    exit;
end;

pid:=SYS.AllPidsByPatternInPath('/bin/bogo-dump');
if length(pid)>0 then begin
   writeln('Stopping bogo-dump...........: ' + pid + ' PID..');
   fpsystem('/bin/kill ' + pid);
end;
  pid:=PID_NUM();
  while SYS.PROCESS_EXIST(pid) do begin
        pid:=PID_NUM();
        sleep(100);
        count:=count+1;
        if count>20 then begin
            writeln('Stopping bogo-dump...........: timeout ('+pid+')...');
            fpsystem('/bin/kill -9 ' + pid);
        end;
  end;



writeln('Stopping bogo-dump...........: success...');


end;

//##############################################################################
function tbogom.STATUS():string;
var ini:TstringList;
begin
ini:=TstringList.Create;

   ini.Add('[BOGOM]');
   if SYS.PROCESS_EXIST(PID_NUM()) then ini.Add('running=1') else  ini.Add('running=0');
   ini.Add('application_installed=1');
   ini.Add('master_pid='+ PID_NUM());
   ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(PID_NUM())));
   ini.Add('master_version=' + VERSION());
   ini.Add('status='+SYS.PROCESS_STATUS(PID_NUM()));
   ini.Add('service_name=APP_BOGOM');
   ini.Add('service_disabled='+SYS.GET_INFO('EnableMilterBogom'));
   ini.Add('service_cmd=bogom');
   result:=ini.Text;
   ini.free;

end;
//#########################################################################################
function tbogom.BOGOFILTER_STATUS():string;
var ini:TstringList;
enabled:string;
begin
ini:=TstringList.Create;
   enabled:=SYS.GET_INFO('EnableMilterBogom');
   if length(enabled)=0 then begin
      enabled:='0';
      SYS.set_INFOS('EnableMilterBogom','0');
   end;
   
   
   ini.Add('[BOGOFILTER]');
   ini.Add('running=1');
   if  fileExists(BOGOFILTER_BIN_PATH()) then ini.Add('application_installed=1') else ini.Add('application_installed=0');
   ini.Add('master_pid=0');
   ini.Add('master_memory=0');
   ini.Add('master_version=' + BOGOFILTER_VERSION());
   ini.Add('status=manual process');
   ini.Add('service_name=APP_BOGOFILTER');
   ini.Add('service_disabled='+enabled);
   result:=ini.Text;
   ini.free;

end;
//#########################################################################################
function tbogom.VERSION():string;
begin
   result:='1.9.2';
   exit;

end;
//#########################################################################################
procedure tbogom.BOGOM_CONF();
var
l:tstringList;
begin
l:=TstringList.Create;
l.add('#');
l.add('# $Id: bogom.conf-example,v 1.11 2006/10/22 10:35:26 reidrac Exp reidrac $');
l.add('policy reject');
l.add('reject "Spam pattern detected by Bogofilter"');
l.add('subject_tag "[spam detected]"');
l.add('verbose 1');
l.add('# default: verbose 0');
l.add('spamicity_header 1');
l.add('bogofilter "'+BOGOFILTER_BIN_PATH()+'"');
l.add('training 0');
l.add('# body_limit <length in bytes>');
l.add('body_limit 256k');
l.add('user "postfix"');
l.add('connection "unix:/var/spool/postfix/bogom/bogom.sock"');
l.add('pidfile "/var/run/bogom/bogom.pid"');
l.add('# exclude_string "<subject exclude string>"');
l.add('# exclude_string "[no-bogofilter]"');
l.add('# exclude_string "*no filter*"');
l.add('# default: empty');
l.add('# forward_spam "<rcpt>"');
l.add('# forward_spam "spammaster"');
l.add('# forward_spam "spam@other.domain.com"');
l.add('# default: empty');
l.add('quarantine_mdir "/opt/artica/bogofilter"');
l.add('# re_connection "<case insensitive extended re>"');
l.add('# re_connection "192\.168\.0\."');
l.add('# re_connection "openbsd\.org$"');
l.add('# default: empty');
l.add('#');
l.add('# re_envfrom "<case insensitive extended re>"');
l.add('# re_envfrom "\.usebox\.net>$"');
l.add('# re_envfrom "@usebox\.net>$"');
l.add('# default: empty');
l.add('#');
l.add('# re_envrcpt "<case insensitive extended re>"');
l.add('# re_envrcpt "spamtrap@usebox\.net>$"');
l.add('# re_envrcpt "ilikespam@"');
l.add('# default: empty');
l.add('');
l.add('# EOF');
l.SaveToFile('/etc/bogom.conf');
l.Free;
end;
//#########################################################################################
procedure tbogom.SET_BOGOFILTER_CONF(key:string;value:string);
var
   l:tstringList;
   RegExpr:TRegExpr;
   i:integer;
   p:boolean;
begin
p:=false;
l:=TStringList.Create;
if not FileExists('/etc/bogofilter.cf') then begin
   l.Add(key + '=' + value);
   l.SaveToFile('/etc/bogofilter.cf');
   l.free;
   exit;
end;


l.LoadFromFile('/etc/bogofilter.cf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^'+key;
For i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       p:=true;
       l.Strings[i]:=key+ '=' + value;
       break;
    end;
end;
RegExpr.free;
if not p then l.Add(key + '=' + value);
l.SaveToFile('/etc/bogofilter.cf');
l.free;
end;
//#########################################################################################
function tbogom.Learn_spam(mailpath:string;spam:boolean):boolean;
var
   cmd_line:string;
   spam_com:string;
begin
     result:=false;
     if spam then spam_com:='--register-spam' else spam_com:='--register-ham';
     cmd_line:=BOGOFILTER_BIN_PATH()+' '+spam_com+' --config-file=/etc/bogofilter.cf --no-header-tags --use-syslog --input-file=' + mailpath + ' >' +ExtractFilePath(mailpath)+'tmp.bogo 2>&1';
     LOGS.outputCmd(cmd_line);

end;
//##############################################################################

end.
