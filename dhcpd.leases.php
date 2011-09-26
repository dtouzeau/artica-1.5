<?php

session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.dhcpd.inc');
include_once('ressources/class.computers.inc');
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
	if(isset($_GET["action-rescan"])){action_rescan_js();exit;}
	if(isset($_POST["DCHP_LEASE_RESCAN"])){DCHP_LEASE_RESCAN();exit;}
page();


function action_rescan_js(){
	$tpl=new templates();
	$page=CurrentPageName();	
	echo "
	var x_DCHP_LEASE_RESCAN= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		if(document.getElementById('main_config_dhcpd')){RefreshTab('main_config_dhcpd');}
		
	 }	
	
	function DCHP_LEASE_RESCAN(){
			var XHR = new XHRConnection();
			XHR.appendData('DCHP_LEASE_RESCAN','yes');
			XHR.sendAndLoad('$page', 'POST',x_DCHP_LEASE_RESCAN);
		}
		
	DCHP_LEASE_RESCAN()";
	
}


function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	$addtitl=$tpl->_ENGINE_parse_body("{add}&raquo;&raquo;{network_legend}");
	$sock=new sockets();
	$scan=Paragraphe("tables-64-running.png", "{rescan}", "{rescan_dhcpleases_text}","javascript:Loadjs('$page?action-rescan=yes')");
	
	$array=unserialize(base64_decode($sock->getFrameWork("network.php?dhcpd-leases-script")));
	if(is_array($array)){
		$scan="
		<table style='width:100%'>
		<tbody>
		<tr>
			<td width=1%><img src='img/64-run.png'></td>
			<td style='font-size:13px'>{importation}:{running} PID:{$array[0]} {since}:{$array[1]}Mn</td>
		</tr>
		</tbody>
		</table>
		";
	}
	
	
	$html="
	<table style='width:100%'>
	<tbody>
	<tr>
		<td style='width:100%' valign='top'><div class=explain>{dhcpd_leases_explain}</div></td>
		<td style='width:1%' valign='top'>$scan
		<div style='text-align:right'>". imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_dhcpd')")."</div>
		</td>
	</tr>
	</tbody>
	</table>
		
	
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
	
	<div id='dhcpd-leases-network' style='width:100%;height:490px;overflow:auto'></div>
	
	
	
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
	
$cmp=new computers();

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["hostname"]=trim($ligne["hostname"]);
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["mac"]==null){continue;}
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
		$js=null;
		$uid=$cmp->ComputerIDFromMAC($ligne["mac"]);
		if($uid<>null){
			$img="30-computer.png";
			$js=MEMBER_JS($uid,1,1);
			$tooltip=$tooltip."<br>{view}";
		}else{
			$img="30-computer-grey.png";
		}
		
		if($ligne["hostname"]==null){$ligne["hostname"]="&nbsp;";}
		if($ligne["ipaddr"]==null){$ligne["ipaddr"]="&nbsp;";}
		if($ligne["mac"]==null){$ligne["mac"]="&nbsp;";}
		
		$html=$html."
		<tr class=$classtr>
		<td width=1% style='font-size:14px' align='center'>". imgtootltip("30-computer.png","$tooltip",$js)."</td>
		<td style='font-size:13px'>$href{$ligne["hostname"]}</a></td>
		<td style='font-size:13px' nowrap>$href{$ligne["ipaddr"]}</a></td>
		<td style='font-size:13px'>$href{$ligne["mac"]}</a></td>
		<td style='font-size:13px' nowrap>$href{$ligne["ends"]}</td>
	</tr>
		";
		
	}
	
$html=$html."</table>


";

echo $tpl->_ENGINE_parse_body($html);
	
}
function DCHP_LEASE_RESCAN(){
	$sock=new sockets();
	$sock->getFrameWork("network.php?dhcpd-leases=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{operation_launched_in_background}");
	
	
}