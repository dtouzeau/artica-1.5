<?php
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.users.menus.inc");
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");

if(isset($_GET["accordion"])){accordion();exit;}
if(isset($_GET["tab-acc"])){tab_accordion();exit;}
if($_GET["accordion-content"]=="members"){accordion_content_members();exit;}
if($_GET["accordion-content"]=="myaccount"){accordion_content_myaccount();exit;}
if($_GET["accordion-content"]=="messaging"){accordion_content_messaging();exit;}
if($_GET["accordion-content"]=="mymessaging"){accordion_content_Mymessaging();exit;}
if(isset($_GET["BodyToolbox"])){BodyToolbox();exit;}
if(isset($_GET["choose-language"])){choose_language();exit;}
if(isset($_POST["miniconfig-POST-lang"])){choose_language_save();exit();}
if(isset($_GET["center-panel"])){center_panel();exit;}


build();

function accordion_content_messaging($return=false){
	$page=CurrentPageName();
	$tpl=new templates();
	$users=new usersMenus();
	$ouencoded=urlencode(base64_encode($_SESSION["ou"]));
	
		$transport=Paragraphe_miniadm("folder-transport-48.png",
		"{localdomains}","{localdomains_text}",
		"Loadjs('domains.edit.domains.php?js=yes&ou=$ouencoded&encoded=yes&in-front-ajax=yes')");
		
		$quarantine_admin=Paragraphe_miniadm("folder-quarantine-extrainfos-48.png",
		"{quarantine_manager}",
		"{quarantine_manager_text}","javascript:LoadAjax('BodyContent','domains.quarantine.php?js={$_SESSION["ou"]}&inline=yes')");		
	
		if(!$users->AllowChangeDomains){
			$transport=Paragraphe_miniadm("folder-transport-48-grey.png",
			"{localdomains}","{localdomains_text}",
			"");			
		}

		if(!$users->AsQuarantineAdministrator){
			$quarantine_admin=Paragraphe_miniadm("folder-quarantine-extrainfos-48-grey.png",
			"{quarantine_manager}",
			"{quarantine_manager_text}","");
		}
	
		
		
	$html=$transport.$quarantine_admin;
	$html=$tpl->_ENGINE_parse_body($html);
	if($return){return $html;}
	echo $html;
}

function build(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	echo "
	<script>
		LoadAjax('left-menus','$page?accordion=yes');
		YahooWinHide();
	</script>
	
	
	";
}

function BodyToolbox(){
	include_once(dirname(__FILE__)."/ressources/class.html.tools.inc");
	$page=CurrentPageName();
	$tpl=new templates();
	$html=new htmltools_inc();
	$lang=$html->LanguageArray();		
	$u=new user($_SESSION["uid"]);
		$connected="{connected_has}:&nbsp;$u->uid <a href=\"javascript:blur();\" 
			OnClick=\"javascript:Minilogoff();\"
			style='text-decoration:underline;font-size:12px'
			>{disconnect}</a>";
		
		$empty="<a href=\"javascript:blur();\" 
			OnClick=\"javascript:CacheOff();\"
			style='text-decoration:underline;font-size:12px'
			>";
	echo $tpl->_ENGINE_parse_body("
		<a href=\"javascript:blur();\" OnClick=\"javascript:RefreshCenterPanel()\">
		<H1 style='margin-bottom:3px'>{organization}:{$_SESSION["ou"]}</H1></a><div style='font-size:12px'>($connected)
		&nbsp;|&nbsp;$empty{empty_cache}</a><div id='tool-map'></div></div>
		
		<script>
			var uid='{$_SESSION["uid"]}';
			function Minilogoff(){
				MyHref('/miniadm.logoff.php');
			}
			
			function RefreshCenterPanel(){
				LoadAjax('BodyContent','$page?center-panel=yes');
			
			}
			if(uid=='-100'){Minilogoff();}
			document.title='Artica ({$lang[$_COOKIE["artica-language"]]}) :: {organization}:{$_SESSION["ou"]} :: $u->uid'; 
			RefreshCenterPanel();
		</script>
		");
	
}

function accordion(){
	$page=CurrentPageName();
	$tpl=new templates();
	$users=new usersMenus();
	
	$array["myaccount"]="{myaccount}";
	$content["myaccount"]=accordion_content_myaccount(true);
	
	
	if($users->POSTFIX_INSTALLED){
		$array["mymessaging"]="{mymessaging}";
		$content["mymessaging"]=accordion_content_messaging(true);
	}
	
	if($users->AsOrgAdmin){$array["members"]="{members}";}
	if($users->POSTFIX_INSTALLED){
		if($users->AsMessagingOrg){$array["messaging"]="{messaging_org}";}
	}
	
	
	
	
	
	
	$cc=0;
	while (list ($num, $val) = each ($array) ){
		
		$jsBlockHide[]="document.getElementById('accordion-div-$num').style.display = 'none'";
		
		$cc++;
		$tr[]="
		<h3 class=\"ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top\">
		<span class=\"ui-icon ui-icon-triangle-1-e\"></span>
			<a href=\"javascript:blur();\" OnClick=\"javascript:MyAccordionSwitch('$num')\">$val</a>
		</h3>
		
	
			<div id='accordion-div-$num' class=\"ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active\" style='display:none'>
				<input type='hidden' id='accordion-$cc' value='$num'>
				<div style='height:auto;margin-left:-20px;margin-right:-20px' id='accordion-content-$cc'>{$content["$num"]}</div>
			</div>
		";
		

		
	}
	
	
	$html="
	<div id='accordion' style='overflow-x: hidden' class='ui-accordion ui-widget ui-helper-reset'>". @implode("\n",$tr)."

</div>	

 <script>
	function LoadMyAccordion(){
		MyAccordionSwitch('myaccount');
  	}

	function MyAccordionSwitch(key){
		". @implode("\n", $jsBlockHide)."
		document.getElementById('accordion-div-'+key).style.display = 'block'; 
		
	}
  
  
  LoadAjax('BodyToolbox','$page?BodyToolbox=yes');
  LoadMyAccordion();
  
  
  </script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function tab_accordion(){
	
	
}

function accordion_content(){
	
	
}

function accordion_content_members(){
	
	$usersmenus=new usersMenus();
	$ou_encoded=base64_encode($_SESSION["ou"]);
	$find_members=Paragraphe_miniadm('find-members-48.png','{find_members}','{find_members_text}',"LoadAjax('BodyContent','domains.manage.org.index.php?org_section=users&SwitchOrgTabs=$ou_encoded&ou=$ou_encoded&mem=yes');");		
	if(($usersmenus->AllowAddUsers) OR ($usersmenus->AsOrgAdmin) OR ($usersmenus->AsMessagingOrg)){	
		$adduser=Paragraphe_miniadm("folder-useradd-48.png","{add_user}","{add_user_text}","Loadjs('domains.add.user.php?ou={$_SESSION["ou"]}')");
		$groups=Paragraphe_miniadm('folder-group-48.png','{manage_groups}','{manage_groups_text}',"Loadjs('domains.edit.group.php?ou=$ou_encoded&js=yes')");
		
	}
	
	echo $adduser.$groups.$find_members."
	<script>
		LoadAjax('BodyContent','domains.manage.org.index.php?org_section=users&SwitchOrgTabs=$ou_encoded&ou=$ou_encoded&mem=yes');
	</script>
	
	";
}

function accordion_content_Mymessaging(){
	$events=Paragraphe_miniadm("48-mailevents.png",
	"{messaging_events}","{messaging_events_text}",
	"LoadAjax('BodyContent','miniamd.user.rtmm.php')");	
	
	//48-spam-grey.png
	
	
	echo $events;
}

function accordion_content_myaccount($return=false){
	include_once(dirname(__FILE__)."/ressources/class.user.inc");
	include_once(dirname(__FILE__)."/ressources/class.html.tools.inc");
	$tpl=new templates();
	$u=new user("{$_SESSION["uid"]}");
	$page=CurrentPageName();
	$dn=urlencode(base64_encode($u->dn));
	$ou_encoded=base64_encode($_SESSION["ou"]);
	$htmltools=new htmltools_inc();
	$sock=new sockets();
	$lang=$htmltools->LanguageArray();	
	$current=$lang[$_COOKIE["artica-language"]];
	$langage=Paragraphe_miniadm("language-48.png",
	"{language}","{change_the_webconsole_language_text}<br><strong>$current</strong>",
	"YahooWin2('320','$page?choose-language=yes','{language}:$current')");
	
	
	$myaccount=Paragraphe_miniadm("identity-48.png",
	"{myaccount}","{myaccount_text}",
	"LoadAjax('BodyContent','domains.edit.user.php?userid={$_SESSION["uid"]}&ajaxmode=yes&dn=$dn')");	
	
	
	$adressebook=Paragraphe_miniadm("48-addressbook.png",
	"{my_address_book}","{my_address_book_text}",
	"LoadAjax('BodyContent','my.addressbook.php')");	
	
	$users=new usersMenus();
	if($users->OPENVPN_INSTALLED){
		$show=false;
		if($users->AllowOpenVPN){$show=true;}
		if($sock->GET_INFO("EnableOpenVPNEndUserPage")==1){$show=true;}
		if($show){
			$openvpn_client=Paragraphe_miniadm("42-openvpn.png",
			"{my_vpn_cnx}","{my_vpn_cnx_text}",
			"LoadAjax('BodyContent','miniadm.openvpn.client.php')");	
		}
		
	}
	
	//
	
	
	$html=$langage.$myaccount.$adressebook.$openvpn_client;
	$html=$tpl->_ENGINE_parse_body($html);
	if($return){return "$html";}
	echo "
	$html
	<script>
			LoadAjax('BodyContent','domains.edit.user.php?userid={$_SESSION["uid"]}&ajaxmode=yes&dn=$dn');
	</script>
	";
}

function choose_language(){
	include_once(dirname(__FILE__)."/ressources/class.html.tools.inc");
	$tpl=new templates();
	$htmltools=new htmltools_inc();
	$lang=$htmltools->LanguageArray();	
	$page=CurrentPageName();
	
	$lang[null]="{select}";
	$html="<table style='width:100%' class=form>
	<tr>
		<td valign='top'>{language}: ($tpl->language)</td>
		<td>". Field_array_Hash($lang,"miniconfig-select-lang",$tpl->language,"style:font-size:16px;")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>". button("{apply}","ChangeLang()")."</td>
	</tr>
	</table>
	
	<script>
	
	var x_ChangeLang= function (obj) {
		var response=obj.responseText;
		location.reload();
	}	
	
	function ChangeLang(){
		var lang=document.getElementById('miniconfig-select-lang').value;
		Set_Cookie('artica-language', lang, '3600', '/', '', '');
		var XHR = new XHRConnection();
		XHR.appendData('miniconfig-POST-lang',lang);
		XHR.sendAndLoad('$page', 'POST',x_ChangeLang);		
		location.reload();
	}
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function choose_language_save(){
	session_start();
	$_SESSION["detected_lang"]=$_POST["miniconfig-POST-lang"];
	writelogs("Unset array of ".count($_SESSION["translation"]),__FUNCTION__,__FILE__,__LINE__);
	unset($_SESSION["translation"]);
	writelogs("-> remove cached",__FUNCTION__,__FILE__,__LINE__);
	REMOVE_CACHED(null);
	writelogs("-> setcookie",__FUNCTION__,__FILE__,__LINE__);
	setcookie("artica-language", $_POST["miniconfig-POST-lang"], time()+172800);
	$tpl=new templates();	
}

function center_panel(){
	
	$users=new usersMenus();
	$tpl=new templates();
	$u=new user($_SESSION["uid"]);
	$mydn=base64_encode($u->dn);
	$info_right[]="	<table style='width:98%' class=form>
	<tr>
		<td valign='top' width=1%><img src='img/webservices-128.png'></td>
		<td valign='top'><H3 style='font-weight:bold'>{myWebServices}</H3>
			<ul>
			<li><a href=\"javascript:blur()\" 
				OnClick=\"javascript:Loadjs('miniadm.www.services.php');\" 
				style='font-size:13px;font-weight:normal'>{myWebServices_text}</a>
			</li>
			</ul>	
			
		
		</td>
	</tr>
	</table>";
	
	$info_left[]="	<table style='width:98%' class=form>
	<tr>
		<td valign='top' width=1%><img src='img/identity-128.png'></td>
		<td valign='top'><H3 style='font-weight:bold'>{myaccount}</H3>
			<ul>
			<li><a href=\"javascript:blur()\" 
				OnClick=\"javascript:LoadAjax('BodyContent','domains.edit.user.php?userid={$_SESSION["uid"]}&ajaxmode=yes&dn=$mydn');\" 
				style='font-size:13px;font-weight:normal'>{myaccount_text}</a>
			</li>
			</ul>	
			
		
		</td>
	</tr>
	</table>";	
	
	if($users->AllowAddUsers){
		$info_left[]=info_organization();
	}
	if($users->AllowChangeDomains){
		$info_right[]=info_messaging();
	}
	
	if(($users->AsDansGuardianAdministrator) OR ($users->AsWebFilterRepository)){
		$info_left[]=info_Dansguardian();
		
	}
	
	
	
	
	//www-128.png
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=50%>".@implode("<br>",$info_left)."</td>
		<td valign='top' width=50%>".@implode("<br>",$info_right)."</td>
	</tr>
	</table>
	<script>
	LoadAjax('tool-map','miniadm.toolbox.php?script=center-panel');
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function info_messaging(){
	$ldap=new clladp();
	$users=new usersMenus();
	$usersNB=$ldap->CountDeDomainsOU($_SESSION["ou"]);
	$ouencoded=base64_encode($_SESSION["ou"]);
return "
	<table style='width:98%' class=form>
	<tr>
		<td valign='top' width=1%><img src='img/128-catch-all.png' OnClick=\"javascript:Loadjs('domains.edit.domains.php?js=yes&ou=$ouencoded&encoded=yes&in-front-ajax=yes');></td>
		<td valign='top'><H3 style='font-weight:bold'>{messaging}: $usersNB {domains}</H3>
			<ul>
			<li><a href=\"javascript:blur()\" 
				OnClick=\"javascript:Loadjs('domains.edit.domains.php?js=yes&ou=$ouencoded&encoded=yes&in-front-ajax=yes');\" 
				style='font-size:13px;font-weight:normal'>{localdomains_text}</a>
			</li>
			</ul>	
			
		
		</td>
	</tr>
	</table>
	";	
	
	
}

function info_Dansguardian(){

return "
	<table style='width:98%' class=form>
	<tr>
		<td valign='top' width=1%><img src='img/www-web-secure-128.png' OnClick=\"javascript:Loadjs('miniadm.webfiltering.index.php');\" ></td>
		<td valign='top'><H3 style='font-weight:bold'>{WEB_FILTERING}</H3>
			<ul>
			<li><a href=\"javascript:blur()\" 
				OnClick=\"javascript:Loadjs('miniadm.webfiltering.index.php');\" 
				style='font-size:13px;font-weight:normal'>{miniadm_web_filtering_text}</a>
			</li>
			</ul>	
			
		
		</td>
	</tr>
	</table>
	";		
	
}

function info_organization(){
	$ldap=new clladp();
	$usersNB=$ldap->CountDeUSerOu($_SESSION["ou"]);
	
	return "
	<table style='width:98%' class=form>
	<tr>
		<td valign='top' width=1%><img src='img/users-info-128.png' OnClick=\"javascript:Loadjs('domains.add.user.php?ou={$_SESSION["ou"]}');\" ></td>
		<td valign='top'><H3 style='font-weight:bold'>{$_SESSION["ou"]}: $usersNB {members}</H3>
			<ul>
			<li><a href=\"javascript:blur()\" 
				OnClick=\"javascript:Loadjs('domains.add.user.php?ou={$_SESSION["ou"]}');\" 
				style='font-size:13px;font-weight:normal'>{add_user}</a>
			</li>
			</ul>	
			
		
		</td>
	</tr>
	</table>
	";
	
	
	
}





?>