program artica_daemon;

uses
  cthreads,libc,linux,BaseUnix,unix,Dos,Classes,SysUtils, logs,Process,RegExpr,global_conf,
  DaemonThread1,dateutils,zSystem,debian_class;

var
 myFile         :TextFile;
 pid            :Tpid;
 GLOBAL_INI     :Myconf;
 zlogs          :Tlogs;
 thread1        :TSampleThread;



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
GLOBAL_INI:=myconf.Create();




     result:=false;
     Root:=ExtractFileDir(ParamStr(0));


     zlogs.Debuglogs('master daemon:: -> EXEC:"'+ Root + '/'+CMD + Silent  + '"');
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
   debug:string;
   D:boolean;
   Beetwen:integer;
   SYS:Tsystem;
   deb:Tdebian;
begin
D:=false;
debug:='';
GLOBAL_INI:=myconf.Create();
D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
if D then debug:=' debug';
zlogs:=Tlogs.Create;
SYS:=Tsystem.Create;
deb:=Tdebian.Create;

if paramStr(1)='iso' then begin
   if SYS.croned_minutes2(30) then begin
      deb.ARTICA_CD_SOURCES_LIST();
   end;
   halt(0);
end;

if paramStr(1)='keyexists' then begin
   writeln(paramStr(2),':',deb.APT_KEY_EXISTS(paramStr(2)));
   halt(0)
end;


if SYS.PROCESS_EXIST(GLOBAL_INI.ARTICA_DAEMON_GET_PID()) then begin
    zlogs.Debuglogs('daemon process already exists...aborting');
    halt(0);
end;

TRY



      deb.linuxlogo();
      deb.INSTALL_NTFS3G();
      deb.free;
      
      ForceDirectories('/etc/artica-postfix');
      AssignFile(myFile, '/etc/artica-postfix/artica-agent.pid');
      ReWrite(myFile);
      WriteLn(myFile, intTostr(fpgetpid));
      zlogs.Debuglogs('daemon():: starting PID '+intTostr(fpgetpid));
      CloseFile(myFile);
      zlogs.Debuglogs('daemon():: artica-postfix daemon..SavePid-> writing /etc/artica-postfix/artica-agent.pid pid=' + IntTostr(fpgetpid));
      EXCEPT
      zlogs.Debuglogs('daemon()::artica-postfix daemon..SavePid-> fatal error writing /etc/artica-postfix/artica-agent.pid');

      END;
 GLOBAL_INI.set_INFOS('MysqlTooManyConnections','0');
 

 

 
 zlogs.Syslogs('Starting artica-postfix daemon pid ' + IntTostr(fpgetpid));
 zlogs.Debuglogs('InitializeCriticalSection...');
 InitializeCriticalSection(thingCountCS);
 zlogs.DeleteFile('/etc/artica-postfix/startall');
 
 zlogs.Debuglogs('Starting......: Thread1');
 thread1 := TSampleThread.Create(false, 1);
 thread1.TimeToLive:=now;
 zlogs.Debuglogs('daemon()::Daemon successfull started with new PID '+intTostr(fpgetpid)+' --> Starting loop');

 while (true) do begin
    if FileExists('/etc/artica-postfix/autokill') then fpsystem('/bin/rm /etc/artica-postfix/autokill');
    zlogs.Debuglogs('master daemon:: -->Sleep 10000');
    sleep(10000);
    zlogs.Debuglogs('master Daemon:: Thread (1) second ??');
    Beetwen:=SecondsBetween(Now,thread1.TimeToLive);

    zlogs.Debuglogs('master Daemon:: Thread (1) ' + IntToStr(Beetwen) + ' seconds ');

  if beetwen>120 then begin
         zlogs.Debuglogs('master Daemon:: Warning !' + IntToStr(Beetwen) + ' seconds sleeping > 120 for thread 1, restart Daemon...');
         fpsystem('/etc/init.d/artica-postfix restart daemon &');
         halt(0);
  end;
    
  end;


  
zlogs.Debuglogs('daemon --> Terminate ----------------------------');
SYS.Free;

end;
//##############################################################################
begin
 zlogs:=Tlogs.Create;
 GLOBAL_INI:=myconf.Create();
 if IsRoot()=false then begin
        writeln('This program wust run as root');
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
         //Assign(stderr,Char('/dev/null'));
         //ReWrite(stderr);
         daemon();
      End;
      -1 : daemon();    { forking error, so run as non-daemon }
      Else Halt;          { successful fork, so parent dies }
   End;
   
   
   
end.

