<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");

if(isset($_GET["post"])){echo GetLogs();exit;}

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
   document.getElementById('wait').innerHTML=\"- \" + reste + \" s\";
        

   if (tant < 10 ) {                           //exemple:caler a une minute (60*1000) 
      timerID = setTimeout(\"demarre()\",1000);
                
   } else {
               tant = 0;
               document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
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
               LoadAjax2('postlogs','$page?post=1');
                   
        demarre();                                 //on lance la fonction demarre qui relance le compteur
   }
}
</script>	
	
	<div id=wait style='margin:5px;font-weight:bold;font-size:12px;text-align:right'></div>
	<div id=postlogs></div>
	
	<script>LoadAjax2('postlogs','$page?post=1');</script>
	<script>demarre();</script>
	";
	
 $tplusr=new template_users('{mailman_events}',$html,0,0,0,0);
 echo $tplusr->web_page;	
	
}



function GetLogs(){
		$sock=new sockets();
		
		$datas=$sock->getfile('mailmanevents');
		writelogs(strlen($datas) . ' bytes',__FUNCTION__,__FILE__);
		$tbl=explode("\n",$datas);
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			$html=$html . "<div style='color:white;margin-bottom:3px'><code>$val</code></div>";
			
		}
		
		echo RoundedBlack($html);
	
	
}