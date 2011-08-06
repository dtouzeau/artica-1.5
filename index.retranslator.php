<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.retranslator.inc');
	include_once('ressources/class.ini.inc');

	
	$user=new usersMenus();
	if($user->blkid_installed==false){header('location:users.index.php');exit();}
	if($user->AsSystemAdministrator==false){header('location:users.index.php');exit();}
	
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["script"])){main_switch_scripts();exit;}
	if(isset($_GET["save"])){save();exit;}
	if(isset($_GET["status"])){Status();exit;}
	if(isset($_GET["DelCompon"])){DelCompon();exit;}
	if(isset($_POST["AddCompon"])){AddCompon();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["UpdateRetransloatorNow"])){update_now();exit;}
	
	js();
	
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_KRETRANSLATOR}","artica.update.php");
	$prefix="retranslator";
	$html="
	var {$prefix}tant=0;
	
	function retranslatordemarre(){
		{$prefix}tant = {$prefix}tant+1;
		if(!YahooWinOpen()){return false;}
		
		if ({$prefix}tant < 25 ) {                           
		{$prefix}timerID =setTimeout(\"retranslatordemarre()\",2000);
	      } else {
			{$prefix}tant = 0;
			{$prefix}ChargeLogs();
			retranslatordemarre(); 
			                              
	   }
	}	

	function {$prefix}ChargeLogs(){
		var selected = $('#retranslator_main').tabs('option', 'selected');
		if(selected==0){RefreshTab('retranslator_main');}
	}
		
	
		function APP_KRETRANSLATOR_LOAD(){
			YahooWin(750,'$page?popup=yes','$title');
			retranslatordemarre();
		}
	

	
var X_AddCompon= function (obj) {
	var tempvalue=obj.responseText;
	alert(tempvalue);
	LoadAjax('retranslator_main','$page?main=components');
}
	
function AddCompon(index){
	var XHR = new XHRConnection();
	XHR.appendData('AddCompon',index);
	XHR.sendAndLoad('$page', 'POST',X_AddCompon);

}

function DeleteComp(comp){
var XHR = new XHRConnection();
	XHR.appendData('DelCompon',comp);
	XHR.sendAndLoad('$page', 'GET',X_AddCompon);
}	
	
	
	APP_KRETRANSLATOR_LOAD()";
	
	echo $html;
	
}


function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["index"]="{index}";
	$array["http_engine"]='{http_engine}';
	$array["components"]='{components}';
	$array["Update_sites_info"]='{Update_sites_info}';
	$array["events"]='{events}';
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=retranslator_main style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#retranslator_main').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
}
	

	
function main_index(){
$wizard=Paragraphe("64-wizard.png","{HELP_ME_RETRANSLATOR}","{HELP_ME_RETRANSLATOR_TEXT}","javascript:Loadjs('wizard.retranslator.php')","{HELP_ME_RETRANSLATOR_TEXT}",255);

$execute=Paragraphe("64-recycle.png","{update_now}","{perform_update_text}","javascript:UpdateRetransloatorNow()","{perform_update_text}",300);


$retrans=new retranslator();
if($retrans->RetranslatorEnabled<>1){$execute=null;}

	$retranslator=new retranslator();
	$page=CurrentPageName();
	
	$myserver=$_SERVER['SERVER_NAME'];
	if(preg_match("#(.+?)\:#",$myserver,$re)){$myserver=$re[1];}
	
	if($retranslator->RetranslatorHttpdEnabled==1){
		$explain_http="<p style='font-size:13px;font-weight:bold;color:#005447'>{RETRANSLATOR_CONNECT_URI}</p>
		<a href='http://$myserver:$retranslator->RetranslatorHttpdPort' style='font-size:13px'>http://$myserver:$retranslator->RetranslatorHttpdPort<a>
		";
	}

	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' with=50%><div id='services_status'></div>$execute</td>
		<td valign='top' with=50%><div id='services_explain'>
		<div style='font-size:13px'>{APP_KRETRANSLATOR_TEXT}<hr>{APP_KRETRANSLATOR_EXPLAIN}</div>$explain_http<hr>$wizard
		
		</div></td> 
	</tr>
	</table>
	<script>
		LoadAjax('services_status','$page?status=yes');
		
		
		var x_UpdateRetransloatorNow= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('retranslator_main');
			
		 }		
		
		function UpdateRetransloatorNow(){
			var XHR = new XHRConnection();
			XHR.appendData('UpdateRetransloatorNow',1)	
			XHR.sendAndLoad('$page', 'GET',x_UpdateRetransloatorNow);
			
		}
		
	</script>
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function update_now(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?retranslator-execute=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{apply_upgrade_help}");
	
}






function main_switch(){
	
	switch ($_GET["main"]) {
		case "index":main_index();exit;break;
		case "yes":main_config();exit;break;
		case "http_engine":main_config();exit;break;
		case "components":main_components();exit;break;
		case "Update_sites_info":main_Update_sites_info();exit;break;
		case "events":main_events();exit;break;
		default:
			break;
	}
	
	
}


function main_switch_scripts(){
	switch ($_GET["script"]) {
		case "load_functions":echo main_script_load();exit;break;
	
		default:
			break;
	}
	
}

function main_Update_sites_info(){
	$retranslator=new retranslator();
	$array=$retranslator->RetranslatorSitesList();
	$table="<table style='width:100%'>";
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		$table=$table."<tr>
		<td style='font-size:12px' nowrap valign='top' width=1%><img src='img/fw_bold.gif'></td>	
		<td style='font-size:14px' nowrap><strong>$ligne</strong></td>
		</tr>
		
		";
		
		
	}
	$table=$table."</table>";
	$html="$tab<br><H3>{Update_sites_info_title}</H3>$table";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function main_events(){
	$sock=new sockets();
	
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?RetranslatorEvents=yes")));
	$datas=array_reverse ($datas, TRUE);
			while (list ($num, $val) = each ($datas) ){
			if(trim($val)==null){continue;}
			$val=htmlentities($val);
			
			$html=$html . "<div style='black;margin-bottom:1px;padding:2px;border-bottom:1px dotted #CCCCCC;border-left:5px solid #CCCCCC;width:105%;margin-left:-30px'>
			<table style='width:100%'>
			<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td><td><code style='font-size:10px'>$val</code></td>
			</tr>
			</table>
			</div>";
			}
		
			
		$html="<H5>{events}</H5><div id='RetranslatorEvents'>$html</div>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);	
	}

function main_components(){
	$retranslator=new retranslator();
	$array=$retranslator->master_array;
	reset($retranslator->MyRetranslateComponentsList);
	$components=$retranslator->MyRetranslateComponentsList;
	
	$html="<H5>{components}</h5>";
	
	$table="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($array) ){
		$button=button("{add}","AddCompon('$num');");
		if($retranslator->TestsRetranslations($ligne["COMPONENTS"])){
			$img="fleche-20.png";$button=null;}else{
			$img="fleche-20-black.png";}
		$table=$table."<tr " . CellRollOver().">
		<td style='font-size:12px' nowrap valign='top' width=1%><img src='img/$img'></td>	
		<td style='font-size:12px' nowrap>$num</td>
		<td style='font-size:12px' nowrap>$button</td>
			
			
		</tr>
		
		";
		
		
	}
	$table=$table."</table>";

	
	$comp="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($components) ){
		$comp=$comp."<tr " . CellRollOver().">
		<td style='font-size:12px' nowrap valign='top' width=1%><img src='img/fw_bold.gif'></td>	
		<td style='font-size:12px' nowrap>$num</td>
		<td style='font-size:12px' nowrap>" . imgtootltip('ed_delete.gif','{delete}',"DeleteComp('$num')")."</td>
		</tr>";
		
	}
	
	$comp=$comp."</table>";
	$html=$html."
	<table style='width:100%'>
	<tr>
		<td valign='top' width=50% style='padding:3px;border:1px solid #CCCCCC'>
		<H3>{available_products_to_be_updated}</H3>
		$table</td>
		<td valign='top' width=50% style='padding:3px;border:1px solid #CCCCCC'>
		<H3>{available_components}</H3>
		$comp</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_config(){
	$retranslator=new retranslator();
	$page=CurrentPageName();
	
	$myserver=$_SERVER['SERVER_NAME'];
	if(preg_match("#(.+?):(.+)#",$myserver)){$myserver=$re[1];}
	
	if($retranslator->RetranslatorHttpdEnabled==1){
		$explain="<p style='font-size:13px;font-weight:bold;color:#005447'>{RETRANSLATOR_CONNECT_URI}</p>
		<a href='http://$myserver:$retranslator->RetranslatorHttpdPort' style='font-size:13px'>http://$myserver:$retranslator->RetranslatorHttpdPort<a>
		";
	}
	
	
	$html="$tab<br><H5>{http_engine}</h5>
	$explain
	<FORM NAME='FFM1'>
	<table style='width:100%'>
		<tr>
		<td colspan=2 style='border-top:1px solid #CCCCCC;padding-top:3px' >
		<input type='hidden' id='save' value='yes' name='save'>
		<tr>
		<td class=legend>{RetranslatorHttpdEnabled}:</td>
		<td>" . Field_numeric_checkbox_img('RetranslatorHttpdEnabled',$retranslator->RetranslatorHttpdEnabled,'{enabled_disabled}')."</td>
		</tr>
		<tr>
		<td class=legend>{RetranslatorHttpdPort}:</td>
		<td>" . Field_text('RetranslatorHttpdPort',$retranslator->RetranslatorHttpdPort,'width:80px')."</td>
		</tr>
		<tr>
			<td colspan=2 align='right'>
			<hr>
			". button("{edit}","ParseForm('FFM1','$page',true);")."
			
		</td>
		</tr>
	</table>
	</FORM>
	";
	
	$html=$html . "<H5>{retranslator_engine}</h5>";
	
	$regions=array("am","ar","at","az","be","bg","br","by","ca","cl","cn","cs","cz","de","ee","es","fr","gb","ge","gr","hk","hu","it","jp","kg","kr","kz","lt","lv","md","mx","nl","pl","ro","ru","th","tj","tm","tr","tw","ua","uk","us","uz");
	while (list ($num, $ligne) = each ($regions) ){
	$hash_regions[$ligne]=$ligne;
	}	
	
	$RetranslatorRegionSettings=Field_array_Hash($hash_regions,'RetranslatorRegionSettings',$retranslator->RetranslatorRegionSettings);
	$loglevel=array(0=>0,1=>1,2=>2,3=>3,4=>4,9=>9);
	$RetranslatorReportLevel=Field_array_Hash($loglevel,'RetranslatorReportLevel',$retranslator->RetranslatorReportLevel);
	
	
	$html=$html ."
	<FORM NAME='FFM2'>
	<table style='width:100%'>
		<tr>
		<td colspan=3 style='border-top:1px solid #CCCCCC;padding-top:3px' >
		<input type='hidden' id='save' value='yes' name='save'>
		<tr>
		<tr>
		<td class=legend>{RetranslatorEnabled}:</td>
		<td>" . Field_numeric_checkbox_img('RetranslatorEnabled',$retranslator->RetranslatorEnabled,'{enabled_disabled}')."</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
			<td class=legend>{RetranslatorCronMinutes}:</td>
			<td>" . Field_text('RetranslatorCronMinutes',$retranslator->RetranslatorCronMinutes,'width:40px')."&nbsp;mn</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class=legend>{RetranslatorReportLevel}:</td>
			<td>$RetranslatorReportLevel</td>
			<td>" . help_icon('{RetranslatorReportLevel_text}')."</td>
		</tr>		
		
		<tr><td colspan=3><H3 style='margin-top:8px;margin-bottom:5px;border-bottom:1px solid #CCCCCC'>{sources_settings}</h3></td></tr>
		
		<tr>
			<td class=legend>{RetranslatorRegionSettings}:</td>
			<td>$RetranslatorRegionSettings</td>
			<td>&nbsp;</td>
		</tr>
		
		
		<tr>
			<td class=legend>{RetranslatorUseUpdateServerUrl}:</td>
			<td>" . Field_yesno_checkbox('RetranslatorUseUpdateServerUrl',$retranslator->RetranslatorUseUpdateServerUrl)."</td>
			<td>" . help_icon('{RetranslatorUseUpdateServerUrl_text}')."</td>
		</tr>	
		
		<tr>
			<td class=legend>{RetranslatorUpdateServerUrl}:</td>
			<td>" . Field_text('RetranslatorUpdateServerUrl',$retranslator->RetranslatorUpdateServerUrl,'width:250px')."</td>
			<td>" . help_icon('{RetranslatorUpdateServerUrl_text}')."</td>
		</tr>

		<tr>
			<td class=legend>{RetranslatorUseUpdateServerUrlOnly}:</td>
			<td>" . Field_yesno_checkbox('RetranslatorUseUpdateServerUrlOnly',$retranslator->RetranslatorUseUpdateServerUrlOnly)."</td>
			<td>" . help_icon('{RetranslatorUseUpdateServerUrlOnly_text}')."</td>
		</tr>			
		
		
		<tr><td colspan=3><H3 style='margin-top:8px;margin-bottom:5px;border-bottom:1px solid #CCCCCC'>{proxy_settings}</h3></td></tr>
		<tr>
			<td class=legend>{RetranslatorUseProxy}:</td>
			<td>" . Field_yesno_checkbox('RetranslatorUseProxy',$retranslator->RetranslatorUseProxy)."</td>
			<td>&nbsp;</td>
		</tr>		
		
		<tr>
			<td class=legend>{RetranslatorProxyAddress}:</td>
			<td>" . Field_text('RetranslatorProxyAddress',$retranslator->RetranslatorProxyAddress,'width:250px')."</td>
			<td>&nbsp;</td>
		</tr>
		
		
		
			
		<tr>
			<td colspan=3 align='right'>
					<hr>". button("{edit}","ParseForm('FFM2','$page',true);")."
		</td>
		</tr>
	</table>
	</FORM>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"index.retranslator.php");
	
	
}

function save(){
	
	$retranslator=new retranslator();
	while (list ($num, $ligne) = each ($_GET) ){
	$retranslator->$num=$ligne;
	}
	
	$retranslator->SaveToServer();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
}


function status(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?retranslator-status=yes"));

	
	
	
	$dbsize=base64_decode($sock->getFrameWork("cmd.php?retranslator-dbsize=yes"));
	$tmpdbsize=base64_decode($sock->getFrameWork("cmd.php?retranslator-tmp-dbsize=yes"));
	
	
	if($datas<>null){
		$ini->loadString($datas);
		$status1=DAEMON_STATUS_ROUND("KRETRANSLATOR",$ini,null);
		$status2=DAEMON_STATUS_ROUND("KRETRANSLATOR_HTTPD",$ini,null);
	}else{
		$status1="<H5>{error}!!!</H5>";
	}
	
	$status3="<div style='font-size:13px;font-weight:bold;text-align:right;padding-bottom:4px;padding-right:5px;color:#A30000'>{files_size_disk}:&nbsp;$dbsize</div>";
	$status4="<div style='font-size:13px;font-weight:bold;text-align:right;padding-bottom:4px;padding-right:5px;color:#A30000'>{files_size_tmp}:&nbsp;$tmpdbsize</div>";
	echo $tpl->_ENGINE_parse_body("$status1<br>$status2<br>$status3$status4");	
	}
	
function AddCompon(){
	$tpl=new templates();
	$retranslator=new retranslator();
	$array=$retranslator->master_array[$_POST["AddCompon"]]["COMPONENTS"];
	if(!is_array($array)){
		echo $tpl->_ENGINE_parse_body("{failed}: index:{$_POST["AddCompon"]}");
		exit;
	}
	while (list ($num, $ligne) = each ($array) ){
		$retranslator->MyRetranslateComponentsList[$ligne]=true;
		$retranslator->SaveToServer();
		
	}
}
function DelCompon(){
	$retranslator=new retranslator();
	unset($retranslator->MyRetranslateComponentsList[$_GET["DelCompon"]]);
	$retranslator->SaveToServer();
	
}
	
?>	