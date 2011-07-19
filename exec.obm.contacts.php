<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.obm.inc");
include_once(dirname(__FILE__)."/ressources/class.contacts.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
$param=$argv[1];

if(preg_match("#--user=(.+)#",$param,$re)){
	write_syslog("Checking {$re[1]}",__FILE__);	
	SynCcontact($re[1]);
	die();
}

if(preg_match("#--delete=(.+)#",$param,$re)){
	write_syslog("deleting {$re[1]}",__FILE__);	
	DeleteContact($re[1]);
	die();
}



function SynCcontact($employeeNumber){
	$ct=new contacts(null,$employeeNumber);
	$obmex=new obm_export(1);
	
	if($ct->uidNumber==null){
		write_syslog("Checking $employeeNumber uidNumber is null aborting, failed to import to OBM database",__FILE__);
		die();
	}
	
		if(preg_match("#ou=(.+?),ou=People,#",$ct->dn,$re)){
					$uidMaster=$re[1];
					$contact=new user($uidMaster);
					if(preg_match("#(.+?)@(.+)#",$contact->mail,$re)){
						$DomainId=GetobmDomainId($re[2]);
						write_syslog("From $uidMaster ($contact->mail) DomainId=$DomainId",__FILE__);
					}
		}	
	
	if(!IsUserExists($ct->uidNumber)){
		write_syslog("Checking $employeeNumber ($ct->uidNumber) does not exists",__FILE__);
		addContact($ct,$DomainId);
	}
	
	
	editContact($ct,$DomainId);
	die();
}

	function GetobmDomainId($domain_name){
		$domain_name=trim($domain_name);
		$sql="SELECT domain_id FROM Domain WHERE domain_name='$domain_name'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,'obm2'));
		write_syslog("$domain_name={$ligne["domain_id"]}",__FILE__);
		return $ligne["domain_id"];
		}


function IsUserExists($uidNumber){
		$obmex=new obm_export(1);
		$obmex->database='obm2';
		$res=false;
		$res=$obmex->UserObmExists($uidNumber);
		if($res){
			write_syslog("$uidNumber This user is an OBM contact",__FILE__);
		}
	
			return $res;
	}
	
function editContact($class,$userobm_domain_id){
	
		if($userobm_domain_id==null){
			write_syslog("Checking $class->uidNumber Unable to find userobm_domain_id",__FILE__);
			return false;
		}	
		$date=date('Y-m-d h:I:s');
		$userobm_id=$class->uidNumber;
		$userobm_gid=546;
		$userobm_perms=0;
		$userobm_lastname=$class->sn;
		$userobm_firstname=$class->givenName;
		$userobm_title=$class->title;
		$userobm_address1=$class->postalAddress;
		$userobm_zipcode=$class->postalCode;
		$userobm_expresspostal=$class->postOfficeBox;
		$userobm_phone=$class->telephoneNumber;
		$userobm_mobile=$class->mobile;
		$userobm_email=$class->mail;
		$userobm_town=$class->l;
		$userobm_fax=$class->facsimileTelephoneNumber;
		$userobm_phone2=null;
		$userobm_company=$class->o;
		$userobm_direction=$class->ou;
		$userobm_status='INT';
		$userobm_password_type="PLAIN";
		$userobm_delegation='Delegation';
		$userobm_local=1;

$sql="UPDATE UserObm SET 
				`userobm_domain_id`='$userobm_domain_id',
				`userobm_timeupdate`='$date',
				`userobm_login`=NULL,
				`userobm_password_type`='PLAIN',
				`userobm_password`=NULL,
				`userobm_uid`='$userobm_id',
				`userobm_gid`='$userobm_gid',
				`userobm_lastname`='$userobm_lastname',
				`userobm_firstname`='$userobm_firstname', 
				`userobm_title`='$userobm_title',
				`userobm_email`='$userobm_email',				
				`userobm_phone`='$userobm_phone',
				`userobm_mobile`='$userobm_mobile',				
				`userobm_address1`='$userobm_address1',
				`userobm_zipcode`='$userobm_zipcode',
				`userobm_town`='$userobm_town', 
				`userobm_company`='$userobm_company',
				`userobm_direction`='$userobm_direction',				
				`userobm_fax`='$userobm_fax',
				`userobm_status`='$userobm_status',
				`userobm_expresspostal`='$userobm_expresspostal', 				 
				`userobm_perms`='$userobm_perms'
				 WHERE userobm_id='$userobm_id';";
				$q=new mysql();
				if($q->QUERY_SQL($sql,'obm2')){
					write_syslog("Editing $uidNumber done",__FILE__);
					$obm2=new OBM2();
					$obm2->CleanOBMGhostAccount();					
					return null;		
				}
	
}
	
	
function addContact($class,$userobm_domain_id){
		
		
		
	
		if($userobm_domain_id==null){
			write_syslog("Checking $class->uidNumber Unable to find userobm_domain_id",__FILE__);
			return false;
		}
		
		
		$userobm_id=$class->uidNumber;
		$userobm_gid=546;
		$userobm_perms=0;
		$userobm_lastname=$class->sn;
		$userobm_firstname=$class->givenName;
		$userobm_title=$class->title;
		$userobm_address1=$class->postalAddress;
		$userobm_zipcode=$class->postalCode;
		$userobm_phone=$class->telephoneNumber;
		$userobm_mobile=$class->mobile;
		$userobm_email=$class->mail;
		$userobm_town=$class->l;
		$userobm_fax=$class->facsimileTelephoneNumber;
		$userobm_phone2=null;
		$userobm_company=$class->o;
		$userobm_direction=$class->ou;
		$userobm_status='INT';
		$userobm_password_type="PLAIN";
		$userobm_delegation='Delegation';
		$userobm_local=1;
	
		
		$date=date('Y-m-d h:I:s');
		$sql="INSERT INTO `UserObm` (
			`userobm_id`, 
			`userobm_domain_id`, 
			`userobm_timeupdate`, 
			`userobm_timecreate`,`userobm_status`,userobm_password_type,userobm_delegation,userobm_local)

			VALUES($userobm_id,'$userobm_domain_id','$date','$date','$userobm_status','$userobm_password_type','$userobm_delegation',
			$userobm_local);";
			
			$q=new mysql();
			if(!$q->QUERY_SQL($sql,"obm2")){
				write_syslog("$q->mysql_error",__FILE__);
			}
			
			$obm2=new OBM2();
			$obm2->CleanOBMGhostAccount();
		
	}
	
function DeleteContact($userobm_id){
	
	$sql="DELETE FROM UserObm WHERE userobm_id='$userobm_id'";
	$q=new mysql();
			if(!$q->QUERY_SQL($sql,"obm2")){
				write_syslog("$q->mysql_error",__FILE__);
				die();
			}
	write_syslog("Deleting $userobm_id success",__FILE__);
	$obm2=new OBM2();
	$obm2->CleanOBMGhostAccount();	
	
}




function all(){

$ldap=new clladp();
$ous=$ldap->hash_get_ou();
if(!is_array($ous)){
	write_syslog("No organizations found, abort exporting OBM v2 users",__FILE__);
	die();
}

while (list ($num, $organization) = each ($ous) ){
	SyncOrg($organization);
	
}
}


function SyncOrg($ou){
	
	
	$ldap=new clladp();
	$hash=$ldap->UserSearch($ou,"");
	
	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i]["uid"][0];
		write_syslog("Checking $uid",__FILE__);	
		$obm=new obm_user($uid);
		
		
		
	}
	
	
	
	
	
	
	
}





?>
