<?php
if(is_file(dirname(__FILE__) . "/settings.inc")){@include(dirname(__FILE__) . "/settings.inc");}
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc");}


class MailBoxes{
	var $engine;
	var $errors;
	var $enable_storage;
	var $database_type;
	var $global_config;
	var $Cyrus_hash;

	
	
	function MailBoxes(){
		}
		
		
	function MailBoxes_get_infos($email){
		$mailbox=new MailBox($email);
		return $mailbox;
		}
		
		
	function GetHashEntities(){
		$ldap=new clladp();
		return $ldap->hash_get_ou();
		
		
	}

		


	

	

	
	
	function add_domain($domain){
		$domain=new domain($domain);
		$domain->Add();
		
	}
	
	function Hash_get_domains_name(){
		$domain=new domain();
		return $domain->GetDomainList();
		
	}
	
	function Delete_entire_domain($domain){
		$transport=new transport_maps($domain);
		$transport->Delete();
		$alias=new alias();	
		$alias->Delete_all($domain);
		$cldomain=new domain($domain);
		$cldomain->Delete();
	}
	
	function alias_delete($email){
		$alias=new alias($email);
		$alias->Delete();
		
	}
	
	function MailBoxes_get_path($user){
		$mailbox=new MailBox();
		return $mailbox->getPathFromUser($user);
		
	}
	
	function MailBoxes_isExists($user){
		$mailbox=new MailBox();
		return $mailbox->IsExists($user);
	}
	
	function MailBoxes_adduser($user,$password=null,$qota=0){
		writelogs('INFOS:Add/edit  ' . $user . ' mailbox',__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		$mailbox=new MailBox();
		$tbl=explode('@',$user);
		$mailbox->username=$user;
		$mailbox->password=$password;
		$mailbox->domain=$tbl[1];
		if($qota>0){
			$qota=$qota*1000;
		}
		$mailbox->quota=$qota;
		$mailbox->maildir="$mailbox->domain/{$tbl[0]}";
		$mailbox->Add();
		}
	
	function Get_Transport_maps($domain){
		$transport=new transport_maps($domain);
		return $transport->goto;
		
	}
	function delete_transport($domain){
		$transport=new transport_maps($domain);
		$transport->Delete();
		$alias=new alias("@$domain");
		$alias->Delete();
	}
	
	function add_alias($username_from,$username_to,$domain){
		$alias=new alias($username_from);
		$alias->goto=$username_to;
		$alias->domain=$domain;
		$alias->Edit();
		}
		
	function Hash_get_aliases($domain){
		$alias=new alias();
		return $alias->hash_list_from_domain($domain);
		
	}		
		

	
	function Get_Goto_alias($email_from){
		$alias=new alias($email_from);
		return $alias->goto;
		
	}
	
	function AutoPassword(){
		$majuscules = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$minuscules = "abcdefghijklmnopqrstuvwxyz";
		$chiffres = "0123456789";
		$pnum=5;
		$Char = ($majuscules.$minuscules.$chiffres.$symboles);
		if (empty($Char)){$note1 = 1;} else {$note1 = 0;};
		if (empty($pnum) || $pnum <= "0" || $pnum > "20"){$note2 = 1;} else {$note2 = 0;};
		$Charnum = $pnum;
		srand((double)microtime()*1000000);
		for($i=0; $i<$Charnum;$i++){ 
			$Mdp= $Mdp.$Char[rand()%strlen($Char)]; 
		}

    	return $Mdp;
	}
	
	
	function Cyrus_Exists($email){
		$this->Cyrus_load();
		if(!is_array($this->Cyrus_hash)){return false;}
		if($this->Cyrus_hash[$email]["DOMAIN"]<>null){return true;}
		return false;
		
		
	}
	
	function Cyrus_load(){
		$filename=dirname(__FILE__) . '/userdb/mailboxes.txt';
		if(!is_file($filename)){
			writelogs("INFOS:unable to stat $filename",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return null;
		}
	
		$filedatas=file_get_contents($filename);
		$tblA=explode("\n",$filedatas);
		while (list ($num, $ligne) = each ($tblA) ){
			$tblB=explode(';',$ligne);
			$this->Cyrus_hash[$tblB[1]]=array("DOMAIN"=>$tblB[0],"QUOTA"=>$tblB[2]);
			
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
}
?>