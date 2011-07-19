unit mimedefhook;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,mimemess, mimepart,articaldap,global_conf;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  TmimeDefhook=class


private
     LOGS          :Tlogs;
     artica_path   :string;
     hookpath      :string;
     MessageID     :string;
     SYS           :Tsystem;
     HEADERS       :TstringList;
     Mime          :TMimeMess;
     RegExpr       :TRegExpr;
     mailfrom      :string;
     GLOBAL_SUBJECT:string;
     Recipients    :TstringList;
     ldap          :Tarticaldap;
     procedure     AddRecipient(email:string);
     procedure     LOAD_SENDER();
     GLOBAL_INI    :myconf;
     globalCommands:string;
     function   html_size_verif_rules(recipient:string;sender:string;ext:string;size:string):boolean;
     function   html_size_verif_extensions(ext_source:string;ext_dest:string;size_source:string;size:string):boolean;

public
    procedure   Free;
    constructor Create();
    procedure   ScanHeaders();
    function    htmlsize():string;
END;

implementation

constructor TmimeDefhook.Create();
var
   i:integer;
   s:string;
begin

       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       Mime:=TMimeMess.Create;
       RegExpr:=TRegExpr.Create;
       Recipients:=TstringList.Create;
       ldap:=Tarticaldap.Create;
       GLOBAL_INI:=myconf.Create;
       s:='';
       
       
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
 globalCommands:=s;
 RegExpr.Expression:='--path=(.+?)\s+';
 RegExpr.Exec(globalCommands);
 LOGS.Debuglogs('create():: '+globalCommands );
 LOGS.Debuglogs('create():: ' + RegExpr.Match[1]);
 hookpath:= RegExpr.Match[1];
       
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
procedure TmimeDefhook.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
procedure TmimeDefhook.ScanHeaders();
var
   i:integer;
   l:TstringList;
begin
  if not FileExists(hookpath + '/INPUTMSG') then begin
     LOGS.Syslogs('WARNING! Unable to stat ' + hookpath + '/INPUTMSG');
     exit;
  end;


  HEADERS:=TstringList.Create;
  HEADERS.LoadFromFile(hookpath + '/INPUTMSG');
  try
     HEADERS.SaveToFile('/tmp/INPUTMSG');
  except
     LOGS.Syslogs('WARNING! Unable to stat /tmp/header');
  
  end;

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
     LOGS.Debuglogs('ScanHeaders():: MessageID: ' + Mime.Header.MessageID );




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
     LOGS.Debuglogs('ScanHeaders():: perform Backup rules...');
     forceDirectories('/opt/artica/mimedefang-hooks/backup-queue');
     HEADERS.SaveToFile('/opt/artica/mimedefang-hooks/backup-queue/' + MessageID);
  end;
  

LOGS.Debuglogs('ScanHeaders():: finish die...');

end;


//##############################################################################
procedure TmimeDefhook.LOAD_SENDER();
var i:integer;
begin
 RegExpr.Expression:='sender=<(.+?)>\s+';
 if RegExpr.Exec(globalCommands) then begin
    mailfrom:=RegExpr.Match[1];
    exit;
 end;

 if FileExists(hookpath + '/SENDER') then begin
    mailfrom:=trim(GLOBAL_INI.ReadFileIntoString(hookpath + '/SENDER'));
    logs.Debuglogs('Realfrom -> "'+mailfrom+'"');
 end else begin

 end;
 
{ SYS.DirFiles(hookpath,'*');
   for i:=0 to SYS.DirListFiles.Count-1 do begin
       logs.Debuglogs('->' + hookpath + '/' + SYS.DirListFiles.Strings[i])
   end;
   
 SYS.DirFiles(hookpath+ '/Work','*');
   for i:=0 to SYS.DirListFiles.Count-1 do begin
       logs.Debuglogs('->' + hookpath + '/Work/' + SYS.DirListFiles.Strings[i])
   end;}
 
end;
//##############################################################################



procedure TmimeDefhook.AddRecipient(email:string);
var i:Integer;
begin

     for i:=0 to Recipients.Count-1 do begin
         if LowerCase(email)=Recipients.Strings[i] then exit;
     
     end;

   Recipients.Add(email);


end;
//##############################################################################

function TmimeDefhook.htmlsize():string;
var
   ext:string;
   exttype:string;
   Getsize:string;
   i:integer;
begin
     ext:=GLOBAL_INI.COMMANDLINE_EXTRACT_PARAMETERS('--ext=(.+?)\s+');
     exttype:=GLOBAL_INI.COMMANDLINE_EXTRACT_PARAMETERS('--type=(.+?)\s+');
     Getsize:=GLOBAL_INI.COMMANDLINE_EXTRACT_PARAMETERS('--size=(.+?)\s+');
     LOGS.Debuglogs('htmlsize():: EXT=' + ext + ' type=' + exttype + ' size=' + GetSize);
     
     if not ldap.Logged then begin
          LOGS.Syslogs('htmlsize() FATAL LDAP error....');
          exit;
     end;
     
     
    for i:=0 to Recipients.Count-1 do begin
        if html_size_verif_rules(Recipients.Strings[i],mailfrom,ext,GetSize) then begin
             LOGS.Debuglogs('htmlsize():: Match for ' + Recipients.Strings[i]);
             writeln('RESPONSE=TRUE');
             
             
        end;
    
    end;
     
end;
//##############################################################################

function TmimeDefhook.html_size_verif_rules(recipient:string;sender:string;ext:string;size:string):boolean;
var

f:users_datas;
i:integer;
RegExpr1:TRegExpr;
fromre,tore,extre,sizere:string;
l:TstringList;
begin
    result:=false;
    logs.Debuglogs('html_size_verif_rules():: -> ' + recipient);
    f:=ldap.UserDataFromMail(recipient);
    
    if length(f.Organization)=0 then begin
       logs.Debuglogs('html_size_verif_rules():: ' + recipient + ' has "' + f.uid + '" have no organization..');
       exit(false);
    end;

    logs.Debuglogs('html_size_verif_rules():: ' + recipient + '(' + f.Organization + ')');
    
    if f.bightml.BigMailHtmlRules.Count=0 then begin
       logs.Debuglogs('html_size_verif_rules():: no rules for this account');
       exit;
    end;
    
    if f.bightml.BigMailHTMLEnabled='no' then begin
        logs.Debuglogs('html_size_verif_rules():: Disabled for this account');
        exit;
    end;

    
    
    RegExpr1:=TRegExpr.Create;

    
    for i:=0 to  f.bightml.BigMailHtmlRules.count-1 do begin
        RegExpr1.Expression:='(.+?);(.+?);([0-9]+);(.+)';
        if RegExpr1.Exec(f.bightml.BigMailHtmlRules.Strings[i]) then begin
           fromre:=RegExpr1.Match[1];
           tore:=RegExpr1.Match[2];
           sizere:=RegExpr1.Match[3];
           extre:=RegExpr1.Match[4];
           if fromre<>'*' then fromre:=AnsiReplaceText(fromre,'*','.*?') else fromre:='.*';
           if tore<>'*' then tore:=AnsiReplaceText(tore,'*','.*?') else tore:='.*';
           RegExpr1.Expression:=fromre;
           if RegExpr1.Exec(sender) then begin
               RegExpr1.Expression:=tore;
                if RegExpr1.Exec(recipient) then begin

                   if html_size_verif_extensions(ext,extre,sizere,size) then begin
                      logs.Debuglogs('html_size_verif_rules():: From ' + fromre + ' to ' + tore + ' (' + extre+') Match');
                      l:=TstringList.Create;
                      l.Add(f.bightml.BigMailHtmlBody);
                      logs.Debuglogs('html_size_verif_rules():: SAVING ' + hookpath + '/BigMailHtmlBody');
                      TRY
                         l.SaveToFile(hookpath + '/BigMailHtmlBody');
                      EXCEPT
                         logs.Debuglogs('html_size_verif_rules():: FATAL ERROR WHILE SAVING ' + hookpath + '/BigMailHtmlBody');
                      END;
                      
                      l.Clear;

                      l.Add(f.bightml.BigMailHtmlConfig);
                      TRY
                         l.SaveToFile(hookpath + '/BigMailHtmlConfig');
                     EXCEPT
                         logs.Debuglogs('html_size_verif_rules():: FATAL ERROR WHILE SAVING ' + hookpath + '/BigMailHtmlConfig');
                      END;
                      
                      exit(true);
                      
                   end;
                   
                   
                end;
           end;
           
           

        end;
    
    end;


end;
//##############################################################################
function TmimeDefhook.html_size_verif_extensions(ext_source:string;ext_dest:string;size_source:string;size:string):boolean;
var
   exts:TStringDynArray;
   i:integer;
   sizei:integer;
begin
    result:=false;
    ext_source:=AnsiReplaceText(ext_source,'.','');

    if pos(',',ext_dest)>0 then begin
       exts:=GLOBAL_INI.Explode(',', ext_dest);
       for i:=0 to length(exts)-1 do begin
          if length(exts[i])>0 then begin
             if LowerCase(ext_source)=LowerCase(trim(exts[i])) then begin
                sizei:=StrToInt(size);
                sizei:=round(round(sizei/1024)/1000);

                if sizei>=StrToInt(size_source) then begin
                   logs.Debuglogs('html_size_verif_extensions():: Matching extension "'+ ext_source + '" Size='+IntTostr(sizei) + ' mb');
                   exit(true);
                end;

             end;
          end;
       
       end;
    end;


end;


end.
