<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.nfs.inc");


	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SaveCluster"])){SaveCluster();exit;}
	
js();

function js(){
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{SAN_CLUSTER}');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		YahooWin2(650,'$page?popup=yes','$title');
	}
	
var x_SaveSanCLusterExport= function (obj) {
	var results=obj.responseText;
	alert(results);
	{$prefix}LoadPage();
	}	
	
	function SaveSanCLusterExport(){
	var XHR = new XHRConnection();
	XHR.appendData('SaveCluster',document.getElementById('main_storage').value);
	document.getElementById('clusterconf').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveSanCLusterExport);		
	}
		
	
	{$prefix}LoadPage();
";	

	echo $html;
}

function popup(){
	$nfs=new nfs();
	
	$html="<H1>{SAN_CLUSTER}</H1>
	
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/san-128.png'></td>
	<td valign='top'>
	<p class=caption>{SAN_CLUSTER_TEXT}</p>
	<div id='clusterconf'>
		<table style='width:100%'>
		<tr>
			<td class=legend nowrap>{main_storage_directory}:</td>
			<td>". Field_text('main_storage',$nfs->SanClusterBasePath)."</td>
			<td><input type='button' OnClick=\"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=main_storage&protocol=no');\" value='{browse}...'>
		</tr>
		<tr>
			<td colspan=3 align='right'>
				<hr>
					<input type='button' OnClick=\"SaveSanCLusterExport();\" value='{edit}&nbsp;&raquo;'>
			</td>
		</tr>
		</table>
		</div>
	</td>
	</tr>
	</table>

		
		
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveCluster(){
	$sock=new sockets();
	$sock->SET_INFO('SanClusterBasePath',$_GET["SaveCluster"]);
	$nfs=new nfs();
	$nfs->SaveToServer();
	
}




?>