unit echo;

interface

uses
  Classes, blcksock, synsock,logs in 'logs.pas';

type
  TTCPEchoDaemon = class(TThread)
  private
    Sock:TTCPBlockSocket;
  public
    Constructor Create;
    Destructor Destroy; override;
    procedure Execute; override;
  end;

  TTCPEchoThrd = class(TThread)
  private
    Sock:TTCPBlockSocket;
    CSock: TSocket;
  public
    Constructor Create (hsock:tSocket);
    procedure Execute; override;
  end;

implementation

{ TEchoDaemon }

Constructor TTCPEchoDaemon.Create;
begin
  sock:=TTCPBlockSocket.create;
  FreeOnTerminate:=true;
  inherited create(false);
end;

Destructor TTCPEchoDaemon.Destroy;
begin
  Sock.free;
end;

procedure TTCPEchoDaemon.Execute;
var
  ClientSock:TSocket;
begin
  with sock do
    begin
      CreateSocket;
      setLinger(true,10);
      bind('127.0.0.1','29900');
      listen;
      repeat
        if terminated then break;
        if canread(1000) then
          begin
            ClientSock:=accept;
            if lastError=0 then TTCPEchoThrd.create(ClientSock);
          end;
      until false;
    end;
end;

{ TEchoThrd }

Constructor TTCPEchoThrd.Create(Hsock:TSocket);
begin
  Csock := Hsock;
  FreeOnTerminate:=true;
  inherited create(false);
end;

procedure TTCPEchoThrd.Execute;
var
  s: string;
  LOGS:TLogs;
begin
  sock:=TTCPBlockSocket.create;
  LOGS:=Tlogs.Create;
  try
    Sock.socket:=CSock;
    sock.GetSins;
    with sock do
      begin
        repeat
          if terminated then break;
          s := RecvPacket(60000);
          if lastError<>0 then break;
          
          SendString(s);
          if lastError<>0 then break;
        until false;
      end;
  finally
    Sock.Free;
  end;
end;

end.
