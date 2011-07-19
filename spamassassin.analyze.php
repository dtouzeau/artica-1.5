<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.spamassassin.inc');
	include_once('ressources/class.mime.parser.inc');
	include_once(dirname(__FILE__).'/ressources/class.rfc822.addresses.inc');
	$user=new usersMenus();
		if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["messages"])){messages();exit;}
	if(isset($_GET["add"])){messages_add();exit;}
	if(isset($_POST["upload-message"])){message_upload();exit;}
	if(isset($_GET["messages-list"])){messages_list();exit;}
	if(isset($_GET["show-results"])){message_results();exit;}
	if(isset($_GET["analyze-message"])){message_analyze();exit;}
	if(isset($_GET["delete-message"])){message_delete();exit;}
	
	
js();


function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{APP_SPAMASSASSIN}::{message_analyze}");		
	echo "YahooWin3('700','$page?tabs=yes','$title');";	
	
}

function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["messages"]='{messages_list}';
	$array["add"]='{add}';
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_spamass_analyzemess style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_spamass_analyzemess\").tabs();});
		</script>";		
	
}

function messages(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<div id='tests-messages-list' style='width:100%;height:450px;overflow:auto'></div>
	
	<script>
	function LoadMessagesTests(){LoadAjax('tests-messages-list','$page?messages-list=yes');}
	
var X_spamass_message_upload= function (obj) {
		var results=obj.responseText;
		LoadMessagesTests();
		
	}		
	
	function spamass_ana_msg(ID){
		var XHR = new XHRConnection();
		XHR.appendData('analyze-message',ID);
		document.getElementById('tests-messages-list').innerHTML='analyze....';
		XHR.sendAndLoad('$page', 'GET',X_spamass_message_upload);
		}	
		
	function DeleteSpamTest(ID){
		var XHR = new XHRConnection();
		XHR.appendData('delete-message',ID);
		document.getElementById('tests-messages-list').innerHTML='analyze....';
		XHR.sendAndLoad('$page', 'GET',X_spamass_message_upload);	
	}
	
	LoadMessagesTests();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function messages_add(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<div style='font-size:16px;color:red' id='post-message-results'></div>
	<div class=explain>{spamass_analyze_post_explain}</div>
	<hr>
	<table style='width:100%' class=form>
	<tr>
		<td valign='top' class=legend>{from}:</td>
		<td>". Field_text("amavid-sender",null,"font-size:14px;padding:3px;width:250px")."</td>
	</tr>
		<td valign='top' class=legend>{recipients}:</td>
		<td><textarea id='amavid-recipients' style='width:100%;height:60px;overflow:auto;font-size:14px'></textarea></td>
	</tr>
	<td colspan=2>
	
		<textarea id='spamass_message' style='width:100%;height:350px;overflow:auto;font-size:7px'></textarea>
	</td>
	</tr>
	</table>
	<hr>
	<center>". button("{submit}","spamass_message_upload()")."</center>
	
	<script>
var X_spamass_message_upload= function (obj) {
		var results=obj.responseText;
		document.getElementById('post-message-results').innerHTML=results;
		
	}		
function spamass_message_upload(){
		var XHR = new XHRConnection();
		XHR.appendData('upload-message','yes');
		XHR.appendData('message',document.getElementById('spamass_message').value);
		XHR.appendData('sender',document.getElementById('amavid-sender').value);
		XHR.appendData('recipients',document.getElementById('amavid-recipients').value);
		document.getElementById('post-message-results').innerHTML='analyze....';
		XHR.sendAndLoad('$page', 'POST',X_spamass_message_upload);
		}
		
</script>";
	
echo $tpl->_ENGINE_parse_body($html);	
	
}

function message_upload(){
	
			$mime=new mime_parser_class();
			$mime->decode_bodies = 0;
			$mime->ignore_syntax_errors = 1;	
			$parameters['Data']=$_POST["message"];
			$parameters['SkipBody']=1;
			$decoded=array();
			$mime->Decode($parameters, $decoded);
			$subject=addslashes($decoded[0]["Headers"]["subject:"]);	
	
	
	$_POST["message"]=addslashes($_POST["message"]);
	$q=new mysql();
	$date=date('Y-m-d H:i:s');
	$sql="
	INSERT INTO `amavisd_tests` (`sender`,`recipients`,`message`,`saved_date`,`subject`) 
	VALUES ('{$_POST["sender"]}','{$_POST["recipients"]}','{$_POST["message"]}','$date','$subject')";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$size=FormatBytes((strlen($_POST["message"])/1024));
	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success} {message_size}:$size<br>$subject");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?spamass-test=yes");
	
	
}

function messages_list(){
	$page=CurrentPageName();
	$tpl=new templates();	

	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{date}</th>
		<th>{from}</th>
		<th>{recipients}</th>
		<th>{status}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	
	
	$sql="SELECT * FROM amavisd_tests ORDER BY ID DESC";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
			
			if($ligne["subject"]==null){$ligne["subject"]="{subject}:{unknown}";}
			$recp=explode(",",$ligne["recipients"]);
			$rcpt_text=@implode($recp,"<br>");
			if($ligne["finish"]==0){$status="{scheduled}";}
			if($ligne["finish"]==1){
				$status="{analyzed}";
				$ahrf="<a href=\"javascript:blur();\" OnClick=\"javascript:SpamassShowMsgStatus({$ligne["ID"]})\"
				style='font-size:11px;font-weight:bold;text-decoration:underline'>";
			
			}
			$delete=imgtootltip("delete-32.png","{delete}","DeleteSpamTest({$ligne["ID"]})");
			$analyze=imgtootltip("refresh-32.png","{analyze}","spamass_ana_msg({$ligne["ID"]})");
			
		$html=$html . "
		<tr  class=$classtr>
		<td width=1% style='font-size:11px' nowrap>$ahrf{$ligne["saved_date"]}</a></td>
		<td width=50%><strong style='font-size:11px'>$ahrf{$ligne["sender"]}</a></td>
		<td width=50%><strong style='font-size:11px'>$ahrf$rcpt_text</a></strong></td>
		<td width=50%><strong style='font-size:11px'>$ahrf$status</a></strong></td>
		<td width=1%>$analyze</td>
		<td width=1%>$delete</td>
		</tr>
		<tr class=$classtr>
			<td colspan=6><i style='font-size:11px'>{$ligne["subject"]}</i></td>
		</tr>";

	}
	
	$html=$html."</table>
	
	<div style='text-align:right;width:100%;margin-top:10px'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('main_config_spamass_analyzemess')")."</div>
	<script>
	function SpamassShowMsgStatus(ID){
		YahooWin2(650,'$page?show-results='+ID,'ID::'+ID);
	}
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function message_results(){
	$ID=$_GET["show-results"];
	if(!is_numeric($ID)){return null;}
	$sql="SELECT subject,amavisd_results FROM amavisd_tests WHERE ID=$ID";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$spamassassin_results=base64_decode($ligne["amavisd_results"]);
	$spamassassin_results=utf8_decode($spamassassin_results);
	$bytes=strlen($spamassassin_results);
	$tbl=explode("\n",$spamassassin_results);
			if(is_array($tbl)){
				while (list ($index, $line) = each ($tbl) ){
					$line=htmlentities($line);
					$line=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$line);
					$line=str_replace(" ","&nbsp;",$line);
					$content=$content."<div style='margin-top:5px'><code style='font-size:12px'>$line</code></div>\n";

					
				}
			
				
			
		}
	$html="
	<div style='font-size:16px'>{$ligne["subject"]} ($bytes bytes)</div>
	<hr>
	$content
	";
	
	echo $html;
	
}

function message_analyze(){
	$sql="UPDATE amavisd_tests SET finish=0 WHERE ID={$_GET["analyze-message"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?spamass-test={$_GET["analyze-message"]}");	
	
}

function message_delete(){
	$sql="DELETE FROM amavisd_tests WHERE ID={$_GET["delete-message"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
}



