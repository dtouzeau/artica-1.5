program artica_compile;

{$mode objfpc}{$H+}

uses
  Classes,Linux,baseUnix, SysUtils,zsystem,RegExpr in 'RegExpr.pas',dos,
  compile;

var
   REGEX:TRegExpr;
   s:string;
   list:TstringList;
   i:integer;
   status:boolean;
   SYS:Tsystem;
   comp:Tcompile;
   
function IsRoot():boolean;
begin
if FpGetEUid=0 then exit(true);
exit(false);
end;


 begin
 comp:=Tcompile.Create;
 REGEX:=TRegExpr.Create;
 s:='';

    if ParamStr(1)='--git' then begin
        comp.git();
        halt(0);
    end;
    if ParamStr(1)='--patch' then begin
        comp.createPatch();
        halt(0);
     end;



 if fpgeteuid<>0  then begin
 writeln('You need to be root to execute this program...');
 halt(0);
 end;




      if ParamStr(1)='--lang' then begin
        comp.langues();
        halt(0);
     end;



 if ParamCount>1 then begin
     for i:=2 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);

     end;
 end;


     if ParamStr(1)='--genver' then begin
        writeln(comp.COMPILE_GEN_VERSION());
        halt(0);
     end;
     

     if ParamStr(1)='--setfolder' then begin
        comp.SET_FOLDER(paramStr(2));
        halt(0);
     end;

     if ParamStr(1)='--setuser' then begin
        comp.SET_CurUser(paramStr(2));
        halt(0);
     end;
     
     if ParamStr(1)='--setDestPath' then begin
        comp.SET_FOLDER(paramStr(2));
        halt(0);
     end;

     if ParamStr(1)='--compile' then begin
        comp.COMPILE;
        halt(0);
     end;
     
   if ParamStr(1)='--patchspec' then begin
        comp.PATCHING_SPECFILE(paramStr(2));
        halt(0);
     end;
     
   if ParamStr(1)='--opensecu' then begin
        comp.OPENSECURITY();
        halt(0);
     end;

    if ParamStr(1)='--sync-user' then begin
        comp.SyncUSerBackup();
        halt(0);
     end;





     writeln('--setup.................................: Compile Only setup-*.tgz');
     writeln('--single-tgz............................: only tgz');
     writeln('--genver................................: generate new version');
     writeln('--setDestPath...........................: Set defined source folder');
     writeln('--setfolder.............................: Set defined compiled target folder');
     writeln('--setuser...............................: Set working user (chown features)');                                  
     writeln('--compile...............................: Compile artica (--postfix,)');
     writeln('--patchspec  directory..................: patch spec file stored in folder');
     writeln('--opensecu..............................: Compile artica-postfix opensecurity');
     writeln('--old-cd................................: Compile CD-ROM');
     writeln('--sync-user.............................: sync user-backup libraries');


     writeln('usage : ' + ParamStr(0) + ' --compile (options)');
     
     halt(0);



end.

