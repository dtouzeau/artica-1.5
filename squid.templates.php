<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');

	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_POST["TEMPLATE_DATA"])){TEMPLATE_SAVE();}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["templates-list"])){templates_list();exit;}
	if(isset($_GET["TEMP"])){FormTemplate();exit;}
	
js();


function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{squid_templates_error}");
	$html="
		YahooWin2('360','$page?popup=yes','$title');
	
	";
	echo $html;
	
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$html="<div class=explain>{squid_choose_template}</div>
	<div style='margin:10px;height:400px;overflow:auto' id='templateslist'></div>
	
	<script>
		LoadAjax('templateslist','$page?templates-list=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function templates_list(){
	$page=CurrentPageName();	
	$tpl=new templates();
	$sql="SELECT TEMPLATE_NAME FROM squid_templates ORDER BY TEMPLATE_NAME";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>{templates}</th>
	</tr>
</thead><tbody>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:14px;font-weight:bold'><a href=\"#\" style='text-decoration:underline' OnClick=\"javascript:s_PopUp('$page?TEMP={$ligne["TEMPLATE_NAME"]}',800,800);\">{$ligne["TEMPLATE_NAME"]}</a></td>
		</tr>
		";
	}
	
	$html=$html."</tbody></table>";
	echo $tpl->_ENGINE_parse_body($html);
}


function FormTemplate(){
	
	$tpl=new templates();
	$q=new mysql();
	$sql="SELECT TEMPLATE_DATA FROM squid_templates WHERE TEMPLATE_NAME='{$_GET["TEMP"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	
	$tpl=new templates();
	$button=$tpl->_ENGINE_parse_body("<input type='submit' value='{apply}'>");
	$tiny=TinyMce('TEMPLATE_DATA',$ligne["TEMPLATE_DATA"]);
	$html="
	<html>
	<head>
	<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
	<script type='text/javascript' language='JavaScript' src='mouse.js'></script>
	<script type='text/javascript' language='javascript' src='XHRConnection.js'></script>
	<script type='text/javascript' language='javascript' src='default.js'></script>
		
	</head>
	<body width=100% style='background-color:#005447;margin:0px;padding:0px'> 
	<form name='FFM1' METHOD=POST style='margin:0px;padding:0px'>
	<input type='hidden' name='TEMPLATE_NAME' value='{$_GET["TEMP"]}'>
	<div style='text-align:center;width:100%;background-color:white;margin-bottom:10px;padding:10px'>$button<br></div>
	<center>
	<div style='width:750px;height:650px'>$tiny</div>
	</center>
	<div style='text-align:center;width:100%;background-color:white;margin-top:10px;padding:10px'>$button<br></div>
	
	</form>
	</body>
	
	</html>";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function TEMPLATE_SAVE(){
	$sql="UPDATE squid_templates SET `TEMPLATE_DATA`='{$_POST["TEMPLATE_DATA"]}' WHERE `TEMPLATE_NAME`='{$_POST["TEMPLATE_NAME"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squid-templates=yes");
	
}


?>