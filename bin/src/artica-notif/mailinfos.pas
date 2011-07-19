unit mailinfos;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,strutils,IniFiles,oldlinux,BaseUnix,Logs,RegExpr in 'RegExpr.pas',ldap,mimemess, mimepart,md5,geoip;

type

  TTMailInfo = record //shared data between component, listen thread and handler
    mail_from:String;
    mail_from_name:string;
    mail_to:string;
    real_mailfrom:string;
    real_mailfrom_domain:string;
    X_SpamTest_Rate:integer;
    X_SpamTest_Info:string;
    MessageID:string;
    MessageSize:string;
    MessageDate:string;
    MessageMD5:string;
    Subject:string;
    FilePath:string;
    FilePath_source:string;
    HeadFilePath:string;
    Artica_results:string;
    GoInQuarantine:boolean;
    KillMail:boolean;
    viruses_list:string;
    GoInVirusQuarantine:boolean;
    ldap_uid:string;
    ldap_ou:string;
    AsEmailRelay:boolean;
    GeoCountry:string;
    GeoCity:string;
    GeoIP:string;
    GeoISP:string;
    RELAIS:array of string;
    RecipientsList:array of string;
    email_relay_path:string;
    uribl:array of string;
    FullMessage:TstringList;
    InPutMethod:boolean;
    InputMethodSend:boolean;
    dspam_result:string;
    dspam_class:string;
    dspam_probability:string;
    dspam_confidence:string;
    dspam_signature:string;
    queue_path:string;
    MessageSizeInt:integer;

  end;

type
  TMailInfo=class




private
   LOGS:Tlogs;
  queue_path:string;
  function MD5FromString(values:string):string;
  function ArticaFilterQueuePath():string;
  procedure EmailRelay_RebuildHeaderfromFile(messagepath:string);
  function FileSizeNum(path:string):integer;
  function MessageDateForMysql(MDate:string;stime:string;twice:boolean):string;
  function LookupURL(Mime:TMimeMess;msg:TTMailInfo):TTMailInfo;
  function LookupURLIsUriAlreadyUsed(msg:TTMailInfo;server:string):boolean;
public
      X_SpamTest_Rate:Integer;
      function RemoveMailTo(SourcePath:string;address:string;DestPath:string):string;
      constructor Create();
      procedure Free;
      function Scan(FilePath:string;messages:TTMailInfo):TTMailInfo;
      messages_infos:TTMailInfo;
      function LookupCountry(IP:string):string;
      function LookupCity(IP:string):string;
      function LookupOrg(IP:string):string;


      
END;

implementation

constructor TMailInfo.Create();
begin
LOGS:=TLogs.Create;
queue_path:=ArticaFilterQueuePath() + '/queue';

end;
PROCEDURE TMailInfo.Free();
begin
LOGS.Free;
end;
//#########################################################################################
function TMailInfo.RemoveMailTo(SourcePath:string;address:string;DestPath:string):string;
var
   Mime:TMimeMess;
   i:integer;
   RegExpr:TRegExpr;
   Z,F:boolean;
   Logs:Tlogs;
begin
     Z:=false;
     F:=False;
     if ParamStr(1)='-remto' then Z:=true;
     Logs:=TLogs.Create;
     Mime:=TMimeMess.Create;
     
     Mime.Lines.LoadFromFile(SourcePath);
     Mime.DecodeMessage;
     RegExpr:=TRegExpr.Create;
     address:=AnsiReplaceText(address,'.','\.');
     RegExpr.Expression:=address;
     for i:=0 to Mime.Header.ToList.Count-1 do begin;
         if Z then writeln('-> ',Mime.Header.ToList.Strings[i]);
           if RegExpr.Exec(Mime.Header.ToList.Strings[i]) then begin
               F:=True;
               if Z then writeln('Found ',Mime.Header.ToList.Strings[i]);
               Logs.logs('RemoveMailTo: found ' + address + ' in '+SourcePath + ' -> delete it'  );
               Mime.Header.ToList.Delete(i);
               break;
           end;
     end;
     Mime.EncodeMessage;
     if Z then writeln('save into  ',DestPath);
     if F then Mime.Lines.SaveToFile(DestPath);
     Mime.Free;
     RegExpr.free;
    
end;
//#########################################################################################

function TMailInfo.Scan(FilePath:string;messages:TTMailInfo):TTMailInfo;
var
   LIST:TStringList;
   i,z:integer;
   Mime:TMimeMess;
   HEADERLIST:TstringList;
   TRANSPORT_TABLE:TstringList;
   XSpamTestRate,tempto:string;
   AntiSpamRate:integer;
   X_SpamTest_Info:string;
   HeadFilePath,tmp_file:String;
   RegExpr:TRegExpr;
   file_ext:string;
   s:string;
   AsEmailRelay:boolean;
   D,ADD:Boolean;
   GeoIP: TGeoIP;
   GeoIPCountry: TGeoIPCountry;
   GEO:TstringList;
   Country,City,Org,IP:string;
   message_md5:string;
   Logs:Tlogs;
begin
   D:=false;
   if ParamStr(1)='-geoip' then begin
      D:=True;
      writeln('Debug mode...');
   end;
    Logs:=Tlogs.create;
    messages.AsEmailRelay:=false;
    messages.FilePath:=FilePath;
    messages.email_relay_path:=FilePath;
    messages.GoInQuarantine:=false;
    messages.GoInVirusQuarantine:=false;
    messages.KillMail:=false;
    messages.Artica_results:='send';
    messages.real_mailfrom_domain:='unknown';
    AntiSpamRate:=0;
    
    TRANSPORT_TABLE:=TStringList.Create;
    LIST:=TStringList.Create;
    LOGS:=Tlogs.Create;
    RegExpr:=TRegExpr.Create;
    Mime:=TMimeMess.Create;
    
    
// File method -----------------------------------------------------------------
    if messages.InPutMethod=false then begin
            LOGS.ArticaSend('Input method=false');
            file_ext:=ExtractFileExt(FilePath);
            logs.ArticaSend('Extension=' + file_ext);
            if file_ext='.content' then  messages.AsEmailRelay:=true;
            if ParamStr(2)='emailrelay-scan' then messages.AsEmailRelay:=true;
            result:=messages;

            if Not FileExists(FilePath) then begin
                   if D then writeln('Unable to stat ' + FilePath);
                   exit;
            end;



            if messages.AsEmailRelay=true then begin
               HeadFilePath:=ChangeFileExt(FilePath,'.envelope.new');
               messages.HeadFilePath:=HeadFilePath;

               if not FileExists(HeadFilePath) then begin
                      logs.ArticaSend('Unable to stat ' + HeadFilePath);
                      tmp_file:=ChangeFileExt(FilePath,'.envelope.bad');
                      logs.ArticaSend('Get header from source location ' + tmp_file);

                      if FileExists(tmp_file) then begin messages.HeadFilePath:=tmp_file;
                      end else begin
                          logs.ArticaSend('unable to get headers from ' + FilePath);
                          EmailRelay_RebuildHeaderfromFile(FilePath);
                          messages.HeadFilePath:=HeadFilePath;
                      end;
               end;


           end else begin
               HeadFilePath:=ChangeFileExt(FilePath,'.head');

               if Not FileExists(HeadFilePath) then begin
                  logs.ArticaSend('TMailInfo::Scan [' + FilePath + '] unable to stat ' + HeadFilePath);
                  ForceDirectories(ArticaFilterQueuePath() + '/failed');
                  Shell('/bin/mv ' + FilePath + ' ' + ArticaFilterQueuePath() + '/failed');
                  exit;
               end;
            end;


            LIST.LoadFromFile(FilePath);
            if Not FileExists(HeadFilePath) then begin
               logs.ArticaSend('unable to stat headers from ' + HeadFilePath);
               if  messages.AsEmailRelay=true then begin
                   EmailRelay_RebuildHeaderfromFile(FilePath);
               end else begin
                   exit;
               end;
            end;
    
            TRANSPORT_TABLE.LoadFromFile(HeadFilePath);
    
            if messages.AsEmailRelay=false then begin
                 RegExpr.Expression:='MAIL_FROM=(.+)';
                 for i:=0 to TRANSPORT_TABLE.Count -1 do begin
                     if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
                        messages.real_mailfrom:=RegExpr.Match[1];
                        break;
                     end;
                 end;


                 RegExpr.Expression:='MAIL_TO=(.+)';
                 for i:=0 to TRANSPORT_TABLE.Count -1 do begin
                     if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
                     messages.mail_to:=messages.mail_to+RegExpr.Match[1] + ',';
                 end;
              end;
         end;



//---------------------------- get the from and to -----------------------------

 if messages.AsEmailRelay=true then begin
        RegExpr.Expression:='X-MailRelay-From:\s+(.+)';
        for i:=0 to TRANSPORT_TABLE.Count -1 do begin
            if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
               messages.real_mailfrom:=RegExpr.Match[1];
               break;
            end;
        end;

        SetLength(messages.RecipientsList, 0);
        RegExpr.Expression:='X-MailRelay-To-Remote:\s+(.+)';


        for i:=0 to TRANSPORT_TABLE.Count -1 do begin
            if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
                messages.mail_to:=LowerCase(RegExpr.Match[1]);
                break;
            end;
        end;


        for i:=0 to TRANSPORT_TABLE.Count -1 do begin
            if RegExpr.Exec(TRANSPORT_TABLE.Strings[i]) then begin
                  SetLength(messages.RecipientsList, length(messages.RecipientsList)+1);
                  messages.RecipientsList[length(messages.RecipientsList)-1]:=LowerCase(RegExpr.Match[1]);
            end;
        end;
  end;
//------------------------------------------------------------------------------
    
    
end;
    
// File method -----------------------------------------------------------------
    if messages.InPutMethod=True then begin
       Mime.Lines.AddStrings(messages.FullMessage);
       LOGS.ArticaSend('Mime Parsing ' + IntToStr(Mime.Lines.Count) + ' lines');
    end else begin
        Mime.Lines.AddStrings(LIST);
    end;
    
    
    Mime.DecodeMessage;
    HEADERLIST:=TStringList.Create;
    
// ----------------------- get uris in the body --------------------------------
   messages:=LookupURL(Mime,messages);
//------------------------------------------------------------------------------
    
    
    for i:=0 to Mime.Header.ToList.Count-1 do begin;
        RegExpr.Expression:='<(.+?)>';
        if RegExpr.Exec(Mime.Header.ToList.Strings[i]) then begin
           tempto:=RegExpr.Match[1];
        end else begin
             tempto:=Mime.Header.ToList.Strings[i];

        end;
        SetLength(messages.RecipientsList, length(messages.RecipientsList)+1);
         messages.RecipientsList[length(messages.RecipientsList)-1]:=tempto;
    end;

    messages.mail_from_name:=Mime.Header.From;
    messages.mail_from:=Mime.Header.From;
    
    XSpamTestRate:=Mime.Header.FindHeader('X-SpamTest-Rate');
    if length(XSpamTestRate)>0 then AntiSpamRate:=StrToInt(XSpamTestRate);
    
    Mime.Header.FindHeaderList('Received',HEADERLIST);
    RegExpr.Expression:='\[([0-9\.]+)\]';
    GEO:=TStringList.Create;
    SetLength(messages.RELAIS, 0);
    for i:=0 to HEADERLIST.Count-1 do begin
        if RegExpr.Exec(HEADERLIST.Strings[i]) then begin
           if RegExpr.Match[1]<>'127.0.0.1' then begin
              ADD:=True;
              for z:=0 to GEO.Count-1 do begin
                    if GEO.Strings[z]=RegExpr.Match[1] then ADD:=False;
              end;
              
              if ADD=True then begin
                  GEO.Add(RegExpr.Match[1]);
                  SetLength(messages.RELAIS, length(messages.RELAIS)+1);
                  messages.RELAIS[length(messages.RELAIS)-1]:=RegExpr.Match[1];
               end;
           end;
        end;
    end;

    if GEO.Count>0 then begin
         IP:=GEO.Strings[GEO.Count-1];
         Country:=LookupCountry(IP);
         City:=LookupCity(IP);
         Org:=LookupOrg(IP);
         if D then writeln('GeoIP result (' + IntToStr(GEO.Count-1) + '): ' + Country + ';' + City + ';' +  Org);
    end;
    
    if Country='unknown' then begin
      if GEO.Count-2>0 then begin
         IP:=GEO.Strings[GEO.Count-2];
         Country:=LookupCountry(IP);
         City:=LookupCity(IP);
         Org:=LookupOrg(IP);
         if D then writeln('GeoIP result (' + IntToStr(GEO.Count-2) + '): ' + Country + ';' + City + ';' +  Org);
      end;
    end;
    messages.GeoCity:=City;
    messages.GeoCountry:=Country;
    messages.GeoISP:=Org;
    messages.GeoIP:=IP;

    
    
    

    if length(messages.mail_to)=0 then begin
         Mime.Header.FindHeaderList('Received',HEADERLIST);
         RegExpr.Expression:='for <(.+?)@(.+?)>';
         for i:=0 to HEADERLIST.Count-1 do begin
              if RegExpr.Exec(HEADERLIST.Strings[i]) then begin
                 messages.mail_to:=RegExpr.Match[1]+'@' +RegExpr.Match[2];
                 break;
              end;
         end;
    HEADERLIST.Clear;

    end;

    
    messages.X_SpamTest_Rate:=AntiSpamRate;


    RegExpr.Expression:='\{(.+?)\}';
    Mime.Header.FindHeaderList('X-SpamTest-Info',HEADERLIST);
     for i:=0 to  HEADERLIST.Count-1 do begin
         if RegExpr.Exec(trim(HEADERLIST.Strings[i])) then messages.X_SpamTest_Info:=messages.X_SpamTest_Info + ',' + RegExpr.Match[1];
      end;


      messages.MessageID:=Mime.Header.MessageID;
      messages.MessageMD5:=MD5FromString(Mime.Lines.Text);
      messages.MessageSize:=IntTOStr(FileSizeNum(messages.FilePath));
      messages.Subject:=Mime.Header.Subject;
      messages.MessageDate:=MessageDateForMysql(DateToStr(mime.Header.Date),TimeToStr(mime.Header.Date)+':00',false);
      
      RegExpr.Expression:='(.+?)@(.+)';
     if RegExpr.Exec(messages.real_mailfrom) then messages.real_mailfrom_domain:=RegExpr.Match[2];


   Mime.Clear;
   TRANSPORT_TABLE.Clear;
   LIST.Clear;
   Mime.Free;
   LOGS.FRee;
   result:=messages;




end;
//#########################################################################################
function TMailInfo.LookupURL(Mime:TMimeMess;msg:TTMailInfo):TTMailInfo;
var
   MaxLines:integer;
   ToLines:integer;
   RegExpr:TRegExpr;
   i:Integer;
begin
    MaxLines:=5000;
    if Mime.Lines.Count>MaxLines then ToLines:=MaxLines-1 else ToLines:=Mime.Lines.Count-1;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='(http|ftp|https)://([a-zA-Z0-9\.\-_@]+)';
    
    SetLength(msg.uribl, 0);
    
    For i:=0 to ToLines Do begin
        if RegExpr.Exec(Mime.Lines.Strings[i]) then begin
             if not LookupURLIsUriAlreadyUsed(msg,RegExpr.Match[2]) then begin
                SetLength(msg.uribl, length(msg.uribl)+1);
                msg.uribl[length(msg.uribl)-1]:=RegExpr.Match[2];
             end;
        end;
    
    end;
    RegExpr.Free;
    result:=msg;
    
end;
//#########################################################################################
function TMailInfo.LookupURLIsUriAlreadyUsed(msg:TTMailInfo;server:string):boolean;
var
i:integer;
begin
result:=false;
for i:=0 to length(msg.uribl)-1 do begin
    if server=msg.uribl[i] then begin
        result:=true;
        break;
    end;
    
end;
end;
//#########################################################################################







//#########################################################################################
function TMailInfo.LookupCountry(IP:string):string;
var
   GeoIP: TGeoIP;
   GeoIPCountry: TGeoIPCountry;
   files:string;
begin
  if not FileExists('/usr/local/share/GeoIP/GeoIP.dat') then exit('unknown');
  GeoIP := TGeoIP.Create('/usr/local/share/GeoIP/GeoIP.dat');
  try
    if GeoIP.GetCountry(IP, GeoIPCountry) = GEOIP_SUCCESS then
    begin
      Result := GeoIPCountry.CountryName;
    end
    else
    begin
      Result := 'unknown';
    end;
  finally
    GeoIP.Free;
  end;
end;
//#########################################################################################
function TMailInfo.LookupCity(IP:string):string;
var
   GeoIP: TGeoIP;
   GeoIPCity: TGeoIPCity;
   files:string;
begin
     files:='/usr/local/share/GeoIP/GeoLiteCity.dat';
     if not FileExists(files) then exit('unknown');
  GeoIP := TGeoIP.Create(files);
  try
    if GeoIP.GetCity(IP, GeoIPCity) = GEOIP_SUCCESS then
    begin
      Result := GeoIPCity.City;
    end
    else
    begin
      Result := 'unknown';
    end;
  finally
    GeoIP.Free;
  end;
end;
//#########################################################################################
function TMailInfo.LookupOrg(IP:string):string;
var
   GeoIP: TGeoIP;
   GeoIPOrg: TGeoIPOrg;
   files:string;
begin
     files:='/usr/local/share/GeoIP/GeoIPASNum.dat';
     if not FileExists(files) then exit('unknown');
  GeoIP := TGeoIP.Create(files);
  try
    if GeoIP.GetOrg(IP, GeoIPOrg) = GEOIP_SUCCESS then
    begin
      Result := GeoIPOrg.Name;
    end
    else
    begin
      Result := 'unknown';
    end;
  finally
    GeoIP.Free;
  end;
end;

//#########################################################################################
function TMailInfo.MD5FromString(values:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5String(values);
exit(MD5Print(Digest));
end;
//#########################################################################################
function TMailInfo.ArticaFilterQueuePath():string;
var ini:TIniFile;
begin
 ini:=TIniFile.Create('/etc/artica-postfix/artica-filter.conf');
 result:=ini.ReadString('INFOS','QueuePath','');
 if length(trim(result))=0 then result:='/usr/share/artica-filter';
end;
//##############################################################################
procedure TMailInfo.EmailRelay_RebuildHeaderfromFile(messagepath:string);
    var
       FILE_DATA:TstringList;
       i:integer;
       RegExpr:TRegExpr;
       mailto,mailfrom:string;
       zLOGS:Tlogs;
begin
  zLOGS:=Tlogs.Create;
  zLOGS.ArticaSend('retreive headers from ' + messagepath);
  FILE_DATA:=TstringList.Create;
  FILE_DATA.LoadFromFile(messagepath);
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='for <(.+?)>;';

  
  for i:=0 to FILE_DATA.Count -1 do begin
      if  RegExpr.Exec(FILE_DATA.Strings[i]) then begin
           mailto:=RegExpr.Match[1];
           break;
      end;
  end;
  
  
  
RegExpr.Expression:='^From:.+<(.+?)>';
  for i:=0 to FILE_DATA.Count -1 do begin
      if  RegExpr.Exec(FILE_DATA.Strings[i]) then begin
           mailfrom:=RegExpr.Match[1];
           break;
      end;
  end;
    RegExpr.free;
    zLOGS.ArticaSend('retreive <' + mailfrom + '> to <' + mailto + '> from ' + messagepath);
    FILE_DATA.Clear;
    FILE_DATA.Add('X-MailRelay-Format: #2821.3');
    FILE_DATA.Add('X-MailRelay-Content: 7bit');
    FILE_DATA.Add('X-MailRelay-From: ' + mailfrom);
    FILE_DATA.Add('X-MailRelay-ToCount: 1');
    FILE_DATA.Add('X-MailRelay-To-Remote: '+mailto);
    FILE_DATA.Add('X-MailRelay-Authentication:');
    FILE_DATA.Add('X-MailRelay-Client: 127.0.0.1');
    FILE_DATA.Add('X-MailRelay-End: 1');
    FILE_DATA.SaveToFile(ChangeFileExt(messagepath,'.envelope.new'));

    FILE_DATA.Clear;
    FILE_DATA.Free;
    zLOGS.free;

end;
//##############################################################################
function TMailInfo.FileSizeNum(path:string):integer;
 Var F : File Of byte;
 Size:longint;
begin
  if not FileExists(path) then exit(0);
  Try
  Assign (F,path);
  Reset (F);
  result:=FileSize(F);
  Close (F);
  except
  exit();
  end;
end;
//##############################################################################
function TMailInfo.MessageDateForMysql(MDate:string;stime:string;twice:boolean):string;
var
   i:integer;
   LOGS:TLogs;
   RegExpr:TRegExpr;
   YEAR,MONTH,DAY:string;
   D:boolean;
begin
   RegExpr:=TRegExpr.create;
   RegExpr.Expression:='([0-9]+)-([0-9]+)-([0-9]+)';
   if RegExpr.Exec(MDate) then begin
   if length(RegExpr.Match[3])=2 then YEAR:='20' + RegExpr.Match[3] else YEAR:=RegExpr.Match[3];
   if length(RegExpr.Match[2])=1 then MONTH:='0' + RegExpr.Match[2] else MONTH:=RegExpr.Match[2];
   if length(RegExpr.Match[1])=1 then DAY:='0' + RegExpr.Match[1] else DAY:=RegExpr.Match[1];
   // if D then writeln('Message Date: ' + YEAR+'-' + MONTH + '-' + DAY + ' ' + stime + ' (MessageDateForMysql)');
   result:=YEAR+'-' + MONTH + '-' + DAY + ' ' + stime;
   end else begin
         if D then writeln('Failed to parse ([0-9]+)-([0-9+])-([0-9]+) ->"' + MDate + '"');
         MDate:=DateToStr(Date);
         stime:=TimeToStr(Time);
         if twice=false then result:=MessageDateForMysql(MDate,stime,true);
   end;

end;

end.
