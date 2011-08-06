<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.cron.inc');

	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["service-status"])){status_service();exit;}
	if(isset($_GET["repositories"])){repositories();exit;}
	if(isset($_GET["EnableAptMirror"])){save();exit;}
	if(isset($_GET["schedule"])){schedule();exit;}
	if(isset($_GET["mirror-schedule"])){schedule_save();exit;}
	if(isset($_GET["mirror-del-schedule"])){schedule_delete();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["events-list"])){events_list();exit;}
	if(isset($_GET["event-id"])){events_id();exit;}
js();


function js(){
	$page=CurrentPageName();
	
	if(isset($_GET["in-front-ajax"])){
		echo "document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?popup=yes');";
		return;
	}

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{REPOSITORY_DEB_MIRROR}");
	echo "YahooWin2(650,'$page?popup=yes','$title');";
	
}

function status(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$users=new usersMenus();
	$img=$users->LinuxDistriCode."_mirror-128.png";
	
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/$img'></td>
	<td valign='top'>
		<div id='apt-mirror-status'></div>
		<div class=explain>{REPOSITORY_DEB_MIRROR_WHY}</div>
		<div style='text-align:right'>". imgtootltip("refresh-32.png","{refresh}","RefreshAPTMIRSTA()")."</div>
	</td>
	</tr>
	</table>
	
	<script>
		function RefreshAPTMIRSTA(){
			LoadAjax('apt-mirror-status','$page?service-status=yes');
		
		}
		
		RefreshAPTMIRSTA();
	</script>";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["status"]="{status}";
	$array["repositories"]="{repositories}";
	$array["schedule"]="{schedule}";
	$array["events"]="{events}";
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}

	
	echo "
	<div id='mirror_tabs' style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#mirror_tabs').tabs({
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

function schedule(){
		$tpl=new templates();
		$sock=new sockets();
		$page=CurrentPageName();
		$sock->getFrameWork("cmd.php?apt-mirror-schedule=yes");
	
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfigSchedule")));		
		
	$table="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{schedules}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
		$cron=new cron();
		if(is_array($config)){
		while (list ($uid, $schedule) = each ($config) ){
			$schedule_enc=base64_encode($schedule);
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$array_text=$cron->ParseCronCommand("$schedule toto /dev/null",null,1,true);
			$delete=imgtootltip("delete-32.png","{delete}","MirDelCron('$schedule_enc')");
			$table=$table."
			<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=99%><strong style='font-size:14px'>$array_text</strong><br><div style='font-size:10px;text-align:right'><i>$schedule</i></div></td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
		
		}
		
	$table=$table."</tbody></table>";
		
	$html="<div id='schedule-div'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap width=1%>{schedule}:</td>
		<td width=99%>". Field_text("mirror-schedule",null,"font-size:15px;padding:3px;width:100%")."</td>
		<td width=1%>". button("{select}","Loadjs('cron.php?field=mirror-schedule')")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","SaveScheduleMirror()")."</td>
	</tr>
	</table>
	<br>
	$table
	</div>
	
	
	
	<script>
	var x_SaveScheduleMirror= function (obj) {
			RefreshTab('mirror_tabs');
		}
	
	
	function SaveScheduleMirror(){
		var XHR = new XHRConnection();
		XHR.appendData('mirror-schedule',document.getElementById('mirror-schedule').value);
		document.getElementById('schedule-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveScheduleMirror);
	}
	
	function MirDelCron(pattern){
		var XHR = new XHRConnection();
		XHR.appendData('mirror-del-schedule',pattern);
		document.getElementById('schedule-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveScheduleMirror)	
	}

	</script>";
	

	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function events_id(){
			if(!is_numeric($_GET["event-id"])){die("Not a numeric !");}
			$sql="SELECT * FROM debian_mirror_events WHERE ID='{$_GET["event-id"]}'";
			$q=new mysql();	
			
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
			
			
			$tbl=explode("\n",$ligne["text"]);
			
		
	$table="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
		$cron=new cron();
		if(is_array($tbl)){
		while (list ($uid, $line) = each ($tbl) ){	
			if(trim($line)==null){continue;}	
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$table=$table."
			<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=99%><strong style='font-size:14px'>$line</strong></td>
			</tr>
			";
			
		}
		
		}
		
	$table=$table."</tbody></table>";
					
			
			echo "
			<H3>{$ligne["subject"]}</H3>
			<hr>
			<div style='height:450px;overflow:auto'>
			$table
			</div>
			";
			
}

function  events_list(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th colspan=2>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
			$sql="SELECT * FROM debian_mirror_events ORDER BY ID DESC LIMIT 0,150";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){echo("<H3>$sql $q->mysql_error</H3>");}			
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			
			
			$html=$html."
			<tr class=$classtr>
			<td width=1%>". imgtootltip("loupe-32.png","{view}","AptMirrEV({$ligne["ID"]})")."</td>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["zDate"]}</strong></td>
			<td width=99%><strong style='font-size:14px'>{$ligne["subject"]}</strong></td>
			</tr>
			";				
				
				
			}
			
			$events=$tpl->_ENGINE_parse_body("{events}");
	$html=$html."</tbody></table>
	<script>
		function AptMirrEV(id){
			YahooWin3('550','$page?event-id='+id,'$events::'+id);
		
		}
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function schedule_save(){
	$sock=new sockets();
	if(trim($_GET["mirror-schedule"])==null){return;}
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfigSchedule")));	
	$config[$_GET["mirror-schedule"]]=$_GET["mirror-schedule"];
	$sock->SaveConfigFile(base64_encode(serialize($config)),"AptMirrorConfigSchedule");
	$sock->getFrameWork("cmd.php?apt-mirror-schedule=yes");
	
	
}

function schedule_delete(){
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfigSchedule")));	
	unset($config[base64_decode($_GET["mirror-del-schedule"])]);
	$sock->SaveConfigFile(base64_encode(serialize($config)),"AptMirrorConfigSchedule");	
	$sock->getFrameWork("cmd.php?apt-mirror-schedule=yes");
}

function status_service(){
	$ini=new Bs_IniHandler();
	$tpl=new templates();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?apt-mirror-ini-status=yes")));
	
	$AptMirrorRepoSize=$size=$sock->GET_INFO("AptMirrorRepoSize");
	if($AptMirrorRepoSize==null){$AptMirrorRepoSize="{unkown}";}
	
	$AptMirrorRepoSize="<div style='text-align:right;font-size:13px;margin-top:8px'>{current_directory}:<strong>$AptMirrorRepoSize</strong></div>";
	
	echo $tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("APP_APT_MIRROR",$ini,null,0).$AptMirrorRepoSize);	
}


function repositories(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfig")));
	$EnableAptMirror=$sock->GET_INFO("EnableAptMirror");
	if($EnableAptMirror==null){$EnableAptMirror=0;}
	$over="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
	if($config["debian_mirror"]==null){$config["debian_mirror"]="http://ftp.de.debian.org/";}
	if($config["DebianEnabled"]==nul){$config["DebianEnabled"]=1;}
	if($config["UbuntuEnabled"]==nul){$config["UbuntuEnabled"]=1;}
	if($config["UbuntuCountryCode"]==null){$config["UbuntuCountryCode"]="us";}
	if($config["nthreads"]==null){$config["nthreads"]=2;}
	
	
$html="<div id='apt-mirror-div'>
<table style='width:100%'>
<tr>
	<td class=legend>{enable_apt_mirror}:</td>
	<td>". Field_checkbox("EnableAptMirror",1,$EnableAptMirror,"CheckAllMirrors()")."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>{acl_dstdomain}:</td>
	<td>". Field_text("webservername",$config["webservername"],"font-size:13px;padding:3px;width:220px")."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>{roundcube_web_folder}:</td>
	<td>". Field_text("webserverpath",$config["webserverpath"],"font-size:13px;padding:3px;width:290px")."</td>
	<td><input type='button' value='{browse}&nbsp;&raquo;' OnClick=\"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=webserverpath');\"></td>
</tr>
<tr>
	<td class=legend>{download_tasks}:</td>
	<td>". Field_text("nthreads",$config["nthreads"],"font-size:13px;padding:3px;width:290px")."</td>
	<td>". help_icon("{apt_mirror_threads}")."</td>
</tr>
<tr>
<td colspan=3 style='font-size:15px;font-weight:bold;height:45px'><br>{DEBIAN_REPOSITORIES}<br></td>
<tr>
	<td class=legend>{enable_debian_repository}:</td>
	<td>". Field_checkbox("DebianEnabled",1,$config["DebianEnabled"],"CheckDebianMirror()")."</td>
	<td>&nbsp;</td>
</tr>

<tr>
	<td class=legend>{debian_mirror}:</td>
	<td>". Field_text("debian_mirror",$config["debian_mirror"],"font-size:13px;padding:3px;width:220px")."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan=3><div style='font-size:11px;text-decoration:underline;text-align:right' 
		$over 
		OnClick=\"javascript:s_PopUpFull('http://www.debian.org/mirror/list',800,600,'')\">{see_complete_list}</td>
</tr>
<tr>
	<td class=legend>{enable_64bits}:</td>
	<td>". Field_checkbox("Debian64",1,$config["Debian64"])."</td>
	<td>&nbsp;</td>
</tr>
<tr><td colspan=3 style='font-size:15px;font-weight:bold;height:45px;'><br>{UBUNTU_REPOSITORIES}<br></td></tr>
<tr>
	<td class=legend>{enable_ubuntu_repository}:</td>
	<td>". Field_checkbox("UbuntuEnabled",1,$config["UbuntuEnabled"],"CheckUbuntuMirror()")."</td>
	<td>&nbsp;</td>
</tr>


<tr>
	<td class=legend>{country_code}:</td>
	<td>". Field_text("UbuntuCountryCode",$config["UbuntuCountryCode"],"font-size:13px;padding:3px;width:40px")."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>{enable_64bits}:</td>
	<td>". Field_checkbox("Ubuntu64",1,$config["Ubuntu64"])."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>Maverick (10.10):</td>
	<td>". Field_checkbox("maverick",1,$config["maverick"])."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>Lucid (10.04):</td>
	<td>". Field_checkbox("lucid",1,$config["lucid"])."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>karmic (9.10):</td>
	<td>". Field_checkbox("karmic",1,$config["karmic"])."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>Jaunty (9.04):</td>
	<td>". Field_checkbox("jaunty",1,$config["jaunty"])."</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class=legend>Intrepid (8.10):</td>
	<td>". Field_checkbox("intrepid",1,$config["intrepid"])."</td>
	<td>&nbsp;</td>
</tr>		
<tr>
	<td class=legend>Hardy (8.04):</td>
	<td>". Field_checkbox("hardy",1,$config["hardy"])."</td>
	<td>&nbsp;</td>
</tr>	
<tr>
	<td colspan=3 align='right'><hr>". button('{apply}','SaveMirrorSettings()')."</td>
</tr>
</table>
</div>
<script>

	var x_SaveMirrorSettings= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			RefreshTab('mirror_tabs');
			CacheOff();
		}			
		
		function SaveMirrorSettings(){
			var XHR = new XHRConnection();
			XHR.appendData('UbuntuEnabled',CheckBoxValidate('UbuntuEnabled'));
			XHR.appendData('Ubuntu64',CheckBoxValidate('Ubuntu64'));
			XHR.appendData('maverick',CheckBoxValidate('maverick'));
			XHR.appendData('lucid',CheckBoxValidate('lucid'));
			XHR.appendData('karmic',CheckBoxValidate('karmic'));
			XHR.appendData('jaunty',CheckBoxValidate('jaunty'));
			XHR.appendData('intrepid',CheckBoxValidate('intrepid'));
			XHR.appendData('hardy',CheckBoxValidate('hardy'));
			XHR.appendData('UbuntuCountryCode',document.getElementById('UbuntuCountryCode').value);	
			XHR.appendData('nthreads',document.getElementById('nthreads').value);
			
			

			XHR.appendData('DebianEnabled',CheckBoxValidate('DebianEnabled'));
			XHR.appendData('Debian64',CheckBoxValidate('Debian64'));
			XHR.appendData('EnableAptMirror',CheckBoxValidate('EnableAptMirror'));
			XHR.appendData('debian_mirror',document.getElementById('debian_mirror').value);	
			XHR.appendData('webserverpath',document.getElementById('webserverpath').value);
			XHR.appendData('webservername',document.getElementById('webservername').value);	
			document.getElementById('apt-mirror-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveMirrorSettings);
		}



	function CheckUbuntuMirror(){
	
		document.getElementById('UbuntuEnabled').disabled=true;
		document.getElementById('Ubuntu64').disabled=true;
		document.getElementById('UbuntuCountryCode').disabled=true;
		document.getElementById('maverick').disabled=true;
		document.getElementById('lucid').disabled=true;
		document.getElementById('karmic').disabled=true;
		document.getElementById('jaunty').disabled=true;
		document.getElementById('intrepid').disabled=true;
		document.getElementById('hardy').disabled=true;
		
		if(document.getElementById('EnableAptMirror').checked){
			document.getElementById('UbuntuEnabled').disabled=false;
			if(document.getElementById('UbuntuEnabled').checked){
				document.getElementById('Ubuntu64').disabled=false;
				document.getElementById('UbuntuCountryCode').disabled=false;
				document.getElementById('maverick').disabled=false;
				document.getElementById('lucid').disabled=false;
				document.getElementById('karmic').disabled=false;
				document.getElementById('jaunty').disabled=false;
				document.getElementById('intrepid').disabled=false;
				document.getElementById('hardy').disabled=false;			
			}
		
		}
		
	}
	
function CheckDebianMirror(){
	document.getElementById('DebianEnabled').disabled=true;
	document.getElementById('debian_mirror').disabled=true;
	document.getElementById('Debian64').disabled=true;
	if(document.getElementById('EnableAptMirror').checked){
		document.getElementById('DebianEnabled').disabled=false;
		if(document.getElementById('DebianEnabled').checked){
			document.getElementById('debian_mirror').disabled=false;
			document.getElementById('Debian64').disabled=false;	
		}	
	}
}

function CheckAllMirrors(){
document.getElementById('webservername').disabled=true;
document.getElementById('webserverpath').disabled=true;
document.getElementById('nthreads').disabled=true;


if(document.getElementById('EnableAptMirror').checked){
	document.getElementById('webservername').disabled=false;
	document.getElementById('webserverpath').disabled=false;
	document.getElementById('nthreads').disabled=false;
	}
	CheckUbuntuMirror();
	CheckDebianMirror();
}
CheckAllMirrors();	

</script>";

echo $tpl->_ENGINE_parse_body($html);


	
}

function events(){
	$page=CurrentPageName();
	echo "
	<div id='apt-mirror-events' style='height:450px;overflow:auto'>
	<script>
		function RefreshAPTMIREV(){
			LoadAjax('apt-mirror-events','$page?events-list=yes');
		}
	RefreshAPTMIREV();
	</script>
	";
	
	
	
}

function save(){
	$sock=new sockets();
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"AptMirrorConfig");
	$sock->SET_INFO("EnableAptMirror",$_GET["EnableAptMirror"]);	
	
}
