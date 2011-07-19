<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["upload"])){PAGE_KAS_ADD_LICENCE();exit;}
if( isset($_POST['upload']) ){LicenceUploaded();exit();}
if(isset($_GET["ajax-pop"])){popup();exit;}

$page=PageKas3Licence();


$tpl=new template_users('{product_licence}',$page);
echo $tpl->web_page;


function popup(){
include_once('kas-tabs.php');
$page=PageKas3Licence();
	$html="
	
	<H1>{statusandlicense}</H1>
	$tab
	$page";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function PageKas3Licence(){
	$tpl=new templates();
		$yum=new usersMenus();
		if($yum->AsPostfixAdministrator==false){return $tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		include_once('ressources/class.kas-filter.inc');	
		$kas1=new KasLicence();
		
$kas=new kas_filter();
$st=$kas->KasStatus();		
		
		$html="<h5>{licence status}</h5>
		
		
		<table style='width:100%'>
		<tr>
		<td width='65%' valign='top'>
		<div style='background-color:white;font-size:11px;padding:3px;border:1px solid #CCCCCC'>
		$kas1->fulldata
		</div>
		$st</table>
		</td>
		<td width='35%' valign='top'>" . AddAnewKey(). "</td>
		</tr>
		</table>";
		
		
		
		
		
		return $tpl->_ENGINE_parse_body($html);
	}
	
function AddAnewKey(){
	$tpl=new templates();
	$page=CurrentPageName();
	$html="<H4>{licence operations}</H4>
	<center><input type='button' Onclick=\"javascript:s_PopUp('$page?upload=yes','550','550');\" value='&laquo;&nbsp;{add new licence}&nbsp;&raquo;'></center>";
	
	$html=Paragraphe('add-key-64.png','{add_a_license}','{add_a_license_text}',"javascript:s_PopUp(\"$page?upload=yes\",\"550\",\"550\");") . "<br>
	" . Paragraphe('shopping-cart-64.png','{by_a_license}','{by_a_license_text}',"javascript:MyHref(\"http://www.kaspersky.com/buy_kaspersky_anti-spam\")");
	
	return $tpl->_ENGINE_parse_body($html,'milter.index.php');
	
	
}


function PAGE_KAS_ADD_LICENCE($error=null){
if(!isset($_SESSION["uid"])){echo "<H1>Session Out</H1>";exit;}
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
$user=new usersMenus();
$page=CurrentPageName();
if($user->AsPostfixAdministrator==false){echo 
	$rpl=new template_users("{import users}","{not allowed}",0,1);
	echo $rpl->web_page;
	exit;}
	
$html="<p>&nbsp;</p>
<div id='content' style='width:400px'>
<h4>{add new licence}</h4>
<p>{kas_licence_text}</p>
<div style='font-size:11px'><code>$error</code></div>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
<p>
<input type=\"file\" name=\"fichier\" size=\"30\">
<input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:90px'>
</p>
</form>

</div>
</fieldset>
";	
$tpl=new template_users('{add new licence}',$html,0,1);
echo $tpl->web_page;
}

function LicenceUploaded(){
	$tmp_file = $_FILES['fichier']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){PAGE_KAS_ADD_LICENCE('{error_unable_to_upload_file}');exit();}
	
	 $type_file = $_FILES['fichier']['type'];
	  if( !strstr($type_file, 'key')){	PAGE_KAS_ADD_LICENCE('{error_file_extension_not_match} :key');	exit();}
	 $name_file = $_FILES['fichier']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){PAGE_KAS_ADD_LICENCE("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    include_once("ressources/class.sockets.inc");
    $socket=new sockets();
 $res=$socket->getfile("kas_licencemanager:$content_dir/$name_file");
 $res=str_replace("\r","",$res);
 $res=wordwrap($res,50,"\n",true);
  $res=nl2br($res);
    
PAGE_KAS_ADD_LICENCE($res);
}
	
?>	