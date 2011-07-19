<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once("ressources/class.os.system.inc");
	include_once("ressources/class.lvm.org.inc");
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}
	
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["health"])){health();exit;}
	if(isset($_GET["health-table"])){health_table();exit;}
	popup();
	
	
function popup(){
	$page=CurrentPageName();
	$time=time();
	
	$html="<div id='$time'></div>
	<script>
		LoadAjax('$time','$page?tabs=yes&dev={$_GET["dev"]}');
	</script>	
	";
	
	echo $html;
	
	
}
	
function tabs(){
	
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["health"]='{health}';
	
	$md=md5($_GET["dev"]);
	

	while (list ($num, $ligne) = each ($array) ){
		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href='$page?$num=yes&dev={$_GET["dev"]}'><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id='$md' style='width:100%;height:620px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$('#$md').tabs();});
		</script>";		
}


function status(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$datas=unserialize(base64_decode($sock->getFrameWork("smart.php?status={$_GET["dev"]}")));
	$html="<div style='font-size:16px'>{APP_SMARTMONTOOLS}</div>
	<div style='text-align:right;font-size:14px;margin-top:5px;border-top:1px solid #CCCCCC;padding-top:5px'><i style='font-size:14px'>{$_GET["dev"]}</i></div>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2></th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	while (list ($key, $val) = each ($datas) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr>
		<td style='font-size:14px;font-weight:bold;text-align:right' width=1% nowrap>$key:</td>
		<td style='font-size:14px;font-weight:bold;text-align:left' width=99% nowrap>$val</td>
		</tr>
		";
		
		
	}

$html=$html."
</tbody>
</table>";

echo $tpl->_ENGINE_parse_body($html);
	
}


function health(){
	$page=CurrentPageName();
	$html="<div id='helth-table'></div>
	
	<script>
		LoadAjax('helth-table','$page?health-table=yes&dev={$_GET["dev"]}');
	</script>
	
	
	";	
	echo $html;
}

function health_table(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$datas=unserialize(base64_decode($sock->getFrameWork("smart.php?health={$_GET["dev"]}")));	
	//http://fr.wikipedia.org/wiki/Self-Monitoring%2C_Analysis_and_Reporting_Technology#Attributs_S.M.A.R.T._connus
	//http://en.wikipedia.org/wiki/S.M.A.R.T.
	$warns[1]=true;
	$warns[5]=true;
	$warns[10]=true;
	$warns[196]=true;
	$warns[197]=true;
	$warns[198]=true;
	$warns[201]=true;
	$warns[220]=true;
	
	//lower=tresh
	$html="	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{attribute}</th>
	<th>{current}</th>
	<th>{treshold}</th>
	<th>{worst}</th>
	<th>{health}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	while (list ($ID, $array) = each ($datas) ){
		$help=help_icon("ID&nbsp;<strong>$ID</strong>:<hr>{{$array["ATTRIBUTE"]}_text}");
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($array["ATTRIBUTE"]=="Unknown_Attribute"){continue;}
		$a=(intval($array["TRESH"])/$array["VALUE"])*100;
		$p=100-$a;
		$d=intval($p);
		
			$color="#5DD13D";
			if($d<75){$color="#F59C44";}
			if($d<50){$color="#D32D2D";}			
		
		$health=pourcentage_basic($d,$color,"$d%");
		
		$html=$html."<tr class=$classtr>
		<td width=1% nowrap>$help</td>
		<td style='font-size:14px;font-weight:bold;text-align:right' width=1% nowrap>{{$array["ATTRIBUTE"]}}:</td>
		<td style='font-size:14px;font-weight:bold;text-align:left' width=1% nowrap>{$array["VALUE"]}</td>
		<td style='font-size:14px;font-weight:bold;text-align:left' width=1% nowrap>{$array["TRESH"]}</td>
		<td style='font-size:14px;font-weight:bold;text-align:left' width=1% nowrap>{$array["WORST"]}</td>
		<td style='font-size:14px;font-weight:bold;text-align:left' width=1% nowrap>$health</td>
		</tr>
		";		
		
	}
	
	$html=$html."
	
	
	</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
