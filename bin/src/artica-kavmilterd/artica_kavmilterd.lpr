program artica_kavmilterd;

{$mode objfpc}{$H+}

uses
  {$IFDEF UNIX}{$IFDEF UseCThreads}
  cthreads,
  {$ENDIF}{$ENDIF}
  Classes,ldap,logs
  { add your units here }, kavmilterd;
  
  
 var
    kavmilter:Tkavmilterd;
    xlogs:Tlogs;

begin

xlogs:=Tlogs.Create;
kavmilter:=Tkavmilterd.Create();

       if ParamStr(1)='-infos' then begin
           xlogs.logs('starting "-infos"');
           kavmilter.infos();
           halt(0);
       end;

      if ParamStr(1)='-save' then begin
         xlogs.logs('starting "-save"');
           kavmilter.SaveToDisk();
           halt(0);
       end;


writeln('artica-kavmilterd usage :');
writeln('-infos.................................: Get config infos');
writeln('-save..................................: Save config from ldap to disk');




halt(0);

end.

