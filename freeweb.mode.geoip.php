<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	
	if(isset($_GET["countries-list"])){countries_list();exit;}
	if(isset($_POST["country"])){save();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{country_block}");
	$html="YahooWin3('760','$page?popup=yes&servername={$_GET["servername"]}','{$_GET["servername"]}::$title');";
	echo $html;
	}
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<div class=explain style='margin-top:10px'>{ipblocks_explain}</div>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>{only_selected}</td>
		<td>". Field_checkbox("GEOOnlySelected", 1,null,"RefreshGeoipApacheList()")."</td>
	</tr>
	</tbody>
	</table>	
	<div id='geoip-apache-list' style='height:600px;overflow:auto;width:100%'></div>

	
	
	<script>
		function RefreshGeoipApacheList(){
			onlyS=0;
			if(document.getElementById('GEOOnlySelected').checked){onlyS=1;}
			LoadAjax('geoip-apache-list','$page?countries-list=yes&servername={$_GET["servername"]}&onlyS='+onlyS);
			}
			
		RefreshGeoipApacheList();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function countries_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$free=new freeweb($_GET["servername"]);
	$GEOIP=$free->Params["GEOIP"];

	
	
	
	
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($free->COUNTRIES_ISO) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		if($_GET["onlyS"]==1){if($GEOIP[$line]<>1){continue;}}
		//$js[]="";
		$content="<td class=legend nowrap>$key</td><td>". Field_checkbox("CT_{$line}", 1,$GEOIP[$line],"CheckAPGEO('$line')")."</td>";
		
		$tables[]=$content;
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top' width=1%>&nbsp;</td>";				
	}
}
				
	$tables[]="</table>
	<script>
			var x_CheckAPGEO=function (obj) {
			var results=obj.responseText;
			if(results.length>3){alert(results);}	
			
		}	
	
	
		function CheckAPGEO(geo){
			var XHR = new XHRConnection();
			if(document.getElementById('CT_'+geo).checked){XHR.appendData('value',1);}else{XHR.appendData('value',0);}
			XHR.appendData('servername','{$_GET["servername"]}');			
			XHR.appendData('country',geo);
			XHR.sendAndLoad('$page', 'POST',x_CheckAPGEO);
		}
	
	</script>
	";	
	echo @implode("\n", $tables);
	
}

function save(){
	$free=new freeweb($_POST["servername"]);
	$GEOIP=$free->Params["GEOIP"];
	if($_POST["value"]==0){unset($free->Params["GEOIP"][$_POST["country"]]);}else{
		$free->Params["GEOIP"][$_POST["country"]]=1;
	}
	$free->SaveParams();
}
