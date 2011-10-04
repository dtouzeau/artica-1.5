<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	if(isset($_GET["popup"])){popup();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();
	$export=$q->COUNT_ROWS("categorize");
	$title=$tpl->_ENGINE_parse_body("$export {websites_to_export}");
	$html="YahooWin('650','$page?popup=yes','$title')";
	echo $html;
	
	
}
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql_squid_builder();	
	$html="<div class=explain>{websites_to_export_explain}</div>
	<div style='margin:top:10px;width:100%;height:450px;overflow:auto'>
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
			<tr>
				<th width=1%>{date}</th>
				<th width=99%>{website}</th>
				<th width=99%>{category}</th>
			</tr>
	</thead>
<tbody>";	
	
	$sql="SELECT * FROM categorize ORDER BY zDate DESC LIMIT 0,150";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}			
		
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}

			$js="Loadjs('$page?website-details=yes&familysite={$ligne["familysite"]}&day={$_GET["day"]}&user-field={$_GET["user-field"]}&user={$_GET["user"]}')";
			$categorize="Loadjs('squid.categorize.php?www={$ligne["sitename"]}')";
			if(trim($ligne["category"])==null){$ligne["category"]="<span style='color:#D70707'>{categorize_this_website}</span>";}			
			$html=$html."
				<tr class=$classtr>
					<td width=1%  style='font-size:14px;'nowrap><strong>{$ligne["zDate"]}</strong></td>
					<td width=99%  style='font-size:14px;' nowrap><strong>{$ligne["pattern"]}</strong></td>
					<td width=1%  style='font-size:14px;' nowrap><strong>{$ligne["category"]}</strong></td>
				</tr>
				";	

		}			
$html=$html."</tbody></table></div>";
echo $tpl->_ENGINE_parse_body($html);		
	
	
	
}	
	



