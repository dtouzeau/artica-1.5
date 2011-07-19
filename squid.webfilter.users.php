<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
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
	if(isset($_GET["add-ldap-group"])){save_group();exit;}
	if(isset($_GET["popup-index"])){popup_index();exit;}
	if(isset($_GET["table"])){table_list();exit;}
	if(isset($_GET["add-ip-addr"])){table_add_ips();exit;}
	if(isset($_GET["del-ip-addr"])){table_del_ips();exit;}
	if(isset($_GET["popup-group"])){popup_group();exit;}
	if(isset($_GET["choose-group"])){groups_fields();exit;}
	
	
popup();

function popup(){
	$page=CurrentPageName();
	$html="<div id='mainDansRulesPanel'></div>
	
	<script>
		function ResfreshMainDansRulesPanel(){
			LoadAjax('mainDansRulesPanel','$page?popup-index=yes')
		}
		ResfreshMainDansRulesPanel();
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function popup_index(){
	$page=CurrentPageName();
	$dans2=new dansguardian($hostname);
	$rules=$dans2->RulesList;
	$tpl=new templates();
	$rules[null]="{select}";
	
	$select_a_rule_first=$tpl->javascript_parse_text("{select_a_rule_first}");
	$squidguard_add_ip_howto=$tpl->javascript_parse_text("{squidguard_add_ip_howto}");
	$group_legend=$tpl->_ENGINE_parse_body('{add_group}');
	
	$html="<H3>{MAP_USERS_RULES}</h3><div style='float:right'>". help_icon("{filter_ip_group_explain}")."</div><div class=explain>{MAP_USERS_RULES_DANSGUARDIAN_TEXT}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' width=1% nowrap>{rules}:</td>
		<td width=1% nowrap>".Field_array_Hash($rules,"auth-rules",$_COOKIE["RefreshDansRulesIPList"],"RefreshDansRulesIPListSelected()",null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	</table>
	<hr>
	<div id='table-ip-list-dans' style='width:100%;height:450px;overflow:auto'></div>
	
	<div style='text-align:right'>". imgtootltip("refresh-32.png","{refresh}","ResfreshMainDansRulesPanel()")."</div>
	
	
	<script>
	
		function RefreshDansRulesIPListSelected(){
			var selected=escape(document.getElementById('auth-rules').value);
			Set_Cookie('RefreshDansRulesIPList', document.getElementById('auth-rules').value, '3600', '/', '', '');
			if(document.getElementById('table-ip-list-dans')){ LoadAjax('table-ip-list-dans','$page?table=yes&rule='+selected);}		
		}	

		RefreshDansRulesIPListSelected();
	</script>
	";
	

	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function table_list(){
	$rule=$_GET["rule"];
	$dans2=new dansguardian($hostname);
	$rules=$dans2->RulesList;	
	$page=CurrentPageName();
	$tpl=new templates();
	
	if($rule<2){
		echo $tpl->_ENGINE_parse_body("<center style='margin:15px'><H3>{DANSDEFAULT_RULE_NO_IP}</H3></center>");
		return;
	}
	
$html="
<input type='hidden' id='auth-rules' value='$rule'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=4>{filter_ip_group}:: {$rules[$rule]}</th>
	</tr>
</thead>
<thead class='thead'>
	<tr>
	<th colspan=2>{awstats_statistics_allhosts}</th>
	<th colspan=2>&nbsp;</th>
	</tr>
</thead>";	


	$sql="SELECT * FROM dansguardian_ipgroups WHERE RuleID=$rule ORDER BY ID DESC";
	
	$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$num=$ligne["ID"];
			$val=$ligne["pattern"];
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$delete=imgtootltip("delete-24.png",'{delete}',"ip_dans_delete('$rule','$num')");
			$html=$html . "
			<tr class=$classtr>
				<td width=1%><img src='img/folder-network-32.png'></td>
				<td><code style='font-size:13px;font-weight:bold'>$val</code></td>
				<td>&nbsp;</td>
				<td width=1%>$delete</td>
			</tr>
			
			";
		
		}	
		
		
$html=$html . "
<thead class='thead'>
	<tr>
	<th colspan=2>{groups}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
</thead>";	

$sql="SELECT * FROM dansguardian_groups WHERE RuleID=$rule";
	$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$num=$ligne["ID"];
			$gpid=$ligne["group_id"];
			$dngroup=trim($ligne["dngroup"]);
			writelogs("$num) Loading group gpid:$gpid DN:\"$dngroup\"",__FUNCTION__,__FILE__,__LINE__);

			if($dngroup<>null){
				$group=new groups($dngroup);
			}else{$group=new groups($gpid);}
			
			$delete=imgtootltip("delete-32.png",'{delete}',"Loadjs('dansguardian.groups.auth.php?delete-js=$num')");
			$dansguardian_rule=$rules[$ligne["RuleID"]];
			$members=count($group->members);
			$view_members=null;
			if($group->ou==null){continue;}
			if($group->groupName==null){$group->groupName="{error}";$delete=null;}
			if($members>0){
				$view_members=imgtootltip("view_members-32.png","{view_members}","Loadjs('dansguardian.groups.auth.php?show-membrs=$gpid')");
			}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html . "
			<tr class=$classtr>
				<td width=1%><img src='img/group-32.png'></td>
				<td style='font-size:14px;font-weight:bolder'>$group->groupName ($group->ou) $members {members}</td>
				<td style='font-size:14px;font-weight:bolder'>$view_members</td>
				<td width=1%>$delete</td>
			</tr>
			
			";
		
		}	

	$select_a_rule_first=$tpl->javascript_parse_text("{select_a_rule_first}");
	$squidguard_add_ip_howto=$tpl->javascript_parse_text("{squidguard_add_ip_howto}");
	$group_legend=$tpl->_ENGINE_parse_body('{add_group}');
	
	$html=$html . "
	<tr>
		<td colspan=4 align='center'>
			<table>
			<tr>
				<td width=50% nowrap>". button("{add_new_address}","AddIpPopupDans()")."</td>
				<td width=50% nowrap>". button("{add_group}","AddGroupDans()")."</td>
			</tr>
			</table>
		</td>
	</tr>
	</tbody>
	</table>
	<script>
		function RefreshDansRulesIPList(){
			var selected=escape(document.getElementById('auth-rules').value);
			Set_Cookie('RefreshDansRulesIPList', document.getElementById('auth-rules').value, '3600', '/', '', '');
			if(document.getElementById('table-ip-list-dans')){ LoadAjax('table-ip-list-dans','$page?table=yes&rule=$rule');}
			if(document.getElementById('rules_list_contents')){ LoadAjax('rules_list_contents','$page?table=yes&rule=$rule');}
		
		}
		
	var x_AddIpPopupDans=function(obj){
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		RefreshDansRulesIPList();
	}	

	
	function AddGroupDans(){
		var selected=document.getElementById('auth-rules').value;
		if(selected<2){alert('$select_a_rule_first');return ;}
		YahooWin3(600,'$page?popup-group=yes&selected='+selected,'$group_legend');
	}
		
	function AddIpPopupDans(){
		var selected=document.getElementById('auth-rules').value;
		if(selected<2){alert('$select_a_rule_first');return ;}
			
	var ips=prompt('$squidguard_add_ip_howto');
		if(ips){
		 	var XHR = new XHRConnection();
    	  	XHR.appendData('add-ip-addr',ips);
    	  	XHR.appendData('RuleID',selected);
          	if(document.getElementById('table-ip-list-dans')){document.getElementById('table-ip-list-dans').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';}
    	  	if(document.getElementById('rules_list_contents')){document.getElementById('rules_list_contents').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';} 
     	 	XHR.sendAndLoad('$page', 'GET',x_AddIpPopupDans);     	
			}
		}
		
	function ip_dans_delete(RuleID,ID){
			var XHR = new XHRConnection();
    	  	XHR.appendData('del-ip-addr',ID);
    	  	XHR.appendData('RuleID',RuleID);
    	  	if(document.getElementById('table-ip-list-dans')){document.getElementById('table-ip-list-dans').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';}
    	  	if(document.getElementById('rules_list_contents')){document.getElementById('rules_list_contents').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';} 
     	 	XHR.sendAndLoad('$page', 'GET',x_AddIpPopupDans);     	
	}
		
	
	</script>
	
	";
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);
	
}

function table_del_ips(){
	$pattern=trim($_GET["del-ip-addr"]);
	$dansrules=new dansguardian_rules(null,$_GET["RuleID"]);
	$dansrules->DelIpToFilter($pattern);
}

function table_add_ips(){
	$pattern=trim($_GET["add-ip-addr"]);
	if($pattern==null){return;}
	$dansrules=new dansguardian_rules(null,$_GET["RuleID"]);
		
	if(strpos($pattern,',')){
		$l=explode(",",$pattern);
		while (list ($num, $val) = each ($l) ){
			$val=trim($val);
			if($val==null){continue;}
			$dansrules->AddIpToFilter($val,$_GET["RuleID"]);
		}
		
		return;
		
	}
	$dansrules->AddIpToFilter($pattern,$_GET["RuleID"]);
	
}
function popup_group(){
	$page=CurrentPageName();
	$ldap=new clladp();
	$tpl=new templates();
	$orgs=$ldap->hash_get_ou(true);
	$orgs[null]="{select}";
	$organizations=Field_array_Hash($orgs,'ou',null,"DansGuardianAuthChooseOrg()",null,0,"font-size:15px;padding:4px");
	$dansg=new dansguardian_rules();
	$please=$tpl->_ENGINE_parse_body('{PLEASE_SELECT_GROUP_FIRST}');
	$rules=$dansg->hash_RulesList();
	$rules[null]="{select}";
	
	$rules_list=Field_array_Hash($rules,'RuleID',$_GET["selected"],null,null,0,"font-size:15px;padding:4px");
	
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
	
	<script>
		function DansGuardianAuthChooseOrg(){
			LoadAjax('dans-groups','$page?choose-group='+document.getElementById('ou').value);
		}
		
	var x_DansGuardianAuthAdd= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);return;}
		RefreshDansRulesIPList();
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
		XHR.appendData('add-ldap-group','yes');
	 	XHR.appendData('gpid',gpid);
		XHR.appendData('RuleID',RuleID);
		XHR.sendAndLoad('$page', 'GET',x_DansGuardianAuthAdd);  			
			
		}		
		
	</script>
	
	";
	
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
function save_group(){
	writelogs("Adding \"{$_GET["gpid"]}\" in rule Number {$_GET["RuleID"]}",__FUNCTION__,__FILE__,__LINE__);
	$sql="INSERT INTO dansguardian_groups(RuleID,group_id) VALUES('{$_GET["RuleID"]}','{$_GET["gpid"]}')";
	if(!is_numeric(trim($_GET["gpid"]))){
		$group_id=time();
		writelogs("Adding Non numeric \"{$_GET["gpid"]}\" in rule Number {$_GET["RuleID"]}",__FUNCTION__,__FILE__,__LINE__);
		$sql="INSERT INTO dansguardian_groups(RuleID,group_id,dngroup) VALUES('{$_GET["RuleID"]}','$group_id','{$_GET["gpid"]}')";
		
	}else{
		if($_GET["gpid"]==0){echo "GPID 0 is not allowed\n";return null;}
	}
	
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){
		writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		echo $q->mysql_error;
		return;
	}
	$dans=new dansguardian_rules();
	$dans->RestartFilters();
	}

?>