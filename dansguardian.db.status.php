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
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	
	
js();



function js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{DANSGUARDIAN_BLACKLISTS_STATUS}');

$html="
	var mem_rule_id='';
	var mem_cat='';
	var rule_main_mem='';
	function DANSGUARDIAN_BLACKLISTS_STATUS(){
	YahooWin2(770,'$page?popup=yes','$title',''); 
	}	
	
	
	DANSGUARDIAN_BLACKLISTS_STATUS();";
	
	echo $html;
	
}

//squidguard-db-status

function popup_squidguard(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?squidguard-db-status=yes")));
	$tpl=new templates();
	
	$html="
	<div style='font-size:14px;'>{DANSGUARDIAN_DB_STATUS_EXPLAIN}</div>
	<div style='width:100%;height:350px;overflow:auto'>
	<table style='width:100%'>
		<tr>
		<th style='font-size:14px' nowrap>{category}</th>
		<th style='font-size:14px' nowrap>{domains}</th>
		<th style='font-size:14px' nowrap>{urls}</th>
		<th style='font-size:14px' nowrap>Regex</th>
		<th style='font-size:14px' nowrap>{date}</th>
		<th style='font-size:14px' nowrap>{squidguard_database_size}</th>
	</tr>";


	if(!is_array($datas)){
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
		return;
	}
	
	
	
	while (list ($path, $array) = each ($datas) ){

		
	//	if($path==null){continue;}
		$category=squidguard_extract_category($path);
		$Narray[$category][$array["type"]]["linesn"]=$array["linesn"];
		
			$Narray[$category][$array["type"]]["size"]=$array["size"];
		
		$Narray[$category][$array["type"]]["date"]=$array["date"];
		

		
	}
	
	

	while (list ($category, $array2) = each ($Narray) ){
		
		
		if($array2["expressionlist"]["linesn"]==null){$array2["expressionlist"]["linesn"]=0;}
		if($array2["domainlist"]["linesn"]==null){$array2["expressionlist"]["linesn"]=0;}
		if($array2["urllist"]["linesn"]==null){$array2["expressionlist"]["linesn"]=0;}
	
		if(is_array($array2["domainlist"])){
			if($array2["domainlist"]["size"]==null){
				$not_compiled=texttooltip("{not_compiled}","{compile_squidguard_databases}<hr><i>{compile_squidguard_databases_text}</i>","Loadjs('squidguard.status.php?compile=yes')",0,"color:red;font-size:14px;font-weight:bold");
				$array2["domainlist"]["size"]="<span style='color:red'>$not_compiled</span>";
			
		}else{$array2["domainlist"]["size"]=FormatBytes($array2["domainlist"]["size"]/1024);}
		
		}else{
			$array2["domainlist"]["size"]="-";
		}

		
		
		if($array2["domainlist"]["date"]==null){
			$array2["domainlist"]["date"]=$array2["expressionlist"]["date"];
		}
		
		
			
		$date=date('d M y h:i',$array2["domainlist"]["date"]);
		
		if(preg_match("#personal-categories\/(.+)#",$category,$re)){
			$category="{personal}: {$re[1]}";
		}
		
		if(preg_match("#web-filter-plus\/BL\/(.+)#",$category,$re)){
			$category="{professional}: {$re[1]}";
		}	

		if(preg_match("#blacklist-artica\/(.+)#",$category,$re)){
			$category="{artica_community}: {$re[1]}";
		}			
		
		$html=$html."
		<tr ". CellRollOver().">
			<td style='font-size:14px' nowrap><strong>$category</strong></td>
			<td style='font-size:14px'  align='right' width=1%><strong>{$array2["domainlist"]["linesn"]}</strong></td>
			<td style='font-size:14px'  align='right' width=1%>{$array2["urllist"]["linesn"]}</td>
			<td style='font-size:14px'  align='right' width=1%>{$array2["expressionlist"]["linesn"]}</td>
			<td style='font-size:14px'  align='right' width=1% nowrap>$date</td>
			<td style='font-size:14px'  align='right' width=1%>{$array2["domainlist"]["size"]}</td>
		</TR>
		
		";		
		
	}
	
	$html=$html."</table></div>";
	
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');			
	
}

function squidguard_extract_category($path){
	$path=str_replace("/var/lib/squidguard/",'',$path);
	
	$path=dirname($path);
	
	return $path;
}


function popup(){
	
	$sock=new sockets();
	if($sock->GET_INFO('squidGuardEnabled')==1){popup_squidguard();exit;}
	
	
	$tpl=new templates();
	$datas=unserialize(base64_decode(@file_get_contents("ressources/logs/dansguardian.patterns")));

	if(!is_array($datas)){
		echo "<center><img src='img/database-urls-error-256.png'></center>";
		exit;
	}
	$html="
	<div style='font-size:14px;'>{DANSGUARDIAN_DB_STATUS_EXPLAIN}</div>
	<div style='width:100%;height:350px;overflow:auto'>
	<table style='width:100%'>
		<tr>
		<th style='font-size:14px' nowrap>{category}</th>
		<th style='font-size:14px' nowrap>{domains}</th>
		<th style='font-size:14px' nowrap>{urls}</th>
		<th style='font-size:14px' nowrap>{pattern_date}</th>
	</tr>";
	
	//javascript:Loadjs('squidguard.status.php?compile=yes')
	
	
	while (list ($num, $array) = each ($datas) ){
		
		$Y=date('Y');
		$d=date('Y-m-d');
		$array[2]=str_replace($d,"{today}",$array[2]);
		$array[2]=str_replace("$Y-","",$array[2]);
		
		
		
		$html=$html."
		<tr ". CellRollOver().">
			<td style='font-size:14px'><strong>$num</strong></td>
			<td style='font-size:14px'  align='right' width=1%>{$array[0]}</td>
			<td style='font-size:14px'  align='right' width=1%>{$array[1]}</td>
			<td style='font-size:14px'  align='right' width=1% nowrap>{$array[2]}</td>
		</TR>
		
		";
		
		
	}
	
	$html=$html."</table></div>";
	
	
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
	
	
	
}
	
?>
