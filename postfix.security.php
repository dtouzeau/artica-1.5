<?php
include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.clamav.inc');

	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tab"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["antivirus"])){section_antivirus();exit;}
	if(isset($_GET["status-pattern"])){section_pattern();exit;}
	if(isset($_GET["clamav-pattern"])){ClamAVPatterns();exit;}
	if(isset($_GET["spamass-pattern"])){SpamAsssPatterns();exit;}
	if(isset($_GET["kav-pattern"])){KavAVPatterns();exit;}
	if(isset($_GET["antispam-content"])){section_content_filtering();exit;}
	
	
	
js();
	
function js(){
	$page=CurrentPageName();
	$html="
	$('#BodyContent').load('$page?tab=yes');
	
	";
	
	echo $html;
	
	
}
function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$filters_settings=$tpl->_ENGINE_parse_body('{antispam_filters}');
	$array["status"]='{status}';
	$array["antispam"]=$filters_settings;
	$array["antispam-content"]="{content_filtering}";
	$array["filters-connect"]="{filters_connect}";
	$array["antivirus"]="antivirus";
	$array["status-pattern"]="{patterns_versions}";
	

	while (list ($num, $ligne) = each ($array) ){
		if($num=="antispam"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.index.php?main=filters\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="filters-connect"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.index.php?main=filters-connect\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_postfix_security style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_postfix_security\").tabs();});
		</script>";		
}


function status(){
	
	
	$tpl=new templates();
	if(is_file("ressources/logs/global.status.ini")){
		
		$ini=new Bs_IniHandler("ressources/logs/global.status.ini");
	}else{
		writelogs("ressources/logs/global.status.ini no such file");
		$sock=new sockets();
		$datas=base64_decode($sock->getFrameWork('cmd.php?Global-Applications-Status=yes'));
		$ini=new Bs_IniHandler($datas);
	}
	
	$sock=new sockets();
	$datas=$sock->getFrameWork('cmd.php?refresh-status=yes');
	$activate=Paragraphe('64-folder-install.png','{AS_ACTIVATE}','{AS_ACTIVATE_TEXT}',"javascript:Loadjs('postfix.index.php?script=antispam')",null,210,null,0,true);
	
	$array[]="ASSP";
	$array[]="AMAVISD";
	$array[]="AMAVISD_MILTER";
	$array[]="SPAMASSASSIN";
	$array[]="SPAMASS_MILTER";
	$array[]="APP_CLUEBRINGER";
	$array[]="DKIM_FILTER";
	$array[]="SPFMILTER";
	$array[]="CLAMAV";
	$array[]="FRESHCLAM";
	$array[]="MAILSPY";	
	$array[]="KAVMILTER";
	$array[]="KAS_MILTER";
	$array[]="KAS3";
	$array[]="BOGOM";
	$array[]="MILTER_GREYLIST";
	$array[]="POLICYD_WEIGHT";
	$array[]="APP_MILTER_DKIM";
	$array[]="APP_ARTICA_POLICY";
	$tr[]=$activate;
	while (list ($num, $ligne) = each ($array) ){
		$tr[]=DAEMON_STATUS_ROUND($ligne,$ini,null,1);
		
	}	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='middle' align='center'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);	
$html="<center>$html</center>";	
$tpl=new templates();
$datas=$tpl->_ENGINE_parse_body($html);		
echo $datas;	
}

function section_content_filtering(){
	$spamassassin="yes";
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$sock=new sockets();	
	$SpamAssMilterEnabled=$sock->GET_INFO("SpamAssMilterEnabled");
	$ou_encoded=base64_encode("_Global");
	
	$keywords=Paragraphe('keywords-64.png','{block_keywords}','{block_keywords_text}',"javascript:Loadjs('spamassassin.keywords.php')",null,210,null,0,true);
	$keywords_disabled=Paragraphe('keywords-64-grey.png','{block_keywords}','{block_keywords_text}',"javascript:blur()",null,210,null,0,true);
	$global_smtp_rules=Buildicon64('DEF_ICO_POSTFIX_REGEX');
	$extensions_block=Paragraphe("bg_forbiden-attachmt-64.png","{attachment_blocking}","{attachment_blocking_text}","javascript:Loadjs('domains.edit.attachblocking.ou.php?ou=$ou_encoded')",null,210,null,0,true);
	$tests_eml=Paragraphe("email-info-64.png","{message_analyze}","{message_as_analyze_text}","javascript:Loadjs('spamassassin.analyze.php')",null,210,null,0,true);
	$tests_eml_disabled=Paragraphe("email-info-64-grey.png","{message_analyze}","{message_as_analyze_text}","",null,210,null,0,true);
	$message_analyze=$tests_eml;
	$sa_update_disabled=Paragraphe("64-spam-update-grey.png","{UPDATE_SA_UPDATE}","{UPDATE_SA_UPDATE_TEXT}","",null,210,null,0,true);
	$sa_update=Paragraphe("64-spam-update.png","{UPDATE_SA_UPDATE}","{UPDATE_SA_UPDATE_TEXT}","javascript:Loadjs('sa.update.php')",null,210,null,0,true);
	
	
	
	
	
	
	
	if($spamassassin<>null){
		if(!$users->AMAVIS_INSTALLED){
			if($users->SPAMASS_MILTER_INSTALLED){
				if($SpamAssMilterEnabled<>1){
					$keywords=$keywords_disabled;
					$message_analyze=$tests_eml_disabled;
					$sa_update=$sa_update_disabled;
				}
			}else{
				$keywords=$keywords_disabled;
				$message_analyze=$tests_eml_disabled;
				$sa_update=$sa_update_disabled;
			}
		}
		
		if($users->AMAVIS_INSTALLED){
			if($users->EnableAmavisDaemon<>1){
				if($users->SPAMASS_MILTER_INSTALLED){
					if($SpamAssMilterEnabled<>1){
						$keywords=$keywords_disabled;
						$message_analyze=$tests_eml_disabled;
						$sa_update=$sa_update_disabled;
						}	
				}else{
					$keywords=$keywords_disabled;
					$message_analyze=$tests_eml_disabled;
					$sa_update=$sa_update_disabled;
				}
			}
				
		}
	}

	if($users->KASPERSKY_SMTP_APPLIANCE){
		$keywords=null;
		$message_analyze=null;
	}	
	
	$tr[]=$keywords;
	$tr[]=$global_smtp_rules;
	$tr[]=$extensions_block;
	$tr[]=$message_analyze;
	$tr[]=$sa_update;
	
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='middle' align='center'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);	
$html="<center>$html</center>";	
$tpl=new templates();
$datas=$tpl->_ENGINE_parse_body($html);		
echo $datas;	
}


function section_antivirus(){
	$users=new usersMenus();
	$sock=new sockets();
	$activate=Paragraphe('64-folder-install.png','{AS_ACTIVATE}','{AS_ACTIVATE_TEXT}',"javascript:Loadjs('postfix.index.php?script=antispam')",null,210,null,0,true);
	
		
	//$clamav_unofficial=Paragraphe("clamav-64.png","{clamav_unofficial}","{clamav_unofficial_text}",
	//"javascript:Loadjs('clamav.unofficial.php')",null,210,100,0,true);//
	
	$clamav_unofficial=Paragraphe("clamav-64.png","{APP_CLAMAV}","{APP_CLAMAV_TEXT}",
	"javascript:Loadjs('clamd.php')",null,210,100,0,true);//	
	
	

		
	$kasper=Paragraphe('icon-antivirus-64.png','{APP_KAVMILTER}','{APP_KAVMILTER_TEXT}',"javascript:Loadjs('milter.index.php?ajax=yes')",null,210,null,0,true);		

	if(!$users->CLAMD_INSTALLED){
				$clamav_unofficial=Paragraphe("clamav-64-grey.png","{APP_CLAMAV}","{APP_CLAMAV_TEXT}",
				"",null,210,100,0,true);
	}
	
	if($users->KASPERSKY_SMTP_APPLIANCE){$clamav_unofficial=null;}
	
	
	$kavmilterEnable=$sock->GET_INFO("kavmilterEnable");
	

	if(!$users->KAV_MILTER_INSTALLED){
		$kasper=Paragraphe('icon-antivirus-64-grey.png','{APP_KAVMILTER}','{error_module_not_installed}',"",null,210,null,0,true);
	}else{
		if($kavmilterEnable<>1){
			$kasper=Paragraphe('icon-antivirus-64-grey.png','{APP_KAVMILTER}','{error_module_not_enabled}',"",null,210,null,0,true);
		}
	}
	
	
	
	$tr[]=$kasper;	
	$tr[]=$clamav_unofficial;
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='middle' align='center'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);	
$html="<center>$html</center>";	
$tpl=new templates();
$datas=$tpl->_ENGINE_parse_body($html);		
echo $datas;	
	
	
}

function section_pattern(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div id='kav-pattern'></div>
	<hr style='margin:10px'>
	<div id='spamass-pattern'></div>
	<hr style='margin:10px'>
	<div id='clamav-pattern'></div>
	
	
	<script>
		LoadAjax('kav-pattern','$page?kav-pattern=yes');
		LoadAjax('spamass-pattern','$page?spamass-pattern=yes');
		LoadAjax('clamav-pattern','$page?clamav-pattern=yes');
	</script>
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function KavAVPatterns(){
	$icon="datasource-32.png";
	$sock=new sockets();
	$tpl=new templates();
	$kavPattern=$sock->getFrameWork("cmd.php?KavMilterDbVer=yes");
	$kas3Patterns=$sock->getFrameWork("cmd.php?Kas3DbVer=yes");

	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr><th colspan=3>Kaspersky</th></tr>
	<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{pattern}</th>
	</tr>
</thead>
<tbody class='tbody'>
		<tr class=oddRow>
			<td width=1%><img src='img/$icon'></td>
			<td style='font-size:14px;font-weight:bold' nowrap>$kavPattern</td>
			<td style='font-size:14px'>{APP_KAVMILTER}</td>
		</tr>
		<tr >
			<td width=1%><img src='img/$icon'></td>
			<td style='font-size:14px;font-weight:bold' nowrap>$kas3Patterns</td>
			<td style='font-size:14px'>{APP_KAS3}</td>
		</tr>		
		

</table>";	
	
	
		echo $tpl->_ENGINE_parse_body($html);
	
	
}



function SpamAsssPatterns(){
	$icon="datasource-32.png";
	$sock=new sockets();
	$tpl=new templates();
	$spamdb=$sock->getFrameWork("cmd.php?SpamAssDBVer=yes");
	

	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr><th colspan=3>Spamassassin</th></tr>
	<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{pattern}</th>
	</tr>
</thead>
<tbody class='tbody'>
		<tr class=oddRow>
			<td width=1%><img src='img/$icon'></td>
			<td style='font-size:14px;font-weight:bold' nowrap>$spamdb</td>
			<td style='font-size:14px'>{APP_SPAMASSASSIN}</td>
		</tr>
</table>";	
	
	
		echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function ClamAVPatterns(){
	$tpl=new templates();
	$users=new usersMenus();
	if(!$users->CLAMD_INSTALLED){return null;}
	if($users->KASPERSKY_SMTP_APPLIANCE){return;}
	
	$clam=new clamav();
	$array=$clam->LoadDatabasesStatus();
	if(!is_array($array)){return null;}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr><th colspan=3>{APP_CLAMAV}</th></tr>
	<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{pattern}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	$icon="datasource-32.png";
	while (list ($file, $date) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/$icon'></td>
			<td style='font-size:14px;font-weight:bold' nowrap>{$date[1]}</td>
			<td style='font-size:14px'>{$date[0]}</td>
		</tr>
		";
		
	}
	
	$html=$html . "</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}