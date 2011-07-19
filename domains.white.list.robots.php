<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.user.inc');


	if(!GetRights()){
		$tpl=new templates();
		$error="alert('{ERROR_NO_PRIVS}');";
		echo $tpl->_ENGINE_parse_body($error);
		die();
	}

if(isset($_GET["wbl_robots"])){wbl_robots();exit;}

if(isset($_GET["AddWhiteRobot"])){wbl_robots_white_add();exit;}
if(isset($_GET["AddBlackRobot"])){wbl_robots_black_add();exit;}
if(isset($_GET["AddQuarantineRobot"])){wbl_robots_quar_add();exit;}
if(isset($_GET["AddReportRobot"])){wbl_robots_report_add();exit;}
if(isset($_GET["wblrobotslist"])){echo wbl_robotslist();exit;}
if(isset($_GET["DeleteRobot"])){wbl_robots_delete();exit;}
js();


function GetRights(){
	$user=new usersMenus();
	if($user->AllowEditOuSecurity){return true;}
	if($user->AsPostfixAdministrator){return true;}
	if($user->AsMessagingOrg){return true;}
	if($user->AsQuarantineAdministrator){return true;}
	return false;
}

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	

	$title=$tpl->_ENGINE_parse_body('{enable_artica_wbl_robots}');
	
	$html="
	
	
	function wbl_robots_index(){
		YahooWin(750,'$page?wbl_robots=yes&ou={$_GET["ou"]}','$title');
	}
	
	wbl_robots_index();
	
	function SelectDomain(){
		var selected_domain=document.getElementById('selected_domain').value;
		var selected_form=document.getElementById('selected_form').value;
		
		if(selected_domain.length>0){
			if(selected_form.length>0){
			 LoadAjax('wblarea','$page?SelectedDomain='+selected_domain+'&type='+selected_form);
			}
		}
	
	}
	
var x_WhiteListArticaSave= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	LoadAjax('wblrobotslist','$page?wblrobotslist=yes&ou={$_GET["ou"]}');
}	
	
	function WhiteListArticaSave(){
		var domain_white=document.getElementById('domain_white').value;
		var prefix=document.getElementById('whitelist').value;
		var whitelist=prefix+'@'+domain_white;
		var XHR = new XHRConnection();
		XHR.appendData('AddWhiteRobot',whitelist);
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('wblrobotslist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WhiteListArticaSave);
		}
		
	function BlackListArticaSave(){
		var domain_black=document.getElementById('domain_black').value;
		var prefix=document.getElementById('blacklist').value;
		var email=prefix+'@'+domain_black;
		var XHR = new XHRConnection();
		XHR.appendData('AddBlackRobot',email);
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('wblrobotslist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WhiteListArticaSave);
	
	}
	
	function ReportListArticaSave(){
		var domain_report=document.getElementById('domain_report').value;
		var prefix=document.getElementById('report').value;
		var email=prefix+'@'+domain_report;
		var XHR = new XHRConnection();
		XHR.appendData('AddReportRobot',email);
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('wblrobotslist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WhiteListArticaSave);	
	}
	
	function QuarListArticaSave(){
		var domain_report=document.getElementById('domain_quar').value;
		var prefix=document.getElementById('quarantine').value;
		var email=prefix+'@'+domain_report;
		var XHR = new XHRConnection();
		XHR.appendData('AddQuarantineRobot',email);
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('wblrobotslist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WhiteListArticaSave);	
	}	
	
	function BlackListArticaSaveEvent(e){if(checkEnter(e)){BlackListArticaSave();}}
	function WhiteListArticaSaveEvent(e){if(checkEnter(e)){WhiteListArticaSave();}}
	function ReportListArticaSaveEvent(e){if(checkEnter(e)){ReportListArticaSave();}}
	function QuarListArticaSaveEvent(e){if(checkEnter(e)){QuarListArticaSave();}}
	
	
	
	function DeleteRobot(email){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteRobot',email);
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('wblrobotslist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WhiteListArticaSave);
	}
	
	
	
	
	";
	echo $html;
	}
function wbl_robotslist(){

	$ou=$_GET["ou"];
	$ldap=new clladp();
	$dn="cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	$pattern="(&(objectclass=transportTable)(cn=*))";
	$attr=array();
	$sr =@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if(!is_array($hash)){return null;}
	
	$html="<table style='width:100%'>";
	
	
	for($i=0;$i<$hash["count"];$i++){
		$transport=$hash[$i]["transport"][0];
		if(preg_match("#(.+?):(.+)#",$transport,$re)){
			$email=$re[2];
			$transport=$re[1];
			if($transport=="artica-whitelist"){$img="fleche-20-right.png";}
			if($transport=="artica-blacklist"){$img="fleche-20-black-right.png";}
			if($transport=="artica-reportwbl"){$img="info-18.png";}
			if($transport=="artica-reportquar"){$img="spamailbox_storage.gif";}
			
			
			
					
		}
		
		$html=$html . "
			<tr " . CellRollOver().">
				<td width=1%><img src='img/$img'></td>
				<td><code style='font-size:13px'>$email</td>
				<td width=1%>" . imgtootltip('ed_delete.gif',"{delete}","DeleteRobot('$email');")."</td>
			</tr>
				";
		
		
		
	}
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}
	
function wbl_robots(){
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){
		$ct=new user($_SESSION["uid"]);
		$ou=$ct->ou;
	}else{
		if($_GET["ou"]<>null){$ou=$_GET["ou"];}
	}
	$ldap=new clladp();
	$domains=$ldap->hash_get_domains_ou($ou);
	if(is_array($domains)){while (list ($num, $ligne) = each ($domains) ){$fDomains[$ligne]=$ligne;}}	
	
	$domain_white=Field_array_Hash($domains,"domain_white");
	$domain_black=Field_array_Hash($domains,"domain_black");
	$domain_report=Field_array_Hash($domains,"domain_report");
	$domain_quar=Field_array_Hash($domains,"domain_quar");
	
	$form=
		
	"
	<input type='hidden' id='ou' value='{$_GET["ou"]}'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend nowrap>{whitelist_addr}:</td>
		<td>" . Field_text('whitelist',null,"width:100%",null,null,null,false,"WhiteListArticaSaveEvent(event)")."</td>
		<td width=1%><strong>&nbsp;@&nbsp;</strong>
		<td>$domain_white</td>
		<td align='right'><input type='button' OnClick=\"javascript:WhiteListArticaSave();\" value='{add}&nbsp;&raquo;&raquo;'></td> 
		<td width=1%>".help_icon('{enable_artica_wbl_robots_explain}')."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{Blacklist_addr}:</td>
		<td>" . Field_text('blacklist',null,null,null,null,null,false,"BlackListArticaSaveEvent(event)")."</td>
		<td width=1%><strong>&nbsp;@&nbsp;</strong>
		<td>$domain_black</td>		
		<td align='right'><input type='button' OnClick=\"javascript:BlackListArticaSave();\" value='{add}&nbsp;&raquo;&raquo;'></td>
		<td width=1%>".help_icon('{enable_artica_wbl_robots_explain}')."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{report_addr}:</td>
		<td>" . Field_text('report',null,null,null,null,null,false,"ReportListArticaSaveEvent(event)")."</td>
		<td width=1%><strong>&nbsp;@&nbsp;</strong>
		<td>$domain_report</td>		
		<td align='right'><input type='button' OnClick=\"javascript:ReportListArticaSave();\" value='{add}&nbsp;&raquo;&raquo;'></td>
		<td width=1%>".help_icon('{enable_artica_report_robots_explain}')."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{quar_addr}:</td>
		<td>" . Field_text('quarantine',null,null,null,null,null,false,"QuarListArticaSaveEvent(event)")."</td>
		<td width=1%><strong>&nbsp;@&nbsp;</strong>
		<td>$domain_quar</td>		
		<td align='right'><input type='button' OnClick=\"javascript:QuarListArticaSave();\" value='{add}&nbsp;&raquo;&raquo;'></td>
		<td width=1%>".help_icon('{enable_artica_quarantine_robots_explain}')."</td>
	</tr>		
	
	
	</table>
	
	";
	
	
	$html="<H1>{enable_artica_wbl_robots}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/chess-white-100.png'></td>
		<td valign='top'>
			
			$form
			<div id='wblrobotslist' style='width:100%;height:120px;overflow:auto'>".wbl_robotslist()."</div>
			
			</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function wbl_robots_white_add(){
	if(!isset($_GET["ou"])){die('No organization');}
	$ou=$_GET["ou"];
	$users=new usersMenus();
	if($users->AllowEditOuSecurity==false){
		$tpl=new templates();
		$error="\n{ERROR_NO_PRIVS}\n";
		echo $tpl->_ENGINE_parse_body($error);
		die();
	
	}
	
if(preg_match("#^(.+?)@(.+?)@(.+)#",$_GET["AddWhiteRobot"],$re)){$_GET["AddWhiteRobot"]="{$re[1]}@{$re[3]}";}
	
	$ldap=new clladp();
	if($ldap->uid_from_email($_GET["AddWhiteRobot"])<>null){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{error_alias_exists}");
		die();
	}
	
	
	if(!Buildn($ou)){return false;}
	
	$dn="cn={$_GET["AddWhiteRobot"]},cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['objectClass'][1]='top';
		$upd['objectClass'][0]='transportTable';
		$upd['cn'][0]="{$_GET["AddWhiteRobot"]}";
		$upd["transport"]="artica-whitelist:{$_GET["AddWhiteRobot"]}";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
		unset($upd);				
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");
	
}
function wbl_robots_black_add(){
	if(!isset($_GET["ou"])){die('No organization');}
	$ou=$_GET["ou"];
	$users=new usersMenus();
	if($users->AllowEditOuSecurity==false){
		$tpl=new templates();
		$error="\n{ERROR_NO_PRIVS}\n";
		echo $tpl->_ENGINE_parse_body($error);
		die();
	
	}
	
	if(preg_match("#^(.+?)@(.+?)@(.+)#",$_GET["AddBlackRobot"],$re)){$_GET["AddBlackRobot"]="{$re[1]}@{$re[3]}";}
	
	$ldap=new clladp();
	if($ldap->uid_from_email($_GET["AddBlackRobot"])<>null){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{error_alias_exists}");
		die();
	}
	
	if(!Buildn($ou)){return false;}
	
	$dn="cn={$_GET["AddBlackRobot"]},cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['objectClass'][1]='top';
		$upd['objectClass'][0]='transportTable';
		$upd['cn'][0]="{$_GET["AddBlackRobot"]}";
		$upd["transport"]="artica-blacklist:{$_GET["AddBlackRobot"]}";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
		unset($upd);				
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");
	
}

function wbl_robots_report_add(){
	if(!isset($_GET["ou"])){die('No organization');}
	$ou=$_GET["ou"];
	$users=new usersMenus();
	if($users->AllowEditOuSecurity==false){
		$tpl=new templates();
		$error="\n{ERROR_NO_PRIVS}\n";
		echo $tpl->_ENGINE_parse_body($error);
		die();
	
	}
if(preg_match("#^(.+?)@(.+?)@(.+)#",$_GET["AddReportRobot"],$re)){$_GET["AddReportRobot"]="{$re[1]}@{$re[3]}";}
	
	$ldap=new clladp();
	if($ldap->uid_from_email($_GET["AddReportRobot"])<>null){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{error_alias_exists}");
		die();
	}
	
	if(!Buildn($ou)){return false;}
	
	$dn="cn={$_GET["AddReportRobot"]},cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['objectClass'][1]='top';
		$upd['objectClass'][0]='transportTable';
		$upd['cn'][0]="{$_GET["AddReportRobot"]}";
		$upd["transport"]="artica-reportwbl:{$_GET["AddReportRobot"]}";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
		unset($upd);				
	}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");
}

function wbl_robots_quar_add(){
	if(!isset($_GET["ou"])){die('No organization');}
	$ou=$_GET["ou"];
	$users=new usersMenus();
	if($users->AllowEditOuSecurity==false){
		$tpl=new templates();
		$error="\n{ERROR_NO_PRIVS}\n";
		echo $tpl->_ENGINE_parse_body($error);
		die();
	
	}
if(preg_match("#^(.+?)@(.+?)@(.+)#",$_GET["AddQuarantineRobot"],$re)){$_GET["AddQuarantineRobot"]="{$re[1]}@{$re[3]}";}
	
	$ldap=new clladp();
	if($ldap->uid_from_email($_GET["AddQuarantineRobot"])<>null){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{error_alias_exists}");
		die();
	}
	
	if(!Buildn($ou)){return false;}
	
	$dn="cn={$_GET["AddQuarantineRobot"]},cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['objectClass'][1]='top';
		$upd['objectClass'][0]='transportTable';
		$upd['cn'][0]="{$_GET["AddQuarantineRobot"]}";
		$upd["transport"]="artica-reportquar:{$_GET["AddQuarantineRobot"]}";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
		unset($upd);				
	}		
$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");	
	
}

function Buildn($ou){
$ldap=new clladp();
$dn="cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="PostfixRobots";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}
	
	$dn="cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="$ou";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}	
	
	$dn="cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="whitelists";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}
	return true;	
}
function wbl_robots_delete(){
	$email=$_GET["DeleteRobot"];
	if(!isset($_GET["ou"])){die('No organization');}
	$ou=$_GET["ou"];
	$users=new usersMenus();
	if($users->AllowEditOuSecurity==false){
		$tpl=new templates();
		$error="\n{ERROR_NO_PRIVS}\n";
		echo $tpl->_ENGINE_parse_body($error);
		die();
	
	}
	$ldap=new clladp();
	$dn="cn=$email,cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ldap_delete($dn)){echo $ldap->ldap_last_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");	
	
	
}

