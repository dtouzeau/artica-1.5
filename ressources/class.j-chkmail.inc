<?php
include_once(dirname(__FILE__).'/class.main_cf.inc');
include_once(dirname(__FILE__).'/class.tcpip.inc');
include_once(dirname(__FILE__).'/class.templates.inc');


class jchkmail{
	var $policy_datas;
	var $CONF_DATAS;
	var $CONF=array();
	var $BadMX=array();
	var $NetClass=array();
	var $RejectShortMsgs=array();
	var $Archive=array();
	var $NetClassNames=array();
	var $NetClassDefaults=array("LOCAL"=>True,"DOMAIN"=>true,"FRIEND"=>true,"AUTH"=>true,"DEFAULT"=>true,"dnsbl"=>true);
	var $NetClassNoFill=array("DEFAULT"=>true,"dnsbl"=>true);
	var $FieldElements=array("NO"=>"{eNO}","YES"=>"{eYES}","NO-QUICK"=>"{eNOQUICK}","YES-QUICK"=>"{eYESQUICK}");
	var $GreyCheckConnect=array();
	var $Rates=array();
	var $Rates_text=array("ConnRate"=>"{ConnRate}","ConnOpen"=>"{ConnOpen}","MsgRate"=>"{MsgRate}","RcptRate"=>"{RcptRate}","MaxRcpt"=>"{MaxRcpt}","MaxMsgs"=>"{MaxMsgs}");
	function jchkmail(){
		$this->LoadConfigs();
		
		
	}
	
	function Save($output=true){
		if(is_array($this->Rates)){reset($this->Rates);}
		if(is_array($this->NetClass)){reset($this->NetClass);}
		$network=$this->BuildNetworks();
		$badmx=$this->BuildBadMX();
		$greylist=$this->BuildGreyCheckConnect();
		$Rates=$this->BuildRates();
		$RejectShortMsgs=$this->BuildRejectShortMsgs();
		$Archive=$this->BuildArchive();
		$this->policy_datas="$network$badmx$greylist$Rates$RejectShortMsgs$Archive$this->policy_datas";
		$sock=new sockets();
		$this->BuildConfigFile();
		$sock->SaveConfigFile($this->policy_datas,"jcheckMailPolicyTXT");
		$sock->SaveConfigFile($this->CONF_DATAS,"jcheckMailConf");
		$sock->getfile('SaveJchkmailConfig');
		if($output){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{APP_JCHECKMAIL} {success}\n");
		}
		
	}
	
	
	function LoadConfigs(){
		$sock=new sockets();
		$this->policy_datas=$sock->GET_INFO('jcheckMailPolicyTXT');
		$this->CONF_DATAS=$sock->GET_INFO('jcheckMailConf');
		if($this->policy_datas==null){$this->policy_datas=$sock->getfile("GetjchkMailOrgConfig:j-policy.txt");}
		if($this->CONF_DATAS==null){$this->CONF_DATAS=$sock->getfile("GetjchkMailOrgConfig:j-chkmail.cf");}
		$this->ParsePolicyFile();
		$this->ParseConfigFile();
	}
	
	
	function ParseConfigFile(){
		if($this->CONF_DATAS==null){echo "<H1>CONF_DATAS=NULL</H1>";exit;}
		$tbl=explode("\n",$this->CONF_DATAS);
		while (list ($num, $line) = each ($tbl) ){
			if(trim($line)==null){continue;}
			if(substr($line,0,1)=='#'){continue;}
			
			if(preg_match("#^([A-Z_a-z]+)\s+(.+)#",$line,$re)){
				$this->CONF[$re[1]]=trim($re[2]);
			}
			
		}
	}
	
	function BuildConfigFile(){
		$this->CONF_DATAS=null;
		if(!is_array($this->CONF)){die("Fatal error (CONF=NULL)");}
		
		
		$users=new usersMenus();
		if($users->CLAMAV_INSTALLED){
			$this->CONF["SCANNER_SOCK"]="local:$users->CLAMAV_SOCKET";
			$this->CONF["SCANNER_PROTOCOL"]="CLAMAV";
		}
		
	   while (list ($num, $line) = each ($this->CONF) ){
			$this->CONF_DATAS=$this->CONF_DATAS . "$num\t\t$line\n";
		}
		
		
	}
	
	
	function ParsePolicyFile(){
		$this->NetClassNames=$this->NetClassDefaults;
		$tbl=explode("\n",$this->policy_datas);
		while (list ($num, $line) = each ($tbl) ){
			if(preg_match('#^BadMX:(.+?)\s+(.+)#',$line,$re)){
				$this->BadMX[]=array($re[1],$re[2]);
				unset($tbl[$num]);
			}
			if(preg_match('#^NetClass:(.+?)\s+(.+)#',$line,$re)){
				$this->NetClassNames[trim($re[2])]=true;
				$this->NetClass[$re[1]]=trim($re[2]);
				unset($tbl[$num]);
			}
			
			if(preg_match('#^GreyCheckConnect:(.+?)\s+(.+)#',$line,$re)){
				$this->GreyCheckConnect[$re[1]]=array(trim($re[2]),"GreyCheckConnect");
				unset($tbl[$num]);
			}
			
			if(preg_match('#^GreyCheckFrom:(.+?)\s+(.+)#',$line,$re)){
				$this->GreyCheckConnect[$re[1]]=array(trim($re[2]),"GreyCheckFrom");
				unset($tbl[$num]);
			}	
			
			if(preg_match('#^GreyCheckTo:(.+?)\s+(.+)#',$line,$re)){
				$this->GreyCheckConnect[$re[1]]=array(trim($re[2]),"GreyCheckTo");
				unset($tbl[$num]);
			}

			if(preg_match('#^ConnRate:(.+?)\s+(.+)#',$line,$re)){
				$this->Rates["ConnRate"][$re[1]]=$re[2];
				unset($tbl[$num]);
			}

			if(preg_match('#^ConnOpen:(.+?)\s+(.+)#',$line,$re)){
				$this->Rates["ConnOpen"][$re[1]]=$re[2];
				unset($tbl[$num]);
			}	
			

			if(preg_match('#^MsgRate:(.+?)\s+(.+)#',$line,$re)){
				$this->Rates["MsgRate"][$re[1]]=$re[2];
				unset($tbl[$num]);
			}	

			if(preg_match('#^RcptRate:(.+?)\s+(.+)#',$line,$re)){
				$this->Rates["RcptRate"][$re[1]]=$re[2];
				unset($tbl[$num]);
			}
			if(preg_match('#^MaxRcpt:(.+?)\s+(.+)#',$line,$re)){
				$this->Rates["MaxRcpt"][$re[1]]=$re[2];
				unset($tbl[$num]);
			}
			if(preg_match('#^MaxMsgs:(.+?)\s+(.+)#',$line,$re)){
				$this->Rates["MaxMsgs"][$re[1]]=$re[2];
				unset($tbl[$num]);
			}

			if(preg_match('#^RejectShortMsgs(To|Connect|From):(.+?)\s+(.+)#',$line,$re)){
				$this->RejectShortMsgs[$re[1]][$re[2]]=trim($re[3]);
				unset($tbl[$num]);
			}
			if(preg_match('#^Archive(To|Connect|From):(.+?)\s+(.+)#',$line,$re)){
				$this->Archive[$re[1]][$re[2]]=trim($re[3]);
				unset($tbl[$num]);
			}						
			
					
			
					
					
			
					
			
			if(trim($line)==null){unset($tbl[$num]);}
			
		}
		$this->policy_datas=implode("\n",$tbl);
	}
	
	function BuildGreyCheckConnect(){
		if(!is_array($this->GreyCheckConnect)){return null;}
		while (list ($num, $ligne) = each ($this->GreyCheckConnect) ){
			$line=$line ."{$ligne[1]}:$num\t\t{$ligne[0]}\n";
			
		}
		return $line;
	}
	
	
	Function BuildRejectShortMsgs(){
		if(!is_array($this->RejectShortMsgs)){return null;}
		while (list ($num, $ligne) = each ($this->RejectShortMsgs) ){
			$element=$num;
			while (list ($pattern, $action) = each ($ligne) ){
				$line=$line ."RejectShortMsgs{$element}:$pattern\t\t{$action}\n";
			}
		}
		return $line;
	}
	
	Function BuildArchive(){
		if(!is_array($this->Archive)){return null;}
		while (list ($num, $ligne) = each ($this->Archive) ){
			$element=$num;
			while (list ($pattern, $action) = each ($ligne) ){
				$line=$line ."Archive{$element}:$pattern\t\t{$action}\n";
			}
		}
		return $line;
	}	
	
	function BuildBadMX(){
		if(!is_array($this->BadMX)){return null;}
		while (list ($num, $ligne) = each ($this->BadMX) ){
			$line=$line ."BadMX:{$ligne[0]}\t\t{$ligne[1]}\n";
			
		}
		
		return $line;
		
	}
	
	function BuildRates(){
		if(!is_array($this->Rates)){return null;}
		while (list ($num, $ligne) = each ($this->Rates) ){
				$RateFamily=$num;
				while (list ($NetClass, $Rate) = each ($ligne) ){
					$conf=$conf ."$RateFamily:$NetClass\t\t$Rate\n";
					
				}
		}
		
		return $conf;
		
		
	}
	
	function GetClassList($NetClassFamily){
		if(!is_array($this->NetClass)){return array();}
		reset($this->NetClass);
		while (list ($num, $ligne) = each ($this->NetClass) ){
			if($ligne==$NetClassFamily){
				$arr[$num]=true;
			}
		}
		return $arr;
	}
	
	function DeleteClassList($NetClassFamily){
			writelogs("($NetClassFamily) deleting this->NetClass",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(is_array($this->NetClass)){
			reset($this->NetClass);
			while (list ($num, $ligne) = each ($this->NetClass) ){
					
				if($num==$NetClassFamily){
					$data=$this->NetClass[$num];
					writelogs("($NetClassFamily) deleting this->NetClass[$num]=$data",__CLASS__.'/'.__FUNCTION__,__FILE__);
					unset($this->NetClass[$num]);
				}
			}
		}
		
		if(is_array($this->Rates)){
			reset($this->Rates);
			while (list ($num, $ligne) = each ($this->Rates) ){
				$RateFamily=$num;
				while (list ($NetClass, $Rate) = each ($ligne) ){
					IF($NetClass==$NetClassFamily){
						writelogs("($NetClassFamily) deleting this->Rates[$num][$NetClass]",__CLASS__.'/'.__FUNCTION__,__FILE__);
						unset($this->Rates[$num][$NetClass]);
					}
				}
			}
		}
		
		$this->Save();
		}	
	
	
	function BuildNetworks(){
		$ip=new IP();
		$main=new main_cf();
		if(is_array($main->array_mynetworks)){
		while (list ($num, $val) = each ($main->array_mynetworks) ){
			$val=trim($val);
			if(preg_match('#(.+?)/(.+)#',$val,$re)){
				$val=$this->StripZero($re[1]);
				$this->NetClass[$val]="LOCAL";
				continue;
				}

		if($ip->isIPAddress($val)){
			if($val=="127.0.0.1"){
				$this->NetClass[$val]="LOCAL";
				continue;
			}
			if(preg_match("#(.+?)\.([0-9]+)$#",$val,$re)){
				$this->NetClass[$re[1]]="FRIEND";
				}
			}
		}
	}
	if(!is_array($this->NetClass)){return null;}
	while (list ($num, $ligne) = each ($this->NetClass) ){
			$conf=$conf ."NetClass:$num\t\t$ligne\n";
			
		}
	
	
		return $conf;	
	}
	
	
	function StripZero($ip){
		preg_match('#(.+?)\.(.+?)\.(.+?)\.(.+)#',$ip,$re);
			if($re[4]==0){unset($re[4]);}
			if($re[3]==0){unset($re[3]);}
			if($re[2]==0){unset($re[2]);}
			unset($re[0]);
			return implode(".",$re);
		
		
	}
	
	
	
	
	
	
	
	
	
	
}
?>