<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.system.network.inc');
	
	
$users=new usersMenus();
if(!PostFixVerifyRights()){
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	echo "alert('$ERROR_NO_PRIVS');";
	die();
	
}

	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["form-rotator-mode"])){form_rotator_mode();exit;}
	if(isset($_GET["ip-rotator-list"])){rotator_list_rules();exit;}
	if(isset($_GET["ip-rotator-form"])){popup_form();exit;}
	if(isset($_GET["SaveRule"])){SaveRule();exit;}
	if(isset($_GET["DeleteRule"])){DeleteRule();exit;}
	if(isset($_GET["popup-rules"])){ListRules();exit;}
	js();

// iptables -t nat -A PREROUTING -p tcp -d 192.168.1.42 --dport 25 -m state --state NEW -m statistic --mode nth --every 2 --packet 0 -j DNAT --to-destination 192.168.1.43:25 

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	if(is_base64_encoded($_GET["ou"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
	$title="{ip_rotator}::{$_GET["hostname"]}/{$_GET["ou"]}";
	$title=$tpl->_ENGINE_parse_body($title);
	$_GET["ou"]=urlencode($_GET["ou"]);
	echo "YahooWin3(660,'$page?tabs=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$title');";
	}
	
function popup_form(){
	$ip=new networking();
	$ips=$ip->ALL_IPS_GET_ARRAY();
	$tpl=new templates();
	$page=CurrentPageName();
	$hostname=$_GET["hostname"];
	$ID=$_GET["ID"];	
	$button_text="{add}";
	if(!is_numeric($ID)){$ID=0;}
	if($ID>0){
		$sql="SELECT * FROM ip_rotator_smtp WHERE ID=$ID";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$button_text="{apply}";
	}
	
	while (list ($ip, $none) = each ($ips) ){
		$main=new maincf_multi(null,null,$ip);
		if($main->myhostname<>null){
			$ips[$ip]="$ip ($main->myhostname)";
		}
	}
	reset($ips);
	$ips[null]="{select}";
	$sources=Field_array_Hash($ips,"ip-src-list",$ligne["ipsource"],"FillIpSrc()",null,0,"font-size:14px;padding:3px");
	$sources2=Field_array_Hash($ips,"ip-dest-list",$ligne["ipdest"],"FillIpDest()",null,0,"font-size:14px;padding:3px");
	
	$mode[null]="{select}";
	$mode["nth"]="{counter}";
	$mode["random"]="{random}";
	
	$tip_src_table="<tr>
		<td class=legend>{connections_for}:</td>
		<td>$sources</td>
		<td>".Field_text("ip-src",$ligne["ipsource"],"font-size:14px;width:120px")."</td>
	</tr>";
	
	
	if($hostname<>"master"){
		$main=new maincf_multi($hostname,$_GET["ou"]);
		$tip_src_table="<tr>
			<td class=legend>{connections_for}:</td>
			<td style='font-size:14px'>$main->ip_addr <input type='hidden' value='$main->ip_addr' id='ip-src'></td>
			<td>&nbsp;</td>
		</tr>";		
		
		$sql="SELECT ipaddr FROM nics_virtuals WHERE org='{$_GET["ou"]}'";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		unset($ips);
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$main=new maincf_multi(null,$_GET["ou"],$ligne["ipaddr"]);
			$ips[$ligne["ipaddr"]]="{$ligne["ipaddr"]} ($main->myhostname)";
			
		}
		$sources2=Field_array_Hash($ips,"ip-dest-list",$ligne["ipdest"],"FillIpDest()",null,0,"font-size:14px;padding:3px");
		
	}
	
	
		
	
$html="	<table style='width:100%' class=form>
$tip_src_table
	<tr>
		<td class=legend>{switch_mode}:</td>
		<td>".Field_array_Hash($mode,"ip-mode",$ligne["mode"],"FillFormMode()",null,0,"font-size:14px;padding:3px")."</td>
		<td><span id='form-rotator-mode'></span></td>
	</tr>	
	<tr>
		<td class=legend>{destination}:</td>
		<td>$sources2</td>
		<td>".Field_text("ip-dest",$ligne["ipdest"],"font-size:14px;width:120px")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'>
			<hr>". button($button_text,"AddNewIprotator($ID)")."</td>
	</tr>
	
	</table>
	
	<script>
	function FillFormMode(){
		LoadAjax('form-rotator-mode','$page?form-rotator-mode='+document.getElementById('ip-mode').value+'&ID=$ID');
	
	}
	FillFormMode();
	</script>
	";	

echo $tpl->_ENGINE_parse_body($html);
	
}

function DeleteRule(){
	$ID=$_GET["ID"];
	if(!is_numeric($ID)){return;}
	$sql="DELETE FROM ip_rotator_smtp WHERE ID='$ID' AND `hostname`='{$_GET["hostname"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	//ip-rotator-48.png
}

function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["popup"]='{ip_rotator}';
	if($_GET["hostname"]=="master"){$array["popup-rules"]='{rules}';}
	$_GET["ou"]=urlencode($_GET["ou"]);
	
		
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_iprotator style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_iprotator').tabs();
				});
		</script>";		
}
	
function popup(){
	
	$page=CurrentPageName();
	$tpl=new templates();		

	
	$html="
	<div class=explain>{ip_rotator_smtp_explain}</div>
	<div id='form-rotator-display'></div>
	<p>&nbsp;</p>
	<div id='ip-rotator-list' style='width:100%;height:230px;overflow:auto'></div>
	


<script>
	function FillIpSrc(){
		document.getElementById('ip-src').value=document.getElementById('ip-src-list').value;
	}
	function FillIpDest(){
		document.getElementById('ip-dest').value=document.getElementById('ip-dest-list').value;
	}
	

	
	function IpRotatorListRefresh(){
		LoadAjax('ip-rotator-list','$page?ip-rotator-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
	}
	
	function IprotatorForm(ID){
		LoadAjax('form-rotator-display','$page?ip-rotator-form=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}&ID='+ID);
	}
	

		var x_AddNewIprotator= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			IpRotatorListRefresh();
			
		}	

		
		function AddNewIprotator(ID){
			var XHR = new XHRConnection();
			var value='';
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			
			value=document.getElementById('ip-src').value;
			if(value.length==0){return;}
			value=document.getElementById('ip-dest').value;
			if(value.length==0){return;}
			value=document.getElementById('ip-mode').value;
			if(value.length==0){return;}
			value=document.getElementById('mode-hits').value;
			if(value.length==0){return;}	
			XHR.appendData('SaveRule','yes');
			XHR.appendData('ID',ID);
			XHR.appendData('ip-src',document.getElementById('ip-src').value);
			XHR.appendData('ip-dest',document.getElementById('ip-dest').value);
			XHR.appendData('ip-mode',document.getElementById('ip-mode').value);
			XHR.appendData('ip-mode-value',document.getElementById('mode-hits').value);
			document.getElementById('ip-rotator-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddNewIprotator);
		}	
	
	IpRotatorListRefresh();
	IprotatorForm(0);
	
</script>
";		echo $tpl->_ENGINE_parse_body($html);
}

function SaveRule(){
	
	if(!is_numeric($_GET["ID"])){echo "ID: Wrong integer";return;}
	
	$sql_add="INSERT INTO ip_rotator_smtp (ipsource,ipdest,`mode`,mode_value,hostname,ou) 
	VALUES('{$_GET["ip-src"]}','{$_GET["ip-dest"]}','{$_GET["ip-mode"]}','{$_GET["ip-mode-value"]}','{$_GET["hostname"]}','{$_GET["ou"]}')";
	
	$sql_edit="UPDATE ip_rotator_smtp SET 
		ipsource='{$_GET["ip-src"]}',
		ipdest='{$_GET["ip-dest"]}',
		`mode`='{$_GET["ip-mode"]}',
		mode_value='{$_GET["ip-mode-value"]}'
		WHERE ID='{$_GET["ID"]}'
		";
	$sql=$sql_add;
	if($_GET["ID"]>0){$sql=$sql_edit;}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
}

//

function rotator_list_rules(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$hostname=$_GET["hostname"];
$html="	<center><table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:400px'>
<thead class='thead'>
	<tr>
	<th width=1%>". imgtootltip("plus-24.png","{add}","IprotatorForm(0)")."</th>
	<th>{from}</th>
	<th nowrap>{switch}</th>
	<th nowrap>{to}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
";
if($hostname=="master"){$sql="SELECT * FROM ip_rotator_smtp ORDER BY ID";}else{
	$sql="SELECT * FROM ip_rotator_smtp WHERE ou='{$_GET["ou"]}' ORDER BY ID";
}

	$mode["nth"]="{counter}";
	$mode["random"]="{random}";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:IprotatorForm({$ligne["ID"]})\" 
		style='font-size:14px;font-weight:bold;text-decoration:underline'>";
		
		$delete=imgtootltip("delete-32.png","{delete}", "IpRotatorDel({$ligne["ID"]})");
		
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:14px' width=50% colspan=2>$href{$ligne["ipsource"]}</a></td>
		<td style='font-size:14px' width=1% nowrap>$href{$mode[$ligne["mode"]]}&nbsp;{$ligne["mode_value"]}</a></td>
		<td style='font-size:14px' width=50%>$href{$ligne["ipdest"]}</a></td>
		<td width=1%>$delete</td>
		</tr>
		";
	}

$html=$html."</table></center>

<script>
	function IpRotatorDel(ID){
			var XHR = new XHRConnection();
			var value='';
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('DeleteRule','yes');
			XHR.appendData('ID',ID);
			document.getElementById('ip-rotator-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddNewIprotator);
		}	
		
</script>

";

echo $tpl->_ENGINE_parse_body($html);
	
}

function form_rotator_mode(){
	$mode=$_GET["form-rotator-mode"];
	
	$ID=$_GET["ID"];	
	
	if($ID>0){
		$sql="SELECT mode_value FROM ip_rotator_smtp WHERE ID=$ID";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	}	
	
	$html="<table>";
	
	if($mode=="nth"){
		$html=$html."<tr>
		<td class=legend>{hits_number}:</td>
		<td>".Field_text("mode-hits",$ligne["mode_value"],"font-size:14px;width:40px")."</td>
		</tr>";
		
	}
	
	if($mode=="random"){
		$f[null]="{select}";
		for($i=1;$i<101;$i++){$dec=$i/100;$f["$dec"]="{$i}%";}
		
		$html=$html."<tr>
		<td class=legend>{hits_pourcentage}:</td>
		<td>".Field_array_Hash($f,"mode-hits",$ligne["mode_value"],null,null,0,"font-size:14px;padding:3px")."</td>
		</tr>";
		
	}	
	
	$html=$html."</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function ListRules(){
	//iptables-save |grep ArticaIpRotator
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?iptables-rotator-show=yes")));
	$tpl=new templates();
$html="	<center><table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{rules}</th>
	
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while (list ($a, $b) = each ($datas) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr><code style='font-size:13px'>$b</code></td></tr>";
		}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}



function PostFixVerifyRights(){
	$usersmenus=new usersMenus();
	if($usersmenus->AsPostfixAdministrator){return true;}
	if($usersmenus->AsMessagingOrg){return true;}
	}	
	