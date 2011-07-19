<?php
include_once(dirname(__FILE__).'/class.user.inc');
class opengoo{
	var $uidNumber;
	var $database="opengoo";
	var $www_servername;
	var $username;
	var $email;
	var $token;
	var $salt;
	var $twister;
	var $display_name;
	var $title;
	var $avatar_file;
	
	
	function opengoo($uidNumber=null,$database=null){
		if($database<>null){
			$this->database=$database;
		}
		
		if($uidNumber<>null){
			$this->uidNumber=$uidNumber;
			$this->loadDatas();
		}

		
	}
	
	function loadDatas(){
		$sql="SELECT * FROM og_users WHERE id=$this->uidNumber";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		$this->username=$ligne["username"];
		$this->email=$ligne["email"];
		$this->token=$ligne["token"];
		$this->twister=$ligne["twister"];
		$this->display_name=$ligne["display_name"];
		$this->title=$ligne["title"];
		$this->salt=$ligne["salt"];
		
	}
	
	
	function get_Organization($web_servername){
		$ldap=new clladp();
		$dn="dc=organizations,$ldap->suffix";
		$attr=array();
		$pattern="(&(objectclass=apacheConfig)(apacheServerName=$web_servername))";
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		$dn=$hash[0]["dn"];
		if(preg_match("#ou=www,ou=(.+?),dc=organizations#",$dn,$re) ){
			return $re[1];
		}
	}
	
	
	function AddCompagny($org){
		if(trim($org)==null){return null;}
		$id=$this->Companyid($org);
		write_syslog("Organization $org \"$id\" company id",__FILE__);
		if($this->Companyid($org)==null){
			$q=new mysql();
			write_syslog("Adding organization $org",__FILE__);
			$sql="INSERT INTO og_companies(name,client_of_id) VALUES('$org',0)";
			$q->QUERY_SQL($sql,$this->database);
		}
		
	}
	
	function UpdateUser($uid){
		if($this->token==null){$this->_add($uid);}
		$ct=new user($uid);
		$token = sha1($this->salt . $ct->password);
		$sql="UPDATE og_users SET 
			email='$ct->mail',
			display_name='$ct->DisplayName',
			title='$ct->title',
			token='$token' WHERE id=$ct->uidNumber";
		
		$q=new mysql();
		write_syslog("updating contact $uid",__FILE__);
		$q->QUERY_SQL($sql,$this->database);
		if($this->ProjectID($ct->uidNumber)==null){$this->Add_projectid($ct->uidNumber);}
		if($this->ProjectTemplateID($ct->uidNumber)==null){$this->Add_projecTemplateID($ct->uidNumber,$ct->DisplayName);}
		$this->CheckMailAccount($ct->uidNumber,$ct);
	}
	
	function _add($uid){
		$ct=new user($uid);
		$salt = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
		$token = sha1($salt . $ct->password);
		$twister=StringTwister::getTwister();
		$Companyid=1;
		$date=date('y-m-d H:i:s');
		$sql="INSERT INTO og_users (id,salt,token,twister,username,company_id,personal_project_id,created_on,updated_on,last_login,last_visit,last_activity) 
		VALUES('$ct->uidNumber','$salt','$token','$twister','$uid','$Companyid','$ct->uidNumber','$date','$date','$date','$date','$date')";
		$q=new mysql();
		writelogs("Adding new contact $uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql,$this->database);
		if(!$q->ok){
			writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$ProjectTemplateID=$this->ProjectTemplateID($ct->uidNumber);
		if($ProjectTemplateID==null){$this->Add_projecTemplateID($ct->uidNumber,$ct->DisplayName);}
		$ProjectID=$this->ProjectID($ct->uidNumber);
		if($ProjectID==null){$this->Add_projectid($ct->uidNumber);}
		$this->CheckMailAccount($ct->uidNumber,$ct);
		
	}
	
	
	function Companyid($organization){
		if(trim($organization)==null){return null;}
		$sql="SELECT * FROM og_companies WHERE name='$organization'";
		
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));	
		
		
		return trim($ligne["id"]);
	}
	
	
	private function ProjectID($uidNumber){
		if(trim($uidNumber)==null){return null;}
		$sql="SELECT * FROM og_project_users WHERE user_id='$uidNumber'";
		
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));	
		return trim($ligne["project_id"]);
	}

	public function UpdateCompany($ou){
		$name=$this->GetCompanyName(1);
		if($name==null){
			$sql="INSERT INTO `og_companies` (`id`, `client_of_id`, `name`, `email`, `notes`, `homepage`, `address`, `address2`, `city`, `state`, `zipcode`, `country`, `phone_number`, `fax_number`, `logo_file`, `timezone`, `hide_welcome_info`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`) VALUES
			(1, 0, 'company', '', '', '', '', '', '', '', '', '', '', '', '', 0.0, 0, NOW(), 1, NOW(), 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0);
			";
		}else{
			$sql="UPDATE og_companies SET  name='$ou' WHERE id=1";
		}
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
	}
	
	private function GetCompanyName($ID){
		$sql="SELECT name FROM og_companies WHERE id=$ID";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		return $ligne["name"];
	}
	
	private function CheckMailAccount($uidNumber,$UserClass){
		$id=$this->MailAccountID($uidNumber);

		$array["user_id"]=$uidNumber;
		$array["name"]=$UserClass->DisplayName." Inbox";
		$array["email"]=$UserClass->uid;
		$array["email_addr"]=$UserClass->mail;
		$array["password"]=$this->ENCRYPT_DECRYPT($UserClass->password);
		$array["server"]="127.0.0.1";
		$array["is_imap"]="1";
		$array["incoming_ssl"]="0";
		$array["incoming_ssl_port"]="993";
		$array["smtp_server"]="127.0.0.1";
		$array["smtp_port"]="25";
		$array["smtp_use_auth"]=1;
		$array["del_from_server"]=0;
		$array["last_checked"]="0000-00-00 00:00:00";
		$array["is_default"]=1;
		$array["workspace"]=$uidNumber;
		$array["sender_name"]=$UserClass->DisplayName;
		
		while (list ($num, $ligne) = each ($array) ){
			$fields[]="`$num`";
			$datas[]="'$ligne'";
		}
		
		
		$sql_add="INSERT INTO `og_mail_accounts` (".implode(",",$fields).") VALUES (".implode(",",$datas).")";
		$sql_edit="UPDATE `og_mail_accounts` SET email_addr='$UserClass->mail', password='".$this->ENCRYPT_DECRYPT($UserClass->password)."',
		email='$UserClass->uid' WHERE id='$id'";
		
		if($UserClass->MailboxActive<>'TRUE'){return;}
		
		$q=new mysql();
		if($id==0){
			writelogs("Adding new email account for $UserClass->mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$q->QUERY_SQL($sql_add,$this->database);
			if(!$q->ok){
				writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
			}
		}else{
			writelogs("Edit email account for $UserClass->mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$q->QUERY_SQL($sql_edit,$this->database);
			if(!$q->ok){
				writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
			}			
		}
		
		$this->CheckIMAPFolders($uidNumber,$UserClass);
		
	}
	
	
	private function CheckIMAPFolders($uidNumber,$UserClass){
		$id=$this->MailAccountID($uidNumber);
		if($id==0){return;}
		$q=new mysql();
		$sql="SELECT COUNT(folder_name) as tcount FROM og_mail_account_imap_folder WHERE account_id='$id'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["tcount"]==0){
				writelogs("Add default folders to check",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				
				$sql="INSERT INTO `og_mail_account_imap_folder` (`account_id`, `folder_name`, `check_folder`) VALUES
				($id, 'INBOX', 1),
				($id, 'INBOX/Drafts', 1),
				($id, 'INBOX/Junk', 1),
				($id, 'INBOX/Sent', 1),
				($id, 'INBOX/Trash', 1);";
				
				$q->QUERY_SQL($sql,$this->database);
					if(!$q->ok){
						writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
					}
		}
		

		$sql="SELECT id FROM og_mail_account_users WHERE account_id='$id' AND user_id='$uidNumber'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["id"]==null){$ligne["id"]=0;}		
		if($ligne["id"]==0){
			$sql="INSERT INTO `og_mail_account_users` (`account_id`, `user_id`, `can_edit`, `is_default`, `signature`, `sender_name`) VALUES
			($id, $uidNumber, 0, 1, '', '$UserClass->DisplayName');";
			
			$q->QUERY_SQL($sql,$this->database);
			if(!$q->ok){
				writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
			}
		}
		
		
		
	}
	
	private function MailAccountID($uidNumber){
		$sql="SELECT id FROM og_mail_accounts WHERE user_id=$uidNumber AND server='127.0.0.1' LIMIT 0,1";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));	
		if($ligne["id"]==null){$ligne["id"]=0;}		
		return $ligne["id"];
	}
	
	
	private function Add_projectid($uidNumber){

		$sql="INSERT INTO `og_project_users` 
		(`project_id`, `user_id`, `created_on`, `created_by_id`,`can_read_messages`, `can_write_messages`,`can_read_tasks`, `can_write_tasks`, 
		`can_read_milestones`,`can_write_milestones`,`can_read_files`, 
		`can_write_files`, 
		`can_read_events`, 
		`can_write_events`, 
		`can_read_weblinks`, 
		`can_write_weblinks`, 
		`can_read_mails`, `can_write_mails`, 
		`can_read_contacts`,
		`can_write_contacts`, 
		`can_read_comments`, 
		`can_write_comments`, 
		`can_assign_to_owners`, 
		`can_assign_to_other`) VALUES
		($uidNumber, $uidNumber, NOW(), $uidNumber, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1)";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		
	}
	
	private function ProjectTemplateID($uidNumber){
		if(trim($uidNumber)==null){return null;}
		$sql="SELECT id FROM og_projects WHERE id='$uidNumber'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));	
		return trim($ligne["id"]);
		}
		
		
	private function Add_projecTemplateID($uidNumber,$DisplayName){
		$sql="INSERT INTO `og_projects` (`id`, `name`, `description`, `show_description_in_overview`, `completed_on`, `completed_by_id`, `created_on`, `created_by_id`, 
		`updated_on`, `updated_by_id`, `color`, `parent_id`, `p1`, `p2`, `p3`, `p4`, `p5`, `p6`, `p7`, `p8`, `p9`, `p10`) VALUES
		($uidNumber, '$DisplayName Personal', '', 0, '0000-00-00 00:00:00', 0, NOW(), $uidNumber, NOW(),
		 1, 0, 0, $uidNumber, 0, 0, 0, 0, 0, 0, 0, 0, 0);";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		
	}

	function UpdateAdmin($username,$password){
		//default was 1 ID
		if($this->isOPenGooUserExists(1)){
			writelogs("$username exists, update it",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->UpdateAdminPass($username,$password);
		}else{
			writelogs("$username does not exists, add it",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->AdNewAdmin($username,$password);
		}
		
		if($this->IsProjectExists(1)){$this->CreatePersonalProject(1);}
		
		
	}
	
	private function isOPenGooUserExists($ID){
		$sql="SELECT username FROM og_users WHERE id=$ID";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		$username=trim($ligne["username"]);
		if($username<>null){return true;}
		return false;
	}
	
	private function GetSalt($ID){
		$sql="SELECT salt FROM og_users WHERE id=$ID";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		return $ligne["salt"];
	}	
	
	private function Getemail($ID){
		$sql="SELECT email FROM og_users WHERE id=$ID";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if(!$q->ok){
			writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		return trim($ligne["email"]);
	}
	
	private function UpdatePassword($ID,$password){
		$salt=$this->GetSalt($ID);
		
		
		writelogs("salt=\"$salt\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if($salt==null){
			$salt = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
			$token = sha1($salt . $password);
			$twister=StringTwister::getTwister();
			
			$sql="UPDATE og_users SET token='$token',salt='$salt',twister='$twister' WHERE id=$ID";
		}else{
			$token = sha1($salt . $password);
			writelogs("TOKEN=\"$token\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="UPDATE og_users SET token='$token' WHERE id=$ID";
		}
		
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
	}
	
	private function AdNewAdmin($username,$password){
			$salt = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
			$token = sha1($salt . $password);
			$twister=StringTwister::getTwister();
			$today="NOW()";
		$sql="INSERT INTO `og_users` (`id`, `company_id`, `personal_project_id`,
		 `username`, `email`, `token`, `salt`, `twister`, `display_name`,
		  `title`, `avatar_file`, `timezone`, `created_on`,
		   `created_by_id`, `updated_on`, `updated_by_id`, `last_login`,
		    `last_visit`, `last_activity`, `can_edit_company_data`,
		     `can_manage_security`, `can_manage_workspaces`,
		      `can_manage_configuration`,
		       `can_manage_contacts`,
		        `can_manage_templates`, 
		        `can_manage_reports`,
		         `can_manage_time`, 
		         `can_add_mail_accounts`,
		          `auto_assign`, `default_billing_id`) VALUES
			(1, 1, 1, '$username', '$username@$this->www_servername', '$token', '$salt', '$twister', '', '',
		 '', 1.0, $today, 0, $today, 1, $today, $today, $today, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0);";
		 	$q=new mysql();
			$q->QUERY_SQL($sql,$this->database);
	}
	
	private function UpdateAdminPass($username,$password){
		$email=$this->Getemail(1);
		if($email==null){$mail=",email='root@$this->www_servername'";}
		$sql="UPDATE og_users SET username='$username'$mail WHERE ID=1";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		$this->UpdatePassword(1,$password);
	}
	
	private function IsProjectExists($user_id){
		$sql="SELECT personal_project_id FROM og_users WHERE id=$user_id";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if(!$q->ok){writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		$personal_project_id=$ligne["personal_project_id"];
		
		$sql="SELECT id FROM og_projects WHERE id=$personal_project_id";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if(!$q->ok){writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		if($ligne["id"]==null){$ligne["id"]=0;}
		if($ligne["id"]>0){return true;}
		return false;
	}
	
	private function CreatePersonalProject($personal_project_id){
		$sql="INSERT INTO `og_projects` (`id`, `name`, `description`, `show_description_in_overview`, `completed_on`, `completed_by_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `color`, `parent_id`, `p1`, `p2`, `p3`, `p4`, `p5`, `p6`, `p7`, `p8`, `p9`, `p10`) VALUES
		($personal_project_id, 'admin_personal', 'Files', 0, '0000-00-00 00:00:00', 0, NOW(), 1, NOW(), 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0);";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		if(!$q->ok){
			writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
	}

	
    function setPassword($value) {
     	//$salt = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
        $token = sha1("7eb8558726805" . $value);
      	$twister=StringTwister::getTwister();
      	echo "salt:$salt\n";
      	echo "token:$token\n";
      	echo "twister:$twister\n";
      	echo "password:".md5($value)."\n";
      	
     	} 
     	
	private function ENCRYPT_DECRYPT($Str_Message) {
		//Function : encrypt/decrypt a string message v.1.0  without a known key
		//Author   : Aitor Solozabal Merino (spain)
		//Email    : aitor-3@euskalnet.net
		//Date     : 01-04-2005
		$Len_Str_Message=STRLEN($Str_Message);
		$Str_Encrypted_Message="";
		FOR ($Position = 0;$Position<$Len_Str_Message;$Position++){
			// long code of the function to explain the algoritm
			//this function can be tailored by the programmer modifyng the formula
			//to calculate the key to use for every character in the string.
			$Key_To_Use = (($Len_Str_Message+$Position)+1); // (+5 or *3 or ^2)
			//after that we need a module division because can't be greater than 255
			$Key_To_Use = (255+$Key_To_Use) % 255;
			$Byte_To_Be_Encrypted = SUBSTR($Str_Message, $Position, 1);
			$Ascii_Num_Byte_To_Encrypt = ORD($Byte_To_Be_Encrypted);
			$Xored_Byte = $Ascii_Num_Byte_To_Encrypt ^ $Key_To_Use;  //xor operation
			$Encrypted_Byte = CHR($Xored_Byte);
			$Str_Encrypted_Message .= $Encrypted_Byte;

			//short code of the function once explained
			//$str_encrypted_message .= chr((ord(substr($str_message, $position, 1))) ^ ((255+(($len_str_message+$position)+1)) % 255));
		}
		RETURN $Str_Encrypted_Message;
	}      	

     	
     public function DefaultsValues(){
     	$q=new mysql();
     	$sql="INSERT INTO `og_administration_tools` (`id`, `name`, `controller`, `action`, `order`) VALUES (1, 'test_mail_settings', 'administration', 'tool_test_email', 1)";
     	$q->QUERY_SQL($sql,$this->database);
     	$sql="INSERT INTO `og_administration_tools` (`id`, `name`, `controller`, `action`, `order`) VALUES (2, 'mass_mailer', 'administration', 'tool_mass_mailer', 2)";
		$q->QUERY_SQL($sql,$this->database);
     	$sql="INSERT INTO `og_groups` (`id`, `name`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `can_edit_company_data`, `can_manage_security`, `can_manage_workspaces`, `can_manage_configuration`, `can_manage_contacts`, `can_manage_templates`, `can_manage_reports`, `can_manage_time`, `can_add_mail_accounts`) VALUES (10000000, 'administrators', '2010-02-17 23:41:45', 0, '2010-02-17 23:41:45', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1);";
     	$q->QUERY_SQL($sql,$this->database);
     	$sql="INSERT INTO `og_config_categories` (`id`, `name`, `is_system`, `category_order`) VALUES (1, 'system', 1, 0),(2, 'general', 0, 1),(3, 'mailing', 0, 2),(4, 'modules', 0, 3),(5, 'passwords', 0, 4);";
     	$q->QUERY_SQL($sql,$this->database);
     	$sql="INSERT INTO `og_config_options` (`id`, `category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
			(1, 'system', 'project_logs_per_page', '10', 'IntegerConfigHandler', 1, 0, NULL),
			(2, 'system', 'messages_per_page', '5', 'IntegerConfigHandler', 1, 0, NULL),
			(3, 'system', 'max_avatar_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
			(4, 'system', 'max_avatar_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
			(5, 'system', 'logs_per_project', '5', 'IntegerConfigHandler', 1, 0, NULL),
			(6, 'system', 'max_logo_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
			(7, 'system', 'max_logo_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
			(8, 'system', 'files_per_page', '50', 'IntegerConfigHandler', 1, 0, NULL),
			(9, 'general', 'upgrade_last_check_datetime', '2006-09-02 13:46:47', 'DateTimeConfigHandler', 1, 0, 'Date and time of the last upgrade check'),
			(10, 'general', 'upgrade_last_check_new_version', '0', 'BoolConfigHandler', 1, 0, 'True if system checked for the new version and found it. This value is used to hightligh upgrade tab in the administration'),
			(11, 'general', 'file_storage_adapter', 'fs', 'FileStorageConfigHandler', 0, 0, 'What storage adapter should be used? fs or mysql'),
			(12, 'general', 'theme', 'default', 'ThemeConfigHandler', 0, 0, NULL),
			(13, 'general', 'days_on_trash', '30', 'IntegerConfigHandler', 0, 0, 'Days before a file is deleted from trash. 0 = Not deleted'),
			(14, 'mailing', 'exchange_compatible', '0', 'BoolConfigHandler', 0, 0, NULL),
			(15, 'mailing', 'mail_transport', 'mail()', 'MailTransportConfigHandler', 0, 0, 'Values: ''mail()'' - try to emulate mail() function, ''smtp'' - use SMTP connection'),
			(16, 'mailing', 'smtp_server', '', 'StringConfigHandler', 0, 0, ''),
			(17, 'mailing', 'smtp_port', '25', 'IntegerConfigHandler', 0, 0, NULL),
			(18, 'mailing', 'smtp_address', '', 'StringConfigHandler', 0, 0, ''),
			(19, 'mailing', 'smtp_authenticate', '0', 'BoolConfigHandler', 0, 0, 'Use SMTP authentication'),
			(20, 'mailing', 'smtp_username', '', 'StringConfigHandler', 0, 0, NULL),
			(21, 'mailing', 'smtp_password', '', 'PasswordConfigHandler', 0, 0, NULL),
			(22, 'mailing', 'smtp_secure_connection', 'no', 'SecureSmtpConnectionConfigHandler', 0, 0, 'Values: no, ssl, tls'),
			(23, 'mailing', 'user_email_fetch_count', '10', 'IntegerConfigHandler', 0, 0, 'How many emails to fetch when checking for email'),
			(24, 'modules', 'enable_notes_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable notes tab.'),
			(25, 'modules', 'enable_email_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable email tab.'),
			(26, 'modules', 'enable_contacts_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable contacts tab.'),
			(27, 'modules', 'enable_calendar_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable calendar tab.'),
			(28, 'modules', 'enable_documents_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable documents tab.'),
			(29, 'modules', 'enable_tasks_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable tasks tab.'),
			(30, 'modules', 'enable_weblinks_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable weblinks tab.'),
			(31, 'modules', 'enable_time_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable time tab.'),
			(32, 'modules', 'enable_reporting_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable reporting tab.'),
			(33, 'passwords', 'min_password_length', '0', 'IntegerConfigHandler', 0, 1, NULL),
			(34, 'passwords', 'password_numbers', '0', 'IntegerConfigHandler', 0, 2, NULL),
			(35, 'passwords', 'password_uppercase_characters', '0', 'IntegerConfigHandler', 0, 3, NULL),
			(36, 'passwords', 'password_metacharacters', '0', 'IntegerConfigHandler', 0, 4, NULL),
			(37, 'passwords', 'password_expiration', '0', 'IntegerConfigHandler', 0, 5, NULL),
			(38, 'passwords', 'password_expiration_notification', '0', 'IntegerConfigHandler', 0, 6, NULL),
			(39, 'passwords', 'account_block', '0', 'BoolConfigHandler', 0, 7, NULL),
			(40, 'passwords', 'new_password_char_difference', '0', 'BoolConfigHandler', 0, 8, NULL),
			(41, 'passwords', 'validate_password_history', '0', 'BoolConfigHandler', 0, 9, NULL),
			(42, 'general', 'checkout_notification_dialog', '0', 'BoolConfigHandler', 0, 0, NULL),
			(43, 'general', 'file_revision_comments_required', '0', 'BoolConfigHandler', 0, 0, NULL),
			(44, 'general', 'currency_code', '$', 'StringConfigHandler', 0, 0, NULL),
			(45, 'general', 'checkout_for_editing_online', '0', 'BoolConfigHandler', 0, 0, NULL),
			(46, 'general', 'show_feed_links', '0', 'BoolConfigHandler', 0, 0, NULL),
			(47, 'general', 'ask_administration_autentification', '0', 'BoolConfigHandler', 0, 0, NULL);";
     		$q->QUERY_SQL($sql,$this->database);
     		$sql="INSERT INTO `og_cron_events` (`id`, `name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
			(1, 'check_mail', 1, 10, 0, 1, '0000-00-00 00:00:00'),
			(2, 'purge_trash', 1, 1440, 1, 1, '0000-00-00 00:00:00'),
			(3, 'check_upgrade', 1, 1440, 0, 1, '0000-00-00 00:00:00'),
			(4, 'send_reminders', 1, 10, 0, 1, '0000-00-00 00:00:00'),
			(5, 'send_password_expiration_reminders', 1, 1440, 1, 1, '0000-00-00 00:00:00'),
			(6, 'send_notifications_through_cron', 1, 1, 0, 0, '0000-00-00 00:00:00'),
			(7, 'delete_mails_from_server', 1, 1440, 1, 1, '0000-00-00 00:00:00'),
			(8, 'clear_tmp_folder', 1, 1440, 1, 1, '0000-00-00 00:00:00');";
     		$q->QUERY_SQL($sql,$this->database);
     		$sql="INSERT INTO `og_file_types` (`id`, `extension`, `icon`, `is_searchable`, `is_image`) VALUES
					(1, 'zip', 'archive.png', 0, 0),
					(2, 'rar', 'archive.png', 0, 0),
					(3, 'bz', 'archive.png', 0, 0),
					(4, 'bz2', 'archive.png', 0, 0),
					(5, 'gz', 'archive.png', 0, 0),
					(6, 'ace', 'archive.png', 0, 0),
					(7, 'mp3', 'audio.png', 0, 0),
					(8, 'wma', 'audio.png', 0, 0),
					(9, 'ogg', 'audio.png', 0, 0),
					(10, 'doc', 'doc.png', 0, 0),
					(11, 'xls', 'xls.png', 0, 0),
					(12, 'gif', 'image.png', 0, 1),
					(13, 'jpg', 'image.png', 0, 1),
					(14, 'jpeg', 'image.png', 0, 1),
					(15, 'png', 'image.png', 0, 1),
					(16, 'mov', 'mov.png', 0, 0),
					(17, 'pdf', 'pdf.png', 0, 0),
					(18, 'psd', 'psd.png', 0, 0),
					(19, 'rm', 'rm.png', 0, 0),
					(20, 'svg', 'svg.png', 0, 0),
					(21, 'swf', 'swf.png', 0, 0),
					(22, 'avi', 'video.png', 0, 0),
					(23, 'mpeg', 'video.png', 0, 0),
					(24, 'mpg', 'video.png', 0, 0),
					(25, 'qt', 'mov.png', 0, 0),
					(26, 'vob', 'video.png', 0, 0),
					(27, 'txt', 'doc.png', 1, 0),
					(28, 'ppt', 'ppt.png', 0, 0);";
     		$q->QUERY_SQL($sql,$this->database);
     		$sql="INSERT INTO `og_group_users` (`group_id`, `user_id`, `created_on`, `created_by_id`) VALUES (10000000, 1, NOW(), 0);";
     		$q->QUERY_SQL($sql,$this->database);
     		$sql="INSERT INTO `og_gs_fonts` (`FontId`, `FontName`) VALUES
					(1, 'Arial'),
					(2, 'Times New Roman'),
					(3, 'Verdana'),
					(4, 'Courier'),
					(5, 'Lucida Sans Console'),
					(6, 'Tahoma');";
     		$q->QUERY_SQL($sql,$this->database);
     		$sql="INSERT INTO `og_guistate` (`user_id`, `name`, `value`) VALUES
			(1, 'mails-manager', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Achecker%25255Ewidth%25253Dn%2525253A20%255Eo%25253Aid%25253Ds%2525253Adraghandle%25255Ewidth%25253Dn%2525253A18%255Eo%25253Aid%25253Ds%2525253Aicon%25255Ewidth%25253Dn%2525253A28%255Eo%25253Aid%25253Ds%2525253AisRead%25255Ewidth%25253Dn%2525253A16%255Eo%25253Aid%25253Ds%2525253AhasAttachment%25255Ewidth%25253Dn%2525253A24%255Eo%25253Aid%25253Ds%2525253Afrom%25255Ewidth%25253Dn%2525253A280%255Eo%25253Aid%25253Ds%2525253Ato%25255Ewidth%25253Dn%2525253A111%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Asubject%25255Ewidth%25253Dn%2525253A766%255Eo%25253Aid%25253Ds%2525253Aaccount%25255Ewidth%25253Dn%2525253A180%255Eo%25253Aid%25253Ds%2525253Atags%25255Ewidth%25253Dn%2525253A60%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Adate%25255Ewidth%25253Dn%2525253A180%255Eo%25253Aid%25253Ds%2525253Afolder%25255Ewidth%25253Dn%2525253A60%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aactions%25255Ewidth%25253Dn%2525253A180%5Esort%3Do%253Afield%253Ds%25253Adate%255Edirection%253Ds%25253ADESC');";
			$q->QUERY_SQL($sql,$this->database);
			$sql="
				INSERT INTO `og_im_types` (`id`, `name`, `icon`) VALUES
				(1, 'ICQ', 'icq.gif'),
				(2, 'AIM', 'aim.gif'),
				(3, 'MSN', 'msn.gif'),
				(4, 'Yahoo!', 'yahoo.gif'),
				(5, 'Skype', 'skype.gif'),
				(6, 'Jabber', 'jabber.gif');";
			$q->QUERY_SQL($sql,$this->database);
			$sql="INSERT INTO `og_object_reminder_types` (`id`, `name`) VALUES
					(1, 'reminder_email'),
					(2, 'reminder_popup');";
		   $q->QUERY_SQL($sql,$this->database);
		   $sql="INSERT INTO `og_projects` (`id`, `name`, `description`, `show_description_in_overview`, `completed_on`, `completed_by_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `color`, `parent_id`, `p1`, `p2`, `p3`, `p4`, `p5`, `p6`, `p7`, `p8`, `p9`, `p10`) VALUES
(1, 'admin_personal', 'Files', 0, '0000-00-00 00:00:00', 0, NOW(), 1, NOW(), 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0);";
		   $q->QUERY_SQL($sql,$this->database);	
		   $sql="INSERT INTO `og_project_users` (`project_id`, `user_id`, `created_on`, `created_by_id`, `can_read_messages`, `can_write_messages`, `can_read_tasks`, `can_write_tasks`, `can_read_milestones`, `can_write_milestones`, `can_read_files`, `can_write_files`, `can_read_events`, `can_write_events`, `can_read_weblinks`, `can_write_weblinks`, `can_read_mails`, `can_write_mails`, `can_read_contacts`, `can_write_contacts`, `can_read_comments`, `can_write_comments`, `can_assign_to_owners`, `can_assign_to_other`) VALUES
			(1, 1, '2010-02-17 23:41:45', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);";
		   $q->QUERY_SQL($sql,$this->database);	
		   $sql="INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES
				(1, 'general', 0, 0, 0),
				(2, 'dashboard', 0, 0, 1),
				(3, 'task panel', 0, 0, 2),
				(4, 'time panel', 1, 0, 3),
				(5, 'calendar panel', 0, 0, 4),
				(6, 'mails panel', 0, 0, 5),
				(7, 'context help', 1, 0, 5);
				";
		   $q->QUERY_SQL($sql,$this->database);	
     }
     

     
    
	
}


class opengoo_builder{
	var $database='';
	function opengoo_builder($database){
		$this->database=$database;
		$this->check_og_user_ws_config_categories();
		$this->og_user_ws_config_option_values();
	}
	
     private function  check_og_user_ws_config_categories(){
     	$q=new mysql();
 	    $q->QUERY_SQL("INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES (1, 'general', 0, 0, 0);",$this->database);
		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES (2, 'dashboard', 0, 0, 1);",$this->database);
		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES (3, 'task panel', 0, 0, 2);",$this->database);
		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES (4, 'time panel', 1, 0, 3);",$this->database);
		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES (5, 'calendar panel', 0, 0, 4);",$this->database);
		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES (6, 'mails panel', 0, 0, 5);",$this->database);
		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_categories` (`id`, `name`, `is_system`, `type`, `category_order`) VALUES (7, 'context help', 1, 0, 5);",$this->database);
 		}
 		
 	private function  og_user_ws_config_option_values(){
 		$q=new mysql();
 		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_option_values` (`option_id`, `user_id`, `workspace_id`, `value`) VALUES (45, 1, 0, '0');",$this->database);
 		$q->QUERY_SQL("INSERT INTO `og_user_ws_config_option_values` (`option_id`, `user_id`, `workspace_id`, `value`) VALUES (74, 1, 0, '1');",$this->database);
 	    $q->QUERY_SQL("INSERT INTO `og_user_ws_config_option_values` (`option_id`, `user_id`, `workspace_id`, `value`) VALUES (75, 1, 0, 'all');",$this->database);
 	    $q->QUERY_SQL("INSERT INTO `og_user_ws_config_option_values` (`option_id`, `user_id`, `workspace_id`, `value`) VALUES (76, 1, 0, 'all');",$this->database);
 	}

 	
}



  /**
  * String twister is set of functions that modify string based on twister so they are harder to guess
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  final class StringTwister {
  
    /**
    * Return twister ID, 10 digits integer that is user for twisting the string
    *
    * @param void
    * @return integer
    */
    static function getTwister() {
      $twister = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
      do {
        shuffle($twister);
      } while($twister[0] == '0');
      return implode('', $twister);
    } // getTwister
    
    /**
    * Twist $untwisted_string with $twister
    *
    * @param string $untwisted_string
    * @param string $twister
    * @return string
    */
    static function twist($untwisted_string, $twister) {
      if(strlen($untwisted_string) <> strlen($twister)) return $untwisted_string;
      
      $twisted_array = array();
      for($i = 0, $strlen = strlen($twister); $i < $strlen; $i++) {
        $twisted_array[(integer) $twister[$i]] = $untwisted_string[$i];
      } // for
      
      ksort($twisted_array);
      return implode('', $twisted_array);
    } // twist
    
    /**
    * Untwist $twisted_string string based on $twister
    *
    * @param string $twisted_string
    * @param string $twister
    * @return string
    */
    static function untwist($twisted_string, $twister) {
      if(strlen($twisted_string) <> strlen($twister)) return $twisted_string;
      
      $twisted_array = string_to_array($twisted_string);
      $untwisted_array = array();
      for($i = 0, $strlen = strlen($twister); $i < $strlen; $i++) {
        $untwisted_array[] = $twisted_array[(integer) $twister[$i]];
      } // for
      
      return implode('', $untwisted_array);
    } // untwist
    
    /**
    * Twist sha1() hash
    *
    * @param string $untwisted_hash
    * @param string $twister
    * @return string
    */
    static function twistHash($untwisted_hash, $twister) {
      if(!is_valid_hash($untwisted_hash)) return $untwisted_sha1;
      $result = '';
      for($i = 0; $i < 4; $i++) {
        $result .= self::twist(substr($untwisted_hash, $i * 10, 10), $twister);
      } // for
      return $result;
    } // twistHash
    
    /**
    * Untwist $twisted_hash based on $twister
    *
    * @param string $twisted_hash
    * @param string $twister
    * @return string
    */
    static function untwistHash($twisted_hash, $twister) {
      if(!is_valid_hash($twisted_hash)) return $twisted_hash;
      $result = '';
      for($i = 0; $i < 4; $i++) {
        $result .= self::untwist(substr($twisted_hash, $i * 10, 10), $twister);
      } // for
      return $result;
    } // untwistHash
    
  } // StringTwister


/**
 * Contact class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 * INSERT INTO `og_users` (`id`, `company_id`, `personal_project_id`, `username`, `email`, `token`, `salt`, `twister`, `display_name`, `title`, `avatar_file`, `timezone`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `last_login`, `last_visit`, `last_activity`, `can_edit_company_data`, `can_manage_security`, `can_manage_workspaces`, `can_manage_configuration`, `can_manage_contacts`, `can_manage_templates`, `can_manage_reports`, `can_manage_time`, `can_add_mail_accounts`, `auto_assign`, `default_billing_id`) VALUES
(1, 1, 1, 'admin', 'admin@opengoo.touzeau.biz', 'f367882f5635121fb321f17dd626cf4d22bcec2c', '24859b680d444', '8230951746', '', '', '', 1.0, '2010-02-17 23:41:45', 0, '2010-02-17 23:41:45', 1, '2010-02-17 23:41:54', '2010-02-17 23:41:54', '2010-02-17 23:45:57', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0);


INSERT INTO `og_companies` (`id`, `client_of_id`, `name`, `email`, `notes`, `homepage`, `address`, `address2`, `city`, `state`, `zipcode`, `country`, `phone_number`, `fax_number`, `logo_file`, `timezone`, `hide_welcome_info`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`) VALUES
(1, 0, 'opengoo', '', '', '', '', '', '', '', '', '', '', '', '', 0.0, 0, '2010-02-17 23:41:45', 1, '2010-02-17 23:41:45', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

INSERT INTO `og_user_passwords` (`id`, `user_id`, `password`, `password_date`) VALUES
(1, 1, 'VFoAVgIEAwxRCFEEBwFQD1VZCQlVCg==', '2010-02-17 23:41:54');
 */
?>