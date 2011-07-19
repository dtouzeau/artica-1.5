<?php

class clamav{
	var $pattern_def=array();
	var $freshclam_array=array();
	var $freshclam_mirrors=array();
	var $freshclam_mirrors_selected=array();
	function clamav(){
		$this->pattern_def=array(
			"phish.ndb"=>"{phish_ndb}",
			"scam.ndb"=>"{scam_ndb}",
			"junk.ndb"=>"{junk_ndb}",
			"spear.ndb"=>"{spear_ndb}",
			"rogue.hdb"=>"{rogue_hdb}",
			"spamimg.hdb"=>"{spamimg_hdb}",
			"lott.ndb"=>"{lott_ndb}",
			"spam.ldb"=>"{spam_ldb}",
			"mbl.db"=>"{security_info} <b>Viruses, Trojans, Worms, or Malware</b>",
			"antispam.ndb"=>"Anti-Spam",
			"winnow_malware.hdb"=>"{winnow_malware_hdb}",
			"winnow_phish_complete.ndb"=>"{winnow_phish_complete_ndb}",
			"winnow_phish_complete_url.ndb"=>"{winnow_phish_complete_url_ndb}",
			"vx.hdb" =>"{security_info} <b>VX</b>",
			"honeynet.hdb"=>"{security_info} <b>Old malwares not detected</b>",
			"securiteinfo.hdb"=>"{security_info} <b>Malwares in the Wild</b>",
			"securiteinfoelf.hdb"=>"{security_info} <b>Malwares ELF (Linux executables)</b>",
			"securiteinfodos.hdb"=>"{security_info} <b>Malwares MS-DOS</b>",
			"securiteinfobat.hdb"=>"{security_info} <b>Malwares BAT</b>",
			"securiteinfohtml.hdb"=>"{security_info} <b>Malwares HTML</b>",
			"securiteinfooffice.hdb"=>"{security_info} <b>Malwares Macros Office</b>",
			"securiteinfopdf.hdb"=>"{security_info} <b>Malwares PDF</b>",
		
		
		
			"MSRBL-Images.hdb"=>"{MSRBL-Images_hdb}",
			"MSRBL-SPAM.ndb"=>"{MSRBL-SPAM_ndb}",
			"main.cld"=>"<b>{main_cld}</b>",
			"main.cvd"=>"<b>{main_cld}</b>",
			"daily.cld"=>"<b>{daily_cld}</b>"
			);
			
		
		
		$this->LoadFreshClamConfig();
		$this->freshclam_mirrors=$this->LoadSitesList();
		
	}
	
	
	function LoadDatabasesStatus(){
		$cachefile=dirname(__FILE__).'/logs/pattern.status.ini';
		if(!is_file($cachefile)){
			$sock=new sockets();
			$datas=$sock->getFrameWork("cmd.php?clamav-av-pattern-status=yes");
			$ini=new Bs_IniHandler();
			$ini->loadString($datas);
		}else{
			$ini=new Bs_IniHandler($cachefile);
		}
		
		
		$array=$ini->_params["CLAMAV"];
		if(!is_array($array)){return $array;}
		while (list ($file, $date) = each ($array) ){
			if(!preg_match('#([0-9]+)-([0-9]+)-([0-9]+)\s+(.+)#',$date,$re)){continue;}
			if($this->pattern_def[$file]==null){continue;}
			$day=$re[1];
			$month=$re[2];
			$year=$re[3];
			$count=$count+1;
			if(strlen($day)==1){$day="0{$day}";}
			if(strlen($month)==1){$month="0{$month}";}
			if(strlen($year)==2){$year="20{$year}";}
			$datef="{$year}-{$month}-{$day} {$re[4]}";
			$rarray["{$year}{$month}{$day}$count"]=array($this->pattern_def[$file],$datef);
			
			
		}
	
		
	
	return  $rarray;}
	
	
	
	function LoadFreshClamConfig(){
		$sock=new sockets();
		$data=$sock->GET_INFO("freshclamConfig");
		$tbl=explode("\n",$data);
		
			while (list ($num, $line) = each ($tbl) ){
				
			if(preg_match('#^DatabaseMirror\s+(.+)#',$line,$re)){
					$this->freshclam_mirrors_selected[trim(strtolower($re[1]))]=trim($re[1]);
					continue;
				}				
				
				if(preg_match("#^(.+?)\s+(.+)#",$line,$re)){
					$this->freshclam_array[trim($re[1])]=trim($re[2]);
				}
				
				
			}
			
			$this->frechclamdefaults();
		}
		
	function freshclam_save(){
		while (list ($num, $line) = each ($this->freshclam_array) ){
			$conf=$conf . "$num $line\n";
		}
		
		if(is_array($this->freshclam_mirrors_selected)){
			while (list ($num, $line) = each ($this->freshclam_mirrors_selected) ){
				$conf=$conf . "DatabaseMirror $num\n";
			}
			
		}else{
			$conf=$conf . "DatabaseMirror db.gb.clamav.net\n";
		}
		
		$sock=new sockets();
		$sock->SaveConfigFile($conf,"freshclamConfig");
		$sock->getfile('FreshClamSave');
	}
	
	function frechclamdefaults(){
		$user="clamav";
		$users=new usersMenus();
		if($users->POSTFIX_INSTALLED){$user="postfix";}
		if($users->CLAMD_CONF_PATH==null){$users->CLAMD_CONF_PATH="/etc/clamav/clamd.conf";}
		if($users->CLAMD_DATABASE_PATH==null){$users->CLAMD_DATABASE_PATH="/var/lib/clamav";}
		
		
		
		if($this->freshclam_array["PidFile"]==null){$this->freshclam_array["PidFile"]="/var/run/clamav/freshclam.pid";}
		if($this->freshclam_array["UpdateLogFile"]==null){$this->freshclam_array["UpdateLogFile"]="/var/log/clamav/freshclam.log";}
		$this->freshclam_array["NotifyClamd"]=$users->CLAMD_CONF_PATH;
		$this->freshclam_array["LogSyslog"]="true";
		$this->freshclam_array["DatabaseDirectory"]=$users->CLAMD_DATABASE_PATH;		
		$this->freshclam_array["DatabaseOwner"]=$user;
		
		if($this->freshclam_array["Checks"]==null){$this->freshclam_array["Checks"]="12";}
		if($this->freshclam_array["MaxAttempts"]==null){$this->freshclam_array["MaxAttempts"]="3";}
		}
		
	function LoadSitesList(){
		$array["USA"][]="clamav.catt.com";
		$array["USA"][]="clamav.clearfield.com ";
		$array["USA"][]="clamav.edebris.com ";
		$array["USA"][]="clamav.liquidweb.com";
		$array["USA"][]="clamav.myriadnetwork.com";
		$array["USA"][]="clamav.swishmail.com";
		$array["USA"][]="clamav.theshell.com";
		$array["USA"][]="clamav-du.viaverio.com";
		$array["USA"][]="clamav-sj.viaverio.com ";
		$array["USA"][]="clamav.mirror.dtcc.edu";
		$array["USA"][]="clamav.cs.utah.edu";
		$array["USA"][]="clamav.inoc.net";
		$array["USA"][]="clamav.securityminded.net";
		$array["USA"][]="clamav-mirror.sonic.net";		
		$array["UK"][]="clamav.anlx.net";
		$array["UK"][]="clamav.spod.org";
		$array["UK"][]="clamav.oucs.ox.ac.uk";
		$array["UK"][]="clamav.public-internet.co.uk";	

		$array["SPAIN"][]="clamav.irontec.com";	
		$array["SPAIN"][]="clamav.gva.es";	
		$array["SPAIN"][]="tumeric.us.es";	
		$array["SPAIN"][]="clamav.n2000.net";			
		
		$array["RUSSIA"][]="clamav.citrin.ru";
		$array["RUSSIA"][]="clamav.eastweb.ru";
		$array["RUSSIA"][]="clamav.industrium.ru";
		$array["RUSSIA"][]="db.ezhik.org.ru";
		$array["RUSSIA"][]="clamav.unix.su";

		$array["PORTUGAL"][]="clamav.awd.pt";
		$array["PORTUGAL"][]="clamav.linux.pt";

		$array["NETHERLANDS"][]="clamav.cambrium.nl";
		$array["NETHERLANDS"][]="clamav.mirror.easycolocate.nl";
		$array["NETHERLANDS"][]="clamav.mirror.evertjenetworks.nl";
		$array["NETHERLANDS"][]="clamav.mirror.omroep.nl";
		$array["NETHERLANDS"][]="clamav.mirror.transip.nl";
		$array["NETHERLANDS"][]="clamav.unnet.nl";
		$array["NETHERLANDS"][]="clamav.xs4all.nl";

		$array["JAPAN"][]="clamav-jp.viaverio.com";
		$array["JAPAN"][]="clamav.nara.wide.ad.jp";
		$array["JAPAN"][]="clamavdb1.ml-club.jp";
		$array["JAPAN"][]="clamavdb2.ml-club.jp";
		$array["JAPAN"][]="clamav.begi.net";
		$array["JAPAN"][]="clamavdb.osj.net";
		$array["JAPAN"][]="clamav.s-lines.net";
		$array["JAPAN"][]="clamav.yukiguni.net";
		$array["JAPAN"][]="clamavdb.mithril-linux.org";

		$array["ITALY"][]="clamav.mirror.garr.it";
		$array["ITALY"][]="clamav.linux.it";
		
		$array["HONGKONG"][]="clamav.cpss.edu.hk";
		$array["HONGKONG"][]="clamav.website-solution.net";

		$array["GERMANY"][]="clamav.datev.de";
		$array["GERMANY"][]="mirror16.clamav.exozet.de";
		$array["GERMANY"][]="clamav.ganneff.de";
		$array["GERMANY"][]="clamav.i-pex.de";
		$array["GERMANY"][]="clamav.mcs.de";
		$array["GERMANY"][]="clamav.netcologne.de";
		$array["GERMANY"][]="clamav.pcn.de";
		$array["GERMANY"][]="clamav.savework.de";
		$array["GERMANY"][]="clamav.spline.de";
		$array["GERMANY"][]="clamav.yomiko.de";
		$array["GERMANY"][]="clamav.internet24.eu";
		$array["GERMANY"][]="clamav.incertum.net";
		$array["GERMANY"][]="clamav.mirror.iphh.net";
		$array["GERMANY"][]="clamav.gencore.org";

		$array["FRANCE"][]="clamav.yolker.be";
		$array["FRANCE"][]="clamav.cisneo.fr";
		$array["FRANCE"][]="clamav.easynet.fr";
		$array["FRANCE"][]="clamav.univ-nantes.fr";
		$array["FRANCE"][]="clamav.kazar.org";

		$array["BELGIUM"][]="clamav.edpnet.net";
		$array["CHINA"][]="at1.clamav.ialfa.net";
		$array["DENMARK"][]="clamav.mirrors.siminn.dk";
		$array["INDONESIA"][]="db.clamav.or.id";
		$array["KOREA"][]="clamav.hostway.co.kr";
		$array["MALAYSIA"][]="clamav.doubleukay.com";
		$array["MALTA"][]="clamav.nic.org.mt";
		
		 
		
		return $array;
		
		
		
	}
	
}


?>