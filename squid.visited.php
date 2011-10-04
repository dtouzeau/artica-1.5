<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.rtmm.tools.inc');
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
	if(isset($_GET["visited"])){visited();exit;}
	if(isset($_GET["visited-list"])){visited_list();exit;}
	
	if(isset($_GET["no-cat"])){not_categorized();exit;}
	if(isset($_GET["no-cat-list"])){not_categorized_list();exit;}
	
	if(isset($_GET["yes-cat"])){categorized();exit;}
	if(isset($_GET["yes-cat-list"])){categorized_list();exit;}	
	
	
	if(isset($_GET["free-cat"])){free_catgorized();exit;}
	if(isset($_POST["textToParseCats"])){free_catgorized_save();exit;}
	
	if(isset($_GET["params"])){parameters();exit;}
	if(isset($_GET["EnableCommunityFilters"])){parameters_save();exit;}
	
	
	if(isset($_GET["CategorizeAll-js"])){CategorizeAll_js();exit;}
	if(isset($_GET["CategorizeAll"])){CategorizeAll_popup();exit;}
	if(isset($_GET["CategorizeAll_category"])){CategorizeAll_perform();exit;}
	if(isset($_GET["cat-explain"])){CategorizeAll_explain();exit;}
	
	
	
	
	
js();

function CategorizeAll_js(){
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body("{visited_websites}");
	$categorize_this_query=$tpl->_ENGINE_parse_body("{categorize_this_query}");
	$page=CurrentPageName();
	$query=urlencode($_GET["query"]);
	$html="
	
	function CategorizeAll(){
			YahooWin4(550,'$page?CategorizeAll=$query','$categorize_this_query');
		
		}	
	
	CategorizeAll();";
	echo $html;	
	
}

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{visited_websites}");
	$categorize_this_query=$tpl->_ENGINE_parse_body("{categorize_this_query}");
	$start="YahooWin3('720','$page?popup=yes&day={$_GET["day"]}','$title');";
	if(isset($_GET["add-www"])){
		$title=$tpl->_ENGINE_parse_body("{add_websites}");
		$start="YahooWin3('420','$page?free-cat=yes','$title');";
	}
	
	$html="
	$start
	
	function CategorizeAll(query){
			YahooWin4(550,'$page?CategorizeAll='+escape(query),'$categorize_this_query');
		
		}	
	
	";
	echo $html;
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["visited"]='{visited_websites}';
	$array["no-cat"]='{not_categorized}';
	$array["yes-cat"]='{categorized}';
	$array["free-cat"]='{add_websites}';
	$array["params"]='{parameters}';

	

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&day={$_GET["day"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_visitedwebs style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_visitedwebs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";			
}


function visited(){
$page=CurrentPageName();	
$html="
<table>
<td class=legend>{search}:</td>
<td>". Field_text("visited-search",$_COOKIE["SQUID_NOT_CAT_SEARCH"],"font-size:13px;padding:3px",null
,null,null,false,"SQUID_VISITED_SEARCH_CHECK(event)")."</td>
</tr>
</table>

<div id='visited_web_sites' style='height:450px;overflow:auto'></div>

<script>
	function SQUID_VISITED_SEARCH_CHECK(e){
		if(!checkEnter(e)){return;}
		var value=document.getElementById('visited-search').value;
		Set_Cookie('SQUID_NOT_CAT_SEARCH',value, '3600', '/', '', '');
		LoadAjax('visited_web_sites','$page?visited-list=yes&day={$_GET["day"]}');
	}
	LoadAjax('visited_web_sites','$page?visited-list=yes&day={$_GET["day"]}');
	
</script>
";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function parameters(){
	$sock=new sockets();
	$EnableCommunityFilters=$sock->GET_INFO("EnableCommunityFilters");
	if($EnableCommunityFilters==null){$EnableCommunityFilters=1;}
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
	<td class=legend>{alert_in_frontpage_website_categorize}:</td>
	<td>".Field_checkbox("EnableCommunityFilters",1,$EnableCommunityFilters,"EnableCommunityFiltersCheck()")."</td>
	</tr>
	</table> 
	<script>
		var x_EnableCommunityFiltersCheck=function(obj){
     	var tempvalue=obj.responseText;
      	if(tempvalue.length>3){alert(tempvalue);}
     	document.getElementById('ssl-bump-wl-id').innerHTML='';
     	sslBumpList();
     	}	
      


	function EnableCommunityFiltersCheck(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableCommunityFilters').checked){
			XHR.appendData('EnableCommunityFilters',1);}else{
			XHR.appendData('EnableCommunityFilters',0);
			}
		XHR.sendAndLoad('$page', 'GET');		
		}
	
	
</script>
";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function parameters_save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableCommunityFilters",$_GET["EnableCommunityFilters"]);
	
}

function free_catgorized(){
	
	$dans=new dansguardian_rules();
	$cats=$dans->array_blacksites;
	$page=CurrentPageName();
	$tpl=new templates();
	while (list ($num, $ligne) = each ($cats) ){
		$newcat[$num]=$num;
	}
	$newcat[null]="{select}";
	$html="
	<div class=explain>{free_catgorized_explain}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{category}</td>
		<td>". Field_array_Hash($newcat,"free-category-add",null,"style:font-size:14px")."</td>
	</tR>
	</table>
	
	<center>
		<textarea style='width:100%;height:250px;overflow:auto;font-size:13px' id='textToParseCats'></textarea>
	<hr>
		". button("{submit}","FreeCategoryPost()")."
		
	</center>
	
	<script>
	var x_FreeCategoryPost= function (obj) {
		var res=obj.responseText;
		if (res.length>0){
			document.getElementById('textToParseCats').value=res;
		}
	}		
	
	function FreeCategoryPost(){
		var XHR = new XHRConnection();
		var cat=document.getElementById('free-category-add').value;
		if(cat.length==0){return;}
		XHR.appendData('category',cat);
		XHR.appendData('textToParseCats',document.getElementById('textToParseCats').value);
		document.getElementById('textToParseCats').value='Processing....';
		XHR.sendAndLoad('$page', 'POST',x_FreeCategoryPost);	
	}		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function already_Cats($www){
	$array[]="addthis.com";
	$array[]="google.";
	$array[]="w3.org";
	$array[]="icra.org";
	$array[]="facebook.";
	while (list ($num, $wwws) = each ($array)){
		$pattern=str_replace(".", "\.", $wwws);
		if(preg_match("#$pattern#", $www)){return true;}
		
	}
	return false;
}

function free_catgorized_save(){
	
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	
	if(!preg_match_all("#http.*?:\/\/(.+?)[\/\s]+#",$_POST["textToParseCats"]."\n",$re)){echo "No webistes\n";}
	
		while (list ($num, $www) = each ($re[1]) ){
			$www=strtolower($www);
			$www=replace_accents($www);
			if($www=="www"){continue;}
			if($www=="ssl"){continue;}
			if(preg_match("#^www\.(.+?)$#i",$www,$ri)){$www=$ri[1];}
			if(already_Cats($www)){continue;}
			if(strpos($www, '"')>0){$www=substr($www, 0,strpos($www, '"'));}
			if(strpos($www, "'")>0){$www=substr($www, 0,strpos($www, "'"));}
			if(strpos($www, ">")>0){$www=substr($www, 0,strpos($www, ">"));}
			if(strpos($www, "?")>0){$www=substr($www, 0,strpos($www, "?"));}
			if(strpos($www, "\\")>0){$www=substr($www, 0,strpos($www, "\\"));}
			$sites[$www]=$www;
		}
		
	
	$q=new mysql_squid_builder();
	$q->CheckTable_dansguardian();
	
	if(count($sites)==0){echo "NO websites\n";return;}
	$category=$_POST["category"];
	echo "analyze ".count($sites)." websites into $category\n";
	while (list ($num, $www) = each ($sites) ){
		$md5=md5($www.$category);
		$cats=$q->GET_CATEGORIES($www);
		
		
		
		if($cats<>null){echo "$www already added in $cats\n";continue;}		
		echo "Added $www\n";
		$q->QUERY_SQL($sql_add,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
		$category_table="category_".$q->category_transform_name($category);
		$q->QUERY_SQL("INSERT IGNORE INTO $category_table (zmd5,zDate,category,pattern,uuid) VALUES('$md5',NOW(),'$category','$www','$uuid')");
		if(!$q->ok){echo "categorize $www failed $q->mysql_error\n";continue;}
		$q->QUERY_SQL("INSERT IGNORE INTO categorize (zmd5,zDate,category,pattern,uuid) VALUES('$md5',NOW(),'$category','$www','$uuid')");
		if(!$q->ok){echo $q->mysql_error."\n";echo $sql;}
	}		
			
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?export-community-categories=yes");
	
	
}



function not_categorized(){
$page=CurrentPageName();	
$html="
<table>
<td class=legend>{search}:</td>
<td>". Field_text("not-cat-search",$_COOKIE["SQUID_NOT_CAT_SEARCH"],"font-size:13px;padding:3px",null
,null,null,false,"SQUID_NOT_CAT_SEARCH_CHECK(event)")."</td>
</tr>
</table>

<div id='not_categorized_sites' style='height:450px;overflow:auto'></div>
<script>
	function SQUID_NOT_CAT_SEARCH_CHECK(e){
		if(!checkEnter(e)){return;}
		var value=document.getElementById('not-cat-search').value;
		Set_Cookie('SQUID_NOT_CAT_SEARCH',value, '3600', '/', '', '');
		LoadAjax('not_categorized_sites','$page?no-cat-list=yes&day={$_GET["day"]}');
	}
	LoadAjax('not_categorized_sites','$page?no-cat-list=yes&day={$_GET["day"]}');
	
</script>
";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function categorized(){
$page=CurrentPageName();	
$tpl=new templates();
$html="
<table>
<td class=legend>{search}:</td>
<td>". Field_text("cat-search",$_COOKIE["SQUID_NOT_CAT_SEARCH"],"font-size:13px;padding:3px",null
,null,null,false,"SQUID_NOT_CAT_SEARCH_CHECK(event)")."</td>
</tr>
</table>

<div id='categorized_sites' style='height:450px;overflow:auto'></div>
<script>
	function SQUID_NOT_CAT_SEARCH_CHECK(e){
		if(!checkEnter(e)){return;}
		var value=document.getElementById('cat-search').value;
		Set_Cookie('SQUID_NOT_CAT_SEARCH',value, '3600', '/', '', '');
		LoadAjax('categorized_sites','$page?yes-cat-list=yes&day={$_GET["day"]}');
	}
	LoadAjax('categorized_sites','$page?yes-cat-list=yes&day={$_GET["day"]}');
	
</script>
";

echo $tpl->_ENGINE_parse_body($html);		
	
}

function visited_list(){
	
	
	if($_COOKIE["SQUID_NOT_CAT_SEARCH"]<>null){$pattern=" WHERE sitename LIKE '%{$_COOKIE["SQUID_NOT_CAT_SEARCH"]}%' ";$pattern=str_replace("*","%",$pattern);}
	$sql="SELECT sitename as website,visited_sites.*  FROM visited_sites $pattern ORDER BY HitsNumber DESC LIMIT 0,300";
	
	
	$day=trim($_GET["day"]);
	if($day<>null){
		$time=strtotime("$day 00:00:00");
		$table=date("Ymd",$time)."_hour";
		
		if($_COOKIE["SQUID_NOT_CAT_SEARCH"]<>null){$pattern=" HAVING website LIKE '%{$_COOKIE["SQUID_NOT_CAT_SEARCH"]}%' ";$pattern=str_replace("*","%",$pattern);}
		$sql="SELECT SUM(hits) as HitsNumber, sitename as website,category,country FROM $table 
		GROUP BY sitename,category,country $pattern ORDER BY HitsNumber DESC LIMIT 0,150";
	}	

	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{country}</th>
	<th colspan=2>{website}&nbsp;$day</th>
	<th>{hits}</th>
	</tr>
</thead>
<tbody class='tbody'>";

	$q=new mysql_squid_builder();
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["country"]<>null){
			if($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]==null){
				$_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]=GetFlags($ligne["country"]);
			}
		}
		
			if($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]==null){$_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]="flags/a2.png";}
		
			$country_text="<strong style=font-size:13px>{$ligne["country"]}<br>{$ligne["ipaddr"]}</strong>";
			$country=imgtootltip($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]],$country_text);
			$delete=imgtootltip("delete-24.png","{delete}","RoutesServerDelete('{$ligne["ipaddr"]}')");
			
			$html=$html."
			<tr class=$classtr>
			<td width=1% valign='middle' align='center'>$country</td>
			<td width=99%><strong style='font-size:14px'>{$ligne["website"]}<br><i style='font-size:11px;color:#970909'>{$ligne["category"]}</i></td>
			<td width=1% valign='middle' align='center'><strong style='font-size:14px'>{$ligne["hits"]}</strong></td>
			<td width=1%>". imgtootltip("add-database-32.png","{categorize}","Loadjs('squid.categorize.php?www={$ligne["website"]}')")."</td>
			</tr>
			";
			
	
			
				
		
	}	
	$html=$html."</tbody></table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

	

function categorized_list(){
	
	if($_COOKIE["SQUID_NOT_CAT_SEARCH"]<>null){
		$pattern=" AND sitename LIKE '%{$_COOKIE["SQUID_NOT_CAT_SEARCH"]}%' ";
		$pattern=str_replace("*","%",$pattern);
	}	
	
	$sql="SELECT * FROM `squid_events_sites` WHERE `category` != '' $pattern ORDER by hits DESC LIMIT 0 , 100";
	$sql="SELECT sitename ,visited_sites.*  FROM visited_sites WHERE 1 $pattern AND LENGTH(category)>1 ORDER BY HitsNumber DESC LIMIT 0,300";
	$page=CurrentPageName();

	
	$day=trim($_GET["day"]);
	if($day<>null){
		$time=strtotime("$day 00:00:00");
		$table=date("Ymd",$time)."_hour";
		
		if($_COOKIE["SQUID_NOT_CAT_SEARCH"]<>null){$pattern=" HAVING sitename LIKE '%{$_COOKIE["SQUID_NOT_CAT_SEARCH"]}%' ";$pattern=str_replace("*","%",$pattern);}
		if($pattern==null){$pattern="HAVING LENGTH(category)>2";}else{$pattern=$pattern ." AND LENGTH(category)>2";}
		$sql="SELECT SUM(hits) as HitsNumber, sitename,category,country FROM $table 
		GROUP BY sitename,category,country $pattern ORDER BY HitsNumber DESC LIMIT 0,100";
	}	

	$q=new mysql_squid_builder();
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$maxrow=mysql_num_rows($results);	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{country}</th>
	<th>{website}&nbsp;($maxrow {items}) $day</th>
	<th>{hits}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";


	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["country"]<>null){
			if($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]==null){
				$_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]=GetFlags($ligne["country"]);
				}
			}	
			if($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]==null){$_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]="flags/a2.png";}
			$ligne["dbpath"]=str_replace(",",", ",$ligne["dbpath"]);
			$country_text="<strong style=font-size:13px>{$ligne["country"]}<br>{$ligne["ipaddr"]}</strong>";
			$country=imgtootltip($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]],$country_text);
			$delete=imgtootltip("delete-24.png","{delete}","RoutesServerDelete('$ip')");
			$html=$html."
			<tr class=$classtr>
			<td width=1% valign='middle' aling='center'>$country</td>
			<td width=99%><strong style='font-size:14px'>{$ligne["sitename"]}<br><i style='font-size:11px;color:#970909'>{$ligne["category"]}</i></td>
			<td width=1%><strong style='font-size:14px'>{$ligne["HitsNumber"]}</td>
			<td width=1%>". imgtootltip("add-database-32.png","{categorize}","Loadjs('squid.categorize.php?www={$ligne["sitename"]}')")."</td>
			</tr>
			";
			
		
		
	}	
	$html=$html."</tbody></table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function not_categorized_list(){
	$tpl=new templates();
	$categorize_this_query=$tpl->_ENGINE_parse_body("{categorize_this_query}");
	$page=CurrentPageName();
	
	if($_COOKIE["SQUID_NOT_CAT_SEARCH"]<>null){
		$pattern=" AND sitename LIKE '%{$_COOKIE["SQUID_NOT_CAT_SEARCH"]}%' ";
		$pattern=str_replace("*","%",$pattern);
		$categorize_all="<div style='font-size:14px;text-align:right;margin-bottom:5px'>
		<a href='javascript:blur();' style='font-size:14px;text-decoration:underline' OnClick=\"javascript:CategorizeAll('{$_COOKIE["SQUID_NOT_CAT_SEARCH"]}');\">{categorize_this_query}</a></div>
		
		";
		
		
	}	
	
	$sql="SELECT * FROM `visited_sites` WHERE LENGTH(category)=0 $pattern ORDER BY HitsNumber DESC LIMIT 0 , 100";
	
	
	$day=trim($_GET["day"]);
	if($day<>null){
		$time=strtotime("$day 00:00:00");
		$table=date("Ymd",$time)."_hour";
		if($_COOKIE["SQUID_NOT_CAT_SEARCH"]<>null){$pattern=" HAVING sitename LIKE '%{$_COOKIE["SQUID_NOT_CAT_SEARCH"]}%' ";$pattern=str_replace("*","%",$pattern);}
		if($pattern==null){$pattern="HAVING LENGTH(category)=0";}else{$pattern=$pattern ." AND LENGTH(category)=0";}
		$sql="SELECT SUM(hits) as HitsNumber, sitename,category,country FROM $table 
		GROUP BY sitename,category,country $pattern ORDER BY HitsNumber DESC LIMIT 0,100";
	}		
	

	
	
	$html="
$categorize_all	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{country}</th>
	<th>{website}&nbsp;$day</th>
	<th>{hits}</th>
	<th>&nbsp;</th>	
	</tr>
</thead>
<tbody class='tbody'>";

	$q=new mysql_squid_builder();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne["website"]=$ligne["sitename"];
		if($ligne["country"]<>null){
			if($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]==null){
				$_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]=GetFlags($ligne["country"]);
			}
					
			}	
			if($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]==null){$_SESSION["COUNTRIES_FLAGS"][$ligne["country"]]="flags/a2.png";}
		
			$country_text="<strong style=font-size:13px>{$ligne["country"]}<br>{$ligne["ipaddr"]}</strong>";
			$country=imgtootltip($_SESSION["COUNTRIES_FLAGS"][$ligne["country"]],$country_text);
			$delete=imgtootltip("delete-24.png","{delete}","RoutesServerDelete('{$ligne["ipaddr"]}')");
			$html=$html."
			<tr class=$classtr>
			<td width=1% valign='middle' aling='center'>$country</td>
			<td width=99%><strong style='font-size:14px'>{$ligne["website"]}</td>
			<td width=19%><strong style='font-size:14px'>{$ligne["HitsNumber"]}</td>
			<td width=1%>". imgtootltip("add-database-32.png","{categorize}","Loadjs('squid.categorize.php?www={$ligne["website"]}')")."</td>
			</tr>
			";
	
		
	}	
	$html=$html."</tbody>
	</table>
	

	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function CategorizeAll_popup(){
	
	$pattern=" AND sitename LIKE '%{$_GET["CategorizeAll"]}%' ";
	$pattern=str_replace("*","%",$pattern);
	$sql="SELECT COUNT( sitename ) AS tcount
	FROM `visited_sites`
	WHERE LENGTH( `category` )=0
	$pattern";
	
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql_squid_builder();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$websites=$tpl->_ENGINE_parse_body("{websites}");
	if(!$q->ok){echo "<H3>$q->mysql_error</H3>";}
	$count=$ligne["tcount"];
	$dans=new dansguardian_rules();
	
	while (list($num,$val)=each($dans->array_blacksites)){	
		$blcks[$num]=$num;
		
	}
	$blcks[null]="{select}";
	$field=Field_array_Hash($blcks,"CategorizeAll_category",null,"CategorizeAllDef()",null,0, "font-size:13px;padding:3px");
	
	$html="
	<div id='cat-perf-all'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{pattern}:</td>
		<td><strong style='font-size:13px'>{$_GET["CategorizeAll"]}</td>
	</tr>	
	<tr>
		<td class=legend>{websites}:</td>
		<td><strong style='font-size:13px'>$count</td>
	</tr>
	<tr>
		<td class=legend>{category}:</td>
		<td>$field</td>
	</tr>
	<tr><td colspan=2><div id='cat-explain'></div></td>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{categorize}","CategorizeAllPerform();")."</td>
	</tr>
	</table>
	</div>
	<script>
		function CategorizeAllDef(){
			LoadAjax('cat-explain','$page?cat-explain='+escape(document.getElementById('CategorizeAll_category').value));
		}
		var x_CategorizeAllPerform=function(obj){
     	var tempvalue=obj.responseText;
      	if(tempvalue.length>3){alert(tempvalue);}
      	if(document.getElementById('not_categorized_sites')){
     		LoadAjax('not_categorized_sites','$page?no-cat-list=yes');
     		}
     		
     	YahooWin4Hide();
     
     	}	
      


	function CategorizeAllPerform(){
		var CategorizeAll_category=document.getElementById('CategorizeAll_category').value;
		if(CategorizeAll_category.length>0){
			var XHR = new XHRConnection();
			XHR.appendData('CategorizeAll_category',CategorizeAll_category);
			if(confirm('*{$_GET["CategorizeAll"]}*: -> $count $websites -> '+CategorizeAll_category+'?')){
				var XHR = new XHRConnection();
				XHR.appendData('CategorizeAll_category',CategorizeAll_category);
				XHR.appendData('pattern','{$_GET["CategorizeAll"]}');
				document.getElementById('cat-perf-all').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_CategorizeAllPerform);		
			}
			}
	}		
		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);

}

function CategorizeAll_explain(){
	$tpl=new templates();
	$dans=new dansguardian_rules();
	$text=$dans->array_blacksites[$_GET["cat-explain"]];
	echo $tpl->_ENGINE_parse_body("<div class=explain>$text</div>");
	
}

function CategorizeAll_perform(){
	if($_GET["pattern"]==null){return;}
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	if($uuid==null){echo "UUID=NULL; Aborting";return;}	
	$pattern=" AND sitename LIKE '%{$_GET["pattern"]}%' ";
	$pattern=str_replace("*","%",$pattern);
	$sql="SELECT sitename FROM `visited_sites` WHERE  LENGTH( `category` )=0 $pattern";
	$category=$_GET["CategorizeAll_category"];
	if($category==null){return;}

	$q=new mysql_squid_builder();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(!$q->ok){
		echo $q->mysql_error."\n".basename(__FILE__)."\nLine".__LINE__;
		return;
	}
	
	
	$category_table="category_".$q->category_transform_name($category);
	
	if(!$q->TABLE_EXISTS($category_table)){$q->CreateCategoryTable($category);}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$website=$ligne["sitename"];
		if($website==null){return;}
		$www=trim(strtolower($website));
		if(preg_match("#^www\.(.+?)$#i",trim($www),$re)){$www=$re[1];}
		$md5=md5($www.$category);
		$enabled=1;
		if($www==null){echo "Alert: website is null...\n";return;}
		$sql_add="INSERT INTO $category_table (zmd5,zDate,category,pattern,uuid,enabled) VALUES('$md5',NOW(),'$category','$www','$uuid',1)";
		$q->QUERY_SQL($sql_add);
		if(!$q->ok){echo $q->mysql_error."\n".basename(__FILE__)."\nLine".__LINE__."\n";echo $sql;return;}
		$sql="INSERT IGNORE INTO categorize (zmd5,zDate,category,pattern,uuid) VALUES('$md5',NOW(),'$category','$www','$uuid')";
		$q->QUERY_SQL($sql);
		if(!$q->ok){echo $q->mysql_error."\n".basename(__FILE__)."\nLine".__LINE__."\n";echo $sql;return;}	
			
		
	}
	
	$sql="UPDATE `visited_sites` SET `category`='$category' WHERE LENGTH( `category` )=0 $pattern";
	$q->QUERY_SQL($sql);
	if(!$q->ok){echo $q->mysql_error."\n".basename(__FILE__)."\nLine".__LINE__."\n";echo $sql;return;}		
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?export-community-categories=yes");	
	
}

