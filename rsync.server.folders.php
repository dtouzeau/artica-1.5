<?php
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.rsync.inc");




	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["dev-list"])){popup_list();exit;}
	


js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_RSYNC_FOLDERS}');


$html="
	YahooWin4(500,'$page?popup=yes','$title')";
	
	echo $html;

}


function popup(){
	$page=CurrentPageName();
	$html=RoundedLightWhite("<div style='width:100%;height:250px;overflow:auto' id='APP_RSYNC_FOLDERS'></div>")."
	<script>
	LoadAjax('APP_RSYNC_FOLDERS','$page?dev-list=yes');
	</script>
	";
	$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
}

function popup_list(){
		$rsync=new rsync();
		if(!is_array($rsync->ou_storages)){return null;}
		$html="<table class=table_form style='width:99%'>
		<tr>
		<th>{organization}</th>
		<th>{mounted}</th>
		</tr>
		";
		while (list ($num, $array) = each ($rsync->ou_storages) ){
		if($bg=="#cce0df"){$bg="#FFFFFF";}else{$bg="#cce0df";}
		$html=$html."
		<tr style='background-color:$bg'>
			<td><strong>{$array["OU"]}</strong></td>
			<td><strong>{$array["mounted"]}</strong></td>
		</tr>
		";
			
		}
		
		$html=$html."</table>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	
}


	

	
	
	

?>