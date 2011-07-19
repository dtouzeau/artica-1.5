<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");

if(isset($_GET["PostfixAddFallBackServer"])){PostfixAddFallBackServer();exit;}
if(isset($_GET["PostfixAddFallBackerserverSave"])){PostfixAddFallBackerserverSave();exit;}
if(isset($_GET["PostfixAddFallBackerserverLoad"])){echo PostfixAddFallBackerserverList();exit;}
if(isset($_GET["PostfixAddFallBackerserverDelete"])){PostfixAddFallBackerserverDelete();exit;}
if(isset($_GET["PostfixAddFallBackServerMove"])){PostfixAddFallBackServerMove();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
js();

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{smtp_fallback_relay}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
$js_add=file_get_contents("js/postfix-fallback.js");	

	
$html="function {$prefix}Loadpage(){
	YahooWin2('650','$page?popup-index=yes','$title');
	}

	$js_add
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}

function popup_index(){

$html="
<table style='width:600px' align=center>
<td valign='top' style='text-align:justify'>" . RoundedLightWhite("<p class=caption>{smtp_fallback_relay_tiny}<br>{smtp_fallback_relay_text}</p>")."</td>
</tr>
</table>
<table style='width:600px' align=center>
<tr>
<td align='center'><input type='button' value='{add_server}&nbsp;&raquo;' OnClick=\"javascript:PostfixAddFallBackServer();\"></td></tr>
<tr>
<td>" . RoundedLightWhite("<div id='table_list'>".PostfixAddFallBackerserverList() . "</div>")."</td></tr>
</table>
";

$js["JS"][]='js/postfix-fallback.js';
$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html);
}


function PostfixAddFallBackServer(){
	$ldap=new clladp();
	
	if($_GET["domainName"]<>null){
		$main=new main_cf();
		$tool=new DomainsTools();
		$arr=explode(',',$main->main_array["smtp_fallback_relay"]);
		if(is_array($arr)){
			$array=$tool->transport_maps_explode($arr[$_GET["domainName"]]);
			$relay_address=$array[1];
			$smtp_port=$array[2];
			$MX_lookup=$array[3];
			$hidden="<input type='hidden' name='TableIndex' value='{$_GET["domainName"]}'>";
		}
	}
	
	if($smtp_port==null){$smtp_port=25;}
	if($MX_lookup==null){$MX_lookup='yes';}
	
	$html="<div style='padding:20px'>
	<H3>{add_server}</H3>
	<p>&nbsp;</p>
	<form name='FFM3'>
	$hidden
	<input type='hidden' name='PostfixAddFallBackerserverSave' value='yes'>
	<table style='width:100%'>
	<td align='right' nowrap><strong>{relay_address}:</strong></td>
	<td>" . Field_text('relay_address',$relay_address) . "</td>	
	</tr>
	</tr>
	<td align='right' nowrap><strong>{smtp_port}:</strong></td>
	<td>" . Field_text('relay_port',$smtp_port) . "</td>	
	</tr>	
	<tr>
	<td align='right' nowrap>" . Field_yesno_checkbox_img('MX_lookups',$MX_lookup,'{enable_disable}')."</td>
	<td>{MX_lookups}</td>	
	</tr>

	<tr>
	<td align='right' class=caption colspan=2><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:XHRPostfixAddFallBackerserverSave();\"></td>
	</tr>		
	<tr>
	<td align='left' class=caption colspan=2><strong>{MX_lookups}</strong><br>{MX_lookups_text}</td>
	</tr>			
		
	</table>
	</FORM>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function PostfixAddFallBackerserverSave(){
	$relay_address=$_GET["relay_address"];
	$tpl=new templates();
	
	if($relay_address==null){echo $tpl->_ENGINE_parse_body('{error_give_server}');return null;}
	
	$smtp_port=$_GET["relay_port"];
	$MX_lookups=$_GET["MX_lookups"];
	
	writelogs("Edit $relay_address $smtp_port $MX_lookups tool->transport_maps_implode($relay_address,$smtp_port,null,$MX_lookups)",__FUNCTION__,__FILE__);
	
	$tool=new DomainsTools();
	$line=$tool->transport_maps_implode($relay_address,$smtp_port,null,$MX_lookups);
	$line=str_replace("smtp:",'',$line);
	$main=new main_cf();
	$arr=explode(',',$main->main_array["smtp_fallback_relay"]);
	
	
	if(isset($_GET["TableIndex"])){
		writelogs("Edit " . $arr[$_GET["TableIndex"]] . " to " . $line,__FUNCTION__,__FILE__);
		$arr[$_GET["TableIndex"]]=$line;
	}
	
	
	if(is_array($arr)){
		while (list ($index, $ligne) = each ($arr) ){
				if($ligne<>null){$array[]=$ligne;}
			}
		}

	if(!isset($_GET["TableIndex"])){$array[]=$line;}
	$main->main_array["smtp_fallback_relay"]=implode(",",$array);
	$main->save_conf();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}

function PostfixAddFallBackerserverList(){
	$main=new main_cf();
	$hash=explode(',',$main->main_array["smtp_fallback_relay"]);
	$tool=new DomainsTools();
$html="<center>
<table style='width:458px;padding:5px;border:1px dotted #8E8785;' align='center'>
	<tr style='background-color:#CCCCCC'>
		<td>&nbsp;</td>
		<td><strong>{relay_address}</strong></td>
		<td align='center'><strong>{smtp_port}</strong></td>
		<td align='center'><strong>{MX_lookups}</strong></td>
		<td><strong>-</strong></td>
		<td align='center'><strong>-</strong></td>
		<td align='center'><strong-</strong></td>
	</tr>";
	
	if(is_array($hash)){
while (list ($index, $ligne) = each ($hash) ){
		if($ligne<>null){
		$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"PostfixAddFallBackServerMove('$index','up')") ."</td>";
		$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"PostfixAddFallBackServerMove('$index','down')") ."</td>";			
		$arr=$tool->transport_maps_explode("smtp:$ligne");
		$html=$html . "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code><a href=\"javascript:PostfixAddFallBackServer('$index');\">{$arr[1]}</a></code></td>
		<td align='center'><code>{$arr[2]}</code></td>
		<td align='center'><code>{$arr[3]}</code></td>
		$cell_up
		$cell_down
		<td align='center' width=1%>" . imgtootltip("x.gif",'{delete}',"PostfixAddFallBackerserverDelete('$index')")."</td>
		</tr>";
		}
	}
}
$html=$html . "</table></center>";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);		
}
function PostfixAddFallBackerserverDelete(){
		$main=new main_cf();
		$arr=explode(',',$main->main_array["smtp_fallback_relay"]);
		if(is_array($arr)){
			unset($arr[$_GET["PostfixAddFallBackerserverDelete"]]);
		}
	$main->main_array["smtp_fallback_relay"]=implode(",",$arr);
	$main->save_conf();	
}
function PostfixAddFallBackServerMove(){
	$main=new main_cf();
	$hash=explode(',',$main->main_array["smtp_fallback_relay"]);	
	$newarray=array_move_element($hash,$hash[$_GET["PostfixAddFallBackServerMove"]],$_GET["move"]);
	$main->main_array["smtp_fallback_relay"]=implode(",",$newarray);
	$main->save_conf();		
}



?>