unit scanQueue;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,strutils,IniFiles,oldlinux,BaseUnix,Logs,RegExpr in 'RegExpr.pas',md5,ldap,mailinfos,
smtpsend,mimemess, mimepart,synautil,db,dnssend,synamisc;

type
  TStringDynArray = array of string;

type

  PostfixMailInfos = record //shared data between component, listen thread and handler
    mail_from:String;
    mail_to:string;
    MessageBody:TstringList;
    recipient_domain:string;
    MX:string;
    source_file_path:string;
  end;

  type
  TscanQueue=class


private
   function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
   function EXTRACT_PARAMETERS(pattern:string):string;
   function ExecPipe(commandline:string):string;
   function  DirFiles(FilePath: string;pattern:string):TstringList;
   procedure CopyFile(SourceFileName, TargetFileName: string);
   procedure DeleteFile(path:string);
   function  Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;


   function  GetFileSize(path:string):longint;
   SOURCE_EMAIL_RELAY_PATH:string;
   USE_DNSMASQ:boolean;
   DirListFiles:TstringList;
   LOGS:Tlogs;
   function  ParseFile(path:string):TTMailInfo;
   function  DSNMASQ_RUN():boolean;
   function  DNSMASQ_PID():string;
   function  SendSMTPNotifications(NotificationMailFrom:string;subject:string;mail_to:string;HtmlBody:string;Email:TTMailInfo):boolean;
   function  PutIntoQuarantine(email:TTMailInfo;uid:string;Subfolder:string):TTMailInfo;

   function ResendMailFailed(Email:TTMailInfo;error:string):boolean;
   function SendPostfixMail(mail:PostfixMailInfos):boolean;

   function WhiteList(Email:TTMailInfo;recipient:string):boolean;
   function ExplodeMailTo(Email:TTMailInfo):boolean;


   //countries deny....
   function ApplyCountryDeny(recipient:string;Email:TTMailInfo):TTMailInfo;
   

   
   function ScanAVMail(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ScanUserRegex(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ScanHeaderRegex(header:string;pattern:string;regex:integer;Email:TTMailInfo):boolean;
   function ResendMail(Email:TTMailInfo):boolean;
   procedure ReleaseMail(email:TTMailInfo);
   function MD5FromString(values:string):string;
   function RetreiveSenderRecipients(Email:TTMailinfo):TTMailinfo;
   function ArticaFilterQueuePath():string;
   function get_ARTICA_PHP_PATH():string;
   Function BuildActionTable(email:TTMailInfo;recipient:string):boolean;


   function ApplyFakedSendAddress(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ApplyRenattach(recipient:string;Email:TTMailInfo):TTMailInfo;
   function ApplyDspam(recipient:string;Email:TTMailInfo):TTMailInfo;

   function IsMailToExist(recipient:string):boolean;
   function DeleteMailTO(recipient:string;Email:TTMailInfo):boolean;
   function email_relay_send(Email:TTMailInfo):boolean;
   function ResolvServers(const DNSHost, Domain: AnsiString; const Servers: TStrings): Boolean;
   function ResolveMXDomain(domain_name:string):string;

   
   MAILTO_LIST:TstringList;
   MAILTO_LIST_ACTION:TstringList;
   QUEUE_PATH:String;
      D:boolean;
public
      constructor Create();
      procedure Free;

      procedure eMailRelay_filter(emailPath:string);
      procedure eMailRelay_scan(emailPath:string);
      procedure ScanIPHeaders(emailPath:string);
      procedure SingleScanPostfix();
      procedure Stats();
      function RemoveMailTo(SourcePath:string;address:string;DestPath:string):string;
      function PostfixReadmail(path:string):PostfixMailInfos;
      function POSFTIX_READ_QUEUE_FILE(queuepath:string;include_source:boolean):TstringList;
      procedure ParsePostfixFiles();

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


procedure TscanQueue.Stats();
var
  FilesList:TstringList;
  c_incoming:integer;
  c_deferred:integer;
  c_active:integer;
  c_hold:integer;
begin
    D:=COMMANDLINE_PARAMETERS('--in-line');


   FilesList:=TstringList.Create;
   FilesList.AddStrings(POSFTIX_READ_QUEUE_FILE('incoming',true));
   c_incoming:=FilesList.Count;
   FilesList.Clear;
   
   
   
   FilesList.AddStrings(POSFTIX_READ_QUEUE_FILE('deferred',true));
   c_deferred:=FilesList.Count;
   FilesList.Clear;
   
   
   FilesList.AddStrings(POSFTIX_READ_QUEUE_FILE('active',true));
   c_active:=FilesList.Count;
   FilesList.Clear;
   
   FilesList.AddStrings(POSFTIX_READ_QUEUE_FILE('hold',true));
   c_hold:=FilesList.Count;
   
   if d then begin
        writeln('incoming:' + IntToStr(c_incoming) + ' active:' +  IntToStr(c_active) + ' deferred:' + IntToStr(c_deferred) + ' hold:' + IntToStr(c_hold));
        exit;
   end;
      

   writeln('incoming...........:',c_incoming);
   writeln('deferred...........:',c_deferred);
   writeln('active.............:',c_active);
   writeln('hold...............:',c_hold);

   exit;
   
end;

procedure TscanQueue.ParsePostfixFiles();

var
   postfix_queue:string;
   FilesList:TstringList;
   filepath:string;
   temp:string;
   JustCount:boolean;
   Mail:PostfixMailInfos;
   i:Integer;
   D:Boolean;
   Watch:integer;
   Fork:boolean;
   backup_path,s_out:string;
begin
   //filepath:=trim(ExecPipe('/usr/sbin/postconf -h queue_directory'));
   JustCount:=False;
   D:=COMMANDLINE_PARAMETERS('debug');
   if not D then D:=COMMANDLINE_PARAMETERS('output');
   Watch:=0;
   
   temp:=EXTRACT_PARAMETERS('--queue-path=(.+)');
   if length(temp)>0 then filepath:=temp;
   temp:='';
   temp:=EXTRACT_PARAMETERS('--just-count');
   if length(temp)>0 then JustCount:=True;
   
   temp:=EXTRACT_PARAMETERS('--watch=([0-9]+)');
   if length(temp)>0 then Watch:=StrToInt(temp);
   
   
    backup_path:=EXTRACT_PARAMETERS('--backup=(.+)');


   fork:=COMMANDLINE_PARAMETERS('--fork');
   
   FilesList:=TstringList.Create;
   writeln('Scanning postfix queue ' + filepath);
   FilesList.AddStrings(POSFTIX_READ_QUEUE_FILE(filepath,true));
   writeln('Success scanning, ' + IntToStr(FilesList.Count) + ' emails in this queue');
   
   if Watch>0 then begin
       if FilesList.Count>Watch then begin
           Shell('/etc/init.d/postfix stop');
           JustCount:=false;
       end;
       
   end;
   
   
   if JustCount then begin
      writeln('Exit forced by token --just-count');
      halt(0);
      exit;
   end;



   for i:=0 to FilesList.Count -1 do begin
       Mail:=PostfixReadmail(FilesList.Strings[i]);
       if D then begin
          writeln('');
          writeln('MASTER ***************************************************************');
          writeln('From.........: ' + mail.mail_from);
          writeln('To...........:'  + mail.mail_to);
          writeln('Domain To....:' +  mail.recipient_domain);
          writeln('MX...........:' +  mail.MX);
          writeln('File.........:' +  FilesList.Strings[i]);
          writeln('Message Size.:' +  IntToStr(length(mail.MessageBody.Text))  + ' bytes');
       end;
          if length(mail.MX)>0 then begin
             try
                if Fork then begin
                    if D then s_out:='output';
                    if D then writeln('Command line .:' + ParamStr(0) + ' --single ' + FilesList.Strings[i] + ' --backup=' + backup_path + ' ' + s_out + ' &');
                   shell(ParamStr(0) + ' --single ' + FilesList.Strings[i] + ' --backup=' + backup_path + ' ' + s_out + '--to-mx=' + mail.MX + ' &');
                end else begin
                    SendPostfixMail(mail);
                end;
             finally
             end;
          end else begin
          if D then writeln('SKIP........: NO MX');
          end;
          
         if d then  writeln('***************************************************************');
         if d then  writeln('');


       
   end;

if Watch>0 then shell('/etc/init.d/postfix start');

halt(0);
exit();

   

end;







//#########################################################################################
procedure TscanQueue.SingleScanPostfix();
var
Mail:PostfixMailInfos;
D:boolean;
MyMx:string;
begin
     D:= COMMANDLINE_PARAMETERS('output');
     writeln('scanning ' + paramStr(2));
     Mail:=PostfixReadmail(paramStr(2));
     MyMx:=EXTRACT_PARAMETERS('--to-mx=(.+)');
     if length(MyMx)>0 then mail.MX:=MyMx;
     if D then begin
          writeln('FORK');
          writeln('***************************************************************');
          writeln('From.........: ' + mail.mail_from);
          writeln('To...........:'  + mail.mail_to);
          writeln('Domain To....:' +  mail.recipient_domain);
          writeln('MX...........:' +  mail.MX);
          writeln('File.........:' +  paramStr(2));
          writeln('Message Size.:' +  IntToStr(length(mail.MessageBody.Text))  + ' bytes');
          writeln('***************************************************************');
     end;
          
     
    if SendPostfixMail(mail) then writeln('SUCCESS !!');
     exit;
     halt(0);
end;



function TscanQueue.PostfixReadmail(path:string):PostfixMailInfos;
var
   LIST:TstringList;
   i:integer;
   commandline:string;
   line:string;
   RegExpr:TRegExpr;
   MessageIntStart:integer;
   MessageEnd:integer;
   D:Boolean;
   post:PostfixMailInfos;
begin
  D:=COMMANDLINE_PARAMETERS('verbose');

  post.MessageBody:=TstringList.Create;
  post.source_file_path:=path;
  
  if not FileExists(path) then exit;
  RegExpr:=TRegExpr.Create;
  if fileexists('/usr/sbin/postcat') then commandline:='/usr/sbin/postcat ' + path + '>/tmp/' + ExtractFileName(path) + ' 2>&1';;
  Shell(commandline);
  LIST:=TstringList.Create;
  if not fileexists('/tmp/' + ExtractFileName(path)) then exit;
  
    LIST.LoadFromFile('/tmp/' + ExtractFileName(path));
    
  For i:=0 to LIST.Count-1 do begin
      RegExpr.Expression:='sender:\s+(.+?)@(.+)';
      if RegExpr.Exec(LIST.Strings[i]) then begin
             post.mail_from:=RegExpr.Match[1]+ '@' + RegExpr.Match[2];

      end;
      
      RegExpr.Expression:='original_recipient:\s+(.+?)@(.+)';
      if RegExpr.Exec(LIST.Strings[i]) then begin
             post.mail_to:=RegExpr.Match[1]+ '@' + RegExpr.Match[2];
             post.recipient_domain:=RegExpr.Match[2];
      end;

     if pos('* MESSAGE CONTENTS',LIST.Strings[i])>0 then MessageIntStart:=i+1;
     if pos('* HEADER EXTRACTED',LIST.Strings[i])>0 then MessageEnd:=i-1;
  end;
  
  for i:=MessageIntStart to MessageEnd do begin
      post.MessageBody.Add(LIST.Strings[i]);
  end;
  post.MX:=ResolveMXDomain(post.recipient_domain);
  DeleteFile('/tmp/' + ExtractFileName(path));
  exit(post);

end;
//#########################################################################################
function TscanQueue.POSFTIX_READ_QUEUE_FILE(queuepath:string;include_source:boolean):TstringList;
const
READ_BYTES = 2048;

Var Info  : TSearchRec;
    Count : Longint;
    path  :string;
    Line:TstringList;
    return_line,queue_source_path:string;
    D:boolean;
    Logs:Tlogs;



Begin

  queue_source_path:=trim(ExecPipe('/usr/sbin/postconf -h queue_directory'));
  Count:=0;
  Line:=TstringList.Create;
  Logs:=TLogs.Create;
  D:=COMMANDLINE_PARAMETERS('debug');
  
  if D then writeln('Postfix queue directory "' +  queue_source_path + '"');


  if include_source then begin
    if length(queuepath)>0  then path:=queue_source_path + '/' + queuepath;
  end else begin

         path:=queuepath;
  end;

  if D then writeln('Scanning Postfix queue directory "' +  path + '"');

  If FindFirst (path+'/*',faAnyFile and faDirectory,Info)=0 then
    begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin

              if Info.Attr=48 then begin
                 Line.AddStrings(POSFTIX_READ_QUEUE_FILE(path + '/' +Info.Name,false));
                 count:=count + Line.Count;
              end;

              if Info.Attr=16 then begin
                 if D then writeln(' -> ' +path + '/' +Info.Name );
                 Line.AddStrings(POSFTIX_READ_QUEUE_FILE(path + '/' +Info.Name,false));
                 count:=count + Line.Count;
              end;

              if Info.Attr=32 then begin
                 Inc(Count);
                 return_line:=path + '/' +Info.Name;
                 Line.Add(return_line);

              end;
              //Writeln (Info.Name:40,Info.Size:15);   postcat -q 3C7F17340B1
         end;
      end;

    Until FindNext(info)<>0;
    end;

  FindClose(Info);
  Logs.logs('POSFTIX_READ_QUEUE_FILE_LIST:: ' + queuepath + ':: ->'  +IntToStr(line.Count) + ' line(s)');
  Logs.free;
  exit(line);
end;



function TscanQueue.ResolveMXDomain(domain_name:string):string;
var
   l:TstringList;
   IPCut:TStringDynArray;
   LOGS:Tlogs;
   s,nameserver,temp:string;
   RegExpr:TRegExpr;
   I,NOTE:Integer;
   ldap:Tldap;
   MyResults:string;
   newip:string;
   Lres:TstringList;
   D:boolean;
   Cache_result:integer;
   etchost:TStringList;
   r_domain_name:string;
   output:boolean;
   resolved_server:string;
begin

  L:=TstringList.create;
  RegExpr:=TRegExpr.Create;
  D:=COMMANDLINE_PARAMETERS('debug');
  output:= COMMANDLINE_PARAMETERS('output');
  if D then output:=true;
  
  
  try
    s := GetDNS;
    l.commatext := s;
    if l.count > 0 then begin
      s := l[0];
      nameserver:=s;
    end;
    finally
    end;
    


    


    nameserver:='127.0.0.1';
    temp:=EXTRACT_PARAMETERS('--nameserver=(.+)');
    if length(temp)>0 then nameserver:=temp;

    if D then writeln('nameserver (' + nameserver + ') --> ' + domain_name);

    Lres:=TstringList.Create;
    RegExpr.Expression:='[0-9]+\.[0-9]+\.[0-9]+\.([0-9]+)';


    if ResolvServers(nameserver,domain_name,Lres) then begin
        if Lres.Count=0 then exit;
        if D then writeln('Resolving ' + domain_name + '=' + Lres.Strings[0] + ' success ');
        if Lres.Count>0 then resolved_server:=Lres.Strings[0];
        r_domain_name:=AnsiReplaceText(resolved_server,'.','\.');
        r_domain_name:=LowerCase(r_domain_name);
        etchost:=TStringList.Create;
        RegExpr.Expression:='^([0-9\.]+)\s+' + r_domain_name;
        if D then writeln('search ' + RegExpr.Expression);
        if FileExists('/etc/hosts') then begin
           if D then writeln('Load /etc/hosts');
           etchost:=TStringList.Create;
           etchost.LoadFromFile('/etc/hosts');
        for i:=0 to etchost.Count-1 do begin
             if D then writeln('scan ... ' + etchost.Strings[i]);
             if RegExpr.Exec(etchost.Strings[i]) then begin
                  if output then writeln('/etc/hosts...:' +  RegExpr.Match[1]);
                  result:=RegExpr.Match[1];
                  RegExpr.free;
                  etchost.free;
                  exit;
             end;
             if D then writeln('not found... try next')

        end;

    end;

    end;

     exit(resolved_server);

end;


//##############################################################################


function TscanQueue.SendPostfixMail(mail:PostfixMailInfos):boolean;

var
   ERROR_REPORTED:TStringlist;
   RECIPIENTS_LIST,mail_to,mail_from:string;
   i,t:integer;
   CommandLine:string;
   RegExpr:TRegExpr;
   LIST:TstringList;
   HEAD:TstringList;
   LOGS:TLogs;
   backup_path:string;
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
 SMTP_PORT:='25';
 cmd_delete:=true;
 D:=COMMANDLINE_PARAMETERS('debug');
 if not D then D:=COMMANDLINE_PARAMETERS('output');
 
 
 if COMMANDLINE_PARAMETERS('--no-delete-source') then cmd_delete:=false;
 if length(mail.MX)=0 then exit(false);

 
 logs.ArticaSend('ResendMail:: Load files');
 mail_from:=mail.mail_from;
 mail_to:=mail.mail_to;

 backup_path:=EXTRACT_PARAMETERS('--backup=(.+)');
 if length(backup_path)=0 then backup_path:='/tmp/postfix-resend-back';
 ForceDirectories(backup_path);
 try
     SMTP := TSMTPSend.Create;
     SMTP.TargetHost:=mail.MX;
     SMTP.TargetPort:=SMTP_PORT;
     temp:='';
     temp:=EXTRACT_PARAMETERS('--timeout=([0-9]+)');
     
     SMTP.Timeout:=10;
     if length(temp)>0 then SMTP.Timeout:=StrToInt(temp);

       if D then  writeln('SMTP Timeout.:',SMTP.Timeout);
    try
     if not SMTP.Login then begin
       if D then  writeln('FAILED.......:' +  SMTP.TargetHost + ':' + SMTP_PORT + ' SMTP_LOGON ('  + SMTP.FullResult.Strings[0]  + ')');
        exit(false);
     end;
     except
      if D then  writeln('FAILED.......:' +  SMTP.TargetHost + ':' + SMTP_PORT + ' SMTP_LOGON ('  + SMTP.FullResult.Strings[0]  + ')');
      exit();
     end;

     if not SMTP.MailFrom(mail_from,Length(mail.MessageBody.Text)) then begin
           if D then  writeln('FAILED.......:' +  SMTP.TargetHost + ':' + SMTP_PORT + ' MAIL_FROM_ERR (' +  SMTP.FullResult.Strings[0]  + ')');
           exit(false);
     end;

     if not SMTP.MailTo(mail_to) then begin
        if D then  writeln('FAILED.......:' +  SMTP.TargetHost + ':' + SMTP_PORT + ' MAIL_TO_ERR (' +  SMTP.FullResult.Strings[0]  + ')');
        exit(false);
     end;

     if not SMTP.MailData(mail.MessageBody) then begin
        if D then  writeln('FAILED.......:' +  SMTP.TargetHost + ':' + SMTP_PORT + ' MAIL_DATA_ERR (' + SMTP.FullResult.Strings[0] + ')');
        exit(false);
     end;

        if D then  writeln('SUCCESS......:' +  SMTP.TargetHost + ':' + SMTP_PORT + ' (' +  SMTP.FullResult.Strings[0] + ')');
    SMTP.Logout;


    if cmd_delete then begin
        if D then  writeln('INFO.........: backup and delete ' + mail.source_file_path);
        CopyFile(mail.source_file_path,backup_path + '/' + ExtractFileName(mail.source_file_path));
        DeleteFile(mail.source_file_path);
     end;
     exit(true);

 finally
 
 end;

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
       if FileExists('/usr/local/bin/bogofilter') then begin
           logs.ArticaSend('BOGOFILTER INSTALLED : learn this spam');
           dspam_temp_path:=ChangeFileExt(email.FilePath_source,'.bogo');
           commandline:='/usr/local/bin/bogofilter  --register-spam --use-syslog --no-config-file --bogofilter-dir=' + email.queue_path + '/bogofilter/users/' +email.ldap_uid + ' --input-file=' + email.FilePath_source + ' >/dev/null 2>&1';;
           logs.ArticaSend(commandline);
           shell(commandline);
    end else begin
            logs.ArticaSend('BOGOFILTER IS NOT INSTALLED : unable to learn this spam');
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

              exit;
           end;
       end;



    end;
    
end;

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
begin
  email.dspam_result:='Unsure';
  email.dspam_class:='Innocent';
  email.dspam_probability:='0.0000';
  email.dspam_confidence:='1.00';
  email.dspam_signature:='N/A';
  result:=Email;
  quue_path:=ArticaFilterQueuePath();


    if not fileExists('/usr/local/bin/bogofilter') then begin
       logs.ArticaSend('BOGOFILTER NOT INSTALLED...');
       exit;
    end;
    logs.ArticaSend('BOGOFILTER INSTALLED...');
    LIST:=TStringList.Create;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='X-Bogosity:\s+([A-Za-z]+),\s+tests=([A-Za-z]+),\s+spamicity=([0-9\.]+)';
    temp_dspam_file:=ChangeFileExt(Email.FilePath_source,'.bogo');
    //temp_dspam_error:=ChangeFileExt(Email.FilePath_source,'.bogo_err');

    
    command_line:='/usr/local/bin/bogofilter --passthrough --ham-true --no-config-file ';
    command_line:=command_line + ' --bogofilter-dir=' + email.queue_path + '/bogofilter/users/' + email.ldap_uid;
    command_line:=command_line + ' --update-as-scored --spam-subject-tag=**SPAM** --use-syslog';
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
              logs.ArticaSend('bogofilter result:=' + email.dspam_result + ' (' + email.dspam_probability + ') message size : ' + IntToStr(messageSize));
              DeleteFile(temp_dspam_file);
              LIST.SaveToFile(email.FilePath_source);
              break;
           end;
        end;



    //DeleteFile(temp_dspam_error);
    
    LIST.Free;
    RegExpr.Free;
    
    exit(email);

end;

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
begin
     LDAP:=TLdap.Create();
     trouve:=false;
          if LDAP.IsWhiteListed(Email.real_mailfrom,recipient) then begin
              logs.ArticaSend('WhiteList::' + recipient + ' as white listed '+Email.real_mailfrom);
              LDAP.Free;
              exit(true);
          end;

          if LDAP.IsWhiteListed(Email.mail_from,recipient) then begin
             logs.ArticaSend('WhiteList::' +recipient + ' as white listed '+Email.mail_from);
              LDAP.Free;
              exit(true);
          end;



    LDAP.Free;
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
  D:=LOGS.COMMANDLINE_PARAMETERS('debug');
  try
    DNS.TargetHost := DNSHost;
    if DNS.DNSQuery(Domain, QType_MX, t) then begin
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
//##############################################################################
function TscanQueue.ExecPipe(commandline:string):string;
const
  READ_BYTES = 2048;
  CR = #$0d;
  LF = #$0a;
  CRLF = CR + LF;

var
  S: TStringList;
  M: TMemoryStream;
  P: TProcess;
  n: LongInt;
  BytesRead: LongInt;
  xRes:string;

begin
  // writeln(commandline);
  M := TMemoryStream.Create;
  BytesRead := 0;
  P := TProcess.Create(nil);
  P.CommandLine := commandline;
  P.Options := [poUsePipes];


  P.Execute;
  while P.Running do begin
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end
    else begin
      Sleep(100);
    end;

  end;

  repeat
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end;
  until n <= 0;
  M.SetSize(BytesRead);
  S := TStringList.Create;
  S.LoadFromStream(M);

  for n := 0 to S.Count - 1 do
  begin
    if length(S[n])>1 then begin

      xRes:=xRes + S[n] +CRLF;
    end;
  end;

  S.Free;
  P.Free;
  M.Free;
  exit( xRes);
end;
//#############################################################################

end.
