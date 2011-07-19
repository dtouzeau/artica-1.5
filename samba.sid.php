<?php
	include_once(dirname(__FILE__).'/ressources/class.templates.inc');
	include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
	include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.samba.inc');
	include_once(dirname(__FILE__).'/ressources/class.computers.inc');
	include_once(dirname(__FILE__).'/ressources/class.groups.inc');
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	
	
	

if(isset($_GET["popup-index"])){popup();exit;}
if(isset($_GET["SID_SUFFIX"])){save();exit;}
if(isset($_GET["popup-logs"])){popup_logs();exit;}
if(isset($_GET["Status"])){echo Status($_GET["Status"]);exit;}
if(isset($_GET["SMBRESTART"])){SMBRESTART();exit;}
if(isset($_GET["SMBCHANGECOMPUTERS"])){SMBCHANGECOMPUTERS();exit;}
if(isset($_GET["SMBGROUPS"])){SMBGROUPS();exit;}
if(isset($_GET["SMBCHANGEUSERS"])){SMBCHANGEUSERS();exit;}
if(posix_getuid()<>0){js();}
function js(){

$page=CurrentPageName();
$prefix=str_replace($page,'.','',$prefix);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{CHANGE_SID_TEXT}','samba.index.php');
	
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="

function {$prefix}Loadpage(){
	YahooWin2('550','$page?popup-index=yes','$title');
	
	}
	
	
var X_SMBCHANGEUSERS= function (obj) {
	var results=escapeVal(obj.responseText,'<br>');
	document.getElementById('popup-logs').innerHTML=document.getElementById('popup-logs').innerHTML+'<hr><div>'+results+'</div>';
	{$prefix}ChangeStatus(100);
	document.getElementById('smbwait').innerHTML='';
	setTimeout('{$prefix}Loadpage()',1000);
	
	}	
	
var X_SMBCHANGECOMPUTERS= function (obj) {
	var results=escapeVal(obj.responseText,'<br>');
	document.getElementById('popup-logs').innerHTML=document.getElementById('popup-logs').innerHTML+'<hr><div>'+results+'</div>';
	{$prefix}ChangeStatus(60);
	var XHR = new XHRConnection();
	XHR.appendData('SMBCHANGEUSERS','yes');
	XHR.sendAndLoad('$page', 'GET',X_SMBCHANGEUSERS);	
			
}

var X_SMBGROUPS= function (obj) {
	var results=escapeVal(obj.responseText,'<br>');
	document.getElementById('popup-logs').innerHTML=document.getElementById('popup-logs').innerHTML+'<hr><div>'+results+'</div>';
	{$prefix}ChangeStatus(50);
	var XHR = new XHRConnection();
	XHR.appendData('SMBCHANGECOMPUTERS','yes');
	XHR.sendAndLoad('$page', 'GET',X_SMBCHANGECOMPUTERS);		
}
		
	
var X_SMBRESTART= function (obj) {
	var results=escapeVal(obj.responseText,'<br>');
	document.getElementById('popup-logs').innerHTML=document.getElementById('popup-logs').innerHTML+'<hr><div>'+results+'</div>';
	{$prefix}ChangeStatus(30);
	var XHR = new XHRConnection();
	XHR.appendData('SMBGROUPS','yes');
	XHR.sendAndLoad('$page', 'GET',X_SMBGROUPS);		
}		
	
var X_SaveSambaSID= function (obj) {
	var results=obj.responseText;
	document.getElementById('popup-logs').innerHTML=document.getElementById('popup-logs').innerHTML+'<hr><div>'+results+'</div>';
	{$prefix}ChangeStatus(20);
	var XHR = new XHRConnection();
	XHR.appendData('SMBRESTART','yes');
	XHR.sendAndLoad('$page', 'GET',X_SMBRESTART);		
	}	
	
	
function SaveSambaSID(){
		var SID_SUFFIX=document.getElementById('SID_SUFFIX').value;
		var XHR = new XHRConnection();
		XHR.appendData('SID_SUFFIX',SID_SUFFIX);
		document.getElementById('formsambasid').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		YahooWin3('550','$page?popup-logs=yes','$title');
		if(document.getElementById('popup-logs')){
			setTimeout('{$prefix}null()',900);
		}
		{$prefix}ChangeStatus(10);
		XHR.sendAndLoad('$page', 'GET',X_SaveSambaSID);	

}

	var x_{$prefix}ChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_samba').innerHTML=tempvalue;
	}

	function {$prefix}ChangeStatus(number){
		var XHR = new XHRConnection();
		XHR.appendData('Status',number);
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChangeStatus);	
	}
	
	function escapeVal(content,replaceWith){
		content = escape(content) 
	
			for(i=0; i<content.length; i++){
				if(content.indexOf(\"%0D%0A\") > -1){
					content=content.replace(\"%0D%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0A\") > -1){
					content=content.replace(\"%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0D\") > -1){
					content=content.replace(\"%0D\",replaceWith)
				}
	
			}	
		return unescape(content);
	}	

	function {$prefix}null(){}
		

	

	
function {$prefix}DisplayDivs(){
		LoadAjax('main_config_postfix','$page?main={$_GET["main"]}&hostname=$hostname')
		{$prefix}demarre();
		{$prefix}ChargeLogs();
		{$prefix}StatusBar();
	}	
	
function RefreshIndexPostfixAjax(){
	{$prefix}StatusBar();
}
	
 {$prefix}Loadpage();
";
	
	echo $html;
}

function popup(){
	$ldap=new clladp();
	$users=new usersMenus();if(!$users->AsSambaAdministrator){die();}	
	$SID=$ldap->LOCAL_SID();
	if(preg_match("#S-1-5-21-(.+)#",$SID,$re)){
		$SID_PREFIX=$re[1];
	}else{
		$SID_PREFIX=$SID;
	}
	
	
	$form="<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' nowrap width=1%>" . Field_text('SID_PREFIX','S-1-5-21','font-size:16px;width:100px;text-align:right',null,null,null,false,null,true)."</td>
		<td valign='top'><span style='font-size:16px'>-</span>&nbsp;" . Field_text('SID_SUFFIX',$SID_PREFIX,'font-size:16px')."</td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:SaveSambaSID();\" value='&nbsp;&nbsp;{edit}&nbsp;&raquo;&nbsp;&nbsp;'></td></tr>
	</table>
	";
	
	$html="
	<div class=explain>{SID_EXPLAIN}</div>
	<br>
	<div id='formsambasid'>
	$form
	</div>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'samba.index.php');	
	
}

function save(){
	$sid="S-1-5-21-".$_GET["SID_SUFFIX"];
	$tpl=new templates();
	
	
	$smb=new samba();
	$smb->ChangeSID($sid);
	if(posix_getuid()<>0){echo $tpl->_ENGINE_parse_body("{CHANGE_SID_TEXT}:$sid {success}<br>\n","samba.index.php");}
	
	
	
	
}
function Status($pourc){
$color="#5DD13D";	
$html="
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
";	


return $html;
}
	

function popup_logs(){
	$pourc=0;
	$table=Status(0);
	$color="#5DD13D";	
$html= "

<table style='width:100%'>
<tr>
			<td>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_samba'>
						$table
					</div>
				</div>
			</td>
</tr>
<tr>
<td>
		<table style='width:100%'>
		<tr>
		<td valign='top' width=1%><div id='smbwait'><img src='img/wait-clock.gif'></div></td>
		<td valign='top'>
		<div id='popup-logs' style='width:100%;background-color:white;border:1px solid #CCCCCC;height:300px;overflow:auto;padding:4px'>{SID_CHANGE_PROCESS}</div></td>
		</td>
		</tr>
		</table>
	</td>
</tr>
</table>
";	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"samba.index.php");	
}
function SMBRESTART(){
	$smb=new samba();
	$smb->SaveToLdap();
	$tpl=new templates();
	if(posix_getuid()<>0){echo $tpl->_ENGINE_parse_body("<hr>{CHANGE_SID_GROUPS}","samba.index.php");}
}

function SMBCHANGECOMPUTERS(){
	$ldap=new clladp();
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=*)(ComputerIP=*)(uid=*))(gecos=computer))";
	$attrs=array("uid","ComputerIP","ComputerOS");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$realuid=$hash[$i]["uid"][0];
		if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$realuid)){continue;}
		$cp=new computers($realuid);
		$cp->UpdateComputerSID();
		if(posix_getuid()<>0){echo "<hr>";}

	}
	
}

function SMBGROUPS(){
	$gp=new groups(null);
	$gp->ChangeSMBGroupsSID();
	$tpl=new templates();
	if(posix_getuid()<>0){echo $tpl->_ENGINE_parse_body("{success}<hr>{CHANGE_SID_COMPUTERS}","samba.index.php");}	
	
}

function SMBCHANGEUSERS(){
	$ldap=new clladp();
	$filter_search="(&(objectClass=sambaSamAccount)(cn=*))";
	$attrs=array("uid");
	$dn="$ldap->suffix";
	if(posix_getuid()==0){echo "$filter_search in $dn\n";}
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs);
	if($hash["count"]==null){$hash["count"]=0;}
	if(posix_getuid()==0){echo "Users={$hash["count"]}\n";}
	for($i=0;$i<$hash["count"];$i++){
		$realuid=$hash[$i]["uid"][0];
		if(strpos("  $realuid",'$')>0){continue;}
		$u=new user($realuid);
		if(posix_getuid()==0){echo "Update $realuid $u->dn\n";}
		$tpl=new templates();
		if(posix_getuid()<>0){echo "{edit}:$u->DisplayName\n";}
		$u->Samba_edit_user();
		

	}	
	
}


?>