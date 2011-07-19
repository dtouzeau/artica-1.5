<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.computers.inc');
	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	if(isset($_GET["mysql-check"])){mysqlcheck();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["database"])){database_infos();exit;}
	

js();	

		
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{browse_mysql_server}',"mysql.index.php");
	$tables_list=$tpl->_ENGINE_parse_body('{tables_list}',"mysql.index.php");
	$uid=$_GET["uid"];
	$prefix=str_replace(".","_",$page);
	$html="
	var mem_id='';
	
	function {$prefix}LoadMainRI(){
		YahooWin3('650','$page?popup=yes','$title');
		}	
		
		
	function LoadMysqlTables(database){
		YahooWin4('650','$page?database='+database,'$tables_list::'+database);
	}
		
	
	
	{$prefix}LoadMainRI();
	";
	
echo $html;	
}

function popup(){
$list=DATABASES_LIST();

$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>$warn$refresh<br>$add<br>$DEF_ICO_REMOTE_STORAGE</td>
	<td valign='top'>
		<div class=explain>{browse_mysql_server_text}</div>
		<div id='databasemysqllist' style='width:100%;height:250px;overflow:auto'>$list</div>
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mysql.index.php');		
	
}

function database_infos(){
	
	$database=$_GET["database"];
	$list=TABLE_LIST($database);
	

$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>$warn$refresh<br>$add<br>$DEF_ICO_REMOTE_STORAGE</td>
	<td valign='top'>
		<div class=explain>{browse_mysql_server_text}</div>
		<div id='tablemysqllist' style='width:100%;height:550px;overflow:auto'>$list</div>
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mysql.index.php');		
}


function TABLE_LIST($database){
	$q=new mysql();
	$array=$q->TABLES_LIST($database);	
	$page=CurrentPageName();
	$tpl=new templates();
	//$array[$Name]=array($dbsize,$dbsize_text,$Rows,$Max_data_length);
	$perfrom_mysqlcheck=$tpl->javascript_parse_text("{perform_mysql_check}");


	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{table}</th>
		<th>{table_size}</th>
		<th>{rows_number}</th>
	</tr>
</thead>
<tbody class='tbody'>";

while (list ($num, $ligne) = each ($array) ){
		if($ligne[0]==null){$ligne[0]="0";}
		$dbsize=$ligne[0];
		$dbsize_text=$ligne[1];
		$Rows=$ligne[2];
		$Max_data_length=$ligne[3];
		$status=TABLE_STATUS($Max_data_length,$dbsize);
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$mysqlcheck=imgtootltip("tables-failed-22.png","MySQL check","MysqlCheck('$num')");
		
		
		$html=$html . "
		<tr class=$classtr>
			<td><code style='font-size:16px;font-weight:bold'>$num</code></td>
			<td width=1%><code style='font-size:16px;font-weight:bold'>$dbsize_text</code></td>
			<td width=1%><code style='font-size:16px;font-weight:bold'>$Rows</code></td>
		</tr>
		<tr class=$classtr>
			<td width=1% colspan=4 align='right' >
				<table style='width:100%' style='background-color: transparent;'>
					<tr style='background-color: transparent;'>
						<td width=99% style='background-color: transparent;'>$mysqlcheck</td>
						<td width=1% nowrap style='background-color: transparent;'><i style='font-size:13px'>$status</i></td>
					</tr>
				</table>
			</td>
		</tr>";
		
	}
	
	$html=$html . "</table>
	<script>

	var x_MysqlCheck= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);return;}	
		
	}		
	
	function MysqlCheck(table){
		if(confirm('$perfrom_mysqlcheck\\n$database/'+table)){
			var XHR = new XHRConnection();
			XHR.appendData('mysql-check',table);
			XHR.appendData('database',table);	
			XHR.sendAndLoad('$page', 'GET',x_MysqlCheck);
			}
		}	

	</script>
		
	
	
	
	";
	return $html;
	
}

function mysqlcheck(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?mysql-check=yes&database={$_GET["database"]}&table={$_GET["mysql-check"]}");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{operation_launched_in_background}");
	
}



function MYSQL_NO_CONNECTIONS($q){
	
	$a=Paragraphe("warning64.png","{ERROR_MYSQL_CONNECTION}",$q->mysql_error);
	$i=Buildicon64('DEF_ICO_MYSQL_PWD');
	$s=Buildicon64("DEF_ICO_MYSQL_USER");
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'>$a</td>
		<td valign='top'>$i</td>
	</tr>
	<tr>
		<td valign='top'>$s</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>";
	return $html;
}


function DATABASES_LIST(){
	
	$q=new mysql();
	$array=$q->DATABASE_LIST();
	
	if(!is_array($array)){
		return MYSQL_NO_CONNECTIONS($q);
		
	}
	
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{database}</th>
		<th>{tables_number}</th>
		<th>{database_size}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	while (list ($num, $ligne) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne[0]==null){$ligne[0]="0";}
		$js="LoadMysqlTables('$num');";
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/datasource-32.png'></td>
			<td width=1% nowrap><a href=\"javascript:blur();\" OnClick=\"$js\" style='font-size:16px;font-weight:bold;text-decoration:underline'>$num</code></td>
			<td width=1% nowrap><code style='font-size:16px;font-weight:bold'>{$ligne[0]}</code></td>
			<td width=80% nowrap><code style='font-size:16px;font-weight:bold'>{$ligne[1]}</code></td>
		</tr>";
		
	}
	
	$html=$html . "
	</tbody>
	</table>";
	return $html;
	
}

function TABLE_STATUS($Max_data_length,$dbsize){
	$pourc=($dbsize/$Max_data_length)*100;
	$pourc=round($pourc,3);
	$color="#5DD13D";
	
	$dbsize=ParseBytes($dbsize);
	$Max_data_length=ParseBytes($Max_data_length); 
	return "$dbsize/$Max_data_length $pourc%";
	
return "<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_postfix_compile'>
						<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
							<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>$dbsize/$Max_data_length&nbsp;{$pourc}%</strong></center>
						</div>
					</div>
				</div>"	;
	
}


?>