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

if(isset($_GET["status"])){status();exit;}

if(isset($_GET["dansguardian-status"])){dansguardian_status();exit;}
if(isset($_GET["groups"])){groups();exit;}
if(isset($_GET["groups-search"])){groups_search();exit;}
if(isset($_GET["dansguardian-service-status"])){dansguardian_service_status();exit;}
if(isset($_GET["ufdbguard"])){ufdbguard_service_section();exit;}
if(isset($_GET["js-ufdbguard"])){ufdbguard_service_js();exit;}
tabs();



function tabs(){
	$squid=new squidbee();
	$tpl=new templates();
	$users=new usersMenus();
	$page=CurrentPageName();
	$array["status"]='{status}';
	

	
	
	$array["rules"]='{rules}';
	$array["groups"]='{groups}';
	$array["databases"]='{databases}';
	
	if($users->APP_UFDBGUARD_INSTALLED){
		if($squid->enable_UfdbGuard==1){
			$array["ufdbguard"]='{service_parameters}';
			$array["ufdbguard-events"]='{events}';
		}
		
	}	
	


	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="rules"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"dansguardian2.mainrules.php\" style='font-size:14px'><span>$ligne</span></a></li>\n");
			continue;
			
		}
		
		if($num=="ufdbguard-events"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"ufdbguard.admin.events.php\" style='font-size:14px'><span>$ligne</span></a></li>\n");
			continue;
			
		}		
		
		if($num=="databases"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"dansguardian2.databases.php\" style='font-size:14px'><span>$ligne</span></a></li>\n");
			continue;
			
		}
				
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=$time\" style='font-size:14px'><span>$ligne</span></a></li>\n");
	}
	
	
	
	echo "$menus
	<div id=main_dansguardian_tabs style='width:99%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
			$(document).ready(function(){
				$('#main_dansguardian_tabs').tabs();
			});
		</script>";	

}

function status_left(){
	
}


function groups(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<div class=explain>{dansguardian2_members_groups_explain}</div>
	<center>
	<table style='width:65%' class=form>
	<tbody>
		<tr><td class=legend>{groups}</td>
		<td>". Field_text("groups-dnas-search",null,"font-size:16px;width:220px",null,null,null,false,"GroupsDansSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","GroupsDansSearch()")."</td>
		</tr>
	</tbody>
	</table>
	</center>
	
	<div id='dansguardian2-groups-list' style='width:100%;height:350px;overlow:auto'></div>
	
	<script>
		function GroupsDansSearchCheck(e){
			if(checkEnter(e)){GroupsDansSearch();}
		}
		
		function GroupsDansSearch(){
			var se=escape(document.getElementById('groups-dnas-search').value);
			LoadAjax('dansguardian2-groups-list','$page?groups-search='+se);
		
		}
		
		GroupsDansSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function groups_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();	
	$search=$_GET["groups-search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$group_text=$tpl->_ENGINE_parse_body("{group}");
	$sql="SELECT * FROM webfilter_group WHERE 1 AND ((groupname LIKE '$search') OR (description LIKE '$search')) ORDER BY groupname LIMIT 0,50";
	
	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}
	
	$add=imgtootltip("plus-24.png","{add} {group}","DansGuardianEditGroup(-1)");
	
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th width=99%>{group}</th>
		<th width=1% align='center'>{members}</th>
		<th width=99%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$CountDeMembers=0;
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","DansGuardianEditGroup('{$ligne["ID"]}','{$ligne["groupname"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","SambaVirtalDel('{$ligne["hostname"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		if($ligne["localldap"]==0){
			$gp=new groups($ligne["gpid"]);
			$groupadd_text="(".$gp->groupName.")";
			$CountDeMembers=$CountDeMembers+count($gp->members);
		}
		
		
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$select</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["groupname"]} $groupadd_text<div style='font-size:10px'><i>{$ligne["description"]}</i></td>
			<td style='font-size:14px;font-weight:bold;color:$color' align='center'>$CountDeMembers</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	</center>
	<script>
		function DansGuardianEditGroup(ID,rname){
			YahooWin3('600','dansguardian2.edit.group.php?ID='+ID,'$group_text::'+ID+'::'+rname);
		
		}
	
	
	</script>";	
	echo $tpl->_ENGINE_parse_body($html);
}

function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top'><div id='dansguardian-status'></div></td>
		<td valign='top'><div id='dansguardian-service-status'></div>
	</tr>
	</tbody>
	</table>
	<script>
		LoadAjax('dansguardian-status','$page?dansguardian-status=yes');
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function dansguardian_status(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	$categories=$q->LIST_TABLES_CATEGORIES();
	$sock=new sockets();
	$ufdb=null;$dansgu=null;
	
	while (list ($table, $ligne) = each ($categories) ){
		if(!preg_match("#category_.+?#", $table)){continue;}
		$tbl[]=$table;
		$c=$c+$q->COUNT_ROWS($table);
	}
	
	if($users->APP_UFDBGUARD_INSTALLED){
		$APP_UFDBGUARD_INSTALLED="{installed}";
		$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
		if($EnableUfdbGuard==1){$EnableUfdbGuard="{enabled}";}else{$EnableUfdbGuard="{disabled}";}
			$ufdb="<tr>
			<td class=legend>{APP_UFDBGUARD}:</td>
			<td><div style='font-size:14px'>". texthref($EnableUfdbGuard,"Loadjs('squid.popups.php?script=plugins')")."</td>
			</tr>";			
		
	}
	
	if($users->DANSGUARDIAN_INSTALLED){
		$DANSGUARDIAN_INSTALLED="{installed}";
		$DansGuardianEnabled=$sock->GET_INFO("DansGuardianEnabled");
		if($DansGuardianEnabled==1){$DansGuardianEnabled="{enabled}";}else{$DansGuardianEnabled="{disabled}";}
			$dansgu="<tr>
			<td class=legend>{APP_UFDBGUARD}:</td>
			<td><div style='font-size:14px'>". texthref($DansGuardianEnabled,"Loadjs('squid.popups.php?script=plugins')")."</td>
			</tr>";			
		
	}	
	
	$html="
	<table style='width:100%' class=form>
	<tbody>
	$ufdb
	$dansgu
	<tr>
	<td class=legend>{categories}:</td>
	<td><div style='font-size:14px'>". count($tbl)."</div></td>
	</tr>
	<tr>
	<td class=legend>{websites_categorized}:</td>
	<td><div style='font-size:14px'>".numberFormat($c,0,""," ")."</td>
	</tr>	
	</tbody>
	</table>
	<script>
		LoadAjax('dansguardian-service-status','$page?dansguardian-service-status=yes');
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function ufdbguard_service_js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body("{web_proxy}&nbsp;&nbsp;&raquo;&raquo;&nbsp;{APP_UFDBGUARD}&nbsp;&nbsp;&raquo;&raquo;&nbsp;{parameters}");
echo "YahooWin('750','$page?ufdbguard=yes&width=100%','$title')";
}

function ufdbguard_service_section(){
		
	$width="650px";
	if(isset($_GET["width"])){$width="{$_GET["width"]}";}
	$template=Paragraphe("banned-template-64.png","{template_label}",'{template_explain}',"javascript:s_PopUp('dansguardian.template.php',800,800)"); 
	$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");
	$ufdbguard_settings=Paragraphe("filter-sieve-64.png","{APP_UFDBGUARD}","{APP_UFDBGUARD_PARAMETERS}","javascript:Loadjs('ufdbguard.php')");
		
	$recompile_all_database=Paragraphe("database-spider-compile2-64.png",
	"{recompile_all_db}","{recompile_all_db_ww_text}","javascript:Loadjs('ufdbguard.databases.php?scripts=recompile')");
	
	$compile_schedule=Paragraphe("clock-gold-64.png","{compilation_schedule}","{compilation_schedule_text}","javascript:Loadjs('ufdbguard.databases.php?scripts=compile-schedule')");	
	
	$ufdbguard_conf=Paragraphe("script-64.png","ufdbguard.conf","{ufdbguard_conf_read_text}","javascript:Loadjs('ufdbguard.databases.php?scripts=config-file')");
	
	$tr[]=$ufdbguard_settings;
	$tr[]=$ufdbguard_conf;
	$tr[]=$squidguardweb;
	$tr[]=$template;
	$tr[]=$recompile_all_database;
	$tr[]=$compile_schedule;
	
	
	$html="<center><div style='width:$width'>".CompileTr3($tr)."</div></center>";
	$tpl=new templates();
	$html= $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	echo $html;			
	
}


function dansguardian_service_status(){
	$page=CurrentPageName();
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	
	$users=new usersMenus();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?squid-ini-status=yes')));
	
	

	$squid_status=DAEMON_STATUS_ROUND("SQUID",$ini,null,1);
	$dansguardian_status=DAEMON_STATUS_ROUND("DANSGUARDIAN",$ini,null,1);
	$APP_SQUIDGUARD_HTTP=DAEMON_STATUS_ROUND("APP_SQUIDGUARD_HTTP",$ini,null,1);
	$APP_UFDBGUARD=DAEMON_STATUS_ROUND("APP_UFDBGUARD",$ini,null,1);	
	
	$tr[]=$squid_status;
	$tr[]=$dansguardian_status;
	$tr[]=$APP_SQUIDGUARD_HTTP;
	$tr[]=$APP_UFDBGUARD;
	
	
	$html="<center><div style='width:550px'>".CompileTr2($tr)."</div></center>";
	$tpl=new templates();
	$html= $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	echo $html;
	
}