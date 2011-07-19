<?php
	include_once('ressources/class.emailings.inc');


	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["upload-form"])){upload_form();exit;}
	if(isset($_GET["database"])){save();exit;}
	
js();
	
	

	
function js(){

	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{import_contacts_db}");
	$start="emailings_start_page();";
	$ou=$_GET["ou"];
	
	$html="
		
	function emailings_start_page(){
		YahooWin4('650','$page?popup=yes&ou=$ou','$title');
	
	}
	$start";
	echo $html;
}	
	
function popup(){
	$ou=$_GET["ou"];
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<div style='font-size:13px'>{import_contacts_db_explain}
	<hr>
	<code>\"{gender}\",\"{firstname}\",\"{lastname}\",\"{emailAddress}\",\"{phone}\",\"{city}\",\"{CP}\",\"{postalAddress}\"
	<br>\"{gender}\",\"{firstname}\",\"{lastname}\",\"{emailAddress}\",\"{phone}\",\"{city}\",\"{CP}\",\"{postalAddress}\"</code>
	<br><span style='color:red'>{use_compressed_zip_file}</span>
	<hr>	
	</div>
	
	<p>&nbsp;</p>
	<H3>{local_file}</H3>
	<div id='local_file'>
	<table style='width:100%'>
			<tr>
			<td class=legend style='font-size:13px' nowrap>{database_name}:</td>
			<td>". Field_text("database_name",null,"font-size:13px;padding:3px")."</td>
		</tr>
			<tr>
			<td class=legend style='font-size:13px'>{file_path}:</td>
			<td>". Field_text("local-file","","width:100%;font-size:13px;padding:3px"). " </td>
			<td width=1%><input type='button' value='{browse}&nbsp;&raquo;' OnClick=\"javascript:Loadjs('tree.php?select-file=zip&target-form=local-file');\"></td>
		</tr>
		<td colspan=3 align='right'><hr>
			". button("{save}","EmailingGetFileFromPath()")."
		
		</td>
		</tr>
		</table>		
	
	
	<script>
	var x_EmailingGetFileFromPath= function (obj) {
		var response=obj.responseText;
		if(response.length>0){alert(response);}
		YahooWin4Hide();
		if(document.getElementById('emailing_campaigns')){
			RefreshTab('emailing_campaigns');
		}
	
	}	
	
	function EmailingGetFileFromPath(){
			 var XHR = new XHRConnection();
	      	 XHR.appendData('database',document.getElementById('database_name').value);
	      	 XHR.appendData('filepath',document.getElementById('local-file').value);
	      	 XHR.appendData('ou','{$_GET["ou"]}');
	      	 document.getElementById('local_file').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	      	 XHR.sendAndLoad('$page', 'GET',x_EmailingGetFileFromPath);
		}
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$ou=base64_decode($_GET["ou"]);
	if($_GET["database"]==null){$_GET["database"]=basename($_GET["filepath"]);}
	$_GET["database"]=format_mysql_table($_GET["database"]);
	$sql="INSERT INTO emailing_db_paths (filepath,databasename,ou,zDate) VALUES('{$_GET["filepath"]}','{$_GET["database"]}','$ou',NOW());";
	$q=new mysql();
	$q->check_emailing_tables();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?emailing-import-contacts=yes");
	
}

	
	
	
	
	
	
	
	
	
	
	
?>