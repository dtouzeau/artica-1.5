<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

if($argv[1]=='--css'){Addcss();die();}
if($argv[1]=='--langs'){LinkLanguages();die();}
if($argv[1]=='--fixurls'){FixUrls();die();}

LoadMailManList();
LoadLdapList();
mm_cfg();
EditMailingLists();
LinkLanguages();
FixUrls();
exec(LOCATE_PHP5_BIN2().' /usr/share/artica-postfix/exec.www.install.php --vhosts &');



function LoadLdapList(){
	$ldap=new clladp();
	$filter="(&(Objectclass=ArticaMailManRobots)(cn=*))";
	
	$sr = @ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",$filter,array());
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	
	for($i=0;$i<$hash["count"];$i++){
		$list=null;$domain=null;
		if($hash[$i]["mailmanowner"][0]==null){continue;}
		$list=$hash[$i]["cn"][0];
		if(preg_match("#(.+?)@(.+)#",$list,$re)){$list=$re[1];$domain=$re[2];}

		
		$admin_email=$hash[$i]["mailmanowner"][0];
		$admin_password=$hash[$i]["mailmanownerpassword"][0];
		$webservername=$hash[$i][strtolower("MailManWebServerName")][0];	
		
		
		
		
		$GLOBALS["MAILMAN_LISTS"][strtolower($list)]=
				array(  "wwww"=>$webservername,
						"domain"=>$domain,
						"adm"=>$admin_email,
						"pass"=>$admin_password
						);
		$GLOBALS["LDAP_LISTS"][$list]=true;
		}
	

	
}


function EditMailingLists(){
	$array=$GLOBALS["MAILMAN_LISTS"];
	if(!is_array($array)){return null;}
	while (list ($list, $params) = each ($array) ){
			
			$webservername=$params["wwww"];
			$admin_email=$params["adm"];
			$admin_password=$params["pass"];
			if($GLOBALS["MAILMAN_LOCAL_LISTS"][strtolower($list)]){
				writelogs("edit distribution list $list vhost=$webservername",__FUNCTION__,__FILE__,__LINE__);
				editlist($webservername,$list,$admin_email,$admin_password);
			}else{
				writelogs("Create distribution list $list vhost=$webservername",__FUNCTION__,__FILE__,__LINE__);
				Addlist($webservername,$list,$admin_email,$admin_password);	
			}
	}	
	
}



function LoadMailManList(){
	if(!is_file("/usr/lib/mailman/bin/list_lists")){return null;}
	exec("/usr/lib/mailman/bin/list_lists -a",$array);
	
	while (list ($num, $ligne) = each ($array) ){
		
		if(preg_match("#([a-zA-Z0-9-_\.]+)\s+-\s+\[#",$ligne,$re)){

			$GLOBALS["MAILMAN_LOCAL_LISTS"][strtolower($re[1])]=true;
		}else{
			
		}
		
	}
	
	return $GLOBALS["MAILMAN_LOCAL_LISTS"];
}


function mm_cfg(){
	$files[]="/etc/mailman/mm_cfg.py";
	$files[]="/usr/lib/mailman/Mailman/mm_cfg.py";
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	while (list ($num, $filename) = each ($files) ){
		fixport($ApacheGroupWarePort,$filename);
	}
}

function fixport($port,$file){


	$arrayDOMS=$GLOBALS["MAILMAN_LISTS"];
	$add_virtualhost[]="VIRTUAL_HOSTS.clear()";
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$user=new usersMenus();
	
	if(is_array($arrayDOMS)){
		while (list ($list, $array) = each ($arrayDOMS) ){
			if($array["domain"]==null){continue;}
			$POSTFIX_STYLE_VIRTUAL_DOMAINS_CLEAN[$array["domain"]]="'".$array["domain"]."'";
			$VIRTUAL_HOSTS[]="'http://{$array["wwww"]}:$port': '{$array["domain"]}'";
			$add_virtualhost[]="add_virtualhost('{$array["wwww"]}', '{$array["domain"]}')";
			
		}
	}
	
	while (list ($index, $line) = each ($POSTFIX_STYLE_VIRTUAL_DOMAINS_CLEAN) ){$POSTFIX_STYLE_VIRTUAL_DOMAINS[]=$line;}
	
	
	if(is_array($POSTFIX_STYLE_VIRTUAL_DOMAINS)){
	  $POSTFIX_STYLE_VIRTUAL_DOMAINS_TEXT="POSTFIX_STYLE_VIRTUAL_DOMAINS=[".implode(",",$POSTFIX_STYLE_VIRTUAL_DOMAINS)."]";
	}	
	
	if(is_array($VIRTUAL_HOSTS)){
	  $VIRTUAL_HOSTS_TEXT="VIRTUAL_HOSTS = {".implode(",",$VIRTUAL_HOSTS)."}";
	}		
	
	$MAILMAN_DEFAULT_URL_PATTERN=$sock->GET_INFO("MAILMAN_DEFAULT_URL_PATTERN");
	$MAILMAN_PUBLIC_ARCHIVE_URL=$sock->GET_INFO("MAILMAN_PUBLIC_ARCHIVE_URL");
	$MAILMAN_DEFAULT_EMAIL_HOST=$sock->GET_INFO("MAILMAN_DEFAULT_EMAIL_HOST");
	$MAILMAN_DEFAULT_URL_HOST=$sock->GET_INFO("MAILMAN_DEFAULT_URL_HOST");
	$MAILMAN_DEFAULT_SERVER_LANGUAGE=$sock->GET_INFO("MAILMAN_DEFAULT_SERVER_LANGUAGE");
		
	
	if($MAILMAN_DEFAULT_URL_PATTERN==null){$MAILMAN_DEFAULT_URL_PATTERN="%s/cgi-bin/mailman/";}
	if($MAILMAN_PUBLIC_ARCHIVE_URL==null){$MAILMAN_PUBLIC_ARCHIVE_URL="http://%(hostname)s:$ApacheGroupWarePort/pipermail/%(listname)s/index.html";}
	if($MAILMAN_DEFAULT_URL_HOST==null){$MAILMAN_DEFAULT_URL_HOST="http://$user->fqdn:$port";}
	if($MAILMAN_DEFAULT_SERVER_LANGUAGE==null){$MAILMAN_DEFAULT_SERVER_LANGUAGE="en";}		
	
	
	$datas=explode("\n",@file_get_contents($file));
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#DEFAULT_URL_PATTERN#",$ligne)){unset($datas[$num]);}
		if(preg_match("#POSTFIX_STYLE_VIRTUAL_DOMAINS#",$ligne)){unset($datas[$num]);}
		if(preg_match("#VIRTUAL_HOSTS#",$ligne)){unset($datas[$num]);}
		if(preg_match("#MTA#",$ligne)){unset($datas[$num]);}
		if(preg_match("#VIRTUAL_HOST_OVERVIEW#",$ligne)){unset($datas[$num]);}
		if(preg_match("#add_virtualhost#",$ligne)){unset($datas[$num]);}
		if(preg_match("#PUBLIC_ARCHIVE_URL#",$ligne)){unset($datas[$num]);}
		if(preg_match("#DEFAULT_SERVER_LANGUAGE#",$ligne)){unset($datas[$num]);}
		if(preg_match("#DEFAULT_EMAIL_HOST#",$ligne)){
			if($MAILMAN_DEFAULT_EMAIL_HOST<>null){
				unset($datas[$num]);
			}
		}
		
		if(preg_match("#DEFAULT_URL_HOST#",$ligne)){
			if($MAILMAN_DEFAULT_URL_HOST<>null){
				unset($datas[$num]);
			}
		}		
		
		if(trim($ligne)==null){unset($datas[$num]);}
		
	}
	
	
	
	
	
	$datas[]=implode("\n",$add_virtualhost);
	$datas[]="DEFAULT_URL_PATTERN='$MAILMAN_DEFAULT_URL_PATTERN'";
	$datas[]=$VIRTUAL_HOSTS_TEXT;
	$datas[]="VIRTUAL_HOST_OVERVIEW = 1";
	$datas[]=$POSTFIX_STYLE_VIRTUAL_DOMAINS_TEXT;
	$datas[]="PUBLIC_ARCHIVE_URL= '$MAILMAN_PUBLIC_ARCHIVE_URL'";
	$datas[]="DEFAULT_SERVER_LANGUAGE= '$MAILMAN_DEFAULT_SERVER_LANGUAGE'";
	$datas[]="MTA='Postfix'";
	
	
	if($MAILMAN_DEFAULT_EMAIL_HOST<>null){
		$datas[]="DEFAULT_EMAIL_HOST='$MAILMAN_DEFAULT_EMAIL_HOST'";
	}
	
	if($MAILMAN_DEFAULT_URL_HOST<>null){
		$datas[]="DEFAULT_URL_HOST='$MAILMAN_DEFAULT_URL_HOST'";
	}	
	echo "Save $file line: ".__LINE__."\n";
	@file_put_contents($file,implode("\n",$datas));
	FixUrls();
}

function FixUrls(){
	LoadLdapList();
	$arrayDOMS=LoadMailManList();
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");	
	writelogs("ApacheGroupWarePort=$ApacheGroupWarePort",__FUNCTION__,__FILE__,__LINE__);
	if(is_array($arrayDOMS)){
		while (list ($list, $none) = each ($arrayDOMS) ){
			$server=$GLOBALS["MAILMAN_LISTS"][$list]["wwww"];
			if($server==null){continue;}
			$uri="http://$server:$ApacheGroupWarePort";
			$cmd="/usr/lib/mailman/bin/withlist -l -r fix_url $list -u $uri";
			writelogs("$list=$uri -> $cmd",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($cmd);
		}
	}	
}




function Addlist($vhost,$listname,$admin_mail,$password){
	if($ApacheGroupWarePort==80){$ApacheGroupWarePort=null;}else{$ApacheGroupWarePort=":$ApacheGroupWarePort";}
	$unix=new unix();
	$emailhost=$unix->hostname_g();	
	$cmd="/usr/lib/mailman/bin/newlist --quiet --urlhost=$vhost --emailhost=$emailhost $listname $admin_mail $password";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$output);
	FixUrls();
}
function editlist($vhost,$listname,$admin_mail,$password){
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	if($ApacheGroupWarePort==80){$ApacheGroupWarePort=null;}else{$ApacheGroupWarePort=":$ApacheGroupWarePort";}
	$unix=new unix();
	$emailhost=$unix->hostname_g();	
	
	$mailserver=$GLOBALS["MAILMAN_LISTS"][strtolower($listname)]["domain"];
	$cmd="/usr/lib/mailman/bin/config_list -o /tmp/$listname.txt $listname";
	exec($cmd);
	$datas=explode("\n",@file_get_contents("/tmp/$listname.txt"));
	while (list ($index, $line) = each ($datas) ){
		if(preg_match("#^host_name#",$line)){
			echo "change hostname\n";
			$datas[$index]="host_name = '$mailserver'";
		}
	}
	
	@file_put_contents("/tmp/$listname.txt",implode("\n",$datas));
	$cmd="/usr/lib/mailman/bin/config_list -i /tmp/$listname.txt $listname";
	exec($cmd);
	@unlink("/tmp/$listname.txt");
	}
	
function Addcss(){
	$path="/usr/share/mailman";
	$unix=new unix();
	$dir=$unix->dirdir($path);
	if(!is_array($dir)){return null;}
	while (list ($path, $dirname) = each ($dir) ){
		Patchcss("$path/private.html");
		Patchcss("$path/options.html");
		Patchcss("$path/admlogin.html");
		Patchcss("$path/subscribe.html");
		Patchcss("$path/emptyarchive.html");
		Patchcss("$path/article.html");
		Patchcss("$path/archtocnombox.html");
		Patchcss("$path/listinfo.html");
		Patchcss("$path/archidxhead.html");
		Patchcss("$path/roster.html");
		Patchcss("$path/archtoc.html");	
	}
	
 $dir=$unix->dirdir("/etc/mailman");
 while (list ($path, $dirname) = each ($dir) ){
 		Patchcss("$path/private.html");
		Patchcss("$path/options.html");
		Patchcss("$path/admlogin.html");
		Patchcss("$path/subscribe.html");
		Patchcss("$path/emptyarchive.html");
		Patchcss("$path/article.html");
		Patchcss("$path/archtocnombox.html");
		Patchcss("$path/listinfo.html");
		Patchcss("$path/archidxhead.html");
		Patchcss("$path/roster.html");
		Patchcss("$path/archtoc.html");	
 }

if(is_file("/usr/lib/mailman/Mailman/htmlformat.py")){
	$tb=explode("\n",@file_get_contents("/usr/lib/mailman/Mailman/htmlformat.py"));
	 while (list ($index, $line) = each ($tb) ){
	 	if(preg_match("#output\.append\(.+?HEAD.+?\)#",$line)){
	 		$tb[$index]="\t\toutput.append('%s<link href=\"../../../css/style.css\" rel=\"stylesheet\"  type=\"text/css\" /></HEAD>' % tab)";
	 		@file_put_contents("/usr/lib/mailman/Mailman/htmlformat.py",implode("\n",$tb));
	 	}
	 }
}
	
}



function Patchcss($filename){
if(!is_file($filename)){
	writelogs("Unable to stat $filename",__FUNCTION__,__FILE__,__LINE__);
}
$dd=@file_get_contents($filename);
if(!preg_match("#<head>(.+?)</head>#is",$dd,$re)){
	writelogs("Unable to preg_match in $filename",__FUNCTION__,__FILE__,__LINE__);
}

$head=$re[1];

if(preg_match("#\.\.\/\.\.\/css\/style.css#is",$head)){return null;}
$re[1]=$re[1]."\n<link href=\"../../../css/style.css\" rel=\"stylesheet\"  type=\"text/css\" />\n";
$dd=str_ireplace($head,$re[1],$dd);
$dd=str_ireplace("BGCOLOR=\"#99CCFF\"","class=\"title\"",$dd);
$dd=str_ireplace('BGCOLOR="#FFF0D0"',"class=\"subtitle\"",$dd);
$dd=str_ireplace('<FONT COLOR="#000000" SIZE="+1">',"",$dd);
$dd=str_ireplace('<FONT COLOR="#000000">',"",$dd);
$dd=str_ireplace('BGCOLOR="#dddddd"',"class='legend'",$dd);
@file_put_contents($filename,$dd);
}
function LinkLanguages(){
	$unix=new unix();
	$array=$unix->dirdir("/usr/share/mailman");
	if(!is_array($array)){return null;}
	while (list ($index, $path) = each ($array) ){
		//echo "\$langs[\"$path\"]=\"$path\";\n";
		
		if(!is_dir("/etc/mailman/$path")){
			symlink($index,"/etc/mailman/$path");
			echo "Creating symbolic link for /etc/mailman/$path\n";
		}
	}
	
	
	
	
}
	
	
?>