unit http;
{$mode objfpc}{$H+}
interface

uses
  Classes, blcksock,synsock,  Synautil, SysUtils,logs,parsehttp,global_conf,ssockets;

type
  TTCPHttpDaemon = class(TThread)
  private
    Sock:TTCPBlockSocket;
    logs:Tlogs;

  public
    Stopped:boolean;
    Constructor Create;
    Destructor Destroy; override;
    procedure Execute; override;
  end;

  TTCPHttpThrd = class(TThread)
  private
    Sock:TTCPBlockSocket;
  public
    Headers: TStringList;
    InputData, OutputData: TMemoryStream;
    Stopit:boolean;
    Constructor Create (hsock:tSocket);
    Destructor Destroy; override;
    procedure Execute; override;

    function ProcessHttpRequest(Request, URI: string): integer;
  end;

implementation

{ TTCPHttpDaemon }

Constructor TTCPHttpDaemon.Create;
begin
  logs:=Tlogs.Create;
  Stopped:=false;
  logs.logs('TTCPHttpDaemon starting initialize server');
  sock:=TTCPBlockSocket.create;
  FreeOnTerminate:=true;
  Priority:=tpNormal;
  
  inherited create(false);
end;

Destructor TTCPHttpDaemon.Destroy;
begin
  logs.logs('TTCPHttpDaemon destroy thread');
  logs.free;
  Sock.free;
  Stopped:=True;
  inherited Destroy;
end;

procedure TTCPHttpDaemon.Execute;
var
  ClientSock:TSocket;
  GLOBAL_INI:myconf;
  LocalPort:integer;
  LocalPortStr:string;
  Count_error_98:integer;
begin
  GLOBAL_INI:=myconf.Create;
  LocalPort:=GLOBAL_INI.get_ARTICA_LOCAL_PORT();
  LocalPortStr:=IntToStr(LocalPort);
  Count_error_98:=0;
  
  with sock do begin
//      CreateSocket;
      setLinger(true,10);
      EnableReuse(true);

      logs.logs('TTCPHttpDaemon starting binding port server Er (' + IntToStr(LastError) + ')');
      
      bind(GLOBAL_INI.get_ARTICA_LISTEN_IP(),LocalPortStr);

       if lastError>0 then  //-------------------------------------
         if lastError<>22 then Logs.logs('TTCPHttpDaemon error Listen mode.:' + GLOBAL_INI.get_ARTICA_LISTEN_IP() + ':' + intToStr(GLOBAL_INI.get_ARTICA_LOCAL_PORT()) + ' Error Number :' + IntTostr(lastError) + ' ' + LastErrorDesc );

          if lastError=98 then begin
            repeat
                Count_error_98:=Count_error_98+1;
                if Count_error_98>0 then begin
                       LocalPort:=LocalPort+1;
                       LocalPortStr:=IntToStr(LocalPort);
                       Logs.logs('TTCPHttpDaemon Watch dog Listen mode SIN Number try to bind a new port (' + LocalPortStr + ')' );
                       GLOBAL_INI.SET_ARTICA_LOCAL_SECOND_PORT(LocalPort);
                end;
                
                setLinger(true,10);
                bind(GLOBAL_INI.get_ARTICA_LISTEN_IP(),LocalPortStr);
                Logs.logs('TTCPHttpDaemon Watch dog Listen mode SIN Number ERROR on :'+ GLOBAL_INI.get_ARTICA_LISTEN_IP() + ':' + LocalPortStr + ': '  + IntTostr(lastError) + ' ' + LastErrorDesc );

                 if FileExists('/etc/artica-postfix/shutdown') then begin
                    Logs.logs('TTCPHttpDaemon terminate loop error 98...');
                    Logs.logs('TTCPHttpDaemon terminate by reading the terminate file "/etc/artica-postfix/shutdown"..');
                    break;
                 end;

                if terminated then begin
                   Logs.logs('TTCPHttpDaemon terminate loop error 98...');
                   Logs.logs('TTCPHttpDaemon Execute free instances...');
                   GLOBAL_INI.Free;
                   exit;
                end;
            until lastError<>98
         end;//-------------------------------------
      

      listen;
      if LocalPort=GLOBAL_INI.get_ARTICA_LOCAL_PORT() then begin
         GLOBAL_INI.SET_ARTICA_LOCAL_SECOND_PORT(0);
         Logs.logs('TTCPHttpDaemon port is not changed disable second port');
      end;
      
      Logs.logs('TTCPHttpDaemon Listen mode.:' + GLOBAL_INI.get_ARTICA_LISTEN_IP() + ':' + LocalPortStr);
      repeat

       if FileExists('/etc/artica-postfix/shutdown') then begin
           Logs.logs('TTCPHttpDaemon terminate by reading the terminate file "/etc/artica-postfix/shutdown"..');
           AbortSocket;
           terminate;
           break;
       end;
       
        if terminated then begin
           Logs.logs('TTCPHttpDaemon terminate...');
           break;
        end;
      if not FileExists('/etc/artica-postfix/shutdown') then begin
        if canread(1000) then begin
            ClientSock:=accept;
            if lastError=0 then begin
               logs.logs('TTCPHttpDaemon Create new thread');
               TTCPHttpThrd.create(ClientSock);
               Logs.logs('TTCPHttpDaemon THREAD SUCCESS...');

            end;
        end;
      end;

          if lastError>0 then Logs.logs('TTCPHttpDaemon fatal error loop');


      until false;
    end;
     Logs.logs('TTCPHttpDaemon Execute free instances...');
    GLOBAL_INI.Free;
end;

{ TTCPHttpThrd }

Constructor TTCPHttpThrd.Create(Hsock:TSocket);
begin
  if FileExists('/etc/artica-postfix/shutdown') then exit;
  sock:=TTCPBlockSocket.create;
  Headers := TStringList.Create;
  InputData := TMemoryStream.Create;
  OutputData := TMemoryStream.Create;

  Sock.socket:=HSock;
  FreeOnTerminate:=true;
  Priority:=tpNormal;
  inherited create(false);
end;

Destructor TTCPHttpThrd.Destroy;
var     logs:Tlogs;
begin
  logs:=Tlogs.Create;
  logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] Destroy');
  logs.Free;
  Sock.free;
  Headers.Free;
  InputData.Free;
  OutputData.Free;
  inherited Destroy;
end;

procedure TTCPHttpThrd.Execute;
var
  b: byte;
  timeout: integer;
  s: string;
  method, uri, protocol: string;
  size: integer;
  x, n: integer;
  resultcode: integer;
  logs:Tlogs;
begin
  if FileExists('/etc/artica-postfix/shutdown') then exit;
  logs:=Tlogs.Create;
  logs.logs('TTCPHttpThrd:[' + IntToStr(GetCurrentThreadID)+ '] start tread' );
  timeout := 120000;
  s := sock.RecvString(timeout);
  if sock.lasterror <> 0 then  begin
    logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] Receive error '+ IntToStr(sock.lasterror) + ' '  + sock.LastErrorDesc  );
    Exit;
  end;
  
  
  logs.logs('TTCPHttpThrd:[' + IntToStr(length(s))+ '] caracters '  );
  if s = '' then  Exit;
  method := fetch(s, ' ');
  if (s = '') or (method = '') then begin
       logs.logs('TTCPHttpThrd:[method=null] exit '  );
     Exit;
  end;

  uri := fetch(s, ' ');
  if uri = '' then begin
     logs.logs('TTCPHttpThrd:[URI=null] exit '  );
     Exit;
  end;
  
  
  protocol := fetch(s, ' ');
  headers.Clear;
  logs.logs('TTCPHttpThrd:[protocol=' + protocol+ ']'  );
  logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] Receive from ' + sock.GetRemoteSinIP);
  size := -1;

  //read request headers
  if protocol <> '' then begin
    if pos('HTTP/', protocol) <> 1 then  Exit;
    repeat
      s := sock.RecvString(Timeout);
      if sock.lasterror <> 0 then Exit;
      if s <> '' then Headers.add(s);
      if Pos('CONTENT-LENGTH:', Uppercase(s)) = 1 then Size := StrToIntDef(SeparateRight(s, ' '), -1);
    until s = '';
  end;
  
  
  //recv document...
  InputData.Clear;
  if size >= 0 then begin
    InputData.SetSize(Size);
    x := Sock.RecvBufferEx(InputData.Memory, Size, Timeout);
    InputData.SetSize(x);
    if sock.lasterror <> 0 then Exit;
  end;
  
  
  OutputData.Clear;
  ResultCode := ProcessHttpRequest(method, uri);

  sock.SendString('HTTP/1.0 ' + IntTostr(ResultCode) + CRLF);

  if protocol <> '' then begin
    headers.Add('Content-length: ' + IntTostr(OutputData.Size));
    headers.Add('Connection: close');
    headers.Add('Date: ' + Rfc822DateTime(now));
    headers.Add('Server: Artica Daemon HTTP server');
    headers.Add('');
    for n := 0 to headers.count - 1 do
      sock.sendstring(headers[n] + CRLF);
  end;
  if sock.lasterror <> 0 then begin
     if sock.LastError<>104 then begin
        logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] ERROR: '+ IntToStr(sock.lasterror) + ' '  + sock.LastErrorDesc  );
        Exit;
     end;
      if sock.LastError=104 then begin
        logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ ']ERROR:'+ IntToStr(sock.lasterror) + ' '  + sock.LastErrorDesc  );
      end;
  end;
  
  logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] SEND ' + IntTostr(OutputData.size) + ' bytes');
  Sock.SendBuffer(OutputData.Memory, OutputData.Size);

  if sock.lasterror>0 then  logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] INFOS '+ IntToStr(sock.lasterror) + ' '  + sock.LastErrorDesc  );
  Sock.AbortSocket;
  sock.CloseSocket;
  logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] socket closed...');

  terminate;
end;

function TTCPHttpThrd.ProcessHttpRequest(Request, URI: string): integer;
var
  parse:Tparsehttp;
  res:boolean;
  logs:Tlogs;
begin
    if FileExists('/etc/artica-postfix/shutdown') then exit;
    logs:=Tlogs.Create;
    parse:=Tparsehttp.Create;
    res:=parse.ParseUri(URI);
    logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] ProcessHttpRequest: processing send datas to socket...');
    
    result := 504;
  if request = 'GET' then begin
    headers.Clear;
    headers.Add('Content-type: Text/Html');
    logs.logs('TTCPHttpThrd:[' + IntToStr(ThreadID)+ '] ProcessHttpRequest: send '+ IntToStr(parse.FileData.Count) + ' lines to socket'  );
    
    if res=true then try
       parse.FileData.SaveToStream(OutputData);
    finally
      parse.Free;
      logs.Free;
    end;
    Result := 200;
  end;
end;

end.
