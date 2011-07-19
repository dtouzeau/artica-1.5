unit mysql_class;
     {$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,IniFiles,logs,RegExpr in 'RegExpr.pas',ldap;
type
TStringDynArray = array of string;

  type
  MySqlop=class

private
       LOGS:Tlogs;
       function ArticaFilterQueuePath():string;
       function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
       DirListFiles:TstringList;
       QUEUE_PATH:string;
       D:boolean;
       procedure DeleteFile(path:string);
       CONNECTED:boolean;
public
      constructor Create;
      destructor Free();
      function STATUS():boolean;

END;

implementation

const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;

constructor MySqlop.Create;
begin
     D:=COMMANDLINE_PARAMETERS('debug');
     if not D then D:=COMMANDLINE_PARAMETERS('-V');
     forcedirectories('/etc/artica-postfix');
     DirListFiles:=TstringList.Create;
     QUEUE_PATH:=ArticaFilterQueuePath();
     LOGS:=TLogs.Create;
     if D then writeln('Initialize....');
end;

destructor MySqlop.Free();
begin
    DirListFiles.Free;
end;
//#########################################################################################
function MySqlop.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:=false;
 if ParamCount>0 then begin
     for i:=0 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:=FoundWhatPattern;
   if RegExpr.Exec(s) then begin
      RegExpr.Free;
      result:=True;
   end;


end;
//#########################################################################################
function MySqlop.STATUS():boolean;
var
ldap:Tldap;
ous:TStringDynArray;
i:Integer;
begin

  ldap:=Tldap.Create;
   ous:=ldap.OuLists();
   writeln();
   writeln('Status of artica-quarantine: ');
   writeln('----------------------------------------------------');
   writeln('Number of Organizations: ', length(ous));

   for i:=0 to length(ous)-1 do begin
          writeln(ous[i] + ':');
   end;



   writeln();
   writeln();


end;



function Mysqlop.





//##############################################################################
function MySqlop.ArticaFilterQueuePath():string;
var ini:TIniFile;
begin
 ini:=TIniFile.Create('/etc/artica-postfix/artica-filter.conf');
 result:=ini.ReadString('INFOS','QueuePath','');
 if length(trim(result))=0 then result:='/usr/share/artica-filter';
end;
//##############################################################################
procedure MySqlop.DeleteFile(path:string);
Var F : Text;

begin
  { Create a file with a line of text in it}
  TRY
     if D then writeln('Delete ' + path);
     Assign (F,path);
     Erase (f);
  except
   writeln('unable to delete ' + path);
  
  end;
end;


end.

