<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.renattach.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["rbl-list"])){LoadRblist($_GET["rbl-list"]);exit;}
if(isset($_GET["EditRbl"])){EditRbl();exit;}
if(isset($_GET["surbl_server"])){Add_surbl_server();exit;}
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
	$action=Field_array_Hash($array,'action',$hash["SURBLServersAction"]);
	$form="<table style='width:60%'>
	<td align='right' nowrap><strong>{action_100_pourc}:</strong>
	<td>$action</td>
	<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EditActionSURbl();\"></td>
	</tr>
	<tr>
	<td align='right'><strong>{rbl_servers}:</strong></td>
	<td>$rbl</td>
	<td align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddSurblServer();\"></td>
	</tr>
	</table>
	
	";
	
	$form=RoundedLightGreen($form);
	
	
	$html="
	<input type='hidden' name='ou' value='$ou' id='ou'>
	<p class='caption'>{surbl_explain}</p>
	$form
	<div id='rbl-list'></div>
	
	
	<script>LoadAjax('rbl-list','$page?rbl-list=$ou');</script>
	";
		
		
		
	
	
$cfg["JS"][]="js/denycountries.ou.js";
$tpl=new template_users('{surbl_rules}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}

function LoadRblist($ou){
	$ldap=new clladp();
	$oudat=$ldap->OUDatas($ou);
	if(!is_array($oudat["SURBLServers"])){echo "&nbsp;";exit;}
	$cell=CellRollOver() . " style='font-size:12px'";
	$serverlist=RblForm(1);
	$html="
	<H5>{rbl_servers}</H5>
	<table style='width:90%'>";
	while (list ($num, $val) = each ($oudat["SURBLServers"]) ){
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
			<td $cell width=1%'><img src='img/i16.gif'></td>
			<td $cell><strong><a href='{$serverlist["$rbl"]["uri"]}' target='_new'>{$serverlist["$rbl"]["name"]}</a></strong></td>
			<td $cell><strong>" . Field_text("$num",$purc,'width:50px',null,'EditSurblRblServer(this)')."</strong></td>
			<td $cell width=1%>" . imgtootltip('x.gif','{delete}',"SURblDelete($num)") . "</td>
			</tr>";
		}
		$html=$html."<tr><td colspan=4 align='right' style='color:#005447;font-size:12px;border-top:1px solid black;padding:5px'><strong>$count%</strong></td></tr>";
		$tpl=new templates();
		echo "<br>" . RoundedLightGrey($tpl->_ENGINE_parse_body($html . "</table>"));
}
function Add_surbl_server(){
	$ldap=new clladp();
	$rbl=$_GET["surbl_server"];
	$ou=$_GET["ou"];
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$upd["SURBLServers"]="$rbl:0";
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
	if(!is_array($oudat["SURBLServers"])){exit;}
	
while (list ($num, $val) = each ($oudat["SURBLServers"]) ){$upd["SURBLServers"][]=$val;}



$value=$upd["SURBLServers"][$_GET["EditRbl"]];
$tb=explode(":",$value);
$upd["SURBLServers"][$_GET["EditRbl"]]="{$tb[0]}:{$_GET["pourc"]}";
$dn="ou={$_GET["ou"]},$ldap->suffix";

while (list ($num, $val) = each ($upd["SURBLServers"]) ){	
		if(strpos($val,':')>0){
			$tb=explode(":",$val);
			$rbl=$tb[0];
			$purc=$tb[1];
			$count=$count + $purc;
		}
}
reset($upd["SURBLServers"]);
$ldap->Ldap_modify($dn,$upd);
	
}

function EditRblAction(){
	$ldap=new clladp();
	$action=$_GET["rbl_action"];
	$ou=$_GET["ou"];
	$upd["SURBLServersAction"][0]=$action;
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
	$upd["SURBLServers"]=$hash["SURBLServers"][$_GET["SURBLServers"]];
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->Ldap_del_mod($dn,$upd)){echo $ldap->ldap_last_error;}	
}

function RblForm($return_array=0){
	$data=file_get_contents('ressources/databases/db.surbl.txt');
	if(preg_match_all("#<server>(.+?)</server>#is",$data,$TABLE)){
		while (list ($num, $val) = each ($TABLE[1]) ){	
			if(preg_match("#<item>(.+)</item>#",$val,$A)){$item=$A[1];}
			if(preg_match("#<name>(.+)</name>#",$val,$A)){$name=$A[1];}			
			if(preg_match("#<uri>(.+)</uri>#",$val,$A)){$uri=$A[1];}				
			$array2[$item]=array("name"=>$name,"uri"=>$uri);
			$array[$item]=$name;
		}
		
		
		$array[""]="{select}";
	}
	
	if($return_array==1){return $array2;}
	return Field_array_Hash($array,'surbl_server',null);	
	
}

?>

