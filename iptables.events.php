<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
$user=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	
	if($user->AsMailBoxAdministrator==false){header('location:users.index.php');exit();}

if(isset($_GET["post"])){echo PostfixLogs();exit;}

page();
function Page(){
	$page=CurrentPageName();
	
	$html="
	
<script language=\"JavaScript\">  // une premiere fonction pour manipuler les valeurs \"dynamiques\"       
function mettre(){                            
   document.form1.source.focus();
   document.form1.source.select();
}

var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
   //document.getElementById('wait').innerHTML=\"- \" + reste + \" s\";
        

   if (tant < 10 ) {                           //exemple:caler a une minute (60*1000) 
      timerID = setTimeout(\"demarre()\",1000);
                
   } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               LoadAjax2('postlogs','$page?post=1');
               demarre();                                //la boucle demarre !
   }
}

function demar1(){
   tant = tant+1;
   
        

   if (tant < 2 ) {                             //delai court pour le premier affichage !
      timerID = setTimeout(\"demar1()\",1000);
                
   } else {
               tant = 0;                            //reinitialise le compteur
               LoadAjax2('postlogs','postfix.events.php?post=1');
                   
        demarre();                                 //on lance la fonction demarre qui relance le compteur
   }
}
</script>	
	
	<div id=wait style='margin:5px;font-weight:bold;font-size:12px;text-align:right'></div>
	<div id=postlogs></div>
	
	<script>LoadAjax2('postlogs','$page?post=1');</script>
	<script>demarre();</script>
	";
	
 echo iframe($html,0);
 
	
	
}



function PostfixLogs(){
		$sock=new sockets();
		$styletd="style='color:white;font-weight:bold;font-size:11px'";
		$datas=$sock->getfile('iptables_events');
		writelogs(strlen($datas) . ' bytes',__FUNCTION__,__FILE__);
		$tbl=explode("\n",$datas);
		$html=$html . "<table style='width:100%'>
		<tr style='background-color:#005447'>
				<td width=1% nowrap $styletd>date</td>
				<td width=1% nowrap $styletd>NIC</td>
				<td nowrap $styletd>MAC</td>
				<td nowrap $styletd>ip</td>
				<td nowrap $styletd>proto</td>
				<td nowrap $styletd>port</td>
				</tr>";
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match('#(.+?)([\s0-9\:]+).+?kernel:.+?:IN=(.+?)\s+OUT=.*?MAC=([0-9\:a-z]+)\s+SRC=([0-9\.]+).+?PROTO=(.+?)\s+.+DPT=([0-9]+)#',$val,$re)){
				$html=$html . "<tr " . CellRollOver().">
				<td width=1% nowrap>{$re[1]} {$re[2]}</td>
				<td width=1% nowrap>{$re[3]}</td>
				<td nowrap>{$re[4]}</td>
				<td nowrap>{$re[5]}</td>
				<td nowrap width=1% >{$re[6]}</td>
				<td nowrap width=1% >{$re[7]}</td>
				</tr>
				
				";
				
			}
			
			
		}
		$html=$html .  "</table>";
		
		echo RoundedLightGrey($html);
	
	
}