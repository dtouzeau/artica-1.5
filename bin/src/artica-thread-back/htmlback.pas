unit htmlback;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,mimemess, mimepart,articaldap,global_conf,artica_mysql;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  Thtmlback=class


private
     LOGS          :Tlogs;
     artica_path, attachmentdir,attachmenturl,fullmessagesdir:string;
     hookpath      :string;
     MessageID     :string;
     SYS           :Tsystem;
     HEADERS       :TstringList;
     QUEUE_LIST    :TstringList;
     Mime          :TMimeMess;
     RegExpr       :TRegExpr;
     mailfrom      :string;
     GLOBAL_SUBJECT:string;
     Recipients    :TstringList;
     ldap          :Tarticaldap;
     procedure     AddRecipient(email:string);
     GLOBAL_INI    :myconf;
     SenderAsRule  :integer;
     mysql         :Tartica_mysql;
     function      PosixExpr(expr:string):string;
     globalCommands:string;
     Organization:string;

     function      VerifSenderRules(sender:string;Recipients:TstringList;subject:string):boolean;
     function      SenderMath(sender:string;rules:TstringList;Recipients:TstringList;subject:string):boolean;
     function      VerifRecipientRules(sender:string;Recipient:string;subject:string):boolean;
     function      RunBackup(message_path:string):boolean;
     procedure     ScanRecipts(filepath:string);
     procedure     ScanStats(filepath:string);
     procedure     ScanWhite(filepath:string);
     procedure     SaveQuarantine(DirectoryPath:string);


     procedure     ScanGenericQuar(filepath:string);
     procedure     GenericQuarConvertHTML(message_path:string);

public
    procedure   Free;
    constructor Create();
    function   ScanQueue(filepath:string):boolean;

END;

implementation
//##############################################################################
procedure thtmlback.free();
begin
    logs.Free;
    SYS.Free;
    ldap.free;
    Mime.free;
end;
//##############################################################################

constructor Thtmlback.Create();
var
   i:integer;
   s:string;
   queuepath:string;
   QRegExpr:TRegExpr;
   count:integer;
begin

       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       Mime:=TMimeMess.Create;
       RegExpr:=TRegExpr.Create;
       Recipients:=TstringList.Create;
       ldap:=Tarticaldap.Create;
       GLOBAL_INI:=myconf.Create;
       QUEUE_LIST:=TstringList.Create;
       mysql:=Tartica_mysql.Create;
       if not mysql.Connected then exit;
       count:=0;
       s:='';
       
       
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
 globalCommands:=s;
 RegExpr.Expression:='--path=(.+?)\s+';
 attachmentdir:='/opt/artica/share/www/attachments';
 fullmessagesdir:='/opt/artica/share/www/original_messages';
 attachmenturl:='images.listener.php?mailattach=';
 
 forceDirectories(fullmessagesdir);


 if not FileExists('/usr/bin/mhonarc') then exit;
       
       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
      
  if not ldap.Logged then begin
      LOGS.Syslogs('WARNING! LDAP connection error...');
      LOGS.Debuglogs('create():: LDAP connection error...' );
      exit;
  end;
  
  
  SYS.DirFiles('/opt/artica/mimedefang-hooks/backup-queue','*');
  QUEUE_LIST.Clear;
  if SYS.DirListFiles.Count>0 then begin
     QUEUE_LIST.AddStrings(SYS.DirListFiles);

     for i:=0 to QUEUE_LIST.Count-1 do begin
        LOGS.Syslogs('<' + QUEUE_LIST.Strings[i]+'> in backup process ( file ' + IntTostr(i)+'/' + IntToStr(QUEUE_LIST.Count)+')');
        ScanQueue('/opt/artica/mimedefang-hooks/backup-queue/' + QUEUE_LIST.Strings[i]);
     end;
  end;



  SYS.DirListFiles.Clear;
  QUEUE_LIST.Clear;
  SYS.DirFiles('/opt/artica/mimedefang-hooks/stats','*');
  
LOGS.Debuglogs('Scanning MimeDefang statistiques ' + IntToStr(SYS.DirListFiles.Count) + ' file(s) in queue');
  if SYS.DirListFiles.Count>0 then begin
     QUEUE_LIST.AddStrings(SYS.DirListFiles);
      for i:=0 to QUEUE_LIST.Count-1 do begin
        ScanStats('/opt/artica/mimedefang-hooks/stats/' + QUEUE_LIST.Strings[i]);
     end;
  end;


  SYS.DirListFiles.Clear;
  QUEUE_LIST.Clear;
  QRegExpr:=TRegExpr.Create;
  count:=0;
  SYS.DirDir('/var/spool/MIMEDefang');
  LOGS.Debuglogs('Scanning MimeDefang Quarantine ' + IntToStr(SYS.DirListFiles.Count) + ' directorie(s) in queue');
  QRegExpr.Expression:='qdir-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)';
  if SYS.DirListFiles.Count>0 then begin
      for i:=0 to SYS.DirListFiles.Count-1 do begin
         queuepath:='/var/spool/MIMEDefang/' +SYS.DirListFiles.Strings[i];
         if QRegExpr.Exec(queuepath) then begin
            SaveQuarantine(queuepath);
            inc(count);
         end;
         if count>10 then break;
     end;
  end;
  
  SYS.DirListFiles.Clear;
  QUEUE_LIST.Clear;
  SYS.DirFiles('/opt/artica/mimedefang-hooks/white','*.sql');
  LOGS.Debuglogs('Scanning MimeDefang auto-white list ' + IntToStr(SYS.DirListFiles.Count) + ' file(s) in queue');
 if SYS.DirListFiles.Count>0 then begin
     QUEUE_LIST.AddStrings(SYS.DirListFiles);
      for i:=0 to QUEUE_LIST.Count-1 do begin
        ScanWhite('/opt/artica/mimedefang-hooks/white/' + QUEUE_LIST.Strings[i]);
     end;
  end;
  
  
 { SYS.DirListFiles.Clear;
  QUEUE_LIST.Clear;
  SYS.DirFiles('/var/spool/jchkmail','*.virus');
  LOGS.Debuglogs('Scanning jchkmail virus quarantine ' + IntToStr(SYS.DirListFiles.Count) + ' file(s) in queue');
  if SYS.DirListFiles.Count>0 then begin
     QUEUE_LIST.AddStrings(SYS.DirListFiles);
      for i:=0 to QUEUE_LIST.Count-1 do begin
        ScanGenericQuar('/var/spool/jchkmail/' + QUEUE_LIST.Strings[i]);
     end;
  end;
  
        }

ldap.Free;
halt(0);

      
end;
//##############################################################################
procedure thtmlback.ScanRecipts(filepath:string);
var
i:Integer;
l:TstringList;
begin
 if FileExists('/opt/artica/mimedefang-hooks/rcpt-queue/' + ExtractFileName(filepath)) then begin
      l:=TstringList.Create;
      l.LoadFromFile('/opt/artica/mimedefang-hooks/rcpt-queue/' + ExtractFileName(filepath));
      for i:=0 to l.count-1 do begin
              AddRecipient(trim(l.Strings[i]));
      end;
 end;

end;
//##############################################################################
procedure thtmlback.ScanGenericQuar(filepath:string);
var
i:Integer;
l:TstringList;
begin
 if FileExists(filepath) then begin
    GenericQuarConvertHTML(filepath);
 end;

end;
//##############################################################################



procedure thtmlback.ScanWhite(filepath:string);
var sqlcom:string;

begin
 sqlcom:=LOGS.ReadFromFile(filepath);
 if length(sqlcom)=0 then exit;
 if mysql.QUERY_SQL(pChar(sqlcom),'artica_backup') then begin
     LOGS.DeleteFile(filepath);
  end else begin
     LOGS.Debuglogs('ScanWhite(): FAILED parsing "'+filepath+'"');
  end;
end;
//##############################################################################
procedure thtmlback.ScanStats(filepath:string);
var
   stats:TiniFile;
   sql:string;
   mailfrom_domain:string;
   mailto:string;
   mailto_domain:string;
   usersdata:users_datas;
begin
  if not FileExists(filepath) then exit;
  stats:=TinIFile.Create(filepath);
  LOGS.Debuglogs('ScanStats(): Parsing ' + FilePath);
  RegExpr.Expression:='.+?@(.+)';
   
   mailto:=stats.ReadString('STATS','TO','');
   
   usersdata:=ldap.UserDataFromMail(mailto);
   if length(usersdata.mail)>0 then mailto:=usersdata.mail;

   
   if RegExpr.Exec(stats.ReadString('STATS','FROM','')) then mailfrom_domain:=RegExpr.Match[1];
   if RegExpr.Exec(mailto) then mailto_domain:=RegExpr.Match[1];
  
  sql:='INSERT INTO mails_events (mailfrom,rcpt_to,zDate,mailfrom_domain,rcpt_to_domain,relayhost) VALUES(';
  sql:=sql + '"' + stats.ReadString('STATS','FROM','unknown') +'","'+stats.ReadString('STATS','TO','unknown')+'","'+stats.ReadString('STATS','TIME','');
  sql:=sql + '","' +  mailfrom_domain  +'","'+mailto_domain + '","'+stats.ReadString('STATS','IP','0.0.0.0') +'")';
  LOGS.Debuglogs('ScanStats(): SQL "' + sql + '"');
  if mysql.QUERY_SQL(pChar(sql),'artica_events') then begin
     GLOBAL_INI.DeleteFile(filepath);
  end else begin
     LOGS.Debuglogs('ScanStats(): FAILED');
  
  end;

end;
//##############################################################################


procedure thtmlback.GenericQuarConvertHTML(message_path:string);
var
ConvertedPath:string;
XMime:TMimeMess;
cmd:string;
msgid:string;
Zdate:string;
SUBJECT:string;
begin
   ConvertedPath:=ExtractFilePath(message_path)+ ExtractFileName(message_path) + '.html';
   logs.Debuglogs('GenericQuarConvertHTML():: Converting in ' + ConvertedPath );
   cmd:='/usr/bin/mhonarc ';
   cmd:=cmd+'-attachmentdir ' + attachmentdir + ' ';
   cmd:=cmd+'-attachmenturl ' + attachmenturl + ' ';
   cmd:=cmd+'-nodoc ';
   cmd:=cmd+'-nofolrefs ';
   cmd:=cmd+'-nomsgpgs ';
   cmd:=cmd+'-nospammode ';
   cmd:=cmd+'-nosubjectthreads ';
   cmd:=cmd+'-idxfname storage ';
   cmd:=cmd+'-nosubjecttxt "no subject" ';
   cmd:=cmd+'-single ';
   cmd:=cmd + message_path + ' ';
   cmd:=cmd + '>'+ConvertedPath;
   
 XMime:=TMimeMess.Create;
 XMime.Lines.LoadFromFile(message_path);
 XMime.DecodeMessage;
 SUBJECT:=XMime.Header.Subject;
 
   
   fpsystem(cmd);
   
end;





procedure thtmlback.SaveQuarantine(DirectoryPath:string);
var
   SENDER,SENDER_DOMAIN,cmd,sql:string;
   xRECIPIENTS:TstringList;
   HEADERS:string;
   ENTIRE_MESSAGE:string;
   HTML_MESSAGE:string;
   XMime:TMimeMess;
   msgid:string;
   Zdate:string;
   SUBJECT:string;
   message_path:string;
   ok:boolean;
   i:integer;
begin
 LOGS.Debuglogs('Scanning Quarantine ' + DirectoryPath);
 SENDER:=GLOBAL_INI.ReadFileIntoString(DirectoryPath + '/SENDER');
 message_path:=DirectoryPath + '/ENTIRE_MESSAGE';
 ok:=false;
 
 msgid:=trim(GLOBAL_INI.ReadFileIntoString(DirectoryPath + '/SENDMAIL-QID'));
 HEADERS:=trim(GLOBAL_INI.ReadFileIntoString(DirectoryPath + '/HEADERS'));
 ENTIRE_MESSAGE:=GLOBAL_INI.ReadFileIntoString(DirectoryPath + '/ENTIRE_MESSAGE');
 
 if not FileExists(DirectoryPath + '/RECIPIENTS') then begin
    logs.Debuglogs('SaveQuarantine():: unable to stat ' + DirectoryPath + '/RECIPIENTS');
    exit;
 end;
 
 
 xRECIPIENTS:=TStringList.Create;
 xRECIPIENTS.LoadFromFile(DirectoryPath + '/RECIPIENTS');
 RegExpr.Expression:='<(.+?)>';
 if RegExpr.Exec(SENDER) then SENDER:=RegExpr.Match[1];
 for i:=0 to xRECIPIENTS.Count-1 do begin
       if RegExpr.Exec(xRECIPIENTS.Strings[i]) then xRECIPIENTS.Strings[i]:=RegExpr.Match[1];
 end;
 

 RegExpr.Expression:='qdir-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)\.([0-9]+)\.([0-9]+)-';
 if RegExpr.Exec(DirectoryPath) then Zdate:=RegExpr.Match[1]+'-'+RegExpr.Match[2]+'-'+ RegExpr.Match[3] + ' ' + RegExpr.Match[4] +':' + RegExpr.Match[5]+ ':' + RegExpr.Match[6];
 RegExpr.Expression:='.+?@(.+)';
 RegExpr.Exec(SENDER);
 SENDER_DOMAIN:=RegExpr.Match[1];
 
 
 XMime:=TMimeMess.Create;
 XMime.Lines.LoadFromFile(message_path);
 XMime.DecodeMessage;
 SUBJECT:=XMime.Header.Subject;
 
   cmd:='/usr/bin/mhonarc ';
   cmd:=cmd+'-attachmentdir ' + attachmentdir + ' ';
   cmd:=cmd+'-attachmenturl ' + attachmenturl + ' ';
   cmd:=cmd+'-nodoc ';
   cmd:=cmd+'-nofolrefs ';
   cmd:=cmd+'-nomsgpgs ';
   cmd:=cmd+'-nospammode ';
   cmd:=cmd+'-nosubjectthreads ';
   cmd:=cmd+'-idxfname storage ';
   cmd:=cmd+'-nosubjecttxt "no subject" ';
   cmd:=cmd+'-single ';
   cmd:=cmd + message_path + ' ';
   cmd:=cmd + '>/opt/artica/tmp/' + ExtractFileName(message_path) + '.html';
 
  logs.Debuglogs('SaveQuarantine():: Export to html ' + message_path);
  fpsystem(cmd);

  HTML_MESSAGE:=GLOBAL_INI.ReadFileIntoString('/opt/artica/tmp/' + ExtractFileName(message_path) + '.html');
  GLOBAL_INI.DeleteFile('/opt/artica/tmp/' + ExtractFileName(message_path) + '.html');
 
 sql:='INSERT INTO quarantine(`MessageID`,`zDate`,`mailfrom`,`mailfrom_domain`,`subject`,`MessageBody`,`fullmesg`,`header`) ';
 sql:=sql+'VALUES("'+mysql.GetAsSQLText(msgid)+'","'+Zdate+'","'+SENDER+'","'+SENDER_DOMAIN+'","'+mysql.GetAsSQLText(SUBJECT)+'","'+mysql.GetAsSQLText(HTML_MESSAGE)+'","'+mysql.GetAsSQLText(XMime.Lines.Text)+'",';
 sql:=sql+'"'+mysql.GetAsSQLText(HEADERS)+'");';
 
 if not mysql.QUERY_SQL(pChar(sql),'artica_backup') then begin
    logs.Debuglogs('SaveQuarantine():: Failed query on mysql...');
    exit;
 end;
 
 
 for i:=0 to xRECIPIENTS.Count-1 do begin
    sql:='INSERT INTO storage_recipients (MessageID,recipient) VALUES("'+ mysql.GetAsSQLText(msgid)+'","'+xRECIPIENTS.Strings[i]+'")';
    if mysql.QUERY_SQL(pChar(sql),'artica_backup') then ok:=true else ok:=false;
 end;

if ok then begin
    logs.Debuglogs('SaveQuarantine():: Delete quarantine "'+DirectoryPath+'"');
    fpsystem('/bin/rm -rf ' +DirectoryPath);
end;

 
 
end;
//##############################################################################

function thtmlback.ScanQueue(filepath:string):boolean;
var
   i:integer;
   l:TstringList;
   rcpt_path:string;
begin
  if not FileExists(filepath) then begin
     LOGS.Syslogs('WARNING! Unable to stat scanned file ' + filepath);
     exit;
  end;
  result:=false;
  LOGS.Debuglogs('---------------------------------------------');
  HEADERS:=TstringList.Create;
  HEADERS.Clear;
  HEADERS.LoadFromFile(filepath);
  LOGS.Debuglogs('ScanQueue():: parsing file ' + filepath);
  Mime.Clear;
  Mime.Lines.AddStrings(HEADERS);
  Mime.DecodeMessage;
  rcpt_path:='/opt/artica/mimedefang-hooks/rcpt-queue/' + ExtractFileName(filepath);

     RegExpr.Expression:='<(.*?)>';
     if RegExpr.Exec(Mime.Header.From) then begin
        mailfrom:=RegExpr.Match[1];
     end else begin
        mailfrom:=Mime.Header.From;
     end;

  
     LOGS.Debuglogs('ScanQueue():: From: ' + mailfrom );

     GLOBAL_SUBJECT:=Mime.Header.Subject;
     LOGS.Debuglogs('ScanQueue():: Subject: ' + GLOBAL_SUBJECT );

  
     MessageID:=Mime.Header.MessageID;
     LOGS.Debuglogs('ScanQueue():: MessageID: ' + Mime.Header.MessageID );
     

     ScanRecipts(filepath);


     for i:=0 to mime.Header.ToList.Count-1 do begin
        AddRecipient(mime.Header.ToList.Strings[i]);
     end;


     for i:=0 to mime.Header.CCList.Count-1 do begin
        AddRecipient(mime.Header.CCList.Strings[i]);
     end;
  
     LOGS.Debuglogs('ScanQueue():: To,bcc List:=' + IntToStr(Recipients.Count));
     LOGS.Syslogs('<' + Mime.Header.MessageID + '> from=<'+mailfrom+'> to ' + IntToStr(Recipients.Count) + ' recipient(s)');
     
     
     if VerifSenderRules(mailfrom,Recipients,GLOBAL_SUBJECT) then begin
         RunBackup(filepath);
         exit;
     end;

     for i:=0 to Recipients.Count-1 do begin
          if VerifRecipientRules(mailfrom,Recipients.Strings[i],GLOBAL_SUBJECT) then begin
           if RunBackup(filepath) then begin
              GLOBAL_INI.DeleteFile(filepath);
              GLOBAL_INI.DeleteFile(rcpt_path);
              exit;
           end;
          end;
     end;
  
   GLOBAL_INI.DeleteFile(filepath);
         GLOBAL_INI.DeleteFile(rcpt_path);


LOGS.Debuglogs('ScanQueue():: finish die...');
LOGS.Debuglogs('---------------------------------------------');
end;

//##############################################################################
function thtmlback.RunBackup(message_path:string):boolean;
var
   cmd:string;
   sqldate:string;
   l:TstringList;
   Formated:string;
   sql:string;
   i:Integer;
   ok:boolean;
   rcpt_path:string;
   RegExpr:TRegExpr;
   recipient_domain:string;
   FullMessage_path:string;

begin
   result:=false;
   ok:=true;
   forceDirectories(attachmentdir);
   rcpt_path:='/opt/artica/mimedefang-hooks/rcpt-queue/' + ExtractFileName(message_path);

   if not FileExists('/usr/bin/mhonarc') then begin
      logs.Syslogs('Fatal error, unable to stat /usr/bin/mhonarc');
      exit(false);
   end;
   
   LOGS.Debuglogs('RunBackup()::' + MessageID + '');
   
   cmd:='/usr/bin/mhonarc ';
   cmd:=cmd+'-attachmentdir ' + attachmentdir + ' ';
   cmd:=cmd+'-attachmenturl ' + attachmenturl + ' ';
   cmd:=cmd+'-nodoc ';
   cmd:=cmd+'-nofolrefs ';
   cmd:=cmd+'-nomsgpgs ';
   cmd:=cmd+'-nospammode ';
   cmd:=cmd+'-nosubjectthreads ';
   cmd:=cmd+'-idxfname storage ';
   cmd:=cmd+'-nosubjecttxt "no subject" ';
   cmd:=cmd+'-single ';
   cmd:=cmd + message_path + ' ';
   cmd:=cmd + '>/opt/artica/tmp/' + ExtractFileName(message_path) + '.html';
   
    RegExpr:=TRegExpr.Create;
    LOGS.Debuglogs('RunBackup()::' + cmd + '');
    fpsystem(cmd);
    
    

    sqldate:=FormatDateTime('YYYY-MM-DD hh:nn:ss',Mime.Header.Date);
    LOGS.Debuglogs('RunBackup():: Message on ' + sqldate + ' <' + mailfrom + '> "' + GLOBAL_SUBJECT +'"');


    FullMessage_path:= fullmessagesdir+'/'+ExtractFileName(message_path)+'.eml';
    if not FileExists(FullMessage_path) then begin
       logs.OutputCmd('/bin/cp '+message_path+' '+ FullMessage_path);
    end;
    
    
    l:=TstringList.Create;
    if not FileExists('/opt/artica/tmp/' + ExtractFileName(message_path) + '.html') then exit(true);
    l.LoadFromFile('/opt/artica/tmp/' + ExtractFileName(message_path) + '.html');
    GLOBAL_INI.DeleteFile('/opt/artica/tmp/' + ExtractFileName(message_path) + '.html');
    GLOBAL_SUBJECT:=mysql.GetAsSQLText(GLOBAL_SUBJECT);
    Formated:=mysql.GetAsSQLText(l.Text);
    mailfrom:=mysql.GetAsSQLText(mailfrom);
    MessageID:=mysql.GetAsSQLText(MessageID);
    sql:='INSERT INTO storage (MessageID,zDate,mailfrom,subject,MessageBody,organization) VALUES ("'+ MessageID+'","'+sqldate+'","'+mailfrom+'","'+GLOBAL_SUBJECT+'","'+ Formated+'","'+Organization+'")';
    if mysql.QUERY_SQL(pChar(sql),'artica_backup') then begin
       for i:=0 to Recipients.Count-1 do begin
         LOGS.Debuglogs('RunBackup()::storage_recipients=' +Recipients.Strings[i]);
         RegExpr.Expression:='(.+?)@(.+)';
         if RegExpr.Exec(Recipients.Strings[i]) then recipient_domain:=RegExpr.Match[2];
         
         sql:='INSERT INTO storage_recipients (MessageID,recipient,recipient_domain) VALUES("'+ MessageID+'","'+Recipients.Strings[i]+'","'+recipient_domain+'")';
         if mysql.QUERY_SQL(pChar(sql),'artica_backup') then ok:=true else ok:=false;
       end;
    end;
    
    
    
    if ok then begin
          if FileExists(FullMessage_path) then begin
             sql:='INSERT INTO orgmails (MessageID,message_path,MessageSize) VALUES("'+ MessageID+'","'+FullMessage_path+'","'+IntToStr(logs.GetFileBytes(FullMessage_path))+'")';
          if mysql.QUERY_SQL(pChar(sql),'artica_backup') then
               logs.Syslogs('<'+MessageID+'> success adding original message into database...');
          end else begin
               logs.Syslogs('<'+MessageID+'> failed adding original message into database...');
          end;
       LOGS.Syslogs('<' + MessageID + '> from=<'+mailfrom+'> successfully backuped (html version)');
       LOGS.Debuglogs('RunBackup()::TRUE... (delete ' + message_path + ')');
       GLOBAL_INI.DeleteFile(message_path);
    end else begin
        LOGS.Syslogs('<' + MessageID + '> from=<'+mailfrom+'> backup error..');
        LOGS.Debuglogs('RunBackup()::FALSE...');
    end;
    
    exit(ok);
   
end;
//##############################################################################

procedure thtmlback.AddRecipient(email:string);
var
   i:Integer;
   RegexR:TRegExpr;
begin
     RegexR:=TRegExpr.Create;
     RegexR.Expression:='<(.+?)>';
     if RegexR.Exec(email) then begin
           email:=RegexR.Match[1];
     end;
     
     
     RegexR.free;
     for i:=0 to Recipients.Count-1 do begin
         if LowerCase(email)=Recipients.Strings[i] then exit;
     
     end;

   Recipients.Add(LowerCase(email));


end;
//##############################################################################
function thtmlback.VerifSenderRules(sender:string;Recipients:TstringList;subject:string):boolean;
var
   i:integer;
   sizei:integer;
   f:users_datas;
begin
    result:=false;
    LOGS.Debuglogs('VerifSenderRules()::' + MessageID + ' Verify if sender has rules');
    f:=ldap.UserDataFromMail(sender);
    if f.HtmlBackup.BackupEnabled='1' then begin
        if f.HtmlBackup.ArticaBackupRules.Count>0 then begin
           LOGS.Debuglogs('ScanQueue()::' + MessageID + ' Sender has rules...');
           if SenderMath(sender,f.HtmlBackup.ArticaBackupRules,recipients,subject) then begin
               LOGS.Debuglogs('ScanQueue()::' + MessageID + ' Sender has rules matching...');
               Organization:=f.Organization;
               exit(true);
           end;
        end;
    end;
end;
//##############################################################################
function thtmlback.VerifRecipientRules(sender:string;Recipient:string;subject:string):boolean;
var
   i:integer;
   sizei:integer;
   f:users_datas;
   RegExpr2:TRegExpr;
   expr:string;
   expr2:string;
   expr3:string;
begin
  result:=false;
  RegExpr2:=TRegExpr.Create;
  RegExpr.Expression:='<f>(.*)</f><t>(.*)</t><s>(.*)</s>';
    LOGS.Debuglogs('VerifRecipientRules()::' + MessageID + ' Verify if Recipient (' + Recipient + ') has rules');
    f:=ldap.UserDataFromMail(Recipient);
    if f.HtmlBackup.BackupEnabled<>'1' then exit;
    if f.HtmlBackup.ArticaBackupRules.Count=0 then exit;

    LOGS.Debuglogs('ScanQueue()::' + MessageID + ' recipient has rules... organization=' + f.Organization);
   
    for i:=0 to f.HtmlBackup.ArticaBackupRules.Count-1 do begin
        if RegExpr.Exec(f.HtmlBackup.ArticaBackupRules.Strings[i]) then begin
                  LOGS.Debuglogs('ScanQueue()::' + MessageID + ' verify rule ' + f.HtmlBackup.ArticaBackupRules.Strings[i]);
                  expr:=PosixExpr(RegExpr.Match[1]);
                  expr2:=PosixExpr(RegExpr.Match[2]);
                  expr3:=PosixExpr(RegExpr.Match[3]);
                  RegExpr2.Expression:=expr;
                  if RegExpr2.Exec(sender) then begin
                     logs.Debuglogs('VerifRecipientRules():' + MessageID + ' ' + sender + ' match ' + RegExpr2.Expression);
                     RegExpr2.Expression:=expr2;
                     if RegExpr2.Exec(Recipient) then begin
                        logs.Debuglogs('VerifRecipientRules():' + MessageID + ' ' + Recipient + ' match ' + RegExpr2.Expression);
                        RegExpr2.Expression:=expr3;
                        if RegExpr2.Exec(subject) then begin
                           logs.Debuglogs('VerifRecipientRules():' + MessageID + ' ' + subject + ' match ' + RegExpr2.Expression);
                           Organization:=f.Organization;
                           exit(true);
                        end;
                     end;
                  end;
              end;
          end;
   
 RegExpr2.free;
   
   

end;
//##############################################################################



function thtmlback.SenderMath(sender:string;rules:TstringList;Recipients:TstringList;subject:string):boolean;
var
   i:integer;
   z:integer;
   RegExpr2:TRegExpr;
   expr:string;
   expr2:string;
   expr3:string;
begin
  result:=false;
  RegExpr2:=TRegExpr.Create;
  RegExpr.Expression:='<f>(.*)</f><t>(.*)</t><s>(.*)</s>';
  
         for i:=0 to rules.Count-1 do begin
              if RegExpr.Exec(rules.Strings[i]) then begin
                   expr:=PosixExpr(RegExpr.Match[1]);
                  expr2:=PosixExpr(RegExpr.Match[2]);
                  expr3:=PosixExpr(RegExpr.Match[3]);

                  if length(expr)=0 then expr:='.*';
                  if length(expr2)=0 then expr2:='.*';
                  if length(expr3)=0 then expr3:='.*';
                  
                  RegExpr2.Expression:=expr;
                  if RegExpr2.Exec(sender) then begin
                     logs.Debuglogs('SenderMath(): ' + sender + ' match ' + RegExpr2.Expression);
                      RegExpr2.Expression:=expr2;
                       for z:=0 to Recipients.Count-1 do begin
                          if RegExpr2.Exec(Recipients.Strings[i]) then begin
                             logs.Debuglogs('SenderMath():' + MessageID + ' ' + Recipients.Strings[i] + ' match ' + RegExpr2.Expression);
                             RegExpr2.Expression:=expr3;
                             if RegExpr2.Exec(subject) then begin
                                logs.Debuglogs('SenderMath():' + MessageID + ' ' + subject + ' match ' + RegExpr2.Expression);
                                exit(true);
                             end;
                          end;
                       
                       end;
                  end;
              end;
         end;
RegExpr2.free;
end;
//##############################################################################

function thtmlback.PosixExpr(expr:string):string;
begin
   if length(expr)=0 then exit('.*');
   
    expr:=AnsiReplaceText(expr,'.','\.');
    expr:=AnsiReplaceText(expr,'*','.*');
    result:=expr;
end;


end.
