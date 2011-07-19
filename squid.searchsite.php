<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	if(isset($_GET["websites"])){search_by_websites();exit;}
	if(isset($_GET["size"])){search_by_websites(1);exit;}
	if(isset($_GET["category"])){search_by_category(1);exit;}
	if(isset($_GET["members"])){search_by_members(1);exit;}
	
	
	
	
	
tabs();


function tabs(){

	
	$page=CurrentPageName();
	$tpl=new templates();
	$find=urlencode($_GET["find"]);
	$array["websites"]='{websites}';
	$array["size"]='{size}';
	$array["category"]='{category}';
	$array["members"]='{members}';
	
	$time=time();
	
	

while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes&search-pattern=$find\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_{$time}_search style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_{$time}_search').tabs({
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

function search_by_websites($size=0){
	$search=$_GET["search-pattern"];
	$tpl=new templates();
	$search="%$search%";
	$search=str_replace("*","%",$search);
	$search=str_replace("%%","%",$search);
	$order="thits";
	if($size==1){$order="tsize";}
	$sql="SELECT SUM(website_size) as tsize ,websites, SUM(website_hits) as thits,category FROM squid_events_sites_day
	WHERE websites LIKE '$search' GROUP BY websites,category ORDER BY $order DESC LIMIT 0,100
	";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		
		echo $tpl->_ENGINE_parse_body("
		<code style='font-size:11px'>$sql</code>
		<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	
	$html=CategorizeAll()."
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{hits}</th>
			<th>{websites}</th>
			<th>{category}</th>
			<th>{size}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	//javascript:;
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$www=$ligne["websites"];
		$size=$ligne["tsize"];
		$size=$size/1024;
		$size=FormatBytes($size);
		$ligne["category"]=str_replace(",","<br>",$ligne["category"]);
		$js="Loadjs('squid.categorize.php?www=$www')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["thits"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$www</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["category"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			</tr>";
	
		}		
	
	$html=$html."</table>";

	echo $tpl->_ENGINE_parse_body($html);	
	
	
}


function search_by_category(){
	$search=$_GET["search-pattern"];
	$tpl=new templates();
	$search="%$search%";
	$search=str_replace("*","%",$search);
	$search=str_replace("%%","%",$search);
	$order="thits";
	if($size==1){$order="tsize";}
	$sql="SELECT SUM(website_size) as tsize ,SUM(website_hits) as thits,category,websites FROM squid_events_sites_day
	WHERE websites LIKE '$search' GROUP BY category,websites ORDER BY $order DESC LIMIT 0,100
	";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		
		echo $tpl->_ENGINE_parse_body("
		<code style='font-size:11px'>$sql</code>
		<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{hits}</th>
			<th>{websites}</th>
			<th>{category}</th>
			<th>{size}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	//javascript:;
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$www=$ligne["websites"];
		$size=$ligne["tsize"];
		$size=$size/1024;
		$size=FormatBytes($size);
		
		if($ligne["category"]==null){$ligne["category"]="unknown";}
		$js="Loadjs('squid.stats.category.php?category-js=yes&time=1&category=".urlencode($ligne["category"])."')";
		$jsWeb="Loadjs('squid.categorize.php?www=$www')";
		//$js="Loadjs('squid.categorize.php?www=$www')";
		
		$ligne["category"]=str_replace(",","<br>",$ligne["category"]);
		
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["thits"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$jsWeb\">$www</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["category"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			</tr>";
	
		}		
	
	$html=$html."</table>";

	echo $tpl->_ENGINE_parse_body($html);	
		
	
}

function search_by_members($size=0){
	$search=$_GET["search-pattern"];
	$tpl=new templates();
	$search="%$search%";
	$search=str_replace("*","%",$search);
	$search=str_replace("%%","%",$search);
	$order="thits";
	if($size==1){$order="tsize";}
	$sql="SELECT SUM(size) as tsize ,SUM(hits) as thits,client,websites FROM squid_events_clients_sites
	WHERE websites LIKE '$search' GROUP BY client,websites ORDER BY $order DESC LIMIT 0,100
	";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		
		echo $tpl->_ENGINE_parse_body("
		<code style='font-size:11px'>$sql</code>
		<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{hits}</th>
			<th>{websites}</th>
			<th>{members}</th>
			<th>{size}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	//javascript:;
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$www=$ligne["websites"];
		$size=$ligne["tsize"];
		$size=$size/1024;
		$size=FormatBytes($size);
		
		if($ligne["category"]==null){$ligne["category"]="unknown";}
		//$js="Loadjs('squid.stats.category.php?category-js=yes&time=1&category=".urlencode($ligne["category"])."')";
		//$js="Loadjs('squid.categorize.php?www=$www')";
		$client=urlencode($ligne["client"]);
		$js="Loadjs('squid.stats.client.php?client-js=yes&time=1&client=$client')";
		
		$ligne["category"]=str_replace(",","<br>",$ligne["category"]);
		
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["thits"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$www</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["client"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			</tr>";
	
		}		
	
	$html=$html."</table>";

	echo $tpl->_ENGINE_parse_body($html);	
	
}


function CategorizeAll(){
	$search=$_GET["search-pattern"];
	if(strlen($search)<2){return null;}
	
	return "<div style='text-align:right;margin:5px'>
		<a href='javascript:blur();' style='font-size:14px;text-decoration:underline' 
			OnClick=\"javascript:Loadjs('squid.visited.php?CategorizeAll-js&query=$search');\">{categorize_this_query}
		</a>
		</div>";
	
	
	
	
}
	
