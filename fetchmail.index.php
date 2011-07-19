<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.fetchmail.inc');
	if(isset($_GET["ajax"])){echo fetch_js();exit;}
	if(isset($_GET["popup"])){echo fetch_popup();exit;}
	if(isset($_GET["status"])){echo FetchMailStatus();exit;}
	if(isset($_GET["enable_fetchmail"])){fetch_enable_save();exit;}
	if(isset($_GET["events"])){FetchLogsPop();exit;}
	if(isset($_GET["getFetchlogs"])){fetch_events();exit;}
	if(isset($_GET["fetch_status"])){FetchMailStatus();exit;}
	if(isset($_GET["reload-fetchmail"])){reloadfetchmail();exit;}

page();	
function page(){
$usersmenus=new usersMenus();
$page=CurrentPageName();
if($usersmenus->AsPostfixAdministrator==true){}else{header('location:users.index.php');exit;}	
$html="<table style='width:600px' align=center>
<tr>
<td width=50% valign='top' class='caption' style='text-align:justify'>
<img src='img/bg_fetchmail.jpg'><p>
{fetchmail_about}</p></td>
<td valign='top'>
	<table>";
//folder-fetchmail-64.jpg
if($usersmenus->AsPostfixAdministrator==true){
		$html=$html . "<tr><td valign='top' ><div id=status></div><br>" . applysettings("fetch")  . "<br></td></tr>
		<tr><td valign='top' >".Paragraphe('folder-tools-64.jpg','{daemon_settings}','{daemon_settings_text}','fetchmail.daemon.settings.php') ."</td></tr>
		<tr><td valign='top'>  ".Paragraphe('folder-logs-64.jpeg','{events}','{events_text}','fetchmail.daemon.events.php') ."</td></tr>";
		}

		

		
$html=$html . "</table>
</td>
</tr>
</table>
<script>LoadAjax('status','$page?status=yes');</script>
";
$tpl=new template_users('Fetchmail',$html);
echo $tpl->web_page;
	
	
	
}

function FetchMailStatus(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=implode("\n",unserialize(base64_decode($sock->getFrameWork('cmd.php?fetchmail-status=yes'))));
	$ini->loadString($datas);
	$status=DAEMON_STATUS_ROUND("FETCHMAIL",$ini,null)."<br>".DAEMON_STATUS_ROUND("FETCHMAIL_LOGGER",$ini,null);
	echo $tpl->_ENGINE_parse_body($status);
	}




function fetch_js(){
$usersmenus=new usersMenus();
$tpl=new templates();
$page=CurrentPageName();	
if(!$usersmenus->AsPostfixAdministrator){
	$title=$tpl->_ENGINE_parse_body('{not allowed}');
	echo "alert('$title');";
	die();
}

if(!$usersmenus->fetchmail_installed){
	$title=$tpl->_ENGINE_parse_body('{ERROR_NOT_INSTALLED_REDIRECT}');
	echo "
	alert('$title');
	Loadjs('setup.index.progress.php?product=APP_FETCHMAIL&start-install=yes');";
	exit;
	
}




$md=md5(date('Ymdhis'));
$title=$tpl->_ENGINE_parse_body('{APP_FETCHMAIL}');
$startcmd="YahooWin0(720,'fetchmail.index.php?popup=yes&md=$md','$title');";
	
if(isset($_GET["in-front-ajax"])){
	$startcmd="$('#BodyContent').load('fetchmail.index.php?popup=yes&md=$md');";
}

$html="

var fetch_timerID  = null;
var fetch_tant=0;
var fetch_reste=0;

function fetch_demarre(){
   fetch_tant = fetch_tant+1;
   fetch_reste=10-fetch_tant;
	if (fetch_tant < 10 ) {                           
      fetch_timerID = setTimeout(\"fetch_demarre()\",10000);
    } else {
		fetch_tant = 0;
		reloadStatus(); 
		if(document.getElementById('$md')){fetch_demarre();}
    }
}

function reloadStatus(){
if(document.getElementById('$md')){
   LoadAjax('$md','$page?fetch_status=yes');
}

}

var x_FetchMailEnable= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)}
    LoadFetchIndex();
	}


function FetchMailEnable(){
	    var XHR = new XHRConnection();
        XHR.appendData('enable_fetchmail',document.getElementById('enable_fetchmail').value);
        document.getElementById('fetchFormEnable').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_FetchMailEnable);
}

var x_ReloadFetchMail= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)}
	}

function ReloadFetchMail(){
  		var XHR = new XHRConnection();
        XHR.appendData('reload-fetchmail',1);
		XHR.sendAndLoad('$page', 'GET',x_ReloadFetchMail);
}


function LoadFetchIndex(){
	$startcmd
	setTimeout(\"reloadStatus()\",2000);
}

LoadFetchIndex();
fetch_demarre();

";



	
	echo $html;
}


function fetch_popup(){
	$users=new usersMenus();
	$tpl=new templates();
	$page=CurrentPageName();
	$fetchmail_rules_text=$tpl->_ENGINE_parse_body("{fetchmail_rules}");
	$users->LoadModulesEnabled();
	if($users->EnableFetchmail==0){
		echo fetch_popup_enable();
		exit;
	}
	
$add_fetchmail=Paragraphe('add-fetchmail-64.png','{add_new_fetchmail_rule}','{fetchmail_explain}',"javascript:add_fetchmail_rules()",null);
$daemon_settings=Paragraphe('folder-tools2-64.png','{daemon_settings}','{daemon_settings_text}',"javascript:YahooWin('550','fetchmail.daemon.settings.php?ajax=yes','{fetchmail_daemon_settings}')");
$rules=Paragraphe('fetchmail-rule-64.png','{fetchmail_rules}','{fetchmail_rules_text}',"javascript:YahooWin('600','fetchmail.daemon.rules.php?ajax=yes','$fetchmail_rules_text')");
$logs=Paragraphe('64-logs.png','{events}','{events_text}',"javascript:s_PopUpScroll('fetchmail.index.php?events=true',800,600);");
$update=Paragraphe('64-recycle.png','{update_now}','{update_fetchmail_now}',"javascript:ReloadFetchMail();");
$html="
<table style='width:100%'>
<tr>
	<td valign='top'><div id='{$_GET["md"]}'></div></td>
	<td valign='top'>


	<table style='width:100%'>
	<tr>
		<td valign='top'>$rules</td>
		<td valign='top'>$daemon_settings</td>
	</tr>
	<tr>
		<td valign='top'>$add_fetchmail</td>	
		<td valign='top'>$logs</td>
	</tr>
	</table>
	</td>
</tr>
</table>
<script>
	 LoadAjax('{$_GET["md"]}','$page?fetch_status=yes');
</script>
	";


echo $tpl->_ENGINE_parse_body($html);
	
}



function fetch_popup_enable(){
	$sock=new sockets();
	$EnableFetchmail=$sock->GET_INFO("EnableFetchmail");
	
	
	$html="
	<H1 >{enable_fetchmail}</H1>
	<br><br>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='fetchFormEnable'>
			" . Paragraphe_switch_img('{enable_fetchmail}','{enable_fetchmail_text}','enable_fetchmail',$EnableFetchmail)."
		</div>
		</td>
		<td valign='top'><input type='button' value='{edit}&nbsp;&raquo&raquo;' OnClick=\"javascript:FetchMailEnable();\" style='font-size:16px;padding:15px'><hr><br>
		<div class=explain>{fetchmail_about}</div>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function fetch_enable_save(){
	$sock=new sockets();
	$sock->SET_INFO('EnableFetchmail',$_GET["enable_fetchmail"]);
	$fetch=new fetchmail();
	$fetch->Save();
	}
	

function fetch_events(){
		$sock=new sockets();
		
		$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?fetchmail-logs=yes')));
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			$val=htmlentities($val);
			
				$html=$html . "<div style='color:white;margin-bottom:3px;'><code>$val</code></div>";
			
			
		}
		
		echo RoundedBlack($html);
	
	
}


function FetchLogsPop(){
	
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
      document.getElementById('fetchev').innerHTML=tempvalue;
      }


function postlogs(){
	     var XHR = new XHRConnection();
	 	XHR.appendData('getFetchlogs','1');
		XHR.sendAndLoad('$page', 'GET',x_postlogs);

}




function demar1(){
   tant = tant+1;
   
        

   if (tant < 2 ) {                             //delai court pour le premier affichage !
      timerID = setTimeout(\"demar1()\",1000);
                
   } else {
               tant = 0;                            //reinitialise le compteur
               LoadAjax2('fetchev','$page?getFetchlogs=1');
                   
        demarre();                                 //on lance la fonction demarre qui relance le compteur
   }
}
</script>	
	<div id=wait style='margin:5px;font-weight:bold;font-size:12px;text-align:right'></div>
	<div id=fetchev style='width:100%'></div>
	
	<script>postlogs();</script>
	<script>demarre();</script>
		
	
	
	";
	$tpl=new template_users("{events}",$html);
	$tpl->nogbPopup=1;
	$tpl->_BuildPopUp($html,"{events}");
	echo $tpl->web_page;
	
}

function reloadfetchmail(){
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?restart-fetchmail=yes');	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success}");
	
}


	