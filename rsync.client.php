<?php
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");


if(isset($_GET["popup-index"])){index();exit;}
if(isset($_GET["remote_server_name"])){save_parameters();exit;}
if(isset($_GET["viewlog-js"])){viewlog_js();exit;}
if(isset($_GET["popup-events-index"])){view_log_popup();exit;}
if(isset($_GET["popup-events-list"])){echo view_log_popup_list();exit;}
if(isset($_GET["popup-events-id"])){view_log_popup_id();exit;}

js();




function viewlog_js(){
$page=CurrentPageName();
$prefix="events_".str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_RSYNC}');
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="


function {$prefix}Loadpage(){
	YahooWin5('650','$page?popup-events-index=yes','$title');
	setTimeout('RsyncMysqlEngine()',900);
	}
	
function ViewRsyncClientDetails(id){
YahooWin6('650','$page?popup-events-id='+id,'$title');
}
	
function RsyncMysqlEngine(){
	LoadAjax('view_log_popup_list','$page?popup-events-list=yes');
}


	
 {$prefix}Loadpage();

";
	
	echo $html;
}


function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_RSYNC}');
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="


function {$prefix}Loadpage(){
	YahooWin4('650','$page?popup-index=yes','$title');
	}
	

	var x_SaveRsyncClientConf= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
		 {$prefix}Loadpage();
		}	
		
	
function SaveRsyncClientConf(){
	ParseForm('rsyncsrverForm','$page',false,false,false,'rsyncsrver','',x_SaveRsyncClientConf);
}

 {$prefix}Loadpage();

";
	
	echo $html;
}

function view_log_popup(){
	
	
	
	$html="<H1>{APP_RSYNC} {events}</H1>
	" . RoundedLightWhite("<div id='view_log_popup_list' style='height:350px;overflow:auto'></div>");
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function view_log_popup_id(){
	
	$id=$_GET["popup-events-id"];
	$q=new mysql();
	$sql="SELECT * FROM rsync_events WHERE ID=$id";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));	
	
	
	$html="<table style='width:100%'>
	<tr>
		<td class=legend>{path}:</td>
		<td><code style='font-weight:bold;font-size:11px'>{$ligne["path"]}</code></td>
	</tr>
	<tr>
		<td class=legend>{sent_numfiles}:</td>
		<td><strong>{$ligne["numfiles"]}</strong></td>
	</tr>	
	<tr>
	<td colspan=2>
	<textarea style='width:100%;height:250px;padding:3px;border:1px dotted #CCCCCC'>{$ligne["events"]}</textarea>
	</td>
	</tr>
	</table>
	
	";
	
	
	$html="<H1>{APP_RSYNC} {events} $id</H1>
	" . RoundedLightWhite("<div id='view_log_popup_id' style='height:350px;overflow:auto'>$html</div>");
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}

function view_log_popup_list(){
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<h2>$error</H2>";
		die();
	}	
	
	$sql="SELECT * FROM rsync_events ORDER BY date_start DESC LIMIT 0,100";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");	
	
	$html="<table style='width:99%'>
	<tr>
		<th>&nbsp;</th>
		<th>{sent}&nbsp;kbs</th>
		<th>{start}</th>
		<th>{end}</th>
		<th>Kb/s</th>
		<th>{duration}</th>
		<th>{server}</th>
	</tr>";
	

$hier=date("Y-m-").(date("j")-1); 
	
	
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){

	preg_match("#([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#",$ligne["date_start"],$re);
	$time1=mktime($re[4],$re[5],$re[6],$re[2],$re[3],$re[1]);
	
	preg_match("#([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#",$ligne["date_end"],$re);
	$time2=mktime($re[4],$re[5],$re[6],$re[2],$re[3],$re[1]);	
	
	$duration=distanceOfTimeInWords($time1,$time2,true);
	
	$ligne["date_start"]=str_replace(date('Y-m-d'),"{today}",$ligne["date_start"]);
	$ligne["date_end"]=str_replace(date('Y-m-d'),"{today}",$ligne["date_end"]);
	$ligne["date_start"]=str_replace($hier,"{yesterday}",$ligne["date_start"]);
	$ligne["date_end"]=str_replace($hier,"{yesterday}",$ligne["date_end"]);	
	
	
	if($ligne["storage_server"]==null){$ligne["storage_server"]="undefined";}
	
	$js="ViewRsyncClientDetails('{$ligne["ID"]}')";
	
	if($ligne["failed"]==1){
		$tr=CellRollOver_rouge($js);
	}else{
		$tr=CellRollOver($js);
	}
	$html=$html."
	<tr $tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td>" . $ligne["sent_size"]."</td>
		<td>" . $ligne["date_start"]."</td>
		<td>" . $ligne["date_end"]."</td>
		<td>" . $ligne["speed"]."</td>
		<td>" . $duration."</td>
		<td>" . $ligne["storage_server"]."</td>
	</tr>
	";
	}
	
$html=$html . "</table>";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
}	



function index(){
	
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$users=new usersMenus();
	$ini->loadString($sock->GET_INFO("RsyncClientParameters"));
	
	$UseOnlyRsync=$sock->GET_INFO('UseOnlyRsync');
	if($UseOnlyRsync==null){$UseOnlyRsync=0;}
	
	$no_dar=Paragraphe_switch_img('{UseOnlyRsync}','{UseOnlyRsync_text}','UseOnlyRsync',$UseOnlyRsync,'{enable_disable}',220);
	
	
	if($ini->_params["CONF"]["remote_server_port"]==null){$ini->_params["CONF"]["remote_server_port"]=873;}
	if($ini->_params["CONF"]["second_remote_server_port"]==null){$ini->_params["CONF"]["second_remote_server_port"]=873;}
	if($ini->_params["CONF"]["second_bwlimit"]==null){$ini->_params["CONF"]["second_bwlimit"]=0;}
	if($ini->_params["CONF"]["bwlimit"]==null){$ini->_params["CONF"]["bwlimit"]=0;}
	if($ini->_params["CONF"]["remote_server_ssl"]==null){$ini->_params["CONF"]["remote_server_ssl"]=0;}
	if($ini->_params["CONF"]["second_remote_server_ssl"]==null){$ini->_params["CONF"]["second_remote_server_ssl"]=0;}
	
	$enable_remote_sync=Paragraphe_switch_img("{enable_remote_sync}","{enable_remote_sync_text}",
	'enable_remote_sync',$ini->_params["CONF"]["enable_remote_sync"],"enable_disable",200);
	
	$what_to_backup= Paragraphe('64-download.png','{what_to_backup}','{what_to_backup_text}',"javascript:YahooWin2(500,'dar.index.php?dar-target=yes','{what_to_backup}')");
	
	if($users->stunnel4_installed){
		$ssl="<tr>
		<td class=legend nowrap>{RsyncClientSSL}:</td>
		<td>" .Field_numeric_checkbox_img('remote_server_ssl',$ini->_params["CONF"]["remote_server_ssl"],"{enable_disable}")."</td>
	</tr>";
		
		$ssl2="	<tr>
		<td class=legend nowrap>{RsyncClientSSL}:</td>
		<td>" .Field_numeric_checkbox_img('second_remote_server_ssl',$ini->_params["CONF"]["second_remote_server_ssl"],"{enable_disable}")."</td>
	</tr>	";		
		
	}

	$form="
	<table style='width:100%'>
	<tr>
		<td colspan=2><H3>{main_backup_server}</H3></td>
	</tr>
	<tr>
		<td class=legend nowrap>{remote_server_name}:</td>
		<td>" . Field_text("remote_server_name",$ini->_params["CONF"]["remote_server_name"],"width:120px")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{remote_server_port}:</td>
		<td>" . Field_text("remote_server_port",$ini->_params["CONF"]["remote_server_port"],"width:60px")."</td>
	</tr>
$ssl		
	<tr>
		<td class=legend nowrap>{RsyncUploadBwlimit}:</td>
		<td>" . Field_text("bwlimit",$ini->_params["CONF"]["bwlimit"],"width:60px")."&nbsp;KBytes</td>
	</tr>	
	
	<tr>
		<td class=legend nowrap>{organization}:</td>
		<td>" . Field_text("organization",$ini->_params["CONF"]["organization"],"width:120px")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{username}:</td>
		<td>" . Field_text("username",$ini->_params["CONF"]["username"],"width:120px")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{password}:</td>
		<td>" . Field_password("password",$ini->_params["CONF"]["password"],"width:120px")."</td>
	</tr>						
	
<tr>
		<td colspan=2><hr>
			<H3>{alternate_backup_server}</H3>
			<p class=caption>{alternate_backup_server_text}</p>
			</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{remote_server_name}:</td>
		<td>" . Field_text("second_remote_server_name",$ini->_params["CONF"]["second_remote_server_name"],"width:120px")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{remote_server_port}:</td>
		<td>" . Field_text("second_remote_server_port",$ini->_params["CONF"]["second_remote_server_port"],"width:60px")."</td>
	</tr>	
	$ssl2
	<tr>
		<td class=legend nowrap>{RsyncUploadBwlimit}:</td>
		<td>" . Field_text("second_bwlimit",$ini->_params["CONF"]["second_bwlimit"],"width:60px")."&nbsp;KBytes</td>
	</tr>	
	
	
	<tr>
		<td class=legend nowrap>{organization}:</td>
		<td>" . Field_text("second_organization",$ini->_params["CONF"]["second_organization"],"width:120px")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{username}:</td>
		<td>" . Field_text("second_username",$ini->_params["CONF"]["second_username"],"width:120px")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{password}:</td>
		<td>" . Field_password("second_password",$ini->_params["CONF"]["second_password"],"width:120px")."</td>
	</tr>						
	</table>	
	
	
	
";
	
	$form=RoundedLightWhite($form);
	
	
	$html="<H1>{APP_RSYNC}</H1>
	<div id='rsyncsrver'>
	<form name='rsyncsrverForm'>
	<p class=caption>{APP_RSYNC_CLIENT_EXPLAIN}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<table style='width:100%'>
				<tr>
					<td>$what_to_backup<div style='width:220px;margin-top:5px;'>$enable_remote_sync</div><div style='width:220px;margin-top:5px;'>$no_dar</div></td>
				</tr>
			</table>
		</td>
		<td valign='top'>$form</td>
	</tr>
	<tr><td colspan=2 align='right'>
	
		<hr>
		<input type='button' OnClick=\"javascript:SaveRsyncClientConf();\" value='{edit}&nbsp;&raquo;'>
	</td>
	</tr>
	</table>
	
	</form>
	</div>
	";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"dar.index.php");
	
}

function save_parameters(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	
	if($_GET["enable_remote_sync"]==0){$_GET["UseOnlyRsync"]=0;}
	$sock->SET_INFO("UseOnlyRsync",$_GET["UseOnlyRsync"]);
	unset($_GET["UseOnlyRsync"]);
	
	while (list ($num, $ligne) = each ($_GET) ){$ini->_params["CONF"][$num]=$ligne;}
	$sock->SaveConfigFile($ini->toString(),"RsyncClientParameters");
	}


?>