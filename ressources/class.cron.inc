<?php

class cron_macros{
		var $cron_defined_macros=array(
	"2mn"=>"2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58 * * * *",	
	"5mn"=>"5,10,15,20,25,30,35,40,45,50,55 * * * *",
	"10mn"=>"10,20,30,40,50 * * * *",
	"20mn"=>"20,40 * * * *",
	"30mn"=>"30 * * * *",
	"1h=>@hourly",
	"2h"=>"0 2,4,6,8,10,12,14,16,18,20,22 * * *",
	"4h"=>"0 4,8,12,14,18,22 * * *",
	"8h"=>"0 8,16 * * *",
	"1d"=>"@daily",
	"2d"=>"0 0 2,4,6,8,10,12,14,16,18,20,22,24,26,28,30 * *",
	"4d"=>"0 0 4,8,12,14,18,22,26,30 * *");	
		
	var $cron_days=array(
		"*"=>"{all}",
		0=>"{sunday}",
		1=>"{monday}",
		2=>"{tuesday}",
		3=>"{wednesday}",
		4=>"{thursday}",
		5=>"{friday}",
		6=>"{saturday}"
		);
		
		
	var $cron_squid=array(
		"S"=>"{sunday}",
		"M"=>"{monday}",
		"T"=>"{tuesday}",
		"W"=>"{wednesday}",
		"H"=>"{thursday}",
		"F"=>"{friday}",
		"A"=>"{saturday}"
		);	

	var $cron_hours=array();
	var $cron_mins=array();
	
	function cron_macros(){
		for($i=0;$i<24;$i++){
			$text=$i;
			if($i<10){
				$text="0$i";
			}
			
			$this->cron_hours[$i]=$text;
		}
		
			for($i=0;$i<60;$i++){
			$text=$i;
			if($i<10){
				$text="0$i";
			}
			
			$this->cron_mins[$i]=$text;
		}		
	}
	
	function cron_compile_eachday($day,$hour,$min){
		return "$min $hour * * $day";
		
	}
	
	function cron_human($pattern){
		$tbl=explode(" ",$pattern);
		if($tbl[0]=="*"){
			$minutes="{all_minutes}";
		}else{
			$minutes=$this->cron_mins[$tbl[0]];
		}
	if($tbl[1]=="*"){
			$hours="{all_hours}";
		}else{
			$hours=$this->cron_hours[$tbl[1]];
		}		
		
	if($tbl[4]=="*"){
			$days="{all_days}";
		}else{
			$days=$this->cron_days[$tbl[4]];
		}	
		
		
		return "$hours:$minutes $days";
		
	}
		
		
}


class cron{
	

		
	
	
	
	
	
	
	
	var $cronID=array(
			1=>"{update} kaspersky {anti-spam} {database}",
			2=>"{update} Kaspersky {antivirus} {database}",
			3=>"{ensure} artica-postfix daemon {is up} ",
			4=>"{task_postfix_network_auto}"
			);
			
	var $LocalArrayCronFiles;
	var $LocalArrayUser;
	
	var $array_artica_task;
	
	
	var $array_special_strings=array(	"@reboot"  =>"@reboot",
							"@yearly"  =>"0 0 1 1 *",
							"@annually"=>"0 0 1 1 *",
							"@monthly" =>"0 0 1 * *",
							"@weekly"  =>"0 0 * * 0",
							"@daily"   =>"0 0 * * *",
							"@midnight"=>"0 0 * * *",
							"@hourly"=>"0 * * * *");
							
							
		var $array_month=    array(	"*"=>"all",
							"1"=>"January",
							"2"=>"February",
							"3"=>"March",
							"4"=>"April",
							"5"=>"May",
							"6"=>"June",
							"7"=>"July",
							"8"=>"August",
							"9"=>"September",
							"10"=>"October",
							"11"=>"November",
							"12"=>"December");
						
		var $array_day_of_week=array(
							"*"=>"all",
							"0"=>"Sunday",
							"1"=>"Monday",
							"2"=>"Tuesday",
							"3"=>"Wednesday",
							"4"=>"Thursday",
							"5"=>"Friday",
							"6"=>"Saturday");
	function cron($noload=0){
		if($noload==0){
			$this->TestTree();
			$this->LoadDatasFromDisk();
			$this->LoadUsersList();
			$this->LoadArticaTaskList();
		}
	}
	
	
	
	function LoadUsersList(){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		$sock=new sockets();
		$datas=$sock->getfile('GetUsersList');
		$arr=explode("\n",$datas);
		while (list ($index, $name) = each ($arr) ){
			if(trim($name)<>null){
			$this->LocalArrayUser[$name]=$name;
			}
		}
		
	}
	
	function LoadArticaTaskList(){
		include_once(dirname(__FILE__) . '/class.users.menus.inc');
		$usermenus=new usersMenus();
		$this->array_artica_task["artica-cron-awstats-mail"]=array(
		"CronFileCommand"=>"1,5,10,15,20,25,30,35,40,45,50,55 * * * * root $usermenus->ARTICA_ROOT_PATH/bin/artica-install -awstats generate >/dev/null",
		"CronFileDescriptions"=>'{artica-cron-awstats.mail_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");
		
		$this->array_artica_task["artica-cron-keepup2date"]=array(
		"CronFileCommand"=>"@hourly root /opt/kav/5.5/kav4mailservers/bin/keepup2date -q",
		"CronFileDescriptions"=>'{artica-cron-keepup2date_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");

		$this->array_artica_task["artica-cron-watchdog"]=array(
		"CronFileCommand"=>"1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41,43,45,47,49,51,53,55,57,59 * * * * root  $usermenus->ARTICA_ROOT_PATH/bin/artica-install -watchdog >/dev/null",
		"CronFileDescriptions"=>'{artica-cron-watchdog_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");	


		$this->array_artica_task["artica-cron-kasupdate"]=array(
		"CronFileCommand"=>"@hourly root /usr/local/ap-mailfilter3/bin/keepup2date -c /usr/local/ap-mailfilter3/etc/keepup2date.conf -q",
		"CronFileDescriptions"=>'{artica-cron-kasupdate_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");
		
		$this->array_artica_task["artica-cron-postfixinet"]=array(
		"CronFileCommand"=>"1,5,10,15,20,25,30,35,40,45,50,55 * * * * root $usermenus->ARTICA_ROOT_PATH/bin/artica-install -postfix inet >/dev/null",
		"CronFileDescriptions"=>'{artica-cron-kasupdate_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");

		$this->array_artica_task["artica-cron-queuegraph"]=array(
		"CronFileCommand"=>"* * * * * root $usermenus->ARTICA_ROOT_PATH/bin/queuegraph/queuegraph-rrd.sh >/dev/null",
		"CronFileDescriptions"=>'{artica-cron-queuegraph_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");	

		$this->array_artica_task["artica-cron-mailgraph"]=array(
		"CronFileCommand"=>"1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41,43,45,47,49,51,53,55,57,59 * * * *  root $usermenus->ARTICA_ROOT_PATH/bin/mailgraph/mailgraph1.cgi >/dev/null",
		"CronFileDescriptions"=>'{artica-cron-mailgraph_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");				
		
		
		$this->array_artica_task["artica-cron-quarantine"]=array(
		"CronFileCommand"=>"0 3 * * *  root $usermenus->ARTICA_ROOT_PATH/bin/artica-quarantine -maintenance >/dev/null",
		"CronFileDescriptions"=>'{artica-cron-quarantine_text}',
		"CronFileMailto"=>'none',
		"CronFileToDelete"=>"no");				
		}		
	
	
	function LoadDatasFromDisk(){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		$sock=new sockets();
		$datas=$sock->getfile('CronDatas');

		if(preg_match_all('#<cron>\s+<filename>([a-z0-9A-Z\/\.\_\-]+)</filename>\s+<filedatas>(.+?)</filedatas>\s+</cron>#is',$datas,$regs)){
			while (list ($index, $filename) = each ($regs[1]) ){
				
				$datas=$regs[2][$index];
				$array[basename($filename)]=$this->GetInfosFromFile($regs[2][$index]);
				
			}
		}
		
		if(is_array($array)){	
			$this->LocalArrayCronFiles=$array;
			while (list ($taskname, $array) = each ($this->LocalArrayCronFiles) ){
				$this->GetTaskDatas($taskname);
				}
			reset($this->LocalArrayCronFiles);
		}else{
			writelogs('WARNING !!! Array is empty... !!!',__FUNCTION__,__FILE__);
		}
	}
		
		
	function Hash_get_tasks_list(){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		$dn="cn=system_cron_task,cn=artica,$ldap->suffix";	
		$sr =@ldap_search($ldap->ldap_connection,$dn,'(&(objectclass=ArticaCronDatas)(cn=*))',array());
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);		
			for($i=0;$i<$hash["count"];$i++){
				$TaskName=$hash[$i]["cn"][0];
				$TaskObs=$hash[$i]["cronfiledescriptions"][0];
				$TaskDelete=$hash[$i]["cronfiletodelete"][0];
				$res[$TaskName]=array("OBS"=>$TaskObs,"DEL"=>$TaskDelete);
			}
		}
		return $res;
		}
	
		
		
	function TestTree(){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		if(!$ldap->ExistsDN("cn=system_cron_task,cn=artica,$ldap->suffix")){
			$upd['cn'][0]="system_cron_task";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			$ldap->ldap_add("cn=system_cron_task,cn=artica,$ldap->suffix",$upd);
			unset($upd);
			}		
	}
	
	function GetTaskDatas($task_name){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		if($task_name=='.placeholder'){return null;}
		$task_name=str_replace('.','-',$task_name);
		$task_name=str_replace(' ','-',$task_name);
		$ldap=new clladp();
		$this->TestTree();
		//writelogs("Get info form $task_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$dn="cn=$task_name,cn=system_cron_task,cn=artica,$ldap->suffix";	
	if(!$ldap->ExistsDN($dn)){
		writelogs("CREATE NEW TASK $task_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(is_array($this->LocalArrayCronFiles[$task_name])){
				$obs=$this->LocalArrayCronFiles[$task_name]["OBS"];
				$cmdLine=$this->LocalArrayCronFiles[$task_name]["CMD"];
				$mailto=$this->LocalArrayCronFiles[$task_name]["MAIL"];
				}
				writelogs("adding task $task_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$upd['cn'][0]="$task_name";
				$upd['objectClass'][0]='ArticaCronDatas';
				$upd['objectClass'][1]='top';
				if($cmdLine==null){$cmdLine="/dev/null";}
				if($mailto==null){$mailto="none";}
				if($obs==null){$obs='No observations...';}
				$upd['CronFileCommand'][0]=$cmdLine;
				$upd['CronFileDescriptions'][0]=$obs;
				$upd['CronFileMailto'][0]=$mailto;
				$upd["CronFileToDelete"][0]="no";
				$ldap->ldap_add($dn,$upd);	
				return array("CMD"=>$cmdLine,"OBS"=>$obs,"DATAS"=>$this->ParseCronCommand($cmdLine),);			
			}
		
		
		
	         	$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=ArticaCronDatas');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			$cronfilecommand=$hash[0]["cronfilecommand"][0];
			$cronfiledescriptions=$hash[0]["cronfiledescriptions"][0];
			$cronfilemailto=$hash[0]["cronfilemailto"][0];
			$CronFileToDelete=$hash[0]["cronfiletodelete"][0];
			return array(
				"cronfilecommand"=>$cronfilecommand,
				"cronfiledescriptions"=>$cronfiledescriptions,
				"cronfilemailto"=>$cronfilemailto,
				"cronfiletodelete"=>$CronFileToDelete,
				"DATAS"=>$this->ParseCronCommand($cronfilecommand),
				"OBS"=>$cronfiledescriptions,
				"CMD"=>$cronfilecommand,
				"MAIL"=>$cronfilemailto);	
		}
		
	}
	
	function ParseCronCommand($command,$taskname=null,$human=0,$compile_words=false){
		//writelogs("$taskname:: ParseCronCommand($command));",__CLASS__.'/'.__FUNCTION__,__FILE__);
		while (list ($index, $line) = each($this->array_special_strings) ){
				$command=str_ireplace($index,$line,$command);
			}
		
		//writelogs("read $command",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if(preg_match('#([\*0-9,\-]+)\s+([\*0-9,\-]+)\s+([\*0-9,\-]+)\s+([\*0-9,\-]+)\s+([\*0-9,\-]+)\s+([0-9A-Za-z\.\-\_]+)\s+(.+)#',$command,$reg)){
		for($i=1;$i<=5;$i++){
			if(preg_match('#([0-9]+)-([0-9]+)#',$reg[$i],$ha)){
				for($z=$ha[1];$z<=$ha[2];$z++){
					$line[]=$z;
				}
				$reg[$i]=implode(',',$line);
			}
			
		}
			$minutes=$reg[1];
			$hours=$reg[2];
			$day=$reg[3];
			$Month=$reg[4];
			$Day_of_the_week=$reg[5];
			$user=$reg[6];
			$Command_line=$reg[7];		
		}else{
			//writelogs("$taskname:: WARNING UNABLE TO READ $command set default values",__CLASS__.'/'.__FUNCTION__,__FILE__);	
			$minutes='*';
			$hours='*';
			$day='*';
			$Month='*';
			$Day_of_the_week='*';
			$user='root';
			$Command_line='/dev/null';
		}
		//writelogs("$taskname:: compile_words=$compile_words minutes=$minutes  hours=$hours day=$day dayofw=$Day_of_the_week month=$Month user=$user command=$Command_line",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($compile_words){$human==1;}
		if($human==1){
			
			$minutes=str_replace('*','{all}',$minutes);
			$hours=str_replace('*','{all}',$hours);
			$day=str_replace('*','{all}',$day);
			$Month=str_replace('*','{all}',$Month);
			$Day_of_the_week=str_replace('*','{all}',$Day_of_the_week);
			
		}
		
		
		
		if($compile_words==true){
			$ct=new cron_macros();
			$words[]="{run}";
			if($day=='{all}'){$words[]="{all_days_of_month}";}
			
			$words[]="{at}";
			
			if(trim($hours)=="{all}"){$words[]="{all_hours}";}else{
				if(strpos($hours,",")>0){
					$ii=explode(",",$hours);
					//writelogs("$taskname::explode -> $hours",__CLASS__.'/'.__FUNCTION__,__FILE__);
					while (list ($index, $hoursnum) = each ($ii) ){
						if(strlen($hoursnum)==1){$hoursnum="0$hoursnum";}
						$words[]="{$hoursnum}H,";
					}
				}else{
					if(strlen($hours)==1){$words[]="0{$hours}H";}else{$words[]="{$hours}H";}
				}		
			}	
			
			//writelogs("$taskname::$minutes -> \"$minutes\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(trim($minutes)=="{all}"){
				
				$words[]="{all_minutes}";}else{
				if(strpos($minutes,",")>0){
					$ii=explode(",",$minutes);
					while (list ($index, $minutesh) = each ($ii) ){
						if(strlen($minutesh)==1){$minutesh="0$minutesh";}
						$words[]="{$minutesh}Mn,";}	
				}else{
				if(strlen($minutes)==1){$words[]="0{$minutes}Mn";}else{$words[]="{$minutes}Mn";}
				}	
				
				
			}
			if($Day_of_the_week=="{all}"){$words[]="{all_days_of_the_week}";}else{
				$words[]="{only_days}:";
				if(strpos($Day_of_the_week,",")>0){
					$ii=explode(",",$Day_of_the_week);
					
					while (list ($index, $daynum) = each ($ii) ){$words[]=$ct->cron_days[$daynum].",";}
				}else{
					$words[]=$ct->cron_days[$Day_of_the_week];
				}		
			}
			
			return @implode(" ",$words);
		}
		
		
		return array(
			"MINUTES"=>$minutes,
			"HOURS"=>$hours,
			"DAY"=>$day,
			"MONTH"=>$Month,
			"DAYW"=>$Day_of_the_week,
			"USER"=>$user,
			"CMD"=>$Command_line,
			"COMMAND_LINE_SOURCE"=>$command
			);
	}
	
	function ImplodeCronCommand($array){
		$minutes=$array["DATAS"]["MINUTES"];
		$hours=$array["DATAS"]["HOURS"];
		$day=$array["DATAS"]["DAY"];
		$month=$array["DATAS"]["MONTH"];
		$dayw=$array["DATAS"]["DAYW"];
		$user=$array["DATAS"]["USER"];
		$cmd=$array["DATAS"]["CMD"];
		writelogs("$minutes $hours $day $month $dayw $user $cmd",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		$line="$minutes $hours $day $month $dayw $user $cmd";
		return $line;
		
	}
	
	function SaveTaskCommandLine($commandline,$taskname){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		$dn="cn=$taskname,cn=system_cron_task,cn=artica,$ldap->suffix";	
		if(!$ldap->ExistsDN($dn)){
			$this->AddNewTask($taskname);
		}
		$hash=$this->GetTaskDatas($taskname);
		writelogs("$taskname:: save new command line \"$commandline\"",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		$upd["cronfilecommand"][0]=$commandline;
		if($hash["cronfilecommand"]=null){
			$ldap->Ldap_add_mod($dn,$upd);
		}else{$ldap->Ldap_modify($dn,$upd);}
		}
		
		
	function SaveTaskSettings($taskname,$user,$command,$mailto,$observations){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		$dn="cn=$taskname,cn=system_cron_task,cn=artica,$ldap->suffix";	
		if(!$ldap->ExistsDN($dn)){$this->AddNewTask($taskname);}
		
		writelogs("$taskname:: user=$user command=$command mailto=$mailto obs=$observations",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		
		$hash=$this->GetTaskDatas($taskname);
		
		writelogs("$taskname:: stored command ={$hash["CMD"]}",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		
		if($hash["CMD"]<>null){
			$arrayH["DATAS"]=$this->ParseCronCommand($hash["CMD"],$taskname);
			writelogs("$taskname::MINUTES ={$arrayH["DATAS"]["MINUTES"]}",__CLASS__ . '/'.__FUNCTION__,__FILE__);	
			writelogs("$taskname::HOURS ={$arrayH["DATAS"]["HOURS"]}",__CLASS__ . '/'.__FUNCTION__,__FILE__);
			}else{
			$arrayH["DATAS"]["MINUTES"]="*";
			$arrayH["DATAS"]["HOURS"]="*";
			$arrayH["DATAS"]["DAY"]="*";	
			$arrayH["DATAS"]["MONTH"]="*";			
			$arrayH["DATAS"]["DAYW"]="*";
			}
		$arrayH["DATAS"]["USER"]=$user;
		$arrayH["DATAS"]["CMD"]=$command;
		$cronfilecommand=$this->ImplodeCronCommand($arrayH);
		
		writelogs("$taskname:: new command =$cronfilecommand",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		
		if($mailto==null){$mailto="none";}
		if($observations==null){$observations="No observations... ";}
		
		$upd["cronfilecommand"][0]=$cronfilecommand;
		$upd["cronfiledescriptions"][0]=$observations;
		$upd["cronfilemailto"][0]=$mailto;
		$upd["CronFileToDelete"][0]="no";
		$ldap->Ldap_modify($dn,$upd);
		}
		
	function TaskDelete($taskname){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		$dn="cn=$taskname,cn=system_cron_task,cn=artica,$ldap->suffix";		
		$upd["CronFileToDelete"][0]="yes";
		$ldap->Ldap_modify($dn,$upd);
	}
	
	function AddNewTask($taskname){
			include_once(dirname(__FILE__) . '/class.ldap.inc');
			$ldap=new clladp();
			$dn="cn=$taskname,cn=system_cron_task,cn=artica,$ldap->suffix";
			$upd['cn'][0]="$taskname";
			$upd['objectClass'][0]='ArticaCronDatas';
			$upd['objectClass'][1]='top';
			
			writelogs("add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			unset($upd);
			$upd['CronFileCommand'][0]="/dev/null";
			$upd['CronFileDescriptions'][0]="No observations....";
			$upd['CronFileMailTo'][0]="none";	
			$upd["CronFileToDelete"][0]="no";		
			$ldap->Ldap_add_mod($dn,$upd);	
			}
	
	
	function GetInfosFromFile($datas){
		$array=explode("\n",$datas);
		while (list ($index, $line) = each ($array) ){
		if(preg_match('#^\#(.+)#',$line,$regs)){
			$datas=str_replace($regs[0],'',$datas);
			$obs[]=$regs[1];
			}
		}
		$datas=trim($datas);
		$datas=str_replace("\r","\n",$datas);
		$array_datas=explode("\n",$datas);
		while (list ($index, $line) = each ($array_datas) ){
			if(trim($line)<>null){
				if(preg_match('#MAILTO=(.+)#',$line,$regs)){
					$mail_to=$regs[1];
				}else{
					$newarray[]=$line;
				}
			}
		}
		
		if(is_array($newarray)){
			$stringCommand=implode("\n",$newarray);
		}
		
		if(is_array($obs)){$stringobs=implode("\n",$obs);}
		
		if($mail_to==null){$mail_to="none";}
		return array("OBS"=>$stringobs,"CMD"=>$stringCommand,"MAIL"=>$mail_to);
	}
	
	function ApplyCronToServer(){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		$taskLists=$this->Hash_get_tasks_list();
		$tempPath=dirname(__FILE__) . '/conf/cron';
		if(!is_dir($tempPath)){
			if(!@mkdir($tempPath)){return false;}
		}
		if(!is_array($taskLists)){return true;}
		while (list ($taskname, $line) = each ($taskLists) ){
		if($line["DEL"]=='yes'){
				if($taskname<>'.placeholder'){
				$filetask=str_replace('.','-',$taskname);
				$filetask=str_replace(' ','-',$filetask);
				$TaskToDel=$TaskToDel . $filetask . "\n";
				}
				$this->_DeleteCNTASKS($taskname);
				
			}else{
				$this->_SaveTaskToServer($taskname);
			}
			
		}
		
		if($TaskToDel<>null){
			$tempfile=dirname(__FILE__) . '/conf/cron/CrontaskToDelete';
			writelogs("ALL TASKS:: Save delete list tasks $tempfile",__FUNCTION__,__FILE__);
			$fp = fopen($tempfile, "w",0); #open for writing
	  		fputs($fp, $TaskToDel); #write all of $data to our opened file
	  		fclose($fp); #close the file		
			}
		include_once(dirname(__FILE__) . '/class.sockets.inc');	
		$sock=new sockets();
		$sock->getfile("ReplicateCronTask");
		return true;
		
		
	}
	
	function _DeleteCNTASKS($taskname){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		$dn="cn=$taskname,cn=system_cron_task,cn=artica,$ldap->suffix";
		$ldap->ldap_delete($dn,false);
		
	}
	
	function _SaveTaskToServer($taskname){
		if($taskname=='.placeholder'){return true;}
		$array=$this->GetTaskDatas($taskname);
		$cronfilecommand=$array["cronfilecommand"];
		if($cronfilecommand=="/dev/null"){return true;}
		$cronfiledescriptions=$array["cronfiledescriptions"];
		$cronfilemailto=$array["cronfilemailto"];
		if($cronfilecommand==null){return true;}
		if($cronfilemailto<>'none'){
			$file="MAILTO=$cronfilemailto\n\n";
		}
		$tbl=explode("\n",$cronfiledescriptions);
		if(is_array($tbl)){
			while (list ($index, $line) = each ($tbl) ){
				$file=$file . "#$line\n";
			}
		}
		
		$taskname=str_replace('.','-',$taskname);
		$taskname=str_replace(' ','-',$taskname);
		
		$file=$file . "\n";
		$file=$file . "$cronfilecommand\n";
			writelogs("$taskname:: Save task to ".dirname(__FILE__) . '/conf/cron/'.$taskname,__FUNCTION__,__FILE__);
			$tempfile=dirname(__FILE__) . '/conf/cron/'.$taskname;
			$fp = fopen($tempfile, "w",0); #open for writing
	  		fputs($fp, $file); #write all of $data to our opened file
	  		fclose($fp); #close the file				
		return true;
		}
	
}

class crontab{
	
function time_hash_day(){
	$array=array(
		null=>"{select}",
		"M"=>"{monday}",
		"T"=>"{tuesday}",
		"W"=>"{wednesday}",
		"H"=>"{thursday}",
		"F"=>"{friday}",
		"A"=>"{saturday}",
		"S"=>"{sunday}"
	);
	return $array;
	
}

function time_hash_hour(){
	for($i=0;$i<24;$i++){
		if(strlen($i)<2){$h["0$i"]="0$i";}else{$h[$i]=$i;}
		
	}
	return $h;
}
function time_hash_min(){
for($i=0;$i<60;$i++){
		if(strlen($i)<2){$h["0$i"]="0$i";}else{$h[$i]=$i;}
		
	}
	return $h;	
	
}	
	
	function crontab(){
		
		
		
		
	}
	
	
	
	
	
}


	

?>