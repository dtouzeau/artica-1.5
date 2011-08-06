<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["antispam"])){config_antispam();exit;}
	if(isset($_GET["notification"])){config_notification();exit;}
	if(isset($_GET["sa_tag2_level_deflt"])){saveconf();exit;}
	if(isset($_GET["mailfrom_notify_admin"])){saveconf();exit;}
	
	
	js();

	
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_AMAVISD_NEW}");
	$hostname=$_GET["hostname"];
	$ou=$_GET["ou"];
	$html="
		function PostfixMultiLoadAmavis(){
			YahooWin3(650,'$page?popup=yes&hostname=$hostname&ou=$ou','$title');	
		
		}
	
	
	PostfixMultiLoadAmavis()";
	echo $html;
	
	}
	
function popup(){
	$hostname=$_GET["hostname"];
	$ou=$_GET["ou"];
	$tpl=new templates();
	$page=CurrentPageName();
	$array["antispam"]='{spamassassin}';
	$array["notification"]='{smtp_notification}';
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname=$hostname&ou=$ou\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_amavis_multi style='width:100%;height:520px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_amavis_multi').tabs({
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

function config_antispam(){
	$hostname=$_GET["hostname"];
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$main=new maincf_multi($_GET["hostname"],base64_decode($_GET["ou"]));
	$conf=unserialize(base64_decode($main->GET_BIGDATA("amavis_config")));
	$users=new usersMenus();
	$tpl=new templates();

	$sa_quarantine_cutoff_level=$tpl->_ENGINE_parse_body('{sa_quarantine_cutoff_level}','spamassassin.index.php');
	$sa_tag3_level_defltl=$tpl->_ENGINE_parse_body('{sa_tag3_level_deflt}','spamassassin.index.php');


if(strlen($sa_quarantine_cutoff_level)>70){
	$sa_quarantine_cutoff_level=texttooltip(substr($sa_quarantine_cutoff_level,0,67)."..:",$sa_quarantine_cutoff_level,null,null,1);
}

if(strlen($sa_tag3_level_defltl)>70){
	$sa_tag3_level_defltl=texttooltip(substr($sa_tag3_level_defltl,0,67)."...:",$sa_tag3_level_defltl,null,null,1);
}

$html="
<div id='amavisspamassassin_multi'>
	<p style='font-size:14px'>{spamassassin_text}</p>
	<table style='width:100%'>	
		<tr>
			<td class=legend nowrap>{sa_tag2_level_deflt}:</td>
			<td width=1%>". Field_text('sa_tag2_level_deflt',$conf["sa_tag2_level_deflt"],'width:90px')."</td>
			<td>" . Field_numeric_checkbox_img('spam_quarantine_spammy',$conf["spam_quarantine_spammy"],'{spam_quarantine_spammy}') . "</td>			
		</tr>
		<tr>
			<td class=legend nowrap>$sa_tag3_level_defltl</td>
			<td width=1%>". Field_text('sa_tag3_level_deflt',$conf["sa_tag3_level_deflt"],'width:90px')."</td>
			<td>" . Field_numeric_checkbox_img('spam_quarantine_spammy2',$conf["spam_quarantine_spammy2"],'{spam_quarantine_spammy}') . "</td>
		</tr>	
		<tr>
			<td class=legend nowrap>{sa_kill_level_deflt}:</td>
			<td width=1%>". Field_text('sa_kill_level_deflt',$conf["sa_kill_level_deflt"],'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>	
		<tr><td colspan=3><hr></td></tR>
		<tr>
			<td class=legend nowrap>{sa_dsn_cutoff_level}:</td>
			<td width=1%>". Field_text('sa_dsn_cutoff_level',$conf["sa_dsn_cutoff_level"],'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class=legend nowrap>$sa_quarantine_cutoff_level</td>
			<td width=1%>". Field_text('sa_quarantine_cutoff_level',$conf["sa_quarantine_cutoff_level"],'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>	
	</table>
	<hr>
		<table style='width:100%'>	
				<tr>
					<td class=legend nowrap>{spam_subject_tag_maps}:</td>
					<td width=1%>" . Field_yesno_checkbox_img('spam_subject_tag_maps_enable',$conf["spam_subject_tag_maps_enable"],'{enable_disable}')."</td>
					<td width=1%>". Field_text('spam_subject_tag_maps',$conf["spam_subject_tag_maps"],'width:190px')."</td>
					<td class=legend nowrap>{score}:</td>
					<td>" . Field_text("sa_tag_level_deflt",$conf["sa_tag_level_deflt"],'width:33px')."</td>
				</tr>	
				<tr>
					<td class=legend nowrap>{spam_subject_tag2_maps}:</td>
					<td>&nbsp;</td>
					<td width=1%>". Field_text('spam_subject_tag2_maps',$conf["spam_subject_tag2_maps"],'width:190px')."</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>	
				
			<tr>
				<td colspan=5 align='right'>
				
				<hr>". button("{apply}","SaveAmavisSpamAssassinMulti()")."
				</td>
			</tr>	
		</table>		
	</div>
	
	<script>
	
	var x_SaveAmavisSpamAssassinMulti= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_config_amavis_multi');
		}			
	
		function SaveAmavisSpamAssassinMulti(){
	      var XHR = new XHRConnection();
	      XHR.appendData('sa_tag2_level_deflt',document.getElementById('sa_tag2_level_deflt').value);
	      XHR.appendData('spam_quarantine_spammy',document.getElementById('spam_quarantine_spammy').value);
	      XHR.appendData('sa_tag3_level_deflt',document.getElementById('sa_tag3_level_deflt').value);
	      XHR.appendData('spam_quarantine_spammy2',document.getElementById('spam_quarantine_spammy2').value);
	      XHR.appendData('sa_kill_level_deflt',document.getElementById('sa_kill_level_deflt').value);
	      XHR.appendData('sa_dsn_cutoff_level',document.getElementById('sa_dsn_cutoff_level').value);
	      XHR.appendData('sa_quarantine_cutoff_level',document.getElementById('sa_quarantine_cutoff_level').value);
	      XHR.appendData('spam_subject_tag_maps_enable',document.getElementById('spam_subject_tag_maps_enable').value);
	      XHR.appendData('spam_subject_tag_maps',document.getElementById('spam_subject_tag_maps').value);
	      XHR.appendData('sa_tag_level_deflt',document.getElementById('sa_tag_level_deflt').value);
	      XHR.appendData('spam_subject_tag2_maps',document.getElementById('spam_subject_tag2_maps').value);
		  XHR.appendData('hostname','$hostname');
		  XHR.appendData('ou','$ou');     
		  document.getElementById('amavisspamassassin_multi').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		  XHR.sendAndLoad('$page', 'GET',x_SaveAmavisSpamAssassinMulti);
		}
	
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html,'spamassassin.index.php');	
	}
	
function saveconf(){
		$main=new maincf_multi($_GET["hostname"],base64_decode($_GET["ou"]));
		$conf=unserialize(base64_decode($main->GET_BIGDATA("amavis_config")));
		if(!is_array($conf)){$conf=array();}
	
		while (list ($key, $val) = each ($_GET) ){
			$conf[$key]=$val;
		}
		$main->SET_BIGDATA("amavis_config",base64_encode(serialize($conf)));
		
}

function config_notification(){
	
	$page=CurrentPageName();
	$hostname=$_GET["hostname"];
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$main=new maincf_multi($_GET["hostname"],base64_decode($_GET["ou"]));
	$conf=unserialize(base64_decode($main->GET_BIGDATA("amavis_config")));
	$users=new usersMenus();
	$tpl=new templates();
	
	$mailfrom_notify_admin=$tpl->_ENGINE_parse_body("{mailfrom_notify_admin}:");
	$mailfrom_notify_recip=$tpl->_ENGINE_parse_body("{mailfrom_notify_recip}:");
	$mailfrom_notify_spamadmin=$tpl->_ENGINE_parse_body("{mailfrom_notify_spamadmin}:");
	$mailfrom_notify=$tpl->_ENGINE_parse_body("{mailfrom_notify}:");
	$virus_admin=$tpl->_ENGINE_parse_body("{virus_admin}:");
	$warnbadhsender=$tpl->_ENGINE_parse_body("{warnbadhsender}:");
	$warnbadhrecip=$tpl->_ENGINE_parse_body("{warnbadhrecip}:");
	$warnvirusrecip=$tpl->_ENGINE_parse_body("{warnvirusrecip}:");
	$warnbannedrecip=$tpl->_ENGINE_parse_body("{warnbannedrecip}:");
	
	
	$sytrip_text=50;
	$sytrip_text_=$sytrip_text-3;
	
	
	if(strlen($mailfrom_notify_admin)>$sytrip_text){$mailfrom_notify_admin=texttooltip(substr($mailfrom_notify_admin,$sytrip_text_)."...:",$mailfrom_notify_admin);}
	if(strlen($mailfrom_notify_recip)>$sytrip_text){$mailfrom_notify_recip=texttooltip(substr($mailfrom_notify_recip,0,$sytrip_text_)."...:",$mailfrom_notify_recip);}
	if(strlen($mailfrom_notify_spamadmin)>$sytrip_text){$mailfrom_notify_spamadmin=texttooltip(substr($mailfrom_notify_spamadmin,0,$sytrip_text_)."...:",$mailfrom_notify_spamadmin);}
	if(strlen($mailfrom_notify)>$sytrip_text){$mailfrom_notify=texttooltip(substr($mailfrom_notify,0,$sytrip_text_)."...:",$mailfrom_notify);}
	if(strlen($virus_admin)>$sytrip_text){$virus_admin=texttooltip(substr($virus_admin,0,$sytrip_text_)."...:",$virus_admin);}
	if(strlen($warnbadhsender)>$sytrip_text){$warnbadhsender=texttooltip(substr($warnbadhsender,0,$sytrip_text_)."...:",$warnbadhsender);}
	if(strlen($warnbadhrecip)>$sytrip_text){$warnbadhrecip=texttooltip(substr($warnbadhrecip,0,$sytrip_text_)."...:",$warnbadhrecip);}
	if(strlen($warnvirusrecip)>$sytrip_text){$warnvirusrecip=texttooltip(substr($warnvirusrecip,0,$sytrip_text_)."...:",$warnvirusrecip);}
	if(strlen($warnbannedrecip)>$sytrip_text){$warnbannedrecip=texttooltip(substr($warnbannedrecip,0,$sytrip_text_)."...:",$warnbannedrecip);}
	

if($conf["virus_admin"]=="undef"){$conf["virus_admin"]=null;}

$html="
	
	<p style='font-size:13px'>{notification_text}</p>
	<div style='amavis_notifs'>
	<table style='width:100%'>	
	<tr>
		<td colspan=2><H3>{mailfrom_notify}:</h3></td>
	</tR>
	
	
	
		<tr>
			<td class=legend nowrap>$mailfrom_notify_admin</td>
			<td width=1%>". Field_text('mailfrom_notify_admin',$conf["mailfrom_notify_admin"],'width:180px')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>$mailfrom_notify_recip</td>
			<td width=1%>". Field_text('mailfrom_notify_recip',$conf["mailfrom_notify_recip"],'width:180px')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>$mailfrom_notify_spamadmin</td>
			<td width=1%>". Field_text('mailfrom_notify_spamadmin',$conf["mailfrom_notify_spamadmin"],'width:180px')."</td>
		</tr>				
	</tr>
	<tr>
		<td colspan=2><H3 style='margin-top:5px'>{smtp_notification}:</h3></td>
	</tR>	
		<tr>
			<td class=legend nowrap>$virus_admin</td>
			<td width=1%>". Field_text('virus_admin',$conf["virus_admin"],'width:180px')."</td>
		</tr>	
	<tr>
		<td class=legend nowrap>$warnbadhsender</td>
		<td width=1%>". Field_numeric_checkbox_img('warnbadhsender',$conf["warnbadhsender"],'{enable_disable}')."</td>
	</tr>
	<tr>
		<td class=legend nowrap>$warnbadhrecip</td>
		<td width=1%>". Field_numeric_checkbox_img('warnbadhrecip',$conf["warnbadhrecip"],'{enable_disable}')."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>$warnvirusrecip</td>
		<td width=1%>". Field_numeric_checkbox_img('warnvirusrecip',$conf["warnvirusrecip"],'{enable_disable}')."</td>
	</tr>		
	<tr>
		<td class=legend nowrap>$warnbannedrecip</td>
		<td width=1%>". Field_numeric_checkbox_img('warnbannedrecip',$conf["warnbannedrecip"],'{enable_disable}')."</td>
	</tr>		
	<tr>
		<td colspan=2 align='right'>
		<hr>
			". button("{apply}","amavis_multi_notifs_save()")."
	</tr>	
	</table>
	</div>
	
	<script>
	var x_amavis_multi_notifs_save= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_config_amavis_multi');
		}
	
	
		function amavis_multi_notifs_save(){
	      var XHR = new XHRConnection();
	      XHR.appendData('mailfrom_notify_admin',document.getElementById('mailfrom_notify_admin').value);
	      XHR.appendData('mailfrom_notify_spamadmin',document.getElementById('mailfrom_notify_spamadmin').value);
	      XHR.appendData('virus_admin',document.getElementById('virus_admin').value);
	      XHR.appendData('warnbadhsender',document.getElementById('warnbadhsender').value);
	      XHR.appendData('warnbadhrecip',document.getElementById('warnbadhrecip').value);
	      XHR.appendData('warnvirusrecip',document.getElementById('warnvirusrecip').value);
	      XHR.appendData('warnbannedrecip',document.getElementById('warnbannedrecip').value);
		  XHR.appendData('hostname','$hostname');
		  XHR.appendData('ou','$ou');     
		  document.getElementById('amavis_notifs').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		  XHR.sendAndLoad('$page', 'GET',x_amavis_multi_notifs_save);		
		
		}
	";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}



?>