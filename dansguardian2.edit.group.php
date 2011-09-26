<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.dansguardian.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsDansGuardianAdministrator){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}
if(isset($_GET["group"])){group_edit();exit;}
if(isset($_GET["members"])){members();exit;}
if(isset($_GET["members-search"])){members_search();exit;}
if(isset($_POST["groupname"])){group_edit_save();exit;}

if(isset($_GET["member-edit"])){members_edit();exit;}
if(isset($_GET["member-type-field"])){members_type_field();exit;}

if(isset($_GET["blacklist"])){blacklist();exit;}
if(isset($_GET["whitelist"])){whitelist();exit;}

if(isset($_POST["pattern"])){member_edit_save();exit;}
if(isset($_POST["member-delete"])){member_edit_del();exit;}

tabs();



function tabs(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["group"]='{group}';
	if($_GET["ID"]>-1){
		$array["members"]='{members}';
		$array["blacklist"]='{blacklists}';
		$array["whitelist"]='{whitelist}';
		
	}


	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	
	echo "$menus
	<div id=main_filter_rule_edit_group style='width:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_filter_rule_edit_group').tabs();
			
			
			});
		</script>";		
	
	
}


function group_edit(){
	$ID=$_GET["ID"];
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql_squid_builder();	
	$DISABLE_DANS_FIELDS=0;
	$button_name="{apply}";
	
	if($ID<0){$button_name="{add}";}
	
	
	if($ID>-1){
		$sql="SELECT * FROM webfilter_group WHERE ID=$ID";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
		
	}
	
	$localldap[0]="{ldap_group}";
	$localldap[1]="{virtual_group}";
	
	$users=new usersMenus();
	if(!$users->DANSGUARDIAN_INSTALLED){$DISABLE_DANS_FIELDS=1;}
	
	
	if(!is_numeric($ligne["enabled"])){$ligne["enabled"]=1;}
	
	$html="
	<div id='dansguardinMainGroupDiv'>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>$ID)&nbsp;{groupname}:</td>
		<td>". Field_text("groupname",$ligne["groupname"],"font-size:14px;")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{groupmode}:</td>
		<td>". Field_array_Hash($localldap,"localldap",$ligne["localldap"],"Checklocalldap()",null,0,"font-size:14px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{groupid}:</td>
		<td>". Field_text("gpid",$ligne["gpid"],"font-size:14px;width:65px")."</td>
		<td><input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('MembersBrowse.php?field-user=gpid&OnlyGroups=1&OnlyGUID=1')\"></td>
	</tr>		
	
	<tr>
		<td class=legend>{enabled}:</td>
		<td>". Field_checkbox("enabled",1,$ligne["enabled"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{description}:</td>
		<td><textarea name='description' id='description' style='width:100%;height:50px;overflow:auto;font-size:14px'>". $ligne["description"]."</textarea></td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button($button_name,"SaveDansGUardianGroupRule()")."</td>
	</tr>
	</tbody>
	</table>
	</div>
	<script>
	
	function Checklocalldap(){
		var v=document.getElementById('localldap').value;
		document.getElementById('gpid').disabled=true;
		if(v==0){document.getElementById('gpid').disabled=false;}
	}
	
	var x_SaveDansGUardianGroupRule= function (obj) {
		var res=obj.responseText;
		var ID='$ID';
		if (res.length>3){alert(res);}
		if(ID<0){YahooWin3Hide();}else{RefreshTab('main_filter_rule_edit_group');}
		if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
		
		
	}
	
		function SaveDansGUardianGroupRule(){
		      var XHR = new XHRConnection();
		      XHR.appendData('groupname', document.getElementById('groupname').value);
		      XHR.appendData('localldap', document.getElementById('localldap').value);
		      XHR.appendData('description', document.getElementById('description').value);
		      XHR.appendData('gpid', document.getElementById('gpid').value);
		      if(document.getElementById('enabled').checked){ XHR.appendData('enabled',1);}else{ XHR.appendData('enabled',0);}
		      XHR.appendData('ID','$ID');
		      AnimateDiv('dansguardinMainGroupDiv');
		      XHR.sendAndLoad('$page', 'POST',x_SaveDansGUardianGroupRule);  		
		}
		
		function CheckFields(){
			var DISABLE_DANS_FIELDS=$DISABLE_DANS_FIELDS;
			document.getElementById('naughtynesslimit').disabled=true;
			document.getElementById('searchtermlimit').disabled=true;
			document.getElementById('bypass').disabled=true;
			document.getElementById('blockdownloads').disabled=true;
			document.getElementById('deepurlanalysis').disabled=true;
			document.getElementById('sslmitm').disabled=true;
			document.getElementById('sslcertcheck').disabled=true;
			document.getElementById('groupmode').disabled=true;
			if(DISABLE_DANS_FIELDS==0){
				document.getElementById('naughtynesslimit').disabled=false;
				document.getElementById('searchtermlimit').disabled=false;
				document.getElementById('bypass').disabled=false;
				document.getElementById('blockdownloads').disabled=false;
				document.getElementById('deepurlanalysis').disabled=false;
				document.getElementById('sslmitm').disabled=false;
				document.getElementById('sslcertcheck').disabled=false;
				document.getElementById('groupmode').disabled=false;		
						
			
			}
		}
	Checklocalldap
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function group_edit_save(){
	$ID=$_POST["ID"];
	$q=new mysql_squid_builder();
	unset($_POST["ID"]);
	if($_POST["groupname"]==null){$_POST["groupname"]=time();}
	while (list ($num, $ligne) = each ($_POST) ){
		$fieldsAddA[]="`$num`";
		$fieldsAddB[]="'".addslashes(utf8_encode($ligne))."'";
		$fieldsEDIT[]="`$num`='".addslashes(utf8_encode($ligne))."'";
		
	}
	
	$sql_edit="UPDATE webfilter_group SET ".@implode(",", $fieldsEDIT)." WHERE ID=$ID";
	$sql_add="INSERT IGNORE INTO webfilter_group (".@implode(",", $fieldsAddA).") VALUES (".@implode(",", $fieldsAddB).")";
	
	if($ID<0){$s=$sql_add;}else{$s=$sql_edit;}
	$q->QUERY_SQL($s);
	 
	if(!$q->ok){echo $q->mysql_error."\n$s\n";return;}
	
	
}

function members(){
	$group_id=$_GET["ID"];
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<div class=explain>{dansguardian2_addedit_members_explain}</div>
	<center>
	<table style='width:65%' class=form>
	<tbody>
		<tr><td class=legend>{members}</td>
		<td>". Field_text("members-dnas-search",null,"font-size:16px;width:220px",null,null,null,false,"membersDansSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","membersDansSearch()")."</td>
		</tr>
	</tbody>
	</table>
	</center>
	
	<div id='dansguardian2-members-list' style='width:100%;height:350px;overlow:auto'></div>
	
	<script>
		function membersDansSearchCheck(e){
			if(checkEnter(e)){membersDansSearch();}
		}
		
		function membersDansSearch(){
			var se=escape(document.getElementById('members-dnas-search').value);
			LoadAjax('dansguardian2-members-list','$page?members-search='+se+'&group_id=$group_id');
		
		}
		
		membersDansSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}
function members_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();	
	$group_id=$_GET["group_id"];
	$search=$_GET["members-search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$group_text=$tpl->_ENGINE_parse_body("{member}");
	$sql="SELECT * FROM webfilter_members WHERE groupid=$group_id AND pattern LIKE '$search' ORDER BY pattern LIMIT 0,50";
	
	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}
	
	$add=imgtootltip("plus-24.png","{add} {member}","DansGuardianEditMember(-1)");
	
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th width=99%>{members}</th>
		<th width=99%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$CountDeMembers=0;
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","DansGuardianEditMember('{$ligne["ID"]}','{$ligne["pattern"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","DansGuardianDeleteMember('{$ligne["ID"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
	
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$select</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["pattern"]}</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	</center>
	<script>
		function DansGuardianEditMember(ID,rname){
			YahooWin5('435','$page?member-edit='+ID+'&ID='+ID+'&group_id=$group_id','$group_text::'+ID+'::'+rname);
		
		}
		
	var x_DansGuardianDeleteMember= function (obj) {
		var res=obj.responseText;
		var ID='$ID';
		if (res.length>3){alert(res);}
		if(document.getElementById('dansguardian2-members-list')){membersDansSearch();}
		if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
		
		
	}
	
	function DansGuardianDeleteMember(ID){
		      var XHR = new XHRConnection();
		      XHR.appendData('member-delete', ID);
		      AnimateDiv('dansguardian2-members-list');
		      XHR.sendAndLoad('$page', 'POST',x_DansGuardianDeleteMember);  		
		}
	
	
	</script>";	
	echo $tpl->_ENGINE_parse_body($html);
}

function members_edit(){
	$ID=$_GET["member-edit"];
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql_squid_builder();		
	$membertype[0]="{ipaddr}";
	$membertype[2]="{cdir}";
	$membertype[1]="{username}";
	$button_name="{apply}";
	if($ID<0){$button_name="{add}";}
	
	
	if($ID>-1){
		$sql="SELECT * FROM webfilter_members WHERE ID=$ID";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
		
	}
	
	$users=new usersMenus();
	if(!$users->DANSGUARDIAN_INSTALLED){$DISABLE_DANS_FIELDS=1;}
	
	
	if(!is_numeric($ligne["enabled"])){$ligne["enabled"]=1;}	
	
	$html="
	<div id='members-edit-group'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enabled}:</td>
		<td>". Field_checkbox("member_enabled",1,$ligne["enabled"])."</td>
	</tr>	
	<tr>
		<td class=legend>{member_type}:</td>
		<td>". field_array_Hash($membertype,"membertype",$ligne["membertype"],"membertypeSwitch()",null,0,"font-size:14px")."</td>
	</tr>
	<tr>
		<td class=legend>{member}:</td>
		<td><span id='member-type-div'></span>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("$button_name","SaveMemberType()")."</td>
	</tr>
	</table>
	
	<script>
	var x_SaveMemberType= function (obj) {
		var res=obj.responseText;
		var ID='$ID';
		if (res.length>3){alert(res);}
		YahooWin5Hide();
		if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
		if(document.getElementById('main_filter_rule_edit_group')){RefreshTab('main_filter_rule_edit_group');}
		
		
	}
	
		function SaveMemberType(){
		      var XHR = new XHRConnection();
		      XHR.appendData('pattern', document.getElementById('pattern').value);
		      XHR.appendData('membertype', document.getElementById('membertype').value);
		      XHR.appendData('groupid', '{$_GET["group_id"]}');	      
		      if(document.getElementById('member_enabled').checked){ XHR.appendData('enabled',1);}else{ XHR.appendData('enabled',0);}
		      XHR.appendData('ID','$ID');
		      AnimateDiv('members-edit-group');
		      XHR.sendAndLoad('$page', 'POST',x_SaveMemberType);  		
		}
		
		
	function membertypeSwitch(){
		membertype=document.getElementById('membertype').value;
		var def=escape('{$ligne["pattern"]}');
		LoadAjaxTiny('member-type-div','$page?member-type-field='+membertype+'&default='+def);
	}
	
	membertypeSwitch();
	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function member_edit_save(){
	$ID=$_POST["ID"];
	$q=new mysql_squid_builder();
	$q->CheckTables();
	unset($_POST["ID"]);
	if($_POST["pattern"]==null){return;}
	
	while (list ($num, $ligne) = each ($_POST) ){
		$fieldsAddA[]="`$num`";
		$fieldsAddB[]="'".addslashes(utf8_encode($ligne))."'";
		$fieldsEDIT[]="`$num`='".addslashes(utf8_encode($ligne))."'";
		
	}
	
	$sql_edit="UPDATE webfilter_members SET ".@implode(",", $fieldsEDIT)." WHERE ID=$ID";
	$sql_add="INSERT IGNORE INTO webfilter_members (".@implode(",", $fieldsAddA).") VALUES (".@implode(",", $fieldsAddB).")";
	
	if($ID<0){$s=$sql_add;}else{$s=$sql_edit;}
	$q->QUERY_SQL($s);
	 
	if(!$q->ok){echo $q->mysql_error."\n$s\n";return;}
	
	
}

function member_edit_del(){
	$ID=$_POST["member-delete"];
	$q=new mysql_squid_builder();
	$q->QUERY_SQL("DELETE FROM webfilter_members WHERE ID='$ID'");	
	if(!$q->ok){echo $q->mysql_error."\n$s\n";return;}
}

function members_type_field(){
	$tpl=new templates();
	if($_GET["member-type-field"]==0){echo field_ipv4("pattern", $_GET["default"],"font-size:14px");}
	if($_GET["member-type-field"]==1){echo Field_text("pattern", $_GET["default"],"font-size:14px");}
	if($_GET["member-type-field"]==2){echo field_ipv4_cdir("pattern", $_GET["default"],"font-size:14px");}
	
}

function blacklist(){
	$ID=$_GET["ID"];
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();
	$dans=new dansguardian_rules();

	$sql="SELECT webfilter_blks.category FROM webfilter_assoc_groups,webfilter_blks WHERE 
		webfilter_blks.modeblk=0 
		AND webfilter_blks.webfilter_id=webfilter_assoc_groups.webfilter_id 
		AND webfilter_assoc_groups.group_id=$ID
		";
	
$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=99% align='center'>{blacklist}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(isset($already[$ligne["category"]])){continue;}
		$CountDeMembers=0;
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		
	
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["category"]}<div style='font-size:11px'>{$dans->array_blacksites[$ligne["category"]]}</div></td>
		</tr>
		";
		$already[$ligne["category"]]=true;
	}
	
	$html=$html."</tbody></table>";
	

	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function whitelist(){
	$ID=$_GET["ID"];
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();
	$dans=new dansguardian_rules();

	$sql="SELECT webfilter_blks.category FROM webfilter_assoc_groups,webfilter_blks WHERE 
		webfilter_blks.modeblk=1 
		AND webfilter_blks.webfilter_id=webfilter_assoc_groups.webfilter_id 
		AND webfilter_assoc_groups.group_id=$ID
		";
	
$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=99% align='center'>{whitelist}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(isset($already[$ligne["category"]])){continue;}
		$CountDeMembers=0;
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		
	
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["category"]}<div style='font-size:11px'>{$dans->array_blacksites[$ligne["category"]]}</div></td>
		</tr>
		";
		$already[$ligne["category"]]=true;
	}
	
	$html=$html."</tbody></table>";

	echo $tpl->_ENGINE_parse_body($html);
	
	
}