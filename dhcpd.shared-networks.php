<?php

session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.dhcpd.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){		
	$tpl=new templates();
	echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();exit();
	}
	
	if(isset($_GET["list-nets"])){list_nets();exit;}
	if(isset($_GET["shared-edit"])){shared_edit();exit;}
	if(isset($_POST["domain-name"])){shared_post();exit;}
	if(isset($_POST["DelDHCPShared"])){shared_del();exit;}
	if(isset($_POST["SharedNetsApply"])){shared_apply();exit;}
page();



function page(){
	$tpl=new templates();
	$page=CurrentPageName();
	$addtitl=$tpl->_ENGINE_parse_body("{add}&raquo;&raquo;{network_legend}");
	
	$html="
	<table>
	<tr>
	<td widht=100% valign='top'><div class=explain>{dhcpd_shared_network_explain}</div></td>
	<td width=1%>". Paragraphe("apply-config-44.gif", "{apply_to_server}", "{apply_settings}","javascript:SharedNetsApply()")."</td>
	</tr>
	</table>
	
	<center>
	<table style='width:80%' class=form>
	<tr>
		<td class=legend>{network_legend}:</td>
		<td>". Field_text("netss",null,'font-size:14px',null,null,null,false,"RefreshSharedNetCheck(event)")."</td>
		<td width=1%>". button("{search}", "RefreshSharedNet()")."</td>
	</tr>
	</table>
	</center>
	<hr>
	
	<div id='dhcpd-shared-network' style='width:100%;height:290px;overflow:auto'></div>
	
	
	
	<script>
	function RefreshSharedNetCheck(e){
		if(checkEnter(e)){RefreshSharedNet();}
	}
	
	function RefreshSharedNet(){
			var se=escape(document.getElementById('netss').value);
			LoadAjax('dhcpd-shared-network','$page?list-nets=yes&search='+se);
		}
		
	function AddDHCPShared(val,title){
		if(!val){val=0;}
		var mtitle='$addtitl';
		if(title){mtitle=title;}
		YahooWin5('650','$page?shared-edit='+val,mtitle);
	}
	
	var x_SharedNetsApply= function (obj) {
		var tempvalue=obj.responseText;	
		if(tempvalue.length>0){alert(tempvalue);return;}
		RefreshSharedNet();
	}
	
	function SharedNetsApply(){
		var XHR = new XHRConnection();
		XHR.appendData('SharedNetsApply','yes');
		XHR.sendAndLoad('$page', 'POST',x_SharedNetsApply);	
	}	
		
	RefreshSharedNet();
	</script>
		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function list_nets(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$search=$_GET["search"];
	$q=new mysql();
	if($search<>null){
		$search="*$search*";
		$search=str_replace("**", "*", $search);
		$search=str_replace("*", "%", $search);
		$search_sql=" WHERE (scopename LIKE '$search') OR (subnet LIKE '$search') OR (range1 LIKE '$search') OR (range2 LIKE '$search')";
	}
	
	$sql="SELECT * FROM dhcpd_sharednets $search_sql ORDER BY scopename";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<th width=1%>". imgtootltip("plus-24.png","{add}","AddDHCPShared()")."</th>
	<th>{scope}</th>
	<th>{group}</th>
	<th>{subnet}</th>
	<th>{range}</th>
	<th>&nbsp;</th>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:AddDHCPShared({$ligne["ID"]},'{$ligne["scopename"]}')\" style='font-size:13px;text-decoration:underline'>"; 
		
		$html=$html."
		<tr class=$classtr>
		<td width=1% style='font-size:14px' align='center'>{$ligne["ID"]}</td>
		<td style='font-size:13px' nowrap>$href{$ligne["scopename"]}</a></td>
		<td style='font-size:13px' nowrap>$href{$ligne["sharednet_name"]}</a></td>
		<td style='font-size:13px'>$href{$ligne["subnet"]}</a></td>
		<td style='font-size:13px'>$href{$ligne["range1"]}-{$ligne["range2"]} {$ligne["netmask"]}/{$ligne["subnet-mask"]}</a></td>
		<td>". imgtootltip("delete-32.png","{delete}","DelDHCPShared({$ligne["ID"]})")."</td>
	</tr>
		";
		
	}
	
$html=$html."</table>
	<script>
	
	var x_DelDHCPShared= function (obj) {
		var tempvalue=obj.responseText;	
		if(tempvalue.length>0){alert(tempvalue);return;}
		RefreshSharedNet();
	}
	
	function DelDHCPShared(ID){
		var XHR = new XHRConnection();
		XHR.appendData('DelDHCPShared',ID);
		XHR.sendAndLoad('$page', 'POST',x_DelDHCPShared);	
	}
</script>

";

echo $tpl->_ENGINE_parse_body($html);
	
}

function shared_edit(){
	$ID=$_GET["shared-edit"];
	$t=time();
	$tpl=new templates();
	$page=CurrentPageName();	
	$q=new mysql();	
	if(!is_numeric($ID)){$ID=0;}
	$sql="SELECT sharednet_name FROM dhcpd_sharednets GROUP BY sharednet_name ORDER BY sharednet_name";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$dhcpd_sharednets[$ligne["sharednet_name"]]=$ligne["sharednet_name"];
	}
	
	$groupname_field=Field_array_Hash($dhcpd_sharednets, "sharednet_name",$ligne["sharednet_name"],"style:font-size:14px;padding:3px");
	$sql="SELECT * FROM dhcpd_sharednets WHERE ID=$ID";
	
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	
	if(count($domains)==0){$dom=Field_text('domain-name',$ligne["domain-name"],"font-size:14px;");}
	else{
		$domains[null]="{select}";
		$dom=Field_array_Hash($domains,'domain-name',$ligne["domain-name"],null,null,null,";font-size:14px;padding:3px");
	}
	
	$button="{apply}";
	if($ID==0){$button="{add}";}	
	
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{groupname}:</td>
		<td>$groupname_field</td>
		<td width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{groupname} ({add}):</td>
		<td>". Field_text("groupnameAdd",null,"font-size:14px")."</td>
		<td>". help_icon("{dhcp-groupnameAdd-explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{scope}:</td>
		<td>". Field_text("scope",$ligne["scopename"],"font-size:14px")."</td>
		<td>". help_icon("{dhcp-scope-explain}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{ddns_domainname}:</td>
		<td>$dom</td>
		<td>". help_icon("{dhcp-domain-name-explain}")."</td>
	</tr>		
	<tr>
		<td class=legend>{subnet}:</td>
		<td>". field_ipv4("subnet",$ligne["subnet"],"font-size:14px;padding:3px",true)."</td>
		<td width=1%>&nbsp;</td>
	</tr>
	
	<tr>
		<td class=legend>{netmask}:</td>
		<td>". field_ipv4("netmask_$t",$ligne["netmask"],"font-size:14px;padding:3px")."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{gateway}:</td>
		<td>".field_ipv4('routers',$ligne["routers"],'font-size:14px;padding:3px')."&nbsp;</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>	
		<td class=legend style='font-size:13px'>{range}&nbsp;{from}:</td>
		<td>".field_ipv4('range1_'.$t,$ligne["range1"],'font-size:14px;padding:3px')."&nbsp;</td>
	</tr>
	<tr>	
		<td class=legend style='font-size:13px'>{range}&nbsp;{to}:</td>
		<td>".field_ipv4('range2_'.$t,$ligne["range2"],'font-size:14px;padding:3px')."&nbsp;</td>
	</tr>

	<tr>
		<td class=legend style='font-size:13px'>{subnet-mask}:</td>
		<td>".field_ipv4('subnet-mask_'.$t,$ligne["subnet-mask"],'font-size:14px;padding:3px')."</td>
		<td>". help_icon("{dhcp-subnet-masq_text}")."</td>
	</tr>	
	
	

	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{DNSServer} 1:</td>
		<td>".field_ipv4('domain-name-servers1',$ligne["domain-name-servers1"],'font-size:14px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{DNSServer} 2:</td>
		<td>".field_ipv4('domain-name-servers2',$ligne["domain-name-servers2"],'font-size:14px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{wins_server}:</td>
		<td>".field_ipv4('wins-server-group',$ligne["wins-server"],'font-size:14px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{servername}:</td>
		<td>".Field_text('server-name',$ligne["server-name"],'width:210px;font-size:14px;padding:3px')."&nbsp;</td>
		<td>". help_icon("{dhcp-server-name_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{next-server}:</td>
		<td>".Field_text('next-server',$ligne["next-server"],'width:210px;font-size:14px;padding:3px')."&nbsp;</td>
		<td>". help_icon("{dhcp-next-server-explain}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{pxe_file}:</td>
		<td>".Field_text('pxe_filename',$ligne["pxe_filename"],'width:110px;font-size:14px;padding:3px')."&nbsp;</td>
		<td>". help_icon("{filename_pxe_explain}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{pxe_root-path}:</td>
		<td>".Field_text('pxe_root-path',$ligne["pxe_root-path"],'width:210px;font-size:14px;padding:3px')."&nbsp;</td>
		<td>". help_icon("{pxe_root-path_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{tftp-server-name}:</td>
		<td>".Field_text('tftp-server-name',$ligne["tftp-server-name"],'width:210px;font-size:14px;padding:3px')."&nbsp;</td>
		<td>". help_icon("{tftp-server-name_explain}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{option-176}:</td>
		<td><textarea style='font-size:14px;height:50px;width:100%;overflow:auto' id='option-176'>{$ligne["option-176"]}</textarea>&nbsp;</td>
		<td>". help_icon("{option-176-explain}")."</td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button($button,"SharedDHCPNetSave()")."</td>
	</tr>
	
	
	</table>
	
	<script>
	
var x_SharedDHCPNetSave= function (obj) {
	var tempvalue=obj.responseText;
	var ID='$ID'
	if(tempvalue.length>0){alert(tempvalue);return;}
	RefreshSharedNet();
	if(ID==0){YahooWin5Hide();}
	}		
	
	function SharedDHCPNetSave(){
	var XHR = new XHRConnection();
		XHR.appendData('domain-name',document.getElementById('domain-name').value);
		XHR.appendData('sharednet_name',document.getElementById('sharednet_name').value);
		XHR.appendData('groupnameAdd',document.getElementById('groupnameAdd').value);
		XHR.appendData('scope',document.getElementById('scope').value);
		XHR.appendData('subnet',document.getElementById('subnet_'.$t).value);
		XHR.appendData('netmask',document.getElementById('netmask_$t').value);
		XHR.appendData('routers',document.getElementById('routers').value);
		XHR.appendData('range1',document.getElementById('range1_$t').value);
		XHR.appendData('range2',document.getElementById('range2_$t').value);
		XHR.appendData('subnet-mask',document.getElementById('subnet-mask').value);
		XHR.appendData('domain-name-servers1',document.getElementById('domain-name-servers1').value);
		XHR.appendData('domain-name-servers2',document.getElementById('domain-name-servers2').value);
		XHR.appendData('tftp-server-name',document.getElementById('tftp-server-name').value);
		XHR.appendData('server-name',document.getElementById('server-name').value);
		XHR.appendData('next-server',document.getElementById('next-server').value);
		XHR.appendData('pxe_filename',document.getElementById('pxe_filename').value);
		XHR.appendData('pxe_root-path',document.getElementById('pxe_root-path').value);
		XHR.appendData('option-176',document.getElementById('option-176').value);
		XHR.appendData('wins-server',document.getElementById('wins-server-group').value);
		
		
		
		XHR.appendData('ID',$ID);
		XHR.sendAndLoad('$page', 'POST',x_SharedDHCPNetSave);	
	}
</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function shared_post(){
	$sharednet_name=$_POST["sharednet_name"];
	if(trim($_POST["groupnameAdd"])<>null){$sharednet_name=$_POST["groupnameAdd"];}
	$tpl=new templates();
	$sharednet_name=replace_accents($sharednet_name);
	$_POST["scope"]=replace_accents($_POST["scope"]);
	
	$sql="
	INSERT INTO dhcpd_sharednets (`sharednet_name`,`scopename`,
	`subnet`,
	`netmask`,
	`range1`,
	`range2`,
	`domain-name-servers1`,
	`domain-name-servers2`,
	`domain-name`,
	`routers`,
	`subnet-mask`,
	`tftp-server-name`,
	`next-server`,
	`pxe_filename`,
	`pxe_root-path`,
	`option-176`,
	`server-name`,
	`wins-server`
	) 
	VALUES('$sharednet_name','{$_POST["scope"]}',
	'{$_POST["subnet"]}',
	'{$_POST["netmask"]}',
	'{$_POST["range1"]}',
	'{$_POST["range2"]}',
	'{$_POST["domain-name-servers1"]}',
	'{$_POST["domain-name-servers2"]}',
	'{$_POST["domain-name"]}',
	'{$_POST["routers"]}',
	'{$_POST["subnet-mask"]}',
	'{$_POST["tftp-server-name"]}',
	'{$_POST["next-server"]}',
	'{$_POST["pxe_filename"]}',
	'{$_POST["pxe_root-path"]}',
	'{$_POST["option-176"]}',
	'{$_POST["server-name"]}',
	'{$_POST["wins-server"]}'
	
	)
	
	";
	
	if(trim($_POST["ID"])>0){
		$sql="UPDATE dhcpd_sharednets SET
		`sharednet_name`='$sharednet_name',
		`scopename`='{$_POST["scope"]}',
		`subnet`='{$_POST["subnet"]}',
		`netmask`='{$_POST["netmask"]}',
		`range1`='{$_POST["range1"]}',
		`range2`='{$_POST["range2"]}',
		`domain-name-servers1`='{$_POST["domain-name-servers1"]}',
		`domain-name-servers2`='{$_POST["domain-name-servers2"]}',
		`domain-name`='{$_POST["domain-name"]}',
		`routers`='{$_POST["routers"]}',
		`subnet-mask`='{$_POST["subnet-mask"]}',
		`tftp-server-name`='{$_POST["tftp-server-name"]}',
		`next-server`='{$_POST["next-server"]}',
		`pxe_filename`='{$_POST["pxe_filename"]}',
		`pxe_root-path`='{$_POST["pxe_root-path"]}',
		`option-176`='{$_POST["option-176"]}',
		`server-name`='{$_POST["server-name"]}',
		`wins-server`='{$_POST["wins-server"]}'
		WHERE ID='{$_POST["ID"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;echo "\n$sql\n";return;}
	
}

function shared_del(){
	$sql="DELETE FROM dhcpd_sharednets WHERE ID={$_POST["DelDHCPShared"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;echo "\n$sql\n";return;}	

	
}
function shared_apply(){
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?restart-dhcpd=yes');
}
