unit builder;

{$mode objfpc}{$H+}

interface

uses
//depreciated oldlinux -> baseunix
Classes, SysUtils,variants,strutils, Process,IniFiles,baseunix,unix,md5,RegExpr in 'RegExpr.pas';

  type
  tbuilder=class


private
       FilesList:TstringList;

public
    constructor Create;
    procedure Free;
    function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
    function ExecPipe(commandline:string):string;
end;

implementation

//-------------------------------------------------------------------------------------------------------


//##############################################################################
constructor tbuilder.Create;

begin



end;
//##############################################################################
PROCEDURE tbuilder.Free();
begin


end;
//##############################################################################
function tbuilder.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:=false;
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:=FoundWhatPattern;
   if RegExpr.Exec(s) then begin
      RegExpr.Free;
      result:=True;
   end;


end;
//##############################################################################
function tbuilder.ExecPipe(commandline:string):string;
const
  READ_BYTES = 2048;
  CR = #$0d;
  LF = #$0a;
  CRLF = CR + LF;

var
  S: TStringList;
  M: TMemoryStream;
  P: TProcess;
  n: LongInt;
  BytesRead: LongInt;
  xRes:string;

begin
  // writeln(commandline);
  if length(trim(commandline))=0 then exit;
  M := TMemoryStream.Create;
  xRes:='';
  BytesRead := 0;
  P := TProcess.Create(nil);
  P.CommandLine := commandline;
  P.Options := [poUsePipes];


  P.Execute;
  while P.Running do begin
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end
    else begin
      Sleep(100);
    end;

  end;

  repeat
    M.SetSize(BytesRead + READ_BYTES);
    n := P.Output.Read((M.Memory + BytesRead)^, READ_BYTES);
    if n > 0 then begin
      Inc(BytesRead, n);
    end;
  until n <= 0;
  M.SetSize(BytesRead);
  S := TStringList.Create;
  S.LoadFromStream(M);

  for n := 0 to S.Count - 1 do
  begin
    if length(S[n])>1 then begin

      xRes:=xRes + S[n] +CRLF;
    end;
  end;

  S.Free;
  P.Free;
  M.Free;
  exit( xRes);
end;
//##############################################################################
procedure BuildArticaPostfixSpec
var spec:TstringList;
begin

spec.Add('Buildroot: /home/dtouzeau/Bureau/artica-compile/rpm/artica-postfix-1.1.020920');
spec.Add('Name: artica-postfix');
spec.Add('Version: 1.1.020920');
spec.Add('Release: 2');
spec.Add('Summary: Web HTTPS interface for postfix administration.automatically install all Open Source eMail products within technical skills.Full features for postfix,cyrus,Openldap,mailman,fetchmail,users management with all securities features (anti-spam/rbl..).');
spec.Add('License: see /usr/share/doc/artica-postfix/copyright');
spec.Add('Distribution: Debian');
spec.Add('Group: Converted/base');
spec.Add('AutoReq: 0');
spec.Add('');
spec.Add('%define _rpmdir ../');
spec.Add('%define _rpmfilename %%{NAME}-%%{VERSION}-%%{RELEASE}.%%{ARCH}.rpm');
spec.Add('%define _unpackaged_files_terminate_build 0');
spec.Add('%define _use_internal_dependency_generator 0');
spec.Add('');
spec.Add('');
spec.Add('%post');
spec.Add('#!/bin/sh -e');
spec.Add('# postinst script for artica-postfix');
spec.Add('#');
spec.Add('');
spec.Add('/usr/share/artica-postifx/bin/artica-install -init-artica');
spec.Add('');
spec.Add('%postun');
spec.Add('#!/bin/sh -e');
spec.Add('# postrm script for artica-postfix');
spec.Add('#');
spec.Add('');
spec.Add('if [ -x "/etc/init.d/artica-postfix" ]; then');
spec.Add('        if [ -x "`which invoke-rc.d 2>/dev/null`" ]; then');
spec.Add('                invoke-rc.d artica-postfix stop || exit 0');
spec.Add('        else');
spec.Add('                /etc/init.d/artica-postfix stop || exit 0');
spec.Add('        fi');
spec.Add('fi');
spec.Add('');
spec.Add('');
spec.Add('%description');
spec.Add('');
spec.Add('');
spec.Add('(artica-postfix see http://www.artica.fr)');
spec.Add('');
spec.Add('%files');


end;




end.

