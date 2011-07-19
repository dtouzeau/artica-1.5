unit process2;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils,variants, Process,Linux,IniFiles,oldlinux,strutils,logs,dateutils,
  RegExpr,
  confiles in 'confiles.pas',
  global_conf in 'global_conf.pas',
  postfix_standard;
type
  Tprocess2=class
  private
    LOGS:Tlogs;
    GLOBAL_INI:myconf;
    function ExecPipe(commandline:string):string;
    debug:boolean;
    PHP_PATH:string;
    procedure AveServerStatistics();
    procedure QUEUEGRAPH();
    function GetFileSizeKo(path:string):longint;
    PROCEDURE THreadlogs(zText:string);
    procedure Execute;



  public

    constructor Create;
    end;

implementation

//##############################################################################
procedure Tprocess2.Execute;
var count:integer;
CacheQueue:integer;
IpInterface:integer;
postfix_standard:Tpostfix_standard;
begin



     PHP_PATH:=GLOBAL_INI.get_ARTICA_PHP_PATH();


     if not DirectoryExists(PHP_PATH +'/ressources/rrd') then begin
        logs.logs('Creating ' + PHP_PATH + '/ressources/rrd' );
        THreadlogs('Creating ' + PHP_PATH + '/ressources/rrd' );
        forcedirectories(PHP_PATH +'/ressources/rrd');
        shell('/bin/chmod 755 ' + PHP_PATH +'/ressources/rrd');
      end;

     count:=0;
     CacheQueue:=0;



              THreadlogs('Execute::NOOP CacheQueue="' + IntToStr(CacheQueue) + '"');
              inc(CacheQueue);
              inc(IpInterface);
              if IpInterface=5 then begin
                 if length(GLOBAL_INI.get_INFOS('ChangeAutoInterface'))>0 then begin
                    postfix_standard:=Tpostfix_standard.Create;
                    postfix_standard.ChangeAutoIpInterfaces;
                    IpInterface:=0;
                 end;
              end;

              AveServerStatistics();
              if fileexists('/usr/bin/rrdtool') then begin
                 QUEUEGRAPH();
                 count:=count+1;
           end;

end;

//##############################################################################
constructor Tprocess2.Create;
begin
   forcedirectories('/etc/artica-postfix');
   GLOBAL_INI:=myConf.Create();
   LOGS:=Tlogs.Create;
   Debug:=GLOBAL_INI.get_DEBUG_DAEMON();
   logs.logsStart('artica-daemon:: ThProcThread[2] Created...');
   Execute();
end;
//##############################################################################
procedure Tprocess2.QUEUEGRAPH();
begin
if FileExists('/etc/cron.d/artica_queuegraph') then GLOBAL_INI.QUEUEGRAPH_IMAGES();
end;
//##############################################################################

function Tprocess2.ExecPipe(commandline:string):string;
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
  // writeln(commandline);
  M := TMemoryStream.Create;
  BytesRead := 0;
  P := TProcess.Create(nil);
  P.CommandLine := commandline;
  P.Options := [poUsePipes];
  if debug then LOGS.Logs('MyConf.ExecPipe -> ' + commandline);

  P.Execute;
  while P.Running do begin
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end
    else begin
      Sleep(100);
    end;

  end;

  repeat
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end;
  until n <= 0;
  M.SetSize(BytesRead);
  S := TStringList.Create;
  S.LoadFromStream(M);
  if debug then LOGS.Logs('Tprocessinfos.ExecPipe -> ' + IntTostr(S.Count) + ' lines');
  for n := 0 to S.Count - 1 do
  begin
    if length(S[n])>1 then begin

      xRes:=xRes + S[n] +CRLF;
    end;
  end;
  if debug then LOGS.Logs('Tprocessinfos.ExecPipe -> exit');
  S.Free;
  P.Free;
  M.Free;
  exit( xRes);
end;
//##############################################################################
PROCEDURE Tprocess2.THreadlogs(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        size:longint;
      BEGIN

        TargetPath:='/var/log/artica-postfix/thread2.log';

        forcedirectories('/var/log/artica-postfix');
        zDate:=DateToStr(Date)+ chr(32)+TimeToStr(Time);


        xText:=zDate + ' ' + zText;

        TRY

        EXCEPT
        END;

        TRY
           if GetFileSizeKo(TargetPath)>1000 then begin
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
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################

function Tprocess2.GetFileSizeKo(path:string):longint;
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
end.

