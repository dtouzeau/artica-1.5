<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){echo "alert('no privileges')";exit;}


if(isset($_GET["pommo-js"])){js();exit;}
if(isset($_GET["pommo-index"])){index();exit;}
if(isset($_GET["PommoFieldhostname"])){pommosave();exit;}
if(isset($_GET["PommoReset"])){PommoReset();exit;}



function pommosave(){
	
	$sock=new sockets();
	$sock->SET_INFO("PommoFieldlang",$_GET["PommoFieldlang"]);
	$sock->SET_INFO("PommoFieldhostname",$_GET["PommoFieldhostname"]);
	$datas=$sock->getfile("EmergencyStart:pommo");
	echo $datas;
	
}

function PommoReset(){
	$q=new mysql();
	$ldap=new clladp();
 	$sql="UPDATE pommo_config SET config_value ='$ldap->ldap_admin' WHERE config_name='admin_username'";
 	$q->QUERY_SQL($sql,'pommo');
 
    
    $sql="UPDATE pommo_config SET config_value ='".md5($ldap->ldap_password)." WHERE config_name='admin_password'";
    $q->QUERY_SQL($sql,'pommo');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{restore_admin_text}');
}



function index(){
	
	$lang=array("en"=>"English (en)",
"en-uk"=>"british english (en-uk)",
"bg"=>"????????? (bg)",
"da"=>"dansk (da)",
"de"=>"deutsch (de)",
"es"=>"espanol (es)",
"fr"=>"francais (fr)",
"it"=>"italiano (it)",
"nl"=>"nederlands (nl)",
"pl"=>"polski (pl)",
"pt"=>"portugues (pt)",
"pt-br"=>"brasil portugues (pt-br)",
"ro"=>"romana (ro)",
"ru"=>"??????? ???? (ru)");
	
$sock=new sockets();
$sock->DeleteCache();
$ll=Field_array_Hash($lang,'PommoFieldlang',$sock->GET_INFO('PommoFieldlang'));


$html="
<img src='img/bg_pommo.png' style='float:right;margin-top:-8px'><H1>{APP_POMMO}</h1>
<p class=caption>{pommo_explain}</p>
<div id='pommodiv'>
<table style='width:100%' class=table_form>
<tr>
<td valign='top' nowrap class=legend>{default_language}:</td>
<td>$ll</td>
</tr>
<tr>
<td valign='top' nowrap class=legend>{hostname}:</td>
<td>".Field_text("PommoFieldhostname",$sock->GET_INFO("PommoFieldhostname"),'width:150px')."</td>
</tr>
<tr>
	<td colspan=2 align='right'><input type='button' OnClick=\"javascript:PommoSave();\" value='{edit}&nbsp;&raquo;'>
</tr>
<tr>
	<td colspan=2 align='right'><input type='button' OnClick=\"javascript:PommoReset();\" value='{restore_admin}&nbsp;&raquo;'>
</tr>

</table>
</div>
";



	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}



function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_POMMO}');
	$page=CurrentPageName();
	$html="
		function Load_pommo(){
			YahooWin(600,'$page?pommo-index=yes','$title');
		
		
		}
		
var X_PommoSave= function (obj) {
	var results=obj.responseText;
	alert(results);
	Load_pommo();
	}			
		
function PommoSave(){
		var XHR = new XHRConnection();
        XHR.appendData('PommoFieldhostname',document.getElementById('PommoFieldhostname').value);
        XHR.appendData('PommoFieldlang',document.getElementById('PommoFieldlang').value);
        document.getElementById('pommodiv').innerHTML=\"<center><img src='img/wait_verybig.gif'></center>\";
        XHR.sendAndLoad('$page', 'GET',X_PommoSave); 
}		
	
function PommoReset(){
		var XHR = new XHRConnection();
        XHR.appendData('PommoReset','yes');
        document.getElementById('pommodiv').innerHTML=\"<center><img src='img/wait_verybig.gif'></center>\";
        XHR.sendAndLoad('$page', 'GET',X_PommoSave); 
}		



Load_pommo();
	";
	
	echo $html;
	
}




	
?>	

