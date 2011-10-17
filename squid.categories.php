<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["field-list"])){field_list();exit;}
	if(isset($_GET["query"])){query();exit;}
	if(isset($_POST["DeleteCategorizedWebsite"])){DeleteCategorizedWebsite();exit;}
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{categories}");
	if($_GET["category"]<>null){$title=$title."::{$_GET["category"]}";}
	if($_GET["website"]<>null){$title=$title."::{$_GET["website"]}";}
	$start="YahooWin3('720','$page?popup=yes&category={$_GET["category"]}&website={$_GET["website"]}','$title');";
	$html="
	$start
	";
	echo $html;
	
}	

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$def=$_COOKIE["urlfilter_website_selected"];
	if($_GET["website"]<>null){$def=$_GET["website"];}
	
	$html="
	<table style='width:100%' class=form>
	<tbody>
	<tr>
	<td class=legend>{category}:</td>
	<td><div id='cats_list'></div></td>
	<td class=legend>{website}:</td>
	<td>". Field_text("website_search",$def,"font-size:16px",null,null,null,false,"SearchByCategoryCheck(event)")."</td>
	<td width=1%>". button("{search}","SearchByCategory()")."</td>
	</tr>
	</tbody>
	</table>
	
	<div id='cats_query_list' style='width:100%;height:550px;overflow:auto'></div>
	
	<script>
	function SearchByCategoryCheck(e){
		if(checkEnter(e)){SearchByCategory();}
	}
	
	function SearchByCategory(){
		var search=escape(document.getElementById('website_search').value);
		var cat=escape(document.getElementById('category_selected').value);
		if(search.length>3){Set_Cookie('urlfilter_website_selected', document.getElementById('website_search').value, '3600', '/', '', '');}
		if(cat.length>3){Set_Cookie('urlfilter_category_selected', document.getElementById('category_selected').value, '3600', '/', '', '');}
		LoadAjax('cats_query_list','$page?query=yes&category='+cat+'&search='+search);
	
	}
	
	LoadAjax('cats_list','$page?field-list=yes&category={$_GET["category"]}');
		
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function query(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$category=$_GET["category"];
	if($category==null){return;}
	$q=new mysql_squid_builder();
	$tableN="category_".$q->category_transform_name($category);
	if($_GET["search"]<>null){
		$search=$_GET["search"];
		$search="*$search*";
		$search=str_replace("**", "*", $search);
		$search=str_replace("**", "*", $search);
		$search=str_replace("*", "%", $search);
		$search=" AND pattern LIKE '$search'";
	}
	
	$sql="SELECT * FROM $tableN WHERE enabled=1 $search ORDER BY pattern LIMIT 0,50 ";
	
	$table="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:350px'>
<thead class='thead'>
	<tr>
	<th width=1%>{website} $category</th>
	<th>{date}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody>";
	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}		
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["pattern"]==null){$q->QUERY_SQL("DELETE FROM $tableN WHERE zmd5='{$ligne["zmd5"]}'");continue;}
		$delete=imgtootltip("delete-32.png","{delete}","DeleteCategorizedWebsite('{$ligne["zmd5"]}')");
		
		
		$table=$table."
		<tr class=$classtr>
		<td style='width:1%;font-size:14px' nowrap>{$ligne["zDate"]}</td>
		<td style='width:1%;font-size:14px' nowrap>{$ligne["pattern"]}</td>
		<td>$delete</td>
		</tr>
		";
		
	}
	$table=$table."</tbody></table>
	<script>
		var x_DeleteCategorizedWebsite= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			SearchByCategory();
		}		
	
	
	function DeleteCategorizedWebsite(zmd5){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteCategorizedWebsite',zmd5);
		XHR.appendData('TABLE','$tableN');
		
		
		AnimateDiv('cats_query_list');
		XHR.sendAndLoad('$page', 'POST',x_DeleteCategorizedWebsite);	
	}	
</script>	
	";
	echo $tpl->_ENGINE_parse_body($table);
	
}



function field_list(){
	$page=CurrentPageName();
	$def=$_COOKIE["urlfilter_category_selected"];
	if($_GET["category"]<>null){$def=$_GET["category"];}
	
	$tpl=new templates();	
	$dans=new dansguardian_rules();
	while (list ($num, $ligne) = each ($dans->array_blacksites) ){$array[$num]=$num;}
	$array[null]="{select}";
	$html=Field_array_Hash($array, "category_selected",$def,"SearchByCategory()",null,0,"font-size:16px");
	echo $tpl->_ENGINE_parse_body($html."<script>SearchByCategory();</script>");

}
function DeleteCategorizedWebsite(){
	$q=new mysql_squid_builder();
	$md5=$_POST["DeleteCategorizedWebsite"];
	$table=$_POST["TABLE"];
	$sql="SELECT * FROM $table WHERE zmd5='$md5'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if(!$q->ok){echo $q->mysql_error;return;}
	$sql="INSERT IGNORE INTO categorize_delete (sitename,category,zmd5) VALUES ('{$ligne["pattern"]}','{$ligne["category"]}','{$ligne["zmd5"]}')";
	$q->QUERY_SQL($sql);
	if(!$q->ok){echo $q->mysql_error;return;}
	$q->QUERY_SQL("UPDATE $table SET enabled=0 WHERE zmd5='$md5'");
	if(!$q->ok){echo $q->mysql_error;return;}
	
}