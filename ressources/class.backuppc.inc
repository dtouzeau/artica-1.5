<?php
include_once(dirname(__FILE__).'/class.computers.inc');




class backuppc{
	var $hostname='';
	var $smb_folders=array();
	var $BlackoutPeriods_config=array();
	var $flat_configs=array();
	var $fullDatas="";
	var $smb_username="";
	var $smb_password="";
	function backuppc($uid){
		$cp=new computers($uid);
		$this->hostname=$cp->ComputerRealName;
		$ini=new Bs_IniHandler();
		$ini->loadString($computer->ComputerCryptedInfos);	
		$this->smb_username=$ini->_params["ACCOUNT"]["USERNAME"];
		$this->smb_password=$ini->_params["ACCOUNT"]["PASSWORD"];	
		$this->loadconfig();
		
	}
	
	
	private function loadconfig(){
		$sock=new sockets();
		$this->fullDatas=base64_decode($sock->getFrameWork("cmd.php?backuppc-comp=$this->hostname"));
		//echo $datas;
		
		$configs=array("FullPeriod","FullAgeMax","PartialAgeMax","RestoreInfoKeepCnt","IncrPeriod",
		"IncrKeepCntMin","IncrKeepCnt","IncrFill","IncrAgeMax","BlackoutGoodCnt","FullKeepCntMin");
		
		//echo $filecontent;
		if(preg_match('#\$Conf\{SmbShareName\}[\s\=]+\[(.+?)\];#is',$this->fullDatas,$re)){
			$f=str_replace("'",'',$re[1]);
			$folders=explode(",",$f);
			while (list ($index, $directory) = each ($folders) ){
				if(trim($directory)==null){continue;}
				$this->smb_folders[$directory]=$directory;
			}
		}
		
			
		while (list ($index, $key) = each ($configs) ){
				if(preg_match('#\$Conf\{'.$key.'\}[\s\=]+\'(.*?)\';#is',$this->fullDatas,$re)){
					$re[1]=str_replace("'",'',$re[1]);
					$this->flat_configs[$key]=trim($re[1]);}
			}
		
		$this->BlackoutPeriods();
		$this->DefaultConfig();
		
	}
	
	
	private function DefaultConfig(){
		if($this->flat_configs["BlackoutGoodCnt"]==null){$this->flat_configs["BlackoutGoodCnt"]=7;}
		if($this->flat_configs["FullPeriod"]==null){$this->flat_configs["FullPeriod"]=6.97;}
		if($this->flat_configs["FullAgeMax"]==null){$this->flat_configs["FullAgeMax"]=90;}
		if($this->flat_configs["PartialAgeMax"]==null){$this->flat_configs["PartialAgeMax"]=3;}
		if($this->flat_configs["RestoreInfoKeepCnt"]==null){$this->flat_configs["RestoreInfoKeepCnt"]=10;}
		if($this->flat_configs["IncrPeriod"]==null){$this->flat_configs["IncrPeriod"]=0.97;}
		if($this->flat_configs["IncrKeepCntMin"]==null){$this->flat_configs["IncrKeepCntMin"]=1;}
		if($this->flat_configs["IncrKeepCnt"]==null){$this->flat_configs["IncrKeepCnt"]=6;}
		if($this->flat_configs["IncrFill"]==null){$this->flat_configs["IncrFill"]=0;}
		if($this->flat_configs["IncrAgeMax"]==null){$this->flat_configs["IncrAgeMax"]=30;}
		if($this->flat_configs["FullKeepCntMin"]==null){$this->flat_configs["FullKeepCntMin"]=30;}
	}
	
	
	private function BlackoutPeriods(){
		if(preg_match('#\$Conf\{BlackoutPeriods\}[\s\=]+\[(.+?)\];#is',$this->fullDatas,$re)){
			$f=$re[1];
			if(preg_match_all("#\{(.+?)\}#is",$f,$re)){
				while (list ($index, $periods) = each ($re[1]) ){
					if(preg_match("#'hourEnd'.+?'(.+?)'#is",$periods,$ti)){
						$hourEnd=$ti[1];
						if(preg_match('#([0-9]+)\.#',$hourEnd,$tr)){$hourEnd=$tr[1];}
						$this->BlackoutPeriods_config[$index]["hourEnd"]=$hourEnd;
					}					
					if(preg_match("#'hourBegin'.+?'(.+?)'#is",$periods,$ti)){
						$hourBegin=$ti[1];
						if(preg_match('#([0-9]+)\.#',$hourBegin,$tr)){$hourBegin=$tr[1];}
						$this->BlackoutPeriods_config[$index]["hourBegin"]=$hourBegin;
					}
					if(preg_match("#'weekDays'.+?\[(.+?)\]#is",$periods,$ti)){
						$weeksdays=explode(",",str_replace("'",'',trim($ti[1])));
						while (list ($a, $b) = each ($weeksdays) ){$this->BlackoutPeriods_config[$index]["weekDays"][trim($b)]=1;}
					}					
				}
			}
			 
		}
	}
// ---------------------------------------------------------------------------------------
	public function Save(){
		$conf=array();
		if(is_array($this->smb_folders)){
			$conf[]="\$Conf{SmbShareName} = [";
			while (list ($folder, $key) = each ($this->smb_folders) ){
				$folder=trim($folder);
				$f[]="'$folder'";}
			$conf[]=@implode("\n,",$f)."\n];";
		}
		
		$conf[]="\$Conf{SmbSharePasswd} = '$this->smb_password';";
		$conf[]="\$Conf{SmbShareUserName} = '$this->smb_username';";
		$conf[]="\$Conf{BlackoutGoodCnt} = '{$this->flat_configs["BlackoutGoodCnt"]}';";
		$conf[]="\$Conf{IncrAgeMax} = '{$this->flat_configs["IncrAgeMax"]}';";
		$conf[]="\$Conf{IncrFill} = '{$this->flat_configs["IncrFill"]}';";
		$conf[]="\$Conf{IncrKeepCnt} = '{$this->flat_configs["IncrKeepCnt"]}';";
		$conf[]="\$Conf{IncrKeepCntMin} = '{$this->flat_configs["IncrKeepCntMin"]}';";
		$conf[]="\$Conf{IncrPeriod} = '{$this->flat_configs["IncrPeriod"]}';";
		$conf[]="\$Conf{IncrLevels} = ['1','2','3','4','5','6'];";		
		$conf[]="\$Conf{FullKeepCnt} = [4, 0, 4, 0, 0, 2];";
		$conf[]="\$Conf{FullKeepCntMin} = '{$this->flat_configs["FullKeepCntMin"]}';";
		$conf[]="\$Conf{FullPeriod} = '{$this->flat_configs["FullPeriod"]}';";
		$conf[]="\$Conf{BackupZeroFilesIsFatal} = '1';";
		
		
		
		if(is_array($this->BlackoutPeriods_config)){
			$conf[]="\$Conf{BlackoutPeriods} = [";
			while (list ($index, $array) = each ($this->BlackoutPeriods_config) ){
				
				$hourEnd=$array["hourEnd"];
				if(preg_match('#([0-9]+)\.#',$hourEnd,$tr)){$hourEnd=$tr[1];}
			
				$hourBegin=$array["hourBegin"];
				if(preg_match('#([0-9]+)\.#',$hourBegin,$tr)){$hourBegin=$tr[1];}
								
				$i[]="".chr(123);
				$i[]="\t'hourEnd' => '$hourEnd.0',";
				$i[]="\t'hourBegin' => '$hourBegin.0',";
				if(is_array($array["weekDays"])){
					while (list ($a, $b) = each ($array["weekDays"])){if(trim($a)<>null){$d[]="\t\t'$a'";}}
						$i[]="\t'weekDays' => [\n".@implode(",\n",$d)."\n\t]";
					unset($d);
					
				}
			 	$i[]="".chr(125).',';
				
			}
			$conf[]=@implode("\n",$i) ."\n];";
			unset($i);
		}
		
		$config=@implode("\n",$conf);
		file_put_contents(dirname(__FILE__)."/logs/$this->hostname.pl",$config);
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?backuppc-save-computer=$this->hostname");
		
		
		
	}
		
		
}

?>