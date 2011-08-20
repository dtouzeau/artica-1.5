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
	
	if(isset($_GET["kav4proxy-groups-list"])){groups_list();exit;}
	if(isset($_GET["group-popup"])){group_popup();exit;}
	if(isset($_GET["group-main-settings"])){group_settings();exit;}
	if(isset($_POST["group-main-settings-save"])){group_settings_save();exit;}
	if(isset($_POST["group-delete"])){group_delete();exit;}
	
	if(isset($_GET["group-ExcludeMimeType"])){ExcludeMimeType();exit;}
	if(isset($_POST["group-ExcludeMimeType-ADD"])){ExcludeMimeType_add();exit;}
	if(isset($_POST["group-ExcludeMimeType-DEL"])){ExcludeMimeType_del();exit;}
	if(isset($_GET["group-ExcludeMimeType-LIST"])){ExcludeMimeType_list();exit;}
	
	if(isset($_GET["group-ExcludeURL"])){ExcludeURL();exit;}
	if(isset($_GET["group-ExcludeURL-LIST"])){ExcludeURL_list();exit;}
	if(isset($_POST["group-ExcludeURL-ADD"])){ExcludeURL_add();exit;}
	if(isset($_POST["group-ExcludeURL-DEL"])){ExcludeURL_del();exit;}
	
	if(isset($_GET["group-ClientIP"])){ClientIP();exit;}
	if(isset($_GET["group-ClientIP-LIST"])){ClientIP_list();exit;}
	if(isset($_POST["group-ClientIP-ADD"])){ClientIP_add();exit;}
	if(isset($_POST["group-ClientIP-DEL"])){ClientIP_del();exit;}	
	
	if(isset($_GET["group-ClientURI"])){ClientURI();exit;}
	if(isset($_GET["group-ClientURI-LIST"])){ClientURI_list();exit;}
	if(isset($_POST["group-ClientURI-ADD"])){ClientURI_add();exit;}
	if(isset($_POST["group-ClientURI-DEL"])){ClientURI_del();exit;}		
	
	
	
popup();	
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$group=$tpl->_ENGINE_parse_body("{group}");
	$html="
	<div class=explain style='margin-bottom:10px'>{Kav4ProxyGroupsHowto}</div>
	<center>
	<table style='width:80%' class=form>
	<tr>
		<td class=legend>{groups}:</td>
		<td>". Field_text("kav4proxy-groups-search",null,"font-size:14px;padding:3px",null,null,null,false,"Kav4ProxyGroupsSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","Kav4ProxyGroupsSearch()")."</td>
	</tr>
	</table>
	</center>
	<div id='kav4proxy-groups-list' style='width:100%;height:350px;overflow:auto'></div>
	
	<script>
		function Kav4ProxyGroupsSearchCheck(e){
			if(checkEnter(e)){Kav4ProxyGroupsSearch();}
		}
		
		function Kav4ProxyGroupsSearch(){
			var se=escape(document.getElementById('kav4proxy-groups-search').value);
			LoadAjax('kav4proxy-groups-list','$page?kav4proxy-groups-list=yes&search='+se);
		}
		
		function GroupEdit(gpname){
			YahooWin3('550','$page?group-popup=yes&gpname='+gpname,'$group:'+gpname);
		}
	
	Kav4ProxyGroupsSearch()
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function groups_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$search=$_GET["search"];	
	$add=imgtootltip("plus-24.png","{add} {group}","GroupEdit('')");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:60%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{groups}&nbsp;|&nbsp;$search_sql</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$sql="SELECT * FROM Kav4Proxy_groups ORDER BY priority DESC LIMIT 0,50";
	
	if($search<>null){
		$search=str_replace("*", "%", $search);
		$sql="SELECT * FROM Kav4Proxy_groups WHERE groupname LIKE '$search' ORDER BY priority DESC LIMIT 0,50";
	}
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		$delete=imgtootltip("delete-32.png","{delete}:{$ligne["groupname"]}","Kav4ProxyGroupDelete('{$ligne["groupname"]}')");
		$select="<a href=\"javascript:blur();\" OnClick=\"javascript:GroupEdit('{$ligne["groupname"]}');\" style='font-size:16px;text-decoration:underline'>";
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:14px;color:$color' colspan=2>$select{$ligne["groupname"]}</a></td>
		<td width=1%>$delete</td>
		</tr>
		
		";
		
		
	}
	
	$html=$html."</tbody>
	</table>
	</center>
	
	<script>
	var x_Kav4ProxyGroupDelete= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
	    	Kav4ProxyGroupsSearch();
		}	

		function Kav4ProxyGroupDelete(gpname){
			var XHR = new XHRConnection();
			XHR.appendData('group-delete','yes');
			XHR.appendData('gpname',gpname);
			AnimateDiv('kav4proxy-groups-list');
			XHR.sendAndLoad('$page', 'POST',x_Kav4ProxyGroupDelete);
		}
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function group_delete(){
	$q=new mysql();
	$gpname=$_POST["gpname"];
	$q->QUERY_SQL("DELETE FROM Kav4Proxy_groups WHERE groupname='$gpname'","artica_backup");
	if(!$q->ok){echo $q->mysq_error;return;}
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
	
}

function group_settings(){
	$gpname=$_GET["gpname"];
	$buttonname="{apply}";
	$page=CurrentPageName();
	$tpl=new templates();	
	$hide="RefreshTab('main_kav4proxyGroup_config')";
	$scriptgpname="XHR.appendData('gpname','$gpname');";
	if($gpname==null){
		$field_gpname=Field_text("gpname",null,"font-size:16px;padding:3px;width:150px",null,null,null,null,"SaveKav4ProxyGroupMainSettingsCheck(event)");
		$scriptgpname="XHR.appendData('gpname',document.getElementById('gpname').value);";
		$hide="YahooWin3Hide();";
	}
	
	$sql="SELECT * FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$URLs=unserialize(base64_decode($ligne["URL"]));
	$hash=unserialize(base64_decode($ligne["EngineAction"]));
	if(!is_numeric($ligne["priority"])){$ligne["priority"]=10;}
	
	
	if(!is_numeric($hash["Cure"])){$hash["Cure"]=1;}
	if(!is_numeric($hash["ScanArchives"])){$hash["ScanArchives"]=1;} 
	if(!is_numeric($hash["ScanPacked"])){$hash["ScanPacked"]=1;} 
	if(!is_numeric($hash["ScanMailBases"])){$hash["ScanMailBases"]=1;} 
	if(!is_numeric($hash["ScanMailPlain"])){$hash["ScanMailPlain"]=1;}
	if(!is_numeric($hash["MaxScanTime"])){$hash["MaxScanTime"]=300;}
	if(!is_numeric($hash["MaxReqLength"])){$hash["MaxReqLength"]=512000;}
	
	
	
	if($URLs[0]==null){$URLs[0]=".*";}
	$html="
	<div id='main-group-settings-div'>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>{groupname}:</td>
		<td>$field_gpname</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{priority}:</td>
		<td>". Field_text("priority",$ligne["priority"],"font-size:14px;width:30px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{MaxReqLength}:</td>
		<td>". Field_text("MaxReqLength",$hash["MaxReqLength"],"font-size:14px;width:90px")."</td>
		<td>". help_icon("{MaxReqLength_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{MaxScanTime}:</strong></td>
		<td align='left' style='font-size:13px'>" . Field_text('MaxScanTime',$hash["MaxScanTime"],'width:70px;font-size:14px')."&nbsp;{seconds}</td>
		<td align='left'>" . help_icon('{MaxScanTime_text}') . "</td>
	</tr>	
	
	<tr>
		<td valign='top' class=legend nowrap>{baseserror}</strong></td>
		<td valign='top' align='left'>" . Field_deny_skip_checkbox_img('BasesErrorAction',$hash["BasesErrorAction"])."</td>
		<td>". help_icon("{BasesErrorAction}")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend nowrap>{corrupted}</strong></td>
		<td valign='top' align='left'>" . Field_deny_skip_checkbox_img('CorruptedAction',$hash["CorruptedAction"])."</td>
		<td>". help_icon("{CorruptedAction}")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend nowrap>{cured}</strong></td>
		<td valign='top' align='left'>" . Field_deny_skip_checkbox_img('CuredAction',$hash["CuredAction"])."</td>
		<td>". help_icon("{CuredAction}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>{error}</strong></td>
		<td valign='top' align='left'>" . Field_deny_skip_checkbox_img('ErrorAction',$hash["ErrorAction"])."</td>
		<td>". help_icon("{ErrorAction}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>{infected}</strong></td>
		<td valign='top' align='left'>" . Field_deny_skip_checkbox_img('InfectedAction',$hash["InfectedAction"])."</td>
		<td>". help_icon("{InfectedAction}")."</td>
	</tr>

	<tr>
		<td valign='top' class=legend>{Cure}:</strong></td>
		<td align='left'>" . Field_checkbox("Cure",1,$hash["Cure"])."</td>
		<td align='left'>" . help_icon('{Cure_text}',false,'milter.index.php') . "</td>
	</tr>					
	<tr>
		<td valign='top' class=legend>{ScanArchives}:</strong></td>
		<td align='left'>" . Field_checkbox("ScanArchives",1,$hash["ScanArchives"])."</td>
		<td align='left'>" . help_icon('{ScanArchives_text}',false,'milter.index.php') . "</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{ScanPacked}:</strong></td>
		<td align='left'>" . Field_checkbox("ScanPacked",1,$hash["ScanPacked"])."</td>
		<td align='left'>" . help_icon('{ScanPacked_text}',false,'milter.index.php') . "</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{ScanMailBases}:</strong></td>
		<td align='left'>" . Field_checkbox("ScanMailBases",1,$hash["ScanMailBases"])."</td>
		<td align='left'>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{ScanMailPlain}:</strong></td>
		<td align='left'>" . Field_checkbox("ScanMailPlain",1,$hash["ScanMailPlain"])."</td>
		<td align='left'>&nbsp;</td>
				</tr>												
	<tr>
		<td valign='top' class=legend>{UseAVBasesSet}:</strong></td>
		<td align='left'>" . Field_array_Hash(array("standard"=>"standard","extended"=>"extended","redundant"=>"redundant"),'UseAVBasesSet',$hash["UseAVBasesSet"])."</td>
		<td align='left'>" . help_icon('{UseAVBasesSet_text}',false,'milter.index.php') . "</td>
				</tr>	
	<tr>		 
	<tr>
		<td colspan=3 align='right'><hr>". button($buttonname,"SaveKav4ProxyGroupMainSettings()")."</td>
	</tr>
	</tbody>
	</table>
	</div>
	<script>
	var x_SaveKav4ProxyGroupMainSettings= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
	    	$hide;
	    	Kav4ProxyGroupsSearch();
		}	

	function SaveKav4ProxyGroupMainSettingsCheck(e){
		if(checkEnter(e)){SaveKav4ProxyGroupMainSettings();}
	}
	
	
		function SaveKav4ProxyGroupMainSettings(){
			var XHR = new XHRConnection();
			$scriptgpname
			XHR.appendData('group-main-settings-save','yes');
			XHR.appendData('BasesErrorAction',document.getElementById('BasesErrorAction').value);
			XHR.appendData('CorruptedAction',document.getElementById('CorruptedAction').value);
			XHR.appendData('CuredAction',document.getElementById('CuredAction').value);
			XHR.appendData('ErrorAction',document.getElementById('ErrorAction').value);
			XHR.appendData('InfectedAction',document.getElementById('InfectedAction').value);
			
			XHR.appendData('MaxScanTime',document.getElementById('MaxScanTime').value);
			XHR.appendData('UseAVBasesSet',document.getElementById('UseAVBasesSet').value);
			XHR.appendData('MaxReqLength',document.getElementById('MaxReqLength').value);
			
			
			
			if(document.getElementById('Cure').checked){XHR.appendData('Cure',1);}else{XHR.appendData('Cure',0);}
			if(document.getElementById('ScanArchives').checked){XHR.appendData('ScanArchives',1);}else{XHR.appendData('ScanArchives',0);}
			if(document.getElementById('ScanPacked').checked){XHR.appendData('ScanPacked',1);}else{XHR.appendData('ScanPacked',0);}
			if(document.getElementById('ScanMailBases').checked){XHR.appendData('ScanMailBases',1);}else{XHR.appendData('ScanMailBases',0);}
			if(document.getElementById('ScanMailPlain').checked){XHR.appendData('ScanMailPlain',1);}else{XHR.appendData('ScanMailPlain',0);}
			
			XHR.appendData('priority',document.getElementById('priority').value);
			AnimateDiv('main-group-settings-div');
			XHR.sendAndLoad('$page', 'POST',x_SaveKav4ProxyGroupMainSettings);
		}
	
	
	</script>";
			
		echo $tpl->_ENGINE_parse_body($html);
}

function group_settings_save(){
	$gpname=$_POST["gpname"];
	
	
	$sql="SELECT groupname FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));

	$URLs[0]=$_POST["URL"];
	$URLs_sql=addslashes(base64_encode(serialize($URLs)));
	$hash["BasesErrorAction"]=$_POST["BasesErrorAction"];
	$hash["CorruptedAction"]=$_POST["CorruptedAction"];
	$hash["CuredAction"]=$_POST["CuredAction"];
	$hash["CuredAction"]=$_POST["CuredAction"];
	$hash["InfectedAction"]=$_POST["InfectedAction"];
	
	
	$hash["MaxScanTime"]=$_POST["MaxScanTime"];
	$hash["Cure"]=$_POST["Cure"];
	$hash["ScanArchives"]=$_POST["ScanArchives"];
	$hash["ScanPacked"]=$_POST["ScanPacked"];
	$hash["ScanMailBases"]=$_POST["ScanMailBases"];
	$hash["ScanMailPlain"]=$_POST["ScanMailPlain"];
	$hash["UseAVBasesSet"]=$_POST["UseAVBasesSet"];
	$hash["MaxReqLength"]=$_POST["MaxReqLength"];
	
	
	
	$EngineAction=addslashes(base64_encode(serialize($hash)));
	$sql="UPDATE Kav4Proxy_groups SET URL='$URLs_sql',
	EngineAction='$EngineAction',
	priority='{$_POST["priority"]}' WHERE groupname='$gpname'";
	
	if($ligne["groupname"]==null){
		$ldap=new clladp();
		$gpname=$ldap->StripSpecialsChars($gpname);
		$sql="INSERT INTO Kav4Proxy_groups (groupname,URL,EngineAction,priority) VALUES ('$gpname','$URLs_sql','$EngineAction','{$_POST["priority"]}')";
		
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
	
}


function Field_deny_skip_checkbox_img($name,$value,$tooltip=null){
	$value=strtolower($value);
	if($tooltip==null){$tooltip='{click_deny_skip}';}
	$tooltip=ParseTooltip($tooltip);
	if($value==null){$value="no";}
	if($tooltip<>null){$tooltip="onMouseOver=\"javascript:AffBulle('$tooltip');lightup(this, 100);\" OnMouseOut=\"javascript:HideBulle();lightup(this, 50);\" style=\"filter:alpha(opacity=50);-moz-opacity:0.5;border:0px;\"";}
	if($value=='skip'){$img='img/status_ok.gif';}else{$img='img/status_critical.gif';}
	$html="
	<input type='hidden' name='$name' id='$name' value='$value'><a href=\"javascript:SwitchDenySkip('$name');\"><img src='$img' id='img_$name' $tooltip></a>";
	return $html;
	
}


function group_popup(){
		$gpname=$_GET["gpname"];
		$tpl=new templates();
		$page=CurrentPageName();
		$users=new usersMenus();
		$array["group-main-settings"]='{main_settings}';
		$array["group-ClientIP"]='{ClientIP}';
		$array["group-ClientURI"]='{ClientURI}';
		$array["group-ExcludeMimeType"]='{exclude}:{ExcludeMimeType}';
		$array["group-ExcludeURL"]='{exclude}:{ExcludeURL}';
		if($gpname==null){
			unset($array["group-ExcludeMimeType"]);
			unset($array["group-ExcludeURL"]);
			unset($array["group-ClientIP"]);
			unset($array["group-ClientURI"]);
		}
		
	while (list ($num, $ligne) = each ($array) ){
		
		$tab[]="<li><a href=\"$page?$num=yes&gpname=$gpname\"><span>$ligne</span></a></li>\n";
			
		}
	
	$html="
		<div id='main_kav4proxyGroup_config' style='background-color:white;margin-top:10px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_kav4proxyGroup_config').tabs();
			

			});
		</script>
	
	";	
	
	echo $tpl->_ENGINE_parse_body($html);
}

function ClientURI_list(){
$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_GET["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	$ExcludeMimeTypeArray=$EngineOption["ClientURI"];
	$ExcludeMimeType=$tpl->javascript_parse_text("{ClientURI}");
	
	
	
	$html="
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{add_clienturl}","ClientURIGroupAdd()")."</th>
		<th width=99%>{ClientURI}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";

	while (list ($num, $ligne) = each ($ExcludeMimeTypeArray) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr class=$classtr>
				<td colspan=2><strong style='font-size:14px' colspan=2>$ligne</strong></td>
				<td width=1%>". imgtootltip("delete-32.png","{delete}","ClientURIGroupDel($num)")."</td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	
	<script>
		function ClientURIGroupAdd(){
			var type=prompt('$ExcludeMimeType (.*,^domain\.org$,.*domain.*...');
			if(!type){return;}
			var XHR = new XHRConnection();
			XHR.appendData('group-ClientURI-ADD',type);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ClientURIGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ClientIPGroupAdd);			
		}
		
		function ClientURIGroupDel(num){
			var XHR = new XHRConnection();
			XHR.appendData('group-ClientURI-DEL',num);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ClientURIGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ClientURIGroupAdd);			
		}		
	
	var x_ClientIPGroupAdd= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
	    	ClientURIRefreshGroupList();
		}	

</script>		
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

    
function ClientIP_list(){
$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_GET["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	$ExcludeMimeTypeArray=$EngineOption["ClientIP"];
	$ExcludeMimeType=$tpl->javascript_parse_text("{ClientIP}");
	$kav4proxyClientIP=$tpl->javascript_parse_text("{kav4proxyClientIP}");
	
	
	$html="
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{add_clientip}","ClientIPGroupAdd()")."</th>
		<th width=99%>{ClientIP}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";

	while (list ($num, $ligne) = each ($ExcludeMimeTypeArray) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr class=$classtr>
				<td colspan=2><strong style='font-size:14px' colspan=2>$ligne</strong></td>
				<td width=1%>". imgtootltip("delete-32.png","{delete}","ClientIPGroupDel($num)")."</td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	
	<script>
		function ClientIPGroupAdd(){
			var type=prompt('$ExcludeMimeType $kav4proxyClientIP');
			if(!type){return;}
			var XHR = new XHRConnection();
			XHR.appendData('group-ClientIP-ADD',type);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ClientIPGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ClientIPGroupAdd);			
		}
		
		function ClientIPGroupDel(num){
			var XHR = new XHRConnection();
			XHR.appendData('group-ClientIP-DEL',num);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ClientIPGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ClientIPGroupAdd);			
		}		
	
	var x_ClientIPGroupAdd= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
	    	ClientIPRefreshGroupList();
		}	

</script>		
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}
function ExcludeURL_list(){
$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_GET["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	$ExcludeMimeTypeArray=$EngineOption["ExcludeURL"];
	$ExcludeMimeType=$tpl->javascript_parse_text("{ExcludeURL}");
	
	
	
	$html="
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{add_clienturl}","ExcludeURLGroupAdd()")."</th>
		<th width=99%>{ExcludeURL}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";

	while (list ($num, $ligne) = each ($ExcludeMimeTypeArray) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr class=$classtr>
				<td colspan=2><strong style='font-size:14px' colspan=2>$ligne</strong></td>
				<td width=1%>". imgtootltip("delete-32.png","{delete}","ExcludeMimeTypeGroupDel($num)")."</td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	
	<script>
		function ExcludeURLGroupAdd(){
			var type=prompt('$ExcludeMimeType (^.*?\.png$,^http://domain.com/uri.*$,.*?domain\.org...');
			if(!type){return;}
			var XHR = new XHRConnection();
			XHR.appendData('group-ExcludeURL-ADD',type);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ExcludeURLTypeGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ExcludeURLGroupAdd);			
		}
		
		function ExcludeMimeTypeGroupDel(num){
			var XHR = new XHRConnection();
			XHR.appendData('group-ExcludeURL-DEL',num);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ExcludeURLTypeGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ExcludeURLGroupAdd);			
		}		
	
	var x_ExcludeURLGroupAdd= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
	    	ExcludeURLTypeRefreshGroupList();
		}	

</script>		
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function ExcludeMimeType_list(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_GET["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	$ExcludeMimeTypeArray=$EngineOption["ExcludeMimeType"];
	$ExcludeMimeType=$tpl->javascript_parse_text("{ExcludeMimeType}");
	
	
	
	$html="
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{add}: {ExcludeMimeType}","ExcludeMimeTypeGroupAdd()")."</th>
		<th width=99%>{ExcludeMimeType}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";

	while (list ($num, $ligne) = each ($ExcludeMimeTypeArray) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr class=$classtr>
				<td colspan=2><strong style='font-size:14px' colspan=2>$ligne</strong></td>
				<td width=1%>". imgtootltip("delete-32.png","{delete}","ExcludeMimeTypeGroupDel($num)")."</td>
			</tr>
			";
		}
	
	$html=$html."
	</tbody>
	</table>
	
	<script>
		function ExcludeMimeTypeGroupAdd(){
			var type=prompt('$ExcludeMimeType (audio/mpeg,video/x-msvideo,image/gif,image/jpeg...');
			if(!type){return;}
			var XHR = new XHRConnection();
			XHR.appendData('group-ExcludeMimeType-ADD',type);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ExcludeMimeTypeGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ExcludeMimeTypeGroupAdd);			
		}
		
		function ExcludeMimeTypeGroupDel(num){
			var XHR = new XHRConnection();
			XHR.appendData('group-ExcludeMimeType-DEL',num);
			XHR.appendData('gpname','$gpname');
			AnimateDiv('ExcludeMimeTypeGroupdiv');
			XHR.sendAndLoad('$page', 'POST',x_ExcludeMimeTypeGroupAdd);			
		}		
	
	var x_ExcludeMimeTypeGroupAdd= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
	    	ExcludeMimeTypeRefreshGroupList();
		}	

</script>		
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

	
function ClientIP_del(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	unset($EngineOption["ClientIP"][$_POST["group-ClientIP-DEL"]]);
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}	
function ClientURI_del(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	unset($EngineOption["ClientURI"][$_POST["group-ClientURI-DEL"]]);
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}

function ClientURI_add(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	if(trim($_POST["group-ClientURI-ADD"])==null){return;}
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	if(strpos($_POST["group-ClientURI-ADD"], ',')>0){
		$tbl=explode(",", $_POST["group-ClientURI-ADD"]);
		while (list ($num, $sligne) = each ($tbl) ){
			if(trim($sligne)==null){continue;}
			$EngineOption["ClientURI"][]=$sligne;
		}
	}else{
		$EngineOption["ClientURI"][]=$_POST["group-ClientURI-ADD"];
	}
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}
function ClientIP_add(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	if(trim($_POST["group-ClientIP-ADD"])==null){return;}
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	if(strpos($_POST["group-ClientIP-ADD"], ',')>0){
		$tbl=explode(",", $_POST["group-ClientIP-ADD"]);
		while (list ($num, $sligne) = each ($tbl) ){
			if(trim($sligne)==null){continue;}
			$EngineOption["ClientIP"][]=$sligne;
		}
	}else{
		$EngineOption["ClientIP"][]=$_POST["group-ClientIP-ADD"];
	}
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}
function ExcludeURL_add(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	if(trim($_POST["group-ExcludeURL-ADD"])==null){return;}
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	if(strpos($_POST["group-ExcludeURL-ADD"], ',')>0){
		$tbl=explode(",", $_POST["group-ExcludeURL-ADD"]);
		while (list ($num, $sligne) = each ($tbl) ){
			if(trim($sligne)==null){continue;}
			$EngineOption["ExcludeURL"][]=$sligne;
		}
	}else{
		$EngineOption["ExcludeURL"][]=$_POST["group-ExcludeURL-ADD"];
	}
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}
function ExcludeURL_del(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	unset($EngineOption["ExcludeURL"][$_POST["group-ExcludeURL-DEL"]]);
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}
function ExcludeMimeType_add(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	if(trim($_POST["group-ExcludeMimeType-ADD"])==null){return;}
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	if(strpos($_POST["group-ExcludeMimeType-ADD"], ',')>0){
		$tbl=explode(",", $_POST["group-ExcludeMimeType-ADD"]);
		while (list ($num, $sligne) = each ($tbl) ){
			if(trim($sligne)==null){continue;}
			$EngineOption["ExcludeMimeType"][]=$sligne;
		}
	}else{
		$EngineOption["ExcludeMimeType"][]=$_POST["group-ExcludeMimeType-ADD"];
	}
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}
function ExcludeMimeType_del(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$gpname=$_POST["gpname"];
	$sql="SELECT EngineOption FROM Kav4Proxy_groups WHERE groupname='$gpname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
	unset($EngineOption["ExcludeMimeType"][$_POST["group-ExcludeMimeType-DEL"]]);
	$EngineOption_NEW=addslashes(base64_encode(serialize($EngineOption)));
	$sql="UPDATE Kav4Proxy_groups SET EngineOption='$EngineOption_NEW' WHERE groupname='$gpname'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("services.php?kav4Proxy-reload=yes");
}
function ExcludeMimeType(){
		$gpname=$_GET["gpname"];
		$tpl=new templates();
		$page=CurrentPageName();	
	
	$html="
	<div class=explain>{ExcludeMimeTypeKavExplain}</div>
	
	<div id='ExcludeMimeTypeGroupdiv' style='height:350px;overflow:auto'></div>
	
	<script>
		function ExcludeMimeTypeRefreshGroupList(){
			LoadAjax('ExcludeMimeTypeGroupdiv','$page?group-ExcludeMimeType-LIST=yes&gpname=$gpname');
		
		}
	
	
		ExcludeMimeTypeRefreshGroupList();
	</script>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function ExcludeURL(){
		$gpname=$_GET["gpname"];
		$tpl=new templates();
		$page=CurrentPageName();	
	
	$html="
	<div class=explain>{ExcludeURLExplain}</div>
	
	<div id='ExcludeURLTypeGroupdiv' style='height:350px;overflow:auto'></div>
	
	<script>
		function ExcludeURLTypeRefreshGroupList(){
			LoadAjax('ExcludeURLTypeGroupdiv','$page?group-ExcludeURL-LIST=yes&gpname=$gpname');
		
		}
	
	
		ExcludeURLTypeRefreshGroupList();
	</script>
	";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function ClientURI(){
		$gpname=$_GET["gpname"];
		$tpl=new templates();
		$page=CurrentPageName();	
	
	$html="
	<div class=explain>{ClientURI_text}</div>
	
	<div id='ClientURIGroupdiv' style='height:350px;overflow:auto'></div>
	
	<script>
		function ClientURIRefreshGroupList(){
			LoadAjax('ClientURIGroupdiv','$page?group-ClientURI-LIST=yes&gpname=$gpname');
		
		}
	
	
		ClientURIRefreshGroupList();
	</script>
	";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}

function ClientIP(){
		$gpname=$_GET["gpname"];
		$tpl=new templates();
		$page=CurrentPageName();	
	
	$html="
	<div class=explain>{ClientIP_text}</div>
	
	<div id='ClientIPGroupdiv' style='height:350px;overflow:auto'></div>
	
	<script>
		function ClientIPRefreshGroupList(){
			LoadAjax('ClientIPGroupdiv','$page?group-ClientIP-LIST=yes&gpname=$gpname');
		
		}
	
	
		ClientIPRefreshGroupList();
	</script>
	";	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}
