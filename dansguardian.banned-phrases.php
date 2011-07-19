<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	
	if(!IsDansGuardianrights()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["DansGuardian_addcategorybannedphraselist"])){main_rules_addcategory_bannedphraselist();exit;}
	if(isset($_GET["DansGuardian_delcategorybannedphraselist"])){main_rules_delcategory_bannedphraselist();exit;}	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{bannedregexpurllist}","dansguardian.index.php");
	$html="
	
	function DANSGUARDIAN_LOAD_BANNEDPHRLIST(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
	var x_sectionrules_categoriesbannedphraselist=function(obj){
	      DANSGUARDIAN_LOAD_BANNEDPHRLIST();
	}	
	
function dansguardian_delcategorybannedphraselist(hostname,rule_main,index){
 var XHR = new XHRConnection();
        XHR.appendData('DansGuardian_delcategorybannedphraselist',index);
        XHR.appendData('rule_main','{$_GET["rule_main"]}');
        document.getElementById('main_rules_bannedphraselist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
        XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesbannedphraselist);  
}
function dansguardian_addcategorybannedphraselist(){
 		var XHR = new XHRConnection();
        XHR.appendData('DansGuardian_addcategorybannedphraselist',document.getElementById('banned').value);
        XHR.appendData('rule_main','{$_GET["rule_main"]}');
        document.getElementById('main_rules_bannedphraselist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
        XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesbannedphraselist);
      }
	
	DANSGUARDIAN_LOAD_BANNEDPHRLIST()";
	
	echo $html;
	
}

function popup(){
		$users=new usersMenus();
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=$dansg->Master_rules_index[$rule_main];
$html="
	<input type='hidden' name='rule_main' value='$rule_main'>
	<h1>$rulename::{bannedphraselist}</H1>
	<p class=caption>{bannedphraselist_explain}</p>
	<br>
	<table style='width:100%'>
	<tr>
	<td>" .Field_array_Hash($dans->array_banned_phrases_list,'banned',null) . "</td>
	<td><input type='button' value='&nbsp;&laquo;&nbsp;{add_category}&nbsp;&raquo;&nbsp;' 
		OnClick=\"javascript:dansguardian_addcategorybannedphraselist('$hostname','$rule_main');\">
	</td>
	</tr>
	</table>
	<div id='main_rules_bannedphraselist_list' style='width:100%;height:250px;overflow:auto'>".main_rules_bannedphraselist_list("$rule_main",1)."</div>
	";	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function main_rules_bannedphraselist_list($rule_main,$noecho=0){
	//bannedphraselist
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$sql="SELECT * FROM dansguardian_files WHERE filename='bannedphraselist' AND RuleID=$rule_main ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$style=CellRollOver();
	$categ="
	<table style='width:99%' class=table_form>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$num=$ligne["ID"];
		$val=$ligne["pattern"];
		$delete=imgtootltip("ed_delete.gif","{delete}","dansguardian_delcategorybannedphraselist('$hostname','$rule_main','$num')");
		$categ=$categ . 
		"<tr>
				<td width=1% valign='top'><img src='img/red-pushpin-24.png'></td>
				<td valign='top'><span style='font-size:13px;font-weight:bold'>{$dans->array_banned_phrases_list[$val]}</span></td>
				<td width=1% valign='top'>$delete</td>
			</tr>";
		}
	
	$categ="<div style='height:500px'>$categ</table></div>";
	
$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$categ");}
	echo $tpl->_ENGINE_parse_body("$categ");		
	
		
	
}
function main_rules_delcategory_bannedphraselist(){
	//bannedphraselist
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->DelCategory_phrase_banned($_GET["DansGuardian_delcategorybannedphraselist"]);
	}

function main_rules_addcategory_bannedphraselist(){
	//bannedphraselist
	writelogs("{$_GET["rule_main"]}",__FUNCTION__,__FILE__);
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->AddCategory_phrase_banned($_GET["DansGuardian_addcategorybannedphraselist"],$_GET["rule_main"]);	
	}



?>