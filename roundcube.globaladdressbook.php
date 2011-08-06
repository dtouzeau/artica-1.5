<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.roundcube.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.apache.inc');
	
	if(!CheckPrivs()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["RoundCubeEnableAddressbook"])){RoundCubeEnableAddressbook();exit;}
	if(isset($_GET["admins"])){admins();exit;}
	if(isset($_GET["find-members"])){admins_find();exit;}
	if(isset($_GET["SearchPattern"])){list_users();exit;}
	if(isset($_GET["admins-add"])){admins_add();exit;}
	if(isset($_GET["admins-del"])){admins_del();exit;}
	if(isset($_GET["admin-list"])){admins_list();exit;}
	
	js();
	
	
function js() {
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_ROUNDCUBE}::{global_addressbook}");
	$page=CurrentPageName();
	
	$html="
	
	YahooWin3('550','$page?popup=yes&www={$_GET["www"]}','$title');
	
	";
	
	echo $html;
}

function index(){
	
	$page=CurrentPageName();
	$www=base64_decode($_GET["www"]);
	$h=new roundcube_globaladdressbook($www);
	
	
	
	$enable=Paragraphe_switch_img("{plugin_addressbook_enable}","{global_addressbook_explain}","RoundCubeEnableAddressbook",$h->enabled,279);
	
	$html="
	<div id='RoundCubeEnableAddressbookDiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/addressbook-128.png'></td>
		<td valign='top'>
			$enable
			<br>

	</tr>
	</table>
			<table style='width:100%'>
			<tr>
				<td class=legend style='font-size:13px'>{global_addressbook_readonly}:</td>
				<td>". Field_checkbox("readonly",1,$h->readonly)."</td>
			</td>
			<tr>
				<td class=legend style='font-size:13px'>{global_addressbook_enablegroups}:</td>
				<td>". Field_checkbox("allowgroups",1,$h->allowgroups)."</td>
			</td>			
			</table>	
	</div>
	<hr>
	<div style='width:100%;text-align:right'>". button("{apply}","RoundCubeEnableAddressbookSave()")."</div>
	<script>
	
	var x_RoundCubeEnableAddressbookSave= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_roundcube_addressbook');
	}	
	
		function RoundCubeEnableAddressbookSave(){
			var XHR = new XHRConnection();
			XHR.appendData('RoundCubeEnableAddressbook',document.getElementById('RoundCubeEnableAddressbook').value);
			XHR.appendData('readonly',document.getElementById('readonly').value);
			XHR.appendData('allowgroups',document.getElementById('allowgroups').value);
			XHR.appendData('www','{$_GET["www"]}');
			document.getElementById('RoundCubeEnableAddressbookDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_RoundCubeEnableAddressbookSave);	
		}	
	
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function popup(){
	$page=CurrentPageName();
	$array["index"]='{index}';
	$array["admins"]='{admin_users}';
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&www={$_GET["www"]}\"><span>$ligne</span></a></li>\n");
		
		}
	
	
	
	echo "
	<div id=main_config_roundcube_addressbook style='width:100%;height:350px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_roundcube_addressbook').tabs({
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

function RoundCubeEnableAddressbook(){
	$www=base64_decode($_GET["www"]);
	$h=new roundcube_globaladdressbook($www);
	$h->enabled=$_GET["RoundCubeEnableAddressbook"];
	$h->readonly=$_GET["readonly"];
	$h->allowgroups=$_GET["allowgroups"];
	$h->Save();	
	
}

function admins(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add_member=$tpl->_ENGINE_parse_body("{add_member}");
	$html="<div class=explain style='margin:10px'>{global_addressbook_admin_users_text}</div>
	<div style='text-align:right'>". button("$add_member","AddGBLMember()")."</div>
	<div id='admin_users_div' style='margin:20px'></div>
	
	<script>
		function AddGBLMember(){
			YahooWin4('400','$page?find-members=yes&www={$_GET["www"]}','$add_member');
		}
		
		var x_DelGADBUser=function (obj) {
			tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			RefreshAdminsList();
			
			
	    }		
		
		function DelGADBUser(uid){
			var XHR = new XHRConnection();
			XHR.appendData('admins-del',uid);
			XHR.appendData('www','{$_GET["www"]}');
			XHR.sendAndLoad('$page', 'GET',x_DelGADBUser);
		}			
		
		function RefreshAdminsList(){
			LoadAjax('admin_users_div','$page?admin-list=yes&www={$_GET["www"]}');
		
		}
		RefreshAdminsList();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
}
	
	
function CheckPrivs(){
	$users=new usersMenus();
	if($users->AsSystemAdministrator){return true;}
	if($users->AsPostfixAdministrator){return true;}
	if($users->AsMailBoxAdministrator){return TRUE;}
	if($users->AllowChangeDomains){return true;}
	if($users->AsMessagingOrg){return true;}
	return false;
}

function admins_find(){
	$www=base64_decode($_GET["www"]);
	$tpl=new templates();
	$page=CurrentPageName();
	//MAIN_INSTANCE
	
$html="
<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{search}:</td>
		<td>". Field_text("SearchPattern",null,"font-size:13px;",null,null,null,false,"SearchUserPress(event)")."</td>
	</tr>
	</table>
	<hr>
	<div id='admins_find_list' style='width:100%;height:350px;overflow:auto'></div>
	
	<script>
	
		function SearchUserPress(e){
			if(checkEnter(e)){SearchUserPerform();}
		}	
	
	   var x_SearchUserPerform=function (obj) {
			tempvalue=obj.responseText;
			document.getElementById('admins_find_list').innerHTML=tempvalue;
	    }		
		
		function SearchUserPerform(){
			var XHR = new XHRConnection();
			XHR.appendData('SearchPattern',document.getElementById('SearchPattern').value);
			XHR.appendData('www','{$_GET["www"]}');
			document.getElementById('admins_find_list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_SearchUserPerform);
		}	
		
		var x_AddGADBUser2=function (obj) {
			tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			RefreshAdminsList();
			
			
	    }		
		
		function AddGADBUser(uid){
			var XHR = new XHRConnection();
			XHR.appendData('admins-add',uid);
			XHR.appendData('www','{$_GET["www"]}');
			XHR.sendAndLoad('$page', 'GET',x_AddGADBUser2);
		}		
		
		
		SearchUserPerform();
	</script>
	
	";	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function admins_list(){
	
	$www=base64_decode($_GET["www"]);	
	$h=new roundcube_globaladdressbook($www);
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=4>&nbsp;</th>
	</tr>
</thead>	
	


	";	
	if(is_array($h->admins)){
	while (list ($num, $ligne) = each ($h->admins) ){
		if($num==null){continue;}			
			$Displayname=$ligne;
			$js="DelGADBUser('".base64_encode($Displayname)."');";
			if(strlen($Displayname)>30){$Displayname=substr($Displayname,0,27)."...";}
			$img="winuser.png";
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/$img'></td>
		<td><strong style='font-size:13px' >$Displayname</td>
		<td width=1%>". imgtootltip("delete-32.png","{delete}",$js)."</td>
		</tr>";
	}
	
	}
	$html=$html."</table>";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
}

function list_users(){
	
	$query=$_GET["SearchPattern"];
	$ldap=new clladp();
	$page=CurrentPageName();
	
	
	$www=base64_decode($_GET["www"]);
	if($www=="MAIN_INSTANCE"){$dn="dc=organizations,$ldap->suffix";}else{
		$w=new vhosts();
		$array=$w->SearchHosts($www);
		$ou=$array["OU"];
		$dn="ou=$ou,dc=organizations,$ldap->suffix";
	}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=4>$ou- {members} $query</th>
	</tr>
</thead>	
	
	";
	
	
	$sr =@ldap_search($ldap->ldap_connection,$dn,
	"(&(objectClass=posixAccount)(|(uid=$query*)(cn=$query*)(displayName=$query*)))",array("displayname","uid"),0,10);
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					
					$displayname=$result[$i]["displayname"][0];
					$uid=$result[$i]["uid"][0];
					if(substr($uid,strlen($uid)-1,1)=='$'){continue;}
					
					
					if($displayname==null){$displayname=$uid;}
					
					$res[$uid]=$displayname;
				}
				
		}	
	if(!is_array($res)){return null;}
	krsort($res);

	while (list ($num, $ligne) = each ($res) ){
			if($num==null){continue;}	
			if(is_numeric($num)){continue;}		
			$Displayname=$ligne;
			$uid=$num;
			$js="AddGADBUser('".base64_encode($uid)."');";
			if(strlen($Displayname)>30){$Displayname=substr($Displayname,0,27)."...";}
			$img="winuser.png";
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
			$html=$html."
				<tr class=$classtr>
				<td width=1%><img src='img/$img'></td>
				<td><strong style='font-size:13px' >$Displayname ($uid)</td>
				<td width=1%>". imgtootltip("add-18.gif","{add}",$js)."</td>
				</tr>";
	}
	$html=$html."</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
}	

function admins_add(){
	if(is_numeric($_GET["admins-add"])){
		echo "{$_GET["admins-add"]}: Bad value\n";
		return;
	}
	$www=base64_decode($_GET["www"]);	
	$uid=base64_decode($_GET["admins-add"]);
	$h=new roundcube_globaladdressbook($www);
	$h->admins[$uid]=$uid;
	$h->Save();
	
	
	
}

function admins_del(){
	$www=base64_decode($_GET["www"]);	
	$uid=base64_decode($_GET["admins-del"]);
	$h=new roundcube_globaladdressbook($www);
	unset($h->admins[$uid]);
	$h->Save();	
	
}

//SearchHosts

	
	
	
?>