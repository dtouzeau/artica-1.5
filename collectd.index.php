<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.collectd.inc");

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){die();}


if(isset($_GET["PopUp"])){echo "<div id='main_collectd_config' style='background-color:#FFFFFF;margin-top:2px;'>".mysql_main_switch()."</div>";exit;}
if(isset($_GET["_status"])){echo _status();exit;}
if(isset($_GET["main"])){echo mysql_main_switch();exit;}
if(isset($_GET["instance_choose"])){echo collectd_selector_instance();exit;}
if(isset($_GET["type_choose"])){echo collectd_selector_type_data();exit;}
if(isset($_GET["GenerateGraph"])){echo GenerateGraph();exit;}
if(isset($_GET["SaveEnableCollectdDaemon"])){EnableCollectdDaemonSave();exit;}

if($_GET["script"]=="plugin_choose"){echo collectd_selector_plugin();exit;}
if($_GET["script"]=="type_choose"){echo collectd_selector_type();exit;}
if($_GET["script"]=="buildgraph"){echo collectd_selector_buildgraph();exit;}
if($_GET["script"]=="EnableCollectdDaemon"){echo EnableCollectdDaemon();exit;}




	main_page();
	
function main_page(){
	
$page=CurrentPageName();
	if($_GET["hostname"]==null){
		$user=new usersMenus();
		$_GET["hostname"]=$user->hostname;}
	
	$html=
"<span id='scripts'><script type=\"text/javascript\" src=\"collectd.index.php?script=load_functions\"></script></span>	
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",3000);
      } else {
               tant = 0;
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('mysql_status','collectd.index.php?_status=yes');
	
	}
	
</script>		
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_collectd.png'style='margin-right:70px;margin-left:70px;margin-bottom:5px'></td>
	<td valign='top'>
		<div id='mysql_status'></div>
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			<table style='width:100%'>	
			<tr>
			<td valign='top'>
				<div id='main_collectd_config'></div>
			</td>
			<td valign='top'>
				<div id='mysqlenable'></div>
			</td>
			</tr>
			</table>
			
		</td>
	</tr>
	</table>
	<script>demarre();ChargeLogs();LoadAjax('main_collectd_config','collectd.index.php?main=$num&hostname={$_GET["hostname"]}');</script>
	
	";
	
	$tpl=new template_users('{APP_COLLECTD}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	
	
	
}

function mysql_tabs(){
	return null;
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["settings"]='{settings}';
	$array["performances"]='{performances}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_mysql_config','collectd.index.php?main=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<div id=tablist>$html</div>");		
}



function _status(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('collectdstatus'));
	$status=DAEMON_STATUS_ROUND("COLLECTD",$ini,null);
	echo $tpl->_ENGINE_parse_body($status);
	}
function mysql_main_switch(){
	$tab=mysql_tabs();
	
	switch ($_GET["main"]) {
		case "settings":echo $tab.collectd_index();break;
		
	
		default:echo $tab.collectd_index();break;
	}
	
	
}



function collectd_index(){

return collectd_selector();	
	
	
}


function collectd_selector_plugin(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$text=$tpl->_ENGINE_parse_body('{span_plugin_instance_text}');
	$html="
	var plugin='';
	plugin=document.getElementById('plugin').value;
	document.getElementById('span_plugin_instance_text').innerHTML='$text';
	document.getElementById('span_type_instance_text').innerHTML='';
	document.getElementById('span_type_instance').innerHTML='';
	document.getElementById('collectd_graphs').innerHTML='';
	LoadAjax('span_plugin_instance','collectd.index.php?instance_choose='+plugin);
	";
	return $html;	
	}
	
function collectd_selector_type(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$text=$tpl->_ENGINE_parse_body('{span_type_instance_text}');
	$html="
	var plugin='';
	plugin=document.getElementById('plugin').value;
	var typ=document.getElementById('type').value;
	document.getElementById('span_type_instance_text').innerHTML='$text';
	document.getElementById('collectd_graphs').innerHTML='';
	LoadAjax('span_type_instance','collectd.index.php?plugin='+plugin+'&type_choose='+typ);
	LoadAjax('collectd_graphs','collectd.index.php?GenerateGraph='+plugin+'&type='+typ);
	
	";
	return $html;	
	}
	
	
function collectd_selector_buildgraph(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	plugin=document.getElementById('plugin').value;
	var typ=document.getElementById('type').value;
	var data='';
	
	if(document.getElementById('data')){
		data=document.getElementById('data').value;
	}
	
	LoadAjax('collectd_graphs','collectd.index.php?GenerateGraph='+plugin+'&type='+typ+'&data='+data);
	";
	return $html;		
}
function GenerateGraph(){
	$plugin=$_GET["GenerateGraph"];
	$type=$_GET["type"];
	
	if($type==null){return null;}
	if($_GET["timespan"]==null){$_GET["timespan"]="D";}
	$type_instance=$_GET["data"];
	if($type_instance<>null){
	 $type_instance="&type_instance=$type_instance"	;
	}
	
	
	$endate=GetTimeStamp();
	
	
	//ON ZOOM
	switch ($_GET["timespan"]) {
		case "H":
			$startDate=GetTimeStamp(-1);
			$timespan_in="H";
			$timespan_out="D";
			break;		
		
		case "D":
			$startDate=GetTimeStamp(-24);
			$timespan_in="H";
			$timespan_out="W";
			break;
			
		case "W":
			$startDate=GetTimeStamp(-420);
			$timespan_in="D";
			$timespan_out="M";
			break;	
			
		case "M":
			$startDate=GetTimeStamp(-720);
			$timespan_in="W";
			$timespan_out="Y";
			break;	

		case "Y":
			$startDate=GetTimeStamp(-8760);
			$timespan_in="M";
			$timespan_out="Y";
			break;	

		default:	
			$startDate=GetTimeStamp(-24);
			$timespan_in="H";
			$timespan_out="D";
			break;			

	}	
	
	//$uri="cgi-bin/collection3/bin/graph.cgi?hostname=localhost&plugin=$plugin&type=$type{$type_instance}&timespan={$_GET["timespan"]}&action=show_selection";
	$uri="cgi-bin/collection3/bin/graph.cgi?hostname=localhost&plugin=$plugin&type=$type;{$type_instance}&begin=$startDate&end=$endate";
	$date_human=date("d/m/Y H:i:s",$startDate);
	$date_human2=date("d/m/Y H:i:s",$endate);
	
	$subtext="
	<div style='text-align:center;font-size:12px;margin:3px;padding:3px;border:1px dotted #CCCCCC'>
		$date_human&nbsp;&raquo;&raquo;&nbsp;$date_human2
	</div>";
	
	

	

	$zoom_in="LoadAjax('collectd_graphs','collectd.index.php?GenerateGraph=$plugin&type=$type&data={$_GET["data"]}&timespan=$timespan_in');";
	$zoom_out="LoadAjax('collectd_graphs','collectd.index.php?GenerateGraph=$plugin&type=$type&data={$_GET["data"]}&timespan=$timespan_out');";
	$refresh="LoadAjax('collectd_graphs','collectd.index.php?GenerateGraph=$plugin&type=$type&data={$_GET['data']}&timespan={$_GET["timespan"]}');";
	$image="
	
	
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
		$subtext
		<img src='$uri' style='padding:3px;margin:5px;margin-top:15px;border:1px solid #CCCCCC'>
	</td>
	<td valign='top' width=99% align='left'>
	<input type='hidden' id='mem_timespan' value='{$_GET["timespan"]}'>
	<input type='hidden' id='mem_plugin' value='{$plugin}'>
	<input type='hidden' id='mem_type' value='{$type}'>
	<input type='hidden' id='mem_instance' value='{$_GET["data"]}'>
		<table style='width:100%;margin-top:20px'>
			<tr>
				<td width=1%>" . imgtootltip("32-zoom-in.png","{zoom_in}",$zoom_in)."</td>
			</tr>
			<tr>
				<td width=1%>" . imgtootltip("32-zoom-out.png","{zoom_out}",$zoom_out)."</td>
			</tr>
			<tr>
				<td width=1%>" . imgtootltip("32-refresh.png","{refresh}",$refresh)."</td>
			</tr>	
		</table>					
				
	</td>
	</tr>
	</table>
	
	
	
	
	
	";
	return $image;
	
	
	
}

function GetTimeStamp($addhour=null){
	if(!is_numeric($addhour)){
		return time();
	}
	
	
$timestamp = time();
$date_time_array = getdate($timestamp);
$hours = $date_time_array["hours"];
$minutes = $date_time_array["minutes"];
$seconds = $date_time_array["seconds"];
$month = $date_time_array["mon"];
$day = $date_time_array["mday"];
$year = $date_time_array["year"];
return mktime($hours + $addhour,$minutes,$seconds,$month,$day,$year);	
}

function collectd_selector_instance(){
	$plugin=$_GET["instance_choose"];
	$collectd=new collectd();
	$array=$collectd->graphs_array[$plugin][0]["type"];
	//print_r($collectd->graphs_array[$plugin]);
	while (list ($num, $val) = each ($array) ){
		$hash[$val["type"]]=$val["type"];
		
	}
	
	$hash[null]="{select}";
	$tpl=new templates();
	$field=
	
	"".
	Field_array_Hash($hash,'type',null,"Loadjs('collectd.index.php?script=type_choose')");
	echo $tpl->_ENGINE_parse_body($field);
	}
	
function collectd_selector_type_data(){
	$plugin=$_GET["plugin"];
	$type=$_GET["type_choose"];
	$collectd=new collectd();
	$tpl=new templates();
	$count=0;
	$array=$collectd->graphs_array[$plugin][0]["type"];	
	while (list ($num, $val) = each ($array) ){
		if($val["type"]==$type){
			if($val["type_instace"]==null){continue;}
			$count=$count+1;
			$hash[$val["type_instace"]]=$val["type_instace"];
		}
		
		
	}
	
	if($count==0){
		$html="
		
		<input type='button' value='{show_graph}&nbsp;&raquo;&raquo;' OnClick=\"javascript:Loadjs('collectd.index.php?script=buildgraph')\">";
		echo $tpl->_ENGINE_parse_body($html);	
		return ;
	}
	
	
	$hash[null]="{select}";
	
	$field=Field_array_Hash($hash,'data',null,"Loadjs('collectd.index.php?script=buildgraph')");
	echo $tpl->_ENGINE_parse_body($field);	
	
}


function collectd_selector(){
	$artica=new artica_general();
	$page=CurrentPageName();
	$collectd=new collectd();
	$array=$collectd->graphs_array;
	$collectd->field_h[null]='{select}';
	$plugin=Field_array_Hash($collectd->field_h,'plugin',null,"Loadjs('collectd.index.php?script=plugin_choose')");
	
	if(isset($_GET['PopUp'])){$title="<H1>{APP_COLLECTD}</H1>";}
	
	$html="$title
	
		<div style='width:99%;padding:3px;margin:3px;margin-top:10px;border:1px solid #DEDEDE;background-image:url(img/bg-items-green.png);background-repeat:repeat-x;background-color:#FFFFFF'>
	<table style='width:100%;'>
	<tr>
		<td class=legend>{plugin}:</td>
		<td>$plugin</td>
		<td class=legend><span id='span_plugin_instance_text'></span></td>
		<td><span id='span_plugin_instance'></span></td>
		
		<td class=legend nowrap><span id='span_type_instance_text'></span></td>
		<td><span id='span_type_instance'></span></td>		
	</tr>
	</table>
	

		<div style='background-image:url(img/note-body-bg.png);padding:-1px;margin:0px;background-position:bottom;width:99%'>
			<div id='collectd_graphs' style='background-color:#FFFFFF;margin-top:2px;'>
			</div>
			
			<div id='enable'>
			<table style='width:100%;background-color:white'>
			<tr>
			<td width=1%>" . Field_numeric_checkbox_img('EnableCollectdDaemon',$artica->EnableCollectdDaemon,'{enable_disable}')."</td>
			<td class=legend align='left' width=1% nowrap>{enable_collected_daemon}</td>
			<td align='left' width=98%><input type='button' OnClick=\"javascript:Loadjs('collectd.index.php?script=EnableCollectdDaemon')\" value='{edit}&nbsp;&raquo;'></td>
			</tr>
			</table>
			</div>
			
	</div>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}
function EnableCollectdDaemon(){
	
	$html="
	
	
var x_SaveEnableCollectdDaemon= function (obj) {
		var tempvalue=obj.responseText;
		alert(tempvalue);
	}
	var enable=document.getElementById('EnableCollectdDaemon').value;
	var XHR = new XHRConnection();
	XHR.appendData('SaveEnableCollectdDaemon',enable);
	XHR.sendAndLoad('collectd.index.php', 'GET',x_SaveEnableCollectdDaemon);			
	";
	
	echo $html;
	}
	
	
function EnableCollectdDaemonSave(){
	$artica=new artica_general();
	$artica->EnableCollectdDaemon=$_GET["SaveEnableCollectdDaemon"];
	$artica->Save();
	}



?>
