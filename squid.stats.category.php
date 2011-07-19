<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["hits"])){popup_hits();exit;}
	if(isset($_GET["size"])){popup_size();exit;}
	if(isset($_GET["search-hits"])){sitesHitslist();exit;}
	if(isset($_GET["search-size"])){sitesHitslist(1);exit;}
	if(isset($_GET["members"])){popup_members(1);exit;}
	if(isset($_GET["search-members"])){SitesMembersList();exit;}
	
	
js();	
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{category}:".$_GET["category"]);
	$html="YahooWin2('750','$page?popup=yes&time={$_GET["time"]}&category=".urlencode($_GET["category"])."','$title');";
	echo $html;
	
	
}

function popup(){
		
	$page=CurrentPageName();
	$tpl=new templates();
	
	$array["hits"]='{hits}';
	$array["size"]='{size}';
	$array["members"]='{members}';
	
	
	

while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes&time={$_GET["time"]}&category=".urlencode($_GET["category"])."\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_stats_consumption style='width:100%;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_stats_consumption').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>");
	
	
}


function popup_hits(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<table>
	<tr>
		<td><strong>{search}</td>
		<td>". Field_text("searchsites",null,"font-size:14px;padding:3px","script:CheckSearchCateSites(event)")."</td>
	</tr>
	</table>
	
	<div id='searchcatsites' style='height:550px;overflow;auto'></div>
	<script>
		function SearchCateSites(){
			var value=document.getElementById('searchsites').value;
			LoadAjax('searchcatsites','$page?search-hits=yes&time={$_GET["time"]}&category=".urlencode($_GET["category"])."&searchword='+value);
		
		}
		
		function CheckSearchCateSites(e){
			if(checkEnter(e)){SearchCateSites();}
		}
		
		SearchCateSites();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function popup_members(){
	$page=CurrentPageName();
	$tpl=new templates();
	$time=time();
	$html="
	<div style='width:100%;text-align:right'>". imgtootltip("refresh-32.png","{refresh}","SearchCateSitesMembers()")."</div>
	<div id='$time' style='height:550px;overflow;auto'></div>
	
	
	<script>
	function SearchCateSitesMembers(){
			LoadAjax('$time','$page?search-members=yes&time={$_GET["time"]}&category=".urlencode(trim($_GET["category"]))."&searchword=');
		}
		
	SearchCateSitesMembers();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_size(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<table>
	<tr>
		<td><strong>{search}</td>
		<td>". Field_text("searchsites",null,"font-size:14px;padding:3px","script:CheckSearchSizeCateSites(event)")."</td>
	</tr>
	</table>
	
	<div id='searchcatsites-size' style='height:550px;overflow;auto'></div>
	<script>
		function SearchCateSitesSize(){
			var value=document.getElementById('searchsites').value;
			LoadAjax('searchcatsites-size','$page?search-size=yes&time={$_GET["time"]}&category=".urlencode($_GET["category"])."&searchword='+value);
		
		}
		
		function CheckSearchSizeCateSites(e){
			if(checkEnter(e)){SearchCateSitesSize();}
		}
		
		
		SearchCateSitesSize();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
}

function sitesHitslist($bysize=0){
	
	$page=CurrentPageName();
	$tpl=new templates();	
	$cats=$_GET["category"];
	$cats_sql="category='$cats'";
	if($cats=="unknown"){
		$cats_sql="LENGTH(category)=0";
	}
	$order="tsum";
	if($bysize==1){$order="tsize";}
	
	if($_GET["time"]=="0"){
		$sqltime="AND WEEK(days)=WEEK(NOW()) AND YEAR(days)=YEAR(NOW())";
	}
	
	if($_GET["time"]=="1"){
		$sqltime="AND MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";
	}	
	
	$html="
	<H2>$cats</H2>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{hits}</th>
			<th>{websites}</th>
			<th>{size}</th>
			<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	$sql="SELECT SUM(website_hits) as tsum,
	SUM(	website_size) as tsize,websites FROM 
	squid_events_sites_day WHERE $cats_sql $sqltime GROUP BY websites ORDER BY $order DESC LIMIT 0,100";	

	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		echo $q->mysql_error;
	}
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
		
		$website=$ligne["websites"];
		if(preg_match("#^www\.(.+)#",$website,$re)){$website=$re[1];}
		$js="Loadjs('squid.categorize.php?www=$website')";
		$size=FormatBytes($ligne["tsize"]/1024);
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["tsum"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["websites"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			<td width=1%>". imgtootltip("add-database-32.png","{categorize}","$js")."</td>
			</tr>";	
	
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function SitesMembersList($bysize=0){
	$page=CurrentPageName();
	$tpl=new templates();	
	$cats=$_GET["category"];
	$cats_sql="category='$cats'";
	if($cats=="unknown"){
		$cats_sql="LENGTH(category)=0";
	}
	$order="tsum";
	if($bysize==1){$order="tsize";}
	
	if($_GET["time"]=="0"){
		$sqltime="AND WEEK(days)=WEEK(NOW()) AND YEAR(days)=YEAR(NOW())";
	}
	
	if($_GET["time"]=="1"){
		$sqltime="AND MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";
	}	
	
	$html="
	<H2>$cats</H2>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{hits}</th>
			<th>{websites}</th>
			<th>{size}</th>
			<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	$sql="SELECT SUM(hits) as tsum,
	SUM(size) as tsize,client FROM 
	squid_events_clients_sites WHERE $cats_sql $sqltime GROUP BY client ORDER BY $order DESC LIMIT 0,100";	

	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		echo $q->mysql_error;
	}
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
		
		$client=$ligne["client"];
		
		$js="Loadjs('squid.stats.client.php?client-js=yes&time={$_GET["time"]}&client=". urlencode($client)."')";
		$size=FormatBytes($ligne["tsize"]/1024);
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["tsum"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$client</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			<td width=1%>". imgtootltip("add-database-32.png","{categorize}","$js")."</td>
			</tr>";	
	
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}


