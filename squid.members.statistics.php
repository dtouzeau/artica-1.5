<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["squid-general-status"])){general_status();exit;}
	if(isset($_GET["squid-status-stats"])){squid_status_stats();exit;}
	
	if(isset($_GET["squid-status-graphs"])){general_status_graphs();exit;}
	if(isset($_GET["squid-cache-flow-performance"])){general_status_cache_graphs();exit;}
	
	if(isset($_GET["squid-members-graphs"])){members_first_graph();exit;}
	if(isset($_GET["month-list-members"])){month_list_members();exit;}
	
	if(isset($_GET["day-consumption"])){day_section();exit;}
	if(isset($_GET["day-members"])){day_graphs();exit;}
	if(isset($_GET["day-left-menu"])){day_menu_left();exit;}
	if(isset($_GET['day-graphs-perform'])){day_graphs_perform();exit;}
	if(isset($_GET["day-users-search"])){day_user_search();exit;}
	if(isset($_GET["day-top-members"])){day_top_members();exit;}
	if(isset($_GET["day-top-member-site"])){day_top_member_sites();exit;}
	
	if(isset($_GET["website-details"])){website_details_js();exit;}
	if(isset($_GET["website-details-popup"])){website_details_popup();exit;}
	if(isset($_GET["family-site-details"])){website_details_familysite();exit;}
	
tabs();


function website_details_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title="{$_GET["day"]}:: {$_GET["user"]} -&raquo; {$_GET["familysite"]}";
	$html="YahooWin('650','$page?website-details-popup=yes&familysite={$_GET["familysite"]}&day={$_GET["day"]}&user-field={$_GET["user-field"]}&user={$_GET["user"]}','$title');";
	echo $html;
	}
	
function website_details_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	if(!isset($_GET["day"])){$_GET["day"]=$q->HIER();}
	if($_GET["day"]==null){$_GET["day"]=$q->HIER();}
	$dayfull="{$_GET["day"]} 00:00:00";
	$date=strtotime($dayfull);
	$tablesrc=date("Ymd",$date)."_hour";

	$sql="SELECT SUM(hits) as tcount,familysite,`{$_GET["user-field"]}`,`hour` FROM $tablesrc GROUP BY familysite,`{$_GET["user-field"]}` ,`hour`
	HAVING `{$_GET["user-field"]}`='{$_GET["user"]}' AND familysite='{$_GET["familysite"]}' ORDER BY `hour`";
	
	$mdF=md5("$tablesrc{$_GET["user-field"]}{$_GET["user"]}");
	
	
	$gp=new artica_graphs();		
	$dayText=date('l',$date);
	$title="{{$dayText}} {$_GET["familysite"]} member: {$_GET["user"]} {hits}";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
		
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$gp->xdata[]=$ligne["hour"];
		$gp->ydata[]=$ligne["tcount"];
	}
		
		
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".$mdF$tablesrc.png";
	$gp->width=550;
	$gp->height=250;
	$gp->filename="$targetedfile";
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{hours}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();	
		
if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);$targetedfile="img/kas-graph-no-datas.png";}

	$html="
	
	<center>
	<span style='font-size:16px'>$title</span>
		<img src='$targetedfile'>
		<div id='family-site-details'></div>
	</center>
	
	<script>
		LoadAjax('family-site-details','$page?family-site-details=yes&familysite={$_GET["familysite"]}&day={$_GET["day"]}&user-field={$_GET["user-field"]}&user={$_GET["user"]}');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);			
		
	
}	

function website_details_familysite(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	if(!isset($_GET["day"])){$_GET["day"]=$q->HIER();}
	if($_GET["day"]==null){$_GET["day"]=$q->HIER();}
	$dayfull="{$_GET["day"]} 00:00:00";
	$date=strtotime($dayfull);
	$tablesrc=date("Ymd",$date)."_hour";

	$sql="SELECT SUM(hits) as tcount,familysite,sitename,category,`{$_GET["user-field"]}` FROM $tablesrc GROUP BY familysite,`{$_GET["user-field"]}` 
	HAVING `{$_GET["user-field"]}`='{$_GET["user"]}' AND familysite='{$_GET["familysite"]}' ORDER BY SUM(hits) DESC";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}		
	
		$table="
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
			<tr>
				<th width=1%>{hits}</th>
				<th width=99%>{website}</th>
			</tr>
	</thead>
<tbody>";	
		
		
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}

			$js="Loadjs('$page?website-details=yes&familysite={$ligne["familysite"]}&day={$_GET["day"]}&user-field={$_GET["user-field"]}&user={$_GET["user"]}')";
			$categorize="Loadjs('squid.categorize.php?www={$ligne["sitename"]}')";
			if(trim($ligne["category"])==null){$ligne["category"]="<span style='color:#D70707'>{categorize_this_website}</span>";}			
			$table=$table."
				<tr class=$classtr style='height:auto'>
					<td width=1%  style='font-size:14px;'nowrap><strong>{$ligne["tcount"]}</strong></td>
					<td width=99%  style='font-size:14px;' nowrap><strong>
					<a href=\"javascript:blur();\" 
					OnClick=\"javascript:$js\" 
					style='font-size:14px;text-decoration:underline;font-weight:bold'>{$ligne["sitename"]}</a></strong>
					<div>
					<a href=\"javascript:blur()\" OnClick=\"javascript:$categorize\" style='font-size:14px;text-decoration:underline'><i>{$ligne["category"]}</i></a>
					</div>
					</td>
				</tr>
				";	

		}			
$table=$table."</tbody></table>";
echo $tpl->_ENGINE_parse_body($table);		
		
}


function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["day-consumption"]='{days}';
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_stats_consumption style='width:100%;font-size:14px'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_stats_consumption').tabs();
			
			
			});
		</script>");		
}

function day_section(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top' width=1%>
			<div id='squid-members-graphs-left-menu'></div>
			<div id='members-day-list' style='width:100%;height:225px;overflow:auto'></div>
			<div id='squid-general-status'></div>
		</td>
		<td valign='top' width=99%><div id='squid-members-days' style='text-align:center'></div></td>
	</tr>
	</tbody>
	</table>
	<script>
		LoadAjax('squid-members-days','$page?day-members=yes');
	</script>
	";
	
	echo $html;	
	
	
}

function day_top_member_sites(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	if(!isset($_GET["day"])){$_GET["day"]=$q->HIER();}
	if($_GET["day"]==null){$_GET["day"]=$q->HIER();}
	$dayfull="{$_GET["day"]} 00:00:00";
	$date=strtotime($dayfull);
	$tablesrc=date("Ymd",$date)."_hour";
	$sql="SELECT SUM(hits) as tcount,familysite,`{$_GET["user-field"]}` FROM $tablesrc GROUP BY familysite,`{$_GET["user-field"]}` 
	HAVING `{$_GET["user-field"]}`='{$_GET["user"]}' ORDER BY SUM(hits) DESC LIMIT 0,10";
	
	$mdF=md5("$tablesrc{$_GET["user-field"]}{$_GET["user"]}");
	
	$gp=new artica_graphs();
	$dayText=date('l',$date);
	if(!$q->TABLE_EXISTS($tablesrc)){return;}	
		$table="
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{hits}</th>
		<th width=99%>{websites}</th>
	</tr>
</thead>
<tbody>";	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".$mdF.png";
	$gp=new artica_graphs($targetedfile,0);	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}			
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}

			$js="Loadjs('$page?website-details=yes&familysite={$ligne["familysite"]}&day={$_GET["day"]}&user-field={$_GET["user-field"]}&user={$_GET["user"]}')";
			
			$table=$table."
				<tr class=$classtr style='height:auto'>
					<td width=1%  style='font-size:14px;'nowrap><strong>{$ligne["tcount"]}</strong></td>
					<td width=99%  style='font-size:14px;' nowrap><strong><a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-size:14px;text-decoration:underline;font-weight:bold'>{$ligne["familysite"]}</a></strong></td>
				</tr>
				";	
			$gp->xdata[]=$ligne["tcount"];
			$gp->ydata[]=$ligne["familysite"];
		}	
	

	$gp->width=560;
	$gp->height=350;
	$gp->ViewValues=false;
	$gp->x_title="{hits}";
	$gp->pie();			
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);$targetedfile="img/kas-graph-no-datas.png";}
	echo $tpl->_ENGINE_parse_body("<div style='font-size:16px'>{$_GET["user"]}: {{$dayText}} TOP {websites} ({hits})</div>
	<center>
	<img src='$targetedfile'>
	</center>
	</div>
	$table</tbody></table>
	
	");			
}

function day_top_members(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	$search=trim($_GET["search"]);
	if(!isset($_GET["day"])){$_GET["day"]=$q->HIER();}
	if($_GET["day"]==null){$_GET["day"]=$q->HIER();}
	$dayfull="{$_GET["day"]} 00:00:00";
	$date=strtotime($dayfull);
	$tablesrc=date("Ymd",$date)."_members";
	$sql="SELECT SUM(hits) as tcount,client,uid,MAC FROM $tablesrc GROUP BY client,uid,MAC ORDER BY SUM(hits) DESC LIMIT 0,10";
	$dayText=date('l',$date);
	if(!$q->TABLE_EXISTS($tablesrc)){return;}
	
		$table="
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th width=1%>{hits}</th>
		<th width=99%>{members}</th>
	</tr>
</thead>
<tbody>";	
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".$mdF$tablesrc.png";
	$gp=new artica_graphs($targetedfile,0);	
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			
			$ipaddr=$ligne["client"];
			$ipaddrlink=
			"<a href=\"javascript:blur();\" 
				OnClick=\"javascript:LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes&day={$_GET["day"]}&user-field=client&user={$ligne["client"]}');\" style='text-decoration:underline'>{$ligne["client"]}</a>";
			
			$uid=trim($ligne["uid"]);
			if($uid=='-'){$uid=null;}
			if($uid==null){
				$uid="<a href=\"javascript:blur();\" 
				OnClick=\"javascript:LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes&day={$_GET["day"]}&user-field=client&user=$ipaddr');\" style='text-decoration:underline'>$ipaddr</a>";
			}else{
				$uid="<a href=\"javascript:blur();\" 
				OnClick=\"javascript:LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes&day={$_GET["day"]}&user-field=uid&user=$uid');\" style='text-decoration:underline'>$uid</a>";
				
			}
			$mac=trim($ligne["MAC"]);
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
			$table=$table.
				"
				<tr class=$classtr style='height:auto'>
					<td width=1%  style='font-size:14px;' nowrap><img src='img/user-single-18.gif'></td>
					<td width=1%  style='font-size:14px;'nowrap><strong>{$ligne["tcount"]}</strong></td>
					<td width=99%  style='font-size:14px;' nowrap><strong>$uid ($ipaddrlink)<div>$mac</div></strong></td>
				</tr>
				";	
			
			
			
				if(trim($ligne["uid"])=='-'){$ligne["uid"]=null;}
				$gp->xdata[]=$ligne["tcount"];
				$gp->ydata[]=$ligne["uid"]."-".$ligne["client"];
		}	
	

	$gp->width=560;
	$gp->height=350;
	$gp->ViewValues=false;
	$gp->x_title="{hits}";
	$gp->pie();			
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);$targetedfile="img/kas-graph-no-datas.png";}
	echo $tpl->_ENGINE_parse_body("<div style='font-size:16px'>{{$dayText}} TOP {members} ({hits})</div>
	<center>
	<img src='$targetedfile'>
	</center>
	</div>
	$table</tbody></table>
	
	");	
	
}



function day_menu_left(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	
	
	
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tdate FROM tables_day ORDER BY zDate LIMIT 0,1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	$mindate=$ligne["tdate"];

	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tdate FROM tables_day ORDER BY zDate DESC LIMIT 0,1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	$maxdate=date('Y-m-d');
	
	$html="
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend nowrap>{from_date}:</td>
		<td>". field_date('SdateMember',$_GET["day"],"font-size:16px;padding:3px;width:95px","mindate:$mindate;maxdate:$maxdate")."</td>	
		<td>". button("{go}","DayMemberChangeDate()")."</td>
	</tr>
	</tbody>
	</table>
	<table style='width:100%;' class=form>
	<tbody>
	<tr>
		<td class=legend>{members}:</td>
		<td>". Field_text("day-members-query",null,"font-size:14px",$_COOKIE["SquidStatsSearchUsersDay"],null,null,false,"SquidStatsSearchUsersDayCheck(event)")."</td>
	</tr>
	</tbody>
	</table>
	
	
	
	<script>
		function DayMemberChangeDate(){
			LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes&day='+document.getElementById('SdateMember').value);
		
		}
		
		function SquidStatsSearchUsersDayCheck(e){
			if(checkEnter(e)){SquidStatsSearchUsersDay();}
		}
		
		function SquidStatsSearchUsersDay(){
			var se=document.getElementById('day-members-query').value;
			if(se.length>0){Set_Cookie('SquidStatsSearchUsersDay', se, '3600', '/', '', '');}else{
				se=Get_Cookie('SquidStatsSearchUsersDay');
				document.getElementById('day-members-query').value=se;
			}
			var search=escape(se);
			LoadAjax('members-day-list','$page?day-users-search=yes&day='+document.getElementById('SdateMember').value+'&search='+search);
		
		}
		
		
		SquidStatsSearchUsersDay();
	</script>
	
	";

	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function day_graphs(){
	$page=CurrentPageName();
	$tpl=new templates();		
	echo "<div id='day_graphs_for_members'></div>
	<script>
		LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes');
	</script>
	
	";
}

function day_user_search(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	$search=trim($_GET["search"]);
	if(!isset($_GET["day"])){$_GET["day"]=$q->HIER();}
	if($_GET["day"]==null){$_GET["day"]=$q->HIER();}
	$dayfull="{$_GET["day"]} 00:00:00";
	$date=strtotime($dayfull);
	$tablesrc=date("Ymd",$date)."_members";
	if($search<>null){
		$search="*$search*";
		$search=str_replace("**", "%", $search);
		$search=str_replace("**", "%", $search);
		$search=str_replace("*", "%", $search);
	}else{
		$search="%";
	}
	
	if(!$q->TABLE_EXISTS($tablesrc)){return;}
	$sql="SELECT client,uid,MAC FROM `$tablesrc` GROUP BY client,uid,MAC 
	HAVING ( (client LIKE '$search') OR (uid LIKE '$search') OR (MAC LIKE '$search') ) ORDER BY uid,client,MAC 
	LIMIT 0,25";
	$gp=new artica_graphs();		
	$dayText=date('l',$date);
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
	
		$table="
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th width=99%>{members}</th>
	</tr>
</thead>
<tbody>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			
			$ipaddr=$ligne["client"];
			$ipaddrlink=
			"<a href=\"javascript:blur();\" 
				OnClick=\"javascript:LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes&day={$_GET["day"]}&user-field=client&user={$ligne["client"]}');\" style='text-decoration:underline'>{$ligne["client"]}</a>";
			
			$uid=trim($ligne["uid"]);
			if($uid=='-'){$uid=null;}
			if($uid==null){
				$uid="<a href=\"javascript:blur();\" 
				OnClick=\"javascript:LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes&day={$_GET["day"]}&user-field=client&user=$ipaddr');\" style='text-decoration:underline'>$ipaddr</a>";
			}else{
				$uid="<a href=\"javascript:blur();\" 
				OnClick=\"javascript:LoadAjax('day_graphs_for_members','$page?day-graphs-perform=yes&day={$_GET["day"]}&user-field=uid&user=$uid');\" style='text-decoration:underline'>$uid</a>";
				
			}
			$mac=trim($ligne["MAC"]);
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
			$table=$table.
				"
				<tr class=$classtr style='height:auto'>
					<td width=1%  style='font-size:14px;height:auto'nowrap><img src='img/user-single-18.gif'></td>
					<td width=99%  style='font-size:12px;height:auto' nowrap><strong>$uid ($ipaddrlink)<div>$mac</div></strong></td>
				</tr>
				";		
				
			}	
		$table=$table."</tbody></table>";
	echo $tpl->_ENGINE_parse_body("$table");
}



function day_graphs_perform(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	$title="{number_of_users}";
	if(!isset($_GET["day"])){$_GET["day"]=$q->HIER();}
	if($_GET["day"]==null){$_GET["day"]=$q->HIER();}
	$dayfull="{$_GET["day"]} 00:00:00";
	$date=strtotime($dayfull);
	$script="<div id='top-members-day'></div><script>LoadAjax('top-members-day','$page?day-top-members=yes&day={$_GET["day"]}');</script>";
	$tablesrc=date("Ymd",$date)."_members";
	if($q->TABLE_EXISTS($tablesrc)){
		$sql="SELECT COUNT(zMD5) as tcount, `hour` FROM `$tablesrc` GROUP BY  `hour` ORDER BY `hour`";
		
		if(isset($_GET["user-field"])){
			$script="<div id='top-members-day'></div><script>LoadAjax('top-members-day','$page?day-top-member-site=yes&day={$_GET["day"]}&user-field={$_GET["user-field"]}&user={$_GET["user"]}');</script>";
			$sql="SELECT SUM(hits) as tcount, `hour`,`{$_GET["user-field"]}` FROM `$tablesrc` GROUP BY  `{$_GET["user-field"]}`,`hour` 
			HAVING `{$_GET["user-field"]}`='{$_GET["user"]}'
			ORDER BY `hour`";
			$title="{hits} {member}: {$_GET["user"]}";
			$mdF=".".md5("{$_GET["user-field"]}:{$_GET["user"]}").".hits.";
		}
		
		$gp=new artica_graphs();		
		$dayText=date('l',$date);
		$results=$q->QUERY_SQL($sql);
		if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
				$gp->xdata[]=$ligne["hour"];
				$gp->ydata[]=$ligne["tcount"];
		}
		
		
		$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".$mdF$tablesrc.png";
		$gp->width=550;
		$gp->height=350;
		$gp->filename="$targetedfile";
		$gp->y_title=null;
		$gp->x_title=$tpl->_ENGINE_parse_body("{hours}");
		$gp->title=null;
		$gp->margin0=true;
		$gp->Fillcolor="blue@0.9";
		$gp->color="146497";
		$gp->line_green();
	}
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);$targetedfile="img/kas-graph-no-datas.png";}

	$html="
	<div style='font-size:16px'>$title {{$dayText}} {$_GET["day"]}</div>
	<center>
		<img src='$targetedfile'>
	</center>
	<script>LoadAjax('squid-members-graphs-left-menu','$page?day-left-menu=yes&day={$_GET["day"]}');</script>
	$script";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<table style='width:100%'>
	<tbody>
	<tr>
	<td valign='top' width=1%>
			<div id='squid-general-status'></div>
		</td>
		<td valign='top' width=99%><div id='squid-members-graphs' style='text-align:center'></div></td>
	</tr>
	</tbody>
	</table>
	<script>
		LoadAjax('squid-general-status','$page?squid-general-status=yes');
	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}

function month_list_members(){
	$q=new mysql_squid_builder();
	$page=CurrentPageName();
	$array=$q->LIST_TABLES_MEMBERS();
	while (list ($num, $ligne) = each ($array) ){
		$ligne=trim($ligne);
		if(!preg_match("#([0-9]+)_members#", $ligne,$re)){continue;}
		$len=strlen($re[1]);
		if($len>6){continue;}
		$year=substr($re[1], 0,4);
		$month=substr($re[1], 4,2);
		if($year<>date('Y')){continue;}
		$uri="<a href=\"javascript:blur()\" OnClick=\"javascript:LoadAjax('squid-members-graphs','$page?squid-members-graphs=yes&month=$year-$month');\" style='font-size:14px;text-decoration:underline;font-weight:bold'>";
		
		
		$tr=$tr."<td style='font-size:14px'>$uri$month</a></td>";
		
		
	}
	
	$html="
<center>
		<table class=form>
		<tbody>
			<tr>$tr</tr>
		</tbody>
	</table>
</center>";
	echo $html;
}

function general_status(){
	$page=CurrentPageName();
	$tpl=new templates();		

	$stylehref="style='font-size:14px;font-weight:bold;text-decoration:underline'";
	$img="img/server-256.png";
	$html="
	<div class=form>
	<center style='margin:5px'>
	<img src='$img'>
	</center>
	<div id='squid-status-stats'></div>
	
	<p>&nbsp;</p>
	<script>
		LoadAjax('squid-status-stats','squid.traffic.statistics.php?squid-status-stats=yes&notypes=yes');	
		LoadAjax('squid-members-graphs','$page?squid-members-graphs=yes');
	</script>
	</div>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function members_first_graph(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	$gp=new artica_graphs();
	if(!isset($_GET["month"])){
		$currentmonth=date('Y-m');
		$currentmonthText=date('F');
		$tablesrc=date("Ym")."_members";
	}else{
		$date=strtotime($_GET["month"]."-". date('d'). "00:00:00");
		$currentmonth=$_GET["month"];
		$currentmonthText=date('F',$date);
		$tablesrc=date('Ym',$date)."_members";
	}
	
	if(!$q->TABLE_EXISTS($tablesrc)){$q->CreateMembersDayTable();}
	
	if(!$q->TABLE_EXISTS($tablesrc,true)){
		echo $tpl->_ENGINE_parse_body("<center style='margin:50px'><H2>{error_no_datas}</H2></center><script>LoadAjax('month-list-members','$page?month-list-members=yes');</script>");
		return;
	}
	
	
	$sql="SELECT COUNT(zMD5) as tcount,`day` FROM `$tablesrc` GROUP BY `day` ORDER BY `day`";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}
	$table="
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:10%'>
<thead class='thead'>
	<tr>
	<th width=1%>{days}</th>
	<th>{members}</th>
	</tr>
</thead>
<tbody>";	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$table=$table."
			<tr class=$classtr>
				<td width=1%  style='font-size:14px' nowrap align=center><strong>{$ligne["day"]}</strong></td>
				<td  style='font-size:14px' nowrap width=99% align=center><strong>{$ligne["tcount"]}</td>
			</tr>
			";		
			
			$gp->xdata[]=$ligne["day"];
			$gp->ydata[]=$ligne["tcount"];
	}
	
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".$tablesrc.png";
	$gp->width=550;
	$gp->height=350;
	$gp->filename="$targetedfile";
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{days}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);$targetedfile="img/kas-graph-no-datas.png";}

	$html="
	<center style='font-size:14px;font-weight:bold'>{months}</center>
	<div id='month-list-members'></div>
	<div style='font-size:16px'>{number_of_users} {{$currentmonthText}} $currentmonth</div>
	<center>
		<img src='$targetedfile'>
	</center>
	$table</tbody></table>
	</center>

	
	<script>
		LoadAjax('month-list-members','$page?month-list-members=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
		
}



function squid_status_stats(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	
	$websitesnums=$q->COUNT_ROWS("visited_sites");
	$websitesnums=numberFormat($websitesnums,0,""," ");	
	
	
	$categories=$q->COUNT_CATEGORIES();
	$categories=numberFormat($categories,0,""," ");

	$q=new mysql_squid_builder();
	$requests=$q->EVENTS_SUM();
	$requests=numberFormat($requests,0,""," ");	
	
	$DAYSNumbers=$q->COUNT_ROWS("tables_day");
	
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT SUM(totalsize) as tsize FROM tables_day"));
	$totalsize=FormatBytes($ligne["tsize"]/1024);
	
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT AVG(cache_perfs) as pourc FROM tables_day"));
	$pref=round($ligne["pourc"]);	

$html="
<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top' style='font-size:14px'><b>$DAYSNumbers</b> {daysOfStatistics}</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><b>$requests</b> {requests}</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><b>$websitesnums</b> {visited_websites}</td>
	</tr>		
	<tr>
		<td valign='top' style='font-size:14px'><b>$categories</b> {websites_categorized}</td>
	</tr>			
	<tr>
		<td valign='top' style='font-size:14px'><b>$totalsize</b> {downloaded_flow}</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><b>$pref%</b> {cache_performance}</td>
	</tr>	
	</tbody>
	</table>";

echo $tpl->_ENGINE_parse_body($html);
	
}




