<?php
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.milter.greylist.inc');
	include_once('ressources/class.kas-filter.inc');
	include_once('ressources/class.spamassassin.inc');
	include_once('ressources/class.amavis.inc');
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["security"])){security_section();exit();}
	if(isset($_GET["postfix-security"])){security_postfix();exit();}
	
	
	
tabs();


function tabs(){
	$hostname=$_GET["hostname"];
	$tpl=new templates();
	if($hostname==null){$hostname="master";}
	$page=CurrentPageName();
	$array["security"]='{security}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname=$hostname\"><span>$ligne</span></a></li>\n");
	}
	$md=md5($hostname);
	
	echo "
	<div id=main_config_postfix_synthesis_$md style='width:100%;height:790px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_postfix_synthesis_$md\").tabs();});
		</script>";		
	
	
	
}

function security_section(){
	$hostname=$_GET["hostname"];
	$md=md5($hostname);
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<div class=explain>{postfix_security_synthesis_explain}</div>
	<div id='synthesis_postfix_$md'></div>
	
	
	
	<script>
		LoadAjax('synthesis_postfix_$md','$page?postfix-security=yes&hostname=$hostname');
	</script>
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function _MaxRcptTO($hostname){
	$main=new maincf_multi($hostname);
	$max_rcpt_to=$main->GET("max_rcpt_to");
	$js_messagelimit="Loadjs('postfix.messages.restriction.php?script=yes');";
	
	$sock=new sockets();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$EnableAmavisDaemon=$users->EnableAmavisDaemon;
	if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
	if(!is_numeric($EnableAmavisDaemon)){$EnableAmavisDaemon=0;}	
	$ArticaPolicyFilterMaxRCPTInternalDomainsOnly=$sock->GET_INFO("ArticaPolicyFilterMaxRCPTInternalDomainsOnly");
	$SpamassassinMaxRCPTScore=$sock->GET_INFO("SpamassassinMaxRCPTScore");	
	if(!is_numeric($SpamassassinMaxRCPTScore)){$SpamassassinMaxRCPTScore=10;}
	
$maxRPTO="	<tr>
		<td class=legend width='350px'>{max_rcpt_to}:</td>
		<td width='230px' nowrap><a href=\"javascript:blur()\" OnClick=\"javascript:$js_messagelimit\" $stylea>$max_rcpt_to {recipients} (To)</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
	</tr>
	<tr>
		<td class=legend width='350px'> {max_rcpt_to}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$js_messagelimit\" $stylea>$max_rcpt_to {recipients} (Cc)</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{max_rcpt_to}:</td>
		<td width='230px'><strong style='font-size:13px'>None (Bcc)</td>
		<td width=1% nowrap><strong style='font-size:13px'>{pass}</td>
	</tr>";	

if($EnableAmavisDaemon==1){
	$main=new maincf_multi("master","master");
	$max_rcpt_to=$main->GET("max_rcpt_to");
$maxRPTO="	<tr>
		<td class=legend width='350px'>{max_rcpt_to}:</td>
		<td width='230px' nowrap><a href=\"javascript:blur()\" OnClick=\"javascript:$js_messagelimit\" {$GLOBALS["STYLEA"]}>(To) $max_rcpt_to {score}=<strong>$SpamassassinMaxRCPTScore</strong></a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}</td>
	</tr>
	<tr>
		<td class=legend width='350px'> {max_rcpt_to}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$js_messagelimit\" {$GLOBALS["STYLEA"]}>(Cc) $max_rcpt_to {score}=<strong>$SpamassassinMaxRCPTScore</strong></a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{max_rcpt_to}:</td>
		<td width='230px'><strong style='font-size:13px'>None (Bcc)</td>
		<td width=1% nowrap><strong style='font-size:13px'>{pass}</td>
	</tr>";		
	
	
}

return $maxRPTO;
	
}



function security_postfix(){
	$hostname=$_GET["hostname"];
	$user=new usersMenus();
	$user->LoadModulesEnabled();
	$md=md5($hostname);
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();	
	$q=new mysql();
	$main=new maincf_multi($hostname);
	$ou=$main->ou;
	$ou_encoded=base64_encode($ou);
	$spamass=new spamassassin();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$SpamassassinMaxRCPTScore=$sock->GET_INFO("SpamassassinMaxRCPTScore");
	
	if($hostname=="master"){
		$message_size_limit=$sock->GET_INFO("message_size_limit");
		$max_rcpt_to=$main->GET("max_rcpt_to");
			
	}
	$stylea="style='font-size:13px;text-decoration:underline;font-weight:bold'";
	$GLOBALS["STYLEA"]=$stylea;
	
	
	if(!is_numeric($SpamassassinMaxRCPTScore)){$SpamassassinMaxRCPTScore=10;}
	if(!is_numeric($message_size_limit)){$message_size_limit=10240000;}
	if($message_size_limit==0){$message_size_limit="{unlimited}";}else{
		$message_size_limit=FormatBytes($message_size_limit/1024);
	}
	
	$js_messagelimit="Loadjs('postfix.messages.restriction.php?script=yes');";
	
	
	if(!is_numeric($max_rcpt_to)){$max_rcpt_to=0;}
	if($max_rcpt_to==0){$max_rcpt_to="{unlimited}";}
	
	//regex
	$data=unserialize(base64_decode($main->GET_BIGDATA("header_check")));
	$header_content_filters_rules=0;
	if(is_array($data)){while (list ($num, $ligne) = each ($data)){if($ligne==null){continue;}$header_content_filters_rules++;}}
	if($header_content_filters_rules==0){$header_content_filters_rules="{no_rules}";}else{$header_content_filters_rules="$header_content_filters_rules&nbsp;{rules}";}
	
	
	$check_client_access=0;
	$data=unserialize(base64_decode($main->GET_BIGDATA("check_client_access")));
	if(is_array($data)){while (list ($num, $ligne) = each ($data)){if($ligne==null){continue;}$check_client_access++;}}
	if($check_client_access==0){$check_client_access="{no_rules}";}else{$check_client_access="$check_client_access&nbsp;{rules}";}
		
	
	$EnableBodyChecks=$main->GET("EnableBodyChecks");
	if(!is_numeric($EnableBodyChecks)){$EnableBodyChecks=0;}
	$EnableBodyChecks_text="{disabled}";
	if($EnableBodyChecks==1){$EnableBodyChecks_text="{enabled}";}
	$sql="SELECT COUNT(*) as tcount FROM postfix_regex_words WHERE `hostname`='{$_GET["hostname"]}' AND enabled=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]==0){$EnableBodyChecks_rules="{no_rules}";}else{$EnableBodyChecks_rules="{$ligne["tcount"]}&nbsp;{rules}";}
	
	$js_iptables="Loadjs('postfix.iptables.php');";
	$js_regex="Loadjs('domains.postfix.multi.regex.php?ou=$ou&hostname=$hostname');";
	
	$EnablePostfixAutoBlock=$sock->GET_INFO("EnablePostfixAutoBlock");
	if(!is_numeric($EnablePostfixAutoBlock)){$EnablePostfixAutoBlock=0;}
	$EnablePostfixAutoBlock_text="{disabled}";
	if($EnablePostfixAutoBlock==1){$EnablePostfixAutoBlock_text="{enabled}";}	
	$sql="SELECT COUNT(*) AS tcount FROM iptables WHERE local_port=25 AND flux='INPUT' AND disable=0";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]==0){$PostfixAutoBlock_rules="{no_rules}";}else{$PostfixAutoBlock_rules="{$ligne["tcount"]}&nbsp;{rules}";}	
	
	
	$smtpd_client_restrictions_js="Loadjs('postfix.smtpd_client_restrictions.php');";
	$EnablePostfixAntispamPack_value=$sock->GET_INFO('EnablePostfixAntispamPack');	
	$EnableGenericrDNSClients=$sock->GET_INFO("EnableGenericrDNSClients");
	$reject_forged_mails=$sock->GET_INFO('reject_forged_mails');	
	$PostfixHideClientMua=$sock->GET_INFO("PostfixHideClientMua");
	$PostfixHideClientMua_js="Loadjs('postfix.hide.headers.php')";
	
	$EnablePostfixInternalDomainsCheck=$sock->GET_INFO('EnablePostfixInternalDomainsCheck');
	$RestrictToInternalDomains=$sock->GET_INFO('RestrictToInternalDomains');
	
	$reject_unknown_client_hostname=$sock->GET_INFO('reject_unknown_client_hostname');
	$reject_unknown_reverse_client_hostname=$sock->GET_INFO('reject_unknown_reverse_client_hostname');
	$reject_unknown_sender_domain=$sock->GET_INFO('reject_unknown_sender_domain');
	$reject_invalid_hostname=$sock->GET_INFO('reject_invalid_hostname');
	$reject_non_fqdn_sender=$sock->GET_INFO('reject_non_fqdn_sender');
	
	if($reject_unknown_client_hostname==1){$reject_unknown_client_hostname="{enabled}";}else{$reject_unknown_client_hostname="{disabled}";}
	if($reject_unknown_reverse_client_hostname==1){$reject_unknown_reverse_client_hostname="{enabled}";}else{$reject_unknown_reverse_client_hostname="{disabled}";}
	if($reject_unknown_sender_domain==1){$reject_unknown_sender_domain="{enabled}";}else{$reject_unknown_sender_domain="{disabled}";}
	if($reject_invalid_hostname==1){$reject_invalid_hostname="{enabled}";}else{$reject_invalid_hostname="{disabled}";}
	if($reject_non_fqdn_sender==1){$reject_non_fqdn_sender="{enabled}";}else{$reject_non_fqdn_sender="{disabled}";}
	if($RestrictToInternalDomains==1){$RestrictToInternalDomains="{enabled}";}else{$RestrictToInternalDomains="{disabled}";}
	if($PostfixHideClientMua==1){$PostfixHideClientMua="{enabled}";}else{$PostfixHideClientMua="{disabled}";}
	
	
	
	
	//Postscreen ------------------------------------------------------------------------------------
	$postscreen_js="Loadjs('postscreen.php?hostname=$hostname&ou=$ou');";
	$EnablePostScreen=$main->GET("EnablePostScreen");
	if(!$user->POSTSCREEN_INSTALLED){$EnablePostScreen=0;}
	$postscreen_dnsbl_action=$main->GET("postscreen_dnsbl_action");
	if($EnablePostScreen==1){$EnablePostScreen="{enabled}";$postscreen_dnsbl_action="$postscreen_dnsbl_action";}else{$EnablePostScreen="{disabled}";$postscreen_dnsbl_action="{disabled}";}
	$data=unserialize(base64_decode($main->GET_BIGDATA("postscreen_dnsbl_sites")));
	$postscreen_dnsbl_count=0;
	while (list ($num, $ligne) = each ($data)){if($ligne==null){continue;}$postscreen_dnsbl_count++;}
	if($postscreen_dnsbl_count==0){$postscreen_dnsbl_count="{no_rules}";}else{$postscreen_dnsbl_count="$postscreen_dnsbl_count&nbsp;{rules}";}
	
	
	//milter-greylist {gl_YES-QUICK} ------------------------------------------------------------------------------------
	$mgreylist_js="Loadjs('milter.greylist.index.php?js=yes');";
	$mgreylist=new milter_greylist(true,$hostname,$ou);
	$MilterGreyListEnabled=$mgreylist->MilterGreyListEnabled;
	if(!$user->MILTERGREYLIST_INSTALLED){$MilterGreyListEnabled=0;}
	$mgreylist_delay="{$mgreylist->main_array["greylist"]}&nbsp;{$mgreylist->main_array["greylist_TIME"]}";
	if($MilterGreyListEnabled==1){$MilterGreyListEnabled="{enabled}&nbsp;$mgreylist_delay";}else{$MilterGreyListEnabled="{disabled}";}

	//Blacklist: ------------------------------------------------------------------------------------
	$Blacklist_js="Loadjs('whitelists.admin.php?js=yes')";
	$sql="SELECT COUNT(*) as tcount FROM postfix_global_blacklist WHERE enabled=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]==0){$Blacklist_rules="{no_rules}";}else{$Blacklist_rules="{$ligne["tcount"]}&nbsp;{rules}";}
	
	//Attachments ------------------------------------------------------------------------------------
	$enable_attachment_blocking_postfix=$main->GET("enable_attachment_blocking_postfix");
	$attachs_js="Loadjs('domains.edit.attachblocking.ou.php?ou=$ou_encoded&hostname=$hostname');";
	if($EnablePostfixMultiInstance){$hostnameq=" AND hostname='$hostname'";}
	$ou_q=$ou;
	if($hostname=="master"){$ou_q="_Global";}
	$sql="SELECT count(*) as tcount FROM smtp_attachments_blocking WHERE ou='$ou_q'$hostnameq";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	if($ligne["tcount"]==0){$attachs_rules="{no_rules}";}else{$attachs_rules="{$ligne["tcount"]}&nbsp;{rules}";}
	if($enable_attachment_blocking_postfix==1){$enable_attachment_blocking_postfix="{enabled}";}else{$enable_attachment_blocking_postfix="{disabled}";}	
	
	//Kas3 ------------------------------------------------------------------------------------
	$ou_kas=$ou;
	if($hostname=="master"){$ou_q=base64_encode("default");$ou_kas="default";}
	$kas_js="Loadjs('domains.edit.kas.php?ou=$ou_q');";
	$kas=new kas_mysql($ou_kas);
	$KasxFilterEnabled=$sock->GET_INFO("KasxFilterEnabled");
	$OPT_FILTRATION_ON=$kas->GET_KEY("OPT_FILTRATION_ON");
	$OPT_PROBABLE_SPAM_ON=$kas->GET_KEY("OPT_PROBABLE_SPAM_ON");
	$ACTION_SPAM_MODE=$kas->GET_KEY("ACTION_SPAM_MODE");
	$ACTION_PROBABLE_MODE=$kas->GET_KEY("ACTION_PROBABLE_MODE");	
	if(!$user->kas_installed){$KasxFilterEnabled=0;}
	if($KasxFilterEnabled==0){$OPT_FILTRATION_ON=0;$OPT_PROBABLE_SPAM_ON=0;}
	$OPT_SPAM_RATE_LIMIT=$kas->GET_KEY("OPT_SPAM_RATE_LIMIT");
	$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
	if(!is_numeric($OPT_SPAM_RATE_LIMIT)){$OPT_SPAM_RATE_LIMIT=3;}
	if($OPT_FILTRATION_ON==1){$kas_text="{level}:{$OPT_SPAM_RATE_LIMIT_TABLE[$OPT_SPAM_RATE_LIMIT]}";}else{$kas_text="{disabled}";$ACTION_SPAM_MODE=-4;$ACTION_PROBABLE_MODE=-4;}
	if($OPT_PROBABLE_SPAM_ON==1){$kas_probable="{enabled}";}else{$kas_probable="{disabled}";}	
	$kas_action_message=array(0=>"{acceptmessage}",1=>"{kassendcopy}",2=>"{quarantine}",-1=>"{kasreject}",-3=>"{kasdelete}",-4=>"{disabled}");
	$ACTION_SPAM_MODE=$kas_action_message[$ACTION_SPAM_MODE];
	$ACTION_PROBABLE_MODE=$kas_action_message[$ACTION_PROBABLE_MODE];
	
	

	
	//Amavis - spamassassin
	$EnableAmavisDaemon=$user->EnableAmavisDaemon;
	if(!$user->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
	$amavis_js="Loadjs('amavis.index.php?ajax=yes');";
	if($EnablePostfixMultiInstance){$hostnameq=" AND hostname='$hostname'";}
	$ou_q=$ou;
	if($hostname=="master"){$ou_q="_Global";}
	$sql="SELECT count(*) as tcount FROM smtp_attachments_blocking WHERE ou='$ou_q'$hostnameq";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	if($ligne["tcount"]==0){$amavis_attachs_rules="{no_rules}";}else{$amavis_attachs_rules="{$ligne["tcount"]}&nbsp;{rules}";}	
	if($EnableAmavisDaemon==0){$EnableAmavisDaemon_text="{disabled}";}else{$EnableAmavisDaemon_text="{enabled}";}
	
	//Spamassassin - keywords
	$sql="SELECT count(*) as tcount FROM spamassassin_keywords WHERE enabled=1 and score>0";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]==0){$spamassassin_keywords="$EnableAmavisDaemon_text&nbsp;{no_rules}";}else{$spamassassin_keywords="$EnableAmavisDaemon_text&nbsp;{$ligne["tcount"]}&nbsp;{rules}";}	
	$spamassassin_keywords_js="Loadjs('spamassassin.keywords.php');";
	
	//spamassassin_backscatter
	$spamassassin_backscatter_js="Loadjs('spamassassin.backscatter.php');";
	$spamassassin_backscatter=$sock->GET_INFO("SpamAssassinVirusBounceEnabled");
	if($EnableAmavisDaemon==0){$spamassassin_backscatter=0;}
	if($spamassassin_backscatter==0){$spamassassin_backscatter="{disabled}";}else{$spamassassin_backscatter="{enabled}";}
	
	$DecodeShortURLs_js="Loadjs('spamassassin.DecodeShortURLs.php');";
	$sql="SELECT COUNT(*) as tcount FROM  spamassassin_table WHERE spam_type='DecodeShortURLs' AND enabled=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]==0){$DecodeShortURLs="$EnableAmavisDaemon_text&nbsp;{no_rules}";}else{$DecodeShortURLs="$EnableAmavisDaemon_text&nbsp;{$ligne["tcount"]}&nbsp;{rules}";}	
		
	
	//plugins, wrongx, pyzor,razor...
	$spamassassin_js="Loadjs('spamassassin.index.php')";
	$EnableSpamassassinWrongMX=$sock->GET_INFO("EnableSpamassassinWrongMX");
	if(!is_numeric($EnableSpamassassinWrongMX)){$EnableSpamassassinWrongMX=1;}
	if($EnableAmavisDaemon==0){$EnableSpamassassinWrongMX=0;}
	if($EnableSpamassassinWrongMX==0){$EnableSpamassassinWrongMX="{disabled}";}else{$EnableSpamassassinWrongMX="{enabled}";}
	
	$use_razor2=$spamass->main_array["use_razor2"];
	if($EnableAmavisDaemon==0){$use_razor2=0;}
	if($use_razor2==0){$use_razor2="{disabled}";}else{$use_razor2="{enabled}";}
	
	$use_pyzor=$spamass->main_array["use_pyzor"];
	if($EnableAmavisDaemon==0){$use_pyzor=0;}
	if($use_pyzor==0){$use_pyzor="{disabled}";}else{$use_pyzor="{enabled}";}	
	
	$amavis=new amavis();
	$amavis_action1="{pass}";
	$amavis_action2="{pass}";
	$amavis_spam="$EnableAmavisDaemon_text&nbsp;{score} >={$amavis->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]}";
	if($amavis->EnableQuarantineSpammy2==1){$amavis_action1="{quarantine}";}

	$amavis_probable="$EnableAmavisDaemon_text&nbsp;{score} >={$amavis->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]}";
	if($amavis->EnableQuarantineSpammy==1){$amavis_action2="{quarantine}";}
	
	$clamav_unofficial_js="Loadjs('clamav.unofficial.php')";
	
	
	$html="
	<div style='text-align:right'>". imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_postfix_synthesis_$md')")."</div>
	<H3 style='font-size:16px'>{APP_POSTFIX} &raquo; {filters_connect}</h3>
	<table style='width:665px' class=form>
		<tr>
			<td class=legend width='350px'>{postfix_autoblock}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$js_iptables\" $stylea>$EnablePostfixAutoBlock_text&nbsp;$PostfixAutoBlock_rules</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{firewall}</td>
		</tr>	
		<tr>
			<td class=legend width='350px'>{black list}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$Blacklist_js\" $stylea>$Blacklist_rules</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>				
		<tr>
			<td class=legend width='350px'>PostScreen:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$postscreen_js\" $stylea>$EnablePostScreen</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>		
		<tr>
			<td class=legend width='350px'>PostScreen RBL:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$postscreen_js\" $stylea>$postscreen_dnsbl_action&nbsp;$postscreen_dnsbl_count</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>			
		<tr>
			<td class=legend width='350px'>{blockips}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$js_regex\" $stylea>$check_client_access</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>
		
		<tr>
			<td class=legend width='350px'>{reject_unknown_client_hostname}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$smtpd_client_restrictions_js\" $stylea>$reject_unknown_client_hostname</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>
		<tr>
			<td class=legend width='350px'>{reject_unknown_reverse_client_hostname}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$smtpd_client_restrictions_js\" $stylea>$reject_unknown_reverse_client_hostname</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>		
		<tr>
			<td class=legend width='350px'>{reject_unknown_sender_domain}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$smtpd_client_restrictions_js\" $stylea>$reject_unknown_sender_domain</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>			
		<tr>
			<td class=legend width='350px'>{reject_invalid_hostname}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$smtpd_client_restrictions_js\" $stylea>$reject_invalid_hostname</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>		
		<tr>
			<td class=legend width='350px'>{reject_non_fqdn_sender}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$smtpd_client_restrictions_js\" $stylea>$reject_non_fqdn_sender</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>	
		<tr>
			<td class=legend width='350px'>{RestrictToInternalDomains}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$smtpd_client_restrictions_js\" $stylea>$RestrictToInternalDomains</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
		</tr>
	</table>
	
	<H3 style='font-size:16px'>{filters_connect}</h3>
	<table style='width:665px' class=form>
		<tr>
			<td class=legend width='350px'>{gl_YES-QUICK}:</td>
			<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$mgreylist_js\" $stylea>$MilterGreyListEnabled</a></td>
			<td width=1% nowrap><strong style='font-size:13px'>{delayed}</td>
		</tr>	
	</table>
	
	<H3 style='font-size:16px'>{APP_POSTFIX} &raquo; {messages_restriction}</h3>
	<table style='width:665px' class=form>
	". _MaxRcptTO($hostname)."
	<tr>
		<td class=legend width='350px'>{message_size_limit}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$js_messagelimit\" $stylea>$message_size_limit</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{HIDE_CLIENT_MUA}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$PostfixHideClientMua_js\" $stylea>$PostfixHideClientMua</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{pass}</td>
	</tr>
	
	
	</table>
	<H3 style='font-size:16px'>{APP_POSTFIX} &raquo; {content_filters}</h3>
	<table style='width:665px' class=form>
	<tr>
		<td class=legend width='350px'>{header_content_filters_rules}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$js_regex\" $stylea>$header_content_filters_rules</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{RegexSimpleWords}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$js_regex\" $stylea>$EnableBodyChecks_text&nbsp;$EnableBodyChecks_rules</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{attachment_blocking}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$attachs_js\" $stylea>$enable_attachment_blocking_postfix&nbsp;$attachs_rules</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{reject}</td>
	</tr>	
	</table>
	<H3 style='font-size:16px'>{content_filters} &raquo; {APP_KAS3}</h3>
	<table style='width:665px' class=form>
	<tr>
		<td class=legend width='350px'>{APP_KAS3}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$kas_js\" $stylea>$kas_text</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{OPT_PROBABLE_SPAM_ON}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$kas_js\" $stylea>$kas_probable</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend width='350px'>{spam option 2}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$kas_js\" $stylea>$ACTION_PROBABLE_MODE</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{spam option 1}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$kas_js\" $stylea>$ACTION_SPAM_MODE</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>&nbsp;</td>
	</tr>		
	
</table>
	<H3 style='font-size:16px'>{content_filters} &raquo; {addons_bundle}</h3>
<table style='width:665px' class=form>
	<tr>
		<td class=legend width='350px'>{attachment_blocking}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$kas_js\" $stylea>$EnableAmavisDaemon_text&nbsp;$amavis_attachs_rules</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{quarantine}</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{block_keywords}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$spamassassin_keywords_js\" $stylea>$spamassassin_keywords</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}+</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{Virus_Bounce_Ruleset}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$spamassassin_backscatter_js\" $stylea>$spamassassin_backscatter</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}+</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{DecodeShortURLs}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$DecodeShortURLs_js\" $stylea>$DecodeShortURLs</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}+</td>
	</tr>		
	<tr>
		<td class=legend width='350px'>WrongMX:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$spamassassin_js\" $stylea>$EnableSpamassassinWrongMX</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}+</td>
	</tr>		
	<tr>
		<td class=legend width='350px'>Razor:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$spamassassin_js\" $stylea>$use_razor2</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}+</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>Pyzor:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$spamassassin_js\" $stylea>$use_pyzor</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{score}+</td>
	</tr>	
	<tr>
		<td class=legend width='350px'>{spam option 2}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$amavis_js\" $stylea>$amavis_probable</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>$amavis_action2</td>
	</tr>			
	<tr>
		<td class=legend width='350px'>{spam option 1}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$amavis_js\" $stylea>$amavis_spam</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>$amavis_action1</td>
	</tr>
	<tr>
		<td class=legend width='350px'>{APP_CLAMAV}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$amavis_js\" $stylea>$EnableAmavisDaemon_text</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{quarantine}</td>
	</tr>		
	<tr>
		<td class=legend width='350px'>{clamav_unofficial}:</td>
		<td width='230px'><a href=\"javascript:blur()\" OnClick=\"javascript:$clamav_unofficial_js\" $stylea>$EnableAmavisDaemon_text</a></td>
		<td width=1% nowrap><strong style='font-size:13px'>{quarantine}</td>
	</tr>			

	

	
</table>	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


