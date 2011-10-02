<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){$tpl=new templates();echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";exit;}
	
	if(isset($_GET["tasks"])){tasks();exit;}
	tabs();
	
	
function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["tasks"]='{tasks}';
	$array["events"]='{events}';

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_squid_stats_tasks style='width:100%;height:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_squid_stats_tasks').tabs();
			
			
			});
		</script>";		
}	

function tasks(){
	
	
	
}