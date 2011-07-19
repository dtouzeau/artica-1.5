<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	

	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["show"])){main_cf_page();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["postfinger"])){postfinger();exit;}

js();

function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{main.cf}");
	$page=CurrentPageName();
	$html="
		function MainCfShowConfig(){
			YahooWin2(800,'$page?tabs=yes','$title');
		}
		MainCfShowConfig();
	
	";
		
	echo $html;
	
	
}

function tabs(){
	
	$page=CurrentPageName();
	$array["show"]="main.cf";
	$array["postfinger"]='postfinger';
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]="<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
			
		}	
	
	$tab="<div id=main_popup_sasl_auth style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_popup_sasl_auth').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($tab);	
	
}

function main_cf_page(){
	
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork('cmd.php?get-main-cf=yes')));
	$html="<table>";
	while (list ($index, $line) = each ($datas) ){
		$html=$html."
		<tr>
			<td><code style='font-size:11px'>". htmlspecialchars($line)."</code></td>
		</tr>
		
		";
	}
	$html=$html."</table>";
	
	echo "$html";
	
	
}
	function postfinger(){
	
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork('cmd.php?postfix-postfinger=yes')));
	$html="<table>";
	while (list ($index, $line) = each ($datas) ){
		$line=htmlspecialchars($line);
		if(preg_match("#--.+?--#",$line)){$line="<H2>$line</H2>";}
		$line=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$line);
		$line=str_replace("  ","&nbsp;&nbsp;",$line);
		
		$html=$html."
		<tr>
			<td><code style='font-size:11px'>$line</code></td>
		</tr>
		
		";
	}
	$html=$html."</table>";
	
	echo "$html";
	
	
}	
?>	

