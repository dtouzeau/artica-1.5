<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	include_once('ressources/class.cron.inc');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_GET["EnableZarafaIndexer"])){SaveConfig();exit;}
	
	
popup();



function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableZarafaIndexer=$sock->GET_INFO("EnableZarafaIndexer");
	$ZarafaIndexerInterval=$sock->GET_INFO("ZarafaIndexerInterval");
	$ZarafaIndexerThreads=$sock->GET_INFO("ZarafaIndexerThreads");
	if(!is_numeric($ZarafaIndexerInterval)){$ZarafaIndexerInterval=60;}
	if(!is_numeric($ZarafaIndexerThreads)){$ZarafaIndexerThreads=2;}
	
	$ZarafaIndexerIntervals[5]="5Mn";
	$ZarafaIndexerIntervals[15]="15Mn";
	$ZarafaIndexerIntervals[30]="30Mn";
	$ZarafaIndexerIntervals[60]="1h";
	$ZarafaIndexerIntervals[120]="2h";
	$ZarafaIndexerIntervals[180]="3h";
	$ZarafaIndexerIntervals[360]="6h";
	$ZarafaIndexerIntervals[720]="12h";
	$ZarafaIndexerIntervals[720]="12h";
	$ZarafaIndexerIntervals[1440]="1 {day}";
	
	
	$html="
	<div class=explain>{zarafa_indexer_explain}</div>
	<div id='zarafa-indexer-div'>
	<p>&nbsp;</p>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable}:</td>
		<td>". Field_checkbox("EnableZarafaIndexer",1,$EnableZarafaIndexer,"ValidZIndexerForm()")."</td>
	</tr>
	<tr>
		<td class=legend>{indexing_interval}:</td>
		<td>". Field_array_Hash($ZarafaIndexerIntervals,"ZarafaIndexerInterval",$ZarafaIndexerInterval,"style:font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend>{threads_max_number}:</td>
		<td>". Field_text("ZarafaIndexerThreads",$ZarafaIndexerThreads,"font-size:13px;padding:3px;width:90px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveIndexerConfig()")."</td>
	</tr>
	</table>
	
	
	</div>	
	<script>
		function ValidZIndexerForm(){
			document.getElementById('ZarafaIndexerInterval').disabled=true;
			document.getElementById('ZarafaIndexerThreads').disabled=true;
			
			if(!document.getElementById('EnableZarafaIndexer').checked){return;}
			document.getElementById('ZarafaIndexerInterval').disabled=false;
			document.getElementById('ZarafaIndexerThreads').disabled=false;
		
		}
		
var x_SaveIndexerConfig=function(obj){
      var tempvalue=obj.responseText;
     RefreshTab('main_config_zarafa');
      }	
		
	function SaveIndexerConfig(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableZarafaIndexer').checked){XHR.appendData('EnableZarafaIndexer',1);}else{XHR.appendData('EnableZarafaIndexer',0);}
		XHR.appendData('ZarafaIndexerInterval',document.getElementById('ZarafaIndexerInterval').value);
		XHR.appendData('ZarafaIndexerThreads',document.getElementById('ZarafaIndexerThreads').value);
		document.getElementById('zarafa-indexer-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveIndexerConfig);
	}		
	ValidZIndexerForm();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveConfig(){
	$sock=new sockets();
	$sock->SET_INFO("EnableZarafaIndexer",$_GET["EnableZarafaIndexer"]);
	$sock->SET_INFO("ZarafaIndexerInterval",$_GET["ZarafaIndexerInterval"]);
	$sock->SET_INFO("ZarafaIndexerThreads",$_GET["ZarafaIndexerThreads"]);
	$sock->getFrameWork("cmd.php?zarafa-restart-server=yes");

}
