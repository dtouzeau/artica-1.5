<?php
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.httpd.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');


if(isset($_GET["script"])){switch_script();exit;}
if(isset($_GET["popup"])){switch_popup();exit;}
if(isset($_GET["listname_add"])){popup_addlist();exit;}
if(isset($_GET["mailmanlist"])){echo popup_list();exit;}
if(isset($_GET["mailmaninfos"])){echo popup_list_info();exit;}
if(isset($_GET["DEFAULT_URL_PATTERN"])){popup_save();exit;}
if(isset($_GET["MailManDeleteList"])){delete_mailman_list();exit;}
if(isset($_GET["BuildMailManRobots"])){buildrobots();exit;}
if(isset($_GET["EnableMailman"])){EnableMailman();exit;}
if(isset($_GET["MailManDeleteList-new"])){delete_distriblist($_GET["MailManDeleteList-new"]);exit;}
if(isset($_GET["adv-options"])){echo popup_options();exit;}
if(isset($_GET["MAILMAN_DEFAULT_URL_PATTERN"])){popup_options_save();exit;}


function EnableMailman(){
	$sock=new sockets();
	$sock->SET_INFO('MailManEnabled',$_GET["EnableMailman"]);
}

function delete_mailman_list(){
	$list=$_GET["MailManDeleteList"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?mailman-delete=$list");
	$sock->getFrameWork('cmd.php?restart-mailman=yes');
	$mailman=new mailmanctl();
	$mailman->DeleteRobot($list,$_GET["domain"]);
}

function delete_distriblist(){
	$list=$_GET["MailManDeleteList-new"];
	$sock=new sockets();
	$datas=implode("\n",unserialize($sock->getFrameWork("cmd.php?mailman-delete={$_GET["MailManDeleteList-new"]}")));
	echo $datas;
}

function buildrobots(){
	$list=$_GET["BuildMailManRobots"];
	$mailman=new mailmanctl();
	$mailman->BuildRobots($list,$_GET["domain"]);
	
}


function switch_script(){
	
$users=new usersMenus();
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){}else{exit;}	
	switch ($_GET["script"]) {
		case "yes":popup_script();break;
		
		default:
			break;
	}
	
	
}
function switch_popup(){
$users=new usersMenus();
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){}else{exit;}	
	
	switch ($_GET["popup"]) {
		case "yes":popup_start();break;
		case "add":popup_add();break;
		default:
			break;
	}
}


function popup_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{manage_distribution_lists}';
	//$array["global-options"]='{mailman_global_options}';

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_section_mailman','$page?popup=yes&main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";		
}


function popup_script(){
$page=CurrentPageName();
$prefix=str_replace('.','_',$page);
$tpl=new templates();
$confirm_delete_mailman=$tpl->_ENGINE_parse_body("{confirm_delete_mailman}");
$advanced_options=$tpl->_ENGINE_parse_body("{advanced_options}");
$html="
	var tmpnum='';
	function {$prefix}load(){
	YahooWin(650,'$page?popup=yes','','');	
	}
	
var x_MailManDeleteList= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	{$prefix}load();
}	

var x_MailManRobots= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){
                alert(tempvalue);
	}
	{$prefix}load();
}	
	
	
	function MailManAddlist(){
		YahooWin2(550,'$page?popup=add&ou={$_GET["ou"]}','','');	
	
	}
	
	function MailManAdvancedOptions(){
		YahooWin2(550,'$page?adv-options=yes','$advanced_options');	
	}
	
	function DeleteMailManList(listname){
		var text='$confirm_delete_mailman';
		if(confirm(text)){
		var XHR = new XHRConnection();
		XHR.appendData('MailManDeleteList-new',listname);
		XHR.sendAndLoad('$page', 'GET',x_MailManDeleteList);		
		}
	}
	
	function MailMainListInfo(listname){
		LoadAjax('mailman_info','$page?mailmaninfos='+listname);
	}
	
	function MailManDeleteList(listname,domain){
		var text='$confirm_delete_mailman';
		if(confirm(text)){
		var XHR = new XHRConnection();
		XHR.appendData('MailManDeleteList',listname);
		XHR.appendData('domain',domain);
		XHR.sendAndLoad('$page', 'GET',x_MailManDeleteList);		
		}
	}
	
	function BuildMailManRobots(listname,domain){
		var XHR = new XHRConnection();
		XHR.appendData('BuildMailManRobots',listname);
		XHR.appendData('domain',domain);
		XHR.sendAndLoad('$page', 'GET',x_MailManRobots);
	
	}
	
var x_EnableMailManList= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	{$prefix}load();
}	
	
function EnableMailManList(){
		var XHR = new XHRConnection();
		XHR.appendData('EnableMailman',document.getElementById('EnableMailman').value);
		XHR.sendAndLoad('$page', 'GET',x_EnableMailManList);
	
	}	
	
var x_SaveAdvancedSettings= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	YahooWin2Hide();
	{$prefix}load();
}	
	
function SaveAdvancedSettings(){
		var XHR = new XHRConnection();
		XHR.appendData('MAILMAN_DEFAULT_URL_PATTERN',document.getElementById('MAILMAN_DEFAULT_URL_PATTERN').value);
		XHR.appendData('MAILMAN_PUBLIC_ARCHIVE_URL',document.getElementById('MAILMAN_PUBLIC_ARCHIVE_URL').value);
		XHR.appendData('MAILMAN_DEFAULT_EMAIL_HOST',document.getElementById('MAILMAN_DEFAULT_EMAIL_HOST').value);
		XHR.appendData('MAILMAN_DEFAULT_URL_HOST',document.getElementById('MAILMAN_DEFAULT_URL_HOST').value);
		XHR.appendData('MAILMAN_DEFAULT_SERVER_LANGUAGE',document.getElementById('MAILMAN_DEFAULT_SERVER_LANGUAGE').value);
		
		
		
		XHR.sendAndLoad('$page', 'GET',x_SaveAdvancedSettings);
}
	
{$prefix}load();";
	echo $html;

}

function popup_options_save(){
	$sock=new sockets();
	$sock->SET_INFO("MAILMAN_DEFAULT_URL_PATTERN",$_GET["MAILMAN_DEFAULT_URL_PATTERN"]);
	$sock->SET_INFO("MAILMAN_PUBLIC_ARCHIVE_URL",$_GET["MAILMAN_PUBLIC_ARCHIVE_URL"]);
	$sock->SET_INFO("MAILMAN_DEFAULT_EMAIL_HOST",$_GET["MAILMAN_DEFAULT_EMAIL_HOST"]);
	$sock->SET_INFO("MAILMAN_DEFAULT_URL_HOST",$_GET["MAILMAN_DEFAULT_URL_HOST"]);
	$sock->SET_INFO("MAILMAN_DEFAULT_SERVER_LANGUAGE",$_GET["MAILMAN_DEFAULT_SERVER_LANGUAGE"]);
	
	
	
	
	$sock->getFrameWork("cmd.php?restart-mailman=yes");
}


function popup_start(){
	
	if($_GET["main"]=="global-options"){popup_options();exit;}
	if($_GET["main"]=="yes"){popup_conf_list();exit;}
	
	echo "<div id='main_section_mailman'>";
	popup_conf_list();
	echo "</div>";
	
}

function popup_conf_list(){
	
$page=CurrentPageName();
	$mailman=new mailmanctl();
	$tabs=popup_tabs();
	$add=Paragraphe("mailman-add.png",'{add_mailman}','{add_mailman_text}','javascript:MailManAddlist()');
	$sock=new sockets();
	$EnableMailman=$sock->GET_INFO("MailManEnabled");
	$enable_mailman=Paragraphe_switch_img('{ENABLE_MAILMAN}','{ENABLE_MAILMAN_TEXT}','EnableMailman',$EnableMailman,null,320);
	$html="
	
	
	<form name='FFMCOMPRESSS'>
	<H1>{APP_MAILMAN}</h1>
	$tabs
	<p class=caption>{manage_distribution_lists}</p>
		<table style='width:100%'>
		<tr>
			<td width=60% valign='top'>
			<div id='mailman_info'></div>
			$enable_mailman
			<div style='text-align:left;margin-top:5px'>".button("{advanced_options}","MailManAdvancedOptions()")."</div>
			<hr>
			<div style='margin-top:5px;text-align:right'>".button("{edit}","EnableMailManList()")."</div>
		</td>
		<td valign='top'>".Paragraphe("info-48.png","{online_help}","{online_help_text}","javascript:s_PopUp('http://www.artica.fr/index.php/menudocmessaging/38-smtp-routing/285-using-distributions-lists-with-mailman-and-artica',1024,800")."
		</td>
		</tr>
		<tr>
		<td valign='top' width=99% colspan=2>
				
		<div id='mailmanlist'>".popup_list()."</div>
		</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mailman.lists.php');			
	
}


function popup_options(){
	$page=CurrentPageName();
	$tabs=popup_tabs();
	$sock=new sockets();
	$user=new usersMenus();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$user->fqdn;

	$MAILMAN_PUBLIC_ARCHIVE_URL=$sock->GET_INFO("MAILMAN_PUBLIC_ARCHIVE_URL");
	$MAILMAN_DEFAULT_URL_PATTERN=$sock->GET_INFO("MAILMAN_DEFAULT_URL_PATTERN");
	$MAILMAN_DEFAULT_URL_HOST=$sock->GET_INFO("MAILMAN_DEFAULT_URL_HOST");
	$MAILMAN_DEFAULT_SERVER_LANGUAGE=$sock->GET_INFO("MAILMAN_DEFAULT_SERVER_LANGUAGE");
	
	
	if($MAILMAN_DEFAULT_URL_HOST==null){$MAILMAN_DEFAULT_URL_HOST="http://$user->fqdn:$ApacheGroupWarePort";}
	if($MAILMAN_DEFAULT_URL_PATTERN==null){$MAILMAN_DEFAULT_URL_PATTERN="%s/cgi-bin/mailman/";}
	if($MAILMAN_PUBLIC_ARCHIVE_URL==null){
	$MAILMAN_PUBLIC_ARCHIVE_URL="http://%(hostname)s:$ApacheGroupWarePort/pipermail/%(listname)s/index.html";}
	if($MAILMAN_DEFAULT_SERVER_LANGUAGE==null){$MAILMAN_DEFAULT_SERVER_LANGUAGE="en";}
	
	$langs["zh_TW"]="zh_TW";
	$langs["de"]="de";
	$langs["pt_BR"]="pt_BR";
	$langs["no"]="no";
	$langs["sl"]="sl";
	$langs["ja"]="ja";
	$langs["sk"]="sk";
	$langs["sv"]="sv";
	$langs["da"]="da";
	$langs["it"]="it";
	$langs["he"]="he";
	$langs["hu"]="hu";
	$langs["vi"]="vi";
	$langs["gl"]="gl";
	$langs["fr"]="fr";
	$langs["es"]="es";
	$langs["tr"]="tr";
	$langs["zh_CN"]="zh_CN";
	$langs["hr"]="hr";
	$langs["ia"]="ia";
	$langs["uk"]="uk";
	$langs["nl"]="nl";
	$langs["ru"]="ru";
	$langs["sr"]="sr";
	$langs["en"]="en";
	$langs["ro"]="ro";
	$langs["cs"]="cs";
	$langs["et"]="et";
	$langs["ar"]="ar";
	$langs["fi"]="fi";
	$langs["pt"]="pt";
	$langs["ko"]="ko";
	$langs["lt"]="lt";
	$langs["eu"]="eu";
	$langs["ca"]="ca";
	$langs["pl"]="pl";
	
	$MAILMAN_DEFAULT_SERVER_LANGUAGE=Field_array_Hash($langs,"MAILMAN_DEFAULT_SERVER_LANGUAGE",$MAILMAN_DEFAULT_SERVER_LANGUAGE);
	
	$html="
	
	
	
	
	<form name='FFMGS'>
	<H1>{mailman_global_options}</h1>
	<p class=caption>{manage_distribution_lists}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend nowrap>{DEFAULT_URL_PATTERN}:</td>
		<td>" . Field_text('MAILMAN_DEFAULT_URL_PATTERN',$MAILMAN_DEFAULT_URL_PATTERN)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{PUBLIC_ARCHIVE_URL}:</td>
		<td>" . Field_text('MAILMAN_PUBLIC_ARCHIVE_URL',$MAILMAN_PUBLIC_ARCHIVE_URL)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{DEFAULT_EMAIL_HOST}:</td>
		<td>" . Field_text('MAILMAN_DEFAULT_EMAIL_HOST',$sock->GET_INFO("MAILMAN_DEFAULT_EMAIL_HOST"))."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{MAILMAN_DEFAULT_URL_HOST}:</td>
		<td>" . Field_text('MAILMAN_DEFAULT_URL_HOST',$MAILMAN_DEFAULT_URL_HOST)."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{language}:</td>
		<td>$MAILMAN_DEFAULT_SERVER_LANGUAGE</td>
	</tr>	
	
<tr>
	<td colspan=2 align='right'>". button("{edit}","SaveAdvancedSettings()")."</td>
	</tr>		
	</table>
</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mailman.lists.php');		
	
}	

function popup_delete(){
	$users=new AutoUsers();
	unset($users->AutoCreateAccountIPArray[$_GET["AutoCreateAccountDelete"]]);
	$users->Save(1);
}

function popup_add(){
	$ldap=new clladp();
	$page=CurrentPageName();
	$users=new usersMenus();
	if($_GET["ou"]<>null){
		
		$domains=$ldap->hash_get_domains_ou($_GET["ou"]);
	}else{
		$domains=$ldap->hash_get_all_domains();
	}
	
	
	$domain=Field_array_Hash($domains,'domain');
	
	$html="<form name='FFMADDLIST'>
	<H1>{add_mailman}</h1>
	<p class=caption>{add_mailman_text}</p>
	<table class=table_form>
	<tr>
		<td valign='top' class=legend>{listname}:</td>
		<td valign='top'>" . Field_text('listname_add',null,'width:120px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{domain}:</td>
		<td valign='top'>$domain</td>
	</tr>	
<tr>
		<td valign='top' class=legend>{urlhost}:</td>
		<td valign='top'>" . Field_text('urlhost',$_SERVER['SERVER_NAME'],'width:160px')."</td>
	</tr>	
<tr>
		<td valign='top' class=legend>{MailManListAdministrator_text}:</td>
		<td valign='top'>" . Field_text('adminmail',null,'width:220px')."</td>
	</tr>		
		<tr>
				<td colspan=2 align='right'><input type='button' 
				OnClick=\"javascript:ParseForm('FFMADDLIST','$page',true,false,false,'mailmanlist','$page?mailmanlist=yes');\" value='{add}&nbsp;&raquo;'></td>
			</tr>	
	</table>
	
	";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mailman.lists.php');			
}


function popup_list(){
	//1.4.010916
	$sock=new sockets();
	$r=$sock->getFrameWork('cmd.php?MailMan-List=yes');
	$datas=unserialize($r);
	if(!is_array($datas)){return null;}
	
	$html="<table style='width:100%'>";
	while (list ($num, $val) = each ($datas) ){
		$val=strtolower(trim($val));
		//$js="MailMainListInfo('$val');";
		if($val==null){continue;}
		$html=$html . "<tr " . CellRollOver($js).">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:16px'><strong>$val</strong>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DeleteMailManList('$val')")."</td>
		</tr>
		
		";
		
		
	}
	
	$html=$html . "</table>";
	$html="<div style='width:100%;height:250px;overflow:auto'>$html</div>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<br>".RoundedLightWhite($html));	
	
	
}

function popup_list_info(){
	$sock=new sockets();
	$list=$_GET["mailmaninfos"];
	
	$mailman=new mailmanctl();
	$default_uri=$mailman->DEFAULT_URL_PATTERN;
	if(substr($default_uri,strlen($default_uri)-1,1)=='/') {
		$default_uri=substr($default_uri,0,strlen($default_uri)-1);
	}
	$default_uri=str_replace('%s',$_SERVER['SERVER_NAME'],$default_uri).'/admin/'.$list;

	
	//https://localhost:9000/cgi-bin/mailman/admin/touzeau/
	
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->getfile("MailManListInfo:$list"));
	
	$html="
	<input type='hidden' id='confirm_delete_mailman' value='{confirm_delete_mailman}'>
	
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1% style='border:1px solid white;padding:5px'>
		" . imgtootltip('cpanel.png','{access_mailman_config}',"s_PopUpFull('$default_uri',800,800)")."
	<p>&nbsp;</p>
	" . imgtootltip('import-users-48.gif','{building_robots}',"BuildMailManRobots('$list','{$ini->_params["INFO"]["host_name"]}')")."
		
		</td>
	<td valign='top'>
	<table class=table_form style='width:100%'>
	<tr>
	<td colspan=2><H3>$list</H3></td>	
	</tr>
	<tr>
	<td colspan=2 align='right'>" . imgtootltip('ed_delete.gif','{delete}',"MailManDeleteList('$list','{$ini->_params["INFO"]["host_name"]}')")."</td>
	</tr>
	<tr>
		<td class=legend>{domain}:</td>
		<td><strong>{$ini->_params["INFO"]["host_name"]}</strong></td>
	</tr>
	<tr>
		<td class=legend nowrap>{subject_prefix}:</td>
		<td><strong>{$ini->_params["INFO"]["subject_prefix"]}</strong></td>
	</tr>
	<tr>
		<td class=legend>Admin:</td>
		<td nowrap><strong>{$ini->_params["INFO"]["owner"]}</strong></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>		
	
	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mailman.lists.php');	
	
}


function popup_save(){
	$mailman=new mailmanctl();
	$mailman->DEFAULT_URL_PATTERN=$_GET["DEFAULT_URL_PATTERN"];
	$mailman->PUBLIC_ARCHIVE_URL=$_GET["PUBLIC_ARCHIVE_URL"];
	$mailman->Save();
	
}




function popup_addlist(){
	$tpl=new templates();
	$listname=strtolower($_GET["listname_add"]);
	$domain=$_GET["domain"];
	$adminmail=$_GET["adminmail"];
	$urlhost=$_GET["urlhost"];
	$emailhost=$_GET["emailhost"];

	$ldap=new clladp();
	$uid=$ldap->uid_from_email($adminmail);
	
	if($uid==null){
		echo $tpl->_ENGINE_parse_body("{mailman_admin_not_exists}",'mailman.lists.php');	
	}
	
	$users=new user($uid);
	$password=$users->password;
	$sock=new sockets();
	$sock->getfile("MailManAddList:$listname;$urlhost;$domain;$adminmail;$password");
	
}


class mailmanctl{
	
	var $DEFAULT_URL_PATTERN=null;
	var $PUBLIC_ARCHIVE_URL=null;
	var $AutoCreateAccountIPArray;
	
	function mailmanctl(){
		$sock=new sockets();
		$this->DEFAULT_URL_PATTERN=$sock->GET_INFO("MailManDefaultUriPattern");
		$this->PUBLIC_ARCHIVE_URL=$sock->GET_INFO("MailManDefaultArchiveUri");
		
		
		if($this->DEFAULT_URL_PATTERN==null){
			$this->DEFAULT_URL_PATTERN="https://%s/cgi-bin/mailman/";
			$this->PUBLIC_ARCHIVE_URL="https://%(hostname)s/pipermail/%(listname)s";
			$this->Save(1);	
			$httpd=new httpd();
			$httpd->SaveToServer();					
			}
			
		if($this->PUBLIC_ARCHIVE_URL==null){
			$this->PUBLIC_ARCHIVE_URL="https://%(hostname)s/pipermail/%(listname)s";
			$this->Save(1);	
		}
		
		$this->BuildBranch();
		
	}
	
	
	function Save($silent=0){
		$sock=new sockets();
		$sock->SET_INFO("MailManDefaultUriPattern",$this->DEFAULT_URL_PATTERN);
		$sock->SET_INFO("MailManDefaultArchiveUri",$this->PUBLIC_ARCHIVE_URL);
		$sock->getFrameWork('cmd.php?MailManSaveGlobalSettings=yes');
		
		
		
		
		$tpl=new templates();
		if($silent==0){
			echo $tpl->_ENGINE_parse_body("{success}","postfix.index.php");
		}
		
	}
	
	
	function BuildBranch(){
		$ldap=new clladp();
		$dn="cn=mailman,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='PostFixStructuralClass';
			$upd["cn"][]="mailman";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		}
		
		$dn="cn=robots,cn=mailman,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='PostFixStructuralClass';
			$upd["cn"][]="robots";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		}		
	}
	
	
	function BuildRobots($list,$domain){
		$array[]="admin";
		$array[]="bounces";
		$array[]="confirm";
		$array[]="join";
		$array[]="leave";
		$array[]="owner";
		$array[]="request";
		$array[]="subscribe";
		$array[]="unsubscribe";
		$tpl=new templates();
		$ldap=new clladp();
		$dn="cn=$list@$domain,cn=robots,cn=mailman,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='ArticaMailManRobots';
			$upd["MailManAliasPath"][]="\"|/var/lib/mailman/mail/mailman post $list\"";
			$upd["TransportMailmanMaps"][]='mailman:';
			$upd["cn"][]="$list@$domain";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
			echo $tpl->_ENGINE_parse_body("{success}:$list\n");
		}else{
			unset($upd);
			echo $tpl->_ENGINE_parse_body("{success}:$list\n");
		}
		
		while (list ($num, $ligne) = each ($array) ){
			$dn="cn=$list-$ligne@$domain,cn=robots,cn=mailman,cn=artica,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='ArticaMailManRobots';
				$upd["MailManAliasPath"][]="\"|/var/lib/mailman/mail/mailman $ligne $list\"";
				$upd["cn"][]="$list-$ligne@$domain";
				$upd["TransportMailmanMaps"][]='mailman:';
				if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
				echo $tpl->_ENGINE_parse_body("{success}:$list-$ligne\n");
				unset($upd);
			}else{
				echo $tpl->_ENGINE_parse_body("{success}:$list-$ligne\n");
				unset($upd);
			}

		}
		
		$main=new main_cf();
		$main->save_conf();
		$main->save_conf_to_server();
		}
		
	function DeleteRobot($list,$domain){
		$array[]="admin";
		$array[]="bounces";
		$array[]="confirm";
		$array[]="join";
		$array[]="leave";
		$array[]="owner";
		$array[]="request";
		$array[]="subscribe";
		$array[]="unsubscribe";
		
		$ldap=new clladp();
		$dn="cn=$list@$domain,cn=robots,cn=mailman,cn=artica,$ldap->suffix";
		if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn);}
		
		while (list ($num, $ligne) = each ($array) ){
			$dn="cn=$list-$ligne@$domain,cn=robots,cn=mailman,cn=artica,$ldap->suffix";		
			if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn);}
			}
		}

}

?>