<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.iptables-chains.inc');
	
	
	$usersmenus=new usersMenus();
	if($usersmenus->AsSystemAdministrator==false){exit();}	
	
	if(isset($_GET["iptables_rules"])){firewall_rules();exit();}
	if(isset($_GET["edit_rule"])){firewall_rule_form();exit;}
	
	if(isset($_POST["source_address"])){firewall_rule_save();exit;}
	if(isset($_POST["DeleteIptableRule"])){firewall_rule_delete();exit;}
	if(isset($_POST["EnableFwRule"])){firewall_rule_enable();exit;}
	if(isset($_POST["EnableLog"])){firewall_rule_log();exit;}
	
	
	
	firewall_popup();
	

function firewall_popup(){
	unset($_SESSION["postfix_firewall_rules"]);
	$users=new usersMenus();
	$tpl=new templates();
	$page=CurrentPageName();
	$rule=$tpl->_ENGINE_parse_body("{rule}");
	if(!$users->AsSystemAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	
	$refresh="<div style='text-align:right;margin-bottom:5px'>". imgtootltip("refresh-32.png","{refresh}","IptablesSearch()")."</div>";


	

	
	
$html="
<center>
<table style='width:50%' class=form>
<tr>
	<td class=legend nowrap>{rules}:</td>
	<td>" . Field_text('search_fw',null,"width:190px;font-size:13px;padding:3px",null,null,null,null,"IptablesSearchKey(event)")."</td>
	<td width=1%>". button("{search}","IptablesSearch()")."</td>
	<td width=1%>$refresh</td>
</tR>
</table>
</center>
<br>
<div id='iptables_rules' style='width:100%;height:420px;overflow:auto'></div>
<script>
	function IptablesSearchKey(e){
			if(checkEnter(e)){
				IptablesSearch();
			}
	}
	
	function IptablesSearch(){
		var pattern=escape(document.getElementById('search_fw').value);
		LoadAjax('iptables_rules','$page?iptables_rules=yes&search='+pattern);
		}
		
	function iptables_edit_rules(num){
		YahooWin5('460','$page?edit_rule=yes&rulemd5='+num,'$rule');
	
	}
	
	IptablesSearch();
</script>

";

echo $tpl->_ENGINE_parse_body($html);

}


function firewall_rule_form(){
	$q=new mysql();
	$tpl=new templates();
	$page=CurrentPageName();	
	$rulemd5=$_GET["rulemd5"];
	$button="{apply}";
	$sql="SELECT * FROM iptables WHERE rulemd5='$rulemd5'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(strlen($rulemd5)<5){$button="{add}";}
	$html="
	<table class=form style='width:100%'>
	<tr>
		<td class=legend>{source_address}:</td>
		<td>". field_ipv4("serverip",$ligne["serverip"],"font-size:14px;padding:3px")."</td>
		<td>". help_icon("{fw_sourceaddr_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{multiples_ports}:</td>
		<td>". Field_text("multiples_ports",$ligne["multiples_ports"],"font-size:14px;padding:3px",null,null,null,false,"SaveIptableRuleCheck(event)")."</td>
		<td>". help_icon("{fw_multiples_ports_explain}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'>". button("$button","SaveIptableRule()")."</td>
	</tr>
	</table>
	
	<script>
	
	var x_SaveIptableRule= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		YahooWin5Hide();
		IptablesSearch();
	}		

	function SaveIptableRuleCheck(e){
		if(checkEnter(e)){SaveIptableRule();}
	}
	
		
	function SaveIptableRule(){
		var XHR = new XHRConnection();
		XHR.appendData('source_address',document.getElementById('serverip').value);
		XHR.appendData('multiples_ports',document.getElementById('multiples_ports').value);
		XHR.appendData('rulemd5','$rulemd5');
		AnimateDiv('iptables_rules');
		XHR.sendAndLoad('$page', 'POST',x_SaveIptableRule);		
		}
		
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function firewall_rule_save(){
	$tpl=new templates();
	$iptables=new iptables_chains();
	$iptables->localport=$_POST["multiples_ports"];
	$iptables->serverip=$_POST["source_address"];
	$iptables->rulemd5=$_POST["rulemd5"];
	if(!$iptables->add_chain()){echo $tpl->javascript_parse_text("\n{failed}\n");return;}
	$sock=new sockets();
	$sock->getFrameWork("network.php?fw-inbound-rules=yes");	
	
}

function firewall_rule_delete(){
	$q=new mysql();
	$q->QUERY_SQL("DELETE FROM iptables WHERE rulemd5='{$_POST["DeleteIptableRule"]}'","artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("network.php?fw-inbound-rules=yes");	
	
}



function firewall_rules(){
	$q=new mysql();
	$page=CurrentPageName();
	
	
	if($_GET["search"]<>null){
		$_GET["search"]=$_GET["search"]."*";
		$_GET["search"]=str_replace("**","*",$_GET["search"]);
		$_GET["search"]=str_replace("*","%",$_GET["search"]);
		if(preg_match("#([a-zA-Z]+)#",$_GET["search"])){
			$sql_search="AND servername LIKE '{$_GET["search"]}' ";
		}else{
			$sql_search="AND serverip LIKE '{$_GET["search"]}' ";
		}
	}
	
	$sql_count="SELECT COUNT(*) AS tcount FROM iptables WHERE flux='INPUT'{$sql_search}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql_count,"artica_backup"));
	$max=$ligne["tcount"];
	$limit=0;	
	
	
	$sql="SELECT * FROM iptables WHERE flux='INPUT' {$sql_search}ORDER BY ID DESC LIMIT $limit,200";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$add=imgtootltip("plus-24.png","{add}","iptables_edit_rules(0)");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{server}</th>
		<th>{port}</th>
		<th>{enable}</th>
		<th>{log}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["servername"]==null){$ligne["servername"]=$ligne["serverip"];}
		$link="iptables_edit_rules('{$ligne["rulemd5"]}');";
		$disable=Field_checkbox("enabled_{$ligne["ID"]}",0,$ligne["disable"],"FirewallDisableRUle('{$ligne["ID"]}')");
		$log=Field_checkbox("log_{$ligne["ID"]}",1,$ligne["log"],"EnableLog('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","IptableDelete('{$ligne["rulemd5"]}')");
		$ligne["events_block"]="<div style=font-size:13px>".nl2br($ligne["events_block"])."</div>";
		$icon="datasource-32.png";
		if($ligne["community"]==1){
			$icon="connect-32-grey.png";
			$delete="<img src='img/delete-32-grey.png'>";
			$tooltip_add="<strong style=font-size:13px>{updated_from_community}</strong><br>";
			$link=null;
		}
		
		if($ligne["service"]==null){$icon="connect-32-grey.png";$link=null;}
		
		$subtext="<div style='font-size:10px'><i><span style='color:#660002;font-weight:bold'>{$ligne["serverip"]}</span> {added_on} {$ligne["saved_date"]}</i></div>";
		$port=$ligne["local_port"];
		if($ligne["multiples_ports"]<>null){$port=str_replace(",", "<br>", $ligne["multiples_ports"]);}
		if($port==0){$port="{all}";}
		$html=$html . "
		<tr  class=$classtr>
		<td width=1%>".imgtootltip($icon,"{$ligne["serverip"]}/$port",$link)."</td>
		<td><strong style='font-size:14px'><code>". texttooltip("{$ligne["servername"]}","$tooltip_add{$ligne["serverip"]}<hr>{$ligne["events_block"]}",null,null,0,"font-size:13px")."</strong></code>$subtext</td>
		<td width=1%><strong style='font-size:14px'>$port</strong></td>
		<td width=1%>$disable</td>
		<td width=1%>$log</td>
		<td width=1%>$delete</td>
		</td>
		</tr>";
		
	}
	$html=$html."</tbody>
	
	</table>
	
	<script>
	var x_IptableDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		IptablesSearch();
	}	
	
	function IptableDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteIptableRule',key);
		XHR.sendAndLoad('$page', 'POST',x_IptableDelete);
		}
		
	var x_FirewallDisableRUle= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
	}		
		

	function FirewallDisableRUle(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID);
		if(document.getElementById('enabled_'+ID).checked){XHR.appendData('EnableFwRule',0);}else{XHR.appendData('EnableFwRule',1);}
		XHR.sendAndLoad('$page', 'POST',x_FirewallDisableRUle);
	}

	function EnableLog(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID);
		if(document.getElementById('enabled_'+ID).checked){XHR.appendData('EnableLog',1);}else{XHR.appendData('EnableLog',0);}
		XHR.sendAndLoad('$page', 'POST',x_FirewallDisableRUle);	
	
	}

	</script>
";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function firewall_rule_enable(){
	$q=new mysql();
	$sql="UPDATE iptables SET disable='{$_POST["EnableFwRule"]}' WHERE ID='{$_POST["ID"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("network.php?fw-inbound-rules=yes");	
}
function firewall_rule_log(){
	$q=new mysql();
	
	$sql="SELECT log FROM iptables WHERE ID='{$_POST["ID"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	if($ligne["log"]==1){$_POST["EnableLog"]=0;}else{$_POST["EnableLog"]=1;}
	$sql="UPDATE iptables SET log='{$_POST["EnableLog"]}' WHERE ID='{$_POST["ID"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("network.php?fw-inbound-rules=yes");	
}