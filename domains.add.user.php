<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	
	if(isset($_GET["index"])){INDEX_CREATE();exit;}
	js();
	
function js(){
	$tpl=new templates();
	
	if(isset($_GET["encoded"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
	$ou=$_GET["ou"];
	
	
	$title=$tpl->_ENGINE_parse_body('{add_user}::'.$ou);
	$ou_encoded=base64_encode($ou);
	$page=CurrentPageName();
	
	$js_add=file_get_contents('js/edit.user.js');
	
	if($ou==null){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body('{error_please_select_an_organization}');
		die("alert('$error')");
	}
	
	$html="
		$js_add
		function loadAdduser(){
			YahooUser(670,'$page?index=yes&ou=$ou_encoded&gpid={$_GET["gpid"]}','$title');
		
		}
		
		loadAdduser();
		
		
	";
	echo $html;
	
}
	

function INDEX_CREATE(){
	$ldap=new clladp();
	if($_GET["ou"]==null){die();}
	$_GET["ou"]=base64_decode($_GET["ou"]);
	$hash=$ldap->hash_groups($_GET["ou"],1);

	
	$domains=$ldap->hash_get_domains_ou($_GET["ou"]);
	
	if(count($domains)==0){
		$users=new usersMenus();
		if($users->POSTFIX_INSTALLED){
			$field_domains=Field_text('user_domain',"{$_GET["ou"]}.com","width:85px");
		}else{
			if(!preg_match("#(.+?)\.(.+)#",$_GET["ou"])){$dom="{$_GET["ou"]}.com";}else{$dom="{$_GET["ou"]}";}
			$field_domains="<code><strong>$dom</strong></code>".Field_hidden('user_domain',"$dom","width:120px");
			
		}
		
		
		
	}else{
		$field_domains=Field_array_Hash($domains,'user_domain');
	}
	
	$tpl=new templates();
	$hash[null]="{select}";
	$groups=Field_array_Hash($hash,'group_id',$_GET["gpid"],"style:font-size:13px;padding:3px");
	$error_no_password=$tpl->javascript_parse_text("{error_no_password}");	
	$error_no_userid=$tpl->javascript_parse_text("{error_no_userid}");
	
	$title="{$_GET["ou"]}:{create_user}";
	
	$step1="
	<table style='width:100%' class=form OnMouseOver=\"javascript:HideExplainAll(1)\">
	<tr>
	<td valign='top' width=1%><img src='img/chiffre1_32.png'></td>
	<td valign='top'>
	<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{name_the_new_account_title}:</div>
	" . Field_text('new_userid',null,"font-size:14px;padding:3px;font-weight:bold;color:#C80000",null,"UserAutoChange_eMail()",null,false,"UserADDCheck(event)") ."

	</td>
	</tr>
	</table>";
	
	$step2="
	<table style='width:100%' class=form OnMouseOver=\"javascript:HideExplainAll(2)\">
	<tr>
	<td valign='top' width=1%><img src='img/chiffre2_32.png'></td>
	<td valign='top'>
	<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{email}</div><br>
	<input type='hidden' name='email' value='' id='email'>
	<span id='prefix_email' style='width:90px;border:1px solid #CCCCCC;padding:2px;font-size:11px;font-weight:bold;margin:2px'>
	</span>@$field_domains&nbsp;
	<div style='text-align:right;font-size:11px;'><i><a href='javascript:ChangeAddUsereMail();'>{change}</a></i>
	
	</td>
	</tr>
	</table>";
	
	$step3="
	<table style='width:100%' class=form OnMouseOver=\"javascript:HideExplainAll(3)\">
	<tr>
	<td valign='top' width=1%><img src='img/chiffre3_32.png'></td>
	<td valign='top'>
	<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{password}</div>
	" . Field_password('password',null,"font-size:14px;padding:3px;width:150px;letter-spacing:3px",null,null,null,false,"UserADDCheck(event)") ."
	</td>
	</tr>
	</table>
	";
	
	$step4="
	<table style='width:100%' class=form OnMouseOver=\"javascript:HideExplainAll(4)\">
	<tr>
	<td valign='top' width=1%><img src='img/chiffre4_32.png'></td>
	<td valign='top'>
	<div style='font-size:14px;font-weight:bold;margin-bottom:5px'>{group}</div>
	<div style='font-size:13px;margin-bottom:5px'>{select_user_group_title}:</div><br>$groups
	</td>
	</tr>
	</table>
	";
	
if($_GET["gpid"]>0){$step4="<input type='hidden' id='group_id' value='{$_GET["gpid"]}'>";}
	
	$html="
	<input type='hidden' id='ou-mem-add-form-user' value='{$_GET["ou"]}'>
	<input type='hidden' id='ou' value='{$_GET["ou"]}'>
	<div id='adduser_ajax_newfrm' style='margin-top:5px'>
	<table style='width:100%'>
	<tr>
	<td valign='top' style='width:50%'>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=290px>$step1</td>
		</tr>
		<tr>
			<td valign='top'>$step2</td>
		</tr>
		<tr>
			<td valign='top'><br>$step3</td>
		</tr>
			<td valign='top'><br>$step4</td>
		</tr>
		<tr>
			<td align='right'>
				<hr>". button("{add}","UserADDSubmit()")."
			</td>
		</tr>			
		</table>
	</td>
	<td valign='top' style='width:50%'>
			<center style='margin-bottom:8px'><img src='img/add-woman-256.png'></center>		
			<div class=explain id='text-1'>{name_the_new_account_explain}</div>
			<div class=explain id='text-2'>{user_email_text}</div>
			<div class=explain id='text-3'>{select_user_group_text}</div>
			<div class=explain id='text-4'>{give_password_text}</div>
			
			
	</td>
	</tr>	
	</table>
	</div>
	
	<script>
		function VerifyFormAddUserCheck(){
			var pass;
			var uid;
			pass=document.getElementById('password').value;
			uid=document.getElementById('new_userid').value;
			if(uid.length<1){alert('$error_no_userid');return false;}
			if(pass.length<1){alert('$error_no_password');return false;}
			return true;
			}
		
		function UserADDSubmit(){
			if(!VerifyFormAddUserCheck()){return;}
			UserADD();
		}
	
	
		function UserADDCheck(e){
			if(checkEnter(e)){UserADDSubmit();}
		}
		
		function HideExplainAll(id){
			document.getElementById('text-1').style.display='none';
			document.getElementById('text-2').style.display='none';
			document.getElementById('text-3').style.display='none';
			document.getElementById('text-4').style.display='none';  
			if(document.getElementById('text-'+id)){
				document.getElementById('text-'+id).style.display='block';
				} 
			
		}
		HideExplainAll();
</script>	
	
	";



echo $tpl->_ENGINE_parse_body($html);
}
?>