<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dkimfilter.inc');
	include_once('ressources/class.main_cf.inc');
	
	
	
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}
if(isset($_POST["save_step"])){echo save_step();exit;}
if(isset($_GET["status"])){echo main_status();exit;}
if(isset($_GET["ApplyConfig"])){echo ApplyConfig();exit;}
if(isset($_GET["FillSenderForm"])){echo FillSenderForm();exit;}
if(isset($_GET["On-BadSignature"])){save();exit();}



page();	
function page(){
$page=CurrentPageName();	
$html="
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",3000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('servinfos','$page?status=yes&hostname={$_GET["hostname"]}');
	}
</script>	


<table style='width:100%' align=center>
<tr>
<td valign='top'>
	<img src='img/dkim_bg.jpg' style='padding:4px;border:1px dotted #CCCCCC;margin:30px;margin-top:0px'>
	
</td>
<td valign='top' width='99%'>
<div id='servinfos'></div><br>
<p class=caption>{dkim_about}</p>
</td>
</tr>
<tr>
	<td colspan=2>
	<div id='main_config'>
	" . sub_page()."
		</div>
	</td>
</tr>
		
</table>
<script>demarre();ChargeLogs()</script>

";
$cfg["JS"][]="js/postfix-tls.js";
//<script>LoadAjax('main_config','$page?main=transport_settings&hostname=$hostname')</script>
$tpl=new template_users('{APP_DKIM_FILTER}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;
	
}


function sub_page(){

$dkim=new dkimfilter();	
	
$filteraction_array=array(
"a"=>"accept the message",
"d"=>"discard the message",
"t"=>"temp-fail the message",
"r"=>"reject the message"
);



$form="
<table style='width:100%'>
				<tr>
					<td align='right' nowrap style='font-size:10px'><strong>{On-BadSignature}:&nbsp;</strong></td>
					<td>" . Field_array_Hash($filteraction_array,'On-BadSignature',$dkim->main_array["On-BadSignature"])."</td>
				</tr>
				<tr>
					<td align='right' nowrap style='font-size:10px'><strong>{On-DNSError}:&nbsp;</strong></td>
					<td>" . Field_array_Hash($filteraction_array,'On-DNSError',$dkim->main_array["On-DNSError"])."</td>
				</tr>	
				<tr>
					<td align='right' nowrap style='font-size:10px'><strong>{On-InternalError}:&nbsp;</strong></td>
					<td>" . Field_array_Hash($filteraction_array,'On-InternalError',$dkim->main_array["On-InternalError"])."</td>
				</tr>
				<tr>
					<td align='right' nowrap style='font-size:10px'><strong>{On-NoSignature}:&nbsp;</strong></td>
					<td>" . Field_array_Hash($filteraction_array,'On-NoSignature',$dkim->main_array["On-NoSignature"])."</td>
				</tr>
				<tr>
					<td align='right' nowrap style='font-size:10px'><strong>{On-Security}:&nbsp;</strong></td>
					<td>" . Field_array_Hash($filteraction_array,'On-Security',$dkim->main_array["On-Security"])."</td>
				</tr>											
				<tr>
					<td align='right' nowrap style='font-size:10px'><strong>{On-SignatureMissing}:&nbsp;</strong></td>
					<td>" . Field_array_Hash($filteraction_array,'On-SignatureMissing',$dkim->main_array["On-SignatureMissing"])."</td>
				</tr>	
				<tr>
					<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffm1','$page',true);\" value='&laquo;&nbsp;&nbsp;{save}&nbsp;&nbsp;&raquo;'></td>
							
			</table>

";




$form="<br>".RoundedLightGrey($form);

	return "
	<table style='width:100%'>
	<tr>
		<td valign='top'>
				<table style='width:100%'>
				<tr>
				<td valign='top' width=1%><img src='img/cpanel.png'></td>
					<td valign='top'>
						<H5>{select_action}</H5>
						<form name='ffm1'>
						$form
						</form>
					</td>
				</tr>	
				</table>
		</td>
	</tr>
	</table>
	
	
	";
	
	
	
}



function main_status(){
	$users=new usersMenus();
	$tpl=new templates();

	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$key_service="DKIM_FILTER";
	$ini->loadString($sock->getfile('dkimstatus',$_GET["hostname"]));	
	if($ini->_params["$key_service"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
	}
	
	$status1="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{{$ini->_params["$key_service"]["service_name"]}}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["$key_service"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap><strong>{$ini->_params["$key_service"]["master_memory"]}&nbsp; kb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["$key_service"]["master_version"]}</strong></td>
		</tr>				
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status1=RoundedLightGreen($status1);
	return $tpl->_ENGINE_parse_body($status1);
	
	
}


function save(){
	$kim=new dkimfilter();
	while (list ($num, $val) = each ($_GET) ){
		$kim->main_array[$num]=$val;
		
	}
	$kim->SaveConf();
	$kim->SaveToserver();
	
}

	
?>	

