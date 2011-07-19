<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.autofs.inc');
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["tools"])){tools();exit;}
	if(isset($_POST["lvsmke2fs"])){lvsmke2fs();exit;}
	if(isset($_POST["autofs"])){autofs();exit;}
	if(isset($_POST["autofs-remove"])){autofs_remove();exit;}
	if(isset($_POST["lvsresize"])){lvsresize();exit;}
	
	
	js();
	
function js(){

	$lvs=$_GET["lvs"];
	$vg=$_GET["vg"];
	$title="$vg&nbsp;&raquo;&nbsp;$lvs";
	$page=CurrentPageName();
	echo "YahooWin2(650,'$page?tabs=yes&lvs=$lvs&vg=$vg','$title');";
}

function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$lvs=$_GET["lvs"];
	$vg=$_GET["vg"];	
	$md=md5("$vg$lvs");
	$array["status"]="{status}";
	$array["tools"]="{tools}";
	
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&lvs=$lvs&vg=$vg\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=$md style='width:100%;height:590px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#$md\").tabs();});
		</script>";		
	
}

function tools(){
	$lvs=$_GET["lvs"];
	$vg=$_GET["vg"];
	$md=md5("$vg$lvs");
	
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$array=unserialize(base64_decode($sock->getFrameWork("lvm.php?lvdisplay=".urlencode($vg))));
	$status=$array["/dev/$vg/$lvs"];
	$UUID=$status["UUID"];
	$mke2fs=Paragraphe("rename-disk-64.png","{create_filesystem}","{create_filesystem_text}","javascript:lvsmke2fs()");
	$autofsp=Paragraphe("database-connect-64-2.png","{automount}","{automount_this_disk}","javascript:lvsAutofs()");
	$autofs=new autofs();
	$hash=$autofs->list_byuuid($status["INFOS"]["UUID"]);
	if(count($hash)>0){
		$autofsp=Paragraphe("database-disconnect-64.png","{disconnect}","{disconnect_this_disk}","javascript:lvsAutofsDel()");
	}
	
	
	
	if($status["INFOS"]["MAGIC_NUMBER"]<>null){
		$mke2fs=Paragraphe("rename-disk-64-grey.png","{create_filesystem}","{create_filesystem_text}","");
	}
	
	if($status["INFOS"]["UUID"]==null){
		$autofsp=Paragraphe("database-connect-64-2-grey.png","{automount}","{automount_this_disk}","");
	}
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td><div id='mke2fs-task'>$mke2fs</div></td>
		<td>$autofsp</td>
	</tr>
	</table>
	
	<script>
	var x_lvsmke2fs= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				RefreshTab('$md');
				
			}	
			
		function lvsmke2fs(){
				var XHR = new XHRConnection();
				XHR.appendData('lvsmke2fs','yes');
				XHR.appendData('vg','$vg');
				XHR.appendData('lvs','$lvs');
				document.getElementById('mke2fs-task').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'POST',x_lvsmke2fs);
			}
			
		function lvsAutofs(){
				var XHR = new XHRConnection();
				XHR.appendData('autofs','yes');
				XHR.appendData('vg','$vg');
				XHR.appendData('lvs','$lvs');
				XHR.sendAndLoad('$page', 'POST',x_lvsmke2fs);		
		}
		
		function lvsAutofsDel(){
				var XHR = new XHRConnection();
				XHR.appendData('autofs-remove','yes');
				XHR.appendData('vg','$vg');
				XHR.appendData('lvs','$lvs');
				XHR.sendAndLoad('$page', 'POST',x_lvsmke2fs);		
		}		
		
		
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$lvs=$_GET["lvs"];
	$vg=$_GET["vg"];	
	$md=md5("$vg$lvs");	
	$groupnamemd=md5($vg);
	$current_text=$tpl->javascript_parse_text("{current}:");	
	$give_new_size_in_mb=$tpl->javascript_parse_text("{give_new_size_in_mb}:");
	$array=unserialize(base64_decode($sock->getFrameWork("lvm.php?lvdisplay=".urlencode($vg))));
	$status=$array["/dev/$vg/$lvs"];
	$crrentsize=$status["CURRENT_SIZE"];
	
	if(is_numeric($crrentsize["POURC"])){
		$free=FormatBytes($crrentsize["FREE"]);
	$currentsize="
	<tr class=oddRow>
		<td style='font-size:14px' class=legend>{used}:</td>
		<td style='font-size:14px;font-weight:bold'>". pourcentage($crrentsize["POURC"])."</td>
		</tr>
		
	<tr class=>
		<td style='font-size:14px' class=legend>{free}:</td>
		<td style='font-size:14px;font-weight:bold'>$free</td>
		</tr>		
		";
	}
	
	
	$sizeMB=round($status["SIZE"]/1024);
	$size=FormatBytes($status["SIZE"]);
	$status["INFOS"]["UUID"];
	if($status["INFOS"]["UUID"]<>null){
		$autofs=new autofs();
		$hash=$autofs->list_byuuid($status["INFOS"]["UUID"]);
		if(count($hash)>0){
			$automount="
				<tr class=oddRow>
				<td style='font-size:14px' class=legend>{automount}:</td>
				<td style='font-size:14px;font-weight:bold'>/automounts/$vg-$lvs</td>
				</tr>";
			
		}
	}
	if($status["INFOS"]["UUID"]==null){$status["INFOS"]["UUID"]="&nbsp;";}
	
	$resize="<a href=\"javascript:blur()\" OnClick=\"javascript:lvsresize()\" style='font-size:14px;font-weight:bold;text-decoration:underline'>";
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=2>$vg&nbsp;&raquo;&nbsp;$lvs</th>
	</tr>
</thead>
<tbody class='tbody'>
<tr class=oddRow>
	<td style='font-size:14px' class=legend>{size}:</td>
	<td style='font-size:14px;font-weight:bold'>$resize&laquo;&nbsp;$size&nbsp;&raquo;</a></td>
</tr>
$currentsize
<tr class=>
	<td style='font-size:14px' class=legend>{uuid}:</td>
	<td style='font-size:14px;font-weight:bold'>{$status["UUID"]}</td>
</tr>
<tr class=oddRow>
	<td style='font-size:14px' class=legend>{uuid}:</td>
	<td style='font-size:14px;font-weight:bold'>{$status["INFOS"]["UUID"]}</td>
</tr>
<tr class=>
	<td style='font-size:14px' class=legend>dev:</td>
	<td style='font-size:14px;font-weight:bold'>/dev/$vg/$lvs</td>
</tr>
$automount
</table>

<script>
	var x_lvsresize= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('$md');
		ExpanVG_$groupnamemd();
	}	

	function lvsresize(){
		var newsize=prompt('$current_text{$sizeMB}MB: $give_new_size_in_mb');
		if(newsize){
			var XHR = new XHRConnection();
			XHR.appendData('lvsresize','yes');
			XHR.appendData('vg','$vg');
			XHR.appendData('lvs','$lvs');
			XHR.appendData('size',newsize);
			XHR.sendAndLoad('$page', 'POST',x_lvsresize);
		
		}
	
	}

</script>
";	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function lvsmke2fs(){
	$sock=new sockets();
	$vg=$_POST["vg"];
	$lvs=$_POST["lvs"];
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("lvm.php?mke2fs=yes&vg=$vg&lvs=$lvs"));
	echo $datas;	
	
}

function lvsresize(){
	$sock=new sockets();
	$vg=$_POST["vg"];
	$lvs=$_POST["lvs"];
	$size=$_POST["size"];
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("lvm.php?lvresize=yes&vg=$vg&lvs=$lvs&size=$size"));
	echo $datas;		
}

function autofs(){
	$sock=new sockets();	
	$lvs=$_POST["lvs"];
	$vg=$_POST["vg"];			
	$array=unserialize(base64_decode($sock->getFrameWork("lvm.php?lvdisplay=".urlencode($vg))));	
	$status=$array["/dev/$vg/$lvs"];
	$auto=new autofs();
	$auto->uuid=$status["INFOS"]["UUID"];
	$auto->by_uuid_addmedia("$vg-$lvs","auto");
	
}

function autofs_remove(){
	$sock=new sockets();	
	$lvs=$_POST["lvs"];
	$vg=$_POST["vg"];			
	$array=unserialize(base64_decode($sock->getFrameWork("lvm.php?lvdisplay=".urlencode($vg))));	
	$status=$array["/dev/$vg/$lvs"];
	$auto=new autofs();
	$auto->uuid=$status["INFOS"]["UUID"];
	$auto->by_uuid_removemedia("$vg-$lvs","auto");	
}
