<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/kav4mailservers.inc');
	$tpl=new template_users();
	
	$user=new usersMenus();
	if($user->AllowChangeKav==false){echo$tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");exit;}
	
	if(isset($_GET["InfectedQuarantine"])){saveDatas();exit;}
	if(isset($_GET["FilterByName_save"])){FilterByName_save();exit;}
	if(isset($_GET["FilterByName_load"])){FilterByName_load();exit;}
	if(isset($_GET["FilterByName_delete"])){FilterByName_delete();exit;}
	if(isset($_GET["PostUpdateCmd"])){PostUpdateCmd();exit;}
	if(isset($_GET["body_template"])){SaveTemplate();exit;}
	if(isset($_GET["LoadAvNotify"])){LoadAvNotify();exit;}
	
	
	if($_GET["tab"]==1){FilterByName();exit;}
	if($_GET["tab"]==2){ReadIniDatas();exit;}
kav_page();
function kav_page(){

	$gidNumber=$_SESSION["privileges"]["gidNumber"];
	if(preg_match('#kav:([0-9]+)#',$_GET["TreeKasSelect"],$reg)){$gidNumber=$reg[1];}
		
	
	
	$ldap=new clladp();
	$HashGroup=$ldap->GroupDatas($gidNumber);
	$ini=new Bs_IniHandler();
	$ini->loadString($HashGroup["KasperkyAVScanningDatas"]);
	$hash=$ini->_params["smtpscan.group:$gidNumber"];
	
	$CuredQuarantine=Field_yesno_checkbox_img("CuredQuarantine",$hash["CuredQuarantine"]);
	$InfectedQuarantine=Field_yesno_checkbox_img("InfectedQuarantine",$hash["InfectedQuarantine"]);
	$SuspiciousQuarantine=Field_yesno_checkbox_img("SuspiciousQuarantine",$hash["SuspiciousQuarantine"]);
	$CorruptedQuarantine=Field_yesno_checkbox_img("CorruptedQuarantine",$hash["CorruptedQuarantine"]);
	$WarningQuarantine=Field_yesno_checkbox_img("WarningQuarantine",$hash["WarningQuarantine"]);
	$FilteredQuarantine=Field_yesno_checkbox_img("FilteredQuarantine",$hash["FilteredQuarantine"]);
	$ErrorQuarantine=Field_yesno_checkbox_img("ErrorQuarantine",$hash["ErrorQuarantine"]);	
	$ProtectedQuarantine=Field_yesno_checkbox_img("ProtectedQuarantine",$hash["ProtectedQuarantine"]);
	
	$CuredAdminNotify=Field_yesno_checkbox_img("CuredAdminNotify",$hash["CuredAdminNotify"]);
	$InfectedAdminNotify=Field_yesno_checkbox_img("InfectedAdminNotify",$hash["InfectedAdminNotify"]);
	$SuspiciousAdminNotify=Field_yesno_checkbox_img("SuspiciousAdminNotify",$hash["SuspiciousAdminNotify"]);
	$CorruptedAdminNotify=Field_yesno_checkbox_img("CorruptedAdminNotify",$hash["CorruptedAdminNotify"]);
	$WarningAdminNotify=Field_yesno_checkbox_img("WarningAdminNotify",$hash["WarningAdminNotify"]);
	$FilteredAdminNotify=Field_yesno_checkbox_img("FilteredAdminNotify",$hash["FilteredAdminNotify"]);
	$ErrorAdminNotify=Field_yesno_checkbox_img("ErrorAdminNotify",$hash["ErrorAdminNotify"]);
	$ProtectedAdminNotify=Field_yesno_checkbox_img("ProtectedAdminNotify",$hash["ProtectedAdminNotify"]);	

	$arrayA=array("unchanged"=>"Unchanged","remove"=>"Remove");
	$arrayB=array("unchanged"=>"Unchanged","remove"=>"Remove","cured"=>"Cured");
	
	$CuredAdminAction=Field_array_Hash($arrayB,"CuredAdminAction",$hash["CuredAdminAction"]);
	$InfectedAdminAction=Field_array_Hash($arrayA,"InfectedAdminAction",$hash["InfectedAdminAction"]);
	$SuspiciousAdminAction=Field_array_Hash($arrayA,"SuspiciousAdminAction",$hash["SuspiciousAdminAction"]);
	$CorruptedAdminAction=Field_array_Hash($arrayA,"CorruptedAdminAction",$hash["CorruptedAdminAction"]);
	$WarningAdminAction=Field_array_Hash($arrayA,"WarningAdminAction",$hash["WarningAdminAction"]);
	$FilteredAdminAction=Field_array_Hash($arrayA,"FilteredAdminAction",$hash["FilteredAdminAction"]);
	$ErrorAdminAction=Field_array_Hash($arrayA,"ErrorAdminAction",$hash["ErrorAdminAction"]);
	$ProtectedAdminAction=Field_array_Hash($arrayA,"ProtectedAdminAction",$hash["ProtectedAdminAction"]);
	
	$CuredRecipientAction=Field_array_Hash($arrayB,"CuredRecipientAction",$hash["CuredRecipientAction"]);
	$InfectedRecipientAction=Field_array_Hash($arrayA,"InfectedRecipientAction",$hash["InfectedRecipientAction"]);
	$SuspiciousRecipientAction=Field_array_Hash($arrayA,"SuspiciousRecipientAction",$hash["SuspiciousRecipientAction"]);
	$CorruptedRecipientAction=Field_array_Hash($arrayA,"CorruptedRecipientAction",$hash["CorruptedRecipientAction"]);
	$WarningRecipientAction=Field_array_Hash($arrayA,"WarningRecipientAction",$hash["WarningRecipientAction"]);
	$FilteredRecipientAction=Field_array_Hash($arrayA,"FilteredRecipientAction",$hash["FilteredRecipientAction"]);
	$ErrorRecipientAction=Field_array_Hash($arrayA,"ErrorRecipientAction",$hash["ErrorRecipientAction"]);
	$ProtectedRecipientAction=Field_array_Hash($arrayA,"ProtectedRecipientAction",$hash["ProtectedRecipientAction"]);	

	$CuredSenderNotify=Field_yesno_checkbox_img("CuredSenderNotify",$hash["CuredSenderNotify"]);
	$InfectedSenderNotify=Field_yesno_checkbox_img("InfectedSenderNotify",$hash["InfectedSenderNotify"]);
	$SuspiciousSenderNotify=Field_yesno_checkbox_img("SuspiciousSenderNotify",$hash["SuspiciousSenderNotify"]);
	$CorruptedSenderNotify=Field_yesno_checkbox_img("CorruptedSenderNotify",$hash["CorruptedSenderNotify"]);
	$WarningSenderNotify=Field_yesno_checkbox_img("WarningSenderNotify",$hash["WarningSenderNotify"]);
	$FilteredSenderNotify=Field_yesno_checkbox_img("FilteredSenderNotify",$hash["FilteredSenderNotify"]);
	$ErrorSenderNotify=Field_yesno_checkbox_img("ErrorSenderNotify",$hash["ErrorSenderNotify"]);
	$ProtectedSenderNotify=Field_yesno_checkbox_img("ProtectedSenderNotify",$hash["ProtectedSenderNotify"]);

	$CuredRecipientNotify=Field_yesno_checkbox_img("CuredRecipientNotify",$hash["CuredRecipientNotify"]);
	$InfectedRecipientNotify=Field_yesno_checkbox_img("InfectedRecipientNotify",$hash["InfectedRecipientNotify"]);
	$SuspiciousRecipientNotify=Field_yesno_checkbox_img("SuspiciousRecipientNotify",$hash["SuspiciousRecipientNotify"]);
	$CorruptedRecipientNotify=Field_yesno_checkbox_img("CorruptedRecipientNotify",$hash["CorruptedRecipientNotify"]);
	$WarningRecipientNotify=Field_yesno_checkbox_img("WarningRecipientNotify",$hash["WarningRecipientNotify"]);
	$FilteredRecipientNotify=Field_yesno_checkbox_img("FilteredRecipientNotify",$hash["FilteredRecipientNotify"]);
	$ErrorRecipientNotify=Field_yesno_checkbox_img("ErrorRecipientNotify",$hash["ErrorRecipientNotify"]);
	$ProtectedRecipientNotify=Field_yesno_checkbox_img("ProtectedRecipientNotify",$hash["ProtectedRecipientNotify"]);

	$CuredRecipientAttachReport=Field_yesno_checkbox_img("CuredRecipientAttachReport",$hash["CuredRecipientAttachReport"]);
	$InfectedRecipientAttachReport=Field_yesno_checkbox_img("InfectedRecipientAttachReport",$hash["InfectedRecipientAttachReport"]);
	$SuspiciousRecipientAttachReport=Field_yesno_checkbox_img("SuspiciousRecipientAttachReport",$hash["SuspiciousRecipientAttachReport"]);
	$CorruptedRecipientAttachReport=Field_yesno_checkbox_img("CorruptedRecipientAttachReport",$hash["CorruptedRecipientAttachReport"]);
	$WarningRecipientAttachReport=Field_yesno_checkbox_img("WarningRecipientAttachReport",$hash["WarningRecipientAttachReport"]);
	$FilteredRecipientAttachReport=Field_yesno_checkbox_img("FilteredRecipientAttachReport",$hash["FilteredRecipientAttachReport"]);
	$ErrorRecipientAttachReport=Field_yesno_checkbox_img("ErrorRecipientAttachReport",$hash["ErrorRecipientAttachReport"]);
	$ProtectedRecipientAttachReport=Field_yesno_checkbox_img("ProtectedRecipientAttachReport",$hash["ProtectedRecipientAttachReport"]);		
	
	
	$Check=Field_yesno_checkbox_img("Check",$hash["Check"]);		
	$AddXHeaders=Field_yesno_checkbox_img("AddXHeaders",$hash["AddXHeaders"]);	
	if($hash["QuarantinePath"]<>null){
		$QuarantinePath=basename($hash["QuarantinePath"]);
		$QuarantinePath="
		<tr class=rowA>
			<td align='right'>{QuarantinePath}</td>
			<td>$QuarantinePath</td>
		</tr>";
			
	}
	
$Infected_row=texttooltip('{Infected}','{click_to_change_notif_content}',"LoadAvNotify('Infected')");
$Cured_row=texttooltip('{Cured}','{click_to_change_notif_content}',"LoadAvNotify('Cured')");
$Suspicious_row=texttooltip('{Suspicious}','{click_to_change_notif_content}',"LoadAvNotify('Suspicious')");	
$Corrupted_row=texttooltip('{Corrupted}','{click_to_change_notif_content}',"LoadAvNotify('Corrupted')");	
$Warning_row=texttooltip('{Warning}','{click_to_change_notif_content}',"LoadAvNotify('Warning')");
$Filtered_row=texttooltip('{Filtered}','{click_to_change_notif_content}',"LoadAvNotify('Filtered')");
$Error_row=texttooltip('{Error}','{click_to_change_notif_content}',"LoadAvNotify('Error')");
$Protected_row=texttooltip('{Protected}','{click_to_change_notif_content}',"LoadAvNotify('Protected')");







	$html=
	"<div style='float:left;margin-left:-120px;margin-top:-100px;'></div>".
	Tabs() . "<br><br>

	<form name='kasperskyactions'>	
	<input type='hidden' name='GidNumber' value='$gidNumber'>
	<table style='width:100%'>
	<tr>
	<td width='1%' valign='top'>&nbsp;</td>
	<td valign='top'>
	
	
		<h4>{LoadKasperskySettings_general_title}</H4>
				<table>
				<tr>
				
				<tr>
				<td align=right>{LoadKasperskySettings_adminaddr}:</td>
				<td><input type='text' name='AdminAddress' value='{$hash["AdminAddress"]}'></td>
				</tr>
				<tr>
				<td align=right>{LoadKasperskySettings_Check}:</td>
				<td>$Check</td>
				</tr>
				<tr>
				<td align=right>{LoadKasperskySettings_AddXHeaders}:</td>
				<td>$AddXHeaders</td>
				</tr>				
				$QuarantinePath
				</table>
	
	</tr>
	</table>
	
		<h4>{av_notify_rules}</H4>

	
<table>
<tr class=rowT>
	<td rowspan=2>{objects}</td>
	<td  rowspan=2>{Quarantine}</td>
	<td colspan=2>{Administrator_rules}</td>
	<td >{sender_rules}</td>
	<td colspan=3>{recipient_rules}</td>
</tr>
<tr class=rowT>
	<td >{notify}</td>
	<td>{actions}</td>
	<td >{notify}</td>
	<td >{notify}</td>
	<td>{attach_report}</td>
	<td>{actions}</td>
	
</tr>
<tr class=rowA align='right'>
	<td>$Infected_row</td>
	<td align='center' width=1%>$InfectedQuarantine</td>
	<td align='center' width=1%>$InfectedAdminNotify</td>
	<td align='center'  width=1%>$InfectedAdminAction</td>
	<td align='center' width=1%>$InfectedSenderNotify</td>
	<td align='center' width=1%>$InfectedRecipientNotify</td>
	<td align='center' width=1%>$InfectedRecipientAttachReport</td>
	<td align='center' >$InfectedRecipientAction</td>
	
</tr>
<tr class=rowB align='right'>
	<td>$Cured_row</td>
	<td align='center' width=1%>$CuredQuarantine</td>
	<td align='center' width=1%>$CuredAdminNotify</td>
	<td align='center'  width=1%>$CuredAdminAction</td>
	<td align='center' width=1%>$CuredSenderNotify</td>
	<td align='center' width=1%>$CuredRecipientNotify</td>
	<td align='center' width=1%>$CuredRecipientAttachReport</td>
	<td align='center' >$CuredRecipientAction</td>
</tr> 			  				 
<tr class=rowA align='right'>
	<td>$Suspicious_row</td>
	<td align='center' width=1%>$SuspiciousQuarantine</td>
	<td align='center' width=1%>$SuspiciousAdminNotify</td>
	<td align='center' >$SuspiciousAdminAction</td>
	<td align='center' width=1%>$SuspiciousSenderNotify</td>
	<td align='center' width=1%>$SuspiciousRecipientNotify</td>
	<td align='center' width=1%>$SuspiciousRecipientAttachReport</td>
	<td align='center' >$SuspiciousRecipientAction</td>
</tr>			  				 
<tr class=rowB align='right'>
	<td>$Corrupted_row</td>
	<td align='center' width=1%>$CorruptedQuarantine</td>	
	<td align='center' width=1%>$CorruptedAdminNotify</td>		
	<td align='center'  width=1%>$CorruptedAdminAction</td>		
	<td align='center' width=1%>$CorruptedSenderNotify</td>
	<td align='center' width=1%>$CorruptedRecipientNotify</td>
	<td align='center' width=1%>$CorruptedRecipientAttachReport</td>
	<td align='center' >$CorruptedRecipientAction</td>
</tr> 			  				 

<tr class=rowA align='right'>
	<td>$Warning_row</td>
	<td class=center width=1%>$WarningQuarantine</td>
	<td class=center width=1%>$WarningAdminNotify</td>	
	<td class=center width=1%>$WarningAdminAction</td>
	<td align='center' width=1%>$WarningSenderNotify</td>
	<td align='center' width=1%>$WarningRecipientNotify</td>
	<td align='center' width=1%>$WarningRecipientAttachReport</td>
	<td align='center' >$WarningRecipientAction</td>
</tr> 			  				 
<tr class=rowB align='right'>
	<td>$Filtered_row</td>
	<td align='center' width=1%>$FilteredQuarantine</td>
	<td align='center' width=1%>$FilteredAdminNotify</td>
	<td align='center'  width=1%>$FilteredAdminAction</td>
	<td align='center' width=1%>$FilteredSenderNotify</td>
	<td align='center' width=1%>$FilteredRecipientNotify</td>
	<td align='center' width=1%>$FilteredRecipientAttachReport</td>
	<td align='center' >$FilteredRecipientAction</td>
	
</tr> 			  				 
<tr class=rowA align='right'>
	<td>$Error_row</td>
	<td align='center' width=1%>$ErrorQuarantine</td>
	<td align='center' width=1%>$ErrorAdminNotify</td>
	<td align='center'  width=1%>$ErrorAdminAction</td>
	<td align='center' width=1%>$ErrorSenderNotify</td>
	<td align='center' width=1%>$ErrorRecipientNotify</td>
	<td align='center' width=1%>$ErrorRecipientAttachReport</td>
	<td align='center' >$ErrorRecipientAction</td>
</tr> 			  				 
<tr class=rowB align='right'>
	<td>$Protected_row</td>
	<td align='center' width=1%>$ProtectedQuarantine</td>
	<td align='center' width=1%>$ProtectedAdminNotify</td>
	<td align='center'  width=1%>$ProtectedAdminAction</td>
	<td align='center' width=1%>$ProtectedSenderNotify</td>
	<td align='center' width=1%>$ProtectedRecipientNotify</td>
	<td align='center' width=1%>$ProtectedRecipientAttachReport</td>
	<td align='center' >$ProtectedRecipientAction</td>
</tr>
</form>
<tr class=rowA align='right' >
	<td colspan=8><input type='button' value='{bt_edit_notify}' OnClick=\"javascript:EditKasperskySettings();\" style='float:right'></td></tr>
</table>
	
	";
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
echo DIV_SHADOW($html,'windows');	
}

function saveDatas(){
	$gidNumber=$_GET["GidNumber"];
	unset($_GET["GidNumber"]);
	
	$ldap=new clladp();
	$mailAliases=$ldap->GroupMailAliases($gidNumber);
	if(is_array($mailAliases)){$recipients=implode(',',$mailAliases);$recipients="Recipients=$recipients\n";}
	
	
	while (list ($num, $ligne) = each ($_GET) ){
			$ini=$ini."$num=$ligne\n";
			}

	$ini="[smtpscan.group:$gidNumber]\n$recipients$ini";

	$Hash=$ldap->GroupAllDatas($gidNumber);
	
	$updatearray["KasperkyAVScanningDatas"][0]=$ini;
	writelogs('Save Kaspersky settings in ' . $Hash["dn"],__FUNCTION__,__FILE__);	
	
	$Classes=$ldap->getobjectDNClass($Hash["dn"],1);
	if(!is_array_key('ArticaSettings',$Classes)){
		$array_class["objectClass"][]="ArticaSettings";
		writelogs('Add ArticaSettings objectClass to group' ,__FUNCTION__,__FILE__);	
		$ldap->Ldap_add_mod($Hash["dn"],$array_class);
		}	
	
	if(!is_array_key("KasperkyAVScanningDatas",$Hash)){
		writelogs('Add KasperkyAVScanningDatas object to group ' .$gidNumber  ,__FUNCTION__,__FILE__);	
		$ldap->Ldap_add_mod($Hash["dn"],$updatearray);
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
		
	}else{
		$ldap->Ldap_modify($Hash["dn"],$updatearray);
		writelogs('Modify KasperkyAVScanningDatas object to group '.$gidNumber ,__FUNCTION__,__FILE__);	
		if($ldap->ldap_last_error<>null){
			echo $ldap->ldap_last_error;exit;}
	}
	
$kav=new kav4mailservers();
$kav->Save();
	echo "OK";
	
	}
	
function FilterByName_save(){
	
	$ext=strtolower($_GET["FilterByName_save"]);
	if(!preg_match('#\*\.([a-z]+)#',$ext)){
		if(preg_match('#^\.([a-z]+)#',$ext)){$ext='*' . $ext;}
		if(preg_match('#^([a-z]+)#',$ext)){$ext='*.' . $ext;}
	}
	$ldap=new clladp();
	if(preg_match('#kav:([0-9]+)#',$_GET["TreeKasSelect"],$reg)){$gidNumber=$reg[1];}
	
	$HashGroup=$ldap->GroupDatas($gidNumber);
	$kav4mailservers=new kav4mailservers(1,$HashGroup["KasperkyAVScanningDatas"]);
	$hashKAV=$kav4mailservers->loadAvSettingsDomain($gidNumber);	
	$tbl=explode(',',$hashKAV["FilterByName"]);
	$tbl[]=$ext;
	
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){unset($tbl[$num]);}
	}
	
	$hashKAV["FilterByName"]=implode(',',$tbl);
	while (list ($num, $ligne) = each ($hashKAV) ){
		$ini=$ini."$num=$ligne\n";
		
	}
	$ini="[smtpscan.group:$gidNumber]\n$ini";
	$updatearray["KasperkyAVScanningDatas"][]=$ini;	
	
if(!is_array_key("KasperkyAVScanningDatas",$HashGroup)){
		$ldap->Ldap_add_mod($HashGroup["dn"],$updatearray);
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
		
	}else{
		$ldap->Ldap_modify($HashGroup["dn"],$updatearray);
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
	}		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{added}');
}

function FilterByName_delete(){
$ldap=new clladp();
	if(preg_match('#kav:([0-9]+)#',$_GET["TreeKasSelect"],$reg)){$gidNumber=$reg[1];}
	$HashGroup=$ldap->GroupDatas($gidNumber);
	$kav4mailservers=new kav4mailservers(1,$HashGroup["KasperkyAVScanningDatas"]);
	$hashKAV=$kav4mailservers->loadAvSettingsDomain($gidNumber);	
	$tbl=explode(',',$hashKAV["FilterByName"]);
	$tbl[]=$ext;
	unset($tbl[$_GET["FilterByName_delete"]]);
	$hashKAV["FilterByName"]=implode(',',$tbl);
	while (list ($num, $ligne) = each ($hashKAV) ){
		$ini=$ini."$num=$ligne\n";
		}
	
	$ini="[smtpscan.group:$gidNumber]\n$ini";
	$updatearray["KasperkyAVScanningDatas"][]=$ini;	
	
	if(!is_array_key("KasperkyAVScanningDatas",$HashGroup)){
		$ldap->Ldap_add_mod($HashGroup["dn"],$updatearray);
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
		
	}else{
		$ldap->Ldap_modify($HashGroup["dn"],$updatearray);
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
	}		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{deleted}');		
	
}
	
	
function Tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;}
	if(preg_match('#kav:([0-9]+)#',$_GET["TreeKasSelect"],$reg)){$gidNumber=$reg[1];}	
	$page=CurrentPageName();
	$array[]="{antivirus} {protection}";
	$array[]="{attachments filters}";
	$array[]="{config file}";
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href= \"javascript:LoadKavTab('$num','$gidNumber');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	}
	
function FilterByName(){
	if(preg_match('#kav:([0-9]+)#',$_GET["TreeKasSelect"],$reg)){$gidNumber=$reg[1];}
	$html=tabs() ."
	<input type='hidden' id='TreeKasSelect' value='{$_GET["TreeKasSelect"]}'>
	<table style='width:100%'>
	<td valign='top' width=1%>
	<img src='img/attachments-kill-120.gif' style='margin-top:5px;'>
	</td>
	<td>
	<fieldset style='width:60%'><legend>{attachments filters}</legend>
	<table>
	<tr>
	<td><strong>{give ext}</strong>:&nbsp;</td><td><input type='text' id='FilterByName' size=10></td><td><input type='button' OnClick=\"javascript:FilterByName_add();\" value='{submit}'></td>
	</tr>
	</table>
	<div id='ext_list'>
	" . FilterByName_load(1).
	
	"</div>
	
	</fieldset>
	
	</td>
	</table>
	";
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
echo DIV_SHADOW($html,'windows');		
}

function ReadIniDatas(){
	$ldap=new clladp();
	if(preg_match('#kav:([0-9]+)#',$_GET["TreeKasSelect"],$reg)){$gidNumber=$reg[1];}
	$HashGroup=$ldap->GroupDatas($gidNumber);
	$kav4mailservers=new kav4mailservers(1,$HashGroup["KasperkyAVScanningDatas"]);
	$hashKAV=$kav4mailservers->array_conf["smtpscan.group:$gidNumber"];
if(is_array($hashKAV)){
	while (list ($num, $ligne) = each ($hashKAV) ){
		$ini=$ini."&nbsp;&nbsp;$num=$ligne<br>";
		}
	}
	
	$ini="[smtpscan.group:$gidNumber]<br>$ini";
	 $ini = wordwrap($ini, 85, "<br>", 1);	
$html=tabs() ."<table style='width:100%'>
	<td valign='top' width=1%>
	<img src='img/config-file-108px.gif' style='margin-top:5px;'>
	</td>
	<td>
		<fieldset>
			<legend>kav4mailserver.conf</legend>
			<table>
				<tr>
					<td><code>$ini</td></td>
				</tr>
			</table>
		</fieldset>
	
	</td>
	</tr>
	</table>

	";	
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
echo DIV_SHADOW($html,'windows');	
}

function FilterByName_load($return=0){
	if(preg_match('#kav:([0-9]+)#',$_GET["TreeKasSelect"],$reg)){$gidNumber=$reg[1];}
	$ldap=new clladp();
	$HashGroup=$ldap->GroupDatas($gidNumber);
	$kav4mailservers=new kav4mailservers(1,$HashGroup["KasperkyAVScanningDatas"]);
	$hashKAV=$kav4mailservers->array_conf["smtpscan.group:$gidNumber"];
	$html="<table style='width:30%' align='center'>";
	$tbl=explode(',',$hashKAV["FilterByName"]);	
	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
			if($ligne<>null){
				if($class=='rowA'){$class="rowB";}else{$class="rowA";}
				$html=$html . "<tr>
				<td width=1% valign=middle>" .get_img_ext($ligne) . "</td>
				<td valign=middle>$ligne</td>
				<td valign=middle width=1%><a href=\"javascript:FilterByName_delete($num);\"><img src='img/x.gif'></a></td>
				</tr>";
			}
		
		}
	$html = $html . "</table>";
	if($return==0){echo $html;exit;}
	return  $html;
}
}

function PostUpdateCmd(){
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){echo$tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");exit;}
	$kav=new kav4mailservers(1);
	while (list ($num, $ligne) = each ($_GET) ){
		$kav->array_conf["updater.options"][$num]=$ligne;
		
	}
	if($kav->Save()==true){echo "OK";}
	}
	
function LoadAvNotify(){
	$notify=$_GET["LoadAvNotify"];
	if(!isset($_GET["LoadAvNotifyType"])){$_GET["LoadAvNotifyType"]="recipient";}
	$LoadAvNotifyType=$_GET["LoadAvNotifyType"];
	$kas=new kav4mailservers();
	$notifyLdapField="TemplateNotify$notify$LoadAvNotifyType";
	$Htemplate=$kas->Load_template_notify_datas($notify,$LoadAvNotifyType);
	$subject=$Htemplate["SUBJECT"];
	$templ=$Htemplate["TPL"];
	$page=CurrentPageName();
	//ParseForm
	$html="<div style='padding:5px;margin:5px'>
		<h4>{notify_templates} &laquo;$notify&raquo;</h4>
		
		" . FieldTabNotify() . "
		<form name='$notifyLdapField'>
		<input type='hidden' name='LdapField' value='$notifyLdapField'>
		<input type='hidden' name='NotifyAction'  value='$LoadAvNotifyType'>
		<input type='hidden' name='notifydest'  value='$notify'>
		<table style='width:100%'>
		<tr>
		<td colspan=2 class=caption style='margin:3px'>{".$notify."}&nbsp;&raquo;&raquo;&nbsp;{"."$LoadAvNotifyType} </td>
		</tr>
		<tr><td colspan=2 class=caption style='margin:3px'>&nbsp;</td></tr>		
		<tr>
		<td align='right' nowrap>{subject}</td>
		<td>" . Field_text('subject',$subject) . "</td>
		</tr>
		<tr>
		<td colspan=2><textarea name='body_template' style='width:100%' rows=20>$templ</textarea></td>
		</tr>
		<tr>
		<td colspan=2 align='right'><input type='button' value='{edit}&nbsp&raquo;' OnClick=\"javascript:ParseForm('$notifyLdapField','$page',true);\"></td>
		</tr>		
		</table>
		<div class=caption>%SENDER%, %RECIPIENT%,, %DATE%, %VIRUSNAME%, %MSGID%, %SUBJECT%, %DATETIME%, %HEADERS%, %ACTION%</div>
		";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	
	
}

function FieldTabNotify(){
	$array=array("sender","recipient","admin");
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["LoadAvNotifyType"]==$ligne){$class="id=tab_current";}else{$class=null;}
			$html=$html . "<li><a href= \"javascript:LoadAvNotify('{$_GET["LoadAvNotify"]}','$ligne');\" $class>{$ligne}</a></li>\n";
		}
	return "<div id=tablist>$html</div>";		
	}
	
function SaveTemplate(){
	$subject=$_GET["subject"];
	$template=$_GET["body_template"];
	$notifydest=$_GET["notifydest"];
	$NotifyAction=$_GET["NotifyAction"];
	$datas="<subject>$subject</subject><template>$template</template>";
	$LdapField=$_GET["LdapField"];
	$ldap=new clladp();
	$tpl=new templates();
	writelogs("****** saving template $LdapField ****",__FUNCTION__,__FILE__);
	$ldap->ArticaDatasAddField($LdapField,$datas);
	if($ldap->ldap_last_error<>null){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);}else{echo  $tpl->_ENGINE_parse_body('{success}');}
}
	

?>
