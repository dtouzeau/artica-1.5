unit logs;

{$mode objfpc}{$H+}

interface

uses
//depreciated oldlinux -> baseunix
Classes, SysUtils,variants,strutils, Process,IniFiles,baseunix,unix,md5,RegExpr in 'RegExpr.pas',systemlog,dateutils;

  type
  Tlogs=class


private
     MaxlogSize:longint;
     D:boolean;
     mem_mysql_port:string;

     function GetFileSizeKo(path:string):longint;
     function GET_INFO(key:string):string;
     function MaxSizeLimit:integer;
     PROCEDURE logsModule(zText:string);
     function MYSQL_PORT():string;
     function MYSQL_MYCNF_PATH:string;
     function MYSQL_SERVER_PARAMETERS_CF(key:string):string;
     function MYSQL_READ_CF(key:string;mycfpath:string):string;
     function MYSQL_EXEC_BIN_PATH():string;
     function FILE_TIME_BETWEEN_MIN(filepath:string):LongInt;
     function SearchAndReplace(sSrc, sLookFor, sReplaceWith: string ): string;
     function SYSTEM_FQDN():string;
     function ReadFileIntoString(path:string):string;
     function LOCATE_PHP5_BIN():string;



public
    Enable_echo:boolean;
    Enable_echo_install:boolean;
    Debug:boolean;
    module_name:string;

    constructor Create;
    procedure Free;
    procedure logs(zText:string);
    PROCEDURE logsInstall(zText:string);
    PROCEDURE logsPostfix(zText:string);
    function GetFileSizeMo(path:string):longint;
    function MD5FromString(value:string):string;
    PROCEDURE Debuglogs(zText:string);
    PROCEDURE logsStart(zText:string);



    function FormatHeure (value : Int64) : String;
    procedure DeleteLogs();
    function   GetFileBytes(path:string):longint;
    PROCEDURE logsThread(ThreadName:string;zText:string);
    PROCEDURE ERRORS(zText:string);
    PROCEDURE RemoveFilesAndDirectories(path:string;pattern:string);
    PROCEDURE INSTALL_MODULES(application_name:string;zText:string);
    PROCEDURE Syslogs(text:string);
    function COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
    FUNCTION TRANSFORM_DATE_MONTH(zText:string):string;

    function getyear():string;

    FUNCTION QUERY_SQL(sql:Pchar;database:string):boolean;
    function QUERY_SQL_BIN(database:string;fileName:string):boolean;
    function EXECUTE_SQL_FILE(filenname:string;database:string;defaultcharset:string=''):boolean;
    function LIST_MYSQL_DATABASES():TstringList;
    function EXECUTE_SQL_STRING(query:string):string;
    function IF_DATABASE_EXISTS(database_name:string):boolean;
    function IF_TABLE_EXISTS(table:string;database:string):boolean;
    function SYS_EVENTS_ROWNUM():integer;
    function DateTimeNowSQL():string;
    function WriteToFile(zText:string;TargetPath:string):boolean;
    procedure DeleteFile(TargetPath:string);
    function ReadFromFile(TargetPath:string):string;
    PROCEDURE Output(zText:string;icon_type:string='info');


    procedure OutputCmd(command:string;realoutput:boolean=false);
    function OutputCmdR(command:string):string;

    procedure set_INFOS(key:string;val:string);
    function FILE_TEMP():string;
    PROCEDURE nmap(zText:string);
    function TABLE_ROWNUM(tablename:string;database:string):integer;
    procedure WriteInstallLogs(text:string);
    function GetAsSQLText(MessageToTranslate:string) : string;
    procedure NOTIFICATION(subject:string;content:string;context:string);
    function DateTimeDiff(Start, Stop : TDateTime) : int64;
    function INSTALL_STATUS(APP_NAME:string;POURC:integer):string;
    procedure LogGeneric(text:string;path:string);
    function MYSQL_INFOS(val:string):string;
    function INSTALL_PROGRESS(APP_NAME:string;info:string):string;
    PROCEDURE EVENTS(subject:string;text:string;context:string;filePath:string);
    PROCEDURE BACKUP_EVENTS(text:string;localsource:string;remote_source:string;success:integer);
    function copyfile(srcfn, destfn:string):boolean;
    PROCEDURE commandlog();
    function FileTimeName():string;
end;

implementation

//-------------------------------------------------------------------------------------------------------


//##############################################################################
constructor Tlogs.Create;

begin
       forcedirectories('/etc/artica-postfix');
       Enable_echo:=false;
       MaxlogSize:=100;
       D:=COMMANDLINE_PARAMETERS('-V');
end;
//##############################################################################
PROCEDURE Tlogs.Free();
begin

end;
//##############################################################################
PROCEDURE Tlogs.EVENTS(subject:string;text:string;context:string;filePath:string);
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   zdate:string;
   zDateSubject:string;
   ini:Tinifile;
   filename:string;
   bigtext:string;
begin
   zdate:=FormatDateTime('yyyy-mm-dd hh:nn:ss', Now);
   zDateSubject:=FormatDateTime('hh:nn:ss', Now);
   filename:='/var/log/artica-postfix/events/'+MD5FromString(context+filePath+subject);

   if FIleExists(filename) then exit;



   ForceDirectories('/var/log/artica-postfix/events');
   ini:=Tinifile.Create(filename);
   ini.WriteString('LOG','processname',ExtractFileName(ParamStr(0)));
   ini.WriteString('LOG','date',zdate);
   ini.WriteString('LOG','context',context);
   ini.WriteString('LOG','subject','['+zDateSubject+']: '+subject);
   ini.WriteString('LOG','filePath',filePath);
   ini.UpdateFile;
   ini.Free;

   bigtext:=ReadFileIntoString(filename);
   bigtext:=bigtext+CRLF+'<text>'+text+'</text>'+CRLF;
   WriteToFile(bigtext,filename);



end;
//##############################################################################



PROCEDURE Tlogs.logsInstall(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        maintenant : Tsystemtime;
        processname:string;
      BEGIN
        if Enable_echo=True then writeln(zText);
        if Enable_echo_install then writeln(zText);
        TargetPath:='/var/log/artica-postfix/artica-install.log';
        processname:=ExtractFileName(ParamStr(0));
        forcedirectories('/var/log/artica-postfix');
        getlocaltime(maintenant);zDate := FormatHeure(maintenant.Year)+'-' +FormatHeure(maintenant.Month)+ '-' + FormatHeure(maintenant.Day)+ chr(32)+FormatHeure(maintenant.Hour)+':'+FormatHeure(maintenant.minute)+':'+ FormatHeure(maintenant.second);
        xText:=zDate + ' [' + processname + '] ' + zText;

        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//#############################################################################
PROCEDURE Tlogs.logsPostfix(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        processname:string;
        info : stat;
        maintenant : Tsystemtime;
      BEGIN
        if Enable_echo=True then writeln(zText);
        if Enable_echo_install then writeln(zText);
        TargetPath:='/var/log/artica-postfix/postfix.log';
        processname:=ExtractFileName(ParamStr(0));
        
        
        
        forcedirectories('/var/log/artica-postfix');
        getlocaltime(maintenant);zDate := FormatHeure(maintenant.Year)+'-' +FormatHeure(maintenant.Month)+ '-' + FormatHeure(maintenant.Day)+ chr(32)+FormatHeure(maintenant.Hour)+':'+FormatHeure(maintenant.minute)+':'+ FormatHeure(maintenant.second);
        xText:=zDate + ' ' +processname + ' ' + zText;

        

        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//#############################################################################


PROCEDURE Tlogs.ERRORS(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        maintenant : Tsystemtime;
      BEGIN
        if Enable_echo=True then writeln(zText);
        if Enable_echo_install then writeln(zText);
        TargetPath:='/var/log/artica-postfix/artica-errors.log';

        forcedirectories('/var/log/artica-postfix');
        getlocaltime(maintenant);zDate := FormatHeure(maintenant.Year)+'-' +FormatHeure(maintenant.Month)+ '-' + FormatHeure(maintenant.Day)+ chr(32)+FormatHeure(maintenant.Hour)+':'+FormatHeure(maintenant.minute)+':'+ FormatHeure(maintenant.second);
        xText:=zDate + ' ' + zText;

        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//#############################################################################
PROCEDURE tlogs.BACKUP_EVENTS(text:string;localsource:string;remote_source:string;success:integer);
var sql:string;
begin
   sql:='INSERT INTO cyrus_backup_events(`zDate`,`local_ressource`, `events`,`remote_ressource` ,`success`) VALUES("'+DateTimeNowSQL()+'","'+localsource+'","'+GetAsSQLText(text)+'","'+remote_source+'","'+IntToStr(success)+'");';
   QUERY_SQL(Pchar(sql),'artica_events');
end;
//#############################################################################
PROCEDURE Tlogs.INSTALL_MODULES(application_name:string;zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        maintenant : Tsystemtime;

      BEGIN
        D:=COMMANDLINE_PARAMETERS('-verbose');
        if not D then D:=COMMANDLINE_PARAMETERS('setup');
        if not D then D:=COMMANDLINE_PARAMETERS('-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-perl-upgrade');
        if not D then D:=COMMANDLINE_PARAMETERS('addons');
        if not D then D:=COMMANDLINE_PARAMETERS('-web-configure');
        if not D then D:=COMMANDLINE_PARAMETERS('-kav-proxy');
        if not D then D:=COMMANDLINE_PARAMETERS('-install-web-artica');
        if not D then D:=COMMANDLINE_PARAMETERS('-init-postfix');
        if not D then D:=COMMANDLINE_PARAMETERS('-init-cyrus');
        if not D then D:=COMMANDLINE_PARAMETERS('-artica-web-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-php-mysql');
        if not D then D:=COMMANDLINE_PARAMETERS('-php5');
        if not D then D:=COMMANDLINE_PARAMETERS('-mysql-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-mysql-reconfigure');
        if not D then D:=COMMANDLINE_PARAMETERS('-roundcube');
        if not D then D:=COMMANDLINE_PARAMETERS('-squid-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-squid-configure');
        if not D then D:=COMMANDLINE_PARAMETERS('linux-net-dev');
        if not D then D:=COMMANDLINE_PARAMETERS('-squid-security');
        if not D then D:=COMMANDLINE_PARAMETERS('-pure-ftpd');
        if not D then D:=COMMANDLINE_PARAMETERS('-perl-addons');
        if not D then D:=COMMANDLINE_PARAMETERS('-curl-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-perl-db-file');
        if not D then D:=COMMANDLINE_PARAMETERS('-amavis-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-amavisd-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-init-amavis');
        if not D then D:=COMMANDLINE_PARAMETERS('-amavis-sql-reconfigure');
        if not D then D:=COMMANDLINE_PARAMETERS('-amavis-sql-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-amavis-sql-configure');
        if not D then D:=COMMANDLINE_PARAMETERS('-install-perl-cyrus');
        if not D then D:=COMMANDLINE_PARAMETERS('-mailutils-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-mailfromd-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-cyrus-imap-install');
        if not D then D:=COMMANDLINE_PARAMETERS('-lighttp');
        if not D then D:=COMMANDLINE_PARAMETERS('-ligphp5');
        if not D then D:=COMMANDLINE_PARAMETERS('-mhonarc-install');
        if not D then D:=COMMANDLINE_PARAMETERS('--init-from-repos');
        


        WriteInstallLogs(zText);
        logs(zText);
        logsInstall('[' + application_name + '] ' + ztext);
        TargetPath:='/var/log/artica-postfix/artica-install-' + application_name + '.log';
        logs(zText);
        Debuglogs(zText);
        if COMMANDLINE_PARAMETERS('--verbose') then begin
           writeln(ztext);
        end else begin
            if COMMANDLINE_PARAMETERS('--screen') then writeln(ztext);
        end;


        if D then writeln(zText);
        forcedirectories('/var/log/artica-postfix');
        getlocaltime(maintenant);zDate := FormatHeure(maintenant.Year)+'-' +FormatHeure(maintenant.Month)+ '-' + FormatHeure(maintenant.Day)+ chr(32)+FormatHeure(maintenant.Hour)+':'+FormatHeure(maintenant.minute)+':'+ FormatHeure(maintenant.second);
        xText:=zDate + ' ' + zText;

        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
            CloseFile(myFile);
            xText:='';
        EXCEPT
             writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//#############################################################################



//##############################################################################
function Tlogs.getyear():string;
var
   maintenant : Tsystemtime;
begin
   getlocaltime(maintenant);
   result:=FormatHeure(maintenant.Year);
end;
//##############################################################################
function Tlogs.DateTimeNowSQL():string;
begin
   result:=FormatDateTime('yyyy-mm-dd hh:nn:ss', Now)
end;
//##############################################################################
function Tlogs.FileTimeName():string;
begin

   result:=FormatDateTime('yyyy-mm-dd hh:nn:ss', Now);
   result:=AnsiReplaceText(result,' ','_');
   result:=AnsiReplaceText(result,':','-');
end;
//##############################################################################


PROCEDURE Tlogs.RemoveFilesAndDirectories(path:string;pattern:string);
var
   l:TstringList;
   i:integer;
begin

if length(path)=0 then exit;
if length(pattern)=0 then pattern:='/*';
if pattern='*' then pattern:='/*';

l:=TstringList.Create;
l.Add('/');
l.Add('/usr');
l.Add('/usr/share');
l.Add('/usr/sbin');
l.Add('/sbin');
l.Add('/usr/local');
l.Add('/home');
l.Add('/home/dtouzeau');
l.Add('/usr/share/artica-postfix');
l.add('/var');
l.add('/etc');
l.add('/var/lib');
l.add('/proc');
l.add('/bin');
l.Add('/lib');

for i:=0 to l.Count-1 do begin
    if l.Strings[i]=path then begin
       Syslogs('Security warning, could not delete Directory "' +path+'"');
       exit;
    end;

end;

l.free;
Debuglogs('Removing '+path+pattern);
Outputcmd('/bin/rm -rf '+ path+pattern);

end;
//##############################################################################





PROCEDURE Tlogs.logsThread(ThreadName:string;zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        maintenant : Tsystemtime;
      BEGIN

        TargetPath:='/var/log/artica-postfix/artica-thread-' + ThreadName + '.log';

        forcedirectories('/var/log/artica-postfix');
        getlocaltime(maintenant);
        zDate := DateTimeNowSQL();

        if length(module_name)>0 then logsModule(zText);
        xText:=zDate + ' ' + zText;


        if D=True then writeln(zText);


        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################
PROCEDURE Tlogs.Output(zText:string;icon_type:string);
var img:string;
   begin
      if icon_type='info' then img:='icon_mini_info.gif';
      if icon_type='error' then img:='icon_mini_off.gif';
      if icon_type='ok' then img:='icon_mini_off.gif';
         writeln(ztext);

      
   
   end;
//##############################################################################
PROCEDURE Tlogs.OutputCmd(command:string;realoutput:boolean);
var
   tmp:string;
   cmd:string;
   i:Integer;
   l:TstringList;
   r:Tstringlist;
begin
   tmp:=FILE_TEMP();

   cmd:=command + ' >' + tmp + ' 2>&1';
   Debuglogs(cmd);
   fpsystem(cmd);
   if FileExists(tmp) then begin
      l:=TstringList.Create;

      try
         l.LoadFromFile(tmp);
      except
         Syslogs('OutputCmd:: FATAL error while reading '+tmp);
         exit;
      end;
      
      r:=TstringList.Create;
      for i:=0 to l.Count-1 do begin
          if length(trim(l.Strings[i]))>0 then r.Add(l.Strings[i]);
      end;
      if r.Count>0 then begin
         Debuglogs(r.Text);
         if realoutput then Output(r.Text,'info');
      end;
      r.free;
      l.free;
      DeleteFile(tmp);
   end;
end;
//##############################################################################
function Tlogs.OutputCmdR(command:string):string;
var
   tmp:string;
   cmd:string;
   i:Integer;
   l:TstringList;
   r:Tstringlist;
begin
   tmp:=FILE_TEMP();
   cmd:=command + ' >' + tmp + ' 2>&1';
   Debuglogs(cmd);
   fpsystem(cmd);
   if FileExists(tmp) then begin
      l:=TstringList.Create;
      r:=TstringList.Create;
      l.LoadFromFile(tmp);

      for i:=0 to l.Count-1 do begin
          if length(trim(l.Strings[i]))>0 then r.Add(l.Strings[i]);
      end;
      if r.Count>0 then begin
         Debuglogs(r.Text);
      end;
      r.free;
      l.free;
      result:=tmp;
   end;
end;
//##############################################################################

function Tlogs.FILE_TEMP():string;
var
   stmp:string;
   mypid:string;
begin
mypid:=IntToStr(fpgetpid);
stmp:=MD5FromString(FormatDateTime('yyyy-mm-dd hh:nn:ss', Now));

result:=GetTempFileName('',ExtractFileName(ParamStr(0))+'-'+stmp+'-'+mypid+'-')
end;


PROCEDURE Tlogs.logsStart(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        maintenant : Tsystemtime;
      BEGIN

        TargetPath:='/var/log/artica-postfix/start.log';

        forcedirectories('/var/log/artica-postfix');
        zDate := DateTimeNowSQL();

        if length(module_name)>0 then logsModule(zText);
        xText:=zDate + ' ' + zText;
        TRY
        EXCEPT
        writeln('unable to write /var/log/artica-postfix/start.log');
        END;

        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################


PROCEDURE tlogs.Syslogs(text:string);
var
   s:string;
   LogString: array[0..1024] of char;
   LogPrefix: array[0..255] of char;
   ProcessName:string;
   facility:longint;
const
  LOG_PID       = $01;
  LOG_CONS      = $02;
  LOG_ODELAY    = $04;
  LOG_NDELAY    = $08;
  LOG_NOWAIT    = $10;
  LOG_PERROR    = $20;
  LOG_EMERG             = 0;
  LOG_ALERT             = 1;
  LOG_CRIT              = 2;
  LOG_ERR               = 3;
  LOG_WARNING           = 4;
  LOG_NOTICE            = 5;
  LOG_INFO              = 6;
  LOG_DEBUG             = 7;
   
begin
   // S := FormatDateTime('yyyy-mm-dd hh:nn:ss', Now)
  ProcessName:= ExtractFileName(ParamStr(0));
  s:= ProcessName+'['+IntToStr(fpGetPid)+']';
  StrPCopy(LogPrefix, s);
  
  facility:=LOG_INFO;
  if ProcessName='artica-filter-smtp-out' then facility:=LOG_MAIL;
  if ProcessName='artica-mailarchive' then facility:=LOG_MAIL;
  if ProcessName='artica-bogom' then facility:=LOG_MAIL;
  if ProcessName='artica-attachments' then facility:=LOG_MAIL;
  
  OpenLog(LogPrefix, LOG_NOWAIT, facility);
  StrPCopy( LogString,text);
  SysLog(2, LogString, [0]);
  CloseLog();
  Debuglogs(text);
end;
//##############################################################################
function Tlogs.SYSTEM_FQDN():string;
begin
    D:=COMMANDLINE_PARAMETERS('debug');
    fpsystem('/bin/hostname >/opt/artica/logs/hostname.txt');
    result:=ReadFileIntoString('/opt/artica/logs/hostname.txt');
    result:=trim(result);
    if D then writeln('hostname=',result);
end;
//##############################################################################
PROCEDURE Tlogs.logs(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        maintenant : Tsystemtime;
        processname:string;
      BEGIN
        processname:=ExtractFileName(ParamStr(0));
        TargetPath:='/var/log/artica-postfix/artica-postfix.log';

        forcedirectories('/var/log/artica-postfix');
        getlocaltime(maintenant);zDate := FormatHeure(maintenant.Year)+'-' +FormatHeure(maintenant.Month)+ '-' + FormatHeure(maintenant.Day)+ chr(32)+FormatHeure(maintenant.Hour)+':'+FormatHeure(maintenant.minute)+':'+ FormatHeure(maintenant.second);

        if length(module_name)>0 then logsModule(zText);
        xText:=zDate + ' ' + processname  + ' ' + zText;

        TRY
        if Enable_echo=True then writeln(zText);
        EXCEPT
        END;
        
        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################
PROCEDURE Tlogs.nmap(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        info : stat;
        maintenant : Tsystemtime;
        processname:string;
      BEGIN
        processname:=ExtractFileName(ParamStr(0));
        forceDirectories('/usr/share/artica-postfix/ressources/logs');
        TargetPath:='/usr/share/artica-postfix/ressources/logs/nmap.log';
        Debuglogs(zText);
        forcedirectories('/var/log/artica-postfix');
        getlocaltime(maintenant);zDate := FormatHeure(maintenant.Year)+'-' +FormatHeure(maintenant.Month)+ '-' + FormatHeure(maintenant.Day)+ chr(32)+FormatHeure(maintenant.Hour)+':'+FormatHeure(maintenant.minute)+':'+ FormatHeure(maintenant.second);

        if length(module_name)>0 then logsModule(zText);
        xText:=zDate + ' ' + processname  + ' ' + zText;

        TRY
        if Enable_echo=True then writeln(zText);
        EXCEPT
        END;

        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################
PROCEDURE Tlogs.commandlog();
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        MasterDirectory:string;
        info : stat;
        MyDate,s:string;
        i:integer;
      BEGIN

        MasterDirectory:='/var/log/artica-postfix';
 if ParamCount>0 then begin
     for i:=0 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);

     end;
     s:=trim(s);
 end;
        TargetPath:=MasterDirectory+'/commands.debug';
        zDate:=FormatDateTime('dd:hh:ss', Now);
        xText:=zDate + ' ' +intTostr(fpgetpid)+ ' '+ s;
        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
                 ExecuteProcess('/bin/rm','-f ' +  TargetPath);
                 xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            try
               WriteLn(myFile, xText);
            finally
            CloseFile(myFile);
            end;
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################
PROCEDURE Tlogs.Debuglogs(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;
        MasterDirectory:string;
        info : stat;
        processname:string;
        RegExpr:TRegExpr;
        MyDate:string;
      BEGIN
        processname:=ExtractFileName(ParamStr(0));
        RegExpr:=TRegExpr.Create;
        MasterDirectory:='/var/log/artica-postfix';

        if processname='artica-make' then writeln(zText);
        
        if COMMANDLINE_PARAMETERS('--verbose') then writeln(ztext);

        if not COMMANDLINE_PARAMETERS('--startall') then begin
           RegExpr.Expression:='Starting[\.\s+:]+';
           if RegExpr.Exec(zText) then writeln(zText);
           RegExpr.Expression:='Stopping[\.\s+:]+';
           if RegExpr.Exec(zText) then writeln(zText);
        end;


           //logging into syslog /start/stop daemons....
           

           RegExpr.Free;

        if processname='artica-pipe-back' then MasterDirectory:='/opt/artica/mimedefang-hooks';
        if processname='artica-mimedefang-pipe' then MasterDirectory:='/opt/artica/mimedefang-hooks';
        if processname='artica-filter-smtp-out' then MasterDirectory:='/opt/artica/mimedefang-hooks';

        if processname='artica-backup' then begin
           if Paramstr(1)='--export-config' then begin
              MasterDirectory:='/usr/share/artica-postfix/ressources/logs';
              TargetPath:=MasterDirectory+'/export-config.debug';
              if not FileExists(TargetPath) then begin
                 fpsystem('/bin/touch '+TargetPath);
                 fpsystem('/bin/chmod 755 '+ TargetPath);
              end;
          end;

           if Paramstr(1)='--import-config' then begin
              MasterDirectory:='/usr/share/artica-postfix/ressources/logs';
              TargetPath:=MasterDirectory+'/export-config.debug';
              if not FileExists(TargetPath) then begin
                 fpsystem('/bin/touch '+TargetPath);
                 fpsystem('/bin/chmod 755 '+ TargetPath);
              end;
          end;


        end;


        
        
        
        forcedirectories(MasterDirectory);
        if length(TargetPath)=0 then begin
           TargetPath:=MasterDirectory+'/' + processname + '.debug';
           if processname='artica-backup' then begin
            MyDate:=FormatDateTime('yyyy-mm-dd', Now);
            TargetPath:=MasterDirectory+'/' + processname + '-'+MyDate+'.debug';
           end;

           if processname='artica-update' then begin
              MyDate:=FormatDateTime('yyyy-mm-dd-hh', Now);
              TargetPath:=MasterDirectory+'/' + processname + '-'+MyDate+'.debug';
           end;
        end;

        zDate :=DateTimeNowSQL();

        if length(module_name)>0 then logsModule(zText);                        
        xText:=zDate + ' ' +intTostr(fpgetpid)+ ' '+ zText;




        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              if processname<>'artica-backup' then begin
                 ExecuteProcess('/bin/rm','-f ' +  TargetPath);
                 xText:=xText + ' (log file was killed before)';
                 end;
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            try
               WriteLn(myFile, xText);
            finally
            CloseFile(myFile);
            end;
        EXCEPT
             //writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//##############################################################################

FUNCTION Tlogs.TRANSFORM_DATE_MONTH(zText:string):string;
begin
  zText:=UpperCase(zText);
  zText:=StringReplace(zText, 'JAN', '01',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'FEB', '02',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'MAR', '03',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'APR', '04',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'MAY', '05',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'JUN', '06',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'JUL', '07',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'AUG', '08',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'SEP', '09',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'OCT', '10',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'NOV', '11',[rfReplaceAll, rfIgnoreCase]);
  zText:=StringReplace(zText, 'DEC', '12',[rfReplaceAll, rfIgnoreCase]);
  result:=zText;
end;


PROCEDURE Tlogs.logsModule(zText:string);
      var
        zDate:string;
        myFile : TextFile;
        xText:string;
        TargetPath:string;


      BEGIN
        D:=COMMANDLINE_PARAMETERS('debug');
        if D then writeln('logsmodule();');
        TargetPath:='/var/log/artica-postfix/' + module_name + '.log';
        forcedirectories('/var/log/artica-postfix');
        zDate:=DateTimeNowSQL();
        xText:=zDate + ' ' + zText;


        TRY
           if GetFileSizeKo(TargetPath)>MaxlogSize then begin
              ExecuteProcess('/bin/rm','-f ' +  TargetPath);
              xText:=xText + ' (log file was killed before)';
              end;
              EXCEPT
              exit;
        end;

        TRY

           AssignFile(myFile, TargetPath);
           if FileExists(TargetPath) then Append(myFile);
           if not FileExists(TargetPath) then ReWrite(myFile);
            WriteLn(myFile, xText);
           CloseFile(myFile);
        EXCEPT
             writeln(xtext + '-> error writing ' +     TargetPath);
          END;
      END;
//#############################################################################
procedure Tlogs.DeleteLogs();
var
        TargetPath:string;
        val_GetFileSizeKo:integer;
begin
   TargetPath:='/var/log/artica-postfix/artica-postfix.log';
  val_GetFileSizeKo:=GetFileSizeKo(TargetPath);
  if debug then logs('Tlogs.DeleteLogs() -> ' + IntToStr(val_GetFileSizeKo) + '>? -> ' + IntToStr(MaxlogSize));
  if val_GetFileSizeKo>MaxlogSize then  fpsystem('/bin/rm -f ' +  TargetPath);

end;
//##############################################################################


function Tlogs.GetFileSizeKo(path:string):longint;
Var
L : File Of byte;
size:longint;
ko:longint;

begin
if not FileExists(path) then begin
   result:=0;
   exit;
end;
   TRY
  Assign (L,path);
  Reset (L);
  size:=FileSize(L);
   Close (L);
  ko:=size div 1024;
  result:=ko;
  EXCEPT

  end;
end;
//##############################################################################
function Tlogs.GetFileBytes(path:string):longint;
Var
L : File Of byte;
size:longint;
ko:longint;

begin
if not FileExists(path) then begin
   result:=0;
   exit;
end;
   TRY
  Assign (L,path);
  Reset (L);
  size:=FileSize(L);
   Close (L);
  ko:=size;
  result:=ko;
  EXCEPT

  end;
end;
function Tlogs.MaxSizeLimit:integer;
begin
exit(100);
end;
//##############################################################################

function Tlogs.FormatHeure (value : Int64) : String;
var minus : boolean;
begin
result := '';
if value = 0 then
result := '0';
Minus := value <0;
if minus then
value := -value;
while value >0 do begin
      result := char((value mod 10) + integer('0'))+result;
      value := value div 10;
end;
 if minus then
 result := '-' + result;
 if length(result)=1 then result := '0'+result;
end;
 //##############################################################################

function Tlogs.SearchAndReplace(sSrc, sLookFor, sReplaceWith: string ): string;
var
  nPos,
  nLenLookFor : integer;
begin
  nPos        := Pos( sLookFor, sSrc );
  nLenLookFor := Length( sLookFor );
  while(nPos > 0)do
  begin
    Delete( sSrc, nPos, nLenLookFor );
    Insert( sReplaceWith, sSrc, nPos );
    nPos := Pos( sLookFor, sSrc );
  end;
  Result := sSrc;
end;

//##############################################################################
function Tlogs.COMMANDLINE_PARAMETERS(FoundWhatPattern:string):boolean;
var
   i:integer;
   s:string;
   RegExpr:TRegExpr;

begin
 s:='';
 result:=false;
 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);
     end;
 end;
   RegExpr:=TRegExpr.Create;
   RegExpr.Expression:='\s+'+FoundWhatPattern;
   if RegExpr.Exec(s) then result:=True;
   RegExpr.Free;
   s:='';

end;
//##############################################################################
function Tlogs.MD5FromString(value:string):string;
var
Digest:TMD5Digest;
begin
Digest:=MD5String(value);
exit(MD5Print(Digest));
end;
//##############################################################################
function Tlogs.ReadFileIntoString(path:string):string;
var
   List:TstringList;
begin

      if not FileExists(path) then begin
        exit;
      end;

      List:=Tstringlist.Create;
      List.LoadFromFile(path);
      result:=List.Text;
      List.Free;
end;
//##############################################################################
function Tlogs.MYSQL_SERVER_PARAMETERS_CF(key:string):string;
var ini:TiniFile;
begin
  result:='';
  if not FileExists(MYSQL_MYCNF_PATH()) then exit();
  ini:=TIniFile.Create(MYSQL_MYCNF_PATH());
  result:=ini.ReadString('mysqld',key,'');
  ini.free;
end;
//#############################################################################
function Tlogs.GET_INFO(key:string):string;
var
   str:string;
begin

str:='';
   if FileExists('/etc/artica-postfix/settings/Daemons/'+key) then begin
      str:=trim(ReadFileIntoString('/etc/artica-postfix/settings/Daemons/'+key));
      result:=str;
   end;

end;
//#############################################################################
function Tlogs.INSTALL_STATUS(APP_NAME:string;POURC:integer):string;
var
   ini:TiniFile;
   user:string;
begin
  result:='';

  user:=GET_INFO('LighttpdUserAndGroup');
  if length(user)=0 then user:='www-data:www-data';
  forceDirectories('/usr/share/artica-postfix/ressources/install');
  ini:=TIniFile.Create('/usr/share/artica-postfix/ressources/install/'+APP_NAME+'.ini');
  ini.WriteString('INSTALL','STATUS',IntToStr(POURC));
  ini.free;
  fpsystem('/bin/chmod 777 /usr/share/artica-postfix/ressources/install');
  fpsystem('/bin/chown -R '+user+' /usr/share/artica-postfix/ressources/install');
end;
//#############################################################################
function Tlogs.MYSQL_MYCNF_PATH:string;
begin
  if FileExists('/etc/mysql/my.cnf') then exit('/etc/mysql/my.cnf');
  if FileExists('/etc/my.cnf') then exit('/etc/my.cnf');

end;
//#############################################################################
function Tlogs.MYSQL_INFOS(val:string):string;
var ini:TIniFile;
    str:string;
begin

   if FileExists('/etc/artica-postfix/settings/Mysql/'+val) then begin
      str:=trim(ReadFileIntoString('/etc/artica-postfix/settings/Mysql/'+val));
      result:=trim(str);

      if result='' then begin
           if(val='port') then exit('3306');
      end;
     exit;
   end;

if not FileExists('/etc/artica-postfix/artica-mysql.conf') then exit();
try
ini:=TIniFile.Create('/etc/artica-postfix/artica-mysql.conf');
result:=ini.ReadString('MYSQL',val,'');
finally
ini.Free;
end;


end;
//#############################################################################
function Tlogs.MYSQL_PORT():string;
var
   mycf_path   :string;
begin
   mycf_path:=MYSQL_MYCNF_PATH();
   result:=MYSQL_READ_CF('port',mycf_path);
   if length(result)=0 then result:='3306';
end;
//#############################################################################
function Tlogs.MYSQL_READ_CF(key:string;mycfpath:string):string;
var ini:TiniFile;
begin
  result:='';
  if not FileExists(mycfpath) then exit();
  ini:=TIniFile.Create(mycfpath);
  result:=ini.ReadString('mysqld',key,'');
  ini.free;
end;
//#############################################################################
function Tlogs.GetFileSizeMo(path:string):longint;
Var
L : File Of byte;
size:longint;
ko:longint;

begin
if not FileExists(path) then begin
   result:=0;
   exit;
end;
   TRY
  Assign (L,path);
  Reset (L);
  size:=FileSize(L);
   Close (L);
  ko:=size div 1024;
  ko:=ko div 1000;
  result:=ko;
  EXCEPT

  end;
end;
function Tlogs.LOCATE_PHP5_BIN():string;
begin
  if FileExists('/usr/bin/php5') then exit('/usr/bin/php5');
  if FIleExists('/usr/bin/php') then exit('/usr/bin/php');
  if FIleExists('/usr/local/apache-groupware/php5/bin/php') then exit('/usr/local/apache-groupware/php5/bin/php');

end;
//##############################################################################


//##############################################################################
FUNCTION Tlogs.QUERY_SQL(sql:Pchar;database:string):boolean;
var tmpstr:string;
begin
     tmpstr:=FILE_TEMP();
     WriteToFile(sql,tmpstr);
    result:=EXECUTE_SQL_FILE(tmpstr,database,'');
end;


FUNCTION Tlogs.GetAsSQLText(MessageToTranslate:string) : string;
var tmpstr:string;
begin
     tmpstr:=FILE_TEMP();
     WriteToFile(MessageToTranslate,tmpstr);
     fpsystem(LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mysql.build.php --GetAsSQLText '+tmpstr);
     result:= ReadFromFile(tmpstr);
     DeleteFile(tmpstr);
end;



function Tlogs.EXECUTE_SQL_FILE(filenname:string;database:string;defaultcharset:string):boolean;
var
   tmpstr:string;
   RegExpr:TRegExpr;
   l:tstringlist;
   i:integer;
begin
tmpstr:=FILE_TEMP();
fpsystem(LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mysql.build.php --execute '+filenname+' '+ database +' >'+tmpstr+' 2>&1');
l:=Tstringlist.Create;
l.LoadFromFile(tmpstr);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='ERROR';
For i:=0 to l.Count -1 do begin
    if RegExpr.Exec(L.strings[i]) then begin
       writeln(L.strings[i]);
       result:=false;
       RegExpr.free;
       l.free;
       DeleteFile(tmpstr);
       exit;
    end;
end;
       result:=true;
       RegExpr.free;
       l.free;
       DeleteFile(tmpstr);

end;
//#############################################################################
function Tlogs.EXECUTE_SQL_STRING(query:string):string;
    var
    root,commandline,password,port,socket,basedir,mysqlbin,server,sql_results:string;
    tempfile:string;
begin
  root    :=MYSQL_INFOS('database_admin');
  password:=MYSQL_INFOS('database_password');
  port    :=MYSQL_INFOS('port');
  server  :=MYSQL_INFOS('mysql_server');
  mysqlbin:=MYSQL_EXEC_BIN_PATH();
  if length(port)=0 then port:='3306';
  if length(server)=0 then server:='127.0.0.1';

  if length(password)>0 then password:=' -p'+password;
  if not fileExists(mysqlbin) then begin
     Debuglogs('EXECUTE_SQL_FILE:: Unable to locate mysql binary (usually in ' + mysqlbin + ')');
     exit();
  end;

   tempfile:=FILE_TEMP();
   commandline:=MYSQL_EXEC_BIN_PATH() + ' --host=' + server+' --port=' + port + ' --socket=' +socket+ ' --skip-column-names --silent --xml --user='+ root +password + ' -e "'+query+'"';
   commandline:=commandline + ' >' + tempfile + ' 2>&1';
   Debuglogs(commandline);
   fpsystem(commandline);
   sql_results:=ReadFileIntoString(tempfile);
   DeleteFile(tempfile);
   exit(sql_results);

end;
//#############################################################################
function Tlogs.LIST_MYSQL_DATABASES():TstringList;
var
RegExpr     :TRegExpr;
l:TstringList;
res:TstringList;
i:integer;
tempfile:string;
begin
tempfile:=FILE_TEMP();

l:=TstringList.Create;
result:=l;
l.Add(EXECUTE_SQL_STRING('SHOW DATABASES'));
try
   l.SaveToFile(tempfile);
except
  exit;
end;


l.clear;
l.LoadFromFile(tempfile);
DeleteFile(tempfile);
RegExpr:=TRegExpr.Create;
res:=TstringList.Create;
RegExpr.Expression:='<field name="Database">(.+?)</field>';
for i:=0 to l.Count-1 do begin
    if RegExpr.Exec(l.Strings[i]) then begin
       res.Add(RegExpr.Match[1]);
    end;
end;

RegExpr.free;
l.free;
result:=res;
end;
//#############################################################################
function Tlogs.IF_DATABASE_EXISTS(database_name:string):boolean;
var
RegExpr     :TRegExpr;
l:TstringList;
res:TstringList;
i:integer;
tempfile:string;
begin
tempfile:=FILE_TEMP();
fpsystem(LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mysql.build.php --database-exists '+database_name+' >'+tempfile+' 2>&1');
l:=TstringList.Create;
l.LoadFromFile(tempfile);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='FALSE';
result:=true;
DeleteFile(tempfile);
for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then result:=false;

end;
RegExpr.free;
l.free;

end;
//#############################################################################
function Tlogs.IF_TABLE_EXISTS(table:string;database:string):boolean;
var
RegExpr     :TRegExpr;
l:TstringList;
res:TstringList;
i:integer;
tempfile:string;
begin
tempfile:=FILE_TEMP();
fpsystem(LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mysql.build.php --table-exists '+database+' '+ table+' >'+tempfile+' 2>&1');
l:=TstringList.Create;
l.LoadFromFile(tempfile);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='FALSE';
result:=true;
DeleteFile(tempfile);
for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then result:=false;

end;
RegExpr.free;
l.free;
end;
//#############################################################################
function Tlogs.SYS_EVENTS_ROWNUM():integer;
begin
result:=TABLE_ROWNUM('syslogs','artica_events');
end;
//#############################################################################
function Tlogs.TABLE_ROWNUM(tablename:string;database:string):integer;
var
RegExpr     :TRegExpr;
l:TstringList;
res:TstringList;
i:integer;
tempfile:string;
begin
result:=0;
tempfile:=FILE_TEMP();
fpsystem(LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.mysql.build.php --rownum '+database+' '+ tablename+' >'+tempfile+' 2>&1');
l:=TstringList.Create;
l.LoadFromFile(tempfile);
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='([0-9]+)';
DeleteFile(tempfile);

for i:=0 to l.Count-1 do begin
   if RegExpr.Exec(l.Strings[i]) then tryStrToInt(RegExpr.Match[1],result);
end;
RegExpr.free;
l.free;
end;
//#############################################################################
function Tlogs.MYSQL_EXEC_BIN_PATH():string;
begin
   if FileExists('/usr/bin/mysql') then exit('/usr/bin/mysql');
   if FileExists('/usr/local/bin/mysql') then exit('/usr/local/bin/mysql');
end;
//#############################################################################

function Tlogs.WriteToFile(zText:string;TargetPath:string):boolean;
      var
        F : Text;
      BEGIN
      result:=true;
      Debuglogs('Tlogs.WriteToFile:: ' + IntToStr(length(zText)) + ' bytes in ' + TargetPath);
      TRY
      forcedirectories(ExtractFilePath(TargetPath));
       EXCEPT
             Debuglogs('Tlogs.WriteFile():: -> error I/O while creating directory in ' +     TargetPath);

        END;

        TRY
           Assign (F,TargetPath);
           Rewrite (F);
           Write(F,zText);
           Close(F);
          exit(true);
        EXCEPT
             Debuglogs('Tlogs.WriteFile():: -> error I/O while Writing in ' +     TargetPath);

        END;
        
exit(false);
      END;
//#############################################################################
function Tlogs.ReadFromFile(TargetPath:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
  F:textfile;
  teststr: string;
  s:string;
begin
  if not FileExists(TargetPath) then exit;
  assignfile(F,TargetPath);
 reset(F);
          repeat
                readln(F,s);
                teststr:=teststr+s+CRLF;
          until eof(F);
 closefile(F);
 result:=teststr;
end;
//#############################################################################
procedure Tlogs.WriteInstallLogs(text:string);
var
zdate,dir,filew,mypid,CurrentInstallProduct:string;
myFile : TextFile;
begin
mypid:=intTostr(fpgetpid);

CurrentInstallProduct:=GET_INFO('CurrentInstallProduct');
if length(CurrentInstallProduct)=0 then exit;
zdate:=DateTimeNowSQL();
dir:='/usr/share/artica-postfix/ressources/logs/'+CurrentInstallProduct;
filew:=dir+'/install.log';
ForceDirectories(dir);

text:=zdate + ' ['+ mypid +']:'+text;

        TRY
           AssignFile(myFile, filew);
           if FileExists(filew) then Append(myFile);
           if not FileExists(filew) then ReWrite(myFile);
            WriteLn(myFile, text);
           CloseFile(myFile);
        EXCEPT

        END;


end;
//#############################################################################
procedure Tlogs.LogGeneric(text:string;path:string);
var
zdate,mypid:string;
myFile : TextFile;
begin
forcedirectories(ExtractFilePath(path));
mypid:=intTostr(fpgetpid);
zdate:=DateTimeNowSQL();

text:=zdate + ' ['+ mypid +']:'+text;
Debuglogs(text);

        TRY
           AssignFile(myFile, path);
           if FileExists(path) then Append(myFile);
           if not FileExists(path) then ReWrite(myFile);
            WriteLn(myFile, text);
           CloseFile(myFile);
        EXCEPT

        END;


end;
//#############################################################################

procedure Tlogs.DeleteFile(TargetPath:string);
Var F : Text;

begin
  if not FileExists(TargetPath) then exit;
  TRY
    Assign (F,TargetPath);
    Erase (f);
  EXCEPT
     Debuglogs('Delete():: -> error I/O in ' +     TargetPath);
  end;
end;
//#############################################################################
procedure Tlogs.set_INFOS(key:string;val:string);
var ini:TIniFile;
begin
try
ini:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
ini.WriteString('INFOS',key,val);
finally
ini.Free;
end;
end;
//#############################################################################
procedure Tlogs.NOTIFICATION(subject:string;content:string;context:string);
begin
EVENTS(subject,content,context,'');


end;
//#############################################################################
function Tlogs.DateTimeDiff(Start, Stop : TDateTime) : int64;
var TimeStamp : TTimeStamp;
begin
  TimeStamp := DateTimeToTimeStamp(Stop - Start);
  Dec(TimeStamp.Date, TTimeStamp(DateTimeToTimeStamp(0)).Date);
  Result := (TimeStamp.Date*24*60*60)+(TimeStamp.Time div 1000);
end;
//#############################################################################
function Tlogs.QUERY_SQL_BIN(database:string;fileName:string):boolean;
    var
       root,commandline,password,port,
       mysqlbin,FileTemp:string;

begin
  root    :=MYSQL_INFOS('database_admin') +#0;
  password:=MYSQL_INFOS('database_password') +#0;
  port    :=MYSQL_SERVER_PARAMETERS_CF('port') +#0;
  result:=false;
  mysqlbin:='';
  FileTemp:=FILE_TEMP();
  if length(password)>0 then password:=' -p'+password;
  if not fileExists(mysqlbin) then begin
     Debuglogs('QUERY_SQL_BIN::Unable to stat mysql client');
     exit(false);
  end;
  commandline:=mysqlbin + ' --port=' + port + ' --database=' + database;
  commandline:=commandline + ' --skip-column-names --silent --xml --user='+ root +password + ' <'+FileName;
  commandline:=commandline + ' >' + FileTemp+ ' 2>&1';

  Debuglogs('QUERY_SQL_BIN::'+commandline);
  fpsystem(commandline);
end;
//#############################################################################
function Tlogs.INSTALL_PROGRESS(APP_NAME:string;info:string):string;
var ini:TiniFile;
begin
  result:='';
  forceDirectories('/usr/share/artica-postfix/ressources/install');
  try
     ini:=TIniFile.Create('/usr/share/artica-postfix/ressources/install/'+APP_NAME+'.ini');
     ini.WriteString('INSTALL','INFO',info);
  except
   writeln('INSTALL_STATUS():: FATAL ERROR STAT /usr/share/artica-postfix/ressources/install/'+APP_NAME+'.ini');
   exit;
  end;
  ini.free;
end;
//#############################################################################
function Tlogs.FILE_TIME_BETWEEN_MIN(filepath:string):LongInt;
var
   fa   : Longint;
   S    : TDateTime;
   maint:TDateTime;
begin
if not FileExists(filepath) then exit(0);
    fa:=FileAge(filepath);
    maint:=Now;
    S:=FileDateTodateTime(fa);
    result:=MinutesBetween(maint,S);
end;
//##############################################################################

function Tlogs.copyfile(srcfn, destfn:string):boolean;
const bufs= 65536;
var buf:pointer;
    f1,f2,bytesread:longint;
begin
  result:=false;
  if not FileExists(srcfn)then exit;
  getmem(buf,bufs);
  f2:=FileCreate(destfn);
  if f2<=0 then exit;
  f1:=FileOpen(srcfn,fmOpenRead);
  if f1<=0 then begin FileClose(f2); exit end;
  repeat
    bytesread:=FileRead(f1,buf^,bufs);
    if bytesread>0 then bytesread:=FileWrite(f2,buf^,bytesread);
  until bytesread<>bufs;
  FileClose(f1); FileClose(f2);
  freemem(buf,bufs);
  result:=bytesread<>-1;
end;



end.

