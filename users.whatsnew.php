<?php
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
session_start();
$ldap=new clladp();

if(!isset($_SESSION["uid"])){
	writelogs("uid=" . $_SESSION["uid"] . " come back to logon",__FUNCTION__,__FILE__);
	header('location:logon.php');
	exit;
	}


$user=new user($_SESSION["uid"]);
$mail=$user->mail;


$sql="SELECT mailfrom,rcpt_to,zDate,mailfrom_domain FROM mails_events WHERE rcpt_to='$mail' AND zDate<=NOW() ORDER BY zDate DESC LIMIT 0,10";
$q=new mysql();

$results=$q->QUERY_SQL($sql," artica_events");
		$html="<span class='smalltitle'>News</span><br /><br />";
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$timestamp=strtotime($ligne["zDate"]);
			if(date('Y-m-d')==date('Y-m-d',$timestamp)){
				$day='{today}';
			}else{
				$day=date('l',$timestamp);
			}
			
			$time=date('H:i:s',$timestamp);
			
			$html=$html . "<span class='smallredtext'>$x $day $time</span><br />
					<span class='bodytext'>{new_mail_receive_from} {$ligne["mailfrom"]}</span><br />
					<a href='users.getfrom.more.php?from={$ligne["mailfrom"]}' class='smallgraytext'>{more}...</a><br /><br>";
			
			
		}

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

?>