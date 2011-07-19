<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once("ressources/class.os.system.inc");
	include_once("ressources/class.lvm.org.inc");
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["tasks"])){tasks();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["vgformat"])){vgformat();exit;}
	if(isset($_GET["lvremove-js"])){lvremove_js();exit;}
	if(isset($_GET["lvremove"])){lvremove();exit;}
	
	if(isset($_GET["affectvg"])){affectvg();exit;}
	if(isset($_GET["affectVGdev"])){affectvg_save();exit;}
	if(isset($_GET["vgformat-logs"])){vgformat_logs();exit;}
	
	if(isset($_GET["extend-js"])){extend_js();exit;}
	if(isset($_GET["extend"])){extend_popup();exit;}
	if(isset($_GET["extend-perform"])){extend_perform();exit;}
	js();
	
	
function lvremove_js(){
	
	$dev=$_GET["lvremove-js"];
	$group_name=$_GET["group_name"];
	$html="
		function vg_refresh_list(){
			LoadAjax('lvg','system.internal.disks.php?lvg=$group_name');
		}
	
	".func_lv_remove($dev)."
	
	lvremove('$dev');
	";
	
	echo $html;
}


function extend_js(){
	$page=CurrentPageName();
	$mapper=$_GET["extend-js"];
	$tpl=new templates();
	$RESIZE_VG=$tpl->_ENGINE_parse_body("{RESIZE_VG}");
	
	$html="
	function lvm_extend_start(){
			YahooWin6(500,'$page?extend=$mapper','$RESIZE_VG');
		}
	
	
	lvm_extend_start();";
	echo $html;
	
}

function tabs(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();

	$arrayDev=unserialize(base64_decode($sock->getFrameWork("cmd.php?udevinfos=yes&dev={$_GET["dev"]}")));
	$arrayField=base64_encode(serialize($arrayDev));
	if(!is_file('ressources/usb.scan.inc')){$sock->getFrameWork("cmd.php?usb-scan-write=yes");}	
	include_once 'ressources/usb.scan.inc';	
	
	$array["popup"]='{info}';
	$array["tasks"]='{tasks}';	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&dev={$_GET["dev"]}&dev-infos=$arrayField\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_lvmvg style='width:100%;height:495px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_lvmvg\").tabs();});
		</script>";		
	
	
}

function tasks(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$dev=$_GET["dev"];
	
	if(preg_match('#\/dev\/(.+?)\/(.+)#',$dev,$re)){
		$re[1]=str_replace('-','--',$re[1]);
		$re[2]=str_replace("-","--",$re[2]);
		$devmapper="/dev/mapper/{$re[1]}-{$re[2]}";
	}

	
	
	$connect=Paragraphe("database-connect-64.png",'{CONNECT_HD}','{CONNECT_HD_TEXT}',"javascript:Loadjs('fstab.php?dev=$dev');");
	$delete_vg=Paragraphe("gd-delete-64.png",'{DELETE_VG}','{DELETE_VG_TEXT}',"javascript:lvremove('$devmapper');");
	$resize_vg=Paragraphe("64-hd-resize.png","{RESIZE_VG}","{RESIZE_VG_TEXT}","javascript:Loadjs('$page?extend-js=$devmapper');");
	$affect=Paragraphe("hd-org-64.png",'{AFFECT_VG}','{AFFECT_VG_TEXT}',"javascript:lvAffect('$dev');");
	$format=Paragraphe("hd-64-format.png",'{FORMAT_HD}','{FORMAT_HD_TEXT}',"javascript:vgformat('$dev');");
	$automount=Paragraphe("magneto-64.png","{automount_center}","{partition_automount_center_text}",
	"javascript:PartitionAutofsConnect2();");

	$automount_disabled=Paragraphe("magneto-64-grey.png","{automount_center}","{partition_automount_center_text}");

	if(!$users->autofs_installed){
		$automount=$automount_disabled;	
	}	
	
	$tr[]="$connect";
	$tr[]="$automount";
	$tr[]=$delete_vg;
	$tr[]=$resize_vg;
	$tr[]=$format;
	$tr[]=$affect;		
	$autofs_ask_dir=$tpl->_ENGINE_parse_body("{autofs_ask_dir}");
	$autofs_ask_dir_def=basename($dev);
	
	$tables[]="<table style='width:100%'><tr>";
	$t=0;
	while (list ($key, $line) = each ($tr) ){
			$line=trim($line);
			if($line==null){continue;}
			$t=$t+1;
			$tables[]="<td valign='top'>$line</td>";
			if($t==2){$t=0;$tables[]="</tr><tr>";}
			}
	
	if($t<2){
		for($i=0;$i<=$t;$i++){
			$tables[]="<td valign='top'>&nbsp;</td>";				
		}
	}	
	$html="<center><div style='width:470px'>". implode("\n",$tables)."</div></center>
	
	<script>


var x_PartitionAutofsConnect2= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);return;}
	RefreshTab('main_config_lvmvg');
	
	}	

	function PartitionAutofsConnect2(){
		var fs='';
		var dir=prompt('$autofs_ask_dir','$autofs_ask_dir_def');
		if(dir){
			var XHR = new XHRConnection();
			XHR.appendData('autofs-connect','$devmapper');
			XHR.appendData('dev','$devmapper');
			XHR.appendData('fs',fs);
			XHR.appendData('LOCAL_DIR',dir);
			XHR.sendAndLoad('system.internal.partition.php', 'GET',x_PartitionAutofsConnect2);			
		}
	
	}
	
</script>
	
	
	";
	$datas=$tpl->_ENGINE_parse_body($html);		
	echo $datas;		
}

function extend_popup(){
	$mapper=$_GET["extend"];
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();
	$DiskInfos=unserialize(base64_decode($sock->getFrameWork("cmd.php?DiskInfos=$mapper")));
	$USED=$DiskInfos["USED"];
	if(preg_match("#\/dev\/mapper\/(.+?)-#",$mapper,$ri)){
		$vg=$ri[1];
		$vgsinfos=unserialize(base64_decode($sock->getFrameWork("cmd.php?vgs-info=$vg")));
		$FREE=$vgsinfos[$vg]["FREE"];
		if(preg_match("#([0-9\.,]+)([A-Z]+)#",$FREE,$re)){$FREE=$re[1];}
	}
		
		
	
	
	$SIZE=$DiskInfos["SIZE"];
	
	if(preg_match("#([0-9]+)([A-Z]+)#",$SIZE,$re)){$SIZE=$re[1];$UNIT=$re[2];}
	if(preg_match("#([0-9]+)([A-Z]+)#",$USED,$re)){$USED=$re[1];$UNIT_USED=$re[2];}
	
	if($UNIT_USED="M"){$USED=$USED/1000;}
	$USED_JS=round($USED);
	
	$RESIZE_VG=$tpl->_ENGINE_parse_body("{RESIZE_VG}");
	$TOT=$SIZE+$FREE;
	$html="<h1>$RESIZE_VG</h1>
	<div id='lvrsizediv'>
	<p class=caption>{RESIZE_VG_TEXT}</p>
	<div style='font-size:14px;margin:5px;font-weight:bold;margin-top:15px;margin-bottom:15px'>{size}:$SIZE$UNIT&nbsp;|&nbsp;{minimal}:$USED$UNIT&nbsp;|&nbsp;{free}:$FREE$UNIT&nbsp;|&nbsp;{maximum}:$TOT</div>
	<input type='hidden' id='vgsize' value='$SIZE'>
	<div id=\"slider\"></div>
	
	<center style='margin:5px'><span style='font-size:18px;color:#005447' id='selected_size'>$SIZE</span><span style='font-size:18px;color:#005447'>{$UNIT}</span></center>
	<div style='margin:5px;text-align:right'>". button("$RESIZE_VG","ResizeVGPerfom()")."</div>
	</div>
	<script>
	$(document).ready(function(){
  		$(\"#slider\").slider({
   			animate: true,
    		change: handleSliderChange,
   			slide: handleSliderSlide,
   			min:$SIZE,
   			max:$TOT,
   			value: $SIZE,
  		});
	});
	
function handleSliderChange(e, ui){
	document.getElementById('vgsize').value=ui.value;
     
}

function handleSliderSlide(e, ui){
	document.getElementById('selected_size').innerHTML=ui.value;
}	

	var x_ResizeVGPerfom= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			lvm_extend_start();
			}	

function ResizeVGPerfom(){
	var newsize=document.getElementById('vgsize').value;
	var XHR = new XHRConnection();
	XHR.appendData('extend-perform','$mapper');
	XHR.appendData('size',newsize);
	XHR.appendData('current','$SIZE');
	XHR.appendData('unit','$UNIT');			
	document.getElementById('lvrsizediv').innerHTML='<div style=\"width:100%;height:300px:overflow:auto;background-color:white\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
	XHR.sendAndLoad('$page', 'GET',x_ResizeVGPerfom);
}
</script>";
echo $tpl->_ENGINE_parse_body($html);
}

function extend_perform(){
	$mapper=$_GET["extend-perform"];
	$size=$_GET["size"];
	$current=$_GET["current"];
	
	if($size>$current){
		$sock=new sockets();
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?lv-resize-add=$mapper&size=$size&unit={$_GET["unit"]}")));
		echo implode("\n",$datas);
		exit;
	}
	
	
	echo 'Operation not permitted';
}

function func_lv_remove($dev=null){
	$tpl=new templates();
	$delete=$tpl->javascript_parse_text('{delete}');
	$macro_build_bigpart_warning=$tpl->javascript_parse_text('{macro_build_bigpart_warning}');
	$macro_build_bigpart_warning=str_replace("\n",'\\n',$macro_build_bigpart_warning);
	$page=CurrentPageName();	
$html="

	var x_lvremove= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			WinORGHide();
			vg_refresh_list(vg_group_mem);
			}	

function lvremove(mapper){
			if(document.getElementById('VG-NAME')){
				vg_group_mem=document.getElementById('VG-NAME').value;
				if(vg_group_mem.length==0){
					alert('VG-NAME=NULL !');
					return;
				}
			}

			if(confirm('\\n$delete: '+mapper+'\\n\\n$macro_build_bigpart_warning')){
				var XHR = new XHRConnection();
				XHR.appendData('lvremove','$dev');
				XHR.appendData('mapper',mapper);
				if(document.getElementById('t')){
					document.getElementById('t').innerHTML='<div style=\"width:100%;height:300px:overflow:auto;background-color:white\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				}
				
				if(document.getElementById('lvg')){
					document.getElementById('lvg').innerHTML='<div style=\"width:100%;height:300px:overflow:auto;background-color:white\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				}				
				
				XHR.sendAndLoad('$page', 'GET',x_lvremove);
			}
		}";	
	return $html;
}
	
function js(){

	$dev=$_GET["dev"];
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_LVM}::'.$dev,'system.internal.disks.php');
	$suffix=str_replace('.','_',$page);
	$macro_build_bigpart_warning=html_entity_decode($tpl->_ENGINE_parse_body('{macro_build_bigpart_warning}','system.internal.disks.php'));
	$macro_build_bigpart_warning=str_replace("\n",'\\n',$macro_build_bigpart_warning);
	$delete=html_entity_decode($tpl->_ENGINE_parse_body('{delete}','system.internal.disks.php'));
	$AFFECT_VG=$tpl->_ENGINE_parse_body('{AFFECT_VG}','system.internal.disks.php');
	$html="
	var vg_group_mem='';
	dev_mem='';
	
		function {$suffix}LoadPage(){
			LoadWinORG(570,'$page?tabs=yes&dev=$dev','$title');
		}
		
	var x_vgformat= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
			setTimeout('vgformat_waitlogs()',3000);
			}	
			
	var x_lvmAffectSave= function (obj) {
		{$suffix}LoadPage();
		YahooWin6Hide();
	}

	
	var x_vgformat_waitlogs= function (obj) {
			var response=obj.responseText;
			document.getElementById('formatlogs').innerHTML=response;
			setTimeout('vgformat_waitlogs()',3000);
			}	
			
			
	function vgformat_waitlogs(){
		if(!WinORGOpen()){return;}
		if(!document.getElementById('formatlogs')){return;}
		var XHR = new XHRConnection();
	    XHR.appendData('vgformat-logs',dev_mem); 
	    XHR.sendAndLoad('$page', 'GET',x_vgformat_waitlogs);   
		}

    
		
		function vgformat(dev){
		    dev_mem=dev;
			if(confirm('$macro_build_bigpart_warning')){
				var XHR = new XHRConnection();
				XHR.appendData('vgformat',dev);
				document.getElementById('t').innerHTML='<div style=\"width:100%;height:400px;overflow:auto;background-color:white\" id=\"formatlogs\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_vgformat);
			
			}
		}
		
		function lvAffect(dev){
			YahooWin6(400,'$page?affectvg=yes&dev=$dev','$AFFECT_VG');
		}

		function lvmAffectSave(){
			var XHR = new XHRConnection();
			XHR.appendData('affectVGdev',document.getElementById('dev_org').value);
			XHR.appendData('ou',document.getElementById('affectToThisOu').value);
			XHR.sendAndLoad('$page', 'GET',x_lvmAffectSave);
		}
		
		
		".func_lv_remove($dev)."
	
	{$suffix}LoadPage();";
	
	echo $html;
	
}

function vgformat(){
	$dev=$_GET["vgformat"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?format-disk-unix=$dev");
	
	
	$tpl=new templates();
	$tbl=explode("\n",$datas);
	while (list ($num, $val) = each ($tbl)){
		if($val==null){continue;}
		echo html_entity_decode(trim($tpl->_ENGINE_parse_body($val)))."\n";
	}
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	
}


function vgformat_logs(){
	$dev=$_GET["vgformat-logs"];
	if($dev==null){return null;}
	$file=md5($dev).".format";
	$sock=new sockets();
	$datas=explode("\n",$sock->getFrameWork("cmd.php?read-log=$file"));
	while (list ($num, $line) = each ($datas)){
		if($line==null){continue;}
		$line=str_replace("","",$line);
		echo "<div><code>$line</code></div>";	
		
	}
	}

function popup(){
	$page=CurrentPageName();
	$suffix=str_replace('.','_',$page);
	$dev=$_GET["dev"];
	$sock=new sockets();
	$datas=trim($sock->getFrameWork("cmd.php?lvdisplay=$dev"));
	$tbl=explode("\n",$datas);
	$lvm_ou=new lvm_org();
	$ou=$lvm_ou->FindOuByDev($dev);
	
	
	if(preg_match('#\/dev\/(.+?)\/(.+)#',$dev,$re)){
		$re[1]=str_replace('-','--',$re[1]);
		$re[2]=str_replace("-","--",$re[2]);
		$devmapper="/dev/mapper/{$re[1]}-{$re[2]}";
		$tbl[]="dev mapper\t\t$devmapper";
	}
	
	
	$dfmoinshdev=$sock->getFrameWork("cmd.php?dfmoinshdev=$devmapper");
	$df_ar=explode("\n",$dfmoinshdev);
	while (list ($num, $line) = each ($df_ar)){
		if(preg_match("#([0-9]+)[A-Za-z]+\s+([0-9]+)[A-Za-z]+\s+([0-9]+)[A-Za-z]+\s+([0-9]+)%\s+(.+)#",$line,$ir)){
			$tot=$ir[1];
			$used=$ir[2];
			$pourc=$ir[4];
			$mounted=$ir[5];		
			$tbl[]="mounted\t\t$mounted ($pourc% {used})";
			$pourcent=$pourc*3;
		}
	}
	if($ou<>null){$tbl[]="{organization}\t\t$ou";}
	
	$t="<table style='width:100%' class=form>";
	while (list ($num, $line) = each ($tbl)){
		if(preg_match('#(.+?)\s+\s+(.+)#',$line,$re)){
			if(trim($re[1])==null){continue;}
			$array[trim($re[1])]=trim($re[2]);
			$t=$t."<tr>
					<td class=legend nowrap style='font-size:14px'>".trim($re[1])."</td>
					<td nowrap><strong style='font-size:14px'>".trim($re[2])."</strong></td>
				</tr>";
		}
	}
	$t=$t."</table>";
	
	
	
	$tpl=new templates();
	
	if($pourc>0){
		$barre="<div style='width:300px;border:1px solid #CCCCCC;height:30px;background-color:#00E100'>
				<div style='float:right;color:white;padding:5px;font-size:13px;font-weight:bold'>$pourc% {used}</div>
				<div style='width:$pourcent;background-color:#D20B2A;height:30px'>&nbsp;</div>
				</div>
			";
	}
	

	
	$html="
	<input type='hidden' id='VG-NAME' value='{$array["VG Name"]}'>

				<div id='t'>
				$t
					<table style='width:100%'>
					<tr>
						<td valign='top'>
							$barre
						</td>
						<td valign='top' width=1%>
						" . imgtootltip('32-redo.png','{refresh}',"{$suffix}LoadPage();")."
						</td>
					</tr>
					</table>
				</div>
		
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'system.internal.disks.php');
	
	
}

function lvremove(){
	include_once("ressources/class.fstab.inc");
	$dev=$_GET["lvremove"];
	$mapper=$_GET["mapper"];
	$fstab=new fstab();
	$sock=new sockets();
	if(is_array($fstab->fstab_array[$mapper])){
	while (list ($num, $array) = each ($fstab->fstab_array[$mapper])){
		$mount_point=$array["mount"];
		$sock->getFrameWork("cmd.php?umount-disk=$mount_point");
		}
	}
	$sock->getFrameWork("cmd.php?fstab-remove=yes&dev=$mapper");
	$sock->getFrameWork("cmd.php?umount-disk=$mapper");
	$sock->getFrameWork("cmd.php?umount-disk=$dev");
	$tpl=base64_decode(unserialize($sock->getFrameWork("cmd.php?lvremove=$dev")));
	if(is_array($tbl)){
	while (list ($num, $val) = each ($tbl)){
		if($val==null){continue;}
		echo html_entity_decode(trim($tpl->_ENGINE_parse_body($val)))."\n";
	}}
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
		
}

function lvremove(){
	
}


function affectvg(){
	$lvm_ou=new lvm_org();
	$ou=$lvm_ou->FindOuByDev($_GET["dev"]);
	$ldap=new clladp();
	$ous=$ldap->hash_get_ou(true);
	$ous[null]="{select}";
	$field=Field_array_Hash($ous,"affectToThisOu",$ou);
	$html="<H1>{AFFECT_VG}</H1>
	<input type='hidden' id='dev_org' value='{$_GET["dev"]}'>
	<p class=caption>{AFFECT_VG_EXPLAIN}</p>
	<table style='width:99%'>
	<tr>
		<td class=legend>{organization}:</td>
		<td>$field</td>
	</tr>
	<td colspan=2 align='right'><hr>". button("{edit}","lvmAffectSave();")."</td>
	</tr>
	</table>
	
	
	";

$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'system.internal.disks.php');	
}

function affectvg_save(){
	$dev=$_GET["affectVGdev"];
	$ou=$_GET["ou"];
	$lvm=new lvm_org($ou);
	$lvm->AffectDev($dev);
	}
	
?>