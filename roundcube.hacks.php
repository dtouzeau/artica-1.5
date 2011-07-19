<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.roundcube.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cron.inc');
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["main"])){main();exit;}
	if(isset($_GET["RoundCubeHackEnabled"])){save();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["ip-enabled"])){events_save();exit;}
	if(isset($_GET["delete-ip"])){delete_ip();exit;}
js();



function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{APP_ROUNDCUBE}::Anti-Hacks');

	$html="YahooWin3('600','$page?popup=yes','$title');";
	
	echo $html;
	
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();

	$array["main"]='{parameters}';
	$array["events"]="{events}";
	

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_roundcube_hack style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_roundcube_hack').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";	
}


function main(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$RoundCubeHackEnabled=$sock->GET_INFO("RoundCubeHackEnabled");
	$RoundCubeHackMaxAttempts=$sock->GET_INFO("RoundCubeHackMaxAttempts");
	$RoundCubeHackMaxAttemptsTimeMin=$sock->GET_INFO("RoundCubeHackMaxAttemptsTimeMin");
	if($RoundCubeHackEnabled==null){$RoundCubeHackEnabled=1;}
	if($RoundCubeHackMaxAttempts==null){$RoundCubeHackMaxAttempts=6;}
	if($RoundCubeHackMaxAttemptsTimeMin==null){$RoundCubeHackMaxAttemptsTimeMin=10;}
	$enable=Paragraphe_switch_img("{enable}","{AntiHacks_roundcube_explain}","RoundCubeHackEnabled",$RoundCubeHackEnabled,400);
	$html="
	<div id='roundcubehackdiv'>
	<table style='widht:100%'>
	<tr>
		<td valign='top' width=1%>$enable</td>
	</tr>
	<tr>
		<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td class=legend>{MaxAttemtps}:</td>
				<td>".Field_text("RoundCubeHackMaxAttempts","$RoundCubeHackMaxAttempts","font-size:13px;padding:3px;width:90px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{MaxTime}:</td>
				<td>".Field_text("RoundCubeHackMaxAttemptsTimeMin","$RoundCubeHackMaxAttemptsTimeMin","font-size:13px;padding:3px;width:90px")."&nbsp;{minutes}</td>
			</tr>			
			<tr>
				<td colspan=2 align='right'><hr>".button("{apply}","SaveRoundCubeHacks()")."</td>
			</tr>
		</table>
		</td>
	</tr>
	</table>
	</div>
	<script>
	var x_SaveRoundCubeHacks= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_roundcube_hack');
	}	
	
		function SaveRoundCubeHacks(){
			var XHR = new XHRConnection();
			XHR.appendData('RoundCubeHackEnabled',document.getElementById('RoundCubeHackEnabled').value);
			XHR.appendData('RoundCubeHackMaxAttempts',document.getElementById('RoundCubeHackMaxAttempts').value);
			XHR.appendData('RoundCubeHackMaxAttemptsTimeMin',document.getElementById('RoundCubeHackMaxAttemptsTimeMin').value);
			document.getElementById('roundcubehackdiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveRoundCubeHacks);	
		}
	</script>		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}	

function save(){
	$sock=new sockets();
	$sock->SET_INFO("RoundCubeHackEnabled",$_GET["RoundCubeHackEnabled"]);
	$sock->SET_INFO("RoundCubeHackMaxAttempts",$_GET["RoundCubeHackMaxAttempts"]);
	$sock->SET_INFO("RoundCubeHackMaxAttemptsTimeMin",$_GET["RoundCubeHackMaxAttemptsTimeMin"]);
	$sock->getFrameWork("cmd.php?roundcube-hack=yes");
	
}

function events(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
		
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th width=1%>&nbsp;</th>
	<th>{ip_address}</th>
	<th>{roundcube_instance}</th>
	<th width=1%>{enabled}</th>
	<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	
	$RoundCubeHackConfig=unserialize(base64_decode($sock->GET_INFO("RoundCubeHackConfig")));
	if(is_array($RoundCubeHackConfig)){
		while (list ($instance, $conf) = each ($RoundCubeHackConfig) ){
			if(!is_array($conf)){continue;}
			while (list ($ip, $enabled) = each ($conf) ){
				$field_enabled=1;
				if(!$enabled){$field_enabled=0;}
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$md5=md5("$instance$ip");
				$hiddens[]="<input type='hidden' id='{$md5}_value' value='". base64_encode(serialize(array("$instance",$ip)))."'>";
				
				$delete=imgtootltip("delete-24.png","{delete}","RAntiHackDelete('$md5')");
				
				$html=$html."
				
				
				<tr class=$classtr>
					<td width=1%><img src='img/fw_bold.gif'>
					<td><strong style='font-size:14px'>$ip</td>
					<td><strong style='font-size:14px'>$instance</td>
					<td width=1%>". Field_checkbox("{$md5}_chk",1,$field_enabled,"RAntiHack('$md5')")."</td>
					<td width=1%>$delete</td>
				</tr>
				";
				
				
				
			}			
		}	
	}
	
	$html=$html."</tbody></table>".@implode("\n",$hiddens)."
	<script>
		function RAntiHack(id){
			var XHR = new XHRConnection();
			XHR.appendData('array_content',document.getElementById(id+'_value').value);
			if(document.getElementById(id+'_value').checked){XHR.appendData('ip-enabled',1);}else{XHR.appendData('ip-enabled',0);}
			XHR.sendAndLoad('$page', 'GET');
		}
		
		var x_RAntiHackDelete= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_config_roundcube_hack');
		}			
		
		function RAntiHackDelete(id){
			var XHR = new XHRConnection();
			XHR.appendData('array_content',document.getElementById(id+'_value').value);
			XHR.appendData('delete-ip','yes');
			XHR.sendAndLoad('$page', 'GET',x_RAntiHackDelete);
		}		
	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function events_save(){

	if($_GET["ip-enabled"]==1){$enabled=true;}else{$enabled=false;}
	$array=unserialize(base64_decode($_GET["array_content"]));
	$instance=$array[0];
	$ip=$array[1];
	
	$sock=new sockets();
	$RoundCubeHackConfig=unserialize(base64_decode($sock->GET_INFO("RoundCubeHackConfig")));
	$RoundCubeHackConfig[$instance][$ip]=$enabled;
	$sock->SaveConfigFile(base64_encode(serialize($RoundCubeHackConfig)),"RoundCubeHackConfig");	
	$sock->getFrameWork("cmd.php?roundcube-hack=yes");
	
}
function delete_ip(){
	$array=unserialize(base64_decode($_GET["array_content"]));
	$instance=$array[0];
	$ip=$array[1];	
	$sock=new sockets();
	$RoundCubeHackConfig=unserialize(base64_decode($sock->GET_INFO("RoundCubeHackConfig")));
	unset($RoundCubeHackConfig[$instance][$ip]);
	$sock->SaveConfigFile(base64_encode(serialize($RoundCubeHackConfig)),"RoundCubeHackConfig");	
	$sock->getFrameWork("cmd.php?roundcube-hack=yes");
}
	
 

