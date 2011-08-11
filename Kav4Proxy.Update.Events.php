<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');


$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["kav4proxy-event-search"])){events_search();exit;}
	if(isset($_GET["details"])){details();exit;}
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_KAV4PROXY}::{update_events}");
	$html="YahooWin5('625','$page?popup=yes','$title');
	
	function Kav4ProxyUpdateDetails(date){
		var dates=escape(date);
		YahooWin6('550','$page?details='+dates,date);
	}
	
	";
	echo $tpl->_ENGINE_parse_body($html);

}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<center>
		<table style='width:80%' class=form>
		<tbody>
		<tr>
			<td class=legend>{EVENT}:<td>
			<td>". Field_text("kav4proxy-event-search",null,"font-size:16px;padding:4px",null,null,null,false,"Kav4ProxyEventCheck(event)")."</td>
			<td widh=1%>". button("{search}","Kav4ProxyEventSearch()")."</td>
		</tr>
		</tbody>
		</table>
		<hr>
		<div id='kav4proxy-event-list' style='width:100%;height:450px;overflow:auto'></div>
		
		<script>
			function Kav4ProxyEventCheck(e){
				if(checkEnter(e)){Kav4ProxyEventSearch();return;}
			}
		
			function Kav4ProxyEventSearch(){
				var se=escape(document.getElementById('kav4proxy-event-search').value);
				LoadAjax('kav4proxy-event-list','$page?kav4proxy-event-search=yes&search='+se);
			
			}
			Kav4ProxyEventSearch();
			
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function events_search(){
	$page=CurrentPageName();
	$tpl=new templates();		 
	if(trim($_GET["search"])<>null){
		$_GET["search"]=$_GET["search"]."*";
		$_GET["search"]=str_replace( "**", "*",$_GET["search"]);
		$_GET["search"]=str_replace( "*", "%",$_GET["search"]);
		$sql="SELECT *,match(content) against('upd') as relevance  FROM `kav4proxy_updates` ORDER BY relevance,zDate DESC LIMIT 0,50";
		
	}else{
		$sql="SELECT subject,zDate FROM `kav4proxy_updates` ORDER BY zDate DESC LIMIT 0,50";
	}
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% colspan=2>{date}</th>
		<th colspan=2>{subject}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$distance=distanceOfTimeInWords(strtotime($ligne["zDate"]),time());
		
		$js="Kav4ProxyUpdateDetails('{$ligne["zDate"]}')";
		$ahref="<a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-size:14px;text-decoration:underline'>";
		$html=$html."
		<tr class=$classtr>
			<td width=1% nowrap style='font-size:11px'>{$ligne["zDate"]}</td>
			<td width=1% nowrap style='font-size:14px'>$distance</td>
			<td style='font-size:14px'>$ahref{$ligne["subject"]}</a></td>
		</tr>
		";
		
		
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}

function details(){
	$q=new mysql();
	$sql="SELECT content FROM kav4proxy_updates WHERE zDate='{$_GET["details"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$content=$ligne["content"];
	$content=htmlspecialchars($content);
	$content=nl2br($content);
	echo "<div class=form style='width:99%;height:450px;overflow:auto'><code>$content</code></div>";
	
	
	
}