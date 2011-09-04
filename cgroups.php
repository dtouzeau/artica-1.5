<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.os.system.inc');
	
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){die();}
	if(isset($_GET["cgroups-groups-list"])){cgroups_list();exit;}
	if(isset($_GET["groupedit"])){cgroup_edit();exit;}
	
	
	
page();



function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	$group=$tpl->_ENGINE_parse_body("{group}");
	// http://www.serverwatch.com/tutorials/article.php/3921001/Setting-Up-Linux-Cgroups.htm
	
	
	$html="
	<div class=explain>{howto_cgroups}</div>
	
	<div id='cgroups-groups-list' style='width:100%;height:450px;overflow:auto'></div>
	
	
	<script>
	
		function RefreshCgroupsList(){
			LoadAjax('cgroups-groups-list','$page?cgroups-groups-list=yes');
		
		}
		
		function CgroupsEdit(ID){
			YahooWin5('550','$page?groupedit=yes&ID='+ID,'$group::'+ID);
		
		}
		
	RefreshCgroupsList();
	</scrip>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function cgroups_list(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();	
	$add=imgtootltip("plus-24.png","{add}","CgroupsEdit(0)");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=4>{groups}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	

		$sql="SELECT *  FROM cgroups_groups WHERE ORDER BY groupname";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","SambaVirtalServer('{$ligne["hostname"]}')");
		$select2=imgtootltip("32-network-server.png","{edit}","SambaVirtalServer('{$ligne["hostname"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","SambaVirtalDel('{$ligne["hostname"]}')");

		$html=$html."
		<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold;color:$color' colspan=2>{$ligne["groupname"]}<div><i style='font-size:10px'>{$ligne["group_description"]}</i></div></a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["workgroup"]}</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>&nbsp;{$ligne["ou"]}</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}	
	
	
	$html=$html."</table></center>
	<script>
	
		var x_SambaVirtalDel=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			BrowseSambaSearch();
		}
	
	
		function SambaVirtalDel(hostname){
			if(confirm('$sure_delete_smb_vrt ['+hostname+']')){
				var XHR = new XHRConnection();
				XHR.appendData('delete-hostname',hostname);
				AnimateDiv('browse-samba-list');
    			XHR.sendAndLoad('$page', 'POST',x_SambaVirtalDel);
			}
		}
	
		
		function SambaVirtalServer(server){
			Loadjs('samba.virtual-server.edit.php?hostname='+server);
		}

	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
}


function cgroup_edit(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();	
	if(!is_numeric($_GET["ID"])){$_GET["ID"]=0;}
	
	if($_GET["ID"]>0){
		$sql="SELECT * FROM cgroups_groups WHERE ID={$_GET["ID"]}";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		
	}
	
	$ligne["memory_limit_in_bytes"]=round(($ligne["memory_limit_in_bytes"]/1024)/1000);
	$ligne["memory_memsw_limit_in_bytes"]=round(($ligne["memory_memsw_limit_in_bytes"]/1024)/1000);
	$ligne["memory_soft_limit_in_bytes"]=round(($ligne["memory_soft_limit_in_bytes"]/1024)/1000);
	
	
	
	
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{groupname}:</td>
		<td>". Field_text("groupname",$ligne["groupname"],"font-size:14px;padding:3px;width:110px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{description}:</td>
		<td>". Field_text("group_description",$ligne["group_description"],"font-size:14px;padding:3px;width:150px")."</td>
		<td>&nbsp;</td>
	</tr>	
	
	
	
	<tr>
		<td class=legend>{memory_soft_limit}:</td>
		<td style='font-size:14px'>". Field_text("memory_soft_limit_in_bytes",$ligne["memory_soft_limit_in_bytes"],"font-size:14px;padding:3px;width:60px")."&nbsp;MB</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{memory_limit}:</td>
		<td style='font-size:14px'>". Field_text("memory_limit_in_bytes",$ligne["memory_limit_in_bytes"],"font-size:14px;padding:3px;width:60px")."&nbsp;MB</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{memory_memsw_limit}:</td>
		<td style='font-size:14px'>". Field_text("memory_memsw_limit_in_bytes",$ligne["memory_memsw_limit_in_bytes"],"font-size:14px;padding:3px;width:60px")."&nbsp;MB</td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend>{swappiness}:</td>
		<td style='font-size:14px'>". Field_checkbox("memory_swappiness",1,$ligne["memory_swappiness"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{memory_force_empty}:</td>
		<td style='font-size:14px'>". Field_checkbox("memory_force_empty",1,$ligne["memory_force_empty"])."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveCgroupsValues()")."</td>
	</tr>		
	</table>
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
}