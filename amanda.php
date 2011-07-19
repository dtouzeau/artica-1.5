<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["params"])){service_parameters();exit;}
	if(isset($_POST["mailto"])){service_parameters_save();exit;}
	
	if(isset($_GET["dumptypes"])){dumptypes();exit;}
	if(isset($_GET["dumptype-key"])){dumptype_popup();exit;}
	if(isset($_POST["dumpname"])){dumptype_save();exit;}
	if(isset($_GET["amanda-dumptypes-list"])){dumptypes_list();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?tabs=yes');";
	
}


function service_parameters(){
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("AmandaServerConfig")));
	$page=CurrentPageName();
	$tpl=new templates();
	if(!isset($config["mailto"])){$config["mailto"]="root";}
	if(!isset($config["tapecycle"])){$config["tapecycle"]="6";}
	if(!isset($config["tapecycleMB"])){$config["tapecycleMB"]="3072";}		
	if(!is_numeric($config["netusage"])){$config["netusage"]=600;}
	
	
	$html="
	
	<div id='amandaserverid'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{recipient} ({notifications}):</td>
		<td>". Field_text("mailto",$config["mailto"],"font-size:14px;padding:3px;width:180px")."</td>
	</tr>
	<tr>
		<td class=legend>{tapecycle}:</td>
		<td>". Field_text("tapecycle",$config["tapecycle"],"font-size:14px;padding:3px;width:90px")."</td>
	</tr>	
	<tr>
		<td class=legend>{tapecycleMB}:</td>
		<td>". Field_text("tapecycleMB",$config["tapecycleMB"],"font-size:14px;padding:3px;width:90px")."&nbsp;MB</td>
	</tr>	
	<tr>
		<td class=legend>{amandanetusage}:</td>
		<td>". Field_text("netusage",$config["netusage"],"font-size:14px;padding:3px;width:90px")."&nbsp;Kbs</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveAmandaServerConfig()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var x_SaveGenBackupPC=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>0){alert(tempvalue);}
     
      
      }	

	function SaveAmandaServerConfig(){
		var XHR = new XHRConnection();
		XHR.appendData('mailto',document.getElementById('mailto').value);
		XHR.appendData('tapecycle',document.getElementById('tapecycle').value);
		XHR.appendData('tapecycleMB',document.getElementById('tapecycleMB').value);
		XHR.appendData('netusage',document.getElementById('netusage').value);
		AnimateDiv('amandaserverid');
		XHR.sendAndLoad('$page', 'POST',x_SaveAmandaServerConfig);		
	
	}
	</script>	
	
	";
echo $tpl->_ENGINE_parse_body($html);
}

function dumptype_save(){
	include_once(dirname(__FILE__)."/class.html.tools.inc");
	$html=new htmltools_inc();
	$_POST["dumpname"]= $html->StripSpecialsChars($_POST["dumpname"]);

	while (list ($num, $ligne) = each ($_POST) ){
		$field[]="`$num`";
		$ligne=addslashes($ligne);
		$vals[]="'$ligne'";
		$upd[]="`$num`='$ligne'";
	}
	
	$sql_add="INSERT INTO amanda_dumptype (".@implode(",", $field).") VALUES (".@implode(",", $vals).")";
	$sql_update="UDPATE amanda_dumptype SET " .@implode(",", $upd) ." WHERE dumpname='{$_POST["dumpname"]}'";;
	
	$q=new mysql();	
	$sql="SELECT * FROM amanda_dumptype WHERE dumpname='{$_POST["dumpname"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$sql=$sql_add;		
	if($ligne["dumpname"]<>null){$sql=$sql_update;}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
}

function service_parameters_save(){
	$sock=new sockets();
	$sock->SaveConfigFile(base64_encode(serialize($_POST)), "AmandaServerConfig");
	$sock->getFrameWork("amanda.php?save-server-config=yes");
	
}

function dumptypes(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div class=explain>{Amandadumptypes_explain}</div>
	<div id='amanda-dumptypes-list' style='width:100%;height:300px;overflow:auto'></div>
	
	
	<script>
		function LoadDumpTypes(){
			LoadAjax('amanda-dumptypes-list','$page?amanda-dumptypes-list=yes');
		
		}
		
		function AddDumptypeServer(key){
			var title;
			if(key.length==0){title='New';}else{title=key;}
			YahooWin3('550','$page?dumptype-key='+key,title);
		
		}
		
	LoadDumpTypes();
	</script>
";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function dumptype_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	
	$button="{apply}";
	if($_GET["dumptype-key"]==null){$button="{add}";}
	$sql="SELECT * FROM amanda_dumptype WHERE dumpname='{$_GET["dumptype-key"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	
	$authR["bsd"]="bsd";
	$authR["krb4"]="krb4";
	$authR["krb5"]="krb5";
	$authR["ssh"]="ssh";
	
$AmandaCompressExplain["none"]="none";
$AmandaCompressExplain["client fast"]="client fast";
$AmandaCompressExplain["client custom"]="client custom";
$AmandaCompressExplain["server best"]="server best";
$AmandaCompressExplain["server fast"]="server fast";
$AmandaCompressExplain["server custom"]="server custom";	

$estimate["client"]="client";
$estimate["calcsize"]="calcsize";
$estimate["server"]="server";

$priority["high"]="high";
$priority["low"]="low";
$priority["medium"]="medium";

$program["GNUTAR"]="GNUTAR";
$program["DUMP"]="DUMP";

$strategy["standard"]="standard";
$strategy["nofull"]="nofull";
$strategy["noinc"]="noinc";
$strategy["skip"]="skip";
$strategy["incronly"]="incronly";

if($ligne["compress"]==null){$ligne["compress"]="client fast";}
if($ligne["dumpcycle"]==null){$ligne["dumpcycle"]="4 weeks";}
if($ligne["estimate"]==null){$ligne["estimate"]="client";}
if($ligne["priority"]==null){$ligne["priority"]="medium";}
if($ligne["program"]==null){$ligne["program"]="GNUTAR";}
if($ligne["strategy"]==null){$ligne["strategy"]="standard";}
if($ligne["comprate"]==null){$ligne["comprate"]="0.50,0.50";}

if($ligne["holdingdisk"]==null){$ligne["holdingdisk"]="1";}
if(!is_numeric($ligne["maxdumps"])){$ligne["maxdumps"]="1";}
if(!is_numeric($ligne["maxpromoteday"])){$ligne["maxpromoteday"]=10000;}




	
	$html="
	<center><span id='formAmandaCheckID'></span></center>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{rule_name}:</td>
		<td>". Field_text("dumpname",$ligne["dumpname"],"font-size:14px;padding:3px;width:180px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{info}:</td>
		<td>". Field_text("comment",$ligne["comment"],"font-size:14px;padding:3px;width:180px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{authentication}:</td>
		<td>". Field_array_Hash($authR, "auth",$ligne["auth"],"style:font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{comprate}:</td>
		<td>". Field_text("comprate",$ligne["comprate"],"font-size:14px;padding:3px;width:180px")."</td>
		<td>". help_icon('{AmandaComprateExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{AmandaCompress}:</td>
		<td>". Field_array_Hash($AmandaCompressExplain, "compress",$ligne["compress"],"style:font-size:13px;padding:3px")."</td>
		<td>". help_icon('{AmandaCompressExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{dumpcycle}:</td>
		<td>". Field_text("dumpcycle",$ligne["dumpcycle"],"font-size:14px;padding:3px;width:180px")."&nbsp;{days}</td>
		<td>". help_icon('{dumpcycleExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{estimate}:</td>
		<td>". Field_array_Hash($estimate, "estimate",$ligne["estimate"],"style:font-size:13px;padding:3px")."</td>
		<td>". help_icon('{AmandaestimateExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{holdingdisk}:</td>
		<td>". Field_checkbox("holdingdisk",1,$ligne["holdingdisk"])."</td>
		<td>". help_icon('{holdingdiskExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{index}:</td>
		<td>". Field_checkbox("index",1,$ligne["index"])."</td>
		<td>". help_icon('{AmandaindexExplain}')."</td>
	</tr>	
	<tr>
		<td class=legend>{maxdumps}:</td>
		<td>". Field_text("maxdumps",$ligne["maxdumps"],"font-size:14px;padding:3px;width:180px")."&nbsp;</td>
		<td>". help_icon('{AmandamaxdumpsExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{promoting}:</td>
		<td>". Field_text("maxpromoteday",$ligne["maxpromoteday"],"font-size:14px;padding:3px;width:180px")."&nbsp;</td>
		<td>". help_icon('{maxpromotedayExplain}')."</td>
	</tr>	
	<tr>
		<td class=legend>{priority}:</td>
		<td>". Field_array_Hash($priority, "priority",$ligne["priority"],"style:font-size:13px;padding:3px")."</td>
		<td>". help_icon('{AmandapriorityExplain}')."</td>
	</tr>	
	<tr>
		<td class=legend>{program}:</td>
		<td>". Field_array_Hash($program, "program",$ligne["program"],"style:font-size:13px;padding:3px")."</td>
		<td>". help_icon('{AmandaprogramExplain}')."</td>
	</tr>	
	<tr>
		<td class=legend>{skip-incr}:</td>
		<td>". Field_checkbox("skip-incr",1,$ligne["skip-incr"])."</td>
		<td>". help_icon('{skip-incrExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{skip-full}:</td>
		<td>". Field_checkbox("skip-full",1,$ligne["skip-full"])."</td>
		<td>". help_icon('{skip-fullExplain}')."</td>
	</tr>
	<tr>
		<td class=legend>{AmandaStrategy}:</td>
		<td>". Field_array_Hash($strategy, "strategy",$ligne["strategy"],"style:font-size:13px;padding:3px")."</td>
		<td>". help_icon('{AmandaStrategyExplain}')."</td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button($button,"SaveAmandaDumpRule()")."</td>
	</tr>					
	</table>
	
<script>
	var x_SaveAmandaDumpRule=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>0){alert(tempvalue);document.getElementById('formAmandaCheckID').innerHTML='';return;}
      YahooWin3Hide();
      LoadDumpTypes();
      }	

	function SaveAmandaDumpRule(){
		var XHR = new XHRConnection();
		XHR.appendData('dumpname',document.getElementById('dumpname').value);
		XHR.appendData('comment',document.getElementById('comment').value);
		XHR.appendData('auth',document.getElementById('auth').value);
		XHR.appendData('comprate',document.getElementById('comprate').value);
		XHR.appendData('compress',document.getElementById('compress').value);
		XHR.appendData('dumpcycle',document.getElementById('dumpcycle').value);
		XHR.appendData('estimate',document.getElementById('estimate').value);
		if(document.getElementById('holdingdisk').checked){XHR.appendData('holdingdisk',1);}else{XHR.appendData('holdingdisk',0);}
		if(document.getElementById('index').checked){XHR.appendData('index',1);}else{XHR.appendData('index',0);}		
		XHR.appendData('maxdumps',document.getElementById('maxdumps').value);
		XHR.appendData('maxpromoteday',document.getElementById('maxpromoteday').value);
		XHR.appendData('priority',document.getElementById('priority').value);
		XHR.appendData('program',document.getElementById('program').value);
		if(document.getElementById('skip-incr').checked){XHR.appendData('skip-incr',1);}else{XHR.appendData('skip-incr',0);}
		if(document.getElementById('skip-full').checked){XHR.appendData('skip-full',1);}else{XHR.appendData('skip-full',0);}
		XHR.appendData('strategy',document.getElementById('strategy').value);
		AnimateDiv('formAmandaCheckID');
		XHR.sendAndLoad('$page', 'POST',x_SaveAmandaDumpRule);		
	}
</script>	
	
	";	

	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function dumptypes_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();

	
	
	$add=imgtootltip("plus-24.png","{add}","AddDumptypeServer('')");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=2>{Amandadumptypes}</th>
		<tH>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$q=new mysql();
		$sql="SELECT dumpname,comment FROM amanda_dumptype ORDER BY dumpname";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$delete=imgtootltip("delete-32.png","{delete}","dumpTypeDelete('{$ligne["dumpname"]}')");
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/Database32.png'></td>
			<td style='font-size:14px' width=50%><a href=\"javascript:blur();\" OnClick=\"javascript:AddDumptypeServer('{$ligne["dumpname"]}');\"
			style='font-size:14px;font-weight:bold;text-decoration:underline'>{$ligne["dumpname"]}</a></td>
			<td style='font-size:14px' width=50%><strong>&laquo;{$ligne["comment"]}&raquo;</strong></td>
			<td width=1%>$delete</td>
			</tr>
			";
		}

		
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function tabs(){
	
	$page=CurrentPageName();
	$array["params"]='{parameters}';
	$array["dumptypes"]='{Amandadumptypes}';
	$array["remote-clients"]='{remote_clients}';
	
	$tpl=new templates();

	while (list ($num, $ligne) = each ($array) ){
		if($num=="remote-clients"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"amada.clients.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_amanda style='width:100%;height:350px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_amanda').tabs();
			
			
			});
		</script>";		
	
	
}