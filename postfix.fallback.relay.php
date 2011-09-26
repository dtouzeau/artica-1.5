<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");

if(isset($_GET["PostfixAddFallBackServer"])){PostfixAddFallBackServer();exit;}
if(isset($_POST["PostfixAddFallBackerserverSave"])){PostfixAddFallBackerserverSave();exit;}
if(isset($_GET["PostfixAddFallBackerserverLoad"])){echo PostfixAddFallBackerserverList();exit;}
if(isset($_POST["PostfixAddFallBackerserverDelete"])){PostfixAddFallBackerserverDelete();exit;}
if(isset($_GET["PostfixAddFallBackServerMove"])){PostfixAddFallBackServerMove();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
js();

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{smtp_fallback_relay}');
if($_GET["hostname"]==null){$_GET["hostname"]="master";}	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	


	
$html="function {$prefix}Loadpage(){
	YahooWin5('650','$page?popup-index=yes&hostname={$_GET["hostname"]}','$title');
	}


 {$prefix}Loadpage();
";	
 
 echo $html;
	
}

function popup_index(){
$page=CurrentPageName();
$tpl=new Templates();
$time=time();
$add_server=$tpl->_ENGINE_parse_body("{add_server}");
$html="
<div class=explain>{smtp_fallback_relay_tiny}<br>{smtp_fallback_relay_text}</div>
<br>
<div id='table_list_$time'></div>
<script>
function PostfixAddFallBackServer(Routingdomain){
	if(!Routingdomain){Routingdomain='';}
	YahooWin6(430,'$page?PostfixAddFallBackServer=yes&hostname={$_GET["hostname"]}&domainName='+Routingdomain,'$add_server')
	}
	
	function RefreshFailBackServers(){
		LoadAjax('table_list_$time','$page?PostfixAddFallBackerserverLoad=yes&hostname={$_GET["hostname"]}&time=$time');
	}
	
	var x_PostfixAddFallBackerserverDelete=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
		RefreshFailBackServers();
		}		
	
	function PostfixAddFallBackerserverDelete(index){
		var XHR = new XHRConnection();	
		XHR.appendData('PostfixAddFallBackerserverDelete',index);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		AnimateDiv('table_list_$time');
		XHR.sendAndLoad('$page', 'POST',x_PostfixAddFallBackerserverDelete);
			
		}	
	
	RefreshFailBackServers();
</script>

";




echo $tpl->_ENGINE_parse_body($html);
}


function PostfixAddFallBackServer(){
	$ldap=new clladp();
	$page=CurrentPageName();
	if($_GET["domainName"]<>null){
		$main=new main_cf();
		$tool=new DomainsTools();
		$arr=explode(',',$main->main_array["smtp_fallback_relay"]);
		if(is_array($arr)){
			$array=$tool->transport_maps_explode($arr[$_GET["domainName"]]);
			$relay_address=$array[1];
			$smtp_port=$array[2];
			$MX_lookup=$array[3];
			$hidden="<input type='hidden' name='TableIndex' value='{$_GET["domainName"]}'>";
		}
	}
	
	if($smtp_port==null){$smtp_port=25;}
	if($MX_lookup==null){$MX_lookup='yes';}
	
	$html="<div id='PostfixAddFallBackerserverSaveID'></div>
	$hidden
	<input type='hidden' name='PostfixAddFallBackerserverSave' value='yes'>
	<table style='width:100%' class=form>
	<td align='right' nowrap class=legend><strong>{relay_address}:</strong></td>
	<td>" . Field_text('relay_address',$relay_address,"font-size:14px;witdh:210px") . "</td>	
	</tr>
	</tr>
	<td align='right' nowrap class=legend><strong>{smtp_port}:</strong></td>
	<td>" . Field_text('relay_port',$smtp_port,"font-size:14px;witdh:60px") . "</td>	
	</tr>	
	<tr>
	
	<td class=legend>{MX_lookups}</td>	
	<td align='right' nowrap>" . Field_checkbox('MX_lookups',1,$MX_lookup)."</td>
	</tr>

	<tr>
	<td align='right' colspan=2><hr>". button("{add}","XHRPostfixAddFallBackerserverSave()")."</td>
	</tr>		
	<tr>
	<td align='left' colspan=2><div class=explain>{MX_lookups}</strong><br>{MX_lookups_text}</div></td>
	</tr>			
		
	</table>
	<script>
	
	var x_XHRPostfixAddFallBackerserverSave=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
		document.getElementById('PostfixAddFallBackerserverSaveID').innerHTML='';
		RefreshFailBackServers();
		}	
	
		function XHRPostfixAddFallBackerserverSave(){
		var XHR = new XHRConnection();	
			if(document.getElementById('MX_lookups').checked){XHR.appendData('MX_lookups','yes');}else{XHR.appendData('MX_lookups','no');}
			XHR.appendData('PostfixAddFallBackerserverSave','yes');
			XHR.appendData('relay_port',document.getElementById('relay_port').value);
			XHR.appendData('relay_address',document.getElementById('relay_address').value);
			XHR.appendData('relay_port',document.getElementById('relay_port').value);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			AnimateDiv('PostfixAddFallBackerserverSaveID');
			XHR.sendAndLoad('$page', 'POST',x_XHRPostfixAddFallBackerserverSave);				
		
		}
		

		
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function PostfixAddFallBackerserverSave(){
	$relay_address=$_POST["relay_address"];
	$tpl=new templates();
	
	if($relay_address==null){echo $tpl->_ENGINE_parse_body('{error_give_server}');return null;}
	
	$smtp_port=$_POST["relay_port"];
	$MX_lookups=$_POST["MX_lookups"];
	
	writelogs("Edit $relay_address $smtp_port $MX_lookups tool->transport_maps_implode($relay_address,$smtp_port,null,$MX_lookups)",__FUNCTION__,__FILE__);
	
	$tool=new DomainsTools();
	$line=$tool->transport_maps_implode($relay_address,$smtp_port,null,$MX_lookups);
	$line=str_replace("smtp:",'',$line);
	$main=new maincf_multi($_POST["hostname"]);
	$arr=explode(',',$main->GET_BIGDATA("smtp_fallback_relay"));
	
	
	if(isset($_GET["TableIndex"])){
		writelogs("Edit " . $arr[$_GET["TableIndex"]] . " to " . $line,__FUNCTION__,__FILE__);
		$arr[$_GET["TableIndex"]]=$line;
	}
	
	
	if(is_array($arr)){
		while (list ($index, $ligne) = each ($arr) ){
				if($ligne<>null){$array[]=$ligne;}
			}
		}

	if(!isset($_GET["TableIndex"])){$array[]=$line;}
	$main->SET_BIGDATA("smtp_fallback_relay",implode(",",$array));
	
}

function PostfixAddFallBackerserverList(){
	$main=new maincf_multi($_GET["hostname"]);
	$tpl=new templates();
	$page=CurrentPageName();
	$add=imgtootltip("plus-24.png","{add_server_domain}","PostfixAddFallBackServer()");
	$hash=explode(',',$main->GET_BIGDATA("smtp_fallback_relay"));
	$tool=new DomainsTools();
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{relay_address}</th>
		<th>{smtp_port}</th>
		<th>{MX_lookups}</th>
		<th colspan=3>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	

	if(is_array($hash)){
		while (list ($index, $ligne) = each ($hash) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				if($ligne<>null){
				$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"PostfixAddFallBackServerMove('$index','up')") ."</td>";
				$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"PostfixAddFallBackServerMove('$index','down')") ."</td>";			
				$arr=$tool->transport_maps_explode("smtp:$ligne");
				$html=$html . "<tr>
			
				<td colspan=2><code style='font-size:14px'><a href=\"javascript:PostfixAddFallBackServer('$index');\">{$arr[1]}</a></code></td>
				<td align='center' style='font-size:14px' ><code>{$arr[2]}</code></td>
				<td align='center'style='font-size:14px'><code>{$arr[3]}</code></td>
				$cell_up
				$cell_down
				<td align='center' width=1%>" . imgtootltip("delete-32.png",'{delete}',"PostfixAddFallBackerserverDelete('$index')")."</td>
				</tr>";
				}
			}
}
$html=$html . "</tbody></table></center>
<script>
	var x_PostfixAddFallBackServerMove=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
		RefreshFailBackServers();
		}	


function PostfixAddFallBackServerMove(num,move){
	var XHR = new XHRConnection();	
	XHR.appendData('PostfixAddFallBackServerMove',num);
	XHR.appendData('move',num);
	XHR.appendData('hostname','{$_GET["hostname"]}');			
	XHR.sendAndLoad('$page', 'GET',x_PostfixAddFallBackServerMove);	
			
}

</script>
";

return $tpl->_ENGINE_parse_body($html);		
}
function PostfixAddFallBackerserverDelete(){
	$main=new maincf_multi($_POST["hostname"]);
	$arr=explode(',',$main->GET_BIGDATA("smtp_fallback_relay"));

		if(is_array($arr)){
			unset($arr[$_POST["PostfixAddFallBackerserverDelete"]]);
		}
	$main->SET_BIGDATA("smtp_fallback_relay",implode(",",$arr));
	
}
function PostfixAddFallBackServerMove(){
	$main=new main_cf();
	$main=new maincf_multi($_GET["hostname"]);
	$hash=explode(',',$main->GET_BIGDATA("smtp_fallback_relay"));	
	$newarray=array_move_element($hash,$hash[$_GET["PostfixAddFallBackServerMove"]],$_GET["move"]);
	$main->SET_BIGDATA("smtp_fallback_relay",implode(",",$newarray));
	
}



?>