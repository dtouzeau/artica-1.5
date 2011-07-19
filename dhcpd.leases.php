<?php

session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.dhcpd.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){		
	$tpl=new templates();
	echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();exit();
	}
	
	if(isset($_GET["list-nets"])){list_nets();exit;}
	if(isset($_GET["shared-edit"])){shared_edit();exit;}
	if(isset($_POST["domain-name"])){shared_post();exit;}
	if(isset($_POST["DelDHCPShared"])){shared_del();exit;}
	if(isset($_POST["SharedNetsApply"])){shared_apply();exit;}
page();



function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	$addtitl=$tpl->_ENGINE_parse_body("{add}&raquo;&raquo;{network_legend}");
	
	$html="
	<div class=explain>{dhcpd_leases_explain}</div>
	
	<center>
	<table style='width:80%' class=form>
	<tr>
		<td class=legend>{leases}:</td>
		<td>". Field_text("leases-s",null,'font-size:14px',null,null,null,false,"LeasesCheck(event)")."</td>
		<td width=1%>". button("{search}", "LeasesSearch()")."</td>
	</tr>
	</table>
	</center>
	<hr>
	
	<div id='dhcpd-leases-network' style='width:100%;height:290px;overflow:auto'></div>
	
	
	
	<script>
	function LeasesCheck(e){
		if(checkEnter(e)){LeasesSearch();}
	}
	
	function LeasesSearch(){
			var se=escape(document.getElementById('leases-s').value);
			LoadAjax('dhcpd-leases-network','$page?list-nets=yes&search='+se);
		}

		
	LeasesSearch();
	</script>
		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function list_nets(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$search=$_GET["search"];
	$q=new mysql();
	if($search<>null){
		$search="*$search*";
		$search=str_replace("**", "*", $search);
		$search=str_replace("*", "%", $search);
		$search_sql=" WHERE (mac LIKE '$search') OR (ipaddr LIKE '$search') OR (hostname LIKE '$search')";
	}
	
	$sql="SELECT * FROM dhcpd_leases $search_sql ORDER BY `dhcpd_leases`.`cltt` DESC LIMIT 0,100";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<th width=1%>&nbsp;</th>
	<th>{hostname}</th>
	<th>{ipaddr}</th>
	<th>{ComputerMacAddress}</th>
	<th>{end}</th>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	 
		$tooltip="
		<table class=form>
		<tr>
			<td class=legend>start:</td>
			<td><strong style=font-size:13px>{$ligne["starts"]}</td>
		</tr>
		<tr>
			<td class=legend>cltt:</td>
			<td><strong style=font-size:13px>{$ligne["cltt"]}</td>
		</tr>
		<tr>
			<td class=legend>tstp:</td>
			<td><strong style=font-size:13px>{$ligne["tstp"]}</td>
		</tr>
		</table>
		";
		
		
		
		$html=$html."
		<tr class=$classtr>
		<td width=1% style='font-size:14px' align='center'>". imgtootltip("30-computer.png","$tooltip")."</td>
		<td style='font-size:13px' nowrap>$href{$ligne["hostname"]}</a></td>
		<td style='font-size:13px' nowrap>$href{$ligne["ipaddr"]}</a></td>
		<td style='font-size:13px'>$href{$ligne["mac"]}</a></td>
		<td style='font-size:13px'>$href{$ligne["ends"]}</td>
	</tr>
		";
		
	}
	
$html=$html."</table>


";

echo $tpl->_ENGINE_parse_body($html);
	
}
