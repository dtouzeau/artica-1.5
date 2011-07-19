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
	if(isset($_GET["DansGuardian_addcategory"])){main_rules_addcategory();exit;}
	if(isset($_GET["DansGuardian_delcategory"])){main_rules_delcategory();exit;}	
	if(isset($_GET["display-categories"])){main_rules_categories_list($_GET["display-categories"]);exit;}
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{categories}");
	$html="
	
	function DANSGUARDIAN_LOAD_CATEGORIES(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
	var x_dansguardian_addcategory=function(obj){
	      DANSGUARDIAN_LOAD_CATEGORIES();
	}	
	
	function dansguardian_addcategory(){
	 		var XHR = new XHRConnection();
	        XHR.appendData('DansGuardian_addcategory',document.getElementById('blacklist').value);
	        XHR.appendData('rule_main','{$_GET["rule_main"]}');
	        document.getElementById('main_rules_categories_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';       
	        XHR.sendAndLoad('$page', 'GET',x_dansguardian_addcategory);  
	    
	}
	
function dansguardian_delcategory(hostname,rule_main,index){
 var XHR = new XHRConnection();
        hostname_mem=hostname;
        rule_main_mem=rule_main;
        XHR.appendData('DansGuardian_delcategory',index);
        XHR.appendData('rule_main','{$_GET["rule_main"]}');
        document.getElementById('main_rules_categories_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
        XHR.sendAndLoad('$page', 'GET',x_dansguardian_addcategory);  
}	

	 var X_DansCategoryEnable= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
	}

function DansCategoryEnable(md,cat,index,rule_main){
 	var XHR = new XHRConnection();
 	XHR.appendData('rule_main','{$_GET["rule_main"]}');
	if(document.getElementById(md).checked){
		XHR.appendData('DansGuardian_addcategory',cat);
	}else{
		 XHR.appendData('DansGuardian_delcategory',index);
	}
	XHR.sendAndLoad('$page', 'GET',X_DansCategoryEnable);  
}

function DansGuardianRefreshCategories(){
	LoadAjax('main_rules_categories_list','$page?display-categories={$_GET["rule_main"]}');
}
	
	DANSGUARDIAN_LOAD_CATEGORIES()";
	
	echo $html;
	
}
function strip_rulename($rulename){
	if(preg_match('#(.+?);(.+)#',$rulename,$re)){
		return $re[1];
		
	}else{
		return $rulename;
	}
	
}

function popup($noecho=0){
	$rule_main=$_GET["rule_main"];
	$sock=new sockets();
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=strip_rulename($dansg->Master_rules_index[$rule_main]);
	$explain="categories_explain";
	$EnableSquidFilterWhiteListing=$sock->GET_INFO("EnableSquidFilterWhiteListing");
	if($EnableSquidFilterWhiteListing==1){$explain="categories_white_explain";}
	
$html="
	<input type='hidden' name='rule_main' value='$rule_main'>
	<div class=explain>{{$explain}}</div>
	<div id='main_rules_categories_list' style='width:100%;height:650px;overflow:auto'></div>
	<script>
		DansGuardianRefreshCategories();
	</script>
	";	




$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$html","dansguardian.index.php");}
	echo $tpl->_ENGINE_parse_body("$html");	
	
	
}



function main_rules_categories_list($rule_main,$noecho=0){
	$dans=new dansguardian_rules();
	$html="
	<table style='width:100%'>
	<tr>
		<th colspan=2>{category}</th>
		<th colspan=2>{enabled}</th>
	</tr>";
	$q=new mysql();
	$sql="SELECT * FROM dansguardian_files WHERE filename='bannedsitelist' AND RuleID=$rule_main";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$val=$ligne["pattern"];
		$array_selected[$val]=$ligne["ID"];
	}
	
	$array_cat=$dans->array_blacksites;
	
	while (list ($num, $val) = each ($array_cat) ){
		if($array_selected[$num]>0){$enabled=1;}else{$enabled=0;}
		$md=md5($num);
		
		$pics=$dans->array_pics[$num];
		if($pics<>null){$pics="<img src='img/$pics'>";}else{$pics="&nbsp;";}
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%>$pics</td>
			<td><strong style='font-size:11px'>$val</td>
			<td>".Field_checkbox("$md",1,$enabled,"DansCategoryEnable('$md','$num','{$array_selected[$num]}','$rule_main')")."</td>
			<td>$num</td>
			
		</tr> 
		
		";
		
	}
	
$categ="<div style='width:100%;height:600px;overflow:auto'>$html</div>";
$tpl=new templates();
if($noecho==1){return $tpl->_ENGINE_parse_body($categ);}
echo $tpl->_ENGINE_parse_body("$categ");	
	
}



	
function main_rules_addcategory(){
	writelogs("add new category ->{$_GET["rule_main"]}={$_GET["DansGuardian_addcategory"]} ",__FUNCTION__,__FILE__);
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->AddCategory($_GET["DansGuardian_addcategory"],$_GET["rule_main"]);
	}	

	
function main_rules_delcategory(){
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->DelCategory($_GET["rule_main"],$_GET["DansGuardian_delcategory"]);
}




	
	
?>