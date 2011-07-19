<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.contacts.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}

if($argv[1]="--export-zarafa"){export_to_zarafa($argv[2]);}




function export_to_zarafa($uid){
	$f[]="First Name,Middle Name,Last Name,Title,Suffix,Initials,Web Page,Gender,Birthday,Anniversary,Location,Language,Internet Free Busy,Notes,E-mail Address,E-mail 2 Address,E-mail 3 Address,Primary Phone,Home Phone,Home Phone 2,Mobile Phone,Pager,Home Fax,Home Address,Home Street,Home Street 2,Home Street 3,Home Address PO Box,Home City,Home State,Home Postal Code,Home Country,Spouse,Children,Manager's Name,Assistant's Name,Referred By,Company Main Phone,Business Phone,Business Phone 2,Business Fax,Assistant's Phone,Company,Job Title,Department,Office Location,Organizational ID Number,Profession,Account,Business Address,Business Street,Business Street 2,Business Street 3,Business Address PO Box,Business City,Business State,Business Postal Code,Business Country,Other Phone,Other Fax,Other Address,Other Street,Other Street 2,Other Street 3,Other Address PO Box,Other City,Other State,Other Postal Code,Other Country,Callback,Car Phone,ISDN,Radio Phone,TTY/TDD Phone,Telex,User 1,User 2,User 3,User 4,Keywords,Mileage,Hobby,Billing Information,Directory Server,Sensitivity,Priority,Private,Categories";
	$ldap=new clladp();
	$ct=new user($uid);
	$dn="ou=$uid,ou=People,dc=$ct->ou,dc=NAB,$ldap->suffix";
	$filter="(objectClass=inetOrgPerson)";
	$attrs=array();
	$hash=$ldap->Ldap_search($dn,$filter,array("employeeNumber"));
	if($GLOBALS["VERBOSE"]){echo "[$uid]: Exporting {$hash["count"]} user(s)\n";}
	for($i=0;$i<$hash["count"];$i++){
		$emp=new contacts(null,$hash[$i]["employeenumber"][0]);
		$f[]=@implode(",",$emp->ContactTocsvArray());
		
	}
	
	$tmpfile="/tmp/$uid.".time().".csv";
	@file_put_contents("$tmpfile", @implode("\n", $f));
	
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$basename=basename($tmpfile);
	$cmd=$php ." ".dirname(__FILE__)."/exec.zarafa.csv2contacts.php $uid \"$ct->password\" $basename 2>&1";
	if($GLOBALS["VERBOSE"]){echo "[$uid]: $cmd\n";}
	exec($cmd,$results);
	if($GLOBALS["VERBOSE"]){while (list ($num, $line) = each ($results) ){echo "[$uid]: $line\n";}}
	
}


