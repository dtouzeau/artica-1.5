program installsql;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,TPostmysql;

var
   toto:string;
   potfixmsql:postfix_mysql;
 begin
     potfixmsql:=postfix_mysql.Create();
     writeln('Starting Mysql create database table for Artica for Postfix Mail system...');
     potfixmsql.install_user();

end.

