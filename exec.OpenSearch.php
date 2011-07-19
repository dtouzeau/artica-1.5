<?php
	include_once(dirname(__FILE__)."/ressources/class.templates.inc");
	include_once(dirname(__FILE__)."/ressources/class.xapian.inc");
	include_once(dirname(__FILE__)."/ressources/class.crypt.php");
	include_once(dirname(__FILE__)."/ressources/class.user.inc");
	include_once(dirname(__FILE__)."/ressources/class.samba.inc");
	include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
	include_once(dirname(__FILE__)."/ressources/class.user.inc");	
	//if(isset($_GET["welcome"])){SendDefaultXml();exit;}
	
	if(isset($_GET["XapianFileInfo"])){XapianFileInfo();exit;}
	if(isset($_GET["download-file"])){download_file();exit;}	
	if(isset($_GET["creds"])){Search();exit;}
	
	
     Welcome();
	
function SendDefaultXML(){
	$page=CurrentPageName();
	$explain="\n<Url type=\"application/x-suggestions+xml\" method=\"get\" template=\"http://ie8.ebay.com/open-search/output-xml.php?q={searchTerms}\"/>\n";
	//$explain=null;
$html="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<OpenSearchDescription xmlns=\"http://a9.com/-/spec/opensearch/1.1/\" xmlns:moz=\"http://www.mozilla.org/2006/browser/search/\">
  <ShortName>{search_on} Artica {$_SERVER['SERVER_NAME']}</ShortName>
  <Description>{search_on} Artica {$_SERVER['SERVER_NAME']}</Description>
  <Url type=\"text/html\" 
  	method=\"get\" template=\"https://{$_SERVER['SERVER_NAME']}/$page?XapianWords={searchTerms}\"/>
  	<moz:SearchForm>https://{$_SERVER['SERVER_NAME']}/$page</moz:SearchForm>
</OpenSearchDescription>";	
file_put_contents(dirname(__FILE__)."/ressources/logs/OpenSearch.xml",$html);
	
}

function Welcome(){
	
	if(isset($_POST["username"])){
		$ct=new user($_POST["username"]);
		if($ct->uidNumber==0){$error="{failed}";}else{
			if($ct->password<>$_POST["password"]){$error="{failed}";}
			else{
				$link=BuildXml();
			}
		}
	}
	
	$form="<div style='font-size:13px;padding:5px;margin-left:30px;margin-right:30px'>
	{install_search_engine_explain}
	</div>
	
	
	<form name='FFM1' METHOD='post'>
	<table style='width:60%;margin:50px;' class=table_form>
		<tr>
			<td class=legend><strong style='font-size:12px'>{username}:</td>
			<td><input type='text' name='username' value='' style='font-size:12px'>
		</tr>
		<tr>
			<td class=legend><strong style='font-size:12px'>{password}:</td>
			<td><input type='password' name='password' value='' style='font-size:12px'>
		</tr>	
		<tr>
			<td colspan=2 align='right'>
				<input type='submit' value='{submit}' style='font-size:12px'>
			</td>
		</tr>
		</table>
	
	";
	
if($link<>null){$form=null;}
	$html="
	<center>$link</center>
	<center><span style='font-size:16px;color:red;font-weight:bold'>$error</span></center>
	$form
	";
	

$tpl=new template_users("{install_search_engine}",$html,1,0,0,0);
$tpl->web_page=str_replace('LeftMenushide();','',$tpl->web_page);
echo $tpl->web_page;
	
}

function BuildXml(){
	$ldap=new clladp();
	$pass=md5($_POST["password"]);
	$page=CurrentPageName();
	$xmlfile=md5($_POST["username"]);
	$explain="\n<Url type=\"application/x-suggestions+xml\" method=\"get\" template=\"http://ie8.ebay.com/open-search/output-xml.php?q={searchTerms}\"/>\n";
	$tpl=new templates();
	$search_on=$tpl->_ENGINE_parse_body("{search_on}");
	
	
$html="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<OpenSearchDescription xmlns=\"http://a9.com/-/spec/opensearch/1.1/\" xmlns:moz=\"http://www.mozilla.org/2006/browser/search/\">
  <ShortName>$search_on Artica {$_SERVER['SERVER_NAME']}</ShortName>
  <Description>$search_on Artica {$_SERVER['SERVER_NAME']}</Description>
  <Url type=\"text/html\" 
  	method=\"get\" template=\"https://{$_SERVER['SERVER_NAME']}/$page?creds={$_POST["username"]};pass=$pass;XapianWords={searchTerms}\"/>
  	<moz:SearchForm>https://{$_SERVER['SERVER_NAME']}/$page</moz:SearchForm>
</OpenSearchDescription>";	
file_put_contents(dirname(__FILE__)."/ressources/logs/$xmlfile.xml",$html);	

$GLOBALS["ADD_HTML_HEADER"]="<link title=\"$search_on Artica {$_SERVER['SERVER_NAME']}\" type=\"application/opensearchdescription+xml\" rel=\"$search_on\" href=\"https://{$_SERVER['SERVER_NAME']}/ressources/logs/$xmlfile.xml\" />";

return "<br><br><hr><a href=\"javascript:window.external.addSearchProvider('https://{$_SERVER['SERVER_NAME']}/ressources/logs/$xmlfile.xml');\"
	style='font-size:16px;font-weight:bolder;text-decoration:underline'>&laquo;&nbsp;{install_search_engine}&nbsp;&raquo;</a>
	<br>{clickonthelink}<hr>";

}

function Search(){
	if(!preg_match("#^(.+?);pass=(.+?);XapianWords=(.+)#",$_GET["creds"],$re)){die();}
	$uid=$re[1];
	$ct=new user($uid);
	if($ct->uidNumber<2){die("$uid does not exists");}
	if(md5($ct->password)<>$re[2]){die("bad password");}
	$XapianWords=$re[3];
$xapian=new XapianSearch();
	if(!is_file("/usr/share/artica-postfix/LocalDatabases/samba.db")){
		$xapian->add_database("/usr/share/artica-postfix/LocalDatabases/samba.db");
	}	
	
	$current=$_GET["p"];
	if($current==null){$current=0;}
	$xapian->start=$current;	
	$xapian->terms=$XapianWords;
	$array=$xapian->search();
	$maxdocs=$array["ESTIMATED"];
	$page_number=round($maxdocs/10);
	if($page_number<=1){$page_text="{page}";}else{$page_text="{pages}";}
if($page_number>1){
	$max=$page_number;
	
	if($max>10){$max=11;}
		
	
for($i=0;$i<$max;$i++){
		if($i==$current){$class="id=tab_current";}else{$class=null;}
		$tab=$tab . "<li><a href=\"$page?creds={$_GET["creds"]}&p=$i\" $class>{page} $i</a></li>\n";
			
		}
		$tab="<div id=tablist>$tab</div>";		
	
}	
	
	
$table="
	<div style='width:95%;font-size:16px;padding:9px;margin-bottom:5px;text-align:right;border-bottom:1px solid #CCCCCC'>
	$maxdocs {results}&nbsp;|&nbsp;$page_number $page_text</div>
	$tab
	<table style='width:95%'>";
	if(is_array($array["RESULTS"])){
		while (list ($num, $arr) = each ($array["RESULTS"]) ){
			$DATA=$arr["DATA"];
			$PERCENT=$arr["PERCENT"];
			$PATH=$arr["PATH"];
			$TIME=$arr["TIME"];
			$SIZE=$arr["SIZE"];
			$endcoded_path=base64_encode($PATH);
			//if(!is_file($PATH)){continue;}
			$basename=basename($PATH);
			$ext=strtolower(Get_extension($basename));
			$img="img/ext/def_small.gif";
			
			if($users->AllowXapianDownload){
				$url="<a href='#' OnClick=\"javascript:XapianFileInfo('$endcoded_path')\">";
			}
			
			if(is_file("img/ext/{$ext}_small.gif")){$img="img/ext/{$ext}_small.gif";}
			$table=$table."
				<tr><td colspan=3>&nbsp;</td>
				<tr>
				<td width=1%><img src='$img'></td>
				<td width=1%><span style='font-size:16px'>$PERCENT%</span></td>
				<td width=99%><strong style='font-size:16px'>$url$basename</a></strong></td>
				</tr>
				<tr>
				<td width=1%>&nbsp;</td>
				<td colspan=2 width=99%><div style='font-size:11px;font-weight:normal'>$DATA</div></td>
				</tr>
				";
			
			
		}
		
		$table=$table."</table>";
	}
	
	$table=$table."
	<script>
			function XapianFileInfo(file){
			YahooWin2(680,'$page?creds={$_GET["creds"]}&XapianFileInfo='+file,'$title');
		}
	</script>
	
	";
	
$tpl=new template_users("&laquo;$XapianWords&raquo;&nbsp;$maxdocs {results}",$table,1,0,0,0);
$tpl->web_page=str_replace('LeftMenushide();','',$tpl->web_page);
echo $tpl->web_page;	
	
}
function XapianFileInfo(){
	
	if(!preg_match("#^(.+?);pass=(.+?);XapianWords=(.+)#",$_GET["creds"],$re)){
		echo "<H1>Bad credentials</H1>";
		die();
	
	}
	$uid=$re[1];
	$ct=new user($uid);
	if($ct->uidNumber<2){die("$uid does not exists");}
	if(md5($ct->password)<>$re[2]){die("bad password");}	
	
	
	if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
	if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}	
	$ldap=new clladp();
	$path=base64_decode($_GET["XapianFileInfo"]);
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
	$path_encrypted=base64_encode($cr->encrypt($path));
	
	$samba=new samba();
	$samba_folders=$samba->GetUsrsRights($uid);

	$download=Paragraphe("download-64.png","{download}","{download} $file<br>".FormatBytes($array["size"]["size"]/1024),"javascript:s_PopUp('$page?download-file=$path_encrypted',10,10)");
	
	if(!IfDirectorySambaRights($samba_folders,$path)){
		$download=null;
	}
	
	$img="img/ext/def.jpg";
	if(is_file("img/ext/$ext.jpg")){$img="img/ext/$ext.jpg";}

$html="<H1>$file</H1>
<code>$path</code>
<div style='font-size:11px;margin-top:3px;padding-top:5px;border-top:1px solid #CCCCCC;text-align:right;'><i>$type</i></div>
<table style='width:100%'>
<tr>
<td width=1% valign='top'><img src='$img' style='margin:15px'></td>
<td valign='top'>
<hr>
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
</table>
</td>
<td valign='top'>
$download
</td>
</tr>
</table>";
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);
	
}

function IfDirectorySambaRights($samba_folders,$pathToCheck){
	while (list ($path, $rights) = each ($samba_folders) ){
		$path=str_replace("/","\/",$path);
		$path=str_replace(".","\.",$path);
		if(preg_match("#$path#",$pathToCheck)){return true;}
		
	}
	return false;
}
function download_file(){
	$ldap=new clladp();
	$cr=new SimpleCrypt($ldap->ldap_password);
	$path=$cr->decrypt(base64_decode($_GET["download-file"]));
	$file=basename($path);
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?file-content=".base64_encode($path)));
	$content_type=base64_decode($sock->getFrameWork("cmd.php?mime-type=".base64_encode($path)));
	header('Content-Type: '.$content_type);
	header("Content-Disposition: inline; filename=\"$file\""); 
	echo $datas;	
		
}


?>