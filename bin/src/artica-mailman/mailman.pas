unit mailman;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,ldap,RegExpr in 'RegExpr.pas',logs,global_conf,unix,BaseUnix;

type

  mailman_settings=record
        local_lists:TstringList;
        ldap_lists:TstringList;
        prepend:string;
        action:string;
  end;

         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;

  type
  Tmailman=class


  private
   logs:Tlogs;
   MailMan_priv:mailman_settings;
   function   is_list_in_ldap(listname:string):boolean;
   PROCEDURE  Output(zText:string);
   procedure  PatchCss_file(filepath:string);
   GLOBAL_INI:MyConf;
   D:boolean;
   ldap:Tldap;
   procedure Load_lists();

  public
      constructor Create();
      destructor  Destroy; override;
      procedure   replicate_local_lists();
      procedure   SaveList(list_name:STRING);
      function    LocalListExists(list_name:string):boolean;
      procedure   LDAP_LIST_INFOS(list_name:string);
      procedure   SaveGeneralConf();
      procedure   PatchCss();
end;

implementation

constructor Tmailman.Create();
begin


   GLOBAL_INI:=Myconf.Create;
   D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
   if D then writeln('Tmailman -> init...');
   MailMan_priv.local_lists:=TStringList.Create;
   MailMan_priv.ldap_lists:=TStringList.Create;
   logs:=Tlogs.Create;
   ldap:=Tldap.Create();
   Load_lists();
end;
//##############################################################################
destructor Tmailman.Destroy;
begin
  logs.Free;
  GLOBAL_INI.free;
  MailMan_priv.local_lists.Free;
  MailMan_priv.ldap_lists.Free;
  inherited Destroy;
end;
//##############################################################################
procedure Tmailman.Load_lists();
VAR
   DATAS:String;
   LISTS:TStringDynArray;
   LDAP_LISTS:TstringList;
   I:Integer;
begin
      if D then writeln('Load_lists() -> Load global configs');
      ldap.Create_General_config_branch();
      
      if D then writeln('Load_lists() -> /opt/artica/mailman/bin/list_lists --bare');
      DATAS:=GLOBAL_INI.ExecPipe('/opt/artica/mailman/bin/list_lists --bare');
     
     
     LISTS:=GLOBAL_INI.Explode(CRLF,DATAS);


     if D then writeln('Load_lists() -> number of lists=',length(LISTS));
     MailMan_priv.local_lists.Clear;
     for i:=0 to length(LISTS)-1 do begin
            if D then writeln('Load_lists() -> LIST[' + intToStr(i) + ']=',LISTS[i]);
            MailMan_priv.local_lists.Add(LISTS[i]);
     end;
     
     MailMan_priv.ldap_lists.Clear;
     MailMan_priv.ldap_lists:=ldap.load_mailman_lists();
     if D then writeln('Load_lists() -> number of ldap lists=',MailMan_priv.ldap_lists.Count);


end;
//##############################################################################
procedure Tmailman.LDAP_LIST_INFOS(list_name:string);
var
   CF:MailmanListParameters;
   CFG:MailmanGlobalListParameters;
begin
   CF:=ldap.mailman_config(list_name);
   CFG:=ldap.mailman_global_config();
   writeln('Waiting operation.............:',CF.MailmanListOperation);
   writeln('MailMan administrator.........:' + CF.MailManListAdministrator+ ':' + CF.MailManListAdminPassword + '@' + list_name);
   writeln('**********************************************************');
   writeln('');
   writeln('MailMan Administrator password:' + CFG.MailManAdminPassword);
   writeln('DEFAULT_EMAIL_HOST............:' + CFG.DEFAULT_EMAIL_HOST);
   writeln('DEFAULT_URL_HOST..............:' + CFG.DEFAULT_URL_HOST);
   writeln('DEFAULT_URL_PATTERN...........:' + CFG.DEFAULT_URL_PATTERN);
   writeln('PUBLIC_ARCHIVE_URL............:' + CFG.PUBLIC_ARCHIVE_URL);
   
   
   
end;

//##############################################################################
procedure Tmailman.SaveGeneralConf();
var
   CFG:MailmanGlobalListParameters;
   L:TstringList;
   RegExpr:TRegExpr;
   i:Integer;
begin
   CFG:=ldap.mailman_global_config();
   if not FileExists('/opt/artica/mailman/Mailman/Defaults.py') then exit;

   L:=TstringList.Create;
   RegExpr:=TRegExpr.Create;
   l.LoadFromFile('/opt/artica/mailman/Mailman/Defaults.py');
     For i:=0 to l.Count-1 do begin
         RegExpr.Expression:='^PUBLIC_ARCHIVE_URL';
         if RegExpr.Exec(l.Strings[i]) then l.Strings[i]:='PUBLIC_ARCHIVE_URL = ''' + CFG.PUBLIC_ARCHIVE_URL + '''';
         
         RegExpr.Expression:='^DEFAULT_EMAIL_HOST';
         if RegExpr.Exec(l.Strings[i]) then l.Strings[i]:='DEFAULT_EMAIL_HOST = ''' + CFG.DEFAULT_EMAIL_HOST + '''';

         RegExpr.Expression:='^DEFAULT_URL_HOST';
         if RegExpr.Exec(l.Strings[i]) then l.Strings[i]:='DEFAULT_URL_HOST = ''' + CFG.DEFAULT_URL_HOST + '''';
     
         RegExpr.Expression:='^DEFAULT_URL_PATTERN';
         if RegExpr.Exec(l.Strings[i]) then l.Strings[i]:='DEFAULT_URL_PATTERN = ''' + CFG.DEFAULT_URL_PATTERN + '''';
         
         RegExpr.Expression:='^VIRTUAL_HOST_OVERVIEW';
         if RegExpr.Exec(l.Strings[i]) then l.Strings[i]:='VIRTUAL_HOST_OVERVIEW ='+CFG.VIRTUAL_HOST_OVERVIEW;
     end;
   
   l.SaveToFile('/opt/artica/mailman/Mailman/Defaults.py');
   shell('/opt/artica/mailman/bin/mmsitepass ' + CFG.MailManAdminPassword + ' >/dev/null 2>&1');

end;
//##############################################################################
procedure Tmailman.PatchCss();
   var
      FileList:TstringList;
      root:string;
      i:Integer;
begin
     root:=extractFilePath(paramStr(0)) + '/install/mailman.css.path.db';
     if not FileExists(root) then begin
        writeln('PatchCss(): Unable to stat ' + root);
        exit;
     end;
     
     FileList:=TstringList.Create;
     FileList.LoadFromFile(root);
     for i:=0 to FileList.Count -1 do begin
         PatchCss_file('/opt/artica/mailman/' + FileList.Strings[i]);
     end;
     
        


end;


procedure Tmailman.SaveList(list_name:string);
var
   CF:MailmanListParameters;
   val:string;
   operation:string;
   l:TstringList;
   t:TstringList;
   command_line,username,password:string;
   D:Boolean;
   global_ini:myconf;
   
begin

CF:=ldap.mailman_config(list_name);
val:=CF.MailmanListConfigDatas;
operation:=CF.MailmanListOperation;
  l:=Tstringlist.Create;
  global_ini:=myconf.Create;
if FileExists('/tmp/mailman.txt') then shell('/bin/rm /tmp/mailman.txt');
  
Output('artica-mailman:: Operation='+ operation);
if operation='ADD' then begin

      output('artica-mailman:: SaveList('+list_name + ') this list must be created ');
      
      if not LocalListExists(list_name) then shell('/opt/artica/mailman/bin/newlist --quiet --urlhost=' + global_ini.SYSTEM_FQDN() + ' ' +  list_name + ' ' + CF.MailManListAdministrator + ' ' + CF.MailManListAdminPassword);
      command_line:='/opt/artica/mailman/bin/config_list -o /tmp/mailman.' +list_name + '.ex ' + list_name + '>>/tmp/mailman.txt';
      shell(command_line);
      Output('artica-mailman:: ' + command_line );
      l.LoadFromFile('/tmp/mailman.' +list_name + '.ex');
      CF.MailmanListOperation:='NULL';
      ldap.update_mailmain_list(list_name,CF);

      exit;
end;

if operation='DEL' then begin

      output('artica-mailman:: SaveList('+list_name + ') this list must be deleted ');

      if LocalListExists(list_name) then begin
         command_line:='/opt/artica/mailman/bin/rmlist ' + list_name + ' >>/tmp/mailman.txt';
         output('artica-mailman:: ' + command_line);
         shell(command_line);
      end;
      ldap.Delete_mailman_list(list_name);
      output('artica-mailman:: Finish');
      exit;
end;


if length(val)=0 then exit;
if not LocalListExists(list_name) then shell('/opt/artica/mailman/bin/newlist --quiet --urlhost=' + list_name + ' root@localhost.localdomain 123');

if length(val)=0 then writeln('no datas');

  l.Add(val);
  if FileExists('/tmp/mailman.' +list_name) then begin
  Output('Remove old config file /tmp/mailman.' +list_name);
  end;
  Output('Writing new config file...');
  shell('/bin/rm /tmp/mailman.' +list_name);
  l.SaveToFile('/tmp/mailman.' +list_name );
  
  command_line:='/opt/artica/mailman/bin/config_list -i ' +  '/tmp/mailman.' +list_name + ' ' + list_name + ' >>/tmp/mailman.txt 2>&1';
  Output(command_line);
  shell(command_line);
  
  command_line:='/opt/artica/mailman/bin/withlist -l -r fix_url ' +list_name + '  --urlhost=' + CF.HostNameList + ' >>/tmp/mailman.txt 2>&1';
  Output(command_line);
  shell(command_line);
  
  if D then begin
      l.LoadFromFile('/tmp/mailman.txt');
      writeln(l.Text);

  end;

  command_line:='/opt/artica/mailman/bin/config_list -o /tmp/mailman.' +list_name + '.ex ' + list_name;
  shell(command_line);
  if fileexists('/tmp/mailman.' +list_name + '.ex') then begin
        l.LoadFromFile('/tmp/mailman.' +list_name + '.ex');
        CF.MailmanListConfigDatas:=l.Text;
        ldap.update_mailmain_list(list_name,CF);
  
  end;
  
  


end;
//##############################################################################
function Tmailman.LocalListExists(list_name:string):boolean;
var
   i:Integer;
begin
result:=false;
   for i:=0 to MailMan_priv.local_lists.Count -1 do begin
       if Lowercase(MailMan_priv.local_lists.Strings[i])=Lowercase(list_name) then begin
            result:=true;
            break;
       end;
   
   end;


end;
//##############################################################################

procedure Tmailman.replicate_local_lists();
var
   i,t:integer;
   tmpfile,command_line:string;
   
begin
     for i:=0 to MailMan_priv.local_lists.Count -1 do begin
      if not is_list_in_ldap(MailMan_priv.local_lists.Strings[i]) then begin
          tmpfile:=trim('/tmp/mailman_' + trim(lowercase(MailMan_priv.local_lists.Strings[i])) + '.txt ');
          command_line:='/opt/artica/mailman/bin/config_list -o ' + tmpfile + ' ' + MailMan_priv.local_lists.Strings[i];
          if D then writeln(command_line);
          shell(command_line);
          
          for t:=0 to 50 do begin
              sleep(100);
              if FileExists(tmpfile) then begin
                 break;
              end else begin
                  if D then writeln('Waiting "' + tmpfile + '"');
              end;
          end;
          
          GLOBAL_INI.StripDiezes(tmpfile);
          writeln('Adding list -> ' + MailMan_priv.local_lists.Strings[i] + ' into ldap');
          ldap.Create_mailmain_list(MailMan_priv.local_lists.Strings[i],GLOBAL_INI.ReadFileIntoString(tmpfile));
          
      end;
      end;



end;
//##############################################################################
function Tmailman.is_list_in_ldap(listname:string):boolean;
var
   i:integer;
begin
result:=false;
  for i:=0 to MailMan_priv.ldap_lists.Count -1 do begin

      if lowercase(listname)=lowercase(MailMan_priv.ldap_lists.Strings[i]) then begin
          if D then writeln(lowercase(listname) + '=EXISTS');
         result:=true;
         exit;
         break;
      end;
  end;
   if D then writeln(lowercase(listname) + '=NOT EXISTS');

end;
//##############################################################################
PROCEDURE Tmailman.Output(zText:string);
      var
         myFile : TextFile;
        TargetPath:string;
        Logs:Tlogs;
      BEGIN
        logs:=Tlogs.Create;
         logs.logs(zText);
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
procedure Tmailman.PatchCss_file(filepath:string);
var
   L:TstringList;
   RegExpr,RegExpr2:TRegExpr;
   i:Integer;
   lineToInsert:string;
begin
  if not FileExists(filepath) then exit;
  l:=TstringList.Create;
  l.LoadFromFile(filepath);
  lineToInsert:='<link href="../../css/mailman/main.css" rel="StyleSheet" type="text/css"/>';
  RegExpr:=TRegExpr.Create;
  RegExpr2:=TRegExpr.Create;
  RegExpr.ModifierI:=true;;

  RegExpr2.Expression:='main\.css';
  for i:=0 to l.Count -1 do begin
  RegExpr.Expression:='(.*)</(head|HEAD)>';
  
      if RegExpr.Exec(l.Strings[i]) then begin
           if not RegExpr2.Exec(RegExpr.Match[1]) then begin
              writeln('Patching ' + filepath);
              l.Strings[i]:=RegExpr.Match[1] + lineToInsert + '</head>';
              l.SaveToFile(filepath);
           end;
           break;
      end;

      
  end;

end;

end.
