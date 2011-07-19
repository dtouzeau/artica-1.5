<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.kav4samba.inc');
	include_once('ressources/class.os.system.inc');
	
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["usblist"])){echo autmount_list();exit;}
	if(isset($_GET["ShareDevice"])){ShareDevice();exit;}
	if(isset($_GET["DeleteUsbShare"])){DeleteUsbShare();exit;}
	

	
	
	//usb-share-128.png
	
	js();
	
	

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{browse}');
	$html="LoadWinORG(550,'$page?popup=yes&server-field={$_GET["server-field"]}&folder-field={$_GET["folder-field"]}','$title')";
	echo $html;
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$datas=unserialize(@file_get_contents("ressources/logs/smbtree.array"));
	
	$html="
	<div style='height:580px;width:100%;overflow:auto'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>

</thead>
<tbody class='tbody'>";		
	
	
	while (list ($DOMAIN, $DOMAIN_ARRAY) = each ($datas) ){
		
		$html=$html."
		<tr class=>
			<th colspan=4 style='font-size:16px;font-weight:bold;text-align:left' colspan=2 align=left>$DOMAIN</th>
		</tr>";
		
		while (list ($SERVER,$SERVER_ARRAY) = each ($DOMAIN_ARRAY) ){
			
		if($SERVER_ARRAY["IP"]==null){$SERVER_ARRAY["IP"]=$SERVER;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/spacer.gif' width=20px height=1px></td>
			<td width=1%><img src='img/32-network-server.png'></td>
			<td width=99% align=left style='font-size:14px;font-weight:bold' colspan=2>$SERVER (<span style='font-size:12px'>{$SERVER_ARRAY["IP"]}</span>)</td>
		</tr>		
		";
		while (list ($index,$shares) = each ($SERVER_ARRAY["SHARES"]) ){
			$folder=$shares;
			if(preg_match("#^(.+?)\s+(.+)#",$shares,$re)){
				$folder=$re[1];
				$explain=$re[2];
			}
			
			$select=imgtootltip("arrow-left-32.png","{select}","SelectSmbTree('{$SERVER_ARRAY["IP"]}','$folder')");
			
			$html=$html."
			<tr class=$classtr>
				<td width=1%><img src='img/spacer.gif' width=20px height=1px></td>
				<td width=1%><img src='img/spacer.gif' width=20px height=1px></td>
				<td width=1%><img src='img/32-network-folder.png'></td>
				<td width=99% align=left style='font-size:13px;font-weight:bold'>
					<table style='width:100%'>
					<tr style='background-color:transparent;border:0px'>
					<td nowrap width=99% style='background-color:transparent;border:0px;font-size:13px;font-weight:bold'>$folder<div style='font-size:9px'><i>$explain</i></div></td>
					<td width=1% style='background-color:transparent;border:0px'>$select</td>
					</tr>
					</table>
			</tr>		
			";		
		}
		
		}
		
		
	}
	
	$html=$html."</table></div>
	<script>
		function SelectSmbTree(host,folder){
			document.getElementById('{$_GET["server-field"]}').value=host;
			document.getElementById('{$_GET["folder-field"]}').value=folder;
			WinORGHide();
		}
	
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
