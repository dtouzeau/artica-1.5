<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.os.system.inc');
	
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();
	}

	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["greensql-status"])){greensql_status();exit;}
	if(isset($_GET["params"])){params();exit;}
	if(isset($_POST["EnableGreenSQL"])){save();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["events-show"])){events_show();exit;}
	
	
	
popup();


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div id='greensql-tabs' style='width:100%;height:675px;overflow:auto'></div>
	
	<script>
		LoadAjax('greensql-tabs','$page?tabs=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$array["status"]='{status}';
	$array["params"]='{parameters}';
	$array["events"]='{events}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_greensql style='width:98%;height:668px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_greensql\").tabs();});
		</script>";	
	
}

function status(){
	$page=CurrentPageName();
	$tpl=new templates();

	$html="<div class=explain>{APP_GREENSQL_ABOUT}</div>
	<div id='greesql-status'></div></td>
	<script>
		LoadAjax('greesql-status','$page?greensql-status=yes');
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function greensql_status(){
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_greensql')")."</div>";
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$datas=$sock->getFrameWork("services.php?greensql-status=yes");
	writelogs(strlen($datas)." bytes for mysql status",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
	$ini->loadString(base64_decode($datas));
	$status=DAEMON_STATUS_ROUND("APP_GREENSQL",$ini,null,0).$refresh;
	
	$q=new mysql;
	$sql="SELECT * FROM proxy WHERE proxyid=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"greensql"));	
	
	$status1=Paragraphe("ok64.png", "{greensql_is_available}", "{greensql_is_available_text2}<br>{$ligne["frontend_ip"]}:{$ligne["frontend_port"]}");
	if(!$fp=@fsockopen($ligne["frontend_ip"], $ligne["frontend_port"], $errno, $errstr, 2)){
		$status1=Paragraphe("error-64.png", "{greensql_is_notavailable}<br>{$ligne["frontend_ip"]}:{$ligne["frontend_port"]}<br>{greensql_is_notavailable_text}", "$errstr");
		
	}
	fclose($fp);
	
	echo $tpl->_ENGINE_parse_body($status."<hr><center>$status1</center>");	
	
}

function params(){
	$tpl=new templates();
	$sock=new sockets();
	$EnableGreenSQL=$sock->GET_INFO("EnableGreenSQL");
	if(!is_numeric($EnableGreenSQL)){$EnableGreenSQL=1;}
	$page=CurrentPageName();
	
	$config=unserialize(base64_decode($sock->GET_INFO("GreenSQLConfig")));
	if(!is_numeric($config["block_level"])){$config["block_level"]=30;}
	if(!is_numeric($config["warn_level"])){$config["warn_level"]=20;}
	if(!is_numeric($config["risk_sql_comments"])){$config["risk_sql_comments"]=30;}
	if(!is_numeric($config["risk_senstivite_tables"])){$config["risk_senstivite_tables"]=10;}
	if(!is_numeric($config["risk_or_token"])){$config["risk_or_token"]=5;}
	if(!is_numeric($config["risk_union_token"])){$config["risk_union_token"]=10;}
	if(!is_numeric($config["risk_var_cmp_var"])){$config["risk_var_cmp_var"]=30;}
	if(!is_numeric($config["risk_always_true"])){$config["risk_always_true"]=30;}
	if(!is_numeric($config["risk_empty_password"])){$config["risk_empty_password"]=30;}
	if(!is_numeric($config["risk_multiple_queries"])){$config["risk_multiple_queries"]=15;}
	
	$q=new mysql;
	$sql="SELECT * FROM proxy WHERE proxyid=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"greensql"));
	
	$html="
	<div id='riskConfig'>
	<table style='width:99%' class=form>
	
	<tr>
		<td class=legend>{enable_service}:</td>
		<td>". Field_checkbox("EnableGreenSQL", 1,$EnableGreenSQL,"EnableGreenSQLCheck()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{ipaddr}:</td>
		<td>". field_ipv4("frontend_ip", $ligne["frontend_ip"])."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{listen_port}:</td>
		<td>". Field_text("frontend_port", $ligne["frontend_port"],"width:90px;font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend>{block_level}:</td>
		<td>". Field_text("block_level", $config["block_level"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_block_level}")."</td>
	</tr>	
	<tr>
		<td class=legend>{warn_level}:</td>
		<td>". Field_text("warn_level", $config["warn_level"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_warn_level}")."</td>
	</tr>		
	<tr>
		<td class=legend>{risk_sql_comments}:</td>
		<td>". Field_text("risk_sql_comments", $config["risk_sql_comments"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_sql_comments}")."</td>
	</tr>		
	<tr>
		<td class=legend>{risk_senstivite_tables}:</td>
		<td>". Field_text("risk_senstivite_tables", $config["risk_senstivite_tables"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_senstivite_tables}")."</td>
	</tr>		
	<tr>
		<td class=legend>{risk_or_token}:</td>
		<td>". Field_text("risk_or_token", $config["risk_or_token"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_or_token}")."</td>
	</tr>	
	<tr>
		<td class=legend>{risk_union_token}:</td>
		<td>". Field_text("risk_union_token", $config["risk_union_token"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_union_token}")."</td>
	</tr>	
	<tr>
		<td class=legend>{risk_var_cmp_var}:</td>
		<td>". Field_text("risk_var_cmp_var", $config["risk_var_cmp_var"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_var_cmp_var}")."</td>
	</tr>	
	<tr>
		<td class=legend>{risk_always_true}:</td>
		<td>". Field_text("risk_always_true", $config["risk_always_true"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_always_true}")."</td>
	</tr>	
	<tr>
		<td class=legend>{risk_empty_password}:</td>
		<td>". Field_text("risk_empty_password", $config["risk_empty_password"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_empty_password}")."</td>
	</tr>		
	<tr>
		<td class=legend>{risk_multiple_queries}:</td>
		<td>". Field_text("risk_multiple_queries", $config["risk_multiple_queries"],"width:30px;font-size:13px;padding:3px")."</td>
		<td>". help_icon("{GREENSQL_risk_multiple_queries}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'>". button("{apply}","SaveGreenSQL()")."</td>
	</tr>
	</table>
	</DIV>
	
	<script>
		var x_SaveGreenSQL=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			RefreshTab('main_config_greensql')
			
		}
		
		function  EnableGreenSQLCheck(){
			document.getElementById('block_level').disabled=true;
			document.getElementById('warn_level').disabled=true;
			document.getElementById('risk_sql_comments').disabled=true;
			document.getElementById('risk_senstivite_tables').disabled=true;
			document.getElementById('risk_or_token').disabled=true;
			document.getElementById('risk_union_token').disabled=true;
			document.getElementById('risk_var_cmp_var').disabled=true;
			document.getElementById('risk_always_true').disabled=true;
			document.getElementById('risk_empty_password').disabled=true;
			document.getElementById('risk_multiple_queries').disabled=true;
			document.getElementById('frontend_port').disabled=true;
			
			
			
			if(document.getElementById('EnableGreenSQL').checked){
				document.getElementById('block_level').disabled=false;
				document.getElementById('warn_level').disabled=false;
				document.getElementById('risk_sql_comments').disabled=false;
				document.getElementById('risk_senstivite_tables').disabled=false;
				document.getElementById('risk_or_token').disabled=false;
				document.getElementById('risk_union_token').disabled=false;
				document.getElementById('risk_var_cmp_var').disabled=false;
				document.getElementById('risk_always_true').disabled=false;
				document.getElementById('risk_empty_password').disabled=false;
				document.getElementById('risk_multiple_queries').disabled=false;
				document.getElementById('frontend_port').disabled=false;			
			
			}
		
		}
	
	
		function SaveGreenSQL(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableGreenSQL').checked){XHR.appendData('EnableGreenSQL',1);}else{XHR.appendData('EnableGreenSQL',0);}
			XHR.appendData('block_level',document.getElementById('block_level').value);
			XHR.appendData('warn_level',document.getElementById('warn_level').value);
			XHR.appendData('risk_sql_comments',document.getElementById('risk_sql_comments').value);
			XHR.appendData('risk_senstivite_tables',document.getElementById('risk_senstivite_tables').value);
			XHR.appendData('risk_or_token',document.getElementById('risk_or_token').value);
			XHR.appendData('risk_union_token',document.getElementById('risk_union_token').value);
			XHR.appendData('risk_var_cmp_var',document.getElementById('risk_var_cmp_var').value);
			XHR.appendData('risk_always_true',document.getElementById('risk_always_true').value);
			XHR.appendData('risk_empty_password',document.getElementById('risk_empty_password').value);
			XHR.appendData('risk_multiple_queries',document.getElementById('risk_multiple_queries').value);
			XHR.appendData('frontend_port',document.getElementById('frontend_port').value);
			XHR.appendData('frontend_ip',document.getElementById('frontend_ip').value);
			AnimateDiv('riskConfig');
			XHR.sendAndLoad('$page', 'POST',x_SaveGreenSQL);
		}
		
	EnableGreenSQLCheck();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableGreenSQL", $_POST["EnableGreenSQL"]);
	$config=base64_encode(serialize($_POST));
	$sock->SaveConfigFile($config, "GreenSQLConfig");
	$sql="UPDATE proxy SET frontend_ip='{$_POST["frontend_ip"]}',frontend_port='{$_POST["frontend_port"]}' WHERE proxyid=1";
	$q=new mysql();
	$q->QUERY_SQL($sql,"greensql");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock->getFrameWork("cmd.php?greensql-reload=yes");
}

function events(){
		$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div id='greensql-events' style='width:100%;height:550px;overflow:auto'></div>
	
	<script>
		LoadAjax('greensql-events','$page?events-show=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function events_show(){
	$sock=new sockets();
	$page=CurrentPageName();
	$datas=unserialize(base64_decode($sock->getFrameWork("services.php?greensql-logs=yes")));
	$tpl=new templates();
	$tbl=array_reverse($datas);
	
$html=$tpl->_ENGINE_parse_body("<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th width=1%>". imgtootltip("refresh-24.png","{refresh}","LoadAjax('greensql-events','$page?events-show=yes');;")."</th>
	<th>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>");		
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$ligne=htmlentities($ligne);
		
		$html=$html . "<tr class=$classtr>
		<td colspan=2><code style='font-size:11px'>$ligne</td>
		</tr>";
	}
	
	echo "$html</table>";
	
}



