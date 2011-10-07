<?php
$GLOBALS["ICON_FAMILY"]="SYSTEM";
session_start();
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(!isset($_SESSION["uid"])){echo "window.location.href = 'logoff.php'";die();}


include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
$users=new usersMenus();
if(!$users->AsAnAdministratorGeneric){writelogs("Redirect to users.index.php",__FUNCTION__,__FILE__,__LINE__);header('location:miniadm.php');exit;}

if(isset($_GET["showInfos"])){showInfos_js();exit;}

if(isset($_GET["showInfos-id"])){showInfos_popup();exit;}
if(isset($_POST["disable"])){disable();exit;}
page();

function page(){

$page=CurrentPageName();
$tpl=new templates();
$q=new mysql();
$sql="SELECT * FROM adminevents WHERE enabled=1 ORDER BY zDate DESC LIMIT 0,50";
$results=$q->QUERY_SQL($sql,"artica_events");
$html="<table style='width:100%' class=form><tbody>";

$f=squid_filters_infos();
while (list ($num, $ligne) = each ($f) ){
	if($ligne["subject"]==null){continue;}
	$ligne["subject"]=$tpl->_ENGINE_parse_body($ligne["subject"]);
	$strlen=strlen($ligne["subject"]);
	$org_text=$ligne["subject"];
	if($strlen>25){$text=substr($ligne["subject"], 0,21)."...";}else{$text=$org_text;}
	
	$html=$html."
	<tr>
		<td width=1%><img src='img/{$ligne["icon"]}'></td>
		<td style='font-size:11px' nowrap><a href=\"javascript:blur();\" OnClick=\"javascript:{$ligne["js"]}\" style='font-size:11px;text-decoration:underline'>$text</a></td>
	</tr>
	";	
}

if(mysql_num_rows($results)==0){$html=$html."</tbody></table>";echo $html;return;}


//if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}





while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["icon"]=="danger64.png"){$ligne["icon"]="danger32.png";}
	if($ligne["icon"]=="warning64.png"){$ligne["icon"]="warning-panneau-32.png";}
	if($ligne["icon"]=="pluswarning64.png"){$ligne["icon"]="warning-panneau-32.png";}
	if($ligne["icon"]=="danger32.png"){$ligne["icon"]="warning-panneau-32.png";}
	if($ligne["icon"]=="license-error-64.png"){$ligne["icon"]="license-error-32.png";}
	
	
	
	
	$ligne["subject"]=$tpl->_ENGINE_parse_body($ligne["subject"]);
	$strlen=strlen($ligne["subject"]);
	$org_text=$ligne["subject"];
	if($strlen>25){$text=substr($ligne["subject"], 0,21)."...";}else{$text=$org_text;}
	$text=texttooltip($text,$org_text,"Loadjs('$page?showInfos={$ligne["zmd5"]}')",null,0,"font-size:11px;text-decoration:underline");
	$html=$html."
	<tr>
		<td width=1%><img src='img/{$ligne["icon"]}'></td>
		<td style='font-size:11px' nowrap>$text</td>
	</tr>
	";
	}





$html=$html."</tbody></table>
<div style='width:100%;text-align:right'>". imgtootltip("20-refresh.png","{refresh}","LoadAjax('admin-left-infos','admin.index.status-infos.php');")."</div>

";

echo $tpl->_ENGINE_parse_body($html);

}

function squid_filters_infos(){
	$sock=new sockets();
	$ligne2=array();
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	if(!$users->SQUID_INSTALLED){return null;}
	$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
	if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
	
	if($SQUIDEnable==0){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:squid_filters_infos():: SQUIDEnable is not enabled... Aborting\n";}
		return;
	}	
	
	$sql="SELECT count(*) as tcount FROM `visited_sites` WHERE LENGTH(category)=0";	
	$q=new mysql_squid_builder();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql));
	if(!$q->ok){echo $q->mysql_error;}
	if($ligne["tcount"]==0){return null;}
	
	
	$ligne2[0]["icon"]="32-categories.png";
	$ligne2[0]["subject"]=$ligne["tcount"]." {websites_not_categorized}";
	$ligne2[0]["js"]="Loadjs('squid.visited.php?onlyNot=yes');";
	
	return $ligne2;

}


function showInfos_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT subject FROM adminevents WHERE zmd5='{$_GET["showInfos"]}'","artica_events"));
	$title=$tpl->_ENGINE_parse_body($ligne["subject"]);
	$html="YahooWin('500','$page?showInfos-id={$_GET["showInfos"]}','$title')";
	echo $html;
}

function showInfos_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT * FROM adminevents WHERE zmd5='{$_GET["showInfos-id"]}'","artica_events"));
	$icon=$ligne["icon"];
	if(preg_match("#([0-9]+)#", $icon,$re)){
		$icon=str_replace($re[1], 64, $icon);
		if(!is_file("img/$icon")){$icon=$ligne["icon"];}
	}
	
	
	if($ligne["jslink"]<>null){
		$link="
		<tr>
			<td colspan=2 align='right'>
			<table style='width:100%'>
			<tbody>
				<tr>
					<td valign='middle' align='right' style='font-size:16px;font-weight:bold'>{goto}</td>
					<td width=1%>". imgtootltip("arrow-right-64.png","{goto}",$ligne["jslink"])."</td>
				</tr>
				</tbody>
			</table>
			</td>
		</tr>
		
		";
	}
	
	$title=$tpl->_ENGINE_parse_body($ligne["subject"]);	
	$html="<div style='font-size:18px;margin-bottom:20px'>$title</div>
	<table style='width:100%'>
	<tbody>
	<tr>
		<td width=1% valign='top'><img src='img/$icon'></td>
		<td width=99%' valign='top'><div class=explain style='font-size:14px'>{$ligne["text"]}</div></td>
	</tr>$link
			<td colspan=2 align='left' style='font-size:16px;font-weight:bold'><a href=\"javascript:blur();\" OnClick=\"javascript:RemoveNotifAdmin()\"
			style='font-size:16px;font-weight:bold;text-decoration:underline'>{ihavereaditremove}</a>
			
			</td>
		</tr>
		
	</tbody>
	</table>
	<script>
	var x_RemoveNotifAdmin= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);return;}
		YahooWinHide();
		LoadAjax('admin-left-infos','admin.index.status-infos.php');
	}		
	
	
	
	function RemoveNotifAdmin(){
		var XHR = new XHRConnection();
		XHR.appendData('disable','{$_GET["showInfos-id"]}');
		XHR.sendAndLoad('$page', 'POST',x_RemoveNotifAdmin);	
		
	}		
</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function disable(){
	$q=new mysql();
	$q->QUERY_SQL("UPDATE adminevents SET enabled=0 WHERE zmd5='{$_POST["disable"]}'","artica_events");
	if(!$q->ok){echo $q->mysql_error;}
}