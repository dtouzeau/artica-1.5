<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.kav4proxy.inc');
	
	$usersmenus=new usersMenus();
if(!$usersmenus->SQUID_INSTALLED && !$usersmenus->KAV4PROXY_INSTALLED && !$usersmenus->AsSquidAdministrator){echo header('location:users.index.php');exit();}
	if( isset($_POST['upload']) ){main_license_LicenceUploaded();exit();}
	if(isset($_GET["Status"])){echo Status();exit;}
	if(isset($_GET["main"])){switchTab();exit;}
	if(isset($_GET["SaveConfStandard"])){SaveConfStandard();exit;}
	if(isset($_GET["kav4proxy_addnewgroup"])){main_rules_add();exit;}
	if(isset($_GET["LoadGroup"])){main_rules_group();exit;}
	if(isset($_POST["Kav4ProxyAddClientIP"])){main_rules_group_addip();exit;}
	if(isset($_POST["Kav4ProxyDeleteClientIP"])){main_rules_group_delip();exit;}
	if(isset($_POST["Kav4ProxyDeleteURL"])){main_rules_group_deluri();exit;}
	if(isset($_POST["Kav4ProxyAddClientURL"])){main_rules_group_adduri();exit;}
	if(isset($_POST["Kav4ProxyAddExcludeURL"])){main_rules_group_addExcludeURL();exit;}
	if(isset($_POST["Kav4ProxyAddExcludeMime"])){main_rules_group_addExcludeMime();exit;}
	if(isset($_POST["Kav4ProxyDeleteExcludeMime"])){main_rules_group_delExcludeMime();exit;}
	if(isset($_POST["Kav4ProxyDeleteExcludeURL"])){main_rules_group_delExcludeURL();exit;}
	if(isset($_GET["group_actions"])){main_rules_group_editactions();exit;}
	if(isset($_GET["Kav4ProxyMoveGroup"])){main_rules_group_move();exit;}
	if(isset($_POST["Kav4ProxyDeleteGroup"])){main_rules_group_del();exit;}
	if(isset($_GET["events"])){main_events_page();exit;}
	if(isset($_GET["Kav4proxyAddkey"])){echo main_license_form_key();exit;}
	if(isset($_GET["iframe_addkey"])){echo main_license_Addkey_form();exit;}
	
	
	if(isset($_GET["sec2"])){main_rules_switch();exit;}

page();	
function page(){



$page=CurrentPageName();



$html="
<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'>
	<table>
	<tr>
		<td width=1% valign='top'><img src='img/bg_kav4proxy.jpg'></td>
		</tr>
	</table>
</td>
<td valign='top'>
<div id='servinfos'></div>
<script>LoadAjax('servinfos','$page?Status=yes');</script>
</td>
</tr>
<tr>
<td colspan=2>
	<div id='mainconfig'></div>
	<script>LoadAjax('mainconfig','$page?main=yes&tab=0');</script>

</td>
</table>

";

$cfg["LANG_FILE"]="milter.index.php.txt";
$cfg["JS"][]="js/kavmilterd.js";
$tpl=new template_users('Kaspersky Antivirus For Squid',$html,0,0,0,0,$cfg);
echo $tpl->web_page;
	
	
	
}

function main_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{globalsettings}';
	$array[]='{update}';
	$array[]='{rules}';
	$array[]='{logs}';	
	$array[]='{statistics}';
	$array[]='{licenseproxy}';		
	$array[]='{services}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('mainconfig','$page?main=yes&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}
function switchTab(){
	switch ($_GET["tab"]) {
		case 0:GlobalSettings();break;
	    case 1:main_update();break;
	    case 2:main_rules();break;
	    case 3:main_events();break;
	    case 4:main_stats();break;
	    case 5:main_license();break;
	    case 6:main_services();break;
		default:GlobalSettings();break;
	}
	
}


function main_services(){
	$html=main_tabs()."<br>
	<H5>{services}</H5>
	<table style='width:100%'>
	<tr>
	<td width=50%></td>
	<td width=50% valing='top'>" . applysettings("kav4proxy") ."</td>
	</tr>
	</table>";	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'milter.index.php');		
}

function Status(){


$page=CurrentPageName();
$users=new usersMenus();
if($users->KAV4PROXY_PID==null){$img1="status_critical.gif";}else{$img1="status_ok.gif";}
if(preg_match('#([0-9]{1,2})([0-9]{1,2})([0-9]{1,4});([0-9]{1,2})([0-9]{1,2})#',$users->KAV4PROXY_PATTERN,$regs)){
			$users->KAV4PROXY_PATTERN="".$regs[3]. "/" .$regs[2]. "/" .$regs[1] . " " . $regs[4] . ":" . $regs[5]  . ' (moscow GMT)';}
$status=RoundedLightGreen("
<H4>Status</H4>
<table style='width:100%'>
<tr>
	<td valign='top'align='center'><img src='img/$img1'></td>
	<td align=right valign='top' ><strong>{use_pid}:</strong></td>
	<td valign='top'>$users->KAV4PROXY_PID</td>
</tr>
<tr>
	<td valign='top' align='center'><img src='img/$img1'></td>
	<td align=right valign='top'><strong>{memory}:</strong></td>
	<td valign='top'>$users->KAV4PROXY_MEMORY mb</td>
</tr>

<tr>
	<td valign='top' align='center'><img src='img/icon_info.gif'></td>
	<td align=right valign='top'><strong>{version}:</strong></td>
	<td valign='top'>$users->KAV4PROXY_VERSION</td>
</tr>
<tr>
<td valign='top' align='center'><img src='img/icon_info.gif'></td>
<td nowrap align=left valign='top'><strong>{pattern_ver}:</strong></td><td></td>
</tr>
<tr>
<td>&nbsp;</td>
<td colspan=2 nowrap align='right'><strong>$users->KAV4PROXY_PATTERN</strong></td>
</tr>
<tr><td colspan=3 align='right'>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('servinfos','$page?Status=yes');")."</td></tr>
</table>");
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($status);	
	
}

function GlobalSettings(){
	$kav4=new kav4proxy();
	$page=CurrentPageName();
	
	if($kav4->main_array["LogFacility"]=="file"){
		$LogFilepath="
	<tr>
		<td align='right'><strong>{LogFilepath}:</strong></td>
		<td align='left'><strong>{$kav4->main_array["LogFilepath"]}</td>
		<td align='left'>&nbsp;</td>
	</tr>
	<tr>
		<td align='right'><strong>{LogRotate}:</strong></td>
		<td align='left'>" . Field_text('LogRotate',$kav4->main_array["LogRotate"],'width:50px')." (mb)</td>
		<td align='left'>" . help_icon('{LogRotate_text}') . "</td>
	</tr>	
	<tr>
		<td align='right'><strong>{RotateRounds}:</strong></td>
		<td align='left'>" . Field_text('RotateRounds',$kav4->main_array["RotateRounds"],'width:50px')." </td>
		<td align='left'>" . help_icon('{RotateRounds_text}') . "</td>
	</tr>		
	
	";
	}
	
$arr2=RoundedLightGrey("
			<table style=width:100%'>
				<tr>
				<td align='right'><strong>{LogFacility}:</strong></td>
				<td align='left'>" . Field_array_Hash(array("syslog"=>"syslog","file"=>"{file}"),'LogFacility',$kav4->main_array["LogFacility"])."</td>
				<td align='left'>" . help_icon('{LogFacility_text}',false,'milter.index.php') . "</td>
				</tr>		
				$LogFilepath
				</table>");	
	
$arr1=RoundedLightGrey("
			<table style='width:100%'>
			<tr>
			<td align='right'><strong>{runasuid}:</strong></td>
			<td align='left'><strong>{$kav4->main_array["User"]}</td>
			<td align='left'>&nbsp;</td>
			</tr>
			<tr>
			<td align='right'><strong>{RunAsGid}:</strong></td>
			<td align='left'><strong>{$kav4->main_array["Group"]}</td>
			<td align='left'>&nbsp;</td>
			</tr>
			<tr>
			<td align='right'><strong>{ListenAddress}:</strong></td>
			<td align='left'><strong>{$kav4->main_array["ListenAddress"]}</td>
			<td align='left'>" . help_icon('{ListenAddress_text}') . "</td>
			</tr>
			<tr>
			<td align='right'><strong>{timeout}:</strong></td>
			<td align='left'>" . Field_text('timeout',$kav4->main_array["Timeout"],'width:100px')."</td>
			<td align='left'>" . help_icon('{timeout}') . "</td>
			</tr>	
			<tr>
			<td align='right'><strong>{TempDir}:</strong></td>
			<td align='left'><strong>{$kav4->main_array["TempDir"]}</td>
			<td align='left'>" . help_icon('{TempDir_text}') . "</td>
			</tr>
			</table>");	
	
$arr3=RoundedLightGreen("
<H5>{icapserver_1}</H5>
				<table style='width:100%'>
				<tr>
				<td align='right'><strong>{MaxChildren}:</strong></td>
				<td align='left'>" . Field_text('MaxChildren',$kav4->main_array["MaxChildren"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxChildren_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{IdleChildren}:</strong></td>
				<td align='left'>" . Field_text('IdleChildren',$kav4->main_array["IdleChildren"],'width:50px')."</td>
				<td align='left'>" . help_icon('{IdleChildren_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{MaxReqsPerChild}:</strong></td>
				<td align='left'>" . Field_text('MaxReqsPerChild',$kav4->main_array["MaxReqsPerChild"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxReqsPerChild_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{MaxEnginesPerChild}:</strong></td>
				<td align='left'>" . Field_text('MaxEnginesPerChild',$kav4->main_array["MaxEnginesPerChild"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxEnginesPerChild_text}',false,'milter.index.php') . "</td>
				</table>");	

$arr4=RoundedLightGreen("
<H5>{icapserver_2}</H5>
				<table style='width:100%'>
				<tr>
				<td align='right'><strong>{AnswerMode}:</strong></td>
				<td align='left'>" . Field_array_Hash(array('partial'=>'partial','complete'=>'complete'),"AnswerMode",null)."</td>
				<td align='left'>" . help_icon('{AnswerMode_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{MaxSendDelayTime}:</strong></td>
				<td align='left'>" . Field_text('MaxSendDelayTime',$kav4->main_array["MaxSendDelayTime"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxSendDelayTime_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{PreviewSize}:</strong></td>
				<td align='left'>" . Field_text('PreviewSize',$kav4->main_array["PreviewSize"],'width:50px')."</td>
				<td align='left'>" . help_icon('{PreviewSize_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{MaxConnections}:</strong></td>
				<td align='left'>" . Field_text('MaxConnections',$kav4->main_array["MaxConnections"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxConnections_text}',false,'milter.index.php') . "</td>
				</tr>				
				<tr>
				<td align='right'><strong>{Allow204}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox('Allow204',$kav4->main_array["Allow204"])."</td>
				<td align='left'>" . help_icon('{Allow204_text}',false,'milter.index.php') . "</td>
				</tr>
				</table>	");	



$scan_engine=RoundedLightGrey("
				<table style='width:100%'>
				<tr>
				<td align='right'><strong>{MaxScanTime}:</strong></td>
				<td align='left'>" . Field_text('MaxScanTime',$kav4->main_array["MaxScanTime"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxScanTime_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{Cure}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("Cure",$kav4->main_array["Cure"])."</td>
				<td align='left'>" . help_icon('{Cure_text}',false,'milter.index.php') . "</td>
				</tr>					
				<tr>
				<td align='right'><strong>{ScanArchives}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanArchives",$kav4->main_array["ScanArchives"])."</td>
				<td align='left'>" . help_icon('{ScanArchives_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{ScanPacked}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanPacked",$kav4->main_array["ScanPacked"])."</td>
				<td align='left'>" . help_icon('{ScanPacked_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{ScanMailBases}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanMailBases",$kav4->main_array["ScanMailBases"])."</td>
				<td align='left'>&nbsp;</td>
				</tr>	
				<tr>
				<td align='right'><strong>{ScanMailPlain}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanMailPlain",$kav4->main_array["ScanMailPlain"])."</td>
				<td align='left'>&nbsp;</td>
				</tr>												
				<tr>
				<td align='right'><strong>{UseAVBasesSet}:</strong></td>
				<td align='left'>" . Field_array_Hash(array("standard"=>"standard","extended"=>"extended","redundant"=>"redundant"),'UseAVBasesSet',$kav4->main_array["UseAVBasesSet"])."</td>
				<td align='left'>" . help_icon('{UseAVBasesSet_text}',false,'milter.index.php') . "</td>
				</tr>	
				</table>");	

$button="<input type='button' style='margin:5px;width:150px' OnClick=\"javascript:ParseForm('ffm1','$page',true);LoadAjax('mainconfig','$page?main=yes&tab=0')\" value='{edit}&nbsp;&raquo;'>";
	
	
	$html=main_tabs() . "<br>
	<form name='ffm1'>
	<input type='hidden' name='SaveConfStandard' value='yes'>
	<H4>{globalsettings}</H4>
	<table STYLE='width:100%;'>
		<tr>
	<td valign='top' >
		$arr1<br>$scan_engine<br>$button
	</td>
	
	<td valign='top' >
		$arr3<br>$arr4<br>

	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"milter.index.php");
	}
	
function SaveConfStandard(){
	$kav=new kav4proxy();
	while (list ($num, $ligne) = each ($_GET) ){
		$kav->rule_array[$num]=$ligne;
	}
	$tpl=new templates();
	if($kav->SaveToLdap()){echo $tpl->_ENGINE_parse_body('{success}');}
}


function main_update(){
	$milter=new kav4proxy();
	include_once('ressources/class.artica.inc');
	$artica=new artica_general();
	$UseProxy=Field_yesno_checkbox("UseProxy",$milter->main_array["UseProxy"]);
	$ProxyAddress=Field_text('ProxyAddress',$milter->main_array["ProxyAddress"],'width:250px');
	
	if($artica->ArticaProxyServerEnabled=='yes'){
		$UseProxy="<strong>Yes</strong><input type='hidden' name='UseProxy' value='yes'>";
		$ProxyAddress="<strong>$artica->ArticaCompiledProxyUri</strong><input type='hidden' name='ProxyAddress' value='$artica->ArticaProxyServerEnabled'>";
	}
	
	
	
$arr=RoundedLightGrey("
			<table style=width:100%'>
				<tr>
				<td align='right'><strong>{UseUpdateServerUrl}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("UseUpdateServerUrl",$milter->main_array["UseUpdateServerUrl"])."</td>
				<td align='left'>" . help_icon('{UseUpdateServerUrl_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{UseUpdateServerUrlOnly}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("UseUpdateServerUrlOnly",$milter->main_array["UseUpdateServerUrlOnly"])."</td>
				<td align='left'>" . help_icon('{UseUpdateServerUrlOnly_text}',false,'milter.index.php') . "</td>
				</tr>		
				<tr>
				<td align='right'><strong>{UpdateServerUrl}:</strong></td>
				<td align='left'>" . Field_text('UpdateServerUrl',$milter->main_array["UpdateServerUrl"],'width:250px')."</td>
				<td align='left'>" . help_icon('{UpdateServerUrl_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{RegionSettings}:</strong></td>
				<td align='left'>" . Field_array_Hash(array("Russia"=>"Russia","Europe"=>"Europe","America"=>"America","China"=>"China","Japan"=>"Japan","Korea"=>"Korea"),'RegionSettings',$milter->main_array["RegionSettings"])."</td>
				<td align='left'>" . help_icon('{RegionSettings_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{UseProxy}:</strong></td>
					<td align='left'>$UseProxy</td>
				<td align='left'>&nbsp;</td>
				</tr>		
				<tr>
				<td align='right'><strong>{ProxyAddress}:</strong></td>
				<td align='left'>$ProxyAddress</td>
				<td align='left'>" . help_icon('{ProxyAddress_text}',false,'milter.index.php') . "</td>
				</tr>																
				
				</table>");	
$arr4="<input type='button' style='margin:5px;width:150px' OnClick=\"javascript:ParseForm('ffm1','$page',true);LoadAjax('mainconfig','$page?main=yes&tab=1')\" value='{edit}&nbsp;&raquo;'>";
	
	
	$html=main_tabs() . "<br>
	<form name='ffm1'>
	<input type='hidden' name='SaveConfStandard' value='yes'>
	<H4>{update_settings}</H4>
	<table STYLE='width:100%;'>
		<tr>
	<td valign='top' >
		<br>$arr<br>
	</td>
	
	<td valign='top' >
		$arr3
	<p>
	$arr4</p>
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');	
	
	
}

function main_rules(){
$add=Paragraphe('member-64-add.png','{add_group}','{add_group_text}',"javascript:Kav4proxyAddGroup();");	
$add=RoundedLightGrey($add);
	
$html=main_tabs() . "<br>

	<input type='hidden' id='add_group_text' value='{add_group_text}'>
	
	
	<table STYLE='width:100%;'>
		<tr>
	<td valign='top' >
	
		<div style='width:450px' id='group_data'>" . main_rules_list() . "</div>
	</td>
	
	<td valign='top' >
		$add
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');		
}

function main_rules_list(){
	
	$kav=new kav4proxy();
	if(!is_array($kav->main_groups)){return null;}
	$style1="style='border-bottom:1px dotted #CCCCCC'";
	$html="<H5>{rules}</H5><table style='width:100%'>
	<tr>
	<th>&nbsp;</th>
	<th><strong>{group}</th>
	<th><strong>{from}</th>
	<th><strong>{to}</th>
	<th><strong>&nbsp;</th>
	<th><strong>&nbsp;</th>
	<th><strong>&nbsp;</th>
	</tr>
	";
	while (list ($num, $val) = each ($kav->main_groups) ){
		$groupname=$val["name"];
		$hash=$val["main"];	
		
		$link=CellRollOver("kav4ProxyEditGroup($num);");
		$ClientIP=$hash["icapserver.groups:$groupname"]["ClientIP"];
		$URI=$hash["icapserver.groups:$groupname"]["URL"];
		
		if(is_array($ClientIP)){
		$ip="<table style='width:100%'>";
			while (list ($index, $line) = each ($ClientIP)){
				if($line<>null){$ip=$ip."<tr><td width=1%><img src='img/fw_bold.gif'></td><td>$line</td></tr>";}
			}
		$ip=$ip."</table>";}
			
			
			
			$u=null;
			$line=null;
			if(is_array($URI)){
				$u="<table style='width:100%'>";
			while (list ($index, $line) = each ($URI)){
				if(trim($line)<>null){$u=$u."<tr>
												<td width=1%><img src='img/fw_bold.gif'></td>
												<td>$line</td>
											</tr>";}
			}
			$u=$u."</table>";}
		
		
		$html=$html . "
		<tr>
		<td width=1% valign='top' $style1><img src='img/member-24.png'></td>
		<td $link valign='top' $style1><strong>$groupname</strong></td>
		<td $link valign='top' $style1><strong>$ip</strong></td>
		<td $link valign='top' $style1><strong>$u</strong></td>
		<td valign='top' width=1% valign='top' $style1>" . imgtootltip('arrow_down.gif','{move}',"Kav4ProxyMoveGroup('$num','down')")."</td>
		<td valign='top' width=1% valign='top' $style1>" . imgtootltip('arrow_up.gif','{move}',"Kav4ProxyMoveGroup('$num','up')")."</td>
		<td valign='top' width=1% valign='top' $style1>" . imgtootltip('x.gif','{delete}',"Kav4ProxyDeleteGroup('$num')")."</td>
		</tr>
		
		
		";
		
		
		
	}
	$html=$html . "</table>";
	$tpl=new templates();
	return  RoundedLightGreen($tpl->_ENGINE_parse_body($html,'milter.index.php'));
	
}

function main_rules_group($gid=null){
	if(!isset($_GET["LoadGroup"])){$_GET["LoadGroup"]=$gid;}
	$gid=$_GET["LoadGroup"];
	$kav=new kav4proxy();
	$groupname=$kav->main_groups[$gid]["name"];
	$hash=$kav->main_groups[$gid]["main"];
	
	$ClientIP=$hash["icapserver.groups:$groupname"]["ClientIP"];
	$URI=$hash["icapserver.groups:$groupname"]["URL"];
	
		if(is_array($ClientIP)){
		$ip="<table style='width:100%'>";
			while (list ($index, $line) = each ($ClientIP)){
				if($line<>null){
					$ip=$ip."<tr>
								<td width=1%><img src='img/fw_bold.gif'></td>
								<td>$line</td>
								<td width=1%>" . imgtootltip('x.gif','{delete}',"Kav4ProxyDeleteClientIP('$gid','$index');")."</td>
							</tr>";}
					}
		$ip=$ip."</table>";}
			
			
			
			$u=null;
			$line=null;
			if(is_array($URI)){
				$u="<table style='width:100%'>";
			while (list ($index, $line) = each ($URI)){
				if(trim($line)<>null){$u=$u."<tr " . CellRollOver().">
												<td width=1%><img src='img/fw_bold.gif'></td>
												<td>$line</td>
												<td width=1%>" . imgtootltip('x.gif','{delete}',"Kav4ProxyDeleteURL('$gid','$index');")."</td>
											</tr>";}
			}
			$u=$u."</table>";}	
	
	//<td align='left'>" . imgtootltip('plus-24.png','{add_clientip}',"Kav4ProxyAddClientIP($gid);")."</td>
	$main=RoundedLightGrey("
			<H5>{rule_flow}</H5>
			<table style=width:100%'>
				<tr>
				<th>{ClientIP}</th>
				<th>{ClientURI}</th>
				</tr>
				<tr>
					<td align='left' valign='top'><a href=\"javascript:Kav4ProxyAddClientIP($gid);\">[{add_clientip}]</a></td>
					<td align='left' valign='top'><a href=\"javascript:Kav4ProxyAddClientURL($gid);\">[{add_clienturl}]</a></td>
				</tr>				
				<tr>
					<td align='left' valign='top'>$ip</td>
					<td align='left' valign='top'>$u</td>
				</tr>	
			</table>");
	

	$html=main_rules_group_tabs($gid)."<br>
	<input type='hidden' id='ClientIPExplain' value='{ClientIP_text}'>
	<input type='hidden' id='ClientURLExplain' value='{ClientURI_text}'>
	$main
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');
	
}



function main_rules_group_scanner($gid=null){
	$kav=new kav4proxy();
	$groupname=$kav->main_groups[$gid]["name"];
	$hash=$kav->main_groups[$gid]["main"]["icapserver.actions:$groupname"];
	$page=CurrentPageName();

$main=RoundedLightGrey("
<form name=\"ffm_$gid\">
			<input type='hidden' name='gid' value='$gid'>
			<input type='hidden' name='group_actions' value='yes'>
			<p class=caption>{scan_options_text}</p>
			
			<table style=width:100%'>
				<tr>
					<td valign='top'><strong>{BasesErrorAction}</strong></td>
					<td valign='top' align='center'>" . Field_deny_skip_checkbox_img('BasesErrorAction',$hash["BasesErrorAction"])."</td>
					
				</tr>
				<tr>
					<td valign='top'><strong>{CorruptedAction}</strong></td>
					<td valign='top' align='center'>" . Field_deny_skip_checkbox_img('CorruptedAction',$hash["CorruptedAction"])."</td>
				</tr>
				<tr>
					<td valign='top'><strong>{CuredAction}</strong></td>
					<td valign='top' align='center'>" . Field_deny_skip_checkbox_img('CuredAction',$hash["CuredAction"])."</td>
				</tr>	
				<tr>
					<td valign='top'><strong>{ErrorAction}</strong></td>
					<td valign='top' align='center'>" . Field_deny_skip_checkbox_img('ErrorAction',$hash["ErrorAction"])."</td>
				</tr>	
				<tr>
					<td valign='top'><strong>{InfectedAction}</strong></td>
					<td valign='top' align='center'>" . Field_deny_skip_checkbox_img('InfectedAction',$hash["InfectedAction"])."</td>
				</tr>
				<tr>
					<td valign='top' colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm_$gid','$page',true);\"></td>
				</tr>																				
			</table></form>");


$hash=$kav->main_groups[$gid]["main"]["icapserver.engine.options:$groupname"];

$scan_engine=RoundedLightGrey("
			<form name=\"ffm1_$gid\">
			<input type='hidden' name='gid' value='$gid'>
			<input type='hidden' name='group_actions' value='yes'>
				<table style='width:100%'>
				<tr>
				<td align='right'><strong>{MaxScanTime}:</strong></td>
				<td align='left'>" . Field_text('MaxScanTime',$hash["MaxScanTime"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxScanTime_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{Cure}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("Cure",$hash["Cure"])."</td>
				<td align='left'>" . help_icon('{Cure_text}',false,'milter.index.php') . "</td>
				</tr>					
				<tr>
				<td align='right'><strong>{ScanArchives}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanArchives",$hash["ScanArchives"])."</td>
				<td align='left'>" . help_icon('{ScanArchives_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{ScanPacked}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanPacked",$hash["ScanPacked"])."</td>
				<td align='left'>" . help_icon('{ScanPacked_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{ScanMailBases}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanMailBases",$hash["ScanMailBases"])."</td>
				<td align='left'>&nbsp;</td>
				</tr>	
				<tr>
				<td align='right'><strong>{ScanMailPlain}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanMailPlain",$hash["ScanMailPlain"])."</td>
				<td align='left'>&nbsp;</td>
				</tr>												
				<tr>
				<td align='right'><strong>{UseAVBasesSet}:</strong></td>
				<td align='left'>" . Field_array_Hash(array("standard"=>"standard","extended"=>"extended","redundant"=>"redundant"),'UseAVBasesSet',$hash["UseAVBasesSet"])."</td>
				<td align='left'>" . help_icon('{UseAVBasesSet_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
					<td valign='top' colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1_$gid','$page',true);\"></td>
				</tr>					
				</table></form>");	

	$html=main_rules_group_tabs($gid)."
	<H5>{scan_options}</H5>
	
		$main
		<br>
		$scan_engine
		";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');	
	
	
}

function Field_deny_skip_checkbox_img($name,$value,$tooltip=null){
	$value=strtolower($value);
	if($tooltip==null){$tooltip='{click_deny_skip}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="no";}
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	if($value=='skip'){$img='img/status_ok.gif';}else{$img='img/status_critical.gif';}
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'><a href=\"javascript:SwitchDenySkip('$name');\"><img src='$img' id='img_$name' $tooltip></a>";
	return $html;
	
}

function main_rules_group_exclude($gid=null){
	$kav=new kav4proxy();
	$groupname=$kav->main_groups[$gid]["name"];
	$hash=$kav->main_groups[$gid]["main"];
	$ExcludeMimeType=$hash["icapserver.filter:$groupname"]["ExcludeMimeType"];
	$ExcludeURL=$hash["icapserver.filter:$groupname"]["ExcludeURL"];	

	
	
		if(is_array($ExcludeURL)){
		$ExcludeURL_line="<table style='width:100%'>";
			while (list ($index, $line) = each ($ExcludeURL)){
				if($line<>null){
					$ExcludeURL_line=$ExcludeURL_line."<tr>
								<td width=1%><img src='img/fw_bold.gif'></td>
								<td>$line</td>
								<td width=1%>" . imgtootltip('x.gif','{delete}',"Kav4ProxyDeleteExcludeURL('$gid','$index');")."</td>
							</tr>";}
					}
		$ExcludeURL_line=$ExcludeURL_line."</table>";}
			
			
			
			$u=null;
			$line=null;
			if(is_array($ExcludeMimeType)){
				$ExcludeMimeType_line="<table style='width:100%'>";
			while (list ($index, $line) = each ($ExcludeMimeType)){
				if(trim($line)<>null){$ExcludeMimeType_line=$ExcludeMimeType_line."<tr " . CellRollOver().">
												<td width=1%><img src='img/fw_bold.gif'></td>
												<td>$line</td>
												<td width=1%>" . imgtootltip('x.gif','{delete}',"Kav4ProxyDeleteExcludeMime('$gid','$index');")."</td>
											</tr>";}
			}
			$ExcludeMimeType_line=$ExcludeMimeType_line."</table>";}		
	
	$main=RoundedLightGrey("
			<H5>{excludes}</H5>
			<table style=width:100%'>
				<tr>
				<th>{ExcludeURL}</th>
				<th>{ExcludeMimeType}</th>
				</tr>
				<tr>
					<td align='left' valign='top'><a href=\"javascript:Kav4ProxyAddExcludeUrl($gid);\">[{add_ExcludeURL}]</a></td>
					<td align='left' valign='top'><a href=\"javascript:Kav4ProxyAddExcludeMime($gid);\">[{add_ExcludeMimeType}]</a></td>
				</tr>				
				<tr>
					<td align='left' valign='top'>$ExcludeURL_line</td>
					<td align='left' valign='top'>$ExcludeMimeType_line</td>
				</tr>	
			</table>");
	

	$html=main_rules_group_tabs($gid)."<br>
	<input type='hidden' id='ExcludeURLExplain' value='{ExcludeURLExplain}'>
	<input type='hidden' id='ExcludeMimeTypeExplain' value='{ExcludeMimeTypeExplain}'>
	$main
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');			
	
}


function main_rules_switch(){
	switch ($_GET["sec2"]) {
		case 0:main_rules_group($_GET["gid"]);break;
		case 1:main_rules_group_exclude($_GET["gid"]);break;
		case 2:main_rules_group_scanner($_GET["gid"]);break;
		default:main_rules_group($_GET["gid"]);break;
	}
	
	
}

function main_events(){
$page=CurrentPageName();	
$main=main_tabs()."<H5>{events}</H5>
<br>
<iframe style='width:100%;height:500px;border:0px' src='$page?events=yes'></iframe>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($main,'milter.index.php');			
		
	//kav4proxy_events 
}

function main_events_page(){
	$sock=new sockets();
	$datas=$sock->getfile("kav4proxy_events");
	$tbl=split("\n",$datas);
	
	if(!is_array($tbl)){
		echo iframe(null,20);exit;
	}
	
	$html="<table style='width:100%'>
	<tr>
	
	<th colspan=2>{time}</th>
	<th>{client}</th>
	<th>URL</th>
	<th>{size}</th>
	<th>{virus}</th>
	</tr>
	
	";
	$tbl=array_reverse ($tbl, TRUE);	
	while (list ($num, $ligne) = each ($tbl) ){
		
		if(preg_match('#([0-9\-]+)\s+([0-9:]+)\s+([0-9]+)\s+([A-Z]+)\s+([0-9\.]+)\s+(.+)#',$ligne,$reg)){
			$date=$reg[1];
			$time=$reg[2];
			$size=$reg[3];
			$results=$reg[4];
			$IP=$reg[5];
			$uri=$reg[6];
			$virus="&nbsp;";
		}else{
			if(preg_match('#([0-9\-]+)\s+([0-9:]+)\s+([0-9]+)\s+([A-Z]+)\s+(.+?)\s+([0-9\.]+)\s+(.+)#',$ligne,$reg)){
				$date=$reg[1];
				$time=$reg[2];
				$size=$reg[3];
				$results=$reg[4];
				$virus=$reg[5];
				$IP=$reg[6];
				$uri=$reg[7];				
					
			}
			
		}
		
		if($uri<>null){
		$a="<a href='$uri' target='_new'>";	
		$uri_=substr($uri,0,50) . "...";
		$uri2=wordwrap($uri,50,'<br>',1);
		$md=md5($ligne);
		$html=$html . "
		<tr>
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td valign='top'>$time</tD>
		<td valign='top'>$IP</td>
		<td valign='top' width=1% 
			OnMouseOver=\"javascript:document.getElementById('$md').innerHTML='$uri2'\"
			OnMouseOut=\"javascript:document.getElementById('$md').innerHTML='$uri_'\"
		>$a<span id='$md'>$uri_</span></a></td>
		<td nowrap>$size bytes</td>
		<td valign='top'>$virus</td>
		</tr>
		
		";}
		
		
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	
	echo iframe($html,20,'500px');
	
}


function main_rules_add(){
	$kav=new kav4proxy();
	$kav->AddNewGroup($_GET["kav4proxy_addnewgroup"]);
	
}

function main_rules_group_tabs($gidnumber){
	if(!isset($_GET["sec2"])){$_GET["sec2"]=0;};
	$page=CurrentPageName();
	$array[]='{rule_flow}';
	$array[]='{excludes}';
	$array[]='{scan_options}';

	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["sec2"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('group_data','kav4proxy.index.php?sec2=$num&gid=$gidnumber');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
	
	
}

function main_rules_group_addip(){
	$kav=new kav4proxy();
	$_POST["rule"]=stripslashes($_POST["rule"]);
	$kav->AddNewClientIP($_POST["Kav4ProxyAddClientIP"],$_POST["rule"]);
	$kav->SaveToLdap();
	}
	
function main_rules_group_addExcludeURL(){
	$kav=new kav4proxy();
	$_POST["rule"]=stripslashes($_POST["rule"]);
	$kav->AddNewClientExcludeURL($_POST["Kav4ProxyAddExcludeURL"],$_POST["rule"]);
	$kav->SaveToLdap();
}

function main_rules_group_addExcludeMime(){
	$kav=new kav4proxy();
	$_POST["rule"]=stripslashes($_POST["rule"]);
	$kav->AddNewClientExcludeMimeType($_POST["Kav4ProxyAddExcludeMime"],$_POST["rule"]);
	$kav->SaveToLdap();
}
function main_rules_group_delExcludeMime(){
	$kav=new kav4proxy();
	$kav->DeleteClientExcludeMimeType($_POST["Kav4ProxyDeleteExcludeMime"],$_POST["rule"]);
}
function main_rules_group_delExcludeURL(){
	$kav=new kav4proxy();
	$kav->DeleteClientExcludeURL($_POST["Kav4ProxyDeleteExcludeURL"],$_POST["rule"]);	
}
	
function main_rules_group_delip(){
	$kav=new kav4proxy();
	$kav->DeleteClientIP($_POST["Kav4ProxyDeleteClientIP"],$_POST["rule"]);
}
function main_rules_group_deluri(){
	$kav=new kav4proxy();
	$kav->DeleteClientURL($_POST["Kav4ProxyDeleteURL"],$_POST["rule"]);	
	}

function main_rules_group_adduri(){
	$kav=new kav4proxy();
	$_POST["rule"]=stripslashes($_POST["rule"]);
	$kav->AddNewClientURL($_POST["Kav4ProxyAddClientURL"],$_POST["rule"]);
	$kav->SaveToLdap();
	}
function main_rules_group_move(){
	$kav=new kav4proxy();
	$number=$_GET["Kav4ProxyMoveGroup"];
	$move=$_GET["move"];
	$ldap=new clladp();
	$res=@ldap_read($ldap->ldap_connection,$kav->dn,"(objectClass=*)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			for($i=0;$i<$hash[0][strtolower('kav4proxygroupsconf')]["count"];$i++){
					$array[$i]=$hash[0][strtolower('kav4proxygroupsconf')][$i];
			}
		}
	
	$new=array_move_element($array,$array[$number],$move);
	while (list ($num, $ligne) = each ($new) ){
		$upd["Kav4ProxyGroupsConf"][]=$ligne;
	}
	
	$ldap->Ldap_modify($kav->dn,$upd);
	$kav=new kav4proxy();
	$kav->SaveToLdap();
	
	
}
	
function main_rules_group_editactions(){
	$kav=new kav4proxy();
	$gid=$_GET["gid"];
	$groupname=$kav->main_groups[$gid]["name"];
	while (list ($num, $ligne) = each ($_GET) ){
		$hash=$kav->main_groups[$gid]["main"]["icapserver.actions:$groupname"][$num]=$ligne;
	}
		$kav->SaveToLdap();	
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
		
}

function main_rules_group_del(){
	$kav=new kav4proxy();
	$number=$_POST["Kav4ProxyDeleteGroup"];
	$ldap=new clladp();
	$res=@ldap_read($ldap->ldap_connection,$kav->dn,"(objectClass=*)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			for($i=0;$i<$hash[0][strtolower('kav4proxygroupsconf')]["count"];$i++){
					$array[$i]=$hash[0][strtolower('kav4proxygroupsconf')][$i];
			}
		}
	writelogs("Delete group number $number",__FUNCTION__,__FILE__);
	$upd["Kav4ProxyGroupsConf"]=$array[$number];
	$ldap->Ldap_del_mod($kav->dn,$upd);
	$kav=new kav4proxy();
	$kav->SaveToLdap();	
	
}

function main_stats(){
		include_once('ressources/charts.php');
	$page=CurrentPageName();
	$users=new usersMenus();
	$kav=new kav4proxy();
	$hash=$kav->BuildStatistics();

	$tpl=new templates();
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?kav4proxy=viruses",300,250,"",true,$users->ChartLicence);	
	$graph2=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?kav4proxy=perf",300,250,"",true,$users->ChartLicence);	
	
$html=main_tabs() . "<br>
	<form name='ffm1'>
	<H5>{statistics}</H5>
	<table style='width:100%'>
	<tr>
	<td colspan=2>
		<table style='width:100%'>
		<tr>
			<td>{requests_per_min}</td>
			<td><strong>{$hash["requests_per_min"]}</td>
			<td>{traffic_per_min}</td>
			<td><strong>{$hash["traffic_per_min"]}</td>			
			<td>{total_connections}</td>
			<td><strong>{$hash["total_connections"]}</td>				
			<td>{total_processes}</td>
			<td><strong>{$hash["total_processes"]}</td>				
			<td>{idle_processes}</td>
			<td><strong>{$hash["idle_processes"]}</td>
		</tr>
		</table>			
			
		</tr>
		
	
	<td valign='top'><h5>Uris</H5>	$graph1</td>
	<td valign='top'><h5>{flow}</H5>	$graph2</td>
	</tr>
	</table>";

	
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');
	
}


function main_license_form_key(){
	$tpl=new templates();
	$page=CurrentPageName();
	return $tpl->_ENGINE_parse_body("<H5>{add_a_license}<iframe src='$page?iframe_addkey=yes' width=100% height=350px style='border:0px'></iframe>",'milter.index.php');
	}
	
function main_license_LicenceUploaded(){
		$tmp_file = $_FILES['fichier']['tmp_name'];
	
	writelogs("tmp_file=$tmp_file",__FUNCTION__,__FILE__);
	
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){main_license_Addkey_form('{error_unable_to_upload_file}');exit();}
	
	 $type_file = $_FILES['fichier']['type'];
	  if( !strstr($type_file, 'key')){	main_license_Addkey_form('{error_file_extension_not_match} :key');	exit();}
	 $name_file = $_FILES['fichier']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){main_license_Addkey_form("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    include_once("ressources/class.sockets.inc");
    $socket=new sockets();
 	$res=$socket->getfile("kav4proxy_licencemanager:$content_dir/$name_file");
	 $res=str_replace("\r","",$res);
	 $res=str_replace("Error registering keyfile","<strong style='color:red'>Error registering keyfile</strong>",$res);
	 
 	 $res=wordwrap($res,40,"\n",true);
 	 $res=nl2br($res);
 	 main_license_Addkey_form($res);	
	
	
}
	
function main_license_Addkey_form($error=null){
	$tpl=new templates();
	
	if($error<>null){$error="<br>".RoundedLightGrey($error)."<br>";}
	
	$form="
	<p>{kav4proxy_licence_text}</p>
	<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
	<p>
	<input type=\"file\" name=\"fichier\" size=\"30\">
	<div style='text-align:right;width:100%'><input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:190px'></div>
	</p>
	</form>

	";
	$form=$error .RoundedLightGreen($form);
	
$html="<html>
		
		<head>$tpl->head
			<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
			
		</head><body style='margin:0px;padding:0px;background-color:#FFFFFF'><br>$form</body></html>";		
	
	
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');	
}

function main_license(){
	$sock=new sockets();
	$license_data=base64_decode($sock->getFrameWork('Kav4ProxyLicense'));
	$license_data=htmlentities($license_data);
	$license_data=nl2br($license_data);
	$license_data=str_replace("<br />\n<br />","<br />",$license_data);
	$license_data=str_replace("License info:","<strong style='font-size:12px'>License info:</strong>",$license_data);
	$license_data=str_replace("Active key info:","<strong style='font-size:12px'>Active key info:</strong>",$license_data);
	$license_data=str_replace("Expiration date","<strong style='color:red'>Expiration date</strong>",$license_data);
	$license_data=str_replace("Kaspersky license manager for Linux","<H6 style='margin-top:0px'>Kaspersky license manager for Linux</H6>",$license_data);
	
	
	$license_data=RoundedLightGreen($license_data);
	$add_key=
	
	
	
	$html=main_tabs() . "<br>
	<form name='ffm1'>
	<H5>{licenseproxy}</H5>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<div id='license_data'>	
	$license_data
	</div>
	</td>
	<td valign='top'>
	" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td>".Paragraphe('add-key-64.png','{add_a_license}','{add_a_license_text}',"javascript:kav4proxy_add_key()") ."</td>
	</tr>
	</table>")."<br>
	
	" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td>".Paragraphe('shopping-cart-64.png','{by_a_license}','{by_a_license_text}',"javascript:MyHref(\"http://www.kaspersky.com/buy_kaspersky_security_internet_gateway\")") ."</td>
	</tr>
	</table>")."<br>
	
	</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'milter.index.php');
	
}



?>
	