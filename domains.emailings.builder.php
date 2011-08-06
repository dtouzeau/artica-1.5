<?php

	include_once('ressources/class.emailings.inc');
	
	
	if(isset($_GET["mailer-settings"])){mailer_settings_js();exit;}
	if(isset($_GET["mailer-settings-popup"])){mailer_settings_popup();exit;}
	if(isset($_GET["mailer-settings-add"])){mailer_settings_popup_add();exit;}
	if(isset($_GET["mailer-settings-list"])){mailer_settings_popup_list();exit;}
	if(isset($_GET["smtp_server_name"])){mailer_settings_popup_save();exit;}
	if(isset($_GET["mailer-settings-del"])){mailer_settings_popup_del();exit;}
	
	
	if(isset($_GET["mailer-link"])){mailer_link_js();exit;}
	if(isset($_GET["mailer-link-popup"])){mailer_link_popup();exit;}
	if(isset($_GET["mailer-link-send"])){mailer_link_send();exit;}
	if(isset($_GET["mailer_link_popup_toolbar_div"])){mailer_link_popup_toolbar();exit;}
	if(isset($_GET["mailer-link-event"])){mailer_link_popup_event();exit;}
	
	if(isset($_GET["MailerLinkAdd"])){mailer_link_add();exit;}
	if(isset($_GET["MailerLinkDelete"])){mailer_link_del();exit;}
	
	if(isset($_GET["MailerLink-infos"])){mailer_link_infos();exit;}
	if(isset($_GET["MailerLink-reconstruct"])){mailer_link_rebuild_queue();exit;}
	
	if(isset($_GET["emailing-smtp-server-list"])){emailrelay_list();exit;}
	if(isset($_GET["emailing-right-panel"])){right_panel();exit;}
	
	if(isset($_GET["popup"])){popup();exit;}
	
	
	
	
builder_list();


function mailer_link_js(){
	
$page=CurrentPageName();
	$tpl=new templates();
	$emailing=$tpl->_ENGINE_parse_body("{emailing}");
	
	$html="
		function mailer_engine_settings_load(){
			YahooWin4(710,'$page?mailer-link-popup={$_GET["ou"]}&ou={$_GET["ou"]}&ID={$_GET["ID"]}','$emailing');
		}
	
	mailer_engine_settings_load();	
	";	
	
	echo $html;
	
}


function mailer_link_popup_toolbar(){
	
if($_GET["ID"]<1){return;}
		$q=new mysql();
		$sql="SELECT * FROM emailing_campain_linker WHERE ID='{$_GET["ID"]}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$delete=Paragraphe("database-linker-delete-64.png","{delete}","{emailing_campain_linker_delete}","javascript:MailerLinkDelete()");	
		$send=Paragraphe("send-database-64.png","{send_emailing}","{send_emailing_text}","javascript:MailerLinkSend()");
		
		if($ligne["locked"]==1){
			$send=Paragraphe("send-database-grey-64.png","{send_emailing}","{$ligne["queue_builder_pourc"]}% ","javascript:MailerLinkSend()");
			$delete=null;
		}
	
		
	$html="
	$delete
	$send
	<br>
	<div style='width:100%;text-align:right'>". imgtootltip("refresh-24.png","{refresh}","mailerlinkInfos()")."</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function mailer_link_popup_event(){
		if($_GET["ID"]<1){return;}
		$q=new mysql();
		$sql="SELECT * FROM emailing_campain_events WHERE ID='{$_GET["ID"]}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$template=emailing_get_template_name($ligne["template_id"]);
		$database=emailing_get_database_name($ligne["database_id"]);
		$size=FormatBytes($ligne["massmailing_size"]/1024);
		
		$events=@explode("\n",$ligne["events_details"]);
		while (list ($num, $line) = each ($events) ){
			if($ligne==null){continue;}
			$txt=$txt."<div><code>$line</code></div>";
		}
		
		$html="
		<table style='width:100%'>
		<tr>
			<td class=legend style='font-size:13px'>{date}:</td>
			<td style='font-size:13px'><strong>{$ligne["zDate"]}</strong></td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px'>{template}:</td>
			<td style='font-size:13px'><strong>$template</strong></td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px'>{database}:</td>
			<td style='font-size:13px'><strong>$database</strong></td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px'>{duration}:</td>
			<td style='font-size:13px'><strong>{$ligne["time_duration"]}</strong></td>
		</tr>							
		<tr>
			<td class=legend style='font-size:13px'>{emails_number}:</td>
			<td style='font-size:13px'><strong>{$ligne["messages_number"]}</strong></td>
		</tr>			
		<tr>
			<td class=legend style='font-size:13px'>{global_size}:</td>
			<td style='font-size:13px'><strong>$size</strong></td>
		</tr>			
		<tr>
			<td colspan=2>
			<hr>
			<div style='width:100%;height:250px;overflow:auto'>$txt</div></td>
		</tr>
		</table>";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	

	

}


function mailer_link_popup(){
	$ou=base64_decode($_GET["ou"]);
	$tpl=new templates();
	$emailing_campain_linker_delete_confirm=$tpl->javascript_parse_text("{emailing_campain_linker_delete_confirm}");
	$page=CurrentPageName();
	$template_name=$tpl->javascript_parse_text("{template_name}");	
	$database_lang=$tpl->javascript_parse_text("{database}");	
	$perform_operation_ask=$tpl->javascript_parse_text("{perform_operation_ask}");
	
	$sql="SELECT ID,template_name FROM emailing_templates WHERE ou='$ou' ORDER BY template_name";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$templates[$ligne["ID"]]=$ligne["template_name"];
	}
	
	$sql="SELECT ID,databasename FROM emailing_db_paths WHERE ou='$ou' ORDER BY databasename";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$dbs[$ligne["ID"]]=$ligne["databasename"] ." (".emailing_builder_get_queue_number($ligne["ID"])." {contacts})";
	}	
	
	if($_GET["ID"]>0){
		$sql="SELECT * FROM emailing_campain_linker WHERE ID='{$_GET["ID"]}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["parameters"]));
	}
	
	
	
	$dbs[null]="{select}";
	$templates[null]="{select}";
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div id='mailer_link_popup_toolbar_div'></div>
	</td>
	<td valign='top'>
	<div id='MailerLinkDIVID'>
	<input type='hidden' id='MailerLinkID' value='{$_GET["ID"]}'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{campain_name}:</td>
		<td>". Field_text("campain_name",$ligne["name"],"font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{template_name}:</td>
		<td>". Field_array_Hash($templates,"template_id",$ligne["template_id"],null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{database}:</td>
		<td>". Field_array_Hash($dbs,"database_id",$ligne["database_id"],null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{simulation_mode}:</td>
		<td>". Field_checkbox("simulation",1,$array["simulation"],"MailerLinkSimulation()")."</td>
	</tr>
	<tr><td>&nbsp;</td><td><p class=caption>{simulation_mode_explain}</p></td></tr>
	<tr>
		<td class=legend style='font-size:13px'>{recipient}:</td>
		<td>". Field_text("recipient",$array["recipient"],"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","MailerLinkEdit()")."</td>
	</tr>
	</table>
</div>
</td>
</tr>
</table>
<hr>
<div style='width:100%;text-align:right'>". imgtootltip("refresh-24.png","{refresh}","mailerlinkInfos()")."</div>
<div id='mailerlinkInfos'></div>
	
	<script>
		function MailerLinkSimulation(){
			if(document.getElementById('simulation').checked){
				document.getElementById('recipient').disabled=false;
			}else{
				document.getElementById('recipient').disabled=true;
			}
		
		}
		
		
	var x_MailerLinkEdit= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin4Hide();
			RefreshTab('emailing_campaigns');	
		}			
		
		function MailerLinkEdit(){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			var template_id=document.getElementById('template_id').value;
			var database_id=document.getElementById('database_id').value;
			if(template_id.length==0){
				alert('$template_name !');
				return;
			}
			
			if(database_id.length==0){
				alert('$database_lang !');
				return;
			}			
			
			XHR.appendData('MailerLinkAdd','yes');
			XHR.appendData('ID',document.getElementById('MailerLinkID').value);
			XHR.appendData('template_id',template_id);
			XHR.appendData('database_id',database_id);
			XHR.appendData('recipient',document.getElementById('recipient').value);
			XHR.appendData('campain_name',document.getElementById('campain_name').value);
			XHR.appendData('ou','{$_GET["ou"]}');
			if(document.getElementById('simulation').checked){XHR.appendData('simulation',1);}else{XHR.appendData('simulation',0);}
			document.getElementById('MailerLinkDIVID').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_MailerLinkEdit);
		}
		
		
		function mailerlinkInfos(){
			LoadAjax('mailerlinkInfos','$page?MailerLink-infos={$ligne["ID"]}&ou={$_GET["ou"]}&ID={$ligne["ID"]}');
			LoadAjax('mailer_link_popup_toolbar_div','$page?mailer_link_popup_toolbar_div=yes&ID={$_GET["ID"]}&ou={$_GET["ou"]}');
			
		}
		
		function MailerLinkDelete(){
			if(!confirm('$emailing_campain_linker_delete_confirm')){return;}
			var XHR = new XHRConnection();
			XHR.appendData('MailerLinkDelete','yes');
			XHR.appendData('ID','{$_GET["ID"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			document.getElementById('MailerLinkDIVID').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_MailerLinkEdit);
		}
		
		
	var x_MailerLinkSend= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('emailing_campaigns');
			mailerlinkInfos();	
		}		
		
		
		function MailerLinkSend(){
			if(confirm('$perform_operation_ask')){		
				var XHR = new XHRConnection();
				XHR.appendData('mailer-link-send','yes');
				XHR.appendData('ID','{$_GET["ID"]}');
				XHR.appendData('ou','{$_GET["ou"]}');
				document.getElementById('mailer_link_popup_toolbar_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_MailerLinkSend);
			}
		}
		
		
		function MailerLinkEventDisplay(ID){
			YahooWin5(650,'$page?mailer-link-event={$_GET["ou"]}&ou={$_GET["ou"]}&ID='+ID,ID);
		}
		

		
		
	MailerLinkSimulation();
	mailerlinkInfos();
	
	</script>
	
	";

	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function mailer_link_add(){
	$p=base64_encode(serialize($_GET));
	$ou=base64_decode($_GET["ou"]);
	$sql="INSERT INTO emailing_campain_linker (template_id,database_id,parameters,ou,name,zdate)
	VALUES('{$_GET["template_id"]}','{$_GET["database_id"]}','$p','$ou','{$_GET["campain_name"]}',NOW())";
	
	if($_GET["ID"]>0){
		$sql="UPDATE emailing_campain_linker SET 
			template_id='{$_GET["template_id"]}',
			database_id='{$_GET["database_id"]}',
			name ='{$_GET["campain_name"]}',
			parameters='$p' WHERE ID='{$_GET["ID"]}'";
	}
	
	$q=new mysql();
	$q->check_emailing_tables();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\nLine:".__LINE__."\nOf:".basename(__FILE__);return;}
	
	
}

function mailer_link_del(){
	$ou=base64_decode($_GET["ou"]);
	$sql="DELETE FROM emailing_campain_queues WHERE campain_linker_id={$_GET["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM emailing_campain_linker WHERE ID={$_GET["ID"]} AND ou='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="DELETE FROM emailing_campain_events WHERE campain_linker_id={$_GET["ID"]}";
	$q->QUERY_SQL($sql,"artica_backup");	
	
}



function mailer_settings_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{mailer_engine_settings}");
	$add_server=$tpl->_ENGINE_parse_body("{add_new_smtp_server}");
	$edit=$tpl->_ENGINE_parse_body("{edit}");
	
	$html="
		function mailer_engine_settings_load(){
			YahooWin4(600,'$page?mailer-settings-popup={$_GET["ou"]}&ou={$_GET["ou"]}','$title');
		}
		
		function mailer_engine_settings_add(){
			YahooWin5(435,'$page?mailer-settings-add={$_GET["ou"]}&ou={$_GET["ou"]}&ID={$_GET["ID"]}','$add_server');
		}
		
		function mailer_settings_popup_edit(ID){
			YahooWin5(435,'$page?mailer-settings-add={$_GET["ou"]}&ou={$_GET["ou"]}&ID='+ID,'$edit');
		}
		
		function mailer_engine_settings_delete(ID){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('mailer-settings-del',ID);
			document.getElementById('mailer_settings_popup_add_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_mailer_engine_settings_save);
		}	

		
	var x_mailer_engine_settings_save= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			mailer_engine_settings_list();
			RefreshTab('emailing_campaigns');
			YahooWin5Hide();
		}			
		
function mailer_engine_settings_save(){
	var XHR = new XHRConnection();
	XHR.appendData('smtp_server_name',document.getElementById('smtp_server_name').value);
	XHR.appendData('smtp_server_port',document.getElementById('smtp_server_port').value);
	XHR.appendData('smtp_auth_user',document.getElementById('smtp_auth_user').value);
	XHR.appendData('smtp_auth_passwd',document.getElementById('smtp_auth_passwd').value);
	XHR.appendData('ID',document.getElementById('ID').value);
	if(document.getElementById('tls_enabled').checked){XHR.appendData('tls_enabled',1);}else{XHR.appendData('tls_enabled',0);}
	XHR.appendData('ou','{$_GET["ou"]}');
	document.getElementById('mailer_settings_popup_add_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_mailer_engine_settings_save);	
	}

function mailer_engine_settings_list(){
	LoadAjax('mailer_settings_popup_div','$page?mailer-settings-list=yes&ou={$_GET["ou"]}');
}
	
mailer_engine_settings_load();";
	
echo $html;	
	
	
}

function mailer_settings_popup_del(){
	$ou=base64_decode($_GET["ou"]);
	$sql="DELETE FROM emailing_mailers WHERE ID='{$_GET["mailer-settings-del"]}' AND ou='$ou'";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?emailing-remove-emailrelays={$_GET["mailer-settings-del"]}");
	$sock->getFrameWork("cmd.php?emailing-build-emailrelays=yes");
}


function mailer_settings_popup_save(){
	$ou=base64_decode($_GET["ou"]);
	if($_GET["smtp_server_port"]==null){$_GET["smtp_server_port"]=25;}	
	$datas=base64_encode(serialize($_GET));
	$ID=$_GET["ID"];
	$sql_add="INSERT INTO emailing_mailers (smtpserver,parameters,ou)
	VALUES('{$_GET["smtp_server_name"]}','$datas','$ou');";
	
	$sql_edit="UPDATE emailing_mailers SET smtpserver='{$_GET["smtp_server_name"]}',parameters='$datas' WHERE ID=$ID";
	$sql=$sql_add;
	if($ID>0){$sql=$sql_edit;}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?emailing-build-emailrelays=yes");
	
	
	}


function mailer_settings_popup(){
		$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div style='font-size:13px'>
	{mailer_engine_settings_explain}	
	</div>
	<div style='text-align:right'>". button("{add_new_smtp_server}","mailer_engine_settings_add()")."</div>
	<hr>
	<div style='width:100%;height:240px;overflow:auto' id='mailer_settings_popup_div'></div>
	<div style='text-align:right'>". imgtootltip("32-refresh.png","{refresh}","mailer_engine_settings_list()")."</div>
	<script>
		mailer_engine_settings_list();
	</script>
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);	
}

function mailer_settings_popup_list(){
	$ou=base64_decode($_GET["ou"]);
	$sock=new sockets();
	$sql="SELECT * FROM emailing_mailers WHERE ou='$ou' ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$html="<table style='width:99%;padding:3px;margin:3px;border:1px solid #CCCCCC'>
	<tr>
		<th colspan=2>{server}</th>
		<th>{emailrelay_status}</th>
	</tr>";
	
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?emailrelay-ou-status=yes&ou={$_GET["ou"]}")));
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$status_text="-";
		$js="mailer_settings_popup_edit({$ligne["ID"]})";
		
		$status=$ini->_params["APP_EMAILRELAY_{$ligne["ID"]}"];
		if($status["master_memory"]>0){
			$mem=FormatBytes($status["master_memory"]);
			$process_queue=$status["queue_num"];
			$status_text="{APP_EMAILRELAY}: {running}, PID {$status["master_pid"]} - $mem {memory}";
		}
		
		
		
		
		$html=$html."<tr ". CellRollOver($js,"{edit}").">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:14px'>{$ligne["smtpserver"]}</td>
		<td><strong style='font-size:12px'>$status_text</td>
		</tr>";
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function mailer_settings_popup_add(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ID=$_GET["ID"];
	$button_name="{add}";
	
	if($ID>0){
		$q=new mysql();
		$sql="SELECT * FROM emailing_mailers WHERE ID=$ID";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$datas=unserialize(base64_decode($ligne["parameters"]));
		
		$button_name="{edit}";
		$delete="
			<tr>
				<td colspan=2 align='right'>". imgtootltip("delete-32.png","{delete}","mailer_engine_settings_delete($ID)")."</strong></td>
			</tr>
		";
	}

$html="
	<div id='mailer_settings_popup_add_div'>
	<input type='hidden' id='ID' value='$ID'>
	<table style='width:100%'>
	$delete
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_server_name}:</strong></td>
		<td>" . Field_text('smtp_server_name',$ligne["smtpserver"],'width:150px;font-size:13px;padding:3px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_server_port}:</strong></td>
		<td>" . Field_text('smtp_server_port',$datas["smtp_server_port"],'width:30px')."</td>
	</tr>	
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_auth_user}:</strong></td>
		<td>" . Field_text('smtp_auth_user',$datas["smtp_auth_user"],'width:150px;font-size:13px;padding:3px')."</td>
	</tr>	
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_auth_passwd}:</strong></td>
		<td>" . Field_password('smtp_auth_passwd',$datas["smtp_auth_passwd"],'width:150px;font-size:13px;padding:3px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{tls_enabled}:</strong></td>
		<td>" . Field_checkbox("tls_enabled",1,$datas["tls_enabled"],'{enable_disable}')."</td>
	</tr>	
	<tr>
	<td align='right' colspan=2><hr>
		".button($button_name,"mailer_engine_settings_save()")."</td>
	</tr>
</table>
</div>
";	

echo $tpl->_ENGINE_parse_body($html);	
}


function builder_list(){
	$page=CurrentPageName();
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'><div id='emailing_smtp_server_list'></div></td>
		<td valign='top'><div id='emailing_right_panel'></div></td>
	</tr>
	</table>
	<div style='text-align:right'>". imgtootltip("32-refresh.png","{refresh}","resfresh_emailing_panel()")."</div>
	
	<script>
	
	   function resfresh_emailing_panel(){
		LoadAjax('emailing_smtp_server_list','$page?emailing-smtp-server-list=yes&ou={$_GET["ou"]}');
		LoadAjax('emailing_right_panel','$page?emailing-right-panel=yes&ou={$_GET["ou"]}');
	   }
	   resfresh_emailing_panel();
	</script>
	";
	
	echo $html;
}


function right_panel(){
	$oud=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$tr[]=Paragraphe("server-mail-add-64.png","{add_emailing}","{add_emailing_text}","javascript:Loadjs('$page?ou={$_GET["ou"]}&mailer-link=yes')");
	$tr[]=Paragraphe("parameters2-64.png",
	"{mailer_engine_settings}","{mailer_engine_settings_text}",
	"javascript:Loadjs('$page?ou={$_GET["ou"]}&mailer-settings=yes')");
	
	$sql="SELECT * FROM emailing_campain_linker WHERE ou='$oud' ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$dbname=emailing_get_database_name($ligne["database_id"]);
			$tpl_name=emailing_get_template_name($ligne["template_id"]);
			$messages_number=emailing_builder_get_queue_number($ligne["database_id"]);
			$tr[]=Paragraphe("server-mail-64.png",
			$ligne["name"],"$messages_number {emails}<br>$dbname, $tpl_name",
			"javascript:javascript:Loadjs('$page?ou={$_GET["ou"]}&mailer-link=yes&ID={$ligne["ID"]}')");
		}
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
	$tables[]="</table>";	
	$tbl=$tpl->_ENGINE_parse_body(@implode("\n",$tables));	

	echo $tbl;
	
	
}

function mailer_link_infos(){
	
	$html="<table>
	<tr>
		<th colspan=2>{date}</th>
		<th>{template}</th>
		<th>{database}</th>
		<th>{duration}</th>
		<th>{emails_number}</th>
		<th>{global_size}</th>
		
	</tr>";
	
	$q=new mysql();
	$sql="SELECT * FROM emailing_campain_events WHERE campain_linker_id='{$_GET["ID"]}' ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$ct=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ct=$ct+1;
		$img="status_ok.gif";
		$size=FormatBytes($ligne["massmailing_size"]/1024);
		if($ligne["task_success"]==0){
			$img="status_critical.gif";
		}
		
		$template=emailing_get_template_name($ligne["template_id"]);
		$database=emailing_get_database_name($ligne["database_id"]);
		
		$html=$html."<tr ". CellRollOver("MailerLinkEventDisplay({$ligne["ID"]})","{infos}").">
		<td width=1%><img src='img/$img'></td>
		<td style='font-size:11px' nowrap>{$ligne["zDate"]}</td>
		<td style='font-size:11px'>$template</td>
		<td style='font-size:11px'>$database</td>
		<td style='font-size:11px'>{$ligne["time_duration"]}</td>
		<td style='font-size:11px' align='center'>{$ligne["messages_number"]}</td>
		<td style='font-size:11px' align='center'>$size</td>
		</tr>
		";
		
	
	}
	if($ct==0){return;}
	$html=$html."</table>
	<script>refreshMailerLinkToolbar();</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function mailer_link_rebuild_queue(){
	$oud=base64_decode($_GET["ou"]);
	$sql="DELETE FROM emailing_campain_queues WHERE campain_linker_id={$_GET["ID"]} AND ou='$oud'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="UPDATE emailing_campain_linker SET queue_builder_pourc=0 WHERE ID={$_GET["ID"]}";
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?emailing-builder-linker={$_GET["ou"]}");
	
	
}

function mailer_link_send(){
	$sock=new sockets();
	$sql="UPDATE emailing_campain_linker SET `queue_builder_pourc`=0, `locked`=1 WHERE ID={$_GET["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock->getFrameWork("cmd.php?emailing-builder-linker-simple=yes&ou={$_GET["ou"]}&ID={$_GET["ID"]}");	
	
}


function emailrelay_list(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?emailrelay-ou-status=yes&ou={$_GET["ou"]}")));
	if(is_array($ini->_params)){
		while (list ($key, $line) = each ($ini->_params)){$html=$html.DAEMON_STATUS_ROUND($key,$ini);}
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
	
?>