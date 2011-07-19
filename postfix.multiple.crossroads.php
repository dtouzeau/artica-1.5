<?php
	if(posix_getuid()==0){die();}
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.tcpip.inc');
	include_once('ressources/class.system.nics.inc');
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["config"])){config();exit;}
	if(isset($_GET["instanceslist-crossroads"])){instanceslist();exit;}
	if(isset($_POST["activate-instances"])){instances_save();exit;}
	if(isset($_GET["client-settings"])){client_settings_form();exit;}
	if(isset($_POST["client-settings-save"])){client_settings_save();exit;}
	
	
	js();
function js(){
	$page=CurrentPageName();
	$ipaddr=$_GET["ipaddr"];
	$tpl=new templates();
	$title=$tpl->javascript_parse_text("{load_balancer}:$ipaddr");
	echo"YahooWin2('650','$page?tabs=yes&ipaddr=".urlencode($ipaddr)."','$title')";
}

function tabs(){
	$ipaddr=$_GET["ipaddr"];
	$page=CurrentPageName();
	$tpl=new templates();
	$array["config"]="{instances}";
	
	while (list ($num, $ligne) = each ($array) ){
		
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ipaddr=".urlencode($ipaddr)."\"><span>$ligne</span></a></li>\n");
			continue;
		
	}
	
	
	echo "
	<div id=main_config_postfixmultipeCrossRoads style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_postfixmultipeCrossRoads\").tabs();});
		</script>";	
	
}


function config(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ipaddr=$_GET["ipaddr"];
	$html="
	<div class=explain>{load_balancer_instances_explain}</div>
	<center>
		<table style='width:450px' class=form>
		<tr>
			<td class=legend valign='middle'>{search}:</td>
			<td valign='middle'>". Field_text("instances-cross-search",null,"font-size:14px;padding:3px;width:100%",null,null,null,false,
			"PostfixInstancesCrossSearchCheck(event)")."</td>
			<td width=1% valign='middle'>". button("{search}","RefreshCrossInstancesList()")."</td>
		</tr>
		</table>
	</center>	
	
	<div id='instanceslist-crossroads' style='width:100%;height:350px'></div>
	
	<script>
	function PostfixInstancesCrossSearchCheck(e){
			if(checkEnter(e)){RefreshCrossInstancesList();}
		}
	
	
		function RefreshCrossInstancesList(){
			var se=escape(document.getElementById('instances-cross-search').value);
			LoadAjax('instanceslist-crossroads','$page?instanceslist-crossroads=yes&search='+se+'&ipaddr=".urlencode($ipaddr)."');
		}
		
		RefreshCrossInstancesList();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function instanceslist(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ipaddr=$_GET["ipaddr"];
	$search=$_GET["search"];
	$search="*".$_GET["search"]."*";
	$search=str_replace("**","*",$search);
	$search=str_replace("*","%",$search);	
	$q=new mysql();	
	$sql="SELECT * FROM crossroads_smtp WHERE ipaddr='$ipaddr'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$arrayConf=unserialize($ligne["parameters"]);
	$instanceslist=$arrayConf["INSTANCES"];
	$instancesParams=$arrayConf["INSTANCES_PARAMS"];
$sql="SELECT ou, ip_address, `key` , `value` FROM postfix_multi 
	WHERE (`key` = 'myhostname' AND value LIKE '$search') OR (`key` = 'myhostname' AND ip_address LIKE '$search') ORDER BY value LIMIT 0,50";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("refresh-32.png","{refresh}","RefreshCrossInstancesList()")."</th>
		<th>{servername}</th>
		<th>{ip_address}</th>
		<th width=1%>{max_con}</th>
		<th width=1%>{weight}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>		
";
	
	$c=0;
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$instance=$ligne["value"];
	
	$strlen=strlen($ligne["value"]);
	if($strlen>33){$hostname_text=substr($ligne["value"],0,33)."...";}else{$hostname_text=$ligne["value"];}
	$md5=md5($ligne["ip_address"]);
	$c++;
	$checkbox=Field_checkbox($md5,1,$instanceslist[$ligne["ip_address"]],"EnableCrossInstance_$c()");
	if(!is_numeric($instancesParams["MAXCONS"][$ligne["ip_address"]])){$instancesParams["MAXCONS"][$ligne["ip_address"]]=0;}
	if(!is_numeric($instancesParams["WEIGTH"][$ligne["ip_address"]])){$instancesParams["WEIGTH"][$ligne["ip_address"]]=1;}
	
	
	$checkboxjs[]="
	function EnableCrossInstance_$c(){
		var XHR = new XHRConnection();
		var value=0;
		XHR.appendData('activate-instances','{$ligne["ip_address"]}');
		if(document.getElementById('$md5').checked){value=1;}
		XHR.appendData('activate-instances-value',value);
		XHR.appendData('ipaddr','$ipaddr');
		XHR.sendAndLoad('$page', 'POST',x_EnableCrossInstance);
		}
	
	";
	
	$link="<a href=\"javascript:blur();\" OnClick=\"javascript:RoundRobbinSettings('{$ligne["ip_address"]}')\"
		style='font-size:14px;font-weight:bold;text-decoration:underline;'>";
	
	$html=$html."<tr  class=$classtr>
	<td style='font-size:14px;font-weight:bold'><img src=img/fw_bold.gif></td>
	<td style='font-size:14px;font-weight:bold'>$link$hostname_text</a></td>
	<td style='font-size:14px;font-weight:bold'>$link{$ligne["ip_address"]}</a></td>
	<td style='font-size:14px;font-weight:bold' width=1%>{$instancesParams["MAXCONS"][$ligne["ip_address"]]}</a></td>
	<td style='font-size:14px;font-weight:bold'width=1% >{$instancesParams["WEIGTH"][$ligne["ip_address"]]}</a></td>
	<td width=1%>$checkbox</td>
	</tR>";	
	}
	
	$html=$html."
	</table>
	<script>
	var x_EnableCrossInstance= function (obj) {
	 	var results=obj.responseText;
		 if(results.length>2){alert(results);}
	 
	}	
	
	function RoundRobbinSettings(client){
		YahooWin4(390,'$page?client-settings='+client+'&ipaddr=$ipaddr',client);
	}
	
	
	
	
	".@implode("\n",$checkboxjs)."
	
</script>";

	echo $tpl->_ENGINE_parse_body($html);
	
}

function client_settings_form(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();	
	$ipaddr=$_GET["ipaddr"];
	$sql="SELECT * FROM crossroads_smtp WHERE ipaddr='$ipaddr'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$page=CurrentPageName();
	
	$client=$_GET["client-settings"];
	$arrayConf=unserialize($ligne["parameters"]);
	$instanceslist=$arrayConf["INSTANCES"];
	$instancesParams=$arrayConf["INSTANCES_PARAMS"];	
	if(!is_numeric($instancesParams["MAXCONS"][$client])){$instancesParams["MAXCONS"][$client]=0;}
	if(!is_numeric($instancesParams["WEIGTH"][$client])){$instancesParams["WEIGTH"][$client]=1;}	
	
	$html="
	<div class=explain>{crossroads_multiple_weight_roundrobbinexplain}</div>
	<p>&nbsp;</p>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{max_connexions}:</td>
		<td>". Field_text("MAXCONS",$instancesParams["MAXCONS"][$client],"width:60px;font-size:16px;")."</td>
	</tr>
	<tr>
		<td class=legend>{weight}:</td>
		<td>". Field_text("WEIGTH",$instancesParams["WEIGTH"][$client],"width:60px;font-size:16px;")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveCrossRoundRbs()")."</td>
	</tr>
	</table>
	
	<script>
	
		var x_SaveCrossRoundRbs= function (obj) {
		 var results=obj.responseText;
		 if(results.length>2){alert(results);return;}
		 YahooWin4Hide();
		 RefreshCrossInstancesList();
		}	
	
	
	function SaveCrossRoundRbs(){
		var XHR = new XHRConnection();
		XHR.appendData('client-settings-save','yes');
		XHR.appendData('ipaddr','$ipaddr');
		XHR.appendData('client','$client');
		XHR.appendData('MAXCONS',document.getElementById('MAXCONS').value);
		XHR.appendData('WEIGTH',document.getElementById('WEIGTH').value);
		XHR.sendAndLoad('$page', 'POST',x_SaveCrossRoundRbs);	
	}
</script>	
	";
	echo $tpl->_ENGINE_parse_body($html);
		
}


function client_settings_save(){
	$q=new mysql();	
	$ipaddr=$_POST["ipaddr"];
	$client=$_POST["client"];
	$sql="SELECT * FROM crossroads_smtp WHERE ipaddr='$ipaddr'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$arrayConf=unserialize($ligne["parameters"]);
	$arrayConf["INSTANCES_PARAMS"]["MAXCONS"][$client]=$_POST["MAXCONS"];
	$arrayConf["INSTANCES_PARAMS"]["WEIGTH"][$client]=$_POST["WEIGTH"];
	
	$datas=addslashes(serialize($arrayConf));
	
	$sqlupd="UPDATE crossroads_smtp SET parameters='$datas' WHERE ipaddr='$ipaddr'";
	$q->QUERY_SQL($sqlupd,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("network.php?crossroads-restart=yes");	
}


function instances_save(){
	$q=new mysql();	
	$ipaddr=$_POST["ipaddr"];
	$sql="SELECT * FROM crossroads_smtp WHERE ipaddr='$ipaddr'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$arrayConf=unserialize($ligne["parameters"]);
	$instanceslist=$arrayConf["INSTANCES"];	
	if($_POST["activate-instances-value"]==1){
		$arrayConf["INSTANCES"][$_POST["activate-instances"]]=1;
	}else{
		unset($arrayConf["INSTANCES"][$_POST["activate-instances"]]);
	}
	
	$datas=addslashes(serialize($arrayConf));
	$sql="INSERT INTO crossroads_smtp (ipaddr,parameters) VALUES('$ipaddr','$datas')";
	$sqlupd="UPDATE crossroads_smtp SET parameters='$datas' WHERE ipaddr='$ipaddr'";
	if($ligne["ipaddr"]<>null){$sql=$sqlupd;}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("network.php?crossroads-restart=yes");
}




