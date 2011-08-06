<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["kav4fs-status"])){service_status();exit;}
	if(isset($_GET["license"])){popup_license();exit;}
	if(isset($_GET["InstallLicenseFile"])){license_add();exit;}
	if(isset($_GET["update"])){update();exit;}
			
	js();
	
	
function js(){

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_KAV4FS}");
	$page=CurrentPageName();
	
	$html="
		function kav4fs_start(){
			YahooWin2('700','$page?popup=yes','$title');
		
		}
		
		
	var x_enable_massmailing_save= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			document.getElementById('enable_massmailing_id').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/mass-mailing-128.png\"></center>';
			YahooWin2Hide();	
		}		
		
		
		function enable_massmailing_save(){
				var XHR = new XHRConnection();
				XHR.appendData('EnableInterfaceMailCampaigns',document.getElementById('EnableInterfaceMailCampaigns').value);
				document.getElementById('enable_massmailing_id').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_enable_massmailing_save);
			
		}		
	
	kav4fs_start()";
	echo $html;
	
}	

function status(){
	$page=CurrentPageName();
	$sock=new sockets();
	$app_infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?kav4fs-infos=yes")));
	
	
	$pattern_date=unserialize(base64_decode($sock->getFrameWork("cmd.php?kaf4fs-pattern=yes")));
	
	if($pattern_date==null){
		$warning_pattern=Paragraphe("warning64.png","{pattern_database_error}","{warning_antivirus_pattern_missing}",null,null,240)."<br>";
	}
	
	if($app_infos["License state"]=="NotInstalled"){
		$warning=Paragraphe("warning64.png","{license_error}","{warning_license_not_installed}",null,null,240)."<br>";
	}
	
	
$html="
<div style='background-image:url(img/kav4fs-top.jpg);margin:-10px;margin-left:-17px;margin-right:-17px;background-repeat:no-repeat;background-color:#005447'>
<table style='margin:-2px;margin-left:-4px;padding:0'>
	<tr>
		<td><img src='img/kav4fs-logo.gif'></td>
		<td style='font-size:12px;color:white;padding-bottom:5px;font-weight:bolder' valign=bottom>{$app_infos["Name"]} {$app_infos["Version"]}</td>
		</tr>
		</table>
</div>
<table style='width:100%;margin-top:15px'>
<tr>
<td valign='top' style='width:240px'>$warning_pattern$warning
<div id='kav4fsState'></div>
</td>
<td valign='top' style='font-size:14px;padding-left:3px;border-left:2px solid #005447'>{APP_KAV4FS_ABOUT}</td>
</tr>
</table>
<script>
	LoadAjax('kav4fsState','$page?kav4fs-status=yes');
</script>


";

echo $html;

}

function service_status(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?kav4fs-ini-status=yes')));
	

	$APP_KAV4FS=DAEMON_STATUS_ROUND("APP_KAV4FS",$ini);
	$APP_KAV4FS_AVS=DAEMON_STATUS_ROUND("APP_KAV4FS_AVS",$ini);	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$APP_KAV4FS<br>$APP_KAV4FS_AVS");
}

	
function popup(){
	
	
	$users=new usersMenus();
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();

	
	$array["status"]='{status}';
	$array["license"]='{license_informations}';
	$array["update"]='{update_settings}';


	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_kav4fs style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_kav4fs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
}


function license_add(){
	$sock=new sockets();
	echo base64_decode($sock->getFrameWork("cmd.php?kaf4fs-install-key=".base64_encode($_GET["InstallLicenseFile"])));
	
}


function popup_license(){
	$page=CurrentPageName();
	
	$html="
		<table style='width:100%'>
		<tr>
			<td colspan=3><H3>{LICENSE_INSTALL}<H3></td>
		<tr>
			<td class=legend style='font-size:13px'>{file_path}:</td>
			<td>". Field_text("license-file","",'width:100%;font-size:13px;padding:3px'). " </td>
			<td width=1%><input type='button' value='{browse}&nbsp;&raquo;' OnClick=\"javascript:Loadjs('tree.php?select-file=key&target-form=license-file');\"></td>
		</tr>
		<tr>
			<td colspan=3 align='right'><hr>". button("{apply}","InstallKav4FsLicense()")."</td>
		</tr>
		</table>
		<div id='license-id'></div>
		<script>
		
		var x_InstallKav4FsLicense=function(obj){
	      var tempvalue=obj.responseText;
	      if(tempvalue.length>3){alert(tempvalue);}
	      document.getElementById('license-id').innerHTML='';
	      
	      }			
		
		function InstallKav4FsLicense(){
				var XHR = new XHRConnection();
				XHR.appendData('InstallLicenseFile',document.getElementById('license-file').value);
				document.getElementById('license-id').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_InstallKav4FsLicense);		
			}
		
		</script>
		
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function update(){
	$sock=new sockets();
	$tasks=unserialize(base64_decode($sock->getFrameWork("cmd.php?kav4fs-tasks=yes")));
	$updates_tasks=$tasks["CLASSES"]["Update"];
	
		$html="<table style='width:100%'>
			<tr>
			<th>&nbsp;</th>
			<th>{task_name}</th>
			<th>{state}</th>
			</tr>
		";	
	
	if(is_array($updates_tasks)){

	
		
		
		
		while (list ($num, $INDEX) = each ($updates_tasks) ){
			
			if($tasks["LIST"][$INDEX]["STATE"]=="Stopped"){$img="status_service_stop.jpg";}
			if($tasks["LIST"][$INDEX]["STATE"]=="Started"){$img="status_service_run.png";}	
			
			$html=$html."<tr ". CellRollOver().">
			<td width=1%><img src='img/task-table.jpg'></td>
			<td style='font-size:13px;font-weight:bold'>{$tasks["LIST"][$INDEX]["NAME"]}</td>
			<td width=1%>". imgtootltip($img,"{$tasks["LIST"][$INDEX]["STATE"]}")."</td>
			</tr>
			";
			
		}
	}
	
	
	$html=$html."</table>";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

?>