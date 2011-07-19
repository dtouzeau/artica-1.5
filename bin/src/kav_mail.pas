unit kav_mail;

{$mode objfpc}{$H+}

interface

uses
Classes, SysUtils,variants, Process,IniFiles,oldlinux,md5,RegExpr in 'RegExpr.pas',logs,global_conf,common,process_infos;

  type
  TkavMail=class


private
     GLOBAL_INI:myconf;

     LOGS:Tlogs;
     COMMON:Tcommon;
     PROC:Tprocessinfos;
     function MD5FromFile(path:string):string;
     procedure ShellExec(cmd:string);

public
    constructor Create;
      Debug:boolean;
      echo_local:boolean;
    procedure aveserver();
    procedure smtpscanner_logs();
    procedure ExportLicenceInfos();
    procedure Free;
end;

implementation

//-------------------------------------------------------------------------------------------------------

//##############################################################################
constructor TkavMail.Create;

begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=myconf.Create();
       LOGS:=Tlogs.Create;
       Debug:=GLOBAL_INI.get_DEBUG_DAEMON();
       COMMON:=Tcommon.Create;
       PROC:=Tprocessinfos.Create;
end;
//##############################################################################
PROCEDURE TkavMail.Free();
begin
GLOBAL_INI.Free;
LOGS.Free;
COMMON.Free;
end;
//##############################################################################
procedure TkavMail.aveserver();
var artica_path:string;
    kav4mailservers,kav4mailservers_artica : stat;
    kav4mailservers_time:longint;
    kav4mailservers_time_artica:longint;
    kav4mailservers_md5:string;
    kav4mailservers_md5_artica,kav4mailservers_path,kav4mailservers_path_artica:string;
begin
     LOGS.Enable_echo:=echo_local;
     if not FileExists('/etc/init.d/aveserver') then exit;
     if not FileExists('/etc/kav/5.5/kav4mailservers/kav4mailservers.conf') then exit;
     if Debug=true then LOGS.logs('TkavMail.aveserver ->');
     
     kav4mailservers_path:='/etc/kav/5.5/kav4mailservers/kav4mailservers.conf';
     kav4mailservers_path_artica:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/ressources/conf/kav4mailservers.conf';
     

     if not FileExists(kav4mailservers_path_artica) then begin
        if Debug=true then LOGS.logs('TkavMail.aveserver -> cp new kav conf');
        ShellExec('/bin/cp ' + kav4mailservers_path + ' '  + kav4mailservers_path_artica);
        ShellExec('/bin/chmod 0666 '+ kav4mailservers_path_artica);
     end;
     

         kav4mailservers_md5:=MD5FromFile(kav4mailservers_path);
         kav4mailservers_md5_artica:= MD5FromFile(kav4mailservers_path_artica);
         if Debug=true then LOGS.logs('TkavMail.ReplicatePolicy -> kav=MD5(' + kav4mailservers_md5 + ') kav in artica=MD5(' + kav4mailservers_md5_artica + ')');
         if kav4mailservers_md5_artica=kav4mailservers_md5 then exit;
         
         fstat(kav4mailservers_path,kav4mailservers);
         fstat(kav4mailservers_path_artica,kav4mailservers_artica);
         kav4mailservers_time:=kav4mailservers.mtime;
         kav4mailservers_time_artica:=kav4mailservers_artica.mtime;
         
         if kav4mailservers_time>kav4mailservers_time_artica then begin
            if Debug=true then LOGS.logs('TkavMail.ReplicatePolicy -> ' + kav4mailservers_path + ' -> ' + kav4mailservers_path_artica);
            ShellExec('/bin/cp ' + kav4mailservers_path + ' ' + kav4mailservers_path_artica);
         
         end;
         
        if kav4mailservers_time_artica>kav4mailservers_time then begin
            if Debug=true then LOGS.logs('TkavMail.ReplicatePolicy -> ' + kav4mailservers_path_artica + ' -> ' + kav4mailservers_path);
            ShellExec('/bin/cp '+ kav4mailservers_path_artica + ' ' + kav4mailservers_path);
            ShellExec('/etc/init.d/aveserver reload');
        end;
        
         if GLOBAL_INI.KAV_LAST_REPLIC_TIME()>=GLOBAL_INI.KAV_REPLICATION_MINUTES() then begin
            GLOBAL_INI.KAV_RESET_REPLIC_TIME();
            ExportLicenceInfos();
         end;
         
end;
//##############################################################################
function TkavMail.MD5FromFile(path:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5File(path);
exit(MD5Print(Digest));
end;
//##############################################################################
procedure TkavMail.ShellExec(cmd:string);
var
   Xresponse:string;
   RegExpr:TRegExpr;
begin
 if debug=true then LOGS.logs(cmd);
 RegExpr:=TRegExpr.Create();
 RegExpr.expression:='>[a-zA-Z0-9\.\/]+';
 if not RegExpr.Exec(cmd) then cmd:=cmd + ' >/tmp/kav.tmp.txt';
 RegExpr.Free;
 Shell(cmd);
 if debug=true then begin
    if Debug=true then LOGS.logs('TkavMail.ShellExec -> ' + cmd);
    if FileExists('/tmp/kav.tmp.txt') then Xresponse:=COMMON.ReadFileIntoString('/tmp/kav.tmp.txt');
    if length(Xresponse)>0 then LOGS.logs(Xresponse);
 end;
end;
//##############################################################################
procedure TkavMail.smtpscanner_logs();
    var log_path,ressources_path:string;
begin
log_path:=GLOBAL_INI.get_kaspersky_mailserver_smtpscanner_logs_path();
ressources_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
If not FileExists(log_path) then begin
    if Debug=true then LOGS.logs('TkavMail.smtpscanner_logs -> Error unable to stat ' + log_path);
    exit;
end;

if Debug=true then LOGS.logs('TkavMail.smtpscanner_logs -> ' + log_path);
ShellExec('/usr/bin/tail '+ log_path +' -n 100 >' + ressources_path + '/ressources/logs/smtpscanner.log');
chmod(ressources_path + '/ressources/logs/smtpscanner.log',0655);
end;
//##############################################################################
procedure TKavMail.ExportLicenceInfos();
var licensemanager,artica_path,datas:string;
   RegExpr:TRegExpr;
begin
     LOGS.Enable_echo:=echo_local;
    if not FileExists('/etc/init.d/aveserver') then exit;
    licensemanager:='/opt/kav/5.5/kav4mailservers/bin/licensemanager';
    if Debug=true then LOGS.logs('TkavMail.ExportLicenceInfos -> ' + licensemanager);
    artica_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
    if not FileExists(licensemanager) then exit;
    ShellExec(licensemanager + ' -s >' + artica_path + '/ressources/conf/kav.key.txt');
    ShellExec('/bin/chown ' + GLOBAL_INI.get_www_userGroup() + ' ' + artica_path + '/ressources/conf/kav.key.txt');
    datas:=PROC.ExecPipe2('/opt/kav/5.5/kav4mailservers/bin/aveserver -v',false);
    RegExpr:=TRegExpr.Create();
    RegExpr.expression:='([0-9\.]+).+RELEASE.+build.+#([0-9]+)';

    if RegExpr.Exec(datas) then begin
       if Debug=true then LOGS.logs('TkavMail.ExportLicenceInfos -> ' + RegExpr.Match[1] + ' build ' + RegExpr.Match[2]);
        GLOBAL_INI.set_INFOS('kaspersky_version',RegExpr.Match[1] + ' build ' + RegExpr.Match[2]);
     end;
     
     if not RegExpr.Exec(datas) then begin
         if Debug=true then LOGS.logs('TkavMail.ExportLicenceInfos -> unable to catch version');
    end;
     RegExpr.Free;

end;
//##############################################################################
end.
