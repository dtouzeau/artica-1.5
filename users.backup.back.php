<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.backup.emails.inc');

if(isset($_GET["direction"])){query();exit;}
if(isset($_GET["ShowBackupMail"])){ShowBackupMail();exit;}
if(isset($_GET["msgbodyif"])){ShowBackupMailInside();exit;}

main_page();

function main_page(){
	
	$from=array(
	"TO"=>"{q_to}",
	"FROM"=>"{q_from}",
	""=>"{dont_know}"

	);
	
	$dir=Field_array_Hash($from,'direction');
	$b=new backup_email($_SESSION["ou"]);
	$messagenumber=$b->MessageNumber();
	$html="
	<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function whatsnew(){
if(document.getElementById('leftpanel_content')){
	LoadAjax('leftpanel_content','users.whatsnew-backup.php');
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
	
	<H2>Query Manager ($messagenumber emails)</H2>
	<code>{backup_text}</code>
	<input type='hidden' id='ou' value='{$_SESSION["ou"]}'>
	
	" . RoundedLightGrey("
	<table style='width:100%'>
	
	<tr>
		<td nowrap align='right'><strong>{search_messages_that}:</strong></td>
		<td>$dir</td>
	</tr>
	<tr>
		<td nowrap align='right'><strong>{with_email}:</strong></td>
		<td>" . Field_text('email')."</td>
	</tr>
	<tr>
		<td nowrap align='right'><strong>{with_subject}:</strong></td>
		<td>" . Field_text('subject')."</td>
	</tr>	
	<tr>
		<td nowrap align='right'><strong>{with_body}:</strong></td>
		<td>" . Field_text('body')."</td>
	</tr>	
	<tr>
	<tr>
	<td colspan=2 align='right'>
		<input type='button' OnClick=\"javascript:findBackuphtml();\" value='{search}&nbsp;&raquo;'>
	</td>
	</tr>	
	</table>")."
	
	<div id='query_list'></div>
	
	
	
	";
	
$cfg["JS"][]="js/users.backup.js";
$tpl=new template_users("{backup}",$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
	
}

function query(){
	
	$direction=$_GET["direction"];
	$uid=$_SESSION["uid"];
	$email=$_GET["email"];
	$subject=$_GET["subject"];
	$body=$_GET["body"];
	
	writelogs("found direction=$direction,uid=$uid,email=$email,subject=$subject",__FUNCTION__,__FILE__);
	
	$ldap=new clladp();
	$user=new user($uid);
	
	$back=new backup_query($uid);
	$mymails=$back->sql_mymails;
	
if($body<>null){
	$bodyf=" ,MATCH (`storage`.MessageBody) AGAINST ('$body') AS bod";
	$body=" AND MATCH (`storage`.MessageBody) AGAINST ('$body')>0";
	$order=" bod DESC,";
	
}	
	
	$sql_start="SELECT `storage`.MessageID,`storage`.zDate,`storage`.subject$bodyf FROM `storage`,storage_recipients WHERE 
		`storage`.MessageID=storage_recipients.MessageID ";
	


//$sq_direction="OR (`storage`.mailfrom='{$user->mail}')";

if($email<>null){
	
	
	
	$email_sql=" AND `storage`.mailfrom LIKE '%$email%'";
	if($direction==null){
		$sq_direction="AND (`storage`.mailfrom LIKE '%$email%')";
	}
	
	
	
}

if($subject<>null){$subject=" AND (`storage`.subject LIKE '%$subject%')";}



switch ($direction) {
	case null:
		$sql="$sql_start AND ($mymails) $sq_direction $subject $body";
		break;
	case "TO":
		$sql="$sql_start AND ($mymails) $email_sql $subject $body";
		break;
	case "FROM":
		$email_sql=" AND (`storage_recipients`.recipient LIKE '%$email%')";
		$sql="$sql_start AND (`storage`.mailfrom='{$user->mail}') $email_sql $subject $body";
		break;
}



$sql=$sql ." ORDER BY $order`storage`.zDate DESC LIMIT 0,50";
writelogs($sql,__FUNCTION__,__FILE__);
$s=new mysql();
$results=$s->QUERY_SQL($sql,"artica_backup");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$arr[$ligne["MessageID"]]=array($ligne["zDate"],$ligne["subject"]);
	}
	
if(!is_array($arr)){echo "None...<br><br><code>$sql</code>";exit;}

$html="<table style='width:100%'>
<tr>
	<th>{date}</th>
	<th>{subject}</th>
</tr>

";
while (list ($num, $array) = each ($arr) ){
	$html=$html ."
	<tr " . CellRollOver("ShowBackupMail('$num')")." >
	<td nowrap valign='top' style='border-bottom:1px dotted #CCCCCC;padding:3px'>{$array[0]}</td>
	<td style='border-bottom:1px dotted #CCCCCC;padding:3px'>{$array[1]}</td>
	</tr>
	
	";
}

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html . "</table><br><br><code>$sql</code>");
		
	
}


function main_mailtabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]="ShowMail";};
	$page=CurrentPageName();
	$array["ShowMail"]='message';
	$array["header"]='header';


	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('dialog1_content','$page?tab=$num&ShowBackupMail={$_GET["ShowBackupMail"]}')\" $class>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<br><div id=tablist>$html</div>");		
}

function ShowBackupMail(){
	$page=CurrentPageName();
	$s=new mysql();
	$sql="SELECT MessageBody FROM `storage` WHERE MessageID='{$_GET["ShowBackupMail"]}';";
	$ligne=mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
	
	if(preg_match('#Body-Begin-->(.+?)<\!--X-Body-of-Message-End-->#is',$ligne["MessageBody"],$re)){
		$ligne["MessageBody"]=$re[1];
	}
	
	
	if(preg_match('#<!--X-Head-of-Message-->(.+?)<!--X-Head-of-Message-End-->#is',$ligne["MessageBody"],$re)){
		$head=$re[1];
		$ligne["MessageBody"]=str_replace($head,'',$ligne["MessageBody"]);
	}
	
	$ligne["MessageBody"]=str_replace("<h1>","<h1 style='font-size:15px'>",$ligne["MessageBody"]);
	
	if($_GET["tab"]=="header"){
		$ligne["MessageBody"]=$head;
	}
	
	
	
	$html=main_mailtabs()."
	<div style='width:100%;overflow-y:auto;height:400px'>{$ligne["MessageBody"]}</div>";
	
	echo $html;

	
}

function ShowBackupMailInside(){
	$s=new mysql();
	$sql="SELECT MessageBody FROM `storage` WHERE MessageID='{$_GET["msgbodyif"]}';";
	$ligne=mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
	
	$tpl=new templates();
	
	$ligne["MessageBody"]=str_ireplace('<head>',"<head>".$tpl->head,$ligne["MessageBody"]);
	
	echo $ligne["MessageBody"];
	
	
}


?>