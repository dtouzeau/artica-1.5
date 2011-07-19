<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.os.system.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["perfs"])){perfs();exit;}
	if(isset($_GET["cache_mem"])){save();exit;}
	js();

	
function js(){

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{tune_squid_performances}");
	$page=CurrentPageName();
	$html="
		YahooWin3('700','$page?popup=yes','$title');
	
	";
		echo $html;
	
	
	
	
}


function popup(){
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/performance-tuning-128.png'></td>
		<td valign='top'>
		<div class=explain>{tune_squid_performances_explain}</div>
		
		<div id='squidperfs'></div></td>
	</tr>
	</table>
	
	<script>
		function refreshPerfs(){
			LoadAjax('squidperfs','$page?perfs=yes');
		}
		
		refreshPerfs();
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function perfs(){
	include_once("ressources/class.os.system.tools.inc");
	$os=new os_system();
	$page=CurrentPageName();
	$sock=new sockets();
	$mem=$os->memory();
	$squid=new squidbee();
	$mem=$mem["ram"]["total"];
	$memory=$mem/1000;
	$mem_cached=round($memory*0.2);
	$cache_mem=$squid->global_conf_array["cache_mem"];
	$fqdncache_size=$squid->global_conf_array["fqdncache_size"];
	$ipcache_size=$squid->global_conf_array["ipcache_size"];
	$ipcache_low=$squid->global_conf_array["ipcache_low"];
	$ipcache_high=$squid->global_conf_array["ipcache_high"];
	
	if(preg_match("#([0-9]+)\s+#",$cache_mem,$re)){$cache_mem=$re[1];}
	
	$swappiness=$sock->getFrameWork("cmd.php?sysctl-value=yes&key=".base64_encode("vm.swappiness")); //15
	$vfs_cache_pressure=$sock->getFrameWork("cmd.php?sysctl-value=yes&key=".base64_encode("vm.vfs_cache_pressure")); //50
	$overcommit_memory=$sock->getFrameWork("cmd.php?sysctl-value=yes&key=".base64_encode("vm.overcommit_memory")); //2
	if(preg_match("#([0-9]+)#",$overcommit_memory,$re)){$overcommit_memory=$re[1];}
	

/*
 * echo 1 > /proc/sys/net/ipv4/ip_forward echo 1 > /proc/sys/net/ipv4/ip_nonlocal_bind echo 0 > /proc/sys/net/ipv4/conf/all/rp_filter echo 1024 65535 > /proc/sys/net/ipv4/ip_local_port_range echo 102400 > /proc/sys/net/ipv4/tcp_max_syn_backlog echo 1000000 > /proc/sys/net/ipv4/ip_conntrack_max echo 1000000 > /proc/sys/fs/file-max echo 60 > /proc/sys/kernel/msgmni echo 32768 > /proc/sys/kernel/msgmax echo 65536 > /proc/sys/kernel/msgmnb :: Maximizing Kernel configuration 
 */	
	

	
	$html="
	<input type='hidden' id='cache_mem' value='$mem_cached'>
	<input type='hidden' id='swappiness' value='15'>
	<input type='hidden' id='vfs_cache_pressure' value='50'>
	<input type='hidden' id='overcommit_memory' value='2'>
	<input type='hidden' id='fqdncache_size' value='51200'>
	<input type='hidden' id='ipcache_size' value='51200'>
	
	<input type='hidden' id='ipcache_low' value='90'>
	<input type='hidden' id='ipcache_high' value='95'>
	
	
	<table cellspacing='0' cellpadding='0' border='0' class='tableView'>

<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{current_value}</th>
		<th>&nbsp;</th>
		<th>{proposal}</th>
	</tr>

		<tr class=oddRow>
		<td align='right' class=legend nowrap>squid:{cache_mem}:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>{$cache_mem}MB</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>{$mem_cached}MB</strong></td>
		</tr>
		<tr>
		<td align='right' class=legend nowrap>{fqdncache_size}:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>$fqdncache_size</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>51200</strong></td>
		</tr>		
		
		
		<tr class=oddRow>
		<td align='right' class=legend nowrap>{ipcache_size}:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>$ipcache_size</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>51200</strong></td>
		</tr>
		
		<tr>
		<td align='right' class=legend nowrap>{ipcache_low}:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>$ipcache_low</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>90</strong></td>
		</tr>

		<tr class=oddRow>
		<td align='right' class=legend nowrap>{ipcache_high}:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>$ipcache_high</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>95</strong></td>
		</tr>		

		
		<tr>
		<td align='right' class=legend nowrap>Kernel:vm.swappiness:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>$swappiness</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>15</strong></td>
		</tr>				
		
		<tr class=oddRow>
		<td align='right' class=legend nowrap>Kernel:vm.vfs_cache_pressure:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>$vfs_cache_pressure</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>50</strong></td>
		</tr>			
		
		
		<tr>
		<td align='right' class=legend nowrap>Kernel:vm.overcommit_memory:</strong></td>
		<td valign='middle'><strong style='font-size:13px'>$overcommit_memory</td>
		<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
		<td valign='middle'><strong style='font-size:13px'>2</strong></td>
		</tr>
					
		</table>
		<hr>
		<div style='text-align:right'>". button("{apply}","SaveSquidPerfs()")."</div>
		
		
		<script>
	var x_SaveSquidPerfs=function (obj) {
		var tempvalue=obj.responseText;
		refreshPerfs();
	}	
	
	function SaveSquidPerfs(){
		var XHR = new XHRConnection();
		XHR.appendData('cache_mem',document.getElementById('cache_mem').value);
		XHR.appendData('swappiness',document.getElementById('swappiness').value);
		XHR.appendData('vfs_cache_pressure',document.getElementById('vfs_cache_pressure').value);
		XHR.appendData('overcommit_memory',document.getElementById('overcommit_memory').value);
		
		XHR.appendData('fqdncache_size',document.getElementById('fqdncache_size').value);
		XHR.appendData('ipcache_size',document.getElementById('ipcache_size').value);
		XHR.appendData('ipcache_low',document.getElementById('ipcache_low').value);
		XHR.appendData('ipcache_high',document.getElementById('ipcache_high').value);
		
		document.getElementById('squidperfs').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
		XHR.sendAndLoad('$page', 'GET',x_SaveSquidPerfs);	
	}		
		
		
		</script>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function save(){
	
	$squid=new squidbee();
	$squid->global_conf_array["cache_mem"]=$_GET["cache_mem"]." MB";
	$squid->global_conf_array["fqdncache_size"]=$_GET["fqdncache_size"];
	$squid->global_conf_array["ipcache_size"]=$_GET["ipcache_size"];
	$squid->global_conf_array["ipcache_low"]=$_GET["ipcache_low"];
	$squid->global_conf_array["ipcache_high"]=$_GET["ipcache_high"];
	$squid->SaveToLdap();
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?sysctl-setvalue={$_GET["swappiness"]}&key=".base64_encode("vm.swappiness")); //15
	$sock->getFrameWork("cmd.php?sysctl-setvalue={$_GET["vfs_cache_pressure"]}&key=".base64_encode("vm.vfs_cache_pressure")); //15
	$sock->getFrameWork("cmd.php?sysctl-setvalue={$_GET["overcommit_memory"]}&key=".base64_encode("vm.overcommit_memory")); //15
	
	
}


	
	
?>