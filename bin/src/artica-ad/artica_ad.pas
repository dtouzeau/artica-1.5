program artica_ad;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils
  { you can add units after this }, activedirectory;

  var
     AD:tad;
begin

    AD:=tad.Create();
    
    
    if Paramstr(1)='--test-connection' then begin
       AD.TEST_CONNECTION(Paramstr(2));
       AD.free;
       halt(0);
    end;
    
    
    
writeln('--test-connection organization......................: Testing active Directory connection from organization datas stored in LDAP');

halt(0);

end.

