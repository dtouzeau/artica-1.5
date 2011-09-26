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
	if(isset($_GET["domains"])){domains();exit;}
	if(isset($_GET["browse-domains-list"])){domains_list();exit;}
	js();
	
	
function js(){
	$q=new mysql();
	$page=CurrentPageName();
	$tpl=new templates();
	$count=$q->COUNT_ROWS("samba_domains_info", "artica_backup");	
	$title=$tpl->_ENGINE_parse_body("$count {domains}");
	$html="YahooWin4('865','$page?tabs=yes','$title');";
	echo $html;
	
}
function tabs(){
	$q=new mysql();
	$page=CurrentPageName();
	$tpl=new templates();
	$count=$q->COUNT_ROWS("samba_domains_info", "artica_backup");	
	$tpl=new templates();
	$array["domains"]="$count {domains}";
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	

	echo "
	<div id=main_smbwbinfodomains style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_smbwbinfodomains').tabs();
			
			
			});
		</script>";	
	
	
}


function domains(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	
	$html="
	<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{domains}:</td>
				<td>". Field_text("smbstatus-domains-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseSmbStatusDOMAINSSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseSmbStatusDOMAINSSearch()")."</td>
			</tr>
			</table>
	</center>		
	<div id='browse-domains-list' style='width:100%;height:430px;overflow:auto;text-align:center'></div>
	
<script>
		function BrowseSmbStatusDOMAINSSearchCheck(e){
			if(checkEnter(e)){BrowseSmbStatusDOMAINSSearch();}
		}
		
		function BrowseSmbStatusDOMAINSSearch(){
			var se=escape(document.getElementById('smbstatus-domains-search').value);
			LoadAjax('browse-domains-list','$page?browse-domains-list=yes&search='+se);
		}
		
		
	BrowseSmbStatusDOMAINSSearch();
</script>	
	
	";
	
	
echo $tpl->_ENGINE_parse_body($html);
}

function domains_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if($_GET["search"]<>null){
		$_GET["search"]=str_replace("*", "%", $_GET["search"]);
		$filter="AND ((domain LIKE '{$_GET["search"]}') OR (Alt_Name LIKE '{$_GET["search"]}'))";
	}
	
	

	$html="
	<div style='width:100%;height:430px;overflow:auto;text-align:center'>
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% nowrap>{domain}</th>
		<th width=1%>SID</th>
		<th width=25%>Active Directory</th>
		<th width=25%>Native</th>
		<th width=25%>Primary</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$maxlen=30;
		$sql="SELECT * FROM samba_domains_info WHERE 1 $filter ORDER BY `Primary` DESC LIMIT 0,150";
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ad_img="<img src='img/20-check.png'>";
		$native_img="<img src='img/20-check.png'>";
		$primary_img="<img src='img/20-check.png'>";
		if($ligne["AD"]==0){$ad_img="&nbsp;";}
		if($ligne["Native"]==0){$native_img="&nbsp;";}
		if($ligne["Primary"]==0){$primary_img="&nbsp;";}
		
		
	
		$color="black";
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:bold;color:$color' nowrap width=99%>{$ligne["domain"]} ({$ligne["Alt_Name"]})</td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1% nowrap>{$ligne["SID"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1% align=center>$ad_img</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1% align=center>$native_img</td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1% align=center>$primary_img</a></td>
		</tr>
		";
	}
	
	$html=$html."</table></center></div>";
	echo $tpl->_ENGINE_parse_body($html);
}


