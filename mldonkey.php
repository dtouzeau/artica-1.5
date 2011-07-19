<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.donkey.inc');
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-status"])){popup_status();exit;}
	if(isset($_GET["popup-users"])){popup_users();exit;}
	if(isset($_GET["popup-settings"])){popup_settings();exit;}
	if(isset($_GET["popup-emule-connected"])){popup_IDs();exit;}
	if(isset($_GET["EnableMLDonKey"])){EnableMLDonKey();exit;}
	if(isset($_GET["internal-settings"])){popup_internal_settings();exit;}
	if(isset($_GET["ED2K-port"])){SaveParams();exit;}
	if(isset($_GET["ForceDeleteEDKUser"])){ForceDeleteEDKUser();exit;}
	if(isset($_GET["popup-bandwith"])){popup_bandwith();exit;}
	if(isset($_GET["schedule-parameter"])){popup_bandwith_params();exit;}
	if(isset($_GET["max_hard_upload_rate"])){popup_bandwith_save();exit;}
	if(isset($_GET["schedule-parameter-list"])){popup_bandwith_list();exit;}
	if(isset($_GET["DelScheduleEDonkey"])){popup_bandwith_del();exit;}
	js();
	
	
	
function js(){

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_MLDONKEY}");
	$page=CurrentPageName();
	
	$start="MLDONKEY_START()";
	if(isset($_GET["in-front-ajax"])){$start="MLDONKEY_START2()";}
	
	$html="
	
	function MLDONKEY_START(){YahooWin2('650','$page?popup=yes','$title');}
	
	function MLDONKEY_START2(){
		$('#BodyContent').load('$page?popup=yes');}		
	
	var X_SaveMLDOnkeyEnable= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_mldonkey');
		}
			


function SaveMLDOnkeyEnable(){
		var XHR = new XHRConnection();
		XHR.appendData('EnableMLDonKey',document.getElementById('EnableMLDonKey').value);
		document.getElementById('others-params').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveMLDOnkeyEnable);		
	
}	


function ForceDeleteEDKUser(uid){
		var XHR = new XHRConnection();
		XHR.appendData('ForceDeleteEDKUser',uid);
		document.getElementById('users-mldkey-table').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveMLDOnkeyEnable);
}
	
	$start;
	";
	
	echo $html;
	
}

function popup_IDs(){
	$ml=new EmuleTelnet();
	$array=$ml->eMuleIDServers();
	
	$html="
	<table style='width:100%'>
	<tr>
		<th colspan=2>{server}</th>
		<th>{status}</th>
	</tr>
	
	";
	
	while (list ($num, $ligne) = each ($array) ){
		$img="ok42.png";
		if(trim($ligne["ID"])=="LowID"){$img="warning42.png";}
		$ligne["NAME"]=htmlspecialchars($ligne["NAME"]);
		$html=$html."
		<tr>
			<td width=1%><img src='img/48-idisk-server.png'></td>
			<td valign='middle'>
				<strong>
					<li style='font-size:14px'>{$ligne["NAME"]}</li>
					<li style='font-size:14px'>{$num}</li>
				</strong>
			</td>
			
			<td valign='middle' align='center' width=1%>
				<img src='img/$img'>
			</td>
		
		<tr>
			<td colspan=3><hr></td>
		</tr>
		
		";
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}
	
	
function popup(){
	$page=CurrentPageName();
	$array["popup-status"]='{status}';
	$array["popup-settings"]='{parameters}';
	$array["popup-bandwith"]='{bandwith}';
	
	$array["popup-emule-connected"]="{eMuleIDServers}";
	
	

	
	$tpl=new templates();

	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_mldonkey style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_mldonkey').tabs({
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

function popup_users(){
	$sock=new sockets();
	$EnableMLDonKey=$sock->GET_INFO("EnableMLDonKey");
	if($EnableMLDonKey==null){$EnableMLDonKey=1;}
	if($EnableMLDonKey==0){return;}
	
	$ml=new EmuleTelnet();
	$ml->LoadUsers();
	if(!is_array($ml->HASH_USERS)){return;}
	
	while (list ($uid, $array) = each ($ml->HASH_USERS) ){
		if($uid=="admin"){continue;}
		$tr[]=
		"<table style='width:120px;margin:3px;padding:3px;border:1px solid #CCCCCC'>
		<tr ". CellRollOver(MEMBER_JS($uid,1,1)).">
			<td valign='top' align='center'>".imgtootltip("user-48.png","$uid",MEMBER_JS($uid,1,1))."</td>
			<td width=1%>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top' align='center'><span style='font-size:13px'>$uid</span></td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","ForceDeleteEDKUser('$uid')")."</td>
		</tr>
		</table>";
		
		
		
	}
	
$tables[]="<table style='width:100%;margin-top:9px'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==4){$t=0;$tables[]="</tr><tr>";}
		}

if($t<4){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	
	
$html="<hr><div style=''><H3>{members}:</H3>". implode("\n",$tables)."</div>";	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function popup_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?mldonkey-ini-status=yes')));
	$status=DAEMON_STATUS_ROUND("APP_MLDONKEY",$ini);
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<img src='img/200-emule.png'>
			<hr>
		</td>
		<td valign='top'>
			$status		
		</td>			
	</tr>
	</table>
	
				<div style='font-size:14px'>
			{APP_MLDONKEY_TEXT}
			</div>
	
	<div id='users-mldkey-table'></div>
	
	<script>
		LoadAjax('users-mldkey-table','$page?popup-users=yes');
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_settings(){
	$sock=new sockets();
	$page=CurrentPageName();
	$EnableMLDonKey=$sock->GET_INFO("EnableMLDonKey");
	if($EnableMLDonKey==null){$EnableMLDonKey=1;}
	$gbl=Paragraphe_switch_img("{ACTIVATE_MLDONKEY}","{ACTIVATE_MLDONKEY_TEXT}","EnableMLDonKey",$EnableMLDonKey,null,300);
		
	if($EnableMLDonKey==1){$script="
	<script>
		LoadAjax('others-params','$page?internal-settings=yes');
	</script>
	";
	}
	
	$html="
	$gbl
	<hr>
	<div style='text-align:right'>". button("{apply}","SaveMLDOnkeyEnable()")."</div>
	
	<div id='others-params'></div>
	$script
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function EnableMLDonKey(){
	
	$sock=new sockets();
	$sock->SET_INFO("EnableMLDonKey",$_GET["EnableMLDonKey"]);
	$sock->getFrameWork("cmd.php?restart-mldonkey=yes");
	
}
	
function popup_internal_settings(){
	$ml=new EmuleTelnet();
	$array=$ml->parameters();
	$page=CurrentPageName();
	
/*
 * 	<tr>
		<td colspan=3><H3>{update_servers_sources}</H3><hr></td>
	</tr>
	<tr>
		<td style='font-size:13px' class=legend>{ED2K-update_server_list_server}:</td>
		<td>". Field_checkbox("ED2K-update_server_list_server","true",strtolower($array["ED2K-update_server_list_server"]))."</td>
		<td>". help_icon("{ED2K-update_server_list_server_help}")."</td>
	</tr>	
 */	
	
	$array["ED2K-port-UDP"]=$array["ED2K-port"]+4;
	
	$html="
	<center>
	<div style='margin-top:5px;padding:5px;border:1px solid #CCCCCC;width:450px'>
	<table style='width:100%'>
	
	<tr>
		<td style='font-size:13px' class=legend>{ED2K-port}:</td>
		<td>". Field_text("ED2K-port",$array["ED2K-port"],"font-size:13px;padding:3px;width:45px")."</td>
		<td>". help_icon("{ED2K-port_help}")."</td>
	</tr>
	<tr>
		<td style='font-size:13px' class=legend>{ED2K-udp-port}:</td>
		<td><strong style='font-size:13px'>{$array["ED2K-port-UDP"]}</strong></td>
		<td>". help_icon("{ED2K-port_help}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>".button("{apply}","SaveEDKParams()")."</td>
	</tr>
	
	</table>
	</div>
	</center>
	<script>
	var X_SaveEDKParams= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_mldonkey');
		}
			


function SaveEDKParams(){
		var XHR = new XHRConnection();
		XHR.appendData('ED2K-port',document.getElementById('ED2K-port').value);
		document.getElementById('others-params').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveEDKParams);		
	
}
</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function ForceDeleteEDKUser(){
	$ml=new EmuleTelnet();
	$ml->UserDelete($_GET["ForceDeleteEDKUser"]);
	
}

function SaveParams(){
	$ml=new EmuleTelnet();
	while (list ($num, $val) = each ($_GET) ){
		$ml->parameters_save($num,$val);
	}
	
	$ml->SaveConfig();
}

function popup_bandwith(){
	
	$page=CurrentPageName();
	$ml=new EmuleTelnet();
	$array=$ml->parameters();
	$tpl=new templates();
	$schedule=$tpl->_ENGINE_parse_body("{schedule}");
	$html="
	<div style='font-size:14px;margin:9px'>{edonkey_bandwith_explain}</div>
	<H3>{current_parameters}</H3>
	
	<strong style='font-size:12px'>{max_hard_upload_rate}:{$array["max_hard_upload_rate"]}&nbsp;kb/s&nbsp;|
	{max_hard_download_rate}:{$array["max_hard_download_rate"]}&nbsp;Kb/s</strong>
	<hr>
		<div style='text-align:right'>". button("{add_schedule_parameter}","AddScheduleEDonkey(0)")."</div>
	
		<div id='edonkey_bandwith_list' style='height:400px;overflow:auto;margin-top:9px'></div>
	
	
	<script>
		function AddScheduleEDonkey(id){
			YahooWin3('550','$page?schedule-parameter='+id,'$schedule');
		}
		
		function RefreshSchedList(){
			LoadAjax('edonkey_bandwith_list','$page?schedule-parameter-list=yes');
		}
		
	var X_SaveParametersSCH= function (obj) {
		var results=obj.responseText;
		if(results.lenght>0){alert(results);}
		YahooWin3Hide();
		RefreshSchedList();
		}		
		
	function DelScheduleEDonkey(ID){
		var XHR = new XHRConnection();
		XHR.appendData('DelScheduleEDonkey',ID);
		document.getElementById('edonkey_bandwith_list').innerHTML='<center><img src=img/wait_verybig.gif></center>'
		XHR.sendAndLoad('$page', 'GET',X_SaveParametersSCH);	
	}		
		
	RefreshSchedList();
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_bandwith_params(){
	$id=$_GET["schedule-parameter"];
	$page=CurrentPageName();
	
	
	if($id>0){
		$sql="SELECT * FROM mldonkey WHERE ID='$id'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["parameters"]));
	}
	
	$button="{add}";
	for($i=0;$i<60;$i++){
		if($i<10){$t="0$i";}else{$t=$i;}
		$minutes[$i]=$t;
	}
	
	for($i=0;$i<24;$i++){
		if($i<10){$t="0$i";}else{$t=$i;}
		$hours[$i]=$t;
	}	
		
	if($id>0){
		$button="{apply}";
	}
	if($array["max_hard_upload_rate"]==null){$array["max_hard_upload_rate"]=10;}
	if($array["max_hard_download_rate"]==null){$array["max_hard_download_rate"]=50;}
	
$html="

<div id='edonkeyschedid'>
<table style='width:240px'>
	<tr>
		<td class=legend style='font-size:13px' nworap>{at_this_time}:</td>
		<td>".Field_array_Hash($hours,"hours",$array["hours"],null,null,0,"font-size:13px;padding:3px")."</td>
		<td width=1% class=legend style='font-size:13px'>:</td>
		<td>".Field_array_Hash($minutes,"minutes",$array["minutes"],null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	</table>
	<hr>
	<div style='font-size:13px'>{change_parameters}:</div>
<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' nworap>{max_hard_upload_rate}:</td>
		<td style='font-size:13px'>". Field_text("max_hard_upload_rate",$array["max_hard_upload_rate"],"width:60px;font-size:13px;padding:3px")."&nbsp;Kbs</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px' nworap>{max_hard_download_rate}:</td>
		<td style='font-size:13px'>". Field_text("max_hard_download_rate",$array["max_hard_download_rate"],"width:60px;font-size:13px;padding:3px")."&nbsp;Kbs</td>
	</tr>	
	<TR>
		<td colspan=2 align='right'>
			<hr>
				". button("$button","SaveParametersSCH($id)").
		"</td>
	</tr>
	
	</table>
</div>
<script>

			


	function SaveParametersSCH(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID);
		XHR.appendData('hours',document.getElementById('hours').value);
		XHR.appendData('minutes',document.getElementById('minutes').value);
		XHR.appendData('max_hard_upload_rate',document.getElementById('max_hard_upload_rate').value);
		XHR.appendData('max_hard_download_rate',document.getElementById('max_hard_download_rate').value);
		document.getElementById('edonkeyschedid').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveParametersSCH);	
		}
		

	
	</script>
	";


	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_bandwith_list(){
	$q=new mysql();
	$sql="SELECT * FROM mldonkey ORDER BY schedule_time DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");

	$html="<table style='width:98%'>";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$params=unserialize(base64_decode($ligne["parameters"]));
		$max_hard_upload_rate_unit="kb/s";
		$max_hard_download_rate_unit="kb/s";
		
		if($params["max_hard_upload_rate"]>1000){
			$params["max_hard_upload_rate"]=$params["max_hard_upload_rate"]/1000;
			$max_hard_upload_rate_unit="MB/s";
		}
		
		if($params["max_hard_download_rate"]>1000){
			$params["max_hard_download_rate"]=$params["max_hard_download_rate"]/1000;
			$max_hard_download_rate_unit="MB/s";
		}		
		
		if($params["hours"]<10){$params["hours"]="0{$params["hours"]}";}
		if($params["minutes"]<10){$params["minutes"]="0{$params["minutes"]}";}
		
		$html=$html."
		<tr>
			<td width=1%><img src='img/time-30.png'></td>
			<td ". CellRollOver("AddScheduleEDonkey({$ligne["ID"]})","{edit}")."><strong style='font-size:13px'>
				{$params["hours"]}:{$params["minutes"]} &nbsp;-&raquo;&nbsp;
				{max_hard_upload_rate}:&nbsp;{$params["max_hard_upload_rate"]}&nbsp;$max_hard_upload_rate_unit,&nbsp;
				{max_hard_download_rate}:&nbsp;{$params["max_hard_download_rate"]}&nbsp;$max_hard_download_rate_unit,&nbsp;
			</td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","DelScheduleEDonkey({$ligne["ID"]})")."</td>
		</tr>
		<tr>
			<td colspan=3><hr></td>
		</tr>				
		";
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function popup_bandwith_save(){
	$hours=$_GET["hours"];
	$id=$_GET["ID"];
	$minutes=$_GET["minutes"];
	$time=mktime($hours,$minutes,0,1,1,2000);
	$params=base64_encode(serialize($_GET));
	$sql_add="INSERT INTO mldonkey (schedule_time,parameters) VALUES('$time','$params');";
	
	$sql_edit="UPDATE mldonkey SET schedule_time='$time', parameters='$params' WHERE ID='$id'";
	$sql=$sql_add;
	if($id>0){$sql=$sql_edit;}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo trim($q->mysql_error);
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?fcron-restart=yes");
		
}
function popup_bandwith_del(){
	$sql="DELETE FROM mldonkey WHERE ID='{$_GET["DelScheduleEDonkey"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo trim($q->mysql_error);
		return;
	}

	$sock=new sockets();
	$sock->getFrameWork("cmd.php?fcron-restart=yes");
	
}

	


?>