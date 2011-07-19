<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.tcpip.inc');

	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsSystemAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["LOGIN"])){SAVE();exit;}
	if(isset($_GET["hamachilist"])){GLIST();exit;}
	if(isset($_GET["status"])){STATUS();exit;}
	if(isset($_GET["sessions"])){SESSIONS();exit;}
	if(isset($_GET["DELETE"])){DELETE();exit;}
	if(isset($_GET["DELETE-NET"])){DELETE_NET();exit;}
	if(isset($_GET["EnableHamachi"])){EnableHamachi();exit;}
	js();
function js(){
	
		$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_AMACHI}");
	$prefix="amachi";
	$html="
	var {$prefix}tant=0;


function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
	if(!YahooWin3Open()){return false;}
	
	if ({$prefix}tant < 10 ) {                           
		setTimeout(\"{$prefix}demarre()\",2000);
      } else {
		{$prefix}tant = 0;
		LoadAjax('hamachi-status','$page?status=yes');
		{$prefix}demarre(); 
		                              
   }
}	
	
		function HAMACHI_START(){
			YahooWin3('700','$page?popup=yes','$title');
		}
		

		
var X_HAMACHI_SAVE= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	HAMACHI_START();
	}
	
var X_FREENETKILL= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	RefreshTab('hamashi_config_postfix');
	}	
		
	function HAMACHI_SAVE(){
		var XHR = new XHRConnection();
		XHR.appendData('LOGIN',document.getElementById('LOGIN').value);
		XHR.appendData('TYPE',document.getElementById('TYPE').value);
		XHR.appendData('NETWORK',document.getElementById('NETWORK').value);
		XHR.appendData('PASSWORD',document.getElementById('PASSWORD').value);
		
		document.getElementById('hamachiid').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		
		XHR.sendAndLoad('$page', 'GET',X_HAMACHI_SAVE);				
	}	
	
var X_HAMACHI_ENABLE= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	}	
	
	
	function HAMACHI_ENABLE(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableHamachi').checked){
			XHR.appendData('EnableHamachi',1);}else{XHR.appendData('EnableHamachi',0);}
			XHR.sendAndLoad('$page', 'GET',X_HAMACHI_ENABLE);
		}

	function HAMACHI_DELETE(ID,net){
		var XHR = new XHRConnection();
		XHR.appendData('DELETE',ID);
		XHR.appendData('NETWORK',net);
		document.getElementById('hamachiid').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_HAMACHI_SAVE);
	}
	
	function FREENETKILL(net){
		var XHR = new XHRConnection();
		XHR.appendData('DELETE-NET',net);
		XHR.sendAndLoad('$page', 'GET',X_FREENETKILL);	
	}
	
	HAMACHI_START();
	{$prefix}demarre();";
	
	echo $html;
	
}


function popup(){
	$sock=new sockets();
	$page=CurrentPageName();
	$array["CREATE_NET"]="{create_network}";
	$array["JOIN_NET"]="{join_network}";
	$array[null]="{select}";
	
	$EnableHamachi=$sock->GET_INFO("EnableHamachi");
	if($EnableHamachi==null){$EnableHamachi=1;}
	//96-smtp-auth.png
	
	$field=Field_array_Hash($array,"TYPE",$ini->_params["SETUP"]["TYPE"],null,null,0,"font-size:14px;padding:4px");

	
	
	$html="
	
	<div id='hamachiid'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		 <div style='text-align:left;padding-bottom:4px'><img src='img/logmein_logo.gif'></div>
		<div id='hamachi-status'></div>
	</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{enable} {APP_AMACHI}:</td>
		<td valign='top'>". Field_checkbox("EnableHamachi",1,$EnableHamachi,"HAMACHI_ENABLE()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{type}:</td>
		<td valign='top'>$field</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{login_name}:</td>
		<td valign='top'>". Field_text("LOGIN",null,"font-size:14px;padding:4px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{network_password}:</td>
		<td valign='top'>". Field_password("PASSWORD",null,"font-size:14px;padding:4px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{network_name}:</td>
		<td valign='top'>". Field_text("NETWORK",null,"font-size:14px;padding:4px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","HAMACHI_SAVE()")."</td>
	</tr>	
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>	
	". TABS()."
	
	<script>
		
		LoadAjax('hamachi-status','$page?status=yes');
	</script>

	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
	
}
function TABS(){
	
	$page=CurrentPageName();
	$array["hamachilist"]='{networks}';
	$array["sessions"]='{sessions}';
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname=$hostname\"><span>$ligne</span></a></li>\n");
	}
	
	
	return "
	<div id=hamashi_config_postfix style='width:100%;height:330px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#hamashi_config_postfix').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
}



function GLIST(){
	$sock=new sockets();
	$ip=$sock->getFrameWork("cmd.php?hamachi-ip");
	$sql="SELECT * FROM hamachi ORDER BY ID DESC";
	$html="<H3>{tcp_address}:$ip</H3><center><table style='width:95%' class=table_form>";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$array=unserialize(base64_decode($ligne["pattern"]));	
		$html=$html."
		<tr ". CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:12px'>{$array["TYPE"]}</strong></td>
		<td><strong style='font-size:12px'>{$array["LOGIN"]}</strong></td>
		<td><strong style='font-size:12px'>{$array["NETWORK"]}</strong></td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","HAMACHI_DELETE({$ligne["ID"]},'{$array["NETWORK"]}')")."</td>
		</tr>
		<tr>
		<td colspan=5><hr></td>
		</tR>
			";
		}
		
	$html=$html."</table></center>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function SAVE(){
	//EnableHamachi
	
	$datas=base64_encode(serialize($_GET));
	$sql="INSERT INTO hamachi (pattern) VALUES('$datas')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?hamachi-net=yes");
	}
function DELETE(){
	$_GET["NETWORK"]=base64_encode($_GET["NETWORK"]);
	$sql="DELETE FROM hamachi WHERE ID={$_GET["DELETE"]}";
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?hamachi-delete-net='{$_GET["NETWORK"]}");	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
}

function STATUS(){
	$sock=new sockets();
	$params=unserialize(base64_decode($sock->getFrameWork("cmd.php?hamachi-status=yes")));
	
	$ini=new Bs_IniHandler();
	$ini->_params=$params;
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("APP_AMACHI",$ini));
	
}

function SESSIONS(){
	$sock=new sockets();
	$params=unserialize(base64_decode($sock->getFrameWork("cmd.php?hamachi-sessions=yes")));
	$ip=$sock->getFrameWork("cmd.php?hamachi-ip");
	
	if(!is_array($params)){return null;}
	$html="
	<H3>{tcp_address}:$ip</H3>
	<center>
	
	<table style='width:95%' class=table_form>";
	while (list ($server,$array) = each ($params) ){
		while (list ($index,$comps) = each ($array) ){
		$html=$html."
		<tr ". CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:14px'>$comps</strong></td>
		<td><strong style='font-size:14px'>$server</strong></td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete} $server","FREENETKILL('$server')")."</td>
		
		</tr>";
		}
		
		
	}
	
	$html=$html."</table></center>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function EnableHamachi(){
	$sock=new sockets();
	$sock->SET_INFO("EnableHamachi",$_GET["EnableHamachi"]);
	$sock->getFrameWork("cmd.php?hamachi-restart=yes");
}

function DELETE_NET(){
	$sock=new sockets();
	$_GET["DELETE-NET"]=base64_encode($_GET["DELETE-NET"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?hamachi-delete-net='{$_GET["DELETE-NET"]}");	
	
}
?>