<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	include_once('ressources/class.spamassassin.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableFuzzyOcr"])){Save();exit;}
	
page();

function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<div id='spamassassin_pkugins_explain_div'></div>
	
	
	<script>
		function RefreshAmavisPamPLugin(){
			LoadAjax('spamassassin_pkugins_explain_div','$page?popup=yes');
		}
		RefreshAmavisPamPLugin();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}


function popup(){
	
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$error_module_not_installed=$tpl->_ENGINE_parse_body("{error_module_not_installed}");
	$EnableFuzzyOcr=$sock->GET_INFO("EnableFuzzyOcr");
	$EnableRelayCountry=$sock->GET_INFO("EnableRelayCountry");
	$EnableSpamassassinWrongMX=$sock->GET_INFO("EnableSpamassassinWrongMX");
	$EnableSPF=$sock->GET_INFO("EnableSPF");
	$EnableSpamassassinDnsEval=$sock->GET_INFO("EnableSpamassassinDnsEval");
	$enable_dkim_verification=$sock->GET_INFO("enable_dkim_verification");
	$EnableSpamassassinURIDNSBL=$sock->GET_INFO("EnableSpamassassinURIDNSBL");
	$EnableDecodeShortURLs=$sock->GET_INFO("EnableDecodeShortURLs");
	$EnableSpamAssassinFreeMail=$sock->GET_INFO("EnableSpamAssassinFreeMail");
	$EnablePhishTag=$sock->GET_INFO("EnablePhishTag");
	$EnableAmavisDKIMVerification=$sock->GET_INFO("EnableAmavisDKIMVerification");
	$AmavisNoInternetTests=$sock->GET_INFO("AmavisNoInternetTests");
	$EnableLDAPAmavis=$sock->GET_INFO("EnableLDAPAmavis");
	
	$DisablePyzor=0;

	$spam=new spamassassin();
	$users=new usersMenus();
	$dkim="&nbsp;";
	if(!is_numeric($EnableSpamassassinURIDNSBL)){$EnableSpamassassinURIDNSBL=1;}
	if(!is_numeric($EnableSpamassassinDnsEval)){$EnableSpamassassinDnsEval=1;}
	if(!is_numeric($AmavisNoInternetTests)){$AmavisNoInternetTests=1;}
	if(!$users->pyzor_installed){$DisablePyzor=1;$pyzor_not_installed=$error_module_not_installed."<br>";}
	
	
	$html="
	<div class=explain >{spamassassin_pkugins_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{EnableLDAPAmavis}:</td>
		<td>". Field_checkbox("EnableLDAPAmavis",1,$EnableLDAPAmavis)."</td>
		<td width=1%>". help_icon("{EnableLDAPAmavis_explain}")."</td>
	<tr>	
	
	
	<tr>
		<td class=legend>{AmavisNoInternetTests}:</td>
		<td>". Field_checkbox("AmavisNoInternetTests",1,$AmavisNoInternetTests,"AmavisNoInternetTestsCheck()")."</td>
		<td width=1%>&nbsp;</td>
	<tr>
		<td class=legend>FuzzyOcr:</td>
		<td>". Field_checkbox("EnableFuzzyOcr",1,$EnableFuzzyOcr)."</td>
		<td>&nbsp;</td>
		<td width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>Razor:</td>
		<td>". Field_checkbox("use_razor2",1,$spam->main_array["use_razor2"])."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{razor_text}")."</td>
	</tr>	
	<tr>
		<td class=legend>Pyzor:</td>
		<td>". Field_checkbox("use_pyzor",1,$spam->main_array["use_pyzor"])."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("$pyzor_not_installed{pyzor_text}")."</td>
	</tr>		
	
	<tr>
		<td class=legend>RelayCountry:</td>
		<td>". Field_checkbox("EnableRelayCountry",1,$EnableRelayCountry)."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{deny_countries_text_spam}")."</td>
	</tr>
	

	
	
	<tr>
		<td class=legend>WrongMX:</td>
		<td>". Field_checkbox("EnableSpamassassinWrongMX",1,$EnableSpamassassinWrongMX)."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{WrongMXPlugin}")."</td>
	</tr>
	<tr>
		<td class=legend>RBL DNSBL:</td>
		<td>". Field_checkbox("EnableSpamassassinDnsEval",1,$EnableSpamassassinDnsEval)."</td>
		<td>&nbsp;</td>
		<td width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>URIDNSBL:</td>
		<td>". Field_checkbox("EnableSpamassassinURIDNSBL",1,$EnableSpamassassinURIDNSBL)."</td>
		<td>&nbsp;</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{ACTIVATE_SPF}:</td>
		<td>". Field_checkbox("EnableSPF",1,$EnableSPF)."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{ACTIVATE_SPF_TEXT}<br>{APP_SPF_TEXT}")."</td>
	</tr>			
	<tr>
		<td class=legend>{enable_dkim_verification}:</td>
		<td>". Field_checkbox("enable_dkim_verification",1,$enable_dkim_verification)."</td>
		<td>$dkim</td>
		<td width=1%>". help_icon("{dkim_about}<br>{dkim_about2}")."</td>
	</tr>		
	<tr>
		<td class=legend>{enable_DecodeShortURLs}:</td>
		<td>". Field_checkbox("EnableDecodeShortURLs",1,$EnableDecodeShortURLs)."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{DecodeShortURLs_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{FreeMail}:</td>
		<td>". Field_checkbox("EnableSpamAssassinFreeMail",1,$EnableSpamAssassinFreeMail)."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{EnableSpamAssassinFreeMail_explain}")."</td>
		
	</tr>	
	<tr>
		<td class=legend width=99%>PhishTag:</td>
		<td width=1%>". Field_checkbox("EnablePhishTag",1,$EnablePhishTag)."</td>
		<td width=1%>". imgtootltip("settings-20.gif","{parameters}","Loadjs('spamassassin.phishtag.php')")."</td>
		<td width=1%>". help_icon("{EnablePhishTag_explain}")."</td>
	</tr>	
	
	
	<tr>
		<td colspan=4 align='right'><hr>". button("{apply}","SaveAmavisPlugins()")."</td>
	</tr>
	
</table>	


<script>

var x_SaveAmavisPlugins=function(obj){
      var tempvalue=obj.responseText;
      RefreshAmavisPamPLugin();
      }	
		
	function SaveAmavisPlugins(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableFuzzyOcr').checked){XHR.appendData('EnableFuzzyOcr',1);}else{XHR.appendData('EnableFuzzyOcr',0);}
		if(document.getElementById('EnableRelayCountry').checked){XHR.appendData('EnableRelayCountry',1);}else{XHR.appendData('EnableRelayCountry',0);}
		if(document.getElementById('EnableSpamassassinWrongMX').checked){XHR.appendData('EnableSpamassassinWrongMX',1);}else{XHR.appendData('EnableSpamassassinWrongMX',0);}
		if(document.getElementById('EnableSpamassassinDnsEval').checked){XHR.appendData('EnableSpamassassinDnsEval',1);}else{XHR.appendData('EnableSpamassassinDnsEval',0);}
		if(document.getElementById('EnableSpamassassinURIDNSBL').checked){XHR.appendData('EnableSpamassassinURIDNSBL',1);}else{XHR.appendData('EnableSpamassassinURIDNSBL',0);}
		if(document.getElementById('EnableSPF').checked){XHR.appendData('EnableSPF',1);}else{XHR.appendData('EnableSPF',0);}
		if(document.getElementById('enable_dkim_verification').checked){XHR.appendData('enable_dkim_verification',1);}else{XHR.appendData('enable_dkim_verification',0);}
		if(document.getElementById('EnableDecodeShortURLs').checked){XHR.appendData('EnableDecodeShortURLs',1);}else{XHR.appendData('EnableDecodeShortURLs',0);}
		
		if(document.getElementById('use_razor2').checked){XHR.appendData('use_razor2',1);}else{XHR.appendData('use_razor2',0);}
		if(document.getElementById('use_pyzor').checked){XHR.appendData('use_pyzor',1);}else{XHR.appendData('use_pyzor',0);}
		if(document.getElementById('AmavisNoInternetTests').checked){XHR.appendData('AmavisNoInternetTests',1);}else{XHR.appendData('AmavisNoInternetTests',0);}
		if(document.getElementById('EnablePhishTag').checked){XHR.appendData('EnablePhishTag',1);}else{XHR.appendData('EnablePhishTag',0);}
		if(document.getElementById('EnableSpamAssassinFreeMail').checked){XHR.appendData('EnableSpamAssassinFreeMail',1);}else{XHR.appendData('EnableSpamAssassinFreeMail',0);}
		
		
		
		document.getElementById('spamassassin_pkugins_explain_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveAmavisPlugins);
	}
	
	function AmavisNoInternetTestsCheck(){
		document.getElementById('EnableSpamassassinWrongMX').disabled=true;
		document.getElementById('EnableSpamassassinDnsEval').disabled=true;
		document.getElementById('EnableSpamassassinURIDNSBL').disabled=true;
		document.getElementById('EnableDecodeShortURLs').disabled=true;
		document.getElementById('EnableSPF').disabled=true;
		if(document.getElementById('AmavisNoInternetTests').checked){
			document.getElementById('EnableSpamassassinWrongMX').value=0;
			document.getElementById('EnableSpamassassinDnsEval').value=0;
			document.getElementById('EnableSpamassassinURIDNSBL').value=0;
			document.getElementById('EnableDecodeShortURLs').value=0;
			document.getElementById('EnableSPF').value=0;
			return;
		}
		document.getElementById('EnableSpamassassinWrongMX').disabled=false;
		document.getElementById('EnableSpamassassinDnsEval').disabled=false;
		document.getElementById('EnableSpamassassinURIDNSBL').disabled=false;
		document.getElementById('EnableDecodeShortURLs').disabled=false;
		document.getElementById('EnableSPF').disabled=false;		
		
		
	}
	
	function DisablePyzor(){
		var MustDisable=$DisablePyzor;
		if(MustDisable==1){
			document.getElementById('use_pyzor').checked=false;
			document.getElementById('use_pyzor').disabled=true;
		}
	
	}
	
	AmavisNoInternetTestsCheck();
	DisablePyzor();
	
	
</script>	
	";
	
echo $tpl->_ENGINE_parse_body($html);	
	
}

function Save(){
	$sock=new sockets();
	$page=CurrentPageName();
	$spam=new spamassassin();
	
	if($_GET["AmavisNoInternetTests"]==1){
		$_GET["EnableSpamassassinWrongMX"]=0;
		$_GET["EnableSpamassassinDnsEval"]=0;
		$_GET["EnableSpamassassinURIDNSBL"]=0;
		$_GET["EnableDecodeShortURLs"]=0;
		$_GET["EnableSPF"]=0;
	}
	$sock->SET_INFO("AmavisNoInternetTests",$_GET["AmavisNoInternetTests"]);
	
	$spam->main_array["use_razor2"]=$_GET["use_razor2"];
	$spam->main_array["use_pyzor"]=$_GET["use_pyzor"];
	$spam->SaveToLdap();
	$sock->SET_INFO("EnableFuzzyOcr",$_GET["EnableFuzzyOcr"]);
	$sock->SET_INFO("EnableRelayCountry",$_GET["EnableRelayCountry"]);
	$sock->SET_INFO("EnableSpamassassinWrongMX",$_GET["EnableSpamassassinWrongMX"]);
	$sock->SET_INFO("EnableSPF",$_GET["EnableSPF"]);
	$sock->SET_INFO("EnableSpamassassinDnsEval",$_GET["EnableSpamassassinDnsEval"]);
	$sock->SET_INFO("enable_dkim_verification",$_GET["enable_dkim_verification"]);
	$sock->SET_INFO("EnableAmavisDKIMVerification",$_GET["enable_dkim_verification"]);
	$sock->SET_INFO("EnableSpamassassinURIDNSBL",$_GET["EnableSpamassassinURIDNSBL"]);
	$sock->SET_INFO("EnableDecodeShortURLs",$_GET["EnableDecodeShortURLs"]);	
	$sock->SET_INFO("EnableSpamAssassinFreeMail",$_GET["EnableSpamAssassinFreeMail"]);
	$sock->SET_INFO("EnablePhishTag",$_GET["EnablePhishTag"]);
	
	
	
	
	
}
