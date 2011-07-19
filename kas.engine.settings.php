<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once(dirname(__FILE__).'/ressources/class.kas-filter.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["TreeKas3SaveSettings"])){SaveSettings();exit;}
if(isset($_GET["DNS_HOSTNAME"])){DnsBlackListAdd();exit;}
if(isset($_GET["DnsBlackListEdit"])){DnsBlackListEdit();exit;}
if(isset($_GET["DnsBlackListDelete"])){DnsBlackListDelete();exit;}
if(isset($_GET["kasinfos"])){RightInfos();exit;}
if(isset($_GET["ajax-pop"])){popup();exit;}




switch ($_GET["page"]) {
	case 1:$page=PageKas3ProcessServer();break;
	case 2:$page=PageKas3DNSBlackSettings();break;
	default:$page=PageKas3MTAClientsSettings();break;
}
$JS["JS"][]="js/kas.js";
$tpl=new template_users('{antispam_engine}',$page,0,0,0,0,$JS);
echo $tpl->web_page;


function popup(){
	include_once('kas-tabs.php');
	$page=GetPage();
	if($page<>null){echo $page;exit;}
	$page=PageKas3MTAClientsSettings();	

	$html="<H1>{antispam_engine}</H1>
	<div id='global_kas_pages' style='width:100%;height:400px;overflow:auto'>$page</div>";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function GetPage(){
	if($_GET["page"]==null){return null;}
	
switch ($_GET["page"]) {
	case 1:return PageKas3ProcessServer();break;
	case 2:return PageKas3DNSBlackSettings();break;
	case 0:return PageKas3MTAClientsSettings();break;
	
}	
return $page;	
	
}


function PageKas3ProcessServer(){
			$page="kas.engine.settings.php";
			$yum=new usersMenus();
			$tpl=new  templates();
			if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
			$kas=new kas_filter();
			$arrayyesno=array("yes"=>"yes","no"=>"no");	
			$FilterParseMSOffice=Field_yesno_checkbox_img('FilterParseMSOffice',$kas->array_datas["FilterParseMSOffice"]);
			$FilterUDSEnabled=Field_yesno_checkbox_img('FilterUDSEnabled',$kas->array_datas["FilterParseMSOffice"]);
			if(isset($_GET["nodiv"])){$tabs=ajaxtabs();}else{$tabs=PageTabs();}
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$tabs
		<form name='kas3S'>
			<input type='hidden' name='TreeKas3SaveSettings' value='yes'>
			
				<H5>{Process Server Settings}</H5>
			
				<table style='width:100%' class=table_form>
				<tr>
					<td align='right'>{Max. number of filtration processes}:</td>
					<td><input type='text' id='ServerMaxFilters' name=ServerMaxFilters value='{$kas->array_datas["ServerMaxFilters"]}' style='width:100px'></td>
				</tr>
				<tr>
					<td align='right'>{Number of filtration processes at server start-up}:</td>
					<td><input type='text' id='ServerStartFilters' name=ServerStartFilters value='{$kas->array_datas["ServerStartFilters"]}' style='width:100px'></td>
				</tr>	
				<tr>
					<td align='right'>{Number of spare filtration processes}:</td>
					<td><input type='text' id='ServerSpareFilters' name=ServerSpareFilters value='{$kas->array_datas["ServerSpareFilters"]}' style='width:100px'></td>
				</tr>
				</table>
		
					<h5>{Check Options}</h5>
				<table style='width:100%' class=table_form>
				<tr >
					<td align='right'>{Number of Received headers to be parsed while retrieving ip address}:</td>
					<td><input type='text' id='FilterReceivedHeadersLimit' name=FilterReceivedHeadersLimit value='{$kas->array_datas["FilterReceivedHeadersLimit"]}' style='width:100px'></td>
				</tr>
				<tr>
					<td align='right'>{Overall timeout of all DNS requests}:</td>
					<td><input type='text' id='FilterDNSTimeout' name=FilterDNSTimeout value='{$kas->array_datas["FilterDNSTimeout"]}' style='width:100px'></td>
				</tr>
				<tr>
					<td align='right'>{Check MS Word and RTF files}:</td>
					<td>$FilterParseMSOffice</td>
				</tr>
				<tr>
					<td align='right'>{UDS_enabled}:</td>
					<td>$FilterUDSEnabled</td>
				</tr>
				<tr >
					<td align='right'>{Timeout for receiving response from UDS server}:</td>
					<td><input type='text' id='FilterUDSTimeout' name=FilterUDSTimeout value='{$kas->array_datas["FilterUDSTimeout"]}' style='width:100px'></td>
				</tr>			
				</table>
					
		
		<h5>{Filtration Process}</h5>
				<table style='width:100%' class=table_form>
					<tr>
						<td align='right'>{Max. number of mail messages to be processed}:</td>
						<td><input type='text' id='FilterMaxMessages' name=FilterMaxMessages value='{$kas->array_datas["FilterMaxMessages"]}' style='width:100px'></td>
					</tr>
					<tr>	
						<td align='right'>{Max. number of mail messages randomization}:</td>
						<td><input type='text' id='FilterRandMessages' name=FilterRandMessages value='{$kas->array_datas["FilterRandMessages"]}' style='width:100px'></td>
					</tr>	
					<tr>	
						<td align='right'>{Max_idle_time_in_seconds}:</td>
						<td><input type='text' id='FilterMaxIdle' name=FilterMaxIdle value='{$kas->array_datas["FilterMaxIdle"]}' style='width:100px'></td>
					</tr>
					<tr>	
						<td align='right'>{Exit_delay_in_seconds}:</td>
						<td><input type='text' id='FilterDelayedExit' name=FilterDelayedExit value='{$kas->array_datas["FilterDelayedExit"]}' style='width:100px'></td>
					</tr>	
													
				
					
				</table>
				</form>
			<div align='rigth' style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:ParseForm('kas3S','$page',true);\" value='{save parameters}&nbsp;&raquo;'></div><br>
			</td>
			</tr>
			</table>";
				
		
		return $tpl->_ENGINE_parse_body($html);
	}
	
function PageKas3DNSBlackSettings(){	
		         $tpl=new templates();
			$yum=new usersMenus();
			$kas=new kas_dns();
			$page=CurrentPageName();
			$main=new main_cf();
			$main=new smtpd_restrictions();
			$field=Field_array_Hash($main->dnsrbl_database["RBL"],'DNS_HOSTNAME');
			if(isset($_GET["nodiv"])){$tabs=ajaxtabs();}else{$tabs=PageTabs();}
			
$html="
$tabs
<form name='kas3S'>
<center>";

$table="<table style='width:80%;;padding:4px;'>
				<tr style='background-color:#005447'>
				<th colspan=5>{DNS_BLACK_LIST_TEXT}</th>
				</tR>
				<tr style='background-color:#005447'>
				<td>&nbsp;</td>
				<td style='color:white'><strong>{DNS_HOST}</strong></td>
				<td style='color:white' width=10% nowrap><strong>{DNS_RATE}</strong></td>
				<td style='color:white' nowrap>&nbsp;</td>
				<td style='color:white' nowrap>&nbsp;</td>
				</tr>";
		
			if(is_array($kas->array_datas)){
				
				while (list ($num, $line) = each ($kas->array_datas) ){
					$table=$table ."<tr>
					<td class=bottom width=1%><img src='img/icn_machinesList.gif'></td>
					<td class=bottom style='font-size:12px;padding:4px'>$num</td>
					<td class=bottom style='font-size:12px;padding:4px'>" . Field_text($num,$line)."</td>
					<td class=bottom style='font-size:12px;padding:4px'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EditKasDnsBlackList('$num');\"></td>
					<td class=bottom style='font-size:12px;padding:4px'>" . imgtootltip('x.gif','{delete}',"KasDnsBlackListDelete('$num');")."</td>
					</tr>";
					
				}
				
			}
			
		$table=$table . "<tr>
			<td width=1%><img src='img/add-cube.gif'></td>
			<td style='font-size:12px;padding:4px'>$field</td>
			<td style='font-size:12px;padding:4px'>" . Field_text('DNS_RATE')."</td>
			<td style='font-size:12px;padding:4px' colspan=2><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:EditKasDnsBlackListAdd();\"></td>
			</tr>
			</table>";

			
			$html=$html. RoundedLightWhite($table)."</center>";
		return  $tpl->_ENGINE_parse_body($html);
}
	
function DnsBlackListAdd(){
	if(!is_numeric($_GET["DNS_RATE"])){$_GET["DNS_RATE"]=0;}
	if($_GET["DNS_RATE"]>100){$_GET["DNS_RATE"]=100;}	
	$kas=new kas_dns();
	$kas->array_datas[$_GET["DNS_HOSTNAME"]]=$_GET["DNS_RATE"];
	$kas->SaveDatas();
	}
function DnsBlackListEdit(){
	if(!is_numeric($_GET["DNS_RATE"])){$_GET["DNS_RATE"]=0;}
	if($_GET["DNS_RATE"]>100){$_GET["DNS_RATE"]=100;}	
	$kas=new kas_dns();	
	$kas->array_datas[$_GET["DnsBlackListEdit"]]=$_GET["DNS_RATE"];
	$kas->SaveDatas();
}
function DnsBlackListDelete(){
$kas=new kas_dns();	
	unset($kas->array_datas[$_GET["DnsBlackListDelete"]]);
	$kas->SaveDatas();	
	
}

function ajaxtabs(){
	if(!isset($_GET["page"])){$_GET["page"]="0";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["1"]='{Process Server Settings}';
	$array["0"]='{MTA Clients Settings}';
	//$array["2"]='{DNS_BLACK_LIST}';
	

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["page"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('global_kas_pages','kas.engine.settings.php?page=$num&nodiv=yes&ajax-pop=yes&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";		
}

function PageTabs(){
	
if(!isset($_GET["page"])){$_GET["page"]="0";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["1"]='{Process Server Settings}';
	$array["0"]='{MTA Clients Settings}';
	//$array["2"]='{DNS_BLACK_LIST}';
	

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["kaspages"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"kas.engine.settings.php?page=$num\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";			
	
}

function PageKas3MTAClientsSettings(){
		         $tpl=new templates();
			$yum=new usersMenus();
			$kas=new kas_filter();
			$page=CurrentPageName();
			
			
		$array_ClientOnError=array(
			'accept'=>"{accept message}",
			'reject'=>"{reject message}",
			'tempfail'=>"generate temporary error");	
		$ClientOnError=Field_array_Hash($array_ClientOnError,'ClientOnError',$kas->array_datas["ClientOnError"]);
	
		if(isset($_GET["nodiv"])){$tabs=ajaxtabs();}else{$tabs=PageTabs();}
		
		$html="
				$tabs
	<form name='kas3S'>
	<input type='hidden' name='TreeKas3SaveSettings' value='yes'>		
		" . RoundedLightWhite("
			<table width='100%'>
			<tr>
				<td align='right'><b>{Filtering size limit}:</b></td>
				<td valign='top'><input type='text' id='ClientFilteringSizeLimit' value='{$kas->array_datas["ClientFilteringSizeLimit"]}' style='width:100px'></td>
			</tr>
			<tr>
			<td colspan=2><i>{ClientFilteringSizeLimit}</i></td>
			<tr>
				<td align='right'><b>{On filtering error}:</b></td>
				<td>$ClientOnError</td>
			</tr>
			<tr>
			<td colspan=2><i>{ClientOnError}</i></td>
			<tr>			
			<tr>
				<td align='right'><b>{Default domain}:</b></td>
				<td><input type='text' id='ClientDefaultDomain' value='{$kas->array_datas["ClientDefaultDomain"]}' style='width:80%'></td>
			</tr>	
			<tr>
			<td colspan=2><i>{ClientDefaultDomain}</i></td>
			<tr>				
			<tr>
				<td align='right'><b>{Connection timeout}:</b></td>
				<td><input type='text' id='ClientDataTimeout' value='{$kas->array_datas["ClientConnectTimeout"]}' style='width:80%'></td>
			</tr>	
			<tr>
			<td colspan=2><i>{ClientConnectTimeout}</i></td>
			<tr>										
			<tr>
				<td align='right'><b>{Data exchange timeout}:</b></td>
				<td><input type='text' id='ClientDataTimeout' value='{$kas->array_datas["ClientDataTimeout"]}' style='width:80%'></td>
			</tr>							
			<tr>
			<td colspan=2><i>{ClientDataTimeout}</i></td>
			<tr>		
			
			
					
			</table>") ."
			<div align='rigth' style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:ParseForm('kas3S','$page',true);\" value='{save parameters}&nbsp;&raquo;'></div><br>
			";
		
		return  $tpl->_ENGINE_parse_body($html);
	}

function SaveSettings(){
	
unset($_GET["TreeKas3SaveSettings"]);
$kas=new kas_filter();
while (list ($key, $value) = each ($_GET) ){
	$kas->array_datas[$key]=$value;
}
$kas->SaveConf();
	
	
}

function RightInfos(){

	
$kas=new kas_filter();
$st=RoundedLightGrey($kas->KasStatus() . "</table>");

$html=applysettings("kas") ."<br>$st";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}

?>