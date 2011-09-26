<?php
$GLOBALS["ICON_FAMILY"]="VPN";
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.openvpn.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.tcpip.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die("alert('no access');");}

	if(isset($_GET["tabs"])){tabs();exit();}
	if(isset($_GET["certificate_infos"])){certificate_infos();exit();}
	
	
	
js();



function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{APP_OPENVPN}::{certificate_infos}");
	$html="YahooWin4('600','$page?tabs=yes','$title')";
	echo $html;
	
}


function tabs(){
	
	$page=CurrentPageName();
	if($html<>null){echo $html;}
	$array["certificate_infos"]="{certificate_infos}";
	
	//$array["adv"]="{clients}";


	
	
	while (list ($num, $ligne) = each ($array) ){
		$tab[]="<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
			
		}
	$tpl=new templates();
	
	

	$html="
		<div id='main_openvpn_sslkey' style='background-color:white;'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_openvpn_sslkey').tabs();
			

			});
		</script>
	
	";
		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	
	echo $html;	
	
	
}

function certificate_infos(){
	$sock=new sockets();
	$tbl=unserialize(base64_decode(($sock->getFrameWork("cmd.php?certificate-viewinfos=yes"))));

	if(!is_array($tbl)){return null;}
	while (list ($num, $val) = each ($tbl) ){
		if(trim($val)==null){continue;}
		$val=str_replace("\t","&nbsp;&nbsp;&nbsp;",$val);
		
		if(preg_match('#^([a-zA-Z\s]+):(.*)#',$val,$re)){
			$val="<strong>{$re[1]}:</strong>&nbsp;{$re[2]}";
		}
		
		$t=$t."<div><code>$val</code></div>";
	}
	
	$html="
	<div style='width:99%;height:450px;overflow:auto' class=form>$t</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

