<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');


if($argv[1]=='--dump'){dump_config();die();}
if($argv[1]=='--write'){writeproxypac();die();}

writelighttpd_config();
writeproxypac();



function writelighttpd_config(){
$sock=new sockets();
$listen_port=$sock->GET_INFO("SquidProxyPacPort");	
$conf[]="#artica-postfix saved by artica lighttpd.conf";
$conf[]="server.username = \"squid\"";
$conf[]="server.groupname =  \"squid\"";
$conf[]="";
$conf[]="server.modules = (";
$conf[]="        \"mod_alias\",";
$conf[]="        \"mod_access\",";
$conf[]="        \"mod_accesslog\",";
$conf[]="        \"mod_compress\",";
$conf[]="	     \"mod_status\"";
$conf[]=")";
$conf[]="";
$conf[]="server.document-root        = \"/usr/share/proxy.pac\"";
$conf[]="server.errorlog             = \"/var/log/squid/proxy.pac.error.log\"";
$conf[]="index-file.names            = ( \"proxy.pac\")";
$conf[]="";
$conf[]="mimetype.assign             = (";
$conf[]="  \".pac\"          =>      \"application/x-ns-proxy-autoconfig\",";
$conf[]="  \".bz2\"          =>      \"application/x-bzip\",";
$conf[]="  \".tbz\"          =>      \"application/x-bzip-compressed-tar\",";
$conf[]="  \".tar.bz2\"      =>      \"application/x-bzip-compressed-tar\",";
$conf[]="  \"\"              =>      \"application/octet-stream\",";
$conf[]=" )";
$conf[]="";
$conf[]="";
$conf[]="accesslog.filename          = \"/var/log/squid/proxy.pac.log\"";
$conf[]="url.access-deny             = ( \"~\", \".inc\" )";
$conf[]="";
$conf[]="static-file.exclude-extensions = ( \".php\", \".pl\", \".fcgi\" )";
$conf[]="server.port                 = $listen_port";
$conf[]="#server.bind                = \"127.0.0.1\"";
$conf[]="#server.error-handler-404   = \"/error-handler.html\"";
$conf[]="#server.error-handler-404   = \"/error-handler.php\"";
$conf[]="server.pid-file             = \"/var/run/proxypac.pid\"";
$conf[]="server.max-fds 		   = 2048";
$conf[]="server.network-backend      = \"write\"";
$conf[]="";
$conf[]="ssl.engine                 = \"disable\"";
$conf[]="status.status-url          = \"/server-status\"";
$conf[]="status.config-url          = \"/server-config\"";

@file_put_contents("/etc/lighttpd/proxypac.conf",@implode("\n",$conf));
echo "Starting......: proxy.pac service writing configuration done (listen port on $listen_port)...\n";
}

function writeproxypac(){
	$sock=new sockets();
	$ProxyPacRemoveProxyListAtTheEnd=$sock->GET_INFO("ProxyPacRemoveProxyListAtTheEnd");
	$isInNet[]="function FindProxyForURL(url, host) {";
	
	
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	if(is_array($datas["PROXYS"])){
		while (list ($num, $uri) = each ($datas["PROXYS"])){
			$condition=proxy_list_conditions($num,$datas["CONDITIONS"][$num]);
			$proxiesCondition=conditions_explode_proxies($num,$datas["CONDITIONS"][$num],$uri);
			if($condition<>null){
				$isInNet[]="\t$condition";
				$isInNet[]="\t\treturn \"".implode("; ",$proxiesCondition)."\"";
				$isInNet[]="";
			}
			$proxies[]="PROXY $uri;";
		}
	}
	
	if(is_array($datas["localHostOrDomainIs"])){
		while (list ($num, $servername) = each ($datas["localHostOrDomainIs"])){
		if(preg_match("#dnsDomainIs:(.+?)$#",trim($servername),$re)){
				$isInNet[]="\tif(dnsDomainIs(host,\"{$re[1]}\")){return \"DIRECT\";}";
				continue;
			}
			
			$isInNet[]="\tif(localHostOrDomainIs(host,\"$servername\")){return \"DIRECT\";}";
		}	
	}
	//patch g_delmas 22/04/2011 (see http://www.artica.fr/forum/viewtopic.php?f=39&t=3429&p=16444#p16444)
	$isInNet[]="\tif(isPlainHostName(host)){return \"DIRECT\";}";
	
	if(is_array($datas["isInNet"])){
		while (list ($num, $array) = each ($datas["isInNet"])){
			$isInNet[]="\tif(isInNet(host,\"{$array[0]}\",\"{$array[1]}\")){return \"DIRECT\";}";
		}
	}
	
	if(is_array($proxies)){
		if($ProxyPacRemoveProxyListAtTheEnd<>1){
			$isInNet[]="\treturn \"". implode("; ",$proxies)."\";";}
	}
	
	if(count($datas["FINAL_PROXY"]>0)){
		$isInNet[]="\treturn \"". implode("; ",clean_final_proxies($datas["FINAL_PROXY"]))."\";";
	}
	
	$isInNet[]="";
	$isInNet[]="}";
	$isInNet[]="";
	@mkdir("/usr/share/proxy.pac",0755,true);
	@file_put_contents("/usr/share/proxy.pac/proxy.pac",@implode("\n",$isInNet));
	@chmod("/usr/share/proxy.pac/proxy.pac",0755);
	@chown("/usr/share/proxy.pac/proxy.pac","squid");
	echo "Starting......: proxy.pac service writing proxy.pac file done...\n";
}

function clean_final_proxies($array){
	while (list ($num, $uri) = each ($array)){
		$p[$uri]=$uri;
	}
	while (list ($num, $line) = each ($p)){
		$t[]="PROXY $line";
	}
	return $t;
}

function conditions_explode_proxies($num,$array,$main_proxy){
	
	$final[]="PROXY $main_proxy";
	if(is_array($array)){
		reset($array);
		while (list ($index, $condition) = each ($array)){
			if($condition["FailOverProxy"]<>null){
			$final[]="PROXY {$condition["FailOverProxy"]}";
		}
	}
	}
	
	return $final;
}

function proxy_list_conditions($num,$array){
	
	if(!is_array($array)){return null;}	
	
	while (list ($index, $condition) = each ($array)){

		if($condition["dnsDomainIs"]<>null){
			$final[]="dnsDomainIs(host, \"{$condition["dnsDomainIs"]}\")";
			
		}

		if($condition["isPlainhost"]<>null){
			$final[]="isPlainhost name(host)";
		}		
		
		
		
	}
	
	if(is_array($final)){
		return "if(".@implode($final," || ").")";
	}
	return $html;
}

function dump_config(){
	echo "Content of /etc/artica-postfix/settings/Daemons/ProxyPacDatas : \n\n";
	echo "----------------------- Decoded -------\n\n";
	$datas=base64_decode(@file_get_contents("/etc/artica-postfix/settings/Daemons/ProxyPacDatas"));
	echo $datas;
	echo "\n---------------------------------------\n\n\n";
	echo "----------------------- Array ----------\n\n";
	print_r(unserialize($datas));
	
	
	
	
}



?>