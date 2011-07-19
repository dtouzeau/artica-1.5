unit Unit2; 

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils, LResources, Forms, Controls, Graphics, Dialogs, StdCtrls,debian_class,BaseUnix,unix,global_conf;

type

  { TForm2 }

  TForm2 = class(TForm)
    address: TEdit;
    Button5: TButton;
    hostname_text: TEdit;
    Button4: TButton;
    DNS1: TEdit;
    DNS2: TEdit;
    Label3: TLabel;
    Label4: TLabel;
    Label5: TLabel;
    Label6: TLabel;
    Label7: TLabel;
    mask: TEdit;
    Label1: TLabel;
    Label2: TLabel;
    gateway: TEdit;
    procedure Button4Click(Sender: TObject);
    procedure Button5Click(Sender: TObject);
    procedure DNS1Change(Sender: TObject);
    procedure FormCreate(Sender: TObject);
  private
    { private declarations }
  public
    { public declarations }
  end; 

var
  Form2: TForm2; 

implementation

{ TForm2 }

procedure TForm2.DNS1Change(Sender: TObject);
begin

end;

procedure TForm2.Button4Click(Sender: TObject);
var l:TstringList;
begin
l:=TstringList.Create;
l.Add('# The loopback network interface');
l.Add('auto lo');
l.Add('iface lo inet loopback');
l.add('');
l.add('# The primary network interface');
l.Add('iface eth0 inet static');
l.Add('  address '+address.Text);
l.Add('  netmask '+mask.Text);
l.Add('  gateway '+gateway.Text);
l.add('');

l.SaveToFile('/etc/network/interfaces');
l.clear;

l.Add('nameserver ' +DNS1.Text);
l.Add('nameserver ' +DNS2.Text);

l.SaveToFile('/etc/resolv.conf');
l.free;
fpsystem('/sbin/ifdown eth0');
fpsystem('/etc/init.d/networking restart');
fpsystem('/sbin/ifup eth0');

application.MessageBox('Done...','change eth0 interface',1);
fpsystem('/sbin/ifup eth0');

end;

procedure TForm2.Button5Click(Sender: TObject);
var deb:Tdebian;
    net:networks_settings;
    global_ini:myconf;
begin
 global_ini:=myconf.Create();
 global_ini.SYSTEM_SET_HOSTENAME(hostname_text.Text);
 
end;



procedure TForm2.FormCreate(Sender: TObject);
var deb:Tdebian;
    net:networks_settings;
    global_ini:myconf;
begin
    global_ini:=myconf.Create();
    deb:=Tdebian.Create;
    net:=deb.LOAD_INTERFACES();
    hostname_text.Text:=global_ini.LINUX_GET_HOSTNAME();
    if length(net.gateway)>0 then  gateway.Text:=net.gateway;
    if length(net.ip)>0 then  address.Text:=net.ip;
    if length(net.netmask)>0 then  mask.Text:=net.netmask;
    if length(net.dns1)>0 then  DNS1.Text:=net.dns1;
    if length(net.dns2)>0 then  DNS2.Text:=net.dns2;
end;

initialization
  {$I unit2.lrs}

end.

