<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.roundcube.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');

	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["form"])){switch_forms();exit;}
	if(isset($_GET["RoundCubeHTTPEngineEnabled"])){main_settings_edit();exit;}
	if(isset($_GET["nmap_delete_ip"])){main_delete_network();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["AddNmapNetwork"])){main_add_nework();exit;}
	if(isset($_GET["debug_level"])){main_save_roundcube_settings();exit;}
	if(isset($_GET["script"])){ajax_js();exit;}
	if(isset($_GET["ajax-pop"])){ajax_pop();exit;}
	if(isset($_GET["roundcubestatus"])){echo main_status();exit;}
	if(isset($_GET["roundcube-pluginv3-list"])){echo pluginv3_table();exit;}
	if(isset($_GET["enable-plugin"])){pluginv3_enable();exit;}
	if(isset($_GET["form1"])){form1_js();exit;}
	if(isset($_GET["form2"])){form2_js();exit;}
	if(isset($_GET["plugins"])){formplugins_js();exit;}
	if(isset($_GET["logslogs"])){echo main_rlogs_parse();exit;}
	
	if(isset($_GET["plugins-sieve"])){plugins_sieve_js();exit;}
	if(isset($_GET["plugin-sieve-popup"])){plugins_sieve_popup();exit;}
	if(isset($_GET["RoundCubeEnableSieve"])){plugins_sieve_save();exit;}
	
	if(isset($_GET["plugins-calendar"])){plugins_calendar_js();exit;}
	if(isset($_GET["plugin-calendar-popup"])){plugins_calendar_popup();exit;}
	if(isset($_GET["RoundCubeEnableCalendar"])){plugins_calendar_save();exit;}
	if(isset($_GET["roundcube-rebuild"])){RoundCube_restart();exit;}	

	
function ajax_pop(){
echo main_tabs();		
}

function form1_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{webserver_parameters}');
	echo "YahooWin2(600,'$page?form=form1','$title');";
	
}
	function form2_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{roundcube_parameters}');
	echo "YahooWin2(650,'$page?form=form2','$title');";
	
}

function plugins_sieve_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{plugin_sieve}');
	echo "
	function RoundCubeEnableSievePage(){
		YahooWin2(450,'$page?plugin-sieve-popup=yes','$title');
		}
	RoundCubeEnableSievePage();";	
}
function plugins_calendar_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{plugin_calendar}');
	echo "
	function RoundCubeEnableCalendarPage(){
		YahooWin2(450,'$page?plugin-calendar-popup=yes','$title');
		}
	RoundCubeEnableCalendarPage();";	
}

function formplugins_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{plugins}');
	echo "YahooWin2(700,'$page?form=plugins','$title');";
	
}


function ajax_index(){
	$tpl=new templates();
	$page=CurrentPageName();
	$configure=Paragraphe("rebuild-64.png","{generate_config}","{rebuild_roundcube_parameters}","javascript:RoundCubeRebuild()",null,280);
	$apply_upgrade_help=$tpl->javascript_parse_text("{apply_upgrade_help}");
	
	echo $tpl->_ENGINE_parse_body("
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/roundcube-original-logo.png'><div class=explain>{about_roundcube}</div></td>
		<td valign='top'>
				<div id='roundcube_daemon_status'>$status</div>
				<br><div id='roundcube-rebuild-div'>$configure</div>
		</td>
	</tr>
	</table>
	<br>")."
	
	
	<script>
		RoundCubeStatus();
		
		var x_RoundCubeRebuild= function (obj) {
			alert('$apply_upgrade_help');
	 		RefreshTab('main_config_roundcube');
		}		
		
		function RoundCubeRebuild(){
			var XHR = new XHRConnection();
			XHR.appendData('roundcube-rebuild','yes');
			document.getElementById('roundcube-rebuild-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_RoundCubeRebuild);	
		}
		
		
	</script>
	";	
	
	 
}

function RoundCube_restart(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?roundcube-restart=yes");
	
	
}


function ajax_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$x=$tpl->javascript_parse_text('{confirm_rebuild}');
	$title=$tpl->_ENGINE_parse_body('{APP_ROUNDCUBE}');
	$prefix=str_replace(".","_",$page);
	$startfunc="LoadMainRoundCube();";
	
	if(isset($_GET["in-front-ajax"])){$startfunc="LoadInLineMainRoundCube();";}
	
	
	
	$html="

	function LoadMainRoundCube(){
		YahooWinS(745,'$page?ajax-pop=yes','$title');
	}
	
	function LoadInLineMainRoundCube(){
		$('#BodyContent').load('$page?ajax-pop=yes');
	}
	
	
	var x_RebuildTables= function (obj) {
	 	RefreshTab('main_config_roundcube');
	}		
	
	function RebuildTables(){
			var z=confirm(x);
			if (z){
				var XHR = new XHRConnection();
				XHR.appendData('main',mysql);
				XHR.appendData('rebuild','yes');
				XHR.sendAndLoad('$page', 'GET',x_RebuildTables);	
			}
		}
		
function RoundCubeStatus(){
		LoadAjax('roundcube_daemon_status','$page?roundcubestatus=yes');
	
	}
	
var x_RoundCubepluginv3Enable= function (obj) {
	var results=obj.responseText;
	alert(results);
	LoadAjax('rndcube3pluglist','$page?roundcube-pluginv3-list=yes');
	}		
	
function RoundCubepluginv3Enable(field){
	var XHR = new XHRConnection();
	XHR.appendData('enable-plugin',field);
	XHR.appendData('value',document.getElementById(field).value);
	document.getElementById('rndcube3pluglist').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_RoundCubepluginv3Enable);	
}
		
$startfunc
";
	
	echo $html;
	
	
	
	
}
function main_tabs(){
	
	$page=CurrentPageName();
	$array["index"]='{index}';
	$array["settings"]='{settings}';
	$array["conf"]='{conf}';
	$array["rlogs"]='{rlogs}';
	$array["mysql"]='{mysql}';
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n");
		
		}
	
	
	
	return "
	<div id=main_config_roundcube style='width:100%;height:480px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_roundcube').tabs();
			

			});
		</script>";		
}

function plugins_sieve_popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$RoundCubeEnableSieve=$sock->GET_INFO("RoundCubeEnableSieve");
	$enable=Paragraphe_switch_img("{plugin_sieve_enable}","{plugin_sieve_text}","RoundCubeEnableSieve",$RoundCubeEnableSieve,279);
	
	$html="
	<div id='RoundCubeEnableSieveDiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/filter-128.png'></td>
		<td valign='top'>$enable</td>
	</tr>
	</table>
	</div>
	<div style='width:100%;text-align:right'>". button("{apply}","RoundCubeEnableSieve()")."</div>
	<script>
	
	var x_RoundCubeEnableSieve= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RoundCubeEnableSievePage();
	}	
	
		function RoundCubeEnableSieve(){
			var XHR = new XHRConnection();
			XHR.appendData('RoundCubeEnableSieve',document.getElementById('RoundCubeEnableSieve').value);
			document.getElementById('RoundCubeEnableSieveDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_RoundCubeEnableSieve);	
		}	
	
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}
function plugins_calendar_popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$RoundCubeEnableCalendar=$sock->GET_INFO("RoundCubeEnableCalendar");
	$enable=Paragraphe_switch_img("{plugin_calendar_enable}","{plugin_calendar_text}","RoundCubeEnableCalendar",$RoundCubeEnableCalendar,279);
	
	$html="<H1>{plugin_calendar}</H1>
	<div id='RoundCubeEnableSieveDiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/calendar-128.png'></td>
		<td valign='top'>$enable</td>
	</tr>
	</table>
	</div>
	<div style='width:100%;text-align:right'>". button("{apply}","RoundCubeEnableCalendar()")."</div>
	<script>
	
	var x_RoundCubeEnableCalendar= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RoundCubeEnableCalendarPage();
	}	
	
		function RoundCubeEnableCalendar(){
			var XHR = new XHRConnection();
			XHR.appendData('RoundCubeEnableCalendar',document.getElementById('RoundCubeEnableCalendar').value);
			document.getElementById('RoundCubeEnableSieveDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_RoundCubeEnableCalendar);	
		}	
	
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function plugins_sieve_save(){
	$sock=new sockets();
	$sock->SET_INFO("RoundCubeEnableSieve",$_GET["RoundCubeEnableSieve"]);
	$rnd=new roundcube();
	$rnd->Save();
}
function plugins_calendar_save(){
	$sock=new sockets();
	$sock->SET_INFO("RoundCubeEnableCalendar",$_GET["RoundCubeEnableCalendar"]);
	$rnd=new roundcube();
	$rnd->Save();
}

function form_tabs(){
	
	
	if(!isset($_GET["form"])){$_GET["form"]="form1";};
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["form1"]='{page} 1';
	$array["form2"]='{page} 2';
	$tpl=new templates();
	if($users->roundcube_intversion>29){
		$main=base64_encode("MAIN_INSTANCE");
		$plugins=Paragraphe("plugins-64.png",'{plugins}',"{roundcube_plugins_text}","javascript:Loadjs('$page?plugins=yes')");
		$sieve=Paragraphe("filter-64.png",'{plugin_sieve}',"{plugin_sieve_text}","javascript:Loadjs('$page?plugins-sieve=yes')");
		$calendar=Paragraphe("calendar-64.png",'{plugin_calendar}',"{plugin_calendar_text}","javascript:Loadjs('$page?plugins-calendar=yes')");
		$globaladdressBook=Paragraphe("addressbook-64.png","{global_addressbook}","{global_addressbook_explain}",
		"javascript:Loadjs('roundcube.globaladdressbook.php?www=$main')");
	}
	
	
	$form1=Paragraphe("domain-main-64.png","{webserver_parameters}","{webserver_parameters_text}","javascript:Loadjs('$page?form1=yes')");
	$form2=Paragraphe("parameters-64.png","{roundcube_parameters}","{roundcube_parameters_text}","javascript:Loadjs('$page?form2=yes')");
	$Hacks=Paragraphe("Firewall-Secure-64.png","Anti-Hacks","{AntiHacks_roundcube_text}","javascript:Loadjs('roundcube.hacks.php')");
	
	
	
	$html="<table>
	<tr>
		<td valign='top'>$form1</td>
		<td valign='top'>$form2</td>
		<td valign='top'>$globaladdressBook</td>
	</tr>
	<tr>
		<td valign='top'>$plugins</td>
		<td valign='top'>$sieve</td>
		<td valign='top'>$calendar</td>
	</tr>
	<tr>
		<td valign='top'>$Hacks</td>
		<td>&nbsp;</td>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(RoundedLightWhite($html));
			
}



function main_switch(){
	
	switch ($_GET["main"]) {
		case "index":echo ajax_index();exit;break;
		case "conf":echo main_conf();exit;break;
		case "rlogs":echo main_rlogs();exit;break;
		case "nmap-add":echo main_form_add();exit;break;
		case "status":echo main_status();exit;break;
		case "mysql":echo main_mysql();exit;break;
		case "rlogss":echo main_rlogs_parse();exit;break;
		default:main_settings();break;
	}
	
	
	
}

function  main_conf(){
	
	$round=new  roundcube();
	$tbl=explode("\n",$round->RoundCubeLightHTTPD);
	
	while (list ($num, $line) = each ($tbl)){
		if($line<>null){
			$line=htmlentities($line);
			$line=str_replace("\t","&nbsp;&nbsp;&nbsp;",$line);
			$html=$html."<div><code>$line</code></div>";
			
		}
		
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<div style='padding:20px;width:90%;height:400px;overflow:auto;background-color:white'>$html</div>");
	
}

function main_mysql(){
	$user=new usersMenus();
	$roundcube=new roundcube();
	
	if(isset($_GET["rebuild"])){
		$roundcube->RebuildMysql();
	}
	
	$status=$roundcube->ParseMysqlInstall();
	$html="
		
			<table style='width:100%' class=table_form>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{RoundCubePath}:</strong></td>
				<td valign='top' nowrap align='left'><strong>$user->roundcube_folder</td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{roundcube_mysql_sources}:</strong></td>
				<td valign='top' nowrap align='left'><strong>$user->roundcube_mysql_sources</strong></td>
			</tr>	
			<tr>
				<td valign='top' nowrap align='right' class=legend>{database}:</strong></td>
				<td valign='top' nowrap align='left'><strong>roundcubemail</strong></td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{database_status}:</strong></td>
				<td valign='top' nowrap align='left'><strong>$status</strong></td>
			</tr>
			<tr>
			<td valign='top' nowrap align='right' colspan=2>
				<hr>".button("{rebuild}","RebuildTables()")."
			</td>
				
			</tr>													
		</table>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}


function main_errors(){
	
	if(!function_exists('mcrypt_module_open')){
		$error="<div style='color:red'>mcrypt.so module is not loaded</div>";
		
		
	}
	if($error<>null){
		$error="<H5>Errors</H5>$error<hr>";
		
	}
	return $error;
}


function form1(){
	$page=CurrentPageName();
	$user=new usersMenus();
	$round=new roundcube();
	$artica=new artica_general();
	
	$debug_levela=array(1=>"log",2=>"report",4=>"show",8=>"trace");
	$debug_level=Field_array_Hash($debug_levela,'debug_level',$round->roundCubeArray["debug_level"]);
	$tpl=new templates();
	$lighttp_max_load_per_proc=$tpl->_ENGINE_parse_body('{lighttp_max_load_per_proc}');
	if(strlen($lighttp_max_load_per_proc)>40){$lighttp_max_load_per_proc=texttooltip(substr($lighttp_max_load_per_proc,0,37)."...",$lighttp_max_load_per_proc);}
	
	
	
$html="
	<form name='FFM1'>
			<div id='wait'></div>
			<table style='width:100%' class=table_form>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{RoundCubePath}:</strong></td>
				<td valign='top' nowrap align='left'><strong>$user->roundcube_folder</td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{roundcube_web_folder}:</strong></td>
				<td valign='top' nowrap align='left'><strong>$user->roundcube_web_folder</td>
			</tr>			

					
			
			<tr>
				<td valign='top' nowrap align='right' class=legend>{RoundCubeHTTPEngineEnabled}:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_checkbox('RoundCubeHTTPEngineEnabled',1,$round->RoundCubeHTTPEngineEnabled,'{enable_disable}')."</td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{listen_port}:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_text('https_port',$round->https_port,'{enable_disable}','width:30px')."</td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>HTTPS:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_checkbox('ssl_enabled',1,$round->roundCubeArray["ssl_enabled"],'{enable_disable}')."</td>
			</tr>			
			
					
			<tr>
				<td align='right' class=legend>{lighttp_max_proc}:</strong></td>
				<td>" . Field_text('lighttp_max_proc',trim($round->lighttp_max_proc),'width:30px')."</td>
			</tr>
			<tr>
				<td align='right' class=legend>{lighttp_min_proc}:</strong></td>
				<td>" . Field_text('lighttp_min_proc',trim($round->lighttp_min_proc),'width:30px')."</td>
			</tr>
			<tr>
				<td align='right' class=legend>$lighttp_max_load_per_proc:</strong></td>
				<td>" . Field_text('lighttp_max_load_per_proc',trim($round->lighttp_max_load_per_proc),'width:30px')."</td>
			</tr>		
		
			<tr>
				<td align='right' class=legend>{PHP_FCGI_CHILDREN}:</strong></td>
				<td>" . Field_text('PHP_FCGI_CHILDREN',trim($round->PHP_FCGI_CHILDREN),'width:30px')."</td>
			</tr>	
			<tr>
				<td align='right' class=legend>{PHP_FCGI_MAX_REQUESTS}:</strong></td>
				<td>" . Field_text('PHP_FCGI_MAX_REQUESTS',trim($round->PHP_FCGI_MAX_REQUESTS),'width:30px')."</td>
			</tr>				
			<tr>
			<td colspan=2 align='right'>
				".button('{edit}','SaveRoundCubeForm1()')."
			</tr>
			</table>
			</form>		
			<script>
			
			var X_SaveRoundCubeForm1= function (obj) {
				document.getElementById('wait').innerHTML='';
				}			
			
			function SaveRoundCubeForm1(){
				var XHR = new XHRConnection();
				if(document.getElementById('RoundCubeHTTPEngineEnabled').checked){XHR.appendData('RoundCubeHTTPEngineEnabled','1');}else{XHR.appendData('RoundCubeHTTPEngineEnabled','0');}
				if(document.getElementById('ssl_enabled').checked){XHR.appendData('ssl_enabled','1');}else{XHR.appendData('ssl_enabled','0');}
				XHR.appendData('https_port',document.getElementById('https_port').value);
				XHR.appendData('lighttp_max_proc',document.getElementById('lighttp_max_proc').value);
				XHR.appendData('lighttp_min_proc',document.getElementById('lighttp_min_proc').value);
				XHR.appendData('lighttp_max_load_per_proc',document.getElementById('lighttp_max_load_per_proc').value);
				XHR.appendData('PHP_FCGI_CHILDREN',document.getElementById('PHP_FCGI_CHILDREN').value);
				XHR.appendData('PHP_FCGI_MAX_REQUESTS',document.getElementById('PHP_FCGI_MAX_REQUESTS').value);
				document.getElementById('wait').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',X_SaveRoundCubeForm1);	
			}
			
			</script>
			
	
";

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}

function pluginv3_enable(){
	$round=new roundcube();	
	//if($_GET["value"]==1){$_GET["value"]=0;$TEXT="{disabled}";}else{$_GET["value"]=1;$TEXT="{enabled}";}
	$round->roundCubeArray[$_GET["enable-plugin"]]=$_GET["value"];
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($_GET["enable-plugin"]." $TEXT\n");
	$round->Save();
	
}

function pluginsv3(){
	$page=CurrentPageName();
	$user=new usersMenus();
	$round=new roundcube();	
	$plugins="<div id='rndcube3pluglist' style='width:100%;height:450px;overflow:auto'>".pluginv3_table()."</div>";
	$plugins=RoundedLightWhite($plugins);
	
	
$html="$tab
<table style='width:100%'>
<tr>
<td valign='top'>
	<img src='img/128-nodes.png'>
</td>
<td valign='top'>
<h3>{APP_ROUNDCUBE3}&nbsp;{plugins}</H3>
<p class=caption>{APP_ROUNDCUBE3_PLUGINS_EXPLAIN}</p>
$plugins
</td>
</tr>
</table>

";	
	$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
}

function pluginv3_table(){
	$round=new roundcube();	
	
	$html="<table style='width:100%'>";
	if(is_array($round->roundcube_plugins_array)){
	while (list ($num, $line) = each ($round->roundcube_plugins_array)){
		if($num=="new_user_identity"){continue;}
		if($num=="autologon"){continue;}
		if($num=="example_addressbook"){continue;}
		if($num=="password"){continue;}
		if($num=="sieverules"){continue;}
		if($num=="calendar"){continue;}
		
		if($round->roundCubeArray["plugin_$num"]==null){$round->roundCubeArray["plugin_$num"]=0;}
		$enable=Field_numeric_checkbox_img("plugin_$num",$round->roundCubeArray["plugin_$num"]);
		
		
		
		$html=$html."
		<tr>
			<td width=1% valign='top'><img src='img/24-nodes.png'></td>
			<td width=98% valign='top'>$line</td>
			<td width=1% valign='top'>$enable</td>
			<td width=1% valign='top'>".button("{apply}","RoundCubepluginv3Enable('plugin_$num')")."</td>
			<tr><td colspan=4><hr></td></tR>
		</tr>";
	}}
	
	$html=$html."</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function form2(){
	$page=CurrentPageName();
	$user=new usersMenus();
	$round=new roundcube();
	$artica=new artica_general();
	$debug_levela=array(1=>"log",2=>"report",4=>"show",8=>"trace");
	$debug_level=Field_array_Hash($debug_levela,'debug_level',$round->roundCubeArray["debug_level"]);
	$tpl=new templates();
	$lighttp_max_load_per_proc=$tpl->_ENGINE_parse_body('{lighttp_max_load_per_proc}');
	if(strlen($lighttp_max_load_per_proc)>40){$lighttp_max_load_per_proc=texttooltip(substr($lighttp_max_load_per_proc,0,37)."...",$lighttp_max_load_per_proc);}
	$auto_create_user=$tpl->_ENGINE_parse_body('{auto_create_user}');
	if(strlen($auto_create_user)>70){$auto_create_user=texttooltip(substr($auto_create_user,0,67)."...",$auto_create_user);}
	
	$enable_caching=$tpl->_ENGINE_parse_body('{enable_caching}');
	if(strlen($enable_caching)>70){$enable_caching=texttooltip(substr($enable_caching,0,67)."...",$enable_caching);}
	
	
$html="<div id='wait'></div><table style='width:100%' class=table_form>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{user_link}:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_text('user_link',$round->roundCubeArray["user_link"],'width:195px')."</td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{roundcube_ldap_directory}:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_checkbox('ldap_ok',1,$round->roundCubeArray["ldap_ok"])."</td>
			</tr>							
			<tr>
				<td valign='top' nowrap align='right' class=legend>{debug_level}:</strong></td>
				<td valign='top' nowrap align='left'><strong>$debug_level</td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>$enable_caching:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_TRUEFALSE_checkbox('enable_caching',$round->roundCubeArray["enable_caching"])."</td>
			</tr>
			<tr>
				<td valign='top' nowrap align='right' class=legend>{upload_max_filesize}:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_text('upload_max_filesize',$round->roundCubeArray["upload_max_filesize"],'width:30px')."M</td>
			</tr>
			
					
			<tr>
				<td valign='top' nowrap align='right' class=legend>$auto_create_user:</strong></td>
				<td valign='top' nowrap align='left'>" . Field_TRUEFALSE_checkbox('auto_create_user',$round->roundCubeArray["auto_create_user"])."</td>
			</tr>
			<tr>
				<td align='right' class=legend>{default_host}:</strong></td>
				<td>" . Field_text('default_host',trim($round->roundCubeArray["default_host"]),'width:130px')."</td>
			</tr>
			<tr>
				<td align='right' class=legend>{locale_string}:</strong></td>
				<td>" . Field_text('locale_string',trim($round->roundCubeArray["locale_string"]),'width:30px')."</td>
			</tr>		
		
			<tr>
				<td align='right' class=legend>{product_name}:</strong></td>
				<td>" . Field_text('product_name',trim($round->roundCubeArray["product_name"]),'width:180px')."</td>
			</tr>	
			<tr>
				<td align='right' class=legend>{skip_deleted}:</strong></td>
				<td>" . Field_TRUEFALSE_checkbox('skip_deleted',$round->roundCubeArray["skip_deleted"])."</td>
			</tr>
			<tr>
				<td align='right' class=legend>{flag_for_deletion}:</strong></td>
				<td style='padding-left:-3px'>
				<table style='width:100%;margin-left:-4px;padding:0px'>
				<tr>
				<td width=1%  valign='top' style='padding-left:-3px'>
				" . Field_TRUEFALSE_checkbox('flag_for_deletion',$round->roundCubeArray["flag_for_deletion"])."</td>
				<td valign='center' >".help_icon('{flag_for_deletion_text}',true)."</td>
				</tr>
				</table>
				</td>
			</tr>					
			<tr>
			<td colspan=2 align='right'>". button("{edit}","SaveRoundCubeForm2();")."
			
			</td>
			</tr>
			</table>
			<script>
			
			var X_SaveRoundCubeForm2= function (obj) {
				document.getElementById('wait').innerHTML='';
				}			
			
			function SaveRoundCubeForm2(){
				var XHR = new XHRConnection();
				if(document.getElementById('ldap_ok').checked){XHR.appendData('ldap_ok','1');}else{XHR.appendData('ldap_ok','0');}
				if(document.getElementById('enable_caching').checked){XHR.appendData('enable_caching','TRUE');}else{XHR.appendData('enable_caching','FALSE');}
				if(document.getElementById('auto_create_user').checked){XHR.appendData('auto_create_user','TRUE');}else{XHR.appendData('auto_create_user','FALSE');}
				if(document.getElementById('flag_for_deletion').checked){XHR.appendData('flag_for_deletion','TRUE');}else{XHR.appendData('flag_for_deletion','FALSE');}
				XHR.appendData('user_link',document.getElementById('user_link').value);
				XHR.appendData('debug_level',document.getElementById('debug_level').value);
				XHR.appendData('upload_max_filesize',document.getElementById('upload_max_filesize').value);
				XHR.appendData('default_host',document.getElementById('default_host').value);
				XHR.appendData('locale_string',document.getElementById('locale_string').value);
				XHR.appendData('product_name',document.getElementById('product_name').value);
				XHR.appendData('skip_deleted',document.getElementById('skip_deleted').value);
				document.getElementById('wait').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',X_SaveRoundCubeForm2);	
			}
			
			</script>			
			
			
			
			";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
}

function switch_forms(){
	
	if($_GET["form"]=="form1"){$form=form1();}
	if($_GET["form"]=="form2"){$form=form2();}
	if($_GET["form"]=="plugins"){$form=pluginsv3();}
	
	echo $form;
	
}


function main_settings(){

	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		
		<p class=caption>{about_roundcube_engine}</p>".main_errors()."
				".form_tabs()."
		</td>
	</tr>
	</table>
	
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function main_settings_edit(){

	$round=new roundcube();
	while (list ($num, $line) = each ($_GET)){
		$round->$num=$line;
		
	}
	$round->roundCubeArray["ssl_enabled"]=$_GET["ssl_enabled"];
	$round->Save();
	
	
	
	
}

function main_save_roundcube_settings(){
	$round=new roundcube();
	while (list ($num, $line) = each ($_GET)){
		$round->roundCubeArray[$num]=$line;
		
	}
	
	$round->Save();
	}

function  main_status(){
	$users=new usersMenus();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('RoundCubeStatus'));	
	$status=DAEMON_STATUS_ROUND("ROUNDCUBE",$ini,null);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);	
	
}

function main_rlogs(){
$tpl=new templates();
$page=CurrentPageName();
	echo $tpl->_ENGINE_parse_body(RoundedLightWhite("<div style='padding:20px;height:350px;overflow:auto' id='rlogs'></div>"))."
	<script>
		LoadAjax('rlogs','$page?logslogs=yes');
	</script>
	";
	
	
}

function main_rlogs_parse(){
	
	$datas=explode("\n",@file_get_contents('/usr/share/roundcube/logs/errors'));
	$datas=array_reverse($datas, TRUE);	
	$html="<table style='width:99%'>";
	while (list ($num, $line) = each ($datas)){
		$c=$c+1;
		if(preg_match("#^\[(.+?)\]:(.+?):(.+)#",$line,$re))
		 if(preg_match("#(.+)\s+\+(.+)$#",$re[1],$ri)){$re[1]=$ri[1];}
		 if(strlen($re[1])>20){$re[1]=substr($re[1],0,17).'...';}
		 if(strlen($re[2])>15){$re[2]=substr($re[2],0,12).'...';}
		$html=$html ."<tr " . CellRollOver().">
			
			<td width=1% nowrap valign='top' style='border-bottom:1px solid #CCCCCC'>{$re[1]}</td>
			<td width=1% nowrap valign='top' style='border-bottom:1px solid #CCCCCC'><strong>{$re[2]}</strong></td>
			<td width=99% valign='top' style='border-bottom:1px solid #CCCCCC'><code>{$re[3]}</code></td>
			</tr>";
		if($c>50){break;}
		
	}
	$html=$html."</table>";
	return $html;
	
}

?>