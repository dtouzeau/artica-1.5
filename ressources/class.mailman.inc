<?php
	include_once(dirname(__FILE__).'/class.templates.inc');
	include_once(dirname(__FILE__).'/class.ldap.inc');
	include_once(dirname(__FILE__).'/class.users.menus.inc');
	
	class mailman{
		
		var $list_name;
		var $mailmanouowner;
		var $mailmanlistconfigdatas;
		var $owner;
		var $moderator;
		var $ban_list;
		var $available_languages;
		var $accept_these_nonmembers;
		var $reject_these_nonmembers;
		var $hold_these_nonmembers;
		var $discard_these_nonmembers;
		var $acceptable_aliases;
		var $header_filter_rules;
		var $bounce_matching_headers;
		var $filter_mime_types;
		var $pass_mime_types;
		var $filter_filename_extensions;
		var $pass_filename_extensions;
		var $topics;
		var $main_array;
		var $array_lang=array(''=>'{select}','cs'=>'Czech','de'=>'German','en'=>'English (USA)','es'=>'Spanish (Spain)','et'=>'Estonian','fi'=>'Finnish','fr'=>'French','hu'=>'Hungarian','it'=>'Italian','ja'=>'Japanese','ko'=>'Korean','lt'=>'Lithuanian','nl'=>'Dutch','no'=>'Norwegian','pt_BR'=>'Portuguese (Brazil)','ru'=>'Russian','sv'=>'Swedish');
		var $header_filter_rules_action=array(0=>"{defer}",7=>"{hold}",2=>'{reject}',3=>'{discard}',6=>'{accept}');
		var $main_default_array;
		
		function mailman($listname=null){
			
			if($listname<>null){
				$this->list_name=$listname;
				$this->loadSettings();
			}
			$this->LoadPyDefaults();
			
			
		}
		
		function SaveOu(){
			$ldap=new clladp();
			$upd["mailmanouowner"][0]=$this->mailmanouowner;
			$ldap->Ldap_modify("cn=$this->list_name,cn=mailman,cn=artica,$ldap->suffix",$upd);
		}

		
		
		function LoadPyDefaults(){
			$ldap=new clladp();
			$dn="cn=Defaults.py,cn=mailman,cn=artica,$ldap->suffix";
			$res=ldap_read($ldap->ldap_connection,$dn,"(objectClass=ArticaMailMangGlobalConfigClass)",array());
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			
		    if(!isset($hash[0][strtolower("MailManAdminPassword")])){
				$upd["MailManAdminPassword"][0]=$ldap->ldap_password;
				$hash[0][strtolower("MailManAdminPassword")][0]=$ldap->ldap_password;
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}
				
 			if(!isset($hash[0][strtolower("MailManDefaultEmailHost")])){
				$upd["MailManDefaultEmailHost"][0]=$_SERVER['SERVER_NAME'];
				$hash[0][strtolower("MailManAdminPassword")][0]=$_SERVER['SERVER_NAME'];
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}	

			if(!isset($hash[0][strtolower("MailManDefaultUrlHost")])){
				$upd["MailManDefaultUrlHost"][0]=$_SERVER['SERVER_NAME'];
				$hash[0][strtolower("MailManDefaultUrlHost")][0]=$_SERVER['SERVER_NAME'];
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}
				
			if(!isset($hash[0][strtolower("MailManDefaultUrlPattern")])){
				$upd["MailManDefaultUrlPattern"][0]='https://%s:9000/mailman/';
				$hash[0][strtolower("MailManDefaultUrlPattern")][0]='https://%s:9000/mailman/';
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}	
				
			if(!isset($hash[0][strtolower("MailManDefaulPublicArchiveUrl")])){
				$upd["MailManDefaulPublicArchiveUrl"][0]='https://%(hostname)s:9000/pipermail/%(listname)s';
				$hash[0][strtolower("MailManDefaulPublicArchiveUrl")][0]='https://%(hostname)s:9000/pipermail/%(listname)s';
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}

			if(!isset($hash[0][strtolower("VirtualHostOverview")])){
				$upd["VirtualHostOverview"][0]='No';
				$hash[0][strtolower("VirtualHostOverview")][0]='No';
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}							
				
	  		$this->main_default_array["MailManAdminPassword"]=$hash[0][strtolower("MailManAdminPassword")][0];
			$this->main_default_array["MailManDefaultEmailHost"]=$hash[0][strtolower("MailManDefaultEmailHost")][0];
			$this->main_default_array["MailManDefaultUrlHost"]=$hash[0][strtolower("MailManDefaultUrlHost")][0];
			$this->main_default_array["MailManDefaultUrlPattern"]=$hash[0][strtolower("MailManDefaultUrlPattern")][0];
			$this->main_default_array["MailManDefaulPublicArchiveUrl"]=$hash[0][strtolower("MailManDefaulPublicArchiveUrl")][0];
			$this->main_default_array["VirtualHostOverview"]=$hash[0][strtolower("VirtualHostOverview")][0];
			
		}
		
		
		function save_PyDefaults(){
			while (list ($num, $val) = each ($this->main_default_array) ){
				$upd[$num][]=$val;
				}
			
				$dn="cn=Defaults.py,cn=mailman,cn=artica,$ldap->suffix";
				$ldap=new clladp();
				$ldap->Ldap_modify($dn,$upd);
			}
		
		
		
		
		function LoadSettings(){
			$ldap=new clladp();
			$dn="cn=$this->list_name,cn=mailman,cn=artica,$ldap->suffix";
			writelogs("load mailman datas $this->list_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$res=ldap_read($ldap->ldap_connection,$dn,"(objectClass=*)",array());
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			
			
			if(!isset($hash[0][strtolower("mailmanMsgHeader")])){
				$upd["mailmanMsgHeader"][0]="NULL";
				$hash[0][strtolower("mailmanMsgHeader")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}
				
				
			if(!isset($hash[0][strtolower("mailmanMsgFooter")])){
				$upd["mailmanMsgFooter"][0]="NULL";
				$hash[0][strtolower("mailmanMsgFooter")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}

			if(!isset($hash[0][strtolower("mailmanDigestHeader")])){
				$upd["mailmanDigestHeader"][0]="NULL";
				$hash[0][strtolower("mailmanDigestHeader")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}	
			if(!isset($hash[0][strtolower("mailmanDigestFooter")])){
				$upd["mailmanDigestFooter"][0]="NULL";
				$hash[0][strtolower("mailmanDigestFooter")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}	
			if(!isset($hash[0][strtolower("mailmanAutoresponseAdminText")])){
				$upd["mailmanAutoresponseAdminText"][0]="NULL";
				$hash[0][strtolower("mailmanAutoresponseAdminText")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}
			if(!isset($hash[0][strtolower("mailmanAutoresponsePostingsText")])){
				$upd["mailmanAutoresponsePostingsText"][0]="NULL";
				$hash[0][strtolower("mailmanAutoresponsePostingsText")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}																			
			if(!isset($hash[0][strtolower("mailmanAutoresponseRequestText")])){
				$upd["mailmanAutoresponseRequestText"][0]="NULL";
				$hash[0][strtolower("mailmanAutoresponseRequestText")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}	
			if(!isset($hash[0][strtolower("mailmanWelcomeMsg")])){
				$upd["mailmanWelcomeMsg"][0]="NULL";
				$hash[0][strtolower("mailmanWelcomeMsg")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}	
			if(!isset($hash[0][strtolower("mailmanGoodbyeMsg")])){
				$upd["mailmanGoodbyeMsg"][0]="NULL";
				$hash[0][strtolower("mailmanGoodbyeMsg")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}
			if(!isset($hash[0][strtolower("mailmanAcceptableAliases")])){
				$upd["mailmanAcceptableAliases"][0]="NULL";
				$hash[0][strtolower("mailmanAcceptableAliases")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}	
			if(!isset($hash[0][strtolower("FilterFilenameExtensions")])){
				$upd["FilterFilenameExtensions"][0]="NULL";
				$hash[0][strtolower("FilterFilenameExtensions")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}																				
		if(!isset($hash[0][strtolower("MailmanListOperation")])){
				$upd["MailmanListOperation"][0]="NULL";
				$hash[0][strtolower("MailmanListOperation")][0]="NULL";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}

		if(!isset($hash[0][strtolower("MailManListAdministrator")])){
				$upd["MailManListAdministrator"][0]=$ldap->ldap_admin. "@localhost.localdomain";
				$hash[0][strtolower("MailManListAdministrator")][0]=$ldap->ldap_admin. "@localhost.localdomain";
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}
		if(!isset($hash[0][strtolower("MailManListAdminPassword")])){
				$upd["MailManListAdminPassword"][0]=$ldap->ldap_admin;
				$hash[0][strtolower("MailManListAdministrator")][0]=$ldap->ldap_admin;
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}											
				
		if(!isset($hash[0][strtolower("HostNameList")])){
				$upd["HostNameList"][0]=$_SERVER['SERVER_NAME'];
				$hash[0][strtolower("HostNameList")][0]=$_SERVER['SERVER_NAME'];
				$ldap->Ldap_add_mod($dn,$upd);
				unset($upd);
				}		
								
				

			$this->mailmanlistconfigdatas=$hash[0]["mailmanlistconfigdatas"][0];
			$this->mailmanouowner=$hash[0]["mailmanouowner"][0];
			$this->CreateArray();
			
			$this->main_array["msg_header"]=$hash[0][strtolower("mailmanMsgHeader")][0];
			$this->main_array["msg_footer"]=$hash[0][strtolower("mailmanMsgFooter")][0];
			$this->main_array["digest_header"]=$hash[0][strtolower("mailmanDigestHeader")][0];
			$this->main_array["digest_footer"]=$hash[0][strtolower("mailmanDigestFooter")][0];
			$this->main_array["autoresponse_admin_text"]=$hash[0][strtolower("mailmanAutoresponseAdminText")][0];
			$this->main_array["autoresponse_postings_text"]=$hash[0][strtolower("mailmanAutoresponsePostingsText")][0];
			$this->main_array["autoresponse_request_text"]=$hash[0][strtolower("mailmanAutoresponseRequestText")][0];
			
			$this->main_array["welcome_msg"]=$hash[0][strtolower("mailmanWelcomeMsg")][0];			
			$this->main_array["goodbye_msg"]=$hash[0][strtolower("mailmanGoodbyeMsg")][0];
			$this->main_array["acceptable_aliases"]=$hash[0][strtolower("mailmanAcceptableAliases")][0];
			$this->main_array["filter_filename_extensions"]=$hash[0][strtolower("FilterFilenameExtensions")][0];
			$this->main_array["host_name"]=$hash[0][strtolower("HostNameList")][0];
			$this->FillDefaults();
			
		}
		
		function CreateArray(){
			$tb=explode("\n",$this->mailmanlistconfigdatas);
			while (list ($num, $val) = each ($tb) ){
				if(!preg_match('#^\##',$val)){
				if(preg_match('#(.+?)[=\s]+(.+)#',$val,$tr)){
					$tr[2]=str_replace("'","",$tr[2]);
					$tr[2]=str_replace("\"","",$tr[2]);
					$this->main_array[$tr[1]]=trim($tr[2]);
					}
				}
			}
		$this->digest_footer();
		$this->Owners();
		$this->moderators();
		$this->welcome_msg();
		$this->goodbye_msg();
		$this->ban_list();
		$this->available_languages_explode();
		$this->accept_these_nonmembers();
		$this->hold_these_nonmembers_explode();
		$this->reject_these_nonmembers_explode();
		$this->discard_these_nonmembers_explode();
		$this->acceptable_aliases_explode();
		$this->header_filter_rules_explode();
		$this->bounce_matching_headers_explode();
		$this->filter_mime_types_explode();
		$this->topics_explode();
		}
		
		function digest_footer(){
			unset($this->main_array["digest_footer"]);
			unset($this->main_array["digest_header"]);
			unset($this->main_array["msg_footer"]);
			unset($this->main_array["msg_header"]);
			unset($this->main_array["member_moderation_notice"]);
			unset($this->main_array["nonmember_rejection_notice"]);
			unset($this->main_array["acceptable_aliases"]);
			unset($this->main_array["header_filter_rules"]);
			unset($this->main_array["bounce_matching_headers"]);
			unset($this->main_array["autoresponse_postings_text"]);
			unset($this->main_array["autoresponse_request_text"]);
			unset($this->main_array["autorespond_admin_text"]);
			unset($this->main_array["filter_mime_types"]);
			unset($this->main_array["pass_mime_types"]);
			unset($this->main_array["filter_filename_extensions"]);
			unset($this->main_array["pass_filename_extensions"]);
			unset($this->main_array["topics"]);
			
			
		
			
			if(preg_match('#member_moderation_notice[=\s]+["]+(.*?)["]+#s',$this->mailmanlistconfigdatas,$re)){$this->main_array["member_moderation_notice"]=$re[1];}
			if(preg_match('#nonmember_rejection_notice[=\s]+["]+(.*?)["]+#s',$this->mailmanlistconfigdatas,$re)){$this->main_array["nonmember_rejection_notice"]=$re[1];}
			
			
			
			if(preg_match('#header_filter_rules[\s=]+\[(.*?)\n#s',$this->mailmanlistconfigdatas,$re)){
				writelogs("/header_filter_rules/ = {{$re[1]}}",__CLASS__ . '/' . __FUNCTION__,__FILE__);		
				$this->main_array["header_filter_rules"]=$re[1];
				}
		
			
			if(preg_match('#bounce_matching_headers[=\s"]+(.*?)["]+#s',$this->mailmanlistconfigdatas,$re)){$this->main_array["bounce_matching_headers"]=$re[1];}
			
			
			if(preg_match('#filter_mime_types[=\s]+"""(.*?)"""#s',$this->mailmanlistconfigdatas,$re)){$this->main_array["filter_mime_types"]=$re[1];}
			if(preg_match('#pass_mime_types[=\s]+"""(.*?)"""#s',$this->mailmanlistconfigdatas,$re)){$this->main_array["pass_mime_types"]=$re[1];}
			if(preg_match('#pass_filename_extensions[=\s]+"""(.*?)"""#s',$this->mailmanlistconfigdatas,$re)){$this->main_array["pass_filename_extensions"]=$re[1];}
			
			if(preg_match('#topics[=\s]+\[(.*?)\]#s',$this->mailmanlistconfigdatas,$re)){$this->main_array["topics"]=$re[1];}
			

		writelogs("header_filter_rules = {$this->main_array["header_filter_rules"]}",__CLASS__ . '/' . __FUNCTION__,__FILE__);		
			
		}
		
		
		function topics_explode(){
			if($this->main_array["topics"]==null){return null;}
			
			if(preg_match_all('#\((.+?)False\)#',$this->main_array["topics"],$TBa)){
				while (list ($num, $val) = each ($TBa[1]) ){
					
					$val=substr($val,1,strlen($val)-1);
					$TBb=explode(',',$val);
					$TBb[0]=trim($TBb[0]);
					$TBb[1]=trim($TBb[1]);
					$TBb[2]=trim($TBb[2]);
					$TBb[0]=substr($TBb[0],0,strlen($TBb[0])-1);
					$TBb[1]=substr($TBb[1],1,strlen($TBb[1])-2);
					$TBb[2]=substr($TBb[2],1,strlen($TBb[2])-2);
					$this->topics[]=array("name"=>$TBb[0],"pattern"=>$TBb[1],"desc"=>$TBb[2]);
					}
				}
		}
		
		
		function topics_implode(){
			if(!is_array($this->topics)){return null;}
			while (list ($num, $val) = each ($this->topics) ){
				$arr[]="('{$val["name"]}', '{$val["pattern"]}', '{$val["desc"]}', False)";
				
			}
			
			return implode(",",$arr);
		}
		
		function welcome_msg(){
			unset($this->main_array["welcome_msg"]);
			if(preg_match("#welcome_msg[=\s]+\"\"\"(.*?)\"\"\"#is",$this->mailmanlistconfigdatas,$re)){$this->main_array["welcome_msg"]=$re[1];}
			
		}
		
		function goodbye_msg(){
			unset($this->main_array["goodbye_msg"]);
			if(preg_match("#goodbye_msg[=\s]+\"\"\"(.*?)\"\"\"#is",$this->mailmanlistconfigdatas,$re)){$this->main_array["goodbye_msg"]=$re[1];}			
			
		}
		
		function Owners(){
			$this->main_array["owner"]=str_replace('[','',$this->main_array["owner"]);
			$this->main_array["owner"]=str_replace(']','',$this->main_array["owner"]);			
			$this->main_array["owner"]=str_replace("'","",$this->main_array["owner"]);
			if($this->main_array["owner"]==null){return null;}
			$tb=explode(',',$this->main_array["owner"]);
			$this->owner=$tb;
			unset($this->main_array["owner"]);			
		}
		
		function ban_list(){
			$this->main_array["ban_list"]=str_replace('[','',$this->main_array["ban_list"]);
			$this->main_array["ban_list"]=str_replace(']','',$this->main_array["ban_list"]);			
			$this->main_array["ban_list"]=str_replace("'","",$this->main_array["ban_list"]);
			if($this->main_array["ban_list"]==null){return null;}
			$tb=explode(',',$this->main_array["ban_list"]);
			$this->ban_list=$tb;
			unset($this->main_array["ban_list"]);						
			
			
		}
		
		function moderators(){
			$this->main_array["moderator"]=str_replace('[','',$this->main_array["moderator"]);
			$this->main_array["moderator"]=str_replace(']','',$this->main_array["moderator"]);			
			$this->main_array["moderator"]=str_replace("'","",$this->main_array["moderator"]);
			if($this->main_array["moderator"]==null){return null;}
			$tb=explode(',',$this->main_array["moderator"]);
			$this->moderator=$tb;
			unset($this->main_array["moderator"]);		
			
		}
		
		function accept_these_nonmembers(){
			$this->main_array["accept_these_nonmembers"]=str_replace('[','',$this->main_array["accept_these_nonmembers"]);
			$this->main_array["accept_these_nonmembers"]=str_replace(']','',$this->main_array["accept_these_nonmembers"]);			
			$this->main_array["accept_these_nonmembers"]=str_replace("'","",$this->main_array["accept_these_nonmembers"]);
			if($this->main_array["accept_these_nonmembers"]==null){return null;}
			$tb=explode(',',$this->main_array["accept_these_nonmembers"]);
			$this->accept_these_nonmembers=$tb;
			unset($this->main_array["accept_these_nonmembers"]);					
			
		}
		
		function hold_these_nonmembers_explode(){
			$this->main_array["hold_these_nonmembers"]=str_replace('[','',$this->main_array["hold_these_nonmembers"]);
			$this->main_array["hold_these_nonmembers"]=str_replace(']','',$this->main_array["hold_these_nonmembers"]);			
			$this->main_array["hold_these_nonmembers"]=str_replace("'","",$this->main_array["hold_these_nonmembers"]);
			if($this->main_array["hold_these_nonmembers"]==null){return null;}
			$tb=explode(',',$this->main_array["hold_these_nonmembers"]);
			$this->hold_these_nonmembers=$tb;
			unset($this->main_array["hold_these_nonmembers"]);					
			
		}	
		function reject_these_nonmembers_explode(){
			$this->main_array["reject_these_nonmembers"]=str_replace('[','',$this->main_array["reject_these_nonmembers"]);
			$this->main_array["reject_these_nonmembers"]=str_replace(']','',$this->main_array["reject_these_nonmembers"]);			
			$this->main_array["reject_these_nonmembers"]=str_replace("'","",$this->main_array["reject_these_nonmembers"]);
			if($this->main_array["reject_these_nonmembers"]==null){return null;}
			$tb=explode(',',$this->main_array["reject_these_nonmembers"]);
			$this->reject_these_nonmembers=$tb;
			unset($this->main_array["reject_these_nonmembers"]);					
		}
		function discard_these_nonmembers_explode(){
			$this->main_array["discard_these_nonmembers"]=str_replace('[','',$this->main_array["discard_these_nonmembers"]);
			$this->main_array["discard_these_nonmembers"]=str_replace(']','',$this->main_array["discard_these_nonmembers"]);			
			$this->main_array["discard_these_nonmembers"]=str_replace("'","",$this->main_array["discard_these_nonmembers"]);
			if($this->main_array["discard_these_nonmembers"]==null){return null;}
			$tb=explode(',',$this->main_array["discard_these_nonmembers"]);
			$this->discard_these_nonmembers=$tb;
			unset($this->main_array["discard_these_nonmembers"]);					
			}					
			
		function available_languages_explode(){
			$this->main_array["available_languages"]=str_replace('[','',$this->main_array["available_languages"]);
			$this->main_array["available_languages"]=str_replace(']','',$this->main_array["available_languages"]);			
			$this->main_array["available_languages"]=str_replace("'","",$this->main_array["available_languages"]);
			if($this->main_array["available_languages"]==null){return null;}
			$tb=explode(',',$this->main_array["available_languages"]);
			$this->available_languages=$tb;
			unset($this->main_array["available_languages"]);
			}
		function acceptable_aliases_explode(){
			if($this->main_array["acceptable_aliases"]==null){return null;}
			$this->main_array["acceptable_aliases"]=str_replace('NULL','',$this->main_array["acceptable_aliases"]);
			$tb=explode("\n",$this->main_array["acceptable_aliases"]);
			$this->acceptable_aliases=$tb;
			unset($this->main_array["acceptable_aliases"]);			
			
		}
		
		function header_filter_rules_explode(){
			if($this->main_array["header_filter_rules"]==null){
				writelogs("no header_filter_rules datas -> aborting",__CLASS__ . '/'. __FUNCTION__,__FILE__);
				return null;}
	        	
		    writelogs("Explode -> {$this->main_array["header_filter_rules"]}",__CLASS__ . '/'. __FUNCTION__,__FILE__);
			if(preg_match_all('#\((.*?)[,\s+](False|True])\)#',$this->main_array["header_filter_rules"],$re)){
				while (list ($num, $val) = each ($re[1]) ){
					$tb=explode(",",$val);
					$tb[0]=str_replace('\\\\','\\',$tb[0]);
					$tb[0]=str_replace("'","",$tb[0]);
					$this->header_filter_rules[$num]["pattern"]=$tb[0];
					$this->header_filter_rules[$num]["action"]=$tb[1];
				}
			}
		}
		
		function header_filter_rules_implode(){
			if(!is_array($this->header_filter_rules)){return null;}
			while (list ($num, $val) = each ($this->header_filter_rules) ){
				$ar[]="('{$val["pattern"]}',{$val["action"]},False)";
			}
			
			$res="[". implode(",",$ar) . "]";
			writelogs("implode=$res",__CLASS__ . '/' . __FUNCTION__,__FILE__ );
			return $res;
		}
		
		function acceptable_aliases_implode(){
			if(!is_array($this->acceptable_aliases)){
				writelogs("no acceptable_aliases datas -> aborting",__CLASS__ . '/'. __FUNCTION__,__FILE__);
				return null;}
				writelogs("implode " .count($this->acceptable_aliases) .' rows',__CLASS__ . '/'. __FUNCTION__,__FILE__);
				$ow=implode("\n",$this->acceptable_aliases);
				writelogs("implode '$ow'",__CLASS__ . '/'. __FUNCTION__,__FILE__);
			return $ow;
			
		}
		function Owners_implode(){
			if(!is_array($this->owner)){
				writelogs("no owner datas -> aborting",__CLASS__ . '/'. __FUNCTION__,__FILE__);
				return null;}
			while (list ($num, $val) = each ($this->owner) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
			
		}
		
		function ban_list_implode(){
		if(!is_array($this->ban_list)){
				writelogs("no ban_list datas -> aborting",__CLASS__ . '/'. __FUNCTION__,__FILE__);
				return null;}
			while (list ($num, $val) = each ($this->ban_list) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
		}
		
		function available_languages_implode(){
			if(!is_array($this->available_languages)){
				writelogs("no available_languages datas -> aborting",__CLASS__ . '/'. __FUNCTION__,__FILE__);
				return null;}
			while (list ($num, $val) = each ($this->available_languages) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
			
		}	
		
		function bounce_matching_headers_explode(){
			$this->bounce_matching_headers=explode("\n",$this->main_array["bounce_matching_headers"]);
			if(is_array($this->bounce_matching_headers)){
			while (list ($num, $val) = each ($this->bounce_matching_headers) ){
				if(trim($val)<>null){
					$r[]=$val;
				}
				}
			$this->bounce_matching_headers=$r;}
			
		}
		
		function filter_mime_types_explode(){
			
			$this->main_array["filter_filename_extensions"]=str_replace("NULL","",$this->main_array["filter_filename_extensions"]);
			
			$this->filter_mime_types=explode("\n",$this->main_array["filter_mime_types"]);
			$this->pass_mime_types=explode("\n",$this->main_array["pass_mime_types"]);
			$this->filter_filename_extensions=explode("\n",$this->main_array["filter_filename_extensions"]);
			$this->pass_filename_extensions=explode("\n",$this->main_array["pass_filename_extensions"]);
			
			
			if(is_array($this->filter_mime_types)){
			while (list ($num, $val) = each ($this->filter_mime_types) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";
						}}
				
				if(is_array($ow)){
					$this->filter_mime_types=$ow;
					reset($this->filter_mime_types);	
					}
			}
			
			
			if(is_array($this->filter_filename_extensions)){
			while (list ($num, $val) = each ($this->filter_filename_extensions) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";
						}}
				
				if(is_array($ow)){
					$this->filter_filename_extensions=$ow;
					reset($this->filter_filename_extensions);	
					}
			}			
			
			
					
		}
			
			
			
		
		
		function moderator_implode(){
			if(!is_array($this->moderator)){return null;}
			while (list ($num, $val) = each ($this->moderator) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
			
		}
		
		function accept_these_nonmembers_imlode(){
		if(!is_array($this->accept_these_nonmembers)){return null;}
			while (list ($num, $val) = each ($this->accept_these_nonmembers) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
		}
		
		function hold_these_nonmembers_implode(){
			if(!is_array($this->hold_these_nonmembers)){
				writelogs("hold_these_nonmembers=not an array",__CLASS__ . '/' . __FUNCTION__,__FILE__ );
				return null;
				}
			
			while (list ($num, $val) = each ($this->hold_these_nonmembers) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
			
		}

		function reject_these_nonmembers_implode(){
			if(!is_array($this->reject_these_nonmembers)){
				writelogs("reject_these_nonmembers=not an array",__CLASS__ . '/' . __FUNCTION__,__FILE__ );
				return null;
				}
			
			while (list ($num, $val) = each ($this->reject_these_nonmembers) ){
				if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
			
		}
		function discard_these_nonmembers_implode(){
			if(!is_array($this->discard_these_nonmembers)){writelogs("discard_these_nonmembers=not an array",__CLASS__ . '/' . __FUNCTION__,__FILE__ );return null;}
			
			while (list ($num, $val) = each ($this->discard_these_nonmembers) ){
					if(trim($val)<>null){
						$val=trim($val);
						$ow[]="'{$val}'";}
						}
			
			if(is_array($ow)){return implode(",",$ow);}
			
		}					

		//reject_these_nonmembers 
		
			
		function FillDefaults(){
			$fields_numerics=array("advertised"=>True,
									"bounce_processing"=>True,
									"administrivia"=>True,
									"unsubscribe_policy"=>True,
									"default_member_moderation"=>True,
									"scrub_nondigest"=>True,
									"emergency"=>True,
									"digest_is_default"=>True,
									"send_reminders"=>True,
									"max_days_to_hold"=>True,
									"forward_auto_discards"=>True,
									"admin_immed_notify"=>True,
									"require_explicit_destination"=>True,
									"admin_notify_mchanges"=>True,
									"digest_send_periodic"=>True,
									"respond_to_post_requests"=>True,
									"mime_is_default_digest"=>True,
									"anonymous_list"=>True,
									"encode_ascii_prefixes"=>True,
									"reply_goes_to_list"=>True,
									"obscure_addresses"=>True,
									"include_list_post_header"=>True,
									"generic_nonmember_action"=>True,
									"first_strip_reply_to"=>True,
									"include_rfc2369_headers"=>True,
									"max_message_size"=>True);
									
			if($this->main_array["subject_prefix"]==null){$this->main_array["subject_prefix"]="[$this->list_name]";}
			if($this->main_array["preferred_language"]==null){$this->main_array["preferred_language"]="en";}
			if($this->main_array["nondigestable"]==null){$this->main_array["nondigestable"]=1;}
			if($this->main_array["digest_size_threshhold"]==null){$this->main_array["digest_size_threshhold"]=30;}
			if($this->main_array["digest_send_periodic"]==null){$this->main_array["digest_send_periodic"]="1";}
			if($this->main_array["_new_volume"]==null){$this->main_array["_new_volume"]="1";}
			if($this->main_array["_send_digest_now"]==null){$this->main_array["_send_digest_now"]="0";}
			if($this->main_array["advertised"]==null){$this->main_array["advertised"]="1";}
			if($this->main_array["unsubscribe_policy"]==null){$this->main_array["unsubscribe_policy"]="0";}
			if($this->main_array["default_member_moderation"]==null){$this->main_array["default_member_moderation"]="0";}
			if($this->main_array["max_num_recipients"]==null){$this->main_array["max_num_recipients"]="10";}
			
			if(trim($this->main_array["new_member_options"])==null){$this->main_array["new_member_options"]=256;}
			
			if($this->main_array["bounce_processing"]==null){$this->main_array["bounce_processing"]='True';}
			if($this->main_array["bounce_score_threshold"]==null){$this->main_array["bounce_score_threshold"]='0.5';}
			if($this->main_array["bounce_info_stale_after"]==null){$this->main_array["bounce_info_stale_after"]='7';}
			if($this->main_array["bounce_you_are_disabled_warnings"]==null){$this->main_array["bounce_you_are_disabled_warnings"]='3';}
			if($this->main_array["bounce_you_are_disabled_warnings_interval"]==null){$this->main_array["bounce_you_are_disabled_warnings_interval"]='7';}
			if($this->main_array["bounce_unrecognized_goes_to_list_owner"]==null){$this->main_array["bounce_unrecognized_goes_to_list_owner"]='1';}
			if($this->main_array["bounce_notify_owner_on_disable"]==null){$this->main_array["bounce_notify_owner_on_disable"]='1';}
			if($this->main_array["bounce_notify_owner_on_removal"]==null){$this->main_array["bounce_notify_owner_on_removal"]='1';}
			if($this->main_array["archive"]==null){$this->main_array["archive"]='1';}
			if($this->main_array["archive_private"]==null){$this->main_array["archive_private"]='1';}
			if($this->main_array["archive_volume_frequency"]==null){$this->main_array["archive_volume_frequency"]='1';}
			if($this->main_array["gateway_to_news"]==null){$this->main_array["gateway_to_news"]='1';}
			if($this->main_array["gateway_to_mail"]==null){$this->main_array["gateway_to_mail"]='1';}
			if($this->main_array["news_moderation"]==null){$this->main_array["news_moderation"]='0';}
			if($this->main_array["news_prefix_subject_too"]==null){$this->main_array["news_prefix_subject_too"]='1';}
			
			if($this->main_array["autorespond_postings"]==null){$this->main_array["autorespond_postings"]='0';}
			if($this->main_array["autorespond_admin"]==null){$this->main_array["autorespond_admin"]='0';}
			if($this->main_array["autorespond_requests"]==null){$this->main_array["autorespond_requests"]='0';}
			if($this->main_array["autoresponse_graceperiod"]==null){$this->main_array["autoresponse_graceperiod"]='90';}
			
			if($this->main_array["filter_content"]==null){$this->main_array["filter_content"]='0';}
			if($this->main_array["collapse_alternatives"]==null){$this->main_array["collapse_alternatives"]='0';}
			if($this->main_array["convert_html_to_plaintext"]==null){$this->main_array["convert_html_to_plaintext"]='1';}
			if($this->main_array["filter_action"]==null){$this->main_array["filter_action"]='0';}
			if($this->main_array["topics_enabled"]==null){$this->main_array["topics_enabled"]='0';}
			if($this->main_array["topics_bodylines_limit"]==null){$this->main_array["topics_bodylines_limit"]='5';}
			if($this->main_array["host_name"]==null){$this->main_array["host_name"]=$_SERVER['SERVER_NAME'];}
			
			
			
			if(!is_numeric($this->main_array["autorespond_requests"])){$this->main_array["autorespond_requests"]=0;}
			if(!is_numeric($this->main_array["bounce_unrecognized_goes_to_list_owner"])){$this->main_array["bounce_unrecognized_goes_to_list_owner"]=1;}
			if(!is_numeric($this->main_array["bounce_notify_owner_on_disable"])){$this->main_array["bounce_notify_owner_on_disable"]=1;}
			if(!is_numeric($this->main_array["bounce_notify_owner_on_removal"])){$this->main_array["bounce_notify_owner_on_removal"]=1;}
			if(!is_numeric($this->main_array["archive"])){$this->main_array["archive"]=1;}
			if(!is_numeric($this->main_array["send_welcome_msg"])){$this->main_array["send_welcome_msg"]=0;}
			if(!is_numeric($this->main_array["send_goodbye_msg"])){$this->main_array["send_goodbye_msg"]=0;}
			if(!is_numeric($this->main_array["administrivia"])){$this->main_array["administrivia"]=1;}
			if(!is_numeric($this->main_array["subscribe_policy"])){$this->main_array["subscribe_policy"]=0;}
			if(!is_numeric($this->main_array["private_roster"])){$this->main_array["private_roster"]=0;}
			if(!is_numeric($this->main_array["member_moderation_action"])){$this->main_array["member_moderation_action"]=0;}
			
		
			
			
			while (list ($num, $val) = each ($this->main_array) ){
				if($fields_numerics[$num]){
					$val=trim($val);
					if(!is_numeric(trim($val))){
						if(strtolower($val)=='true'){$this->main_array[$num]=1;}
						if(strtolower($val)==null){$this->main_array[$num]=0;}
						if(strtolower($val)=='false'){$this->main_array[$num]=0;}
						if(strtolower($val)=='yes'){$this->main_array[$num]=1;}
						if(strtolower($val)=='no'){$this->main_array[$num]=0;}						
					}
				}
			}
			
			reset($this->main_array);
			
			
		}
		
		function Save(){

			$this->FillDefaults();
			
			
			$cfg="#Saved by artica\n";
			$cfg=$cfg. "real_name = '" . trim($this->main_array["real_name"]) . "'\n";
			$cfg=$cfg. "owner = [" . $this->Owners_implode() . "]\n";
			$cfg=$cfg. "ban_list = [" . $this->ban_list_implode() . "]\n";
			$cfg=$cfg. "host_name = '" . trim($this->main_array["host_name"]) . "'\n";
	
			$cfg=$cfg. "moderator = [" . $this->moderator_implode() . "]\n";	
			$cfg=$cfg. "description = '" . trim($this->main_array["description"])  . "'\n";
			$cfg=$cfg. "info = '" . trim($this->main_array["info"])  . "'\n";
			$cfg=$cfg. "subject_prefix = '" . trim($this->main_array["subject_prefix"])  . "'\n";
			$cfg=$cfg. "anonymous_list = " . trim($this->main_array["anonymous_list"])  . "\n";
			$cfg=$cfg. "first_strip_reply_to = " . trim($this->main_array["first_strip_reply_to"])  . "\n";
			$cfg=$cfg. "reply_goes_to_list = " . trim($this->main_array["reply_goes_to_list"])  . "\n";
			$cfg=$cfg. "reply_to_address = '" . trim($this->main_array["reply_to_address"])  . "'\n";
			$cfg=$cfg. "send_reminders = " . trim($this->main_array["send_reminders"])  . "\n";
			$cfg=$cfg. "umbrella_list = False\n";
			$cfg=$cfg. "umbrella_member_suffix = '-owner'\n";
			//$cfg=$cfg. "admin_member_chunksize = 30\n";
			$cfg=$cfg. "welcome_msg = \"\"\"" . trim($this->main_array["welcome_msg"])  . "\"\"\"\n";
			$cfg=$cfg. "goodbye_msg = \"\"\"" . trim($this->main_array["goodbye_msg"])  . "\"\"\"\n";
			$cfg=$cfg. "msg_header = \"\"\"" . trim($this->main_array["msg_header"])  . "\"\"\"\n";
			$cfg=$cfg. "msg_footer = \"\"\"" . trim($this->main_array["msg_footer"])  . "\"\"\"\n";
			$cfg=$cfg. "digest_header = \"\"\"" . trim($this->main_array["digest_header"])  . "\"\"\"\n";
			$cfg=$cfg. "digest_footer = \"\"\"" . trim($this->main_array["digest_footer"])  . "\"\"\"\n";
			$cfg=$cfg. "nonmember_rejection_notice = '" . trim($this->main_array["nonmember_rejection_notice"])  . "'\n";
			
			
			
			 
			$cfg=$cfg. "send_welcome_msg = " . trim($this->main_array["send_welcome_msg"])  . "\n";
			$cfg=$cfg. "send_goodbye_msg = " . trim($this->main_array["send_goodbye_msg"])  . "\n";
			
			$cfg=$cfg. "admin_immed_notify = " . trim($this->main_array["admin_immed_notify"])  . "\n";
			$cfg=$cfg. "admin_notify_mchanges = " . trim($this->main_array["admin_notify_mchanges"])  . "\n";
			$cfg=$cfg. "respond_to_post_requests = " . trim($this->main_array["respond_to_post_requests"])  . "\n";
			$cfg=$cfg. "emergency = " . trim($this->main_array["emergency"])  . "\n";
			
			$cfg=$cfg. "new_member_options = " . trim($this->main_array["new_member_options"])  . "\n";
			$cfg=$cfg. "administrivia = " . trim($this->main_array["administrivia"])  . "\n";
			$cfg=$cfg. "max_message_size = " . trim($this->main_array["max_message_size"])  . "\n";
			$cfg=$cfg. "max_days_to_hold = " . trim($this->main_array["max_days_to_hold"])  . "\n";
			$cfg=$cfg. "include_rfc2369_headers = " . trim($this->main_array["include_rfc2369_headers"])  . "\n";
			$cfg=$cfg. "include_list_post_header = " . trim($this->main_array["include_list_post_header"])  . "\n";
			$cfg=$cfg. "preferred_language = '" . trim($this->main_array["preferred_language"])  . "'\n";			
			$cfg=$cfg. "available_languages = [" . $this->available_languages_implode() . "]\n";	
			$cfg=$cfg. "encode_ascii_prefixes = '" . trim($this->main_array["encode_ascii_prefixes"])  . "'\n";
			$cfg=$cfg. "nondigestable = " . trim($this->main_array["nondigestable"])  . "\n";
			$cfg=$cfg. "scrub_nondigest = " . trim($this->main_array["scrub_nondigest"])  . "\n";
			$cfg=$cfg. "digest_is_default = " . trim($this->main_array["digest_is_default"])  . "\n";
			$cfg=$cfg. "mime_is_default_digest = " . trim($this->main_array["mime_is_default_digest"])  . "\n";
			$cfg=$cfg. "digest_send_periodic = " . trim($this->main_array["digest_send_periodic"])  . "\n";
			$cfg=$cfg. "digest_size_threshhold = " . trim($this->main_array["digest_size_threshhold"])  . "\n";
			$cfg=$cfg. "advertised = " . trim($this->main_array["advertised"])  . "\n";
			$cfg=$cfg. "unsubscribe_policy = " . trim($this->main_array["unsubscribe_policy"])  . "\n";
			$cfg=$cfg. "subscribe_policy = " . trim($this->main_array["subscribe_policy"])  . "\n";
			$cfg=$cfg. "private_roster = " . trim($this->main_array["private_roster"])  . "\n";
			$cfg=$cfg. "obscure_addresses = " . trim($this->main_array["obscure_addresses"])  . "\n";
			$cfg=$cfg. "default_member_moderation = " . trim($this->main_array["default_member_moderation"])  . "\n";
			$cfg=$cfg. "member_moderation_action = " . trim($this->main_array["member_moderation_action"])  . "\n";
			$cfg=$cfg. "member_moderation_notice = '" . trim($this->main_array["member_moderation_notice"])  . "'\n";
			$cfg=$cfg. "accept_these_nonmembers = [" . $this->accept_these_nonmembers_imlode() . "]\n";	
			$cfg=$cfg. "hold_these_nonmembers = [" . $this->hold_these_nonmembers_implode() . "]\n";	
			$cfg=$cfg. "reject_these_nonmembers = [" . $this->reject_these_nonmembers_implode() . "]\n";
			$cfg=$cfg. "discard_these_nonmembers = [" . $this->discard_these_nonmembers_implode() . "]\n";
			$cfg=$cfg. "forward_auto_discards = " . trim($this->main_array["forward_auto_discards"])  . "\n";
			$cfg=$cfg. "generic_nonmember_action = " . trim($this->main_array["generic_nonmember_action"])  . "\n";
			$cfg=$cfg. "require_explicit_destination = " . trim($this->main_array["require_explicit_destination"])  . "\n";
			$cfg=$cfg. "acceptable_aliases = \"\"\"" . trim($this->acceptable_aliases_implode())  . "\"\"\"\n";
			
			
			
			$header_filter_rules_implode=$this->header_filter_rules_implode();
			if(trim($header_filter_rules_implode)<>null){
				$cfg=$cfg. "header_filter_rules = $header_filter_rules_implode\n";
			}
			
			if(is_array($this->bounce_matching_headers)){
				$cfg=$cfg. "bounce_matching_headers = \"\"\"" . implode("\n",$this->bounce_matching_headers)  . "\"\"\"\n";
			}
			$cfg=$cfg. "bounce_processing = " . trim($this->main_array["bounce_processing"]) . "\n";
			$cfg=$cfg. "bounce_score_threshold = " . trim($this->main_array["bounce_score_threshold"]) . "\n";
			$cfg=$cfg. "bounce_info_stale_after = " . trim($this->main_array["bounce_info_stale_after"]) . "\n";
			$cfg=$cfg. "bounce_you_are_disabled_warnings = " . trim($this->main_array["bounce_you_are_disabled_warnings"]) . "\n";
			$cfg=$cfg. "bounce_you_are_disabled_warnings_interval = " . trim($this->main_array["bounce_you_are_disabled_warnings_interval"]) . "\n";
			$cfg=$cfg. "bounce_unrecognized_goes_to_list_owner = " . trim($this->main_array["bounce_unrecognized_goes_to_list_owner"]) . "\n";
			$cfg=$cfg. "bounce_notify_owner_on_disable = " . trim($this->main_array["bounce_notify_owner_on_disable"]) . "\n";
			$cfg=$cfg. "bounce_notify_owner_on_removal = " . trim($this->main_array["bounce_notify_owner_on_removal"]) . "\n";
			$cfg=$cfg. "archive = " . trim($this->main_array["archive"]) . "\n";
			$cfg=$cfg. "archive_private = " . trim($this->main_array["archive_private"]) . "\n";
			$cfg=$cfg. "archive_volume_frequency = " . trim($this->main_array["archive_volume_frequency"]) . "\n";
			$cfg=$cfg. "nntp_host = '" . trim($this->main_array["nntp_host"]) . "'\n";
			$cfg=$cfg. "linked_newsgroup = '" . trim($this->main_array["linked_newsgroup"]) . "'\n";
			$cfg=$cfg. "gateway_to_news = " . trim($this->main_array["gateway_to_news"]) . "\n";
			$cfg=$cfg. "gateway_to_mail = " . trim($this->main_array["gateway_to_mail"]) . "\n";
			$cfg=$cfg. "news_moderation = " . trim($this->main_array["news_moderation"]) . "\n";
			$cfg=$cfg. "news_prefix_subject_too = " . trim($this->main_array["news_prefix_subject_too"]) . "\n";
			$cfg=$cfg. "autorespond_postings = " . trim($this->main_array["autorespond_postings"]) . "\n";
			$cfg=$cfg. "autorespond_admin = " . trim($this->main_array["autorespond_admin"]) . "\n";
			
			
			$cfg=$cfg. "autorespond_requests = " . trim($this->main_array["autorespond_requests"]) . " \n";
			$cfg=$cfg. "autoresponse_graceperiod = " . trim($this->main_array["autoresponse_graceperiod"]) . "\n";
			
			
			
			$cfg=$cfg. "autoresponse_postings_text = \"\"\"" . trim($this->main_array["autoresponse_postings_text"]) . "\"\"\"\n";
			
			
			$this->main_array["autorespond_admin_text"]=str_replace('"','',$this->main_array["autorespond_admin_text"]);
			
			$cfg=$cfg. "autoresponse_admin_text = \"\"\"" . trim($this->main_array["autoresponse_admin_text"]) . "\"\"\"\n";
			
			
			$cfg=$cfg. "autoresponse_request_text =\"\"\"" . trim($this->main_array["autoresponse_request_text"]) . "\"\"\"\n";
			
			$cfg=$cfg. "filter_content = " . trim($this->main_array["filter_content"]) . "\n";
			$cfg=$cfg. "collapse_alternatives = " . trim($this->main_array["collapse_alternatives"]) . "\n";
			$cfg=$cfg. "convert_html_to_plaintext = " . trim($this->main_array["convert_html_to_plaintext"]) . "\n";
			$cfg=$cfg. "filter_action = " . trim($this->main_array["filter_action"]) . "\n";
			if(is_array($this->filter_mime_types)){
				$cfg=$cfg. "filter_mime_types = \"\"\"" . trim(implode("\n",$this->filter_mime_types))  . "\"\"\"\n";
			}
			
			if(is_array($this->pass_mime_types)){
				$cfg=$cfg. "pass_mime_types = \"\"\"" . trim(implode("\n",$this->pass_mime_types))  . "\"\"\"\n";
			}
			$cfg=$cfg. "filter_filename_extensions = \"\"\"" . implode("\n",$this->filter_filename_extensions)  . "\"\"\"\n";
			$cfg=$cfg. "pass_filename_extensions = \"\"\"" . implode("\n",$this->pass_filename_extensions)  . "\"\"\"\n";
						
			$cfg=$cfg. "topics_enabled = " . trim($this->main_array["topics_enabled"]) . "\n";
			$cfg=$cfg. "topics_bodylines_limit = " . trim($this->main_array["topics_bodylines_limit"]) . "\n";
			$cfg=$cfg. "topics = [" . trim($this->topics_implode()) . "]\n";
		
		
			$ldap=new clladp();
			$dn="cn=$this->list_name,cn=mailman,cn=artica,$ldap->suffix";
			$upd["MailmanListConfigDatas"][0]="$cfg";
			$upd["mailmanMsgFooter"][0]=$this->main_array["msg_footer"];
			$upd["mailmanMsgHeader"][0]=$this->main_array["msg_header"];
			$upd["mailmanDigestHeader"][0]=$this->main_array["digest_header"];
			
			$upd["mailmanDigestFooter"][0]=$this->main_array["digest_footer"];
			$upd["mailmanAutoresponseAdminText"][0]=$this->main_array["autoresponse_admin_text"];
			$upd["mailmanAutoresponsePostingsText"][0]=$this->main_array["autoresponse_postings_text"];
			$upd["mailmanAutoresponseRequestText"][0]=$this->main_array["autoresponse_request_text"];
			$upd["mailmanWelcomeMsg"][0]=$this->main_array["welcome_msg"];
			$upd["mailmanGoodbyeMsg"][0]=$this->main_array["goodbye_msg"];
			$upd["HostNameList"][0]=$this->main_array["host_name"];
			
			
			
			if(trim($this->main_array["acceptable_aliases"])<>null){$upd["mailmanAcceptableAliases"][0]=$this->main_array["acceptable_aliases"];}
			if(trim($this->main_array["filter_filename_extensions"])<>null){$upd["FilterFilenameExtensions"][0]=$this->main_array["filter_filename_extensions"];}
			if(!$ldap->Ldap_modify($dn,$upd)){echo $ldap->ldap_last_error;}
			
			
		}
		
		
		
	}
	
?>	