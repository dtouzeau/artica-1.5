<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	
	$access=true;
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){$access=false;}
	if(!$access){
		$tpl=new templates();
		echo "alert('".$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}')."');";
		die();
		}
		
	if(isset($_GET["processes-index"])){process_index();exit;}
	if(isset($_GET["ppp"])){echo processlist();exit;}
	js();

	
	
	
function js(){
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{POSTFIX_PROCESS_NUMBER}');
$page=CurrentPageName();	
$html="



function PPPStartPage(){
	YahooWinS(600,'$page?processes-index=yes','$title');
	}
	



function PPPRefesh(){
	LoadAjax('ppp','$page?ppp=yes');

}


PPPStartPage();";
	
	echo $html;
	
}

function process_index(){
	
$refresh=imgtootltip("64-recycle.png","{refresh}","PPPRefesh()");
	
	
$pp=processlist();
	
	$html="
	<H1>{POSTFIX_PROCESS_NUMBER}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div id='logojoom'><img src='img/rouage-64.png'></div><br>$refresh
		</td>
		<td width=99% valign='top'>
			<div id='ppp'>$pp</div>
		</td>
	</tr>
	
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function processlist(){
	
	
	$sock=new sockets();
	$datas=$sock->getfile("PostfixSMTPDCountProcesses");
	
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){continue;}
		$ll=explode(";",$ligne);
		$process[$ll[0]]=$ll[1];
		
	}
	
	
	$html="<table style='width:100%'>
	<tr>
		<td><strong style='font-size:13px'>{process_smtpd}</td>
		<td><strong style='font-size:13px'>".BuildProgression($process["process_smtpd"],100)."</td>
		
	</tr>
	<tr>
		<td><strong style='font-size:13px'>{process_pickup}</td>
		<td><strong style='font-size:13px'>".BuildProgression($process["process_pickup"],1)."</td>
	</tr>	
	<tr>
		<td><strong style='font-size:13px'>{process_cleanup}</td>
		<td><strong style='font-size:13px'>".BuildProgression($process["process_cleanup"],100)."</td>
	</tr>
	<tr>
		<td><strong style='font-size:13px'>{process_trivial_rewrite}</td>
		<td><strong style='font-size:13px'>".BuildProgression($process["process_trivial-rewrite"],100)."</td>
	</tr>
	<tr>
	<td colspan=2><hr></td></tr>";

	$users=new usersMenus();
	if($users->spamassassin_installed){
	$html=$html."
	<tr>
		<td><strong style='font-size:13px'>{process_spamd}</td>
		<td><strong style='font-size:13px'>".BuildProgression($process["process_spamd"],10)."</td>
	</tr>
	
	";	
		
	}
	if($users->AMAVIS_INSTALLED){
		$amavis=new amavis();
		$max_servers=$amavis->main_array["BEHAVIORS"]["max_servers"];
		
	$html=$html."
	<tr>
		<td><strong style='font-size:13px'>{process_amavisd}</td>
		<td><strong style='font-size:13px'>".BuildProgression($process["process_amavisd"]-1,$max_servers)."</td>
	</tr>
	<tr>
		<td><strong style='font-size:13px'>{process_amavisd-milter}</td>
		<td><strong style='font-size:13px'>".BuildProgression($process["process_amavisd-milter"],$max_servers)."</td>
	</tr>";
	
	
		
	}
	
	
	$html=$html."</table>";
	$html=RoundedLightWhite($html);
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html,"postfix.index.php");	
	
}

function BuildProgression($current,$max){
$pourc=$current/$max;
$pourc=round($pourc,2);	
$pourc=$pourc*100;
if($pourc>90){$color="#D32D2D";}else{$color="#5DD13D";}
$pourc_px=$pourc;
if($pourc>100){$pourc_px=100;}
$color="#5DD13D";

					return "
					<table style='width:100%'>
					<tr>
					<td>
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color'>
								<div style='width:{$pourc_px}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
									<strong>{$pourc}%</strong>
								</div>
							</div>
					</td>
					<td nowrap><strong style='font-size:12px'>$current/$max ({$pourc}%)</strong></td>
					</tr>
				</table>";	
	
}

function sugar_form1($ou){
	$ldap=new clladp();
	$sugar=new joomla($ou);
	
	
	if($sugar->params["CONF"]["ldap_connection_user"]==null){
			if($_SESSION["uid"]==-100){
				$ldap_connection_user=$ldap->ldap_admin;
			}else{
				$ldap_connection_user=$_SESSION["uid"];
			}
	}else{
		$ldap_connection_user=$sugar->params["CONF"]["ldap_connection_user"];
	}
	
	
	
	if($sugar->params["CONF"]["sugaradminname"]==null){$sugar->params["CONF"]["sugaradminname"]="admin";}
	if($sugar->params["CONF"]["sugaradminpassword"]==null){$sugar->params["CONF"]["sugaradminpassword"]="secret";}
	
	$html="<table style='width:100%'>
	<tr>
		<td class=legend>{sugarservername}:</td>
		<td>" . Field_text('sugarservername',$sugar->params["CONF"]["sugarservername"],'width:99%')."</td>
		<td>" . help_icon('{joomlaservername_help}',false,"domains.joomla.php")."</td>
	</tr>	
	<tr>
		<td class=legend>{admin_name}:</td>
		<td>" . Field_text('sugaradminname',$sugar->params["CONF"]["sugaradminname"],'width:120px')."</td>
		<td>" . help_icon('{connection_user_help}')."</td>
	</tr>
	<tr>
		<td class=legend>{admin_password}:</td>
		<td>" . Field_password('sugaradminpassword',$sugar->params["CONF"]["sugaradminpassword"])."</td>
		<td>" . help_icon('{admin_password_help}')."</td>
	</tr>	
	<tr><td colspan=3><hr></tr>
	<tr><td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SavesugarForm();\"></td></tr>
	</table>";
	
	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html,"domains.joomla.php");
	
}



function sugar_install(){
	$ou=$_GET["sugar-install"];
	$sock=new sockets();
	$datas=$sock->getfile("sugarInstall:$ou");
	echo $datas;
	
}

function sugar_save(){
	$ou=$_GET["ou"];
	$sugar=new joomla($ou);

	while (list ($num, $ligne) = each ($_GET) ){
		$sugar->params["CONF"][$num]=$ligne;
		$sugar->SaveParams();
	}
	$su=new SugarCRM($ou);
	$su->CreateAdminPassword();
	$su->UpdateLDAPConfig();
	
}
	
	
	

?>