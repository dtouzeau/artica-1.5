<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	
	//http://blog.scoutapp.com/articles/2009/07/31/understanding-load-averages
	
	if(posix_getuid()<>0){
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["manager"])){manager();exit;}
	if(isset($_GET["replicat"])){replicat();exit;}
	if(isset($_GET["EnableMysqlClusterManager"])){manager_save();exit;}
	if(isset($_GET["EnableMysqlClusterReplicat"])){replicat_save();exit;}
	if(isset($_GET["Addreplica"])){replicat_add();exit;}
	if(isset($_GET["replicat-list"])){echo replicat_list();exit;}
	if(isset($_GET["Delreplica"])){replica_delete();exit;}
	if(isset($_GET["status"])){echo status();exit;}
	
	js();
	
function js(){
		
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{MYSQL_CLUSTER}');
	$title2=$tpl->_ENGINE_parse_body('{MYSQL_CLUSTER_MANAGER}');
	$title3=$tpl->_ENGINE_parse_body('{MYSQL_CLUSTER_REPLICAT}');
	$MYSQL_CLUSTER_REPLICA_ADD_EXPLAIN=$tpl->_ENGINE_parse_body('{MYSQL_CLUSTER_REPLICA_ADD_EXPLAIN}');
	$prefix=str_replace('.','_',$page);
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	var {$prefix}timeout=0;
	
	
	
	function {$prefix}LoadMainPage(){
		YahooWin('870','$page?popup=yes','$title');
		{$prefix}demarre();
		}
		
	function {$prefix}demarre(){
	if(!YahooWinOpen()){return false;}
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=5-{$prefix}tant;
		if ({$prefix}tant <9 ) {                           
			{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",2000);
	      } else {
					if(!YahooWinOpen()){return false;}
					{$prefix}tant = 0;
					{$prefix}ChargeLogs();
					{$prefix}demarre();
	   }
	}

	function {$prefix}ChargeLogs(){
		LoadAjax('mysql-cluster-status','$page?status=yes');
	
	}
	
	
	function ManagerCluster(){
		YahooWin2('750','$page?manager=yes','$title2');
	}
	
	function ReplicatCluster(){
		YahooWin2('750','$page?replicat=yes','$title2');
	}	
	
	var x_MysqlClusterManagerSave= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		{$prefix}LoadMainPage();	
		ManagerCluster();
		}	

	var x_MysqlClusterReplicatSave= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		{$prefix}LoadMainPage();	
		ReplicatCluster();
		}		
	
	function MysqlClusterManagerSave(){
	    var XHR = new XHRConnection();
		XHR.appendData('EnableMysqlClusterManager',document.getElementById('EnableMysqlClusterManager').value);
		XHR.appendData('MysqlClusterManagerHostName',document.getElementById('MysqlClusterManagerHostName').value);
		XHR.appendData('MysqlClusterManagerID',document.getElementById('MysqlClusterManagerID').value);
		document.getElementById('clustermgm').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_MysqlClusterManagerSave);
		}	
		
		
	function MysqlClusterReplicatSave(){
	    var XHR = new XHRConnection();
		XHR.appendData('EnableMysqlClusterReplicat',document.getElementById('EnableMysqlClusterReplicat').value);
		XHR.appendData('MysqlClusterManagerTarget',document.getElementById('MysqlClusterManagerTarget').value);
		XHR.appendData('MysqlClusterReplicatID',document.getElementById('MysqlClusterReplicatID').value);
		document.getElementById('clustermgm').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_MysqlClusterReplicatSave);	
	
	}
	
	var x_AddReplica= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}	
		LoadAjax('clusterlist','$page?replicat-list=yes');
		}

	function ReplicatDelete(replica){
			var XHR = new XHRConnection();
			XHR.appendData('Delreplica',replica);
			document.getElementById('clusterlist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_AddReplica);
	}
	
	
	function AddReplica(){
		var txt='$MYSQL_CLUSTER_REPLICA_ADD_EXPLAIN';
		var replica=prompt(txt);
		if(replica){
			var XHR = new XHRConnection();
			XHR.appendData('Addreplica',replica);
			document.getElementById('clusterlist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_AddReplica);
		
		}
	}
		
	{$prefix}LoadMainPage();
	";
	
	echo $html;
}

function manager_save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableMysqlClusterManager",$_GET["EnableMysqlClusterManager"]);
	$sock->SET_INFO("MysqlClusterManagerHostName",$_GET["MysqlClusterManagerHostName"]);
	$sock->SET_INFO("MysqlClusterManagerID",$_GET["MysqlClusterManagerID"]);

	$tpl=new templates();
	echo html_entity_decode($tpl->_ENGINE_parse_body('{success}'));
}

function popup(){
	$sock=new sockets();
	$EnableMysqlClusterManager=$sock->GET_INFO('EnableMysqlClusterManager');
	$EnableMysqlClusterReplicat=$sock->GET_INFO('EnableMysqlClusterReplicat');	
		$status=status();
	$users=new usersMenus();
	if(!$users->MYSQL_NDB_MGMD_INSTALLED){
		$text=Paragraphe("danger64.png",'{NBD_MGMD_NOT_INSTALLED}','{NBD_MGMD_NOT_INSTALLED_TEXT}');
	}else{
		
		$manager=Paragraphe('server-master-check.png','{MYSQL_CLUSTER_MANAGER}','{MYSQL_CLUSTER_MANAGER_TEXT}',"javascript:ManagerCluster();");
		$replicat=Paragraphe('server-setup-64.png','{MYSQL_CLUSTER_REPLICAT}','{MYSQL_CLUSTER_REPLICAT_TEXT}',"javascript:ReplicatCluster();");
		
		if(($EnableMysqlClusterManager==1) && ($EnableMysqlClusterReplicat==1)){
			$sock->SET_INFO("EnableMysqlClusterReplicat",0);
			$EnableMysqlClusterReplicat=0;
		}
		
		if($EnableMysqlClusterManager==1){$replicat=null;}
		if($EnableMysqlClusterReplicat==1){$manager=null;}
		
		$text="$manager<br>$replicat";
		
	}
	
	
$html="
<H1>{MYSQL_CLUSTER}</H1>
<p class=caption>{MYSQL_CLUSTER_TEXT}</p>
<table style=width:100%' class=table_form>
<tr>
	<td valign='top' width=1%><img src='img/mysqlcluster-256.png'></td>
	<td valign='top' style='padding-left:10px'>". RoundedLightWhite("<div style='width:100%'>$text</div>")."</td>
	<td valign='top'><div id='mysql-cluster-status'>$status</div></td>
</tr>
</table>



";	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function manager(){
	$sock=new sockets();
	$net=new networking();
	$EnableMysqlClusterManager=$sock->GET_INFO('EnableMysqlClusterManager');
	$arrip=$net->ALL_IPS_GET_ARRAY();
	unset($arrip["127.0.0.1"]);
	$arrip[null]="{select}";
	$ip=Field_array_Hash($net->ALL_IPS_GET_ARRAY(),'MysqlClusterManagerHostName',$sock->GET_INFO('MysqlClusterManagerHostName'));
$replicat_list=replicat_list();
	for($i=1;$i<256;$i++){
		$arr[$i]=$i;
	}
	
	$id=Field_array_Hash($arr,'MysqlClusterManagerID',$sock->GET_INFO('MysqlClusterManagerID'));
	
	$enable=Paragraphe_switch_img('{ENABLE_MYSQL_CLUSTER_MANAGER}','{ENABLE_MYSQL_CLUSTER_MANAGER_TEXT}','EnableMysqlClusterManager',$EnableMysqlClusterManager,null,320);
	$add=Paragraphe('cluster-replica-add.png','{MYSQL_CLUSTER_REPLICA_ADD}','{MYSQL_CLUSTER_REPLICA_ADD_TEXT}',"javascript:AddReplica();");
	
	$form="<table style='width:100%'>
	<tr>
	<td class=legend nowrap>{MysqlClusterManagerHostName}:</td>
	<td>$ip</td>
	</tr>
	<tr>
	<td class=legend nowrap>{MysqlClusterManagerID}:</td>
	<td>$id</td>
	</tr>	
	</table>
	<br>$add
	";
	
	
	
$html="
<H1>{MYSQL_CLUSTER_MANAGER}</H1>
<div id='clustermgm'>
<p class=caption>{MYSQL_CLUSTER_MANAGER_TEXT}</p>
<table style=width:100%'>
<tr>
	<td valign='top'>$enable<br>".RoundedLightWhite("<div style='width:100%;height:100px;overlfow:auto' id='clusterlist'>$replicat_list</div>")."</td>
	<td valign='top' style='padding-left:10px'>". RoundedLightWhite($form)."</td>
</tr>
<tr>
<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:MysqlClusterManagerSave();\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>
</div>
";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function replicat(){
	$sock=new sockets();
	$net=new networking();
	$EnableMysqlClusterReplicat=$sock->GET_INFO('EnableMysqlClusterReplicat');
	$MysqlClusterManagerTarget=$sock->GET_INFO('MysqlClusterManagerTarget');


	for($i=2;$i<256;$i++){
		$arr[$i]=$i;
	}	
	
	$id=Field_array_Hash($arr,'MysqlClusterReplicatID',$sock->GET_INFO('MysqlClusterReplicatID'));
	
	$enable=Paragraphe_switch_img('{ENABLE_MYSQL_CLUSTER_REPLICAT}','{ENABLE_MYSQL_CLUSTER_REPLICAT_TEXT}','EnableMysqlClusterReplicat',$EnableMysqlClusterReplicat,null,320);
	
	$form="<table style='width:100%'>
	<tr>
	<td class=legend nowrap>{MysqlClusterManagerTarget}:</td>
	<td>". Field_text('MysqlClusterManagerTarget',$MysqlClusterManagerTarget,'width:95px')."</td>
	</tr>
	<tr>
	<td class=legend nowrap>{MysqlClusterManagerID}:</td>
	<td>$id</td>
	</tr>	
	</table>
	";
	
	
	
$html="
<H1>{MYSQL_CLUSTER_REPLICAT}</H1>
<div id='clustermgm'>
<p class=caption>{MYSQL_CLUSTER_REPLICAT_TEXT}</p>
<table style=width:100%'>
<tr>
	<td valign='top'>$enable</td>
	<td valign='top' style='padding-left:10px'>". RoundedLightWhite($form)."</td>
</tr>
<tr>
<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:MysqlClusterReplicatSave();\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>
</div>
";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function replicat_save(){
	$sock=new sockets();
	
	if($_GET["MysqlClusterManagerTarget"]=='127.0.0.1'){echo "127.0.0.1 => not allowed";exit;}
	
	$sock->SET_INFO('EnableMysqlClusterReplicat',$_GET["EnableMysqlClusterReplicat"]);
	$sock->SET_INFO('MysqlClusterManagerTarget',$_GET["MysqlClusterManagerTarget"]);
	$sock->SET_INFO('MysqlClusterReplicatID',$_GET["MysqlClusterReplicatID"]);
	$tpl=new templates();
	echo html_entity_decode($tpl->_ENGINE_parse_body('{success}'));
	
}

function replicat_add(){
	$server=$_GET["Addreplica"];
	if(trim($server)==null){return;}
	
	$sock=new sockets();
	$datas=explode("\n",$sock->GET_INFO('MysqlReplicasList'));
	$datas[]=$server;
	$sock->SaveConfigFile(implode($datas,"\n"),"MysqlReplicasList");
	}
	
function replica_delete(){
	$server=trim($_GET["Delreplica"]);
	$sock=new sockets();
	$datas=explode("\n",$sock->GET_INFO('MysqlReplicasList'));	
	while (list ($num, $server) = each ($datas) ){
		if(trim($server)==null){continue;}	
		$arr[trim($server)]==$server;
	}
	
	unset($arr[$server]);
	if(!is_array($arr)){
		$sock->SaveConfigFile(" ","MysqlReplicasList");
		
	}else{
		while (list ($num, $server) = each ($arr) ){
			$conf=$conf."$server\n";
		}
		$sock->SaveConfigFile($conf,"MysqlReplicasList");
	}
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{DELETE_MYSQL_REPLICA_INFOS}');	
	
	
}
function replicat_list(){
	$sock=new sockets();
	$datas=explode("\n",$sock->GET_INFO('MysqlReplicasList'));
	if(!is_array($datas)){return null;}
	$html="
	<H3>{MYSQL_REPLICAS}</H3>
	<table style='width:100%'>";
		while (list ($num, $server) = each ($datas) ){
			if(trim($server)==null){continue;}
			$html=$html . "
			<tr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><code style='font-size:13px'>$server</code></div>
				<td width=1%>". imgtootltip('ed_delete.gif',"{delete}","ReplicatDelete('$server');")."</td>
			</tr>
			";
			
		}
		
		$html=$html ."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	}
	
function status(){

	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('mysqlstatus'));
	$status1=DAEMON_STATUS_ROUND("ARTICA_MYSQL",$ini,null);
	$status2=DAEMON_STATUS_ROUND("MYSQL_CLUSTER_MGMT",$ini,null);
	$status3=DAEMON_STATUS_ROUND("MYSQL_CLUSTER_REPLICA",$ini,null);
	return $tpl->_ENGINE_parse_body("$status1<br>$status2<br>$status3");	
	
	
}




?>