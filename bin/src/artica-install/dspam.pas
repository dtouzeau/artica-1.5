unit dspam;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils, Process,logs,unix,
    RegExpr in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/RegExpr.pas',
    zsystem in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/zsystem.pas';



  type
  tdspam=class


private
     LOGS:Tlogs;
     artica_path:string;
     SYS:Tsystem;



public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  VERSION():string;
    function  BIN_PATH():string;
    procedure DSPAM_EDIT_PARAM_MULTI(key:string;value:string);
    function  DSPAM_IS_PARAM_EXISTS(key:string;value:string):boolean;
    procedure DSPAM_REMOVE_PARAM(key:string);
    function  DSPAM_GET_PARAM(key:string):string;
    procedure DSPAM_EDIT_PARAM(key:string;value:string);
    procedure SET_CONFIG();
END;

implementation

constructor tdspam.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;



       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tdspam.free();
begin
    logs.Free;
end;
//##############################################################################
function tdspam.BIN_PATH():string;
begin
exit(SYS.LOCATE_DSPAM());
end;
//#############################################################################
procedure tdspam.DSPAM_EDIT_PARAM_MULTI(key:string;value:string);
var

   s:string;
   l:TstringList;
begin
s:='';
if not FileExists('/etc/dspam/dspam.conf') then exit;
if DSPAM_IS_PARAM_EXISTS(key,value) then exit;
l:=TstringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');



   if length(s)=0 then begin
        logs.Debuglogs('DSPAM_EDIT_PARAM:: Add the value "'+value+'"');
        l.Add(key + ' ' + value);
        l.SaveToFile('/etc/dspam/dspam.conf');
        l.free;
        exit();
   end;

 l.Free;

end;

//##############################################################################
procedure tdspam.DSPAM_REMOVE_PARAM(key:string);
var
   i:integer;
   RegExpr:TRegExpr;
   l:TstringList;
begin
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TstringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');


RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       l.Delete(i);
       l.SaveToFile('/etc/dspam/dspam.conf');
       RegExpr.Free;
       l.Free;
       DSPAM_REMOVE_PARAM(key);
       exit;
   end;
end;


 l.SaveToFile('/etc/dspam/dspam.conf');
 RegExpr.Free;
 l.Free;

end;

//##############################################################################
function tdspam.DSPAM_GET_PARAM(key:string):string;
var
   i:integer;
   RegExpr:TRegExpr;
   l:TStringList;

begin
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TStringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       result:=trim(RegExpr.Match[1]);
       break;
   end;

end;
 RegExpr.Free;
 l.Free;


end;
//##############################################################################
function tdspam.DSPAM_IS_PARAM_EXISTS(key:string;value:string):boolean;
var
   i:integer;
   RegExpr:TRegExpr;
   l:TStringList;

begin
result:=false;
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TStringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       if value=RegExpr.Match[1] then begin
          result:=true;
          break;
       end;
   end;

end;
 RegExpr.Free;
 l.Free;


end;
//##############################################################################
procedure tdspam.SET_CONFIG();
var
l:Tstringlist;
begin
forceDirectories('/var/log/dspam');
fpsystem('/bin/chown -R postfix:postfix /var/log/dspam');
l:=TstringList.Create;
l.Add('Home /var/amavis/dspam');
l.Add('StorageDriver /usr/local/lib/libmysql_drv.so');
l.Add('TrustedDeliveryAgent "no"');
l.Add('OnFail error');
l.Add('Trust root');
l.Add('Trust mail');
l.Add('Trust mailnull ');
l.Add('Trust smmsp');
l.Add('Trust daemon');
l.Add('TrainingMode teft');
l.Add('TestConditionalTraining on');
l.Add('Feature whitelist');
l.Add('Algorithm graham burton');
l.Add('Tokenizer chain');
l.Add('PValue bcr');
l.Add('WebStats on');
l.Add('Preference "spamAction=quarantine"');
l.Add('Preference "showFactors=on"');
l.Add('AllowOverride trainingMode');
l.Add('AllowOverride spamAction spamSubject');
l.Add('AllowOverride statisticalSedation');
l.Add('AllowOverride enableBNR');
l.Add('AllowOverride enableWhitelist');
l.Add('AllowOverride signatureLocation');
l.Add('AllowOverride showFactors');
l.Add('AllowOverride optIn optOut');
l.Add('AllowOverride whitelistThreshold');
l.Add('HashRecMax		98317');
l.Add('HashAutoExtend		on  ');
l.Add('HashMaxExtents		0');
l.Add('HashExtentSize		49157');
l.Add('HashPctIncrease 10');
l.Add('HashMaxSeek		10');
l.Add('HashConnectionCache	10');
l.Add('Notifications	off');
l.Add('LocalMX 127.0.0.1');
l.Add('SystemLog on');
l.Add('UserLog   on');
l.Add('Opt out');
l.Add('ProcessorURLContext on');
l.Add('ProcessorBias on');
l.Add('MySQLServer '+SYS.MYSQL_INFOS('mysql_server'));
l.Add('MySQLPort '+SYS.MYSQL_INFOS('port'));
l.Add('MySQLUser '+SYS.MYSQL_INFOS('database_admin'));
l.Add('MySQLPass '+SYS.MYSQL_INFOS('database_password'));
l.Add('MySQLDb      artica_events');
l.Add('MySQLCompress  true');
l.Add('MySQLConnectionCache        10');
l.Add('#MySQLVirtualTable          dspam_virtual_uids');
l.add('#MySQLVirtualUIDField       uid');
l.Add('#MySQLVirtualUsernameField  username');
l.Add('MySQLUIDInSignature    on');
l.add('Notifications        on');
l.add('ParseToHeaders on');
l.Add('ChangeModeOnParse on');
l.add('ChangeUserOnParse full');
l.add('Preference "showFactors=on"');
l.add('Preference "spamAction=tag"');
l.add('Preference "spamSubject=[SPAM]"');
forceDirectories('/usr/local/etc/dspam');
l.SaveToFile('/usr/local/etc/dspam/dspam.conf');
l.free;
end;


procedure tdspam.DSPAM_EDIT_PARAM(key:string;value:string);
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;
   l:TstringList;
begin
if not FileExists('/etc/dspam/dspam.conf') then exit;
l:=TstringList.Create;
l.LoadFromFile('/etc/dspam/dspam.conf');
s:=DSPAM_GET_PARAM(key);
   if length(s)=0 then begin
        logs.Debuglogs('DSPAM_EDIT_PARAM:: Add the value "'+value+'"');
        l.Add(key + ' ' + value);
        l.SaveToFile('/etc/dspam/dspam.conf');
        l.free;
        exit();
   end;

RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^' + key + '\s+(.+)';
for i:=0 to l.Count -1 do begin
   if RegExpr.Exec(l.Strings[i]) then begin
       l.Strings[i]:=key + ' ' + value;
       l.SaveToFile('/etc/dspam/dspam.conf');
       break;
   end;
end;
 RegExpr.Free;
 l.Free;

end;

//##############################################################################
function tdspam.VERSION():string;
var

   F:TstringList;
   t:string;
   i:integer;
   RegExpr:TRegExpr;
begin
   t:=logs.FILE_TEMP();
   if not FileExists(BIN_PATH()) then exit();
   fpsystem(BIN_PATH() + ' --version >' + t + ' 2>&1');
   if not FileExists(t) then exit;

   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='DSPAM Anti-Spam Suite\s+([0-9\.]+)';

   F:=TstringList.Create;
   F.LoadFromFile(t);
   for i:=0 to F.Count-1 do begin
       if RegExpr.Exec(F.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;
   end;
   logs.DeleteFile(t);
   RegExpr.free;
   F.Free;
end;
//#############################################################################

end.

