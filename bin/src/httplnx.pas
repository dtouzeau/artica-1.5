program httpserv;

{$mode objfpc}{$H+}

uses
  cthreads,custapp, Classes, SysUtils, fpcunit,http;

type

  MyWWW = class(TCustomApplication)
  private
         sHTTP:TTCPHttpDaemon;
  protected
    procedure DoRun; override;
  public
  Constructor Create;
  end;

constructor MyWWW.create;
begin
     writeln('create daemon');
    sHTTP:=TTCPHttpDaemon.create;
    sHTTP.Execute;
end;


  procedure MyWWW.DoRun;
  var
    I: integer;
    S: string;
  begin

  //  Terminate;
  end;


var
  App: MyWWW;

begin
  App := MyWWW.Create;
  App.Initialize;
  App.Title := 'www Console Test Case';

  App.Run;
  App.Free;
end.


