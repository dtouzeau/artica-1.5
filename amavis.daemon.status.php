<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["max_servers"])){max_servers_save();exit;}
	
status();	
	
function status(){
	
	$amavis=new amavis();
	$max_servers=$amavis->main_array["BEHAVIORS"]["max_servers"];
	$tpl=new templates();
	$page=CurrentPageName();
	
	if(!is_file("ressources/logs/amavis.infos.array")){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?amavis-watchdog=yes");
	}
	
	
	$table="<table style='width:250px'>
	<th colspan=2>{cpu}</th>
	<th>&nbsp;</th>
	<th>{type}</th>
	<th>&nbsp;</th>
	<th>RSS</th>
	<th>VMSIZE</th>
	</tr>
	
	";
	
	$datas=unserialize(@file_get_contents("ressources/logs/amavis.infos.array"));
	
	
	$childs=0;
		while (list ($pid, $array) = each ($datas)){
			$TYPE=$array["TYPE"];
			$CPU=$array["CPU"];
			$TIME=$array["TIME"];
			$RSS=$array["RSS"];
			$VMSIZE=$array["VMSIZE"];
			$color="#5DD13D";
			if($CPU>75){$color="#F59C44";}
			if($CPU>85){$color="#D32D2D";}			
			$childs_text='-';
			if($TYPE<>"master"){if($TYPE<>"virgin"){if($TYPE<>"virgin child"){$childs++;$childs_text=$childs;}}}
			$pourc=pourcentage_basic($CPU,$color,"&nbsp;$CPU%");
			$TOT_RSS=$TOT_RSS+$RSS;
			$TOT_VMSIZE=$TOT_VMSIZE+$VMSIZE;
		$table=$table."
		<tr>
			<td>$pourc</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>$CPU%</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>$childs_text</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>$TYPE</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>{$TIME}Mn</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>{$RSS}Mb</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>{$VMSIZE}Mb</td>
		</tr>
		
		";
			
			
			
		}
		
		if(preg_match("#([0-9]+)\*([0-9]+)#",$amavis->main_array["BEHAVIORS"]["child_timeout"],$re)){
			$seconds=intval($re[2]);
			$int=intval($re[1]);
			$total_seconds=round($int*$seconds)/60;
		}
		
		for($i=1;$i<60;$i++){
			if($i<10){$t="0$i";}else{$t=$i;}
			$mins[$i]=$t;
		}
		

			
	$table=$table."
		<tr>
			<td>&nbsp;</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>&nbsp;</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>&nbsp;</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>&nbsp;</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>&nbsp;</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>{$TOT_RSS}Mb</td>
			<td width=1% nowrap style='font-size:11px;font-weight:bold'>{$TOT_VMSIZE}Mb</td>
		</tr>	
	
	</table>";	

	$html="<table style='width:100%'>
	<tr>
	<td valign='top'>$table</td>
		<td valign='top' align='left' width=99%>
			<div style='font-size:14px;font-weight:bold;margin-bottom:10px'>{processes}:$childs/$max_servers {used}</div>
			<table style='width:100%'>
			<tr>
				<td class=legend>{processes}:</td>
				<td>". Field_text("max_servers",$max_servers,"font-size:16px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend nowrap>{child_ttl}:</td>
				<td style='font-size:16px;padding:3px'>". Field_array_Hash($mins,"child_timeout",$total_seconds,"style:font-size:16px;padding:3px")."&nbsp;Mn</td>
			</tr>	
			<tr><td colspan=2 align='right'><i style='font-size:12px'>{$amavis->main_array["BEHAVIORS"]["child_timeout"]} = {$total_seconds}Mn</i></td></tr>
			<tr>
				<td colspan=2 align='right'><a href=\"javascript:blur();\" 
				OnClick=\"javascript:Loadjs('amavis.daemon.watchdog.php')\"
				style='font-size:14px;text-decoration:underline'
				><i>{watchdog_parameters}</i></a></td>		
			<tr>
				<td colspan=2 align=right><hr>". button("{apply}","SaveMaxProcesses()")."</td>
			</tr>
			</table>
			<br>
			<div style='text-align:right'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('main_config_amavis');")."</div>
			<br>
			<div class=explain id='mmmdiv'>{amavis_max_server_explain}</div>
			
	</tr>
	</table>
	
	<script>
	var x_SaveMaxProcesses= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshTab('main_config_amavis');
	}		
	
	function SaveMaxProcesses(){
		var XHR = new XHRConnection();
		XHR.appendData('max_servers',document.getElementById('max_servers').value);
		XHR.appendData('child_timeout',document.getElementById('child_timeout').value);		
		document.getElementById('mmmdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveMaxProcesses);
		}	

	</script>
	
	
	";
	
			
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}	

function max_servers_save(){
	$amavis=new amavis();
	$amavis->main_array["BEHAVIORS"]["max_servers"]=$_GET["max_servers"];
	$amavis->main_array["BEHAVIORS"]["child_timeout"]="{$_GET["child_timeout"]}*60";
	$amavis->Save();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-ssl=yes");
	
}
