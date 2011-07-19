<?php
include_once(dirname(__FILE__)."/ressources/class.gluster.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.gluster.samba.php");

$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}
if(isset($_GET["in-front-ajax"])){js();exit;}
if(isset($_GET["popup-add-cluster"])){add_cluster_popup();exit;}
if(isset($_GET["cluster_client"])){add_cluster_save();exit;}
if(isset($_GET["clients-list"])){clients_list();exit;}
if(isset($_GET["paths-list"])){paths_list();exit;}


if(isset($_GET["delete_cluster_client"])){del_cluster();exit;}
if(isset($_GET["popup-events-client"])){cluster_client_events();exit;}
index();


function js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page');";
}

function index(){
	$tpl=new templates();
	$ADD_CLUSTER_RESSOUCE=$tpl->_ENGINE_parse_body("{ADD_CLUSTER_RESSOUCE}");
	$events=$tpl->_ENGINE_parse_body("{events}");
	$page=CurrentPageName();
	$bt="<div style='text-align:right'>". button("$ADD_CLUSTER_RESSOUCE","AddClusterClient()")."</div>";
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1% valign='top'>". Paragraphe("cluster-replica-add.png",$ADD_CLUSTER_RESSOUCE,"{ADD_CLUSTER_RESSOUCE_TEXT}","javascript:AddClusterClient()")."</td>
	<td valign='top' width=99%>
	
	<div id='gluster-slaves-list' style='width:100%;height:200px;overflow:auto,padding:3px;border:1px solid #CCCCCC'></div>
	<div style='text-align:right;font-size:12px;margin:5px'>". button("{refresh}","RefreshGlusterClients()")."</div>
	
	<div id='gluster-paths-list' style='width:100%;height:200px;overflow:auto,padding:3px;border:1px solid #CCCCCC'></div>
	<div style='text-align:right;font-size:12px;margin:5px'>". button("{refresh}","RefreshGlusterPaths()")."</div>
	
	</td>
	</tr>
	</table>
	<script>
	
	function AddClusterClient(){
		YahooWin(350,'$page?popup-add-cluster=yes','$ADD_CLUSTER_RESSOUCE');
	}
	
	function RefreshGlusterClients(){
		LoadAjax('gluster-slaves-list','$page?clients-list=yes');
	}
	
	function RefreshGlusterPaths(){
		LoadAjax('gluster-paths-list','$page?paths-list=yes');
	}
	
	function GlusterEventsClient(ID){
		YahooWin(650,'$page?popup-events-client='+ID,'$events');
	}	
	
	var x_DelClientCLientBut= function (obj) {
		var response=obj.responseText;
		if(response.length>3){alert(response);return}
	    RefreshGlusterClients();
	}	
	
	function DelClientCLientBut(ID){
		var XHR = new XHRConnection();
		XHR.appendData('delete_cluster_client',ID);
		document.getElementById('gluster-slaves-list').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_DelClientCLientBut);
	}
	
	RefreshGlusterClients();
	RefreshGlusterPaths();
	</script>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function add_cluster_popup(){
	$page=CurrentPageName();
$html="
	<div id='cluster-add-div'>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{servername}:</td>
		<td valign='top' >". Field_text('cluster_client',null,'width:150px;font-size:13px;padding:3px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{listen_port}:</td>
		<td valign='top' >". Field_text('cluster_port',9000,'width:60px;font-size:13px;padding:3px')."</td>
	</tr>	
	<tr>
		<td valign='top' colspan=2 align=right><hr>". button("{add}","AddClientCLientBut()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var x_AddClientCLientBut= function (obj) {
		var response=obj.responseText;
		if(response.length>3){alert(response);return}
	    YahooWinHide();
	    RefreshGlusterClients();
	}		
	
	function AddClientCLientBut(){
		var XHR = new XHRConnection();
		XHR.appendData('cluster_client',document.getElementById('cluster_client').value);
		XHR.appendData('cluster_port',document.getElementById('cluster_port').value);
		document.getElementById('cluster-add-div').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_AddClientCLientBut);		
	}	
	
	</script>
	
	";	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function clients_list(){
	
	$html="
	<table class=tableView>
	<thead class=thead>
	<tr>
		<th colspan=3>{servername}</th>
		<th>{notified}</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	";
	
	$sql="SELECT * FROM glusters_clients ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$events=null;
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		$img="warning24.png";
		if($ligne["client_notified"]==1){$img="ok24.png";}
		$NotifToDelete=$ligne["NotifToDelete"];
		$delete=imgtootltip("delete-24.png","{delete}","DelClientCLientBut('{$ligne["ID"]}')");
		if($NotifToDelete==1){
			$text_color="#CCCCCC";
			$delete="&nbsp;";
		}else{$text_color="#000000";}
		
		$params=unserialize(base64_decode($ligne["parameters"]));
		if(is_array($params["LOGS"])){
			$events=imgtootltip("30-logs.png","{events}","GlusterEventsClient('{$ligne["ID"]}')");
		}
		
		$html=$html."
		<tr class=$cl>
		<td width=1%><img src='img/30-computer.png'></td>
		<td><code style='font-size:14px;color:$text_color;font-weight:bold'>{$ligne["client_ip"]}:{$ligne["client_port"]}</td>
		<td width=1%>$events</td>
		<td width=1% align='center'><img src='img/$img'></td>
		<td width=1% align='center'>$delete</td>
		</tr>
		";
		
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function cluster_client_events(){
	$q=new mysql();
	$ID=$_GET["popup-events-client"];
	$sql="SELECT client_ip,parameters FROM glusters_clients WHERE ID=$ID";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ip=$ligne["client_ip"];
	$array=unserialize(base64_decode($ligne["parameters"]));
	$logs=$array["LOGS"];
	
	@krsort($logs);
	$html="
	<div style='width:100%;height:350px;overflow:auto'>
	<table class=tableView>
	<thead class=thead>
	<tr>
		<th colspan=2>&nbsp;</th>
	</tr>
	</thead>
	";
	while (list ($num, $ligne) = each ($logs) ){
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		$html=$html."
		<tr class=$cl>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:12px'>[$ip]:: $ligne</td>
		</tr>
		";		
		
	}
	
	$html=$html."</table></div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	

}


function add_cluster_save(){
	$sql="INSERT INTO glusters_clients (client_ip,client_port,client_notified) 
	VALUES('{$_GET["cluster_client"]}','{$_GET["cluster_port"]}',0);";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?gluster-notify-clients=yes");
}

function del_cluster(){
	$sql="UPDATE glusters_clients SET NotifToDelete=1 WHERE ID='{$_GET["delete_cluster_client"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?gluster-delete-clients=yes");	
}

function paths_list(){
	$gls=new gluster_client();
	$array=$gls->implode_bricks();
	if(!is_array($array)){return null;}
	
$html="
	
	<table class=tableView>
	<thead class=thead>
	<tr>
		<th colspan=3>{mounted_cluster_folders}</th>
	</tr>
	</thead>
	";	
	
	$sock=new sockets();
	

	while (list ($path, $arrays) = each ($array) ){
		$path_encoded=base64_encode($path);
		$ok="ok24.png";
		
		$mounted=$sock->getFrameWork("cmd.php?glfs-is-mounted=$path_encoded");
		if($mounted<>1){$ok="danger24.png";}
		
	if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		$html=$html."<tr class=$cl>
		<td width=1%><img src='img/$ok'></td>
		<td><code style='font-size:12px'>$path</td>
		<td>". explode_paths($arrays)."</td>
		</tr>
		";
		
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function explode_paths($array){
	$html="<table style='width:100%'>";
	
	while (list ($num, $arrays) = each ($array) ){
	if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		$html=$html."<tr class=$cl>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:12px'>{$arrays["SERVER"]}:{$arrays["BRICKNAME"]}</td>
		</tr>";
	}
	
	$html=$html."</table>";
	return $html;
}




?>