<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.kav4samba.inc');
	
	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}

	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
	if(!CheckSambaRights()){echo "<H1>$ERROR_NO_PRIVS</H1>";die();}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["members"])){members();exit;}
	if(isset($_GET["files"])){files();exit;}
	
	
	if(isset($_GET["browse-files-list"])){files_list();exit;}
	if(isset($_GET["browse-smbstatus-list"])){members_list();exit;}
	js();
	
	
function js(){
	$q=new mysql();
	$page=CurrentPageName();
	$tpl=new templates();
	$count=$q->COUNT_ROWS("smbstatus_users", "artica_events");
	$title=$tpl->_ENGINE_parse_body("$count {members_connected}");
	$html="YahooWin4('720','$page?tabs=yes','$title');";
	echo $html;
	
}
function tabs(){
	$q=new mysql();
	$page=CurrentPageName();
	$tpl=new templates();
	$count=$q->COUNT_ROWS("smbstatus_users", "artica_events");		
	$tpl=new templates();
	$array["members"]="$count {members_connected}";
	$array["files"]='{files}';
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	

	echo "
	<div id=main_smbtsatustab style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_smbtsatustab').tabs();
			
			
			});
		</script>";	
	
	
}


function members(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	
	$html="
	<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{members}:</td>
				<td>". Field_text("smbstatus-members-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseSmbStatusSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseSmbStatusSearch()")."</td>
			</tr>
			</table>
	</center>		
	<div id='browse-smbstatus-list' style='width:100%;height:430px;overflow:auto;text-align:center'></div>
	
<script>
		function BrowseSmbStatusSearchCheck(e){
			if(checkEnter(e)){BrowseSmbStatusSearch();}
		}
		
		function BrowseSmbStatusSearch(){
			var se=escape(document.getElementById('smbstatus-members-search').value);
			LoadAjax('browse-smbstatus-list','$page?browse-smbstatus-list=yes&search='+se);
		}
		
		
	BrowseSmbStatusSearch();
</script>	
	
	";
	
	
echo $tpl->_ENGINE_parse_body($html);
}

function members_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if($_GET["search"]<>null){
		$_GET["search"]=str_replace("*", "%", $_GET["search"]);
		$filter="AND ((username LIKE '{$_GET["search"]}') OR (usersgroup LIKE '{$_GET["search"]}') OR (computer LIKE '{$_GET["search"]}'))";
	}
	
	if(isset($_GET["byPid"])){$filter="AND pid='{$_GET["byPid"]}'";}

	$html="
	<div style='width:100%;height:430px;overflow:auto;text-align:center'>
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% nowrap>{date}</th>
		<th width=1% nowrap>{member}</th>
		<th width=1%>{group}</th>
		<th width=25%>{computer}</th>
		<th width=25%>{share}</th>
		<th width=25%>{files}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$maxlen=30;
		$sql="SELECT * FROM smbstatus_users WHERE 1 $filter ORDER BY username LIMIT 0,150";
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_events");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$pid=$ligne["pid"];
		$ligne2=mysql_fetch_array($q->QUERY_SQL("SELECT COUNT(zmd5) as tcount FROM smbstatus_users_dirs WHERE pid='$pid'","artica_events"));
		$files=$ligne2["tcount"];
		$linkfiles="
		<a href=\"javascript:blur();\" OnClick=\"javascript:YahooWin5('650','$page?browse-files-list=yes&byPid=$pid','PID:$pid');\"
		style='font-size:14px;font-weight:bold;color:$color;text-decoration:underline'>
		";
		if($files==0){$linkfiles=null;}
	$color="black";
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:bold;color:$color' nowrap>{$ligne["zDate"]}</td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1% nowrap>{$ligne["username"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50%>{$ligne["usersgroup"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50>{$ligne["computer"]}<div style='font-size:10px'><i>{$ligne["ip_addr"]}</i></div></a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50%>{$ligne["sharename"]}</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% align=center>$linkfiles&nbsp;$files&nbsp;</a></td>
			
		</tr>
		";
	}
	
	$html=$html."</table></center></div>";
	echo $tpl->_ENGINE_parse_body($html);
}



function files_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if($_GET["search"]<>null){
		$_GET["search"]=str_replace("*", "%", $_GET["search"]);
		$filter="AND ((	directory LIKE '{$_GET["search"]}') OR (filepath LIKE '{$_GET["search"]}'))";
	}
	
	if(isset($_GET["byPid"])){$filter="AND pid='{$_GET["byPid"]}'";}

	$html="
	<div style='width:100%;height:430px;overflow:auto;text-align:center'>
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% nowrap>{date}</th>
		<th width=1%>{members}</th>
		<th width=1% nowrap>{directory}</th>
		<th width=1%>{files}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$maxlen=30;
		$sql="SELECT * FROM smbstatus_users_dirs WHERE 1 $filter ORDER BY zDate DESC LIMIT 0,150";
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_events");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$pid=$ligne["pid"];
		$ligne2=mysql_fetch_array($q->QUERY_SQL("SELECT COUNT(ID) as tcount FROM smbstatus_users WHERE pid='$pid'","artica_events"));
		$users=$ligne2["tcount"];
		
		$linkusers="
		<a href=\"javascript:blur();\" OnClick=\"javascript:YahooWin5('650','$page?browse-smbstatus-list=yes&byPid=$pid','PID:$pid');\"
		style='font-size:14px;font-weight:bold;color:$color;text-decoration:underline'>
		";		
		if($users==0){$linkusers=null;}
		
		
	$color="black";
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:bold;color:$color' nowrap>{$ligne["zDate"]}</td>
			<td style='font-size:14px;font-weight:bold;color:$color' nowrap align=center>$linkusers&nbsp;$users&nbsp;</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1% nowrap>{$ligne["directory"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50%>{$ligne["filepath"]}</a></td>
		</tr>
		";
	}
	
	$html=$html."</table></center></div>";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function files(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	
	$html="
	<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{files}:</td>
				<td>". Field_text("smbstatus-files-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseSmbStatusFilesSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseSmbStatusFilesSearch()")."</td>
			</tr>
			</table>
	</center>		
	<div id='browse-smbstatusF-list' style='width:100%;height:430px;overflow:auto;text-align:center'></div>
	
<script>
		function BrowseSmbStatusFilesSearchCheck(e){
			if(checkEnter(e)){BrowseSmbStatusFilesSearch();}
		}
		
		function BrowseSmbStatusFilesSearch(){
			var se=escape(document.getElementById('smbstatus-files-search').value);
			LoadAjax('browse-smbstatusF-list','$page?browse-files-list=yes&search='+se);
		}
		
		
	BrowseSmbStatusFilesSearch();
</script>	
	
	";
	
	
echo $tpl->_ENGINE_parse_body($html);
}