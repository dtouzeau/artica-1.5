unit reports;

{$LONGSTRINGS ON}
{$mode objfpc}{$H+}
interface

uses
Classes, SysUtils,variants, IniFiles,oldlinux,strutils,md5,RegExpr in 'RegExpr.pas',db,sqlite3ds,ldap;

type
  TStringDynArray = array of string;


type

  ServFailedInfo= record //shared data between component, listen thread and handler
    zMD5:String;
    DOMAIN:string;
    MX:string;
    MX_IP:string;
    COUNT_TIME:integer;
    MESSAGE_ID:string;
  end;






 type
  Treports=class


private
       function MD5FromString(values:string):string;
       procedure DeleteMaxDayQuarantineForUser(LineUser:string;o:OU_settings);
       procedure DeleteFile(filepath:string);
       function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
       CountMail:integer;
       function ArticaFilterQueuePath():string;
       procedure GetSQLError(cmdsql:string;sql_result:string);

public
    destructor Destroy; override;
    constructor Create;
    function  UserListAsQuarantine(ou:string):TStringDynArray;
    function  GenerateQuarantineMail_report_yesterday(email:string;ou:OU_settings):string;
    function  GenerateQuarantineMail_report_Other(email:string;ou:OU_settings):string;
    procedure deleteallmailfrommailtoother(email:string);
    procedure deleteallmailfrommailtoyesterday(email:string);


END;

implementation

constructor TReports.Create;
begin
end;

destructor TReports.Destroy();
begin
     inherited Destroy;
end;

//##############################################################################
function TReports.UserListAsQuarantine(ou:string):TStringDynArray;
var
   db:TSqlite3Dataset;
   SQL,email:string;
   userlist:array of string;
   zCount:integer;
   CountMessages:array of integer;
   yest,other:string;
   ldap:Tldap;
   o:OU_settings;
   mail_from,subject,body:string;
   l:Tstringlist;
   D:boolean;
begin
    db:=TSqlite3Dataset.Create(nil);
    db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
    SQL:='SELECT count(ID) as tcount,mail_to,ou,Deleted,quarantine from messages ';
    sql:=sql+ ' GROUP BY mail_to,Deleted,quarantine,ou HAVING quarantine=1 AND Deleted IS NULL AND ou="' + ou + '"';
    db.SQL:=SQL;
    db.Open;
    db.First;
    D:=COMMANDLINE_PARAMETERS('verbose');


    ldap:=Tldap.Create;
    o:=ldap.OUDATAS(ou);
    l:=TstringList.create;
    mail_from:=o.report_template_from;
    if D then writeln('rows ---> ', db.RecordCount);
    if D then writeln(sql);
   
     while not db.EOF do begin
         zCount:=db.FieldByName('tcount').AsInteger;
         email:=db.FieldByName('mail_to').AsString;
         if D then begin
            writeln('');
            writeln('******************************************************************************************');
            writeln('eMail ---> ', email);
         end;
            
            
         yest:=GenerateQuarantineMail_report_yesterday(email,o) + GenerateQuarantineMail_report_Other(email,o);
         if D then writeln('length of yest ---> ', length(yest));
         if length(yest)>0 then begin

                l.Add('<FROM>' + mail_from + '</FROM>');
                l.Add('<TO>' + email + '</TO>');


                body:=o.report_template_body;
                body:=AnsiReplaceText(body,'%NUMBER%',IntTOStr(zCount));
                body:=AnsiReplaceText(body,'%MAXDAY%',o.quarantine_maxday);
                body:=AnsiReplaceText(body,'%MAILTABLE%',yest);

                subject:=o.report_template_subject;
                subject:=AnsiReplaceText(subject,'%NUMBER%',IntToStr(zCount));
                subject:=AnsiReplaceText(subject,'%MAXDAY%',o.quarantine_maxday);
                

                l.Add('<SUBJECT>' +subject+ '</SUBJECT>');
                l.Add('<MAILBODY><html><head></head><body>' +body+ '</body></html></MAILBODY>');
                l.SaveToFile('/tmp/' +email+ '.xml');
                l.Clear;

                if D then writeln('/usr/share/artica-postfix/bin/artica-mime notif /tmp/' + email + '.xml');
                Shell('/usr/share/artica-postfix/bin/artica-mime notif /tmp/' + email + '.xml');
         
         end;
         if D then begin
              writeln('******************************************************************************************');
              writeln('');
              writeln('');
         end;
         db.Next;
     end;
    db.Close;
    l.Free;
end;
 //##############################################################################
function TReports.GenerateQuarantineMail_report_yesterday(email:string;ou:OU_settings):string;
var
   db:TSqlite3Dataset;
   SQL:string;
   userlist:array of string;
   zCount:integer;
   L:TstringList;
   line:string;
   count:integer;
   ldap:Tldap;
   D:boolean;
   bg,subject:string;
begin
   D:=false;
   D:=COMMANDLINE_PARAMETERS('verbose');
   ldap:=Tldap.Create;

   SQL:='SELECT  mail_from,zMD5,subject,message_path,mail_to,quarantine,Deleted,strftime("%H:%M", received_date) as ttime,strftime("%Y%m%d", received_date)+0 as tdate FROM messages WHERE tdate=strftime("%Y%m%d", datetime("now","-1 day"))+0 AND mail_to="'+email + '" AND quarantine=1 AND Deleted IS NULL';
   db:=TSqlite3Dataset.Create(nil);
   db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
   if ParamStr(2)='yesterday' then writeln(sql);
   db.SQL:=SQL;
   if D then writeln('GenerateQuarantineMail_report_yesterday:'+SQL);
   
   
   db.Open;
   if db.RecordCount=0 then begin
      if D then writeln('no records for ' + email);
      if ParamStr(2)='yesterday' then writeln('no records for ' + email);
      db.Close;
      db.Free;
      exit;
   end;

   db.First;
   bg:='CCCCCC';
   line:='<H3>Yesterday</H3><H4><a href="' +ou.web_uri + '/cmd.quarantine.php?method=deleteAllYesterday&mail=' + email +'">Delete all mails</a></H4><table style="width:80%;padding:5px;margin:5px;border:1px solid #CCCCCC">';
   if D then writeln('START LOOP');
   while not db.EOF do begin
   if FileExists(db.FieldByName('message_path').AsString) then begin
      TRY
      if bg='CCCCCC' then bg:='FFFFFF' else bg:='CCCCCC';
      subject:=db.FieldByName('subject').AsString;
      if length(subject)>70 then subject:=copy(subject,0,67) + '...';
      line:=line + '<tr style="background-color:#' + bg + '">';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong>' + db.FieldByName('ttime').AsString +'</strong></td>';
      line:=line + '<td width=10% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong>' + db.FieldByName('mail_from').AsString +'</strong></td>';
      line:=line + '<td width=15% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong>' + subject +'</strong></td>';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong><a href="' +ou.web_uri + '/cmd.quarantine.php?method=release&id=' + db.FieldByName('zMD5').AsString + '">Release mail</a></strong></td>';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong><a href="' +ou.web_uri + '/cmd.quarantine.php?method=delete&id=' + db.FieldByName('zMD5').AsString + '">Delete mail</a></strong></td>';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong><a href="' +ou.web_uri + '/cmd.quarantine.php?method=white&id=' + db.FieldByName('zMD5').AsString + '">White list sender</a></strong></td>';
      line:=line + '</tr>';
      inc(count);
      EXCEPT
         if D then writeln('ERROR in LOOP');
      end;
   end;
   db.Next;

   end;
   db.close;
   line:=line + '</table>';
   if ParamStr(2)='yesterday' then writeln(line);
   if D then writeln('DONE..........');
   exit(line);

end;
//##############################################################################
function TReports.GenerateQuarantineMail_report_Other(email:string;ou:OU_settings):string;
var
   db:TSqlite3Dataset;
   SQL:string;
   userlist:array of string;
   zCount:integer;
   L:TstringList;
   line:string;
   count:integer;
   ldap:Tldap;
   bg:string;
   subject:string;
begin
   ldap:=Tldap.Create;
   SQL:='SELECT  mail_from,zMD5,subject,message_path,mail_to,quarantine,Deleted,strftime("%Y-%m-%d %H:%M", received_date) as ttime,strftime("%Y%m%d", received_date)+0 as tdate FROM messages WHERE tdate<strftime("%Y%m%d", datetime("now","-1 day"))+0 AND mail_to="'+email + '" AND quarantine=1 AND Deleted IS NULL LIMIT 0,500';
   db:=TSqlite3Dataset.Create(nil);
   db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
   if ParamStr(2)='yesterday' then writeln(sql);
   db.SQL:=SQL;
   db.Open;
   if db.RecordCount=0 then begin
      db.Close;
      db.Free;
      exit;
   end;

   db.First;

   line:='<HR><H3>Before yesterday</H3><H4><a href="' +ou.web_uri + '/cmd.quarantine.php?method=deleteAllOther&mail=' + email +'">Delete all mails</a></H4><table style="width:100%;padding:5px;margin:5px;border:1px solid #CCCCCC">';
   while not db.EOF do begin
   if FileExists(db.FieldByName('message_path').AsString) then begin
      if bg='CCCCCC' then bg:='FFFFFF' else bg:='CCCCCC';
      subject:=db.FieldByName('subject').AsString;
      if length(subject)>70 then subject:=copy(subject,0,67) + '...';
      line:=line + '<tr style="background-color:#' + bg + '">';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong>' + db.FieldByName('ttime').AsString +'</strong></td>';
      line:=line + '<td width=10% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong>' + db.FieldByName('mail_from').AsString +'</strong></td>';
      line:=line + '<td width=15% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong>' + subject +'</strong></td>';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong><a href="' +ou.web_uri + '/cmd.quarantine.php?method=release&id=' + db.FieldByName('zMD5').AsString + '">Release mail</a></strong></td>';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong><a href="' +ou.web_uri + '/cmd.quarantine.php?method=delete&id=' + db.FieldByName('zMD5').AsString + '">Delete mail</a></strong></td>';
      line:=line + '<td width=5% nowrap style="padding:3px;margin:2px;border:1px solid #CCCCCC;font-size:10px"><strong><a href="' +ou.web_uri + '/cmd.quarantine.php?method=white&id=' + db.FieldByName('zMD5').AsString + '">White list sender</a></strong></td>';
      line:=line + '</tr>';
      inc(count);
      inc(count);
   end;
   db.Next;

   end;
   db.close;
   line:=line + '</table>';
 if ParamStr(2)='yesterday' then writeln(line);
   exit(line);

end;
//##############################################################################
function TReports.MD5FromString(values:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5String(values);
exit(MD5Print(Digest));
end;
//##############################################################################
procedure TReports.DeleteMaxDayQuarantineForUser(LineUser:string;o:OU_settings);
var
   i:integer;
   RegExpr:TRegExpr;
   ST:Integer;
   sql:string;
   db:TSqlite3Dataset;
   D:boolean;
   ReallyCount:integer;
   l:TstringList;
   body,subject:string;
   TABLE:TstringList;
begin
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='(.+?);([0-9]+)';
     db:=TSqlite3Dataset.Create(nil);
     if ParamStr(2)='MaxDay' then D:=True;
     if not RegExpr.Exec(LineUser) then exit;



     sql:='SELECT ID,zMD5,message_path,mail_from,subject from messages WHERE strftime("%Y%m%d", received_date)+0<strftime("%Y%m%d", datetime("now","-' + o.quarantine_maxday+' day"))+0 and mail_to="' + RegExpr.Match[1] + '" AND Deleted IS NULL and quarantine=1';
     db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
     db.SQL:=SQL;
     if D then writeln(SQL);
     db.Open;
     db.First;
     TABLE:=TstringList.Create;
     TABLE.Add('<table style="width:100%">');

while not db.EOF do begin
      if FileExists(db.FieldByName('message_path').AsString) then begin

            inc(ReallyCount);
            DeleteFile(db.FieldByName('message_path').AsString);
            SQL:='UPDATE messages SET Deleted=1 WHERE ID=' + db.FieldByName('ID').AsString;
            db.QuickQuery(SQL);
            GetSQLError(SQL,db.SqliteReturnString);
            TABLE.Add('<tr><td>' + db.FieldByName('mail_from').AsString + '</td><td>' + db.FieldByName('subject').AsString + '</td></tr>');
            if D then writeln(db.FieldByName('message_path').AsString + ' --> ' + SQL);
       end;
      db.Next;
   end;
   db.Close;
   TABLE.Add('</table>');
   l:=TstringList.Create;
   l.Add('<FROM>' + o.maxday_template_from + '</FROM>');
   l.Add('<TO>' + RegExpr.Match[1] + '</TO>');


   body:=o.maxday_template_body;
   subject:=o.maxday_template_subject;
   body:=AnsiReplaceText(body,'%NUMBER%',IntTOStr(ReallyCount));
   body:=AnsiReplaceText(body,'%MAXDAY%',o.quarantine_maxday);
   body:=AnsiReplaceText(body,'%MAILTABLE%',TABLE.Text);
   subject:=AnsiReplaceText(subject,'%NUMBER%',IntTOStr(ReallyCount));
   subject:=AnsiReplaceText(subject,'%MAXDAY%',o.quarantine_maxday);

   l.Add('<SUBJECT>' +subject+ '</SUBJECT>');
   l.Add('<MAILBODY><html><head></head><body>' +body+ '</body></html></MAILBODY>');
   l.SaveToFile('/tmp/' + RegExpr.Match[1] + '.xml');
   l.Free;
   TABLE.free;

   writeln('Really Count ' + IntTOStr(ReallyCount) + '/' + RegExpr.Match[2] + ' For ' + RegExpr.Match[1]);
   Shell('/usr/share/artica-postfix/bin/artica-mime notif /tmp/' + RegExpr.Match[1] + '.xml');

end;
//##############################################################################
procedure TReports.DeleteFile(filepath:string);
var F:Text;
begin
if FileExists(filepath) then begin
   try
    Assign (F,filepath);
    Erase (f);
    finally
    end;
end;
end;
//##############################################################################
procedure TReports.deleteallmailfrommailtoother(email:string);
var
   db:TSqlite3Dataset;
   SQL:string;
   zCount:integer;
   count:integer;
   bg:string;
   subject,mdate,ID:string;
   message_path,mail_from:string;
begin
   SQL:='SELECT  ID,mail_from,zMD5,subject,message_path,mail_to,quarantine,Deleted,strftime("%Y-%m-%d %H:%M", received_date) as ttime,strftime("%Y%m%d", received_date)+0 as tdate';
   SQL:=SQL + ' FROM messages WHERE tdate<strftime("%Y%m%d", datetime("now","-1 day"))+0 AND mail_to="'+email + '" AND quarantine=1 AND Deleted IS NULL';
   db:=TSqlite3Dataset.Create(nil);
   db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
   db.SQL:=SQL;
   db.Open;
   if db.RecordCount=0 then begin
     writeln('NO record');
      db.Close;
      db.Free;
      exit;
   end;

   db.First;
   writeln('<strong>'+IntTOStr(db.RecordCount)+ ' records</strong><hr>');
   while not db.EOF do begin
         message_path:=db.FieldByName('message_path').AsString;
         subject:=db.FieldByName('subject').AsString;
         mdate:=db.FieldByName('ttime').AsString;
         mail_from:=db.FieldByName('mail_from').AsString;
         ID:=db.FieldByName('ID').AsString;
         DeleteFile(message_path);
         db.QuickQuery('UPDATE messages SET Deleted=1 WHERE ID=' + ID);
         GetSQLError(SQL,db.SqliteReturnString);
         if count<200 then begin
            writeln('From:<strong>' + mail_from + '</strong> on ' + mdate);
            writeln('Subject:&nbsp;&laquo;' + subject + '&nbsp;&raquo; (deleted)');
            writeln('<br>');
         end;
         Inc(count);
         db.Next;
   end;
   db.Close;
   db.Free;
end;

//##############################################################################
procedure TReports.deleteallmailfrommailtoyesterday(email:string);
var
   db:TSqlite3Dataset;
   SQL:string;
   zCount:integer;
   count:integer;
   bg:string;
   subject,mdate,ID:string;
   message_path,mail_from:string;
begin
   SQL:='SELECT  ID,mail_from,zMD5,subject,message_path,mail_to,quarantine,Deleted,strftime("%H:%M", received_date) as ttime,strftime("%Y%m%d", received_date)+0 as tdate FROM messages WHERE tdate=strftime("%Y%m%d", datetime("now","-1 day"))+0 ';
   SQL:=SQL + ' AND mail_to="'+email + '" AND quarantine=1 AND Deleted IS NULL';
   db:=TSqlite3Dataset.Create(nil);
   db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
   db.SQL:=SQL;
   db.Open;
   if db.RecordCount=0 then begin
     writeln('NO record');
      db.Close;
      db.Free;
      exit;
   end;

   db.First;
   writeln('<strong>'+IntTOStr(db.RecordCount)+ ' records</strong><hr>');
   while not db.EOF do begin
         message_path:=db.FieldByName('message_path').AsString;
         subject:=db.FieldByName('subject').AsString;
         mdate:=db.FieldByName('ttime').AsString;
         mail_from:=db.FieldByName('mail_from').AsString;
         ID:=db.FieldByName('ID').AsString;
         DeleteFile(message_path);
         db.QuickQuery('UPDATE messages SET Deleted=1 WHERE ID=' + ID);
         GetSQLError(SQL,db.SqliteReturnString);
         if count<200 then begin
            writeln('From:<strong>' + mail_from + '</strong> on ' + mdate);
            writeln('Subject:&nbsp;&laquo;' + subject + '&nbsp;&raquo; (deleted)');
            writeln('<br>');
         end;
         Inc(count);
         db.Next;
   end;
   db.Close;
   db.Free;
end;
//##############################################################################
function TReports.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:=false;
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
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
procedure TReports.GetSQLError(cmdsql:string;sql_result:string);
var
   RegExpr:TRegExpr;
   FileName:string;
   DATAS:TstringList;
begin
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='SQLITE_DONE';
  if not RegExpr.Exec(sql_result) then begin
     FileName:=ArticaFilterQueuePath() + '/' +MD5FromString(cmdsql) + '.sql';
     DATAS:=TstringList.Create;
     DATAS.Add(cmdsql);
     DATAS.SaveToFile(FileName);
     DATAS.Free;
  end;


end;
//##############################################################################
function TReports.ArticaFilterQueuePath():string;
var ini:TIniFile;
begin
 ini:=TIniFile.Create('/etc/artica-postfix/artica-filter.conf');
 result:=ini.ReadString('INFOS','QueuePath','');
 if length(trim(result))=0 then result:='/usr/share/artica-filter';
end;
//##############################################################################

end.

