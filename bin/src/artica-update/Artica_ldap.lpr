program Artica_ldap;

{$mode objfpc}{$H+}

uses
  cthreads,Classes,logs,unix,BaseUnix,SysUtils,RegExpr,ldap,global_conf,ldapconf;

var
GLOBAL_INI:myconf;
tempfile:TstringList;
s,y:string;
xldap:Tldap;
i:Integer;
D:boolean;
fetchmail               :fetchmail_settings;
inadyn                  :inadyn_settings;
proxy                   :http_proxy_settings;
XSETS                   :tldapconf;
zlogs                    :Tlogs;


//##############################################################################

begin


  GLOBAL_INI:=myconf.Create;
  XSETS:=tldapconf.Create();
  D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
  s:='';
  
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
 zlogs:=Tlogs.Create;

 
  xldap:=Tldap.Create;
  
  if ParamStr(1)='-fetchmail' then begin

      if D then writeln('Load fetchmail settings...');
      fetchmail:=xldap.Load_Fetchmail_settings();
      GLOBAL_INI.FETCHMAIL_APPLY_CONF(fetchmail.fetchmailrc);
      halt(0);
  end;
  
  if ParamStr(1)='-getlive' then begin
      if D then writeln('Load fetchmail settings...');
      fetchmail:=xldap.Load_Fetchmail_settings();
      GLOBAL_INI.FETCHMAIL_APPLY_GETLIVE(fetchmail.FetchGetLive);
      halt(0);
  end;
  
  if ParamStr(1)='-cyrus-restore' then begin
         xldap.DeleteCyrusUser();
         xldap.CreateCyrusUser();
         halt(0);
  end;
  if ParamStr(1)='-inadyn' then begin
         GLOBAL_INI.INADYN_PERFORM_STOP();
         inadyn:=xldap.Load_inadyn_settings();
         if inadyn.ArticaInadynRule.Count >0 then begin
            if D then writeln('inadyn.ArticaInadynRule.Count>0');
            if StrToInt(inadyn.ArticaInadynPoolRule)>0 then begin
               for i:=0 to inadyn.ArticaInadynRule.Count -1 do begin
                   GLOBAL_INI.INADYN_PERFORM(inadyn.ArticaInadynRule.Strings[i]+inadyn.proxy_settings.IniSettings,StrToInt(inadyn.ArticaInadynPoolRule));
               end;
            end;
         end;
         halt(0);
  end;
  
  if ParamStr(1)='-kav4proxy' then begin
         y:=xldap.Load_Kav4proxy_settings();
         if length(y)>0 then begin
            tempfile:=TStringList.Create;
            tempfile.Add(xldap.Load_Kav4proxy_settings());
            tempfile.SaveToFile('/etc/opt/kaspersky/kav4proxy.conf');
            GLOBAL_INI.KAV4PROXY_STOP();
            GLOBAL_INI.KAV4PROXY_START();
            halt(0);
         end;
  end;
  
  
  
//------------------------------------------------------------------------------
  if ParamStr(1)='-proxy' then begin
      proxy:=xldap.Load_proxy_settings();
      writeln('Enabled.............:' + proxy.ArticaProxyServerEnabled);
      writeln('Server..............:' + proxy.ArticaProxyServerName + ':' +proxy.ArticaProxyServerPort);
      writeln('Username............:' + proxy.ArticaProxyServerUsername + ':' + proxy.ArticaProxyServerUserPassword);
      halt(0);
  
  end;
//------------------------------------------------------------------------------
 if ParamStr(1)='-squid' then begin
      if D then writeln('Load squid settings...');
      XSETS.squid(ParamStr(2));
      halt(0);
  end;
  
//------------------------------------------------------------------------------
   if ParamStr(1)='-dansguardian' then begin
      if length(ParamStr(2))=0 then begin
         writeln('no servername specified');
         halt(0);
         end;
      
      if D then writeln('Load dansguardian settings...');
      XSETS.Dansguardian(ParamStr(2));
      halt(0);
  end;
//------------------------------------------------------------------------------
   if ParamStr(1)='-secu-level' then begin
       XSETS.ApplySecuLevel();
       halt(0);
   end;
 
//------------------------------------------------------------------------------
   if ParamStr(1)='-ftp-users' then begin
       XSETS.FtpUsers();
       halt(0);
   end;
//------------------------------------------------------------------------------
   if ParamStr(1)='-sqlgrey' then begin
      if length(ParamStr(2))=0 then begin
         writeln('no servername specified');
         halt(0);
         end;

      if D then writeln('Load sqlgrey settings...');
      XSETS.sqlgrey(ParamStr(2));
      halt(0);
  end;
//------------------------------------------------------------------------------
   if ParamStr(1)='-maincf' then begin
       XSETS.maincf();
       halt(0);
   end;
//------------------------------------------------------------------------------

   if ParamStr(1)='--maintenance' then begin
       XSETS.FoldersSizeConfig();
       XSETS.maintenance();
       halt(0);
   end;
   
   if ParamStr(1)='-amavis' then begin
       XSETS.Amavis();
       halt(0);
   end;

   if ParamStr(1)='-crossroads' then begin
       if ParamStr(2)='sync' then begin
          zlogs.logs('Synchronize slaves servers for crossroads');
          XSETS.crossroads_sync();
          halt(0);
       end;
       
       if ParamStr(2)='apply' then begin
          XSETS.crossroads_apply(ParamStr(3));
          halt(0);
       end;
       
   end;
 
 
 if length(ParamStr(1))>0 then begin
 
     writeln('usage....................................');
     writeln('-cyrus-restore............: re-create ldap cyrus user admin');
     writeln('-fetchmail................: Save fetchmail config from LDAP to disk');
     writeln('-getlive..................: perform GetLive fetching');
     writeln('-inadyn...................: perform inadyn synchronisation..');
     writeln('-proxy....................: Get proxy informations');
     writeln('-kav4proxy................: perform Kaspersky for squid config from ldap');
     writeln('-squid servername.........: perform squid config from ldap + give the name of this server stored in ldap');
     writeln('-dansguardian servername..: Apply DansGuardian settings from ldap to disk...');
     writeln('-secu-level...............: Apply modules enabled setting from ldap to disk...');
     writeln('-ftp-users servername.....: Apply FTP users settings from ldap to disk...');
     writeln('-sqlgrey servername.......: Apply sqlgrey settings from ldap to disk...');
     writeln('-maincf...................: Apply Posfix Main.cf to disk...');
     writeln('-amavis...................: Apply amavis settings to disk...');
     writeln('-crossroads sync..........: Send syncronize orders to crossroads slaves ');
     writeln('');
     writeln('use --verbose/debug after commands lines to see more process infos');
     halt(0);
 end;
   zlogs.logs('recieve ' + s);
  xldap.CreateSuffix();
  xldap.CreateArticaUser();
  xldap.CreateCyrusUser();
  xldap.CreateMailManBranch();
  halt(0);
end.
