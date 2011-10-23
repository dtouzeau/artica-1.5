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
	if(isset($_GET["subtitles-categories"])){subtitle_categories();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["field-list"])){field_list();exit;}
	if(isset($_GET["query"])){query();exit;}
	if(isset($_POST["DeleteCategorizedWebsite"])){DeleteCategorizedWebsite();exit;}
	if(isset($_GET["move-category-popup"])){MoveCategory_popup();exit;}
	if(isset($_POST["MoveCategorizedWebsite"])){MoveCategorizedWebsite();exit;}
	if(isset($_POST["MoveCategorizedWebsitePattern"])){MoveCategorizedWebsiteAll();exit;}
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
	$start="YahooWin4('720','$page?tabs=yes&category={$_GET["category"]}&website={$_GET["website"]}','$title');";
	$html="
	$start
	";
	echo $html;
	
}	

function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["popup"]='{manage}';
	$array["list"]='{status}';
	
	
	
	

while (list ($num, $ligne) = each ($array) ){
		if($num=="list"){
			$html[]= "<li><a href=\"dansguardian2.databases.php?categories=\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			continue;
		}
	
	
		$html[]= "<li><a href=\"$page?$num&category={$_GET["category"]}&website={$_GET["website"]}\"><span style='font-size:14px'>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_categories_zoom style='width:100%;font-size:14px'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_categories_zoom').tabs();
			
			
			});
		</script>");		
	
	
}

function subtitle_categories(){
	if(CACHE_SESSION_GET(__FUNCTION__, __FILE__,15)){return;}
	$tpl=new templates();
	$q=new mysql_squid_builder();
	$categories=$q->COUNT_CATEGORIES();
	$categories=numberFormat($categories,0,""," ");
	$tablescat=$q->LIST_TABLES_CATEGORIES();
	$tablescatNUM=numberFormat(count($tablescat),0,""," ");		
	$html="<div style='font-size:16px'><b>$categories</b> {websites_categorized}&nbsp;|&nbsp;<b>$tablescatNUM</b> {categories}</div>";
	CACHE_SESSION_SET(__FUNCTION__, __FILE__, $tpl->_ENGINE_parse_body($html));
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$def=$_COOKIE["urlfilter_website_selected"];
	if($_GET["website"]<>null){$def=$_GET["website"];}
		
	
	
	$html="
	<center id='subtitles-categories'></center>
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
	$moveall=imgtootltip("arrow-right-32.png","{move} {all}","MoveAllCategorizedWebsite()");
	$table="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:350px'>
<thead class='thead'>
	<tr>
	<th width=1%>{website} $category</th>
	<th>{date}</th>
	<th>$moveall</th>
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
		<div style='font-size:16px;margin-bottom:15px'><code>&laquo;http://$searchOrg&raquo; {in} &laquo;$category&raquo;</code></div>
		<div style='font-size:14px'><a href=\"javascript:blur();\" 
		OnClick=\"javascript:Loadjs('squid.visited.php?add-www=yes&websitetoadd=$searchOrg')\"
		style='font-size:14px;font-weight:bold;text-decoration:underline'
		>{webiste_notfound_additask}</a>
		</div>
		</center>
		<script>LoadAjaxTiny('subtitles-categories','$page?subtitles-categories=yes');</script>
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
	
	function MoveAllCategorizedWebsite(){
		YahooWin5(550,'$page?move-category-popup=yes&website=&zmd5=&category-source=$category&table-source=$tableN&bysearch={$_GET["search"]}','$movetext::{$_GET["search"]}');
		
	}
	
	LoadAjaxTiny('subtitles-categories','$page?subtitles-categories=yes');
	
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
	$button=button("{move}","MoveCategoryPerform()");
	if(isset($_GET["bysearch"])){
		$button=button("{move} {all}","MoveAllCategoryPerform()");
		
	}
	
	$html="
	<div id='move-category-div'>
	<div class=explain>{move_category_explain}</div>
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{category}:</td>
				<td><div id='catsmove_list'></div></td>
				<td width=1%>$button</td>
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
		
		function MoveAllCategoryPerform(){
			var nextCategory=document.getElementById('CategoryNext').value;
			if(confirm('$movetext {$_GET["bysearch"]}/$category ->  {$_GET["bysearch"]}/'+nextCategory+'?')){
				var XHR = new XHRConnection();
				XHR.appendData('MoveCategorizedWebsitePattern','{$_GET["bysearch"]}');
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

function MoveCategorizedWebsite($md5=null,$nextCategory=null,$table=null){
	$q=new mysql_squid_builder();
	$sock=new sockets();
	if($md5==null){$md5=$_POST["MoveCategorizedWebsite"];}
	if($nextCategory==null){$nextCategory=trim($_POST["NextCategory"]);}
	if($table==null){$table=trim($_POST["TABLE"]);}
	
	
	if($nextCategory==null){echo "Next category = Null\n";return;}
	if($table==null){echo "Table = Null\n";return;}
	if($md5==null){echo "md5 = Null\n";return;}
	
	
	if(!isset($GLOBALS["uuid"])){$GLOBALS["uuid"]=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));}
	$uuid=$GLOBALS["uuid"];
	
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

function MoveCategorizedWebsiteAll(){

		$q=new mysql_squid_builder();
		$search=trim($_POST["MoveCategorizedWebsitePattern"]);
		if(strlen($search)<4){echo "Wrong query...No search pattern";return;}
		
		$search="*$search*";
		$search=str_replace("**", "*", $search);
		$search=str_replace("**", "*", $search);
		$search=str_replace("*", "%", $search);
		$search=" AND pattern LIKE '$search'";
		$sql="SELECT * FROM {$_POST["TABLE"]} WHERE enabled=1 $search ORDER BY pattern";
		$results=$q->QUERY_SQL($sql);
		if(mysql_num_rows($results)==0){echo "Wrong query...No rows...";return;}
		if(mysql_num_rows($results)>200){echo "To many webistes to migrate: ".mysql_num_rows($results)." query is wrong...";return;}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["pattern"]==null){$q->QUERY_SQL("DELETE FROM {$_POST["TABLE"]} WHERE zmd5='{$ligne["zmd5"]}'");continue;}
			MoveCategorizedWebsite($ligne["zmd5"],$_POST["NextCategory"],$_POST["TABLE"]);
		}		
		
		
		
}

