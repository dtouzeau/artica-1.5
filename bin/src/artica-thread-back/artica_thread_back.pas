program artica_thread_back;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,logs,global_conf,zsystem,BaseUnix,strutils, htmlback;



var
   GLOBAL_INI   :myconf;
   hook         :Thtmlback;
   SYS          :Tsystem;
   pid          :string;
begin

  SYS:=Tsystem.Create();
  pid:=SYS.PROCESS_LIST_PID(Paramstr(0));

  pid:=trim(AnsiReplaceText(pid,intTostr(fpgetpid),''));
  if length(pid)>0 then begin
     halt(0);
  end;


  hook:=Thtmlback.Create();
  hook.Free;






end.
