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
while (list ($num, $ligne) = each ($_REQUEST) ){writelogs("item: $num","MAIN",__FILE__,__LINE__);}



if(isset($_GET["rule"])){rule_edit();exit;}
if(isset($_GET["blacklist"])){blacklist();exit;}
if(isset($_GET["whitelist"])){whitelist();exit;}
if(isset($_POST["groupname"])){rule_edit_save();exit;}
if(isset($_POST["blacklist"])){blacklist_save();exit;}
if(isset($_POST["whitelist"])){whitelist_save();exit;}
if(isset($_GET["groups"])){groups();exit;}
if(isset($_GET["groups-search"])){groups_list();exit;}
if(isset($_GET["choose-group"])){groups_choose();exit;}
if(isset($_GET["choose-groups-search"])){groups_choose_search();exit;}
if(isset($_POST["choose-groupe-save"])){groups_choose_add();exit;}
if(isset($_POST["choose-groupe-del"])){groups_choose_del();exit;}

tabs();



function tabs(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["rule"]='{rule}';
	if($_GET["ID"]>-1){
		$array["blacklist"]='{blacklists}';
		$array["whitelist"]='{whitelist}';
		if($_GET["ID"]<>0){$array["groups"]='{groups}';}
	}


	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num={$_GET["ID"]}&ID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	
	echo "$menus
	<div id=main_filter_rule_edit style='width:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_filter_rule_edit').tabs();
			
			
			});
		</script>";		
	
	
}
function whitelist(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql_squid_builder();		
	$dans=new dansguardian_rules();

	
	$sql="SELECT `category` FROM webfilter_blks WHERE `webfilter_id`={$_GET["whitelist"]} AND modeblk=1";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}	
	
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$cats[$ligne["category"]]=true;
		}
	
	$html="
	<div class=explain>{dansguardian2_whitelist_explain}</div>
	
	<div style='height:490px;overflow:auto;margin:9px'>
	<table style='width:100%'><tbody>";
	
	while (list ($num, $val) = each ($dans->array_blacksites) ){
		$md="w".md5($num);
		$field_enabled=0;
		if($cats[$num]){$field_enabled=1;}
		$field=Field_checkbox("$md",1,$field_enabled,"EditCategoryWhiteList('$md','$num')");
		if($dans->array_pics[$num]<>null){$pic="<img src='img/{$dans->array_pics[$num]}'>";}else{$pic="&nbsp;";}
		$color="black";
		if($hash[$num]){
			$field="<img src='img/check2.gif'>";
			$color="red";
		}
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%>$pic</td>
			<td><strong style='font-size:11px;color:$color'>$val</td>
			<td>$field</td>
			<td><span style='color:$color'>$num</span></td>
			
		</tr> 
		
		";
	}
	$html=$html."</tbody></table>
	
	<script>
	var x_EditCategoryWhiteList= function (obj) {
		var res=obj.responseText;
		if (res.length>3){alert(res);}
		if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
	}			
	
	
	function EditCategoryWhiteList(id,category){
		var XHR = new XHRConnection();
		XHR.appendData('whitelist',category);
		XHR.appendData('rule_id',{$_GET["whitelist"]});
		if(document.getElementById(id).checked){
		XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		XHR.sendAndLoad('$page', 'POST',x_EditCategoryWhiteList);	
	}
</script>	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function blacklist(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql_squid_builder();		
	$dans=new dansguardian_rules();

	
	$sql="SELECT `category` FROM webfilter_blks WHERE `webfilter_id`={$_GET["blacklist"]} AND modeblk=0";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}	
	
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$cats[$ligne["category"]]=true;
		}
	
	$html="
	<div class=explain>{dansguardian2_blacklist_explain}</div>
	
	<div style='height:490px;overflow:auto;margin:9px'>
	<table style='width:100%'><tbody>";
	
	while (list ($num, $val) = each ($dans->array_blacksites) ){
		$md=md5($num);
		$field_enabled=0;
		if($cats[$num]){$field_enabled=1;}
		$field=Field_checkbox("$md",1,$field_enabled,"EditCategoryBlacklist('$md','$num')");
		if($dans->array_pics[$num]<>null){$pic="<img src='img/{$dans->array_pics[$num]}'>";}else{$pic="&nbsp;";}
		$color="black";
		if($hash[$num]){
			$field="<img src='img/check2.gif'>";
			$color="red";
		}
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%>$pic</td>
			<td><strong style='font-size:11px;color:$color'>$val</td>
			<td>$field</td>
			<td><span style='color:$color'>$num</span></td>
			
		</tr> 
		
		";
	}
	$html=$html."</tbody></table>
	
	<script>
	var x_EditCategoryBlacklist= function (obj) {
		var res=obj.responseText;
		if (res.length>3){alert(res);}
		if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
	}			
	
	
	function EditCategoryBlacklist(id,category){
		var XHR = new XHRConnection();
		XHR.appendData('blacklist',category);
		XHR.appendData('rule_id',{$_GET["blacklist"]});
		if(document.getElementById(id).checked){
		XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		XHR.sendAndLoad('$page', 'POST',x_EditCategoryBlacklist);	
	}
</script>	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function blacklist_save(){
	$q=new mysql_squid_builder();	
	$sql="SELECT ID FROM webfilter_blks WHERE category='{$_POST["blacklist"]}' AND modeblk=0 AND webfilter_id='{$_POST["rule_id"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if(($_POST["enabled"]==1) && ($ligne["ID"]==0)){
		$sql="INSERT IGNORE INTO webfilter_blks (webfilter_id,category,modeblk) VALUES ('{$_POST["rule_id"]}','{$_POST["blacklist"]}','0')";
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql);
		if(!$q->ok){echo $q->mysql_error;return;} 
	}
	
	if(($_POST["enabled"]==0) && ($ligne["ID"]>0)){
		$q->QUERY_SQL("DELETE FROM webfilter_blks WHERE ID={$ligne["ID"]}");
		if(!$q->ok){echo $q->mysql_error;return;} 
	}

	$sock=new sockets();
	$sock->getFrameWork("squid.php?rebuild-filters=yes");
	
}

function whitelist_save(){
	$q=new mysql_squid_builder();	
	$sql="SELECT ID FROM webfilter_blks WHERE category='{$_POST["whitelist"]}' AND modeblk=1 AND webfilter_id='{$_POST["rule_id"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if(($_POST["enabled"]==1) && ($ligne["ID"]==0)){
		$q->QUERY_SQL("INSERT IGNORE INTO webfilter_blks (webfilter_id,category,modeblk) VALUES ('{$_POST["rule_id"]}','{$_POST["whitelist"]}','1')");
		if(!$q->ok){echo $q->mysql_error;return;} 
	}
	
	if(($_POST["enabled"]==0) && ($ligne["ID"]>0)){
		$q->QUERY_SQL("DELETE FROM webfilter_blks WHERE ID={$ligne["ID"]}");
		if(!$q->ok){echo $q->mysql_error;return;} 
	}

	$sock=new sockets();
	$sock->getFrameWork("squid.php?rebuild-filters=yes");	
	
}

function rule_edit(){
	$ID=$_GET["rule"];
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql_squid_builder();	
	$DISABLE_DANS_FIELDS=0;
	$groupmode[0]="{banned}";
	$groupmode[1]="{filtered}";
	$groupmode[2]="{exception}";
	$button_name="{apply}";
	
	if($ID<0){$button_name="{add}";}
	
	
	if($ID>-1){
		$sql="SELECT * FROM webfilter_rules WHERE ID=$ID";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
		
	}
	
	$users=new usersMenus();
	if(!$users->DANSGUARDIAN_INSTALLED){$DISABLE_DANS_FIELDS=1;}
	if($ID==0){$ligne["groupname"]="default";}
	
	if(!is_numeric($ligne["enabled"])){$ligne["enabled"]=1;}
	
	$html="
	<div id='dansguardinMainRuleDiv'>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>$ID)&nbsp;{rule_name}:</td>
		<td>". Field_text("groupname",$ligne["groupname"],"font-size:14px;")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{groupmode}:</td>
		<td>". Field_array_Hash($groupmode,"groupmode",$ligne["groupmode"],"style:font-size:14px;")."</td>
		<td>&nbsp;</td>
	</tr>		
	
	<tr>
		<td class=legend>{enabled}:</td>
		<td>". Field_checkbox("enabled",1,$ligne["enabled"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{blockdownloads}:</td>
		<td>". Field_checkbox("blockdownloads",1,$ligne["blockdownloads"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{deepurlanalysis}:</td>
		<td>". Field_checkbox("deepurlanalysis",1,$ligne["deepurlanalysis"])."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{sslmitm}:</td>
		<td>". Field_checkbox("sslmitm",1,$ligne["sslmitm"])."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{sslcertcheck}:</td>
		<td>". Field_checkbox("sslcertcheck",1,$ligne["sslcertcheck"])."</td>
		<td>&nbsp;</td>
	</tr>			
	<tr>
		<td class=legend>{naughtynesslimit}:</td>
		<td>". Field_text("naughtynesslimit",$ligne["naughtynesslimit"],"font-size:14px;width:60px")."</td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend>{searchtermlimit}:</td>
		<td>". Field_text("searchtermlimit",$ligne["searchtermlimit"],"font-size:14px;width:60px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{bypass}:</td>
		<td>". Field_text("bypass",$ligne["bypass"],"font-size:14px;width:60px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button($button_name,"SaveDansGUardianMainRule()")."</td>
	</tr>
	</tbody>
	</table>
	</div>
	<script>
	
	var x_SaveDansGUardianMainRule= function (obj) {
		var res=obj.responseText;
		var ID='$ID';
		if (res.length>3){alert(res);}
		if(ID<0){YahooWin3Hide();}else{RefreshTab('main_filter_rule_edit');}
		if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
		
		
	}
	
		function SaveDansGUardianMainRule(){
		      var XHR = new XHRConnection();
		      XHR.appendData('groupname', document.getElementById('groupname').value);
		      XHR.appendData('naughtynesslimit', document.getElementById('naughtynesslimit').value);
		      XHR.appendData('searchtermlimit', document.getElementById('searchtermlimit').value);
		      XHR.appendData('bypass', document.getElementById('bypass').value);
		      if(document.getElementById('enabled').checked){ XHR.appendData('enabled',1);}else{ XHR.appendData('enabled',0);}
		      if(document.getElementById('blockdownloads').checked){ XHR.appendData('blockdownloads',1);}else{ XHR.appendData('blockdownloads',0);}
		      if(document.getElementById('deepurlanalysis').checked){ XHR.appendData('deepurlanalysis',1);}else{ XHR.appendData('deepurlanalysis',0);}
		      if(document.getElementById('sslmitm').checked){ XHR.appendData('sslmitm',1);}else{ XHR.appendData('sslmitm',0);}
		      if(document.getElementById('sslcertcheck').checked){ XHR.appendData('sslcertcheck',1);}else{ XHR.appendData('sslcertcheck',0);}
		      XHR.appendData('ID','$ID');
		      AnimateDiv('dansguardinMainRuleDiv');
		      XHR.sendAndLoad('$page', 'POST',x_SaveDansGUardianMainRule);  		
		}
		
		function CheckFields(){
			var DISABLE_DANS_FIELDS=$DISABLE_DANS_FIELDS;
			var ID=$ID;
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
			if(ID==0){
				document.getElementById('enabled').disabled=true;
				document.getElementById('groupname').disabled=true;
			}
			
			
			
		}
	CheckFields();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function rule_edit_save(){
	$ID=$_POST["ID"];
	$q=new mysql_squid_builder();
	unset($_POST["ID"]);
	$build=false;
	if($_POST["groupname"]==null){$_POST["groupname"]=time();}
	while (list ($num, $ligne) = each ($_POST) ){
		$fieldsAddA[]="`$num`";
		$fieldsAddB[]="'".addslashes(utf8_encode($ligne))."'";
		$fieldsEDIT[]="`$num`='".addslashes(utf8_encode($ligne))."'";
		
	}
	
	$sql_edit="UPDATE webfilter_rules SET ".@implode(",", $fieldsEDIT)." WHERE ID=$ID";
	$sql_add="INSERT IGNORE INTO webfilter_rules (".@implode(",", $fieldsAddA).") VALUES (".@implode(",", $fieldsAddB).")";
	
	if($ID<0){$s=$sql_add;$build=true;}else{$s=$sql_edit;}
	$q->QUERY_SQL($s);
	 
	if(!$q->ok){echo $q->mysql_error."\n$s\n";return;}
	if($build){
		$sock=new sockets();
		$sock->getFrameWork("webfilter.php?compile-rules=yes");
	}
	
}

function groups(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<div class=explain>{dansguardian2_rules_groups_explain}</div>
	<center>
	<table style='width:65%' class=form>
	<tbody>
		<tr><td class=legend>{groups}</td>
		<td>". Field_text("groups-rule-search",null,"font-size:16px;width:220px",null,null,null,false,"GroupsDansRuleSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","GroupsDansRuleSearch()")."</td>
		</tr>
	</tbody>
	</table>
	</center>
	
	<div id='dansguardian2-groups-rule-list' style='width:100%;height:350px;overlow:auto'></div>
	
	<script>
		function GroupsDansRuleSearchCheck(e){
			if(checkEnter(e)){GroupsDansRuleSearch();}
		}
		
		function GroupsDansRuleSearch(){
			var se=escape(document.getElementById('groups-rule-search').value);
			LoadAjax('dansguardian2-groups-rule-list','$page?groups-search='+se+'&rule-id={$_GET["groups"]}');
		
		}
		
		GroupsDansRuleSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);

}

function groups_list(){
	
	$search=$_GET["groups-search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);	
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql_squid_builder();		
	$add_group=$tpl->_ENGINE_parse_body("{add} {group}");
	$sql="SELECT webfilter_assoc_groups.ID,webfilter_assoc_groups.webfilter_id,
	webfilter_group.groupname,
	webfilter_group.description,
	webfilter_group.gpid,
	webfilter_group.localldap,
	webfilter_group.enabled 
	FROM webfilter_group,webfilter_assoc_groups WHERE ((webfilter_group.groupname LIKE '$search' AND webfilter_assoc_groups.webfilter_id={$_GET["rule-id"]}) 
	OR (webfilter_group.description LIKE '$search' AND webfilter_assoc_groups.webfilter_id={$_GET["rule-id"]}))
	AND webfilter_assoc_groups.group_id=webfilter_group.ID	
	ORDER BY webfilter_group.groupname LIMIT 0,50
	";
$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}
	
	$add=imgtootltip("plus-24.png","{add} {group}","DansGuardianAddSavedGroup()");
	
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th width=99%>{group}</th>
		<th width=1% align='center'>{members}</th>
		<th width=99%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{unlink}","UnlinkFilterGroup('{$ligne["ID"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		if($ligne["localldap"]==0){
			$gp=new groups($ligne["gpid"]);
			$groupadd_text="(".$gp->groupName.")";
			$CountDeMembers=count($gp->members);
		}
		
		
		$html=$html."
		<tr class=$classtr>
			<td colspan=2 style='font-size:14px;font-weight:bold;color:$color'>{$ligne["groupname"]} $groupadd_text<div style='font-size:10px'><i>{$ligne["description"]}</i></td>
			<td style='font-size:14px;font-weight:bold;color:$color' align='center'>$CountDeMembers</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	</center>
	<script>
		var x_UnlinkFilterGroup= function (obj) {
			var res=obj.responseText;
			if (res.length>3){alert(res);}
			if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
			GroupsDansRuleSearch();		
			
			
		}
	
		function UnlinkFilterGroup(ID){
		      var XHR = new XHRConnection();
		      XHR.appendData('choose-groupe-del', ID);
		      XHR.sendAndLoad('$page', 'POST',x_UnlinkFilterGroup);  		
		}	

	
		function DansGuardianAddSavedGroup(){
			YahooWin4('380','$page?choose-group={$_GET["rule-id"]}','$add_group');
		}
	
	</script>";	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function groups_choose(){
	$ID=$_GET["choose-group"];
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<center>
	<table style='width:65%' class=form>
	<tbody>
		<tr><td class=legend>{groups}</td>
		<td>". Field_text("groups-choose-search",null,"font-size:16px;width:220px",null,null,null,false,"GroupschooseSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","GroupsDansSearch()")."</td>
		</tr>
	</tbody>
	</table>
	</center>
	
	<div id='dansguardian2-groups-choose-list' style='width:100%;height:350px;overlow:auto'></div>
	
	<script>
		function GroupschooseSearchCheck(e){
			if(checkEnter(e)){GroupsChooseSearch();}
		}
		
		function GroupsChooseSearch(){
			var se=escape(document.getElementById('groups-choose-search').value);
			LoadAjax('dansguardian2-groups-choose-list','$page?choose-groups-search='+se+'&ruleid='+$ID);
		
		}
		
		GroupsChooseSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
	
function groups_choose_search(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();	
	$search=$_GET["groups-search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$group_text=$tpl->_ENGINE_parse_body("{group}");
	$sql="SELECT * FROM webfilter_group WHERE 1 AND ((groupname LIKE '$search') OR (description LIKE '$search')) ORDER BY groupname LIMIT 0,50";
	
	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:11px'>$sql</code>";}
	
	
	
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=99%>{group}</th>
		<th width=1% align='center'>{members}</th>
		<th width=99%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		if($ligne["localldap"]==0){
			$gp=new groups($ligne["gpid"]);
			$groupadd_text="(".$gp->groupName.")";
			$CountDeMembers=count($gp->members);
		}
		
		$add=imgtootltip("plus-24.png","{add} {group}","DansGuardianAddSavedGroup({$ligne["ID"]})");
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["groupname"]} $groupadd_text<div style='font-size:10px'><i>{$ligne["description"]}</i></td>
			<td style='font-size:14px;font-weight:bold;color:$color' align='center'>$CountDeMembers</td>
			<td width=1%>$add</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	</center>
	<script>
		var x_SaveDansGUardianMainRule= function (obj) {
			var res=obj.responseText;
			if (res.length>3){alert(res);}
			if(document.getElementById('main_dansguardian_tabs')){RefreshTab('main_dansguardian_tabs');}
			if(document.getElementById('main_filter_rule_edit')){RefreshTab('main_filter_rule_edit');}		
			
			
		}
	
		function DansGuardianAddSavedGroup(ID){
		      var XHR = new XHRConnection();
		      XHR.appendData('choose-groupe-save', ID);
		      XHR.appendData('ruleid', '{$_GET["ruleid"]}');
		      XHR.sendAndLoad('$page', 'POST',x_SaveDansGUardianMainRule);  		
		}
	
	</script>";	
	echo $tpl->_ENGINE_parse_body($html);	
}

function groups_choose_add(){
	$ruleid=$_POST["ruleid"];
	$groupid=$_POST["choose-groupe-save"];
	$md5=md5("$ruleid$groupid");
	
	$sql="INSERT INTO webfilter_assoc_groups (zMD5,webfilter_id,group_id) VALUES('$md5',$ruleid,$groupid)";
	$q=new mysql_squid_builder();
	$q->CheckTables(null);
	$q->QUERY_SQL($sql);
	if(!$q->ok){echo $q->mysql_error;}
	
	$sock=new sockets();
	$sock->getFrameWork("squid.php?rebuild-filters=yes");	
	
}

function groups_choose_del(){
	$ID=$_POST["choose-groupe-del"];
	$sql="DELETE FROM webfilter_assoc_groups WHERE ID='$ID'";
	$q=new mysql_squid_builder();
	$q->CheckTables(null);
	$q->QUERY_SQL($sql);
	if(!$q->ok){echo $q->mysql_error;}

	$sock=new sockets();
	$sock->getFrameWork("squid.php?rebuild-filters=yes");	
}



