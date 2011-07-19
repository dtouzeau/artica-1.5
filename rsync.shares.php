<?php
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.rsync.inc");

$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}


if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["RsyncDaemonEnable"])){save();exit;}
if(isset($_GET["finduser-popup"])){popup_users();exit;}
if(isset($_GET["finduser"])){finduser();exit;}
if(isset($_GET["adduser"])){adduser();exit;}
if(isset($_GET["RsyncFormatUsers"])){RsyncFormatUsers();exit;}
if(isset($_GET["delete-user"])){deluser();exit;}
if(isset($_GET["ShareName"])){SaveRsyncForm();exit;}
js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_RSYNC_SERVER}');
$users=$tpl->_ENGINE_parse_body('{users}');
$share=$_GET["share-dir"];

$html="

	function RsyncSharesStart(){
		YahooWin4(363,'$page?popup=yes&dir=$share','$share');
	}
	
	
var X_SaveRsyncEnable= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin4Hide();
	}		
	
	function SaveRsyncEnable(){
		var XHR = new XHRConnection();
		if(document.getElementById('RsyncDaemonEnable')){
			XHR.appendData('RsyncDaemonEnable',document.getElementById('RsyncDaemonEnable').value);
			document.getElementById('img_RsyncDaemonEnable').src='img/wait_verybig.gif';
		}
		
				XHR.sendAndLoad('$page', 'GET',X_SaveRsyncEnable);	
	
	}
	
	function RsyncAddUser(){
		YahooWin5(400,'$page?finduser-popup=yes&dir=$share','$users')
	
	}
	
var X_RsyncFindUser= function (obj) {
	var results=obj.responseText;
	document.getElementById('RsyncFUserid').innerHTML=results;
	
	}	
	
	function RsyncFindUser(){
		var XHR = new XHRConnection();
		XHR.appendData('finduser',document.getElementById('Rsyncquery').value);
		document.getElementById('RsyncFUserid').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_RsyncFindUser);	
	}
	
	function RsyncFindUserClick(e){
		if(checkEnter(e)){RsyncFindUser();}
	}
	
	
	var X_SyncAddUserToFolder= function (obj) {
		var results=obj.responseText;
		document.getElementById('rsync_users').value=results;
		RsyncFormatUsers();
	}	
	
	var X_RsyncFormatUsers= function (obj) {
		var results=obj.responseText;
		document.getElementById('RsyncUsersList').innerHTML=results;
	}		
	
	
	function RsyncFormatUsers(){
		var rsync_users=document.getElementById('rsync_users').value;
		var XHR = new XHRConnection();
		XHR.appendData('RsyncFormatUsers',rsync_users);
		document.getElementById('RsyncUsersList').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_RsyncFormatUsers);		
	}
	
	
	
	function SyncAddUserToFolder(uid){
		var rsync_users=document.getElementById('rsync_users').value;
		var XHR = new XHRConnection();
		XHR.appendData('adduser',uid);
		XHR.appendData('rsync_users',rsync_users);
		document.getElementById('RsyncUsersList').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_SyncAddUserToFolder);		
		
	}
	
	function DeleteUser(index){
		var rsync_users=document.getElementById('rsync_users').value;
		var XHR = new XHRConnection();
		XHR.appendData('delete-user',index);
		XHR.appendData('rsync_users',rsync_users);
		document.getElementById('RsyncUsersList').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_SyncAddUserToFolder);		
	
	}
	
	var X_SaveRsyncForm= function (obj) {
		var results=obj.responseText;
		if(results.length>0){
			document.getElementById('share-rsync-id').innerHTML='';
			alert(results);
		}
		RsyncSharesStart();
	}		
	
	function SaveRsyncForm(){
		var rsync_users=document.getElementById('rsync_users').value;
		var ShareName=document.getElementById('ShareName').value;
		var XHR = new XHRConnection();
		XHR.appendData('ShareName',ShareName);
		XHR.appendData('SharePath','$share');
		XHR.appendData('rsync_users',rsync_users);
		document.getElementById('share-rsync-id').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_SaveRsyncForm);			
	
	}
	
	RsyncSharesStart();";
	
	echo $html;

}

function SaveRsyncForm(){
	$array=unserialize(base64_decode($_GET["rsync_users"]));
	if(is_array($array)){
		while (list ($index, $uid) = each ($array) ){
			if($uid==null){continue;}
			$hash[$uid]=$uid;
		}
		if(is_array($hash)){
			while (list ($account, $user) = each ($hash) ){
				$final_array[]=$user;
			}
		}
	}
	
	
	$rsync=new rsyncd_conf();
	if($_GET["ShareName"]==null){$_GET["ShareName"]=basename($_GET["SharePath"]);}
	$rsync->main_array[$_GET["SharePath"]]["NAME"]=$_GET["ShareName"];
	if(is_array($final_array)){
		$rsync->main_array[$_GET["SharePath"]]["auth users"]=implode(",",$final_array);
	}else{
		$rsync->main_array[$_GET["SharePath"]]["auth users"]=null;
	}
	
	$rsync->save();
	
	
}




function popup(){
		$path=$_GET["dir"];	
		$rsync=new rsyncd_conf();
		$users=$rsync->main_array[$path]["auth users"];
		$users=base64_encode(serialize(explode(",",$users)));
		
		if($rsync->main_array[$path]["NAME"]==null){$rsync->main_array[$path]["NAME"]=basename($path);}
		
		// folder-granted-remove-rsync-48.png
		$html="
		<H1>{SHARE_RSYNC}</H1>
		<div id='share-rsync-id'></div>
		<table style='width:100%'>
		<tr>
			<td class=legend>{share_name}:</td>
			<td>". Field_text("ShareName",$rsync->main_array[$path]["NAME"],'width:240px;font-size:13px;padding:3px')."</td>
		</tr>	
		<tr>
			<td colspan=2 align='right'>
				<hr>
					". button("{apply}","SaveRsyncForm()")."
			</td>
		</tr>
					
		</table>
		<hr>
		<H3>{users}</H3>
		<input type='hidden' id='rsync_users' value='$users'>
		<div id='RsyncUsersList' style='width:100%;height:200px;overflow:auto;padding:3px;border:1px solid #CCCCCC'></div>
		<div style='text-align:right'>". button("{add_user}","RsyncAddUser()")."</div>
		<script>
			RsyncFormatUsers();
		</script>
		";
		
		
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		
	
}

function RsyncFormatUsers(){
	$array=unserialize(base64_decode($_GET["RsyncFormatUsers"]));
	if(!is_array($array)){return null;}
	

	while (list ($index, $uid) = each ($array) ){
			if($uid==null){continue;}
			$Displayname=$ligne;
			if($Displayname==null){$Displayname=$uid;}
			$img="winuser.png";
			$js="SyncAddUserToFolder('$uid')";
			
			if(substr($num,strlen($num)-1,1)=='$'){continue;}
			$html=$html."<table>
					<tr ". CellRollOver().">
					<td width=1%><img src='img/$img'></td>
					<td ><strong style='font-size:11px' >$Displayname</td>
					<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DeleteUser($index)")."</td>
					</tr>
					</table>";
	}		
		
		
		
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_users(){
	$form1="
	<table style='width:100%'>
	<td>" . Field_text('Rsyncquery',null,'width:100%',null,null,null,null,"RsyncFindUserClick(event);")."</td>
	<td align='right'>
	". button("{search}","RsyncFindUser()")."
	</td>
	</table>";
	
	$form1="<div style='width:350px'>$form1</div><br>";
	$form2="<div style='padding:5px;border:1px solid #CCCCCC;width:340px;background-color:white;height:200px;overflow:auto' id='RsyncFUserid'></div>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$form1$form2");	
	
	
	
}

function adduser(){
	$users_list=unserialize(base64_decode($_GET["rsync_users"]));
	$users_list[]=$_GET["adduser"];
	echo base64_encode(serialize($users_list));
	}
	
function deluser(){
	$users_list=unserialize(base64_decode($_GET["rsync_users"]));	
	unset($users_list[$_GET["delete-user"]]);
	echo base64_encode(serialize($users_list));
}

function finduser(){
		$users=$_GET["finduser"];
		$ldap=new clladp();
		writelogs("(&(objectClass=userAccount)(uid=$users*))",__FUNCTION__,__FILE__,__LINE__);
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=userAccount)(|(uid=$users*)(cn=$users*)(displayName=$users*)))",array("displayname","uid"));
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				
				for($i=0;$i<$result["count"];$i++){
					$displayname=$result[$i]["displayname"][0];
					$uid=$result[$i]["uid"][0];
					if($displayname==null){$displayname=$uid;}
					$res[$uid]=$displayname;
				}
				
		}
		if(!is_array($res)){return null;}

	while (list ($uid, $ligne) = each ($res) ){
		
			$Displayname=$ligne;
			if($Displayname==null){$Displayname=$uid;}
			$img="winuser.png";
			$js="SyncAddUserToFolder('$uid')";
			
			if(substr($uid,strlen($uid)-1,1)=='$'){continue;}
			$html=$html."<table>
					<tr>
					<td width=1%><img src='img/$img'></td>
					<td 
					onMouseOver=\"this.style.background='#CCCCCC';this.style.cursor='pointer'\" 
					OnMouseOut=\"this.style.background='transparent';this.style.cursor='default'\"
					OnClick=\"javascript:$js;\"
					><strong style='font-size:11px' >$Displayname</td>
					</tr>
					</table>";
	}		
		
		
		
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

?>