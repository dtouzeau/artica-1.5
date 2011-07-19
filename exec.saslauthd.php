<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");


$ldap=new clladp();
$conf[]="ldap_servers: ldap://$ldap->ldap_host:$ldap->ldap_port/";
$conf[]="ldap_version: 3";
$conf[]="ldap_search_base: dc=organizations,$ldap->suffix";
$conf[]="ldap_scope: sub";
$conf[]="ldap_filter: uid=%u";
$conf[]="ldap_auth_method: bind";
$conf[]="ldap_bind_dn: cn=$ldap->ldap_admin,$ldap->suffix";
$conf[]="ldap_password: $ldap->ldap_password";
$conf[]="ldap_timeout: 10";
$conf[]="";
echo "Starting......: saslauthd ldap:/$ldap->ldap_host:$ldap->ldap_port\n";
@file_put_contents(saslauthd_conf(),@implode("\n",$conf));



function saslauthd_conf(){
	if(is_file("/etc/saslauthd.conf")){return "/etc/saslauthd.conf";}
	if(is_file("/usr/local/etc/saslauthd.conf")){return "/usr/local/etc/saslauthd.conf";}
	return "/etc/saslauthd.conf";
}
