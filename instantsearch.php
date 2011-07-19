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
	
	if(isset($_GET["index"])){InstantSearch();exit;}
	if(isset($_GET["InstantSearchEnable"])){InstantSearchSave();exit;}
	if(isset($_GET["CrawlNow"])){CrawlNow();exit;}
	if(isset($_GET["Crawllogs"])){CrawLog();exit;}
	if(isset($_GET["logs"])){IntstantLogs();exit;}
	if(isset($_GET["refresh-logs"])){IntstantLogsRefresh();exit;}
	
	
js();



function js(){

$tpl=new templates();
$page=CurrentPageName();

$title=$tpl->_ENGINE_parse_body('{InstantSearch}');

$html="

	function InstantSearch(){
		YahooWin2('600','$page?index=yes','$title');
	
	}
	
	var x_InstantSearchSave= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		InstantSearch();
	}		
	
function InstantSearchSave(){
		var CrawlPeriod=document.getElementById('InstantSearchCrawlPeriod').value;
		var InstantSearchEnable=document.getElementById('InstantSearchEnable').value;
		var XHR = new XHRConnection();
		document.getElementById('InstantSearchDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.appendData('InstantSearchCrawlPeriod',CrawlPeriod);
		XHR.appendData('InstantSearchEnable',InstantSearchEnable);
		XHR.sendAndLoad('$page', 'GET',x_InstantSearchSave);
		 		
	}
	
	
	
function InstantCrawl(){
		YahooWin3('650','$page?CrawlNow=yes','$title');
		setTimeout('InstantCrawlResfresh()',1500);		
	}	
	
function InstantCrawlResfresh(){
	LoadAjax('instantcrawl','$page?Crawllogs=yes');

}

function InstantLogs(){
		YahooWin3('650','$page?logs=yes','$title');
		setTimeout('InstantLogsRefresh()',1500)
}

function InstantLogsRefresh(){
		LoadAjax('instantcrawl','$page?refresh-logs=yes');
}


	
	

InstantSearch();
";
	echo $html;
	
}

function InstantSearch(){

	$sock=new sockets();
	$button=Paragraphe_switch_img('{InstantSearch_enable}',"{InstantSearch_enable_text}",'InstantSearchEnable',$sock->GET_INFO('InstantSearchEnable',"{enable_disable}",300));
	
	$tt["0"]=0;
	for($i=1;$i<100;$i++){
		if($i<10){$t="0$i";}else{$t=$i;}
		$tt[$t]=$i;
	}
	
	
	$hour=$sock->GET_INFO("InstantSearchCrawlPeriod");
	if($hour==null){$hour=120;}
	$hour=$hour/60;
	
	$save="<div style='width:100%;text-align:right;margin-top:9px;border-top:1px solid #CCCCCC'>
		<input type='button' OnClick=\"javascript:InstantSearchSave();\" value='{save}&nbsp;&raquo'>
	</div>";
	
	$crawl="<div style='width:100%;text-align:right;margin-top:9px;border-top:1px solid #CCCCCC'>
		<input type='button' OnClick=\"javascript:InstantCrawl();\" value='{crawl_now}&nbsp;&raquo'>
	</div>";

	$logs="<div style='width:100%;text-align:right;margin-top:9px;border-top:1px solid #CCCCCC'>
		<input type='button' OnClick=\"javascript:InstantLogs();\" value='{events}&nbsp;&raquo'>
	</div>";	
	
	$html="<H1>{InstantSearch}</H1>
	<div id='InstantSearchDiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=60%>$button$save</td>
		<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td valign='top' class=legend nowrap>{crawl_each}:</td>
				<td valign='top' >" . Field_array_Hash($tt,'InstantSearchCrawlPeriod',$hour)."</td>
				<td valign='top' >{hours}</td>
			</tr>
			<tr>
			<td colspan=3 ><p class=caption>{crawl_each_0}</p></td>
			</tr>
			</table>
			$save
			<p>&nbsp;</p>
			$crawl
			<p>&nbsp;</p>
			$logs
		</td>
	</tr>
	</table>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function InstantSearchSave(){
	$sock=new sockets();
	$sock->SET_INFO("InstantSearchEnable",$_GET["InstantSearchEnable"]);
	$sock->SET_INFO("InstantSearchCrawlPeriod",($_GET["InstantSearchCrawlPeriod"]*60));
	
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