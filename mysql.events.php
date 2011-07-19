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
	
	if($user->AsOrgAdmin==false){header('location:users.index.php');exit();}

if(isset($_GET["post"])){echo PostfixLogs();exit;}

page();
function Page(){
	
	$event_id=$_GET["event_id"];
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
               LoadAjax2('postlogs','$page?post=1&event=$event_id');
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
	
	<script>LoadAjax2('postlogs','$page?post=1&event=$event_id');</script>
	<script>demarre();</script>
	";
	
 echo iframe($html,0);
 
	
	
}



function PostfixLogs(){
	include_once('ressources/class.mysql.inc');
$sql="SELECT * FROM events WHERE event_id={$_GET["event"]} ORDER BY zDate DESC LIMIT 0,100";
$my=new mysql();

$html="<table style='width:100%'>";
 $html=$html . "<tr style='background-color:#005447'>
	  <td width=1%>&nbsp;</td>
	  <td width=1% nowrap>&nbsp;</td>
	  <td>&nbsp;</td>
	  </tr>";

$resultat=$my->QUERY_SQL($sql,'artica_events');
	while($ligne=@mysql_fetch_array($resultat,MYSQL_ASSOC)){
	
	switch ($ligne["event_type"]) {
		case 0:$img="icon_mini_warning.gif";break;
		case 1:$img="icon-mini-ok.gif";break;
		case 2:$img="icon-mini-info.gif";break;
		default:$img="icon-mini-info.gif";break;
	}
		
	  $html=$html . "<tr ". CellRollOver().">
	  <td width=1%><img src='img/$img' style='margin:2px'></td>
	  <td width=1% nowrap>{$ligne["zDate"]}</td>
	  <td>{$ligne["text"]}</td>
	  </tr>";
	  }

	 $html=$html ."</table>";
	 $html=RoundedLightGrey($html);
	 
	 $page=$html;
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($page);
		
		
	
	
}