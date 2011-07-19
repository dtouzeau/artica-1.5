<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.mysql.inc");


if(posix_getuid()<>0){
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["popup-wizard"])){popup_wizard();exit;}
if(isset($_GET["connexions_list"])){echo connexions_list();exit;}
if(isset($_GET["AddConnexion"])){AddConnexion();exit;}

js();


function js(){
	
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{ARTICA_VPN_CONNECT}');
	
	$html="
		function {$prefix}StartPage(){
			YahooWin3('650','$page?popup=yes');
		
		}
		
		function WIZARD_CONNECT_ARTICA(){
			YahooWin4('650','$page?popup-wizard=yes');
		}
		
	var x_WIZARD_CONNECT_ARTICA_ADD=function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		YahooWin4Hide();
		OpenVpnCOnnexionsRefresh();
		}		
		
		function WIZARD_CONNECT_ARTICA_ADD(){
			var XHR = new XHRConnection();
			XHR.appendData('AddConnexion','yes');
			XHR.appendData('connexion_name',document.getElementById('connexion_name').value);
			XHR.appendData('servername',document.getElementById('servername').value);
			XHR.appendData('port',document.getElementById('port').value);		
			XHR.appendData('username',document.getElementById('username').value);
			XHR.appendData('password',document.getElementById('password').value);
			XHR.appendData('id',document.getElementById('id').value);
			document.getElementById('WIZARD_CONNECT_ARTICA_DIV').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_WIZARD_CONNECT_ARTICA_ADD);
		
		}
		
		
		function OpenVpnCOnnexionsRefresh(){
			LoadAjax('connexions_list','$page?connexions_list=yes');
		
		}
		
		function EditConnextion(ID){
			YahooWin4('650','$page?popup-wizard=yes&ID='+ID);
		}
		
		function DelConnexion(id){
			LoadAjax('connexions_list','$page?connexions_list=yes&delete='+id);
			YahooWin4Hide();
		}
		
	
	{$prefix}StartPage();";
	
	echo $html;
}

function AddConnexion(){
	
if(posix_getuid()<>0){
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo replace_accents(html_entity_decode($tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}")));
		die();exit();
	}
}	
	
	$sql="INSERT INTO vpnclient (servername,serverport,admin,password,connexion_name) 
	VALUES('{$_GET["servername"]}','{$_GET["port"]}','{$_GET["username"]}','{$_GET["password"]}','{$_GET["connexion_name"]}');";
	
	
	if($_GET["id"]>0){
		$sql="UPDATE vpnclient SET servername='{$_GET["servername"]}',
		serverport='{$_GET["port"]}',
		admin='{$_GET["username"]}',
		password='{$_GET["password"]}',
		connexion_name='{$_GET["connexion_name"]}' WHERE ID='{$_GET["id"]}'";
		
		
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){
		echo $q->mysql_error;
	}
	
	
}


function popup(){
	
	$artica_wizard=Paragraphe("64-wizard.png",'{WIZARD_CONNECT_ARTICA}','{WIZARD_CONNECT_ARTICA_TEXT}',"javascript:WIZARD_CONNECT_ARTICA();");
	
	if(!function_exists("curl_init")){
		$warn1=Paragraphe("64-red.png",'{ERROR_CURL_SO}','{ERROR_CURL_SO_TEXT}');
		
		
	}
	
	$panel="$artica_wizard<br>$warn1";
	$remotesites=connexions_list();
	
	$html="<H1>{ARTICA_VPN_CONNECT}</H1>
	<table style='width:100%'>
	<tr>
		
		<td valign='top'>
				<p class=caption>{ARTICA_VPN_CONNECT_TEXT}</p>
				". RoundedLightWhite("<div id='connexions_list' style='width:100%;height:220px;overflow:auto'>$remotesites</div>")."
		</td>
		<td valign='top'><img src='img/2monitors-stars-128.png'><hr>$panel</td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function connexions_list(){
if(posix_getuid()<>0){
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo replace_accents(html_entity_decode($tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}")));
		die();exit();
	}
}		
	
	
	$q=new mysql();
	
	if(isset($_GET["delete"])){
		$sql="DELETE FROM vpnclient WHERE ID='{$_GET["delete"]}'";
		$results=$q->QUERY_SQL($sql,"artica_backup");
	}
	
	$sql="SELECT * FROM vpnclient ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	
	
		
		$js="EditConnextion({$ligne["ID"]})";
		$html="<table style='width:100%'>";
		$html=$html . "
		<tr ". CellRollOver($js).">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=99%><strong style='font-size:12px'>{$ligne["connexion_name"]}</strong></td>
			<td width=99%><strong style='font-size:12px'>{$ligne["servername"]}</strong></td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DelConnexion({$ligne["ID"]})")."</td>
		</tr>
		
		";
	}
	$html=$html."</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
}

function popup_wizard(){
	$but="{add}";
	if(isset($_GET["ID"])){
		$but="{edit}";
		$sql="SELECT * FROM vpnclient WHERE ID={$_GET["ID"]}";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		
	}
	
if($ligne["serverport"]==null){$ligne["serverport"]=9000;}
	
	
	$form="
	<input type='hidden' id='id' value='{$_GET["ID"]}'>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{connexion_name}:</td>
		<td valign='top'>". Field_text('connexion_name',$ligne["connexion_name"],'width:180px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{servername}:</td>
		<td valign='top'>". Field_text('servername',$ligne["servername"],'width:180px')."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{artica_listen_port}:</td>
		<td valign='top'>". Field_text('port',$ligne["serverport"],'width:120px')."</td>
	</tr>		
	<tr>
		<td valign='top' class=legend>{username}:</td>
		<td valign='top'>". Field_text('username',$ligne["admin"],'width:120px')."</td>
	</tr>		
	<tr>
		<td valign='top' class=legend>{password}:</td>
		<td valign='top'>". Field_password('password',$ligne["password"],'width:120px')."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:WIZARD_CONNECT_ARTICA_ADD();\" value='$but&nbsp;&raquo;'></td>
	</tr>
	</table>		
	";
	$form=RoundedLightWhite($form);
	
	$html="<H1>{WIZARD_CONNECT_ARTICA}</H1>
	<div id='WIZARD_CONNECT_ARTICA_DIV'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/64-wizard.png'></td>
		<td valign='top'>
		<p class=caption>{WIZARD_CONNECT_ARTICA_TEXT}</p>
		$form</td>
		<td valign='top'>$panel</td>
	</tr>
	</table>
	</div>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"san.cluster.php");	
	
}



?>