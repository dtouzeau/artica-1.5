<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	
	
	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}

	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
	if(!CheckSambaRights()){echo "<H1>$ERROR_NO_PRIVS</H1>";die();}
	
	if(isset($_GET["browse-net-list"])){net_list();exit;}
	if(isset($_GET["net-id-js"])){net_id_js();exit;}
	if(isset($_GET["net-id"])){net_id();exit;}
	if(isset($_POST["net-id"])){net_id_save();exit;}
	if(isset($_POST["delete-id"])){net_id_delete();exit;}
	if(isset($_POST["EnableRemoteAnnounce"])){EnableRemoteAnnounceSave();exit;}
popup();

function EnableRemoteAnnounceSave(){
	$sock=new sockets();
	$sock->SET_INFO("SambaEnableRemoteAnnounce", $_POST["EnableRemoteAnnounce"]);
	$sock->getFrameWork('cmd.php?samba-save-config=yes');
	
	
}

function net_id_save(){
	
	$sql="INSERT INTO samba_remote_announce (hostname,ipaddr,domain) VALUES('{$_POST["hostname"]}','{$_POST["ipaddr"]}','{$_POST["domain"]}')";
	if($_POST["net-id"]>0){
		$sql="UPDATE samba_remote_announce SET ipaddr='{$_POST["ipaddr"]}',domain='{$_POST["domain"]}' WHERE ID='{$_POST["net-id"]}'";
	}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?samba-save-config=yes');
}

function net_id_delete(){
	
	$sql="DELETE FROM samba_remote_announce WHERE ID='{$_POST["delete-id"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?samba-save-config=yes');
}


function net_id(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$id=$_GET["net-id"];
	$button="{add}";
	if($id>0){
		$button="{apply}";
		$q=new mysql();
		$sql="SELECT * FROM samba_remote_announce WHERE ID='$id' and hostname='{$_GET["hostname"]}'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$ipaddr=$ligne["ipaddr"];
		$domain=$ligne["domain"];
	}
	
	if($domain==null){
		$smb=new samba();
		$domain=$smb->main_array["global"]["workgroup"];
	}
	
	$html="
	<span id='smbanncleid'></span>
	<div class=explain >{SAMBA_REMOTE_ANNOUNCE_EXPLAIN_FIELD}</div>
	<table style='width:100%' class=form>
	<tr>
	<td class=legend>{domain}:</td>
	<td>". Field_text("smbradomain",$domain,"font-size:14px;padding:3px;width:220px")."</td>
	<td>&nbsp;</td>
	</tr>	
	
	<tr>
	<td class=legend>{ipaddr}:</td>
	<td>". field_ipv4("iptsmb_addr",$ipaddr,"font-size:14px;padding:3px'")."</td>
	<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("$button", "SaveSMBRARule()")."</td>
	</tr>
	
	</table>
	
	<script>
		var x_SaveSMBRARule=function (obj) {
			var results=obj.responseText;
			if(results.length>2){
				alert(results);
				document.getElementById('smbanncleid').innerHTML='';
				return;

			}			
			YahooWin3Hide();
			BrowseSBNETSearch();
		}
	
	
		function SaveSMBRARule(){
			var XHR = new XHRConnection();
			XHR.appendData('net-id','$id');
			XHR.appendData('ipaddr',document.getElementById('iptsmb_addr').value);
			XHR.appendData('domain',document.getElementById('smbradomain').value);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			
			AnimateDiv('smbanncleid');
    		XHR.sendAndLoad('$page', 'POST',x_SaveSMBRARule);
			
		}

	</script>";
	echo $tpl->_ENGINE_parse_body($html);	
}

function net_id_js(){
	$page=CurrentPageName();
	$tpl=new templates();	
		$id=$_GET["net-id-js"];
		$title="{network}:&nbsp;&raquo;&nbsp;$id&nbsp;&raquo;&nbsp;{add}";
		
		if($id>0){
			$q=new mysql();
			$sql="SELECT ipaddr FROM samba_remote_announce WHERE ID='$id'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
			$title="{network}:&nbsp;&raquo;&nbsp;$id&nbsp;&raquo;&nbsp;{$ligne["ipaddr"]}";
			}
	$title=$tpl->javascript_parse_text($title);
	$html="YahooWin3('445','$page?net-id=$id&hostname={$_GET["hostname"]}','$title');";
	echo $html;
			
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$SambaEnableRemoteAnnounce=$sock->GET_INFO("SambaEnableRemoteAnnounce");
	if(!is_numeric($SambaEnableRemoteAnnounce)){$SambaEnableRemoteAnnounce=0;}
	$hostname=$_GET["hostname"];
	if($hostname==null){$hostname="master-samba-artica";}
	$html="
	<table style='widht:100%'>
	<tr>
		<td valign='top' width=99%>
			<div class=explain>{SAMBA_REMOTE_ANNOUNCE_EXPLAIN}</div>
		</td>
		<td valign='top' width=1%>
			". Paragraphe_switch_img("{enable_feature}", "{enable_remote_announce_explain}","SambaEnableRemoteAnnounce",$SambaEnableRemoteAnnounce)."
			<hr>
			<div style='text-align:right'><hr>". button('{apply}',"SaveSambaEnableRemoteAnnounce()")."</div>
			</td>
		</td>
		<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{networks}:</td>
				<td>". Field_text("browse-smbnets-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseSBNETSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseSBNETSearch()")."</td>
			</tr>
			</table>
		</center>	

<div id='browse-smbnet-list' style='width:100%;height:350px;overflow:auto;text-align:center'></div>		
		
		
		
<script>
		function BrowseSBNETSearchCheck(e){
			if(checkEnter(e)){BrowseSBNETSearch();}
		}
		
		function BrowseSBNETSearch(){
			var se=escape(document.getElementById('browse-smbnets-search').value);
			LoadAjax('browse-smbnet-list','$page?browse-net-list=yes&search='+se+'&field={$_GET["field"]}&hostname=$hostname');
		}
		
		var x_SMBRemoteAnnounceDel=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			BrowseSBNETSearch();
		}
	
	
		function SaveSambaEnableRemoteAnnounce(ID){
				var XHR = new XHRConnection();
				XHR.appendData('EnableRemoteAnnounce',document.getElementById('SambaEnableRemoteAnnounce').value);
    			XHR.sendAndLoad('$page', 'POST');
			
		}
			
	BrowseSBNETSearch();
</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	//iptables -A OUTPUT -j ACCOUNT --addr 0.0.0.0/0 --tname all_outgoing
	// tcp_account_rules
}


function net_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ldap=new clladp();
	$sock=new sockets();
	$users=new usersMenus();
	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("***","*",$search);
	$search=str_replace("**","*",$search);
	$search_sql=str_replace("*","%",$search);
	$search_sql=str_replace("%%","%",$search_sql);
	$search_regex=str_replace(".","\.",$search);	
	$search_regex=str_replace("*",".*?",$search);
	
	
	$add=imgtootltip("plus-24.png","{add}","SMBRemoteAnnounce('0')");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=4>{networks}&nbsp;|&nbsp;$search_regex&nbsp;|&nbsp;$search_sql</th>
	</tr>
</thead>
<tbody class='tbody'>";

		$q=new mysql();
		$q->BuildTables();
		$sql="SELECT * FROM samba_remote_announce WHERE hostname='{$_GET["hostname"]}' AND ipaddr LIKE '$search_sql' ORDER BY ID DESC";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		$c=0;
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$select=imgtootltip("32-parameters.png","{edit}","SMBRemoteAnnounce('{$ligne["ID"]}')");
			$delete=imgtootltip("delete-32.png","{delete}","SMBRemoteAnnounceDel('{$ligne["ID"]}')");
			$color="black";
			$c++;
			$html=$html."
			<tr class=$classtr>
				<td width=1%>$select</td>
				<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["ipaddr"]} ({$ligne["domain"]})</a></td>
				<td width=1%>$delete</td>
			</tr>
			";
		}
		
		if($c=0){
			$ldap=new clladp();
			$smb=new samba();
			$nets=$ldap->load_mynetworks();
			if(is_array($nets)){
		
			while (list ($index, $ipmask) = each ($nets) ){
				if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)#",$ipmask,$re)){
					if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
					$html=$html."
					<tr class=$classtr>
						<td width=1%>&nbsp;</td>
						<td style='font-size:14px;font-weight:bold;color:$color'>{$re[1]}.{$re[2]}.{$re[3]}.255 ({$smb->main_array["global"]["workgroup"]})</a></td>
						<td width=1%>&nbsp;</td>
					</tr>
					";					
					}
				}			
			
			}
		}
	
	$html=$html."</table></center>
	<script>
	
		var x_SMBRemoteAnnounceDel=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			BrowseSBNETSearch();
		}
	
	
		function SMBRemoteAnnounceDel(ID){
				var XHR = new XHRConnection();
				XHR.appendData('delete-id',ID);
				AnimateDiv('browse-smbnet-list');
    			XHR.sendAndLoad('$page', 'POST',x_SMBRemoteAnnounceDel);
			
		}
	
		
		function SMBRemoteAnnounce(ID){
			Loadjs('$page?net-id-js='+ID+'&hostname={$_GET["hostname"]}');
		}

	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}
