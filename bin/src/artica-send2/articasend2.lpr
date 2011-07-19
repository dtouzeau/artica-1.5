program articasend2;

{$mode objfpc}{$H+}

uses
  Classes,unix,BaseUnix,SysUtils,scanQueue; //

var
 Q:TscanQueue;

s:string;
Guid:string;
myFile : TextFile;
i:Integer;
OLD_PID:string;
ttime:string;


//##############################################################################

begin


 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;

q:=TscanQueue.Create();
     if ParamStr(1)='--input' then begin
      q.pipe_scan();
      halt(1);
     end;



  if ParamStr(1)='--help' then begin
          writeln('PURGE resend *eml files');
          writeln('-----------------------------------------');
          writeln('--purge............: purge queue directory');
          writeln('--queue-path=path..: change queue path from default to specific path ');
          writeln('--remote-port=port.: Change port from 29300 to other ');
          writeln('--no-delete-source.: Do not delete files sources');
          writeln('verbose............: output debug');
          writeln('');
          writeln('');
          writeln('PURGE sql files');
          writeln('-----------------------------------------');
          writeln('--fix-sql..........: perform sql operation stored in sql files');
          writeln('');
          writeln('');
          writeln('user infos');
          writeln('-----------------------------------------');
          writeln('--userinfos [email].: Parse users informations in ldap directory');
          writeln('');
          writeln('');



  halt(0);
  end;


     if length(ParamStr(1))=0 then begin


        writeln(' No queue number specified/File/input process specifed....');
        halt(0);
     end;

     if ParamStr(1)='--userinfos' then begin
    q.UserInfos(ParamStr(2));

     end;


     if ParamStr(1)='--purge' then begin
 q.PurgeQueue();
      halt(0);
     end;

     if ParamStr(1)='--fix-sql' then begin
 q.PurgeSqlQueue();
      halt(0);
     end;


     if ParamStr(1)='-rbl' then begin


     if ParamStr(1)='-rbl' then begin
 q.rblinfo(paramstr(2),paramstr(3));
       halt(0);
     end;
     if ParamStr(1)='-surbl' then begin
 q.surblinfo(paramstr(2),paramstr(3));
        halt(0);
     end;

     if ParamStr(1)='-geoip' then begin
        writeln('Scan localizations of ' +ParamStr(2));
 q.ScanIPHeaders(ParamStr(2));
        writeln('end...');
        halt(0);
     end;

     if ParamStr(1)='-remto' then begin
          writeln('Try to remove ' + ParamStr(3) + ' in ' + ParamStr(2));
 q.RemoveMailTo(ParamStr(2),ParamStr(3),ParamStr(4));
          halt(0);
     end;




     if FileExists(ParamStr(1)) then begin
 Q:=TscanQueue.Create;
            if ParamStr(2)='emailrelay-scan' then begin
 q.eMailRelay_filter(ParamStr(1));
               halt(0);
            end;

 q.eMailRelay_scan(trim(ParamStr(1)));
            halt(0);
     end;






 Q:=TscanQueue.Create;
 q.Execute(ParamStr(1));
  halt(0);
   ;
end;
end.
