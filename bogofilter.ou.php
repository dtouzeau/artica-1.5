<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["bogospam_user"])){bogo_add_spam_user();exit;}
if(isset($_GET["GetRobots"])){echo BogoRobots();exit;}
if(isset($_GET["bogospam_action"])){bogospam_action();exit;}
if(isset($_GET["DeleteRobot"])){DeleteRobot();exit;}

INDEX();


function INDEX(){
	
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$Hash_datas=$ldap->OUDatas($ou);
	
	$h_type=array("spam"=>"spam","ham"=>"ham");
	$domains1=$ldap->hash_get_domains_ou($ou);
	$domainsF1=Field_array_Hash($domains1,'bogo_spam_domain',null,null,null,0,'width:150px');
	$type=Field_array_Hash($h_type,'bogo_type',null,null,null,0,'width:100px');
	$hash_action=array("delete"=>"{bogo_delete}","quarantine"=>"{bogo_quarantine}","prepend"=>"{bogo_prepend}");
	
	$form_email="
	<input type='hidden' id='ou' value='$ou'>
	<H5>{bogo_robots}</H5>
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap><strong>{add_bogo_spam}:</strong></td>
	<td>" . Field_text('bogo_spam') . "</td>
	<td>$domainsF1</td>
	<td>$type</td>
	<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:bogoAddSpamUser();\"></td>
	</tr>
	</table>
	
	";
	
	for($i=1;$i<10;$i++){
		$h_level[$i.'0']=$i.'0 %';
	}
	
	
	$actions_datas=explode(';',$Hash_datas["BogoFilterAction"]);
	
	$action="
	<H5>{spam_action}</H5>
	{spam_action_text} " . Field_array_Hash($h_level,'exceed',$actions_datas[0],null,null,0,'width:60px')." {then} " . Field_array_Hash($hash_action,'action',$actions_datas[1],null,null,0,'width:160px') . "<br><br>
	{if_bogo_prepend} " . Field_text('bogo_prepend',$actions_datas[2],'width:150px') . "
	<div style='width:100%;text-align:right'><input type='button' value='{edit}&nbsp;&raquo;&raquo;' OnClick=\"javascript:BogoFilterAction();\"></div>
	
	";
	
	
	
	
	
	$area_robots="<div id='robots'></div><script>LoadAjax('robots','$page?GetRobots=$ou')</script>";	
	$area_robots=RoundedLightGrey($area_robots);
	$action=RoundedLightGrey($action);
	
	
	$form_email=RoundedLightGrey($form_email);
	
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/bg_bogofilter.jpg'></td>
	<td valign='top' width=99%>" . RoundedLightGreen("{bogo_intro}")."</td>
	</tr>
	</table>
	$form_email
	<br>
	$action
	<br>
	$area_robots
	";
	
	
	
$cfg["JS"][]="js/bogofilter.js";
$tpl=new template_users('{APP_BOGOFILTER}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}
function bogo_add_spam_user(){
	$ou=$_GET["ou"];
	$bogospam_user=$_GET["bogospam_user"];
	$bogospam_domain=$_GET["bogospam_domain"];
	$bogospam_type=$_GET["bogospam_type"];
	$userid=$bogospam_user;
	$password="NOPASS";
	$group_id=0;
	$email="$bogospam_user@$bogospam_domain";
	$tpl=new templates();
	$userid=str_replace(" ",".",$userid);
	
	$ldap=new clladp();
	$dn="cn=$userid,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
	$upd["cn"][0]=$userid;
	$upd["ObjectClass"][0]='top';
	$upd["ObjectClass"][1]='userAccount';
	$upd["ObjectClass"][2]='organizationalPerson';
	$upd["ObjectClass"][3]='ArticaBogoFilterAdmin';
	$upd["uid"][0]=$userid;
	$upd["accountActive"][0]="TRUE";
	$upd["mail"][0]="$email";
	$upd["accountGroup"][0]=$group_id;
	$upd["domainName"][0]=$bogospam_domain;
	$upd["homeDirectory"][0]="/home/$userid";
	$upd["mailDir"][0]="/home/$userid/mail";
	$upd["sn"][0]=$userid;
	$upd["displayName"][0]=$userid . " bogofilter robot";
	$upd["userPassword"][0]=$password;
	$upd["BogoFilterMailType"][0]="$bogospam_type";
	
	if(!$ldap->ldap_add($dn,$upd)){echo "ERROR: $ldap->ldap_last_error";exit;}
	}
	
}


function BogoRobots(){
	
	$ou=$_GET["GetRobots"];
	$ldap=new clladp();
		$filters=array("uid","BogoFilterMailType","mail");
			$dr =ldap_search($ldap->ldap_connection,"ou=$ou,dc=organizations,$ldap->suffix","(&(objectClass=ArticaBogoFilterAdmin)(BogoFilterMailType=*))",$filters);	
			
			if($dr){
				$results = ldap_get_entries($ldap->ldap_connection,$dr);
				for($i=0;$i<$results["count"];$i++){
					
					$hash[$results[$i]["uid"][0]]=array("MAIL"=>$results[$i]["mail"][0],"TYPE"=>$results[$i][strtolower("BogoFilterMailType")][0]);
					
				}
			
			}else{echo "<span></span>";}	
	
	$html="<table style='width:100%'>
	<tr style='background-color:#CCCCCC'>
	<td>&nbsp;</td>
	<td><strong>{robot}</strong></td>
	<td><strong align='center'><center>{type}</center></strong></td>
	<td><strong>&nbsp;</strong></td>
	</tr>
	";		
if(is_array($hash)){
	while (list ($num, $line) = each ($hash) ){
		$html=$html . "<tr>
		<td " . CellRollOver() . " width=1%><img src='img/fw_bold.gif'></td>
		<td " . CellRollOver() . ">{$line["MAIL"]}</td>
		<td " . CellRollOver() . " width=20% align='center'>{bogo_{$line["TYPE"]}}</td>
		<td " . CellRollOver() . " width=1%>" . imgtootltip('x.gif','{delete}',"DeleteRobot('$num','$ou')")."</td>
		</tr>";
		}
}
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html . "</table>");

}

function bogospam_action(){
	$ldap=new clladp();
	$tpl=new templates();
	$upd["BogoFilterAction"]="{$_GET["exceed"]};{$_GET["bogospam_action"]};{$_GET["prepend"]}";
	$dn="ou={$_GET["ou"]},$ldap->suffix";
	if(!$ldap->Ldap_modify($dn,$upd)){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);}else{echo $tpl->_ENGINE_parse_body('{success}');}
}
function DeleteRobot(){
	$ldap=new clladp();	
	$H=$ldap->UserDatas($_GET["DeleteRobot"]);
	$dn=$H["dn"];
	$ldap->ldap_delete($dn,true);
	
	
}
