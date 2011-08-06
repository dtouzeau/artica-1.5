<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}


	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["upgrade-kernel-confirm"])){upgrade();exit;}
	
js();


function upgrade(){
	
	$pkg=$_GET["upgrade-kernel-confirm"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?system-debian-upgrade-kernel=$pkg");
	
}



function js(){
		$page=CurrentPageName();
		$tpl=new templates();
		$title=$tpl->_ENGINE_parse_body("{system_kernel}");
		$perform_installation_of=$tpl->javascript_parse_text("{perform_installation_of}");
		$server_will_be_rebooted=$tpl->javascript_parse_text("{server_will_be_rebooted}");
		$html="
		
		
		function system_kernel_load(){
			YahooWin('820','$page?popup=yes','$title');
			}
			
		var x_KernelInstall=function(obj){
	      var tempvalue=obj.responseText;
	      if(tempvalue.length>3){alert(tempvalue);}
	      YahooWinHide();
		}			
			
		function KernelInstall(package){
			if(confirm('$perform_installation_of:\\n--------------\\n'+package+'\\n--------------\\n$server_will_be_rebooted')){
				var XHR = new XHRConnection();
		 		XHR.appendData('upgrade-kernel-confirm',package);
			 	XHR.sendAndLoad('$page', 'GET',x_KernelInstall);
			}
		}
			
		system_kernel_load();
		";
		echo $html;
		
	
}


function popup(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?system-debian-kernel=yes");
	$array=unserialize(base64_decode(@file_get_contents("ressources/logs/kernel.lst")));
	$CPU_FAMILY=$array["INFOS"]["CPU_FAMILY"];
	$soitquatrebits=$array["INFOS"]["64BITS"];
	$HT_SUPPORT=$array["INFOS"]["HT"];
	$CURRENT=$array["INFOS"]["CURRENT"];
	$MODEL=$array["INFOS"]["MODEL"];
	$nb_cpu=$array["INFOS"]["PROCESSOR"];
	if($CPU_FAMILY<10){$icpu="i{$CPU_FAMILY}86";}
	if(preg_match("#.+?-([0-9]+)86#",$CURRENT,$re)){$kernel_arch="i{$re[1]}86";}
	
	if($kernel_arch<>null){
		if($icpu<>null){
			if($icpu<>$kernel_arch){
				$why="$icpu<>$kernel_arch";
				$must_change=true;
			}
		}
	}
	
	if($icpu==null){$icpu=$CPU_FAMILY;}
	if($HT_SUPPORT){$HT_SUPPORT="{yes}";}else{$HT_SUPPORT="{no}";}
	if($soitquatrebits){$soitquatrebits="{yes}";}else{$soitquatrebits="{no}";}
	
	if(is_array($array["DPKG"])){
		
		$packages="<table style='width:100%'>
		<tr>
			<th colspan=2>{version}</th>
			<th>{minor}</th>
			<th>{architecture}</th>
			<th colspan=2>{infos}</th>
		</tr>
		";
		
		while (list ($num, $array2) = each ($array["DPKG"]) ){
			$color="black";
			$install="<input type='button' OnClick=\"javascript:KernelInstall('{$array2["PACKAGE"]}');\" value='&nbsp;{upgrade}&nbsp;&raquo;' style='margin:0px;'>";
			if($array2["FULL_VERSION"]==$CURRENT){
				$color="red";
				$install="&nbsp;";
			}
			if($array2["ARCH"]=="generic-pae"){$array2["ARCH"]="PAE Generic > 64 Gb mem";}
			if($array2["ARCH"]=="virtual"){$array2["ARCH"]="Virtual Machine environment";}
			
			
			
			
			$packages=$packages."
			<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td style='font-size:12px;font-weight:bold;color:$color'>{$array2["VERSION"]}</td>
			<td style='font-size:12px;font-weight:bold;color:$color'>{$array2["BUILD"]}</td>
			<td style='font-size:12px;font-weight:bold;color:$color'>{$array2["ARCH"]}</td>
			<td style='font-size:10px;font-weight:bold;color:$color'>{$array2["INFOS"]}</td>
			<td width=1%>$install</td>
			</tr>";
			
			
		}
		
	}
	$packages=$packages."</table>";
	
	$img="<img src='img/linux-inside-96.png'>";
	
	if($must_change){$img=Paragraphe("warning64.png","{kernel_mismatch}","{kernel_mismatch_text}<br>$why");}
	
	
	$head="
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{current_version}</td>
		<td style='font-size:13px'><strong>$CURRENT</strong></td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{cpu_family}</td>
		<td style='font-size:13px'><strong>$icpu</strong></td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{nb_cpus}</td>
		<td style='font-size:13px'><strong>$nb_cpu</strong></td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{model}</td>
		<td style='font-size:13px'><strong>$MODEL</strong></td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>Intel® Hyper-Threading Technology</td>
		<td style='font-size:13px'><strong>$HT_SUPPORT</strong></td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>64 bits:</td>
		<td style='font-size:13px'><strong>$soitquatrebits</strong></td>
	</tr>				
	</table>";
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>&nbsp;</td>
		<td valign='top'>
			<table>
			<tr>
				<td width=1%>$img</td>
				<td>$head</td>
			</tr>
			</table>
			<hr>
			$packages
		</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}



?>