<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.spamassassin.inc');
	$user=new usersMenus();
		if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["SaveGeneralSettings"])){SaveConf();exit;}
	if(isset($_GET["rewrite_headers"])){rewrite_headers();exit;}
	if(isset($_GET["add_headers"])){add_headers();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["SpamassAddTrustedNetwork"])){Save_Trusted_Networks();exit;}
	if(isset($_GET["SpamassDelTrustedNetwork"])){Delete_Trusted_Networks();exit;}
	if(isset($_GET["salearn-schedule-js"])){salearn_schedule_js();exit;}
	if(isset($_GET["salearn-schedule-popup"])){salearn_schedule_popup();exit;}
	if(isset($_GET["SalearnSchedule"])){salearn_schedule_save();exit;}
	
	if(isset($_GET["popup-spamass-scores-behavior"])){scores_behavior();exit;}
	if(isset($_GET["popup-spamass-rewrite-headers"])){rewrite_headers_form();exit;}
	if(isset($_GET["popup-spamass-add-headers"])){add_headers_form();exit;}
	
	if(isset($_GET["popup-spamass-check"])){popup_spamass_check();exit;}
	if(isset($_GET["popup-spamass-check-perform"])){popup_spamass_check_perform();exit;}
	
	js();
	
	
function js(){

	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_SPAMASSASSIN}");
	$scores_behavior=$tpl->_ENGINE_parse_body("{scores_behavior}");
	$title1=$tpl->_ENGINE_parse_body("{title1}");
	$spamassassin_check_config=$tpl->_ENGINE_parse_body("{spamassassin_check_config}");
	$html="
	
		function SPAMASS_LOADP(){
			YahooWin2(750,'$page?popup=yes','$title');
		
		}
		var refreshTrustedNet=function(obj){
		RefreshTab('main_config_spamass');
      }


function SpamassAddTrustedNetwork(){
        var net;
        net=prompt(document.getElementById('addtrustednet').value);
        if(net){
             var XHR = new XHRConnection();
             XHR.appendData('SpamassAddTrustedNetwork',net);
	     XHR.sendAndLoad('spamassassin.index.php', 'GET',refreshTrustedNet);   
        }
}

function SpamassDelTrustedNetwork(index){
       	var XHR = new XHRConnection();
        XHR.appendData('SpamassDelTrustedNetwork',index);
	    XHR.sendAndLoad('spamassassin.index.php', 'GET',refreshTrustedNet);      
        }		
function spamass_scores_behavior(){
	YahooWin4(550,'$page?popup-spamass-scores-behavior=yes','$scores_behavior');
}
function spamass_rewrite_headers(){
	YahooWin4(370,'$page?popup-spamass-rewrite-headers=yes','$title1');
}
function spamass_add_headers(){
	YahooWin4(550,'$page?popup-spamass-add-headers=yes','$title1');
}


var X_SpamassinScoreBehavior= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		spamass_scores_behavior();
	}		
function SpamassinScoreBehavior(){
		var XHR = new XHRConnection();
		XHR.appendData('SaveGeneralSettings','yes');
		XHR.appendData('report_safe',document.getElementById('report_safe').value);
		XHR.appendData('use_bayes',document.getElementById('use_bayes').value);
		XHR.appendData('bayes_auto_learn',document.getElementById('bayes_auto_learn').value);
		XHR.appendData('required_score',document.getElementById('required_score').value);
		XHR.appendData('block_with_required_score',document.getElementById('block_with_required_score').value);
		document.getElementById('SpamassinScoreBehaviorDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_SpamassinScoreBehavior);
		}
		
function SpamAssassinCheckService(){
	YahooWin4(750,'$page?popup-spamass-check=yes','$spamassassin_check_config');

}
		
	
	SPAMASS_LOADP()";
	
echo $html;	
}

function popup_spamass_check(){
	$page=CurrentPageName();
	$html="
	
	<div id='popup_spamass_check_div' style='width:100%;height:400px;overflow:auto'></div>
	
	
	
	<script>
		LoadAjax('popup_spamass_check_div','$page?popup-spamass-check-perform=yes');
	</script>
	";
	
	echo $html;
}

function popup_spamass_check_perform(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?spamass-check=yes")));
	
	
	while (list ($num, $ligne) = each ($datas) ){
		$color="#000000";
		if(preg_match("#error#i",$ligne)){
			$color="red";
		}
		
	if(preg_match("#failed#i",$ligne)){
			$color="red";
		}		
		
		
		
		if(preg_match("#module installed#",$ligne)){
			$color="black;font-weight:bolder";
		}
		
		
		$html[]="<div><code style='font-size:10px;color:$color'>". htmlspecialchars($ligne)."</code></div>";
		
	}
	
	echo implode("\n",$html);
	
	
}


function popup(){

	
	
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["settings"]='{settings}';
	$array["trusted_networks"]='{trusted_networks}';
	$array["plugins"]='{plugins}';
	$array["conf"]='{config}';


	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_spamass style='width:100%;height:630px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_spamass').tabs({
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

	
function salearn_schedule_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{salearnschedule}");
	

	
	$html="
	
	function LoadSaLearn(){
		YahooWin2(500,'$page?salearn-schedule-popup=yes','$title');
	
	}
	
	LoadSaLearn();
	
	var x_SaLearnSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		LoadSaLearn();
	}	
		
	
	function SaLearnSave(){
		var XHR = new XHRConnection();
		XHR.appendData('SalearnSchedule',document.getElementById('SalearnSchedule').value);
		XHR.sendAndLoad('$page', 'GET',x_SaLearnSave);
		}
	
	";
	
	echo $html;
}

function salearn_schedule_save(){
	$SalearnSchedule=$_GET["SalearnSchedule"];
	if($SalearnSchedule>59){
		$SalearnSchedule=round($SalearnSchedule/60)."h";
	}
	
	$sock=new sockets();
	$sock->SET_INFO("SalearnSchedule",$SalearnSchedule);
	$sock->getfile("RestartDaemon");
	$tpl=new templates();
echo	$tpl->_ENGINE_parse_body("{success}\n{salearnschedule} {every}: " .$SalearnSchedule."\n{salearnschedule_text}");
	
}



function salearn_schedule_popup(){
	$sock=new sockets();
	$SalearnSchedule=$sock->GET_INFO("SalearnSchedule");
	if($SalearnSchedule==null){$SalearnSchedule="2h";}
	
	for($i=1;$i<12;$i++){
		$t=$i*60;
		$array[$t]=$t;
		
	}	
	
	if(preg_match("#([0-9]+)h#",$SalearnSchedule,$re)){
		$SalearnSchedule=$re[1]*60;
	}
	
	
	$html="
	<H1>{salearnschedule}</H1>
	<p class=caption>{salearnschedule_text}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{salearnschedule}:</td>
		<td>". Field_array_Hash($array,"SalearnSchedule",$SalearnSchedule)."&nbsp;{minutes}</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' Onclick=\"javascript:SaLearnSave();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

	
function main_page(){
	$page=CurrentPageName();

	
	$html=
	"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
	}
</script>	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_spam-assassin.jpg'>	<p class=caption>{about}</p></td>
	<td valign='top'><div id='services_status'>". main_status() . "</div></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();LoadAjax('main_config','$page?main=yes');</script>
	
	";
	
	$cfg["JS"][]='js/spamassassin.js';
	$tpl=new template_users('{APP_SPAMASSASSIN}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	




function main_switch(){
	
	switch ($_GET["main"]) {
		case "status":main_status();exit;break;
		case "settings":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "trusted_networks":main_trusted_networks();exit;break;
		case "trusted_networks_list": echo main_trusted_networks_list();exit;break;
		case "conf":echo main_conf();exit;break;
		case "plugins";echo main_plugins();exit;
		default:
			break;
	}
	
	
}	

function main_status_milter(){
if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$sock=new sockets();
	
	if($users->MimeDefangEnabled==1){
		include_once('ressources/class.mimedefang.inc');
		$ini->loadString($sock->getfile('mimedefangstatus',$_GET["hostname"]));	
		$mime=new mimedefang();
		
		if($mime->ScriptConf_array["BUILD"]["ENABLE_SA"]==1){
			$title="{managed_by_mimedefang}";
			$milter_status=DAEMON_STATUS_ROUND('MIMEDEFANG',$ini,$title);
			
			
		}else{
			$title="{managed_by_mimedefang_not_enabled}";
			$milter_status=DAEMON_STATUS_ROUND('MIMEDEFANG',$ini,$title);		
		}
		
		
		
	}else{
	
	$ini->loadString($sock->getfile('MILTER_SPAMASS_STATUS',$_GET["hostname"]));	
	$milter_status=DAEMON_STATUS_ROUND('SPAMASS_MILTER',$ini);
	
	
	}
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($milter_status);	
	
}

function main_status_core(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->getfile('CORE_SPAMASS_STATUS',$_GET["hostname"]));	
	$status=DAEMON_STATUS_ROUND('SPAMASSASSIN',$ini);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);	
	
}


function main_status(){
	
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_spam-assassin.jpg'>	<div class=explain>{spamassassin_about}</div></td>
	<td valign='top'><div id='services_status'>". main_status_milter() . "<br>" . main_status_core() . "</div></td>
	</tr>
	</table>
	";
	$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function main_config(){
	
	$settings=Paragraphe("64-settings.png","{scores_behavior}","{scores_behavior_text}","javascript:spamass_scores_behavior()");
	$rewrite_header=Paragraphe("icon_settings-64.png","{rewrite_header}","{spamass_rewrite_header}","javascript:spamass_rewrite_headers()");
	$add_header=Paragraphe("icon_settings-64.png","{add_header}","{spamass_add_header}","javascript:spamass_add_headers()");
	$backsquatter=Paragraphe("bug-warning-64.png","{Virus_Bounce_Ruleset}","{Virus_Bounce_Ruleset_text}",
	"javascript:Loadjs('spamassassin.backscatter.php')");
	
	$service_check=Paragraphe("service-check-64.png","{spamassassin_check_config}","{spamassassin_check_config_text}",
	"javascript:SpamAssassinCheckService()");
	
	
	$DecodeShortURLs=Paragraphe("spider-restrict-64.png","{DecodeShortURLs}",
	"{spamassassin_DecodeShortURLs_text}",
	"javascript:Loadjs('spamassassin.DecodeShortURLs.php')");
	
	
	
	$tr[]=$settings;
	$tr[]=$rewrite_header;
	$tr[]=$add_header;
	$tr[]=$mailspy;
	$tr[]=$backsquatter;
	$tr[]=$DecodeShortURLs;
	$tr[]=$service_check;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
	
	$html=implode("\n",$tables);	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function scores_behavior(){
	$users=new usersMenus();
	$spam=new spamassassin();
	$page=CurrentPageName();	
	$sock=new sockets();
	if($users->AMAVIS_INSTALLED){
		$EnableAmavisDaemon=$sock->GET_INFO("EnableAmavisDaemon");
		$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
		if($EnablePostfixMultiInstance==1){$EnableAmavisDaemon=1;}
	}
	
	if($EnableAmavisDaemon==1){
		$js_load="SpamAssassinDisableScores()";
		$disable_explain_spamassin="{disabled_amavis_enabled}";
	}
	
	
$html="
<div id='SpamassinScoreBehaviorDiv'>
	<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top' class=legend>{report_safe}:</strong></td>
			<td valign='top'>" . Field_TRUEFALSE_checkbox_img('report_safe',$spam->main_array["report_safe"])."</td>
			<td valign='top'>" . help_icon("{report_safe_text}")."</td>
			</tr>
			
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{use_bayes}:</strong></td>
			<td valign='top'>" . Field_TRUEFALSE_checkbox_img('use_bayes',$spam->main_array["use_bayes"])."</td>
			<td valign='top'>&nbsp;</td>
		</tr>	
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{auto_learn}:</strong></td>
			<td valign='top'>" . Field_TRUEFALSE_checkbox_img('bayes_auto_learn',$spam->main_array["bayes_auto_learn"])."</td>
			<td valign='top'>&nbsp;</td>
		</tr>	
		<tr>
			<td align='right' nowrap valign='top' class=legend>{required_score}:</strong></td>
			<td valign='top' colspan=2>" . Field_text('required_score',$spam->main_array["required_score"],'width:50px',null,null,'{required_score_text}')."</td>
			
		</tr>
		<tr>
			<td align='right' nowrap valign='top' class=legend>{block_with_required_score}:</strong></td>
			<td valign='top' colspan=2>" . Field_text('block_with_required_score',$spam->block_with_required_score,'width:50px',null,null,'{block_with_required_score_text}')."</td>
		</tr>	
		<tr><td colspan=3 align='right'><i>$disable_explain_spamassin</i></td></tr>
		<tr>
			<td colspan=3 align='right' valign='top'>
			<hr>
			". button("{save}","SpamassinScoreBehavior()")."
			</td>
		</tr>
</table>
</div>
<script>
	function SpamAssassinDisableScores(){
		document.getElementById('block_with_required_score').disabled=true;
		document.getElementById('required_score').disabled=true;
		
	
	}

$js_load
</script>
";	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function rewrite_headers_form(){
	$users=new usersMenus();
	$spam=new spamassassin();
	$page=CurrentPageName();	
	
$html="
<div id='spamassass_rewrite_headers_div'>
<input type='hidden' name='rewrite_headers' value='yes'>
	<table style='width:100%'>
	<tr><td $style colspan=3 align='left' valign='top'><H5>{rewrite_header}</H5></td></tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{subject}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('Subject',$spam->rewrite_headers["Subject"],'width:250px',null,null,'{rewrite_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{From}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('From',$spam->rewrite_headers["From"],'width:250px',null,null,'{rewrite_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{To}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('To',$spam->rewrite_headers["To"],'width:250px',null,null,'{rewrite_header_txt}')."</td>
	</tr>		
	<tr>
	<td $style colspan=3 align='right' valign='top'>
		<hr>
	". button("{save}","spamassass_rewrite_headers()")."
	</td>
	</tr>

	</table>
</div>
	<script>
var X_spamassass_rewrite_headers= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		spamass_rewrite_headers();
	}		
function spamassass_rewrite_headers(){
		var XHR = new XHRConnection();
		XHR.appendData('rewrite_headers','yes');
		XHR.appendData('Subject',document.getElementById('Subject').value);
		XHR.appendData('From',document.getElementById('From').value);
		XHR.appendData('To',document.getElementById('To').value);
		document.getElementById('spamassass_rewrite_headers_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_spamassass_rewrite_headers);
		}	
	</script>
	
	";
			
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function add_headers_form(){
	$users=new usersMenus();
	$spam=new spamassassin();
	$page=CurrentPageName();	
	if($spam->add_headers["spam"]==null){
		$spam->add_headers["spam"]="Flag _YESNOCAPS_";
	}
	
if($spam->add_headers["ham"]==null){
		$spam->add_headers["ham"]="Checker-Version SpamAssassin _VERSION_ (_SUBVERSION_) on _HOSTNAME_";
	}

	
if($spam->add_headers["all"]==null){
		$spam->add_headers["all"]="Status _YESNO_, score=_SCORE_ required=_REQD_ tests=_TESTS_ autolearn=_AUTOLEARN_ version=_VERSION_";
	}
		
	
$html="	
	<div id='spamassass_add_headers_div'>
	<table style='width:100%'>
	<tr><td $style colspan=3 align='left' valign='top'><H5>{add_header} (X-Spam-)</H5><hr></td></tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{spam}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('spam',$spam->add_headers["spam"],'width:250px',null,null,'{add_spamassassin_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{ham}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('ham',$spam->add_headers["ham"],'width:250px',null,null,'{add_spamassassin_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{all}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('all',$spam->add_headers["all"],'width:250px',null,null,'{add_spamassassin_header_txt}')."</td>
	</tr>		
	<tr>
	<td $style colspan=3 align='right' valign='top'>
		<hr>
	". button("{save}","spamassass_add_headers()")."
	</td>
	</tr>

	</table>
</div>
	<script>
var X_spamassass_add_headers= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		spamass_add_headers();
	}		
function spamassass_add_headers(){
		var XHR = new XHRConnection();
		XHR.appendData('add_headers','yes');
		XHR.appendData('spam',document.getElementById('spam').value);
		XHR.appendData('ham',document.getElementById('ham').value);
		XHR.appendData('all',document.getElementById('all').value);
		document.getElementById('spamassass_add_headers_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_spamassass_add_headers);
		}	
	</script>	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function main_config2(){
	//$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$spam=new spamassassin();
	$page=CurrentPageName();
	
	$html="
	<form name='FFM_DANS2'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	
<br>
<form name='FFM_DANS3'>
<input type='hidden' name='rewrite_headers' value='yes'>
	<table style='width:100%'>
	<tr><td colspan=3 ><H3>{title1}</H3><hr></td></tr>
	<tr><td $style colspan=3 align='right' valign='top'><H5>{rewrite_header}</H5></td></tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{subject}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('Subject',$spam->rewrite_headers["Subject"],'width:250px',null,null,'{rewrite_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{From}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('From',$spam->rewrite_headers["From"],'width:250px',null,null,'{rewrite_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{To}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('To',$spam->rewrite_headers["To"],'width:250px',null,null,'{rewrite_header_txt}')."</td>
	</tr>		
	<tr>
	<td $style colspan=3 align='right' valign='top'>
		<hr>
	". button("{save}","ParseForm('FFM_DANS3','$page',true)")."
	</td>
	</tr>

	</table><br>
<form name='FFM_DANS4'>
<input type='hidden' name='add_headers' value='yes'>
	<table style='width:100%'>
	<tr><td $style colspan=3 align='right' valign='top'><H5>{add_header}</H5><hr></td></tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{spam}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('spam',$spam->add_headers["spam"],'width:250px',null,null,'{add_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{ham}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('ham',$spam->add_headers["ham"],'width:250px',null,null,'{add_header_txt}')."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{all}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('all',$spam->add_headers["all"],'width:250px',null,null,'{add_header_txt}')."</td>
	</tr>		
	<tr>
	<td $style colspan=3 align='right' valign='top'>
		<hr>
	". button("{save}","ParseForm('FFM_DANS4','$page',true)")."
	</td>
	</tr>

	</table></FORM><br>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveConf(){
	$spam=new spamassassin();
	$sock=new sockets();
	if(isset($_GET["EnableSpamassassinWrongMX"])){
		$sock->SET_INFO('EnableSpamassassinWrongMX',$_GET["EnableSpamassassinWrongMX"]);
	}
		
		
	if(isset($_GET["block_with_required_score"])){
		$spam->block_with_required_score=$_GET["block_with_required_score"];
		unset($_GET["block_with_required_score"]);
	}
	
while (list ($num, $val) = each ($_GET) ){
		$spam->main_array[$num]=$val;
		
	}	
$spam->SaveToLdap();
	
}

function rewrite_headers(){
		$spam=new spamassassin();
	unset($_GET["rewrite_headers"]);
	
while (list ($num, $val) = each ($_GET) ){
		$spam->rewrite_headers[$num]=$val;
		}	
$spam->SaveToLdap();		

}

function add_headers(){
		$spam=new spamassassin();
	unset($_GET["add_headers"]);
	
while (list ($num, $val) = each ($_GET) ){
		$spam->add_headers[$num]=$val;
		}	
$spam->SaveToLdap();		
	
}




function main_plugins(){
$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$spam=new spamassassin();
	$users=new usersMenus();
	$page=CurrentPageName();
	$sock=new sockets();
	
	if(!$users->razor_installed){
		$razor_but=Paragraphe_switch_disable("Razor","{razor_text}");
		
	}else{
		$razor_but=Paragraphe_switch_img("Razor","{razor_text}","use_razor2",$spam->main_array["use_razor2"],"{enable_disable}",300);
		
	}
	
	if(!$users->pyzor_installed){
		$pyzor_but=Paragraphe_switch_disable("Pyzor","{pyzor_text}");
		
	}else{
		$pyzor_but=Field_numeric_checkbox_img('use_pyzor',$spam->main_array["use_pyzor"],'{enable_disable}');
		$pyzor_but=Paragraphe_switch_img("Pyzor","{pyzor_text}","use_pyzor",$spam->main_array["use_pyzor"],"{enable_disable}",300);
	}	

	$EnableSpamassassinWrongMX=$sock->GET_INFO("EnableSpamassassinWrongMX");
	if($EnableSpamassassinWrongMX==null){$EnableSpamassassinWrongMX=1;}	
	$wrongmx=Paragraphe_switch_img("WrongMX","{WrongMXPlugin}","EnableSpamassassinWrongMX",$EnableSpamassassinWrongMX,"{enable_disable}",300);
	
	$html="

	<form name='FFM_DANS5'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	
	<table style='width:100%'>
		<tr>
			<td width=1% valign='top'>$razor_but</td>
			<td width=1% valign='top'>$pyzor_but</td>
		</tr>
		<tr>
			<td width=1% valign='top'>$wrongmx</td>
			<td>&nbsp;</td>
		<tr>
			
			
		<tr>
	<td $style colspan=2 align='right' valign='top'>
	<hr>
	". button("{edit}","ParseForm('FFM_DANS5','$page',true);")."
	
	</tr>

	</table></FORM><br>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}





function GetNewForm(){
	
$pure=new milter_greylist();
$id=$_GET["class"];
$line=$pure->ParseAcl($pure->acl[$id]);
	
	
	switch ($_GET["ChangeFormType"]) {
		case "dnsrbl":
			if(!preg_match('#delay\s+([0-9]+)([a-z])#',$line[3],$re)){
				$re[1]=15;
				$re[2]="m";
			}
			$form=
			"<table style='width:100%'>
				<tr>
					<td strong width=1% nowrap align='right'>{dnsrbl_service}:</strong></td>
					<td>" . Field_array_Hash($pure->dnsrbl_class,'dnsrbl_class',null) . "</td>
				</tr>
				<tr>
					<td strong width=1% nowrap align='right'>{delay}:</strong></td>
					<td>" . Field_text("delay","{$re[1]}{$re[2]}",'width:100px') . "</td>
				</tr>				
			</table>";
			
			
			break;
	
		default:$form="<table style='width:100%'>
			<tr>
				<td align='right' width=1% nowrap>{pattern}:</strong></td>
				<td><textarea name='pattern' rows=3 style='width:100%'>{$line[3]}</textarea>
			</tr>
		</table>";
			break;
	}
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($form);
}





function main_conf(){
$pure=new spamassassin();
	$page=CurrentPageName();
	$g=$pure->global_conf;
	
	$text=explode("\n",$g);
	while (list ($key, $line) = each ($text) ){
		if(trim($line)==null){continue;}
		$t[]="<div><code>$line</code></div>";
	}
	
	$html="
	<h5>{config}</H5>
	<div style='padding:10px'>
	". implode("\n",$t)."
	</div>";
		
$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);		
}


function main_dnsrbl(){
	$pure=new milter_greylist();
$page=CurrentPageName();
	$link="YahooWin(450,'$page?edit_dnsrbl=&subline=0','{add_dnsrbl}');";
	$html="
	<p class=caption><div style='float:right'>
	
	
	<input type='button' OnClick=\"javascript:$link;\" value='{add_dnsrbl}&nbsp;&raquo;'></div>
	{dnsrbl_text}</p>
	<div id='acllist'>".main_dnsrbl_list()."</div>

	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}


function main_trusted_networks_list(){
	$spam=new spamassassin();
	$trusted_networks=$spam->trusted_networks;
	
	$html=$html . "<table style=width:100%>
	
	";
	
	if(is_array($trusted_networks)){
		while (list ($index, $line) = each ($trusted_networks) ){
			$html=$html . 
			
			"<tr " . CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td>&nbsp;$line</td></td>
			<td>" . imgtootltip('x.gif','{delete}',"SpamassDelTrustedNetwork($index)") . "</td>
			</tr>";
		}
		
		
	}
	
$tpl=new templates();
	return RoundedLightGrey($tpl->_ENGINE_parse_body($html."</table>"));		
	
}

function main_trusted_networks(){

	
	
$table="
<input type='hidden' id='addtrustednet' value='{addtrustednet}'>
<div style='text-align:right'>
". button("{add} {trusted_networks}","SpamassAddTrustedNetwork()")."
</div>
<br>
<div id='main_trusted_networks' style='width:250px'>
" . main_trusted_networks_list() . "
</div>
";

$html="
<table style='width:100%'>
<tr>
	<td valign='top'>$table</td>
	<td valign='top' style='border-left:1px solid black;padding:5px'><div class=explain>{trusted_networks_text}</div></td>
</tr>
</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function Save_Trusted_Networks(){
	$spam=new spamassassin();
	$spam->trusted_networks[]=$_GET["SpamassAddTrustedNetwork"];
	$spam->SaveToLdap();
	}
function Delete_Trusted_Networks(){
	$spam=new spamassassin();
	unset($spam->trusted_networks[$_GET["SpamassDelTrustedNetwork"]]);
	$spam->SaveToLdap();
}

function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<iframe src='miltergreylist.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}
?>
