<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	$user=new usersMenus();
		if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["params"])){params();exit;}
	if(isset($_GET["EnableArticaSMTPStatistics"])){SaveParams();exit;}
	
	
js();

function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{rbl_check_artica}");		
	echo "YahooWin3('700','$page?tabs=yes','$title');";
	
}
function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["params"]='{settings}';
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_artica_smtp_stats style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_artica_smtp_stats\").tabs();});
		</script>";		
	
}

function params(){
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();
	$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
	$ArticaStatusUsleep=$sock->GET_INFO("ArticaStatusUsleep");
	$ArticaSMTPStatsTimeFrame=$sock->GET_INFO("ArticaSMTPStatsTimeFrame");
	$ArticaSMTPStatsMaxFiles=$sock->GET_INFO("ArticaSMTPStatsMaxFiles");
	
	if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}
	if(!is_numeric($ArticaStatusUsleep)){$ArticaStatusUsleep=50000;}
	if(!is_numeric($ArticaSMTPStatsTimeFrame)){$ArticaSMTPStatsTimeFrame=2;}
	if(!is_numeric($ArticaSMTPStatsMaxFiles)){$ArticaSMTPStatsMaxFiles=2400;}
	
	
	
	
	$hoursEX[1]="1 {minute}";
	$hoursEX[1]="2 {minutes}";
	$hoursEX[1]="3 {minutes}";
	$hoursEX[1]="5 {minutes}";
	$hoursEX[10]="10 {minutes}";
	$hoursEX[15]="15 {minutes}";
	$hoursEX[30]="30 {minutes}";
	$hoursEX[60]="1 {hour}";
	$hoursEX[120]="2 {hours}";
	$hoursEX[180]="3 {hours}";
	$hoursEX[420]="4 {hours}";
	$hoursEX[480]="8 {hours}";	
	
	$enable=Paragraphe_switch_img("{ENABLE_ARTICA_SMTP_STATS}","{ARTICA_SMTP_STATS_EXPLAIN}","EnableArticaSMTPStatistics",$EnableArticaSMTPStatistics,null,550);
	$ArticaSMTPStatsTimeFrame=Field_array_Hash($hoursEX,"ArticaSMTPStatsTimeFrame",$ArticaSMTPStatsTimeFrame,"style:font-size:14px;padding:3px");
	$html="
	<div id='ENABLE_ARTICA_SMTP_STATS'>
		$enable
	<div style='width:100%;text-align:right'><hr>". button("{apply}","SaveArticaStatsEngine()")."</div>
	<br>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{ArticaStatusUsleep}:</td>
		<td style='font-size:14px'>". Field_text("ArticaStatusUsleep",$ArticaStatusUsleep,"width:160px;font-size:14px;padding:3px")."</td>
		<td>". help_icon("{ArticaStatusUsleep_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{ArticaSMTPStatsTimeFrame}:</td>
		<td style='font-size:14px'>$ArticaSMTPStatsTimeFrame</td>
		<td>". help_icon("{ArticaSMTPStatsTimeFrame_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{ArticaSMTPStatsMaxFiles}:</td>
		<td style='font-size:14px'>". Field_text("ArticaSMTPStatsMaxFiles",$ArticaSMTPStatsMaxFiles,"width:160px;font-size:14px;padding:3px")."</td>
		<td>". help_icon("{ArticaSMTPStatsMaxFiles_explain}")."</td>
	</tr>	
	</table>
	
	</div>
	<script>
	
		var x_SaveArticaStatsEngine= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_config_artica_smtp_stats');
		}			
		
		function SaveArticaStatsEngine(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableArticaSMTPStatistics',document.getElementById('EnableArticaSMTPStatistics').value);
			XHR.appendData('ArticaStatusUsleep',document.getElementById('ArticaStatusUsleep').value);
			XHR.appendData('ArticaSMTPStatsTimeFrame',document.getElementById('ArticaSMTPStatsTimeFrame').value);
			XHR.appendData('ArticaSMTPStatsMaxFiles',document.getElementById('ArticaSMTPStatsMaxFiles').value);
			document.getElementById('ENABLE_ARTICA_SMTP_STATS').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveArticaStatsEngine);		
		}
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function SaveParams(){
	$sock=new sockets();
	$sock->SET_INFO("EnableArticaSMTPStatistics",$_GET["EnableArticaSMTPStatistics"]);
	$sock->SET_INFO("ArticaStatusUsleep",$_GET["ArticaStatusUsleep"]);
	$sock->SET_INFO("ArticaSMTPStatsTimeFrame",$_GET["ArticaSMTPStatsTimeFrame"]);
	$sock->SET_INFO("ArticaSMTPStatsMaxFiles",$_GET["ArticaSMTPStatsMaxFiles"]);
	
	
	
}

?>