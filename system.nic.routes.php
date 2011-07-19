<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	
	
	$usersmenus=new usersMenus();
	if($usersmenus->AsSystemAdministrator==false){exit;}
	if(isset($_GET["add-route"])){routes_add();exit;}
	if(isset($_GET["routes-icons"])){routes_icons();exit;}
	if(isset($_GET["routes-popup-add"])){routes_popup_add();exit;}
	if(isset($_GET["routes-listes"])){routes_listes();exit;}
	if(isset($_GET["del-route"])){routes_del();exit;}
popup();


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add_new_route_text=$tpl->_ENGINE_parse_body("{add_new_route_text}");
	$html="
	<table style='width:100%'>
	<tr>
	
		<td valign='top'><div id='routes-listes' style='width:100%;height:540px'></div></td>
		<td valign='top'><div id='routes-icons'></div></td>
	</tr>
	</table>
	<script>
		function RefreshRoutes(){
			LoadAjax('routes-listes','$page?routes-listes=yes');
		
		}
		
		function SystemAddRoute(){
			YahooWin4('455','$page?routes-popup-add=yes','$add_new_route_text');
		}
		
		LoadAjax('routes-icons','$page?routes-icons=yes');
		RefreshRoutes();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function routes_listes(){
		$tpl=new templates();
		$page=CurrentPageName();
		$types[1]="{network_nic}";
		$types[2]="{host}";	
		$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		$users=new usersMenus();
		if($users->AsSystemAdministrator){$AsNetworksAdministrator=1;}else{$AsNetworksAdministrator=0;}			
	
		$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{type}</th>
		<th>{nic}</th>
		<th>{pattern}</th>
		<th>{gateway}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
		
	$sql="SELECT * FROM nic_routes ORDER BY `nic`";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:14px' nowrap>&nbsp;{$types[$ligne["type"]]}</td>
		<td style='font-size:14px' nowrap>&nbsp;{$ligne["nic"]}</td>
		<td style='font-size:14px' nowrap>&nbsp;{$ligne["pattern"]}</td>
		<td style='font-size:14px' nowrap>&nbsp;{$ligne["gateway"]}</td>
		<td>". imgtootltip("delete-32.png","{delete}","DeleteRouteNicsList('{$ligne["zmd5"]}')")."</td>
		</tr>
		";
	}
		
	echo $tpl->_ENGINE_parse_body($html."</table>
	<script>
		var x_DeleteRouteNicsList= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);return;}
				RefreshRoutes();
			}		
	
	
		function DeleteRouteNicsList(md){
			var AsNetworksAdministrator='$AsNetworksAdministrator';
			if(AsNetworksAdministrator!=='1'){alert('$ERROR_NO_PRIVS');return;}				
			var XHR = new XHRConnection();
			XHR.appendData('del-route',md);
			XHR.sendAndLoad('$page', 'GET',x_DeleteRouteNicsList);		
		
		}
	</script>	
	");	
		 
	
}

function routes_del(){
$sock=new sockets();
$sock->getFrameWork("cmd.php?ip-del-route={$_GET["del-route"]}");
	
}

function routes_icons(){
	
	$add=Paragraphe("add-64.png","{add}","{add_new_route_text}","javascript:SystemAddRoute()");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($add);

}

function routes_popup_add(){
	$sock=new sockets();
	$nic=new networking();
	$tpl=new templates();
	$page=CurrentPageName();
	while (list ($num, $val) = each ($nic->array_TCP) ){
		if($val==null){continue;}
		$nics[$num]="$num:$val";
		
	}
	
	
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$users=new usersMenus();
	if($users->AsSystemAdministrator){$AsNetworksAdministrator=1;}else{$AsNetworksAdministrator=0;}
	
	$nics[null]="{select}";
	
	$types[1]="{network_nic}";
	$types[2]="{host}";
	
	
	$html="
	
	<div id='add-new-routes-nics'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{nic}:</td>
		<td>". Field_array_Hash($nics,"route-nic",null,"style:font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{type}:</td>
		<td>". Field_array_Hash($types,"route-type",1,"style:font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{pattern}:</td>
		<td valign='top'>
		<table style='width:120px'>
			<tr>
			<td width=1% nowrap>" . Field_text('net_1',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('net_2',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('net_3',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('net_4',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>&nbsp;/&nbsp;</td>
			<td width=1% nowrap>" . Field_text('cdir',null,'width:35px;font-size:13px;padding:3px')."</td>	 
		 </tr>
		</table>
		</td>
	</tr>	
	
	
	<tr>
		<td class=legend>{gateway}:</td>
		<td valign='top'>
		<table style='width:120px'>
			<tr>
			<td width=1% nowrap>" . Field_text('gw_1',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('gw_2',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('gw_3',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>			
			<td width=1% nowrap>" . Field_text('gw_4',null,'width:35px;font-size:13px;padding:3px')."</td>
		 </tr>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","SaveNewRouteNic()")."</td>
	</tr>
	</table>
	</div>
	<script>
		var x_SaveNewRouteNic= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			RefreshRoutes();
			
			
			}		
	
	
		function SaveNewRouteNic(){
			var AsNetworksAdministrator='$AsNetworksAdministrator';
			if(AsNetworksAdministrator!=='1'){alert('$ERROR_NO_PRIVS');return;}				
			var XHR=XHRParseElements('add-new-routes-nics');
			XHR.appendData('add-route','yes');
			XHR.sendAndLoad('$page', 'GET',x_SaveNewRouteNic);		
		
		}
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);

}


function routes_add(){
	$pattern="{$_GET["net_1"]}.{$_GET["net_2"]}.{$_GET["net_3"]}.{$_GET["net_4"]}";
	$gw="{$_GET["gw_1"]}.{$_GET["gw_2"]}.{$_GET["gw_3"]}.{$_GET["gw_4"]}";
	$cdir="{$_GET["cdir"]}";
	
	$ip=new networking();
	if(!$ip->checkIP($pattern)){echo "IP $pattern\nFailed";return;}
	if(!$ip->checkIP($gw)){echo "Gateway $gw\nFailed";return;}		
	$route_nic=$_GET["route-nic"];
	$type=$_GET["route-type"];
	$md5=md5("$route_nic$type$cdir$gw$pattern");
	$sql="INSERT INTO nic_routes (`type`,`gateway`,`pattern`,`zmd5`,`nic`)
	VALUES('$type','$gw','$pattern/$cdir','$md5','$route_nic');";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ip-build-routes=yes");
}



