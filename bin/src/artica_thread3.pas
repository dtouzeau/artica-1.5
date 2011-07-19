program artica_thread3;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils, process3,oldlinux,RegExpr,logs;
  
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
   logs:Tlogs;
begin
     if ParamStr(1)='-V' then D:=true;
      result:=false;
      logs:=Tlogs.Create;
      PiDPath:='/etc/artica-postfix/artica-process3.pid';
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

     logs.logs('Process3: ' +PidString);
     if FileExists('/proc/' + PidString + '/exe') then begin
             if D then writeln('Process3: ' +PidString + ' -> exists stopping');
             logs.logs('Process3: ' +PidString + ' -> exists');
        exit();
     end;

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
  
  
var P2:Tprocess3;
begin

   if ParamStr(1)='-V' then writeln('Creating process3..debug mode');
   if not TestAnCreatePid then halt(0);

   P2:=Tprocess3.Create;
   if ParamStr(1)='-V' then writeln('End....');
   
   if ParamStr(1)='keep' then readln();
   halt(0);
end.

