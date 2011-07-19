<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["EnableQOS"])){SaveWebQOS();exit;}

page();	
	
	
function page(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sock=new sockets();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize($ligne["Params"]);	
	
	$EnableQOS=$Params["QOS"]["EnableQOS"];
	$QS_ClientEntries=$Params["QOS"]["QS_ClientEntries"];
	$QS_SrvMaxConnPerIP=$Params["QOS"]["QS_SrvMaxConnPerIP"];
	$MaxClients=$Params["QOS"]["MaxClients"];
	$QS_SrvMaxConnClose=$Params["QOS"]["QS_SrvMaxConnClose"];
	$QS_SrvMinDataRate=$Params["QOS"]["QS_SrvMinDataRate"];
	$LimitRequestFields=$Params["QOS"]["LimitRequestFields"];
	$QS_LimitRequestBody=$Params["QOS"]["QS_LimitRequestBody"];
	if(!is_numeric($QS_ClientEntries)){$QS_ClientEntries=100000;}
	if(!is_numeric($QS_SrvMaxConnPerIP)){$QS_SrvMaxConnPerIP=50;}
	if(!is_numeric($MaxClients)){$MaxClients=256;}
	if(!is_numeric($QS_SrvMaxConnClose)){$QS_SrvMaxConnClose=180;}
	if($QS_SrvMinDataRate==null){$QS_SrvMinDataRate="150 1200";}
	if(!is_numeric($LimitRequestFields)){$LimitRequestFields=30;}
	if(!is_numeric($QS_LimitRequestBody)){$QS_LimitRequestBody=102400;}
	if(!is_numeric($EnableQOS)){$EnableQOS=0;}
	
	$html="
	<div id='webdav-qos'>
	<div style='font-size:16px'>{QOS}</div>
	<div class=explain>{mod_qos_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable_qos_service}:</td>
		<td>". Field_checkbox("EnableQOS",1,$EnableQOS)."</td>
		<td width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{QS_ClientEntries}:</td>
		<td>". Field_text("QS_ClientEntries",$QS_ClientEntries,"font-size:13px;width:90px")."</td>
		<td>". help_icon("{QS_ClientEntries_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{QS_SrvMaxConnPerIP}:</td>
		<td>". Field_text("QS_SrvMaxConnPerIP",$QS_SrvMaxConnPerIP,"font-size:13px;width:30px")."</td>
		<td>". help_icon("{QS_SrvMaxConnPerIP_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{QOSMaxClients}:</td>
		<td>". Field_text("MaxClients",$MaxClients,"font-size:13px;width:30px")."</td>
		<td>". help_icon("{QOSMaxClients_explain}")."</td>
	</tr>		
	<tr>
		<td class=legend>{QS_SrvMaxConnClose}:</td>
		<td>". Field_text("QS_SrvMaxConnClose",$QS_SrvMaxConnClose,"font-size:13px;width:60px")."</td>
		<td>". help_icon("{QS_SrvMaxConnClose_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{QS_SrvMinDataRate}:</td>
		<td>". Field_text("QS_SrvMinDataRate",$QS_SrvMinDataRate,"font-size:13px;width:90px")."</td>
		<td>". help_icon("{QS_SrvMinDataRate_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{LimitRequestFields}:</td>
		<td>". Field_text("LimitRequestFields",$LimitRequestFields,"font-size:13px;width:60px")."</td>
		<td></td>
	</tr>		
	<tr>
		<td class=legend>{QS_LimitRequestBody}:</td>
		<td>". Field_text("QS_LimitRequestBody",$QS_LimitRequestBody,"font-size:13px;width:90px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=3 align=right><hr>". button("{apply}","SaveWebQOS()")."</td>
	</tr>
	</table>
	<p>&nbsp;</p>
	</div>
	
	<script>
	
		var x_SaveWebQOS=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			RefreshTab('main_config_freewebedit');	
		}		
	
		function SaveWebQOS(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableQOS').checked){
				XHR.appendData('EnableQOS',1);
			}else{
				XHR.appendData('EnableQOS',0);
			}
			
			
			XHR.appendData('QS_ClientEntries',document.getElementById('QS_ClientEntries').value);
			XHR.appendData('QS_SrvMaxConnPerIP',document.getElementById('QS_SrvMaxConnPerIP').value);
			XHR.appendData('MaxClients',document.getElementById('MaxClients').value);
			XHR.appendData('QS_SrvMaxConnClose',document.getElementById('QS_SrvMaxConnClose').value);
			XHR.appendData('QS_SrvMinDataRate',document.getElementById('QS_SrvMinDataRate').value);
			XHR.appendData('LimitRequestFields',document.getElementById('LimitRequestFields').value);
			XHR.appendData('QS_LimitRequestBody',document.getElementById('QS_LimitRequestBody').value);
			XHR.appendData('servername','{$_GET["servername"]}');
			document.getElementById('webdav-qos').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
    		XHR.sendAndLoad('$page', 'GET',x_SaveWebQOS);
		}
		
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SaveWebQOS(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize($ligne["Params"]);	

	while (list ($num, $ligne) = each ($_GET) ){
		$Params["QOS"][$num]=$ligne;
		
	}
	
	$data=addslashes(serialize($Params));
	$sql="UPDATE freeweb SET `Params`='$data' WHERE servername='{$_GET["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_GET["servername"]}");	
	
}



	
	