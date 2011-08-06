<?php
	include_once('ressources/class.emailings.inc');
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["databasename"])){save();exit;}
	
	js();
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{link_databases}");
	$start="link_databases_start_page();";
	$ou=$_GET["ou"];
	
	$html="
	function link_databases_start_page(){
		YahooWin3('550','$page?popup=yes&ou={$_GET["ou"]}','$title');
	
	}
	$start";
	echo $html;
}	



function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ou_decrypted=base64_decode($_GET["ou"]);
	
	$html="
	<div id='link_databases_emailing_div'>
	<div class=explain>{link_databases_emailing_text}<br>{merged_database_explain}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{database_name}:</td>
		<td>". Field_text("databasename",null,"font-size:13px;padding:3px;width:220px")."</td>
	</tr>
	</table>
	<table style='width:100%'>";
		
	$sql="SELECT * FROM emailing_db_paths WHERE ou='$ou_decrypted' and merged=0 ORDER BY databasename";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$ct=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ct++;
		$databasename=$ligne["databasename"];
		$field_name="{$databasename}_f";
		$js[]="if(document.getElementById('$field_name').checked){XHR.appendData('DB_ADD_$ct','$databasename');}";
		$html=$html.
		"<tr>
			<td class=legend>$databasename:</td>
			<td>". Field_checkbox("$field_name",1,0)."</td>
		</tr>";
		}
		
		$jjs=@implode("\n",$js);
		
	$html=$html."
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","CreatEmailingVirtualDB()")."</td>
	</tr>
	
	</table>
	</div>
	<script>
		var x_CreatEmailingVirtualDB= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin3Hide();
			RefreshTab('emailing_campaigns');	
		}	
	
	
		function CreatEmailingVirtualDB(){
			var XHR = new XHRConnection();
	      	XHR.appendData('databasename',document.getElementById('databasename').value);
	      	XHR.appendData('ou','{$_GET["ou"]}');
			$jjs
			document.getElementById('link_databases_emailing_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_CreatEmailingVirtualDB);
				
		}
	
	</script>";

			
	echo $tpl->_ENGINE_parse_body($html);
	
	
		
}


function save(){
	
	$databasename=$_GET["databasename"];
	$databasename=format_mysql_table($databasename);
	
	
	$ou=base64_decode($_GET["ou"]);
	$q=new mysql();
	$tpl=new templates();
	//CheckTableEmailingContacts

	while (list ($key, $line) = each ($_GET) ){
		if(preg_match("#DB_ADD_[0-9]+#",$key)){
			$tables[]="emailing_{$line}";
		}
		
	}
	
	if(count($tables)==0){echo $tpl->javascript_parse_text("{NODB_SELECTED}");return;}
	
	$q->CheckTableEmailingContacts("emailing_$databasename",$tables);
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	
	$sql="INSERT INTO emailing_db_paths (databasename,progress,finish,ou,zDate,merged)
	VALUES('$databasename','100','1','$ou',NOW(),'1');";
	$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}	
	
	
}












