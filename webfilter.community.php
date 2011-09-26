<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["categories"])){categories();exit;}
	if(isset($_GET["cat-search"])){categories_search();exit;}
	if(isset($_POST["delete-cat-pattern"])){pattern_delete();exit;}
js();


function status(){
	
	$q=new mysql_squid_builder();
	$page=CurrentPageName();
	$tpl=new templates();
	$total=FormatNumber($q->COUNT_ROWS("dansguardian_community_categories","artica_backup"),0,'.',' ',3);
	
	
	$sql="SELECT count(*) as tcount FROM `dansguardian_sitesinfos` WHERE `dbpath` = ''";	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	
	$pp=Paragraphe("64-categories.png",$ligne["tcount"]." {websites_not_categorized}",
	"{websites_not_categorized_text}","javascript:Loadjs('squid.visited.php')",null,300,76);	
	
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/webfilter-community-128.png'></td>
	<td valign='top' width=99%'>
		<div class=explain>{APP_WEBFILTER_COMMUNITY_EXPLAIN}</div>
		<div style='font-size:14px;font-weight:bold'>$total {websites} ({community})</div>
		$pp
	</td>
	</tr>
	</table>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function FormatNumber($number, $decimals = 0, $thousand_separator = '&nbsp;', $decimal_point = '.'){ 
	$tmp1 = round((float) $number, $decimals);
  while (($tmp2 = preg_replace('/(\d+)(\d\d\d)/', '\1 \2', $tmp1)) != $tmp1)
    $tmp1 = $tmp2;
  return strtr($tmp1, array(' ' => $thousand_separator, '.' => $decimal_point));
} 

function pattern_delete(){
	$sql="DELETE FROM dansguardian_community_categories WHERE pattern='{$_POST["pattern"]}' AND category='{$_POST["category"]}'";
	$q=new mysql_squid_builder();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function categories(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array_cats=categories_list();
	$html="
	<table style='width:100%'>
	<td class=legend>{category}:</td>
	<td>". Field_array_Hash($array_cats,"AR_CAT",null,"CatSearch()",null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<td class=legend>{search}:</td>
	<td>". Field_text("AR_CAT_SE",null,"font-size:13px;padding:3px;width:220px",null,null,null,false,"CatSearchCheck(event)")."</td>
	</tr>
	</table>
		
	<div style='margin:5px;padding:5px;height:350px;overflow:auto' id='AR_CAT_LIST'></div>
	
	<script>
		function CatSearch(){
			var cat=escape(document.getElementById('AR_CAT').value);
			var search=escape(document.getElementById('AR_CAT_SE').value);
			LoadAjax('AR_CAT_LIST','$page?cat-search=yes&category='+cat+'&search='+search);
		}
		
		function CatSearchCheck(e){
			if(checkEnter(e)){CatSearch();}
		}
		
		
	var x_DansGuardianCommunityDeletePattern= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				CatSearch();
			}
		
		function DansGuardianCommunityDeletePattern(category,pattern){
			var XHR = new XHRConnection();
			XHR.appendData('delete-cat-pattern','yes');
			XHR.appendData('category',category);
			XHR.appendData('pattern',pattern);
			AnimateDiv('AR_CAT_LIST');
			XHR.sendAndLoad('$page', 'POST',x_DansGuardianCommunityDeletePattern);
		
		}
		
		
	
	CatSearch();
	</script>";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function categories_list(){
	
	if(count($_SESSION["ARC-CAT-LIST"])>1){return $_SESSION["ARC-CAT-LIST"];}
	$sql="SELECT category,COUNT(pattern) as tcount FROM dansguardian_community_categories GROUP BY category";
	$q=new mysql_squid_builder();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo("$q->mysql_error");}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[$ligne["category"]]="{$ligne["category"]} ({$ligne["tcount"]} {items})";
		
	}
	$array[null]="{select}";
	$_SESSION["ARC-CAT-LIST"]=$array;
	return $_SESSION["ARC-CAT-LIST"];
}

function categories_search(){
	$q=new mysql_squid_builder();
	$page=CurrentPageName();
	$tpl=new templates();	
	$pattern=$_GET["search"];
	$category=$_GET["category"];
	
	if($pattern<>null){
		$pattern=str_replace("*","%",$pattern);
		$pattern=" AND pattern LIKE '$pattern'";
	}
	if($category<>null){
		$category=" AND category='$category'";
	}
	
	
	$sql="SELECT category,zDate,pattern FROM dansguardian_community_categories WHERE 1 $pattern $category ORDER BY zDate DESC LIMIT 0,150";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo("$q->mysql_error");}	

	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th >&nbsp;</th>
	<th colspan=3>{website}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			
			<td width=100%><strong style='font-size:14px'>{$ligne["pattern"]}</strong></td>
			<td width=1% nowrap style='font-size:14px'><div style='font-size:11px'><i>{date}:{$ligne["zDate"]}<br>{category}:{$ligne["category"]}</i></strong></td>
			<td width=1% nowrap style='font-size:14px'>". imgtootltip("delete-32.png","{delete}","DansGuardianCommunityDeletePattern('{$ligne["category"]}','{$ligne["pattern"]}')")."</td>
			</tr>
			";		
		
	}

	$html=$html."</tbody></table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function js(){
	$page=CurrentPageName();
	
	if(isset($_GET["in-front-ajax"])){
		echo "document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?popup=yes');";
		return;
	}

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{community}");
	echo "YahooWin2(650,'$page?popup=yes','$title');";
	
}
	
	
function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["status"]="{status}";
	$array["categories"]="{categories}";
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}

	
	echo "
	<div id='articacat_tabs' style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#articacat_tabs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
	
}	