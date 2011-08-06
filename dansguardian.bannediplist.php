<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	include_once('ressources/class.computers.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SelectDansGuardianExceptionipList"])){echo FindComputerByIP();exit;}
	if(isset($_GET["AddDansGuardianExceptionipList"])){AddDansGuardianExceptionipList();exit;}
	if(isset($_GET["ExceptionipListRefresh"])){echo ComputersList();exit;}
	if(isset($_GET["DelDansGuardianExceptionipList"])){DelDansGuardianExceptionipList();exit;}
	js();
	
	
function js(){
	
$page=CurrentPageName();
$tpl=new templates();

$prefix=str_replace(".","_",$page);
$title=$tpl->_ENGINE_parse_body("{black_ip_group}");
$html="
var {$prefix}timerID  = null;
var {$prefix}timerID1  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var m_gpid;
var m_ou;

function {$prefix}load(){
	YahooWin2('750','$page?popup=yes','$title');
}

function {$prefix}StartPage(){
	if(!document.getElementById('squid_main_config')){
		setTimeout(\"{$prefix}StartPage()\",500);
	}
	LoadAjax('squid_main_config','$page?main=yes');
	setTimeout(\"{$prefix}demarre()\",500);
	setTimeout(\"{$prefix}ChargeLogs()\",500)
}

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=10-{$prefix}tant;
			if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"demarre()\",5000);
		      } else {
		{$prefix}tant = 0;
		              
		{$prefix}ChargeLogs();
		{$prefix}demarre();                                //la boucle demarre !
		   }
	}


function {$prefix}ChargeLogs(){
	var status='status';
	
	if(document.getElementById('statusid')){
		status=document.getElementById('statusid').value;
	}
	LoadAjax('services_status_squid','squid.index.php?status='+status+'&hostname={$_GET["hostname"]}&apply-settings=no');
	}
	

		
	function AddCache(folder){
		document.getElementById('cache_graph').innerHTML='';
		YahooWin(500,'$page?add-cache=yes&cache='+folder);
		
	}
	
var x_DeleteCache= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue)};
    cachelist();  
	}	
	
function cachelist(){
	LoadAjax('cache_list','$page?cache-list=yes');   
}
	
function DeleteCache(folder){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteCache',folder);
		document.getElementById('cache_list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_DeleteCache);
}

function ConnectionTime(){
	YahooWin(500,'$page?connection-time=yes');
}

function ConnectionTimeSelectOU(){
	var ou=document.getElementById('ou').value;
	LoadAjax('group_field','$page?connection-time-showgroup='+ou);
	}
	
function ConnectionTimeSelectGroup(){
	var ou=document.getElementById('ou').value;
	var gpid=document.getElementById('gpid').value;
	LoadAjax('ConnectionTimeRule','$page?connection-time-rule=yes&ou='+ou+'&gpid='+gpid);
	
}

function ConnecTimeRefreshlist(gpid,ou){
LoadAjax('rule_list','$page?time-rule-list=yes&gpid='+gpid+'&ou='+ou);   
}

var x_SelectDansGuardianExceptionipList= function (obj) {
	document.getElementById('popup_selected_computers').innerHTML=obj.responseText;
	}
	
var x_ExceptionipList_Refresh_list= function (obj) {
	document.getElementById('popup_saved_computers').innerHTML=obj.responseText;
	}	
	
function ExceptionipList_Refresh_list(){
	var XHR = new XHRConnection();
	document.getElementById('popup_saved_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('ExceptionipListRefresh','yes');
	XHR.sendAndLoad('$page', 'GET',x_ExceptionipList_Refresh_list);
}
	
var x_AddDansGuardianExceptionipList= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		ExceptionipList_Refresh_list();
	}	

function SelectDansGuardianExceptionipList(e){

	if(checkEnter(e)){
		AddDansGuardianExceptionipList(document.getElementById('IpWhite').value,'');
		return;
	}

	var XHR = new XHRConnection();
	document.getElementById('popup_selected_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('SelectDansGuardianExceptionipList',document.getElementById('IpWhite').value);
	XHR.sendAndLoad('$page', 'GET',x_SelectDansGuardianExceptionipList);
}

function AddDansGuardianExceptionipList(ip,uid){
var XHR = new XHRConnection();
	document.getElementById('popup_saved_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('AddDansGuardianExceptionipList',ip);
	XHR.appendData('uid',uid);
	XHR.sendAndLoad('$page', 'GET',x_AddDansGuardianExceptionipList);

}

function DelDansGuardianExceptionipList(ip){
var XHR = new XHRConnection();
	document.getElementById('popup_saved_computers').innerHTML='<center><img src=\"img/wait.gif\"></center>'
	XHR.appendData('DelDansGuardianExceptionipList',ip);
	XHR.sendAndLoad('$page', 'GET',x_AddDansGuardianExceptionipList);
}



{$prefix}load();";
echo $html;
	
}	

function popup(){
$tpl=new templates();	
$html="
<table style='width:100%'>
<tr>
	<td class=legend nowrap width=1%>{search}:</td>
	<td width=99%><center>". Field_text("IpWhite",null,"padding:3px;margin:3px;font-size:14px",null,null,null,false,"SelectDansGuardianExceptionipList(event)")."</center></td>
</tr>
</table>
<table style='width:100%'>
<td valign='top' style='border:1px solid #CCCCCC'><div id='popup_saved_computers' style='height:450px;overflow:auto'>". ComputersList()."</div></td>
<td valign='top' style='border:1px solid #CCCCCC'><div id='popup_selected_computers' style='height:450px;overflow:auto'>". FindComputerByIP()."</div>

<div style='text-align:right;font-size:12px;text-decoration:underline;margin-top:5px;font-weight:bolder' 
	OnClick=\"javascript:". MEMBER_JS('newcomputer$',1,1)."\" 
	OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"
	>{add_computer}</div>
</td>
</tr>
</table>


";	
	
echo $tpl->_ENGINE_parse_body($html);
	
}

function FindComputerByIP(){
	
	if($_GET["SelectDansGuardianExceptionipList"]=='*'){$_GET["SelectDansGuardianExceptionipList"]=null;}
	if($_GET["SelectDansGuardianExceptionipList"]==null){$tofind="*";}else{$tofind="{$_GET["SelectDansGuardianExceptionipList"]}*";}
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";
	
	writelogs($filter_search,__FUNCTION__,__FILE__,__LINE__);
	$ldap=new clladp();
	$attrs=array("uid","ComputerIP","ComputerMacAddress","ComputerMachineType");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs,10);
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=4>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	

for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=str_replace('$','',$hash[$i]["uid"][0]);
	$ip=$hash[$i][strtolower("ComputerIP")][0];
	$mac=$hash[$i][strtolower("ComputerMacAddress")][0];
	if(trim($ip)==null){continue;}
	
	$js="AddDansGuardianExceptionipList('$ip','$realuid');";
	
	//--enable-arp-acl
		if($mac<>null){$mac="<br><span style='font-size:10px'>$mac</span>";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$showomp=MEMBER_JS($realuid,1,1);
		$html=$html."
		<tr class=$classtr>
			<td width=1% >". imgtootltip("computer-32.png",'{edit}',"$showomp")."</td>
			<td nowrap><strong style='font-size:14px'>{$hash[$i]["uid"][0]}$mac</strong></td>
			<td ><strong style='font-size:14px'>$ip</strong></td>
			<td width=1% >". imgtootltip("plus-24.png","{add}",$js)."</td>
			
		</tr>
	";	
	
	
	
	}
$html=$html . "
</tbody>
</table>
</center>
";


$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}

function ComputersList(){
$q=new mysql();
$sock=new sockets();
	$sql="SELECT ID,pattern,uid FROM dansguardian_files WHERE filename='bannediplist' AND RuleID=1 ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		$mysql_error="::$q->mysql_error";
		if(preg_match("#doesn't exist#",$q->mysql_error)){
			$q->CheckTable_dansguardian();
			if(!$q->ok){$mysql_error=$mysql_error."::$q->mysql_error";}else{$mysql_error=null;}
		}
	}
	
	$style=CellRollOver();
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=4>{black_ip_group}$mysql_error</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$MAC=null;$uid=null;
		if($ligne["uid"]<>null){
			$cmp=new computers($ligne["uid"]);
			$MAC=$cmp->ComputerMacAddress;
			$uid="<br><i style='font-size:9px'>$cmp->ComputerRealName</i>";
		}
		
		if(!IsPhysicalAddress($MAC)){
			$MAC=$sock->getFrameWork("cmd.php?ip-to-mac={$ligne["pattern"]}");
		}
		
		if(!IsPhysicalAddress($MAC)){$MAC=null;}
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% ><img src='img/computer-32.png'></td>
			<td  nowrap><strong style='font-size:14px'>{$ligne["pattern"]}$uid</strong></td>
			<td  nowrap><strong style='font-size:14px'>&nbsp;$MAC</strong></td>
			<td ><strong>". imgtootltip("delete-24.png","{delete}","DelDansGuardianExceptionipList('{$ligne["ID"]}');")."</strong></td>
		</tr>
	";
	}
	$html=$html . "
		</tbody>
	</table>";	
	$html="<center><div style='height:450px;overflow:auto'>$html</div></center>";		
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);		
	
	
}

function AddDansGuardianExceptionipList(){
	$uid=$_GET["uid"];
	$dans=new dansguardian_rules(null,1);
	if(!$dans->Add_bannediplist($_GET["AddDansGuardianExceptionipList"],$_GET["AddDansGuardianExceptionipList"],$uid,1)){
		echo $dans->error;
	}
	
}

function DelDansGuardianExceptionipList(){
	$dans=new dansguardian_rules(null,1);
	if(!$dans->Del_bannediplist(1,$_GET["DelDansGuardianExceptionipList"])){
		echo $dans->error;
	}	
}

	
?>