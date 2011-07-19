unit inetaux;

{
  Auxiliary routines for TCP/IP programming.
  0.2
  25. 4. 2000
  Sebastian Koppehel, <basti@bastisoft.de>
}

interface

{
  Switch between host and network byte order for words and longints.
  Note: These routines assume that you are on an Intel(R) machine.
}

function htons(i : Integer) : Integer;
function ntohs(i : Integer) : Integer;
function hton(l : LongInt) : LongInt;
function ntoh(l : LongInt) : LongInt;

{
  Convert between dotted-decimal and longint ip addresses.
  Note 1: LongInts are in network byte order.
  Note 2: Plain numbers (without dots) are not recognized.
}

function StrToAddr(s : String) : LongInt;
function AddrToStr(addr : LongInt) : String;

implementation

function htons(i : Integer) : Integer;
begin
   htons := lo(i) shl 8 or hi(i);
end;

function ntohs(i : Integer) : Integer;
begin
   ntohs := htons(i);
end;

function hton(l : LongInt) : LongInt;
begin
   hton := (lo(lo(l)) shl 8 or hi(lo(l))) shl 16
     or (lo(hi(l)) shl 8 or hi(hi(l)));
end;

function ntoh(l : LongInt) : LongInt;
begin
   ntoh := hton(l);
end;

function StrToAddr(s : String) : LongInt;
var
   r, i, p, c : LongInt;
   t : String;
begin
   StrToAddr := 0;
   r := 0;
   for i := 0 to 3 do
   begin
      p := Pos('.', s);
      if p = 0 then p := Length(s) + 1;
      if p <= 1 then exit;
      t := Copy(s, 1, p - 1);
      Delete(s, 1, p);
      Val(t, p, c);
      if (c <> 0) or (p < 0) or (p > 255) then exit;
      r := r or p shl (i * 8);
   end;
   StrToAddr := r;
end;

function AddrToStr(addr : LongInt) : String;
var
   r, s : String;
   i : LongInt;
begin
   r := '';
   for i := 0 to 3 do
   begin
      Str(addr shr (i * 8) and $FF, s);
      r := r + s;
      if i < 3 then r := r + '.';
   end;
   AddrToStr := r;
end;

end.

