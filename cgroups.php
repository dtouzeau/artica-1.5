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
	if(isset($_GET["cgroupsMustCompile"])){cgroupsMustCompile();exit;}
	if(isset($_GET["groupedit"])){cgroup_edit();exit;}
	if(isset($_GET["groupedit-tabs"])){cgroup_edit_tabs();exit;}
	if(isset($_POST["groupdel"])){cgroup_del();exit;}
	if(isset($_POST["ID"])){cgroup_save();exit;}
	if(isset($_GET["groupprocesses"])){cgroup_processes();exit;}
	if(isset($_GET["processes-cgroup-list"])){cgroup_processes_list();exit;}
	if(isset($_POST["processes-cgroup-add"])){cgroup_processes_add();exit;}
	if(isset($_POST["processes-cgroup-del"])){cgroup_processes_del();exit;}
	if(isset($_GET["groupprocesses_list"])){cgroup_processes_running();exit;}
	if(isset($_GET["processes-cgroup-running-list"])){cgroup_processes_running_list();exit;}
	if(isset($_POST["ApplyCgroupConf"])){ApplyCgroupConf();exit;}
	if(isset($_POST["RestartService"])){RestartService();exit;}
	if(isset($_POST["CgroupKill"])){CgroupKill();exit;}
	if(isset($_POST["CgroupMove"])){CgroupMove();exit;}
	if(isset($_POST["CgroupMoveAll"])){CgroupMoveAll();exit;}
	if(isset($_POST["CgroupKillAll"])){CgroupKillAll();exit;}
	if(isset($_POST["cgroupsEnabled"])){cgroupsEnabledSave();exit;}
	
	
page();



function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	$group=$tpl->_ENGINE_parse_body("{group}");
	$processes=$tpl->_ENGINE_parse_body("{processes}");
	// http://www.serverwatch.com/tutorials/article.php/3921001/Setting-Up-Linux-Cgroups.htm
	$sock=new sockets();
	$cgroupsEnabled=$sock->GET_INFO("cgroupsEnabled");
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=99%'>
			<div class=explain>{howto_cgroups}</div>
		</td>
		<td valign='top' width=1%>
			<div id='cgroupsMustCompile'></div>
			<div style='text-align:right'>". imgtootltip("refresh-24.png","{refresh}","RefreshCgroupsList()")."</div>	
		</td>
	</tr>
	</table>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable_service}:</td>
		<td>". Field_checkbox("cgroupsEnabled", 1,$cgroupsEnabled,"cgroupsEnabledSave()")."</td>
	</tr>
	</table>
	
	<div id='cgroups-groups-list' style='width:100%;height:450px;overflow:auto'></div>
	
	
	<script>
	
		function RefreshCgroupsList(){
			LoadAjax('cgroups-groups-list','$page?cgroups-groups-list=yes');
		
		}
		
		function ApplyCgroupConf(){
			var XHR = new XHRConnection();
			XHR.appendData('ApplyCgroupConf','yes');
			AnimateDiv('cgroups-groups-list');
			AnimateDiv('cgroupsMustCompile');
    		XHR.sendAndLoad('$page', 'POST',x_CgroupsDel);		
		}
		
		function RestartCgroupsService(){
			var XHR = new XHRConnection();
			XHR.appendData('RestartService','yes');
			AnimateDiv('cgroups-groups-list');
			AnimateDiv('cgroupsMustCompile');
    		XHR.sendAndLoad('$page', 'POST',x_CgroupsDel);		
		}		
		
		
		function cgroupsEnabledSave(){
				var XHR = new XHRConnection();
				if(document.getElementById('cgroupsEnabled').checked){
					XHR.appendData('cgroupsEnabled',1);
				}else{
					XHR.appendData('cgroupsEnabled',0);
				}
				AnimateDiv('cgroups-groups-list');
    			XHR.sendAndLoad('$page', 'POST',x_CgroupsDel);
			
		}		
		
		
		function CgroupsEdit(ID){
			if(ID>0){
				YahooWin5('680','$page?groupedit-tabs=yes&ID='+ID,'$group::'+ID);
				return;
			}
			YahooWin5('550','$page?groupedit=yes&ID='+ID,'$group::'+ID);
		
		}
		
		function CgroupsProcesses(ID){
			YahooWin5('550','$page?groupprocesses=yes&GPID='+ID,'$group::'+ID+'::$processes');
		}
		
	RefreshCgroupsList();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function cgroups_list(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();	
	$add=imgtootltip("plus-24.png","{add}","CgroupsEdit(0)");
	$delete_ask=$tpl->javascript_parse_text("{inputbox delete group}");
	$sock=new sockets();
	$cgroupsEnabled=$sock->GET_INFO("cgroupsEnabled");
	if(!is_numeric($cgroupsEnabled)){$cgroupsEnabled=0;}
	$classtr="oddRow";
	$color="black";
	if($cgroupsEnabled==0){$color="#CCCCCC";}
	$memory_structure=1;
	$family=unserialize(base64_decode($sock->getFrameWork("cgroup.php?get-cgroups-family=yes")));
	if(!$family["memory"]){$memory_structure=0;}	
		
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{groups}</th>
		<th>{processes}</th>
		<th>{cpu_shares}</th>
		<th>{memory_limit}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
	<tr class=$classtr>
			<td width=1%>&nbsp;</td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=99%>{system}<div><i style='font-size:10px'>{all_out_of_groups}</i></div></a></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% align='center'>&nbsp;*&nbsp;</td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1%>100%</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>&nbsp;{unlimited}</td>
			<td width=1%>&nbsp;</td>
		</tr>";	
	
	

		$sql="SELECT *  FROM cgroups_groups ORDER BY cpu_shares,groupname";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$color="black";	
		if($cgroupsEnabled==0){$color="#CCCCCC";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("software-task-32.png","{processes}","CgroupsProcesses('{$ligne["ID"]}')");
		$select2=imgtootltip("32-network-server.png","{edit}","SambaVirtalServer('{$ligne["hostname"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","CgroupsDel('{$ligne["ID"]}')");
		$link="<a href=\"javascript:blur();\" OnClick=\"javascript:CgroupsEdit({$ligne["ID"]});\" style='font-size:14px;font-weight:bold;color:$color;text-decoration:underline'>";
		$linkADD="<a href=\"javascript:blur();\" OnClick=\"javascript:CgroupsProcesses({$ligne["ID"]});\" style='font-size:14px;font-weight:bold;color:$color;text-decoration:underline'>";
		
		$sql="SELECT COUNT(groupid) as tcount FROM cgroups_processes WHERE groupid={$ligne["ID"]}";
		$ligne2=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$processesNumber=$ligne2["tcount"];
		$ligne["cpu_shares"]=round(($ligne["cpu_shares"]/1024)*100,2);
		if($memory_structure==0){$ligne["memory_limit_in_bytes"]="&nbsp;-&nbsp;";}
		
		
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$select</td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=99%>$link{$ligne["groupname"]}</a><div><i style='font-size:10px'>{$ligne["group_description"]}</i></div></a></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% align='center'>$linkADD&nbsp;$processesNumber&nbsp;</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1%>{$ligne["cpu_shares"]}%</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>&nbsp;{$ligne["memory_limit_in_bytes"]}MB</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}	
	
	
	$html=$html."</table></center>
	<script>
	
		var x_CgroupsDel=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			RefreshCgroupsList();
		}
	
	
		function CgroupsDel(ID){
			if(confirm('$delete_ask')){
				var XHR = new XHRConnection();
				XHR.appendData('groupdel',ID);
				AnimateDiv('cgroups-groups-list');
    			XHR.sendAndLoad('$page', 'POST',x_CgroupsDel);
			}
		}
		
		LoadAjax('cgroupsMustCompile','$page?cgroupsMustCompile=yes');


	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
}

function cgroup_edit_tabs(){
	$page=CurrentPageName();
	$array["groupedit"]='{parameters}';
	$array["groupprocesses"]='{processes}';
	$array["groupprocesses_list"]='{running_processes}';
	
	
	$tpl=new templates();

	while (list ($num, $ligne) = each ($array) ){
		if($num=="groupprocesses"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?groupprocesses=yes&GPID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		if($num=="groupprocesses_list"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?groupprocesses_list=yes&GPID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_cgroupid style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_cgroupid').tabs();
				});
		</script>";		
	
	
}


function cgroup_edit(){
	$tpl=new templates();
	$page=CurrentPageName();
	$user=new usersMenus();
	$q=new mysql();	
	if(!is_numeric($_GET["ID"])){echo "not a numeric!!<br>";$_GET["ID"]=0;}
	$cpunum=$user->CPU_NUMBER;
	$button_name="{add}";
	$sock=new sockets();
	$is_cpu_rt=trim($sock->getFrameWork("cgroup.php?is-cpu-rt=yes"));
	
	
	if($_GET["ID"]>0){
		$sql="SELECT * FROM cgroups_groups WHERE ID={$_GET["ID"]}";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if(!$q-ok){echo "<H2>$q->mysql_error</H2>";}
		$button_name="{apply}";
	}
	
	if($ligne["cpuset_cpus"]==null){$ligne["cpuset_cpus"]="0,1,2,3,4,5,6,7,8";}
	
	$trCPUS=explode(",",$ligne["cpuset_cpus"]);
	
	while (list ($num, $cpu_ligne) = each ($trCPUS) ){
		$SaveCPUS[$cpu_ligne]=true;
	}
	
	for($i=0;$i<$cpunum;$i++){
		$cpu_enabled=0;
		if($SaveCPUS[$i]){$cpu_enabled=1;}
		$cpusFields[]="<td tyle='font-size:14px'>$i&nbsp;". Field_checkbox("cpu_$i", 1,$cpu_enabled)."</td>";
		$cpujs[]="if(document.getElementById('cpu_$i').checked){XHR.appendData('cpuset_cpus_$i',1);}else{XHR.appendData('cpuset_cpus_$i',0);}";
		
	}	
	
	$ligne["memory_limit_in_bytes"]=$ligne["memory_limit_in_bytes"];
	$ligne["memory_memsw_limit_in_bytes"]=$ligne["memory_memsw_limit_in_bytes"];
	$ligne["memory_soft_limit_in_bytes"]=$ligne["memory_soft_limit_in_bytes"];
	if(!is_numeric($ligne["cpu_shares"])){$ligne["cpu_shares"]=1024;}
	$ligne["cpu_shares"]=round(($ligne["cpu_shares"]/1024)*100,2);
	if(!is_numeric($ligne["cpu_rt_runtime_us"])){$ligne["cpu_rt_runtime_us"]=950000;}
	if(!is_numeric($ligne["cpu_rt_period_us"])){$ligne["cpu_rt_period_us"]=1000000;}
	if(!is_numeric($ligne["memory_swappiness"])){$ligne["memory_swappiness"]=60;}
	
	
	
	$memory=1;
	
	$family=unserialize(base64_decode($sock->getFrameWork("cgroup.php?get-cgroups-family=yes")));
	if(!$family["memory"]){$memory=0;}
	$html="
	<div id='cgroups_limitdiv'></div>
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
		<td class=legend>{cpu_shares}:</td>
		<td style='font-size:14px'>". Field_text("cpu_shares",$ligne["cpu_shares"],"font-size:14px;padding:3px;width:60px")."&nbsp;%</td>
		<td>". help_icon("{cpu_shares_text}")."</td>
	</tr>
	<tr>
		<td class=legend>CPUS ($cpunum):</td>
		<td style='font-size:14px'><table><tr>". @implode("\n", $cpusFields)."</tr></table></td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend>{cpu_rt_runtime_us}:</td>
		<td style='font-size:14px'>". Field_text("cpu_rt_runtime_us",$ligne["cpu_rt_runtime_us"],"font-size:14px;padding:3px;width:75px")."&nbsp;Ms</td>
		<td>". help_icon("{cpu_rt_runtime_us_text}")."</td>
	</tr>		
	<tr>
		<td class=legend>{cpu_rt_period_us}:</td>
		<td style='font-size:14px'>". Field_text("cpu_rt_period_us",$ligne["cpu_rt_period_us"],"font-size:14px;padding:3px;width:75px")."&nbsp;Ms</td>
		<td>". help_icon("{cpu_rt_runtime_us_text}")."</td>
	</tr>		
	
	<tr>
		<td class=legend>{memory_soft_limit}:</td>
		<td style='font-size:14px'>". Field_text("memory_soft_limit_in_bytes",$ligne["memory_soft_limit_in_bytes"],"font-size:14px;padding:3px;width:60px")."&nbsp;MB</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{memory_limit}:</td>
		<td style='font-size:14px'>". Field_text("memory_limit_in_bytes",$ligne["memory_limit_in_bytes"],"font-size:14px;padding:3px;width:60px")."&nbsp;MB</td>
		<td>". help_icon("{memory_soft_limit_text}")."</td>
	</tr>	
	<tr>
		<td class=legend>{memory_memsw_limit}:</td>
		<td style='font-size:14px'>". Field_text("memory_memsw_limit_in_bytes",$ligne["memory_memsw_limit_in_bytes"],"font-size:14px;padding:3px;width:60px")."&nbsp;MB</td>
		<td>". help_icon("{memory_memsw_limit_text}")."</td>
	</tr>		
	<tr>
		<td class=legend>{swappiness}:</td>
		<td style='font-size:14px'>". Field_text("memory_swappiness",$ligne["memory_swappiness"],"font-size:14px;padding:3px;width:60px")."&nbsp;%</td>
		<td>". help_icon("{swappiness_text}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("$button_name","SaveCgroupsValues()")."</td>
	</tr>		
	</table>
	
	<script>
	var x_SaveCgroupsValues=function (obj) {
			var id={$_GET["ID"]};
			var results=obj.responseText;
			document.getElementById('cgroups_limitdiv').innerHTML='';
			if(results.length>2){
				alert(results);
				RefreshCgroupsList();
				return;

			}			
			if(id==0){YahooWin5Hide();}
			RefreshCgroupsList();
		}
	
	
		function SaveCgroupsValues(hostname){
				var XHR = new XHRConnection();
				XHR.appendData('ID',{$_GET["ID"]});
				XHR.appendData('groupname',document.getElementById('groupname').value);
				XHR.appendData('group_description',document.getElementById('group_description').value);
				XHR.appendData('cpu_shares',document.getElementById('cpu_shares').value);
				XHR.appendData('cpu_rt_runtime_us',document.getElementById('cpu_rt_runtime_us').value);
				XHR.appendData('cpu_rt_period_us',document.getElementById('cpu_rt_period_us').value);
				XHR.appendData('memory_soft_limit_in_bytes',document.getElementById('memory_soft_limit_in_bytes').value);
				XHR.appendData('memory_limit_in_bytes',document.getElementById('memory_limit_in_bytes').value);
				XHR.appendData('memory_memsw_limit_in_bytes',document.getElementById('memory_memsw_limit_in_bytes').value);
				XHR.appendData('memory_swappiness',document.getElementById('memory_swappiness').value);
				". @implode("\n", $cpujs)."
				AnimateDiv('cgroups-groups-list');
				AnimateDiv('cgroups_limitdiv');
    			XHR.sendAndLoad('$page', 'POST',x_SaveCgroupsValues);
			
		}

		function CheckFromcGROUPS(){
			var MEMORY=$memory;
			var is_cpu_rt='$is_cpu_rt';
			if(MEMORY==0){
				document.getElementById('memory_soft_limit_in_bytes').disabled=true;
				document.getElementById('memory_limit_in_bytes').disabled=true;
				document.getElementById('memory_memsw_limit_in_bytes').disabled=true;
				document.getElementById('memory_swappiness').disabled=true;
			}
			
			if(is_cpu_rt=='FALSE'){
				document.getElementById('cpu_rt_period_us').disabled=true;
				document.getElementById('cpu_rt_runtime_us').disabled=true;			
			}
			
		}
	CheckFromcGROUPS();
	</script>
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
}

function cgroup_save(){
	
	$ID=$_POST["ID"];
	unset($_POST["ID"]);
	
	$_POST["cpu_shares"]=$_POST["cpu_shares"]/100;
	$_POST["cpu_shares"]=round(1024*$_POST["cpu_shares"]);
	
	$ldap=new clladp();
	$_POST["groupname"]=$ldap->StripSpecialsChars($_POST["groupname"]);
	while (list ($num, $ligne) = each ($_POST) ){
		if(preg_match("#cpuset_cpus_([0-9]+)#", $num,$re)){
			if($ligne==1){$cpuset_cpus_tb[]=$re[1];}
			unset($_POST[$num])	;
		}
	}
	
	
	if(!is_array($cpuset_cpus_tb)){$cpuset_cpus_tb=array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8);}
	$_POST["cpuset_cpus"]=@implode(",", $cpuset_cpus_tb);
	
	reset($_POST);
	
	while (list ($num, $ligne) = each ($_POST) ){
		$fields[]="`$num`";
		$values[]="'".addslashes($ligne)."'";
		$upd[]="`$num`='".addslashes($ligne)."'";
	}
	
	$sql_edit="UPDATE cgroups_groups SET ".@implode(",", $upd)." WHERE ID=$ID";
	$sql="INSERT INTO cgroups_groups (".@implode(",",$fields).") VALUES (".@implode(",",$values).")";
	if($ID>0){$sql=$sql_edit;}
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->SET_INFO("cgroupsMustCompile", 1);		
	
	
	
}

function cgroup_processes(){
	$tpl=new templates();
	$page=CurrentPageName();

	$html="
	<center style='margin:bottom:10px'>
		<table style='width:90%' class=form>
		<tbody>
			<tr>
				<td class=legend>{processes}:</td>
				<td>". Field_text("processes-cgroup-find",null,"font-size:16px;padding:3px",null,null,null,false,"ProcessesCGroupsFindCheck(event)")."</td>
				<td width=1%>". button("{search}","ProcessesCGroupsFind()")."</td>
			</tr>
		</tbody>
		</table>
	</center>
	<div id='processes-cgroup-list' style='width:100%;height:450px;overflow:auto'></div>
	
	
	<script>
		function ProcessesCGroupsFindCheck(e){
			if(checkEnter(e)){ProcessesCGroupsFind();}
		}
		
		function ProcessesCGroupsFind(){
			var se=escape(document.getElementById('processes-cgroup-find').value);
			LoadAjax('processes-cgroup-list','$page?processes-cgroup-list=yes&GPID={$_GET["GPID"]}&search='+se);
		
		}
		
		ProcessesCGroupsFind();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function cgroup_processes_running(){
	$tpl=new templates();
	$page=CurrentPageName();
	$kill_ask=$tpl->javascript_parse_text("{kill_ask}");
	$move_task_default_ask=$tpl->javascript_parse_text("{move_task_default_ask}");
	$kill_all_tasks=$tpl->javascript_parse_text("{kill_all_tasks}");
	$html="
	<center style='margin:bottom:10px'>
		<table style='width:90%' class=form>
		<tbody>
			<tr>
				<td class=legend>{processes}:</td>
				<td>". Field_text("processes-cgroup-running-find",null,"font-size:16px;padding:3px",null,null,null,false,"ProcessesCGroupsRFindCheck(event)")."</td>
				<td width=1%>". button("{search}","ProcessesCGroupsRFind()")."</td>
			</tr>
		</tbody>
		</table>
	</center>
	<div id='processes-cgroup-running-list' style='width:100%;height:450px;overflow:auto'></div>
	
	
	<script>
		function ProcessesCGroupsRFindCheck(e){
			if(checkEnter(e)){ProcessesCGroupsRFind();}
		}
		
		function ProcessesCGroupsRFind(){
			var se=escape(document.getElementById('processes-cgroup-running-find').value);
			LoadAjax('processes-cgroup-running-list','$page?processes-cgroup-running-list=yes&GPID={$_GET["GPID"]}&search='+se);
		
		}
		
		ProcessesCGroupsRFind();
		
		var x_CgroupKill=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			ProcessesCGroupsRFind();
		}
	
	
		function CgroupKill(ID){
			if(confirm('$kill_ask'+'? '+ID)){
				var XHR = new XHRConnection();
				XHR.appendData('CgroupKill',ID);
				AnimateDiv('processes-cgroup-running-list');
    			XHR.sendAndLoad('$page', 'POST',x_CgroupKill);
			}
		}	

		
		function CgroupMove(ID){
			if(confirm('$move_task_default_ask ? '+ID)){
				var XHR = new XHRConnection();
				XHR.appendData('CgroupMove',ID);
				AnimateDiv('processes-cgroup-running-list');
    			XHR.sendAndLoad('$page', 'POST',x_CgroupKill);
			}	
		
		}
		function CgroupMoveAll(group){
			if(confirm('$move_task_default_ask ? '+group+' * ')){
				var XHR = new XHRConnection();
				XHR.appendData('CgroupMoveAll',group);
				AnimateDiv('processes-cgroup-running-list');
    			XHR.sendAndLoad('$page', 'POST',x_CgroupKill);
			}	
		
		}	

		function CgroupKillAll(group){
			if(confirm('$kill_all_tasks ? '+group+' * ')){
				var XHR = new XHRConnection();
				XHR.appendData('CgroupKillAll',group);
				AnimateDiv('processes-cgroup-running-list');
    			XHR.sendAndLoad('$page', 'POST',x_CgroupKill);
			}	
		
		}			
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function cgroup_processes_running_list(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();			
	$sql="SELECT groupname FROM cgroups_groups WHERE ID={$_GET["GPID"]}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q-ok){echo "<H2>$q->mysql_error</H2>";return;}	
	
	$sock=new sockets();
	$hash=unserialize(base64_decode($sock->getFrameWork("cgroup.php?runingplist={$ligne["groupname"]}")));
	if(!is_array($hash)){return null;}
	//print_r($hash);
	$_GET["query"]=trim($_GET["query"]);
	if($_GET["query"]=='*'){$_GET["query"]=null;}
	$sock=new sockets();
	if(strlen(trim($_GET["query"]))>0){
		$_GET["query"]=str_replace(".", "\.", $_GET["query"]);
		$_GET["query"]=str_replace("*", ".*?", $_GET["query"]);
		$_GET["query"]=str_replace("/", "\/", $_GET["query"]);
		$_GET["query"]=str_replace(" ", "\s+", $_GET["query"]);
		
	}

	
	$html=$html."
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>pid</th>
	<th>{cpu}</th>
	<th>{memory}</th>
	<th>{processes} ({$ligne["groupname"]})</th>
	<th>". imgtootltip("arrowdown-24.png","{move_all_process_to_default}","CgroupMoveAll('{$ligne["groupname"]}');")."</th>
	<th>". imgtootltip("delete-24.png","{kill_all_tasks}","CgroupKillAll('{$ligne["groupname"]}');")."</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	
while (list ($num, $ligne) = each ($hash) ){
		$ligne=trim($ligne);
		if($ligne==null){continue;}
		if(!preg_match("#^.+?([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+[0-9]+\s+[0-9]+.+?\s+.+?\s+[a-zA-Z0-9:]+\s+[0-9:]+\s+(.+?)$#", $ligne,$re)){echo "<li>$ligne</li>\n";continue;}
		$cmd=trim($re[4]);
		if($_GET["query"]<>null){if(!preg_match("#{$_GET["query"]}#", $cmd)){continue;}}
		
		
		
		$psespace=strpos($cmd, " ");
		$pname=$cmd;
		$pid=$re[1];
		$kill=imgtootltip("delete-32.png","{kill_task}","CgroupKill($pid)");
		$move=imgtootltip("arrowdown-32.png","{move_task}","CgroupMove($pid)");
		$strnln=strlen($pname);
		$max=60;
		if($strnln>$max){$pname=substr($pname, 0,$max-3)."...";}
		
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
		<tr class=$classtr>
		<td width=1% align='center' valign='middle' style='font-size:14px;'>$pid</td>
		<td width=1% align='center' valign='middle' style='font-size:14px;'>{$re[2]}%</td>
		<td width=1% align='center' valign='middle' style='font-size:14px;'>{$re[3]}%</td>
		<td><strong style='font-size:12px;text-decoration:underline' >
		". texttooltip($pname,"$cmd",null,null,null,"font-size:12px;font-weight:bold",1)."</strong>
		<div style='text-align:right;text-decoration:none'>
			<i style='font-size:11px;font-weight:normal;text-decoration:none'></i>
		</div>
		<td width=1% align='center' valign='middle' style='font-size:14px;'>$move</td>
		<td width=1% align='center' valign='middle' style='font-size:14px;'>$kill</td>
		</td>
		
		</tr>
	";
	}
	
	$html=$html."</tbody></table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
}

function cgroup_processes_list(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();		
	$give_process_or_full_command=$tpl->javascript_parse_text("{give_process_or_full_command}");
	
$add=imgtootltip("plus-24.png","{add}","Loadjs('ProcessesBrowse.php?function=CgroupProcessAdd')");
	$classtr="oddRow";
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{process}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
	";	
		$_GET["search"]=trim($_GET["search"]);
		if(trim($_GET["search"])<>null){
			$_GET["search"]="*{$_GET["search"]}*";
			$_GET["search"]=str_replace("**", "*", $_GET["search"]);
			$_GET["search"]=str_replace("**", "*", $_GET["search"]);
			$_GET["search"]=str_replace("*", "%", $_GET["search"]);
			$filter=" AND process_name LIKE '{$_GET["search"]}'";
		}

		$sql="SELECT * FROM cgroups_processes WHERE 1 $filter AND groupid={$_GET["GPID"]} ORDER BY process_name";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$color="black";	
		$delete=imgtootltip("delete-32.png","{delete}","CgroupProcessDel('{$ligne["process_name"]}')");
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold;color:$color' width=99% colspan=2>$link{$ligne["process_name"]} ({$ligne["user"]})</a></td>
			<td width=1%>$delete</td>
		</tr>
		";
	}	
	
	
	$html=$html."</table></center>
	<script>
	
		var x_CgroupProcessAdd=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			ProcessesCGroupsFind();
			if(document.getElementById('cgroups-groups-list')){
				RefreshCgroupsList();
			}
		}
	
	
		function CgroupProcessAdd(process_user,process_path){
				if(!process_user){alert('Fatal error, no such user');return;}
				if(!process_path){alert('Fatal error, no such process for user :'+process_user);return;}
				process_path=prompt('$give_process_or_full_command',process_path);
				if(process_path){
					var XHR = new XHRConnection();
					XHR.appendData('processes-cgroup-add',process_path);
					XHR.appendData('user',process_user);
					XHR.appendData('GPID',{$_GET["GPID"]});
					AnimateDiv('processes-cgroup-list');
    				XHR.sendAndLoad('$page', 'POST',x_CgroupProcessAdd);
    				}
			
		}
		
		function CgroupProcessDel(process){
			var XHR = new XHRConnection();
			XHR.appendData('processes-cgroup-del',process);
			XHR.appendData('GPID',{$_GET["GPID"]});
			AnimateDiv('processes-cgroup-list');
    		XHR.sendAndLoad('$page', 'POST',x_CgroupProcessAdd);		
		
		}
	
		
		
	
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
		
	
}

function cgroup_processes_add(){
	$p=addslashes(utf8_encode($_POST["processes-cgroup-add"]));
	$sql="INSERT IGNORE INTO cgroups_processes(process_name,user,groupid) VALUES ('$p','{$_POST["user"]}','{$_POST["GPID"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->SET_INFO("cgroupsMustCompile", 1);	
	
}
function cgroup_processes_del(){
	$sql="DELETE FROM cgroups_processes WHERE process_name='{$_POST["processes-cgroup-del"]}' AND groupid='{$_POST["GPID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->SET_INFO("cgroupsMustCompile", 1);		
}

function cgroup_del(){
	$ID=$_POST["groupdel"];
	$sql="DELETE FROM cgroups_processes WHERE groupid='$ID'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sql="DELETE FROM cgroups_groups WHERE ID='$ID'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$sock=new sockets();
	$sock->SET_INFO("cgroupsMustCompile", 1);
}

function cgroupsMustCompile(){
	
	$sock=new sockets();
	$cgroupsEnabled=$sock->GET_INFO("cgroupsEnabled");
	if(!is_numeric($cgroupsEnabled)){$cgroupsEnabled=0;}	
	
	
	
	$tpl=new templates();
	$ini_stats=new Bs_IniHandler();
	$datas=base64_decode($sock->getFrameWork("cgroup.php?status=yes"));
	$ini_stats->loadString($datas);

	echo "<div style='width:220px'>";
	echo $tpl->_ENGINE_parse_body(DAEMON_STATUS_TEXT("APP_CGROUPS", $ini_stats));
	echo "<div style='width:220px'>";
	
	
	if($cgroupsEnabled==1){
		echo $tpl->_ENGINE_parse_body(ParagrapheTEXT("service-restart-32.png", "{restart_service}", "{restart_service_text}","javascript:RestartCgroupsService();"));
	}
	echo "</div>";	
	$sock=new sockets();
	$cgroupsMustCompile=$sock->GET_INFO("cgroupsMustCompile");
	if($cgroupsMustCompile<>1){return;}
	if($cgroupsEnabled==1){
		echo "<div style='width:220px'>";
		echo $tpl->_ENGINE_parse_body(ParagrapheTEXT("service-restart-32.png", "{apply config}", "{apply_backup_behavior}","javascript:ApplyCgroupConf();"));
		echo "</div>";
	}
	
}

function ApplyCgroupConf(){
	$sock=new sockets();
	$sock->getFrameWork("cgroup.php?ApplyCgroupConf=yes");
	$sock->SET_INFO("cgroupsMustCompile",0);
}
function RestartService(){
	$sock=new sockets();
	$sock->getFrameWork("cgroup.php?restart=yes");
	$sock->SET_INFO("cgroupsMustCompile",0);	
}

function CgroupKill(){
	if($_POST["CgroupKill"]>5){
		$sock=new sockets();
		$sock->getFrameWork("cgroup.php?kill-proc={$_POST["CgroupKill"]}");
	}	
}
function CgroupMove(){
		$sock=new sockets();
		$sock->getFrameWork("cgroup.php?mv-def-proc={$_POST["CgroupMove"]}");
		
}
function CgroupMoveAll(){
	$sock=new sockets();
	$sock->getFrameWork("cgroup.php?mv-all-def-proc={$_POST["CgroupMoveAll"]}");
		
}
function CgroupKillAll(){
	$sock=new sockets();
	$sock->getFrameWork("cgroup.php?kill-all-procs={$_POST["CgroupKillAll"]}");
		
}
function cgroupsEnabledSave(){
	$sock=new sockets();
	$sock->SET_INFO("cgroupsEnabled", $_POST["cgroupsEnabled"]);
	$sock->getFrameWork("cgroup.php?restart=yes");
	
}








