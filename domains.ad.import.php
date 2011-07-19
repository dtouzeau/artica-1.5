<?php

include_once(dirname(__FILE__).'/ressources/class.activedirectory.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.groups.inc');
include_once(dirname(__FILE__).'/ressources/class.cron.inc');


if(isset($_GET["popup-index"])){popup();exit;}
if(isset($_GET["status"])){main_status();exit;}
if(isset($_GET["main"])){main_panel();exit;}
if(isset($_POST["step1"])){SaveSettings();exit;}
if(isset($_GET["import"])){main_import();exit;}
if(isset($_GET["ad-settings"])){main_config();exit;}
if(isset($_GET["FindBranch"])){FindBranch();exit;}
if(isset($_GET["ad-filter"])){main_filters();exit;}
if(isset($_GET["AccountLess3Carac"])){main_filters_save();exit;}
if(isset($_GET["ad-schedule"])){main_schedule();exit;}
if(isset($_GET["enable_schedule"])){SaveSchedule();exit;}
if(isset($_POST["perform"])){perform();exit;}
if(isset($_GET["ad-logs"])){events();exit;}


	js();
	
	
function js(){

$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{import_ad_title}');
$ActiveDirectorySettings=$tpl->_ENGINE_parse_body("{ActiveDirectorySettings}");
$filters=$tpl->_ENGINE_parse_body("{filters}");
$schedule=$tpl->_ENGINE_parse_body("{schedule}");
$importation_success_scheduled=$tpl->javascript_parse_text("{importation_success_scheduled}");
$event=$tpl->javascript_parse_text("{events}");
	
	$users=new usersMenus();
	if(!$users->AsOrgAdmin){
		if(!$users->AsOrgAdmin){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
		}
	}

$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var {$prefix}timeout=0;

function {$prefix}Loadpage(){
	RTMMail('480','$page?popup-index=yes','$title');
	setTimeout(\"{$prefix}StartALL()\",900);

	}
	
function {$prefix}StartALL(){
	{$prefix}timeout={$prefix}timeout+1;
	
	if({$prefix}timeout>10){
		alert('timeout');
		return;
	}

	if(!document.getElementById('main_config_ad')){
		setTimeout(\"{$prefix}StartALL()\",900);
		return;
	}
	LoadAjax('main_config_ad','$page?main=yes&ou={$_GET["ou"]}');
	{$prefix}timeout=0;
	{$prefix}ChargeLogs();
	{$prefix}demarre(); 
	
}

function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
	if(!RTMMailOpen()){return;}
	
	if ({$prefix}tant < 10 ) {                           
	{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",2000);
      } else {
		{$prefix}tant = 0;
		{$prefix}ChargeLogs();
		{$prefix}demarre(); 
		                              
   }
}

	function {$prefix}ChargeLogs(){
		LoadAjax('services_status_ad','$page?status=yes&ou={$_GET["ou"]}');
	}
	
	function ChockAdStatus(){
	{$prefix}ChargeLogs();
	}
	

function ActiveDirectorySettings(){
	YahooWin3(700,'$page?ad-settings=yes&ou={$_GET["ou"]}','$ActiveDirectorySettings');
}

function ActiveDirectoryFiltersSettings(){
	YahooWin3(700,'$page?ad-filter=yes&ou={$_GET["ou"]}','$filters');
}

function ActiveDirectoryScheduleSettings(){
	YahooWin3(280,'$page?ad-schedule=yes&ou={$_GET["ou"]}','$schedule');
}
function ActiveDirectoryLOGS(){
	YahooWin3(720,'$page?ad-logs=yes&ou={$_GET["ou"]}','$event');
}



var X_PerformAdImport=function(obj){
	var text;
	text=obj.responseText;
	alert('$importation_success_scheduled');
}
function PerformAdImport(){
		    var XHR = new XHRConnection();
		  	XHR.appendData('perform','yes');
		    XHR.appendData('ou','{$_GET["ou"]}');
		    XHR.sendAndLoad('$page', 'POST',X_PerformAdImport); 
    		}


	function AddStep2(ou){
	    var dn_ou=document.getElementById(\"dn_ou\").value;
	    var target_group=document.getElementById(\"target_group\").value;
	    LoadAjax('main_config_ad','domains.ad.import.php?import=yes&ou='+ou+'&dn_ou='+dn_ou+'&target_group='+target_group);
	}	
	
	
	{$prefix}Loadpage();
";
	

	echo $html;
}
	
	
function popup(){
	
$page=CurrentPageName();
	

$importnow=Paragraphe("64-recycle.png","{launch}","{launch_ad_import_now}","javascript:PerformAdImport()");

	$html="
	<table style='width:100%'>
	<tr>
	<td><div id='services_status_ad'></div></td>
	<td><div id='step2'>$importnow</div></td>
	</tr>
	
	</table>
	<div id='main_config_ad'></div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function main_status(){
	$tpl=new templates();
	$ad=new wad($_GET["ou"]);
	if($ad->ldap_host==null){
		$img="64-grey.png";
		$title="{disabled}";
		$text="{waiting_settings}";
		echo $tpl->_ENGINE_parse_body(Paragraphe($img,$title,$text,''));
		exit;
	}
	
	if($ad->TestingADConnection()){
		$img="64-green.png";
		$title="{connected}";
		$text="$ad->ldap_host:$ad->ldap_port";
		
	    if($ad->suffix==null){
	    	$img="warning64.png";
	    	$text=$text."<div style=color:red>{missing} suffix</div>";
	    }else{
	    	$count=$ad->CountDeusers();
	    	if(!$count){
	    		$img="warning64.png";
	    		$text=$text."<div style=color:red>$ad->ldap_error</div>";
	    	}else{
	    		$text=$text."<div style=color:#005447;font-weight:bolder>$count {users}</div>";
	    	}
	    }
	
		

		
	}else{
	$img="64-red.png";
		$title="{failed}";
		$text="$ad->ldap_error";

		
	}
	
	echo $tpl->_ENGINE_parse_body(Paragraphe($img,$title,$text,"javascript:ChockAdStatus()"));
}


function main_panel(){
	
	
	$parameters=Paragraphe("64-network-user.png","{ActiveDirectorySettings}","{ActiveDirectorySettings_text}","javascript:ActiveDirectorySettings()");
	$filter=Paragraphe("filter-user-64.png","{filters}","{filters_ad_settings}","javascript:ActiveDirectoryFiltersSettings()");
	
	$schedule=Paragraphe("ScheduleSettings-64.png","{schedule}","{schedule_import_settings}","javascript:ActiveDirectoryScheduleSettings()");
	$ActiveDirectoryLOGS=Paragraphe("64-logs.png","{events}","{events_text}","javascript:ActiveDirectoryLOGS()");
	

	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>$parameters</td>
		<td valign='top'>$filter</td>
	</tr>
	<tr>
		<td valign='top'>$schedule</td>
		<td valign='top'>$ActiveDirectoryLOGS</td>
	</tr>
	</table>
	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function main_config(){
	
	$ldap=new wad($_GET["ou"]);
	$l=new clladp();
	$page=CurrentPageName();
	
	if($ldap->ldap_port==null){$ldap->ldap_port="389";}
	if($ldap->ldap_admin==null){$ldap->ldap_admin="Administrator";}
	
	$ldapi=new clladp();
	$hgp=$ldapi->hash_groups($_GET["ou"],1);
	
	if($_GET["LDAP_LAST_ERROR"]<>null){
		$error=RoundedLightWhite("<center><H4 style='color:#B00003'>{$_GET["LDAP_LAST_ERROR"]}</H4></center>");
	}
	
	if($ldap->anonymous==1){$DISABLED=true;}
	
	$html="$error
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/chiffre1.png'></td>
		<td valign='top'><H3>{ad_server} ({$_GET["ou"]})</H3>
		<div class=explain>{ad_server_text}</div>
		<table style='width:100%'>
		<tr>
			<td align='right' style='font-weight:bold;font-size:14px'>{server_name}:</td>
			<td align='left'>" . Field_text('server_name',$ldap->ldap_host,'width:120px;font-size:16px;padding:3px')."</td>
		</tr>
		<tr>
			<td align='right' style='font-weight:bold;font-size:14px'>{server_port}:</td>
			<td align='left'>" . Field_text('server_port',$ldap->ldap_port,'width:90px;font-size:16px;padding:3px')."</td>
		</tr>		
		</table>
		</td>
	</tr>
	</table>
	<table style='width:100%'>
	<tr><td colspan=3><hr></td></tr>
	<tr>
		<td width=1% valign='top'><img src='img/chiffre2.png'></td>
		<td valign='top'><H3>{admin_dn}</H3>
		<div class=explain>{admin_dn_text}</div>
		<table style='width:100%'>
		<tr>
			<td align='right' style='font-weight:bold;font-size:14px' nowrap>{anonymous_access}:</td>
			<td align='left'>" . Field_checkbox("anonymous",1,$ldap->anonymous,"ADAnonymousCheck()")."</td>
		</tr>	
		<tr>
			<td align='right' style='font-weight:bold;font-size:14px' nowrap>{search_byous}:</td>
			<td align='left'>" . Field_checkbox("EngineByOu",1,$ldap->EngineByOu,"")."</td>
		</tr>			
		<tr>
			<td align='right' style='font-weight:bold;font-size:14px' nowrap>{administrator}:</td>
			<td align='left'>" . Field_text('administrator',$ldap->ldap_admin,'width:360px;font-size:16px;padding:3px',null,null,null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td align='right' style='font-weight:bold;font-size:14px' nowrap>{password}:</td>
			<td align='left'>" . Field_password('password',$ldap->ldap_password,'width:220px;font-size:16px;padding:3px',null,null,null,false,null,$DISABLED)."</td>
		</tr>		
		<tr>
			<td align='right' style='font-weight:bold;font-size:14px' nowrap>{suffix}:</td>
			<td align='left'>" . Field_text('suffix',"$ldap->suffix",'width:320px;font-size:16px;padding:3px',null,null,null,false,null,false)."</td>
		</tr>		
		</table>
		</td>
	</tr>
	</table>
	
<table style='width:100%'>
	<tr><td colspan=3><hr></td></tr>
	<tr>
		<td width=1% valign='top'><img src='img/chiffre3.png'></td>
		<td valign='top'><H3>{local_group}</H3>
		<div class=explain>{local_group_text}</div>
		<table style='width:100%'>
		<tr>
			<td align='right' style='font-weight:bold;font-size:12px'>{local_group}:</td>
			<td align='left'>" .Field_array_Hash($hgp,'target_group',$ad->target_group,null,null,0,"font-size:13px;padding:3px")."</td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
		</td>
	</tr>
	</table>	
	
	<table style='width:100%'>
	<tr><td><hr></td></tr>
	<tr><td align='right'>". button("{edit}","SaveADSettings();")."</td>
	</tr>
	</table>	
	<script>
		function ADAnonymousCheck(){
			if(document.getElementById(\"anonymous\").checked){
				document.getElementById(\"administrator\").disabled=true;
				document.getElementById(\"password\").disabled=true;
				
			}else{
				document.getElementById(\"administrator\").disabled=false;
				document.getElementById(\"password\").disabled=false;
			}
		}
		
		
			var X_SaveADSettings=function(obj){
		      var text;
		      text=obj.responseText;
		      ChockAdStatus();
		      YahooWin3Hide();
		      
		      	
		      }
		function SaveADSettings(){
		    var server=document.getElementById(\"server_name\").value;
		    var port=document.getElementById(\"server_port\").value;
		    var password=document.getElementById(\"password\").value;
		   		password=base64_encode(password);
		    
		    var administrator=document.getElementById(\"administrator\").value;
		    var suffix=document.getElementById(\"suffix\").value;
		    var XHR = new XHRConnection();
		    
		    if(document.getElementById(\"anonymous\").checked){
		    	XHR.appendData('anonymous',1);
			}else{
				XHR.appendData('anonymous',0);
			}
			
		    if(document.getElementById(\"EngineByOu\").checked){
		    	XHR.appendData('EngineByOu',1);
			}else{
				XHR.appendData('EngineByOu',0);
			}			
			
			
		    
		    XHR.appendData('server',server);
		    XHR.appendData('port',port);
		    XHR.appendData('password',password);
		    XHR.appendData('administrator',administrator);
		    XHR.appendData('suffix',suffix);
		    XHR.appendData('step1','yes');
		    XHR.appendData('ou','{$_GET["ou"]}');
		    XHR.appendData('target_group',document.getElementById(\"target_group\").value);
		    
		    
		    XHR.sendAndLoad('$page', 'POST',X_SaveADSettings); 
    		}
		
		
	</script>
	
	
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveSettings(){
	$ad=new wad($_POST["ou"]);
	$admin=$_POST["administrator"];
	$ad->ldap_admin=$admin;
	$ad->ldap_password=base64_decode($_POST["password"]);
	$ad->suffix=$_POST["suffix"];
	$ad->ldap_host=$_POST["server"];
	$ad->ldap_port=$_POST["port"];
	$ad->anonymous=$_POST["anonymous"];
	$ad->target_group=$_POST["target_group"];
	$ad->EngineByOu=$_POST["EngineByOu"];
	$ad->SaveToLdap();	
	
}

function FindBranch(){
	$ad=new wad($_GET["ou"]);
	$ad->FindBranchs();
	
}


function main_step2(){

	
	
	$ous=$ad->table_ou();
	$ous[null]="{select}";
	$ous["Users"]="Users";
	
	$ldap=new clladp();
	$hgp=$ldap->hash_groups($_POST["ou"],1);
	
	
	
	$html="<table style='width:100%'>
	<tr><td colspan=3><hr></td></tr>
	<tr>
		<td width=1% valign='top'><img src='img/chiffre3.png'></td>
		<td valign='top'><H3>{remote_org}</H3>
		<p class=caption>{remote_org_text}</p>
		<table style='width:100%'>
		<tr>
			<td align='right' style='font-weight:bold;font-size:12px'>{remote_org}:</td>
			<td align='left'>" .Field_array_Hash($ous,'dn_ou',$ad->dn_ou)."</td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
<table style='width:100%'>
	<tr><td colspan=3><hr></td></tr>
	<tr>
		<td width=1% valign='top'><img src='img/chiffre4.png'></td>
		<td valign='top'><H3>{local_group}</H3>
		<p class=caption>{local_group_text}</p>
		<table style='width:100%'>
		<tr>
			<td align='right' style='font-weight:bold;font-size:12px'>{local_group}:</td>
			<td align='left'>" .Field_array_Hash($hgp,'target_group',$ad->target_group)."</td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
<table style='width:100%'>
	<tr><td><hr></td></tr>
	<tr><td align='right'><input type='button' value='{perform_importation}&nbsp;&raquo;' OnClick=\"javascript:AddStep2('{$_POST["ou"]}');\"></td>
	</tr>
	</table>			";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function main_import(){
	$ad=new wad($_GET["ou"]);
	if(isset($_GET["dn_ou"])){$ad->dn_ou=$_GET["dn_ou"];}
	if(isset($_GET["target_group"])){$ad->target_group=$_GET["target_group"];}
	$ad->SaveToLdap();
	$ad->Perform_import();
		
		$html="
		<input type='hidden' id='dn_ou' value='$ad->dn_ou'>
		<input type='hidden' id='target_group' value='$ad->target_group'>
		<br>
		<center>
		<input type='button' style='margin:10px' value='&laquo;&nbsp;{restart_importation}&nbsp;&raquo;' OnClick=\"javascript:AddStep2('{$_GET["ou"]}');\">
		<br>
		" . RoundedLightGreen($ad->ldap_error)."
		<br>
		
		</center>";
		
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	}
	
function main_filters_save(){
$ad=new wad($_GET["ou"]);
$ad->sub_suffix=$_GET["sub-suffix"];
$ad->domain_filter=$_GET["domain_filter"];
$ad->AccountLess3Carac=$_GET["AccountLess3Carac"];
$ad->ImportDistributionsList=$_GET["ImportDistributionsList"];
$ad->SaveToLdap();
	
}
	
	
function main_filters(){
$ad=new wad($_GET["ou"]);
$page=CurrentPageName();
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' class=legend nowrap>{import_sub_branch}:</td>
		<td>". Field_text("sub-suffix",$ad->sub_suffix,"width:300px;font-size:12px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend nowrap>{import_ad_domain_only}:</td>
		<td>". Field_text("domain_filter",$ad->domain_filter,"width:300px;font-size:12px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>{AccountLess3Carac}:</td>
		<td>". Field_checkbox("AccountLess3Carac",1,$ad->AccountLess3Carac)."</td>
	</tr>
	<tr>
		<td valign='top' class=legend nowrap>{ImportDistributionsList}:</td>
		<td>". Field_checkbox("ImportDistributionsList",1,$ad->ImportDistributionsList)."</td>
	</tr>
	
	
	<tr>
		<td colspan=2 align='right'><hr>
			". button("{save}","SaveADFilters()")."
		</td>
	</tr>
	
	</table>
	
<script>
			var X_SaveADFilters=function(obj){
		      var text;
		      text=obj.responseText;
		      ChockAdStatus();
		      YahooWin3Hide();
			}
		function SaveADFilters(){
		    var XHR = new XHRConnection();		    
		    if(document.getElementById(\"AccountLess3Carac\").checked){
		    	XHR.appendData('AccountLess3Carac',1);
			}else{
				XHR.appendData('AccountLess3Carac',0);
			}
			
 			if(document.getElementById(\"ImportDistributionsList\").checked){
		    	XHR.appendData('ImportDistributionsList',1);
			}else{
				XHR.appendData('ImportDistributionsList',0);
			}			
		    
		    XHR.appendData('sub-suffix',document.getElementById(\"sub-suffix\").value);
		    XHR.appendData('domain_filter',document.getElementById(\"domain_filter\").value);
		    
		    XHR.appendData('ou','{$_GET["ou"]}');
		    XHR.sendAndLoad('$page', 'GET',X_SaveADFilters); 
   			}
</script>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
	
function main_schedule(){
$ad=new wad($_GET["ou"]);
$page=CurrentPageName();
$cron=new cron_macros();

while (list ($num, $val) = each ($cron->cron_defined_macros) ){
	if($num==null){continue;}
	$array[$num]=$num;
}
$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend nowrap>{enable}:</td>
		<td>". Field_checkbox("enable_schedule",1,$ad->enable_schedule)."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>{schedule}:</td>
		<td valign='top' >". Field_array_Hash($array,"schedule",$ad->schedule,null,null,0,"font-size:14px;padding:3px")."</td>
	</tr>
	
	<tr>
		<td colspan=2 align='right'><hr>
			". button("{save}","SaveADSchedule()")."
		</td>
	</tr>
	
	</table>
<script>
			var X_SaveADSchedule=function(obj){
		      var text;
		      text=obj.responseText;
		      ChockAdStatus();
		      YahooWin3Hide();
			}
		function SaveADSchedule(){
		    var XHR = new XHRConnection();		    
		    if(document.getElementById(\"enable_schedule\").checked){
		    	XHR.appendData('enable_schedule',1);
			}else{
				XHR.appendData('enable_schedule',0);
			}
		    
		    XHR.appendData('schedule',document.getElementById(\"schedule\").value);
		    XHR.appendData('ou','{$_GET["ou"]}');
		    XHR.sendAndLoad('$page', 'GET',X_SaveADSchedule); 
   			}
</script>
	
	";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function SaveSchedule(){
$ad=new wad($_GET["ou"]);
$ad->enable_schedule=$_GET["enable_schedule"];
$ad->schedule=$_GET["schedule"];
$ad->SaveToLdap();	
	
}

function perform(){
	$ouenc=$_POST["ou"];
	$sock=new sockets();
	$ouenc=base64_encode($ouenc);
	$sock->getFrameWork("cmd.php?ad-import-perform=yes&ou=$ouenc");
}

function events(){
	$ad=new wad($_GET["ou"]);
	$tpl=new templates();
	$datas=trim($ad->ParseAdlogs());
	if($datas==null){
		$datas="<center style='font-size:16px'>{error_no_datas}</center>";
	}
	
	
	echo $tpl->_ENGINE_parse_body($datas);
}


	