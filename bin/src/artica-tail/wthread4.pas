unit wthread4;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
interface
uses
  Libc, Classes,logs,SysUtils,global_conf,IniFiles,RegExpr,systemlog,mimedefang,unix,artica_mysql;
type
  TSampleThread4 = class(TThread)
  private
    tid          : integer;
    logs         :tlogs;
    maillog_path :string;
    fileSize_mem :integer;
    GLOBAL_INI   :myconf;
    memvals      :TiniFile;
    artica_path  :string;
  protected
    procedure Execute; override;
  public
    procedure start;
    Ended:boolean;
    constructor Create(startSuspended: boolean);
  end;

implementation
//##############################################################################
procedure TSampleThread4.Execute;
begin
     while not Terminated do begin
           start;
           logs.Debuglogs('wthread4:[' + IntToStr(tid)+']: Sleeping 30 seconds...');
           __sleep(30);
    end;
end;

//##############################################################################
constructor TSampleThread4.Create(startSuspended: boolean);
begin
  inherited Create(startSuspended);
  FreeOnTerminate := True;
  tid:=ThreadID;



end;
//##############################################################################

procedure TSampleThread4.start;

begin
    Ended:=false;
    GLOBAL_INI:=myconf.Create;
    artica_path:=GLOBAL_INI.get_ARTICA_PHP_PATH();
    logs:=tlogs.Create;
    logs.Debuglogs('wthread4:[' + IntToStr(tid)+']: Starting '+artica_path + '/bin/artica-ldap -nmap');
    fpsystem(artica_path + '/bin/artica-ldap -nmap');
    Ended:=true;
end;


end.


