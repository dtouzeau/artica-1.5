<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/ressources/class.emailings.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

if($argv[1]=="--id"){parse_db($argv[2]);die();}
if($argv[1]=="--import-id"){import_users($argv[2]);die();}
if($argv[1]=="--make-unique"){make_database_unique($argv[2]);die();}
if($argv[1]=="--get-prefix"){get_prefix($argv[2]);die();}



$unix=new unix();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$pid=trim(@file_get_contents($pidfile));
if($unix->process_exists($pid)){
	$pid=getmypid();
	echo "[$pid]:: Process $pid already running...\n";
	die();
}


function get_prefix($path){
	
	if(!is_file("$path.pre")){
	$handle = @fopen("$path", "r");
	$d=0;
	if ($handle) {
			while (!feof($handle)){
				$d++;
				$buffer = fgets($handle, 4096);
				
				if(preg_match("#^(.+?)\..+?@#",$buffer,$re)){
					$prefix_point[$re[1]]=true;
					//echo "point:{$re[1]}\n";
					
				}
				
				if(preg_match("#^(.+?)\-.+?@#",$buffer,$re)){
					$prefix_tiret[$re[1]]=true;
					//echo "tiret:{$re[1]}\n";
				}				
				
			
				if($d>10000){
					$dixmille=$dixmille+10000;
					echo "ok $dixmille ". count($prefix_tiret)." prefix\n";
					$d=0;
				}
			}	
	}

	echo "Purge db\n";
	while (list ($prefix, $none) = each ($prefix_point) ){
		if($prefix_tiret[$prefix]){
			$newprefix[$prefix]=true;
		}
		
	}
	
	@file_put_contents("$path.pre",serialize($newprefix));
	}else{
		$newprefix=unserialize(@file_get_contents("$path.pre"));
	}
	echo "close db ". count($newprefix)." prefixes\n";
	@fclose($handle);
	if(is_file("$path.clean")){@unlink("$path.clean");}
	echo "open $path read\n";
	echo "open $path.clean\n";
	$handlet = @fopen("$path.clean", "w");
	$handle = @fopen("$path", "r");
	while (!feof($handle)){
		$d++;
		$buffer = fgets($handle, 4096);
		if($buffer==null){continue;}
		$old=$buffer;
		reset($newprefix);
		while (list ($prefix, $none) = each ($newprefix) ){
			if($prefix==null){continue;}
			if(strlen($prefix)<3){continue;}
			$buffer=str_replace($prefix.'-',"",$buffer);
			$buffer=str_replace($prefix.'.',"",$buffer);
			$buffer=str_replace("$prefix","",$buffer);
			
			
		}
		if(substr($buffer,0,1)=='@'){
			echo "skip $old -> $buffer after ". count($newprefix) ." prefix\n";
		}
		echo "write $old -> $buffer\n";
		@fwrite($handlet,$buffer."\n");
		
		
			if($d>10000){
					$dixmille=$dixmille+10000;
					echo "ok $dixmille\n";
					$d=0;
				}		
		
		
	}
@fclose($handle);
@fclose($handlet);
	
}


$pid=getmypid();
echo "[$pid]::master Running pid $pid\n";
file_put_contents($pidfile,$pid);

$unix=new unix();
$nohup=$unix->find_program("nohup");

$q=new mysql();
$sql="SELECT ID FROM emailing_db_paths WHERE finish=0 ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo "[$pid]::master $q->mysql_error\n";
	}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$id=$ligne["ID"];
			$cmd="$nohup ". LOCATE_PHP5_BIN2()." ". __FILE__." --id $id &";
			echo "[$pid]::master $cmd\n";
			system($cmd);
	}

echo "[$pid]::master halt $pid\n";
	
function parse_db($id){
	
$unix=new unix();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".$id.pid";
$pid=trim(@file_get_contents($pidfile));
if($unix->process_exists($pid)){
	$pid=getmypid();	
	echo "[$pid]:: Process $pid already running...\n";
	die();
}	
$pid=getmypid();	
file_put_contents($pidfile,$pid);
$sql="SELECT * FROM emailing_db_paths WHERE ID=$id";
$q=new mysql();
$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));


$unzipbin=$unix->find_program("unzip");
if($unzipbin==null){
	echo "[$pid]:: unzip tool no such file or directory...\n";
	update_status($id,"110",1,"unzip tool no such file or directory");
	die();
}

$zip_path=$ligne["filepath"];
if(!is_file($zip_path)){
	echo "[$pid]:: $zip_path no such file or directory\n";
	update_status($id,"110",1,"zip db no such file or directory");
	die();
}

$tmp_path="/tmp/emailing-import/$id";
writeevent("using $tmp_path",$id);

@mkdir($tmp_path,666,true);
writeevent("Uncompress $zip_path",$id);
echo "$unzipbin -o $zip_path -d $tmp_path\n";
shell_exec("$unzipbin -o $zip_path -d $tmp_path");


$files=$unix->DirFiles($tmp_path);
if(!is_array($files)){
	update_status($id,"110",1,"$zip_path corrupted or no files stored");
	die();	
}

while (list ($filename, $file_name) = each ($files)){
	writeevent("parsing $file_name",$pid);
	$max=$unix->COUNT_LINES_OF_FILE("$tmp_path/$file_name");
	writeevent("parsing $file_name $max entries",$pid);
	$handle = @fopen("$tmp_path/$file_name", "r");
	$ligne["databasename"]=format_mysql_table($ligne["databasename"]);
	$q=new mysql();
	$q->CheckTableEmailingContacts("emailing_{$ligne["databasename"]}");
	$sql="INSERT INTO emailing_{$ligne["databasename"]} (`gender`,`firstname`, `lastname`,`email`,`phone`,`city`,`cp`,`postaladdress`,`domain`) VALUES";
	if ($handle) {
			while (!feof($handle)){
				$tw=$tw+1;
				$count=$count+1;
				unset($re);
				$buffer = fgets($handle, 4096);
				if(trim($buffer)==null){continue;}
				$buffer=str_replace('"',"",$buffer);
				$buffer=str_replace(';',",",$buffer);
				$lines=explode(",",addslashes($buffer));
				if(!is_array($lines)){
					if(preg_match("#.+?@.+#",$buffer)){
						for($i=0;$i<8;$i++){$lines[$i]="";}
						$lines[3]=trim($buffer);
					}
				}
				
				if(count($lines)<2){
					if(preg_match("#.+?@.+#",$buffer)){
						for($i=0;$i<8;$i++){$lines[$i]="";}
						$lines[3]=trim($buffer);
					}
				}				

				$lines[3]=str_replace(";",".",$lines[3]);
				$lines[3]=str_replace("?",".",$lines[3]);
				$lines[3]=str_replace("@.","@",$lines[3]);
				$lines[3]=str_replace('^','@',$lines[3]);
				$lines[3]=str_replace(',','.',$lines[3]);
				
				if(trim($lines[3])==null){
					writeevent("failed 3:[$buffer] [".__LINE__."]",$pid);
					$GLOBALS["FAILED_CONTACTS"]=$GLOBALS["FAILED_CONTACTS"]+1;
    				unset($lines);
    				continue;	
				}				
				
				
			  	if(!preg_match("#(.+?)@(.+?)\.(.+)#",$lines[3],$re)){
    				writeevent("failed 3:{$lines[3]} bad email address [".__LINE__."]",$pid);
    				$GLOBALS["FAILED_CONTACTS"]=$GLOBALS["FAILED_CONTACTS"]+1;
    				unset($lines);
    				continue;
    			}
    			
    			
    			$domain=$re[2];
    			if(substr($domain,strlen($domain)-1,1)=='.'){$domain=substr($domain,0,strlen($domain)-1);}
    			if(preg_match("#^\..+#",$domain)){
    				writeevent("failed 3:{$lines[3]} bad domain $domain [".__LINE__."]",$pid);
    			   	$GLOBALS["FAILED_CONTACTS"]=$GLOBALS["FAILED_CONTACTS"]+1;
    				unset($lines);
    				continue;	
    			}
    			
    			
				if($GLOBALS["EMAILS"][$lines[3]]){
					writeevent("failed 3:{$lines[3]} already exists [".__LINE__."]",$pid);
					$GLOBALS["FAILED_CONTACTS"]=$GLOBALS["FAILED_CONTACTS"]+1;
					continue;
				}   

				if(isBlockedMail($lines[3])){
					writeevent("failed 3:{$lines[3]} is blacklisted [".__LINE__."]",$pid);
					$GLOBALS["FAILED_CONTACTS"]=$GLOBALS["FAILED_CONTACTS"]+1;
					continue;					
				}
    									
				$sqla[]="('{$lines[0]}',
				'{$lines[1]}',
				'{$lines[2]}',
				'{$lines[3]}',
				'{$lines[4]}',
				'{$lines[5]}',
				'{$lines[6]}',
				'{$lines[7]}','$domain')";
				
				$GLOBALS["SUCCESS_CONTACTS"]=$GLOBALS["SUCCESS_CONTACTS"]+1;
    			$GLOBALS["EMAILS"][$lines[3]]=true;
    			if(count($GLOBALS["EMAILS"])>10000){$GLOBALS["EMAILS"]=array();}
    			unset($lines);
    
				if($tw>100){
					$fullsql=$sql."\n".@implode(",",$sqla);
					$q=new mysql();
					$q->QUERY_SQL($fullsql,"artica_backup");
					if(!$q->ok){writeevent($id,"$q->mysql_error\n$fullsql");}
					unset($sqla);
					
					$purc=$count/$max;
					$purc=$purc*100;
					$purc=round($purc,0);
					update_status($id,$purc,0);
					$tw=0;
				}
				
				
			}
			
			
	fclose($handle);
		
	if(is_array($sqla)){
		$fullsql=$sql."\n".@implode(",",$sqla);
		$q->QUERY_SQL($fullsql,"artica_backup");
		if(!$q->ok){
			writeevent($id,"$q->mysql_error\n$fullsql");
			}
	}	
	
	}

	
}

if($GLOBALS["SUCCESS_CONTACTS"]>0){
	writeevent("Failed.:{$GLOBALS["FAILED_CONTACTS"]}",$id);
	writeevent("Success:{$GLOBALS["SUCCESS_CONTACTS"]}",$id);
	update_status($id,100,1,null);
}else{
	update_status($id,110,0,null);
}

	
}

function isBlockedMail($email){
	$q=new mysql();
	$email=trim(strtolower($email));
	$sql="SELECT email FROM emailing_campain_blacklist WHERE email='$email'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["email"]<>null){return true;}
	return false;
}


function update_status($id,$pourc,$finish,$text=null){
	if($text==null){$text=addslashes(@implode("\n",$GLOBALS["l"]));}else{
		if(is_array($GLOBALS["l"])){
			$GLOBALS["l"][]=$text;
			$text=addslashes(@implode("\n",$GLOBALS["l"]));
		}
	}
	echo "[$pid] ".date('H:i:s')." ($id) $pourc%\n";
	$sql="UPDATE emailing_db_paths SET progress='$pourc', finish='$finish',reports_import='$text' WHERE ID='$id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
}

function writeevent($text,$pid){
	echo "[$pid] ".date('H:i:s')." $text\n";
	$GLOBALS["l"][]="[$pid] ".date('H:i:s')." $text";
}


function make_database_unique($ID){
	
		$unix=new unix();
		$pidfile="/etc/artica-postfix/".basename(__FILE__).".make_database_unique.$ID.pid";
		$pid=trim(@file_get_contents($pidfile));
		if($unix->process_exists($pid)){
			$pid=getmypid();echo "[$pid]:: Process $pid already running...\n";
			update_status($ID,100,1,"Cleaning operation stopped Process $pid already running");
			die();
		}
		
		$pid=getmypid();
		echo "[$pid]::master Running pid $pid\n";
		file_put_contents($pidfile,$pid);	
	
	writeevent("Starting make unique Database ID $ID",$pid);
	if(!is_numeric($ID)){
		update_status($ID,100,1,"Error ID is not an integer");
		return;
	}
	
	$sql="SELECT * FROM emailing_db_paths WHERE ID=$ID and merged=0";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$databasename=$ligne["databasename"];
	$ou=$ligne["ou"];

	writeevent("Starting make unique Database ID $ID $ou/$databasename",$pid);
	
	
	$sql="SELECT databasename FROM emailing_db_paths WHERE ou='$ou'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		update_status($ID,100,1,"Error $q->mysql_error while get all databases ou=$ou");
		return;
	}	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($ligne["databasename"]==$databasename){continue;}
		$DBS["emailing_{$ligne["databasename"]}"]="emailing_{$ligne["databasename"]}";
		
	}
	
	writeevent("Starting cleaning " .@implode("\n",$DBS)." database(s)",$pid);
	if(count($DBS)==0){
		update_status($ID,100,1,"Error unable to find databases for $ou");
		return;
	}
	
	$sql="SELECT email FROM emailing_{$databasename}";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		update_status($ID,100,1,"Error $q->mysql_error while get all emails from  $databasename");
		return;
	}	

	$max=mysql_num_rows($results);
	writeevent("Starting parsing  $max emails",$pid);
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$email=$ligne["email"];
		while (list ($tablename, $none) = each ($DBS) ){
			$q->ok=true;
			$q->QUERY_SQL("DELETE FROM $tablename WHERE email='$email'","artica_backup");
			if(!$q->ok){
				writeevent("$email -> $q->mysql_error",$pid);
			}
			
			usleep(500);
		}
		usleep(500);
		reset($DBS);
		
	}
	
	update_status($ID,100,1,"Cleaning operation success from  $databasename $max emails addresses");
	
	
}


function import_users($id){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=trim(@file_get_contents($pidfile));
	if($unix->process_exists($pid)){echo "[".getmypid()."]:: Process $pid already running...\n";die();}

	$pid=getmypid();
	@file_put_contents($pidfile,$pid);

	$emailing=new emailings($id);
	if($emailing->error){echo __FUNCTION__." class error: $emailing->mysql_error\n";exit;}
	
	
	$sql="SELECT * FROM $emailing->tablename";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo __FUNCTION__." $q->mysql_error\n";exit;}
	
	$max=mysql_num_rows($results);
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table($ou);
	while (list ($domain, $no) = each ($domains) ){
		$DOMAINS_ARRAY[$domain]=true;
	}
	
	
	if($emailing->array_options["export_domain"]==null){
		update_export_status($id,110,"No default domain set");
		return;
	}
	
	if($emailing->array_options["gpid"]==null){
		update_export_status($id,110,"No default group set");
		return;
	}	
	
	$gpid=$emailing->array_options["gpid"];
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$count=$count+1;
		$tw=$tw+1;
		$firstname=$ligne["firstname"];
		$lastname=$ligne["lastname"];
		$email=$ligne["email"];
		$phone=$ligne["phone"];
		$city=$ligne["city"];
		$cp=$ligne["cp"];
		$postaladdress=$ligne["postaladdress"];
		
		if(!preg_match("#(.+?)@#",$email,$re)){continue;}
		$uid=$re[1];
		$new_email="$uid@{$emailing->array_options["export_domain"]}";
		$ct=new user($uid);
		if(!$ct->DoesNotExists){
			$GLOBALS["SUCCESS_CONTACTS"]=$GLOBALS["SUCCESS_CONTACTS"]+1;
			echo "$emailing->ou/$uid::$new_email $firstname $lastname already exists [SUCCESS]\n";	
		}
		$ct->mail=$new_email;
		$ct->postalCode=$cp;
		$ct->postalAddress=$postaladdress;
		$ct->town=$city;
		$ct->telephoneNumber=$phone;
		$ct->DisplayName="$firstname $lastname";
		$ct->sn=$lastname;
		$ct->givenName=$firstname;
		$ct->group_id=$gpid;
		$ct->ou=$emailing->ou;
		$ct->password=$emailing->array_options["export_default_password"];
		
		
		if($tw>2){
			$purc=$count/$max;
			$purc=$purc*100;
			$purc=round($purc,0);
			update_export_status($id,$purc);
			$tw=0;
		}		
		
		
		if(!$ct->add_user()){
			writeevent("$emailing->ou/$uid::$new_email $firstname $lastname [FAILED]");
			$GLOBALS["FAILED_CONTACTS"]=$GLOBALS["FAILED_CONTACTS"]+1;continue;
		}
		
		$GLOBALS["SUCCESS_CONTACTS"]=$GLOBALS["SUCCESS_CONTACTS"]+1;
			writeevent("$emailing->ou/$uid::$new_email $firstname $lastname [SUCCESS]");
		if($new_email<>$email){
				$user=new user($uid);
				$user->add_alias($email);
		}
		
			 	
		
	}
	
	writeevent("Failed.:{$GLOBALS["FAILED_CONTACTS"]}",$id);
	writeevent("Success:{$GLOBALS["SUCCESS_CONTACTS"]}",$id);
	update_status($id,100,1,null);	
	
}

function update_export_status($id,$pourc,$text=null){
	if($text==null){$text=addslashes(@implode("\n",$GLOBALS["l"]));}else{
		if(is_array($GLOBALS["l"])){
			$GLOBALS["l"][]=$text;
			$text=addslashes(@implode("\n",$GLOBALS["l"]));
		}
	}
	echo date('H:i:s')." ($id) $pourc% $text\n";
	$sql="UPDATE emailing_db_paths SET progress_export='$pourc',reports_import='$text' WHERE ID='$id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
}



	
?>
