<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.backup.inc');
	
	
	if(isset($_GET["restore-index"])){index();exit;}
	if(isset($_GET["search_user"])){search_user();exit;}
	if(isset($_GET["restorembx-selected-user"])){echo find_mailbox_ressources_from_uid($_GET["restorembx-selected-user"]);exit;}
	if(isset($_GET["filekey"])){filekey_history();exit;}
	if(isset($_GET["DarRestoreMBX"])){DarRestoreMBX();exit;}
	if(isset($_GET["GetStatus"])){restore_status();exit;}
	
	
	js();
	
	
function js(){

	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("restore_mailbox");
	$DarRestoreMBX_perform=$tpl->_ENGINE_parse_body('{DarRestoreMBX_perform}');
	
	$html="
var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;
	
	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;

		
		if(document.getElementById('restore-progress-number')){
			if(document.getElementById('restore-progress-number').value>99){
				{$prefix}finish();
				return false;
			}
		}
		
		if ({$prefix}tant < 5 ) {                           
		{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",900);
	      } else {
			{$prefix}tant = 0;
			{$prefix}LoadStatus();
			{$prefix}demarre(); 
			                              
	   }
	}

	var x_{$prefix}ChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('restorembxdiv').innerHTML=tempvalue;
	}	

	function {$prefix}LoadStatus(){
		var XHR = new XHRConnection();
		XHR.appendData('GetStatus','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChangeStatus);
	}


	function {$prefix}finish(){
		if(document.getElementById('wait')){
			document.getElementById('wait').innerHTML='';
		}
		
	}	
	
	function {$prefix}StartPage(){
		RTMMail('550','$page?restore-index=yes');
	
	}
	
	function RestoreMBXID(filekey){
		YahooWin6(550,'$page?filekey='+filekey,'$title');
	}
	
	var x_RestoreMBXSearchUser=function (obj) {
		document.getElementById('RestoreMBXSearchUserResults').innerHTML=obj.responseText;
		}			

	function RestoreMBXSearchUser(){
		var username=document.getElementById('username').value;
		var XHR = new XHRConnection();
		XHR.appendData('search_user',username);
		document.getElementById('RestoreMBXSearchUserResults').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_RestoreMBXSearchUser);	
	}		
	
	function RestoreMBXSearchUserKey(e){
		if(checkEnter(e)){RestoreMBXSearchUser();}
	}
	
	var x_RestoreMbxSelectedUser=function (obj) {
		document.getElementById('RestoreMBXSearchUserResults').innerHTML=obj.responseText;
		}		
	
	function RestoreMbxSelectedUser(user){
		var XHR = new XHRConnection();
		XHR.appendData('restorembx-selected-user',user);
		document.getElementById('RestoreMBXSearchUserResults').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_RestoreMbxSelectedUser);	
		}
		
	var x_DarRestoreMBX=function (obj) {
		document.getElementById('restorembxdiv').innerHTML=obj.responseText;
		{$prefix}demarre();
		}	
		
function DarRestoreMBX(idfile,database){
		if(confirm('$DarRestoreMBX_perform')){
			var XHR = new XHRConnection();
			XHR.appendData('DarRestoreMBX',idfile);
			XHR.appendData('database',database);
			document.getElementById('restorembxdiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_DarRestoreMBX);	
			}
	}
	
	{$prefix}StartPage();";
	
echo $html;
}

function index(){
	

	$html="
	<H1>{restore_mailbox}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>". RoundedLightWhite("<img src='img/128-hand-user.png'>")."<br><p class=caption>{restore_mailbox_text}</p></td>
		<td valign='top'>
		<p class=caption>{restore_mailbox_ask_user}</p>
			<table style='width:100%' class=table_form>
				<tr>
					<td valign='top' class=legend nowrap>{username}:</td>
					<td valign='top'>" . Field_text('username',null,'width:120px',null,null,null,false,"RestoreMBXSearchUserKey(event)")."</td>
					<td valign='top'><input type='button' OnClick=\"javascript:RestoreMBXSearchUser()\" value='{search}&nbsp;&raquo;'></td>
				</tr>
			</table>
<br>".RoundedLightWhite("
	<div id='RestoreMBXSearchUserResults' style='width:99%;height:150px;overflow:auto'></div>")."			
		</td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}


function search_user(){
	
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$stringtofind=$_GET["search_user"];
	$tpl=new templates();
	$usermenu=new usersMenus();
	$ldap=new clladp();
	if($usermenu->AsAnAdministratorGeneric==true){
		$hash_full=$ldap->UserSearch(null,$stringtofind);
	}else{
		$us=$ldap->UserDatas($_SESSION["uid"]);
		$hash_full=$ldap->UserSearch($us["ou"],$stringtofind);
	}
	
	$html="<table style='width:100%'>";

	for($i=0;$i<$hash_full[0]["count"];$i++){
		$displayname=$hash_full[0][$i]["displayname"][0];
		$mail=$hash_full[0][$i]["mail"][0];
		if(strlen($mail)>27){$mail=substr($mail,0,24).'...';}
		$uid=$hash_full[0][$i]["uid"][0];
		$js="RestoreMbxSelectedUser('$uid');";
		$html=$html . 
		"<tr ". CellRollOver($js).">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><code nowrap>$displayname</code></td>
			<td><code>$mail</code></td>
		</tr>
			
		";
		
	}
	
	$html=$html ."</table>";
	echo $html;
	
}

function find_mailbox_ressources_from_uid($uid){
	$firstletter=substr($uid,0,1);
	$uidp=$uid;
	$uidp=str_replace(".","^",$uidp);
	$pattern="$firstletter/user/$uidp/cyrus.index";
	$sql="SELECT filekey,filedate,filepath,mount_md5 FROM dar_index WHERE filepath='$pattern' AND filesize>0 AND database_name='cyrus_imap_mail'";
	$q=new mysql();
	$html="
	<p class=caption>{external_resource_choose_text}</p>
	<table style='width:100%'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th>{date}</th>
		<th>{external_resource}</th>
	</tr>";
	
	
	$resultats=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($resultats,MYSQL_ASSOC)){
			$filedate=$ligne["filedate"];
			$mount_md5=$ligne["mount_md5"];
			$filekey=$ligne["filekey"];
			$filepath=$ligne["filepath"];
			$filedate=texttooltip($filedate,$filepath);
			$js="RestoreMBXID('$filekey');";
			
			$html=$html."
			<tr ". CellRollOver($js).">
				<td valign='top' width=1%><img src='img/fw_bold.gif'></td>
				<td valign='top' nowrap>$filedate</td>
				<td valign='top' width=99%>".hide_ressources($mount_md5)."</td>
			</tr>					
			";
			
			
		}
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
	
}

function filekey_history(){
	$id=$_GET["filekey"];
	$sock=new sockets();
	$sock->getfile("DarFindFiles:$id");
	$datas=file_get_contents("ressources/logs/dar.find.$id.txt");
	$tbl=explode("\n",$datas);
	
	$htmlt="<table style='width:100%' class=table_form>";
	
	while (list ($num, $ligne) = each ($tbl) ){
		
		
		
		if(preg_match('#\s+([0-9]+)\s+(.+)#',$ligne,$re)){
			$js="DarRestoreMBX('$id','{$re[1]}');";
			$htmlt=$htmlt."<tr ". CellRollOver($js).">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td>$re[2]</td>
			</tr>";
		}else{
			
		}
		
	}
	
	$htmlt=$htmlt . "</table>";
	
	$html="<H1>{restore}</H1>
	<div id='restorembxdiv'>
	<input type='hidden' id='restore-progress-number' value='100'>
	<p class=caption>{restore_choose_date}</p>
	<hr>
	$htmlt
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"dar.index.php");	
	}
function DarRestoreMBX(){
	$users=new usersMenus();
	$id=$_GET["DarRestoreMBX"];
	$database=$_GET["database"];
	$sock=new sockets();
	$sock->getfile("DarRestoreDirectory:$id;dir:$users->cyr_partition_default;$database");	
	restore_status();
}
function Status($pourc,$text=null){
	if($text==null){$text="{scheduled}";}
	if($text<>null){$text="&nbsp;$text";}
$color="#5DD13D";

if($pourc>100){$pourc=100;$color="red";}

$html="
<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
</div>
	<div style='font-size:12px;font-weight:bold;text-align:center;'>
	&laquo;&laquo;<span style='font-size:12px;font-weight:bold;text-decoration:underline;'>$text</span>&raquo;&raquo;</div>
	<input type='hidden' id='restore-progress-number' value='$pourc'>
	
";	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}

function restore_status(){
	if(!is_file("ressources/logs/exec.dar.find.restore.ini")){
		echo Status(10,"{waiting}...");
		return null;
	}
	$ini=new Bs_IniHandler("ressources/logs/exec.dar.find.restore.ini");
	echo Status($ini->get('STATUS','progress'),$ini->get('STATUS','text'));
}
	


?>