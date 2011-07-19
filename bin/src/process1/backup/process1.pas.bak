program process1;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,RegExpr,unix,baseUnix, principale,global_conf,logs,zSystem,monitorix;
var
   P1:Tprocess1;
   GLOBAL_INI:myconf;
   D:boolean;
   master_exists:boolean;
   zlogs:Tlogs;
   mypid:string;
   SYS:Tsystem;
   zmonitorix:tmonitorix;

//##############################################################################
function TestAnCreatePid():boolean;
var
   Afile:TStringList;
   RegExpr:TRegExpr;
   PidString:String;
   PiDPath:string;
   myFile:TextFile;
   GLOBAL_INI:myconf;
   minutes_delayed:integer;

begin
PidString:='0';
      result:=false;
      PiDPath:='/etc/artica-postfix/artica-process1.pid';
      GLOBAL_INI:=myconf.Create;
      if FileExists(PiDPath) then begin
         minutes_delayed:=GLOBAL_INI.SYSTEM_FILE_BETWEEN_NOW(PiDPath);
      end;
      
      Afile:=TStringList.Create;
      GLOBAL_INI:=myconf.Create();
      if FIleExists(PiDPath) then Afile.LoadFromFile(PiDPath);

           RegExpr:=TRegExpr.Create;
           RegExpr.Expression:='([0-9]+)';
           if RegExpr.Exec(Afile.Text) then PidString:=RegExpr.Match[1];
           RegExpr.Free;
           Afile.Free;

           if length(PidString)>0 then begin
                 if PidString<>'0' then begin
                     if FileExists('/proc/' + PidString + '/exe') then begin
                        if minutes_delayed>4 then begin
                           fpsystem('/bin/kill -9 ' +  PidString);
                        end else begin
                            exit();
                        end;
                     end;
                 end;
           end;

     TRY
        ForceDirectories('/etc/artica-postfix');
        AssignFile(myFile, PiDPath);
        ReWrite(myFile);
        WriteLn(myFile, intTostr(fpgetpid));
        CloseFile(myFile);
      EXCEPT
            exit;
      END;
      result:=true;

end;
//##############################################################################


begin
D:=false;
GLOBAL_INI:=myconf.Create();
zlogs:=tlogs.Create;
SYS:=Tsystem.Create;
if ParamStr(1)='-V' then begin
   writeln('process1 start in debug mode');
   D:=True;
end;

if not D then  D:= GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
mypid:=IntToStr(fpgetpid);
if ParamStr(1)='--local-sid' then begin
        GLOBAL_INI.SYSTEM_LOCAL_SID();
        halt(0);
end;

if ParamStr(1)='--cpup' then begin
       try
       writeln(SYS.GET_CPU_POURCENT(StrToInt(ParamStr(2))));
       finally
       end;
       halt(0);
end;

if SYS.PROCESS_EXIST(SYS.PIDOF_PATTERN('process1 '+ParamStr(1))) then begin
     zlogs.Debuglogs('Aborting command line process1 '+ParamStr(1) +' Already executed');
     halt(0);
end;


if ParamStr(1)='time' then begin
   writeln('running since: ',SYS.PROCCESS_TIME_MIN(ParamStr(2)),' mn');
   halt(0);
end;


if ParamStr(1)='--force' then begin
        P1:=Tprocess1.Create();
        zlogs.Debuglogs('shutdown...');
        zlogs.OutputCmd('/bin/touch /etc/artica-postfix/process1.cron');
        halt(0);
end;


if ParamStr(1)='--checkout' then begin
   P1:=Tprocess1.Create();
   halt(0);
end;

if ParamStr(1)='--help' then begin
   writeln('-V................: Run in debug mode');
   writeln('debug.............: Add debug infos');
   writeln('-perm.............: Set permissions');
   writeln('-mysql............: Parse mysql queue and perform queries');
   writeln('--start...........: Start services');
   writeln('--start --force...: Start services even the master process doesn''t exists');


   D:=True;
   halt(0);
end;

    if length(ParamStr(1))>0 then zlogs.Debuglogs('Receive "' +ParamStr(1)+'"');

 if ParamStr(1)='--iostat' then begin
          P1:=Tprocess1.Create();
          writeln(P1.IOSTAT());
          halt(0);
     end;

 if ParamStr(1)='--cpulimit' then begin
          P1:=Tprocess1.Create();
          P1.CleanCpulimit();
          halt(0);
     end;


     if ParamStr(1)='-perm' then begin
          if SYS.croned_seconds(10) then begin
          P1:=Tprocess1.Create();
          P1.CheckFoldersPermissions();
          end;
          halt(0);
     end;
     
     if ParamStr(1)='-kasstat' then begin
          P1:=Tprocess1.Create();
          P1.move_kas3_stats();
          halt(0);
     end;
     

     if ParamStr(1)='-exec' then begin
          if SYS.croned_seconds(5) then begin
          writeln('start ' +  ParamStr(2));
          fpsystem(ParamStr(2));
          writeln('handle done');
          end;
          halt(0);
     end;

    if ParamStr(1)='--mysql-status' then begin
       writeln(SYS.MYSQL_STATUS());
       halt(0);
    end;

 if ParamStr(1)='--kill' then begin
         P1:=Tprocess1.Create();
         P1.KillsfUpdatesBadProcesses();
         P1.CleanBadProcesses();
         halt(0);
     end;
     

 if ParamStr(1)='--cleanlogs' then begin
         P1:=Tprocess1.Create();
         P1.cleanlogs();
         writeln('Memory used: ',SYS.PROCESS_MEMORY(mypid));
         halt(0);
     end;

 if ParamStr(1)='--mailgraph' then begin
         P1:=Tprocess1.Create();
         P1.mailgraph_log();
         halt(0);
     end;
     

 if ParamStr(1)='--monitorix' then begin
          zmonitorix:=Tmonitorix.Create;
          zmonitorix.Start();

          halt(0);
     end;



     
     
     master_exists:=GLOBAL_INI.SYSTEM_PROCESS_EXIST(GLOBAL_INI.ARTICA_DAEMON_GET_PID());
     if ParamStr(1)='--force' then begin
        master_exists:=true;
        P1:=Tprocess1.Create();
        halt(0);
     end;
        
     
     if ParamStr(1)='--start' then begin
              if D then writeln('starting all artica-postfix services....');
              if master_exists then begin
                 if D then writeln('Sleep 700');
                 sleep(700);
                 if D then writeln('-> SYSTEM_START_ARTICA_ALL_DAEMON()');
                 zlogs.Debuglogs('->GLOBAL_INI.SYSTEM_START_ARTICA_ALL_DAEMON();');
                 GLOBAL_INI.SYSTEM_START_ARTICA_ALL_DAEMON();


              end else begin
                 if D then writeln('Artica-postfix daemon is stopped, aborting');
              end;
      halt(0);
     end;
     

     mypid:=SYS.GET_PID_FROM_PATH('/etc/artica-postfix/artica-process1.pid');
     if SYS.PROCESS_EXIST(mypid) then begin
         zlogs.Debuglogs('Tprocess1.Create():: '+mypid+' PID Already exists..Abort');
         halt(0);
     end;


     if not TestAnCreatePid() then begin
        zlogs.Debuglogs('TestAnCreatePid() return false, shutdown...');
        halt(0);
     end;
     
     if Not FileExists('/etc/artica-postfix/process1.cron') then zlogs.OutputCmd('/bin/touch /etc/artica-postfix/process1.cron');
     
     if SYS.FILE_TIME_BETWEEN_SEC('/etc/artica-postfix/process1.cron')>20 then begin
        zlogs.Debuglogs('Tprocess1.Create();');
        P1:=Tprocess1.Create();
        zlogs.Debuglogs('shutdown...');
        zlogs.OutputCmd('/bin/touch /etc/artica-postfix/process1.cron');
     end;

     
     zlogs.Debuglogs('----------------------------------------------------------------------');
halt(0);
end.

