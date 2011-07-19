unit SharedFolders;

interface
uses
  Libc, Classes,logs,SysUtils,global_conf,IniFiles,RegExpr,zsystem,unix,dateutils;
type
  tSharedFolder = class
  private
    tid          : integer;
    logs         :tlogs;
    fileSize_mem :integer;
    GLOBAL_INI   :myconf;
    SYS          :Tsystem;
    artica_path  :string;
    SharedFolders:TstringList;
    MountedClients:TstringList;


    procedure    ParseSharedFolders(ini_path:string);
    procedure    ListSharedFolder();
    procedure    StartSharedFolders();
    procedure    ParseSharedFolders(SourcePath:string;prefix:string;ini:TiniFile;gpid:string);
    procedure    MountSharedFolder(SourcePath:string;DestPath:string;gpid:string);
    function     IsMounted(DestPath:string):boolean;
    procedure    AddMountedHistory(path:string;gpid:string);
    procedure    RemoveMountedHistory(gpid:string);
    procedure    RemoveInHistory(line:string);



    procedure    ExecuteProcess(commandLine:string);

  protected
    procedure Execute; override;
    
  public
    TimeToLive:TDateTime;
    constructor Create(startSuspended: boolean; newID: integer);
  end;




var
  thingCount: integer;
  thingCountCS: TRTLCriticalSection;

implementation


constructor tSharedFolder.Create();
begin
     GLOBAL_INI   :=myconf.Create;
     SYS          :=Tsystem.Create;
     artica_path  :=GLOBAL_INI.get_ARTICA_PHP_PATH();
     logs         :=tlogs.Create;
     SharedFolders:=TstringList.Create;
     MountedClients:=TstringList.Create;


      StartSharedFolders();


  end;

  logs.Debuglogs('Execute:: -> end');
end;


procedure tSharedFolder.ListSharedFolder();
var
   l:TstringList;
   RegExpr:TRegExpr;
   i:Integer;
begin
  SharedFolders.Clear;
  MountedClients.Clear;
  SYS.DirFiles('/etc/artica-postfix/SharedFolers','*.sha');
  logs.Debuglogs('DaemonThread1::ListSharedFolder:: ' + IntTOStr(SYS.DirListFiles.Count) + ' files..');
  SharedFolders.AddStrings(SYS.DirListFiles);
  
  l:=TstringList.Create;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='(.+?)\s+(\/.+?)\s+none\s+.+?bind\s+';
  if FileExists('/etc/mtab') then begin
      l.LoadFromFile('/etc/mtab');
      for i:=0 to l.Count-1 do begin
          if RegExpr.Exec(l.Strings[i]) then begin
              MountedClients.Add(RegExpr.Match[2]);
          end;
      end;
  end;
  
  
  
end;
//#########################################################################################
procedure tSharedFolder.StartSharedFolders();
var
   i:Integer;
   ini_path:string;
begin
   ListSharedFolder();
   
   for i:=0 to SharedFolders.Count-1 do begin
        ini_path:='/etc/artica-postfix/SharedFolers/' + SharedFolders.Strings[i];
        logs.Debuglogs('DaemonThread1::StartSharedFolders:: ' + ini_path);
        ParseSharedFolders(ini_path);
   end;

end;
//#########################################################################################
procedure tSharedFolder.ExecuteProcess(commandLine:string);
begin
  logs.Debuglogs('DaemonThread1:: -->' + artica_path+'/bin/'+commandLine +' &');
  fpsystem(artica_path+'/bin/'+commandLine +' &');
end;
//#########################################################################################

procedure tSharedFolder.ParseSharedFolders(ini_path:string);
var
   ini:TiniFile;
   prefix:string;
   sharedfolder_count:integer;
   gpid,sourcePath:string;
   key:string;
   i:integer;
begin
   ini:=TiniFile.Create(ini_path);
   prefix:=ini.ReadString('SHARED','SharedFolderPrefix','');
   gpid:=ini.ReadString('SHARED','groupid','0');
   if gpid='0' then exit;
   key:=ini.ReadString('SHARED','key','');
   RemoveMountedHistory(gpid);
   sharedfolder_count:=ini.ReadInteger('SHARED_FOLDERS','sharedfolder_count',0);
   logs.Debuglogs('DaemonThread1::StartSharedFolders:: prefix='+ prefix + ' Sources folder=' + INtToStr(sharedfolder_count) + ' [group=' + gpid + ']');
   
   for i:=0 to sharedfolder_count-1 do begin
       sourcePath:=ini.ReadString('SHARED_FOLDERS','shared.' + IntToStr(i),'');
       if FileExists(sourcePath) then begin
              ParseSharedFolders(SourcePath,prefix,ini,gpid);
       end;
   
   end;

end;
//#########################################################################################
procedure tSharedFolder.ParseSharedFolders(SourcePath:string;prefix:string;ini:TiniFile;gpid:string);
var
i:integer;
MemberCount:integer;
HomeDirectory:string;
TargetDir:string;
Dirname:string;
begin
    MemberCount:=ini.ReadInteger('members','members_count',0);
    Dirname:=ExtractFileName(SourcePath);
    for i:=0 to MemberCount-1 do begin
         HomeDirectory:=ini.ReadString('members','member.'+IntToStr(i),'');
         if length(HomeDirectory)>0 then begin
             TargetDir:=HomeDirectory + '/' + prefix + Dirname;
             if Not DirectoryExists(TargetDir) then begin
                ForceDirectories(TargetDir);
                MountSharedFolder(SourcePath,TargetDir,gpid);
             end else begin
                 if not IsMounted(TargetDir) then MountSharedFolder(SourcePath,TargetDir,gpid);
             
             end;
         end;
    
    end;

end;
//#########################################################################################
procedure tSharedFolder.MountSharedFolder(SourcePath:string;DestPath:string;gpid:string);
var cmd:string;
begin
   cmd:='/bin/mount --bind ' + SourcePath + ' ' + DestPath + ' && /bin/mount --make-shared ' + DestPath;
   logs.Syslogs('Bind source ' + SourcePath + ' to destination ' + DestPath + ' for group ' + gpid);
   logs.Debuglogs('DaemonThread1::MountSharedFolder::Bind source ' + SourcePath + ' to destination ' + DestPath + ' for group ' + gpid);
   fpsystem(cmd);
   AddMountedHistory(DestPath,gpid);
end;
//#########################################################################################
function tSharedFolder.IsMounted(DestPath:string):boolean;
var
   i:Integer;
begin
    result:=false;
    for i:=0 to MountedClients.Count-1 do begin
          if DestPath=MountedClients.Strings[i] then begin
              result:=True;
              break;
          end;
    
    end;

end;
//#########################################################################################
procedure tSharedFolder.AddMountedHistory(path:string;gpid:string);
var
   l:TstringList;
   ini_path:string;
   tmpini:TiniFile;
   key:string;
begin
    ini_path:='/etc/artica-postfix/SharedFolers/' + gpid +'.sha';
    if FileExists(ini_path) then begin
        tmpini:=TiniFile.Create(ini_path);
        key:=tmpini.ReadString('SHARED','key','nokey');
        tmpini.Free;
    end else begin
        key:='nokey';
    end;
    l:=TstringList.Create;
    if FileExists('/etc/artica-postfix/mounted.db') then l.LoadFromFile('/etc/artica-postfix/mounted.db');
    l.Add(gpid+';'+path+';'+key);
    l.SaveToFile('/etc/artica-postfix/mounted.db');
    l.free;
end;
//#########################################################################################
procedure tSharedFolder.RemoveMountedHistory(gpid:string);
var
   l:TstringList;
   ini_path:string;
   tmpini:TiniFile;
   key:string;
   RegExpr:TRegExpr;
   i:integer;
begin
    ini_path:='/etc/artica-postfix/SharedFolers/' + gpid +'.sha';
    if not FileExists(ini_path) then exit;
    tmpini:=TiniFile.Create(ini_path);
    key:=tmpini.ReadString('SHARED','key','nokey');
    tmpini.Free;
    if not FileExists('/etc/artica-postfix/mounted.db') then exit;
    RegExpr:=TRegExpr.Create;
    RegExpr.Expression:='^([0-9]+);(.+?);(.+)';
    l:=TstringList.Create;
    l.LoadFromFile('/etc/artica-postfix/mounted.db');
    for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
            if RegExpr.Match[1]=gpid then begin
                 if RegExpr.Match[3]<>key then begin
                     logs.Debuglogs('DaemonThread1::MountSharedFolder::unmount ' + RegExpr.Match[2]);
                     logs.Syslogs('DaemonThread1::MountSharedFolder::unmount ' + RegExpr.Match[2]);
                     fpsystem('/bin/umount -l ' + RegExpr.Match[2]);
                     rmdir(RegExpr.Match[2]);
                     RemoveInHistory(l.Strings[i]);
                     RemoveInHistory(l.Strings[i]);
                     RemoveInHistory(l.Strings[i]);
                 end;
            end;
       end;
    end;
    
    l.Free;
    RegExpr.Free;
    
end;
//#########################################################################################

procedure tSharedFolder.RemoveInHistory(line:string);
var
   l:TstringList;
   i:Integer;
begin
    if not FileExists('/etc/artica-postfix/mounted.db') then exit;
    l:=TstringList.Create;
    l.LoadFromFile('/etc/artica-postfix/mounted.db');
    for i:=0 to l.Count-1 do begin
        if l.Strings[i]=line then begin
           logs.Debuglogs('DaemonThread1::MountSharedFolder::Delete line ' + IntToStr(i) + 'in /etc/artica-postfix/mounted.db');
            l.Delete(i);
            break;
        end;
    
    end;
    
    L.SaveToFile('/etc/artica-postfix/mounted.db');
    l.Free;
    
end;
//#########################################################################################






end.


