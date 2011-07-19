unit scanQueue;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}




interface

uses
Classes, SysUtils,Process,strutils,IniFiles,unix,logs,RegExpr in 'RegExpr.pas',md5,sendldap,mailinfos,
smtpsend,mimemess, mimepart,synautil,sqlite3ds,dnssend,synamisc;

type
  TStringDynArray = array of string;


  type
  TscanQueue=class


private
   SOURCE_EMAIL_RELAY_PATH:string;
   USE_DNSMASQ:boolean;
   DirListFiles:TstringList;
   LOGS:Tlogs;


   function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
   function EXTRACT_PARAMETERS(pattern:string):string;
   function  DirFiles(FilePath: string;pattern:string):TstringList;
   procedure CopyFile(SourceFileName, TargetFileName: string);
   procedure DeleteFile(path:string);
   function  Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
   function  RebuildHeader(Email:TTMailInfo):boolean;
   function  DetectDsapmError(error_path:string):boolean;
   function  GetFileSize(path:string):longint;


   //BigHTML;
   procedure BigHtmlMail(Email:TTMailInfo);
   procedure BigHtmlMail_Backup(Email:TTMailInfo);
   function  ParseFile(path:string):TTMailInfo;
   function  RBL_CACHE(rbl_name_server:string;ip_to_test:string;TableName:string):integer;
   procedure EDIT_RBL_CACHE(rbl_name_server:string;ip_to_test:string;res:string;TableName:string);
   function  DSNMASQ_RUN():boolean;
   function  DNSMASQ_PID():string;
   
   //mailman
   function  TRUSTED_LOCAL_USERS(messageinfo:TTMailInfo):boolean;
   function  ScanMailMan(email:TTMailInfo):TTMailInfo;
   function  SendSMTPNotifications(NotificationMailFrom:string;subject:string;mail_to:string;HtmlBody:string;Email:TTMailInfo):boolean;
   function  PutIntoQuarantine(email:TTMailInfo;uid:string;Subfolder:string):TTMailInfo;
   function  PurgeMail(headfile:string;bodyfile:string):boolean;
   function  ResendMailFailed(Email:TTMailInfo;error:string):boolean;

   function  BOGOFILTER_ROBOTS(messageinfo:TTMailInfo):boolean;
   procedure BOGOFILTER_EXECUTE_ROBOT(messageinfo:TTMailInfo;ldap_infos:ldapinfos;robot_type:string);
   
   
   function  WhiteList(Email:TTMailInfo;recipient:string):boolean;
   function  ExplodeMailTo(Email:TTMailInfo):boolean;
   function  ApplyKasRules(recipient:string;Email:TTMailInfo):TTMailInfo;

   //countries deny....
   function ApplyCountryDeny(recipient:string;Email:TTMailInfo):TTMailInfo;
   

   
   //RBL LIst
   function ScanRBL(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ScanSURBL(recipient:string;Email:TTMailInfo):TTMailInfo;

   //mailman
   function MAILMAN_DETECT_ROBOT(messageinfo:TTMailInfo):TTMailInfo;
   
   function ScanAVMail(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ScanUserRegex(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ScanHeaderRegex(header:string;pattern:string;regex:integer;Email:TTMailInfo):boolean;
   function ResendMail(Email:TTMailInfo):boolean;
   procedure ReleaseMail(email:TTMailInfo);
   function MD5FromString(values:string):string;
   procedure SendSQLQuery(mail_path:string;action:string;uid:string;ou:string;quarantine:string;email:TTMailInfo);
   function RetreiveSenderRecipients(Email:TTMailinfo):TTMailinfo;
   function ArticaFilterQueuePath():string;
   function get_ARTICA_PHP_PATH():string;
   Function BuildActionTable(email:TTMailInfo;recipient:string):boolean;

   function ApplyRules(messageinfo:TTMailInfo;recipient:string):TTMailInfo;
   function ApplyFakedSendAddress(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ApplyRenattach(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ApplyDspam(recipient:string;Email:TTMailInfo):TTMailInfo;

   function IsMailToExist(recipient:string):boolean;
   function DeleteMailTO(recipient:string;Email:TTMailInfo):boolean;
   function email_relay_send(Email:TTMailInfo):boolean;
   function ResolvServers(const DNSHost, Domain: AnsiString; const Servers: TStrings): Boolean;


   
   MAILTO_LIST:TstringList;
   MAILTO_LIST_ACTION:TstringList;
   QUEUE_PATH:String;
      D:boolean;
public
      constructor Create();
      procedure Free;
      PROCEDURE Execute(QueueNumber:string);
      procedure eMailRelay_filter(emailPath:string);
      procedure eMailRelay_scan(emailPath:string);
      procedure ScanIPHeaders(emailPath:string);
      function rblinfo(rbl_name_server:string;ip_to_test:string):boolean;
      function surblinfo(rbl_name_server:string;ip_to_test:string):boolean;
      function RemoveMailTo(SourcePath:string;address:string;DestPath:string):string;
      procedure pipe_scan();
      PROCEDURE PurgeQueue();
      PROCEDURE PurgeSqlQueue();
      function  UserInfos(email:string):boolean;
      function  ResendMailFromEmlFile(path:string):boolean;
END;

implementation

constructor TscanQueue.Create();
begin
     LOGS:=TLogs.Create;
     DirListFiles:=TstringList.Create;
     MAILTO_LIST:=TstringList.Create;
     MAILTO_LIST_ACTION:=TStringList.Create;
     QUEUE_PATH:=ArticaFilterQueuePath() + '/queue';
     D:=COMMANDLINE_PARAMETERS('-V');
     USE_DNSMASQ:=DSNMASQ_RUN();
end;
PROCEDURE TscanQueue.Free();
begin
 DirListFiles.Free;
 LOGS.Free;
end;
//#########################################################################################
PROCEDURE TscanQueue.Execute(QueueNumber:string);
var
   MaxFiles:Integer;
   i:integer;
   messageinfo:TTMailInfo;
   QueuePathString:string;
begin
    if Length(QueueNumber)=0 then exit;
    QueuePathString:=QUEUE_PATH + '/' + QueueNumber;
    USE_DNSMASQ:=DSNMASQ_RUN();
    MaxFiles:=10;
    DirFiles(QueuePathString,'*.queue');
    if not D then LOGS.logs('TscanQueue:: "' + IntTOStr(DirListFiles.Count) + '" messages in queue (' + QueuePathString + ')');
    if D then writeln('artica-send:: TscanQueue:: "' + IntTOStr(DirListFiles.Count) + '" messages in queue (' + QueuePathString + ')');
    if DirListFiles.Count<Maxfiles then MaxFiles:=DirListFiles.Count;
    if D then writeln('TscanQueue : Maxfiles=' ,MaxFiles);
    
    for  i:=0 to MaxFiles-1 do begin
    
          MAILTO_LIST.Clear;
          MAILTO_LIST_ACTION.Clear;
          messageinfo:=ParseFile(DirListFiles.Strings[i]);
          LOGS.logs('Execute::[' +IntTOStr(i) + '] from: <' + messageinfo.real_mailfrom + '>(' + messageinfo.mail_from_name+' ' + messageinfo.GeoCountry + '/' + messageinfo.GeoCity + ') to <' + messageinfo.mail_to +'> (' + messageinfo.ldap_uid + ') RATE: ' + IntToStr(messageinfo.X_SpamTest_Rate) + ' Result=[' +messageinfo.Artica_results + ']');
    end;
    
    if DirListFiles.Count=0 then begin
       DirListFiles.Clear;
       DirFiles(QueuePathString,'*.*');
       if DirListFiles.Count>0 then shell('/bin/rm ' + QueuePathString + '/*');
    end;

    DirListFiles.Clear;
end;
//#########################################################################################
PROCEDURE TscanQueue.PurgeQueue();
var
   MaxFiles:Integer;
   i:integer;
   messageinfo:TTMailInfo;
   QueuePathString:string;
   HeadFIle:string;
   BodyFile:string;
   ScanQueuePath:string;
   temp:string;
   D:boolean;
   
begin
 QueuePathString:=ArticaFilterQueuePath();
 D:=COMMANDLINE_PARAMETERS('verbose');
 temp:=EXTRACT_PARAMETERS('--queue-path=(.+)');
 if length(temp)>0 then QueuePathString:=temp;
 if d then writeln('Use "' + QueuePathString + '" as *.eml, *.head scanned files');
 
   temp:=EXTRACT_PARAMETERS('--remote-port=([0-9]+)');
   if length(temp)>0 then begin
        if d then writeln('Use "' + temp + '" as SMTP port');
   end;
   
 
  DirFiles(QueuePathString,'*.eml');
  if DirListFiles.Count=0 then exit;
  for i:=0 to DirListFiles.Count-1 do begin
      BodyFile:=DirListFiles.Strings[i];
      HeadFIle:=ChangeFileExt(DirListFiles.Strings[i],'.head');
      if not FileExists(headfile) then begin
         if FileExists(BodyFile) then DeleteFile(BodyFile);
      end else begin
          PurgeMail(headfile,BodyFile);
      end;
  end;
  
  



end;
//#########################################################################################
PROCEDURE TscanQueue.PurgeSqlQueue();
var
   MaxFiles:Integer;
   i:integer;
   messageinfo:TTMailInfo;
   QueuePathString:string;
   sql:string;
   FileSQL:TStringList;
   dsTest:TSqlite3Dataset;
   RegExpr:TRegExpr;
   sqLOGS:Tlogs;
   D:boolean;
begin
  D:=false;
  QueuePathString:=ArticaFilterQueuePath();
  DirFiles(QueuePathString,'*.sql');
  if DirListFiles.Count=0 then exit;
  D:=COMMANDLINE_PARAMETERS('verbose');


  FileSQL:=TStringList.Create;
  dsTest:=TSqlite3Dataset.Create(nil);
  dsTest.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';;
  sqLOGS:=Tlogs.Create;
  RegExpr:=TRegExpr.Create;




  for i:=0 to DirListFiles.Count-1 do begin
      FileSQL.LoadFromFile(DirListFiles.Strings[i]);
      dsTest.QuickQuery(FileSQL.Text);
      if d then writeln(DirListFiles.Strings[i] + ' --> ' + dsTest.ReturnString);
      LOGS.logs(DirListFiles.Strings[i] + ' --> ' + dsTest.ReturnString);

      RegExpr.Expression:='SQLITE_DONE';
      if RegExpr.Exec(dsTest.ReturnString) then begin
         DeleteFile(DirListFiles.Strings[i]);
      end;
  end;





end;
//#########################################################################################
function TscanQueue.UserInfos(email:string):boolean;
var
   infos:ldapinfos;
   ldap:Tsendldap;
   i:integer;
   RegExpr:TRegExpr;
begin
  ldap:=Tsendldap.Create;
  infos:=ldap.Ldap_infos(email);
  writeln();
  writeln();
  writeln('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln('uid...................:',infos.uid);
  writeln('user dn...............:',infos.user_dn);
  writeln('user ou...............:',infos.user_ou);
  writeln('Trust local users.....:',infos.TrustMyUsers);
  writeln('RBL Action............:',infos.RBL_SERVER_ACTION);
  
  
    write('White list............:');
  for i:=0 to infos.WhiteList.Count-1 do begin
      write('['+infos.WhiteList.Strings[i] + '] ' );
  end;
  writeln('');
  
  
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='robot;'+email+';(.+?);(.+?);(.+)';
   for i:=0 to infos.MAILMAN_ROBOTS.Count-1 do begin
       if RegExpr.Exec(infos.MAILMAN_ROBOTS.Strings[i]) then begin
            LOGS.logs('mailman:: ' + email + ' is a mailman robot for list "' + RegExpr.Match[1] + '" type=' + RegExpr.Match[3] + ' ou=' + RegExpr.Match[2] );
            writeln('RBL Action............:',infos.RBL_SERVER_ACTION);
            writeln('Mailman robot.........:list "' + RegExpr.Match[1] + '" type=' + RegExpr.Match[3] + ' ou=' + RegExpr.Match[2]);
            writeln('Mailman robot.........:infos "' + infos.MAILMAN_ROBOTS.Strings[i] + '"');

       end;

   end;

  RegExpr.Free;
  
  
  
    write('RBL Servers...........:');
  for i:=0 to infos.RBL_SERVERS.Count-1 do begin
      write('['+infos.RBL_SERVERS.Strings[i]+ '] ');
  end;
  writeln('');
  writeln('BOGOFILTER Action.....:',infos.BOGOFILTER_ACTION);
    write('BOGOFILTER Robot......:');
  for i:=0 to infos.BOGOFILTER_ROBOTS.Count-1 do begin
      write('['+infos.BOGOFILTER_ROBOTS.Strings[i] + '] ' );
  end;
   writeln('BOGOFILTER Action.....:',infos.BOGOFILTER_ACTION);
   writeln('HtmlBlock enabled.....:',infos.html_block.enabled);
   for i:=0 to infos.html_block.Rules.Count-1 do begin
   writeln('HtmlBlock rules.......:['+IntTostr(i)+'] "'+ infos.html_block.Rules.Strings[i] + '"');
   end;
  
  
  writeln('');
  writeln('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
  writeln();
  writeln();

end;

//#########################################################################################
function TscanQueue.PurgeMail(headfile:string;bodyfile:string):boolean;

var
   ERROR_REPORTED:TStringlist;
   RECIPIENTS_LIST,mail_to,mail_from:string;
   i,t:integer;
   CommandLine:string;
   RegExpr:TRegExpr;
   LIST:TstringList;
   HEAD:TstringList;
   LOGS:TLogs;
   SMTP:TSMTPSend;
   SMTP_PORT:string;
   temp:string;
   cmd_delete:boolean;
   D:boolean;
begin
   Result:=false;
 LIST:=TstringList.Create;
 HEAD:=TStringList.Create;
 LOGS:=TLogs.Create;
 RegExpr:=TRegExpr.Create;
 SMTP_PORT:='29300';
 cmd_delete:=true;
 D:=COMMANDLINE_PARAMETERS('verbose');
 
  temp:=EXTRACT_PARAMETERS('--remote-port=([0-9]+)');
  if length(temp)>0 then SMTP_PORT:=temp;
  if COMMANDLINE_PARAMETERS('--no-delete-source') then cmd_delete:=false;
 

 
 LOGS.logs('ResendMail:: Load files');
 LIST.LoadFromFile(bodyfile);
 HEAD.LoadFromFile(headfile);
 mail_from:=HEAD.Strings[0];
 mail_to:=HEAD.Strings[1];


     LOGS.logs('ReleaseMail:: to "' + mail_to + '" from "' + mail_from + '"');

     SMTP := TSMTPSend.Create;
     SMTP.TargetHost:='127.0.0.1';
     SMTP.TargetPort:=SMTP_PORT;
     if D then writeln('ReleaseMail:: to "' + mail_to + '" from "' + mail_from + '" -> 127.0.0.1:' + SMTP_PORT);
     
     if not SMTP.Login then begin
        LOGS.logs('ReleaseMail:: Failed connect to 127.0.0.1 ' + SMTP_PORT + ' -> ' + SMTP.FullResult.Text);
        if d then writeln('ReleaseMail:: Failed connect to 127.0.0.1 ' + SMTP_PORT + ' -> ' + SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailFrom(mail_from,Length(LIST.Text)) then begin
       if d then writeln(SMTP.FullResult.Text);
       LOGS.logs(SMTP.FullResult.Text);
       exit(false);
     end;

     if not SMTP.MailTo(mail_to) then begin
        if d then writeln(SMTP.FullResult.Text);
        LOGS.logs(SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailData(LIST) then begin
        if d then writeln(SMTP.FullResult.Text);
        LOGS.logs(SMTP.FullResult.Text);
        exit(false);
     end;
     LOGS.logs(SMTP.FullResult.Text);
    if d then writeln(SMTP.FullResult.Text);
     SMTP.Logout;
     if cmd_delete then begin
        if d then writeln('delete ' + headfile + ' and ' + bodyfile);
        DeleteFile(headfile);
        DeleteFile(bodyfile);
     end;
     exit(true);



end;
//##############################################################################



procedure TscanQueue.eMailRelay_filter(emailPath:string);
var
 messageinfo:TTMailInfo;
 tmp_message:string;
begin
   messageinfo:=ParseFile(emailPath);


end;
//#########################################################################################
procedure TscanQueue.ScanIPHeaders(emailPath:string);
var
 messageinfo:TTMailInfo;
 email:TMailInfo;
 tmp_message:string;
begin

  writeln(ExtractFileName(emailPath));
   email:=TMailInfo.Create;
   messageinfo:=email.Scan(emailPath,messageinfo);
   writeln(messageinfo.mail_from);
   


end;
//#########################################################################################
function TscanQueue.RemoveMailTo(SourcePath:string;address:string;DestPath:string):string;
var email:TMailInfo;
begin
   If not FileExists(SourcePath) then exit;
   email:=TMailInfo.Create;
   email.RemoveMailTo(SourcePath,address,DestPath);
end;
//#########################################################################################
procedure TscanQueue.pipe_scan();
var headpath,newheadpath,CopyDir:string;
    i:integer;
    email_content:TstringList;
    head_content:TStringList;
    From:string;
    mail_to,line:string;
    messageinfo:TTMailInfo;
    email:TMailInfo;
    SourceFilePath:string;
    queue_path:string;
    xldap:Tsendldap;
    
begin
  email:=TMailInfo.Create;
  head_content:=TStringList.Create;
  messageinfo.FullMessage:=TstringList.Create;
  messageinfo.InPutMethod:=True;
  messageinfo.InputMethodSend:=False;
  messageinfo.Artica_results:='send';
  queue_path:=ArticaFilterQueuePath();
  xldap:=Tsendldap.Create;
  
  From:=ParamStr(2);
  mail_to:=ParamStr(3);

  head_content.Add(From);
  head_content.Add(mail_to);
  
  While Not Eof(input) do begin
        Readln(input, line);
        messageinfo.FullMessage.Add( line );
    end;
    
    LOGS.logs('');
    LOGS.logs('');
    LOGS.logs('> > >START------------------------------------------------------------------------------------------------');
    LOGS.logs('input >> ' + IntToStr(messageinfo.FullMessage.Count) + ' ->');

    messageinfo.queue_path:=queue_path;
    messageinfo.real_mailfrom:=From;
    messageinfo.mail_to:=mail_to;

    messageinfo:=email.Scan('',messageinfo);
    messageinfo.ldap:=xldap.Ldap_infos(mail_to);
    messageinfo.ldap_uid:=messageinfo.ldap.uid;
    messageinfo.ldap_ou:=messageinfo.ldap.user_ou;


    messageinfo.HeadFilePath:=queue_path + '/' + messageinfo.MessageMD5 + '.head';
    messageinfo.FilePath:=queue_path + '/' + messageinfo.MessageMD5 + '.eml';
    head_content.SaveToFile(messageinfo.HeadFilePath);
    messageinfo.FullMessage.SaveToFile(messageinfo.FilePath);
    SourceFilePath:=messageinfo.FilePath;
    messageinfo.FilePath_source:=messageinfo.FilePath;
    messageinfo.MessageSizeInt:=GetFileSize(messageinfo.FilePath_source);
    

    
    LOGS.logs('FROM............... : ' +messageinfo.real_mailfrom + ' <' +messageinfo.mail_from + '>');
    LOGS.logs('TO (ldap)...........: ' +messageinfo.ldap_uid + ' <' +mail_to + '>');
    LOGS.logs('Subject............ : ' +messageinfo.Subject);
    LOGS.logs('X-Spam Tests results: ' +IntToStr(messageinfo.X_SpamTest_Rate));

     if BOGOFILTER_ROBOTS(messageinfo) then begin
        LOGS.logs('BOGOFILTER robot detected...');
        LOGS.logs('------------------------------------------------------------------------------------------------> > > END');
        LOGS.logs('');
        LOGS.logs('');
        exit;
     end;

    messageinfo:=MAILMAN_DETECT_ROBOT(messageinfo);
    if messageinfo.mailman_robot then begin
       LOGS.logs('MAILMAN robot detected...');
       messageinfo:=ApplyRules(messageinfo,mail_to)
    end else begin
        if not TRUSTED_LOCAL_USERS(messageinfo) then begin
           messageinfo:=ApplyRules(messageinfo,mail_to)
           end else begin
            BuildActionTable(messageinfo,mail_to);
           end;
    end;




    ReleaseMail(messageinfo);
    LOGS.logs('------------------------------------------------------------------------------------------------> > > END');

    LOGS.logs('');
    LOGS.logs('');
    email.Free;
    halt(0);
    exit;
    

end;
//#########################################################################################
function TscanQueue.TRUSTED_LOCAL_USERS(messageinfo:TTMailInfo):boolean;
var
 ldap:Tsendldap;
 ldap_infos:ldapinfos;
begin
  result:=false;
  if messageinfo.ldap.TrustMyUsers<>'yes' then exit(false);

  if length(messageinfo.ldap.user_ou)=0 then exit(false);
  ldap:=Tsendldap.Create;
  ldap_infos:=ldap.Ldap_infos(messageinfo.real_mailfrom);
  Ldap.free;
  if ldap_infos.user_ou=messageinfo.ldap.user_ou then begin
     LOGS.logs(messageinfo.real_mailfrom +' is trusted sender (has a local user) ' + ldap_infos.user_ou + '=' + messageinfo.ldap.user_ou);
     exit(true);
  end;
  


end;
//#########################################################################################
function TscanQueue.MAILMAN_DETECT_ROBOT(messageinfo:TTMailInfo):TTMailInfo;
var
   RegExpr:TRegExpr;
   line:string;
   i:integer;
begin
   result:=messageinfo;
   messageinfo.mailman_robot:=false;
   if messageinfo.ldap.MAILMAN_ROBOTS.Count=0 then exit();
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='cn="'+messageinfo.mail_to+'"';
   for i:=0 to messageinfo.ldap.MAILMAN_ROBOTS.Count-1 do begin
       if RegExpr.Exec(messageinfo.ldap.MAILMAN_ROBOTS.Strings[i]) then begin
            line:=messageinfo.ldap.MAILMAN_ROBOTS.Strings[i];
            RegExpr.Expression:='ou="(.+?)"';
            if RegExpr.Exec(line) then messageinfo.ldap_ou:=RegExpr.Match[1];
            messageinfo.ldap_uid:=messageinfo.mail_to;

            RegExpr.Expression:='mailmanrobottype="(.+?)"';
            if RegExpr.Exec(line) then messageinfo.mailman_listtype:=RegExpr.Match[1];

            RegExpr.Expression:='mailmandistrilist="(.+?)"';
            if RegExpr.Exec(line) then messageinfo.mailman_listname:=RegExpr.Match[1];
            
            LOGS.logs('mailman:: ' + messageinfo.mail_to + ' is a mailman robot for list "' + messageinfo.mailman_listname+ '" type="' + messageinfo.mailman_listtype + '" ou="' + messageinfo.ldap_ou + '"');
            messageinfo.Artica_results:='send';
            messageinfo.mailman_robot:=true;
            result:=messageinfo;
            break;
       end else begin
           //LOGS.logs('mailman:: ' +messageinfo.ldap.MAILMAN_ROBOTS.Strings[i] + ' failed for "' + RegExpr.Expression + '"');
       end;
   
   end;
  if messageinfo.mailman_robot=false then LOGS.logs('MAILMAN:: <' + messageinfo.mail_to + '> not a mailman robot in ' + IntToStr(messageinfo.ldap.MAILMAN_ROBOTS.Count) + ' robots list ..');
  RegExpr.Free;

end;



//#########################################################################################
function TscanQueue.BOGOFILTER_ROBOTS(messageinfo:TTMailInfo):boolean;
var
   RegExpr:TRegExpr;
   i:integer;
   email:string;
   ldap_infos:ldapinfos;
   ldap:Tsendldap;
begin
   if not FileExists('/usr/local/bin/bogofilter') then exit(false);
   ldap:=Tsendldap.Create;
   ldap_infos:=ldap.Ldap_infos(messageinfo.real_mailfrom);
   result:=false;


    if ldap_infos.BOGOFILTER_ROBOTS.Count=0 then begin
       LOGS.logs('bogofilter:: no robots here for ' + messageinfo.mail_from);
       ldap.Free;
       exit(false);
    end;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='(.+?);(.+)';
    for i:=0 to ldap_infos.BOGOFILTER_ROBOTS.Count-1 do begin
        if RegExpr.Exec(ldap_infos.BOGOFILTER_ROBOTS.Strings[i]) then begin
            if LowerCase(RegExpr.Match[1])=messageinfo.mail_to then begin
            
                   BOGOFILTER_EXECUTE_ROBOT(messageinfo,ldap_infos,RegExpr.Match[2]);
                   result:=true;
            end;
        end;
    
    end;
end;
//#########################################################################################
procedure TscanQueue.BOGOFILTER_EXECUTE_ROBOT(messageinfo:TTMailInfo;ldap_infos:ldapinfos;robot_type:string);
var
   cmd_line:string;
   queue_path:string;
begin
    queue_path:=messageinfo.queue_path;

     if robot_type='ham' then begin
       cmd_line:='/usr/local/bin/bogofilter --register-ham --no-config-file --bogofilter-dir=' + queue_path + '/' + ldap_infos.uid + ' --input-file=' + messageinfo.FilePath_source;
     end;

     if robot_type='spam' then begin
        cmd_line:='/usr/local/bin/bogofilter --register-spam --no-config-file --bogofilter-dir=' + queue_path + '/' + ldap_infos.uid + ' --input-file=' + messageinfo.FilePath_source;
     
     end;
     LOGS.logs('bogofilter:: register ' + robot_type + ' for ' + ldap_infos.uid);
     LOGS.logs('bogofilter:: ' + cmd_line);
     Shell( cmd_line + ' >/dev/null 2>&1');
     DeleteFile(messageinfo.FilePath_source);
     DeleteFile(messageinfo.HeadFilePath);


end;




//#########################################################################################
procedure TscanQueue.eMailRelay_scan(emailPath:string);
var headpath,newheadpath,CopyDir:string;
    i:integer;
    Exec_path:string;
    messageinfo:TTMailInfo;
begin
    LOGS.logs('');
    LOGS.logs('');
    LOGS.logs('> > >START------------------------------------------------------------------------------------------------');
    LOGS.logs('Receive ' + emailPath + ' -> in-process queue');
    SOURCE_EMAIL_RELAY_PATH:=emailPath;
    headpath:=ChangeFileExt(emailPath,'.envelope.new');
    CopyDir:=ExtractFilePath(emailPath) + 'in-process';


    
    
    if not DirectoryExists(ArticaFilterQueuePath() + '/in-process') then begin
        LOGS.logs('Creating folder ' + ArticaFilterQueuePath() + '/in-process');
        forceDirectories(ArticaFilterQueuePath() + '/in-process');
    end;
    
    CopyFile(emailPath,CopyDir+ '/' + extractFileName(emailPath));
    CopyFile(headpath,CopyDir+ '/' + extractFileName(headpath));
    
    LOGS.logs('eMailRelay_scan:: block ' + emailPath);
    eMailRelay_filter(emailPath);
    
    LOGS.logs(' Delete: ' + CopyDir+ '/' + extractFileName(emailPath) );
    LOGS.logs(' Delete: ' + CopyDir+ '/' + extractFileName(headpath) );
    
    DeleteFile(CopyDir+ '/' + extractFileName(emailPath));
    DeleteFile(CopyDir+ '/' + extractFileName(headpath));


    LOGS.logs('------------------------------------------------------------------------------------------------> > > END');
    LOGS.logs('');
    LOGS.logs('');
     halt(0);

    exit;
end;
//#########################################################################################
procedure TscanQueue.ReleaseMail(email:TTMailInfo);

   var
      Message_path:string;
      uid,HeadFilePath:string;
      ldap:Tsendldap;
      action:string;
      actions:TStringDynArray;
      i:integer;
      recipient:string;
      quarantine:boolean;
      kill,virus_quarantine:boolean;
      artica_action,ou:string;
      ActionQuarantine:string;
      Original_mailFilePath,tmp:string;

begin
     HeadFilePath:=ChangeFileExt(email.FilePath,'.head');
     ActionQuarantine:='0';
    ldap:=Tsendldap.Create();

             LOGS.logs('ReleaseMail:: ' + email.mail_to + ' actions number=' + IntToStr(MAILTO_LIST_ACTION.count));
             quarantine:=false;
             virus_quarantine:=false;
             kill:=false;
             artica_action:='send';
             email.InputMethodSend:=True;
        
             actions:=Explode(';',MAILTO_LIST_ACTION.Strings[0]);
             recipient:=actions[0];
             uid:=actions[1];
             artica_action:=actions[5];
             ou:=actions[6];
          
          
             if actions[2]='yes' then quarantine:=true;
             if actions[3]='yes' then kill:=true;
             if actions[4]='yes' then virus_quarantine:=true;
             Original_mailFilePath:=email.FilePath;
             tmp:=ldap.eMailFromUid(uid);
             LOGS.logs('ReleaseMail:: mail status=' + artica_action + ' for account ' + ou +'/' + uid + ' <' + recipient + '> --> ' +tmp);
             if email.InputMethodSend then LOGS.logs('ReleaseMail:: ResendMail:: InputMethodSend=True') else LOGS.logs('ReleaseMail:: ResendMail:: InputMethodSend=false');
             if email.mailman_robot=true then LOGS.logs('ReleaseMail:: This mail is generated to mailman robot ' +tmp);
             

      if length(tmp)>0 then begin
             
             ForceDirectories('/var/quarantines/procmail/' + uid + '/quarantine');
             ForceDirectories('/var/quarantines/procmail/' + uid + '/viruses');
             
             if not DirectoryExists('/var/quarantines/procmail/' + uid + '/quarantine')then begin
                   shell('/bin/chmod 755 /var/quarantines/procmail');
                   ForceDirectories('/var/quarantines/procmail/' + uid);
                   shell('/bin/chmod 755 /var/quarantines/procmail/' + uid);
                   ForceDirectories('/var/quarantines/procmail/' + uid + '/quarantine');
                   ForceDirectories('/var/quarantines/procmail/' + uid + '/viruses');
                   shell('/bin/chmod -R 755 /var/quarantines/procmail/' + uid);
             end;
             

             if quarantine then LOGS.logs('ACTION=QUARANTINE') else LOGS.logs('ACTION=NO QUARANTINE');
             if kill then LOGS.logs('ACTION=DELETE') else LOGS.logs('ACTION=NO DELETE');
             if email.InputMethodSend then LOGS.logs('InputMethodSend=TRUE') else LOGS.logs('InputMethodSend=FALSE');
             LOGS.logs('ACTION=' + artica_action);

         //---------------- message is infected ------------------------------
             if artica_action='infected' then begin
                if virus_quarantine=true then begin
                     email:=PutIntoQuarantine(email,uid,'infected');
                     ActionQuarantine:='1';
                     DeleteMailTO(recipient,email);
                end;
                
                if kill then begin
                    DeleteMailTO(recipient,email);
                    email.InputMethodSend:=False;
                end;
             end;
          
          if artica_action<>'white' then begin
            //---------------- message is black listed  ------------------------------
                 if artica_action='black' then begin
                    DeleteMailTO(recipient,email);
                    email.InputMethodSend:=False;
                 end;
          //---------------- message is filtered ------------------------------
                 if artica_action='filtered' then begin
                    if quarantine=true then begin
                       email:=PutIntoQuarantine(email,uid,'filtered');
                       ActionQuarantine:='1';
                       DeleteMailTO(recipient,email);
                       email.InputMethodSend:=False;
                    end;
                 end;
          
              //---------------- message is spam ------------------------------
                 if artica_action='spam' then begin
                    if quarantine then begin
                       email:=PutIntoQuarantine(email,uid,'spam');
                       ActionQuarantine:='1';
                       DeleteMailTO(recipient,email);
                    end;

                    if kill then begin
                       DeleteMailTO(recipient,email);
                        email.InputMethodSend:=False;
                    end;
                 end;
                 
              //---------------- message is bogo ------------------------------
                 if artica_action='bogo' then begin
                    if quarantine then begin
                       email:=PutIntoQuarantine(email,uid,'spam');
                       ActionQuarantine:='1';
                       DeleteMailTO(recipient,email);
                    end;

                    if kill then begin
                        DeleteMailTO(recipient,email);
                        email.InputMethodSend:=False;
                    end;
                 end;
                 
                 
               //---------------- message is faked  ------------------------------
                 if artica_action='faked' then begin
                    if quarantine then begin
                       email:=PutIntoQuarantine(email,uid,'faked');
                       ActionQuarantine:='1';
                    end;

                    if kill then begin
                       DeleteMailTO(recipient,email);
                       email.InputMethodSend:=False;
                    end;
                    
                 end;
            end;
       end;
       
       
       //************ BigHtml ****************************************************
       LOGS.logs('ReleaseMail:: Big Html rule ???.....');
       BigHtmlMail(email);


     //---------------- message is mailman ------------------------------
       if email.mailman_robot=true then begin
            LOGS.logs('ReleaseMail:: This email is a mailman robot, terminate communication...');
            DeleteMailTO(recipient,email);
            email.InputMethodSend:=False;
       end;
   
   SendSQLQuery(email.FilePath,artica_action,uid,ou,ActionQuarantine,email);


    if email.InputMethodSend then LOGS.logs('ReleaseMail --> ResendMail:: InputMethodSend=True') else LOGS.logs('ReleaseMail --> ResendMail:: InputMethodSend=false');
    ResendMail(email);


end;
//#########################################################################################
function TscanQueue.PutIntoQuarantine(email:TTMailInfo;uid:string;Subfolder:string):TTMailInfo;
var
   Message_path:string;
   LOGS:TLogs;
   fileName:string;
begin

     ForceDirectories('/var/quarantines/procmail/' + uid + '/quarantine');
     LOGS:=TLogs.Create;

     if not DirectoryExists('/var/quarantines/procmail/' + uid + '/quarantine')then begin
        shell('/bin/chmod 755 /var/quarantines/procmail');
        ForceDirectories('/var/quarantines/procmail/' + uid);
        shell('/bin/chmod -R 755 /var/quarantines/procmail/' + uid);
     end;

   if not DirectoryExists('/var/quarantines/procmail/' + uid + '/quarantine')then LOGS.logs('ReleaseMail:: Warn !! unable to create folder /var/quarantines/procmail/' + uid + '/quarantine');
   if email.InPutMethod=True then fileName:=email.MessageMD5 +'.eml' else fileName:=ExtractFileName(email.FilePath);
   Message_path:='/var/quarantines/procmail/' + uid + '/quarantine/' + Subfolder + '.'+ uid + '.'+ fileName;


   if email.InPutMethod=True then begin
          LOGS.logs('quarantine engine --> Input method..."' + Message_path + '"');
          email.FullMessage.SaveToFile(Message_path);
   
   end else begin
       LOGS.logs('quarantine engine --> Copy file "' + Message_path + '"');
       CopyFile(email.FilePath,Message_path);
   end;
   email.FilePath:=Message_path;
   result:=email;
end;

//#########################################################################################



procedure TscanQueue.SendSQLQuery(mail_path:string;action:string;uid:string;ou:string;quarantine:string;email:TTMailInfo);
var sql:string;
    QueuePath:string;
    sqLOGS:Tlogs;
    FileTemp:string;
    database:string;
    dsTest:TSqlite3Dataset;
    RegExpr:TRegExpr;
    DatabasePath:string;
    sql_err_file:string;
    sql_err:TStringList;
begin
     DatabasePath:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
     dsTest:=TSqlite3Dataset.Create(nil);
     dsTest.FileName:=DatabasePath;
     sqLOGS:=Tlogs.Create;
     RegExpr:=TRegExpr.Create;
    
    
    email.real_mailfrom:=AnsiReplaceText(email.real_mailfrom,'"','""');
    email.real_mailfrom_domain:=AnsiReplaceText(email.real_mailfrom_domain,'"','""');
    email.mail_to:=AnsiReplaceText(email.mail_to,'"','""');
    email.Subject:=AnsiReplaceText(email.Subject,'"','""');
    email.MessageDate:=AnsiReplaceText(email.MessageDate,'"','""');
    email.GeoISP:=AnsiReplaceText(email.GeoISP,'"','');
    email.GeoCountry:=AnsiReplaceText(email.GeoCountry,'"','');
    email.GeoCity:=AnsiReplaceText(email.GeoCity,'"','');
    
    
    sql:='INSERT INTO messages (MessageID,mail_from,mailfrom_domain,mail_to,subject,zDate,received_date,SpamRate,message_path,filter_action,ou,MailSize,SpamInfos,quarantine,GeoISP,GeoCountry,';
    sql:=sql+ 'GeoCity,';
    sql:=sql+ 'GeoTCPIP,';
    sql:=sql+ 'zMD5,';
    sql:=sql+ 'dspam_result,';
    sql:=sql+ 'dspam_class,';
    sql:=sql+ 'dspam_probability,';
    sql:=sql+ 'dspam_confidence,';
    sql:=sql+ 'dspam_signature';
    sql:=sql+ ') ';
    sql:=sql + 'VALUES("'+ email.MessageID+'","' + email.real_mailfrom+'","' + email.real_mailfrom_domain+'","' + email.mail_to +'","'+email.Subject+'",';
    sql:=sql + '"'+email.MessageDate+'",datetime(''now''),"'+IntToStr(email.X_SpamTest_Rate)+'","'+email.FilePath+'","' +action+'","' +ou+'","' + email.MessageSize + '","' + email.X_SpamTest_Info+'",';
    sql:=sql + '"' + quarantine + '","' +email.GeoISP + '","' + email.GeoCountry + '","' + email.GeoCity + '","' + email.GeoIP + '","' + email.MessageMD5+ '"';
    sql:=sql + ',"' + email.dspam_result + '"';
    sql:=sql + ',"' + email.dspam_class + '"';
    sql:=sql + ',"' + email.dspam_probability + '"';
    sql:=sql + ',"' + email.dspam_confidence + '"';
    sql:=sql + ',"' + email.dspam_signature + '"';
    sql:=sql + ')';

    TRY
    dsTest.QuickQuery(sql);
    LOGS.logs(sql+'=' +DatabasePath + '  "' + dsTest.ReturnString + '"');
    sqLOGS.logs(dsTest.ReturnString);
    RegExpr.Expression:='SQLITE_DONE';
    if not RegExpr.Exec(dsTest.ReturnString) then begin
          sql_err:=TStringList.Create;
          sql_err.Add(sql);
          sql_err_file:=ChangeFileExt(email.FilePath,'.sql');
          sqLOGS.logs('Put error to ' +sql_err_file);
          sql_err.SaveToFile(sql_err_file);
          sql_err.Free;
    end;
    
    
    
    EXCEPT
    sqLogs.ERRORS('SendSQLQuery:: ERROR generated ' +  dsTest.ReturnString);
    sqLogs.ERRORS(sql);
    END;
    dsTest.Close;
    dsTest.Free;
    sqLOGS.Free;
  
    

end;
//#########################################################################################




function TscanQueue.ArticaFilterQueuePath():string;
var ini:TIniFile;
begin
 ini:=TIniFile.Create('/etc/artica-postfix/artica-filter.conf');
 result:=ini.ReadString('INFOS','QueuePath','');
 if length(trim(result))=0 then result:='/var/spool/artica-filter';
end;
//##############################################################################
Function TscanQueue.BuildActionTable(email:TTMailInfo;recipient:string):boolean;
var
   ldap:Tsendldap;
   uid:string;
   ou:string;
   RegExpr:TRegExpr;
   quarantine,kill,virus_quarantine,log_add:string;
begin
     ldap:=Tsendldap.Create();
     uid:=ldap.uidFromMail(recipient);
     ou:=ldap.OU_From_eMail(recipient);
     quarantine:='no';
     kill:='no';
     virus_quarantine:='no';
     log_add:='';

     if email.GoInQuarantine=true then quarantine:='yes';
     if email.KillMail=true then kill:='yes';
     if email.GoInVirusQuarantine=true then virus_quarantine:='yes';
     
     if email.mailman_robot=true then begin
        log_add:=log_add + ' --> Mailman robot';
        email.KillMail:=true
     end;
     
     LOGS.logs('BuildActionTable:: <' + recipient + '> --->' +email.Artica_results + log_add);
     if length(uid)=0 then uid:=recipient;
     if length(ou)=0 then begin
         RegExpr:=TRegExpr.Create;
         RegExpr.Expression:='@([a-zA-Z0-9\.\-_]+)';
         if RegExpr.Exec(recipient) then ou:=RegExpr.Match[1];
         RegExpr.Free;
     end;
     
     
     MAILTO_LIST_ACTION.Add(recipient+';' + uid + ';'+quarantine+';' + kill+';'+virus_quarantine + ';' + email.Artica_results+ ';' + ou);
end;
//##############################################################################
function TscanQueue.ParseFile(path:string):TTMailInfo;

var
   email:TMailInfo;
   messageinfo:TTMailInfo;
   HeadPth:string;
   i:integer;
   recipient:string;
begin
  if D then writeln('ParseFile ' + path);
      email:=TMailInfo.Create;
  TRY
  

  
  messageinfo:=email.Scan(path,messageinfo);
  ExplodeMailTo(messageinfo);
  MAILTO_LIST_ACTION.Clear;
  LOGS.logs('ParseFile:: number of recipients ' + IntToStr(MAILTO_LIST.Count));
  USE_DNSMASQ:=DSNMASQ_RUN();
  if not USE_DNSMASQ then LOGS.logs('ParseFile::DNSMASQ is disabled');
  
  for i:=0 to MAILTO_LIST.Count -1 do begin
         recipient:=MAILTO_LIST.Strings[i];
         messageinfo.Artica_results:='send';
         LOGS.logs('');
         LOGS.logs('ParseFile::************************************************************************');
         messageinfo:=ApplyRules(messageinfo,recipient);
         LOGS.logs('ParseFile::******************* <' + recipient + '> ******************* ' + messageinfo.Artica_results);
         LOGS.logs('ParseFile::************************************************************************');

         LOGS.logs('');
         
    end;
    ReleaseMail(messageinfo);

  
  FINALLY
         email.Free;

  end;
       exit(messageinfo);
end;
//#########################################################################################
function TscanQueue.ApplyRules(messageinfo:TTMailInfo;recipient:string):TTMailInfo;
var D:boolean;

begin
     D:=COMMANDLINE_PARAMETERS('verbose');

     
     if D then LOGS.logs('-->ApplyRules <--;');
     if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
        end;

     LOGS.logs('ApplyRules:: Scan email for ' + recipient + ' rules');
     

     
 //************ Extensions cheking ****************************************************
   LOGS.logs('ApplyRules:: RENATTACH ???.....');
   if D then LOGS.logs('-->ApplyRenattach();');
   messageinfo:=ApplyRenattach(recipient,messageinfo);
     if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
     end;


 //************ Antivirus ****************************************************
        if not FileExists('/opt/kav/5.6/kavmilter/bin/kavmilter') then begin
           LOGS.logs('ApplyRules:: ANTIVIRUS ???.....');
           if D then LOGS.logs('--> ScanAVMail();');
           messageinfo:=ScanAVMail(recipient,messageinfo);
           if messageinfo.Artica_results='infected' then begin
              BuildActionTable(messageinfo,recipient);
              exit(messageinfo);
           end;
        end;
        

        
 //************ WhiteList ****************************************************
         LOGS.logs('ApplyRules:: WHITE LIST ???.....');
         if WhiteList(messageinfo,recipient) then begin
            messageinfo.Artica_results:='send';
            messageinfo.WHITE_LISTED:=True;
         end;
         
 //************ anti-spam ****************************************************
        LOGS.logs('ApplyRules:: ANTI-SPAM ???.....');
        messageinfo:=ApplyKasRules(recipient,messageinfo);
        if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
        end;
        
        
 //************ bogofilter ****************************************************
       LOGS.logs('ApplyRules:: BOGOFILTER ???.....');
        messageinfo:=ApplyDspam(recipient,messageinfo);
        if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
        end;


  //************ Faked sender ****************************************************
       if D then LOGS.logs('ApplyRules:: Detect Faked sender address');
       LOGS.logs('ApplyRules:: FAKED ???.....');
        messageinfo:=ApplyFakedSendAddress(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
  //************ Country Deny ****************************************************
        LOGS.logs('ApplyRules:: COUNTRY DENY ???.....');
         messageinfo:=ApplyCountryDeny(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
 //************ RBL****************************************************
         LOGS.logs('ApplyRules:: RBL ???.....');
         messageinfo:=ScanRBL(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
 //************ SURBL****************************************************
         LOGS.logs('ApplyRules:: SURBL ???.....');
         messageinfo:=ScanSURBL(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
         
//************ User defined rules ****************************************************
        LOGS.logs('ApplyRules:: User defined rules ???.....');
        messageinfo:=ScanUserRegex(recipient,messageinfo);
        LOGS.logs('ApplyRules:: ScanUserRegex:='+ messageinfo.Artica_results);
        if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
        end;

//************ mailman ****************************************************
       LOGS.logs('ApplyRules:: MAILMAN ???.....');
        if messageinfo.mailman_robot=true then begin
              ScanMailMan(messageinfo);
              BuildActionTable(messageinfo,recipient);
              exit(messageinfo);
         end else begin
             LOGS.logs('ApplyRules:: ' + recipient + ' Not a mailman robot...');
         end;

        messageinfo.Artica_results:='send';
        BuildActionTable(messageinfo,recipient);
        exit(messageinfo);
end;
//#########################################################################################
function TscanQueue.DeleteMailTO(recipient:string;Email:TTMailInfo):boolean;
var
   i:integer;
   LIST:TstringList;
   HeadFilePath:string;
   dspam_temp_path:string;
   commandline:string;
   zLIST:TStringList;
begin

    if email.InPutMethod=true then begin
        dspam_temp_path:=ChangeFileExt(email.FilePath_source,'.bogo');
     if email.mailman_robot=false then begin
       if email.Artica_results<>'bogo' then begin
          if FileExists('/usr/local/bin/bogofilter') then begin
             LOGS.logs('BOGOFILTER INSTALLED : learn this spam');
             commandline:='/usr/local/bin/bogofilter  --register-spam --use-syslog --no-config-file --bogofilter-dir=' + email.queue_path + '/bogofilter/users/' +email.ldap_uid + ' --input-file=' + email.FilePath_source + ' >/dev/null 2>&1';;
             LOGS.logs(commandline);
             shell(commandline);
          end else begin
            LOGS.logs('BOGOFILTER IS NOT INSTALLED : unable to learn this spam');
          end;
      end;
     end;
    DeleteFile(dspam_temp_path);
    DeleteFile(email.FilePath_source);
    DeleteFile(email.HeadFilePath);
    exit();
    end;


    for i:=0 to MAILTO_LIST.count-1 do begin
        if trim(MAILTO_LIST.Strings[i])=trim(recipient) then MAILTO_LIST.Strings[i]:='';
    end;
    
    if Email.AsEmailRelay=true then begin

       HeadFilePath:=ChangeFileExt(SOURCE_EMAIL_RELAY_PATH,'.envelope.new');
       LOGS.logs('Delete recipient "' + recipient + '" in "' + HeadFilePath+ '"');
       
       RemoveMailTo(SOURCE_EMAIL_RELAY_PATH,recipient,SOURCE_EMAIL_RELAY_PATH);
       
       if not FileExists(HeadFilePath) then exit;
       LIST:=TstringList.Create;
       LIST.LoadFromFile(HeadFilePath);
       For i:=0 to LIST.Count -1 do begin
           if pos(recipient,LIST.Strings[i])>0 then begin
              LOGS.logs('Delete recipient "' + LIST.Strings[i] + '"');
              LIST.Delete(i);
              LIST.SaveToFile(HeadFilePath);
              LIST.Free;
              DeleteMailTO(recipient,Email);
              RebuildHeader(Email);
              exit;
           end;
       end;



    end;
    
end;
//#########################################################################################
function TscanQueue.RebuildHeader(Email:TTMailInfo):boolean;
var
   RegExpr     :TRegExpr;
   HeadFilePath:string;
   MyFILE      :TstringList;
   MailtoCount:integer;
   i:          integer;
begin
  HeadFilePath:=ChangeFileExt(SOURCE_EMAIL_RELAY_PATH,'.envelope.new');
  MyFILE:=TStringList.create;
  RegExpr:=TRegExpr.Create;


  RegExpr.Expression:='X-MailRelay-To-Remote';
  MyFILE.LoadFromFile(HeadFilePath);

  for i:=0 to MyFILE.Count -1 do begin
     if RegExpr.Exec(MyFILE.Strings[i]) then inc(MailtoCount);
  end;
  
  LOGS.logs('RebuildHeader: new header as now ' + IntToStr(MailtoCount) + ' remote recipient(s)');
  
  RegExpr.Expression:='X-MailRelay-ToCount:\s+([0-9]+)';
  for i:=0 to MyFILE.Count -1 do begin
     if RegExpr.Exec(MyFILE.Strings[i]) then begin
           LOGS.logs('RebuildHeader:: Change X-MailRelay-ToCount from ' + RegExpr.Match[1] + ' to ' + IntToStr(MailtoCount));
           MyFILE.Strings[i]:='X-MailRelay-ToCount: ' + IntToStr(MailtoCount);
     end;
  end;
   MyFILE.SaveToFile(HeadFilePath);
  
end;

//#########################################################################################
function TscanQueue.ExplodeMailTo(Email:TTMailInfo):boolean;
var
   i:integer;
   ldap:Tsendldap;
   uid:string;
begin
   MAILTO_LIST.Clear;
   ldap:=Tsendldap.Create;
   LOGS.logs('ExplodeMailTo::' + IntToStr(length(email.RecipientsList)-1) + ' recipients');
   for i:=0 to length(email.RecipientsList)-1 do begin
        if not IsMailToExist(email.RecipientsList[i]) then begin
           uid:=ldap.uidFromMail(email.RecipientsList[i]);
           if length(uid)>0 then begin
              LOGS.logs('ExplodeMailTo:: ' +IntTostr(i) + '] <'+email.RecipientsList[i] + '> --> will scan for ' + uid);
              MAILTO_LIST.Add(email.RecipientsList[i]);
           end;
        end;
   end;

end;
//#########################################################################################
function TscanQueue.IsMailToExist(recipient:string):boolean;
var i:integer;
begin
result:=false;
   for i:=0 to MAILTO_LIST.Count -1 do begin
        if trim(MAILTO_LIST.Strings[i])=trim(recipient) then begin
              result:=true;
              break;
        end;
   end;
end;
//#########################################################################################
function TscanQueue.ScanMailMan(email:TTMailInfo):TTMailInfo;
var cmd:string;
begin
    if not FileExists('/opt/artica/mailman/mail/mailman') then begin
       LOGS.logs('ScanMailMan:: unable to stat /opt/artica/mailman/mail/mailman (aborting)');
       email.mailman_robot:=false;
       exit(email);
    end;
    
     if email.mailman_listtype='main' then begin
            cmd:='/opt/artica/mailman/mail/mailman post ' + email.mailman_listname + ' <' + email.FilePath_source;
            LOGS.logs('ScanMailMan:: executing ' + cmd);
            shell(cmd);
            exit(email);
     end;
     
     cmd:='/opt/artica/mailman/mail/mailman ' + email.mailman_listtype + ' ' + email.mailman_listname + ' <' + email.FilePath_source;
     LOGS.logs('ScanMailMan:: executing ' + cmd);
     shell(cmd);
     exit(email);


end;
//#########################################################################################
function TscanQueue.ApplyFakedSendAddress(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   RegExpr:TRegExpr;
   ldap:Tsendldap;
   Parameter:string;
   SenderName:string;
   ConSender:string;
   ou:string;
begin
  if length(trim(Email.real_mailfrom))=0 then exit;
  if email.WHITE_LISTED=true then exit(email);
  if D then LOGS.logs('ApplyFakedSendAddress:: Initialize');
  ldap:=Tsendldap.Create;
  result:=Email;
  if D then LOGS.logs('ApplyFakedSendAddress:: get ou from ' + recipient);
  ou:=trim(ldap.OU_From_eMail(trim(recipient)));
  if length(ou)=0 then exit;
  if D then LOGS.logs('ApplyFakedSendAddress:: get Parameter from  ' + ou);
  Parameter:=ldap.FackedSenderParameters(ou);
  if D then LOGS.logs('ApplyFakedSendAddress:: Parameter=' + Parameter);
  if Parameter='pass' then begin
     ldap:=Tsendldap.Create;
     exit(Email)
  end;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='<(.+?)>';
  
  if D then LOGS.logs('ApplyFakedSendAddress:: ou=' + ou + ';Parameter=' + Parameter);
  
  if not RegExpr.Exec(Email.mail_from_name) then begin
     LOGS.logs('ApplyFakedSendAddress:: unable to match into ' + Email.mail_from_name + ' aborting...');
     exit(Email);
  end;
  SenderName:=LowerCase(RegExpr.Match[1]);
  ConSender:=LowerCase(Email.real_mailfrom);
  
   LOGS.logs('ApplyFakedSendAddress:: '+ConSender + '< >' + SenderName);
  if ConSender<>SenderName then begin
   Email.Artica_results:='faked';
   if Parameter='delete' then email.KillMail:=true;
      if Parameter='quarantine' then begin
            email.KillMail:=true;
            email.GoInQuarantine:=true;
      end;
   end;

 LOGS.logs('ApplyFakedSendAddress:: '+Email.Artica_results);
 RegExpr.Free;
 ldap.free;
 result:=Email;
  

end;

//#########################################################################################
function TscanQueue.ApplyCountryDeny(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   RegExpr:TRegExpr;
   ldap:Tsendldap;
   ou:string;
   TMP:TStringDynArray;
   i:integer;
begin
  if length(trim(Email.real_mailfrom))=0 then exit;
  if email.WHITE_LISTED=true then exit(email);
  if D then LOGS.logs('ApplyCountryDeny:: Initialize');
  ldap:=Tsendldap.Create;
  result:=Email;
  
  if length(email.ldap_ou)=0 then begin
     if D then LOGS.logs('ApplyCountryDeny:: get ou from ' + recipient);
        email.ldap_ou:=trim(ldap.OU_From_eMail(trim(recipient)));
  end;
  
  
  if length(ou)=0 then exit;
  if D then LOGS.logs('ApplyCountryDeny:: get Parameter from  ' + ou);
  TMP:=ldap.LoadOUCountriesDeny(ou);
  
  
  if length(TMP)=0 then begin
     ldap.Free;
     exit(email);
  end;
  

  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='(.+?):(.+)';

  for i:=0 to length(TMP)-1 do begin
      if RegExpr.Exec(TMP[i]) then begin
         if Uppercase(email.GeoCountry)=Uppercase(RegExpr.Match[1]) then begin
              LOGS.logs('ApplyCountryDeny:: ' + Uppercase(email.GeoCountry) + '=' + Uppercase(RegExpr.Match[1]));
              email.Artica_results:='country_deny';
              if RegExpr.Match[2]='quarantine' then begin
                 email.GoInQuarantine:=true;
                 email.KillMail:=true;
              end;
              if RegExpr.Match[2]='delete' then email.KillMail:=true;
              break;
         end;
      end;
  end;


 LOGS.logs('ApplyCountryDeny:: '+Email.Artica_results);
 RegExpr.Free;
 ldap.free;
 result:=Email;
end;

//#########################################################################################
function TscanQueue.ApplyRenattach(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   renattach_path,RenAttach_info:string;
   ldap:Tsendldap;
   mRegExpr:TRegExpr;
   LOGS:Tlogs;
   rule_name,rule_datas,TempDatas,rules_applied:string;
   TMP_FILE:TStringList;
   CounterFiles:integer;
   renattach_conf:string;
   Mime:TMimeMess;
   TmpFile:string;
begin
    result:=Email;
    renattach_path:=get_ARTICA_PHP_PATH() + '/bin/renattach';
    if Not FileExists(renattach_path) then exit;
    ldap:=Tsendldap.Create;
    TempDatas:=ldap.LoadRenAttachPolicies(recipient);
    
    if length(TempDatas)=0 then begin
        ldap.free;
        exit(Email);
    end;
    
    mRegExpr:=TRegExpr.Create;
    LOGS:=Tlogs.Create;
    mRegExpr.Expression:='<rule><rulename>(.+?)</rulename><datas>(.+?)</datas></rule>';
    TMP_FILE:=TStringList.Create;

    if mRegExpr.Exec(TempDatas) then begin
           repeat;
              rule_name:=mRegExpr.Match[1];
              rule_datas:=mRegExpr.Match[2];
              rules_applied:=rules_applied + ' ' + rule_name;
              TMP_FILE.Text:=rule_datas;
              renattach_conf:=ChangeFileExt(email.FilePath,'.ref');
              TmpFile:=ChangeFileExt(email.FilePath,'.rzf');
              
              TMP_FILE.SaveToFile(renattach_conf);
              shell('/bin/cat ' + Email.FilePath + '|'+ renattach_path + ' -c ' +renattach_conf+'>'+TmpFile);
              shell('/bin/mv ' + TmpFile + ' ' + Email.FilePath);
              shell('/bin/rm ' + renattach_conf);
              TMP_FILE.Clear;
              until Not(mRegExpr.ExecNext);
        end;
    LOGS.logs('ApplyRenattach:: rules group=' + trim(rules_applied));
    if Not FileExists(Email.FilePath) then begin
        Email.Artica_results:='attach_killed';
        exit(email);
        mRegExpr.Free;
        LOGS.Free;
        TMP_FILE.Free;
    end;
    
    

    Mime:=TMimeMess.Create;
    Mime.Lines.LoadFromFile(Email.FilePath_source);
    Mime.DecodeMessage;
    RenAttach_info:=Mime.Header.FindHeader('X-RenAttach-Info');
    
    if length(RenAttach_info)=0 then begin
       Mime.Free;
       mRegExpr.Free;
       LOGS.Free;
       TMP_FILE.Free;
       exit(email);
    end;
    
    

    mRegExpr.Expression:='mode=(.+?)\s+action=([a-zA-Z]+)\s+count=([0-9]+)';
    if mRegExpr.Exec(RenAttach_info) then begin
      CounterFiles:=StrToInt(mRegExpr.Match[3]);
      if CounterFiles>0 then begin
         LOGS.logs('ApplyRenattach "' + RenAttach_info + '"');
         Email.Artica_results:='attach_' + mRegExpr.Match[1] + '_' +  mRegExpr.Match[2];
      end;
    end;
    Mime.Free;
    mRegExpr.Free;
    LOGS.Free;
    TMP_FILE.Free;
    exit(email);
end;
//#########################################################################################
function TscanQueue.ApplyDspam(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   command_line:string;
   temp_dspam_file,temp_dspam_error:string;
   LIST,ERR:TstringList;
   i,t:Integer;
   RegExpr:TRegExpr;
   Table:TStringDynArray;
   quue_path:string;
   messageSize:integer;
   bogo_tag:string;
   bogo_pourc:integer;
   mime:TMailInfo;
begin
  email.dspam_result:='Unsure';
  email.dspam_class:='Innocent';
  email.dspam_probability:='0.0000';
  email.dspam_confidence:='1.00';
  email.dspam_signature:='N/A';
  if email.WHITE_LISTED=true then exit(email);
  result:=Email;
  bogo_tag:='';
  quue_path:=email.queue_path;

    if length(email.ldap.user_ou)=0 then begin
         LOGS.logs('bogofilter::  not an internal user');
         exit;
    end;

    if not fileExists('/usr/local/bin/bogofilter') then begin
       LOGS.logs('bogofilter:: this application is NOT INSTALLED...');
       exit;
    end;
    LOGS.logs('bogofilter:: start operations');
    LIST:=TStringList.Create;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='X-Bogosity:\s+([A-Za-z]+),\s+tests=([A-Za-z]+),\s+spamicity=([0-9\.]+)';
    temp_dspam_file:=ChangeFileExt(Email.FilePath_source,'.bogo');

    if Email.ldap.BOGOFILTER_PARAM.action='prepend' then begin
       bogo_tag:='--spam-subject-tag=' + Email.ldap.BOGOFILTER_PARAM.prepend;
    end;

    
    command_line:='/usr/local/bin/bogofilter --passthrough --ham-true --no-config-file ';
    command_line:=command_line + ' --bogofilter-dir=' + email.queue_path + '/bogofilter/users/' + email.ldap_uid;
    command_line:=command_line + ' --update-as-scored --use-syslog';
    //command_line:=command_line + ' --no-header-tags';
    //command_line:=command_line + ' --robx=0.52 --min_dev=0.375 --robs=0.0178 --spam-cutoff=0.99 --ham-cutoff=0';
    command_line:=command_line + ' --input-file=' + Email.FilePath_source;
    command_line:=command_line + ' --output-file=' + temp_dspam_file;


    shell(command_line);
    LOGS.logs(command_line);
    t:=0;
    if FileExists(temp_dspam_file) then begin
      messageSize:=GetFileSize(temp_dspam_file);
      if messageSize<email.MessageSizeInt then begin
           LOGS.logs('bogofilter WARNING: corrupted message !!!.. aborting scanning');
           DeleteFile(temp_dspam_file);
           LIST.Free;
           RegExpr.Free;
           exit(email);
      end;
    end;
    
    
       LIST.LoadFromFile(temp_dspam_file);
        for i:=0 to LIST.Count -1 do begin
           if RegExpr.Exec(LIST.Strings[i]) then begin
              email.dspam_result:=RegExpr.Match[1];
              email.dspam_probability:=RegExpr.Match[3];
              LOGS.logs('bogofilter:: result:=' + email.dspam_result + ' (' + email.dspam_probability + ') message size : ' + IntToStr(messageSize));
              bogo_pourc:=Round(StrToFloat(email.dspam_probability)*100);
              break;
           end;
        end;



       LIST.SaveToFile(email.FilePath_source);
       
       
       
       if bogo_pourc>=email.ldap.BOGOFILTER_PARAM.max_rate then begin
           LOGS.logs('bogofilter:: SPAM detected by bogofilter (' + IntToStr(bogo_pourc) + ' >=' + IntToStr(email.ldap.BOGOFILTER_PARAM.max_rate));
           email.Artica_results:='bogo';
           if email.ldap.BOGOFILTER_PARAM.action='prepend' then begin
              mime:=TMailInfo.Create;
              mime.PrependSubject(email,email.ldap.BOGOFILTER_PARAM.prepend);
              mime.Free;
           end else begin
              if email.ldap.BOGOFILTER_PARAM.action='delete'     then email.KillMail:=true;
              if email.ldap.BOGOFILTER_PARAM.action='quarantine' then email.GoInQuarantine:=True;
           end;
       end;

   DeleteFile(temp_dspam_file);
   LIST.Free;
   RegExpr.Free;
   exit(email);

end;
//#########################################################################################
function TscanQueue.DetectDsapmError(error_path:string):boolean;
var
    RegExpr:TRegExpr;
    TF:TstringList;
    i:Integer;
begin
   result:=false;
   if not FileExists(error_path) then exit(true);
   RegExpr:=TRegExpr.Create;
   TF:=TStringList.Create;
   TF.LoadFromFile(error_path);
   
   For i:=0 to TF.Count-1 do begin
       RegExpr.Expression:='invalid pointer';
       if RegExpr.Exec(TF.Strings[i]) then begin
           RegExpr.free;
           TF.Free;
           exit(true);
       end;
   
   end;
  RegExpr.free;
  TF.Free;
  exit(false);
   

end;
//#########################################################################################
function TscanQueue.ApplyKasRules(recipient:string;Email:TTMailInfo):TTMailInfo;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
    RegExpr:TRegExpr;
    Table:TStringDynArray;
    ldap:Tsendldap;
    i,t,detection_rate,action_quarantine,action_killmail,action_prepend:integer;
    second_rate,second_quarantine,second_killmail,second_prepend:integer;
    Ename,mailmD5,uid:string;
    goInQuarantine:boolean;
    Parameters:string;
    regexUser_results:string;
    USER_OU:string;
    X_SpamTest_Rate:integer;

begin
   Result:=Email;
   LOGS:=Tlogs.Create;
   goInQuarantine:=false;
   ldap:=Tsendldap.Create();
   try
      Parameters:=ldap.LoadASRules(recipient);
      uid:=ldap.uidFromMail(recipient);
      if length(uid)=0 then exit(Email);
      Email.ldap_uid:=uid;
      
      if length(email.ldap_ou)=0 then begin
         USER_OU:=ldap.OU_From_eMail(recipient);
         email.ldap_ou:=USER_OU;
      end else begin
         USER_OU:=email.ldap_ou;
      end;
      
      RegExpr:=TRegExpr.Create;
      RegExpr.Expression:='(.+?)@(.+)';
      if RegExpr.Exec(recipient) then begin
          Ename:=RegExpr.Match[1];
      end else begin
             Ename:=recipient;
      end;
   EXCEPT
     LOGS.logs('ApplyKasRules::INTERNAL ERROR !!');
     exit;
   end;

   X_SpamTest_Rate:=Email.X_SpamTest_Rate;



   RegExpr.Expression:='([a-z\_\-]+)="(.+?)"';
   if RegExpr.Exec(Parameters) then repeat
             if RegExpr.Match[1]='detection_rate' then detection_rate:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='action_quarantine' then action_quarantine:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='action_killmail' then action_killmail:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='action_prepend' then action_prepend:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='second_rate' then second_rate:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='second_quarantine' then second_quarantine:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='second_killmail' then second_killmail:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='second_prepend' then second_prepend:=StrToInt(RegExpr.Match[2]);
    until not RegExpr.ExecNext;


   if X_SpamTest_Rate>detection_rate then begin
      if X_SpamTest_Rate<second_rate then begin
        LOGS.logs('KASPERSKY ANTISPAM:: ' + USER_OU + '\'+ uid + ' detection_rate=' + IntToStr(detection_rate) + ' >' +   IntToStr(second_rate) + ' exceed maximum detection rate defined');
        if action_quarantine=1 then Email.GoInQuarantine:=True;
        if action_killmail=1 then Email.KillMail:=true;
        Email.Artica_results:='spam';
        exit(Email);
      end;
   end;

   if X_SpamTest_Rate>second_rate then begin
        LOGS.logs('KASPERSKY ANTISPAM::' + USER_OU + '\'+ uid + ' detection_rate=' + IntToStr(detection_rate) + '>' +   IntToStr(second_rate) + ' exceed maximum second detection rate defined');
        if second_quarantine=1 then Email.GoInQuarantine:=True;
        if second_killmail=1 then Email.KillMail:=true;
        Email.Artica_results:='spam';
        exit(Email);
   end;
Email.Artica_results:='send';
exit(Email);
   
end;
//#########################################################################################
function TscanQueue.RetreiveSenderRecipients(Email:TTMailinfo):TTMailInfo;
var
TRANSPORT_TABLE:TStringList;
HeadFilePath:string;
RegExpr:TRegExpr;
i:integer;
begin
     LOGS.logs('RetreiveSenderRecipients:: -> ' + Email.FilePath);
      TRANSPORT_TABLE:=TStringList.Create;
      HeadFilePath:=ChangeFileExt(Email.FilePath,'.head');
      if not FileExists(HeadFilePath) then begin
        LOGS.logs('RetreiveSenderRecipients:: unable to stat ' + HeadFilePath);
      exit;
      end;

      TRANSPORT_TABLE.LoadFromFile(HeadFilePath);
      LOGS.logs('RetreiveSenderRecipients::"' + HeadFilePath + '" TRANSPORT_TABLE=' + IntToStr(TRANSPORT_TABLE.Count) + '"' + TRANSPORT_TABLE.Text + '"');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='MAIL_FROM=(.+)';
    for i:=0 to TRANSPORT_TABLE.Count -1 do begin
     if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
          LOGS.logs('RetreiveSenderRecipients:: -> ' + RegExpr.Match[1]);
          Email.real_mailfrom:=RegExpr.Match[1];
          break;
          end;

    end;
    RegExpr.Expression:='MAIL_TO=(.+)';
    for i:=0 to TRANSPORT_TABLE.Count -1 do begin
     if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
          LOGS.logs('RetreiveSenderRecipients:: -> ' + RegExpr.Match[1]);
          Email.mail_to:=Email.mail_to+RegExpr.Match[1] + ',';
          break;
          end;

    end;
    RegExpr.Free;
    TRANSPORT_TABLE.Free;

end;
//#########################################################################################
function TscanQueue.WhiteList(Email:TTMailInfo;recipient:string):boolean;
var
   ldap:Tsendldap;
   trouve:boolean;
   i:integer;
   mail_from,blocked:string;
   RegExpr:TRegExpr;
begin

     trouve:=false;
     mail_from:=LowerCase(Email.real_mailfrom);
     if email.ldap.WhiteList.Count=0 then exit;


     result:=false;
     RegExpr:=TRegExpr.Create;
     D:=COMMANDLINE_PARAMETERS('debug');



  for i:=0 to email.ldap.WhiteList.Count-1 do begin
        blocked:=LowerCase(email.ldap.WhiteList.Strings[i]);


        if blocked=mail_from then begin
           RegExpr.Free;
           exit(true);
        end;
           
        if Pos('*',blocked)>0 then begin
           blocked:=AnsiReplaceText(blocked,'*','.+');
           if D then writeln('IsWhiteListed:: RegExpr="' + Blocked + '"');
           RegExpr.Expression:=blocked;
           if RegExpr.Exec(mail_from) then begin
              RegExpr.Free;
              exit(true);
           end;
        end;
  end;

    exit(false);
end;
//#########################################################################################
function TscanQueue.DirFiles(FilePath: string;pattern:string):TstringList;
Var Info : TSearchRec;
    Count : Longint;
    D:boolean;
Begin

  Count:=0;
  If FindFirst (FilePath+'/'+ pattern,faAnyFile,Info)=0 then begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin
           DirListFiles.Add(FilePath + '/' + Info.Name);

         end;
      end;

    Until FindNext(info)<>0;
    end;
  FindClose(Info);
  exit();
end;
//#########################################################################################
function TscanQueue.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
var
  SepLen       : Integer;
  F, P         : PChar;
  ALen, Index  : Integer;
begin
  SetLength(Result, 0);
  if (S = '') or (Limit < 0) then
    Exit;
  if Separator = '' then
  begin
    SetLength(Result, 1);
    Result[0] := S;
    Exit;
  end;
  SepLen := Length(Separator);
  ALen := Limit;
  SetLength(Result, ALen);

  Index := 0;
  P := PChar(S);
  while P^ <> #0 do
  begin
    F := P;
    P := StrPos(P, PChar(Separator));
    if (P = nil) or ((Limit > 0) and (Index = Limit - 1)) then
      P := StrEnd(F);
    if Index >= ALen then
    begin
      Inc(ALen, 5); // mehrere auf einmal um schneller arbeiten zu können
      SetLength(Result, ALen);
    end;
    SetString(Result[Index], F, P - F);
    Inc(Index);
    if P^ <> #0 then
      Inc(P, SepLen);
  end;
  if Index < ALen then
    SetLength(Result, Index); // wirkliche Länge festlegen
end;
//####################################################################################
function TscanQueue.ScanAVMail(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   AVRESULTS:TStringList;
   MESSAGE_DATAS:TStringList;
   mail_temp_file,Parameters,NotifyFromAddress,NotifyMessageSubject,NotifyMessageTemplate,uid,line,mailMD5:string;
   RegExpr:TRegExpr;
   ldap:Tsendldap;
   ArchiveMail,DeleteDetectedVirus,NotifyFrom,NotifyTo:integer;
   VIRUS_DETECTED:boolean;
   AProcess: TProcess;
   commandline:string;
begin
  Email.Artica_results:='send';
  Result:=Email;
  if not FileExists('/opt/kav/5.5/kav4mailservers/bin/aveclient') then exit();
  if not FileExists(Email.FilePath) then exit();

  VIRUS_DETECTED:=false;
  AVRESULTS:=TStringList.Create;
  MESSAGE_DATAS:=TStringList.Create;
  RegExpr:=TRegExpr.Create;
  ldap:=Tsendldap.Create();


  Parameters:=ldap.LoadAVRules(recipient);
  uid:=ldap.uidFromMail(recipient);
  MESSAGE_DATAS.LoadFromFile(Email.FilePath);
  

  ldap.Free;

  mailMD5:=MD5FromString(MESSAGE_DATAS.Text);
  
  mail_temp_file:=ChangeFileExt(Email.FilePath,'.tmp');
  

   RegExpr.Expression:='([a-zA-Z\_\-]+)="(.+?)"';
   if RegExpr.Exec(Parameters) then repeat
             if RegExpr.Match[1]='ArchiveMail' then ArchiveMail:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='DeleteDetectedVirus' then DeleteDetectedVirus:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='NotifyFrom' then NotifyFrom:=StrToInt(RegExpr.Match[2]);
             if RegExpr.Match[1]='NotifyMessageSubject' then NotifyMessageSubject:=RegExpr.Match[2];
             if RegExpr.Match[1]='NotifyFromAddress' then NotifyFromAddress:=RegExpr.Match[2];
             if RegExpr.Match[1]='NotifyTo' then NotifyTo:=StrToInt(RegExpr.Match[2]);

    until not RegExpr.ExecNext;

    RegExpr.Expression:='<NotifyMessageTemplate>(.+?)</NotifyMessageTemplate>';
    if RegExpr.Exec(Parameters) then NotifyMessageTemplate:=RegExpr.Match[1];

  MESSAGE_DATAS.SaveToFile(mail_temp_file);
  
  commandline:='/opt/kav/5.5/kav4mailservers/bin/aveclient -p /var/run/aveserver -s ' +mail_temp_file+' >'+mail_temp_file+'.scan';
  Shell(commandline);
  AVRESULTS.LoadFromFile(mail_temp_file+'.scan');
  RegExpr.Expression:='INFECTED';
  DeleteFile(mail_temp_file);
  DeleteFile(mail_temp_file+'.scan');


  if length(AVRESULTS.Text)>0 then begin
          line:=AVRESULTS.Strings[0];
          if RegExpr.Exec(line) then begin
             VIRUS_DETECTED:=true;
             eMail.viruses_list:=AVRESULTS.Strings[1];
          end;
  end;

  AVRESULTS.Free;
  RegExpr.Free;
  if VIRUS_DETECTED=false then begin
       LOGS.logs('KASPERSKY AV::CLEAN');
       eMail.Artica_results:='send';
       exit(eMail);
  end;
  email.Artica_results:='infected';
  LOGS.logs('KASPERSKY AV::INFECTED:: ArchiveMail=' + IntToStr(ArchiveMail) + ' NotifyTo=' + IntToStr(NotifyTo) + ' DeleteDetectedVirus=' + IntToStr(NotifyTo));
  if ArchiveMail=1 then begin
     eMail.GoInVirusQuarantine:=True;
  end;



  if NotifyFrom=1 then begin
       SendSMTPNotifications(NotifyFromAddress,NotifyMessageSubject,eMail.real_mailfrom,NotifyMessageTemplate,eMail);
  end;

  if NotifyTo=1 then begin
      SendSMTPNotifications(NotifyFromAddress,NotifyMessageSubject,recipient,NotifyMessageTemplate,eMail);
  end;

  if DeleteDetectedVirus=1 then email.KillMail:=true;

  result:=email;


end;
//##############################################################################
function TscanQueue.MD5FromString(values:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5String(values);
exit(MD5Print(Digest));
end;
//####################################################################################
function TscanQueue.SendSMTPNotifications(NotificationMailFrom:string;subject:string;mail_to:string;HtmlBody:string;Email:TTMailInfo):boolean;
 var
 SMTP: TSMTPSend;
 MIME:TMimeMess;
 HTML_DATAS:TstringList;
 MAIL_DATAS:TstringList;
 MultiPartMix, MultiPartRel, MultiPartAlt: TMimePart;
 label MyFree;
 begin
     result:=false;
     HTML_DATAS:=TstringList.Create;
     MAIL_DATAS:=TstringList.Create;
     SMTP:=TSMTPSend.Create;
     MIME:=TMimeMess.Create;
     logs:=Tlogs.Create;
     LOGS.logs('SendSMTPNotifications:: Original message FROM <' + Email.real_mailfrom + '> "' + email.Subject + '" MAIL TO <' + trim(email.mail_to) + '>' );

     email.viruses_list:=AnsiReplaceText(email.viruses_list,'LINFECTED','');
     subject:=AnsiReplaceText(subject,'%SUBJECT%',Email.Subject);
     subject:=AnsiReplaceText(subject,'%SENDER%', Email.real_mailfrom);
     subject:=AnsiReplaceText(subject,'%MAILTO%',email.mail_to);
     subject:=AnsiReplaceText(subject,'%VIRUS%',email.viruses_list);



     HtmlBody:=AnsiReplaceText(HtmlBody,'%SUBJECT%',Email.Subject);
     HtmlBody:=AnsiReplaceText(HtmlBody,'%SENDER%', Email.real_mailfrom);
     HtmlBody:=AnsiReplaceText(HtmlBody,'%MAILTO%',email.mail_to);
     HtmlBody:=AnsiReplaceText(HtmlBody,'%VIRUS%',email.viruses_list);

     HTML_DATAS.Add(HtmlBody);


     MAIL_DATAS.Add('From: <' +NotificationMailFrom +  '>');
     MAIL_DATAS.Add('Date: ' + Rfc822DateTime(now));
     MAIL_DATAS.Add('To: <' + mail_to + '>');
     MAIL_DATAS.Add('Subject: ' + subject);
     MAIL_DATAS.Add('X-Mailer: artica-filter');


     MultiPartMix := MIME.AddPartMultipart('mixed', nil);
     MultiPartRel := MIME.AddPartMultipart('related', MultiPartMix);
     MultiPartAlt := MIME.AddPartMultipart('alternative', MultiPartRel);
     MIME.AddPartHTML(HTML_DATAS, MultiPartAlt);
     MIME.EncodeMessage;
     MAIL_DATAS.AddStrings(MIME.Lines);

     SMTP.TargetHost:='127.0.0.1';
     SMTP.TargetPort:='29300';
     if not SMTP.Login then goto MyFree;

     LOGS.logs('SendSMTPNotifications:: Success connecting to ' + SMTP.TargetHost + ' on port ' + SMTP.TargetPort);
     if not SMTP.MailFrom(NotificationMailFrom, Length(MAIL_DATAS.Text)) then goto MyFree;
     if not SMTP.MailTo(trim(mail_to)) then goto MyFree;
     if not SMTP.MailData(MAIL_DATAS) then goto Myfree;

     SMTP.Logout;
     LOGS.logs('SendSMTPNotifications:: SUCCESS sending notification to "'+mail_to + '" from "'+NotificationMailFrom+'"');

           SMTP.Free;
           HTML_DATAS.FRee;
           MIME.free;
           MAIL_DATAS.Free;
    exit(true);



   myfree:
   LOGS.logs('SendSMTPNotifications:: ERROR SENDING MAIL "' +SMTP.FullResult.Text + '"');
   SMTP.Free;
   HTML_DATAS.FRee;
   MIME.free;
   MAIL_DATAS.Free;
   exit(false);



 end;
//##############################################################################
 function TscanQueue.ScanUserRegex(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   i:integer;
   RegExpr:TRegExpr;
   ldap:Tsendldap;
   pattern:string;
   regex:string;
   action:string;
begin
  RegExpr:=TRegExpr.Create;
  ldap:=Tsendldap.Create();
  email.Artica_results:='send';
  if email.WHITE_LISTED=true then exit(email);
  ldap.LoadArticaUserRules(recipient);
  if ldap.TEMP_LIST.Count=0 then begin
    LOGS.logs('ScanUserRegex:: no user rules...');
    ldap.Free;
    RegExpr.free;
    exit();
  end;
  LOGS.logs('ScanUserRegex:: ' + IntToStr(ldap.TEMP_LIST.Count) + ' rules');
  for i:=0 to ldap.TEMP_LIST.Count-1 do begin
      RegExpr.Expression:='<header>(.+?)</header><pattern>(.+?)</pattern><regex>(.+?)</regex><action>(.+?)</action>';
      if RegExpr.Exec(ldap.TEMP_LIST.Strings[i]) then begin
              if ScanHeaderRegex(RegExpr.Match[1],RegExpr.Match[2],StrToInt(RegExpr.Match[3]),Email)=true then begin
                  action:=trim(RegExpr.Match[4]);
                  LOGS.logs('MAIL MATCH USER POLICY RULE: "' + action + '"');

                   if action='quarantine' then begin
                     LOGS.logs('mail will be filtered and quarantine');
                     Email.Artica_results:='filtered';
                     email.GoInQuarantine:=true;
                     exit(email);
                  end;
                  
                  if action='pass' then begin
                     LOGS.logs('mail will be white listed');
                     Email.Artica_results:='white';
                     ldap.Free;
                     RegExpr.free;
                     exit(email);
                  end;


                  if action='delete' then begin
                     Email.Artica_results:='filtered';
                     LOGS.logs('mail will be white filtered and deleted');
                     Email.KillMail:=true;
                     ldap.Free;
                     RegExpr.free;
                     exit(email);
                  end;
                  
                  if action='quarantine' then begin
                     Email.Artica_results:='filtered';
                     LOGS.logs('mail will be white filtered and quarantine');
                     Email.GoInQuarantine:=true;
                     ldap.Free;
                     RegExpr.free;
                     exit(email);
                  end;
                  
                  

                     

                  exit(email);
              end;
      end;
  end;

                  ldap.Free;
                  RegExpr.free;
                  exit(email);
end;

//##############################################################################

function TscanQueue.ScanRBL(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   i,t:integer;
   Action:string;
   RegExpr:TRegExpr;
   PC:Integer;
   PCTOT:Integer;
   LOGS:Tlogs;
begin

   if length(email.ldap_ou)=0 then exit(email);
   if email.WHITE_LISTED=true then exit(email);
   RegExpr:=TRegExpr.create;
   LOGS:=Tlogs.create;
   PC:=0; PCTOT:=0;
   Action:=email.ldap.RBL_SERVER_ACTION;
   RegExpr.Expression:='(.+?):([0-9]+)';
   LOGS.logs('ScanRBL::' + Action + '->' + IntToStr(email.ldap.RBL_SERVERS.Count) + ' RBL servers' + ' ' + IntToStr(length(Email.RELAIS)) + ' IP found');
   if email.ldap.RBL_SERVERS.Count=0 then begin
      exit;
      RegExpr.Free;
   end;
   
   
   for i:=0 to email.ldap.RBL_SERVERS.Count-1 do begin
        if RegExpr.Exec(email.ldap.RBL_SERVERS.Strings[i]) then begin
                LOGS.logs('ScanRBL::' + RegExpr.Match[1] + ' {' + RegExpr.Match[2] + '}+' +IntToStr(PCTOT));
                PCTOT:=PCTOT+ StrToInt(RegExpr.Match[2]);
                for t:=0 to length(Email.RELAIS)-1 do begin
                    if rblinfo(RegExpr.Match[1],Email.RELAIS[t]) then begin
                         PC:=PC+StrToint(RegExpr.Match[2]);
                         LOGS.logs('ScanRBL::' + RegExpr.Match[1] + '->' + Email.RELAIS[t] + '=BLOCK');

                    end else begin
                         LOGS.logs('ScanRBL::' + RegExpr.Match[1] + '->' + Email.RELAIS[t] + '=SAFE');
                    end;
                
                end;
        
        end;
   
   end;
LOGS.logs('ScanRBL:: Result=' + IntToStr(PC) + ' Must reach 100');
if PC>=100 then begin
     email.Artica_results:='RBL';
     if Action='quarantine' then email.GoInQuarantine:=True;
     if action='delete' then email.KillMail:=true;
     if action='pass' then email.Artica_results:='send';
end;

   RegExpr.Free;
   exit(email);

end;
//##############################################################################
function TscanQueue.ScanSURBL(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   LIST:TStringDynArray;
   i,t:integer;
   Action:string;
   ldap:Tsendldap;
   RegExpr:TRegExpr;
   PC:Integer;
   PCTOT:Integer;
   LOGS:Tlogs;
begin
   ldap:=Tsendldap.Create();
   RegExpr:=TRegExpr.create;
   LOGS:=Tlogs.create;

  if length(email.ldap_ou)=0 then begin
     if D then LOGS.logs('ScanSURBL:: get ou from ' + recipient);
        email.ldap_ou:=trim(ldap.OU_From_eMail(trim(recipient)));
  end;
  
  if email.WHITE_LISTED=true then exit(email);
  
    PC:=0; PCTOT:=0;
   Action:=ldap.LoadOUSURBLAction(email.ldap_ou);
   LIST:=ldap.LoadOUSURBL(email.ldap_ou);
   RegExpr.Expression:='(.+?):([0-9]+)';
   LOGS.logs('ScanSURBL::' + Action + '->' + IntToStr(length(LIST)) + ' SURBL servers' + ' ' + IntToStr(length(Email.uribl)) + ' URI server found');
   if length(LIST)=0 then begin
      exit;
      ldap.free;
      RegExpr.Free;
   end;


   for i:=0 to length(LIST)-1 do begin
        if RegExpr.Exec(LIST[i]) then begin
                LOGS.logs('ScanSURBL::' + RegExpr.Match[1] + ' {' + RegExpr.Match[2] + '}+' +IntToStr(PCTOT));
                PCTOT:=PCTOT+ StrToInt(RegExpr.Match[2]);
                for t:=0 to length(Email.uribl)-1 do begin
                    if rblinfo(RegExpr.Match[1],Email.uribl[t]) then begin
                         PC:=PC+StrToint(RegExpr.Match[2]);
                         LOGS.logs('ScanSURBL::' + RegExpr.Match[1] + '->' + Email.uribl[t] + '=BLOCK');

                    end else begin
                         LOGS.logs('ScanSURBL::' + RegExpr.Match[1] + '->' + Email.uribl[t] + '=SAFE');
                    end;

                end;

        end;

   end;
LOGS.logs('ScanSURBL:: Result=' + IntToStr(PC) + ' Must reach 100');
if PC>=100 then begin
     email.Artica_results:='SURBL';
     if Action='quarantine' then email.GoInQuarantine:=True;
     if action='delete' then email.KillMail:=true;
     if action='pass' then email.Artica_results:='send';
end;
   ldap.free;
   RegExpr.Free;
   exit(email);
  
  
   
end;
//##############################################################################
function TscanQueue.DNSMASQ_PID():string;
var
   RegExpr:TRegExpr;
   filedatas:TStringList;
   i:Integer;
begin
     if not FileExists('/var/run/dnsmasq.pid') then exit();
     filedatas:=TStringList.Create;
     filedatas.LoadFromFile('/var/run/dnsmasq.pid');
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='([0-9]+)';
     For i:=0 to filedatas.Count-1 do begin
         if RegExpr.Exec(filedatas.Strings[i]) then begin
               result:=RegExpr.Match[1];
               break;
         end;

     end;

    RegExpr.Free;
    filedatas.Free;

end;
//##############################################################################
function TscanQueue.DSNMASQ_RUN():boolean;
  var pid:string;
begin
  result:=false;
   if FileExists('/usr/sbin/dnsmasq') then result:=true;
   if FileExists('/usr/local/sbin/dnsmasq') then result:=true;
end;
//##############################################################################

function TscanQueue.surblinfo(rbl_name_server:string;ip_to_test:string):boolean;
var
   l:TstringList;
   IPCut:TStringDynArray;
   LOGS:Tlogs;
   s,nameserver:string;
   RegExpr:TRegExpr;
   I,NOTE:Integer;
   MyResults:string;
   newip:string;
   Lres:TstringList;
   D:boolean;
   Cache_result:integer;
begin
  result:=false;
  LOGS:=Tlogs.create;
  if not D then if ParamStr(1)='-surbl' then D:=True;
  if not USE_DNSMASQ then begin
     Cache_result:=RBL_CACHE(rbl_name_server,ip_to_test,'uribl');
     if Cache_result>0 then begin
        exit(true);
        LOGS.Free;
     end;
  end;
  L:=TstringList.create;
  RegExpr:=TRegExpr.Create;

  
  try
    s := GetDNS;
    l.commatext := s;
    if l.count > 0 then begin
      s := l[0];
      nameserver:=s;
    end;
    finally
    end;
    
    if USE_DNSMASQ then nameserver:='127.0.0.1';
    

    newip:= ip_to_test + '.' + rbl_name_server;
    if D then writeln('nameserver (' + nameserver + ') --> ' + newip);
    
    Lres:=TstringList.Create;
    RegExpr.Expression:='[0-9]+\.[0-9]+\.[0-9]+\.([0-9]+)';
    NOTE:=0;
    
    if ResolvServers(nameserver,newip,Lres) then begin
        for i:= 0 to Lres.Count -1 do begin
            myResults:=Lres.Strings[i];
            if D then writeln('Result[' + IntToStr(i) + ']=',myResults);
            if RegExpr.Exec(myResults) then begin
                if D then writeln('NOTE=',RegExpr.Match[1]);
                NOTE:=StrToInt(RegExpr.Match[1]);
                if NOTE>0 then begin
                     if D then writeln('Balcklisted=',RegExpr.Match[1]);
                     result:=true;
                end;
            
            end;

        end;
    
    end;
    
    if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,IntToStr(NOTE),'surbl');
  
end;


//##############################################################################
function TscanQueue.rblinfo(rbl_name_server:string;ip_to_test:string):boolean;
var
   l:TstringList;
   IPCut:TStringDynArray;
   LOGS:Tlogs;
   s,nameserver:string;
   RegExpr:TRegExpr;
   I,LDI:Integer;
   ldap:Tsendldap;
   newip:string;
   Lres:TstringList;
   D:boolean;
   Cache_result:integer;
begin
  if USE_DNSMASQ then
  result:=false;
  newip:='';
  LOGS:=Tlogs.create;
  RegExpr:=TRegExpr.Create;
  if not USE_DNSMASQ then begin
         Cache_result:=RBL_CACHE(rbl_name_server,ip_to_test,'rbl');
         if Cache_result>0 then begin
               if Cache_result=1 then begin
                  LOGS.Free;
                  exit(false);
               end;
        
               if Cache_result=2 then begin
                  LOGS.logs('rblinfo:: DNSMX:: request from="' +ip_to_test + '" =>' + rbl_name_server + ' You are a verified open relay');
                  LOGS.Free;
                  exit(true);
               end;

               if Cache_result=3 then begin
                  LOGS.logs('rblinfo:: DNSMX:: request from="' +ip_to_test + '" =>' + rbl_name_server + '  Direct mail from dialups not allowed');
                  LOGS.Free;
                  exit(true);
               end;
        
               if Cache_result=4 then begin
                  LOGS.logs('rblinfo:: DNSMX:: request from="' +ip_to_test + '" =>' + rbl_name_server + '  You are a confirmed spam source');
                  LOGS.Free;
                  exit(true);
               end;
  
          end;
  end;


  D:=LOGS.COMMANDLINE_PARAMETERS('-V');
  l := TStringList.create;
  IPCut:=Explode('.',ip_to_test);
  
  for i:=length(IPCut)-1 downto 0 do begin
          if i>0 then newip:=newip + IPCut[i]+'.' else newip:=newip + IPCut[i];
  end;
  
  newip:=newip +'.' + rbl_name_server;
  if D then writeln('NEWIP: ',newip);

  Lres:=TstringList.Create;

  try
    s := GetDNS;
    l.commatext := s;
    if l.count > 0 then
    begin
      s := l[0];
      nameserver:=s;
    end;
    finally
    end;
    if D then writeln('Resolver ' + newip);
    if USE_DNSMASQ then nameserver:='127.0.0.1';
    
    if ResolvServers(nameserver,newip,Lres) then begin
       RegExpr.Expression:='[0-9]+\.[0-9]+\.[0-9]+\.([0-9]+)';
    
    
    for i:=0 to Lres.Count -1 do begin
        if trim(Lres.Strings[i])='127.0.0.2' then begin
           LOGS.logs('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' You are a verified open relay');
           if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'2','rbl');
           result:=True;
        end;
        
        if trim(Lres.Strings[i])='127.0.0.3' then begin
           LOGS.logs('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' Direct mail from dialups not allowed');
           if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'3','rbl');
           result:=True;
        end;
        
        if trim(Lres.Strings[i])='127.0.0.4' then begin
           LOGS.logs('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' You are a confirmed spam source');
           if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'4','rbl');
           result:=True;
        end;
        
        if result=false then begin
              if RegExpr.Exec(Lres.Strings[i]) then begin
                   LDI:=StrToint(RegExpr.Match[1]);
                   if LDI>4 then begin
                       LOGS.logs('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' Note: ' + RegExpr.Match[1]);
                       if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'4','rbl');
                       result:=True;
                   end;
              end;
        
        end;
        
        
    end;
   end;
   
   if result=false then begin
      if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'1','rbl');
   end;
   
   RegExpr.Free;
   Lres.free;
   l.free;
   LOGS.free;

end;

//##############################################################################
function TscanQueue.RBL_CACHE(rbl_name_server:string;ip_to_test:string;TableName:string):integer;
var
   db:TSqlite3Dataset;
   sql:string;
   res:string;
   suffix:string;
begin
if not FileExists('/usr/share/artica-postfix/LocalDatabases/rbl_database.db') then begin
   LOGS.logs('RBL_CACHE:: Unable to stat rbl_database.db RBL/URIBL cache is disabled');
   exit;
end;
  if TableName='uribl' then suffix:='uribl_';
   db:=TSqlite3Dataset.Create(nil);
   db.FileName:='/usr/share/artica-postfix/LocalDatabases/rbl_database.db';
   sql:='SELECT ' + suffix + 'result FROM ' + TableName + ' WHERE ' + suffix + 'service="' + rbl_name_server + '" AND ' + suffix + 'mx="' + ip_to_test + '" AND strftime("%Y-%m-%d",' + suffix + 'zDate)=strftime("%Y-%m-%d","now") LIMIT 0,1';
   
   db.SQL:=sql;
   db.Open;
   db.First;
   res:=db.FieldByName(suffix+'result').AsString;
   db.Close;
   if Length(res)=0 then res:='0';
   result:=StrToInt(res);
end;
//##############################################################################
procedure TscanQueue.EDIT_RBL_CACHE(rbl_name_server:string;ip_to_test:string;res:string;TableName:string);
   var
   db:TSqlite3Dataset;
   sql:string;
   suffix:string;
begin
   db:=TSqlite3Dataset.Create(nil);
   db.FileName:='/usr/share/artica-postfix/LocalDatabases/rbl_database.db';
   if TableName='uribl' then suffix:='uribl_';
   
   
   sql:='INSERT INTO ' + TableName + '(' + suffix + 'service,' + suffix + 'mx,' + suffix + 'result) VALUES("' +rbl_name_server+'","' + ip_to_test+'","' + res +'")';
   db.QuickQuery(sql);
   LOGS.logs('EDIT_RBL_CACHE:: ' +db.ReturnString);
   LOGS.logs(sql + ' => ' + db.ReturnString);
   db.Close;
   db.Free;
end;

//##############################################################################
function TscanQueue.ResolvServers(const DNSHost, Domain: AnsiString; const Servers: TStrings): Boolean;
var
  DNS: TDNSSend;
  t: TStringList;
  n, m, x: Integer;
  D:boolean;
  LOGS:Tlogs;
begin
  Result := False;
  Servers.Clear;
  t := TStringList.Create;
  DNS := TDNSSend.Create;
  Logs:=TLogs.create;
  D:=LOGS.COMMANDLINE_PARAMETERS('-V');
  try
    DNS.TargetHost := DNSHost;
    if DNS.DNSQuery(Domain, Qtype_A, t) then begin
      if D then writeln(t.text);
      for n := 0 to t.Count - 1 do begin
        x := Pos(',', t[n]);
        if x > 0 then
          for m := 1 to 6 - x do
            t[n] := '0' + t[n];
      end;
      { sort server list }
      t.Sorted := True;
      { result is sorted list without preference numbers }
      for n := 0 to t.Count - 1 do
      begin
        x := Pos(',', t[n]);
        Servers.Add(Copy(t[n], x + 1, Length(t[n]) - x));
      end;
      Result := True;
    end else begin
         if D then writeln('Failed resolve...');
    end;
  finally
    DNS.Free;
    t.Free;
  end;
end;
//##############################################################################
function TscanQueue.ScanHeaderRegex(header:string;pattern:string;regex:integer;Email:TTMailInfo):boolean;
var
   i:integer;
   header_lcase:string;
   Header_values:TStringList;
   Found:boolean;
   RegExpr:TRegExpr;
   Mime:TMimeMess;
begin
   Found:=false;
   Header_values:=TStringList.Create;
   RegExpr:=TRegExpr.Create;
   result:=false;
   header_lcase:=AnsiLowerCase(header);

    Mime:=TMimeMess.Create;
    Mime.Lines.LoadFromFile(email.FilePath);
    Mime.DecodeMessage;

   if header_lcase='subject' then begin
      Header_values.Add(Mime.Header.Subject);
      Found:=true
   end;

   if header_lcase='from' then begin
      Header_values.Add(Mime.Header.From);
      Found:=true
   end;

   if header_lcase='to' then begin
      Header_values.AddStrings(Mime.Header.ToList);
      Found:=true
   end;

   if header_lcase='cc' then begin
      Header_values.AddStrings(Mime.Header.CCList);
      Found:=true
   end;
   if found=false then Mime.Header.FindHeaderList(header,Header_values);

   if regex=0 then begin
        pattern:=AnsiReplaceText(pattern,'.','\.');
        pattern:=AnsiReplaceText(pattern,'*','.+');
        pattern:=AnsiReplaceText(pattern,'[','\[');
        pattern:=AnsiReplaceText(pattern,']','\]');
        pattern:=AnsiReplaceText(pattern,'(','\(');
        pattern:=AnsiReplaceText(pattern,')','\)');
        pattern:=AnsiReplaceText(pattern,'$','\$');
        pattern:=AnsiReplaceText(pattern,'{','\{');
        pattern:=AnsiReplaceText(pattern,'}','\}');
        pattern:=AnsiReplaceText(pattern,'|','\|');
        pattern:=AnsiReplaceText(pattern,'!','\!');
        pattern:=AnsiReplaceText(pattern,'?','\?');
   end;

   //LOGS.logs('ScanHeaderRegex:: scan header ' + header + ' pattern:"' + pattern + '" in '+ IntToStr(Header_values.Count) + ' line(s)');
   if Header_values.Count=0 then begin
      Header_values.Free;
      RegExpr.free;
      exit;
   end;


   RegExpr.Expression:=pattern;
   RegExpr.ModifierI:=true;
   LOGS.logs('ScanHeaderRegex:: "' + pattern + '" in "' + header_lcase + '" for ' + IntToStr(Header_values.Count) + ' Lines (to ' + Email.ldap_uid + ')');
   for i:=0 to Header_values.Count-1 do begin
       if RegExpr.Exec(Header_values.Strings[i]) then begin
              LOGS.logs('ScanHeaderRegex:: Found in "' + header + '" "' + Header_values.Strings[i] + '" string');
              Header_values.Free;
              RegExpr.free;
              exit(true);

       end;

   end;



   Header_values.Free;
   RegExpr.free;
   exit(false);
end;
//##############################################################################
procedure TscanQueue.BigHtmlMail(Email:TTMailInfo);
   var
      i           :integer;
      RegExpr     :TRegExpr;
      rules       :string;
      MessageSize :Integer;
begin
   if not FileExists(ExtractFilePath(ParamStr(0)) + 'mhonarc') then begin
          LOGS.logs('BigHtmlMail:: ' + ExtractFilePath(ParamStr(0)) + 'mhonarc doesn''t exists');
          exit;
   end;

   if Email.ldap.html_block.enabled<>'yes' then exit;
   RegExpr:=TRegExpr.Create;
   MessageSize:=StrToInt(Email.MessageSize);
   if MessageSize=0 then MessageSize:=logs.GetFileSizeMo(email.FilePath_source);
   LOGS.logs('BigHtmlMail:: html block is enabled');
   for i:=0 to Email.ldap.html_block.Rules.Count-1 do begin
     rules:=Email.ldap.html_block.Rules.Strings[i];
     rules:=AnsiReplaceText(rules,'*','.+');
     RegExpr.Expression:='(.+?);(.+?);([0-9]+)';
     if RegExpr.Exec(rules) then begin
        LOGS.logs('BigHtmlMail:: rule number ' + intTostr(i) + ' From:' + RegExpr.Match[1] + ' To:' + RegExpr.Match[2] + ' Size:' + RegExpr.Match[3] + ' mb');
           if MessageSize>=StrToInt(RegExpr.Match[3]) then begin
              RegExpr.Expression:=RegExpr.Match[1];
              if RegExpr.Exec(Email.mail_from) then begin
                    RegExpr.Expression:=RegExpr.Match[2];
                    if RegExpr.Exec(Email.mail_to) then begin
                        LOGS.logs('BigHtmlMail:: rule number ' + intTostr(i) + ' match');
                        BigHtmlMail_Backup(email);
                    end;
              end;
           end else begin LOGS.logs('BigHtmlMail:: rule number ' + intTostr(i) + ' doesn''t match size "' + IntToStr(MessageSize) + '"');end;
     end;

   end;
   

end;
//##############################################################################
procedure TscanQueue.BigHtmlMail_Backup(Email:TTMailInfo);
const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
  var
     cmd              :string;
     RegExpr          :TRegExpr;
     Configuration    :TStringList;
     i                :integer;
     path             :string;
     destination_path :string;
     destination_file :string;
     prependsubject   :string;
     hostname         :string;
     tmpFile          :TstringList;
     use_php          :boolean;
     MIME             :TMimeMess;
     HTML_DATAS       :TstringList;
     MAIL_DATAS       :TstringList;
     MultiPartMix, MultiPartRel, MultiPartAlt: TMimePart;
     subject          :string;
     MaxDay           :string;
begin
  cmd:=ExtractFilePath(ParamStr(0)) + 'mhonarc';
  RegExpr:=TRegExpr.Create;
  use_php:=false;
  subject:='';
Configuration:=TStringList.Create;
Configuration.Add(email.ldap.html_block.config);
Configuration.SaveToFile('/tmp/' + email.MessageMD5 + '.bightml.cf');
Configuration.LoadFromFile('/tmp/' + email.MessageMD5 + '.bightml.cf');
DeleteFile('/tmp/' + email.MessageMD5 + '.bightml.cf');

for i:=0 to Configuration.Count-1 do begin
      RegExpr.expression:='prependsubject[\s=]+(.+)';
      if RegExpr.Exec(Configuration.Strings[i]) then  prependsubject:=RegExpr.Match[1];
      RegExpr.expression:='hostname[\s=]+(.+)';
      if RegExpr.Exec(Configuration.Strings[i]) then  hostname:=RegExpr.Match[1];
      RegExpr.expression:='maxday[\s=]+(.+)';
      if RegExpr.Exec(Configuration.Strings[i]) then  MaxDay:=RegExpr.Match[1];
end;
Configuration.free;
destination_path:=email.queue_path + '/bightml/' +email.ldap_ou + '/'+ email.MessageMD5;
destination_file:=destination_path + '/' + email.MessageMD5 + '.html';




if not FileExists(destination_file) then begin
  Forcedirectories(destination_path);
  if not FileExists(email.FilePath_source) then begin
         LOGS.logs('BigHtmlMail:: ' + email.FilePath_source + ' does not exists');
         exit;
  end;

  cmd:='cd ' + destination_path + ' && ' +cmd+ ' -single < '+ email.FilePath_source +' > ' + destination_file ;
  LOGS.logs(cmd);
  fpsystem(cmd);
  if not FileExists(destination_file) then begin
     LOGS.logs('BigHtmlMail:: Unable to stat ' +destination_file);
     exit;
  end;
end;
RegExpr.expression:='download\.attach\.php';
if RegExpr.Exec(hostname) then begin use_php:=true end else begin use_php:=false end;

//+ '?path='+email.MessageMD5 + '&org='+email.ldap_ou end else URI:=hostname + '/' + email.ldap_ou + '/'+ email.MessageMD5 + '

 tmpFile:=TstringList.Create;
 tmpFile.LoadFromFile(destination_file);

 for i:=0 to tmpFile.Count-1 do begin
     RegExpr.expression:='<body>';
     if RegExpr.Exec(tmpFile.Strings[i]) then begin
       tmpFile.Strings[i]:=tmpFile.Strings[i] + email.ldap.html_block.Disclaimer;
     end;


     RegExpr.expression:='<a href="(.+?)"';
     if RegExpr.Exec(tmpFile.Strings[i]) then begin
          if use_php=true then begin
             tmpFile.Strings[i]:=AnsiReplaceText(tmpFile.Strings[i],RegExpr.Match[1],hostname + '?path='+email.MessageMD5 + '&org='+email.ldap_ou + '&file=' + ExtractFileName(RegExpr.Match[1]));
          end else begin
             tmpFile.Strings[i]:=AnsiReplaceText(tmpFile.Strings[i],RegExpr.Match[1],hostname + '/bightml/' +email.ldap_ou + '/'+ email.MessageMD5 +'/' + ExtractFileName(RegExpr.Match[1]));
          end;
             
     end;
 
 end;
 tmpFile.SaveToFile(destination_file);
 tmpFile.Clear;
 tmpFile.Add('[GENERAL]');
 tmpFile.Add('maxday=' +MaxDay);
 tmpFile.Add('from=' +email.real_mailfrom);
 tmpFile.Add('to=' +email.mail_to);
 tmpFile.Add('subject=' +email.Subject);
 tmpFile.SaveToFile(ChangeFileExt(destination_file,'.conf'));
 
 MIME:=TMimeMess.Create;
 HTML_DATAS:=TstringList.Create;
 MAIL_DATAS:=TstringList.Create;
 HTML_DATAS.LoadFromFile(destination_file);
 if length(prependsubject)>0 then begin
    subject:=prependsubject + ' ' + email.Subject;
 end else begin
    subject:=email.Subject;
 end;
 LOGS.logs('BigHtmlMail:: building MAIL_DATAS ' + IntToStr(email.HeaderDatas.Count) + ' header lines, ' + IntToStr(HTML_DATAS.Count) + ' html lines');
 MAIL_DATAS.AddStrings(email.HeaderDatas);
 MAIL_DATAS.Add('From: ' +email.mail_from);
 MAIL_DATAS.Add('Date: ' + Rfc822DateTime(now));
 MAIL_DATAS.Add('To: <' + email.mail_to + '>');
 MAIL_DATAS.Add('Subject: ' + subject);
 MAIL_DATAS.Add('X-Mailer: artica-filter');
 
 
 MultiPartMix := MIME.AddPartMultipart('mixed', nil);
 MultiPartRel := MIME.AddPartMultipart('related', MultiPartMix);
 MultiPartAlt := MIME.AddPartMultipart('alternative', MultiPartRel);
 MIME.AddPartHTML(HTML_DATAS, MultiPartAlt);
 MIME.Header.Subject:=subject;
 MIME.EncodeMessage;
 MAIL_DATAS.AddStrings(MIME.Lines);
 MAIL_DATAS.SaveToFile(email.FilePath_source);
 email.FullMessage.Clear;
 email.FullMessage.AddStrings(MAIL_DATAS);
 LOGS.logs('BigHtmlMail:: Rebuild the message done....');
  

end;
//##############################################################################
function TscanQueue.ResendMailFromEmlFile(path:string):boolean;
var
   Email:TTMailInfo;
begin

    email.FilePath_source:=path;
    email.HeadFilePath:=ChangeFileExt(path,'.head');
    email.InPutMethod:=true;
    email.InputMethodSend:=true;
    result:=ResendMail(email);
end;
//##############################################################################
function TscanQueue.ResendMail(Email:TTMailInfo):boolean;

var

   RECIPIENTS_LIST,mail_to,mail_from:string;
   i:integer;
   LIST:TstringList;
   HEAD:TstringList;
   LOGS:TLogs;
   SMTP:TSMTPSend;

begin
   Result:=false;
  LIST:=TstringList.Create;
  HEAD:=TStringList.Create;
  LOGS:=TLogs.Create;
  RECIPIENTS_LIST:='';
  if not FileExists(Email.FilePath_source) then begin
    LOGS.logs('unable to stat ' + email.FilePath_source);
    exit
 end;

 if not FileExists(email.HeadFilePath) then begin
    LOGS.logs('unable to stat ' + email.HeadFilePath);
    exit
 end;


   if not email.InPutMethod then begin
      LOGS.logs('INPUT=FALSE');
      if email.AsEmailRelay=true then begin
        result:=email_relay_send(Email);
        exit;
      end;
   end;

   if email.InPutMethod=false then begin
      for i:=0 TO MAILTO_LIST.Count -1 do begin
        if length(trim(MAILTO_LIST.Strings[i]))>0 then RECIPIENTS_LIST:=RECIPIENTS_LIST + ' ' + trim(MAILTO_LIST.Strings[i]);
      end;
   end else begin
       RECIPIENTS_LIST:=email.mail_to;
   
   end;
   
   
 result:=false;


 
 LOGS.logs('ResendMail:: INPUT=TRUE');
 
 if email.InputMethodSend=false then begin
     LOGS.logs('ResendMail:: InputMethodSend=FALSE message will be deleted');
     DeleteFile(email.FilePath_source);
     DeleteFile(email.HeadFilePath);
     exit(true);
 end;
  LOGS.logs('ResendMail:: Load files');
  LIST.LoadFromFile(email.FilePath_source);
  HEAD.LoadFromFile(email.HeadFilePath);
  mail_from:=HEAD.Strings[0];
  mail_to:=HEAD.Strings[1];


     LOGS.logs('ReleaseMail:: to "' + mail_to + '" from "' + mail_from + '"');

     SMTP := TSMTPSend.Create;
     SMTP.TargetHost:='127.0.0.1';
     SMTP.TargetPort:='29300';

     LOGS.logs('ReleaseMail:: init ok');
     if not SMTP.Login then begin
        LOGS.logs('ReleaseMail:: Failed connect to 127.0.0.1 29300 -> ' + SMTP.FullResult.Text);
        ResendMailFailed(email,SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailFrom(mail_from,Length(LIST.Text)) then begin
       LOGS.logs(SMTP.FullResult.Text);
       ResendMailFailed(email,SMTP.FullResult.Text);
       exit(false);
     end;

     if not SMTP.MailTo(mail_to) then begin
        LOGS.logs(SMTP.FullResult.Text);
        ResendMailFailed(email,SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailData(LIST) then begin
        LOGS.logs(SMTP.FullResult.Text);
        ResendMailFailed(email,SMTP.FullResult.Text);
        exit(false);
     end;
     LOGS.logs(SMTP.FullResult.Text);
     SMTP.Logout;

     DeleteFile(email.FilePath_source);
     DeleteFile(email.HeadFilePath);
     exit(true);



end;
//##############################################################################
function TscanQueue.ResendMailFailed(Email:TTMailInfo;error:string):boolean;
var
   QueuePath,NeweMailPath,NeweHeadPath,error_path:string;
   ERR:TStringList;
begin
  result:=true;
  QueuePath:=ArticaFilterQueuePath() + '/in-failed';
  ForceDirectories(QueuePath);
  NeweMailPath:=QueuePath + '/' + ExtractFileName(email.FilePath);
  NeweHeadPath:=QueuePath + '/' + ExtractFileName(email.HeadFilePath);
  error_path:=ChangeFileExt(NeweHeadPath,'.err');
  CopyFile(email.HeadFilePath,NeweHeadPath);
  CopyFile(email.FilePath_source,NeweMailPath);
  ERR:=TStringList.Create;
  ERR.Add(error);
  ERR.SaveToFile(error_path);
  ERR.Free;
  DeleteFile(email.HeadFilePath);
  DeleteFile(email.FilePath_source);

end;
//##############################################################################



function TscanQueue.email_relay_send(Email:TTMailInfo):boolean;
   var
      email_content_path:string;
      email_header_path:string;
      queue_path:string;
begin
     queue_path:=ArticaFilterQueuePath();
     email_content_path:=queue_path + '/' + ExtractFileName(email.FilePath);
     email_header_path:=queue_path + '/' +ExtractFileName(email.HeadFilePath);
     email_header_path:=LeftStr(email_header_path,pos('.new',email_header_path)-1);
     LOGS.logs('reprocess "' + email.HeadFilePath+ '"');
     
     LOGS.logs('reprocess "' + email_header_path + '" "' + email_content_path + '"');
     
     fpsystem('/bin/mv ' + email.FilePath + ' ' +  email_content_path);
     fpsystem('/bin/mv ' + email.HeadFilePath + ' ' +  email_header_path);
     exit(true);

end;




function TscanQueue.get_ARTICA_PHP_PATH():string;
var path:string;
begin
  if not DirectoryExists('/usr/share/artica-postfix') then begin
  path:=ParamStr(0);
  path:=ExtractFilePath(path);
  path:=AnsiReplaceText(path,'/bin/','');
  exit(path);
  end else begin
  exit('/usr/share/artica-postfix');
  end;

end;
//##############################################################################
function TscanQueue.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:=false;
 s:='';
 if ParamCount>0 then begin
     for i:=0 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:=FoundWhatPattern;
   if RegExpr.Exec(s) then begin
      RegExpr.Free;
      result:=True;
   end;


end;
//##############################################################################
procedure TscanQueue.CopyFile(SourceFileName, TargetFileName: string);
var
    sStream, dStream: TFileStream;
begin
 try
    sStream := TFileStream.Create(SourceFileName, fmOpenRead);

    try
      dStream := TFileStream.Create(TargetFileName, fmCreate);

      try
        dStream.CopyFrom(sStream, 0);
      finally
        dstream.free;
      end;
    finally
      sStream.Free;
    end;
  except
   LOGS.logs('CopyFile error ! from ' + SourceFileName + ' ' + TargetFileName);
   fpsystem('/bin/cp ' + SourceFileName + ' ' + TargetFileName);

  end;
end;

//##############################################################################

procedure TscanQueue.DeleteFile(path:string);
Var F : Text;
begin
 if not FileExists(path) then begin
        exit;
 end;
TRY
 Assign (F,path);
 Erase (f);
 EXCEPT
 LOGS.logs('DeleteFIle error ' + path);
 fpsystem('/bin/rm ' + path);
 exit();
 end;
 
  //LOGS.logs(path + ' deleted');
end;
//##############################################################################
function TscanQueue.EXTRACT_PARAMETERS(pattern:string):string;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:='';
 RegExpr:=TRegExpr.Create;
 if ParamCount>1 then begin
     for i:=0 to ParamCount do begin
        s:=ParamStr(i);
         RegExpr.Expression:=pattern;
         if RegExpr.Exec(s) then begin
            Result:=RegExpr.Match[1];
            break;
         end;

     end;
  end;
  RegExpr.Free;
end;
//##############################################################################
function TscanQueue.GetFileSize(path:string):longint;
Var
L : File Of byte;
size:longint;
ko:longint;

begin
if not FileExists(path) then begin
   result:=0;
   exit;
end;
   TRY
  Assign (L,path);
  Reset (L);
  size:=FileSize(L);
   Close (L);
  ko:=size;
  result:=ko;
  EXCEPT

  end;
end;
//##############################################################################

end.
