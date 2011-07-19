<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["freeweb-member-list"])){memberslist();exit;}
	if(isset($_POST["freeweb-member-add"])){membersAdd();exit;}
	if(isset($_POST["freeweb-member-del"])){membersDel();exit;}
	
	
	
	js();
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{authentication}&nbsp;&raquo;{members}&nbsp;&raquo;{$_GET["servername"]}");
	$html="YahooWin6('450','$page?popup=yes&servername={$_GET["servername"]}','$title')";
	echo $html;
	}
	
function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();	
	$freeweb=new freeweb($_GET["servername"]);
	if($freeweb->ou<>null){$suffix="&organization=$freeweb->ou";}
	
	$html="
	<div class=explain>{freeweb_authldap_members_explain}</div>
	<table style='width:100%'>
		<tr>
			<td class=legend>{members}</td>
			<td>".Field_text("freeweb-member-add",null,"font-size:16px;padding:3px,width:120px")."</td>
			<td><input type='button' OnClick=\"javascript:Loadjs('MembersBrowse.php?field-user=freeweb-member-add$suffix&prepend=1&prepend-guid=1');\" value='{browse}...'></td>
			<td>". button("{add}","FreeWebAuthldapAdd()")."</td>
		</tr>
	</table>
	
	<div id='freeweb-member-list' style='width:100%;height:250px;overflow:auto'></div>
	
	
	<script>
	
		var x_FreeWebAuthldapAdd=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			RefreshMembersFreeWebList();		
		}		
	
		function FreeWebAuthldapAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('freeweb-member-add',document.getElementById('freeweb-member-add').value);
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebAuthldapAdd);
		}
		
		function RefreshMembersFreeWebList(){
			LoadAjax('freeweb-member-list','$page?freeweb-member-list=yes&servername={$_GET["servername"]}');
		
		}
		
		function DeleteLDAPAUthMember(val){
			var XHR = new XHRConnection();
			XHR.appendData('freeweb-member-del',val);
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebAuthldapAdd);
		}
		
	RefreshMembersFreeWebList();
	
	</script>
	
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);

}

function membersAdd(){
	$freeweb=new freeweb($_POST["servername"]);
	$users=$freeweb->Params["LDAP"]["members"][$_POST["freeweb-member-add"]]=true;
	$freeweb->SaveParams();
}
function membersDel(){
	$freeweb=new freeweb($_POST["servername"]);
	unset($freeweb->Params["LDAP"]["members"][$_POST["freeweb-member-del"]]);
	$freeweb->SaveParams();	
}


function memberslist(){
	$freeweb=new freeweb($_GET["servername"]);
	$users=$freeweb->Params["LDAP"]["members"];
	$tpl=new templates();
	$page=CurrentPageName();

	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=3>{members}/{groups}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
if(is_array($users)){	
while (list ($num, $ligne) = each ($users) ){
		if($num==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$delete=imgtootltip("delete-32.png","{delete}","DeleteLDAPAUthMember('$num')");
		
		$gid=0;
		
		
		if(preg_match("#^group:@(.+?):([0-9]+)#",$num,$re)){
			$img="wingroup.png";
			$Displayname="{$re[1]} ({$re[2]})";
			$gid=$re[2];
		}
		if(preg_match("#^user:(.+)#",$num,$re)){
			$img="winuser.png";
			$Displayname="{$re[1]}";
		}		
$html=$html."
		<tr class=$classtr>
		<td width=1% align='center' valign='middle'><img src='img/$img'></td>
		<td><strong style='font-size:14px;text-decoration:underline' >$Displayname</td>
		<td width=1% align='center' valign='middle'>$delete</td>
		</tr>
	";
	}
}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}
