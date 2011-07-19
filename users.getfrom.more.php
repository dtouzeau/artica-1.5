<?php
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/charts.php');

session_start();
$ldap=new clladp();

if(!isset($_SESSION["uid"])){
	writelogs("uid=" . $_SESSION["uid"] . " come back to logon",__FUNCTION__,__FILE__);
	header('location:logon.php');
	exit;
	}
	
if(isset($_GET["graphmore"])){graphmore();exit;}

page();
function page(){
	$user=new user($_SESSION["uid"]);
	$page=CurrentPageName();
	$mail=$user->mail;
	$users=new usersMenus();
	$graph=InsertChart('js/charts.swf',"js/charts_library","$page?graphmore={$_GET["from"]}&from={$_GET["from"]}",550,480,"",true,$users->ChartLicence);
	
	$html=
	"
	<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function whatsnew(){
if(document.getElementById('leftpanel_content')){
	LoadAjax('leftpanel_content','users.whatsnew.php');
}

}

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	whatsnew();
	}

</script>
<script>demarre();</script>
<script>;setTimeout(\"whatsnew()\",3000);</script>

	
	<H2>{$_GET["from"]}:: mails/{day}</H2>$graph";
	
	
$tpl=new template_users("{$_GET["from"]}",$html,$_SESSION);

echo $tpl->web_page;	
	
}

function graphmore(){
	$month=date('Y-m');
	$users=new user($_SESSION["uid"]);
	
$sql="SELECT COUNT(zDate) as tcount ,rcpt_to,mailfrom,
		DATE_FORMAT(zDate,'%Y-%m-%d') as tday,DATE_FORMAT(zDate,'%Y-%m') as tmonth FROM mails_events group by DATE_FORMAT(zDate,'%Y-%m-%d'),mailfrom	
		HAVING rcpt_to='$users->mail' AND mailfrom='{$_GET["from"]}' AND tmonth='$month' ORDER BY tday;";
		


$textes[]='title';
$donnees[]='nb mails/day';
	$s=new mysql();
	$results=$s->QUERY_SQL($sql,"artica_events");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$textes[]=$ligne["tday"];
		$donnees[]=$ligne["tcount"];
		}	
	
	//$links=array("url"=>"javascript:MyHref('system_statistics.php?LinesMessagesHour=yes',_category_)","target"=>"javascript");
	include_once('listener.graphs.php');
	BuildGraphCourbe(array($textes,$donnees),$links);

	
}
?>