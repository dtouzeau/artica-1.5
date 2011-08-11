<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.os.system.inc');
	
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();
	}	
	
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["members"])){members();exit;}
	if(isset($_GET["members-list"])){members_list();exit;}
	if(isset($_GET["members-delete"])){members_delete();exit;}
	if(isset($_GET["members-add"])){members_add_popup();exit;}
	if(isset($_GET["members-save"])){members_add_save();exit;}
	if(isset($_GET["mysql-dir"])){mysql_dir_popup();exit;}
	if(isset($_POST["ChangeMysqlDir"])){mysql_dir_save();exit;}
	if(isset($_GET["mysql-status"])){mysql_status();exit;}
	
	
js();

function js(){
	$page=CurrentPageName();
echo "
		document.getElementById('BodyContent').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		$('#BodyContent').load('$page?tabs=yes');"	;
}

	
function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$users=new usersMenus();
	$array["popup"]='{APP_MYSQL}';
	$array["events"]='{events}';
	$array["parameters"]='{mysql_settings}';
	
	$array["members"]='{mysql_users}';
	$array["ssl"]='{ssl}';
	$array["globals"]='{globals_values}';
	
	
	
	if($users->APP_GREENSQL_INSTALLED){
		$array["greensql"]='{APP_GREENSQL}';
	}
	
	while (list ($num, $ligne) = each ($array) ){
		if($num=="greensql"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"greensql.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="parameters"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"mysql.settings.php?inline=yes\"><span>$ligne</span></a></li>\n");
			continue;			
			
		}
		
		if($num=="ssl"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"system.mysql.ssl.php\"><span>$ligne</span></a></li>\n");
			continue;
		}

		if($num=="globals"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"system.mysql.globals.php\"><span>$ligne</span></a></li>\n");
			continue;
		}

		if($num=="events"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"system.mysql.events.php\"><span>$ligne</span></a></li>\n");
			continue;
		}			
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_mysql style='width:100%;height:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_mysql\").tabs();});
		</script>";		
}

function popup(){
		$artica=new artica_general();
		$tpl=new templates();
	    $page=CurrentPageName();
		if(preg_match('#(.+?):(.*)#',$artica->MysqlAdminAccount,$re)){
			$rootm=$re[1];
			$pwd=$re[2];
		}
		
		//$p=Paragraphe('folder-64-backup.png','{mysql_database}','{mysql_database_text}',"javascript:Loadjs('mysql.index.php')",null);
		$i=Buildicon64('DEF_ICO_MYSQL_PWD');
		$j=Buildicon64('DEF_ICO_MYSQL_CLUSTER');
		$browse=Buildicon64("DEF_ICO_MYSQL_BROWSE");
		$changep=Buildicon64("DEF_ICO_MYSQL_USER");
		$mysqlrepair=Paragraphe('mysql-repair-64.png','{mysql_repair}','{mysql_repair_text}',"javascript:YahooWin(400,'mysql.index.php?repair-databases=yes')",null);
		//YahooWin(400,'artica.performances.php?main_config_mysql=yes');

		//$mysqlperformances=Paragraphe('mysql-execute-64.png','{mysql_database}','{mysql_performance_level_text}',"javascript:YahooWin(400,'artica.performances.php?main_config_mysql=yes');",null);
		$mysqlperformances=Paragraphe('folder-64-backup.png','{mysql_database}',
		'{mysql_performance_level_text}',"javascript:Loadjs('mysql.settings.php');",null);
		$mysql_benchmark=Paragraphe('mysql-benchmark-64.png','{mysql_benchmark}','{mysql_benchmark_text}',"javascript:YahooWin3(400,'artica.performances.php?MysqlTestsPerfs=yes','{mysql_benchmark}');",null);
		$mysql_audit=Paragraphe('mysql-audit-64.png','{mysql_audit}','{mysql_audit_text}',"javascript:YahooWin3(600,'artica.settings.php?mysql-audit=yes');",null);
		$movefolder=Paragraphe('folder-64.png','{storage_directory}',
		'{change_mysql_directory_text}',"javascript:YahooWin3(405,'$page?mysql-dir=yes','{storage_directory}');",null);
		
		
		
		
		$tr[]=$p;
		$tr[]=$mysqlrepair;
		//$tr[]=$mysqlperformances;
		$tr[]=$i;
		$tr[]=$changep;
		$tr[]=$browse;
		$tr[]=$movefolder;
		$tr[]=$j;
		$tr[]=$mysql_benchmark;
		$tr[]=$mysql_audit;

	$tables[]="<table style='width:500px'><tr>";
	$t=0;
	while (list ($key, $line) = each ($tr) ){
			$line=trim($line);
			if($line==null){continue;}
			$t=$t+1;
			$tables[]="<td valign='top'>$line</td>";
			if($t==2){$t=0;$tables[]="</tr><tr>";}
			}
	
	if($t<2){
		for($i=0;$i<=$t;$i++){
			$tables[]="<td valign='top'>&nbsp;</td>";				
		}
	}	
	
	
$html="<div style='width:720px'>
<table style='width:100%'>
<tr>
<td valign='top' width=1%><div id='mysql-status' style='width:250px'></div></td>
<td valign='top' width=99%>
	". implode("\n",$tables)."
	</td>
	</tr>
</table>
	
	</div>
	<script>
	LoadAjax('mysql-status','$page?mysql-status=yes');
	</script>
	
	";

	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html,"postfix.plugins.php");	
	echo $datas;
	
	
}

function mysql_status(){
	$specific=Paragraphe("script-64.png", "{mysql_perso_conf}", "{mysql_perso_conf_text}","javascript:Loadjs('system.mysql.perso.php')",null,220);
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_mysql')")."</div>";
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$datas=$sock->getFrameWork("services.php?mysql-status=yes");
	writelogs(strlen($datas)." bytes for mysql status",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
	$ini->loadString(base64_decode($datas));
	$status=DAEMON_STATUS_ROUND("ARTICA_MYSQL",$ini,null,0)."<br>".DAEMON_STATUS_ROUND("MYSQL_CLUSTER_MGMT",$ini,null,0).$refresh."<br>$specific";
	echo $tpl->_ENGINE_parse_body($status);	
	
}


function members(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add_user=$tpl->_ENGINE_parse_body("{add_user}");
	$html="
	<div id='mysql-members-id'></div>
	
	
	<script>
		function LoadMysqlMembers(){
			LoadAjax('mysql-members-id','$page?members-list=yes');
		
		}
		

		function AddMysqlUser(){
			YahooWin('350','$page?members-add=yes','$add_user');
		}		
		
		LoadMysqlMembers();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function members_list(){
	
	$sql="SELECT * FROM user ORDER BY User";
	$page=CurrentPageName();
	$tpl=new templates();	
	$delete_alert=$tpl->javascript_parse_text("{delete}");
	
	$html="
	
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
		". Paragraphe32("add_user","add_mysql_user_text","AddMysqlUser()","folder-useradd-32.png")."
	</td>
		<td valign='top' width=99%>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{hostname}</th>
			<th>{username}</th>
			<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"mysql");
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$password=$ligne["Password"];
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$array=array("host"=>$ligne["Host"],"user"=>$ligne["User"]);
		
		$delete=imgtootltip("delete-32.png","{delete}","DeleteMysqlUser('". base64_encode(serialize($array))."','{$ligne["User"]}@{$ligne["Host"]}')");
		
	$ligne["Host"]=str_replace("%","{all}",$ligne["Host"]);
	$html=$html."
		<tr class=$classtr>
		<td style='font-size:13px'><strong><code style='font-size:13px'>{$ligne["Host"]}</code></strong></td>
		<td style='font-size:13px'><strong style='font-size:13px'>{$ligne["User"]}</strong></td>
		<td style='font-size:13px'>$delete</td>
		</tr>
		
		";
		}

	$html=$html."</table>
	</td>
	</tr>
	</table>
	
	<script>
	var x_DeleteMysqlUser= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		LoadMysqlMembers();
	}		
	
	function DeleteMysqlUser(arra,user){
		if(confirm('$delete_alert '+user+' ?')){
			var XHR = new XHRConnection();
			XHR.appendData('members-delete',arra);
			document.getElementById('mysql-members-id').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_DeleteMysqlUser);
			}
	}

	</script>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function members_delete(){
	
	$array=unserialize(base64_decode($_GET["members-delete"]));
	if(!is_array($array)){return;}
	$sql="DROP USER '{$array["user"]}'@'{$array["host"]}';";
	$q=new mysql();
	if(!$q->EXECUTE_SQL($sql)){
		echo "user:{$array["user"]}\nHost:{$array["host"]}\n\n$q->mysql_error";
	}
	
}


function members_add_popup(){
$page=CurrentPageName();
	$tpl=new templates();		
	
	$html="
	<div id='memberdiv'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{server}:</td>
		<td>". Field_text("servername",$servername,"font-size:14px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend>{username}:</td>
		<td>". Field_text("username",$username,"font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("password",$password,"font-size:14px;padding:3px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","EditMysqlUser()")."</td>
	</tr>
	</table>
	
	<script>
	var x_EditMysqlUser= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		LoadMysqlMembers();
		YahooWinHide();
	}		
	
	function EditMysqlUser(){
		var XHR = new XHRConnection();
		XHR.appendData('members-save','yes');
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('username',document.getElementById('username').value);
		XHR.appendData('password',base64_encode(document.getElementById('password').value));
		document.getElementById('memberdiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_EditMysqlUser);
	}		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function members_add_save(){
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__."<br>";}
	$server=trim($_GET["servername"]);
	$username=trim($_GET["username"]);
	$password=trim(base64_decode($_GET["password"]));
	if($server=="*"){$server="%";}
	if($GLOBALS["VERBOSE"]){echo __LINE__." ->mysql()<br>";}
	$q=new mysql();
	$sql="SELECT User FROM user WHERE Host='$server' AND User='$username'";
	if($GLOBALS["VERBOSE"]){echo $sql."<br>";}
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"mysql"));
	if($GLOBALS["VERBOSE"]){echo "User:{$ligne["User"]}<br>";}
	if(trim($ligne["User"])==null){
		$sql="CREATE USER '$username'@'$server' IDENTIFIED BY '$password';";
		if($GLOBALS["VERBOSE"]){echo $sql."<br>";}
		if(!$q->EXECUTE_SQL($sql)){echo "user:$username\nHost:$server\n\n$q->mysql_error";return;}
	}
	
	$sql="GRANT ALL PRIVILEGES ON * . * TO '$username'@'$server' IDENTIFIED BY '$password' WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0";
if($GLOBALS["VERBOSE"]){echo $sql."<br>";}
	if(!$q->EXECUTE_SQL($sql)){echo "user:$username\nHost:$server\n\n$q->mysql_error";return;}
	
}

function mysql_dir_popup(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$ChangeMysqlDir=$sock->GET_INFO("ChangeMysqlDir");
	if($ChangeMysqlDir==null){$ChangeMysqlDir="/var/lib/mysql";}
	
	$html="
	<div id='ChangeMysqlDirDiv'>
	<div class=explain>{ChangeMysqlDir_explain}</div>
	<p>&nbsp;</p>
	<table style='width:100%'>
	<tr>
		<td class=legend>{directory}:</td>
		<td>". Field_text("ChangeMysqlDir",$ChangeMysqlDir,"font-size:16px;padding:3px;width:220px")."</td>
		<td><input type='button' value='{browse}...' OnClick=\"Loadjs('SambaBrowse.php?no-shares=yes&field=ChangeMysqlDir')\"></td>
	</tr>
	<tr>
		<td colspan=3 align='right'>
			<hr>". button("{apply}","SaveChangeMysqlDir()")."</td>
	</tr>
	</table>
	<script>
		var x_SaveChangeMysqlDir= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin3Hide();
		}
				
		function SaveChangeMysqlDir(){
			var XHR = new XHRConnection();
			XHR.appendData('ChangeMysqlDir',document.getElementById('ChangeMysqlDir').value);
			document.getElementById('ChangeMysqlDirDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'POST',x_SaveChangeMysqlDir);	
		}	
	</script>
	</div>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function mysql_dir_save(){
	$sock=new sockets();
	$sock->SET_INFO("ChangeMysqlDir",$_POST["ChangeMysqlDir"]);
	$sock->getFrameWork("cmd.php?ChangeMysqlDir=yes");
}

?>