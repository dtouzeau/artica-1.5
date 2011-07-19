<?php
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.contacts.inc");


if(isset($_GET["newtab"])){CONTACT_NEW_TAB();exit;}
if(isset($_GET["contact"])){CONTACT_INDEX();exit;}
if(isset($_GET["contact-index"])){CONTACT_PAGE();exit;}
if(isset($_GET["completeName"])){CONTACT_COMPLETE_NAME();exit;}
if(isset($_POST["xEditSave"])){CONTACT_SAVE();exit;}
if(isset($_GET["showtab"])){echo CONTACT_TABS();exit;}
if(isset($_GET["section"])){CONTACT_SWITCH();exit;}
if(isset($_GET["delete-contact"])){CONTACT_DELETE();exit;}
if(isset($_GET["mozillaSecondEmailList"])){echo mozillaSecondEmailList();exit;}
if(isset($_GET["mozillaSecondEmail"])){mozillaSecondEmailAdd();exit;}
if(isset($_GET["mozillaSecondEmailDelete"])){mozillaSecondEmailDelete();exit;}
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{contact}');
	$complete_name=$tpl->_ENGINE_parse_body('{complete_name}');

	$page=CurrentPageName();
	
	if(isset($_GET["employeeNumber"])){$uri="employeeNumber={$_GET["employeeNumber"]}";}
	if(isset($_GET["uidUser"])){$uri="uidUser={$_GET["uidUser"]}";}
	
	
	if(!isset($_GET["section"])){$_GET["section"]="contact";}
	$html="
		var mem_section='';
	
		function LoadContact(){
			YahooWin5(800,'$page?contact=yes&$uri','$title');
			
		
		}
		
		function CompleteName(){
			YahooWin6(350,'$page?completeName=yes','$complete_name');
			setTimeout('CompleteNameFill()',500);
		
		}
		
		function CompleteNameCheck(event){
			if(checkEnter(event)){CompleteNameEdit();}
		}
		
		function CompleteNameFill(){
			if(!document.getElementById('givenName1')){
				setTimeout('CompleteNameFill()',500);
				return false;
			}
			
			var givenname=document.getElementById('givenName').value;
			var sn=document.getElementById('sn').value;
			document.getElementById('givenName1').value=givenname;
			document.getElementById('sn1').value=sn;
			
		
		}
		
		function CompleteNameEdit(){
			var givenname=document.getElementById('givenName1').value;
			var sn=document.getElementById('sn1').value;
			document.getElementById('givenName').value=givenname;
			document.getElementById('sn').value=sn;
			document.getElementById('displayName').value=givenname+' '+sn;
			YahooWin6Hide();
				
		
		}
		
		function ContactTabs(num,div){
			if(document.getElementById('employeeNumber')){
				var employeeNumber=document.getElementById('employeeNumber').value;
				LoadAjax(div,'$page?section='+num+'&employeeNumber='+employeeNumber);
			}
			
			if(document.getElementById('uidUser')){
				var uidUser=document.getElementById('uidUser').value;
				LoadAjax(div,'$page?section='+num+'&uidUser='+uidUser);
			}					
		}
		
		function x_EditContact(){
			alert(mem_section);
			
		}
		
		
		function EditContactCheck(event,form){
			if(document.getElementById('locked').value==1){return;}
			if(!form){form='FFM_CONTACT_PAGE';}
			if(checkEnter(event)){EditContact(form);}
		}	

		function mozillaSecondEmailCheck(event){
			if(document.getElementById('locked').value==1){return;}
			if(checkEnter(event)){mozillaSecondEmailAdd();}
		}
		function EditContact(Form_name){
			$.post('$page', $('#'+Form_name).serialize());
			RefreshTab('main_config_contact');
		}
		
	var x_mozillaSecondEmailAdd= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
			if(document.getElementById('employeeNumber')){
				var employeeNumber=document.getElementById('employeeNumber').value;
				LoadAjax('mozillaSecondEmailList','$page?mozillaSecondEmailList=yes&employeeNumber='+employeeNumber);
				}
			
			if(document.getElementById('uidUser')){
				var uidUser=document.getElementById('uidUser').value;
				LoadAjax('mozillaSecondEmailList','$page?mozillaSecondEmailList=yes&uidUser='+uidUser);
				
			}
		}

		function mozillaSecondEmailDelete(num){
					
			var XHR = new XHRConnection();
			XHR.appendData('mozillaSecondEmailDelete',num);
			mem_section=document.getElementById('tab').value;
			if(document.getElementById('employeeNumber')){
				var employeeNumber=document.getElementById('employeeNumber').value;
				XHR.appendData('employeeNumber',employeeNumber);
			}
			if(document.getElementById('uidUser')){
				var uidUser=document.getElementById('uidUser').value;
				XHR.appendData('uidUser',uidUser);
			}
				
			XHR.sendAndLoad('$page', 'GET',x_mozillaSecondEmailAdd);	
		
		}
		
		function mozillaSecondEmailAdd(){
			var mozillaSecondEmail=document.getElementById('mozillaSecondEmail').value;
			var XHR = new XHRConnection();
			XHR.appendData('mozillaSecondEmail',mozillaSecondEmail);
			mem_section=document.getElementById('tab').value;
			if(document.getElementById('employeeNumber')){
				var employeeNumber=document.getElementById('employeeNumber').value;
				XHR.appendData('employeeNumber',employeeNumber);
			}
			if(document.getElementById('uidUser')){
				var uidUser=document.getElementById('uidUser').value;
				XHR.appendData('uidUser',uidUser);
			}
				
			XHR.sendAndLoad('$page', 'GET',x_mozillaSecondEmailAdd);	
		}
		
		
	var x_ContactDelete= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
		RefreshTabsCT();
		YahooWin5Hide();
		}			
		
		function ContactDelete(dn){
			var XHR = new XHRConnection();
			XHR.appendData('delete-contact',dn);
			document.getElementById('contact_section').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_ContactDelete);	
		
		}
		
	function RefreshTabsCT(){
		if(document.getElementById('main_config_myaddressbook')){RefreshTab('main_config_myaddressbook');}
	}
	
	
		
	
	LoadContact();
	
	";
	
	echo $html;
	
}

function CONTACT_COMPLETE_NAME(){
	$html="
	<div class=contact>
	<table style='width:100%' class=table_form>
		<tr>
			<td class=legend nowrap>{givenname}:</td>
			<td>" . Field_text('givenName1',null,null,null,null,null,false,"CompleteNameCheck(event)")."</td>
		</tr>
		<tr>
			<td class=legend nowrap>{sn}:</td>
			<td>" . Field_text('sn1',null,null,null,null,null,false,"CompleteNameCheck(event)")."</td>
		</tr>

		<tr>
			<td align='right' colspan=2>
			<hr>". button('{apply}',"CompleteNameEdit()").
			"</td>
		</tr>	
	</table>	
	</div>
		
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function mozillaSecondEmailList(){
if(!isset($_GET["uidUser"])){
		$ct=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);
	}else{
		$ct=new contacts($_SESSION["uid"],null,$_GET["uidUser"]);	
	}

	if(!is_array($ct->mozillaSecondEmail)){return null;}
	
	$html="<table style='width:100%'>";
	
	print_r($ct->mozillaSecondEmai);
	
	while (list ($num, $ligne) = each ($ct->mozillaSecondEmail) ){
		$html=$html ."<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td><code>$ligne</code></td>
			<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","mozillaSecondEmailDelete($num);")."</td>
		</tr>";
			
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
}



function CONTACT_INDEX(){
		$array["contact-index"]="{contact}";
		$array["personal-informations"]="{personal_informations}";
		$array["postal-address"]="{postal_address}";
		$tpl=new templates();
		$page=CurrentPageName();
		
	while (list ($num, $ligne) = each ($array) ){
		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?newtab=$num&employeeNumber={$_GET["employeeNumber"]}&uidUser={$_GET["uidUser"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_contact style='width:750px;height:520px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_contact').tabs();
		});
		</script>";			
	
}

function CONTACT_NEW_TAB(){
	
	
	if($_GET["uidUser"]==null){
		$ct=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);
		$hidden="<input type='hidden' id='employeeNumber' name='employeeNumber' value='$ct->employeeNumber'>";
		
	}
		
		
	if($_GET["uidUser"]<>null){
		$hidden="<input type='hidden' id='uidUser' name='uidUser' value='{$_GET["uidUser"]}'>";	
	}		
	
	$id=time();
	$html="$hidden
	<div id='$id' class='contact'></div>
	
	<script>
		ContactTabs('{$_GET["newtab"]}','$id');
	</script>
	";
	echo $html;
}


	
function CONTACT_PAGE(){

	$button=button("{apply}","EditContact('FFM_CONTACT_PAGE');RefreshTabsCT();");
	$button_complete=button("{complete_name}","CompleteName();");
	$add_email_picture=imgtootltip("plus-24.png","{add}","mozillaSecondEmailAdd()");
	$locked=0;
	
	if(!isset($_GET["uidUser"])){
		$ct=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);
		$delete=imgtootltip("ed_delete.gif",'{delete}',"ContactDelete('{$_GET["employeeNumber"]}')");
		$hidden="<input type='hidden' id='employeeNumber' name='employeeNumber' value='$ct->employeeNumber'>";
		$ProfessionalEmail=Field_text('mail',"$ct->mail",null,null,null,null,false,"EditContactCheck(event)");
		$img_picture=imgtootltip($ct->img_identity,"{picture}","s_PopUp('edit.thumbnail.php?employeeNumber=$ct->employeeNumber',600,300);");
		if($_SESSION["uid"]<>-100){
			$ldap=new clladp();
			$user=new user($_SESSION["uid"]);
			$dn="cn=$ct->sn $ct->givenName,ou=$user->uid,ou=People,dc=$user->ou,dc=NAB,$ldap->suffix";
			if($dn<>$ct->dn){$delete=null;}
			
			}		
		
		}	
		
		
		
	if($_GET["uidUser"]<>null){
		$ct=new contacts($_SESSION["uid"],null,$_GET["uidUser"]);
		$hidden="<input type='hidden' id='uidUser' name='uidUser' value='{$_GET["uidUser"]}'>";
		$ProfessionalEmail=Field_text('mail',"$ct->mail",null,null,null,null,false,"EditContactCheck(event)",true);
		$img_picture=imgtootltip($ct->img_identity,"{picture}","s_PopUp('edit.thumbnail.php?uid={$_GET["uidUser"]}',600,300);");
		
		if($_SESSION["uid"]<>-100){
			 	$users=new usersMenus();
			 	if(!$users->AllowAddUsers){
			 		if($_SESSION["uid"]<>$_GET["uidUser"]){
			 			$delete=null;
			 			$button=null;
			 			$button_complete=null;
			 			$img_picture=imgtootltip($ct->img_identity,"{picture}");
			 			$add_email_picture=null;
			 			$locked=1;
			 		}
			 	}
			}		
		}
	
	if(strpos($ct->nsAIMid,"one.")>0){$ct->nsAIMid="";}	
	if(strpos($ct->nsICQid,"one.")>0){$ct->nsICQid="";}	
	if(strpos($ct->nsMSNid,"one.")>0){$ct->nsMSNid="";}	
	if(strpos($ct->nsYahooid,"one.")>0){$ct->nsYahooid="";}
	
$instantMessaging="<hr><div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{instant_messaging}</div>
		<table style='width:100%;margin-left:10px'>
			<tr>
				<td class=legend valign='middle' nowrap>{nsAIMid}:</td>
				<td valign='middle'>" . Field_text('nsAIMid',"$ct->nsAIMid",null,null,null,null,false,"EditContactCheck(event)")."</td>
				<td class=legend valign='middle' nowrap>{nsICQid}:</td>
				<td valign='middle'>" . Field_text('nsICQid',"$ct->nsICQid",null,null,null,null,false,"EditContactCheck(event)")."</td>	
			</tr>
			<tr>			
				<td class=legend valign='middle' nowrap>{nsMSNid}:</td>
				<td valign='middle'>" . Field_text('nsMSNid',"$ct->nsMSNid",null,null,null,null,false,"EditContactCheck(event)")."</td>
				<td class=legend valign='middle' nowrap>{nsYahooid}:</td>
				<td valign='middle'>" . Field_text('nsYahooid',"$ct->nsYahooid",null,null,null,null,false,"EditContactCheck(event)")."</td>					
			</tr>			
		</table>";
	
	if($_GET["uidUser"]<>null){$instantMessaging=null;}

	if(trim($ct->img_identity)==null){$ct->img_identity="contact-unknown-user.png";}
	
	
	$fieldStyle=null;
	$form="
		<input type='hidden' id='tab' value='{$_GET["section"]}'>
		<input type='hidden' id='givenName' name='givenName' value='$ct->givenName'>
		<input type='hidden' id='sn' name='sn' value='$ct->sn'>
		<input type='hidden' id='locked' name='locked' value='$locked'>
		$hidden
		<input type='hidden' id='xEditSave' name='xEditSave' value='yes'>
		<table style='width:100%'>
		<tr>
			<td valign='top'>
				<table style='width:100%'>
					<tr>
						<td valign='top'>
							<div style='border:1px solid #CCCCCC;width:61px;padding:2px'>$img_picture</div>
						</td>
						<td valign='top'><br>
							<table style='width:100%'>
								<tr>
									<td valign='top'>$button_complete</td>
									<td valign='middle'>" . Field_text('displayName',"$ct->displayName",null,null,null,null,false,"EditContactCheck(event)",true,"CompleteName()")."</td>
									<td class=legend>{xmozillanickname}:</td>
									<td valign='middle' colspan=3>" . Field_text('mozillaNickname',"$ct->mozillaNickname",null,null,null,null,false,"EditContactCheck(event)")."</td>
								</tr>
									<td valign='middle' class=legend>{department}:</td>
									<td valign='middle'>" . Field_text('ou',"$ct->ou",null,null,null,null,false,"EditContactCheck(event)")."</td>
									<td valign='middle' class=legend nowrap>{employeeNumber}:</td>		
									<td valign='middle'><code>{$_GET["employeeNumber"]}{$_GET["uidUser"]}</code></td>
									<td valign='middle'>$delete</td>		
								<tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
		<td valign='top'>
		<hr>
		<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{electronic_address}</div>
		<table style='width:100%;margin-left:10px'>
			<tr>
				<td class=legend valign='top' nowrap>{professional_email}:</td>
				<td valign='top'>$ProfessionalEmail</td>
				<td class=legend valign='top' nowrap>{personal_email}:</td>
				<td valign='top'>
				<table style='width:100%;margin-bottom:3px'>
					<tr>
						<td>" . Field_text('mozillaSecondEmail',"",null,null,null,null,false,
				"mozillaSecondEmailCheck(event)")."
						</td>
						<td>$add_email_picture</td>
					</tr>
				</table>
				".RoundedLightGrey("<div id='mozillaSecondEmailList' style='height:40px;overflow:auto'>".mozillaSecondEmailList()."</div>")."
				</td>
			</tr>			
		</table>
		<hr><div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{phone_title}</div>
		<table style='width:100%;margin-left:10px'>
			<tr>
				<td class=legend valign='middle' nowrap>{phone}:</td>
				<td valign='middle'>" . Field_text('telephoneNumber',"$ct->telephoneNumber",null,null,null,null,false,"EditContactCheck(event)")."</td>
				<td class=legend valign='middle' nowrap>{mobile}:</td>
				<td valign='middle'>" . Field_text('mobile',"$ct->mobile",null,null,null,null,false,"EditContactCheck(event)")."</td>	
			</tr>
			<tr>			
				<td class=legend valign='middle' nowrap>{homePhone}:</td>
				<td valign='middle'>" . Field_text('homePhone',"$ct->homePhone",null,null,null,null,false,"EditContactCheck(event)")."</td>
				<td class=legend valign='middle' nowrap>{facsimileTelephoneNumber}:</td>
				<td valign='middle'>" . Field_text('Fax',"$ct->Fax",null,null,null,null,false,
				"EditContactCheck(event)")."</td>					
			</tr>			
		</table>

	
		
		
		</td>
		</tr>
		
		</table>
		";
	
	
	
	$html="
	<form name='FFM_CONTACT_PAGE' id='FFM_CONTACT_PAGE'>
		$form
	</form>
	<table  style='width:100%'>
	<tr>
		<td align='right'>$button</td>
	</tr>
	</table>
	
	<script>
		var lock=$locked;
		if(lock==1){
			document.getElementById('mozillaSecondEmail').disabled=true;
			
		}
	
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function CONTACT_SAVE(){
	$uid=$_SESSION["uid"];
	
	if($_POST["uidUser"]<>null){
		writelogs("{$_SESSION["uid"]}:: Save employee id \"{$_POST["uidUser"]}\"",__FUNCTION__,__FILE__);
		$contact=new contacts($uid,null,$_POST["uidUser"]);
	}
	
	if($_POST["employeeNumber"]<>null){
		$employeeNumber=$_POST["employeeNumber"];
		writelogs("{$_SESSION["uid"]}::Save employee number \"$employeeNumber\"",__FUNCTION__,__FILE__);
		$contact=new contacts($uid,$employeeNumber);
	}
	
	if($_POST["employeeNumber"]==null && $_POST["uidUser"]==null){
		writelogs("{$_SESSION["uid"]}::Add employee",__FUNCTION__,__FILE__);
		$contact=new contacts($uid,null);	
	}
	
	while (list ($num, $ligne) = each ($_POST) ){
		$ligne=ParseSpecialsCharacters($ligne);
		$ligne=utf8_encode($ligne);
		writelogs("{$_SESSION["uid"]}::Save $num=\"".$ligne."\"",__FUNCTION__,__FILE__);
		$contact->$num=$ligne;
		}
	
	echo $contact->Save();
	
	
}

function CONTACT_SWITCH(){
	
	switch ($_GET["section"]) {
		case "contact":echo CONTACT_PAGE();break;
		case "personal-informations":echo CONTACT_PERSO();break;
		case "postal-address":echo CONTACT_ADDRESS();break;
		
		
		default:echo CONTACT_PAGE();break;
		break;
	}
	
	
}
function CONTACT_PERSO(){
	$locked=0;
	
	$businessRoleEnabled=false;
	$managerNameEnabled=false;
	$assistantNameEnabled=false;
	$spouseNameEnabled=false;
	$birthDateEnabled=false;
	$anniversaryEnabled=false;
	$noteEnabled=null;
	$button=button("{apply}","EditContact('FFM_CONTACT_PAGE2');RefreshTabsCT();");
	if(!isset($_GET["uidUser"])){
		$ct=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);
		$token="<input type='hidden' id='employeeNumber' name='employeeNumber' value='$ct->employeeNumber'>";

	}else{
		$ct=new contacts($_SESSION["uid"],null,$_GET["uidUser"]);	
		$token="<input type='hidden' id='uidUser' name='uidUser' value='{$_GET["uidUser"]}'>";
		$businessRoleEnabled=true;
		$managerNameEnabled=true;
		$assistantNameEnabled=true;
		$spouseNameEnabled=true;
		$birthDateEnabled=true;
		$anniversaryEnabled=true;
		$noteEnabled=" DISABLED";
	}
	
	if($_GET["uidUser"]<>null){
		if($_SESSION["uid"]<>-100){
			 	$users=new usersMenus();
			 	if(!$users->AllowAddUsers){
			 		if($_SESSION["uid"]<>$_GET["uidUser"]){
			 			$delete=null;
			 			$button=null;
			 			$locked=1;
			 			
			 		}
			 	}
			}
	}	
	
	
		
	
	
	$form="
		<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{websites}</div>
		<input type='hidden' id='tab' value='{$_GET["section"]}'>
		<input type='hidden' id='locked' value='$locked'>
		$token
		<input type='hidden' id='xEditSave' name='xEditSave' value='yes'>
<table style='width:100%;margin-left:10px'>
			<tr>
				<td class=legend valign='middle' nowrap>{website}:</td>
				<td valign='middle'>" . Field_text('labeledURI',"$ct->labeledURI",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')")."</td>
			</tr>
		</table>
<hr>		
<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{work}</div>
		<table style='width:100%;margin-left:10px'>
			<tr>
				<td class=legend valign='middle' nowrap>{businessRole}:</td>
				<td valign='middle'>" . Field_text('businessRole',"$ct->businessRole",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')",$businessRoleEnabled)."</td>
				<td class=legend valign='middle' nowrap>{working_title}:</td>
				<td valign='middle'>" . Field_text('title',"$ct->title",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')")."</td>	
			</tr>
			<tr>			
				<td class=legend valign='middle' nowrap>{company}:</td>
				<td valign='middle'>" . Field_text('o',"$ct->o",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')")."</td>
				<td class=legend valign='middle' nowrap>{department}:</td>
				<td valign='middle'>" . Field_text('ou',"$ct->ou",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')")."</td>					
			</tr>	
			<tr>			
				<td class=legend valign='middle' nowrap>{managerName}:</td>
				<td valign='middle'>" . Field_text('managerName',"$ct->managerName",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')",$managerNameEnabled)."</td>
				<td class=legend valign='middle' nowrap>{assistantName}:</td>
				<td valign='middle'>" . Field_text('assistantName',"$ct->assistantName",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')",$assistantNameEnabled)."</td>					
			</tr>						
		</table>
<hr>		
<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{other}</div>	
<table style='width:100%;margin-left:10px'>
			<tr>
				<td class=legend valign='middle' nowrap>{roomNumber}:</td>
				<td valign='middle'>" . Field_text('roomNumber',"$ct->roomNumber",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')")."</td>
				<td class=legend valign='middle' nowrap>{birthDate}:</td>
				<td valign='middle'>" . Field_text('birthDate',"$ct->birthDate",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')",$birthDateEnabled)."</td>	
			</tr>	
			<tr>
				<td class=legend valign='middle' nowrap>{spouseName}:</td>
				<td valign='middle'>" . Field_text('spouseName',"$ct->spouseName",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')",$spouseNameEnabled)."</td>
				<td class=legend valign='middle' nowrap>{birthDate}:</td>
				<td valign='middle'>" . Field_text('anniversary',"$ct->anniversary",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE2')",$anniversaryEnabled)."</td>	
			</tr>	
			<tr>
				<td class=legend valign='top' nowrap>{note}:</td>
				<td valign='middle' colspan=3><textarea name=note id='note' style='width:99%;height:100px' $noteEnabled>$ct->note</textarea></td>	
			</tr>
		</table>					
		";		


	
	$html="
	<form name='FFM_CONTACT_PAGE2' id='FFM_CONTACT_PAGE2'>
		$form
	</form>
	<table  style='width:100%'>
	<tr>
		<td align='right'>$button</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function CONTACT_ADDRESS(){
	$locked=0;
	$button=button("{apply}","EditContact('FFM_CONTACT_PAGE3');RefreshTabsCT();");
	if(!isset($_GET["uidUser"])){
		$ct=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);
		$token="<input type='hidden' id='employeeNumber' name='employeeNumber' value='$ct->employeeNumber'>";

	}else{
		$ct=new contacts($_SESSION["uid"],null,$_GET["uidUser"]);	
		$token="<input type='hidden' id='uidUser' name='uidUser' value='{$_GET["uidUser"]}'>";
	}
	
	if($_GET["uidUser"]<>null){
		if($_SESSION["uid"]<>-100){
			 	$users=new usersMenus();
			 	if(!$users->AllowAddUsers){
					if($_SESSION["uid"]<>$_GET["uidUser"]){$delete=null;$button=null;$locked=1;}
			 	}
			}
	}	
	
	
	$form="
		<input type='hidden' id='tab' value='{$_GET["section"]}'>
		<input type='hidden' id='locked' value='$locked'>
		$token
		<input type='hidden' id='xEditSave' name='xEditSave' value='yes'>
		<hr>		
		<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{homePostalAddress}</div>		
		<table style='width:100%;margin-left:10px'>
			<tr>
				<td class=legend valign='top' nowrap>{address}:</td>
				<td valign='middle' colspan=3><textarea name='homePostalAddress' id='homePostalAddress' style='width:99%;height:100px'>". $ct->homePostalAddress."</textarea></td>	
			</tr>
		</table>
		<hr>		
		<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{postalAddress}</div>		
		
		<table style='width:100%;margin-left:2px'>
			<td valign='top' width=70%>
				<table style='width:100%;margin-left:10px'>
				<tr>
					<td class=legend valign='top' nowrap>{address}:</td>
					<td valign='middle'><textarea name='postalAddress' id='postalAddress' style='width:99%;height:100px'>$ct->postalAddress</textarea></td>	
				</tr>
				</table>
			</td>
			<td valign='top' width=30%>
			<table style='width:100%;margin-left:4px'>
			<tr>
				<td class=legend valign='middle' nowrap>{city}:</td>
				<td valign='middle'>" . Field_text('l',"$ct->l",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE3')")."</td>
			</tr>
				<td class=legend valign='middle' nowrap>{postalCode}:</td>
				<td valign='middle'>" . Field_text('postalCode',"$ct->postalCode",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE3')")."</td>	
			</tr>
			</tr>
				<td class=legend valign='middle' nowrap>{state}:</td>
				<td valign='middle'>" . Field_text('st',"$ct->st",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE3')")."</td>	
			</tr>	
			</tr>
				<td class=legend valign='middle' nowrap>{country}:</td>
				<td valign='middle'>" . Field_text('c',"$ct->c",null,null,null,null,false,"EditContactCheck(event,'FFM_CONTACT_PAGE3')")."</td>	
			</tr>					
			</table>
			</td>
			</tr>
			</table>
		
		";		


	
	$html="
	<form name='FFM_CONTACT_PAGE3' id='FFM_CONTACT_PAGE3'>
		$form
	</form>
	<table  style='width:100%'>
	<tr>
		<td align='right'>
		<td align='right'>$button</td>
		</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function CONTACT_DELETE(){
		$employeeNumber=$_GET["delete-contact"];
		$ct=new contacts($_SESSION["uid"],$employeeNumber);
	
if($_SESSION["uid"]<>-100){
		$ldap=new clladp();
		$user=new user($_SESSION["uid"]);
		$dn="cn=$ct->sn $ct->givenName,ou=$user->uid,ou=People,dc=$user->ou,dc=NAB,$ldap->suffix";
		if($dn==$ct->dn){
			$ldap->ldap_delete($ct->dn,true);
			$sock=new sockets();
			$sock->getfile("OBMContactDelete:$ct->uidNumber");
			
		}else{
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		}
		
	}	
	
}


function mozillaSecondEmailAdd(){
if(!isset($_GET["uidUser"])){
		$ct=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);
	}else{
		$ct=new contacts($_SESSION["uid"],null,$_GET["uidUser"]);	
	}
	$tpl=new templates();
	if(!$ct->mozillaSecondEmailAdd($_GET["mozillaSecondEmail"])){
		echo $tpl->_ENGINE_parse_body("{$_GET["mozillaSecondEmail"]} {failed}\n");
	}else{
		echo $tpl->_ENGINE_parse_body("{$_GET["mozillaSecondEmail"]} {success}\n");
	}
	
}
function mozillaSecondEmailDelete(){
if(!isset($_GET["uidUser"])){
		$ct=new contacts($_SESSION["uid"],$_GET["employeeNumber"]);
	}else{
		$ct=new contacts($_SESSION["uid"],null,$_GET["uidUser"]);	
	}
	$tpl=new templates();
	if(!$ct->mozillaSecondEmailDel($_GET["mozillaSecondEmailDelete"])){
		echo $tpl->_ENGINE_parse_body("{$_GET["mozillaSecondEmail"]} {failed}\n");
	}else{
		echo $tpl->_ENGINE_parse_body("{$_GET["mozillaSecondEmail"]} {success}\n");
	}	
	
}
	
	
	
	
	
	
	
	
	
	
?>