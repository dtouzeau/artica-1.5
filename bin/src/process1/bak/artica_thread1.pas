program artica_thread1;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,process1,RegExpr,oldlinux;
var P1:Tprocess1;



//##############################################################################
function TestAnCreatePid():boolean;
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
   PidString:String;
   PiDPath:string;
   myFile:TextFile;
begin
      result:=false;
      PiDPath:='/etc/artica-postfix/artica-process1.pid';
      if not FileExists(path) then begin
        PidString:='0';
      end;
      TRY
        Afile:=TStringList.Create;
        Afile.LoadFromFile(PiDPath);

     EXCEPT
           PidString:='0';
     end;

           RegExpr:=TRegExpr.Create;
           RegExpr.Expression:='([0-9]+)';
           if RegExpr.Exec(Afile.Text) then PidString:=RegExpr.Match[1];
           RegExpr.Free;
           Afile.Free;
           

     if FileExists('/proc/' + PidString + '/exe') then exit();

     TRY
        ForceDirectories('/etc/artica-postfix');
        AssignFile(myFile, PiDPath);
        ReWrite(myFile);
        WriteLn(myFile, intTostr(getpid));
        CloseFile(myFile);
      EXCEPT
            exit;
      END;
      result:=true;

end;
//##############################################################################

procedure CreatePID();

begin

end;
//##############################################################################

begin

     if not TestAnCreatePid() then halt(0);


if ParamStr(1)='-V' then writeln('process1 start in debug mode');
CreatePID();
P1:=Tprocess1.Create();
if ParamStr(1)='-V' then writeln('process1 DONE');
halt(0);
end.

