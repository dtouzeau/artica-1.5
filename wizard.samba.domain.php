<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');	
include_once('ressources/class.sockets.inc');	
include_once("ressources/class.artica.inc");
include_once("ressources/class.samba.inc");
include_once("ressources/class.user.inc");
include_once("ressources/class.computers.inc");

if(isset($_POST["server_name"])){main_save_server();exit;}
if(isset($_POST["add_computer"])){main_add_computer();exit;}
if(isset($_POST["root_password"])){main_save_root();exit;}
if(isset($_GET["script"])){echo switch_script();exit;}
if(isset($_GET["main"])){echo switch_main();exit;}
$users=new usersMenus();
if($users->AsArticaAdministrator==false){die();}


function switch_script(){
	
	switch ($_GET["script"]) {
		case "domain":echo scripts_pdc();exit;
			
			break;
	
		default:
			break;
	}
	
}


function switch_main(){
	switch ($_GET["main"]) {
		case "pdc_start":echo main_pdc_start();exit;break;
		case "pdc_2":echo main_pdc_root();exit;break;
		case "pdc_3":echo main_pdc_add_computer();exit;break;
		case "pdc_4":echo main_pdc_howto1();exit;break;
		case "pdc_5":echo main_pdc_howto2();exit;break;
		case "pdc_6":echo main_pdc_howto3();exit;break;
		case "pdc_7":echo main_pdc_howto4();exit;break;
		default:
			break;
	}	
	
}

function main_save_server(){
	
	$server_name=$_POST["server_name"];
	$workgroup=$_POST["workgroup"];
	$samba=new samba();
	
	$samba->main_array["global"]["netbios name"]=$server_name;
	$samba->main_array["global"]["workgroup"]=$workgroup;
	$samba->main_array["global"]["domain logons"]="yes";
	$samba->main_array["global"]["preferred master"]="yes";
	$samba->main_array["global"]["domain master"]="yes";
	$samba->main_array["global"]["local master"]="yes";
	$samba->main_array["global"]["os level"]=34;
	$samba->SaveToLdap();
	
}
function main_save_root(){
	if($_POST["root_password"]==null){return null;}
	$group=new groups();
	$group->BuildOrdinarySambaGroups();
	$tpl=new templates();
	$user=new user();
	if(!$user->CreateModifyRootUSer($_POST["root_password"])){
		echo $user->ldap_error;
	}else{
		echo $tpl->_ENGINE_parse_body('{success}');
	}
}
function main_add_computer(){
	$computer=$_POST["add_computer"];
	if(trim($computer)<>null){
		$comp=new computers();
		$comp->uid=$computer;
		if(!$comp->Add()){echo $comp->ldap_error;}
	}
	
}


function scripts_pdc(){
	$page=CurrentPageName();
	$title=
	
	$html="
	var root_password_text='{root_password}';
	var root_error_pass='{root_error_pass}';
	var add_computer_title='{add_computer_title}';
	var add_user_title='{add_user_title}';
	YahooWin(550,'$page?main=pdc_start','{domain_controler}');
	
	
	function  samba_pdc_step2(){
		var server_name=document.getElementById('server_name').value;
		var workgroup=document.getElementById('workgroup').value;
		var XHR = new XHRConnection();
		XHR.appendData('server_name',server_name);
		XHR.appendData('workgroup',workgroup);
		XHR.sendAndLoad('$page', 'POST');
		YahooWin(550,'$page?main=pdc_2',root_password_text);
		}
		
	var x_samba_pdc_step3= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		YahooWin(550,'$page?main=pdc_3',add_computer_title);
		}		
		
	function samba_pdc_step3(){
	  var root_password=document.getElementById('root_password').value;
	  var root_password2=document.getElementById('root_password2').value;
	  if(root_password!==root_password2){
	    alert(root_error_pass);
	    return;
	  }
	 var XHR = new XHRConnection();
	 XHR.appendData('root_password',root_password);
	 XHR.sendAndLoad('$page', 'POST',x_samba_pdc_step3);
	 
	}
	
	var x_samba_pdc_step4= function (obj) {
		var results=obj.responseText;
		if(results.length>0){
			alert(results);
			YahooWin(550,'$page?main=pdc_3',add_computer_title);
			return;
		}
		YahooWin(650,'$page?main=pdc_4');
	}	
	
	function samba_pdc_step4(){
		var computer=document.getElementById('computer_name').value;
		var XHR = new XHRConnection();
		XHR.appendData('add_computer',computer);
		XHR.sendAndLoad('$page', 'POST',x_samba_pdc_step4);
	}
	

	
	";
	
	$tpl=new  templates();
	return $tpl->_ENGINE_parse_body($html);
	
}


function main_pdc_start(){
	$samba=new samba();
	
	$server_name=$samba->main_array["global"]["netbios name"];
	$workgroup=$samba->main_array["global"]["workgroup"];
	
	$html="
<table style='width:100%'>
<tr>
<td valign='top'><img src='img/48-samba-pdc.png'></td>
<td valign='top' width=99%>
	<H5>{welcome}</H5>
	<p class=caption>{welcome_text}</p>
	<table style='width:100%'>
	<tr>
		<td align='right' nowrap><strong>{server_name}:</strong></td>
		<td align='left' nowrap>".Field_text('server_name',$server_name,'width:130px')."</strong></td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{workgroup}:</strong></td>
		<td align='left' nowrap>".Field_text('workgroup',$workgroup,'width:130px')."</strong></td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><input type='button' value='{next}&raquo;' OnClick=\"javascript:samba_pdc_step2();\"></td>
	</tr>
	</table>
</td>
</tr>
</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}
function main_pdc_root(){
	$samba=new samba();
	$password=$samba->GetAdminPassword('administrator');
	
	
	
	$html="
<table style='width:100%'>
<tr>
<td valign='top'><img src='img/48-samba-pdc.png'></td>
<td valign='top' width=99%>
	<H5>{root_password}</H5>
	<p class=caption>{root_password_text}</p>
	
	<H3>{$samba->main_array["global"]["workgroup"]}\administrator account</H3>
	<table style='width:100%'>
	<tr>
		<td align='right' nowrap><strong>{root_password_field}:</strong></td>
		<td align='left' nowrap>".Field_password('root_password',$password,'width:130px')."</strong></td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{root_password_field2}:</strong></td>
		<td align='left' nowrap>".Field_password('root_password2',$password,'width:130px')."</strong></td>
	</tr>	
	<td colspan=2 align='right'><input type='button' value='{next}&raquo;' OnClick=\"javascript:samba_pdc_step3();\"></td>
	</tr>
	</table>
</td>
</tr>
</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}

function main_pdc_add_computer(){
	$html="
<table style='width:100%'>
<tr>
<td valign='top'><img src='img/48-samba-pdc.png'></td>
<td valign='top' width=99%>
	<H5>{add_computer_title}</H5>
	<p class=caption>{add_computer_text}</p>
	<table style='width:100%'>
	<tr>
		<td align='right' nowrap><strong>{computer_name}:</strong></td>
		<td align='left' nowrap>".Field_text('computer_name','','width:130px')."</strong></td>
	</tr>
	<td colspan=2 align='right'><input type='button' value='{next}&raquo;' OnClick=\"javascript:samba_pdc_step4();\"></td>
	</tr>
	</table>
</td>
</tr>
</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function main_pdc_howto1(){
	$page=CurrentPageName();
	$html="
<table style='width:100%'>
<tr>
<td valign='top'><img src='img/48-samba-pdc.png'></td>
<td valign='top' width=99%>
	<H5>{configuration_success}</H5>
	<p class=caption>{howto1}</p>
	<table style='width:100%'>
	<tr>
		<td align='center' nowrap>
		<div style='width:100%;height:300px;overflow:auto'>
		<img src='img/samba-pdc/001.png'>
		</div></td>
		
	</tr>
	<td colspan=2 align='right'><input type='button' value='{next}&raquo;' OnClick=\"javascript:YahooWin(650,'$page?main=pdc_5');\"></td>
	</tr>
	</table>
</td>
</tr>
</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}
function main_pdc_howto2(){
	$page=CurrentPageName();
	$html="
<table style='width:100%'>
<tr>
<td valign='top'><img src='img/48-samba-pdc.png'></td>
<td valign='top' width=99%>
	<H5>{configuration_success}</H5>
	<p class=caption>{howto2}</p>
	<table style='width:100%'>
	<tr>
		<td align='center' nowrap colspan=2>
		<div style='width:100%;height:300px;overflow:auto'>
		<img src='img/samba-pdc/002.png'>
		</div></td>
		
	</tr>
	<td  align='left'><input type='button' value='&laquo;{back}' OnClick=\"javascript:YahooWin(650,'$page?main=pdc_4');\"></td>
	<td  align='right'><input type='button' value='{next}&raquo;' OnClick=\"javascript:YahooWin(650,'$page?main=pdc_6');\"></td>
	
	</tr>
	</table>
</td>
</tr>
</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}
function main_pdc_howto3(){
	$page=CurrentPageName();
	$samba=new samba();
	$html="
<table style='width:100%'>
<tr>
<td valign='top'><img src='img/48-samba-pdc.png'></td>
<td valign='top' width=99%>
	<H5>{configuration_success}</H5>
	<H3 style='color:red'>{use}: {$samba->main_array["global"]["workgroup"]}\administrator account</H3>
	<p class=caption>{howto3}</p>
	<table style='width:100%'>
	<tr>
		<td align='center' nowrap colspan=2>
		<div style='width:100%;height:300px;overflow:auto'>
		<img src='img/samba-pdc/003.png'>
		</div>
		</td>
		
	</tr>
	<td  align='left'><input type='button' value='&laquo;{back}' OnClick=\"javascript:YahooWin(650,'$page?main=pdc_5');\"></td>
	<td  align='right'><input type='button' value='{next}&raquo;' OnClick=\"javascript:YahooWin(650,'$page?main=pdc_7');\"></td>
	
	</tr>
	</table>
</td>
</tr>
</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}
function main_pdc_howto4(){
	$page=CurrentPageName();
	$html="
<table style='width:100%'>
<tr>
<td valign='top'><img src='img/48-samba-pdc.png'></td>
<td valign='top' width=99%>
	<H5>{configuration_success}</H5>
	<p class=caption>{howto4}</p>
	<table style='width:100%'>
	<tr>
		<td align='center' nowrap colspan=2><img src='img/samba-pdc/004.png'></td>
		
	</tr>
	<td  align='left'><input type='button' value='&laquo;{back}' OnClick=\"javascript:YahooWin(650,'$page?main=pdc_6');\"></td>
	<td  align='right'>&nbsp;</td>
	
	</tr>
	</table>
</td>
</tr>
</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}




?>