<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.user.inc');
	
	//if(count($_POST)>0)
	$usersmenus=new usersMenus();
	if(!$usersmenus->AllowAddUsers){
		writelogs("Wrong account : no AllowAddUsers privileges",__FUNCTION__,__FILE__);
		if(isset($_GET["js"])){
			$tpl=new templates();
			$error="{ERROR_NO_PRIVS}";
			echo $tpl->_ENGINE_parse_body("alert('$error')");
			die();
		}
		header("location:domains.manage.org.index.php?ou={$_GET["ou"]}");
		}
		
		if(isset($_GET["popup"])){popup();exit;}
		if(isset($_GET["list"])){popup_list();exit;}
		if(isset($_GET["add_already_member_add"])){popup_add();exit;}
		js();
		
function js(){
	$ou_decoded=base64_decode($_GET["ou"]);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{add_already_member}");
	$members=$tpl->_ENGINE_parse_body("{members}");
	$page=CurrentPageName();
	$html="
		function add_already_member_load(){
			YahooWin2('550','$page?popup=yes&ou={$_GET["ou"]}&gpid={$_GET["gpid"]}','$title');
		
		}
	

		
		
	var x_add_already_member_add= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			add_already_member_list_refresh();
			RefreshTab('main_group_config');	
		}		
		
		function add_already_member_add(uid_encoded){
		  var XHR = new XHRConnection();
	      XHR.appendData('add_already_member_add',uid_encoded);
	      XHR.appendData('gpid','{$_GET["gpid"]}');
	      XHR.appendData('ou','{$_GET["ou"]}');
	      document.getElementById('already_member_list_{$_GET["gpid"]}').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	      if(document.getElementById('members_area')){
	      	document.getElementById('members_area').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		  }
		XHR.sendAndLoad('$page', 'GET',x_add_already_member_add);	
		}
		
		
		add_already_member_load();";
		
		
		echo $html;
	
	
}

function popup_add(){
	$uid=base64_decode($_GET["add_already_member_add"]);
	$group=new groups($_GET["gpid"]);
	$group->AddUsertoThisGroup($uid);
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	
<center>
<table style='width:95%' class=form>
<tr>
	<td  class=legend>{members}:</td>
	<td>". Field_text("members_import_search",null,"font-size:13px;padding:3px",null,null,null,false,"MembersImportWearchCheck(event)")."</td>
	<td width=1%>". button("{search}","add_already_member_list_refresh()")."</td>
</tr>
</table>
</center>	
	
	<div id='already_member_list_{$_GET["gpid"]}' style='height:300px;overflow:auto'></div>
	
	
	<script>
		function MembersImportWearchCheck(e){
			if(checkEnter(e)){add_already_member_list_refresh();return;}
			
		}
	
	
		function add_already_member_list_refresh(){
			var se=escape(document.getElementById('members_import_search').value);
			LoadAjax('already_member_list_{$_GET["gpid"]}','$page?list=yes&ou={$_GET["ou"]}&gpid={$_GET["gpid"]}&search='+se);
		}
	
	
		add_already_member_list_refresh();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function popup_list(){
	$ou=base64_decode($_GET["ou"]);
	$gpid=$_GET["gpid"];
	$tofind=$_GET["search"];
	$groups=new groups($gpid);
	$group_members=$groups->members_array;
	
	$ldap=new clladp();
	if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
	$filter="(&(objectClass=userAccount)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(uid=$tofind) (givenname=$tofind)))";
	$attrs=array("displayName","uid","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber");
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs,150);
	if(strlen($ldap->ldap_last_error)<>5){$error="<H2>$ldap->ldap_last_error</H2>";}
	
	
	$number=$hash["count"];
	
	

	
	$html="$error
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th width=1%>". imgtootltip("refresh-24.png","{refresh}","add_already_member_list_refresh()")."</th>
	<th colspan=3>{members}:$ou</th>
	</tr>
</thead>
<tbody class='tbody'>";

		
		$classtr=null;
	for($i=0;$i<$number;$i++){
		$userARR=$hash[$i];
		$exist=false;
		
		$uid=$userARR["uid"][0];
		if($uid=="squidinternalauth"){continue;}
		if($group_members[$uid]){$exist=true;}
		$js="add_already_member_add('".base64_encode($uid)."');";
		$sn=texttooltip($userARR["sn"][0],"{add}:$uid",$js,null,0,"font-size:13px");
		$givenname=texttooltip($userARR["givenname"][0],"{add}:$uid",$js,null,0,"font-size:13px");
		$title=texttooltip($userARR["title"][0],"{add}:$uid",$js,null,0,"font-size:13px");
		$mail=texttooltip($userARR["mail"][0],"{add}:$uid",$js,null,0,"font-size:13px");
		$telephonenumber=texttooltip($userARR["telephonenumber"][0],"{add}:$uid",$js,null,0,"font-size:13px");
		$color="black";
		$add=imgtootltip("32-plus.png","{add}:$uid",$js);
		if($exist){$add="&nbsp;";$color="#CCCCCC";}
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html ."
		<tr class=$classtr>
			<td width=1%><img src='img/contact-32.png'></td>
			<td style='font-size:14px;color:$color'>$uid</td>
			<td style='font-size:14px;color:$color'>$mail</td>
			<td style='font-size:14px;color:$color'>$add</td>
			
		</tr>
		";
		
	}
	$html=$html ."</table>";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
	
}



?>