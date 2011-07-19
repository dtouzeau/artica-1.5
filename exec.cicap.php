<?php
	include_once(dirname(__FILE__)."/ressources/class.templates.inc");
	include_once(dirname(__FILE__)."/ressources/class.crypt.php");
	include_once(dirname(__FILE__)."/ressources/class.user.inc");
	include_once(dirname(__FILE__)."/ressources/class.samba.inc");
	include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
	include_once(dirname(__FILE__)."/ressources/class.user.inc");	
	include_once(dirname(__FILE__)."/ressources/class.squid.inc");
	
	
	if(isset($_GET["file"])){C_ICAP_FILE();exit;}
	if(isset($_GET["download-file"])){DOWNLOAD();exit;}
	
	
function C_ICAP_FILE(){
$file=$_GET["file"];
if($file<>null){
	$ci=new cicap();
	$quarantinef=$ci->main_array["CONF"]["VirSaveDir"]."/$file";
	writelogs("$file -> $quarantinef",__FUNCTION__,__FILE__,__LINE__);
	$infos=FileInfo($quarantinef);
}else{
	$infos="ERROR! No file!";
}
$tpl=new template_users("{$_GET["file"]}",$infos,1,0,0,0);
$tpl->web_page=str_replace('LeftMenushide();','',$tpl->web_page);
echo $tpl->web_page;

}

function FileInfo($original_path){
	$path=$original_path;
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?filestat=". base64_encode($path))));
	$type=base64_decode($sock->getFrameWork("cmd.php?filetype=". base64_encode($path)));	
	$permissions=$array["perms"]["human"];
	$permissions_dec=$array["perms"]["octal1"];
	$accessed=$array["time"]["accessed"];
	$modified=$array["time"]["modified"];
	$created=$array["time"]["created"];
	$file=$array["file"]["basename"];
	$permissions_g=$array["owner"]["group"]["name"].":". $array["owner"]["owner"]["name"];
	$ext=Get_extension($file);
	$page=CurrentPageName();
	
	$cr=new SimpleCrypt($ldap->ldap_password);
	$path_encrypted=base64_encode($original_path);
	if($array["size"]["blocks"]<>null){
		$download=Paragraphe("download-64.png","{download}","{download} $file<br>".FormatBytes($array["size"]["size"]/1024),"$page?download-file=$path_encrypted");
	}
	
	$img="img/ext/def.jpg";
	if(is_file("img/ext/$ext.jpg")){$img="img/ext/$ext.jpg";}

	$table="
	<table>
	<tr>
		<td class=legend>{permission}:</td>
		<td><strong>$permissions $permissions_g ($permissions_dec)</td>
	</tr>
	<tr>
		<td class=legend>{accessed}:</td>
		<td><strong>$accessed</td>
	</tr>
<tr><td class=legend>{modified}:</td><td><strong>$modified</td></tr>
<tr><td class=legend>{created}:</td><td><strong>$created</td></tr>
<tr>
	<td class=legend>{size}:</td>
	<td><strong>{$array["size"]["size"]} bytes (". FormatBytes($array["size"]["size"]/1024).")</td>
</tr>
<tr>
	<td class=legend>blocks:</td>
	<td><strong>{$array["size"]["blocks"]}</td>
</tr>	
<tr>
	<td class=legend>block size:</td>
	<td><strong>{$array["size"]["block_size"]}</td>
</tr>
</table>";

if($array["size"]["blocks"]==null){$table=null;}
	
$html="
<div style='font-size:11px;margin-top:3px;padding-top:5px;border-top:1px solid #CCCCCC;text-align:right;'><i>$type</i></div>
<table style='width:100%'>
<tr>
<td width=1% valign='top'><img src='$img' style='margin:15px'></td>
<td valign='top'>
<hr>
$table
</td>
<td valign='top'>
$download
</td>
</tr>
</table>";
$tpl=new templates();	
return  $tpl->_ENGINE_parse_body($html);
	
}
function DOWNLOAD(){
	$ldap=new clladp();
	
	$path=base64_decode($_GET["download-file"]);
	writelogs("$path",__FUNCTION__,__FILE__,__LINE__);
	$file=basename($path);
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?file-content=".base64_encode($path)));
	$content_type=base64_decode($sock->getFrameWork("cmd.php?mime-type=".base64_encode($path)));
	header('Content-Type: '.$content_type);
	header("Content-Disposition: inline; filename=\"$file\""); 
	echo $datas;	
		
}
	
?>