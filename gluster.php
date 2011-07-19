<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.gluster.inc');


$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["add-cluster-popup"])){add_cluster_popup();exit;}
if(isset($_GET["cluster-list"])){echo add_cluster_list();exit;}
if(isset($_GET["add-cluster-client"])){echo add_cluster_save();exit;}
if(isset($_GET["notify-cluster-client"])){echo add_cluster_notify();exit;}
if(isset($_GET["delete-cluster-client"])){echo del_cluster();exit;}
if(isset($_GET["main-cluster-list"])){echo clusters_list();exit;}
if(isset($_GET["cluster-details-popup"])){echo clusters_details();exit;}
if(isset($_GET["replicator-cluster"])){replicator_cluster();exit;}
if(isset($_GET["CyrusImapDisableCluster"])){CyrusImapDisableCluster();exit;}

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_GLUSTER}');
	$prefix=str_replace(".","_",$page);

	
	$html="
	var {$prefix}tant=0;
	var {$prefix}reste=0;

	
		function {$prefix}Start(){
			RTMMail('750','$page?popup=yes');
			{$prefix}ClustersLoop();
		}
		
		function AddCluster(){
			YahooWin('550','$page?add-cluster-popup=yes');
		}
		
		
		function ClusterDetails(name){
			YahooWin2('550','$page?cluster-details-popup='+name);
		}
		
		
function {$prefix}ClustersLoop(){
	{$prefix}tant = {$prefix}tant+1;
	if(!RTMMailOpen()){return;}
	
	if ({$prefix}tant < 10 ) {                           
		setTimeout(\"{$prefix}ClustersLoop()\",1000);
      } else {
		{$prefix}tant = 0;
		{$prefix}ClustersStatus();
		{$prefix}ClustersLoop();		                              
   }
}


var x_{$prefix}CLusterList2= function (obj) {
	var response=obj.responseText;
	if(document.getElementById('cluster-list')){document.getElementById('cluster-list').innerHTML=response;}
}

var x_{$prefix}ClustersStatus= function (obj) {
		var response=obj.responseText;
		if(document.getElementById('mainclusterslist')){document.getElementById('mainclusterslist').innerHTML=response;}
		if(!YahooWinOpen()){return;}
		var XHR = new XHRConnection();
		XHR.appendData('cluster-list','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}CLusterList2);
	}	


	function {$prefix}ClustersStatus(){
		var XHR = new XHRConnection();
		XHR.appendData('main-cluster-list','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ClustersStatus);
	}
		
		
var x_AddClusterButton= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
	    LoadAjax('cluster-list','$page?cluster-list=yes');
	}		
	
	
	function AddClusterButton(){
		var XHR = new XHRConnection();
		XHR.appendData('add-cluster-client',document.getElementById('cluster_client').value);
		document.getElementById('cluster-list').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_AddClusterButton);
	}
	
	
	
	function ClusterNotify(cluster){
		var XHR = new XHRConnection();
		XHR.appendData('notify-cluster-client',cluster);
		document.getElementById('cluster-list').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_AddClusterButton);	
	}
	
	function GlusterDelete(cluster){
		var XHR = new XHRConnection();
		XHR.appendData('delete-cluster-client',cluster);
		document.getElementById('cluster-list').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_AddClusterButton);		
	}
	
var x_SetAsReplicator= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
	    YahooWin2('550','$page?cluster-details-popup='+document.getElementById('popup-server-name-details').value);
	}		
	
	function SetAsReplicator(name){
		var XHR = new XHRConnection();
		XHR.appendData('replicator-cluster',name);
		document.getElementById('replicator').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SetAsReplicator);		
	}
	
	function CyrusImapDisableCluster(){
		var CyrusImapDisableCluster=0;
		if(document.getElementById('CyrusImapDisableCluster').checked==true){
			CyrusImapDisableCluster=1;
		}
	
		var XHR = new XHRConnection();
		XHR.appendData('CyrusImapDisableCluster',CyrusImapDisableCluster);
		XHR.sendAndLoad('$page', 'GET');	
	
	}
			
	
	{$prefix}Start();";
	
	echo $html;
}

function popup(){
	$list=clusters_list();
	$addreplica=Paragraphe("cluster-replica-add.png","{ADD_CLUSTER_RESSOUCE}",'{ADD_CLUSTER_RESSOUCE_TEXT}',"javascript:AddCluster()");
	$sock=new sockets();
	$CyrusImapDisableCluster=$sock->GET_INFO("CyrusImapDisableCluster");
	
	$disablecluster="<table style='width:100%'>
	<tr ". CellRollOver().">
		<td valign='top' align='right'>{disable_cluster_onthisserver}:</td>
		<td width=0.5%>". Field_checkbox('CyrusImapDisableCluster',1,$CyrusImapDisableCluster,"CyrusImapDisableCluster();")."</td>
	</tr>
	</table>
	
	";
	
	
	
	$html="<H1>{APP_GLUSTER}</H1>
	<table style='width:100%'>
	<tr >
		<td valign='top' >$addreplica$disablecluster</td>
		<td valign='top' >". RoundedLightWhite("<div id='mainclusterslist' style='width:100%;height:300px;overflow:auto'>$list</div>")."</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function add_cluster_popup(){
	
	$list=add_cluster_list();
$html="<H1>{ADD_CLUSTER_RESSOUCE}</H1>
<p class=caption>{ADD_CLUSTER_RESSOUCE}</p>

	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend>{servername}:</td>
		<td valign='top' >". Field_text('cluster_client',null,'width:150px')."</td>
		<td valign='top' ><input type='button' OnClick=\"javascript:AddClusterButton()\" value='{add}&nbsp;&raquo;'></td>
	</tr>
	</table>
	". RoundedLightWhite("<div style='width:100%;height:200px;overflow:auto' id='cluster-list'>$list</div>")."
	
	";	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function add_cluster_save(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cluster-add={$_GET["add-cluster-client"]}");
}

function del_cluster(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cluster-delete={$_GET["delete-cluster-client"]}");
	
}

function add_cluster_list(){
	$gl=new gluster();
	$array=$gl->clients;
	if(!is_array($array)){return null;}
	
	$html="<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($array) ){
		
		
		$text="{waiting_settings}";
		$img="status_service_wait.png";
		if($gl->PARAMS_CLIENTS[$num]["notify"]==1){
			$img="status_service_wait.png";
			$text="{order_scheduled}";
		}
		
		if($gl->PARAMS_CLIENTS[$num]["notified"]==1){
			$img="status_service_run.png";
			$text="{gluster_settings_saved}";
		}		
		
	if($gl->PARAMS_CLIENTS[$num]["error"]==1){
			$img="status_service_removed.png";
			$text=$gl->PARAMS_CLIENTS[$num]["error_text"];
		}	


	if($gl->STATUS_CLIENTS[$num]["running"]==1){
		$img="status_service_run.png";
		$text2="<br><i><span style='font-size:10px'>{running} PID {$gl->STATUS_CLIENTS[$num]["master_pid"]}</i></span>";
	}else{
			if(isset($gl->STATUS_CLIENTS[$num]["running"])){
				if($gl->STATUS_CLIENTS[$num]["running"]==0){
					$img="status_service_removed.png";
					 $text2="&nbsp;|<strong style=color:red>{stopped}</strong>";
				}
			}
		}
		
		
			
		
		
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1% valign='top'><img src='img/base.gif'></td>
			<td valign='top'><strong><code style='font-size:13px'>$num</strong></code></td>
			<td width=1% valign='top'><img src='img/$img'></td>
			<td><strong>$text</strong>$text2</td>
			<td width=1% valign='top'>". imgtootltip("ed_delete.gif","{delete}","javascript:GlusterDelete('$num');")."</td>
			<td valign='top'><input type='button' OnClick=\"javascript:ClusterNotify('$num')\" value='{notify}&nbsp;&raquo;' style='margin:0'></td>
		</tr>
		";
		
	}
	
	$html=$html."</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
}

function clusters_list(){
	$gl=new gluster();
	$array=$gl->clients;
	if(!is_array($array)){return null;}
	
	//server-warning-64.png
	//server-ok-64.png
	//server-refresh-64.png
	
	$html="<table style='width:100%'><tr>";
	$count=0;
	while (list ($num, $ligne) = each ($array) ){
		$img="server-refresh-64.png";
		$text2="{waiting_settings}";
		$count=$count+1;
		if($gl->PARAMS_CLIENTS[$num]["notify"]==1){
			$img="server-refresh-64.png";
			$text="{order_scheduled}";
		}
		
		if($gl->PARAMS_CLIENTS[$num]["notified"]==1){
			$img="server-ok-64.png";
			$text="{gluster_settings_saved}";
		}		
		
	if($gl->PARAMS_CLIENTS[$num]["error"]==1){
			$img="server-error-64.png";
			$text=$gl->PARAMS_CLIENTS[$num]["error_text"];
		}		
		
		if($gl->STATUS_CLIENTS[$num]["running"]==1){
		  $img="server-ok-64.png";
		 $text2="{running} PID {$gl->STATUS_CLIENTS[$num]["master_pid"]}";
		}else{
			if(isset($gl->STATUS_CLIENTS[$num]["running"])){
				
				if($gl->STATUS_CLIENTS[$num]["running"]==0){
					$img="server-error-64.png";
					 $text2="&nbsp;|<strong style=color:red>{stopped}</strong>";
				}
			}
		}
		
		
		
		if($count>2) {$tr="</tr><tr>";$count=0;}else{$tr=null;}
		$js="javascript:ClusterDetails('$num')";
		$html=$html."$tr<td valign='top'>". Paragraphe($img,"$num","$text&nbsp;|&nbsp;$text2",$js)."</td>";
		
	}
	
	$html=$html."</tr></table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
}

function add_cluster_notify(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?notify-clusters={$_GET["notify-cluster-client"]}");
}

function replicator_cluster(){
	$replic=$_GET["replicator-cluster"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?notify-clusters=$replic");
	
}


function clusters_details(){
		$name=$_GET["cluster-details-popup"];
		$status=cluster_details_status($name);
		$gluster=new gluster();
		$conf=$gluster->STATUS_CLIENTS[$name];
		$bricks=explode(',',$conf["bricks"]);
		if(is_array($bricks)){
			
			$ff="<table style='width:100%'>";
			while (list ($num, $ligne) = each ($bricks) ){
				$ff=$ff."
				<tr>
					<td><img src='img/folderopen-share.gif'>". $conf[$ligne]."</td>
				</tr>
				";}
				
			$ff=$ff."</table>";
			
			$ff=RoundedLightWhite("<h3>{replicated_folders}</H3>$ff");
			
		}
	
	
	
	$html="<H1>$name</H1>
	<input type='hidden' id='popup-server-name-details' value='$name'>
	
	<div id='replicator'>
	<table style='width:100%'>
		<tr>
			<td valign='top' width=50%><H3>{status}</H3>
			$status
			<div style='width:100%;text-align:right;margin:5px'>
				<input type='button' OnClick=\"javascript:SetAsReplicator('$name');\" value='{notify}&nbsp;&raquo' style='font-size:14px'>
			</div>
			</td>
			<td valign='top'>$ff</td>
		</tr>
	</table>
	</div>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function cluster_details_status($name){
	$gluster=new gluster();
	$statusL[$name]=$gluster->STATUS_CLIENTS[$name];
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->_params=$statusL;
	$status=DAEMON_STATUS_ROUND("$name",$ini);	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);
	
}

function CyrusImapDisableCluster(){
	$sock=new sockets();
	$sock->SET_INFO("CyrusImapDisableCluster",$_GET["CyrusImapDisableCluster"]);
	$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes");
}



?>