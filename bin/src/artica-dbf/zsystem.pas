unit zsystem;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, SysUtils,Process,strutils,IniFiles,oldlinux,BaseUnix,Logs,RegExpr in 'RegExpr.pas',md5;

  type
  Tsystem=class


private
       GLOBAL_INI:TIniFile;
        ArticaDirectory,jour,heure:string;
        ArticaBuildPath:string;
        version:string;

       function ReadFileIntoString(path:string):string;
       function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;


public
      constructor Create();
      procedure Free;
        procedure BuildArticaFiles();
       function IsGroupExists(groupname:string):boolean;
       function SystemUserID(username:string):string;
       function SystemGroupID(group_name:string):string;
       function DirectoryGroupOwner(path:string):string;
       function SystemGroupName(group_id:dword):string;
       function IsUserExists(username:string):boolean;
       procedure CreateGroup(Groupname:string);
       procedure AddUserToGroup(username:string;groupname:string;xshell:string;sHome:string);
       function DirectoryCountFiles(FilePath: string):integer;
       function ScanArticaFiles(FilePath:string):integer;
       procedure ShowScreen(line:string);
       function ScanINIArticaFiles(key:string):string;
       function ScanINIArticaFilesSave(key:string;Value:string):string;
       function DirFiles(FilePath: string;pattern:string):TstringList;
       DirListFiles:TstringList;
       function DirDir(FilePath: string):TstringList;
       
END;

implementation

constructor Tsystem.Create();
begin
   DirListFiles:=TstringList.Create();
end;
PROCEDURE Tsystem.Free();
begin
DirListFiles.Clear;
DirListFiles.Free;
end;
function Tsystem.IsGroupExists(groupname:string):boolean;
var
FileDatas:TStringList;
group_path:string;
index:integer;
RegExpr:TRegExpr;
begin
       group_path:='/etc/group';
       FileDatas:=TStringList.Create;
       if not FileExists(group_path) then begin
          writeln('ERROR FATAL !! -> ' + group_path +' Doesn''t exists ???');
          exit(false);
       end;

       RegExpr:=TRegExpr.Create;
       FileDatas.LoadFromFile(group_path);
       RegExpr.expression:=groupname;
       for Index := 0 to FileDatas.Count - 1 do  begin
           if  RegExpr.Exec(FileDatas.Strings[Index]) then begin
             RegExpr.Free;
             exit(true);
       end;
   end;

   RegExpr.Free;
   exit(false);


end;
function Tsystem.SystemUserID(username:string):string;
var
FileDatas:TStringList;
group_path:string;
index:integer;
RegExpr:TRegExpr;
begin
       group_path:='/etc/passwd';
       FileDatas:=TStringList.Create;
       if not FileExists(group_path) then begin
          writeln('ERROR FATAL !! -> ' + group_path +' Doesn''t exists ???');
          exit();
       end;

       RegExpr:=TRegExpr.Create;
       FileDatas.LoadFromFile(group_path);
       RegExpr.expression:=username+':x:([0-9]+):([0-9]+)::';
       for Index := 0 to FileDatas.Count - 1 do  begin
           if  RegExpr.Exec(FileDatas.Strings[Index]) then begin
             Result:=RegExpr.Match[2];
             RegExpr.Free;
             exit();
       end;
   end;

   RegExpr.Free;
   exit();
end;
function Tsystem.SystemGroupID(group_name:string):string;
var
FileDatas:TStringList;
group_path:string;
index:integer;
RegExpr:TRegExpr;
GroupIDString:string;
begin
       group_path:='/etc/group';
       FileDatas:=TStringList.Create;
       if not FileExists(group_path) then begin
          writeln('ERROR FATAL !! -> ' + group_path +' Doesn''t exists ???');
          exit();
       end;

       RegExpr:=TRegExpr.Create;
       FileDatas.LoadFromFile(group_path);
       RegExpr.expression:=group_name+':x:([0-9]+)';
       for Index := 0 to FileDatas.Count - 1 do  begin
           if  RegExpr.Exec(FileDatas.Strings[Index]) then begin
                  Result:=RegExpr.Match[1];
                  RegExpr.Free;
                  exit();
               end;

      end;

   RegExpr.Free;
   exit();


end;
function Tsystem.DirectoryGroupOwner(path:string):string;
var info:stat;
var Guid:dword;
begin

if fpstat(path,info)=0 then  begin

//  writeln ('uid     : ',info.st_uid);
  Guid:=info.st_gid;
  Result:=SystemGroupName(Guid);
end;
end;

function Tsystem.SystemGroupName(group_id:dword):string;
var
FileDatas:TStringList;
group_path:string;
index:integer;
RegExpr:TRegExpr;
GroupIDString:string;
begin
       group_path:='/etc/group';
       GroupIDString:=IntToStr(group_id);
       FileDatas:=TStringList.Create;
       if not FileExists(group_path) then begin
          writeln('ERROR FATAL !! -> ' + group_path +' Doesn''t exists ???');
          exit();
       end;

       RegExpr:=TRegExpr.Create;
       FileDatas.LoadFromFile(group_path);
       RegExpr.expression:='([A-Za-z0-9-_]+):x:([0-9]+)';
       for Index := 0 to FileDatas.Count - 1 do  begin
           if  RegExpr.Exec(FileDatas.Strings[Index]) then begin
               if RegExpr.Match[2]=GroupIDString then begin;
                  Result:=RegExpr.Match[1];
                  RegExpr.Free;
                  exit();
               end;
           end;
      end;

   RegExpr.Free;
   exit();


end;
function Tsystem.IsUserExists(username:string):boolean;
var
FileDatas:TStringList;
group_path:string;
index:integer;
RegExpr:TRegExpr;
begin
       group_path:='/etc/passwd';
       FileDatas:=TStringList.Create;
       if not FileExists(group_path) then begin
          writeln('ERROR FATAL !! -> ' + group_path +' Doesn''t exists ???');
          exit(false);
       end;

       RegExpr:=TRegExpr.Create;
       FileDatas.LoadFromFile(group_path);
       RegExpr.expression:=username;
       for Index := 0 to FileDatas.Count - 1 do  begin
           if  RegExpr.Exec(FileDatas.Strings[Index]) then begin
             RegExpr.Free;
             exit(true);
       end;
   end;

   RegExpr.Free;
   exit(false);
end;

procedure Tsystem.CreateGroup(Groupname:string);
begin
  if FileExists('/usr/sbin/groupadd') then Shell('/usr/sbin/groupadd ' + Groupname );
end;

procedure Tsystem.AddUserToGroup(username:string;groupname:string;xshell:string;sHome:string);
var cmd:string;
begin
    if FileExists('/usr/sbin/useradd') then begin
           if length(groupname)>0 then   cmd:='-g ' + groupname + ' ' ;
           if length(xshell)>0 then   cmd:=cmd + '-s ' + xshell+ ' ';
           if length(sHome)>0 then   cmd:=cmd + '-d ' + sHome+ ' ';
           cmd:=cmd + username;
           Shell('/usr/sbin/useradd ' + cmd );
    end;
end;


function Tsystem.ReadFileIntoString(path:string):string;
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
        writeln('Error:thProcThread.ReadFileIntoString -> file not found (' + path + ')');
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
              writeln('Error:thProcThread.ReadFileIntoString -> unable to read (' + path + ')');
           end;
result:=datas_file;


end;
//#########################################################################################
function Tsystem.DirectoryCountFiles(FilePath: string):integer;
Var Info : TSearchRec;
    Count : Longint;

Begin
  Count:=0;
  If FindFirst (FilePath+'/*',faAnyFile and faDirectory,Info)=0 then
    begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin
              if Info.Attr=48 then count:=count +  DirectoryCountFiles(FilePath + '/' +Info.Name);
              if Info.Attr=16 then count:=count +  DirectoryCountFiles(FilePath + '/' +Info.Name);
              if Info.Attr=32 then Inc(Count);
              //Writeln (Info.Name:40,Info.Size:15);
         end;
      end;

    Until FindNext(info)<>0;
    end;
  FindClose(Info);
  exit(count);
end;
//#########################################################################################
function Tsystem.DirFiles(FilePath: string;pattern:string):TstringList;
Var Info : TSearchRec;
    Count : Longint;
    D:boolean;
Begin
  D:=COMMANDLINE_PARAMETERS('debug');
  if D then ShowScreen('DirFiles:: ' + FilePath + ' ' + pattern );
  Count:=0;
  If FindFirst (FilePath+'/'+ pattern,faAnyFile,Info)=0 then begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin
           if D then ShowScreen('DirFiles:: Found ' + Info.Name );
           DirListFiles.Add(Info.Name);

         end;
      end;

    Until FindNext(info)<>0;
    end;
  FindClose(Info);
  DirFiles:=DirListFiles;
  exit();
end;
//#########################################################################################
function Tsystem.DirDir(FilePath: string):TstringList;
Var Info : TSearchRec;
    Count : Longint;
    D:boolean;
Begin
  D:=COMMANDLINE_PARAMETERS('debug');
  if D then ShowScreen('DirDir:: ' + FilePath + ' *' );
  Count:=0;
   DirListFiles:=TstringList.Create();
  If FindFirst (FilePath+'/*',faDirectory,Info)=0 then begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin
           if D then ShowScreen('DirDir:: Found ' + Info.Name );
           DirListFiles.Add(Info.Name);

         end;
      end;

    Until FindNext(info)<>0;
    end;
  FindClose(Info);
  DirDir:=DirListFiles;
  exit();
end;
//#########################################################################################

procedure Tsystem.BuildArticaFiles();
var
    timestamp:string;
    BackupSrc:string;
begin



    
  if DirectoryExists(ArticaBuildPath + '/bin/src') then begin
       ShowScreen('ScanArticaFiles:: move source files...');
       BackupSrc:='/tmp/src_' + version;
       Shell('/bin/mv ' + ArticaBuildPath + '/bin/src' + ' ' +  BackupSrc);
    end;
    ShowScreen('ScanArticaFiles:: remove unecessary source files...');
    if DirectoryExists(ArticaBuildPath + '/bin') then begin
       shell('/bin/rm ' + ArticaBuildPath + '/bin/*.o');
       shell('/bin/rm ' + ArticaBuildPath + '/bin/*.ppu');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/ressources/settings.inc');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/01cpu*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/02loadavg*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/03mem*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/04hddio*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/05hdd*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/06proc*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/10net*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/img/mailgraph_*.png');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/ressources/databases/postfix-queue-cache.conf');
       Shell('/bin/rm -rf ' + ArticaBuildPath + '/ressources/databases/queue.list.*.cache');
       Shell('/bin/rm  ' + ArticaBuildPath + '/ressources/settings.inc');
       shell('/usr/bin/strip -s ' + ArticaBuildPath + '/bin/artica-install');
       shell('/usr/bin/strip -s ' + ArticaBuildPath + '/bin/artica-postfix');
    end;
    
    ShowScreen('ScanArticaFiles:: Compresss source folder...');
    shell('cd ' + BackupSrc + ' && tar -czf /tmp/artica_src_' + version + '.tgz *');
    ShowScreen('ScanArticaFiles:: Compresss application folder...=>cd ' + ArticaBuildPath + ' && tar -czf /tmp/artica_' + version + '.tgz *');
    shell('cd ' + ArticaBuildPath + ' && tar -czf /tmp/artica_' + version + '.tgz *');

    Shell('/bin/rm -rf ' + ArticaBuildPath);
    Shell('/bin/rm -rf ' + BackupSrc);
    
    ShowScreen('ScanArticaFiles::Done...');
    
end;






function Tsystem.ScanArticaFiles(FilePath:string):integer;
Var
   Info : TSearchRec;
    Count : Longint;
    path,Directory:string;
    PathTo:string;
    PathFrom:string;
    PathTime:integer;
    FileDateNum:Longint;
    Ini:TiniFile;
    IniDateNum:longint;
    DateMD5String:string;
    IniDateMD5String,snap:string;
    TMD5 : TMD5Digest;
    major:integer;
    minor:integer;
    build:integer;
    NewBuild:integer;
    FileMD5:string;
Begin
  Count:=0;

  
  
  
  if length(ArticaDirectory)=0 then begin
     ArticaDirectory:=ExtractFileDir(ParamStr(0));
     ArticaDirectory:=AnsiReplaceText(ArticaDirectory,'/bin','');
     ShowScreen('ScanArticaFiles:: ArticaDirectory="' + ArticaDirectory + '"');
  end;
  
  if length(ArticaBuildPath)=0 then begin
     if COMMANDLINE_PARAMETERS('build')=true then begin
      Ini:=TiniFile.Create(ArticaDirectory + '/version.ini');
      NewBuild:=ini.ReadInteger('VERSION','build',0);
      NewBuild:=NewBuild+1;
      ini.WriteInteger('VERSION','build',NewBuild);
      snap:='snapshot';
     end else begin
      Ini:=TiniFile.Create(ArticaDirectory + '/version.ini');
      NewBuild:=ini.ReadInteger('VERSION','minor',0);
      NewBuild:=NewBuild+1;
      ini.WriteInteger('VERSION','minor',NewBuild);
      shell('/bin/rm /etc/artica-postfix/Builder.ini');
      snap:='full';
     end;
    version:= snap + '.' + IntToStr(ini.ReadInteger('VERSION','major',0)) + '.' +IntToStr(ini.ReadInteger('VERSION','minor',0)) + '.' + IntToStr(ini.ReadInteger('VERSION','build',0));
    ArticaBuildPath:='/tmp/artica-postfix_' + version;
    ShowScreen('ScanArticaFiles:: ArticaBuildDirectory="' + ArticaBuildPath + '"');
  end;

  if length(FilePath)=0 then FilePath:=ArticaDirectory;
  Directory:=AnsiReplaceText(FilePath,ArticaDirectory,'');


  
  If FindFirst (FilePath+'/*',faAnyFile and faDirectory,Info)=0 then
    begin
    Repeat
      if Info.Name<>'..' then begin
         if Info.Name <>'.' then begin
              if Info.Attr=48 then begin
                 ScanArticaFiles(FilePath + '/' +Info.Name);
              end;
                 
              if Info.Attr=16 then begin

                  ScanArticaFiles(FilePath + '/' +Info.Name);
              end;

              if Info.Attr=32 then begin
                 PathTo:=Directory + '/' +Info.Name;
                 PathFrom:=FilePath + '/' +Info.Name;
                 FileDateNum:=FileAge(PathFrom);
                 if Copy(PathTo,0,1)='/' then PathTo:=Copy(PathTo,2,length(PathTo)-1);
                 PathTo:=ArticaBuildPath + '/' + PathTo;
                 PathTime:=info.Time;
                 TMD5:=MD5String(IntToSTr(FileDateNum));
                 DateMD5String:=MD5Print(TMD5);

                 
                 TMD5:=MD5String(PathFrom);
                 FileMD5:=MD5Print(TMD5);
                 
                 IniDateMD5String:=ScanINIArticaFiles(FileMD5);

                if DateMD5String<>IniDateMD5String then begin
                   ShowScreen('ScanArticaFiles:: ' + FileMD5 + '("' + DateMD5String + '" <> "' +IniDateMD5String + '")');
                   ForceDirectories(ExtractFileDir(PathTo));
                   Shell('/bin/cp ' + PathFrom + ' ' + PathTo);
                   ScanINIArticaFilesSave(FileMD5,DateMD5String);
                end;

              end;
              
         end;
      end;

    Until FindNext(info)<>0;
    end;
  FindClose(Info);




end;
function Tsystem.ScanINIArticaFiles(key:string):string;
var
   Ini:TStringList;
   RegExpr:TRegExpr;
   i:integer;
  begin
  if not FileExists('/etc/artica-postfix/Builder.ini') then exit;
  Ini:=TStringList.Create;
  Ini.LoadFromFile('/etc/artica-postfix/Builder.ini');
  RegExpr:=TRegExpr.create;
  RegExpr.Expression:=key+'=([a-z0-9]+)';
  for i:=0 to ini.Count-1 do begin;
  if RegExpr.Exec(ini.Strings[i]) then begin
     result:=RegExpr.Match[1];
     RegExpr.Free;
     Ini.Free;
     exit();
  end;
  end;
 RegExpr.Free;
     Ini.Free;

end;
function Tsystem.ScanINIArticaFilesSave(key:string;Value:string):string;
var     Ini:TiniFile;
  begin
  Ini:=TiniFile.Create('/etc/artica-postfix/Builder.ini');
   ini.WriteString('BUILD',key,Value);
  ini.Free;
  exit
end;


//#########################################################################################
procedure Tsystem.ShowScreen(line:string);
 var  logs:Tlogs;
 begin
    logs:=Tlogs.Create();
    writeln('Tsystem::' + line);
    logs.Enable_echo_install:=True;
    logs.Logs('Tsystem::' + line);
    logs.free;

 END;
//#########################################################################################
function Tsystem.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
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
