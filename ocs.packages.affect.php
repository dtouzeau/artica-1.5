<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ocs.inc');
	
	

	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["search"])){search();exit;}
	if(isset($_GET["AffectPackageToValue"])){ENABLE_PACKAGE();exit;}
	js();
	
	
	
function js(){
	$FILEID=$_GET["FILEID"];
	$ocs=new ocs();
	$title=$ocs->PACKAGE_NAME_FROM_FILEID($FILEID)." {affected}";		
	$page=CurrentPageName();
	$tpl=new templates();
	$prefix=str_replace(".","_",$page);
	
	$title=$tpl->_ENGINE_parse_body("$title");
	$html="
	
	function {$prefix}LoadMain(){
		YahooWin6('550','$page?popup=yes&FILEID=$FILEID','$title');
		
	}


	{$prefix}LoadMain();";
	
	echo $html;		
}
	
	function popup(){
		$page=CurrentPageName();
		$FILEID=$_GET["FILEID"];
		$html="<div style='font-size:14px;margin:8px'>{PACKAGE_AFFECT_HOWTO}</div>
		<div id='package-affect-computer-list' style='width:95%;height:350px;padding:3px;margin:3px;border:1px solid #CCCCCC'></div>
		<hr>
		<table style='width:100%'>
		<tr>
			<td class=legend style='font-size:13px'>{search}:</td>
			<td>". Field_text("OcsSearcCompPack",null,"font-size:13px;padding:3px;width:100%",null,null,null,false,"SearchComputersCheck(event)")."</td>
		</tr>
		</table>
		
		<script>
			function SearchComputersCheck(e){
				if(checkEnter(e)){SearchComputers();}
			}
		
			function SearchComputers(){
				LoadAjax('package-affect-computer-list','$page?FILEID=$FILEID&search='+document.getElementById('OcsSearcCompPack').value);
			}
		
		SearchComputers();
	</script>";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
		
	}
	
function search(){
	$ocs=new ocs();
	$page=CurrentPageName();
	$sql=$ocs->COMPUTER_SEARCH_QUERY($_GET["search"]);
	$q=new mysql();
	$FILEID=$_GET["FILEID"];
	$results=$q->QUERY_SQL($sql,"ocsweb");
	if(!$q->ok){
		echo "<p>&nbsp;</p><p style='font-size:15px'>$q->mysql_error<hr>$sql</p>";
		return;
	}	
	
	
	$html="
	<table style='width:100%;'>
	<tr>
		<th colspan=2>{computer}</th>
		<th>{ip_address}</th>
		<th>{affected}</th>
	</tr>";
	
	$hash=$ocs->PACKAGE_HASH_AFFECTED_COMPUTERS($FILEID);
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["IPADDRESS"]=="0.0.0.0"){continue;}
	$HARDWARE_ID=$ligne["ID"];
		$html=$html."
		<tr ". CellRollOver().">
		<td width=1%><img src='img/laptop-32.png'></td>
			<td style='font-size:13px'>{$ligne["NAME"]}</td>
			<td style='font-size:13px'>{$ligne["IPSRC"]}</td>
			<td>". Field_checkbox("ID_{$HARDWARE_ID}",1,$hash[$HARDWARE_ID],"OCSPackageAffectID($HARDWARE_ID)")."</td>
		</tr>
		";
		
	}
	$html=$html."</table>
	<script>
		var x_OCSPackageAffectID=function (obj) {
			var results=obj.responseText;
			if (results.length>0){
				alert(results);
				return;
			}
			if(document.getElementById('packages-list')){
				RefreshOCSPackageList();
			}
		}
		
		function OCSPackageAffectID(HARDWARE_ID){
				var XHR = new XHRConnection();
				if(document.getElementById('ID_'+HARDWARE_ID).checked){
					XHR.appendData('AffectPackageToValue','1');
				}else{
					XHR.appendData('AffectPackageToValue','0');
				}
				XHR.appendData('FILEID','$FILEID');
				XHR.appendData('HARDWARE_ID',HARDWARE_ID);
				XHR.sendAndLoad('$page', 'GET',x_OCSPackageAffectID);
		}	
	
	</script>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function ENABLE_PACKAGE(){
	$HARDWARE_ID=$_GET["HARDWARE_ID"];
	$FILEID=$_GET["FILEID"];
	$enabled=$_GET["AffectPackageToValue"];
	$ocs=new ocs();
	if($enabled==1){
		$ocs->PACKAGE_AFFECT($FILEID,$HARDWARE_ID);
	}else{
		$ocs->PACKAGE_DESAFFECT($FILEID,$HARDWARE_ID);
	}
			
	
	
}

	

?>