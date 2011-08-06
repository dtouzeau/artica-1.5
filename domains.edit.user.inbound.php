<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');	
	
	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	if(!GetRights_aliases2()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["RecipientToAdd"])){SAVE();exit;}
	if(isset($_GET["usermap"])){usermap();exit;}
	if(isset($_GET["RedirectDelete"])){REDIRECT_DELETE();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{inbound_parameters}");
	
	$html="
	function USER_RECIPT_LOAD(){
		YahooWin3(650,'$page?userid={$_GET["userid"]}&popup=yes','$title');
	}
	
	function RecipientToAddCheck(e){if(checkEnter(e)){RecipientToAdd();}}
	
var x_RecipientToAdd= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	USER_RECIPT_LOAD();
}

function RecipientToAdd(){
	var UserID='{$_GET["userid"]}';
	var RecipientToAdd=document.getElementById('RecipientToAdd').value;
	
	document.getElementById('RecipientToAddID').innerHTML='<center style=width:100%><img src=img/wait_verybig.gif></center>';
	var XHR = new XHRConnection();
	XHR.appendData('RecipientToAdd',RecipientToAdd);
	XHR.appendData('MailAlternateAddress',document.getElementById('MailAlternateAddress').value);
	XHR.appendData('redirect_messages_to',document.getElementById('redirect_messages_to').value);
	XHR.appendData('uid',UserID);
	XHR.sendAndLoad('$page', 'GET',x_RecipientToAdd);	
	
}
	function HideRedirect(){document.getElementById('redirect_messages_to').disabled=true;}
	
	function RedirectDelete(id){
		var XHR = new XHRConnection();
		XHR.appendData('RedirectDelete',id);
		XHR.sendAndLoad('$page', 'GET',x_RecipientToAdd);		
	
	}


	USER_RECIPT_LOAD();
	";
	echo $html;
}
	

function popup(){
			
			$page=CurrentPageName();
			writelogs("USER_ACCOUNT::{$_GET["userid"]}",__FUNCTION__,__FILE__,__LINE__);	
			$user=new user($_GET["userid"]);
			$ou=$user->ou;
			$priv=new usersMenus();
			$sock=new sockets();
			$recipient_bcc_maps_text='{recipient_bcc_maps_text}';
			
			$ArticaFilterEnableRedirect=$sock->GET_INFO("ArticaFilterEnableRedirect");
			
	
	$styleTDRight="style='padding:5px;font-size:13px'";
	$styleTDLeft="style='padding:5px;font-size:11px'";			
			
		
	if($ArticaFilterEnableRedirect<>1){
		$jsRedirect="HideRedirect();";
	}

	$html="
		
		<table style='width:100%'>
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{duplicate_mailto}:</strong>
			<td $styleTDLeft>" . Field_text('RecipientToAdd',$user->RecipientToAdd[0],"padding:3px;font-size:13px",null,null,null,false,"RecipientToAddCheck(event)")."</td>
			<td width=1%>" . help_icon("{duplicate_mailto_text}")."</td>
		</tr>
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{transforme_mailto}:</strong>
			<td $styleTDLeft>" . Field_text('MailAlternateAddress',$user->MailAlternateAddress,
			"padding:3px;font-size:13px",null,null,null,false,"RecipientToAddCheck(event)")."</td>
			<td>" . help_icon("{transforme_mailto_text}")."</td>
		</tr>	
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{redirect_messages}:</strong>
			<td $styleTDLeft>" . Field_text('redirect_messages_to',null,
			"padding:3px;font-size:13px",null,null,null,false,"RecipientToAddCheck(event)")."</td>
			<td>" . help_icon("{redirect_messages_text}")."</td>
		</tr>	
		
		</table>
		<br>
		<div class=explain>$recipient_bcc_maps_text</div>
		<div style='width:100%;height:150px;overflow:auto' id='RecipientToAddID'></div>
		
		
		
		<script>
			
		
			$jsRedirect
			LoadAjax('RecipientToAddID','$page?usermap=yes&userid={$_GET["userid"]}');
			
		</script>
		";
	

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function usermap(){
	$user=new user($_GET["userid"]);
	
	$end1="<td width=1% align='center'><img src='img/user-48.png'></td>";
	$end11="<td align='center' style='font-size:13px'>$user->mail</td>";	
	
	if($user->RecipientToAdd[0]<>null){
		$spy="<td width=1% align='center'><img src='img/user-spy-48.png'></td>
		<td width=1% align='center'><img src='img/arrow-right-64.png'></td>";
		$spyend="<td align='center' style='font-size:13px'>{$user->RecipientToAdd[0]}</td>
		<td>&nbsp;</td>";
	}
	
	if($user->MailAlternateAddress<>null){
		$end1="<td width=1% align='center'><img src='img/user-black-48.png'></td>";
		$end11="<td align='center' style='font-size:13px'>{$user->MailAlternateAddress}</td>";		
	}
	
	$sock=new sockets();
	$ArticaFilterEnableRedirect=$sock->GET_INFO("ArticaFilterEnableRedirect");	
	
	if($ArticaFilterEnableRedirect==1){
		$redirects=REDIRECT_QUERIES($user->mail);
		if($redirects<>null){$end11="<td align='center' style='font-size:13px'>$redirects</td>";}
	}
	
	
	if(is_array($user->HASH_ALL_MAILS)){
		
	}
	
	$html="
	<hr>
	<table style='width:100%'>
	<tr>
		<td width=1% align='center'><img src='img/user-48.png'></td>
		<td width=1% align='center'><img src='img/arrow-right-64.png'></td>
		$spy
		
		$end1
	</tr>
	<tr>
		<td align='center' style='font-size:13px'>".@implode(", ",$user->HASH_ALL_MAILS)."</td>
		<td>&nbsp;</td>
		$spyend
		$end11
	</tr>
	</table>
	<hr>
	";
	
	echo $html;
	
	
	
}

function REDIRECT_QUERIES($mail){
		$sql="SELECT `id`,`mail` FROM `redirectmail` WHERE `from`='$mail' AND `del`=1 LIMIT 0,10";
		$sock=new sockets();
		$ArticaFilterRedirectExternalSQL=$sock->GET_INFO("ArticaFilterRedirectExternalSQL");
	
	if($ArticaFilterRedirectExternalSQL==1){
		$array=unserialize(base64_decode($sock->GET_INFO("ArticaFilterRedirectExternalSQLDatas")));
		$bd=@mysql_connect($array["mysql_servername"],$array["mysql_username"],$array["password"]);
		$ok=@mysql_select_db($array["mysql_database"]);
		if(!$ok){
			$des=mysql_error();
			echo "<span style='color:red'>redirect: $des L.".__LINE__."</span>";
			@mysql_close($bd);
			return false;
		}
		$results=mysql_query($sql);
		if(mysql_error()){$des=mysql_error();@mysql_close($bd);echo "<span style='color:red'>redirect: $des L.".__LINE__."</span>";return false;}
	}else{
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo "<span style='color:red'>redirect: $des L.".__LINE__."</span>";
			@mysql_close($q->mysql_connection);
			return false;
		}
	}
	
	$redirected=false;
	$mailFromRedirect=$recipient;
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$mailToRedirect=strtolower(trim($ligne["mail"]));
		if($mailToRedirect==null){continue;}
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%><img src='img/identity-24.png'></td>
			<td><span style='font-size:12px'>{redirected}:$mailToRedirect</span></td>
			<td>". imgtootltip("ed_delete.gif","{delete}","RedirectDelete({$ligne["id"]})")."</td>
		</tr>
		";
		$redirected=true;
	}
	
	if(!$redirected){return null;}
	
	return "<table>$html</table>";
	
}



function GetRights_aliases2(){
	$usersprivs=new usersMenus();
	if($usersprivs->AsSystemAdministrator){return true;}
	if($usersprivs->AsAnAdministratorGeneric){return true;}
	if($usersprivs->AllowEditOuSecurity){return true;}
	if($usersprivs->AsMessagingOrg){return true;}
	if($usersprivs->AllowEditAliases){return true;}
	return false;
}

function ADD_REDIRECT_MESSAGES($uid,$email){
	$ct=new user($_GET["uid"]);
	$sock=new sockets();
	$ArticaFilterEnableRedirect=$sock->GET_INFO("ArticaFilterEnableRedirect");
	if($ArticaFilterEnableRedirect<>1){return;}
	$_GET["redirect_messages_to"]=trim($_GET["redirect_messages_to"]);
	
	$ArticaFilterRedirectExternalSQL=$sock->GET_INFO("ArticaFilterRedirectExternalSQL");	
	$mail=$ct->mail;
	$sql="INSERT INTO `redirectmail` (`mail`,`from`,`del`) VALUES('{$_GET["redirect_messages_to"]}','$mail','1')";
	
if($ArticaFilterRedirectExternalSQL==1){
		$array=unserialize(base64_decode($sock->GET_INFO("ArticaFilterRedirectExternalSQLDatas")));
		$bd=@mysql_connect($array["mysql_servername"],$array["mysql_username"],$array["password"]);
		$ok=@mysql_select_db($array["mysql_database"]);
		if(!$ok){
			echo __FUNCTION__."\n".mysql_error()."\nLine: ".__LINE__;
			@mysql_close($bd);
			return false;
		}
		$results=mysql_query($sql);
		if(mysql_error()){
			echo __FUNCTION__."\n".mysql_error()."\nLine: ".__LINE__;
			return false;
		}
	}else{
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo __FUNCTION__."\n$q->mysql_error\nLine: ".__LINE__;
			@mysql_close($q->mysql_connection);
			return false;
		}
	}	
	
	
}

function REDIRECT_DELETE(){
	$sock=new sockets();
	$ArticaFilterRedirectExternalSQL=$sock->GET_INFO("ArticaFilterRedirectExternalSQL");	
	if(!is_numeric($_GET["RedirectDelete"])){return null;}

	$sql="DELETE FROM `redirectmail` WHERE id={$_GET["RedirectDelete"]}";
	
if($ArticaFilterRedirectExternalSQL==1){
		$array=unserialize(base64_decode($sock->GET_INFO("ArticaFilterRedirectExternalSQLDatas")));
		$bd=@mysql_connect($array["mysql_servername"],$array["mysql_username"],$array["password"]);
		$ok=@mysql_select_db($array["mysql_database"]);
		if(!$ok){
			echo __FUNCTION__."\n".mysql_error()."\nLine: ".__LINE__;
			@mysql_close($bd);
			return false;
		}
		$results=mysql_query($sql);
		if(mysql_error()){
			echo __FUNCTION__."\n".mysql_error()."\nLine: ".__LINE__;
			return false;
		}
	}else{
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo __FUNCTION__."\n$q->mysql_error\nLine: ".__LINE__;
			@mysql_close($q->mysql_connection);
			return false;
		}
	}	



}

function SAVE(){
	$RecipientToAdd=trim($_GET["RecipientToAdd"]);
	$MailAlternateAddress=trim($_GET["MailAlternateAddress"]);
	
	$ct=new user($_GET["uid"]);
	if($email==null){$ct->del_all_bcc();}
	
	
	if($_GET["redirect_messages_to"]<>null){
		ADD_REDIRECT_MESSAGES($_GET["uid"],$_GET["redirect_messages_to"]);
	}
	
	
	if($RecipientToAdd<>null){
		if(!preg_match("#(.+?)@(.+)#",$RecipientToAdd)){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{ERROR_INVALID_EMAIL_ADDR}:'.$RecipientToAdd);
		exit;
		}
		$ct=new user($_GET["uid"]);
		$ct->add_bcc($_GET["RecipientToAdd"]);
	}
	
	if($MailAlternateAddress<>null){
	if(!preg_match("#(.+?)@(.+)#",$MailAlternateAddress)){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{ERROR_INVALID_EMAIL_ADDR}:'.$MailAlternateAddress);
		exit;
	}	

	
	}
	
	$ct->MailAlternateAddress=$MailAlternateAddress;
	$ct->SaveMailAlternateAddress();	
	
	}
	
	

?>