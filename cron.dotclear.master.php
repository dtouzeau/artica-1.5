<?php
include_once(dirname(__FILE__).'/ressources/class.dotclear.inc');
include(dirname(__FILE__) . '/ressources/settings.inc');
if(!$_GLOBAL["mysql_enabled"]){die("Mysql features is not enabled");}

		include(dirname(__FILE__) . "/ressources/settings.inc");
		$ldap_admin=$_GLOBAL["ldap_admin"];
		$ldap_password=$_GLOBAL["ldap_password"];


$bd=mysqlOpen();
AddManager($ldap_admin,$ldap_password,$bd);
mysql_close($bd);

	function AddManager($username,$password,$bd){
		include(dirname(__FILE__) . "/ressources/settings.inc");
		
		
		$password=crypt::hmac('artica',$password);
		$date=date('Y-m-d H:i:s');
		$sql="SELECT user_pwd FROM dotclear_user WHERE user_id='$uid'";
		$ligne=@mysql_fetch_array(zQUERY_SQL($bd,$sql));
		if($ligne["user_pwd"]==null){
			
			$sql="INSERT INTO `dotclear_user` (`user_id`, `user_super`, `user_status`, `user_pwd`, `user_recover_key`,
 				`user_name`, `user_firstname`, `user_displayname`, `user_email`, `user_url`,
  				`user_desc`, `user_default_blog`, `user_options`, `user_lang`, `user_tz`,
   				`user_post_status`, `user_creadt`,
    			`user_upddt`) VALUES
				('$username', 1, 1, '$password', NULL, '$username', '$username', NULL, 'root@localhost.localdomain', 
				NULL, NULL, NULL, 'a:3:{s:9:\"edit_size\";i:24;s:14:\"enable_wysiwyg\";b:1;s:11:\"post_format\";s:4:\"wiki\";}', 'en',
				 'Europe/Berlin', -2, '$date', '$date');";
			zQUERY_SQL($bd,$sql);
			
		}else{
			
			$sql="UPDATE `artica_backup`.`dotclear_user` SET `user_pwd` = '$password' WHERE `dotclear_user`.`user_id` = '$username' LIMIT 1 ;";
			zQUERY_SQL($bd,$sql);
			
		}
		
	}
	
	
function mysqlOpen(){
		include(dirname(__FILE__) . '/ressources/settings.inc');
		$mysql_server=$_GLOBAL["mysql_server"];
		$mysql_admin=$_GLOBAL["mysql_admin"];
		$mysql_password=$_GLOBAL["mysql_password"];
		$mysql_port=$_GLOBAL["mysql_port"];	
		$bd=@mysql_connect("$mysql_server:$mysql_port",$mysql_admin,$mysql_password);
		if(!$bd){
    		$des=mysql_error();
    		die("Unable to connect to mysql server $des");
		}
		
		return $bd;	
	
	
}	

function zQUERY_SQL($bd,$sql){
		$database="artica_backup";
		$ok=@mysql_select_db($database,$bd);
    	if (!$ok){
    		$errnum=mysql_error();
    		$des=mysql_error();
    		die("Error Number ($errnum) ($des) ".__FUNCTION__.'/'.__FILE__);
    		return null;
    
    	}	

    $results=mysql_query($sql,$bd);
	
		if(mysql_error()){
				$errnum=mysql_error();
				$des=mysql_error();
				
		}	
	return $results;
}



?>