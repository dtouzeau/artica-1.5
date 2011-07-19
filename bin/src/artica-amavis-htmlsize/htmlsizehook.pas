unit htmlsizeHook;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,mimemess, mimepart,articaldap,global_conf,bogofilter,artica_mysql,synachar,synacode;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;
  
type HtmlSizeSettings=record
     FileList:string;
     Global_uri:string;
     GlobalConfig:string;
     Organization:string;
     disclaimer:string;
     Prepended:boolean;
end;

  type
  ThtmlsizeHook=class


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
     MessageParts  :TstringList;
     GlobalTrapText:string;
     HtmlSizeQueue :string;
     procedure     AddRecipient(email:string);
     function      ParsePart2(SubPart:TMimePart):boolean;
     function      DecodeSubPart(SubPart:TMimePart):string;
     function      CheckExtensions(filename:string):boolean;
     GLOBAL_INI    :myconf;
     globalCommands:string;

     procedure  AddDomain(email:string);
     procedure  Duplicates(mailpath:string);
     function   CompressParts(Mime:TMimeMess):TMimeMess;
     function   ExtractFileNameFromHeader(header:string):string;
     function   Compress(filename:string):string;
     function   ExtractMailFrom(Mime:TMimeMess):string;
     function   ExtractMailTo(Mime:TMimeMess):string;
     procedure  MailFailed(filepath:string);
     function   ExtractMailToTsring(Mime:TMimeMess):TstringList;
     procedure  BuildParts(Mime:TMimeMess);
     function   ReplaceParts(Mime:TMimeMess;Settings:HtmlSizeSettings):TMimeMess;
     function   ReplaceParts_testFile(filelist:string;filename:string):boolean;
     
     
     procedure  MailHTMLStrip(FilePath:string);
     function   VerifHTMLSizeLdap(recipient:string;sender:string):HtmlSizeSettings;
     function   VerifExtensionsAndSize(extensionToCheck:string;SizeToCheck:Integer):string;
     
     
     ExtensionsList:TstringList;
     CompressQueue:string;
     procedure ResendMail(filepath:string);
     FileList:TstringList;

public
    procedure   Free;
    constructor Create();
    procedure   MailCompress(filepath:string);
END;

implementation

constructor ThtmlsizeHook.Create();
var
   i:integer;
   s:string;
   mailpath:string;

begin

       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       Mime:=TMimeMess.Create;
       RegExpr:=TRegExpr.Create;
       Recipients:=TstringList.Create;
       Domains:=TstringList.Create;
       ldap:=Tarticaldap.Create;
       GLOBAL_INI:=myconf.Create;
       ExtensionsList:=TstringList.Create;
       MessageParts:=TstringList.Create;
       s:='';
       
       
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
 
 
        if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
 
 
 globalCommands:=s;
 RegExpr.Expression:='--path=(.+?)\s+';
if RegExpr.Exec(globalCommands) then begin
   LOGS.Debuglogs('create():: '+globalCommands );
   LOGS.Debuglogs('create():: ' + RegExpr.Match[1]);
   mailpath:= RegExpr.Match[1];
   exit;
end;

 if length(SYS.GET_INFO('HtmlsizeQueue'))=0 then SYS.set_INFO('HtmlsizeQueue','/var/spool/artica/htmlsize');
        if length(SYS.GET_INFO('CompressQueue'))=0 then SYS.set_INFO('CompressQueue','/var/spool/artica/compress');

        ForceDirectories(SYS.GET_INFO('HtmlsizeQueue'));
        ForceDirectories(SYS.GET_INFO('CompressQueue'));
        fpsystem('/bin/chmod -R 755 '+SYS.GET_INFO('HtmlsizeQueue')+' && /bin/chown -R postfix:postfix '+SYS.GET_INFO('HtmlsizeQueue'));
        fpsystem('/bin/chmod -R 755 '+SYS.GET_INFO('CompressQueue')+' && /bin/chown -R postfix:postfix '+SYS.GET_INFO('CompressQueue'));

   
  CompressQueue:=SYS.GET_INFO('CompressQueue');
  FileList:=TstringList.Create;
  SYS.DirFiles(CompressQueue,'*.eml');
  logs.Debuglogs('CompressQueue:'+CompressQueue+':'+IntToStr(SYS.DirListFiles.Count) +' file(s)');
  for i:=0 to SYS.DirListFiles.Count-1 do begin
       MailCompress(CompressQueue+'/'+SYS.DirListFiles.Strings[i]);
  end;


  HtmlSizeQueue:=SYS.GET_INFO('HtmlsizeQueue');
  SYS.DirListFiles.Clear;
  SYS.DirFiles(HtmlSizeQueue,'*.eml');
  logs.Debuglogs('HtmlSizeQueue:'+HtmlSizeQueue+':'+IntToStr(SYS.DirListFiles.Count) +' file(s)');
  if(SYS.DirListFiles.Count=0) then begin
      if length(HtmlSizeQueue)>5 then logs.OutputCmd('/bin/rm -f '+ HtmlSizeQueue+'/*.*');
  end;
  
  
  for i:=0 to SYS.DirListFiles.Count-1 do begin
       MessageParts.Clear;
       MailHTMLStrip(HtmlSizeQueue+'/'+SYS.DirListFiles.Strings[i]);
  end;

  halt(0);
  
  
 


    


      
  if not ldap.Logged then begin
      LOGS.Syslogs('WARNING! LDAP connection error...');
      LOGS.Debuglogs('create():: LDAP connection error...' );
      exit;
  end;
      
end;
//##############################################################################
procedure ThtmlsizeHook.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
procedure ThtmlsizeHook.MailCompress(filepath:string);
var
   i:integer;
   s:string;
   list1:Tstringlist;
   MimeBody:TMimePart;
   p: TMimepart;
   sender:string;
   Mime:TMimeMess;
begin

 Mime:=TMimeMess.Create;
 Mime.Lines.LoadFromFile(filepath);
 Mime.DecodeMessage;
 if ExtensionsList.Count=0 then begin
    if not FileExists('/etc/artica-postfix/settings/Daemons/AutoCompressExtensions') then begin
       logs.Debuglogs('unable to stat /etc/artica-postfix/settings/Daemons/AutoCompressExtensions');
       ResendMail(filepath);
       exit;
    end;
    ExtensionsList.LoadFromFile('/etc/artica-postfix/settings/Daemons/AutoCompressExtensions');
 end;
    
   if FileExists(SYS.LOCATE_ZIP()) then begin
       Mime:=CompressParts(Mime);
    end else begin
      logs.Syslogs('Unable to stat zip tool, aborting...');
      ResendMail(filepath);
    end;

    Mime.EncodeMessage;
    Mime.Lines.SaveToFile(filepath);
    ResendMail(filepath);

end;
//##############################################################################
procedure ThtmlsizeHook.ResendMail(filepath:string);
var
   sender:string;
   Mime:TMimeMess;
   mailto:string;
   cmd:string;
begin
 Mime:=TMimeMess.Create;
 Mime.Lines.LoadFromFile(filepath);
 Mime.DecodeMessage;
 sender:=ExtractMailFrom(Mime);
 if length(sender)=0 then begin
     logs.Debuglogs('ResendMail: no sender found');
     MailFailed(filepath);
     exit;
 end;
 logs.Debuglogs('ResendMail: Extract recipients');
 mailto:=ExtractMailTo(Mime);
if not FileExists('/usr/sbin/sendmail') then begin
   logs.Syslogs('Fatal error unable to locate /usr/sbin/sendmail');
   MailFailed(filepath);
end else begin
   logs.Debuglogs('/usr/sbin/sendmail ok '+trim(mailto));

end;
logs.Syslogs('from=<'+trim(sender)+'> to={'+trim(mailto)+'} resend...');
if length(sender)=0 then begin
   logs.Syslogs('FATAL ERROR '+filepath+' Sender is NULL !!');
   MailFailed(filepath);
   exit;
end;
cmd:='/usr/sbin/sendmail -bm -t "'+mailto+'" "'+sender +'"<'+filepath;
logs.OutputCmd(cmd);
logs.DeleteFile(filepath);
 

end;
//##############################################################################
procedure ThtmlsizeHook.MailFailed(filepath:string);
begin
  forceDirectories('/var/spool/artica-failed');
  fpsystem('/bin/mv ' + filepath + ' /var/spool/artica-failed/'+ExtractFileName(filepath));
  logs.Syslogs('File was stored in /var/spool/artica-failed/'+ExtractFileName(filepath));
end;
//##############################################################################
procedure ThtmlsizeHook.Duplicates(mailpath:string);
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
procedure ThtmlsizeHook.AddRecipient(email:string);
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
procedure ThtmlsizeHook.AddDomain(email:string);
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
function ThtmlsizeHook.ExtractMailFrom(Mime:TMimeMess):string;
var
   sender:string;
   RegExpr:TRegExpr;
begin
   sender:=Mime.Header.From;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='<(.+?)>';
   if RegExpr.Exec(sender) then sender:=RegExpr.Match[1];
   RegExpr.free;
   result:=sender;
end;
//##############################################################################
function ThtmlsizeHook.ExtractMailTo(Mime:TMimeMess):string;
var
   mailto:string;
   i:integer;
   l:TstringList;
begin

     l:=TstringList.Create;
     l.AddStrings(ExtractMailToTsring(Mime));
     for i:=0 to l.Count-1 do begin
        mailto:=mailto+' ' + l.Strings[i];
     end;
    result:=mailto;
    l.free;
end;
//##############################################################################
function ThtmlsizeHook.ExtractMailToTsring(Mime:TMimeMess):TstringList;
var
   mailto:string;
   i:integer;
   RegExpr:TRegExpr;
   l:TstringList;
begin
     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;

     for i:=0 to Mime.Header.ToList.Count-1 do begin


        RegExpr.Expression:='<(.+?)>';
        if RegExpr.Exec(Mime.Header.ToList.Strings[i]) then begin
          mailto:=Lowercase(RegExpr.Match[1])
        end else begin
           mailto:=Lowercase(Mime.Header.ToList.Strings[i]);
        end;

        RegExpr.Expression:='^.+?cn=(.+)';
        if RegExpr.Exec(mailto) then mailto:=RegExpr.Match[1];


        RegExpr.Expression:='recipients_cn=(.+)';
        if RegExpr.Exec(mailto) then mailto:=RegExpr.Match[1];

        logs.Debuglogs('ExtractMailToTsring:: Add recipient "'+mailto+'"');

        l.Add(mailto);
     end;
     RegExpr.free;
    result:=l;
end;
//##############################################################################
procedure ThtmlsizeHook.MailHTMLStrip(FilePath:string);
var
   i:integer;
   s:string;
   list1:Tstringlist;
   MimeBody:TMimePart;
   p: TMimepart;
   sender:string;
   Mime:TMimeMess;
   MimeSource:TMimeMess;
   mailfrom:string;
   Recipients:TstringList;
   HtmlSize:HtmlSizeSettings;
   FileChecked:string;
begin

  if not ldap.Logged then begin
          LOGS.Syslogs('MailHTMLStrip() FATAL LDAP error....');
          exit;
  end;

     
 Mime:=TMimeMess.Create;
 Mime.Lines.LoadFromFile(filepath);
 Mime.DecodeMessage;

 BuildParts(Mime);

 mailfrom:=ExtractMailFrom(Mime);
 Recipients:=TStringList.Create;
 Recipients.AddStrings(ExtractMailToTsring(Mime));
 LOGS.Debuglogs('MailHTMLStrip():: Checking routing from:<'+mailfrom+'> To: '+IntToStr(Recipients.Count) + ' recipient(s)');
 
     
    for i:=0 to Recipients.Count-1 do begin
             HtmlSize:=VerifHTMLSizeLdap(Recipients.Strings[i],mailfrom);
             LOGS.Debuglogs('htmlsize():: HtmlSize.FileList: ' + IntToStr(length(HtmlSize.FileList)) +' entries');
             if length(HtmlSize.FileList)>0 then begin
                  LOGS.Debuglogs('htmlsize():: Match for ' + Recipients.Strings[i]+' ' +FileChecked+' files list');
                  htmlSize.Prepended:=false;
                  MimeSource:=TMimeMess.Create;
                  MimeSource.Lines.LoadFromFile(filepath);
                  MimeSource.DecodeMessage;
                  MimeSource:=ReplaceParts(MimeSource,HtmlSize);
                  MimeSource.EncodeMessage;
                  if DirectoryExists(HtmlSizeQueue+'/'+MimeSource.Header.MessageID) then fpsystem('/bin/rm -rf '+HtmlSizeQueue+'/'+MimeSource.Header.MessageID);
                  MimeSource.Lines.SaveToFile(FilePath);
                  break;
             end;
   end;
 LOGS.Debuglogs('MailHTMLStrip():: resend mail...');
 ResendMail(FilePath);
     
end;
//##############################################################################
function ThtmlsizeHook.VerifHTMLSizeLdap(recipient:string;sender:string):HtmlSizeSettings;
var
   f:users_datas;
   RegExpr1:TRegExpr;
   t,i,u:integer;
   exts:TStringDynArray;
   HtmlSize:HtmlSizeSettings;
   fromre:string;
   tore:string;
   sizere:string;
   extre:string;
   filechecked:string;
   ExtensionToCheck:string;
   myldap:Tarticaldap;
begin

  myldap:=Tarticaldap.Create();
  f:=myldap.UserDataFromMail(recipient);
  myldap.free;


  if length(f.Organization)=0 then begin
       logs.Debuglogs('VerifHTMLSizeLdap():: ' + recipient + ' has "' + f.uid + '" have no organization..');
       exit();
  end;
    

  if f.bightml.BigMailHtmlRules.Count=0 then begin
       logs.Debuglogs('VerifHTMLSizeLdap():: no rules for this account');
       exit;
    end;

    if f.bightml.BigMailHTMLEnabled='no' then begin
        logs.Debuglogs('VerifHTMLSizeLdap():: Disabled for this account');
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
                  logs.Debuglogs('VerifHTMLSizeLdap::Rule['+IntToStr(i)+'] recipient match '+tore+' sender match '+fromre);
                  
                  exts:=GLOBAL_INI.Explode(',', extre);
                  for t:=0 to length(exts)-1 do begin
                      if length(exts[i])>0 then begin
                        filechecked:=VerifExtensionsAndSize(exts[i],StrToInt(sizere));
                        if length(filechecked)>0 then begin
                            logs.Debuglogs('VerifHTMLSizeLdap::Match ' + filechecked);
                            HtmlSize.FileList:=filechecked;
                            HtmlSize.GlobalConfig:=f.bightml.BigMailHtmlConfig;
                            HtmlSize.Organization:=f.Organization;
                            HtmlSize.disclaimer:=f.bightml.BigMailHtmlBody;
                            exit(HtmlSize);
                        end;
                      end;
                  end;
                  

                  
               end;
           end;
       end;
end;
    
result:=HtmlSize;

end;
//##############################################################################
function ThtmlsizeHook.VerifExtensionsAndSize(extensionToCheck:string;SizeToCheck:Integer):string;
var
   i:Integer;
   RegExpr:TRegExpr;
   ext:string;

begin
   RegExpr:=TRegExpr.Create;
   extensionToCheck:='.'+extensionToCheck;
   RegExpr.Expression:='(.+?);([0-9]+)';
   for i:=0 to MessageParts.Count-1 do begin
     if RegExpr.Exec(MessageParts.Strings[i]) then begin
         ext:=ExtractFileExt(RegExpr.Match[1]);
         if ext=extensionToCheck then begin
             if StrToInt(RegExpr.Match[2])>=SizeToCheck then begin
                result:=result+','+ExtractFileName(RegExpr.Match[1]);
             end;
         end;
     end;

   end;
   
 RegExpr.Free;

end;
//##############################################################################
function ThtmlsizeHook.CompressParts(Mime:TMimeMess):TMimeMess;
      var
        i:integer;
        o:integer;
        s:string;
        filename:string;
        Headers:string;
        NewCompressedFile:string;
BEGIN
     logs.Debuglogs('CompressParts:: Sub Part Count :' + IntToStr(mime.MessagePart.GetSubPartCount));
     s:=format('%-30s',[mime.MessagePart.primary+'/'+mime.MessagePart.secondary]);
     s:=lowercase(s);
     logs.Debuglogs('CompressParts:: First part:' + s);

     for i:=0 to mime.MessagePart.GetSubPartCount-1 do begin
            s:=trim(format('%-30s',[mime.MessagePart.GetSubPart(i).Primary+'/'+mime.MessagePart.GetSubPart(i).secondary]));
            logs.Debuglogs('CompressParts::['+IntToStr(i)+'], Part:'+s+' Encoding:'+mime.MessagePart.GetSubPart(i).Encoding+' Charset:'+mime.MessagePart.GetSubPart(i).DefaultCharset);

            mime.MessagePart.GetSubPart(i).DecodePartHeader;
            Headers:=mime.MessagePart.GetSubPart(i).Headers.Text;
            logs.Debuglogs('CompressParts::['+IntToStr(i)+'], Filename:'+mime.MessagePart.GetSubPart(i).FileName);
            filename:=mime.MessagePart.GetSubPart(i).FileName;
            filename:=AnsiReplaceText(filename,' ','_');
            logs.Debuglogs('CompressParts:: Filename:'+filename + '('+ExtractFileExt(filename)+')');
            if length(filename)>0 then begin
              if CheckExtensions(filename) then begin
                   mime.MessagePart.GetSubPart(i).DecodePart;
                   mime.MessagePart.GetSubPart(i).DecodedLines.SaveToFile(CompressQueue+'/'+filename);
                   NewCompressedFile:=Compress(filename);
                   if FileExists(NewCompressedFile) then begin
                       logs.DeleteFile(CompressQueue+'/'+filename);
                       mime.MessagePart.DeleteSubPart(i);
                       mime.AddPartBinaryFromFile(NewCompressedFile,mime.MessagePart);
                       logs.DeleteFile(NewCompressedFile);
                       result:=CompressParts(Mime);
                   end;

               end;
            end;
    end;
    
    result:=Mime;
     

END;
//##############################################################################
procedure ThtmlsizeHook.BuildParts(Mime:TMimeMess);
      var
        i:integer;
        o:integer;
        s:string;
        filename:string;
        Headers:string;
        TargetedFile:string;
        TargetedSize:string;
        tmpstr:string;
BEGIN
     s:=format('%-30s',[mime.MessagePart.primary+'/'+mime.MessagePart.secondary]);
     s:=lowercase(s);

     for i:=0 to mime.MessagePart.GetSubPartCount-1 do begin
            s:=trim(format('%-30s',[mime.MessagePart.GetSubPart(i).Primary+'/'+mime.MessagePart.GetSubPart(i).secondary]));
            mime.MessagePart.GetSubPart(i).DecodePartHeader;
            Headers:=mime.MessagePart.GetSubPart(i).Headers.Text;
            filename:=mime.MessagePart.GetSubPart(i).FileName;


            if length(filename)>0 then begin
                   TargetedFile:=HtmlSizeQueue+'/'+mime.Header.MessageID+'/'+ filename;
                   ForceDirectories(ExtractFilePath(TargetedFile));
                   mime.MessagePart.GetSubPart(i).DecodePart;
                   if not FileExists(TargetedFile) then mime.MessagePart.GetSubPart(i).DecodedLines.SaveToFile(TargetedFile);
                   TargetedSize:=IntTostr(logs.GetFileSizeMo(TargetedFile));
                   logs.Debuglogs('BuildParts:: Filename:'+filename + '('+ExtractFileExt(filename)+')'+ '['+TargetedSize+'Mb]');
                   MessageParts.Add(TargetedFile+';'+TargetedSize);
                   mime.MessagePart.DeleteSubPart(i);
                   BuildParts(Mime);
                   exit;
            end;
    end;

END;
//##############################################################################
function ThtmlsizeHook.ReplaceParts(Mime:TMimeMess;Settings:HtmlSizeSettings):TMimeMess;
      var
        i:integer;
        o:integer;
        s:string;
        filename:string;
        Headers:string;
        TargetedFile:string;
        TargetedSize:string;
        tmpstr:string;
        Config:TiniFile;
        TargetedConfig:string;
        l:TstringList;
        uri:string;
        urifile:string;
        prependsubject:string;
BEGIN
     s:=format('%-30s',[mime.MessagePart.primary+'/'+mime.MessagePart.secondary]);
     s:=lowercase(s);
     TargetedConfig:=HtmlSizeQueue+'/'+mime.Header.MessageID+'/Conf.ini';
     ForceDirectories(ExtractFilePath(TargetedConfig));
     l:=TstringList.Create;

     l.Add(Settings.GlobalConfig);
     l.SaveToFile(TargetedConfig);
     l.Clear;
     

     Config:=TiniFile.Create(TargetedConfig);
     uri:=Config.ReadString('config','hostname','https://127.0.0.1:9000/striped_attachments');
     prependsubject:=Config.ReadString('config','prependsubject','[message too big]');
     Config.Free;
     if not Settings.Prepended then begin
        logs.Debuglogs('ReplaceParts():Prepend the subject');
        mime.Header.Subject:=prependsubject+' '+mime.Header.Subject;
        Settings.Prepended:=True;
     end;
     
     
     logs.Debuglogs('ReplaceParts(): analyze '+IntToStr(mime.MessagePart.GetSubPartCount)+ ' Parts');
     for i:=0 to mime.MessagePart.GetSubPartCount-1 do begin
            s:=trim(format('%-30s',[mime.MessagePart.GetSubPart(i).Primary+'/'+mime.MessagePart.GetSubPart(i).secondary]));
            mime.MessagePart.GetSubPart(i).DecodePartHeader;
            Headers:=mime.MessagePart.GetSubPart(i).Headers.Text;
            filename:=mime.MessagePart.GetSubPart(i).FileName;
            logs.Debuglogs('ReplaceParts():Check ['+filename+']');
            if length(filename)>0 then begin
                   if ReplaceParts_testFile(Settings.FileList,filename) then begin
                      logs.Debuglogs('ReplaceParts():: replace '+filename);
                      TargetedFile:='/var/spool/artica-filter/bightml/'+Settings.Organization+'--'+filename;
                      ForceDirectories(ExtractFilePath(TargetedFile));
                      mime.MessagePart.GetSubPart(i).DecodePart;
                      mime.MessagePart.GetSubPart(i).DecodedLines.SaveToFile(TargetedFile);
                      urifile:='<p style="margin:5px;padding:5px;background-color:#CCCCCC;border:1px solid black">';
                      urifile:=urifile+'<strong style="font-size:14px"><a href="'+uri+'/'+Settings.Organization+'--'+filename+'">'+Settings.Organization+'--'+filename+'</a></strong></p>';
                      l.Clear;
                      l.Add(settings.disclaimer);
                      l.Add(urifile);
                      l.SaveToFile(TargetedFile+'.html');
                      mime.MessagePart.DeleteSubPart(i);
                      mime.AddPartHTMLFromFile(TargetedFile+'.html',mime.MessagePart);
                      logs.DeleteFile(TargetedFile+'.html');
                      result:=ReplaceParts(Mime,Settings);
                      exit;
                   end;
            end;
    end;
 result:=Mime;
END;
//##############################################################################
function ThtmlsizeHook.ReplaceParts_testFile(filelist:string;filename:string):boolean;
var
   filess:TStringDynArray;
   i:integer;
begin
   result:=false;
   filess:=GLOBAL_INI.Explode(',',filelist);
   for i:=0 to length(filess)-1 do begin
       if filess[i]=filename then begin
          result:=true;
          break;
       end;
   end;
end;
//##############################################################################



function ThtmlsizeHook.Compress(filename:string):string;
var
   newfilename:string;
   cmd:string;
begin
   newfilename:=CompressQueue+'/'+AnsiReplaceText(filename,ExtractFileExt(filename),'.zip');
   if FileExists(CompressQueue+'/'+filename) then begin
      cmd:=SYS.LOCATE_ZIP()+' -9 '+newfilename+' '+CompressQueue+'/'+filename;
      logs.OutputCmd(cmd);
      if FileExists(newfilename) then exit(newfilename);
   end;

end;


function ThtmlsizeHook.CheckExtensions(filename:string):boolean;
var
   i:integer;
   RegExpr:TRegExpr;
   ext:string;
begin

  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='\.(.+?)$';
  if not RegExpr.Exec(filename) then begin
     logs.Debuglogs('ThtmlsizeHook.CheckExtensions():: unable to determine extensions in ' + filename);
     RegExpr.free;
     exit(false);
  end;
  ext:=trim(lowerCase(RegExpr.Match[1]));
  
  for i:=0 to ExtensionsList.Count-1 do begin
     if trim(lowerCase(ExtensionsList.Strings[i]))=ext then begin
        result:=true;
        break;
     end;
  end;
  
  RegExpr.free;

end;
//##############################################################################
function ThtmlsizeHook.ExtractFileNameFromHeader(header:string):string;
var
RegExpr:TRegExpr;
filename:string;
begin
   RegExpr:=TRegExpr.Create;
   
   if NeedCharsetConversion(header) then begin

   end;
   
   

RegExpr.Expression:='name="(.*?)=\?ISO-8859-5\?Q\?(.+?)\?="';
   if RegExpr.Exec(header) then begin
       filename:=RegExpr.Match[1]+CharsetConversion(RegExpr.Match[2],ISO_8859_5,GetCurCP);
       result:=filename;
       RegExpr.free;
       exit;
   end;
   
   RegExpr.Expression:='name="(.*?)=\?UTF-8\?B\?(.+?)\?="';
   if RegExpr.Exec(header) then begin
       filename:=RegExpr.Match[1]+DecodeBase64(RegExpr.Match[2]);
       result:=filename;
       RegExpr.free;
       exit;
   end;
   
   RegExpr.Expression:='filename=(.+?)\.(.[a-zA-Z0-9]+)';
   if RegExpr.Exec(header) then begin
      filename:=RegExpr.Match[1];
      filename:=AnsiReplaceText(filename,'"','');
      filename:=AnsiReplaceText(filename,'''','');
      result:=filename;
      RegExpr.free;
      exit;
   end;
    RegExpr.Expression:='name=(.+?)\.(.[a-zA-Z0-9]+)\s+';
 if RegExpr.Exec(header) then begin
      filename:=RegExpr.Match[1];
      filename:=AnsiReplaceText(filename,'"','');
      filename:=AnsiReplaceText(filename,'''','');
      result:=filename;
      RegExpr.free;
      exit;
   end;
    
    
end;
//##############################################################################
function ThtmlsizeHook.ParsePart2(SubPart:TMimePart):boolean;
var
   o:integer;
   s:string;
   HTML_FOUND:boolean;
   D:boolean;
begin
     HTML_FOUND:=false;
     result:=false;
     SubPart.DecodePart;
     if D then writeln('ParsePart2: Subpart:' +intToStr(SubPart.GetSubPartCount));

     for o:=0 to SubPart.GetSubPartCount-1 do begin
                s:=format('%-30s',[SubPart.GetSubPart(o).primary+'/'+SubPart.GetSubPart(o).secondary]);
                s:=lowercase(s);
                if D then writeln('ParsePart2: Content-type(' + intToStr(o)+'):' +s);

                if  trim(s)='text/html' then begin
                      //DecodeSubPart(SubPart.GetSubPart(o));
                      HTML_FOUND:=true;
                      result:=true;
                      break;
                end;
;
                if trim(s)='text/plain' then begin
                   if HTML_FOUND=false then begin
                      // DecodeSubPart(SubPart.GetSubPart(o));
                       result:=true;
                       break
                   end;
                end;

              if trim(s)='text/rfc822-headers' then begin
                if HTML_FOUND=false then begin
                  // DecodeSubPart(SubPart.GetSubPart(o));
                   result:=true;
                   break
                end;
              end;

              if trim(s)='multipart/alternative' then begin
                if HTML_FOUND=false then begin
                   result:=ParsePart2(SubPart.GetSubPart(o));
                   break
                end;
              end;

              if trim(s)='text/rfc822-headers' then begin
                 if HTML_FOUND=false then begin
                   //DecodeSubPart(mime.MessagePart.GetSubPart(o));
                   result:=true;
                   break
                end;
              end;

              if trim(s)='multipart/alternative' then begin
                 result:=ParsePart2(mime.MessagePart.GetSubPart(o));
                 break;
              end;

              if trim(s)='multipart/related' then begin
                 result:=ParsePart2(mime.MessagePart.GetSubPart(o));
                 break;
              end;

              if trim(s)='multipart/mixed' then begin
                 result:=ParsePart2(mime.MessagePart.GetSubPart(o));
                 break;
              end;


    end;

end;
//##############################################################################

function ThtmlsizeHook.DecodeSubPart(SubPart:TMimePart):string;
var
   i,temp_count:integer;

   s,j:string;
   RegExpr:TRegExpr;
   MESSSAGE_TEXT,HEADERLIST:TstringList;
   BODY:TStream;
   COMMANDS,MailFrom_text,MailFrom_name,OU_NAME:string;
   MailfromParsed:boolean;
   content_type:string;
   MAILTO,MESSAGE_DATE:string;
   sql,Subject,message_spam_rate,message_id,backupType,mailfrom_domain,MailSize,SPAM_INFO,HeadFilePath,quarantineStore:string;
   D,ST,A,SQ:boolean;
   LOGS:TLogs;
   DELETE_THE_FILE:boolean;

begin
  A:=false;
end;
//##############################################################################


end.

