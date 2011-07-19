unit artica_tcp;

{$MODE DELPHI}
{$LONGSTRINGS ON}

interface

uses
    Classes, SysUtils,variants,logs,unix,RegExpr in 'RegExpr.pas',libc;

 const
  IP_NAMESIZE = 16;
 type
  ipstr = array[0..IP_NAMESIZE-1] of char;

  type
  ttcp=class


private
     LOGS:Tlogs;
     function GetFlags(Interfaccia: String): Integer;



public
    procedure   Free;
    constructor Create;
     function GetIPAddressOfInterface( if_name:ansistring):ansistring;
     function GetMacAddress(Interfaccia: String): String;
     function GetNetMaskAddressOfInterface(if_name:ansistring):ansistring;
     function GetBroadCastOfInterface(if_name:ansistring):ansistring;
     function InterfacesList(): String;


END;

implementation

constructor ttcp.Create;
begin
      LOGS:=tlogs.Create();

end;
//##############################################################################
procedure ttcp.free();
begin
    logs.Free;
end;
//##############################################################################
 function ttcp.GetIPAddressOfInterface( if_name:ansistring):ansistring;
 var
  ifr : ifreq;
  tmp:ipstr;
  sock : longint;
  p:pChar;

begin
  Result:='0.0.0.0';
  

  
  strncpy( ifr.ifr_ifrn.ifrn_name, pChar(if_name), IF_NAMESIZE-1 );
  ifr.ifr_ifru.ifru_addr.sa_family := AF_INET;
  FillChar(tmp[0], IP_NAMESIZE, #0);
  sock := socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
  if ( sock >= 0 ) then begin
    if ( ioctl( sock, SIOCGIFADDR, @ifr ) >= 0 ) then begin
      p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
      if ( p <> nil ) then strncpy(tmp, p, IP_NAMESIZE-1);
      if ( tmp[0] <> #0 ) then Result :=  tmp;
    end;
    libc.__close(sock);
  end;
 end;
//##############################################################################
function ttcp.GetNetMaskAddressOfInterface(if_name:ansistring):ansistring;
var
  ifr : ifreq;
  tmp:ipstr;
  sock : longint;
  p:pChar;
begin

  strncpy(ifr.ifr_ifrn.ifrn_name, pChar(if_name), IF_NAMESIZE- 1);
  sock:= socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
  if sock>= 0 then begin
    if ioctl(sock, SIOCGIFNETMASK, @ifr)>= 0 then begin
      p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
      if ( p <> nil ) then strncpy(tmp, p, IP_NAMESIZE-1);
      if ( tmp[0] <> #0 ) then Result :=  tmp;
    end else begin
        writeln('err..');
    end;
    libc.__close(sock);
  end;
end;

//##############################################################################
function ttcp.GetBroadCastOfInterface(if_name:ansistring):ansistring;
var
  ifr : ifreq;
  tmp:ipstr;
  sock : longint;
  p:pChar;
begin

  strncpy(ifr.ifr_ifrn.ifrn_name, pChar(if_name), IF_NAMESIZE- 1);
  sock:= socket(AF_INET, SOCK_DGRAM, IPPROTO_IP);
  if sock>= 0 then begin
    if ioctl(sock, SIOCSIFHWBROADCAST, @ifr)>= 0 then begin
      p:=inet_ntoa( ifr.ifr_ifru.ifru_addr.sin_addr );
      if ( p <> nil ) then strncpy(tmp, p, IP_NAMESIZE-1);
      if ( tmp[0] <> #0 ) then Result :=  tmp;
    end else begin
        writeln('err..');
    end;
    libc.__close(sock);
  end;
end;

//##############################################################################


function ttcp.InterfacesList(): String;
const SIOCGIWNAME= 35585;
var
  ifc: ifconf;
  ifr: array[0..1023] of ifreq;
  sock, I: Integer;
  interface_name,interface_mac,interface_type,interface_ip,interface_up,interface_mask: String;
  l:TstringList;
begin
  InterfacesList:= '';
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    ifc.ifc_len:= SizeOf(ifr);
    ifc.ifc_ifcu.ifcu_req:= ifr;
    if ioctl(sock, SIOCGIFCONF, @ifc)= 0 then begin
      l:=TstringList.Create;
      for I:= 0 to ifc.ifc_len div SizeOf(ifreq)- 1 do begin
        interface_name:= ifr[I].ifr_ifrn.ifrn_name;
        interface_mac:=GetMacAddress(ifr[I].ifr_ifrn.ifrn_name);
        interface_ip:= GetIPAddressOfInterface(ifr[I].ifr_ifrn.ifrn_name);
        interface_mask:=GetNetMaskAddressOfInterface(ifr[I].ifr_ifrn.ifrn_name);
        interface_type:='lan';

        
        
        if (GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_LOOPBACK)<> 0 then begin
          interface_type:='Loopbak';
        end;

        if ioctl(sock, SIOCGIWNAME, @ifr[I])= 0 then begin
             interface_type:='Wireless';
        end;
           
           
        if (GetFlags(ifr[I].ifr_ifrn.ifrn_name) and IFF_UP)<> 0 then begin
           interface_up:='1'
        end else begin
            interface_up:= '0';
        end;
        
        
        l.Add('['+interface_name+']');
        l.Add('mac='+interface_mac);
        l.Add('ip='+interface_ip);
        l.Add('mask='+interface_mask);
        l.Add('type='+interface_type);
        l.Add('up='+interface_up);
       end;
    end;
    libc.__close(sock);
  end;
  
  result:=l.Text;
  logs.logs('InterfacesList:' + result);
  l.free;
  
end;
//##############################################################################
function ttcp.GetMacAddress(Interfaccia: String): String;
var
  ifr : ifreq;
  sock: Integer;
begin
  GetMacAddress:= '';
  strncpy(ifr.ifr_ifrn.ifrn_name, pChar(Interfaccia), IF_NAMESIZE- 1);
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    if ioctl(sock, SIOCGIFHWADDR, @ifr)>= 0 then begin
      result:= IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[0], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[1], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[2], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[3], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[4], 2)+ ':'+
        IntToHex(ifr.ifr_ifru.ifru_hwaddr.sa_data[5], 2);
    end;
    libc.__close(sock);
  end;
end;
//##############################################################################
function ttcp.GetFlags(Interfaccia: String): Integer;
var
  ifr : ifreq;
  sock: Integer;
begin
  GetFlags:= 0;
  strncpy(ifr.ifr_ifrn.ifrn_name, pChar(Interfaccia), IF_NAMESIZE- 1);
  sock:= socket(AF_INET, SOCK_DGRAM, 0);
  if sock>= 0 then begin
    if ioctl(sock, SIOCGIFFLAGS, @ifr)>= 0 then begin
      GetFlags:= ifr.ifr_ifru.ifru_flags;
    end;
    libc.__close(sock);
  end;
end;
//##############################################################################






end.
