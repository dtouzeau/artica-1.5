<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsDansGuardianAdministrator){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["status"])){status();exit;}
if(isset($_GET["dansguardian-status"])){status_left();exit;}

rules();




function rules(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$q=new mysql_squid_builder();
	$rule_text=$tpl->_ENGINE_parse_body("{rule}");
	$sql="SELECT ID,enabled,groupmode,groupname FROM webfilter_rules ORDER BY groupname";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	$add=imgtootltip("plus-24.png","{add} {rule}","DansGuardianEditRule(-1)");
	
	$select=imgtootltip("32-parameters.png","{edit}","DansGuardianEditRule('0','default')");
	$style="style='font-size:14px;font-weight:bold;color:black'";
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th width=99%>{rules}</th>
		<th width=1%>{groups}</th>
		<th width=1%>{blacklists}</th>
		<th width=1%>{whitelists}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
		<tr class=oddRow>
			<td width=1%>$select</td>
			<td $style width=99%>". texthref("Default", "DansGuardianEditRule('0','default')")."<div><i style='font-size:10px'>{$ligne["ipaddr"]}</i></div></a></td>
			<td width=1% align='center' $style>-</td>
			<td width=1% align='center' $style>". COUNTDEGBLKS(0)."</td>
			<td width=1% align='center' $style>". COUNTDEGBWLS(0)."</td>
			<td width=1% >&nbsp;</td>
		</tr>
";
	
	$classtr="oddRow";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","DansGuardianEditRule('{$ligne["ID"]}','{$ligne["groupname"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","SambaVirtalDel('{$ligne["hostname"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		$style="style='font-size:14px;font-weight:bold;color:$color'";
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$select</td>
			<td $style width=99%>".texthref($ligne["groupname"],"DansGuardianEditRule('{$ligne["ID"]}','{$ligne["groupname"]}')")."<div><i style='font-size:10px'>{$ligne["ipaddr"]}</i></div></a></td>
			<td width=1% align='center' $style>". COUNTDEGROUPES($ligne["ID"])."</td>
			<td width=1% align='center' $style>". COUNTDEGBLKS($ligne["ID"])."</td>
			<td width=1% align='center' $style>". COUNTDEGBWLS($ligne["ID"])."</td>
			<td width=1% >$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	</center>
	<script>
		function DansGuardianEditRule(ID,rname){
			YahooWin3('600','dansguardian2.edit.php?ID='+ID,'$rule_text::'+ID+'::'+rname);
		
		}
	
	
	</script>";

	
echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function COUNTDEGROUPES($ruleid){
	$q=new mysql_squid_builder();
	$sql="SELECT COUNT(ID) as tcount FROM webfilter_assoc_groups WHERE webfilter_id='$ruleid'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if(!is_numeric($ligne["tcount"])){$ligne["tcount"]=0;}
	return $ligne["tcount"];
}

function COUNTDEGBLKS($ruleid){
	$q=new mysql_squid_builder();
	$sql="SELECT COUNT(ID) as tcount FROM webfilter_blks WHERE webfilter_id='$ruleid' AND modeblk=0" ;
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if(!is_numeric($ligne["tcount"])){$ligne["tcount"]=0;}
	return $ligne["tcount"];	
}
function COUNTDEGBWLS($ruleid){
	$q=new mysql_squid_builder();
	$sql="SELECT COUNT(ID) as tcount FROM webfilter_blks WHERE webfilter_id='$ruleid' AND modeblk=1" ;
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	if(!is_numeric($ligne["tcount"])){$ligne["tcount"]=0;}
	return $ligne["tcount"];	
}








