<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.lvm.org.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.postfwd2.inc');
	
	if(!Isright()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
	}
	
	if(isset($_GET["objects-list-search"])){objects_search();exit;}
	if(isset($_GET["ObjectID-js"])){ObjectID_js();exit;}
	if(isset($_GET["ObjectID-popup"])){ObjectID_popup();exit;}
	
	if(isset($_POST["ObjectName"])){ObjectName_save();exit;}
	if(isset($_GET["object-datas-list"])){Object_data_list();exit;}
	if(isset($_POST["DeleteObjectItem"])){Object_data_delete();exit;}
	if(isset($_POST["DeleteObject"])){Object_delete();exit;}
page();	
	
function Isright(){
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsPostfixAdministrator){return true;}
	if(!$users->AsOrgStorageAdministrator){return false;}
	if(isset($_GET["ou"])){if($_SESSION["ou"]<>$_GET["ou"]){return false;}}
	return true;
	
	}
	
function ObjectID_js(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$q=new mysql();
	$title="{object}::{add}";
	if($_GET["ObjectID-js"]>0){
		$q=new mysql();
		$sql="SELECT * FROM postfwd2_objects WHERE ID='{$_GET["ObjectID-js"]}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$title="{object}::".$ligne["ObjectName"];
	}
	$title=$tpl->_ENGINE_parse_body($title);
	$html="YahooWin4('550','$page?ObjectID-popup=yes&ID={$_GET["ObjectID-js"]}&instance={$_GET["instance"]}','$title')";
	echo $html;
	
}

function ObjectID_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$q=new mysql();
	$sql="SELECT * FROM postfwd2_objects WHERE ID='{$_GET["ID"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$buttonname="{add}";
	if($_GET["ID"]>0){$buttonname="{apply}";}
	$html="
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>{name}:<td>
		<td>". Field_text("ObjectName",$ligne["ObjectName"],"font-size:16px;padding:3px;width:220px",null,null,null,false,"SaveObjectMainFormCheck(event)")."</td>
		<td width=1% align=right>". button($buttonname,"SaveObjectMainForm()")."</td>
	</tr>
	</tbody>
	</table>
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{rule}:</td>
				<td>". Field_text("objects-rule-search",null,"font-size:16px",null,null,null,false,"SearchobjectsRuleListCheck(event)")."</td>
				<td>". button("{search}","SearchobjectsRuleList()")."</td>
			</tr>
		</tbody>
	</table>
	<div id='object-datas-list' style='width:100%;height:350px;overflow:auto'></div>
<script>
function SearchobjectsRuleListCheck(e){
	if(checkEnter(e)){SearchobjectsRuleList();}
}
	
function SearchobjectsRuleList(){
	var ID={$_GET["ID"]};
	if(ID>0){
		var se=escape(document.getElementById('objects-search').value);
		LoadAjax('object-datas-list','$page?object-datas-list=yes&search='+se+'&instance={$_GET["instance"]}&ID={$_GET["ID"]}');
	}
}

var x_SaveObjectMainForm= function (obj) {
	var results=obj.responseText;
	if(results.length>5){alert(results);}
	var ID={$_GET["ID"]};
	if(ID==0){YahooWin4Hide();}	
	SearchobjectsList();		
}		

function SaveObjectMainFormCheck(e){
	if(checkEnter(e)){SaveObjectMainForm();exit;}
}
	
function SaveObjectMainForm(){
	var XHR = new XHRConnection();
	XHR.appendData('ObjectName',document.getElementById('ObjectName').value);
	XHR.appendData('ID','{$_GET["ID"]}');
	XHR.appendData('instance','{$_GET["instance"]}');
	XHR.sendAndLoad('$page', 'POST',x_SaveObjectMainForm);
}		
	SearchobjectsRuleList();	
</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function ObjectName_save(){
	$ID=$_POST["ID"];
	$_POST["ObjectName"]=addslashes($_POST["ObjectName"]);
	if($ID==0){
		$sql="INSERT INTO postfwd2_objects(ObjectName,instance) VALUES('{$_POST["ObjectName"]}','{$_POST["instance"]}')";
	}else{
		$sql="UPDATE postfwd2_objects SET ObjectName='{$_POST["ObjectName"]}' WHERE ID='$ID'";
	}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function Object_data_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$q=new mysql();
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$action_delete_rule=$tpl->javascript_parse_text("{action_delete_rule}");
	
	
		
	$add=imgtootltip("plus-24.png","{add}","PostfwdItemObject(0)");
	
	$sql="SELECT * FROM postfwd2_items WHERE item_data LIKE '$search' AND objectID='{$_GET["ID"]}' ORDER BY ID DESC LIMIT 0,50";
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th nowrap>{rule}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$postfwd2=new postfwd2();
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){
			if(strpos($q->mysql_error, "doesn't exist")>0){
				$q->check_postfwd2_table();
				if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
				$q=new mysql();
				$results=$q->QUERY_SQL($sql,"artica_backup");
			}
		}
		
		
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		//$selectUri="<a href=\"javascript:blur();\" OnClick=\"javascript:PostfwdItemObject('{$ligne["ID"]}');\" style='font-size:14px;text-decoration:underline'>";
		$delete=imgtootltip("delete-32.png","{delete}","DeleteObjectItem('{$ligne["ID"]}')");

		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap colspan=2 style='font-size:16px'>$selectUri{$postfwd2->item_to_text($ligne,true)}</a></td>	
			<td width=1% $delete</td>
		</tr>
		";
	}
$html=$html."</tbody>
	</table>
	<script>
	
	function PostfwdItemObject(itemid){
		YahooWin3('550','postfwd2.php?postfwd2-item=yes&itemid='+itemid+'&ID={$_GET["ID"]}&instance={$_GET["instance"]}&ByObject=yes','::'+itemid);
	}	
		
	function postfwd2ReloadItemsList(){
		SearchobjectsRuleList();
	}
	
	var x_DeleteObjectItem= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			if(document.getElementById('object-datas-list')){SearchobjectsRuleList();}
	}		
	
	function DeleteObjectItem(ID){	
		var XHR = new XHRConnection();
		XHR.appendData('DeleteObjectItem',ID);
		if(document.getElementById('object-datas-list')){AnimateDiv('object-datas-list');}
		XHR.sendAndLoad('$page', 'POST',x_DeleteObjectItem);
	}
</script>
";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function Object_data_delete(){
	
	$q=new mysql();
	$q->QUERY_SQL("DELETE FROM postfwd2_items WHERE ID='{$_POST["DeleteObjectItem"]}'","artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}


	
function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<div class=explain>{postfwd_objects_explain}</div>
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{objects}:</td>
				<td>". Field_text("objects-search",null,"font-size:16px",null,null,null,false,"SearchobjectsListCheck(event)")."</td>
				<td>". button("{search}","SearchobjectsList()")."</td>
			</tr>
		</tbody>
	</table>
	<div id='objects-list-table' style='width:100%;height:350px;overflow:auto;background-color:white'></div>
	
	<script>
		function SearchobjectsListCheck(e){
			if(checkEnter(e)){SearchobjectsList();}
		}
	
		function SearchobjectsList(){
			var se=escape(document.getElementById('objects-search').value);
			LoadAjax('objects-list-table','$page?objects-list-search=yes&search='+se+'&instance={$_GET["instance"]}');
		}
	
	SearchobjectsList();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function objects_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$q=new mysql();
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$action_delete_rule=$tpl->javascript_parse_text("{action_delete_rule}");
	
		
	$add=imgtootltip("plus-24.png","{add_object}","Loadjs('$page?ObjectID-js=0&instance={$_GET["instance"]}')");
	
	$sql="SELECT * FROM postfwd2_objects WHERE `ObjectName` LIKE '$search' AND instance='{$_GET["instance"]}' ORDER BY ObjectName LIMIT 0,50";
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th nowrap>{objects}</th>
		<th>{rules}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$selectUri="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?ObjectID-js={$ligne["ID"]}&instance={$_GET["instance"]}');\"
		style='font-size:16px;text-decoration:underline'>";
		
		$sql="SELECT COUNT(ID) as tcount FROM postfwd2_items WHERE objectID={$ligne["ID"]}";
		$ligne3=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		
		$delete=imgtootltip("delete-32.png","{delete}","DeleteObject('{$ligne["ID"]}')");
		$html=$html."
		<tr class=$classtr>	
			<td width=1% $style nowrap colspan=2>$selectUri{$ligne["ObjectName"]}</a></td>
			<td width=99% style='font-size:16px;' nowrap>{$ligne3["tcount"]}&nbsp;{rules}</td>
			<td width=1% $delete</td>
		</tr>
		";
	}
	$html=$html."</tbody></table>
	<script>
	var x_DeleteObject= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			if(document.getElementById('objects-list-table')){SearchobjectsList();}
			
		}		
	
		function DeleteObject(ID){
		if(confirm('$action_delete_rule')){			
			YahooWin3Hide();
			var XHR = new XHRConnection();
			XHR.appendData('DeleteObject',ID);
			if(document.getElementById('objects-list-table')){AnimateDiv('objects-list-table');}
			XHR.sendAndLoad('$page', 'POST',x_DeleteObject);
			}
			
		}
	
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function Object_delete(){
	$sql="DELETE FROM postfwd2_items WHERE objectID={$_POST["DeleteObject"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sql="DELETE FROM postfwd2_objects WHERE ID={$_POST["DeleteObject"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
}