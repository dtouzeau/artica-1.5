<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.mimedefang.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
	
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["popup"])){echo main_tabs();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_KAS3} ({statistics})");
	
	$html="
		function APP_KAS3_STATS_SHOW(){
			YahooWin(780,'$page?popup=yes','$title');
		}
	
	
	APP_KAS3_STATS_SHOW();";
	
	echo $html;
}
	
function main_page(){
	

	
	$html=
	"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	var selected=document.getElementById('selected').value;
	LoadAjax('main_config','$page?main='+selected);
	}
</script>	
<div id='main_config'></div>

<script>demarre();LoadAjax('main_config','$page?main=hourly');</script>
	
	";
	
	$cfg["JS"][]='js/mimedefang.js';
	$tpl=new template_users('{APP_KAS3} ({statistics})',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}
	
function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="hourly";};
	$page=CurrentPageName();
	$tpl=new templates();
	
	
	$array["hourly"]='{hourly}';
	$array["daily"]='{daily}';
	$array["monthly"]='{monthly}';
	$array["year"]='1 {year}';

		
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n");
		}
	
	
return "
	<div id=admin_kasstats_tabs style='width:755px;height:500px;overflow:auto;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#admin_kasstats_tabs').tabs({
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


function main_switch(){
/*d__all_7dM.png
d00000000_1mM.png

d00000000_1yM.png
d00000000_7dS.png
d00000000_7dM.png

g00000000_7dM.png

d__all_1mM.png

g00000000_1mM.png
d00000000_1yS.png
g__all_7dM.png
d00000000_1mS.png
g__all_7dS.png
g00000000_1yM.png
g00000000_1mS.png
d__all_1mS.png
g00000000_1yS.png
g00000000_7dS.png

g__all_1mM.png
d__all_1yM.png
g__all_1mS.png
d__all_7dS.png
g__all_1yM.png
d__all_1yS.png

g__all_1yS.png
*/	

	
	switch ($_GET["main"]) {
		case "hourly":$type="24h";break;
		case "daily":$type="7d";break;
		case "monthly":$type="1m";break;
		case "year":$type="1y";break;
		default:
			break;
	}

	$html="
	<input type='hidden' name='selected' id='selected' value='{$_GET["main"]}'>
	<br><H5>{{$_GET["main"]}} {statistics}".gph($type);
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function gph($type){
		$md=md5(date('Ymdhis'));	

	return "
	<center><H3>messages</h3></center>
	<table style='width:100%;border:1px dotted #FFFFFF;margin:4px''>
	<tr>
		<td align='center'><img src='images.listener.php?uri=kas3/d__all_{$type}M.png&md=$md' style='border:1px solid #CCCCCC;margin:5px'></td>
	</tr>
	<tr>
		<td align='center'><img src='images.listener.php?uri=kas3/g__all_{$type}M.png&md=$md' style='border:1px solid #CCCCCC;margin:5px'></td>
	</tr>
	</table>	
	<br>
	<center><H3>{size}</h3></center>
	<table style='width:100%;border:1px dotted #FFFFFF;margin:4px''>
	<tr>
		<td align='center'><img src='images.listener.php?uri=kas3/d__all_{$type}S.png&md=$md' style='border:1px solid #CCCCCC;margin:5px'></td>
	</tr>
	<tr>
		<td align='center'><img src='images.listener.php?uri=kas3/g__all_{$type}S.png&md=$md' style='border:1px solid #CCCCCC;margin:5px'></td>
	</tr>
	</table><br>
	
	";

}
	
?>	