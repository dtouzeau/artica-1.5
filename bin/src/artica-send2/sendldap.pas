unit sendldap;

{$MODE DELPHI}
{$LONGSTRINGS ON}


interface

uses
  Classes, SysUtils,ldapsend,RegExpr in 'RegExpr.pas',IniFiles,strutils;
  
type
  TStringDynArray = array of string;
  
type

  bogofilter_settings=record
        max_rate:integer;
        prepend:string;
        action:string;
  end;
  
  htmlblock=record
       enabled        :string;
       config         :string;
       Rules          :TstringList;
       Disclaimer     :string;

  end;

  ldapinfos =record
       BlackList:TStringList;
       WhiteList:TStringList;
       user_dn:string;
       user_ou:string;
       uid:string;
       RBL_SERVER_ACTION:string;
       RBL_SERVERS:TStringList;
       BOGOFILTER_ROBOTS:TStringList;
       BOGOFILTER_ACTION:string;
       BOGOFILTER_PARAM:bogofilter_settings;
       MAILMAN_ROBOTS:TstringList;
       TrustMyUsers:string;
       html_block:htmlblock;
  end;
  

  


  
  
  type
  TSendldap=class
  

  private
       ldap_admin,ldap_password,ldap_suffix:string;
       function get_LDAP(key:string):string;
       function get_CONF(key:string):string;
       function Query_A(Query_string:string;return_attribute:string):TStringDynArray;
       DN_ROOT:string;
       function ParseResultInStringList(Items:TLDAPAttribute):TStringList;
       function SearchSingleAttribute(Items:TLDAPAttributeList;SearchAttribute:string):string;
       function SearchResults_single(SearchResults:TLDAPResultList;WhatToFind:string):string;
       function SearchResults_multiple(SearchResults:TLDAPResultList;WhatToFind:string):TstringList;
       function mailman_ou(distri:string):string;
  public
      constructor Create();
      destructor Destroy; override;
      function Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
      function EmailFromAliase(email:string):string;
      function LoadBlackList(email:string):string;
      function LoadASRules(email:string):string;
      function LoadAVRules(email:string):string;
      function LoadOUASRules(ou:string):string;
      function LoadOUCountriesDeny(ou:string):TStringDynArray;

      function LoadOURBL(ou:string):TStringDynArray;
      function LoadOUSURBLAction(ou:string):string;
      function LoadOUSURBL(ou:string):TStringDynArray;
       function Ldap_infos(email:string):ldapinfos;
      function LoadRenAttachPolicies(email:string):string;
      function LoadArticaUserRules(email:string):string;

      function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
      function Query(Query_string:string;return_attribute:string):string;
      function IsBlackListed(mail_from:string;mail_to:string):boolean;
      function IsLocalDomain(domainName:string):boolean;
      function uidFromMail(email:string):string;
      function eMailFromUid(uid:string):string;
      function OU_From_eMail(email:string):string;
      function QuarantineMaxDayByOu(Ou:string):string;
      function IsOuDomainBlackListed(Ou:string;domain:string):boolean;
      function FackedSenderParameters(Ou:string):string;
      function ArticaMaxSubQueueNumberParameter():integer;
      function ArticaDenyNoMXRecordsOu(Ou:string):string;
      function LoadFetchMailConfigs():TStringDynArray;
      procedure SaveFetchMailSettings();
      function OuLists():TStringDynArray;
      function implode(ArrayS:TStringDynArray):string;

      function LoadAllOu():string;
      SEARCH_DN:string;
      TEMP_LIST:TstringList;

end;

implementation

constructor Tsendldap.Create();
begin
   SEARCH_DN:='';
   ldap_admin:=get_LDAP('admin');
   ldap_password:=get_LDAP('password');
   ldap_suffix:=get_LDAP('suffix');
   TEMP_LIST:=TstringList.Create;
end;
//##############################################################################
destructor Tsendldap.Destroy;
begin
  TEMP_LIST.Free;
  inherited Destroy;
end;
//##############################################################################
function Tsendldap.OuLists():TStringDynArray;
var Myquery:string;
resultats:TStringDynArray;
begin
     Myquery:='(&(ObjectClass=organizationalUnit)(ou=*))';
     resultats:=Query_A(MyQuery,'ou');
     exit(resultats);
end;
//##############################################################################



//##############################################################################
function Tsendldap.LoadBlackList(email:string):string;
var

right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin
     right_email:=EmailFromaliase(email);
     D:=COMMANDLINE_PARAMETERS('black=');
     if D then writeln('Get list of black emails for "' + right_email + '"');
     Myquery:='(&(ObjectClass=ArticaSettings)(mail=' +right_email + '))';
     resultats:=trim(Query(MyQuery,'KasperkyASDatasDeny'));
     if D then writeln(resultats);
     exit(trim(resultats));
end;
//##############################################################################




function Tsendldap.eMailFromUid(uid:string):string;
var

right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin

     D:=COMMANDLINE_PARAMETERS('debug');
     if D then writeln('Get email of  for "' + uid + '"');
     Myquery:='(&(ObjectClass=userAccount)(uid=' +uid + '))';
     resultats:=Query(MyQuery,'mail');
     resultats:=trim(resultats);
     if D then writeln(resultats);
     exit(resultats);
end;
//##############################################################################
function Tsendldap.LoadArticaUserRules(email:string):string;
var
right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin
   right_email:=EmailFromaliase(email);
   Myquery:='(&(ObjectClass=ArticaSettings)(mail=' +right_email + '))';
   resultats:=Query(MyQuery,'ArticaUserFilterRule');
   exit(resultats);
end;
//##############################################################################
function Tsendldap.LoadRenAttachPolicies(email:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
         var
            right_email,Myquery,resultats,uid,ou:string;
            i:integer;
            D:boolean;
            PoliciesList:string;
            tmpres:TstringList;
begin
   D:=COMMANDLINE_PARAMETERS('-V');
   right_email:=EmailFromaliase(email);
   uid:=uidFromMail(right_email);
   ou:=OU_From_eMail(right_email);
   
   if length(ou)=0 then exit;
   
   SEARCH_DN:='ou=' + ou + ','+ ldap_suffix;
   if D then writeln('LoadRenAttachPolicies:: '+ email + '=<' + right_email + '> (' +ou + '\' + uid+') search in "' + SEARCH_DN + '"' );
   Myquery:='(&(ObjectClass=posixGroup)(memberUid=' + uid + '))';
   resultats:=Query(MyQuery,'FiltersExtensionsGroupName');
   
   tmpres:=TstringList.Create;
   tmpres.AddStrings(TEMP_LIST);
   for i:=0 to tmpres.Count -1 do begin
         if D then writeln('LoadRenAttachPolicies:: Check :"' + tmpres.Strings[i] + '"');
         Myquery:='(&(ObjectClass=FilterExtensionsGroup)(cn='+tmpres.Strings[i] +'))';
         resultats:=trim(Query(MyQuery,'FiltersExtensionsSettings'));
         if length(resultats)>0 then PoliciesList:=PoliciesList + '<rule><rulename>' + tmpres.Strings[i]  + '</rulename><datas>' + resultats + '</datas></rule>' + CRLF;
   
   end;

   TEMP_LIST.Clear;
   tmpres.Clear;
   tmpres.Free;
   exit(PoliciesList);
end;

function Tsendldap.LoadOUSURBLAction(ou:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
         var
            right_email,Myquery,resultats:string;
            i:integer;
            D:boolean;
            PoliciesList:string;
            tmpres:TStringDynArray;
begin
   TEMP_LIST.Clear;
   D:=COMMANDLINE_PARAMETERS('-V');
   if length(ou)=0 then exit;

   SEARCH_DN:='ou=' + ou + ','+ ldap_suffix;
   if D then writeln('LoadOUSURBLAction:: (' +ou + ') search in "' + SEARCH_DN + '"' );
   Myquery:='(&(ObjectClass=ArticaSettings)(ou=' + ou + '))';
   tmpres:=Query_A(MyQuery,'SURBLServersAction');
   if length(tmpres)=0 then exit;
   exit(tmpres[0]);


end;
//##############################################################################
function Tsendldap.LoadFetchMailConfigs():TStringDynArray;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
         var
            right_email,Myquery,resultats:string;
            i:integer;
            D:boolean;
            PoliciesList:string;
            tmpres:TstringList;
begin
   TEMP_LIST.Clear;
   D:=false;
   D:=COMMANDLINE_PARAMETERS('-V');
   if not D then if ParamStr(2)='dump' then D:=True;
   SEARCH_DN:=ldap_suffix;
   
   if D then writeln('LoadFetchMailConfigs::search in "' + SEARCH_DN + '"' );
   Myquery:='(&(ObjectClass=ArticaSettings))';
   result:=Query_A(MyQuery,'FetchMailsRules');
   if D then begin
      writeln('TStringDynArray; rows:', length(result));
      for i:=0 to length(result)-1 do begin
          writeln(result[i]);
      end;
   end;
   exit();


end;
//##############################################################################
procedure Tsendldap.SaveFetchMailSettings();
var
   ARR:TStringDynArray;
   F:TStringList;
   i:integer;
   fetchmail_daemon_pool,fetchmail_daemon_postmaster:string;
begin
   fetchmail_daemon_pool:=get_CONF('fetchmail_daemon_pool');
   fetchmail_daemon_postmaster:=get_CONF('fetchmail_daemon_postmaster');
   if length(fetchmail_daemon_pool)=0 then fetchmail_daemon_pool:='600';
   if length(fetchmail_daemon_postmaster)=0 then fetchmail_daemon_postmaster:='root';
   F:=TStringList.Create;
   F.Add('set daemon '+fetchmail_daemon_pool);
   F.Add('set postmaster "'+fetchmail_daemon_postmaster +'"');
   ARR:=LoadFetchMailConfigs();
    F.Add('');
   for i:=0 to length(ARR)-1 do begin
          F.Add(ARR[i]);
          F.Add('');
   end;
   
   
   if ParamStr(3)='simulate' then begin
      writeln(F.Text);
      exit;
   end;
   
    F.SaveToFile('/etc/fetchmailrc');

end;
//##############################################################################


//##############################################################################
function Tsendldap.LoadOURBL(ou:string):TStringDynArray;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
         var
            right_email,Myquery,resultats:string;
            i:integer;
            D:boolean;
            PoliciesList:string;
            tmpres:TstringList;
begin
   TEMP_LIST.Clear;
   D:=COMMANDLINE_PARAMETERS('-V');
   if length(ou)=0 then exit;

   SEARCH_DN:='ou=' + ou + ','+ ldap_suffix;
   if D then writeln('LoadOURBL:: (' +ou + ') search in "' + SEARCH_DN + '"' );
   Myquery:='(&(ObjectClass=ArticaSettings)(ou=' + ou + '))';
   result:=Query_A(MyQuery,'RblServers');
   if D then writeln('TStringDynArray; rows:', length(result));
   exit();


end;
//##############################################################################
function Tsendldap.LoadOUSURBL(ou:string):TStringDynArray;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
         var
            right_email,Myquery,resultats:string;
            i:integer;
            D:boolean;
            PoliciesList:string;
            tmpres:TstringList;
begin
   TEMP_LIST.Clear;
   D:=COMMANDLINE_PARAMETERS('-V');
   if length(ou)=0 then exit;

   SEARCH_DN:='ou=' + ou + ','+ ldap_suffix;
   if D then writeln('LoadOUSURBL:: (' +ou + ') search in "' + SEARCH_DN + '"' );
   Myquery:='(&(ObjectClass=ArticaSettings)(ou=' + ou + '))';
   result:=Query_A(MyQuery,'SURBLServers');
   if D then writeln('TStringDynArray; rows:', length(result));
   exit();


end;
//##############################################################################
function Tsendldap.LoadOUCountriesDeny(ou:string):TStringDynArray;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
         var
            right_email,Myquery,resultats:string;
            i:integer;
            D:boolean;
            PoliciesList:string;
            tmpres:TstringList;
begin
   D:=COMMANDLINE_PARAMETERS('-V');
   if length(ou)=0 then exit;
   SEARCH_DN:='ou=' + ou + ','+ ldap_suffix;
   if D then writeln('LoadOUCountriesDeny:: (' +ou + ') search in "' + SEARCH_DN + '"' );
   Myquery:='(&(ObjectClass=ArticaSettings)(ou=' + ou + '))';
   result:=Query_A(MyQuery,'CountryDeny');

   exit();


end;
//##############################################################################
function Tsendldap.IsLocalDomain(domainName:string):boolean;
var
Myquery,resultats:string;
begin
    result:=false;
    Myquery:='(&(ObjectClass=organizationalUnit)(associatedDomain=' + domainName+'))';
    resultats:=Query(MyQuery,'associatedDomain');
    if length(resultats)>0 then exit(true);
    SEARCH_DN:='cn=transport_map,cn=artica,' + ldap_suffix;
    Myquery:='(&(ObjectClass=transportTable)(cn=' + domainName+'))';
    resultats:=Query(MyQuery,'cn');
    if length(resultats)>0 then exit(true);
end;
//##############################################################################
function Tsendldap.LoadAllOu():string;
var
right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin

   Myquery:='(&(ObjectClass=organizationalUnit)(ou=*))';
   resultats:=Query(MyQuery,'ou');
   exit(resultats);
end;
//##############################################################################
function Tsendldap.ArticaDenyNoMXRecordsOu(Ou:string):string;
var
right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin

   Myquery:='(&(ObjectClass=organizationalUnit)(ou=' + ou + '))';
   resultats:=Query(MyQuery,'ArticaDenyNoMXRecords');
   resultats:=trim(resultats);
   if length(resultats)=0 then resultats:='pass';
   exit(resultats);
   
end;
//##############################################################################
function Tsendldap.QuarantineMaxDayByOu(Ou:string):string;
var
right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin

   Myquery:='(&(ObjectClass=organizationalUnit)(ou=' + ou + '))';
   resultats:=Query(MyQuery,'ArticaMaxDayQuarantine');
   exit(trim(resultats));
end;
//##############################################################################
function Tsendldap.IsOuDomainBlackListed(Ou:string;domain:string):boolean;
var
right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin
   result:=false;
   SEARCH_DN:='cn=blackListedDomains,ou=' + ou + ',' + ldap_suffix;
   Myquery:='(&(ObjectClass=DomainsBlackListOu)(cn='+domain+'))';
   resultats:=trim(Query(MyQuery,'cn'));
   if length(resultats)>0 then exit(true);
   
end;
//##############################################################################
function Tsendldap.FackedSenderParameters(Ou:string):string;
var
resultats,Myquery:string;
begin
   result:='pass';
   SEARCH_DN:='ou=' + ou + ',' + ldap_suffix;
   Myquery:='(&(ObjectClass=ArticaSettings)(ArticaFakedMailFrom=*))';
   resultats:=trim(Query(MyQuery,'ArticaFakedMailFrom'));
   if length(resultats)=0 then result:='pass' else result:=resultats;

end;
//##############################################################################
function Tsendldap.ArticaMaxSubQueueNumberParameter():integer;
var
resultats,Myquery:string;
begin
   result:=5;
   SEARCH_DN:='cn=artica,' + ldap_suffix;
   Myquery:='(&(ObjectClass=ArticaSettings)(ArticaMaxSubQueueNumber=*))';
   resultats:=trim(Query(MyQuery,'ArticaMaxSubQueueNumber'));
   if length(resultats)=0 then resultats:='5';
   result:=StrToInt(resultats);
end;
//##############################################################################





function Tsendldap.LoadASRules(email:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
RegExpr:TRegExpr;
right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin
     right_email:=EmailFromaliase(email);
     D:=COMMANDLINE_PARAMETERS('asrules=');
     if D then writeln('Get list of Kaspersky antispam rules for "' + right_email + '"');
     Myquery:='(&(ObjectClass=ArticaSettings)(mail=' +right_email + '))';
     resultats:=Query(MyQuery,'KasperkyASDatasRules');
     if trim(resultats)='DEFAULT' then begin
          RegExpr:=TRegExpr.Create;
          RegExpr.Expression:='ou=(.+?),.+';
          if RegExpr.Exec(DN_ROOT) then resultats:=LoadOUASRules(RegExpr.Match[1]);
     end;
     if trim(resultats)='DEFAULT' then begin
            resultats:='detection_rate="45"' + CRLF;
            resultats:=resultats+ 'action_quarantine="1"' + CRLF;
            resultats:=resultats+ 'action_killmail="1"' + CRLF;
            resultats:=resultats+ 'action_prepend="0"' + CRLF;
            resultats:=resultats+ 'second_rate="90"' + CRLF;
            resultats:=resultats+ 'second_quarantine="0"' + CRLF;
            resultats:=resultats+ 'second_killmail="1"' + CRLF;
            resultats:=resultats+ 'second_prepend="0"' + CRLF;
            
     
     end;
     
     if D then writeln(resultats);
     exit(resultats);
end;
//##############################################################################
function Tsendldap.OU_From_eMail(email:string):string;
var
   RegExpr:TRegExpr;
   right_email,Myquery,resultats:string;
   i,t,u:integer;
   D:boolean;
   F:boolean;
begin
     D:=COMMANDLINE_PARAMETERS('whereis=');
     F:=COMMANDLINE_PARAMETERS('debug');

    if F then writeln('OU_From_eMail: ' + email );
    right_email:=EmailFromaliase(email);
    if D then writeln('Where is "' + right_email + '" ?');
    Myquery:='(&(ObjectClass=userAccount)(mail=' +right_email + '))';
    if F then writeln('OU_From_eMail: ' + Myquery );
    resultats:=Query(MyQuery,'ObjectName');
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='ou=(.+?),.+';
    if RegExpr.Exec(resultats) then result:=RegExpr.Match[1];
end;


//##############################################################################
function Tsendldap.LoadAVRules(email:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
RegExpr:TRegExpr;
right_email,Myquery,resultats,ou:string;
i,t,u:integer;
D:boolean;
begin
     right_email:=EmailFromaliase(email);
     ou:=OU_From_eMail(right_email);
     D:=COMMANDLINE_PARAMETERS('avrules=');
     if D then writeln('Get list of Kaspersky antivirus rules for "' + ou + '"');
     Myquery:='(&(ObjectClass=ArticaSettings)(ou=' +ou + '))';
     resultats:=Query(MyQuery,'KasperkyAVScanningDatas');
     if trim(resultats)='DEFAULT' then begin
     resultats:='NotifyFromAddress="postmaster"' + CRLF;
     resultats:=resultats+ 'DeleteDetectedVirus="1"' + CRLF;
     resultats:=resultats+ 'NotifyFrom="1"' + CRLF;
     resultats:=resultats+ 'NotifyTo="1"' + CRLF;
     resultats:=resultats+ 'ArchiveMail="1"' + CRLF;
     resultats:=resultats+ 'NotifyMessageSubject="%SUBJECT%"' + CRLF;
     resultats:=resultats+ '<NotifyMessageTemplate><p><font face="arial,helvetica,sans-serif" size="4" color="#ff0000">Warning !!</font></p>';
     resultats:=resultats+ '<p>The message %SUBJECT% sended by %SENDER% For %MAILTO% was infected please, try to send your messages without any viruses.</p><p><strong>Virus detected</strong> :</p><blockquote><p>%VIRUS% !!!<br /> </p></blockquote></NotifyMessageTemplate>' + CRLF;
     end;

     if D then writeln(resultats);
     exit(resultats);
end;
//##############################################################################
function Tsendldap.LoadOUASRules(ou:string):string;
var

right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin

     D:=COMMANDLINE_PARAMETERS('asrules=');
     if D then writeln('Get list of Kaspersky antispam rules for "' + ou + '"');
     Myquery:='(&(ObjectClass=ArticaSettings)(ou=' +ou + '))';
     resultats:=Query(MyQuery,'KasperkyASDatasRules');
     exit(resultats);
end;
//##############################################################################
function Tsendldap.uidFromMail(email:string):string;
var

right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin

     D:=COMMANDLINE_PARAMETERS('uid=');
     right_email:=EmailFromaliase(email);
     Myquery:='(&(ObjectClass=userAccount)(mail=' +right_email + '))';
     resultats:=trim(Query(MyQuery,'uid'));
     exit(resultats);
end;

function Tsendldap.IsBlackListed(mail_from:string;mail_to:string):boolean;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   QueryDatabase:TStringDynArray;
   i:integer;
   blocked:string;
   D:boolean;
   RegExpr:TRegExpr;
begin
result:=false;
mail_to:=EmailFromaliase(LowerCase(mail_to));
mail_from:=LowerCase(mail_from);
D:=COMMANDLINE_PARAMETERS('debug');

  QueryDatabase:=Explode(CRLF,LoadBlackList(mail_to));
  if Length(QueryDatabase)=0 then begin
     if D then writeln('"' + mail_to + '" has no black list entries');
     exit(false);
  end;
  
  for i:=0 to Length(QueryDatabase)-1 do begin
        blocked:=LowerCase(QueryDatabase[i]);
        if D then writeln('IsBlackListed:: blocked="' + blocked + '"');
        if blocked=mail_from then exit(true);
        if Pos('*',blocked)>0 then begin
           RegExpr:=TRegExpr.Create;
           blocked:=AnsiReplaceText(blocked,'*','.+');
           if D then writeln('IsBlackListed:: RegExpr="' + Blocked + '"');
           RegExpr.Expression:=blocked;
           if RegExpr.Exec(mail_from) then begin
              RegExpr.Free;
              exit(true);
           end;
        end;
           
        
        
  end;
  
  if D then writeln('IsBlackListed:: Done');

end;
 //######################################################################################
function Tsendldap.mailman_ou(distri:string):string;
var
   l:TStringList;
   i,t:integer;
   AttributeNameQ:string;
   ldap:TLDAPSend;
begin
   ldap :=  TLDAPSend.Create;
     ldap.TargetHost := '127.0.0.1';
     ldap.TargetPort := '389';
     ldap.UserName := ldap_admin;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;
     
 if not ldap.Login then begin
        ldap.Free;
        exit();
     end;

   result:='';
   l:=TStringList.Create;
   l.Add('MailManOuOwner');
   if ldap.Search('cn=artica,'+ldap_suffix , False, '(&(objectclass=ArticaMailManClass)(cn=' + distri + '))', l) then begin
    if ldap.SearchResult.Count>0 then begin
         for i:=0 to ldap.SearchResult.Count -1 do begin
              for t:=0 to ldap.SearchResult.Items[i].Attributes.Count -1 do begin
                  AttributeNameQ:=LowerCase(ldap.SearchResult.Items[i].Attributes[t].AttributeName);
                  if AttributeNameQ='mailmanouowner' then result:=ldap.SearchResult.Items[i].Attributes.Items[t].Strings[0];
              end;
         
         end;
    
    
    end;
    
   end;

   ldap.Logout;
   ldap.free;

end;



 //######################################################################################
function Tsendldap.Ldap_infos(email:string):ldapinfos;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   ldap:TLDAPSend;
   l:TStringList;
   i,t,u:integer;
   D,Z:boolean;
   value_result:string;
   AttributeNameQ:string;
   USER_DN:string;
   RES:ldapinfos;
   Query_string:string;
   return_attribute:string;
   DN_ROOT:string;
   RegExpr:TRegExpr;
begin
     D:=false;
     D:=COMMANDLINE_PARAMETERS('debug');
     Z:=COMMANDLINE_PARAMETERS('q=');
     ldap :=  TLDAPSend.Create;
     ldap.TargetHost := '127.0.0.1';
     ldap.TargetPort := '389';
     ldap.UserName := ldap_admin;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;

     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;

    RES.RBL_SERVERS:=TStringList.Create;
    RES.BOGOFILTER_ROBOTS:=TStringList.Create;
    RES.WhiteList:=TStringList.Create;
    RES.html_block.Rules:=TstringList.Create;
    
    USER_DN:='';
    value_result:='';
    result:=RES;
    ldap.Bind;
    l:=TstringList.Create;
    RegExpr:=TRegExpr.Create;

    
    
    // ***************************************************************** user
    
  RES.MAILMAN_ROBOTS:=TStringList.Create;
    //*************** Mailman ******************************************************
    SEARCH_DN:='cn=mailman,cn=artica,'+ ldap_suffix;
    if ldap.Search(SEARCH_DN, False, '(&(objectclass=ArticaMailManRobots)(cn=*))', l) then begin
       if ldap.SearchResult.Count>0 then begin
              for i:=0 to ldap.SearchResult.Count -1 do begin
                 for t:=0 to ldap.SearchResult.Items[i].Attributes.Count -1 do begin
                     AttributeNameQ:=LowerCase(ldap.SearchResult.Items[i].Attributes[t].AttributeName);
                     USER_DN:=ldap.SearchResult.Items[i].ObjectName;

                     if AttributeNameQ='objectclass' then begin
                          RES.MAILMAN_ROBOTS.Add('robot');
                     end;
                     value_result:=ldap.SearchResult.Items[i].Attributes.Items[t].Strings[0];

                     if AttributeNameQ='cn' then begin
                        RES.MAILMAN_ROBOTS.Strings[RES.MAILMAN_ROBOTS.Count-1]:=RES.MAILMAN_ROBOTS.Strings[RES.MAILMAN_ROBOTS.Count-1] + ';cn="'+value_result + '"';

                     end;
                     
                     if AttributeNameQ='mailmandistrilist' then begin
                        RES.user_ou:=mailman_ou(value_result);
                        RES.uid:=value_result;
                        RES.MAILMAN_ROBOTS.Strings[RES.MAILMAN_ROBOTS.Count-1]:=RES.MAILMAN_ROBOTS.Strings[RES.MAILMAN_ROBOTS.Count-1] + ';mailmandistrilist="'+value_result + '";ou="' + RES.user_ou + '"';
                     end;

                        
                     if AttributeNameQ='mailmanrobottype' then RES.MAILMAN_ROBOTS.Strings[RES.MAILMAN_ROBOTS.Count-1]:=RES.MAILMAN_ROBOTS.Strings[RES.MAILMAN_ROBOTS.Count-1] + ';mailmanrobottype="'+value_result + '"';

                        

                     

                 end;
              end;


       end;
    end;





//=====================================================================================================================
if length(RES.user_ou)=0 then begin
    ldap.Search(ldap_suffix, False, '(&(objectclass=userAccount)(mailAlias=' + email+'))', l);
    if D then writeln('(&(objectclass=userAccount)(mailAlias=' + email+')) count :',ldap.SearchResult.Count);
    if ldap.SearchResult.Count>0 then begin
       if D then writeln('ldap.SearchResult.Items[0].ObjectName=',ldap.SearchResult.Items[0].ObjectName);
       RES.user_dn:=ldap.SearchResult.Items[0].ObjectName;
    end else begin
        ldap.Search(ldap_suffix, False, '(&(objectclass=userAccount)(mail=' + email+'))', l);
        if ldap.SearchResult.Count>0 then RES.user_dn:=ldap.SearchResult.Items[0].ObjectName;
    end;

    if length(RES.user_dn)=0 then begin
          ldap.Search(ldap_suffix, False, '(&(objectclass=userAccount)(SenderCanonical=' + email+'))', l);
          if ldap.SearchResult.Count>0 then RES.user_dn:=ldap.SearchResult.Items[0].ObjectName;
    end;


    if length(RES.user_dn)=0 then begin
       ldap.Logout;
       ldap.Free;
       exit(res);
    end;
    

    RegExpr.Expression:='.+?ou=(.+?),';
    if RegExpr.Exec(RES.user_dn) then RES.user_ou:=RegExpr.Match[1];

    if length(RES.user_ou)=0 then begin
        ldap.Logout;
       ldap.Free;
       exit(res);
    end;
    
    
    
    
    RES.uid:=SearchSingleAttribute(ldap.SearchResult.Items[0].Attributes,'uid');

     for i:=0 to ldap.SearchResult.Count -1 do begin
       for t:=0 to ldap.SearchResult.Items[i].Attributes.Count -1 do begin
                AttributeNameQ:=LowerCase(ldap.SearchResult.Items[i].Attributes[t].AttributeName);
                if AttributeNameQ=LowerCase('KasperkyASDatasAllow') then RES.WhiteList.AddStrings(ParseResultInStringList(ldap.SearchResult.Items[i].Attributes.Items[t]));
        end;
     
     end;

end;
//=====================================================================================================================

    

    SEARCH_DN:='ou='+RES.user_ou + ',' + ldap_suffix;
    //----------- HTML Block -------------------------------------------------------------------
    if ldap.Search(SEARCH_DN, False, '(objectclass=ArticaOuBigMailHTML)', l) then begin

        RES.html_block.enabled:=SearchResults_single(ldap.SearchResult,'BigMailHTMLEnabled');
        RES.html_block.config:=SearchResults_single(ldap.SearchResult,'BigMailHtmlConfig');
        RES.html_block.Rules.AddStrings(SearchResults_multiple(ldap.SearchResult,'BigMailHtmlRules'));
        RES.html_block.Disclaimer:=SearchResults_single(ldap.SearchResult,'BigMailHtmlBody');
        
    end;
    //----------- HTML Block -------------------------------------------------------------------
    
    
    
    if ldap.Search(SEARCH_DN, False, '(&(objectclass=ArticaBogoFilterAdmin)(BogoFilterMailType=*))', l) then begin
        for i:=0 to ldap.SearchResult.Count -1 do begin
             RES.BOGOFILTER_ROBOTS.Add(SearchSingleAttribute(ldap.SearchResult.Items[i].Attributes,'mail') + ';' +SearchSingleAttribute(ldap.SearchResult.Items[i].Attributes,'BogoFilterMailType'));
        end;
    end;
    
    
    
    if D then writeln('Ldap_infos:: (&(ObjectClass=ArticaSettings)(ou=' + RES.user_ou + ')');

    l.Add('*');
    Query_string:='(&(ObjectClass=ArticaSettings)(ou=' + RES.user_ou + '))';

    
    if not ldap.Search(SEARCH_DN, False, Query_string, l) then begin
       if D then writeln('Ldap_infos::  failed "' + ldap.FullResult + '"');
       ldap.Logout;
       ldap.Free;
       exit;
    end;

 if D then writeln('Ldap_infos:: Results Count :' + IntToStr(ldap.SearchResult.Count));




 if ldap.SearchResult.Count=0 then begin
     if D then writeln('Ldap_infos::  no results...');
       ldap.Logout;
       ldap.Free;
       exit();
 end;

 if Z then writeln(CRLF +CRLF +'************************************************');


 for i:=0 to ldap.SearchResult.Count -1 do begin
      if D then writeln('QUERY:: ObjectName.......: "' +ldap.SearchResult.Items[i].ObjectName + '"');
      DN_ROOT:=ldap.SearchResult.Items[i].ObjectName;
      if D then writeln('QUERY:: Count attributes.: ' +IntToStr(ldap.SearchResult.Items[i].Attributes.Count));
      

      RES.RBL_SERVER_ACTION:=SearchSingleAttribute(ldap.SearchResult.Items[i].Attributes,'rblserversaction');
      
      //----------- bogofilter ------------------------------------------------------------------------------
      RES.BOGOFILTER_ACTION:=SearchSingleAttribute(ldap.SearchResult.Items[i].Attributes,'BogoFilterAction');
      if length(RES.BOGOFILTER_ACTION)=0 then RES.BOGOFILTER_ACTION:='90;prepend;*** SPAM ***';
      //-----------------------------------------------------------------------------------------------------

      //----------- Trust users ------------------------------------------------------------------------------
      RES.TrustMyUsers:=SearchSingleAttribute(ldap.SearchResult.Items[i].Attributes,'OuTrustMyUSers');
      if length(RES.TrustMyUsers)=0 then RES.TrustMyUsers:='yes';
      //-----------------------------------------------------------------------------------------------------
      


      for t:=0 to ldap.SearchResult.Items[i].Attributes.Count -1 do begin

               AttributeNameQ:=LowerCase(ldap.SearchResult.Items[i].Attributes[t].AttributeName);
               if D then writeln('QUERY:: Attribute name[' + IntToStr(t) + '].......: "' + AttributeNameQ + '"');

               if AttributeNameQ='rblservers' then RES.RBL_SERVERS.AddStrings(ParseResultInStringList(ldap.SearchResult.Items[i].Attributes.Items[t]));
               
               
               
      end;

 end;

     if Z then writeln();
     if Z then writeln('************************************************');
     if D then writeln('QUERY:: logout');
     

     RegExpr.Expression:='([0-9]+);([a-z]+);(.+)';
     if RegExpr.Exec(RES.BOGOFILTER_ACTION) then begin
           RES.BOGOFILTER_PARAM.max_rate:=StrToInt(RegExpr.Match[1]);
           RES.BOGOFILTER_PARAM.action:=RegExpr.Match[2];
           RES.BOGOFILTER_PARAM.prepend:=RegExpr.Match[3];
     end;
     
     result:=RES;
     RegExpr.Free;
     ldap.Logout;
     ldap.Free;

end;
 //##############################################################################
function Tsendldap.ParseResultInStringList(Items:TLDAPAttribute):TStringList;
var
   D:boolean;
   i:integer;
   A:TstringList;
begin
     D:=false;
     D:=COMMANDLINE_PARAMETERS('debug');
   A:=TstringList.Create;

   if D then writeln('ParseResultInStringList:: Count items......: ' +IntToStr(Items.Count));
   for i:=0 to Items.Count -1 do begin
       A.Add(Items.Strings[i]);
   end;
exit(A);
   
end;
 //##############################################################################
function Tsendldap.SearchSingleAttribute(Items:TLDAPAttributeList;SearchAttribute:string):string;
var
   D:boolean;
   i:integer;
   AttributeName:string;
begin
     D:=false;
     D:=COMMANDLINE_PARAMETERS('debug');
   if D then writeln('SearchSingleAttribute:: Count items......: ' +IntToStr(Items.Count));
   for i:=0 to Items.Count -1 do begin
            AttributeName:=LowerCase(Items[i].AttributeName);
            if D then writeln('SearchSingleAttribute:: AttributeName......: ' +AttributeName);
            if LowerCase(SearchAttribute)=AttributeName then begin
                    if D then writeln(chr(9) + Items[i].Strings[0]);
                    result:=Items[i].Strings[0];
                    break;
            end;
            
            
   end;
   
exit();

end;


 //##############################################################################
function Tsendldap.Query(Query_string:string;return_attribute:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var  ldap:TLDAPSend;
l:TStringList;
i,t,u:integer;
D,Z:boolean;
value_result:string;
AttributeNameQ:string;
begin
D:=false;
D:=COMMANDLINE_PARAMETERS('debug');
Z:=COMMANDLINE_PARAMETERS('q=');
ldap :=  TLDAPSend.Create;
     ldap.TargetHost := '127.0.0.1';
     ldap.TargetPort := '389';
     ldap.UserName := ldap_admin;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;
     
     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;

    return_attribute:=LowerCase(return_attribute);
    ldap.Bind;
    l:=TstringList.Create;
    l.Add('*');
    if length(SEARCH_DN)=0 then SEARCH_DN:=ldap_suffix;

    if D then writeln('QUERY:: "' + Query_string  + '" find attr:' + return_attribute);
    if D then writeln('QUERY:: IN DN "' + SEARCH_DN  + '"');

    if not ldap.Search(SEARCH_DN, False, Query_string, l) then begin
       if D then writeln('QUERY::  failed "' + ldap.FullResult + '"');
       ldap.Logout;
       ldap.Free;
       exit;
    end;
    
 if D then writeln('QUERY:: Results Count :' + IntToStr(ldap.SearchResult.Count));


 if ldap.SearchResult.Count=0 then begin
     if D then writeln('QUERY::  no results...');
       ldap.Logout;
       ldap.Free;
       exit();
 end;
 
 if Z then writeln(CRLF +CRLF +'************************************************');
 
 
 for i:=0 to ldap.SearchResult.Count -1 do begin
      if D then writeln('QUERY:: ObjectName.......: "' +ldap.SearchResult.Items[i].ObjectName + '"');
      DN_ROOT:=ldap.SearchResult.Items[i].ObjectName;
      if return_attribute='objectname' then begin
         ldap.Logout;
         ldap.Free;
         if D then writeln('QUERY:: RETURN ObjectName.......: "' +DN_ROOT + '"');
         exit(DN_ROOT);
      end;
      
      if D then writeln('QUERY:: Count attributes.: ' +IntToStr(ldap.SearchResult.Items[i].Attributes.Count));
      
      for t:=0 to ldap.SearchResult.Items[i].Attributes.Count -1 do begin

      AttributeNameQ:=LowerCase(ldap.SearchResult.Items[i].Attributes[t].AttributeName);
      if D then writeln('QUERY:: Attribute name[' + IntToStr(t) + '].......: "' + AttributeNameQ + '"');
      
     TEMP_LIST.Clear;
     if AttributeNameQ=return_attribute then begin
              if D then writeln('QUERY:: Count items......: ' +IntToStr(ldap.SearchResult.Items[i].Attributes.Items[t].Count));
              for u:=0 to ldap.SearchResult.Items[i].Attributes.Items[t].Count-1 do begin
                  value_result:=ldap.SearchResult.Items[i].Attributes.Items[t].Strings[u];
                  if D then writeln('QUERY:: ADD item[' + IntToStr(t) + ']"............:'+value_result+ '"');
                  TEMP_LIST.Add(trim(value_result));
                  Result:=Result + value_result+CRLF;
              end;
        end;
     end;
 
 end;
 
     if Z then writeln(Result);
      if Z then writeln('************************************************');
     if D then writeln('QUERY:: logout');

     ldap.Logout;
     ldap.Free;
 
end;
//##############################################################################
function Tsendldap.implode(ArrayS:TStringDynArray):string;
var
   i:integer;
   D:boolean;
begin
D:=COMMANDLINE_PARAMETERS('debug');
if D then writeln('Arrays:', length(ArrayS));
    for i:=0 to length(ArrayS) -1 do begin
     if length(ArrayS[i])>0 then result:=result + '|' + ArrayS[i];
    end;
end;


//##############################################################################
function Tsendldap.Query_A(Query_string:string;return_attribute:string):TStringDynArray;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var  ldap:TLDAPSend;
l:TStringList;
i,t,u,r:integer;
D,Z:boolean;
value_result:string;
AttributeNameQ:string;
begin
D:=false;
D:=COMMANDLINE_PARAMETERS('debug');
Z:=COMMANDLINE_PARAMETERS('q=');
ldap :=  TLDAPSend.Create;
     ldap.TargetHost := '127.0.0.1';
     ldap.TargetPort := '389';
     ldap.UserName := ldap_admin;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;

     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;

    return_attribute:=LowerCase(return_attribute);
    ldap.Bind;
    l:=TstringList.Create;
    l.Add('*');
    if length(SEARCH_DN)=0 then SEARCH_DN:=ldap_suffix;

    if D then writeln('QUERY:: "' + Query_string  + '" find attr:' + return_attribute);
    if D then writeln('QUERY:: IN DN "' + SEARCH_DN  + '"');

    if not ldap.Search(SEARCH_DN, False, Query_string, l) then begin
       if D then writeln('QUERY::  failed "' + ldap.FullResult + '"');
       ldap.Logout;
       ldap.Free;
       exit;
    end;

 if D then writeln('QUERY:: Results Count :' + IntToStr(ldap.SearchResult.Count));


 if ldap.SearchResult.Count=0 then begin
     if D then writeln('QUERY::  no results...');
       ldap.Logout;
       ldap.Free;
       exit();
 end;

 if Z then writeln(CRLF +CRLF +'************************************************');


 for i:=0 to ldap.SearchResult.Count -1 do begin
      if D then writeln('QUERY:: ObjectName.......: "' +ldap.SearchResult.Items[i].ObjectName + '"');
      DN_ROOT:=ldap.SearchResult.Items[i].ObjectName;
      if return_attribute='objectname' then begin
         ldap.Logout;
         ldap.Free;
      end;

      if D then writeln('QUERY:: Count attributes.: ' +IntToStr(ldap.SearchResult.Items[i].Attributes.Count));

      for t:=0 to ldap.SearchResult.Items[i].Attributes.Count -1 do begin

      AttributeNameQ:=LowerCase(ldap.SearchResult.Items[i].Attributes[t].AttributeName);
      if D then writeln('QUERY:: Attribute name[' + IntToStr(t) + '].......: "' + AttributeNameQ + '"');

     TEMP_LIST.Clear;
     if AttributeNameQ=return_attribute then begin
              if D then writeln('QUERY:: Count items......: ' +IntToStr(ldap.SearchResult.Items[i].Attributes.Items[t].Count));
              SetLength(result, 0);
              for u:=0 to ldap.SearchResult.Items[i].Attributes.Items[t].Count-1 do begin
              
                  value_result:=ldap.SearchResult.Items[i].Attributes.Items[t].Strings[u];
                  if D then writeln('QUERY:: ADD item[' + IntToStr(t) + ']"............:'+value_result+ '"[' + intToStr(r) + ']"');
                  SetLength(result, length(result)+1);
                  result[length(result)-1]:=value_result;
              end;
        end;
     end;

 end;

     if Z then writeln('rows:',length(result));
      if Z then writeln('************************************************');
     if D then writeln('QUERY:: logout');

     ldap.Logout;
     ldap.Free;

end;



//##############################################################################
function Tsendldap.SearchResults_single(SearchResults:TLDAPResultList;WhatToFind:string):string;
var
   i,t:Integer;
   AttributeNameQ:string;

begin
     result:='';
     for i:=0 to SearchResults.Count -1 do begin
             for t:=0 to SearchResults.Items[i].Attributes.Count -1 do begin
                  AttributeNameQ:=LowerCase(SearchResults.Items[i].Attributes[t].AttributeName);
                  if AttributeNameQ=LowerCase(WhatToFind) then begin
                      result:=SearchResults.Items[i].Attributes.Items[t].Strings[0];
                      break;
                  end;
             end;
     end;
end;
//##############################################################################
function Tsendldap.SearchResults_multiple(SearchResults:TLDAPResultList;WhatToFind:string):TstringList;
var
   i,t,u:Integer;
   AttributeNameQ:string;
   k:TstringList;
begin
k:=TstringList.Create;
result:=k;
       for i:=0 to SearchResults.Count -1 do begin
             for t:=0 to SearchResults.Items[i].Attributes.Count -1 do begin
                  AttributeNameQ:=LowerCase(SearchResults.Items[i].Attributes[t].AttributeName);
                  if AttributeNameQ=LowerCase(WhatToFind) then begin
                      for u:=0 to SearchResults.Items[i].Attributes.Items[t].Count-1 do begin
                        k.Add(SearchResults.Items[i].Attributes.Items[t].Strings[u]);
                      end;
                  end;
             end;
        end;
 result:=k;

end;
//##############################################################################


function Tsendldap.EmailFromaliase(email:string):string;
var  ldap:TLDAPSend;
l:TStringList;
i,t,u:integer;
D:boolean;
F:boolean;
begin
      F:=COMMANDLINE_PARAMETERS('debug');
      if F then writeln('EmailFromaliase:' + email);
     ldap :=  TLDAPSend.Create;
     if F then writeln('EmailFromaliase:init engine success');
     ldap.TargetHost := '127.0.0.1';
     ldap.TargetPort := '389';
     ldap.UserName := ldap_admin;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;
     if F then writeln('EmailFromaliase:Login "' + ldap_admin + '"');
     if not ldap.Login then begin
        if F then writeln('EmailFromaliase:Error connection');
        ldap.Free;
        exit(email);
     end;

     if F then writeln('EmailFromaliase: Bind');
     ldap.Bind;
     if F then writeln('EmailFromaliase: Binded');
     D:=COMMANDLINE_PARAMETERS('aliases');


    l:=TstringList.Create;
    l.Add('mail');
    if F then writeln('EmailFromaliase:(&(objectclass=userAccount)(mailAlias=' + email+'))');
    ldap.Search(ldap_suffix, False, '(&(objectclass=userAccount)(mailAlias=' + email+'))', l);
    //writeln(LDAPResultdump(ldap.SearchResult));
    
    if D then writeln('Count:' + IntToStr(ldap.SearchResult.Count));
    
    if ldap.SearchResult.Count>0 then begin
         result:=ldap.SearchResult.Items[0].Attributes.Items[0].Strings[0];
         if D then writeln(email+'="' + result + '"');
         ldap.Logout;
         ldap.Free;
         exit;
    end else begin
        result:=email;
         if D then writeln(email+'="' + result + '"');
         ldap.Logout;
         ldap.Free;
        exit;
    end;
    
    
     writeln('count=' + IntToStr(ldap.SearchResult.Count));
     for i:=0 to ldap.SearchResult.Count -1 do begin
       writeln( ldap.SearchResult.Items[i].ObjectName);
       writeln( 'attributes:=' +IntToStr(ldap.SearchResult.Items[i].Attributes.Count));
       writeln('ObjectName:'+ldap.SearchResult.Items[i].ObjectName);
       
       
        for t:=0 to ldap.SearchResult.Items[i].Attributes.Count -1 do begin
              for u:=0 to ldap.SearchResult.Items[i].Attributes.Items[t].Count-1 do begin
                  writeln(ldap.SearchResult.Items[i].Attributes.Items[t].Strings[u]);
              end;
        end;
        
     end;
     writeln('logout');

     ldap.Logout;
     ldap.Free;

end;
//##############################################################################
function Tsendldap.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
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
function Tsendldap.get_LDAP(key:string):string;
var value:string;
GLOBAL_INI:TiniFile;
begin
if not fileExists('/etc/artica-postfix/artica-postfix-ldap.conf') then begin
   writeln('unable to stat /etc/artica-postfix/artica-postfix-ldap.conf !!!');
   exit;
end;
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix-ldap.conf');
value:=GLOBAL_INI.ReadString('LDAP',key,'');
result:=value;
GLOBAL_INI.Free;
end;

//##############################################################################
function Tsendldap.get_CONF(key:string):string;
var value:string;
GLOBAL_INI:TiniFile;
begin
if not fileExists('/etc/artica-postfix/artica-postfix.conf') then begin
   writeln('unable to stat /etc/artica-postfix/artica-postfix.conf !!!');
   exit;
end;
GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
value:=GLOBAL_INI.ReadString('ARTICA',key,'');
result:=value;
GLOBAL_INI.Free;
end;

//##############################################################################

function Tsendldap.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
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
//##############################################################################

end.

