<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.tcpip.inc');

$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}

if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["browse-rules-list"])){rules_list();exit;}

if(isset($_GET["rule-id-js"])){rule_id_js();exit;}
if(isset($_GET["rule-id"])){rule_form();exit;}
if(isset($_POST["rule-id"])){rule_save();exit;}
if(isset($_POST["delete-id"])){rule_delete();exit;}
js();


function rule_id_js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$id=$_GET["rule-id-js"];
	$title="{rule}:&nbsp;&raquo;&nbsp;$id&nbsp;&raquo;&nbsp;{add}";
		
		if($id>0){
			$q=new mysql();
			$sql="SELECT rulename,ipaddr FROM tcp_account_rules WHERE ID='$id'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
			$title="{rule}:&nbsp;&raquo;&nbsp;$id&nbsp;&raquo;&nbsp;{$ligne["rulename"]} ({$ligne["ipaddr"]})";
			}
	$title=$tpl->javascript_parse_text($title);
	$html="YahooWin3('445','$page?rule-id=$id','$title');";
	echo $html;
			
	
}

function rule_form(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$id=$_GET["rule-id"];
	$button="{add}";
	if($id>0){
		$button="{apply}";
		$q=new mysql();
		$sql="SELECT * FROM tcp_account_rules WHERE ID='$id'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(preg_match("#([0-9\.]+)\/([0-9]+)#", $ligne["ipaddr"],$re)){
			$ip=new ipv4($re[1], $re[2]);
			$ipaddr=$re[1];
			$netmask=$ip->netmask();
		}else{
			$ipaddr=$ligne["ipaddr"];
		}
	
	}
	
	
	$arrayMODE["INPUT"]="INPUT";
	$arrayMODE["OUTPUT"]="OUTPUT";
	$arrayMODE["FORWARD"]="FORWARD";
	$time=time();
	$q=new mysql();
	$sql="SELECT * FROM tcp_account_rules WHERE ID='$id'";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	$html="
	<span id='iptrleid'></span>
	<div class=explain >{ipatccount_rule_explain}</div>
	<table style='width:100%' class=form>
	<tr>
	<td class=legend>{rule_name}:</td>
	<td>". Field_text("ipt_rname",$ligne["rulename"],"font-size:14px;padding:3px'")."</td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td class=legend>{mode}:</td>
	<td>". Field_array_Hash($arrayMODE, "ipt_mode",$ligne["mode"],"style:font-size:14px;padding:3px")."</td>
	<td>&nbsp;</td>
	</tr>	
	
	<tr>
	<td class=legend>{ipaddr}:</td>
	<td>". field_ipv4("ipt_ipaddr",$ipaddr,"font-size:14px;padding:3px'")."</td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td class=legend>{mask}:</td>
	<td>". field_ipv4("ipt_netmask",$netmask,"font-size:14px;padding:3px'")."</td>
	<td>&nbsp;</td>
	</tr>		
	<tr>
		<td colspan=3 align='right'><hr>". button("$button", "SaveiptAccountRule()")."</td>
	</tr>
	
	</table>
	
	<script>
		var x_SaveiptAccountRule=function (obj) {
			var results=obj.responseText;
			if(results.length>2){
				alert(results);
				document.getElementById('iptrleid').innerHTML='';
				return;

			}			
			YahooWin3Hide();
			BrowseRulesSearch();
		}
	
	
		function SaveiptAccountRule(){
			var XHR = new XHRConnection();
			XHR.appendData('rulename',document.getElementById('ipt_rname').value);
			XHR.appendData('rule-id','$id');
			XHR.appendData('ipaddr',document.getElementById('ipt_ipaddr').value);
			XHR.appendData('mask',document.getElementById('ipt_netmask').value);
			XHR.appendData('mode',document.getElementById('ipt_mode').value);
			AnimateDiv('iptrleid');
    		XHR.sendAndLoad('$page', 'POST',x_SaveiptAccountRule);
			
		}

	</script>";
	echo $tpl->_ENGINE_parse_body($html);	
}

function rule_save(){
	
	$ipaddr=$_POST["ipaddr"];
	$mask=$_POST["mask"];
	$rulename=$_POST["rulename"];
	$rulename=replace_accents($rulename);
	$mode=$_POST["mode"];
	$id=$_POST["rule-id"];
	if(!is_numeric($id)){$id=0;}
	if($mask<>null){
		if($mask=="0.0.0.0"){$mask="0";}else{
			$ip=new IP();
			$ipaddr=$ip->maskTocdir($ipaddr, $mask);
		}
		
	}
	
	
	$sql="INSERT INTO tcp_account_rules (rulename,ipaddr,`mode`) VALUES('$rulename','$ipaddr','$mode');";
	if($id>0){$sql="UPDATE tcp_account_rules SET rulename='$rulename',ipaddr='$ipaddr',`mode`='$mode' WHERE ID=$id";}
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo $q->mysql_error;return;}
	
}

function rule_delete(){
	$id=$_POST["delete-id"];
	$sql="DELETE FROM tcp_account_rules WHERE ID='$id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
}


function js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?tabs=yes');";		
}

function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["popup"]='{rules}';
	$array["statistics"]='{statistics}';
	while (list ($num, $ligne) = each ($array) ){
		if($num=="statistics"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"iptaccount.stats.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_iptaccount style='width:100%;height:700px;overflow:auto'><ul>". implode("\n",$html)."</ul></div>
	<script>
	  $(document).ready(function() {
		$(\"#main_config_iptaccount\").tabs();});
	</script>";		
		
	
}	



function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<div class=explain>{APP_IPTACOUNT_EXPLAIN}</div>
		<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{rules}:</td>
				<td>". Field_text("browse-rules-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseRulesSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseRulesSearch()")."</td>
			</tr>
			</table>
		</center>	

<div id='browse-rules-list' style='width:100%;height:450px;overflow:auto;text-align:center'></div>		
		
		
		
<script>
		function BrowseRulesSearchCheck(e){
			if(checkEnter(e)){BrowseRulesSearch();}
		}
		
		function BrowseRulesSearch(){
			var se=escape(document.getElementById('browse-rules-search').value);
			LoadAjax('browse-rules-list','$page?browse-rules-list=yes&search='+se+'&field={$_GET["field"]}');
		}
		

			
	BrowseRulesSearch();
</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	//iptables -A OUTPUT -j ACCOUNT --addr 0.0.0.0/0 --tname all_outgoing
	// tcp_account_rules
}


function rules_list(){
	$sock=new sockets();
	$installed=trim($sock->getFrameWork("network.php?iptaccount-installed=yes"));
	$tpl=new templates();
	if($installed=="FALSE"){
		
		echo $tpl->_ENGINE_parse_body("<div style='font-size:16px;margin:10px'>{iptaccount_not_installed_explain}</div>");
		return;
	}
	
	$page=CurrentPageName();
	$tpl=new templates();
	$ldap=new clladp();
	$sock=new sockets();
	$users=new usersMenus();
	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("***","*",$search);
	$search=str_replace("**","*",$search);
	$search_sql=str_replace("*","%",$search);
	$search_sql=str_replace("%%","%",$search_sql);
	$search_regex=str_replace(".","\.",$search);	
	$search_regex=str_replace("*",".*?",$search);
	
	
	$add=imgtootltip("plus-24.png","{add}","RulesiptAccount('0')");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=4>{rules}&nbsp;|&nbsp;$search_regex&nbsp;|&nbsp;$search_sql</th>
	</tr>
</thead>
<tbody class='tbody'>";

		$q=new mysql();
		$sql="SELECT * FROM tcp_account_rules WHERE rulename LIKE '$search_sql' ORDER BY rulename";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","RulesiptAccount('{$ligne["ID"]}')");
		$select2=imgtootltip("folder-network-32.png","{edit}","RulesiptAccount('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","RulesiptAccountDel('{$ligne["ID"]}')");
		$color="black";
		$html=$html."
		<tr class=$classtr>
			<td width=1%>$select2</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["rulename"]} ({$ligne["ipaddr"]})</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["mode"]}</a></td>
			<td width=1%>$select</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table></center>
	<script>
	
		var x_RulesiptAccountDel=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			BrowseRulesSearch();
		}
	
	
		function RulesiptAccountDel(ID){
				var XHR = new XHRConnection();
				XHR.appendData('delete-id',ID);
				AnimateDiv('browse-rules-list');
    			XHR.sendAndLoad('$page', 'POST',x_RulesiptAccountDel);
			
		}
	
		
		function RulesiptAccount(ID){
			Loadjs('$page?rule-id-js='+ID);
		}

	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}
