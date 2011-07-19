unit notifs;

{$mode objfpc}{$H+}

interface

uses
//depreciated oldlinux -> baseunix
Classes, SysUtils,variants,strutils, Process,IniFiles,baseunix,unix,md5,RegExpr in 'RegExpr.pas',
systemlog,smtpsend,logs,mimemess,mimepart,zsystem,unixnetwork;

  type
  tnotifs=class


private

   mail_to,mail_from:string;
   network:tnetwork;
   LOGS:TLogs;
   msgid:string;
   sql:string;
   FullMesg,tmppath:string;
   MIME:Tmimemess;
   subject:string;
   conf:TiniFile;
   SYS:Tsystem;
   PROCEDURE Send(TargetFile:string);
   D:boolean;
public
    constructor Create;
    procedure Free;
    PROCEDURE ParseQueue();
end;

implementation

//-------------------------------------------------------------------------------------------------------


//##############################################################################
constructor tnotifs.Create;
var
   pid,mypid:string;
begin
       forcedirectories('/etc/artica-postfix');

       logs:=Tlogs.Create;
       SYS:=Tsystem.Create;
       D:=SYS.COMMANDLINE_PARAMETERS('debug');
       if D then writeln('Checking pid..');
       if not SYS.BuildPids() then halt(0);
       if D then writeln('Checking /etc/artica-postfix/smtpnotif.conf..');
       
       if not FileExists('/etc/artica-postfix/smtpnotif.conf') then halt(0);
       conf:=TiniFile.Create('/etc/artica-postfix/smtpnotif.conf');
       if conf.ReadString('SMTP','enabled','0')<>'1' then halt(0);
       network:=tnetwork.Create;
       if network.isNetworkDown() then begin
          logs.Debuglogs('Network is down, aborting');
          halt(0);
       end;
       mypid:=IntToStr(fpgetpid);
       pid:=SYS.PidAllByProcessPath(ParamStr(0));
       pid:=trim(AnsiReplaceText(pid,mypid,''));
       logs.Debuglogs('PIDs:"'+pid+'"');
       if length(pid)>0 then begin
          logs.Debuglogs('Already instances running');
          halt(0);
       end;
       
       
       
       
       
       

end;
//##############################################################################
PROCEDURE tnotifs.Free();
begin
logs.free;
SYS.free;

end;
//##############################################################################
PROCEDURE tnotifs.ParseQueue();
var
f:tstringList;
i:integer;
begin
    f:=tstringlist.Create;
    f.AddStrings(SYS.SearchFilesInPath('/opt/artica/share/notifications','*'));
    for i:=0 to f.Count-1 do begin
        if sys.FILE_TIME_BETWEEN_MIN(f.Strings[i])>1440 then begin
           logs.DeleteFile(f.Strings[i]);
           continue;
        end;
        
        send(f.Strings[i]);
    
    end;

end;





//##############################################################################
PROCEDURE tnotifs.Send(TargetFile:string);
var
   smtp_server_port:string;
   smtp_server_name:string;
   smtp_auth_user:string;
   smtp_from:string;
   smtp_to:string;
   Body:TstringList;
   ltls:string;
   tls:boolean;
   datas:TstringList;
   i:integer;
   p: TMimepart;
   SMTP:TSMTPSend;
begin
if not FileExists(TargetFile) then exit;

 SMTP := TSMTPSend.Create;
Body:=TstringList.Create;
datas:=TstringList.Create;
datas.LoadFromFile(TargetFile);
subject:=datas.Strings[0];
if subject='sent' then exit;


for i:=1 to datas.Count-1 do begin
    Body.Add(datas.Strings[i]);
end;
logs.Debuglogs('Body  '+IntToStr(datas.count)+' lines');



if length(trim(conf.ReadString('SMTP','smtp_server_name','')))=0 then smtp_server_name:='127.0.0.1' else smtp_server_name:=conf.ReadString('SMTP','smtp_server_name','');
if length(conf.ReadString('SMTP','smtp_server_port',''))=0 then smtp_server_port:='25' else smtp_server_port:=conf.ReadString('SMTP','smtp_server_port','');
smtp_auth_user:=conf.ReadString('SMTP','smtp_auth_user','');
SMTP.TargetHost:=conf.ReadString('SMTP','smtp_server_name','');
SMTP.TargetPort:=smtp_server_port;
//SMTP.Timeout:=5;
if conf.ReadString('SMTP','tls_enabled','0')='1' then begin
   tls:=true;
   ltls:=' using TLS';
   SMTP.AutoTLS:=true;
end  else begin
     ltls:='TLS disabled';
        tls:=false;
end;

if length(subject)=0 then subject:='[ARTICA] notifications tests';
smtp_to:=conf.ReadString('SMTP','smtp_dest','root@localhost');


Mime:=Tmimemess.Create;
p := Mime.AddPartMultipart('mixed', nil);
Mime.Header.CustomHeaders.Add('X-Artica: PASS');
Mime.Header.Subject:=subject;
Mime.Header.From:=conf.ReadString('SMTP','smtp_sender','artica@localhost');
Mime.Header.ToList.Add(smtp_to);
Mime.AddPartText(Body,p);
Mime.EncodeMessage;


if length(smtp_auth_user)>0 then begin
   SMTP.UserName:=smtp_auth_user;
   ltls:=ltls + ' username="'+ smtp_auth_user+'"';
   SMTP.Password:=conf.ReadString('SMTP','smtp_auth_passwd','');
end;

logs.Debuglogs('Connection trought '+SMTP.TargetHost+':'+SMTP.TargetPort + ' ' + ltls);
if not SMTP.Login then begin
       logs.Syslogs('Failed connect to '+SMTP.TargetHost+':'+SMTP.TargetPort +' "'+SMTP.FullResult.Text+'"');
       halt(0);
end;


logs.Debuglogs('Start protocol  trought '+smtp_server_name+':'+smtp_server_port);
if not SMTP.MailFrom(conf.ReadString('SMTP','smtp_sender','artica@localhost'),Length(Mime.Lines.Text)) then begin
   logs.Syslogs('Failed send "mail from:" trought '+smtp_server_name+':'+smtp_server_port +' "'+SMTP.FullResult.Text+'"');
   halt(0);
end;

logs.Debuglogs('Send to recipient <'+conf.ReadString('SMTP','smtp_dest','root@localhost')+'>');

if not SMTP.MailTo(smtp_to) then begin
   logs.Syslogs('Failed send "rctp to:"'+smtp_to+' trought '+smtp_server_name+':'+smtp_server_port +' "'+SMTP.FullResult.Text+'"');
   halt(0);
end;

if not SMTP.MailData(Mime.Lines) then begin
   logs.Syslogs('Failed send "DATA" trought '+smtp_server_name+':'+smtp_server_port +' "'+SMTP.FullResult.Text+'"');
   halt(0);
end;

            logs.Syslogs(SMTP.FullResult.Text);
            SMTP.Logout;
            datas.Insert(0,'sent');
            try
            datas.SaveToFile(TargetFile);
            finally
            datas.free;
            end;
            
logs.Syslogs('notification ('+IntTostr(Mime.Lines.Count)+' lines from=<'+ conf.ReadString('SMTP','smtp_sender','artica@localhost')+'> to=<'+ conf.ReadString('SMTP','smtp_dest','root@localhost')+'> Success send notification mail...trought '+SMTP.TargetHost+':'+SMTP.TargetPort + ' ' + ltls);


{
smtp_notifications=yes
enabled=1
smtp_server_name=mailgate.kaspersky.com
smtp_server_port=465
smtp_sender=artica@pc-touzeau.klf.fr
smtp_dest=david.touzeau@fr.kaspersky.com
smtp_auth_user=david.touzeau
smtp_auth_passwd=JPMUvjeV72PJVnyu
tls_enabled=0


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
   }
end;



end.


