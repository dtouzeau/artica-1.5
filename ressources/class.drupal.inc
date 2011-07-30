<?php


class drupal_vhosts{
	var $servername;
	var $groupware;
	var $ou;
	var $www_dir;
	var $ServerPort;
	var $drupalDatabase;
	var $DrupalModules=array();
	var $drush7;
	
	function drupal_vhosts($hostname){
		if($hostname<>null){
			$this->servername=$hostname;
			$this->load();
			$unix=new unix();
			$this->drush7=$unix->find_program("drush7");
			if(!is_file($this->drush7)){die();}
		}	
		
		//cp -rf /usr/share/drupal7/* /var/www/drupal.touzeau.com/
		
	}
	
	private function Load(){
			$sql="SELECT * from freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			$this->groupware=$ligne["groupware"];
			$this->servername=$ligne["servername"];
			$this->ou=$ligne["ou"];
			$this->www_dir=$ligne["www_dir"];
			$this->ServerPort=$ligne["ServerPort"];
			if($this->www_dir==null){$this->www_dir="/var/www/$this->servername";}
			$this->drupalDatabase="drupal_".time();
			if($ligne["mysql_database"]<>null){$this->drupalDatabase=$ligne["mysql_database"];}else{
				$sql="UPDATE freeweb SET mysql_database='{$this->drupalDatabase}' WHERE servername='$this->servername'";
				$q->QUERY_SQL($sql,"artica_backup");
			}
			$this->DrupalModules=unserialize(base64_decode($ligne["DrupalModules"]));
	}
	
	public function install(){
		if($this->groupware<>"DRUPAL"){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] this is not a drupal groupware");return;}
		$q=new mysql();
		if(!$q->DATABASE_EXISTS($this->drupalDatabase)){
			$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] creating database \"$this->drupalDatabase\"");
			$q->CREATE_DATABASE($this->drupalDatabase);
			if(!$q->ok){
				$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] creating database \"$this->drupalDatabase\" FAILED");
				$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] $q->mysql_error");
				return;
			}
		}else{
			$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] database \"$this->drupalDatabase\" ok");
		}
		
		if($this->TestsInstall()){
			$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] core is installed");
			$array=$this->DrupalStatusArray();
			if(!isset($array["SITE_PATH"])){
				$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] configuring Drupal...");
				$this->DrushInstall();
			}
			return;
		}
		$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] installing Drupal");
		shell_exec("/bin/cp -rf /usr/share/drupal7/* $this->www_dir/");
		if(!$this->TestsInstall()){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] installing Drupal FAILED !");return;}
		$this->DrushInstall();
		$this->populate_infos();
		
		
	}
	
	public function DrushInstall(){
		$q=new mysql();
		chdir($this->www_dir);
		$cmd="$this->drush7 --uri=$this->servername site-install standard --db-url=mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server/$this->drupalDatabase -y 2>&1";
		exec($cmd,$results);
		while (list ($num, $ligne) = each ($results) ){
			$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] $ligne");
		}
	}
	
	
	private function DrupalStatusArray(){
	chdir($this->www_dir);
		exec("$this->drush7 status 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#(.+?)\s+:\s+(.+)#", $ligne,$re)){
				$re[1]=trim($re[1]);
				$re[1]=str_replace(" ", "_", $re[1]);
				$re[1]=strtoupper($re[1]);
				$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] {$re[1]} = {$re[2]}");
				$status[$re[1]]=trim($re[2]);
			}
		}
		$status["UPDATED"]=date("Y-m-d H:i:s");
		if(!isset($status["DATABASE_USERNAME"])){
			if(!isset($GLOBALS["INSTALL_EXECUTED"])){
				$GLOBALS["INSTALL_EXECUTED"]=true;
				$this->install();
			}
			
		}
		unset($status["DATABASE_USERNAME"]);
		return $status;
		
	}
	
	private function DrupalUsersInfos(){
		chdir($this->www_dir);
		
		$sql="SELECT name FROM users";
		$q=new mysql();
		$zresults=$q->QUERY_SQL($sql,$this->drupalDatabase);
		while($zligne=mysql_fetch_array($zresults,MYSQL_ASSOC)){
		
		if($GLOBALS["VERBOSE"]){$this->echo2("$this->drush7  user-information {$zligne["name"]} --full 2>&1");}
		exec("$this->drush7  user-information {$zligne["name"]} --full 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#php: Warning:#",$ligne)){continue;}
			if($GLOBALS["VERBOSE"]){$this->echo2("[DEBUG] [DRUPAL]: user-information \"$ligne\"");}
			if(preg_match("#(.+?):(.*)#", $ligne,$re)){
				$re[1]=trim($re[1]);
				$re[2]=trim($re[2]);
				$re[1]=str_replace(" ", "_", $re[1]);
				$re[1]=strtoupper($re[1]);
				if($re[1]=="UID"){$uid=$re[2];$array[$uid]["INFOS"]=$this->UserInfosSingle($zligne["name"]); }
				$array[$uid][$re[1]]=$re[2];
				
				}
			}
		
		}

		return $array;
		
	}
	

	
	function dump_modules($noinstall=false){
		chdir($this->www_dir);
		
		$cmd="$this->drush7 --root=$this->www_dir cron 2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);		
		
		
		$cmd="$this->drush7 pm-refresh 2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);		
		
		
		$cmd="$this->drush7 pm-list 2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);
		while (list ($num, $ligne) = each ($results) ){	
			if(preg_match("#^(.+)\s+(.+?)\s+\((.+?)\)\s+(.+?)\s+(.+?)\s+(.+)#", $ligne,$re)){
				$version=$re[6];
				if(preg_match("#installed\s+(.+)#",$version, $ri)){$version=$ri[1];}
				if($GLOBALS["VERBOSE"]){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] {$re[3]} -> $version");}
				$modules[$re[3]]=array("NAME"=>$re[1],"VERSION"=>$version);
				continue;
			}
			
			if(preg_match("#^\s+\((.+)\)#",$ligne,$re)){
				$modules[$re[1]]=array("NAME"=>$re[1],"VERSION"=>0);
				continue;
			}
			
		if($GLOBALS["VERBOSE"]){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] $ligne No match");}
			
		}	
		
		$final=addslashes(base64_encode(serialize($modules)));
		$sql="UPDATE freeweb SET DrupalModules='$final' WHERE servername='$this->servername'";
		$q=new mysql();
		$q->BuildTables();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$noinstall){
			$unix=new unix();
			$nohup=$unix->find_program("nohup");
			$cmd=$nohup." ".$unix->LOCATE_APACHE_BIN_PATH()." /usr/share/artica-postfix/exec.freeweb.php --drupal-modules-install $this->servername >/dev/null 2>&1 &";
			shell_exec(trim($cmd));
		}				
		
		
	}
	
	function install_modules(){
		$array["fusion_core"]=true;
		$array["fusion_starter"]=true;
		$array["fusion_starter_lite"]=true;
		$array["acquia_marina-7.x-1.0-rc3"]=true;
		$array["marinelli"]=true;
		$array["skinr"]=true;
		$array["advanced_help"]=true;
		$array["tao"]=true;
		$array["rubik"]=true;
		$array["danland"]=true;
		//$array["cti_flex"]=true;
		$array["tarski"]=true;
		$array["zen"]=true;
		$array["view"]=true;
		$array["date"]=true;
		$array["wysiwyg"]=true;
		$array["ckeditor"]=true;
		$array["cck-7.x-2.x-dev"]=true;
		$array["ctools"]=true;
		$array["link"]=true;
		$array["image"]=true;
		$array["panels"]=true;
		$array["calendar"]=true;
		$array["admin"]=true;
		$array["andromeda"]=true;
		$array["block_class-7.x-1.x"]=true;
		$array["nice_menus-7.x-2.x"]=true;
		if(!is_array($this->DrupalModules)){return null;}
		if(count($this->DrupalModules)<2){return null;}
		
			if($GLOBALS["VERBOSE"]){
			while (list ($module, $num) = each ($this->DrupalModules) ){
				$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] $module installed");
				
			}
			reset($this->DrupalModules);
		}		
		
		$install=false;
		while (list ($module, $num) = each ($array) ){
			$module_check=$module;
			if(preg_match("#^(.+?)\-[0-9]+#", $module,$re)){$module_check=$re[1];}
			if(!isset($this->DrupalModules[$module_check])){
				$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] $module/$module_check is not installed");
				chdir($this->www_dir);
				$cmd="$this->drush7 pm-download $module -y 2>&1";
				$this->echo2($cmd);
				$install=true;
				exec($cmd,$results);
				while (list ($num, $ligne) = each ($results) ){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] \"$ligne\"");}
			}
		}
		
		if($install){
			chdir($this->www_dir);
			$cmd="$this->drush7 pm-update 2>&1";
			$this->echo2($cmd);
			$this->dump_modules(true);
		
		}
		
	}
	
	
	function add_user($uid,$password=null){
		$mail=null;
		if($password==null){
			$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] load user $uid");
			$u=new user($uid);
			$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] loaded user $uid ok");
			if(strlen($u->password)==0){if(!$q->ok){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] user $uid does not exists in LDAP Database");}return;}
			$password=$u->password;
			$mail=$u->mail;
		}
		if($mail<>null){$mail=" --mail=\"$u->mail\"";}
		chdir($this->www_dir);
		$cmd="$this->drush7 user-create $uid$mail --password=\"$password\" 2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);
		while (list ($num, $ligne) = each ($results) ){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] \"$ligne\"");}
		$this->populate_infos();
	}
	
	function active_user($uid,$enable){
		chdir($this->www_dir);
		$cmdoff="$this->drush7 user-block $uid 2>&1";
		$cmdon="$this->drush7 user-unblock $uid  2>&1";
		$cmd=$cmdon;
		if($enable==0){$cmd=$cmdoff;}
		$this->echo2($cmd);
		exec($cmd,$results);
		while (list ($num, $ligne) = each ($results) ){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] \"$ligne\"");}
		$this->populate_infos();		
	}
	
	function priv_user($uid,$priv){
		chdir($this->www_dir);
		if($GLOBALS["VERBOSE"]){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] priv_user() $uid -> $priv");}
		
		$cmd="$this->drush7 user-remove-role \"administrator\" $uid  2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);
		$cmd="$this->drush7 user-remove-role \"authenticated user\" $uid  2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);
		
		if($priv=="administrator"){
			$cmd="$this->drush7 user-add-role \"administrator\" $uid  2>&1";
			$this->echo2($cmd);
			exec($cmd,$results);
			while (list ($num, $ligne) = each ($results) ){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] \"$ligne\"");}
			$this->populate_infos();
			return;
		}
		
		if($priv=="user"){
			$cmd="$this->drush7 user-add-role \"authenticated user\" $uid  2>&1";
			$this->echo2($cmd);
			exec($cmd,$results);
			while (list ($num, $ligne) = each ($results) ){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] \"$ligne\"");}
			$this->populate_infos();
			return;
		}		
		
		$cmd="$this->drush7 user-add-role \"anonymous user\" $uid  2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);
		while (list ($num, $ligne) = each ($results) ){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] \"$ligne\"");}
		$this->populate_infos();
		return;		
		$this->populate_infos();
		return;
		
		
	}
	
	function del_user($uid){
		chdir($this->www_dir);
		$cmd="$this->drush7 user-cancel \"$uid\" -y 2>&1";
		$this->echo2($cmd);
		exec($cmd,$results);
		while (list ($num, $ligne) = each ($results) ){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] \"$ligne\"");}
		$this->populate_infos();		
		
	}

	private function UserInfosSingle($uid){
		chdir($this->www_dir);
		if($GLOBALS["VERBOSE"]){$this->echo2("$this->drush7  user-information $uid 2>&1");}
		exec("$this->drush7  user-information $uid 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#php: Warning:#",$ligne)){continue;}
			if($GLOBALS["VERBOSE"]){$this->echo2("[DEBUG] [DRUPAL]: user-information \"$ligne\"");}
			if(preg_match("#(.+?):(.*)#", $ligne,$re)){
				$re[1]=trim($re[1]);
				$re[2]=trim($re[2]);
				$re[1]=str_replace(" ", "_", $re[1]);
				$re[1]=strtoupper($re[1]);
				
				$array[$re[1]]=$re[2];
				}
		}

		return $array;	
		
	}
	
	public function populate_infos(){
		$global_status=$this->DrupalStatusArray();
		$users=$this->DrupalUsersInfos();
		$array["GLOBAL_STATUS"]=$global_status;
		$array["USERS"]=$users;
		$final=addslashes(base64_encode(serialize($array)));
		$sql="UPDATE freeweb SET DrupalInfos='$final' WHERE servername='$this->servername'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] populated infos failed");return;}
		$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] populated infos done.");
		$unix=new unix();
		$nohup=$unix->find_program("nohup");
		$cmd=$nohup." ".$unix->LOCATE_APACHE_BIN_PATH()." /usr/share/artica-postfix/exec.freeweb.php --drupal-modules $this->servername >/dev/null 2>&1 &";
		shell_exec(trim($cmd));
	}
	
	
	private function TestsInstall(){
			if(!is_dir($this->www_dir)){
				$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] $this->www_dir no such directory");
				return false;
			}
			$f[]="web.config";
			$f[]="index.php";
			$f[]="update.php";
			$f[]="scripts/cron-curl.sh";
			$f[]="scripts/drupal.sh";
			$f[]="scripts/cron-lynx.sh";
			$f[]="scripts/code-clean.sh";
			$f[]="scripts/run-tests.sh";
			$f[]="scripts/password-hash.sh";
			$f[]="scripts/dump-database-d6.sh";
			$f[]="scripts/generate-d6-content.sh";
			$f[]="authorize.php";
			$f[]="includes/unicode.inc";
			$f[]="includes/xmlrpc.inc";
			$f[]="includes/theme.maintenance.inc";
			$f[]="includes/unicode.entities.inc";
			$f[]="includes/locale.inc";
			$f[]="includes/xmlrpcs.inc";
			$f[]="includes/path.inc";
			$f[]="includes/entity.inc";
			$f[]="includes/date.inc";
			$f[]="includes/bootstrap.inc";
			$f[]="includes/authorize.inc";
			$f[]="includes/file.inc";
			$f[]="includes/password.inc";
			$f[]="includes/lock.inc";
			$f[]="includes/batch.inc";
			$f[]="includes/errors.inc";
			$f[]="includes/actions.inc";
			$f[]="includes/install.inc";
			$f[]="includes/batch.queue.inc";
			$f[]="includes/install.core.inc";
			$f[]="includes/module.inc";
			$f[]="includes/cache-install.inc";
			$f[]="includes/stream_wrappers.inc";
			$f[]="includes/archiver.inc";
			$f[]="includes/graph.inc";
			$f[]="includes/session.inc";
			$f[]="includes/token.inc";
			$f[]="includes/filetransfer";
			$f[]="includes/filetransfer/ftp.inc";
			$f[]="includes/filetransfer/filetransfer.inc";
			$f[]="includes/filetransfer/local.inc";
			$f[]="includes/filetransfer/ssh.inc";
			$f[]="includes/mail.inc";
			$f[]="includes/registry.inc";
			$f[]="includes/menu.inc";
			$f[]="includes/ajax.inc";
			$f[]="includes/pager.inc";
			$f[]="includes/common.inc";
			$f[]="includes/update.inc";
			$f[]="includes/theme.inc";
			$f[]="includes/language.inc";
			$f[]="includes/file.mimetypes.inc";
			$f[]="includes/iso.inc";
			$f[]="includes/image.inc";
			$f[]="includes/utility.inc";
			$f[]="includes/tablesort.inc";
			$f[]="includes/updater.inc";
			$f[]="includes/form.inc";
			$f[]="includes/database/database.inc";
			$f[]="includes/database/sqlite/database.inc";
			$f[]="includes/database/sqlite/select.inc";
			$f[]="includes/database/sqlite/query.inc";
			$f[]="includes/database/sqlite/install.inc";
			$f[]="includes/database/sqlite/schema.inc";
			$f[]="includes/database/select.inc";
			$f[]="includes/database/query.inc";
			$f[]="includes/database/mysql/database.inc";
			$f[]="includes/database/mysql/query.inc";
			$f[]="includes/database/mysql/install.inc";
			$f[]="includes/database/mysql/schema.inc";
			$f[]="includes/database/schema.inc";
			$f[]="includes/database/log.inc";
			$f[]="includes/database/pgsql/database.inc";
			$f[]="includes/database/pgsql/select.inc";
			$f[]="includes/database/pgsql/query.inc";
			$f[]="includes/database/pgsql/install.inc";
			$f[]="includes/database/pgsql/schema.inc";
			$f[]="includes/database/prefetch.inc";
			$f[]="includes/cache.inc";
			$f[]="profiles/minimal/minimal.install";
			$f[]="profiles/minimal/minimal.profile";
			$f[]="profiles/minimal/minimal.info";
			$f[]="profiles/standard/standard.install";
			$f[]="profiles/standard/standard.profile";
			$f[]="profiles/standard/standard.info";
			$f[]="profiles/standard/translations";
			$f[]="profiles/testing/testing.profile";
			$f[]="profiles/testing/testing.info";
			$f[]="profiles/testing/testing.install";	

			
			while (list ($num, $ligne) = each ($f) ){
				if(is_dir($this->www_dir."/".$ligne)){continue;}
				if(!is_file($this->www_dir."/".$ligne)){
					$this->echo2("Starting......: Apache \"$this->servername\" [DRUPAL] ".$this->www_dir."/".$ligne ." no such file...");
					return false;
				}
			}
		return true;
	}
	
	private function echo2($line){
		echo $line."\n";
		if($GLOBALS["VERBOSE"]){return;}
		writelogs($line,__CLASS__.'/'.__FUNCTION__,__FILE__,0);
		
	}
	
	
}