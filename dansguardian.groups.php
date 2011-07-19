<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	
	if(isset($_GET["add-group-rule"])){add_group_sql();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["add-group"])){add_group_form();exit;}
	if(isset($_GET["ou-search"])){add_group_list();exit;}
	if(isset($_GET["groups-list"])){group_list();exit;}
	if(isset($_GET["del-group-rule"])){group_del();exit;}
	js();
	
	
function js(){
$page=CurrentPageName();	
$tpl=new templates();
$ID=$_GET["ID"];
$title=$tpl->_ENGINE_parse_body('{APP_DANSGUARDIAN}:: {groups}:: {rule}::'.$ID);
$add_group=$tpl->_ENGINE_parse_body('{add_group}:: {rule}::'.$ID);
$html="
	var mem_rule_id='';
	var mem_cat='';
	var rule_main_mem='';
	function DansGuardianGroups(){
		YahooWin5(500,'$page?popup=yes','$title'); 
	}			
	
	function DansGuardianGroupsAddForm(){
		YahooWin6(400,'$page?add-group=yes','$add_group'); 
	}
	
	function DansGuardianGroupsSelectOU(){
		LoadAjax('group-id','$page?ou-search='+ document.getElementById('ou-selected').value);
	}
	
	function DansGuardianRefreshGroups(){
		LoadAjax('group-list','$page?groups-list=$ID');
	}
	
	var x_DansGuardianGroupsAdd=function(obj){
	  var tempvalue=obj.responseText;
	  if(tempvalue.length>0){alert(tempvalue);}
	  DansGuardianRefreshGroups();
	  YahooWin6Hide();
      
	}	
	
	function DansGuardianGroupsAdd(){
	  var XHR = new XHRConnection();
      XHR.appendData('add-group-rule','$ID');
      XHR.appendData('gpid',document.getElementById('group-selected').value);
      document.getElementById('group-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
      XHR.sendAndLoad('$page', 'GET',x_DansGuardianGroupsAdd);    
	}
	
	function DansGuardianGroupsDel(ID){
	  var XHR = new XHRConnection();
      XHR.appendData('del-group-rule',ID);
      document.getElementById('group-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
      XHR.sendAndLoad('$page', 'GET',x_DansGuardianGroupsAdd);  
	}
	
	DansGuardianGroups();";
	
echo $html;
}

function add_group_sql(){
	if($_GET["gpid"]==null){return;}
	$sql="INSERT INTO dansguardian_groups(RuleID,group_id) VALUES('{$_GET["add-group-rule"]}','{$_GET["gpid"]}');";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	}
	
function group_del(){
	$sql="DELETE FROM dansguardian_groups WHERE ID='{$_GET["del-group-rule"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
}	
	
function group_list(){
	$sql="SELECT * FROM dansguardian_groups WHERE RuleID='{$_GET["groups-list"]}';";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
$style=CellRollOver();
	$html="
	<table style='width:99%'>
	<tr>
		<th>&nbsp;</th>
		<th nowrap>{group}</th>
		<th nowrap>{ou}</th>
		<th nowrap>{members}</th>
		<th nowrap></th>
	</tR>
		
	";	
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$groups=new groups($ligne["group_id"]);
		$html=$html. "
		<tr $style>
			<td width=1%><img src='img/tree-groups.gif'></td>
			<td width=99% nowrap style='font-size:14px' nowrap align='right'>$groups->groupName</td>
			<td width=1% nowrap style='font-size:14px' nowrap align='right'>$groups->ou</td>
			<td width=1% nowrap style='font-size:14px' nowrap align='right'>". count($groups->members)."</td>
			<td width=1% nowrap style='font-size:14px'>". imgtootltip("ed_delete.gif","{delete}","DansGuardianGroupsDel('{$ligne["ID"]}')")."</td>
		</tr>
		";
		}
	
	
	$html=$html."</table>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup(){
	
	
	$html="
	<div style='float:right;margin:3px'>". Paragraphe("group-add-64.png","{add_group}","{DANSGUARDIAN_ADD_GROUP_EXPLAIN}","javascript:DansGuardianGroupsAddForm()")."</div>
	<div style='font-size:13px;padding:5px'>{DANSGUARDIAN_GROUP_EXPLAIN}</div>
	
	<div id='group-list' style='width:100%;height:250px;overflow:auto;margin-top:8px;margin-bottom:8px;padding:5px;'></div>
	<script>DansGuardianRefreshGroups();</script>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function add_group_form(){
	$ldap=new clladp();
	
	$ous=$ldap->hash_get_ou(true);
	$ous[null]="{select}";
	$ou_field=Field_array_Hash($ous,'ou-selected',null,"DansGuardianGroupsSelectOU()",null,0,"font-size:14px");
	
	
$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' style='font-size:14px' align='right'>{ou}:</td>
		<td valign='top'>$ou_field</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px' align='right'>{group}:</td>
		<td valign='top'><span id='group-id'></span></td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>
			<hr>
				". button("{add}","DansGuardianGroupsAdd()")."
		</td>
	</tr>	
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function add_group_list(){
	$ldap=new clladp();
	$groups=$ldap->hash_groups($_GET["ou-search"],1);
	$groups[null]="{select}";
	$ou_field=Field_array_Hash($groups,'group-selected',null,null,null,0,"font-size:14px");	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($ou_field);
	
}


	

?>