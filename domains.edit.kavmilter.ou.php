<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.postfix-multi.inc');
	include_once('ressources/class.mysql.inc');
	
	
	
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsOrgPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
	
	
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["popup"])){main_tabs();exit;}
	if(isset($_GET["EnableNotifications"])){SAVE_GROUP1();exit;}
	if(isset($_GET["ScanPolicy"])){SAVE_GROUP1();exit;}
	if(isset($_GET["RBL_ADD"])){add();exit;}
	if(isset($_GET["OURBLDEL"])){delete();exit;}
	
js();


function js(){
		
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_KAVMILTER}',"milter.index.php");
	
	
	$html="
		function OU_KAVMILTER(){
			YahooWin4('700','$page?popup=yes&ou=$ou','$title');
		
		}
		
var x_KAVMILTER_SAVECF= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	RefreshTab('main_rulle_kav4lms');
}
	
	function KAVMILTER_SAVECF(){
			var XHR = new XHRConnection();
			XHR.appendData('ScanPolicy',document.getElementById('ScanPolicy').value);
			XHR.appendData('UsePlaceholderNotice',document.getElementById('UsePlaceholderNotice').value);
			XHR.appendData('DefaultAction',document.getElementById('DefaultAction').value);
			XHR.appendData('SuspiciousAction',document.getElementById('SuspiciousAction').value);
			XHR.appendData('ProtectedAction',document.getElementById('ProtectedAction').value);
			XHR.appendData('ErrorAction',document.getElementById('ErrorAction').value);
			XHR.appendData('FilteredNameAction',document.getElementById('FilteredNameAction').value);
			XHR.appendData('ou','$ou');
			document.getElementById('img_KAV').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_KAVMILTER_SAVECF);
		
	}
	
	function OURBLDEL(ID){
		var XHR = new XHRConnection();
		XHR.appendData('OURBLDEL',ID);
		XHR.appendData('ou','$ou');
		XHR.sendAndLoad('$page', 'GET',x_OURBLADD);
	
	}		
	
	OU_KAVMILTER();";
	
	echo $html;
	
}

function main_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array["rule"]='{rule}';
	$array["config"]='{view_config}';
	$array["notifications"]='{notifications}';	
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num&tab=$num&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
	}
	

	echo "
	<div id=main_rulle_kav4lms style='width:100%;height:430px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_rulle_kav4lms').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
		
			});
		</script>";			
}

function main_switch(){
switch ($_GET["main"]) {
		case "rule":main_rule();exit;
		case "config":main_config_file();break;
	    case "notifications":main_notifications();break;
	  
	}
	
}

function main_config_file(){
	$sql="SELECT config FROM kavmilter WHERE ou='{$_GET["ou"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$ligne["config"]=trim($ligne["config"]);
	if($ligne["config"]==null){
			$tpl=new templates();
		echo "<center style='font-size:16px'>".$tpl->_ENGINE_parse_body("{error_no_datas}")."</center>";
		return;
	}
	
	$l=explode("\n",base64_decode($ligne["config"]));
	if(is_array($l)){
	while (list ($num, $val) = each ($l) ){
		if($val==null){continue;}
		echo "<div>$num<code>$val</code></div>";
	}}else{
		$tpl=new templates();
		echo "<center style='font-size:12px'>".$tpl->_ENGINE_parse_body("{error_no_datas}")."</center>";
	}
	
	
	
}

function main_notifications(){
$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	
	$ini=new Bs_IniHandler();
	$sql="SELECT config FROM kavmilter WHERE ou='$ou'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ini->loadString(base64_decode($ligne["config"]));
	$milter=$ini->_params["group.notifications"];	

	if($milter["EnableNotifications"]==null){$milter["EnableNotifications"]="no";}
	if($milter["AdminAddresses"]==null){$milter["AdminAddresses"]="postmaster@localhost";}
	if($milter["PostmasterAddress"]==null){$milter["PostmasterAddress"]="root@localhost";}
	if(trim($milter["MessageSubject"])==null){$milter["MessageSubject"]="Anti-virus notification message";}
	if(trim($milter["SenderSubject"])==null){$milter["SenderSubject"]="Anti-virus notification message (sender)";}
	if(trim($milter["ReceiverSubject"])==null){$milter["ReceiverSubject"]="Anti-virus notification message (recipient)";}
	if(trim($milter["AdminSubject"])==null){$milter["AdminSubject"]="Anti-virus notification message (Admin)";}
	
	
	
	
	
	$html="<div id='kavmnot'>
<table style='width:100%'>
				<tr>
				<td class=legend>{EnableNotifications}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("EnableNotifications",$milter["EnableNotifications"])."</td>
				<td align='left'>" . help_icon('{EnableNotifications_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{AdminAddresses}:</strong></td>
				<td align='left'>" . Field_text('AdminAddresses',$milter["AdminAddresses"],'width:250px')."</td>
				<td align='left'>" . help_icon('{AdminAddresses_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{PostmasterAddress}:</strong></td>
				<td align='left'>" . Field_text('PostmasterAddress',$milter["PostmasterAddress"],'width:250px')."</td>
				<td align='left'>&nbsp;</td>
				</tr>									
				<tr>
				<td class=legend>{MessageSubject}:</strong></td>
				<td align='left'>" . Field_text('MessageSubject',$milter["MessageSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{MessageSubject_text}') . "</td>
				</tr>		
				<tr>
				<td class=legend>{SenderSubject}:</strong></td>
				<td align='left'>" . Field_text('SenderSubject',$milter["SenderSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{SenderSubject_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{ReceiverSubject}:</strong></td>
				<td align='left'>" . Field_text('ReceiverSubject',$milter["ReceiverSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{ReceiverSubject_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{AdminSubject}:</strong></td>
				<td align='left'>" . Field_text('AdminSubject',$milter["AdminSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{AdminSubject_text}') . "</td>
				</tr>	
			</table>
			</div>";
	

	
if($milter["NotifyAdmin"]=="all"){$milter["NotifyAdmin"]="infected,protected,filtered,error";}
if($milter["NotifyRecipients"]=="all"){$milter["NotifyRecipients"]="infected,protected,filtered,error";}
if($milter["NotifySender"]=="all"){$milter["NotifySender"]="infected,protected,filtered,error";}



$NotifyAdminA=explode(",",$milter["NotifyAdmin"]);
$NotifyRecipientsA=explode(",",$milter["NotifyRecipients"]);
$NotifySenderA=explode(",",$milter["NotifySender"]);

while (list ($num, $val) = each ($NotifyAdminA) ){
	$NotifyAdmin[$val]=$val;
}
while (list ($num, $val) = each ($NotifyRecipientsA) ){
	$NotifyRecipients[$val]=$val;
}
while (list ($num, $val) = each ($NotifySenderA) ){
	$NotifySender[$val]=$val;
}

$not="<hr>
<table style='width:100%'>
	<tr>
		<th>&nbsp;</th>
		<tH align='center'>{infected}</th>
		<th align='center'>{protected}</tH>
		<th align='center'>{filtered}</th>
		<th align='center'>{error}</th>
	</tr>
	<tr>
		<td><strong>{administrator}</strong></td>
		<td align='center'>". Field_checkbox("admin_infected","infected",$NotifyAdmin["infected"])."</td>
		<td align='center'>". Field_checkbox("admin_protected","protected",$NotifyAdmin["protected"])."</td>
		<td align='center'>". Field_checkbox("admin_filtered","filtered",$NotifyAdmin["filtered"])."</td>
		<td align='center'>". Field_checkbox("admin_error","error",$NotifyAdmin["error"])."</td>
	</tR>
	<tr>
		<td><strong>{sender}</strong></td>
		<td align='center'>". Field_checkbox("sender_infected","infected",$NotifySender["infected"])."</td>
		<td align='center'>". Field_checkbox("sender_protected","protected",$NotifySender["protected"])."</td>
		<td align='center'>". Field_checkbox("sender_filtered","filtered",$NotifySender["filtered"])."</td>
		<td align='center'>". Field_checkbox("sender_error","error",$NotifySender["error"])."</td>
	</tR>
	<tr>
		<td><strong>{recipient}</strong></td>
		<td align='center'>". Field_checkbox("recipient_infected","infected",$NotifyRecipients["infected"])."</td>
		<td align='center'>". Field_checkbox("recipient_protected","protected",$NotifyRecipients["protected"])."</td>
		<td align='center'>". Field_checkbox("recipient_filtered","filtered",$NotifyRecipients["filtered"])."</td>
		<td align='center'>". Field_checkbox("recipient_error","error",$NotifyRecipients["error"])."</td>
	</tR>			
</table>
<div style='text-align:right'><hr>". button("{edit}","SAVE_NOTIFICATIONS()")."</div>

<script>
var x_SAVE_NOTIFICATIONSKAV= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	RefreshTab('main_rulle_kav4lms');
}
	
	function SAVE_NOTIFICATIONS(){
			var XHR = new XHRConnection();
			if(document.getElementById('admin_infected').checked){XHR.appendData('NotifyAdmin[]','infected');}
			if(document.getElementById('admin_protected').checked){XHR.appendData('NotifyAdmin[]','protected');}
			if(document.getElementById('admin_filtered').checked){XHR.appendData('NotifyAdmin[]','filtered');}
			if(document.getElementById('admin_error').checked){XHR.appendData('NotifyAdmin[]','error');}
			
			if(document.getElementById('sender_infected').checked){XHR.appendData('NotifySender[]','infected');}
			if(document.getElementById('sender_protected').checked){XHR.appendData('NotifySender[]','protected');}
			if(document.getElementById('sender_filtered').checked){XHR.appendData('NotifySender[]','filtered');}
			if(document.getElementById('sender_error').checked){XHR.appendData('NotifySender[]','error');}

			if(document.getElementById('recipient_infected').checked){XHR.appendData('NotifyRecipients[]','infected');}
			if(document.getElementById('recipient_protected').checked){XHR.appendData('NotifyRecipients[]','protected');}
			if(document.getElementById('recipient_filtered').checked){XHR.appendData('NotifyRecipients[]','filtered');}
			if(document.getElementById('recipient_error').checked){XHR.appendData('NotifyRecipients[]','error');}	

			XHR.appendData('EnableNotifications',document.getElementById('EnableNotifications').value);
			XHR.appendData('AdminAddresses',document.getElementById('AdminAddresses').value);
			XHR.appendData('PostmasterAddress',document.getElementById('PostmasterAddress').value);
			XHR.appendData('MessageSubject',document.getElementById('MessageSubject').value);
			XHR.appendData('SenderSubject',document.getElementById('SenderSubject').value);
			XHR.appendData('ReceiverSubject',document.getElementById('ReceiverSubject').value);
			XHR.appendData('SaveNotifications','1');
			XHR.appendData('ou','$ou');
			document.getElementById('kavmnot').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SAVE_NOTIFICATIONSKAV);
		
	}
</script>

";


	
	
$tpl=new templates();

echo $tpl->_ENGINE_parse_body($html.$not,"milter.index.php");
	
}

/*
 * 
 * NotifyAdmin=infected
NotifyRecipients=all
NotifySender=infected
 * 
tous:

NotifyAdmin=all
NotifyRecipients=all
NotifySender=all
 * 
 * 
 */


function main_rule(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	
	$ini=new Bs_IniHandler();
	$sql="SELECT config FROM kavmilter WHERE ou='$ou'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ini->loadString(base64_decode($ligne["config"]));
	
	$milter=$ini->_params;	
	$group_actions=$milter["group.actions"];
	$group_settings=$milter["group.settings"];
	
	
	
$html="
<table style='width:100%'>
<tr>
	<td valign='top'><img src='img/tank-256.png' id='img_KAV'></td>
	<td valign='top'>
	<table style=width:100%' class=table_form>
				<tr>
				<td class=legend nowrap>{ScanPolicy}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("message"=>"message","combined"=>"combined"),'ScanPolicy',$group_settings["ScanPolicy"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{ScanPolicy_text}') . "</td>
				</tr>
				<tr>
				<td class=legend nowrap>{UsePlaceholderNotice}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("UsePlaceholderNotice",$group_actions["UsePlaceholderNotice"])."</td>
				<td align='left'>" . help_icon('{UsePlaceholderNotice_text}') . "</td>
				</tr>				
				
				
				
				<tr>
				<td class=legend nowrap>{DefaultAction}:</strong></td>
					<td align='left'>" . 
					Field_array_Hash(array("warn"=>"warn","drop"=>"drop","reject"=>"reject","cure"=>"cure","delete"=>"delete","skip"=>"skip"),	
					'DefaultAction',$group_actions["DefaultAction"],null,null,0,'width:150px')."
				</td>
				<td align='left'>" . help_icon('{DefaultAction_text}') . "</td>
				</tr>				
				<tr>
				<td class=legend nowrap>{SuspiciousAction}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("warn"=>"warn","drop"=>"drop","reject"=>"reject","delete"=>"delete","skip"=>"skip"),'SuspiciousAction',$group_actions["SuspiciousAction"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{SuspiciousAction_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend nowrap>{ProtectedAction}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("delete"=>"delete","skip"=>"skip"),'ProtectedAction',$group_actions["ProtectedAction"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{ProtectedAction_text}') . "</td>
				</tr>
				<tr>
				<td class=legend nowrap>{ErrorAction}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("warn"=>"warn","delete"=>"delete","skip"=>"skip"),'ErrorAction',$group_actions["ErrorAction"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{ErrorAction_text}') . "</td>
				</tr>
				<tr>
				<td class=legend nowrap>{FilteredNameAction}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("warn"=>"warn","delete"=>"delete","skip"=>"skip"),'FilteredNameAction',$group_actions["FilteredNameAction"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{FilteredNameAction_text}') . "</td>
				</tr>				
				
				
				
				<tr>
				<td colspan=3 align='right'>
					<hr>".button("{edit}","KAVMILTER_SAVECF()")."
				</td>
				</tr>		
				
				</table>
			</td>
		</tr>
	</table>";	
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php,milter.index.php");	
}

function SAVE_GROUP1(){
	$ou=$_GET["ou"];
	$sql="SELECT ou,config FROM kavmilter WHERE ou='$ou'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($ligne["config"]));
	$milter_notifs=$ini->_params["group.notifications"];
	$group_settings=$ini->_params["group.settings"];
	$group_actions=$ini->_params["group.actions"];
	
	$group_filter=$ini->_params["group.filter"];
	
	$sock=new sockets();
	
	$group_notifications=$ini->_params["group.notifications"];	

	if(isset($_GET["ScanPolicy"])){$group_settings["ScanPolicy"]=$_GET["ScanPolicy"];}
	if(isset($_GET["DefaultAction"])){$group_actions["DefaultAction"]=$_GET["DefaultAction"];}
	if(isset($_GET["ErrorAction"])){$group_actions["ErrorAction"]=$_GET["ErrorAction"];}
	if(isset($_GET["ProtectedAction"])){$group_actions["ProtectedAction"]=$_GET["ProtectedAction"];}
	if(isset($_GET["SuspiciousAction"])){$group_actions["SuspiciousAction"]=$_GET["SuspiciousAction"];}
	if(isset($_GET["UsePlaceholderNotice"])){$group_actions["UsePlaceholderNotice"]=$_GET["UsePlaceholderNotice"];}
	if(isset($_GET["FilteredNameAction"])){$group_filter["FilteredNameAction"]=$_GET["FilteredNameAction"];}
	
	
	
	if($group_filter["FilteredNameAction"]==null){$group_filter["FilteredNameAction"]="skip";}
	
	if($ligne["ou"]==null){$add=true;}
	$conf=$conf."[group.definition]\n";
	$conf=$conf."GroupName=touzeau\n";
	$conf=$conf."Priority=10\n";
	$conf=$conf."Recipients=\n";
	$conf=$conf."Senders=\n";
	$conf=$conf."\n";
	$conf=$conf."[group.settings]\n";
	$conf=$conf."AddDisclaimer=no\n";
	$conf=$conf."AddXHeaders=yes\n";
	$conf=$conf."ScanPolicy={$group_settings["ScanPolicy"]}\n";
	$conf=$conf."\n";
	$conf=$conf."[group.actions]\n";
	$conf=$conf."DefaultAction={$group_actions["DefaultAction"]}\n";
	$conf=$conf."ErrorAction={$group_actions["ErrorAction"]}\n";
	$conf=$conf."ProtectedAction={$group_actions["ProtectedAction"]}\n";
	$conf=$conf."SuspiciousAction={$group_actions["SuspiciousAction"]}\n";
	$conf=$conf."UsePlaceholderNotice={$group_actions["UsePlaceholderNotice"]}\n";
	$conf=$conf."VirusNameAction=drop\n";
	$conf=$conf."VirusNameList=\n";
	$conf=$conf."\n";
	$conf=$conf."[group.filter]\n";
	$conf=$conf."ExcludeMime=\n";
	$conf=$conf."ExcludeName=\n";
	$conf=$conf."ExcludeSize=\n";
	$conf=$conf."FilteredMimeAction=skip\n";
	$conf=$conf."FilteredNameAction={$group_filter["FilteredNameAction"]}\n";
	$conf=$conf."FilteredSizeAction=skip\n";
	$conf=$conf."IncludeMime=\n";
	$conf=$conf."IncludeName=\n";
	$conf=$conf."IncludeSize=\n";
	$conf=$conf."\n";
	$conf=$conf."[group.notifications]\n";
	
	
	if(isset($_GET["AdminAddresses"])){$group_notifications["AdminAddresses"]=$_GET["AdminAddresses"];}
	if(isset($_GET["AdminSubject"])){$group_notifications["AdminSubject"]=$_GET["AdminSubject"];}
	if(isset($_GET["EnableNotifications"])){$group_notifications["EnableNotifications"]=$_GET["EnableNotifications"];}
	if(isset($_GET["PostmasterAddress"])){$group_notifications["PostmasterAddress"]=$_GET["PostmasterAddress"];}
	if(isset($_GET["ReceiverSubject"])){$group_notifications["ReceiverSubject"]=$_GET["ReceiverSubject"];}
	if(isset($_GET["SenderSubject"])){$group_notifications["SenderSubject"]=$_GET["SenderSubject"];}
	$SendmailPath=trim(base64_decode($sock->getFrameWork("cmd.php?SendmailPath=yes")));
	
	if(isset($_GET["SaveNotifications"])){
		if(!isset($_GET["NotifyAdmin"])){$group_notifications["NotifyAdmin"]="none";}
		if(!isset($_GET["NotifyRecipients"])){$group_notifications["NotifyRecipients"]="none";}
		if(!isset($_GET["NotifySender"])){$group_notifications["NotifySender"]="none";}
		if(trim($_GET["MessageSubject"])==null){$_GET["MessageSubject"]="Anti-virus notification message";}
		if(trim($_GET["SenderSubject"])==null){$_GET["SenderSubject"]="Anti-virus notification message (sender)";}
		if(trim($_GET["ReceiverSubject"])==null){$_GET["ReceiverSubject"]="Anti-virus notification message (recipient)";}
		if(trim($_GET["AdminSubject"])==null){$_GET["AdminSubject"]="Anti-virus notification message (Admin)";}		
		
		
	}
	
	if(isset($_GET["NotifyAdmin"])){$group_notifications["NotifyAdmin"]=@implode(",",$_GET["NotifyAdmin"]);}
	if(isset($_GET["NotifyRecipients"])){$group_notifications["NotifyRecipients"]=@implode(",",$_GET["NotifyRecipients"]);}
	if(isset($_GET["NotifySender"])){$group_notifications["NotifySender"]=@implode(",",$_GET["NotifySender"]);}
	
	if($group_notifications["EnableNotifications"]==null){$group_notifications["EnableNotifications"]="no";}
	if($group_notifications["NotifySender"]==null){$group_notifications["NotifySender"]="none";}
	if($group_notifications["NotifyAdmin"]==null){$group_notifications["NotifyAdmin"]="none";}
	if($group_notifications["AdminAddresses"]==null){$group_notifications["AdminAddresses"]="root@localhost.localdomain";}
	
	$conf=$conf."AdminAddresses={$group_notifications["AdminAddresses"]}\n";
	$conf=$conf."AdminSubject={$group_notifications["AdminSubject"]}\n";
	$conf=$conf."Charset=us-ascii\n";
	$conf=$conf."EnableNotifications={$group_notifications["EnableNotifications"]}\n";
	$conf=$conf."MessageDir=/var/db/kav/5.6/kavmilter/templates\n";
	$conf=$conf."MessageSubject={$group_notifications["MessageSubject"]}\n";
	$conf=$conf."NotifyAdmin={$group_notifications["NotifyAdmin"]}\n";
	$conf=$conf."NotifyRecipients={$group_notifications["NotifyRecipients"]}\n";
	$conf=$conf."NotifySender={$group_notifications["NotifySender"]}\n";
	$conf=$conf."PostmasterAddress={$group_notifications["PostmasterAddress"]}\n";
	$conf=$conf."ReceiverSubject={$group_notifications["ReceiverSubject"]}\n";
	$conf=$conf."RejectReply=Message rejected because it contains malware\n";
	$conf=$conf."SenderSubject={$group_notifications["SenderSubject"]}\n";
	$conf=$conf."SendmailPath=$SendmailPath\n";
	$conf=$conf."TransferEncoding=7bit\n";
	$conf=$conf."UseCustomTemplates=no\n";
	$conf=$conf."\n";
	$conf=$conf."[group.backup]\n";
	$conf=$conf."BackupDir=/var/db/kav/5.6/kavmilter/backup/\n";
	$conf=$conf."BackupOption=all\n";
	$conf=$conf."BackupPolicy=info\n";
	$conf=$conf."\n";
	
	$conf_mls=$conf_mls."[kav4lms:groups.$ou]\n";
	$conf_mls=$conf_mls."\n";
	$conf_mls=$conf_mls."[kav4lms:groups.$ou.definition]\n";
	$conf_mls=$conf_mls."Priority=10\n";
	$conf_mls=$conf_mls."Recipients=\n";
	$conf_mls=$conf_mls."Senders=\n";;
	$conf_mls=$conf_mls."\n";
	$conf_mls=$conf_mls."[kav4lms:groups.$ou.settings]\n";
	$conf_mls=$conf_mls."AddDisclaimer=no\n";
	$conf_mls=$conf_mls."AddXHeaders=message\n";
	$conf_mls=$conf_mls."Check=all\n";
	$conf_mls=$conf_mls."MIMEEncodingHeuristics=no\n";
	$conf_mls=$conf_mls."MaxScanDepth=10\n";
	$conf_mls=$conf_mls."MaxScanTime=30\n";
	$conf_mls=$conf_mls."RejectReply=Message rejected because it contains malware\n";
	$conf_mls=$conf_mls."ScanArchives=yes\n";
	$conf_mls=$conf_mls."ScanPacked=yes\n";
	$conf_mls=$conf_mls."ScanPolicy={$group_settings["ScanPolicy"]}\n";
	$conf_mls=$conf_mls."UseAVBasesSet=standard\n";
	$conf_mls=$conf_mls."UseCodeAnalyzer=yes\n";
	$conf_mls=$conf_mls."UsePlaceholderNotice=yes\n";
	$conf_mls=$conf_mls."\n";
	$conf_mls=$conf_mls."[kav4lms:groups.$ou.actions]\n";
	$conf_mls=$conf_mls."DefaultAction={$group_actions["DefaultAction"]}\n";
	$conf_mls=$conf_mls."ErrorAction={$group_actions["ErrorAction"]}\n";
	$conf_mls=$conf_mls."ProtectedAction={$group_actions["ProtectedAction"]}\n";
	$conf_mls=$conf_mls."SuspiciousAction={$group_actions["SuspiciousAction"]}\n";
	$conf_mls=$conf_mls."UsePlaceholderNotice={$group_actions["UsePlaceholderNotice"]}\n";
	$conf_mls=$conf_mls."VirusNameAction=drop\n";
	$conf_mls=$conf_mls."FilteredMimeAction=skip\n";
	$conf_mls=$conf_mls."FilteredNameAction={$group_filter["FilteredNameAction"]}\n";
	$conf_mls=$conf_mls."FilteredSizeAction=skip\n";
	$conf_mls=$conf_mls."InfectedAction=skip\n";
	$conf_mls=$conf_mls."VirusNameAction=drop\n";
	$conf_mls=$conf_mls."\n";
	$conf_mls=$conf_mls."[kav4lms:groups.$ou.contentfiltering]\n";
	$conf_mls=$conf_mls."ExcludeMime=\n";
	$conf_mls=$conf_mls."ExcludeName=\n";
	$conf_mls=$conf_mls."ExcludeSize=\n";
	$conf_mls=$conf_mls."IncludeMime=\n";
	$conf_mls=$conf_mls."IncludeName=\n";
	$conf_mls=$conf_mls."IncludeSize=\n";
	$conf_mls=$conf_mls."RenameTo=.vir\n";
	$conf_mls=$conf_mls."VirusNameList=\n";
	$conf_mls=$conf_mls."\n";
	$conf_mls=$conf_mls."[kav4lms:groups.$ou.notifications]\n";
	$conf_mls=$conf_mls."AdminAddresses={$group_notifications["PostmasterAddress"]}\n";
	$conf_mls=$conf_mls."AdminSubject={$group_notifications["AdminSubject"]}\n";
	$conf_mls=$conf_mls."Charset=us-ascii\n";
	$conf_mls=$conf_mls."NotifyAdmin={$group_notifications["NotifyAdmin"]}\n";
	$conf_mls=$conf_mls."NotifyRecipients={$group_notifications["NotifyRecipients"]}\n";
	$conf_mls=$conf_mls."NotifySender={$group_notifications["NotifySender"]}\n";
	$conf_mls=$conf_mls."PostmasterAddress=POSTMASTER@localhost\n";
	$conf_mls=$conf_mls."RecipientsSubject={$group_notifications["ReceiverSubject"]}\n";
	$conf_mls=$conf_mls."SenderSubject={$group_notifications["SenderSubject"]}\n";
	$conf_mls=$conf_mls."Subject={$group_notifications["MessageSubject"]}\n";
	$conf_mls=$conf_mls."Templates=/etc/opt/kaspersky/kav4lms/templates/en\n";
	$conf_mls=$conf_mls."TransferEncoding=7bit\n";
	$conf_mls=$conf_mls."UseCustomTemplates=no\n";
	$conf_mls=$conf_mls."\n";
	$conf_mls=$conf_mls."[kav4lms:groups.$ou.backup]\n";
	$conf_mls=$conf_mls."Destination=/var/opt/kaspersky/kav4lms/backup/\n";
	$conf_mls=$conf_mls."Options=all\n";
	$conf_mls=$conf_mls."Policy=info\n";	
		
	$sql="UPDATE kavmilter SET config='".base64_encode($conf)."',configlms='".base64_encode($conf_mls)."' WHERE ou='$ou';";
	if($add){
		$sql="INSERT INTO kavmilter(ou,config,configlms) VALUES('$ou','".base64_encode($conf)."','".base64_encode($conf_mls)."')";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	
	
	$sock->getFrameWork("cmd.php?kavmilter-configure");
	
}

?>