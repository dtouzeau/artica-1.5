<?php
session_start();
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.openvpn.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.tcpip.inc');
include_once('ressources/class.mysql.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die("alert('no access');");}


if(isset($_GET["js-inline"])){js_inline();exit;}
if(isset($_GET["popup"])){popup();exit;}

if(isset($_GET["remote-site-js"])){remote_site_js();exit;}
if(isset($_GET["remote-site"])){remote_site_tabs();exit;}
if(isset($_GET["remotesite-config"])){remote_site_edit();exit;}
if(isset($_GET["remotesite-routes"])){remote_site_routes();exit;}
if(isset($_GET["remotesite-routes-list"])){remote_site_routes_list();exit;}


if(isset($_POST["remote-site-route-add"])){remote_site_routes_add();exit;}
if(isset($_POST["remote-site-route-del"])){remote_site_routes_del();exit;}


if(isset($_POST["siteid"])){remote_site_save();exit;}
if(isset($_GET["remotesiteslist"])){echo remote_sitelist();exit;}
if(isset($_GET["delete-siteid"])){remote_site_delete();exit;}
if(isset($_GET["config-site"])){remote_site_config();exit;}
js();



function js_inline(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?infront=yes');";	
}


function remote_site_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{REMOTE_SITES_VPN}','index.openvpn.php');
	$ADD_REMOTE_SITES_VPN=$tpl->_ENGINE_parse_body('{REMOTE_SITES_VPN}&nbsp;&raquo;{ADD_REMOTE_SITES_VPN}','index.openvpn.php');
	if(!is_numeric($_GET["siteid"])){$_GET["siteid"]=0;}
	if($_GET["siteid"]>0){
		$q=new mysql();
		$sql="SELECT * FROM vpnclient WHERE ID='{$_GET["siteid"]}'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$title="{$ligne["sitename"]} - {$ligne["IP_START"]}/{$ligne["netmask"]}";
	}else{
		$title=$ADD_REMOTE_SITES_VPN;
	}
	echo "YahooWin4(640,'$page?remote-site={$_GET["siteid"]}&siteid={$_GET["siteid"]}','$title');";
	
}

function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{REMOTE_SITES_VPN}','index.openvpn.php');
	$ADD_REMOTE_SITES_VPN=$tpl->_ENGINE_parse_body('{ADD_REMOTE_SITES_VPN}','index.openvpn.php');
	$DOWNLOAD_CONFIG_FILES=$tpl->_ENGINE_parse_body('{DOWNLOAD_CONFIG_FILES}','users.openvpn.index.php');
	$page=CurrentPageName();
	
	$function="RemoteVPNStart()";
	$internalcode="YahooWin3(600,'$page?popup=yes','$title');";
	
	if(isset($_GET["infront"])){
		echo "<div id='remotesites-div'></div>";
		$function="RemoteVPNStart()";
		$internalcode="LoadAjax('remotesites-div','$page?popup=yes')";
		}
	
	$page=CurrentPageName();
	if(isset($_GET["infront"])){echo "<script>";}
	$html="
	
	function RemoteVPNStart(){
		$internalcode
	}
	
	function EditVPNRemoteSite(siteid){
		Loadjs('$page?remote-site-js=yes&siteid='+siteid);
		
	}
	
	function VPNRemoteSiteRefreshList(){
		LoadAjax('remotesiteslist','$page?remotesiteslist=yes');
	}
	

	
	function RemoteVPNDelete(siteid){
		var XHR = new XHRConnection();
		XHR.appendData('delete-siteid',siteid);
		XHR.sendAndLoad('$page', 'GET',x_EditOpenVPNSite);	
	}
	
	function VPNRemoteSiteConfig(siteid){
		YahooWin5(540,'$page?config-site='+siteid,'$DOWNLOAD_CONFIG_FILES');
	}
	
	
	$function
	";
	
	echo $html;
if(isset($_GET["infront"])){echo "</script>";}
	
}


function popup(){
	
	//$add=Paragraphe('HomeAdd-64.png','{ADD_REMOTE_SITES_VPN}','{ADD_REMOTE_SITES_VPN_TEXT}',"javascript:EditVPNRemoteSite('');",null,210,null,0,false);
	
	
	$list=remote_sitelist();
	
	$html="
	
	<div id='remotesiteslist' style='width:100%;height:250px;overflow:auto'>$list</div></td>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function remote_site_delete(){
	$sql="DELETE FROM vpnclient WHERE ID={$_GET["delete-siteid"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	if($q->ok){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?artica-meta-openvpn-sites=yes");
		$sock->getFrameWork("services.php?openvpn=yes");		
	}
	
	
}

function remote_site_tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	if(!is_numeric($_GET["siteid"])){$_GET["siteid"]=0;}
	$array["remotesite-config"]="{settings}";
	if($_GET["siteid"]>0){
		$array["remotesite-routes"]="{routes}";
	}
	
		while (list ($num, $ligne) = each ($array) ){
	
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&siteid={$_GET["siteid"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_remotesitetab style='width:100%;height:500px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_remotesitetab\").tabs();});
		</script>";	
}

function remote_site_edit(){
	$siteid=$_GET["siteid"];
	$explain="<div class=explain>{ADD_REMOTE_SITES_VPN_TEXT}</div>";
	$page=CurrentPageName();
	$openvpn=new openvpn();
	$IP_START=$openvpn->main_array["GLOBAL"]["IP_START"];
	if($IP_START==null){$IP_START="10.8.0.0";}
		
	if($siteid>0){
		$explain=null;
		$q=new mysql();
		$sql="SELECT * FROM vpnclient WHERE ID='$siteid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	}
	
	$title=$ligne["sitename"];
	if(!is_numeric($ligne["FixedIPAddr"])){$ligne["FixedIPAddr"]=0;}
	$button_title="{edit}";
	
	if($title==null){
		$title="{ADD_REMOTE_SITES_VPN}";
		$button_title="{add}";
	}
	
	if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#", $ligne["IP_START"],$re)){
		$IP_START_1=$re[1];
		$IP_START_2=$re[2];
		$IP_START_3=$re[3];
	}
	
	if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#", $ligne["netmask"],$re)){
		$netmask_1=$re[1];
		$netmask_2=$re[2];
		$netmask_3=$re[3];
		$netmask_4=$re[4];
	}

	if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#", $ligne["wakeupip"],$re)){
		$wake_1=$re[1];
		$wake_2=$re[2];
		$wake_3=$re[3];
		$wake_4=$re[4];
	}

	if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#", $IP_START,$re)){
		$server_ip_start_1=$re[1];
		$server_ip_start_2=$re[2];
		$server_ip_start_3=$re[3];
		$server_ip_start_4=$re[4];
	}			
	
	$html="
	
	<input type='hidden' id='siteid' value='$siteid'>
	<table style='width:99.5%' class=form>
		<tr>
			<td class=legend nowrap>{site_name}:</td>
			<td>". Field_text("sitename",$ligne["sitename"],"width:220px;font-size:14px;padding:3px")."</td>
			<td></td>
		</tr>
		<tr>
			<td class=legend nowrap>{from_ip_address}:</td>
			<td>
				<table style='width:5%'>
				<tr>
					<td width=1%>". Field_text("IP_START_1",$IP_START_1,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("IP_START_2",$IP_START_2,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("IP_START_3",$IP_START_3,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1% align=center style='font-size:14px'>0</td>
				</tr>
				</table>
			</td>
			
		<td>". help_icon("{openvpn_remitesite_ip_explain}")."</td>
		</tr>
		<tr>
			<td class=legend nowrap>{netmask}:</td>
			<td>
			
			<table style='width:5%'>
				<tr>
					<td width=1%>". Field_text("netmask_1",$netmask_1,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("netmask_2",$netmask_2,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("netmask_3",$netmask_3,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("netmask_4",$netmask_4,"width:35px;font-size:14px;padding:3px")."</td>
				</tr>
				</table>
			</td>
			<td>". help_icon("{openvpn_netmask_ip_explain}")."</td>
		</tr>

		<tr>
			<td class=legend nowrap>{fixed_ip_addr}:</td>
			<td>
			<table style='width:5%'>
				<tr>
					<td width=1%>". Field_text("server_ip_start_1",$server_ip_start_1,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("server_ip_start_2",$server_ip_start_2,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("server_ip_start_3",$server_ip_start_3,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("FixedIPAddr",$ligne["FixedIPAddr"],"width:35px;font-size:14px;padding:3px")."</td>
				</tr>
				</table>
			</td>
			<td>". help_icon("{openvpn_fixed_ip_addr_explain}")."</td>
		</tr>		
		
		
		<tr>
			<td class=legend nowrap>{wake_up_ip}:</td>
			<td>
			
			<table style='width:5%'>
				<tr>
					<td width=1%>". Field_text("wake_1",$wake_1,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("wake_2",$wake_2,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("wake_3",$wake_3,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("wake_4",$wake_4,"width:35px;font-size:14px;padding:3px")."</td>
				</tr>
				</table>
			</td>
			<td>". help_icon("{wakeup_vpn_ip_explain}")."</td>
		</tr>			
		
		
		<tr>
			<td colspan=3 align='right'>
			<hr>
			". button($button_title,"EditOpenVPNSitelocal2()")."
				
			</td>
		</tr>			
	</table>
	<script>
	var x_EditOpenVPNSitelocal2= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;}
		YahooWin4Hide();
		VPNRemoteSiteRefreshList();
		
	}			
	
	function EditOpenVPNSitelocal2(){
		var ipstart=document.getElementById('IP_START_1').value+'.'+document.getElementById('IP_START_2').value+'.'+document.getElementById('IP_START_3').value+'.0';
		var netmask=document.getElementById('netmask_1').value+'.'+document.getElementById('netmask_2').value+'.'+document.getElementById('netmask_3').value+'.'+document.getElementById('netmask_4').value;
		var wakeup=document.getElementById('wake_1').value+'.'+document.getElementById('wake_2').value+'.'+document.getElementById('wake_3').value+'.'+document.getElementById('wake_4').value;
		var XHR = new XHRConnection();
		XHR.appendData('siteid','$siteid');
		XHR.appendData('sitename',document.getElementById('sitename').value);
		XHR.appendData('FixedIPAddr',document.getElementById('FixedIPAddr').value);
		XHR.appendData('IP_START',ipstart);
		XHR.appendData('netmask',netmask);
		XHR.appendData('wakeup',wakeup);
		XHR.sendAndLoad('$page', 'POST',x_EditOpenVPNSitelocal2);	
		
	}
	
	function disbaleSomeFields(){
		document.getElementById('server_ip_start_1').disabled=true;
		document.getElementById('server_ip_start_2').disabled=true;
		document.getElementById('server_ip_start_3').disabled=true;
		
	
	}
	disbaleSomeFields();
</script>


	
	
	
	";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'index.openvpn.php');
	
	
	
}

function remote_sitelist(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?OpenVPNServerSessions=yes')));
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match("#([0-9\.]+),(.+?),([0-9\.\:]+),(.+)#", $ligne,$re)){$array[$re[2]]["LOCAL_IP"]=$re[1];}
		if(preg_match('#(.+?),([0-9\.\:]+),([0-9]+),([0-9]+),(.+)#',$ligne,$re)){
			if(preg_match("#(.+?):#", $re[2],$ri)){$re[2]=$ri[1];}
			$array[$re[1]]["REMOTE_IP"]=$re[2];
			$array[$re[1]]["b_received"]=$re[3];
			$array[$re[1]]["b_sent"]=$re[4];
			$array[$re[1]]["time"]=$re[5];
		}
	}	
	
	
	$sql="SELECT * FROM vpnclient WHERE connexion_type=1 ORDER BY sitename DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<th width=1%>". imgtootltip("plus-24.png","{ADD_REMOTE_SITES_VPN}","EditVPNRemoteSite('')")."</th>
	<th>{site_name}</th>
	<th nowrap>{from_ip_address}</th>
	<th>{netmask}</th>
	<th>{download}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
	";
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$js="EditVPNRemoteSite('{$ligne["ID"]}')";
			$jsDownload="VPNRemoteSiteConfig('{$ligne["ID"]}');";
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ahref="<a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-size:16px;text-decoration:underline'>";
			$status="danger32.png";
			if($array[$ligne["sitename"]]["REMOTE_IP"]<>null){$status="32-green.png";}
			$status=imgtootltip($status,$array[$ligne["sitename"]]["REMOTE_IP"],$js);
			$html=$html. "
			<tr class=$classtr>
			<td width=1%>$status</td>
			<td  nowrap style='font-size:16px'>$ahref{$ligne["sitename"]}</a></td>
			<td nowrap style='font-size:16px' width=1%>$ahref{$ligne["IP_START"]}</a></td>
			<td nowrap style='font-size:16px' width=1%>$ahref{$ligne["netmask"]}</a></td>
			<td width=1% align='middle' valign='center'>". imgtootltip("32-winzip.png","{download}","$jsDownload")."</td>
			<td width=1%>". imgtootltip("delete-32.png","{delete}","RemoteVPNDelete2('{$ligne["ID"]}','{$ligne["sitename"]}')")."</td>
			</tr>
			
			";
		
		}
	$tpl=new templates();	
	$confirm=$tpl->javascript_parse_text("{delete} ?");
	$html=$html."</table>
	<div style='width:100%;text-align:right'><hr>".button("{ADD_REMOTE_SITES_VPN}","EditVPNRemoteSite('')")."</div>
	
	<script>
	
	var x_RemoteVPNDelete2= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;}
		VPNRemoteSiteRefreshList();
		
	}	
	
		function RemoteVPNDelete2(siteid,stname){
		if(confirm('$confirm '+stname)){
			var XHR = new XHRConnection();
			XHR.appendData('delete-siteid',siteid);
			XHR.sendAndLoad('$page', 'GET',x_RemoteVPNDelete2);
		}	
	}
	
	</script>
	
	";
	
	
	
	return $tpl->_ENGINE_parse_body($html,"users.openvpn.index.php");
	
}



function remote_site_save(){
	$sitename=$_POST["sitename"];
	$IP_START=$_POST["IP_START"];
	$netmask=$_POST["netmask"];
	$siteid=$_POST["siteid"];
	$wakeupip=$_POST["wakeup"];
	$FixedIPAddr=$_POST["FixedIPAddr"];
	$connexion_type=1;
	
	if($sitename==null){$error[]="{site_name}";}
	if($IP_START==null){$error[]="{from_ip_address}";}
	if($netmask==null){$error[]="{netmask}";}
	
	$ip=new ip();
	if(!$ip->isValid($IP_START)){$error[]="{from_ip_address}";}
	
	if(count($error)>0){
		echo "{error}:".implode("\n",$error)." =NULL\n";
		exit;
	}
	
	
	if($siteid==0){
		$sql="INSERT INTO vpnclient (sitename,IP_START,netmask,connexion_type,wakeupip,FixedIPAddr) VALUES('$sitename','$IP_START','$netmask',1,'$wakeupip','$FixedIPAddr')";
	}else{
		$sql="UPDATE vpnclient SET sitename='$sitename',IP_START='$IP_START',netmask='$netmask',wakeupip='$wakeupip',FixedIPAddr='$FixedIPAddr' WHERE ID='$siteid'";
	}
	
	$q=new mysql();
	$q->BuildTables();
	$q->QUERY_SQL($sql,"artica_backup");
	
	
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?artica-meta-openvpn-sites=yes");
	$sock=new sockets();
	$sock->getFrameWork("services.php?openvpn=yes");	
	
	}
	
function remote_site_config(){
	$vpn=new openvpn();
	$siteid=$_GET["config-site"];
	
		$q=new mysql();
		$sql="SELECT * FROM vpnclient WHERE ID='$siteid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
		$sitename=$ligne["sitename"];
		$sitename=str_replace(" ","_",$sitename);
		$sitename=strtolower($sitename);

	$vpn->site_id=$siteid;	
	$config=$vpn->BuildClientconf($sitename);
	$uid=$sitename;
	$sock=new sockets();
	$sock->SaveConfigFile($config,"$uid.ovpn");
	//$datas=$sock->getfile('OpenVPNGenerate:'.$uid);	
	$datas=$sock->getFrameWork("openvpn.php?build-vpn-user=$uid&basepath=".dirname(__FILE__)."&site-id=$siteid");
	$tbl=explode("\n",$datas);
	$tbl=array_reverse($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line)==null){continue;}
		
		
		$color="black";
		if(preg_match("#error#",$line)){$color="red";}
		if(preg_match("#warning#",$line)){$color="red";}
		if(preg_match("#unable to#",$line)){$color="red";}
		
		
		$html=$html . "<div><code style='font-size:10px;color:$color;'>" . htmlentities($line)."</code></div>";
		
	}
	
	if(is_file('ressources/logs/'.$uid.'.zip')){
		$download="
		<center>
			<a href='ressources/logs/".$uid.".zip'><img src='img/64-winzip.png' title=\"{DOWNLOAD_CONFIG_FILES}\" style='padding:8Px;border:1px solid #055447;margin:3px'></a>
		</center>
		";
		
	}
	
	$download=$download."<hr>";
	
	$html="
	
	
	$download
	<H3>{events}</H3>
	". RoundedLightWhite("<div style='width:100%;height:200px;overflow:auto'>$html</div>");
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function remote_site_routes(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$siteid=$_GET["siteid"];

	$html="
	<div class=explain>{openvpn_remotesites_routes_explain}</div>
	<center>
		<table style='width:80%' class=form>
		<tr>
			<td class=legend nowrap>{from_ip_address}:</td>
			<td>
				<table style='width:5%'>
				<tr>
					<td width=1%>". Field_text("route_vpn_1",$IP_START_1,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("route_vpn_2",$IP_START_2,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("route_vpn_3",$IP_START_3,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1% align=center style='font-size:14px'>0</td>
				</tr>
				</table>
			</td>
			
		<td>". help_icon("{openvpn_remitesite_ip_explain}")."</td>
		</tr>
		<tr>
			<td class=legend nowrap>{netmask}:</td>
			<td>
			
			<table style='width:5%'>
				<tr>
					<td width=1%>". Field_text("netmask_vpn_1",$netmask_1,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("netmask_vpn_2",$netmask_2,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("netmask_vpn_3",$netmask_3,"width:35px;font-size:14px;padding:3px")."</td>
					<td width=1% align=center style='font-size:14px'>&nbsp;.&nbsp;</td>
					<td width=1%>". Field_text("netmask_vpn_4",$netmask_4,"width:35px;font-size:14px;padding:3px")."</td>
				</tr>
				</table>
			</td>
			<td>". help_icon("{openvpn_netmask_ip_explain}")."</td>
	
		
		
		
		<tr>
			<td colspan=3 align='right'><hr>". button("{add}","AddOpenVPnRemoteSiteRoute()")."</td></tr>
	</table>
	</center>
	<div id='openvpn-remotesite-routes-list' style='height:220px;overflow:auto'></div>
	
	<script>
	var x_AddOpenVPnRemoteSiteRoute= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;}
		OpenVPNRemoteSiteRefreshRoutes();
	}			
	
	function AddOpenVPnRemoteSiteRoute(){
		var ipstart=document.getElementById('route_vpn_1').value+'.'+document.getElementById('route_vpn_2').value+'.'+document.getElementById('route_vpn_3').value+'.0';
		var netmask=document.getElementById('netmask_vpn_1').value+'.'+document.getElementById('netmask_vpn_2').value+'.'+document.getElementById('netmask_vpn_3').value+'.'+document.getElementById('netmask_vpn_4').value;
		var XHR = new XHRConnection();
		XHR.appendData('siteid','$siteid');
		XHR.appendData('remote-site-route-add',ipstart);
		XHR.appendData('netmask',netmask);
		AnimateDiv('openvpn-remotesite-routes-list');
		XHR.sendAndLoad('$page', 'POST',x_AddOpenVPnRemoteSiteRoute);	
	}
	
	function OpenVPNRemoteSiteRefreshRoutes(){
		LoadAjax('openvpn-remotesite-routes-list','$page?remotesite-routes-list=yes&siteid=$siteid');
	}
	OpenVPNRemoteSiteRefreshRoutes();
</script>	
	";
	
echo $tpl->_ENGINE_parse_body($html);	
	
}

function remote_site_routes_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
		
$html="
<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:80%'>
<thead class='thead'>
	<th width=1%>&nbsp;</th>
	<th>&nbsp;</th>
</thead>
<tbody class='tbody'>";	

	$siteid=$_GET["siteid"];
	$q=new mysql();
	$sql="SELECT remote_site_routes FROM vpnclient WHERE ID='$siteid'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	if(!$q->ok){echo "<H2>$q->mysql_error;</H2>";}
	$remote_site_routes=unserialize(base64_decode($ligne["remote_site_routes"]));
	
	while (list ($num, $ligne) = each ($remote_site_routes) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:16px;letter-spacing:2px;font-weight:bold' width=99%>$num&nbsp;-&nbsp;$ligne</td>
		<td width=1%>". imgtootltip("delete-32.png","{delete}","OpenVPNRemoteSiteDeleteRoutes('$num')")."</td>
		</tr>
		";
		
	}
	
	$html=$html."</table></center>
	
	<script>
	function OpenVPNRemoteSiteDeleteRoutes(network){
		var XHR = new XHRConnection();
		XHR.appendData('siteid','$siteid');
		XHR.appendData('remote-site-route-del',network);
		AnimateDiv('openvpn-remotesite-routes-list');
		XHR.sendAndLoad('$page', 'POST',x_AddOpenVPnRemoteSiteRoute);	
	}	
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);

}

function remote_site_routes_add(){
	$siteid=$_POST["siteid"];
	$q=new mysql();
	$sql="SELECT remote_site_routes FROM vpnclient WHERE ID='$siteid'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$remote_site_routes=unserialize(base64_decode($ligne["remote_site_routes"]));
	$remote_site_routes[$_POST["remote-site-route-add"]]=$_POST["netmask"];
	
	$data=addslashes(base64_encode(serialize($remote_site_routes)));
	$sql="UPDATE vpnclient SET remote_site_routes='$data' WHERE ID='$siteid'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("services.php?openvpn=yes");	
	
}

function remote_site_routes_del(){
	$siteid=$_POST["siteid"];
	$q=new mysql();
	$sql="SELECT remote_site_routes FROM vpnclient WHERE ID='$siteid'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$remote_site_routes=unserialize(base64_decode($ligne["remote_site_routes"]));
	unset($remote_site_routes[$_POST["remote-site-route-del"]]);
	
	$data=addslashes(base64_encode(serialize($remote_site_routes)));
	$sql="UPDATE vpnclient SET remote_site_routes='$data' WHERE ID='$siteid'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("services.php?openvpn=yes");	
}


?>