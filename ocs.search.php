<?php
$GLOBALS["ICON_FAMILY"]="COMPUTERS";
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');

if(posix_getuid()<>0){
	$users=new usersMenus();
	if((!$users->AsSambaAdministrator) OR (!$users->AsSystemAdministrator)){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}

if(isset($_GET["SearchComputers"])){SearchComputers();exit;}
if(isset($_GET["compt-status"])){comp_ping();exit;}

page();


function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<input type='hidden' id='FilterByDate' value=''>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>{computers}:</td>
		<td>". Field_text("SearchComputers",null,"font-size:16px",null,null,null,null,"SearchComputersCheck(event)")."</td>
	</tr>
	</table>
	
	<div id='SearchComputers-list' style='width:100%;height:525px;overflow:auto'></div>
	
	
	<script>
		function SearchComputersCheck(e){
			if(checkEnter(e)){SearchComputers();}
		}
		
		function SearchComputers(){
			var u_f_FilterByDate='';
			var f_FilterByDate=document.getElementById('FilterByDate').value;
			var se=escape(document.getElementById('SearchComputers').value);
			if(f_FilterByDate.length>2){u_f_FilterByDate='&orderBydate='+f_FilterByDate;}
			LoadAjax('SearchComputers-list','$page?SearchComputers='+se+u_f_FilterByDate+'&mode={$_GET["mode"]}&value={$_GET["value"]}&callback={$_GET["callback"]}');
		}
		
		function OcsFilterBYDate(){
			var f=document.getElementById('FilterByDate').value;
			if(f.length<2){f='DESC';}
			if(f=='ASC'){f='DESC';}else{f='ASC';}
			document.getElementById('FilterByDate').value=f;
			SearchComputers();
		}
		
		
		SearchComputers();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function SearchComputers(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();	
	$search="*".$_GET["SearchComputers"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$order="ORDER BY hardware.LASTDATE DESC";
	if(trim($_GET["orderBydate"])<>null){$order="ORDER BY hardware.LASTDATE {$_GET["orderBydate"]} ";}
	$add_computer_js="YahooUser(780,'domains.edit.user.php?userid=newcomputer$&ajaxmode=yes','New computer');";
	$add=imgtootltip("plus-24.png","{add}",$add_computer_js);
	$sql="SELECT networks.*,hardware.* FROM networks,hardware WHERE
	networks.HARDWARE_ID=hardware.ID
	AND ( (hardware.NAME LIKE '$search') OR (networks.MACADDR LIKE '$search') OR (networks.IPADDRESS LIKE '$search') OR (hardware.OSNAME LIKE '$search'))
	$order LIMIT 0,30
	";
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th><a href=\"javascript:blur();\" OnClick=\"OcsFilterBYDate()\" style='font-weight:bold;text-decoration:underline'>{date}</a></th>
		<th>{hostname}</th>
		<th>{ipaddr}</th>
		<th>MAC</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$results=$q->QUERY_SQL($sql,"ocsweb");
		$computer=new computers();
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["MACADDR"]=="unknown"){continue;}
		$color="black";	
		$uid=null;
		$OSNAME=null;
		if($ligne["OSNAME"]=="Unknown"){$ligne["OSNAME"]=null;}
		//$delete=imgtootltip("delete-32.png","{delete}","CgroupProcessDel('{$ligne["process_name"]}')");
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$md=md5($ligne["MACADDR"]);
		$uid=$computer->ComputerIDFromMAC($ligne["MACADDR"]);
		$view="&nbsp;";
		$jsfiche=MEMBER_JS($uid,1,1);
		$uri=$ligne["NAME"];
		if($uid<>null){$view=imgtootltip("computer-32.png","{view}",$jsfiche);}
		$js[]="LoadAjaxTiny('cmp-$md','$page?compt-status={$ligne["IPADDRESS"]}');";
		if($ligne["OSNAME"]<>null){$OSNAME="<div style='font-size:9px'><i>{$ligne["OSNAME"]}</i></div>";}
		if($_GET["callback"]<>null){
			$view=imgtootltip("arrow-down-32.png","{select}","{$_GET["callback"]}('$uid')");
			$uri=texttooltip($ligne["NAME"],"{view}",$jsfiche,null,0,"font-size:12px;text-decoration:underline");
		}
		
		
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:normal;color:$color' width=1% nowrap>$view</td>
			<td style='font-size:12px;font-weight:normal;color:$color' width=1% nowrap>$link{$ligne["LASTDATE"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=99%>$link$uri</a>$OSNAME</td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1%>$link{$ligne["IPADDRESS"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1%>$link{$ligne["MACADDR"]}</a></td>
			<td width=1%><div id='cmp-$md'><img src='img/unknown24.png'></div></td>
		</tr>
		";
	}	

	$html=$html."</tbody></table>
	
	<script>
	". @implode("\n", $js)."
	
	
	
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function comp_ping(){
	$sock=new sockets();
	$R=$sock->getFrameWork("network.php?ping={$_GET["compt-status"]}");
	if($R=="TRUE"){echo "<img src='img/ok24.png'>";return;}
	echo "<img src='img/unknown24.png'>";
}


