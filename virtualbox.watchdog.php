<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsVirtualBoxManager==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["VirtualBoxWtachDogEnabled"])){SAVE();exit;}	

	

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$infos=$sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]));
	$array=unserialize(base64_decode($infos));
	$title=$tpl->_ENGINE_parse_body("{VIRTUALBOX_WATCHDOG}: {$array["NAME"]}");
	$html="
		function VirtualBoxWatchdogLoad(){
			YahooWin3('550','$page?popup=yes&uuid={$_GET["uuid"]}','$title');
		}

		VirtualBoxWatchdogLoad()";

echo $html;
	
}

function popup(){
	$uuid=$_GET["uuid"];
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();		
	$sql="SELECT `uuid` FROM virtualbox_watchdog WHERE `uuid`='$uuid'";
	$q=new mysql();
	$enabled=0;
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["uuid"]<>null){$enabled=1;}
	
	$virt=Paragraphe_switch_img("{ENABLE_VIRTUALBOX_WATCHDOG}","{VIRTUALBOX_WATCHDOG_TEXT}","VirtualBoxWtachDogEnabled",$enabled);
	
	$html="
	<div id='Watchhddiv'>
	<table style='width:100%'>
		<tr>
			<td valign='top'><img src='img/rouage-90.png'></td>
			<td valign='top'>$virt
				<hr>
				<div style='text-align:right;width:100%'>". button("{apply}","SaveWtachDogVirt()")."</div>
			</td>
		</tr>
	</table>
	</div>
	<script>
var X_SaveWtachDogVirt= function (obj) {
	var results=obj.responseText;
	document.getElementById('Watchhddiv').innerHTML='';
	if(results.length>0){alert(results);}
	YahooWin3Hide(); 
	}	
	
	
function SaveWtachDogVirt(){
		var XHR = new XHRConnection();
		XHR.appendData('VirtualBoxWtachDogEnabled',document.getElementById('VirtualBoxWtachDogEnabled').value);
		XHR.appendData('uuid','{$_GET["uuid"]}');
		document.getElementById('Watchhddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveWtachDogVirt);		
	}	
</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
		
	
}

function SAVE(){
	
	if($_GET["VirtualBoxWtachDogEnabled"]==1){
		$sql="INSERT INTO virtualbox_watchdog (`uuid`) VALUES ('{$_GET["uuid"]}');";
	}else{
		$sql="DELETE FROM virtualbox_watchdog WHERE uuid='{$_GET["uuid"]}'";
	}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
}