unit scanQueue;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,strutils,IniFiles,oldlinux,BaseUnix,Logs,RegExpr in 'RegExpr.pas',md5,ldap,mailinfos,
smtpsend,mimemess, mimepart,synautil,db,sqlite3ds,dnssend,synamisc;

type
  TStringDynArray = array of string;


  type
  TscanQueue=class


private
   function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
   function EXTRACT_PARAMETERS(pattern:string):string;
   function  DirFiles(FilePath: string;pattern:string):TstringList;
   procedure CopyFile(SourceFileName, TargetFileName: string);
   procedure DeleteFile(path:string);
   function  Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
   function  RebuildHeader(Email:TTMailInfo):boolean;
   function  DetectDsapmError(error_path:string):boolean;
   function  GetFileSize(path:string):longint;
   SOURCE_EMAIL_RELAY_PATH:string;
   USE_DNSMASQ:boolean;
   DirListFiles:TstringList;
   LOGS:Tlogs;
   function  ParseFile(path:string):TTMailInfo;
   function  RBL_CACHE(rbl_name_server:string;ip_to_test:string;TableName:string):integer;
   procedure EDIT_RBL_CACHE(rbl_name_server:string;ip_to_test:string;res:string;TableName:string);
   function  DSNMASQ_RUN():boolean;
   function  DNSMASQ_PID():string;
   
   function  TRUSTED_LOCAL_USERS(messageinfo:TTMailInfo):boolean;
   
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
   
   //SURBL LIST

   
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
    if not D then logs.ArticaSend('TscanQueue:: "' + IntTOStr(DirListFiles.Count) + '" messages in queue (' + QueuePathString + ')');
    if D then writeln('artica-send:: TscanQueue:: "' + IntTOStr(DirListFiles.Count) + '" messages in queue (' + QueuePathString + ')');
    if DirListFiles.Count<Maxfiles then MaxFiles:=DirListFiles.Count;
    if D then writeln('TscanQueue : Maxfiles=' ,MaxFiles);
    
    for  i:=0 to MaxFiles-1 do begin
    
          MAILTO_LIST.Clear;
          MAILTO_LIST_ACTION.Clear;
          messageinfo:=ParseFile(DirListFiles.Strings[i]);
          logs.ArticaSend('Execute::[' +IntTOStr(i) + '] from: <' + messageinfo.real_mailfrom + '>(' + messageinfo.mail_from_name+' ' + messageinfo.GeoCountry + '/' + messageinfo.GeoCity + ') to <' + messageinfo.mail_to +'> (' + messageinfo.ldap_uid + ') RATE: ' + IntToStr(messageinfo.X_SpamTest_Rate) + ' Result=[' +messageinfo.Artica_results + ']');
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
      if d then writeln(DirListFiles.Strings[i] + ' --> ' + dsTest.SqliteReturnString);
      sqLOGS.ArticaSendSql(DirListFiles.Strings[i] + ' --> ' + dsTest.SqliteReturnString);

      RegExpr.Expression:='SQLITE_DONE';
      if RegExpr.Exec(dsTest.SqliteReturnString) then begin
         DeleteFile(DirListFiles.Strings[i]);
      end;
  end;





end;
//#########################################################################################
function TscanQueue.UserInfos(email:string):boolean;
var
   infos:ldapinfos;
   ldap:Tldap;
   i:integer;
begin
  ldap:=Tldap.Create;
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
 

 
 logs.ArticaSend('ResendMail:: Load files');
 LIST.LoadFromFile(bodyfile);
 HEAD.LoadFromFile(headfile);
 mail_from:=HEAD.Strings[0];
 mail_to:=HEAD.Strings[1];


     LOGS.ArticaSend('ReleaseMail:: to "' + mail_to + '" from "' + mail_from + '"');

     SMTP := TSMTPSend.Create;
     SMTP.TargetHost:='127.0.0.1';
     SMTP.TargetPort:=SMTP_PORT;
     if D then writeln('ReleaseMail:: to "' + mail_to + '" from "' + mail_from + '" -> 127.0.0.1:' + SMTP_PORT);
     
     if not SMTP.Login then begin
        LOGS.ArticaSend('ReleaseMail:: Failed connect to 127.0.0.1 ' + SMTP_PORT + ' -> ' + SMTP.FullResult.Text);
        if d then writeln('ReleaseMail:: Failed connect to 127.0.0.1 ' + SMTP_PORT + ' -> ' + SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailFrom(mail_from,Length(LIST.Text)) then begin
       if d then writeln(SMTP.FullResult.Text);
       LOGS.ArticaSend(SMTP.FullResult.Text);
       exit(false);
     end;

     if not SMTP.MailTo(mail_to) then begin
        if d then writeln(SMTP.FullResult.Text);
        LOGS.ArticaSend(SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailData(LIST) then begin
        if d then writeln(SMTP.FullResult.Text);
        LOGS.ArticaSend(SMTP.FullResult.Text);
        exit(false);
     end;
     LOGS.ArticaSend(SMTP.FullResult.Text);
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
    ldap:Tldap;
    
begin
  email:=TMailInfo.Create;
  head_content:=TStringList.Create;
  messageinfo.FullMessage:=TstringList.Create;
  messageinfo.InPutMethod:=True;
  messageinfo.InputMethodSend:=False;
  messageinfo.Artica_results:='send';
  queue_path:=ArticaFilterQueuePath();
  ldap:=Tldap.Create;
  
  From:=ParamStr(2);
  mail_to:=ParamStr(3);

  head_content.Add(From);
  head_content.Add(mail_to);
  
  While Not Eof(input) do begin
        Readln(input, line);
        messageinfo.FullMessage.Add( line );
    end;
    
    logs.ArticaSend('');
    logs.ArticaSend('');
    logs.ArticaSend('> > >START------------------------------------------------------------------------------------------------');
    logs.ArticaSend('input >> ' + IntToStr(messageinfo.FullMessage.Count) + ' ->');

    messageinfo.queue_path:=queue_path;
    messageinfo.real_mailfrom:=From;
    messageinfo.mail_to:=mail_to;
    messageinfo:=email.Scan('',messageinfo);
    messageinfo.ldap:=ldap.Ldap_infos(mail_to);
    messageinfo.ldap_uid:=messageinfo.ldap.uid;
    messageinfo.ldap_ou:=messageinfo.ldap.user_ou;


    messageinfo.HeadFilePath:=queue_path + '/' + messageinfo.MessageMD5 + '.head';
    messageinfo.FilePath:=queue_path + '/' + messageinfo.MessageMD5 + '.eml';
    head_content.SaveToFile(messageinfo.HeadFilePath);
    messageinfo.FullMessage.SaveToFile(messageinfo.FilePath);
    SourceFilePath:=messageinfo.FilePath;
    messageinfo.FilePath_source:=messageinfo.FilePath;
    messageinfo.MessageSizeInt:=GetFileSize(messageinfo.FilePath_source);
    

    
    logs.ArticaSend('FROM............... : ' +messageinfo.real_mailfrom + ' <' +messageinfo.mail_from + '>');
    logs.ArticaSend('TO (ldap)...........: ' +messageinfo.ldap_uid + ' <' +mail_to + '>');
    logs.ArticaSend('Subject............ : ' +messageinfo.Subject);
    logs.ArticaSend('X-Spam Tests results: ' +IntToStr(messageinfo.X_SpamTest_Rate));

     if BOGOFILTER_ROBOTS(messageinfo) then begin
        logs.ArticaSend('------------------------------------------------------------------------------------------------> > > END');
        logs.ArticaSend('');
        logs.ArticaSend('');
        exit;
     end;

    
    if not TRUSTED_LOCAL_USERS(messageinfo) then messageinfo:=ApplyRules(messageinfo,mail_to);



    ReleaseMail(messageinfo);
    logs.ArticaSend('------------------------------------------------------------------------------------------------> > > END');

    logs.ArticaSend('');
    logs.ArticaSend('');
    email.Free;
    halt(0);
    exit;
    

end;
//#########################################################################################
function TscanQueue.TRUSTED_LOCAL_USERS(messageinfo:TTMailInfo):boolean;
var
 ldap:Tldap;
 ldap_infos:ldapinfos;
begin
  result:=false;
  if messageinfo.ldap.TrustMyUsers<>'yes' then exit(false);

  ldap:=Tldap.Create;
  ldap_infos:=ldap.Ldap_infos(messageinfo.real_mailfrom);
  Ldap.free;
  if ldap_infos.user_ou=messageinfo.ldap.user_ou then begin
     logs.ArticaSend(messageinfo.real_mailfrom +' is trusted (local user)');
     exit(true);
  end;
  


end;
//#########################################################################################
function TscanQueue.BOGOFILTER_ROBOTS(messageinfo:TTMailInfo):boolean;
var
   RegExpr:TRegExpr;
   i:integer;
   email:string;
   ldap_infos:ldapinfos;
   ldap:Tldap;
begin
   if not FileExists('/usr/local/bin/bogofilter') then exit(false);
   ldap:=Tldap.Create;
   ldap_infos:=ldap.Ldap_infos(messageinfo.real_mailfrom);
   result:=false;


    if ldap_infos.BOGOFILTER_ROBOTS.Count=0 then begin
       logs.ArticaSend('bogofilter:: no robots here for ' + messageinfo.mail_from);
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
     logs.ArticaSend('bogofilter:: register ' + robot_type + ' for ' + ldap_infos.uid);
     logs.ArticaSend('bogofilter:: ' + cmd_line);
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
    logs.ArticaSend('');
    logs.ArticaSend('');
    logs.ArticaSend('> > >START------------------------------------------------------------------------------------------------');
    logs.ArticaSend('Receive ' + emailPath + ' -> in-process queue');
    SOURCE_EMAIL_RELAY_PATH:=emailPath;
    headpath:=ChangeFileExt(emailPath,'.envelope.new');
    CopyDir:=ExtractFilePath(emailPath) + 'in-process';


    
    
    if not DirectoryExists(ArticaFilterQueuePath() + '/in-process') then begin
        logs.ArticaSend('Creating folder ' + ArticaFilterQueuePath() + '/in-process');
        forceDirectories(ArticaFilterQueuePath() + '/in-process');
    end;
    
    CopyFile(emailPath,CopyDir+ '/' + extractFileName(emailPath));
    CopyFile(headpath,CopyDir+ '/' + extractFileName(headpath));
    
    logs.ArticaSend('eMailRelay_scan:: block ' + emailPath);
    eMailRelay_filter(emailPath);
    
    logs.ArticaSend(' Delete: ' + CopyDir+ '/' + extractFileName(emailPath) );
    logs.ArticaSend(' Delete: ' + CopyDir+ '/' + extractFileName(headpath) );
    
    DeleteFile(CopyDir+ '/' + extractFileName(emailPath));
    DeleteFile(CopyDir+ '/' + extractFileName(headpath));


    logs.ArticaSend('------------------------------------------------------------------------------------------------> > > END');
    logs.ArticaSend('');
    logs.ArticaSend('');
     halt(0);

    exit;
end;
//#########################################################################################
procedure TscanQueue.ReleaseMail(email:TTMailInfo);

   var
      Message_path:string;
      uid,HeadFilePath:string;
      ldap:TLdap;
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
    ldap:=Tldap.Create();

    for i:=0 to MAILTO_LIST_ACTION.Count -1 do begin
             logs.ArticaSend('ReleaseMail:: ' + MAILTO_LIST_ACTION.Strings[i]);
             quarantine:=false;
             virus_quarantine:=false;
             kill:=false;
             artica_action:='send';
             email.InputMethodSend:=True;
        
             actions:=Explode(';',MAILTO_LIST_ACTION.Strings[i]);
             recipient:=actions[0];
             uid:=actions[1];
             artica_action:=actions[5];
             ou:=actions[6];
          
          
             if actions[2]='yes' then quarantine:=true;
             if actions[3]='yes' then kill:=true;
             if actions[4]='yes' then virus_quarantine:=true;
             Original_mailFilePath:=email.FilePath;
             tmp:=ldap.eMailFromUid(uid);
             logs.ArticaSend('ReleaseMail:: mail status=' + artica_action + ' for account ' + ou +'/' + uid + ' <' + recipient + '> --> ' +tmp);

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
             

             if quarantine then logs.ArticaSend('ACTION=QUARANTINE') else logs.ArticaSend('ACTION=NO QUARANTINE');
             if kill then logs.ArticaSend('ACTION=DELETE') else logs.ArticaSend('ACTION=NO DELETE');
             if email.InputMethodSend then logs.ArticaSend('InputMethodSend=TRUE') else logs.ArticaSend('InputMethodSend=FALSE');
             logs.ArticaSend('ACTION=' + artica_action);

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
             
   
   SendSQLQuery(email.FilePath,artica_action,uid,ou,ActionQuarantine,email);
   if email.InPutMethod then logs.ArticaSend('ReleaseMail:: InPutMethod=True') else logs.ArticaSend('ReleaseMail:: InPutMethod=false');

end;

    if not email.InPutMethod then begin
       if email.AsEmailRelay=true then begin
          exit();
       end;
       DeleteFile(email.FilePath_source);
       DeleteFile(email.HeadFilePath);
       exit();
    end;



    if email.InputMethodSend then logs.ArticaSend('ReleaseMail --> ResendMail:: InputMethodSend=True') else logs.ArticaSend('ReleaseMail --> ResendMail:: InputMethodSend=false');
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

   if not DirectoryExists('/var/quarantines/procmail/' + uid + '/quarantine')then logs.ArticaSend('ReleaseMail:: Warn !! unable to create folder /var/quarantines/procmail/' + uid + '/quarantine');
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
    sqLOGS.ArticaSendSql(sql+'=' +DatabasePath + '  "' + dsTest.SqliteReturnString + '"');
    sqLOGS.ArticaSend(dsTest.SqliteReturnString);
    RegExpr.Expression:='SQLITE_DONE';
    if not RegExpr.Exec(dsTest.SqliteReturnString) then begin
          sql_err:=TStringList.Create;
          sql_err.Add(sql);
          sql_err_file:=ChangeFileExt(email.FilePath,'.sql');
          sqLOGS.ArticaSend('Put error to ' +sql_err_file);
          sql_err.SaveToFile(sql_err_file);
          sql_err.Free;
    end;
    
    
    
    EXCEPT
    sqLogs.ERRORS('SendSQLQuery:: ERROR generated ' +  dsTest.SqliteReturnString);
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
 if length(trim(result))=0 then result:='/usr/share/artica-filter';
end;
//##############################################################################
Function TscanQueue.BuildActionTable(email:TTMailInfo;recipient:string):boolean;
var
   ldap:Tldap;
   uid:string;
   ou:string;
   RegExpr:TRegExpr;
   quarantine,kill,virus_quarantine:string;
begin
     ldap:=Tldap.Create();
     uid:=ldap.uidFromMail(recipient);
     ou:=ldap.OU_From_eMail(recipient);
     quarantine:='no';
     kill:='no';
     virus_quarantine:='no';
     
     Logs.ArticaSend('BuildActionTable:: <' + recipient + '> --->' +email.Artica_results);
     if email.GoInQuarantine=true then quarantine:='yes';
     if email.KillMail=true then kill:='yes';
     if email.GoInVirusQuarantine=true then virus_quarantine:='yes';
     
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
  Logs.ArticaSend('ParseFile:: number of recipients ' + IntToStr(MAILTO_LIST.Count));
  USE_DNSMASQ:=DSNMASQ_RUN();
  if not USE_DNSMASQ then Logs.ArticaSend('ParseFile::DNSMASQ is disabled');
  
  for i:=0 to MAILTO_LIST.Count -1 do begin
         recipient:=MAILTO_LIST.Strings[i];
         messageinfo.Artica_results:='send';
         logs.ArticaSend('');
         logs.ArticaSend('ParseFile::************************************************************************');
         messageinfo:=ApplyRules(messageinfo,recipient);
         logs.ArticaSend('ParseFile::******************* <' + recipient + '> ******************* ' + messageinfo.Artica_results);
         logs.ArticaSend('ParseFile::************************************************************************');

         logs.ArticaSend('');
         
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
     if D then logs.ArticaSend('-->ApplyRules <--;');
     if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
        end;

     logs.ArticaSend('ApplyRules:: Scan email for ' + recipient + ' rules');
     

     
 //************ Extensions cheking ****************************************************
   if D then logs.ArticaSend('-->ApplyRenattach();');
   messageinfo:=ApplyRenattach(recipient,messageinfo);
     if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
     end;


 //************ Antivirus ****************************************************
           if D then logs.ArticaSend('--> ScanAVMail();');
        messageinfo:=ScanAVMail(recipient,messageinfo);
        
        if messageinfo.Artica_results='infected' then begin
           BuildActionTable(messageinfo,recipient);
           exit(messageinfo);
        end;
 //************ WhiteList ****************************************************
         if WhiteList(messageinfo,recipient) then begin
            messageinfo.Artica_results:='white';
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
 //************ anti-spam ****************************************************
        messageinfo:=ApplyKasRules(recipient,messageinfo);

        if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
        end;
        
        
 //************ bogofilter ****************************************************
        messageinfo:=ApplyDspam(recipient,messageinfo);
        if messageinfo.Artica_results<>'send' then begin
             BuildActionTable(messageinfo,recipient);
             exit(messageinfo);
        end;


  //************ Faked sender ****************************************************
       if D then logs.ArticaSend('ApplyRules:: Detect Faked sender address');
        messageinfo:=ApplyFakedSendAddress(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
  //************ Country Deny ****************************************************
         messageinfo:=ApplyCountryDeny(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
 //************ RBL****************************************************
         messageinfo:=ScanRBL(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         
 //************ SURBL****************************************************
         messageinfo:=ScanSURBL(recipient,messageinfo);
         if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
         end;
         

        
 //************ User defined rules ****************************************************
        messageinfo:=ScanUserRegex(recipient,messageinfo);
        logs.ArticaSend('ApplyRules:: ScanUserRegex:='+ messageinfo.Artica_results);
        if messageinfo.Artica_results<>'send' then begin
            BuildActionTable(messageinfo,recipient);
            exit(messageinfo);
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
    
       if email.Artica_results<>'bogo' then begin
          if FileExists('/usr/local/bin/bogofilter') then begin
             logs.ArticaSend('BOGOFILTER INSTALLED : learn this spam');
             commandline:='/usr/local/bin/bogofilter  --register-spam --use-syslog --no-config-file --bogofilter-dir=' + email.queue_path + '/bogofilter/users/' +email.ldap_uid + ' --input-file=' + email.FilePath_source + ' >/dev/null 2>&1';;
             logs.ArticaSend(commandline);
             shell(commandline);
          end else begin
            logs.ArticaSend('BOGOFILTER IS NOT INSTALLED : unable to learn this spam');
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
       logs.ArticaSend('Delete recipient "' + recipient + '" in "' + HeadFilePath+ '"');
       
       RemoveMailTo(SOURCE_EMAIL_RELAY_PATH,recipient,SOURCE_EMAIL_RELAY_PATH);
       
       if not FileExists(HeadFilePath) then exit;
       LIST:=TstringList.Create;
       LIST.LoadFromFile(HeadFilePath);
       For i:=0 to LIST.Count -1 do begin
           if pos(recipient,LIST.Strings[i])>0 then begin
              logs.ArticaSend('Delete recipient "' + LIST.Strings[i] + '"');
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
  
  logs.ArticaSend('RebuildHeader: new header as now ' + IntToStr(MailtoCount) + ' remote recipient(s)');
  
  RegExpr.Expression:='X-MailRelay-ToCount:\s+([0-9]+)';
  for i:=0 to MyFILE.Count -1 do begin
     if RegExpr.Exec(MyFILE.Strings[i]) then begin
           logs.ArticaSend('RebuildHeader:: Change X-MailRelay-ToCount from ' + RegExpr.Match[1] + ' to ' + IntToStr(MailtoCount));
           MyFILE.Strings[i]:='X-MailRelay-ToCount: ' + IntToStr(MailtoCount);
     end;
  end;
   MyFILE.SaveToFile(HeadFilePath);
  
end;

//#########################################################################################
function TscanQueue.ExplodeMailTo(Email:TTMailInfo):boolean;
var
   i:integer;
   Ldap:Tldap;
   uid:string;
begin
   MAILTO_LIST.Clear;
   Ldap:=TLdap.Create;
   logs.ArticaSend('ExplodeMailTo::' + IntToStr(length(email.RecipientsList)-1) + ' recipients');
   for i:=0 to length(email.RecipientsList)-1 do begin
        if not IsMailToExist(email.RecipientsList[i]) then begin
           uid:=ldap.uidFromMail(email.RecipientsList[i]);
           if length(uid)>0 then begin
              logs.ArticaSend('ExplodeMailTo:: ' +IntTostr(i) + '] <'+email.RecipientsList[i] + '> --> will scan for ' + uid);
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


function TscanQueue.ApplyFakedSendAddress(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   RegExpr:TRegExpr;
   ldap:Tldap;
   Parameter:string;
   SenderName:string;
   ConSender:string;
   ou:string;
begin
  if length(trim(Email.real_mailfrom))=0 then exit;

  if D then logs.ArticaSend('ApplyFakedSendAddress:: Initialize');
  ldap:=TLdap.Create;
  result:=Email;
  if D then logs.ArticaSend('ApplyFakedSendAddress:: get ou from ' + recipient);
  ou:=trim(ldap.OU_From_eMail(trim(recipient)));
  if length(ou)=0 then exit;
  if D then logs.ArticaSend('ApplyFakedSendAddress:: get Parameter from  ' + ou);
  Parameter:=ldap.FackedSenderParameters(ou);
  if D then logs.ArticaSend('ApplyFakedSendAddress:: Parameter=' + Parameter);
  if Parameter='pass' then begin
     ldap:=Tldap.Create;
     exit(Email)
  end;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='<(.+?)>';
  
  if D then logs.ArticaSend('ApplyFakedSendAddress:: ou=' + ou + ';Parameter=' + Parameter);
  
  if not RegExpr.Exec(Email.mail_from_name) then begin
     logs.ArticaSend('ApplyFakedSendAddress:: unable to match into ' + Email.mail_from_name + ' aborting...');
     exit(Email);
  end;
  SenderName:=LowerCase(RegExpr.Match[1]);
  ConSender:=LowerCase(Email.real_mailfrom);
  
   logs.ArticaSend('ApplyFakedSendAddress:: '+ConSender + '< >' + SenderName);
  if ConSender<>SenderName then begin
   Email.Artica_results:='faked';
   if Parameter='delete' then email.KillMail:=true;
      if Parameter='quarantine' then begin
            email.KillMail:=true;
            email.GoInQuarantine:=true;
      end;
   end;

 logs.ArticaSend('ApplyFakedSendAddress:: '+Email.Artica_results);
 RegExpr.Free;
 ldap.free;
 result:=Email;
  

end;

//#########################################################################################
function TscanQueue.ApplyCountryDeny(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   RegExpr:TRegExpr;
   ldap:Tldap;
   ou:string;
   TMP:TStringDynArray;
   i:integer;
begin
  if length(trim(Email.real_mailfrom))=0 then exit;

  if D then logs.ArticaSend('ApplyCountryDeny:: Initialize');
  ldap:=TLdap.Create;
  result:=Email;
  
  if length(email.ldap_ou)=0 then begin
     if D then logs.ArticaSend('ApplyCountryDeny:: get ou from ' + recipient);
        email.ldap_ou:=trim(ldap.OU_From_eMail(trim(recipient)));
  end;
  
  
  if length(ou)=0 then exit;
  if D then logs.ArticaSend('ApplyCountryDeny:: get Parameter from  ' + ou);
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
              logs.ArticaSend('ApplyCountryDeny:: ' + Uppercase(email.GeoCountry) + '=' + Uppercase(RegExpr.Match[1]));
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


 logs.ArticaSend('ApplyCountryDeny:: '+Email.Artica_results);
 RegExpr.Free;
 ldap.free;
 result:=Email;
end;

//#########################################################################################
function TscanQueue.ApplyRenattach(recipient:string;Email:TTMailInfo):TTMailInfo;
var
   renattach_path,RenAttach_info:string;
   ldap:Tldap;
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
    ldap:=Tldap.Create;
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
    logs.ArticaSend('ApplyRenattach:: rules group=' + trim(rules_applied));
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
         logs.ArticaSend('ApplyRenattach "' + RenAttach_info + '"');
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
  result:=Email;
  bogo_tag:='';
  quue_path:=email.queue_path;

    if length(email.ldap.user_ou)=0 then begin
         logs.ArticaSend('bogofilter::  not an internal user');
         exit;
    end;

    if not fileExists('/usr/local/bin/bogofilter') then begin
       logs.ArticaSend('bogofilter:: this application is NOT INSTALLED...');
       exit;
    end;
    logs.ArticaSend('bogofilter:: start operations');
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
    logs.ArticaSend(command_line);
    t:=0;
    if FileExists(temp_dspam_file) then begin
      messageSize:=GetFileSize(temp_dspam_file);
      if messageSize<email.MessageSizeInt then begin
           logs.ArticaSend('bogofilter WARNING: corrupted message !!!.. aborting scanning');
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
              logs.ArticaSend('bogofilter:: result:=' + email.dspam_result + ' (' + email.dspam_probability + ') message size : ' + IntToStr(messageSize));
              bogo_pourc:=Round(StrToFloat(email.dspam_probability)*100);
              break;
           end;
        end;



       LIST.SaveToFile(email.FilePath_source);
       
       
       
       if bogo_pourc>=email.ldap.BOGOFILTER_PARAM.max_rate then begin
           logs.ArticaSend('bogofilter:: SPAM detected by bogofilter (' + IntToStr(bogo_pourc) + ' >=' + IntToStr(email.ldap.BOGOFILTER_PARAM.max_rate));
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
    ldap:Tldap;
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
   ldap:=Tldap.Create();
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
     logs.ArticaSend('ApplyKasRules::INTERNAL ERROR !!');
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
        logs.ArticaSend('KASPERSKY ANTISPAM:: ' + USER_OU + '\'+ uid + ' detection_rate=' + IntToStr(detection_rate) + ' >' +   IntToStr(second_rate) + ' exceed maximum detection rate defined');
        if action_quarantine=1 then Email.GoInQuarantine:=True;
        if action_killmail=1 then Email.KillMail:=true;
        Email.Artica_results:='spam';
        exit(Email);
      end;
   end;

   if X_SpamTest_Rate>second_rate then begin
        logs.ArticaSend('KASPERSKY ANTISPAM::' + USER_OU + '\'+ uid + ' detection_rate=' + IntToStr(detection_rate) + '>' +   IntToStr(second_rate) + ' exceed maximum second detection rate defined');
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
     logs.ArticaSend('RetreiveSenderRecipients:: -> ' + Email.FilePath);
      TRANSPORT_TABLE:=TStringList.Create;
      HeadFilePath:=ChangeFileExt(Email.FilePath,'.head');
      if not FileExists(HeadFilePath) then begin
        logs.ArticaSend('RetreiveSenderRecipients:: unable to stat ' + HeadFilePath);
      exit;
      end;

      TRANSPORT_TABLE.LoadFromFile(HeadFilePath);
      logs.ArticaSend('RetreiveSenderRecipients::"' + HeadFilePath + '" TRANSPORT_TABLE=' + IntToStr(TRANSPORT_TABLE.Count) + '"' + TRANSPORT_TABLE.Text + '"');

    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='MAIL_FROM=(.+)';
    for i:=0 to TRANSPORT_TABLE.Count -1 do begin
     if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
          logs.ArticaSend('RetreiveSenderRecipients:: -> ' + RegExpr.Match[1]);
          Email.real_mailfrom:=RegExpr.Match[1];
          break;
          end;

    end;
    RegExpr.Expression:='MAIL_TO=(.+)';
    for i:=0 to TRANSPORT_TABLE.Count -1 do begin
     if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
          logs.ArticaSend('RetreiveSenderRecipients:: -> ' + RegExpr.Match[1]);
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
   LDAP:Tldap;
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
   ldap:Tldap;
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
  ldap:=Tldap.Create();


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
       logs.ArticaSend('KASPERSKY AV::CLEAN');
       eMail.Artica_results:='send';
       exit(eMail);
  end;
  email.Artica_results:='infected';
  logs.ArticaSend('KASPERSKY AV::INFECTED:: ArchiveMail=' + IntToStr(ArchiveMail) + ' NotifyTo=' + IntToStr(NotifyTo) + ' DeleteDetectedVirus=' + IntToStr(NotifyTo));
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
     logs.ArticaSend('SendSMTPNotifications:: Original message FROM <' + Email.real_mailfrom + '> "' + email.Subject + '" MAIL TO <' + trim(email.mail_to) + '>' );

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

     logs.ArticaSend('SendSMTPNotifications:: Success connecting to ' + SMTP.TargetHost + ' on port ' + SMTP.TargetPort);
     if not SMTP.MailFrom(NotificationMailFrom, Length(MAIL_DATAS.Text)) then goto MyFree;
     if not SMTP.MailTo(trim(mail_to)) then goto MyFree;
     if not SMTP.MailData(MAIL_DATAS) then goto Myfree;

     SMTP.Logout;
     logs.ArticaSend('SendSMTPNotifications:: SUCCESS sending notification to "'+mail_to + '" from "'+NotificationMailFrom+'"');

           SMTP.Free;
           HTML_DATAS.FRee;
           MIME.free;
           MAIL_DATAS.Free;
    exit(true);



   myfree:
   logs.ArticaSend('SendSMTPNotifications:: ERROR SENDING MAIL "' +SMTP.FullResult.Text + '"');
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
   ldap:Tldap;
   header:string;
   pattern:string;
   regex:string;
   action:string;
begin
  RegExpr:=TRegExpr.Create;
  ldap:=Tldap.Create();
  email.Artica_results:='send';

  ldap.LoadArticaUserRules(recipient);
  if ldap.TEMP_LIST.Count=0 then begin
    logs.ArticaSend('ScanUserRegex:: no user rules...');
    ldap.Free;
    RegExpr.free;
    exit();
  end;
  logs.ArticaSend('ScanUserRegex:: ' + IntToStr(ldap.TEMP_LIST.Count) + ' rules');
  for i:=0 to ldap.TEMP_LIST.Count-1 do begin
      RegExpr.Expression:='<header>(.+?)</header><pattern>(.+?)</pattern><regex>(.+?)</regex><action>(.+?)</action>';
      if RegExpr.Exec(ldap.TEMP_LIST.Strings[i]) then begin
              if ScanHeaderRegex(RegExpr.Match[1],RegExpr.Match[2],StrToInt(RegExpr.Match[3]),Email)=true then begin
                  action:=trim(RegExpr.Match[4]);
                  logs.ArticaSend('MAIL MATCH USER POLICY RULE: "' + action + '"');

                   if action='quarantine' then begin
                     logs.ArticaSend('mail will be filtered and quarantine');
                     Email.Artica_results:='filtered';
                     email.GoInQuarantine:=true;
                     exit(email);
                  end;
                  
                  if action='pass' then begin
                     logs.ArticaSend('mail will be white listed');
                     Email.Artica_results:='white';
                     ldap.Free;
                     RegExpr.free;
                     exit(email);
                  end;


                  if action='delete' then begin
                     Email.Artica_results:='filtered';
                     logs.ArticaSend('mail will be white filtered and deleted');
                     Email.KillMail:=true;
                     ldap.Free;
                     RegExpr.free;
                     exit(email);
                  end;
                  
                  if action='quarantine' then begin
                     Email.Artica_results:='filtered';
                     logs.ArticaSend('mail will be white filtered and quarantine');
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

   if length(email.ldap_ou)=0 then exit;
   RegExpr:=TRegExpr.create;
   LOGS:=Tlogs.create;
   PC:=0; PCTOT:=0;
   Action:=email.ldap.RBL_SERVER_ACTION;
   RegExpr.Expression:='(.+?):([0-9]+)';
   LOGS.ArticaSend('ScanRBL::' + Action + '->' + IntToStr(email.ldap.RBL_SERVERS.Count) + ' RBL servers' + ' ' + IntToStr(length(Email.RELAIS)) + ' IP found');
   if email.ldap.RBL_SERVERS.Count=0 then begin
      exit;
      RegExpr.Free;
   end;
   
   
   for i:=0 to email.ldap.RBL_SERVERS.Count-1 do begin
        if RegExpr.Exec(email.ldap.RBL_SERVERS.Strings[i]) then begin
                LOGS.ArticaSend('ScanRBL::' + RegExpr.Match[1] + ' {' + RegExpr.Match[2] + '}+' +IntToStr(PCTOT));
                PCTOT:=PCTOT+ StrToInt(RegExpr.Match[2]);
                for t:=0 to length(Email.RELAIS)-1 do begin
                    if rblinfo(RegExpr.Match[1],Email.RELAIS[t]) then begin
                         PC:=PC+StrToint(RegExpr.Match[2]);
                         LOGS.ArticaSend('ScanRBL::' + RegExpr.Match[1] + '->' + Email.RELAIS[t] + '=BLOCK');

                    end else begin
                         LOGS.ArticaSend('ScanRBL::' + RegExpr.Match[1] + '->' + Email.RELAIS[t] + '=SAFE');
                    end;
                
                end;
        
        end;
   
   end;
LOGS.ArticaSend('ScanRBL:: Result=' + IntToStr(PC) + ' Must reach 100');
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
   ldap:Tldap;
   RegExpr:TRegExpr;
   PC:Integer;
   PCTOT:Integer;
   LOGS:Tlogs;
begin
   ldap:=Tldap.Create();
   RegExpr:=TRegExpr.create;
   LOGS:=Tlogs.create;

  if length(email.ldap_ou)=0 then begin
     if D then logs.ArticaSend('ScanSURBL:: get ou from ' + recipient);
        email.ldap_ou:=trim(ldap.OU_From_eMail(trim(recipient)));
  end;
    PC:=0; PCTOT:=0;
   Action:=ldap.LoadOUSURBLAction(email.ldap_ou);
   LIST:=ldap.LoadOUSURBL(email.ldap_ou);
   RegExpr.Expression:='(.+?):([0-9]+)';
   LOGS.ArticaSend('ScanSURBL::' + Action + '->' + IntToStr(length(LIST)) + ' SURBL servers' + ' ' + IntToStr(length(Email.uribl)) + ' URI server found');
   if length(LIST)=0 then begin
      exit;
      ldap.free;
      RegExpr.Free;
   end;


   for i:=0 to length(LIST)-1 do begin
        if RegExpr.Exec(LIST[i]) then begin
                LOGS.ArticaSend('ScanSURBL::' + RegExpr.Match[1] + ' {' + RegExpr.Match[2] + '}+' +IntToStr(PCTOT));
                PCTOT:=PCTOT+ StrToInt(RegExpr.Match[2]);
                for t:=0 to length(Email.uribl)-1 do begin
                    if rblinfo(RegExpr.Match[1],Email.uribl[t]) then begin
                         PC:=PC+StrToint(RegExpr.Match[2]);
                         LOGS.ArticaSend('ScanSURBL::' + RegExpr.Match[1] + '->' + Email.uribl[t] + '=BLOCK');

                    end else begin
                         LOGS.ArticaSend('ScanSURBL::' + RegExpr.Match[1] + '->' + Email.uribl[t] + '=SAFE');
                    end;

                end;

        end;

   end;
LOGS.ArticaSend('ScanSURBL:: Result=' + IntToStr(PC) + ' Must reach 100');
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
   ldap:Tldap;
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
   ldap:Tldap;
   parameters:string;
   newip,cache_type:string;
   Lres:TstringList;
   D:boolean;
   Cache_result:integer;
begin
  if USE_DNSMASQ then
  result:=false;
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
                  LOGS.ArticaSend('rblinfo:: DNSMX:: request from="' +ip_to_test + '" =>' + rbl_name_server + ' You are a verified open relay');
                  LOGS.Free;
                  exit(true);
               end;

               if Cache_result=3 then begin
                  LOGS.ArticaSend('rblinfo:: DNSMX:: request from="' +ip_to_test + '" =>' + rbl_name_server + '  Direct mail from dialups not allowed');
                  LOGS.Free;
                  exit(true);
               end;
        
               if Cache_result=4 then begin
                  LOGS.ArticaSend('rblinfo:: DNSMX:: request from="' +ip_to_test + '" =>' + rbl_name_server + '  You are a confirmed spam source');
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
           LOGS.ArticaSend('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' You are a verified open relay');
           if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'2','rbl');
           result:=True;
        end;
        
        if trim(Lres.Strings[i])='127.0.0.3' then begin
           LOGS.ArticaSend('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' Direct mail from dialups not allowed');
           if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'3','rbl');
           result:=True;
        end;
        
        if trim(Lres.Strings[i])='127.0.0.4' then begin
           LOGS.ArticaSend('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' You are a confirmed spam source');
           if not USE_DNSMASQ then EDIT_RBL_CACHE(rbl_name_server,ip_to_test,'4','rbl');
           result:=True;
        end;
        
        if result=false then begin
              if RegExpr.Exec(Lres.Strings[i]) then begin
                   LDI:=StrToint(RegExpr.Match[1]);
                   if LDI>4 then begin
                       LOGS.ArticaSend('rblinfo:: DNSMX:: request from="' +s + '" =>' + rbl_name_server + ' Note: ' + RegExpr.Match[1]);
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
   logs.ArticaSend('RBL_CACHE:: Unable to stat rbl_database.db RBL/URIBL cache is disabled');
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
   logs.ArticaSend('EDIT_RBL_CACHE:: ' +db.SqliteReturnString);
   logs.ArticaSendSql(sql + ' => ' + db.SqliteReturnString);
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

   //logs.ArticaSend('ScanHeaderRegex:: scan header ' + header + ' pattern:"' + pattern + '" in '+ IntToStr(Header_values.Count) + ' line(s)');
   if Header_values.Count=0 then begin
      Header_values.Free;
      RegExpr.free;
      exit;
   end;


   RegExpr.Expression:=pattern;
   RegExpr.ModifierI:=true;
   logs.ArticaSend('ScanHeaderRegex:: "' + pattern + '" in "' + header_lcase + '" for ' + IntToStr(Header_values.Count) + ' Lines (to ' + Email.ldap_uid + ')');
   for i:=0 to Header_values.Count-1 do begin
       if RegExpr.Exec(Header_values.Strings[i]) then begin
              logs.ArticaSend('ScanHeaderRegex:: Found in "' + header + '" "' + Header_values.Strings[i] + '" string');
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


function TscanQueue.ResendMail(Email:TTMailInfo):boolean;

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

begin
   Result:=false;
 LIST:=TstringList.Create;
 HEAD:=TStringList.Create;
 LOGS:=TLogs.Create;
   if not FileExists(Email.FilePath_source) then begin
    logs.ArticaSend('unable to stat ' + email.FilePath_source);
    exit
 end;

 if not FileExists(email.HeadFilePath) then begin
    logs.ArticaSend('unable to stat ' + email.HeadFilePath);
    exit
 end;


   if not email.InPutMethod then begin
      logs.ArticaSend('INPUT=FALSE');
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


 
 logs.ArticaSend('ResendMail:: INPUT=TRUE');
 
 if email.InputMethodSend=false then begin
     logs.ArticaSend('ResendMail:: InputMethodSend=FALSE message will be deleted');
     DeleteFile(email.FilePath_source);
     DeleteFile(email.HeadFilePath);
     exit(true);
 end;
  logs.ArticaSend('ResendMail:: Load files');
 LIST.LoadFromFile(email.FilePath_source);
 HEAD.LoadFromFile(email.HeadFilePath);
 mail_from:=HEAD.Strings[0];
 mail_to:=HEAD.Strings[1];


     LOGS.ArticaSend('ReleaseMail:: to "' + mail_to + '" from "' + mail_from + '"');

     SMTP := TSMTPSend.Create;
     SMTP.TargetHost:='127.0.0.1';
     SMTP.TargetPort:='29300';
     if not SMTP.Login then begin
        LOGS.ArticaSend('ReleaseMail:: Failed connect to 127.0.0.1 29300 -> ' + SMTP.FullResult.Text);
        ResendMailFailed(email,SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailFrom(mail_from,Length(LIST.Text)) then begin
       LOGS.ArticaSend(SMTP.FullResult.Text);
       ResendMailFailed(email,SMTP.FullResult.Text);
       exit(false);
     end;

     if not SMTP.MailTo(mail_to) then begin
        LOGS.ArticaSend(SMTP.FullResult.Text);
        ResendMailFailed(email,SMTP.FullResult.Text);
        exit(false);
     end;

     if not SMTP.MailData(LIST) then begin
        LOGS.ArticaSend(SMTP.FullResult.Text);
        ResendMailFailed(email,SMTP.FullResult.Text);
        exit(false);
     end;
     LOGS.ArticaSend(SMTP.FullResult.Text);
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
     logs.ArticaSend('reprocess "' + email.HeadFilePath+ '"');
     
     logs.ArticaSend('reprocess "' + email_header_path + '" "' + email_content_path + '"');
     
     shell('/bin/mv ' + email.FilePath + ' ' +  email_content_path);
     shell('/bin/mv ' + email.HeadFilePath + ' ' +  email_header_path);
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
   logs.ArticaSend('CopyFile error ! from ' + SourceFileName + ' ' + TargetFileName);
  Shell('/bin/cp ' + SourceFileName + ' ' + TargetFileName);

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
 logs.ArticaSend('DeleteFIle error ' + path);
 shell('/bin/rm ' + path);
 exit();
 end;
 
  logs.ArticaSend(path + ' deleted');
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
