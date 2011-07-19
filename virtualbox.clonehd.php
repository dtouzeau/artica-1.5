<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsVirtualBoxManager==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["clonehd-source"])){clonehd();exit;}	

	

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$infos=$sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]));
	$array=unserialize(base64_decode($infos));
	$title=$tpl->_ENGINE_parse_body("{CLONE_HD}: {$array["NAME"]}");
	$html="
		function VirtualBoxCloneHDLoad(){
			YahooWin3('550','$page?popup=yes&uuid={$_GET["uuid"]}','$title');
		}

		VirtualBoxCloneHDLoad()";

echo $html;
	
}


function clonehd(){
	$array=unserialize(base64_decode($_GET["clonehd-source"]));
	$sock=new sockets();	
	$infos=$sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["clonehd-uuid"]));	
	$array_infos=unserialize(base64_decode($infos));
	$uuid=$array["uuid"];
	$filename=$array["filename"];
	$type=$_GET["clonehd-type"];
	$format=$_GET["clonehd-format"];
	$newarray=array("uuid"=>$uuid,"filename"=>$filename,"type"=>$type,"format"=>$format,"NAME"=>$array_infos["NAME"]);	
	
		
	$data=base64_encode(serialize($newarray));
	
	$sock=new sockets();
	echo $sock->getFrameWork("cmd.php?virtualbox-clonehd=$data");
	
	
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$infos=$sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]));
	$array=unserialize(base64_decode($infos));
	
	
	
	//print_r($array);		
	
/*VBoxManage clonehd         <uuid>|<filename> <outputfile>
                           [--format VDI|VMDK|VHD|RAW|<other>]
                           [--variant Standard,Fixed,Split2G,Stream,ESX]
                           [--type normal|writethrough|immutable]
                           [--remember]*/

	
	while (list ($uuid_hd, $path) = each ($array["HDS"]) ){
				$newarray=array("uuid"=>$uuid_hd,"filename"=>$path);	
				$hds[base64_encode(serialize($newarray))]=basename($path);
	}
	
	
	
	$format=array(null=>"{same_format}","VDI"=>"VDI","VMDK"=>"VMDK","VHD"=>"VHD","RAW"=>"RAW");
	$typer=array(null=>" ","normal"=>"normal","writethrough"=>"writethrough","immutable"=>"immutable");
	$html="
	<div id='clonehddiv'>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/storage-128.png'>
	<td valign='top'>
	<div class=explain>{CLONE_HD_TEXT}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend>{source_hd}:</td>
		<td>". Field_array_Hash($hds,"clonehd-source",null,null,null,0,'font-size:13px;padding:3px')."</td>
	</tr>	
	<tr>
		<td class=legend>{format}:</td>
		<td>". Field_array_Hash($format,"clonehd-format",null,null,null,0,'font-size:13px;padding:3px')."</td>
	</tr>
	<tr>
		<td class=legend>{type}:</td>
		<td>". Field_array_Hash($typer,"clonehd-type"," ",null,null,0,'font-size:13px;padding:3px')."</td>
	</tr>	
	<tr>
		<td valign='top' align='right' colspan=2><hr>". button("{apply}","CloneHDSAve()")."</td>
	</tr>
	</table>
	</tr>
	</table>
	</div>
	<script>
	
	
var X_CloneHDSAve= function (obj) {
	var results=obj.responseText;
	document.getElementById('clonehddiv').innerHTML='';
	if(results.length>0){alert(results);}
	YahooWin3Hide(); 
	}	
	
	
function CloneHDSAve(){
		var XHR = new XHRConnection();
		XHR.appendData('clonehd-source',document.getElementById('clonehd-source').value);
		XHR.appendData('clonehd-format',document.getElementById('clonehd-format').value);
		XHR.appendData('clonehd-type',document.getElementById('clonehd-type').value);
		XHR.appendData('clonehd-uuid','{$_GET["uuid"]}');
		document.getElementById('clonehddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_CloneHDSAve);		
	}
	
	
	</script>";
	
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}
