{**
INFO
    Basic DNS (and network) functions like converting hostname to IP
    address and back.
**}
unit PwfDns;

{$MODE objfpc}
{$H+}

interface

uses BaseUnix, NetDB, SysUtils, Sockets;

type
  TDns =
    class
      public
        function StrToHostAddr(IP: string): Cardinal;
        function HostAddrToStr(Entry: Cardinal): string;
        function StrToNetAddr(IP: string): Cardinal;
        function NetAddrToStr(Entry: Cardinal): string;
        function GetHostName(const Address: string): string;
        function GetHostIP(const Name: string): string;
    end;

var Dns : TDns;

implementation

function TDns.NetAddrToStr (Entry : Cardinal) : string;
type THostAddr = array[1..4] of Byte;
Var Dummy : string[4];
    I : LongInt;
begin
  NetAddrToStr:='';
  For I:=4 Downto 1 do
   begin
   Dummy:='';
   Str(THostAddr(Entry)[I],Dummy);
   NetAddrToStr:=NetAddrToStr+Dummy;
   If I>1 Then NetAddrToStr:=NetAddrToStr+'.';
   end;
end;

function TDns.StrToNetAddr(IP : string) : Cardinal;
type THostAddr = array[1..4] of Byte;
Var Dummy : string[4];
   I : LongInt;
   J : Integer;
   Temp : THostAddr;
begin
 Result:=0;
 For I:=1 to 4 do
  begin
  If I<4 Then
    begin
    J:=Pos('.',IP);
    If J=0 then exit;
    Dummy:=Copy(IP,1,J-1);
    Delete (IP,1,J);
    end
  else
    Dummy:=IP;
  Val (Dummy, Temp[I], J);
  If J<>0 then Exit;
  end;
 Result:=Cardinal(Temp);
end;

function TDns.HostAddrToStr (Entry : Cardinal) : string;
type THostAddr = array[1..4] of Byte;
var Dummy : string[4];
    I : LongInt;
begin
  HostAddrToStr:='';
  For I:=1 to 4 do
   begin
   Dummy:='';
   Str(THostAddr(Entry)[I],Dummy);
   HostAddrToStr:=HostAddrToStr+Dummy;
   If I < 4 Then HostAddrToStr:=HostAddrToStr+'.';
   end;
end;

function TDns.StrToHostAddr(IP: string): Cardinal;
type THostAddr = array[1..4] of Byte;
var Dummy : string[4];
   I : LongInt;
   J : Integer;
   Temp : THostAddr;
begin
 Result:=0;
 For I:=4 downto 1 do
  begin
  If I > 1 Then
    begin
    J:=Pos('.',IP);
    If J=0 then exit;
    Dummy:=Copy(IP,1,J-1);
    Delete (IP,1,J);
    end
  else
    Dummy:=IP;
  Val (Dummy, Temp[I], J);
  If J <> 0 then Exit;
  end;
 Result:=Cardinal(Temp);
end;

function TDns.GetHostName(const Address: string): string;
var
  HE: THostEntry;
begin
  Result:='';
  if GetHostbyAddr(in_addr(StrToHostAddr(Address)), HE) then
    Result:=HE.Name
  else if ResolveHostbyAddr(in_addr(StrToHostAddr(Address)), HE) then
    Result:=HE.Name;
end;

function TDns.GetHostIP(const Name: string): string;
var
  HE: THostEntry;
begin
  Result:='';
  if GetHostByName(Name, HE) then
    Result:=NetAddrToStr(Cardinal(HE.Addr)) // for localhost
  else if ResolveHostByName(Name, HE) then
    Result:=HostAddrToStr(Cardinal(HE.Addr));
end;

initialization

  dns := TDns.Create;

finalization

  dns.Free;

end.
