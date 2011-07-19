<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$user=new usersMenus();
	if($user->AllowViewStatistics==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
if(isset($_GET["graph"])){graphs();exit;}
if(isset($_GET["popup"])){popup();exit;}


js();

function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{emails_flow}");
	
	$html="
	function main_mailgraph_statistics_start(){
			YahooWin2(790,'$page?popup=yes','$title');
		}
	
	main_mailgraph_statistics_start();";
	echo $html;	
	
	
}
function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["day"]="{emails_flow}";
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?graph=yes&t=$num&\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_stats_yorels_index style='width:100%;height:800px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_stats_yorels_index').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";


	
	
}

function page(){
if(!isset($_GET["t"])){$t='day';}else{$t=$_GET["t"];}
$page=CurrentPageName();
$usersmenus=new usersMenus();


$html="

<script language=\"JavaScript\">       
	var timerID  = null;
	var timerID1  = null;
	var tant=0;
	var reste=0;

	function demarre(){
	   tant=tant+1;
	   reste=10-tant;
		if (tant < 10 ) {                           
	      timerID = setTimeout(\"demarre()\",5000);
	      } else {
	               tant = 0;
	               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
	               ChargeLogs();
	               demarre();                                //la boucle demarre !
	   }
	}

function demar1(){
   tant = tant+1;
   
        

   if (tant < 2 ) {                             //delai court pour le premier affichage !
      timerID = setTimeout(\"demar1()\",1000);
                
   } else {
               tant = 0;                            //reinitialise le compteur
               ChargeLogs();
                   
        demarre();                                 //on lance la fonction demarre qui relance le compteur
   }
}

function ChargeLogs(){
	var t;
	if(document.getElementById('t')){
		t=document.getElementById('t').value;}else{t='day';}
	LoadAjax2('graphs','$page?graph=yes&hostname={$_GET["hostname"]}&t='+t);
	LoadAjax2('services_status','$page?status=yes&hostname={$_GET["hostname"]}&t='+t);
	}


</script>	


<br>
<div id='graphs'></div>
<script>ChargeLogs();demarre();</script>
";
$tpl=new template_users('{emails_flow}',$html);
echo $tpl->web_page;
	
	
	
}	
	

function graphs(){
$t=$_GET["t"];
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("content-type:text/html");

$md=md5(date('Ymdhis'));
	$users=new usersMenus(nul,0,$_GET["hostname"]);
	
	
	
	$html="
<input type='hidden' id='t' value='$t'>
<p class='caption'>{emails_flow_text}</p>
<table style='width:600px' align=center>
<tr>
<td valign='top'>
	<center style='margin:4px'>
		<H5>{emails_flow_hour}</H5>
		<img src='images.listener.php?uri=mailgraph/mailgraph_0.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{emails_flow_day}</H5>
		<img src='images.listener.php?uri=mailgraph/mailgraph_1.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{emails_flow_month}</H5>
		<img src='images.listener.php?uri=mailgraph/mailgraph_3.png&md=$md'>
	</center>					
</td>
</tr>
</table>	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}
	

	
	
?>	

