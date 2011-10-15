<?php
include_once(dirname(__FILE__)."/class.mysql.inc");
include_once(dirname(__FILE__)."/class.groups.inc");

class compile_ufdbguard{
	var $SquidGuardIPWeb;
	
	
	function compile_ufdbguard(){
		$sock=new sockets();
		$usrs=new usersMenus();
		$this->SquidGuardIPWeb=$sock->GET_INFO("SquidGuardIPWeb");
		if($this->SquidGuardIPWeb==null){$this->SquidGuardIPWeb="http://$usrs->hostname:9020/exec.squidguard.php";}
		$this->SquidGuardIPWeb=str_replace('.(none)',"",$this->SquidGuardIPWeb);
	}
	
	
	public function buildConfig(){
		$sock=new sockets();
		$sources=$this->build_sources();
		$categories=$this->build_categories();
		$acls=$this->build_acls();
		$conf[]="dbhome /var/lib/squidguard";
		$conf[]="logdir /var/log/squid";
	
		$ufdbguardConfig=unserialize(base64_decode($sock->GET_INFO("ufdbguardConfig")));
		if(!isset($ufdbguardConfig["enforce-https-with-hostname"])){$ufdbguardConfig["enforce-https-with-hostname"]=0;}
		if(!isset($ufdbguardConfig["enforce-https-official-certificate"])){$ufdbguardConfig["enforce-https-official-certificate"]=0;}
		if(!isset($ufdbguardConfig["https-prohibit-insecure-sslv2"])){$ufdbguardConfig["https-prohibit-insecure-sslv2"]=0;}
		
		if($ufdbguardConfig["enforce-https-with-hostname"]==null){$ufdbguardConfig["enforce-https-with-hostname"]=0;}
		if($ufdbguardConfig["enforce-https-official-certificate"]==null){$ufdbguardConfig["enforce-https-official-certificate"]=0;}
		if($ufdbguardConfig["https-prohibit-insecure-sslv2"]==null){$ufdbguardConfig["https-prohibit-insecure-sslv2"]=0;}			
			
		while (list ($key, $line) = each ($ufdbguardConfig) ){
				if($line==0){$ufdbguardConfig[$key]="off";}
				if($line==1){$ufdbguardConfig[$key]="on";}
				if($line==null){$ufdbguardConfig[$key]="off";}
			}
			
		$conf[]="logblock on";
		$conf[]="logall on";
		$conf[]="max-logfile-size  20000000";
		$conf[]="analyse-uncategorised-urls on";
		$conf[]="check-proxy-tunnels queue-checks";
		$conf[]="safe-search on";
		$conf[]="#SSL enforce:";
		$conf[]="enforce-https-with-hostname {$ufdbguardConfig["enforce-https-with-hostname"]}";
		$conf[]="enforce-https-official-certificate {$ufdbguardConfig["enforce-https-official-certificate"]}";
		$conf[]="https-prohibit-insecure-sslv2 {$ufdbguardConfig["https-prohibit-insecure-sslv2"]}";
		$conf[]="";
 		$conf[]=$sources;
 		$conf[]=$categories;
 		$conf[]=$acls;				
		
 		return @implode("\n", $conf);
		
	}
	
	
	private function build_sources(){
		$q=new mysql_squid_builder();
		$sql="SELECT * FROM webfilter_group WHERE enabled=1";
		$results=$q->QUERY_SQL($sql);
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$GLOBALS["SRC"][$ligne["ID"]]=$ligne["groupname"];
		$groupname=$ligne["groupname"];
		$array[]="source \"$groupname\" {";
		if($GLOBALS["VERBOSE"]){writelogs("$groupname LDAP?={$ligne["localldap"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		if($ligne["localldap"]==0){
			$gp=new groups($ligne["gpid"]);
			while (list ($num, $member) = each ($gp->members)){
				if(trim($member)==null){continue;}
				if(strpos($member, "$")>0){continue;}
				$array[]="\tuser\t\"$member\"";
				
			}
		}
		if($GLOBALS["VERBOSE"]){writelogs("$groupname -> webfilter_members -> {$ligne["ID"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		$sql="SELECT * FROM webfilter_members WHERE enabled=1 AND groupid={$ligne["ID"]}";
		$results2=$q->QUERY_SQL($sql);
		while($ligne2=mysql_fetch_array($results2,MYSQL_ASSOC)){
			if(trim($ligne2["pattern"])==null){continue;}
			if($GLOBALS["VERBOSE"]){writelogs("$groupname {$ligne2["pattern"]}={$ligne2["membertype"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			if($ligne2["membertype"]==0){$array[]="\tip\t{$ligne2["pattern"]}";}
			if($ligne2["membertype"]==2){$array[]="\tip\t{$ligne2["pattern"]}";}
			if($ligne2["membertype"]==1){$array[]="\tuser\t\"{$ligne2["pattern"]}\"";}			
		}
		
		$array[]="}";
		$array[]="";
		return @implode("\n", $array);
	}
		
		
		}
		
	function build_categories(){
		$q=new mysql_squid_builder();
		$main_path="/var/lib/squidguard";
		$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'category_%'";
		$results=$q->QUERY_SQL($sql);
		$sock=new sockets();
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$CountDeMembers=0;
			$table=$ligne["c"];
			if(!preg_match("#^category_(.+)#", $table,$re)){continue;}
			$categoryname=$re[1];
			if(!is_file("$main_path/$categoryname/domains")){
				$GLOBALS["CATEGORIES_SKIPPED"][$categoryname]=true;
				ufdbguard_admin_events("skip category $categoryname $main_path/$categoryname/domains no such file",__FUNCTION__,__FILE__,__LINE__,"config");
				continue;
			}
			if(!is_file("$main_path/$categoryname/domains.ufdb")){
				$GLOBALS["CATEGORIES_SKIPPED"][$categoryname]=true;
				ufdbguard_admin_events("skip category $categoryname $main_path/$categoryname/domains.ufdb no such file",__FUNCTION__,__FILE__,__LINE__,"config");
				continue;
			}
			
			$size=@filesize("$main_path/$categoryname/domains");
			if($size<35){
				$GLOBALS["CATEGORIES_SKIPPED"][$categoryname]=true;
				ufdbguard_admin_events("skip category $categoryname $main_path/$categoryname/domains $size bytes -> empty file",__FUNCTION__,__FILE__,__LINE__,"config");
				continue;
			}
			
			$GLOBALS["CATEGORIES_ADDED"][$categoryname]=true;
			$array[]="category \"$categoryname\" {";
			$array[]="\tdomainlist\t\"$categoryname/domains\"";
			$array[]="\texpressionlist\t\"$categoryname/expressions\"";
			$array[]="}\n";
			
			

					
			
			
	
	}
	
			$ufdbguardConfig=unserialize(base64_decode($sock->GET_INFO("ufdbguardConfig")));
			if($ufdbguardConfig["enforce-https-with-hostname"]==null){$ufdbguardConfig["enforce-https-with-hostname"]=0;}
			if($ufdbguardConfig["enforce-https-official-certificate"]==null){$ufdbguardConfig["enforce-https-official-certificate"]=0;}
			if($ufdbguardConfig["https-prohibit-insecure-sslv2"]==null){$ufdbguardConfig["https-prohibit-insecure-sslv2"]=0;}			
			
			while (list ($key, $line) = each ($ufdbguardConfig) ){
				if($line==0){$ufdbguardConfig[$key]="off";}
				if($line==1){$ufdbguardConfig[$key]="on";}
				if($line==null){$ufdbguardConfig[$key]="off";}
			}
			
			if($ufdbguardConfig["enforce-https-with-hostname"]=="on"){$add_ufdbgard_secu=true;}
			if($ufdbguardConfig["enforce-https-official-certificate"]=="on"){$add_ufdbgard_secu=true;}
			if($ufdbguardConfig["https-prohibit-insecure-sslv2"]=="on"){$add_ufdbgard_secu=true;}
			if($add_ufdbgard_secu){$GLOBALS["ADD_UFDBGUARD_SSL"]=true;}
			
			$array[]="\ncategory security {";
			$array[]="\toption enforce-https-with-hostname {$ufdbguardConfig["enforce-https-with-hostname"]}";
			$array[]="\toption enforce-https-official-certificate {$ufdbguardConfig["enforce-https-official-certificate"]}";
			$array[]="\toption https-prohibit-insecure-sslv2 {$ufdbguardConfig["https-prohibit-insecure-sslv2"]}";
			$array[]="}\n";	
	
	return @implode("\n", $array);
	
	

	}
	
	function build_acls(){
		$allows=array();
		$banns=array();		
		$q=new mysql_squid_builder();
		$sql="SELECT * FROM webfilter_group WHERE enabled=1";
		$redirect_pattern="clientaddr=%a&clientname=%n&clientuser=%i&clientgroup=%s&targetgroup=%t&url=%u";
		if(!preg_match("#^http.*?\/\/#",$this->SquidGuardIPWeb)){
			$this->SquidGuardIPWeb="https://$this->SquidGuardIPWeb";
			if(!preg_match("#squidguard\.php#",$this->SquidGuardIPWeb)){$this->SquidGuardIPWeb=$this->SquidGuardIPWeb."/exec.squidguard.php?$redirect_pattern";}
		}		
		
		$array[]="acl{";
		$sql="SELECT category,modeblk FROM webfilter_blks WHERE webfilter_id=0";
		$results=$q->QUERY_SQL($sql);
		echo "Starting......: ufdbGuard build ACLS for default rule ". mysql_num_rows($results)." items...\n";
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$category=trim($ligne["category"]);
			if($category==null){continue;}
			$category=str_replace("/", "_", $category);
			$category=str_replace("-", "_", $category);
			$category=str_replace(" ", "_", $category);
			if(isset($GLOBALS["CATEGORIES_SKIPPED"][$category])){continue;}
			if(!isset($GLOBALS["CATEGORIES_ADDED"][$category])){continue;}
			
			if($ligne["modeblk"]==0){$banns[]="!$category";}
			if($ligne["modeblk"]==1){$allows[]="$category";}			
			
			
		}
		
		$array[]="\t\"default\"{";
		$array[]="\t\tpass ".trim(@implode(" ", $allows)." ".@implode(" ", $banns))." all";
		$array[]="\t\tredirect $this->SquidGuardIPWeb";
		$array[]="\t}";			
		
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$GLOBALS["SRC"][$ligne["ID"]]=$ligne["groupname"];
			$groupname=$ligne["groupname"];
			$allows=array();
			$banns=array();
			$array[]="\t\"$groupname\"{";
			$sql="SELECT webfilter_blks.category,webfilter_blks.modeblk FROM webfilter_assoc_groups,webfilter_blks WHERE 
				webfilter_blks.webfilter_id=webfilter_assoc_groups.webfilter_id 
				AND webfilter_assoc_groups.group_id={$ligne["ID"]}";
				$results2=$q->QUERY_SQL($sql);
				if(!$q->ok){echo $q->mysql_error."\n$sql\n";}
				while($blks=mysql_fetch_array($results2,MYSQL_ASSOC)){
					if(isset($ARRAY["{$blks["modeblk"]}{$ligne["ID"]}{$blks["category"]}"])){continue;}
					$category=trim($blks["category"]);
					if($category==null){continue;}
					$category=str_replace("/", "_", $category);
					$category=str_replace("-", "_", $category);
					$category=str_replace(" ", "_", $category);
					if($blks["modeblk"]==0){$banns[]="!$category";}
					if($blks["modeblk"]==1){$allows[]="$category";}
					$ARRAY["{$blks["modeblk"]}{$ligne["ID"]}{$blks["category"]}"]=true;

					
				}
			
			$array[]="\t\tpass ".trim(@implode(" ", $allows)." ".@implode(" ", $banns))." all";
			$array[]="\t\tredirect $this->SquidGuardIPWeb?$redirect_pattern";
			$array[]="\t}";	
			
			
		}
		$array[]="}";
		return @implode("\n", $array);
		
		
	}
	
	function compile_category($category){
		$q=new mysql_squid_builder();
		$main_path="/var/lib/squidguard/$category";
		
		$tablename="category_$category";
		if(!$q->TABLE_EXISTS($tablename)){ufdbguard_admin_events("Failed to compile $category no such table",__FUNCTION__,__FILE__,__LINE__,"compile");return;}
		
		$sql="SELECT * FROM `$tablename` WHERE enabled=1";
		$q=new mysql_squid_builder();
		$unix=new unix();
		$t1=time();
		$results=$q->QUERY_SQL($sql);
		$mysqlquerry_took=$unix->distanceOfTimeInWords($t1,time(),true);
		if(!$q->ok){ufdbguard_admin_events("Failed to compile $category $q->mysql_error",__FUNCTION__,__FILE__,__LINE__,"compile");echo $q->mysql_error."\n";return ;}
		$num=mysql_num_rows($results);
		if($num==0){ufdbguard_admin_events("Failed to compile $category (no datas)",__FUNCTION__,__FILE__,__LINE__,"compile");return ;}
		
		
		@mkdir($main_path,0755,true);
		$fh = fopen($main_path."/domains", 'w');
		
		$skipped=0;
		$inserted=0;
		$t1=time();
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$datas=$ligne["pattern"];
			if($datas==null){$skipped++;continue;}
			if(!$this->VerifyDomainCompiledPattern($datas)){$skipped++;continue;}
			$inserted++;		
			fwrite($fh, "{$ligne["pattern"]}\n");
		}
		fclose($fh);
		$writetodisk_took=$unix->distanceOfTimeInWords($t1,time(),true);
		
		if(!is_file("$main_path/urls")){shell_exec("/bin/touch $main_path/urls");}
		$t1=time();
		$this->UfdbGenTable($main_path,$category);
		$compiledbs_took=$unix->distanceOfTimeInWords($t1,time(),true);
		ufdbguard_admin_events("$category compilation done\nWebsites:$inserted\nSQL took:$mysqlquerry_took\nWrite to disk took:$writetodisk_took\nUFDB Compilation took:$compiledbs_took",__FUNCTION__,__FILE__,__LINE__,"compile");
		
				
	}
	
	private function UfdbGenTable($directory,$category){
		$unix=new unix();
		$ufdbGenTable=$unix->find_program("ufdbGenTable");
		if(strlen($ufdbGenTable)<strlen("ufdbGenTable")){return;}
		$category_compile=$category;
		if(strlen($category_compile)>15){
			$category_compile=str_replace("recreation_","recre_",$category_compile);
			$category_compile=str_replace("automobile_","auto_",$category_compile);
			$category_compile=str_replace("finance_","fin_",$category_compile);
			if(strlen($category_compile)>15){
				$category_compile=str_replace("_", "", $category_compile);
				if(strlen($category_compile)>15){$category_compile=substr($category_compile, strlen($category_compile)-15,15);}
			}
		}	
		$u=" -u $directory/urls";
		$d=" -d $directory/domains";
		$EXEC_NICE=$unix->EXEC_NICE();
		$cmd="$EXEC_NICE$ufdbGenTable -n -D -W -t $category_compile$d$u";
		shell_exec($cmd);
		
		
	}
	

	private function VerifyDomainCompiledPattern($pattern){
		if(!preg_match("#^(.+?)\.(.+)#",$pattern)){return false;}
		
		return true;
	}	
	
	
}