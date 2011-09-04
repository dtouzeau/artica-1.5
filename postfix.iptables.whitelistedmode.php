<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.iptables-chains.inc');

$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["params"])){params();exit;}		
	if(isset($_POST["EnablePostfixAutoBlockWhiteListed"])){Save();exit;}
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{white_listed_mode}");
	$html="YahooWin4('650','$page?tabs=yes','$title')";
	echo $html;
}


function params(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$EnablePostfixAutoBlockWhiteListed=$sock->GET_INFO("EnablePostfixAutoBlockWhiteListed");
	if(!is_numeric($EnablePostfixAutoBlockWhiteListed)){$EnablePostfixAutoBlockWhiteListed=0;}
	$form=Paragraphe_switch_img("{white_listed_mode}",
	"{instant_iptables_whitelisted_explain}<br>{enable_white_listed_mode_text}",'EnablePostfixAutoBlockWhiteListed',$EnablePostfixAutoBlockWhiteListed,"{enable_disable}",330);
	
	$html="
	<div id='SaveInstantWhiteListModeID'>
	$form
	<hr>
	<div style='text-align:right'>". button('{apply}',"SaveInstantWhiteListMode()")."</div>
	</div>
	
<script>
	var x_SaveInstantWhiteListMode= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		if(document.getElementById('instant_iptables_tabs')){RefreshTab('instant_iptables_tabs');}
		RefreshTab('instant_iptables_wlmodetabs');
		
		
	}	
		
	function SaveInstantWhiteListMode(){
		var XHR = new XHRConnection();
		XHR.appendData('EnablePostfixAutoBlockWhiteListed',document.getElementById('EnablePostfixAutoBlockWhiteListed').value);
		AnimateDiv('SaveInstantWhiteListModeID');
		XHR.sendAndLoad('$page', 'POST',x_SaveInstantWhiteListMode);	
	}		
	
</script>	
	";
	
echo $tpl->_ENGINE_parse_body($html);
	
}


function tabs(){
		$page=CurrentPageName();
	$tpl=new templates();	
	$array["params"]='{parameters}';
	$array["tab-iptables-whlhosts"]='{hosts}:{white list}';


	

	while (list ($num, $ligne) = each ($array) ){
		if($num=="tab-iptables-whlhosts"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"whitelists.admin.php?popup-hosts=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=instant_iptables_wlmodetabs style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#instant_iptables_wlmodetabs').tabs();
			
			
			});
		</script>";		
	
}

function Save(){
	$sock=new sockets();
	$EnablePostfixAutoBlock=$sock->GET_INFO("EnablePostfixAutoBlock");
	if(!is_numeric($EnablePostfixAutoBlock)){$EnablePostfixAutoBlock=0;}
	if($_POST["EnablePostfixAutoBlockWhiteListed"]==1){
		if($EnablePostfixAutoBlock==0){$sock->SET_INFO("EnablePostfixAutoBlock", 1);}
	}
	
	$sock->SET_INFO("EnablePostfixAutoBlockWhiteListed", $_POST["EnablePostfixAutoBlockWhiteListed"]);
	$sock->getFrameWork("cmd.php?postfix-iptables-compile=yes");
}