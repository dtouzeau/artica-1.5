<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.mysql.inc');
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["browse-domains-list"])){browse_domains_list();exit;}
js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->javascript_parse_text("{browse} {domains}");
	$html="YahooSetupControl('650','$page?popup=yes&field={$_GET["field"]}','$title');";
	echo $html;
	
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();

	
	$html="<center>
	<table style='width:70%' class=form>
	<tr>
		<td class=legend>{domains}:</td>
		<td>". Field_text("browse-domains-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseDomainsSearchCheck(event)")."</td>
		<td>". button("{search}","BrowseDomainsSearch()")."</td>
	</tr>
	</table>
	</center>
	<div id='browse-domains-list' style='width:100%;height:450px;overflow:auto'></div>
<script>
		function BrowseDomainsSearchCheck(e){
			if(checkEnter(e)){BrowseDomainsSearch();}
		}
		
		function BrowseDomainsSearch(){
			var se=escape(document.getElementById('browse-domains-search').value);
			LoadAjax('browse-domains-list','$page?browse-domains-list=yes&search='+se+'&field={$_GET["field"]}');
		}
			
	BrowseDomainsSearch();
</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function browse_domains_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ldap=new clladp();
	$users=new usersMenus();
	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("***","*",$search);
	$search=str_replace("**","*",$search);
	$search_sql=str_replace("*","%",$search);
	$search_sql=str_replace("%%","%",$search_sql);
	$search_regex=str_replace(".","\.",$search);	
	$search_regex=str_replace("*",".*?",$search);
	

	if($users->AsSystemAdministrator){
		$q=new mysql();
		$sql="SELECT domain FROM officials_domains WHERE domain LIKE '$search_sql' ORDER BY domain";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$domains[$ligne["domain"]]=$ligne["domain"];}	
		$hash=$ldap->hash_get_all_domains();
		while (list ($num, $ligne) = each ($hash) ){if(preg_match("#$search_regex#", $ligne)){$domains[$ligne]=$ligne;}}	
		
	}else{
		$hash=$ldap->hash_get_domains_ou($_SESSION["ou"]);
		while (list ($num, $ligne) = each ($hash) ){if(preg_match("#$search_regex#", $ligne)){$domains[$ligne]=$ligne;}}
	}
	
	ksort($domains);
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=2>{domains}&nbsp;|&nbsp;$search_regex&nbsp;|&nbsp;$search_sql</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($num, $ligne) = each ($domains) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("plus-24.png","{select}","SelectBrowseDomains('$ligne')");
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/domain-32.png'></td>
			<td style='font-size:14px;font-weight:bold'>{$ligne}</td>
			<td width=1%>$select</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	<script>
		
		function SelectBrowseDomains(domain){
			document.getElementById('{$_GET["field"]}').value=domain
			YahooSetupControlHide();
		}

	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}