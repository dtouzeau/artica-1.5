<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.lvm.org.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
	if(!Isright()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-add-attach"])){popup_add_attach();exit;}
	if(isset($_GET["AddSMTPAttachment"])){AddSMTPAttachment();exit;}
	if(isset($_GET["attachmentslist"])){attachmentslist();exit;}
	if(isset($_GET["IncludeByNameDelete"])){IncludeByNameDelete();exit;}
	if(isset($_GET["AddPostFixDefaultRules"])){AddPostFixDefaultRules();exit;}
	if(isset($_GET["DeleteAllAttachments"])){DeleteAllAttachments();exit;}
	if(isset($_GET["enable_attachment_blocking_postfix"])){enable_attachment_blocking_postfix_save();exit;}
	js();
	
	function js(){
	$ou=base64_decode($_GET["ou"]);
	$hostname=$_GET["hostname"];
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{attachment_blocking}');
	$add_attachment_blocking_text=$tpl->_ENGINE_parse_body("{add_attachment_blocking_text}");
	$delete_all=$tpl->_ENGINE_parse_body("{delete_all}");
	$html="
		var mem_dev='';
		function attachment_blocking_load(){
			mem_dev='';
			YahooWin('650','$page?popup=yes&ou=$ou&hostname=$hostname','$ou::$title')
		}
		
		function AddAttachmentForm(){
			YahooWin2('450','$page?popup-add-attach=yes&ou=$ou&hostname=$hostname','$ou::$title')
		}
		
		var x_AddSMTPAttachment= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2Hide();	
			RefreshAttachementsList();
		}	
	
		function AddSMTPAttachment(){
			var XHR = new XHRConnection();
				XHR.appendData('ou','$ou');
				XHR.appendData('hostname','$hostname');
				XHR.appendData('AddSMTPAttachment',document.getElementById('AddSMTPAttachment').value);
				document.getElementById('popup-add-attach-div').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_AddSMTPAttachment);
		
		}
		
		function AddSMTPAttachmentEnter(e){
			if(checkEnter(e)){AddSMTPAttachment();}
		}		
		
		function IncludeByNameDelete(ID){
				var XHR = new XHRConnection();
				XHR.appendData('ou','$ou');
				XHR.appendData('hostname','$hostname');
				XHR.appendData('IncludeByNameDelete',ID);
				XHR.sendAndLoad('$page', 'GET',x_AddSMTPAttachment);
		}
		
		function RefreshAttachementsList(){
			LoadAjax('attachmentslist','$page?attachmentslist=yes&ou=$ou&hostname=$hostname');
		}
		
		function AddPostFixDefaultRules(){
				var XHR = new XHRConnection();
				XHR.appendData('ou','_Global');
				XHR.appendData('hostname','$hostname');
				XHR.appendData('AddPostFixDefaultRules','yes');
				document.getElementById('attachmentslist').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_AddSMTPAttachment);
		}
		
		function DeleteAllAttachments(){
			if(confirm('$delete_all ?')){
				var XHR = new XHRConnection();
				XHR.appendData('ou','$ou');
				XHR.appendData('hostname','$hostname');
				XHR.appendData('DeleteAllAttachments','yes');
				document.getElementById('attachmentslist').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_AddSMTPAttachment);
			}
		}		
		
		
		
	attachment_blocking_load();";	
	
	echo $html;
}	

function enable_attachment_blocking_postfix_save(){
	$main=new maincf_multi($_GET["hostname"]);
	$main->SET_VALUE("enable_attachment_blocking_postfix",$_GET["enable_attachment_blocking_postfix"]);	
	$sock=new sockets();
	
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		$sock->getFrameWork("cmd.php?postfix-multi-mime-header-checks={$_GET["hostname"]}");
		return;
	}
		
	
	$users=new usersMenus();
	if($users->KAV_MILTER_INSTALLED){$sock->getFrameWork("cmd.php?kavmilter-configure=yes");}
	$sock->getFrameWork("cmd.php?postfix-mime-header-checks=yes");
		
}
	
function popup(){
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$ou_js=$ou;
	$hostname=$_GET["hostname"];
	if($ou=="_Global"){
		$global_pattern=button("{add_default_rules}","AddPostFixDefaultRules()");
	}
	if($hostname==null){$hostname="master";$ou_js="master";}
	$main=new maincf_multi($hostname);
	$enable_attachment_blocking_postfix=$main->GET("enable_attachment_blocking_postfix");
	if(!is_numeric($enable_attachment_blocking_postfix)){$enable_attachment_blocking_postfix=0;}
	
	
	
	
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td valign='top' class=legend width=1% nowrap style='font-size:16px'>{enable}:</td>
		<td valign='top' align='left'>". Field_checkbox("enable_attachment_blocking_postfix",1,$enable_attachment_blocking_postfix,"enable_attachment_blocking_postfix_save()")."</td>
	</tr>
	</table>	
	
	<table style='width:100%'>
	<tr>
		<td width=90% valign='top'>
			<p style='font-size:16px;padding:3px'>{attachment_blocking_text}</p>
		</td>
		<td valign='top'>". imgtootltip("plus-24.png","{add_attachment_blocking_text}","AddAttachmentForm()")."</td>
		<td valign='top'>". imgtootltip("delete-24.png","{delete_all}","DeleteAllAttachments()")."</td>
	</tr>
	</table>
		
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/bg_forbiden-attachmt.jpg'></td>
		<td valign='top'><div id='attachmentslist' style='width:100%;height:260px;overflow:auto'></div></td>
	</tr>
	<tr>
		<td colspan=2 align='right'>$global_pattern</td>
	</tr>
	</table>
	</div>
	<script>
		RefreshAttachementsList();
		
	
		var x_enable_attachment_blocking_postfix_save= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
		}	
	
		function enable_attachment_blocking_postfix_save(){
			var XHR = new XHRConnection();
				XHR.appendData('ou','$ou_js');
				XHR.appendData('hostname','$hostname');
				if(document.getElementById('enable_attachment_blocking_postfix')){
					XHR.appendData('enable_attachment_blocking_postfix',1);}else{
					XHR.appendData('enable_attachment_blocking_postfix',0);}
				XHR.sendAndLoad('$page', 'GET',x_enable_attachment_blocking_postfix_save);
		
		}		
		
		
	</script>
		
	
	
	";	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}

function popup_add_attach(){
	
	$html="
	<div id='popup-add-attach-div'>
	<p class=caption>{add_attachment_blocking_text_explain}</p>
	<p>". Field_text("AddSMTPAttachment",null,"width:100%;font-size:14px",null,null,null,false,"AddSMTPAttachmentEnter(event)")."</p>
	<div style='text-align:right'><hr>". button("{add}","AddSMTPAttachment()")."
	</div></div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
function AddSMTPAttachment(){
	$ou=$_GET["ou"];
	$tbl=explode(",",$_GET["AddSMTPAttachment"]);
	$q=new mysql();
	
		while (list ($index, $file) = each ($tbl) ){
			$file=trim(strtolower($file));
			$file=str_replace(".","",$file);
			$sql="INSERT INTO smtp_attachments_blocking (IncludeByName,ou,hostname) VALUES ('$file','$ou','{$_GET["hostname"]}')";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				echo $q->mysql_error;
				return;
			}
		}
		
	$sock=new sockets();
	
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		if($_GET["hostname"]<>null){
			$sock->getFrameWork("cmd.php?postfix-multi-mime-header-checks={$_GET["hostname"]}");
		}
		return;
	}
	
	$users=new usersMenus();
	if($ou<>"_Global"){
		if($users->KAV_MILTER_INSTALLED){$sock->getFrameWork("cmd.php?kavmilter-configure=yes");}
	}else{
		$sock->getFrameWork("cmd.php?postfix-mime-header-checks=yes");
	}
}

function DeleteAllAttachments(){
	$ou=$_GET["ou"];
	if($_GET["hostname"]<>null){
		$hostname=" AND hostname='{$_GET["hostname"]}'";
	}
	$sql="DELETE FROM smtp_attachments_blocking WHERE ou='$ou'$hostname";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sock=new sockets();
	
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		if($_GET["hostname"]<>null){
			$sock->getFrameWork("cmd.php?postfix-multi-mime-header-checks={$_GET["hostname"]}");
		}
		return;
	}	
	
	
	$users=new usersMenus();
	if($ou<>"_Global"){
		if($users->KAV_MILTER_INSTALLED){$sock->getFrameWork("cmd.php?kavmilter-configure=yes");}
	}else{
		$sock->getFrameWork("cmd.php?postfix-mime-header-checks=yes");
	}	
	
}


function AddPostFixDefaultRules(){
	$stringdefault="386|acf|ade|ag|ani|app|asd|avi|aw|bat|bin|btm|cab|cbl|cgi|chm|cil|class|cmd|com|cpel|crt|css|cur|cvp|divx|dll|dot|drv|exe|fon|fxp|hhp|hlp|hta|inf|ini|isp|it|jar|jse|keyreg|ksh|lib|lnk|m3u|mat|mda|mhtm|mif|mpg|mpeg|msc|nte|nws|obj|ocx|ops|ov.pcd|pgm|pif|pls|pot|prg|qtif|ra|reg|rm|s3m|sam|scr|shs|slb|smm|src|swf|sys|url|vbs|vir|vmx|vxd|wax|wma|wpd|wsc|wvx|xms";
	$tbl=explode("|",$stringdefault);
	$q=new mysql();
		while (list ($index, $file) = each ($tbl) ){
			$file=trim(strtolower($file));
			$file=str_replace(".","",$file);
			$sql="INSERT INTO smtp_attachments_blocking (IncludeByName,ou) VALUES ('$file','_Global')";
			$q->QUERY_SQL($sql,"artica_backup");
		}	
		
	$sock=new sockets();
	
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		if($_GET["hostname"]<>null){
			$sock->getFrameWork("cmd.php?postfix-multi-mime-header-checks={$_GET["hostname"]}");
		}
		return;
	}	
	
	$sock->getFrameWork("cmd.php?postfix-mime-header-checks=yes");	
	
}



function attachmentslist(){
	$sock=new sockets();
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		$hostname=" AND hostname='{$_GET["hostname"]}'";
	}
	GlobalAttachments();
	$sql="SELECT * FROM smtp_attachments_blocking WHERE ou='{$_GET["ou"]}'$hostname ORDER BY IncludeByName";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$style=CellRollOver();
	$html="
	<table style='width:99%'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IncludeByName"]==null){continue;}
		$file=$ligne["IncludeByName"];
			if($_SESSION["FILES_TYPE"][$file]==null){	
			if(is_file("img/ext/{$file}_small.gif")){
				$_SESSION["FILES_TYPE"][$file]="img/ext/{$file}_small.gif";}else{
			}
		}	

		if($_SESSION["FILES_TYPE"][$file]==null){$_SESSION["FILES_TYPE"][$file]="img/ext/ico_small.gif";}
		
		$html=$html."
		<tr $style>
			<td width=1%><img src='{$_SESSION["FILES_TYPE"][$file]}'></t>
			<td><strong style='font-size:13px'>{$ligne["IncludeByName"]}</td>
			<td>". imgtootltip("ed_delete.gif","{delete}","IncludeByNameDelete({$ligne["ID"]})")."</td>
		</tR>
		";
	
	}
	
	if($_GET["ou"]<>"_Global"){
	if(is_array($GLOBALS["DEFAULT"])){
		while (list ($num, $ligne) = each ($GLOBALS["DEFAULT"]) ){
		if(is_file("img/ext/{$num}_small.gif")){$GLOBALS[$num]="img/ext/{$num}_small.gif";}else{$GLOBALS[$num]="img/ext/ico_small.gif";}
					
			
		$html=$html."
		<tr $style>
			<td width=1%><img src='{$GLOBALS[$num]}'></t>
			<td><strong style='font-size:13px;color:#B6B6AF'>". texttooltip($num,"{globally_banned}")."</td>
			<td>&nbsp;</td>
		</tR>
		";
		}
	}}
	
	
	
	
		$html=$html."</table>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);		
	
}

function IncludeByNameDelete(){
	$sql="DELETE FROM smtp_attachments_blocking  WHERE ou='{$_GET["ou"]}' AND ID='{$_GET["IncludeByNameDelete"]}'";
	$q=new mysql();
	$ou=$_GET["ou"];
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$users=new usersMenus();
	writelogs("OU->$ou",__FUNCTION__,__FILE__,__LINE__);
	
	
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
		if($_GET["hostname"]<>null){
			$sock->getFrameWork("cmd.php?postfix-multi-mime-header-checks={$_GET["hostname"]}");
		}
		return;
	}	
	
	if($ou<>"_Global"){
		if($users->KAV_MILTER_INSTALLED){
			$sock->getFrameWork("cmd.php?kavmilter-configure=yes");
		}
		
		
		
	}else{
		writelogs("->cmd.php?postfix-mime-header-checks=yes",__FUNCTION__,__FILE__,__LINE__);
		$sock->getFrameWork("cmd.php?postfix-mime-header-checks=yes");
	}
	
}




function GlobalAttachments(){
	$sock=new sockets();
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return ;}
	
$sql=new mysql();
	$sql="SELECT * FROM smtp_attachments_blocking WHERE ou='_Global' ORDER BY IncludeByName";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){return null;}
		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IncludeByName"]==null){continue;}
		$GLOBALS["DEFAULT"][$ligne["IncludeByName"]]=true;
		
	}	
	
}



function Isright(){
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsPostfixAdministrator){return true;}
	if(!$users->AsOrgStorageAdministrator){return false;}
	if(isset($_GET["ou"])){if($_SESSION["ou"]<>$_GET["ou"]){return false;}}
	
	return true;
	
	}
?>