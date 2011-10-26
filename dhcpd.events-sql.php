<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.computers.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){		
	$tpl=new templates();
	echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();exit();
	}
	
	
	if(isset($_GET["search"])){search();exit;}
	page();
	
function page(){
	
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<center>
	<table style='width:55%' class=form>
	<tbody>
		<tr><td class=legend>{events}:</td>
		<td>". Field_text("events-dhcpd-search",null,"font-size:16px;width:220px",null,null,null,false,"EventsDHCPDSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","EventsDHCPDSearch()")."</td>
		</tr>
	</tbody>
	</table>
	</center>
	
	<div id='events-dhcpd-list' style='width:100%;height:350px;overlow:auto'></div>
	
	<script>
		function EventsDHCPDSearchCheck(e){
			if(checkEnter(e)){EventsDHCPDSearch();}
		}
		
		function EventsDHCPDSearch(){
			var se=escape(document.getElementById('events-dhcpd-search').value);
			LoadAjax('events-dhcpd-list','$page?search='+se);
		
		}
		
		EventsDHCPDSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function search(){

	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);	
	if(CACHE_SESSION_GET(__FUNCTION__.$search, __FILE__,15)){return;}	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$q=new mysql();	
	
	$computers=new computers();

	$sql="SELECT * FROM dhcpd_logs WHERE description LIKE '$search' ORDER BY zDate DESC LIMIT 0,100";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$color="black";
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$uid=null;
		$mac=null;
		if(preg_match("#to\s+([0-9a-z:]+)\s+via#",$ligne["description"],$re)){$mac=$re[1];}
		if(preg_match("#from\s+([0-9a-z:]+)\s+via#",$ligne["description"],$re)){$mac=$re[1];}
		
		if($mac<>null){
		
			$uid=$computers->ComputerIDFromMAC($mac);
			if($uid<>null){
				
				$uri="<a href=\"javascript:blur();\" OnClick=\"javascript:YahooUser(870,'domains.edit.user.php?userid=$uid&ajaxmode=yes&dn=','$uid');\" style='font-size:14px;font-weight:bold;color:$color;text-decoration:underline'>$mac</a>&nbsp;<span style='font-size:11px'>($uid)</span>";
				$ligne["description"]=str_replace($mac,$uri,$ligne["description"]);
			}
		}
		
	
		$html=$html."
		<tr class=$classtr>
			
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% nowrap>{$ligne["zDate"]}</div></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=99% >{$ligne["description"]}</div></td>
		</tr>
		";
	}
	
	
	$header="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=99% colspan=2>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	

	
	$html=$header.$html."</table>
	</center>
	
	<script>

	</script>
	";
	CACHE_SESSION_SET(__FUNCTION__.$search, __FILE__,$tpl->_ENGINE_parse_body($html));
}