<?php
session_start();
if(!isset($_SESSION["uid"])){echo "<H1>Session Out</H1>";exit;}
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
$user=new usersMenus();
if($user->AsPostfixAdministrator==false){echo 
	$rpl=new template_users("{import users}","{not allowed}",0,1);
	echo $rpl->web_page;
	exit;}
	
if( isset($_POST['upload']) ){LicenceUploaded();exit();}
MainPage();	
	
function MainPage($results=null){
$html="<p>&nbsp;</p>
<div id='content'>
<fieldset  style='width:350px'><legend>{add new licence}</legend>
	<p>{aveserver_licence_add}</p>
<div style='font-size:11px'><code>$results</code></div>
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
	if( !is_uploaded_file($tmp_file) ){MainPage('{error_unable_to_upload_file}');exit();}
	
	 $type_file = $_FILES['fichier']['type'];
	  if( !strstr($type_file, 'key')){	MainPage('{error_file_extension_not_match} :key');	exit();}
	 $name_file = $_FILES['fichier']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){MainPage("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    include_once("ressources/class.sockets.inc");
    $socket=new sockets();
 $res=$socket->getfile("aveserver_licencemanager:$content_dir/$name_file");
 $res=str_replace("\r","",$res);
 $res=wordwrap($res,50,"\n",true);
  $res=nl2br($res);
    
MainPage($res);
}
?>