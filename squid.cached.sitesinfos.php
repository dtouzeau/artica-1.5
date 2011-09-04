<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	if(isset($_GET["caches-control"])){cache_control();exit;}
	if(isset($_GET["AddCachedSitelist-js"])){AddCachedSitelist_js();exit;}
	if(isset($_GET["AddCachedSitelist-popup"])){AddCachedSitelist_popup();exit;}
	if(isset($_GET["refresh_pattern_site"])){AddCachedSitelist_save();exit;}
	if(isset($_GET["AddCachedSitelist-delete"])){AddCachedSitelist_js_delete();exit;}
	if(isset($_GET["sites-list"])){WEBSITES_LIST();exit;}
	if(isset($_GET["delete-id"])){AddCachedSitelist_delete();exit;}
	if(isset($_GET["js"])){js();exit;}
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body("{cache}::{cache_control}");
	$html="YahooWin5('700','$page?caches-control=yes&byjs=yes','$title');";
	echo $html;
	
}

function cache_control(){
	$page=CurrentPageName();
	echo "<div id='cached_sites_infos' style='width:100%;height:650px;overflow:auto'><center><img src=\"img/wait_verybig.gif\"></center></div>
<script>LoadAjax('cached_sites_infos','$page?sites-list=yes');</script>";
	
}

	
function AddCachedSitelist_js(){
	$tpl=new templates();
	$add_new_cached_web_site=$tpl->_ENGINE_parse_body('{add_new_cached_web_site}');
	$page=CurrentPageName();
	$html="
		function AddCachedSitelistStart(){
			YahooWin3('600','$page?AddCachedSitelist-popup=yes&id={$_GET["id"]}','$add_new_cached_web_site');
			
		}
		
		var x_AddCachedSitelistSave= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			if(document.getElementById('AddCachedSitelistDiv')){
				document.getElementById('AddCachedSitelistDiv').innerHTML='';
			}
			if(document.getElementById('cached_sites_infos')){
				LoadAjax('cached_sites_infos','squid.cached.sitesinfos.php?sites-list=yes');
			}
			YahooWin3Hide();
		}			
		
		function AddCachedSitelistSave(){
			var XHR = new XHRConnection();
			XHR.appendData('id',document.getElementById('id').value);
			XHR.appendData('refresh_pattern_site',document.getElementById('refresh_pattern_site').value);
			XHR.appendData('refresh_pattern_min',document.getElementById('refresh_pattern_min').value);
			XHR.appendData('refresh_pattern_pourc',document.getElementById('refresh_pattern_pourc').value);
			XHR.appendData('refresh_pattern_max',document.getElementById('refresh_pattern_max').value);
			XHR.appendData('refresh_pattern_option',document.getElementById('refresh_pattern_option').value);
			
			
			document.getElementById('AddCachedSitelistDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_AddCachedSitelistSave);			
		
		}
		

		
		function AddCachedSiteListCheckEnter(e){
			if(checkEnter(e)){AddCachedSitelistSave();}
		}
		
	
	AddCachedSitelistStart();";
	echo $html;
}

function AddCachedSitelist_js_delete(){
	$page=CurrentPageName();
	$html="	
		var x_AddCachedSitelist_js_delete= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			
			if(document.getElementById('cached_sites_infos')){
				LoadAjax('cached_sites_infos','squid.cached.sitesinfos.php?sites-list=yes');
			}
			
		}		

		function AddCachedSitelist_js_delete(){
			var XHR = new XHRConnection();
			XHR.appendData('delete-id','{$_GET["AddCachedSitelist-delete"]}');
			if(document.getElementById('cached_sites_infos')){
				document.getElementById('cached_sites_infos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			}

			XHR.sendAndLoad('$page', 'GET',x_AddCachedSitelist_js_delete);	
			
		}


AddCachedSitelist_js_delete()";
echo $html;
}

function AddCachedSitelist_delete(){
	
	$sql="DELETE FROM squid_speed WHERE ID={$_GET["delete-id"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql\n$q->mysql_error";return;}
}


function AddCachedSitelist_save(){
	$pattern=$_GET["refresh_pattern_site"];
	$pattern=str_replace(".","\.",$pattern);
	$pattern=str_replace("*",".*?",$pattern);

	
if($_GET["refresh_pattern_min"]<5){$_GET["refresh_pattern_min"]=5;}
	$sql="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('{$_GET["refresh_pattern_site"]}','{$_GET["refresh_pattern_min"]}','{$_GET["refresh_pattern_pourc"]}',
	'{$_GET["refresh_pattern_max"]}',
	'{$_GET["refresh_pattern_option"]}'
	);";	
	
	$id=$_GET["id"];
	if($id>0){
		$sql="UPDATE squid_speed SET 
			domain='{$_GET["refresh_pattern_site"]}',
			refresh_pattern_min='{$_GET["refresh_pattern_min"]}',
			refresh_pattern_perc='{$_GET["refresh_pattern_pourc"]}',
			refresh_pattern_max='{$_GET["refresh_pattern_max"]}',
			refresh_pattern_options='{$_GET["refresh_pattern_option"]}'
			WHERE ID='{$_GET["id"]}'
			";
	}
	
	
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql\n$q->mysql_error";return;}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
	
}

function AddCachedSitelist_popup(){
	
	$option[null]="---------";
	$option["override-lastmod"]="override-lastmod";
	$option["override-expire"]="override-expire";
	$option["reload-into-ims"]="reload-into-ims";
	$option["override-expire ignore-no-cache ignore-no-store ignore-private"]="{ignore_all}";
	$option["ignore-reload"]="ignore-reload";
	$option["reload-into-ims ignore-no-cache"]="reload-into-ims+ignore-no-cache";
	

	if($_GET["id"]>0){
		$sql="SELECT * FROM squid_speed WHERE ID={$_GET["id"]}";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$domain=$ligne["domain"];
		$pourc=$ligne["refresh_pattern_perc"];
		$refresh_pattern_min=$ligne["refresh_pattern_min"];
		$refresh_pattern_max=$ligne["refresh_pattern_max"];
		$refresh_pattern_option=$ligne["refresh_pattern_options"];
		}
		
		$refresh_pattern_opt=Field_array_Hash($option,"refresh_pattern_option",$refresh_pattern_option,null,null,0,"font-size:13px;padding:3px");
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/windows-internet-128.png'></td>
	<td valign='top'>
	<div id='AddCachedSitelistDiv'>
	". Field_hidden("id","{$_GET["id"]}")."
	<div style='font-size:13px;padding:5px'>{squid_refresh_pattern_explain}</div>
	<table style='width:99%'>
	<tr>
		<td class=legend style='font-size:13px'>{website_name}:</td>
		<td>". Field_text("refresh_pattern_site",$domain,'font-size:13px;padding:3px',null,null,null,false,"AddCachedSiteListCheckEnter(event)")."</td>
		<td width=1%>". help_icon("{refresh_pattern_site}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{minimal_time}:</td>
		<td style='font-size:13px'>". Field_text("refresh_pattern_min",$refresh_pattern_min,'width:45px;font-size:13px;padding:3px',null,null,null,false,"AddCachedSiteListCheckEnter(event)")."&nbsp;Mn</td>
		<td width=1%>". help_icon("{refresh_pattern_min}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{percentage}:</td>
		<td style='font-size:13px'>". Field_text("refresh_pattern_pourc",$pourc,'width:45px;font-size:13px;padding:3px',null,null,null,false,"AddCachedSiteListCheckEnter(event)")."&nbsp;%</td>
		<td width=1%>". help_icon("{refresh_pattern_pourc}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{maximal_time}:</td>
		<td style='font-size:13px'>". Field_text("refresh_pattern_max",$refresh_pattern_max,'width:45px;font-size:13px;padding:3px',null,null,null,false,"AddCachedSiteListCheckEnter(event)")."&nbsp;Mn</td>
		<td width=1%>". help_icon("{refresh_pattern_max}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{option}:</td>
		<td style='font-size:13px'>$refresh_pattern_opt</td>
		<td width=1%>". help_icon("{refresh_pattern_option}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>
			". button("{apply}","AddCachedSitelistSave()").
		"</td>
	</tr>
	</table>		
	</div>
	</td>
	</tr>
	</table>
	<center><img src='img/refresh_pattern_graph.gif'></center>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
	
	
function WEBSITES_LIST(){
	$q=new mysql();
	if(isset($_GET["remove-all"])){
		$sql="TRUNCATE TABLE `squid_speed`";
		$q->QUERY_SQL($sql,"artica_backup");
	}
	
	if(isset($_GET["defaults"])){WEBSITES_DEFAULTS();}
	
    $page=CurrentPageName();
	$sql="SELECT * FROM `squid_speed` WHERE `domain` IS NOT NULL";
	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error";}
	$html="
	
	<hr>
	<div class=explain>{refresh_pattern_intro}</div>
	<div style='text-align:right'>
	<table style='width:99%'>
	<tr>
	<td width=99%>&nbsp;</td>
	
	<td align='right' width=1%>". imgtootltip("proxy-delete-32.png","{delete_all} & {add_default_settings}","LoadAjax('cached_sites_infos','squid.cached.sitesinfos.php?sites-list=yes&defaults=yes&remove-all=yes');")."</td>
	<td align='right' width=1%>". imgtootltip("filter-add-32.png","{add_default_settings}","LoadAjax('cached_sites_infos','squid.cached.sitesinfos.php?sites-list=yes&defaults=yes');")."</td>
	<td align='right' width=1%>". imgtootltip("website-add-32.png","{add_new_cached_web_site}","Loadjs('$page?AddCachedSitelist-js=yes')")."</td>
	</tr>
	</div>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
	<tr>
		<th>{website}</th>
		<th>{expire_time}</th>
		<th>%</th>
		<th>{limit}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>	";
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select="Loadjs('$page?AddCachedSitelist-js=yes&id={$ligne["ID"]}');";
		
		$ligne["refresh_pattern_min"]=$ligne["refresh_pattern_min"];
		$ligne["refresh_pattern_min"]=distanceOfTimeInWords(0,$ligne["refresh_pattern_min"],true);
		$ligne["refresh_pattern_min"]=str_replace("about","",$ligne["refresh_pattern_min"]);
		
		$ligne["refresh_pattern_max"]=$ligne["refresh_pattern_max"];
		$ligne["refresh_pattern_max"]=distanceOfTimeInWords(0,$ligne["refresh_pattern_max"],true);
		$ligne["refresh_pattern_max"]=str_replace("about","",$ligne["refresh_pattern_max"]);		
		$link="<a href=\"javascript:blur();\" OnClick=\"javascript:$select\" style='font-size:12px;font-weight:bold;text-decoration:underline'>";
		if(trim($ligne["domain"])=='.'){$ligne["domain"]="{all}";}
			$html=$html. "
			<tr class=$classtr>
				<td align='left' >$link{$ligne["domain"]}</a></td>
				<td width=1% nowrap>$link{$ligne["refresh_pattern_min"]}</a></td>
				<td width=1%  align='right'>$link{$ligne["refresh_pattern_perc"]}%</a></td>
				<td width=1%  nowrap>$link{$ligne["refresh_pattern_max"]}</a></td>
				<td width=1%>". imgtootltip("delete-32.png","{delete}","Loadjs('$page?AddCachedSitelist-delete={$ligne["ID"]}')")."</td>
			</tr>
			";
		
		
	}
	
	$html=$html. "</table>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function WEBSITES_DEFAULTS(){
	

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .(gif|png|jpg|jpeg|ico)$','10080',90,43200,'override-expire ignore-no-cache ignore-no-store ignore-private');";


$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .(iso|avi|wav|mp3|mp4|mpeg|swf|flv|x-flv)$', 0, 90, 260009, 'override-expire ignore-no-cache ignore-no-store ignore-private');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .(deb|rpm|exe|zip|tar|tgz|ram|rar|bin|ppt|doc|tiff|bz2|gz)$',0, 90, 260009, 'override-expire ignore-no-cache ignore-no-store ignore-private');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .(html|htm|css|js|xml)$', 1440, 40, 40320,'');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .kaspersky-labs.com/*.(diff|exe|klz|zip)$',1440,100,28800,'reload-into-ims ignore-no-cache');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .avast.com/*.(exe|vpu)$',1440, 100, 28800, 'reload-into-ims ignore-no-cache');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .avira-update.com/*.gz$', 1440, 100, 28800, 'reload-into-ims ignore-no-cache');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i global-download.acer.com/*/Driver/*zip', 1440, 100, 260009,'reload-into-ims ignore-no-cache');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .windowsupdate.com/*.(cab|exe|dll|msi|psf)' ,0 ,80, 43200, 'reload-into-ims ignore-no-cache');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://*.windowsupdate.microsoft.com/' , 1440 ,80,20160,'reload-into-ims');";
$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://*.update.microsoft.com/' , 1440 ,80, 20160,' reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://download.microsoft.com/' , 1440 ,80, 20160,' reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://windowsupdate.microsoft.com/' , 1440 ,80, 20160,' reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://office.microsoft.com/' , 1440 ,80, 20160,' reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://w?xpsp[0-9].microsoft.com/' , 1440, 80, 20160,' reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://w2ksp[0-9].microsoft.com/' , 1440 ,80, 20160,' reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('http://*.archive.ubuntu.com/' , 1440 ,80, 20160,' reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('*.debian.org/' 1440, 80, 20160' , reload-into-ims');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('-i .microsoft.com/*.(cab|exe|dll|msi)',10080,100,43200,'reload-into-ims ignore-no-cache');";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
VALUES('-i ^http://*.gmail*/*',720 ,100,4320,'')";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
VALUES('-i ^http://*.googlesyndication*/*',1440 ,100,4320,'')";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
VALUES('-i ^http://notify*dropbox.com',1440 ,100,2880,'reload-into-ims ignore-no-cache')";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
VALUES('-i ^http://safebrowsing-cache.google.com/*',1440 ,100,2880,'reload-into-ims ignore-no-cache')";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
VALUES('-i ^http://*gmodules.com/*',1440 ,100,2880,'reload-into-ims ignore-no-cache')";



http://safebrowsing-cache.google.com/


$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
VALUES('-i ^http://*google.*/*',2880 ,100,4320,'')";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
VALUES('-i ^http://*.ubuntu.*/*',2880 ,100,4320,'')";

$t[]="INSERT INTO squid_speed (domain,refresh_pattern_min,refresh_pattern_perc,refresh_pattern_max,refresh_pattern_options)
	VALUES('.','0',100,43200,'reload-into-ims override-lastmod');";




$q=new mysql();
while (list ($num, $val) = each ($t)){
	$q->QUERY_SQL($val,"artica_backup");
}
$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");

	
}

?>