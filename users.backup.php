<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.backup.emails.inc');


if(isset($_GET["ShowBackupMail"])){ShowBackupMail();exit;}
if(isset($_GET["msgbodyif"])){ShowBackupMailInside();exit;}
if(isset($_GET["msgnumber"])){users_message_numbers();exit;}
if(isset($_GET["backup_search"])){query();exit;}
if(isset($_GET["ResendSendMail"])){ResendSendMail();exit;}
if(isset($_GET["show-advanced-options"])){advanced_options();exit;}
if(isset($_GET["save-advanced-options"])){advanced_options_save();exit;}
main_page();

function main_page(){
	
	
	$users=new usersMenus();
	if(($users->AsMailBoxAdministrator) or ($users->AsPostfixAdministrator) or ($users->AsQuarantineAdministrator)){
		$advanced="&laquo;&nbsp;<a href='#' OnClick=\"javascript:BackupAdancedOptions();\" style='font-weight:bolder'>{advanced_options}</a>&nbsp;&raquo;";
	}
	
	$page=CurrentPageName();
	
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


var x_results= function (obj) {
	var results=obj.responseText;
	document.getElementById('query_list').innerHTML=results;
}

function ChargeLogs(){
	whatsnew();
	}
	
function messageNumber(){
	LoadAjax('message_number','$page?msgnumber=yes');
	
}

function LaunchSearch(){
document.getElementById('query_list').innerHTML='<center style=\"margin:10px;\"><img src=img/wait_verybig.gif></center>';
	var backup_search=document.getElementById('backup_search').value;
	var XHR = new XHRConnection();
    XHR.appendData('backup_search',backup_search);
    XHR.sendAndLoad('$page', 'GET',x_results);
}

function BackupAdancedOptions(){
	YahooWin2(500,'$page?show-advanced-options','{advanced_options}');
}

var x_SaveAdvancedOptions= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin2Hide();
}

function SaveAdvancedOptions(){
	var recipient=document.getElementById('recipient').value;
	var sender=document.getElementById('sender').value;
	var XHR = new XHRConnection();
    XHR.appendData('save-advanced-options','yes');
    XHR.appendData('recipient',recipient);
    XHR.appendData('sender',sender);
    
    
    XHR.sendAndLoad('$page', 'GET',x_SaveAdvancedOptions);

}

</script>
<script>demarre();messageNumber();</script>
<script>;setTimeout(\"whatsnew()\",3000);</script>
	
	<input type='hidden' id='ou' value='{$_SESSION["ou"]}'>
	
	<center>
		<table style='width:100%;margin:4px;padding:4px;border-right:1px solid #CCCCCC;border-bottom:1px solid #CCCCCC'>
		<tr>
		<td valign='top' width=1%><img src='img/title-esearch.png' style='margin-bottom:3px'>
			<div style='text-align:right'><strong style='font-size:11px'>(<span id='message_number'></span> emails)</strong></div></td>
		<td valign='top' style='padding-left:5px'>
	
				<input type='text' name='backup_search' id='backup_search' style='font-size:13px;font-family:Tahoma;padding:5px' 
				style='width:90%' onkeypress=\"{if(event.keyCode==13)LaunchSearch()}\">
				
		<table style='width:100%'>
		<tr>
			<td valign='top'><p style='font-size:11px'>{backup_text}</p></td>
			<td valign='top'>
				<table style='width:100%'>
				<tr>
				<td align='right'>$advanced</td>
				<td align='right'>
					<input type='button' OnClick=\"javascript:LaunchSearch();\" value='{search}&nbsp;&raquo;' style='font-size:13px'>
				</td>
				</tr>
				</table>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		
		
	</center>
	<div id='query_list'></div>
	
	
	
	";
$cfg["JS"][]="js/users.backup.js";	
$tpl=new template_users("{backup}",$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
	
}

function ResendSendMail(){
	
	$path=$_GET["ResendSendMail"];
	$email=$_GET["email"];
	$sock=new sockets();
	$sock->getfile("ResendSendMail:$path;$email");
	
$uid=$_SESSION["uid"];
			$user=new user($uid);
			$js="javascript:ResendSendMail('$path','$user->mail')";
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body(Paragraphe('folder-64-fetchmail.png','{resend_mail_success}',"{resend_mail_text_success}<br>",$js,"{resend_mail_text}"));
}

function users_message_numbers(){
	
	$users=new usersMenus();
	if($users->AsPostfixAdministrator){
		$b=new backup_email();
	}else{
		$b=new backup_email($_SESSION["ou"]);
	}
	
	$messagenumber=$b->MessageNumber();
	echo $messagenumber;
}

function query(){
	
	
	$users=new usersMenus();
	$stringtofind=trim($_GET["backup_search"]);
	$stringtofind=str_replace("*",'',$stringtofind);
	
	$recipient_to_find=trim($_SESSION["backup_search_recipient"]);
	if($recipient_to_find<>null){
		if(strpos($recipient_to_find,"*")==0){$recipient_to_find=$recipient_to_find."*";}
		$recipient_to_find=str_replace("*","%",$recipient_to_find);
		$recipient_to_find_fields=",storage_recipients.recipient";
		$recipient_to_find=" AND storage_recipients.recipient LIKE '$recipient_to_find'";
	}
	
	$sender_to_find=trim($_SESSION["backup_search_sender"]);
	if($sender_to_find<>null){
		if(strpos($sender_to_find,"*")==0){$sender_to_find=$sender_to_find."*";}
		$sender_to_find=str_replace("*","%",$sender_to_find);
		$sender_to_find=" AND storage.mailfrom LIKE '$sender_to_find'";
	}	
	
	
	
	
	
	if(!$users->AsPostfixAdministrator){
			$uid=$_SESSION["uid"];
			$ldap=new clladp();
			$user=new user($uid);
			$back=new backup_query($uid);
			$mymails=$back->sql_mymails;
			$sql="SELECT storage.MessageID,storage.mailfrom$recipient_to_find_fields,storage.zDate,storage.subject,storage.MessageBody,MATCH (storage.MessageBody) 
			AGAINST (\"$stringtofind\") AS pertinence  
			FROM storage,storage_recipients WHERE storage.MessageID=storage_recipients.MessageID AND ($mymails)$recipient_to_find$sender_to_find ORDER BY pertinence DESC LIMIT 0,90";
			
			if($stringtofind==null){
				$sql="SELECT storage.MessageID,storage.mailfrom$recipient_to_find_fields,storage.zDate,storage.subject,storage.MessageBody 
				FROM storage,storage_recipients WHERE storage.MessageID=storage_recipients.MessageID AND ($mymails)$recipient_to_find$sender_to_find ORDER BY zDate DESC LIMIT 0,90";
			}
			
			
	}else{
		$sql="SELECT storage.MessageID,storage.mailfrom$recipient_to_find_fields,storage.zDate,storage.subject,storage.MessageBody,MATCH (storage.MessageBody) 
			AGAINST (\"$stringtofind\") AS pertinence  
			FROM storage,storage_recipients WHERE storage.MessageID=storage_recipients.MessageID$recipient_to_find$sender_to_find ORDER BY pertinence DESC LIMIT 0,90";
			
		if($stringtofind==null){
				$sql="SELECT storage.MessageID,storage.mailfrom$recipient_to_find_fields,storage.zDate,storage.subject,storage.MessageBody 
				FROM storage,storage_recipients WHERE storage.MessageID=storage_recipients.MessageID$recipient_to_find$sender_to_find ORDER BY zDate DESC LIMIT 0,90";
			}			
	}
	
writelogs($sql,__FUNCTION__,__FILE__);
$s=new mysql();
$results=$s->QUERY_SQL($sql,"artica_backup");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$html=$html.formatResults($ligne);
	
	}
	
echo "$html";
exit;

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


function formatResults($ligne){
return formatQueryResultsAsGoogle($ligne);
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
	
	$sql="SELECT message_path, MessageSize FROM orgmails WHERE MessageID='{$_GET["ShowBackupMail"]}'";
	$ligne=mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
	$message_path=$ligne["message_path"];
	$message_size=$ligne["message_size"];
	
	
	$sql="SELECT MessageBody FROM storage WHERE MessageID='{$_GET["ShowBackupMail"]}';";
	$ligne=mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
	
	
	
	
	
	if(preg_match('#Body-Begin-->(.+?)<\!--X-Body-of-Message-End-->#is',$ligne["MessageBody"],$re)){
		$ligne["MessageBody"]=$re[1];
	}
	
	
	if(preg_match('#<!--X-Head-of-Message-->(.+?)<!--X-Head-of-Message-End-->#is',$ligne["MessageBody"],$re)){
		$head=$re[1];
		$ligne["MessageBody"]=str_replace($head,'',$ligne["MessageBody"]);
	}
	$ligne["MessageBody"]=CleanMail($ligne["MessageBody"]);
		
	if($_GET["tab"]=="header"){
		$ligne["MessageBody"]=$head;
	}
	
	if($message_path<>null){
		$sock=new sockets();
		$r=$sock->getfile("statfile:$message_path");
		if(!preg_match('#SUCCESS#',$r)){
			$resend=Paragraphe('folder-64-fetchmail-grey.png','{mail_backup_lost}','{mail_backup_lost_text}');
		}
		else{
			$uid=$_SESSION["uid"];
			$user=new user($uid);
			$usr=new usersMenus();
			if($usr->AsPostfixAdministrator){
				$user->mail="ask";
			}
			
			$js="javascript:ResendSendMail('$message_path','$user->mail')";
			$tpl=new templates();
			$resend="<div id='resendmail'>".Paragraphe('folder-64-fetchmail.png','{resend_mail}','{resend_mail_text}<br>'.$user->mail,$js,"{resend_mail_text}")."</div>";
			$resend=$tpl->_ENGINE_parse_body($resend);
			
		}
	}
	
	$html=main_mailtabs()."
	<table style='width:100%'>
	<tr>
		<td valign='top'>
	<div style='width:600px;overflow:auto;height:400px;margin-top:5px;padding:5px;background-color:#F9F9F9;border:1px solid #CCCCCC'>
		{$ligne["MessageBody"]}
	</div>
	</td>
	<td valign='top'>$resend</td>
	</tr>
	</table>";
	
	echo $html;

	
}

function advanced_options(){
	
	$html="<H1>{advanced_options}</H1>
	<H3>{search_by}:</h3>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{recipient}:</td>
		<td>" . Field_text('recipient',$_SESSION["backup_search_recipient"],'width:190px')."</td>
	</tr>
	<tr>
		<td class=legend>{sender}:</td>
		<td>" . Field_text('sender',$_SESSION["backup_search_sender"],'width:190px')."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><input type=button OnClick=\"javascript:SaveAdvancedOptions();\" value='{apply}&nbsp;&raquo;'></td>
	</tr>
	</table>
	";
	
$tp=new templates();
echo $tp->_ENGINE_parse_body($html);	
	
	
}

function advanced_options_save(){
	
$users=new usersMenus();
	if(($users->AsMailBoxAdministrator) or ($users->AsPostfixAdministrator) or ($users->AsQuarantineAdministrator)){
		$_SESSION["backup_search_recipient"]=$_GET["recipient"];
		$_SESSION["backup_search_sender"]=$_GET["sender"];
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	}
	
	
}


function ShowBackupMailInside(){
	$s=new mysql();
	$sql="SELECT MessageBody FROM storage WHERE MessageID='{$_GET["msgbodyif"]}';";
	$ligne=mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
	
	$tpl=new templates();
	
	$ligne["MessageBody"]=str_ireplace('<head>',"<head>".$tpl->head,$ligne["MessageBody"]);
	
	echo $ligne["MessageBody"];
	
	
}


?>