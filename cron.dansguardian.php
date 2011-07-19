<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
$file=$argv[1];
$_GET["DEBUG"]=false;
if(trim(strtolower($argv[2]))=="--debug"){$_GET["DEBUG"]=true;}


if($argv[1]=='--rebuild-sites'){
	rebuildsites();
	die('finish');
}

die("depreciated");
if(!file_exists($file)){write_syslog(" Unable to stat $file",__FILE__);}
$q=new mysql();
$q->BuildTables();
if(!$q->TestingConnection()){write_syslog("Unable to logon to mysql",__FILE__);}
$datas_file=file_get_contents($file);
$datas=explode("\n",$datas_file);
write_syslog("$file: ".Count($datas) . " line(s) with " . strlen($datas_file). " bytes length",__FILE__);

while (list ($num, $val) = each ($datas) ){
		
		if(trim($val==null)){continue;}
		if($_GET["DEBUG"]){echo "Parsing line number $num/".Count($datas)."\n";}
		parseDansLine($val);
		}
		
@unlink($file);
System("/etc/init.d/artica-postfix restart squid &");


function parseDansLine($line){

	
	if(!preg_match('#([0-9\.]+)\s+([0-9\:]+)\s+-\s+(.+?)\s+ht#',$line,$re)){
		echo "Failed parsed \"$line\"\n";
	}
	$line="ht".str_replace($re[0],'',$line);
	$date=str_replace('.',"-",$re[1]) . " " . $re[2];
	$client=$re[3];
	
	if(!preg_match('#^(.+?)\s+#',$line,$re)){
		echo "Failed $line ".__LINE__."\n";
		return false;
	}
	
	$line=str_replace($re[0],'',$line);
	$uri=$re[1]."/";
	if(preg_match('#^.+?:\/\/(.+?)[\/:\s]#',$uri,$re)){
		$domain=$re[1];
	}else{
			echo "ERROR: unable to found the domain in \"$uri\"\n";
			return null;
		}
	
	
	if(preg_match('#\*DENIED\*#',$line)){
		if(preg_match("#DENIED\*\s+(.+?)[:\.](.+?)\s+(GET|POST)#",$line,$re)){
			$TYPE=$re[1];
			$raison=$re[2];
		}else{
			echo "ERROR: find reason in $line\n";
		}
		
	}else{
		$TYPE="PASS";
	}
	
	
	$domainMD5=md5($domain);
	AddSite($domain);
	if($_GET["DEBUG"]){echo "Adding new $uri ($TYPE) [$raison] $client\n";}
	$md5a=md5("$domainMD5$uri$TYPE$raison$client$date");
	$q=new mysql();
	$sql="INSERT INGNORE INTO dansguardian_events(sitename,URI,`TYPE`,REASON,CLIENT,zDate,zMD5) 
	VALUES('$domainMD5','$uri','$TYPE','$raison','$client','$date','$md5a');";
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){write_syslog("Failed : \"$sql\" ($q->mysql_error)",__FILE__);}
	
	
	
	
}

function AddSite($domain){
	$domainMD5=md5($domain);
	
	
if(!$_GET["DD"][$domainMD5]){
		if($_GET["DEBUG"]){echo "Adding site $domain\n";}
		$q=new mysql();
		$sql="INSERT INTO dansguardian_sites(website_md5,website) VALUES('$domainMD5','$domain');";
		echo "Adding domain index $domain\n";
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){write_syslog("AddSite():: Failed : \"$sql\" ($q->mysql_error)",__FILE__);}
		$_GET["DD"][$domainMD5]=TRUE;
	}else{
	if($_GET["DEBUG"]){echo "$domain already exists in database\n";}	
	}
	
}

function rebuildsites(){
	$q=new mysql();
	$sql="SELECT uri FROM dansguardian_events";
	$results=$q->QUERY_SQL($sql,'artica_events');
	while ($ligne = mysql_fetch_array($results)) {
		if(preg_match('#^.+?:\/\/(.+?)[\/:\s]#',$ligne["uri"],$re)){
			$domain=$re[1];
			AddSite($domain);
			}else{
			echo "ERROR: unable to found the domain in \"{$ligne["uri"]}\"\n";
			}
		
	}
	
}











?>