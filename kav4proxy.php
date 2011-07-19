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
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ExcludeMimeType"])){ExcludeMimeType();exit;}
	if(isset($_GET["MimeTypeList"])){ExcludeMimeType_list();exit;}
	if(isset($_GET["MimeTypeToAdd"])){ExcludeMimeType_add();exit;}
	if(isset($_GET["KavProxyDeleteLine"])){KavProxyDeleteLine();exit;}
	if(isset($_GET["icapserver_engine_options"])){icapserver_engine_options();exit;}
	if(isset($_GET["MaxChildren"])){icapserver_engine_options_save();exit;}

js();


function js(){
	
$page=CurrentPageName();
$tpl=new templates();
$icapserver_1=$tpl->_ENGINE_parse_body("{icapserver_1}","kav4proxy.index.php");
$title=$tpl->_ENGINE_parse_body("{web_proxy}&nbsp;&nbsp;&raquo;&raquo;&nbsp;{APP_KAV4PROXY}&nbsp;&nbsp;&raquo;&raquo;&nbsp;{parameters}");
$title2=$title."&nbsp;&nbsp;&raquo;&raquo;&nbsp;".$tpl->_ENGINE_parse_body("{exclude}:{ExcludeMimeType}");

$html="

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
	if(tempvalue.length>0){alert(tempvalue)};
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
	if(tempvalue.length>0){alert(tempvalue)};
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

	Kav4Proxyload();
	";
	
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
	<tr>
		<td class=legend>{add}: {ExcludeMimeType}</td>
		<td>". Field_text("MimeTypeToAdd",null,"font-size:13px;width:250px",null,null,null,false,"ExcludeMimeTypeAddEnter(event)")."</td>
	</tr>
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
	
	<table style='width:100%'>";

		$results=$kav->q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$html=$html."<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:13px'>{$ligne["data"]}</strong></td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","KavProxyDeleteExcludeMimeType({$ligne["ID"]})")."</td>
			</tr>
			";
		}
	
	$html=$html."</table></div>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function icapserver_engine_options(){
$kav4=new Kav4Proxy();
$html=" 
<div id='icapserver_engine_options'>
				<table style='width:100%'>
				<tr>
				<td align='right'><strong>{MaxChildren}:</strong></td>
				<td align='left'>" . Field_text('MaxChildren',$kav4->main_array["MaxChildren"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxChildren_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{IdleChildren}:</strong></td>
				<td align='left'>" . Field_text('IdleChildren',$kav4->main_array["IdleChildren"],'width:50px')."</td>
				<td align='left'>" . help_icon('{IdleChildren_text}',false,'milter.index.php') . "</td>
				</tr>
				<tr>
				<td align='right'><strong>{MaxReqsPerChild}:</strong></td>
				<td align='left'>" . Field_text('MaxReqsPerChild',$kav4->main_array["MaxReqsPerChild"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxReqsPerChild_text}',false,'milter.index.php') . "</td>
				</tr>	
				<tr>
				<td align='right'><strong>{MaxEnginesPerChild}:</strong></td>
				<td align='left'>" . Field_text('MaxEnginesPerChild',$kav4->main_array["MaxEnginesPerChild"],'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxEnginesPerChild_text}',false,'milter.index.php') . "</td>
				<tr>
				<tr>
				<td align='right'><strong>{PreviewSize}:</strong></td>
				<td align='left'>" . Field_text('PreviewSize',$kav4->main_array["PreviewSize"],'width:50px')."</td>
				<td align='left'>" . help_icon('{PreviewSize_text}',false,'milter.index.php') . "</td>
				<tr>
				<tr>
				<td align='right'><strong>{MaxReqLength}:</strong></td>
				<td align='left'>" . Field_text('MaxReqLength',$kav4->main_array["MaxReqLength"],'width:50px')."</td>
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