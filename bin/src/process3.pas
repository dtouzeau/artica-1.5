unit process3;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils,variants, Process,oldlinux,logs,zsystem,
  RegExpr,
  global_conf in 'global_conf.pas';
type
  Tprocess3=class
  private
    LOGS:Tlogs;
    GLOBAL_INI:myconf;
    function LaunchSubQueueScan(QueueNumber:String):boolean;
    function ArticaSendOLdPID(QueueNumberString:string):string;
    procedure ParseSubQueues();
    function ExecPipe(commandline:string):string;
    procedure ParseThreadsCommands();
    debug:boolean;
    function GetFileSizeKo(path:string):longint;
    PROCEDURE THreadlogs(zText:string);
    procedure CleanMailRelayQueue();
    procedure Execute;
    D:boolean;


  public

    constructor Create;
    end;

implementation

//##############################################################################

constructor Tprocess3.Create;
begin
   forcedirectories('/etc/artica-postfix');
   GLOBAL_INI:=myConf.Create();
   LOGS:=Tlogs.Create;
   LOGS.logsThread('process3','artica-daemon:: Tprocess3:: Create');
   if ParamStr(1)='-V' then D:=true;
   Execute();
end;

//##############################################################################
procedure Tprocess3.Execute;
var
CacheQueue:integer;
IpInterface:integer;
QueueNumber:integer;
artica_send_pid:string;
artica_sql_pid:string;
begin

     LOGS.logsThread('process3','Execute:: Initialized');
     LOGS.logsThread('process3','Execute:: Process3 PID ' +IntTOStr(getpid));

     ParseThreadsCommands();

     TRY
      GLOBAL_INI.ARTICA_FILTER_WATCHDOG();
      
      if not FileExists('/usr/local/sbin/emailrelay') then begin
            ParseSubQueues();
      end else begin
          CleanMailRelayQueue();
      
      end;
      
      
      QueueNumber:=GLOBAL_INI.ARTICA_SQL_QUEUE_NUMBER();
        LOGS.logsThread('process3','Execute:: SQL QUEUE NUMBER: ' +IntTOStr(QueueNumber));
      
      
      if QueueNumber>0 then begin
         artica_sql_pid:=GLOBAL_INI.ARTICA_SQL_PID();
         if not GLOBAL_INI.SYSTEM_PROCESS_EXIST(artica_send_pid) then begin
            LOGS.logs('artica-daemon:: Tprocess3:: Exectute artica-sql for ' + IntToStr(QueueNumber) + ' sql commands');
            LOGS.logsThread('process3','Exectute artica-sql for ' + IntToStr(QueueNumber) + ' sql commands');
            ExecPipe(GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-sql');
            LOGS.logsThread('process3','DONE');
         end;
      end;
      EXCEPT

      LOGS.ERRORS('Tprocess3::ERROR IN LOOP ! function "Execute" in ProcTHread3.pas');

      end;




     LOGS.logsThread('process3','Terminating thread 3...');

end;
//##############################################################################
procedure Tprocess3.CleanMailRelayQueue();
var
   sys:Tsystem;
   i:integer;
   bodyFilePath:string;
   queue_path:string;
begin
     sys:=Tsystem.Create();
     queue_path:=GLOBAL_INI.ARTICA_FILTER_QUEUEPATH();
     GLOBAL_INI.ARTICA_FILTER_CLEAN_QUEUE();
     sys.DirFiles(queue_path,'*.bad');
     if D then LOGS.logsThread('process3','CleanMailRelayQueue:: Bad files : ' + IntToStr(sys.DirListFiles.Count) );
     
     if sys.DirListFiles.Count>0 then begin
         for i:=0 to sys.DirListFiles.Count-1 do begin
             if D then LOGS.logsThread('process3','CleanMailRelayQueue:: ' + sys.DirListFiles.Strings[i] );
             bodyFilePath:=LeftStr(sys.DirListFiles.Strings[i],pos('.envelope',sys.DirListFiles.Strings[i])-1) + '.content';
            if FileExists(queue_path+'/'+ bodyFilePath) then shell('/bin/rm ' + queue_path+'/'+ bodyFilePath);
            if FileExists(queue_path+'/'+ sys.DirListFiles.Strings[i]) then shell('/bin/rm ' + queue_path+'/'+ sys.DirListFiles.Strings[i]);
         end;
     
     
     end;
     
     sys.Free;

end;


procedure Tprocess3.ParseSubQueues();
var
   i:Integer;
   QueueFilesNumber:integer;
   MaxProcessSend:integer;
   ProcessNumber:integer;
begin
     MaxProcessSend:=GLOBAL_INI.ARTICA_SEND_MAX_SUBQUEUE_NUMBER();
     LOGS.logsThread('process3','MaxProcessSend=' + IntToStr(MaxProcessSend) + ' Max Process queue parsers');

 For i:=0 to 99 do begin
        QueueFilesNumber:=GLOBAL_INI.ARTICA_SEND_SUBQUEUE_NUMBER(IntToStr(i));
       if QueueFilesNumber>0 then begin
           LOGS.logsThread('process3','for artica-send Queue number (' + IntToStr(i) + ')='+intToStr(QueueFilesNumber));
           if LaunchSubQueueScan(IntToStr(i))=true then begin
                inc(ProcessNumber);
                if ProcessNumber>MaxProcessSend then break;
           end;
        end;
 end;


end;
//##############################################################################
function Tprocess3.LaunchSubQueueScan(QueueNumber:String):boolean;
var
   artica_send_pid:string;
   P:TProcess;

begin
     result:=false;
     artica_send_pid:=ArticaSendOLdPID(QueueNumber);
     //LOGS.logsThread('process3',QueueNumber + ' PID: "' + artica_send_pid + '"');
     if FileExists('/proc/' + artica_send_pid + '/exe') then exit(false);
        LOGS.logs('artica-daemon:: Tprocess3:: Execute artica-send for ' + QueueNumber + ' subqueue');
        LOGS.logsThread('process3','Execute artica-send for ' + QueueNumber + ' subqueue');
        P := TProcess.Create(nil);
        P.CommandLine := GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-send ' + QueueNumber;
        P.Execute;
        P.free;
        exit(true);

end;
//##############################################################################
function Tprocess3.ArticaSendOLdPID(QueueNumberString:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:TStringList;
   i:integer;
   datas:string;
   datas_file:string;
   RegExpr:TRegExpr;
   Path:string;

begin
      result:='0';
      path:='/etc/artica-postfix/artica-send.' + QueueNumberString + '.pid';
      if not FileExists(path) then begin
        exit();
      end;
      TRY
        Afile:=TStringList.Create;
        Afile.LoadFromFile(path);

     EXCEPT
           exit();
     end;

           RegExpr:=TRegExpr.Create;
           RegExpr.Expression:='([0-9]+)';
           if RegExpr.Exec(Afile.Text) then result:=RegExpr.Match[1];
           RegExpr.Free;
           Afile.Free;




end;
//##############################################################################





procedure Tprocess3.ParseThreadsCommands();
var
   FileDataCommand:TstringList;
   i:integer;
   XLogs:TLogs;
begin
    XLogs:=Tlogs.Create;

    TRY
    if not fileExists('/etc/artica-postfix/background') then begin
       XLogs.free;
       if D then LOGS.logsThread('process3','ParseThreadsCommands:: DONE');
       exit;
    end;

    LOGS.logsThread('process3','OPEN /etc/artica-postfix/background');
    FileDataCommand:=TstringList.Create;
    FileDataCommand.LoadFromFile('/etc/artica-postfix/background');
    shell('/bin/rm /etc/artica-postfix/background');
    for i:=0 to FileDataCommand.Count -1 do begin
        XLogs.logsThread('thProcThread','ParseThreadsCommands:: -> Shell('+ FileDataCommand.Strings[i]+')');
        XLogs.logs('Tprocess3.ParseThreadsCommands:: -> Shell('+ FileDataCommand.Strings[i]+')');
        shell(FileDataCommand.Strings[i]);
    end;
    FileDataCommand.Free;
    EXCEPT
    LOGS.ERRORS('Tprocess3::ParseThreadsCommands FATAL ERROR' );
    end;
    XLogs.free;
    LOGS.logsThread('process3','ParseThreadsCommands:: DONE');

end;
//##############################################################################




function Tprocess3.ExecPipe(commandline:string):string;
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
PROCEDURE Tprocess3.THreadlogs(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        size:longint;
      BEGIN

        TargetPath:='/var/log/artica-postfix/thread3.log';

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

function Tprocess3.GetFileSizeKo(path:string):longint;
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

