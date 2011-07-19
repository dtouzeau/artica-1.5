<?php
	
			
			
			
			
			if($_SESSION["uid"]<>-100){
						
			
			
				$sub_array_pages["ACCOUNT"]["javascript:Loadjs(\"users.account.php?js=yes\")"]='{account}';
				$this->main_left_menus["users.account.php"]=array(
							"TITLE"=>"{account}",
							"IMG"=>"identity-64.png",
							"TEXT"=>"{menu_account_text}",
							"AJAX"=>"javascript:Loadjs('users.account.php?js=yes')",
							"NOAJAX"=>false);
				}
				
			if($this->POSTFIX_INSTALLED){	
			if($_SESSION["backup_feature"]){
				$sub_array_pages["ACCOUNT"]["users.backup.php"]="{backup}";
				$this->main_left_menus["users.backup.php"]=array(
					"TITLE"=>"{backup}",
					"IMG"=>"folder-64-artica-backup.png",
					"TEXT"=>"{menu_backup_text}",
					"NOAJAX"=>true);
				}
			}
							
//************************************** avec postfix **********************************************************************************************			
		if(!$asSuperAdmin){
			
					if($this->AsOrgAdmin){
									$this->main_left_menus['ORG']=array(
										"TITLE"=>"{$_SESSION["ou"]}",
										"IMG"=>"folder-org-64.png",
										"TEXT"=>"{manage_organisations_text}",
										"POPUP"=>true,
										"NOAJAX"=>false,
										"AJAX"=>"javascript:Loadjs('domains.manage.org.index.php?js=yes&ou={$_SESSION["ou"]}')",
										);
						}
						
						
						
					if($this->AsMessagingOrg){

						
					}
			
			   if($this->EnablePerUserRemoteAddressBook==1){
			   		$this->main_left_menus["users.addressbook.index.php"]=array(
										"TITLE"=>"{ADDRESSBOOK}",
										"IMG"=>"64-addressbook.png",
										"TEXT"=>"{ADDRESSBOOK_TEXT}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('users.addressbook.index.php')",
										);
			   	
			   }
			   if($this->cyrus_imapd_installed){
					$this->main_left_menus["my.addressbook.php"]=array(
										"TITLE"=>"{YOUR_ADDRESSBOOK}",
										"IMG"=>"64-your-address-book.png",
										"TEXT"=>"{YOUR_ADDRESSBOOK_TEXT}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('my.addressbook.php')",
										);
			   }			   
			   
			  
			
			
									
				if($this->OPENVPN_INSTALLED){
					if($this->AllowOpenVPN){
								$this->main_left_menus["users.openvpn.index.php"]=array(
										"TITLE"=>"{APP_OPENVPN}",
										"IMG"=>"64-openvpn.png",
										"TEXT"=>"{menu_openvpn_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('users.openvpn.index.php?&uid={$_SESSION["uid"]}')",
										);
					}
				}
			
			
			if($this->POSTFIX_INSTALLED){
						if($this->cyrus_imapd_installed==true){
							if($this->roundcube_installed==true){
								if($_SESSION["MailboxActive"]=="TRUE"){
									include_once(dirname(__FILE__).'/class.roundcube.inc');
									$r=new roundcube();
									$sub_array_pages["ACCOUNT"]["popup:".$r->roundCubeArray["user_link"]]="{webmail}";
										$this->main_left_menus[$r->roundCubeArray["user_link"]]=array(
										"TITLE"=>"{webmail}",
										"IMG"=>"folder-usermailbox-64.png",
										"TEXT"=>"{menu_webmail_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
									
									}
							
							}
							
							if($_SESSION["MailboxActive"]=="TRUE"){
								$this->main_left_menus["users.out-of-office.php"]=array(
										"TITLE"=>"{OUT_OF_OFFICE}",
										"IMG"=>"64-out-of-office.png",
										"TEXT"=>"{menu_OUT_OF_OFFICE_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('sieve.vacation.php')",
										);
								}
							
							if($this->APP_ATOPENMAIL_INSTALLED==true){
								if($_SESSION["MailboxActive"]=="TRUE"){
									include_once(dirname(__FILE__).'/class.roundcube.inc');
									$r=new roundcube();
									$sub_array_pages["ACCOUNT"]["popup:mail/index.php"]="{APP_ATOPENMAIL}";
										$this->main_left_menus["mail/index.php"]=array(
										"TITLE"=>"{APP_ATOPENMAIL}",
										"IMG"=>"64-atmail.png",
										"TEXT"=>"{menu_webmail_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
									}
							}								
		
									
									
								if($this->AllowFetchMails){
									if($_SESSION["MailboxActive"]=="TRUE"){
									if($this->fetchmail_installed){
										$sub_array_pages["ACCOUNT"]["users.fetchmail.index.php"]="{fetchmail}";
										$this->main_left_menus["users.fetchmail.index.php"]=array(
										"TITLE"=>"{fetchmail}",
										"IMG"=>"folder-64-fetchmail.png",
										"TEXT"=>"{menu_fetchmail_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('wizard.fetchmail.newbee.php?script=yes&uid={$_SESSION["uid"]}')",
										);
										
									/*$sub_array_pages["ACCOUNT"]["users.hotmail.index.php"]="{hotmail}";
										$this->main_left_menus["users.hotmail.index.php"]=array(
										"TITLE"=>"{hotmail}",
										"IMG"=>"64-msn.png",
										"TEXT"=>"{fetch_hotmail}",
										"NOAJAX"=>true);*/
									
										}									
									
									}}
									
									
								/*if($this->AllowEmailing){
									$this->main_left_menus["users.emailing.index.php"]=array(
										"TITLE"=>"{emailing}",
										"IMG"=>"64-emailing.png",
										"TEXT"=>"{menu_emailing_text}",
										"AJAX"=>"javascript:Loadjs('users.emailing.php?js=yes')",
										"NOAJAX"=>false);
									
								}*/
									
						}
						
				if(!$this->cyrus_imapd_installed==true){	
						if($this->GNARWL_INSTALLED){
								$this->main_left_menus["users.out-of-office.php"]=array(
										"TITLE"=>"{OUT_OF_OFFICE}",
										"IMG"=>"64-out-of-office.png",
										"TEXT"=>"{menu_OUT_OF_OFFICE_text}",
										"NOAJAX"=>false,
										"AJAX"=>"Loadjs('users.out-of-office.php')",
										);
								}
						}						
					
				if($_SESSION["uid"]<>-100){
						if($this->cyrus_imapd_installed){
							if($this->AllowChangeMailBoxRules){
								if($_SESSION["MailboxActive"]=="TRUE"){
									$sub_array_pages["ACCOUNT"]["user.sieve.index.php"]='{manage_your_mailbox_rules}';
									$this->main_left_menus["user.sieve.index.php"]=array(
											"TITLE"=>"{mailbox_rules}",
											"IMG"=>"folder-mailrules-64.png",
											"TEXT"=>"{menu_sieve_text}",
											"NOAJAX"=>false,
											"AJAX"=>"Loadjs('users.sieve.php')",);
							}
						}}
				
				if($this->EnableAmavisDaemon==1){
					if($this->AllowEditAsWbl==true){
					$sub_array_pages["ACCOUNT"]["users.aswb.php"]="{AllowEditAsWbl}";
					$this->main_left_menus["users.aswb.php"]=array(
									"TITLE"=>"{AllowEditAsWbl}",
									"IMG"=>"folder-domains-blacklist-64.png",
									"TEXT"=>"{AllowEditAsWbl_text}",
									"NOAJAX"=>true);
					}
					
					if($this->AllowChangeUserKas==1){
						$this->main_left_menus["javascript:Loadjs('users.amavis.php')"]=array(
									"TITLE"=>"{AntiSpam_parameters}",
									"IMG"=>"folder-caterpillar.png",
									"TEXT"=>"{AntiSpam_parameters_text}",
									"NOAJAX"=>true);
					}
				
				
				
				}
					
					
					if($this->DOTCLEAR_INSTALLED){
						if($_SESSION["DotClearUserEnabled"]==1){
							$sub_array_pages["ACCOUNT"]["javascript:YahooWin(450,\"dotclear.index.php?UserDotClear=yes\")"]="{APP_DOTCLEAR}";
							$this->main_left_menus["javascript:YahooWin(450,'dotclear.index.php?UserDotClear=yes')"]=array(
									"TITLE"=>"{APP_DOTCLEAR}",
									"IMG"=>"64-dotclear.png",
									"TEXT"=>"{dotclear_user_intro}",
									"NOAJAX"=>true);
						}
					}
								
					}
		
					if($_SESSION["backup_feature"]){
						$sub_array_pages["ACCOUNT"]["users.backup.php"]="{backup}";
						$this->main_left_menus["users.backup.php"]=array(
									"TITLE"=>"{backup}",
									"IMG"=>"folder-64-artica-backup.png",
									"TEXT"=>"{menu_backup_text}",
									"NOAJAX"=>true);
						
					}
						
					
						$sub_array_pages["ACCOUNT"]["user.quarantine.query.php"]="{quarantine}";
					
						$this->main_left_menus["user.quarantine.query.php"]=array(
									"TITLE"=>"{quarantine}",
									"IMG"=>"folder-quarantine-0-64.png",
									"TEXT"=>"{menu_quarantine_text}",
									"NOAJAX"=>true);
									
											
					if($this->POSTFIX_INSTALLED){
						if($_SESSION["MailboxActive"]=="TRUE"){
									$this->main_left_menus['statistics']=array(
										"TITLE"=>"{statistics}",
										"IMG"=>"64-charts.png",
										"TEXT"=>"{menu_statistic_text}",
										"POPUP"=>true,
										"NOAJAX"=>false,
										"AJAX"=>"javascript:Loadjs('users.index.php?graph-js=yes')",
										);
						}
					}
					
			

										
						
//----------------------------------------- OBM -------------------------------------------------------			
			if($this->OBM_INSTALLED){
					if($this->OBMEnabled){
					include_once(dirname(__FILE__).'/class.obm.inc');
							$obm=new obm();
							if($obm->external_url==null){$obm->external_url=$this->hostname;}
							$sub_array_pages["ACCOUNT"]["popup:https://$obm->external_url:$obm->apache_listen"]="{calendar}";
							$this->main_left_menus["https://$obm->external_url:$obm->apache_listen"]=array(
										"TITLE"=>"{calendar}",
										"IMG"=>"64-obm.png",
										"TEXT"=>"{menu_calendar_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
							
						}else{
							writelogs("OBM is not enabled, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
						}
					}else{
						writelogs("OBM is not installed, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
					}
	
//-----------------------------------------//-----------------------------------------//-----------------
			if($this->OBM2_INSTALLED){
				$sock=new sockets();
				$Obm2ListenPort=trim($sock->GET_INFO('Obm2ListenPort'));
				$Obm2Externaluri=trim($sock->GET_INFO('Obm2Externaluri'));	
				if($Obm2ListenPort==null){$Obm2ListenPort=8080;}					
				if(trim($Obm2Externaluri)==null){$Obm2Externaluri="http://$this->hostname:$Obm2ListenPort";}
				$sub_array_pages["ACCOUNT"]["popup:$Obm2Externaluri"]="{APP_OBM2} {calendar}";
				$this->main_left_menus[$Obm2Externaluri]=array(
										"TITLE"=>"{APP_OBM2} {calendar}",
										"IMG"=>"64-obm2.png",
										"TEXT"=>"{menu_calendar_text}",
										"POPUP"=>true,
										"NOAJAX"=>true);
			}
	
	
//-----------------------------------------//-----------------------------------------//-----------------

//-----------------------------------------//-----------------------------------------//-----------------
			if($this->XAPIAN_PHP_INSTALLED){
				if($this->INSTANSEARCH_ENABLE){
					$sub_array_pages["ACCOUNT"]["xapian.index.php"]="{InstantSearch}";
					$this->main_left_menus["xapian.index.php"]=array(
											"TITLE"=>"{InstantSearch}",
											"IMG"=>"64-xapian.png",
											"TEXT"=>"{InstantSearch_text}",
											"POPUP"=>false,
											"NOAJAX"=>true);
				}
			}
	
	
//-----------------------------------------//-----------------------------------------//-----------------				
			

		}}

		//************************************************************************************************************************************	
//#######################################################################################################################
		
		
		
?>