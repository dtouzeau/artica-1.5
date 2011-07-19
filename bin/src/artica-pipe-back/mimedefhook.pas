unit mimedefhook;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,mimemess, mimepart,articaldap,global_conf,bogofilter,artica_mysql;

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
     Domains       :TstringList;
     ldap          :Tarticaldap;
     mysql         :Tartica_mysql;
     procedure     AddRecipient(email:string);
     procedure     LOAD_SENDER();
     GLOBAL_INI    :myconf;
     globalCommands:string;
     function   html_size_verif_rules(recipient:string;sender:string;ext:string;size:string):boolean;
     function   html_size_verif_extensions(ext_source:string;ext_dest:string;size_source:string;size:string):boolean;
     procedure  ScanRecipts_inCOMMANDS();
     procedure  performStats();
     function   bogofilter(mailpath:string):boolean;
     function   bogofilter_spam(mailpath:string):boolean;
     procedure  bogofilter_learn(mailpath:string);
     procedure  autowhite(mailpath:string);
     procedure  AddDomain(email:string);
     procedure  Duplicates(mailpath:string);

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
       Domains:=TstringList.Create;
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
procedure TmimeDefhook.ScanRecipts_inCOMMANDS();
var
l:TstringList;
i:integer;
begin

if not FileExists(hookpath + '/COMMANDS') then begin
     LOGS.Syslogs('WARNING! Unable to stat ' + hookpath + '/COMMANDS');
     exit;
  end;

  l:=TstringList.Create;
  l.LoadFromFile(hookpath + '/COMMANDS');
   RegExpr.Expression:='^R<(.+?)>';
  for i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
           AddRecipient(RegExpr.Match[1]);
        end;
     end;
end;
//##############################################################################
procedure TmimeDefhook.performStats();
var
   zDate:string;
   l:TiniFile;
   FileName:string;
begin
   zDate:=LOGS.DateTimeNowSQL();
    RegExpr.Expression:='values=<(.*?)>;(.+?);(.+)\s+--end';
    if RegExpr.Exec(globalCommands) then begin
        FileName:=LOGS.MD5FromString(zDate + RegExpr.Match[1]+RegExpr.Match[2]+RegExpr.Match[3]);
        ForceDirectories('/opt/artica/mimedefang-hooks/stats');
        l:=TiniFile.Create('/opt/artica/mimedefang-hooks/stats/'+FileName);
        l.WriteString('STATS','FROM',RegExpr.Match[1]);
        l.WriteString('STATS','TO',RegExpr.Match[3]);
        l.WriteString('STATS','IP',RegExpr.Match[2]);
        l.WriteString('STATS','TIME',zDate);
        l.UpdateFile;
        l.Free;
    end else begin
        LOGS.logs('performStats() not match '+ RegExpr.Expression +' for ' +globalCommands + ' die...' );
    end;
end;
//##############################################################################
procedure TmimeDefhook.ScanHeaders();
var
   i:integer;
   l:TstringList;
begin
HEADERS:=TstringList.Create;

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
 




end;

//##############################################################################
procedure TmimeDefhook.Duplicates(mailpath:string);
var
   f   :users_datas;
   i   :integer;
   md  :TstringList;
   
begin
md:=TstringList.Create;

     for i:=0 to Recipients.Count -1 do begin
          f:=ldap.UserDataFromMail(Recipients.Strings[i]);
          logs.Debuglogs('Duplicates():: For ' + f.mail + '='+f.RecipientToAdd);
          if length(f.RecipientToAdd)>0 then begin
             logs.Debuglogs('Duplicates():: ' + Recipients.Strings[i] + ' '+  f.RecipientToAdd);
              md.Add('dup:' +f.RecipientToAdd);
          end;
     end;
     try
        md.SaveToFile(mailpath + '/toadd')
     except;
      logs.Debuglogs('Duplicates():: failed to save ' + mailpath + '/toadd');
     end;
     
end;




//##############################################################################
procedure TmimeDefhook.autowhite(mailpath:string);
var
   f   :users_datas;
   i   :integer;
   md  :string;
   sql :string;
begin
     f:=ldap.UserDataFromMail(mailfrom);
     if length(f.Organization)>0 then begin
         ForceDirectories('/opt/artica/mimedefang-hooks/white');
           for i:=0 to Recipients.Count -1 do begin
               md:=LOGS.MD5FromString(mailfrom+Recipients.Strings[i]);
               sql:='INSERT INTO  `artica_backup`.`artica_whitelist`(`zMD5`,`mailfrom`,`mailto`,`zDate`) VALUES ("'+md+'","'+mailfrom+'","'+Recipients.Strings[i]+'","'+LOGS.DateTimeNowSQL()+'")';
               LOGS.WriteToFile(sql,'/opt/artica/mimedefang-hooks/white/'+md+'.sql');
               logs.Debuglogs('autowhite():: '+mailfrom+' => '+ Recipients.Strings[i] +' ( add whitelist)' );
           end;
     logs.Debuglogs('autowhite():: finish');
     exit();
     end;

     mysql:=Tartica_mysql.Create;
     for i:=0 to Recipients.Count -1 do begin
         sql:='SELECT zMD5 FROM `artica_whitelist` WHERE `mailto`="'+Recipients.Strings[i]+'" AND `mailfrom`="'+mailfrom+'" LIMIT 0,1';
         md:=mysql.STORE_SQL_SINGLE(sql,'artica_backup');
           if length(md)>0 then begin
              writeln('whitelisted ('+ md+')');
              logs.Debuglogs('autowhite():: '+mailfrom+' => '+ Recipients.Strings[i] +' ( is whitelisted)' );
              mysql.free;
              exit;
           end;
     end;

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
var
   i:Integer;
   sRegExpr:TRegExpr;
begin
    sRegExpr:=TRegExpr.Create;
    sRegExpr.Expression:='<(.+?)>';
    if sRegExpr.Exec(email) then email:=sRegExpr.Match[1];
    sRegExpr.free;
    
     for i:=0 to Recipients.Count-1 do begin
         if LowerCase(email)=Recipients.Strings[i] then exit;
     
     end;

   Recipients.Add(LowerCase(email));
   AddDomain(email);

end;
//##############################################################################
procedure TmimeDefhook.AddDomain(email:string);
var
  sRegExpr:TRegExpr;
  i:Integer;
begin
  sRegExpr:=TRegExpr.Create;
  sRegExpr.Expression:='.+?@(.+)';
  
  if not sRegExpr.Exec(email) then exit;
  
  
 for i:=0 to Domains.Count-1 do begin
         if LowerCase(sRegExpr.Match[1])=Domains.Strings[i] then exit;

     end;
  Domains.Add(LowerCase(sRegExpr.Match[1]));
  sRegExpr.free;
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
function TmimeDefhook.bogofilter(mailpath:string):boolean;
var
   bogo:Tbogofilter;
   f   :users_datas;
   cmd,bogopath,bogores :String;
   email:TMimeMess;
   i:integer;
   userpath:string;
   number:DWord;
begin

  bogo:=Tbogofilter.Create;
  RegExpr.Expression:='spamicity=([0-9\.]+)';
  bogopath:=mailpath + '.bogo';
  for i:=0 to Recipients.Count-1 do begin
         f:=ldap.UserDataFromMail(Recipients.Strings[i]);
         
         if f.BOGOFILTER_PARAM.BogoFilterMailType='spam' then begin
               logs.Debuglogs('bogofilter():: -> ' + Recipients.Strings[i] + ' is a bogofilter robot for save has spam...');
               bogofilter_spam(mailpath);
               halt(0);
         end;
         
         
         if length(f.Organization)>0 then begin
             userpath:='/opt/artica/mimedefang-hooks/'+f.Organization;
             ForceDirectories(userpath);
             logs.Debuglogs('bogofilter():: -> ' + Recipients.Strings[i]+'='+f.mail);
             cmd:=bogo.Build_CommandLine(mailpath,userpath);
             logs.Debuglogs('bogofilter():: -> ' +cmd);
             fpsystem(cmd);
             if FileExists(bogopath) then begin
                email:=TMimeMess.Create;
                email.Lines.LoadFromFile(bogopath);
                email.DecodeMessage;
                bogores:=email.Header.FindHeader('X-Bogosity');
                if RegExpr.Exec(bogores) then begin
                   number:=Round(StrToFloat(RegExpr.Match[1])*100);
                   logs.Debuglogs('bogofilter():: -> ' +RegExpr.Match[1] + ' score=' + IntToStr(number) + ' must higher than ' + IntToStr(f.BOGOFILTER_PARAM.max_rate));
                   if number>f.BOGOFILTER_PARAM.max_rate then begin
                   
                   end else begin
                       writeln(bogores);
                       halt(0);
                   end;
               end;
            end;
          end;
   end;
  
  
end;
//##############################################################################
function TmimeDefhook.bogofilter_spam(mailpath:string):boolean;
var
   bogo:Tbogofilter;
   cmd_line,userpath:string;
   f   :users_datas;
begin
     bogo:=Tbogofilter.Create;
      f:=ldap.UserDataFromMail(mailfrom);
      if length(f.Organization)=0 then exit;
      userpath:='/opt/artica/mimedefang-hooks/'+f.Organization;
      forceDirectories(userpath);
      cmd_line:=bogo.DEAMON_BIN_PATH()+' --register-spam --no-config-file --no-header-tags --bogofilter-dir=' + userpath + ' --use-syslog --input-file=' + mailpath + ' >' +ExtractFilePath(mailpath)+'tmp.bogo 2>&1';
      fpsystem(cmd_line);
      logs.Debuglogs('bogofilter():: -> ' + cmd_line);
      logs.Debuglogs('bogofilter():: -> ' + GLOBAL_INI.ReadFileIntoString(ExtractFilePath(mailpath)+'tmp.bogo'));
      
      
      writeln('BOGOFILTER_SPAM');
end;
//##############################################################################
procedure TmimeDefhook.bogofilter_learn(mailpath:string);
var
     bogo:Tbogofilter;
     cmd_line,userpath:string;
     f:ou_datas;
     i:Integer;
begin
   if not ldap.Logged then exit;

   bogo:=Tbogofilter.Create;

   for i:=0 to Domains.Count-1 do begin
       f:=ldap.Load_OU_DATAS(Domains.Strings[i]);
       if length(f.Organization)>0 then begin
            userpath:='/opt/artica/mimedefang-hooks/'+f.Organization;
            cmd_line:=bogo.DEAMON_BIN_PATH()+' --register-spam --no-config-file --no-header-tags --bogofilter-dir=' + userpath + ' --use-syslog --input-file=' + mailpath + ' >' +ExtractFilePath(mailpath)+'tmp.bogo 2>&1';
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
