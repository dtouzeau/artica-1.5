unit backup_rsync;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,BaseUnix,unix,RegExpr in 'RegExpr.pas',zsystem,openldap,cyrus,rdiffbackup,samba,
    mysql_daemon;


  type
  tbackup_rsync=class


private
     LOGS           :Tlogs;
     verbose        :boolean;
     D              :boolean;
     SYS            :Tsystem;
     function       BuildParams(server:string;server_port:string;username:string;password:string;organization:string;sbwlimit:integer;ssl:integer):string;
     function       DetectError(path:string):boolean;
     function       BuildSSLTunel(remote_server:string;remote_port:string):integer;
     function       RSYNC_STUNNEL_PID():string;
     function       STUNNEL_START():boolean;

public
    artica_path    :string;
    procedure   Free;
    constructor Create;
    RsyncClientParameters_path:string;
    RsyncClientParameters:TiniFile;
    EnableFeature:integer;
    remote_server_name,remote_server_port,organization,username,password:string;
    second_remote_server_name,second_remote_server_port,second_organization,second_username,second_password:string;
    remote_server_ssl,second_remote_server_ssl:integer;


    bwlimit:integer;
    second_bwlimit:integer;
    procedure RsyncRemoteFolder(folderpath:string;computername:string='');
    procedure      SSL_STOP();
END;

implementation

constructor tbackup_rsync.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       SetCurrentDir(ExtractFilePath(Paramstr(0)));

      if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;


     EnableFeature:=0;

     verbose:=SYS.COMMANDLINE_PARAMETERS('--verbose');


     //enable_remote_sync
     RsyncClientParameters_path:='/etc/artica-postfix/settings/Daemons/RsyncClientParameters';
     if FileExists(RsyncClientParameters_path) then begin
        RsyncClientParameters:=TiniFile.Create(RsyncClientParameters_path);
        remote_server_name:=RsyncClientParameters.ReadString('CONF','remote_server_name','');
        remote_server_port:=RsyncClientParameters.ReadString('CONF','remote_server_port','873');
        organization:=RsyncClientParameters.ReadString('CONF','organization','');
        username:=RsyncClientParameters.ReadString('CONF','username','');
        password:=RsyncClientParameters.ReadString('CONF','password','');

        if not TryStrToInt(RsyncClientParameters.ReadString('CONF','remote_server_ssl','0'),remote_server_ssl) then remote_server_ssl:=0;
        if not TryStrToInt(RsyncClientParameters.ReadString('CONF','second_remote_server_ssl','0'),second_remote_server_ssl) then second_remote_server_ssl:=0;



        second_remote_server_name:=RsyncClientParameters.ReadString('CONF','second_remote_server_name','');
        second_remote_server_port:=RsyncClientParameters.ReadString('CONF','second_remote_server_port','873');
        second_organization:=RsyncClientParameters.ReadString('CONF','second_organization','');
        second_username:=RsyncClientParameters.ReadString('CONF','second_username','');
        second_password:=RsyncClientParameters.ReadString('CONF','second_password','');


        if not TryStrToint(RsyncClientParameters.ReadString('CONF','bwlimit','0'),bwlimit) then bwlimit:=0;
        if not TryStrToint(RsyncClientParameters.ReadString('CONF','second_bwlimit','0'),second_bwlimit) then second_bwlimit:=0;


        if not TryStrToint(RsyncClientParameters.ReadString('CONF','enable_remote_sync','0'),EnableFeature) then EnableFeature:=0;
     end;



end;
//##############################################################################
procedure tbackup_rsync.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
procedure tbackup_rsync.RsyncRemoteFolder(folderpath:string;computername:string);

var
   backup_dir:string;
   commandlines:string;
   path_name:string;
   events:Tstringlist;
   logs_path:string;
   connection:string;
   rsh_options:string;
   cmdline:string;
   remote_server_name_log:string;
   pid:string;

begin

 if  EnableFeature<>1 then begin
       logs.Debuglogs('RsyncRemoteFolder():: Remote synchronization client feature is disabled...');
       exit;
 end;

 if not DirectoryExists(folderpath) then begin
    logs.Debuglogs('RsyncRemoteFolder():: unable to stat '+folderpath);
    exit;
 end;
   rsh_options:=' --rsh="/usr/bin/ssh -o StrictHostKeyChecking=no -o PasswordAuthentication=no -o ServerAliveInterval=10 -o ConnectTimeout=5" ';

   logs_path:=logs.FILE_TEMP();

   backup_dir:=ExtractFileName(folderpath);
   backup_dir:=' --backup-dir="'+backup_dir+'_'+FormatDateTime('yyyy-mm-dd hh:nn', Now)+'" ';

   events:=Tstringlist.Create;
   events.Add('<started_on>'+logs.DateTimeNowSQL()+'</started_on>');
   remote_server_name_log:=remote_server_name;    




   logs.Debuglogs('RsyncRemoteFolder():: trying main remote server');
   connection:=BuildParams(remote_server_name,remote_server_port,username,password,organization,bwlimit,remote_server_ssl);
   if length(connection)>0 then begin
      commandlines:='/usr/bin/rsync --contimeout=10 --timeout=5 --archive ';
      commandlines:=commandlines+' --delete --stats --compress --backup --log-file='+logs_path+' "'+folderpath+'"'+connection;

      pid:=SYS.PIDOF_PATTERN('rsync.+?'+folderpath);
      logs.Debuglogs('RsyncRemoteFolder:: Already a PID='+pid);


      if not SYS.PROCESS_EXIST(pid) then begin
         logs.Debuglogs(commandlines);
         fpsystem(SYS.EXEC_NICE()+commandlines);
      end;
   end;


   if DetectError(logs_path) then begin
          logs.Debuglogs('Error detected, try secondary server');
      if length(trim(second_remote_server_name))>0 then begin

         events.Add('<server>'+remote_server_name_log+'</server>');
         events.Add('<foldermd5>'+logs.MD5FromString(folderpath)+'</foldermd5>');
         events.Add('<rsyncevents>'+logs.ReadFromFile(logs_path)+'</rsyncevents>');
         if length(computername)>0 then begin
            events.Add('<folder>smb://'+computername+'/'+folderpath+'</folder>');
         end else begin
             events.Add('<folder>'+folderpath+'</folder>');
         end;
          events.Add('<finishon>'+logs.DateTimeNowSQL()+'</finishon>');
          ForceDirectories('/var/log/artica-postfix/rsync-queue');
          logs.WriteToFile(events.Text,'/var/log/artica-postfix/rsync-queue/' +logs.MD5FromString(events.Text+logs.DateTimeNowSQL())+'.sql');
          logs.DeleteFile(logs_path);

          events.Clear;
          events.Add('<started_on>'+logs.DateTimeNowSQL()+'</started_on>');


             remote_server_name_log:=second_remote_server_name;
             connection:=BuildParams(second_remote_server_name,second_remote_server_port,second_username,second_password,second_organization,second_bwlimit,second_remote_server_ssl);


             if length(connection)>0 then begin
                pid:=SYS.PIDOF_PATTERN('rsync.+?'+folderpath);
                logs.Debuglogs('RsyncRemoteFolder:: Already a PID='+pid);
                 if not SYS.PROCESS_EXIST(pid) then begin
                  commandlines:=SYS.EXEC_NICE()+'/usr/bin/rsync --contimeout=10 --timeout=5 --archive --delete --stats --compress --backup --log-file='+logs_path+' "'+folderpath+'"'+connection;
                  logs.Debuglogs(commandlines);
                  fpsystem(commandlines);
                end;
             end;
      end;
  end;


   logs.Debuglogs('Log size: ' + IntTOstr(length(logs.ReadFromFile(logs_path)))+' bytes');
   logs.Debuglogs(logs.ReadFromFile(logs_path));


   events.Add('<server>'+remote_server_name_log+'</server>');
   events.Add('<foldermd5>'+logs.MD5FromString(folderpath)+'</foldermd5>');
   events.Add('<rsyncevents>'+logs.ReadFromFile(logs_path)+'</rsyncevents>');
   if length(computername)>0 then begin
      events.Add('<folder>smb://'+computername+'/'+folderpath+'</folder>');
   end else begin
       events.Add('<folder>'+folderpath+'</folder>');
   end;


   events.Add('<finishon>'+logs.DateTimeNowSQL()+'</finishon>');

   logs.Debuglogs('writing events');
   ForceDirectories('/var/log/artica-postfix/rsync-queue');
   logs.WriteToFile(events.Text,'/var/log/artica-postfix/rsync-queue/' +logs.MD5FromString(events.Text+logs.DateTimeNowSQL()+remote_server_name_log)+'.sql');
   events.Free;
   logs.DeleteFile(logs_path);


end;
//##############################################################################
function tbackup_rsync.DetectError(path:string):boolean;
var
l:Tstringlist;
RegExpr:TRegExpr;
i:integer;
begin
result:=false;
l:=tstringlist.Create;
if not FileExists(path) then exit(true);
l.LoadFromFile(path);
RegExpr:=TRegExpr.Create;

for i:=0 to l.Count-1 do begin
RegExpr.Expression:='rsync error';
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=true;
       break;
    end;

RegExpr.Expression:='rsync: read error';
    if RegExpr.Exec(l.Strings[i]) then begin
       result:=true;
       break;
    end;

end;
    l.free;
    RegExpr.free;
    if(result) then begin
      logs.Debuglogs('Error detected for rsync');
    end else begin
      logs.Debuglogs('NO Error detected for rsync');
    end;

end;

//##############################################################################


function tbackup_rsync.BuildParams(server:string;server_port:string;username:string;password:string;organization:string;sbwlimit:integer;ssl:integer):string;
var
   password_file:string;
   password_token:string;
   connection:string;
   path_name:string;
   bwlimit_cmd:string;
   newport:integer;

begin
   password_file:=logs.FILE_TEMP();
   password_token:='';
   if length(password)>0 then begin
         logs.WriteToFile(password,password_file);
         logs.OutputCmd('/bin/chmod 600 '+password_file);
         password_token:=' --password-file='+password_file+' ';
   end;


   if ssl=1 then begin
      newport:=BuildSSLTunel(server,server_port);
      if newport=0 then exit();
      server_port:=IntToStr(newport);
      server:='127.0.0.1';
   end;


   path_name:=LowerCase(organization+username);
   path_name:=logs.MD5FromString(path_name);
   if sbwlimit>0 then bwlimit_cmd:=' --bwlimit='+IntToStr(sbwlimit)+' ';
   connection:=' rsync://'+username+'@'+server+':'+server_port+'/'+path_name;
   result:=password_token+bwlimit_cmd+connection

end;
//##############################################################################
function tbackup_rsync.BuildSSLTunel(remote_server:string;remote_port:string):integer;
var
   freeport:integer;
   l:Tstringlist;
   pid:string;
   Tfile:TiniFIle;
begin
    result:=0;

    if not FileExists(SYS.LOCATE_STUNNEL()) then begin
       logs.Debuglogs('Unable to find stunnel');
       exit(0);
    end;


    pid:=RSYNC_STUNNEL_PID();
    if SYS.PROCESS_EXIST(pid) then begin
       Tfile:=TiniFile.Create('/etc/rsync/stunnel-client.conf');
       TryStrtoInt(tfile.ReadString('ssync','accept','0'),result);
       Tfile.free;
       exit;
    end;




    freeport:=SYS.FREE_PORT();
    if freeport=0 then begin
        logs.Debuglogs('Unable to find a free port for stunnel');
        exit(0);
    end;


   l:=Tstringlist.Create;
   l.Add('client = yes');
   l.Add('pid = /var/run/rsync/stunnel-client.pid');
   l.Add('output = /var/log/rsync/stunnel-client.log');
   l.Add('[ssync]');
   l.add('accept = '+IntToStr(freeport));
   l.add('connect = '+remote_server+':'+remote_port);
   logs.WriteToFile(l.Text, '/etc/rsync/stunnel-client.conf');
   l.free;

   if STUNNEL_START() then result:=freeport;


end;
//##############################################################################
function tbackup_rsync.STUNNEL_START():boolean;
var
   cmd:string;
   pid:string;
   count:integer;
begin


pid:=RSYNC_STUNNEL_PID();
if SYS.PROCESS_EXIST(pid) then begin
    logs.Debuglogs('Starting......: Rsync SSL is already running pid '+pid);
    exit(true);
end;

logs.Debuglogs('Starting......: Rsync SSL Deamon');
cmd:=SYS.LOCATE_STUNNEL()+' /etc/rsync/stunnel-client.conf';

logs.OutputCmd(cmd);
pid:=RSYNC_STUNNEL_PID();
count:=0;
  while not SYS.PROCESS_EXIST(pid) do begin
              sleep(100);
              inc(count);
              if count>30 then begin
                 logs.DebugLogs('Starting......: Rsync stunnel daemon (timeout!!!)');
                 break;
              end;

              pid:=RSYNC_STUNNEL_PID();
        end;


pid:=RSYNC_STUNNEL_PID();

    if not SYS.PROCESS_EXIST(pid) then begin

         logs.DebugLogs('Starting......: Rsync SSL daemon (failed!!!)');
         exit(false);
    end else begin
         logs.DebugLogs('Starting......: Rsync SSL daemon Success with new PID '+pid);
         exit(true);
    end;
end;
//#############################################################################

procedure tbackup_rsync.SSL_STOP();
var
   pid:string;
   cmd:string;
   count:integer;
begin

if not FileExists(SYS.LOCATE_STUNNEL()) then begin
   exit;
end;
pid:=RSYNC_STUNNEL_PID();

if sys.PROCESS_EXIST(pid) then begin
   writeln('Stopping Rsync SSL.......: Daemon PID '+pid);
   logs.OutputCmd('/bin/kill ' + pid);
   count:=0;
   while SYS.PROCESS_EXIST(pid) do begin
      sleep(500);

      inc(count);
       if count>50 then begin
          writeln('Stopping Rsync SSL.......: Timeout while force stopping Daemon pid:'+pid);
            break;
       end;
       pid:=RSYNC_STUNNEL_PID();
   end;
end else begin
   writeln('Stopping Rsync SSL.......: Daemon Already stopped');
end;

pid:=RSYNC_STUNNEL_PID();



end;
//#############################################################################
function tbackup_rsync.RSYNC_STUNNEL_PID():string;
var
   pid_path:string;
   pid:string;
begin

    pid_path:='/var/run/rsync/stunnel-client.pid';

    pid:=SYS.GET_PID_FROM_PATH(pid_path);

   if not SYS.PROCESS_EXIST(pid) then begin
       if verbose then logs.Debuglogs('RSYNC_STUNNEL_PID: '+pid+' failed');
      result:=SYS.PIDOF_PATTERN(SYS.LOCATE_STUNNEL()+' /etc/rsync/stunnel-client.conf');
      if verbose then logs.Debuglogs('RSYNC_STUNNEL_PID: pidof='+pid);
   end else begin
       result:=pid;
   end;


end;
//##############################################################################




end.
