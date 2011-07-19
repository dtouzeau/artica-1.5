<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

if(isset($_GET["popup-index"])){popup();exit;}
if(isset($_GET["LIMIT"])){save();exit;}
if(isset($_GET['events-js'])){events_js();exit;}
if(isset($_GET["popup-events"])){events_popup();exit;}


js();


function popup(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("RTMMailConfig"));
	if($ini->_params["ENGINE"]["LIMIT"]==null){$ini->_params["ENGINE"]["LIMIT"]="100";}
	if($ini->_params["ENGINE"]["LOG_DAY_LIMIT"]==null){$ini->_params["ENGINE"]["LOG_DAY_LIMIT"]="20";}
	
	if($ini->_params["Discard"]["row_color"]==null){$ini->_params["Discard"]["row_color"]="#D00000";}
	if($ini->_params["Discard"]["text_color"]==null){$ini->_params["Discard"]["text_color"]="#FFFFFF";}
	
	if($ini->_params["Greylisting"]["row_color"]==null){$ini->_params["Greylisting"]["row_color"]="#949494";}
	if($ini->_params["Greylisting"]["text_color"]==null){$ini->_params["Greylisting"]["text_color"]="#FFFFFF";}
	
	if($ini->_params["Relay_access_denied"]["row_color"]==null){$ini->_params["Relay_access_denied"]["row_color"]="#D00000";}
	if($ini->_params["Relay_access_denied"]["text_color"]==null){$ini->_params["Relay_access_denied"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["User_unknown_in_relay_recipient_table"]["row_color"]==null){$ini->_params["User_unknown_in_relay_recipient_table"]["row_color"]="#D00000";}
	if($ini->_params["User_unknown_in_relay_recipient_table"]["text_color"]==null){$ini->_params["User_unknown_in_relay_recipient_table"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["RBL"]["row_color"]==null){$ini->_params["RBL"]["row_color"]="#949494";}
	if($ini->_params["RBL"]["text_color"]==null){$ini->_params["RBL"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["hostname_not_found"]["row_color"]==null){$ini->_params["hostname_not_found"]["row_color"]="#FFECEC";}
	if($ini->_params["hostname_not_found"]["text_color"]==null){$ini->_params["hostname_not_found"]["text_color"]="#000000";}		
		
	if($ini->_params["Domain_not_found"]["row_color"]==null){$ini->_params["Domain_not_found"]["row_color"]="#FFECEC";}
	if($ini->_params["Domain_not_found"]["text_color"]==null){$ini->_params["Domain_not_found"]["text_color"]="#000000";}

	if($ini->_params["DNS_Error"]["row_color"]==null){$ini->_params["DNS_Error"]["row_color"]="#D00000";}
	if($ini->_params["DNS_Error"]["text_color"]==null){$ini->_params["DNS_Error"]["text_color"]="#FFFFFF";}		
	
	if($ini->_params["SPAM"]["row_color"]==null){$ini->_params["SPAM"]["row_color"]="#F36C15";}
	if($ini->_params["SPAM"]["text_color"]==null){$ini->_params["SPAM"]["text_color"]="#FFFFFF";}

	if($ini->_params["SPAMMY"]["row_color"]==null){$ini->_params["SPAMMY"]["row_color"]="#FFC59E";}
	if($ini->_params["SPAMMY"]["text_color"]==null){$ini->_params["SPAMMY"]["text_color"]="#000000";}	

	if($ini->_params["Sended"]["row_color"]==null){$ini->_params["Sended"]["row_color"]="#FFFFFF";}
	if($ini->_params["Sended"]["text_color"]==null){$ini->_params["Sended"]["text_color"]="#000000";}	

	if($ini->_params["User_unknown"]["row_color"]==null){$ini->_params["User_unknown"]["row_color"]="#FFECEC";}
	if($ini->_params["User_unknown"]["text_color"]==null){$ini->_params["User_unknown"]["text_color"]="#000000";}

$array["Content scanner malfunction"]="Content scanner malfunction";
$array["Discard"]="Discard";
$array["DNS Error"]="DNS Error";
$array["Domain not found"]="Domain not found";
$array["Error"]="Error";
$array["Greylisting"]="Greylisting";
$array["hostname not found"]="hostname not found";
$array["Mailbox unknown"]="Mailbox unknown";
$array["RBL"]="RBL";
$array["Relay access denied"]="Relay access denied";
$array["Sended"]="Sended";
$array["SPAM"]="SPAM";
$array["SPAMMY"]="SPAMMY";
$array["User unknown in relay recipient table"]="User unknown in relay recipient table";
$array[null]="{all}";
$field=Field_array_Hash($array,'FILTERBY',$ini->_params["ENGINE"]["FILTERBY"]);	
	
	$html="
	<H1>{RTM_PARAMETERS}</H1>
	<p class=caption>{RTM_PARAMETERS_TEXT}</p>
	<div id='RTMDIV'>
	<form name='RTM1'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>" . Buildicon64("DEF_ICO_EVENTS_RTMM")."</td>
	<td valign='top'>
		<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>{row_number}:</td>
			<td>" . Field_text("LIMIT",$ini->_params["ENGINE"]["LIMIT"],"width:40px")."</td>
		</tr>
		<tr>
			<td class=legend>{LOG_DAY_LIMIT}:</td>
			<td>" . Field_text("LOG_DAY_LIMIT",$ini->_params["ENGINE"]["LOG_DAY_LIMIT"],"width:40px")."</td>
		</tr>	
		<tr>
			<td class=legend>{show}:</td>
			<td>" . $field."</td>
		</tr>	
		</table>
	</td>
	</tr>
	</table>
	
	
	<table style='width:100%' class=table_form>
	<tr>
		<th>{event}</th>
		<th>{row_color}</th>
		<th>{text_color}</th>
	</tr>
	<tr>
		<td class=legend>Discard:</td>
		<td>" . Field_text("Discard_row_color",$ini->_params["Discard"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("Discard_text_color",$ini->_params["Discard"]["text_color"],"width:80px",'color')."</td>
	</tr>
	<tr>
		<td class=legend>Greylisting:</td>
		<td>" . Field_text("Greylisting_row_color",$ini->_params["Greylisting"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("Greylisting_text_color",$ini->_params["Greylisting"]["text_color"],"width:80px",'color')."</td>
	</tr>

	<tr>
		<td class=legend>Relay access denied:</td>
		<td>" . Field_text("Relay_access_denied_row_color",$ini->_params["Relay_access_denied"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("Relay_access_denied_text_color",$ini->_params["Relay_access_denied"]["text_color"],"width:80px",'color')."</td>
	</tr>


	<tr>
		<td class=legend>User unknown in relay recipient table:</td>
		<td>" . Field_text("User_unknown_in_relay_recipient_table_row_color",$ini->_params["User_unknown_in_relay_recipient_table"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("User_unknown_in_relay_recipient_table_text_color",$ini->_params["User_unknown_in_relay_recipient_table"]["text_color"],"width:80px",'color')."</td>
	</tr>	
	
	<tr>
		<td class=legend>User unknown:</td>
		<td>" . Field_text("User_unknown_row_color",$ini->_params["User_unknown"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("User_unknown_color",$ini->_params["User_unknown"]["text_color"],"width:80px",'color')."</td>
	</tr>	
	
	<tr>
		<td class=legend>RBL:</td>
		<td>" . Field_text("RBL_row_color",$ini->_params["RBL"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("RBL_text_color",$ini->_params["RBL"]["text_color"],"width:80px",'color')."</td>
	</tr>	

	<tr>
		<td class=legend>hostname not found:</td>
		<td>" . Field_text("hostname_not_found_row_color",$ini->_params["hostname_not_found"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("hostname_not_found_text_color",$ini->_params["hostname_not_found"]["text_color"],"width:80px",'color')."</td>
	</tr>	
	<tr>
		<td class=legend>DNS Error:</td>
		<td>" . Field_text("DNS_Error_row_color",$ini->_params["DNS_Error"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("DNS_Error_text_color",$ini->_params["DNS_Error"]["text_color"],"width:80px",'color')."</td>
	</tr>	
	<tr>
		<td class=legend>SPAM:</td>
		<td>" . Field_text("SPAM_row_color",$ini->_params["SPAM"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("SPAM_text_color",$ini->_params["SPAM"]["text_color"],"width:80px",'color')."</td>
	</tr>	
	<tr>
		<td class=legend>SPAMMY:</td>
		<td>" . Field_text("SPAMMY_row_color",$ini->_params["SPAMMY"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("SPAMMY_text_color",$ini->_params["SPAMMY"]["text_color"],"width:80px",'color')."</td>
	</tr>
	<tr>
		<td class=legend>Sended:</td>
		<td>" . Field_text("Sended_row_color",$ini->_params["Sended"]["row_color"],"width:80px",'color')."</td>
		<td>" . Field_text("Sended_text_color",$ini->_params["Sended"]["text_color"],"width:80px",'color')."</td>
	</tr>				
	<tr>
	<td colspan=3 align='right'>
	<hr>
	". button("{apply}","SaveRTMParameters()")."
	
	</td>
	</tr>
	</table>
	</form>
	</div>
	<br>
	
	
	
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function save(){
	
	
$ini=new Bs_IniHandler();
$ini->_params["ENGINE"]["LIMIT"]=$_GET["LIMIT"];
$ini->_params["ENGINE"]["FILTERBY"]=$_GET["FILTERBY"];
$ini->_params["ENGINE"]["LOG_DAY_LIMIT"]=$_GET["LOG_DAY_LIMIT"];


while (list ($num, $val) = each ($_GET) ){
	if(preg_match("#(.+?)_row_color#",$num,$re)){$ini->_params[$re[1]]["row_color"]=$val;}
	if(preg_match("#(.+?)_text_color#",$num,$re)){$ini->_params[$re[1]]["text_color"]=$val;}
	}

$sock=new sockets();
$sock->SaveConfigFile($ini->toString(),"RTMMailConfig");
$sock->getfile("LaunchRTMMail");
	
}

function events_js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{artica_events_status}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="

function {$prefix}LoadpageEV(){
	YahooWin2('650','$page?popup-events=yes','$title');
	
	}
	

	var x_SaveRTMParameters= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
		 {$prefix}Loadpage();
		}	
	
function {$prefix}DisplayDivs(){
		jscolor.bind()
	}	
	
function SaveRTMParameters(){
	ParseForm('RTM1','$page',false,false,false,'RTMDIV','',x_SaveRTMParameters);
}

 {$prefix}LoadpageEV();
";
	
	echo $html;
}

function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{RTM_PARAMETERS}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="

function {$prefix}Loadpage(){
	YahooWin('650','$page?popup-index=yes','$title');
	setTimeout('{$prefix}DisplayDivs()',900);
	switch_tab('emails_received');
	}
	

	var x_SaveRTMParameters= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
		 {$prefix}Loadpage();
		}	
	
function {$prefix}DisplayDivs(){
		jscolor.bind()
	}	
	
function SaveRTMParameters(){
	ParseForm('RTM1','$page',false,false,false,'RTMDIV','',x_SaveRTMParameters);
}
	
Loadjs('js/jscolor.js');
 {$prefix}Loadpage();
";
	
	echo $html;
}

function events_popup(){
	$sock=new sockets();
	$datas=explode("\n",$sock->getFrameWork("cmd.php?viewlogs=artica-status.debug"));
	if(is_array($datas)){
		array_reverse($datas);
		while (list ($num, $line) = each ($datas) ){
			$tbl=$tbl."<div><code>$line</code></div>";
		}
		
		}
		
$html="<H1>{artica_events_status}</H1>
".RoundedLightWhite("<div style='width:100%;height:350px;overflow:auto'>$tbl</div>");
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}


?>