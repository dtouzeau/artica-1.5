<?php
	session_start();
	if(!isset($_SESSION["uid"])){echo "document.location.href='logoff.php'";die();}
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){die("<H1>EXIT</H1>");}
	
	if(isset($_GET["js-inline"])){js_inline();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["browse-samba-list"])){virtual_servers_list();exit;}
	if(isset($_POST["delete-hostname"])){virtual_servers_delete();exit;}
	if(isset($_POST["EnableSambaVirtualsServers"])){EnableSambaVirtualsServersSave();exit;}
function js_inline(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?popup=yes');";	
}
function EnableSambaVirtualsServersSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableSambaVirtualsServers", $_POST["EnableSambaVirtualsServers"]);
	$sock->getFrameWork('cmd.php?samba-save-config=yes');
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$EnableSambaVirtualsServers=$sock->GET_INFO("EnableSambaVirtualsServers");

	$add=Paragraphe("64-net-server-add.png", "{add_virtual_server}", "{add_smb_virtual_server}","javascript:SambaVirtalServer('')");
	
	$html="
	<table style='width:750px'>
	<tr>
	<td width=550px valign='top'>
		<div class=explain>{samba_virtual_explain}</div>
		<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{servers}:</td>
				<td>". Field_text("browse-samba-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseSambaSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseSambaSearch()")."</td>
			</tr>
			</table>
		</center>		
		<div id='browse-samba-list' style='width:100%;height:450px;overflow:auto;text-align:center'></div>
		
	</td>
	<td width=200px valign='top'>
	 ". Paragraphe_switch_img("{enable_samba_virtual_servers}", "{enable_samba_virtual_servers_text}","EnableSambaVirtualsServers",$EnableSambaVirtualsServers)."
	 	<hr>
	 	<div style='text-align:right'>". button("{apply}","EnableSambaVirtualsServersSave()")."</div>
	 	<p>&nbsp;</p>$add
	 </td>
	</td>
	</tr>
	</table>
	
	
	
<script>
		function BrowseSambaSearchCheck(e){
			if(checkEnter(e)){BrowseSambaSearch();}
		}
		
		function BrowseSambaSearch(){
			var se=escape(document.getElementById('browse-samba-search').value);
			LoadAjax('browse-samba-list','$page?browse-samba-list=yes&search='+se+'&field={$_GET["field"]}');
		}
		
		var x_EnableSambaVirtualsServersSave=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			BrowseSambaSearch();
		}		
		
		function EnableSambaVirtualsServersSave(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableSambaVirtualsServers',document.getElementById('EnableSambaVirtualsServers').value);
			AnimateDiv('browse-samba-list');
    		XHR.sendAndLoad('$page', 'POST',x_EnableSambaVirtualsServersSave);
		}
			
	BrowseSambaSearch();
</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function virtual_servers_list(){
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
	$sure_delete_smb_vrt=$tpl->javascript_parse_text("{sure_delete_smb_vrt}");
	$EnableSambaVirtualsServers=$sock->GET_INFO("EnableSambaVirtualsServers");
	if(!is_numeric($EnableSambaVirtualsServers)){$EnableSambaVirtualsServers=0;}
	
	$add=imgtootltip("plus-24.png","{add}","SambaVirtalServer('')");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=5>{virtual_servers}&nbsp;|&nbsp;$search_regex&nbsp;|&nbsp;$search_sql</th>
	</tr>
</thead>
<tbody class='tbody'>";

		$q=new mysql();
		$sql="SELECT hostname,ou,workgroup,ipaddr FROM samba_hosts WHERE hostname LIKE '$search_sql' ORDER BY hostname";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","SambaVirtalServer('{$ligne["hostname"]}')");
		$select2=imgtootltip("32-network-server.png","{edit}","SambaVirtalServer('{$ligne["hostname"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","SambaVirtalDel('{$ligne["hostname"]}')");
		$color="black";
		if($EnableSambaVirtualsServers==0){$color="#CCCCCC";}
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$select2</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["hostname"]}<div><i style='font-size:10px'>{$ligne["ipaddr"]}</i></div></a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["workgroup"]}</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>&nbsp;{$ligne["ou"]}</td>
			<td width=1%>$select</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table></center>
	<script>
	
		var x_SambaVirtalDel=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			BrowseSambaSearch();
		}
	
	
		function SambaVirtalDel(hostname){
			if(confirm('$sure_delete_smb_vrt ['+hostname+']')){
				var XHR = new XHRConnection();
				XHR.appendData('delete-hostname',hostname);
				AnimateDiv('browse-samba-list');
    			XHR.sendAndLoad('$page', 'POST',x_SambaVirtalDel);
			}
		}
	
		
		function SambaVirtalServer(server){
			Loadjs('samba.virtual-server.edit.php?hostname='+server);
		}

	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}
function virtual_servers_delete(){
	$sql="DELETE FROM samba_hosts WHERE hostname='{$_POST["delete-hostname"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	}

