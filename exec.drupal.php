<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;}

$sock=new sockets();
$unix=new unix();

if($argv[1]=="--init"){initialize();die();}
if($argv[1]=="--build"){buildconf();die();}
if($argv[1]=="--cron"){execute_cron();die();}

function buildconf(){
$q=new mysql();	
$conf[]="<?php";
$conf[]="\$db_url = 'mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server:$q->mysql_port/drupal';";
$conf[]="\$db_prefix = '';";
$conf[]="\$update_free_access = FALSE;";
$conf[]="# \$base_url = 'http://www.example.com';  // NO trailing slash!";
$conf[]="ini_set('arg_separator.output',     '&amp;');";
$conf[]="ini_set('magic_quotes_runtime',     0);";
$conf[]="ini_set('magic_quotes_sybase',      0);";
$conf[]="ini_set('session.cache_expire',     200000);";
$conf[]="ini_set('session.cache_limiter',    'none');";
$conf[]="ini_set('session.cookie_lifetime',  2000000);";
$conf[]="ini_set('session.gc_maxlifetime',   200000);";
$conf[]="ini_set('session.save_handler',     'user');";
$conf[]="ini_set('session.use_cookies',      1);";
$conf[]="ini_set('session.use_only_cookies', 1);";
$conf[]="ini_set('session.use_trans_sid',    0);";
$conf[]="ini_set('url_rewriter.tags',        '');";




$conf[]=" \$conf = array(";
$conf[]="   'site_name' => 'First Artica Drupal site',";
$conf[]="   'theme_default' => 'minnelli',";
$conf[]="   'anonymous' => 'Visitor',";
$conf[]="   'maintenance_theme' => 'minnelli',";
$conf[]="   'reverse_proxy' => FALSE,";
$conf[]="//'reverse_proxy_addresses' => array('a.b.c.d', ...),";
$conf[]=" );";
$conf[]="?>";	

@file_put_contents("/usr/share/drupal/sites/default/settings.php",@implode("\n",$conf));
@chmod("/usr/share/drupal/sites/default/settings.php",0666);
if(is_file("/usr/share/drupal/sites/default/default.settings.php")){@unlink("/usr/share/drupal/sites/default/default.settings.php");}
	
}

function initialize(){
	
	$q=new mysql();
	if(!$q->DATABASE_EXISTS("drupal")){
		$q->CREATE_DATABASE("drupal");
		if(!$q->DATABASE_EXISTS("drupal")){
			echo "Failed to create \"drupal\" database with error $q->mysql_error\n";
		}
		
	}
	
	
}

function execute_cron(){
$ldap=new clladp();
$sock=new sockets();
$unix=new unix();
$wget=$unix->find_program("wget");
if($wget==null){echo "Unable to stat wget\n";}
$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
$attr=array();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
//print_r($hash);
for($i=0;$i<$hash["count"];$i++){
	
		$root=$hash[$i]["apachedocumentroot"][0];
		$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
		$apacheservername=trim($hash[$i]["apacheservername"][0]);
		
		$dn=$hash[$i]["dn"];
		$wwwsslmode=$hash[$i]["wwwsslmode"][0];
		if(preg_match("#ou=www,ou=(.+?),dc=organizations#",$dn,$re) ){$hash[$i]["OU"][0]=trim($re[1]);$ouexec=trim($re[1]);}
		
		if($GLOBALS["ONLY"]<>null){if($wwwservertype<>$GLOBALS["ONLY"]){continue;}}
		
		
		if($wwwservertype=="DRUPAL"){
			if($wwwsslmode=="TRUE"){$port="443";}else{$port=$ApacheGroupWarePort;}
			$cmd="$wget -O - -q http://$apacheservername:$port/cron.php";
			echo "running $cmd";
			shell_exec($cmd);
		}		
		
		
	}	
}

?>