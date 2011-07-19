<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.samba.inc');


	
	
	$user=new usersMenus();
	if($user->AsSambaAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["quotas-disk-list"])){echo disks_list();exit;}
	if(isset($_GET["manage-quotas"])){echo manage_quotas_js();exit;}
	if(isset($_GET["manage-quotas-popup"])){echo manage_quotas_popup();exit;}
	if(isset($_GET["manage-quotas-popup-add"])){echo manage_quotas_popup_add();exit;}
	if(isset($_GET["repquota"])){repquota();exit;}
	if(isset($_GET["SaveUserQuota"])){SaveUserQuota();exit;}
	if(isset($_GET["RecheckQuotasAll"])){RecheckQuotasAll();exit;}
page();


function manage_quotas_js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$dir=$_GET["mount"];
	$title=$tpl->_ENGINE_parse_body("{quota_disk}:$dir");
	$dir=urlencode($dir);
	echo "YahooWin5('785','$page?manage-quotas-popup=yes&mount=$dir','$title');";
}


function manage_quotas_popup(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$dir=$_GET["mount"];
	$dir=urlencode($dir);
	$add=$tpl->_ENGINE_parse_body("{add}");
	$html="
	
	<div id='manage-quotas-popup-list' style='width:100%;height:550px;overflow:auto'></div>
	
	<script>
		function AddQuotaUserGroup(item){
			var item_text='';
			if(!item){item='';}else{item_text=item;}
			item=escape(item);
			YahooWin6('550','$page?manage-quotas-popup-add&mount=$dir&item='+item,'$add::'+item_text+'::{$_GET["mount"]}');
		}
		
		function repquota(){
			LoadAjax('manage-quotas-popup-list','$page?repquota=yes&mount=$dir');
		}
		repquota();
		
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function manage_quotas_popup_add(){
	$page=CurrentPageName();
	$tpl=new templates();

	$member="	<tr>
		<td class=legend>{member}:</td>
		<td>". Field_text("QuotaUser",null,"font-size:14px;width:220px")."</td>
		<td width=1%><input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('SambaBrowseUsers.php?field-user=QuotaUser&prepend=yes')\"></td>
	</tr>";
	
	if(strlen($_GET["item"])>3){
		$mount=urlencode($_GET["mount"]);
		$sock=new sockets();
		$list=unserialize(base64_decode($sock->getFrameWork("cmd.php?repquota=yes&mount=$mount")));
		$USER_ARRAY=$list["USERS"][$_GET["item"]];
		
		$member="	<tr>
		<td class=legend>{member}:</td>
		<td style='font-size:14px'><strong>{$_GET["item"]}</strong></td>
		<td width=1%><input type='hidden' id='QuotaUser' value='{$_GET["item"]}'></td>
	</tr>";
		
	}
	
	if(!is_numeric($USER_ARRAY["BLOCK_SOFT"])){$USER_ARRAY["BLOCK_SOFT"]=0;}
	if(!is_numeric($USER_ARRAY["BLOCK_HARD"])){$USER_ARRAY["BLOCK_HARD"]=0;}
	if(!is_numeric($USER_ARRAY["FILE_SOFT"])){$USER_ARRAY["FILE_SOFT"]=0;}
	if(!is_numeric($USER_ARRAY["FILE_HARD"])){$USER_ARRAY["FILE_HARD"]=0;}	
	//Array ( [STATUS] => -- [BLOCK_USED] => 0 [BLOCK_SOFT] => 500000 [BLOCK_HARD] => 500000 
	//[BLOCK_GRACE] => [FILE_USED] => 3 [FILE_SOFT] => 50 [FILE_HARD] => 50 [FILE_GRACE] => ) 

	if($USER_ARRAY["BLOCK_HARD"]>0){$USER_ARRAY["BLOCK_HARD"]=$USER_ARRAY["BLOCK_HARD"]/1000;}
	if($USER_ARRAY["BLOCK_SOFT"]>0){$USER_ARRAY["BLOCK_SOFT"]=$USER_ARRAY["BLOCK_SOFT"]/1000;}
	
	for($i=0;$i<91;$i++){
		if($i<10){$t="0$i";}else{$t=$i;}
		$grash[$i]=$t;
	}
	
	$html="
	<div id='submit-quota-div'></div>
	<table style='width:100%' class=form>
	$member
	<tr>
		<td colspan=3><span style='font-size:16px'>{size}:</span></td>
	</tr>
	
	<tr>
		<td class=legend>{quota_soft}:</td>
		<td style='font-size:14px;'>". Field_text("block_quota_soft",$USER_ARRAY["BLOCK_SOFT"],"font-size:14px;width:90px")."&nbsp;MB</td>
		<td>".help_icon("{system_quota_soft_explain}")."</td>
	<tr>
		<td class=legend>{quota_hard}:</td>
		<td style='font-size:14px;'>". Field_text("block_quota_hard",$USER_ARRAY["BLOCK_HARD"],"font-size:14px;width:90px")."&nbsp;MB</td>
		<td>".help_icon("{system_quota_hard_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{grace_period}:</td>
		<td style='font-size:14px;'>". Field_array_Hash($grash,"block_grace",7,"style:font-size:14px;")."&nbsp;{days}</td>
		<td>".help_icon("{system_grace_period_explain}")."</td>
	</tr>	
	<tr>
		<td colspan=3><span style='font-size:16px'>{files}:</span></td>
	</tr>	
	<tr>
		<td class=legend>{quota_soft}:</td>
		<td style='font-size:14px;'>". Field_text("files_quota_soft",$USER_ARRAY["FILE_SOFT"],"font-size:14px;width:90px")."&nbsp;{files}</td>
		<td>".help_icon("{system_quota_soft_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{quota_hard}:</td>
		<td style='font-size:14px;'>". Field_text("files_quota_hard",$USER_ARRAY["FILE_HARD"],"font-size:14px;width:90px")."&nbsp;{files}</td>
		<td>".help_icon("{system_quota_hard_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{grace_period}:</td>
		<td style='font-size:14px;'>". Field_array_Hash($grash,"files_grace",7,"style:font-size:14px;")."&nbsp;{days}</td>
		<td>".help_icon("{system_grace_period_explain}")."</td>
	</tr>			
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveMembersQuotas()")."</td>
	</tr>
	</table>

	<script>
	var x_SaveMembersQuotas= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			document.getElementById('submit-quota-div').innerHTML='';
			repquota();
			YahooWin6Hide();
		}		
	
		
		function SaveMembersQuotas(){
				var XHR = new XHRConnection();
				var QuotaUser=document.getElementById('QuotaUser').value;
				if(QuotaUser.length<3){return;}
	      		XHR.appendData('QuotaUser',QuotaUser);
	      		XHR.appendData('mount','{$_GET["mount"]}');
	      		XHR.appendData('block_quota_soft',document.getElementById('block_quota_soft').value);
	      		XHR.appendData('block_quota_hard',document.getElementById('block_quota_hard').value);
	      		XHR.appendData('block_grace',document.getElementById('block_grace').value);
	      		
	      		XHR.appendData('files_quota_soft',document.getElementById('files_quota_soft').value);
	      		XHR.appendData('files_quota_hard',document.getElementById('files_quota_hard').value);
	      		XHR.appendData('files_grace',document.getElementById('files_grace').value);	      		
	      		
	      		XHR.appendData('SaveUserQuota','yes');
				document.getElementById('submit-quota-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_SaveMembersQuotas);	
		}	
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveUserQuota(){
	
	
	$uid=$_GET["QuotaUser"];
	$tpl=new templates();
	$sock=new sockets();
	
	if(preg_match("#(.+?):@(.+)#",$uid,$re)){$uid="{$re[1]}:{$re[2]}";}
	
	if(!preg_match("#(.+?):(.+)#",$uid)){
		writelogs("Unable to preg_match $uid",__FUNCTION__,__FILE__,__LINE__);
		echo "$uid -> False";return;
	}
	
	
	
	$block_quota_soft=$_GET["block_quota_soft"];
	$block_quota_hard=$_GET["block_quota_hard"];
	$block_quota_soft=$_GET["block_quota_soft"];
	$block_grace=$_GET["block_grace"];
	$files_quota_soft=$_GET["files_quota_soft"];
	$files_quota_hard=$_GET["files_quota_hard"];
	$files_grace=$_GET["files_grace"];
	$mount=urlencode($_GET["mount"]);
	
	if(!is_numeric($block_quota_soft)){$block_quota_soft=0;}
	if(!is_numeric($block_quota_hard)){$block_quota_hard=0;}
	if(!is_numeric($block_grace)){$block_grace=0;}
	if(!is_numeric($files_quota_soft)){$files_quota_soft=0;}
	if(!is_numeric($files_quota_hard)){$files_quota_hard=0;}
	if(!is_numeric($files_grace)){$files_grace=0;}
	
	$block_quota_soft=$block_quota_soft*1000;
	$block_quota_hard=$block_quota_hard*1000;
	
	$block_grace=$block_grace*24;
	$block_grace=$block_grace*60;
	$block_grace=$block_grace*60;
	
	$files_grace=$files_grace*24;
	$files_grace=$files_grace*60;
	$files_grace=$files_grace*60;

	$sock=new sockets();
	writelogs("->getFrameWork(cmd.php?setquota=yes&u=$uid&mount=$mount&b=$block_quota_soft&bh=$block_quota_hard&bg=...)",__FUNCTION__,__FILE__,__LINE__);
	$datas=$sock->getFrameWork("cmd.php?setquota=yes&u=$uid&mount=$mount&b=$block_quota_soft&bh=$block_quota_hard&bg=$block_grace&f=$files_quota_soft&fh=$files_quota_hard&fg=$files_grace");
	$output=unserialize(base64_decode($datas));
	echo @implode("\n",$output);
	
	
}


function page(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$RecheckQuotasAll_text=$tpl->javascript_parse_text("{RecheckQuotasAll}");
	$html="
	<div id='quotas-list' style='width:100%;height:690px;overflow:auto'></div>
	
	
	<script>
		function RefreshQuotasRoot(){
			LoadAjax('quotas-list','$page?quotas-disk-list=yes');
		}
		
		function RecheckQuotasAll(){
			if(confirm('$RecheckQuotasAll_text')){
				var XHR = new XHRConnection();
	      		XHR.appendData('RecheckQuotasAll','yes');
				XHR.sendAndLoad('$page', 'GET');	
			}	
		}

	RefreshQuotasRoot();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function disks_list(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$disks=unserialize(base64_decode($sock->getFrameWork("cmd.php?disks-quotas-list=yes")));
	$stats=unserialize(base64_decode($sock->getFrameWork("cmd.php?quotastats=yes")));
	$html="";

	
	
	
	$html=$html."
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{partitions}</th>
	<th>". imgtootltip("service-check-32.png","{recheck}","RecheckQuotasAll()")."</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	if(is_array($disks)){
		while (list ($part, $line) = each ($disks) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$upart=urlencode($part);
			$link="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?manage-quotas=yes&mount=$upart')\" style='font-size:16px;text-decoration:underline'>";
			
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/Database32.png'></td>
			<td style='font-size:16px'>$link$part</a></td>
			<td>&nbsp;</td>
			</tr>
			";
		}
	}
	
	$html=$html."</table><p>&nbsp;</p>";
	
	$html=$html."
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{status}</th>
	</tr>
</thead>
<tbody class='tbody'>";	

	while (list ($key, $line) = each ($stats) ){
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:13px'nowrap width=1% align=right><strong>$key</strong></td>
		<td style='font-size:13px'nowrap width=99%><strong>$line</strong></td>
		</tR>";	
		
	}
	$html=$html."</table>";	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function repquota(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$list=unserialize(base64_decode($sock->getFrameWork("cmd.php?repquota=yes&mount={$_GET["mount"]}")));
	
	
	$html=$html."
<div class=explain>{repquota_explain}</div>
<div style='text-align:right;margin-bottom:5px'>
<table>
<tr>
<td align='left'>". button("{add}","AddQuotaUserGroup()")."</td>
<td align='right' style='width:100%'>
". imgtootltip("refresh-32.png","{refresh}","repquota()")."
</td>
</tr>
</table>
</div>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{members}</th>
	<th>{size}:{used}</th>
	<th>{quota_soft}</th>
	<th>{quota_hard}</th>
	<th>{files}:{used}</th>
	<th>{quota_soft}</th>
	<th>{quota_hard}</th>
	</tr>
</thead>
<tbody class='tbody'>";		

	while (list ($member, $array) = each ($list["USERS"]) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$p1_text=null;
		$p2_text=null;
		$p3_text=null;
		
		$BLOCK_USED_UNIT="MB";
		$BLOCK_USED=round($array["BLOCK_USED"]/1000,2);
		if($BLOCK_USED>1000){
			$BLOCK_USED=round($BLOCK_USED/1000,2);
			$BLOCK_USED_UNIT="Gb";
		}
		
		$BLOCK_SOFT_UNIT="MB";
		$BLOCK_SOFT=$array["BLOCK_SOFT"];
		if($BLOCK_SOFT==0){
			$BLOCK_SOFT="{unlimited}";
			$BLOCK_SOFT_UNIT=null;
		}else{
			$BLOCK_SOFT=round($array["BLOCK_SOFT"]/1000,2);
			if($BLOCK_SOFT>1000){
				$BLOCK_SOFT=round($BLOCK_SOFT/1000,2);
				$BLOCK_SOFT_UNIT="Gb";
			}
			
		}
		
		$BLOCK_HARD_UNIT="MB";
		$BLOCK_HARD=$array["BLOCK_HARD"];
			if($BLOCK_HARD==0){
				$BLOCK_HARD="{unlimited}";
				$BLOCK_HARD_UNIT=null;
			}else{
				$BLOCK_HARD=round($array["BLOCK_HARD"]/1000,2);
				if($BLOCK_HARD>1000){
					$BLOCK_HARD=round($BLOCK_HARD/1000,2);
					$BLOCK_HARD_UNIT="Gb";
				}
			}	

		if(preg_match("#(.+?):(.+)#",$member,$re)){
			if($re[1]=="group"){$img="wingroup.png";}
			if($re[1]=="user"){$img="winuser.png";}
			if($re[1]=="computer"){$img="base.png";}
			$member_text=$re[2];
			$a_modify="<a href=\"javascript:blur();\" OnClick=\"javascript:AddQuotaUserGroup('$member');\" style='font-size:13px;text-decoration:underline;font-weight:bold'>";
			if($re[2]=="root"){$a_modify=null;}
		}
			
		$color_file="black";
		$color_block="black";

		if($array["FILE_SOFT"]>0){
			if($array["FILE_HARD"]==$array["FILE_USED"]){$color_file="#BE0F0F";}
			if($array["FILE_USED"]>=$array["FILE_SOFT"]){$color_block="#F97937";}
			$p3=round($array["FILE_USED"]/$array["FILE_SOFT"],2)*100;
			$p3_text="&nbsp;($p3%)";
			if($p3>80){$color_file="#F97937";}
			if($p3>90){$color_file="#BE0F0F";}
			
		}
		
		if($array["BLOCK_SOFT"]>0){
			
			if($array["BLOCK_HARD"]==$array["BLOCK_USED"]){$color_block="#BE0F0F";}
			if($array["BLOCK_USED"]>=$array["BLOCK_SOFT"]){$color_block="#F97937";}
			$p1=round($array["BLOCK_USED"]/$array["BLOCK_SOFT"],2)*100;
			if($p1>80){$color_block="#F97937";}
			$p2=round($array["BLOCK_USED"]/$array["BLOCK_HARD"],2)*100;
			if($p2>80){$color_block="#BE0F0F";}		
			$p1_text="&nbsp;($p1%)";
			$p2_text="&nbsp;($p2%)";
		}
		

		
		
		$aligntd="align='center' valign='middle'";
$html=$html."
		<tr class=$classtr>
		<td style='font-size:13px'nowrap width=1% align=left><img src='img/$img'></td>
		<td style='font-size:13px'nowrap width=99% align=left><strong>$a_modify$member_text</a></strong></td>
		<td style='font-size:13px'nowrap width=1% $aligntd><strong style='color:$color_block'>$BLOCK_USED&nbsp;$BLOCK_USED_UNIT</strong></td>
		<td style='font-size:13px'nowrap width=1% $aligntd><strong style='color:$color_block'>$BLOCK_SOFT&nbsp;$BLOCK_SOFT_UNIT$p1_text</strong></td>
		<td style='font-size:13px'nowrap width=1% $aligntd><strong style='color:$color_block'>$BLOCK_HARD&nbsp;$BLOCK_HARD_UNIT$p2_text</strong></td>
		<td style='font-size:13px;border-left:5px solid black'nowrap width=1% $aligntd><strong style='color:$color_file'>{$array["FILE_USED"]}$p3_text</strong></td>
		<td style='font-size:13px'nowrap width=1% $aligntd><strong style='color:$color_file'>{$array["FILE_SOFT"]}</strong></td>
		<td style='font-size:13px'nowrap width=1% $aligntd><strong style='color:$color_file'>{$array["FILE_HARD"]}</strong></td>		
		</tR>";	
		
	}
	$html=$html."</table>";	
	
	echo $tpl->_ENGINE_parse_body($html);
	}

function RecheckQuotasAll(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?quotas-recheck=yes");
	
}