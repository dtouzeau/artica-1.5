<?php
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');


if(isset($_GET["script"])){switch_script();exit;}
if(isset($_GET["popup"])){switch_popup();exit;}
if(isset($_GET["QuarantineAutoCleanEnabled"])){SaveSettings();exit;}

function switch_script(){
	switch ($_GET["script"]) {
		case "quarantine":popup_script();break;
		
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
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body("{quarantine}");
$html="
	var tmpnum='';
	
	load();
	
	function load(){
	YahooWin(550,'$page?popup=settings','$title','');	
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
	
var x_SaveQuarantineConfig= function (obj) {
	var results=obj.responseText;	
	if(results.length>0){alert(results);}
	YahooWinHide();
}		
	
	function SaveQuarantineConfig(){
		var XHR = new XHRConnection();
		XHR.appendData('QuarantineAutoCleanEnabled',document.getElementById('QuarantineAutoCleanEnabled').value);
		XHR.appendData('QuarantineMaxDayToLive',document.getElementById('QuarantineMaxDayToLive').value);
		
		XHR.appendData('StorageAutoCleanEnabled',document.getElementById('StorageAutoCleanEnabled').value);
		XHR.appendData('StorageMaxDayToLive',document.getElementById('StorageMaxDayToLive').value);		
		
		document.getElementById('quarform').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_SaveQuarantineConfig);
	
	}

	";
	echo $html;

}


function popup_start(){
	$page=CurrentPageName();
	$quar=new GlobalQuarantine();
	
	$q=new mysql();
	$sql="SELECT COUNT(MessageID) as tcount FROM quarantine";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$message_number=$ligne["tcount"];
	if($message_number==null){$message_number=0;}
	
	$sql="SELECT COUNT(MessageID) as tcount FROM storage";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$message_storage_number=$ligne["tcount"];
	if($message_storage_number==null){$message_storage_number=0;}	
	
	
	$html="
	<input type='hidden' id='addextension_help' name='addextension_help' value='{addextension_help}'>
	
	<p class=caption>{quarantine_text}</p>
	<div style='width:100%;font-size:12px;font-weight:bold;text-align:right;border-bottom:1px dotted #CCCCCC;margin-bottom:3px'>
	$message_number {quarantine} {emails_recieved}&nbsp;|&nbsp;$message_storage_number {backup} {emails_recieved}</div>
	<div id='quarform'>
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/90-quarantaine.png'></td>
		<td valign='top'>
			<form name='FFMCOMPRESSS'>
				<table>
				<tr>
					<td class=legend>{QuarantineAutoCleanEnabled}:</td>
					<td>" . Field_numeric_checkbox_img('QuarantineAutoCleanEnabled',$quar->QuarantineAutoCleanEnabled,'{enable_disable}')."</td>
				</tr>
				<tr>
					<td class=legend>{QuarantineMaxDayToLive}:</td>
					<td>" . Field_text('QuarantineMaxDayToLive',$quar->QuarantineMaxDayToLive,'width:40px')."</td>
				</tr>
				<tr>
					<td class=legend>{StorageAutoCleanEnabled}:</td>
					<td>" . Field_numeric_checkbox_img('StorageAutoCleanEnabled',$quar->StorageAutoCleanEnabled,'{enable_disable}')."</td>
				</tr>
				<tr>
					<td class=legend>{StorageMaxDayToLive}:</td>
					<td>" . Field_text('StorageMaxDayToLive',$quar->StorageMaxDayToLive,'width:40px')."</td>
				</tr>	
				
			<tr>
				<td colspan=2 align='right'>
				<hr>
				". button("{edit}","SaveQuarantineConfig()")."
				</td>
			</tr>
				
			
		</table>
		</div>
		</form>
		
		</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.index.php');		
	
}	



function SaveSettings(){
	
	$GlobalQuarantine=new GlobalQuarantine();
	$GlobalQuarantine->QuarantineAutoCleanEnabled=$_GET["QuarantineAutoCleanEnabled"];
	$GlobalQuarantine->QuarantineMaxDayToLive=$_GET["QuarantineMaxDayToLive"];
	
	$GlobalQuarantine->StorageMaxDayToLive=$_GET["StorageMaxDayToLive"];
	$GlobalQuarantine->StorageAutoCleanEnabled=$_GET["StorageAutoCleanEnabled"];
	
	$GlobalQuarantine->Save();
	
}


class GlobalQuarantine{
	var $QuarantineAutoCleanEnabled=1;
	var $QuarantineMaxDayToLive=15;
	var $StorageMaxDayToLive=60;
	var $StorageAutoCleanEnabled=1;
	var $extensions=array();
	
	function GlobalQuarantine(){
		$sock=new sockets();
		$this->QuarantineAutoCleanEnabled=$sock->GET_INFO("QuarantineAutoCleanEnabled");
		if($this->QuarantineAutoCleanEnabled==null){
			$this->QuarantineAutoCleanEnabled=1;
			$this->Save(1);
		}
		$this->QuarantineMaxDayToLive=$sock->GET_INFO("QuarantineMaxDayToLive");
		if($this->QuarantineMaxDayToLive==null){
			$this->QuarantineMaxDayToLive=15;
			$this->Save(1);
		}	

		$this->StorageMaxDayToLive=$sock->GET_INFO("StorageMaxDayToLive");
		if($this->StorageMaxDayToLive==null){
			$this->StorageMaxDayToLive=60;
			$this->Save(1);
		}

		$this->StorageAutoCleanEnabled=$sock->GET_INFO("StorageAutoCleanEnabled");
		if($this->StorageAutoCleanEnabled==null){
			$this->StorageAutoCleanEnabled=1;
			$this->Save(1);
		}		
		
		
	}
	
	function Save($silent=0){
		$sock=new sockets();
		$sock->SET_INFO("QuarantineAutoCleanEnabled",$this->QuarantineAutoCleanEnabled);
		$sock->SET_INFO("QuarantineMaxDayToLive",$this->QuarantineMaxDayToLive);
		$sock->SET_INFO("StorageMaxDayToLive",$this->StorageMaxDayToLive);
		$sock->SET_INFO("StorageAutoCleanEnabled",$this->StorageAutoCleanEnabled);
		$tpl=new templates();
		if($silent==0){
			echo $tpl->_ENGINE_parse_body("{quarantine}: {success}","postfix.index.php");
		}
		
	}
	
	
	
	
}

?>