program samplecgi;

{$mode objfpc}{$H+}

uses
  cgiModules, wmdump;

begin
  Application.Title:='artica-cgi-application';
  Application:=TModuledCGIApplication.Create(nil);
  Application.Initialize;
  Application.CreateForm(TDemoModule, DemoModule);
  Application.Run;
end.

