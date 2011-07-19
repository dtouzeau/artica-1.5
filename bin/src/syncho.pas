unit syncho;

{$mode objfpc}{$H+}

interface

uses
Classes, SysUtils,variants, Process,Linux,BaseUnix,IniFiles,oldlinux,strutils,md5,logs,common,
RegExpr in 'RegExpr.pas',
confiles in 'confiles.pas',
global_conf in 'global_conf.pas',kav_mail;

  type
  Tsync=class


private
     GLOBAL_INI:myconf;
     LOGS:Tlogs;
     COMMON:Tcommon;
     Enable_echo:boolean;

     procedure VerifyPermissions();
     procedure ShellExec(cmd:string);
     function MD5FromFile(path:string):string;
     Debug:boolean;
public


      constructor Create;
      procedure Free;
      procedure StartOperations();
      procedure SetLocalEcho();



END;

implementation

constructor Tsync.Create;

begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=myconf.Create();
       LOGS:=Tlogs.Create;
       LOGS.Debug:=Debug;
       LOGS.Enable_echo:=Enable_echo;
       COMMON:=Tcommon.Create;
       COMMON.debug:=Debug;
end;
procedure Tsync.Free;
begin
  COMMON.Free;
  GLOBAL_INI.free;
  LOGS.Free;
end;

procedure TSync.StartOperations();
var kav:TkavMail;
begin

    if FileExists('/etc/init.d/aveserver') then begin
        kav:= TkavMail.Create;
        kav.aveserver;
        kav.smtpscanner_logs();
        kav.free;
        end;
        
        
        

    VerifyPermissions();

end;
procedure TSync.SetLocalEcho();
begin
   Debug:=True;
   Enable_echo:=True;
   LOGS.Enable_echo:=Enable_echo;
   LOGS.Debug:=Debug;
end;

//#####################################################################################
procedure Tsync.VerifyPermissions();
var    ressources_path:string;
begin
    ressources_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
    ShellExec('/bin/chown -R'  + GLOBAL_INI.get_www_userGroup() +' ' +  ressources_path + '/ressources/conf');

    
end;
//#####################################################################################

procedure Tsync.ShellExec(cmd:string);
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
    if Debug=true then LOGS.logs('Tsync.ShellExec -> ' + cmd);
    Xresponse:=COMMON.ReadFileIntoString('/tmp/tmp.txt');
    if length(Xresponse)>0 then LOGS.logs(Xresponse);
 end;
end;
//#####################################################################################


function Tsync.MD5FromFile(path:string):string;
var StACrypt,StCrypt:String;
Digest:TMD5Digest;
begin
Digest:=MD5File(path);
exit(MD5Print(Digest));
end;

end.
