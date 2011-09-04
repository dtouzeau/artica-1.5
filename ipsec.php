<?php
	session_start();
	if($_SESSION["uid"]==null){
		if(count($_GET)>0){echo "<script>window.location.href ='logoff.php';</script>";die();}
		echo "window.location.href ='logoff.php';";die();
	}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.user.inc');

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["Net-to-Net"])){NetToNet();exit;}
	
	
	
js();	

function js(){
	$page=CurrentPageName();
	
$html="	
	function IpsecIndexLoadpage(){
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?popup=yes');
	}
	IpsecIndexLoadpage();
	
	
	
	
";	
	
echo $html;	
}

function status(){
	echo "<H1>In progress...</H1>";
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["status"]='{status}';
	$array["Net-to-Net"]="Net-to-Net";
	$array["Roadwarrior"]="{Roadwarrior}";
	$array["events"]="{events}";
	

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_ipsec style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_ipsec').tabs({
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

function NetToNet(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$IpSecConfig=unserialize(base64_decode($sock->GET_INFO("IpSecConfigNetToNet")));
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td colspan=2><strong style='font-size:14px'>{your_network}</strong></td>
	</tr>
	<tr>
		<td class=legend>{public_ip}:</td>
		<td>". Field_text("left",$IpSecConfig["left"],"font-size:13px;padding:3px;width:120px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{public_fqdn_name}:</td>
		<td valign='top'>". Field_text("leftid",$IpSecConfig["leftid"],"font-size:13px;padding:3px;width:180px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{ip}:</td>
		<td valign='top'>". Field_text("server_route_ip",null,"font-size:13px;padding:3px;width:120px",null,"CalcRouteIpsecServerCDIR()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{netmask}:</td>
		<td valign='top'>". Field_text("server_route_netmask",null,"font-size:13px;padding:3px;width:120px",null,"CalcRouteIpsecServerCDIR()")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{cdir}:</td>
		<td valign='top'>". Field_text("Leftsubnet",$IpSecConfig["Leftsubnet"],"font-size:13px;padding:3px;width:120px",null)."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{Leftnexthop}:</td>
		<td valign='top'>". Field_text("Leftnexthop",$IpSecConfig["Leftnexthop"],"font-size:13px;padding:3px;width:120px",null)."</td>
		<td>
			<table style='margin:0;padding:0'>
				<tr>
					<td class=legend>{use_default_route}:</td>
					<td>". Field_checkbox("defaultroute1",1,$IpSecConfig["defaultroute1"],"SwitchDefaultRouteOne()")."</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan=2>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=2><strong style='font-size:14px'>{remote_network}</strong></td>
	</tr>		
	<tr>
		<td class=legend>{public_ip}:</td>
		<td>". Field_text("right",$IpSecConfig["right"],"font-size:13px;padding:3px;width:120px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{public_fqdn_name}:</td>
		<td valign='top'>". Field_text("rightid",$IpSecConfig["rightid"],"font-size:13px;padding:3px;width:180px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{ip}:</td>
		<td valign='top'>". Field_text("server_route_ip2",null,"font-size:13px;padding:3px;width:120px",null,"CalcRouteIpsecServerCDIR2()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{netmask}:</td>
		<td valign='top'>". Field_text("server_route_netmask2",null,"font-size:13px;padding:3px;width:120px",null,"CalcRouteIpsecServerCDIR2()")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{cdir}:</td>
		<td valign='top'>". Field_text("rightsubnet",$IpSecConfig["rightsubnet"],"font-size:13px;padding:3px;width:120px",null)."</td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{Leftnexthop}:</td>
		<td valign='top'>". Field_text("rightnexthop",$IpSecConfig["rightnexthop"],"font-size:13px;padding:3px;width:120px",null)."</td>
		<td>
			<table style='margin:0;padding:0'>
			<tr>
				<td class=legend>{use_default_route}:</td>
				<td>". Field_checkbox("defaultroute1",1,$IpSecConfig["defaultroute2"],"SwitchDefaultRouteTwo()")."</td>
			</tr>
			</table>
		</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button('{apply}',"SaveSiteToSite()")."</td>
	</tr>
	</table>
	
	
	<script>
		var x_CalcRouteIpsecServerCDIR= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){document.getElementById('Leftsubnet').value=tempvalue;}
		}			
		
		function CalcRouteIpsecServerCDIR(){
			var XHR = new XHRConnection();
			XHR.appendData('route_ip',document.getElementById('server_route_ip').value);
			XHR.appendData('route_netmask',document.getElementById('server_route_netmask').value);	
			XHR.sendAndLoad('pptpd.php', 'GET',x_CalcRouteIpsecServerCDIR);
		}

		var x_CalcRouteIpsecServerCDIR2= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){document.getElementById('rightsubnet').value=tempvalue;}
		}			
		
		function CalcRouteIpsecServerCDIR2(){
			var XHR = new XHRConnection();
			XHR.appendData('route_ip',document.getElementById('server_route_ip2').value);
			XHR.appendData('route_netmask',document.getElementById('server_route_netmask2').value);	
			XHR.sendAndLoad('pptpd.php', 'GET',x_CalcRouteIpsecServerCDIR2);
		}
				
	
		function SwitchDefaultRouteOne(){
			document.getElementById('Leftnexthop').disabled=false;
			if(document.getElementById('defaultroute1').checked){document.getElementById('Leftnexthop').disabled=true;}
		}
		
		function SwitchDefaultRouteTwo(){
			document.getElementById('rightnexthop').disabled=false;
			if(document.getElementById('defaultroute2').checked){document.getElementById('rightnexthop').disabled=true;}
		}		
	
	
	SwitchDefaultRouteOne();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

//You'll need to allow udp/500 and udp/4500 to your external interface through the firewall on your INPUT chain. I also added protocol 50. 
//http://www.strongswan.org/docs/readme42.htm#section_2.5

//http://www.strongswan.org/uml/testresults/ikev1/rw-cert/carol.listall

//ipsec newhostkey --output /etc/ipsec.secrets --bits 2048 --verbose
//ipsec showhostkey --left