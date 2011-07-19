unit ldaplearn;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, articaldap,SysUtils,strutils,Process,unix,global_conf,Logs,RegExpr in 'RegExpr.pas',IniFiles,BaseUnix, spamass,setup_libs,
zsystem;


  type
  tldaplearn=class


private
       GLOBAL_INI:myconf;
       LOGS:Tlogs;
       SYS:Tsystem;
       ldap:tarticaldap;
       slib:tlibs;

public
      constructor Create();
      procedure Free;

      //export mailboxes.
      PROCEDURE      SpamAssassin_whitelistBlacklist();
      PROCEDURE      sa_learn();
      FUNCTION       UNDERSTAND_fetchmailrc():TstringList;
      procedure      CleanCacheFile();
      
END;

implementation

constructor tldaplearn.Create();
begin
  GLOBAL_INI:=myconf.Create;
  SYS:=GLOBAL_INI.SYS;
  ldap:=tarticaldap.Create();
  LOGS:=Tlogs.Create;
  slib:=Tlibs.Create;
  if not slib.PERL_GENERIC_INSTALL('Config-IniFiles','Config::IniFiles') then begin
     logs.Syslogs('Unable to install Config::IniFiles perl extension');
     exit;
  end;


end;
//#############################################################################
PROCEDURE tldaplearn.Free();
begin
  ldap.free;
end;
//#############################################################################
PROCEDURE tldaplearn.sa_learn();
var
  f:TstringList;
  i:integer;
  userd:users_datas;
  l:TstringList;
  ftmp:string;
  cmd:string;
  RegExpr                :TRegExpr;
  WBLReplicEnable:integer;
  WBLReplicaHamEnable:integer;
  nice:string;
  spamass:Tspamass;
  razorhome,razorconf:string;
begin


//IniFiles.pm
    if ldap.Logged=false then begin
    logs.Syslogs('sa_learn:: Logged is false;aborting...');
    exit;
 end;
  spamass:=Tspamass.Create(SYS);
  spamass.RAZOR_INIT();
  razorhome:=spamass.RAZOR_GET_VALUE('razorhome');
  razorconf:=spamass.RAZOR_AGENT_CONF_PATH();

l:=Tstringlist.Create;
f:=TstringList.Create;
if not TryStrToInt(SYS.GET_INFO('WBLReplicEnable'),WBLReplicEnable) then WBLReplicEnable:=0;
if not TryStrToInt(SYS.GET_INFO('WBLReplicaHamEnable'),WBLReplicaHamEnable) then WBLReplicaHamEnable:=0;
if FileExists('/var/log/artica-postfix/sa-learn-internal.debug') then logs.DeleteFile('/var/log/artica-postfix/sa-learn-internal.debug');
if FileExists('/var/log/artica-postfix/sa-learn-external.debug') then logs.DeleteFile('/var/log/artica-postfix/sa-learn-external.debug');
nice:=SYS.EXEC_NICE();

   f.AddStrings(ldap.SpamAssassinAutoLearnUsers());
   for i:=0 to f.Count-1 do begin
       logs.Debuglogs('Parsing user ' + f.Strings[i]);
       userd:=ldap.Load_userasdatas(f.Strings[i]);
       l.Add('[CONF]');
       l.Add('ImapServer=127.0.0.1');
       l.Add('username='+f.Strings[i]);
       l.Add('password='+userd.userPassword);
       l.Add('enable=1');
       l.Add('enable_spam=' +IntToStr(WBLReplicEnable));
       l.Add('enable_ham=' +IntToStr(WBLReplicaHamEnable));
       l.Add('razorhome=' +razorhome);
       l.Add('razorconf=' +razorconf);
       l.Add('UseSSL=0');
       ftmp:=logs.FILE_TEMP();
       try
          l.SaveToFile(ftmp);
          cmd:=nice+'/usr/share/artica-postfix/bin/imap-learn.pl --path '+ftmp + ' >>/var/log/artica-postfix/sa-learn-internal.debug';
          logs.Debuglogs(cmd);
          fpsystem(cmd);
          sleep(1000);
          CleanCacheFile();
          l.Clear;
          
       except
          continue;
       end;
   end;
   
logs.EVENTS(IntToStr(f.Count)+' Internal mailboxes parsed','','sa-learn','');
f.Clear;
logs.Debuglogs('Parsing fetchmail accounts');
f.AddStrings(UNDERSTAND_fetchmailrc());
RegExpr:=TRegExpr.Create;
for i:=0 to f.Count-1 do begin
    l.Clear;
    if length(trim(f.Strings[i]))=0 then continue;
    l.Add('[CONF]');
    RegExpr.Expression:='server=(.+?);';
    if RegExpr.Exec(f.Strings[i]) then begin
       l.Add('ImapServer='+RegExpr.Match[1]);
       logs.Debuglogs('Parsing fetchmail '+RegExpr.Match[1]);
    end;

    RegExpr.Expression:='proto=(.+?);';
    if RegExpr.Exec(f.Strings[i]) then begin
       if  RegExpr.Match[1]<>'imap' then continue;
    end;
    
    RegExpr.Expression:='user=(.+?);';
    if RegExpr.Exec(f.Strings[i]) then l.Add('username='+RegExpr.Match[1]);

    RegExpr.Expression:='pass=(.+?);';
    if RegExpr.Exec(f.Strings[i]) then l.Add('password='+RegExpr.Match[1]);

    RegExpr.Expression:='ssl=([0-9]);';
    if RegExpr.Exec(f.Strings[i]) then l.Add('UseSSL='+RegExpr.Match[1]);

    
    RegExpr.Expression:='mail=(.+?);';
    if RegExpr.Exec(f.Strings[i]) then begin
       userd:=ldap.UserDataFromMail(RegExpr.Match[1]);
       logs.Debuglogs('Parsing fetchmail accounts for '+userd.uid + ' enable='+userd.EnableUserSpamLearning);
       l.Add('enable='+userd.EnableUserSpamLearning);
    end;
    l.Add('enable_spam='+ IntToStr(WBLReplicEnable));
    l.Add('enable_ham='+ IntToStr(WBLReplicaHamEnable));
    l.Add('razorhome=' +razorhome);
    l.Add('razorconf=' +razorconf);
    ftmp:=logs.FILE_TEMP();
    try
          l.SaveToFile(ftmp);
          cmd:=nice+'/usr/share/artica-postfix/bin/imap-learn.pl --path '+ftmp + ' >>/var/log/artica-postfix/sa-learn-external.debug';
          logs.Debuglogs(cmd);
          fpsystem(cmd);
          sleep(1000);
          CleanCacheFile();
          logs.DeleteFile(ftmp);
          l.Clear;

       except
          continue;
       end;

end;


fpsystem(nice+sys.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.import-white-lists.php');
SpamAssassin_whitelistBlacklist();
logs.EVENTS(IntToStr(f.Count)+' external mailboxes parsed','','sa-learn','');

   
end;
//#############################################################################
procedure tldaplearn.CleanCacheFile();
var
   l:TstringList;
   ll:TstringList;
   i:Integer;
begin

   if not FileExists('/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes') then exit;
   l:=TstringList.Create;
   ll:=TstringList.Create;
   l.LoadFromFile('/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes');
   for i:=0 to l.COunt-1 do begin
        if length(trim(l.Strings[i]))=0 then continue;
            ll.Add(l.Strings[i]);

   end;

   try
      ll.SaveToFile('/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes');
   except
   logs.Syslogs('FATAL Error while saving /etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes');
   exit;
   end;

   ll.free;
   l.free;


end;
 //#############################################################################



FUNCTION tldaplearn.UNDERSTAND_fetchmailrc():TstringList;
var
   RegExpr                :TRegExpr;
   l                      :TstringList;
   s                      :TstringList;
   i                      :integer;
   server                 :string;
   pass                   :string;
   protocol               :string;
   user                   :string;
   uid                    :string;
   foundpoll              :boolean;
   keep                   :string;
   t                      :integer;
begin
s:=TstringList.Create();


if not FileExists('/etc/fetchmailrc') then begin
   logs.Debuglogs('UNDERSTAND_fetchmailrc:: unable to stat /etc/fetchmailrc');
   exit;
end;
t:=0;
l:=TstringList.Create;
l.LoadFromFile('/etc/fetchmailrc');
foundpoll:=false;
RegExpr:=TRegExpr.Create;
      for i:=0 to l.Count-1 do begin
          RegExpr.Expression:='(poll\s+|server|skip|defaults)';
          if RegExpr.Exec(l.Strings[i]) then begin
             s.Add('');
             t:=s.Count-1;
          end;
          RegExpr.Expression:='poll\s+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'server='+RegExpr.Match[1]+';';
          RegExpr.Expression:='proto[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'protocol='+Lowercase(RegExpr.Match[1])+';';
          RegExpr.Expression:='user[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'user='+RegExpr.Match[1]+';';
          RegExpr.Expression:='pass[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'pass='+RegExpr.Match[1]+';';
          RegExpr.Expression:='is[\s|"]+([A-ZA-z0-9\.\-_@\$\&]+)';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'mail='+RegExpr.Match[1]+';';
          RegExpr.Expression:='ssl';
          if RegExpr.Exec(l.Strings[i]) then s.Strings[t]:=s.Strings[t]+'ssl=1;';
      end;
result:=s;
end;



//#############################################################################
PROCEDURE tldaplearn.SpamAssassin_whitelistBlacklist();
var
   l:Tstringlist;
   cf:tstringList;
   kas3_white:Tstringlist;
   kas3_black:TstringList;
   RegExpr:TRegExpr;
   i,t:integer;
   emails:TStringDynArray;
   firstpart:string;
   lastpart:string;
   RegExpr2:TRegExpr;
   spamass:Tspamass;
   spammass_conf_path:string;
   spammassDirectory:string;
begin

 if ldap.Logged=false then begin
    logs.Syslogs('SpamAssassin_whitelistBlacklist:: Logged is false;aborting...');
    exit;
 end;
   spamass:=Tspamass.Create(SYS);
   spammass_conf_path:=spamass.SPAMASSASSIN_LOCAL_CF();
   spammassDirectory:=ExtractFilePath(spammass_conf_path);
   
   kas3_white:=TStringList.Create;
   kas3_white.Add('<?xml version="1.0" encoding="utf-8"?>');
   kas3_white.Add('#include <base/common-allow.xml.macro>');
   kas3_white.Add('BEGIN_COMMON_ALLOW_EMAIL_LIST');
   
   
   kas3_black:=TStringList.Create;
   kas3_black.Add('<?xml version="1.0" encoding="utf-8"?>');
   kas3_black.Add('#include <base/common-deny.xml.macro>');
   kas3_black.Add('BEGIN_COMMON_DENY_EMAIL_LIST');


   l:=Tstringlist.Create;
   l.AddStrings(ldap.BlackListedList());
   logs.Debuglogs('SpamAssassin_whitelistBlacklist::' +IntToSTr(l.Count) + ' Entries');
   RegExpr:=TRegExpr.Create;
   RegExpr2:=TRegExpr.Create;
   
   RegExpr.Expression:='^(W|B)(.+?)=(.+)';
   cf:=TstringList.Create;
   For i:=0 to l.Count-1 do begin
        if RegExpr.Exec(l.Strings[i]) then begin
           logs.Debuglogs('SpamAssassin_whitelistBlacklist:: match '+l.Strings[i]);
               emails:=GLOBAL_INI.Explode(';',RegExpr.Match[3]);
               cf.Add('# rcpt ' + RegExpr.Match[2]);
               for t:=0 to length(emails)-1 do begin
                   RegExpr2.Expression:='(.*)@(.*)';
                   RegExpr2.Exec(emails[t]);
                   firstpart:=RegExpr2.Match[1];
                   lastpart:=RegExpr2.Match[2];
                   if length(firstpart)=0 then firstpart:='*';
                   if length(lastpart)=0 then lastpart:='*';
                   if RegExpr.Match[1]='W' then begin
                      cf.Add('whitelist_from'+chr(9)+firstpart+'@'+lastpart);
                      kas3_white.Add('EMAIL_ENTRY("'+firstpart+'@'+lastpart+'")');
                   end;
                      
                      
                   if RegExpr.Match[1]='B' then begin
                      cf.Add('blacklist_from'+chr(9)+firstpart+'@'+lastpart);
                      kas3_black.Add('EMAIL_ENTRY("'+firstpart+'@'+lastpart+'")');
                   end;
               end;
        end;
   end;

 if FileExists(spamass.SPAMASSASSIN_BIN_PATH()) then begin
  try
    ForceDirectories(spammassDirectory);
    cf.SaveToFile(spammassDirectory+ 'wbl.cf');
    except
      logs.Syslogs('SpamAssassin_whitelistBlacklist():: FATAL ERROR WHITE SAVING '+spammassDirectory+ 'wbl.cf');
      cf.free;
      exit;
     end;
     logs.Syslogs('SpamAssassin_whitelistBlacklist():: include '+spammassDirectory+ 'wbl.cf');
     spamass.SPAMASSASSIN_ADD_INCLUDE_FILE(spammassDirectory+ 'wbl.cf');
     spamass.SPAMASSASSIN_RELOAD();
     spamass.Free;
 end else begin
   logs.Debuglogs('SpamAssassin_whitelistBlacklist():: spamassassin is not installed, skip it');
 end;
 
kas3_white.Add('END_COMMON_ALLOW_EMAIL_LIST');
kas3_black.Add('END_COMMON_DENY_EMAIL_LIST');

if FileExists('/usr/local/ap-mailfilter3/bin/mkprofiles') then begin
   if FileExists('/usr/local/ap-mailfilter3/conf/def/common/common-deny.xml') then kas3_black.SaveToFile('/usr/local/ap-mailfilter3/conf/def/common/common-deny.xml');
   if FileExists('/usr/local/ap-mailfilter3/conf/def/common/common-allow.xml') then kas3_white.SaveToFile('/usr/local/ap-mailfilter3/conf/def/common/common-allow.xml');
   fpsystem('/usr/local/ap-mailfilter3/bin/mkprofiles >/dev/null 2>&1');
end;

kas3_black.Free;
kas3_white.Free;
cf.free;

end;
//#############################################################################


end.

