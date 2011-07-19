<?php
if(!is_file("class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once('class.ldap.inc');}
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__)."/class.mysql.inc");
include_once(dirname(__FILE__)."/class.spamassassin.inc");

class mimedefang{
	var $dn;
	var $main_array=array();
	var $global_conf;
	var $ScriptConf;
	var $main_folders;
	var $ScriptConf_array;
	var $Disclaimer;
	var $graph_array=array();
	
	function mimedefang(){
		$this->verifyBranch();
		$this->LoadSettings();
		$this->ParseSettings();
		$this->BuilGraphArray();
		
	}
	
function verifyBranch(){
	$ldap=new clladp();
	$this->dn="cn=mimedefang,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($this->dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='MimeDefangClass';
		$upd["cn"]="mimedefang";
		
		$this->main_array["bad_exts"]="ade|adp|app|asd|asf|asx|bas|bat|chm|cmd|com|cpl|crt|dll|exe|fxp|hlp|hta|hto|inf|ini|ins|isp|jse?|lib|lnk|mdb|mde|msc|msi|msp|mst|ocx|pcd|pif|prg|reg|scr|sct|sh|shb|shs|sys|url|vb|vbe|vbs|vcs|vxd|wmd|wms|wmz|wsc|wsf|wsh|\{[^\}]+\}";
		$upd["MimeDefangScriptsConf"][]=$this->BuildScriptConf();
		$upd["MimeDefangFilter"][]=$this->BuildConfig();
		$upd["MimeDefangDisclaimer"][]=$this->Disclaimer;
		
		$ldap->ldap_add($this->dn,$upd);
	}
	
	$this->BuildMysqlDatabases();
}


function BuilGraphArray(){
	$this->graph_array[]="daily_spam_9recipient_stacked_bar_Heartlight_Traffic.png";
	$this->graph_array[]="daily_spam_9sender_stacked_bar.png";
	$this->graph_array[]="daily_spam_9value2_stacked_bar.png";
	$this->graph_array[]="daily_spamprobable_spamvirusmail_in_summary_line.png";
	$this->graph_array[]="daily_spamvirus_9recipient_stacked_bar.png";
	$this->graph_array[]="daily_virus_9value2_stacked_bar.png";
	$this->graph_array[]="daily_virus_value1_stacked_bar.png";
	$this->graph_array[]="hourly_spam_9recipient_stacked_bar_Heartlight_Traffic.png";
	$this->graph_array[]="hourly_spam_9sender_stacked_bar.png";
	$this->graph_array[]="hourly_spam_9value2_stacked_bar.png";
	$this->graph_array[]="hourly_spamprobable_spamvirusmail_in_summary_line.png";
	$this->graph_array[]="hourly_spamvirus_9recipient_stacked_bar.png";
	$this->graph_array[]="hourly_virus_9value2_stacked_bar.png";
	$this->graph_array[]="hourly_virus_value1_stacked_bar.png";
	$this->graph_array[]="monthly_spam_9recipient_stacked_bar_Heartlight_Traffic.png";
	$this->graph_array[]="monthly_spam_9sender_stacked_bar.png";
	$this->graph_array[]="monthly_spam_9value2_stacked_bar.png";
	$this->graph_array[]="monthly_spamprobable_spamvirusmail_in_summary_line.png";
	$this->graph_array[]="monthly_spamvirus_9recipient_stacked_bar.png";
	$this->graph_array[]="monthly_virus_9value2_stacked_bar.png";
	$this->graph_array[]="monthly_virus_value1_stacked_bar.png";	
}


function BuildDefaults(){
	
	if($this->main_array["AdminAddress"]==null){$this->main_array["AdminAddress"]="root@localhost";}
	if($this->main_array["AdminName"]==null){$this->main_array["AdminName"]="MIMEDefang Administrator";}
	if($this->main_array["DaemonAddress"]==null){$this->main_array["DaemonAddress"]="root@localhost";}
	if($this->main_array["AddWarningsInline"]==null){$this->main_array["AddWarningsInline"]=0;}
	if($this->ScriptConf_array["BUILD"]["EnableSA"]==null){$this->ScriptConf_array["BUILD"]["EnableSA"]=0;}
	if($this->ScriptConf_array["BUILD"]["SA_SCORE"]==null){$this->ScriptConf_array["BUILD"]["SA_SCORE"]=40;}
	if($this->ScriptConf_array["BUILD"]["ENABLE_AV"]==null){$this->ScriptConf_array["BUILD"]["ENABLE_AV"]=0;}
	if($this->ScriptConf_array["BUILD"]["ENABLE_BOGO"]==null){$this->ScriptConf_array["BUILD"]["ENABLE_BOGO"]=0;}
	if($this->ScriptConf_array["BUILD"]["BIGHTML_ENABLED"]==null){$this->ScriptConf_array["BUILD"]["BIGHTML_ENABLED"]=0;}
	if($this->ScriptConf_array["BUILD"]["ENABLE_DISCLAIMER"]==null){$this->ScriptConf_array["BUILD"]["ENABLE_DISCLAIMER"]=0;}
	if($this->ScriptConf_array["BUILD"]["CONVERT_TNEF"]==null){$this->ScriptConf_array["BUILD"]["CONVERT_TNEF"]=0;}
	if($this->ScriptConf_array["BUILD"]["SA_RCPT_EMAIL"]==null){$this->ScriptConf_array["BUILD"]["SA_RCPT_EMAIL"]="root@localhost";}
	if($this->ScriptConf_array["BUILD"]["BACKUP_ENABLED"]==null){$this->ScriptConf_array["BUILD"]["BACKUP_ENABLED"]=0;}
	if($this->ScriptConf_array["BUILD"]["QUARANTINE_SPAM"]==null){$this->ScriptConf_array["BUILD"]["QUARANTINE_SPAM"]=0;}
	if($this->ScriptConf_array["BUILD"]["DISCARD_SPAM_SPAMASS"]==null){$this->ScriptConf_array["BUILD"]["DISCARD_SPAM_SPAMASS"]=7;}
	if($this->ScriptConf_array["BUILD"]["DISCARD_SPAM_KAS3"]==null){$this->ScriptConf_array["BUILD"]["DISCARD_SPAM_KAS3"]=90;}
	if($this->ScriptConf_array["BUILD"]["AUTOWHITE_LIST"]==null){$this->ScriptConf_array["BUILD"]["AUTOWHITE_LIST"]=1;}
	if($this->ScriptConf_array["BUILD"]["RECIPIENT_TRANSFORM"]==null){$this->ScriptConf_array["BUILD"]["RECIPIENT_TRANSFORM"]=0;}
	if($this->ScriptConf_array["BUILD"]["FILTER_LOCAL_SENDER_ENABLED"]==null){$this->ScriptConf_array["BUILD"]["FILTER_LOCAL_SENDER_ENABLED"]=1;}
	
	if($this->Disclaimer==null){
		$this->Disclaimer="<hr><code>All information contained in this email is confidential and may be used by the intended recipient only</code></hr>";
	}
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$sock=new sockets();
	if($sock->GET_INFO("MailArchiverEnabled")==1){
		$this->ScriptConf_array["BUILD"]["BACKUP_ENABLED"]=0;
	}
	
	
}

	

function SaveToLdap(){
	$upd["MimeDefangScriptsConf"][]=$this->BuildScriptConf();
	$upd["MimeDefangFilter"][]=$this->BuildConfig();
	$upd["MimeDefangDisclaimer"][]=$this->Disclaimer;
	$ldap=new clladp();
	if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{success}:MimeDefang;\n");
	}
	$sock=new sockets();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if($users->MimeDefangEnabled==1){
		$sock->getfile('mimedefangsave');
	}
	
}

function LoadSettings(){
		$ldap=new clladp();
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=MimeDefangClass)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->global_conf=$hash[0][strtolower('MimeDefangFilter')][0];
			$this->ScriptConf=$hash[0][strtolower('MimeDefangScriptsConf')][0];
			$this->MimeDefangDisclaimer=$hash[0][strtolower('MimeDefangDisclaimer')][0];
			WriteLogs("loadconfig $this->dn MimeDefangFilter=" . strlen($this->global_conf) . " bytes end",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		
		
	}

	
function ParseSettings(){
	
	$ini=new Bs_IniHandler();
	$ini->loadString($this->ScriptConf);
	$this->ScriptConf_array=$ini->_params;
	
	
	$tbl=explode("\n",$this->global_conf);
	WriteLogs("parse ". count($tbl)." lines",__CLASS__.'/'.__FUNCTION__,__FILE__);
	while (list ($num, $ligne) = each ($tbl)){
		//WriteLogs("$num: $ligne",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(preg_match('#\$AdminAddress.+?\'(.+?)\'#',$ligne,$re)){
			$this->main_array["AdminAddress"]=$re[1];
		}
		
		if(preg_match('#\$AdminName\s+=\s+\'(.+?)\'#',$ligne,$re)){
			$this->main_array["AdminName"]=$re[1];
		}

		if(preg_match('#\$DaemonAddress\s+=\s+\'(.+?)\'#',$ligne,$re)){
			$this->main_array["DaemonAddress"]=$re[1];
		}

		if(preg_match('#\$AddWarningsInline.+?([0-9])#',$ligne,$re)){
			$this->main_array["AddWarningsInline"]=$re[1];
		}		
		
		if(preg_match('#\$bad_exts.+?\((.+?)\)#',$ligne,$re)){
			$this->main_array["bad_exts"]=$re[1];
		}			
			

		
	}
	
	$this->BuildDefaults();
	
}


function BuildScriptConf(){
	$ini=new Bs_IniHandler();
	$this->BuildDefaults();
	$ini->_params=$this->ScriptConf_array;
	return $ini->toString();
	
	
}
	

	

function BuildConfig(){
			
			$this->BuildDefaults();
			$conf=$conf ."# -*- Perl -*-\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# mimedefang-filter\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# Suggested minimum-protection filter for Microsoft Windows clients, plus\n";
			$conf=$conf ."# SpamAssassin checks if SpamAssassin is installed.\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# Copyright (C) 2002 Roaring Penguin Software Inc.\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# This program may be distributed under the terms of the GNU General\n";
			$conf=$conf ."# Public License, Version 2, or (at your option) any later version.\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# \$Id$\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# Set administrator's e-mail address here.  The administrator receives\n";
			$conf=$conf ."# quarantine messages and is listed as the contact for site-wide\n";
			$conf=$conf ."# MIMEDefang policy.  A good example would be 'defang-admin@mydomain.com'\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."\$AdminAddress = '{$this->main_array["AdminAddress"]}';\n";
			$conf=$conf ."\$AdminName = '{$this->main_array["AdminName"]}';\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# Set the e-mail address from which MIMEDefang quarantine warnings and\n";
			$conf=$conf ."# user notifications appear to come.  A good example would be\n";
			$conf=$conf ."# 'mimedefang@mydomain.com'.  Make sure to have an alias for this\n";
			$conf=$conf ."# address if you want replies to it to work.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."\$DaemonAddress = '{$this->main_array["DaemonAddress"]}';\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# If you set \$AddWarningsInline to 1, then MIMEDefang tries *very* hard\n";
			$conf=$conf ."# to add warnings directly in the message body (text or html) rather\n";
			$conf=$conf ."# than adding a separate \"WARNING.TXT\" MIME part.  If the message\n";
			$conf=$conf ."# has no text or html part, then a separate MIME part is still used.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."\$AddWarningsInline = {$this->main_array["AddWarningsInline"]};\n";
			$conf=$conf ."\$QuarantineSpamMessages = {$this->ScriptConf_array["BUILD"]["QUARANTINE_SPAM"]};\n";
			$conf=$conf ."\$ArticaAutoWhiteList = {$this->ScriptConf_array["BUILD"]["AUTOWHITE_LIST"]};\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# To enable syslogging of virus and spam activity, add the following\n";
			$conf=$conf ."# to the filter:\n";
			$conf=$conf ."# md_graphdefang_log_enable();\n";
			$conf=$conf ."# You may optionally provide a syslogging facility by passing an\n";
			$conf=$conf ."# argument such as:  md_graphdefang_log_enable('local4');  If you do this, be\n";
			$conf=$conf ."# sure to setup the new syslog facility (probably in /etc/syslog.conf).\n";
			$conf=$conf ."# An optional second argument causes a line of output to be produced\n";
			$conf=$conf ."# for each recipient (if it is 1), or only a single summary line\n";
			$conf=$conf ."# for all recipients (if it is 0.)  The default is 1.\n";
			$conf=$conf ."# Comment this line out to disable logging.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."md_graphdefang_log_enable('mail', 1);\n";
			$conf=$conf ."md_graphdefang_log_enable('spam',1);\n";
			$conf=$conf ."md_graphdefang_log_enable('possible_spam',1);\n";
			$conf=$conf ."md_graphdefang_log_enable('virus',1);\n";
			$conf=$conf ."md_graphdefang_log_enable('suspicious_chars',1);\n";
			$conf=$conf ."md_graphdefang_log_enable('bad_filename',1);\n";
			$conf=$conf ."md_graphdefang_log_enable('non_multipart',1);\n";
			
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# Uncomment this to block messages with more than 50 parts.  This will\n";
			$conf=$conf ."# *NOT* work unless you're using Roaring Penguin's patched version\n";
			$conf=$conf ."# of MIME tools, version MIME-tools-5.411a-RP-Patched-02 or later.\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# WARNING: DO NOT SET THIS VARIABLE unless you're using at least\n";
			$conf=$conf ."# MIME-tools-5.411a-RP-Patched-02; otherwise, your filter will fail.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."#\$MaxMIMEParts = 50;\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# Set various stupid things your mail client does below.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Set the next one if your mail client cannot handle multiple \"inline\"\n";
			$conf=$conf ."# parts.\n";
			$conf=$conf ."\$Stupidity{\"NoMultipleInlines\"} = 0;\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Detect and load Perl modules\n";
			$conf=$conf ."detect_and_load_perl_modules();\n";
			$conf=$conf ."\n";
			$conf=$conf ."# This procedure returns true for entities with bad filenames.\n";
			$conf=$conf ."sub filter_bad_filename  {\n";
			$conf=$conf ."    my(\$entity) = @_;\n";
			$conf=$conf ."    my(\$bad_exts, \$re);\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Bad extensions\n";
			$conf=$conf ."    \$bad_exts = '({$this->main_array["bad_exts"]})';\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Do not allow:\n";
			$conf=$conf ."    # - CLSIDs  {foobarbaz}\n";
			$conf=$conf ."    # - bad extensions (possibly with trailing dots) at end\n";
			$conf=$conf ."    \$re = '\.' . \$bad_exts . '\.*$';\n";
			$conf=$conf ."\n";
			$conf=$conf ."    return 1 if (re_match(\$entity, \$re));\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Look inside ZIP files\n";
			$conf=$conf ."    if (re_match(\$entity, '\.zip$') and\n";
			$conf=$conf ."	\$Features{\"Archive::Zip\"}) {\n";
			$conf=$conf ."	my \$bh = \$entity->bodyhandle();\n";
			$conf=$conf ."	if (defined(\$bh)) {\n";
			$conf=$conf ."	    my \$path = \$bh->path();\n";
			$conf=$conf ."	    if (defined(\$path)) {\n";
			$conf=$conf ."		return re_match_in_zip_directory(\$path, \$re);\n";
			$conf=$conf ."	    }\n";
			$conf=$conf ."	}\n";
			$conf=$conf ."    }\n";
			$conf=$conf ."    return 0;\n";
			$conf=$conf ."}\n";
			$conf=$conf ."\n";
			
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf."sub filter_recipient($$$$$$$$$) {\n";
			$conf=$conf."\tmy (\$recipient, \$sender, \$ip, \$hostname, \$first, \$helo,\$rcpt_mailer, \$rcpt_host, \$rcpt_addr) = @_;\n";
			$conf=$conf."\tmy \$results;\n";
			$conf=$conf."\t\$res=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=stat values=\"\$sender;\$ip;\$rcpt_addr\" --end`;\n";
			$conf=$conf.$this->FilterTroughtInternet();		
			$conf=$conf."\treturn (\"CONTINUE\", \"ok\");\n";
			$conf=$conf."}\n";
			$conf=$conf."#***********************************************************************\n\n";			
			
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# %PROCEDURE: filter_begin\n";
			$conf=$conf ."# %ARGUMENTS:\n";
			$conf=$conf ."#  \$entity -- the parsed MIME::Entity\n";
			$conf=$conf ."# %RETURNS:\n";
			$conf=$conf ."#  Nothing\n";
			$conf=$conf ."# %DESCRIPTION:\n";
			$conf=$conf ."#  Called just before e-mail parts are processed\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub filter_begin {\n";
			$conf=$conf ."    my(\$entity) = @_;\n";
			$conf=$conf ."    # ALWAYS drop messages with suspicious chars in headers\n";
			$conf=$conf ."    if (\$SuspiciousCharsInHeaders) {\n";
			$conf=$conf ."        md_graphdefang_log('suspicious_chars');\n";
			$conf=$conf ."	# action_quarantine_entire_message(\"Message quarantined because of suspicious characters in headers\");\n";
			$conf=$conf ."	# Do NOT allow message to reach recipient(s)\n";
			$conf=$conf ."	return action_discard();\n";
			$conf=$conf ."    }\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Copy original message into work directory as an \"mbox\" file for\n";
			$conf=$conf . $this->ClamAv();
			$conf=$conf ."}\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# %PROCEDURE: filter\n";
			$conf=$conf ."# %ARGUMENTS:\n";
			$conf=$conf ."#  entity -- a Mime::Entity object (see MIME-tools documentation for details)\n";
			$conf=$conf ."#  fname -- the suggested filename, taken from the MIME Content-Disposition:\n";
			$conf=$conf ."#           header.  If no filename was suggested, then fname is \"\"\n";
			$conf=$conf ."#  ext -- the file extension (everything from the last period in the name\n";
			$conf=$conf ."#         to the end of the name, including the period.)\n";
			$conf=$conf ."#  type -- the MIME type, taken from the Content-Type: header.\n";
			$conf=$conf ."#\n";
			$conf=$conf ."#  NOTE: There are two likely and one unlikely place for a filename to\n";
			$conf=$conf ."#  appear in a MIME message:  In Content-Disposition: filename, in\n";
			$conf=$conf ."#  Content-Type: name, and in Content-Description.  If you are paranoid,\n";
			$conf=$conf ."#  you will use the re_match and re_match_ext functions, which return true\n";
			$conf=$conf ."#  if ANY of these possibilities match.  re_match checks the whole name;\n";
			$conf=$conf ."#  re_match_ext checks the extension.  See the sample filter below for usage.\n";
			$conf=$conf ."# %RETURNS:\n";
			$conf=$conf ."#  Nothing\n";
			$conf=$conf ."# %DESCRIPTION:\n";
			$conf=$conf ."#  This function is called once for each part of a MIME message.\n";
			$conf=$conf ."#  There are many action_*() routines which can decide the fate\n";
			$conf=$conf ."#  of each part; see the mimedefang-filter man page.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub filter {\n";
			$conf=$conf ."use Cwd;\n";
			$conf=$conf ."my \$path;\n";
			$conf=$conf ."my \$size;\n";			
			$conf=$conf ."my \$results;\n";
			$conf=$conf ."my \$BigHtmlDirectory=\"/var/spool/artica-filter/bightml\";\n";			
			$conf=$conf ."\$path=getcwd();\n";
			$conf=$conf ."    my(\$entity, \$fname, \$ext, \$type) = @_;\n";
			$conf=$conf ."\n";
			$conf=$conf ."\treturn if message_rejected(); # Avoid unnecessary work\n";
			$conf=$conf ."\n";			
			$conf=$conf ."\n";
			$conf=$conf ."\t# Block message/partial parts\n";
			$conf=$conf ."    if (lc(\$type) eq \"message/partial\") {\n";
			$conf=$conf ."        md_graphdefang_log('message/partial');\n";
			$conf=$conf ."	action_bounce(\"MIME type message/partial not accepted here\");\n";
			$conf=$conf ."	return action_discard();\n";
			$conf=$conf ."    }\n";
			$conf=$conf ."\n";
			$conf=$conf ."    if (filter_bad_filename(\$entity)) {\n";
			$conf=$conf ."        md_graphdefang_log('bad_filename', \$fname, \$type);\n";
			$conf=$conf ."	return action_drop_with_warning(\"An attachment named \$fname was removed from this document as it\nconstituted a security hazard.  If you require this document, please contact\nthe sender and arrange an alternate means of receiving it.\n\");\n";
			$conf=$conf ."    }\n";
			$conf=$conf ."\n";
			$conf=$conf . $this->ConvertTNEF();
			$conf=$conf . $this->BightmlFeature();
			$conf=$conf ."    return action_accept();\n";
			$conf=$conf ."}\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# %PROCEDURE: filter_multipart\n";
			$conf=$conf ."# %ARGUMENTS:\n";
			$conf=$conf ."#  entity -- a Mime::Entity object (see MIME-tools documentation for details)\n";
			$conf=$conf ."#  fname -- the suggested filename, taken from the MIME Content-Disposition:\n";
			$conf=$conf ."#           header.  If no filename was suggested, then fname is \"\"\n";
			$conf=$conf ."#  ext -- the file extension (everything from the last period in the name\n";
			$conf=$conf ."#         to the end of the name, including the period.)\n";
			$conf=$conf ."#  type -- the MIME type, taken from the Content-Type: header.\n";
			$conf=$conf ."# %RETURNS:\n";
			$conf=$conf ."#  Nothing\n";
			$conf=$conf ."# %DESCRIPTION:\n";
			$conf=$conf ."#  This is called for multipart \"container\" parts such as message/rfc822.\n";
			$conf=$conf ."#  You cannot replace the body (because multipart parts have no body),\n";
			$conf=$conf ."#  but you should check for bad filenames.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub filter_multipart {\n";
			$conf=$conf ."    my(\$entity, \$fname, \$ext, \$type) = @_;\n";
			$conf=$conf ."\n";
			$conf=$conf ."    return if message_rejected(); # Avoid unnecessary work\n";
			$conf=$conf ."\n";
			$conf=$conf ."    if (filter_bad_filename(\$entity)) {\n";
			$conf=$conf ."        md_graphdefang_log('bad_filename', \$fname, \$type);\n";
			$conf=$conf ."	action_notify_administrator(\"A MULTIPART attachment of type \$type, named \$fname was dropped.\n\");\n";
			$conf=$conf ."	return action_drop_with_warning(\"An attachment of type \$type, named \$fname was removed from this document as it\nconstituted a security hazard.  If you require this document, please contact\nthe sender and arrange an alternate means of receiving it.\n\");\n";
			$conf=$conf ."    }\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Block message/partial parts\n";
			$conf=$conf ."    if (lc(\$type) eq \"message/partial\") {\n";
			$conf=$conf ."        md_graphdefang_log('message/partial');\n";
			$conf=$conf ."	action_bounce(\"MIME type message/partial not accepted here\");\n";
			$conf=$conf ."	return;\n";
			$conf=$conf ."    }\n";
			$conf=$conf ."\n";
			$conf=$conf ."    return action_accept();\n";
			$conf=$conf ."}\n";
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."# %PROCEDURE: defang_warning\n";
			$conf=$conf ."# %ARGUMENTS:\n";
			$conf=$conf ."#  oldfname -- the old file name of an attachment\n";
			$conf=$conf ."#  fname -- the new \"defanged\" name\n";
			$conf=$conf ."# %RETURNS:\n";
			$conf=$conf ."#  A warning message\n";
			$conf=$conf ."# %DESCRIPTION:\n";
			$conf=$conf ."#  This function customizes the warning message when an attachment\n";
			$conf=$conf ."#  is defanged.\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub defang_warning {\n";
			$conf=$conf ."    my(\$oldfname, \$fname) = @_;\n";
			$conf=$conf ."    return\n";
			$conf=$conf ."	\"An attachment named '\$oldfname' was converted to '\$fname'.\\n\" .\n";
			$conf=$conf ."	\"To recover the file, right-click on the attachment and Save As\\n\" .\n";
			$conf=$conf ."	\"'\$oldfname'\\n\";\n";
			$conf=$conf ."}\n";
			$conf=$conf ."\n";
			$conf=$conf ."# If SpamAssassin found SPAM, append report.  We do it as a separate\n";
			$conf=$conf ."# attachment of type text/plain\n";
			
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub filter_end {\n";
			$conf=$conf ."\tuse Cwd;\n";
			$conf=$conf ."\tmy \$path;\n";
			$conf=$conf ."\tmy \$results;\n";
			$conf=$conf ."\tmy \$XSpamTestHeader;\n";
			$conf=$conf ."\tmy \$XSpamTestRate;\n";
			$conf=$conf ."\tmy \$GotoQuarantine;\n";					
			$conf=$conf ."\tmy \$bogores;\n";			
			$conf=$conf ."\t\$path=getcwd();\n";			
			$conf=$conf ."\tmy(\$entity) = @_;\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # If you want quarantine reports, uncomment next line\n";
			$conf=$conf ."    # send_quarantine_notifications();\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # IMPORTANT NOTE:  YOU MUST CALL send_quarantine_notifications() AFTER\n";
			$conf=$conf ."    # ANY PARTS HAVE BEEN QUARANTINED.  SO IF YOU MODIFY THIS FILTER TO\n";
			$conf=$conf ."    # QUARANTINE SPAM, REWORK THE LOGIC TO CALL send_quarantine_notifications()\n";
			$conf=$conf ."    # AT THE END!!!\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # No sense doing any extra work\n";
			$conf=$conf ."    return if message_rejected();\n";
			$conf=$conf . $this->QuarantinePointer();
			$conf=$conf ."\n\n";
					
			$conf=$conf . $this->Kaspersky_antispam();
			$conf=$conf . $this->bogofilter_header();
			$conf=$conf . $this->SpamAssassin();
			$conf=$conf ."\n";
			$conf=$conf ."    # I HATE HTML MAIL!  If there's a multipart/alternative with both\n";
			$conf=$conf ."    # text/plain and text/html parts, nuke the text/html.  Thanks for\n";
			$conf=$conf ."    # wasting our disk space and bandwidth...\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # If you want to strip out HTML parts if there is a corresponding\n";
			$conf=$conf ."    # plain-text part, uncomment the next line.\n";
			$conf=$conf ."    # remove_redundant_html_parts(\$entity);\n";
			$conf=$conf ."\n";
			$conf=$conf ."    md_graphdefang_log('mail_in');\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Deal with malformed MIME.\n";
			$conf=$conf ."    # Some viruses produce malformed MIME messages that are misinterpreted\n";
			$conf=$conf ."    # by mail clients.  They also might slip under the radar of MIMEDefang.\n";
			$conf=$conf ."    # If you are worried about this, you should canonicalize all\n";
			$conf=$conf ."    # e-mail by uncommenting the action_rebuild() line.  This will\n";
			$conf=$conf ."    # force _all_ messages to be reconstructed as valid MIME.  It will\n";
			$conf=$conf ."    # increase the load on your server, and might break messages produced\n";
			$conf=$conf ."    # by marginal software.  Your call.\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # action_rebuild();\n";
			$conf=$conf ."\n";
			$conf=$conf . $this->BackupFeature();
			$conf=$conf . $this->BuildDisclaimer();
			$conf=$conf ."}\n";
			$conf=$conf ."\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub ParseUseradd{\n";
			$conf=$conf ."\tmy \$data_file=shift;\n";
			$conf=$conf ."\tif(open(DAT, \$data_file)){\n";
			$conf=$conf ."\twhile( <DAT> ) {\n";
			$conf=$conf ."\t\tmy(\$line) = \$_;\n";
			$conf=$conf ."\t\tchomp(\$line);\n";
			$conf=$conf ."\t\tif (\$line =~/dup:(.+)/){\n";
			$conf=$conf ."\t\t\tadd_recipient(\$1);\n";
			$conf=$conf ."\t\t}\n";
			$conf=$conf ."\t}\n";
			$conf=$conf ."\tclose(DAT);\n";
			$conf=$conf ."\t}\n";
			$conf=$conf ."}\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub ReadFileIntoString{\n";
			$conf=$conf ."   my \$results;\n";
			$conf=$conf ."   my \$data_file=shift;\n";
			$conf=$conf ."    if(open(DAT, \$data_file)){\n";
			$conf=$conf ."	while( <DAT> ) {\n";
			$conf=$conf ."	   \$results=\$results.\$_;\n";
			$conf=$conf ."	}		\n";
			$conf=$conf ."	close(DAT);\n";
			$conf=$conf ."     }\n";
			$conf=$conf ."\n";
			$conf=$conf ."return \$results;\n";
			$conf=$conf ."}\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub ReadKeyIntoFile{\n";
			$conf=$conf ."   my \$results;\n";
			$conf=$conf ."   my \$data_file=shift;\n";
			$conf=$conf ."   my \$key=shift;\n";
			$conf=$conf ."    if(open(DAT, \$data_file)){\n";
			$conf=$conf ."	while( <DAT> ) {\n";
			$conf=$conf ."	   chomp; \n";
			$conf=$conf ."	   if(\$_=~/\$key=(.+)/){\n";
			$conf=$conf ."		close(DAT);\n";
			$conf=$conf ."		return \$1;\n";
			$conf=$conf ."	   }\n";
			$conf=$conf ."\n";
			$conf=$conf ."	}		\n";
			$conf=$conf ."	close(DAT);\n";
			$conf=$conf ."     }\n";
			$conf=$conf ."\n";
			$conf=$conf ."return \$results;\n";
			$conf=$conf ."}\n";
			$conf=$conf ."#***********************************************************************\n";
			$conf=$conf ."sub WriteToFile{\n";
			$conf=$conf ."\tmy \$filepath=shift;\n";
			$conf=$conf ."\tmy \$line=shift;\n";
			$conf=$conf ."\topen (MYFILE, \">>\$filepath\");\n";
			$conf=$conf ."\tprint MYFILE \"\$line\\n\";\n";
			$conf=$conf ."\tclose (MYFILE)\n";
			$conf=$conf ."\n";
			$conf=$conf ."}\n";
			$conf=$conf ."#************************************************************************\n";						
			
			$conf=$conf ."# DO NOT delete the next line, or Perl will complain.\n";
			$conf=$conf ."1;\n";
			return $conf;
	
}

function bogofilter_header(){
if($this->ScriptConf_array["BUILD"]["ENABLE_BOGO"]<>1){return null;}	
$conf=$conf ."#------------------------------------ Bogofilter --------------------------------------\n";
$conf=$conf ."\t\$bogores=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=bogo --end`;\n";
$conf=$conf ."\tif(length($bogores)>0){\n";
$conf=$conf ."\t\taction_add_header(\"X-Bogosity\", \$bogores);\n";
$conf=$conf ."\t}\n";
$conf=$conf ."# --------------------------------------------------------------------------------------------\n\n";
return $conf;
}


function Kaspersky_antispam(){
		$maxrate=$this->ScriptConf_array["BUILD"]["DISCARD_SPAM_KAS3"];
		$conf=$conf ."# Checking if Kaspersky Anti-spam has already detected has spam.-------------------------------\n";
		$conf=$conf ."\tif (open(IN,\"<./HEADERS\")) {\n";
		$conf=$conf ."        \t\$head = MIME::Head->read(\*IN);\n";
		$conf=$conf ."\t\t\$XSpamTestRate=\$head->get('X-SpamTest-Rate');\n\n";
		
		$conf=$conf ."\t\tif(\$XSpamTestRate>$maxrate){\n";
		$conf=$conf ."\t\t\tmd_graphdefang_log('spam', \$XSpamTestRate, \$RelayAddr);\n";
if($this->ScriptConf_array["BUILD"]["ENABLE_BOGO"]==1){
		$conf=$conf ."\t\t\t\$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=learn_bogo --end`;\n";
	}		
		$conf=$conf ."\t\t\taction_discard();\n";
		$conf=$conf ."\t\t\treturn;\n";
		$conf=$conf ."\t\t}\n\n";
		
		$conf=$conf ."\t\tif(\$XSpamTestRate>70){\$GotoQuarantine=1;}\n";
		$conf=$conf ."\t\t\$XSpamTestHeader=\$head->get('X-SpamTest-Header');\n";
		$conf=$conf ."\t\tif(\$XSpamTestHeader=~/spam/){\$GotoQuarantine=1;}\n";
		$conf=$conf ."\t\tif(\$GotoQuarantine==1){\n";
		$conf=$conf ."\t\t\tmd_syslog('info', \"\$MsgID: Kaspersky anti-spam found this mail as spam (Rate \$XSpamTestRate - \$XSpamTestHeader)\");\t\n";
		$conf=$conf ."\t\t\tmd_graphdefang_log('spam', \$XSpamTestRate, \$RelayAddr);\n";
		if($this->ScriptConf_array["BUILD"]["ENABLE_BOGO"]==1){
		$conf=$conf ."\t\t\t\$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=learn_bogo --end`;\n";
		}
		$conf=$conf ."\t\t\tif(\$QuarantineSpamMessages==1){\n";
		$conf=$conf ."\t\t\taction_quarantine_entire_message();\n";
		$conf=$conf ."\t\t\taction_discard();\n";
		$conf=$conf ."\t\t\treturn;\n";
		$conf=$conf ."\t\t\t}\n";
		$conf=$conf ."\t\treturn;}\n";
		$conf=$conf ."\t}\n";
		$conf=$conf ."\n";
		$conf=$conf ."# --------------------------------------------------------------------------------------------\n\n";
return $conf;	
	
}


function SpamAssassin(){
			if($this->ScriptConf_array["BUILD"]["ENABLE_SA"]<>1){return null;}
			$score=$this->ScriptConf_array["BUILD"]["SA_SCORE"];
			$maxrate=$this->ScriptConf_array["BUILD"]["DISCARD_SPAM_SPAMASS"];
			$conf=$conf ."\t# Spam checks if SpamAssassin is installed\n";
			$conf=$conf ."\tif (\$Features{\"SpamAssassin\"}) {\n";
			$conf=$conf ."\t\tif (-s \"./INPUTMSG\" < 100*1024) {\n";
			$conf=$conf ."\t\t# Only scan messages smaller than 100kB.  Larger messages\n";
			$conf=$conf ."\t\t# are extremely unlikely to be spam, and SpamAssassin is\n";
			$conf=$conf ."\t\t# dreadfully slow on very large messages.\n";
			$conf=$conf ."\t\tmy(\$hits, \$req, \$names, \$report) = spam_assassin_check();\n";
			$conf=$conf ."\t\tmy(\$score);\n";
			$conf=$conf ."\t\tif (\$hits < $score) {\n";
			$conf=$conf ."\t\t\t\$score = \"*\" x int(\$hits);\n";
			$conf=$conf ."\t\t} else {\n";
			$conf=$conf ."\t\t\t\$score = \"*\" x $score;\n";
			$conf=$conf ."\t\t}\n";
			$conf=$conf ."\t\t# We add a header which looks like this:\n";
			$conf=$conf ."\t\t# X-Spam-Score: 6.8 (******) NAME_OF_TEST,NAME_OF_TEST\n";
			$conf=$conf ."\t\t# The number of asterisks in parens is the integer part\n";
			$conf=$conf ."\t\t# of the spam score clamped to a maximum of $score.\n";
			$conf=$conf ."\t\t# MUA filters can easily be written to trigger on a\n";
			$conf=$conf ."\t\t# minimum number of asterisks...\n\n";
			
			$conf=$conf ."\t\tif (\$hits > $maxrate) {\n";
			$conf=$conf ."\t\t\tmd_graphdefang_log('spam', \$hits, \$RelayAddr);\n";
			if($this->ScriptConf_array["BUILD"]["ENABLE_BOGO"]==1){$conf=$conf ."\t\t\t\$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=learn_bogo --end`;\n";}										
			$conf=$conf ."\t\t\taction_discard();\n";
			$conf=$conf ."\t\t\treturn;\n";									
			$conf=$conf ."\t\t}\n\n";			
			$conf=$conf ."\t\tif (\$hits >= \$req) {\n";
			if($this->ScriptConf_array["BUILD"]["ENABLE_BOGO"]==1){$conf=$conf ."\t\t\t\$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=learn_bogo --end`;\n";}			
			$conf=$conf ."\t\t\taction_change_header(\"X-Spam-Score\", \"\$hits (\$score) \$names\");\n";
			$conf=$conf ."\t\t\tmd_graphdefang_log('spam', \$hits, \$RelayAddr);\n";
			$conf=$conf ."\t\t\tif(\$QuarantineSpamMessages==1){\n";
			$conf=$conf ."\t\t\t\taction_quarantine_entire_message();\n";
			$conf=$conf ."\t\t\t\tWriteToFile(get_quarantine_dir().\"/HEADERS\",\"X-Spam-Score: \$hits (\$score) \$names\");\n";
			$conf=$conf ."\t\t\t\tif(length(\$bogores)>0){WriteToFile(get_quarantine_dir().\"/HEADERS\",\"X-Bogosity: \$bogores\");}\n";
			$conf=$conf. $this->SpamAssRedirectTomail();
			$conf=$conf ."\t\t\t\taction_discard();\n";		
			$conf=$conf ."\t\t\t\treturn;\n\t\t\t}\n";
			$conf=$conf ."\n";
			$conf=$conf ."\t\t\t# If you find the SA report useful, add it, I guess...\n";
			$conf=$conf ."\t\t\taction_add_part(\$entity, \"text/plain\", \"-suggest\",\n";
			$conf=$conf ."\t\t\t\"\$report\n\",\n";
			$conf=$conf ."\t\t\t\"SpamAssassinReport.txt\", \"inline\");\n";
			$conf=$conf. $this->SpamAssRedirectTomail();
			$conf=$conf ."\t\treturn;\n";
			$conf=$conf ."\t\t} else {\n";
			$conf=$conf ."\t\t\t# Delete any existing X-Spam-Score header?\n";
			$conf=$conf ."\t\t\t#action_delete_header(\"X-Spam-Score\");\n";
			$conf=$conf ."\t\t}\n";
			$conf=$conf ."\t}\n";
			$conf=$conf ." \t}\n";
			
			return $conf;	
	
}

function ClamAV(){
			if($this->ScriptConf_array["BUILD"]["ENABLE_AV"]<>1){return null;}
			$conf=$conf ."    # virus-scanning\n";
			$conf=$conf ."    md_copy_orig_msg_to_work_dir_as_mbox_file();\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Scan for viruses if any virus-scanners are installed\n";
			$conf=$conf ."    my(\$code, \$category, \$action) = message_contains_virus();\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Lower level of paranoia - only looks for actual viruses\n";
			$conf=$conf ."    \$FoundVirus = (\$category eq \"virus\");\n";
			$conf=$conf ."\n";
			$conf=$conf ."    # Higher level of paranoia - takes care of \"suspicious\" objects\n";
			$conf=$conf ."    # \$FoundVirus = (\$action eq \"quarantine\");\n";
			$conf=$conf ."\n";
			$conf=$conf ."    if (\$FoundVirus) {\n";
			$conf=$conf ."	md_graphdefang_log('virus', \$VirusName, \$RelayAddr);\n";
			$conf=$conf ."	md_syslog('warning', \"Discarding because of virus \$VirusName\");\n";
			$conf=$conf ."	return action_discard();\n";
			$conf=$conf ."    }\n";
			$conf=$conf ."\n";
			$conf=$conf ."    if (\$action eq \"tempfail\") {\n";
			$conf=$conf ."	action_tempfail(\"Problem running virus-scanner\");\n";
			$conf=$conf ."	md_syslog('warning', \"Problem running virus scanner: code=\$code, category=\$category, action=\$action\");\n";
			$conf=$conf ."    }\n";
			return $conf;		
	
}

function BuildDisclaimer(){
	if($this->ScriptConf_array["BUILD"]["ENABLE_DISCLAIMER"]<>1){return null;}
	
	$disclaimer_text=strip_tags($this->Disclaimer);
	$disclaimer_text=str_replace("\n",'\n',$disclaimer_text);
	$Disclaimer=nl2br($this->Disclaimer);
	$Disclaimer=str_replace("\n","",$Disclaimer);
	
	$tbl=explode(";",$this->ScriptConf_array["BUILD"]["DICLAIMER_ADDR"]);
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne<>null){
			$ligne=str_replace('.','\.',$ligne);
			$ifc[]="(\$RelayAddr=~/^$ligne/)";
		}
	}
	
	
	
	$a=$a."\tappend_text_boilerplate(\$entity, \"$disclaimer_text\", 0);\n";
	$a=$a."\tappend_html_boilerplate(\$entity, \"$Disclaimer\", 0);\n";
	
	
	if(count($ifc)>0){
		$conf=$conf . "\tif(\n\t";
		$conf=$conf . implode("||",$ifc);
		$conf=$conf ."){\n$a\t}\n";
		
	}else{
		$conf=$a;
	}
	
	return $conf;
	}


function ConvertTNEF(){
	if($this->ScriptConf_array["BUILD"]["CONVERT_TNEF"]<>1){return null;}
	$conf=$conf."\tif (lc(\$type) eq \"application/ms-tnef\"  or lc(\$fname) eq \"winmail.dat\" ) {\n";
	$conf=$conf."\t\tuse Convert::TNEF;\n";
	$conf=$conf."\t\tmy \$tnef = Convert::TNEF->read_ent(\$entity)\n";
	$conf=$conf."\t\tor die \$Convert::TNEF::errstr;\n";
	$conf=$conf."\t\tfor (\$tnef->attachments) {\n";
	$conf=$conf."\t\t\taction_add_part(\$entity, \"text/plain\", \"base64\", \$_->data, \$_->longname, \"attachment\");\n";
	$conf=$conf."\t\t\tmd_graphdefang_log('tnef_ext', \$_->longname);\n";
	$conf=$conf."\t\t}\n";
	$conf=$conf."\t# Remark this if you want still want to keep the winmail.dat file\n";
	$conf=$conf."\treturn action_drop();\n";
	$conf=$conf."\t}\n";	
	return $conf;
	
}

	function SpamAssRedirectTomail(){
		if($this->ScriptConf_array["BUILD"]["SA_RCPT_ENABLE"]<>1){return null;}
			$conf=$conf ."\n#-------------------- Spamasssin redirecto to mail --------------------------------------\n";
			$conf=$conf."\n\t# Add a header with original recipients, just for info\n";
			$conf=$conf."\taction_add_header(\"X-Orig-Rcpts\", join(\", \", @Recipients));\n";
			$conf=$conf."\n";
			$conf=$conf."\t# Remove original recipients\n";
			$conf=$conf."\tforeach \$recip (@Recipients) {\n";
			$conf=$conf."\t\tdelete_recipient(\$recip);\n";
			$conf=$conf."\t}\n";
			$conf=$conf."\t# Send to spam address\n";
			$conf=$conf."\tadd_recipient('{$this->ScriptConf_array["BUILD"]["SA_RCPT_EMAIL"]}');\n";
			$conf=$conf ."# --------------------------------------------------------------------------------------------\n\n";
		return $conf;
	
	}


	function BightmlFeature(){
			if($this->ScriptConf_array["BUILD"]["BIGHTML_ENABLED"]==0){return null;}
			$conf=$conf ."\n#------------------------------------ Bightml feature --------------------------------------\n";
			$conf=$conf ."\n";
			$conf=$conf ."   if (defined(\$fname)) {\n";
			$conf=$conf ."	\$size = (stat(\$entity->bodyhandle->path))[7];\n";
			$conf=$conf ."        \$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=htmlsize --ext=\$ext --type=\$type --size=\$size --end`;\n";
			$conf=$conf ."	if(\$results=~m/TRUE/ms){\n";
			$conf=$conf ."		my \$bodyText=ReadFileIntoString(\"./BigMailHtmlBody\");\n";
			$conf=$conf ."	        my \$url=ReadKeyIntoFile(\"./BigMailHtmlConfig\",\"hostname\");\n";
			$conf=$conf ."	        my \$prependSubject=ReadKeyIntoFile(\"./BigMailHtmlConfig\",\"prependsubject\");\n";
			$conf=$conf ."	        my \$ou=ReadKeyIntoFile(\"./BigMailHtmlConfig\",\"ou\");\n";
			$conf=$conf ."           	\$bodyText=\"\$bodyText\\n\\t_URL_\"; \n";
			$conf=$conf ."		if (length(\$prependSubject)>0){\n";
			$conf=$conf ."			action_change_header(\"Subject\", \"\$prependSubject \$Subject\");\n";
			$conf=$conf ."			}\n";
			$conf=$conf ."	       \n";
			$conf=$conf ."		return action_replace_with_url(\$entity,\"\$BigHtmlDirectory\",\$url,\$bodyText);\n";
			$conf=$conf ."		\n";
			$conf=$conf ."	}\n";
			$conf=$conf ."\n";
			$conf=$conf ."	}\n";
			$conf=$conf ."\n";
			$conf=$conf ."# --------------------------------------------------------------------------------------------\n\n";
			return $conf;
			}


	function BackupFeature(){
		if($this->ScriptConf_array["BUILD"]["BACKUP_ENABLED"]==1){
		$conf=$conf . "\n\n#------------------------------------ Backup feature --------------------------------------\n";
		$conf=$conf . "\t\$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=backup --end`;\n";
		$conf=$conf . "# --------------------------------------------------------------------------------------------\n\n";
		}
		$conf=$conf .$this->RecipientTranslation();
		
		return $conf;
		
		}
		
		
function RecipientTranslation(){
if($this->ScriptConf_array["BUILD"]["RECIPIENT_TRANSFORM"]==0){return null;}	
$conf=$conf ."#------------------------------------ Recipients Translations features --------------------------------------\n";
$conf=$conf ."\t\$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=translations --end`;\n";
$conf=$conf ."\tParseUseradd(\"\$path/toadd\");\n";
$conf=$conf ."# --------------------------------------------------------------------------------------------\n\n";	
return $conf;	
}
		
		
function QuarantinePointer(){
	
$conf=$conf ."# Checking if Message should not pass trought filters.--------------------------------------\n";
$conf=$conf ."\tif (open(IN,\"<./HEADERS\")) {\n";
$conf=$conf ."\t\t\$head = MIME::Head->read(\*IN);\n";
$conf=$conf ."\t\t\$results=\$head->get('X-Artica');\n";
$conf=$conf ."\t\tif(\$results=~/PASS/){\n";
$conf=$conf ."\t\t\tmd_syslog('info', \"\$MsgID: X-Artica='\$results' stopping filters...\");\n";
$conf=$conf ."\t\t\tmd_graphdefang_log('mail_in');\n";
$conf=$conf. $this->BackupFeature();
$conf=$conf ."\t\t\treturn;\n";
$conf=$conf ."\t\t}\n";
$conf=$conf ."\t}\n\n";
$conf=$conf ."# --------------------------------------------------------------------------------------------\n\n";
$conf=$conf ."# Checking Auto-white list features.--------------------------------------------------------\n";
$conf=$conf."\tif(\$ArticaAutoWhiteList==1){\n";
$conf=$conf."\t\t\$results=`/usr/share/artica-postfix/bin/artica-mimedefang-pipe --path=\$path --perform=whitelist --end`;\n";
$conf=$conf."\t\tif(\$results=~/whitelisted/){\n";
$conf=$conf."\t\t\tmd_syslog('info', \"\$MsgID: whitelisted stopping filters...\");\n";
$conf=$conf."\t\t\tmd_graphdefang_log('mail_in');\n";
$conf=$conf."\t\t\taction_add_header(\"X-Artica\", \$results);\n";
$conf=$conf. $this->BackupFeature();
$conf=$conf."\t\t\treturn;\n";
$conf=$conf."\t\t}\n";
$conf=$conf."\t}\n";
$conf=$conf ."# --------------------------------------------------------------------------------------------\n\n";
return $conf;	
	
}

function FilterTroughtInternet(){
if($this->ScriptConf_array["BUILD"]["FILTER_LOCAL_SENDER_ENABLED"]==0){return null;}	
$conf=$conf."\n\t#------------------------------------ trought foreign domains ? --------------------------------------\n";
$conf=$conf."\t\$results=`/usr/share/artica-postfix/bin/artica-filter-smtp-out --path=\"\$path\" --from=\"\$sender\" --to=\"\$rcpt_addr\" --mailer=\"\$rcpt_addr\" --recipient=\"\$recipient\" --ip=\"\$ip\" --first=\"\$first\" --end`;\n";
$conf=$conf."\tif(\$results=~m/TRUE/ms){\n";
$conf=$conf."\t\treturn (\"REJECT\", \"Sorry; You cannot send trought foreign domains.\");\n";
$conf=$conf."\t}\n";
$conf=$conf."\t# --------------------------------------------------------------------------------------------\n\n";
return $conf;
	
}
		
		
		
	function BuildMysqlDatabases(){
		
		$mysql=new mysql();
		if(!$mysql->DATABASE_EXISTS('artica_events')){$mysql->CREATE_DATABASE('artica_events');}
		
		$sql="CREATE TABLE IF NOT EXISTS `mails_events` (
				  `ID` int(20) NOT NULL auto_increment,
				  `mailfrom` varchar(255) NOT NULL,
				  `rcpt_to` varchar(255) NOT NULL,
				  `zDate` datetime NOT NULL,
				  `mailfrom_domain` varchar(255) NOT NULL,
				  `rcpt_to_domain` varchar(255) default NULL,
				  `relayhost` varchar(20) NOT NULL,
				  PRIMARY KEY  (`ID`),
				  KEY `rcpt_to_domain` (`rcpt_to_domain`),
				  KEY `rcpt_to` (`rcpt_to`),
				  KEY `zDate` (`zDate`),
				  KEY `mailfrom_domain` (`mailfrom_domain`),
				  KEY `relayhost` (`relayhost`)
				) AUTO_INCREMENT=2 ;";
		

		$mysql->QUERY_SQL($sql,'artica_events');
		
		
	}
	
	
	
}
	

?>