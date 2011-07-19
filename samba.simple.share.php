<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.pdns.inc');
	
	

	
	if(!CheckSambaRights()){die();}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SimpleShareSearch"])){SimpleShareSearch();exit;}
	if(isset($_GET["SimpleShareAddCompForm"])){SimpleShareAddCompForm();exit;}
	if(isset($_GET["computername_add"])){SimpleShareAddComputerLDAP();exit;}
	if(isset($_GET["SharedList"])){SharedList();exit;}
	if(isset($_GET["add-uid"])){SimpleShareAddCompToPath();exit;}
	if(isset($_GET["del-uid"])){SimpleShareDelCompToPath();exit;}
	js();
	
function js(){
	$tpl=new templates();
	$simple_share=$tpl->_ENGINE_parse_body("{simple_share}");
	$page=CurrentPageName();
	$html="
	
	YahooWin2('500','$page?popup=yes&path={$_GET["path"]}','$simple_share');
	
	function x_SimpleSearchAddComputerPath(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){
			alert(tempvalue);
			SimpleSharePathList();
			return;
		}	
		
		if(document.getElementById('main_config_folder_properties')){
			RefreshTab('main_config_folder_properties');
		}
		
		SimpleSharePathList();
	}	
	
	function SimpleSearchAddComputerPath(uid){
		var XHR = new XHRConnection();
		XHR.appendData('path','{$_GET["path"]}');
		XHR.appendData('add-uid',uid);
		document.getElementById('SharedPathComputerList').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SimpleSearchAddComputerPath);		
	
	}
	
	function SimpleSearchDeleteComputerPath(value){
		var XHR = new XHRConnection();
		XHR.appendData('path','{$_GET["path"]}');
		XHR.appendData('del-uid',value);
		document.getElementById('SharedPathComputerList').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SimpleSearchAddComputerPath);		
	}
	
	function SimpleSharePathList(){
		LoadAjax('SharedPathComputerList','$page?SharedList=yes&path={$_GET["path"]}');
	}
	
	
	";
	
	echo $html;
	
	
}

function popup(){
	$page=CurrentPageName();
	$html="
	<div class=explain>{simple_share_explain}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{search}:</td>
		<td>". Field_text("SimpleShareSearch",null,'font-size:13px;padding:3px',
		null,null,null,false,"SimpleSearchKeyPress(event)")."</td>
	</tr>
	</table>
	
	<div id='SimpleShareSearchResults' style='width:100%;height:200px;overflow:auto;border:1px solid #CCCCCC;padding:3px'></div>
	<br>
	<div id='SimpleShareAddCompForm' style='width:100%;'></div>
	<br>
	<div id='SharedPathComputerList' style='width:100%;height:200px;overflow:auto;border:1px solid #CCCCCC;padding:3px'></div>	
	
	<script>
	
	function SimpleSearchKeyPress(e){
		if(checkEnter(e)){SimpleSearch();}
	}
	
	function SimpleSearchAddComputerKeyPress(e){
			if(checkEnter(e)){SimpleSearchAddComputer();}
	}
	
	
	function SimpleSearch(){
		LoadAjax('SimpleShareSearchResults','$page?SimpleShareSearch='+document.getElementById('SimpleShareSearch').value);
	}
	
	function SimpleShareAddCompForm(){
		LoadAjax('SimpleShareAddCompForm','$page?SimpleShareAddCompForm=yes');
	}
	
	function x_SimpleSearchAddComputer(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){
			alert(tempvalue);
			SimpleShareAddCompForm();
			return;
		}	
		SimpleShareAddCompForm();
		SimpleSearch();
	}		
	
	function SimpleSearchAddComputer(){
		var XHR = new XHRConnection();
		XHR.appendData('computername_add',document.getElementById('computername_add').value);
		document.getElementById('SimpleShareSearch').value=document.getElementById('computername_add').value;
		XHR.appendData('computerip_add',document.getElementById('computerip_add').value);
		document.getElementById('SimpleShareAddCompForm').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SimpleSearchAddComputer);				
	
	}	
	
	
	SimpleSearch();
	SimpleShareAddCompForm();
	SimpleSharePathList();
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SimpleShareAddComputerLDAP(){
	
	$tpl=new templates();
	$computer=$_GET["computername_add"];
	$ip=$_GET["computerip_add"];
	if($computer==null){
		echo $tpl->_ENGINE_parse_body("{computer_name} = NULL");
		exit;
	}
	if($ip==null){
		echo $tpl->_ENGINE_parse_body("{ip_address} = NULL");
		exit;
	}

	$uid="$computer$";
	$comp=new computers($uid);
	$comp->ComputerIP=$ip;
	$comp->ComputerRealName=$computer;
	if(!$comp->Add()){
		echo @implode("\n",$GLOBALS["INJECT_COMPUTER_TOLDAP"]);
		return;
	}
	
	
	
	
}


function SimpleShareAddCompForm(){
	
$html="<table class=tableView>
	<thead class=thead>
	<tr>
		<th>{add_computer} {computer_name}</th>
		<th>{ip_address}</th>
		<th>{add}</th>
	</tr>
	</thead>
	<tr>
		<td >".Field_text("computername_add",null,"font-size:13px;padding:3px",
		null,null,null,false,"SimpleSearchAddComputerKeyPress(event)")."</td>
		<td >".Field_text("computerip_add",null,"font-size:13px;padding:3px",
		null,null,null,false,"SimpleSearchAddComputerKeyPress(event)")."</td>
		<td width=1%>". imgtootltip("plus-24.png","{add}","SimpleSearchAddComputer()")."</td>
	</tr>
	</table>";	
$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);	
}

	
function SimpleShareSearch(){
	
	if($_GET["SimpleShareSearch"]=='*'){$_GET["SimpleShareSearch"]=null;}
	if($_GET["SimpleShareSearch"]==null){$tofind="*";}else{$tofind="*{$_GET["SimpleShareSearch"]}*";}
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";
	$ldap=new clladp();
	$attrs=array("uid","ComputerIP","ComputerOS","ComputerMachineType","ComputerMacAddress");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs,20);


$html="<table class=tableView>
	<thead class=thead>
	<tr>
		<th colspan=2>{computers} ($tofind)</th>
		<th colspan=2>{ip_address}</th>
	</tr>
	</thead>


";

for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=str_replace('$','',$hash[$i]["uid"][0]);
	$js_show=MEMBER_JS($realuid,1);
	if($_GET["callback"]<>null){$js_selection="{$_GET["callback"]}('$realuid');";}
	

	
	$ip=$hash[$i][strtolower("ComputerIP")][0];
	$os=$hash[$i][strtolower("ComputerOS")][0];
	$type=$hash[$i][strtolower("ComputerMachineType")][0];
	$mac=$hash[$i][strtolower("ComputerMacAddress")][0];
	$name=$hash[$i]["uid"][0];
	if(strlen($name)>25){$name=substr($name,0,23)."...";}
	
	
	if($os=="Unknown"){if($type<>"Unknown"){$os=$type;}}
	if(!preg_match("#^[0-9]+\.[0-9]+#",$ip)){$ip=$ip="0.0.0.0";}
	
	
	if(strlen($os)>20){$os=texttooltip(substr($os,0,17).'...',$os,null,null,1);}
	if(strlen($ip)>20){$ip=texttooltip(substr($ip,0,17).'...',$ip,null,null,1);}
	
	$img="<img src='img/base.gif'>";
	if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
	$roolover=CellRollOver($js_selection,"{select}");
	
		
	$html=$html . 
	"<tr class=$cl>
	<td width=1% $roolover>$img</td>
	<td nowrap width=99%><strong style='font-size:12px'>$name</strong></td>
	<td width=2% nowrap><strong style='font-size:12px'>$ip</strong></td>
	<td width=1%>". imgtootltip("plus-24.png","{add}","SimpleSearchAddComputerPath('$realuid')")."</td>
	</tr>
	";
	}
$html=$html . "</table>";
$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);
}

function SharedList(){
	
	$samba=new samba();
	$keypath=$samba->GetShareName(base64_decode($_GET["path"]));
	
	$hosts=explode(" ",$samba->main_array[$keypath]["hosts allow"]);
	if(is_array($hosts)){
	while (list ($index, $host) = each ($hosts) ){
		if($host==null){continue;}
		$hote[$host]=$host;
		
	}}
	
$html="<table class=tableView>
	<thead class=thead>
	<tr>
		<th colspan=3>{computers}</th>
	</tr>
	</thead>


";	
if(is_array($hote)){
	while (list ($index, $host) = each ($hote) ){
		$img="<img src='img/base.gif'>";
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
			$html=$html . 
			"<tr class=$cl>
			<td width=1% $roolover>$img</td>
			<td nowrap width=99%><strong style='font-size:12px'>$host</strong></td>
			<td width=1%>". imgtootltip("delete-24.png","{add}","SimpleSearchDeleteComputerPath('$host')")."</td>
			</tr>
			";	
	
	}}
	
	
$html=$html . "</table>";
$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);	
	
}

function SimpleShareAddCompToPath(){
	$uid=$_GET["add-uid"];
	

	
	$samba=new samba();
	$keypath=$samba->GetShareName(base64_decode($_GET["path"]));
	$hosts=explode(" ",$samba->main_array[$keypath]["hosts allow"]);
	if(is_array($hosts)){
	while (list ($index, $host) = each ($hosts) ){
		if($host==null){continue;}
		$hote[$host]=$host;
		
	}}	
	
	$comp=new computers($uid);
	$pdns=new pdns();
	$array=$pdns->IpToHosts($comp->ComputerIP);	
	if(is_array($array)){
		while (list ($index, $val) = each ($array) ){
			$hote[$val]=$val;
		}
	}else{
		$hote[$comp->ComputerIP]=$comp->ComputerIP;
	}
	
	$hote[$comp->ComputerRealName]=$comp->ComputerRealName;
	
	if(is_array($hote)){
	while (list ($index, $host) = each ($hote) ){
			if(strpos($host,'$')>0){continue;}
			$final[]=$host;
	}}
	
	if(count($final)>0){
		$samba->main_array[$keypath]["hosts allow"]=@implode(" ",$final);
		$samba->main_array[$keypath]["hosts deny"]="0.0.0.0/0";
		$samba->main_array[$keypath]["public"]="yes";
		$samba->main_array[$keypath]["force user"]="root";
		$samba->main_array[$keypath]["guest ok"]="yes";
		$samba->main_array[$keypath]["read only"]="no";
		$samba->main_array[$keypath]["browseable"]="yes";
		$samba->main_array["global"]["guest account"]="nobody";
		$samba->main_array["global"]["map to guest"]="Bad Password";				
		unset($samba->main_array[$keypath]["write list"]);
		unset($samba->main_array[$keypath]["valid users"]);
		unset($samba->main_array[$keypath]["read list"]);		
	}else{
		unset($samba->main_array[$keypath]["force user"]);
		unset($samba->main_array[$keypath]["public"]);
		unset($samba->main_array[$keypath]["guest ok"]);
		unset($samba->main_array[$keypath]["read only"]);
		unset($samba->main_array[$keypath]["hosts deny"]);
		unset($samba->main_array[$keypath]["hosts allow"]);
	}
	
	
	
	$samba->SaveToLdap();
}

function SimpleShareDelCompToPath(){
	$uid=$_GET["del-uid"];
	$samba=new samba();
	$keypath=$samba->GetShareName(base64_decode($_GET["path"]));
	$hosts=explode(" ",$samba->main_array[$keypath]["hosts allow"]);
	if(is_array($hosts)){
	while (list ($index, $host) = each ($hosts) ){
		if($host==null){continue;}
		$hote[$host]=$host;
		
	}}	

	unset($hote[$uid]);
	
	
	
	if(is_array($hote)){
	while (list ($index, $host) = each ($hote) ){
			$final[]=$host;
	}}	
	
	
	
if(count($final)>0){
		$samba->main_array[$keypath]["hosts allow"]=@implode(" ",$final);
		$samba->main_array[$keypath]["hosts deny"]="0.0.0.0/0";
		$samba->main_array[$keypath]["public"]="yes";
		$samba->main_array[$keypath]["force user"]="root";
		$samba->main_array[$keypath]["guest ok"]="yes";
		$samba->main_array[$keypath]["read only"]="no";
		$samba->main_array[$keypath]["browseable"]="yes";
		$samba->main_array["global"]["guest account"]="nobody";
		$samba->main_array["global"]["map to guest"]="Bad Password";				
		unset($samba->main_array[$keypath]["write list"]);
		unset($samba->main_array[$keypath]["valid users"]);
		unset($samba->main_array[$keypath]["read list"]);		
	}else{
		unset($samba->main_array[$keypath]["force user"]);
		unset($samba->main_array[$keypath]["public"]);
		unset($samba->main_array[$keypath]["guest ok"]);
		unset($samba->main_array[$keypath]["read only"]);
		unset($samba->main_array[$keypath]["hosts deny"]);
		unset($samba->main_array[$keypath]["hosts allow"]);		
	}	
	
	$samba->SaveToLdap();
	
}
	

?>