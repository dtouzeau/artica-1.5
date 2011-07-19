unit bogom_parse;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,
    logs in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/logs.pas',unix,
    RegExpr in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas',
    articaldap in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-ldap/articaldap.pas',
    mimemess in '/home/dtouzeau/developpement/artica-postfix/bin/src/mimemessage_src/mimemess.pas',
    artica_mysql,
    mimepart in '/home/dtouzeau/developpement/artica-postfix/bin/src/mimemessage_src/mimepart.pas';
type

  mailrecord=record
        mailfrom:string;
        mailto:TstringList;
        subject:string;
        messageID:string;
        MessageDate:string;
        OriginalMessage:TstringList;
        HtmlMessage:string;
        message_path:string;
        
        
  end;

  type
  tmailarchive=class


private
     LOGS          :Tlogs;
     artica_path   :string;
     SYS           :Tsystem;
     mysql         :Tartica_mysql;
     ldap          :Tarticaldap;
     globalCommands:string;
     function       DecodeMessage(message_path:string):mailrecord;
     function       DecodeMailAddr(source:string):string;
     function       SaveToMysql(msg:mailrecord):boolean;
     attachmentdir  :string;
     fullmessagesdir:string;
     attachmenturl  :string;
     function       TransFormToHtml(message_path:string):string;
     procedure      ScanHeaders();


public
    procedure   Free;
    procedure  ParseQueue();
    constructor Create();
END;

implementation

constructor tmailarchive.Create();
var
   i:integer;
   s:string;
begin

       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       ldap:=Tarticaldap.Create;
       s:='';

 if not FileExists('/usr/bin/mhonarc') then begin
     LOGS.Syslogs('FATAL ERROR: Unable to stat /usr/bin/mhonarc');
     halt(0);
 end;
 
 
 
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
 
 
 globalCommands:=s;
 
 attachmentdir:='/opt/artica/share/www/attachments';
 fullmessagesdir:='/opt/artica/share/www/original_messages';
 attachmenturl:='images.listener.php?mailattach=';
 
 

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

end;
//##############################################################################
procedure tmailarchive.free();
begin
    logs.Free;
    SYS.Free;
    ldap.free;
end;
//##############################################################################
procedure tmailarchive.ParseQueue();
var
   l:TstringList;
   i:integer;
   TargetFile:string;
   msg:mailrecord;
   performed:boolean;

begin

    l:=TstringList.Create;
    l:=SYS.DirFiles('/tmp/savemail','*.msg');
    performed:=false;
    logs.Debuglogs('processing ' + IntToStr(l.Count) + ' dump mail(s)');
    
    for i:=0 to l.Count-1 do begin
        TargetFile:='/tmp/savemail/'+ l.Strings[i];
        logs.Debuglogs('processing '+ TargetFile);
        

        try
         msg:=DecodeMessage(TargetFile);
        except
         logs.syslogs('FATAL ERROR ON decoding file '+TargetFile);
         continue;
        end;

        try
         performed:=SaveToMysql(msg);
        except
         logs.syslogs('FATAL ERROR ON processing mysql database on file '+TargetFile);
         continue;
        end;

        
        if performed then begin
           logs.Debuglogs(TargetFile+' success backuped');
           logs.DeleteFile(TargetFile);
        end;
        
    end;

end;
//##############################################################################
function tmailarchive.SaveToMysql(msg:mailrecord):boolean;
var
   mssql:Tartica_mysql;
   subject:string;
   html:string;
   mailfrom:string;
   sql:string;
   f:users_datas;
   Organization:string;
   recipient_domain:string;
   MessageID,FullMessage_path:string;
   i:integer;
   RegExpr:TRegExpr;
   GetFileBytes:string;

begin

    mssql:=Tartica_mysql.Create;
    f:=ldap.UserDataFromMail(msg.mailfrom);
    result:=false;
    subject:=mssql.GetAsSQLText(msg.subject);
    html:=mssql.GetAsSQLText(msg.HtmlMessage);
    mailfrom:=mssql.GetAsSQLText(msg.mailfrom);
    MessageID:=mssql.GetAsSQLText(msg.messageID);
    Organization:=f.Organization;
    FullMessage_path:=fullmessagesdir+'/'+logs.MD5FromString(msg.messageID+msg.message_path)+'.eml';
    try

       msg.OriginalMessage.SaveToFile(FullMessage_path);
   except
          logs.Syslogs('Fatal error while writing file ' + FullMessage_path);
          exit;
   end;


GetFileBytes:=IntToStr(logs.GetFileBytes(FullMessage_path));
sql:='INSERT INTO storage (MessageID,zDate,mailfrom,subject,MessageBody,organization) ';
sql:=sql+'VALUES ("'+ MessageID+'","'+msg.MessageDate+'","'+mailfrom+'","'+subject+'","'+ html+'","'+Organization+'")';


    if not mssql.QUERY_SQL(pChar(sql),'artica_backup') then begin
         logs.Syslogs('warning Unable to backup ' + MessageID + ' message in storage table, mysql error, try later time');
         exit(false);
    end;
    
    
    sql:='INSERT INTO orgmails (MessageID,message_path,MessageSize) VALUES("'+ MessageID+'","'+FullMessage_path+'","'+GetFileBytes+'")';
    if not mssql.QUERY_SQL(pChar(sql),'artica_backup') then begin
         logs.Syslogs('warning Unable to backup ' + MessageID + ' message in orgmails table, mysql error, try later time');
         exit(false);
    end;
    
    
    RegExpr:=TRegExpr.Create;
    for i:=0 to msg.mailto.Count-1 do begin
         RegExpr.Expression:='(.+?)@(.+)';
         if RegExpr.Exec(msg.mailto.Strings[i]) then recipient_domain:=RegExpr.Match[2];

         sql:='INSERT INTO storage_recipients (MessageID,recipient,recipient_domain) VALUES("'+ MessageID+'","'+msg.mailto.Strings[i]+'","'+recipient_domain+'")';
         if not mssql.QUERY_SQL(pChar(sql),'artica_backup') then begin
            logs.Syslogs('warning Unable to backup ' + MessageID + ' to=<' + msg.mailto.Strings[i] + '> message in storage_recipients table, mysql error, try later time');
            exit(false);
         end;
         
         logs.Syslogs(msg.messageID + ': from=<' +msg.mailfrom+'>, to=<'+msg.mailto.Strings[i]+'>, original-size='+GetFileBytes+ ', html-size='+IntToStr(length(msg.HtmlMessage))+' bytes, status=backup_success');
         
    end;
 mssql.free;
 RegExpr.free;
 msg.OriginalMessage.Free;
 msg.mailto.Free;
 exit(true);

end;
//##############################################################################



function tmailarchive.DecodeMessage(message_path:string):mailrecord;
var
   msg:mailrecord;
   Mime:TMimeMess;
   mailto:string;
   i:Integer;
   RegExpr     :TRegExpr;
begin
   Mime:=TMimeMess.Create;
   msg.mailto:=TstringList.Create;
   msg.OriginalMessage:=TstringList.Create;
   Mime.Lines.LoadFromFile(message_path);
   Mime.DecodeMessage;
   msg.messageID:=Mime.Header.MessageID;
   msg.MessageDate:=FormatDateTime('yyyy-mm-dd hh:mm:ss', Mime.Header.Date);
   RegExpr:=TRegExpr.Create;
   msg.mailfrom:=DecodeMailAddr(Mime.Header.From);
   msg.subject:=Mime.Header.Subject;
   msg.OriginalMessage.LoadFromFile(message_path);
   msg.HtmlMessage:=TransFormToHtml(message_path);
   msg.message_path:=message_path;
   
   for i:=0 to mime.Header.ToList.Count-1 do begin;
        mailto:=DecodeMailAddr(mime.Header.ToList.Strings[i]);
        msg.mailto.Add(mailto);
   end;
   
   for i:=0 to mime.Header.CCList.Count-1 do begin;
        mailto:=DecodeMailAddr(mime.Header.CCList.Strings[i]);
        msg.mailto.Add(mailto);
   end;
   
   
  result:=msg;

end;
//##############################################################################
function tmailarchive.DecodeMailAddr(source:string):string;
var
RegExpr     :TRegExpr;
begin
RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='<(.+?)>';
   if RegExpr.Exec(source) then begin
      result:=RegExpr.Match[1];
   end else begin
        RegExpr.Expression:='(.+?)@(.+?)\s+';
        if RegExpr.Exec(source) then begin
            result:=RegExpr.Match[1]+'@'+RegExpr.Match[2];
        end else begin
            result:=source;
         end;
   end;

   result:=AnsiReplaceText(result,'"<','');
   result:=AnsiReplaceText(result,'"','');
   result:=LowerCase(result);
 RegExpr.Free;

end;
//##############################################################################
function tmailarchive.TransFormToHtml(message_path:string):string;
var
   cmd:string;
begin


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
   cmd:=cmd + '>/opt/artica/tmp/' + ExtractFileName(message_path) + '.html 2>&1';

  logs.Debuglogs('TransFormToHtml():: Export to html ' + message_path);
  logs.Debuglogs('TransFormToHtml():: '+cmd);
  fpsystem(cmd);

  result:=logs.ReadFromFile('/opt/artica/tmp/' + ExtractFileName(message_path) + '.html');
  logs.DeleteFile('/opt/artica/tmp/' + ExtractFileName(message_path) + '.html');

end;




procedure tmailarchive.ScanHeaders();
var
   i:integer;
   l:TstringList;
   HEADERS:TstringList;
begin
HEADERS:=TstringList.Create;
{
RegExpr.Expression:='--perform=stat';
 if RegExpr.Exec(globalCommands) then begin
     performStats();
     halt(0);
 end;


  if not FileExists(hookpath + '/INPUTMSG') then begin
     LOGS.Syslogs('WARNING! Unable to stat ' + hookpath + '/INPUTMSG');
     exit;
  end;


  HEADERS:=TstringList.Create;
  HEADERS.LoadFromFile(hookpath + '/INPUTMSG');


  Mime.Lines.AddStrings(HEADERS);
  Mime.DecodeMessage;

  LOAD_SENDER();
  RegExpr.Expression:='<(.*?)>';
  if length(mailfrom)=0 then begin
     if RegExpr.Exec(Mime.Header.From) then mailfrom:=RegExpr.Match[1];
  end;

     LOGS.Debuglogs('ScanHeaders():: From: ' + mailfrom );

     GLOBAL_SUBJECT:=Mime.Header.Subject;
     LOGS.Debuglogs('ScanHeaders():: Subject: ' + GLOBAL_SUBJECT );


     MessageID:=Mime.Header.MessageID;
     MessageID:=AnsiReplaceText(MessageID,';','-');
     MessageID:=AnsiReplaceText(MessageID,' ','-');
     MessageID:=AnsiReplaceText(MessageID,'$','-');
     MessageID:=AnsiReplaceText(MessageID,'%','-');
     MessageID:=AnsiReplaceText(MessageID,'!','-');
     MessageID:=AnsiReplaceText(MessageID,'&','-');




     LOGS.Debuglogs('ScanHeaders():: MessageID: ' + Mime.Header.MessageID );

     ScanRecipts_inCOMMANDS();


     for i:=0 to mime.Header.ToList.Count-1 do begin
        AddRecipient(mime.Header.ToList.Strings[i]);
     end;


     for i:=0 to mime.Header.CCList.Count-1 do begin
        AddRecipient(mime.Header.CCList.Strings[i]);
     end;

     LOGS.Debuglogs('ScanHeaders():: To,bcc List:=' + IntToStr(Recipients.Count));

  RegExpr.Expression:='--perform=htmlsize';

   if RegExpr.Exec(globalCommands) then begin
     LOGS.Debuglogs('ScanHeaders():: perform HTML Size..');
     htmlsize();
  end;

RegExpr.Expression:='--perform=backup';

   if RegExpr.Exec(globalCommands) then begin
     logs.Syslogs('<' + MessageID + '> mail is put into backup queue in /opt/artica/mimedefang-hooks/backup-queue/ waiting backup process');
     LOGS.Debuglogs('ScanHeaders():: perform Backup rules...');
     forceDirectories('/opt/artica/mimedefang-hooks/backup-queue');
     forceDirectories('/opt/artica/mimedefang-hooks/rcpt-queue');
     HEADERS.SaveToFile('/opt/artica/mimedefang-hooks/backup-queue/' + MessageID);
     Recipients.SaveToFile('/opt/artica/mimedefang-hooks/rcpt-queue/' + MessageID);


  end;


RegExpr.Expression:='--perform=bogo';
   if RegExpr.Exec(globalCommands) then begin
      LOGS.Debuglogs('ScanHeaders():: perform bogofilter rules...');
      bogofilter(hookpath + '/INPUTMSG');
      LOGS.Debuglogs('ScanHeaders():: finish die...');
      halt(0);
   end;

RegExpr.Expression:='--perform=learn_bogo';
   if RegExpr.Exec(globalCommands) then begin
      LOGS.Debuglogs('ScanHeaders():: perform bogofilter learning...');
      bogofilter_learn(hookpath + '/INPUTMSG');
      LOGS.Debuglogs('ScanHeaders():: finish die...');
      halt(0);
   end;

RegExpr.Expression:='--perform=whitelist';
 if RegExpr.Exec(globalCommands) then begin
      LOGS.Debuglogs('ScanHeaders():: perform whitelist learning...');
      autowhite(hookpath + '/INPUTMSG');
      LOGS.Debuglogs('ScanHeaders():: finish die...');
      halt(0);
 end;

RegExpr.Expression:='--perform=translations';
 if RegExpr.Exec(globalCommands) then begin
      LOGS.Debuglogs('ScanHeaders():: perform duplicates emails for recipients...');
      Duplicates(hookpath);
      LOGS.Debuglogs('ScanHeaders():: finish die...');
      halt(0);
 end;



   }

end;

//##############################################################################
end.
