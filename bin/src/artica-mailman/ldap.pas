unit ldap;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,ldapsend,RegExpr in 'RegExpr.pas',global_conf,IniFiles,logs;
  
type TStringDynArray = array of string;
  
type bogofilter_settings=record
        max_rate:integer;
        prepend:string;
        action:string;
  end;
  

   
type MailmanGlobalListParameters=record
        DEFAULT_EMAIL_HOST:string;     //MailManDefaultEmailHost
        DEFAULT_URL_HOST:string;       //MailManDefaultUrlHost
        DEFAULT_URL_PATTERN:string;    //MailManDefaultUrlPattern
        PUBLIC_ARCHIVE_URL:string;      //MailManDefaulPublicArchiveUrl
        VIRTUAL_HOST_OVERVIEW:string;   //VirtualHostOverview
        MailManAdminPassword:string;
end;

type kavmilterd_parameters=record
     kavmilter_conf:string;
     kavmilter_rules:TstringList;

end;

type MailmanListParameters=record
         MailmanListOperation:string;
         MailManListAdministrator:string;
         MailManListAdminPassword:string;
         MailmanListConfigDatas:string;
         HostNameList:string;
         GlobalSettings:MailmanGlobalListParameters;
   end;

type  ldapinfos =record
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
       TrustMyUsers:string;
end;





  
  
  type Tldap=class
  

  private
       ldap_admin,ldap_password,ldap_suffix,ldap_server:string;
       function Connect():boolean;
       function IsArticaCreated():boolean;
       function IfDNExists(dn:string):boolean;
       PROCEDURE Output(zText:string);
       ldap_common:TLDAPSend;
       function get_LDAP(key:string):string;
       function get_CONF(key:string):string;
       function Query_A(Query_string:string;return_attribute:string):TStringDynArray;
       DN_ROOT:string;
       function ParseResultInStringList(Items:TLDAPAttribute):TStringList;
       function SearchSingleAttribute(Items:TLDAPAttributeList;SearchAttribute:string):string;
       function Create_dcObject(dn:string;name:string):boolean;
       procedure DumpAttributes(LDAPAttributeList:TLDAPAttributeList);

  public
      constructor Create();
      destructor Destroy; override;
      function   Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
      function   load_mailman_lists():TStringList;
      function   mailman_config(list_name:string):MailmanListParameters;
      function   mailman_global_config():MailmanGlobalListParameters;
      procedure  Create_General_config_branch();
      
      
      function  Load_kavmilterd_parameters():kavmilterd_parameters;
      
      procedure  DeleteCyrusUser();
      function   COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
      function   Query(Query_string:string;return_attribute:string):string;
      procedure  CreateArticaUser();
      function   OuLists():TStringDynArray;
      function   implode(ArrayS:TStringDynArray):string;
      function   ParseSuffix():boolean;
      procedure  CreateSuffix();
      function   CreateCyrusUser():boolean;
      procedure  Create_mailmain_list(listename:string;configDatas:string);
      function   LoadAllOu():string;
      procedure  update_mailmain_list(listename:string;configDatas:MailmanListParameters);
      procedure  Delete_mailman_list(listename:string);
      SEARCH_DN:string;
      TEMP_LIST:TstringList;

end;

implementation

constructor Tldap.Create();
begin
   SEARCH_DN:='';
   ldap_admin:=get_LDAP('admin');
   ldap_password:=get_LDAP('password');
   ldap_suffix:=get_LDAP('suffix');
   ldap_server:=get_LDAP('server');
   if length(ldap_server)=0 then ldap_server:='127.0.0.1';
   TEMP_LIST:=TstringList.Create;
   ldap_common:=TLDAPSend.Create;
end;
//##############################################################################
destructor Tldap.Destroy;
begin
  TEMP_LIST.Free;
  ldap_common.Logout;
  ldap_common.Free;
  inherited Destroy;
end;
//##############################################################################
function Tldap.OuLists():TStringDynArray;
var Myquery:string;
resultats:TStringDynArray;
begin
    Myquery:='(&(ObjectClass=organizationalUnit)(ou=*))';
     resultats:=Query_A(MyQuery,'ou');
     exit(resultats);
end;
//##############################################################################
function Tldap.load_mailman_lists():TStringList;
var
   Myquery,AttributeNameQ:string;
   D:boolean;
   A,l:TStringList;
   i,t:integer;
begin
     A:=TStringList.Create;
     D:=COMMANDLINE_PARAMETERS('debug');
     SEARCH_DN:='cn=mailman,cn=artica,' + ldap_suffix;
     Myquery:='(&(ObjectClass=ArticaMailManClass)(cn=*))';
     result:=A;
if not Connect() then exit;
    l:=TstringList.Create;
    l.Add('cn');
    if D then writeln('load_mailman_lists() -> Search ' + myquery + ' in ' + SEARCH_DN);


     if not ldap_common.Search(SEARCH_DN, False, myquery, l) then begin
        if D then writeln('load_mailman_lists() -> Search failed');
        exit;
     end;
        for i:=0 to ldap_common.SearchResult.Count -1 do begin
             for t:=0 to ldap_common.SearchResult.Items[i].Attributes.Count -1 do begin
              if D then writeln('load_mailman_lists() -> AttributeNameQ='  + AttributeNameQ);
              AttributeNameQ:=LowerCase(ldap_common.SearchResult.Items[i].Attributes[t].AttributeName);
                  if AttributeNameQ=LowerCase('cn') then A.Add(ldap_common.SearchResult.Items[i].Attributes[t].Strings[0]);
                  if D then writeln('load_mailman_lists() -> a[' + IntToStr(a.Count) + ']=' +ldap_common.SearchResult.Items[i].Attributes[t].Strings[0]);
             end;
        end;

    result:=A;
    l.free;

end;
//##############################################################################
procedure Tldap.CreateArticaUser();
  var
  ldap: TLDAPsend;
  LDAPAttributeList: TLDAPAttributeList;
  LDAPAttribute: TLDAPAttribute;
  attr: TLDAPAttribute;
  dn:string;
  i:integer;
  z:integer;
  RegExpr:TRegExpr;
begin
     ldap :=  TLDAPSend.Create;
     ldap.TargetHost := ldap_server;
     ldap.TargetPort := '389';
     ldap.UserName := 'cn=' +ldap_admin + ',' + ldap_suffix;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;


     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;

    if not ldap.Bind then begin
       writeln('CreateArticaUser:: failed bind "' + ldap.UserName + '"');
       ldap.free;
       exit;
    end;
      dn:='cn=artica,' +  ldap_suffix;


     LDAPAttributeList := TLDAPAttributeList.Create;


     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='ObjectClass';
     LDAPAttribute.Add('organizationalRole');
     LDAPAttribute.Add('ArticaSettings');
     LDAPAttribute.Add('top');

     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='cn';
     LDAPAttribute.Add('artica');

     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='ArticaWebRootURI';
     LDAPAttribute.Add('http://127.0.0.1:9000/artica-postfix');

     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='already exists';
     if not ldap.Add(dn,LDAPAttributeList) then begin
        if not RegExpr.Exec(ldap.ResultString) then begin
        writeln(ldap.ResultString);
        DumpAttributes(LDAPAttributeList);
        end;
     end;
     RegExpr.free;
     ldap.Logout;
     ldap.free;
     
end;



//##############################################################################
procedure Tldap.DumpAttributes(LDAPAttributeList:TLDAPAttributeList);
var
   i,z:integer;
   datas:string;
begin

     for i:=0 to LDAPAttributeList.Count -1 do begin
         for z:=0 to  LDAPAttributeList.Items[i].Count -1 do begin
         datas:=LDAPAttributeList.Items[i].Strings[z];
         if length(datas)>50 then datas:='{datas}->' + IntToStr(length(datas)) + ' length';
         writeln(LDAPAttributeList.Items[i].AttributeName + '[' + intToStr(z) + ']=' + datas);
         end;
     end;
end;
//##############################################################################
procedure Tldap.DeleteCyrusUser();
  var
  ldap: TLDAPsend;
  LDAPAttributeList: TLDAPAttributeList;
  LDAPAttribute: TLDAPAttribute;
  attr: TLDAPAttribute;
  dn:string;
  i:integer;
  z:integer;
  RegExpr:TRegExpr;
  cyrus_admin:string;
  cyrus_password:string;
begin
ldap :=  TLDAPSend.Create;
     ldap.TargetHost := ldap_server;
     ldap.TargetPort := '389';
     ldap.UserName := 'cn=' +ldap_admin + ',' + ldap_suffix;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;

     cyrus_admin:=get_LDAP('cyrus_admin');
     cyrus_password:=get_LDAP('cyrus_password');

     if length(cyrus_admin)=0 then cyrus_admin:='cyrus';
     if length(cyrus_password)=0 then cyrus_password:=ldap_password;



     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;

    if not ldap.Bind then begin
       writeln('failed bind');
       exit;
    end;
     dn:='cn=' + cyrus_admin + ',' +  ldap_suffix;
     
     if ldap.Delete(dn) then writeln('success delete "' + cyrus_admin + '" cyrus-imapd admin');
     
     
end;
//##############################################################################
procedure Tldap.update_mailmain_list(listename:string;configDatas:MailmanListParameters);
 var
  LDAPAttributeList: TLDAPAttributeList;
  LDAPAttribute: TLDAPAttribute;
  attr: TLDAPAttribute;
  mods:TLDAPModifyOp;
  dn:string;
  D:boolean;
  RegExpr:TRegExpr;
begin
  LDAPAttributeList := TLDAPAttributeList.Create;
    D:=COMMANDLINE_PARAMETERS('debug');
    if length(configDatas.MailmanListConfigDatas)=0 then begin
       if D then writeln('Create_mailmain_list()-> no datas provided');
       output('Create_mailmain_list()-> no datas provided');
       exit;
    end;

     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='MailmanListConfigDatas';
     LDAPAttribute.Add(configDatas.MailmanListConfigDatas);
     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='MailmanListOperation';
     LDAPAttribute.Add(configDatas.MailmanListOperation);

     
     if not ldap_common.Modify('cn=' + listename + ',cn=mailman,cn=artica,' + ldap_suffix,MO_Replace,LDAPAttribute) then begin
       if D then writeln('Error number ' + IntToStr(ldap_common.ResultCode) + ' ' +  ldap_common.ResultString);
       output('Error number ' + IntToStr(ldap_common.ResultCode) + ' ' +  ldap_common.ResultString);
     end;

end;
//##############################################################################

procedure Tldap.Create_General_config_branch();
var
  D:boolean;
  LDAPAttributeList: TLDAPAttributeList;
  LDAPAttribute: TLDAPAttribute;
  attr: TLDAPAttribute;
  dn,hostname:string;
  GLOBAL_INI:myconf;

begin
     if not Connect() then exit;
     dn:= 'cn=Defaults.py,cn=mailman,cn=artica,'+ldap_suffix;
     if IfDNExists(dn) then exit;

        GLOBAL_INI:=myconf.Create;
        hostname:=GLOBAL_INI.SYSTEM_FQDN();
        LDAPAttributeList := TLDAPAttributeList.Create;
         
         
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='cn';
            LDAPAttribute.Add('Defaults.py');
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='ObjectClass';
            LDAPAttribute.Add('top');
            LDAPAttribute.Add('ArticaMailMangGlobalConfigClass');
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='MailManDefaultEmailHost';
            LDAPAttribute.Add(hostname);
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='MailManDefaultUrlHost';
            LDAPAttribute.Add(hostname);
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='MailManDefaultUrlPattern';
            LDAPAttribute.Add('https://%s:9000/mailman/');
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='MailManAdminPassword';
            LDAPAttribute.Add(ldap_password);
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='MailManDefaulPublicArchiveUrl';
            LDAPAttribute.Add('https://%(hostname)s:9000/pipermail/%(listname)s');

            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='VirtualHostOverview';
            LDAPAttribute.Add('Off');
            
            


            
            if not ldap_common.Add(dn,LDAPAttributeList) then begin
                 writeln('Error number ' + IntToStr(ldap_common.ResultCode) + ' ' +  ldap_common.ResultString);
                 output('Create_General_config_branch() -> Error number ' + IntToStr(ldap_common.ResultCode) + ' ' +  ldap_common.ResultString);
            end;

end;
//##############################################################################
procedure Tldap.Delete_mailman_list(listename:string);
begin
if not Connect() then exit;
if not IfDNExists('cn=' + listename + ',cn=mailman,cn=artica,' + ldap_suffix) then exit;
ldap_common.Delete('cn=' + listename + ',cn=mailman,cn=artica,' + ldap_suffix);
end;

//##############################################################################
procedure Tldap.Create_mailmain_list(listename:string;configDatas:string);
  var
  LDAPAttributeList: TLDAPAttributeList;
  LDAPAttribute: TLDAPAttribute;
  attr: TLDAPAttribute;
  dn:string;
  D:boolean;
  RegExpr:TRegExpr;
begin

    LDAPAttributeList := TLDAPAttributeList.Create;
    D:=COMMANDLINE_PARAMETERS('debug');
    if length(configDatas)=0 then begin
       if D then writeln('Create_mailmain_list()-> no datas provided');
       exit;
    end;
    
    
    
    if not IfDNExists('cn=mailman,cn=artica,'+ldap_suffix) then begin
            if D then writeln('cn=artica,cn=mailman,'+ldap_suffix + ' doesn''t exists, create it');
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='cn';
            LDAPAttribute.Add('mailman');
            
            LDAPAttribute:= LDAPAttributeList.Add;
            LDAPAttribute.AttributeName:='ObjectClass';
            LDAPAttribute.Add('top');
            LDAPAttribute.Add('PostFixStructuralClass');

            
            if not ldap_common.Add('cn=mailman,cn=artica,'+ldap_suffix,LDAPAttributeList) then begin
             if D then writeln('Create_mailmain_list() -> Error unable to create branch cn=artica,cn=mailman,'+ldap_suffix);
             if D then writeln('Error number ' + IntToStr(ldap_common.ResultCode) + ' ' +  ldap_common.ResultString);
             DumpAttributes(LDAPAttributeList);
             exit;
            end;
             
    end;

       LDAPAttributeList.Clear;




     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='ObjectClass';
     LDAPAttribute.Add('top');
     LDAPAttribute.Add('ArticaMailManClass');
     
     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='cn';
     LDAPAttribute.Add(listename);

     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='MailManOuOwner';
     LDAPAttribute.Add('undefined');
     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='MailmanListConfigDatas';
     LDAPAttribute.Add(configDatas);

 RegExpr:=TRegExpr.Create;
 RegExpr.Expression:='already exists';
     if not ldap_common.Add('cn=' + listename + ',cn=mailman,cn=artica,' + ldap_suffix,LDAPAttributeList) then begin
        if not RegExpr.Exec(ldap_common.ResultString) then begin
           writeln(ldap_common.ResultString);
           DumpAttributes(LDAPAttributeList);
        end;
     end else begin
          writeln('Create_mailmain_list()-> ' + listename + ' success');
     end;

RegExpr.free;

end;

//##############################################################################
function Tldap.CreateCyrusUser():boolean;
  var
  ldap: TLDAPsend;
  LDAPAttributeList: TLDAPAttributeList;
  LDAPAttribute: TLDAPAttribute;
  attr: TLDAPAttribute;
  dn:string;
  i:integer;
  z:integer;
  RegExpr:TRegExpr;
  cyrus_admin:string;
  cyrus_password:string;
begin
     ldap :=  TLDAPSend.Create;
     ldap.TargetHost := ldap_server;
     ldap.TargetPort := '389';
     ldap.UserName := 'cn=' +ldap_admin + ',' + ldap_suffix;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;

     cyrus_admin:=get_LDAP('cyrus_admin');
     cyrus_password:=get_LDAP('cyrus_password');

     if length(cyrus_admin)=0 then cyrus_admin:='cyrus';
     if length(cyrus_password)=0 then cyrus_password:=ldap_password;



     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;
     
    if not ldap.Bind then begin
       writeln('failed bind');
       exit;
    end;
     dn:='cn=' + cyrus_admin + ',' +  ldap_suffix;


     
     LDAPAttributeList := TLDAPAttributeList.Create;
     
     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='ObjectClass';
     LDAPAttribute.Add('top');
     LDAPAttribute.Add('inetOrgPerson');


     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='cn';
     LDAPAttribute.Add(cyrus_admin);
     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='sn';
     LDAPAttribute.Add(cyrus_admin);
     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='userPassword';
     LDAPAttribute.Add(ldap_password);
     
     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='uid';
     LDAPAttribute.Add(cyrus_admin);

     RegExpr:=TRegExpr.Create;
     RegExpr.Expression:='already exists';
     if not ldap.Add(dn,LDAPAttributeList) then begin
        if not RegExpr.Exec(ldap.ResultString) then begin
        writeln(ldap.ResultString);
        DumpAttributes(LDAPAttributeList);
        end;
     end else begin
          writeln('Starting......: Create cyrus-imapd admin "' + cyrus_admin + '" success');
     end;
        
     ldap.Logout;
     ldap.free;

     


end;

//##############################################################################

function Tldap.Connect():boolean;
var
   Logs:Tlogs;
   D:boolean;
begin
 result:=false;
 D:=COMMANDLINE_PARAMETERS('debug');
 Logs:=Tlogs.Create;

     ldap_common.TargetHost := '127.0.0.1';
     ldap_common.TargetPort := '389';
     ldap_common.UserName := 'cn=' + ldap_admin + ',' + ldap_suffix;
     ldap_common.Password := ldap_password;
     ldap_common.Version := 3;
     ldap_common.FullSSL := false;

     if D then writeln('Connect() -> ' + ldap_common.TargetHost + ':' + ldap_common.TargetPort  + ' //'  + ldap_common.UserName);

     if not ldap_common.Login then begin
        if D then writeln('Connect() -> Warning, unable to establish communication to ' + ldap_common.TargetHost + ':' + ldap_common.TargetPort);
        ldap_common.Free;
        exit();
     end;


   if not ldap_common.Bind then begin
      writeln('Connect() -> failed logon with "' + ldap_common.UserName + '"');
      exit;
   end;
   result:=true;
   Logs.free;
 
 
 
end;


//##############################################################################
function Tldap.IsArticaCreated():boolean;
var l:TstringList;
begin
 result:=false;

     if not Connect() then exit;
     l:=TstringList.Create;
     l.Add('*');
 
    if ldap_common.Search(ldap_suffix, False, '(&(objectclass=organizationalRole)(cn=artica)', l) then begin
       if ldap_common.SearchResult.Count>0 then result:=true;

    end;


end;
//##############################################################################
function Tldap.IfDNExists(dn:string):boolean;
var
   D:boolean;
   l:TstringList;
begin
  D:=false;
  result:=false;
  D:=COMMANDLINE_PARAMETERS('debug');
  DN_ROOT:=dn;
  if not Connect() then exit;
    l:=TstringList.Create;
    l.Add('*');
  
 if ldap_common.Search(dn, False, '(objectclass=*)', l) then begin
       if ldap_common.SearchResult.Count>0 then result:=true;

    end;
  
  
end;
//##############################################################################


//##############################################################################
function Tldap.ParseSuffix():boolean;
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
begin
  D:=false;
  result:=false;
     D:=COMMANDLINE_PARAMETERS('debug');
     ldap :=  TLDAPSend.Create;
     ldap.TargetHost := '127.0.0.1';
     ldap.TargetPort := '389';
     ldap.UserName := 'cn=' + ldap_admin + ',' + ldap_suffix;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;

     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;


   if not ldap.Bind then begin
      writeln('failed logon with "' + ldap.UserName + '"');
      exit;
   end;
    l:=TstringList.Create;
    l.Add('*');


    // ***************************************************************** user

   result:=false;
    if ldap.Search(ldap_suffix, False, '(objectclass=dcObject)', l) then begin
       if ldap.SearchResult.Count>0 then result:=true;

    end;


end;





//##############################################################################
procedure Tldap.CreateSuffix();
var
   ldap:TLDAPSend;
   l:TStringList;
   i,t,u:integer;
   D,Z:boolean;
   value_result:string;
   USER_DN:string;
   Query_string:string;
   newdn:string;
   DN_ROOT:string;
   RegExpr:TRegExpr;
   tbl:TStringDynArray;
begin
   USER_DN:=ldap_suffix;


   //if ParseSuffix() then exit;
   tbl:=Explode(',',USER_DN);
   newdn:=tbl[0];
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='dc=(.+)';
   RegExpr.Exec(tbl[0]);
   
   
   for i:=1 to  length(tbl)-1 do begin

          writeln('Create ' +newdn);
          if not Create_dcObject(newdn,RegExpr.Match[1]) then begin
             writeln('FAILED create ' + newdn);
             break;
          end;
          newdn:=newdn + ',' + tbl[i];
   end;
   

    
    
    
   
   

end;
//##############################################################################
function Tldap.Create_dcObject(dn:string;name:string):boolean;
  var
  ldap: TLDAPsend;
  LDAPAttributeList: TLDAPAttributeList;
  LDAPAttribute: TLDAPAttribute;
  attr: TLDAPAttribute;

begin



     ldap :=  TLDAPSend.Create;
     ldap.TargetHost := ldap_server;
     ldap.TargetPort := '389';
     ldap.UserName := 'cn=' +ldap_admin + ',' + ldap_suffix;
     ldap.Password := ldap_password;
     ldap.Version := 3;
     ldap.FullSSL := false;


     if not ldap.Login then begin
        ldap.Free;
        exit();
     end;

    if not ldap.Bind then begin
       writeln('failed bind "' + ldap.UserName + '"');
       exit;
    end;
    
    

     LDAPAttributeList := TLDAPAttributeList.Create;


     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='ObjectClass';
     LDAPAttribute.Add('top');
     LDAPAttribute.Add('dcObject');
     LDAPAttribute.Add('organization');

     
     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='o';
     LDAPAttribute.Add(name);

     LDAPAttribute:= LDAPAttributeList.Add;
     LDAPAttribute.AttributeName:='dc';
     LDAPAttribute.Add(name);
     
     result:=ldap.Add(dn,LDAPAttributeList);
     if not result then writeln(name + ': ' + ldap.ResultString);
     ldap.free;

end;



 //##############################################################################
function Tldap.LoadAllOu():string;
var
right_email,Myquery,resultats:string;
i,t,u:integer;
D:boolean;
begin

   Myquery:='(&(ObjectClass=organizationalUnit)(ou=*))';
   resultats:=Query(MyQuery,'ou');
   exit(resultats);
end;
function Tldap.ParseResultInStringList(Items:TLDAPAttribute):TStringList;
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
function Tldap.SearchSingleAttribute(Items:TLDAPAttributeList;SearchAttribute:string):string;
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
function  Tldap.mailman_config(list_name:string):MailmanListParameters;
var
               l:TStringList;
               i,t,u:integer;
               D:boolean;
               value_result:string;
               AttributeNameQ,myQuery:string;
               CF:MailmanListParameters;
begin
result:=CF;
D:=false;
D:=COMMANDLINE_PARAMETERS('debug');
if not Connect() then exit;
    l:=TstringList.Create;
    l.Add('*');
    SEARCH_DN:='cn=' + list_name + ',cn=mailman,cn=artica,' + ldap_suffix;
    myQuery:='(&(objectClass=ArticaMailManClass)(cn=' + list_name+'))';
    
    if D then writeln('mailman_config() -> Search ' + myquery + ' in ' + SEARCH_DN);
    
    
     if not ldap_common.Search(SEARCH_DN, False, myquery, l) then begin
        if D then writeln('mailman_config() -> Search failed');
        exit;
     end;
        for i:=0 to ldap_common.SearchResult.Count -1 do begin
             for t:=0 to ldap_common.SearchResult.Items[i].Attributes.Count -1 do begin
              if D then writeln('mailman_config() -> AttributeNameQ='  + AttributeNameQ);
              AttributeNameQ:=LowerCase(ldap_common.SearchResult.Items[i].Attributes[t].AttributeName);
                  if AttributeNameQ=LowerCase('MailManListAdministrator') then CF.MailManListAdministrator:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('MailManListAdminPassword') then CF.MailManListAdminPassword:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('MailmanListConfigDatas') then CF.MailmanListConfigDatas:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('MailmanListOperation') then CF.MailmanListOperation:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('HostNameList') then CF.HostNameList:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  

             end;
        end;



      result:=CF;
end;

 //##############################################################################
function  Tldap.Load_kavmilterd_parameters():kavmilterd_parameters;
var
               l:TStringList;
               i,t,u:integer;
               D:boolean;
               value_result:string;
               AttributeNameQ,myQuery:string;
               CF:kavmilterd_parameters;
begin


CF.kavmilter_rules:=TstringList.Create;
result:=CF;
D:=false;
D:=COMMANDLINE_PARAMETERS('debug');
if not Connect() then exit;
    l:=TstringList.Create;
    l.Add('*');
    SEARCH_DN:='cn=kavmilterd,cn=artica,' + ldap_suffix;
    myQuery:='(objectClass=ArticaKavMilterd)';
    result:=cf;
    if D then writeln('kavmilterd_parameters() -> Search ' + myquery + ' in ' + SEARCH_DN);


     if not ldap_common.Search(SEARCH_DN, False, myquery, l) then begin
        if D then writeln('kavmilterd_parameters() -> Search failed');
        exit;
     end;
        for i:=0 to ldap_common.SearchResult.Count -1 do begin
             for t:=0 to ldap_common.SearchResult.Items[i].Attributes.Count -1 do begin
              if D then writeln('kavmilterd_parameters() -> AttributeNameQ='  + AttributeNameQ);
                  AttributeNameQ:=LowerCase(ldap_common.SearchResult.Items[i].Attributes[t].AttributeName);
                  if AttributeNameQ=LowerCase('kavmilterconf') then CF.kavmilter_conf:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('KavMilterdRuleConf') then begin
                     CF.kavmilter_rules.Add(ldap_common.SearchResult.Items[i].Attributes[t].Strings[0]);
                  end;
             end;
        end;

      
      
      


      result:=CF;
end;
 //##############################################################################
 
 
function Tldap.mailman_global_config():MailmanGlobalListParameters;
var
               l:TStringList;
               i,t,u:integer;
               D:boolean;
               value_result,dn:string;
               AttributeNameQ,myQuery:string;
               CF:MailmanGlobalListParameters;
begin
 dn:= 'cn=Defaults.py,cn=mailman,cn=artica,'+ldap_suffix;
 result:=CF;
D:=false;
D:=COMMANDLINE_PARAMETERS('debug');
 if not Connect() then exit;
    l:=TstringList.Create;
    l.Add('*');
    SEARCH_DN:=dn;
    myQuery:='(&(objectClass=ArticaMailMangGlobalConfigClass)(cn=Defaults.py))';
if D then writeln('mailman_global_confif() -> Search ' + myquery + ' in ' + SEARCH_DN);


     if not ldap_common.Search(SEARCH_DN, False, myquery, l) then begin
        if D then writeln('mailman_global_confif() -> Search failed');
        exit;
     end;
        for i:=0 to ldap_common.SearchResult.Count -1 do begin
             for t:=0 to ldap_common.SearchResult.Items[i].Attributes.Count -1 do begin
              if D then writeln('mailman_config() -> AttributeNameQ='  + AttributeNameQ);
              AttributeNameQ:=LowerCase(ldap_common.SearchResult.Items[i].Attributes[t].AttributeName);
                  if AttributeNameQ=LowerCase('MailManDefaulPublicArchiveUrl') then CF.PUBLIC_ARCHIVE_URL:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('MailManAdminPassword') then CF.MailManAdminPassword:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('MailManDefaultUrlHost') then CF.DEFAULT_URL_HOST:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('MailManDefaultEmailHost') then CF.DEFAULT_EMAIL_HOST:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('MailManDefaultUrlPattern') then CF.DEFAULT_URL_PATTERN:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  if AttributeNameQ=LowerCase('VirtualHostOverview') then CF.VIRTUAL_HOST_OVERVIEW:= ldap_common.SearchResult.Items[i].Attributes[t].Strings[0];
                  

                  
             end;
        end;
        
 if length(CF.PUBLIC_ARCHIVE_URL)=0 then CF.PUBLIC_ARCHIVE_URL:='https://%(hostname)s:9000/pipermail/%(listname)s';
 if length(CF.VIRTUAL_HOST_OVERVIEW)=0 then CF.VIRTUAL_HOST_OVERVIEW:='No';
 result:=CF;
end;
 //##############################################################################

 
 
 
function Tldap.Query(Query_string:string;return_attribute:string):string;
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
function Tldap.implode(ArrayS:TStringDynArray):string;
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
function Tldap.Query_A(Query_string:string;return_attribute:string):TStringDynArray;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
l:TStringList;
i,t,u,r:integer;
D,Z:boolean;
value_result:string;
AttributeNameQ:string;
begin
D:=false;
D:=COMMANDLINE_PARAMETERS('debug');
    if D then writeln('Query_A() -> connect...');
if not Connect() then begin
          if D then writeln('Connection failed');
          exit;
end;


    return_attribute:=LowerCase(return_attribute);
    l:=TstringList.Create;
    l.Add('*');
    if length(SEARCH_DN)=0 then SEARCH_DN:=ldap_suffix;

    if D then writeln('QUERY:: "' + Query_string  + '" find attr:' + return_attribute);
    if D then writeln('QUERY:: IN DN "' + SEARCH_DN  + '"');

    if not ldap_common.Search(SEARCH_DN, False, Query_string, l) then begin
       if D then writeln('QUERY::  failed "' + ldap_common.FullResult + '"');
       exit;
    end;

 if D then writeln('QUERY:: Results Count :' + IntToStr(ldap_common.SearchResult.Count));


 if ldap_common.SearchResult.Count=0 then begin
     if D then writeln('QUERY::  no results...');
       exit();
 end;

 if Z then writeln(CRLF +CRLF +'************************************************');


 for i:=0 to ldap_common.SearchResult.Count -1 do begin
      if D then writeln('QUERY:: ObjectName.......: "' +ldap_common.SearchResult.Items[i].ObjectName + '"');
      DN_ROOT:=ldap_common.SearchResult.Items[i].ObjectName;
      if return_attribute='objectname' then begin
         exit;
      end;

      if D then writeln('QUERY:: Count attributes.: ' +IntToStr(ldap_common.SearchResult.Items[i].Attributes.Count));

      for t:=0 to ldap_common.SearchResult.Items[i].Attributes.Count -1 do begin

      AttributeNameQ:=LowerCase(ldap_common.SearchResult.Items[i].Attributes[t].AttributeName);
      if D then writeln('QUERY:: Attribute name[' + IntToStr(t) + '].......: "' + AttributeNameQ + '"');

     TEMP_LIST.Clear;
     if AttributeNameQ=return_attribute then begin
              if D then writeln('QUERY:: Count items......: ' +IntToStr(ldap_common.SearchResult.Items[i].Attributes.Items[t].Count));
              SetLength(result, 0);
              for u:=0 to ldap_common.SearchResult.Items[i].Attributes.Items[t].Count-1 do begin
              
                  value_result:=ldap_common.SearchResult.Items[i].Attributes.Items[t].Strings[u];
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


end;




function Tldap.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
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
function Tldap.get_LDAP(key:string):string;
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
function Tldap.get_CONF(key:string):string;
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

function Tldap.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
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
//##############################################################################
PROCEDURE Tldap.Output(zText:string);
      var
 myFile : TextFile;
        TargetPath:string;

      BEGIN

        TargetPath:='/tmp/mailman.txt';

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, zText);
           CloseFile(myFile);
        EXCEPT
             writeln(ztext + '-> error writing ' +     TargetPath);
          END;
      END;
//#############################################################################
end.

