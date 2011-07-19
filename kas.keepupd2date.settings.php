<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.artica.inc');
	include_once(dirname(__FILE__).'/ressources/class.kas-filter.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["KasforceUpdates"])){KasforceUpdates();exit;}
if(isset($_GET["KasUpdates"])){KasUpdates();exit;}
if(isset($_GET["section"])){switchlogs();exit;}


$page=PageKas3UpdateConfig();
$JS["JS"][]="js/kas.js";
$tpl=new template_users('{product_update_settings}',$page,0,0,0,0,$JS);
echo $tpl->web_page;
//KasTrapUpdatesErrors

function UpdatesErrors(){
	$kas=new kas_filter();
	$updates_error=$kas->GetTrapUpdatesError();	

if(is_array($updates_error)){
			$html=$html."<H5>{updates_error}</H5>
			<center>
			<table style='width:450px'>
			<tr style='background-color:#005447;'>
			<td>&nbsp;</td>
			<td style='color:white;font-weight:bold' width=5%>{date}</td>
			<td style='color:white;font-weight:bold'>{details}</td>
			</tr>";
			
			
			while (list ($key, $val) = each ($updates_error) ){
				if(preg_match('#\[([0-9\-:\s]+).+?\]#',$val,$reg)){
					$val=str_replace($reg[0],'',$val);
				
				$html=$html . "<tr>
				<td width=1% class='bottom'><img src='img/status_warning.jpg'></td>
				<td class='bottom' nowrap><div style='color:red;font-size:11px;padding:3px;'>{$reg[1]}</div></td>
				<td class='bottom'><div style='color:red;font-size:11px;padding:3px;'>$val</div></td>
				</tR>";
				}
				
			}
			$html=$html . "</table></center>";
		}
		
			$tpl=new templates();	
	return  $tpl->_ENGINE_parse_body($html);	
	
}

function switchlogs(){
	switch ($_GET["tab"]) {
		case 0:echo UpdatesSuccess();break;
		case 1:echo UpdatesErrors();break;
		default:UpdatesSuccess();break;

	}
	
	
}


function UpdatesSuccess(){
$kas=new kas_filter();
		
		$updates_success=$kas->GetTrapSuccessUpdates();	
if(is_array($updates_success)){
			$html=$html."<H5>{updates_success}</H5>
			<center>
			<table style='width:450px'>
			<tr style='background-color:#005447;'>
			<td>&nbsp;</td>
			<td style='color:white;font-weight:bold'  width=5%>{date}</td>
			<td style='color:white;font-weight:bold'>{details}</td>
			</tr>";
			
			
			while (list ($key, $val) = each ($updates_success) ){
				if(preg_match('#\[([0-9\-:\s]+).+?\]#',$val,$reg)){
					$val=str_replace($reg[0],'',$val);
				
				$html=$html . "<tr>
				<td width=1% class='bottom'><img src='img/status_ok.jpg'></td>
				<td class='bottom' nowrap><div style='color:blue;font-size:11px;padding:3px;'>{$reg[1]}</div></td>
				<td class='bottom'><div style='color:blue;font-size:11px;padding:3px;'>$val</div></td>
				</tR>";
				}
				
			}
			$html=$html . "</table></center>";
		}		
	$tpl=new templates();	
	return  $tpl->_ENGINE_parse_body($html);
	
}

function tabs(){
	
	
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{updates_success}';
	$array[]='{updates_error}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('middle_area','$page?section=yes&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function PageKas3UpdateConfig(){
		$yum=new usersMenus();
		$page=CurrentPageName();
		$tpl=new templates();
		if($yum->AsPostfixAdministrator==false){return $tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		$kas=new kas_filter();
		$cron=$kas->CronTask();
		
		
		$kas=new kasUpdater();
		$array_conf=$kas->array_updater_data["updater.options"];
		
		$UseUpdateServerUrl=Field_yesno_checkbox_img('UseUpdateServerUrl',$array_conf["UseUpdateServerUrl"]);
		$UseUpdateServerUrlOnly=Field_yesno_checkbox_img('UseUpdateServerUrlOnly',$array_conf["UseUpdateServerUrlOnly"]);
		$PassiveFtp=Field_yesno_checkbox_img('PassiveFtp',$array_conf["PassiveFtp"]);
		
		
		
		$UseProxy=Field_yesno_checkbox_img('UseProxy',$array_conf["UseProxy"]);
		$ProxyAddress= Field_text('ProxyAddress',$array_conf["ProxyAddress"]);
		
		$artica=new artica_general();
		if($artica->ArticaProxyServerEnabled=='yes'){
			$UseProxy="<input type='hidden' value='yes' name='UseProxy'><strong>Yes</strong>";
			$ProxyAddress="<input type='hidden' value='yes' name='ProxyAddress'><a href='artica.settings.php'><b>$artica->ArticaCompiledProxyUri</b></a>";
		}
		
		
		$config1="
				
		<table style='width:100%'>
		<tr>
		<td>$UseUpdateServerUrl</td>
		<td align='left'><strong>{UseUpdateServerUrl}</strong></td>
		
		</tr>
		<tr>
		<td>$UseUpdateServerUrlOnly</td>
		<td align='left'><strong>{UseUpdateServerUrlOnly}</strong></td>
		</tr>
		<tr>
		<td>$PassiveFtp</td>
		<td align='left'><strong>{PassiveFtp}</strong></td>
		</tr>			
		</table>";
		
		$config1=RoundedLightGrey($config1);
		
		
		$proxy="
		<table style='width:100%'>
		<tr>
		<td align='right' width=30% nowrap><strong>{UpdateServerUrl}:</strong></td>
		<td align='left' nowrap>" . Field_text('UpdateServerUrl',$array_conf["UpdateServerUrl"])."</td>
		</tr>	
		<tr>
		<td align='right' nowrap><strong>{UseProxy}:</strong></td>
		<td align='left'>$UseProxy</td>
		</tr>		
		<tr>
		<td align='right' nowrap><strong>{ProxyAddress}:</strong></td>
		<td align='left'>$ProxyAddress</td>
		</tr>	
		<tr>
		<td align='right' nowrap><strong>{ConnectTimeout}:</strong></td>
		<td align='left'>" . Field_text('ConnectTimeout',$array_conf["ConnectTimeout"],'width:30px')."</td>
		</tr>	
		<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffmupdate','domains.php',true);\" value='{submit}&nbsp;&raquo;'></td>
		</tr>
		</table>";
		
		$proxy=RoundedLightGrey($proxy);
		
		$html="
		<form name=ffmupdate>
		
		<input type='hidden' name=kas3UpdaterConfig value='kas3UpdaterConfig'>
		
		<table style='width:100%'>
		<tr>
		<td valign='top'>
		<br><H5>{keepup2date settings}</H5><br>
		
		$config1<br>$proxy<br>" . tabs() . "<br><div id='middle_area'></div>
		<script>LoadAjax('middle_area','$page?section=yes&tab=0')</script></td>
		
		<td valign='top' width=40%>" . RightInfos() . "</td>
		</tr>
		</table>
		<br>
		
		<br>

		</form>
		<br>

		
	
	";
		
		
	

		return $tpl->_ENGINE_parse_body($html);
	}

	function PageProcMailIntro(){
		$yum=new usersMenus();
		if($yum->AsMailBoxAdministrator==false){return $tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		
		if($this->AutomaticConfig==false){
			$build="<fieldset><legend>{apply config}</legend>
			<center><input type='button' value='{apply config}&nbsp;&raquo;' OnClick=\"javascript:TreeProcMailApplyConfig()\"></center>
			</fieldset>";
			
		}		
		
		$html="<fieldset>
		<legend>{APP_PROCMAIL}</legend>
		{about_procmail}<p>&nbsp;</p>
		<center><input type='button' value='&laquo;&nbsp;{manage_procmail_rules}&nbsp;&raquo' OnClick=\"javascript:TreeProcMailRules()\"></center>
		</fieldset>
		
		
		$build";
		return $tpl->_ENGINE_parse_body($html);
		
	}
function KasforceUpdates(){
	$kas=new kas_filter();
	$kas->ForceUpdateKas();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}
function KasUpdates(){
$kas=new kas_filter();
	$kas->KasUpdateNow();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
}
function RightInfos(){

	
$kas=new kas_filter();
$st=RoundedLightGrey($kas->KasStatus() . "</table>");

$perf=Paragraphe('proxy-64.png','{update_now}','{update_now}',"javascript:KasUpdates();");
$pref_force=Paragraphe('proxy-delete-64.png','{clean_update}','{clean_update}',"javascript:KasforceUpdates();");

$perf=RoundedLightGrey($perf);
$pref_force=RoundedLightGrey($pref_force);
$html=applysettings("kas") ."$perf<br>$pref_force<br>$st";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
	
}
	