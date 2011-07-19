<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");
$user=new usersMenus();
if($user->AsPostfixAdministrator==false){header('location:logon.php');}

if(isset($_GET["address_verify_map"])){PostFixVerifyDatabaseSave();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
js();

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{address_verify_map}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}


$js=file_get_contents("js/postfix-cache.js");	
	
$html="
$js

function {$prefix}Loadpage(){
	YahooWin2('650','$page?popup-index=yes','$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}

function popup_index(){
$main=new main_cf();


$html="<H1>{address_verify_map_minitext}</H1>".RoundedLightWhite("<p class=caption>{address_verify_map_text}</p>")."<br>


<form name='FFMDBCache'>

<table style='width:600px' align=center>
<tr>
<td valign='top'>
		" .RoundedLightWhite("
		<table style='width:100%;margin:10px'>
			<tr>
				<td nowrap class=legend>{address_verify_map_field}:</strong></td>
				<td>" . Field_text('address_verify_map',str_replace('btree:','',$main->main_array["address_verify_map"])) . "</td>
			</tr>
			<tr>
				<td nowrap class=legend>{address_verify_negative_cache}:</strong></td>
				<td>" . Field_yesno_checkbox_img('address_verify_negative_cache',$main->main_array["address_verify_negative_cache"],'{address_verify_negative_cache_text}') . "</td>
			</tr>	
			<tr>
				<td nowrap class=legend>{address_verify_poll_count}:</strong></td>
				<td>" . Field_text('address_verify_poll_count',$main->main_array["address_verify_poll_count"],'width:30%',null,null,'{address_verify_poll_count_text}') . "</td>
			</tr>
			<tr>
				<td nowrap class=legend>{address_verify_poll_delay}:</strong></td>
				<td>" . Field_text('address_verify_poll_delay',$main->main_array["address_verify_poll_delay"],'width:30%',null,null,'{address_verify_poll_delay_text}') . "</td>
			</tr>									
			
			
			
			<tr>
				<td nowrap class=legend>{address_verify_sender}:</strong></td>
				<td>" . Field_text('address_verify_sender',$main->main_array["address_verify_sender"],'width:30%',null,null,'{address_verify_sender_text}') . "</td>
			</tr>			
			<tr>
				<td align='right' nowrap class=legend>{address_verify_negative_expire_time}:</strong></td>
				<td>" . Field_text('address_verify_negative_expire_time',$main->main_array["address_verify_negative_expire_time"],'width:30%',null,null,'{address_verify_negative_expire_time_text}') . "</td>
			</tr>
			<tr>
				<td align='right' nowrap class=legend>{address_verify_negative_refresh_time}:</strong></td>
				<td>" . Field_text('address_verify_negative_refresh_time',$main->main_array["address_verify_negative_refresh_time"],'width:30%',null,null,'{address_verify_negative_refresh_time_text}') . "</td>
			</tr>
			<tr>
				<td align='right' nowrap class=legend>{address_verify_positive_expire_time}:</strong></td>
				<td>" . Field_text('address_verify_positive_expire_time',$main->main_array["address_verify_positive_expire_time"],'width:30%',null,null,'{address_verify_positive_expire_time_text}') . "</td>
			</tr>
			<tr>
				<td align='right' nowrap class=legend>{address_verify_positive_refresh_time}:</strong></td>
				<td>" . Field_text('address_verify_positive_refresh_time',$main->main_array["address_verify_positive_refresh_time"],'width:30%',null,null,'{address_verify_positive_refresh_time_text}') . "</td>
			</tr>									
			
			
					

			
					
			<tr>
				<td align='right' colspan=2><input type='button' value='{delete}&nbsp;&raquo;' OnClick=\"PostFixVerifyDatabaseDeleteSave();\">&nbsp;&nbsp;<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"PostFixVerifyDatabaseSave();\"></td>
			</tr>				
		</table>")."
</td>
</tr>
</table></form>";


$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html);
}

function PostFixVerifyDatabaseSave(){
	$main=new main_cf();
	$tpl=new templates();
	$OldValue=$main->main_array["address_verify_map"];
	$OldValue=str_replace('btree:','',$OldValue);
	$newvalue=$_GET["address_verify_map"];
	
	if($newvalue<>null){
		if($OldValue==$newvalue){
			PostFixVerifyDatabaseSaveParseOthers();
			echo $tpl->_ENGINE_parse_body('{success}');
			return null;
		}
		if($OldValue<>$newvalue){
			if($OldValue<>null){
				$socks=new sockets();
				$socks->getfile('DeleteTheMainFilePostfixSettings:'  .$OldValue );				
				}

		$main->main_array["address_verify_map"]="btree:$newvalue";
		$main->save_conf();					
			
		}
	}else{
	   if($OldValue<>null){
		$main->main_array["address_verify_map"]="";
		$main->save_conf();
		$socks=new sockets();
		$socks->getfile('DeleteTheMainFilePostfixSettings:'  .$OldValue );		   	
	   	
	   }
		
	
	}
PostFixVerifyDatabaseSaveParseOthers();
	echo $tpl->_ENGINE_parse_body('{success}');
}

function PostFixVerifyDatabaseSaveParseOthers(){
	$main=new main_cf();
	unset($_GET["address_verify_map"]);
	while (list ($num, $ligne) = each ($_GET) ){
		$main->main_array[$num]=$ligne;
		
	}
	$main->save_conf();
	
}



?>