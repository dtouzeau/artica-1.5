<?php
	if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc")){include_once(dirname(__FILE__) . '/class.templates.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc");}
	if(!is_file("class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once('class.ldap.inc');}
	include_once(dirname(__FILE__).'/class.cyrus.inc');	
	if(!is_file("class.users.menus.inc")){include_once(dirname(__FILE__) . '/class.users.menus.inc');}else{include_once('class.users.menus.inc');}
	include_once(dirname(__FILE__).'/class.status.inc');
	include_once(dirname(__FILE__).'/class.main_cf.inc');
	
	
Class HtmlPages{
	var $tplClass;
	var $GLOBAL_CONFIG;
	var $AutomaticConfig=false;
	var $fetchmail_installed=false;
	var $cyrus_imapd_installed=false;
	var $kas_installed=false;
	var $aveserver_installed;
	var $rrdtool_installed;
	var $procmail_installed=false;
	
	function HtmlPages(){
		$tpl=new templates();
		$this->tplClass=$tpl;
		$this->GLOBAL_CONFIG=$tpl->GLOBAL_CONFIG;
		$this->AutomaticConfig=$this->GLOBAL_CONFIG["AutomaticConfig"];
		$this->fetchmail_installed=$this->GLOBAL_CONFIG["fetchmail_installed"];
		$this->cyrus_imapd_installed=$this->GLOBAL_CONFIG["cyrus_imapd_installed"];
		$this->kas_installed=$this->GLOBAL_CONFIG["kas_installed"];
		$this->aveserver_installed=$this->GLOBAL_CONFIG["aveserver_installed"];
		$this->rrdtool_installed=$this->GLOBAL_CONFIG["rrdtool_installed"];
		$this->procmail_installed=$this->GLOBAL_CONFIG["procmail_installed"];
	}
	
	
	function PageTaskAdd($taskname){
		
		$yum=new usersMenus();
		$cron=new cron();
		if($yum->AsSystemAdministrator==false){
			$html=$this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");
			return DIV_SHADOW($html,'windows');
		}
		$arrayTasks=$cron->cronID;
		

		$fieldtask=Field_array_Hash($arrayTasks,'TaskID');
			
		$html="<fieldset style='width:80%'>
		<input type='hidden' id='taskname' value=\"$taskname\">
			<legend>$taskname</legend>
				<strong>{select task}</strong>:&nbsp;$fieldtask
		<div align='right'><input type='button' OnClick=\"javascript:SendTaskAdd()\" value='{submit}&nbsp;&raquo:'>
			</fieldset>
			";
		
		
		return DIV_SHADOW($this->tplClass->_ENGINE_parse_body($html),'windows');
		
	}
	
	
	
	

	

	
	
	
	
	
	function PageAveServerUpdateLogs(){
		$yum=new usersMenus();
		if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		$sock=new sockets();
		$file=$sock->getfile("keeup2datelog_kav");
		$table=explode("\n",$file);
		array_reverse($table);
		$html="<table style='width:100%'>";
		while (list ($num, $val) = each ($table) ){
			$val=htmlentities($val);
			$html=$html . "<tr><td style='border-bottom:1px solid #CCCCCC'><span style='font-size:9px'>$val</span></td></tr>";
			
		}
		$html=$html . "</table>";
		return $this->tplClass->_ENGINE_parse_body("<fieldset><legend>{logs}</legend>$html</fieldset>");
	
		
		
	}
	
	function PageAveServerStatus(){
		$yum=new usersMenus();
		if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		$status=new status(1);
		$html="<div id='status'>{$status->avestatus_satus()}<br>{$this->PageAveserverLicence()}<br>{$this->PageAveServerSmtpScannerLogs()}</div>";
		return $this->tplClass->_ENGINE_parse_body($html);
		}
		
		
	
	
	
	function PageAveServerSmtpScannerLogs(){
		$yum=new usersMenus();
		if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		$sock=new sockets();
		$file=$sock->getfile("smtpscanner.log");
		$table=explode("\n",$file);
		array_reverse($table);
		$html="<table style='width:100%'>";
		while (list ($num, $val) = each ($table) ){
			$val=htmlentities($val);
			$html=$html . "<tr><td style='border-bottom:1px solid #CCCCCC'><span style='font-size:9px'>$val</span></td></tr>";
			
		}
		$html=$html . "</table>";
		return $this->tplClass->_ENGINE_parse_body("<fieldset><legend>{antivirus daemon} {logs}</legend>$html</fieldset>");
	}
	

	
	
	function PagePostfixRules(){
		$yum=new usersMenus();
		include_once(dirname(__FILE__) . '/class.main_cf_filtering.inc');
		if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		$html="<fieldset><legend>{postfix rules}</legend>
		<center><input type=button Value=\"{add_new_rule}&nbsp;&raquo;\" OnClick=\"javascript:TreePostfixAddHeaderCheckRule()\" style='width:200px'></center>";
		$main=new main_header_check();	
		if(is_array($main->array_rules)){
			$html=$html."<br><center><table style='width:100%'>";
			while (list ($num, $val) = each ($main->array_rules) ){
				$html=$html . 
				"<tr>
				<td width=1%>" . imgtootltip('x.gif','{delete}',"TreePostfixDeleteHeaderCheckRule($num)")."</td>
				<td><code><a href=\"javascript:TreePostfixAddHeaderCheckRule($num);\">$val</a></code></td>
				</tr>";
				}
			$html=$html . "</table></center>";
			
		}
		
			
		$html=$html ."</fieldset>";
		return $this->tplClass->_ENGINE_parse_body($html);
		
	}
	



	

	

	
	function PagePostfixIndex(){
		$stat=new status(1);
		if($this->AutomaticConfig==false){
			$build="<fieldset><legend>{apply config}</legend>
			<center><input type='button' value='{apply config}&nbsp;&raquo;' OnClick=\"javascript:TreePostfixBuildConfiguration()\"></center>
			</fieldset>";
			
		}
		echo "<div id='status'>".$this->tplClass->_ENGINE_parse_body($stat->Postfix_satus() . $build.$this->PagePostfixLogs())."</div>";
		
		}
		
	function PageFetchmail_Daemon(){
		include_once(dirname(__FILE__). '/class.fetchmail.inc');
		$yum=new usersMenus();
		if($yum->AsMailBoxAdministrator==false){$html=$this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");return $html;}
		$status=new status(1);
		$stat=$status->fetchmail_satus();
		$html="<fieldset><legend>{fetchmail}</legend>
		$stat
		</fieldset>
		<fieldset><legend>{daemon} {settings}</legend>
					<form name=ffmFetch>
					<center><table>
					<tr>
						<td align='right'><strong>{daemon} {pool} ms</strong></td>
						<td align='left'>" . Field_text('fetchmail_daemon_pool',$this->GLOBAL_CONFIG["fetchmail_daemon_pool"]) . "</td>
					</tr>
					<tr>
						<td align='right'><strong>{postmaster}</strong></td>
						<td align='left'>" . Field_text('fetchmail_daemon_postmaster',$this->GLOBAL_CONFIG["fetchmail_daemon_postmaster"]) . "</td>
					</tr>	
					<tr>
					<td colspan=2 align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffmFetch','domains.php',true);\"></td>
					</tr>				
					</table>	
					</form>
					</center>
				</fieldset>";
		
	return $this->tplClass->_ENGINE_parse_body("<div id=status>$html</div>");	
	}
	
	function PageKas3_status(){
		$yum=new usersMenus();
		if($yum->AsPostfixAdministrator==false){$html=$this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");return $html;}
		$status=new status(1);
		$stat=$status->kas_status();
		$html="
		<fieldset><legend>{kas3}</legend>
		$stat
		</fieldset>";
		
		/*if($this->AutomaticConfig==false){
			$html=$html."<br><fieldset><legend>{apply config}</legend>
					<center><input type='button' value='{apply config}' OnClick=\"javascript:TreeFetchMailApplyConfig()\"></center>
				</fieldset>";
			
		}*/		
		
		return $this->tplClass->_ENGINE_parse_body("<div id=status>$html</div>");
	}
	
	
	function PageCyrus_status(){
		include_once(dirname(__FILE__) . "/class.cyrus.inc");
		$cyrus=new cyrus();
		$cyrus->LoadMailBox();
		if(isset($_SESSION["array_mailboxes"])){
			$mailBoxCount=count($_SESSION["array_mailboxes"]);
		}else{$mailBoxCount=0;}
		$ldapbox=$cyrus->LoadLdapAllMailboxes();
		$ldapboxCount=count($ldapbox);
		$status=new status(1);
		$stat=$status->cyrus_satus();
		$html="
		<fieldset><legend>{cyrus_imap}</legend>
		$stat
		<center>
		<table style='width:90%'>
		<tr>
		<td width='1%'><img src='img/emails-20.gif'></td>
		<td><strong>$mailBoxCount {mailboxes_stored_cyrus}</strong></td>
		</tr>
		<tr>
		<td width='1%'><img src='img/family-20.gif'></td>
		<td><strong>$ldapboxCount {mailboxes_stored_ldap}</strong></td>		
		
		</tr>
		</table>
		</center>
		<br>
		<center>";
		if($this->AutomaticConfig==false){
		$html=$html . "<input type='button' OnClick=\"javascript:TreeSynchronyzeMailBoxes();\" value='&laquo;&nbsp;{synchronize mailboxes}&nbsp;&raquo;' style='width:200px'>";
		}
		$html=$html ."</center></fieldset>";
		
		return $this->tplClass->_ENGINE_parse_body("<div id='status'>$html</div>");
	}
	
	function PageLogMonitorIndex(){
		$yum=new usersMenus();
		$html="<fieldset><legend>logs monitor</legend>
		<img src='img/log-monitor-128.gif' align='left' style='margin:4px'>{logs monitor explain}
		<p>&nbsp;</p>
		<center><input type='button' value='{start_log_monitor}&nbsp;&raquo;' OnClick=\"javascript:StartLogMonitor();\"></center>
		</fieldset>
		";
		$html=$this->tplClass->_ENGINE_parse_body($html);
		return $html;
		
	}
		

	
	
	function PageFetchMailShowServerInfos($server_pool_name){
		include_once(dirname(__FILE__). '/class.fetchmail.inc');
		
		$mny=new usersMenus();
		if($mny->AsMailBoxAdministrator==false){return DIV_SHADOW($this->tplClass->_ENGINE_parse_body('{no_privileges}'),'windows');}
		$fetch=new fetchmail();
		$line=$fetch->CompileFromServer($server_pool_name,1);
		$line=nl2br($line);
		$line=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$line);
		$line="<fieldset><legend>$server_pool_name</legend><code>$line</code></fieldset>";
		return DIV_SHADOW($this->tplClass->_ENGINE_parse_body($line),'windows');
		
		
		
	}
	
	function PagePostfixLogs(){
	
	$sock=new sockets();
	$datas=$sock->getfile('maillog:postfix');
	$datas=htmlentities($datas);
	$tbl=explode("\n",$datas);
	$tbl=array_reverse ($tbl, TRUE);
	$html= "<br><fieldset><legend>{events}</legend>
	<table>";
	
	while (list ($num, $val) = each ($tbl) ){
		if(trim($val)<>null){
		if($class=="rowA"){$class="rowB";}else{$class="rowA";}
		$html=$html . "<tr class=$class>
		<td width=1%><img src='" . statusLogs($val) . "'></td>
		<td width=99%' style='font-size:10px;'>$val</td>
		</tr>
		";
		}
		
	}
	
return $html . "</table></fieldset>";
		
		
	}
	


function PageOrganisations(){
	$yum=new usersMenus();if($yum->AsArticaAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
	$html="
	<input type='hidden' id='add_new_organisation_text' value='{add_new_organisation_text}'>
	<input type='hidden' id='delete_organisation_text' value='{delete_organisation_text}'>
	<fieldset><legend>{organizations}</legend>
	<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/organisation-128.gif'></td>
	<td>
		<table style='width:100%'>
		<tr>
		<td width='1%'><img src='img/add-22.gif'></td>
		<td><input type='button' value='{add_new_organisation}&nbsp;&raquo;' OnClick=\"javascript:TreeAddNewOrganisation();\"></td>
		</tr>";
	
		$ldap=new clladp();
		$hash=$ldap->hash_get_ou();
		 while (list ($num, $ligne) = each ($hash) ){
		 	$html=$html . "<tr>
		 	<td width=1%>" . imgtootltip('x.gif','{delete}',"TreeDeleteOrganisation('$ligne');") . "</td>
		 	<td><strong>$ligne</strong>
		 	</tr>";
		 	
		 }
	
	
	$html=$html . "
	</td>
	</tr>
	</table>
	</fieldset>
	
	
	";
	return $this->tplClass->_ENGINE_parse_body($html);	
	
}




function PagePostfix_maincf_Tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$array[]='{inet_interfaces_title}';
	$array[]='{local network}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:TreePostfixLoadPage('$num');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	
}



function PagePostfixSecurityRulesExplain(){
	
	$html="
	<fieldset><legend>{SectionSecurityRulesExplainTitle}</legend>
	{SectionSecurityRulesExplain}
	</fieldset>";
	$html=$this->tplClass->_ENGINE_parse_body($html);
	return $html;
}

function PagePostfixHeaderCheck_windows($num){
	include_once(dirname(__FILE__) . "/class.main_cf_filtering.inc");
	$main=new main_header_check();
	$operator["match"]='{match}';
	$operator["not_match"]='{not_match}';
	
	$action=$main->array_human_actions;
	$rule=$main->headers_regex_table[$num];
	
	
	
	$fields=Field_array_Hash($main->array_headers_values,'fields_headers',$rule["fields_headers"]);
	$field_op=Field_array_Hash($operator,"fields_operator",$rule["fields_operator"]);
	$field_action=Field_array_Hash($action,'fields_action',$rule["fields_action"],'TreePostfixHeaderCheckInfoActions()');
	
	
	
	$html="<fieldset>
		<legend>{header_rule}</legend>
		<form name='TreeRegexFilterRuleForm'>
		<input type='hidden' name='id' value='$num' id='$num'>
		<center><table style='width:90%'>
		<tr>
		<td width=40% valign='top'><div id='TreeRegexFiltersexplain'><img src='img/scripts.gif'></div></td>
		<td valign='top' width=60%>
		<table style='width:100%'>
		<tr>
			<td align='right'><strong>{if}</td>
			<td align='left'>$field_op</td>
		</tr>
		<tr>
			<td align='right' valign='top'><strong>{string}</td>
			<td align='left'>" . Field_text('fields_text',$rule["fields_text"])."<br><span style='font-size:9px'><i>{regex_filter_explain}</i></td>
		</tr>					
		<tr>
		<td align='right'><strong>{match_in}</td>
		<td align='left'>$fields</td>
		</tr>
		<tr>
			<td align='right'><strong>{then}</td>
			<td align='left'>$field_action</td>
		</tr>
		<tr>
			<td align='right'><strong>{width}</td>
			<td align='left'>" . Field_text('fields_action_width',$rule["fields_action_width"])."</td>
		</tr>
		<tr>
		<td colspan=2 align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:TreePostfixHeaderCheckUpdateForm(); \"></td>
		</tr>
		</table>					
		</td>
		</tr>
		</table>
		</fieldset>";
	$html=$this->tplClass->_ENGINE_parse_body($html);
	return DIV_SHADOW($html,'windows');
	
	
}

function PagePostfixsmtpd_client_restrictions($Sender=0){
	$yum=new usersMenus();if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
	
	switch ($Sender) {
		case 1:
			$title="{smtpd_sender_restrictions}";
			$body="{smtpd_sender_restrictions text}";
			$button="{manage smtpd_sender_restrictions}";			
			break;
	
		case 2:
			$title="{smtpd_recipient_restrictions}";
			$body="{smtpd_recipient_restrictions_text}";
			$button="{manage smtpd_recipient_restrictions}";			
			break;	
			
		case 0:
			$title="{smtpd_client_restrictions}";	
			$body="{smtpd_client_restrictions text}";
			$button="{manage smtpd_client_restrictions}";			
			break;					
		default:
			$title="{smtpd_client_restrictions}";	
			$body="{smtpd_client_restrictions text}";
			$button="{manage smtpd_client_restrictions}";
			break;
	}
	
	
	$html="<fieldset>
		<legend>$title</legend>
		<i>$body</i><br>
		<center>
		<input type='button' OnClick=\"javascript:TreePostfixLoadSmtpd_client_restrictions($Sender);\" value='{$button}&nbsp;&raquo;'>
		</center>
		</fieldset>".$this->PagePostfixsmtpd_client_restrictions_table(1,$Sender);
	return $this->tplClass->_ENGINE_parse_body($html);
}

function PagePostfixsmtpd_client_restrictions_table($noform=0,$Sender=0){
	$yum=new usersMenus();if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
	$main=new smtpd_restrictions();
	
	if($noform==1){$button=null;}
	

	switch ($Sender) {
		case 1:
			$title="{smtpd_sender_restrictions}";
			$body="{smtpd_sender_restrictions_text}";
			$button="{manage smtpd_sender_restrictions}";			
			break;
	
		case 2:
			$title="{smtpd_recipient_restrictions}";
			$body="{smtpd_recipient_restrictions_text}";
			$button="{manage smtpd_recipient_restrictions}";			
			break;
			
		case 3:
			$title="{smtpd_helo_restrictions}";
			$body="{smtpd_helo_restrictions_text}";
			$button="{manage smtpd_helo_restrictions}";			
			break;				
			
		case 0:
			$title="{smtpd_client_restrictions}";	
			$body="{smtpd_client_restrictions_text}";
			$button="{manage smtpd_client_restrictions}";			
			break;					
		default:
			$title="{smtpd_client_restrictions}";	
			$body="{smtpd_client_restrictions_text}";
			break;
	}	
	$button="<input type='button' value='{add new rule}&nbsp;&raquo;' OnClick=\"javascript:TreeSmtpd_client_restrictions_addrule($Sender);\">";
	$html="
	<div style='padding:5px;margin:5px'>
	<h4>$title</H4>
	<div class=caption>$body</div>
	<div align='right' style='padding-right:20px;padding-bottom:5px'>
	<span id=rulelist></span>&nbsp;$button
	
	</div>
	<div id=ruleform></div>
	
	<table style='width:79%;border:1px dotted #CCCCCC' align='center'>";
	
	
	switch ($Sender) {
		case 0:
			$array_table=$main->ClientArray;
			$icon_array=$main->smtpd_client_restrictions_table;
			break;
			
		case 1:
			$icon_array=$main->smtpd_sender_restrictions_table;
			$array_table=$main->SenderArray;			
			break;
			
		case 2:
			$icon_array=$main->smtpd_recipient_restrictions_table;
			$array_table=$main->RecipientArray;			
			break;

		case 3:
			$icon_array=$main->smtpd_helo_restrictions_table;
			$array_table=$main->HelloArray;			
			break;					
	
		default:
			$array_table=$main->ClientArray;
			$icon_array=$main->smtpd_client_restrictions_table;			
			break;
	}
	

	
	
	
	if(is_array($array_table)){
		while (list ($num, $ligne) = each ($array_table) ){
			$field=$ligne["value"];
			$data=$ligne["datas"];
		
			switch ($icon_array[1][$field]["mode"]) {
				case 'allow':$img='icon_ok.gif';break;
				case 'disallow':$img='icon_err.gif';break;
				case 'warn':$img='icon_info.gif';break;
				default:$img='icon_info.gif';break;
			}
			
			$tooltip=tooltipjs("{{$ligne["value"]}_text}",1);
			$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"TreeSmtpd_client_restrictions_moverule($num,'up',$Sender)") ."</td>";
			$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"TreeSmtpd_client_restrictions_moverule($num,'down',$Sender)") ."</td>";
			$cell_del="<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"TreeSmtpd_client_restrictions_deleterule($num,$Sender)") ."</td>";
			if($noform==1){$cell_up=null;$cell_down=null;$cell_del=null;}
			$html=$html . "<tr>
			<td width=1%><img src='img/$img'></td>
			<td width=1% nowrap><a href='#' $tooltip>{{$ligne["value"]}}</a></td>
			<td width=90% align='left'>&nbsp;$data</td>
			$cell_up
			$cell_down
			<td width=1%>&nbsp;</td>
			$cell_del
			</tr>";
			
		}
	}
	$html=$html . "</table>
	</div>";
	return $html;
	
}


function PagePostfixDomains(){
	$yum=new usersMenus();if($yum->AsPostfixAdministrator==false){return $this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
	$ldap=new clladp();
	$hash=$ldap->AllDomains();
	$html="<fieldset>
		<legend>{associatedDomain}s</legend>
		<table style='width:100%'>";
	
	while (list ($num, $val) = each ($hash) ){
		$gp=null;
		if(preg_match('#ou=(.+),#',$val,$regs)){$gp=$regs[1];}
		$html=$html . "<tr>
		<td width=1%><img src='img/tree-domains-20.gif'></td>
		<td><strong>$num</td>
		<td><strong>Group:&nbsp;$gp</td>
		</tr>";
	}
	$html=$html . "</table></fieldset>";
	return $this->tplClass->_ENGINE_parse_body($html);
}




function PagePostfixSMTPSaslAuth(){
	$main=new main_cf();
	$enable=Field_TRUEFALSE_checkbox_img('smtp_sasl_password_maps_enable',$main->smtp_sasl_password_maps,"{click_enable_disable}");
	
	$formadd="
	<br>
	<form name='ffm_sasl'>
	<input type='hidden' name='smtp_sasl_password_id' value='{$_GET["TreeSMTPAuthEdit"]}'>
	<table style='border:1px dotted #CCCCCC' width=90% align=center>
		<tr>
		<td align=left colspan=2 style='padding-bottom:4px'><i><strong style='font-size:12px'>{form add/mod}</strong></i></td>
		</tr>	
		<tr>
			<td align='right'><strong>{domain}:</strong></td>
			<td align=left>" . Field_text('smtp_sasl_password_domain',$main->smtp_sasl_password_hash[$_GET["TreeSMTPAuthEdit"]]["DOMAIN"]) . "</td>
		</tr>
		<tr>
			<td align='right'><strong>{username}:</strong></td>
			<td align=left>" . Field_text('smtp_sasl_password_username',$main->smtp_sasl_password_hash[$_GET["TreeSMTPAuthEdit"]]["USERNAME"]) . "</td>
		</tr>	
		<tr>
			<td align='right'><strong>{password}:</strong></td>
			<td align=left>" . Field_text('smtp_sasl_password_password',$main->smtp_sasl_password_hash[$_GET["TreeSMTPAuthEdit"]]["PASSWORD"]) . "</td>
		</tr>
		<tr>
			
			<td align=right colspan=2><input type='button' value='{submit}&nbsp;&raquo;'  OnClick=\"javascript:ParseForm('ffm_sasl','tree.listener.postfix.php',true);LoadTree('settings:postfix:domains:auth');\" ></td>
		</tr>		
						
		</table></form><br>
		<table style='border:1px dotted #CCCCCC' width=90% align=center>";
	
			while (list ($num, $ligne) = each ($main->smtp_sasl_password_hash) ){
						if($ligne["DOMAIN"]<>null){
							$formadd=$formadd . "<tr>
							<td width=1%>" . imgtootltip('tree-auth-20.gif','{edit}',"TreeSMTPAuthEdit($num)")."</td>
							<td><strong>{$ligne["DOMAIN"]}</td>
							<td>{$ligne["USERNAME"]}:{$ligne["PASSWORD"]}</td>
							<td width=1%>" . imgtootltip("x.gif",'{delete}',"TreeSMTPSaslAuthDelete($num)") . "</td>
							</tr>";
						}
			}
	
	$formadd=$formadd."</table>";
	if($main->smtp_sasl_password_maps==false){$formadd=null;}
	$yum=new usersMenus();if($yum->AsPostfixAdministrator==false){return DIV_SHADOW($this->tplClass->_ENGINE_parse_body("<h3>{not allowed}</H3>"),'windows');}	
	$html="<fieldset><legend>{SMTP SASL Authentication}</legend>
	<p>{SMTP SASL Authentication text}</p>
	<table><tr>
	<td valign='middle'><strong>{SMTP SASL Authentication enable}</strong></td>
	<td valign='middle'>$enable</td>
	<td valign='middle'><input type='button' value='Go&nbsp;&raquo;' OnClick=\"javascript:TreeEnableSMTPAuth();\"></td>
	
	</tr>
	</table>
	$formadd
	</fieldset>";
	return $this->tplClass->_ENGINE_parse_body($html);
	
}




	
	
	
	

	
	
	function PageOu($dn){
		$ldap=new clladp();
		$privileges=new usersMenus();
		
		$hash=$ldap->DNGetDatas($dn);
		$hashUsers=$ldap->hash_get_users_Only_ou($hash["ou"]);
		$HashDomains=$ldap->hash_get_domains_ou($hash["ou"]);
		
		$button_domain="<td width=1% align='center'>" . imgtootltip('add-domain-48.gif','{add domain explain}',"Tree_Internet_Domain_Add_New('$dn','ou:$dn')") ."</td>";
		
		if($privileges->AllowChangeDomains==false){$button_domain=null;}
		
		$html="
		<input type='hidden' id=explain_1 value=\"{explain 1}\">
		<input type='hidden' id='inputbox add group' value=\"{inputbox add group}\">
		<input type='hidden' id='inputbox add user' value=\"{inputbox add user}\">
		
		<table style='width:100%;border:1px solid #CCCCCC' align='center'>
		<tr style='background-color:#eeeeee'>
			$button_domain
			<td width=1% align='center'>" . imgtootltip('add-group-48.gif','{add group explain}',"Tree_group_Add_New('{$hash["ou"]}','$ldap->suffix')") ."</td>
			<td width=1% align='center'>" . imgtootltip('adduser-48.gif','{add user explain}',"Tree_ou_Add_user('{$hash["ou"]}','$ldap->suffix')") ."</td>
			<td width=1% align='center'>" . imgtootltip('import-users-48.gif','{import users}',"s_PopUp('export.listener.php?ou={$hash["ou"]}&suffix=$ldap->suffix&mode=ImportUsers',500,500);") ."</td>
			<td width=1% align='center'>" . imgtootltip('find-48.gif','{import users}',"TreeOuLoadPageFindUser('{$hash["ou"]}');") ."</td>
			
			
			
		</tr>
		</table>
		<br>
		<fieldset><legend>{$hash["ou"]} {associatedDomain}</legend>

		<table width=100%>
		<tr>";
		
		if(is_array($HashDomains)){
			while (list ($num, $ligne) = each ($HashDomains) ){
				$delete="<a href=\"javascript:Tree_Delete_Domain($num,'ou:$dn')\"><img src='img/x.gif'>";
				if($privileges->AllowChangeDomains==false){$delete=null;}
				$html=$html ."
				<tr>
					<td align='right' nowrap width=1%><img src='img/upload.gif'></td>
					<td align='left'><strong>$ligne</strong></td>
					<td align='right' nowrap width=1%>$delete</td>
				</tr>";
			}
		}
		
		$html=$html . "</table>
		</fieldset><br>";
		
		
		return $this->tplClass->_ENGINE_parse_body($html);
		}
		
		
    function PageOuFindUser($ou,$tofind=null){
    	
    	if($tofind<>null){
    		$ldap=new clladp();
    		$hash=$ldap->UserSearch($ou,$tofind);
    		if(is_array($hash)){
    			$results="<fieldset>" . count($hash) . " {entries}</filedset>
    			<table style='width:100%'>";
    			while (list ($num, $ligne) = each ($hash) ){
    				$uid=$ligne["uid"][0];
    				if($uid<>null){
    					$mail=$ligne["mail"][0];
    					$tooltip="<div style=border:1px solid #CCCCCC><strong>$uid</strong><br>$mail</strong>";
    					$results=$results . "<tr><td width=1%>" . imgtootltip('user-single-18.gif',$tooltip,"EditLdapUser('{$ligne["dn"]}')")."<td>" . texttooltip($ligne["displayname"][0],$tooltip,"EditLdapUser('{$ligne["dn"]}')") . "</td></tr>";
    				}
    				}
    			$results=$results . "</table>";
    			
    		}
    	}
    	
    	$html="<fieldset><legend>{find user}</legend>
    	<strong>User to find</strong>:&nbsp;<input type='text' id='Tofind' value='$tofind'>&nbsp;<input type='button' OnClick=\"TreeOuFindUser('$ou');\" value='Go&nbsp;&raquo;'><br>
    	<i>{star_explain}</i>
    	</fieldset><br>
    	$results
    	";
    	return DIV_SHADOW($this->tplClass->_ENGINE_parse_body($html),'find');
    		
    	
    }
		
		
    function PageGroup($gid){
    	$ldap=new clladp();
    	$hash=$ldap->GroupDatas($gid);
    	$ou=$hash["ou"];
    	$description=$hash["description"];
    	$tootip="
    	<table style='width:80%;border:1px solid #CCCCCC' align='center'>
		<tr style='background-color:#eeeeee'>
			<td width=1% align='center'>" . imgtootltip('edit-48.gif','{edit group explain}',"Tree_Internet_Domain_Add_New('$dn','ou:$dn')") ."</td>
			<td width=1% align='center'>" . imgtootltip('add-group-48.gif','{add group explain}',"Tree_group_Add_New('{$hash["ou"]}','$ldap->suffix')") ."</td>
			<td width=1% align='center'>" . imgtootltip('adduser-48.gif','{add user explain}',"Tree_group_Add_user('{$hash["ou"]}','$ldap->suffix')") ."</td>
			
		</tr>
		</table>
		<br>";
    	
    	$html="<fieldset><legend>{$hash["cn"]} {group} </legend>
    	<i>$description</i>
    	<input type='hidden' id='inputbox delete group' value=\"{inputbox delete group}\">
    	<table style='width:100%'>
				<tr>
					<td width=1% valign='top'><img src='img/users-folder-64.gif'></td>
					<td valign='top'>
						<table style='width:100%'>
						<tr>
							<td align='right'><strong>{group name}:</td>" . Field_text('group_name',$hash["cn"]) ."</td>
						</tr>
						<tR>
							<td align='right'><strong>{description}:</td><textarea name='description' id='description'>{$hash["description"]}</textarea></td>				
						</tr>
						<tr>
							<td align='right' colspan=2><input type='button' value='{submit}' OnClick=\"javascript:Tree_group_edit1('$gid','$ou','$ldap->suffix');\"></td>
						</tr>									
						</table>
					</td>
				</tr>
				<tr><td align='left' colspan=2>". imgtootltip('x.gif','{delete group explain}',"Tree_group_delete('$gid','$ou','$ldap->suffix')")."</tr>
			</table>
    	
    	
    	</fieldset><br>
    	
    	" . $this->PageGroupPrivileges($gid);;
    	
    	return $this->tplClass->_ENGINE_parse_body($html);
    	
    	
    	
    }
    
    
    
    
    function PageUserWhiteList($userid){
    		$ldap=new clladp();
	$hash=$ldap->UserDatas($userid);	
	
	$html="<table align='center'>";
	
	if(is_array($hash["WhiteList"])){
		while (list ($num, $ligne) = each ($hash["WhiteList"]) ){
			$html=$html . 
			"<tr>
			<td width=1%><img src='img/goodmail.png'></td>
			<td><strong>$ligne</strong></td>
			<td width=1%>" . imgtootltip('x.gif','{delete mail}',"UserDeleteWhiteList('$ligne','$userid')") ."</td>
			</tr>";
			
		}
		
	}
	$html=$html . "</table>";
	
	return $this->tplClass->_ENGINE_parse_body($html);
    	}
    function PageUserBlackList($userid){
    		$ldap=new clladp();
	$hash=$ldap->UserDatas($userid);	
	
	$html="<table align='center'>";
	
	if(is_array($hash["blacklist"])){
		while (list ($num, $ligne) = each ($hash["blacklist"]) ){
			$html=$html . 
			"<tr>
			<td width=1%><img src='img/badmail.png'></td>
			<td><strong>$ligne</strong></td>
			<td width=1%>" . imgtootltip('x.gif','{delete mail}',"UserDeleteBlackList('$ligne','$userid')") ."</td>
			</tr>";
			
		}
		
	}
	$html=$html . "</table>";
	
	return $this->tplClass->_ENGINE_parse_body($html);
    	}    

		
		


		
      function PageDomain($ou,$num){
      		$ldap=new clladp();
      		
      		$HashDomains=$ldap->hash_get_domains_ou($ou);
		$domain=$HashDomains[$num];
      		$transport=$this->PageTransport($domain,$ou);
		
      		$html="$transport<br><fieldset style='width:100%'>
      				<legend>$domain</legend>
      					
      			" . $this->PageUsersInDomain($domain,$ou) . 	"
      			</fieldset>";
      		
		
		return $this->tplClass->_ENGINE_parse_body($html);      		
      	
      }
      
      function PageUserMailBoxForm($uid){
      	include_once(dirname(__FILE__). "/class.cyrus.inc");
      	
      	$ldap=new clladp();
      	$hash=$ldap->UserDatas($uid);
      
      	if($hash["MailboxActive"]=='TRUE'){
      		$cyrus=new cyrus();
      		$res=$cyrus->get_quota_array($uid);
		if($cyrus->connection_off==true OR $cyrus->error==true){return $tpl->_parse_body('{cyrus_connection_error}');}
		$free=$cyrus->USER_STORAGE_LIMIT -$cyrus->USER_STORAGE_USAGE;
		$mailboxInfos="<tr><td>&nbsp;</td><td style='font-size:9px' align='left'><i>$cyrus->USER_STORAGE_LIMIT bytes/$cyrus->USER_STORAGE_USAGE bytes ($free bytes {free})</i></td></tr>";
      	}
      	
      	
      	if($ldap->ldap_last_error<>null){return  nl2br($ldap->ldap_last_error);}
      	$html="<fieldset><legend>{$hash["displayName"]}</legend>
      	<form name='FFUserMailBox'>
      	<input type='hidden' name='TreeUserMailBoxEdit' value='$uid'>
      	<table style='width:100%'>
      	
      	<tr>
	      	
      		<td  align='right' width=1%>" . Field_TRUEFALSE_checkbox_img('MailboxActive',$hash["MailboxActive"]) . "</td>
	      	<td><strong>{MailboxActive}</strong>
	      	<td rowspan=3><input type='button' value='&laquo;&nbsp;{cyrus_apply_config}&nbsp;&raquo;' OnClick=\"javascript:Cyrus_mailbox_apply_settings('$uid');TreeUserMailBoxForm('$uid');\"></td>
      	</tr>
      	<tr>
	      	<td  align='right' nowrap><strong>{mailbox account}:</strong></td>
	      	<td>$uid</td>
      	</tr>      	
      	<tr>
	      	<td  align='right' nowrap><strong>{mailbox quota}:</strong></td>
	      	<td>" . Field_text('MailBoxMaxSize',$hash["MailBoxMaxSize"],'width:30%')."&nbsp;MB</td>
      	</tr>
      		$mailboxInfos
      	<tr>
      		<td colspan=2 align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFUserMailBox','domains.php',true);\"></td>
      	</tr>
      	</table>
      	</form>
      	</fieldset>";
      	return DIV_SHADOW($this->tplClass->_ENGINE_parse_body($html),'windows');
      	
      }
      
	function PageUser($hash=null,$userid=null,$nobox=0){
		
		$ldap=new clladp();
		if(!is_array($hash) && $userid<>null){
			$ldap=new clladp();
			$userarr=$ldap->UserDatas($userid);
			$hash=$ldap->ReadDNInfos($userarr["dn"]);
			$hash["ou"]=$userarr["ou"];
		}
		
			$eMailT=explode('@',$array['mail']);
			$domain=$eMailT[1];
			$email=$eMailT[0];	
			
	if($nobox==0){
		$leftimg="<img src='img/folder-user.gif'>";
	}
	//MailboxActive	
	
	$domains=$ldap->hash_get_domains_ou($hash["ou"]);
	if(is_array($domains)){
		while (list ($num, $ligne) = each ($domains) ){$fDomains[$ligne]=$ligne;}
		
		$domainName="<tr>
					<td align='right'><strong>{domainName}:</strong>
					<td>" . Field_array_Hash($fDomains,'domainName',$hash["domainName"]) . "</td>
					</tr>"	;
	}
	
	if($this->cyrus_imapd_installed==true){$button_mailboxes="<input type='button' value='{mailbox settings}&nbsp;&raquo;' OnClick=\"javascript:TreeUserMailBoxForm('$userid');\" style='margin-right:20px'>";}
	
	
	if($hash["displayname"]==null){$hash["displayname"]="unknown";}
	$html= "
	<fieldset>
		<legend>{$hash["displayname"]}</legend>
		<table>
			<tr>
			<td valign='top' width=1%>
				$leftimg
			</td>
			<td valign='top'>
				
				<form name='userLdapform'>
				<input type='hidden' name='dn' value='{$hash["dn"]}'>
				<table>
					<tr>
					<td align='right'><strong>{ou}:</strong>
					<td>{$hash["ou"]}</td>
					</tr>					
					<tr>
					<td align='right'><strong>{firstname}:</strong>
					<td>" . Field_text('givenname',$hash["givenname"])."</td>
					</tr>	
					<tr>
					<td align='right'><strong>{lastname}:</strong>
					<td>" . Field_text('sn',$hash["sn"],'width:98%',null,'user_autofill();')."</td>
					</tr>			
					
					<tr>
					<td align='right'><strong>{DisplayName}:</strong>
					<td>" . Field_text('displayname',$hash["displayname"])."</td>
					</tr>	
			
					<tr>
					<td align='right'><strong>{email}:</strong>
					<td>" . Field_text('mail',$hash["mail"])."</td>
					</tr>
					<tr>
					<td align='right'><strong>{sender_canonical}:</strong>
					<td>" . Field_text('SenderCanonical',$hash["sendercanonical"])."</td>
					</tr>					
					
					
					
					$domainName
					<tr>
					<td align='right'><strong>{userid}:</strong>
					<td>" . Field_text('uid',$hash["uid"])."</td>
					</tr>	
					
					<tr>
					<td align='right'><strong>{password}:</strong>
					<td><input type='password' id='userpassword' name='userpassword' value='{$hash["userpassword"]}'></td>
					</tr>	
					<tr>
					<td align='right'><strong>{MailboxActive}:</strong>
					<td>" . Field_TRUEFALSE_checkbox_img('MailboxActive',$hash["mailboxactive"]) ."</td>
					</tr>							
			
					<tr>
					
					<td align='right'  colspan=2 >$button_mailboxes<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:SaveLdapUser('{$hash["ou"]}');\"></td>
					</tr>				
				</table>
				</form>	
			</td>
		</tr>
		</table>
	</fieldset>
	<br>" . $this->PageUserAliases($userid);;		
	
	
	return $this->tplClass->_ENGINE_parse_body($html);
	
	}
	
	function PageRootArticaLdapSettings(){
		include_once(dirname(__FILE__).'/class.artica.inc');
		$artica=new artica();
		$user=new usersMenus();	
		if($user->AsArticaAdministrator==false){return null;}	
		$style="style='border-bottom:1px solid #CCCCCC'";
		$html=$html . "<fieldset style='width:88%'>
		<legend >{ldap settings}</legend>
			<form name='ffmArtica2'>
			<input type='hidden' name='save_ldap_settings' value='not used'>
			<table style='padding:5px;border:1px solid #CCCCCC;width:90%' align='center'>
			<tr >
				<td $style align='right'><strong>{ldap admin}:</strong>
				<td $style >" . Field_text('admin',$artica->array_ldap_config["LDAP"]["admin"])."</td>
			</tr>
			<tr >
				<td $style align='right'><strong>{ldap password}:</strong>
				<td $style ><input type='password' name='password' value=\"{$artica->array_ldap_config["LDAP"]["password"]}\" style='width:100%'></td>
			</tr>	
			<tr >
				<td $style align='right'><strong>{ldap suffix}:</strong>
				<td $style >" . Field_text('suffix',$artica->array_ldap_config["LDAP"]["suffix"])."</td>
			</tr>						
			<tr>
				<td colspan=2 >&nbsp;</td>
			<tr >
				<td $style align='right'><strong>{cyrus admin}:</strong>
				<td $style >" . Field_text('cyrus_admin',$artica->array_ldap_config["LDAP"]["cyrus_admin"])."</td>
			</tr>		
			<tr >
				<td $style align='right'><strong>{cyrus password}:</strong>
				<td $style ><input type='password' name='cyrus_password' value=\"{$artica->array_ldap_config["LDAP"]["cyrus_password"]}\" style='width:100%'></td>
			</tr>	
			
			<td >&nbsp;</td>
			<td align='right' ><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffmArtica2','domains.php',true);\"></td>
			</tr>
			</table>
			</form>
		</fieldset>";
		$html=$this->tplClass->_ENGINE_parse_body($html);
		return DIV_SHADOW($html,'windows');
		
		
	}
	
	
	function PageRootArticaConfig(){
		include_once(dirname(__FILE__).'/class.artica.inc');
		$artica=new artica();
		$user=new usersMenus();	
		if($user->AsArticaAdministrator==false){return null;}
		$automatic_config=Field_yesno_checkbox_img('AutomaticConfig',$artica->array_config["ARTICA"]["AutomaticConfig"]);	
		$MANAGE_MAILBOXES=Field_TRUEFALSE_checkbox_img('MANAGE_MAILBOXES',$artica->array_config["ARTICA"]["MANAGE_MAILBOXES"]);	
		
		$LOCALPORT=Field_text('LOCALPORT',$artica->array_config["ARTICA"]["LOCALPORT"],'width:30%');
		
		$this_path=dirname(__FILE__);
		$this_path=str_replace('/ressources','',$this_path);
		$style="style='border-bottom:1px solid #CCCCCC'";
		$html=$html . "<fieldset style='width:88%'>
		<legend >{artica config}</legend>
			<form name='ffmArtica1'>
			<input type='hidden' name='PHP_PATH' value='$this_path'>
			<table style='padding:5px;border:1px solid #CCCCCC;width:90%' align='center'>
			<tr >
				<td $style><strong>{AutomaticConfig}</strong>
				<td $style width=1% align='center'>$automatic_config</td>
			</tr>
			<tr>
				<td colspan=2 ><strong>{web_server_path}:</strong></td>
			</tr>
				<td  colspan=2 $style align='left'>$this_path</td>
			</tr>			
			<tr >
				<td $style><strong>{manage_imap}</strong>
				<td $style width=1% align='center'>$MANAGE_MAILBOXES</td>
			</tr>
			<tr >
				<td $style colspan=2><strong>{listen_port}</strong>:&nbsp;$LOCALPORT
			</tr>								
			</table>
			<table>
			<tr>
			<td ><input type='button' value='{ldap settings}&nbsp;&raquo;' OnClick=\"javascript:artica_ldap_settings();\"></td>
			<td ><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:TreeArticaSaveSettings();\"></td>
			</tr>
			</table>
			</form>
		</fieldset>";
		
		return $html;
	}
	
	
	function PageRoot(){
		if($this->AutomaticConfig==false){
		$html= "
		<fieldset>
			<legend>{apply config}</legend>
			<table>
				<tr>
				<td valign='top' width=1%>
					<img src='img/apply-config-44.gif'>
				</td>
				<td style='padding:4px'>
				<strong style='font-size:11px'><i>{apply config text}</i></strong>
				<div align='right'>
				<input type='button' OnClick=\"javascript:TreeApplyConfigs();\" value='{apply config}&nbsp;&raquo;' ></div>
				</td>
			</tr>
			</table>
		</fieldset><br>";
	}
		
	$html=$html . $this->PageRootArticaConfig();
		

		
	
	return $this->tplClass->_ENGINE_parse_body($html);	
		
	}
	
	
    function PageUserAliases($userid){
    	$ldap=new clladp();
	$hash=$ldap->UserDatas($userid);
    	$aliases=$hash["mailAlias"];
    	$html="<fieldset>
		<legend>{aliases}</legend>
		<table>\n";
    	if(is_array($aliases)){
    		while (list ($num, $ligne) = each ($aliases) ){
    		$html=$html . "<tr>
    		<td width=1%><img src='img/mailbox_storage.gif'></td>
    		<td width=91% nowrap align='left'><code>$ligne</code></td>
    		<td width=1%>" . imgtootltip('x.gif','{delete aliase}',"TreeUserDeleteAliases('$ligne','$userid')")."</td>
    		</tr>
    		";
    		}
    	}
    	$html=$html . "
    	<tr><td colspan=3><strong>{add aliase}:&nbsp;" . Field_text('aliases',null,'width:45%') . "&nbsp;<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:TreeUserAddAliases('$userid');\" style='margin:0px'></td></tr>
    	</table>
    	</fieldset>";
    	return $html;
    }
      
      
      function PageUsersInDomain($domain,$ou){
	$ldap=new clladp();
	if($ou==null){$ou=$_GET["ou"];}else{$_GET["ou"]=$ou;}
	if($domain==null){$domain=$_GET["domain"];}{$_GET["domain"]=$domain;}
	
	$hash=$ldap->HashGetUsersAccounts($ou,$_GET["domain"]);
	if(is_array($hash)){
	$html="<table style='width:100%'>";
		while (list ($num, $ligne) = each ($hash) ){
			if($ligne["cn"]<>null){
				if($ligne["mailboxactive"]=='TRUE'){
					$mailbox="<img src='img/mailbox.gif'>";
				}else{$mailbox="<img src='img/mailbox_storage.gif'>";}
				
			$html= $html . "<tr>
				<td width=1%><img src='img/outicon_1002.gif'></td>
				<td><a href=\"javascript:EditLdapUser('{$ligne["dn"]}');\">{$ligne["mail"]}</a></TD>
				<td width=1%>$mailbox</td>
				</tr>";
			}
			
		}
		
		$html=$html . "</table>";
		
	}
	if($html<>null){
	
	return $this->tplClass->_ENGINE_parse_body($html);
	}


	
}      	
      
	
	
}


?>