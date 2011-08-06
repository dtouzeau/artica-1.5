<?php
	session_start();
	if($_SESSION["uid"]==null){
		if(isset($_GET["imap-account-popup"])){echo "window.location.href ='logoff.php';";die();}
		if(count($_GET)>0){echo "<script>window.location.href ='logoff.php';</script>";die();}
		echo "window.location.href ='logoff.php';";die();
	}	
	include_once('ressources/class.emailings.inc');
	if(isset($_GET["popup"])){popup();exit;}
	
	
	
	if(isset($_GET["imap"])){imap();exit;}
	if(isset($_GET["imap-account-delete"])){imap_account_delete();exit;}
	if(isset($_GET["imap-account-save"])){imap_account_save();exit;}
	if(isset($_GET["imap-account-popup"])){imap_account_popup();exit;}
	if(isset($_GET["imap-account"])){imap_account_js();exit;}
	
	if(isset($_GET["database"])){database_popup();exit;}
	if(isset($_GET["database-search"])){database_search();exit;}
	if(isset($_GET["database-blacklist-delete"])){database_delete();exit;}
	if(isset($_GET["database-blacklist-add"])){database_add();exit;}
	
	if(isset($_GET["events"])){events_popup();exit;}
	if(isset($_GET["events-search"])){events_list();exit;}
	js();
	
	

	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{bad_mails}");
	$start="emailings_badmail_start_page();";
	$ou=$_GET["ou"];
	
	$html="
	function emailings_badmail_start_page(){
		YahooWin3('650','$page?popup=yes&ou={$_GET["ou"]}','$title');
	
	}
	$start";
	echo $html;
}

function popup(){
	
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$array["imap"]="{imap_accounts}";
	$array["database"]="{blacklist_database}";
	$array["events"]="{events}";
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		$a[]="<li><a href=\"$page?$num=yes&ou=$ou\"><span>". $tpl->_ENGINE_parse_body("$ligne")."</span></a></li>\n";
	}	
	
	
	$html="
	<div id='emailings_badmail' style='background-color:white;width:100%;height:600px;overflow:auto'>
	<ul>
		".implode("\n",$a)."
	</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#emailings_badmail').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>
	
	";
	
	echo $html;
}

function imap_account_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{imap_account}::".base64_decode($_GET["imap-account"]));
	$start="emailings_imapaccount_start_page();";
	$ou=$_GET["ou"];
	
	$html="
	function emailings_imapaccount_start_page(){
		YahooWin4('550','$page?imap-account-popup=yes&ou={$_GET["ou"]}&imap-account={$_GET["imap-account"]}','$title');
	
	}
	$start";
	echo $html;	
}


function events_popup(){
	$page=CurrentPageName();	
	$tpl=new templates();
	$ou=$_GET["ou"];	
	$html="
	<div id='events_emailing_search' style='width:100%;height:500px;overflow:auto'></div>
	<script>
		function RefreshEventsList(){
			LoadAjax('events_emailing_search','$page?events-search=yes&ou=$ou')
		}
		RefreshEventsList();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function events_list(){
	$page=CurrentPageName();	
	$tpl=new templates();
	$ou=$_GET["ou"];		
	$sql="SELECT * FROM emailing_campain_imap_events ORDER BY zDate DESC LIMIT 0,100";
	$q=new mysql();
	$count_rows=$q->COUNT_ROWS("emailing_campain_imap_events","artica_backup");
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo ("<H2>$q->mysql_error</H2>");}
	
	$html="
		<div style='float:right;margin:8px'>". imgtootltip("refresh-32.png","{refresh}","RefreshEventsList()")."</div><H2>$count_rows {entries}</H2>
	<hr>
	
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th colspan=3>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
	$html=$html."
	<tr class=$classtr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:11px'>{$ligne["zDate"]}</td>
		<td><strong style='font-size:11px'>{$ligne["subject"]}</td>
	</tr>";
		
	}	
	
	$html=$html."
	</tbody>
	</table>";
	echo $tpl->_ENGINE_parse_body($html);
}


function database_popup(){
	$page=CurrentPageName();	
	$tpl=new templates();
	$ou=$_GET["ou"];	
		

	
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{search}:</td>
		<td>". Field_text("blk_search","*","font-size:13px;padding:3px",null,null,null,false,"BlackListSearchKey(event)")."</td>
	</tr>
	</table>
	

	
	<div id='blacklist_emailing_search' style='width:100%;height:500px;overflow:auto'></div>
	
	<script>
	
		function SearchBLK(){
			var s=escape(document.getElementById('blk_search').value);
			LoadAjax('blacklist_emailing_search','$page?database-search='+s+'&ou=$ou');
		}
		
		function BlackListSearchKey(e){
			if(checkEnter(e)){SearchBLK();}
		}
		
		
	SearchBLK();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function database_delete(){
	$email=base64_decode($_GET["database-blacklist-delete"]);
	$email=addslashes($email);
	$sql="DELETE FROM emailing_campain_blacklist WHERE email='$email'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo ("$q->mysql_error\n$sql");}
	
}

function database_add(){
	$_GET["database-blacklist-add"]=trim(strtolower($_GET["database-blacklist-add"]));
	if($_GET["database-blacklist-add"]==null){return;}
	$sql="INSERT INTO emailing_campain_blacklist (email) VALUES('{$_GET["database-blacklist-add"]}')";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo ("$q->mysql_error\n$sql");}	
}

function database_search(){
$page=CurrentPageName();	
	$tpl=new templates();	
	$pattern=$_GET["database-search"];
	$pattern=str_replace("*","%",$pattern);
	
	$sql="SELECT email FROM emailing_campain_blacklist WHERE email LIKE '$pattern' ORDER BY email LIMIT 0,100";
	$q=new mysql();
	$count_rows=$q->COUNT_ROWS("emailing_campain_blacklist","artica_backup");
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo ("<H2>$q->mysql_error</H2>");}
	
	$html="
		<div style='float:right;margin:8px'>". imgtootltip("email-add-32.png","{add}","AddEmailIngBlackList()")."</div><H2>$count_rows {entries}</H2>
	<hr>
	
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th width=1%>&nbsp;</th>
			<th>{email}</th>
			<th width=1%>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$b64=base64_encode($ligne["email"]);
	$html=$html."
	<tr class=$classtr>
		<td width=1%><img src='img/bad-email-24.png'></td>
		<td><strong style='font-size:14px'>{$ligne["email"]}</td>
		<td width=1%>". imgtootltip("delete-24.png","{delete}","BlackListEmailDelete('$b64')")."</td>
	</tr>";
		
	}	
	
	$html=$html."
	</tbody>
	</table>
	
	<script>
	
		var x_BlackListEmailDelete= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			SearchBLK();
		}	
	
		function BlackListEmailDelete(email){
			var XHR = new XHRConnection();
			XHR.appendData('database-blacklist-delete',email);
			XHR.appendData('ou','{$_GET["ou"]}');
			document.getElementById('blacklist_emailing_search').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_BlackListEmailDelete);				
							
		}
		
		function AddEmailIngBlackList(){
			var email=prompt('eMail:');
			if(email){
				var XHR = new XHRConnection();
				XHR.appendData('database-blacklist-add',escape(email));
				XHR.appendData('ou','{$_GET["ou"]}');
				document.getElementById('blacklist_emailing_search').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_BlackListEmailDelete);
			}
		}
		
	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function imap_account_popup(){
	$page=CurrentPageName();	
	$tpl=new templates();
	$ou=$_GET["ou"];	
	$q=new mysql();
	$account=base64_decode($_GET["imap-account"]);
	$sql="SELECT parameters FROM emailing_campain_imap WHERE account_name='$account'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$PARAMS=unserialize(base64_decode($ligne["parameters"]));
	if(trim($account)<>null){$delete=imgtootltip("delete-48.png","{delete}","DeleteImapAccount()");}
	
	$html="
	<div id='EmailingAccountID'>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>$delete</td>
	<td width=99%>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{accountname}:</td>
		<td>". Field_text("account_name",$PARAMS["account_name"],"font-size:13px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{enabled}:</td>
		<tD>". Field_checkbox("enabled",1,$PARAMS["enabled"],'imapaccountEnableDisableEmailing()')."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{servername}:</td>
		<td>". Field_text("servername",$PARAMS["servername"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{use_ssl}:</td>
		<tD>". Field_checkbox("use_ssl",1,$PARAMS["use_ssl"])."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{username}:</td>
		<td>". Field_text("username",$PARAMS["username"],"font-size:13px;padding:3px;width:220px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{password}:</td>
		<td>". field_password("password",$PARAMS["password"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>
	<td valing='top' colspan=2 align='right'><hr>". button("{save}","EmailingAccountImapSave()")."</td>
	</td>
	</table>
	</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	function imapaccountEnableDisableEmailing(){
		document.getElementById('servername').disabled=true;
		document.getElementById('use_ssl').disabled=true;
		document.getElementById('username').disabled=true;
		document.getElementById('password').disabled=true;
		if(document.getElementById('enabled').checked){
			document.getElementById('servername').disabled=false;
			document.getElementById('use_ssl').disabled=false;
			document.getElementById('username').disabled=false;
			document.getElementById('password').disabled=false;		
		}
	
	}
	
	
	var x_EmailingAccountImapSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			RefreshTab('emailings_badmail');
			YahooWin4Hide();
		}			
		
		function EmailingAccountImapSave(){
			var tempvalue=document.getElementById('account_name').value;
			if(tempvalue.length>3){
				var XHR = new XHRConnection();
				XHR.appendData('imap-account-save','yes');
				XHR.appendData('account_name',document.getElementById('account_name').value);
				if(document.getElementById('enabled').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
				if(document.getElementById('use_ssl').checked){XHR.appendData('use_ssl',1);}else{XHR.appendData('use_ssl',0);}
				XHR.appendData('servername',document.getElementById('servername').value);
				XHR.appendData('username',document.getElementById('username').value);
				XHR.appendData('password',document.getElementById('password').value);
				XHR.appendData('ou','{$_GET["ou"]}');
				XHR.appendData('imap-account','{$_GET["imap-account"]}');
				document.getElementById('EmailingAccountID').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_EmailingAccountImapSave);			
			}
		
		}
		
		function DeleteImapAccount(){
			var XHR = new XHRConnection();
			XHR.appendData('imap-account-delete','yes');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('imap-account','{$_GET["imap-account"]}');
			document.getElementById('EmailingAccountID').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_EmailingAccountImapSave);				
							
		}
		
		imapaccountEnableDisableEmailing();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
}

function imap_account_delete(){
	$imapaccount=base64_decode($_GET["imap-account"]);
	$ou=base64_decode($_GET["ou"]);
	$sql="DELETE FROM emailing_campain_imap WHERE account_name='$imapaccount' and ou='$ou'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	
	$sql="DELETE FROM emailing_campain_imap_events WHERE account='$imapaccount'";
	$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo $q->mysql_error;}	
	
}


function imap_account_save(){

	$params=base64_encode(serialize($_GET));
	$ou=base64_decode($_GET["ou"]);
	$enabled=$_GET["enabled"];
	$imapaccount=base64_decode($_GET["imap-account"]);

	if($imapaccount==null){
		$imapaccount=str_replace(" ","-",$_GET["account_name"]);
		$imapaccount=replace_accents($imapaccount);
		$sql="INSERT INTO emailing_campain_imap (account_name,ou,enabled,parameters) VALUES('$imapaccount','$ou','$enabled','$params')";
	}else{
		$sql="UPDATE emailing_campain_imap SET enabled='$enabled', parameters='$params' WHERE account_name='$imapaccount'";
	}
		
		
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
	
}


function imap(){
	$page=CurrentPageName();	
	$tpl=new templates();
	$ou=base64_decode($_GET["ou"]);
	if(!function_exists("imap_open")){echo "<H1>FATAL !! imap_open does not exists, please contact Artica support team !</H1>";die();}
	$tr[]=Paragraphe("email-add-64.png","{add_imap_account}","{add_imap_bad_emailing_account_text}","javascript:Loadjs('$page?imap-account=&ou={$_GET["ou"]}')");
	
	$sql="SELECT account_name,parameters FROM emailing_campain_imap WHERE ou='$ou'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo ("<H2>$q->mysql_error</H2>");}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$parms=unserialize(base64_decode($ligne["parameters"]));
		$text="{server}: {$parms["servername"]}<br>{username}: {$parms["username"]}<br>";
		$account=base64_encode($ligne["account_name"]);
		$tr[]=Paragraphe("email-settings-64.png",$parms["account_name"],$text,"javascript:Loadjs('$page?imap-account=$account&ou={$_GET["ou"]}')");
	}
	
	
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
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
