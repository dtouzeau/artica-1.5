unit wmdump;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils, LResources, cgiModules,global_conf,RegExpr,lighttpd,zsystem,debian_class,openldap,
  artica_cron in '/home/dtouzeau/developpement/artica-postfix/bin/src/artica-install/artica_cron.pas';

type

  { TDemoModule }

  TDemoModule = class(TCGIDatamodule)
    procedure DemoModuleCGIRequest(Sender: TObject);
  private
    GLOBAL_INI:MyConf;
    Reg:TRegExpr;
    SYS:Tsystem;
    deb:tdebian;
    openldp:Topenldap;
    cron:tcron;
    lighttp:Tlighttpd;
    procedure EmitRequestVariables;
    procedure EmitServerVariables;
    function  Welcome():string;
    function  EnterPage():string;
    function GetRequestVariables(xname:string):string;
    procedure ParsePOST();
    { private declarations }
  public
    { public declarations }
    procedure EmitVariable(Const VarName,VarValue : String);
  end; 

var
  DemoModule: TDemoModule;

implementation

uses cgiapp;

{ TDemoModule }

{
  The OnCGIRequest handler is called to handle the request
}
procedure TDemoModule.DemoModuleCGIRequest(Sender: TObject);
   var page:string;
begin
  // Emit content type (See ContentType property of the module)
  EmitContentType;
  GLOBAL_INI:=myconf.Create();
  Reg:=TRegExpr.Create;
  SYS:=Tsystem.Create();
  deb:=tdebian.Create();
  openldp:=Topenldap.Create;
  cron:=tcron.Create(SYS);
  page:='';
  
  
page:=Welcome();
AddResponseLn('<HTML>');
AddResponseLn('<TITLE>Artica '+GLOBAL_INI.ARTICA_VERSION() +' Internal interface</TITLE>');
AddResponseLn('<link href="../css/styles_main.css" rel="styleSheet" type="text/css" />');
AddResponseLn('<link href="../css/styles_header.css" rel="styleSheet" type="text/css" />');
AddResponseLn('<link href="../css/styles_middle.css" rel="styleSheet" type="text/css" />');
AddResponseLn('<link href="../css/styles_forms.css" rel="styleSheet" type="text/css" />');
AddResponseLn('<link href="../css/styles_tables.css" rel="styleSheet" type="text/css" />');
AddResponseLn('<body class="yui-skin-sam">');
AddResponseLn('<div id="header"><div style="float:right;top:50px"><img src="../images/rght.gif"></div>');
AddResponseLn('	<div id="menus_1">');
AddResponseLn('		<span class="left"><a href="index.cgi"><img src="../css/images/logo.gif" alt="" /></a></span><span class=left></span>');
AddResponseLn('	<ul>');
AddResponseLn('		<li></li>');
AddResponseLn('		<li></li>');
AddResponseLn('		<li><a href="http://www.artica.fr/forum/viewforum.php?f=3">Community</a></li>');
AddResponseLn('		<li><a href="http://www.artica.fr/forum/index.php">Support</a></li>');
AddResponseLn('		<li><a href="index.cgi">Home</a></li>');
AddResponseLn('	</ul>');
AddResponseLn('	</div id="nav_primary">');
AddResponseLn('	<div id="menus_2">');
AddResponseLn('	<ul>');
AddResponseLn('	</ul>');
AddResponseLn('	</div id="menus_2">');
AddResponseLn('</div id="header">');
AddResponseLn('<div id="middle">');
AddResponseLn('	<div id="content">');
AddResponseLn('		<table style="width:100%">');
AddResponseLn('			<tr>');
AddResponseLn('				<td valign="top" style="padding:0px;margin:0px;width:160px">');
AddResponseLn('				</td>');
AddResponseLn('				<td valign="top" style="padding-left:3px">');
AddResponseLn('						');
AddResponseLn('					<div id="BodyContent">');
AddResponseLn('					<h1 id="template_title">Artica-postfix '+GLOBAL_INI.ARTICA_VERSION()+ ' internal administration</h1>');
AddResponseLn('					<!-- content -->');
AddResponseLn(page);
AddResponseLn('					<!-- content end -->');
AddResponseLn('					</div>');
AddResponseLn('				</td>');
AddResponseLn('');
AddResponseLn('				<td valign="top"></td>');
AddResponseLn('			</tr>	');
AddResponseLn('	</table>	');
AddResponseLn('');
AddResponseLn('	<div class="clearleft"></div>');
AddResponseLn('	<div class="clearright"></div>');
AddResponseLn('	</div id="content">');
AddResponseLn('</div id="middle">');
AddResponseLn('');
AddResponseLn('<div id="footer">');
AddResponseLn('');
AddResponseLn('<div id="nav_bottom">');
AddResponseLn('<ul>');
AddResponseLn('');
AddResponseLn('</ul>');
AddResponseLn('</div id="nav_bottom">');
AddResponseLn('');
AddResponseLn('<div id="copyright">');
AddResponseLn('<p class="note">Artica for postfix.</p>');
AddResponseLn('</div id="copyright">');
AddResponseLn('');
AddResponseLn('</div id="footer"></div>');
AddResponseLn('');
AddResponseLn('<script>');
AddResponseLn('YAHOO.namespace("example.container");');
AddResponseLn('');
AddResponseLn('function init() {');
AddResponseLn('	');
AddResponseLn('');
AddResponseLn('	// Instantiate the Dialog');
AddResponseLn('	YAHOO.example.container.dialog0= new YAHOO.widget.Dialog("dialog0", ');
AddResponseLn('							{ width : "750px",');
AddResponseLn('							  xy:[100,100],');
AddResponseLn('							  fixedcenter : false,');
AddResponseLn('							  visible : false, ');
AddResponseLn('							  constraintoviewport : true,');
AddResponseLn('							  draggable:true,');
AddResponseLn('							  close:true,');
AddResponseLn('						          modal :false,');
AddResponseLn('							iframe :false,');
AddResponseLn('							');
AddResponseLn('							 ');
AddResponseLn('							});');
AddResponseLn('');
AddResponseLn('');
AddResponseLn('	YAHOO.example.container.dialog1 = new YAHOO.widget.Dialog("dialog1", ');
AddResponseLn('							{ width : "30em",');
AddResponseLn('							  fixedcenter : true,');
AddResponseLn('							  visible : false, ');
AddResponseLn('							  constraintoviewport : true,');
AddResponseLn('							 ');
AddResponseLn('							});');
AddResponseLn('							');
AddResponseLn('	YAHOO.example.container.dialog2 = new YAHOO.widget.Dialog("dialog2", ');
AddResponseLn('							{ width : "30em",');
AddResponseLn('							  fixedcenter : true,');
AddResponseLn('							  visible : false, ');
AddResponseLn('							  constraintoviewport : true,');
AddResponseLn('							 ');
AddResponseLn('							});');
AddResponseLn('');
AddResponseLn('	YAHOO.example.container.dialog3 = new YAHOO.widget.Dialog("dialog3", ');
AddResponseLn('							{ width : "30em",');
AddResponseLn('							  fixedcenter : true,');
AddResponseLn('							  visible : false, ');
AddResponseLn('							  constraintoviewport : true,');
AddResponseLn('							 ');
AddResponseLn('							});								');
AddResponseLn('	');
AddResponseLn('	// Render the Dialog');
AddResponseLn('	YAHOO.example.container.dialog0.render();');
AddResponseLn('	YAHOO.example.container.dialog1.render();');
AddResponseLn('	YAHOO.example.container.dialog2.render();');
AddResponseLn('	YAHOO.example.container.dialog3.render();');
AddResponseLn('');
AddResponseLn('	');
AddResponseLn('}');
AddResponseLn('');
AddResponseLn('YAHOO.util.Event.onDOMReady(init);');
AddResponseLn('</script>');
AddResponseLn('<div id="dialog0">');
AddResponseLn('	<div class="hd" id="dialog0_title"></div>');
AddResponseLn('');
AddResponseLn('	<div class="bd" id="dialog0_content">');
AddResponseLn('	</div>');
AddResponseLn('</div> ');
AddResponseLn('');
AddResponseLn('');
AddResponseLn('<div id="dialog1">');
AddResponseLn('	<div class="hd" id="dialog1_title"></div>');
AddResponseLn('	<div class="bd" id="dialog1_content">');
AddResponseLn('	</div>');
AddResponseLn('</div>');
AddResponseLn('<div id="dialog2">');
AddResponseLn('	<div class="hd" id="dialog2_title"></div>');
AddResponseLn('	<div class="bd" id="dialog2_content">');
AddResponseLn('');
AddResponseLn('	</div>');
AddResponseLn('</div> ');
AddResponseLn('<div id="dialog3">');
AddResponseLn('	<div class="hd" id="dialog3_title"></div>');
AddResponseLn('	<div class="bd" id="dialog3_content">');
AddResponseLn('	</div>');
AddResponseLn('</div>  ');
AddResponseLn('</body>');
AddResponseLn('</html>');

//  EmitServerVariables;
//  AddResponseLn('<HR/>');
//  EmitRequestVariables;
end;

procedure TDemoModule.EmitServerVariables;

begin
  AddResponseLn('<H1>CGI Server environment:</H1>');
  AddResponseLn('<TABLE>');
  AddResponseLn('<TR><TH>Variable</TH><TH>Value</TH></TR>');
  // Server environment is accessible as properties of the Application class.
  // The same list can be retrieved as a name=value stringlist with the
  // GetCGIVarList call.
  EmitVariable('AuthType',Application.AuthType);
  EmitVariable('ContentLength',IntToStr(Application.ContentLength));
  EmitVariable('ContentType',Application.ContentType);
  EmitVariable('GatewayInterface',Application.GatewayInterface);
  EmitVariable('PathInfo',Application.PathInfo);
  EmitVariable('PathTranslated',Application.PathTranslated);
  EmitVariable('QueryString',Application.QueryString);
  EmitVariable('RemoteAddress',Application.RemoteAddress);
  EmitVariable('RemoteHost',Application.RemoteHost);
  EmitVariable('RemoteIdent',Application.RemoteIdent);
  EmitVariable('RemoteUser',Application.RemoteUser);
  EmitVariable('RequestMethod',Application.RequestMethod);
  EmitVariable('ScriptName',Application.ScriptName);
  EmitVariable('ServerName',Application.ServerName);
  EmitVariable('ServerPort',IntToStr(Application.ServerPort));
  EmitVariable('ServerProtocol',Application.ServerProtocol);
  EmitVariable('ServerSoftware',Application.ServerSoftware);
  EmitVariable('HTTPAccept',Application.HTTPAccept);
  EmitVariable('HTTPAcceptCharset',Application.HTTPAcceptCharset);
  EmitVariable('HTTPAcceptEncoding',Application.HTTPAcceptEncoding);
  EmitVariable('HTTPIfModifiedSince',Application.HTTPIfModifiedSince);
  EmitVariable('HTTPReferer',Application.HTTPReferer);
  EmitVariable('HTTPUserAgent',Application.HTTPUserAgent);
  EmitVariable('Email',Application.Email);
  EmitVariable('Administrator',Application.Administrator);

//  EmitVariable('',Application.);
end;
//##############################################################################
function TDemoModule.Welcome():string;
var
html:string;
query:string;
begin

query:=Application.QueryString;
reg.Expression:='enter=yes';
if reg.Exec(query) then begin
   result:=EnterPage();
   exit;
end;


html:=html +'<center>';
html:=html +'<div style="width:667px;height:395px;background-image:url(../images/logon.jpg);background-repeat:no-repeat;border:1px solid #FFFFFF">';
html:=html +'<div style="float:right;margin-right:65px;margin-top:60px">';
html:=html +'<table >';
html:=html +'<tr>';
html:=html +'<td><a href="index.cgi?enter=yes"><H3>&laquo;Enter&raquo;</H3></td>';
html:=html +'</tr>';
html:=html +'</table>';
html:=html +'</div>';
html:=html +'</div>';
html:=html +'</center>';

result:=html;
end;
//##############################################################################
function TDemoModule.EnterPage():string;
var
html,artica_daemon_status,articaimg,lightimg,ifconf,lightstatus,uris,locked_password:string;
query:string;
begin
  lighttp:=Tlighttpd.Create(SYS);
  
  if GetRequestVariables('save')='yes' then begin
     ParsePOST();
  end;

  
  
   if not SYS.PROCESS_EXIST(cron.PID_NUM()) then begin
      artica_daemon_status:='Artica daemon is stopped !';
      articaimg:='danger32.png';
   end else  begin
      artica_daemon_status:='Artica daemon is running using '+IntToStr(SYS.PROCESS_MEMORY(cron.PID_NUM()))+' Kb memory';
      articaimg:='ok32.png';
   end;
   
   if not SYS.PROCESS_EXIST(lighttp.LIGHTTPD_PID()) then begin
      lightstatus:='lighttpd is stopped !';
      lightimg:='danger32.png';
   end else  begin
     lightstatus:='lighttpd daemon is running using '+IntToStr(SYS.PROCESS_MEMORY(lighttp.LIGHTTPD_PID()))+' Kb memory';
     lightimg:='ok32.png';

   end;
   
   ifconf:=SYS.ifconfig_html();
   uris:=SYS.https_uris(lighttp.LIGHTTPD_LISTEN_PORT());

html:='<H2>Status of ' + SYS.HOSTNAME_g()+'</H2><table style="width:100%;padding:3px;border:1px solid #CCCCCC">';
html:=html+'<tr>';
html:=html+'<td align="right" nowrap valign="top"><strong style="font-size:13px">Artica web server status:</strong></td>';
html:=html+'<td align="left" nowrap valign="top"><strong style="font-size:13px">'+lightstatus+'</strong></td>';
html:=html+'<td align="left" width=1% valign="top"><img src="../images/'+lightimg+'"></td>';
html:=html+'</tr>';
html:=html+'<tr>';
html:=html+'<td align="right" nowrap><strong style="font-size:13px">Artica Daemon status:</strong></td>';
html:=html+'<td align="left" nowrap><strong style="font-size:13px">'+artica_daemon_status+'</strong></td>';
html:=html+'<td align="left" width=1%><img src="../images/'+articaimg+'"></td>';
html:=html+'</tr>';
html:=html+'</table>';

html:=html+'    <table style="width:100%">';
html:=html+'           <tr>';
html:=html+'                 <td align="right" nowrap><strong>SSL Certificate path:</strong></td>';
html:=html+'                 <td width="1%"><img src="../images/fw_bold.gif"></td>';
html:=html+'                 <td align="left" nowrap width=99%><strong>'+lighttp.LIGHTTPD_CERTIFICATE_PATH()+'</strong></td>';
html:=html+'           </tr>';

locked_password:=SYS.get_INFO('artica_interface_locked_passwd');
if GetRequestVariables('unlocksave')=locked_password then locked_password:='';


if length(locked_password)=0 then begin
   html:=html+'           <tr>';
   html:=html+'                 <td align="right" nowrap><strong>Username and password:</strong></td>';
   html:=html+'                 <td width="1%"><img src="../images/fw_bold.gif"></td>';
   html:=html+'                 <td align="left" nowrap width=99%><strong style="color:red">username &#8220;'+openldp.get_LDAP('admin')+'&#8221; password &#8220;'+openldp.get_LDAP('password')+'&#8221;</strong></td>';
   html:=html+'           </tr>';
   html:=html+'           <tr>';
   html:=html+'                 <td align="right" nowrap><strong>Lock password:</strong></td>';
   html:=html+'                 <td align="left" nowrap width=99% colspan=2><form name=ffm method=get>';
   html:=html+'                 <input type="hidden" name="enter" value="yes"><input type="hidden" name="save" value="yes"><input type="password" name="locksave" style="width:60px">&nbsp;<input type="submit" value="Go&nbsp;&raquo;"></form></td>';
   html:=html+'           </tr>';
end else begin
   html:=html+'                 <td align="right" nowrap><strong>Unlock password:</strong></td>';
   html:=html+'                 <td align="left" nowrap width=99% colspan=2><form name=ffm method=get>';
   html:=html+'                 <input type="hidden" name="enter" value="yes"><input type="hidden" name="save" value="yes"><input type="password" name="unlocksave" style="width:60px">&nbsp;<input type="submit" value="Go&nbsp;&raquo;"></form></td>';
   html:=html+'           </tr>';

end;


html:=html+'           <tr>';
html:=html+'                 <td align="right" nowrap><strong>Keyboard language:</strong></td>';
html:=html+'                 <td width="1%"><img src="../images/fw_bold.gif"></td>';
html:=html+'                 <td align="left" nowrap width=99%><strong>'+deb.keyboard_language()+'</strong>';
html:=html+'           </tr>';




html:=html+'    </table>';
html:=html+'    <table style="width:100%"><tR><td valign="top">' + uris+'<br>You can connect to the Artica interface using these uris</td> <td valign="top">'+ifconf+'</td></tr></table>';
result:=html;
  
  

end;
//##############################################################################
procedure TDemoModule.ParsePOST();
begin

if length(GetRequestVariables('locksave'))>0 then begin
  GLOBAL_INI.set_INFOS('artica_interface_locked_passwd',GetRequestVariables('locksave'));
  exit;
end;

end;
function TDemoModule.GetRequestVariables(xname:string):string;

Var
  L : TStringList;
  I : Integer;
  N,V : String;

begin

  L:=TStringList.Create;
  try
    Application.GetRequestVarList(L,False);
    For I:=0 to L.Count-1 do
      begin
      L.GetNameValue(I,N,V);
      if N=xname then exit(V);
      end;
    // Alternatively,
    // Application.RequestVariables[Varname : string] gives named acces to the variables
    // Application.RequestvariableCount returns the number of variables.
  finally
    L.Free;
  end;
  AddResponseLn('</TABLE>');
end;


procedure TDemoModule.EmitRequestVariables;

Var
  L : TStringList;
  I : Integer;
  N,V : String;
  
begin
  AddResponseLn('</TABLE>');
  AddResponseLn('<H1>Query variables:</H1>');
  AddResponseLn('<TR><TH>Variable</TH><TH>Value</TH></TR>');
  L:=TStringList.Create;
  try
    // Retrieve parsed list of variables as name=value pairs
    Application.GetRequestVarList(L,False);
    For I:=0 to L.Count-1 do
      begin
      L.GetNameValue(I,N,V);
      
      EmitVariable(N,V);
      end;
    // Alternatively,
    // Application.RequestVariables[Varname : string] gives named acces to the variables
    // Application.RequestvariableCount returns the number of variables.
  finally
    L.Free;
  end;
  AddResponseLn('</TABLE>');
end;
  
procedure TDemoModule.EmitVariable(const VarName, VarValue: String);
begin
  AddResponseLn(Format('<TR><TD>%s</TD><TD>%s</TD></TR>',[VarName,VarValue]));
end;

initialization
  {$I wmdump.lrs}

end.

