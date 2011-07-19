<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
		
	
	if(isset($_GET["show"])){domain_amavis_front();exit;}
	if(isset($_GET["amavisSpamLover"])){SaveAmavisConfig();exit;}
	if(isset($_GET["GoBackDefaultAmavis"])){GoBackDefaultAmavis();exit;}
	if(isset($_GET["amavis_bypass_rcpt"])){amavis_bypass_rcpt();exit;}
	
js();



function js(){
	
	$tpl=new templates();
	$sock=new sockets();
	$EnableLDAPAmavis=$sock->GET_INFO("EnableLDAPAmavis");
	if(!is_numeric($EnableLDAPAmavis)){$EnableLDAPAmavis=0;}
	$domain=$_GET["domain"];
	
	
	$translate_page="amavis.index.php";
	$page=CurrentPageName();

	$title=$domain." ::{spam_rules}";
	$title=$tpl->_ENGINE_parse_body($title,$translate_page);
	$start="LoadDomainAmavis();";
	if(isset($_GET["bytabs"])){
		$prefix="
		<div id='amavis-domain-ou' style='width:100%'></div>
		<script>";
		$start="LoadDomainAmavis2();";
		$prefix2="</script>";
	}
	
	$html="
	$prefix
	function LoadDomainAmavis(){
		YahooWin2(750,'$page?show=yes&domain=$domain','$title');
	}
	
	function LoadDomainAmavis2(){
		LoadAjax('amavis-domain-ou','$page?show=yes&domain=$domain');
	}
	
	
	var x_SaveDomainAmavis= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				$start;	
			}			
			
	
	function SaveUserAmavis(){
		var XHR = new XHRConnection();
		XHR.appendData('amavisSpamLover',document.getElementById('amavisSpamLover').value);
		XHR.appendData('amavisBadHeaderLover',document.getElementById('amavisBadHeaderLover').value);
		XHR.appendData('amavisBypassVirusChecks',document.getElementById('amavisBypassVirusChecks').value);
		XHR.appendData('amavisBypassSpamChecks',document.getElementById('amavisBypassSpamChecks').value);
		XHR.appendData('amavisBypassHeaderChecks',document.getElementById('amavisBypassHeaderChecks').value);
		XHR.appendData('amavisSpamTagLevel',document.getElementById('amavisSpamTagLevel').value);
		XHR.appendData('amavisSpamTag2Level',document.getElementById('amavisSpamTag2Level').value);
		XHR.appendData('amavisSpamKillLevel',document.getElementById('amavisSpamKillLevel').value);
		XHR.appendData('amavisSpamModifiesSubj',document.getElementById('amavisSpamModifiesSubj').value);
		
		XHR.appendData('amavisSpamDsnCutoffLevel',document.getElementById('amavisSpamDsnCutoffLevel').value);
		XHR.appendData('amavisSpamQuarantineCutoffLevel',document.getElementById('amavisSpamQuarantineCutoffLevel').value);
		XHR.appendData('amavisSpamSubjectTag',document.getElementById('amavisSpamSubjectTag').value);
		XHR.appendData('amavisSpamSubjectTag2',document.getElementById('amavisSpamSubjectTag2').value);
		
		
		XHR.appendData('domain','$domain');
		document.getElementById('domain-amavis').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDomainAmavis);	
	
	}
	
	function GoBackDefaultAmavis(){
		var XHR = new XHRConnection();
		XHR.appendData('GoBackDefaultAmavis','$domain');
		document.getElementById('domain-amavis').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDomainAmavis);	
	}
	
	$start
	$prefix2
	";

	

	echo $html;
	
}


function domain_amavis_front(){
	$sock=new sockets();
	$users=new usersMenus();
	$tpl=new templates();	
	$domain=$_GET["domain"];
	$page=CurrentPageName();
	
	if(!$users->AMAVIS_INSTALLED){
		echo $tpl->_ENGINE_parse_body("<div class=explain>{NO_FEATURE_AMAVIS_NOT_INSTALLED}</div>");	
		return;
	}
	$EnableAmavisDaemon=$sock->GET_INFO("EnableAmavisDaemon");
	$EnableLDAPAmavis=$sock->GET_INFO("EnableLDAPAmavis");
	if(!is_numeric($EnableLDAPAmavis)){$EnableLDAPAmavis=0;}
	if(!is_numeric($EnableAmavisDaemon)){$EnableAmavisDaemon=0;}		
	
	if($EnableAmavisDaemon==0){
		echo $tpl->_ENGINE_parse_body("<div class=explain>{NO_FEATURE_AMAVIS_NOT_ENABLED}</div>");	
		return;		
	}
	
	
	$amavis_bypass_rcpt=0;
	$sql="SELECT `pattern` FROM amavis_bypass_rcpt WHERE `pattern`='{$_GET["domain"]}'";
	$q=new mysql();
	
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["pattern"]<>null){$amavis_bypass_rcpt=1;}

	$amavis_bypass_rcpt_html="
	<table style='width:100%;margin-bottom:5px' class=form>
	<tr>
		<td class=legend>{bypass_content_filter_for_inbound_domain}:</td>
		<td>". Field_checkbox("amavis_bypass_rcpt",1,$amavis_bypass_rcpt,"amavis_bypass_rcpt_domainCheck()")."</td>
	</tr>
	</table>
	<script>
	
		var x_amavis_bypass_rcpt_domainCheck= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
			}
	
	function amavis_bypass_rcpt_domainCheck(){
		var XHR = new XHRConnection();
		if(document.getElementById('amavis_bypass_rcpt')){XHR.appendData('amavis_bypass_rcpt','1');}else{XHR.appendData('amavis_bypass_rcpt','0');}
		XHR.appendData('domain','$domain');
		XHR.sendAndLoad('$page', 'GET',x_amavis_bypass_rcpt_domainCheck);	
		}
		
	</script>
	";

echo $tpl->_ENGINE_parse_body($amavis_bypass_rcpt_html);	

if($EnableLDAPAmavis==0){
	echo $tpl->_ENGINE_parse_body("<div class=explain>{EnableLDAPAmavis_is_disabled_explain}</div>");
	return;
	
}

if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}


$dom=new DomainsTools();
$dom->LoadAmavisDomain($domain);
$button_admin="<div style='text-align:center'>". button('{back_to_defaults}',"GoBackDefaultAmavis()")."</div>";
$button_save="<div style='text-align:right;width:100%'>". button('{apply}',"SaveUserAmavis()")."</div>";
if($EnableLDAPAmavis==0){$button_save=null;$button_admin=null;}


$form1="
<table style='width:100%' class=form>
<tr>
	<td class=legend>{amavisSpamLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisSpamLover',$dom->amavisSpamLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBadHeaderLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBadHeaderLover',$dom->amavisBadHeaderLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassVirusChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassVirusChecks',$dom->amavisBypassVirusChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassSpamChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassSpamChecks',$dom->amavisBypassSpamChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassHeaderChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassHeaderChecks',$dom->amavisBypassHeaderChecks,'{enable_disable}')."</td>
</tR>
</table>
$button_save";


$sa_quarantine_cutoff_level=$tpl->_ENGINE_parse_body('{sa_quarantine_cutoff_level}','amavis.index.php,spamassassin.index.php');
if(strlen($sa_quarantine_cutoff_level)>50){
	$sa_quarantine_cutoff_level=texttooltip(substr($sa_quarantine_cutoff_level,0,47).'...',$sa_quarantine_cutoff_level);
}

$sa_dsn_cutoff_level=$tpl->_ENGINE_parse_body('{sa_dsn_cutoff_level}','amavis.index.php,spamassassin.index.php');
if(strlen($sa_dsn_cutoff_level)>50){
	$sa_dsn_cutoff_level=texttooltip(substr($sa_dsn_cutoff_level,0,47).'...',$sa_dsn_cutoff_level);
}


$form2="

<table style='width:100%' class=form>	
		<tr>
			<td class=legend nowrap>{sa_tag2_level_deflt}:</td>
			<td width=1%>". Field_text('amavisSpamTag2Level',$dom->amavisSpamTag2Level,'width:90px')."</td>
			<td>&nbsp;</td>
							
		</tr>
		<tr>
			<td class=legend nowrap>{sa_kill_level_deflt}:</td>
			<td width=1%>". Field_text('amavisSpamKillLevel',$dom->amavisSpamKillLevel,'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
			<td class=legend nowrap>$sa_dsn_cutoff_level:</td>
			<td width=1%>". Field_text('amavisSpamDsnCutoffLevel',$dom->amavisSpamDsnCutoffLevel,'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class=legend nowrap>$sa_quarantine_cutoff_level:</td>
			<td width=1%>". Field_text('amavisSpamQuarantineCutoffLevel',$dom->amavisSpamQuarantineCutoffLevel,'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>
	</table>

";


$spam_subject_tag2_maps=$tpl->_ENGINE_parse_body('{spam_subject_tag2_maps}','amavis.index.php,spamassassin.index.php');



$form3="
<table style='width:100%' class=form>
<tr>
	
	
	<td class=legend nowrap>{amavisSpamModifiesSubj}:</td>
	<td width=1%>" . Field_TRUEFALSE_checkbox_img('amavisSpamModifiesSubj',$dom->amavisSpamModifiesSubj,'{enable_disable}')."</td>	
	
</tr>	
</table>
<table style='width:100%'>
		<tr>
			<td class=legend nowrap>{spam_subject_tag_maps}:</td>
			<td width=1%>". Field_text('amavisSpamSubjectTag',$dom->amavisSpamSubjectTag,'width:190px')."</td>
			<td class=legend nowrap>{score}:</td>
			<td>" . Field_text("amavisSpamTagLevel",$dom->amavisSpamTagLevel,'width:33px')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>$spam_subject_tag2_maps:</td>
			<td width=1%>". Field_text('amavisSpamSubjectTag2',$dom->amavisSpamSubjectTag2,'width:190px')."</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>		
</table>

";




$html="
<div class=explain>{amavis_domain_text}</div>
<div id='domain-amavis'>$form1<br>$form2<br>$form3</div>$button_save
<div style='text-align:right;width:100%'>$button_admin</div>






";

/*	
amavisSpamLover: FALSE
amavisBadHeaderLover: FALSE
amavisBypassVirusChecks: FALSE
amavisBypassSpamChecks: FALSE

amavisBypassHeaderChecks: FALSE
amavisSpamTagLevel: -999
amavisSpamTag2Level: 5
amavisSpamKillLevel: 5
amavisSpamModifiesSubj: TRUE
*/


echo $tpl->_ENGINE_parse_body($html,'amavis.index.php,spamassassin.index.php');
}


function GoBackDefaultAmavis(){
		$users=new usersMenus();
		$tpl=new templates();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$domain=$_GET["GoBackDefaultAmavis"];
		$dom=new DomainsTools();
		$dom->LoadAmavisDomain($domain);
		$dom->SetDefaultAmavisConfig();
	
}


function SaveAmavisConfig(){
	$tpl=new templates();
	$domain=$_GET["domain"];
	$users=new usersMenus();
	if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		
	
	writelogs("domain=$domain",__FUNCTION__,__FILE__);
	$dom=new DomainsTools();
	$dom->LoadAmavisDomain($domain);	
	while (list ($num, $ligne) = each ($_GET)){
		$dom->$num=$ligne;
	}
	
	if($dom->SaveAmavisConfig()){
		echo $tpl->_ENGINE_parse_body('{success}');
	}

}

function amavis_bypass_rcpt(){
	$amavis_bypass_rcpt=$_GET["amavis_bypass_rcpt"];
	$sql="DELETE FROM amavis_bypass_rcpt WHERE `pattern`='{$_GET["domain"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if($amavis_bypass_rcpt==1){
		$sql="INSERT INTO amavis_bypass_rcpt (`pattern`) VALUES('{$_GET["domain"]}');";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}	
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?amavis-restart=yes");
	$sock->getFrameWork("cmd.php?postfix-smtp-sasl=yes");
	
}


?>