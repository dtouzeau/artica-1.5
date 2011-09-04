<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.rtmm.tools.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.artica.graphs.inc');

$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["virus"])){virus();exit;}
	if(isset($_GET["categories"])){categories_month();exit;}
	if(isset($_GET["countries"])){countries_month();exit;}
	if(isset($_GET["category-details"])){category_details_tabs();exit;}
	if(isset($_GET["category-details-period"])){category_details_period();exit;}
	if(isset($_GET["category-details-websites"])){category_details_websites();exit;}
	if(isset($_GET["category-details-countries"])){category_details_countries();exit;}
	
tabs();

function category_details_tabs(){
		$font_size=$_GET["font-size"];
		if($font_size==null){$font_size="100%";}
		$tpl=new templates();
		$page=CurrentPageName();
		$users=new usersMenus();
		$array["period"]="{period} {$_GET["period"]}";
		$array["websites"]="TOP {websites}";
		$array["countries"]="TOP {countries}";
		
		
	while (list ($num, $ligne) = each ($array) ){
			$tab[]="<li><a href=\"$page?category-details-$num=yes&period={$_GET["period"]}&category={$_GET["category"]}\"><span style='font-size:$font_size'>$ligne</span></a></li>\n";
		}
	$html="
		<div id='main_categorydetail_stats' style='background-color:white;margin-top:10px;height:650px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_categorydetail_stats').tabs();
			

			});
		</script>
		";	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function category_details_countries(){
		$tpl=new templates();
		$page=CurrentPageName();	
		$pngfile="ressources/logs/web/kav4proxy.".md5("{$_GET["category"]}.{$_GET["period"]}").".countries.png";
	
	if($_GET["period"]=="month"){
		$tri="MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";
	}
	$q=new mysql();
	$sql="SELECT SUM(hits) as thits,country FROM kav4proxyDays WHERE $tri 
	AND category='{$_GET["category"]}' GROUP BY country ORDER BY thits DESC LIMIT 0,10";
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	if(mysql_num_rows($results)<2){return ;}	
	
	if(!$q->ok){echo "<H2>$q->mysql_error\n</H2>";return;}
	
	$html="
	<div style='width:100%;height:350px;overflow;auto'>
	<center><img src='$pngfile'></center>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{hits}</th>
		<th width=1% colspan=2>{country}</th>
		
	</tr>
</thead>
<tbody class='tbody'>";	
	$gp->xdata[]="";
	$gp->ydata[]="";
	
	
	
	@unlink($pngfile);	
	$gp=new artica_graphs($pngfile,0);
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$gp->xdata[]=$ligne["thits"];
			$gp->ydata[]=$ligne["country"];
			$country=$ligne["country"];
			if($country<>null){$img_country=GetFlags($country);}else{$img_country="flags/name.png";}
			
			
			
			
			//$uri="<a href=\"javascript:blur()\" OnClick=\"YahooWin2('750','$page?category-details=yes&period=month&category={$ligne["category"]}','$text_category');\" style='text-decoration:underline'>";
			
			$html=$html."<tr class=$classtr>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["thits"]}</strong></td>
			<td width=1% nowrap><strong style='font-size:14px'><img src='img/$img_country'></td>
			<td width=99% nowrap align=left><strong style='font-size:14px'>$country</a></strong></td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	";
	
	$gp->width=350;
	$gp->height=350;
	$gp->ViewValues=false;
	$gp->x_title="{countries}";
	$gp->pie();	

	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function category_details_websites(){
			$tpl=new templates();
		$page=CurrentPageName();	
	
	if($_GET["period"]=="month"){
		$tri="MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";
	}
	$q=new mysql();
	$sql="SELECT SUM(hits) as thits,websites,country FROM kav4proxyDays WHERE $tri 
	AND category='{$_GET["category"]}' GROUP BY websites,country ORDER BY thits DESC LIMIT 0,50";
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	if(mysql_num_rows($results)<2){return ;}	
	
	if(!$q->ok){echo "<H2>$q->mysql_error\n</H2>";return;}
	
	$html="
	<div style='width:100%;height:590px;overflow:auto'>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{hits}</th>
		<th width=1% colspan=2>{country}</th>
		<th width=1% colspan=2>{websites}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ligne["size"]=FormatBytes($ligne["size"]/1024);
			$country=$ligne["country"];
			if($country<>null){$img_country=GetFlags($country);}else{$img_country="flags/name.png";}
			$categorize=imgtootltip("add-database-32.png","{categorize} {$ligne["websites"]}","Loadjs('squid.categorize.php?www={$ligne["websites"]}&bykav=yes');");
			$length=strlen($ligne["websites"]);
			$www=$ligne["websites"];
			if($length>53){$www=substr($www, 0,50)."...";}
			$html=$html."<tr class=$classtr>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["thits"]}</strong></td>
			<td width=1% nowrap  align=center><strong style='font-size:14px'>". imgtootltip($img_country,$country)."</strong></td>
			<td width=99% nowrap><strong style='font-size:14px'>{$ligne["country"]}</strong></td>
			<td width=1% nowrap><strong style='font-size:14px'>$www</strong></td>
			<td width=1% nowrap><strong style='font-size:14px'>$categorize</td>
			
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	</div>
	";	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function category_details_period(){
			$tpl=new templates();
		$page=CurrentPageName();	
	
	if($_GET["period"]=="month"){
		$tri="MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";
	}
	$q=new mysql();
	$sql="SELECT SUM(hits) as thits,days,category FROM kav4proxyDays WHERE $tri AND category='{$_GET["category"]}' GROUP BY days ORDER BY days";
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	if(mysql_num_rows($results)<2){return ;}
	
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$xdata[]=$ligne["days"];
		$ydata[]=$ligne["thits"];
		$c++;	
}
	$md=md5("{$_GET["category"]}{$_GET["period"]}");
	$targetedfile="ressources/logs/".basename(__FILE__).".$md.png";
	if(is_file($targetedfile)){@unlink($targetedfile);}
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=650;
	$gp->height=450;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title="Mn";
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	$tpl=new templates();
	
	echo "<center style='margin:3px;padding:5px;border:1px solid #CCCCCC'><img src='$targetedfile'></center>";
	
}


function tabs(){
		$font_size=$_GET["font-size"];
		if($font_size==null){$font_size="14px";}
		$tpl=new templates();
		$page=CurrentPageName();
		$users=new usersMenus();
		$array["virus"]='{virus}';
		$array["categories"]='{categories}';
		$array["countries"]='{countries}';
	while (list ($num, $ligne) = each ($array) ){
			$tab[]="<li><a href=\"$page?$num=yes\"><span style='font-size:$font_size'>$ligne</span></a></li>\n";
		}
	$html="
		<div id='main_kav4proxy_stats' style='background-color:white;margin-top:10px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_kav4proxy_stats').tabs();
			

			});
		</script>
		";	
	
	echo $tpl->_ENGINE_parse_body($html);
}

function virus(){
		$tpl=new templates();
		$page=CurrentPageName();	
	
	
	$sql="SELECT * FROM kav4proxyVirus WHERE MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW()) ORDER BY days DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error\n</H2>";return;}
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src=img/icon-antivirus-64.png></td>
	<td><div class=explain>{kav4proxyvirus_month_stats_explain}</div></td>
	</tr>
	</table>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{days}</th>
		<th width=1%>{size}</th>
		<th width=1%>{hits}</th>
		<th width=1%>{category}</th>
		<th width=1% colspan=2>{website}</th>
		<th width=1%>{client}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ligne["size"]=FormatBytes($ligne["size"]/1024);
			$country=$ligne["country"];
			if($country<>null){$img_country=GetFlags($country);}else{$img_country="flags/name.png";}

			$html=$html."<tr class=$classtr>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["days"]}</strong></td>
			<td width=1% nowrap align=center><strong style='font-size:14px'>{$ligne["size"]}</strong></td>
			<td width=1% nowrap align=center><strong style='font-size:14px'>{$ligne["hits"]}</strong></td>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["category"]}</strong></td>
			<td width=1% nowrap  align=center><strong style='font-size:14px'>". imgtootltip($img_country,$country)."</strong></td>
			<td width=99% nowrap><strong style='font-size:14px'>{$ligne["sitename"]}</strong></td>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["client"]}</strong></td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
		
}

function categories_month(){
		$tpl=new templates();
		$page=CurrentPageName();	
	$sql="SELECT SUM(hits) as thits,category FROM kav4proxyDays WHERE MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW()) GROUP BY category ORDER BY thits DESC LIMIT 0,10";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error\n</H2>";return;}
	
$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src=img/64-categories.png></td>
	<td><div class=explain>{kav4proxycat_month_stats_explain}</div></td>
	</tr>
	</table>
	<center style='margin:5px'><img src='ressources/logs/web/kav4proxy.MCAT.png'  border=0></center>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{hits}</th>
		<th width=1%>{category}</th>
		
	</tr>
</thead>
<tbody class='tbody'>";	
	$gp->xdata[]="";
	$gp->ydata[]="";
@unlink(dirname(__FILE__)."/ressources/logs/web/kav4proxy.MCAT.png");	
$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/kav4proxy.MCAT.png",0);
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$gp->xdata[]=$ligne["thits"];
			
			$text_category=$ligne["category"];
			if($text_category==null){$text_category=$tpl->_ENGINE_parse_body("{not_categorized}");}
			$text_category=str_replace("forums,forums", "forums",$text_category);
			$gp->ydata[]=$text_category;
			
			$uri="<a href=\"javascript:blur()\" OnClick=\"YahooWin2('750','$page?category-details=yes&period=month&category={$ligne["category"]}','$text_category');\" style='text-decoration:underline'>";
			
			$html=$html."<tr class=$classtr>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["thits"]}</strong></td>
			<td width=99% nowrap align=left><strong style='font-size:14px'>$uri$text_category</a></strong></td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	";
	
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{categories}";
	$gp->pie();	

	echo $tpl->_ENGINE_parse_body($html);	
	
}
function countries_month(){
		$tpl=new templates();
		$page=CurrentPageName();	
	$sql="SELECT SUM(hits) as thits,country FROM kav4proxyDays WHERE MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW()) GROUP BY country ORDER BY thits DESC LIMIT 0,10";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error\n</H2>";return;}
	
$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src=img/domain-64.png></td>
	<td><div class=explain>{kav4proxycountry_month_stats_explain}</div></td>
	</tr>
	</table>
	<center style='margin:5px'><img src='ressources/logs/web/kav4proxy.MCOUNT.png'  border=0></center>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% colspan=2>{hits}</th>
		<th width=1%>{country}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	$gp->xdata[]="";
	$gp->ydata[]="";
@unlink(dirname(__FILE__)."/ressources/logs/web/kav4proxy.MCOUNT.png");	
$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/kav4proxy.MCOUNT.png",0);
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$gp->xdata[]=$ligne["thits"];
			
			$text_category=$ligne["country"];
			$country=$ligne["country"];
			if($country<>null){$img_country=GetFlags($country);}else{$img_country="flags/name.png";}
			if($text_category==null){$text_category=$tpl->_ENGINE_parse_body("{unknown}");}
			
			$gp->ydata[]=$text_category;
			$html=$html."<tr class=$classtr>
			<td width=1% nowrap><img src='img/$img_country'></td>
			<td width=1% nowrap><strong style='font-size:14px'>{$ligne["thits"]}</strong></td>
			<td width=99% nowrap align=left><strong style='font-size:14px'>$text_category</strong></td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	";
	
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{country}";
	$gp->pie();	

	echo $tpl->_ENGINE_parse_body($html);	
	
}
