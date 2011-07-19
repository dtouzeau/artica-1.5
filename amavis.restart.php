<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	$user=new usersMenus();
	if(!$user->AsPostfixAdministrator){"alert('No privileges');";exit;};
	if(isset($_GET["start"])){start();exit;}
	if(isset($_GET["compile-amavis"])){compile_amavis();exit;}
	if(isset($_GET["compile-toserver"])){compile_to_server();exit;}
	if(isset($_GET["stop-svc"])){stop_svc();exit;}
	if(isset($_GET["start-svc"])){start_svc();exit;}
	if(isset($_GET["Status"])){echo Status($_GET["Status"]);exit;}
	
	
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('apply config');
	
$html="

	function AmavisRestartStart(){
		RTMMail('600','$page?start=yes');
		setTimeout('AmavisRestartStep1()',1000);
	
	}
	
	function Amavisfinish(){
		document.getElementById('waitamavis').innerHTML='';
		ChangeStatus(100);
	}
	
	var x_AmavisRestartStep4= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		Amavisfinish();
	}		
	
	
	
	function AmavisRestartStep4(){
		ChangeStatus(50);
		var XHR = new XHRConnection();
		XHR.appendData('start-svc','yes');
		XHR.sendAndLoad('$page', 'GET',x_AmavisRestartStep4);	
		}	
	
	
	var x_AmavisRestartStep3= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		AmavisRestartStep4();
	}	
	
	function AmavisRestartStep3(){
		ChangeStatus(50);
		var XHR = new XHRConnection();
		XHR.appendData('stop-svc','yes');
		XHR.sendAndLoad('$page', 'GET',x_AmavisRestartStep3);	
		}		
	
	var x_AmavisRestartStep2= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		AmavisRestartStep3();
	}		
	
	
	function AmavisRestartStep2(){
		ChangeStatus(20);
		var XHR = new XHRConnection();
		XHR.appendData('compile-toserver','yes');
		XHR.sendAndLoad('$page', 'GET',x_AmavisRestartStep2);	
		}	
	
	
	var x_AmavisRestartStep1= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		AmavisRestartStep2();
	}	
	
	function AmavisRestartStep1(){
		ChangeStatus(10);
		var XHR = new XHRConnection();
		XHR.appendData('compile-amavis','yes');
		XHR.sendAndLoad('$page', 'GET',x_AmavisRestartStep1);	
		}
		
	var x_ChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_amavis').innerHTML=tempvalue;
	}			
		
	function ChangeStatus(number){
		var XHR = new XHRConnection();
		XHR.appendData('Status',number);
		XHR.sendAndLoad('$page', 'GET',x_ChangeStatus);	
	}	
	function FilLogs(logs){
		logs=escapeVal(logs,'<br>');
		var textlogs=document.getElementById('amavistextlogs').innerHTML;
		textlogs='<div style=\"margin:3px;padding:3px;border-bottom:1px solid #CCCCCC\"><code>'+logs+'</code></div>'+textlogs;
		document.getElementById('amavistextlogs').innerHTML=textlogs;
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


AmavisRestartStart();";	
	
echo $html;	
	
}

function compile_amavis(){
	$amavis=new amavis();
	$amavis->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('<hr>{save_config_to_server}<hr> ',"amavis.index.php");
	
}

function compile_to_server(){
	$sock=new sockets();
	echo $sock->getfile('ApplyAmavis');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('<hr>{save_config_to_server} {success}<hr>',"amavis.index.php");
	echo $tpl->_ENGINE_parse_body('<hr>{stop_amavis}<hr>',"amavis.index.php");	
	
}


function start(){
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
	$html="<H1>{APP_AMAVISD_NEW}</H1>
	<p class=caption>{APPLY_SETTINGS_AMAVIS}</p>
	<table style='width:100%'>
	<tr>
		<td width=1%><span id='waitamavis'><img src='img/wait.gif'></span>
		</td>
		<td width=99%>
			<table style='width:100%'>
			<tr>
			<td>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_amavis'>
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
	" . RoundedLightWhite("<div id='amavistextlogs' style='width:99%;height:120px;overflow:auto'></div>")."";
	
	echo $tpl->_ENGINE_parse_body($html,"amavis.index.php");
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

function stop_svc(){
	$sock=new sockets();
	$tpl=new templates();
	echo $sock->getfile('STOP_AMAVISDNEW');
	echo $tpl->_ENGINE_parse_body("{start_amavis}\n","amavis.index.php");		
	
}
function start_svc(){
	$sock=new sockets();
	$tpl=new templates();
	echo $sock->getfile('START_AMAVISDNEW');	
	
}

?>