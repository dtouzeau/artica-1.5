unit rdiffbackup;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem;

  type
  trdiffbackup=class


private
     LOGS:Tlogs;
     SYS:TSystem;
     artica_path:string;



public
    procedure   Free;
    constructor Create;
    function rdiff_bin_path():string;
    function rdiff_version():string;
    function dar_bin_path():string;
    function dar_version():string;
    function dar_manager_bin_path():string;
    function build_dar_command(backup_path:string;source_path:string):string;
    FUNCTION DAR_PID():string;
    FUNCTION DAR_STATUS():string;
    FUNCTION DAR_DATABASE_EXISTS(database_name:string;uuid:string):boolean;
    FUNCTION DAR_DATABASE_COUNT_FILES(uuid:string):string;
    FUNCTION DAR_DATABASE_COUNT_FILES_SINGLE(database_number:string;uuid:string):string;
    FUNCTION DAR_DATABASE_SEARCH_FILES(uuid:string;pattern:string):string;
    FUNCTION DAR_DATABASE_FILE_INFO(uuid:string;target_file:string):string;
    FUNCTION DAR_DATABASE_PATH(uuid:string;num:string):string;
    function dar_int_version():integer;
    FUNCTION PERSO_BACKUPS_LIST():TstringList;

END;

implementation

constructor trdiffbackup.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;


       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure trdiffbackup.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
function trdiffbackup.rdiff_bin_path():string;
begin
  if FileExists('/usr/bin/rdiff-backup') then exit('/usr/bin/rdiff-backup');
end;
//##############################################################################
function trdiffbackup.dar_bin_path():string;
begin
  if FileExists('/usr/bin/dar') then exit('/usr/bin/dar');
end;
//##############################################################################
function trdiffbackup.dar_manager_bin_path():string;
begin
  if FileExists('/usr/bin/dar_manager') then exit('/usr/bin/dar_manager');
end;
//##############################################################################
function trdiffbackup.dar_int_version():integer;
var
   tmpstr:string;
begin
   tmpstr:=dar_version();
   if length(tmpstr)=0 then tmpstr:='0';
   tmpstr:=AnsiReplaceText(tmpstr,'.','');
   if Not TryStrToInt(tmpstr,result) then result:=0;
end;
//##############################################################################




function trdiffbackup.dar_version():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
   tmpstr:=logs.FILE_TEMP();
   fpsystem(dar_bin_path()+' -V >'+tmpstr + ' 2>&1');
   if not fileExists(tmpstr) then exit;

   l:=TstringList.Create;
   l.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='dar version\s+([0-9\.]+)';
   for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;
   end;

   l.Free;
   RegExpr.free;
end;
//##############################################################################
function trdiffbackup.rdiff_version():string;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
   tmpstr:=logs.FILE_TEMP();
   fpsystem(rdiff_bin_path()+' -V >'+tmpstr + ' 2>&1');
   if not fileExists(tmpstr) then exit;
   
   l:=TstringList.Create;
   l.LoadFromFile(tmpstr);
   logs.DeleteFile(tmpstr);
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='rdiff-backup\s+([0-9\.]+)';
   for i:=0 to l.Count-1 do begin
       if RegExpr.Exec(l.Strings[i]) then begin
          result:=RegExpr.Match[1];
          break;
       end;
   end;
   
   l.Free;
   RegExpr.free;
end;
//##############################################################################
function trdiffbackup.build_dar_command(backup_path:string;source_path:string):string;
var
   cmd:string;
   ref:string;
   exclude_compress:string;
   head:string;
   source_backup:string;
begin
  if not DirectoryExists(source_path) then exit;

  ref:=backup_path + '.1.dar';
  exclude_compress:='-Z "*.jpg" -Z "*.gz" -Z "*.zip" -Z "*.rar" -Z "*.arj"';

  if not FIleExists(ref) then begin
      cmd:=dar_bin_path() + ' -c "' + backup_path+'" -R "'+source_path+'" -s 734003200 -y -m 150 -X "*.iso" '+exclude_compress;
      exit(cmd);
  end else begin
  
      head:=ExtractFileName(backup_path);
      source_backup:=backup_path;
      backup_path:=AnsiReplaceText(backup_path,'/'+head,'');
      cmd:=dar_bin_path() + ' -c  "'+backup_path+'/'+head+'_`date -I`" -R "'+source_path+'" -A "'+source_backup+'" -w -s 734003200  -y -m 150 -X "*.iso" '+exclude_compress;
      exit(cmd);
  end;
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_PID():string;
begin
exit(SYS.PidAllByProcessPath(dar_bin_path()));
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_STATUS():string;
var
   ini:TstringList;
begin

ini:=TstringList.Create;
ini.Add('[DAR]');
if FileExists(dar_bin_path()) then  begin
      if SYS.PROCESS_EXIST(DAR_PID()) then ini.Add('running=1') else  ini.Add('running=0');
      ini.Add('application_installed=1');
      ini.Add('master_pid='+ DAR_PID());
      ini.Add('master_memory=' + IntToStr(SYS.PROCESS_MEMORY(DAR_PID())));
      ini.Add('master_version=' + dar_version());
      ini.Add('status='+SYS.PROCESS_STATUS(DAR_PID()));
      ini.Add('service_name=APP_DAR');
   end;

result:=ini.Text;
ini.free
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_DATABASE_EXISTS(database_name:string;uuid:string):boolean;
var
   l:TstringList;
   i:integer;
   RegExpr:TRegExpr;
   tmpstr:string;
begin
  result:=false;
  if not FileExists(dar_manager_bin_path()) then exit;
  tmpstr:=LOGS.FILE_TEMP();
  ForceDirectories('/opt/artica/share/dar');
  if not FileExists('/opt/artica/share/dar/'+uuid+'.db') then logs.OutputCmd('/usr/bin/dar_manager -C /opt/artica/share/dar/'+uuid+'.db');
  fpsystem(dar_manager_bin_path()+' -l -B /opt/artica/share/dar/'+uuid+'.db >'+tmpstr+' 2>&1');
  if not FileExists(tmpstr) then exit;
  RegExpr:=TRegExpr.Create;
  RegExpr.Expression:='([0-9]+)\s+(.+?)\s+(.+)';
  l:=TstringList.create;
  l.LOadFromFile(tmpstr);
  logs.DeleteFile(tmpstr);
  for i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.strings[i]) then begin
         if RegExpr.Match[3]=database_name then begin
            result:=true;
            break;
         end;
      end;
  end;
  
  l.free;
   RegExpr.free;
  
  
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_DATABASE_COUNT_FILES(uuid:string):string;
var
   tmpstr:string;
   cmd:string;
begin
  result:='0';
  if not FileExists(dar_manager_bin_path()) then exit;
  tmpstr:=LOGS.FILE_TEMP();
  ForceDirectories('/opt/artica/share/dar');
  if not FileExists('/opt/artica/share/dar/'+uuid+'.db') then exit('0');
  cmd:=dar_manager_bin_path() +' -u 0 -B /opt/artica/share/dar/'+uuid+'.db|wc -l >' +tmpstr +' 2>&1';
  fpsystem(cmd);
  logs.Debuglogs(cmd);
  result:=logs.ReadFromFile(tmpstr);
  logs.Debuglogs('DAR_DATABASE_COUNT_FILES:: result="'+result+'"');
  logs.DeleteFile(tmpstr);
  exit(result);
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_DATABASE_COUNT_FILES_SINGLE(database_number:string;uuid:string):string;
var
   tmpstr:string;
   cmd:string;
   res:string;
begin
  result:='0';
  if not FileExists(dar_manager_bin_path()) then exit;
  tmpstr:=LOGS.FILE_TEMP();
  ForceDirectories('/opt/artica/share/dar');
  if not FileExists('/opt/artica/share/dar/'+uuid+'.db') then exit('Error stat /opt/artica/share/dar/'+uuid+'.db');
  cmd:=dar_manager_bin_path() +' -u '+database_number+' -B /opt/artica/share/dar/'+uuid+'.db|wc -l >' +tmpstr +' 2>&1';
  fpsystem(cmd);
  logs.Debuglogs(cmd);
  res:=logs.ReadFromFile(tmpstr);
  logs.Debuglogs('DAR_DATABASE_COUNT_FILES_SINGLE:: result="'+res+'"');
  logs.DeleteFile(tmpstr);
  exit(res);
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_DATABASE_SEARCH_FILES(uuid:string;pattern:string):string;
var
   tmpstr:string;
   cmd:string;
   RegExpr:tRegExpr;
   l:TstringList;
   i:integer;
   s:TstringList;
begin
  result:='0';
  RegExpr:=TRegExpr.Create;
  if not FileExists(dar_manager_bin_path()) then exit;
  tmpstr:=LOGS.FILE_TEMP();
  pattern:=AnsiReplaceText(pattern,'*','');
  ForceDirectories('/opt/artica/share/dar');
  if not FileExists('/opt/artica/share/dar/'+uuid+'.db') then exit('0');
  cmd:=dar_manager_bin_path() +' -u 0 -B /opt/artica/share/dar/'+uuid+'.db|/bin/grep "'+pattern+'" >' +tmpstr +' 2>&1';
  fpsystem(cmd);
  logs.Debuglogs(cmd);
  if not FileExists(tmpstr) then exit;
  l:=TstringList.Create;
  l.LoadFromFile(tmpstr);
  logs.DeleteFile(tmpstr);
  RegExpr.Expression:='^\[(.+?)\]\[(.*?)\]\s+(.+)';
  s:=TstringList.Create;
  s.Add('<?php');
  s.Add('$_DAR_RES=Array(');

  for i:=0 to l.Count-1 do begin
      logs.Debuglogs(l.Strings[i]);
      if RegExpr.Exec(l.Strings[i]) then begin
           s.Add('"'+ RegExpr.Match[3]+'"=>"'+RegExpr.Match[3]+'",');
      end;
  
  end;
  s.Add(');');
  s.Add('?>');
  
  result:=s.Text;
  s.free;
  l.free;
  RegExpr.free;
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_DATABASE_FILE_INFO(uuid:string;target_file:string):string;
var
   tmpstr:string;
   cmd:string;
   RegExpr:tRegExpr;
   l:TstringList;
   i:integer;
   s:TstringList;
begin
  result:='0';
  RegExpr:=TRegExpr.Create;
  if not FileExists(dar_manager_bin_path()) then exit;
  tmpstr:=LOGS.FILE_TEMP();

  ForceDirectories('/opt/artica/share/dar');
  if not FileExists('/opt/artica/share/dar/'+uuid+'.db') then exit('0');
  cmd:=dar_manager_bin_path() +' -f '+target_file+' -B /opt/artica/share/dar/'+uuid+'.db >' +tmpstr +' 2>&1';
  fpsystem(cmd);
  logs.Debuglogs(cmd);
  if not FileExists(tmpstr) then exit;
  l:=TstringList.Create;
  l.LoadFromFile(tmpstr);
  logs.DeleteFile(tmpstr);
  RegExpr.Expression:='([0-9]+)\s+(.+)';
  s:=TstringList.Create;


  for i:=0 to l.Count-1 do begin
      logs.Debuglogs(l.Strings[i]);
      if RegExpr.Exec(l.Strings[i]) then begin
           s.Add(RegExpr.Match[1]+';'+RegExpr.Match[2]);
      end;

  end;
  result:=s.Text;
  s.free;
  l.free;
  RegExpr.free;
end;
//##############################################################################
FUNCTION trdiffbackup.DAR_DATABASE_PATH(uuid:string;num:string):string;
var
   tmpstr:string;
   cmd:string;
   RegExpr:tRegExpr;
   l:TstringList;
   i:integer;
begin
  result:='0';
  RegExpr:=TRegExpr.Create;
  if not FileExists(dar_manager_bin_path()) then exit;
  tmpstr:=LOGS.FILE_TEMP();

  ForceDirectories('/opt/artica/share/dar');
  if not FileExists('/opt/artica/share/dar/'+uuid+'.db') then exit();
  cmd:=dar_manager_bin_path() +' -l -B /opt/artica/share/dar/'+uuid+'.db >' +tmpstr +' 2>&1';
  fpsystem(cmd);
  logs.Debuglogs(cmd);
  if not FileExists(tmpstr) then exit;
  l:=TstringList.Create;
  l.LoadFromFile(tmpstr);
  logs.DeleteFile(tmpstr);
  RegExpr.Expression:=num+'\s+(.+?)\s+(.+)';



  for i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
           result:=RegExpr.Match[1]+'/'+RegExpr.Match[2];
           break;
      end;

  end;
  l.free;
  RegExpr.free;
end;
//##############################################################################
FUNCTION trdiffbackup.PERSO_BACKUPS_LIST():TstringList;
var
   RegExpr:tRegExpr;
   l:TstringList;
   i:integer;
   s:TstringList;
begin
  s:=TstringList.Create;
  result:=s;
  RegExpr:=TRegExpr.Create;
  if not FileExists('/etc/artica-postfix/artica-backup.conf') then exit;
  l:=TstringList.Create;
  l.LoadFromFile('/etc/artica-postfix/artica-backup.conf');
  RegExpr.Expression:='PersoPath=(.+)';



  for i:=0 to l.Count-1 do begin
      if RegExpr.Exec(l.Strings[i]) then begin
           s.Add(RegExpr.Match[1]);
      end;

  end;
  result:=s;
  l.free;
  RegExpr.free;
end;



end.
