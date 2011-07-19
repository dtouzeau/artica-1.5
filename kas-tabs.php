<?php

if(isset($_GET["kaspages"])){kaspages();}

function global_kastabs(){
	if(!isset($_GET["kaspages"])){
		
		if(isset($_GET["kasSelectedpage"])){$kaspages=$_GET["kasSelectedpage"];}else{
		$kaspages="antispam_rules";};
	}else{
		$kaspages=$_GET["kaspages"];
	}
	
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["antispam_engine"]='{antispam_engine}';
	$array["antispam_rules"]='{antispam_rules}';
	$array["statuslicense"]='{statusandlicense}';

	

	while (list ($num, $ligne) = each ($array) ){
		if($kaspages==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('global_kas_pages','kas-tabs.php?kaspages=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";		
}


function kaspages(){
	
	switch ($_GET["kaspages"]) {
		case "antispam_rules":header("location:kas.group.rules.php?ajax-pop=yes&nodiv=yes&kasSelectedpage={$_GET["kaspages"]}");break;
		case "antispam_engine":header("location:kas.engine.settings.php?ajax-pop=yes&nodiv=yes&kasSelectedpage={$_GET["kaspages"]}");break;
		case "statuslicense":header("location:kas.licence.settings.php?ajax-pop=yes&nodiv=yes&kasSelectedpage={$_GET["kaspages"]}");break;
		case "actions":header("location:kas.group.rules.php?ajax-pop=yes&nodiv=yes&kasSelectedpage={$_GET["kaspages"]}");break;
	
		default:
			break;
	}
}


?>