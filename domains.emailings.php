<?php
	include_once('ressources/class.emailings.inc');
	
	if(isset($_GET["popup-js"])){popup_js();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-tabs"])){popup_tabs();exit;}
	if(isset($_GET["databases"])){main_databases();exit;}
	if(isset($_GET["emailing-templates"])){main_templates();exit;}
	if(isset($_GET["database-infos"])){database_infos();exit;}
	if(isset($_GET["delete-db"])){database_delete();exit;}
	if(isset($_GET["database-migrate"])){database_migrate_popup();exit;}
	if(isset($_GET["database-migrate-perform"])){database_migrate_perform();exit;}
	if(isset($_GET["emailing-search"])){emailing_search();exit;}
	if(isset($_GET["make-unique-table"])){database_make_unique();exit;}
	js();
	
	

	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{email_campaigns}");
	$start="emailings_start_page();";
	$ou=$_GET["ou"];
	
	$html="
	function emailings_start_page(){
		YahooWin3('650','$page?popup=yes&ou={$_GET["ou"]}','$title');
	
	}
	$start";
	echo $html;
}

function popup_js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?popup-tabs=yes&ou={$_GET["ou"]}');";
}


function popup(){
	$page=CurrentPageName();
	$html="
	<script>
		$('#BodyContent').load('$page?popup-tabs=yes&ou={$_GET["ou"]}');
	</script>
	";
	echo $html;
	
}


function popup_tabs(){
	
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$array["databases"]="{contacts_databases}";
	$array["emailing-templates"]="{templates}";
	$array["emailing-builder"]="{emailings}";
	
	$tpl=new templates();
	
		while (list ($num, $ligne) = each ($array) ){
		
		if($num=="emailing-builder"){
			$a[]="<li><a href=\"domains.emailings.builder.php?ou=$ou\"><span>". $tpl->_ENGINE_parse_body("$ligne")."</span></a></li>\n";
			continue;	
		}
		$a[]="<li><a href=\"$page?$num=yes&ou=$ou\"><span>". $tpl->_ENGINE_parse_body("$ligne")."</span></a></li>\n";
		
			
		}	
	
	
	$html="
	<div id='emailing_campaigns' style='background-color:white;width:100%;height:600px;overflow:auto'>
	<ul>
		".implode("\n",$a)."
	</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#emailing_campaigns').tabs({
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

function main_databases(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();

	$ou_decrypted=base64_decode($_GET["ou"]);
	
	$tr[]=Paragraphe("add-database-64.png","{import_contacts_db}","{import_contacts_db_text}","javascript:Loadjs('domains.emailings.import.php?ou={$_GET["ou"]}')");
	$tr[]=Paragraphe("contact-card-add-64.png","{add_contact}","{add_contact_text}","javascript:Loadjs('domains.emailings.contacts.php?ou={$_GET["ou"]}')");
	$tr[]=Paragraphe("bad-email-64.png","{bad_mails}","{bad_mails_emailing_text}","javascript:Loadjs('domains.emailings.badmails.php?ou={$_GET["ou"]}')");	
	
	
	$sql="SELECT COUNT(*) as tcount FROM emailing_db_paths WHERE ou='$ou_decrypted'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["tcount"]>1){
		$tr[]=Paragraphe("database-link-64.png","{link_databases}","{link_databases_emailing_text}","javascript:Loadjs('domains.emailings.dblink.php?ou={$_GET["ou"]}')");
	}
	
	
	
	$sql="SELECT * FROM emailing_db_paths WHERE ou='$ou_decrypted' ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$img="database-link-64.png";
			$progress_export_text=null;
			$databasename=$ligne["databasename"];
			$js="javascript:EmailingDatabaseContacts({$ligne["ID"]},'$databasename')";
			$progress=$ligne["progress"];
			$progress_export=$ligne["progress_export"];
			
			if($progress_export>0){
				if($progress_export<100){
					$progress_export_text="<img src=img/ajax-menus-loader.gif><br>{exporting}: $progress_export%<br></div>";
				}
			}
			
			
			if($progress<100){
				$text="{importing} $progress%";
				$img="tables-64-running.png";
				
			}else{
				
				if($progress>100){
					$img="tables-failed-64.png";
					$text=$ligne["reports_import"];
				}else{
					
						$q=new mysql();
						$infos_count=$q->COUNT_ROWS("emailing_{$ligne["databasename"]}","artica_backup");
						$img="tables-64.png";
						$text="$infos_count {contacts}";
					
				}
			}
			
			if($ligne["merged"]==1){
				$progress_export_text="<br>{merged_database}";
				$img="tables-lock-64.png";
			}
			
			
		$tr[]=Paragraphe($img,$databasename,"$text$progress_export_text",$js);	
			
			
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
	$search_text=$tpl->_ENGINE_parse_body("{search}");
	echo "
	
	<table style='width:100%'>
	<tr>
		<td class=legend>$search_text:</td>
		<td>". Field_text("emailing-search",null,"font-size:13px;padding:3px",null,null,null,false,"eMailingSearchPressKey(event)")."</td>
		<td><div style='text-align:right;width:100%'>". imgtootltip("32-refresh.png","{refresh}","RefreshTab('emailing_campaigns')")."</td>
	</tr>
	</table>
	<div id='emailing_table_content_bases'>
	$tbl
	</div>
	
	<script>
		function EmailingDatabaseContacts(ID,dbname){
			YahooWin4(600,'$page?ou={$_GET["ou"]}&database-infos='+ID,dbname);
		}
		
		function MigrateUsersToLdap(ID,dbname){
			YahooWin5(550,'$page?ou={$_GET["ou"]}&database-migrate='+ID,dbname);
		}	

		function eMailingSearchPressKey(e){
			if(!checkEnter(e)){return;}
			var ss=document.getElementById('emailing-search').value;
			eMailingSearch(ss);
		}
		
		function eMailingSearch(ss){
			if(ss.length>0){
				LoadAjax('emailing_table_content_bases','$page?ou={$_GET["ou"]}&emailing-search='+ss);
			}
		}
		
		
	</script>
	
	";
	
}

function database_infos(){
	$ID=$_GET["database-infos"];
	$ou=$_GET["ou"];
	$tpl=new templates();
	$sql="SELECT * FROM emailing_db_paths WHERE ID=$ID";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$databasename=$ligne["databasename"];
	$merged=$ligne["merged"];
	$make_unique_table_text=$tpl->javascript_parse_text("{make_unique_table_text}");
	$page=CurrentPageName();
	
	$tbl=@explode("\n",$ligne["reports_import"]);
	while (list ($index, $line) = each ($tbl)){
		$t[]="<div><code>$line</code></div>";
	}
	$q=new mysql();
	if(!$q->TABLE_EXISTS("emailing_{$databasename}","artica_backup")){$q->CheckTableEmailingContacts("emailing_{$databasename}");}
	
	$nb_contacts=$q->COUNT_ROWS("emailing_{$databasename}","artica_backup");
	
	$sql="SELECT domain FROM emailing_{$databasename} GROUP BY domain";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$nb_domains=mysql_num_rows($results);

	//table-synonyms-settings-96.png
	//table-synonyms-settings-32.png
	
	
	$are_you_sure_to_delete=$tpl->javascript_parse_text("{are_you_sure_to_delete}");
	$add_contact_js="Loadjs('domains.emailings.contacts.php?ou=$ou&dbname=$databasename')";
	
	$add_contact_icon="<td width=1% style='border-right:2px solid #CCCCCC;padding:4px'>". imgtootltip("contact-card-add-32.png","{add_contact_text}","$add_contact_js")."</td>";
	$synonym="<td width=1% style='border-right:2px solid #CCCCCC;padding:4px'>". imgtootltip("table-synonyms-settings-32.png","{make_unique_table_text}","MakeUniqueTable()")."</td>";
	$infos_plus=null;
	$explain_plus=null;
	
	
	
	if($merged==1){
		$add_contact_icon=null;
		$synonym=null;
		$infos_plus="&nbsp;{merged_database}";
		$explain_plus="<div class=explain>{merged_database_explain}</div>";
	}
	
	$html="
	<div id='db_$ID'>
	<table style='width:100%'>
	<tr>
	<td width=1% style='border-right:2px solid #CCCCCC;padding:4px'>". imgtootltip("delete-32.png","{delete}","DeleteDB();")."</td>
	<td width=1% style='border-right:2px solid #CCCCCC;padding:4px'>". imgtootltip("user-migrate-32.png","{migrate_user_tini_text}","MigrateUsersToLdap($ID,'$databasename');")."</td>
	$add_contact_icon
	$synonym
	
	
	
	<td nowrap><H4>$databasename (ref:$ID)</H4></td>
	</tr>
	</table>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/table-show-90.png'></td>
	<td valign='top' width=99%>
		<table style='width:100%'>
		<tr>
			<td valign='top' style='font-size:13px' class=legend>{contacts}:</td>
			<td valign='top' style='font-size:13px'><strong style='font-size:13px'>$nb_contacts</strong></td>
		</tr>
		<tr>
			<td valign='top' style='font-size:13px' class=legend>{nb_domains}:</td>
			<td valign='top' style='font-size:13px'><strong style='font-size:13px'>$nb_domains</strong></td>
		</tr>		
		<tr>
			<td colspan=2>
			<H3>{infos}$infos_plus</H3>$explain_plus
			<br>
			<div style='height:220px;overflow:auto;border-top:1px solid #005447'>".@implode("\n",$t)."</div></td>
		</tr>
	</table>
	</div>
	</tr>
	</table>
	</div>
	<script>
	
	var x_DeleteDB= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin4Hide();
			RefreshTab('emailing_campaigns');	
		}		
	
		function DeleteDB(ID){
			if(confirm('$are_you_sure_to_delete $databasename')){
			 	var XHR = new XHRConnection();
	      		XHR.appendData('delete-db','$ID');
	      		XHR.appendData('ou','{$_GET["ou"]}');
	      		if(document.getElementById('db_$ID')){
	      			document.getElementById('db_$ID').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
				}
		  		XHR.sendAndLoad('$page', 'GET',x_DeleteDB);	
				}
		}
		
		function MakeUniqueTable(){
			if(confirm('$make_unique_table_text')){
				var XHR = new XHRConnection();
	      		XHR.appendData('make-unique-table','yes');
	      		XHR.appendData('ou','{$_GET["ou"]}');
	      		XHR.appendData('source-table','$ID');
				XHR.sendAndLoad('$page', 'GET',x_DeleteDB);	
			}
		}
		
		
</script>
		
	
	";

	

echo $tpl->_ENGINE_parse_body($html);	
	
}

function database_delete(){
	$ID=$_GET["delete-db"];
	$sql="SELECT * FROM emailing_db_paths WHERE ID=$ID";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$databasename=$ligne["databasename"];
	if($databasename==null){return;}
	$sql="DROP TABLE `emailing_{$databasename}`";
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="DELETE FROM emailing_db_paths WHERE ID=$ID";
	$q->QUERY_SQL($sql,"artica_backup");
	
	
}

function main_templates(){
  $page=CurrentPageName();
  $tpl=new templates();
  $ou_decrypt=base64_decode($_GET["ou"]);
  $tr[]=Paragraphe("template-add-64.png","{template_add}","{template_add_emailing_text}","javascript:Loadjs('domains.emailings.templates.php?add-template-js=yes&ou={$_GET["ou"]}')");
    

  $sql="SELECT ID,template_name,zdate FROM emailing_templates WHERE ou='$ou_decrypt' ORDER BY zdate DESC";
  $q=new mysql();
  $results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){  
			
			$tr[]=Paragraphe("template-view-64.png",$ligne["template_name"],"{created_on}:{$ligne["zdate"]}<br>{click_to_edit_template}","javascript:Loadjs('domains.emailings.templates.php?edit-template-js={$ligne["ID"]}&ou={$_GET["ou"]}')");
			
			
			
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
	echo $tpl->_ENGINE_parse_body("$tbl");
}

function database_migrate_popup(){
		$tpl=new templates();
	$users=new usersMenus();
	if(!$users->AllowAddUsers){
		echo "<H2>".$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}")."</H2>";exit;
	}
	
	$ID=$_GET["database-migrate"];
	$ou=$_GET["ou"];
	$ou=base64_decode($ou);
	$emailing=new emailings($ID);
	$ldap=new clladp();
	$group=$ldap->hash_groups($ou,1);
	$group[null]="{select}";
	
	
	$field=Field_array_Hash($group,'emailing_gpid_migr',trim($emailing->array_options["gpid"]),null,null,0,"font-size:14px;padding:4px");
	$page=CurrentPageName();
	$confirm=$tpl->javascript_parse_text('{confirm}?');
	
	
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table($ou);
	while (list ($domain, $no) = each ($domains) ){
		$DOMAINS_ARRAY[$domain]=$domain;
	}	
	$DOMAINS_ARRAY[null]="{select}";
	$domains_field=Field_array_Hash($DOMAINS_ARRAY,'export_domain',$emailing->array_options["export_domain"],null,null,0,"font-size:14px;padding:4px");
	
	$html="
	<div id='database_migrate_popup_id'>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/user-migrate-90.png'></td>
	<td valign='top'>
	<div style='font-size:13px'>{migrate_user_text}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:14px'>{group}:</td>
		<td>$field</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{domain}:</td>
		<td>$domains_field</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{default_password}:</td>
		<td>". Field_password("export_default_password",$emailing->array_options["export_default_password"],"font-size:14px;padding:4px;width:120px")."</td>
	</tr>			
	<tr>
		<td colspan=2 align='right'><hr>". button("{import}","MigrateUsersToLdapPerform()")."</td>
	</tr>
	
	</table>
	
	
	
	</td>
	</tr>
	</table>
	</div>
	
	<script>
		var x_MigrateUsersToLdapPerform= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			YahooWin5Hide();
			RefreshTab('emailing_campaigns');	
		}		
	
		function MigrateUsersToLdapPerform(){
			if(confirm('$confirm')){
				var XHR = new XHRConnection();
	      		XHR.appendData('database-migrate-perform','$ID');
	      		XHR.appendData('ou','{$_GET["ou"]}');
	      		XHR.appendData('gpid',document.getElementById('emailing_gpid_migr').value);
	      		XHR.appendData('export_domain',document.getElementById('export_domain').value);
	      		XHR.appendData('export_default_password',document.getElementById('export_default_password').value);
	      		if(document.getElementById('database_migrate_popup_id')){document.getElementById('database_migrate_popup_id').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';}
		  		XHR.sendAndLoad('$page', 'GET',x_MigrateUsersToLdapPerform);	
				}
			
			}	

	</script>
	
	";
		echo $tpl->_ENGINE_parse_body("$html");
	
	
}
function database_migrate_perform(){
	$emailing=new emailings($_GET["database-migrate-perform"]);
	if(!$emailing->SET_OPTIONS($_GET)){echo $emailing->mysql_error;exit;}
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{EXPORT_OPERATION_IN_BACKGROUND}");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?emailing-database-migrate-perform={$_GET["database-migrate-perform"]}");
}

function emailing_search(){
	$ou=base64_decode($_GET["ou"]);
	$search=$_GET["emailing-search"];
	$search=str_replace("%","\%",$search);
	$search=str_replace("*","%",$search);
	if(substr($search,strlen($search)-1,1)<>"%"){$search=$search."%";}
	
	$q=new mysql();
	$sql="SELECT * FROM emailing_db_paths WHERE ou='$ou' and merged=0 ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$databasename=$ligne["databasename"];
			$sqls[$databasename]="SELECT * FROM emailing_$databasename WHERE (email LIKE '$search') OR (lastname LIKE '$search') OR (firstname LIKE '$search') LIMIT 0,100";
		}

		$html="
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
			<th>&nbsp;</th>
			<th>{firstname}</th>
			<th>{lastname}</th>
			<th>{email}</th>
			<th>{database}</th>
			<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
";
		while (list ($databasename, $sql) = each ($sqls) ){
			writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			
			if(!$q->ok){
				$html=$html."
				<tr ". CellRollOver().">
				<td colspan=6>Error $databasename:: $q->mysql_error <code>$sql</code></td>
				</tr>";
				continue;
			}
			
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$js="Loadjs('domains.emailings.contacts.php?ou=$ou&dbname=$databasename&ID={$ligne["ID"]}')";
				$js_delete="Loadjs('domains.emailings.contacts.php?ou=$ou&dbname=$databasename&ID={$ligne["ID"]}&delete=yes')";
				$img=imgtootltip("contact-48.png","{edit}","$js");
				$delete=imgtootltip("delete-32.png","{delete}","$js_delete");
				$html=$html."
				<tr  class=$classtr>
			<td width=1%>$img</th>
			<td style='font-size:14px'>&nbsp;{$ligne["firstname"]}</td>
			<td style='font-size:14px'>&nbsp;{$ligne["lastname"]}</td>
			<td style='font-size:14px'>&nbsp;{$ligne["email"]}</td>
			<td style='font-size:14px'>{$databasename}</td>
			<td style='font-size:14px'>$delete</td>
			</tr>
				
				";
				
			}
			
			
		}
		$html=$html."</table>";
	
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function database_make_unique(){
	$ou=$_GET["ou"];
	$ID=$_GET["source-table"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?emailing-make-unique-table=yes&ID=$ID");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{apply_upgrade_help}");
	
}

	
	
	
?>