<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.dansguardian.inc');
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsDansGuardianAdministrator){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["categories"])){categories();exit;}
if(isset($_GET["category-search"])){categories_search();exit;}

if(isset($_GET["add-perso-cat-js"])){add_category_js();exit;}
if(isset($_GET["add-perso-cat-popup"])){add_category_popup();exit;}

if(isset($_POST["category_text"])){add_category_save();exit;}

if(isset($_GET["events"])){events();exit;}
if(isset($_GET["updtdb-list-search"])){events_search();exit;}

if(isset($_GET["compile-db-js"])){compile_db_js();exit;}
if(isset($_POST["compile-db-perform"])){compile_db_perform();exit;}

if(isset($_GET["compile-all-dbs-js"])){compile_all_db_js();exit;}
if(isset($_POST["compile-alldbs-perform"])){compile_all_db_perform();exit;}




tabs();

function compile_db_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ask=$tpl->javascript_parse_text("{confirm_dnsg_compile_db} {$_GET["compile-db-js"]}");
	$html="
	
	
var X_compiledb= function (obj) {
		var results=obj.responseText;
		if(results.length>1){alert(results);}
		if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
	}
	
	function compiledb(){
		if(confirm('$ask')){
			var XHR = new XHRConnection();
			XHR.appendData('compile-db-perform','{$_GET["compile-db-js"]}');
			XHR.sendAndLoad('$page', 'POST',X_compiledb);
		
		}
	}
	
	compiledb();
	";
	
	echo $html;
	
}

function compile_all_db_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ask=$tpl->javascript_parse_text("{confirm_dnsg_compileall_db}");
	$html="
	
	
var X_compileAlldbs= function (obj) {
	var results=obj.responseText;
	if(results.length>1){alert(results);}
	if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
	}
	
	function compileAlldbs(){
		if(confirm('$ask')){
			var XHR = new XHRConnection();
			XHR.appendData('compile-alldbs-perform','yes');
			XHR.sendAndLoad('$page', 'POST',X_compileAlldbs);
		
		}
	}
	
	compileAlldbs();
	";
	
	echo $html;	
	
}

function compile_db_perform(){
	$sock=new sockets();
	$sock->getFrameWork("squid.php?ufdbguard-compile-database={$_POST["compile-db-perform"]}");
	}
function compile_all_db_perform(){
	$sock=new sockets();
	$sock->getFrameWork("squid.php?ufdbguard-compile-alldatabases=yes");	
}



function tabs(){
	$tpl=new templates();
	$page=CurrentPageName();
	$array["categories"]='{databases}';
	$array["events-status"]='{update_status}';
	$array["events"]='{update_events}';
	


	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="events-status"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"squid.blacklist.php?status=yes\" style='font-size:14px'><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=$time\" style='font-size:14px'><span>$ligne</span></a></li>\n");
	}
	
	
	
	echo "
	<div id=main_databasesCAT_quicklinks_tabs style='width:99%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
			$(document).ready(function(){
				$('#main_databasesCAT_quicklinks_tabs').tabs();
			});
		</script>";	

}



function categories(){
	
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<center>
	<table style='width:55%' class=form>
	<tbody>
		<tr><td class=legend>{categories}:</td>
		<td>". Field_text("category-dnas-search",null,"font-size:16px;width:220px",null,null,null,false,"CategoryDansSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","CategoryDansSearch()")."</td>
		</tr>
	</tbody>
	</table>
	</center>
	
	<div id='dansguardian2-category-list' style='width:100%;height:350px;overlow:auto'></div>
	
	<script>
		function CategoryDansSearchCheck(e){
			if(checkEnter(e)){CategoryDansSearch();}
		}
		
		function CategoryDansSearch(){
			var se=escape(document.getElementById('category-dnas-search').value);
			LoadAjax('dansguardian2-category-list','$page?category-search='+se);
		
		}
		
		CategoryDansSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function categories_search(){

	$search=$_GET["category-search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);	
	if(CACHE_SESSION_GET(__FUNCTION__.$search, __FILE__,15)){return;}	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$q=new mysql_squid_builder();	
	$dans=new dansguardian_rules();
	$EnableWebProxyStatsAppliance=$sock->GET_INFO("EnableWebProxyStatsAppliance");
	if(!is_numeric($EnableWebProxyStatsAppliance)){$EnableWebProxyStatsAppliance=0;}

	
	$sql="SELECT * FROM personal_categories";
	$results=$q->QUERY_SQL($sql);
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		//$q->CreateCategoryTable($ligne["category"]);
		$PERSONALSCATS[$ligne["category"]]=$ligne["category_description"];
	}
	
	$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'category_$search'";
	$results=$q->QUERY_SQL($sql);
	$add=imgtootltip("plus-24.png","{add} {category}","Loadjs('$page?add-perso-cat-js=yes')");
	$compile_all=imgtootltip("compile-distri-32.png","{saveToDisk} {all}","Loadjs('$page?compile-all-dbs-js=yes')");
	if(!$q->ok){echo  " <H2>Fatal Error: $q->mysql_error</H2>";}
	
		
	$sock=new sockets();
	$sock->getFrameWork("ufdbguard.php?db-status=yes");
	$ArraySIZES=unserialize(@file_get_contents("ressources/logs/web/ufdbguard_db_status"));
	

	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$CountDeMembers=0;
		$table=$ligne["c"];
		$sizedb=$ArraySIZES[$table]["DBSIZE"];
		$sizeTXT=$ArraySIZES[$table]["TXTSIZE"];
		
		
		if(!preg_match("#^category_(.+)#", $table,$re)){continue;}
		$categoryname=$re[1];
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","DansGuardianEditMember('{$ligne["ID"]}','{$ligne["pattern"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","DansGuardianDeleteMember('{$ligne["ID"]}')");
		$compile=imgtootltip("compile-distri-32.png","{saveToDisk}","DansGuardianCompileDB('$categoryname')");
		$color="black";
		
		$items=$q->COUNT_ROWS($ligne["c"]);
		$TOTAL_ITEMS=$TOTAL_ITEMS+$items;
		
		if(!isset($dans->array_blacksites[$categoryname])){
			if(isset($dans->array_blacksites[str_replace("_","-",$categoryname)])){$categoryname=str_replace("_","-",$categoryname);}
			if(isset($dans->array_blacksites[str_replace("_","/",$categoryname)])){$categoryname=str_replace("_","/",$categoryname);}
		}
		if($dans->array_pics[$categoryname]<>null){$pic="<img src='img/{$dans->array_pics[$categoryname]}'>";}else{$pic="&nbsp;";}
	
		if($EnableWebProxyStatsAppliance==0){
				if($sizedb==0){$pic="<img src='img/warning-panneau-32.png'>";}
				$sizedb_org=$sizedb;
				$sizedb=FormatBytes($sizedb/1024);
		}else{
			$sizedb_org=$q->TABLE_SIZE($table);
			$sizedb=FormatBytes($sizedb_org/1024);
		}
		
		$sizedb=texttooltip($sizedb,"$sizedb_org bytes",null,null,1,"font-size:14px;font-weight:bold;color:$color");
		
		
	
		$linkcat=null;
		$text_category=$dans->array_blacksites[$categoryname];
		if(isset($PERSONALSCATS[$categoryname])){
			$text_category=$PERSONALSCATS[$categoryname];
			if($pic=="&nbsp;"){$pic="<img src='img/20-categories-personnal.png'>";}
			$linkcat="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?add-perso-cat-js=yes&cat=$categoryname')\"
			style='font-size:14px;font-weight:bold;color:$color;text-decoration:underline'>";
		}
		
		if($EnableWebProxyStatsAppliance==0){
			if($sizedb_org<35){$pic="<img src='img/warning-panneau-32.png'>";}
		}
		$viewDB=imgtootltip("mysql-browse-database-32.png","{view}","javascript:Loadjs('squid.categories.php?category={$categoryname}')");
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$pic</td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=99%>
			$linkcat$categoryname</a><div style='font-size:11px;width:100%;font-weight:normal'>{$text_category}</div></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% nowrap align='right'>$sizedb</td>
			<td width=1%>$viewDB</td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% nowrap align='right'>".numberFormat($items,0,""," ")."</td>
			<td width=1%>$compile</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$TOTAL_ITEMS=numberFormat($TOTAL_ITEMS,0,""," ");	
	$header="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th width=99%>{category}</th>
		<th width=1%>{size}</th>
		<th width=1% colspan=2>$TOTAL_ITEMS {items}</th>
		<th width=1%>$compile_all</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	

	
	$html=$header.$html."</table>
	</center>
	
	<script>
		function DansGuardianCompileDB(category){
			Loadjs('$page?compile-db-js='+category);
		}
	</script>
	";
	CACHE_SESSION_SET(__FUNCTION__.$search, __FILE__,$tpl->_ENGINE_parse_body($html));
}

function add_category_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{add}::{personal_category}");
	if($_GET["cat"]<>null){$title=$tpl->_ENGINE_parse_body("{$_GET["cat"]}::{personal_category}");}
	$html="YahooWin4('505','$page?add-perso-cat-popup=yes&cat={$_GET["cat"]}','$title');";
	echo $html;
}

function add_category_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$error_max_dbname=$tpl->javascript_parse_text("{error_max_database_name_no_more_than}");
	$error_category_textexpl=$tpl->javascript_parse_text("{error_category_textexpl}");
	
	if($_GET["cat"]<>null){
		$q=new mysql_squid_builder();
		$sql="SELECT category_description FROM personal_categories WHERE category='{$_GET["cat"]}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	}
	
	$html="
	<div id='perso-cat-form'>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>{category}:</td>
		<td>". Field_text("category-to-add","{$_GET["cat"]}","font-size:16px;padding:3px;width:320px")."</td>
	</tr>
	<tr>
		<td class=legend >{description}:</td>
		<td><textarea name='category_text' id='category_text' style='height:50px;overflow:auto;width:320px;font-size:16px'>{$ligne["category_description"]}</textarea>
	</tr>
	
	<tr>
	<td colspan=2 align='right'><hr>". button("{apply}","SavePersonalCategory()")."</td>
	</tr>
	</tbody>
	</table>
	</div>
	<script>
var X_SavePersonalCategory= function (obj) {
	var results=obj.responseText;
	if(results.length>1){alert(results);}
	if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
	YahooWin4Hide();
	}
		
	function SavePersonalCategory(){
		var XHR = new XHRConnection();
		var db=document.getElementById('category-to-add').value;
		var expl=document.getElementById('category_text').value;
		if(db.length<5){return;}
		if(expl.length<5){alert('$error_category_textexpl');return;}
		if(db.length>15){alert('$error_max_dbname: 15');return;}
		XHR.appendData('personal_database',db);
		XHR.appendData('category_text',document.getElementById('category_text').value);
		AnimateDiv('perso-cat-form');
		XHR.sendAndLoad('$page', 'POST',X_SavePersonalCategory);				
	}	
	
	function checkform(){
		var cat='{$_GET["cat"]}';
		if(cat.length>0){document.getElementById('category-to-add').disabled=true;}
	}
checkform();
</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function add_category_save(){
	include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
	$ldap=new clladp();
	$dans=new dansguardian_rules();
	$_POST["personal_database"]=strtolower($ldap->StripSpecialsChars($_POST["personal_database"]));
	
	if($_POST["personal_database"]=="security"){$_POST["personal_database"]="security2";}
	
	if(isset($dans->array_blacksites[$_POST["personal_database"]])){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{category_already_exists}");
		return;
	}
	
	$_POST["category_text"]=addslashes($_POST["category_text"]);
	$q=new mysql_squid_builder();
	$sql="SELECT category FROM personal_categories WHERE category='{$_POST["personal_database"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if($ligne["category"]<>null){
		$sql="UPDATE personal_categories SET category_description='{$_POST["category_text"]}'";
	}else{
		$sql="INSERT IGNORE INTO personal_categories (category,category_description) VALUES ('{$_POST["personal_database"]}','{$_POST["category_text"]}');";
	}
	
	
	
	$q->QUERY_SQL($sql);
	if(!$q->ok){echo $q->mysql_error;return;}
	$q->CreateCategoryTable($_POST["personal_database"]);
	$sock=new sockets();
	$sock->getFrameWork("squid.php?export-web-categories=yes");
	
}



function events(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$q=new mysql_squid_builder();
	$sql="SELECT category FROM updateblks_events GROUP BY category ORDER BY category";
	$results=$q->QUERY_SQL($sql);	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$cat[$ligne["category"]]=$ligne["category"];
	}
	$cat[null]="{select}";
	
	$html="
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{service}:</td>
				<td>". Field_text("updtdb-search",null,"font-size:16px",null,null,null,false,"updtdbSearchEventsCheck(event)")."</td>
				<td class=legend>{category}:</td>
				<td>". Field_array_Hash($cat, "updtdb-event-category",null,"style:font-size:14px")."</td>
				<td>". button("{search}","updtdbSearchEvents()")."</td>
			</tr>
		</tbody>
	</table>
	<div id='updtdb-list-table' style='width:100%;height:350px;overflow:auto;background-color:white'></div>
	
	<script>
		function updtdbSearchEventsCheck(e){
			if(checkEnter(e)){ufdbguardSearchEvents();}
		}
	
		function updtdbSearchEvents(){
			var se=escape(document.getElementById('updtdb-search').value);
			var cat=escape(document.getElementById('updtdb-event-category').value);
			LoadAjax('updtdb-list-table','$page?updtdb-list-search=yes&search='+se+'&category='+cat);
		}
	
	updtdbSearchEvents();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function events_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$emailing_campain_linker_delete_confirm=$tpl->javascript_parse_text("{emailing_campain_linker_delete_confirm}");
	
	$style="style='font-size:14px;'";
	if($_GET["category"]<>null){
		$catsql=" AND `category`='{$_GET["category"]}'";
	}
	
	$sql="SELECT * FROM updateblks_events WHERE `text` LIKE '$search' $catsql ORDER BY zDate DESC LIMIT 0,50";
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{date}</th>
		<th>{events}&nbsp;|&nbsp;$search</th>
		<th>{category}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$q=new mysql_squid_builder();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql);
		$cs=0;
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if(preg_match("#line:([0-9]+)\s+script:(.+)#", $ligne["text"],$re)){
			$ligne["text"]=str_replace("line:{$re[1]} script:{$re[2]}", "", $ligne["text"]);
		}
		$line=$re[1];
		$file=$re[2];
		$ligne["text"]=htmlentities($ligne["text"]);
		$ligne["text"]=nl2br($ligne["text"]);
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>{$ligne["zDate"]}</td>
			<td width=99% $style nowrap>{$ligne["text"]}<div style='font-size:11px'>$file Pid:{$ligne["PID"]} - {$ligne["function"]}() line:$line</div></td>
			<td width=1% $style nowrap>{$ligne["category"]}</td>
			
		</tr>
		";
	}
	$html=$html."</tbody></table>
	
	<script>

	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}