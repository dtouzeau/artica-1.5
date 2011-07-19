unit monitorix;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,logs,unix,RegExpr in 'RegExpr.pas',zsystem,lighttpd;

type LDAP=record
      admin:string;
      password:string;
      suffix:string;
      servername:string;
      Port:string;
  end;

  type
  tmonitorix=class


private
     LOGS:Tlogs;
     GLOBAL_INI:TiniFIle;
     SYS:TSystem;
     artica_path:string;
     lighttp:Tlighttpd;

public
    procedure   Free;
    constructor Create;
    procedure   START();
    procedure   INSTALL_MONITORIX();
END;

implementation

constructor tmonitorix.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();
       SYS:=Tsystem.Create;
       lighttp:=Tlighttpd.Create(SYS);

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tmonitorix.free();
begin
    logs.Free;
    SYS.Free;
end;
//##############################################################################
procedure tmonitorix.INSTALL_MONITORIX();
begin
   if not FileExists('/etc/init.d/monitorix.sh') then begin
      if fileExists(artica_path + '/bin/install/monitorix.deb') then begin
         if FileExists('/usr/bin/dpkg') then begin
             logs.OutputCmd('/usr/bin/dpkg -i '+artica_path + '/bin/install/monitorix.deb');
         end;
      end;
   end;


end;
//##############################################################################
procedure tmonitorix.START();
var
   tmpstr:string;
   uri:string;
   port:string;
   start:boolean;
   num:integer;
begin

if SYS.GET_INFO('EnableMonitorix')<>'1' then exit;
if not FileExists('/etc/init.d/monitorix.sh') then begin
   INSTALL_MONITORIX();
   if not FileExists('/etc/init.d/monitorix.sh') then exit;
end;

start:=false;

if not start then begin
   if not FileExists('/etc/artica-postfix/monitorix.time') then begin
      logs.Debuglogs('unable to stat /etc/artica-postfix/monitorix.time assign start=true');
      start:=true;
   end;
end;

if not start then begin
   num:=SYS.FILE_TIME_BETWEEN_SEC('/etc/artica-postfix/monitorix.time');
   logs.Debuglogs('tmonitorix.START():: '+IntToStr(num)+' seconds');
   if num>50 then start:=true;
end;


if not start then exit;
logs.OutputCmd('/bin/touch /etc/artica-postfix/monitorix.time');
logs.OutputCmd('/usr/sbin/monitorix.pl update');
tmpstr:=logs.FILE_TEMP();
port:=lighttp.LIGHTTPD_LISTEN_PORT();
uri:='https://127.0.0.1:'+port+'/cgi-bin/monitorix.cgi?mode=localhost&graph=all&when=day&color=black';
logs.Debuglogs(uri);
SYS.WGET_DOWNLOAD_FILE(uri,tmpstr);
uri:='https://127.0.0.1:'+port+'/cgi-bin/monitorix.cgi?mode=localhost&graph=all&when=week&color=black';
logs.Debuglogs(uri);
SYS.WGET_DOWNLOAD_FILE(uri,tmpstr);
uri:='https://127.0.0.1:'+port+'/cgi-bin/monitorix.cgi?mode=localhost&graph=all&when=month&color=black';
logs.Debuglogs(uri);
SYS.WGET_DOWNLOAD_FILE(uri,tmpstr);
uri:='https://127.0.0.1:'+port+'/cgi-bin/monitorix.cgi?mode=localhost&graph=all&when=year&color=black';
logs.Debuglogs(uri);
SYS.WGET_DOWNLOAD_FILE(uri,tmpstr);
logs.DeleteFile(tmpstr);
ForceDirectories('/opt/artica/share/www/monitorix');
logs.OutputCmd('/bin/mv /var/www/monitorix/imgs/* /opt/artica/share/www/monitorix/');

end;
//##############################################################################

end.
