<?php
include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.clamav.inc');
	include_once('ressources/class.kas-filter.inc');


	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SAVE_QUAR"])){SAVE_QUAR();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{quarantine_policies}");
	echo "YahooWin3('650','$page?popup=yes','$title')";
	
	
}

function popup(){
	$users=new usersMenus();
	$users->LoadModulesEnabled();	
	$page=CurrentPageName();
	$tpl=new templates();
	$kas_enabled=0;
	$amavis_enabled=0;
	$kas=new kas_mysql("default");
	if($kas->GET_KEY("ACTION_SPAM_MODE")==2){$ACTION_SPAM_MODE=1;}
	if($kas->GET_KEY("ACTION_PROBABLE_MODE")==2){$ACTION_PROBABLE_MODE=1;}
	if($kas->GET_KEY("ACTION_FORMAL_MODE")==2){$ACTION_FORMAL_MODE=1;}
	
	
	
	$amavis=new amavis();
	$sa_tag3_level_defltl=$tpl->_ENGINE_parse_body('{sa_tag3_level_deflt}');
	
	
	if($users->kas_installed){if($users->KasxFilterEnabled==1){$kas_enabled=1;}}
	if($users->AMAVIS_INSTALLED){if($users->EnableAmavisDaemon==1){$amavis_enabled=1;}}
	
	
	$html="
	<div id='kas3feature'>
		<table style='width:100%' class=form>
		<tr>
			<td colspan=2><strong style='font-size:16px'>{APP_KAS3}</strong></td>
		</tr>
		<tr>
			<td class=legend>{spam option 1}</td>
			<td>". Field_checkbox("ACTION_SPAM_MODE",1,$ACTION_SPAM_MODE)."</td>
		</tr>
		<tr>
			<td class=legend>{spam option 2}</td>
			<td>". Field_checkbox("ACTION_PROBABLE_MODE",1,$ACTION_PROBABLE_MODE)."</td>
		</tr>	
		<tr>
			<td class=legend>{spam option 4}</td>
			<td>". Field_checkbox("ACTION_FORMAL_MODE",1,$ACTION_FORMAL_MODE)."</td>
		</tr>	
		</table>
	</div>
	
	<div id='amavisfeature'>
		<table style='width:100%' class=form>
			<tr>
				<td colspan=2><strong style='font-size:16px'>{APP_AMAVIS}/{APP_SPAMASSASSIN}</strong></td>
			</tr>
			<tr>
				<td class=legend>{spam option 1} <b>({score} {$amavis->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]})</b></td>
				<td>". Field_checkbox("EnableQuarantineSpammy2",1,$amavis->EnableQuarantineSpammy2)."</td>
			</tr>			
			<tr>
				<td class=legend>{spam option 2} <b>({score} {$amavis->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]})</b></td>
				<td>". Field_checkbox("EnableQuarantineSpammy",1,$amavis->EnableQuarantineSpammy)."</td>
			</tr>
	
			</table>
	</div>
	
	
	<div style='text-align:right;width:100%'><hr>". button("{apply}","SaveQuarPolicies()")."</div>
	
	<script>
		function DisableQuarPolicies(){
			var kas_enabled=$kas_enabled;
			var amavis_enabled=$amavis_enabled;
			if(kas_enabled!==1){
				document.getElementById('ACTION_SPAM_MODE').disabled=true;
				document.getElementById('ACTION_PROBABLE_MODE').disabled=true;
				document.getElementById('ACTION_FORMAL_MODE').disabled=true;
			}
			
			if(amavis_enabled!==1){
				document.getElementById('EnableQuarantineSpammy2').disabled=true;
				document.getElementById('EnableQuarantineSpammy').disabled=true;
			}			
		
		}
		
var x_SaveQuarPolicies= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	YahooWin3Hide();
}		
	
	
		function SaveQuarPolicies(){
			var kas_enabled=$kas_enabled;
			var amavis_enabled=$amavis_enabled;		
			var XHR = new XHRConnection();
			XHR.appendData('SAVE_QUAR',1);
			if(kas_enabled==1){
				XHR.appendData('SAVE_KAS',1);
				if(document.getElementById('ACTION_SPAM_MODE').checked){XHR.appendData('ACTION_SPAM_MODE',1);}else{XHR.appendData('ACTION_SPAM_MODE',0);}
				if(document.getElementById('ACTION_PROBABLE_MODE').checked){XHR.appendData('ACTION_PROBABLE_MODE',1);}else{XHR.appendData('ACTION_PROBABLE_MODE',0);}
				if(document.getElementById('ACTION_FORMAL_MODE').checked){XHR.appendData('ACTION_FORMAL_MODE',1);}else{XHR.appendData('ACTION_FORMAL_MODE',0);}
			}
			
			if(amavis_enabled==1){
				XHR.appendData('SAVE_AMAVIS',1);
				if(document.getElementById('EnableQuarantineSpammy2').checked){XHR.appendData('EnableQuarantineSpammy2',1);}else{XHR.appendData('EnableQuarantineSpammy2',0);}
				if(document.getElementById('EnableQuarantineSpammy').checked){XHR.appendData('EnableQuarantineSpammy',1);}else{XHR.appendData('EnableQuarantineSpammy',0);}
			}
			
			document.getElementById('kas3feature').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			document.getElementById('amavisfeature').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveQuarPolicies);			
		
		}
	
	
	DisableQuarPolicies();
	</script>
	
	


";
	
echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function SAVE_QUAR(){
	if(isset($_GET["SAVE_KAS"])){
		$kas=new kas_mysql("default");
		if($_GET["ACTION_SPAM_MODE"]==1){$kas->SET_VALUE("ACTION_SPAM_MODE",2);}else{$kas->SET_VALUE("ACTION_SPAM_MODE",-1);}
		if($_GET["ACTION_FORMAL_MODE"]==1){$kas->SET_VALUE("ACTION_FORMAL_MODE",2);}else{$kas->SET_VALUE("ACTION_FORMAL_MODE",0);}
		if($_GET["ACTION_PROBABLE_MODE"]==1){$kas->SET_VALUE("ACTION_PROBABLE_MODE",2);}else{
				$kas->SET_VALUE("ACTION_SPAM_MODE",0);
				$kas->SET_VALUE("ACTION_SPAM_SUBJECT_PREFIX","[SPAM]");
			}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?kas-reconfigure=yes");
		}
		
		
		
	if(isset($_GET["SAVE_AMAVIS"])){
		$amavis=new amavis();
		$amavis->main_array["BEHAVIORS"]["spam_quarantine_spammy"]=$_GET["EnableQuarantineSpammy"];
		$amavis->main_array["BEHAVIORS"]["spam_quarantine_spammy2"]=$_GET["EnableQuarantineSpammy2"];
		$amavis->Save();
		
	}
		
	}
	

	
	
	