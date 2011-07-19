<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");



if(isset($_GET["start"])){echo start();exit;}
if(isset($_GET["post"])){echo events();exit;}
if(isset($_GET["who"])){echo who_js();exit;}
if(isset($_GET["who-start"])){echo who_start();exit;}
if(isset($_GET["who-build"])){echo who_events();exit;}
js();exit;


function who_start(){
	$events=who_events(1);
	$html="
	<H1>{APP_PUREFTPD} {current_connections}</H1>
	<div id='showeventsWHO' style='width:100%;height:250px;overflow:auto'>
		$events
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'fileshares.index.php');
}

function who_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_PUREFTPD} {events}");
	$html="
		var timerPureLogsWHO  = null;
		var tantPureLogsWHO=0;
		var restePurelogsWHO=0;
		
		
		function PureCronDemarreWHO(){
		   tantPureLogsWHO = tantPureLogsWHO+1;
		   restePurelogsWHO=10-tantPureLogsWHO;
		   if(!YahooWin3Open()){return false;}
		   
		   if (restePurelogsWHO >0 ) {                            
		      timerPureLogsWHO = setTimeout(\"PureCronDemarreWHO()\",700);
		    } else {
				tantPureLogsWHO = 0;
		        LoadAjax2('showeventsWHO','$page?who-build=1');
		        PureCronDemarreWHO();
		   }
		}		
	
		function pureftpdEventsWHO(){
			YahooWin3('600','$page?who-start=yes','$title');
			setTimeout(\"PureCronDemarreWHO()\",1700);
			
		}
		pureftpdEventsWHO();
		";
	echo $html;
}

function who_events($return=0){
	$sock=new sockets();
	$data=$sock->getfile("pureftpwho");
	$data=str_replace("Content-Type: text/html","",$data);
	$data=str_replace('summary="Pure-FTPd server status"',"style='width:99%' class=table_form",$data);
	if($return==1){return $data;}
	echo $data;
	
}



function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_PUREFTPD} {events}");
	$html="
		var timerPureLogs  = null;
		var tantPureLogs=0;
		var restePurelogs=0;
		
		
		function PureCronDemarre(){
		   tantPureLogs = tantPureLogs+1;
		   restePurelogs=10-tantPureLogs;
		   if(!YahooWin2Open()){return false;}
		   
		   if (restePurelogs >0 ) {                            
		      timerPureLogs = setTimeout(\"PureCronDemarre()\",700);
		    } else {
				tantPureLogs = 0;
		        LoadAjax2('showevents','$page?post=1');
		        PureCronDemarre();
		   }
		}		
	
		function pureftpdEvents(){
			YahooWin2('600','$page?start=yes','$title');
			setTimeout(\"PureCronDemarre()\",1700);
			
		}
		pureftpdEvents();
		";
	echo $html;
}


function start(){
	$events=events(1);
	$html="
	<H1>{APP_PUREFTPD} {events}</H1>
	<div id='showevents' style='width:100%;height:250px;overflow:auto'>
		$events
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


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
var current_page=\"$page\";

function demarre(){
   tant = tant+1;
   reste=10-tant;
  
    document.getElementById('wait').innerHTML='';    

   if (tant < 10 ) {                           //exemple:caler a une minute (60*1000) 
      timerID = setTimeout(\"demarre()\",1000);
                
   } else {
               tant = 0;
               document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               LoadAjax2('showevents','$page?post=1');
               demarre();                                //la boucle demarre !
   }
}

function demar1(){
   tant = tant+1;
   
        

   if (tant < 2 ) {                             //delai court pour le premier affichage !
      timerID = setTimeout(\"demar1()\",1000);
                
   } else {
               tant = 0;                            //reinitialise le compteur
               LoadAjax2('showevents','$page?post=1');
                   
        demarre();                                 //on lance la fonction demarre qui relance le compteur
   }
}
</script>	
	
	<div id=wait style='margin:5px;font-weight:bold;font-size:12px;text-align:right'></div>
	<div id=showevents></div>
	
	<script>LoadAjax2('showevents','$page?post=1');</script>
	<script>demarre();</script>
	";
	
 $tplusr=new template_users('{events}',$html,0,0,0,0);
 echo $tplusr->web_page;	
	
}



function events($return=0){
		$sock=new sockets();
		
		$datas=$sock->getfile('pureftpd_logs');
		
		writelogs(strlen($datas) . ' bytes',__FUNCTION__,__FILE__);
		$tbl=explode("\n",$datas);
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			$val=htmlentities($val);
				$html=$html . "<div style='color:white;margin-bottom:3px'><code>$val</code></div>";
			
			
		}
		if($return==1){return RoundedBlack($html);}
		echo RoundedBlack($html);
	
	
}