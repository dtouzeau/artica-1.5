<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once("ressources/class.os.system.inc");
	include_once("ressources/class.lvm.org.inc");
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}
	
	
	if(isset($_GET["status"])){section_status();exit;}
	if(isset($_GET["disks"])){section_disks();exit;}
	
	if(isset($_GET["iscsi-list"])){iscsi_list();exit;}
	if(isset($_POST["shared_folder"])){iscsi_save();exit;}
	if(isset($_GET["EnableISCSI"])){EnableISCSI();exit;}
	
	
	if(isset($_GET["popup-edit"])){iscsi_tabs();exit;}
	if(isset($_GET["popup-disk"])){iscsi_disk();exit;}
	if(isset($_GET["popup-params"])){iscsi_params();exit;}
	if(isset($_GET["ImmediateData"])){iscsi_params_save();exit;}
	
	
	if(isset($_GET["popup-security"])){iscsi_secu();exit;}
	if(isset($_GET["uid"])){iscsi_secu_save();exit;}
	
	if(isset($_GET["iscsi-status"])){iscsi_status();exit;}
	if(isset($_GET["iCsciDiskDelete"])){iscsi_disk_delete();exit;}
	tabs();

function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["disks"]='{disks}';
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_iscsi_master style='width:100%;height:590px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_iscsi_master\").tabs();});
		</script>";		
		
	
}

function iscsi_status(){
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?iscsi-status=yes")));
	$status=DAEMON_STATUS_ROUND("APP_IETD",$ini,null,0);
	echo $tpl->_ENGINE_parse_body($status);		
}

function section_status(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$sock=new sockets();
	$EnableISCSI=$sock->GET_INFO("EnableISCSI");
	$html="
	<table style='width:100%'>
	<tr>
		<td width='230px' valign='top'><div id='iscsi-status'></div></td>
		<td valign='top'>
			<div class=explain>{iscsi_explain}</div>
				<div style=text-align:right'>
				<table style='width:220px' class=form>
						<tr>
							<td class=legend>{EnableISCSI}:</td>
							<td>". Field_checkbox("EnableISCSI",1,$EnableISCSI,"EnableISCSICheck()")."</td>
						</tr>
				</table>
				</div>
		</td>
	</tr>
	</table>
	
<script>
	function iscsi_status(){
		LoadAjax('iscsi-status','$page?iscsi-status=yes');
	
	}
	

	var x_EnableISCSICheck= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		iscsi_status();
	}		
	
	function EnableISCSICheck(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableISCSI').checked){
			XHR.appendData('EnableISCSI',1);}else{XHR.appendData('EnableISCSI',0);	}
			document.getElementById('iscsi-status').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_EnableISCSICheck);
		}

	iscsi_status();
</script>
";

	echo $tpl->_ENGINE_parse_body($html);
	
}



function section_disks(){
	$page=CurrentPageName();
	$tpl=new templates();
$html="
	
	<div id='iscsi-list' style='width:100%;height:250px'></div>
	
	
	<script>
		function iscsiList(){
			LoadAjax('iscsi-list','$page?iscsi-list=yes');
		}
		
		function Addiscsi(ID){
			YahooWin3(650,'$page?popup-edit=yes&ID='+ID,ID+'::iSCSI');
		}
		
		var x_iCsciDiskDelete=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			iscsiList();
		}

		function iCsciDiskDelete(ID){
			var XHR = new XHRConnection();
			XHR.appendData('iCsciDiskDelete',ID);
			document.getElementById('iscsi-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
    		XHR.sendAndLoad('$page', 'GET',x_iCsciDiskDelete);	
		}		
		
		iscsiList();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function iscsi_tabs(){
	$ID=$_GET["ID"];
	$array["popup-disk"]='{disk}';
	if($ID>0){
		$array["popup-security"]='{security}';
		$array["popup-params"]='{parameters}';
		
		
	}
	$page=CurrentPageName();
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html[]= "<li><a href=\"$page?$num=yes&ID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n";
		}
	
	
	echo $tpl->_parse_body("
	<div id=iscsid$ID style='width:100%;height:530px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#iscsid$ID').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>");		
	
	
}

function iscsi_params(){
	$sql="SELECT Params FROM iscsi_params WHERE ID='{$_GET["ID"]}'";
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));			
	$Params=unserialize(base64_decode($ligne["Params"]));
	
	if(!is_numeric($Params["MaxConnections"])){$Params["MaxConnections"]=1;}
	if(!is_numeric($Params["ImmediateData"])){$Params["ImmediateData"]=1;}
	if(!is_numeric($Params["Wthreads"])){$Params["Wthreads"]=8;}
	if($Params["IoType"]==null){$Params["IoType"]="fileio";}
	if($Params["mode"]==null){$Params["mode"]="wb";}

	$hashIoType=array("fileio"=>"{fileio}","blockio"=>"{blockio}");
	$hashMode=array("ro"=>"{ro}","wb"=>"{wb}");
	
	
	$html="
	<div id='SaveiscsciSettings-div'></div>
<table style='width:100%' class=form>
	<tr>
		<td class=legend>{MaxConnections}:</td>
		<td>". Field_text("iscsi-MaxConnections",$Params["MaxConnections"],"font-size:14px;padding:3px;width:60px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{IoType}:</td>
		<td>". Field_array_Hash($hashIoType,"iscsi-IoType",$Params["IoType"],"style:font-size:14px;padding:3px;")."</td>
		<td>". help_icon("{iscsi_IoType_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{mode}:</td>
		<td>". Field_array_Hash($hashMode,"iscsi-mode",$Params["mode"],"style:font-size:14px;padding:3px;")."</td>
		<td>&nbsp;</td>
	</tr>			
	<tr>
		<td class=legend>{ImmediateData}:</td>
		<td>". Field_checkbox("iscsi-ImmediateData",1,$Params["ImmediateData"])."</td>
		<td>". help_icon("{ImmediateData_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{Wthreads}:</td>
		<td>". Field_text("iscsi-Wthreads",$Params["Wthreads"],"font-size:14px;padding:3px;width:60px")."</td>
		<td>". help_icon("{Wthreads_explain}")."</td>
	</tr>
	<tr>
		<td colspan=3 align='right'>
			<hr>". button("{apply}","SaveiscsciSettings()")."</td>
	</tr>
	</table>
	<script>
	
		
		var x_SaveiscsciSettings=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			var ID={$_GET["ID"]};
			RefreshTab('iscsid{$_GET["ID"]}');
		}		
		
		function SaveiscsciSettings(){
			var ID={$_GET["ID"]};
			var XHR = new XHRConnection();
			XHR.appendData('ID',{$_GET["ID"]});
			if(document.getElementById('iscsi-ImmediateData').checked){XHR.appendData('ImmediateData',1);}else{XHR.appendData('ImmediateData',0);}
			XHR.appendData('Wthreads',document.getElementById('iscsi-Wthreads').value);
			XHR.appendData('mode',document.getElementById('iscsi-mode').value);
			XHR.appendData('iscsi-IoType',document.getElementById('iscsi-mode').value);
			XHR.appendData('MaxConnections',document.getElementById('iscsi-MaxConnections').value);
			document.getElementById('SaveiscsciSettings-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
    		XHR.sendAndLoad('$page', 'GET',x_SaveiscsciSettings);		
			}	

	</script>
	
	";

	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function iscsi_params_save(){
	$sql="SELECT Params FROM iscsi_params WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
	$Params=unserialize(base64_decode($ligne["Params"]));
	while (list ($num, $ligne) = each ($_GET) ){
		$Params[$num]=$ligne;
	}
	
	$newParams=base64_encode(serialize($Params));
	$sql="UPDATE iscsi_params SET `Params`='$newParams' WHERE ID='{$_GET["ID"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?reload-iscsi=yes");	
}


function iscsi_secu(){
	$sql="SELECT * FROM iscsi_params WHERE ID='{$_GET["ID"]}'";
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
	$html="
	<div class=explain id='iscsi-auth-div'>{iscsi-secu-explain}</div>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable}:</td>
		<td>". Field_checkbox("iscsi-EnableAuth",1,$ligne["EnableAuth"],"EnableAuthCheck()")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{member}:</td>
		<td>". Field_text("iscsi-member",$ligne["uid"],"font-size:14px;padding:3px;width:220px")."</td>
		<td width=1%><input type='button' OnClick=\"javascript:Loadjs('MembersBrowse.php?field-user=iscsi-member&OnlyUsers=1');\" value='{browse}...'></td>
	</tr>
	<tr>
		<td colspan=3 align='right'>
			<hr>
				". button("{apply}","SaveAuthParams()")."
		</td>
	</tr>
	</table>
	
	<script>
		function EnableAuthCheck(){
			document.getElementById('iscsi-member').disabled=true;
			if(document.getElementById('iscsi-EnableAuth').checked){
				document.getElementById('iscsi-member').disabled=false;
			}
		
		}
		
		var x_SaveAuthParams=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			var ID={$_GET["ID"]};
			RefreshTab('iscsid{$_GET["ID"]}');
		}		
		
		function SaveAuthParams(){
			var ID={$_GET["ID"]};
			var XHR = new XHRConnection();
			XHR.appendData('ID',{$_GET["ID"]});
			if(document.getElementById('iscsi-EnableAuth').checked){XHR.appendData('EnableAuth',1);}else{XHR.appendData('EnableAuth',0);}
			XHR.appendData('uid',document.getElementById('iscsi-member').value);
			document.getElementById('iscsi-auth-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
    		XHR.sendAndLoad('$page', 'GET',x_SaveAuthParams);		
			}	

			EnableAuthCheck();
		</script>
		";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function iscsi_secu_save(){
	$sql="UPDATE iscsi_params SET `uid`='{$_GET["uid"]}',`EnableAuth`='{$_GET["EnableAuth"]}' WHERE ID={$_GET["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?reload-iscsi=yes");
	
	
	
}

function iscsi_disk(){
	$sql="SELECT * FROM iscsi_params WHERE ID='{$_GET["ID"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$button_text="{apply}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
	include_once 'ressources/usb.scan.inc';
	while (list ($num, $line) = each ($_GLOBAL["disks_list"])){
		if($num=="size (logical/physical)"){continue;}
		$ID_MODEL_2=$line["ID_MODEL_2"];
		$PARTITIONS=$line["PARTITIONS"];
		//print_r($line);
		if(is_array($PARTITIONS)){
			while (list ($dev, $part) = each ($PARTITIONS)){
				$MOUNTED=$part["MOUNTED"];
				if(strlen($MOUNTED)>20){$MOUNTED=substr($MOUNTED,0,17)."...";}
				$SIZE=$part["SIZE"];
				$TYPE=$part["TYPE"];
				if($TYPE==82){continue;}
				if($TYPE==5){continue;}
				$devname=basename($dev);
				$devs[$dev] ="($devname) $MOUNTED $SIZE [$ID_MODEL_2]";
			}
		}
	}
	
	$iscsar=array("disk"=>"{disk}","file"=>"{file}");
	$iscsarF=Field_array_Hash($iscsar,"iscsi-type",$ligne["type"],"ChangeIscsiType()",null,0,"font-size:14px;padding:3px");
	$devsF=Field_array_Hash($devs,"iscsi-part",$ligne["dev"],"style:font-size:14px;padding:3px");
	if($ligne["hostname"]==null){
		$users=new usersMenus();
		$ligne["hostname"]=$users->fqdn;
	}
	
	if($_GET["ID"]==0){$button_text="{add}";}
	
	if(!is_numeric($ligne["file_size"])){$ligne["file_size"]=5;}
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/64-idisk-server.png'></td>
		<td valign='top' style='width:100%'>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{type}:</td>
				<td>$iscsarF</td>
				<td width=1%>". help_icon("{iscsi_type_edit_explain}")."</td>
			</tr>
			<tr>
				<td class=legend>{path}:</td>
				<td>". Field_text("iscsi-path",$ligne["dev"],"font-size:14px;padding:3px;width:220px")."</td>
				<td width=1%>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend>{size}:</td>
				<td style='font-size:14px;'>". Field_text("iscsi-size",$ligne["file_size"],"font-size:14px;padding:3px;width:30px")."&nbsp;G</td>
				<td width=1%>&nbsp;</td>
			</tr>					
			<tr>
				<td class=legend>{partition}:</td>
				<td>$devsF</td>
				<td width=1%>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend>{hostname}:</td>
				<td>". Field_text("iscsi-hostname",$ligne["hostname"],"font-size:14px;padding:3px;width:220px")."</td>
				<td width=1%>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend colspan=3 align='left' style='text-align:left;padding-top:10px'>{shared_folder}:</td>
			</tr>
			<tr>
				<td colspan=3>". Field_text("iscsi-folder",$ligne["shared_folder"],"font-size:14px;padding:3px;width:190px")."</td>
			
			</tr>			
			<tr>
				<td colspan=3 align='right'><hr>
					". button("$button_text","SaveIscsi()")	."</td>
			</tr>	
		</table>
		</td>
	</tr>
	</table>

	<script>
		function ChangeIscsiType(){
			document.getElementById('iscsi-path').disabled=true;
			document.getElementById('iscsi-part').disabled=true;
			document.getElementById('iscsi-size').disabled=true;
			var type=document.getElementById('iscsi-type').value;
			if(type=='disk'){
				document.getElementById('iscsi-part').disabled=false;
			}
			if(type=='file'){
				document.getElementById('iscsi-path').disabled=false;
				document.getElementById('iscsi-size').disabled=false;
			}
		
		}
		
		var x_SaveIscsi=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			var ID={$_GET["ID"]};
			if(ID>0){	
				RefreshTab('iscsid{$_GET["ID"]}');
			}else{
				YahooWin3Hide();
			}
			
			iscsiList();
		}		
		
		function SaveIscsi(){
			var ID={$_GET["ID"]};
			var XHR = new XHRConnection();
			XHR.appendData('ID',{$_GET["ID"]});
			XHR.appendData('hostname',document.getElementById('iscsi-hostname').value);
			XHR.appendData('path',document.getElementById('iscsi-path').value);
			XHR.appendData('type',document.getElementById('iscsi-type').value);
			XHR.appendData('partition',document.getElementById('iscsi-part').value);
			XHR.appendData('file_size',document.getElementById('iscsi-size').value);
			XHR.appendData('shared_folder',document.getElementById('iscsi-folder').value);
    		XHR.sendAndLoad('$page', 'POST',x_SaveIscsi);		
		
		}
		
		
	ChangeIscsiType();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function iscsi_save(){
	$hostname=$_POST["hostname"];
	$type=$_POST["type"];
	$size=$_POST["file_size"];
	$ID=$_POST["ID"];
	$foldername=$_POST["shared_folder"];
	$foldername=strtolower($foldername);
	$foldername=replace_accents($foldername);
	$foldername=str_replace(" ","_",$foldername);
	$foldername=str_replace(".","-",$foldername);
	
	
	$q=new mysql();
	$tpl=new templates();
	if($ID==0){
		$sql="SELECT ID FROM iscsi_params WHERE shared_folder='$foldername'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["ID"]>0){
			echo $tpl->javascript_parse_text("$foldername {ERROR_OBJECT_ALREADY_EXISTS}");
			return;
		}
	}
	
	
	if($foldername==null){$foldername=time();}
	if($type=='file'){$dev=$_POST["path"];}else{$dev=$_POST["partition"];}
	if(!is_numeric($size)){$size=5;}
	if(!is_numeric($ID)){$ID=0;}
	$sql="INSERT INTO iscsi_params (`hostname`,`dev`,`type`,`file_size`,`shared_folder`)
	VALUES('$hostname','$dev','$type','$size','$foldername')";
	
	$sqlu="UPDATE iscsi_params SET hostname='$hostname',`dev`='$dev',
	`type`='$type',`shared_folder`='$foldername' WHERE ID=$ID";
	
	if($ID>0){$sql=$sqlu;}
	
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?restart-iscsi=yes");	
	
	
}

function iscsi_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT * FROM iscsi_params ORDER BY ID DESC";
	$q=new mysql();
	$sock=new sockets();
	$EnableISCSI=$sock->GET_INFO("EnableISCSI");
	if(!is_numeric($EnableISCSI)){$EnableISCSI=0;}
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	$add=imgtootltip("plus-24.png","{add_iscsi_disk}","Addiscsi(0)");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th align='center'>$add</th>
	<th>{shared_folder}</th>
	<th>{hostname}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["useSSL"]==1){$ssl="check2.gif";}else{$ssl="check1.gif";}
		$color="black";
		if($EnableISCSI==0){$color="#CCCCCC";}
		$html=$html."
			<tr class=$classtr>
			<td width=1%>". imgtootltip("48-idisk-server.png","{edit}","Addiscsi('{$ligne["ID"]}')")."</td>
			<td nowrap><strong style='font-size:14px;color:$color'>{$ligne["shared_folder"]}</strong><div style='font-size:12px'><i>{$ligne["dev"]}</i></div></td>
			<td nowrap><strong style='font-size:14px;color:$color'>{$ligne["hostname"]}</strong></td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","iCsciDiskDelete('{$ligne["ID"]}')")."</td>
			</tr>
			";
		
	}	
	
	
$html=$html."
<tbody>
	</table>


";
echo $tpl->_ENGINE_parse_body($html);

}

function iscsi_disk_delete(){
	$sql="DELETE FROM iscsi_params WHERE ID='{$_GET["iCsciDiskDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?restart-iscsi=yes");
	
}



function EnableISCSI(){
	$sock=new sockets();
	$sock->SET_INFO("EnableISCSI",$_GET["EnableISCSI"]);
	$sock->getFrameWork("cmd.php?restart-iscsi=yes");
	
	
}