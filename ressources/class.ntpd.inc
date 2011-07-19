<?php

class ntpd{
	var $ntpdConf;
	var $NTPDEnabled;
	var $servers;
	var $dn;
	
	function ntpd($noparse=false){

		$this->Load();
		if($noparse==false){
			writelogs("Parse servers...",__CLASS__ .'/'.__FUNCTION__ ,__FILE__);
			$this->ParseConfig();
		}
		
		
	}
	
	
	function Load(){
		$sock=new sockets();
		$this->ntpdConf=$sock->GET_INFO("NTPDConf");
		if(trim($this->ntpdConf)==null){$this->ntpdConf=$this->buildconf();}
		$this->NTPDEnabled=$sock->GET_INFO("NTPDEnabled");
		
	}
	
	function SaveToLdap(){
		
		$sock=new sockets();
		$sock->SaveConfigFile($this->buildconf(),"NTPDConf");
		$sock->SET_INFO("NTPDEnabled",$this->NTPDEnabled);
		$sock->getFrameWork("cmd.php?ntpd-restart=yes");
		
	}

		
		
	function ParseConfig(){
		$tbl=explode("\n",$this->ntpdConf);
		if(!is_array($tbl)){return null;}
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match('#^server\s+(.+)#',$val,$re)){
				$this->servers[]=$re[1];
			}
		}
		
		if(!is_array($this->servers)){$this->Builddefaults_servers();}
		
		
	}
	
	function BuildServers(){
		if(!is_array($this->servers)){$this->builddefaults_servers();}
		reset($this->servers);
			while (list ($num, $val) = each ($this->servers) ){
			if($val==null){continue;}
			writelogs("server $val",__CLASS__ .'/'.__FUNCTION__ ,__FILE__);
			 $conf=$conf . "server $val\n";
			
		}
		reset($this->servers);
		return $conf;
	}
	
	function builddefaults_servers(){
		$this->servers[]='0.debian.pool.ntp.org iburst';
		$this->servers[]='1.debian.pool.ntp.org iburst';
		$this->servers[]='2.debian.pool.ntp.org iburst';
		$this->servers[]='3.debian.pool.ntp.org iburst';
	}
	
	function buildconf(){

		
		
			$conf=$conf."driftfile /var/lib/ntp/ntp.drift\n";
			$conf=$conf."statsdir /var/log/ntpstats/\n";
			$conf=$conf."\n";
			$conf=$conf."statistics loopstats peerstats clockstats\n";
			$conf=$conf."filegen loopstats file loopstats type day enable\n";
			$conf=$conf."filegen peerstats file peerstats type day enable\n";
			$conf=$conf."filegen clockstats file clockstats type day enable\n";
			$conf=$conf."\n";
			$conf=$conf."\n";
			$conf=$conf."# You do need to talk to an NTP server or two (or three).\n";
			$conf=$conf."#server ntp.your-provider.example\n";
			$conf=$conf."\n";
			$conf=$conf."# pool.ntp.org maps to more than 300 low-stratum NTP servers.\n";
			$conf=$conf."# Your server will pick a different set every time it starts up.\n";
			$conf=$conf."#  *** Please consider joining the pool! ***\n";
			$conf=$conf."#  *** <http://www.pool.ntp.org/join.html> ***\n";
			$conf=$conf . $this->BuildServers();
			$conf=$conf."\n";
			$conf=$conf."# By default, exchange time with everybody, but don't allow configuration.\n";
			$conf=$conf."# See /usr/share/doc/ntp-doc/html/accopt.html for details.\n";
			$conf=$conf."restrict -4 default kod notrap nomodify nopeer noquery\n";
			$conf=$conf."restrict -6 default kod notrap nomodify nopeer noquery\n";
			$conf=$conf."\n";
			$conf=$conf."# Local users may interrogate the ntp server more closely.\n";
			$conf=$conf."restrict 127.0.0.1\n";
			$conf=$conf."restrict ::1\n";
			$conf=$conf."\n";
			$conf=$conf."# Clients from this (example!) subnet have unlimited access,\n";
			$conf=$conf."# but only if cryptographically authenticated\n";
			$conf=$conf."#restrict 192.168.123.0  mask  255.255.255.0 notrust\n";
			$conf=$conf."\n";
			$conf=$conf."# If you want to provide time to your local subnet, change the next line.\n";
			$conf=$conf."# (Again, the address is an example only.)\n";
			$conf=$conf."#broadcast 192.168.123.255\n";
			$conf=$conf."\n";
			$conf=$conf."# If you want to listen to time broadcasts on your local subnet,\n";
			$conf=$conf."# de-comment the next lines. Please do this only if you trust everybody\n";
			$conf=$conf."# on the network!\n";
			$conf=$conf."#disable auth\n";
			$conf=$conf."#broadcastclient\n";		
		return $conf;
		
	}
	
	
	function SaveToServer(){
		$sock=new sockets();
		$sock->getFrameWork('cmd.php?ntpd-restart=yes');
		}
		
	function ServersList(){
	   $array["France"][]="fr.pool.ntp.org";		
	   $array["France"][]="0.fr.pool.ntp.org";
	   $array["France"][]="1.fr.pool.ntp.org";
	   $array["France"][]="2.fr.pool.ntp.org";
	   $array["France"][]="3.fr.pool.ntp.org";

 		$array["Italy"][]="fr.pool.ntp.org";	   
 		$array["Italy"][]="0.it.pool.ntp.org";
 		$array["Italy"][]="1.it.pool.ntp.org";
 		$array["Italy"][]="2.it.pool.ntp.org";
		$array["Italy"][]="3.it.pool.ntp.org";

	   $array["Portugal"][]="pt.pool.ntp.org";		
	   $array["Portugal"][]="1.pt.pool.ntp.org";
	   $array["Portugal"][]="3.europe.pool.ntp.org";
	   $array["Portugal"][]="0.europe.pool.ntp.org";
		
		$array["Spain"][]="es.pool.ntp.org";
		$array["Spain"][]="0.es.pool.ntp.org";
		$array["Spain"][]="3.europe.pool.ntp.org";
		$array["Spain"][]="0.europe.pool.ntp.org";
	   
		$array["Germany"]="0.de.pool.ntp.org";
		$array["Germany"]="1.de.pool.ntp.org";
		$array["Germany"]="2.de.pool.ntp.org";
		$array["Germany"]="3.de.pool.ntp.org";

		$array["India"]="in.pool.ntp.org";		
		$array["India"]="2.in.pool.ntp.org";
		$array["India"]="3.asia.pool.ntp.org";
		$array["India"]="2.asia.pool.ntp.org";

		$array["Canada"]="ca.pool.ntp.org";		
		$array["Canada"]="0.ca.pool.ntp.org";
		$array["Canada"]="1.ca.pool.ntp.org";
		$array["Canada"]="2.ca.pool.ntp.org";
		$array["Canada"]="3.ca.pool.ntp.org";

		$array["United States"]="us.pool.ntp.org";		
		$array["United States"]="0.us.pool.ntp.org";
		$array["United States"]="1.us.pool.ntp.org";
		$array["United States"]="2.us.pool.ntp.org";
		$array["United States"]="3.us.pool.ntp.org";
		
		$array["Mexico"]="mx.pool.ntp.org";	
		$array["Mexico"]="0.north-america.pool.ntp.org";
		$array["Mexico"]="1.north-america.pool.ntp.org";
		$array["Mexico"]="2.north-america.pool.ntp.org";
		$array["Mexico"]="3.north-america.pool.ntp.org";

		$array["Brazil"]="br.pool.ntp.org";		
		$array["Brazil"]="3.br.pool.ntp.org";
		$array["Brazil"]="0.south-america.pool.ntp.org";
		$array["Brazil"]="3.south-america.pool.ntp.org";

		$array["Chile"]="cl.pool.ntp.org";		
		$array["Chile"]="0.south-america.pool.ntp.org";
		$array["Chile"]="1.south-america.pool.ntp.org";
		$array["Chile"]="2.south-america.pool.ntp.org";
		$array["Chile"]="3.south-america.pool.ntp.org";

		$array["Argentina"]="ar.pool.ntp.org";		
		$array["Argentina"]="0.ar.pool.ntp.org";
		$array["Argentina"]="1.south-america.pool.ntp.org";
		$array["Argentina"]="3.south-america.pool.ntp.org";
		
		$array["Venezuela"]="ve.pool.ntp.org";	
		$array["Venezuela"]="0.south-america.pool.ntp.org";
		$array["Venezuela"]="1.south-america.pool.ntp.org";
		$array["Venezuela"]="2.south-america.pool.ntp.org";
		$array["Venezuela"]="3.south-america.pool.ntp.org";
		
		$array["Tanzania"]="tz.pool.ntp.org";
		$array["Tanzania"]="0.africa.pool.ntp.org";
		$array["Tanzania"]="1.africa.pool.ntp.org";
		$array["Tanzania"]="2.africa.pool.ntp.org";
		$array["Tanzania"]="3.africa.pool.ntp.org";
		
		$array["Russian Federation"]="ru.pool.ntp.org";		
		$array["Russian Federation"]="0.ru.pool.ntp.org";
		$array["Russian Federation"]="1.ru.pool.ntp.org";
		$array["Russian Federation"]="2.ru.pool.ntp.org";
		$array["Russian Federation"]="3.ru.pool.ntp.org";
		
		return $array;
		
	}
		
		

		
	
}
//  /opt/artica/bin/ntpq -n -c rv 127.0.0.1
// /opt/artica/bin/ntpd -c /opt/artica/etc/ntpd.conf -p /var/run/ntpd.pid