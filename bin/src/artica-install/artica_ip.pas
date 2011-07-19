unit artica_ip;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,strutils,IniFiles, Process,md5,logs,unix,RegExpr in 'RegExpr.pas',libc;

  type
  tip=class


private
     LOGS:Tlogs;
     D:boolean;
     artica_path:string;
     inif:TiniFile;



public
    procedure   Free;
    constructor Create;
    function LOCAL_IP(ifname:string):string;


END;

implementation

constructor tip.Create;
begin
       forcedirectories('/etc/artica-postfix');
       LOGS:=tlogs.Create();

       if not DirectoryExists('/usr/share/artica-postfix') then begin
              artica_path:=ParamStr(0);
              artica_path:=ExtractFilePath(artica_path);
              artica_path:=AnsiReplaceText(artica_path,'/bin/','');

      end else begin
          artica_path:='/usr/share/artica-postfix';
      end;
end;
//##############################################################################
procedure tip.free();
begin
    FreeAndNil(logs);
end;
//##############################################################################
function tip.LOCAL_IP(ifname:string):string;
var
 ifr : ifreq;
 sock : longint;
 p:pChar;


begin
 Result:='';
 strncpy( ifr.ifr_ifrn.ifrn_name, pChar(ifname), IF_NAMESIZE-1 );
 ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
 sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
 if ( sock >= 0 ) then begin
   if ( ioctl( sock, SIOCGIFADDR, @ifr ) >= 0 ) then begin
     p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
     if ( p <> nil ) then Result :=  p;
   end;
   libc.__close(sock);
 end;

end;
//##############################################################################


end.

