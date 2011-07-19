<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["rules"])){rules();exit;}
	if(isset($_GET["rules-list"])){rules_list();exit;}
	if(isset($_POST["DeleteAllRules"])){rules_delete_all();exit;}
	if(isset($_POST["DeleteSingleRule"])){rule_delete_single();exit;}
	if(isset($_POST["SecFilterEngine"])){SecFilterEngineSave();exit;}
	if(isset($_POST["EnableRule"])){rule_enable_single();exit;}
	if(isset($_GET["rule-form"])){rule_form();exit;}
	if(isset($_POST["rule-edit"])){rule_edit();exit;}
	if(isset($_GET["events"])){events();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{security_enforcement}");
	$html="YahooWin3('650','$page?tabs=yes&servername={$_GET["servername"]}','{$_GET["servername"]}::$title');";
	echo $html;
	}


function tabs(){
	
	$tpl=new templates();	
	$page=CurrentPageName();
	
	
	$array["popup"]='{parameters}';
	$array["rules"]='{rules}';
	$array["events"]='{events}';
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_modsecu style='width:100%;height:670px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_modsecu\").tabs();});
		</script>";		
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$freeweb=new freeweb($_GET["servername"]);
	
	$Params=$freeweb->Params["mod_security"];
	if($Params["SecFilterDefaultAction"]==null){$Params["SecFilterDefaultAction"]="Hello World!";}
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend>{FreeWebsEnableModSecurity}:</td>
		<td>". Field_checkbox("SecFilterEngine",1,$Params["SecFilterEngine"])."</td>
	</tr>
	<tr>
		<td class=legend>{SecFilterScanPost}:</td>
		<td>". Field_checkbox("SecFilterScanPOST",1,$Params["SecFilterScanPOST"])."</td>
	</tr>	
	
	
	<tr>
		<td class=legend>{SecFilterCheckURLEncoding}:</td>
		<td>". Field_checkbox("SecFilterCheckURLEncoding",1,$Params["SecFilterCheckURLEncoding"])."</td>
	</tr>	
	<tr>
		<td class=legend>{SecFilterCheckUnicodeEncoding}:</td>
		<td>". Field_checkbox("SecFilterCheckUnicodeEncoding",1,$Params["SecFilterCheckUnicodeEncoding"])."</td>
	</tr>		
	<tr>
		<td class=legend>{SecFilterDefaultAction}:</td>
		<td>". Field_text("SecFilterDefaultAction",$Params["SecFilterDefaultAction"],"width:120px;font-size:13px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveModSecurity()")."</td>
	</tr>
	</table>
	
	<script>
	var x_SaveModSecurity=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}			
		RefreshTab('main_config_modsecu');
	}	
	
	function SaveModSecurity(){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		if(document.getElementById('SecFilterEngine').checked){XHR.appendData('SecFilterEngine',1);}else{XHR.appendData('SecFilterEngine',0);}
		if(document.getElementById('SecFilterCheckURLEncoding').checked){XHR.appendData('SecFilterCheckURLEncoding',1);}else{XHR.appendData('SecFilterCheckURLEncoding',0);}
		if(document.getElementById('SecFilterCheckUnicodeEncoding').checked){XHR.appendData('SecFilterCheckUnicodeEncoding',1);}else{XHR.appendData('SecFilterCheckUnicodeEncoding',0);}
		if(document.getElementById('SecFilterScanPOST').checked){XHR.appendData('SecFilterScanPOST',1);}else{XHR.appendData('SecFilterScanPOST',0);}
		XHR.appendData('SecFilterDefaultAction',document.getElementById('SecFilterDefaultAction').value);
    	XHR.sendAndLoad('$page', 'POST',x_SaveModSecurity);		
	}
	
	function SecDisabled(){
		document.getElementById('SecFilterCheckURLEncoding').disabled=true;
		document.getElementById('SecFilterCheckUnicodeEncoding').disabled=true;
		document.getElementById('SecFilterDefaultAction').disabled=true;
	}
	SecDisabled();
	</script>	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function SecFilterEngineSave(){
	$freeweb=new freeweb($_POST["servername"]);
	while (list ($num, $ligne) = each ($_POST) ){
		$freeweb->Params["mod_security"][$num]=$ligne;
	}
	
	$freeweb->SaveParams();

}

function rules(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	
	$sql="SELECT COUNT(ID) as tcount FROM freewebs_secfilters WHERE servername='{$_GET["servername"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($ligne["tcount"]==0){add_default_rules();}

	$html="
<center>
<table style='width:95%' class=form>
<tr>
	<td class=legend>{rules}:</td>
	<td>". Field_text("rules_area_search",null,"font-size:13px;padding:3px",null,null,null,false,"SecModSearchCheck(event)")."</td>
	<td width=1%>". button("{search}","SecModSearch()")."</td>
</tr>
</table>
</center>
<div id='SecFilterRules' style='width:100%;height:550px;overflow:auto'></div>
	<script>
		function SecModSearchCheck(e){
			if(checkEnter(e)){SecModSearch();}
		}
	
	
		function SecModSearch(){
			var se=escape(document.getElementById('rules_area_search').value);
			LoadAjax('SecFilterRules','$page?rules-list=yes&servername={$_GET["servername"]}&search='+se);
		
		}
	
		SecModSearch();
	</script>
	";

	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function rules_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	
	$rule_text=$tpl->_ENGINE_parse_body("{rule}");
	$add=imgtootltip("plus-24.png","{add}","RuleForm(0)");	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th width=1%>$add</th>
		<th colspan=2>{rules}</th>
		<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	if(strlen($_GET["search"])>0){
		$search="*{$_GET["search"]}*";
		$search=str_replace("*","%",$search);
		$search=str_replace("%%","%",$search);
		$qsearch="AND `value` LIKE '$search'";
	}
	
	$sql="SELECT * FROM freewebs_secfilters WHERE servername='{$_GET["servername"]}' $qsearch ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code>$sql</code>";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{delete}","DeleteSingleRule({$ligne["ID"]})");
		
		$edit=imgtootltip("rule-24.png","{edit}","RuleForm({$ligne["ID"]})");
		$html=$html."
		<tr class=$classtr>
		<td width=1%>$edit</td>
		<td style='font-size:14px'>{$ligne["key"]} {$ligne["value"]}</td>
		<td style='font-size:14px'>". Field_checkbox("ENABLE_{$ligne["ID"]}",1,$ligne["enabled"],"EnableRule({$ligne["ID"]})")."</td>
		<td>$delete</td>
		</tr>
		";
			
	}
	
	$html=$html."
	<tr class=$classtr>
		<td colspan=4 align='right'>". imgtootltip("delete-32.png","{delete_all}","DeleteAllRules()")."</td>
	</tr>
	
	</table>
	<script>
	
	var x_DeleteAllRules=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}			
		RefreshTab('main_config_modsecu');
	}	
		
	
	function DeleteAllRules(){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		XHR.appendData('DeleteAllRules','yes');
    	XHR.sendAndLoad('$page', 'POST',x_DeleteAllRules);		
	}
	
	function DeleteSingleRule(ID){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		XHR.appendData('DeleteSingleRule',ID);
    	XHR.sendAndLoad('$page', 'POST',x_DeleteAllRules);		
	}
	
	function EnableRule(ID){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		XHR.appendData('EnableRule',ID);
		if(document.getElementById('ENABLE_'+ID).checked){
			XHR.appendData('value',1);
			
		}else{
			XHR.appendData('value',0);
		}
		XHR.sendAndLoad('$page', 'POST');	
		
	}
	
	function RuleForm(ID){
		YahooWin5('550','$page?rule-form=yes&ID='+ID+'&servername={$_GET["servername"]}','$rule_text::'+ID);
	}
	
</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);

	
}

function add_default_rules(){
$q=new mysql();
$SecFilter[]="/bin/sh ";
$SecFilterSelective[]="REQUEST_LINE /bin/ps";
$SecFilterSelective[]="REQUEST_LINE ps\x20";
$SecFilter[]="wget\x20";
$SecFilter[]="uname\x20-a";
$SecFilter[]="/usr/bin/id";
$SecFilter[]="\;id";
$SecFilter[]="/bin/echo";
$SecFilter[]="/bin/kill";
$SecFilter[]="/bin/chmod";
$SecFilter[]="/chgrp";
$SecFilter[]="/chown";
$SecFilter[]="/usr/bin/chsh";
$SecFilter[]="tftp\x20";
$SecFilter[]="gcc\x20-o";
$SecFilter[]="cc\x20";
$SecFilter[]="/usr/bin/cpp";
$SecFilter[]="cpp\x20";
$SecFilter[]="/usr/bin/g\+\+";
$SecFilter[]="g\+\+\x20";
$SecFilter[]="bin/python";
$SecFilter[]="python\x20";
$SecFilter[]="bin/tclsh";
$SecFilter[]="tclsh8\x20";
$SecFilter[]="bin/nasm";
$SecFilter[]="nasm\x20";
$SecFilter[]="/usr/bin/perl";
$SecFilter[]="perl\x20";
$SecFilter[]="traceroute\x20";
$SecFilter[]="/bin/ping";
$SecFilter[]="nc\x20";
$SecFilter[]="nmap\x20";
$SecFilter[]="/usr/X11R6/bin/xterm";
$SecFilter[]="\x20-display\x20";
$SecFilter[]="lsof\x20";
$SecFilter[]="rm\x20";
$SecFilter[]="/bin/mail";
$SecFilterSelective[]="REQUEST_LINE /bin/ls";
$SecFilter[]="/etc/inetd\.conf log,pass";
$SecFilter[]="/etc/motd log,pass";
$SecFilter[]="/etc/shadow log,pass";
$SecFilter[]="conf/httpd\.conf log,pass";
$SecFilterSelective[]="REQUEST_LINE \.htgroup log,pass";
$SecFilterSelective[]="REQUEST_LINE /rksh";
$SecFilterSelective[]="REQUEST_LINE /bash log,pass";
$SecFilterSelective[]="REQUEST_LINE /perl\?";
$SecFilterSelective[]="REQUEST_LINE /zsh";
$SecFilterSelective[]="REQUEST_LINE /csh";
$SecFilterSelective[]="REQUEST_LINE /tcsh";
$SecFilterSelective[]="REQUEST_LINE /rsh";
$SecFilterSelective[]="REQUEST_LINE /ksh";
$SecFilterSelective[]="REQUEST_LINE /icat log,pass";
$SecFilterSelective[]="REQUEST_LINE /cgi-bin/ls log,pass";
$SecFilter[]="document\.domain\(";
$SecFilter[]="javascript\://";
$SecFilter[]="img src=javascript";
$SecFilter[]="\.htpasswd";
$SecFilter[]='../';
$SecFilter[]="ls\x20-l";
$SecFilter[]="/etc/passwd";
$SecFilter[]="\.htaccess";
$SecFilter[]='cd\.\.';
$SecFilter[]='/....';
$SecFilter[]="cat\x20";
$SecFilter[]="Authorization\: Basic ";
$SecFilterSelective[]="REQUEST_LINE /\.history";
$SecFilterSelective[]="REQUEST_LINE /\.bash_history";
$SecFilterSelective[]="REQUEST_LINE /*\x0a\.pl";
$SecFilterSelective[]="REQUEST_LINE /\?M=D log,pass";
$SecFilterSelective[]="REQUEST_LINE /server-status log,pass";
$SecFilter[]="chunked";
$SecFilterSelective[]="REQUEST_LINE /mod_gzip_status log,pass";
$SecFilter[]="SQSPELL_APP\[";
$SecFilter[]="cmdd=";
$SecFilter[]="phpbb_root_path=";
$SecFilterSelective[]="REQUEST_LINE /quick-reply\.php log,pass";
$SecFilter[]="path=http\://";
$SecFilter[]="\.php";
$SecFilterSelective[]="REQUEST_LINE /uploadimage\.php log,pass";
$SecFilterSelective[]="REQUEST_LINE /upload\.php log,pass";
$SecFilterSelective[]="REQUEST_LINE /privmsg\.php log,pass";
$SecFilterSelective[]="REQUEST_LINE /test\.php log,pass";
$SecFilter[]="/boot";
$SecFilter[]="/dev";
$SecFilter[]="/etc";
$SecFilter[]="/initrd";
$SecFilter[]="/lost+found";
$SecFilter[]="/mnt";
$SecFilter[]="/proc";
$SecFilter[]="/root";
$SecFilter[]="/sbin";
$SecFilter[]="/tmp";
$SecFilter[]="/usr/local/apache";
$SecFilter[]="/var/spool";
$SecFilter[]="/bin/cc";
$SecFilter[]="/bin/gcc";
$SecFilter[]="<*script";
$SecFilter[]="into*from";
$SecFilter[]="update*from";
$SecFilter[]="select*from";

while (list ($num, $ligne) = each ($SecFilter) ){
	if($ligne==null){continue;}
	$md5=md5("SecFilter:$ligne");
	
	$sql="INSERT IGNORE INTO freewebs_secfilters(servername,value,zmd5)
	VALUES('{$_REQUEST["servername"]}','$ligne','$md5')";
	$q->QUERY_SQL($sql,"artica_backup");
	}

while (list ($num, $ligne) = each ($SecFilterSelective) ){
	if(!preg_match("#^(.+?)\s+(.+)#",$ligne,$re)){continue;}
	$md5=md5("SecFilterSelective:{$re[1]}{$re[2]}");
	$sql="INSERT IGNORE INTO freewebs_secfilters(servername,`key`,value,zmd5)
	VALUES('{$_REQUEST["servername"]}','{$re[1]}','{$re[2]}','$md5')";
	$q->QUERY_SQL($sql,"artica_backup");
	}	
}

function rules_delete_all(){
	$sql="DELETE FROM freewebs_secfilters WHERE servername='{$_REQUEST["servername"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	add_default_rules();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_REQUEST["servername"]}");
	
}

function rule_delete_single(){
	if(!is_numeric($_POST["ID"])){return;}
	$sql="DELETE FROM freewebs_secfilters WHERE servername='{$_REQUEST["servername"]}' AND ID={$_POST["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_REQUEST["servername"]}");		
}
function rule_enable_single(){
	if(!is_numeric($_POST["EnableRule"])){return;}
	$sql="UPDATE freewebs_secfilters SET `enabled`={$_POST["value"]} WHERE servername='{$_REQUEST["servername"]}' AND ID={$_POST["EnableRule"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_REQUEST["servername"]}");		
	
}

function rule_form(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();		
	if(!is_numeric($_GET["ID"])){return;}
	$f[null]="{default}";
	$f["REMOTE_ADDR"]="REMOTE_ADDR";
	$f["REMOTE_ADDR"]="REQUEST_FILENAME";
	$f["REQUEST_LINE"]="REQUEST_LINE";
	$f["REQUEST_URI"]="REQUEST_URI"; 
	$f["WEBSERVER_ERROR_LOG"]="WEBSERVER_ERROR_LOG";
	$f["REQUEST_LINE|ARG_VALUES"]="REQUEST_LINE {and} ARG_VALUES";  
	 $button="{add}";
	$actions[null]="{default}";
	$actions["deny,log"]="{deny}";
	$actions["log,pass"]="{pass}";
	
	$sql="SELECT * FROM freewebs_secfilters WHERE ID={$_GET["ID"]}";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($_GET["ID"]>0){$button="{apply}";}
	
	$re=array();
	$pattern=$ligne["value"];
	if(preg_match("#(.+?)\s+([deny|pass|log|,]+)#",$pattern,$re)){
		$pattern=$re[1];
		$action=$re[2];
	}
	
	
	$html="<table style='width:100%' class=form>
	<tr>
		<td class=legend>{type}:</td>
		<td>". Field_array_Hash($f,"type",$ligne["key"],"style:font-size:13px;padding:3px") ."</td>
	</tr>
	<tr>
		<td class=legend>{action}:</td>
		<td>". Field_array_Hash($actions,"action",$action,"style:font-size:13px;padding:3px") ."</td>
	</tr>
	<tr>
		<td class=legend>{match}:</td>	
		<td>". Field_text("pattern",$pattern,"font-size:14px;padding:3px;width:220px") ."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button($button,"SaveNewRule()")."</td>
	</tr>
	</table>
	
	<script>
		var x_SaveNewRule=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			YahooWin5Hide();			
			SecModSearch();
	}	
		
	
	function SaveNewRule(){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		XHR.appendData('rule-edit','yes');
		XHR.appendData('ID',{$_REQUEST["ID"]});
		XHR.appendData('type',document.getElementById('type').value);
		XHR.appendData('action',document.getElementById('action').value);
		XHR.appendData('pattern',document.getElementById('pattern').value);		
    	XHR.sendAndLoad('$page', 'POST',x_SaveNewRule);		
	}	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function rule_edit(){
	
	if($_POST["type"]<>null){
		$key=$_POST["type"];
	}
	
	$pattern="{$_POST["pattern"]} {$_POST["action"]}";
	$md5=md5("$key $pattern");
	$sql="INSERT IGNORE INTO freewebs_secfilters (servername,`key`,`value`,`zmd5`) VALUES('{$_POST["servername"]}','$key','$pattern','$md5')";
	
	if($_POST["ID"]>0){
		$sql="UPDATE freewebs_secfilters SET `key`='$key',`value`='$pattern',zmd5='$md5' WHERE ID='{$_REQUEST["ID"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_REQUEST["servername"]}");	
	
}

function events(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$add=imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_modsecu')");
		$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th width=1%>$add</th>
		<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("freeweb.php?mode-security-log=yes&servername={$_GET["servername"]}")));
	while (list ($num, $ligne) = each ($datas) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if(trim($ligne)==null){continue;}
		
		
		$html=$html."
		<tr class=$classtr>
		
		<td style='font-size:11px' colspan=2>$ligne</td>
		</tr>
		
		";
			
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}


?>