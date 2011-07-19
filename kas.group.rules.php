<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.kas-filter.inc');
	
	
$usersmenus=new usersMenus();
$artica=new artica_general();
if($usersmenus->AllowChangeKas==false){die('No permissions');exit;}
if(isset($_GET["main"])){switch_tabs();exit;}
if(isset($_GET["SAVE_KAS"])){SAVEPOST();exit;}
if(isset($_GET["ajax"])){kas_js();exit;}
if(isset($_GET["ajax-pop"])){kas_pop();exit;}
if(isset($_POST["rebuildtables"])){kas_rebuild_tables();exit;}


INDEX_KAS();

function kas_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_KAS3}');
	$datas=file_get_contents("js/kas.js");
	$html="
	$datas
	YahooWin(500,'$page?ajax-pop=yes','$title');
	
	";
	
	echo $html;
	
	
}

function kas_pop_Selectedpage(){
	include_once('kas-tabs.php');
	
	
	if($_GET["kasSelectedpage"]=="actions"){$INDEX_KAS=INDEX_KAS_GROUPS(1);}else{
		$INDEX_KAS=INDEX_KAS(1);
	}
	
	$html="<H1>{antispam_rules}</H1>
	
	".INDEX_KAS(1);
	
	if(!isset($_GET["nodiv"])){
		$html="<div id='global_kas_pages'>$html</div>";
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function kas_pop(){
	
	$tpl=new templates();
	$confirm_rebuild=$tpl->javascript_parse_text("{confirm_rebuild}");
	if(isset($_GET["kasSelectedpage"])){kas_pop_Selectedpage();exit;}
	
	$sock=new sockets();
	$kasversion=unserialize(base64_decode($sock->getFrameWork("cmd.php?kasversion=yes")));
	
	$ou=base64_encode("default");
	$page=CurrentPageName();
	$antispam_engine=Paragraphe("folder-performances-64.png","{antispam_engine}","{antispam_engine_text}",
	"javascript:YahooWin2(500,'kas-tabs.php?kaspages=antispam_engine');");
	
	$global_rules=Paragraphe("folder-rules2-64.png","{antispam_rules}","{antispam_rules_text}",
	"javascript:Loadjs('domains.edit.kas.php?ou=$ou');");
	
	$licenses=Paragraphe("64-key.png","{statusandlicense}","{statusandlicense_text}",
	"javascript:Loadjs('squid.newbee.php?kav-license=yes&license-type=kas');");	
	
	$pattern_status=Paragraphe('pattern-database-64.png','{antivirus_database}',"{date}:<b>{$kasversion["pattern"]}</b><hr>{size}:<b>{$kasversion["size"]}</b>");
	
	$update_kaspersky=Paragraphe('kaspersky-update-64.png','{UPDATE_KAS3}','{UPDATE_KAS3_TEXT}',"javascript:Loadjs('squid.newbee.php?update-kav=yes&type=kas')");
	
	$rebuilddb=Paragraphe('64-troubleshoot-rebuild.png','{rebuild_tables}','{rebuild_tables}',"javascript:rebuild_kas_tables()");
	
	
	$html="
	<div id='ebuild_kas_tables_id'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$antispam_engine</td>
		<td valign='top'>$global_rules</td>
	</tr>
	<tr>
		<td valign='top'>$licenses</td>
		<td valign='top'>$pattern_status</td>
	</tr>
	<tr>
	<tr>
		<td valign='top'>$update_kaspersky</td>
		<td valign='top'>$rebuilddb</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	var x_rebuild_kas_tables=function (obj) {
			var results=obj.responseText;
			if(results.length>10){alert(results);}			
			
		}	
		
		function rebuild_kas_tables(){
			if(confirm('$confirm_rebuild')){
				var XHR = new XHRConnection();
				XHR.appendData('rebuildtables','yes');
    			XHR.sendAndLoad('$page', 'POST',x_rebuild_kas_tables);
			}
		}	

	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);	
exit;
}

function kas_rebuild_tables(){
	$q=new mysql();
	$sql="DROP TABLE kas3";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$q->BuildTables();
	
}


function INDEX_KAS($noecho=0){
$page=CurrentPageName();
$general=section_general();
$html="
<form name='FFM1'>
<input type='hidden' name='SAVE_KAS' value='YES' id='SAVE_KAS'>
<div id='main_kas3'>
".section_general()."
</div>
</form>
</div>";
if($noecho==1){
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

$tpl=new template_users('{antispam_rules}',$html);
echo $tpl->web_page;	
	
}



function switch_tabs(){
	switch ($_GET["main"]) {
		case "section_general":echo section_general();break;
		case "dns_spf":echo dns_spf();break;
		case "Headers_Checks":echo Headers_Checks();break;
		case "eastern_encodings":echo eastern_encodings();break;
	
		default:
			break;
	}
	
	
}




function _tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="section_general";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["section_general"]='{general}';
	$array["dns_spf"]='{DNS_SPF}';
	$array["Headers_Checks"]='{Headers_Checks}';
	$array["eastern_encodings"]='{eastern_encodings}';

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_kas3','$page?popup=yes&main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";		
}

function eastern_encodings(){
$tab=_tabs();	
$kas=new kas_single();
$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
$OPT_SPAM_RATE_LIMIT=Field_array_Hash($OPT_SPAM_RATE_LIMIT_TABLE,'OPT_SPAM_RATE_LIMIT',$kas->main_array["OPT_SPAM_RATE_LIMIT"]);
$page=CurrentPageName();	
	
$html="$tab<H5>{eastern_encodings}</H5>
<table style=width:100% class=table_form>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_KOREAN}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_KOREAN',$kas->main_array["OPT_LANG_KOREAN"],'{enable_disable_kas}') . "</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_CHINESE}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_CHINESE',$kas->main_array["OPT_LANG_CHINESE"],'{enable_disable_kas}') . "</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_JAPANESE}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_JAPANESE',$kas->main_array["OPT_LANG_JAPANESE"],'{enable_disable_kas}') . "</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_THAI}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_THAI',$kas->main_array["OPT_LANG_THAI"],'{enable_disable_kas}') . "</td>
	<td>&nbsp;</td>
</tr>
<tr><td colspan=3 align=right><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','$page',true);\"></td></tr>
</table>";


$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}


function Headers_Checks(){
$tab=_tabs();	
$kas=new kas_single();
$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
$OPT_SPAM_RATE_LIMIT=Field_array_Hash($OPT_SPAM_RATE_LIMIT_TABLE,'OPT_SPAM_RATE_LIMIT',$kas->main_array["OPT_SPAM_RATE_LIMIT"]);
$page=CurrentPageName();	
	
$html="	$tab
<H5>{Headers_Checks}</H5>
<table style=width:100% class=table_form>
<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_TO_UNDISCLOSED}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_TO_UNDISCLOSED',$kas->main_array["OPT_HEADERS_TO_UNDISCLOSED"],'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_TO_UNDISCLOSED_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{HEADERS_FROM_OR_TO_DIGITS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('HEADERS_FROM_OR_TO_DIGITS',$kas->main_array["HEADERS_FROM_OR_TO_DIGITS"],'{enable_disable}') . "</td>
	<td>{HEADERS_FROM_OR_TO_DIGITS_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{HEADERS_FROM_OR_TO_NO_DOMAIN}:</strong></td>
	<td>" . Field_numeric_checkbox_img('HEADERS_FROM_OR_TO_NO_DOMAIN',$kas->main_array["OPT_HEADERS_FROM_OR_TO_NO_DOMAIN"],'{enable_disable}') . "</td>
	<td>{HEADERS_FROM_OR_TO_NO_DOMAIN_TEXT}</td>
</tr>


<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_SUBJECT_TOO_LONG}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_SUBJECT_TOO_LONG',$kas->main_array["OPT_HEADERS_SUBJECT_TOO_LONG"],'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_SUBJECT_TOO_LONG_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_SUBJECT_WS_OR_DOTS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_SUBJECT_WS_OR_DOTS',$kas->main_array["OPT_HEADERS_SUBJECT_WS_OR_DOTS"],'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_SUBJECT_WS_OR_DOTS_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID',$kas->main_array["OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID"],'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID_TEXT}</td>
</tr>
<tr><td colspan=3 align=right><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','$page',true);\"></td></tr>
</table>";

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}


function dns_spf(){

$tab=_tabs();	
$kas=new kas_single();
$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
$OPT_SPAM_RATE_LIMIT=Field_array_Hash($OPT_SPAM_RATE_LIMIT_TABLE,'OPT_SPAM_RATE_LIMIT',$kas->main_array["OPT_SPAM_RATE_LIMIT"]);
$page=CurrentPageName();	
	
$html="	$tab
<H5>{DNS_SPF}</H5>
<table style='width:100%' class=table_form>
<tr>
	<td align='right' nowrap class=legend>{OPT_DNS_DNSBL}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_DNS_DNSBL',$kas->main_array["OPT_DNS_DNSBL"],'{enable_disable}') . "</td>
	<td>{OPT_DNS_DNSBL_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_DNS_HOST_IN_DNS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_DNS_HOST_IN_DNS',$kas->main_array["OPT_DNS_HOST_IN_DNS"],'{enable_disable}') . "</td>
	<td>{OPT_DNS_HOST_IN_DNS_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_SPF}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_DNS_DNSBL',$kas->main_array["OPT_SPF"],'{enable_disable}') . "</td>
	<td>{OPT_SPF_TEXT}</td>
</tr>
<tr><td colspan=3 align=right><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','$page',true);\"></td></tr>
</table>
";	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}

function section_general(){
$tab=_tabs();	
$kas=new kas_single();
$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
$OPT_SPAM_RATE_LIMIT=Field_array_Hash($OPT_SPAM_RATE_LIMIT_TABLE,'OPT_SPAM_RATE_LIMIT',$kas->main_array["OPT_SPAM_RATE_LIMIT"]);
$page=CurrentPageName();	


$html="	$tab
<H5>{general}</h5>

<table style='width:100%' class=table_form>
<tr>
	<td align='right' nowrap class=legend>{OPT_FILTRATION_ON}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_FILTRATION_ON',$kas->main_array["OPT_FILTRATION_ON"],'{enable_disable}') . "</td>
	<td>{OPT_FILTRATION_ON_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_SPAM_RATE_LIMIT}:</strong></td>
	<td>$OPT_SPAM_RATE_LIMIT</td>
	<td>{OPT_SPAM_RATE_LIMIT_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_PROBABLE_SPAM_ON}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_PROBABLE_SPAM_ON',$kas->main_array["OPT_PROBABLE_SPAM_ON"],'{enable_disable}') . "</td>
	<td>{OPT_PROBABLE_SPAM_ON_TEXT}</td>
</tr>

<tr>
	<td align='right' nowrap class=legend>{OPT_USE_DNS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_USE_DNS',$kas->main_array["OPT_USE_DNS"],'{enable_disable}') . "</td>
	<td>{OPT_USE_DNS_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_USE_SURBL}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_USE_SURBL',$kas->main_array["OPT_USE_SURBL"],'{enable_disable}') . "</td>
	<td>{OPT_USE_SURBL_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{ACTION_SPAM_SUBJECT_PREFIX}:</strong></td>
	<td colspan=2>" . Field_text('ACTION_SPAM_SUBJECT_PREFIX',$kas->ACTION_SPAM_SUBJECT_PREFIX,'width:150px') . "</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{ACTION_PROBABLE_SUBJECT_PREFIX}:</strong></td>
	<td colspan=2>" . Field_text('ACTION_PROBABLE_SUBJECT_PREFIX',$kas->ACTION_PROBABLE_SUBJECT_PREFIX,'width:150px') . "</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{ACTION_BLACKLISTED_SUBJECT_PREFIX}:</strong></td>
	<td colspan=2>" . Field_text('ACTION_BLACKLISTED_SUBJECT_PREFIX',$kas->ACTION_BLACKLISTED_SUBJECT_PREFIX,'width:150px') . "</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{ACTION_FORMAL_SUBJECT_PREFIX}:</strong></td>
	<td colspan=2>" . Field_text('ACTION_FORMAL_SUBJECT_PREFIX',$kas->ACTION_FORMAL_SUBJECT_PREFIX,'width:150px') . "</td>
</tr>





<tr><td colspan=3 align=right><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','$page',true);\"></td></tr>
</table>"; 	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}



function SAVEPOST(){
$kas=new kas_single();

$kas->ACTION_SPAM_SUBJECT_PREFIX=$_GET["ACTION_SPAM_SUBJECT_PREFIX"];
$kas->ACTION_PROBABLE_SUBJECT_PREFIX=$_GET["ACTION_PROBABLE_SUBJECT_PREFIX"];
$kas->ACTION_BLACKLISTED_SUBJECT_PREFIX=$_GET["ACTION_BLACKLISTED_SUBJECT_PREFIX"];
$kas->ACTION_FORMAL_SUBJECT_PREFIX=$_GET["ACTION_FORMAL_SUBJECT_PREFIX"];

unset($_GET["SAVE_KAS"]);
	while (list ($num, $val) = each ($_GET) ){
		$kas->main_array[$num]=$val;
	
	}
	$kas->Save();
	
	
}


