<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.squid.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsDansGuardianAdministrator){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}


if(isset($_GET["ufdbguard-list-search"])){search();exit;}
page();

function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$q=new mysql();
	$sql="SELECT category FROM ufdbguard_admin_events GROUP BY category ORDER BY category";
	$results=$q->QUERY_SQL($sql,"artica_events");	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$cat[$ligne["category"]]=$ligne["category"];
	}
	$cat[null]="{select}";
	
	$html="
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{service}:</td>
				<td>". Field_text("ufdbguard-search",null,"font-size:16px",null,null,null,false,"ufdbguardSearchEventsCheck(event)")."</td>
				<td class=legend>{category}:</td>
				<td>". Field_array_Hash($cat, "ufdbguard-event-category",null,"style:font-size:14px")."</td>
				<td>". button("{search}","ufdbguardSearchEvents()")."</td>
			</tr>
		</tbody>
	</table>
	<div id='ufdbguard-list-table' style='width:100%;height:350px;overflow:auto;background-color:white'></div>
	
	<script>
		function ufdbguardSearchEventsCheck(e){
			if(checkEnter(e)){ufdbguardSearchEvents();}
		}
	
		function ufdbguardSearchEvents(){
			var se=escape(document.getElementById('ufdbguard-search').value);
			var cat=escape(document.getElementById('ufdbguard-event-category').value);
			LoadAjax('ufdbguard-list-table','$page?ufdbguard-list-search=yes&search='+se+'&category='+cat);
		}
	
	ufdbguardSearchEvents();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}
function search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$emailing_campain_linker_delete_confirm=$tpl->javascript_parse_text("{emailing_campain_linker_delete_confirm}");
	
	$style="style='font-size:14px;'";
	if($_GET["category"]<>null){
		$catsql=" AND `category`='{$_GET["category"]}'";
	}
	
	$sql="SELECT * FROM ufdbguard_admin_events WHERE `description` LIKE '$search' $catsql ORDER BY zDate DESC LIMIT 0,50";
	
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
	
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_events");
		$cs=0;
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne["description"]=htmlentities($ligne["description"]);
		$ligne["description"]=nl2br($ligne["description"]);
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>{$ligne["zDate"]}</td>
			<td width=99% $style nowrap>{$ligne["description"]}<div style='font-size:11px'>{$ligne["filename"]} - {$ligne["function"]}() {line}:{$ligne["line"]}</div></td>
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