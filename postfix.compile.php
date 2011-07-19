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
	$html="
	
	
	
	function StartCompilePostfix(){
		YahooWin(500,'$page?popup=yes','$title');
		setTimeout('ApplyAmavis()',1000);
	}
	
	var x_ApplyAmavis= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		compile_kavmilter();
	}	
	
	var x_compile_kavmilter= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		compile_kasmilter();
	}	
	
	var x_compile_kasmilter= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		compile_miltergreylist();
	}
	
	var x_compile_miltergreylist= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		compile_postfix_save();
	}	

	var x_compile_postfix_save= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		compile_postfix_server();
	}		

	var x_compile_postfix_server= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		compile_header_check();
	}
	var x_compile_header_check= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		check_sender_access()
	}
	var x_check_sender_access= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		finish();
	}	

	
	
	
	function finish(){
		ChangeStatus(100);
		document.getElementById('wait_image').innerHTML='&nbsp;';
		document.getElementById('wait_image').innerHTML='&nbsp;';
	}
	

	function FilLogs(logs){
		logs=escapeVal(logs,'<br>');
		var textlogs=document.getElementById('textlogs').innerHTML;
		textlogs='<div style=\"margin:3px;padding:3px;border-bottom:1px solid #CCCCCC\"><code>'+logs+'</code></div>'+textlogs;
		document.getElementById('textlogs').innerHTML=textlogs;
	}
	
	function escapeVal(content,replaceWith){
		content = escape(content) 
	
			for(i=0; i<content.length; i++){
				if(content.indexOf(\"%0D%0A\") > -1){
					content=content.replace(\"%0D%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0A\") > -1){
					content=content.replace(\"%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0D\") > -1){
					content=content.replace(\"%0D\",replaceWith)
				}
	
			}	
		return unescape(content);
	}	
	
	function ApplyAmavis(){
		ChangeStatus(10);
		var XHR = new XHRConnection();
		XHR.appendData('ApplyAmavis','yes');
		XHR.sendAndLoad('$page', 'GET',x_ApplyAmavis);	
		}
	function compile_kavmilter(){
		ChangeStatus(15);
		var XHR = new XHRConnection();
		XHR.appendData('compile_kavmilter','yes');
		XHR.sendAndLoad('$page', 'GET',x_compile_kavmilter);	
		}	
	
	function compile_kasmilter(){
		ChangeStatus(30);
		var XHR = new XHRConnection();
		XHR.appendData('compile_kasmilter','yes');
		XHR.sendAndLoad('$page', 'GET',x_compile_kasmilter);	
		}	
		
	function compile_miltergreylist(){
		ChangeStatus(35);
		var XHR = new XHRConnection();
		XHR.appendData('compile_miltergreylist','yes');
		XHR.sendAndLoad('$page', 'GET',x_compile_miltergreylist);	
		}			

	function compile_postfix_save(){
		ChangeStatus(45);
		var XHR = new XHRConnection();
		XHR.appendData('compile_postfix_save','yes');
		XHR.sendAndLoad('$page', 'GET',x_compile_postfix_save);	
		
		}
	function compile_postfix_server(){
		ChangeStatus(50);
		var XHR = new XHRConnection();
		XHR.appendData('compile_postfix_server','yes');
		XHR.sendAndLoad('$page', 'GET',x_compile_postfix_server);	
		}
	function compile_header_check(){
		ChangeStatus(70);
		var XHR = new XHRConnection();
		XHR.appendData('compile_header_check','yes');
		XHR.sendAndLoad('$page', 'GET',x_compile_header_check);	
		}
		
	function check_sender_access(){
		ChangeStatus(75);
		var XHR = new XHRConnection();
		XHR.appendData('check_sender_access','yes');
		XHR.sendAndLoad('$page', 'GET',x_check_sender_access);			
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
	";
	echo $html;
	}
	
	
function popup(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	$pourc=0;
	$table=Status(0);
	$color="#5DD13D";
	$html="<H1>{APP_POSTFIX}</H1>
	<p class=caption>{APPLY_SETTINGS_POSTFIX}</p>
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
	" . RoundedLightWhite("<div id='textlogs' style='width:99%;height:120px;overflow:auto'></div>")."";
	
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
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if(!$users->AMAVIS_INSTALLED){
			echo $tpl->_ENGINE_parse_body("<strong>{APP_AMAVISD_NEW}:</strong> {error_module_not_installed}");
			die();
	}
	
	if($users->EnableAmavisDaemon<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_AMAVISD_NEW}:</strong> {error_module_not_enabled}");
		die();		
	}
include_once("ressources/class.amavis.inc");
$amavis=new amavis();
$amavis->SaveToServer();	


	
}

function compile_kavmilter(){
	$tpl=new templates();
	$users=new usersMenus();
	$users->LoadModulesEnabled();	
	if(!$users->KAV_MILTER_INSTALLED){
			echo $tpl->_ENGINE_parse_body("<strong>{APP_KAVMILTER}:</strong> {error_module_not_installed}");
			die();
	}	
	
	if($users->KAVMILTER_ENABLED<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_KAVMILTER}:</strong> {error_module_not_enabled})");
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
if(!$users->kas_installed){
			echo $tpl->_ENGINE_parse_body("<strong>{APP_KAS3}:</strong> {error_module_not_installed}");
			die();
	}		
if($users->KasxFilterEnabled<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_KAS3}:</strong> {error_module_not_enabled})");
		die();	
}
	include_once("ressources/class.kas-filter.inc");
	$kas=new kas_single();
	$kas->Save();
}

function compile_postfix_save(){
	$tpl=new templates();
	$tpl=new templates();
	$main=new main_cf();
	$main->save_conf();
	echo $tpl->_ENGINE_parse_body("<br><strong>{APP_POSTFIX}:</strong>{postfix_main_settings} {success}");
}
function compile_postfix_server(){	
	$tpl=new templates();
	$main=new main_cf();
	if(!$main->save_conf_to_server()){
			echo $tpl->_ENGINE_parse_body("<br><strong>{APP_POSTFIX}:</strong>{postfix_main_settings} {error}");
			echo $tpl->_ENGINE_parse_body('<br>{postfix_main_settings} {error}');
			return null;
		}	
	echo $tpl->_ENGINE_parse_body("<br><strong>{APP_POSTFIX}:</strong>{apply config} {success}");
	
}
function compile_header_check(){	
		$tpl=new templates();
		$filters=new main_header_check();
		$filters->SaveToDaemon();		
		echo $tpl->_ENGINE_parse_body("<strong>{POSTFIX_FILTERS}:</strong>&nbsp;{apply config}&nbsp;{success}");
		
}
function check_sender_access(){
	$tpl=new templates();
	$main=new main_cf();
	$u=$main->check_sender_access();
	if($u>0){
		echo $tpl->_ENGINE_parse_body("\n{ENABLE_INTERNET_DENY} {success} {$u} {users} {enabled}","postfix.index.php");
	}	
	
}

function compile_miltergreylist(){
	$users=new usersMenus();
	$tpl=new templates();
	$users->LoadModulesEnabled();
	
	$policy=new policydweight();
	$policy->SaveConf();
	
	if($users->MILTERGREYLIST_INSTALLED<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_MILTERGREYLIST}:</strong> {error_module_not_installed})");
		die();	
	}	
	
if($users->MilterGreyListEnabled<>1){
		echo $tpl->_ENGINE_parse_body("<strong>{APP_MILTERGREYLIST}:</strong> {error_module_not_enabled})");
		die();	
	}
	
	$milter=new milter_greylist();
	$milter->SaveToLdap();
	echo $tpl->_ENGINE_parse_body("<br><strong>{APP_MILTERGREYLIST}:</strong>{apply config} {success}");
	}





?>