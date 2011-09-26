<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["status"])){main_status();exit;}
	if(isset($_GET["SaveGeneralSettings"])){main_save();exit;}
	if(isset($_GET["DansGuardian_AddRuleName"])){main_rules_addnew();exit;}
	if(isset($_GET["DansGuardianRebuildDatabase"])){mysql_rebuild();exit;}
	if($_GET["main"]=="performances"){main_performances();exit;}
	if($_GET["main"]=="scanner"){main_scanner();exit;}
	if($_GET["main"]=="download"){main_download();exit;}
	if(isset($_GET["compile-ufdb-total"])){compile_ufdb_total();exit;}

	
	
	if(isset($_GET["category-create-new"])){main_rules_createcategory();exit;}
	if(isset($_GET["category-filed-list"])){main_rules_categories_fieldlist();exit;}
	if(isset($_GET["category-user-edit"])){main_rules_category_user_edit();exit;}
	if(isset($_GET["category-add-site"])){main_rules_category_user_add();exit;}
	if(isset($_GET["category-del-site"])){main_rules_category_user_del();exit;}
	
	
	

	if(isset($_GET["weighted-phrase-list-create-category"])){main_rules_createcategory_weight();exit;}
	
	if(isset($_GET["weighted-phrase-list-dropdown"])){main_rules_weightedphraselist_dropdown();exit;}
	if(isset($_GET["weighted-phrase-list-edit-category"])){main_rules_weightedphraselist_category_edit();exit;}
	if(isset($_GET["weighted-phrase-list-add-category-rule"])){main_rules_weightedphraselist_category_addwords();exit;}
	if(isset($_GET["weighted-phrase-list-category-list"])){main_rules_weightedphraselist_category_edit_list($_GET["weighted-phrase-list-category-list"]);exit;}
	if(isset($_GET["weighted-phrase-list-del-category-rule"])){main_rules_weightedphraselist_category_delete();exit;}
	

	if(isset($_GET["RulesSaveGeneralSettings"])){main_rules_SaveGeneralSettings();exit;}
	if(isset($_GET["ApplyDansGuardianSettings"])){main_rules_apply_conf();exit;}
	if(isset($_GET["deleteMasterRule"])){main_rules_delete();exit;}
	if(isset($_GET["find-rule"])){xfindrule();exit;}
	
	
	if(isset($_GET["js"])){popup_js();exit;}
	if(isset($_GET["dansguardian-popup"])){popup_tabs();exit;}
	if(isset($_GET["popup-index"])){popup_dansguardian_main();exit;}
	
	
	if(isset($_GET["popup-databases"])){popup_databases();exit;}
	
	
	if(isset($_GET["popup-rules"])){popup_rules();exit;}
	if(isset($_GET["pop-rules-list"])){echo popup_rules_list();exit;}
	if(isset($_GET["popup-rules-list"])){echo popup_rules_list();exit;}
	
	if(isset($_GET["popup-authentication"])){popup_authentication();exit;}
	
	
	if(isset($_GET["popup-restrictions"])){echo popup_rules_restrictions();exit;}
	if(isset($_GET["popup-files"])){echo popup_rules_files();exit;}
	if(isset($_GET["CompilePolicies"])){echo compile_js();exit;}
	if(isset($_GET["dansguardian-compile"])){echo compile_page();exit;}
	if(isset($_GET["compile-policies-1"])){compile_policies_1();EXIT;}
	if(isset($_GET["compile-policies-2"])){compile_policies_2();EXIT;}
	
	if(isset($_GET["popup-group-ip"])){ip_group_page();exit;}
	
	if(isset($_GET["add-address-step1"])){ip_group_wizard();exit;}
	if(isset($_GET["add-address-js"])){ip_group_js();exit;}
	if(isset($_GET["ip-group_list-rule"])){ip_group_list($_GET["ip-group_list-rule"]);exit;}
	if(isset($_GET["AddComputerToDansGuardian"])){ip_group_list_add_computer();exit;}
	
	if(isset($_GET["template"])){popup_template();exit;}
	
	
	if(isset($_POST["popup_template"])){popup_template_save();exit;}
	if(isset($_GET["template-options"])){template_options_js();exit;}
	if(isset($_GET["template-options-page"])){template_options_page();exit;}
	if(isset($_GET["DansGuardianEnableUserArticaIP"])){template_options_save();exit;}
	if(isset($_GET["dansguardian-rotate-logs"])){rotate_logs();exit;}
	if(isset($_GET["squid-restart-js"])){squid_restart_js();exit;}
	if(isset($_GET["squid-restart-perform"])){squid_restart_perform();exit;}
	
	
	if(isset($_GET["rule_main"])){main_rules_switch();exit;}
	
	
	
function squid_restart_js(){
$page=CurrentPageName();

$html="
	var x_squid_restart_start= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
	}	
	
	function squid_restart_start(){
			var XHR = new XHRConnection();
			XHR.appendData('squid-restart-perform','yes');
			XHR.sendAndLoad('$page', 'GET',x_squid_restart_start); 
	}
	

squid_restart_start()";
echo $html;
	
	
}

function squid_restart_perform(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squid-reconfigure=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{service_squid_restart_explain}");
	
}

	
function popup_js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_DANSGUARDIAN}');
$data=file_get_contents("js/dansguardian.js")."\n".file_get_contents("js/icap.js");
$create_new_cat=$tpl->_ENGINE_parse_body("{create_new_category_text}");
$rotate_logs=$tpl->_ENGINE_parse_body("{rotate_logs}");
$edit_your_weighted_category=$tpl->_ENGINE_parse_body('{edit_your_weighted_category}');

$startpage="LoadDansGuardianIntro();";

$html="
	var mem_rule_id='';
	var mem_cat='';
	var rule_main_mem='';
	function LoadDansGuardianIntro(){
	$('#BodyContent').load('$page?dansguardian-popup=yes&switch={$_GET["switch"]}');
	//YahooWinS(770,'$page?dansguardian-popup=yes&switch={$_GET["switch"]}','$title',''); 
	}	
	$data

	var x_ip_group_end_s= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		LoadAjax('ip_group_rule_list_'+mem_rule_id,'dansguardian.index.php?ip-group_list-rule='+mem_rule_id);
	}
	
	function ip_group_delete(rule_id,index){
	 	var XHR = new XHRConnection();
	 	mem_rule_id=rule_id;
	 	XHR.appendData('add-address-step1','yes');
		XHR.appendData('rule_id',rule_id);
		XHR.appendData('delete_index',index);
		XHR.sendAndLoad('dansguardian.index.php', 'GET',x_ip_group_end_s);  
	}

	var x_dansguardian_createCategory= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		if(document.getElementById('categories_field_list')){;
			LoadAjax('categories_field_list','dansguardian.index.php?category-filed-list=yes');
		}
	}	
	
	function dansguardian_createCategory(){
		var newcat=prompt('$create_new_cat');
		if(newcat){
			var XHR = new XHRConnection();
			XHR.appendData('category-create-new',newcat);
			XHR.sendAndLoad('dansguardian.index.php', 'GET',x_dansguardian_createCategory); 
		}
	}
	
	function dansguardian_edit_user_category(categoryname){
		YahooWin4('600','$page?category-user-edit='+categoryname,categoryname);
	}
	
	var x_AddUserWebSite= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		dansguardian_edit_user_category(mem_cat);
	}		
	
function AddUserWebSite(categoryname){
		mem_cat=categoryname;
		var web=document.getElementById('UserWebSite').value;
		var XHR = new XHRConnection();
		document.getElementById('main_rules_category_user_edit').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.appendData('category-add-site',web);
		XHR.appendData('category',categoryname);
		XHR.sendAndLoad('dansguardian.index.php', 'GET',x_AddUserWebSite);
		 		
	}

function main_rules_category_user_delete(category,num){
		mem_cat=category;
		var web=document.getElementById('UserWebSite').value;
		var XHR = new XHRConnection();
		document.getElementById('main_rules_category_user_edit').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.appendData('category-del-site',num);
		XHR.appendData('category',category);
		XHR.sendAndLoad('dansguardian.index.php', 'GET',x_AddUserWebSite);
	}
	
	var x_WeightedPhraseListAdd= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		LoadAjax('weightedphraselist_dropdown','dansguardian.index.php?weighted-phrase-list-dropdown=yes');
	}			

	function WeightedPhraseListAdd(){
		var category=prompt('$create_new_cat');
		if(category){
			var XHR = new XHRConnection();
			document.getElementById('weightedphraselist_dropdown').innerHTML='<center style=\"width:100%\"><img src=img/wait.gif></center>';
			XHR.appendData('weighted-phrase-list-create-category',category);
			XHR.sendAndLoad('dansguardian.index.php', 'GET',x_WeightedPhraseListAdd);			
		}
	}
	
	function dansguardian_edit_user_categoryWeight(category){
		YahooWin4('650','$page?weighted-phrase-list-edit-category='+category,'$edit_your_weighted_category');
	
	}
	
	var x_WeightedPhraseListAddCategoryRule= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		LoadAjax('main_rules_weightedphraselist_category_list','dansguardian.index.php?weighted-phrase-list-category-list='+mem_cat);
	}			
	
	function WeightedPhraseListAddCategoryRule(category){
		mem_cat=category;
		var words=document.getElementById('words').value;
		var score=document.getElementById('score').value;
		document.getElementById('main_rules_weightedphraselist_category_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';		
		var XHR = new XHRConnection();
		XHR.appendData('words',words);
		XHR.appendData('score',score);
		XHR.appendData('weighted-phrase-list-add-category-rule',category);
		XHR.sendAndLoad('dansguardian.index.php', 'GET',x_WeightedPhraseListAddCategoryRule);
	}
	
	function WeightedPhraseListDelCategoryRule(category,num){
		mem_cat=category;
		var XHR = new XHRConnection();
		XHR.appendData('weighted-phrase-list-del-category-rule',category);
		XHR.appendData('index',num);
		document.getElementById('main_rules_weightedphraselist_category_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('dansguardian.index.php', 'GET',x_WeightedPhraseListAddCategoryRule);
		}
		
	var x_DansGuardianRebuildDatabase= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		LoadDansGuardianIntro();
	}		
		
	function DansGuardianRebuildDatabase(){
		
		var XHR = new XHRConnection();
		XHR.appendData('DansGuardianRebuildDatabase','yes');
		document.getElementById('DansGuardianRebuildDatabase').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('dansguardian.index.php', 'GET',x_DansGuardianRebuildDatabase);
		}	
	function DansGuardianRotateLogs(){
		YahooWin3('600','$page?dansguardian-rotate-logs=yes','$rotate_logs');
	}

	
	
	$startpage";	
	
	echo $html;
	
}


function compile_js(){
	$tpl=new templates();
$page=CurrentPageName();	
$title=$tpl->_ENGINE_parse_body('{apply config} {APP_DANSGUARDIAN}');	
$html="


var x_step1=function(obj){
      document.getElementById('compile_dansguardian_step1').innerHTML=obj.responseText;
      step2();
	}

var x_step2=function(obj){
      document.getElementById('compile_dansguardian_step2').innerHTML=obj.responseText;
	}

	function step1(){
      var XHR = new XHRConnection();
      XHR.appendData('compile-policies-1','yes');
      document.getElementById('compile_dansguardian_step1').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
      XHR.sendAndLoad('dansguardian.index.php', 'GET',x_step1);     	
	
	}
	
	function step2(){
      var XHR = new XHRConnection();
      XHR.appendData('compile-policies-2','yes');
      document.getElementById('compile_dansguardian_step2').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
      XHR.sendAndLoad('dansguardian.index.php', 'GET',x_step2);     	
	
	}	

	function LoadCompileDansgardian(){
		YahooWin5(350,'$page?dansguardian-compile=yes','$title','');
		setTimeout(\"step1()\",1000); 
	}	

	LoadCompileDansgardian();";	
	
	echo $html;
	}
	
	
function compile_page(){
	
	$html="<H1>{apply config}</H1>
	<center>
	<div style='padding:3px;margin:3px;border:1px solid #CCCCCC;width:97%;height:200px;overflow:auto;'>
	<div id='compile_dansguardian_step1' style='text-align:left'></div>
	<div id='compile_dansguardian_step2' style='text-align:left'></div>
	</div>
	</center>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function compile_policies_1(){
	$tpl=new templates();
	$dans=new dansguardian();
	$dans->SaveSettings();
	$rules=$dans->Master_rules_index;
	echo $tpl->_ENGINE_parse_body('<div>{compiling} {APP_DANSGUARDIAN} '.count($rules).' {rules}</div>');
	echo "<hr>";
	$users=new usersMenus();
	echo $tpl->javascript_parse_text('<div>{compiling} {APP_DANSGUARDIAN} {success}</div>');
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?reload-dansguardian=yes");
	echo $tpl->javascript_parse_text('<div>{installing} {APP_DANSGUARDIAN} {parameters} {success}</div>');
}

function compile_policies_2(){
	$tpl=new templates();
	$squid=new squidbee();
	$squid->SaveToLdap();
	$squid->SaveToServer();
	echo $tpl->javascript_parse_text('<div>{compiling} {APP_SQUID} {success}</div>');
	
}

function popup_tabs(){
	
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["index"]='{index}';
	$array["rules"]='{rules}';
	$array["databases"]='{databases}';
	$array["authentication"]='{authentication}';
	$array["proxy-events"]='{events}';
	$sock=new sockets();
	$EnableUfdbGuard=$sock->GET_INFO('EnableUfdbGuard');
	if(!is_numeric($EnableUfdbGuard)){$EnableUfdbGuard=0;}
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="rules"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?popup-rules=yes&switch={$_GET["switch"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="index"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?popup-index=yes&switch={$_GET["switch"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="proxy-events"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"squid.artica.events.php\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		
		
		if($num=="databases"){
			if($users->APP_UFDBGUARD_INSTALLED){
				if($EnableUfdbGuard==1){
					$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"ufdbguard.databases.php\"><span>$ligne</span></a></li>\n");
					continue;
				}
			}
				
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?popup-databases=yes&switch={$_GET["switch"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}		

		if($num=="authentication"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"squid.webfilter.users.php?popup=yes&switch={$_GET["switch"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}			
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n");
		//$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	echo "
	<div id=dansguardian_main_config style='width:100%;height:730px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#dansguardian_main_config').tabs();
				});
		</script>";		
	
	
}

function popup_databases(){
	
	$page=CurrentPageName();
	$sock=new sockets();
	
	$dansguardian_enabled=$sock->GET_INFO("DansGuardianEnabled");
	if($dansguardian_enabled==null){$dansguardian_enabled=0;$sock->SET_INFO("DansGuardianEnabled",0);}
	$squidGuardEnabled=$sock->GET_INFO("squidGuardEnabled");
	if($squidGuardEnabled==null){$squidGuardEnabled=0;$sock->SET_INFO("squidGuardEnabled",0);}
	$cicap_enabled=$sock->GET_INFO('CicapEnabled');
	$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
	$EnableSquidClamav=$sock->GET_INFO("EnableSquidClamav");	
	
	
	$dansguardian_db=Paragraphe("spider-database-64.png","{DANSGUARDIAN_BLACKLISTS_STATUS}",'{DANSGUARDIAN_BLACKLISTS_STATUS_TEXT}',"javascript:Loadjs('dansguardian.db.status.php');");
	$dansguardian_update=Paragraphe("64-update-urls-database.png","{DANSGUARDIAN_BLACKLISTS_UPDATE}",'{DANSGUARDIAN_BLACKLISTS_UPDATE_TEXT}',"javascript:Loadjs('dansguardian.db.update.php');");
	$compile=Paragraphe('system-64.png','{apply_squid}','{apply_squid_text}',"javascript:Loadjs('$page?squid-restart-js=yes')");		
	$database_plus=Paragraphe('database-spider-plus.png','{shallalist}','{shallalist_text}',"javascript:Loadjs('shallalist.php')");
	$artica_community=Paragraphe('webfilter-community-64.png','{community}','{webfilter_community_text}',"javascript:Loadjs('webfilter.community.php')");
			
		if($EnableUfdbGuard==1){
			$database_check=Paragraphe('database-check.png','{databases_maintenance}','{databases_maintenance_text}',"javascript:Loadjs('urls.db.maint.php')");
		}	
		
		if($squidGuardEnabled==1){
			$compile_db=Paragraphe("compile-database-64.png","{compile_squidguard_databases}","{compile_squidguard_databases_text}","javascript:Loadjs('squidguard.status.php?compile=yes')");
			$database_check=Paragraphe('database-check.png','{databases_maintenance}','{databases_maintenance_text}',"javascript:Loadjs('urls.db.maint.php')");
			
		}	
	
	$tr[]=$compile;
	$tr[]=$compile_db;
	$tr[]=$database_check;
	$tr[]=$artica_community;
	$tr[]=$dansguardian_db;
	$tr[]=$database_plus;
	$tr[]=$dansguardian_update;		

	
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
	
	$html=$html.implode("\n",$tables);		  

	 
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html,"squid.newbee.php,squid.index.php");

echo $html;	
	
}


function popup_dansguardian_main(){
	
	
	
	$table=true;
	$mysql=new mysql();
	$mysqlSquid=new mysql_squid_builder();
	$users=new usersMenus();
	if(!$mysqlSquid->TABLE_EXISTS("dansguardian_categories","artica_backup")){
		$table=false;
	}
	if(!$mysql->TABLE_EXISTS("dansguardian_files","artica_backup")){
		$table=false;
	}	
	
	if(!$mysql->TABLE_EXISTS("dansguardian_rules","artica_backup")){
		$table=false;
	}	

	if(!$mysql->TABLE_EXISTS("dansguardian_weightedphraselist","artica_backup")){
		$table=false;
	}	

	if(!$mysql->TABLE_EXISTS("dansguardian_ipgroups","artica_backup")){
		$table=false;
	}	
	
	if(!$mysql->TABLE_EXISTS("dansguardian_whitelists","artica_events")){
		$table=false;
	}		
	
	if(!$table){
		popup_mysql_error();exit;
	}
	
	$page=CurrentPageName();
	$performances=Paragraphe("folder-tasks2-64.png","{performances}",'{performances_text}',"javascript:YahooWin(500,'$page?main=performances');");
	$content_scanner=Paragraphe("64-webscanner.png","{content_scanner}",'{content_scanner_text}',"javascript:YahooWin(651,'$page?main=scanner');");
	$download=Paragraphe("icon-download.gif","{download}",'{download_text}',"javascript:YahooWin(600,'$page?main=download');");
	$rules=Paragraphe("folder-rules2-64.png","{rules}",'{rules_text}',"javascript:YahooWin(600,'$page?popup-rules=yes');");
	$apply=applysettings_dansguardian();
	$squidguardweb=Paragraphe("parameters2-64-grey.png","{banned_page_webservice}","{banned_page_webservice_text}",null);
	
	$sock=new sockets();
	$logsize=trim($sock->getfile('DansGuardianLogSize'));
	$dansguardian_enabled=$sock->GET_INFO("DansGuardianEnabled");
	if($dansguardian_enabled==null){$dansguardian_enabled=0;$sock->SET_INFO("DansGuardianEnabled",0);}
	$squidGuardEnabled=$sock->GET_INFO("squidGuardEnabled");
	if($squidGuardEnabled==null){$squidGuardEnabled=0;$sock->SET_INFO("squidGuardEnabled",0);}
	$cicap_enabled=$sock->GET_INFO('CicapEnabled');
	$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
	$EnableSquidClamav=$sock->GET_INFO("EnableSquidClamav");
	
	
	
	
	$others="
		<table style='width:100%'>
		<tr>
			<td valign='top' class=caption nowrap>{logs_size}:</td>
			<td style='width:99%' valign='top'><strong>$logsize&nbsp;Ko</strong></td>
			<td>" . imgtootltip('22-recycle.png','{rotate_logs}',"DansGuardianRotateLogs();")."</td>
		</tr>
		</table>
			
		
	
	
	";
	
			if($_GET["switch"]=="from-squid"){
					$OnMouseOver="OnMouseOver=\";this.style.cursor='pointer'\"";
					$OnMouseOut="OnMouseOut=\"this.style.cursor='default'\"";
					$from_squid="<div style='padding:3px;font-size:13px'>{web_proxy}&nbsp;&raquo;<span $OnMouseOver $OnMouseOut OnClick=\"javascript:Loadjs('squid.newbee.php?yes=yes&#bullet#')\" >{filters}</span></div>";
			}
	
			
	$simple_intro="{danseguardian_simple_intro}";
	
	if($squidGuardEnabled==1){
		$simple_intro="{squidguard_simple_intro}";
		$tool="<div style='text-align:right'>". texttooltip("&laquo;&nbsp;{squidguard_testrules}&nbsp;&raquo;","{squidguard_testrules}","Loadjs('squidguard.tests.php')",null,0,"font-size:14px")."</div>";
	}		

	if($EnableUfdbGuard==1){
		$simple_intro="{ufdbguard_simple_intro}";
		$tool=null;
		$others=null;
	}		
	
			
	
	 $html="
	 <table style='width:100%;'>
	 	<tr>
		 		<td valign='top'>
		 			<img src='img/bg_dansguardian.png'>
		 			
		 			$others
		  		</td>
		  		<td valign='top'>
		  			<p  style='font-size:13px'>$from_squid$simple_intro</p>
		  			$tool
		  		</td>
		  	</tr>
		  </table>";
		  

		
		$blackcomputer=Paragraphe("64-black-computer.png","{black_ip_group}",'{black_ip_group_text}',"javascript:Loadjs('dansguardian.bannediplist.php');");
		$whitecomputer=Paragraphe("64-white-computer.png","{white_ip_group}",'{white_ip_group_text}',"javascript:Loadjs('dansguardian.exceptioniplist.php');");
		$template=Paragraphe("banned-template-64.png","{template_label}",'{template_explain}',"javascript:s_PopUp('dansguardian.template.php',800,800)"); 
		$denywebistes=Paragraphe('folder-64-denywebistes.png','{deny_websites}','{deny_websites_text}',"javascript:Loadjs('squid.popups.php?script=url_regex')");  
		$whitelisting=Paragraphe('domain-whitelist-64-grey.png','{www_whitelisting}','{www_whitelisting_text}');
		
		
		
		// -> $cicap_enabled
		
		$cicap_dnsbl=Paragraphe("64-cop-acls-dnsbl.png","{CICAP_DNSBL}","{CICAP_DNSBL_TEXT}","javascript:Loadjs('c-icap.dnsbl.php')");
		
		
		if($cicap_enabled==0){$cicap_dnsbl=null;}
		if(!$users->C_ICAP_DNSBL){$cicap_dnsbl=null;}
		
		if($dansguardian_enabled==0){
			$download=null;
			$performances=null;
			$content_scanner=null;
			$apply=null;
		}
		
		writelogs("squidGuardEnabled=$squidGuardEnabled - EnableUfdbGuard=$EnableUfdbGuard",__FUNCTION__,__FILE__,__LINE__);
		
		if($squidGuardEnabled==1){
			$squidguard_status=Paragraphe('squidguard-status-64.png','{squidguard_status}','{squidguard_status_text}',"javascript:Loadjs('squidguard.status.php')");
			$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");
			$whitelisting=Paragraphe('domain-whitelist-64.png','{www_whitelisting}','{www_whitelisting_text}',"javascript:Loadjs('squid.filters.whitelisting.php')");
			$whitelisting=Paragraphe('domain-whitelist-64.png','{www_whitelisting}','{www_whitelisting_text}',"javascript:echo('under construction')");
		}
		
		if($EnableUfdbGuard==1){
			$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");
			$whitelisting=Paragraphe('domain-whitelist-64.png','{www_whitelisting}','{www_whitelisting_text}',"javascript:Loadjs('squid.filters.whitelisting.php')");
			$whitelisting=Paragraphe('domain-whitelist-64.png','{www_whitelisting}','{www_whitelisting_text}',"javascript:echo('under construction')");
			
		}
		
		if($EnableSquidClamav==1){
			$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");
		}
		

		if($users->APP_UFDBGUARD_INSTALLED){
			$ufdbguard_settings=Paragraphe("filter-sieve-64.png","{APP_UFDBGUARD}","{APP_UFDBGUARD_PARAMETERS}",
			"javascript:Loadjs('ufdbguard.php')");
		}	

		
	
	$tr[]=$apply;
	$tr[]=$whitelisting;
	$tr[]=$squidguard_status;

	$tr[]=$denywebistes;
	$tr[]=$cicap_dnsbl;
	$tr[]=$blackcomputer;
	$tr[]=$whitecomputer;
	
	$tr[]=$download;
	$tr[]=$ufdbguard_settings;
	$tr[]=$performances;
	$tr[]=$content_scanner;
	$tr[]=$template;
	$tr[]=$squidguardweb;
	
	
	

	
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
	
	$html=$html.implode("\n",$tables);		  

	 
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html,"squid.newbee.php,squid.index.php");

echo $html;
	
}




function main_save(){
	$dans=new dansguardian($_GET["hostname"]);
	while (list ($num, $line) = each ($_GET)){
		$dans->Master_array[$num]=$line;
		
	}
	
	if(!$dans->SaveSettings()){
			echo $dans->last_errors;
			exit;}
		else{
		$tpl=new templates();
		echo $tpl->javascript_parse_text('{success}');
		
		}
}

function main_tabs(){
	$page=CurrentPageName();
	$array["performances"]='{performances}';
	$array["scanner"]='{content_scanner}';
	$array["download"]='{download}';
	$array["rules"]='{rules}';
	$array['members']='{members}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=$num&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_members(){
	
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}	
	$dans=new dansguardian_rules($hostname,0);
	$members=$dans->BuildMembersList();
	$members=htmlentities($members);
	$members=nl2br($members);
	
$html=main_tabs() . "<br>
	<h5>{members}</H5>
	<form name='FFM_DANS2'>
	<input type='hidden' name='hostname' value='$hostname'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>";	

$conf=$members;
	$table=explode("\n",$conf);
	
	$html=$html. "
	
	<br>
	<div style='padding:5px;margin:5px'>
	<table style='width:100%'>
	
	"; 
	
	while (list ($num, $val) = each ($table) ){
		$linenumber=$num+1;
		$html=$html . "<tr><td width=1% style='background-color:#CCCCCCC'><strong>$linenumber</strong></td><td width=99%'><code>$val</code></td></tr>";
		
		
	}
	$html=$html . "</table>
	
	</div>";


$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
	
	
}

function main_scanner(){
	
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
		$page=CurrentPageName();
	$dans=new dansguardian($hostname);	
	$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	
	$weightedphrasemode=array(0=>"{weightedphrasemode_0}",1=>'{weightedphrasemode_1}',2=>'{weightedphrasemode_2}');
	$preservecase=array(0=>"{preservecase_0}",1=>"{preservecase_1}",2=>"{preservecase_2}");
	$phrasefiltermode=array(0=>"{phrasefiltermode_0}",1=>"{phrasefiltermode_1}",2=>"{phrasefiltermode_2}",3=>"{phrasefiltermode_3}");
	//Parse engine
	
	$html="
	<h1>{content_scanner}</H5>
	<form name='FFM_DANS2'>
	<input type='hidden' name='hostname' value='$hostname'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<table style='width:100%' class=table_form>
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{template_label}:</strong></td>
	<td valign='top' align='right'><input type='button' OnClick=\"javascript:YahooWin3(700,'$page?template=yes','{template_label}');\" value='{template_label}...' style='margin:0px'></td>
	<td valign='top'>".help_icon('{template_explain}')."</td>
	</tr>	
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{showweightedfound}:</strong></td>
	<td valign='top'>" . Field_onoff_checkbox_img('showweightedfound',$dans->Master_array["showweightedfound"])."</td>
	<td valign='top'>".help_icon('{showweightedfound_text}')."</td>
	</tr>	
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{weightedphrasemode}:</strong></td>
	<td valign='top' colspan=2>" . Field_array_Hash($weightedphrasemode,'weightedphrasemode',$dans->Master_array["weightedphrasemode"])."</td>
	</tr>	
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{urlcachenumber}:</strong></td>
	<td valign='top'>" . Field_text('urlcachenumber',$dans->Master_array["urlcachenumber"],'width:50px')."</td>
	<td valign='top'>".help_icon('{urlcachenumber_text}')."</td>
	</tr>	
	
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{urlcacheage}:</strong></td>
	<td valign='top'>" . Field_text('urlcacheage',$dans->Master_array["urlcacheage"],'width:50px')."</td>
	<td valign='top'>".help_icon('{urlcacheage_text}')."</td>
	</tr>	

	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{scancleancache}:</strong></td>
	<td valign='top'>" . Field_onoff_checkbox_img('scancleancache',$dans->Master_array["scancleancache"])."</td>
	<td valign='top'>".help_icon('{scancleancache_text}')."</td>
	</tr>	

	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{phrasefiltermode}:</strong></td>
	<td valign='top' colspan=2>" . Field_array_Hash($phrasefiltermode,'phrasefiltermode',$dans->Master_array["phrasefiltermode"])."</td>
	</tr>	
	<tr><td>&nbsp;</td><td colspan=2 >".help_icon('{phrasefiltermode_text}')."</td></tr>
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{preservecase}:</strong></td>
	<td valign='top' colspan=2>" . Field_array_Hash($preservecase,'preservecase',$dans->Master_array["preservecase"])."</td>
	</tr>	
	<tr><td>&nbsp;</td><td colspan=2 >".help_icon('{preservecase_text}')."</td></tr>	
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend class=legend>{hexdecodecontent}:</strong></td>
	<td valign='top'>" . Field_onoff_checkbox_img('hexdecodecontent',$dans->Master_array["hexdecodecontent"])."</td>
	<td valign='top'>".help_icon('{hexdecodecontent_text}')."</td>
	</tr>	
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{forcequicksearch}:</strong></td>
	<td valign='top'>" . Field_onoff_checkbox_img('forcequicksearch',$dans->Master_array["forcequicksearch"])."</td>
	<td valign='top'>".help_icon('{forcequicksearch_text}')."</td>
	</tr>

	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{reverseaddresslookups}:</strong></td>
	<td valign='top'>" . Field_onoff_checkbox_img('reverseaddresslookups',$dans->Master_array["reverseaddresslookups"])."</td>
	<td valign='top'>".help_icon('{reverseaddresslookups_text}')."</td>
	</tr>				
	
	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{createlistcachefiles}:</strong></td>
	<td valign='top'>" . Field_onoff_checkbox_img('createlistcachefiles',$dans->Master_array["createlistcachefiles"])."</td>
	<td valign='top'>".help_icon('{createlistcachefiles_text}')."</td>
	</tr>

	<tr>
	<td $style align='right' nowrap valign='top' class=legend>{maxuploadsize}:</strong></td>
	<td valign='top'>" . Field_text('maxuploadsize',$dans->Master_array["maxuploadsize"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxuploadsize_text}')."</td>
	</tr>		
		
	
	<tr>
	<tr><td colspan=3 style='border-top:1px solid #005447'>&nbsp;</td></tr>
	<tr>
	<td $style colspan=3 align='right' valign='top'><input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM_DANS2','$page',true);\"></td>
	</tr>

	</table></FORM><br>$table";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}

function main_download(){
	
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$dans=new dansguardian($hostname);	
	$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	
	$weightedphrasemode=array(0=>"{weightedphrasemode_0}",1=>'{weightedphrasemode_1}',2=>'{weightedphrasemode_2}');
	$preservecase=array(0=>"{preservecase_0}",1=>"{preservecase_1}",2=>"{preservecase_2}");
	$phrasefiltermode=array(0=>"{phrasefiltermode_0}",1=>"{phrasefiltermode_1}",2=>"{phrasefiltermode_2}",3=>"{phrasefiltermode_3}");
	
	$enable_clamav="
	<tr>
	<td align='right' nowrap valign='top' class=legend>{enable_clamav}:</strong></td>
	<td valign='top'>" . Field_checkbox('enable_clamav',1,$dans->enable_clamav)."</td>
	<td valign='top'>". help_icon("{enable_clamav_text}")."</td>
	</tr>";
	
	if(!$users->CLAMD_INSTALLED){
		$enable_clamav="<tr>
				<td align='right' nowrap valign='top' class=legend>{enable_clamav}:</strong></td>
				<td valign='top'><input type='hidden' id='enable_clamav' value='0' name='enable_clamav'></td>
				<td valign='top'><img src='img/status_warning.gif'> {CLAMD_NOT_INSTALLED_TEXT}</td>
				</tr>";
		
		$warn_clamav=Paragraphe("dansguardian-warning.png","{CLAMD_NOT_INSTALLED}","{CLAMD_NOT_INSTALLED_TEXT}");
		
	}else{
		if($users->MEM_TOTAL_INSTALLEE<716800){
		$enable_clamav="<tr>
				<td align='right' nowrap valign='top' class=legend>{enable_clamav}:</strong></td>
				<td valign='top'><input type='hidden' id='enable_clamav' value='0' name='enable_clamav'></td>
				<td valign='top'><img src='img/status_warning.gif'> {MEM_TOTAL_INF_700_TEXT}</td>
				</tr>";
		
		$warn_clamav=Paragraphe("dansguardian-warning.png","{MEM_TOTAL_INF_700}","{MEM_TOTAL_INF_700_TEXT}");
		
		}
	}
	
		
	$html="
	<h1>{download}</h1>
	<form name='FFM_DANS2'>
	<input type='hidden' name='hostname' value='$hostname'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$warn_clamav</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap valign='top' class=legend>{deletedownloadedtempfiles}:</strong></td>
	<td valign='top'>" . Field_onoff_checkbox_img('deletedownloadedtempfiles',$dans->Master_array["deletedownloadedtempfiles"])."</td>
	<td valign='top'>". help_icon("{deletedownloadedtempfiles_text}")."</td>
	</tr>	
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{initialtrickledelay}:</strong></td>
	<td valign='top'>" . Field_text('initialtrickledelay',$dans->Master_array["initialtrickledelay"],'width:50px')."</td>
	<td valign='top'>". help_icon("{initialtrickledelay_text}")."</td>
	</tr>	

	<tr>
	<td align='right' nowrap valign='top' class=legend>{trickledelay}:</strong></td>
	<td valign='top'>" . Field_text('trickledelay',$dans->Master_array["trickledelay"],'width:50px')."</td>
	<td valign='top'>". help_icon("{trickledelay_text}")."</td>
	</tr>	
	
	$enable_clamav
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{enable_user_button}:</strong></td>
	<td valign='top'>" . Field_checkbox('DansGuardianEnableUserFrontEnd',1,$dans->DansGuardianEnableUserFrontEnd)."</td>
	<td valign='top' aling='right'>". button("{options}...","Loadjs('$page?template-options=yes')")."</td>
	</tr>	

	<tr>
	<td $style colspan=3 align='right' valign='top'>
	<hr>". button("{save}","ParseForm('FFM_DANS2','$page',true)")."
	</td>
	</tr>

	</table></FORM></td></table><br>$table";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}


function main_performances(){
	
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
		$page=CurrentPageName();
		$dans=new dansguardian($hostname);
	
	
	$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	
	$html="
	<h1>{performances}</H1>
	<form name='FFM_DANS1'>
	<input type='hidden' name='hostname' value='$hostname'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<table style='width:100%' class=table_form>
	<tr>
	<td align='right' nowrap valign='top' class=legend>{maxchildren}:</strong></td>
	<td valign='top'>" . Field_text('maxchildren',$dans->Master_array["maxchildren"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxchildren_text}')."</td>
	</tr>	
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{minchildren}:</strong></td>
	<td valign='top'>" . Field_text('minchildren',$dans->Master_array["minchildren"],'width:50px')."</td>
	<td valign='top'>".help_icon('{minchildren_text}')."</td>
	</tr>	
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{minsparechildren}:</strong></td>
	<td valign='top'>" . Field_text('minsparechildren',$dans->Master_array["minsparechildren"],'width:50px')."</td>
	<td valign='top'>".help_icon('{minsparechildren_text}')."</td>
	</tr>	
	
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{preforkchildren}:</strong></td>
	<td valign='top'>" . Field_text('preforkchildren',$dans->Master_array["preforkchildren"],'width:50px')."</td>
	<td valign='top'>".help_icon('{preforkchildren_text}')."</td>
	</tr>	

	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{maxsparechildren}:</strong></td>
	<td valign='top'>" . Field_text('maxsparechildren',$dans->Master_array["maxsparechildren"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxsparechildren_text}')."</td>
	</tr>		
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{maxagechildren}:</strong></td>
	<td valign='top'>" . Field_text('maxagechildren',$dans->Master_array["maxagechildren"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxagechildren_text}')."</td>
	</tr>	
	
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{maxips}:</strong></td>
	<td valign='top'>" . Field_text('maxips',$dans->Master_array["maxips"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxips_text}')."</td>
	</tr>

	<tr>
	<td align='right' nowrap valign='top' class=legend>{maxcontentfiltersize}:</strong></td>
	<td valign='top'>" . Field_text('maxcontentfiltersize',$dans->Master_array["maxcontentfiltersize"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxcontentfiltersize_text}')."</td>
	</tr>	
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{maxcontentramcachescansize}:</strong></td>
	<td valign='top'>" . Field_text('maxcontentramcachescansize',$dans->Master_array["maxcontentramcachescansize"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxcontentramcachescansize_text}')."</td>
	</tr>		
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{maxcontentfilecachescansize}:</strong></td>
	<td valign='top'>" . Field_text('maxcontentfilecachescansize',$dans->Master_array["maxcontentfilecachescansize"],'width:50px')."</td>
	<td valign='top'>".help_icon('{maxcontentfilecachescansize_text}')."</td>
	</tr>	
	
	
	
	<tr>
	<tr><td colspan=3 style='border-top:1px solid #005447'>&nbsp;</td></tr>
	<tr>
	<td $style colspan=3 align='right' valign='top'><input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM_DANS1','$page',true);\"></td>
	</tr>

	</table>
	</FORM><br>$table";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}

function main_rules_SaveGeneralSettings(){
	
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
		while (list ($num, $ligne) = each ($_GET) ){
			$dans->Main_array[$num]=$ligne;
		}
		
	$dans->SaveRule();
		
	}


function main_rules_mainsettings(){
	
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=strip_rulename($dansg->Master_rules_index[$rule_main]);
	$page=CurrentPageName();
	$groupmode=array('0'=>"{groupmode_0}",'1'=>"{groupmode_1}",'2'=>"{groupmode_2}");
	
	
	
	$sock=new sockets();
	$dansguardian_enabled=$sock->GET_INFO("DansGuardianEnabled");
	if($dansguardian_enabled==null){$dansguardian_enabled=0;$sock->SET_INFO("DansGuardianEnabled",0);}
	$squidGuardEnabled=$sock->GET_INFO("squidGuardEnabled");
	if($squidGuardEnabled==null){$squidGuardEnabled=0;$sock->SET_INFO("squidGuardEnabled",0);}	
	$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
	if($EnableUfdbGuard==null){$EnableUfdbGuard=0;$sock->SET_INFO("EnableUfdbGuard",0);}	
	
	$dansguardian_part="	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{groupmode}:</strong></td>
	<td valign='top'>" . Field_array_Hash($groupmode,'groupmode',$dans->Main_array["groupmode"],null,null,1,"font-size:13px;padding:3px")."</td>
	<td valign='top'>" . help_icon('{groupmode_text}')."</td>
	</tr>		
	
	<td align='right' nowrap valign='top' class=legend>{naughtynesslimit}:</strong></td>
	<td valign='top'>" . Field_text('naughtynesslimit',$dans->Main_array["naughtynesslimit"],'width:50px;font-size:13px;padding:3px')."</td>
	<td valign='top'>" . help_icon('{naughtynesslimit_text}')."</td>
	</tr>
	<tr>
	<td align='right' nowrap valign='top' class=legend>{categorydisplaythreshold}:</strong></td>
	<td valign='top'>" . Field_text('categorydisplaythreshold',$dans->Main_array["categorydisplaythreshold"],'width:50px;font-size:13px;padding:3px')."</td>
	<td valign='top'>" . help_icon('{categorydisplaythreshold_text}')."</td>
	</tr>
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{embeddedurlweight}:</strong></td>
	<td valign='top'>" . Field_text('embeddedurlweight',$dans->Main_array["embeddedurlweight"],'width:50px;font-size:13px;padding:3px')."</td>
	<td valign='top'>" . help_icon('{embeddedurlweight_text}')."</td>
	</tr>		
	
	
	<tr>
	<td align='right' nowrap valign='top' class=legend>{deepurlanalysis}:</strong></td>
	<td valign='top'>" . Field_checkbox('deepurlanalysis','on',$dans->Main_array["deepurlanalysis"])."</td>
	<td valign='top'>" . help_icon('{deepurlanalysis_text}')."</td>
	</tr>	";
	
	$users=new usersMenus();
	if(!$users->DANSGUARDIAN_INSTALLED){$dansguardian_part=null;}
	
	if($squidGuardEnabled==1){$dansguardian_part=null;}
	if($EnableUfdbGuard==1){$dansguardian_part=null;}
	
	
	
	$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	
	$html="
	<div id='main_rules_mainsettings_id'>
	<input type='hidden' name='RulesSaveGeneralSettings' value='yes'>
	<input type='hidden' name='rule_main' value='$rule_main'>
	<table style='width:100%'>

	<tr>
	<td align='right' nowrap valign='middle' class=legend>{groupname}:</strong></td>
	<td valign='top'>" . Field_text('groupname',$dans->Main_array["groupname"],'width:220px;font-size:13px;padding:3px')."</td>
	<td valign='top'>" . help_icon('{groupname_text}')."</td>
	</tr>	
	$dansguardian_part
	
	<td $style colspan=3 align='right' valign='top'>
	<hr>
		". button("{save}","main_rules_mainsettings_save();")."
	</tr>

	</table>
	<br>$table
	</div>
	
	<script>
	   
	var x_main_rules_mainsettings_save= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2Hide();
		}	
	
	
	
	
		function main_rules_mainsettings_save(){
			var XHR = new XHRConnection();
			XHR.appendData('RulesSaveGeneralSettings','yes');
			XHR.appendData('rule_main','$rule_main');
			XHR.appendData('groupname',document.getElementById('groupname').value);
			if(document.getElementById('groupmode')){
				XHR.appendData('groupmode',document.getElementById('groupmode').value);
			}
			
			if(document.getElementById('naughtynesslimit')){
				XHR.appendData('naughtynesslimit',document.getElementById('naughtynesslimit').value);
			}	

			
			if(document.getElementById('categorydisplaythreshold')){
				XHR.appendData('categorydisplaythreshold',document.getElementById('categorydisplaythreshold').value);
			}		

			if(document.getElementById('embeddedurlweight')){
				XHR.appendData('embeddedurlweight',document.getElementById('embeddedurlweight').value);
			}				
			if(document.getElementById('deepurlanalysis')){
				if(document.getElementById('deepurlanalysis').checked){
					XHR.appendData('deepurlanalysis','on');
				}else{
					XHR.appendData('deepurlanalysis','off');
				}
			}
		document.getElementById('main_rules_mainsettings_id').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_main_rules_mainsettings_save);	}
		
		
	</script>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}

function main_rules_delete(){
$num=$_GET["deleteMasterRule"];
$ldap=new clladp();
$dans=new dansguardian();
$dans->delete_rule($num);
echo main_rules();

}

function popup_authentication(){
	$page=CurrentPageName();
	$sock=new sockets();
	$hasProxyTransparent=$sock->GET_INFO("hasProxyTransparent");
	
	$squid=new squidbee();
	$LDAP_AUTH=$squid->LDAP_AUTH;
	
	if($hasProxyTransparent==1){
		$IP_SET=true;
	}else{
		if($LDAP_AUTH==0){$IP_SET=true;}
		if($LDAP_AUTH==1){$IP_SET=FALSE;}
	}
	if($squid->enable_squidguard==1){$groupipadd=Paragraphe("64-filter-computer.png","{filter_ip_group}",'{filter_ip_group_text}',"javascript:YahooWin(600,'$page?popup-group-ip=yes','{filter_ip_group}');");}
	
	$html="
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/secure-user-network-64.png'></td>
		<td valign='top'><H3 style='font-size:14px'>{MAP_USERS_RULES}</H3>
		<table style='width:100%'>
			<tr>
				<td valign='top' ><p style='font-size:12px'>{MAP_USERS_RULES_DANSGUARDIAN_TEXT}</p>
				<td valign='top' width=1%>$groupipadd</td>
			</tr>
		</table>
		</td>
	</tr>
	</table>";
	

$table_users="<div id='dansguardian_auth' style='height:450px;overflow:auto'></div>
	<script>
	LoadAjax('dansguardian_auth','dansguardian.groups.auth.php');
	</script>
";

	
	if($IP_SET){
		$groups=Paragraphe("64-filter-computer.png","{filter_ip_group}",'{filter_ip_group_text}',"javascript:YahooWin(600,'$page?popup-group-ip=yes');");
		$table_users="<div id='dansguardian_ip_users' style='height:450px;overflow:auto'></div>
	<script>
	LoadAjax('dansguardian_ip_users','dansguardian.index.php?popup-group-ip=yes');
	</script>";
		
	}
	
	$tr[]=$groups;

	
	

	
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
	
	$barr=implode("\n",$tables);	
	
	$html="$html$table_users$barr";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}


function popup_rules(){
	$page=CurrentPageName();
	
	$html="
	<input type='hidden' id='dans_add_rule_text' value='{dans_add_rule_text}'>
	<div id='dans_rules_content'></div>
	<div id='rules_lists' style='width:100%;height:228px;overflow:auto'>$rules</div>
	
	<div id='rules_list_contents' style='margin-top:8px;padding:3px;border:1px solid #CCCCCC;height:430px;overflow:auto'></div>

	<script>
		LoadAjax('rules_lists','$page?popup-rules-list=yes');
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function popup_rules_list(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	if(strlen($_GET["delete"])>0){
		if(is_numeric($_GET["delete"])){
		$dans=new dansguardian($hostname);
		$dans->delete_rule($_GET["delete"]);		
	}}
	
	$dans2=new dansguardian($hostname);
	$rules=$dans2->RulesList;
	$squid=new squidbee();
	
	if(!is_array($rules)){return null;}
	writelogs("Rules number: ".count($rules),__FUNCTION__,__FILE__);		
	//48-notes.png
	$count=0;
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{rules}</th>
	<th>{groups}</th>
	<th colspan=2>{restrictions}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
while (list ($num, $val) = each ($rules) ){
		$users=new usersMenus();
		$page=CurrentPageName();
		if(preg_match('#(.+?);(.+)#',$val,$re)){$rulename=$re[1];}else{$rulename=$val;}	
		$rule_settings="YahooWin2(650,'$page?rule_main=$num&tab=mainsettings&hostname={$_GET["hostname"]}','$rulename: {rule_behavior}');";
		$restrictions="YahooWin2(730,'$page?popup-restrictions=$num&hostname={$_GET["hostname"]}','$rulename: {restrictions}');";
		$delete=imgtootltip('delete-32.png','{delete}',"LoadAjax('rules_lists','dansguardian.index.php?pop-rules-list=yes&delete=$num');");
		$files="YahooWin2(720,'$page?popup-files=$num&hostname={$_GET["hostname"]}','$rulename: {files_restrictions}');";
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		
		$restrictions="LoadAjax('rules_list_contents','$page?popup-restrictions=$num&hostname={$_GET["hostname"]}')";
		$files="LoadAjax('rules_list_contents','$page?popup-files=$num&hostname={$_GET["hostname"]}');";
		
		//https://192.168.1.19:9000/squid.webfilter.users.php?table=yes&rule=3 // 
		
		if(!$users->EnableManageUsersTroughActiveDirectory){
			$tr_groups=imgtootltip("members-priv-32.png","$rulename::{privileges}","Loadjs('dansguardian.groups.php?ID=$num')");
		}
		$tr_rule_settings=imgtootltip("script-32.png","$rulename::{rule_behavior}",$rule_settings);
		$tr_rule_restrictions=imgtootltip("banned-template-32.png","$rulename::{restrictions}",$restrictions);
		
		$tr_auth=imgtootltip("view_members-32.png ","$rulename::{groups}","LoadAjax('rules_list_contents','squid.webfilter.users.php?table=yes&rule=$num')");
		
		
		if($count==0){
			$delete="&nbsp;";;
			$tr_rule_settings="&nbsp;";
			$tr_auth="&nbsp;";
			$tr_groups="&nbsp;";;
			}
			
		if($squid->enable_squidguard==1){
			$files="Loadjs('dansguardian.banned-extensions.php?rule_main=$num')";		
		}
		
		if(!$users->DANSGUARDIAN_INSTALLED){
			$files="Loadjs('dansguardian.banned-extensions.php?rule_main=$num')";
		}
		
		if(!$squid->enable_dansguardian==0){
			$files="Loadjs('dansguardian.banned-extensions.php?rule_main=$num')";
		}		
		
		
		$sql="SELECT count(*) as tcount FROM dansguardian_ipgroups WHERE RuleID=$num";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$count_ip=$ligne["tcount"];
		$sql="SELECT count(*) as tcount FROM dansguardian_groups WHERE RuleID=$num";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$count_groups=$ligne["tcount"];		
		
		$tr_filerestrictions=imgtootltip("bg_forbiden-attachmt-32.png","$rulename::{files_restrictions}",$files);
		$text="<div style='text-align:right;font-size:10px;padding-top:3px;'><i><strong>$count_ip</strong> {filter_ip_group}, <strong>$count_groups</strong> {groups}</div>";
		
		
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$tr_rule_settings</td>
			<td width=99% nowrap style='font-size:14px'><strong style='letter-spacing:2px'>$rulename</strong>$text</td>
			<td  width=1% valign='middle' align='center'>$tr_auth</td>
			<td  width=1% valign='middle' align='center'>$tr_rule_restrictions</td>
			<td  width=1% valign='middle' align='center'>$tr_filerestrictions</td>
			<td  width=1% valign='middle' align='center'>$tr_groups</td>
			<td  width=1% valign='middle' align='center'>$delete</td>
		</tr>
		
		";		
		
		
	$count=$count+1;
}
$users=new usersMenus();
$button_ufdb="&nbsp;";
$button_addrtules=button("{add_rule}","dansguardian_addrule('');");
$compile_rule_text=$tpl->javascript_parse_text("{WARNING_UFDBGUARD_COMPILES_RULES_ASK}");
if($users->APP_UFDBGUARD_INSTALLED){
	$button_ufdb=button("{compile_rules}","dansguardian_compileMyrulesUFDB('');");
}



$html=$html."
	<tr class=$classtr>
			<td colspan=7 align='right' valign='middle' style='padding:3px'>
				<table>
				<tr>
				<td>$button_ufdb</td>
				<td>$button_addrtules</td>
				</tr>
				</table>
			</tr>
			</tbody>
		</table>
		
<script>
		
	var x_dansguardian_compileMyrulesUFDB=function(obj){
			var results=obj.responseText;
			if(results.length>0){alert(results);}
		}		
		
	function dansguardian_compileMyrulesUFDB(){
			if(confirm('$compile_rule_text')){
				var XHR = new XHRConnection();
				XHR.appendData('compile-ufdb-total','yes');
				XHR.sendAndLoad('$page', 'GET',x_dansguardian_compileMyrulesUFDB);
			}
			
		}
</script>
	";
	
		
	

return $tpl->_ENGINE_parse_body($html);

}


function popup_rules_files(){
$dans=new dansguardian($hostname);

if($_GET["popup-files"]=="DansDefault"){
	$rulename="Default rule";
}else{
	$rule_tmp=$dans->Master_rules_index[$_GET["popup-files"]];
	if(preg_match('#(.+?);(.+)#',$rule_tmp,$re)){$rulename=$re[1];}else{$rulename=$val;}
}	

$bannedextensionlist=Paragraphe("64-filetype.png","{bannedextensionlist}",'{bannedextensionlist_user_explain}',
"javascript:Loadjs('dansguardian.banned-extensions.php?rule_main={$_GET["popup-files"]}')"
);


$BannedMimetype=Paragraphe("64-mime.png","{BannedMimetype}",'{BannedMimetype_explain}',
"javascript:Loadjs('dansguardian.banned-mime.php?rule_main={$_GET["popup-files"]}')");

//javascript:LoadAjax('dans_rules_content','dansguardian.index.php?rule_main=1&tab=BannedMimetype&hostname=pc-touzeau.klf.fr')
$ExeptionFileSiteList=Paragraphe("64-download.png","{ExeptionFileSiteList}",'{BannedMimetype_user_explain}',
"javascript:Loadjs('dansguardian.exception.filesite.php?rule_main={$_GET["popup-files"]}')");	

$squid=new squidbee();
if($squid->enable_squidguard==1){
	$BannedMimetype=null;
	$ExeptionFileSiteList=null;
}


$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$bannedextensionlist
		</td>
		<td valign='top'>
			$BannedMimetype
		</td>	
		<td valign='top'>
			$ExeptionFileSiteList
		</td>			
	</tr>
	<tr>
		<td valign='top'>
			$banned_regex
		</td>
		<td valign='top'>
			$ExceptionSiteList
		</td>
		<td>&nbsp;</td>
	</table>
	";	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}


function popup_rules_restrictions(){
$sock=new sockets();	
if($_GET["popup-restrictions"]=="DansDefault"){
	$rulename="Default rule";
}else{
	$dans=new dansguardian($hostname);	
	$rule_tmp=$dans->Master_rules_index[$_GET["popup-restrictions"]];
	if(preg_match('#(.+?);(.+)#',$rule_tmp,$re)){$rulename=$re[1];}else{$rulename=$val;}
}		
	
$rulename_encrypted=base64_encode($rulename);
$categories=Paragraphe("64-categories-ban.png","{categories}",'{banned_catgories_text}',
"javascript:Loadjs('dansguardian.categories.php?rule_main={$_GET["popup-restrictions"]}&rule-name=$rulename_encrypted','{categories}')");


$EnableSquidFilterWhiteListing=$sock->GET_INFO("EnableSquidFilterWhiteListing");
if($EnableSquidFilterWhiteListing==1){
$categories=Paragraphe("64-categories-white.png","{categories}",'{allowed_catgories_text}',
"javascript:Loadjs('dansguardian.categories.php?rule_main={$_GET["popup-restrictions"]}&rule-name=$rulename_encrypted','{categories}')");	
}



$personal_categories=Paragraphe("64-categories-personnal.png","{personal_categories}",'{personal_categories_text}',
"javascript:Loadjs('dansguardian.categories.personnal.php?rule_main={$_GET["popup-restrictions"]}&rule-name=$rulename_encrypted','{personal_categories}')");


$weightedphraselist=Paragraphe("64-weight-phrases.png","{weightedphraselist}",'{weightedphraselist_text}',
"javascript:Loadjs('dansguardian.weight-phrases.php?rule_main={$_GET["popup-restrictions"]}')");


$bannedphraselist=Paragraphe("64-banned-phrases.png","{bannedphraselist}",'{bannedphraselist_explain}',
"javascript:Loadjs('dansguardian.banned-phrases.php?rule_main={$_GET["popup-restrictions"]}')");

$banned_regex=Paragraphe("64-banned-regex.png","{bannedregexpurllist}",'{bannedregexpurllist_explain}',
	"javascript:Loadjs('dansguardian.banned-regex-purlist.php?rule_main={$_GET["popup-restrictions"]}')");

$ExceptionSiteList=Paragraphe("routing-rule.png","{ExceptionSiteList}",'{dansguardian_exception_site_list}',
	"javascript:Loadjs('dansguardian.exception.sites.php?rule_main={$_GET["popup-restrictions"]}')");	

if($_GET["popup-restrictions"]=="DansDefault"){
	$banned_regex=null;
	$bannedphraselist=null;
	}

$users=new usersMenus();	
$squid=new squidbee();
if($squid->enable_squidguard){
	$weightedphraselist=null;	
	$bannedphraselist=null;
	$banned_regex=null;
}

if(!$users->DANSGUARDIAN_INSTALLED){
	$weightedphraselist=null;	
	$bannedphraselist=null;
	$banned_regex=null;	
}

$tr[]=$categories;
$tr[]=$personal_categories;
$tr[]=$ExceptionSiteList;
$tr[]=$bannedphraselist;
$tr[]=$banned_regex;
$tr[]=$weightedphraselist;
	
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



function main_rules(){
	$page=CurrentPageName();
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	
	$dans=new dansguardian($hostname);
	
	$html=main_tabs() . "<br>
	<input type='hidden' id='dans_add_rule_text' value='{dans_add_rule_text}'>
	
	<div id='dans_rules_content'>
	<br><center><input type='button' value='&nbsp;&laquo;&nbsp;{add_rule}&nbsp;&raquo;&nbsp;' OnClick=\"javascript:dansguardian_addrule('$hostname');\"></center>
	</div>
	";
	
	
	$style=CellRollOver();
	$rules=$dans->Master_rules_index;
	if(is_array($rules)){
		$table="<table style='width:100%'>";
		
	while (list ($num, $val) = each ($rules) ){
		
		if(preg_match('#(.+?);(.+)#',$val,$re)){
			$rulename=$re[1];
		}else{
			$rulename=$val;
		}
		$table=$table . 
		"<tr $style>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td width=1%><strong>$num</strong></td>
		<td><strong style='font-size:13px'>$rulename</strong></td>
		<td " . CellRollOver("LoadAjax('dans_rules_content','$page?rule_main=$num&tab=mainsettings&hostname={$_GET["hostname"]}')") . " align='center'>
			<strong style='font-size:13px'>[{settings}]</strong>
		</td>		
		
		<td " . CellRollOver("LoadAjax('dans_rules_content','$page?rule_main=$num&tab=categories&hostname={$_GET["hostname"]}')") . " align='center'>
			<strong style='font-size:13px'>[{url_rules}]</strong>
		</td>
		<td " . CellRollOver("LoadAjax('dans_rules_content','$page?rule_main=$num&tab=BannedMimetype&hostname={$_GET["hostname"]}')") . "  align='center'>
			<strong style='font-size:13px'>[{extensions_rules}]</strong>
		</td>

		<td " . CellRollOver("LoadAjax('squid_main_config','$page?deleteMasterRule=$num')") . "  align='center'>
			<strong style='font-size:13px'>[{delete}]</strong>
		</td>
			
		</tr>
		";
		
	}
	$table=$table . "</table>";
	}
	
	
	
	$table=RoundedLightGrey($title.$table);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html.$table,'squid.index.php');
}


function main_rules_switch(){
	
	switch ($_GET["tab"]) {
		case "categories":echo main_rules_categories();exit;break;
		case "weightedphraselist":echo main_rules_weightedphraselist();break;exit;
		case "bannedphraselist": echo main_rules_bannedphraselist();break;
		case "bannedregexpurllist";echo main_rules_bannedregexpurllist();break;
		case "ExceptionSiteList";echo main_rules_exceptionsitelist();break;
		case "ExeptionFileSiteList";echo main_ExeptionFileSiteList();break;
		case "bannedextensionlist";main_extensions_bannedextensionslist();break;
		case "BannedMimetype":main_extensions_bannedMimeTypelist();break;
		case "mainsettings":main_rules_mainsettings();break;
		case "categories-list":main_rules_categories_list($_GET["rule_main"]);break;
		case "categories-weightedphraselist":main_rules_weightedphraselist_list($_GET["rule_main"]);break;
		case "categories-bannedphraselist":main_rules_bannedphraselist_list($_GET["rule_main"]);break;
		case "ExceptionSiteList-popup":main_rules_exceptionsitelist_list($_GET["rule_main"]);break;
		case "bannedextensionlist-popup":main_extensions_bannedextensionslist_list($_GET["rule_main"]);break;
		case "BannedMimetype-popup":main_extensions_bannedMimeTypelist_list($_GET["rule_main"]);break;
		case "ExeptionFileSiteList-popup":main_ExeptionFileSiteList_list($_GET["rule_main"]);break;
		default:
			break;
	}
	
	
}

function main_rules_tabs(){
	$rule_main=$_GET["rule_main"];
	$page=CurrentPageName();
	$array["categories"]='{categories}';
	$array["weightedphraselist"]='{weightedphraselist}';
	$array["bannedphraselist"]='{bannedphraselist}';
	$array["bannedregexpurllist"]='{bannedregexpurllist}';
	$array['ExceptionSiteList']='{ExceptionSiteList}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('dans_rules_content','$page?rule_main=$rule_main&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	
}

function main_extensions_tabs(){
$rule_main=$_GET["rule_main"];
	$page=CurrentPageName();
	$array["bannedextensionlist"]='{bannedextensionlist}';
	$array["BannedMimetype"]='{BannedMimetype}';
	$array["ExeptionFileSiteList"]='{ExeptionFileSiteList}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('dans_rules_content','$page?rule_main=$rule_main&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	
}


function strip_rulename($rulename){
	if(preg_match('#(.+?);(.+)#',$rulename,$re)){
		return $re[1];
		
	}else{
		return $rulename;
	}
	
}

function main_rules_weightedphraselist_category_delete(){
	$dansguardian=new dansguardian();
	$dansguardian->DefinedCategoryWeightedPhraseListDeleteRule($_GET["index"]);
	}



	


function main_rules_createcategory_weight(){
	$dans=new dansguardian();
	$dans->DefinedCategoryWeightedPhraseListAdd($_GET["weighted-phrase-list-create-category"]);
	
}


function main_configfile(){
$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}
	$dans=new dansguardian($hostname);
	$conf=$dans->BuildConfig();
	$table=explode("\n",$conf);
	
	$html=
	main_tabs() . "
	
	<br>
	<div style='padding:5px;margin:5px'>
	<table style='width:100%'>
	
	"; 
	
	while (list ($num, $val) = each ($table) ){
		$html=$html . "<tr><td width=1% style='background-color:#CCCCCCC'><strong>$num</strong></td><td width=99%'><code>$val</code></td></tr>";
		
		
	}
	$html=$html . "</table>
	
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function main_rules_addnew(){
	$dans=new dansguardian($_GET["hostname"]);
	$_GET["DansGuardian_AddRuleName"]=
	$dans->AddRule($_GET["DansGuardian_AddRuleName"]);
	}






function main_rules_createcategory(){
	$category=$_GET["category-create-new"];
	$dans=new dansguardian();
	$dans->DefinedCategoryBlackListAdd($category);
	
}

function main_rules_apply_conf(){
	
	if($_GET["hostname"]==null){
		$users=new usersMenus();
		$_GET["hostname"]=$users->hostname;
	}
	$dans=new dansguardian($_GET["hostname"]);

	$dans->SaveSettings();
if(is_array($dans->Master_rules_index)){
		while (list ($num, $line) = each ($dans->Master_rules_index)){
			$rules=new dansguardian_rules($hostname,$num);
			$rules->SaveConfigFiles();
			}
		}
	
	$squid=new squidbee();
	$squid->SaveToLdap();
	$squid->SaveToServer();
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(applysettings("dansguardian","ApplyDansGuardianSettings('{$_GET["hostname"]}')"));
}
	
function main_status(){

	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('dansguardian_status',$_GET["hostname"]));		

if($ini->_params["DANSGUARDIAN"]["running"]==0){
		$img="okdanger32.png";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
		
	}	
	
$html="
<table style='width:1OO%'>
<tr>
<td width=1% valign='top'>
<img src='img/$img'></td>
<td>
	<table style='width:100%'>
		<tr>
		<td colspan=2>{dansguardian_status}:&nbsp;<span style='font-size:13px'>$status</span></strong></td>
		</tr>
		<tr>
		<td align='right' nowrap>{pid}:</strong></td>
		<td>{$ini->_params["DANSGUARDIAN"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'>{memory}:</strong></td>
		<td>{$ini->_params["DANSGUARDIAN"]["master_memory"]}&nbsp; mb</strong></td>
		</tr>
		<tr>
		<td align='right' nowrap>{version}:</strong></td>
		<td>{$ini->_params["DANSGUARDIAN"]["master_version"]}</strong></td>
		</tr>
	</table>
	</td>
</table>
";	

$html=RoundedLightGrey($html);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html."<br>");	
}

function main_status_analyse_logs(){
	if(!file_exists('ressources/logs/dansguardian.start')){return ;}
	$datas=explode("\n",file_get_contents('ressources/logs/dansguardian.start'));
	$error=null;
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match('#failed#',$ligne,$re)){
			$error="{failed_start_service}";
			$error_text=$re[1];
			$img="status_bad_config.png";
			break;
		}
	}
	
	if($error<>null){
		return "<tr ". CellRollOver('DansGuardianViewStartError()','{view}').">
		<td align='right' valign='top'><img src='img/$img'></td>
		<td valign='top'><strong style='color:red'>$error:</strong><br><span style='color:red'>$error_text</span></td>
		</tr>";
		
	}
	
}

function main_status_analyse_viewstarterrors(){
	
	if(!file_exists('ressources/logs/squid.start')){return ;}
	$datas=explode("\n",file_get_contents('ressources/logs/squid.start'));
while (list ($num, $ligne) = each ($datas) ){
		if($ligne<>null){
			$html=$html . "<div style='padding:2px;border:1px solid white'><code style='font-size:10px'>$ligne</code></div>";
		}
	}
		
	echo $html;
	
}

function xfindrule(){
	$dans=new dansguardian();
	$dans->FindRuleDN($_GET["find-rule"]);
	$dans->rebuild_indexrules();
	
}


function ip_group_page(){
	$page=CurrentPageName();
	$dans=new dansguardian();
	$add=Paragraphe("64-add-computer.png","{add_address}","{add_address_dansguardian}","javascript:Loadjs('$page?add-address-js=yes')");
	$RulesNumber=$dans->RulesNumber();
	
	if($RulesNumber<2){
		$add="<p style='font-size:12px;font-weight:bold;color:#C3393E;padding:4px;width:240px'>{NO_RULESNUMBER_DANS_EXPLAIN}<br>
		<a href='#' OnClick=\"javascript:YahooWin(600,'dansguardian.index.php?popup-rules=yes');\">{parameters}</a></p>";
	}
	
	$html="
	
	<div class=explain><div style='float:right;margin:5px'>$add</div>{filter_ip_group_explain}</div>
	" . ip_group_rule_list() ."
		";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function ip_group_rule_list(){
	$dans=new dansguardian();
	
$rules=$dans->Master_rules_index;
	if(!is_array($rules)){return null;}

$html="
<div style='width:100%;height:500px;overflow:auto;margin-top:8px'>
<table style='width:99%'>";
while (list ($num, $val) = each ($rules) ){
		$page=CurrentPageName();
		if($num==1){continue;}
		if(preg_match('#(.+?);(.+)#',$val,$re)){$rulename=$re[1];}else{$rulename=$val;}	
		$html=$html . "
		<tr>
			<td valign='top'><H3 style='font-size:16px'>$rulename</H3></td>
			<td>" . imgtootltip('24-computer-alias-add.png','{add_computer}',"Loadjs('computer-browse.php?mode=dansguardian-ip-group&value=$num');")."</td>
		</tr>
		<tr>
			<td valign='top' colspan=2><div id='ip_group_rule_list_$num'>" . ip_group_list($num,1)."</td>
		</tr>
		<tr>
			<td valign='top' colspan=2><hr></td>
		</tr>		
		
		";
}

$html=$html . "</table></div>";
return $html;
	
	
}


function ip_group_list_add_computer(){
	include_once(dirname(__FILE__).'/ressources/class.computers.inc');
	$computer=new computers($_GET["AddComputerToDansGuardian"]);
	if($computer->ComputerIP==null){return null;}
	$dansrules=new dansguardian_rules(null,$_GET["AddComputerToDansGuardianRule"]);
	$dansrules->AddIpToFilter($computer->ComputerIP,$_GET["AddComputerToDansGuardianRule"]);
}


function ip_group_list($rule,$noecho=0){
	
	$dansg=new dansguardian_rules(null,$rule);
	$sql="SELECT * FROM dansguardian_ipgroups WHERE RuleID=$rule ORDER BY ID DESC";
	$html="<table style='width:100%'>";
	$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$num=$ligne["ID"];
			$val=$ligne["pattern"];
			$delete=imgtootltip("delete-24.png",'{delete}',"ip_group_delete('$rule','$num')");
			$html=$html . "
			<tr ". CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><code style='font-size:13px;font-weight:bold'>$val</code></td>
				<td width=1%>$delete</td>
			</tr>
			
			";
		
		}	
	
	$html=$html . "</table>";
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);
	
}


function ip_group_js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{add_address}");
	$html="
	var ip_family='';
	var pattern='';
	var rule_id='';
	function ip_group_start(){
		YahooWin3(500,'dansguardian.index.php?add-address-step1=yes','$title');
		}
	
	
	
		
	function ip_group_wizard(){
		ip_family=document.getElementById('ip_family').value;
		YahooWin3(500,'dansguardian.index.php?add-address-step1=yes&selected-ip-family='+ip_family,'$title: '+ip_family);
	
	}
	
	function ip_group_single(){
		pattern=document.getElementById('ip_address').value;
		if(pattern.length>7){
			last_step();
		}
	}
	
	function ip_group_pattern(){
		pattern=document.getElementById('ip_address').value;
		last_step();
		
	}	
	
	function ip_group_subnet(){
	 var ip=document.getElementById('ip_start').value;
	 var net=document.getElementById('netmask').value;
	 pattern=ip+'/'+net;
	 if(pattern.length>7){
			last_step();
		}
	}
	
	function ip_group_range(){
		 var ip=document.getElementById('ip_start').value;
		 var ip_to=document.getElementById('ip_to').value;
		 pattern=ip+'-'+ip_to;
		 if(pattern.length>7){
			last_step();
		}
	}
	
	function last_step(){
	
		YahooWin3(500,'dansguardian.index.php?add-address-step1=yes&selected-ip-family=rule&pattern='+pattern,'$title: '+ip_family);
	}
	
	var x_ip_group_end= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		YahooWin3Hide();
		LoadAjax('ip_group_rule_list_'+rule_id,'dansguardian.index.php?ip-group_list-rule='+rule_id);
	}
	

	
	function ip_group_end(){
	  var XHR = new XHRConnection();
	  rule_id=document.getElementById('rule_id').value;
      XHR.appendData('add-address-step1','yes');
      XHR.appendData('pattern',pattern);
      XHR.appendData('rule_id',rule_id);
      document.getElementById('ip_group_end').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
      XHR.sendAndLoad('dansguardian.index.php', 'GET',x_ip_group_end);

	}
	
		
	ip_group_start();
	
	
	
	";
	
	echo $html;
}

function ip_group_wizard(){
if(isset($_GET["delete_index"])){
	writelogs("Must delete rule number {$_GET["delete_index"]}",__FUNCTION__,__FILE__);
	$dansrules=new dansguardian_rules(null,$_GET["rule_id"]);
	$dansrules->DelIpToFilter($_GET["delete_index"]);
	exit;
}

if(isset($_GET["rule_id"])){
	$dansrules=new dansguardian_rules(null,$_GET["rule_id"]);
	if($dansrules->AddIpToFilter($_GET["pattern"],$_GET["rule_id"])){
		$tpl=new templates();
		echo $tpl->javascript_parse_text('{success}: '.$_GET["pattern"]);
	}
	exit;
}


$arr=array("single"=>"{single_ip}","subnet"=>"{subnet}","group"=>"{group_ip}")	;

$users=new usersMenus();
$sock=new sockets();
if($users->C_ICAP_INSTALLED){
	if($sock->GET_INFO('CicapEnabled')==1){
		$arr=array("single"=>"{single_ip}","pattern"=>"{pattern}")	;		
	}
}

$select=Field_array_Hash($arr,'ip_family',null,null,0,"font-size:13px;padding:3px");
$tpl=new templates();


$form="
	<table>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{select_ip_family}:</td>
		<td>$select</td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr>	
		<td colspan=2 align='right'><hr>". button("{add}","ip_group_wizard()")."</td>
	</table>";



if($_GET["selected-ip-family"]=="single"){
	
$form="
	<table>
	<tr>
		<td class=legend>{ip_address}:</td>
		<td style='font-size:13px'>" . Field_text('ip_address',null,'width:120px;font-size:13px;padding:3px')."</td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr>
		<td>". button("{back}","ip_group_start()")."</td>
		<td align='right'>". button("{next}","ip_group_single()")."</td>
	</tr>
	</table>";	
	
}


if($_GET["selected-ip-family"]=="pattern"){
	
$form="
<p class=caption style='font-size:14px'>Eg: <li style='font-size:13px'>192.168.1</li>
<li style='font-size:13px'>192.168.</li></p>
	<table>
	<tr>
		<td class=legend>{pattern}:</td>
		<td style='font-size:13px'>" . Field_text('ip_address',null,'width:120px;font-size:13px;padding:3px')."</td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr>
		<td>". button("{back}","ip_group_start()")."</td>
		<td align='right'>". button("{next}","ip_group_pattern()")."</td>
	</tr>
	</table>";	
	
}

if($_GET["selected-ip-family"]=="subnet"){
	
$form="
	<p class=caption style='font-size:14px'>Eg: <li style='font-size:13px'>192.168.1.0</li> <li style='font-size:13px'>255.255.255.0</li></p>
	<table>
	<tr>
		<td class=legend>{ip_start}:</td>
		<td>" . Field_text('ip_start',null,'width:190px;font-size:13px;padding:3px')."</td>
	</tr>
	<tr>
		<td class=legend>{netmask}:</td>
		<td>" . Field_text('netmask',null,'width:190px;font-size:13px;padding:3px')."</td>
	</tr>	
	<tr>	
	<tr><td colspan=2><hr></td></tr>
		<td><input type='button' OnClick=\"javascript:ip_group_start()\" value='&laquo;&nbsp;{back}'></td>
		<td align='right'><input type='button' OnClick=\"javascript:ip_group_subnet()\" value='{next}&nbsp;&raquo;'></td>
	</table>";	
	
}
if($_GET["selected-ip-family"]=="group"){
	
$form="
	<p class=caption>Ex: <li>192.168.1.20</li> <li>192.168.1.140</li></p>
	<table>
	<tr>
		<td class=legend>{ip_start}:</td>
		<td>" . Field_text('ip_start',null,'width:120px')."</td>
	</tr>
	<td class=legend>{ip_to}:</td>
		<td>" . Field_text('ip_to',null,'width:120px')."</td>
	</tr>
	<tr>	
	<tr><td colspan=2><hr></td></tr>
		<td><input type='button' OnClick=\"javascript:ip_group_start()\" value='&laquo;&nbsp;{back}'></td>
		<td align='right'><input type='button' OnClick=\"javascript:ip_group_range()\" value='{next}&nbsp;&raquo;'></td>
	</table>";	
	
}

if($_GET["selected-ip-family"]=="rule"){
	$dans=new dansguardian();
	$rules=$dans->Master_rules_index;
	if(is_array($rules)){
		while (list ($num, $val) = each ($rules) ){
			if($num==1){continue;}
			$rulename=$val;	
			$arr1[$num]=$rulename;
		}
		
		
		
		$form="
		<div id='ip_group_end'>
			<strong style='font-size:13px'>{$_GET["pattern"]}</strong>
			<table class='table_form'>
			<tr>
				<td class=legend>{select_rule}:</td>
				<td>" . Field_array_Hash($arr1,'rule_id',null)."</td>
			</tr>
			<tr><td colspan=2><hr></td></tr>
				<td><input type='button' OnClick=\"javascript:ip_group_start()\" value='&laquo;&nbsp;{back}'></td>
				<td align='right'><input type='button' OnClick=\"javascript:ip_group_end()\" value='{add}&nbsp;&raquo;'></td>
			</table>
		</div>";	
	
}}
	$html="

	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/64-add-computer.png' style='margin:5px'></td>
	<td valign='top' width=99%>$form</td>
	</tr>
	</table>";
	
	

	echo $tpl->_ENGINE_parse_body($html);	
	
}


function popup_template(){
	$dans=new dansguardian();
	$template=$dans->template;
	
	$html="
	<H1>{template_label}</H1>
	<div id='popup_template'>
	<textarea id='template_content' style='width:100%;height:300px'>$template</textarea>
	<div style='text-align:right;width:100%'><input type='button' OnClick=\"javascript:SaveDansGuardianTemplate()\" value='{edit}&nbsp;&raquo;'>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
function popup_template_save(){
	$tpl=$_POST["popup_template"];
	$sock=new sockets();
	$sock->SaveConfigFile($tpl,"DansGuardianHTMLTemplate");
	}
	
	
function template_options_js(){
		$page=CurrentPageName();
		$tpl=new templates();
		$title=$tpl->_ENGINE_parse_body('{enable_user_button}');
		
		$html="
		
			function LoadTemplateOptions(){
				YahooWin2('600','$page?template-options-page=yes');
			
			}
			
			var x_TemplateOptionsSave=function (obj) {
				LoadTemplateOptions();
			}			
			
			function TemplateOptionsSave(){
				var DansGuardianEnableUserArticaIP=document.getElementById('DansGuardianEnableUserArticaIP').value;
				document.getElementById('DansGuardianEnableUserArticaIP_div').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
				var XHR = new XHRConnection();
				XHR.appendData('DansGuardianEnableUserArticaIP',DansGuardianEnableUserArticaIP);
				XHR.sendAndLoad('$page', 'GET',x_TemplateOptionsSave);
			}
			
			LoadTemplateOptions();
		
		";
		
		echo $html;
		
}

function template_options_save(){
	$dans=new dansguardian();
	$dans->DansGuardianEnableUserArticaIP=$_GET["DansGuardianEnableUserArticaIP"];
	$dans->SaveSettings();
	
}


function template_options_page(){
	$dans=new dansguardian();
	
	$form="
	<div id='DansGuardianEnableUserArticaIP_div'>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend align='left'>{ip_artica_server}:</td>
		<td>" . Field_text('DansGuardianEnableUserArticaIP',$dans->DansGuardianEnableUserArticaIP,"width:120px")."</td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:TemplateOptionsSave();\" value='{edit}&nbsp;&raquo;'></td></tr>
	
	
	</table>
	</div>
	";
	
	$form=RoundedLightWhite($form);
	
	$member_add=Paragraphe('member-add-64.png','{add_allowed_users}','{add_allowed_users_text}',"javascript:TemplateUserSearch();");
	
	
	$html="<H1>{enable_user_button}</H1>
	<p class=caption>{enable_user_button_text}</p>
	<br>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$form</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>
	
	
	";
	
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
}

function main_rules_category_user_add(){
	$category=$_GET["category"];
	$site=$_GET["category-add-site"];
	$dansguardian=new dansguardian();
	$rules=$dansguardian->DefinedCategoryBlackListAddRule($category,$site);
	
}

function main_rules_category_user_del(){
	$category=$_GET["category"];
	$num=$_GET["category-del-site"];
	$dansguardian=new dansguardian();
	$rules=$dansguardian->DefinedCategoryBlackListDelRule($num);	
	}


function main_rules_category_user_edit(){
	$categoryname=$_GET["category-user-edit"];
	$mysqlSquid=new mysql_squid_builder();
	$sql="SELECT ID,pattern FROM dansguardian_categories WHERE category_name='$categoryname' ORDER BY ID DESC";
	$results=$mysqlSquid->QUERY_SQL($sql,"artica_backup");
	$table="<table style='width:100%'>";
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$num=$ligne["ID"];
		$val=$ligne["pattern"];
		if(trim($val)==null){continue;}
			$table=$table . "
			<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><code style='font-size:12px'>$val</td>
				<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","main_rules_category_user_delete('$categoryname','$num')")."</td>
			</tR>
			";
	}	
		
	$table="<div style='width:100%;height:300px;overflow:auto'>$table</table></div>";
	$table=RoundedLightWhite($table);
	
	
$html="<H1>{category}:$categoryname</H1>
<div id=main_rules_category_user_edit>
<p class=caption>{main_rules_category_user_edit}</p>
<center>
<table style='width:70%' class=table_form>
<tr>
	<td class=legend>{website}:</td>
	<td>" . Field_text("UserWebSite",null,'width:250px')."</td>
	<td align='right'><input type='button' OnClick=\"javascript:AddUserWebSite('$categoryname');\" value='{add}&nbsp;&raquo;'>
</tr>
</table>
</center>

<br>
$table
</div>
";	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function rotate_logs(){
	$sock=new sockets();
	$datas=$sock->getfile("DansGuardianRotateLogs");
	$tbl=explode("\n",$datas);
	$table="<table style='width:100%'>";
	
while (list ($num, $val) = each ($tbl) ){
		if(trim($val)==null){continue;}
			$table=$table . "
			<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><code style='font-size:12px'>$val</td>
				
			</tR>
			";
		}	
		
	$table="<div style='width:100%;height:300px;overflow:auto'>$table</div>";
	$table=RoundedLightWhite($table);

	$html="<H1>{rotate_logs}</H1><br>$table";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function main_rules_weightedphraselist_category_edit(){
	$category=$_GET["weighted-phrase-list-edit-category"];
	$rules=main_rules_weightedphraselist_category_edit_list($category,1);
	$html="<H1>$category</H1>
	<p class=caption>{weighted_phrases_list_explain}</p>
	<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>{words}:</td>
			<td>" . Field_text('words',null,'width:100%')."</td>
			<td class=legend>{score}:</td>
			<td>" . Field_text('score',0,'width:30px')."</td>
		</tr>
		<tr>
			<td colspan=4 align='right'><input type='button' OnClick=\"javascript:WeightedPhraseListAddCategoryRule('$category');\" value='{add}&nbsp;&raquo;'></td>
		</tr>
	</table>
	<br>
	<div id='main_rules_weightedphraselist_category_list'>$rules</div>";
	
	
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function main_rules_weightedphraselist_category_edit_list($category,$noecho=0){
	$q=new mysql();
	$sql="SELECT * FROM dansguardian_weightedphraselist WHERE category_name='$category' ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
		
	$html="
	<div style='width:99%;height:250px;overflow:auto'>
	<table style='width:100%'>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$line=$ligne["pattern"];
		$num=$ligne["ID"];
		$line=trim($line);
		
		if(!preg_match('#(.+?)<([0-9\-]+)>$#',$line,$re)){continue;}
		$words=$re[1];
		$score=$re[2];
		$words=str_replace("<",'',$words);
		$words=str_replace(">",'',$words);
		$html=$html . "<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=99%><code style='font-size:13px'>$words</code></td>
			<td width=1% align='right'><strong style='font-size:13px'>$score</strong></td>
			<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","WeightedPhraseListDelCategoryRule('$category',$num)")."</td>
			</tr>";
		
		
	}
	
	$html=$html . "</table></div>";
	
	$html=RoundedLightWhite($html);
	if($noecho==1){return $html;}
	echo $html;
	
}



function main_rules_weightedphraselist_category_addwords(){
	$category=$_GET["weighted-phrase-list-add-category-rule"];
	$words=$_GET["words"];
	$score=$_GET["score"];
	$dansguardian=new dansguardian();
	$dansguardian->DefinedCategoryWeightedPhraseListAddRule($category,$words,$score);
}

function popup_mysql_error(){
	
	$html="<H1>{dansguardian_tables_error}</H1>
	
	<table style='width:100%'>
	<td valign='top'><img src='img/database-error-256.png'></td>
	<td valign='top'>
	<div id='DansGuardianRebuildDatabase'>
	<p class=caption style='color:red;font-size:14px'>{dansguardian_tables_error_text}</p>
	<center style='margin:50px'><input type='button' OnClick=\"javascript:DansGuardianRebuildDatabase();\" value='{build_dansguardian_databases}&nbsp;&raquo;'></td>
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function mysql_rebuild(){
	$q=new mysql();
	if(!$q->CheckTable_dansguardian()){
		echo $q->mysql_error;
	}
	
	
}

function compile_ufdb_total(){
	$sock=new sockets();
	$sock->getFrameWork("ufdbguard.php?recompile-all=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success}");
	
}

