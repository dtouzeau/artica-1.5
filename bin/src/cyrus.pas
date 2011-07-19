unit cyrus;

{$mode objfpc}{$H+}

interface

uses
Classes, SysUtils,variants, Process,Linux,BaseUnix,IniFiles,oldlinux,strutils,md5,logs,common,process_infos,
RegExpr in 'RegExpr.pas',
confiles in 'confiles.pas',
global_conf in 'global_conf.pas';


  type
  TCyrus=class


private
     GLOBAL_INI:myconf;
     LOGS:Tlogs;
     COMMON:Tcommon;
     PROC:Tprocessinfos;
     procedure xShell(cmd:string);
     memory_list_cyrus,memory_domains_cyrus:TStringList;
     function ExportDomainifExists(domain:string):boolean;
     function ExportMailBoxesifExists(email:string):boolean;
     function MD5FromFile(path:string):string;
     procedure Synchronize_virtual_mailboxes_maps();


public

      constructor Create;
      procedure Free;
      Enable_echo:boolean;
      Debug:boolean;


END;

implementation
//#####################################################################################
constructor TCyrus.Create;
begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=myconf.Create();
       LOGS:=Tlogs.Create;
       LOGS.Debug:=Debug;
       LOGS.Enable_echo:=Enable_echo;
       COMMON:=Tcommon.Create;
       COMMON.debug:=Debug;
       PROC:=Tprocessinfos.Create;
       Debug:=GLOBAL_INI.get_DEBUG_DAEMON();
       memory_list_cyrus:=TStringList.Create;
       memory_domains_cyrus:=TStringList.Create;
end;
//#####################################################################################
procedure TCyrus.Free();
begin
   GLOBAL_INI.Free;
   LOGS.Free;
   COMMON.Free;
   PROC.Free;
   memory_list_cyrus.Free;
   memory_domains_cyrus.Free;
end;

//#####################################################################################
procedure TCyrus.Synchronize_virtual_mailboxes_maps();
var virtual_postfix_path,md5_postfix,md5_my:string;

begin
 if GLOBAL_INI.get_POSTFIX_DATABASE()<>'hash' then exit;
 if not FileExists('/etc/artica-postfix/virtual_mailboxes_maps.cf') then exit;
 
 virtual_postfix_path:=GLOBAL_INI.postfix_get_virtual_mailboxes_maps();
  if debug then LOGS.logs('TCyrus.ExportMailBoxes()-> virtual_mailboxes_maps -> ' + virtual_postfix_path);
  
  if length(virtual_postfix_path)=0 then begin
      if debug then LOGS.logs('TCyrus.ExportMailBoxes()->error  virtual_mailboxes_maps is null');
      exit;
  end;
  
  md5_postfix:=MD5FromFile(virtual_postfix_path);
  md5_my:=MD5FromFile('/etc/artica-postfix/virtual_mailboxes_maps.cf');
  if md5_postfix<>md5_my then begin
      if debug then LOGS.logs('TCyrus.ExportMailBoxes()-> replicate virtual_mailboxes_maps');
      xShell('/bin/cp /etc/artica-postfix/virtual_mailboxes_maps.cf ' + virtual_postfix_path);
      xShell('/usr/sbin/postmap ' +  virtual_postfix_path);
      LOGS.logs('TCyrus.ExportMailBoxes()-> Reload postfix mail system after replicating virtual_mailboxes_maps');
      xShell('/etc/init.d/postfix reload');
      
  end;
  
  

end;



//#####################################################################################
function TCyrus.ExportMailBoxesifExists(email:string):boolean;
var i:integer;
begin
   result:=false;

   for i:=0 to memory_list_cyrus.Count -1 do begin
             if memory_list_cyrus.Strings[i]=email then begin
             result:=True;
             exit;
             end;
   end;
end;
//#####################################################################################
function TCyrus.ExportDomainifExists(domain:string):boolean;
var i:integer;
begin
   result:=false;

   for i:=0 to memory_domains_cyrus.Count -1 do begin
             if memory_domains_cyrus.Strings[i]=domain then begin
             result:=True;
             exit;
             end;
   end;
end;
//#####################################################################################



//#####################################################################################
procedure TCyrus.xShell(cmd:string);
var
   Xresponse:string;
   RegExpr:TRegExpr;
begin
 if debug=true then LOGS.logs(cmd);
 RegExpr:=TRegExpr.Create();
 RegExpr.expression:='>[a-zA-Z0-9\.\/]+';
 if not RegExpr.Exec(cmd) then cmd:=cmd + ' >/tmp/tmp.txt';
 RegExpr.Free;
 Shell(cmd);
 if debug=true then begin
    if Debug=true then LOGS.logs('TCyrus.xShell -> ' + cmd);
    Xresponse:=COMMON.ReadFileIntoString('/tmp/tmp.txt');
    if length(Xresponse)>0 then LOGS.logs(Xresponse);
 end;
end;
//####################################################################################
function TCyrus.MD5FromFile(path:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5File(path);
exit(MD5Print(Digest));
end;
//####################################################################################
end.
