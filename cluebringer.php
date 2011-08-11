<?php
	if(posix_getuid()==0){die();}
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');

	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["service-status"])){status_service();exit;}
		
	if(isset($_GET["CLUEBRINGER_SAVE"])){CLUEBRINGER_SAVE();exit;}
	
	if(isset($_GET["members"])){members();exit;}
	if(isset($_GET["remote-users"])){remote_users();exit;}
	if(isset($_GET["local-users"])){local_users();exit;}
	if(isset($_GET["member-add"])){members_add();exit;}
	if(isset($_GET["member-delete"])){members_delete();exit;}		
	
	
js();

function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_CLUEBRINGER}");
	$page=CurrentPageName();
	$html="YahooWin3('720','$page?popup=yes','$title')";
	echo $html;
	
}	
function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["status"]='{status}';
	$array["members"]='{members}';

	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_cluebringer style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_cluebringer').tabs();
			
			
			});
		</script>";			
}

function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	
$perfs[0]="{small_server}";
$perfs[1]="{medium_server}";	
$perfs[2]="{large_server}";

$sock=new sockets();
$conf=unserialize(base64_decode($sock->GET_INFO("ClubringerMasterConf")));

if($conf["perfs"]==null){$conf["perfs"]=1;}

$p=Paragraphe("folder-interface-64.png","{web_interface}","{access_to_web_administration_interface}",
"javascript:s_PopUp('cluebringer/index.php','800','800')",null,310);
	
# Small mailserver:  2, 2, 4, 10, 1000
# Medium mailserver: 4, 4, 12, 25, 1000
# Large mailserver: 8, 8, 16, 64, 1000	
	
$html="
<div id='CLUEBRINGER_ID'>
<table style='width:100%'>
<tr>
	<td valign='top' width=50%>
		<span id='CLUEBRINGER_STATUS'></span>
		<div style='text-align:right'>". imgtootltip("refresh-32.png","{refresh}","REFRESH_CLUEBRINGER_STATUS()")."</div>	
		<br>
		$p
	</td>
	<td valign='top'>
		<table style='width:100%'>
			<tr>
				<td class=legend>{performances}:</td>
				<td>". Field_array_Hash($perfs,"perfs",$conf["perfs"],null,null,0,"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","CLUEBRINGER_SAVE()")."</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</div>
<script>
	
	function REFRESH_CLUEBRINGER_STATUS(){
		LoadAjax('CLUEBRINGER_STATUS','$page?service-status=yes');
		}
	
	
	
	var X_CLUEBRINGER_SAVE=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      RefreshTab('main_config_cluebringer');
      }	
	
	function CLUEBRINGER_SAVE(){
		var XHR = new XHRConnection();
		XHR.appendData('CLUEBRINGER_SAVE',1);
		XHR.appendData('perfs',document.getElementById('perfs').value);
		document.getElementById('CLUEBRINGER_ID').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_CLUEBRINGER_SAVE);		
		}	
	REFRESH_CLUEBRINGER_STATUS();
</script>
"	;
	echo $tpl->_ENGINE_parse_body($html);
}

function CLUEBRINGER_SAVE(){
	$sock=new sockets();
	$conf=unserialize(base64_decode($sock->GET_INFO("ClubringerMasterConf")));	
	while (list ($num, $ligne) = each ($_GET) ){$conf[$num]=$ligne;}
	$sock->SaveConfigFile(base64_encode(serialize($conf)),"ClubringerMasterConf");
	$sock->getFrameWork("cmd.php?cluebringer-restart=yes");
}


function status_service(){
	$tpl=new templates();
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?cluebringer-ini-status=yes')));
	$status=DAEMON_STATUS_ROUND("APP_CLUEBRINGER",$ini,null,1);
	echo $tpl->_ENGINE_parse_body($status);
}

function members_add(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("ClueBringerMembers")));
	$array[$_GET["member-add"]]=$_GET["member-add"];
	$sock->SaveConfigFile(base64_encode(serialize($array)),"ClueBringerMembers");
	$sock->getFrameWork("cmd.php?cluebringer-passwords=yes");
	
}

function members_delete(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("ClueBringerMembers")));
	unset($array[$_GET["member-delete"]]);
	$sock->SaveConfigFile(base64_encode(serialize($array)),"ClueBringerMembers");
	$sock->getFrameWork("cmd.php?cluebringer-passwords=yes");		
}


function members(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$users=new usersMenus();
	$sock=new sockets();
	if(!$users->ARTICA_META_ENABLED){
		$createUser=imgtootltip("identity-add-48.png","{add user explain}","Loadjs('create-user.php');");
	}else{
		if($sock->GET_INFO("AllowArticaMetaAddUsers")==1){
			$createUser=imgtootltip("identity-add-48.png","{add user explain}","Loadjs('create-user.php');");
		}
	}
	$html="
	<div class=explain>{CLUEBRINGER_MEMBERS_EXPLAIN}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=50%>
		<center>". Field_text("local_user_search",null,"font-size:13px;padding:3px",null,null,null,false,"SearchLocalUserEnter(event)")."</center>
		<hr>
		<div style='width:100%;height:300px;overflow:auto' id='local-users'></div>
		<div style='text-align:right;width:100%;padding-top:5px;border-top:1px solid #CCCCCC'>$createUser</div>
	</td>
	<td valign='top'  width=50%>
		
		<div style='width:100%;height:300px;overflow:auto' id='remote-users'></div>
	</td>
	</tr>
	<script>
		function refresh_remote_users(){
			LoadAjax('remote-users','$page?remote-users=yes');
		}
		
		function RefreshLocalMember(){
			var search=escape(document.getElementById('local_user_search').value);
			LoadAjax('local-users','$page?local-users='+search);
		}
		
		function SearchLocalUserEnter(e){
			if(checkEnter(e)){RefreshLocalMember();}
		}
		
		refresh_remote_users();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function remote_users(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ip_address=$tpl->_ENGINE_parse_body("{ip_address}");		
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("ClueBringerMembers")));
	$ldap=new clladp();
	$array[$ldap->ldap_admin]=$ldap->ldap_admin;
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{cluebringer_access}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	if(is_array($array)){
		while (list ($uid, $conf) = each ($array) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ct=new user($uid);
			$delete=imgtootltip("delete-32.png","{delete}","RemoteDelMember('$uid')");
			$js=MEMBER_JS($uid,1,1);
			if($uid==$ldap->ldap_admin){$delete="&nbsp;";$js=null;}
			$img=imgtootltip("contact-48.png","{view}",$js);

			$html=$html."
			<tr class=$classtr>
			<td width=1%>$img</td>
			<td><strong style='font-size:14px'>$ct->DisplayName</td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
	}
	
	$html=$html."</tbody></table>
	<script>
		RefreshLocalMember();
		
	var x_RemoteDelMember= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			refresh_remote_users();
		}
				
		function RemoteDelMember(uid){
			var XHR = new XHRConnection();
			XHR.appendData('member-delete',uid);	
			document.getElementById('remote-users').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_RemoteDelMember);	
		}

		
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function local_users(){
	$stringtofind=$_GET["local-users"];
	$ldap=new clladp();
	$page=CurrentPageName();
	$tpl=new templates();		
	//if($stringtofind==null){$stringtofind="*";}
	$hash=$ldap->UserSearch(null,$stringtofind);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("ClueBringerMembers")));	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=4>{members}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	for($i=0;$i<$hash[0]["count"];$i++){
		$ligne=$hash[0][$i];
		$uid=$ligne["uid"][0];
		if($uid==null){continue;}
		if($uid=="squidinternalauth"){continue;}
		if($array[$uid]<>null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ct=new user($uid);
			$js=MEMBER_JS($uid,1,1);
			$img=imgtootltip("contact-48.png","{view}",$js);
			$add=imgtootltip("plus-24.png","{add}","SargAddMember('$uid')");
			$html=$html."
			<tr class=$classtr>
			<td width=1%>$img</td>
			<td><strong style='font-size:14px'>$ct->DisplayName</td>
			<td width=1%>$add</td>
			</tr>
			";		
		
	}
	$html=$html."</tbody></table>
	<script>
	
	var x_SargAddMember= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			refresh_remote_users();
	}		
		function SargAddMember(uid){
			var XHR = new XHRConnection();
			XHR.appendData('member-add',uid);	
			document.getElementById('remote-users').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SargAddMember);	
		}
		
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}