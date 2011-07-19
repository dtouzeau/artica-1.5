<?php
if(!is_file("/usr/bin/gnarwl")){die("unable to stat /usr/bin/gnarwl\n");}
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
$ldap=new clladp();
$pattern="(&(mail=*)(vacationEnabled=TRUE))";
$arr=array("uid");
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$arr);
if (!$sr) {die();}
$hash=ldap_get_entries($ldap->ldap_connection,$sr);
for($i=0;$i<$hash["count"];$i++){
	$uid=$hash[$i]["uid"][0];
	$user=new user($uid);
	$user->VacationCheck();
	}
echo("Finish...{$hash["count"]} account(s) active\n");



$pattern="(&(mail=*)(vacationEnabled=FALSE))";
$arr=array("uid");
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$arr);
if (!$sr) {die();}
$hash=ldap_get_entries($ldap->ldap_connection,$sr);
for($i=0;$i<$hash["count"];$i++){
	$uid=$hash[$i]["uid"][0];
	$user=new user($uid);
	$user->VacationCheck();
	}
	
echo("Finish...{$hash["count"]} account(s) inactive\n");	
?>