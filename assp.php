<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.assp.inc');
	
	
	$usersmenus=new usersMenus();
if(!$usersmenus->AsPostfixAdministrator){
	$tpl=new templates();
	echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
	exit;
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["greylist-js"])){greylist_js();exit;}
if(isset($_GET["greylist-popup"])){greylist_popup();exit;}
if(isset($_GET["DelayGripvalue"])){greylist_save();exit;}
if(isset($_GET["script-backup"])){backup_js();exit;}
if(isset($_GET["backup-popup"])){backup_popup();exit;}
if(isset($_GET["EnableASSPBackup"])){backup_save();exit;}
if(isset($_GET["MessageScoringLowerLimit"])){scoring_save();exit;}
if(isset($_GET["script-scoring"])){scoring_js();exit;}
if(isset($_GET["scoring-popup"])){scoring_popup();exit;}


js();

function js(){
	$page=CurrentPageName();
	$prefix=str_replace(".",'_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_ASSP}');
	
	
	
	$html="
	
	function {$prefix}Load(){
		YahooWin2('770','$page?popup=yes','$title');
	
	}
	{$prefix}Load();
	";
	
	
echo $html;
	
	
}


function scoring_js(){
	$page=CurrentPageName();
	$prefix=str_replace(".",'_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{messages_scoring}');
	
	
	
	$html="
	
	function {$prefix}Load(){
		YahooWin3('650','$page?scoring-popup=yes','$title');
	
	}
	
var x_ASSPScaveScores= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}Load();
	}		
	
	function ASSPScaveScores(){
			var XHR = new XHRConnection();
			XHR.appendData('MessageScoringLowerLimit',document.getElementById('MessageScoringLowerLimit').value);
			XHR.appendData('MessageScoringLowerLimitTag',document.getElementById('MessageScoringLowerLimitTag').value);
			XHR.appendData('MessageScoringUpperLimit',document.getElementById('MessageScoringUpperLimit').value);
			document.getElementById('ASSP_SCORES').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_ASSPScaveScores);
	
	}
	
	{$prefix}Load();
	";
	
	
echo $html;	
}

function scoring_save(){
	$sock=new sockets();
	$sock->SET_INFO("ASSPMessageScoringLowerLimit",$_GET["MessageScoringLowerLimit"]);
	$sock->SET_INFO("ASSPMessageScoringLowerLimitTag",$_GET["MessageScoringLowerLimitTag"]);
	$sock->SET_INFO("ASSPMessageScoringUpperLimit",$_GET["MessageScoringUpperLimit"]);
	$sock->getFrameWork("cmd.pĥp?reload-assp=yes");
}

function scoring_popup(){
	
	$sock=new sockets();
	$MessageScoringLowerLimit=$sock->GET_INFO("ASSPMessageScoringLowerLimit");
	$MessageScoringLowerLimitTag=$sock->GET_INFO("ASSPMessageScoringLowerLimitTag");
	$MessageScoringUpperLimit=$sock->GET_INFO("ASSPMessageScoringUpperLimit");
	if($MessageScoringLowerLimit==null){$MessageScoringLowerLimit=50;}
	if($MessageScoringUpperLimit==null){$MessageScoringUpperLimit=60;}
	
	$html="<H1>{messages_scoring}</H1>
	<div id='ASSP_SCORES'>
	". RoundedLightWhite("<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{MessageScoringLowerLimit}:</td>
		<td valign='top'>". Field_text("MessageScoringLowerLimit",$MessageScoringLowerLimit,"width:90px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{MessageScoringLowerLimitTag}:</td>
		<td valign='top'>". Field_text("MessageScoringLowerLimitTag",$MessageScoringLowerLimitTag,"width:190px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{MessageScoringUpperLimit}:</td>
		<td valign='top'>". Field_text("MessageScoringUpperLimit",$MessageScoringUpperLimit,"width:90px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>
			<input type='button' OnClick=\"javascript:ASSPScaveScores();\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>	
	</table>")."</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}


function popup(){
	$assp=new assp();
	$page=CurrentPageName();
	$connextion=$_SERVER["SERVER_NAME"];
	if(preg_match("#(.+?):#",$connextion,$re)){$connextion=$re[1];}
	$connextion=str_replace("www.","",$connextion);
	$website=Paragraphe("rouage-64.png","{ASSP_INTERFACE}","{ASSP_INTERFACE_TEXT}<br>port:$assp->webAdminPort","javascript:s_PopUpFull('http://$connextion:$assp->webAdminPort',900,900)");
	$notinstalled=Paragraphe("setup-90-back.png",
	"{ASSP_NOT_INSTALLED}","{ASSP_NOT_INSTALLED_TEXT}","javascript:Loadjs('setup.index.progress.php?product=APP_ASSP&start-install=yes');",null,390);
	
	
	
	$assp_greylist=Paragraphe("64-milter-greylist.png","{greylisting}","{grelisting_text}","javascript:Loadjs('$page?greylist-js=yes')");
	$backup=Paragraphe('folder-64-backup.png','{backupemail_behavior}','{backupemail_behavior_text}',"javascript:Loadjs('assp.php?script-backup=yes')");
	$scoring=Paragraphe("burglar-score-64.png","{messages_scoring}","{messages_scoring_text}","javascript:Loadjs('assp.php?script-scoring=yes')");
	
	
	$forum="<table style='width:100%'>
			<tr>
				<td valign='top'>$website</td>
				<td valign='top'>$backup</td>
			</tr>
			<tr>
				<td valign='top'>$assp_greylist</td>
				<td valign='top'>$scoring</td>
			</tr>			
			
			";
			
	$status=ASSPStatus();	
			
	$users=new usersMenus();
	if(!$users->ASSP_INSTALLED){
		$forum="<table style='width:100%'>
			<tr>
				<td valign='top' align='center'>$notinstalled</td>
			</tr>";
		
	}
	
	
	
	$html="<table style='width:100%'>
	<tr>
		<TD valign='top'>
			<img src='img/assp-128.png'><hr>$status
		</td>
		<td valing='top'>
			<table style='width:100%'>
			<tr>
				<td valign='top'>$forum</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function ASSPStatus(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadFile("ressources/logs/global.status.ini");
	$status=DAEMON_STATUS_ROUND("ASSP",$ini,null);
	return  $tpl->_ENGINE_parse_body($status);
	
	
}

function backup_save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableASSPBackup",$_GET["EnableASSPBackup"]);
	if($_GET["EnableASSPBackup"]==1){
		$sock->SET_INFO("MailArchiverEnabled",0);
		$sock->SET_INFO("EnableAmavisBackup",0);
	}
	$sock->getFrameWork("cmd.pĥp?reload-assp=yes");
}

function backup_js(){
$page=CurrentPageName();
	$prefix=str_replace(".",'_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{backupemail_behavior}');
	
	
	
	$html="
	
	function {$prefix}BackupLoad(){
		YahooWin3('550','$page?backup-popup=yes','$title');
	}
	
var x_EnableASSPBackup= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}BackupLoad();
	}	
		
	function EnableASSPBackup(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableASSPBackup',document.getElementById('EnableASSPBackup').value);
			document.getElementById('assp_backup').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_EnableASSPBackup);
		}		
	
	{$prefix}BackupLoad();";
	
echo $html;		
	
}

function backup_popup(){
	
$sock=new sockets();
$milter=Paragraphe_switch_img('{enable_backup}','{enable_backup_email_text}','EnableASSPBackup',$sock->GET_INFO("EnableASSPBackup"),'{enable_disable}',290);

	$html="
	<H1>{backupemail_behavior}</H1>
	<div id='assp_backup'>
		<table style='width:100%'>
		<tr>
	
		<td valign='top' width=50%>
			$milter
		</td>
		</tr>
		<td align='right'><hr>
		<input type='button' OnClick=\"javascript:EnableASSPBackup();\" value='{edit}&nbsp;&raquo;'>
		</td>	
		</tr>
		</table>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.index.php');		
}


function greylist_js(){
$page=CurrentPageName();
	$prefix=str_replace(".",'_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{greylisting}');
	
	
	
	$html="
	
	function {$prefix}GreyListLoad(){
		YahooWin3('550','$page?greylist-popup=yes','$title');
	
	}
	

var x_{$prefix}ASSPGreyListSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}GreyListLoad();
	}	
		
		function ASSPGreyListSave(){
			var XHR = new XHRConnection();
			XHR.appendData('DelayGripvalue',document.getElementById('DelayGripvalue').value);
			XHR.appendData('DelaySSL',document.getElementById('DelaySSL').value);
			XHR.appendData('DelayEmbargoTime',document.getElementById('DelayEmbargoTime').value);
			XHR.appendData('DelayWaitTime',document.getElementById('DelayWaitTime').value);
			XHR.appendData('DelayExpiryTime',document.getElementById('DelayExpiryTime').value);
			XHR.appendData('ASSPEnableDelaying',document.getElementById('ASSPEnableDelaying').value);										
			document.getElementById('asspgrey').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}ASSPGreyListSave);
		}	
	
	
	{$prefix}GreyListLoad();
	";
	
	
echo $html;	
	
}

function greylist_save(){
	$sock=new sockets();
	$sock->SET_INFO('ASSPEnableDelaying',$_GET["ASSPEnableDelaying"]);
	$ini=new Bs_IniHandler();
	while (list ($num, $val) = each ($_GET) ){
		$ini->_params["CONF"][$num]=$val;
	}
	$sock->SaveConfigFile($ini->toString(),"ASSPDelayingConfig");
	$sock->getFrameWork("cmd.pĥp?reload-assp=yes");
	
}

function greylist_popup(){
	$sock=new sockets();
	$EnableDelaying=$sock->GET_INFO("ASSPEnableDelaying");
	
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("ASSPDelayingConfig"));
	
	if($ini->_params["CONF"]["DelayGripvalue"]==null){$ini->_params["CONF"]["DelayGripvalue"]="0.4";}
	if($ini->_params["CONF"]["DelaySSL"]==null){$ini->_params["CONF"]["DelaySSL"]="1";}
	if($ini->_params["CONF"]["DelayEmbargoTime"]==null){$ini->_params["CONF"]["DelayEmbargoTime"]="5";}
	if($ini->_params["CONF"]["DelayWaitTime"]==null){$ini->_params["CONF"]["DelayWaitTime"]="28";}
	if($ini->_params["CONF"]["DelayExpiryTime"]==null){$ini->_params["CONF"]["DelayExpiryTime"]="36";}
	
	$enable=Paragraphe_switch_img("{enable} {greylisting}","{EnableDelaying_text}","ASSPEnableDelaying",$EnableDelaying);
	
	$form="
	<div id='asspgrey'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>$enable</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{DelayGripvalue}:</td>
		<td>". Field_text("DelayGripvalue",$ini->_params["CONF"]["DelayGripvalue"],"width:50px")."</td>
		<td>". help_icon("{DelayGripvalue_text}")."</td>
	</tr>
	<tr>
		<td class=legend>{DelayEmbargoTime}:</td>
		<td>". Field_text("DelayEmbargoTime",$ini->_params["CONF"]["DelayEmbargoTime"],"width:50px")."&nbsp;mn</td>
		<td>". help_icon("{DelayEmbargoTime_text}")."</td>
	</tr>
	<tr>
		<td class=legend>{DelayWaitTime}:</td>
		<td>". Field_text("DelayWaitTime",$ini->_params["CONF"]["DelayWaitTime"],"width:50px")."&nbsp;H</td>
		<td>". help_icon("{DelayWaitTime_text}")."</td>
	</tr>	
	<tr>
		<td class=legend>{DelayExpiryTime}:</td>
		<td>". Field_text("DelayExpiryTime",$ini->_params["CONF"]["DelayExpiryTime"],"width:50px")."&nbsp;{days}</td>
		<td>". help_icon("{DelayExpiryTime_text}")."</td>
	</tr>			
	<tr>
		<td class=legend>{DelaySSL}:</td>
		<td>". Field_numeric_checkbox_img("DelaySSL",$ini->_params["CONF"]["DelaySSL"],"{DelaySSL_text}")."</td>
		<td>". help_icon("{DelaySSL_text}")."</td>
	</tr>	
	<tr>
		
		<td colspan=3 align='right'><hr><input type='button' OnClick=\"javascript:ASSPGreyListSave();\" value='{edit}&nbsp;&raquo;'>
	</tr>
	

	</table>
	</td>
	</tr>
	</table>
	</div>
	";
	
	
	$html="<H1>{greylisting}</H1>
	<p class=caption>{grelisting_text}</p>$form";
	$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);
}

?>