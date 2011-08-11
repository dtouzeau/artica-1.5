<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["AS_ROOT"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.lvm.org.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.groups.inc');
include_once(dirname(__FILE__).'/ressources/class.mount.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=="--build"){build();exit;}

function build(){
	$sock=new sockets();	
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){$EnableSambaActiveDirectory=0;}
	if($EnableSambaActiveDirectory==0){echo "Starting......: pam.d, ActiveDirectory is disabled\n";}else{echo "Starting......: pam.d, ActiveDirectory is Enabled\n";}
		
	$f[]="@include common-auth";
	$f[]="@include common-account";
	$f[]="@include common-session";
	
	@file_put_contents("/etc/pam.d/samba", @implode("\n", $f));
	echo "Starting......: pam.d, \"/etc/pam.d/samba\" done\n";
	unset($f);


	
	if(is_file("/etc/pam.d/common-account")){
		 $f[]="#";
		 $f[]="# /etc/pam.d/common-account - authorization settings common to all services";
		 $f[]="#";
		 $f[]="# This file is included from other service-specific PAM config files,";
		 $f[]="# and should contain a list of the authorization modules that define";
		 $f[]="# the central access policy for use on the system.  The default is to";
		 $f[]="# only deny service to users whose accounts are expired in /etc/shadow.";
		 $f[]="#";
		 if($EnableSambaActiveDirectory==1){ $f[]="account sufficient       pam_winbind.so";} 
		 $f[]="account sufficient pam_ldap.so";
		 $f[]="account required   pam_unix.so try_first_pass";
		 $f[]="";
		 @file_put_contents("/etc/pam.d/common-account", @implode("\n", $f)); 
		 echo "Starting......: pam.d, \"/etc/pam.d/common-account\" done\n";
		 unset($f);
		}
 
	 if(is_file("/etc/pam.d/common-auth")){
		 $f[]="#";
		 $f[]="# /etc/pam.d/common-auth - authentication settings common to all services";
		 $f[]="#";
		 $f[]="# This file is included from other service-specific PAM config files,";
		 $f[]="# and should contain a list of the authentication modules that define";
		 $f[]="# the central authentication scheme for use on the system";
		 $f[]="# (e.g., /etc/shadow, LDAP, Kerberos, etc.).  The default is to use the";
		 $f[]="# traditional Unix authentication mechanisms.";
		 $f[]="#";
		 if($EnableSambaActiveDirectory==1){ $f[]="auth sufficient pam_winbind.so";} 
		 $f[]="auth sufficient pam_ldap.so";
		 $f[]="auth	requisite	pam_unix.so nullok_secure try_first_pass";
		 $f[]="auth	optional	pam_smbpass.so migrate";
		 $f[]="";
		 @file_put_contents("/etc/pam.d/common-auth", @implode("\n", $f)); 
		 echo "Starting......: pam.d, \"/etc/pam.d/common-auth\" done\n";
		 unset($f); 
	 }
 
 
	 $f[]="#%PAM-1.0";
	 $f[]="";
	 $f[]="#@include common-auth";
	 $f[]="#@include common-account";
	 $f[]="auth    sufficient      pam_unix.so ";
	 $f[]="auth    required        pam_unix.so";
	 $f[]="session required pam_permit.so";
	 $f[]="session required pam_limits.so";
	 $f[]="";
	 @file_put_contents("/etc/pam.d/sudo", @implode("\n", $f)); 
	 echo "Starting......: pam.d, \"/etc/pam.d/sudo\" done\n";
	 unset($f); 


	if(is_file("/etc/pam.d/common-password")){
		 $f[]="#";
		 $f[]="# /etc/pam.d/common-password - password-related modules common to all services";
		 if($EnableSambaActiveDirectory==1){ $f[]="password        [success=1 default=ignore]      pam_winbind.so use_authtok try_first_pass";}
		 $f[]="password   sufficient  pam_ldap.so";
		 $f[]="password   requisite   pam_unix.so nullok obscure md5 try_first_pass";
		 $f[]="";
		 $f[]="# Alternate strength checking for password. Note that this";
		 $f[]="# requires the libpam-cracklib package to be installed.";
		 $f[]="# You will need to comment out the password line above and";
		 $f[]="# uncomment the next two in order to use this.";
		 $f[]="# (Replaces the `OBSCURE_CHECKS_ENAB'', `CRACKLIB_DICTPATH'')";
		 $f[]="#";
		 $f[]="# password required	  pam_cracklib.so retry=3 minlen=6 difok=3";
		 $f[]="# password required	  pam_unix.so use_authtok nullok md5 try_first_pass";
		 $f[]="";
		 $f[]="# minimally-intrusive inclusion of smbpass in the stack for";
		 $f[]="# synchronization.  If the module is absent or the passwords don''t";
		 $f[]="# match, this module will be ignored without prompting; and if the ";
		 $f[]="# passwords do match, the NTLM hash for the user will be updated";
		 $f[]="# automatically.";
		 $f[]="password   optional   pam_smbpass.so nullok use_authtok use_first_pass";
		 $f[]="";
		 @file_put_contents("/etc/pam.d/common-password", @implode("\n", $f)); 
		 echo "Starting......: pam.d, \"/etc/pam.d/common-password\" done\n";
		 unset($f); 
	}

	if(is_file("/etc/pam.d/common-session")){
		$f[]="# here are the per-package modules (the \"Primary\" block)";
		$f[]="session	[default=1]			pam_permit.so";
		$f[]="# here's the fallback if no module succeeds";
		$f[]="session	requisite			pam_deny.so";
		$f[]="# prime the stack with a positive return value if there isn't one already;";
		$f[]="# this avoids us returning an error just because nothing sets a success code";
		$f[]="# since the modules above will each just jump around";
		$f[]="session	required			pam_permit.so";
		$f[]="# and here are more per-package modules (the \"Additional\" block)";
		$f[]="session	optional			pam_krb5.so minimum_uid=1000";
		$f[]="session	required			pam_unix.so ";
		$f[]="session	optional			pam_winbind.so ";
		$f[]="session	optional			pam_ldap.so ";
		if(ifispam_mkhomedir()){
			$f[]="session	required			pam_mkhomedir.so skel=/etc/skel/ umask=0022";
		}
		$f[]="# end of pam-auth-update config";
		$f[]="";
		 @file_put_contents("/etc/pam.d/common-session", @implode("\n", $f)); 
		 echo "Starting......: pam.d, \"/etc/pam.d/common-session\" done\n";
		 unset($f); 
	}	
	
	if(is_file("/etc/pam.d/system-auth-ac")){
		$f[]="#%PAM-1.0";
		$f[]="# This file is auto-generated.";
		$f[]="# User changes will be destroyed the next time authconfig is run.";
		$f[]="auth        required      pam_env.so";
		$f[]="auth        sufficient    pam_unix.so nullok try_first_pass";
		$f[]="auth        requisite     pam_succeed_if.so uid >= 500 quiet";
		$f[]="auth        sufficient    pam_ldap.so use_first_pass";
		if($EnableSambaActiveDirectory==1){ $f[]="auth        sufficient    pam_winbind.so use_first_pass";}
		$f[]="auth        required      pam_deny.so";
		$f[]="";
		$f[]="account     required      pam_unix.so";
		$f[]="account     sufficient    pam_succeed_if.so uid < 500 quiet";
		$f[]="account     sufficient    pam_ldap.so use_first_pass";
		if($EnableSambaActiveDirectory==1){ $f[]="account     sufficient    pam_winbind.so use_first_pass";}
		$f[]="account     required      pam_permit.so";
		$f[]="";
		$f[]="password    requisite     pam_cracklib.so try_first_pass retry=3";
		$f[]="password    sufficient    pam_unix.so md5 shadow nullok try_first_pass use_authtok";
		$f[]="password    sufficient    pam_ldap.so use_first_pass";
		if($EnableSambaActiveDirectory==1){ $f[]="password    sufficient    pam_winbind.so use_first_pass";}
		$f[]="password    required      pam_deny.so";
		$f[]="";
		$f[]="session     optional      pam_keyinit.so revoke";
		$f[]="session     required      pam_limits.so";
		$f[]="session     [success=1 default=ignore] pam_succeed_if.so service in crond quiet use_uid";
		$f[]="session     optional      pam_ldap.so use_first_pass";
		if($EnableSambaActiveDirectory==1){ $f[]="session     optional      pam_winbind.so use_first_pass";}
		if(ifispam_mkhomedir()){ $f[]="session     required      pam_mkhomedir.so skel=/etc/skel/ umask=0022";}
		$f[]="session     required      pam_unix.so";
		$f[]="";	
		 @file_put_contents("/etc/pam.d/system-auth-ac", @implode("\n", $f)); 
		 echo "Starting......: pam.d, \"/etc/pam.d/system-auth-ac\" done\n";
		 unset($f); 	
	}

}
function ifispam_mkhomedir(){
	if(is_file("/lib/x86_64-linux-gnu/security/pam_mkhomedir.so")){return true;}
	if(is_file("/lib/security/pam_mkhomedir.so")){return true;}
	if(is_file("/lib/i386-linux-gnu/security/pam_mkhomedir.so")){return true;}
	echo "Starting......: pam.d, pam_mkhomedir.so no such file\n";
	return false;
	
}