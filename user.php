<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
include_once(dirname(__FILE__)."/ressources/class.mailmanCTL.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}
$tpl=new templates();

if(isset($_GET["UserIdentity"])){echo UserIdentity();exit;}

$users=new usersMenus();

if($users->POSTFIX_INSTALLED){
	$bottom=Building_bottom_section_mail();
}

$websites=Websites();
$user=new user($_SESSION["uid"]);
$GLOBALS["CLASS_USER"]=$user;

$title="{welcomeb} $user->DisplayName";
$UserIdentity=UserIdentity();
$mailman_sites=mailman_sites();
$html="
<div style='margin:10px'>
<H1>$title</H1>

	<table style='width:100%;'>
	<tr>
	<td valign='top'>$websites
	</td>
	<td valign='top' align='right'>
	<div id='UserIdentity'>
		$UserIdentity
	</div>
	$mailman_sites
	</td>
	</tr>
	</table>
		
</div>



			

";




$html=$html.$bottom;

if(isset($_GET["ajax"])){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	exit;
}


$tpl=new templates("&nbsp;",$user->DisplayName);
echo $tpl->buildPage();


function Building_bottom_section_mail(){
	if(is_object($GLOBALS["CLASS_USER"])){$user=$GLOBALS["CLASS_USER"];}else{$user=new user($_SESSION["uid"]);}
	if(!isset($_SESSION["ALL_MAILS"])){
		$_SESSION["ALL_MAILS"]=$user->HASH_ALL_MAILS;
	}
	
	$func=new funct();
	
	if($_SESSION["getCountOFMailsRTM"]==null){
		$count=$func->getCountOFMailsRTM();
		$_SESSION["getCountOFMailsRTM"]=$count;
	}else{
		$count=$_SESSION["getCountOFMailsRTM"];
	}
	
	
	$events="
   		<div class=\"bottom-box1\">
      		<div class=\"bottom-box1-inside\"><span class=\"title-14\">$count {events}</span>
      		<div class=\"bottom-box-th\"><img src=\"img/eve-pic.jpg\"></div>
	    		<div class=bottom-box-tx>{user_events_emails_query_text}</div>
		   		<div class=\"green-link-box\"><a href=\"rtmm.php\" class=\"read-more\">&raquo;&raquo;&nbsp;{events}</a></div>

	 		</div>
	  	</div>";

	  if($_SESSION["getSizeOfBackupedMails"]==null){	  	
	 	$count=$func->getSizeOfBackupedMails();
	 	$count=$count/1024;
	 	$count=FormatBytes($count);
	 	$_SESSION["getSizeOfBackupedMails"]=$count;
	  }else{
	  	$count=$_SESSION["getSizeOfBackupedMails"];
	  }
	 
	$backup="
	
   		<div class=\"bottom-box1\">
      		<div class=\"bottom-box1-inside\"><span class=\"title-14\">$count {backup}</span>
      		<div class=\"bottom-box-th\"><img src=\"img/eve-pic-1.jpg\"></div>
	    		<div class=bottom-box-tx>{user_backup_emails_query_text}</div>
		   		<div class=\"green-link-box\"><a href=\"backup.php\" class=\"read-more\">&raquo;&raquo;&nbsp;{backup}</a></div>

	 		</div>
	  	</div>";

  if($_SESSION["getCountOFMailQuar"]==null){	  	
	 	$count=$func->getCountOFMailQuar();

	 	
	 	$_SESSION["getCountOFMailQuar"]=$count;
	  }else{
	  	$count=$_SESSION["getCountOFMailQuar"];
	  }
	 	  	
$quarantine="
	
   		<div class=\"bottom-box1\">
      		<div class=\"bottom-box1-inside\"><span class=\"title-14\">$count {quarantinems}</span>
      		<div class=\"bottom-box-th\"><img src=\"img/eve-pic-2.jpg\"></div>
	    		<div class=bottom-box-tx>{user_quarantine_emails_query_text}</div>
		   		<div class=\"green-link-box\"><a href=\"backup.php\" class=\"read-more\">&raquo;&raquo;&nbsp;{backup}</a></div>

	 		</div>
	  	</div>";	  	
	  
	  
	  
	  
	  	

	$UserIdentity=UserIdentity();
	  
	return"

	<div class=\"body-bottom\">$events$backup$quarantine</div>";
	
}

function UserIdentity(){
if(is_object($GLOBALS["CLASS_USER"])){$user=$GLOBALS["CLASS_USER"];}else{$user=new user($_SESSION["uid"]);}
	
	$html="
	
<div class='c_fr' style='width:300px'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div style='border:1px solid #BBD8FB;text-align:center;padding:5px;background-color:#FFF'>".imgtootltip("$user->img_identity","{edit}","Loadjs('user.picture.php')")."</div>
		</td>
		</td>
		<td valign='top'>	
			<table style='width:100%'>
			<tr>
				<td align='right'><strong><H2>$user->DisplayName</H2>
				
				<span style='font-size:9px'>$user->mail</span>
				<br>
				<span style='font-size:9px'>$user->telephoneNumber</span>
				
				</td>
			</tr>
		
			</table>
		</td>
	</tr>
	<tr>
		<td colspan=2 align='right' style='border-top:1px dotted #BBD8FB'>
		".button("{edit}","Loadjs('user.edit.php')")."</td>
		</tr>
</table></div>	";
$tpl=new templates();	
return $tpl->_ENGINE_parse_body($html);
	
	
	
}

function Websites(){
	if(is_object($GLOBALS["CLASS_USER"])){$usr=$GLOBALS["CLASS_USER"];}else{$usr=new user($_SESSION["uid"]);}
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	include_once(dirname(__FILE__).'/ressources/class.apache.inc');
	$h=new vhosts($usr->ou);
	$array=$h->LoadVhosts($usr->ou);
	$tpl=new templates();
	if(count($array)==0){return null;}
	//s_PopUp
	$html="<H3>{available_websites}</h3>";
	
	while (list ($www, $type) = each($array) ){
		if($www==null){continue;}
		if($type==null){continue;}
		if($type=="ARTICA_USR"){continue;}
		$js="s_PopUp('http://$www:$ApacheGroupWarePort',800,800)";
		$text=$tpl->_ENGINE_parse_body("{{$h->TEXT_ARRAY[$type]["TEXT"]}}");
		if(strlen($text)>90){$text=substr($text,0,87)."...";}
		$html=$html.iconTable("","{{$h->TEXT_ARRAY[$type]["TITLE"]}}","$text",$js);

	}
	
	
	return $html;
}

function mailman_sites(){
	$users=new usersMenus();
	if(is_object($GLOBALS["CLASS_USER"])){$user=$GLOBALS["CLASS_USER"];}else{$user=new user($_SESSION["uid"]);}
	$mailman=new mailman_control($user->ou);
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");	
	if(!$users->MAILMAN_INSTALLED){return null;}
	if($sock->GET_INFO('MailManEnabled')<>1){return null;}	
	$array=$mailman->GetLists_array();
	if(!is_array($array)){return null;}
	$text="<table>";
	while (list ($liste, $www) = each($array) ){
		$text=$text."
			<tr>
				<td width=1%><img src='img/3.gif'></td>
				<td><span  OnClick=\"javascript:s_PopUp('http://$www:$ApacheGroupWarePort/',1024,800);\" style='color:black;text-decoration:underline'>$liste ($www)</span></td>
			</tr>";
	}
	
	$html=iconTable("","{your_distributions_lists}","$text</table>");
$tpl=new templates();	
return $tpl->_ENGINE_parse_body($html);	
}

function vacation(){
	if(is_object($GLOBALS["CLASS_USER"])){$user=$GLOBALS["CLASS_USER"];}else{$user=new user($_SESSION["uid"]);}
	if($user->vacationActive<>"TRUE"){return null;}
	$tpl=new templates();
	$page=CurrentPageName();
	$date1=date("Y-m-d",$user->vacationStart);
	$date2=date("Y-m-d",$user->vacationEnd);

	
	
	
}







?>
