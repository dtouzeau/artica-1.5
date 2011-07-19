<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.obm.inc');
	
	
	if(isset($_GET["start"])){echo obm2_index();exit;}
	if(isset($_GET["Obm2ListenPort"])){obm2_save();exit;}
	
	js();
	
	
	
function js(){
	$user=new usersMenus();	
	if($user->AsArticaAdministrator==false){
		$tpl=new templates();
		echo "alert('{$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}')}');";
		exit;
	}
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_OBM}');
	
	$html="
		function Obm2Load(){
			YahooWin3(700,'$page?start=yes','$title');
		
		}
		
	function x_Obm2Save(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		Obm2Load();
		}		
		
		function Obm2Save(){
			var XHR = new XHRConnection();
			XHR.appendData('Obm2Externaluri',document.getElementById('Obm2Externaluri').value);
			XHR.appendData('Obm2ListenPort',document.getElementById('Obm2ListenPort').value);
			document.getElementById('obm2Div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_Obm2Save);		
			}
	
	Obm2Load();
	";
	
	echo $html;
	
	
}

function obm2_save(){
	$sock=new sockets();
	$sock->SET_INFO("Obm2Externaluri",$_GET["Obm2Externaluri"]);
	$sock->SET_INFO("Obm2ListenPort",$_GET["Obm2ListenPort"]);
	$sock->getfile("Obm2restart");
	}


function obm2_index(){
	
$ini=new Bs_IniHandler();
	
		
	
	$tpl=new templates();
	$users=new usersMenus();	
	
	$sock=new sockets();
	$ini->loadString($sock->getfile('Obm2Status',$_GET["hostname"]));
	$status=$tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("OBM2",$ini));
	
	
	$Obm2ListenPort=trim($sock->GET_INFO('Obm2ListenPort'));
	$Obm2Externaluri=trim($sock->GET_INFO('Obm2Externaluri'));
	
	
	if($Obm2ListenPort==null){$Obm2ListenPort=8080;}
if($Obm2Externaluri==null){$Obm2Externaluri="http://$users->hostname:$Obm2ListenPort";}
	$html="
	<H1>{APP_OBM2}</H1>
	<div id='obm2Div'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		" . RoundedLightWhite("
			<table style='width:100%'>
				<tr>
					<td class=legend>{listen_port}:</td>
					<td>" . Field_text('Obm2ListenPort',$Obm2ListenPort,'width:40px')."</td>
				</tr>
				<tr>
					<td class=legend>{external_url}:</td>
					<td>" . Field_text('Obm2Externaluri',$Obm2Externaluri,'width:240px')."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:Obm2Save();\"></td>
				</tr>				
			</table>")."
		</td>
		<td valign='top'>
		$status
		</td>
	</tr>
	</table>
	</div>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'obm.index.php');
	
	
}
	
	

	
function main_page(){
	

	
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
	<td width=1% valign='top'><img src='img/bg_obm.png' style='margin-right:80px'></td>
	<td valign='top'><div id='services_status'>". main_status() . "</div><br><p class=caption>{about}</p></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();LoadAjax('main_config','$page?main=yes');</script>
	
	";
	
	$cfg["JS"][]='js/obm.js';
	$tpl=new template_users('{APP_OBM}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{main_settings}';
	$array["logs"]='{events}';	
	$array["syncevents"]='{syncevents}';
	$array["apacheconf"]='{apacheconf}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "syncevents":main_sync();exit;break;
		case "conf":echo main_conf();exit;break;
		case "apacheconf":echo main_httpdconf();exit;break;
		default:
			break;
	}
	
	
}	

function main_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('obmapachestatus',$_GET["hostname"]));	
	$status=DAEMON_STATUS_ROUND("OBM_APACHE",$ini,null);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);
	
}
function main_conf(){
	
	$sock=new sockets();
	$datas=$sock->getfile('cyrus_imapconf');
	$datas=htmlspecialchars($datas);
	$datas=nl2br($datas);
	$datas=str_replace("\n","",$datas);
	$datas=str_replace("<br /><br />","<br />",$datas);
	$html=main_tabs()."
	<br><H5>{config}</H5>
	<div style='padding:10px;margin:10px;border 1px dotted #CCCCCC'>
	<code>$datas</code>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function main_export_logs(){
		if(is_file(dirname(__FILE__).'/ressources/logs/obm-synchro.log')){
		$datas=file_get_contents(dirname(__FILE__).'/ressources/logs/obm-synchro.log');
		}
		
		$html="<textarea style='border:0px;padding:3px;width:100%;font-size:9px' rows=30>$datas</textarea>";
		echo $html;
		
		
	
	
}	
	


function main_config(){

	 $page=CurrentPageName();
	 $entete=main_tabs()."<br>
	 <H5>{main_settings}</H5>
	 <br>
	 ";
	 
	 $obm=new obm();
	 if($obm->external_url==null){$obm->external_url=$_SERVER['SERVER_NAME'];}
	 
	 $form="
	 <input type='hidden' id='warn_export_obm' value='{warn_export_obm}'>
	 <table style='width:100%'>
	 <tr>
	 <td width=1% valign='top'>
	 
	 	<table style='width:100%'>
	 	<tr>
	 		<td valign='top'>" . Paragraphe("waifolder-update-64.jpg","{export_artica_users}","{export_artica_users_text}","javascript:obm_export();")."</td>
	 	</TR>
	 	<tr>
	 		<td valign='top'>" . Paragraphe("64-obm.png","{APP_OBM}","{obm_connect_has_admin}","javascript:s_PopUpFull('https://$obm->external_url:$obm->apache_listen',800,600,'OBM');")."</td>
	 	</tr>
	 </table>
	 		
	 		
	 <td valign='top'>" . RoundedLightGreen("
	 <form name='ffm1'>
	 <table style='width:100%'>
	 <tr>
	 	<td align='right' nowrap style='font-weight:bold'>{enable_obm}:</td>
	 	<td>" . Field_numeric_checkbox_img('OBMEnabled',$obm->OBMEnabled,'{enable_disable}')."</td>
	 </tr>
	 <tr>
	 	<td align='right' nowrap style='font-weight:bold'>{https_port}:</td>
	 	<td>" . Field_text('apache_listen',$obm->apache_listen,'width:150px')."</td>
	 </tr>
	 <tr>
	 	<td align='right' nowrap style='font-weight:bold'>{external_protocol}:</td>
	 	<td>" . Field_text('external_protocol',$obm->external_protocol,'width:150px',null,null,'{external_url_text}')."</td>
	 </tr>
	 <tr>
	 	<td align='right' nowrap style='font-weight:bold'>{external_url}:</td>
	 	<td>" . Field_text('external_url',$obm->external_url,'width:150px',null,null,'{external_url_text}')."</td>
	 </tr>	
	 <tr>
	 	<td align='right' nowrap style='font-weight:bold'>{OBMSyncCron}:</td>
	 	<td>" . Field_text('OBMSyncCron',$obm->OBMSyncCron,'width:90px',null,null,'{OBMSyncCron_text}')."</td>
	 </tr>		 	 	 
	 <tr>
	 <td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffm1','$page',true,false,false,'main_config','$page?main=yes');\" value='{edit}&nbsp;&raquo;'>
	 </table>
	 </form>")."
	 </td>
	 </tr>
	 </table>
	 
	 ";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$entete$form");
	
}

function main_httpdconf(){
	$obm=new obm(); 
	$conf=$obm->httpd_conf;
	
	$conf=htmlspecialchars($conf);
	$conf=nl2br($conf);
	
	$entete=main_tabs()."<br>
	 <H5>{apacheconf}</H5>
	 <br>
	 <div style='padding:5px;margin:10px;border:1px dotted #CCCCCC'>
	 <code>$conf</code>
	 </div>
	 ";	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$entete");	
}

function main_sync(){
	$sql="SELECT * FROM events WHERE event_id='5' ORDER BY ID DESC LIMIT 0,100";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_events');
	$table="<table style='width:550px'>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		
		$ligne["text"]=htmlspecialchars($ligne["text"]);
		$ligne["text"]=nl2br($ligne["text"]);
		$table=$table . 
		
		"
		<tr><td colspan=3><hr></td></tr>
		<tr>
			<td valign='top' width=1%><img src='img/fw_bold.gif'></td>
			<td valign='top' nowrap><strong>{$ligne["zDate"]}</strong></td>
			<td valign='top'><code>{$ligne["text"]}</code></td>	
		</tr>		
			";
			
	}$table=$table . "</table>"			;
	
	$table=RoundedLightGrey($table);
	
$entete=main_tabs()."<br>
	 <H5>{syncevents}</H5>
	 <br><div style='width:550px'>$table</div>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($entete);
	
}



function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html=main_tabs() . "
	<H5>{events}</H5>
	<iframe src='obm.apache.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}
function main_save(){
	$obm=new obm();
	$obm->OBMEnabled=$_GET["OBMEnabled"];
	
	if($obm->OBMEnabled==1){
		$bm=new obm_export_single(1);
		$ldap=new clladp();
		$bm->CreateManager($ldap->ldap_admin,$ldap->ldap_password);
	}
	
	$obm->apache_listen=$_GET["apache_listen"];
	$obm->external_protocol=$_GET["external_protocol"];
	$obm->external_url=$_GET["external_url"];
	
	if(preg_match('#([0-9]+)(m|h|d)#',$_GET["OBMSyncCron"])){
		$obm->ObmOBMSyncCron=$_GET["OBMSyncCron"];
	}
	$obm->SaveToLdap();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	}
	
function export(){
	
	switch ($_GET["export"]) {
		case -1:
			export_main();exit;break;
		case 0:
			$obm=new obm_export();break;
		case 5:
			main_export_logs();;break;
				
				
			
			
			
		default:
			break;
	}
	
	
}

function export_main(){
	
	$html="<H5>{exporting_obm}</H5>
	<div id='message_0'></div>
	<div id='message_1'></div>
	<div id='message_2'></div>
	<div id='message_3'></div>
	<div id='message_4'></div>
	<div id='message_5'></div>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function export_domains(){
	$obm=new obm_export();
	$obm->Update_Organizations();
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{organizations_added}:&nbsp;<b>$obm->domain_added</b></td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function export_update_domains(){
	$obm=new obm_export();
	$obm->Update_Domains();
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{domains_updated}:&nbsp;<b>$obm->domain_uddated</b></td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function export_users(){
	$obm=new obm_export();
	$obm->Update_users();
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{user_added}:&nbsp;<b>$obm->user_added</b></td>
	</tr>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{user_failed}:&nbsp;<b>$obm->user_failed</b></td>
	</tr>	
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function export_users_update(){
	$obm=new obm_export();
	$obm->Update_sync_users();
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{user_updated}:&nbsp;<b>$obm->user_added</b></td>
	</tr>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{user_failed}:&nbsp;<b>$obm->user_failed</b></td>
	</tr>	
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	}
function export_groups(){
	$obm=new obm_export();
	$obm->Update_groups();
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{group_updated}:&nbsp;<b>$obm->user_added</b></td>
	</tr>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{group_failed}:&nbsp;<b>$obm->user_failed</b></td>
	</tr>	
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function export_admin(){
	$obm=new obm_export();
	if($obm->Update_admin()){$li="{success}";}else{$li='{failed}';}
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td style='font-size:11px'>{update_global_admin}:&nbsp;<b>$li</b></td>
	</tr>
	</table>
	<p class=caption>{close_now}</p>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}



?>
