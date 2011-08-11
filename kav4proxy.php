<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.kav4proxy.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');

$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ExcludeMimeType"])){ExcludeMimeType();exit;}
	if(isset($_GET["MimeTypeList"])){ExcludeMimeType_list();exit;}
	if(isset($_GET["MimeTypeToAdd"])){ExcludeMimeType_add();exit;}
	if(isset($_GET["KavProxyDeleteLine"])){KavProxyDeleteLine();exit;}
	if(isset($_GET["icapserver_engine_options"])){icapserver_engine_options();exit;}
	if(isset($_GET["MaxChildren"])){icapserver_engine_options_save();exit;}
	if(isset($_GET["inline"])){js();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["kav4proxy-status"])){kav4proxy_status();exit;}

js();

function kav4proxy_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();	
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?squid-ini-status=yes')));
	$kav=DAEMON_STATUS_ROUND("KAV4PROXY",$ini,null,1);
	$Keep=DAEMON_STATUS_ROUND("KAV4PROXY_KEEPUP2DATE",$ini,null,1);
	
	$pattern_date=base64_decode($sock->getFrameWork("cmd.php?kav4proxy-pattern-date=yes"));
	$pattern_date_org=$pattern_date;
	if($pattern_date==null){$pattern_date="<strong style='font-size:11px;color:#C61010'>{av_pattern_database_obsolete_or_missing}</strong>";}else{
	$day=substr($pattern_date, 0,2);
	$month=substr($pattern_date, 2,2);
	$year=substr($pattern_date, 4,4);
	$re=explode(";",$pattern_date_org);
	$time=$re[1];
	$H=substr($time, 0,2);
	$M=substr($time, 2,2);
	$pattern_date="$year/$month/$day $H:$M:00";	
		
		
		
	}
	
	$html="$kav$Keep
	<div style='text-align:right'>". imgtootltip("refresh-24.png","{refresh}","Kav4ProxyStatus()")."</div>
	<br>
	<table class=form>
	<tbody>
		<tr>
			<td class=legend>{pattern_date}:</td>
			<td style='font-size:14px'>$pattern_date</td>
		</tr>
	</tbody>
	</table>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function status(){
		$tpl=new templates();
		$page=CurrentPageName();	
	$html="
	<table style='width:100%'>
	<tbody>
		<tr>
			<td width=1% valing='top'><div id='kav4proxy-status'></div></td>
			<td width=1% valign='top'>
			<center><img src=img/kaspersky-logo-250.png></center>
			<div class=explain>{kav4proxy_about}</div></td>
		</tr>
	</tbody>
	</table>
	
	<script>
		function Kav4ProxyStatus(){
			LoadAjax('kav4proxy-status','$page?kav4proxy-status=yes');
		
		}
	Kav4ProxyStatus();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}



function tabs(){
		$tpl=new templates();
		$page=CurrentPageName();
		$users=new usersMenus();
		$array["status"]='{status}';
		$array["tasks"]='{tasks}';
		$array["ExcludeMimeType"]='{exclude}:{ExcludeMimeType}';
		$array["icapserver_engine_options"]='{icapserver_1}';
		
	
		
		

	while (list ($num, $ligne) = each ($array) ){
		if($num=="tasks"){
			$tab[]="<li><a href=\"Kav4Proxy.Tasks.php\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			continue;
		}
		$tab[]="<li><a href=\"$page?$num=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			
		}
	
	
	

	$html="
		<div id='main_kav4proxy_config' style='background-color:white;margin-top:10px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_kav4proxy_config').tabs();
			

			});
		</script>
	
	";	
	
	echo $tpl->_ENGINE_parse_body($html);

}


function js(){
	$Kav4Proxyload="Kav4Proxyload()";
if(isset($_GET["inline"])){
	$Kav4Proxyload="Kav4ProxyloadInLIne()";
	$prefix="<div id='Kav4Proxy-div'>
	</div>
	<script>
	
	";
	$suffix="</script>";
}	
$page=CurrentPageName();
$tpl=new templates();
$icapserver_1=$tpl->_ENGINE_parse_body("{icapserver_1}","kav4proxy.index.php");
$title=$tpl->_ENGINE_parse_body("{web_proxy}&nbsp;&nbsp;&raquo;&raquo;&nbsp;{APP_KAV4PROXY}&nbsp;&nbsp;&raquo;&raquo;&nbsp;{parameters}");
$title2=$title."&nbsp;&nbsp;&raquo;&raquo;&nbsp;".$tpl->_ENGINE_parse_body("{exclude}:{ExcludeMimeType}");

$html="
	$prefix
	function Kav4Proxyload(){
		YahooWin('550','$page?popup=yes','$title');
	}	
	
	function ExcludeMimeTypePopUp(){
		YahooWin2('600','$page?ExcludeMimeType=yes','$title2');
	}

	function icapserver_engine_options(){
		YahooWin2('350','$page?icapserver_engine_options=yes','$icapserver_1');
	}

	function ExcludeMimeTypeAddEnter(e){
		if(!checkEnter(e)){return;}
		ExcludeMimeTypeAdd();
	}
var x_ExcludeMimeTypeAdd= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue)};
    ExcludeMimeTypeRefreshList();  
	}	

function ExcludeMimeTypeAdd(){
		var XHR = new XHRConnection();
		XHR.appendData('MimeTypeToAdd',document.getElementById('MimeTypeToAdd').value);
		document.getElementById('ExcludeMimeTypediv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ExcludeMimeTypeAdd);
}

var x_icapserver_engine_options_save= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue)};
    YahooWin2Hide();
	}	


function icapserver_engine_options_save(){
		var XHR = new XHRConnection();
		XHR.appendData('MaxChildren',document.getElementById('MaxChildren').value);
		XHR.appendData('IdleChildren',document.getElementById('IdleChildren').value);
		XHR.appendData('MaxReqsPerChild',document.getElementById('MaxReqsPerChild').value);
		XHR.appendData('PreviewSize',document.getElementById('PreviewSize').value);
		XHR.appendData('MaxReqLength',document.getElementById('MaxReqLength').value);
		XHR.appendData('MaxEnginesPerChild',document.getElementById('MaxEnginesPerChild').value);
		document.getElementById('icapserver_engine_options').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_icapserver_engine_options_save);
}

      

function KavProxyDeleteExcludeMimeType(id){
		var XHR = new XHRConnection();
		XHR.appendData('KavProxyDeleteLine',id);
		document.getElementById('ExcludeMimeTypediv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ExcludeMimeTypeAdd);
}

function ExcludeMimeTypeRefreshList(){
	LoadAjax('ExcludeMimeTypediv','$page?MimeTypeList=yes');
}


function Kav4ProxyloadInLIne(){
	LoadAjax('Kav4Proxy-div','$page?tabs=yes');
}

	$Kav4Proxyload;
	$suffix";
	
echo $html;	
	
}

function popup(){
	
	$html="
	<table style='width=100%'>
	<tr>
		<td valign='top'>". Paragraphe("good-files-64.png","{exclude}:{ExcludeMimeType}","{ExcludeMimeType_text}","javascript:ExcludeMimeTypePopUp()")."</td>
		<td valign='top'>". Paragraphe("kav4proxy-settings-64.png","{icapserver_1}","{kav4proxyprocess_explain}","javascript:icapserver_engine_options()")."</td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"kav4proxy.index.php");	
	
	
}
function ExcludeMimeType(){
	
	$html="
	<p style='font-size:13px'>{ExcludeMimeTypeKavExplain}</p>
	
	<table style='width:100%'>
	<tbody>
	<tr>
		<td class=legend>{add}: {ExcludeMimeType}</td>
		<td>". Field_text("MimeTypeToAdd",null,"font-size:13px;width:250px",null,null,null,false,"ExcludeMimeTypeAddEnter(event)")."</td>
	</tr>
	</tbody>
	</table>
	
	<div id='ExcludeMimeTypediv' style='height:350px;overflow:auto'></div>
	
	<script>
		ExcludeMimeTypeRefreshList();
	</script>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}


function ExcludeMimeType_add(){
	$kav=new Kav4Proxy();
	$kav->SET("icapserver.filter","ExcludeMimeType",$_GET["MimeTypeToAdd"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?kav4proxy-reconfigure=yes");
	
}
function KavProxyDeleteLine(){
	$kav=new Kav4Proxy();
	$sql="DELETE FROM `artica_backup`.`kav4Proxy` WHERE `kav4Proxy`.`ID` ={$_GET["KavProxyDeleteLine"]}";
	
	$kav->q->QUERY_SQL($sql,"artica_backup");
	if(!$kav->q->ok){
		echo $sql."\n".$kav->q->mysql_error;
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?kav4proxy-reconfigure=yes");
	//--reload-kav4proxy
}

if(isset($_GET["KavProxyDeleteLine"])){KavProxyDeleteLine();exit;}

function ExcludeMimeType_list(){
	$kav=new Kav4Proxy();
	$sql="SELECT ID,data FROM kav4Proxy WHERE `key`='icapserver.filter' AND `value`='ExcludeMimeType'";
	$html="
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% colspan=2>&nbsp;</th>
		
	</tr>
</thead>
<tbody class='tbody'>";

		$results=$kav->q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."<tr class=$classtr>
			<td><strong style='font-size:14px'>{$ligne["data"]}</strong></td>
			<td width=1%>". imgtootltip("delete-32.png","{delete}","KavProxyDeleteExcludeMimeType({$ligne["ID"]})")."</td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function icapserver_engine_options(){
$kav4=new Kav4Proxy();
$html=" 
<div id='icapserver_engine_options'>
				<table style='width:100%' class=form>
				<tr>
				<td align='right' style='font-size:14px' class=legend><strong>{MaxChildren}:</strong></td>
				<td align='left'>" . Field_text('MaxChildren',$kav4->main_array["MaxChildren"],'width:50px;font-size:14px')."</td>
				<td align='left'>" . help_icon('{MaxChildren_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right' style='font-size:14px' class=legend><strong>{IdleChildren}:</strong></td>
				<td align='left'>" . Field_text('IdleChildren',$kav4->main_array["IdleChildren"],'width:50px;font-size:14px')."</td>
				<td align='left'>" . help_icon('{IdleChildren_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right' style='font-size:14px' class=legend><strong>{MaxReqsPerChild}:</strong></td>
				<td align='left'>" . Field_text('MaxReqsPerChild',$kav4->main_array["MaxReqsPerChild"],'width:50px;font-size:14px')."</td>
				<td align='left'>" . help_icon('{MaxReqsPerChild_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right' style='font-size:14px' class=legend><strong>{MaxEnginesPerChild}:</strong></td>
				<td align='left'>" . Field_text('MaxEnginesPerChild',$kav4->main_array["MaxEnginesPerChild"],'width:50px;font-size:14px')."</td>
				<td align='left'>" . help_icon('{MaxEnginesPerChild_text}',false,'milter.index.php') . "</td>
				<tr>
				<tr>
				<td align='right' style='font-size:14px' class=legend><strong>{PreviewSize}:</strong></td>
				<td align='left'>" . Field_text('PreviewSize',$kav4->main_array["PreviewSize"],'width:50px;font-size:14px')."</td>
				<td align='left'>" . help_icon('{PreviewSize_text}',false,'milter.index.php') . "</td>
				<tr>
				<tr>
				<td align='right' style='font-size:14px' class=legend><strong>{MaxReqLength}:</strong></td>
				<td align='left'>" . Field_text('MaxReqLength',$kav4->main_array["MaxReqLength"],'width:50px;font-size:14px')."</td>
				<td align='left'>" . help_icon('{MaxReqLength_text}',false,'milter.index.php') . "</td>
				<tr>						

				
				
					<td colspan=3 align='right'>
						<hr>
						". button("{save}","icapserver_engine_options_save()")."</td>
				</tr>
				</table>
			</div>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}
function icapserver_engine_options_save(){
		$kav=new Kav4Proxy();
		$kav->MOD("icapserver.filter","MaxReqLength",$_GET["MaxReqLength"]);		
		$kav->MOD("icapserver.protocol","PreviewSize",$_GET["PreviewSize"]);
		$kav->MOD("icapserver.process","MaxChildren",$_GET["MaxChildren"]);
		$kav->MOD("icapserver.process","IdleChildren",$_GET["IdleChildren"]);
		$kav->MOD("icapserver.process","MaxReqsPerChild",$_GET["MaxReqsPerChild"]);
		$kav->MOD("icapserver.process","MaxEnginesPerChild",$_GET["MaxEnginesPerChild"]);		
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?kav4proxy-reconfigure=yes");		
}



?>