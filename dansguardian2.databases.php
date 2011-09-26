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

tabs();
function tabs(){
	$tpl=new templates();
	$page=CurrentPageName();
	$array["categories"]='{databases}';
	$array["events"]='{update_events}';
	
	


	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		
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
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();	
	$dans=new dansguardian_rules();
	
	$search=$_GET["category-search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);	
	
	
	$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'category_$search'";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo  " <H2>Fatal Error: $q->mysql_error</H2>";}
		
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th width=99%>{category}</th>
		<th width=1%>{size}</th>
		<th width=1%>{items}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
		
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
		$color="black";
		
		$items=$q->COUNT_ROWS($ligne["c"]);
		
		
		if(!isset($dans->array_blacksites[$categoryname])){
			if(isset($dans->array_blacksites[str_replace("_","-",$categoryname)])){$categoryname=str_replace("_","-",$categoryname);}
			if(isset($dans->array_blacksites[str_replace("_","/",$categoryname)])){$categoryname=str_replace("_","/",$categoryname);}
		}
		if($dans->array_pics[$categoryname]<>null){$pic="<img src='img/{$dans->array_pics[$categoryname]}'>";}else{$pic="&nbsp;";}
	
		if($sizedb==0){$pic="<img src='img/warning-panneau-32.png'>";}
		$sizedb=FormatBytes($sizedb/1024);

		
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$pic</td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=99%>
			$categoryname<div style='font-size:11px;width:100%;font-weight:normal'>{$dans->array_blacksites[$categoryname]}</div></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% nowrap align='right'>$sizedb</td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% nowrap align='right'>".numberFormat($items,0,""," ")."</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	

	
	$html=$html."</table>
	</center>";
	echo $tpl->_ENGINE_parse_body($html);
}



