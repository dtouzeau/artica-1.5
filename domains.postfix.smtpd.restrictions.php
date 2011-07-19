<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.postfix-multi.inc');
	
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsOrgPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
	
if(isset($_GET["popup"])){smtpd_client_restrictions_popup();exit;}
if(isset($_GET["reject_unknown_client_hostname"])){smtpd_client_restrictions_save();exit;}


js();


function js_smtpd_client_restrictions_save(){
	$page=CurrentPageName();
	$ou=base64_decode($_GET["ou"]);
	return "
function smtpd_client_restrictions_save(){
	var XHR = new XHRConnection();
		XHR.appendData('reject_unknown_client_hostname',document.getElementById('reject_unknown_client_hostname').value);
		XHR.appendData('reject_unknown_reverse_client_hostname',document.getElementById('reject_unknown_reverse_client_hostname').value);
		XHR.appendData('reject_unknown_sender_domain',document.getElementById('reject_unknown_sender_domain').value);
		XHR.appendData('reject_invalid_hostname',document.getElementById('reject_invalid_hostname').value);
		XHR.appendData('reject_non_fqdn_sender',document.getElementById('reject_non_fqdn_sender').value);
		XHR.appendData('EnablePostfixAntispamPack',document.getElementById('EnablePostfixAntispamPack').value);
		XHR.appendData('ou','$ou');
		document.getElementById('smtpd_client_restrictions_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_smtpd_client_restrictions_save);	
	}
	
	
	";
	
	
}


function smtpd_client_restrictions_popup(){
	
	
	$sock=new sockets();
	$restrictions=get_restrictions_classes();	
	$reject_unknown_client_hostname=Paragraphe_switch_img_32('{reject_unknown_client_hostname}','{reject_unknown_client_hostname_text}',
	"reject_unknown_client_hostname",$restrictions["reject_unknown_client_hostname"],"{enable_disable}","100%");
	$reject_unknown_reverse_client_hostname=Paragraphe_switch_img_32('{reject_unknown_reverse_client_hostname}','{reject_unknown_reverse_client_hostname_text}',
	"reject_unknown_reverse_client_hostname",$restrictions["reject_unknown_reverse_client_hostname"],"{enable_disable}","100%");
	
	$reject_unknown_sender_domain=Paragraphe_switch_img_32('{reject_unknown_sender_domain}','{reject_unknown_sender_domain_text}',
	"reject_unknown_sender_domain",$restrictions["reject_unknown_sender_domain"],"{enable_disable}","100%");
	
	$reject_invalid_hostname=Paragraphe_switch_img_32('{reject_invalid_hostname}','{reject_invalid_hostname_text}',
	"reject_invalid_hostname",$restrictions["reject_invalid_hostname"],"{enable_disable}","100%");
	
	$reject_non_fqdn_sender=Paragraphe_switch_img_32('{reject_non_fqdn_sender}','{reject_non_fqdn_sender_text}',
	"reject_non_fqdn_sender",$restrictions["reject_non_fqdn_sender"],"{enable_disable}","100%");
	
	$EnablePostfixAntispamPack=Paragraphe_switch_img_32('{EnablePostfixAntispamPack}','{EnablePostfixAntispamPack_text}',
	"EnablePostfixAntispamPack",$restrictions["EnablePostfixAntispamPack"],"{enable_disable}","100%");	

	
	
	
	
$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
	<img src='img/96-planetes-free.png'>
	</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<p class=caption>{smtpd_client_restrictions_text}</p>
	</td>
	<td valign='top'>
		$whitelists
	</td>
	</tr>
	</table>
	<hr>
	<div style='width:100%;text-align:right'>
	". button('{edit}','smtpd_client_restrictions_save()')."
	</div>
	</td>
	</tr>
	</table>
	<div id='smtpd_client_restrictions_div'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>$reject_unknown_client_hostname</td>
	<td valign='top'>$reject_unknown_reverse_client_hostname</td>
	</tr>
	<tr>
	<td valign='top'>$reject_unknown_sender_domain</td>
	<td valign='top'>$reject_invalid_hostname</td>
	</tr>
	<tr>
	<td valign='top'>$reject_non_fqdn_sender</td>
	<td valign='top'>$EnablePostfixAntispamPack</td>
	</tr>	
	</table>
	</div>";


//smtpd_client_connection_rate_limit = 100
//smtpd_client_recipient_rate_limit = 20
	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
	
	
}

function get_restrictions_classes(){
	$main=new main_multi($_GET["ou"],1);
	return $main->smtpd_client_restrictions_get();
}

function smtpd_client_restrictions_save(){
	$ou=$_GET["ou"];
	unset($_GET["ou"]);
	while (list ($key, $line) = each ($_GET) ){
		$array[$key]=$line;
	}
		$ayrre=base64_encode(serialize($array));
		$q=new mysql();
		$sql="SELECT ID FROM postfix_multi WHERE `key`='smtpd_client_restrictions' AND `ou`='$ou'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]<1){
			$sql="INSERT INTO postfix_multi  (`ou`,`key`,`ValueTEXT`) VALUES('$ou','smtpd_client_restrictions','$ayrre')";
		}else{
			$sql="UPDATE postfix_multi SET `ValueTEXT`='$ayrre' WHERE ID={$ligne["ID"]}";
		}
		$q->QUERY_SQL($sql,"artica_backup");
	
		if(!$q->ok){
			echo $q->mysql_error;
			return;
		}
		
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-ou=$ou");		
		
	return;
	$upd_vals["PostFixRestrictionClassList"][]="permit_mynetworks=\"\"";
	$upd_vals["PostFixRestrictionClassList"][]="permit_sasl_authenticated=\"\"";
	$upd_vals["PostFixRestrictionClassList"][]="check_client_access=\"hash:/etc/postfix/postfix_allowed_connections\"";
	if($_GET["reject_unknown_client_hostname"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_unknown_client_hostname=\"\"";}
	if($_GET["reject_invalid_hostname"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_invalid_hostname=\"\"";}
	if($_GET["reject_unknown_reverse_client_hostname"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_unknown_reverse_client_hostname=\"\"";}
	if($_GET["reject_unknown_sender_domain"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_unknown_sender_domain=\"\"";}
	if($_GET["reject_non_fqdn_sender"]==1){$upd_vals["PostFixRestrictionClassList"][]="reject_non_fqdn_sender=\"\"";}
	
	if($EnablePostfixAntispamPack==1){
		
		$upd_vals["PostFixRestrictionClassList"][]="reject_rbl_client=\"zen.spamhaus.org\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_rbl_client=\"sbl.spamhaus.org\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_rbl_client=\"cbl.abuseat.org\"";
		
		/*reject_rbl_client bl.spamcop.net,
        reject_rbl_client b.barracudacentral.org,
        reject_rbl_client zen.spamhaus.org,
        reject_rbl_client dul.dnsbl.sorbs.net,
        reject_rbl_client psbl.surriel.com,
        reject_rbl_client ix.dnsbl.manitu.net, 
		*/
	}
	
	$upd_vals["PostFixRestrictionClassList"][]="permit=\"\"";
	
	$sock=new sockets();
	$sock->SET_INFO('EnablePostfixAntispamPack',$EnablePostfixAntispamPack);
	
	if(!$ldap->Ldap_modify("cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",$upd_vals)){
		echo "Modify smtpd_client_restrictions branch\n$ldap->ldap_last_error";
			return null;
		}

unset($upd_vals);		
if($EnablePostfixAntispamPack==1){
		$upd_vals["PostFixRestrictionClassList"][]="permit_mynetworks=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="permit_sasl_authenticated=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="check_client_access=\"hash:/etc/postfix/postfix_allowed_connections\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_non_fqdn_hostname=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="reject_invalid_hostname=\"\"";
		$upd_vals["PostFixRestrictionClassList"][]="permit=\"\"";
		
	if(!$ldap->Ldap_modify("cn=smtpd_helo_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",$upd_vals)){
		echo "Modify datas in smtpd_helo_restrictions branch\n$ldap->ldap_last_error";
			return null;
		}		
		
	}		
		
$main=new main_cf();		
$main->save_conf_to_server(1);
$sock=new sockets();
$tpl=new templates();
$datas=$sock->getfile("PostfixReload");
if(trim($datas==null)){
	for($i=0;$i<10;$i++){
		sleep(1);
		$datas=trim($sock->getfile("PostfixReload")) ." ($i)";
		break;
	}
	
}

echo $tpl->_ENGINE_parse_body("\n{smtpd_client_restrictions} {success} \"$datas\"\n");

	
	
	
}




function js(){
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{smtpd_client_restrictions_icon}',"postfix.index.php");
	$title2=$tpl->_ENGINE_parse_body('{PostfixAutoBlockManageFW}',"postfix.index.php");
	$title_compile=$tpl->_ENGINE_parse_body('{PostfixAutoBlockCompileFW}',"postfix.index.php");
	
	$prefix="smtpd_client_restriction";
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList_explain}');
	$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=20-{$prefix}tant;
		if(!YahooWin4Open()){return false;}
		if ({$prefix}tant < 5 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
	      } else {
				{$prefix}tant = 0;
				{$prefix}CheckProgress();
				{$prefix}demarre();                                
	   }
	}	
	
	
	function {$prefix}StartPostfixPopup(){
		YahooWin2(650,'$page?popup=yes&ou=$ou','$title');
	}
	
var x_smtpd_client_restrictions_save= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	{$prefix}StartPostfixPopup();
}		
	

	
	function PostfixAutoBlockStartCompile(){
		{$prefix}CheckProgress();
		{$prefix}demarre();       
	}
	
	var x_{$prefix}CheckProgress= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('PostfixAutoBlockCompileStatusCompile').innerHTML=tempvalue;
	}	
	
	function {$prefix}CheckProgress(){
			var XHR = new XHRConnection();
			XHR.appendData('compileCheck','yes');
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}CheckProgress);
	
	}

	
	function PostfixIptablesSearchKey(e){
			if(checkEnter(e)){
				PostfixIptablesSearch();
			}
	}

		
".js_smtpd_client_restrictions_save()."
	
	
	{$prefix}StartPostfixPopup();
	";
	echo $html;
	}
?>