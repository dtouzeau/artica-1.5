<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.lvm.org.inc');
	include_once('ressources/class.os.system.inc');
	
	if(!Isright()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["dev"])){dev();exit;}
	if(isset($_GET["dev-init"])){dev_init();exit;}
	if(isset($_GET["dev-del"])){dev_del();exit;}
	js();
	
	
	
function js(){
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{your_storage}');
	$html="
		var mem_dev='';
		function HD_OU_LOAD(){
			mem_dev='';
			YahooWin('750','$page?popup=yes&ou=$ou','$title')
		}
		
		function HD_OU_DEV_SELECT(){
			LoadAjax('hdou','$page?dev='+document.getElementById('dev').value+'&ou=$ou');
		}
		
		var x_HD_OU_DEV_INIT= function (obj) {
				LoadAjax('hdou','$page?dev='+mem_dev+'&ou=$ou');
		}
		var x_HD_OU_DELETE= function (obj) {
				HD_OU_LOAD();
		}					
		
		function HD_OU_DEV_INIT(dev){
			    mem_dev=dev;
				var XHR = new XHRConnection();
				XHR.appendData('dev-init',dev);
				document.getElementById('hdou').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_HD_OU_DEV_INIT);
		}
		
		function HD_OU_DELETE(dev){
				 mem_dev=dev;
				var XHR = new XHRConnection();
				XHR.appendData('dev-del',dev);
				XHR.appendData('ou','$ou');
				document.getElementById('hdou').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_HD_OU_DELETE);
		}
	
	
	
	HD_OU_LOAD();";
	
	echo $html;
}

function popup(){
	
	$lvm=new lvm_org($_GET["ou"]);
	$array=$lvm->disklist;
	
	while (list ($num, $ligne) = each ($array) ){
		$devs[]=$num;
		$list[$num]=basename($num);
	}
	
	$list[null]="{select}";
	$select=Field_array_Hash($list,"dev",null,"HD_OU_DEV_SELECT()",null,0,"font-size:14px;padding:5px");
$_GET["dev"]=$devs[0];
	$html="
	<table style='width:100%;text-color:black'>
	<tr>
		<td valign='top'><p class=caption>{your_storage_text}</p></td>
		<td valign='top'>$select</td>
	</tr>
	</table>
	<div id='hdou'>". dev(1)."</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function dev($return=0){
	$dev=$_GET["dev"];
	if($dev==null){return;}
	$sock=new sockets();
	$sys=new usb();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?parted-print=$dev")));
	
	
	
	$mapper=$sock->getFrameWork("cmd.php?lvs-mapper=$dev");
	$mount_point=$sys->isMounted($mapper);
	if($mount_point<>null){
		$img=$sock->getFrameWork("cmd.php?philesize-img-path=$mount_point");
		if($img<>null){
			$img="<img src='$img' WIDTH=450 HEIGHT=450>";
		}
	}
	
	$delete_vg=Paragraphe("gd-delete-64.png",'{UNLINK_VOLUME}','{UNLINK_VOLUME_TEXT}',"javascript:HD_OU_DELETE('$dev');");
	$html="
	<H3>". basename($dev)."</H3>
	<table class=table_form>";
	while (list ($num, $ligne) = each ($datas) ){
		$ligne=str_replace($mapper,"",$ligne);
		if(preg_match("#(.+?):(.+)#",$ligne,$re)){
			$html=$html."<tr><td class=legend nowrap>{$re[1]}:</td><td><strong>{$re[2]}</strong></td></tr>";
		}
		
	}
	
	if($mount_point){
		$browse=Paragraphe("64-share.png","{browse}","{browse_disk_text}","javascript:Loadjs('SambaBrowse.php?jdisk=$mapper&mounted=$mount_point&t=&homeDirectory=&no-shares=yes&field=&protocol=');");
		$html=$html."<tr><td class=legend nowrap>{mounted}:</td><td><strong>{yes}</strong></td></tr>";
	}else{
		$html=$html."<tr><td class=legend nowrap>{mounted}:</td><td><strong>{No}</strong></td></tr>";
	}
	$html=$html."<tr><td class=legend nowrap>mapper:</td><td><strong>$mapper</strong></td></tr>";
	$html=$html."</table>";
	
	$page="<table style='width:100%'>
	<tr>
		<td valign='top'>
			$html
			<hr>
			<div style='background-color:white'>
			$img
			</div>
		</td>
		<td valign='top'>
			<table style='width:100%'>
			
			<tr>
				<td>$browse</td>
			</tr>
			<tr>
				<td>$delete_vg</td>
			</tr>
			</table>
		</td>
		</tr>
	</table>
	
	";
	
	$tpl=new templates();
	
	if($return==1){return $tpl->_ENGINE_parse_body($page);}
	echo $tpl->_ENGINE_parse_body($page);
	

	
}

function dev_init(){
	$dev=$_GET["dev-init"];
	$sock=new sockets();
	echo implode("\n",base64_decode($sock->getFrameWork("cmd.php?mkfs=$dev")));
	
}

function dev_del(){
	$dev=$_GET["dev-del"];
	$lvm=new lvm_org($_GET["ou"]);
	$lvm->DeAffectDev($dev);
}
	

function Isright(){
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if(!$users->AsOrgStorageAdministrator){return false;}
	if(isset($_GET["ou"])){if($_SESSION["ou"]<>$_GET["ou"]){return false;}}
	
	return true;
	
	}
	
	
	
	
?>