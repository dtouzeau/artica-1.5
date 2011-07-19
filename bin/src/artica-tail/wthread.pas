unit wthread;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
interface
uses
  Libc, Classes,SysUtils,unix,wthread3,md5,baseunix;
type
  TSampleThread = class(TThread)
  private
    tid          : integer;
    thread3      : TSampleThread3;
    MaxlogSize   :integer;
    PROCEDURE    OutputCmd(command:string);
    procedure    DeleteFile(TargetPath:string);
    function     GetFileSizeKo(path:string):longint;
    PROCEDURE    Debuglogs(zText:string);
    function     FILE_TEMP():string;
    function     MD5FromString(value:string):string;

  protected
    procedure Execute; override;
  public
  
    constructor Create(startSuspended: boolean; newID: integer);
  end;
  
  
  
  

implementation

procedure TSampleThread.Execute;
begin

     Debuglogs('wthread0:['+ IntToStr(tid)+']: Starting loop...');
  while not Terminated do begin
    try

       Debuglogs('wthread0:['+ IntToStr(tid)+']: '+ExtractFilePath(ParamStr(0)) + 'artica-tail-syslog');
       OutputCmd(ExtractFilePath(ParamStr(0)) + 'artica-tail-syslog');
       OutputCmd(ExtractFilePath(ParamStr(0)) + 'artica-ldap -nmap');
    except
       Debuglogs('wthread0:['+ IntToStr(tid)+']: FATAL ERROR');
    end;
     Debuglogs('wthread0:['+ IntToStr(tid)+']: wait 5 seconds...');
    __sleep(5);
  end;
  
  Debuglogs('wthread0:['+ IntToStr(tid)+']: Terminated...');
end;


constructor TSampleThread.Create(startSuspended: boolean;newID: integer);
begin
  inherited Create(startSuspended);
  tid := ThreadID;
  MaxlogSize:=100;
  thread3:= TSampleThread3.Create(false);


end;
//##############################################################################
PROCEDURE TSampleThread.OutputCmd(command:string);
var
   tmp:string;
   cmd:string;
   zout:string;
   i:Integer;
   l:TstringList;
   r:Tstringlist;
begin
   tmp:=FILE_TEMP();
   cmd:=command + ' >' + tmp + ' 2>&1';
   Debuglogs(cmd);
   fpsystem(cmd);
   if FileExists(tmp) then begin
      l:=TstringList.Create;
      r:=TstringList.Create;
      l.LoadFromFile(tmp);

      for i:=0 to l.Count-1 do begin
          if length(trim(l.Strings[i]))>0 then r.Add(l.Strings[i]);
      end;
      if r.Count>0 then begin
         Debuglogs(r.Text);

      end;
      r.free;
      l.free;
      DeleteFile(tmp);
   end;
end;
//##############################################################################
procedure TSampleThread.DeleteFile(TargetPath:string);
Var F : Text;

begin
  if not FileExists(TargetPath) then exit;
  TRY
    Assign (F,TargetPath);
    Erase (f);
  EXCEPT
     Debuglogs('Delete():: -> error I/O in ' +     TargetPath);
  end;
end;
//#############################################################################
PROCEDURE TSampleThread.Debuglogs(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        MasterDirectory:string;
        info : stat;
        processname:string;
      BEGIN
        processname:=ExtractFileName(ParamStr(0));
        MasterDirectory:='/var/log/artica-postfix';

        forcedirectories(MasterDirectory);
        TargetPath:=MasterDirectory+'/' + processname + '.debug';
        zDate := FormatDateTime('yyyy-mm-dd hh:nn:ss', Now);
        xText:=zDate + ' ' +intTostr(fpgetpid)+ ' '+ zText;

        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            try
               WriteLn(myFile, xText);
            finally
            CloseFile(myFile);
            end;
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################
function TSampleThread.GetFileSizeKo(path:string):longint;
Var
L : File Of byte;
size:longint;
ko:longint;

begin
if not FileExists(path) then begin
   result:=0;
   exit;
end;
   TRY
  Assign (L,path);
  Reset (L);
  size:=FileSize(L);
   Close (L);
  ko:=size div 1024;
  result:=ko;
  EXCEPT

  end;
end;
//##############################################################################
function TSampleThread.FILE_TEMP():string;
begin
result:='/opt/artica/tmp/'+ MD5FromString(FormatDateTime('yyyy-mm-dd hh:nn:ss', Now)+IntToStr(random(2548)));
end;
//##############################################################################
function TSampleThread.MD5FromString(value:string):string;
var
Digest:TMD5Digest;
begin
Digest:=MD5String(value);
exit(MD5Print(Digest));
end;
//##############################################################################
end.


