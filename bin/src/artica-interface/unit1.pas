unit Unit1; 

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils, LResources, Forms, Controls, Graphics, Dialogs, ExtCtrls,
  StdCtrls,lighttpd,Zsystem,unix,BaseUnix,openldap,global_conf,unit2,debian_class;

type

  { TForm1 }

  TForm1 = class(TForm)
    Button1: TButton;
    Button10: TButton;
    Button2: TButton;
    Button3: TButton;
    Button4: TButton;
    Button5: TButton;
    Button6: TButton;
    Button7: TButton;
    Button8: TButton;
    Button9: TButton;
    locked: TEdit;
    host: TLabel;
    temperature: TLabel;
    version: TLabel;
    httpserver_port: TLabel;
    certificate: TLabel;
    artica_daemon_status: TLabel;
    Label8: TLabel;
    Label9: TLabel;
    username: TLabel;
    Image1: TImage;
    Label1: TLabel;
    httpserver_status: TLabel;
    Label2: TLabel;
    Label3: TLabel;
    Label4: TLabel;
    Label5: TLabel;
    Label6: TLabel;
    Label7: TLabel;
    Timer1: TTimer;
    password: TLabel;
    version1: TLabel;
    version2: TLabel;
    keyboard_info: TLabel;
    version3: TLabel;
    version4: TLabel;
    procedure Button10Click(Sender: TObject);
    procedure Button1Click(Sender: TObject);
    procedure Button2Click(Sender: TObject);
    procedure Button3Click(Sender: TObject);
    procedure Button4Click(Sender: TObject);
    procedure Button5Click(Sender: TObject);
    procedure Button6Click(Sender: TObject);
    procedure Button7Click(Sender: TObject);
    procedure Button8Click(Sender: TObject);
    procedure Button9Click(Sender: TObject);
    procedure FormCreate(Sender: TObject);
    procedure Image1Click(Sender: TObject);
    procedure StaticText2Click(Sender: TObject);
    procedure Timer1Timer(Sender: TObject);
  private

  public
    { public declarations }
  end; 

var
  Form1: TForm1;


implementation

{ TForm1 }

procedure TForm1.Timer1Timer(Sender: TObject);
  var
  zlighttpd:Tlighttpd;
  openldp:Topenldap;
  SYS:tsystem;
  zmyconf:myconf;
  deb:Tdebian;
  locked_password:string;
begin
   zlighttpd:=Tlighttpd.Create;
   openldp:=Topenldap.Create;
   SYS:=TSystem.Create;
   zmyconf:=myconf.Create();
   deb:=tdebian.Create;
   
   locked_password:=zmyconf.get_INFOS('artica_interface_locked_passwd');
   deb.remove_bip();
   if Not FileExists('/etc/artica-postfix/first.boot.install') then begin
      fpsystem('/etc/init.d/artica-postfix stop');
      fpsystem('/etc/init.d/artica-postfix start');
      fpsystem('/bin/touch /etc/artica-postfix/first.boot.install');
   end;
   
   if length(locked_password)>0 then begin
      if locked.Text='1' then begin
           username.Visible:=false;
           password.Visible:=false;
      end else begin
         username.Visible:=true;
         password.Visible:=true;
      end;
   end;
   
   httpserver_status.Caption:=zlighttpd.CACHE_STATUS();
   temperature.Caption:=SYS.materiel_get_temperature();
   httpserver_port.Caption:=zlighttpd.LIGHTTPD_LISTEN_PORT();
   certificate.Caption:=zlighttpd.LIGHTTPD_CERTIFICATE_PATH();
   Label3.Caption:=SYS.ifconfig_text();
   Label6.Caption:=SYS.https_uris(httpserver_port.Caption);
   username.Caption:=openldp.get_LDAP('admin');
   password.caption:=openldp.get_LDAP('password');
   version.Caption:=zmyconf.ARTICA_VERSION();
   host.caption:=zmyconf.LINUX_GET_HOSTNAME;
   keyboard_info.Caption:=deb.keyboard_language();

   if not SYS.PROCESS_EXIST(zmyconf.ARTICA_DAEMON_GET_PID()) then begin
      artica_daemon_status.Caption:='Artica daemon is stopped';
      Button1.Caption:='start artica';
   end else  begin
     artica_daemon_status.Caption:='Artica daemon is running PID '+zmyconf.ARTICA_DAEMON_GET_PID();
     Button1.Caption:='stop artica';
   end;
   
   
   zlighttpd.free;
   openldp.free;
   SYS.free;
   zmyconf.free;
   
end;

procedure TForm1.StaticText2Click(Sender: TObject);
begin

end;

procedure TForm1.Button1Click(Sender: TObject);
var
  SYS:tsystem;
  zmyconf:myconf;

begin
     SYS:=TSystem.Create;
    zmyconf:=myconf.Create();
if not SYS.PROCESS_EXIST(zmyconf.ARTICA_DAEMON_GET_PID()) then begin
      fpsystem('/etc/init.d/artica-postfix start');
      Application.MessageBox('Starting artica executed','Starting artica',1);
   end else  begin
     fpsystem('exo-open --launch TerminalEmulator "/etc/init.d/artica-postfix stop"');
   end;

   SYS.free;
   zmyconf.free;
   


end;

procedure TForm1.Button10Click(Sender: TObject);
  var
  zmyconf:myconf;
  locked_password:string;
  naswer:string;
  cont:boolean;
begin
cont:=false;
zmyconf:=myconf.Create;
locked_password:=zmyconf.get_INFOS('artica_interface_locked_passwd');
if length(locked_password)=0 then begin
 locked_password := InputBox ('Set password','Please type the password to unlock/lock the admin/password text', '');
 zmyconf.set_INFOS('artica_interface_locked_passwd',locked_password);
 cont:=true;
end;


if not cont then begin
   if InputQuery ('lock/unlock password', 'Give the password', TRUE, naswer) then begin
      if naswer=locked_password then cont:=true;
   end;
end;


if not cont then exit;


 if locked.Text='1' then begin
    locked.Text:='0';
 end else begin
     locked.Text:='1';
 end;
 
      if locked.Text='1' then begin
           username.Visible:=false;
           password.Visible:=false;
      end else begin
         username.Visible:=true;
         password.Visible:=true;
      end;

end;

procedure TForm1.Button2Click(Sender: TObject);
begin
  fpsystem('reboot');
end;

procedure TForm1.Button3Click(Sender: TObject);
var    zlighttpd:Tlighttpd;
begin
    zlighttpd:=Tlighttpd.Create;
    fpsystem('iceape https://127.0.0.1:'+zlighttpd.LIGHTTPD_LISTEN_PORT());
   zlighttpd.free;
end;

procedure TForm1.Button4Click(Sender: TObject);
begin
    fpsystem('exo-open --launch TerminalEmulator /etc/init.d/artica-postfix stop apache');
    fpsystem('exo-open --launch TerminalEmulator /etc/init.d/artica-postfix start apache');
end;

procedure TForm1.Button5Click(Sender: TObject);
begin
  fpsystem('exo-open --launch TerminalEmulator "tail -f /var/log/artica-postfix/artica-install.debug" &');
end;

procedure TForm1.Button6Click(Sender: TObject);
begin
     fpsystem('exo-open --launch TerminalEmulator "tail -f /var/log/lighttpd/access.log" &');
     fpsystem('exo-open --launch TerminalEmulator "tail -f /var/log/lighttpd/error.log" &');
end;

procedure TForm1.Button7Click(Sender: TObject);
var
   form2:Tform2;
begin
form2:=Tform2.Create(nil);
form2.Show;

end;

procedure TForm1.Button8Click(Sender: TObject);
begin
   fpsystem('exo-open --launch TerminalEmulator "/usr/share/artica-postfix/bin/artica-update --update --force --screen --verbose " & ');
   fpsystem('exo-open --launch TerminalEmulator "tail -f /var/log/artica-postfix/artica-update.debug " & ');
end;

procedure TForm1.Button9Click(Sender: TObject);
var
  deb:Tdebian;
begin
     DEB:=tDEBIAN.Create;
    deb.keyboardTofr();
    Application.MessageBox('Settings will take effect after rebooting XFCE...','Keyboard to french',1);

end;

procedure TForm1.FormCreate(Sender: TObject);
begin

   if Not FileExists('/etc/artica-postfix/first.boot.install') then begin
      fpsystem('/etc/init.d/artica-postfix stop');
      fpsystem('/etc/init.d/artica-postfix start');
      fpsystem('/bin/touch /etc/artica-postfix/first.boot.install');
   end;
fpsystem('iceape http://127.0.0.1:47980/index.php -width=780 heigth=640 &');
halt(0);
end;

procedure TForm1.Image1Click(Sender: TObject);
begin

end;

initialization
  {$I unit1.lrs}

end.

