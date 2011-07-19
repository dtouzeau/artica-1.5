<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["iframe"])){up_iframe();exit;}
if( isset($_POST['upload']) ){NTConfigloaded();exit();}
js();

function js(){
	
$tpl=new templates();
$page=CurrentPageName();
$prefix=str_replace('.',"_",$page);
$title=$tpl->_ENGINE_parse_body('{NTconfig}',"fileshares.index.php");
$buttonText=$tpl->_ENGINE_parse_body('{browse}',"fileshares.index.php");

$html="
	var timeout=0;

	function {$prefix}_load(){
		YahooWin5('650','$page?popup=yes','$title');
	}
	





{$prefix}_load();";
	
echo $html;	
}

function popup(){
$page=CurrentPageName();	
$icon=Buildicon64('DEF_ICO_NTPOL_HOWTO');	
	
$html="<H1>{NTconfig}</H1>
<p class=caption>{NTconfig_text}</p>
<p class=caption>{NTconfig_howto}</p>
<table style='width:100%'>
<tr>
<td valign='top'>
". RoundedLightWhite("
<iframe src='$page?iframe=yes' style='width:100%;height:120px;border:0px'></iframe>

")."</td>
<td valign='top'>$icon</td>
</tr>
</table>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"fileshares.index.php");
	
}


function up_iframe($error=null){
	$file=dirname(__FILE__)."/ressources/conf/upload/NTconfig.pol";
	if(is_file($file)){
		$download="<a href='ressources/conf/upload/NTconfig.pol' style='font-weight:bold;color:blue;font-size:12px;font-family:arial;text-decoration:underline'>{download} {policy}</a>";
	}
	$page=CurrentPageName();
$html="
<div style='color:red;font-size:12px;font-family:arial'>$error</div>
<div style='color:blue;font-size:12px'>$download</div>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
<p>
<input type=\"file\" name=\"fichier\" size=\"30\">
<hr>
<div style='text-align:right'>
<input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:90px'>
</div>
</p>
</form>";	
	$tpl=new templates();
	echo iframe($tpl->_ENGINE_parse_body($html),0,0);
	
}

function NTConfigloaded(){
	$tmp_file = $_FILES['fichier']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){up_iframe('{error_unable_to_upload_file}');exit();}
	
	 $type_file = $_FILES['fichier']['type'];
	 if( !strstr($type_file, 'application/octet-stream')){	up_iframe('{error_file_extension_not_match} :'.$type_file.' did not match application/octet-stream');	exit();}
	 $name_file = $_FILES['fichier']['name'];
	 $ext=file_ext($name_file);
	  if( !strstr($ext, 'pol')){	up_iframe('{error_file_extension_not_match} :.'.$ext.' did not match .pol');	exit();}

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/NTconfig.pol") ){up_iframe("{error_unable_to_move_file} : $content_dir/NTconfig.pol");exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    $res="{success}";

$sock=new sockets();
$sock->getfile("PushNTconfig:$content_dir/NTconfig.pol");
up_iframe($res);
}

?>