<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["popup-group"])){popup_group();exit;}
	if(isset($_GET["choose-group"])){groups_fields();exit;}
	if(isset($_GET["popup-members"])){group_members();exit;}
	if(isset($_GET["RuleID"])){save();exit;}
	if(isset($_GET["show-membrs"])){group_members_js();exit;}
	if(isset($_GET["delete-js"])){delete_js();exit;}
	if(isset($_GET["delete-id"])){delete_id();exit;}
	if(isset($_GET["js-groups"])){js_group();exit;}
	
table_list();	


function delete_id(){
	
	$sql="DELETE FROM dansguardian_groups WHERE ID={$_GET["delete-id"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
}

function delete_js(){
	
	$page=CurrentPageName();
	
$html="

	var x_DeleteGroupAuth= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);return;}
		RefreshDansRulesIPList();
		}		
		
		function DeleteGroupAuth(){
			var XHR = new XHRConnection();
	 		XHR.appendData('delete-id','{$_GET["delete-js"]}');
			XHR.sendAndLoad('$page', 'GET',x_DeleteGroupAuth);  			
			
		}
		
	DeleteGroupAuth();";	
	
	echo $html;
}
	
function table_list(){

	$page=CurrentPageName();
	$dansg=new dansguardian_rules(null,$rule);
	$rules=$dansg->hash_RulesList();
	$tpl=new templates();
	
	$group_legend=$tpl->_ENGINE_parse_body('{add_group}');
	$please=$tpl->_ENGINE_parse_body('{PLEASE_SELECT_GROUP_FIRST}');
	$view_members_text=$tpl->_ENGINE_parse_body('{view_members}');
	
	$html="
	
	<hr style='color:#005447'>
	<div style='width:100%;text-align:right'>
	". button("{add_group}","DansGuardianAuthAddGrp()")."</div>
	<center style='padding:10px'>
	<table style='width:99;border:2px solid #CCCCCC;padding:3px'>
	<tr>
		<th>&nbsp;</th>
		<th style='font-size:14px;font-weight:bolder'>{organization}</th>
		<th style='font-size:14px;font-weight:bolder'>{groups}</th>
		<th style='font-size:14px;font-weight:bolder'>{members}</th>
		<th style='font-size:14px;font-weight:bolder'>{rule}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
";
	
	
	$sql="SELECT * FROM dansguardian_groups ORDER BY RuleID DESC";
	$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$num=$ligne["ID"];
			$gpid=$ligne["group_id"];
			$group=new groups($gpid);
			$delete=imgtootltip("delete-32.png",'{delete}',"Loadjs('$page?delete-js=$num')");
			$dansguardian_rule=$rules[$ligne["RuleID"]];
			$members=count($group->members);
			$view_members=null;
			if($group->ou==null){$group->ou="{error}";$delete=null;}
			if($group->groupName==null){$group->groupName="{error}";$delete=null;}
			if($members>0){
				$view_members=imgtootltip("view_members-32.png","{view_members}","Loadjs('$page?show-membrs=$gpid')");
			}
			
			$html=$html . "
			<tr ". CellRollOver().">
				<td width=1%><img src='img/group-32.png'></td>
				<td style='font-size:14px;font-weight:bolder'>$group->ou</td>
				<td style='font-size:14px;font-weight:bolder'>$group->groupName</td>
				<td style='font-size:14px;font-weight:bolder' align='center'>$members</td>
				<td style='font-size:14px;font-weight:bolder'>$dansguardian_rule</td>
				<td style='font-size:14px;font-weight:bolder'>$view_members</td>
				<td width=1%>$delete</td>
			</tr>
			
			";
		
		}	
	
	$html=$html . "</table></center><hr style='color:#005447'>
	
	<script>
function DansGuardianAuthAddGrp(){
			YahooWin3(500,'$page?popup-group=yes','$group_legend');
		
		}
		
		function DansGuardianAuthChooseOrg(){
			LoadAjax('dans-groups','$page?choose-group='+document.getElementById('ou').value);
		}
		
		
	var x_DansGuardianAuthAdd= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);return;}
		LoadAjax('dansguardian_auth','$page');
		YahooWin3Hide();
		}		
		
		function DansGuardianAuthAdd(){
			if(!document.getElementById('gpid').value){
				alert('$please');
				return;
			}
			
			var gpid=document.getElementById('gpid').value;
			var RuleID=document.getElementById('RuleID').value;
			if(gpid.length==0){
				alert('$please');
				return;
			}
			if(RuleID.length==0){return;}			
			
		var XHR = new XHRConnection();
	 	XHR.appendData('gpid',gpid);
		XHR.appendData('RuleID',RuleID);
		XHR.sendAndLoad('$page', 'GET',x_DansGuardianAuthAdd);  			
			
		}
		
		function DansGuardianShowMembers(gpid){
			YahooWin3(500,'$page?popup-members='+gpid,'$view_members_text');
		}
				
	</script>
	
	
	";
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function js_group(){
	$page=CurrentPageName();
	$tpl=new templates();
	$group_legend=$tpl->_ENGINE_parse_body('{add_group}');	
$html="
		";	
		
		echo $html;
	
}

function popup_group(){
	
	$ldap=new clladp();
	$orgs=$ldap->hash_get_ou(true);
	$orgs[null]="{select}";
	$organizations=Field_array_Hash($orgs,'ou',null,"DansGuardianAuthChooseOrg()",null,0,"font-size:15px;padding:4px");
	$dansg=new dansguardian_rules();
	
	$rules=$dansg->hash_RulesList();
	$rules[null]="{select}";
	
	$rules_list=Field_array_Hash($rules,'RuleID',null,null,null,0,"font-size:15px;padding:4px");
	
	$html="
	<table style='width:99%'>
	<tr>
		<td class=legend style='font-size:15px'>{organization}:</td>
		<td>$organizations</td>
	</tr>
	<tr><td class=legend style='font-size:15px'>{groups}:</td>
	<td><span id='dans-groups'></span>
	</tr>
	<tr>
		<td class=legend style='font-size:15px'>{rule}:</td>
		<td>$rules_list</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","DansGuardianAuthAdd()")."</td>
	</tr>
	</table>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function groups_fields(){
	$ou=$_GET["choose-group"];
	if($ou==null){return null;}
	$ldap=new clladp();
	$tpl=new templates();
	$hash=$ldap->hash_groups($ou,1);
	$hash[null]=$tpl->_ENGINE_parse_body("{select}");
	$groups=Field_array_Hash($hash,'gpid',null,null,null,0,"font-size:15px;padding:4px");

	
	echo $groups;	
	
}

function save(){
	$sql="INSERT INTO dansguardian_groups(RuleID,group_id) VALUES('{$_GET["RuleID"]}','{$_GET["gpid"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?reload-dansguardian");		
	
	
}
function group_members_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$view_members=$tpl->_ENGINE_parse_body("{view_members}");
	
	
	$html="
		YahooWin3(500,'$page?popup-members={$_GET["show-membrs"]}','$view_members');	
	
	";
		echo $html;
}


function group_members(){
	$gpid=$_GET["popup-members"];
	$q=new mysql();
	
	$sql="SELECT dngroup FROM dansguardian_groups WHERE group_id='{$gpid}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(trim($ligne["dngroup"])<>null){$group=new groups($ligne["dngroup"]);}else{
	$group=new groups($gpid);}
	
	
	
	
	$html="<H1>$group->groupName</H1>
	<div style='height:300px;overflow:auto'>
	<table style='width:99%'>";
	
	while (list ($key, $member) = each ($group->members) ){
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%><img src='img/identity-32.png'></td>
			<td style='font-size:16px;padding:3px'>$member</td>
		</tr>
		
		";
	}
	
	$html=$html."</table></div>";
	echo $html;
}

	
	
?>