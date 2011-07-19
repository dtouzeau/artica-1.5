<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');



if(!isset($_SESSION["uid"])){die();}
		if($_GET["ou"]==null){if($_COOKIE["SwitchOrgTabsOu"]<>null){$_GET["ou"]=$_COOKIE["SwitchOrgTabsOu"];}}
		
		$user=new usersMenus();
		
		if($_GET["ou"]==null){
			if($user->AsArticaAdministrator){
				$ldap=new clladp();
				$hash=$ldap->hash_get_ou(false);
				$_GET["ou"]=$hash[0];
			}else{
				$_GET["ou"]=$hash[0]=ORGANISTATION_FROM_USER();
			}
			
		}
		
		
if($_GET["ou"]<>null){
	header("location:domains.manage.org.index.php?ou={$_GET["ou"]}");
}else{
	header("location:domains.index.php");
}
		
		
function ORGANISTATION_FROM_USER(){
	$ldap=new clladp();
	$hash=$ldap->Hash_Get_ou_from_users($_SESSION["uid"],1);
	if(is_array($hash)){return $hash[0];}
	}			
?>	