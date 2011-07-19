program interface2;

{$mode objfpc}{$H+}

uses
  {$IFDEF UNIX}{$IFDEF UseCThreads}
  cthreads,
  {$ENDIF}{$ENDIF}
  Classes,sysutils,Unix
  { you can add units after this };

begin


   if Not FileExists('/etc/artica-postfix/first.boot.install') then begin
      fpsystem('/etc/init.d/artica-postfix stop');
      fpsystem('/etc/init.d/artica-postfix start');
      fpsystem('/bin/touch /etc/artica-postfix/first.boot.install');
   end;

fpsystem('/etc/init.d/artica-postfix start apache');
   
   if FileExists('/usr/bin/iceweasel') then begin
       fpsystem('/usr/bin/iceweasel http://127.0.0.1:47980/index.php &');
       halt(0);
   end;

fpsystem('iceape http://127.0.0.1:47980/index.php &');
halt(0);

end.

