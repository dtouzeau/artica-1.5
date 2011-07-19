<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.mysql.inc");

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){header('location:users.index.php');exit();}



if(isset($_GET["mysqlstatus"])){echo mysql_status();exit;}
if(isset($_GET["main"])){echo mysql_main_switch();exit;}
if(isset($_GET["mysqlenable"])){echo mysql_enable();exit;}
if(isset($_GET["changemysqlenable"])){mysql_action_enable_change();exit;}
if(isset($_GET["mysql_account"])){testsMysql();exit;}
if($_GET["script"]=="mysql_enabled"){echo js_mysql_enabled();exit;}
if($_GET["script"]=="mysql_save_account"){echo js_mysql_save_account();exit;}
if(isset($_GET["databases_status"])){Database_Status();exit;}
if(isset($_GET["repair-databases"])){repair_database();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["mysql-settings-popup"])){mysql_settings_js();exit;}
if(isset($_GET["mysql-settings-popup-show"])){echo mysql_settings(true);exit;}

	js();
	
	
function js(){

	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_MYSQL_ARTICA}");
	$prefix=str_replace(".","_",$page);
	
	if(isset($_GET["account"])){
		$ajax="LoadAjax('main_mysql_config','mysql.index.php?main=settings&hostname=')";
	}else{
		$ajax="LoadAjax('main_mysql_config','mysql.index.php?main=&hostname=')";
	}
	
	
	$html="
var {$prefix}timerID  = null;
var {$prefix}timerID1  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;

function {$prefix}demarre(){
{$prefix}tant = {$prefix}tant+1;
{$prefix}reste=5-{$prefix}tant;
	if ({$prefix}tant < 5 ) {                           
{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",3000);
      } else {
		{$prefix}tant = 0;
		{$prefix}ChargeLogs();
		{$prefix}demarre();                                //la boucle demarre !
   }
}

function mystatus(){
	if(document.getElementById('mystatus')){
		LoadAjax('mystatus','$page?databases_status=yes');
	}
}

function {$prefix}ChargeLogs(){
	mystatus();
	LoadAjax('mysql_status','$page?mysqlstatus=yes');
	
	}
		
function mystatus(){
	if(document.getElementById('mystatus')){
		LoadAjax('mystatus','$page?databases_status=yes');
	}
}
	
	

	function {$prefix}SartMysql(){	
		YahooWin3(700,'$page?popup=yes','$title');
		setTimeout('Loadall()',900);
	}
	
	function Loadall(){
		demarre();
		ChargeLogs();
		mystatus();
		$ajax
		LoadAjax('mysql_status','$page?mysqlstatus=yes');
		LoadAjax('mysqlenable','$page?mysqlenable=yes');	
		}
	
	{$prefix}SartMysql()
	";
	
	echo $html;
}

function popup(){
	
$html="
	<h1>{APP_MYSQL_ARTICA}</H1><span id='scripts'><script type=\"text/javascript\" src=\"$page?script=load_functions\"></script></span>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>".RoundedLightWhite("<img src='img/bg_mysql.png'style='margin-right:30px;margin-bottom:5px'>")."</td>
	<td valign='top'>
		<div id='mysql_status'></div>
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			<br>
			".RoundedLightWhite("
			<table style='width:100%'>	
			<tr>
			<td valign='top'>
				<div id='main_mysql_config'></div>
			</td>
			<td valign='top'>
				<div id='mysqlenable'></div>
			</td>
			</tr>
			</table>")."
			
		</td>
	</tr>
	</table>
"	;

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}
	


function mysql_tabs(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["settings"]='{settings}';
	$array["performances"]='{performances}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_mysql_config','$page?main=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<div id=tablist>$html</div>");		
}



function mysql_status(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('mysqlstatus'));
	$status=DAEMON_STATUS_ROUND("ARTICA_MYSQL",$ini,null);
	echo $tpl->_ENGINE_parse_body($status);
	}
function mysql_main_switch(){
	$tab=mysql_tabs();
	
	switch ($_GET["main"]) {
		case "settings":echo $tab.mysql_settings();break;
		case "performances":echo $tab.mysql_performances();break;
	
		default:echo $tab.mysql_performances();break;
	}
	
	
}

function mysql_performances(){
	$html="
	<table style='width:100%'>
		<tr>
			<td class=legend>
				{change_mysql_power}:
			</td>
			<td>
				<input type=button value='{mysql_performance_level}&nbsp;&raquo;' OnClick=\"javascript:YahooWin(400,'artica.performances.php?main_config_mysql=yes');\">
			</td>
			</tr>
			<tr>
			<td class=legend>
				{mysql_repair}:
			</td>			
			<td>
				<input type=button value='{mysql_repair}&nbsp;&raquo;' OnClick=\"javascript:YahooWin(400,'mysql.index.php?repair-databases=yes','{waiting}...');\">
			</td>			
		</tr>
	</table>
	<div id='mystatus'></div>
	";
	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}

function mysql_enable(){
$artica=new artica_general();
$page=CurrentPageName();
$icon=Paragraphe_switch_img('{enable_mysql}',"{enable_mysql_text}","enable_mysql",$artica->EnableMysqlFeatures);
$html="

<div>$icon
	<div style='text-align:right;margin-top:5px'><input type='button' OnClick=\"javascript:Loadjs('$page?script=mysql_enabled')\" value='{edit}&nbsp;&raquo;'></div>
</div>

";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}
function js_mysql_enabled(){
	$page=CurrentPageName();
	$html="var enable=document.getElementById('enable_mysql').value;
	YahooWin(400,'$page?changemysqlenable='+enable);
	LoadAjax('mysqlenable','$page?mysqlenable=yes');
	";
	echo $html;
}

function mysql_action_enable_change(){
	$enable=$_GET["changemysqlenable"];
	$artica=new artica_general();
	$artica->EnableMysqlFeatures=$enable;
	
	if($enable==0){
		
		$main=new main_cf();
		$main->save_conf();
		$main->save_conf_to_server();
	}
	
	$artica->SaveMysqlSettings();
	$sock=new sockets();
	$datas=$sock->getfile('restartmysql');
	$datas=htmlentities($datas);
	$tbl=explode("\n",$datas);
	$datas='';
	while (list ($num, $val) = each ($tbl) ){
		$datas=$datas."<div>$val</div>";
		
	}
	echo "<div style='width:100%;height:500px;overflow:auto'>$datas</div>";
	
	
}

function mysql_settings_js(){
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{mysql_account}',"artica.settings.php");
	$prefix=str_replace(".","_",$page);	
	
	$page=CurrentPageName();
	$js="
	function {$prefix}LoadMainRI(){
		YahooWin3('450','$page?mysql-settings-popup-show=yes','$title');
		}	
		
		
	{$prefix}LoadMainRI();		
	
	
	";
	echo $js;
	
}


function mysql_settings($notitle=false){
	
	$user=new usersMenus();
	if(!$user->AsArticaAdministrator){
		if(!$user->AsSystemAdministrator){
			$tpl=new templates();
			$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS} !AsSystemAdministrator');
			$text=replace_accents(html_entity_decode($text));
			echo "alert('$text');";
			exit;
	}}
	
		$artica=new artica_general();
	    $page=CurrentPageName();
		if(preg_match('#(.+?):(.*)#',$artica->MysqlAdminAccount,$re)){
			$rootm=$re[1];
			$pwd=$re[2];
		}	
		
		$servername=$artica->MysqlServerName;
		
	$html="
	
	<table style='width:100%'>
	
		<tr>
		<td align='right' nowrap class=legend>{mysqlserver}:</strong></td>
		<td align='left'>" . Field_text('mysqlserver',$servername,'width:90px;padding:3px;font-size:13px',null,null,'')."</td>
		</tr>	
		<tr>
		<td align='right' nowrap class=legend>{mysqlroot}:</strong></td>
		<td align='left'>" . Field_text('mysqlroot',$rootm,'width:90px;padding:3px;font-size:13px',null,null,'{mysqlroot_text}')."</td>
		</tr>
		<tr>
		<td align='right' nowrap class=legend>{mysqlpass}:</strong></td>
		<td align='left'>" . Field_password("mysqlpass",$pwd,"width:90px;padding:3px;font-size:13px")."</td>
		</tr>
		<tr>
			<td colspan=2 align='right'>
				<hr>". button("{apply}","Loadjs('$page?script=mysql_save_account')")."
			</td>
		</tr>	
	</table>
	";	
	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html,"artica.settings.php");	
}

function js_mysql_save_account(){
	$page=CurrentPageName();
	$html="
	var mysqlserver=document.getElementById('mysqlserver').value;
	var mysqlroot=escape(document.getElementById('mysqlroot').value);
	var mysqlpass=escape(base64_encode(document.getElementById('mysqlpass').value));
	YahooWin4(400,'$page?mysql_account='+mysqlroot+'&mysqlpass='+mysqlpass+'&mysqlserver='+mysqlserver);
	";
	echo $html;
}


function testsMysql(){
	$_GET["mysqlpass"]=trim(base64_decode($_GET["mysqlpass"]));
	
	writelogs("testing {$_GET["mysqlserver"]}:3306 with user {$_GET["mysql_account"]} and password \"{$_GET["mysqlpass"]}\"",__FUNCTION__,__FILE__,__LINE__);
	
	$bd=@mysql_connect("{$_GET["mysqlserver"]}:3306",$_GET["mysql_account"],$_GET["mysqlpass"]);
	$database=md5('Y-m-d H:i:s');
	$tpl=new templates();
	if(!$bd){
			$errnum=mysql_errno();
    		$des=mysql_error();
    		echo "<div style='font-size:12px;color:red;font-weight:bold'>
    				<p>{connection}:ERR N.$errnum</p> $des
    			</div>";
    		exit;
			}
			
	$results=@mysql_query("CREATE DATABASE $database");
	if(!$bd){
		$errnum=mysql_errno();
    		$des=mysql_error();
    		echo RoundedLightWhite("<div style='font-size:12px;color:red;font-weight:bold'>
    				<p>{privileges}:ERR N.$errnum</p> $des
    			</div>");
    		exit;
	}
	$results=@mysql_query("DROP DATABASE $database");
	
	$artica=new artica_general();
	$artica->MysqlAdminAccount="{$_GET["mysql_account"]}:{$_GET["mysqlpass"]}";
	$artica->MysqlServerName=$_GET["mysqlserver"];
	$artica->SaveMysqlSettings();
	$mysql=new mysql();
	$mysql->mysql_server=$_GET["mysqlserver"];
	$mysql->mysql_admin=$_GET["mysql_account"];
	$mysql->mysql_password=$_GET["mysqlpass"];
	$mysql->hostname=$_GET["mysqlserver"];
	$mysql->BuildTables();
	
	
	$tpl=new templates();
	echo RoundedLightWhite($tpl->_ENGINE_parse_body('<div>{success} {edit} {mysql_account}</div>'));

	
	
	
	
}


function Database_Status(){
	$my=new mysql();
	
	$artica_back=$my->DATABASE_STATUS("artica_backup");
	$artica_events=$my->DATABASE_STATUS("artica_events");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<H5>{databases_status}</H5>$artica_back.$artica_events");
	
	
}

function repair_database(){
	$sock=new sockets();
	$datas=$sock->getfile('MysqlRepairDatabase');
	$tb=explode("\n",$datas);
	
while (list ($num, $ligne) = each ($tb) ){
			if(trim($ligne)==null){continue;}
			$ligne=htmlentities($ligne);
			$dd=$dd."<div><strong style='font-size:12px;color:black'><code>$ligne</code></strong></div>";
		}
	$dd=RoundedLightWhite($dd);
	$html="
	<H1>{mysql_repair}</H1>
	<div style='width:100%;height:300px;overflow:auto'>$dd</div>
	
	
	";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}


?>
