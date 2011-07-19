program artica_dbf;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,artica_db_sqlite;

var

dbx:artica_sqlite;
res:boolean;


begin
   dbx:=artica_sqlite.Create;
   
   
   if ParamStr(1)='-install' then begin
      dbx.CREATE_table_messages();
      dbx.CreateDatabaseRBL();
      dbx.UpgradeTableMessages();
      halt(0);
   end;
   
   if ParamStr(1)='-upgrade' then begin
      dbx.UpgradeTableMessages();
      dbx.CreateDatabaseRBL();
      halt(0);
   end;
   
 if ParamStr(1)='-iftable' then begin
      if ParamStr(3)='mail' then res:=dbx.TableExistsG(ParamStr(2),'/usr/share/artica-postfix/LocalDatabases/artica_database.db');
      if ParamStr(3)='rbl' then res:=dbx.TableExistsG(ParamStr(2),'/usr/share/artica-postfix/LocalDatabases/rbl_database.db');
      if res then writeln('TRUE') else writeln('FALSE');
      halt(0);
   end;

   
   
   if  ParamStr(1)='--help' then begin
       writeln('-install : Create database and tables');
       writeln('-upgrade : upgrade tables and database');
       writeln('-iftable [table name] (database name) :Test if table exists on "mail" or "rbl" database');
       
   
   end;
      
   if ParamStr(1)='-dump-failed' then dbx.Dump_ServersFailed();
   if ParamStr(1)='-scan-failed' then dbx.MAILLOG_SCAN_FAILED_SERRVERS();
   if ParamStr(1)='-lib-ver' then dbx.LIB_VERSION();
   if ParamStr(1)='-create-mess' then dbx.Create_table_messages();
   if ParamStr(1)='-isfield' then if not dbx.IfChampsMessagesExists(ParamStr(2)) then writeln('No') else writeln('Yes');
   halt(0);


end.

