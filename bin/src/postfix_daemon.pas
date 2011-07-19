program artica_postfix_daemon;

uses
  cthreads,linux,BaseUnix,oldlinux,Dos,Classes,SysUtils, logs,common,Process,RegExpr;

var
   i:integer;
   xLOGS:Tlogs;
   CMN:Tcommon;
   s:string;
   bHup,bTerm : boolean;
   fLog : text;
   logname : string;
   aOld,aTerm,aHup : pSigActionRec;
   ps1  : psigset;
   sSet : cardinal;
   pid : longint;
   secs : longint;
   hr,mn,sc,sc100 : word;
   parameters:string;
   myFile:TextFile;
   OLD_PID:string;

{ handle SIGHUP & SIGTERM }
{ keep this code small! }
procedure DoSig(sig : longint);cdecl;
begin
   case sig of
      SIGHUP : bHup := true;
      SIGTERM : bTerm := true;
   end;
end;

{ create the log file }
Procedure NewLog;
Begin
   Assign(fLog,logname);
   Rewrite(fLog);
   GetTime(hr,mn,sc,sc100);
   Writeln(flog,'Log created at ',hr:0,':',mn:0,':',sc:0);
   Close(fLog);
End;

//##############################################################################
procedure StartOperations;
var LOGS:Tlogs;
begin

   logs:=Tlogs.Create;
   logs.logsStart('artica-daemon:: *************************************************************************');
   logs.logsStart('artica-daemon:: PID ' +IntTOStr(getpid));
   logs.logsStart('artica-daemon:: StartOperations...');


   logs.free;
   
      TRY
      ForceDirectories('/etc/artica-postfix');
      AssignFile(myFile, '/etc/artica-postfix/artica-agent.pid');
      ReWrite(myFile);
      WriteLn(myFile, intTostr(getpid));
      CloseFile(myFile);
      EXCEPT
      writeln('SavePid-> error writing /etc/artica-postfix/artica-agent.pid');
      END;
   
  end;
function IsRoot():boolean;
begin
if GetEUid=0 then exit(true);
exit(false);
end;

//##############################################################################
function StartProcess(CMD:string):boolean;
var
 P:TProcess;
 Root:string;
begin
     Root:=ExtractFileDir(ParamStr(0));
     Shell( Root + '/'+CMD + ' &');exit;
     
     P := TProcess.Create(nil);
     try
     P.CommandLine := Root + '/'+CMD;
     P.Options := [poNoConsole];
     P.Execute;
     P.Free;
     finally

     end;
     
     
end;



function GetOLdPID():string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:TStringList;
   i:integer;
   datas:string;
   datas_file:string;
   D:boolean;
   RegExpr:TRegExpr;
   Path:string;
begin
      result:='0';
      path:='/etc/artica-postfix/artica-agent.pid';
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


begin
secs := 10;
    if IsRoot()=false then begin
        writeln('This program wust run as root');
        halt(0);
    end;

  if ParamStr(1)='no-daemon' then begin
        StartOperations();
        readln();
        halt(0)
  end;

 
      OLD_PID:=GetOLdPID();
     if FileExists('/proc/' + OLD_PID + '/exe') then begin
        writeln('There is already an instance running: ' +OLD_PID);
        halt(0);
     end;

    

    
    
   { set global daemon booleans }
   bHup := true; { to open log file }
   bTerm := false;

   { block all signals except -HUP & -TERM }
   sSet := $ffffbffe;
   ps1 := @sSet;
   sigprocmask(sig_block,ps1,nil);

   { setup the signal handlers }
   new(aOld);
   new(aHup);
   new(aTerm);
   { v1.0 changed the structure of SigActionRec }
   {$ifdef VER0 }
   aTerm^.sa_handler := @DoSig;
   aHup^.sa_handler := @DoSig;
   {$else}
   aTerm^.handler.sh := @DoSig;
   aHup^.handler.sh := @DoSig;
   {$endif}
   aTerm^.sa_mask := 0;
   aTerm^.sa_flags := 0;
   aTerm^.sa_restorer := nil;
   aHup^.sa_mask := 0;
   aHup^.sa_flags := 0;
   aHup^.sa_restorer := nil;
   SigAction(SIGTERM,aTerm,aOld);
   SigAction(SIGHUP,aHup,aOld);


   { daemonize }
   pid := Fork;
   Case pid of
      0 : Begin { we are in the child }
         {$ifdef VER1_00_0 }
         writeln('WARNING:');
         writeln('Please upgrade your compiler to a later snapshot.');
         writeln('v1.00 contains a bug in the system unit relative to');
         writeln('using /dev/null.');
         writeln('input, output, and stderr have not been re-directed.');
         {$else}
         Close(input);  { close standard in }
         Close(output); { close standard out }
         Assign(output,'/dev/null');
         ReWrite(output);
         Close(stderr); { close standard error }
         Assign(stderr,'/dev/null');
         ReWrite(stderr);
         {$endif}
      End;
      -1 : secs := 0;     { forking error, so run as non-daemon }
      Else Halt;          { successful fork, so parent dies }
   End;
        writeln('StartOperations...');

       StartOperations;
   { begin processing loop }
   If secs > 0 Then
   Repeat
      If bHup Then Begin
         {$I-}
         //Close(fLog);
         IoResult;
         {$I+}
         bHup := false;
      End;


      If not bTerm Then
         { wait a while }
         Select(0,nil,nil,nil,secs*500);
         StartProcess('process1');
         StartProcess('process2');
         StartProcess('process3');

          //Fset.logs('daemon loop');
   Until bTerm;
         CMN.Free;
         halt(0);
end.




