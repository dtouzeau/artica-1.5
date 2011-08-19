<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.kav4proxy.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');

$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_POST["update-kav4proxy"])){kav4Proxy_Update_Now();exit;}
	
page();

function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
	if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
	$license=Paragraphe("64-kav-license.png", "{license_info}", "{license_info_text}","javascript:Loadjs('Kav4Proxy.License.php')");
	$update_kaspersky=Paragraphe('kaspersky-update-64.png','{UPDATE_ANTIVIRUS}','{APP_KAV4PROXY}<br>{UPDATE_ANTIVIRUS_TEXT}',
	"javascript:UpdateKav4Proxy()");
	
	$update_events=Paragraphe('events-64.png','{update_events}','{update_events_text}',
	"javascript:Loadjs('Kav4Proxy.Update.Events.php')");
	
	if($SQUIDEnable==0){$tr[]=Paragraphe('bg-server-settings-64.png','{enable_squid_service}','{enable_squid_service_text}',"javascript:Loadjs('squid.newbee.php?reactivate-squid=yes')");}
	$tr[]=$license;
	$tr[]=$update_kaspersky;
	$tr[]=$update_events;
	
$tables[]="<table style='width:100%'><tbody><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</tbody></table>
<script>
	var x_UpdateKav4Proxy= function (obj) {
	      var results=obj.responseText;
	      alert(results);
	}	

	function UpdateKav4Proxy(){
			var XHR = new XHRConnection();
			XHR.appendData('update-kav4proxy','yes');
			XHR.sendAndLoad('$page', 'POST',x_UpdateKav4Proxy);	
	}
</script>
";	
$html=implode("\n",$tables);	
echo $tpl->_ENGINE_parse_body($html);	
	
}


function kav4Proxy_Update_Now(){
	$sock=new sockets();
	$sock->getFrameWork("squid.php?kav4proxy-update-now=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{UPDATE_ANTIVIRUS_DATABASE_PERFORMED}");
}