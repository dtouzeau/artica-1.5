<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.contacts.inc");
include_once(dirname(__FILE__)."/ressources/class.zarafa.contacts.inc");
include_once(dirname(__FILE__)."/ressources/class.sockets.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');


include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
define("SERVER", "file:///var/run/zarafa");

	$sock=new sockets();
	$ZarafaImportContactsInLDAPEnable=$sock->GET_INFO("ZarafaImportContactsInLDAPEnable");
	if(!is_numeric($ZarafaImportContactsInLDAPEnable)){$ZarafaImportContactsInLDAPEnable=0;}
	if($ZarafaImportContactsInLDAPEnable==0){if($GLOBALS["VERBOSE"]){echo "ZarafaImportContactsInLDAPEnable = $ZarafaImportContactsInLDAPEnable, aborting\n";die();}}
	

if($argv[1]=="--all"){ParseAllcontacts();die();}
ParseContacts($argv[1]);


function ParseAllcontacts(){

	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pidtime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){return;}
	@file_put_contents($pidfile, getmypid());
	if($unix->file_time_min($pidtime)<120){return;}
	@file_put_contents($pidtime, time());
	$ldap=new clladp();
	$suffix=$ldap->suffix;	
	$arr=array("uid");
	$sr = @ldap_search($ldap->ldap_connection,"dc=organizations,$suffix",'(objectclass=userAccount)',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){
				ParseContacts($hash[$i]["uid"][0]);
				if(system_is_overloaded(dirname(__FILE__))){$unix->send_email_events(basename(__FILE__)." Overloaded aborting task", "Zarafa contacts importation has been canceled due to overloaded system", "mailbox");return;}					
				sleep(1);
			}
		}
}


function ParseContacts($uid){
	if($uid=="--all"){ParseAllcontacts();return;}
	$ct=new user($uid);	
	$f=new zarafa_contacts();
	$f->connect(null, $ct->uid, $ct->password);
	$contacts=$f->ParseContacts();

	while (list ($num, $contact) = each ($contacts) ){
		
		if($contact["email1"]==null){continue;}
		$_contact=new contacts($ct->uid);
		$employeeNumber=$_contact->employeeNumberByEmail($contact["email1"]);
		if($employeeNumber<>null){$_contact=new contacts($ct->uid,$employeeNumber);if($_contact->modifyTimestamp==$contact["last_modification_time"]){continue;}	}
		
		
		$_contact->displayName=$contact["display_name"];
		$_contact->fileAs=$contact["fileas"];
		$_contact->givenName=$contact["given_name"]; // First Name=$contact["gender"];
		$_contact->telephoneNumber=$contact["office_telephone_number"];
		$_contact->homePhone=$contact["home_telephone_number"];
		$_contact->homePostalAddress=$contact["home_address_street"].", {$contact["home_address_postal_code"]} {$contact["home_address_city"]} {$contact["home_address_state"]}  {$contact["home_address_country"]}";
		$_contact->street=$contact["business_street"];
		//$_contact->postOfficeBox=$contact["gender"];//Professional address
		$_contact->postalCode=$contact["business_postcode"];
		$_contact->postalAddress=$contact["business_street"];
		$_contact->l=$contact["business_city"];   //	Business city
		$_contact->st=$contact["business_state"];  //	Business state/province
		$_contact->c=$contact["business_country"];  // 	Country
		$_contact->mail=$contact["email1"];
		$_contact->mobile=$contact["cellular_telephone_number"];
		$_contact->labeledURI=$contact["business_home_page"]; // personal URL
		$_contact->modifyTimestamp=$contact["last_modification_time"]; //when entry was modified
		$_contact->department=$contact["department_name"];
		$_contact->o=$contact["company_name"];
		$_contact->note=$contact["notes"];
		if(isset($contact["email2"])){$_contact->mozillaSecondEmail[]=$contact["email2"];}
		if(isset($contact["email3"])){$_contact->mozillaSecondEmail[]=$contact["email3"];}
		$_contact->mozillaNickname=$contact["nickname"];
		$_contact->facsimileTelephoneNumber=$contact["primary_fax_number"];
		$_contact->Fax=$contact["primary_fax_number"];
		$_contact->nsMSNid=$contact["IM1"];
		$_contact->businessRole=$contact["profession"];
		$_contact->managerName=$contact["manager_name"];
		$_contact->assistantName=$contact["assistant"];
		if(isset($contact["birthday"])){$_contact->birthDate=date("Y-m-d",$contact["birthday"]);}
		$_contact->spouseName=$contact["spouse_name"];
		if(isset($contact["wedding_anniversary"])){$_contact->anniversary=date("Y-m-d",$contact["wedding_anniversary"]);}
		$_contact->modifyTimestamp=$contact["last_modification_time"];
		
		$_contact->title=$contact["title"]; //Working title, as opposed to personell title. e.g. "Project leader", etc. 
		$_contact->sn=$contact["surname"]; // Last Name=$contact["gender"];
		$_contact->NoExport=true;
		$_contact->Save();
		sleep(1);
		
		
		
	}
}
	
	/*

            [subject] => Touzeau, Daniel
            


            [business_fax_number] => 
            [company_name] => 
            [title] => 
            [department_name] => 
            [office_location] => 
            [nickname] => 
            [display_name_prefix] => M.
            [generation] => 
            [sensitivity] => 0
            [last_modification_time] => 1309563104
            [assistant_telephone_number] => 
            [business2_telephone_number] => 
            [callback_telephone_number] => 
            [car_telephone_number] => 
            [company_telephone_number] => 
            [home2_telephone_number] => 
            [home_fax_number] => 
            [other_telephone_number] => 
            [pager_telephone_number] => 
            [primary_fax_number] => 
            [primary_telephone_number] => 
            [radio_telephone_number] => 
            [telex_telephone_number] => 
            [ttytdd_telephone_number] => 
            [home_address_street] => 
            [home_address_city] => 
            [home_address_state] => 
            [home_address_postal_code] => 
            [home_address_country] => 
            [other_address_street] => 
            [other_address_city] => 
            [other_address_state] => 
            [other_address_postal_code] => 
            [other_address_country] => 
            [notes] => /*
	 */

