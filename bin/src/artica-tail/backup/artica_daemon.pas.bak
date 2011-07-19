program artica_tail;

uses
  cthreads,libc,linux,BaseUnix,unix,Dos,Classes,SysUtils, logs,Process,RegExpr,global_conf,
  wthread,zsystem;

var
myFile:TextFile;
pid:Tpid;
GLOBAL_INI:Myconf;
zlogs:Tlogs;
thread1: TSampleThread;


function IsRoot():boolean;
begin
if FpGetEUid=0 then exit(true);
exit(false);
end;

//##############################################################################
function StartProcess(CMD:string):boolean;
var
 P:TProcess;
 Root:string;
 D:boolean;
 Silent:string;
begin

D:=false;
 GLOBAL_INI:=myconf.Create();
D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');

if not D then Silent:=' & >/dev/null 2>&1';

     result:=false;
     Root:=ExtractFileDir(ParamStr(0));
     if D then zlogs.logs(Root + '/'+CMD + Silent);
     if not FileExists( Root + '/'+CMD) then exit;


     fpsystem( Root + '/'+CMD + Silent);


     exit;
     
     P := TProcess.Create(nil);
     try
     P.CommandLine := Root + '/'+CMD;
     P.Options := [poNoConsole];
     P.Execute;
     P.Free;
     finally

     end;
     result:=true;
     
end;
//##############################################################################

procedure daemon;
var
   D:boolean;
   SYS:Tsystem;
begin
SYS:=Tsystem.Create();
D:=false;
GLOBAL_INI:=myconf.Create();
D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
zlogs:=Tlogs.Create;

      ForceDirectories('/etc/artica-postfix');
      if SYS.PROCESS_EXIST(SYS.GET_PID_FROM_PATH('/etc/artica-postfix/artica-tail.pid')) then begin
      
           zlogs.Debuglogs('Starting......: Artica-tail already exists PID ' + sys.PidAllByProcessPath(paramstr(0)) +' aborting...');
           halt(0);
      end;
      SYS.FREE;

TRY

      

      AssignFile(myFile, '/etc/artica-postfix/artica-tail.pid');
      ReWrite(myFile);
      WriteLn(myFile, intTostr(fpgetpid));
      zlogs.Debuglogs('--> starting PID '+intTostr(fpgetpid));

      CloseFile(myFile);
      zlogs.Logs('Starting......: artica-postfix daemon..SavePid-> writing /etc/artica-postfix/artica-tail.pid pid=' + IntTostr(fpgetpid));
      EXCEPT
            zlogs.Logs('Starting......: artica-tail daemon..SavePid-> error writing /etc/artica-postfix/artica-tail.pid');
            writeln('SavePid-> error writing /etc/artica-postfix/artica-tail.pid');
      END;




 thread1 := TSampleThread.Create(false, 1);

 
 
// thread2:=TSampleThread2.Create(false, 1);

 
zlogs.logs('artica-tail --> Starting loop');
 while (true) do begin
    sleep(1000);

  end;
zlogs.logs('artica-tail --> Terminate');

end;
//##############################################################################
begin
 zlogs:=Tlogs.Create;
 GLOBAL_INI:=myconf.Create();
 if IsRoot()=false then begin
        writeln('This program wust run as root');
        halt(0);
    end;
    
  if Paramstr(1)='--debug' then begin
     writeln('start in debug mode');
      thread1 := TSampleThread.Create(true, 1);
      halt(0);
  
  end;
    
  if FileExists('/etc/artica-postfix/autokill') then fpsystem('/bin/rm /etc/artica-postfix/autokill');
     pid:=fpfork;
    Case pid of
      0 : Begin { we are in the child }
         Close(input);  { close standard in }
         Close(output); { close standard out }
         Assign(output,'/dev/null');
         ReWrite(output);
         //Close(stderr); { close standard error }
         //Assign(stderr,'/dev/null');
         //ReWrite(stderr);
         daemon();
      End;
      -1 : daemon();    { forking error, so run as non-daemon }
      Else Halt;          { successful fork, so parent dies }
   End;

   
   
end.

