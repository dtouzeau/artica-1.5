unit xapian;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',zsystem,samba;

  type
  txapian=class


private
     LOGS:Tlogs;
     D:boolean;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;



public
    procedure   Free;
    constructor Create(const zSYS:Tsystem);
    function  VERSION():string;
    function  OMINDEX_VERSION():string;
    function  PHP_VERSION():string;
    function  APP_CATDOC_VERSION():string;
    function  APP_UNRTF_VERSION():string;
    function  APP_XPDF_VERSION():string;
    function  APP_UNZIP_VERSION():string;
    function  APP_ANTIWORD_VERSION():string;
    function  PHP_SO_PATH():string;



END;

implementation

constructor txapian.Create(const zSYS:Tsystem);
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=zSYS;




       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure txapian.free();
begin
    FreeAndNil(logs);
end;
//##############################################################################
function txapian.VERSION():string;
  var
   RegExpr:TRegExpr;
   x:string;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/xapian-config';
     if not FileExists(path) then begin
        logs.Debuglogs('txapian.VERSION():: xapian is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_XAPIAN');
   if length(result)>0 then exit;
   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' --version >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='\s+([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_XAPIAN',result);
logs.Debuglogs('APP_XAPIAN:: -> ' + result);
end;
//#############################################################################
function txapian.OMINDEX_VERSION():string;
  var
   RegExpr:TRegExpr;
   x:string;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/omindex';
     if not FileExists(path) then begin
        logs.Debuglogs('txapian.OMINDEX_VERSION():: omeindex is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_XAPIAN_OMEGA');
   if length(result)>0 then exit;

     tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -v >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='\s+([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_XAPIAN_OMEGA',result);
logs.Debuglogs('APP_XAPIAN_OMEGA:: -> ' + result);
end;
//#############################################################################
function txapian.PHP_SO_PATH():string;
var
str:string;
begin
    str:=SYS.LOCATE_PHP5_EXTENSION_DIR()+'/xapian.so';
    if FileExists(str) then exit(str);

end;
//#############################################################################

function txapian.PHP_VERSION():string;
  var
   RegExpr:TRegExpr;
   x:string;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:=SYS.LOCATE_PHP5_EXTENSION_DIR()+'/xapian.so';
     if not FileExists(path) then begin
        logs.Debuglogs('txapian.PHP_VERSION():: '+path+' is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_XAPIAN_PHP');
   if length(result)>0 then exit;
   result:=VERSION();
   SYS.SET_CACHE_VERSION('APP_XAPIAN_PHP',result);
   logs.Debuglogs('APP_XAPIAN_PHP:: -> ' + result);
end;
//#############################################################################
function txapian.APP_XPDF_VERSION():string;

  var
   RegExpr:TRegExpr;
   x:string;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/pdfinfo';
     if not FileExists(path) then begin
        logs.Debuglogs('txapian.APP_XPDF_VERSION():: xpdf is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_XPDF');
   if length(result)>0 then exit;

     tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -v >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='\s+([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_XPDF',result);
logs.Debuglogs('APP_XPDF:: -> ' + result);
end;
//#############################################################################
function txapian.APP_UNRTF_VERSION():string;

  var
   RegExpr:TRegExpr;
   x:string;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/unrtf';
     if not FileExists(path) then begin
        logs.Debuglogs('txapian.APP_UNRTF_VERSION():: unrtf is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_UNRTF');
   if length(result)>0 then exit;

   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' --version >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_UNRTF',result);
logs.Debuglogs('APP_UNRTF:: -> ' + result);
end;
//#############################################################################
function txapian.APP_CATDOC_VERSION():string;

  var
   RegExpr:TRegExpr;
   x:string;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/catdoc';
     if not FileExists(path) then begin
        logs.Debuglogs('txapian.APP_CATDOC_VERSION():: catdoc is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_CATDOC');
   if length(result)>0 then exit;

   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -V >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='([0-9\.]+)';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_CATDOC',result);
logs.Debuglogs('APP_CATDOC:: -> ' + result);
end;
//#############################################################################
function txapian.APP_UNZIP_VERSION():string;
begin
result:=SYS.APP_UNZIP_VERSION();
end;
//#############################################################################
function txapian.APP_ANTIWORD_VERSION():string;

  var
   RegExpr:TRegExpr;
   x:string;
   tmpstr:string;
   l:TstringList;
   i:integer;
   path:string;
begin



     path:='/usr/bin/antiword';
     if not FileExists(path) then begin
        logs.Debuglogs('txapian.APP_ANTIWORD_VERSION():: catdoc is not installed');
        exit;
     end;


   result:=SYS.GET_CACHE_VERSION('APP_ANTIWORD');
   if length(result)>0 then exit;

   tmpstr:=logs.FILE_TEMP();
   fpsystem(path+' -v >'+tmpstr+' 2>&1');


     l:=TstringList.Create;
     RegExpr:=TRegExpr.Create;
     l.LoadFromFile(tmpstr);
     RegExpr.Expression:='\s+Version:\s+([0-9\.]+)\s+';
     for i:=0 to l.Count-1 do begin
         if RegExpr.Exec(l.Strings[i]) then begin
            result:=RegExpr.Match[1];
            break;
         end;
     end;
l.Free;
RegExpr.free;
SYS.SET_CACHE_VERSION('APP_ANTIWORD',result);
logs.Debuglogs('APP_ANTIWORD:: -> ' + result);
end;
//#############################################################################



end.
