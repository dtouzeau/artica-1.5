<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.fetchmail.inc');
$user=new usersMenus();
if(!$user->AllowFetchMails){header('location:user.index.php');}
if(isset($_GET["LoadRules"])){RULES();exit();}
if(isset($_GET["ViewScript"])){ViewScript();exit;}
if(isset($_GET["ViewRule"])){ViewRule();exit;}

PAGE();


function PAGE(){
	$page=CurrentPageName();
	$artica=new artica_general();
	if($artica->EnableFetchmail==0){
		
		$warning=Paragraphe('warning64.png','{service_disabled}','{service_fetchmail_disabled_admin}',"",null,300,100);
	}
	
	
	$html="
	<input type='hidden' value='{$_SESSION["uid"]}' id='uid'>
	<input type='hidden' value='{confirm_delete}' id='confirm'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/bg_fetchmail2.png'></td>
		<td valign='top' style='padding-left:20px'>$warning" . Paragraphe('64-plus.png','{add_new_fetchmail_rule}','{add_new_fetchmail_rule_text}',"javascript:add_fetchmail_rules();",null,300,100) ."
	</tr>
	<tr>
	<td colspan=2>
		<div id='fetchmail_users_datas'></div>
	</td>
	</tr>
	</table>
		<script>LoadAjax('fetchmail_users_datas','$page?LoadRules=yes');</script>
	
	";
	$tpl=new template_users("{fetchmail_rules}",$html);
	echo $tpl->web_page;
	
	
}

function RULES(){
	$page=CurrentPageName();
	$uid=$_SESSION["uid"];
	$ldap=new clladp();
	$u=$ldap->UserDatas($uid);
	
	$fr=new Fetchmail_settings();
	$tpl=new templates();
	
	if(is_array($u["FetchMailsRulesSources"])){
	while (list ($num, $ligne) = each ($u["FetchMailsRulesSources"]) ){
		$arr=$fr->parse_config($ligne);
		$id=md5($line);
		$arr=$arr[1];
		if($arr["ssl"]){$ssl="{using_ssl}";}
		if($arr["port"]<>null){$port="{with_port} {$arr["port"]}";}else{$port=null;}
		$img_disabled="42-green.png";
		if($arr["disabled"]){$img_disabled="42-red.png";}
		$line="
		<table>
			<tr>
				<td width=1% valign='top'><img src='img/$img_disabled'></td>
				<td nowrap>
				<H5>{fetch}&nbsp; &#34;{$arr["poll"]}&#34;&nbsp;$disabled</H5>
					<div style='font-size:11px' id='$id'>
						{fetch_address} &#34;<strong>" . $arr["user"] . "</strong>&#34;<br>
						{protocol}: <strong>{$arr["proto"]} $port $ssl</strong><br>
						{and_send_to} <strong>&#34;" . $arr["is"] . "&#34;</strong>
					</div>
				</td>
			</tr>
			<tr><td colspan=2 align='right'>
				<table style='width:1%'>
					<tr>
						<td>" . imgtootltip('32-zoom-in.png','{view}',"LoadAjax('$id','$page?ViewScript=$num&id=$id');") . "</td>
						<td>" . imgtootltip('32-edit.png','{edit}',"UserFetchMailRule($num,'$uid');") . "</td>
						<td>" . imgtootltip('32-cancel.png','{delete}',"UserDeleteFetchMailRule($num);") . "</td>
					</tr>
				</table>
			</td>
		</tr>
		</table>";
		
		$left=RoundedLightGrey($tpl->_ENGINE_parse_body($line));
		
		$res[]=$left;
		}}
	
	if(is_array($res)){echo $tpl->_ENGINE_parse_body(implode("<br>",$res));};
	echo $list;
	
	
	
	
}

function HumanRule($ligne){
	$fr=new Fetchmail_settings();
	$arr=$fr->parse_config($ligne);
	$arr=$arr[1];
	if($arr["ssl"]){$ssl="{using_ssl}";}
	if($arr["port"]<>null){$port="{with_port} {$arr["port"]}";}else{$port=null;}
	$line="{fetch_address} &#34;<strong>" . $arr["user"] . "</strong>&#34;<br>
	{protocol}: <strong>{$arr["proto"]} $port $ssl</strong><br>
	{and_send_to} <strong>&#34;" . $arr["is"] . "&#34;</strong>";
	return $line;
}

function ViewRule(){
$uid=$_SESSION["uid"];
	$ldap=new clladp();
	$u=$ldap->UserDatas($uid);	
	$line=$u["FetchMailsRulesSources"][$_GET["ViewRule"]];
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(HumanRule($line));
}



function ViewScript(){
	$uid=$_SESSION["uid"];
	$ldap=new clladp();
	$u=$ldap->UserDatas($uid);
	$script=$u["FetchMailsRulesSources"][$_GET["ViewScript"]];
	$tbl=explode("\n",$script);
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne<>null){
			$arr[]=$ligne;
		}
	}
	
	$script=implode("\n",$tbl);
	$script=htmlspecialchars($script);
	$script="<table style='width:100%'><tr><td valign='top' width=1%>" . imgtootltip('42-redo-left.png','{back}',"LoadAjax('{$_GET["id"]}','$page?ViewRule={$_GET["ViewScript"]}&id={$_GET["id"]}');")."</td><td valign='top'><code>$script</code></td></tr></table>";
	$script=nl2br($script);
	$script=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$script);
	
	
	
	echo $script;
	
}

