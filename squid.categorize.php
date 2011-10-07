<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.rtmm.tools.inc');

	
	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["categorizer"])){popup_categories();exit;}
	if(isset($_GET["top10"])){popup_top10();exit;}
	if(isset($_GET["top10-list"])){popup_top10_list();exit;}
	if(isset($_GET["top10-users"])){popup_top10_users();exit;}
	
	
	if(isset($_GET["category"])){save_category();exit;}
	if(isset($_GET["categories-of"])){exit();}
	js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$start="CategorizeLoad()";
	
	if(isset($_GET["load-js"])){
		$_GET["www"]=$_GET["load-js"];
		$start="CategorizeLoadAjax()";
		
	}
	
	if(trim($_GET["www"])==null){
			$error_no_website_selected=$tpl->javascript_parse_text("{error_no_website_selected}");
			echo "alert('$error_no_website_selected');";
			return;
	}	
	
	$html="
	function CategorizeLoad(){
		YahooWinBrowse(650,'$page?popup=yes&www={$_GET["www"]}&bykav={$_GET["bykav"]}','{$_GET["www"]}');
	
	}
	
	function CategorizeLoadAjax(){
		LoadAjax('popup_other_squid_category_webpage','$page?popup=yes&www={$_GET["www"]}&bykav={$_GET["bykav"]}');
	}
	
	
	var x_DansCommunityCategory= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		if(document.getElementById('main_config_visitedwebs')){RefreshTab('main_config_visitedwebs');}
	}		
	
	function DansCommunityCategory(md,category,website){
		var XHR = new XHRConnection();
		XHR.appendData('category',category);
		XHR.appendData('website',website);
		if(document.getElementById(md).checked){
		XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		XHR.sendAndLoad('$page', 'GET',x_DansCommunityCategory);	
	}
	
	
	$start;
	
	";
	
	if(isset($_GET["load-js"])){echo "
	<div id='popup_other_squid_category_webpage'></div>
	<script>$html</script>";return;}
	
	echo $html;
	
}



function save_category(){
	$www=trim(strtolower(base64_decode($_GET["website"])));
	if(preg_match("#^www\.(.+?)$#i",$www,$re)){$www=$re[1];}
	$category=$_GET["category"];
	$md5=md5($www.$category);
	$sock=new sockets();
	$q=new mysql_squid_builder();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	$enabled=$_GET["enabled"];
	
	$q->CreateCategoryTable($category);
	$category_table=$q->category_transform_name($category);
	$sql="SELECT zmd5 FROM category_$category_table WHERE pattern='$www'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql));
	
	$sql_add="INSERT IGNORE INTO categorize (zmd5,zDate,category,pattern,uuid) VALUES('$md5',NOW(),'$category','$www','$uuid')";
	$sql_add2="INSERT IGNORE INTO category_$category_table (zmd5,zDate,category,pattern,uuid) VALUES('$md5',NOW(),'$category','$www','$uuid')";
	$sql_edit="UPDATE category_$category_table SET enabled='$enabled' WHERE zmd5='{$ligne["zmd5"]}'";
	
	writelogs("$www/$category = {$ligne["zmd5"]}",__FUNCTION__,__FILE__,__LINE__);
	
	if($ligne["zmd5"]==null){
		$q->QUERY_SQL($sql_add2);
		$q->QUERY_SQL($sql_add);
	}
	else{
		writelogs("$sql_edit",__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql_edit);
	
	}
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	$q->QUERY_SQL("UPDATE visited_sites SET category='' WHERE sitename='$www'");
	if(!$q->ok){echo $q->mysql_error."\n";echo $sql."\n";}

	$cats=addslashes($q->GET_CATEGORIES($www,true));
	
	$q->QUERY_SQL("UPDATE visited_sites SET category='$cats' WHERE sitename='$www'");
	if(!$q->ok){echo $q->mysql_error."\n";echo $sql."\n";}
	
	
	$newmd5=md5("$cats$www");
	$q->QUERY_SQL("INSERT IGNORE INTO categorize_changes (zmd5,sitename,category) VALUES('$newmd5','$www','$cats')");
	if(!$q->ok){echo $q->mysql_error."\n";echo $sql."\n";}
	if($enabled==1){
		$q->QUERY_SQL("DELETE FROM categorize_delete WHERE zmd5='$md5'");
	}else{
		$q->QUERY_SQL("INSERT IGNORE INTO categorize_delete(zmd5,sitename,category) VALUES('$md5','$www','$category')");
	}
	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?export-community-categories=yes");
	$sock->getFrameWork("squid.php?re-categorize=yes");
	
}




function popup_top10(){
	$page=CurrentPageName();
	$html="<div id='popup_top10_div' style='height:490px;overflow:auto'></div>
	<script>
		LoadAjax('popup_top10_div','$page?top10-list=yes&www={$_GET["www"]}&bykav={$_GET["bykav"]}');
	</script>
	
	";
	
	echo $html;
	
}

function popup_top10_users(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	
	//if(preg_match("#^www\.(.+?)$#",$_GET["www"],$re)){$_GET["www"]=$re[1];}
	
	
	$sql="SELECT SUM(size) as tsize, client FROM squid_events_clients_sites WHERE websites='{$_GET["www"]}' GROUP BY client ORDER BY tsize DESC LIMIT 0,10";
	
	if($_GET["bykav"]=="yes"){
		$table="Kav4Proxy_".date('Ym');
		$sql="SELECT SUM(size) as tsize, client FROM `$table` WHERE sitename='{$_GET["www"]}' 
		GROUP BY client ORDER BY tsize DESC LIMIT 0,10";
	}
	
		$html="
		<H2>{$_GET["www"]}</H2>
		
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{size}</th>
			<th>{member}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
		
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$size=$ligne["tsize"]/1024;
		$size=FormatBytes($size);
		
		$js="Loadjs('squid.stats.client.php?client-js=yes&time=0&client={$ligne["client"]}')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["client"]}</a></td>
			</tr>";
	
		}		
	
	$html=$html."</table>";

	echo $tpl->_ENGINE_parse_body($html);		
	
}


function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["categorizer"]='{categories}';
	//$array["top10"]='{top10_hits}';
	//$array["top10-users"]='{top10_users}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&www={$_GET["www"]}&bykav={$_GET["bykav"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_categorizer style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_categorizer').tabs();
			
			
			});
		</script>";		
	
}

function popup_categories(){
	$tpl=new templates();
	
	
	if(trim($_GET["www"])==null){
			$error_no_website_selected=$tpl->javascript_parse_text("{error_no_website_selected}");
		echo "
		<script>
			alert('$error_no_website_selected');
			YahooWinBrowseHide();
		</script>
		";
		
		return;
	}

	$www=trim(strtolower($_GET["www"]));
	if(preg_match("#www\.(.+?)$#i",$www,$re)){$www=$re[1];}
	$q=new mysql_squid_builder();
	$cats=explode(",", $q->GET_CATEGORIES($www));
	if(is_array($cats)){while (list ($num, $ligne) = each ($cats) ){$ligne=trim($ligne);if($ligne==null){continue;}$hash_community[$ligne]=true;}}
	
	
	$www_encoded=base64_encode($_GET["www"]);
	$count=count($hash);
	$count_community=count($hash_community);
	$html="
	<div style='font-size:13px'>{dansguardian_categorize_explain}</div>
	<hr>
	<div style='font-size:13px;color:red'>$count_community {categoryies_community}</div>
	<hr>
	<div style='height:490px;overflow:auto;margin:9px'>
	<table style='width:100%'>
	";
	
	$dans=new dansguardian_rules();
	while (list ($num, $val) = each ($dans->array_blacksites) ){
		$md=md5($num);
		$field_enabled=0;
		if($hash_community[$num]){$field_enabled=1;}
		$field=Field_checkbox("$md",1,$field_enabled,"DansCommunityCategory('$md','$num','$www_encoded')");
		if($dans->array_pics[$num]<>null){$pic="<img src='img/{$dans->array_pics[$num]}'>";}else{$pic="&nbsp;";}
		$color="black";
		if($hash[$num]){
			$field="<img src='img/check2.gif'>";
			$color="red";
		}
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%>$pic</td>
			<td><strong style='font-size:11px;color:$color'>$val</td>
			<td>$field</td>
			<td><span style='color:$color'>$num</span></td>
			
		</tr> 
		
		";
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_top10_list(){
	
	
	
	$sql="SELECT count( uri ) AS tcount, uri, CLIENT
	FROM `dansguardian_events` WHERE `sitename` = '{$_GET["www"]}' GROUP BY uri, CLIENT ORDER BY tcount DESC LIMIT 0 , 10";
	
	if($_GET["bykav"]=="yes"){
		$table="Kav4Proxy_".date('Ym');
		$sql="SELECT count( uri ) AS tcount, uri, client as CLIENT
		FROM `$table` WHERE `sitename` = '{$_GET["www"]}' GROUP BY uri, client ORDER BY tcount DESC LIMIT 0 , 10";
	}
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{hits}</th>
	<th>{urls}</th>
	<th>{member}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	$pointer="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
			$html=$html."
			<tr class=$classtr>
			<td width=1% valign='middle' aling='center'><img src='img/fw_bold.gif'></td>
			<td width=1% valign='middle' aling='center'><strong style='font-size:14px'>{$ligne["tcount"]}</td>
			<td width=99%><strong style='font-size:13px;text-decoration:underline' $pointer OnClick=\"javascript:s_PopUpFull('{$ligne["uri"]}',800,800)\" >{$ligne["uri"]}</td>
			<td width=1% valign='middle' aling='center'><strong style='font-size:14px'>{$ligne["CLIENT"]}</td>
			</tr>
			";
			
		
	}	
	$html=$html."</tbody></table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


/*SELECT count( uri ) AS tcount, uri, CLIENT
FROM `dansguardian_events`
WHERE `sitename` = 'photos-f.ak.fbcdn.net'
GROUP BY uri, CLIENT
ORDER BY tcount DESC
LIMIT 0 , 10
*/

?>