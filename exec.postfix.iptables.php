<?php

$_GET["filelogs"]="/var/log/artica-postfix/iptables.debug";
$_GET["filetime"]="/etc/artica-postfix/croned.1/".basename(__FILE__).".time";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.iptables-chains.inc');
include_once(dirname(__FILE__) . '/ressources/class.baseunix.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');

$sock=new sockets();
$GLOBALS["EnablePostfixAutoBlock"]=trim($sock->GET_INFO("EnablePostfixAutoBlock"));
if($GLOBALS["EnablePostfixAutoBlock"]==null){$GLOBALS["EnablePostfixAutoBlock"]=0;}

if($argv[1]=='--compile'){Compile_rules();die();}
if($argv[1]=='--parse-queue'){parsequeue();die();}
if($argv[1]=='--no-check'){$_GET["nocheck"]=true;}
if($argv[1]=='--parse-sql'){ParseLastEvents();die();}
if($argv[1]=='--delete-all-iptables'){DeleteAllIpTablesRules();die();}
if($argv[1]=='--test-white'){$iptablesClass=new iptables_chains();$GLOBALS["IPTABLES_WHITELISTED"]=$iptablesClass->LoadWhiteLists();$iptablesClass->isWhiteListed($argv[2]);die();}
if($argv[1]=='--export-drop'){ExportDrop();die();}
if($argv[1]=='--transfert-white'){ParseResolvMX();die();}
if($argv[1]=='--upgrade-white'){UpgradeWhiteList();die();}
if($argv[1]=='--ipdeny'){ipdeny();die();}
if($argv[1]=='--perso'){perso();die();}

if($GLOBALS["VERBOSE"]){echo "Parsing ".@implode(" ", $argv)."\n";}


if(!Build_pid_func(__FILE__,"MAIN")){writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);die();}

parsequeue();
if($GLOBALS["EnablePostfixAutoBlock"]<>1){events("This feature is currently disabled ({$GLOBALS["EnablePostfixAutoBlock"]})");die();}
die();
		
//iptables -L OUTPUT --line-numbers		
//iptables -A INPUT -s 65.55.44.100 -p tcp --destination-port 25 -j DROP;

function DeleteAllIpTablesRules(){
	$unix=new unix();
	$iptables=$unix->find_program("iptables");	
	shell_exec("$iptables -F");	
	shell_exec("$iptables -F");
	shell_exec("$iptables -t nat -F");
	shell_exec("$iptables -t mangle -F");
	shell_exec("$iptables -X");
}





function ArrayIPTables(){
$pattern="#INPUT\s+-s\s(.+?)\/.+?--dport 25.+?ArticaInstantPostfix#";	
$cmd="/sbin/iptables-save > /etc/artica-postfix/iptables.conf"; 
system($cmd);
events("ArrayIPTables:: loading current ipTables list");
$datas=explode("\n",@file_get_contents("/etc/artica-postfix/iptables.conf"));
if(!is_array($datas)){return null;}
while (list ($num, $ligne) = each ($datas) ){
	if(preg_match($pattern,$ligne,$re)){
		$array[$re[1]]=$re[1];
	}else{
		
	}
}
events("ArrayIPTables:: loading current ipTables list ". count($array). " rules");
return $array;



}

function iptables_delete_all(){
events("Exporting datas iptables-save > /etc/artica-postfix/iptables.conf");
system("/sbin/iptables-save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
$pattern="#.+?ArticaInstantPostfix#";	
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){continue;}
		events("skip rule $ligne from deletion");
		$conf=$conf . $ligne."\n";
		}

events("restoring datas iptables-restore < /etc/artica-postfix/iptables.new.conf");
file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
system("/sbin/iptables-restore < /etc/artica-postfix/iptables.new.conf");
}
function iptables_ipdeny_delete_all(){
events("Exporting datas iptables-save > /etc/artica-postfix/iptables.conf");
system("/sbin/iptables-save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
$pattern="#.+?ArticaIpDeny#";
$c=0;	
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){$c++;continue;}
		events("skip rule $ligne from deletion");
		$conf=$conf . $ligne."\n";
		}
echo "Ban country $c removed rules...\n";
events("restoring datas iptables-restore < /etc/artica-postfix/iptables.new.conf");
file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
system("/sbin/iptables-restore < /etc/artica-postfix/iptables.new.conf");
}
function iptables_perso_delete_all(){
events("Exporting datas iptables-save > /etc/artica-postfix/iptables.conf");
system("/sbin/iptables-save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
$pattern="#.+?ArticaPersoRules#";
$c=0;	
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){$c++;continue;}
		events("skip rule $ligne from deletion");
		$conf=$conf . $ligne."\n";
		}
echo "Ban country $c removed rules...\n";
events("restoring datas iptables-restore < /etc/artica-postfix/iptables.new.conf");
file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
system("/sbin/iptables-restore < /etc/artica-postfix/iptables.new.conf");
}

function perso(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){echo "Already running pid $pid\n";return;}		
		
	
	$iptables=$unix->find_program("iptables");
	$iptablesClass=new iptables_chains();
	
	$sql="SELECT * FROM iptables WHERE disable=0 AND flux='INPUT' AND service='MANUAL'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	
	$GLOBALS["IPTABLES_WHITELISTED"]=$iptablesClass->LoadWhiteLists();
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		$ligne["multiples_ports"]=trim($ligne["multiples_ports"]);
		$port=" --destination-port {$ligne["local_port"]}";
		
		if($iptablesClass->isWhiteListed($ip)){continue;}
		if($ligne["local_port"]==-1){
			if($ligne["multiples_ports"]==null){continue;}
			$port=" -m multiport --dports {$ligne["multiples_ports"]}";
		}
		
		if($ligne["local_port"]==0){$port=null;}
		
		if($ligne["log"]==1){
			$log=" -j LOG --log-prefix \"FW_IN DROP: \"";
		}
		
		events("LOG {$ligne["serverip"]} REJECT INBOUND PORT $port");
		progress(35,"Building logging rules for $ip");
		$cmd="$iptables -A INPUT -s $ip -p tcp$port$log  -m comment --comment \"ArticaPersoRules\"";
		$commands[]=$cmd;
	}	
	
	
	
	
	if(is_array($commands)){
		if($GLOBALS["VERBOSE"]){echo count($commands)." should be performed\n";while (list ($index, $line) = each ($commands) ){ECHO $ligne."\n";}return;}
		while (list ($index, $line) = each ($commands) ){
			shell_exec($line);
		}
	}	
	Compile_rules();
	
}



function ipdeny(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){echo "Already running pid $pid\n";return;}		
	
	
	
	if($GLOBALS["VERBOSE"]){echo "iptables_ipdeny_delete_all()\n";}
	iptables_ipdeny_delete_all();
	
	$sock=new sockets();
	$EnableIpBlocks=$sock->GET_INFO("EnableIpBlocks");	
	if($EnableIpBlocks<>1){return ;}
	$sql="SELECT * FROM ipblocks_set";
	$q=new mysql();
	$list=array("af"=>"afghanistan","al"=>"albania","dz"=>"algeria","as"=>"samoa","ad"=>"andorra","ao"=>"angola","ai"=>"anguilla","ag"=>"barbuda","ar"=>"argentina","am"=>"armenia","aw"=>"aruba","au"=>"australia","at"=>"austria","az"=>"azerbaijan","bs"=>"bahamas","bh"=>"bahrain","bd"=>"bangladesh","bb"=>"barbados","by"=>"belarus","be"=>"belgium","bz"=>"belize","bj"=>"benin","bm"=>"bermuda","bt"=>"bhutan","bo"=>"bolivia","ba"=>"herzegovina","bw"=>"botswana","br"=>"brazil","io"=>"territory","bn"=>"darussalam","bg"=>"bulgaria","bf"=>"faso","bi"=>"burundi","kh"=>"cambodia","cm"=>"cameroon","ca"=>"canada","ky"=>"islands","cf"=>"republic","cl"=>"chile","cn"=>"china","co"=>"colombia","cd"=>"the","ck"=>"islands","cr"=>"rica","ci"=>"ivoire","hr"=>"croatia","cu"=>"cuba","cy"=>"cyprus","cz"=>"republic","dk"=>"denmark","dj"=>"djibouti","do"=>"republic","ec"=>"ecuador","eg"=>"egypt","sv"=>"salvador","er"=>"eritrea","ee"=>"estonia","et"=>"ethiopia","fo"=>"islands","fj"=>"fiji","fi"=>"finland","fr"=>"france","gf"=>"guiana","pf"=>"polynesia","ga"=>"gabon","gm"=>"gambia","ge"=>"georgia","de"=>"germany","gh"=>"ghana","gi"=>"gibraltar","gr"=>"greece","gl"=>"greenland","gd"=>"grenada","gu"=>"guam","gt"=>"guatemala","gw"=>"bissau","gy"=>"guyana","ht"=>"haiti","hn"=>"honduras","hk"=>"kong","hu"=>"hungary","is"=>"iceland","in"=>"india","id"=>"indonesia","ir"=>"of","iq"=>"iraq","ie"=>"ireland","il"=>"israel","it"=>"italy","jm"=>"jamaica","jp"=>"japan","jo"=>"jordan","kz"=>"kazakhstan","ke"=>"kenya","ki"=>"kiribati","kr"=>"of","kw"=>"kuwait","kg"=>"kyrgyzstan","la"=>"republic","lv"=>"latvia","lb"=>"lebanon","ls"=>"lesotho","lr"=>"liberia","ly"=>"jamahiriya","li"=>"liechtenstein","lt"=>"lithuania","lu"=>"luxembourg","mo"=>"macao","mk"=>"of","mg"=>"madagascar","mw"=>"malawi","my"=>"malaysia","mv"=>"maldives","ml"=>"mali","mt"=>"malta","mr"=>"mauritania","mu"=>"mauritius","mx"=>"mexico","fm"=>"of","md"=>"of","mc"=>"monaco","mn"=>"mongolia","ma"=>"morocco","mz"=>"mozambique","mm"=>"myanmar","na"=>"namibia","nr"=>"nauru","np"=>"nepal","nl"=>"netherlands","an"=>"antilles","nc"=>"caledonia","nz"=>"zealand","ni"=>"nicaragua","ne"=>"niger","ng"=>"nigeria","nu"=>"niue","nf"=>"island","mp"=>"islands","no"=>"norway","om"=>"oman","pk"=>"pakistan","pw"=>"palau","ps"=>"occupied","pa"=>"panama","pg"=>"guinea","py"=>"paraguay","pe"=>"peru","ph"=>"philippines","pl"=>"poland","pt"=>"portugal","pr"=>"rico","qa"=>"qatar","ro"=>"romania","ru"=>"federation","rw"=>"rwanda","kn"=>"nevis","lc"=>"lucia","ws"=>"samoa","sm"=>"marino","sa"=>"arabia","sn"=>"senegal","sc"=>"seychelles","sl"=>"leone","sg"=>"singapore","sk"=>"slovakia","si"=>"slovenia","sb"=>"islands","za"=>"africa","es"=>"spain","lk"=>"lanka","sd"=>"sudan","sr"=>"suriname","sz"=>"swaziland","se"=>"sweden","ch"=>"switzerland","sy"=>"republic","tw"=>"china","tj"=>"tajikistan","tz"=>"of","th"=>"thailand","tg"=>"togo","to"=>"tonga","tt"=>"tobago","tn"=>"tunisia","tr"=>"turkey","tm"=>"turkmenistan","tv"=>"tuvalu","ug"=>"uganda","ua"=>"ukraine","ae"=>"emirates","gb"=>"kingdom","us"=>"states","uy"=>"uruguay","uz"=>"uzbekistan","vu"=>"vanuatu","ve"=>"venezuela","vn"=>"nam","vg"=>"british","ye"=>"yemen","zm"=>"zambia","zw"=>"zimbabwe");
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code>$sql</code>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		echo "Ban country {$list[$ligne["country"]]} {$ligne["country"]}\n";
		ipdeny_perform($ligne["country"]);}	
}

function ipdeny_perform($country){
	$unix=new unix();
	$iptables=$unix->find_program("iptables");	
	$sql="SELECT * FROM ipblocks_db WHERE country='$country'";
	$list=array("af"=>"afghanistan","al"=>"albania","dz"=>"algeria","as"=>"samoa","ad"=>"andorra","ao"=>"angola","ai"=>"anguilla","ag"=>"barbuda","ar"=>"argentina","am"=>"armenia","aw"=>"aruba","au"=>"australia","at"=>"austria","az"=>"azerbaijan","bs"=>"bahamas","bh"=>"bahrain","bd"=>"bangladesh","bb"=>"barbados","by"=>"belarus","be"=>"belgium","bz"=>"belize","bj"=>"benin","bm"=>"bermuda","bt"=>"bhutan","bo"=>"bolivia","ba"=>"herzegovina","bw"=>"botswana","br"=>"brazil","io"=>"territory","bn"=>"darussalam","bg"=>"bulgaria","bf"=>"faso","bi"=>"burundi","kh"=>"cambodia","cm"=>"cameroon","ca"=>"canada","ky"=>"islands","cf"=>"republic","cl"=>"chile","cn"=>"china","co"=>"colombia","cd"=>"the","ck"=>"islands","cr"=>"rica","ci"=>"ivoire","hr"=>"croatia","cu"=>"cuba","cy"=>"cyprus","cz"=>"republic","dk"=>"denmark","dj"=>"djibouti","do"=>"republic","ec"=>"ecuador","eg"=>"egypt","sv"=>"salvador","er"=>"eritrea","ee"=>"estonia","et"=>"ethiopia","fo"=>"islands","fj"=>"fiji","fi"=>"finland","fr"=>"france","gf"=>"guiana","pf"=>"polynesia","ga"=>"gabon","gm"=>"gambia","ge"=>"georgia","de"=>"germany","gh"=>"ghana","gi"=>"gibraltar","gr"=>"greece","gl"=>"greenland","gd"=>"grenada","gu"=>"guam","gt"=>"guatemala","gw"=>"bissau","gy"=>"guyana","ht"=>"haiti","hn"=>"honduras","hk"=>"kong","hu"=>"hungary","is"=>"iceland","in"=>"india","id"=>"indonesia","ir"=>"of","iq"=>"iraq","ie"=>"ireland","il"=>"israel","it"=>"italy","jm"=>"jamaica","jp"=>"japan","jo"=>"jordan","kz"=>"kazakhstan","ke"=>"kenya","ki"=>"kiribati","kr"=>"of","kw"=>"kuwait","kg"=>"kyrgyzstan","la"=>"republic","lv"=>"latvia","lb"=>"lebanon","ls"=>"lesotho","lr"=>"liberia","ly"=>"jamahiriya","li"=>"liechtenstein","lt"=>"lithuania","lu"=>"luxembourg","mo"=>"macao","mk"=>"of","mg"=>"madagascar","mw"=>"malawi","my"=>"malaysia","mv"=>"maldives","ml"=>"mali","mt"=>"malta","mr"=>"mauritania","mu"=>"mauritius","mx"=>"mexico","fm"=>"of","md"=>"of","mc"=>"monaco","mn"=>"mongolia","ma"=>"morocco","mz"=>"mozambique","mm"=>"myanmar","na"=>"namibia","nr"=>"nauru","np"=>"nepal","nl"=>"netherlands","an"=>"antilles","nc"=>"caledonia","nz"=>"zealand","ni"=>"nicaragua","ne"=>"niger","ng"=>"nigeria","nu"=>"niue","nf"=>"island","mp"=>"islands","no"=>"norway","om"=>"oman","pk"=>"pakistan","pw"=>"palau","ps"=>"occupied","pa"=>"panama","pg"=>"guinea","py"=>"paraguay","pe"=>"peru","ph"=>"philippines","pl"=>"poland","pt"=>"portugal","pr"=>"rico","qa"=>"qatar","ro"=>"romania","ru"=>"federation","rw"=>"rwanda","kn"=>"nevis","lc"=>"lucia","ws"=>"samoa","sm"=>"marino","sa"=>"arabia","sn"=>"senegal","sc"=>"seychelles","sl"=>"leone","sg"=>"singapore","sk"=>"slovakia","si"=>"slovenia","sb"=>"islands","za"=>"africa","es"=>"spain","lk"=>"lanka","sd"=>"sudan","sr"=>"suriname","sz"=>"swaziland","se"=>"sweden","ch"=>"switzerland","sy"=>"republic","tw"=>"china","tj"=>"tajikistan","tz"=>"of","th"=>"thailand","tg"=>"togo","to"=>"tonga","tt"=>"tobago","tn"=>"tunisia","tr"=>"turkey","tm"=>"turkmenistan","tv"=>"tuvalu","ug"=>"uganda","ua"=>"ukraine","ae"=>"emirates","gb"=>"kingdom","us"=>"states","uy"=>"uruguay","uz"=>"uzbekistan","vu"=>"vanuatu","ve"=>"venezuela","vn"=>"nam","vg"=>"british","ye"=>"yemen","zm"=>"zambia","zw"=>"zimbabwe");
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$cmd="$iptables -A INPUT -s {$ligne["cdir"]} -p tcp -j LOG --log-prefix \"IPDENY {$list["$country"]} - $country DROP: \" -m comment --comment \"ArticaIpDeny\"";
		$commands[]=$cmd;
	}

	if($GLOBALS["VERBOSE"]){echo count($commands)." should be performed\n";}
	
	if(is_array($commands)){
		while (list ($index, $line) = each ($commands) ){
			$results=array();
			exec($line,$results);
			if($GLOBALS["VERBOSE"]){echo $line."\n".@implode("\n", $results);}
		}
	}
	
	echo "Ban country {$list["$country"]} $country ". count($commands)." entries done\n";
	
}






function Compile_rules(){
	progress(5,"Cleaning rules");
	iptables_delete_all();
	$unix=new unix();
	$iptables=$unix->find_program("iptables");
	$sock=new sockets();
	$iptablesClass=new iptables_chains();
	$InstantIptablesEventAll=$sock->GET_INFO("InstantIptablesEventAll");
	if(!is_numeric($InstantIptablesEventAll)){$InstantIptablesEventAll=1;}
	if($GLOBALS["VERBOSE"]){echo "InstantIptablesEventAll=$InstantIptablesEventAll\n";}
	
	if($GLOBALS["EnablePostfixAutoBlock"]<>1){progress(100,"Building rules done...");return;}
	events("Query iptables rules from mysql");
	progress(10,"Query rules");
	progress(25,"Building logging rules");
	$sql="SELECT * FROM iptables WHERE disable=0 AND flux='INPUT' and log=1 AND local_port=25";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	
	$GLOBALS["IPTABLES_WHITELISTED"]=$iptablesClass->LoadWhiteLists();
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		if($iptablesClass->isWhiteListed($ip)){continue;}
		events("LOG {$ligne["serverip"]} REJECT INBOUND PORT 25");
		progress(35,"Building logging rules for $ip");
		$cmd="$iptables -A INPUT -s $ip -p tcp --destination-port 25 -j LOG --log-prefix \"SMTP DROP: \" -m comment --comment \"ArticaInstantPostfix\"";
		$commands[]=$cmd;
		
		
		
	}
	

	
	progress(40,"Building rules...");
	$sql="SELECT * FROM iptables WHERE disable=0 AND flux='INPUT' AND local_port=25";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	progress(55,"Building rules...");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		if($iptablesClass->isWhiteListed($ip)){continue;}
		events("ADD REJECT {$ligne["serverip"]} INBOUND PORT 25");
		progress(60,"Building rules for $ip...");
		if($InstantIptablesEventAll==1){
			if($GLOBALS["VERBOSE"]){echo "$ip -> LOG\n";}
			$cmd="$iptables -A INPUT -s $ip -p tcp --destination-port 25 -j LOG --log-prefix \"SMTP DROP: \" -m comment --comment \"ArticaInstantPostfix\"";
			$commands[]=$cmd;
		}
		
		$cmd="$iptables -A INPUT -s $ip -p tcp --destination-port 25 -j DROP -m comment --comment \"ArticaInstantPostfix\"";
		$commands[]=$cmd;
	}
	
	if($GLOBALS["VERBOSE"]){
		echo count($commands)." should be performed\n";
		return;
	}
	
	if(is_array($commands)){
		while (list ($index, $line) = each ($commands) ){
			shell_exec($line);
		}
	}
	
	
	progress(90,"Building rules done...");
	progress(100,"Building rules done...");
	
	
}

function progress($pourc,$text){
	if($GLOBALS["VERBOSE"]){echo "$pourc% $text\n";}
	$file="/usr/share/artica-postfix/ressources/logs/compile.iptables.progress";
	$ini=new Bs_IniHandler();
	$ini->set("PROGRESS","pourc",$pourc);
	$ini->set("PROGRESS","text",$text);
	$ini->saveFile($file);
	chmod($file,0777);
	}



function events($text){
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile=$_GET["filelogs"];
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid] $text\n");
		@fclose($f);	
		}

		
function load_whitelist(){
$array=array();
	
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$array[$ligne["ipaddr"]]=$ligne["ipaddr"];
		
		
	}		
	
	
	$sql="SELECT serverip FROM iptables WHERE disable=1 AND flux='INPUT'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		$array[$ip]=$ip;
	}	
$array["127.0.0.1"]="127.0.0.1";
return $array;	
}



function parsequeue(){
	$unix=new unix();
	$iptables=$unix->find_program("iptables");
	$q=new mysql();
	$q->Check_iptables_table();
	$ini=new Bs_IniHandler();
	$ini->loadFile('/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults');	
	
	foreach (glob("/var/log/artica-postfix/smtp-hack/*.hack") as $filename) {
		$basename=basename($filename);
		$array=unserialize(@file_get_contents($filename));
		
		$IP=$array["IP"];
		if($IP=="127.0.0.1"){@unlink($filename);continue;}
		
		$server_name=gethostbyaddr($IP);
		$matches=$array["MATCHES"];
		$EVENTS=$array["EVENTS"];
		$date=$array["DATE"];
		
		if($GLOBALS["VERBOSE"]){echo "$basename: servername:$server_name IP=[$IP]\n";}
		
		$cmd="$iptables -A INPUT -s $IP -p tcp --destination-port 25 -j DROP -m comment --comment \"ArticaInstantPostfix\"";
		$iptablesClass=new iptables_chains();
		$iptablesClass->serverip=$IP;
		$iptablesClass->servername=$server_name;
		$iptablesClass->rule_string=$cmd;
		$iptablesClass->EventsToAdd=$EVENTS;
		if($iptablesClass->addPostfix_chain()){
			if($GLOBALS["VERBOSE"]){echo "Add IP:Addr=<$IP>, servername=<{$server_name}> to mysql\n";}
			$ini->set($IP,"events",$matches);
			$ini->set($IP,"iptablerule",$cmd);
			$ini->set($IP,"hostname",$server_name);	
			if($GLOBALS["VERBOSE"]){echo "delete $filename\n";}	
			@unlink($filename);
		}
		
	}
	
	$filestr=$ini->toString();
	file_put_contents("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockResults",$filestr);
	
}

function ParseLastEvents(){
	$timefile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$timeF=file_time_min($timefile);
	if($timeF<240){
		if($GLOBALS["VERBOSE"]){echo "$timeF minutes, need to wait 240\n";}
		return;
	}
	@file_put_contents($timefile,"#");
	
	
	$unix=new unix();
	$iptables=$unix->find_program("iptables");	
	
	if($GLOBALS["VERBOSE"]){echo "Loading Whitelist\n";}
	
	$whitelist=load_whitelist();
	if($GLOBALS["VERBOSE"]){echo "Loading Whitelist ". count($whitelist). " items\n";}
	
	$sock=new sockets();
	$PostfixInstantIptablesLastDays=$sock->GET_INFO("PostfixInstantIptablesLastDays");
	$PostfixInstantIptablesMaxEvents=$sock->GET_INFO("PostfixInstantIptablesMaxEvents");
	if(!is_numeric($PostfixInstantIptablesLastDays)){$PostfixInstantIptablesLastDays=7;}
	if(!is_numeric($PostfixInstantIptablesMaxEvents)){$PostfixInstantIptablesMaxEvents=50;}
	
	$sql="SELECT COUNT(ipaddr) as tcount,ipaddr,smtp_err,hostname 
	FROM mail_con_err_stats WHERE zDate<DATE_SUB(NOW(),INTERVAL 1 DAY) 
	AND zDate>=DATE_SUB(NOW(),INTERVAL $PostfixInstantIptablesLastDays DAY) 
	GROUP BY ipaddr,smtp_err,hostname ORDER BY COUNT(ipaddr) DESC";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	$newarray=array();
	
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count_events=$ligne["tcount"];
		if($count_events<3){break;}
		$ipaddr=$ligne["ipaddr"];
		$error=$ligne["smtp_err"];
		$server_name=$ligne["hostname"];
		if($whitelist[$server_name]){echo "Whitelisted $server_name\n";continue;}
		if($whitelist[$ipaddr]){echo "Whitelisted $ipaddr\n";continue;}
		$newarray[$ipaddr]["HOST"]=$server_name;
		$newarray[$ipaddr]["EVENTS_TEXT"][]="$server_name [$ipaddr] - $count_events $error";
		if(isset($newarray[$ipaddr])){
			$newarray[$ipaddr]["EVENTS"]=$newarray[$ipaddr]["EVENTS"]+$count_events;
		}else{
			$newarray[$ipaddr]["EVENTS"]=$newarray[$ipaddr]["EVENTS"];
		}

	}	
	
	if(!is_array($newarray)){return;}
	$newarray2=$newarray;
	while (list ($ipaddr, $ligne) = each ($newarray) ){
		$count=$ligne["EVENTS"];
		if($count<$PostfixInstantIptablesMaxEvents){
			unset($newarray2[$ipaddr]);
			//if($GLOBALS["VERBOSE"]){echo "skipping $ipaddr {$ligne["HOST"]} $count events\n";}
			continue;
		}
		
				
	}
	
	if($GLOBALS["VERBOSE"]){echo count($newarray2)." items -> Array:newarray2\n";}
	if(count($newarray2)==0){return;}
	$ipCount=0;
	while (list ($ipaddr, $ipaddrARR) = each ($newarray2) ){
		$sql="SELECT rulemd5 FROM iptables WHERE serverip='$ipaddr' AND local_port='25'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["rulemd5"]<>null){
			if($GLOBALS["VERBOSE"]){
				echo "Skip $ipaddr already added\n";
			}
			continue;
		}
		$EVENTS="{$ipaddrARR["EVENTS"]} refused connexions:\n".@implode("\n",$ipaddrARR["EVENTS_TEXT"]);
		$cmd="$iptables -A INPUT -s $ipaddr -p tcp --destination-port 25 -j DROP -m comment --comment \"ArticaInstantPostfix\"";
		$iptablesClass=new iptables_chains();
		$iptablesClass->serverip=$ipaddr;
		$iptablesClass->servername=$server_name;
		$iptablesClass->rule_string=$cmd;
		$iptablesClass->EventsToAdd=$EVENTS;		
		if(!$iptablesClass->addPostfix_chain()){$FAILED="FAILED TO add $ipaddr ";}
		$notifs[]=$EVENTS;
		shell_exec($cmd);
		if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
		$ipCount++;
		}
	
	if($ipCount>0){
		$unix->send_email_events("Instant Iptables $ipCount addresse(s) added",
		"Calculation since $PostfixInstantIptablesLastDays days and for $PostfixInstantIptablesMaxEvents minimal blocks events\n".
		@implode("\n",$notifs),"postfix");
	}
		
		
}

function ExportDrop(){
	if($GLOBALS["EnablePostfixAutoBlock"]<>1){return;}
	$pidpath="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidpath);
	$unix=new unix();
	if($unix->process_exists($oldpid)){
		if($GLOBALS["VERBOSE"]){echo "Already executed $oldpid\n";}
		return;
	}
	@file_get_contents($pidpath,getmypid());
	
	$grep=$unix->find_program("grep");
	$syslog=$unix->LOCATE_SYSLOG_PATH();
	$cmd="$grep -E \"smtp kernel.*?SMTP DROP\" $syslog >/usr/share/artica-postfix/ressources/logs/iptables-smtp-drop.log";
	shell_exec($cmd);
	@chmod("/usr/share/artica-postfix/ressources/logs/iptables-smtp-drop.log",0777);
	
}

function ParseResolvMX(){
	$sock=new sockets();
	$WhiteListResolvMX=$sock->GET_INFO("WhiteListResolvMX");
	if(!is_numeric($WhiteListResolvMX)){return null;}
	if($WhiteListResolvMX==0){return null;}
	if(!function_exists("getmxrr")){echo "getmxrr() no such function\n";return;}
	$sql="SELECT sender FROM postfix_global_whitelist WHERE enabled=1 ORDER BY sender";
	if($GLOBALS["VERBOSE"]){echo $sql."\n";}
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
	
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$WHITELISTED[$ligne["ipaddr"]]=true;
		$WHITELISTED[$ligne["hostname"]]=true;
		
	}	

	$count_whitelisted_before=count($WHITELISTED);
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$domain=trim($ligne["sender"]);
		if($domain==null){continue;}
		if(preg_match("#@(.+)#",$domain,$re)){$domain=$re[1];}
		if(strpos($domain,"*")>0){continue;}
		$array_mx=resolvMX($domain);
		if(count($array_mx)==0){continue;}
		echo "$domain = ".count($array_mx)." mx\n";
		while (list ($ipaddr, $hostname) = each ($array_mx) ){
			$notif[]="$domain: $hostname [$ipaddr]";
			$WHITELISTED[$ipaddr]=$hostname;
			
			
		}
	}
	
	$count_whitelisted_after=count($WHITELISTED);
	$somme=$count_whitelisted_after-$count_whitelisted_before;
	
	if($somme==0){echo "Nothing to do...\n";return;}
	
	
	if($somme>0){
		if($GLOBALS["VERBOSE"]){echo "$somme items added in array\n".@implode("\n",$notif);}
		$unix=new unix();
		$unix->send_email_events("$somme items MX has been whitelisted",@implode("\n",$notif),"postfix");
		
	}
	
	reset($WHITELISTED);

	while (list ($value, $hostname) = each ($WHITELISTED) ){
		if(trim($value)==null){continue;}
		$sql="DELETE FROM iptables WHERE serverip='$value' AND local_port=25";
		$q->QUERY_SQL($sql,"artica_backup");
		$sql="INSERT IGNORE INTO postfix_whitelist_con (ipaddr,hostname) VALUES('$value','$hostname')";
		$q->QUERY_SQL($sql,"artica_backup");
		
		
	}	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --postscreen";
	shell_exec($cmd);
}

function resolvMX($domains){
getmxrr($domains, $mx_records, $mx_weight);
for($i=0;$i<count($mx_records);$i++){$mxs[$mx_records[$i]] = $mx_weight[$i];}
@asort ($mxs);
$records = array_keys($mxs);
for($i = 0; $i < count($records); $i++){
	$ip=gethostbyname($records[$i]);
	$newArray[$ip]=$records[$i];
	}
return $newArray;
}

function UpgradeWhiteList(){
	if(!is_file("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockWhiteList")){
		echo "Starting... PostfixAutoBlockWhiteList no such file\n";
		return;}
	$tpl=explode("\n",@file_get_contents("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockWhiteList"));
	
	echo "Starting... upgrade ".count($tpl)." ip list\n";
	
	$q=new mysql();
	while (list ($index, $ipaddrTXT) = each ($tpl) ){
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ipaddrTXT)){
			$ipaddr=gethostbyname($ipaddrTXT);
			$hostname=$ipaddrTXT;
		}else{
			$ipaddr=$ipaddrTXT;
			$hostname=gethostbyaddr($ipaddrTXT);
		}	
		echo "Inserting $hostname [$ipaddr]\n";
		$sql="INSERT IGNORE INTO postfix_whitelist_con (ipaddr,hostname) VALUES('$ipaddr','$hostname')";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo $q->mysql_error."\n";
			return;
		}
	}
	
	@unlink("/etc/artica-postfix/settings/Daemons/PostfixAutoBlockWhiteList");

}
?>