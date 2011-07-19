unit confiles;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas';

  type
  TConfFiles=class


private
       ReadFile:string;
       FileDatas:TStringList;
       xPath:string;
       function XReadFile(FilePath:string):string;

public
      function GetValue(value_name:string):string;
      function EditValue(const value_name:string;const value_datas:string):string;
      constructor Create(filePath:string);
      procedure Free;

END;

implementation

constructor TConfFiles.Create(filePath:string);
begin
   xPath:=filePath;
   FileDatas:=TStringList.Create;
   if FileExists(filePath) then begin
   //writeln('ConfFiles.GetValue::',filePath);
   
      FileDatas.LoadFromFile(filePath);
   end
   else begin
       // writeln('ConfFiles.GetValue::Error there is no xPath');
   end;
   
   
end;

procedure TConfFiles.Free();
begin
   FileDatas.Free;
end;


function TConfFiles.GetValue(value_name:string):string;
var
RegExpr:TRegExpr;
   datas:string;
   Index:integer;
begin
   if length(xPath)=0 then begin
      writeln('ConfFiles.GetValue::Error there is no xPath');
   end;

   RegExpr:=TRegExpr.create;
   //writeln('analyse ... ',FileDatas.Count);
   RegExpr.expression:=value_name + '([="\s+]+)([a-z0-9\.-_]+)(["|\s+])';
   for Index := 0 to FileDatas.Count - 1 do  begin

       if  RegExpr.Exec(FileDatas.Strings[Index]) then begin
              //writeln('ConfFiles.GetValue:: Found ->  ' +  RegExpr.Match[2]);
              result:=RegExpr.Match[2];
              RegExpr.Free;
              exit;
       end;
   end;

   RegExpr.Free;

end;
function TConfFiles.EditValue(const value_name:string;const value_datas:string):string;
var
   RegExpr:TRegExpr;
   datas:string;
   Index:integer;
   replaced:string;
begin

   if not FileExists(xPath) then exit();
   RegExpr:=TRegExpr.create;
   RegExpr.expression:=value_name + '([="\s+]+)([a-z0-9\.-_]+)(["|\s+])';
   for Index := 0 to FileDatas.Count - 1 do  begin
       if  RegExpr.Exec(FileDatas.Strings[Index]) then begin
           FileDatas.Strings[Index]:=RegExpr.Replace(FileDatas.Strings[Index],value_name +'$1' + value_datas + '$3',true);
              RegExpr.Free;
              FileDatas.SaveToFile(xPath);
              exit;
       end;
   end;

   RegExpr.Free;

end;





function TConfFiles.XReadFile(FilePath:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:text;
   i:integer;
   datas:string;
   datas_file:string;
begin

      if not FileExists(FilePath) then begin
         exit;

      end;
      TRY
     assign(Afile,FilePath);
     reset(Afile);
     while not EOF(Afile) do
           begin
           readln(Afile,datas);
           
           datas_file:=datas_file + datas +CRLF;
           end;

close(Afile);
             EXCEPT

           end;
result:=datas_file;
end;



end.

