<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
include_once('ressources/class.templates.inc');
include_once('ressources/class.mailboxes.inc');
writequeries();

$mailb=new MailBoxes();
if($mailb->errors<>null){
	$tpl=new templates('{title}',$mailb->errors);
	echo $tpl->web_page;
	exit;
	}




if(isset($_GET["AddDomain"])){AddDomain();exit;}
if(isset($_GET["add_maiboxes"])){add_maiboxes();exit;}
if(isset($_GET["edit_maiboxes"])){edit_maiboxes();exit;}
if(isset($_GET["expand_domain"])){expand_domain();exit;}
if(isset($_GET["edit_transport"])){edit_transport();exit;}
if(isset($_GET["save_transport"])){save_transport();exit;}
if(isset($_GET["xdelete_transport_text"])){xdelete_transport_text();exit;}
if(isset($_GET["delete_transport_confirm"])){delete_transport_confirm();exit;}
if(isset($_GET["xdelete_alias_text"])){xdelete_alias_text();exit;}
if(isset($_GET["delete_alias_confirm"])){delete_alias_confirm();}
if(isset($_GET["xdelete_domain_text"])){xdelete_domain_text();exit;}
if(isset($_GET["delete_domain_confirm"])){delete_domain_confirm();exit;}
if(isset($_GET["MailBoxStorage"])){MailBoxStorage();exit;}
if(isset($_GET["add_storage_mailbox"])){add_storage_mailbox();exit;}

$toolbarr=ToolBarr() . "{PAGE_TEXT}<br><div id='domain_list'>" . List_domains() . "</div>";


$html=$toolbarr;

$tpl=new templates('{title}',$html);
echo $tpl->web_page;



function ToolBarr(){
	$page=CurrentPageName();
$html="
<div id=\"green_top_list_container\">
<ul id=\"green_top_list\">
<li><a href=\"javascript:AddDomain();\">{bt_add_domain}</a></li>
</ul>
</div>
<div style='clear:both;margin:4px'>&nbsp;</div>
	
	";return $html;
	
}


function AddDomain(){
	$boxes=new MailBoxes();
	$boxes->add_domain($_GET["AddDomain"]);
	echo List_domains();}
	
function List_domains(){
	$boxes=new MailBoxes();
	$hash=$boxes->Hash_get_domains_name();
	if(!is_array($hash)){return null;}
	
	$html="<table style='width:80%' alifn='center'>
	<tr><td colspan=4 style='font: normal 13px \"Lucida Sans Unicode\";background-color:black;color:white;font-size:13px;font-weight:bold;padding:4px;letter-spacing:3px;border:1px solid #CCCCCC'>{ARRAY_TITLE}</TD></TR>
	";
	
	while (list ($num, $ligne) = each ($hash) ){
		$html=$html . "<tr>
		<td width=16px class='table_row1' align='center' valign='top'><img src='img/upload.gif'></td>
		<td  class='table_row_text' valign='top'>
		<a href=\"javascript:expand_domain('{$ligne["domain"]}');\" 
		onMouseOver=\"javascript:AffBulle('{js_expand_domain}');\" 
		OnMouseOut=\"javascript:HideBulle();\"
		>{$ligne["domain"]}</a>
		<div id='expand_{$ligne["domain"]}'></div>
		</td>
		<td width=1%  class='table_row1' valign='top'><a href=\"javascript:DelDomain('{$ligne["domain"]}','');\"><img src='img/deluser.gif' onMouseOver=\"javascript:AffBulle('{js_del_domain}');\" OnMouseOut=\"javascript:HideBulle();\"></a></td>		
		<td width=1%  class='table_row1' valign='top'><a href=\"javascript:adduser('{$ligne["domain"]}','');\"><img src='img/adduser.gif' onMouseOver=\"javascript:AffBulle('{js_add_user}');\" OnMouseOut=\"javascript:HideBulle();\"></a></td>
		</tr>";
		
		
	}
	$tpl=new Templates();
	$html=$tpl->_parse_body($html);
	return $html . "</table>";
}


function edit_maiboxes(){
	writelogs("receive requests",__FUNCTION__,__FILE__);
	$mailbox=new MailBoxes();
	$remote_server=trim($_GET["remote_server"]);
	$email_source=$_GET["email_source"];
	$domain_source=$_GET["domain_source"];
	$username_from=$_GET["username_from"];
	$username_to=$_GET["username_to"];
	
	if($remote_server<>null){
		$mailbox->edit_Transport_maps($domain_source,$remote_server);
		exit;
		}
		
	if($username_from<>null){
		$username_from=str_replace('*','',$username_from);
		$username_to=str_replace('*','',$username_to);
		if(!preg_match('#\@#',$username_to)){
			$username_to="$username_to@$domain_source";
		}
		
		
		writelogs("INFOS:mailbox->add_alias($username_from@$domain_source,$username_to,$domain_source)",__FUNCTION__,__FILE__);
		$mailbox->add_alias("$username_from@$domain_source",$username_to,$domain_source);
		exit;
	}
	
	
	
	
	
}


function add_maiboxes(){
	$email=$_GET["add_maiboxes_user"];
	$domain=$_GET["add_maiboxes"];	
	$mailbox=new MailBoxes();
	
	if($email==null){
	$transport=$mailbox->Get_Transport_maps($domain);	
	if($transport<>null){
		echo DIV_SHADOW('{ERROR_CANNOT_ADD_TRANSPORT_EXISTS}','windows');
		exit;
		
	}
	}else{
		
	}
	
	$email_source=$_GET["add_maiboxes_user"];
	$domain=$_GET["add_maiboxes"];
	$goto=$mailbox->Get_Goto_alias($email_source);
	$transport=$mailbox->Get_Transport_maps($domain);
	
	$js_check="OnClick=\"javascript:mailbox_EnableAll();\"";
	
	
	$all=Field_checkbox("allusers",1,0,$js_check);
	if($email<>null){
		$tbl_email_from=explode('@',$email);
		$email=$tbl_email_from[0];
		if($tbl_email_from[0]==null){
			$email="*";
			$email_disabled="disabled=true";
			$transport_disabled="disabled=true";
			$all=Field_checkbox("allusers",1,1,$js_check);}
		else{$all=Field_checkbox("allusers",1,0,$js_check);
			
		}
	}
	if($goto<>null){
	$tbl=explode('@',$goto);
	if($tbl[0]==null){
		$goto="*$goto";
	}
	}
	if($goto<>null && $email_source<>null){$transport_disabled="disabled=true";}
	
	//style='width:100%;padding:5px;border:1px solid #005447' 
	$html="
	<input type='hidden' id='edit_maiboxes' value='1'>
	<input type='hidden' id='domain_source' value='$domain'>
	<input type='hidden' id='email_source' value='$email_source'>
	
	<table class='table_form'>
	<td colspan=2><H2>{create_user_title} ($domain)</H2></td>
	<tr>
		<td class='table_form_legend'>{create_user_all}</td>
		<td class='table_form_data'>$all</td>
	</tr>	
	<tr>
		<td class='table_form_legend'>{create_user_email}</td>
		<td class='table_form_data' ><input type='text' id='username_from' value='$email' onkeypress=\"javascrit:DisableFormUser();\" $email_disabled>&nbsp;@$domain</td>
	</tr>
	<tr>
		<td class='table_form_legend'>{create_user_local}</td>
		<td class='table_form_data' ><input type='text' id='username_to' value='$goto' onkeypress=\"javascrit:DisableFormUser();\"></td>
	</tr>
	<tr>
		<td class='table_form_legend'>{create_user_remote_server}</td>
		<td class='table_form_data'><input type='text' id='remote_server' onkeypress=\"javascrit:DisableFormUser();\" $transport_disabled></td>
	</tr>
	<tr>
		<td class='table_form_button'  colspan=2><input type='button' value='Submit&nbsp;&raquo;' OnClick=\"javascript:Edituser();\"></td>
	</tr>			
	
	
	</table>
	
	";
	
	echo DIV_SHADOW($html,'windows');
	}
	
function expand_domain(){
	writelogs("receive requests",__FUNCTION__,__FILE__);
	$domain=$_GET["expand_domain"];
	$mailbox=new MailBoxes();	
	
	$html="<table style='width:250%' align='center' border=0 class='table_little' style='border:1px solid #CCCCCC'>
	<tr>
	<td align='center' style='border-bottom:1px solid #CCCCCC'>&nbsp;</td>
	<td align='center' style='border-bottom:1px solid #CCCCCC'>{expand_domain}</td>
	<td align='center' style='border-bottom:1px solid #CCCCCC'>{expand_domain_goto}</td>
	<td align='center' style='border-bottom:1px solid #CCCCCC'>{expand_domain_object}</td>
	<td align='center' style='border-bottom:1px solid #CCCCCC'>{expand_domain_mailbox}</td>
	<td align='center' style='border-bottom:1px solid #CCCCCC'>{expand_domain_target}</td>
	<td align='center' style='border-bottom:1px solid #CCCCCC'>&nbsp;</td>
	";
	
	$transport=$mailbox->Get_Transport_maps($domain);	
	
	writelogs("if there is a transport for $domain ???->$transport",__FUNCTION__,__FILE__);
	
	if($transport<>null){
		$transport=str_replace("smtp:",'',$transport);
		$html=$html."<tr>
		<td style='width:50px'  valign='MIDDLE' align='center'>&nbsp;<a href=\"javascript:edit_transport('$domain');\"  onMouseOver=\"javascript:AffBulle('{js_edit_transport}');\" OnMouseOut=\"javascript:HideBulle();\">
			<img src='img/edit.jpg'>&nbsp;</td>
		<td style='width:50px'  valign='MIDDLE' align='center'>&nbsp;$domain&nbsp;</td>
		<td style='width:50px' valign='MIDDLE' align='center'>&nbsp;<img src='img/forwd_22.gif'>&nbsp;</td>
		<td style='width:50px' valign='MIDDLE' align='center'>&nbsp;<img src='img/computer.gif'>&nbsp;</td>
		<td valign='MIDDLE'  style='width:50px' align='center'>&nbsp;$transport</td>
		<td valign='MIDDLE'  style='width:50px' align='center'>&nbsp;
		<a href=\"javascript:delete_transport('$domain');\"  onMouseOver=\"javascript:AffBulle('{js_del_transport}');\" OnMouseOut=\"javascript:HideBulle();\"><img src='img/deluser.gif'>&nbsp;</td>
		</tr>";
		$html= $html . "</table>";
		
		$tpl=new Templates();
		echo $tpl->_parse_body($html);
		exit;
		
	}
	$virtual_domain=new domain();
	$hash=$mailbox->Hash_get_mailbox_list($domain);
	
	
	if(!is_array($hash)){
		writelogs("INFOS:Array is returned empty by Hash_get_mailbox_list($domain)",__FUNCTION__,__FILE__);
		
		return null;}
		
	while (list ($num, $ligne) = each ($hash) ){
		$address=$ligne["address"];
		$s_address=$ligne["address"];
		$tbl=explode('@',$address);

		if($tbl[0]==null){
			$address="*$address";
		}
		
		
		$goto=$ligne["goto"];
		$tbl=explode('@',$goto);
		
		$s_goto=$ligne["goto"];
		if($mailbox->enable_storage==true){
			if ($virtual_domain->IsLocalDomain($tbl[1])){
				$gotourl="<a href=\"javascript:MailBoxStorage('$goto','{$tbl[1]}');\"
				onMouseOver=\"javascript:AffBulle('{js_edit_mailbox_storage}');\" 
				OnMouseOut=\"javascript:HideBulle();\" >";
				
				if($mailbox->MailBoxes_isExists($goto)){
					$icon_mailbox="<img src='img/mailbox_sorage.gif'>";
				}else{$icon_mailbox="&nbsp;";}
				
				}
			}
		
		if($tbl[0]==null){
			$gotourl=null;
			$goto="*$goto";
		}
		
		
		
		
		$html=$html."<tr>
		<td style='width:50px'  valign='MIDDLE' align='center'>&nbsp;
			<a href=\"javascript:adduser('$domain','$s_address');\" onMouseOver=\"javascript:AffBulle('{js_edit_alias}');\" OnMouseOut=\"javascript:HideBulle();\">
				<img src='img/edit.jpg'>&nbsp;
		</td>
		<td style='width:50px'  valign='MIDDLE' align='left'>&nbsp;$address&nbsp;</td>
		<td style='width:50px' valign='MIDDLE' align='center'>&nbsp;<img src='img/forwd_22.gif'>&nbsp;</td>
		<td style='width:50px' valign='MIDDLE' align='center'>&nbsp;<img src='img/mailbox.gif'>&nbsp;</td>
		<td style='width:50px' valign='MIDDLE' align='center'>&nbsp;$icon_mailbox&nbsp;</td>
		<td valign='MIDDLE'  style='width:50px' align='left'>&nbsp;$gotourl$goto</a></td>
		<td valign='MIDDLE'  style='width:50px' align='center'>&nbsp;
		<a href=\"javascript:delete_alias('$s_address','$domain');\"  onMouseOver=\"javascript:AffBulle('{js_del_alias}');\" OnMouseOut=\"javascript:HideBulle();\"><img src='img/deluser.gif'>&nbsp;</td>
		</tr>";
		
	}
	$html= $html . "</table>";	
		$tpl=new Templates();
		echo $tpl->_parse_body($html);
		exit;	
}


function MailBoxStorage($email=null){
	$user=$email;
	
	if(isset($_GET["MailBoxStorage"])){$user=$_GET["MailBoxStorage"];}
	
	$mailbox=new MailBoxes();
	
	if($mailbox->MailBoxes_isExists($user)==false){
		$html="<input type='hidden' id='user' value='$user'>
		<input type='hidden' id='add_pop3_imap_text' value=\"{add_pop3_imap_text}\">
		<table class='table_form'>
	<td colspan=2><H2>$user&nbsp;{mailboxstorage_title}</H2></td>
	</tr>
	<tr>
		<td class='table_form_legend'>{mailboxstorage_not_define}</td>
		<td class='table_form_data'><input type='button' value='{bt_add_mailbox}' OnClick=\"javascript:add_storage_mailbox('$user')\"></td>
	</tr>	
	</table>";	
	echo DIV_SHADOW($html,'windows');
	exit;
	}
	
	$path=$mailbox->MailBoxes_get_path($user);
	
	$html="
	<input type='hidden' id='user' value='$user'>
	<input type='hidden' id='edit_pop3_imap_text' value=\"{edit_pop3_imap_text}\">
	<table class='table_form'>
	<td colspan=2><H2>$user&nbsp;{mailboxstorage_title}</H2></td>
	</tr>
	<tr>
		<td class='table_form_legend'>{mailboxstorage_path}</td>
		<td class='table_form_data'>$path</td>
	</tr>	
	<tr>
		<td class='table_form_legend'>{mailboxstorage_password}</td>
		<td class='table_form_data' ><input type='password' id='password' value='$user' ></td>
	</tr>
		<td class='table_form_button'  colspan=2><input type='button' value='Submit&nbsp;&raquo;' OnClick=\"javascript:edit_storage_mailbox('$user');\"></td>
	</tr>			
	
	
	</table>
	
	";
	
	echo DIV_SHADOW($html,'windows');
	
}

function delete_domain_confirm(){
	writelogs("Receive " . $_GET["delete_domain_confirm"],__FUNCTION__,__FILE__);
	$domain=$_GET["delete_domain_confirm"];
	$mailb=new MailBoxes();
	$mailb->Delete_entire_domain($domain);
	}
function edit_transport(){
	$domain=$_GET["edit_transport"];
	$mailbox=new MailBoxes();	
	$transport=$mailbox->Get_Transport_maps($domain);
	$transport=str_replace('smtp:','',$transport);
	echo $transport;
}
function save_transport(){
	$domain=$_GET["save_transport"];
	$mailbox=new MailBoxes();
	$mailbox->edit_Transport_maps($domain,$_GET["save_transport_target"]);	
	}
function xdelete_transport_text(){
	$html="{xdelete_transport_text}";
	$tpl=new Templates();
	echo trim($tpl->_parse_body($html));	
	}
function xdelete_alias_text(){
	$html="{xdelete_alias_text}";
	$tpl=new Templates();
	echo trim($tpl->_parse_body($html));		
	}
function delete_transport_confirm(){
	$domain=$_GET["delete_transport_confirm"];
	$mailbox=new MailBoxes();
	$mailbox->delete_transport($domain);
	}
function delete_alias_confirm(){
	$mailbox=new MailBoxes();	
	$mailbox->alias_delete($_GET["delete_alias_confirm"]);
	echo List_domains();
	}
function xdelete_domain_text(){
$html="{xdelete_domain_text}";
	$tpl=new Templates();
	echo trim($tpl->_parse_body($html));		
}
function add_storage_mailbox(){
	$email=$_GET["add_storage_mailbox"];
	$password=$_GET["add_storage_mailbox_password"];
	$mailbox=new MailBoxes();
	$mailbox->MailBoxes_adduser($email,$password);
	echo MailBoxStorage($email);
	
}
	
?>