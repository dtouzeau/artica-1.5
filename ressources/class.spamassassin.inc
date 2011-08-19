<?php
if(!is_file("class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once('class.ldap.inc');}


class spamassassin{
	var $rewrite_header;
	var $main_array=array();
	var $required_score="5.0";
	var $rewrite_headers=array();
	var $trusted_networks=array();
	var $add_headers=array();
	var $dn;
	var $global_conf;
	var $block_with_required_score=null;
	var $CountriesCode;
	var $main_country=array();
	var $EnableSpamassassinDnsEval=0;
	
	
	
function spamassassin(){
	
	$this->LoadConfig();
	$this->ParseConfig();
	$this->Parse_rewrite_headers();
	$this->Parse_add_headers();
	$this->Parse_trusted_networks();
	$this->CountriesCode=$this->LoadCountriesCodes();
	$this->LoadCountries();
	$sock=new sockets();
	$this->EnableSpamassassinDnsEval=$sock->GET_INFO("EnableSpamassassinDnsEval");
	if(!is_numeric($this->EnableSpamassassinDnsEval)){$this->EnableSpamassassinDnsEval=0;}
	
}
	


function ParseConfig(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^([a-z_0-9]+)\s+(.+)#',$ligne,$re)){
			$this->main_array[$re[1]]=$re[2];
		}
		
	}
	$this->buildDefault();
}

function Parse_rewrite_headers(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^rewrite_header\s+(Subject|From|To)\s+(.+)#',$ligne,$re)){
			$this->rewrite_headers[$re[1]]=$re[2];
		}
		
		if(preg_match('#milter_block_with_required_score:([0-9\.]+)#',$ligne,$re)){
			$this->block_with_required_score=$re[1];
		}
		
	}
}
function Parse_add_headers(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^add_header\s+(spam|ham|all)\s+(.+)#',$ligne,$re)){
			$this->add_headers[$re[1]]=$re[2];
		}
	}
}

function Parse_trusted_networks(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^trusted_networks\s+(.+)#',$ligne,$re)){
			$ARR[]=$re[1];
		}
	}

	
	
	
	
	$this->trusted_networks=$ARR;
}

private function parse_internal_networks(){
	$ldap=new clladp();
	$nets=$ldap->load_mynetworks();
	while (list ($i, $mask) = each ($nets) ){
		if(preg_match("#^127\.0\.0\.#",$mask)){continue;}
		$ARR[]=$mask;
	}
	
	
	return "internal_networks " .@implode(" ",$ARR);
}

function SaveToLdap(){
	$sock=new sockets();
	$sock->SaveConfigFile($this->BuildConfig(),"SpamAssassinConfFile");
	$tpl=new templates();
	$this->SaveRelayCountry();
	$sock=new sockets();
	$this->spamassassin_reloadAndSave();
	
	
}



function buildDefault(){
	
	if($this->main_array["required_score"]==null){$this->main_array["required_score"]="5.0";}
	if($this->main_array["report_safe"]==null){$this->main_array["report_safe"]="1";}
	if($this->main_array["bayes_auto_learn"]==null){$this->main_array["bayes_auto_learn"]="1";}
	if($this->main_array["use_bayes"]==null){$this->main_array["use_bayes"]="1";}
	if($this->main_array["use_terse_report"]==null){$this->main_array["use_terse_report"]="1";}
	if($this->main_array["use_auto_whitelist"]==null){$this->main_array["use_auto_whitelist"]="1";}
	if(!is_numeric($this->main_array["use_razor2"])){$this->main_array["use_razor2"]=0;}
	if(!is_numeric($this->main_array["use_pyzor"])){$this->main_array["use_pyzor"]=0;}
	if($this->block_with_required_score==null){$this->block_with_required_score=10;}
	
	$users=new usersMenus();
	if($users->razor_installed){
		$this->main_array["razor_config"]=$users->razor_config;
	}else{
		$this->main_array["use_razor2"]=0;
	}
	
	if(!$users->pyzor_installed){
		$this->main_array["use_pyzor"]=0;
	}	
	
	
	
	
	
}


function spamassassin_reloadAndSave(){
	$sock=new sockets();
	
	$EnableAmavisDaemon=$sock->GET_INFO("EnableAmavisDaemon");
	if($EnableAmavisDaemon==1){
		$sock->getFrameWork("cmd.php?spamass-build=yes");
	}else{		
		$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");
	}
	
	
}


function LoadCountries(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("RelayCountryPlugin"));
	$this->main_country=$ini->_params;
	
}

function SaveRelayCountry(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->_params=$this->main_country;
	$sock->SaveConfigFile($ini->toString(),"RelayCountryPlugin");
	$this->spamassassin_reloadAndSave();
	}


	function LoadConfig(){
		$sock=new sockets();
		$this->global_conf=$sock->GET_INFO("SpamAssassinConfFile");
	}
	
	
	function BuildConfig(){
				
				$this->buildDefault();
		
				
				$conf=$conf ."# This is the right place to customize your installation of SpamAssassin.\n";
				$conf=$conf ."#\n";
				$conf=$conf ."# See 'perldoc Mail::SpamAssassin::Conf' for details of what can be\n";
				$conf=$conf ."# tweaked.\n";
				$conf=$conf ."#\n";
				$conf=$conf ."# Only a small subset of options are listed below\n";
				$conf=$conf ."#Used by spamass-milter:\n";
				$conf=$conf ."#milter_block_with_required_score:$this->block_with_required_score";
				$conf=$conf ."#\n";
				$conf=$conf ."# more infos in http://spamassassin.apache.org/full/3.2.x/doc/Mail_SpamAssassin_Conf.html\n";
				$conf=$conf ."###########################################################################\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Add *****SPAM***** to the Subject header of spam e-mails\n";
				$conf=$conf ."add_header spam Flag _YESNOCAPS_\n";
				$conf=$conf ."add_header ham Checker-Version SpamAssassin _VERSION_ (_SUBVERSION_) on _HOSTNAME_\n";
				$conf=$conf ."add_header all Status _YESNO_, score=_SCORE_ required=_REQD_ tests=_TESTS_ autolearn=_AUTOLEARN_ version=_VERSION_\n";

				
				$conf=$conf ."#\n";
				while (list ($num, $ligne) = each ($this->rewrite_headers) ){
					if(trim($ligne)<>null){
						$conf=$conf ."rewrite_header $num $ligne\n";
					}
				}
				$conf=$conf ."#\n";
				while (list ($num, $ligne) = each ($this->add_headers) ){
					if(trim($ligne)<>null){
						$conf=$conf ."add_header $num $ligne\n";
					}
				}				
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Save spam messages as a message/rfc822 MIME attachment instead of\n";
				$conf=$conf ."#   modifying the original message (0: off, 2: use text/plain instead)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."report_safe {$this->main_array["report_safe"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Set which networks or hosts are considered 'trusted' by your mail\n";
				$conf=$conf ."#   server (i.e. not spammers)\n";
				$conf=$conf ."#\n";
				if(is_array($this->trusted_networks)){
					reset($this->trusted_networks);
					while (list ($num, $ligne) = each ($this->trusted_networks) ){
						if(trim($ligne)<>null){
							$conf=$conf ."trusted_networks $ligne\n";
						}
					}	
				}
				
				$conf=$conf.$this->parse_internal_networks()."\n";
				
				
				
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Set file-locking method (flock is not safe over NFS, but is faster)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."lock_method flock\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Set the threshold at which a message is considered spam (default: 5.0)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."required_score {$this->main_array["required_score"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Use Bayesian classifier (default: 1)\n";
				$conf=$conf ."bayes_path\t/etc/spamassassin/bayes\n"; 
				$conf=$conf ."#\n";
				$conf=$conf ."use_bayes {$this->main_array["use_bayes"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Bayesian classifier auto-learning (default: 1)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."bayes_auto_learn {$this->main_array["bayes_auto_learn"]}\n";
				$conf=$conf ."# DNS TUNNING\n";
				
				if($this->EnableSpamassassinDnsEval==0){
				$conf=$conf ."skip_rbl_checks 1\n";
				$conf=$conf ."dns_available no\n";
				}else{
					$conf=$conf ."dns_available yes\n";
				}
				
				$conf=$conf ."\n";
				$conf=$conf ."\n"; 
				$conf=$conf ."#Auto white-listing\n";       
				$conf=$conf ."auto_whitelist_file_mode 0666\n";
				$conf=$conf ."auto_whitelist_path /etc/spamassassin/auto_whitelist\n";
				$conf=$conf ."\n";
				
				
				$conf=$conf ."\n"; 
				$conf=$conf ."#plugins\n";	
				$conf=$conf. $this->WrongMX();
				
				if($this->main_array["razor_config"]<>null){
				if($this->main_array["use_razor2"]==1){
					$conf=$conf ."use_razor2 {$this->main_array["use_razor2"]}\n";
					$conf=$conf ."razor_config {$this->main_array["razor_config"]}\n";
					}
				}
				if($this->main_array["use_pyzor"]==1){$conf=$conf ."use_pyzor {$this->main_array["use_pyzor"]}\n";}
				$conf=$conf ."\n"; 
				$conf=$conf ."#   Set headers which may provide inappropriate cues to the Bayesian\n";
				$conf=$conf ."#   classifier\n";
				$conf=$conf ."#\n";
				$conf=$conf ."# bayes_ignore_header X-Bogosity\n";
				$conf=$conf ."# bayes_ignore_header X-Spam-Flag\n";
				$conf=$conf ."# bayes_ignore_header X-Spam-Status\n";
				$conf=$conf.$this->keywords();
				$conf=$conf.$this->LargeSubjects();
				$conf=$conf .$this->MaxRCPT();
				$conf=$conf .$this->WhiteScores();
				$conf=$conf.$this->report_template();
				return $conf;
		
		
	}
	
private function report_template(){
$f[]="";	
$f[]="clear_report_template";
$f[]="report << begin content Filter report :";
$f[]="report Spam detection software, running on the system (_HOSTNAME_), has";
$f[]="report identified this incoming email as possible spam.  The original message";
$f[]="report has been attached to this so you can view it (if it isn't spam) or label";
$f[]="report similar future email.  If you have any questions, see";
$f[]="report _CONTACTADDRESS_ for details.";
$f[]="report";
$f[]="report Content preview:  _PREVIEW_";
$f[]="report";
$f[]="report Content analysis details:   (_SCORE_ points, _REQD_ required)";
$f[]="report";
$f[]="report pts rule name              description";
$f[]="report  ---- ---------------------- -----------------------------------------";
$f[]="report _SUMMARY_";
$f[]="report end content filter report >>";
$f[]="";
return @implode("\n",$f);
	
	
}
	
private function MaxRCPT(){
	$main=new maincf_multi("master","master");
	$max_rcpt_to=$main->GET("max_rcpt_to");
	if($max_rcpt_to==0){return array();}
	$sock=new sockets();
	$ArticaPolicyFilterMaxRCPTInternalDomainsOnly=$sock->GET_INFO("ArticaPolicyFilterMaxRCPTInternalDomainsOnly");
	$SpamassassinMaxRCPTScore=$sock->GET_INFO("SpamassassinMaxRCPTScore");
	
	if(!is_numeric($ArticaPolicyFilterMaxRCPTInternalDomainsOnly)){$ArticaPolicyFilterMaxRCPTInternalDomainsOnly=0;}
	if(!is_numeric($SpamassassinMaxRCPTScore)){$SpamassassinMaxRCPTScore=10;}
	
	
	if($ArticaPolicyFilterMaxRCPTInternalDomainsOnly==1){
		$ldap=new clladp();
		$doms=$ldap->hash_get_all_domains();
		$c=0;
		while (list ($num, $ligne) = each ($doms) ){
			$domain=str_replace(".","\.",$num);
		
			$c++;
			$f[]="header MAX_RPCPT_$c            To =~ /(.*?(@)$domain){{$max_rcpt_to},}/i";
			$f[]="header MAX_CC_$c            Cc =~ /(.*?(@)$domain){{$max_rcpt_to},}/i";
			$f[]="score MAX_CC_$c            $SpamassassinMaxRCPTScore";
			$f[]="describe MAX_RPCPT_$c  Sent to $max_rcpt_to+ $num recipients";
			$f[]="describe MAX_CC_$c  Sent to $max_rcpt_to+ Cc $num recipients";
			$f[]="score MAX_RPCPT_$c	$SpamassassinMaxRCPTScore";
			return @implode("\n",$f)."\n";
			
		}
	}
	
			$f[]="header MAX_RPCPT            To =~ /(.*?(@).*?){{$max_rcpt_to},}/i";
			$f[]="header MAX_CC            Cc =~ /(.*?(@).*?){{$max_rcpt_to},}/i";
			$f[]="score MAX_CC            $SpamassassinMaxRCPTScore";
			$f[]="describe MAX_RPCPT  Sent to $max_rcpt_to+ recipients";
			$f[]="describe MAX_CC  Sent to $max_rcpt_to+ Cc recipients";
			$f[]="score MAX_RPCPT	$SpamassassinMaxRCPTScore";
			return @implode("\n",$f)."\n";	
	
	
}	
	
	
	private function LargeSubjects(){
		return;
		$sock=new sockets();
		$EnableSpamassassinLargeSubjects=$sock->GET_INFO("EnableSpamassassinLargeSubjects");
		if(!is_numeric($EnableSpamassassinLargeSubjects)){$EnableSpamassassinLargeSubjects=1;}
		if($EnableSpamassassinLargeSubjects<>1){return;}
		$f[]="header LW_SUBJECT_SPAMMY  Subject =~ /^[0-9a-zA-Z,.+_\-'!\\\\\/]{31,}$/";
		$f[]="describe LW_SUBJECT_SPAMMY Subject appears spammy (31 or more characters without spaces. Only numbers, letters, and formatting)";
		$f[]="score  LW_SUBJECT_SPAMMY 0.2";
		$f[]="";
		$f[]="The key is to score the actual subject rule low, but bump the SA score ";
		$f[]="with meta rules that increase the score as more indicators are hit. I've ";
		$f[]="had moderate success with the rules below:";
		$f[]="";
		$f[]="# Rule 2: Message is HTML and has a tracking ID, or comes from a free mail address";
		$f[]="# Therefore, must hit HTML_MESSAGE, and either TRACKER_ID or FREEMAIL_FROM";
		$f[]="meta LW_SPAMMY_EMAIL1  (LW_SUBJECT_SPAMMY && HTML_MESSAGE && (TRACKER_ID || FREEMAIL_FROM))";
		$f[]="describe LW_SPAMMY_EMAIL1 Spammy HTML message that has a tracking ID or is freemail";
		$f[]="score  LW_SPAMMY_EMAIL1 1.0";
		$f[]="";
		$f[]="";
		$f[]="# Rule 3: Message hits LW_SPAMMY_EMAIL1 and MIME_QP_LONG_LINE";
		$f[]="# It's unusual for non-spam HTML messages to have really long Quoted Printable lines";
		$f[]="meta LW_SPAMMY_EMAIL2  (LW_SPAMMY_EMAIL1 && (MIME_QP_LONG_LINE || __LW_NET_TESTS))";
		$f[]="describe LW_SPAMMY_EMAIL2 Spammy HTML message also has a Quoted Printable line > 76 chars, or hits net check";
		$f[]="score  LW_SPAMMY_EMAIL2 1.0";
		$f[]="";		
		return @implode("\n",$f);	
	}
	
	private function WhiteScores(){
		$sql="SELECT * FROM postfix_global_whitelist WHERE enabled=1 AND score>0 ORDER BY sender";	
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$whitelist_from=$ligne["sender"];
			$RULENAME=$ligne["sender"];
			$RULENAME=str_replace(".","_",$RULENAME);
			$RULENAME=str_replace("@","AT_",$RULENAME);
			$RULENAME=str_replace("*","",$RULENAME);
			$RULENAME=strtoupper($RULENAME);
			
			$ligne["sender"]=str_replace("@","[at]",$ligne["sender"]);
			$ligne["sender"]=str_replace("*","[all]",$ligne["sender"]);
			
			$whitelist_from=str_replace(".","\.",$whitelist_from);
			$whitelist_from=str_replace("*",".*?",$whitelist_from);
			$whitelist_from=str_replace("@","\@",$whitelist_from);
			$tt[]="header WHITELIST_FROM_$RULENAME\tFrom =~ /$whitelist_from/i";
			$tt[]="describe WHITELIST_FROM_$RULENAME\tsubstract -{$ligne["score"]} from {$ligne["sender"]}";
			$tt[]="score WHITELIST_FROM_$RULENAME\t-{$ligne["score"]}.0";
			
		}
		return @implode("\n",$tt);	
	}		
		
	
	
	
	private function WrongMX(){
			$sock=new sockets();
			$EnableSpamassassinWrongMX=$sock->GET_INFO("EnableSpamassassinWrongMX");
			if($EnableSpamassassinWrongMX==null){$EnableSpamassassinWrongMX=0;}
			if($EnableSpamassassinWrongMX<>1){return null;}
			$f[]="";
			$f[]="loadplugin      WrongMX wrongmx.pm";
			$f[]="";
			$f[]="header          WRONGMX eval:wrongmx()";
			$f[]="describe        WRONGMX Sent to lower pref MX when higher pref MX was up.";
			$f[]="tflags          WRONGMX net";
			$f[]="score           WRONGMX 1.0";	
			$f[]="";
			return @implode("\n",$f);	
		
	}
	
	
	private function keywords(){
		$sql="SELECT * FROM spamassassin_keywords WHERE enabled=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$prefix="body";
			$prefix_regex=" ";
			switch ($ligne["header"]) {
				case "subject":
					$prefix="header";
					$prefix_regex="Subject =~ ";
					break;
			}
			
			$keyword=trim($ligne["pattern"]);
			if(trim($ligne["score"])==null){$ligne["score"]="0.1";}
			
			$keyword=str_replace(".","\.",$keyword);
			$keyword=str_replace("(","\(",$keyword);
			$keyword=str_replace(")","\)",$keyword);
			$keyword=str_replace("[","\[",$keyword);
			$keyword=str_replace("]","\]",$keyword);
			$keyword=str_replace("{","\{",$keyword);
			$keyword=str_replace("}","\}",$keyword);
			$keyword=str_replace("+","\+",$keyword);
			$keyword=str_replace("*",".+?",$keyword);
			$keyword=str_replace(" ","\s+",$keyword);
			
			$f[]="$prefix KEYWORD_RULE_{$ligne["ID"]}\t$prefix_regex/$keyword/i";
			$f[]="score KEYWORD_RULE_{$ligne["ID"]}\t{$ligne["score"]}";
			
		}
		
		return @implode("\n",$f)."\n";
		
	}
	
function LoadCountriesCodes(){
			$CountriesCode["AC"]="Ascension Island";
			$CountriesCode["AD"]="Andorra";
			$CountriesCode["AE"]="United Arab Emirates";
			$CountriesCode["AF"]="Afghanistan";
			$CountriesCode["AG"]="Antigua and Barbuda";
			$CountriesCode["AI"]="Anguilla";
			$CountriesCode["AL"]="Albania";
			$CountriesCode["AM"]="Armenia";
			$CountriesCode["AN"]="Netherlands Antilles";
			$CountriesCode["AO"]="Angola";
			$CountriesCode["AQ"]="Antarctica";
			$CountriesCode["AR"]="Argentina";
			$CountriesCode["AS"]="American Samoa";
			$CountriesCode["ASIA"]="Pan-Asia and Asia Pacific community";
			$CountriesCode["AT"]="Austria";
			$CountriesCode["AU"]="Australia";
			$CountriesCode["AW"]="Aruba";
			$CountriesCode["AX"]="Aland Islands";
			$CountriesCode["AZ"]="Azerbaijan";
			$CountriesCode["BA"]="Bosnia and Herzegovina";
			$CountriesCode["BB"]="Barbados";
			$CountriesCode["BD"]="Bangladesh";
			$CountriesCode["BE"]="Belgium";
			$CountriesCode["BF"]="Burkina Faso";
			$CountriesCode["BG"]="Bulgaria";
			$CountriesCode["BH"]="Bahrain";
			$CountriesCode["BI"]="Burundi";
			$CountriesCode["BIZ"]="Restricted for Business";
			$CountriesCode["BJ"]="Benin";
			$CountriesCode["BL"]="Saint Barthelemy";
			$CountriesCode["BM"]="Bermuda";
			$CountriesCode["BN"]="Brunei Darussalam";
			$CountriesCode["BO"]="Bolivia";
			$CountriesCode["BR"]="Brazil";
			$CountriesCode["BS"]="Bahamas";
			$CountriesCode["BT"]="Bhutan";
			$CountriesCode["BV"]="Bouvet Island";
			$CountriesCode["BW"]="Botswana";
			$CountriesCode["BY"]="Belarus";
			$CountriesCode["BZ"]="Belize";
			$CountriesCode["CA"]="Canada";
			$CountriesCode["CAT"]="Catalan linguistic";
			$CountriesCode["CC"]="Cocos (Keeling) Islands";
			$CountriesCode["CD"]="Congo, The Democratic Republic";
			$CountriesCode["CF"]="Central African Republic";
			$CountriesCode["CG"]="Congo";
			$CountriesCode["CH"]="Switzerland";
			$CountriesCode["CI"]="Cote d'Ivoire";
			$CountriesCode["CK"]="Cook Islands";
			$CountriesCode["CL"]="Chile";
			$CountriesCode["CM"]="Cameroon";
			$CountriesCode["CN"]="China";
			$CountriesCode["CO"]="Colombia";
			$CountriesCode["COM"]="top-level domain .com";
			$CountriesCode["COOP"]="Cooperative associations";
			$CountriesCode["CR"]="Costa Rica";
			$CountriesCode["CU"]="Cuba";
			$CountriesCode["CV"]="Cape Verde";
			$CountriesCode["CX"]="Christmas Island";
			$CountriesCode["CY"]="Cyprus";
			$CountriesCode["CZ"]="Czech Republic";
			$CountriesCode["DE"]="Germany";
			$CountriesCode["DJ"]="Djibouti";
			$CountriesCode["DK"]="Denmark";
			$CountriesCode["DM"]="Dominica";
			$CountriesCode["DO"]="Dominican Republic";
			$CountriesCode["DZ"]="Algeria";
			$CountriesCode["EC"]="Ecuador";
			$CountriesCode["EDU"]="Post-secondary institutions";
			$CountriesCode["EE"]="Estonia";
			$CountriesCode["EG"]="Egypt";
			$CountriesCode["EH"]="Western Sahara";
			$CountriesCode["ER"]="Eritrea";
			$CountriesCode["ES"]="Spain";
			$CountriesCode["ET"]="Ethiopia";
			$CountriesCode["EU"]="European Union";
			$CountriesCode["FI"]="Finland";
			$CountriesCode["FJ"]="Fiji";
			$CountriesCode["FK"]="Falkland Islands (Malvinas)";
			$CountriesCode["FM"]="Micronesia, Federated States of";
			$CountriesCode["FO"]="Faroe Islands";
			$CountriesCode["FR"]="France";
			$CountriesCode["GA"]="Gabon";
			$CountriesCode["GB"]="United Kingdom";
			$CountriesCode["GD"]="Grenada";
			$CountriesCode["GE"]="Georgia";
			$CountriesCode["GF"]="French Guiana";
			$CountriesCode["GG"]="Guernsey";
			$CountriesCode["GH"]="Ghana";
			$CountriesCode["GI"]="Gibraltar";
			$CountriesCode["GL"]="Greenland";
			$CountriesCode["GM"]="Gambia";
			$CountriesCode["GN"]="Guinea";
			$CountriesCode["GOV"]="Government";
			$CountriesCode["GP"]="Guadeloupe";
			$CountriesCode["GQ"]="Equatorial Guinea";
			$CountriesCode["GR"]="Greece";
			$CountriesCode["GS"]="South Georgia and the South Sandwich Islands";
			$CountriesCode["GT"]="Guatemala";
			$CountriesCode["GU"]="Guam";
			$CountriesCode["GW"]="Guinea-Bissau";
			$CountriesCode["GY"]="Guyana";
			$CountriesCode["HK"]="Hong Kong";
			$CountriesCode["HM"]="Heard Island and McDonald Islands";
			$CountriesCode["HN"]="Honduras";
			$CountriesCode["HR"]="Croatia";
			$CountriesCode["HT"]="Haiti";
			$CountriesCode["HU"]="Hungary";
			$CountriesCode["ID"]="Indonesia";
			$CountriesCode["IE"]="Ireland";
			$CountriesCode["IL"]="Israel";
			$CountriesCode["IM"]="Isle of Man";
			$CountriesCode["IN"]="India";
			$CountriesCode["INFO"]="top-level domain .info";
			$CountriesCode["INT"]="Treaties between governments";
			$CountriesCode["IO"]="British Indian Ocean Territory";
			$CountriesCode["IQ"]="Iraq";
			$CountriesCode["IR"]="Iran, Islamic Republic of";
			$CountriesCode["IS"]="Iceland";
			$CountriesCode["IT"]="Italy";
			$CountriesCode["JE"]="Jersey";
			$CountriesCode["JM"]="Jamaica";
			$CountriesCode["JO"]="Jordan";
			$CountriesCode["JOBS"]="Human resource managers";
			$CountriesCode["JP"]="Japan";
			$CountriesCode["KE"]="Kenya";
			$CountriesCode["KG"]="Kyrgyzstan";
			$CountriesCode["KH"]="Cambodia";
			$CountriesCode["KI"]="Kiribati";
			$CountriesCode["KM"]="Comoros";
			$CountriesCode["KN"]="Saint Kitts and Nevis";
			$CountriesCode["KP"]="Korea, Democratic People's Republic of";
			$CountriesCode["KR"]="Korea, Republic of";
			$CountriesCode["KW"]="Kuwait";
			$CountriesCode["KY"]="Cayman Islands";
			$CountriesCode["KZ"]="Kazakhstan";
			$CountriesCode["LA"]="Lao People's Democratic Republic";
			$CountriesCode["LB"]="Lebanon";
			$CountriesCode["LC"]="Saint Lucia";
			$CountriesCode["LI"]="Liechtenstein";
			$CountriesCode["LK"]="Sri Lanka";
			$CountriesCode["LR"]="Liberia";
			$CountriesCode["LS"]="Lesotho";
			$CountriesCode["LT"]="Lithuania";
			$CountriesCode["LU"]="Luxembourg";
			$CountriesCode["LV"]="Latvia";
			$CountriesCode["LY"]="Libyan Arab Jamahiriya";
			$CountriesCode["MA"]="Morocco";
			$CountriesCode["MC"]="Monaco";
			$CountriesCode["MD"]="Moldova, Republic of";
			$CountriesCode["ME"]="Montenegro";
			$CountriesCode["MF"]="Saint Martin";
			$CountriesCode["MG"]="Madagascar";
			$CountriesCode["MH"]="Marshall Islands";
			$CountriesCode["MIL"]="United States Military";
			$CountriesCode["MK"]="Macedonia, The Former Yugoslav Republic of";
			$CountriesCode["ML"]="Mali";
			$CountriesCode["MM"]="Myanmar";
			$CountriesCode["MN"]="Mongolia";
			$CountriesCode["MO"]="Macao";
			$CountriesCode["MOBI"]="mobile products and services";
			$CountriesCode["MP"]="Northern Mariana Islands";
			$CountriesCode["MQ"]="Martinique";
			$CountriesCode["MR"]="Mauritania";
			$CountriesCode["MS"]="Montserrat";
			$CountriesCode["MT"]="Malta";
			$CountriesCode["MU"]="Mauritius";
			$CountriesCode["MUSEUM"]="museums";
			$CountriesCode["MV"]="Maldives";
			$CountriesCode["MW"]="Malawi";
			$CountriesCode["MX"]="Mexico";
			$CountriesCode["MY"]="Malaysia";
			$CountriesCode["MZ"]="Mozambique";
			$CountriesCode["NA"]="Namibia";
			$CountriesCode["NAME"]="individuals";
			$CountriesCode["NC"]="New Caledonia";
			$CountriesCode["NE"]="Niger";
			$CountriesCode["NET"]="top-level domain .net";
			$CountriesCode["NF"]="Norfolk Island";
			$CountriesCode["NG"]="Nigeria";
			$CountriesCode["NI"]="Nicaragua";
			$CountriesCode["NL"]="Netherlands";
			$CountriesCode["NO"]="Norway";
			$CountriesCode["NP"]="Nepal";
			$CountriesCode["NR"]="Nauru";
			$CountriesCode["NU"]="Niue";
			$CountriesCode["NZ"]="New Zealand";
			$CountriesCode["OM"]="Oman";
			$CountriesCode["ORG"]="top-level domain .org";
			$CountriesCode["PA"]="Panama";
			$CountriesCode["PE"]="Peru";
			$CountriesCode["PF"]="French Polynesia";
			$CountriesCode["PG"]="Papua New Guinea";
			$CountriesCode["PH"]="Philippines";
			$CountriesCode["PK"]="Pakistan";
			$CountriesCode["PL"]="Poland";
			$CountriesCode["PM"]="Saint Pierre and Miquelon";
			$CountriesCode["PN"]="Pitcairn";
			$CountriesCode["PR"]="Puerto Rico";
			$CountriesCode["PRO"]="professionals and related entities";
			$CountriesCode["PS"]="Palestinian Territory, Occupied";
			$CountriesCode["PT"]="Portugal";
			$CountriesCode["PW"]="Palau";
			$CountriesCode["PY"]="Paraguay";
			$CountriesCode["QA"]="Qatar";
			$CountriesCode["RE"]="Reunion";
			$CountriesCode["RO"]="Romania";
			$CountriesCode["RS"]="Serbia";
			$CountriesCode["RU"]="Russian Federation";
			$CountriesCode["RW"]="Rwanda";
			$CountriesCode["SA"]="Saudi Arabia";
			$CountriesCode["SB"]="Solomon Islands";
			$CountriesCode["SC"]="Seychelles";
			$CountriesCode["SD"]="Sudan";
			$CountriesCode["SE"]="Sweden";
			$CountriesCode["SG"]="Singapore";
			$CountriesCode["SH"]="Saint Helena";
			$CountriesCode["SI"]="Slovenia";
			$CountriesCode["SJ"]="Svalbard and Jan Mayen";
			$CountriesCode["SK"]="Slovakia";
			$CountriesCode["SL"]="Sierra Leone";
			$CountriesCode["SM"]="San Marino";
			$CountriesCode["SN"]="Senegal";
			$CountriesCode["SO"]="Somalia";
			$CountriesCode["SR"]="Suriname";
			$CountriesCode["ST"]="Sao Tome and Principe";
			$CountriesCode["SU"]="Soviet Union (being phased out)";
			$CountriesCode["SV"]="El Salvador";
			$CountriesCode["SY"]="Syrian Arab Republic";
			$CountriesCode["SZ"]="Swaziland";
			$CountriesCode["TC"]="Turks and Caicos Islands";
			$CountriesCode["TD"]="Chad";
			$CountriesCode["TEL"]="individuals to publish their contact data";
			$CountriesCode["TF"]="French Southern Territories";
			$CountriesCode["TG"]="Togo";
			$CountriesCode["TH"]="Thailand";
			$CountriesCode["TJ"]="Tajikistan";
			$CountriesCode["TK"]="Tokelau";
			$CountriesCode["TL"]="Timor-Leste";
			$CountriesCode["TM"]="Turkmenistan";
			$CountriesCode["TN"]="Tunisia";
			$CountriesCode["TO"]="Tonga";
			$CountriesCode["TP"]="Portuguese Timor (being phased out)";
			$CountriesCode["TR"]="Turkey";
			$CountriesCode["TRAVEL"]="activity is in the travel industry";
			$CountriesCode["TT"]="Trinidad and Tobago";
			$CountriesCode["TV"]="Tuvalu";
			$CountriesCode["TW"]="Taiwan";
			$CountriesCode["TZ"]="Tanzania, United Republic of";
			$CountriesCode["UA"]="Ukraine";
			$CountriesCode["UG"]="Uganda";
			$CountriesCode["UK"]="United Kingdom";
			$CountriesCode["UM"]="United States Minor Outlying Islands";
			$CountriesCode["US"]="United States";
			$CountriesCode["UY"]="Uruguay";
			$CountriesCode["UZ"]="Uzbekistan";
			$CountriesCode["VA"]="Holy See (Vatican City State)";
			$CountriesCode["VC"]="Saint Vincent and the Grenadines";
			$CountriesCode["VE"]="Venezuela, Bolivarian Republic of";
			$CountriesCode["VG"]="Virgin Islands, British";
			$CountriesCode["VI"]="Virgin Islands, U.S.";
			$CountriesCode["VN"]="Viet Nam";
			$CountriesCode["VU"]="Vanuatu";
			$CountriesCode["WF"]="Wallis and Futuna";
			$CountriesCode["WS"]="Samoa";
			$CountriesCode["YE"]="Yemen";
			$CountriesCode["YT"]="Mayotte";
			$CountriesCode["YU"]="Yugoslavia (being phased out)";
			$CountriesCode["ZA"]="South Africa";
			$CountriesCode["ZM"]="Zambia";
			$CountriesCode["ZW"]="Zimbabwe";	
			return $CountriesCode;
			}
	
	
}