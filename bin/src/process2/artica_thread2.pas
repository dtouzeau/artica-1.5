program artica_thread2;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,oldlinux, process2,RegExpr,Logs;

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
   Logs:Tlogs;
begin
      result:=false;
      PiDPath:='/etc/artica-postfix/artica-process2.pid';
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

     Logs:=Tlogs.Create;
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


var P2:Tprocess2;
begin
  if not TestAnCreatePid() then halt(0);

   P2:=Tprocess2.Create;
   halt(0);


end.

