<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.lvm.org.inc');	
	include_once('ressources/class.safebox.inc');
	
	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	

//permissions	
	if(!CheckRights()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").'");';
	}
	
	if(isset($_GET["main"])){USER_SAFEBOX();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["CryptedHomeSize"])){Save();exit;}
	if(isset($_GET["safe-box-page"])){USER_SAFEBOX_PAGE();exit;}
	if(isset($_GET["safe-box-log"])){SAFEBOX_LOGS();exit;}
	if(isset($_GET["mount-safebox"])){MOUNT_SAFEBOX();exit;}
	if(isset($_GET["umount-safebox"])){UMOUNT_SAFEBOX();exit;}
	if(isset($_GET["check-safebox"])){CHECK_SAFEBOX();exit;}
	
	
	js();
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{coffrefort}");
	$page=CurrentPageName();
	$html="
	function UserCoffreStart(){	
		YahooWin3(650,'$page?popup={$_GET["uid"]}','{$_GET["uid"]}');
		
	}
	var x_EnableCoffreSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		UserCoffreStart();
		}			
		
function EnableCoffreSave(){
	var CryptedHomeSize=document.getElementById(\"CryptedHomeSize\").value;
	var CryptedHomePassword=document.getElementById(\"CryptedHomePassword\").value;
	var CryptedHome=document.getElementById(\"CryptedHome\").value;
	var XHR = new XHRConnection();
	XHR.appendData('CryptedHomePassword',CryptedHomePassword);
	XHR.appendData('CryptedHome',CryptedHome);
	XHR.appendData('CryptedHomeSize',CryptedHomeSize);
	XHR.appendData('uid','{$_GET["uid"]}');
	document.getElementById('coffrefortdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_EnableCoffreSave);
	}		
		
	UserCoffreStart();";
	
	echo $html;
	
}

function Save(){
	$user=new user($_GET["uid"]);
	
	if($_GET["CryptedHome"]==1){$_GET["CryptedHome"]="TRUE";}else{$_GET["CryptedHome"]="FALSE";}
	$user->CryptedHome=$_GET["CryptedHome"];
	$user->CryptedHomePassword=$_GET["CryptedHomePassword"];
	$user->CryptedHomeSize=$_GET["CryptedHomeSize"];
	$user->edit_safeBox();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SafeBoxUser=yes&uid={$_GET["uid"]}");
	
	
	
}

function popup(){
	$uid=$_GET["popup"];
	$ct=new user($uid);
	
	if($ct->CryptedHome=="TRUE"){$CryptedHome=1;}else{$CryptedHome=0;}
	
	$enable=Paragraphe_switch_img("{ENABLE_COFFREFORT}","{ENABLE_COFFREFORT_TEXT}","CryptedHome",$CryptedHome,null,450);
	
	
	$html="
	<h1>{coffrefort} $ct->CryptedHome</H1>
	<div id='coffrefortdiv'>
	<table style='width:100%'>
		<tr>
			<td valign='top'><img src='img/safe-box-128.png'></td>
			<td valign='top'>$enable
				<table style='width:100%'>
					<tr>
						<td class=legend>{DepositBoxSize}:</td>
						<td>". Field_text("CryptedHomeSize",$ct->CryptedHomeSize,'width:90px;font-size:13px') ."&nbsp;G</td>
					</tr>				
					<tr>
						<td class=legend>{password}:</td>
						<td>". Field_password("CryptedHomePassword",$ct->CryptedHomePassword,"width:90px;font-size:13px") ."</td>
					</tr>
				</table>
				<div style='text-align:right'><hr>
					". button("{save}","EnableCoffreSave()")."
				</div>
			
			</td>
		</tr>
	</table>
	</div>
	";
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);
}
	
	
	
function CheckRights(){
$usersprivs=new usersMenus();
if($usersprivs->AsAnAdministratorGeneric){return true;}
if($usersprivs->AsOrgAdmin){return true;}
if($usersprivs->AllowAddUsers){return true;}	
	
}	

function USER_SAFEBOX(){
	$page=CurrentPageName();

	$html="
	var SafeBoxCount=0;
	var SafeBoxCounttimerID=0;
	

	function SafeBoxCounter(){
		SafeBoxCount = SafeBoxCount+1;
		if(!document.getElementById(\"safebox\")){return false;}
		if(!YahooUserOpen()){return false;}
		
		if (SafeBoxCount< 10 ) {                           
			SafeBoxCounttimerID =setTimeout(\"SafeBoxCounter()\",2000);
	      } else {
			SaveBoxRefresh();
			SafeBoxCount=0;    
			SafeBoxCounter();                          
	   }
	}
	
	var X_FillSafeBox=function(obj) {
		var results=obj.responseText;
		document.getElementById('safebox').innerHTML=results;
		LoadAjax('safeboxlogs','$page?safe-box-log=yes&uid={$_GET["uid"]}');
	}

	function SaveBoxRefresh(){
		var XHR = new XHRConnection();
		XHR.appendData('safe-box-page','yes');
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.sendAndLoad('$page', 'GET',X_FillSafeBox);	
		}
	
	function SafeBoxMount(){
		var XHR = new XHRConnection();
		XHR.appendData('mount-safebox','yes');
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.sendAndLoad('$page', 'GET',X_FillSafeBox);			
		}
		
	function SafeBoxUMount(){
		var XHR = new XHRConnection();
		XHR.appendData('umount-safebox','yes');
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.sendAndLoad('$page', 'GET',X_FillSafeBox);		
		}
		
	function SafeBoxCheck(){
		var XHR = new XHRConnection();
		XHR.appendData('check-safebox','yes');
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.sendAndLoad('$page', 'GET',X_FillSafeBox);	
	}
	
	if(!document.getElementById(\"safebox\")){
		alert('safebox: not found');
	}else{
		LoadAjax('safebox','$page?safe-box-page=yes&uid={$_GET["uid"]}');
	}
	SafeBoxCounter();	
	";
	
	echo $html;
}

function USER_SAFEBOX_PAGE(){
	$user=new user($_GET["uid"]);
	$safeboxIcon=Paragraphe("safe-box-64.png","{coffrefort}","{coffrefort_create_user}","javascript:Loadjs('domains.edit.user.safebox.php?uid=$user->uid')");
	
	$page=CurrentPageName();
	$table=SafeBoxStatus($_GET["uid"]);
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		$safeboxIcon
		</td>
		<td valign='top'>
	<div id='safeBoxid'>$table</div>
	</td>
	</tr>
	</table>

	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function SafeBoxStatus($uid){
	$safe=new safebox($uid);
	if(!$safe->mounted){
		$button=button("{mount}","SafeBoxMount()");
	}else{
		$button=button("{umount} {coffrefort}","SafeBoxUMount()");
		$info=SafeBoxInfos("/dev/mapper/$uid");
		$fsk=button("{checkfs}","SafeBoxCheck()");
	}
	
	
		
	
	$table="
			<table class='table_form' style='width:100%'>
			<tr>
				<td class=legend>{coffrefort}:</td>
				<td><strong>$safe->crypted_filepath ({$safe->crypted_filesize}G)</td>
			</tr>
			<tr>
				<td class=legend valign='top'>{mounted}:</td>
				<td valign='top'>$info</td>
			</table>
			<div style='width:100%;text-align:right'>
				$fsk&nbsp;&nbsp;&nbsp;$button
			</div>
			<hr>
			<div id='safeboxlogs' style='width:100%;height:320px;overflow:auto;padding:3px;border:1px solid #CCCCCC'></div>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($table);
}

function MOUNT_SAFEBOX(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?mount-safebox=yes&uid={$_GET["uid"]}");
	echo SafeBoxStatus($_GET["uid"]);
}
function UMOUNT_SAFEBOX(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?umount-safebox=yes&uid={$_GET["uid"]}");
	echo SafeBoxStatus($_GET["uid"]);	
}
function CHECK_SAFEBOX(){
$sock=new sockets();
	$sock->getFrameWork("cmd.php?check-safebox=yes&uid={$_GET["uid"]}");
	echo SafeBoxStatus($_GET["uid"]);		
}

function SafeBoxInfos($mapper){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?DiskInfos=$mapper")));
	
	$html="<table style='width:100%'>
	<tr>
		<td>". pourcentage($array["POURC"])."</td>
	</tr>
	<tr>
	<td align='left'>{free}:{$array["FREE"]}&nbsp;|&nbsp;{size}:{$array["SIZE"]}&nbsp;|&nbsp;{used}:{$array["USED"]}<br>
	<code>{$array["MOUNTED"]}</td>
	</tr>
	</table>	
	
	";
	return $html;
	
}

function SAFEBOX_LOGS(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?safebox-logs={$_GET["uid"]}&uid={$_GET["uid"]}")));
	if(count($datas)<2){echo "<center style='margin:10px'><img src=img/wait_verybig.gif></center>";return null;}
	$datas=array_reverse($datas);
	while (list ($num, $ligne) = each ($datas) ){
		echo "<div><code>".htmlentities($ligne)."</code></div>\n";
		
	}
	
}
	
	
?>