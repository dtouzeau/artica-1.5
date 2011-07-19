<?php
//http://www.alexa.com/siteowners/widgets/sitestats?
include_once('ressources/class.templates.inc');
include_once('ressources/class.ini.inc');
include_once('ressources/class.user.inc');
if(isset($_GET["tab"])){switch_tabs();exit;}
if(isset($_GET["Newtab"])){add_tab();exit;}
if(isset($_GET["delete-tab"])){delete_tab();exit;}
if(isset($_GET["DeleteTabConfirmed"])){delete_tab_confirmed();exit;}
if(isset($_GET["rebuild-icons"])){rebuildicons();exit;}
if(isset($_GET["add-icon"])){main_icon_js();exit;}
if(isset($_GET["show-icons"])){main_icon_list();exit;}
if(isset($_GET["new_icon"])){add_icon();exit;}
if(isset($_GET["delete_icon"])){del_icon();exit;}
if(isset($_GET["ChangeClass"])){echo main_icon_list_list();exit;}
if(isset($_GET["manage-icon"])){echo manage_icons_js();exit;}
if(isset($_GET["show-manage"])){echo manage_icon_page();exit;}
if(isset($_GET["move-widget"])){echo manage_icon_move();exit;}
if(isset($_GET["widget-manage-list"])){echo manage_icons_list($_GET["widget-manage-list"]);exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["tab-js"])){tabjs();exit;}
if(isset($_GET["DeleteInterface"])){DeleteInterface();exit;}

js();
exit;

function DeleteInterface(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsSystemAdministrator){
		echo $tpl->_ENGINE_parse_body(html_entity_decode('{ERROR_NO_PRIVS'));
		exit;
	}

	$u=new user($_GET["uid"]);
	$u->UsersInterfaceDatasDelete();
	}

function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);	
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsSystemAdministrator){
		echo "alert('".$tpl->_ENGINE_parse_body(html_entity_decode('{ERROR_NO_PRIVS'))."');";
		exit;
	}
	
	$uid=$_GET["uid"];
	$title=$tpl->_ENGINE_parse_body("$uid:: {user_interface}");
	$titletab=replace_accents(html_entity_decode($tpl->_ENGINE_parse_body("$uid:: {ADD_NEW_TAB_ASK}",'admin.tabs.php')));
	$error_want_operation=replace_accents((html_entity_decode($tpl->_ENGINE_parse_body('{error_want_operation}'))));
	
	$html="var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;
	var {$prefix}memtab='';	


	function {$prefix}LoadPage(){
		RTMMail(750,'$page?popup=$uid','$title');
	}
	
	var x_UserAddTab= function (obj) {
		var tempvalue=obj.responseText;
			if(tempvalue.length>0){
				alert(tempvalue);
				return;
			}	
			
			Loadjs('$page?tab-js=index&uid={$_GET["uid"]}')
	}	


	var x_DeleteAllUserInterface= function (obj) {
		var tempvalue=obj.responseText;
			if(tempvalue.length>0){
				alert(tempvalue);
				return;
			}	
			
			RTMMailHide();
	}	

function DeleteAllUserInterface(){
	if(confirm('$error_want_operation')){
			var XHR = new XHRConnection();
     		XHR.appendData('DeleteInterface','yes');
     		XHR.appendData('uid','$uid');
     		document.getElementById('emule-page').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     		XHR.sendAndLoad('$page', 'GET',x_DeleteAllUserInterface);	
	
	}
}

	
	function UserAddTab(){
		var tabname=prompt('$titletab');
		if(tabname){
			
			var XHR = new XHRConnection();
     		XHR.appendData('Newtab',tabname);
     		XHR.appendData('uid','$uid');
     		document.getElementById('emule-page').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     		XHR.sendAndLoad('$page', 'GET',x_UserAddTab);	
		
		}
	
	}
	
	{$prefix}LoadPage();";

	echo $html;
	}
	

function popup(){
	$uid=$_GET["popup"];
	if(!isset($_GET["tab"])){$tab="index";}

$html="<H1>{user_interface}</H1>
<p class=caption>{user_interface_text}</p>
<div style='text-align:right;padding:5px;'><input type='button' OnClick=\"javascript:DeleteAllUserInterface();\" value='{delete}&nbsp;&raquo;'></div>

<div id='emule-page'>". perso_page($tab)."</div>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'admin.tabs.php');
	
	
	
}


function GetTAB(){
if(isset($_GET["popup"])){$_GET["uid"]=$_GET["popup"];}

$array["index"]="{index}";
$array=$array+get_perso_tabs();

$page=CurrentPageName();
if($_GET["tab_current"]==null){$_GET["tab_current"]="frontend";}



	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab_current"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:Loadjs('$page?tab-js=$num&uid={$_GET["uid"]}')\" $class {$style[$num]}>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body("<div id=tablist>$html</div><br>");		
	
}
	
function rebuildicons(){
	$tab=$_GET["rebuild-icons"];
	$uid=$_GET["uid"];
	$cache_tab_file="ressources/profiles/$uid.$tab";
	@unlink($cache_tab_file);
	$page=CurrentPageName();
	echo "Loadjs('$page?tab-js=$tab&uid={$_GET["uid"]}');";
	
}
	
function get_perso_tabs(){
	$uid=$_GET["uid"];
	$ini=new Bs_IniHandler();
	if($uid<>"__SESSION__"){
		$users=new user($uid);
		$ini->loadString($users->UsersInterfaceDatas);
	}else{
		$ini->loadString($_SESSION["UsersInterfaceDatas"]);
	}
	
	$tabs=$ini->_params;
	if(!is_array($tabs)){return array();}
	while (list ($num, $ligne) = each ($tabs) ){
		if($num==null){continue;}
		$array[$num]=$ligne["name"];
		
	}
if(!is_array($array)){return array();}
	return $array;
}

function add_tab(){
	$uid=$_GET["uid"];
	$tab=md5($_GET["Newtab"]);
	$ini=new Bs_IniHandler();
	$users=new user($uid);
	$ini->loadString($users->UsersInterfaceDatas);
	$ini->_params[$tab]["name"]=$_GET["Newtab"];
	$users->UsersInterfaceDatasSave($ini->toString());
	}

function switch_tabs(){
	if(trim($_GET["tab"]==null)){die();}
	switch ($_GET["tab"]){
		case "frontend":main_admin();break;
		case "add-tab":main_add_tab();exit;break;
		default:echo perso_page($_GET["tab"]);
	}
	
}

function delete_tab(){
	$page=CurrentPageName();
	$tpl=new templates();
	$text=replace_accents((html_entity_decode($tpl->_ENGINE_parse_body('{error_want_operation}'))));
	$uid=$_GET["uid"];
	
$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsSystemAdministrator){
		echo "alert('".$tpl->_ENGINE_parse_body(html_entity_decode('{ERROR_NO_PRIVS'))."');";
		exit;
	}
		
	
	$html="
	var x_DeleteUserTab= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		Loadjs('$page?tab-js=index&uid=$uid')
		}
		
	function DeleteUserTab(){
			if(confirm('$text')){
				var XHR = new XHRConnection();
     			XHR.appendData('DeleteTabConfirmed','{$_GET["delete-tab"]}');
     			XHR.appendData('uid','$uid');
     			document.getElementById('emule-page').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";                               		      	
     			XHR.sendAndLoad('$page', 'GET',x_DeleteUserTab);	
				}
		}
	DeleteUserTab();";
		
echo $html;		
	
}

function main_admin(){
	$page=CurrentPageName();
	
	echo "LoadAjax('BodyContent','admin.index.php?admin-ajax=yes');
	setTimeout(\"AdminIndexChargeFunctions()\",900);
	
	function AdminIndexChargeFunctions(){
		ChargeLogs();
		Demarre_right();demarre();LoopRight();CheckDaemon();LoadMasterTabs();
	
	}
	
function switch_tab(num,hostname){
	var uri='admin.index.php?main='+ num +'&hostname='+hostname;
	Delete_Cookie('ARTICA-INDEX-ADMIN-TAB', '/', '');
	Set_Cookie('ARTICA-INDEX-ADMIN-TAB', uri, '3600', '/', '', '');
	LoadAjax('events',uri)
}	
	
	";}
	
function main_add_tab(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ask=$tpl->_parse_body('{ADD_NEW_TAB_ASK}');
	
	$html="
	
var x_MainAdminAddTab= function (obj) {
var tempvalue=obj.responseText;
	if(tempvalue.length>0){
		alert(tempvalue);
	}	
	
	LoadMasterTabs();
	
}
	
	function MainAdminAddTab(){
		var tabname=prompt('$ask');
		if(tabname){
			var XHR = new XHRConnection();
     		XHR.appendData('Newtab',tabname);
     		document.getElementById('emule-page').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     		XHR.sendAndLoad('$page', 'GET',x_MainAdminAddTab);	
		
		}
	
	}
	
	MainAdminAddTab();
";
	
	
echo $html;	
}

function perso_page($tab){
	$uid=$_GET["uid"];
	return BuildCacheTab($tab);
}

function BuildCacheTab($tab){
	$uid=$_GET["uid"];
	if(isset($_GET["popup"])){$uid=$_GET["popup"];}
	if($uid=="__SESSION__"){$uid=$_SESSION["uid"];}
	
	$page=CurrentPageName();
	$cancel=Paragraphe("64-cancel.png","{DELETE_THIS_TAB}","{DELETE_THIS_TAB_TEXT}","javascript:Loadjs('users.tabs.php?delete-tab=$tab&uid=$uid');");
	$add=Paragraphe("64-circle-plus.png","{ADD_WIDGET}","{ADD_WIDGET_TEXT}","javascript:Loadjs('users.tabs.php?add-icon=$tab&uid=$uid');");	
	$settings=Paragraphe('64-widget-manage.png','{MANAGE_WIDGETS}','{MANAGE_WIDGETS_TEXT}',"javascript:Loadjs('users.tabs.php?manage-icon=$tab&uid=$uid');");
	$rebuild=Paragraphe("64-refresh.png","{REBUILD_PAGE}","{REBUILD_PAGE_TEXT}","javascript:Loadjs('users.tabs.php?rebuild-icons=$tab&uid=$uid');");
	
	$users=new user($uid);
	$ini=new Bs_IniHandler();
	$ini->loadString($users->UsersInterfaceDatas);
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	
	$count=0;
	$ico=new deficons($uid);
	
	while (list ($num, $ligne) = each ($icons) ){
		if($ligne==null){continue;}
		$icon_s[$ligne]=$ligne;
	}
	
if(is_array($icon_s)){
	while (list ($num, $ligne) = each ($icon_s) ){
		if($count==3){
			$t=$t."</tr><tr>";
			$count=0;
		}
		$t=$t."<td valign='top'>".$ico->BuildIconUsers($ligne)."</td>";
		$count=$count+1;
		
	}
}

	$b1=Paragraphe("folder-interface-64.png","{add_a_new_tab}","{add_a_new_tab_text}","javascript:UserAddTab()");
	$page=CurrentPageName();
	$cancel=Paragraphe("64-cancel.png","{DELETE_THIS_TAB}","{DELETE_THIS_TAB_TEXT}","javascript:Loadjs('$page?delete-tab=$tab&uid=$uid');");
	$add=Paragraphe("64-circle-plus.png","{ADD_WIDGET}","{ADD_WIDGET_TEXT}","javascript:Loadjs('$page?add-icon=$tab&uid=$uid');");
	if($tab=="index"){$cancel=$settings;}else{$b1=$settings;}
	
	$admin_section="	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>$b1</td>
		<td valign='top' width=1%>$add</td>
		<td valign='top' width=1%>$cancel</td>
	</tr>
	</table>";
	$usersAdmin=new usersMenus();
	if(!$usersAdmin->AsArticaAdministrator){$admin_section=null;}
	

$title=$ini->_params[$tab]["name"];
$tabs=GetTAB();

$html="
<br>
$tabs
<div style='width:100%;height:450px;overflow:auto'>
<table style='width:100%'>
<tr>
$t
</tr>
</tr>
</table>
$admin_section
</div>";

$tpl=new templates();

return $tpl->_ENGINE_parse_body($html,"admin.tabs.php");
}

function delete_tab_confirmed(){
	$tab=$_GET["DeleteTabConfirmed"];
	$uid=$_GET["uid"];
	$page=CurrentPageName();	
	$users=new user($uid);
	$ini=new Bs_IniHandler();
	$ini->loadString($users->UsersInterfaceDatas);
	unset($ini->_params[$tab]);
	$users->UsersInterfaceDatasSave($ini->toString());
	}

function tabjs(){
	$page=CurrentPageName();
	$html="
	document.getElementById('emule-page').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
	if(YahooWinOpen()){YahooWinHide();}
	LoadAjax('emule-page','$page?tab={$_GET["tab-js"]}&uid={$_GET["uid"]}');
	";
	echo $html;
}

function manage_icons_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsSystemAdministrator){
		echo "alert('".$tpl->_ENGINE_parse_body(html_entity_decode('{ERROR_NO_PRIVS'))."');";
		exit;
	}
		
	
	$title=$tpl->_ENGINE_parse_body("{WIDGETS_AREA}",'admin.tabs.php');	
	$uid=$_GET["uid"];
	$html="
	function MainAdminUWidgetManageLaunch(){
		YahooWin(500,'$page?show-manage=yes&icon={$_GET["manage-icon"]}&uid=$uid','$title');
	
	}	
	
var x_AddWidgetUIcon= function (obj) {	
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}	
	Loadjs('users.tabs.php?rebuild-icons={$_GET["manage-icon"]}&uid=$uid');
	}		
	
	function DelWidgetUIcon(icon_name){
		var XHR = new XHRConnection();
     	XHR.appendData('delete_icon',icon_name);
     	XHR.appendData('delete_icon_tab','{$_GET["manage-icon"]}');
     	XHR.appendData('uid','$uid');
     	document.getElementById('emule-page').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     	XHR.sendAndLoad('$page', 'GET',x_AddWidgetUIcon);
     	Loadjs('$page?rebuild-icons={$_GET["manage-icon"]}&uid=$uid');
     	MainAdminUWidgetManageLaunch();
	}	
	
	function WidgetUDown(num){
		LoadAjax('widgetlist','$page?move-widget='+num +'&icon-page={$_GET["manage-icon"]}&move=down&uid=$uid');
		Loadjs('$page?rebuild-icons={$_GET["manage-icon"]}&uid=$uid');
	}
	
	function WidgetUUp(num){
		LoadAjax('widgetlist','$page?move-widget='+num +'&icon-page={$_GET["manage-icon"]}&move=up&uid=$uid');
		Loadjs('$page?rebuild-icons={$_GET["manage-icon"]}&uid=$uid');
	}
	
	MainAdminUWidgetManageLaunch();
	";
	
	echo $html;
	
}

function manage_icon_move(){
	$tab=$_GET["icon-page"];
	$uid=$_GET["uid"];
	$ini=new Bs_IniHandler();
	$user=new user($uid);
	$ini->loadString($user->UsersInterfaceDatas);
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	
	$arrc=new tweak_array();
	if($_GET["move"]=='up'){
   		$icons2=$arrc->move($icons,$_GET["move-widget"]+1,$_GET["move-widget"]);
	}else{
		$icons2=$arrc->move($icons,$_GET["move-widget"],$_GET["move-widget"]+1);
	}
	
	reset($icons2);
	
	if(count($icons2)==0){
			echo "failed";
			echo manage_icons_list($tab);exit;
	}
	

while (list ($num, $ligne) = each ($icons2) ){
		if($ligne==null){continue;}
		$icon_s[$ligne]=$ligne;
	}

while (list ($num, $ligne) = each ($icon_s) ){
		if($ligne==null){continue;}
		$icon_t[]=$ligne;
	}	

$ini->_params[$tab]["icons"]=implode(',',$icon_t);
$user->UsersInterfaceDatasSave($ini->toString());
echo manage_icons_list($tab);
	
}

function manage_icon_page(){
	
	$tpl=new templates();
	
	$icons=manage_icons_list($_GET["icon"]);
	
	$html="<H1>{MANAGE_WIDGETS}</H1>
	".RoundedLightWhite("
	<div style='height:450px;overflow:auto' id='widgetlist'>$icons</div>");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'admin.tabs.php');
	
}

function manage_icons_list($tab){
	$uid=$_GET["uid"];
	$users=new user($uid);
	$ini=new Bs_IniHandler();
	$ini->loadString($users->UsersInterfaceDatas);
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	$count=0;
	$ico=new deficons($uid);
	
	while (list ($num, $ligne) = each ($icons) ){
		$icon_s[]=$ligne;
	}
	
	if(is_array($icon_s)){
		while (list ($num, $ligne) = each ($icon_s) ){
			if($ligne==null){continue;}
			$t=$t.$ico->BuildIconRowUser($ligne,$num);
		}
	}

	$html="<table style='width:100%'>$t</table>";
	
	return $html;

	
}




function main_icon_js(){

	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{WIDGETS_AREA}",'admin.tabs.php');
	$uid=$_GET["uid"];
	$html="
	function {$prefix}MainAdminWidgetSection(){
		YahooWin(700,'users.tabs.php?show-icons=yes&icon={$_GET["add-icon"]}&uid=$uid','$title');
	
	}
var x_AddWidgetUIcon= function (obj) {	
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}	
	Loadjs('users.tabs.php?rebuild-icons={$_GET["add-icon"]}&uid=$uid');
	AddIconUChangeClass();
	}	
	
	function AddWidgetUIcon(icon_name){
		var XHR = new XHRConnection();
     	XHR.appendData('new_icon',icon_name);
     	XHR.appendData('new_icon_tab','{$_GET["add-icon"]}');
     	XHR.appendData('uid','$uid');
     	document.getElementById('emule-page').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     	XHR.sendAndLoad('$page', 'GET',x_AddWidgetUIcon);	
	
	}
	
	function DelWidgetUIcon(icon_name){
		var XHR = new XHRConnection();
     	XHR.appendData('delete_icon',icon_name);
     	XHR.appendData('delete_icon_tab','{$_GET["add-icon"]}');
     	XHR.appendData('uid','$uid');
     	document.getElementById('BodyContent').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     	XHR.sendAndLoad('$page', 'GET',x_AddWidgetUIcon);	
	}
	
	function AddIconUChangeClass(){
		var class='';
		if(document.getElementById('class')){
			class=document.getElementById('class').value
		}
		LoadAjax('icons_users_listes','$page?ChangeClass='+class+'&icon={$_GET["add-icon"]}&uid=$uid');
		
	}
	
{$prefix}MainAdminWidgetSection();
";
	
	
echo $html;		
	
	

}

function add_icon(){
	$uid=$_GET["uid"];
	$tab=$_GET["new_icon_tab"];
	$page=CurrentPageName();	
	
	$users=new user($uid);

	$ini=new Bs_IniHandler();
	$ini->loadString($users->UsersInterfaceDatas);
	$icons=explode(",",$ini->_params[$_GET["new_icon_tab"]]["icons"]);
	$icons[]=$_GET["new_icon"];
	$ini->_params[$_GET["new_icon_tab"]]["icons"]=implode(",",$icons);
	$users->UsersInterfaceDatasSave($ini->toString());

}
function del_icon(){
	$uid=$_GET["uid"];
	$tab=$_GET["delete_icon_tab"];
	$icon=$_GET["delete_icon"];
	$page=CurrentPageName();	
	$users=new user($uid);
	$ini=new Bs_IniHandler();
	$ini->loadString($users->UsersInterfaceDatas);
	$icons=explode(",",$ini->_params[$tab]["icons"]);

	while (list ($num, $ligne) = each ($icons) ){
		if($ligne==$icon){
			unset($icons[$num]);
			}
	}
	
	
	$ini->_params[$tab]["icons"]=implode(",",$icons);
	$users->UsersInterfaceDatasSave($ini->toString());
	
}

function main_icon_list(){
	$uid=$_GET["uid"];
	if($uid<>"__SESSION__"){
		$ico=new deficons($uid);
	}else{
		$ico=new deficons($_SESSION["uid"]);
	}		
	

	$ico->categories_users[null]="{select}";
	$classes=Field_array_Hash($ico->array_icons_users,"class",$_GET["class"],"AddIconChangeClass()");
	
	
	
	$html="
	<input type='hidden' id='tabid' value='{$_GET["icon"]}'>
	<H1>{WIDGETS_AREA}</H1>
	<p class=caption>{WIDGETS_AREA_EXPLAIN}</p>
	<div id='icons_users_listes' style='width:100%;height:400px;overflow:auto'>".main_icon_list_list()."</div>
	";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'admin.tabs.php');
}

function main_icon_list_list(){
	$uid=$_GET["uid"];
	$ini=new Bs_IniHandler();
	
	if($uid<>"__SESSION__"){
		$ico=new deficons($uid);
		$users=new user($uid);
		$ini->loadString($users->UsersInterfaceDatas);
	}else{
		$ico=new deficons($_SESSION["uid"]);
		$ini->loadString($_SESSION["UsersInterfaceDatas"]);
	}	
	
	
	$tab=$_GET["icon"];
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	
	while (list ($num, $ligne) = each ($icons) ){
		if($ligne==null){continue;}
		$icon_s[$ligne]=true;
	}	

	if($_GET["ChangeClass"]==null){$_GET["ChangeClass"]="menu_user";}
	$icos=$ico->Build32_widgets_users();
	$array=$icos[$_GET["ChangeClass"]];


	while (list ($num, $ligne) = each ($array) ){
		
		if($count==3){
			$t=$t."</tr><tr>";
			$count=0;
		}
		if($icon_s[$num]){continue;}
	
		
		$t=$t."<td valign='top'>$ligne</td>";
		$count=$count+1;
		
		
	}	
	
$html="<table style='width:100%'>
	<tr>$t</tr>
	</table>";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
	
}



?>