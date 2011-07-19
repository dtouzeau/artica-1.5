<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.main_cf.inc');
$user=new usersMenus();
$tpl=new templates();
if($user->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');}
if(isset($_GET["finish"])){finish();exit;}
step1();


function step1(){
	
$user=new usersMenus();

	
	$html="
	<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'><img src='img/bg_wizard.jpg'>
</td>
<td valign='top'>
	<div style='padding:5px;margin:5px'>
		<H4>{wizard1_welcome}</H4>
	<p>{wizard1_welcome_text}</p>
</td>
</tr>
<tr>
<td valign='top' colspan=2>" . wizard2()."

</td>
</tr>
</table>";
$JS["JS"][]="js/artica_wizard.js";
$JS["JS"][]="postfix.js";
$tpl=new template_users('{create_org_postfix}',$html,0,0,0,0,$JS);
echo $tpl->web_page;	
	
}


function wizard2(){
		$sys=new systeminfos();
		while (list ($num, $val) = each ($sys->array_ip) ){$arr[$num]=$num;}
		$arr["all"]="all";
		$arr[""]='{select}';
		$nics=Field_array_Hash($arr,'nic_hook',$user->ChangeAutoInterface);	
		$main=new main_cf();
		if(is_array($main->array_mynetworks)){
			$mynetwork=implode(',',$main->array_mynetworks);				
		}
		$hash_relay=array("mail"=>"mail","relay"=>"relay","single"=>"Hub");
		$fieldrelay=Field_array_Hash($hash_relay,'relay',$relay_behavior,null,null,0,'width:100%;');	
	
$html="
	<div style='padding:5px;margin:5px;width:100%'>
		
	" . RoundedLightGrey("	
	<H4>{network_settings}</H4>
	<table width=100%>
	<tr>
	<td><img src='img/150-nic.png'></td>
	<td valign='top'>	
		<table>
			<tr>
				<td width=60% class='caption'><H5>{inet_interfaces_title}</H5>{network_settings_text}</td>
				<td align='left'>$nics</td>
			</tr>
			<tr>
				<td width=60% class='caption'><h5>{mynetworks_title}:</h5>{mynetworks_single_text}<p class=caption>$mynetwork</p></td >
				<td style='padding-left:5px' align='left'><input type='button' value='{add}&nbsp;&raquo;'  OnClick=\"javascript:postfix_add_network_v2();\"></td>
			</tr>		
			<tr>
			<tr><td colspan=2>&nbsp;</td></tr>
			<td align='left'>&nbsp;</td>
			
			</tr>
			</table>
		</td>
	</tr>
</table>")."

<br>". RoundedLightGrey("
<H4>{organization}</h4>	
<table width=100%>
	<tr>
	<td colspan=3>
		<table width=100%>
			<td valign='top'><img src='img/150-org.png'></td>
			<td valign='top'>	
				<table>
					<tr>
						<td width=60% class='caption' valign='top' align='right'><h5>{question_company_name}:</H5>
						<td style='padding-left:5px' align='left' class='caption' >" . Field_text('company_name',$_GET["company_name"]) . "</td>
					</tr>		
				</table>
			</td>
		</table>
	</td>
	<tr><td align='right' colspan=3><input type='button' value='{finish}&nbsp;&raquo;' OnClick=\"javascript:EditWizar1();\"></td></tr>
	</tr>
	</table>")."
	
	
	</div>
	";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
}

function Save(){
	$user=new usersMenus();
if(isset($_GET["nic_hook"])){
		include_once("ressources/class.sockets.inc");
		$sock=new sockets();
		$sock->getfile('PostFixChangeAutoInterface:'.$_GET["nic_hook"]);
		$nic_hook=$_GET["nic_hook"];
	}else{$nic_hook=$user->ChangeAutoInterface;}	
	
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


		

function finish(){
	$company_name=$_GET["company_name"]	;
	$hook=$_GET["nic_hook"];
	$tpl=new templates();
	
	if($hook==null){echo $tpl->_ENGINE_parse_body('{error}: {no} {inet_interfaces_title}');return null;}
	if($company_name==null){echo $tpl->_ENGINE_parse_body('{error}: {no} {company_name}');return null;}
	
	$ldap=new clladp();
	$ldap->AddOrganization($company_name);
	include_once("ressources/class.sockets.inc");
	$sock=new sockets();
	$sock->getfile('PostFixChangeAutoInterface:'.$_GET["nic_hook"]);	
	
	}




