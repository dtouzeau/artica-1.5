<?php
include_once('ressources/class.users.menus.inc');
include_once ("ressources/class.templates.inc");
include_once ("ressources/class.user.inc");
include_once ("ressources/class.fetchmail.inc");
session_start();

if(isset($_GET["script"])){start_js();exit;}
if(isset($_GET["page-index"])){page_index();exit;}
if(isset($_GET["page-display"])){page_list();exit;}
if(isset($_GET["page-right-button"])){page_list_buttons();exit;}
if(isset($_GET["page-modify"])){page_modify_rule();exit;}
if(isset($_GET["fetchmail_rule_id"])){page_save();exit;}
if(isset($_GET["DeleteFetchAccount"])){page_del();exit;}
if(isset($_GET["page-fetchmail-aliases"])){page_fetchmail_aliases_index();exit;}
if(isset($_GET["page-fetchmail-aliases-list"])){echo page_fetchmail_aliases_list($_GET["page-fetchmail-aliases-list"]);exit;}
if(isset($_GET["FetchmailAddAliase"])){page_fetchmail_aliases_add();exit;}
if(isset($_GET["FetchmailDeleteAliase"])){page_fetchmail_aliases_del();exit;}


if(isset($_GET["enable-js-rule"])){page_list_js_enable();exit;}
if(isset($_GET["enable-fetch-rule"])){page_list_js_save();exit;}

if(isset($_GET["find-isp-popup"])){find_isp_popup();exit;}
if(isset($_GET["isp-choose-proto"])){find_isp_proto();exit;}
if(isset($_GET["isp-end"])){find_isp_end();exit;}


function start_js(){
	$page=CurrentPageName();
	if($_GET["uid"]){$uid=$_GET["uid"];}else{$uid=$_SESSION["uid"];}
	
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		if($uid<>$_SESSION["uid"]){
			echo "alert('No privileges!\n');";
			return false;
		}
	}
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{messaging_accounts}');
	$title1=$tpl->_ENGINE_parse_body('{fetchmail_aliases}');
	$server=$tpl->_ENGINE_parse_body('{server_name}');
	$username=$tpl->_ENGINE_parse_body('{username}');
	$delconfirm=$tpl->javascript_parse_text('{fetch_delete_rule_confirm}');
	$fetchaliase=$tpl->javascript_parse_text('{fetchmail_aliases_ask}');
	$GET_RIGHT_ISP_SETTINGS=$tpl->_ENGINE_parse_body('{GET_RIGHT_ISP_SETTINGS}');
	
	$html="
	var uid='$uid';
	
	var x_FetchmailAddAliase= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		LoadAjax('FetchmailAddAliaseDIV','$page?page-fetchmail-aliases-list=$uid');
	}		
	
	
	function StartFetchmailPage(){
		YahooWin5('650','$page?page-display=$uid','$title');
	}
	
	function AliasesFetchmail(){
		YahooWin4(550,'$page?page-fetchmail-aliases=yes&uid=$uid','$title1');
		
	}
	
	function DisplayAccount(){
		Loadjs('wizard.fetchmail.newbee.php?script=yes&uid=$uid')
	}
	
	function SelectRule(num){
		LoadAjax('rightbutton','$page?page-right-button='+num+'&uid=$uid');
	
	}
	
	function ModifyFetchAccount(num){
		YahooWin4('510','$page?page-modify='+num+'&uid='+uid);
		
	
	}
	
	function AddFetchAccount(){
		YahooWin4('510','$page?page-modify=-1&uid='+uid);
	}
	
	function FetchmailAddAliase(uid){
		var email=prompt('$fetchaliase');
		if(email){
			var XHR = new XHRConnection();
			XHR.appendData('FetchmailAddAliase',email);
			XHR.appendData('uid','$uid');
			document.getElementById('FetchmailAddAliaseDIV').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_FetchmailAddAliase);
		
		}
	
	}
	
	function FetchmailDeleteAliase(email){
			var XHR = new XHRConnection();
			XHR.appendData('FetchmailDeleteAliase',email);
			XHR.appendData('uid','$uid');
			document.getElementById('FetchmailAddAliaseDIV').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_FetchmailAddAliase);
	
	}
	
var x_FetchmailSaveAccount= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	YahooWin4Hide();
	DisplayAccount();
}		
	
	function SaveAccount(){
		if(document.getElementById('poll').value==''){
			alert('$server=NULL!');
			return true;
		}
		
		if(document.getElementById('user').value==''){
			alert('$username=NULL!');
			return true;
		}
		
		
		var XHR = new XHRConnection();
		if(document.getElementById('smtp_host')){XHR.appendData('smtp_host',document.getElementById('smtp_host').value);}
		XHR.appendData('fetchmail_rule_id',document.getElementById('fetchmail_rule_id').value);
		XHR.appendData('is',document.getElementById('is').value);
		XHR.appendData('uid',document.getElementById('uid').value);
		XHR.appendData('user',document.getElementById('user').value);
		XHR.appendData('pass',document.getElementById('pass').value);
		XHR.appendData('poll',document.getElementById('poll').value);
		XHR.appendData('proto',document.getElementById('proto').value);
		if(document.getElementById('keep').checked){XHR.appendData('keep',1);}else{XHR.appendData('keep',0);}
		document.getElementById('fetchmail-rule').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_FetchmailSaveAccount);
		}
	
var x_DeleteFetchAccount= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	DisplayAccount();
}	
	
	function DeleteFetchAccount(num,poll){
		if(confirm(poll+': \\n$delconfirm')){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteFetchAccount',num);
			XHR.appendData('uid','$uid');
			XHR.sendAndLoad('$page', 'GET',x_DeleteFetchAccount);		
			}
	}
	
	function LoadFetchmailISPList(){
		YahooWin5(300,'$page?find-isp-popup=yes','$GET_RIGHT_ISP_SETTINGS');
	}
	
	function FetchmailISPSelect(){
		var isp_choose=document.getElementById('isp_choose').value;
		LoadAjax('isp_proto','$page?isp-choose-proto='+isp_choose);
	}
	
	function FetchmailISPProtoSelect(){
		var isp_choose=document.getElementById('isp_choose').value;
		var isp_proto_list=document.getElementById('isp_proto_list').value;
		LoadAjax('isp_end','$page?isp-end=yes&isp='+isp_choose+'&proto='+isp_proto_list);
	}
	
	function ApplyISPFind(){
		document.getElementById('poll').value=document.getElementById('isp_server').value;
		document.getElementById('proto').value=document.getElementById('isp_protos').value;
		document.getElementById('choosen_isp').value=document.getElementById('isp_server_name').value;
		YahooWin5Hide();
	}
	
	StartFetchmailPage();
	
	";
	
	
	echo $html;
}


function page_index(){
	$uid=$_GET["uid"];
	$user=new user($uid);
	
	if(count($user->fetchmail_rules)>0){
		if(count($user->FetchMailMatchAddresses)==0){
			$error=Paragraphe("64-red.png","{no_fetchmail_aliases}","{no_fetchmail_aliases_intro}","javascript:AliasesFetchmail()",null,210,null,1);
		}
		else{$error=Paragraphe("recup-remote-mail.png","{fetchmail_aliases}","{no_fetchmail_aliases_intro}","javascript:AliasesFetchmail()",null,210,null,1);}
	}
	
	
	
	$html="
	<div style='width:100%;background-image:url(img/bg-wizard-panel-email.png);background-repeat:repeat-y;margin:-10px;padding:0px' id='ftechid'>
	<table style='width:100%'>
	<tr>
	<td style='width:120px' valign='top'>&nbsp;</td>
	<td valign='top' style='padding-top:10px'>
		<p class=caption>{wizard_intro}</p>
		<div style='text-align:right;border-bottom:1px dotted #CCCCCC;margin-bottom:5px'><H3>$user->DisplayName</H3></div>
			<table style='width:100%;margin-left:10px'>
			<tr>
			<td valign='top'>
				<table style='width:100%'>
				<tr>
				<td>" . Paragraphe("folder-64-fetchmail.png",'{fetchmail_modify_rules}','{fetchmail_modify_rules_text}',"javascript:DisplayAccount();")."</td>
				</tr>
				<tR>
				<td>" . Paragraphe("folder-64-fetchmail-add.png",'{fetchmail_add_rule}','{fetchmail_add_rule_text}',"javascript:AddFetchAccount();")."<br></td>
				</tr>
				</table>
			</td>
			<td valign='top'>
				<table style='width:100%;margin-left:10px'>
					<tr>
						<td valign='top'>$error</td>
					</tr>
				</table>
			</td>
			</tr>
			</table>
	</div>
	<p>&nbsp;</p>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function page_list_js_enable(){
	$page=CurrentPageName();
	$html="
		var fetchmailRule='{$_GET["enable-js-rule"]}';
		var uid='{$_GET["uid"]}';
		
		var x_page_list_js_enable= function (obj) {
			var results=trim(obj.responseText);
			if(results.length>0){alert(results);}
			DisplayAccount();
		}		
		
		function page_list_js_enable(){
			var XHR = new XHRConnection();
			if(document.getElementById('{$_GET["enable-js-rule"]}_enabled').checked){
				XHR.appendData('enable-fetch-rule',1);
			}else{
			 XHR.appendData('enable-fetch-rule',0);
			}
			XHR.appendData('fetchmail-rule-id','{$_GET["enable-js-rule"]}');
			XHR.sendAndLoad('$page', 'GET');		
		}
	
	page_list_js_enable();
	";
	echo $html;
}

function page_list_js_save(){
	
	$sql="UPDATE fetchmail_rules SET enabled='{$_GET["enable-fetch-rule"]}' WHERE ID='{$_GET["fetchmail-rule-id"]}'"; 
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql";return;}
	$sock=new sockets();
	if($sock->GET_INFO("ArticaMetaEnabled")==1){$sock->getFrameWork("cmd.php?artica-meta-fetchmail-rules=yes");}
	
	$sock->getFrameWork('cmd.php?restart-fetchmail=yes');
	
}

function page_list(){
	$fetch=new Fetchmail_settings();
	$page=CurrentPageName();
	$rules=$fetch->LoadUsersRules($_GET["page-display"]);
	$user=new user($_GET["page-display"]);
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	if(count($rules)>0){
		if(count($user->FetchMailMatchAddresses)==0){
			$error=Paragraphe("64-red.png","{no_fetchmail_aliases}","{no_fetchmail_aliases_intro}","javascript:AliasesFetchmail()",null,210,null,1);
		}else{
			$error=Paragraphe("recup-remote-mail.png","{fetchmail_aliases}","{no_fetchmail_aliases_intro}","javascript:AliasesFetchmail()",null,210,null,1);
		}
	}	
	
	
	
	if(is_array($rules)){
		$tbl="<table style='width:98%'>
		<tr>
			
			<th colspan=2><strong>{user}</strong></th>
			<th><strong>{imap_server_name}</th>
			<th colspan=3><strong>{enabled}</th>
			
			
		</tr>";
		while (list ($num, $ligne) = each ($rules) ){
			
			$enabled=Field_checkbox("{$num}_enabled",1,$ligne["enabled"],"Loadjs('$page?enable-js-rule=$num&uid={$_GET["page-display"]}')");
			if($ligne["enabled"]==0){$color="#E60B03";}else{$color="black";}
			
			$edit=imgtootltip("24-administrative-tools.png","{edit}","ModifyFetchAccount($num)");
			$delete=imgtootltip("ed_delete.gif","{delete}","DeleteFetchAccount($num,'{$ligne["poll"]}');");
			$warn="&nbsp;";
			
			
			if($EnablePostfixMultiInstance==1){
				if(trim($ligne["smtp_host"])==null){
					$warn=imgtootltip("icon_mini_warning.gif","{smtp_host_not_set}");
				}
			}
			
			$tbl=$tbl."
				<tr ". CellRollOver().">
				<td>$warn</td>
				<td style='font-size:11px;color:$color' align='left' nowrap>{$ligne["user"]}</td>
				<td style='font-size:11px;color:$color' align='left' nowrap>{$ligne["poll"]}:{$ligne["proto"]}</td>
				<td style='font-size:11px;color:$color' align='right' nowrap>$enabled</td>
				<td style='font-size:11px;color:$color' align='right' nowrap>$edit</td>
				<td style='font-size:11px;color:$color' align='right' nowrap>$delete</td>
				</tr>";
		}
	}else{
		$tbl="<tr><td colspan=3>{click_on_add}</td></tr>";
	}
	
	$tbl=$tbl."
	<tr>
		<td colspan=5><hr></td>
	</tr>
	</table>";
	
	
	$html="
	<div style='margin-bottom:10px'>
		<table style='width:100%'>
		<tr>
		<td valign='top'>
			<strong style='font-size:18px;margin-top:10px;color:#005447'>{fetchmail_modify_rules}<hr></strong>
		</td>
		<td valign='top'>". imgtootltip("plus-24.png","{add}","AddFetchAccount()")."</td>
		</tr>
		</table>
	</div>
	
	
	<table style='width:100%;'>
	<tr>
		<td valign='top'>
			<div>
				<table style='width:100%'>
					<tr>
						<td valign='top'><div id='fetchmail-rules-js' style='height:250px;overflow:auto'>$tbl</div></td>
						<td valign='top'>$error</td>
					</tr>
				</table>
			</div>
		</td>
		<td valign='top'>
		<div id='rightbutton'></div>
		</td>
	</tr>
	</table>
		
		
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}
function page_list_buttons(){
	$num=$_GET["page-right-button"];
	
	$fetchmail=new Fetchmail_settings();
	$array=$fetchmail->LoadRule($num);
	
	$html="
	<table style='width:100%'>
	<tr>
		<td>". button("{edit}","ModifyFetchAccount($num)")."</td>
	</tr>
	<tr>
		<td>". button("{delete}","DeleteFetchAccount($num,'{$array["poll"]}');")."</td>
	</tr>	
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function fetchmail_PostFixMultipleInstanceList($ou){
	$sock=new sockets();
	$uiid=$sock->uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));	
	$q=new mysql();
	$sql="SELECT `value`,`ip_address` FROM postfix_multi WHERE `key`='myhostname' AND `ou`='$ou' AND `uuid`='$uiid'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$q->mysql_error\n$sql",__FUNCTION__,__FILE__,__LINE__);}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$array[$ligne["value"]]=$ligne["value"];
	}
	$array[null]="{select}";
	return $array;
}


function page_modify_rule(){
	
	
	$user=new user($_GET["uid"]);
	$fetch=new Fetchmail_settings();
	$page=CurrentPageName();
	$array=$fetch->LoadRule($_GET["page-modify"]);
	$sock=new sockets();
	$warn="&nbsp;";
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		if($array["smtp_host"]==null){$warn="<img src='img/icon_mini_warning.gif'>";}
		$smtp_sender=
		"<tr>
			<td width=1%>$warn</td>
			<td valign='top' class=legend nowrap>{local_smtp_host}:</td>
			<td valign='top'>".	Field_array_Hash(fetchmail_PostFixMultipleInstanceList($user->ou),"smtp_host",$array["smtp_host"])."</td>
		</tr>";
	}
	
	$buttonadd="{edit}";
	$advanced=button("{advanced_options}...","UserFetchMailRule({$_GET["page-modify"]},'{$_GET["uid"]}')");
	
	

	
if($_GET["page-modify"]<0){
	$advanced=null;
	$array["keep"]=true;
	$array["proto"]="imap";
	$buttonadd="{add}";
	$find_isp=Paragraphe("64-infos.png","{GET_RIGHT_ISP_SETTINGS}","{GET_RIGHT_ISP_SETTINGS_TEXT}","javascript:Loadjs('$page?find-isp-js=yes')");
}	
	
	$arraypr=array("imap"=>"imap","pop3"=>"pop3");
	$keep=Field_checkbox('keep',1,$array["keep"]);		
	
$html="
	<div style='margin-bottom:10px'><br>
		<h3 style='font-size:18px;color:#005447;border-bottom:1px solid #005447'>{$array["poll"]}</h3>
	</div>
	
	<div style='width:100%;overflow:auto' id='fetchmail-rule'>
	<form name='ffmfetch'>
	<input type='hidden' name='fetchmail_rule_id' id='fetchmail_rule_id' value='{$_GET["page-modify"]}'>
	<input type='hidden' name='is' id='is' value='$user->mail'>
	<input type='hidden' name='uid' id='uid' value='{$_GET["uid"]}'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
			<table style='width:350px;'>
			<tr>
				<td valign='top' colspan=2 style='font-size:13px;font-weight:bold;padding-bottom:10px;padding-top:15px'>{session_information}:<br></td>
			</tr>
			<tr>
				<td width=1%>&nbsp;</td>
				<td class=legend nowrap>{username}:</td>
				<td>" . Field_text('user',$array["user"])."</td>
			</tr>
			<tr>
				<td width=1%>&nbsp;</td>
				<td class=legend nowrap>{password}:</td>
				<td>" . Field_password('pass',$array["pass"])."</td>
			</tr>	
			
			<tr>
				<td valign='top' colspan=3 style='font-size:13px;font-weight:bold;padding-bottom:10px;padding-top:15px'>{server_information}:<br></td>
			</tr>
			<tr>
				<td width=1%>&nbsp;</td>
				<td class=legend nowrap valign='top'>{imap_server_name}:</td>
				<td>
					<table style='width:100%'>
					<tr>
						<td valign='top'>
							" . Field_text('poll',$array["poll"])."
						</td>
						<td valign='top'>" .
							imgtootltip('22-infos.png','<strong>{GET_RIGHT_ISP_SETTINGS}</strong><br>{GET_RIGHT_ISP_SETTINGS_TEXT}',
							"LoadFetchmailISPList()")."&nbsp;<span id='choosen_isp' style='font-weight:bolder'></span>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			
			<tr>
				<td width=1%>&nbsp;</td>
				<td class=legend nowrap>{protocol}:</td>
				<td>" . Field_array_Hash($arraypr,'proto',$array["proto"])."</td>
			</tr>	
			$smtp_sender
			<tr>
				<td width=1%>&nbsp;</td>
				<td class=legend nowrap>{not_delete_messages}:</td>
				<td>$keep</td>
			</tr>		
			<tr>
				
				<td valign='top' colspan=3 align='right'>$advanced</td>
			</tr>
			</table>
	
	</td>
		
	</tr>
	</table>
	</form>
	<hr>
	<div style='text-align:right;width:100%'>". button("{cancel}","YahooWin4Hide()")."
	&nbsp;&nbsp;&nbsp;". button("$buttonadd","SaveAccount()")."
	
	</div>
	</div>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}

function page_save(){
	
	$ldap=new clladp();
	$user=new user($_GET["uid"]);
	$fetchmail=new Fetchmail_settings();
	if($_GET["fetchmail_rule_id"]>-1){
		$fetchmail->EditRule($_GET,$_GET["fetchmail_rule_id"]);
		
	}else{
		
		if(!$fetchmail->AddRule($_GET)){
			echo "->AddRule Class, Mysql error !\n";
			return;
			
		}
	}
	
	$fetchmail=new fetchmail();
	$fetchmail->Save();
	
}

function page_fetchmail_aliases_index(){
	$uid=$_GET["uid"];
	$tpl=new templates();
	$list=page_fetchmail_aliases_list($uid);
	
	$add=RoundedLightWhite(Paragraphe("64-alias-add.png","{add_alias}","{add_alias_text}","javascript:FetchmailAddAliase('$uid')",'{add_alias}',220));
	
	$form="<table style='width:100%'>
	<tr>
		<td valign='top' width=100%>
			<div id='FetchmailAddAliaseDIV'>
			$list
			</div>
		</td>
		<td valign='top'>
		$add
		</td>
	</tr>
	</table>
	
	";
	
	
	$html="<H1>{fetchmail_aliases}</H1>
	<p class=caption>{fetchmail_aliases_text}</p>
	$form
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function page_fetchmail_aliases_list($uid){
	$users=new user($uid);
	if(!is_array($users->FetchMailMatchAddresses)){return null;}
	
	$html="<table style='width:99%'>";
	
	while (list ($num, $ligne) = each ($users->FetchMailMatchAddresses) ){
		if($ligne==null){continue;}
		$html=$html . "
		<tr ". CellRollOver().">
			<td><strong style='font-size:13px'>$ligne</td>
			<td width=1%>" . imgtootltip('ed_delete.gif',"{delete}","FetchmailDeleteAliase('$ligne')")."</td>
		</tr>";	
		
		
	}
	
	$html=$html . "</table>";
	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body($html); 
	}
	
function page_fetchmail_aliases_add(){
	$email=trim($_GET["FetchmailAddAliase"]);
	
	$ldap=new clladp();
	$hash=$ldap->find_users_by_mail($email);
	if($hash>0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{error_alias_exists}');
		exit;
	}
	
	$uid=$_GET["uid"];
	$users=new user($uid);
	$users->add_alias_fetchmail($email);
	
}
function page_fetchmail_aliases_del(){
	$email=trim($_GET["FetchmailDeleteAliase"]);
	$uid=$_GET["uid"];
	$users=new user($uid);
	$users->del_alias_fetchmail($email);	
}





function page_del(){
	$num=$_GET["DeleteFetchAccount"];
	$uid=$_GET["uid"];
	$fetchmail=new Fetchmail_settings();
	$fetchmail->DeleteRule($num,$uid);
	}
	
function find_isp_popup(){
	$isp=new fetchmail();
	$array=$isp->ISPDB;
	$newarray=$array["ARRAY_POP_ISP"]+$array["ARRAY_IMAP_ISP"];
	
	while (list ($num, $ligne) = each ($newarray) ){
		$isp_list[$num]=$num;
	}
	ksort($isp_list);
	$isp_list[null]="{select}";
	
	$html="
	<p class=caption>{GET_RIGHT_ISP_SETTINGS_TEXT}</p>
	<table class=table_form>
	<tr>
		<td valign='top' class=legend>{ISP}:</td>
		<td valign='top'>".Field_array_Hash($isp_list,'isp_choose',null,"FetchmailISPSelect()",null,0,"font-size:13px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{proto}:</td>
		<td valign='top'><span id='isp_proto'></span></td>
	</tr>
	<tr>
		<td colspan=2 align='right'><span id='isp_end'></span></td>
	</tr>	
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function find_isp_proto(){
	$ispname=$_GET["isp-choose-proto"];
	$isp=new fetchmail();
	$array=$isp->ISPDB;	
	if($array["ARRAY_POP_ISP"][$ispname]<>null){
		$arrayz["POP3"]="POP3";
	}
	
	if($array["ARRAY_IMAP_ISP"][$ispname]<>null){
		$arrayz["IMAP"]="IMAP";
	}

	$arrayz[null]="{select}";
	$html=Field_array_Hash($arrayz,'isp_proto_list',null,"FetchmailISPProtoSelect()",null,0,"font-size:13px");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function find_isp_end(){
	$isp=$_GET["isp"];
	$proto=$_GET["proto"];
	$isps=new fetchmail();
	$array=$isps->ISPDB;
	if($proto=="POP3"){$ar=$array["ARRAY_POP_ISP"];}
	if($proto=="IMAP"){$ar=$array["ARRAY_IMAP_ISP"];}
	$server=$ar[$isp];

	$html="
	<input type='hidden' name='isp_server_name' id='isp_server_name' value='{$_GET["isp"]}'>
	<input type='hidden' name='isp_server' id='isp_server' value='$server'>
	<input type='hidden' name='isp_protos' id='isp_protos' value='". strtolower($proto)."'>
	<hr>
	<p class=caption>$isp ($proto)</p><hr>".button("{apply}","ApplyISPFind()")."
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
	
}


?>