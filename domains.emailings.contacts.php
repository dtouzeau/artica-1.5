<?php
	include_once('ressources/class.emailings.inc');
	
	
	if(isset($_GET["popup-add-contact"])){popup_add_contact();exit;}
	if(isset($_GET["add-contact"])){popup_add_contact_save();exit;}
	if(isset($_GET["del-contact"])){popup_del_contact();exit;}
	js();
	
	

	
function js(){

	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{add_contact}");
	
	if($_GET["ID"]<>null){$title=$tpl->_ENGINE_parse_body("{edit_contact}::{$_GET["ID"]}::{$_GET["dbname"]}");}
	
	$start="emailings_contact_add_start();";
	$ou=$_GET["ou"];
	
	if($_GET["delete"]<>null){$start="emailings_contact_del_start()";}
	
	$html="
		
	function emailings_contact_add_start(){
		YahooWin5('550','$page?popup-add-contact=yes&ou={$_GET["ou"]}&dbname={$_GET["dbname"]}&ID={$_GET["ID"]}','$title');
	
	}
	
	var x_emailings_contact_del_start= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			if(document.getElementById('emailing_table_content_bases')){
				var ss=document.getElementById('emailing-search').value;
				eMailingSearch(ss);
			}
			
		}		
	
	
	function emailings_contact_del_start(){
			var XHR = new XHRConnection();
	      	XHR.appendData('del-contact','yes');
	      	XHR.appendData('ou','{$_GET["ou"]}');
	      	XHR.appendData('db','{$_GET["dbname"]}');
	      	XHR.appendData('ID','{$_GET["ID"]}');	
	      	if(document.getElementById('emailing_table_content_bases')){
	      		document.getElementById('emailing_table_content_bases').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			}
		  	XHR.sendAndLoad('$page', 'GET',x_emailings_contact_del_start);		      	
	}
	
	$start";
	echo $html;
}

function popup_del_contact(){
	
	$sql="DELETE FROM emailing_{$_GET["db"]} WHERE ID={$_GET["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function popup_add_contact(){
	$page=CurrentPageName();
	$ou_decrypted=base64_decode($_GET["ou"]);
	$ID=$_GET["ID"];
	$q=new mysql();
	$sql="SELECT * FROM emailing_db_paths WHERE ou='$ou_decrypted' and merged=0 ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$databasename=$ligne["databasename"];
			$hash[$databasename]=$databasename;
			
		}
	
	
	$dbs=Field_array_Hash($hash,"database",null,null,null,0,"font-size:13px;padding:3px");	
	$logo="contact-card-add-128.png";
	$button_title="{add}";
	$resfresh_after="RefreshTab('emailing_campaigns');";
	
	if($_GET["dbname"]<>null){
		$dbs=Field_hidden("database",$_GET["dbname"])."<span style='font-size:13px'>{$_GET["dbname"]}</span>";
		$js_add="ContactHideDBADD()";
		
		if($ID<>null){
			$logo="contact-card-show-128.png";
			$button_title="{edit}";
			$resfresh_after=null;
			$sql="SELECT * FROM emailing_{$_GET["dbname"]} WHERE ID=$ID";
			$q=new mysql();
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));				
		}
		
	}
	
		
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
		<img src='img/$logo'>
		</td>
		<td valign='top'>
			<div id='emailing_add_contact_div'>
			". Field_hidden("ID",$_GET["ID"])."
			<table style='width:100%'>
			<tr>
					<td class=legend style='font-size:13px'>{gender}:</td>
					<td>". Field_text("gender",$ligne["gender"],"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>			
				<tr>
					<td class=legend style='font-size:13px'>{firstname}:</td>
					<td>". Field_text("firstname",$ligne["firstname"],"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{lastname}:</td>
					<td>". Field_text("lastname",$ligne["lastname"],"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{email}:</td>
					<td>". Field_text("email",$ligne["email"],"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{phone}:</td>
					<td>". Field_text("phone",$ligne["phone"],"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{city}:</td>
					<td>". Field_text("city",$ligne["city"],"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{CP}:</td>
					<td>". Field_text("cp",$ligne["cp"],"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{PostalAddress}:</td>
					<td><textarea name='postaladdress' id='postaladdress' style='font-size:13px;width:100%;height:80px'>{$ligne["postaladdress"]}</textarea></td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{database}:</td>
					<td>$dbs</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{or_add_db}:</td>
					<td>". Field_text("db_add",null,"font-size:13px;padding:3px",null,null,null,false,"AddContactPressKey(event)")."</td>
				</tr>								
				<tr><td colspan=2 align='right'><hr>". button("$button_title","eMailingAddContact()")."</td></tr>
				</table>
				</div>																										
		</td>
	</tr>
	</table>
	<script>
		
	var x_eMailingAddContact= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin5Hide();
			$resfresh_after
			
		}		
	
		function eMailingAddContact(){
			 	var XHR = new XHRConnection();
	      		XHR.appendData('add-contact','yes');
	      		XHR.appendData('ou','{$_GET["ou"]}');
	      		XHR.appendData('gender',document.getElementById('gender').value);
	      		XHR.appendData('firstname',document.getElementById('firstname').value);
	      		XHR.appendData('lastname',document.getElementById('lastname').value);
	      		XHR.appendData('email',document.getElementById('email').value);
	      		XHR.appendData('phone',document.getElementById('phone').value);
	      		XHR.appendData('city',document.getElementById('city').value);
	      		XHR.appendData('cp',document.getElementById('cp').value);
	      		XHR.appendData('postaladdress',document.getElementById('postaladdress').value);
	      		XHR.appendData('database',document.getElementById('database').value);
	      		XHR.appendData('db_add',document.getElementById('db_add').value);
	      		XHR.appendData('ID',document.getElementById('ID').value);
	      		document.getElementById('emailing_add_contact_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		  		XHR.sendAndLoad('$page', 'GET',x_eMailingAddContact);	
				
		}
		
		function ContactHideDBADD(){
			document.getElementById('db_add').disabled=true;
		}
		
		
	function AddContactPressKey(e){
		if(checkEnter(e)){eMailingAddContact();}
	}
	
	$js_add
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
}

function popup_add_contact_save(){
	$db=$_GET["database"];
	$ID=$_GET["ID"];
	$_GET["ou"]=base64_decode($_GET["ou"]);
	if(trim($_GET["db_add"])<>null){$db=$_GET["db_add"];}
	$db=format_mysql_table($db);
	$email=$_GET["email"];
	if(preg_match("#.+?@(.+)#",$email,$re)){$domain=$re[1];}
	
	$sql_add="INSERT INTO emailing_{$db} (gender,firstname,lastname,email,domain,phone,city,cp,postaladdress)
	VALUES('{$_GET["gender"]}','{$_GET["firstname"]}','{$_GET["lastname"]}','{$_GET["email"]}','$domain',
	'{$_GET["phone"]}','{$_GET["city"]}','{$_GET["cp"]}','{$_GET["postaladdress"]}');
	";
	
	$sql_edit="UPDATE emailing_{$db} SET 
	gender='{$_GET["gender"]}',
	lastname='{$_GET["lastname"]}',
	email='{$_GET["email"]}',
	domain='{$domain}',
	phone='{$_GET["phone"]}',
	city='{$_GET["city"]}',
	cp='{$_GET["cp"]}',
	postaladdress='{$_GET["postaladdress"]}'
	WHERE ID=$ID
	";
	
	if($ID>0){$sql=$sql_edit;}else{$sql=$sql_add;}
	
	$q=new mysql();
	
	writelogs("Checking emailing_{$db}",__FUNCTION__,__FILE__,__LINE__);
	
	if(!$q->TABLE_EXISTS("emailing_{$db}","artica_backup")){
		writelogs("Create emailing_{$db}",__FUNCTION__,__FILE__,__LINE__);
		$q->CheckTableEmailingContacts("emailing_{$db}");
		$sql_table="INSERT INTO emailing_db_paths(databasename,ou,zDate,finish,progress)
		VALUES ('$db','{$_GET["ou"]}',NOW(),1,100);
		";
		
		$z=new mysql();
		$z->QUERY_SQL($sql_table,"artica_backup");
		writelogs("$z->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		if(!$z->ok){echo "\n$z->mysql_error\n$sql_table\n\n";}
	}
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "\n$q->mysql_error\n$sql\n\n";}
	
	
}



?>