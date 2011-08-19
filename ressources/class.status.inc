<?php

class status{
	var $logFile="/var/log/artica-postfix/background-status.debug";
	var $_postfix_status;
	var $array_queue;	
	var $array_right;
	var $array_left;
	var $sock;
	var $cyrus_init_d;
	var $status_in_line;
	var $myini;
	var $newversions=array();
	function status($noconnect=0,$local_ini=null,$logfile=null){
		if(!isset($GLOBALS["AS_ROOT"])){if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}else{$GLOBALS["AS_ROOT"]=false;}}
		if($logfile<>null){$this->logFile=$logfile;}
		if(!file_exists(dirname(__FILE__) . '/settings.inc')){
			$this->events("ERROR: unable to stat " . dirname(__FILE__) . '/settings.inc',__FUNCTION__,__LINE__);
			return false;
		}
		$this->events("Include settings.inc",__FUNCTION__,__LINE__);
		include(dirname(__FILE__) . '/settings.inc');
		$this->events("Loading sockets",__FUNCTION__,__LINE__);
		$sock=new sockets();
		$this->events("Loading cmd.php?postfixQueues=yes",__FUNCTION__,__LINE__);
		$queues=unserialize($sock->getFrameWork("cmd.php?postfixQueues=yes"));
		$this->array_queue=$queues;
		$this->cyrus_init_d=$_GLOBAL["cyrus_initd_path"];
		$this->myini=$local_ini;
		$this->sock=new sockets();
		if($noconnect<>0){return null;}
		$this->events("Starting status...Postfix_satus()",__FUNCTION__,__LINE__);
		$this->array_right[]=$this->Postfix_satus();
		$this->events("Starting status...cyrus_satus()",__FUNCTION__,__LINE__);
		$this->array_right[]=$this->cyrus_satus();
		$this->events("Starting status...avestatus_satus()",__FUNCTION__,__LINE__);
		$this->array_right[]=$this->avestatus_satus();
		$this->events("Starting status...artica_status()",__FUNCTION__,__LINE__);
		$this->array_left[]=$this->artica_status();
		if ($_GLOBAL["kas_installed"]==true){
			$this->events("Starting status...kas_status()",__FUNCTION__,__LINE__);
			$this->array_right[]=$this->kas_status();
		}
		$this->events("Starting status...DONE",__FUNCTION__,__LINE__);
		
	}
	
function events($text,$function,$line ){
		
		if(!$GLOBALS["AS_ROOT"]){
			if($GLOBALS["VERBOSE"]){echo "class.status.inc: $function $text $line\n";}
			return;
		}
		if($this->logFile==null){$this->logFile="/var/log/artica-postfix/background-status.debug";}
		$pid=getmypid();
		$date=date('H:i:s');
		$logFile=$this->logFile;
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$file=basename(__FILE__);
		$class=__CLASS__;
		@fwrite($f, "$file[$pid] $date $class::$function: $text\n");
		@fclose($f);	
		}	
		
function ProcessExistsByCommand($command){
	$cmd="/usr/bin/pgrep -l -f \"$command\"";
	writelogs($cmd,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$vals);
while (list ($num, $val) = each ($vals) ){
		writelogs("\"$val\" ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$val=trim($val);
		if($val==null){continue;}
		if(preg_match("#pgrep#",$val)){continue;}
		if(!preg_match('#([0-9]+)#',$val,$re)){continue;}
		writelogs("\"$val\" Processing {$val["master_pid"]} PID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(is_file("/proc/{$val["master_pid"]}/exe")){return true;}
	}
	return false;	
}

 public function PGREP($command){
 	$command=str_replace(".","\.",$command);
 	$command=str_replace("/","\/",$command);
	$cmd="/usr/bin/pgrep -l -f \"$command\"";
	writelogs($cmd,__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
	exec($cmd,$vals);
while (list ($num, $val) = each ($vals) ){
		writelogs("\"$val\" ",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
		$val=trim($val);
		if($val==null){continue;}
		if(preg_match("#pgrep#",$val)){continue;}
		if(!preg_match('#([0-9]+)#',$val,$re)){continue;}
		writelogs("\"$val\" Processing {$val["master_pid"]} PID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(is_file("/proc/{$val["master_pid"]}/exe")){return true;}
	}
	return false;	
}
	

	
	function StatusFailed($returnArray=0){
		$this->events("running",__FUNCTION__,__LINE__);
		include_once(dirname(__FILE__) . '/class.ini.inc');
		$p=new usersMenus();
		$bycommand=false;
		if(posix_getuid()==0){$bycommand=true;}
		$users=new usersMenus();
		$users->LoadModulesEnabled();	
		$socks=new sockets();
		$ini=new Bs_IniHandler();
		$StopMonitoNFS=$socks->GET_INFO('StopMonitoNFS');
		if(file_exists(dirname(__FILE__)."/logs/global.status.ini")){
			$ini->loadFile(dirname(__FILE__)."/logs/global.status.ini");
		}else{
			$ini->loadString($socks->getfile('daemons_status'));
		}
		
		if(!is_array($ini->_params)){return null;}
	
		
		while (list ($num, $val) = each ($ini->_params) ){
			if($val["application_installed"]==0){continue;}
			if($val["service_disabled"]=='0'){continue;}
			if($val["running"]==1){continue;}
			if($val["service_croned"]==1){continue;}
			if($val["application_installed"]==null){continue;}
			if($val["status"]=="command line"){continue;}
			$val["master_pid"]=trim($val["master_pid"]);
			if($val["master_pid"]>0){continue;}
			if($val["service_name"]=="APP_XFCE"){continue;}
			if($val["service_name"]==="APP_CONSOLEKIT"){continue;}
			if($val["service_name"]==="APP_PRELOAD"){continue;}			
			writelogs("Scanning {$val["service_name"]} disabled={$val["service_disabled"]} master_pid={$val["master_pid"]} installed={$val["application_installed"]} running=0",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__);
			
			if($val["service_name"]=="APP_NFS"){
				if($StopMonitoNFS==1){continue;}
			}
			
			if($val["service_name"]=="APP_ARTICA_MYSQMAIL"){
					writelogs("{$val["service_name"]} -> pgrep",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
					if($this->PGREP("artica-postfix/exec.maillog.php")){
						writelogs("Scanning {$val["service_name"]} artica-postfix/exec.maillog.php exists, continue;",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__); 
						continue;
					}else{
						writelogs("Scanning {$val["service_name"]} pgrep failed (bycommand=$bycommand)",__FUNCTION__.'/'.__CLASS__,__FILE__,__LINE__);
					}
			}			
			
			if($bycommand){
				$this->events("Scanning by command=true",__FUNCTION__,__LINE__);
				
				$this->events("Scanning {$val["service_name"]} installed={$val["application_installed"]} running={$val["running"]} master_pid={$val["master_pid"]}",__FUNCTION__,__LINE__);
				if($val["master_pid"]<>null){
					if(is_file("/proc/{$val["master_pid"]}/exe")){
						$this->events("Scanning {$val["service_name"]} /proc/{$val["master_pid"]}/exe exists, continue;"); 
						continue;
					}
				}
				
				
				
				if($val["service_name"]=="APP_ARTICA_MYSQMAIL"){
					
					if($this->ProcessExistsByCommand("artica-postfix/exec.maillog.php")){
						$this->events("Scanning {$val["service_name"]} artica-postfix/exec.maillog.php exists, continue;"); 
						continue;
					}else{
						$this->events("ProcessExistsByCommand return false");
					}
				
				system('/etc/init.d/artica-postfix start postfix-logger &');
				if($this->ProcessExistsByCommand("artica-postfix/exec.maillog.php")){
						$this->events("Scanning {$val["service_name"]} artica-postfix/exec.maillog.php exists, with start it command;"); 
						continue;
					}				
				}
				
				
				if($val["service_name"]=="APP_POSTFIX"){
					if($this->postfix_check_pid()){continue;}
				}
				
				if($val["service_name"]=="APP_SPAMASSASSIN"){
					if(!$users->MEM_HIGER_1G){continue;}
					
					if($this->PIDOF($users->spamassassin_bin_path)){
						$this->events("Scanning {$val["service_name"]} pidof return true");
						continue; 
					}
					
				}
				

				

				
			 $this->events("Scanning {$val["service_name"]}...",__FUNCTION__,__LINE__);	
			  if($val["service_name"]=="APP_KAS3_MILTER"){
					if($this->PIDOF("/usr/local/ap-mailfilter3/bin/kas-milter")){
						$this->events("Scanning {$val["service_name"]} pidof return true");
						continue; 
					}else{
						$this->events("Scanning {$val["service_name"]} pidof return false, start it");
						system('/etc/init.d/kas3 start &');
						$this->events("Scanning {$val["service_name"]} done");
						if($this->PIDOF("/etc/init.d/artica-postfix start kas3 &")){
							$this->events("Scanning {$val["service_name"]} pidof return true");
							continue;
						}
						
					}
					
				}				

			}
			
			
			
			$infos="&nbsp;";
					
					
					
						$count=$count+1;
						$why='{stopped}';
						
						if($val["license"]=="license_expired"){
							$why="{license_expired}";
						}
						if(trim($val["service_cmd"])<>null){
							$javascript="javascript:YahooWin3(550,'admin.index.php?EmergencyStart={$val["service_cmd"]}','{{$val["service_name"]}}');";
							$javascript_a="YahooWin3(550,'admin.index.php?EmergencyStart={$val["service_cmd"]}','{{$val["service_name"]}}');";
							$tooltip="{start_service_in_debug}";
						}else{
							$javascript="admin.index.services.status.php";
							$tooltip=$why;
						}
						$table["BIG"][]=Paragraphe("danger64.png","{{$val["service_name"]}}",
						"<span style='color:#D32D2D'>$why</span><br>$infos","$javascript",$tooltip,'300',73);
						$table["LITTLE"][]="
						<tr " . CellRollOver($javascript_a,$tooltip)." style='border:1px solid red'>
							<td valign='top'>
								<table style='width:282px;border-bottom:1px solid #D32D2D;border-right:1px solid #D32D2D;border-top:1px solid #CCCCCC;border-left:1px solid #CCCCCC;margin-top:5px'>
								<tr>
									<td width=1% valign='top'><img src='img/danger24.png'></td>
									<td><strong>{{$val["service_name"]}}</strong>:<span style='color:#D32D2D'>$why</span>...&nbsp;$infos</td>
								</tr>
								</table>
							</td>
						</tr>
						";
						
						$json[]=array("PRODUCT"=>$val["service_name"],"WHY"=>$why,"INFOS"=>$infos,"service_cmd"=>$val["service_cmd"]);
					
				
			
		}
		if($returnArray==0){
			if(count($table["BIG"])>0){
				$final=implode(" ",$table["BIG"]);
				if(count($table["BIG"])>3){
					$final="<div style='width:310px;height:120px;overflow:auto'><table>".implode(" ",$table["LITTLE"])."</table></div>";
				}
				$tpl=new templates();
				return $tpl->_ENGINE_parse_body($final);
			}
			
		}
		$this->events("finish",__FUNCTION__,__LINE__);
		return $json;
		
	}
	
function postfix_check_pid(){
	$queue=system("/usr/sbin/postconf -h queue_directory");
	if(!is_file("$queue/pid/master.pid")){
		$this->events("Scanning {$val["service_name"]} $queue/pid/master.pid does not exists");
		return false;
	}
	
	$pid=trim(file_get_contents("$queue/pid/master.pid"));
	if(is_file("/proc/$pid/exe")){
		$this->events("Scanning {$val["service_name"]} /proc/$pid/exe exists, continue;"); 
		return true;
	}

  $this->events("Scanning {$val["service_name"]} /proc/$pid/exe does not exists, failed;"); 
  return false;
}

function PIDOF($path){
	$datas=system("/bin/pidof $path");
	$this->events("Scanning $path= $datas");
	if(trim($datas)<>null){return true;}
	
}

function AllStatus(){
		include_once(dirname(__FILE__) . '/class.html.pages.inc');
		include_once(dirname(__FILE__) . '/class.ini.inc');
		$p=new usersMenus();
		$users=new usersMenus();
//-----------------------------------------------------------------------------------------------------------------------------		
		if($p->kas_installed==true){
			$stat=trim($users->KAS_STATUS);
			$stat=explode(';',$stat);
			$ap_process_server=explode('-',$stat[0]);
			$ap_spfd=explode('-',$stat[1]);
			$kas_license=explode('-',$stat[2]);
			$kas_thttpd=explode('-',$stat[3]);
			
			$res=$ap_process_server[1]+$ap_spfd[1]+$kas_license[1] + $kas_thttpd[1];			
			if($res==4){$kas=$this->BuildLittleTemplate('kas_status',true);}else{$kas=$this->BuildLittleTemplate('kas_status',false);}
			}
//-----------------------------------------------------------------------------------------------------------------------------	
		if($this->sock->TestArticaPort()==false){
		$srv_artica=$this->BuildLittleTemplate('srv_artica',false);}else{$srv_artica=$this->BuildLittleTemplate('srv_artica',true);}
			
//-----------------------------------------------------------------------------------------------------------------------------		
		$stat=$users->POSTIFX_STATUS;
		$stat=explode(';',$stat);
		$postfixStatus=$stat[0];	
		if($postfixStatus==1){
			$postfix_status=$this->BuildLittleTemplate('postfix_status',true);}else{$postfix_status=$this->BuildLittleTemplate('postfix_status',false,"IndexStartPostfix()");}
//-----------------------------------------------------------------------------------------------------------------------------

	if($p->aveserver_installed==true){
		$stat=trim($users->KAV_STATUS);
		$stat=explode(';',$stat);
		$title="aveserver_status";
		if($stat[0]==1){
		$aveserver=$this->BuildLittleTemplate($title,true);}else{
		$aveserver=$this->BuildLittleTemplate($title,false);}
	}
//-----------------------------------------------------------------------------------------------------------------------------					
	if($users->fetchmail_installed==true){
		$stat=trim($users->FETCHMAIL_STATUS);
		$stat=explode(';',$stat);
		$title="fetchmail_status";
		if($stat[0]==1){
		$fetchmail_status=$this->BuildLittleTemplate($title,true);}else{
		$fetchmail_status=$this->BuildLittleTemplate($title,false);}
		
	}
//-----------------------------------------------------------------------------------------------------------------------------					
writelogs("Cyrus installed ? $users->cyrus_imapd_installed",__FUNCTION__,__FILE__);	
if($users->cyrus_imapd_installed==true){
		$stat=trim($users->CYRUS_STATUS);
		$stat=explode(';',$stat);
		$title="cyrus_status";
		if($stat[0]==1){
		$cyrus_status=$this->BuildLittleTemplate($title,true);}else{
		$cyrus_status=$this->BuildLittleTemplate($title,false);}	
	}
//-----------------------------------------------------------------------------------------------------------------------------		
	//$sock=new sockets();
	if($users->ARTICA_POLICY_STATUS){
	$title="artica_policy_status";
		$artica_policy=$this->BuildLittleTemplate($title,true);}else{
		$artica_policy=$this->BuildLittleTemplate("artica_policy_status",false);}		
//-----------------------------------------------------------------------------------------------------------------------------		
//-----------------------------------------------------------------------------------------------------------------------------		
	//$sock=new sockets();
	if($users->KAV_MILTER_INSTALLED){
	 $title="kav_milter_status";
		if ($users->KAVMILTER_PID<>null) {
	 		$kav_milter_status=$this->BuildLittleTemplate($title,true);}else{
			$kav_milter_status=$this->BuildLittleTemplate($title,false);}
	}
	
	if($users->SQUID_INSTALLED){
	$title="squid_status";
		if ($users->SQUID_PID<>null) {
	 		$squid_status=$this->BuildLittleTemplate($title,true);}else{
			$squid_status=$this->BuildLittleTemplate($title,false);}	
	}
	
	if($users->KAV4PROXY_INSTALLED){
	$title="kav4proxy_status";
		if ($users->KAV4PROXY_PID<>null) {
	 		$kav4proxy_status=$this->BuildLittleTemplate($title,true);}else{
			$kav4proxy_status=$this->BuildLittleTemplate($title,false);}	
	}

	
	if($users->DANSGUARDIAN_INSTALLED){
		$title="dansguardian_status";
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->getFrameWork('cmd.php?squid-status=yes'));
		if($ini->_params["DANSGUARDIAN"]["master_pid"]<>null){
			$dansguardian_status=$this->BuildLittleTemplate($title,true);}else{
				$dansguardian_status=$this->BuildLittleTemplate($title,false);}	
		}
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->getfile('pureftd_status'));
		
			
if($users->PUREFTP_INSTALLED){
		$title="pureftpd_satus";
		
		if($ini->_params["PUREFTPD"]["master_pid"]<>null){
			$pure_ftp=$this->BuildLittleTemplate($title,true);}else{
				$pure_ftp=$this->BuildLittleTemplate($title,false);}	
		}

		if($ini->_params["SQLGREY"]["sqlgrey_installed"]==1){
			$title="sqlgrey_satus";
			if($ini->_params["SQLGREY"]["master_pid"]<>null){
			$sqlgrey=$this->BuildLittleTemplate($title,true);}else{
			$sqlgrey=$this->BuildLittleTemplate($title,false);}	
			
		}
			
	

//-----------------------------------------------------------------------------------------------------------------------------	
	if($users->POSTFIX_INSTALLED==false){$postfix_status=null;$artica_filter=null;}
	$html="<H4>{services_status}</H4>
	<table>
	$srv_artica
	$postfix_status
	$sqlgrey
	$artica_policy
	$artica_filter
	$cyrus_status
	$fetchmail_status
	$kas
	$aveserver
	$kav_milter_status
	$squid_status
	$dansguardian_status
	$kav4proxy_status
	$pure_ftp
	</table>
	";
	
	$in_line="
	<table style='width:100%'>
	<tr>
		<td>
			<table style='width:100%'>
			$srv_artica
			</table>
		</td>
		<td>
			<table style='width:100%'>
			$artica_filter
			</table>
		</td>	
		<td>
			<table style='width:100%'>
			$artica_policy
			</table>
		</td>	
		<td>
			<table style='width:100%'>
			$postfix_status
			</table>
		</td>
		<td>
			<table style='width:100%'>
			$cyrus_status
			</table>
		</td>							
	</tr>
	
	";
	$this->status_in_line=$in_line;
	return $html;
	
	
	
}


function BuildLittleTemplate($title,$status=false,$onfailed=null){
	if($status==false){
		if($onfailed<>null){$a="<a href=\"javascript:$onfailed\">";$b="</a>";}
		return "<tr>
				<td width=1% style='border-bottom:1px dotted #CCCCCC' valign='top'><img src='img/icon_err.gif'></td>
				<td style='border-bottom:1px dotted #CCCCCC' valign='top'>{".$title."}</td>
				<td style='border-bottom:1px dotted #CCCCCC' valign='top'>$a{some_service_is_stopped}$b</td>
			</tr>";
		
	}else{
		return "<tr>
				<td width=1% style='border-bottom:1px dotted #CCCCC' valign='top'><img src='img/icon_ok.gif'></td>
				<td style='border-bottom:1px dotted #CCCCCC' valign='top'>{".$title."}</td>
				<td  style='border-bottom:1px dotted #CCCCCC' valign='top'>{all_services_running}</td>
			</tr>";
	}
	
	
}
	
	
	
	
	function kas_status(){
		$user=new usersMenus();
		$stat=trim($user->KAS_SATUS);
		$stat=explode(';',$stat);
		if(!is_array($stat)){return null;}
		
		if($stat[0]==null){
			$img='i24.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{kas_status}',$text);
		}
		$ap_process_server=explode('-',$stat[0]);
		$ap_spfd=explode('-',$stat[1]);
		$kas_license=explode('-',$stat[2]);
		$kas_thttpd=explode('-',$stat[3]);
		$res=$ap_process_server[1]+$ap_spfd[1]+$kas_license[1] + $kas_thttpd[1];
		
		if($res<4){
			$img='warning24.png';
			$text="{some_service_is_stopped}";
			}
		if($res==4){
			$text="{all_services_running} {using_pid} :";
			$img='ok24.png';
		}
			
		
		
		$text=$text . "<ul>
		<li>ap-process-server: PID {$ap_process_server[0]}</li>
		<li>ap-spfd: PID {$ap_spfd[0]}</li>
		<li>kas-licence: PID {$kas_license[0]}</li>
		<li>kas-thttpd: PID {$kas_thttpd[0]}</li></ul>";
		
		
		return $this->BuildDesign($img,'{kas_status}',$text);
	}
	
	
	function artica_status(){
	
	if($this->sock->TestArticaPort()==false){
		$img='danger32.png';
		$text='{error_no_socks}<br> {request_start_artica_service}';
		$color='e53838';
		return $this->BuildDesign_infos($img,'{srv_artica}',$text,$color);
	}else{
		$pid=$this->sock->getfile('pid');
		$img='ok32.png';
		$text="{service_running} {using_pid} $pid {on port}:{$this->sock->remote_port}";
		$color='18a712';
	}
	if(!$this->sock->error==false){
		if($this->_postfix_status==0){
			$this->array_left[]=$this->BuildDesign_infos('i32.png','{postfix_status_stopped}','{recommend_restart_postfix}','5e5ec7');
			
		}
	}
	
	return $this->BuildDesign_infos($img,'{srv_artica}',$text,$color);
	
	}
	
	
function Squid_status(){
	if(!function_exists("mysql_connect")){
		return "<H2>Unable to stat  function mysql_connect, check php Mysql module or reboot the server</H2>";
	}
	$q=new mysql();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?squid-ini-status=yes")));
	$master_version=$ini->_params["SQUID"]["master_version"];
	$master_pid=$ini->_params["SQUID"]["master_pid"];
	
if($ini->_params["SQUID"]["running"]==0){
		$img="status_postfix_bg_failed.png";
		$status="{stopped}";
		$start="<hr><div style='text-align:right'>".button("{start}","Loadjs('StartStopServices.php?APP=APP_SQUID&cmd=squid-cache&action=start')")."</div>";
	}else{
			if(preg_match("#2\.3.*#",$master_version)){$img='status_postfix_bg_ok23.png';}
			if(preg_match("#2\.5.*#",$master_version)){$img='status_postfix_bg_ok25.png';}
			if(preg_match("#2\.7.*#",$master_version)){$img='status_postfix_bg_ok27.png';}
			if(preg_match("#2\.6.*#",$master_version)){$img='status_postfix_bg_ok26.png';}			
			if(preg_match("#2\.8.*#",$master_version)){$img='status_postfix_bg_ok28.png';}
			if(preg_match("#2\.9.*#",$master_version)){$img='status_postfix_bg_ok29.png';}
			if(preg_match("#3\.0.*#",$master_version)){$img='status_postfix_bg_ok30.png';}
			if(preg_match("#3\.1.*#",$master_version)){$img='status_postfix_bg_ok31.png';}
			if(preg_match("#3\.2.*#",$master_version)){$img='status_postfix_bg_ok32.png';}
			if(preg_match("#3\.3.*#",$master_version)){$img='status_postfix_bg_ok33.png';}
			$text="{service_running}<br>{using_version} $master_version {pid} $master_pid";
	}

if($ini->_params["SQUID"]["icap_enabled"]<>'1'){
	
	$icap="<table style='width:100%;margin:0px;' " .CellRollOver($js_service).">
		<tr>
			<td width=1%><img src='img/danger16.png'></td>
			<td align='left' nowrap><strong style='color:#D01A1A;font-size:11px'>{no_icap_support}</td>
			<td width=1% align='right'>&nbsp;</td>
		</tr>
	</table>";
	
	
}

	
	$status_dansguardian=DAEMON_STATUS_LINE("DANSGUARDIAN",$ini,null,1,"220px");
	$status_kav4proxy=DAEMON_STATUS_LINE("KAV4PROXY",$ini,null,1,"220px");
	$cicap=DAEMON_STATUS_LINE("C-ICAP",$ini,null,1,"220px");
	$APP_SQUIDGUARD_HTTP=DAEMON_STATUS_LINE("APP_SQUIDGUARD_HTTP",$ini,null,1,"220px");
	$APP_UFDBGUARD=DAEMON_STATUS_LINE("APP_UFDBGUARD",$ini,null,1,"220px");


	$pointers="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\" ";
	$requests=$q->COUNT_ROWS("dansguardian_events","artica_events");
	$requests=numberFormat($requests,0,""," ");
	
	$sql="SELECT COUNT( ID ) as tcount FROM blocked_websites WHERE WEEK( zDate ) = WEEK( NOW( ) )";
	$ligneW=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$blocked_today=numberFormat($ligneW["tcount"],0,""," ")." {blocked_websites} {this_week}";
	
	
	$websitesnums=$q->COUNT_ROWS("dansguardian_sitesinfos","artica_backup");
	$websitesnums=numberFormat($websitesnums,0,""," ");	
	
	$categories=$q->COUNT_ROWS("dansguardian_community_categories","artica_backup");
	$categories=numberFormat($categories,0,""," ");		
	
	$sock=new sockets();
	$sock->SET_INFO("squidStatsCategoriesNum",$categories);
	$sock->SET_INFO("squidStatsWebSitesNum",$websitesnums);
	$sock->SET_INFO("squidStatsBlockedToday",$blocked_today);
	$sock->SET_INFO("squidStatsRequestNumber",$requests);

	$design="
	<div style='background-image:url(img/$img);background-repeat:no-repeat;width:350px;height:270px'>
		<div style='padding-left:155px'>
			<h3 style='font-size:16px;font-weight:bold;'>{APP_SQUID}</h3>
			<br><span style='font-size:11px;font-weight:bold'><i style='color:black'>$text</i>$start$icap
			$status_dansguardian$status_kav4proxy$cicap$APP_SQUIDGUARD_HTTP$APP_UFDBGUARD</span>
			<div style='font-size:12px;margin-top:4px;border-top:1px solid #CCCCCC;text-align:right;width:220px'><i><strong>$requests {requests}</strong></i></div>
			<div style='font-size:12px;text-align:right;width:220px' $pointers OnClick=\"javascript:Loadjs('squid.visited.php')\"><i><strong style='text-decoration:underline'>$categories {websites_categorized}</strong></i></div>
			<div style='font-size:12px;text-align:right;width:220px' $pointers OnClick=\"javascript:Loadjs('squid.visited.php')\"><i><strong style='text-decoration:underline'>$websitesnums {visited_websites}</strong></i></div>
			<div style='font-size:10px;text-align:right;width:220px' $pointers OnClick=\"javascript:Loadjs('squid.visited.php')\"><i><strong style='text-decoration:underline'>$blocked_today</strong></i></div>
			
		</div>
	</div>
	
	";
	
		$cache_file=dirname(__FILE__)."/logs/web/squid.status.html";
		writelogs("Writing $cache_file",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
		@file_put_contents($cache_file,$design);
		@chmod($cache_file,777);
	
	return $design;
	
}

function Postfix_multi_status($hostname){
	
	$sock=new sockets();
	$q=new mysql();
	$sql="SELECT SUM(`size`) as tsize,COUNT(msgid) as tcount FROM postqueue WHERE `instance`='$hostname'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));	
	$tot=$ligne["tcount"];
	$tot_size=$ligne["tsize"]/1024;
	$tot_size=FormatBytes($tot_size);
	
	
	
	
	
$status_queues="
			<table>
			<tr>
				<td colspan=2><span style='font-size:13px'>{smtp_queues}</span></td>
			</tr>
				
	
				<tr $link>
					<td align='right' style='color:#C53110;border-top:1px dotted #CCCCCC'>Total:&nbsp;</td>
					<td style='color:#C53110;border-top:1px dotted #CCCCCC;font-size:12px'>
					<strong>$tot&nbsp;</strong>emails <i style='font-size:9px'>($tot_size)</i></td>
				</tr>
				
			</table>";	
			
$stat=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-mutli-stat=$hostname")));

	switch ($stat[0]) {
		
		case -2:$img='i64.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{postfix_status}',$text);
			break;	
		
		case '-1':$img='status_postfix_bg_failed.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='status_postfix_bg_ok.png';
			if(preg_match("#2\.7.*#",$stat[1])){$img='status_postfix_bg_ok27.png';}
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2];
			break;
		case '0':
			
			$img='status_postfix_bg_failed.png';
			$text=RoundedLightWhite('<span style=color:red>{service_stopped}<br>{using_version} ' . $stat[1]."</span>");
			$start="<hr><div style='text-align:right'>".button("{start}","MultipleInstanceRestart()")."</div>";
					
			break;
	}

	$design="
	<div style='background-image:url(img/$img);background-repeat:no-repeat;width:400px;height:210px'>
		<div style='padding-left:155px'>
			<H3 style='font-size:16px;font-weight:bold;'>{APP_POSTFIX}</H3>
			<div style='font-size:11px;font-weight:bold;text-align:right;padding-top:4px;border-top:1px solid black;margin:4px'>$hostname</div>
			<br><span style='font-size:11px;font-weight:bold'><i style='color:black'>$text</i>$start$status_queues</span>
		</div>
		<table style='width:100%'>
			<tr>
				<td valign='top'>
					<table style='width:100%;margin-top:5px'>
						<tr>
							<td style='border-right:2px solid black;padding:4px' align='center'>$amavis</td>
							<td style='border-right:2px solid black;padding:4px' align='center'>$dstat</td>
							<td style='border-right:2px solid black;padding:4px' align='center'>$rpcess_number</td>
						</tr>
					</table>
				</td>
				<td valign='top'>
					
				</td>
			</tr>
		</table>
	</div>
	";	
	
return $design;
	
}




function Postfix_satus(){
	$link_quarantine_progress=null;$artica_backup=null;$nb_events=null;$backup=null;$rejected=null;$quarantines=null;$instantIptables=null;$multis=array();$multiples_instances=null;$h5_style=null;$start=null;
	if(!function_exists("mysql_connect")){ 
		return "<H2>Unable to stat  function mysql_connect, check php Mysql module or reboot the server</H2>";
	}
	$time=date("H:i");
	if($_SESSION[md5("statusPostfix_satus")][$time]<>null){return $_SESSION[md5("statusPostfix_satus")][$time];}
	unset($_SESSION[md5(__CLASS__.__FUNCTION__)]);
	$this->events("Loading usersMenus()",__FUNCTION__,__LINE__);
	$usersmenus=new usersMenus();
	$this->events("Loading LoadModulesEnabled()",__FUNCTION__,__LINE__);
	$usersmenus->LoadModulesEnabled();
	$stat=$usersmenus->POSTIFX_STATUS;
	$this->events("Loading sockets()",__FUNCTION__,__LINE__);
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$this->events("cmd.php?postfix-multi-postqueue=MASTER",__FUNCTION__,__LINE__);
	$total=base64_decode($sock->getFrameWork("cmd.php?postfix-multi-postqueue=MASTER"));
	$this->events("EnablePostfixMultiInstance?",__FUNCTION__,__LINE__);
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
	if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}
	$ini_notifs=new Bs_IniHandler();
	$this->events("cmd.php?SmtpNotificationConfigRead=yes",__FUNCTION__,__LINE__);
	$ini_notifs->loadString($sock->getFrameWork("cmd.php?SmtpNotificationConfigRead=yes"));	
	if(!isset($ini_notifs->_params["SMTP"]["PostfixQueueMaxMails"])){$ini_notifs->_params["SMTP"]["PostfixQueueMaxMails"]=null;}
	$this->events("$stat",__FUNCTION__,__LINE__);
	$stat=explode(';',$stat);
	$this->_postfix_status=$stat[0];
	$textFONTStyle="font-size:11px";
	
	
	$link="Loadjs('postfix.queue.monitoring.php?js')";
	$logs="javascript:s_PopUp('postfix.events.php?pop=true',450,400);";
	$c=0;
	if($EnablePostfixMultiInstance==1){
		writelogs("EnablePostfixMultiInstance=1 $time=".strlen($_SESSION[md5(__CLASS__.__FUNCTION__)][$time])." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$ini_stats=new Bs_IniHandler();
		$ini_stats->loadString(base64_decode($sock->getFrameWork("cmd.php?postfix-single-status=yes")));
		$c=0;
		while (list ($key, $array) = each ($ini_stats->_params) ){
			if(preg_match("#POSTFIX-MULTI-#",$key,$re)){
				$c++;
				if($c>3){continue;}
				$multis[]=DAEMON_STATUS_LINE($key,$ini_stats,"noaccoloade",0,null,true);
			}
		}
	}
	
	if($c>3){
		
		$multis[]="<table style='width:98%;margin:0px;' 
		OnMouseOver=\"this.style.background='#D1D1D1';;this.style.cursor='pointer';\" 
		OnMouseOut=\"this.style.background='transparent';this.style.cursor='default';\"  
		OnClick=\"javascript:Loadjs('postfix.multiples.php')\"
		>
		<tr>
			<td width=1%><img src='img/loupe-32.png'></td>
			<td align='left' nowrap><strong>$c {instances}...</td>
		</tr>

	</table>
		";
	}
	
	
	if($usersmenus->DSTAT_INSTALLED){
		
		$dstat=texttooltip("{statistics}","{statistics}","Loadjs('dstat.postfix.php')");
		
	}
	
	
	if($usersmenus->AMAVIS_INSTALLED){
		if($usersmenus->EnableAmavisDaemon==1){		
		$amavis=texttooltip("{APP_AMAVISD_NEW}","{APP_AMAVIS_EXPLAIN}","Loadjs('amavis.index.php?ajax=yes')");
		}
	}
	$rpcess_number=texttooltip("{POSTFIX_PROCESS_NUMBER}","{POSTFIX_PROCESS_NUMBER}","Loadjs('postfix.process.number.php')");
	
	
	
	
	if(is_file(dirname(__FILE__).'/logs/artica-backup-size.ini')){
		$ini=new Bs_IniHandler(dirname(__FILE__).'/logs/artica-backup-size.ini');
		if($ini->_params["artica_backup"]["original_messages"]==null){$ini->_params["artica_backup"]["original_messages"]=0;}
		if($ini->_params["artica_backup"]["attachments"]==null){$ini->_params["artica_backup"]["attachments"]=0;}
		$size=$ini->_params["artica_backup"]["original_messages"]+$ini->_params["artica_backup"]["attachments"];
		$size=FormatBytes($size);
		$link2=CellRollOver("Loadjs('postfix.backup.monitoring.php')");
		if(!isset($this->array_queue["backup"])){$this->array_queue["backup"]=0;}
		if(!isset($this->array_queue["quarantine"])){$this->array_queue["quarantine"]=0;}
		
		
		$artica_backup="<tr>
						<td align='right' $link2 style='$textFONTStyle'>{backup_size}:</a>&nbsp;</td>
						<td $link2 ><strong style='$textFONTStyle'>$size</td>
						</tr>";
						
		if($this->array_queue["quarantine"]>0){
			$link_quarantine_progress=CellRollOver("Loadjs('postfix.quarantine.progress.php')");
		}
						
		
		
	}
	
$oldaddon="				<tr>
					<td align='right' $link>backup queue:</a>&nbsp;</td>
					<td $link><strong>{$this->array_queue["backup"]} </strong>emails</td>
				</tr>	
				<tr $link_quarantine_progress>
					<td align='right' nowrap>quarantine queue:</a>&nbsp;</td>
					<td nowrap><strong>{$this->array_queue["quarantine"]} </strong>emails</td>
				</tr>					
				$artica_backup";	
	
	
	$link_corrupt="Loadjs('postfix.corrupt.queue.php')";
	$link_incoming=CellRollOver("Loadjs('postfix.queue.monitoring.php?show-queue=incoming&count={$this->array_queue["incoming"]}');");
	$link_active=CellRollOver("Loadjs('postfix.queue.monitoring.php?show-queue=active&count={$this->array_queue["active"]}');");
	$link_deferred=CellRollOver("Loadjs('postfix.queue.monitoring.php?show-queue=deferred&count={$this->array_queue["deferred"]}');");
	
	if($ini_notifs->_params["SMTP"]["PostfixQueueMaxMails"]==null){$ini_notifs->_params["SMTP"]["PostfixQueueMaxMails"]=20;}
	$q=new mysql();
	$sql="SELECT SUM(`size`) as tsize,COUNT(msgid) as tcount FROM postqueue WHERE `instance`='master'";
	if(function_exists("mysql_fetch_array")){
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));	
	}
	$tot=$ligne["tcount"];
	$tot_size=$ligne["tsize"]/1024;
	$tot_size=FormatBytes($tot_size);
	
	
	if($ini_notifs->_params["SMTP"]["PostfixQueueMaxMails"]>0){
		if($tot>$ini_notifs->_params["SMTP"]["PostfixQueueMaxMails"]){
			$tot="<span style='font-size:12px;font-weight:bold;color:red'>$tot</span>";
		}
	}
		
	$table_quarantine=$q->COUNT_ROWS("quarantine","artica_backup");
	
	if($table_quarantine>0){$quarantines="
		<tr ". CellRollOver("Loadjs('domains.quarantine.php?js=yes&Master=yes')","{all_quarantines_text}").">
					<td align='right' style='$textFONTStyle'>{quarantine}:</a>&nbsp;</td>
					<td colspan=2 nowrap><strong style='$textFONTStyle'>". numberFormat($table_quarantine,0,'.',' ')."</strong>&nbsp;emails&nbsp;</td>
		</tr>";
	}		
	
	
	if($EnableArticaSMTPStatistics==1){
		$table_storage=$q->COUNT_ROWS("storage","artica_backup");
		$table_smtp_logs=$q->COUNT_ROWS("smtp_logs","artica_events");
		$sql="SELECT SUM( emails ) as tcount FROM smtp_logs_day WHERE bounce_error != 'Sended'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));	
		$rejected_number=$ligne["tcount"];
	
		if($rejected_number>0){$rejected="
			<tr ". CellRollOver("Loadjs('postfix.stats.cnx.php')","{rejected_mails}").">
						<td align='right' style='$textFONTStyle'>{rejected_mails}:</a>&nbsp;</td>
						<td colspan=2 nowrap><strong style='$textFONTStyle'>". numberFormat($rejected_number,0,'.',' ')."</strong>&nbsp;emails&nbsp;</td>
			</tr>";
		}		
		
	
		
		if($table_storage>0){$backup="
					<tr ". CellRollOver("Loadjs('domains.backup.php?js=yes&Master=yes')","{all_mailbackup_text}").">
						<td align='right' style='$textFONTStyle'>{backup}:</a>&nbsp;</td>
						<td colspan=2 nowrap><strong style='$textFONTStyle'>". numberFormat($table_storage,0,'.',' ')."</strong>&nbsp;emails&nbsp;</td>
					</tr>	";
		}	
	
		
		if($table_smtp_logs>0){$nb_events="
					<tr ". CellRollOver().">
						<td align='right' style='font-size:11px'>{processed}:</a>&nbsp;</td>
						<td nowrap><strong style='font-size:11px'>". numberFormat($table_smtp_logs,0,'.',' ')."</strong>&nbsp;emails</td>
						<td width=1%>". imgtootltip("icon_info.gif","{processed_messages_explain}","$link")."</td>
					</tr>	";
		}

	}

	$EnablePostfixAutoBlock=$sock->GET_INFO("EnablePostfixAutoBlock");
	if($EnablePostfixAutoBlock==1){
		$q=new mysql();
		$sql="SELECT COUNT(*) as tcount FROM iptables WHERE local_port=25 AND flux='INPUT' AND community=1";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$count_artica=$ligne["tcount"];
		$sql="SELECT COUNT(*) as tcount FROM iptables WHERE local_port=25 AND flux='INPUT' AND community IS NULL";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
		$count_local=$ligne["tcount"];

		$instantIptables="
				<tr><td colspan=2 style='padding-top:5px'><strong style='font-size:13px'><i>Instant Iptables:</i></strong></td></tr>
				<tr>
					<td align='right' style='$textFONTStyle'>{local}:</a>&nbsp;</td>
					<td nowrap><strong style='$textFONTStyle'>$count_local</strong>&nbsp;{rules}</td>
					<td width=1%>&nbsp;</td>
				</tr>
				<tr>
					<td align='right' style='$textFONTStyle'>{community}:</a>&nbsp;</td>
					<td nowrap><strong style='$textFONTStyle'>$count_artica</strong>&nbsp;{rules}</td>
					<td width=1%></td>
				</tr>";			
		
	}
			
	if($EnableArticaSMTPStatistics==0){$nb_events=null;$rejected=null;}
	
	$status_queues="
			<table>
				<tr ". CellRollOver().">
					<td align='right' style='$textFONTStyle' nowrap>{smtp_queues}:</a>&nbsp;</td>
					<td ><strong style='$textFONTStyle'>$tot</strong>&nbsp;{mails} <i style='font-size:9px'>($tot_size)</i></td>
					<td width=1%>". imgtootltip("icon_info.gif","{smtp_queues}","$link")."</td>
				</tr>
			$nb_events
			$backup
			$rejected
			$quarantines
			$instantIptables
			
			</table>";
	
	$queue="
	<table style='width:100%'>
	<tr>
	
	</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td>" . imgtootltip('22-logs.png','{events}',"PostfixPopupEvents()")."</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	";

	$sock=new sockets();
	$stat=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-stat=yes")));
	$EnablePostfixAutoBlock=$sock->GET_INFO("EnablePostfixAutoBlock");
	if($stat[0]==0){
		$ini=new Bs_IniHandler();
		$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?postfix-single-status=yes")));
	}
	
	switch ($stat[0]) {
		
		case -2:$img='i64.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{postfix_status}',$text);
			break;	
		
		case '-1':$img='status_postfix_bg_failed.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='status_postfix_bg_ok.png';
			if(preg_match("#2\.3.*#",$stat[1])){$img='status_postfix_bg_ok23.png';}
			if(preg_match("#2\.5.*#",$stat[1])){$img='status_postfix_bg_ok25.png';}
			if(preg_match("#2\.7.*#",$stat[1])){$img='status_postfix_bg_ok27.png';}
			if(preg_match("#2\.6.*#",$stat[1])){$img='status_postfix_bg_ok26.png';}			
			if(preg_match("#2\.8.*#",$stat[1])){$img='status_postfix_bg_ok28.png';}
			if(preg_match("#2\.9.*#",$stat[1])){$img='status_postfix_bg_ok29.png';}
			if(preg_match("#3\.0.*#",$stat[1])){$img='status_postfix_bg_ok30.png';}
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2];
			break;
		case '0':
			
			$img='status_postfix_bg_failed.png';
			$text="<span style=color:red>{service_stopped}<br>{using_version} " . $stat[1]."</span>";
			$start="<hr><div style='text-align:right'>".button("{start}","Loadjs('StartStopServices.php?APP=APP_POSTFIX&cmd=postfix-single&action=start')")."</div>";
					
			break;
	}
	
if(is_array($multis)){$multiples_instances=@implode("\n",$multis);}
	
	$_SESSION[md5("statusPostfix_satus")][$time]="
	<div style='background-image:url(img/$img);background-repeat:no-repeat;width:350px;height:270px'>
		<div style='padding-left:155px'>
			<H3 style='font-size:16px;font-weight:bold;'>{APP_POSTFIX}</h3>
			<span style='font-size:11px;font-weight:bold'>
				<i style='color:black;font-size:11px'>$text</i>$start
				$status_queues
				$multiples_instances
				</span>
				<div style='text-align:right'>
					<i style='font-size:9px'>{status}:{time} $time</i>
				</div>
		</div>
	</div>
	";
	
	
	//$_SESSION[md5(__CLASS__.__FUNCTION__)][$time]=$this->BuildDesign($img,'{postfix_status}',$text,"$amavis$dstat$rpcess_number","javascript:Loadjs('RTMMail.php')");
	@file_put_contents('/usr/share/artica-postfix/ressources/logs/postfix.status.html',$_SESSION[md5(__CLASS__.__FUNCTION__)][$time]);
	@chmod("/usr/share/artica-postfix/ressources/logs/postfix.status.html",0777);
	return $_SESSION[md5(__CLASS__.__FUNCTION__)][$time];
	
	
	
}
function fetchmail_satus(){
	$user=new usersMenus();
	$stat=trim($user->FETCHMAIL_STATUS);
	$stat=explode(';',$stat);
	$this->_postfix_status=$stat[0];
	
	switch ($stat[0]) {
		
		case null:$img='i24.png';
			$text='{no_infos}';
			return $this->BuildDesign($img,'{fetchmail_status}',$text);
			break;	
		
		case '-1':$img='warning24.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='ok24.png';
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2];
			break;
		case '0':$img='warning24.png';
			$text='{service_stopped}<br>{using_version} ' . $stat[1] . ' {pid} ' . $stat[2] . "<br><i style='color:red'>{$user->fetchmail_daemon_logs}</i>";
					
			break;
	}
	
	return RoundedLightGreen($this->BuildDesign($img,'{fetchmail_status}',$text));
	
	
}

function cyrus_satus(){
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	$title="{cyrus_status}";
	$init=basename($this->cyrus_init_d);
	$stat=trim($users->CYRUS_STATUS);
	$stat=explode(';',$stat);
	switch ($stat[0]) {
		case null:$img='i24.png';
			$text='{no_infos}';
			break;			
		case '-1':$img='i24.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='ok24.png';
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2] .$this->banner_services($init);
			break;
		case '0':$img='warning24.png';
			$text='{service_stopped}<br>{using_version} ' . $stat[1] . ' {pid} ' . $stat[2] .$this->banner_services($init);		
			break;
	}
	return $this->BuildDesign($img,$title,$text);
	
	
	
}



function avestatus_satus(){
	$stat=trim($this->sock->getfile('aveserver_status'));
	$stat=explode(';',$stat);
	$title="{aveserver_status}";
	switch ($stat[0]) {
		case null:$img='i24.png';
			$text='{no_infos}';
			break;			
		case '-1':$img='i24.png';
			$text='{service_not_installed}';
			break;
		case '1':$img='ok24.png';
			$date=$this->avestatus_pattern_date($stat[3],$stat[4]);
			$text='{service_running}<br>{using_version} ' . $stat[1]. ' {pid} ' . $stat[2]  .$date .$this->banner_services('aveserver');
			break;
		case '0':$img='warning24.png';
			$date=$this->avestatus_pattern_date($stat[3],$stat[4]);
			$text='{service_stopped}<br>{using_version} ' . $stat[1] . ' {pid} ' . $stat[2] . $date. $this->banner_services('aveserver');		
			break;
	}
	
	return $this->BuildDesign($img,$title,$text);
	
}


function avestatus_pattern_date($date,$time,$nobuild=0){
	if(preg_match('#([0-9]{2})([0-9]{2})([0-9]{4})#',$date,$reg)){
		$day=$reg[1];
		$month=$reg[2];
		$year=$reg[3];
	}else{return null;}
	
	if(preg_match('#([0-9]{2})([0-9]{2})#',$time,$reg)){
		$hour=$reg[1];
		$min=$reg[2];
		
	}else{return null;}	
	$date1 = mktime($hour-2,$min,0,$month,$day,$year);
	$time_date=date('Y/m/d H:i/s',$date1);
	
$date2 = mktime(date('H'),date('I'),0,date('m'),date('d'),date('Y'));
	$diff_date = $date2-$date1;
	$diff['H'] = (int)($diff_date/(60*60));
	$diff['D'] = (int)($diff_date/(60*60*24));
	if($diff['H'] >0){
		if($nobuild==0){
			$this->array_left[]=$this->BuildDesign_infos('warning32.png','{update_kaspersky_database_title}','{recommend_update_kaspersky}','ffa22a');
		}
	}
	if($nobuild==0){
	$text="<br>{pattern_update_date} :<strong>$time_date</stong><br></strong><i> {since} {$diff['D']} {days},  {$diff['H']} {hours} </i>";
	}else{
		$text="<strong>$time_date</stong><br></strong><i> {since} {$diff['D']} {days},  {$diff['H']} {hours}</i>";
	}
	return $text;
	
}

function dateadd($per,$n,$d) {
   switch($per) {
      case "yyyy": $n*=12;
      case "m":
         $d=mktime(date("H",$d),date("i",$d)
            ,date("s",$d),date("n",$d)+$n
            ,date("j",$d),date("Y",$d));
         $n=0; break;
      case "ww": $n*=7;
      case "d": $n*=24;
      case "h": $n*=60;
      case "n": $n*=60;
   }
   return $d+$n;
}

function BuildDesign($img,$title,$text,$letcon=null,$title_js=null){
	$h5_style=null;
	if($title_js<>null){
		$js_h5="OnClick=\"$title_js\" OnMouseOver=\"this.style.cursor='pointer'\"
		OnMouseOut=\"this.style.cursor='default\"";
		$h5_style="border-bottom:1px solid #CCCCCC;'";
		
		
	}
	
		$html="
	<table style='border:0px;margin:5px'>
	<td width=1% valign='top'>
		<img src='img/$img'>
		$letcon	
	</td>
		
	<td valign='top' width=100%>
	<table style='width:100%'>
		<tr>
			<td><h5 $title_js $h5_style>$title</h5></td>
		</tr>
		<tr>
			<td valign='top'>$text</td>
		</tr>
	</table>
	</td>
	</tr>
	</table>";
	return $html;
	
}
function BuildDesign_infos($img,$title,$text,$color){
		$html="
	<div style='border:1px solid #CCCCCC;margin-bottom:5px'>
		<div style='background-color:#$color;width:250px;height:20px;padding-top:5px;'>
			<img src='img/$img' align='left'><strong style='font-size:12px;color:white;padding-left:5px;'>$title</strong>
		</div>
		
	<div style='padding:10px;'>$text</div>
	</div>";
	
	return $html;
}

function banner_services($service){
	$start="<a href='#' OnClick=\"javascript:artica_service_start('$service','applications:$service');\"  onMouseOver=\"javascript:AffBulle('{action_start_service}');\" OnMouseOut=\"javascript:HideBulle();\">";
	$stop="<a href='#' OnClick=\"javascript:artica_service_stop('$service','applications:$service');\" onMouseOver=\"javascript:AffBulle('{action_stop_service}');\" OnMouseOut=\"javascript:HideBulle();\">";
	return
	"
	<table style='margin:0px;padding:0px;border:0px;width:90%;;border-top:1px solid #3939ac;margin-top:8px;'>
	<tr>
	<td align='right' style='padding:0px;margin:0px'>
	<table style='margin:0px;padding:0px;border:0px;width:1%' width=1%>
	<tr>
		<td width=1% style='margin:0px;padding:0px' nowrap>&nbsp;$start<img src='img/task-run.gif'></a>&nbsp;</td>
		<td width=1% style='margin:0px;padding:0px;' nowrap>&nbsp;$stop<img src='img/task-stop-on.gif'></a>&nbsp;</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>";
}

function ParseIniStatus(){
	if(!is_file($this->myini)){return "<tr><td>{error_waiting_sync}</td></tr>";}
	$ini=new Bs_IniHandler($this->myini);
	
	if($ini->_params["SQLGREY"]["sqlgrey_installed"]==1){
			$title="sqlgrey_satus";
			if($ini->_params["SQLGREY"]["master_pid"]<>null){
			$sqlgrey=$this->BuildLittleTemplate($title,true);}else{
			$sqlgrey=$this->BuildLittleTemplate($title,false);}
	}

if($ini->_params["POSTFIX"]["postfix_installed"]==1){
			$title="postfix_status";
			if($ini->_params["SQLGREY"]["master_pid"]<>null){
			$postfix=$this->BuildLittleTemplate($title,true);}else{
			$postfix=$this->BuildLittleTemplate($title,false);}
	}	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($postfix.$sqlgrey);			
	
}

public function BuildNewersions(){
	if(!is_file(dirname(__FILE__).'/index.ini')){
		$this->NewVersionsEvents("unable to stat ". dirname(__FILE__).'/index.ini');
	}
	
	if(!$this->front_page_notify()){
		@file_put_contents(dirname(__FILE__)."/logs/jGrowl-new-versions.txt","#");
		$this->NewVersionsEvents("front_page_notify return false");
		return;
	}
	
	$users=new usersMenus();
	$this->status_artica_version($users);
	$this->status_amavis_version($users);
	$this->status_dansguardian_version($users);
	$this->status_fetchmail_version($users);
	$this->status_squid_version($users);
	$this->status_nmap_version($users);
	$this->status_roundcube_version($users);
	$this->status_assp_version($users);
	$this->status_spamassassin($users);
	$datas=serialize($this->newversions);
	
	@file_put_contents(dirname(__FILE__)."/logs/jGrowl-new-versions.txt",$datas);
	@chmod(dirname(__FILE__)."/logs/jGrowl-new-versions.txt",0755);
	
}

private function front_page_notify(){
$sock=new sockets();	
$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/ArticaAutoUpdateConfig");
$front_page_notify=$ini->_params["AUTOUPDATE"]["front_page_notify"];
$this->NewVersionsEvents("front_page_notify():: front_page_notify=$front_page_notify");
if($front_page_notify==null){return true;}
if(strtolower($front_page_notify)=='yes'){return true;}
if(strtolower($front_page_notify)=='no'){return false;}
return true;
	
}

private function NewVersionsEvents($text){
	writelogs($text,__CLASS__.'/'."BuildNewersions",__FILE__,__LINE__);
}


private function status_amavis_version($users){
	if($users->KASPERSKY_SMTP_APPLIANCE){return false;}
	if(!$users->AMAVIS_INSTALLED){return false;}
	$localversion=$users->AMAVISD_VERSION;
	if($localversion==null){return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["amavisd-new"];
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
		
	if($remote_int>$local_int){
		$this->newversions["APP_AMAVISD_NEW"]=array(
			"TITLE"=>"{AMAVIS_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{AMAVIS_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_AMAVISD_NEW&start-install=yes"
		);
	}
}
private function status_dansguardian_version($users){
	$sock=new sockets();if($sock->GET_INFO('SQUIDEnable')==0){return false;}
	if(!$users->DANSGUARDIAN_INSTALLED){return false;}
	$localversion=$users->DANSGUARDIAN_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat local dansguardian version");return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["dansguardian"];
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");	
	if($remote_int>$local_int){
		$this->newversions["APP_DANSGUARDIAN"]=array(
			"TITLE"=>"{DANSGUARDIAN_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{DANSGUARDIAN_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_DANSGUARDIAN&start-install=yes"
		);		
		
	}
}

private function status_fetchmail_version($users){
	if(!$users->fetchmail_installed){$this->NewVersionsEvents(__FUNCTION__.":: fetchmail is not installed");return false;}
	$localversion=$users->FETCHMAIL_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat fetchmail version");return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["fetchmail"];
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");	
	if($remote_int>$local_int){
		$this->newversions["APP_FETCHMAIL"]=array(
			"TITLE"=>"{FETCHMAIL_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{FETCHMAIL_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_FETCHMAIL&start-install=yes"
		);	
	
	}
}

private function status_assp_version($users){
	if($users->KASPERSKY_SMTP_APPLIANCE){return false;}
	if(!$users->ASSP_INSTALLED){$this->NewVersionsEvents(__FUNCTION__.":: assp is not installed");return false;}
	$localversion=$users->ASSP_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat assp version");return null;}
	if(preg_match("#([0-9\.]+)#",$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["assp"];
	$this->NewVersionsEvents(__FUNCTION__.":: ASSP \"$remote_version\" On web server");
	
	if(preg_match("#([0-9\.]+)#",$remote_version,$re)){$remote_version=$re[1];}
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");	
	if($remote_int>$local_int){
		$this->newversions["APP_ASSP"]=array(
			"TITLE"=>"{ASSP_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{ASSP_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_ASSP&start-install=yes"
		);	
	
	}
}


private function status_squid_version($users){
	$sock=new sockets();if($sock->GET_INFO('SQUIDEnable')==0){return false;}
	if(!$users->SQUID_INSTALLED){$this->NewVersionsEvents(__FUNCTION__.":: squid is not installed");return false;}
	if(!is_file(dirname(__FILE__).'/index.ini')){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat ".dirname(__FILE__).'/index.ini');return null;}
	$localversion=$users->SQUID_VERSION;
	if($localversion==null){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat squid version");return null;}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["squid3"];
	$localversion=str_replace("STABLE","",$localversion);
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local $localversion against $remote_version ");
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$localversion,$re)){
		$localversion=$re[1].'.'.$re[2].'.'.$re[3];
		$local_major=$re[1];
		$local_minor=$re[2];
		$local_build=$re[3];
	}
	
	

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$remote_version,$re)){
		$remote_major=$re[1];
		$remote_minor=$re[2];
		$remote_build=$re[3];
		}
	$new=false;
	if($local_major<$remote_major){$new=true;}
	if(!$new){
		if($local_major==$remote_major){
			if($remote_minor>$local_minor){$new=true;}
		}
	}
	
	if(!$new){
		if($local_major==$remote_major){
			if($local_minor==$remote_minor){
				if($remote_build>$local_build){$new=true;}
			}
		}		
		
	}
	

	if($new){	
		$this->NewVersionsEvents(__FUNCTION__.":: new  $remote_version version");
		$this->newversions["APP_SQUID"]=array(
			"TITLE"=>"{SQUID_NEWVERSION}:&nbsp;$remote_version",
			"TEXT"=>"{SQUID_NEWVERSION_TEXT}",
			"JS"=>"setup.index.progress.php?product=APP_SQUID&start-install=yes"
		);			
		
	}else{
		$this->NewVersionsEvents(__FUNCTION__.":: no new  current $localversion version");
	}
}

private	function status_nmap_version($users){
		
		if(!$users->nmap_installed){
			$this->NewVersionsEvents(__FUNCTION__.":: NMAP is not installed");
			return false;}
			
		
		$localversion=$users->nmap_version;
		$this->NewVersionsEvents(__FUNCTION__.":: Local version is $users->nmap_version");	
	
		if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
		$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
		$remote_version=$ini->_params["NEXT"]["nmap"];
		
		if(preg_match('#([0-9]+)\.([0-9]+)$#',$localversion,$re)){
			$localversion="{$re[1]}.{$re[2]}0";
		}	
		
		$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version");	
		if(preg_match('#([0-9]+)\.([0-9]+)$#',$remote_version,$re)){
			$remote_version="{$re[1]}.{$re[2]}0";
		}
		
	
		
		$remote_int=str_replace('.','',$remote_version);
		$local_int=str_replace('.','',$localversion);
		
		$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
		
		if($remote_int>$local_int){
		$this->newversions["APP_NMAP"]=array(
				"TITLE"=>"{NMAP_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{NMAP_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_NMAP&start-install=yes"
			);			
		}
	}
	
private function status_roundcube_version($users){
	if(!$users->roundcube_installed){
		$this->NewVersionsEvents(__FUNCTION__.":: roundcube is not installed");
		return false;}
		
	if($users->ZARAFA_INSTALLED){return false;}
		
	if(!is_file(dirname(__FILE__).'/index.ini')){$this->NewVersionsEvents(__FUNCTION__.":: unable to stat ".dirname(__FILE__).'/index.ini');return null;}
	$localversion=$users->roundcube_intversion;
	
	if($GLOBALS["DEBUG"]){echo "local version:$localversion\n";}

	if($localversion==null){
		if($users->roundcube_version<>null){
			if(preg_match("#([0-9\.]+)#",$users->roundcube_version,$re)){
				$localversion=$re[1];
			}
		}
	}
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local int version is $localversion");
	$this->NewVersionsEvents(__FUNCTION__.":: Folder is $users->roundcube_folder");
	$this->NewVersionsEvents(__FUNCTION__.":: Local version is $users->roundcube_version");	

	if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["roundcubemail3"];
	$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version ");
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)$#',$remote_version,$re)){
		$this->NewVersionsEvents(__FUNCTION__.":: $remote_version -> MATCH 1");	
		$remote_version="{$re[1]}{$re[2]}0{$re[3]}";
		
	}elseif(preg_match('#([0-9]+)\.([0-9]+)$#',$remote_version,$re)){
		$this->NewVersionsEvents(__FUNCTION__.":: $remote_version -> MATCH 2");	
		$remote_version="{$re[1]}.{$re[2]}0";
	
	}elseif(preg_match('#([0-9]+)\.([0-9]+)$#',$remote_version,$re)){
		$this->NewVersionsEvents(__FUNCTION__.":: $remote_version -> MATCH 3");
		$remote_version="{$re[1]}.{$re[2]}0";
	}
	
	$this->NewVersionsEvents(__FUNCTION__.":: remote version (2) is $remote_version ");

	
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
	
	if($remote_int>$local_int){
		$this->NewVersionsEvents(__FUNCTION__.":: Build warning...");
		//APP_ROUNDCUBE3
		$this->newversions["APP_ROUNDCUBE3"]=array(
				"TITLE"=>"{ROUNDCUBE_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{ROUNDCUBE_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_ROUNDCUBE3&start-install=yes"
			);					
		
	}
	}

private function status_samba_version($users){	
	if(!$users->SAMBA_INSTALLED){$this->NewVersionsEvents(__FUNCTION__.":: Samba is not installed");return false;}
	$localversion=$users->SAMBA_VERSION;
	

	if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["samba"];
	
	
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$remote_version,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$remote_version="{$re[1]}.{$re[2]}.{$re[3]}";
	}
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$localversion,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$localversion="{$re[1]}.{$re[2]}.{$re[3]}";
	}	
	$this->NewVersionsEvents(__FUNCTION__.":: Local version is $localversion");
	$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version");

	
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
	
	if($remote_int>$local_int){
		$this->newversions["APP_SAMBA"]=array(
				"TITLE"=>"{SAMBA_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{SAMBA_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_SAMBA&start-install=yes"
			);		
		
	}
	}
	
private function status_spamassassin($users){
	if($users->KASPERSKY_SMTP_APPLIANCE){return false;}
	if(!$users->spamassassin_installed){$this->NewVersionsEvents(__FUNCTION__.":: SpamAssassin is not installed");return false;}
	$localversion=$users->spamassassin_version;
	

	if(preg_match('#([0-9\.]+)#',$localversion,$re)){$localversion=$re[1];}
	$ini=new Bs_IniHandler(dirname(__FILE__).'/index.ini');
	$remote_version=$ini->_params["NEXT"]["Mail-SpamAssassin"];
	
	
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$remote_version,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$remote_version="{$re[1]}.{$re[2]}.{$re[3]}";
	}
	
	if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$localversion,$re)){
		if(strlen($re[3])==1){$re[3]=$re[3]."0";}
		$localversion="{$re[1]}.{$re[2]}.{$re[3]}";
	}	
	$this->NewVersionsEvents(__FUNCTION__.":: Local version is $localversion");
	$this->NewVersionsEvents(__FUNCTION__.":: remote version is $remote_version");

	
	$remote_int=str_replace('.','',$remote_version);
	$local_int=str_replace('.','',$localversion);
	
	$this->NewVersionsEvents(__FUNCTION__.":: Local $local_int against $remote_int ");
	
	if($remote_int>$local_int){
		$this->newversions["APP_SPAMASSASSIN"]=array(
				"TITLE"=>"{SPAMASSASSIN_NEWVERSION}:&nbsp;$remote_version",
				"TEXT"=>"{SPAMASSASSIN_NEWVERSION_TEXT}",
				"JS"=>"setup.index.progress.php?product=APP_SPAMASSASSIN&start-install=yes"
			);		
		
	}
	}	

private function status_artica_version($users){
			$ini=new Bs_IniHandler(dirname(__FILE__)."/index.ini");
			$remote_version=$ini->_params["NEXT"]["artica"];
			$local_version=$users->ARTICA_VERSION;
			$remote_version_bin=str_replace('.','',$remote_version);
			$local_version_bin=str_replace('.','',$local_version);
			if($local_version<$remote_version){
			$this->newversions["APP_ARTICA"]=array(
				"TITLE"=>"{upgrade_artica}:&nbsp;$remote_version",
				"TEXT"=>"{upgrade_artica_text}",
				"JS"=>"artica.update.php?js=yes"
			);					
				
				
			}
		}	
}
function status_pgrep($command){
	$cmd="/usr/bin/pgrep -l -f \"$command\"";
	writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$vals);
	while (list ($num, $val) = each ($vals) ){
			writelogs("\"$val\" ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$val=trim($val);
			if($val==null){continue;}
			if(preg_match("#pgrep#",$val)){continue;}
			if(!preg_match('#([0-9]+)#',$val,$re)){continue;}
			writelogs("\"$val\" Processing {$val["master_pid"]} PID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(is_file("/proc/{$val["master_pid"]}/exe")){return true;}
		}
		return false;	
}
?>