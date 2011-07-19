unit inet_thread_daemon;

{$mode objfpc}{$H+}

interface
uses Classes,Sockets,BaseUnix,errors,logs;
const
    testSize = 92;
    RemoteAddress ='127.0.0.1';
    Port=6549;

type
  MyInet = class(TThread)
  private
         Buffer   : string[255];
         Addr     : TInetSockAddr;
         S        : Longint;
         Sin,Sout : Text;
         function SwapWord(w:word):word;
        logs:Tlogs;
         procedure Perror (const z:string);


  protected
    procedure Execute; override;


  public
        Constructor Create;
end;

implementation

Constructor MyInet.Create;
begin
  FreeOnTerminate:=true;
  inherited create(false);
end;


procedure MyInet.Execute;
var  t:integer;
begin
    t:=10228;
  logs:=Tlogs.Create;
  logs.logs('MyInet.Execute -> Start inet');
  Addr.family:=AF_INET;
  Addr.port:=SwapWord(Port);
  Addr.addr:=0;  //Le serveur ecoute sur toute la plage IP
 {création du socket - com reseau - mode connecté}
 { S va contenir le num indentification du socket}
 S:=Socket (AF_INET,SOCK_STREAM,0);
 if SocketError<>0 then Perror ('Serveur : Erreur Socket : ');
 SetSocketOptions(S,SOL_SOCKET,SO_REUSEADDR,1,sizeof(1));
 {Fourni au socket S les infos de connexion}
 if not Bind(S,Addr,sizeof(Addr))
 then Perror ('Serveur : Erreur Bind : ');

 {mise en ecoute en mode passif}
 if not Listen (S,1) then  Perror ('Serveur : Erreur Listen : ');
 Writeln('J''attend la connection du Client (le lancer dans une autre console)');

 repeat           {accepter une connexion d'un client}
   if not Accept (S,Addr,Sin,Sout) then Perror ('Serveur : Erreur Accept :');
   Reset(Sin);
   ReWrite(Sout);

   Writeln(Sout,'Message du Serveur');
   Flush(SOut);
   repeat
     Readln(Sin,Buffer);
     Writeln('le Server a recu : ',buffer);
   until (eof(sin))or(buffer='FIN');




   if terminated then break;
 until (buffer='FIN');
 writeln('Closesocket');
 Shutdown(S,2);
 closeSocket(S);
 if not terminated then Execute;
 logs.logs('MyInet.Execute ->socket closed');
end;

 function MyInet.SwapWord(w:word):word;
  begin SwapWord:=((w and $ff) shl 8)+(w shr 8); end;

 procedure MyInet.Perror (const z:string);
begin
  writeln(S,strerror(SocketError),'(',SocketError,')');
//  halt(100);
end;

end.
