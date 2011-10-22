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
	if(isset($_GET["move-category-popup"])){MoveCategory_popup();exit;}
	if(isset($_POST["MoveCategorizedWebsite"])){MoveCategorizedWebsite();exit;}
	
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{categories}");
	if($_GET["category"]<>null){$title=$title."::{$_GET["category"]}";}
	if($_GET["website"]<>null){
		if(preg_match("#^www\.(.+)#", $_GET["website"],$re)){$_GET["website"]=$re[1];}
		$title=$title."::{$_GET["website"]}";
	}
	$start="YahooWin4('720','$page?popup=yes&category={$_GET["category"]}&website={$_GET["website"]}','$title');";
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
	$movetext=$tpl->_ENGINE_parse_body("{move}");
	if($category==null){return;}
	$q=new mysql_squid_builder();
	$tableN="category_".$q->category_transform_name($category);
	$searchOrg=$_GET["search"];
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
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody>";
	
	$results=$q->QUERY_SQL($sql);
	
	$number=mysql_num_rows($results);
	if($number==0){
		if($searchOrg<>null){
			if(strpos($searchOrg, "*")==0){
		$html="
		<center style='margin:30px'>
		<div style='font-size:16px'><code>http://$searchOrg</code></div>
		<div style='font-size:14px'><a href=\"javascript:blur();\" 
		OnClick=\"javascript:Loadjs('squid.visited.php?add-www=yes&websitetoadd=$searchOrg')\"
		style='font-size:14px;font-weight:bold;text-decoration:underline'
		>{webiste_notfound_additask}</a>
		</div>
		</center>
		";
		echo $tpl->_ENGINE_parse_body($html);
		return;
		}
		
		}
		
	}
	
	
	
	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}		
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["pattern"]==null){$q->QUERY_SQL("DELETE FROM $tableN WHERE zmd5='{$ligne["zmd5"]}'");continue;}
		$delete=imgtootltip("delete-32.png","{delete}","DeleteCategorizedWebsite('{$ligne["zmd5"]}')");
		$move=imgtootltip("arrow-right-32.png","{move}","MoveCategorizedWebsite('{$ligne["zmd5"]}','{$ligne["pattern"]}')");
		
		$table=$table."
		<tr class=$classtr>
		<td style='width:1%;font-size:14px' nowrap>{$ligne["zDate"]}</td>
		<td style='width:1%;font-size:14px' nowrap>{$ligne["pattern"]}</td>
		<td>$move</td>
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
	
	function MoveCategorizedWebsite(zmd5,website){
		YahooWin5(550,'$page?move-category-popup=yes&website='+website+'&zmd5='+zmd5+'&category-source=$category&table-source=$tableN','$movetext::'+website);
	}
	
</script>	
	";
	echo $tpl->_ENGINE_parse_body($table);
	
}

function MoveCategory_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$category=$_GET["category-source"];	
	$movetext=$tpl->javascript_parse_text("{move}");
	$webiste=$_GET["website"];
	$tableN=$_GET["table-source"];
	$zmd5=$_GET["zmd5"];
	$html="
	<div id='move-category-div'>
	<div class=explain>{move_category_explain}</div>
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{category}:</td>
				<td><div id='catsmove_list'></div></td>
				<td width=1%>". button("{move}","MoveCategoryPerform()")."</td>
			</tr>
		</tbody>
	</table>
	<span id='catmove-explain'></span>
	
	</div>

	<script>
		
		
		var x_MoveCategoryPerform= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			if(document.getElementById('cats_query_list')){SearchByCategory();}
			YahooWin5Hide();
		}		
		
		function MoveCategoryPerform(){
			var nextCategory=document.getElementById('CategoryNext').value;
			if(confirm('$movetext $webiste/$category ->  $webiste/'+nextCategory+'?')){
				var XHR = new XHRConnection();
				XHR.appendData('MoveCategorizedWebsite','$zmd5');
				XHR.appendData('TABLE','$tableN');
				XHR.appendData('NextCategory',nextCategory);
				XHR.appendData('website','$webiste');
				AnimateDiv('move-category-div');
				XHR.sendAndLoad('$page', 'POST',x_MoveCategoryPerform);				
			}
		
		}
	
		function MoveCategoryPerformText(){
			var nextCategory=document.getElementById('CategoryNext').value;	
			LoadAjax('catmove-explain','squid.visited.php?cat-explain='+nextCategory);
		
		}
		
		LoadAjax('catsmove_list','$page?field-list=yes&category=$category&callback=MoveCategoryPerformText&field-name=CategoryNext&callbackAfter=MoveCategoryPerformText');
	</script>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}



function field_list(){
	$page=CurrentPageName();
	$def=$_COOKIE["urlfilter_category_selected"];
	if($_GET["category"]<>null){$def=$_GET["category"];}
	
	$tpl=new templates();	
	$dans=new dansguardian_rules();
	while (list ($num, $ligne) = each ($dans->array_blacksites) ){$array[$num]=$num;}
	$array[null]="{select}";
	$callback="SearchByCategory";
	$callbackjs="<script>$callback();</script>";
	
	$fieldname="category_selected";
	if($_GET["field-name"]<>null){$fieldname=$_GET["field-name"];}
	if($_GET["callback"]<>null){$callback=$_GET["callback"];}
	if($_GET["callbackAfter"]<>null){$callbackjs="<script>{$_GET["callbackAfter"]}();</script>";}
	$html=Field_array_Hash($array, $fieldname,$def,"$callback()",null,0,"font-size:16px");
	echo $tpl->_ENGINE_parse_body($html."$callbackjs");

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
	$sock->getFrameWork("squid.php?export-deleted-categories=yes");
}

function MoveCategorizedWebsite(){
	$q=new mysql_squid_builder();
	$sock=new sockets();
	$md5=$_POST["MoveCategorizedWebsite"];
	$nextCategory=trim($_POST["NextCategory"]);
	if($nextCategory==null){echo "Next category = Null\n";return;}
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	$table=$_POST["TABLE"];
	$sql="SELECT * FROM $table WHERE zmd5='$md5'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if(!$q->ok){echo $q->mysql_error;return;}
	$www=$ligne["pattern"];
	$sql="INSERT IGNORE INTO categorize_delete (sitename,category,zmd5) VALUES ('{$ligne["pattern"]}','{$ligne["category"]}','{$ligne["zmd5"]}')";
	$q->QUERY_SQL($sql);
	if(!$q->ok){echo $q->mysql_error;return;}
	$q->QUERY_SQL("UPDATE $table SET enabled=0 WHERE zmd5='$md5'");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock->getFrameWork("squid.php?export-deleted-categories=yes");
	
	
	$newmd5=md5($www.$nextCategory);
	$q->QUERY_SQL("INSERT IGNORE INTO categorize_changes (zmd5,sitename,category) VALUES('$newmd5','$www','$nextCategory')");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$q->CreateCategoryTable($nextCategory);
	$category_table=$q->category_transform_name($nextCategory);	
	$q->QUERY_SQL("INSERT IGNORE INTO categorize (zmd5,zDate,category,pattern,uuid) VALUES('$newmd5',NOW(),'$nextCategory','$www','$uuid')");
	if(!$q->ok){echo $q->mysql_error;return;}
	$q->QUERY_SQL("INSERT IGNORE INTO category_$category_table (zmd5,zDate,category,pattern,uuid,enabled) VALUES('$md5',NOW(),'$nextCategory','$www','$uuid',1)");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
	$cats=addslashes($q->GET_CATEGORIES($www,true));
	
	$q->QUERY_SQL("UPDATE visited_sites SET category='$cats' WHERE sitename='$www'");
	if(!$q->ok){echo $q->mysql_error."\n";echo $sql."\n";}	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?export-community-categories=yes");
	$sock->getFrameWork("squid.php?re-categorize=yes");	
	
	
	
	
	
}
