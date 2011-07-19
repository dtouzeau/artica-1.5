program artica_postfix_daemon;

uses
  cthreads,linux,BaseUnix,oldlinux,Dos,Classes,Crt,SysUtils, logs,common,process_infos,global_conf,cyrus,kav_mail,
  ProcThread1,ProcThread2, ProcThread3;
   //inet_thread_daemon
var
   i:integer;
   Fset:thProcThread;
   Fset2:thProcThread2;
   xLOGS:Tlogs;
   GLOBAL_INI:myconf;
   CMN:Tcommon;
   mcyrus:Tcyrus;
   kaspersky:TkavMail;
   ppr:Tprocessinfos;
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
   str_pid:string;
   debug:boolean;
   scount:integer;
   Thread3:thProcThread3;
  // mysock:MyInet;

{ handle SIGHUP & SIGTERM }
{ keep this code small! }
procedure DoSig(sig : longint);cdecl;
begin
   case sig of
      SIGHUP : bHup := true;
      SIGTERM : bTerm := true;
   end;
end;

{ create the pid file }
Procedure CreatePID;
Var       F : Text;
          myFile : TextFile;
          TargetPath:string;
begin
TargetPath:='/etc/artica-postfix/artica-agent.pid';
 if FileExists(TargetPath) then begin
    TRY
       Assign (F,TargetPath);
       Erase (f);
    EXCEPT
          writeln('Error: unable to delete (' + TargetPath + ')');
    end;
 end;
    
    TRY
       AssignFile(myFile, TargetPath);
       ReWrite(myFile);
       WriteLn(myFile, intTostr(getpid));
       CloseFile(myFile);
    EXCEPT
          writeln('Error: writing pid file' +     TargetPath);
    END;
    
End;


procedure StartOperations;
begin

        VerfyPID();
        if debug then writeln('StartOperations: artica-postfix starting pid '   + IntTOStr(getpid));
        if debug then writeln('StartOperations: artica-postfix Create First Thread');
        Fset:=thProcThread.Create(false);
        Fset2:=thProcThread2.Create;
        Thread3:=thProcThread3.Create;
        fset.Resume;
        if debug then writeln('StartOperations: artica-postfix Create First Thread done...');

        xLOGS:=Tlogs.Create;
        CMN:=TCommon.Create;


   xLOGS.logs('Saving artica PID ' +IntTOStr(getpid));
   xLOGS.logs('Using mail logs ' +CMN.mail_log_path());

 end;
 
procedure VerfyPID();
var
begin
    GLOBAL_INI:=myconf.Create;
    str_pid:=GLOBAL_INI.ARTICA_DAEMON_GET_PID();

    if length(str_pid)>0 then begin
       if FileExists('/proc/' + str_pid + '/exe') then begin
          writeln('An artica-postfix instance (' + str_pid + ') is currently running or waiting to close.. please restart service');
          halt(0);
       end;
    end;

    GLOBAL_INI.Free;
    CreatePID();
end;
 
 
function IsRoot():boolean;
begin
if GetEUid=0 then exit(true);
exit(false);
end;




begin
debug:=False;
secs := 10;
    if IsRoot()=false then begin
        writeln('This program wust run as root');
        halt(0);
    end;
 parameters:=Trim(ParamStr(1));
 if length(parameters)>0 then begin
      if parameters='debug' then debug:=True;
      

      
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
         if debug=false then begin
            Close(input);  { close standard in }
            Close(output); { close standard out }
            Assign(output,'/dev/null');
            ReWrite(output);
            Close(stderr); { close standard error }
            Assign(stderr,'/dev/null');
            ReWrite(stderr);
         end;
         {$endif}
      End;
      -1 : secs := 0;     { forking error, so run as non-daemon }
      Else Halt;          { successful fork, so parent dies }
   End;

        if fileexists('/etc/artica-postfix/shutdown') then shell(' rm -rf /etc/artica-postfix/shutdown');
        StartOperations;
   { begin processing loop }
   If secs > 0 Then
   Repeat
      writeln('bHup -> ',bHup);
      If bHup Then Begin
         {$I-}
         IoResult;
         {$I+}
         bHup := false;
      End;


      If not bTerm Then
         { wait a while }
         Select(0,nil,nil,nil,secs*1000);
         if FileExists('/etc/artica-postfix/shutdown') then bTerm:=True;


   Until bTerm;
         scount:=0;
         writeln('bTerm -> ',bHup);
         writeln('Close -> threads');
         XLOGS.logs('DAEMON: Free threads and terminate daemon...');
         Fset.ProcThreadStatus:=True;
         Fset.Terminate;
         Thread3.Terminate;
         while not Fset.Stopped do begin
               XLOGS.logs('DAEMON: waiting stopping all threads Timeout:' + IntToStr(scount));
               Fset.Terminate;
               Thread3.Terminate;
               Select(0,nil,nil,nil,secs*300);
               Inc(scount);
               if scount>5 then begin
                  writeln('timeout...finish');
                  XLOGS.logs('DAEMON: timeout...finish..');
                  halt(0);
               end;
               if not fileexists('/etc/artica-postfix/shutdown') then Shell('echo ''kkk'' >/etc/artica-postfix/shutdown');
         end;


         XLOGS.logs('DAEMON: Stopped successfully');
         writeln('Close -> DONE');
         XLOGS.Free;
         Fset.Free;
         CMN.Free;

         halt(0);
end.




