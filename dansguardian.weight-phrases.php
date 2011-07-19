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
	if(isset($_GET["DansGuardian_addcategoryWeight"])){main_rules_addcategory_weight();exit();}
	if(isset($_GET["DansGuardian_delcategoryWeight"])){main_rules_delcategory_weight();exit;}
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{weightedphraselist}");
	$html="
	
	function DANSGUARDIAN_LOAD_WEIGHTPHRS(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
	var x_sectionrules_categoriesWeight=function(obj){
	      DANSGUARDIAN_LOAD_WEIGHTPHRS();
	}	
	
function dansguardian_addcategoryWeight(hostname,rule_main){
 	var XHR = new XHRConnection();
        XHR.appendData('DansGuardian_addcategoryWeight',document.getElementById('weighted').value);
        XHR.appendData('rule_main','{$_GET["rule_main"]}');
        document.getElementById('main_rules_weightedphraselist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
        XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesWeight);  
    
}
	
function dansguardian_delcategoryWeight(hostname,rule_main,index){
 		var XHR = new XHRConnection();
        XHR.appendData('DansGuardian_delcategoryWeight',index);
        XHR.appendData('rule_main','{$_GET["rule_main"]}');
        document.getElementById('main_rules_weightedphraselist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
        XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesWeight);  
}
	
	DANSGUARDIAN_LOAD_WEIGHTPHRS()";
	
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
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=strip_rulename($dansg->Master_rules_index[$rule_main]);
	
	
$html="
	<table style='width:100%'>
	<tr>
		<td valig='top'>
			<input type='hidden' name='rule_main' value='$rule_main'>
			<p class=caption>{weightedphraselist_explain}</p>
		</td>
		<td valign='top' align='right'>
			<input type='button' value='{create_new_category}&nbsp;&raquo;' OnClick=\"javascript:WeightedPhraseListAdd()\">
		</td>
	</tr>
	</table>
	<table style='width:100%'>
	<tr>
	<td><span id='weightedphraselist_dropdown'>" . main_rules_weightedphraselist_dropdown(1)."</td>
	<td>
	".  imgtootltip("plus-24.png","{add_category}","dansguardian_addcategoryWeight()")."
	</td>
	</tr>
	</table><br>
	<div id='main_rules_weightedphraselist_list' style='width:100%;height:250px;overflow:auto'>
	".main_rules_weightedphraselist_list($rule_main,1)."</div>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}
function main_rules_weightedphraselist_dropdown($noecho=0){
	$rule_main=$_GET["rule_main"];
	
	
	if($rule_main=="DansDefault"){
		$dans=new dansguardian_default_rules();
		$rulename="Default rule";
		
	}else{
		$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
		$dansg=new dansguardian($_GET["hostname"]);
		$rulename=strip_rulename($dansg->Master_rules_index[$rule_main]);
	}
	

	
	while (list ($num, $cat) = each ($dans->array_weighted_phrases_lists) ){
		$array[$num]=$cat;
	}
	
	$d=new dansguardian();
	$d->DefinedCategoryWeightedPhraseListLoad();
	
	while (list ($num, $cat) = each ($d->UserCategoryWeightedPhraseList) ){
		$array[$cat]=$cat;
	}	
	
	$list=Field_array_Hash($array,'weighted',null,null,null,0,'font-size:13px');
	$tpl=new templates();
	$list=$tpl->_ENGINE_parse_body($list);
	if($noecho==1){
		return $list;
	}
	
	echo $list;
	
}
function main_rules_weightedphraselist_list($rule_main,$noecho=0){
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansguardian=new dansguardian();
	$users_rules=$dansguardian->DefinedCategoryWeightedPhraseListLoad();
	//weightedphraselist
	
	$categ="<table style='width:99%'>";	
	$sql="SELECT * FROM dansguardian_files WHERE filename='weightedphraselist' AND RuleID='$rule_main'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$val=$ligne["pattern"];
		$val=trim($val);
		$num=$ligne["ID"];
		$edit_icon=null;
		$delete_icon=imgtootltip("ed_delete.gif","{delete}","dansguardian_delcategoryWeight('$hostname','$rule_main','$num')");
		$category_name=$dans->array_weighted_phrases_lists[$val];
		
		if($users_rules[$val]==$val){
			$edit_icon=imgtootltip("icon_edit.gif","{edit}","dansguardian_edit_user_categoryWeight('$val')");
			if(strlen($val)>11){
				$category_name=texttooltip("{your_category}:&nbsp;".substr($val,0,8)."...",$val,"dansguardian_edit_user_categoryWeight('$val')");
			}
			else{
				$category_name=texttooltip("{your_category}:&nbsp;$val",$val,"dansguardian_edit_user_categoryWeight('$val')");
			}	
		}
	
			$categ=$categ . 
				"<tr>
				<td width=1% valign='top'><img src='img/red-pushpin-24.png'></td>				
	 	   		<td valign='top'><span style='font-size:13px;font-weight:bold'>$category_name</span></td>
	 	   		<td width=1%>$edit_icon</td>
				<td valign='top'>$delete_icon</td>
				</tr>";
		}
		
		
	
	
	$categ="<div style='height:500px'>$categ</table></div>";
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$categ");}
	echo $tpl->_ENGINE_parse_body("$categ");		
}
function main_rules_delcategory_weight(){
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->DelCategory_phrase_weight($_GET["DansGuardian_delcategoryWeight"]);
}
function main_rules_addcategory_weight(){
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	writelogs("add {$_GET["rule_main"]} rule...{$_GET["DansGuardian_addcategoryWeight"]}",__FUNCTION__,__FILE__);
	$dans->AddCategory_phrase_weight($_GET["DansGuardian_addcategoryWeight"],$_GET["rule_main"]);
	}
	

	
	
?>