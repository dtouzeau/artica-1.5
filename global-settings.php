<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
include_once('ressources/class.templates.inc');
include_once('ressources/class.main_cf.inc');

if(isset($_GET["AddNetworks"])){AddNetworks();exit;}
if(isset($_GET["DeleteNetworks"])){DeleteNetworks();exit;}
if(isset($_GET["Delete_Intet_Interface"])){Delete_Intet_Interface();exit;}
if(isset($_GET["add_inet"])){add_inet();exit;}
if ($_GET["page"]=='viewmain'){viewmain();exit;}
if ($_GET["page"]=='mastercf'){mastercf();exit;}


$main=new main_cf();
		if($main->error==true){
			$tpl=new templates('{Postfix Global settings}','{error_no_socks}');
			echo $tpl->web_page;
			exit;
		}

$page=CurrentPageName();
$tabs=tabs();
$mynetworks=Mynetworks();
$html="

$tabs
<script>
function AddNetworks(){
	var net=prompt('Network','');
	if(net){
		var XHR = new XHRConnection();
		XHR.appendData('AddNetworks',net);
		XHR.setRefreshArea('div_net');
		XHR.sendAndLoad('$page', 'GET');
		}
	}
	
	
function AddInet(){
	var inet=prompt('Interface ?','');
	if(inet){
		var XHR = new XHRConnection();
		XHR.appendData('add_inet',inet);
		XHR.setRefreshArea('div_inet');
		XHR.sendAndLoad('$page', 'GET');
		}
	}
	
function Delete_Inet_Interface(num){
		alert('ok');
		var XHR = new XHRConnection();
		XHR.appendData('Delete_Intet_Interface',num);
		XHR.setRefreshArea('div_inet');
		XHR.sendAndLoad('$page', 'GET');	
}

	
function DeleteMynetworks(num,val){
	if(confirm('Delete ' + val + ' value ?')){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteNetworks',num);
		XHR.setRefreshArea('div_net');
		XHR.sendAndLoad('$page', 'GET');
		}
}
</script>

<fieldset>
<LEGEND>{title1}</LEGEND>
<p>{text1}</p>
<table>
<tr class='rowA'>
<td align='right'>{mydestination_title}</td>
<td><input type='text' name='mydestination' id='mydestination' value='{$main->main_array["mydestination"]}' style='width:80%'></td>
<td width=1%><img src='img/help.gif' style='float:right;cursor:pointer' OnClick=\"javascript:Help('mydestination_explain');\"></td>
</tr>

<tr class='rowA'>
<td align='right'>{myorigin_title}</td>
<td><input type='text' name='myorigin' id='myorigin' value='{$main->main_array["myorigin"]}' style='width:80%'></td>
<td width=1%><img src='img/help.gif' style='float:right;cursor:pointer' OnClick=\"javascript:Help('myorigin_explain');\"></td>
</tr>

<tr class='rowA'>
<td align='right'>{myhostname_title}</td>
<td><input type='text' name='myhostname' id='myhostname' value='{$main->main_array["myhostname"]}' style='width:80%'></td>
<td width=1%><img src='img/help.gif' style='float:right;cursor:pointer' OnClick=\"javascript:Help('myhostname_explain');\"></td>
</tr>

<tr class='rowA'>
<td align='right'>{mynetworks_title}</td>
<td>
	<div id='div_net'>$mynetworks</div><br>
	<input type='button' value='Add a network&nbsp;&raquo;' OnClick=\"javascript:AddNetworks();\" style='float:right'>
</td>
<td width=1%><img src='img/help.gif' style='float:right;cursor:pointer' OnClick=\"javascript:Help('mynetworks_explain');\"></td>
</tr>

<tr class='rowA'>
<td align='right'>{relayhost_title}</td>
<td><input type='text' name='relayhost' id='relayhost' value='{$main->main_array["relayhost"]}' style='width:80%'></td>
<td width=1%><img src='img/help.gif' style='float:right;cursor:pointer' OnClick=\"javascript:Help('relayhost_explain');\"></td>
</tr>

<tr class='rowA'>
<td align='right'>{inet_interfaces_title}</td>
<td><div id='div_inet'>" . MyInet_interfaces() . "</div><br>
<input type='button' value='{bt_add_inet}' OnClick=\"javascript:AddInet();\" style='float:right'>
</td>
<td width=1%><img src='img/help.gif' style='float:right;cursor:pointer' OnClick=\"javascript:Help('inet_interfaces_explain');\"></td>
</tr>

<tr>
<td align='right' colspan=3><div style='text-align:right' align='right'><input type='button' value='Submit&nbsp;&raquo;' OnClick=\"javascript:main_cf_submit_fields();\" style='float:right'></div></td>
</tr>

</table>

</fieldset>

<input type='hidden' id='myhostname_explain' value=\"{myhostname_text}\">
<input type='hidden' id='myorigin_explain' value=\"{myorigin_text}\">
<input type='hidden' id='mynetworks_explain' value=\"{mynetworks_text}\">
<input type='hidden' id='relayhost_explain' value=\"{relayhost_text}\">
<input type='hidden' id='mydestination_explain' value=\"{mydestination_text}\">
<input type='hidden' id='mydestination_explain' value=\"{mydestination_text}\">
<input type='hidden' id='inet_interfaces_explain' value=\"{inet_interfaces_text}\">

";

$tpl=new templates('{Postfix Global settings}',$html);
echo $tpl->web_page;


function MyInet_interfaces(){
	$main=new main_cf();
	
$html="

	<table>
	<tr class=rowT>
	<td colspan=3 >{inet_interfaces_title}</td>
	</tr>";
	
	if(!is_array($main->array_inet_interfaces)){return null;}
	while (list ($num, $val) = each ($main->array_inet_interfaces) ){

		$html=$html . "
		<tr class=rowB>
			<td width=1%><img src='img/network.gif'></td>
			<td >$val</td>
			<td  width=1%><a href=\"#\" OnClick=\"javascript:Delete_Inet_Interface($num);\"><img src='img/x.gif'  border=0></td>
		</tr>";
		
	}
	
	$html=$html . "</table>
	<p>{inet_interfaces_text}</p>
	";
	$tpl=new templates();
	return $tpl->_parse_body($html);
}


function Mynetworks(){
	$main=new main_cf();
	$html="

	<table>
	<tr class=rowT>
	<td colspan=3 >{mynetworks_title}</td>
	</tr>";
	
	if(!is_array($main->array_mynetworks)){return null;}
	while (list ($num, $val) = each ($main->array_mynetworks) ){

		$html=$html . "
		<tr class=rowB>
			<td width=1%><img src='img/network-1.gif'></td>
			<td >$val</td>
			<td  width=1%><a href=\"javascript:DeleteMynetworks($num,'$val');\"><img src='img/x.gif'  border=0></td>
		</tr>";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_parse_body($html);
}

function  AddNetworks(){
	$main=new main_cf();
	$main->array_mynetworks[]=$_GET["AddNetworks"];
	$main->save_conf();
	echo Mynetworks();
}

function DeleteNetworks(){
	$num=$_GET["DeleteNetworks"];
	$main=new main_cf();
	unset($main->array_mynetworks[$num]);
	$main->save_conf();
	echo Mynetworks();
}

function add_inet(){
	$main=new main_cf();
	$main->array_inet_interfaces[]=$_GET["add_inet"];
	$main->save_conf();
	echo MyInet_interfaces();
}
function Delete_Intet_Interface(){
	$main=new main_cf();
	unset($main->array_inet_interfaces[$_GET["Delete_Intet_Interface"]]);
	$main->save_conf();
	echo MyInet_interfaces();	
}


function tabs(){
$page=currentpagename();

$array["{pcfg_title1}"]="";
$array["{pcfg_title2}"]="page=viewmain";
$array["{pcfg_title3}"]="page=mastercf";

//QUERY_STRING

while (list ($num, $val) = each ($array) ){
	if($val==$_SERVER["QUERY_STRING"]){
		$li=$li . "\t<li class='active'>$num</li>\n";
	}else{$li=$li . "\t<li><a href='$page?$val'>$num</a></li>\n";}
	
}

$html="
<ul id=\"onglets\">
  $li
</ul>";	
return $html;	
	
}

function viewmain(){
	
$main=new main_cf();
$page=CurrentPageName();
$tabs=tabs();	
$hash=$main->array_hashs;
 $html=
"$tabs
<table>
<tr>
<td width=50% valign='top'>
<FIELDSET>
	<legend>{title_main_hash}</legend>
	<table>
		<tr class='rowA'>
			<td align='right'>virtual_alias_maps:</td>
			<td>{$hash['virtual_alias_maps']}</td>
		</tr>
		<tr class='rowA'>
			<td align='right'>virtual_mailbox_domains:</td>
			<td>{$hash['virtual_mailbox_domains']}</td>
		</tr>	
		<tr class='rowA'>
			<td align='right'>virtual_mailbox_maps:</td>
			<td>{$hash['virtual_mailbox_maps']}</td>
		</tr>			
		<tr class='rowA'>
			<td align='right'>transport_maps:</td>
			<td>{$hash['transport_maps']}</td>
		</tr>	
		<tr class='rowA'>
			<td align='right'>relay_domains:</td>
			<td>{$hash['relay_domains']}</td>
		</tr>						
	</table>
</FIELDSET>
</td>
<td width=50% valign='top'>
<FIELDSET>
	<legend>{title_main_transport}</legend>
	<table>
		<tr class='rowA'>
			<td align='right'>virtual_transport:</td>
			<td>{$main->main_array['virtual_transport']}</td>
		</tr>				
	</table>
</FIELDSET>
</td>
</tr>
</table>


";

$tpl=new templates('{pcfg_title2}',$html);
echo $tpl->web_page;

	
}

function mastercf(){

$master=new master_cf();
if($master->error<>null){ERROR_CLASS($master->error);exit;}
$array=$master->master_array;
$tabs=tabs();
	$html="$tabs<table  class='table_master_cf'>
	<tr class=rowT>
	<td width=50>service</td>
	<td width=20>type</td>
	<td width=20>private</td>
	<td width=20>unpriv</td>
	<td width=20>chroot</td>
	<td width=20>wakeup</td>
	<td>command + parameters</td>
	</td>
	";

	while (list ($num, $val) = each ($array) ){
		$ligne=$val["LINE"];
		if($class=="rowA"){$class="rowB";}else{$class="rowA";}
		$html =$html . "<tr class='$class'>
		<td>{$ligne["SERVICE"]}</TD>
		<td align='center'>{$ligne["TYPE"]}</TD>
		<td  align='center'>{$ligne["PRIVATE"]}</TD>
		<td  align='center'>{$ligne["UNPRIV"]}</TD>
		<td  align='center'>{$ligne["CHROOT"]}</TD>
		<td  align='center'>{$ligne["WAKEUP"]}</TD>
		<td  align='left'>{$ligne["COMMAND"]}" . table_options($val["OPTIONS"]) . "</TD>
		</tr>
		";
	}
	
	$html=$html . "</table>";
	
	$tpl=new templates("{title}",$html);
	echo $tpl->web_page;	
	
}
	

function table_options($line){

	if(count($line)==0){return null;}

	
	
	while (list ($num, $val) = each ($line) ){
	$html = $html . "<div style='margin-left:10px'>$val</div>";
	
	
}
return $html ;
}
	

function ERROR_CLASS($error){
	
	$body="<p style='font-size:13px'><b>The page can not continue to be excuted du the error &laquo;$error&raquo;</p>";
	$tpl=new templates("Error generated by class master_cf",$body);
	echo $tpl->web_page;
	
}	


?>