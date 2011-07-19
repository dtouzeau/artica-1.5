unit ps;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, BaseUnix,md5,unix,RegExpr in 'RegExpr.pas',libc,users;

  type
  TStringDynArray = array of string;

  type
  tps=class


private

     D:boolean;
     artica_path:string;
     inif:TiniFile;
     function DirectoryListTstring(path:string):TstringList;
     function TransFormBinaryString(const Value: string): string;
     function IsBinaryString(const Value: string): Boolean;
     function ReadFileIntoString(path:string):string;
     function CreatePIDArrayInfo(pid:string):string;
     function UsernameFromID(id:string):string;
     function GroupNameFromGID(group_id:dword):string;
     function Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
     function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;

public
    procedure   Free;
    constructor Create;
    function    load_proc():boolean;


END;

implementation

constructor tps.Create;
begin
       forcedirectories('/etc/artica-postfix');


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tps.free();
begin

end;
//##############################################################################
function tps.load_proc():boolean;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   l:TstringList;
   i:Integer;
   RegExpr:TRegExpr;
   ProcessPath,PPID:string;
   pid:string;
   cmdline:string;
   org_cmdline:string;
   Execline:string;
   argToFound:string;
   found:boolean;
   D:boolean;
   Arrays:Tstringlist;
   ProcessName:string;
   splitty:TStringDynArray;
   XWRT:Tstringlist;

Begin

  if length(Paramstr(1))=0 then begin
     writeln('usage: ' + ExtractFileName(ParamStr(0)) + ' path');
     writeln('Ex: ' + ExtractFileName(ParamStr(0)) + ' /tmp/process.inc');
     halt(0);
  end;
  
  D:=COMMANDLINE_PARAMETERS('--verbose');
  cmdline:='';
  argToFound:='';
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='/proc/([0-9]+)';
  l:=TstringList.Create;
  l.AddStrings(DirectoryListTstring('/proc'));
  Arrays:=TstringList.Create;
  if D then writeln('Number of row:', l.Count);
  
  For i:=0 to l.Count-1 do begin



      if RegExpr.Exec(l.Strings[i]) then begin
      
        pid:=RegExpr.Match[1];
        
        
        if FileExists(l.Strings[i]+'/exe') then begin
           ProcessPath:=fpReadlink(l.Strings[i]+'/exe');
           org_cmdline:=trim(ReadFileIntoString(l.Strings[i]+'/cmdline'));
           if IsBinaryString(org_cmdline)  then org_cmdline:=TransFormBinaryString(org_cmdline);
           ProcessName:=ExtractFilename(ProcessPath);
        end;

        splitty:=explode('|',CreatePIDArrayInfo(pid));
        if length(splitty)=0 then begin
           if D then writeln('row:', i,' proc ['+l.Strings[i]+'],splitty=0,abort');
           continue;
        end;
        Arrays.Add('"'+pid+'"=>Array('+CRLF+'"memory"=>"'+splitty[0]+'","status"=>'+splitty[1]+',"process_path"=>"'+ProcessPath+'","processname"=>"'+ProcessName+'","cmdline"=>"'+org_cmdline+'"),');
        

      end;


  end;
  
  
  XWRT:=TStringList.Create;
  XWRT.Add('<?php');
  XWRT.Add('$processes=Array('+arrays.Text+');');
  XWRT.Add('?>');
  
  XWRT.SaveToFile(Paramstr(1));
  

  FreeAndNil(l);
  FreeAndnil(RegExpr);


end;
//#########################################################################################
function tps.CreatePIDArrayInfo(pid:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   l:TstringList;
   RegExpr:TRegExpr;
   i,t:integer;
   next:Tstringlist;
   MemorySIze:string;
   splitty:TstringList;
   username,line:string;
   grpname:string;
begin

    splitty:=TstringList.Create;
    if not FileExists('/proc/'+pid+'/status') then exit;
    l:=tstringList.Create;
    next:=TstringList.Create;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^([a-zA-Z_-]+):\s+(.+)';
    l.LoadFromFile('/proc/'+pid+'/status');
    for i:=0 to l.Count-1 do begin
        RegExpr.Expression:='^([a-zA-Z_-]+):\s+(.+)';
        if RegExpr.Exec(l.Strings[i]) then begin
           if LowerCase(RegExpr.Match[1])='uid' then begin
               line:=RegExpr.Match[2];
               RegExpr.Expression:='\s+';
               RegExpr.Split(line,splitty);
               if length(trim(splitty.Strings[0]))>0 THEN username:=UsernameFromID(splitty.Strings[0]);
               next.Add('"uid"=>"'+username+'",');
               username:='';
               continue;
           end;
           
           
           if LowerCase(RegExpr.Match[1])='gid' then begin
               line:=RegExpr.Match[2];
               RegExpr.Expression:='\s+';
               RegExpr.Split(line,splitty);
               if length(trim(splitty.Strings[0]))>0 THEN grpname:=GroupNameFromGID(StrToInt(splitty.Strings[0]));
               next.Add('"gid"=>"'+grpname+'",');
               grpname:='';
               continue;
           end;

           next.Add('"'+LowerCase(RegExpr.Match[1])+'"=>"'+RegExpr.Match[2]+'",');
         end;


        RegExpr.Expression:='^VmRSS:\s+([0-9]+)';
        if RegExpr.Exec(l.Strings[i]) then MemorySIze:=RegExpr.Match[1];
        
    
    end;
    
   result:=MemorySize+'|Array('+next.Text+')'+CRLF;
   FreeAndNil(next);
   FreeAndNil(RegExpr);
   FreeAndNil(l);


end;





function tps.DirectoryListTstring(path:string):TstringList;
var
   Dir:string;
   mSize:longint;
   Info : TSearchRec;
   D:boolean;
   f:TstringList;
   RegExpr:TRegExpr;
begin
    f:=TstringList.Create;
    RegExpr:=TRegExpr.Create;
    result:=f;
    path:=AnsiReplaceText(path,'//','/');
    if Copy(path,length(path),1)='/' then path:=Copy(path,0,length(path)-1);
    RegExpr.Expression:='/proc/[0-9]+';
    
    if Not DirectoryExists(path) then exit(f);

If FindFirst (path+'/*',(faAnyFile and faDirectory),Info)=0 then begin
    Repeat
       if info.Name<>'..' then begin
          if info.name<>'.' then begin
             dir:=path + '/' + info.Name;
              If (info.Attr=48) or (info.Attr=49) then begin
                 IF RegExpr.Exec(dir) then begin
                    f.Add(dir);
                 end;
              end;

          end;
       end;
    Until FindNext(info)<>0;
end;
    result:=f;
//    FreeAndNil(f);
end;
//#########################################################################################
function tps.IsBinaryString(const Value: string): Boolean;
var
  n: integer;
begin
  Result := False;
  for n := 1 to Length(Value) do
    if Value[n] in [#0..#8, #10..#31] then

    begin
      Result := True;
      Break;
    end;
end;
//#########################################################################################
function tps.TransFormBinaryString(const Value: string): string;
var
  n: integer;
begin

  for n := 1 to Length(Value) do begin
    if Value[n] in [#0..#8, #10..#31] then
    begin
         result:=result+' ';

    end else begin
         result:=result+Value[n];
  end;
  end;
end;
//#########################################################################################
function tps.ReadFileIntoString(path:string):string;
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

       if not FileExists(path) then exit;


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

           end;
result:=datas_file;
end;
//#########################################################################################
function tps.UsernameFromID(id:string):string;
begin
try
   result:=GetUserName(StrToInt(id));
except
      exit;
end;
end;
//##############################################################################
function tps.Explode(const Separator, S: string; Limit: Integer = 0):TStringDynArray;
var
  SepLen       : Integer;
  F, P         : PChar;
  ALen, Index  : Integer;
begin
  SetLength(Result, 0);
  if (S = '') or (Limit < 0) then
    Exit;
  if Separator = '' then
  begin
    SetLength(Result, 1);
    Result[0] := S;
    Exit;
  end;
  SepLen := Length(Separator);
  ALen := Limit;
  SetLength(Result, ALen);

  Index := 0;
  P := PChar(S);
  while P^ <> #0 do
  begin
    F := P;
    P := StrPos(P, PChar(Separator));
    if (P = nil) or ((Limit > 0) and (Index = Limit - 1)) then
      P := StrEnd(F);
    if Index >= ALen then
    begin
      Inc(ALen, 5); // mehrere auf einmal um schneller arbeiten zu können
      SetLength(Result, ALen);
    end;
    SetString(Result[Index], F, P - F);
    Inc(Index);
    if P^ <> #0 then
      Inc(P, SepLen);
  end;
  if Index < ALen then
    SetLength(Result, Index); // wirkliche Länge festlegen
end;
//#########################################################################################
function tps.GroupNameFromGID(group_id:dword):string;
begin
 result:='';

  try
     result:=GetGroupName(group_id);
  except
  exit();
  end;
end;
//##############################################################################
function tps.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 s:='';
 result:=false;
 if ParamCount>1 then begin
     for i:=2 to ParamCount do begin
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
//##############################################################################
end.

