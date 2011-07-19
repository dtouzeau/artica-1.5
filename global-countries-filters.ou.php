<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.renattach.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["LoadDenyCountries"])){LoadDenyCountries($_GET["LoadDenyCountries"]);exit;}
if(isset($_GET["AddDenyCountry"])){AddDenyCountry();exit;}
if(isset($_GET["CountryDelete"])){CountryDelete();exit;}

INDEX();


function INDEX(){
	if(!isset($_GET["ou"])){header('location:domains.index.php');exit;}
	$page=CurrentPageName();
	
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$country=CountriesList();
	$array=array("delete"=>"{delete_mail}","quarantine"=>"{quarantine}");
	$action=Field_array_Hash($array,'action',null);
	$form="<table style='width:60%'>
	
	<tr>
	<td align='right'><strong>{countries}:</strong></td>
	<td>$country</td>
	</tr>
	<td align='right'><strong>{action}:</strong>
	<td>$action</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddDenyCountry();\"></td>
	</tr>
	</table>
	
	";
	
	$form=RoundedLightGreen($form);
	
	
	$html="
	<input type='hidden' name='ou' value='$ou' id='ou'>
	<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/bg_deny-country.jpg'></td>
	<td width=99% valign='top'><p class='caption'>{deny_country_explain}</caption></td>
	</tr>
	</table>
$form
	<div id='CountryList'></div>
	
	
	<script>LoadAjax('CountryList','$page?LoadDenyCountries=$ou');</script>
	";
		
		
		
	
	
$cfg["JS"][]="js/denycountries.ou.js";
$tpl=new template_users('{deny_countries}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}

function CountriesList(){
	
	$datas=explode("\n",file_get_contents('ressources/databases/db.countries.txt'));
	while (list ($num, $val) = each ($datas) ){
		if($val<>null){$arr[$val]=$val;}
		
	}
	$arr[""]='{select}';
	return Field_array_Hash($arr,'country_selected',null);
	
}

function LoadDenyCountries($ou){
	$ldap=new clladp();
	$oudat=$ldap->OUDatas($ou);
	if(!is_array($oudat["CountryDeny"])){echo "&nbsp;";exit;}
	$cell=CellRollOver() . " style='font-size:12px'";
	$html="
	<H5>{countries_deny_list}</H5>
	<table style='width:60%'>";
	while (list ($num, $val) = each ($oudat["CountryDeny"]) ){
		if(strpos($val,':')>0){
			$tb=explode(":",$val);
			$country=$tb[0];
			$action=$tb[1];
			
		}else{$country=$val;$action="delete";}
			$html=$html . "
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td $cell><strong>$country</strong></td>
			<td $cell><strong>$action</strong></td>
			<td $cell width=1%>" . imgtootltip('x.gif','{delete}',"CountryDelete($num)") . "</td>
			</tr>";
		}
		
		$tpl=new templates();
		echo "<br>" . RoundedLightGrey($tpl->_ENGINE_parse_body($html . "</table>"));
}
function AddDenyCountry(){
	$ldap=new clladp();
	$country=$_GET["AddDenyCountry"];
	$ou=$_GET["ou"];
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$upd["CountryDeny"]="$country:{$_GET["action"]}";
	if(!$ldap->Ldap_add_mod($dn,$upd)){
		echo $ldap->ldap_last_error;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
	}
	
	
}
function CountryDelete(){
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$hash=$ldap->OUDatas($ou);
	$upd["CountryDeny"]=$hash["CountryDeny"][$_GET["CountryDelete"]];
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->Ldap_del_mod($dn,$upd)){
echo $ldap->ldap_last_error;
	}	
}

?>

