<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	include_once('ressources/class.milter.greylist.inc');
	include_once('ressources/class.policyd-weight.inc');						
	
	

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["EnableBlockUsersTroughInternet"])){save();exit;}
if(isset($_GET["Status"])){echo Status($_GET["Status"]);exit;}
if(isset($_GET["ApplyAmavis"])){compile_amavis();exit;}
if(isset($_GET["compile_kavmilter"])){compile_kavmilter();exit;}
if(isset($_GET["compile_kasmilter"])){compile_kasmilter();exit;}
if(isset($_GET["compile_postfix_save"])){compile_postfix_save();exit;}
if(isset($_GET["compile_postfix_server"])){compile_postfix_server();exit;}
if(isset($_GET["compile_header_check"])){compile_header_check();exit;}
if(isset($_GET["check_sender_access"])){check_sender_access();exit;}
if(isset($_GET["compile_miltergreylist"])){compile_miltergreylist();exit;}


js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	$title=$tpl->_ENGINE_parse_body('{apply config}',"postfix.index.php");
	$html="YahooWin(500,'$page?popup=yes','$title');";
	echo $html;
	}
	
	
function popup(){
	$users=new usersMenus();
	$tpl=new templates();
	$page=CurrentPageName();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	$pourc=0;
	$table=Status(0);
	$color="#5DD13D";
	$html="
	<div class=explain>{APPLY_SETTINGS_POSTFIX}</div>
	<table style='width:100%'>
	<tr>
		<td width=1%><div id='wait_image'><img src='img/wait.gif'></div>
		</td>
		<td width=99%>
			<table style='width:100%'>
			<tr>
			<td>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_postfix_compile'>
						<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
							<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
						</div>
					</div>
				</div>
			</td>
			</tr>
			</table>		
		</td>
	</tr>
	</table>
	<br>
	<div id='textlogs' style='width:99%;height:120px;overflow:auto'></div>
	
	<script>
	function StartCompilePostfix(){
		setTimeout('ApplyAmavis()',1000);
	}

	function finish(){
		ChangeStatus(100);
		document.getElementById('wait_image').innerHTML='&nbsp;';
		document.getElementById('wait_image').innerHTML='&nbsp;';
		if(document.getElementById('admin_perso_tabs')){RefreshTab('admin_perso_tabs');}
		if(document.getElementById('main_config_postfix')){RefreshTab('main_config_postfix');}
		YahooWinHide();
		
		
	}
	
	function ApplyAmavis(){
		ChangeStatus(10);
		LoadAjaxSilent('textlogs','$page?ApplyAmavis=yes');
		}
		

		
	var x_ChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_postfix_compile').innerHTML=tempvalue;
	}		
		
		
	function ChangeStatus(number){
		var XHR = new XHRConnection();
		XHR.appendData('Status',number);
		XHR.sendAndLoad('$page', 'GET',x_ChangeStatus);	
	}

	
	StartCompilePostfix();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
}
function Status($pourc){
$color="#5DD13D";	
$html="
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
";	


return $html;
	
}


function compile_amavis(){
	$tpl=new templates();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SaveMaincf=yes");
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$page=CurrentPageName();
	
	$script="
	<div id='compile_amavis'></div>
	<script>
		ChangeStatus(15);
		LoadAjaxSilent('compile_amavis','$page?compile_kavmilter=yes');
	</script>	
	
	";
	
	if(!$users->AMAVIS_INSTALLED){
			echo $tpl->_ENGINE_parse_body("<strong>{APP_AMAVISD_NEW}:</strong> {error_module_not_installed}").$script;
			die();
	}
	
	if($users->EnableAmavisDaemon<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_AMAVISD_NEW}:</strong> {error_module_not_enabled}").$script;
		die();		
	}
include_once("ressources/class.amavis.inc");
$amavis=new amavis();
$amavis->SaveToServer();	
$sock=new sockets();
$sock->getFrameWork("services.php?restart-postfix-all=yes");
echo $script;
	
}

function compile_kavmilter(){
	$tpl=new templates();
	$users=new usersMenus();
	$users->LoadModulesEnabled();	
	$page=CurrentPageName();
	
	$script="
	<div id='compile_miltergreylist'></div>
	<script>
		ChangeStatus(35);
		LoadAjaxSilent('compile_miltergreylist','$page?compile_miltergreylist=yes');
	</script>
	";	
	
	if(!$users->KAV_MILTER_INSTALLED){
			echo $tpl->_ENGINE_parse_body("<strong>{APP_KAVMILTER}:</strong> {error_module_not_installed}").$script;
			die();
	}	
	
	if($users->KAVMILTER_ENABLED<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_KAVMILTER}:</strong> {error_module_not_enabled})").$script;
		die();		
	}	
	
include_once("ressources/class.kavmilterd.inc");
$kavmilterd=new kavmilterd();
$kavmilterd->SaveToLdap();
$tpl=new templates();
echo $tpl->_ENGINE_parse_body("<strong>{APP_KAVMILTER}:</strong> {success} {aveserver_main_settings}");		
}
function compile_kasmilter(){
	$tpl=new templates();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$page=CurrentPageName();
	
	$script="
	<div id='compile_kasmilter'></div>
	<script>
		ChangeStatus(30);
		LoadAjaxSilent('compile_kasmilter','$page?compile_kavmilter=yes');
	</script>
	";
	
if(!$users->kas_installed){
			echo $tpl->_ENGINE_parse_body("<strong>{APP_KAS3}:</strong> {error_module_not_installed}").$script;
			die();
	}		
if($users->KasxFilterEnabled<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_KAS3}:</strong> {error_module_not_enabled})").$script;
		die();	
}
	include_once("ressources/class.kas-filter.inc");
	$kas=new kas_single();
	$kas->Save();
}

function compile_postfix_save(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$script="
	<div id='compile_postfix_save'></div>
	<script>
		ChangeStatus(50);
		LoadAjaxSilent('compile_postfix_save','$page?compile_postfix_server=yes');
	</script>
	";		
	
	echo $tpl->_ENGINE_parse_body("<br><strong>{APP_POSTFIX}:</strong>{postfix_main_settings} {success}").$script;
}
function compile_postfix_server(){	
	$tpl=new templates();
	$page=CurrentPageName();
	
	$script="
	<div id='compile_postfix_server'></div>
	<script>
		ChangeStatus(55);
		LoadAjaxSilent('compile_postfix_server','$page?compile_header_check=yes');
	</script>
	";		
	
	echo $tpl->_ENGINE_parse_body("<br><strong>{APP_POSTFIX}:</strong>{apply config} {success}").$script;
	
}
function compile_header_check(){	
		$tpl=new templates();
		$page=CurrentPageName();	
		
	$script="
	<div id='compile_header_check'></div>
	<script>
		ChangeStatus(70);
		LoadAjaxSilent('compile_header_check','$page?check_sender_access=yes');
	</script>
	";				
		
	echo $tpl->_ENGINE_parse_body("<strong>{POSTFIX_FILTERS}:</strong>&nbsp;{apply config}&nbsp;{success}").$script;
		
}
function check_sender_access(){
	$tpl=new templates();

	$script="
	<script>
		finish();
	</script>
	";		
	
	if($u>0){
		echo $tpl->_ENGINE_parse_body("\n{ENABLE_INTERNET_DENY} {success} {$u} {users} {enabled}","postfix.index.php").$script;
}	else{
		echo $script;
	}
	
	$sock=new sockets();
	$sock->getFrameWork("services.php?restart-postfix-all=yes");
	
}

function compile_miltergreylist(){
	$users=new usersMenus();
	$tpl=new templates();
	$users->LoadModulesEnabled();
	$page=CurrentPageName();
	
	$policy=new policydweight();
	$policy->SaveConf();
	
	$script="
	<div id='compile_miltergreylist'></div>
	<script>
		ChangeStatus(45);
		LoadAjaxSilent('compile_miltergreylist','$page?compile_postfix_save=yes');
	</script>
	";	
	
	if($users->MILTERGREYLIST_INSTALLED<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_MILTERGREYLIST}:</strong> {error_module_not_installed})").$script;
		die();	
	}	
	
if($users->MilterGreyListEnabled<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_MILTERGREYLIST}:</strong> {error_module_not_enabled})").$script;
		die();	
	}
	
	$milter=new milter_greylist();
	$milter->SaveToLdap();
	echo $tpl->_ENGINE_parse_body("<br><strong>{APP_MILTERGREYLIST}:</strong>{apply config} {success}").$script;
	}





?>