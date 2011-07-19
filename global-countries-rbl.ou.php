<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.renattach.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["rbl-list"])){LoadRblist($_GET["rbl-list"]);exit;}
if(isset($_GET["EditRbl"])){EditRbl();exit;}
if(isset($_GET["rbl_server"])){Add_rbl_server();exit;}
if(isset($_GET["rbl_action"])){EditRblAction();exit;}
if(isset($_GET["RblDelete"])){RblDelete();exit;}

INDEX();


function INDEX(){
	if(!isset($_GET["ou"])){header('location:domains.index.php');exit;}
	$page=CurrentPageName();
	
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$hash=$ldap->OUDatas($_GET["ou"]);

	$rbl=RblForm();
	
	$array=array("delete"=>"{delete_mail}","quarantine"=>"{quarantine}","pass"=>"{pass}");
	$action=Field_array_Hash($array,'action',$hash["RblServersAction"]);
	$form="<table style='width:60%'>
	<td align='right' nowrap><strong>{action_100_pourc}:</strong>
	<td>$action</td>
	<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EditActionRbl();\"></td>
	</tr>
	<tr>
	<td align='right'><strong>{rbl_servers}:</strong></td>
	<td>$rbl<br><strong>{related} <a href='http://spamlinks.net/filter-dnsbl-lists.htm#spamsource' target='_new'>http://spamlinks.net</a> </strong></td>
	<td align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddRblServer();\"></td>
	</tr>
	</table>
	
	";
	
	$form=RoundedLightGreen($form);
	
	
	$html="
	<input type='hidden' name='ou' value='$ou' id='ou'>
	<p class='caption'>{rbl_explain}</p>
	$form
	<div id='rbl-list'></div>
	
	
	<script>LoadAjax('rbl-list','$page?rbl-list=$ou');</script>
	";
		
		
		
	
	
$cfg["JS"][]="js/denycountries.ou.js";
$tpl=new template_users('{rbl_check}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}

function RblForm(){
	
$datas=file_get_contents('ressources/dnsrbl.db');
$tb=explode("\n",$datas);

while (list ($num, $ligne) = each ($tb) ){
	if($ligne<>null){
		$tr=explode(':',$ligne);
		$array[$tr[1]]="{$tr[1]} ({$tr[0]})";
	}
}	
	
	$array[""]="{select}";
	ksort($array);
	return Field_array_Hash($array,'rbl_server',null);
	
}

function LoadRblist($ou){
	$ldap=new clladp();
	$oudat=$ldap->OUDatas($ou);
	if(!is_array($oudat["RblServers"])){echo "&nbsp;";exit;}
	$cell=CellRollOver() . " style='font-size:12px'";
	$html="
	<H5>{rbl_servers}</H5>
	<table style='width:60%'>";
	while (list ($num, $val) = each ($oudat["RblServers"]) ){
		if(strpos($val,':')>0){
			$tb=explode(":",$val);
			$rbl=$tb[0];
			$purc=$tb[1];
			$count=$count + $purc;
			
		}else{$country=$val;$action="delete";}
			$html=$html . "
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td $cell><strong>$rbl</strong></td>
			<td $cell><strong>" . Field_text("$num",$purc,'width:50px',null,'EditRblServer(this)')."</strong></td>
			<td $cell width=1%>" . imgtootltip('x.gif','{delete}',"RblDelete($num)") . "</td>
			</tr>";
		}
		$html=$html."<tr><td colspan=4 align='right' style='color:#005447;font-size:12px;border-top:1px solid black;padding:5px'><strong>$count%</strong></td></tr>";
		$tpl=new templates();
		echo "<br>" . RoundedLightGrey($tpl->_ENGINE_parse_body($html . "</table>"));
}
function Add_rbl_server(){
	$ldap=new clladp();
	$rbl=$_GET["rbl_server"];
	$ou=$_GET["ou"];
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$upd["RblServers"]="$rbl:0";
	if(!$ldap->Ldap_add_mod($dn,$upd)){
		echo $ldap->ldap_last_error;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
	}
	}
	
function EditRbl(){
	$ldap=new clladp();
	$oudat=$ldap->OUDatas($_GET["ou"]);
	if(!is_numeric($_GET["pourc"])){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{ERROR_INCORRECT_DATA}');
		exit;}
	if(!is_array($oudat["RblServers"])){exit;}
	
while (list ($num, $val) = each ($oudat["RblServers"]) ){$upd["RblServers"][]=$val;}



$value=$upd["RblServers"][$_GET["EditRbl"]];
$tb=explode(":",$value);
$upd["RblServers"][$_GET["EditRbl"]]="{$tb[0]}:{$_GET["pourc"]}";
$dn="ou={$_GET["ou"]},$ldap->suffix";

while (list ($num, $val) = each ($upd["RblServers"]) ){	
		if(strpos($val,':')>0){
			$tb=explode(":",$val);
			$rbl=$tb[0];
			$purc=$tb[1];
			$count=$count + $purc;
		}
}
reset($upd["RblServers"]);
$ldap->Ldap_modify($dn,$upd);
	
}

function EditRblAction(){
	$ldap=new clladp();
	$action=$_GET["rbl_action"];
	$ou=$_GET["ou"];
	$upd["RblServersAction"][0]=$action;
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
if(!$ldap->Ldap_modify($dn,$upd)){
	echo $ldap->ldap_last_error;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
	}
}
	
function RblDelete(){
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$hash=$ldap->OUDatas($ou);
	$upd["RblServers"]=$hash["RblServers"][$_GET["RblDelete"]];
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->Ldap_del_mod($dn,$upd)){
echo $ldap->ldap_last_error;
	}	
}

?>

