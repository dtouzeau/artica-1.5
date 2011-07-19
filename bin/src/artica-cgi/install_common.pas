unit install_common;

{$mode objfpc}{$H+}
interface

uses
Classes, SysUtils,variants, Process,Linux,BaseUnix,IniFiles,oldlinux,strutils,md5,logs,RegExpr,confiles,global_conf;

  type
  Tcommon=class


private
     GLOBAL_INI:MyConf;
     LOGS:Tlogs;

public
    procedure Free;
    constructor Create;
    function ls(folder:string):string;
    function lsqueue(folder:string):string;
    function ReadFileIntoString(path:string):string;
    procedure killfile(path:string);
    function ExecPipe(commandline:string;ShowOut:boolean):string;
    debug:boolean;
    Enable_echo:boolean;
    function BuildArtica():string;
    function mail_log_path():string;
    function ScreenoutputFile(filepath:string):string;

END;

implementation

constructor Tcommon.Create;

begin
       forcedirectories('/etc/artica-postfix');
       GLOBAL_INI:=MyConf.Create();
       LOGS:=tlogs.Create();
       LOGS.Enable_echo:=Enable_echo;
       
end;

PROCEDURE Tcommon.Free();
begin
GLOBAL_INI.Free;
LOGS.Free;

end;
//##############################################################################
function Tcommon.ls(folder:string):string;
var
  SearchRec: TSearchRec;
  FileName:string;
  Count:integer;
  Location,list:string;
  l1,l2:integer;
  sRes:longint;
  nextt:boolean;
begin
   if not (folder[Length(folder)] in ['\', '/']) then folder := folder + '/';
   if Debug=True then LOGS.logs('Tcommon.ls():: -> init(' + folder + ')');

   sRes:=FindFirst(folder+'*', faAnyFile,SearchRec);
   if Debug=True then LOGS.logs('Tcommon.ls():: -> Result ' +IntToStr(sRes));

   repeat
           nextt:=false;

              if (SearchRec.Name = '.') then  nextt:=true;
              if (SearchRec.Name = '..') then  nextt:=true;
              if (trim(SearchRec.Name) = '') then  nextt:=true;
              if (SearchRec.Name[1]='.') then  nextt:=true;
              if nextt=false then begin
                 if Debug=True then LOGS.logs('thProcThread.ls():: ->' +SearchRec.Name);
                 list:=list +  '|' + folder   + SearchRec.Name + '|;';
              end;
    until (FindNext(SearchRec) <> 0);
    FindClose(SearchRec);
    exit(list);
end;
//##############################################################################
function Tcommon.lsqueue(folder:string):string;
var
  SearchRec: TSearchRec;
  FileName:string;
  Count:integer;
  Location,list:string;
  l1,l2:integer;
  sRes:longint;
  nextt:boolean;
Var F : Text;
begin
   if not (folder[Length(folder)] in ['\', '/']) then folder := folder + '/';
   if Debug=True then LOGS.logs('Tcommon.lsqueue():: -> init(' + folder + ')');

   sRes:=FindFirst(folder+'*.tmp', faAnyFile,SearchRec);
   if Debug=True then LOGS.logs('Tcommon.lsqueue():: -> Result ' +IntToStr(sRes));
   if sRes=-1 then exit;

   repeat
           nextt:=false;

              if (SearchRec.Name = '.') then  nextt:=true;
              if (SearchRec.Name = '..') then  nextt:=true;
              if (trim(SearchRec.Name) = '') then  nextt:=true;
              if (SearchRec.Name[1]='.') then  nextt:=true;
              if nextt=false then begin
                 if Debug=True then LOGS.logs('Tcommon.lsqueue():: ->' +SearchRec.Name);
                 list:=list +  '|' + folder   + SearchRec.Name + '|;';
              end;
    until (FindNext(SearchRec) <> 0);
    FindClose(SearchRec);
    exit(list);
end;
//##############################################################################
function Tcommon.ReadFileIntoString(path:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:text;
   i:integer;
   datas:string;
   datas_file:string;
begin

      if not FileExists(path) then begin
        LOGS.logs('Error:thProcThread.ReadFileIntoString -> file not found (' + path + ')');
        exit;

      end;
      TRY
     assign(Afile,path);
     reset(Afile);
     while not EOF(Afile) do
           begin
           readln(Afile,datas);
           datas_file:=datas_file + datas +CRLF;
           end;

close(Afile);
             EXCEPT
              LOGS.logs('Error:thProcThread.ReadFileIntoString -> unable to read (' + path + ')');
           end;
result:=datas_file;


end;
//##############################################################################

procedure Tcommon.killfile(path:string);
Var F : Text;
begin
  LOGS.logs('thProcThread.killfile -> Deleting (' + path + ')');
 if not FileExists(path) then begin
        LOGS.logs('Error:thProcThread.killfile -> file not found (' + path + ')');
        exit;
 end;
TRY
 Assign (F,path);
 Erase (f);
 EXCEPT
 LOGS.logs('Error:thProcThread.killfile -> unable to delete (' + path + ')');
 end;
end;
//##############################################################################

function Tcommon.ExecPipe(commandline:string;ShowOut:boolean):string;
const
  READ_BYTES = 2048;
  CR = #$0d;
  LF = #$0a;
  CRLF = CR + LF;

var
  S: TStringList;
  M: TMemoryStream;
  P: TProcess;
  n: LongInt;
  BytesRead: LongInt;
  xRes:string;

begin

  M := TMemoryStream.Create;
  BytesRead := 0;
  P := TProcess.Create(nil);
  P.CommandLine := commandline;
  P.Options := [poUsePipes];
  if ShowOut then WriteLn('-- executing ' + commandline + ' --');
  P.Execute;
  while P.Running do
  begin
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0
    then begin
      Inc(BytesRead, n);
    end
    else begin
      Sleep(100);
    end;
  end;

  repeat
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0
    then begin
      Inc(BytesRead, n);
    end;
  until n <= 0;
  if BytesRead > 0 then WriteLn;
  M.SetSize(BytesRead);
  S := TStringList.Create;
  S.LoadFromStream(M);
 if ShowOut then WriteLn('-- linecount = ', S.Count, ' --');
  for n := 0 to S.Count - 1 do
  begin
    if length(S[n])>1 then begin
      //if ShowOut  then WriteLn(IntToStr(n+1) + '.', S[n]);
      xRes:=xRes + S[n] +CRLF;
    end;
  end;
  if ShowOut  then WriteLn(xRes + '-- end --');
  S.Free;
  P.Free;
  M.Free;
end;
//##############################################################################

function Tcommon.BuildArtica():string;
var

   source_directories,Fdir,version:string;

begin
    writeln('Building artica...');


    source_directories:=GLOBAL_INI.get_ARTICA_PHP_PATH();
    writeln('Specify the source directory [' + source_directories + ']');
    readln(source_directories);
    if length(source_directories)=0 then source_directories:=GLOBAL_INI.get_ARTICA_PHP_PATH();
    if FileExists('/usr/bin/strip') then begin
        writeln('Strip files...');
        Shell('/usr/bin/strip -s ' + source_directories + '/bin/artica-install >/tmp/null');
        Shell('/usr/bin/strip -s ' + source_directories + '/bin/artica-postfix >/tmp/null');
        Shell('/usr/bin/strip -s ' + source_directories + '/bin/install-sql >/tmp/null');
        Shell('/bin/rm -rf ' + source_directories + '/bin/*.o >/tmp/null');
        Shell('/bin/rm -rf ' + source_directories + '/bin/*.ppu >/tmp/null');
        Shell('/bin/rm -rf ' + source_directories + '/ressources/backup');
        Shell('/bin/rm -rf ' + source_directories + '/ressources/userdb');
        Shell('/bin/rm -rf ' + source_directories + '/ressources/logs/*');
        Shell('/bin/rm -rf ' + source_directories + '/ressources/conf/*');
        Shell('/bin/rm -rf ' + source_directories + '/aliases');
    end;
    writeln('Specify the version ? [0.0.0]');
    readln(version);
    Shell('/bin/tar --force-local -v -p -C ' +source_directories + '/ -rf artica-postfix-' + version + '.tar . >/tmp/null');
    GLOBAL_INI.Free;
    

end;
function Tcommon.mail_log_path():string;
var filedatas,ExpressionGrep:string;
RegExpr:TRegExpr;
begin

if not FileExists('/etc/syslog.conf') then exit;
filedatas:=ReadFileIntoString('/etc/syslog.conf');
   ExpressionGrep:='mail\.=info.+?-([\/a-zA-Z_0-9\.]+)?';
   RegExpr:=TRegExpr.create;
   RegExpr.ModifierI:=True;
   RegExpr.expression:=ExpressionGrep;
   if RegExpr.Exec(filedatas) then  begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     exit;
   end;
   
   
   ExpressionGrep:='mail\.\*.+?-([\/a-zA-Z_0-9\.]+)?';
   RegExpr.expression:=ExpressionGrep;
   if RegExpr.Exec(filedatas) then   begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     exit;
   end;
   
  RegExpr.Free;
///usr/bin/rrdtool
end;

function Tcommon.ScreenoutputFile(filepath:string):string;
var
list:TstringList;
i:integer;
begin
writeln('');
writeln('...');
  if not FileExists(filepath) then exit;
  list:=TstringList.Create;
  list.LoadFromFile(filepath);
  For i:=0 to  list.Count-1 do begin
  writeln(trim(list[i]));
  end;
  list.free;

end;




end.

