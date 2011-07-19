program artica_resend;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,smtpsend,artica_mysql,logs,mimemess
  { you can add units after this };

var
   SMTP:TSMTPSend;
   mail_to,mail_from:string;
   sLOGS:TLogs;
   mysql:Tartica_mysql;
   msgid:string;
   sql:string;
   FullMesg,tmppath:string;
   MIME:Tmimemess;
   subject:string;
begin
SMTP := TSMTPSend.Create;
SMTP.TargetHost:='127.0.0.1';
SMTP.TargetPort:='25';
sLOGS:=Tlogs.Create;


   if ParamStr(1)='quarantine' then begin
        mysql:=Tartica_mysql.Create;
        msgid:=ParamStr(2);
        mail_to:=ParamStr(3);
        writeln('resend command receive to ' + mail_to + ' user');
        
        
        sql:='SELECT mailfrom,subject,fullmesg FROM quarantine WHERE MessageID="' +msgid+'"';
        if mysql.STORE_SQL(sql,'artica_backup') then begin
           TRY
              mail_from:=mysql.rowbuf[0];
           EXCEPT
             writeln('Unable to get infos from Database, it seems this mail is deleted..');
             halt(0);
           end;
           
           subject:=mysql.rowbuf[1];
           FullMesg:=mysql.rowbuf[2];
           writeln('resend message id ' + msgid +'"'+subject+'" ' + IntToStr(length(FullMesg)) + ' bytes');
           tmppath:='/opt/artica/tmp/'+sLOGS.MD5FromString(msgid);
           writeln('Write tempfile to ' + tmppath);
           if not sLOGS.WriteToFile(FullMesg,tmppath) then begin
              writeln('unable to write file ' +  tmppath);
              exit;
           end;
            Mime:=Tmimemess.Create;
            writeln('Loading message...');
            Mime.Lines.LoadFromFile(tmppath);
            sLOGS.DeleteFile(tmppath);
            Mime.DecodeMessage;
            Mime.Header.CustomHeaders.Add('X-Artica: PASS');
            writeln('Encode message...');
            Mime.EncodeMessage;

            writeln('Connecting...');
            if not SMTP.Login then begin
               writeln('Failed connect to 127.0.0.1 25 -> ' + SMTP.FullResult.Text);
               halt(0);
            end;

            if not SMTP.MailFrom(mail_from,Length(Mime.Lines.Text)) then begin
               writeln(SMTP.FullResult.Text);
               halt(0);
            end;

            if not SMTP.MailTo(mail_to) then begin
               writeln(SMTP.FullResult.Text);
               halt(0);
            end;

            if not SMTP.MailData(Mime.Lines) then begin
               writeln(SMTP.FullResult.Text);
               halt(0);
            end;
            writeln(SMTP.FullResult.Text);
            SMTP.Logout;
            writeln('Success resend mail...');
            
            sql:='DELETE FROM quarantine WHERE MessageID="' +msgid+'"';
            if mysql.QUERY_SQL(PChar(sql),'artica_backup') then begin
               sql:='DELETE FROM storage_recipients WHERE MessageID="' +msgid+'"';
               if mysql.QUERY_SQL(PChar(sql),'artica_backup') then begin
                  writeln('Success delete mail...');
               end else begin
                  writeln('ERROR delete mail into mysql database...');
               end;
            end else begin
              writeln('ERROR delete mail into mysql database...');
            end;
            
            
            
            halt(0)
        end else begin
           writeln('Unable to get infos from Database, it seems this mail is deleted..');
           halt(0);
        
        end;
   end;




end.

