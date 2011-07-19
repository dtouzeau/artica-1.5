<?php
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');


if(isset($_GET["script"])){switch_script();exit;}
if(isset($_GET["popup"])){switch_popup();exit;}
if(isset($_GET["autcompress_enabled"])){SaveSettings();exit;}
if(isset($_GET["AutoCompressAddExtension"])){AutoCompressAddExtension();exit;}
if(isset($_GET["AutoCompressDelete"])){AutoCompressDelete();exit;}
if(isset($_GET["extlist"])){echo autcompress_list();exit;}


function switch_script(){
	switch ($_GET["script"]) {
		case "winzip":popup_script();break;
		
		default:
			break;
	}
	
	
}
function switch_popup(){
	
	switch ($_GET["popup"]) {
		case "settings":popup_start();break;
		default:
			break;
	}
}


function popup_script(){
$page=CurrentPageName();
$html="
	var tmpnum='';
	
	load();
	
	function load(){
	YahooWin(550,'$page?popup=settings','','');	
	}
	
var x_AutoCompressAddExtension= function (obj) {
	var results=obj.responseText;	
	alert(results)
	LoadAjax('extlist','$page?extlist=yes','','');
}	
	
	function AutoCompressAddExtension(){
		var text=document.getElementById('addextension_help').value
		var tmpnum=prompt(text);
		if(tmpnum){
			var XHR = new XHRConnection();
			XHR.appendData('AutoCompressAddExtension',tmpnum);
			XHR.sendAndLoad('$page', 'GET',x_AutoCompressAddExtension);
			}
	}
	
	function AutoCompressDelete(num){
		var XHR = new XHRConnection();
		XHR.appendData('AutoCompressDelete',num);
		XHR.sendAndLoad('$page', 'GET',x_AutoCompressAddExtension);		
	}

	";
	echo $html;

}


function popup_start(){
	$page=CurrentPageName();
	$autocompress=new autocompress();
	
	$html="
	<input type='hidden' id='addextension_help' name='addextension_help' value='{addextension_help}'>
	<H1>{auto-compress}</h1>
	<p class=caption>{Auto-compress_explain}</p>
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/90-winzip.png'></td>
		<td valign='top'>
			<form name='FFMCOMPRESSS'>
				<table class=table_form>
				<tr>
					<td class=legend>{enable_autocompress}</td>
					<td>" . Field_numeric_checkbox_img('autcompress_enabled',$autocompress->autcompress_enabled,'{enable_disable}')."</td>
				</tr>
			<tr>
				<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFMCOMPRESSS','$page',true);\" value='{edit}&nbsp;&raquo;'></td>
			</tr>
				
			
		</table>
		</form>
			<div id='extlist'>
				".autcompress_list()."		
			</div>
		</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.index.php');		
	
}	


function AutoCompressAddExtension(){
	$autocompress=new autocompress();
	$tbl=explode(",",$_GET["AutoCompressAddExtension"]);
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$autocompress->extensions[]=$ligne;
	}
	
	$autocompress->Save();
	
}

function AutoCompressDelete(){
	$autocompress=new autocompress();
	unset($autocompress->extensions[$_GET["AutoCompressDelete"]]);
	$autocompress->Save();
	
}


function autcompress_list(){
	$autocompress=new autocompress();
$html="
<div style='width:100%;text-align:right'>
	<input type='button' OnClick=\"javascript:AutoCompressAddExtension();\" value='{add}&nbsp;&raquo;'>
</div>
<div style='width:100%;height:250px;overflow:auto'>";
if(is_array($autocompress->extensions)){
	while (list ($num, $ligne) = each ($autocompress->extensions) ){
		$ligne=trim($ligne);
	if(file_exists('img/file_ico/'.$ligne.'.gif')){$img="img/file_ico/$ligne.gif";}else{$img="img/file_ico/unknown.gif";}
	$table=$table."
	<div style='float:left;margin:2px'>
	<table class=table_form>
	<tr " . CellRollOver().">
	<td width=1%' align='center'><img src='$img'></td>
	<td width=1%'>" . imgtootltip('ed_delete.gif',"{delete}","AutoCompressDelete('$num');")."</td>
	
	</tr>
	
	<tr>
	<td align='center' colspan=2><strong style='font-size:11px'>$ligne</td>
	
	
	</tr>
	</table>
	</div>
	";
}
}

$table=$table."</div>";
$html=$html . $table."</div>";	

$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html,'postfix.index.php');		


}

function SaveSettings(){
	
	$autocompress=new autocompress();
	$autocompress->autcompress_enabled=$_GET["autcompress_enabled"];
	$autocompress->Save();
	
}


class autocompress{
	var $autcompress_enabled=0;
	var $extensions=array();
	
	function autocompress(){
		$sock=new sockets();
		$this->autcompress_enabled=$sock->GET_INFO("AutoCompressEnabled");
		$extensions=explode("\n",$sock->GET_INFO("AutoCompressExtensions"));
		if(is_array($extensions)){
			while (list ($num, $ligne) = each ($extensions) ){
				if(trim($ligne)==null){continue;}
				$this->extensions[]=$ligne;
			}
		}
		
	}
	
	function Save(){
		$sock=new sockets();
		$sock->SET_INFO('AutoCompressEnabled',$this->autcompress_enabled);
		if(is_array($this->extensions)){
			
			$sock->SaveConfigFile(implode("\n",$this->extensions),"AutoCompressExtensions");
		}
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{auto-compress}: {success}","postfix.index.php");
		
	}
	
	
	
	
}

?>