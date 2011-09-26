<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.ntpd.inc');
	$user=new usersMenus();
	if($user->AsArticaAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["ntpdAdd"])){ntpdAdd();exit;}
	if(isset($_GET["ntpdservermove"])){ntpdservermove();exit;}
	if(isset($_GET["ntpdserverdelete"])){ntpdserverdelete();exit;}
	if(isset($_GET["NTPDEnabled"])){NTPDEnabled();exit;}
	if(isset($_GET["op"])){main_switch_op();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["enable-ntpd-switch"])){ntpd_switch();exit;}
	if(isset($_GET["list"])){echo main_server_list();exit;}
	if(isset($_GET["country"])){ntpdAddCountry();exit;}
	js();
	
	
function js(){

	$page=CurrentPageName();
	$prefix="ntpdpage";
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_NTPD}');
	$give_server_name=$tpl->_ENGINE_parse_body('{give_server_name}');
	
$html= "

		function {$prefix}load(){
			YahooSetupControl(650,'$page?popup=yes','$title');
			{$prefix}timeout=0;
			
		}

		
		function {$prefix}FILL(){
			{$prefix}timeout={$prefix}timeout+1;
			if({$prefix}timeout>10){alert('timeout');return;}
			if(!document.getElementById('ntpd_main_config')){
				setTimeout(\"{$prefix}FILL()\",900);
				return;
			}
			
			LoadAjax('ntpd_main_config','$page?main=yes');
			if(YahooWinSOpen()){YahooWinSHide();}
			
			ChargeLogs();
			{$prefix}demarre();
	}
	
	
var refresh_server_list= function (obj) {
			LoadAjax('serverlist','$page?list=yes');
			}		
		
		function ntpdAdd(){
		    var server=prompt('$give_server_name');
		    if(server){
	         var XHR = new XHRConnection();
		      XHR.appendData('ntpdAdd',server);
		      XHR.sendAndLoad('ntpd.index.php', 'GET',refresh_server_list);      
		    }
		}

		function ntpdservermove(num,dir){
		      var XHR = new XHRConnection();
		      XHR.appendData('ntpdservermove',num);
		      XHR.appendData('direction',dir);
		      XHR.sendAndLoad('ntpd.index.php', 'GET',refresh_server_list);    
		    }
		    
		function ntpdserverdelete(num){
		      var XHR = new XHRConnection();
		      XHR.appendData('ntpdserverdelete',num);
		      XHR.sendAndLoad('ntpd.index.php', 'GET',refresh_server_list);      
		    }
		
		function ntpdSave(){
		 YahooWin(440,'ntpd.index.php?op=-1');
		        for(var i=0;i<5;i++){
		                setTimeout('ntpdSave_run('+i+')',1500);
		        }
		}
		function ntpdSave_run(number){
		        LoadAjax2('message_'+number,'ntpd.index.php?op='+number)
		        }

{$prefix}load();
";
		
		echo $html;

}
	
	
function popup(){
	
echo main_tabs();
exit;
	
	$html=
	"
	

	<p class=caption>{ntp_about}</p>
	<table style='width:100%'>
	<tr>
	<td valign='top'>".Paragraphe('connection-add-64.png','{add_title_server}','{add_text_server}',"javascript:ntpdAdd();",'add_title_server')."</td>
	<td valign='top'><div id='ntpd_services_status'></div><br></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='ntpd_main_config'></div>
		</td>
	</tr>
	</table>
	";
	

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	}



function main_tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["index"]="{index}";
	$array["yes"]='{main_settings}';
	$array["logs"]='{events}';	
	$array["ntpdconf"]='{ntpdconf}';
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_parse_body("<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n");
		}
	
	return "
	<div id=ntpd_main_config style='width:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#ntpd_main_config').tabs({
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

function index(){
	$status=main_status();	
	$page=CurrentPageName();

	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div class=explain>{ntp_about}</p>
	</td>
	<td valign='top'>
		<div id='ntpd-status'></div>
		<div id='enable-ntpd'></div>
	</td>
	</tr>
	</table>
	
	
	<script>
		LoadAjax('enable-ntpd','$page?enable-ntpd-switch=yes');
		
	
		var X_SaveEnableNTPDSwitch= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadAjax('enable-ntpd','$page?enable-ntpd-switch=yes');
			NTPD_STATUS();
			}		
		
		function SaveEnableNTPDSwitch(){
			var XHR = new XHRConnection();
      		XHR.appendData('NTPDEnabled',document.getElementById('NTPDEnabled').value);
      		document.getElementById('enable-ntpd').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
      		XHR.sendAndLoad('$page', 'GET',X_SaveEnableNTPDSwitch);    
		
		}
		
		
		function NTPD_STATUS(){
			LoadAjax('ntpd-status','$page?status=yes');
		
		}
		NTPD_STATUS();
	</script>
	";
	
	
	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function ntpd_switch(){
	$sock=new sockets();
	$NTPDEnabled=$sock->GET_INFO("NTPDEnabled");	
	$NTPDServerEnabled=$sock->GET_INFO("NTPDServerEnabled");

	if($NTPDEnabled==0){
		if($NTPDServerEnabled==1){$NTPDEnabled=1;}
	}
	
	
	$enable=Paragraphe_switch_img("{ENABLE_APP_NTPD}","{APP_NTPD_ENABLE_TEXT}","NTPDEnabled",$NTPDEnabled,550);
	$tpl=new templates();
	
	$enable=$enable."
	<hr>
	<div style='text-align:right'>".button("{edit}","SaveEnableNTPDSwitch()")."</div>";
	
	echo $tpl->_ENGINE_parse_body($enable);	
}	


function main_switch(){
	
	switch ($_GET["main"]) {
		case "index":index();exit;break;
		case "yes":ntpd_main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "syncevents":main_sync();exit;break;
		case "conf":echo main_conf();exit;break;
		case "ntpdconf":echo main_ntpdconf();exit;break;
		case "server_list":echo main_server_list();exit;break;
		default:
			break;
	}
	
	
}	

function main_status(){
	$users=new usersMenus();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('services.php?ntpd-status=yes')));	
	$status=DAEMON_STATUS_ROUND("NTPD",$ini,null);
	$tpl=new templates();
	
	$refresh="<div style='text-align:right'>".imgtootltip("refresh-24.png","{refresh}","NTPD_STATUS()")."</div>";
	
	return $tpl->_ENGINE_parse_body($status.$refresh);		
	
	
}


function ntpd_main_config(){
$ntp=new ntpd(true);
$array=$ntp->ServersList();

while (list ($num, $val) = each ($array) ){
	$i[$num]=$num;
}
$i[null]="{choose}";

$choose=Field_array_Hash($i,'ntpd_servers_choosen',null,null,null,0,'font-size:14px;padding:3px');

	 $page=CurrentPageName();
	 $form="
	 <table style='width:100%'>
	 <tr>
	 	<td valign='top' style='width:2%;padding-top:5px'>$choose</td>
	 	<td style='width:100%' align='left' valign='top'>". button('{apply}',"ntpd_choose_server()")."</td>
	 </tr>
	 </table>
	 <div class=explain>{how_to_find_timeserver}</div><hr>

	 
	
		<div style='text-align:right;width:100%'>". button("{add}","ntpdAdd()")."&nbsp;|&nbsp;". button("{ntpd_apply}","ntpdSave()")."</div>
	 	<div id=serverlist style='width:100%;height:250px;overflow:auto'>" .main_server_list() . "</div>
	 	<hr>
	 	<div style='text-align:right;width:100%'></div>
	 	
	 	
	 	<script>
	 		function ntpd_choose_server(){
				var XHR = new XHRConnection();
	      		XHR.appendData('country',document.getElementById('ntpd_servers_choosen').value);
	      		document.getElementById('serverlist').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	      		XHR.sendAndLoad('$page', 'GET',X_ntpd_choose_server);    
			}
	 		
		var X_ntpd_choose_server= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('ntpd_main_config');
			}		
		
	
	 		
	 	</script>
		";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$entete$form");
	
}

function main_server_list(){
	$ntp=new ntpd();
	if(!is_array($ntp->servers)){return null;}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%;margin-top:10px'>
<thead class='thead'>
	<tr>
	<th colspan=3>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while (list ($num, $val) = each ($ntp->servers) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html . "<tr class=$classtr>
		<td nowrap><strong><code style='font-size:14px'>$val</code></strong></td>
		<td width=1% valign='middle'>" . imgtootltip('arrow-down-32.png','{down}',"ntpdservermove('$num','down')")."</TD>
		<td width=1% valign='middle'>" . imgtootltip('arrow-up-32.png','{up}',"ntpdservermove('$num','up')")."</TD>
		<td width=1% valign='middle'>" . imgtootltip('delete-32.png','{delete}',"ntpdserverdelete('$num')")."</TD>		
		</tr>
		";
		
	}
	
	$html=$html . "</table>";
	return $html;
	
}

function main_ntpdconf(){
	$ntpd=new ntpd();
	$conf=explode("\n",$ntpd->ntpdConf);
	
	while (list ($num, $val) = each ($conf) ){
		if($val==null){continue;}
		$dats[]="<div><code>".htmlspecialchars($val)."</code></div>";
	}
	
	
	$entete="	 ". RoundedLightWhite("
	 <div style='padding:5px;margin:10px;width:95%;height:300px;overflow:auto'>
	 ". implode("\n",$dats)."
	 </div>");	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$entete");	
}

function main_sync(){
	$sql="SELECT * FROM events WHERE event_id='5' ORDER BY ID DESC LIMIT 0,100";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_events');
	$table="<table style='width:550px'>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		
		$ligne["text"]=htmlspecialchars($ligne["text"]);
		$ligne["text"]=nl2br($ligne["text"]);
		$table=$table . 
		
		"
		<tr><td colspan=3><hr></td></tr>
		<tr>
			<td valign='top' width=1%><img src='img/fw_bold.gif'></td>
			<td valign='top' nowrap><strong>{$ligne["zDate"]}</strong></td>
			<td valign='top'><code>{$ligne["text"]}</code></td>	
		</tr>		
			";
			
	}$table=$table . "</table>"			;
	
	$table=RoundedLightGrey($table);
	
$entete="<br>
	 <H5>{syncevents}</H5>
	 <br><div style='width:550px'>$table</div>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($entete);
	
}

function main_switch_op_save(){
	$ntp=new ntpd();
	$ntp->SaveToLdap();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<strong>{save_ntpd_ok}</strong>");
}

function main_switch_op_server(){
	$ntp=new ntpd();
	$ntp->SaveToServer();
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<strong>{save_toserver_ok}</strong>");	
	
}

function main_switch_op_end(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<p class=caption>{close_windows}</p>");		
	
}

function main_switch_op(){
	
	switch ($_GET["op"]) {
		case 0:main_switch_op_save();exit;break;
		case 1:main_switch_op_server();exit;break;
		case 2:main_switch_op_end();exit;break;
		default:
			break;
	}
	
	
	$html="
	<H5>{ntpd_apply}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/folder-tasks-64.jpg'></td>
	<td valign='top'>
		<div id='message_0' style='margin:3px'></div>
		<div id='message_1' style='margin:3px'></div>
		<div id='message_2' style='margin:3px'></div>
	
	</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}



function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<iframe src='ntpd.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}

	
function ntpdAddCountry(){
	$ntp=new ntpd();
	$countries=$ntp->ServersList();
	unset($ntp->servers);
	while (list ($num, $server) = each ($countries[$_GET["country"]]) ){
		$ntp->servers[]=$server;
		
	}
	$ntp->SaveToLdap();
	
	
}
	
function ntpdAdd(){
	$ntp=new ntpd();
	$ntp->servers[]=$_GET["ntpdAdd"];
	$ntp->SaveToLdap();
	}
function ntpdservermove(){
	$ntp=new ntpd();
	$ntp->servers=array_move_element($ntp->servers,$ntp->servers[$_GET["ntpdservermove"]],$_GET["direction"]);
	$ntp->SaveToLdap();
	}
function ntpdserverdelete(){
	$ntp=new ntpd();
	unset($ntp->servers[$_GET["ntpdserverdelete"]]);
	$ntp->SaveToLdap();
	}
function NTPDEnabled(){
	$sock=new sockets();
	$sock->SET_INFO("NTPDEnabled",$_GET["NTPDEnabled"]);
	$sock->SET_INFO("NTPDServerEnabled",$_GET["NTPDEnabled"]);
	$sock->getFrameWork("cmd.php?ntpd-restart=yes");

}


?>
