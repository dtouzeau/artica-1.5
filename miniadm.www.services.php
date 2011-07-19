<?php
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.apache.inc");



	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["tabs"])){tabs_admin();exit;}
	if(isset($_GET["freewebs"])){freewebs_main();exit;}
	js();



function js(){
	$page=CurrentPageName();
	$users=new usersMenus();
	if($users->AsWebMaster){
		echo "LoadAjax('BodyContent','$page?tabs=yes');";
		return;
	}	
	echo "LoadAjax('BodyContent','$page?popup=yes');";
	
}

function tabs_admin(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$arr["freewebs"]="{manage_websites}";
	$arr["popup"]="{myWebServices}";
	
	
	while(list( $num, $ligne ) = each ($arr)){
		if($num=="freewebs"){
			$toolbox[]=$tpl->_ENGINE_parse_body("<li><a href=\"freeweb.php?webs=yes\"><span>$ligne</span></a></li>");
			continue;
		}
		$toolbox[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>");
	}
	
	
	
	
	$html = "<div id='container-www-tabs' style='width:99%;margin:0px;background-color:white'>
			<ul>
				" . implode ( "\n\t", $toolbox ) . "
			</ul>
		</div>
		<script>
		 $(document).ready(function() {
			$(\"#container-www-tabs\").tabs();});
		</script>";
	echo $html;
	
}




function popup(){

	
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();	
		
	$sock=new sockets();
	$FreeWebListen=$sock->GET_INFO("FreeWebListen");
	$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
	$FreeWebListenSSLPort=$sock->GET_INFO("FreeWebListenSSLPort");	
	if($FreeWebListenPort==null){$FreeWebListenPort=80;}
	if($FreeWebListenSSLPort==null){$FreeWebListenSSLPort=443;}	
	$users=new usersMenus();
	$apache=new vhosts();
	$TEXT_ARRAY=$apache->TEXT_ARRAY;
	$IMG_ARRAY_64=$apache->IMG_ARRAY_64;
	$sql="SELECT * FROM freeweb WHERE ou='{$_SESSION["ou"]}' ORDER BY servername";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	
	
	if($users->ZARAFA_INSTALLED){
				$html=$html."
		<table class=form style='width=98%'>
		<tr>
			<td width=1% valign='top'><img src='img/64-restore-mailbox.png' style='margin:8px'></td>
			<td>
				<div style='font-size:14px;font-weight:bold'>{mailbox}</div>
				<table style='width:100%'>
				<tr>
					<td class=legend>{mailbox_server_address}:</td>{$_SERVER["SERVER_NAME"]}:143</td>
				</tr>
				<tr>
					<td class=legend>{relay_address}:</td>{$_SERVER["SERVER_NAME"]}:25</td>
				</tr>
				</table>
			</td>
		</tr>
		</table><br>";
		
		
	}
	
	

	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$img="domain-main-64.png";
		$explain=null;
		$uriprefix="http://";
		$port=$FreeWebListenPort;
		if($ligne["useSSL"]==1){$uriprefix="https://";$port=$FreeWebListenSSLPort;}
		
		
		if($ligne["groupware"]<>null){
			
			$explain="<div style='font-size:13px;font-weight:bold'>{{$TEXT_ARRAY[$ligne["groupware"]]["TITLE"]}}</div>
			<div class=explain>{{$TEXT_ARRAY[$ligne["groupware"]]["TEXT"]}}</div>";
			$img=$IMG_ARRAY_64[$ligne["groupware"]];
		}
		
		
		$html=$html."
		<table class=form style='width=98%'>
		<tr>
			<td width=1% valign='top'><img src='img/$img' style='margin:8px'></td>
			<td>
				<div style='font-size:14px;font-weight:bold'>{$ligne["servername"]}</div>$explain
				<div style='text-align:right'><a href=\"$uriprefix{$ligne["servername"]}:$port\"
				style='width:11px;text-decoaration:underline'
				>$uriprefix{$ligne["servername"]}:$port</a>
			</td>
		</tr>
		</table><br>";
		
	}
	
	$html=$html."
	
	
	<script>
		LoadAjax('tool-map','miniadm.toolbox.php?script=". urlencode($page)."');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

