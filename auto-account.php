<?php
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');


if(isset($_GET["script"])){switch_script();exit;}
if(isset($_GET["popup"])){switch_popup();exit;}
if(isset($_GET["ipfrom"])){SaveSettings();exit;}
if(isset($_GET["AutoCreateAccountDelete"])){popup_delete();exit;}

if(isset($_GET["iplist"])){echo popup_ips();exit;}

function switch_script(){
	
$users=new usersMenus();
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){}else{exit;}	
	switch ($_GET["script"]) {
		case "yes":popup_script();break;
		
		default:
			break;
	}
	
	
}
function switch_popup(){
$users=new usersMenus();
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){}else{exit;}	
	
	switch ($_GET["popup"]) {
		case "yes":popup_start();break;
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
	YahooWin(550,'$page?popup=yes','','');	
	}
	
var x_AutoCreateAccountDelete= function (obj) {
	load();
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
	
	function AutoCreateAccountDelete(num){
		var XHR = new XHRConnection();
		XHR.appendData('AutoCreateAccountDelete',num);
		XHR.sendAndLoad('$page', 'GET',x_AutoCreateAccountDelete);		
	}

	";
	echo $html;

}


function popup_start(){
	$page=CurrentPageName();
	$users=new AutoUsers();
	
$enable=Paragraphe_switch_img('{enable_autocreate}','{auto_account_explain}','AutoCreateAccountEnabled',$users->AutoCreateAccountEnabled,'{enable_disable}');
	
	$html="
	<form name='FFMCOMPRESSS'>
	<H1>{auto_account}</h1>
	<p class=caption>{auto_account_text}</p>
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'>$enable</td>
		<td valign='top' width=99%>
				<table class=table_form>
			<tr>
				<td colspan=2><H3>{add_network}</H3></td>
			</tr>				
				<tr>
					<td class=legend>{ipfrom}:</td>
					<td>" . Field_text('ipfrom','','width:100px')."</td>
				</tr>
				<tr>
					<td class=legend>{ipto}:</td>
					<td>" . Field_text('ipto','','width:100px')."</td>
				</tr>	
				<tr>
				<td colspan=2><div id='iplist'>" . popup_ips() . "</div></td>
			</tr>								
			<tr>
				<td colspan=2 align='right'><input type='button' 
				OnClick=\"javascript:ParseForm('FFMCOMPRESSS','$page',true,false,false,'iplist','$page?iplist=yes');\" value='{edit}&nbsp;&raquo;'></td>
			</tr>
				
			
		</table>
		</form>
		
		</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.index.php');		
	
}	

function popup_delete(){
	$users=new AutoUsers();
	unset($users->AutoCreateAccountIPArray[$_GET["AutoCreateAccountDelete"]]);
	$users->Save(1);
}

function popup_ips(){
	$users=new AutoUsers();
	$list=$users->AutoCreateAccountIPArray;
	if(!is_array($list)){return null;}
	
	$html="<table style='width:100%'>";
	while (list ($num, $val) = each ($list) ){
		$val=trim($val);
		$html=$html . "<tr " . CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:12px'><strong>$val</strong>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","AutoCreateAccountDelete($num)")."</td>
		</tr>
		
		";
		
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<br>".RoundedLightGrey($html));
	
	
	
}



function SaveSettings(){
	$AutoUsers=new AutoUsers();
	
	if($_GET["ipfrom"]<>null){
		$ip=new IP();
		
		if(preg_match("#([0-9\.]+)\.([0-9]+)$#",$_GET["ipfrom"],$re)){
			$secondip=$re[1].".255";
		}
		
		$cdir=$ip->ip2cidr($_GET["ipfrom"],$secondip);
		
		
		
		if($cdir<>null){
			echo "CDIR:$cdir\n{$_GET["ipfrom"]}-$secondip\n";
			$AutoUsers->AutoCreateAccountIPArray[]=$cdir;
		}else{
			echo "CDIR:{$_GET["ipfrom"]}-$secondip=false\n";
		}
		
		
	}
	
	
	$AutoUsers->AutoCreateAccountEnabled=$_GET["AutoCreateAccountEnabled"];
	$AutoUsers->Save();
	
}


class AutoUsers{
	var $AutoCreateAccountEnabled=0;
	var $AutoCreateAccountIPList=null;
	var $AutoCreateAccountIPArray;
	
	function AutoUsers(){
		$sock=new sockets();
		$this->AutoCreateAccountEnabled=$sock->GET_INFO("AutoCreateAccountEnabled");
		$this->AutoCreateAccountIPList=$sock->GET_INFO("AutoCreateAccountIPList");
		if(strlen($this->AutoCreateAccountEnabled)==0){
			$this->AutoCreateAccountEnabled=0;
			$this->Save(1);
		}
		
		$this->ParseArray();
		
		if(!is_array($this->AutoCreateAccountIPArray)){
			writelogs("Loading default subnets",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->AutoCreateAccountIPList=$this->AutoCreateAccountIPListDefault();
			$this->Save(1);	
			$this->ParseArray();
			}
		
		
		
			
		
	}
	
	function ParseArray(){
		$arr=explode(",",$this->AutoCreateAccountIPList);
		if(is_array($arr)){
			while (list ($num, $val) = each ($arr) ){
				if(trim($val)==null){continue;}
				$this->AutoCreateAccountIPArray[]=$val;
			}
		}
	}
	
	function Save($silent=0){
		$sock=new sockets();
		if(is_array($this->AutoCreateAccountIPArray)){
			$this->AutoCreateAccountIPList=implode(',',$this->AutoCreateAccountIPArray);
			$sock->SET_INFO("AutoCreateAccountIPList",$this->AutoCreateAccountIPList);
		}
		$sock->SET_INFO("AutoCreateAccountEnabled",$this->AutoCreateAccountEnabled);
		
		$tpl=new templates();
		if($silent==0){
			echo $tpl->_ENGINE_parse_body("{success}","postfix.index.php");
		}
		
	}
	
	
	function AutoCreateAccountIPListDefault(){
		if($_SESSION["uid"]==null){return null;}
		$page=CurrentPageName();
		writelogs("[$page] Create default list...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$users=new usersMenus();
		if($users->POSTFIX_INSTALLED){
			writelogs("Postfix is installed",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$main=new main_cf();
			writelogs("Class loaded...",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$list=$main->array_mynetworks;
			writelogs("array_mynetworks ".count($list) . " rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(is_array($list)){
				$this->AutoCreateAccountIPList=trim(implode(",",$list));
				writelogs("AutoCreateAccountIPList=$this->AutoCreateAccountIPList",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return $this->AutoCreateAccountIPList;
				
			}else{
				writelogs("array_mynetworks is not an array",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
			
			
		}
		
		
		
	}
	
	
	
	
}

?>