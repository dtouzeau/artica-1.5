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
	
	
	main_page();
	
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
	$tpl=new template_users('{APP_MIMEDEFANG} ({statistics})',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}
	
function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="hourly";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	
	
	$array["hourly"]='{hourly}';
	$array["daily"]='{daily}';
	$array["monthly"]='{monthly}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_switch(){
	$md=md5(date('Ymdhis'));	
	$mime=new mimedefang();
	$html=main_tabs() . "
	<input type='hidden' name='selected' id='selected' value='{$_GET["main"]}'>
	<br><H5>{{$_GET["main"]}} {statistics}";
	while (list ($num, $ligne) = each ($mime->graph_array) ){
		if(preg_match("#{$_GET["main"]}#",$ligne)){
			$html=$html . "
			<div style='border:1px dotted #CCCCCC;margin:4px'><img src='images.listener.php?uri=graphdefang/$ligne&md=$md'></div>
			
			";
			
		}
		
	}
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}
	
?>	