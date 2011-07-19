<?php
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.backup.emails.inc');
session_start();
$ldap=new clladp();

if(!isset($_SESSION["uid"])){
	writelogs("uid=" . $_SESSION["uid"] . " come back to logon",__FUNCTION__,__FILE__);
	header('location:logon.php');
	exit;
	}


	
$backup=new backup_query($_SESSION["uid"]);	

		$html="<span class='smalltitle'>News</span><br /><br />";
		$results=$backup->sql_last_backuped_mails(0,10);
		
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$timestamp=strtotime($ligne["zDate"]);
			if(date('Y-m-d')==date('Y-m-d',$timestamp)){
				$day='{today}';
			}else{
				$day=date('l',$timestamp);
			}
			
			$time=date('H:i:s',$timestamp);
			
			$subject=$ligne["subject"];
			
			$html=$html . "<span class='smallredtext'>$x $day $time </span><br />
					<span class='bodytext'><i>{$ligne["mailfrom"]}</i><br>$subject</span><br />
					<a href='#' OnClick=\"javascript:ShowBackupMail('{$ligne["MessageID"]}')\" class='smallgraytext'>{more}...</a><br /><br>";
			
			
		}

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

?>