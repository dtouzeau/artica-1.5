<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["maintenance"])){maintenance_settings();exit;}
	
	if(isset($_GET["remote-users"])){remote_users();exit;}
	if(isset($_GET["local-users"])){local_users();exit;}
	if(isset($_GET["member-add"])){members_add();exit;}
	if(isset($_GET["member-delete"])){members_delete();exit;}		
	
	if(isset($_GET["tools"])){tools();exit;}
	if(isset($_GET["run-compile"])){task_run_sarg();exit;}
	
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["squidMaxTableDays"])){SAVE();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body("{ARTICA_DATABASE_MAINTENANCE}");
	$html="YahooWin2('650','$page?popup=yes','$title');";
	
	echo $html;
	
	
	
}

function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["maintenance"]="{maintenance}";
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	$id=time();
	
	echo "
	<div id='artica_squid_db_tabs' style='width:100%;height:400px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#artica_squid_db_tabs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
	
}	

function maintenance_settings(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	
	
	$requests=$q->COUNT_ROWS("dansguardian_events","artica_events");
	$requests=numberFormat($requests,0,""," ");
	$dansguardian_events="dansguardian_events_".date('Ym');	
	$sql="SELECT max( ID ) as tid FROM $dansguardian_events";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	$sql="SELECT zDate, DATE_FORMAT(zDate,'%M %W %Y %H:%i') as tdate FROM $dansguardian_events WHERE ID ={$ligne["tid"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	
	$lastevents=$ligne["zDate"];
	$lastevents_text=$ligne["tdate"];
	$t2=strtotime($lastevents);
	
	$sql="SELECT min( ID ) as tid FROM $dansguardian_events";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	$sql="SELECT zDate,DATE_FORMAT(zDate,'%M %W %Y') as tdate FROM $dansguardian_events WHERE ID ={$ligne["tid"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));	
	
	$first_events=$ligne["zDate"];
	$first_events_text=$ligne["tdate"];
	$t1=strtotime($first_events);
	
	$distanceOfTimeInWords=distanceOfTimeInWords($t1,$t2);
	
	
	$sock=new sockets();
	$squidMaxTableDays=$sock->GET_INFO("squidMaxTableDays");
	$squidMaxTableDaysBackup=$sock->GET_INFO("squidMaxTableDaysBackup");
	$squidMaxTableDaysBackupPath=$sock->GET_INFO("squidMaxTableDaysBackupPath");
	if($squidMaxTableDays==null){$squidMaxTableDays=730;}
	if($squidMaxTableDaysBackup==null){$squidMaxTableDaysBackup=1;}
	if($squidMaxTableDaysBackupPath==null){$squidMaxTableDaysBackupPath="/home/squid-mysql-bck";}
	
	
	$html="<div class=explain>{ARTICA_DATABASE_SQUID_MAINTENANCE_WHY}</div>
	<div id='maxdayeventsdiv'>
	<H3 style='font-size:16px;'>{status}</H3>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:14px'>{rows}:</td>
		<td><strong style='font-size:14px'>$requests</strong></td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{from}:</td>
		<td><strong style='font-size:14px'>$first_events_text</strong></td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{to}:</td>
		<td><strong style='font-size:14px'>$lastevents_text</strong></td>
	</tr>	
	<tr>
		<td class=legend style='font-size:14px'></td>
		<td align='right' style='border-top:1px solid #CCCCCC'><strong style='font-size:14px;color:#D40606'>$distanceOfTimeInWords</strong></td>
	</tr>	
	</table>
	<table style='width:100%'>		
	<tr>
		<td class=legend>{max_day_events}:</td>
		<td>". Field_text("squidMaxTableDays","$squidMaxTableDays","font-size:13px;padding:3px;width:90px")."</td>
	</tr>
	<tr>
		<td class=legend>{backup_datas_before_delete}:</td>
		<td>". Field_checkbox("squidMaxTableDaysBackup",1,"$squidMaxTableDaysBackup")."</td>
	</tr>
	<tr>
		<td class=legend>{backup_path}:</td>
		<td>". Field_text("squidMaxTableDaysBackupPath","$squidMaxTableDaysBackupPath","font-size:13px;padding:3px;width:190px")."</td>
	</tr>			
	<tr>
		<td colspan=2 align='right'>". button("{apply}","SavesquidMaxTableDays()")."</td>
	</tr>
	</table>
	</div>
<script>
	var x_SavesquidMaxTableDays= function (obj) {
			RefreshTab('artica_squid_db_tabs');
		}
	
	
	function SavesquidMaxTableDays(){
		var XHR = new XHRConnection();
		XHR.appendData('squidMaxTableDays',document.getElementById('squidMaxTableDays').value);
		XHR.appendData('squidMaxTableDaysBackup',document.getElementById('squidMaxTableDaysBackup').value);
		XHR.appendData('squidMaxTableDaysBackupPath',document.getElementById('squidMaxTableDaysBackupPath').value);
		document.getElementById('maxdayeventsdiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavesquidMaxTableDays);
	}
</script>	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("squidMaxTableDays",$_GET["squidMaxTableDays"]);
	$sock->SET_INFO("squidMaxTableDaysBackup",$_GET["squidMaxTableDaysBackup"]);
	}

