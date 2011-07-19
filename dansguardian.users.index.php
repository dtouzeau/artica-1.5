<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.dansguardian.inc');

if(isset($_POST["userid"])){checkprivs();exit;}
if(isset($_POST["testing-connection"])){TEST_LOCAL_CONNECTION();exit;}

start();




function start(){
	$uri=$_GET["uri"];
	$page=CurrentPageName();
	$tpl=new templates();
	$success=$tpl->_ENGINE_parse_body('{success}');
	$title=$tpl->_ENGINE_parse_body('{unlock_web_site}');
	$dans=new dansguardian();
	$port=trim($dans->Master_array["filterport"]);	
	$success=
	
	$form="
	<script>
	var timeout=0;
	function releasetouri(){
		document.getElementById('mmwait').innerHTML=\"\";
		document.location.href=\"{$_GET["uri"]}\";
	}
	
	function CheckUnlockForm(e){
		if(checkEnter(e)){ReleaseWebSite();}
	
		}
	
	
	var x_ReleaseWebSite= function (obj) {
		var text=obj.responseText;
		
		if(text=='SUCCESS'){
			alert('$success');
			timeout=0;
			setTimeout(\"testingConnection()\",1000);
			return false;
			}
			
		alert(text);
		document.getElementById('mmwait').innerHTML=\"\";
	}
	
	
var x_testingConnection= function (obj) {
		var text=obj.responseText;
		if(text=='SUCCESS'){
			setTimeout(\"releasetouri()\",1000);
			return;
		}
		
		setTimeout(\"testingConnection()\",1000);
}

	function testingConnection(){
		timeout=timeout+1;
		if(timeout>20){
			releasetouri();
			return;
			}	
		var XHR = new XHRConnection();
		XHR.appendData('testing-connection','$port');
		XHR.sendAndLoad('$page', 'POST',x_testingConnection);
	
	}
	
	function ReleaseWebSite(){
		var password=document.getElementById('password').value;
		var user=document.getElementById('username').value;
		var XHR = new XHRConnection();
		XHR.appendData('password',password);
		XHR.appendData('userid',user);
		XHR.appendData('uri','$uri');
		document.getElementById('mmwait').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'POST',x_ReleaseWebSite);	
	}
	</script>
	
	<span id='mmwait'></span>
	<center>
	<div style='width:700px;border:2px solid red;margin-top:100px;margin-left:100px;padding:20px;background-color:red'>
	<div style='width:600px;border:2px solid red;padding:20px;background-color:white'>
		<div style='font-size:18px;margin:10px;text-align:center'>$uri</div>
	<div style='width:300px;margin-top:50px;margin-bottom:50px'>
	". RoundedLightYellow("
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{username}:</td>
		<td>" . Field_text('username',null,'width:120px',null,null,null,null,"CheckUnlockForm(event)")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{password}:</td>
		<td>" . Field_password('password',null,'width:120px',null,null,null,null,"CheckUnlockForm(event)")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:ReleaseWebSite();\" value='{unlock}&nbsp;&raquo;'></td>
	</tr>
	</table>
	")."
	</div>
	</div>
	</div>
	</center>
	
	
	";
	

	$tpl=new template_users($title,$form,0,1,1,0);
	$tpl->_BuildPopUp($form,$title);
	echo $tpl->web_page;
	
	
	
	
}

function checkprivs(){
$_POST["userid"]=trim($_POST["userid"]);	
include("ressources/settings.inc");
$socks=new sockets();

	
	
	if(strtolower($_POST["userid"])==strtolower($_GLOBAL["ldap_admin"])){
		if($_POST["password"]<>$_GLOBAL["ldap_password"]){
			die("bad password");
		}
		
			$dans=new dansguardian_rules(null,1);
			$dans->Add_exceptionsitelist(1,$_POST["uri"]);
			AddEvents("Manager",$_POST["uri"]);
			$sock=new sockets();
			$sock->getFrameWork("reload-dansguardian");
			die("SUCCESS");		
		
	}
	
		$ldap=new clladp();
		writelogs('This is not Global admin, so test user...',__FUNCTION__,__FILE__);
		$hash=$ldap->UserDatas($_POST["userid"]);
		$userPassword=$hash["userPassword"];
		if(trim($hash["uid"])==null){
			writelogs('Unable to get user infos abort',__FUNCTION__,__FILE__);
			die("Unknown user");
		}
	
	
	if(trim($_POST["password"])==trim($userPassword)){
		$users=new usersMenus($ldap->_Get_privileges_userid($_POST["userid"]));
		$priv_array=$users->_ParsePrivieleges($ldap->_Get_privileges_userid($_POST["userid"]));
		$users->_TranslateRights($priv_array);
		if($users->AllowDansGuardianBanned){
			$dans=new dansguardian_rules(null,1);
			$dans->Add_exceptionsitelist(1,$_POST["uri"]);
			$sock=new sockets();
			AddEvents($_POST["userid"],$_POST["uri"]);
			$sock->getFrameWork("cmd.php?reload-dansguardian");
			die("SUCCESS");
			
			}
	}else{
		die("BAD PASSWORD");
	}
	die("No privileges");
}


function AddEvents($uid,$uri){
	include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
	$date=date("Y-m-d H:i:s");
	$sql="INSERT INTO dansguardian_whitelists (zDate,uri,uid) VALUES('$date','$uri','$uid')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	}
	
function TEST_LOCAL_CONNECTION(){
	$fp = @fsockopen("127.0.0.1", $_POST["testing-connection"], $errno, $errstr, 1);
		
	if(!$fp){
		echo "NO";
		return false;	
	}

	fclose($fp);
	echo "SUCCESS";
	}


?>