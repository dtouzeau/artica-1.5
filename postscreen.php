<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
$users=new usersMenus();
if(!$users->AsPostfixAdministrator){
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	echo "alert('$ERROR_NO_PRIVS');";
	die();
	
}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["settings"])){parameters();exit;}
	if(isset($_GET["dnsbl"])){dnsbl();exit;}
	if(isset($_GET["dnsbl-list"])){dnsbl_list();exit;}
	if(isset($_GET["dnsbl-add"])){dnsbl_add();exit;}
	if(isset($_GET["dnsbl-delete"])){dnsbl_delete();exit;}
	if(isset($_GET["postscreen_dnsbl_action"])){saveConfig();exit;}
	if(isset($_GET["postscreen_bare_newline_enable"])){saveConfig();exit;}
	
	
	
	if(isset($_GET["EnablePostScreen"])){EnablePostScreen_edit();exit;}

js();



function js(){
	$page=CurrentPageName();
	$title="PostScreen::{$_GET["hostname"]}/{$_GET["ou"]}";
	echo "YahooWin3(660,'$page?popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$title');";
	}
	

function popup(){
	
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();
	$array["index"]='{index}';
	$array["settings"]="{parameters}";
	$array["dnsbl"]="{dnsbl}";
	$array["white"]="{hosts}:{white list}";


// Total downloaded: 100%, Result: Retranslation successful and update is not requested
	
	
	while (list ($num, $ligne) = each ($array) ){
		if($num=="white"){
			$tab[]="<li><a href=\"whitelists.admin.php?popup-hosts=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		$tab[]="<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
			
	}

	$html="
		<div id='main_postscreen_config' style='background-color:white'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_postscreen_config').tabs();
				});
		</script>
	
	";
		
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function dnsbl(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$action=array("ignore"=>"ignore","enforce"=>"enforce","drop"=>"drop");
	
	$data=file_get_contents("ressources/dnsrbl.db");
	$tr=explode("\n",$data);
	while (list ($num, $val) = each ($tr) ){
		if(preg_match("#RBL:(.+)#",$val,$re)){$RBL[$re[1]]=$re[1];}
		if(preg_match("#RHSBL:(.+)#",$val,$re)){$RHSBL[$re[1]]=$re[1];}		
	}
	$list=Field_array_Hash($RBL,"DNBL_TO_ADD",null,"style:font-size:13px;padding:3px");	
	$postscreen_dnsbl_action=$main->GET("postscreen_dnsbl_action");
	$postscreen_dnsbl_ttl=$main->GET("postscreen_dnsbl_ttl");
	$postscreen_dnsbl_threshold=$main->GET("postscreen_dnsbl_threshold");
	
	if($postscreen_dnsbl_action==null){$postscreen_dnsbl_action="ignore";}
	if($postscreen_dnsbl_ttl==null){$postscreen_dnsbl_ttl="1h";}
	if($postscreen_dnsbl_threshold==null){$postscreen_dnsbl_threshold="1";}
	
	
	$html="
	<div class=explain>{postscreen_dnsbl_sites}</div>
	
	<table style='width:100%' class=form id='tosave'>
	
	<tr>
		<td class=legend>{postscreen_dnsbl_action}:</td>
		<td>". Field_array_Hash($action,"postscreen_dnsbl_action",$postscreen_dnsbl_action,"style:font-size:13px;padding:3px")."</td>
		<td width=1%>". help_icon("{postscreen_dnsbl_action_text}")."</td>
	</tr>	
	<tr>
		<td class=legend>{postscreen_dnsbl_threshold}:</td>
		<td>". Field_text("postscreen_dnsbl_threshold",$postscreen_dnsbl_threshold,"font-size:13px;padding:3px;width:60px")."</td>
		<td width=1%>". help_icon("{postscreen_dnsbl_threshold_text}")."</td>
	</tr>
	<tr>
		<td class=legend>{postscreen_dnsbl_ttl}:</td>
		<td>". Field_text("postscreen_dnsbl_ttl",$postscreen_dnsbl_ttl,"font-size:13px;padding:3px;width:60px")."</td>
		<td width=1%>". help_icon("{postscreen_dnsbl_ttl_text}")."</td>
	</tr>			
	<tr><td colspan=3 align='right'><hr>". button("{apply}","SaveDNSBLConfig()")."</td></tr>
	</table>	
	
	<table class=form>
	<tr>
		<td class=legend>{DNSBL}:</td>
		<td>$list</td>
		<td class=legend>{threshold}:</td>
		<td>". Field_text("dnsbl_threshold",$postscreen_dnsbl_threshold,"font-size:13px;padding:3px;width:60px")."</td>
		<td width=1%>". button("{add}","AddPostScreenDNSBL()")."</td>
	</tr>
	
	</table>
		
	
	<div id='postscreen_dnbl_list' style='width:100%;height:210px;overflow:auto'></div>
	
	<script>
	
		var x_AddPostScreenDNSBL= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			RefreshPostScreenDNSBL();
			
		}	
		
		function SaveDNSBLConfig(){
			var XHR = XHRParseElements('tosave');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			document.getElementById('postscreen_dnbl_list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddPostScreenDNSBL);
		}
	
		function AddPostScreenDNSBL(){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('dnsbl-add',document.getElementById('DNBL_TO_ADD').value);
			XHR.appendData('dnsbl_threshold',document.getElementById('dnsbl_threshold').value);
			document.getElementById('postscreen_dnbl_list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddPostScreenDNSBL);
		}
		
		function DeletePostScreenDNSBL(site){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('dnsbl-delete',site);
			XHR.appendData('dnsbl_threshold',document.getElementById('dnsbl_threshold').value);
			document.getElementById('postscreen_dnbl_list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddPostScreenDNSBL);		
		}

	RefreshPostScreenDNSBL();
	
	function RefreshPostScreenDNSBL(){
		LoadAjax('postscreen_dnbl_list','$page?dnsbl-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
	
	}
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function dnsbl_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("postscreen_dnsbl_sites")));	
	
		$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{dnsbl}</th>
	<th>{threshold}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
		
	if(is_array($array)){
		while (list ($site, $threshold) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."<tr class=$classtr>
						<td width=1%><img src='img/domain-32.png'></td>
						<td><strong style='font-size:16px'>$site</strong></td>
						<td width=1%  align='center'><strong style='font-size:16px'>$threshold</strong></td>
						<td width=1% align='center'>".imgtootltip("delete-24.png",'{delete}',"DeletePostScreenDNSBL('$site')")."</td>
					</tr>";					
		
		
			
		}
	}	

	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function dnsbl_add(){

	writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: adding {$_GET["dnsbl-add"]} threshold:{$_GET["dnsbl_threshold"]}");
	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	
	$array=array();
	$array=unserialize(base64_decode($main->GET_BIGDATA("postscreen_dnsbl_sites")));
	if(!is_array($array)){$array=array();}
	
	$array[$_GET["dnsbl-add"]]=$_GET["dnsbl_threshold"];
	
	if(!$main->SET_BIGDATA("postscreen_dnsbl_sites",base64_encode(serialize($array)))){
		writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: error...");
		echo "ERROR";
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postscreen=yes&hostname={$_GET["hostname"]}");		
}

function dnsbl_delete(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$array=array();
	$array=unserialize(base64_decode($main->GET_BIGDATA("postscreen_dnsbl_sites")));
	if(!is_array($array)){$array=array();}	
	unset($array[$_GET["dnsbl-delete"]]);
	if(!$main->SET_BIGDATA("postscreen_dnsbl_sites",base64_encode(serialize($array)))){
		writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: error...");
		echo "ERROR";return;
	}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postscreen=yes&hostname={$_GET["hostname"]}");		
}

function saveConfig(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$hostname=$_GET["hostname"];
	unset($_GET["hostname"]);
	unset($_GET["ou"]);
	while (list ($key, $value) = each ($_GET) ){
		if(!$main->SET_VALUE($key,$value)){return;}
		
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postscreen=yes&hostname=$hostname");	
}



function parameters(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	
	$postscreen_bare_newline_action=$main->GET("postscreen_bare_newline_action");
	$postscreen_bare_newline_enable=$main->GET("postscreen_bare_newline_enable");
	
	$postscreen_bare_newline_ttl=$main->GET("postscreen_bare_newline_ttl");
	$postscreen_cache_cleanup_interval=$main->GET("postscreen_cache_cleanup_interval");
	$postscreen_cache_retention_time=$main->GET("postscreen_cache_retention_time");
	$postscreen_client_connection_count_limit=$main->GET("postscreen_client_connection_count_limit");
	$postscreen_pipelining_enable=$main->GET("postscreen_pipelining_enable");
	$postscreen_pipelining_action=$main->GET("postscreen_pipelining_action");
	$postscreen_pipelining_ttl=$main->GET("postscreen_pipelining_ttl");
	$postscreen_post_queue_limit=$main->GET("postscreen_post_queue_limit");
	$postscreen_pre_queue_limit=$main->GET("postscreen_pre_queue_limit");
	$postscreen_non_smtp_command_enable=$main->GET("postscreen_non_smtp_command_enable");
	$postscreen_non_smtp_command_action=$main->GET("postscreen_non_smtp_command_action");
	$postscreen_non_smtp_command_ttl=$main->GET("postscreen_non_smtp_command_ttl");
	$postscreen_forbidden_commands=$main->GET("postscreen_forbidden_command");
	$postscreen_dnsbl_action=$main->GET("postscreen_dnsbl_action");
	
	
	
	if($postscreen_bare_newline_action==null){$postscreen_bare_newline_action="ignore";}
	if(!is_numeric($postscreen_bare_newline_enable)){$postscreen_bare_newline_enable="0";}
	if($postscreen_bare_newline_ttl==null){$postscreen_bare_newline_ttl="30d";}
	if($postscreen_cache_cleanup_interval==null){$postscreen_cache_cleanup_interval="12h";}
	if($postscreen_cache_retention_time==null){$postscreen_cache_retention_time="7d";}
	if($postscreen_client_connection_count_limit==null){$postscreen_client_connection_count_limit="50";}
	if($postscreen_pipelining_enable==null){$postscreen_pipelining_enable="0";}
	if($postscreen_pipelining_action==null){$postscreen_pipelining_action="ignore";}
	if($postscreen_pipelining_ttl==null){$postscreen_pipelining_ttl="30d";}			
	if($postscreen_post_queue_limit==null){$postscreen_post_queue_limit="100";}
	if($postscreen_pre_queue_limit==null){$postscreen_pre_queue_limit="100";}
	
	if($postscreen_non_smtp_command_enable==null){$postscreen_non_smtp_command_enable="0";}
	if($postscreen_non_smtp_command_action==null){$postscreen_non_smtp_command_action="drop";}
	if($postscreen_non_smtp_command_ttl==null){$postscreen_non_smtp_command_ttl="30d";}
	if($postscreen_forbidden_commands==null){$postscreen_forbidden_commands="CONNECT, GET, POST";}
	if($postscreen_dnsbl_action==null){$postscreen_dnsbl_action="ignore";}
	
	
	
	
	
	$action=array("ignore"=>"ignore","enforce"=>"enforce","drop"=>"drop");
	$html="
	<div id='NavigationForms4'>
	<h3><a href=\"#\">{Bare_newline_test}</a></h3>
	<div>
	<div class=explain>{POSTSCREEN_BARRE_NEWLINE_EXPLAIN}</div>
	
			<table style='width:100%' class=form>
			
			<tr>
			<td class=legend>{postscreen_bare_newline_enable}:</td>
			<td>". Field_checkbox("postscreen_bare_newline_enable",1,$postscreen_bare_newline_enable)."</td>
			<td width=1%>". help_icon("{postscreen_bare_newline_enable_text}")."</td>
			</tr>	
			
			
			<tr>
			<td class=legend>{postscreen_bare_newline_action}:</td>
			<td>". Field_array_Hash($action,"postscreen_bare_newline_action",$postscreen_bare_newline_action,"style:font-size:13px;padding:3px")."</td>
			<td width=1%>". help_icon("{postscreen_bare_newline_action_text}")."</td>
			</tr>
			
			<tr>
			<td class=legend>{postscreen_bare_newline_ttl}:</td>
			<td>". Field_text("postscreen_bare_newline_ttl",$postscreen_bare_newline_ttl,"font-size:13px;padding:3px;width:60px")."</td>
			<td width=1%>". help_icon("{postscreen_bare_newline_ttl_text}")."</td>
			</tr>	
			<tr><td colspan=3 align='right'><hr>". button("{apply}","SavePostScreenConfig()")."</td></tr>
			</table>
	</div>

	<h3><a href=\"#\">{Command_pipelining_test}</a></h3>
	<div>
	<div class=explain>{postscreen_pipelining_explain}</div>	

	<table style='width:100%' class=form>
	
	<tr>
		<td class=legend>{postscreen_pipelining_enable}:</td>
		<td>". Field_checkbox("postscreen_pipelining_enable",1,$postscreen_pipelining_enable)."</td>
		<td width=1%>". help_icon("{postscreen_pipelining_enable_text}")."</td>
	</tr>	
	
	<tr>
		<td class=legend>{postscreen_pipelining_action}:</td>
		<td>". Field_array_Hash($action,"postscreen_pipelining_action",$postscreen_pipelining_action,"style:font-size:13px;padding:3px")."</td>
		<td width=1%>". help_icon("{postscreen_pipelining_action_text}")."</td>
	</tr>	
	<tr>
		<td class=legend>{postscreen_pipelining_ttl}:</td>
		<td>". Field_text("postscreen_pipelining_ttl",$postscreen_pipelining_ttl,"font-size:13px;padding:3px;width:60px")."</td>
		<td width=1%>". help_icon("{postscreen_pipelining_ttl_text}")."</td>
	</tr>	
	<tr><td colspan=3 align='right'><hr>". button("{apply}","SavePostScreenConfig()")."</td></tr>
	</table>
	
	</div>
	
	<h3><a href=\"#\">{postscreen_non_smtp_command_title}</a></h3>
	<div>
	<div class=explain>{postscreen_non_smtp_command_explain}</div>	
	<table style='width:100%' class=form>
	
	<tr>
		<td class=legend>{postscreen_non_smtp_command_enable}:</td>
		<td>". Field_checkbox("postscreen_non_smtp_command_enable",1,$postscreen_non_smtp_command_enable)."</td>
		<td width=1%>". help_icon("{postscreen_non_smtp_command_enable_text}")."</td>
	</tr>	
	
	<tr>
		<td class=legend>{postscreen_non_smtp_command_action}:</td>
		<td>". Field_array_Hash($action,"postscreen_non_smtp_command_action",$postscreen_non_smtp_command_action,"style:font-size:13px;padding:3px")."</td>
		<td width=1%>". help_icon("{postscreen_non_smtp_command_action_text}")."</td>
	</tr>	
	<tr>
		<td class=legend>{postscreen_non_smtp_command_ttl}:</td>
		<td>". Field_text("postscreen_non_smtp_command_ttl",$postscreen_non_smtp_command_ttl,"font-size:13px;padding:3px;width:60px")."</td>
		<td width=1%>". help_icon("{postscreen_non_smtp_command_ttl_text}")."</td>
	</tr>	
	<tr>
		<td class=legend>{postscreen_forbidden_commands}:</td>
		<td>". Field_text("postscreen_forbidden_commands",$postscreen_forbidden_commands,"font-size:13px;padding:3px;width:220px")."</td>
		<td width=1%>". help_icon("{postscreen_forbidden_commands_text}")."</td>
	</tr>	
	<tr><td colspan=3 align='right'><hr>". button("{apply}","SavePostScreenConfig()")."</td></tr>
	
	</table>
	
	</div>
	
	<h3><a href=\"#\">{limits}</a></h3>
	<div>
		<table style='width:100%' class=form>
		
		<tr>
			<td class=legend>{client_connection_count_limit}:</td>
			<td>". Field_text("postscreen_client_connection_count_limit",$postscreen_client_connection_count_limit,"font-size:13px;padding:3px;width:60px")."</td>
			<td width=1%>". help_icon("{client_connection_count_limit_text}")."</td>
		</tr>	
		<tr>
			<td class=legend>{postscreen_post_queue_limit}:</td>
			<td>". Field_text("postscreen_post_queue_limit",$postscreen_post_queue_limit,"font-size:13px;padding:3px;width:60px")."</td>
			<td width=1%>". help_icon("{postscreen_post_queue_limit_text}")."</td>
		</tr>
			<td class=legend>{postscreen_pre_queue_limit}:</td>
			<td>". Field_text("postscreen_pre_queue_limit",$postscreen_pre_queue_limit,"font-size:13px;padding:3px;width:60px")."</td>
			<td width=1%>". help_icon("{postscreen_pre_queue_limit_text}")."</td>
		</tr>	
		<tr><td colspan=3 align='right'><hr>". button("{apply}","SavePostScreenConfig()")."</td></tr>
		
		</table>
	</div>
</div>

<script>
		$(function() {
			$( \"#NavigationForms4\" ).accordion({autoHeight: false,navigation: true});});
			
		var x_SavePostScreenConfig= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
		}	
		
		function SavePostScreenConfig(){
			var XHR = XHRParseElements('NavigationForms4');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.sendAndLoad(\"$page\", 'GET',x_SavePostScreenConfig);
		}			
	
</script>	
	
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


	
function index(){
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	
	$html="
	<div id='postscreen-intro'>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/postscreen-128.png'></td>
	
		<td valign='top'>". Paragraphe_switch_img("{ENABLE_POSTSCREEN}","{POSTSCREEN_TEXT}","EnablePostScreen",$main->GET("EnablePostScreen"),null,390)."
		<hr>
		<div style='text-align:right'>". button("{apply}","SaveEnablePostScreen()")."</div>
		</td>
	</tr>
	</table>
	</div>
	<script>
	
	var x_SaveEnablePostScreen= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		RefreshTab('main_postscreen_config');
	}	
	
		function SaveEnablePostScreen(){
		var XHR = new XHRConnection();
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('EnablePostScreen',document.getElementById('EnablePostScreen').value);
		document.getElementById('postscreen-intro').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveEnablePostScreen);
		
		}
	
	</script>
			
			
		
";
	
	echo $tpl->_ENGINE_parse_body($html);
}


function EnablePostScreen_edit(){
	writelogs("Saving PostScreen service {$_GET["hostname"]}/{$_GET["ou"]}={$_GET["EnablePostScreen"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$main->SET_VALUE("EnablePostScreen",$_GET["EnablePostScreen"]);	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postscreen=yes&hostname={$_GET["hostname"]}");
	
}
