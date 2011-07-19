<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.samba.inc');
	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){die("<H1>EXIT</H1>");}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["main-params"])){params();exit;}
	if(isset($_GET["hosts-table"])){wins_dat();exit;}
	if(isset($_GET["name-resolve-order-list"])){name_resolve_order();exit;}
	if(isset($_POST["SaveWins"])){SaveWins();exit;}
	if(isset($_POST["SaveWins"])){SaveWins();exit;}
	if(isset($_GET["browse-wins-list"])){WinsList();exit;}
	js_inline();

function js_inline(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?tabs=yes');
	";	
}


function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["main-params"]="{parameters}";
	$array["hosts-table"]="{hosts_table}";
		
	while (list ($num, $ligne) = each ($array) ){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_wins_samba style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_wins_samba').tabs();
			});
		</script>";		
	
	
}
function params(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$SambaActAsWins=$sock->GET_INFO("SambaActAsWins");
	$SambaWinsServer=$sock->GET_INFO("SambaWinsServer");
	$SambaUsDNS=$sock->GET_INFO("SambaUsDNS");

	if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$SambaWinsServer)){$SambaWinsServer="0.0.0.0";}
	
	$html="<div class=explain>{samba_wins_explain}</div>
	
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{SambaActAsWins}:</td>
		<td>". Field_checkbox("SambaActAsWins", 1,$SambaActAsWins,"SambaActAsWinsCheck()")."</td>
		<td>". help_icon("{SambaActAsWins_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{use_another_WINS_server}:</td>
		<td>". field_ipv4("SambaWinsServer",$SambaWinsServer)."</td>
		<td>". help_icon("{use_another_WINS_server_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{SambaDnsProxy}:</td>
		<td>". Field_checkbox("SambaUsDNS", 1,$SambaUsDNS)."</td>
		<td>". help_icon("{SambaDnsProxy_explain}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveWinsSettings()")."</td>
	</tr>
	</table>
	<div id='name_resolve_order_id'></div>

<script>
	function name_resolve_order_refresh(){
			LoadAjax('name_resolve_order_id','$page?name-resolve-order-list=yes');
	}	
	
	function NameResolveOrderSet(index,position){
		LoadAjax('name_resolve_order_id','$page?name-resolve-order-list=yes&index='+index+'&pos='+position);
	}	
	
	
	function SambaActAsWinsCheck(){
		if(document.getElementById('SambaActAsWins').checked){
			Ipv4FieldDisable('SambaWinsServer');
		}else{
			Ipv4FieldEnable('SambaWinsServer');
		}
	
	}
	
	
	var x_SaveWinsSettings=function (obj) {
			tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			RefreshTab('main_config_wins_samba');
	    }	
	
	function SaveWinsSettings(){
		var XHR = new XHRConnection();
		XHR.appendData('SaveWins','yes');
		if(document.getElementById('SambaActAsWins').checked){XHR.appendData('SambaActAsWins','1');}else{XHR.appendData('SambaActAsWins','0');}
		if(document.getElementById('SambaUsDNS').checked){XHR.appendData('SambaUsDNS','1');}else{XHR.appendData('SambaUsDNS','0');}
		XHR.appendData('SambaWinsServer',document.getElementById('SambaWinsServer').value);
		AnimateDiv('name_resolve_order_id');
		XHR.sendAndLoad('$page', 'POST',x_SaveWinsSettings);		
		}	
	
	
	name_resolve_order_refresh();
	SambaActAsWinsCheck();
	
	
	
</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveWins(){
	$sock=new sockets();
	$sock->SET_INFO("SambaActAsWins", $_POST["SambaActAsWins"]);
	$sock->SET_INFO("SambaUsDNS", $_POST["SambaUsDNS"]);
	$sock->SET_INFO("SambaWinsServer", $_POST["SambaWinsServer"]);
	$sock->getFrameWork('cmd.php?samba-save-config=yes');
}


function name_resolve_order(){
	$tpl=new templates();
	$smb=new samba();
	$name_resolve=explode(" ",$smb->main_array["global"]["name resolve order"]);
	
	if(isset($_GET["index"])){
		$name_resolve_new=array_move_element_flat($name_resolve,$_GET["index"],$_GET["pos"]);
		$smb->main_array["global"]["name resolve order"]=implode(" ",$name_resolve_new);
		$smb->SaveToLdap();
		
		echo "<script>name_resolve_order_refresh();</script>";return;
	}
	
	
	$html="
	<hr>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th colspan=3>{name_resolve_order}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	while (list ($num, $val) = each ($name_resolve) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/Database32.png'></td>
			<td><strong style='font-size:14px'>{smb_{$val}}</strong></td>
			<td width=1%>". imgtootltip("arrow-up-32.png","{up}","NameResolveOrderSet('$num','up')")."</td>
			<td width=1%>". imgtootltip("arrow-down-32.png","{down}","NameResolveOrderSet('$num','down')")."</td>
			</tr>
			
			";
		
	}
	$html=$html."</tbody><table>";	
	echo $tpl->_ENGINE_parse_body($html);
}

function wins_dat(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	
	<center>
	<table style='width:70%' class=form>
	<tr>
		<td class=legend>{computers}:</td>
		<td>". Field_text("browse-wins-search",null,"font-size:14px;padding:3px",null,null,null,false,"WinsSambaSearchCheck(event)")."</td>
		<td>". button("{search}","BrowseWinsSearch()")."</td>
	</tr>
	</table>
	</center>
	<div id='browse-wins-list' style='width:100%;height:450px;overflow:auto;text-align:center'></div>
	<center>
	
</center>
<script>
		function WinsSambaSearchCheck(e){
			if(checkEnter(e)){BrowseWinsSearch();}
		}
		
		function BrowseWinsSearch(){
			var se=escape(document.getElementById('browse-wins-search').value);
			LoadAjax('browse-wins-list','$page?browse-wins-list=yes&search='+se);
		}
		

			
	BrowseWinsSearch();
</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function WinsList(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$datas=unserialize(base64_decode($sock->getFrameWork("samba.php?wins-list=yes&search=".urlencode($_GET["search"]))));
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=2>{hostname}</th>
		<th>{ipaddr}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		if(!preg_match('#"(.+?)\#.+?[0-9]+\s+(.+?)$#', $ligne,$re)){continue;}
		if(isset($a[$re[1]])){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$a[$re[1]]=true;
		$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/30-computer.png'></td>
		<td><strong style='font-size:14px'>{$re[1]}</td>
		<td><strong style='font-size:14px'>{$re[2]}</td>
		</tr>
		";
	}
	
	$html=$html."</table>";
echo $tpl->_ENGINE_parse_body($html);	
	
}
	



