<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.cups.inc');
	include_once('ressources/class.samba.inc');	
	
$usersmenus=new usersMenus();
if(($usersmenus->AsArticaAdministrator==false)){echo "alert('No privileges')";exit;}

if(isset($_GET["start"])){troubleshoot_start();exit;}
if(isset($_GET["rebuildldap"])){rebuildldap();exit;}
if(isset($_GET["slapindex"])){slapindex();exit;}
if(isset($_GET["restore-db"])){restore_db();exit;}
if(isset($_GET["restore-db-file"])){restore_db_file();exit;}
if(isset($_GET["artica-branch"])){artica_branch_js();exit;}
if(isset($_GET["artica-branch-perform"])){artica_branch_perform();exit;}


js();


function artica_branch_perform(){
	$tpl=new templates();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?repair-artica-ldap-branch=yes");
	echo $tpl->javascript_parse_text('{CORRUPTED_LDAP_BRANCH_PERFORMED}',1);
}

function artica_branch_js(){
	$tpl=new templates();
	$text=$tpl->javascript_parse_text('{CORRUPTED_LDAP_BRANCH_PERFORM}');
	$page=CurrentPageName();
	$html="
		var x_ArticaLdapBStart= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
			}
	
	
		function ArticaLdapBStart(){
			if(confirm('$text')){
				var XHR = new XHRConnection();
				XHR.appendData('artica-branch-perform','yes');
				XHR.sendAndLoad('$page', 'GET',x_ArticaLdapBStart);	
			}
		
		}
	
	ArticaLdapBStart()";
	
	echo $html;
	
	
}

function js(){
	$tpl=new templates();
	$title=$tpl->javascript_parse_text('{troubleshoot}');
	$want=$tpl->javascript_parse_text('{error_want_operation}');
	$error_no_ipaddr=$tpl->javascript_parse_text("{error_no_ipaddr}");
	$error_no_localization=$tpl->javascript_parse_text("{error_no_localization}");
	$rebuild_ldap_databases=$tpl->javascript_parse_text("{rebuild_ldap_databases}");
	$index_ldap_databases=$tpl->javascript_parse_text('{index_ldap_databases}');
	$restore_ldap_database=$tpl->javascript_parse_text('{restore_ldap_database}');
	$page=CurrentPageName();
	$html="
function troubleshootPage(){
   YahooWin2('720','$page?start=yes','$title');                
}

function troubleshoot_rebuildldap(){
	if(confirm('$want')){
		 YahooWin3('600','$page?rebuildldap=yes','$rebuild_ldap_databases');         
	
	}
}
	
function troubleshoot_index(){
	if(confirm('$want')){
		 YahooWin3('600','$page?slapindex=yes','$index_ldap_databases');         
	
	}
}


function troubleshoot_restoreldap(){
	YahooWin3('400','$page?restore-db=yes','$restore_ldap_database');    
}

function troubleshoot_restoreldap_file(file){
	if(confirm('$want')){
		 YahooWin3('600','$page?restore-db-file='+file,'$restore_ldap_database');       
	}
}



troubleshootPage();

";

echo $html;

}

function troubleshoot_start(){
	
	$html="<H1>{troubleshoot}</H1>
	<table style='width:100%'>
	<tr>
		
	<td valign='top'>
	
	<table style='width:100%'>
	<tr>
		<td valign='top'>" . Paragraphe("64-troubleshoot-rebuild.png","{rebuild_ldap_databases}","{rebuild_ldap_databases_text}","javascript:troubleshoot_rebuildldap()")."</td>
		<td valign='top'>" . Paragraphe("64-troubleshoot-index.png","{index_ldap_databases}","{index_ldap_databases_text}","javascript:troubleshoot_index()")."</td>
		<td valign='top'>". Buildicon64("DEF_ICO_LDAP")."</td>
	</tr>
	<tr>
		<td valign='top'>" . Paragraphe("64-troubleshoot-restore.png","{restore_ldap_database}","{restore_ldap_database_text}","javascript:troubleshoot_restoreldap()")."</td>
		<td valign='top'>" . Paragraphe("64-export.png","{export_artica_settings}","{export_artica_settings_text}","javascript:Loadjs('index.export.php')")."</td>
		<td valign='top'>&nbsp;</<td valign='top'>
	</tr>
	<tr>
		<td valign='top'>" . Paragraphe("64-import.png","{import_artica_settings}","{import_artica_settings_text}","javascript:Loadjs('index.import.php')")."</td>
		<td>". Buildicon64("DEF_ICO_RESTOREMBX")."</td>
		<td valign='top'>&nbsp;</<td valign='top'>
	</table>
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function rebuildldap(){
	
	$sock=new sockets();
	$datas=$sock->getfile("LdapRebuildDatabases");
	$tbl=explode("\n",$datas);
	
	$table="<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($tbl) ){
		
		if(trim($ligne)==null){continue;}
		$table=$table.
		"<tr " . CellRollOver_jaune().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:10px'>$ligne</code></td>
		</tr>";
		}
		
		$table="<div style='width:99%;height:300px;overflow:auto'>$table</table></div>";
		$table=RoundedLightWhite($table);
		
	
$html="<H1>{rebuild_ldap_databases}</H1>
	$table";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function slapindex(){
	
	$sock=new sockets();
	$datas=$sock->getfile("slapindex");
	$tbl=explode("\n",$datas);
	
	$table="<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($tbl) ){
		
		if(trim($ligne)==null){continue;}
		$table=$table.
		"<tr " . CellRollOver_jaune().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:10px'>$ligne</code></td>
		</tr>";
		}
		
		$table="<div style='width:99%;height:300px;overflow:auto'>$table</table></div>";
		$table=RoundedLightWhite($table);
		
	
$html="<H1>{index_ldap_databases}</H1>
	$table";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}


function restore_db(){
	
	
	$sock=new sockets();
	$datas=$sock->getfile("RecoveryLdapList");
	$tbl=explode("\n",$datas);
	rsort($tbl);
	$table="<table style='width:100%'>";	
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$a=explode(";",$ligne);
		$file=$a[0];
		$size=$a[1];
		if(!preg_match("#([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)#",$file,$re)){continue;}
		$date="{$re[1]}-{$re[2]}-{$re[3]} {$re[4]}:00:00";
		$tot=$tot+$size;
		$js="troubleshoot_restoreldap_file('$file');";
	$table=$table.
		"<tr " . CellRollOver($js).">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:10px'>$date</code></td>
		<td><code style='font-size:10px'>$file</code></td>
		<td><code style='font-size:10px'>$size KB</code></td>
		</tr>";
				
		
	}
	
$table=$table.	"
		<tr><td colspan=4><hr></td></tr>
		<tr>
		<td width=1%>&nbsp;</td>
		<td><code style='font-size:10px'>&nbsp;</code></td>
		<td><code style='font-size:10px'>&nbsp;</code></td>
		<td><code style='font-size:10px'><strong>$tot KB</strong></code></td>
		</tr>";
	
$table="<div style='width:99%;height:200px;overflow:auto'>$table</table></div>";
		$table=RoundedLightWhite($table);	
	
$html="<H1>{restore_ldap_database}</H1>
	$table";	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function restore_db_file(){
	$file=$_GET["restore-db-file"];
	$sock=new sockets();
	$datas=$sock->getfile("RecoveryLdapFile:$file");

$tbl=explode("\n",$datas);
	
	$table="<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($tbl) ){
		
		if(trim($ligne)==null){continue;}
		$table=$table.
		"<tr " . CellRollOver_jaune().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:10px'>$ligne</code></td>
		</tr>";
		}
		
		$table="<div style='width:99%;height:300px;overflow:auto'>$table</table></div>";
		$table=RoundedLightWhite($table);
		
	
$html="<H1>{restore_ldap_database} $file</H1>
	$table";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}

	


?>