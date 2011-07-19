unit installthread;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils,variants,oldlinux;
type
  mThread = class(TThread)
  private

    CountLocked:integer;
    CountTime:integer;
    ProcThreadStatus:boolean;
    MaxCount:integer;

  protected
    procedure Execute; override;



  public
  function StartExecution(isDebug:boolean):boolean;
    pid:integer;
    debug:boolean;
    FileToFollow:string;
    end;

implementation


var
  ID_local: integer;
  cmd_local: string;
  Time_local: integer;

procedure mThread.Execute;
begin
  ProcThreadStatus:=False;
  while not ProcThreadStatus do begin
        StartExecution(false);
  end;

end;

function mThread.StartExecution(isDebug:boolean):boolean;
var
     list:TstringList;
     i:integer;
begin
     Select(0,nil,nil,nil,100);
     if FileExists(FileToFollow) then begin
        list:=TstringList.Create;
        list.LoadFromFile(FileToFollow);
        if list.Count-1>MaxCount then begin
          For i:=MaxCount to  list.Count-1 do begin
            writeln(trim(list[i]));
            MaxCount:=i;
          end;
        end;
        list.free;
     
     end;


end;


end.


