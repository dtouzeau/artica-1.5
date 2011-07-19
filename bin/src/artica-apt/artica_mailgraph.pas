program artica_mailgraph;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils
  { you can add units after this }, mailgraph_class;
  
  var mailg:Tmailgraph;

begin

  mailg:=Tmailgraph.Create;
  mailg.Build_binary();
  mailg.mailgraphcgi();
  halt(0);

end.

