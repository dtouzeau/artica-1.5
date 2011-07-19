<?php
include_once('ressources/class.ldap.inc');
include_once('ressources/class.user.inc');
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.mysql.inc');
$users=new usersMenus();

if(isset($_GET["QuarantineShowMailFile"])){QuarantineShowMailFile();exit;}
if(isset($_GET["ReleaseMail"])){ReleaseMail();exit;}
if(isset($_GET["AddWhiteAndRelease"])){AddWhiteAndRelease();exit;}
if(isset($_GET["DeleteMailsFrom"])){DeleteMailsFrom();exit;}
if(isset($_GET["QuarantineMessageDelete"])){QuarantineMessageDelete();exit;}
INDEX();
function INDEX(){
	
	if(isset($_GET["mail"])){
		$jsshowmail="
		<script>
			Loadjs('domains.quarantine.php?message-id={$_GET["mail"]}');
		</script>
		";
	}
	

	if(isset($_GET["search"])){
		$lsatquarantines=Search();
		$title='your_query';
	}else{
	$lsatquarantines=LastQuarantines();
	$title='last_quarantine_files';
	}
	
	$user=new user($_SESSION["uid"]);

$show["10"]=10;
$show["20"]=20;
$show["50"]=50;
$show["100"]=100;
$show["150"]=150;
$show["300"]=300;
$show["500"]=500;

if($_GET["show"]==null){$_GET["show"]=50;}

$show=Field_array_Hash($show,"show",$_GET["show"]);
	
$html="
<table style='width:100%'>
<tr>
<td valign='top'>
<img src='img/90-quarantaine.png'>
</td>
<td valign='top'>
<form name=FFM1 method=get>
<input type='hidden' name='search' value='yes'>
<center>
" . RoundedLightGrey("
<div class=caption>{star_supported}</div>
<table align=center class=table_form>
<tr><td colspan=2><H3>{search_text}</H3></td></tr>
<tr><td nowrap class=legend><strong>{by_sender}:</td><td align='left'>" . Field_text('bySender',$_GET["bySender"])."</td></tr>
<tr><td nowrap class=legend><strong>{by_subject}:</td><td align='left'>" . Field_text('BySubject',$_GET["BySubject"])."</td></tr>
<tr><td nowrap class=legend><strong>{show}:</td><td align='left'>$show&nbsp;{rows}</td></tr>
<tr><td colspan=2 align=right><input type=submit value='{search}&nbsp;&raquo'></td></tr>
<tr><td colspan=2 class=legend>{for}:&nbsp;" .  implode(", ",$user->HASH_ALL_MAILS). "</td></tr>
</table>")."
</center>
</form>
</td>
</tr>
</table>


<p>&nbsp;</p>
	<table style='width:100%' align=center>
		<tr>
			<td width=1% valign='top'>
				<H4>{{$title}}</H4>$lsatquarantines
			</td>
		</tr>
	</table>
$jsshowmail
";
$JS["JS"][]="js/user.quarantine.js";
$tpl=new template_users('{query_quarantine}',$html,0,0,0,0,$JS);
echo $tpl->web_page;	
	
}

function LastQuarantines(){
	writelogs("LastQuarantines -> Start",__FUNCTION__,__FILE__);
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.user.inc');
	$ldap=new clladp();
	$q=new mysql();
	$user=new user($_SESSION["uid"]);
	
	if($_GET["page"]>1){
		$limit_1=$_GET["page"]."0";
	}else{$limit_1="0";}	
	
while (list ($num, $ligne) = each ($user->HASH_ALL_MAILS) ){
	$recipient_sql[]="mailto='$ligne'";
	
}
	$recipients=implode(" OR ",$recipient_sql);
	$sql="SELECT mailfrom,zDate,MessageID,DATE_FORMAT(zdate,'%W %D %H:%i') as tdate,subject,mailto 
	FROM quarantine WHERE 1 AND ($recipients) ORDER BY zDate DESC LIMIT 0,{$_GET["show"]};";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");		
	$html=ParseSqlresults($results);
	//$tabs=QuarantineTabs();
	return "
	
	<div style='font-size:12px;text-align:right;font-weight:bold;font-style:italic;width:100%;border-bottom:1px dotted #CCCCCC;'>
	" . QuarantineNumber() . " {messages_in_quarantine}</div>" .$html;
}

function ParseSqlresults($results){
	
$html="
<div style='height:400px;overflow:auto;margin-top:5px'>
<table style='width:99%' class=table_form>
	<tr " . CellRollOver().">
	<th>&nbsp;</th>
	<th><strong>{time}</strong></th>
	<th><strong>{mail_from}</th>
	<th><strong>{subject}</th>
	<th>&nbsp;</th>
	</tr>";
	writelogs("LastQuarantines -> Query sql end",__FUNCTION__,__FILE__);
	while($ligne=@mysql_fetch_array($results)){	
		$count=$ligne["ID"];
		writelogs("$count",__FUNCTION__,__FILE__);
		$file=basename($ligne["message_path"]);
		$delete=imgtootltip('x.gif','{delete}',"QuarantineMessageDelete('{$ligne["ID"]}')");
		$ligne["subject"]=htmlentities($ligne["subject"]);
		
		$tooltip="<H4>{view}</H4><ul style=font-size:13px><li><b>Date:</b>{$ligne["zDate"]}</li><li><b>From:</b>{$ligne["mailfrom"]}</li></ul>";
		
		
		if(strlen($ligne["mailfrom"])>30){$ligne["mailfrom"]=substr($ligne["mailfrom"],0,27)."...";}
		if(strlen($ligne["subject"])>34){$ligne["subject"]=substr($ligne["subject"],0,31)."...";}
		
		$ligne["subject"]=substr($ligne["subject"],0,500);
		$edit="OnClick=\"javascript:Loadjs('domains.quarantine.php?message-id={$ligne["MessageID"]}')\"";
		if($color=="#CCCCCC"){$color="#FFFFFF";}else{$color="#CCCCCC";}
		
		
		
		
		$html=$html . "
		<tr id='line$count' style='background-color:$color;font-size:12px' ".CellRollOver_black(null,$tooltip,$color).">
			<td valign='top' style='padding:3px'><img src='img/fw_bold.gif'></td>	
			<td nowrap style='font-size:12px;padding:3px' $edit>{$ligne["tdate"]}</a></td>
			<td nowrap style='font-size:12px;padding:3px' $edit>{$ligne["mailfrom"]}</a></td>
			<td nowrap style='font-size:12px;padding:3px' $edit>{$ligne["subject"]}</td>
			<td>$delete</td>
		</tr>
		
		";
	}	
	
	$html=$html ."</table></div>";	
	return RoundedLightGrey($html);
}

function QuarantineNumber(){
	$user=new user($_SESSION["uid"]);
	
	while (list ($num, $ligne) = each ($user->HASH_ALL_MAILS) ){
		$recipient_sql[]="quarantine.mailto='$ligne'";
	}
	$recipients=implode(" OR ",$recipient_sql);
	$sql="SELECT COUNT(quarantine.mailfrom) as tcount FROM quarantine WHERE 1 AND ($recipients)";	
	
	$q=new mysql();
	
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	return $ligne["tcount"];
	
}

function QuarantineTabs(){
	$number_pages=QuarantineNumber();
	$next_finish=$number_pages-10;
	$page=CurrentPageName();
	$number=$number_pages/50;
	$next=10;
	$start=1;
	
	if(isset($_GET["next"])){
		$start=$next;
		$next=$next+10;
		if($number>10){$number=$next;}
	}else{
		if($number>10){$number=10;}
	}
	
	if($_GET["page"]==null){
		$query_page=1;
		$next=10;
		$start=1;
	}else{
		$query_page=$_GET["page"];
		}
	
	
	  $query_page_next=$next;
	   $end="<li><a href=\"$page?next=$next&page=$query_page_next\">{next}&raquo;</a></li>
	   <li><a href=\"$page?next=$next_finish&page=$number_pages\">{next}&raquo;&raquo;</a></li>";	
	   
	
	
	for($i=$start;$i<$number;$i++){
		if($query_page==$i){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"$page?page=$i\" $class>&laquo;&nbsp;$i&nbsp;&raquo;</a></li>\n";
			
		}
	return "<div id=tablist>$html$end</div>";			
	
	
}


function QuarantineShowMailFile(){
	$sock=new sockets();
	$email=$sock->getfile('QuarantineShowEmailFile:/var/quarantines/procmail/' .  $_SESSION["uid"] . '/quarantine/' . $_GET["QuarantineShowMailFile"]);
	
	if(preg_match('#<ADDON>(.+?)</ADDON>#is',$email,$reg)){
		$addon=$reg[1];
		$email=str_replace($reg[0],'',$email);
	}
	
	
	$html="
	<input type='hidden' id='extra_infos' value='$addon'>
	<div id='div_extra' style='padding:20px;position:absolute;visibility:hidden;background-color:white;border:2px solid #005447;z-index:10000;width:420px'></div>
	<div style='padding:5px'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<table style='width:100%'>
		<tr><td valign='top' >".Paragraphe('folder-quarantine-release-64.jpg','{release_mail}','{release_mail_text}',"javascript:ReleaseMail(\"{$_GET["QuarantineShowMailFile"]}\")",'release_mail',200) ."</td></tr>
		<tr><td valign='top' >".Paragraphe('folder-quarantine-delete-64.jpg','{delete_mails}','{delete_mails_text}',"javascript:DeleteMailsFrom()",'delete_mails_text',200) ."</td></tr>
		<tr><td valign='top' >".Paragraphe('folder-quarantine-extrainfos-64.jpg','{extra_infos}','{extra_infos_text}',"javascript:ExtraInfos()",'extra_infos',200) ."</td></tr>
		<tr><td valign='top' >".Paragraphe('folder-quarantine-white-64.jpg','{white list}','{white_list_email_text}',"javascript:AddWhiteAndRelease(\"{$_GET["QuarantineShowMailFile"]}\")",'white_list_email_text',200) ."</td></tr>
		
		<tr><td valign='top'></td></tr>
	</table>
	</td>
	<td valign='top' width=99% valign='top'>$email</td>
	</tr>
	</table>
	<div class=caption style='text-align:right'>{file}:{$_GET["QuarantineShowMailFile"]}</div>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
function ReleaseMail(){
	$file=$_GET["ReleaseMail"];
	$sock=new sockets();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($sock->getfile('QuarantineReleaseMail:/var/quarantines/procmail/' .  $_SESSION["uid"] . '/new/' . $file.":".$_SESSION["uid"]));
	
}
function AddWhiteAndRelease(){
	$email_from=$_GET["AddWhiteAndRelease"];
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$hashObj=$ldap->getobjectDNClass($hash["dn"],1);

	if(!isset($hashObj["ArticaSettings"])){
		$add_array["objectClass"][]="ArticaSettings";
		$ldap->Ldap_add_mod($hash["dn"],$add_array);
	}
	
	$update_array["KasperkyASDatasAllow"][]=$email_from;
	$ldap->Ldap_add_mod($hash["dn"],$update_array);
	if($ldap->ldap_last_error<>null){echo nl2br($ldap->ldap_last_error);}
}
function DeleteMailsFrom(){
	$ldap=new clladp();
	$sender=$_GET["DeleteMailsFrom"];
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mail=$hash["mail"];
	
	$sql="SELECT file_path FROM `quarantine` WHERE `mailto`='$mail'  AND `mailfrom`='$sender'";
	$resultats=QUERY_SQL($sql);
	while($ligne=mysql_fetch_array($resultats,MYSQL_ASSOC)){	
		$lines=$lines.$ligne["file_path"] . "\n";
		}
	
	$tempfile=dirname(__FILE__)."/ressources/conf/" . md5($mail);
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $lines); 
	fclose($fp);		
	
	$sock=new sockets();
	$res=trim($sock->getfile("QuarantineDeletePattern:$tempfile"));	
	if($res=="OK"){
		$sql="DELETE FROM `quarantine` WHERE `mailto`='$mail'  AND `mailfrom`='$sender'";
		QUERY_SQL($sql);
		echo "OK";
	}
}

function Search(){
	$Sender=$_GET["bySender"];
	$BySubject=$_GET["BySubject"];
	$Sender=str_replace('.','\.',$Sender);
	$Sender=str_replace('*','.+',$Sender);
	$_GET["byOther"]=str_replace('*','.+',$_GET["byOther"]);
	$BySubject=str_replace('.','\.',$BySubject);
	$BySubject=str_replace('*','.+',$BySubject);
	$BySubject=str_replace('[','\[',$BySubject);
	$BySubject=str_replace(']','\]',$BySubject);	
	
	if($Sender<>null){
		$regex="^From:.+?$Sender";
		}

		
		
	$q=new mysql();
	$user=new user($_SESSION["uid"]);
	while (list ($num, $ligne) = each ($user->HASH_ALL_MAILS) ){$recipient_sql[]="mailto='$ligne'";}
	$recipients=implode(" OR ",$recipient_sql);	
	if($Sender<>null){$Sender= " AND  mailfrom REGEXP '$Sender'";}	
	if($BySubject<>null){$BySubject= " AND subject REGEXP '$BySubject'";}
	
	$sql="SELECT *,DATE_FORMAT(zdate,'%W %D %H:%i') as tdate FROM quarantine WHERE 1 AND($recipients) $Sender $BySubject LIMIT 0,50";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	return ParseSqlresults($results);
	}
	
function QuarantineMessageDelete(){
	$ID=$_GET["QuarantineMessageDelete"];
	$sql="SELECT message_path FROM messages WHERE ID='$ID'";
	$ligne=mysql_fetch_array(QUERY_SQL($sql)); 
	$lines=$ligne["message_path"];
	$tempfile=dirname(__FILE__)."/ressources/conf/" . md5($sql);
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $lines); 
	fclose($fp);			
	
	$sock=new sockets();
	$res=trim($sock->getfile("QuarantineDeletePattern:$tempfile"));	
	if($res=="OK"){
		$sql="UPDATE `messages` SET `Deleted` = '1'   WHERE ID='$ID' ";
		QUERY_SQL($sql);
		echo "OK";
	}
	
}

function auth(){

	
}
	
	


?>