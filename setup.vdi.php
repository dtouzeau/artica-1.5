<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.mysql.inc");


$tpl=new templates();
$page=CurrentPageName();
if(isset($_GET["install_status"])){install_status();exit;}
if(isset($_GET["installvdi"])){install_vdi();exit;}
if(posix_getuid()<>0){
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){

		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
}

$button="<input type='button' style='padding:30px;padding-left:180px;padding-right:180px;font-size:16px' OnClick=\"javascript:VDIInstall();\" value='{install_upgrade}'>";
if(isset($_GET["debug"])){debug();exit;}
$installation_lauched=$tpl->javascript_parse_text("{installation_lauched}");
$users=new usersMenus();
if($users->LinuxDistriCode<>"DEBIAN"){
	if($users->LinuxDistriCode<>"UBUNTU"){
		$button="<H2>{THIS_DISTRIBUTION_IS_NOT_SUPPORTED}</H2>";
	}
}



$html="
<div style='background-image:url(img/vdi-bg.png);background-repeat:no-repeat;height:500px;width:100%'>
<H1>{virtual_desktop_infr}</H1>
<div class=explain>{HOWTO_INSTALL_VDI}</div>

<center style='margin:20px'>$button</center>

<div style='width:100%;height:290px;overflow:auto' id='vdi-debug'></div>

<script>
VDItant=0;



function VDIdemarre(){
   VDItant = VDItant+1;
  if(document.getElementById('sucess-vdi')){CacheOff();return;}
   if(!YahooSetupControlOpen()){return;}
	if (VDItant < 10 ) {                           
      setTimeout(\"VDIdemarre()\",2000);
      } else {
               VDItant = 0;
               VDILogs();
               VDIdemarre();
   }
}



var X_VDIInstall= function (obj) {
	var results=obj.responseText;
	document.getElementById('vdi-debug').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	if(results.length>0){alert(results);}
	VDIdemarre();
}	

function VDILogs(){
	LoadAjax('vdi-debug','$page?debug=yes');
}
	
	
function VDIInstall(){
	if(confirm('$installation_lauched')){
			var XHR = new XHRConnection();
			XHR.appendData('installvdi','yes');
			document.getElementById('vdi-debug').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_VDIInstall);
			}		
	}
	VDILogs();
</script>
";


echo $tpl->_ENGINE_parse_body($html);


function debug(){
	$tpl=new templates();
	$users=new usersMenus();
	if($users->VIRTUALBOX_INSTALLED){
		echo "<H2>".$tpl->_ENGINE_parse_body("{success}")."</H2>
		<input type='hidden' id='sucess-vdi' value='1'><hr>".status_service();
		return;
	}
	
	$datas=@file_get_contents("ressources/logs/vdi-install.dbg");
	$datas=explode("\n",$datas);
	if(!is_array($datas)){echo "<center><img src=img/wait_verybig.gif></center>";return;}
	krsort($datas);
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#[0-9\.,]+%#",$line)){continue;}
		$color="black";
		if(preg_match("#Unable to#",$line)){$color="red";}
		if(preg_match("#Unpacking#",$line)){$color="blue";}
		if(preg_match("#Setting up#i",$line)){$color="blue";}
		
		$html=$html."<div><code style='font-size:12px;color:$color'>$line</code></div>\n";
		
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function status_service(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?virtualbox-ini-all-status=yes')));
	$tpl=new templates();
	$status=DAEMON_STATUS_ROUND("APP_VIRTUALBOX_WEBSERVICE",$ini,null,1).
	"<br>".DAEMON_STATUS_ROUND("APP_TFTPD",$ini,null,1)."<br>".DAEMON_STATUS_ROUND("DHCPD",$ini,null,1);
	return $tpl->_ENGINE_parse_body($status);
}

function install_vdi(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?install-vdi=yes");
	
}
