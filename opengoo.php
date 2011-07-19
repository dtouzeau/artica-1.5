<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsArticaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");
		die();		
	}	
	
	if(isset($_GET["index"])){opengooadmin();exit;}
	if(isset($_GET["port"])){opengooadminSave();exit;}

	
	
js();



function js(){

$tpl=new templates();
$page=CurrentPageName();

$title=$tpl->_ENGINE_parse_body('{APP_OPENGOO}');

$html="

	function opengooadmin(){
		YahooWin2('600','$page?index=yes','$title');
	
	}
	
	var x_opengooadminSave= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		opengooadmin();
	}		
	
function opengooadminSave(){
		var port=document.getElementById('port').value;
		var XHR = new XHRConnection();
		document.getElementById('InstantSearchDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.appendData('port',port);
		XHR.sendAndLoad('$page', 'GET',x_opengooadminSave);
		 		
	}
opengooadmin();
";
	echo $html;
	
}

function opengooadmin(){

	$sock=new sockets();
	
	$port=$sock->GET_INFO("ApacheGroupWarePort");
	
	
	$save="<div style='width:100%;text-align:right;margin-top:9px;border-top:1px solid #CCCCCC'>
		<input type='button' OnClick=\"javascript:opengooadminSave();\" value='{save}&nbsp;&raquo'>
	</div>";
	
	
	
	$html="<H1>{APP_OPENGOO}</H1>
	<img src='img/logo_goo.png'>
	<div id='InstantSearchDiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=60%>&nbsp;</td>
		<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td valign='top' class=legend nowrap>{listen_port}:</td>
				<td valign='top' >" . Field_text('port',$port,'width:30px')."</td>
				
			</tr>
			
			</table>
			$save
			
		</td>
	</tr>
	</table>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function opengooadminSave(){
	$sock=new sockets();
	$sock->SET_INFO("ApacheGroupWarePort",$_GET["port"]);
	$sock->getFrameWork("cmd.php?RestartApacheGroupwareNoForce=yes");
	
}

function CrawlNow(){
	///etc/artica-postfix/InstantSearch.time
	$sock=new sockets();
	$datas=$sock->getfile('InstantSearchEnableCrawl');
	
	$crawl="<div style='width:100%;text-align:right;margin-top:9px;border-top:1px solid #CCCCCC'>
		<input type='button' OnClick=\"javascript:InstantCrawlResfresh();\" value='{refresh}&nbsp;&raquo'>
	</div>";	
	
	$html="<H1>{InstantSearch}</h1>$crawl
	
	".RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto' id='instantcrawl'></div>");
	
	$tpl=new templates();
	$tpl->_ENGINE_parse_body($html);
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function CrawLog(){
	if(!is_file("/usr/share/artica-postfix/ressources/logs/InstantCrawl.log")){return null;}
		$datas=file_get_contents("/usr/share/artica-postfix/ressources/logs/InstantCrawl.log");
		$bl=explode("\n",$datas);
	
	
	while (list ($num, $val) = each ($bl) ){
		if(trim($val)==null){continue;}
		$html=$html ."<div><code>" .htmlspecialchars($val)."</code></div>\n";
	}
	
	echo $html;
	
}

function IntstantLogsRefresh(){
	if(!is_file("/usr/share/artica-postfix/ressources/logs/web/instantsearch-cron.log")){return null;}
    $html="<table style='width:100%'>
    <tr>
    	<th>&nbsp;</th>
    	<th>{start}</th>
    	<th>{indexed}</th>
    	<th>{skipped}</th>
    	<th>{end}</th>
    </tR>
    
    ";		
	$ini=new Bs_IniHandler("/usr/share/artica-postfix/ressources/logs/web/instantsearch-cron.log");
	
	while (list ($num, $val) = each ($ini->_params) ){
		

		
		if(trim($num)==null){continue;}
				$html=$html . "
		    <tr>
    	<td width=1%><img src='img/fw_bold.gif'>
    	<td><strong>{$val["StartOn"]}</strong></td>
    	<td><strong>{$val["indexed"]}</strong></td>
    	<td><strong>{$val["skipped"]}</strong></td>
    	<td><strong>$num</strong></td>
    </tR>";
	}
	
	$html=$html . "</table>";
	echo $html;	
	
}

function IntstantLogs(){
///etc/artica-postfix/InstantSearch.time
	
	$crawl="<div style='width:100%;text-align:right;margin-top:9px;border-top:1px solid #CCCCCC'>
		<input type='button' OnClick=\"javascript:InstantLogsRefresh();\" value='{refresh}&nbsp;&raquo'>
	</div>";	
	
	$html="<H1>{InstantSearch}</h1>$crawl
	
	".RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto' id='instantcrawl'></div>");
	
	$tpl=new templates();
	$tpl->_ENGINE_parse_body($html);
	echo $tpl->_ENGINE_parse_body($html);	
	
}



?>