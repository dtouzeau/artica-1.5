program post_conf;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,TPostmysql;

var
   toto:string;
   potfixmsql:postfix_mysql;
 begin
     potfixmsql:=postfix_mysql.Create();

if Trim(ParamStr(1))='' then begin
   writeln('Starting installation of Artica for Postfix Mail system...');

   if potfixmsql.Testing_system()=false then exit();
   potfixmsql.install_inc();
   potfixmsql.InstallArticaDaemon();
end;


if Trim(ParamStr(1))='-inc' then begin
  writeln('Starting configure of Artica for Postfix Mail system...');
  potfixmsql.install_inc();
  potfixmsql.Install_web_settings();
  exit();
end;

if Trim(ParamStr(1))='-www' then begin
  writeln('Starting configure of Artica for Postfix Mail system...');
  potfixmsql.Install_web_settings();
  exit();
end;

if Trim(ParamStr(1))='-reconfigure-postfix-mysql' then begin
  writeln('Starting reconfigure Postfix Mail system for Mysql database...');
  potfixmsql.BuildPostFixMysqlFiles();
  exit();
end;



if Trim(ParamStr(1))='--help' then begin
 writeln('usage:');
 writeln('Full install: "./install"');
 writeln('Install only settings for artica daemon/web: "./install -inc"');
 writeln('reconfigure postfix mysql settings : "./install -reconfigure-postfix-mysql"');
 writeln('Install only settings for artica web: "./install -www" ');
end;

end.

