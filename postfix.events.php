<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once("ressources/class.templates.inc");
	include_once("ressources/class.ldap.inc");
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}



if(isset($_GET["post"])){echo PostfixLogs();exit;}
if(isset($_GET["pop"])){echo PostfixLogsPop();exit;}
if(isset($_GET["getPopupLogs"])){echo getPopupLogs();exit;}
page();
function Page(){
	
	
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
               LoadAjax2('postlogs','postfix.events.php?post=1');
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
	
	<script>LoadAjax2('postlogs','postfix.events.php?post=1');</script>
	<script>demarre();</script>
	";
	
 $tplusr=new template_users('{postfix_events}',$html,0,0,0,0);
 echo $tplusr->web_page;	
	
}



function PostfixLogs(){
		$sock=new sockets();
		
		$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?postfix-tail=yes')));
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			$html=$html . "<div style='color:white;margin-bottom:3px'><code>$val</code></div>";
			
		}
		
		echo RoundedBlack($html);
	
	
}

function getPopupLogs(){
		$sock=new sockets();
		if(isset($_GET["filter"])){
			$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-tail=yes&filter={$_GET["filter"]}")));
			$_COOKIE["postfix_filter"]=$_GET["filter"];
		}else{
			$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?postfix-tail=yes')));
		}
		
		
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$img=statusLogs($val);
			$html=$html . "<div style='black;margin-bottom:1px;padding:2px;border-bottom:1px dotted #CCCCCC;border-left:5px solid #CCCCCC;width:105%;margin-left:-30px'>
			<table style='width:100%'>
			<tr>
			<td width=1%><img src='$img'></td><td><code style='font-size:10px'>$val</code></td>
			</tr>
			</table>
			</div>";
			}
		}	
		
		echo $html;
	
}

function PostfixLogsPop(){
	
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
   
        

   if (tant < 5 ) {                           //exemple:caler a une minute (60*1000) 
      timerID = setTimeout(\"demarre()\",700);
                
   } else {
               tant = 0;
               //;
               postlogs();
               demarre();                                //la boucle demarre !
   }
}

var x_postlogs=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById('postlogs').innerHTML=tempvalue;
      document.getElementById('showstatus').innerHTML='&nbsp;';
      }


function postlogs(){
	    var filter=document.getElementById('filter').value;

		document.getElementById('showstatus').innerHTML='<img src=img/wait.gif>';
	 	var XHR = new XHRConnection();
	    if(filter.length>0){
	    	XHR.appendData('filter',filter);
	    }	 	
	 	XHR.appendData('getPopupLogs','1');
		XHR.sendAndLoad('$page', 'GET',x_postlogs);

}




function demar1(){
   tant = tant+1;
   
        

   if (tant < 2 ) {                             //delai court pour le premier affichage !
      timerID = setTimeout(\"demar1()\",1000);
                
   } else {
               tant = 0;                            //reinitialise le compteur
               LoadAjax2('postlogs','postfix.events.php?getPopupLogs=1');
                   
        demarre();                                 //on lance la fonction demarre qui relance le compteur
   }
}
</script>	
	<table style='width:100%;padding:3px;margin:3px;border:1px solid #CCCCCC'>
	<tr>
		<td class=legend width=1%>{filter}:</td>
		<td width=98%><input type='text' id='filter' name='filter' value='{$_COOKIE["postfix_filter"]}' style='width:190px'>
		<td width=1%><span id='showstatus'></span>
	</tr>
	</table>	
	<div id=wait style='margin:5px;font-weight:bold;font-size:12px;text-align:right'></div>
	<div id=postlogs style='width:100%'></div>
	
	<script>LoadAjax2('postlogs','postfix.events.php?getPopupLogs=1');</script>
	<script>demarre();</script>
		
	
	
	";
	$tpl=new template_users("{postfix_events}",$html);
	$tpl->nogbPopup=1;
	$tpl->_BuildPopUp($html,"{postfix_events}");
	echo $tpl->web_page;
	
}