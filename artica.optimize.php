<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.backup.inc');
	include_once('ressources/class.os.system.inc');
	
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){die();}
	if(isset($_POST["ApacheGroupware"])){Save();exit;}
	if(isset($_GET["popup"])){popup2();exit;}
	
	popup();
	
	
function popup(){
	
	$page=CurrentPageName();
	$html="<div id='mysqlopt' style='width:100%;height:590px;overflow:auto'></div>
	<script>LoadAjax('mysqlopt','$page?popup=yes');</script>
	";
	echo $html;
	
}	
	
function popup2(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$disable_mysql=0;
	$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
	$EnableNSCD=$sock->GET_INFO("EnableNSCD");
	$LighttpdRunAsminimal=$sock->GET_INFO("LighttpdRunAsminimal");
	$SlapdThreads=$sock->GET_INFO("SlapdThreads");	
	$EnableArticaStatus=$sock->GET_INFO("EnableArticaStatus");	
	$EnableArticaExecutor=$sock->GET_INFO("EnableArticaExecutor");	
	$EnableArticaBackground=$sock->GET_INFO("EnableArticaBackground");	
	$memory=intval($sock->getFrameWork("services.php?total-memory=yes"));
	$MysqlConfigLevel=$sock->GET_INFO("MysqlConfigLevel");
	if($memory<550){$disable_mysql=1;}
	if(!is_numeric($EnableNSCD)){$EnableNSCD=1;}
	if(!is_numeric($LighttpdRunAsminimal)){$LighttpdRunAsminimal=0;}
	if(!is_numeric($EnableArticaStatus)){$EnableArticaStatus=1;}
	if(!is_numeric($EnableArticaExecutor)){$EnableArticaExecutor=1;}
	if(!is_numeric($EnableArticaBackground)){$EnableArticaBackground=1;}
	
	
	if(!is_numeric($MysqlConfigLevel)){$MysqlConfigLevel=0;}
	$users=new usersMenus();
	
	if($users->NSCD_INSTALLED){
		$nscd="
		<tr>
		<td class=legend valign='top'>{APP_NSCD}:</td>
		<td valign='top'>". Field_checkbox("EnableNSCD",1,$EnableNSCD)."</td>
		<td><div class=explain>{NSCD_DISABLE_EXPLAIN}</div></td>
		</tr>	
		
		";
	}
	
	$mysqlr[0]="{default}";
	$mysqlr[1]="{lower_config}";
	$mysqlr[2]="{very_lower_config}";
	$mysqlf=Field_array_Hash($mysqlr,"MysqlConfigLevel", $MysqlConfigLevel,"style:font-size:13px;padding:3px");
	
	
	$html="
	<input type='hidden' id='arcoptze_text' value='{artica_optimize_explain}'>
	<div class=explain id='arcoptze'>{artica_optimize_explain}</div>
	<table style='width:98%' class=form>
	<tr>
		<td class=legend valign='top'>{APP_GROUPWARE_APACHE}:</td>
		<td valign='top'>". Field_checkbox("ApacheGroupware",1,$ApacheGroupware)."</td>
		<td><div class=explain>{APACHE_GROUPWARE_DISABLE_EXPLAIN}</div></td>
	</tr>
	<tr>
		<td class=legend valign='top'>{LighttpdRunAsminimal}:</td>
		<td valign='top'>". Field_checkbox("LighttpdRunAsminimal",1,$LighttpdRunAsminimal)."</td>
		<td><div class=explain>{reduce_artica_web_explain}</div></td>
	</tr>	
	<tr>
		<td class=legend valign='top'>{APP_MYSQL}:</td>
		<td valign='top'>$mysqlf</td>
		<td><div class=explain>{Reduce_mysql_explain}</div></td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveOptimize()")."</td>
	</tR>		
	</table>
	<table style='width:98%' class=form>
	<tr>
		<td class=legend valign='top'>{SlapdThreads}:</td>
		<td valign='top'>". Field_text("SlapdThreads",$SlapdThreads,"font-size:13px;width:60px")."</td>
		<td><div class=explain>{SlapdThreads_explain}</div></td>
	</tr>	
	<tr>
		<td class=legend valign='top'>{APP_ARTICA_STATUS}:</td>
		<td valign='top'>". Field_checkbox("EnableArticaStatus",1,$EnableArticaStatus)."</td>
		<td><div class=explain>{DisableArticaStatusService_explain}</div></td>
	</tr>
	<tr>
		<td class=legend valign='top'>{APP_ARTICA_EXECUTOR}:</td>
		<td valign='top'>". Field_checkbox("EnableArticaExecutor",1,$EnableArticaExecutor)."</td>
		<td><div class=explain>{DisableArticaExecutorService_explain}</div></td>
	</tr>	
	<tr>
		<td class=legend valign='top'>{APP_ARTICA_BACKGROUND}:</td>
		<td valign='top'>". Field_checkbox("EnableArticaBackground",1,$EnableArticaBackground)."</td>
		<td><div class=explain>{DisableEnableArticaBackgroundService_explain}</div></td>
	</tr>	
	
	$nscd
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveOptimize()")."</td>
	</tR>
	
	
	</table>
	
	<script>
	function FieldsChecks(){
		var disable_mysql=$disable_mysql;
		if(disable_mysql==1){document.getElementById('MysqlConfigLevel').disabled=true;}
	}
	
	
	var x_SaveOptimize= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		document.getElementById('arcoptze').innerHTML=document.getElementById('arcoptze_text').value;
		
	}		
	
	function SaveOptimize(){
		var disable_mysql=$disable_mysql;
		var XHR = new XHRConnection();
		XHR.appendData('SlapdThreads',document.getElementById('SlapdThreads').value);
		if(disable_mysql==0){XHR.appendData('MysqlConfigLevel',document.getElementById('MysqlConfigLevel').value);}
		if(document.getElementById('ApacheGroupware').checked){XHR.appendData('ApacheGroupware',1);}else{XHR.appendData('ApacheGroupware',0);}
		if(document.getElementById('EnableArticaStatus').checked){XHR.appendData('EnableArticaStatus',1);}else{XHR.appendData('EnableArticaStatus',0);}
		if(document.getElementById('EnableArticaExecutor').checked){XHR.appendData('EnableArticaExecutor',1);}else{XHR.appendData('EnableArticaExecutor',0);}
		if(document.getElementById('EnableArticaBackground').checked){XHR.appendData('EnableArticaBackground',1);}else{XHR.appendData('EnableArticaBackground',0);}
		if(document.getElementById('LighttpdRunAsminimal').checked){XHR.appendData('LighttpdRunAsminimal',1);}else{XHR.appendData('LighttpdRunAsminimal',0);}
		if(document.getElementById('EnableNSCD')){if(document.getElementById('EnableNSCD').checked){XHR.appendData('EnableNSCD',1);}else{XHR.appendData('EnableNSCD',0);}}
		AnimateDiv('arcoptze');
		XHR.sendAndLoad('$page', 'POST',x_SaveOptimize);
	
	}	

	FieldsChecks();
</script>	
";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function Save(){
	$sock=new sockets();
	$sock->SET_INFO("ApacheGroupware", $_POST["ApacheGroupware"]);
	$sock->SET_INFO("LighttpdRunAsminimal", $_POST["LighttpdRunAsminimal"]);
	$sock->SET_INFO("EnableArticaStatus", $_POST["EnableArticaStatus"]);
	$sock->SET_INFO("EnableArticaExecutor", $_POST["EnableArticaExecutor"]);
	$sock->SET_INFO("EnableArticaBackground", $_POST["EnableArticaBackground"]);
	if(isset($_POST["MysqlConfigLevel"])){$sock->SET_INFO("MysqlConfigLevel", $_POST["MysqlConfigLevel"]);}	
	if(isset($_POST["EnableNSCD"])){$sock->SET_INFO("EnableNSCD", $_POST["EnableNSCD"]);}
	
	$sock->SET_INFO("SlapdThreads", $_POST["SlapdThreads"]);
	$sock->getFrameWork("services.php?restart-apache-groupware=yes");
	$sock->getFrameWork("services.php?restart-lighttpd=yes");
	$sock->getFrameWork("services.php?restart-ldap=yes");
	$sock->getFrameWork("services.php?restart-cron=yes");
	

	if(isset($_POST["EnableNSCD"])){
		$sock->getFrameWork("services.php?restart-artica-status=yes");
		if($_POST["EnableNSCD"]==1){$sock->getFrameWork("services.php?stop-nscd=yes");}
	}
	
	if(isset($_POST["MysqlConfigLevel"])){$sock->getFrameWork("services.php?restart-mysql=yes");}
	
	
	
	
	
}


