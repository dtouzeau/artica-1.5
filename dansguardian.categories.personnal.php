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
	
	if(isset($_GET["popup-personal"])){popup_personal();exit;}
	if(isset($_GET["popup-personal-list"])){echo popup_personal_list($_GET["rule_main"],$_GET["category"]);exit;}
	if(isset($_GET["personal_category_delete"])){personal_category_delete();exit;}
	if(isset($_GET["WebsiteToAdd"])){echo popup_personal_add();exit;}
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{personal_categories}");
	$html="
	var mem_dans_personal_cat='';
	function DANSGUARDIAN_LOAD_PCATEGORIES(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
	var x_dansguardian_addPersonalcategory=function(obj){
	      DANSGUARDIAN_LOAD_PCATEGORIES();
	}	
	
	function dansguardian_addPersonalcategory(){
	 		var XHR = new XHRConnection();
	        XHR.appendData('DansGuardian_addcategory',document.getElementById('blacklist').value);
	        XHR.appendData('rule_main','{$_GET["rule_main"]}');
	        document.getElementById('main_rules_categories_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';       
	        XHR.sendAndLoad('$page', 'GET',x_dansguardian_addPersonalcategory);  
	    
	}
	
	function dansguardian_edit_personal_category(category){
		YahooWin3(650,'$page?popup-personal=yes&rule_main={$_GET["rule_main"]}&category='+category,category);
	
	}
	
	var x_CategoryAdd_add=function(obj){
	     LoadAjax('personal-list','$page?popup-personal-list=yes&category='+mem_dans_personal_cat+'&rule_main={$_GET["rule_main"]}');
	}	
	
	function CategoryAdd_add(){
		var XHR = new XHRConnection();
			mem_dans_personal_cat=document.getElementById('category').value;
	        XHR.appendData('WebsiteToAdd',document.getElementById('WebsiteToAdd').value);
	        XHR.appendData('rule_main','{$_GET["rule_main"]}');
	        XHR.appendData('category',mem_dans_personal_cat);
	        document.getElementById('personal-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';       
	        XHR.sendAndLoad('$page', 'GET',x_CategoryAdd_add);  
			}
			
	function personal_category_delete(ID){
			var XHR = new XHRConnection();
			mem_dans_personal_cat=document.getElementById('category').value;
			XHR.appendData('personal_category_delete',ID);
			XHR.appendData('rule_main','{$_GET["rule_main"]}');
			XHR.sendAndLoad('$page', 'GET',x_CategoryAdd_add);
	}
			
	function CategoryAdd_enter(e){
		if(checkEnter(e)){CategoryAdd_add();return;}
	}			
	
	
function dansguardian_delPersonalcategory(index){
 		var XHR = new XHRConnection();
        XHR.appendData('DansGuardian_delcategory',index);
        XHR.appendData('rule_main','{$_GET["rule_main"]}');
        document.getElementById('main_rules_categories_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
        XHR.sendAndLoad('$page', 'GET',x_dansguardian_addPersonalcategory);  
}

	 var X_DansCategoryEnable= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
	}

function DansPersonalCategoryEnable(md,cat,index,rule_main){
 	var XHR = new XHRConnection();
 	XHR.appendData('rule_main','{$_GET["rule_main"]}');
	if(document.getElementById(md).checked){
		XHR.appendData('DansGuardian_addcategory',cat);
		
	}else{
		 XHR.appendData('DansGuardian_delcategory',index);
	}
	XHR.sendAndLoad('$page', 'GET',X_DansCategoryEnable);  
}


	
	DANSGUARDIAN_LOAD_PCATEGORIES()";
	
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
	<input type='hidden' name='rule_main' value='$rule_main'>
	<p class=caption>{categories_explain}</p>
	<div id='main_rules_categories_list'>".main_rules_categories_list("$rule_main",1)."</div>
	";	




$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("<br>$html<br>$categ<br>","dansguardian.index.php");}
	echo $tpl->_ENGINE_parse_body("$html<br>$categ<br>");	
	
	
}

function main_rules_categories_fieldlist($noecho=0){
	$dans=new dansguardian_rules();
	$array=$dans->array_blacksites;
	$dansguardian=new dansguardian();
	
	
	if(is_array($array)){
		while (list ($num, $val) = each ($array) ){
			$array[$val]=$val;
		}
	}
	
	
	if($noecho==1){
		return Field_array_Hash($array,'blacklist',null,null,null,0,"font-size:13px;padding:3px");
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body(Field_array_Hash($array,'blacklist',null));
	}
	
}
function main_rules_categories_list($rule_main,$noecho=0){
	$dans=new dansguardian_rules();
	$html="
	<table style='width:100%'>
	<tr>
		<th>{category}</th>
		<th>{edit}</th>
		<th colspan=2>{enabled}</th>
	</tr>";
	$q=new mysql();
	$sql="SELECT ID,category FROM dansguardian_personal_categories WHERE category_type='enabled' AND RuleID=$rule_main";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$val=$ligne["category"];
		$array_selected[$val]=$ligne["ID"];
	}
	
	$array_cat=$dans->array_blacksites;
	
	while (list ($num, $val) = each ($array_cat) ){
		if($array_selected[$num]>0){$enabled=1;}else{$enabled=0;}
		$md=md5($num);
		$edit_icon=imgtootltip("icon_edit.gif","{edit}","dansguardian_edit_personal_category('$num')");
		$html=$html."
		<tr ". CellRollOver().">
			<td><strong style='font-size:11px'>$val</td>
			<td align='center' valign='middle'>$edit_icon</td>
			<td>".Field_checkbox("$md",1,$enabled,"DansPersonalCategoryEnable('$md','$num','{$array_selected[$num]}','$rule_main')")."</td>
			<td>$num</td>
			
		</tr> 
		
		";
		
	}
	
$categ="<div style='width:100%;height:600px;overflow:auto'>$html</div>";
$tpl=new templates();
if($noecho==1){return $tpl->_ENGINE_parse_body($categ);}
echo $tpl->_ENGINE_parse_body("$categ");	
	
}













function _main_rules_categories_list($rule_main,$noecho=0){
	$dansguardian=new dansguardian();
	$array_categories_user=$dansguardian->DefinedCategoryBlackListLoad();
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	//bannedsitelist
	
	$q=new mysql();
	$sql="SELECT * FROM dansguardian_personal_categories WHERE category_type='enabled' AND RuleID=$rule_main";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	

	$categ="
	<table style='width:99%'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){		
		$num=$ligne["ID"];
		$val=$ligne["category"];
		if(trim($val)==null){continue;}
		$delete_icon=imgtootltip("ed_delete.gif","{delete}","dansguardian_delPersonalcategory('{$ligne["ID"]}')");
		$edit_icon=imgtootltip("icon_edit.gif","{edit}","dansguardian_edit_personal_category('{$ligne["category"]}')");
		$style=CellRollOver("dansguardian_edit_personal_category('{$ligne["category"]}')");
		$categ=$categ . 
		"<tr>
			<td width=1% valign='top'><img src='img/red-pushpin-24.png'></td>				
				<td valign='top' $style><span style='font-size:13px;font-weight:bold'>$val:{$dans->array_blacksites[$val]}</span></td>
				<td valign='top'>$delete_button</td>
				<td width=1%>$edit_icon</td>
				<td width=1%>$delete_icon</td>
			</tr>
				";
			
		}
		
	
	$categ="<div style='height:500px'>$categ</div>";
	
$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$categ");}
	echo $tpl->_ENGINE_parse_body("$categ");		
	
	}
	
function main_rules_addcategory(){
	writelogs("add new category ->{$_GET["rule_main"]}={$_GET["DansGuardian_addcategory"]} ",__FUNCTION__,__FILE__);
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->AddPersonalCategory($_GET["DansGuardian_addcategory"],$_GET["rule_main"]);
	}	

	
function popup_personal_add(){
	$_GET["WebsiteToAdd"]=str_replace("http://",'',$_GET["WebsiteToAdd"]);
	$_GET["WebsiteToAdd"]=str_replace("https://",'',$_GET["WebsiteToAdd"]);
	$_GET["WebsiteToAdd"]=str_replace("ftp://",'',$_GET["WebsiteToAdd"]);
	writelogs("add new webiste ->{$_GET["rule_main"]}={$_GET["WebsiteToAdd"]} ",__FUNCTION__,__FILE__);
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->AddPersonalWebSiteOnCategory($_GET["WebsiteToAdd"],$_GET["category"],$_GET["rule_main"]);		
}

function personal_category_delete(){
	$ID=$_GET["personal_category_delete"];
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->DeletePersonalCategoryWebSite($ID);
	
}
	
function main_rules_delcategory(){
	$dans=new dansguardian_rules($_GET["hostname"],$_GET["rule_main"]);
	$dans->DelPersonalCategory($_GET["DansGuardian_delcategory"]);
}


function popup_personal(){
	$rule_main=$_GET["rule_main"];
	writelogs("Loading default rule $rule_main...",__FUNCTION__,__FILE__);
	$rules=popup_personal_list($rule_main,$_GET["category"]);
	
$html="
	
	<input type='hidden' name='category' id='category' value='{$_GET["category"]}'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<p class=caption>{add_block_site_explain}</p>
			<table style='width:100%'>
					<tr>
					<td>" . Field_text('WebsiteToAdd',null,'font-size:13px;padding:3px',null,null,null,false,"CategoryAdd_enter(event)")."</td>
					<td>
					<td width=1%>". imgtootltip("plus-24.png","{add}","CategoryAdd_add()")."

					</td>
					</tr>
			</table>
		
		
			</td>
		<td valign='top'>$apply</td>
	</tr>
	</table>
	
	<br>
	<div id='personal-list' style='width:100%;height:300px;overflow:auto'>
	$rules
	</div>
	";	


	$tpl=new templates();
	echo $tpl->_parse_body("$html<br>$categ<br>");
}
function popup_personal_list($rule_main,$category){
		$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
		$sql="SELECT * FROM dansguardian_personal_categories WHERE category='$category' AND category_type='data' AND RuleID=$rule_main ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$style=CellRollOver();
	$categ="
	<table style='width:99%'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$num=$ligne["ID"];
		$pattern=$ligne["pattern"];
	$categ=$categ . 
		"<tr $style>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td width=99%><strong style='font-size:13px'>$pattern</strong></td>
		<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"personal_category_delete('$num')") ."</td>
		</tr>
		";
		}
	$categ=$categ . "</table>";
	
	
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$categ");}
	return $tpl->_ENGINE_parse_body("$categ");		
	$tpl=new templates();

}

	
	
?>