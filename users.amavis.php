<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.mysql.inc');
	
	
	if(isset($_GET["amavis-front"])){user_amavis_front();exit;}
	if(isset($_GET["show"])){user_amavis_tabs();exit;}
	if(isset($_GET["amavisSpamLover"])){SaveAmavisConfig();exit;}
	if(isset($_GET["GoBackDefaultAmavis"])){GoBackDefaultAmavis();exit;}
	if(isset($_GET["milter-greylist"])){miltergrelist();exit;}
	if(isset($_POST["greylistDisable"])){miltergrelist_save();exit;}
js();



function js(){
	
	$tpl=new templates();
	
	if(!isset($_GET["userid"])){
		if(!isset($_SESSION["uid"])){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_SESSION["uid"];
	}
	
	if(isset($_GET["userid"])){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_GET["userid"];
		$adduri="&userid=$uid";
		$addXHR="XHR.appendData('userid','$uid');";
	}
	
	
	$translate_page="amavis.index.php";
	$page=CurrentPageName();

	$title=$uid." ::{spam_rules}";
	$title=$tpl->_ENGINE_parse_body($title,$translate_page);
	
	
	$html="
	
	function LoadUserAmavis(){
		YahooWin2(600,'$page?show=yes$adduri','$title');
	}
	
	
	var x_SaveUserAmavis= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				RefreshTab('main_user_as');
			}			
			
	
	function SaveUserAmavis(){
		var XHR = new XHRConnection();
		XHR.appendData('amavisSpamLover',document.getElementById('amavisSpamLover').value);
		XHR.appendData('amavisBadHeaderLover',document.getElementById('amavisBadHeaderLover').value);
		XHR.appendData('amavisBypassVirusChecks',document.getElementById('amavisBypassVirusChecks').value);
		XHR.appendData('amavisBypassSpamChecks',document.getElementById('amavisBypassSpamChecks').value);
		XHR.appendData('amavisBypassHeaderChecks',document.getElementById('amavisBypassHeaderChecks').value);
		XHR.appendData('amavisSpamTagLevel',document.getElementById('amavisSpamTagLevel').value);
		XHR.appendData('amavisSpamTag2Level',document.getElementById('amavisSpamTag2Level').value);
		XHR.appendData('amavisSpamKillLevel',document.getElementById('amavisSpamKillLevel').value);
		XHR.appendData('amavisSpamModifiesSubj',document.getElementById('amavisSpamModifiesSubj').value);
		$addXHR
		AnimateDiv('user-amavis');
		XHR.sendAndLoad('$page', 'GET',x_SaveUserAmavis);	
	
	}
	
	function GoBackDefaultAmavis(){
		var XHR = new XHRConnection();
		XHR.appendData('GoBackDefaultAmavis','$uid');
		AnimateDiv('user-amavis');
		XHR.sendAndLoad('$page', 'GET',x_SaveUserAmavis);	
	}
	
	LoadUserAmavis();
	
	";

	

	echo $html;
	
}

function user_amavis_tabs(){
	$page=CurrentPageName();
	$tpl = new templates ( );
	$arr["amavis-front"]="{content_filter}";
	$users=new usersMenus();
	if($users->MILTERGREYLIST_INSTALLED){
		$sock=new sockets();
		if($sock->GET_INFO("MilterGreyListEnabled")){
			$arr["milter-greylist"]="{greylisting}";
		}
	}
	
if(isset($_GET["userid"])){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_GET["userid"];
		$adduri="&userid=$uid";
	}else{
		$adduri="&userid={$_SESSION["uid"]}";
	}	
	
	while(list( $num, $ligne ) = each ($arr)){
		
		$html[]= "<li><a href=\"$page?$num=yes$adduri\"><span>$ligne</span></a></li>\n";
		
	}
	
	
	
	
	echo $tpl->_ENGINE_parse_body("
	<div id=main_user_as style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_user_as\").tabs();});
		</script>");		
	
}


function user_amavis_front(){
	
	
	
$tpl=new templates();	
$users=new usersMenus();
$sock=new sockets();
if(!$users->AMAVIS_INSTALLED){
		echo $tpl->_ENGINE_parse_body("<div class=explain>{NO_FEATURE_AMAVIS_NOT_INSTALLED}</div>");	
		return;
	}
	$EnableAmavisDaemon=$sock->GET_INFO("EnableAmavisDaemon");
	$EnableLDAPAmavis=$sock->GET_INFO("EnableLDAPAmavis");
	if(!is_numeric($EnableLDAPAmavis)){$EnableLDAPAmavis=0;}
	if(!is_numeric($EnableAmavisDaemon)){$EnableAmavisDaemon=0;}		
	
	if($EnableAmavisDaemon==0){
		echo $tpl->_ENGINE_parse_body("<div class=explain>{NO_FEATURE_AMAVIS_NOT_ENABLED}</div>");	
		return;		
	}

if(isset($_GET["userid"])){
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_GET["userid"];
		$button_admin="<div style='text-align:center'>
		<input type='button' OnClick=\"javascript:GoBackDefaultAmavis('$uid')\" value='{back_to_defaults}&nbsp;&raquo;'></div>";
		
	}
else{
	$uid=$_SESSION["uid"];
}
		
$user=new user($uid);	

$form="
<table style='width:100%'>
<tr>
	<td class=legend>{amavisSpamLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisSpamLover',$user->amavisSpamLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBadHeaderLover}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBadHeaderLover',$user->amavisBadHeaderLover,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassVirusChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassVirusChecks',$user->amavisBypassVirusChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassSpamChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassSpamChecks',$user->amavisBypassSpamChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisBypassHeaderChecks}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisBypassHeaderChecks',$user->amavisBypassHeaderChecks,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamModifiesSubj}:</td>
	<td>" . Field_TRUEFALSE_checkbox_img('amavisSpamModifiesSubj',$user->amavisSpamModifiesSubj,'{enable_disable}')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamTagLevel}:</td>
	<td>" . Field_text('amavisSpamTagLevel',$user->amavisSpamTagLevel,'width:90px')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamTag2Level}:</td>
	<td>" . Field_text('amavisSpamTag2Level',$user->amavisSpamTag2Level,'width:90px')."</td>
</tR>
<tr>
	<td class=legend>{amavisSpamKillLevel}:</td>
	<td>" . Field_text('amavisSpamKillLevel',$user->amavisSpamKillLevel,'width:90px')."</td>
</tR>
<tr>
	<td colspan=2 align='right'><hr>
	". button("{apply}","SaveUserAmavis()")."
	</td>
</tr>
</table>
";


$html="
<H3>{spam_rules}</H3>
<div class=explain>{amavis_user_text}</div>
<hr>
$button_admin
<div id='user-amavis'>$form</div>
";

/*	
amavisSpamLover: FALSE
amavisBadHeaderLover: FALSE
amavisBypassVirusChecks: FALSE
amavisBypassSpamChecks: FALSE

amavisBypassHeaderChecks: FALSE
amavisSpamTagLevel: -999
amavisSpamTag2Level: 5
amavisSpamKillLevel: 5
amavisSpamModifiesSubj: TRUE
*/


echo $tpl->_ENGINE_parse_body($html,'amavis.index.php');
}


function GoBackDefaultAmavis(){
		$tpl=new templates();
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$userid=$_GET["GoBackDefaultAmavis"];
		$user=new user($userid);
		$user->DeleteAmavisConfig();
	
}


function SaveAmavisConfig(){
	$tpl=new templates();
if(isset($_GET["userid"])){
		$users=new usersMenus();
		if(!$users->AllowChangeAntiSpamSettings){echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;}
		$uid=$_GET["userid"];
		unset($_GET["userid"]);
	}
else{
	$uid=$_SESSION["uid"];
}	
	writelogs("uid=$uid",__FUNCTION__,__FILE__);
	$user=new user($uid);	
	while (list ($num, $ligne) = each ($_GET)){
		$user->$num=$ligne;
	}
	
	if($user->SaveAmavisConfig()){
		echo $tpl->_ENGINE_parse_body('{success}');
	}

}

function miltergrelist(){
	$q=new mysql();
	$page=CurrentPageName();
	$tpl=new templates();
	$sql="SELECT uid FROM whitelist_uid_greylist WHERE uid='{$_GET["userid"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	if($ligne["uid"]<>null){$enabled=1;}else{$enabled=0;}
	
	$paragraphe=Paragraphe_switch_img("{disable_greylisting}","{user_greylist_explain}","greylistDisable",$enabled);
	
	$html="
	<div id='greylistDisable-div'>
	$paragraphe
	<div style='width:100%;text-align:right'>". button("{apply}","SaveDisableGreyList()")."</div>
	</div>
	<script>
	var x_SaveDisableGreyList= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				RefreshTab('main_user_as');
			}			
			
	
	function SaveDisableGreyList(){
		var XHR = new XHRConnection();
		XHR.appendData('greylistDisable',document.getElementById('greylistDisable').value);
		XHR.appendData('userid','{$_GET["userid"]}');
		AnimateDiv('greylistDisable-div');
		XHR.sendAndLoad('$page', 'POST',x_SaveDisableGreyList);	
	
	}		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function miltergrelist_save(){
	$delete="DELETE FROM whitelist_uid_greylist WHERE uid='{$_POST["userid"]}'";
	$sql="INSERT INTO whitelist_uid_greylist (uid) VALUES ('{$_POST["userid"]}')";
	if($_POST["greylistDisable"]==0){
		$sql="$delete";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?milter-greylist-reconfigure=yes");
	
	
	
}


?>