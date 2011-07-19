unit mysql;
//     {$MODE DELPHI}
{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,mysql4,Process,strutils,IniFiles,logs,RegExpr in 'RegExpr.pas';


  type
  Fmysql=class

private
      sock : PMYSQL;
      qmysql : TMYSQL;
      qbuf : string [160];
      mquery : string;
      alloc : PMYSQL;
      LOGS:Tlogs;
      GLOBAL_INI:TIniFile;
      sql_server,sql_password,sql_database,sql_user:string;
      host, user, passwd, Query,database : Pchar;

      D:boolean;

      function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
      FUNCTION DeleteQuarantineFile(ID:string;Path:string):boolean;
      function rm(path:string):boolean;
public
      rowbuf : MYSQL_ROW;
      recbuf : PMYSQL_RES;
      FUNCTION Connect():boolean;
      constructor Create;
      FUNCTION QUERY_SQL(sql:string):boolean;
      FUNCTION QUERY_SQL_ROWS(sql:string):boolean;
      procedure DeleteOldQuarantineDays(maxday:string;ou:string);
      function DatabaseExists():boolean;
      function CreateDatabase(DatabaseName:String):boolean;
      FUNCTION ifTableExits(table:string):boolean;
      destructor Free();

END;

implementation

constructor Fmysql.Create;
var dummy, dummy1, dummy2, dummy3:string;
begin
     D:=COMMANDLINE_PARAMETERS('-V');
     forcedirectories('/etc/artica-postfix');
     GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-mysql.conf');
     dummy:=GLOBAL_INI.ReadString('MYSQL','mysql_server','');
     dummy1:=GLOBAL_INI.ReadString('MYSQL','database_admin','root');
     dummy2:=GLOBAL_INI.ReadString('MYSQL','database_password','');
     dummy3:=GLOBAL_INI.ReadString('MYSQL','database_name','artica_filter');
     if dummy='' then begin
         if D then writeln('No mysql server define...according 127.0.0.1 for server');
         dummy:='127.0.0.1';
     end;

     LOGS:=TLogs.Create;
     host:=@dummy[1];
     user:=@dummy1[1];
     passwd:=@dummy2[1];
     database:=@dummy3[1];


end;

destructor Fmysql.Free();
begin
    GLOBAL_INI.Free;
    LOGS.Free;
end;

//##############################################################################

function Fmysql.Connect():boolean;
begin
alloc := mysql_init(PMYSQL(@qmysql));
if length(trim(passwd))=0 then begin
   sock :=  mysql_real_connect(alloc, host, user, nil, nil, 0, nil, 0);
end else begin
   sock :=  mysql_real_connect(alloc, host, user, passwd, nil, 0, nil, 0);
end;

  if sock=Nil then
    begin
       LOGS.LOGS('artica-sql:: Connect:: Couldn''t connect to MySQL');
       if D then writeln('Couldn''t connect to MySQL.');
       LOGS.LOGS('artica-sql:: Connect:: Error was: '+ StrPas(mysql_error(@qmysql)));
      if D then writeln('Error was: '+ StrPas(mysql_error(@qmysql)));
      exit(false);
   end;
  exit(true);

end;
//##############################################################################
function Fmysql.DatabaseExists():boolean;

var sql_results:longint;
begin
   sql_results:=mysql_select_db(sock,database) ;

   if sql_results=1 then begin
   LOGS.LOGS('artica-sql:: DatabaseExists:: Couldnt select database ->' +database);
   LOGS.LOGS('artica-sql:: DatabaseExists:: '+ mysql_error(sock));
   if D then writeln(mysql_error(sock));
   exit(false);
   end;



exit(true);

end;
//##############################################################################
function FMysql.CreateDatabase(DatabaseName:String):boolean;
var
   sql_results:longint;
   sql:string;
begin
    sql:='CREATE DATABASE `' + DatabaseName + '`';
    sql_results:=mysql_query(alloc, pChar(sql));
    if sql_results=1 then begin
       if D then  writeln('mysql_select_db-> unable to check database ' + database  + ' error number : ',sql_results);
        LOGS.LOGS('artica-sql:: QUERY_SQL:: '+ mysql_error(sock));
        if D then Writeln (mysql_error(sock));
        exit(false);
     end;
     
    exit(true);
     
end;



function Fmysql.ifTableExits(table:string):boolean;
     var res:longint;
begin

            table:='SHOW TABLES LIKE ''' + table + ''';';
            mysql_query(alloc,Pchar(table));
            recbuf := mysql_store_result(alloc);
            res:=mysql_num_rows(recbuf);
            writeln('testing table '+ table + ' ->',res);
            if res=0 then exit(false);
            exit(true);

end;
//##############################################################################

FUNCTION Fmysql.QUERY_SQL(sql:string):boolean;

var
   sql_results:longint;
begin
     result:=false;
     if not D then if COMMANDLINE_PARAMETERS('setup') then D:=true;
     
     
     if not DatabaseExists then exit;
     sql_results:=mysql_query(alloc, pChar(sql));
     if sql_results=1 then begin
       if D then  writeln('mysql_select_db-> unable to check database ' + database  + ' error number : ',sql_results);
        LOGS.LOGS('artica-sql:: QUERY_SQL:: '+ mysql_error(sock));
        if D then Writeln (mysql_error(sock));
        exit(false);
     end;


 exit(true);
end;
//##############################################################################
procedure Fmysql.DeleteOldQuarantineDays(maxday:string;ou:string);
var
   RegExpr:TRegExpr;
   FileDatas:TstringList;
   sql:string;
   Count:integer;
   message_path:string;
   ID:string;
   
begin
   sql:='SELECT ID,zDate,message_path,DATEDIFF(DATE_ADD(received_date, INTERVAL ' + maxday + ' DAY ),NOW()) as newdate FROM messages WHERE Deleted=0 AND filter_action=''quarantine'' AND ou=''' + ou + '''';
    if not QUERY_SQL_ROWS(sql) then exit;
    Count:=0;
    Try
 while (rowbuf <>nil) do begin
     inc(Count);
     ID:=rowbuf[0];
     message_path:=rowbuf[2];
     DeleteQuarantineFile(ID,message_path);
     rowbuf:= mysql_fetch_row(recbuf);
 end;
 Except
    logs.logs('artica-sql:: FATAL ERROR in DeleteOldQuarantineDays in loop function after ' + IntToStr(Count) + ' th cycle..' );
 end;
   mysql_free_result (recbuf);
end;
//##############################################################################
FUNCTION Fmysql.DeleteQuarantineFile(ID:string;Path:string):boolean;
var sql:string;
begin
result:=false;

  if rm(Path) then begin
     if D then logs.logs('artica-sql::DeleteQuarantineFile:: File=' + Path + ' ID=' + ID);
      sql:='UPDATE messages SET Deleted=1 WHERE ID=' + ID;
      result:=QUERY_SQL(sql);
      if not result then logs.logs('artica-sql:: DeleteQuarantineFile failed to query "' + sql + '"');
  end;


end;
//##############################################################################
function Fmysql.rm(path:string):boolean;
Var F : Text;
begin
   if Not FileExists(path) then exit(true);

   Try
   Assign (F,Path);
   Erase(F);
   Except
   result:=false;
   end;
End;
//##############################################################################


FUNCTION Fmysql.QUERY_SQL_ROWS(sql:string):boolean;

var
   sql_results:longint;
   Fieldsnum:integer;
   rowsnum:integer;
   count:integer;
   i,t:integer;
   name:Pchar;
begin
     result:=false;
     if D then writeln('Fmysql.QUERY_SQL:: Check database: ',database);
     if not DatabaseExists then exit;

     sql_results:=mysql_query(alloc, pChar(sql));
     if sql_results=1 then begin
       if D then  writeln('mysql_select_db->error number : ',sql_results);
        LOGS.LOGS('artica-sql:: QUERY_SQL:: '+ mysql_error(sock));
        if D then Writeln (mysql_error(sock));
        exit();
     end;
     
     
     recbuf := mysql_store_result(alloc);
     if RecBuf=Nil then begin
        if D then  writeln('QUERY_SQL_ROWS->error number : ',sql_results);
        if D then  writeln('Query returned nil result');;
     end;
        Fieldsnum:=mysql_num_fields(recbuf);
        rowsnum:=mysql_num_rows (recbuf);
        if D then Writeln ('Number of records returned  : ',Fieldsnum);
        if D then Writeln ('Number of fields returned  : ',rowsnum);



 rowbuf := mysql_fetch_row(recbuf);
 result:=true;

end;

//##############################################################################
function Fmysql.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 result:=false;
 if ParamCount>0 then begin
     for i:=0 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:=FoundWhatPattern;
   if RegExpr.Exec(s) then begin
      RegExpr.Free;
      result:=True;
   end;


end;
//##############################################################################






end.

