<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsPostfixAdministrator){
	$tpl=new templates();
	echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();
	}	
	
	
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["table"])){smtp_sasl_password_maps_table();exit;}
	if(isset($_GET["DeleteSmtpSaslPasswordMaps"])){DeleteSmtpSaslPasswordMaps();exit;}
	if(isset($_GET["DeleteGenericPasswordMaps"])){DeleteGenericPasswordMaps();exit;}
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{passwords_table}");
	
	$html="YahooWin3('680','$page?popup=yes','$title');";
	
	echo $html;
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<div class=explain>{smtp_sasl_password_maps_table_explain}</div>
	
	<div id='smtp_sasl_password_maps_table'></div>
	
	<script>
		function smtp_sasl_password_maps_table_refresh(){
			LoadAjax('smtp_sasl_password_maps_table','$page?table=yes');
		
		}
			
	var X_DeleteSmtpSaslPasswordMaps= function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			smtp_sasl_password_maps_table_refresh();
		}		
		
		
	function DeleteSmtpSaslPasswordMaps(uencode){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteSmtpSaslPasswordMaps',uencode);
			XHR.sendAndLoad('$page', 'GET',X_DeleteSmtpSaslPasswordMaps);
			
		}	
		
	function DeleteGenericPasswordMaps(email,mArray){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteGenericPasswordMaps',email);
			XHR.appendData('mArray',mArray);
			XHR.sendAndLoad('$page', 'GET',X_DeleteSmtpSaslPasswordMaps);	
	
	}
		
	smtp_sasl_password_maps_table_refresh();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function DeleteSmtpSaslPasswordMaps(){
	$ldap=new clladp();
	$server=base64_decode($_GET["DeleteSmtpSaslPasswordMaps"]);
	$dn="cn=$server,cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
	
	if($ldap->ExistsDN($dn)){
		if(!$ldap->ldap_delete($dn,true)){
			echo "$dn\n$ldap->ldap_last_error";
			return;
		}
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-relayhost=yes");
	
}


function smtp_sasl_password_maps_table(){
	$maps=new smtp_sasl_password_maps();
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{relay}</th>
			<th>{username}</th>
			<th>{password}</th>
			<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody class='tbody'>";		
	
	
	while (list ($relaisa, $ligne) = each ($maps->smtp_sasl_password_hash) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$username=null;
		$password=null;
		if(preg_match("#^(.+?):(.+?)$#",$ligne,$re)){$username=$re[1];$password=$re[2];}
		$relaisa_encoded=base64_encode($relaisa);
		$delete=imgtootltip("delete-32.png","{delete}","DeleteSmtpSaslPasswordMaps('$relaisa_encoded')");
		
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:13px'><strong><code style='font-size:13px'>$relaisa</code></strong></td>
		<td style='font-size:13px'><strong style='font-size:13px'>$username</strong></td>
		<td style='font-size:13px'>***** (".strlen($password)." {characters})</td>
		<td style='font-size:13px'>$delete</td>
		</tr>
		
		";
		}
		
		
	$sender_dependent_relayhosts_maps=sender_dependent_relayhosts_maps();

	while (list ($relaisa, $ligne) = each ($sender_dependent_relayhosts_maps) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$type=$ligne["type"];
		$passwordt=$ligne["PASSWORD"];
		$type_value=$ligne["value"];
		
		
		$username=null;
		$password=null;
		if(preg_match("#^(.+?):(.+?)$#",$passwordt,$re)){$username=$re[1];$password=$re[2];}
		$relaisa_encoded=base64_encode($relaisa);
		$type_value_encoded=base64_encode(serialize($ligne));
		$delete=imgtootltip("delete-32.png","{delete}","DeleteGenericPasswordMaps('$relaisa_encoded','$type_value_encoded')");
		
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:13px'><strong><code style='font-size:13px'>$relaisa</code></strong></td>
		<td style='font-size:13px'><strong style='font-size:13px'>$username</strong></td>
		<td style='font-size:13px' nowrap>***** (".strlen($password)." {characters})</td>
		<td style='font-size:13px'>$delete</td>
		</tr>
		
		";
		}
		
		
		$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function DeleteGenericPasswordMaps(){
	$email=base64_decode($_GET["DeleteGenericPasswordMaps"]);
	writelogs("Delete $email",__FUNCTION__,__FILE__,__LINE__);
	
	
	$ligne=unserialize(base64_decode($_GET["mArray"]));
	
	
	
	if(!is_array($ligne)){
		echo "mArray !== Not an array()\n";
		return;
	}
	
	$type=$ligne["type"];
	$passwordt=$ligne["PASSWORD"];
	$type_value=$ligne["value"];
	writelogs("Delete $type=$type_value",__FUNCTION__,__FILE__,__LINE__);

	if($type=="uid"){
		include_once(dirname(__FILE__)."/ressources/class.user.inc");
		$u=new user($type_value);
		$u->del_transport();
		return;
		
	}
	
	
	if($type=="dn"){
		$ldap=new clladp();
		if(!$ldap->ldap_delete($type_value)){
			echo basename(__FILE__)."\nLine:\n".__LINE__."\nError:$type_value\n$ldap->ldap_last_error";
			return;
		}
		
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-relayhost=yes");
	$sock->getFrameWork("cmd.php?postfix-hash-senderdependent=yes");
	
}

function sender_dependent_relayhosts_maps(){
	$ldap=new clladp();
	$filter="(&(objectClass=SenderDependentRelayhostMaps)(cn=*))";
	$attrs=array("cn","SenderRelayHost","dn");
	$dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=trim($hash[$i][strtolower("SenderRelayHost")][0]);
		$sender_dependent_relayhost_maps[$mail]=array("PASSWORD"=>"$value","type"=>"dn","value"=>"{$hash[$i]["dn"]}");
	}
	
	$filter="(&(objectClass=userAccount)(mail=*))";
	$attrs=array("mail","AlternateSmtpRelay","uid");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$value=trim($hash[$i][strtolower("AlternateSmtpRelay")][0]);
		if($value==null){continue;}
		$sender_dependent_relayhost_maps[$mail]=array("PASSWORD"=>"$value","type"=>"uid","value"=>"{$hash[$i]["uid"][0]}");
	}	
	
	$filter="(&(objectClass=SenderDependentSaslInfos)(cn=*))";
	$attrs=array("cn","SenderCanonicalRelayHost","dn");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=trim($hash[$i][strtolower("SenderCanonicalRelayHost")][0]);
		$sender_dependent_relayhost_maps[$mail]=array("PASSWORD"=>"$value","type"=>"dn","value"=>"{$hash[$i]["dn"]}");
	
	}
	
	

	return $sender_dependent_relayhost_maps;
}
?>
