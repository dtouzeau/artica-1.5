<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.main_cf.inc');
$user=new usersMenus();
$tpl=new templates();
if($user->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');}
if(isset($_GET["wizard1"])){wizard1();exit;}
if(isset($_GET["wizard2"])){wizard2();exit;}
if(isset($_GET["wizard3"])){wizard3();exit;}
if(isset($_GET["wizard4"])){wizard4();exit;}
if(isset($_GET["wizard5"])){wizard5();exit;}
if(isset($_GET["wizard6"])){wizard6();exit;}
if(isset($_GET["wizard7"])){wizard7();exit;}
if(isset($_GET["wizard8"])){wizard8();exit;}
if(isset($_GET["finish"])){finish();exit;}
step1();


function Wizardpme(){
$users=new usersMenus()	;


$play_is=RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td valign='top'>". imgtootltip('icon_settings-64.png','{edit}',"MyHref('artica.wizard.ispout.php')")."</td>
	<td valign='top'>
		<H5>{send_to_isp}</h5>
		{send_to_isp_text}
	
	</td>
	</tr>
	</table>",null,1);


$plays_is1=RoundedLightGrey("	<table style='width:100%'>
	<tr>
		<td valign='top'>". imgtootltip('icon_settings-64.png','{edit}',"MyHref('artica.wizard.isprelay.php')")."</td>
		<td valign='top'>
			<H5>{send_isp_relay}</h5>
			{send_isp_relay_text}
		
		</td>
	</tr>
	</table>
	",null,1);


$plays_is2=RoundedLightGrey("	<table style='width:100%'>
	<tr>
		<td valign='top'>". imgtootltip('icon_settings-64.png','{edit}',"MyHref('artica.wizard.fetchmail.php')")."</td>
		<td valign='top'>
			<H5>{get_mails_isp}</h5>
			{get_mails_isp_text}
		
		</td>
	</tr>	
	</table>",null,1);


$html="<h4>{play_with_your_isp}</h4>
	$play_is
	$plays_is1
	$plays_is2";
	
	return $html;
}



function step1(){
	$html="
	<script type=\"text/javascript\" language=\"javascript\" src=\"js/artica_wizard.js\"></script>
	<script type=\"text/javascript\" language=\"javascript\" src=\"postfix.js\"></script>
	<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'><img src='img/bg_wizard.jpg'>
</td>
<td valign='top'>
	<table>
	";
	
$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-update.jpg','{create_org_postfix}','{create_org_postfix_text}',"artica.wizard.org.php") ."</td></tr>";


$html=$html . "</table>
</td>
</tr>
<tr>
<td><span id='wizardpme'>" . RoundedLightGrey(Wizardpme())."</spam></td>
</tr>

</table>";
$tpl=new template_users('{artica_wizard}',$html,0,0,0,0,$array);
echo $tpl->web_page;	
	
}

function wizard1(){
	
	$html="
	<div style='padding:5px;margin:5px'>
		<H2>{wizard1_welcome}</H2>
	<p>{wizard1_welcome_text}</p>
	<div style='text-align:right'><input type='button' value='{start}&nbsp;&raquo;' OnClick=\"javascript:wizard2();\"></div>
	</div>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function wizard2(){
		
		$user=new usersMenus();
		$sys=new systeminfos();
		while (list ($num, $val) = each ($sys->array_ip) ){$arr[$num]=$num;}
		$arr["all"]="all";
		$arr[""]='{select}';
		$nics=Field_array_Hash($arr,'nic_hook',$user->ChangeAutoInterface);
	
$html="
	<div style='padding:5px;margin:5px'>
		<H2>{network_settings}</H2>
	<table>
	<tr>
	<td><img src='img/150-nic.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
	<td width=60% class='caption'>
	<H4>{inet_interfaces_title}</H4>
	{network_settings_text}</td>
	<td align='left'>$nics</td>
	</tr>
	<tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<td align='left'><input type='button' value='&laquo;&nbsp;{previous}' OnClick=\"javascript:wizard2();\"></td>
	<td align='right'><input type='button' value='{next}&nbsp;&raquo;' OnClick=\"javascript:wizard3();\"></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}
function wizard3(){
	$user=new usersMenus();
	if(isset($_GET["nic_hook"])){
		include_once("ressources/class.sockets.inc");
		$sock=new sockets();
		$sock->getfile('PostFixChangeAutoInterface:'.$_GET["nic_hook"]);
		$nic_hook=$_GET["nic_hook"];
	}else{$nic_hook=$user->ChangeAutoInterface;}
		
	$main=new main_cf();
	$mynetwork=implode(',',$main->array_mynetworks);
$html="
	<div style='padding:5px;margin:5px'>
		<H2>{network_settings}</H2>
	<table>
	<tr>
	<td><img src='img/150-nic.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
		<td width=60% class='caption' nowrap><strong>{inet_interfaces_title}:</strong></td >
		<td style='padding-left:5px' align='left'><strong>$nic_hook</strong></td>
	</tr>
	<tr>
		<td width=60% class='caption'><h4>{mynetworks_title}:</h4>{mynetworks_single_text}<p class=caption>$mynetwork</p></td >
		<td style='padding-left:5px' align='left'><input type='button' value='{add}&nbsp;&raquo;'  OnClick=\"javascript:postfix_add_network_v2();\"></td>
	</tr>	
	
	
	
	<tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<td align='left'><input type='button' value='&laquo;&nbsp;{previous}' OnClick=\"javascript:wizard2();\"></td>
	<td align='right'><input type='button' value='{next}&nbsp;&raquo;' OnClick=\"javascript:wizard4();\"></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>
	";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function MyNetworks(){
	$main=new main_cf();
		
	if(is_array($main->array_mynetworks)){
		$net="<table style='width:100%'>";

		while (list ($num, $val) = each ($main->array_mynetworks) ){
			$net=$net . "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>$val</td>
			</tr>";}
		$net=$net . "</table>";
		}	
	return $net;
}

function wizard4(){
	$user=new usersMenus();
	$nic_hook=$user->ChangeAutoInterface;
		
	
	
$html="
	<div style='padding:5px;margin:5px'>
		<H2>{organizations}</H2>
	<table>
	<tr>
	<td><img src='img/150-org.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
		<td width=60% class='caption' nowrap align='right'><strong>{inet_interfaces_title}:</strong></td >
		<td style='padding-left:5px' align='left'><strong>$nic_hook</strong></td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{mynetworks_title}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >" . MyNetworks() . "</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><h4>{question_company_name}:</H4>
		<td style='padding-left:5px' align='left' class='caption' >" . Field_text('company_name',$_GET["company_name"]) . "</td>
	</tr>		
	
	
	<tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<td align='left'><input type='button' value='&laquo;&nbsp;{previous}' OnClick=\"javascript:wizard3();\"></td>
	<td align='right'><input type='button' value='{next}&nbsp;&raquo;' OnClick=\"javascript:wizard5();\"></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>
	";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}
function wizard5(){
	$user=new usersMenus();
	$nic_hook=$user->ChangeAutoInterface;
	$company_name=$_GET["company_name"]	;
	$domain_name=$_GET["domain_name"];
	
$html="
	<input type='hidden' id='company_name' value='$company_name'>
	<div style='padding:5px;margin:5px'>
		<H2>{organizations}</H2>
	<table>
	<tr>
	<td><img src='img/150-org.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
		<td width=60% class='caption' nowrap align='right'><strong>{inet_interfaces_title}:</strong></td >
		<td style='padding-left:5px' align='left'><strong>$nic_hook</strong></td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{mynetworks_title}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >" . MyNetworks() . "</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{question_company_name}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$company_name</td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><h4>{question_domain_name}:</H4>
		<td style='padding-left:5px' align='left' class='caption' >" . Field_text('domain_name',$domain_name)."</td>
	</tr>				
	
	
	<tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<td align='left'><input type='button' value='&laquo;&nbsp;{previous}' OnClick=\"javascript:wizard5();\"></td>
	<td align='right'><input type='button' value='{next}&nbsp;&raquo;' OnClick=\"javascript:wizard6();\"></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>
	";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}
function wizard6(){
	$user=new usersMenus();
	$nic_hook=$user->ChangeAutoInterface;
	$company_name=$_GET["company_name"]	;
	$domain_name=$_GET["domain_name"];
	$relay_behavior=$_GET["relay"];
	$hash_relay=array("mail"=>"mail","relay"=>"relay","single"=>"Hub");
	$fieldrelay=Field_array_Hash($hash_relay,'relay',$relay_behavior,null,null,0,'width:100%;');	
	
$html="
	<input type='hidden' id='company_name' value='$company_name'>
	<input type='hidden' id='domain_name' value='$domain_name'>
	
	<input type='hidden' id='step' value='6'>
	<div style='padding:5px;margin:5px'>
		<H2>{organizations}</H2>
	<table>
	<tr>
	<td><img src='img/150-org.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
		<td width=60% class='caption' nowrap align='right'><strong>{inet_interfaces_title}:</strong></td >
		<td style='padding-left:5px' align='left'><strong>$nic_hook</strong></td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{mynetworks_title}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >" . MyNetworks() . "</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{question_company_name}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$company_name</td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{question_domain_name}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$domain_name</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><H4>{relay_behavior}:</H4>{relay_behavior_text2}
		<td style='padding-left:5px' align='left' class='caption' >$fieldrelay</td>
	</tr>				
	<tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<td align='left'><input type='button' value='&laquo;&nbsp;{previous}' OnClick=\"javascript:wizard5();\"></td>
	<td align='right'><input type='button' value='{next}&nbsp;&raquo;' OnClick=\"javascript:wizard7();\"></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>
	";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}
function wizard7(){
	
	$user=new usersMenus();
	$nic_hook=$user->ChangeAutoInterface;
	$company_name=$_GET["company_name"]	;
	$domain_name=$_GET["domain_name"];
	$relay_behavior=$_GET["relay"];
	$relay_infos=$_GET["relay_infos"];

	if($relay_behavior=='single'){
		
		$f="
		<tr>
		<td width=60% class='caption' valign='top' align='right'><H4>{relay_ip}:</H4>
		<td style='padding-left:5px' align='left' class='caption' >".Field_text('relay_infos')."</td>
		</tr>";
		
	}else{
	$f="
		<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{relay_ip}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >localhost</td>
		</tr>";	
		
	}
	
	
$html="
	<input type='hidden' id='company_name' value='$company_name'>
	<input type='hidden' id='domain_name' value='$domain_name'>
	<input type='hidden' id='relay' value='$relay_behavior'>
	<input type='hidden' id='setp' value='7'>
	<div style='padding:5px;margin:5px'>
		<H2>{organizations}</H2>
	<table>
	<tr>
	<td><img src='img/150-org.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
		<td width=60% class='caption' nowrap align='right'><strong>{inet_interfaces_title}:</strong></td >
		<td style='padding-left:5px' align='left'><strong>$nic_hook</strong></td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{mynetworks_title}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >" . MyNetworks() . "</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{question_company_name}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$company_name</td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{question_domain_name}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$domain_name</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{relay_behavior}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$relay_behavior</td>
	</tr>
	$f				
	<tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<td align='left'><input type='button' value='&laquo;&nbsp;{previous}' OnClick=\"javascript:wizard6();\"></td>
	<td align='right'><input type='button' value='{next}&nbsp;&raquo;' OnClick=\"javascript:wizard8();\"></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>
	";	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);			
}
function wizard8(){
	
	$user=new usersMenus();
	$nic_hook=$user->ChangeAutoInterface;
	$company_name=$_GET["company_name"]	;
	$domain_name=$_GET["domain_name"];
	$relay_behavior=$_GET["relay"];
	$relay_infos=$_GET["relay_infos"];

	if($relay_behavior=='single'){
		
		$f="
		<tr>
		<td width=60% class='caption' valign='top' align='right'><H4>{relay_ip}:</H4>
		<td style='padding-left:5px' align='left' class='caption' >".Field_text('relay_infos')."</td>
		</tr>";
		
	}else{
	$f="
		<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{relay_ip}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >localhost</td>
		</tr>";	
		
	}
	
	
$html="
	<input type='hidden' id='company_name' value='$company_name'>
	<input type='hidden' id='domain_name' value='$domain_name'>
	<input type='hidden' id='relay' value='$relay_behavior'>
	<input type='hidden' id='relay_infos' value='$relay_infos'>
	<input type='hidden' id='setp' value='8'>
	<div style='padding:5px;margin:5px'>
		<H2>{organizations}</H2>
	<table>
	<tr>
	<td><img src='img/150-org.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
		<td width=60% class='caption' nowrap align='right'><strong>{inet_interfaces_title}:</strong></td >
		<td style='padding-left:5px' align='left'><strong>$nic_hook</strong></td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{mynetworks_title}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >" . MyNetworks() . "</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{question_company_name}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$company_name</td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{question_domain_name}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$domain_name</td>
	</tr>	
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{relay_behavior}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$relay_behavior</td>
	</tr>
	<tr>
		<td width=60% class='caption' valign='top' align='right'><strong>{relay_ip}:</strong>
		<td style='padding-left:5px' align='left' class='caption' >$relay_infos</td>
	</tr>	
		
	<tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<td align='left'><input type='button' value='&laquo;&nbsp;{previous}' OnClick=\"javascript:wizard7();\"></td>
	<td align='right'><input type='button' value='{finish}&nbsp;&raquo;' OnClick=\"javascript:finish();\"></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	
	</div>
	";	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);			
}
function finish(){
	include_once('ressources/class.artica.inc');
	$company_name=$_GET["company_name"]	;
	$domain_name=$_GET["domain_name"];
	$relay_behavior=$_GET["relay"];
	$relay_infos=$_GET["relay_infos"];

	$artica=new artica_general();
	$artica->RelayType=$relay_behavior;
	$artica->Save();
	
	$ldap=new clladp();
	$ldap->AddOrganization($company_name);
	if($relay_behavior=="single"){
		if($relay_infos==null){$relay_infos="127.0.0.1";}
		$ldap->AddDomainTransport($company_name,$domain_name,$relay_infos,'25','smtp');
	}else{$ldap->AddDomainEntity($company_name,$domain_name);}
$html="<div style='padding:5px;margin:5px'>
		<H2>{finish}</H2>
	<table>
	<tr>
	<td><img src='img/150-org.jpg'></td>
	<td valign='top'>	
	<table>
	<tr>
		<td width=60% class='caption' nowrap align='right'><strong>{all_settings_saved}:</strong></td >
		
	</tr>	
	</table>
	</div>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}




