unit quarantine;

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
  Tquarantine=class


private
       function MD5FromString(values:string):string;
       function OuDeleteMaxDayQuarantineAddUser(Userlist:TStringDynArray;email:string;number:integer):TStringDynArray;
       procedure DeleteMaxDayQuarantineForUser(LineUser:string;o:OU_settings);
       
public
    destructor Destroy; override;
    constructor Create;
    procedure Fixup();
    function UserListAsQuarantine(ou:string):TStringDynArray;
    procedure GenerateQuarantineMail(email:string);
    procedure OuConfig(ou:string);
    procedure OuDeleteMaxDayQuarantine(ou:string);
END;

implementation

constructor Tquarantine.Create;
begin
end;

destructor Tquarantine.Destroy();
begin
     inherited Destroy;
end;
procedure Tquarantine.Fixup();
var
   db:TSqlite3Dataset;
   SQL,email,ID:string;
begin
     db:=TSqlite3Dataset.Create(nil);
     db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
     db.SQL:='SELECT ID,mail_to from messages where mail_to LIKE "%,%"';
     db.Open;

     db.First;
    while not db.EOF do begin
          email:=db.FieldByName('mail_to').AsString;
          writeln('Analyze ',email);
          if Copy(email,length(email),1)=',' then begin
             email:=Copy(email,0,length(email)-1);

             ID:=db.FieldByName('ID').AsString;
             db.QuickQuery('UPDATE messages SET mail_to="' + email + '" WHERE ID=' + ID );
          end;
          db.Next;
    end;
    db.Close;
    db.SQL:='SELECT ID,MessageID from messages where zMD5 IS NULL';
 db.Open;

     db.First;
    while not db.EOF do begin
       email:=MD5FromString(db.FieldByName('ID').AsString + db.FieldByName('MessageID').AsString);
       writeln('UPDATE messages SET zMD5="' + email + '" WHERE ID=' + db.FieldByName('ID').AsString );
        db.QuickQuery('UPDATE messages SET zMD5="' + email + '" WHERE ID=' + db.FieldByName('ID').AsString );
      db.Next;
    end;
    
db.Close;

   writeln('Fixing tables done');
end;



function Tquarantine.UserListAsQuarantine(ou:string):TStringDynArray;
var
   db:TSqlite3Dataset;
   SQL,email:string;
   userlist:array of string;
   zCount:integer;
   CountMessages:array of integer;
begin
    db:=TSqlite3Dataset.Create(nil);
    db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
    SQL:='SELECT count(ID) as tcount,mail_to FROM messages GROUP BY mail_to HAVING datetime(received_date)<datetime("now") AND quarantine=1 AND Deleted IS NULL AND ou="' + ou + '" LIMIT 0,10';
    db.SQL:=SQL;
    db.Open;
    db.First;

    SetLength(userlist, 0);
     while not db.EOF do begin
         zCount:=db.FieldByName('tcount').AsInteger;
         email:=db.FieldByName('mail_to').AsString;
         writeln(email + ' as '  + IntToStr(zCount) + ' messages in quarantine');
          db.Next;
     end;
    db.Close;
end;

procedure Tquarantine.GenerateQuarantineMail(email:string);
var
   db:TSqlite3Dataset;
   SQL:string;
   userlist:array of string;
   zCount:integer;
   L:TstringList;
   line:string;
   count:integer;
begin
//   SQL='SELECT count(ID) as tcount,mail_to,strftime("%Y%m%d", received_date)+0 as tdate FROM messages GROUP BY mail_to,strftime("%Y%m%d", received_date)+0 HAVING strftime("%Y%m%d", received_date)+0< strftime("%Y%m%d", datetime("now","-2 day"))+0';
   SQL:='SELECT *,strftime("%H:%M",received_date) as ttime,strftime("%Y%m%d", received_date)+0 as tdate FROM messages WHERE strftime("%Y%m%d", received_date)+0=strftime("%Y%m%d", datetime("now","-1 day"))+0 AND mail_to="' + email + '" AND quarantine=1 AND Deleted IS NULL';
   db:=TSqlite3Dataset.Create(nil);
   db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
   writeln(sql);
   db.SQL:=SQL;
   db.Open;
   db.First;

   line:='<table style="width:100%;padding:5px;margin:5px;border:1px solid #CCCCCC">';
   while not db.EOF do begin
   line:=line + '<tr>';
   line:=line + '<td><strong>' + db.FieldByName('ttime').AsString +'</strong></td>';
   line:=line + '<td><strong>' + db.FieldByName('mail_from').AsString +'</strong></td>';
   line:=line + '<td><strong>' + db.FieldByName('subject').AsString +'</strong></td>';
   line:=line + '</tr>';
   db.Next;
   inc(count);
   end;
   db.close;
   line:=line + '</table>';
   L:=TstringList.Create;
   l.Add(line);
   l.SaveToFile('/home/dtouzeau/temp.html');

end;
function Tquarantine.MD5FromString(values:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5String(values);
exit(MD5Print(Digest));
end;
//##############################################################################
procedure Tquarantine.OuConfig(ou:string);
var
   ldap:TLdap;
   o:OU_settings;
begin
ldap:=Tldap.Create();
  o:=ldap.OUDATAS(ou);
  
  writeln('Organization parameters of '+ ou);
  writeln('****************************************************************');
  writeln('');
  writeln('Max Quarantine days..............:' + o.quarantine_maxday);
  writeln('Max Quarantine Subject...........:' + o.maxday_template_subject);
  writeln('Max Quarantine From..............:' + o.maxday_template_from);
  writeln('');
  

end;
//##############################################################################
procedure Tquarantine.OuDeleteMaxDayQuarantine(ou:string);
var
   ldap:TLdap;
   o:OU_settings;
   I:Integer;
   SQL,email,number:string;
   db:TSqlite3Dataset;
   D:Boolean;
   Userlist:TStringDynArray;
begin
  D:=false;
  if ParamStr(2)='MaxDay' then D:=True;
  ldap:=Tldap.Create();
  o:=ldap.OUDATAS(ou);
  SQL:='SELECT Count(ID) as tcount ,mail_to,strftime("%Y%m%d", received_date)+0 FROM messages GROUP BY mail_to,strftime("%Y%m%d", received_date)+0 HAVING strftime("%Y%m%d", received_date)+0<strftime("%Y%m%d", datetime("now","-' + o.quarantine_maxday+' day"))+0 AND ou="'+ou+'" AND Deleted IS NULL and quarantine=1';
  db:=TSqlite3Dataset.Create(nil);
  db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
  db.SQL:=SQL;
  if D then writeln(SQL);
  db.Open;
  db.First;
  SetLength(Userlist, 0);
   while not db.EOF do begin
      email:=db.FieldByName('mail_to').AsString;
      number:=db.FieldByName('tcount').AsString;
      Userlist:=OuDeleteMaxDayQuarantineAddUser(Userlist,email,StrToInt(number));
      db.Next;
   end;
   
   if D then begin
       for i:=0 to length(Userlist)-1 do begin
           writeln(Userlist[i]);
           DeleteMaxDayQuarantineForUser(Userlist[i],o);
       end;
   
   end;
   
   
end;
//##############################################################################
procedure Tquarantine.DeleteMaxDayQuarantineForUser(LineUser:string;o:OU_settings);
var
   i:integer;
   RegExpr:TRegExpr;
   ST:Integer;
   sql:string;
   db:TSqlite3Dataset;
   D:boolean;
   ReallyCount:integer;
begin
     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='(.+?);([0-9]+)';
     db:=TSqlite3Dataset.Create(nil);
     if ParamStr(2)='MaxDay' then D:=True;
     if not RegExpr.Exec(LineUser) then exit;
     sql:='SELECT ID,zMD5,message_path from messages WHERE strftime("%Y%m%d", received_date)+0<strftime("%Y%m%d", datetime("now","-' + o.quarantine_maxday+' day"))+0 and mail_to="' + RegExpr.Match[1] + '" AND Deleted IS NULL and quarantine=1';
     db.FileName:='/usr/share/artica-postfix/LocalDatabases/artica_database.db';
     db.SQL:=SQL;
     if D then writeln(SQL);
     db.Open;
     db.First;
      
while not db.EOF do begin
      if D then begin
         if FileExists(db.FieldByName('message_path').AsString) then begin
            writeln(db.FieldByName('message_path').AsString + ' -->Delete');
            inc(ReallyCount);
            
         end else begin
            writeln(db.FieldByName('message_path').AsString + ' -->FAILED');
         end;
      end;
      db.Next;
   end;
   
   if D then writeln('Really Count ' + IntTOStr(ReallyCount) + '/' + RegExpr.Match[2] + ' For ' + RegExpr.Match[1]);
   
end;
//##############################################################################


function Tquarantine.OuDeleteMaxDayQuarantineAddUser(Userlist:TStringDynArray;email:string;number:integer):TStringDynArray;
var
   i:integer;
   RegExpr:TRegExpr;
   ST:Integer;
begin
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:=email+';([0-9]+)';
   for i:=0 to Length(Userlist)-1 do begin
         if RegExpr.Exec(Userlist[i]) then begin
              ST:=StrToInt(RegExpr.Match[1]);
              ST:=ST+number;
              Userlist[i]:=email + ';' + IntToStr(ST);
              RegExpr.free;
              Exit(Userlist);
         end;
   
   end;
   SetLength(Userlist, length(Userlist)+1);
   Userlist[length(Userlist)-1]:=email+ ';' + IntToStr(number);
   RegExpr.free;
   Exit(Userlist);
end;
//##############################################################################

end.

