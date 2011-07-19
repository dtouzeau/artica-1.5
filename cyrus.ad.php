<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	if(isset($_GET["tabs"])){main_tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["CyrusToAD"])){save();exit;}
	if(isset($_GET["logs"])){logs();exit;}
	if(isset($_GET["events"])){logs_list();exit;}
	
js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ad_samba_member}");
	$html="YahooWin2(450,'$page?tabs=yes','$title');";
	echo $html;
	
	
}

function main_tabs(){
	
	$page=CurrentPageName();
	$array["popup"]='{services_settings}';
	$array["logs"]='{events}';	

	
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]="<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
			
		}	
	
	$tab="<div id=main_config_cyrus_ad style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_cyrus_ad').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
	$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($tab);
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$CyrusToAD=$sock->GET_INFO("CyrusToAD");
	if($CyrusToAD==null){$CyrusToAD=0;}
	$array=unserialize(base64_decode($sock->GET_INFO("CyrusToADConfig")));
	$enable=Paragraphe_switch_img("{make_samba_ad}","{ad_samba_member_text}","CyrusToAD",$CyrusToAD,null,330);
	
	$html="
	$enable
	<hr>
	<div class=explain>{ActiveDirectorySettings_text}</div>
	<div id='CyrusToAdDiv'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap style='font-size:13px'>{activedirectory_server}:</td>
		<td>". Field_text("servername",$array["servername"],"font-size:120px;font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap style='font-size:13px'>{activedirectory_domain}:</td>
		<td>". Field_text("domain",$array["domain"],"font-size:120px;font-size:13px;padding:3px")."</td>
	</tr>		
	<tr>
		<td class=legend nowrap style='font-size:13px'>{activedirectory_admin}:</td>
		<td>". Field_text("admin",$array["admin"],"font-size:90px;font-size:13px;padding:3px")."</td>
	</tr>			
	<tr>
		<td class=legend nowrap style='font-size:13px'>{password}:</td>
		<td>". Field_password("password",$array["password"],"font-size:30px;font-size:13px;padding:3px")."</td>
	</tr>	

	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","CyrusToAddSave()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
var x_CyrusToAddSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	RefreshTab('main_config_cyrus_ad');
	}
	
	
	function CyrusToAddSave(){
		var XHR = new XHRConnection();
		XHR.appendData('CyrusToAD',document.getElementById('CyrusToAD').value);
		XHR.appendData('domain',document.getElementById('domain').value);
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('admin',document.getElementById('admin').value);
		XHR.appendData('password',document.getElementById('password').value);
		XHR.sendAndLoad('$page', 'GET',x_CyrusToAddSave);
	
	}		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function save(){
	$sock=new sockets();
	

	$arraySamba["ADDOMAIN"]=$_GET["domain"];
	$arraySamba["ADSERVER"]=$_GET["servername"];
	$arraySamba["ADADMIN"]=$_GET["admin"];
	$arraySamba["PASSWORD"]=$_GET["password"];
	$sock->SaveConfigFile(serialize($arraySamba),"SambaAdInfos");
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"CyrusToADConfig");
	$sock->SET_INFO("CyrusToAD",$_GET["CyrusToAD"]);
	$sock->getFrameWork("cmd.php?cyrus-to-ad=yes");
	
}

function logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("
	<div style='text-align:right'>". imgtootltip("refresh-24.png",'{refresh}',"RefreshLogs()")."</div>
	<div style='height:350px;overflow:auto' id='cyrustoadev'></div>
	
	<script>
		function RefreshLogs(){
			LoadAjax('cyrustoadev','$page?events=yes');
		}
		RefreshLogs();
	</script>
	");
	
}

function logs_list(){
	
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?cyrus-to-ad-events=yes"));
	$tbl=explode("\n",$datas);	
	@krsort($tbl);
	
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>";

	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/fw_bold.gif'>
		<td width=99%'><code>$ligne</code></td>
		</tr>
		";
	}	


	$html=$html."</tbody></table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
