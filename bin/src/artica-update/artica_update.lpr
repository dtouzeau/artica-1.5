program artica_update;

{$mode objfpc}{$H+}

uses
  Classes,logs,unix,BaseUnix,strutils,SysUtils,RegExpr,update,zsystem,kav4proxy,
  kavmilter in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kavmilter.pas',
  kas3 in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/kas3.pas';

var
tempfile:TstringList;
s:string;
EnableScheduleUpdates   :integer;
XSETS                   :tupdate;
zlogs                   :Tlogs;
D                       :boolean;
i                       :integer;
SYS                     :Tsystem;
mypid                   :string;
zkas3                   :tkas3;
zkavmilter              :tkavmilter;
zkav4proxy              :tkav4proxy;


//##############################################################################

begin

  XSETS:=tupdate.Create();
  SYS:=Tsystem.Create();
  zlogs:=Tlogs.Create;
  D:=SYS.COMMANDLINE_PARAMETERS('--verbose');

  if ParamStr(1)='-V' then begin
    writeln('Revision 01/09/2011');
     halt(0);
  end;




  if ParamStr(1)='-refresh-index' then begin
     zlogs.Debuglogs('Refresh index...');
     XSETS.CheckIndex();
     halt(0);
  end;

  if ParamStr(1)='--kav4proxy' then begin
     zlogs.Debuglogs('Update Kav4proxy');
     zkav4proxy:=Tkav4proxy.Create(SYS);
     zkav4proxy.KAV4PROXY_PERFORM_UPDATE();
     halt(0);
  end;

  if ParamStr(1)='--kav4proxy--pattern' then begin
     zkav4proxy:=Tkav4proxy.Create(SYS);
     writeln(zkav4proxy.PATTERN_DATE());
     halt(0);
  end;



 if ParamStr(1)='--index' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--index Already instance executed');
         halt(0);
    end;
    XSETS.indexini();
    halt(0);
 end;

 if ParamStr(1)='--system-id' then begin
    writeln(XSETS.CheckSYSTEMID());
    halt(0);
 end;


  if ParamStr(1)='--kas3' then begin
     zkas3:=tkas3.Create(SYS);
     zkas3.PERFORM_UPDATE();
     halt(0);
  end;

  if ParamStr(1)='--kavmilter' then begin
     zkavmilter:=tkavmilter.Create(SYS);
     zkavmilter.PERFORM_UPDATE();
     halt(0);
  end;
  

  
  if ParamStr(1)='-startinstall' then begin
     XSETS.Initialize_installation();
     halt(0);
  end;


  


 if ParamStr(1)='--retranslator' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--retranslator Already instance executed');
         halt(0);
    end;
    XSETS.KasperskyRetranslation();
    halt(0);
 end;
 
 
 if ParamStr(1)='--clamav' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--clamav Already instance executed');
         halt(0);
    end;
    XSETS.perform_clamav_updates();
    halt(0);
 end;



 if ParamStr(1)='--clamav-engine' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--clamav-engine Already instance executed');
         halt(0);
    end;
    XSETS.clamav_engine_update();
    halt(0);
 end;

 if ParamStr(1)='--spamassassin' then begin
    if not SYS.COMMANDLINE_PARAMETERS('--force') then begin
       if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--spamassassin Already instance executed');
         halt(0);
       end;
    end;
    XSETS.update_spamassasin();
    XSETS.update_spamassasin_sought_rules_yerp_org();
    XSETS.update_spamassasin_saupdates_openprotect_com();
    XSETS.update_spamassasin_scanmail();
    XSETS.update_spamassassin_blacklist();
    halt(0);
 end;

 if ParamStr(1)='--spamassassin-bl' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--spamassassin-bl Already instance executed');
         halt(0);
    end;
    XSETS.update_spamassassin_blacklist();
    halt(0);
 end;


 if ParamStr(1)='--MalwarePatrol' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--MalwarePatrol Already instance executed');
         halt(0);
    end;
    XSETS.MalwarePatrol();
    halt(0);
 end;


 if ParamStr(1)='--filter-plus' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--filter-plus Already instance executed');
         halt(0);
    end;
    XSETS.update_webfilterplus();
    halt(0);
 end;

 if ParamStr(1)='--ipblocks' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--ipblocks Already instance executed');
         halt(0);
    end;
    XSETS.ipblocks();
    halt(0);
 end;






 if ParamStr(1)='--backup-ldap' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs(ParamStr(1)+' Already instance executed');
         halt(0);
    end;
    XSETS.backup_ldap_database();
    halt(0);
 end;

 if ParamStr(1)='--dansguardian' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--dansguardian Already instance executed');
         halt(0);
    end;
    XSETS.update_dansguardian();
    XSETS.update_squidguard();
    XSETS.update_webfilterplus();
    halt(0);
 end;

 if ParamStr(1)='--squidguard' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('--squidguard Already instance executed');
         halt(0);
    end;
    XSETS.update_squidguard();
    halt(0);
 end;




 if ParamStr(1)='--upgrade-nightly' then begin
    if not SYS.BuildPids() then begin
         zlogs.Debuglogs('Already instance executed');
         halt(0);
    end;
    XSETS.NightlyBuild();
    halt(0);
 end;

if ParamStr(1)='--help' then begin
   writeln('For verbosed output, add --verbose or --debug at the end of your command line');
   writeln('');
   writeln('-refresh-index...............................: Refresh the repository index file. --force supported');
   writeln('--index......................................: Refresh the repository index file. --force supported');
   writeln('--upgrade-nightly............................: Upgrade Artica to a nightly build');
   writeln('--dansguardian...............................: Update blacklists web sites for DansGuardian');
   writeln('--MalwarePatrol..............................: Update blacklists web sites from Malware Patrol');
   writeln('--filter-plus ...............................: Update licensed blacklists web sites from Artica');
   writeln('--spamassassin-bl............................: Update & compile SA blacklists');
   writeln('--clamav-engine..............................: Update & compile ClamAV Engine');
   writeln('--ipblocks...................................: Update ipblocks');





   halt(0);
end;



  s:='';

 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
 mypid:=intTostr(fpgetpid);
 s:=trim(SYS.PROCESS_LIST_PID(ParamStr(0)));
 s:=trim(AnsiReplaceText(s,mypid,''));

if not SYS.BuildPids() then halt(0);

if not TryStrToInt(SYS.GET_INFO('EnableScheduleUpdates'),EnableScheduleUpdates) then EnableScheduleUpdates:=0;
 if EnableScheduleUpdates=1 then begin
    if not SYS.COMMANDLINE_PARAMETERS('--force') then begin
       if not SYS.COMMANDLINE_PARAMETERS('--bycron') then begin
          writeln('Artica-update is scheduled: add --force token to force die()');
          halt(0);
       end;
    end;
 end;

  if ParamStr(1)='--patchs' then begin
     XSETS.ApplyPatchs();
     zlogs.Debuglogs('Halt now....');
     halt(0);
  end;


 if D then writeln('Recieve ',s);
 XSETS.CheckAndInstall();

 
 if length(s)>0 then zlogs.Debuglogs('Receive ' + s);
 
 XSETS.perform_update();
 XSETS.ipblocks();
 XSETS.perform_update_nightly();
 
 zlogs.Debuglogs('Halt now....');
 halt(0);
end.
