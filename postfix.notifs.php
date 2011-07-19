<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf.inc');
	
	
	if(!Isright()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["params"])){id_sender();exit;}
	if(isset($_GET["double_bounce_sender"])){SaveParams();exit;}
	if(isset($_GET["templates"])){templates_postfix();exit;}
	if(isset($_GET["postfix-notifs-template"])){templates_postfix_form();exit;}
	if(isset($_POST["template_save"])){templates_postfix_save();exit;}
	
	
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body("{POSTFIX_SMTP_NOTIFICATIONS}");
	if(!isset($_GET["hostname"])){$_GET["hostname"]="master";}
	$html="YahooWin3('570','$page?tabs=yes&hostname={$_GET["hostname"]}','{$_GET["hostname"]}::$title');";
	echo $html;
	
	
}

function tabs(){
	$array["params"]="{parameters}";
	$array["templates"]="{templates}";
	
	$page=CurrentPageName();
	$tpl=new templates();
	if(!isset($_GET["hostname"])){$_GET["hostname"]="master";}

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_postfix_notifs style='width:100%;height:580px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_postfix_notifs\").tabs();});
		</script>";		
	
}


function id_sender(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$main=new maincf_multi($_GET["hostname"]);
	$double_bounce_sender=$main->GET("double_bounce_sender");
	$address_verify_sender=$main->GET("address_verify_sender");
	$twobounce_notice_recipient=$main->GET("2bounce_notice_recipient");
	$error_notice_recipient=$main->GET("error_notice_recipient");
	$delay_notice_recipient=$main->GET("delay_notice_recipient");
	$empty_address_recipient=$main->GET("empty_address_recipient");
	
	$sock=new sockets();
	$PostfixPostmaster=$sock->GET_INFO("PostfixPostmaster");
	if(trim($PostfixPostmaster)==null){$PostfixPostmaster="postmaster";}
	
	if($double_bounce_sender==null){$double_bounce_sender="double-bounce";};
	if($address_verify_sender==null){$address_verify_sender="\$double_bounce_sender";}
	if($twobounce_notice_recipient==null){$twobounce_notice_recipient="postmaster";}
	if($error_notice_recipient==null){$error_notice_recipient=$PostfixPostmaster;}
	if($delay_notice_recipient==null){$delay_notice_recipient=$PostfixPostmaster;}
	if($empty_address_recipient==null){$empty_address_recipient=$PostfixPostmaster;}
	
	$notify_class=unserialize(base64_decode($main->GET_BIGDATA("notify_class")));
	if(!is_array($notify_class)){
		$notify_class["notify_class_resource"]=1;
		$notify_class["notify_class_software"]=1;
	}
	
		$notify_class_software=$notify_class["notify_class_software"];
		$notify_class_resource=$notify_class["notify_class_resource"];
		$notify_class_policy=$notify_class["notify_class_policy"];
		$notify_class_delay=$notify_class["notify_class_delay"];
		$notify_class_2bounce=$notify_class["notify_class_2bounce"];
		$notify_class_bounce=$notify_class["notify_class_bounce"];
		$notify_class_protocol=$notify_class["notify_class_protocol"];
	
	
	$html="<div class=explain>{POSTFIX_SMTP_NOTIFICATIONS_TEXT}</div>
	<div id='ffm1notif'>
	<table style='width:100%' class=form>	
	<tr>
		<td class=legend nowrap>{double_bounce_sender}:</td>
		<td>" . Field_text('double_bounce_sender',$double_bounce_sender,'font-size:13px;padding:3px;width:160px')."</td>
		<td>". help_icon("{double_bounce_sender_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap nowrap>{address_verify_sender}:</td>
		<td>" . Field_text('address_verify_sender',$address_verify_sender,'font-size:13px;padding:3px;width:160px')."</td>
		<td>". help_icon("{address_verify_sender_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{2bounce_notice_recipient}:</td>
		<td>" . Field_text('2bounce_notice_recipient',$twobounce_notice_recipient,'font-size:13px;padding:width:160px')."</td>
		<td>". help_icon("{2bounce_notice_recipient_text}")."</td>
	</tr>
	
	<tr>
		<td class=legend nowrap>{error_notice_recipient}:</td>
		<td>" . Field_text('error_notice_recipient',$error_notice_recipient,'font-size:13px;padding:width:160px')."</td>
		<td>". help_icon("{error_notice_recipient_text}")."</td>
	</tr>
	
	<tr>
		<td class=legend nowrap>{delay_notice_recipient}:</td>
		<td>" . Field_text('delay_notice_recipient',$delay_notice_recipient,'font-size:13px;padding:width:160px')."</td>
		<td>". help_icon("{delay_notice_recipient_text}")."</td>
	</tr>
	
	<tr>
		<td class=legend nowrap>{empty_address_recipient}:</td>
		<td>" . Field_text('empty_address_recipient',$empty_address_recipient,'font-size:13px;padding:width:160px')."</td>
		<td>". help_icon("{empty_address_recipient_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{empty_address_recipient}:</td>
		<td>" . Field_text('empty_address_recipient',$empty_address_recipient,'font-size:13px;padding:width:160px')."</td>
		<td>". help_icon("{empty_address_recipient_text}")."</td>
	</tr>
	</table>
<div style='font-size:16px;margin:10px'>{notify_class}</div>
<table style='width:100%' class=form>	
	</tr>
	<tr>
		<td class=legend nowrap>{notify_class_bounce}:</td>
		<td>" . Field_checkbox('notify_class_bounce',1,$notify_class_bounce)."</td>
		<td>". help_icon("{notify_class_bounce_text}")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{notify_class_2bounce}:</td>
		<td>" . Field_checkbox('notify_class_2bounce',1,$notify_class_2bounce)."</td>
		<td>". help_icon("{notify_class_2bounce_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{notify_class_delay}:</td>
		<td>" . Field_checkbox('notify_class_delay',1,$notify_class_delay)."</td>
		<td>". help_icon("{notify_class_delay_text}")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{notify_class_policy}:</td>
		<td>" . Field_checkbox('notify_class_policy',1,$notify_class_policy)."</td>
		<td>". help_icon("{notify_class_policy_text}")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{notify_class_protocol}:</td>
		<td>" . Field_checkbox('notify_class_protocol',1,$notify_class_protocol)."</td>
		<td>". help_icon("{notify_class_protocol_text}")."</td>
	</tr>	
	
	<tr>
		<td class=legend nowrap>{notify_class_resource}:</td>
		<td>" . Field_checkbox('notify_class_resource',1,$notify_class_resource)."</td>
		<td>". help_icon("{notify_class_resource_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{notify_class_software}:</td>
		<td>" . Field_checkbox('notify_class_software',1,$notify_class_software)."</td>
		<td>". help_icon("{notify_class_software_text}")."</td>
	</tr>
	

	
	<tr>
		<td colspan=3 align='right'>
		<hr>
		". button("{edit}","SavePostfixNotificationsForm()")."
		</td>
	</tr>		
</table>	
</div>
	<script>
	
	var x_SavePostfixNotificationsForm= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshTab('main_config_postfix_notifs');
	}	
	
	function SavePostfixNotificationsForm(){
		var XHR = new XHRConnection();
		XHR.appendData('double_bounce_sender',document.getElementById('double_bounce_sender').value);
		XHR.appendData('address_verify_sender',document.getElementById('address_verify_sender').value);
		XHR.appendData('2bounce_notice_recipient',document.getElementById('2bounce_notice_recipient').value);
		XHR.appendData('error_notice_recipient',document.getElementById('error_notice_recipient').value);
		XHR.appendData('delay_notice_recipient',document.getElementById('delay_notice_recipient').value);
		XHR.appendData('empty_address_recipient',document.getElementById('empty_address_recipient').value);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		if(document.getElementById('notify_class_software').checked){XHR.appendData('notify_class_software','1');}else{XHR.appendData('notify_class_software','0');}
		if(document.getElementById('notify_class_resource').checked){XHR.appendData('notify_class_resource','1');}else{XHR.appendData('notify_class_resource','0');}
		if(document.getElementById('notify_class_policy').checked){XHR.appendData('notify_class_policy','1');}else{XHR.appendData('notify_class_policy','0');}
		if(document.getElementById('notify_class_delay').checked){XHR.appendData('notify_class_delay','1');}else{XHR.appendData('notify_class_delay','0');}
		if(document.getElementById('notify_class_2bounce').checked){XHR.appendData('notify_class_2bounce','1');}else{XHR.appendData('notify_class_2bounce','0');}
		if(document.getElementById('notify_class_bounce').checked){XHR.appendData('notify_class_bounce','1');}else{XHR.appendData('notify_class_bounce','0');}
		if(document.getElementById('notify_class_protocol').checked){XHR.appendData('notify_class_protocol','1');}else{XHR.appendData('notify_class_protocol','0');}
	
		document.getElementById('ffm1notif').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SavePostfixNotificationsForm);
	}
		
	
	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function templates_postfix(){
$main=new bounces_templates();
$page=CurrentPageName();
	while (list ($num, $ligne) = each ($main->templates_array) ){
		$tmpl=$tmpl. Paragraphe('64-templates.png',$num,"{{$num}}","javascript:ShowTemplateFrom('$num')",null,210,null,0,true);
			
		}
$html=$html."
<center>
	<div id='id_templates'>$tmpl</div>
</center>
<script>
	function ShowTemplateFrom(template){
		YahooWin4(650,'$page?postfix-notifs-template='+template+'&hostname={$_GET["hostname"]}',template); 
	
	}
</script>

";		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function SaveParams(){
	$main=new maincf_multi($_GET["hostname"]);
	$main->SET_VALUE("double_bounce_sender",$_GET["double_bounce_sender"]);
	$main->SET_VALUE("address_verify_sender",$_GET["address_verify_sender"]);
	$main->SET_VALUE("2bounce_notice_recipient",$_GET["2bounce_notice_recipient"]);
	$main->SET_VALUE("error_notice_recipient",$_GET["error_notice_recipient"]);
	$main->SET_VALUE("delay_notice_recipient",$_GET["delay_notice_recipient"]);
	$main->SET_VALUE("empty_address_recipient",$_GET["empty_address_recipient"]);
		$notif["notify_class_software"]=$_GET["notify_class_software"];
		$notif["notify_class_resource"]=$_GET["notify_class_resource"];
		$notif["notify_class_policy"]=$_GET["notify_class_policy"];
		$notif["notify_class_delay"]=$_GET["notify_class_delay"];
		$notif["notify_class_2bounce"]=$_GET["notify_class_2bounce"];
		$notif["notify_class_bounce"]=$_GET["notify_class_bounce"];
		$notif["notify_class_protocol"]=$_GET["notify_class_protocol"];
		$main->SET_BIGDATA("notify_class",base64_encode(serialize($notif)));
			
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-notifs=yes&hostname={$_GET["hostname"]}");
}

function templates_postfix_form(){
	$template=$_GET["postfix-notifs-template"];
	$tpl=new templates();
	$page=CurrentPageName();
	$mainTPL=new bounces_templates();
	$main=new maincf_multi($_GET["hostname"]);
	
	$array=unserialize(base64_decode($main->GET_BIGDATA($template)));
	if(!is_array($array)){
		$array=$mainTPL->templates_array[$template];
	}
	$html="
		<div id='ffm1notif2'>
		<div class=explain>{{$template}}</div>
		<table style='width:100%' class=form>
		<tr>
			<td class=legend>Charset:</td>
			<td>" . Field_text('Charset',$array["Charset"],'width:90px;font-size:13px;padding:3px')."</td>
		</tr>
		<tr>
			<td class=legend>{mail_from}:</td>
			<td>" . Field_text('From',$array["From"],'width:200px;font-size:13px;padding:3px')."</td>
		</tr>
		<tr>
			<td class=legend>{subject}:</td>
			<td>" . Field_text('Subject',$array["Subject"],'width:290px;font-size:13px;padding:3px')."</td>
		</tr>	
		<tr>
			<td class=legend>Postmaster-Subject:</td>
			<td>" . Field_text('Postmaster-Subject',$array["Postmaster-Subject"],'width:290px;font-size:13px;padding:3px')."</td>
		</tr>	
		<tr>
			<td valign='top' colspan=2 align='right'>
			". button("{apply}","SavePostfixNotifTemplateForm()")."
			</td>
			
		</tr>	
		<tr>
			<td valign='top' colspan=2><textarea id='template-Body' style=';font-size:13px;padding:3px;width:100%;border:1px dotted #CCCCCC;height:200px;margin:4px;padding:4px'>{$array["Body"]}</textarea></td>
		</tr>
			
		</table>

	<script>
	
	var x_SavePostfixNotifTemplateForm= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		YahooWin4Hide();
		RefreshTab('main_config_postfix_notifs');
	}	
	
	function SavePostfixNotifTemplateForm(){
		var XHR = new XHRConnection();
		XHR.appendData('Charset',document.getElementById('Charset').value);
		XHR.appendData('From',document.getElementById('From').value);
		XHR.appendData('Subject',document.getElementById('Subject').value);
		XHR.appendData('Postmaster-Subject',document.getElementById('Postmaster-Subject').value);
		XHR.appendData('Body',document.getElementById('template-Body').value);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('template_save','$template');		
		document.getElementById('ffm1notif2').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'POST',x_SavePostfixNotifTemplateForm);
	}
		
	
	</script>		
		";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function templates_postfix_save(){
	$template=$_POST["template_save"];
	$main=new maincf_multi($_POST["hostname"]);
	$main->SET_BIGDATA($template,base64_encode(serialize($_POST)));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-notifs=yes&hostname={$_GET["hostname"]}");
	
}


function Isright(){
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsPostfixAdministrator){return true;}
	if(!$users->AsOrgStorageAdministrator){return false;}
	if(isset($_REQUEST["ou"])){if($_SESSION["ou"]<>$_GET["ou"]){return false;}}
	return true;
	
	}
