<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.os.system.inc');	

	if(isset($_GET["index"])){external_storage_usb();exit;}
	if(isset($_GET["external-storage-usb-list"])){echo external_storage_usb_list();exit;}
	
	js();
	
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{use_network_storage}',"dar.index.php");
	$prompt=$tpl->_ENGINE_parse_body('{prompt_new_computer}');
	$prefix=str_replace(".","_",$page);
	$page=CurrentPageName();	
	$html="
	function {$prefix}Load(){
		RTMMail(650,'$page?index=yes&set-field={$_GET["set-field"]}','$title');
	}
	
	{$prefix}Load();
	
	function BrowseUsbSelect(uid){
	   if(document.getElementById('{$_GET["set-field"]}')){
	   	document.getElementById('{$_GET["set-field"]}').value='usb:'+uid;
		}else{
			alert('Cannot find {$_GET["set-field"]} field');
		}
	}
	
	function BrowseHiddenComputer(){
		var computer=prompt('$prompt');
		if(computer){
			Loadjs('ComputerBrowse.php?&computer='+computer+'&field={$_GET["set-field"]}&format-artica=yes');
		}
	
	}
	
	
	";
	
	echo $html;
	}


function external_storage_usb(){
	$list=external_storage_usb_list();
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<input type='hidden' id='browser-computers-list' value='{$_GET["set-field"]}'>
	<h1>{use_network_storage}</H1>
	<table style='width:99%'>
	<tr>
	<td valign='top' with=70%>
	<p class=caption>{use_network_storage_text}</p>
	</td>
	<td valign='top'>".Buildicon64("DEF_ICO_ADDCOMP")."</td>
	</tr>
	</table>
	<hr>
	<div style='width:100%;height:300px;overflow:auto;padding:3px;' id='smblistp'>
		$list
	</div>
	";

echo  $tpl->_ENGINE_parse_body($html,"dar.index.php");	
	
}


function computer_list(){
	if($_GET["tofind"]=='*'){$_GET["tofind"]=null;}
	if($_GET["tofind"]==null){$tofind="*";}else{$tofind="*{$_GET["tofind"]}*";}
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";
	
$ldap=new clladp();
$attrs=array("uid","ComputerIP","ComputerOS");
$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs);
for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=strtolower(str_replace('$','',$hash[$i]["uid"][0]));
	$js=MEMBER_JS($realuid,1);
	$finalHash[$hash[$i]["uid"][0]]=$hash[$i][strtolower("ComputerIP")][0];
	
}
	
return $finalHash;		
	
}


function external_storage_usb_list(){
	$sock=new sockets();
	$datas=$sock->getfile('smb_scan');
	$finalHash=computer_list();
	

	
	$list="
	
	<table style='width:100%'><tr>";
	
	$tb=explode("\n",$datas);
	if(is_array($tb)){
		$count=0;
	while (list ($uid, $line) = each ($tb) ){
		if(preg_match("#^([0-9\.]+)\s+(.+?)\s+.+?\[(.+?)\]#",$line,$re)){
			$finalHash[strtolower($re[2])]=$re[1];
		}
	}
	}
	
	if(is_array($finalHash)){
		while (list ($uid, $line) = each ($finalHash) ){
			if($count==3){$list=$list."</tr><tr>";$count=0;}
			
			$list=$list."<td>" . Paragraphe32("noacco:$uid",
			$line,
			"Loadjs('ComputerBrowse.php?&computer=$uid&field={$_GET["set-field"]}&format-artica=yes');","32-network-server.png",150)."</td>";
			$count=$count+1;
		}
	}
	
	
	$list=$list."</tr>	</table>";
	$html=$list;
	$tpl=new templates();
	
	return  $tpl->_ENGINE_parse_body($html,"dar.index.php");
	
}
?>