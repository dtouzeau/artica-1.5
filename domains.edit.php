<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.mailboxes.inc');
	include_once('ressources/kav4mailservers.inc');
	include_once('ressources/class.cyrus.inc');



	if(isset($_GET["delete_transport"])){delete_transport();exit;}
	if(isset($_GET["save_transport"])){save_transport();exit;}
	if(isset($_GET["edit_antivirus_protection"])){edit_antivirus_protection();exit;}
	if(isset($_GET["LoadKasperskySettings"])){LoadKasperskySettings();exit;}
	if(isset($_GET["kasperskyactions"])){kasperskyactions();exit;}
	if(isset($_GET["deleteuserdn"])){users_delete();exit;}
	if(isset($_GET["users_refresh_table"])){echo users_table($_GET["ou"],$_GET["domain"]);exit;}
	if(isset($_GET["add_transport"])){add_transport();exit;}
	
	
//if(!isset($_GET["domain"])){header('location:domains.php');exit;}
//if(!isset($_GET["ou"])){header('location:domains.php');exit;}

writequeries();
		


PageIndex();

function PageIndex(){
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$transport=$ldap->GetTransportTable($domain);
	$bt_add_user="<input type='button' value='{add_user}&nbsp;&raquo;' OnClick=\"javascript:editmailbox('','$ou','$domain');\">";
	$bt_add_transport="&nbsp;|&nbsp;<input type='button' value='{transport_add}&nbsp;&raquo;' OnClick=\"javascript:add_transport('$domain','$ou');\">";
	$bt_av_settings="&nbsp;|&nbsp;<input type='button' value='{av_settings}&nbsp;&raquo;' OnClick=\"javascript:LoadKasperskySettings('$domain','$ou');\">";
	$bt_add_group="<input type='button' value='{add_new_group}&nbsp;&raquo;' OnClick=\"javascript:GroupADD('$ou');\">&nbsp;|&nbsp;";
	
	if($transport<>null){
		$users_table=transport_map($domain,$ou);
		$bt_add_user=null;
		$bt_add_transport=null;
	}else{
	$users_table=users_table();
	}
	
	if(aveserver_status()<0){$bt_av_settings=null;}
	
	$html=
	"<Fieldset>
		<legend>{users} - {ARRAY_TRANSPORT}</legend>
		<table>
			<tr class='rowB'>
			<td>$bt_add_group$bt_add_user$bt_add_transport$bt_av_settings</td>
			</tr>
		</table>
			<input type='hidden' id='ERROR_DELETE_USER' value=\"{ERROR_DELETE_USER}\">
		<table>
		<tr>
		<td width=50% valign='top'>
			<center><div id='group_table' ></div></center>
			<script>GroupLoad('$ou');</script>
		</td>
		<td width=50% valign='top'>
			<center><div id='users_table' >$users_table</div></center>
		</td>	
		</tr>
		</table>
		</fieldset>";

$tpl=new templates($_GET["domain"],$html,'mailbox.settings.php.js');
echo $tpl->web_page;	
	
	
	
}


function users_table($ou=null,$domain=null){
	$ldap=new clladp();
	$cyrus=new cyrus();
	$cyrus->LoadMailBox();
	$array_mailboxes=$_SESSION["array_mailboxes"];
	
	if($ou==null){$ou=$_GET["ou"];}else{$_GET["ou"]=$ou;}
	if($domain==null){$domain=$_GET["domain"];}{$_GET["domain"]=$domain;}
	
	$hash=$ldap->HashGetUsersAccounts($ou,$_GET["domain"]);
	if(is_array($hash)){
		$html="
		<center>
		<table>
		<tr class='rowT'>
			<td  nowrap width=1% colspan=2>&nbsp;</td>
			<td nowrap width=1%>{firstname}</td>
			<td nowrap width=1%>{lastname}</td>			
			<td nowrap width=1%>{email}</td>
			<td nowrap width=1%>&nbsp;</td>
			
		</tr>";
		
		while (list ($num, $ligne) = each ($hash) ){
			if($ligne["cn"]<>null){
				if($_SESSION["LOCK_IMAP"]==true){
					$mailbox="<img src='img/mailbox_bad_status.gif'>";
				}else{
					if($array_mailboxes[$ligne["uid"]]=="yes"){
						$mailbox="<img src='img/mailbox.gif'>";
					}else{$mailbox="&nbsp;";}
				}
				
				
			$html=$html . "<tr class='rowA' OnMouseOver=\"javascript:this.className = 'rowB';\" OnMouseOut=\"javascript:this.className = 'rowA';\">
			<td width=1%><img src='img/nomail.gif' style='border:0px;cursor:pointer'  OnClick=\"javascript:editmailbox('{$ligne["dn"]}','$ou','$domain','0');\" onMouseOver=\"javascript:AffBulle('{edit_user}');\" OnMouseOut=\"javascript:HideBulle();\"></td>
			<td width=1%>$mailbox</td>
			<td>&nbsp;{$ligne["first_name"]}&nbsp;</td>
			<td>&nbsp;{$ligne["last_name"]}&nbsp;</td>
			<td>&nbsp;{$ligne["mail"]}&nbsp;</td>
			<td>&nbsp;<a href=\"#\" OnClick=\"javascript:del_user('{$ligne["dn"]}','$ou','$domain');\"><img src='img/x.gif' onMouseOver=\"javascript:AffBulle('{delete_thisuser}');\" OnMouseOut=\"javascript:HideBulle();\"></a>&nbsp;</td>
			</tr>";
			}
			
		}
		
		$html=$html . "</table></center>";
		
	}
	if($html<>null){
	$tpl=new templates();
	return $tpl->_parse_body($html);
	}


	
}





function transport_map($domain,$ou,$nodelete=0){

	$ldap=new clladp();
	$mailbox=new MailBoxes();
	$transport=$ldap->GetTransportTable($domain);
	$hash=$mailbox->transport_maps_explode($transport);
	
	$service=array(
	"smtp"=>"smtp","relay"=>"relay","lmtp"=>"lmtp"
	);
	$field_service=Field_array_Hash($service,'transport_maps_service',$hash[0]);
	$delete="<a href='#' OnClick=\"javascript:delete_transport('$domain','$ou');\"><img src='img/x.gif'  onMouseOver=\"javascript:AffBulle('{transport_text_delete}');\" OnMouseOut=\"javascript:HideBulle();\"></a>";
	if($nodelete==1){$delete=null;}
$html="
	<p></p>	
	<center>
		<input type='hidden' id='ERROR_DELETE_TRANSPORT' value='{ERROR_DELETE_TRANSPORT}'>
		<table style='width:90%'>
		<tr class='rowT'>
			
			<td  width=99% colspan=2>{no_mailbox_cause_transport}</td>
		</tr>	
		<tr class='rowA'>
			<td nowrap width=1% align='right'>{transport_domain}:</td>
			<td nowrap width=99%>$domain {transport_will_translate_to}</td>
		</tr>		
			
		<tr class='rowA'>
			<td nowrap width=1% align='right'>{transport_type}:</td>
			<td nowrap width=99%>$field_service</td>
		</tr>
			<td nowrap width=1% align='right'>{transport_ip}:</td>			
			<td nowrap width=99%><input type='text' id='transport_maps' value='{$hash[1]}'></td>
		<tr class='rowB'>
			<td nowrap width=1% align='right'>{transport_port}:</td>
			<td nowrap width=99%><input type='text' id='transport_maps_port' value='{$hash[2]}'></td>
		</tr>
		<tr class='rowA'>
			<td nowrap width=1% align='right'><center>$delete</center></td>
			<td nowrap width=1% align='right'><input type='button' OnClick=\"javascript:edit_transport('$domain','$ou');\" value='{submit}&nbsp;&raquo;'></td>
			
		</tr>		
			
		</table>";
$tpl=new templates();
return $tpl->_parse_body($html);
	
}

function add_transport(){
	$html="<fieldset style='width:70%'><legend>{transport_add}</legend>" . transport_map($_GET["domain"],$_GET["ou"],1) . "</fieldset>";
	$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
	
}



function save_transport(){
	$mailbox=new MailBoxes();
	$domain=$_GET["save_transport"];
	$ou=$_GET["ou"];
	$line=$mailbox->transport_maps_implode($_GET["transport_maps"],$_GET["transport_maps_port"],$_GET["transport_maps_service"]);
	$ldap=new clladp();
	$transport=$ldap->GetTransportTable($domain);	
	if($transport<>null){
		$upd["transport"][0]=$line;
		$ldap->Ldap_modify("cn=$domain,ou=$ou,dc=organizations,$ldap->suffix",$upd);
		echo transport_map($domain,$ou);
		return null;
	}else{
		$upd['cn'][0]=$domain;
		$upd['transport'][0]=$line;
		$upd['objectClass'][0]='transportTable';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add("cn=$domain,ou=$ou,dc=organizations,$ldap->suffix",$upd);
		echo transport_map($domain,$ou);
		return null;		
	}
	
}

function delete_transport(){
	$domain=$_GET["domain"];
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$ldap->ldap_delete("cn=$domain,ou=$ou,dc=organizations,$ldap->suffix");
	
	
}

function aveserver_status(){
	$sock=new sockets();
	return trim($sock->getfile('avestatus'));
	}





function kav4mailservers($domain=null){
	if($domain<>null){$_GET["domain"]=$domain;}
	$mailbox=new MailBoxes();
	if ($mailbox->global_config["_GLOBAL"]["kav_mail"]=false){
		writelogs("Kaspersky for Mail server is not enabled...",__FUNCTION__,__FILE__);
		return "&nbsp;";}
	$kav4mailservers=new kav4mailservers();
	if ($kav4mailservers->IsDomainLicenced($_GET["domain"])==false){
		$check=Field_checkbox('kav4mailservers_enabled',1,0);
		$checked=false;
	}else{$check=Field_checkbox('kav4mailservers_enabled',1,1);$checked=true;}
	
	if($checked==true){
		$rouage="<a href=\"javascript:LoadKasperskySettings('{$_GET["domain"]}');\"><img src='img/rouage2.png'  onMouseOver=\"javascript:AffBulle('{js_antivirus_settings}');\" OnMouseOut=\"javascript:HideBulle();\"></a>";
		
	}
	
	
	$html="<table style='width:250px'>
	<tr class=rowT>
	<td colspan=3>{antivirus_protection}</td>
	</tr>
	<tr class=rowA align='right'>
	<td>{antivirus_protection_enabled}</td>
	<td width=1%>$check</td>
	<td width='1%'>$rouage</td>
	</tr>
	<tr class rowB>
	<td align='right' colspan=2>
	<input type='button' value='{bt_edit_antivirus_protection}' OnClick=\"javascript:edit_antivirus_protection('{$_GET["domain"]}');\" style='float:right'>
	</td>
	<td>&nbsp;</td>
	</table>
	";
	$tpl=new templates();
	$html=$tpl->_parse_body($html);
	return $html;
}

function edit_antivirus_protection(){
	$kav4mailservers=new kav4mailservers();
	if($_GET["enabled"]==1){
		$kav4mailservers->LicenseDomains[$_GET["edit_antivirus_protection"]]=$_GET["edit_antivirus_protection"];
		
	}else{
		unset($kav4mailservers->LicenseDomains[$_GET["edit_antivirus_protection"]]);
	}
	
	$kav4mailservers->Save();
	echo kav4mailservers($_GET["edit_antivirus_protection"]);
}

function LoadKasperskySettings($domain=null){
	if($domain<>null){$_GET["LoadKasperskySettings"]=$domain;}
	$kav4mailservers=new kav4mailservers();
	$hash=$kav4mailservers->loadAvSettingsDomain($_GET["LoadKasperskySettings"]);
	
	$CuredQuarantine=Field_yesno_checkbox("CuredQuarantine",$hash["CuredQuarantine"]);
	$InfectedQuarantine=Field_yesno_checkbox("InfectedQuarantine",$hash["InfectedQuarantine"]);
	$SuspiciousQuarantine=Field_yesno_checkbox("SuspiciousQuarantine",$hash["SuspiciousQuarantine"]);
	$CorruptedQuarantine=Field_yesno_checkbox("CorruptedQuarantine",$hash["CorruptedQuarantine"]);
	$WarningQuarantine=Field_yesno_checkbox("WarningQuarantine",$hash["WarningQuarantine"]);
	$FilteredQuarantine=Field_yesno_checkbox("FilteredQuarantine",$hash["FilteredQuarantine"]);
	$ErrorQuarantine=Field_yesno_checkbox("ErrorQuarantine",$hash["ErrorQuarantine"]);	
	$ProtectedQuarantine=Field_yesno_checkbox("ProtectedQuarantine",$hash["ProtectedQuarantine"]);
	
	$CuredAdminNotify=Field_yesno_checkbox("CuredAdminNotify",$hash["CuredAdminNotify"]);
	$InfectedAdminNotify=Field_yesno_checkbox("InfectedAdminNotify",$hash["InfectedAdminNotify"]);
	$SuspiciousAdminNotify=Field_yesno_checkbox("SuspiciousAdminNotify",$hash["SuspiciousAdminNotify"]);
	$CorruptedAdminNotify=Field_yesno_checkbox("CorruptedAdminNotify",$hash["CorruptedAdminNotify"]);
	$WarningAdminNotify=Field_yesno_checkbox("WarningAdminNotify",$hash["WarningAdminNotify"]);
	$FilteredAdminNotify=Field_yesno_checkbox("FilteredAdminNotify",$hash["FilteredAdminNotify"]);
	$ErrorAdminNotify=Field_yesno_checkbox("ErrorAdminNotify",$hash["ErrorAdminNotify"]);
	$ProtectedAdminNotify=Field_yesno_checkbox("ProtectedAdminNotify",$hash["ProtectedAdminNotify"]);	

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

	$CuredSenderNotify=Field_yesno_checkbox("CuredSenderNotify",$hash["CuredSenderNotify"]);
	$InfectedSenderNotify=Field_yesno_checkbox("InfectedSenderNotify",$hash["InfectedSenderNotify"]);
	$SuspiciousSenderNotify=Field_yesno_checkbox("SuspiciousSenderNotify",$hash["SuspiciousSenderNotify"]);
	$CorruptedSenderNotify=Field_yesno_checkbox("CorruptedSenderNotify",$hash["CorruptedSenderNotify"]);
	$WarningSenderNotify=Field_yesno_checkbox("WarningSenderNotify",$hash["WarningSenderNotify"]);
	$FilteredSenderNotify=Field_yesno_checkbox("FilteredSenderNotify",$hash["FilteredSenderNotify"]);
	$ErrorSenderNotify=Field_yesno_checkbox("ErrorSenderNotify",$hash["ErrorSenderNotify"]);
	$ProtectedSenderNotify=Field_yesno_checkbox("ProtectedSenderNotify",$hash["ProtectedSenderNotify"]);

	$CuredRecipientNotify=Field_yesno_checkbox("CuredRecipientNotify",$hash["CuredRecipientNotify"]);
	$InfectedRecipientNotify=Field_yesno_checkbox("InfectedRecipientNotify",$hash["InfectedRecipientNotify"]);
	$SuspiciousRecipientNotify=Field_yesno_checkbox("SuspiciousRecipientNotify",$hash["SuspiciousRecipientNotify"]);
	$CorruptedRecipientNotify=Field_yesno_checkbox("CorruptedRecipientNotify",$hash["CorruptedRecipientNotify"]);
	$WarningRecipientNotify=Field_yesno_checkbox("WarningRecipientNotify",$hash["WarningRecipientNotify"]);
	$FilteredRecipientNotify=Field_yesno_checkbox("FilteredRecipientNotify",$hash["FilteredRecipientNotify"]);
	$ErrorRecipientNotify=Field_yesno_checkbox("ErrorRecipientNotify",$hash["ErrorRecipientNotify"]);
	$ProtectedRecipientNotify=Field_yesno_checkbox("ProtectedRecipientNotify",$hash["ProtectedRecipientNotify"]);

	$CuredRecipientAttachReport=Field_yesno_checkbox("CuredRecipientAttachReport",$hash["CuredRecipientAttachReport"]);
	$InfectedRecipientAttachReport=Field_yesno_checkbox("InfectedRecipientAttachReport",$hash["InfectedRecipientAttachReport"]);
	$SuspiciousRecipientAttachReport=Field_yesno_checkbox("SuspiciousRecipientAttachReport",$hash["SuspiciousRecipientAttachReport"]);
	$CorruptedRecipientAttachReport=Field_yesno_checkbox("CorruptedRecipientAttachReport",$hash["CorruptedRecipientAttachReport"]);
	$WarningRecipientAttachReport=Field_yesno_checkbox("WarningRecipientAttachReport",$hash["WarningRecipientAttachReport"]);
	$FilteredRecipientAttachReport=Field_yesno_checkbox("FilteredRecipientAttachReport",$hash["FilteredRecipientAttachReport"]);
	$ErrorRecipientAttachReport=Field_yesno_checkbox("ErrorRecipientAttachReport",$hash["ErrorRecipientAttachReport"]);
	$ProtectedRecipientAttachReport=Field_yesno_checkbox("ProtectedRecipientAttachReport",$hash["ProtectedRecipientAttachReport"]);		
	
	if($hash["QuarantinePath"]<>null){
		$QuarantinePath=basename($hash["QuarantinePath"]);
		$QuarantinePath="
		<tr class=rowA>
			<td align='right'>{QuarantinePath}</td>
			<td>$QuarantinePath</td>
		</tr>";
			
	}
	
	
	$html="
	<fieldset>
		<legend>{LoadKasperskySettings_general_title}</legend>
		<table>
		<tr class=rowA>
		<td align=right>{LoadKasperskySettings_adminaddr}:</td>
		<input type='text' id='AdminAddress' value='{$hash["AdminAddress"]}'></td>
		</tr>
		$QuarantinePath
		</table>
	</fieldset>
	<fieldset>
		<legend>{av_notify_rules}</legend>

<form name='kasperskyactions'>		
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
	<td>{Infected}</td>
	<td id='center' width=1%>$InfectedQuarantine</td>
	<td id='center' width=1%>$InfectedAdminNotify</td>
	<td id='center' >$InfectedAdminAction</td>
	<td id='center' width=1%>$InfectedSenderNotify</td>
	<td id='center' width=1%>$InfectedRecipientNotify</td>
	<td id='center' width=1%>$InfectedRecipientAttachReport</td>
	<td id='center' >$InfectedRecipientAction</td>
	
</tr>
<tr class=rowB align='right'>
	<td>{Cured}</td>
	<td id='center' width=1%>$CuredQuarantine</td>
	<td id='center' width=1%>$CuredAdminNotify</td>
	<td id='center' >$CuredAdminAction</td>
	<td id='center' width=1%>$CuredSenderNotify</td>
	<td id='center' width=1%>$CuredRecipientNotify</td>
	<td id='center' width=1%>$CuredRecipientAttachReport</td>
	<td id='center' >$CuredRecipientAction</td>
</tr> 			  				 
<tr class=rowA align='right'>
	<td>{Suspicious} </td>
	<td id='center' width=1%>$SuspiciousQuarantine</td>
	<td id='center' width=1%>$SuspiciousAdminNotify</td>
	<td id='center' >$SuspiciousAdminAction</td>
	<td id='center' width=1%>$SuspiciousSenderNotify</td>
	<td id='center' width=1%>$SuspiciousRecipientNotify</td>
	<td id='center' width=1%>$SuspiciousRecipientAttachReport</td>
	<td id='center' >$SuspiciousRecipientAction</td>
</tr>			  				 
<tr class=rowB align='right'>
	<td>{Corrupted}</td>
	<td id='center' width=1%>$CorruptedQuarantine</td>	
	<td id='center' width=1%>$CorruptedAdminNotify</td>		
	<td id='center'>$CorruptedAdminAction</td>		
	<td id='center' width=1%>$CorruptedSenderNotify</td>
	<td id='center' width=1%>$CorruptedRecipientNotify</td>
	<td id='center' width=1%>$CorruptedRecipientAttachReport</td>
	<td id='center' >$CorruptedRecipientAction</td>
</tr> 			  				 

<tr class=rowA align='right'>
	<td>{Warning}</td>
	<td class=center width=1%>$WarningQuarantine</td>
	<td class=center width=1%>$WarningAdminNotify</td>	
	<td class=center>$WarningAdminAction</td>
	<td id='center' width=1%>$WarningSenderNotify</td>
	<td id='center' width=1%>$WarningRecipientNotify</td>
	<td id='center' width=1%>$WarningRecipientAttachReport</td>
	<td id='center' >$WarningRecipientAction</td>
</tr> 			  				 
<tr class=rowB align='right'>
	<td>{Filtered}</td>
	<td id='center' width=1%>$FilteredQuarantine</td>
	<td id='center' width=1%>$FilteredAdminNotify</td>
	<td id='center' >$FilteredAdminAction</td>
	<td id='center' width=1%>$FilteredSenderNotify</td>
	<td id='center' width=1%>$FilteredRecipientNotify</td>
	<td id='center' width=1%>$FilteredRecipientAttachReport</td>
	<td id='center' >$FilteredRecipientAction</td>
	
</tr> 			  				 
<tr class=rowA align='right'>
	<td>{Error}</td>
	<td id='center' width=1%>$ErrorQuarantine</td>
	<td id='center' width=1%>$ErrorAdminNotify</td>
	<td id='center' >$ErrorAdminAction</td>
	<td id='center' width=1%>$ErrorSenderNotify</td>
	<td id='center' width=1%>$ErrorRecipientNotify</td>
	<td id='center' width=1%>$ErrorRecipientAttachReport</td>
	<td id='center' >$ErrorRecipientAction</td>
</tr> 			  				 
<tr class=rowB align='right'>
	<td>{Protected}</td>
	<td id='center' width=1%>$ProtectedQuarantine</td>
	<td id='center' width=1%>$ProtectedAdminNotify</td>
	<td id='center' >$ProtectedAdminAction</td>
	<td id='center' width=1%>$ProtectedSenderNotify</td>
	<td id='center' width=1%>$ProtectedRecipientNotify</td>
	<td id='center' width=1%>$ProtectedRecipientAttachReport</td>
	<td id='center' >$ProtectedRecipientAction</td>
</tr>
</form>
<tr class=rowA align='right' >
	<td colspan=8><input type='button' value='{bt_edit_notify}' OnClick=\"javascript:EditKasperskySettings('{$_GET["LoadKasperskySettings"]}');\" style='float:right'></td></tr>
</table>
</fieldset>
			
		
	</fieldset>
	";
	
	$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
	
	

}
function kasperskyactions(){
	$domain=$_GET["kasperskyactions"];
	$kav4mailservers=new kav4mailservers();
	unset($_GET["kasperskyactions"]);
	$_GET["Check"]="yes";
	$_GET["AddXHeaders"]="yes";
	$_GET["Recipients"]='.*@' . str_replace('.','\.',$domain);
	$_GET["QuarantinePath"]=dirname(__FILE__) . "/kaspersky/quarantines/$domain";
	$kav4mailservers->EditAvSettingsDomain($domain,$_GET);
	echo LoadKasperskySettings($domain);
	}

	

	




function users_delete(){
	$ldap=new clladp();
	$ldap->ldap_delete($_GET["deleteuserdn"],true);
	echo users_table($_GET["ou"],$_GET["domain"]);
}





?>
	
