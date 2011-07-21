<?php
$GLOBALS["VERBOSE"]=false;
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
session_start ();
include_once ('ressources/class.templates.inc');
include_once ('ressources/class.ldap.inc');
include_once ('ressources/class.users.menus.inc');
include_once ('ressources/class.artica.inc');
include_once ('ressources/class.user.inc');
include_once ('ressources/class.computers.inc');
include_once ('ressources/class.ini.inc');


$usersprivs = new usersMenus ( );
$change_aliases = GetRights_aliases();

if ($change_aliases == 0) {
	echo "alert('".$tpl->_ENGINE_parse_body ( "{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}" )."');";
	return;
}
if(isset($_POST["NewHostname"])){changecomputername();exit;}
if(isset($_GET["show-config"])){showConfig();exit;}


function showConfig(){
	echo MEMBER_JS($_GET["userid"],1,1);
	
}

js();

function js(){
	
	$comp=new computers($_GET["userid"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$text=$tpl->javascript_parse_text("$comp->uid:{change_computer_text}");
	$t=time();
	$html="
	var x_$t='';
	
var x_ChangeComputerName= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);return;}
	if(document.getElementById('computerlist')){BrowsComputersRefresh();}
	if(document.getElementById('YahooUser')){YahooUserHide();}
	Loadjs('$page?show-config=yes&userid='+x_$t);
	
	}		
	
	
	
	function ChangeComputerName(){
		var newhostname=prompt('$text');	
		if(!newhostname){return;}
		var XHR = new XHRConnection();
		x_$t=newhostname+'$';
		XHR.appendData('NewHostname',newhostname);
		XHR.appendData('userid','{$_GET["userid"]}');
		XHR.sendAndLoad('$page', 'POST',x_ChangeComputerName);	
		
	}	
	
	ChangeComputerName();
	";
	
	echo $html;
}


function changecomputername(){
	$comp=new computers($_POST["userid"]);
	$_POST["NewHostname"]=trim(strtolower($_POST["NewHostname"]));
	$_POST["NewHostname"]=str_replace('$', '', $_POST["NewHostname"]);
	$actualdn=$comp->dn;
	$newrdn="cn={$_POST["NewHostname"]}$";
	$ldap=new clladp();
	if(!preg_match("#^cn=(.+?),[a-zA-Z\s]+#" ,$actualdn,$re)){echo "Unable to preg_match $actualdn\n";return;}
	
	$newDN=str_replace($re[1], $_POST["NewHostname"].'$', $actualdn);

	
	if($newDN==null){
		echo "Unable to preg_match $actualdn -> {$re[1]}\n";return;
	}
	
	if(!$ldap->Ldap_rename_dn($newrdn,$actualdn,null)){
		echo "Rename failed $ldap->ldap_last_error\nFunction:".__FUNCTION__."\nFile:".__FILE__."\nLine".__LINE__."\nExpected DN:$newDN";
		return;
	}	
	
	
	$upd["uid"][0]=$_POST["NewHostname"].'$';
	if(!$ldap->Ldap_modify($newDN, $upd)){
		echo "Update UID {$upd["uid"][0]} failed:\n$ldap->ldap_last_error\nFunction:".__FUNCTION__."\nFile:".__FILE__."\nLine".__LINE__."\nExpected DN:$newDN\nExpected value:{$_POST["NewHostname"]}";
	}
	
	
	
}
