<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.bind9.inc');	

	$user=new usersMenus();
	if(!$user->AsDnsAdministrator){header('location:users.index.php');die();}
	
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["forwarder_add"])){forwarder_add();exit;}
	if(isset($_GET["forwarder_delete"])){forwarder_delete();exit;}
	if(isset($_GET["zone_save"])){SaveZoneConfig();exit;}
	if(isset($_GET["zone-hosts-list"])){echo zone_hosts_lits($_GET["zone-hosts-list"]);exit;}
	if(isset($_GET["search-hosts"])){echo zone_hosts_lits($_GET["zone"]);exit;}
	if(isset($_GET["show-zones"])){echo main_zones_list();exit;}
	if(isset($_GET["AddNewDnsZone"])){AddNewDnsZone();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["zone_delete"])){zone_delete();exit;}
	if(isset($_GET["compile-bind"])){compilebind();exit;}
	
	
	main_page();
	
	
function main_page(){
	$page=CurrentPageName();

	if($_GET["hostname"]==null){
		$user=new usersMenus();
		
		$_GET["hostname"]=$user->hostname;}
	
	$html=
	"
	
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	var status='status';
	
	if(document.getElementById('statusid')){
		status=document.getElementById('statusid').value;
	}
	LoadAjax('services_status','$page?status='+status+'&hostname={$_GET["hostname"]}');
	}
</script>		
	
	
	
	
	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
		<img src='img/bg_bind9.png'></td>
	<td valing='top'>
		<div id='services_status'></div>
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			<div id='squid_main_config'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('squid_main_config','$page?main=yes')</script>
	<script>demarre();</script>
	<script>ChargeLogs();</script>
	";
	
	
	$cfg["JS"][]='js/bind.js';
	
	
	
	$tpl=new template_users('{APP_BIND9} {statistics}',$html,0,0,0,0,$cfg);
	
	echo $tpl->web_page;
	
	
	
}


function main_switch(){
	if(!isset($_GET["tab"])){$_GET["tab"]="";};
	
	if($_GET["hostname"]==null){
		$users=new usersMenus();
		$_GET["hostname"]=$users->hostname;
		}	

	switch ($_GET["tab"]) {
		default:main_statistics();exit;break;
	}
	
	
}




function main_statistics(){
	$tab=main_tabs();
	$html="$tab";
	if($_GET["tab"]<>null){
	$html=$html."<H3>{statistics} &raquo;{{$_GET["tab"]}}</H3>
	<p class=caption>{{$_GET["tab"]}_text}</p>
	";
	}
	$html=$html . "	<table style='width:100%'>
	<tr>
		<td>
		<H3>{day}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=days&ds={$_GET["tab"]}'></center></td>
	</tr>
	<tr>
		
		<td>
		<hr>
		<H3>{week}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=weeks&ds={$_GET["tab"]}'></center></td>
	</tr>
	<tr>
		
		<td>
		<hr>
		<H3>{month}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=months&ds={$_GET["tab"]}'></center></td>
	</tr>
	<tr>
		
		<td>
		<hr>
		<H3>{year}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=years&ds={$_GET["tab"]}'></center></td>
	</tr>			
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}



function ApplySettings_icon(){
$tpl=new templates();
return  $tpl->_ENGINE_parse_body(RoundedLightGrey(Paragraphe("system-64.png",'{apply_settings}',"{apply_settings_text}","javascript:CompileBind9();","apply_settings")));
	
}

function Forwarders_list(){
	
	$bind=new bind9();
	$html="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($bind->forwarders) ){
		$html=$html . "<tr ".  CellRollOver().">
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td><strong>$ligne</strong></td>
		<td width=1%>". imgtootltip('ed_delete.gif',"{delete} $ligne","forwarder_delete($num);")."</td>
		</tr>
		
		";}
		
	$html=$html."</table>";
$tpl=new templates();
	return RoundedLightGreen($tpl->_ENGINE_parse_body($html));	
	
}


function main_tabs(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$array[""]='{all}';
	$array["sum"]='{sum}';
	$array['success']='{success}';
	$array['recursion']='{recursion}';
	$array['failure']='{failure}';
	
	 
	

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=yes&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";		
}


function main_status(){
	$users=new usersMenus();
	$tpl=new templates();

	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$key_service="BIND9";
	$ini->loadString($sock->getfile('bind9status',$_GET["hostname"]));	
	if($ini->_params["$key_service"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
	}
	
	$status1="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{{$ini->_params["$key_service"]["service_name"]}}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["$key_service"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap><strong>{$ini->_params["$key_service"]["master_memory"]}&nbsp; kb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["$key_service"]["master_version"]}</strong></td>
		</tr>				
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status1=RoundedLightGreen($status1);
	return $tpl->_ENGINE_parse_body($status1);
	
	
}

?>
	
	
	
	
