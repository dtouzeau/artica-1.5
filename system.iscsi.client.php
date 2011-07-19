<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once("ressources/class.os.system.inc");
	include_once("ressources/class.lvm.org.inc");
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}
	
	
	if(isset($_GET["add"])){add_js();exit;}
	if(isset($_GET["add-popup"])){add_popup();exit;}
	if(isset($_GET["iscsi-search"])){add_search();exit;}
	if(isset($_GET["add-select"])){add_select_popup();exit;}
	if(isset($_POST["Params"])){add_select_sql();exit;}
	if(isset($_GET["iScsiClientDelete"])){delete_client();exit;}
	if(isset($_GET["iScsciReconnect"])){iScsciReconnect();exit;}
	
function add_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{add_iscsi_disk}");
	
	$html="YahooWin2('670','$page?add-popup=yes','$title');";
	echo $html;
	
}

function add_popup(){
	$page=CurrentPageName();
	$tpl=new templates();

	$html="<div class=explain>{add_iscsi_explain}</div>
	<hr>
	<center>
		<table style='width:260px' class=form>
		<tr>
			<td class=legend nowrap>{addr}:</td>
			<td>". Field_text("iscsi_search",null,"font-size:14px;font-weight:bold;width:210px",null,null,null,false,"SearchIscsiCheck(event)")."</td>
			<td width=1%>". button("{search}","SearchIscsi()")."</td>
		</tr>
		</table>	
	
		<div style='text-align:right;margin-bottom:10px;margin-top:10px'>". imgtootltip("32-refresh.png","{refresh}","SearchIscsi()")."</td>
		<div id='iscsi-search-list' style='width:100%;height:250px;overflow:auto'></div>
		
		
		<script>
			function SearchIscsiCheck(e){
				if(checkEnter(e)){SearchIscsi();}
			}
		
			function SearchIscsi(){
				LoadAjax('iscsi-search-list','$page?iscsi-search='+document.getElementById('iscsi_search').value);
			}
			
			
		var x_iScsiClientDelete=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			SearchIscsi();
		}		
		
		function iScsiClientDelete(ID){
			var XHR = new XHRConnection();
			XHR.appendData('iScsiClientDelete',ID);					
    		XHR.sendAndLoad('$page', 'GET',x_iScsiClientDelete);		
			}				
			
			
		SearchIscsi();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function add_search(){
	// sudo iscsiadm --mode discovery --type sendtargets --portal 192.168.1.106
	$page=CurrentPageName();
	$tpl=new templates();	
	$ip=trim($_GET["iscsi-search"]);
	$sock=new sockets();
	if($ip<>null){
		$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?iscsi-search=$ip")));
	}
	
	$array_sessions=unserialize(base64_decode($sock->getFrameWork("cmd.php?iscsi-sessions=yes")));
	
	while (list ($ip, $subarray) = each ($array_sessions)){
		while (list ($ip, $subarray2) = each ($subarray)){
			$ids="{$subarray2["ISCSI"]}:{$subarray2["PORT"]}:{$subarray2["FOLDER"]}";
			$MSESSIONS[$ids]=true;
		}
		
	}
	
	
	
	$sql="SELECT ID,hostname,directory FROM iscsi_client";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){		
		$TABLE["{$ligne["hostname"]}:{$ligne["directory"]}"]=$ligne["ID"];
		
	}
	
		
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th colspan=2>&nbsp;</th>
			<th>{port}</th>
			<th>{disk}</th>
			<th>{directory}</th>
			<th colspan=3>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	

	if(is_array($array)){
		while (list ($ip, $subarray) = each ($array)){
			while (list ($ip, $subarray2) = each ($subarray)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$ID=null;
				$delete=null;
				$content=base64_encode(serialize($subarray2));
				$delete="&nbsp;";
				$ids="{$subarray2["ISCSI"]}:{$subarray2["PORT"]}:{$subarray2["FOLDER"]}";
				if(isset($TABLE[$ids])){
					if(is_numeric($TABLE[$ids])){
						$ID=$TABLE[$ids];
						$remove[$ID]=true;
						$delete=imgtootltip("delete-32.png","{delete}","iScsiClientDelete('$ID');");
					}
				}
				
				$stat="ok32-grey.png";
				if($MSESSIONS[$ids]){$stat="ok32.png";}
				
				$select=imgtootltip("hard-drive-add-32.png","{select}<hr>{$subarray2["FOLDER"]}",
				"iscsciSelect('$content','{$subarray2["ISCSI"]}/{$subarray2["FOLDER"]}','$ID')");
				$html=$html . "
				<tr  class=$classtr>
					<td width=1%><img src='img/net-drive-32.png'></td>
					<td width=1% align='center' nowrap><strong style='font-size:14px'>{$subarray2["ID"]}</strong></td>
					<td width=1% align='center' nowrap><strong style='font-size:14px'>{$subarray2["PORT"]}</strong></td>
					<td width=99% align='left'><strong style='font-size:14px'>{$subarray2["ISCSI"]}</strong></td>
					<td width=1% align='center' nowrap><strong style='font-size:14px'>{$subarray2["FOLDER"]}</strong></td>
					<td width=1%><img src='img/$stat'></td>
					<td width=1%>$select</td>
					<td width=1%>$delete</td>
				</tr>";		
			}		
				
				
			}
		}
		
	$sql="SELECT * FROM iscsi_client";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($remove[$ligne["ID"]]){continue;}
		$subarray2=unserialize(base64_decode($ligne["Params"]));
		$select=imgtootltip("hard-drive-add-32.png","{select}<hr>{$subarray2["FOLDER"]}",
		"iscsciSelect('$content','{$subarray2["ISCSI"]}/{$subarray2["FOLDER"]}','{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","iScsiClientDelete('{$ligne["ID"]}');");
		$stat="ok32-grey.png";
		if($MSESSIONS[$ids]){$stat="ok32.png";}
		$html=$html . "
			<tr  class=$classtr>
				<td width=1%><img src='img/net-drive-32.png'></td>
				<td width=1% align='center' nowrap><strong style='font-size:14px'>{$ligne["ID"]}</strong></td>
				<td width=1% align='center' nowrap><strong style='font-size:14px'>{$subarray2["PORT"]}</strong></td>
				<td width=99% align='left'><strong style='font-size:14px'>{$subarray2["ISCSI"]}</strong></td>
				<td width=1% align='center' nowrap><strong style='font-size:14px'>{$subarray2["FOLDER"]}</strong></td>
				<td width=1%><img src='img/$stat'></td>
				<td width=1%>$select</td>
				<td width=1%>$delete</td>
			</tr>";		
	}			
		
	
			
		

$html=$html."</table>
<div style='text-align:right;width:100%;margin-top:15px'>". button("{connect}:{all}","iScsciReconnect()")."</td>

<script>
	function iscsciSelect(base,title,ID){
		YahooWin3Hide();
		YahooWin3('500','$page?add-select='+base+'&ID='+ID,title);
	
	}
	var x_iScsciReconnect=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);return;}
		SearchIscsi();
	}		
		
	function iScsciReconnect(){
		var XHR = new XHRConnection();
		XHR.appendData('iScsciReconnect','yes');					
    	XHR.sendAndLoad('$page', 'GET',x_iScsciReconnect);		
	}	

</script>
";
echo $tpl->_ENGINE_parse_body($html);

	
}

function add_select_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ID=$_GET["ID"];
	if(!is_numeric($ID)){$ID=0;}	
	if($ID>0){
		$sql="SELECT * FROM iscsi_client WHERE ID='{$_GET["ID"]}'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
		$subarray2=unserialize(base64_decode($ligne["Params"]));
	}
	
	
	if($ID==0){$subarray2=unserialize(base64_decode($_GET["add-select"]));}

	
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/net-disk-add-64.png'></td>
	<td valign='top'>
		<table style=width:100%' class=form>
		<tr>
			<td class=legend>{addr}:</td>
			<td style='font-size:13px;font-weight:bold'>{$subarray2["IP"]}:{$subarray2["PORT"]}</td>
		</tR>
		<tr>
			<td class=legend>{disk}:</td>
			<td style='font-size:13px;font-weight:bold'>{$subarray2["ISCSI"]}</td>
		</tR>
		<tr>
			<td class=legend>{directory}:</td>
			<td style='font-size:13px;font-weight:bold'>{$subarray2["FOLDER"]}</td>
		</tR>
		<tr>
			<td class=legend>{enable_authentication}:</td>
			<td style='font-size:13px;font-weight:bold'>". Field_checkbox("iscsi-EnableAuth",1,$ligne["EnableAuth"],"EnableAuthCCheck()")."</td>
		</tR>			
		<tr>
			<td class=legend nowrap>{username}:</td>
			<td style='font-size:13px;font-weight:bold'>". Field_text("iscsi-username",$ligne["username"],"font-size:14px;padding:3px;width:210px")."</td>
		</tR>
		<tr>
			<td class=legend>{password}:</td>
			<td style='font-size:13px;font-weight:bold'>". Field_password("iscsi-password",$ligne["password"],"font-size:14px;padding:3px;width:150px")."</td>
		</tR>		
		<tr>
			<td class=legend>{persistante_connection}:</td>
			<td style='font-size:13px;font-weight:bold'>". Field_checkbox("iscsi-persistante",1,$ligne["Persistante"])."</td>
		</tR>	
		<tr>
			<td colspan=2 align='right'><hr>". button("{connect}","iscsi_client_connect()")."</td>
		</tr>	
		</table>
	</td>
	</tr>
	</table>		
	
	<script>
	
		function EnableAuthCCheck(){
			document.getElementById('iscsi-password').disabled=true;
			document.getElementById('iscsi-username').disabled=true;
			if(document.getElementById('iscsi-EnableAuth').checked){
				document.getElementById('iscsi-password').disabled=false;
				document.getElementById('iscsi-username').disabled=false;
			}			
		}

		
		var x_iscsi_client_connect=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			YahooWin3Hide();
			SearchIscsi();
		}		
		
		function iscsi_client_connect(){
			var XHR = new XHRConnection();
			var password=document.getElementById('iscsi-password').value;
			XHR.appendData('username',document.getElementById('iscsi-username').value);
			XHR.appendData('password',password);
			XHR.appendData('Params','{$_GET["add-select"]}');
			XHR.appendData('ID','$ID');					
			if(document.getElementById('iscsi-persistante').checked){XHR.appendData('persistante',1);}else{XHR.appendData('persistante',0);}
			if(document.getElementById('iscsi-EnableAuth').checked){XHR.appendData('EnableAuth',1);}else{XHR.appendData('EnableAuth',0);}
    		XHR.sendAndLoad('$page', 'POST',x_iscsi_client_connect);		
			}	
	EnableAuthCCheck();
	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function add_select_sql(){
	$subarray2=unserialize(base64_decode($_POST["Params"]));
	
	$ID=$_POST["ID"];
	if(!is_numeric($ID)){$ID=0;}		
	
	$sql="INSERT INTO iscsi_client(username,password,Params,hostname,directory,Persistante,EnableAuth)
	VALUES('{$_POST["username"]}','{$_POST["password"]}','{$_POST["Params"]}','{$subarray2["ISCSI"]}:{$subarray2["PORT"]}','{$subarray2["FOLDER"]}','{$_POST["persistante"]}','{$_POST["EnableAuth"]}')";
	
	$sql_edit="UPDATE `iscsi_client`
	SET `username`='{$_POST["username"]}',
	`password`='{$_POST["password"]}',
	`Persistante`='{$_POST["persistante"]}',
	`EnableAuth`='{$_POST["EnableAuth"]}'
	WHERE `ID`='{$_POST["ID"]}'
	";
	if($ID>0){$sql=$sql_edit;}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?iscsi-client=yes");	
}
function delete_client(){
	$ID=$_GET["iScsiClientDelete"];
	if(!is_numeric($ID)){$ID=0;}		
	$sql="DELETE FROM iscsi_client WHERE ID='$ID'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?iscsi-client=yes");
}
function iScsciReconnect(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?iscsi-client=yes");	
}


	
	
