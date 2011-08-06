<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.cron.inc');
	include_once('ressources/class.squid.bandwith.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["js"])){js();exit;}

	
	if(isset($_GET["rules"])){rules_popup();exit;}
	if(isset($_GET["rules-add"])){rules_add();exit;}
	if(isset($_GET["rules-del"])){rules_del();exit;}
	if(isset($_GET["rule_name"])){rules_save();exit;}
	if(isset($_GET["rule-id"])){rule_panel();exit;}
	
	if(isset($_GET["acl-time"])){acl_time();exit;}
	if(isset($_GET["bandacltime_ID"])){acl_time_save();exit;}
	
	if(isset($_GET["acl-net"])){acl_net_popup();exit;}
	if(isset($_GET["acl-net-popup-add"])){acl_net_add_popup();exit;}
	if(isset($_GET["acl-net-add"])){acl_net_add();exit;}
	if(isset($_GET["acl-net-del"])){acl_net_del();exit;}
	if(isset($_GET["acl-net-list"])){acl_net_list();exit;}
	if(isset($_GET["acl-net-enable"])){acl_net_enabled();exit;}
	
	if(isset($_GET["acl-www"])){acl_www_popup();exit;}
	if(isset($_GET["acl-www-list"])){acl_www_list();exit;}
	if(isset($_GET["acl-www-add"])){acl_www_add();exit;}
	if(isset($_GET["acl-www-del"])){acl_www_del();exit;}
	if(isset($_GET["acl-www-enable"])){acl_www_enabled();exit;}
	
	
	if(isset($_GET["acl-file"])){acl_file_popup();exit;}
	if(isset($_GET["acl-file-list"])){acl_file_list();exit;}
	if(isset($_GET["acl-file-add"])){acl_file_add();exit;}
	if(isset($_GET["acl-file-del"])){acl_file_del();exit;}
	if(isset($_GET["acl-file-add-all"])){acl_file_add_all();exit;}
	if(isset($_GET["acl-file-enable"])){acl_file_enabled();exit;}
	if(isset($_GET["acl-file-del-all"])){acl_file_del_all();exit;}
	
	
	
popup();

function js(){
	
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page');";
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$edit_the_rule=$tpl->_ENGINE_parse_body("{edit_the_rule}");
	$add_rule=$tpl->_ENGINE_parse_body("{add_rule}");
	$delete_rule=$tpl->_ENGINE_parse_body("{delete_rule}");
	$time_restriction=$tpl->_ENGINE_parse_body("{time_restriction}");
	$networks=$tpl->_ENGINE_parse_body("{networks}");
	$websites=$tpl->_ENGINE_parse_body("{websites}");
	$BannedMimetype=$tpl->_ENGINE_parse_body("{BannedMimetype}");
	$by_file_type=$tpl->_ENGINE_parse_body("{by_file_type}");
	$html="
	
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><div id='SquidBandLeft'></div></td>
	<td valign='top' width=100%><div id='SquidBandRight'></div></td>
	</tr>
	</table>
	<script>
	function RefreshPanel(){
		LoadAjax('SquidBandLeft','$page?rules=yes');
		if(document.getElementById('right-panel-id')){
			var IDsel=document.getElementById('right-panel-id').value;
			if(IDsel>0){SquidBandRightPanel(IDsel);}
		}
		
	}
	
	function SquidBandRightPanel(ID){
		LoadAjax('SquidBandRight','$page?rule-id='+ID);
	}
	
	function EditBandRule(ID){
		YahooWin(500,'$page?rules-add=yes&ID='+ID,'$edit_the_rule');
	}
	
	function x_DeleteBandRule(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}
				document.getElementById('SquidBandRight').innerHTML='';
				RefreshPanel();
	}	

	function BandAclTime(ID){
		YahooWin(500,'$page?acl-time=yes&ID='+ID,'$time_restriction');
	}
	
	function BandAclNet(ID){
		YahooWin(500,'$page?acl-net=yes&ID='+ID,'$networks');
	}	
	
	function BandAclWWW(ID){
		YahooWin(500,'$page?acl-www=yes&ID='+ID,'$websites');
	}		

	function BandAclMIME(ID){
		YahooWin(500,'$page?acl-mime=yes&ID='+ID,'$BannedMimetype');
	}	

	function BandAclFILE(ID){
		YahooWin(650,'$page?acl-file=yes&ID='+ID,'$by_file_type');
	}	
	
	
	
	function DeleteBandRule(ID){
		if(confirm('$delete_rule ?')){
			var XHR = new XHRConnection();
			XHR.appendData('ID',ID);
			XHR.appendData('rules-del','yes');
			document.getElementById('SquidBandRight').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			document.getElementById('SquidBandLeft').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_DeleteBandRule);	
		}
	}
	
	function AddBandRule(){
		YahooWin(500,'$page?rules-add=yes','$add_rule');
	}
	
	RefreshPanel();
	</script>
	
	
	";
	
	echo $html;
}

//databases/extentions-mime.db

function rule_panel(){
	
	$sql="SELECT * FROM squid_pools WHERE ID={$_GET["rule-id"]}";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$edit=Paragraphe("bandwith-limit-edit-64.png","{edit_the_rule}","{edit_the_rule} {$ligne["rulename"]}","javascript:EditBandRule({$_GET["rule-id"]})");
	$delete=Paragraphe("bandwith-limit-del-64.png","{delete_rule}","{delete_rule} {$ligne["rulename"]}",
	"javascript:DeleteBandRule({$_GET["rule-id"]})");
	
	$time=Paragraphe("64-planning.png","{time_restriction}","{squid_band_time_restriction_text}",
	"javascript:BandAclTime({$_GET["rule-id"]})");
	
	$net=Paragraphe("bandwith-limit-user-64.png","{networks}","{squid_band_net_restriction_text}",
	"javascript:BandAclNet({$_GET["rule-id"]})");
	
	$domains=Paragraphe("bandwith-limit-www-64.png","{websites}","{squid_band_www_restriction_text}",
	"javascript:BandAclWWW({$_GET["rule-id"]})");
	
	$file=Paragraphe("64-filetype.png","{by_file_type}","{squid_band_file_restriction_text}",
	"javascript:BandAclFILE({$_GET["rule-id"]})");	
	
	
	
	$tr[]=$edit;
	$tr[]=$delete;
	$tr[]=$net;
	$tr[]=$domains;
	$tr[]=$file;
	$tr[]=$time;
	
	
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
				
$tables[]="</table>";

	$maintext=rule_format_text($ligne["total_net"],$ligne["total_users"]);
	$s=new squid_bandwith_builder();
	$s->compile();
	$html=implode("\n",$tables)."
	<hr>
	<div class=explain>".@implode("<br>",$s->rules_explain[$_GET["rule-id"]])." {then} $maintext</div>
	<input type='hidden' id='right-panel-id' value='{$_GET["rule-id"]}'>";
	
	
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
}



function rules_popup(){
	
	$html="<table>
	<tr>
		<td>". Paragraphe("bandwith-limit-64-add.png","{add_rule}","{add_bandwith_rule}","javascript:AddBandRule()")."</td>
	</tr>
	";
	
	
	$sql="SELECT * FROM squid_pools ORDER BY ID";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	  $text=rule_format_text($ligne["total_net"],$ligne["total_users"]);
	  $html=$html."	<tr>
		<td>". Paragraphe("bandwith-limit-64.png","{$ligne["rulename"]}","$text","javascript:SquidBandRightPanel('{$ligne["ID"]}')")."</td>
	</tr>";
	}
	
	$html=$html."</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function rule_format_text($total_net,$total_users){
	$t=explode("/",$total_net);
	$delay_pool_net=$t[0];
	$delay_pool_net=$delay_pool_net*8;
	$delay_pool_net=$delay_pool_net/1000;
	
	$t=explode("/",$total_users);
	$delay_pool_limit=$t[0];
	$delay_pool_limit=$delay_pool_limit*8;
	$delay_pool_limit=$delay_pool_limit/1000;	
	
	$delay_pool_max_file=$t[1];
	$delay_pool_max_file=$delay_pool_max_file*8;
	$delay_pool_max_file=$delay_pool_max_file/1000;		
	
	return "{delay_pool_param_net} {$delay_pool_net} kb/s.<br>{delay_pool_param_user_max} {$delay_pool_max_file} kb/s 
	{delay_pool_param_user_limit} {$delay_pool_limit} kb/s";
	}


function rules_add(){
	$button_title="{add}";
	$ID=$_GET["ID"];
	$enable=1;
	
	if($ID>0){
		$sql="SELECT * FROM squid_pools WHERE ID=$ID";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$rule_name=$ligne["rulename"];
		$t=explode("/",$ligne["total_net"]);
		$delay_pool_net=$t[0];
		$delay_pool_net=$delay_pool_net*8;
		$delay_pool_net=$delay_pool_net/1000;	

		$t=explode("/",$ligne["total_users"]);
		$delay_pool_limit=$t[0];
		$delay_pool_limit=$delay_pool_limit*8;
		$delay_pool_limit=$delay_pool_limit/1000;	
		
		$delay_pool_max_file=$t[1];
		$delay_pool_max_file=$delay_pool_max_file*8;
		$delay_pool_max_file=$delay_pool_max_file/1000;			
		$button_title="{edit}";
		$enable=$ligne["enable"];
	}

	
	
	$page=CurrentPageName();
	$html="
	<div id='DelayPoolDiv'>
	<input type='hidden' id='ID' value='$ID'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap style='font-size:13px'>{rule_name}:</td>
		<td style='font-size:13px'>". 
		Field_text("rule_name",$rule_name,'width:220px;font-size:13px;padding:3px')."</td>
	</tr>	
	<tr>
		<td class=legend nowrap style='font-size:13px' nowrap>{activate_rule}:</td>
		<td style='font-size:13px'>". Field_checkbox("enable",1,$enable)."</td>
	</tr>		
	<tr>
		<td class=legend nowrap style='font-size:13px'>{delay_pool_param_net}:</td>
		<td style='font-size:13px'>". 
		Field_text("delay_pool_net",$delay_pool_net,'width:60px;font-size:13px;padding:3px')." KB/s</td>
	</tr>
	<tr>
	<td class=legend nowrap style='font-size:13px'>{delay_pool_param_user_max}:</td>	
	<td style='font-size:13px'>". 
		Field_text("delay_pool_max_file",$delay_pool_max_file,'width:60px;font-size:13px;padding:3px')." KB/s</td>
	</tr>
	<td class=legend nowrap style='font-size:13px'>{delay_pool_param_user_limit}:</td>	
	<td style='font-size:13px'>". 
		Field_text("delay_pool_limit",$delay_pool_limit,'width:60px;font-size:13px;padding:3px')." KB/s</td>
	</tr>	
	
	<tr>
		<td colspan=2 align='right'><hr>". button($button_title,"SaveSquidBand()")."</td>
	</tr>		
	</table>
	</div>
	<script>
		function x_SaveSquidBand(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}
				YahooWinHide();
				RefreshPanel();
				}			
			
		function SaveSquidBand(){
			var XHR = new XHRConnection();
			XHR.appendData('ID','$ID');
			
			if(document.getElementById('enable').checked){XHR.appendData('enable',1);}else{XHR.appendData('enable',0);}
			XHR.appendData('rule_name',document.getElementById('rule_name').value);
			XHR.appendData('delay_pool_net',document.getElementById('delay_pool_net').value);
			XHR.appendData('delay_pool_max_file',document.getElementById('delay_pool_max_file').value);
			XHR.appendData('delay_pool_limit',document.getElementById('delay_pool_limit').value);
			document.getElementById('DelayPoolDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveSquidBand);	
			}
	
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function rules_save(){
	$q=new mysql();
	$rulename=$q->mysql_real_escape_string2($_GET["rule_name"]);
	if($rulename==null){$rulename=$_GET["rule_name"];}
	$delay_pool_net=$_GET["delay_pool_net"];
	$delay_pool_net=$delay_pool_net*1000;
	$delay_pool_net=$delay_pool_net/8;
	
	$delay_pool_max_file=$_GET["delay_pool_max_file"];
	$delay_pool_max_file=$delay_pool_max_file*1000;
	$delay_pool_max_file=$delay_pool_max_file/8;
	
	$delay_pool_limit=$_GET["delay_pool_limit"];
	$delay_pool_limit=$delay_pool_limit*1000;
	$delay_pool_limit=$delay_pool_limit/8;	
	
	
	$delay_pool_net="$delay_pool_net/$delay_pool_net";
	$delay_pool_net2="$delay_pool_limit/$delay_pool_max_file";
	$sql="INSERT INTO squid_pools (rulename,total_net,total_users)
	VALUES('{$_GET["rule_name"]}','$delay_pool_net','$delay_pool_net2')";
	
	if($_GET["ID"]>0){
		$sql="UPDATE squid_pools
		SET rulename='$rulename',
		total_net='$delay_pool_net',
		total_users='$delay_pool_net2',
		enable='{$_GET["enable"]}'
		WHERE ID={$_GET["ID"]}
		";
	}
	
	$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}
function rules_del(){
	$ID=$_GET["ID"];
	$sql="DELETE FROM squid_pools WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$sql="DELETE FROM squid_pools_acls WHERE pool_id=$ID";	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}

function rule_name($ID){
	$sql="SELECT rulename FROM squid_pools WHERE ID=$ID";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	return $ligne["rulename"];
}

function acl_time(){
	$page=CurrentPageName();
	$pool_id=$_GET["ID"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='TIME_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));
	if($ACL_DATAS["enable"]==null){$ACL_DATAS["enable"]="1";}
	$d="
	
	<table style='width:100%'>";
	$cron=new cron_macros();
	while (list ($key, $day) = each ($cron->cron_squid) ){
		$value=$ACL_DATAS[$key];
		$d=$d."
		<tr>
			<td class=legend>$day</td>
			<td>". Field_checkbox($key,1,$value)."</td>
		</tr>
		";
		$js[]="if(document.getElementById('$key').checked){XHR.appendData('$key',1);}else{XHR.appendData('$key',0);}";
	}
	
	$jsc=implode("\n",$js);
	
	$d=$d."</table>";
	
	$e="<table style='with:100%'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{activate_rule}:</td>
		<td>". Field_checkbox("time_restriction_enable",1,$ACL_DATAS["enable"])."</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{from}:</td>
		<td>". Field_array_Hash($cron->cron_hours,"hour1",$ACL_DATAS["hour1"],null,null,0,'font-size:13px;padding:3px')."</td>
		<td style='font-size:13px'>:</td>
		<td>". Field_array_Hash($cron->cron_mins,"min1",$ACL_DATAS["min1"],null,null,0,'font-size:13px;padding:3px')."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{to}:</td>
		<td>". Field_array_Hash($cron->cron_hours,"hour2",$ACL_DATAS["hour2"],null,null,0,'font-size:13px;padding:3px')."</td>
		<td style='font-size:13px'>:</td>
		<td>". Field_array_Hash($cron->cron_mins,"min2",$ACL_DATAS["min2"],null,null,0,'font-size:13px;padding:3px')."</td>
	</tr>	
	</table>";
	
	
	$rulename=rule_name($pool_id);
	$html="
	<H3>$rulename::{time_restriction}</H3>
	<div id='BandAclTimeID'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$d</td>
		<td valign='top'>$e</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","BandAclTimeSave()")."</td>
	</tr>
	</table>
	
	</div>
	
	<script>
		function x_BandAclTimeSave(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}
				YahooWinHide();
				RefreshPanel();
				}			
			
		function BandAclTimeSave(){
			var XHR = new XHRConnection();
			XHR.appendData('pool_id','$pool_id');
			XHR.appendData('bandacltime_ID','{$ligne["ID"]}');
			if(document.getElementById('time_restriction_enable').checked){XHR.appendData('enable',1);}else{XHR.appendData('enable',0);}
			$jsc
			
			XHR.appendData('hour1',document.getElementById('hour1').value);
			XHR.appendData('min1',document.getElementById('min1').value);
			XHR.appendData('hour2',document.getElementById('hour2').value);
			XHR.appendData('min2',document.getElementById('min2').value);			
			document.getElementById('BandAclTimeID').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_BandAclTimeSave);	
			}		
</script>";
	
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function acl_time_save(){
	$pool_id=$_GET["pool_id"];
	$acl_time_id=$_GET["bandacltime_ID"];
	if($acl_time_id<1){
		$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='TIME_RESTRICT'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$acl_time_id=$ligne["ID"];
	}	
	
	$datas=base64_encode(serialize($_GET));
	$sql="INSERT INTO squid_pools_acls (pool_id,ACL_TYPE,ACL_DATAS,enabled) VALUES('$pool_id','TIME_RESTRICT','$datas','{$_GET["enable"]}')";
	if($acl_time_id>0){
		$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas',enabled='{$_GET["enable"]}' WHERE ID='$acl_time_id'";
	}
	
	$q=new mysql();
	$q->CheckTablesSquid();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}

function acl_net_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add_network=$tpl->_ENGINE_parse_body("{add_network}");
	
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id={$_GET["ID"]} AND ACL_TYPE='SRC_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["enabled"]==null){$ligne["enabled"]="1";}	
	
	$html="
	<div style='text-align:right'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{activate_rule}:</td>
		<td width=1%>". Field_checkbox("enable_net_rule",1,$ligne["enabled"],"BandAclNetEnable()")."</td>
		<td width=99% align='right'>".button("$add_network","BandAclNetAddPopup()")."</td>
	</tr>
	</table>
	</div>
	
	<hr>
	<div style='height:250px;overflow:auto' id='BandAclNetDiv'></div>
	
	<script>
		function BandAclNetAddPopup(){
			YahooWin2('550','$page?acl-net-popup-add=yes','$add_network');
		}
		
		function BandAclNetAddCheck(e){
			if(checkEnter(e)){
				BandAclNetAdd();
			}
		}
		
		function x_BandAclNetAdd(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}
				if(document.getElementById('BandAclNetDivAdd')){document.getElementById('BandAclNetDivAdd').innerHTML='';}
				YahooWin2Hide();
				BandAclNetRefresh();
				RefreshPanel();
				}			
			
		function BandAclNetAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('acl-net-add',document.getElementById('squid-band-acl-net-field').value);
			XHR.appendData('pool_id','{$_GET["ID"]}');
			document.getElementById('BandAclNetDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			if(document.getElementById('BandAclNetDivAdd')){
				document.getElementById('BandAclNetDivAdd').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			}
			XHR.sendAndLoad('$page', 'GET',x_BandAclNetAdd);	
			}

		function BandAclNetRefresh(){
			LoadAjax('BandAclNetDiv','$page?acl-net-list={$_GET["ID"]}');
		}
		
		function BandAclNetEnable(){
				var XHR = new XHRConnection();
			
				if(document.getElementById('enable_net_rule').checked){
					XHR.appendData('acl-net-enable',1);	
				}else{
					XHR.appendData('acl-net-enable',0);
				}
			XHR.appendData('pool_id','{$_GET["ID"]}');
			XHR.sendAndLoad('$page', 'GET',x_BandAclNetAdd);						
		}
		
		function BandAclNetDel(index){
			var XHR = new XHRConnection();
			XHR.appendData('acl-net-del',index);
			XHR.appendData('pool_id','{$_GET["ID"]}');
			document.getElementById('BandAclNetDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_BandAclNetAdd);			
		
		}
		
		
	BandAclNetRefresh();	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function acl_net_list(){
	
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id={$_GET["acl-net-list"]} AND ACL_TYPE='SRC_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	
	$html="
	<table class=tableView style='width:95%'>
		<thead class=thead>
			<tr>
				<th width=1% nowrap colspan=3>{networks}:</td>
			</tr>
		</thead>";	
	
	if(is_array($ACL_DATAS)){
	while (list ($key, $net) = each ($ACL_DATAS) ){
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		
		
		$html=$html."
		<tr class=$cl>
			<td width=1%><img src='img/22-win-nic.png'></td>
			<td width=99%><code style='font-size:14px'>$net</td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","BandAclNetDel('$key')")."</td>
		</tr>";
		}
	}
		
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	}

function acl_net_add_popup(){
	$tpl=new templates();
	$html="
	<div id='BandAclNetDivAdd'>
	<div class=explain>{SQUID_NETWORK_HELP}</div>
	". Field_text("squid-band-acl-net-field",null,"font-size:14px;padding:3px;margin:10px",null,null,null,false,"BandAclNetAddCheck(event)")."</div>
	</div>";
	echo $tpl->_ENGINE_parse_body($html);
}
function acl_net_add(){
	$pool_id=$_GET["pool_id"];
	
	$pattern=$_GET["acl-net-add"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='SRC_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	$ACL_DATAS[]=$pattern;
	$datas=base64_encode(serialize($ACL_DATAS));
	
	$sql="INSERT INTO squid_pools_acls (pool_id,ACL_TYPE,ACL_DATAS) VALUES('$pool_id','SRC_RESTRICT','$datas')";
	if($ligne["ID"]>0){
		$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}	
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}
function acl_net_del(){
	$pool_id=$_GET["pool_id"];
	
	$index=$_GET["acl-net-del"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='SRC_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	unset($ACL_DATAS[$index]);
	$datas=base64_encode(serialize($ACL_DATAS));
	
	
	if($ligne["ID"]<1){echo "???\n";exit;}
	$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}
function acl_net_enabled(){
	$pool_id=$_GET["pool_id"];
	$index=$_GET["acl-net-enable"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='SRC_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	if($ligne["ID"]<1){echo "???\n";exit;}
	$sql="UPDATE squid_pools_acls SET enabled='$index' WHERE ID='{$ligne["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}


function acl_www_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$give_internet_domain_name=$tpl->javascript_parse_text("{give_internet_domain_name}");
	
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id={$_GET["ID"]} AND ACL_TYPE='DOMAIN_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["enabled"]==null){$ligne["enabled"]="1";}	
	
	$html="
	<div style='text-align:right'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{activate_rule}:</td>
		<td width=1%>". Field_checkbox("enable_net_rule",1,$ligne["enabled"],"BandAclWWWEnable()")."</td>
		<td width=99% align='right'>".button("{add}","BandAclWWWAddPopup()")."</td>
	</tr>
	</table>
	</div>
	
	<hr>
	<div style='height:250px;overflow:auto' id='BandAclWWWDiv'></div>
	
	<script>
		function BandAclWWWAddPopup(){
			var www=prompt('$give_internet_domain_name');
			if(www){
				var XHR = new XHRConnection();
				XHR.appendData('acl-www-add',www);
				XHR.appendData('pool_id','{$_GET["ID"]}');
				document.getElementById('BandAclWWWDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_BandAclWWWAdd);
					
			}
		}

		
		function x_BandAclWWWAdd(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}
				if(document.getElementById('BandAclWWWDiv')){document.getElementById('BandAclWWWDiv').innerHTML='';}
				BandAclWWWRefresh();
				RefreshPanel();
				}			
			


		function BandAclWWWRefresh(){
			LoadAjax('BandAclWWWDiv','$page?acl-www-list={$_GET["ID"]}');
		}
		
		function BandAclWWWEnable(){
				var XHR = new XHRConnection();
			
				if(document.getElementById('enable_net_rule').checked){
					XHR.appendData('acl-www-enable',1);	
				}else{
					XHR.appendData('acl-www-enable',0);
				}
			XHR.appendData('pool_id','{$_GET["ID"]}');
			XHR.sendAndLoad('$page', 'GET',x_BandAclWWWAdd);						
		}
		
		function BandAclWWWDel(index){
			var XHR = new XHRConnection();
			XHR.appendData('acl-www-del',index);
			XHR.appendData('pool_id','{$_GET["ID"]}');
			document.getElementById('BandAclWWWDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_BandAclWWWAdd);			
		
		}
		
		
	BandAclWWWRefresh();	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function acl_www_list(){
	
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id={$_GET["acl-www-list"]} AND ACL_TYPE='DOMAIN_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	
	$html="
	<table class=tableView style='width:95%'>
		<thead class=thead>
			<tr>
				<th width=1% nowrap colspan=3>{domains}:</td>
			</tr>
		</thead>";	
	
	if(is_array($ACL_DATAS)){
	while (list ($key, $net) = each ($ACL_DATAS) ){
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		
		
		$html=$html."
		<tr class=$cl>
			<td width=1%><img src='img/domain-32.png'></td>
			<td width=99%><code style='font-size:14px'>$net</td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","BandAclWWWDel('$key')")."</td>
		</tr>";
		}
	}
		
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	}
function acl_www_add(){
	$pool_id=$_GET["pool_id"];
	$pattern=$_GET["acl-www-add"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='DOMAIN_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	$ACL_DATAS[]=$pattern;
	$datas=base64_encode(serialize($ACL_DATAS));
	
	$sql="INSERT INTO squid_pools_acls (pool_id,ACL_TYPE,ACL_DATAS) VALUES('$pool_id','DOMAIN_RESTRICT','$datas')";
	if($ligne["ID"]>0){
		$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}	
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}
function acl_www_del(){
	$pool_id=$_GET["pool_id"];
	$index=$_GET["acl-www-del"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='DOMAIN_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	unset($ACL_DATAS[$index]);
	$datas=base64_encode(serialize($ACL_DATAS));
	if($ligne["ID"]<1){echo "???\n";exit;}
	$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}
function acl_www_enabled(){
	$pool_id=$_GET["pool_id"];
	$index=$_GET["acl-www-enable"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='DOMAIN_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["ID"]<1){echo "???\n";exit;}
	$sql="UPDATE squid_pools_acls SET enabled='$index' WHERE ID='{$ligne["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}

function acl_file_popup(){
	$page=CurrentPageName();
	$global_pattern=button("{add_default_rules}","BandAclFileAddAll()");
	$tpl=new templates();
	$acl_file_type_add_popup=$tpl->_ENGINE_parse_body("{acl_file_type_add_popup}");
	
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id={$_GET["ID"]} AND ACL_TYPE='FILE_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["enabled"]==null){$ligne["enabled"]="1";}		
	
	$html="
	
	<table style='width:100%'>
	<tr>
		<td width=90% valign='top'>
			<p style='font-size:16px;padding:3px'>{add_attachment_bandwith_text}</p>
		</td>
		<td class=legend style='font-size:13px;' valign='middle' nowrap>{activate_rule}:</td>
		<td width=30% valign='middle'>". Field_checkbox("enable_net_rule",1,$ligne["enabled"],"BandAclFileEnable()")."</td>		
		<td valign='top'>". imgtootltip("plus-24.png","{add_file}","BandAclFileAddPopup()")."</td>
		<td valign='top'>". imgtootltip("delete-24.png","{delete_all}","BandAclFileDelAll()")."</td>
	</tr>
	</table>
		
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/bg_forbiden-attachmt.jpg'></td>
		<td valign='top'><div id='attachmentslist' style='width:100%;height:420px;overflow:auto'></div></td>
	</tr>
	<tr>
		<td colspan=2 align='right'>$global_pattern</td>
	</tr>
	</table>
	</div>
	<script>
	
	
		function BandAclFileAddPopup(){
			var ext=prompt('$acl_file_type_add_popup');
			if(ext){
				var XHR = new XHRConnection();
				XHR.appendData('acl-file-add',ext);
				XHR.appendData('pool_id','{$_GET["ID"]}');
				document.getElementById('attachmentslist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_BandAclFileAdd);
					
			}
		}
		
		function BandAclFileAddAll(){
			var XHR = new XHRConnection();
			XHR.appendData('acl-file-add-all','yes');
			XHR.appendData('pool_id','{$_GET["ID"]}');
			document.getElementById('attachmentslist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_BandAclFileAdd);		
		
		}

		
		function x_BandAclFileAdd(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}
				if(document.getElementById('attachmentslist')){document.getElementById('attachmentslist').innerHTML='';}
				RefreshAttachementsList();
				RefreshPanel();
				}			
			


		function RefreshAttachementsList(){
			LoadAjax('attachmentslist','$page?acl-file-list={$_GET["ID"]}');
		}
		
		function BandAclFileEnable(){
				var XHR = new XHRConnection();
			
				if(document.getElementById('enable_net_rule').checked){
					XHR.appendData('acl-file-enable',1);	
				}else{
					XHR.appendData('acl-file-enable',0);
				}
			XHR.appendData('pool_id','{$_GET["ID"]}');
			XHR.sendAndLoad('$page', 'GET',x_BandAclFileAdd);						
		}
		
		function BandAclFileDel(index){
			var XHR = new XHRConnection();
			XHR.appendData('acl-file-del',index);
			XHR.appendData('pool_id','{$_GET["ID"]}');
			document.getElementById('attachmentslist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_BandAclFileAdd);			
		
		}
		function BandAclFileDelAll(index){
			var XHR = new XHRConnection();
			XHR.appendData('acl-file-del-all','yes');
			XHR.appendData('pool_id','{$_GET["ID"]}');
			document.getElementById('attachmentslist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_BandAclFileAdd);			
		
		}
				
		
	
	
		RefreshAttachementsList();
		
		
		
	</script>
		
	
	
	";	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}	
	
	
	
function acl_file_list(){
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id={$_GET["acl-file-list"]} AND ACL_TYPE='FILE_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	
	$html="
	<table class=tableView style='width:95%'>
		<thead class=thead>
			<tr>
				<th width=1% nowrap colspan=3>{by_file_type}:</td>
			</tr>
		</thead>";	
	
	if(is_array($ACL_DATAS)){
	while (list ($key, $file) = each ($ACL_DATAS) ){
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		if($_SESSION["FILES_TYPE"][$file]==null){	
			if(is_file("img/ext/{$file}_small.gif")){
				$_SESSION["FILES_TYPE"][$file]="img/ext/{$file}_small.gif";}else{
			}
		}	

		if($_SESSION["FILES_TYPE"][$file]==null){$_SESSION["FILES_TYPE"][$file]="img/ext/ico_small.gif";}
		
		$html=$html."
		<tr class=$cl>
			<td width=1%><img src='{$_SESSION["FILES_TYPE"][$file]}'></td>
			<td width=99%><code style='font-size:14px'>$file</td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","BandAclFileDel('$key')")."</td>
		</tr>";
		}
	}
		
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}
function acl_file_add(){
	$pool_id=$_GET["pool_id"];
	$pattern=$_GET["acl-file-add"];
	
	if(strpos($pattern,',')>0){$tbl=explode(",",$pattern);}else{$tbl[]=$pattern;}
	
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='FILE_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	
	while (list ($index, $file) = each ($tbl) ){
		$ACL_DATAS[]=$file;
	}
	$datas=base64_encode(serialize($ACL_DATAS));
	
	$sql="INSERT INTO squid_pools_acls (pool_id,ACL_TYPE,ACL_DATAS) VALUES('$pool_id','FILE_RESTRICT','$datas')";
	if($ligne["ID"]>0){
		$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}	
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
}

function acl_file_add_all(){
	$pool_id=$_GET["pool_id"];
	$pattern="asf|avi|m1v|mp2|mp2v|mpa|flv|x-flv|mpe|mpeg|mpg|mpv2|wmv|dat|mkv|div|divx|ac3|dts|vob|dvr-ms|mp4|m2v|vro|rm|3gp|ram|raw|qt|mov|svcd|xdiv|m4v|m2ts|bup|3gpp|3g2|3gp2|3mm|aep|ajp|amv|amx|arf|asf|avs|d2v|d3v|dmb|dxr|
dvx|f4v|dv|bsf|rmvb|rv|aif|aifc|aiff|au|mid|midi|mp3|rmi|snd|wav|wma|vqf|aaf|ogg|srf|tga|hdf|wbmp|wmf|x3f|xbm|xpm|cr2|crw|dcr|djvu|emf|fpx|icl|icn|mrw|nef|orf|pbm|pcd|pef|pgm|plp|ppm|raf|ras|raw|rs|exe|msi|rpm|bin|dmg|cab|ace|arj|bzip2|cab|gzip|lzh|lzw|tar|tbz|gz|jar|tgz|uue|iso|7-zip|rar|alz|nrg|zip|";
	
	$tbl=explode("|",$pattern);
	
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='FILE_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	
	while (list ($index, $file) = each ($ACL_DATAS) ){
		$ff[$file]=$file;
	}
	
	while (list ($index, $file) = each ($tbl) ){
		$ff[$file]=$file;
	}	

	
	while (list ($index, $file) = each ($ff) ){
		$new[]=$file;
	}	
	
	$datas=base64_encode(serialize($new));
	
	$sql="INSERT INTO squid_pools_acls (pool_id,ACL_TYPE,ACL_DATAS) VALUES('$pool_id','FILE_RESTRICT','$datas')";
	if($ligne["ID"]>0){
		$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");	
}


function acl_file_del(){
	$pool_id=$_GET["pool_id"];
	$index=$_GET["acl-file-del"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='FILE_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	unset($ACL_DATAS[$index]);
	$datas=base64_encode(serialize($ACL_DATAS));
	if($ligne["ID"]<1){echo "???\n";exit;}
	$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");	
}
function acl_file_del_all(){
	$pool_id=$_GET["pool_id"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='FILE_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));	
	unset($ACL_DATAS);
	$datas=base64_encode(serialize($ACL_DATAS));
	if($ligne["ID"]<1){echo "???\nID={$ligne["ID"]}\n";exit;}
	$sql="UPDATE squid_pools_acls SET ACL_DATAS='$datas' WHERE ID='{$ligne["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");
	
}


function acl_file_enabled(){
	$pool_id=$_GET["pool_id"];
	$index=$_GET["acl-file-enable"];
	$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='FILE_RESTRICT'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["ID"]<1){echo "???\n";exit;}
	$sql="UPDATE squid_pools_acls SET enabled='$index' WHERE ID='{$ligne["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();$sock->getFrameWork("cmd.php?squid-reload=yes");		
}

?>