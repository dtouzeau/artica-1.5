unit userdb;

{$mode objfpc}{$H+}

interface

uses
Classes, SysUtils,variants, Process,Linux,BaseUnix,IniFiles,oldlinux,strutils,md5,logs,common,process_infos,cyrus,
RegExpr in 'RegExpr.pas',
confiles in 'confiles.pas',
global_conf in 'global_conf.pas';


  type
  TMailBoxes_userdb=class


private
     GLOBAL_INI:myconf;
     LOGS:Tlogs;
     COMMON:Tcommon;
     PROC:Tprocessinfos;
     function MD5FromFile(path:string):string;
     procedure xShell(cmd:string);
     function ADD_CyrusdbUser(user:string;domain:string;path:string;password:string;quota:string):boolean;


public


      constructor Create;
      procedure Free;
      procedure analyzeMailboxes();
      procedure CheckUserdbQueue();
      procedure SetLocalEcho();
      function IfMailboxdbUserExists(user:string;domain:string):boolean;
      function ADD_MailboxdbUser(user:string;domain:string;path:string;password:string):boolean;
      Enable_echo:boolean;
      Debug:boolean;
      

END;

implementation

constructor TMailBoxes_userdb.Create;

begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=myconf.Create();
       Debug:=GLOBAL_INI.get_DEBUG_DAEMON();
       LOGS:=Tlogs.Create;
       LOGS.Debug:=Debug;
       LOGS.Enable_echo:=Enable_echo;
       COMMON:=Tcommon.Create;
       COMMON.debug:=Debug;
       PROC:=Tprocessinfos.Create;
       LOGS.module_name:='userdb';
end;
procedure TMailBoxes_userdb.SetLocalEcho();
begin
   Enable_echo:=True;
   LOGS.Enable_echo:=Enable_echo;
   LOGS.Debug:=Debug;
end;


procedure TMailBoxes_userdb.Free();
begin
   GLOBAL_INI.Free;
   COMMON.Free;
   PROC.Free;
   LOGS.Free;
end;

procedure TMailBoxes_userdb.analyzeMailboxes();
var
enablemb,USE_MYSQL:boolean;
authdaemonrc_path,mailbox_path,userdb_path,artica_path,ListUsers_string,permissions,mdA,mdB:string;
ListFiles: TStringList;
ListUsers: TStringList;
lsResults:string;
FileDatas:TStringList;
i:integer;
j:integer;
RegExpr:TRegExpr;
begin

         LOGS.Enable_echo:=Enable_echo;
         LOGS.Debug:=Debug;

         artica_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
         ForceDirectories(artica_path + '/ressources/userdb');

         
     if Enable_echo=true then writeln('################ ANALYSE authdb IMAP/POP3 ############################');
     Debug:=GLOBAL_INI.get_DEBUG_DAEMON();
     
     if GLOBAL_INI.get_POSTFIX_DATABASE<>'hash' then  begin
          if debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: "hash" database is not selected on this server');
          exit;
     end;
     
     if GLOBAL_INI.get_MANAGE_MAILBOXES()<>'yes' then begin
          if debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: mailboxes is not managed on this server');
          exit;
     end;

if length(artica_path)=0 then begin
       if debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: init -> RESSOURCES/ressourcespath length=0');
       LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::WARNING: unable to locate artica-web php path;please check install');
       exit();
     end;

     if not FileExists(artica_path) then begin
        if debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: init -> RESSOURCES/ressourcespath cannot listing');
        LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::WARNING: unable to locate artica-web path (' +artica_path + ');please check install');
        exit();
     end;


     mailbox_path:=GLOBAL_INI.get_COURIER_MAILBOX_PATH();
     authdaemonrc_path:=GLOBAL_INI.get_COURIER_AUTHDAEMON_PATH();
     
     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: init -> mailbox_path=' + mailbox_path);
     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: init -> authdaemonrc_path=' + authdaemonrc_path);
     
     
     
     if length(authdaemonrc_path)=0 then begin
           LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::WARNING: unable to locate authdaemonrc path;please check install');
           exit();
     end;

     userdb_path:=ExtractFilePath(authdaemonrc_path) + 'userdb';
     if debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: init -> userdb_path=' + userdb_path);

     if not FileExists(userdb_path) then begin
            LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::WARNING: unable to locate userdb path (' +userdb_path + ');please check install');
            exit();
     end;
     artica_path:=artica_path + '/ressources/userdb';
     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: init -> artica_path=' + artica_path);

     if not FileExists(artica_path) then begin
         LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::database IMAP files, creating (' +artica_path + ');');
         if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: -> force directories "' + artica_path + '"');
         forcedirectories(artica_path);
         if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: -> xShell(/bin/chmod 0777 ' + artica_path + ')');
         xShell('/bin/chmod 0777 ' + artica_path);
     end;

     // --> CheckUserdbQueue();
     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: -> TMailBoxes_userdb.CheckUserdbQueue()');
     CheckUserdbQueue();

     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes():: -> TMailBoxes_userdb.ls(' + userdb_path + ')');
     lsResults:=COMMON.ls(userdb_path);
     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::"' + lsResults + '" <- TMailBoxes_userdb.ls()');
     ListFiles:=TStringList.Create;
     ListFiles.Delimiter := ';';
     ListFiles.QuoteChar := '|';
     ListFiles.DelimitedText:=lsResults;
    if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::ListFiles->DelimitedText="' + IntToStr(ListFiles.Count) + '"');

     if ListFiles.Count=0 then begin
        if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::ListFiles-> no files seen ->exit');
        ListFiles.Free;
        exit;
     end;

     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::ListUsers-> initialize');
     ListUsers:=TStringList.Create;
     FileDatas:=TStringList.Create;
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='(.+)?\s+(.+)?';
     if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::start loop');
    for i:=0 to ListFiles.Count-1 do begin
        if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::FOR -> parsing '+ ListFiles[i]);
        FileDatas.LoadFromFile(ListFiles[i]);
        if FileDatas.Count>0 then begin
                  for j:=0 to FileDatas.Count-1 do begin
                          if RegExpr.Exec(FileDatas[j]) then begin
                             ListUsers_string:=ExtractFileName(ListFiles[i]) + ';' + RegExpr.Match[1];
                             if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::Match -> ' + ListUsers_string);
                             ListUsers.Add(ListUsers_string);
                          end;

                  end;

        end
        else begin
              if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::'+ ListFiles[i] + ' -> no lines');
        end;


    end;

   if ListUsers.Count>0 then begin
      if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::save file ' + artica_path + '/mailboxes.txt');
      ListUsers.SaveToFile(artica_path + '/mailboxes.txt');
      if Debug=True then LOGS.logs('TMailBoxes_userdb.analyzeMailboxes()::->xShell(/bin/chmod 0777 ' + artica_path + '/mailboxes.txt)');
      xShell('/bin/chmod 0777 ' + artica_path);
      xShell('/bin/chown ' + GLOBAL_INI.get_www_userGroup() + ' ' + artica_path);
      xShell('/bin/chmod 0777 ' + artica_path + '/mailboxes.txt');
      xShell('/bin/chown ' + GLOBAL_INI.get_www_userGroup() +' ' + artica_path + '/mailboxes.txt');
      
   end;
   
   ListFiles.Free;
   ListUsers.Free;
   FileDatas.Free;
   RegExpr.Free;
        if Enable_echo=true then writeln('############################################################################################');

end;
//##############################################################################
procedure TMailBoxes_userdb.CheckUserdbQueue();
var artica_path,ls_results,FileDatas,mail_server:string;
ListFiles:TStringList;
i:integer;
RegExpr,RegQuota:TRegExpr;
MailBoxExists:boolean;
begin
      artica_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
      artica_path:=artica_path + '/ressources/userdb';
      ls_results:=COMMON.lsqueue(artica_path);
      mail_server:=GLOBAL_INI.get_MANAGE_MAILBOX_SERVER();

      if Debug=True then LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::"' + ls_results + '" <- COMMON.lsqueue() mailserver "' + mail_server + '"');
      

      
      ListFiles:=TStringList.Create;
      ListFiles.Delimiter := ';';
      ListFiles.QuoteChar := '|';
      ListFiles.DelimitedText:=ls_results;
      RegExpr:=TRegExpr.Create;
      RegQuota:=TRegExpr.Create;
      RegExpr.Expression:='<USER>(.+?)</USER><MAILDIR>(.+?)</MAILDIR><DOMAIN>(.+?)</DOMAIN><PASSWORD>(.+?)</PASSWORD><QUOTA>([0-9]+)</QUOTA>';
      RegQuota.Expression:='<USER>(.+?)</USER><MAILDIR>(.+?)</MAILDIR><DOMAIN>(.+?)</DOMAIN><QUOTA>([0-9]+)</QUOTA>';

      for i:=0 to ListFiles.Count-1 do begin
        if Debug=True then LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::FOR -> parsing '+ ListFiles[i]);
        FileDatas:=COMMON.ReadFileIntoString(ListFiles[i]);
        if RegExpr.Exec(FileDatas) then begin
             if Debug=True then LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::-> File like mailbox command (' + RegExpr.Match[1] + ' @' + RegExpr.Match[3] + '=' + RegExpr.Match[2] + ' QUOTA=' + RegExpr.Match[5]+ ')');

             if mail_server='courier' then begin
                 if Debug=True then LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::-> TECHNO COURIER HASH');
                 MailBoxExists:=IfMailboxdbUserExists(RegExpr.Match[1],RegExpr.Match[3]);
                 if MailBoxExists=True then begin
                    LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::-> TRUE');
                    ADD_MailboxdbUser(RegExpr.Match[1],RegExpr.Match[3],RegExpr.Match[2],RegExpr.Match[4]);
                    COMMON.killfile(ListFiles[i]);
                 end;



               if MailBoxExists=False then begin
                     if Debug=True then LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::-> FALSE');
                     ADD_MailboxdbUser(RegExpr.Match[1],RegExpr.Match[3],RegExpr.Match[2],RegExpr.Match[4]);
                     COMMON.killfile(ListFiles[i]);
               end;
            end;
            
            if mail_server='cyrus' then begin
                if Debug=True then LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::-> TECHNO CYRUS');
                ADD_CyrusdbUser(RegExpr.Match[1],RegExpr.Match[3],RegExpr.Match[2],RegExpr.Match[4],RegExpr.Match[5]);
                COMMON.killfile(ListFiles[i]);
            end;
            

        end;

   if RegQuota.Exec(FileDatas) then begin
        if Debug=True then LOGS.logs('TMailBoxes_userdb.CheckUserdbQueue()::-> File like set quota command (' + RegQuota.Match[1]  + ' QUOTA=' + RegQuota.Match[4]+ ')');
//         Edit_CyrusdbUserQuota(trim(RegQuota.Match[1]) ,RegQuota.Match[4]);
         COMMON.killfile(ListFiles[i]);
    end;
        
        
        
        
      end;

ListFiles.Free;
RegExpr.Free;
RegQuota.Free;
end;





//##############################################################################
function TMailBoxes_userdb.ADD_CyrusdbUser(user:string;domain:string;path:string;password:string;quota:string):boolean;
var
cyrus_pl,database_method,command_line:string;
mcyrus:Tcyrus;

begin
   cyrus_pl:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/cyrus.pl';
   database_method:=GLOBAL_INI.get_POSTFIX_DATABASE();
    if Debug=True then LOGS.logs('TMailBoxes_userdb.ADD_CyrusdbUser()::-> cyrus.pl path:"' + cyrus_pl + '"');
    if Debug=True then LOGS.logs('TMailBoxes_userdb.ADD_CyrusdbUser()::-> database method:"' + database_method + '"');
    if database_method='hash' then begin
          command_line:='/bin/echo ' + password + '|/usr/sbin/saslpasswd2 -p -c ' + user + ' -u ' + domain;
          xShell(command_line);
    end;
    if length(quota)=0 then quota:='0';
   xShell(cyrus_pl + ' -adduser ' + GLOBAL_INI.Cyrus_get_admin_name() + ' ' + GLOBAL_INI.Cyrus_get_adminpassword + ' ' +user + ' ' + password + ' ' + quota);
    mcyrus:=Tcyrus.Create;
    //mcyrus.ExportMailBoxes(True);
    mcyrus.Free;
     //echo 180872|saslpasswd2 -p -c titi

end;

//##############################################################################

function TMailBoxes_userdb.ADD_MailboxdbUser(user:string;domain:string;path:string;password:string):boolean;
var commandline,commandline2,authdaemonrc_path,userdb_path,mail_boxes_path,map_gid,map_uid:string;

begin
 authdaemonrc_path:=GLOBAL_INI.get_COURIER_AUTHDAEMON_PATH();
 userdb_path:=ExtractFilePath(authdaemonrc_path) + 'userdb/' + domain;
 mail_boxes_path:=GLOBAL_INI.get_COURIER_MAILBOX_PATH();
 map_gid:=GLOBAL_INI.get_COURIER_MAILBOX_PATH_GID();
 map_uid:=GLOBAL_INI.get_COURIER_MAILBOX_PATH_UID();
 commandline:='/usr/sbin/userdb -f '+ userdb_path + ' ' +  user + ' set home=' +mail_boxes_path +' uid=' + map_uid + ' gid=' + map_gid + ' mail=' + mail_boxes_path + '/' + domain + '/' + user;
 commandline2:='/bin/echo '''+ password + '''| /usr/sbin/userdbpw -md5 | /usr/sbin/userdb -f ' + userdb_path + ' ' +  user + ' set systempw';

  if Debug=True then LOGS.logs('TMailBoxes_userdb.ADD_MailboxdbUser()::-> ' + commandline);
  if Debug=True then LOGS.logs('TMailBoxes_userdb.ADD_MailboxdbUser()::-> ' + commandline2);
  xShell(commandline);
  xShell(commandline2);
  LOGS.logs('TMailBoxes_userdb.ADD_MailboxdbUser():: compile IMPA/POP3 database');
  if Debug=True then LOGS.logs('TMailBoxes_userdb.ADD_MailboxdbUser()::-> /usr/sbin/makeuserdb');
  xShell('/usr/sbin/makeuserdb');

end;
//##############################################################################
function TMailBoxes_userdb.IfMailboxdbUserExists(user:string;domain:string):boolean;
var
authdaemonrc_path,userdb_path:string;
ListFiles:TStringList;
i:integer;
RegExpr:TRegExpr;
begin
 authdaemonrc_path:=GLOBAL_INI.get_COURIER_AUTHDAEMON_PATH();
 userdb_path:=ExtractFilePath(authdaemonrc_path) + 'userdb/' + domain;
  if Debug=True then LOGS.logs('TMailBoxes_userdb.IfMailboxdbUserExists()::-> Parsing ' + userdb_path );
 if not FileExists(userdb_path) then begin
       if Debug=True then LOGS.logs('TMailBoxes_userdb.IfMailboxdbUserExists()::-> ' + userdb_path + ' does not exist; return false');
       exit(false);
 end;
 ListFiles:=TStringList.Create;
 ListFiles.LoadFromFile(userdb_path);
 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:=user+'\s+(.+)?';
 for i:=0 to ListFiles.Count-1 do begin
          if RegExpr.Exec(ListFiles[i]) then begin
             if Debug=True then LOGS.logs('TMailBoxes_userdb.IfMailboxdbUserExists()::-> ' + user + ' exist; return true');
              ListFiles.Free;
              RegExpr.Free;
              exit(true);
          end;
 end;
   if Debug=True then LOGS.logs('TMailBoxes_userdb.IfMailboxdbUserExists()::-> ' + user + ' doesn''t exist; return false');
   ListFiles.Free;
   RegExpr.Free;
   exit(false);
end;
function TMailBoxes_userdb.MD5FromFile(path:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5File(path);
exit(MD5Print(Digest));
end;
//#####################################################################################
procedure TMailBoxes_userdb.xShell(cmd:string);
var
   Xresponse:string;
   RegExpr:TRegExpr;
   tlogsRes:TstringList;
   i:integer;
begin
 if debug=true then LOGS.logs(cmd);
 RegExpr:=TRegExpr.Create();
 RegExpr.expression:='>[a-zA-Z0-9\.\/]+';
 if not RegExpr.Exec(cmd) then cmd:=cmd + ' >/tmp/tmp.txt';
 RegExpr.Free;
 Shell(cmd);
 if debug=true then begin
    if Debug=true then LOGS.logs('TMailBoxes_userdb.xShell -> ' + cmd);
    Xresponse:=COMMON.ReadFileIntoString('/tmp/tmp.txt');
    if length(Xresponse)>0 then begin
       tlogsRes:=TstringList.Create;
       tlogsRes.LoadFromFile('/tmp/tmp.txt');
       for i:=0 to tlogsRes.Count-1 do begin;
           LOGS.logs(tlogsRes.Strings[i]);
       end;
        tlogsRes.Free;
    end;
end;
end;
//#####################################################################################

end.

