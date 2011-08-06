<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsArticaAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
	}	

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){enable_form();exit;}
	if(isset($_GET["EnableEmergingThreats"])){save();exit;}
	if(isset($_GET["list"])){list_threats();exit;}
	if(isset($_GET["list_threats"])){list_threads_perform();exit;}
	
js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("YahooWin3('400','$page?popup=yes','{EmergingThreats}');");
	
	
}

function list_threads_perform(){
	$se=$_GET["se"];
	$se=str_replace("*",".+",$se);
	$se=str_replace(".","\.",$se);
	
	$tpl=new templates();
$list=unserialize(@file_get_contents("ressources/logs/EnableEmergingThreatsBuild.db"));
	if(!is_array($list)){
		echo $tpl->_ENGINE_parse_body("<H2>{ERROR_NO_DATA}</H2>");
		return;
	}
	
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99.5%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{$list["COUNT"]} {rules}</th>
	</tr>
</thead>
<tbody class='tbody'>";
$count=0;
	if(is_array($list["THREADS"])){
		while (list ($num, $ligne) = each ($list["THREADS"]) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				if(trim($se)<>null){if(!preg_match("#$se#",$ligne)){continue;}}
				$count++;
				$html=$html.
				"<tr class=$classtr>
					<td width=1%><img src='img/dns-cp-22.png'></td>
					<td><strong style='font-size:11px'>$ligne</td>
				</tr>";
				if($count>500){break;}
				
			}
	}

	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);	
}

function list_threats(){
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?EnableEmergingThreatsBuild=yes");

	
$html="
<center>". Field_text("searchZ",null,"font-size:16px;padding:3px","script:EmergingThreatsSearch(event)")."</center>
<br>
<div id='list_threats' style='height:350px;overflow;auto'></div>

<script>

	function EmergingThreatsSearch(e){
		if(checkEnter(e)){EmergingThreatsSearchZ();}
	}
	
	function EmergingThreatsSearchZ(){
		var se=escape(document.getElementById('searchZ').value);
		LoadAjax('list_threats','$page?list_threats=yes&se='+se);
	}	
	EmergingThreatsSearchZ();
</script>
";
	echo $html;
	
}
	
function popup(){
	
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["list"]='{rules}';
	$tpl=new templates();


	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo  "
	<div id=main_config_EmergingThreats style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_EmergingThreats\").tabs();});
		</script>";		
	
	
}

function enable_form(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$EnableEmergingThreats=$sock->GET_INFO("EnableEmergingThreats");
	$p=Paragraphe_switch_img("{enable_EmergingThreats}","{EmergingThreats_text}","EnableEmergingThreats",
	$EnableEmergingThreats,null,350);
	
	$html="
	<div id='EnableEmergingThreatsDiv'>
		$p
	
	<div style='text-align:right'><hr>". button("{apply}","SaveEnableEmergingThreats()")."</div>
	
	</div>
	<script>
	function x_SaveEnableEmergingThreats(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_config_EmergingThreats');
	}	
	
	
	function SaveEnableEmergingThreats(){
	    var XHR = new XHRConnection();
		XHR.appendData('EnableEmergingThreats',document.getElementById('EnableEmergingThreats').value);
		document.getElementById('EnableEmergingThreatsDiv').innerHTML=\"<center style='width:400px'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_SaveEnableEmergingThreats);
	}
	</script>
	";
		
	echo $tpl->_ENGINE_parse_body($html);	
		
	
}	

function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableEmergingThreats",$_GET["EnableEmergingThreats"]);
	$sock->getFrameWork("cmd.php?EnableEmergingThreats=yes");
}
	
