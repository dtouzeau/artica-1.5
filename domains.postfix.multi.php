<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.rtmm.tools.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["in-front-ajax"])){popup_js_front();exit;}
	if(isset($_GET["content"])){main_content();exit;}
	if(isset($_GET["left-menus"])){left_menus();exit;}
	if(isset($_GET["add-server"])){add_server_popup();exit;}
	if(isset($_GET["inet_interfaces"])){add_server_save();exit;}
	if(isset($_GET["servers-list"])){server_list();exit;}
	if(isset($_GET["rttm-logs"])){rttm_logs();exit;}
	popup();
			
	
function popup_js_front(){
	$tpl=new templates();
	$page=CurrentPageName();
	if(isset($_GET["encoded"])){
		$ou=base64_decode($_GET["ou"]);
	}
	$html="
	
	$('#BodyContent').load('$page?ou=$ou&with-title=yes');
	
	";
	echo $html;	
}	
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$width="720";
	$add_mail_server=$tpl->_ENGINE_parse_body("{add_mail_server}");
	if(isset($_GET["with-title"])){
		$title="<div style='margin-top:20px;margin-left:5px'><H1>{messaging_servers}</H1></h1>";
		$width="760";
	}
	
	$html="$title<div style='width:{$width}px'>
	<table style='width:100%'>
	<tr>	
	<td valign='top' width=5%>
		<div id='postfix-multi-servers-list'></div>
		<div id='postfix-multi-servers-listed' style='padding:3px;border-right:2px solid #CCCCCC;'></div>	
	</td>
	<td valign='top' width=95%><div id='postfix-multi-servers-content'></div></td>
	</tr>
	</table>
	</div>
	
	
	
	<script>
	    function postfix_multi_refresh_servers(){
	    	LoadAjax('postfix-multi-servers-content','$page?content=yes&ou={$_GET["ou"]}');
		}
	
			
		function RefreshRTMMTable(){
			LoadAjax('post-multi-rttm','$page?rttm-logs=yes&ou={$_GET["ou"]}');
		}	

		function RefreshRTMMTableForce(){
			LoadAjax('post-multi-rttm','$page?rttm-logs=yes&ou={$_GET["ou"]}&force-refresh=yes');
		}		
		
		function RefreshPostfixMultiList(){
			var se='';
			if(document.getElementById('instances-search-ou')){
				var se=escape(document.getElementById('instances-search-ou').value);
				se='&search-instance='+se;
			}
			LoadAjax('postfix-multi-servers-listed','$page?servers-list=yes&ou={$_GET["ou"]}'+se);
		}
		
		function AddPostfixMulti(){
			YahooWin3('475','$page?add-server=yes&ou={$_GET["ou"]}','$add_mail_server');
		}
		
		function PostfixMultiServerParamsSection(name){
			LoadAjax('postfix-multi-servers-content','domains.postfix.multi.config.php?ou={$_GET["ou"]}&hostname='+name);
			
		}
		
		
		
	var x_AddPostfixMultiSave= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert('\"'+results+'\"');}
		postfix_multi_refresh_servers();
		YahooWin3Hide();
		}
		
	
		
		function AddPostfixMultiSave(){
			var XHR = new XHRConnection();
			XHR.appendData('inet_interfaces',document.getElementById('inet_interfaces').value);
			XHR.appendData('hostname',document.getElementById('hostname').value+'.'+document.getElementById('domain').value);
			XHR.appendData('ou','{$_GET["ou"]}');
			document.getElementById('AddPostfixMultiSave').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad('$page', 'GET',x_AddPostfixMultiSave);
			}
		
		
	postfix_multi_refresh_servers();
	</script>	
		
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function GetFreeIps(){
	
	$q=new mysql();
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));

	$sql="SELECT `value` FROM postfix_multi WHERE `uuid`='$uuid' AND `ou`='{$_GET["ou"]}' AND `key`='inet_interfaces'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$already[trim($ligne["value"])]=true;
	}
	
	
	$sql="SELECT ipaddr FROM nics_virtuals WHERE org='{$_GET["ou"]}'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["ipaddr"]=trim($ligne["ipaddr"]);
		if($already[trim($ligne["ipaddr"])]){continue;}
		$nics_virtuals[trim($ligne["ipaddr"])]=$ligne["ipaddr"];
	}
	
	return 	$nics_virtuals;
}

function main_content(){
	
	
	$sock=new sockets();
	$q=new mysql();

	
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));	
	$sql="SELECT `value`,ip_address FROM postfix_multi WHERE `uuid`='$uuid' AND `ou`='{$_GET["ou"]}' AND `key`='myhostname'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	
	$html="<table style='width:100%'>
	<th>&nbsp;</th>
	<th colspan=2>{servername}</th>
	<th>{smtp_queues}</th>
	<th>PID</th>
	</tr>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$hostname=$ligne["value"];
		$infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-multi-status=$hostname")));
		$pid="-";	
		$running="danger16.png";	
		if(is_array($infos)){
			$pid=$infos["PID"];
			$running="ok16.png";
		}
		$tot=base64_decode($sock->getFrameWork("cmd.php?postfix-multi-postqueue=$hostname"));
		
		$html=$html."
		<tr ". CellRollOver("PostfixMultiServerParamsSection('$hostname')").">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td width=99%><strong style='font-size:12px'>$hostname</strong></td>
		<td width=1% align=center>".imgtootltip($running,"{running}")."</td>
		<td width=1% align=center><strong style='font-size:12px'>$tot</strong></td>
		<td width=1% align=center><strong style='font-size:12px'>$pid</strong></td>
		</tr>			
		";
		
		
		
	}
	

	
	$html=$html."</table>
	
	<div id='post-multi-rttm'></div>

	<script>
	 RefreshPostfixMultiList();
	</script>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
		
	
}






function server_list(){
	$sock=new sockets();
	$maxSearch=4;
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	$search_org=$_GET["search-instance"];	
	if(preg_match("#(.+?):([0-9]+)#",$search_org,$re)){
		$search_org=trim($re[1]);
		$maxSearch=$re[2];
	}
	
	if(strlen($_GET["search-instance"])>2){
		$_GET["search-instance"]="*{$_GET["search-instance"]}*";
		$_GET["search-instance"]=str_replace("*","%",$_GET["search-instance"]);
		$_GET["search-instance"]=str_replace("%%","%",$_GET["search-instance"]);
		$searchq="AND value LIKE '{$_GET["search-instance"]}'";

	}
	
	$sql="SELECT `value`,ip_address FROM postfix_multi WHERE `uuid`='$uuid' AND `ou`='{$_GET["ou"]}' AND `key`='myhostname' $searchq";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$count=mysql_num_rows($results);
	
	
		$formsaerch="
		<table class=form>
		<tr>
			<td class=legend>{search}:</td>
			<td>". Field_text('instances-search-ou',$search_org,'font-size:13px;width:110px',null,null,null,false,"InstanceSearchOuCheck(event)")."</td>
		</tr>
		</table>
		
		";
	
	
	if($searchq==null){
		if($count<5){$formsaerch=null;}
	}
	
	$html=$html."$formsaerch";
	
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$c++;
			if($c>$maxSearch){break;}
			$main=new maincf_multi($ligne["value"],$_GET["ou"]);
			$VirtualHostNameToChange=$main->GET("VirtualHostNameToChange");	
			$PostFixEnableAiguilleuse=$main->GET("PostFixEnableAiguilleuse");
			$freeze_delivery_queue=$main->GET('freeze_delivery_queue');
			if($VirtualHostNameToChange<>null){$VirtualHostNameToChange="<br>$VirtualHostNameToChange";}
		    $img="48-network-server.png";
		
		if($PostFixEnableAiguilleuse==1){
			$img="48-network-server-star.png";
			$server_text="{router}";
		}
		
		if($freeze_delivery_queue==1){
			$img="48-network-server-paused.png";	
			$server_text="{WARN_QUEUE_FREEZE}";
		}
		
		$servername=$ligne["value"].strlen($ligne["value"]);
		if(strlen($servername)>17){$servername=substr($servername,0,14)."...";}
		
		$js="PostfixMultiServerParamsSection('{$ligne["value"]}')";
		$html=$html."
		<table style='width:100%;margin-top:10px;padding:10px'>
		<tr ". CellRollOver("$js").">
			<td valign=top width=1% style='padding:5px'><img src='img/$img'></td>
			<td valign='top' style='padding:5px'>
			<div>
				<strong style='font-size:13px'>$servername</strong>
			</div>
			<div style='border-top:1px solid #CCCCCC;text-align:right'>
				<i style='font-size:11px'>$server_text - {$ligne["ip_address"]}$VirtualHostNameToChange</i></div>
			</td>
			</tr>
		</table>
		<script>RefreshRTMMTable()</script>
		";
		
	}

	$ips=GetFreeIps();
	if(is_array($ips)){
		$html=$html."
		<table style='width:100%;margin-top:10px;padding:10px'>
		<tr ". CellRollOver("AddPostfixMulti()").">
			<td valign=top width=1% style='padding:5px'><img src='img/48-network-server-add.png'></td>
			<td valign='top' style='padding:5px'>
			<div>
				<strong style='font-size:13px'>{add_mail_server}</strong>
			</div>
			<div><i style='font-size:11px'>{postfix_multi_add_mailserver}</i></div>
			</td>
			</tr>
		</table>
		<script>RefreshRTMMTable()</script>
		";
	}else{
		$html=$html."
		<table style='width:100%;margin-top:10px;padding:10px'>
		<tr>
			<td valign=top width=1% style='padding:5px'><img src='img/48-network-server-add-grey.png'></td>
			<td valign='top' style='padding:5px'>
			<div>
				<strong style='font-size:13px'>{add_mail_server}</strong>
			</div>
			<div><i style='font-size:11px'>{postfix_multi_add_mailserver_no_ips}</i></div>
			</td>
			</tr>
		</table>";	
	}
	
	
	$html=$html."
	
	<script>
		function InstanceSearchOuCheck(e){
			if(checkEnter(e)){InstanceSearchOu();}
		}
		
		function InstanceSearchOu(){
			RefreshPostfixMultiList();
		
		

		}
	</script>
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}


function add_server_save(){
	$inet_interfaces=$_GET["inet_interfaces"];
	$q=new mysql();
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	$_GET["hostname"]=trim($_GET["hostname"]);
	
	$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' AND `value`='{$_GET["hostname"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["value"]<>null){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{servername_already_used}");
		return;
	}
	
	
	$sql="INSERT INTO  postfix_multi (`uuid`,`ou`,`key`,`value`,`ip_address`) VALUES('$uuid','{$_GET["ou"]}','inet_interfaces','$inet_interfaces','$inet_interfaces');";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo "$sql\n$q->mysql_error\n";
		return;
	}
	
	$sql="INSERT INTO  postfix_multi (`uuid`,`ou`,`key`,`value`,`ip_address`) VALUES('$uuid','{$_GET["ou"]}','myhostname','{$_GET["hostname"]}','$inet_interfaces');";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo "$sql\n$q->mysql_error\n";
		return;
	}	
	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?restart-postfix-single-now=yes");
	
}

function add_server_popup(){
	$ips=GetFreeIps();
	$tpl=new templates();
	$ldap=new clladp();
	
	$domains=$ldap->Hash_domains_table($_GET["ou"]);
	if(is_array($domains)){
		while (list ($num, $val) = each ($domains) ){$hashsoms[$num]=$num;}
	}else{
		echo $tpl->_ENGINE_parse_body("<H2 style=color:red'>{ERROR_CREATE_SMTP_DOMAINS_FIRST}</h2>");
		return; 
	}
	
	$ips_field=Field_array_Hash($ips,"inet_interfaces",null,null,null,0,"font-size:13px;padding:3px");
	$domains_field=Field_array_Hash($hashsoms,"domain",null,null,null,0,"font-size:13px;padding:3px");
	
	$html="
	<div id='AddPostfixMultiSave'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{listen_ip}:</td>
		<td class=legend style='font-size:13px'>$ips_field</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{servername}:</td>
		<td class=legend style='font-size:13px'>". Field_text("hostname",null,
		"font-size:13px;padding:3px","script:AddPostfixMultiSaveCheck(event)")."</td>
		<td><span style='font-size:13px'>.</span>$domains_field</td>
	<tr>
	<td colspan=3 align='right'>
	<hr>
		". button("{add}","AddPostfixMultiSave()")."</td>
	</tr>
	</table>
	</div>
	<script>
		function AddPostfixMultiSaveCheck(e){
			if(checkEnter(e)){AddPostfixMultiSave();}
		}
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
}


function rttm_logs(){
	
	if(!isset($_GET["force-refresh"])){
		if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return null;}
	}
	
	$sql=__sql_domain($_GET["ou"]);
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$tr=$tr.format_line($ligne);
		}

	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body("
	<hr><div style='text-align:right;padding:4px'>". imgtootltip("refresh-24.png","{refresh}","RefreshRTMMTableForce()")."</div>
	<table style='width:100%'>
	<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{from}</th>
		<th>&nbsp;</th>
		<th>{to}</th>
		<th>{status}</th>
	</tr>
	$tr</table>");			

SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);	
echo $html;	
	
}


function __sql_domain($ou){
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table($ou);
	if(!is_array($domains)){return null;}
	while (list ($domain,$nothing) = each ($domains) ){
		$array_domain[]="OR delivery_domain='$domain'";
		$array_domain_sender[]="OR sender_domain='$domain'";
	}
	
	$sql_domain=implode(" ",$array_domain);
	$sql_domain_sent=implode(" ",$array_domain_sender);
	if(substr($sql_domain,0,2)=="OR"){$sql_domain=substr($sql_domain,2,strlen($sql_domain));}
	if(substr($sql_domain_sent,0,2)=="OR"){$sql_domain_sent=substr($sql_domain_sent,2,strlen($sql_domain_sent));}
	
	$sql_domain="(".trim($sql_domain).")";
	$sql_domain_sent="(".trim($sql_domain_sent).")";
	
	$sql="SELECT * FROM smtp_logs WHERE 1 AND $sql_domain OR $sql_domain_sent ORDER BY time_stamp DESC LIMIT 0,100";
	return $sql;	
	
}

	
?>