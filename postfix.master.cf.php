<?php

include_once('ressources/class.main_cf.inc');

$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==true){}else{die("wrong privileges");}	




if(isset($_POST["DELETEALL"])){DELETEALL();exit;}
if(isset($_POST["COMMAND"])){COMMANDS_SAVE();exit;}
if(isset($_POST["zmd5"])){save_service();exit;}
if(isset($_GET["script"])){echo echo_js();exit;}
if(isset($_GET["main_page"])){echo main_page();exit;}
if(isset($_GET["service-info"])){echo service_info();exit;}
if(isset($_GET["add_option_service"])){add_option_service();exit;}
if(isset($_POST["del_option_service"])){del_option_service();exit;}
if(isset($_POST["del_service"])){del_service();exit;}
if(isset($_GET["service-ssl"])){main_ssl();exit;}
if(isset($_GET["enable_smtps"])){enable_smtps();exit;}
if(isset($_GET["RebuildMaster"])){rebuild_master();exit;}
if(isset($_GET["tabs"])){main_tabs();exit;}
if(isset($_GET["default"])){main_page();exit;}
if(isset($_GET["config"])){main_conf();exit;}
if(isset($_GET["commands-list"])){COMMANDS_LIST();exit;}
if(isset($_GET["mastercf-list"])){main_page_list();exit;}
if(isset($_POST["MasterCFUseDefaults"])){MasterCFUseDefaults();exit;}
while (list ($index, $opt) = each ($_REQUEST) ){
	writelogs("UNABLE TO UNSERSTAND $index= $opt","main",__FILE__,__LINE__);
}

die();




function script_ssl(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ENABLE_SMTPS=$tpl->_ENGINE_parse_body('{ENABLE_SMTPS}');
	$html="
	function LoadMasterCFSSL(){
		YahooWin3(750,'$page?service-ssl=yes','master.cf (SSL)','$ENABLE_SMTPS'); 
	}
	
var x_SaveMasterCFSSL= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadMasterCFSSL();
			}		
	
	
	function SaveMasterCFSSL(){
		var XHR = new XHRConnection();
    	XHR.appendData('enable_smtps',document.getElementById('enable_smtps').value);
    	
    	document.getElementById('smtps').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SaveMasterCFSSL);
		}	
	LoadMasterCFSSL();";	
	echo $html;
	
	
}


function main_ssl(){
	$enabled=0;
	$master=new master_cf(1);
	
	$form=Paragraphe_switch_img('{ENABLE_SMTPS}','{SMTPS_TEXT}','enable_smtps',$master->PostfixEnableMasterCfSSL,null,450);
	$page=CurrentPageName();
	$html="
	<div id='smtps'>
		<table style='width:100%'>
		<tr>
		
		<td align='left' width=99%>$form</td>
	</tr>
	<tr>
		<td align='right'><hr>". button("{save}","SaveMasterCFSSL()")."</td>
	</tr>
	</table>
	</div>";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
	
	
}

function echo_js(){
	
	if($_GET["script"]=="ssl"){
		script_ssl();
		exit;
	}
	if($_GET["hostname"]==null){$_GET["hostname"]="master";}
	$page=CurrentPageName();
	$hostname=$_GET["hostname"];
	
	$html="
	var tmpval='';
	
	
	YahooWin2(950,'$page?tabs=yes&hostname={$_GET["hostname"]}','master.cf (services)',''); 
	
	function PostfixServiceInfo(key,service_label){
		YahooWin3(550,'$page?service-info='+key+'&hostname={$_GET["hostname"]}','master.cf ('+service_label+')',''); 
		}
		

	
	function PostfixServiceTable(){
		LoadAjax('dialog2_content','$page?main=default&hostname={$_GET["hostname"]}')
	
	}
	
	
	function RebuildMaster(){
			var XHR = new XHRConnection();
    		XHR.appendData('RebuildMaster','yes');
    		XHR.sendAndLoad('$page','GET',x_service);
	
	}
	
	";
	
	
	echo $html;
	
	
	
	
}


function service_info(){
	$key=$_GET["service-info"];
	$page=CurrentPageName();
	$q=new mysql();
	$master=new master_cf(0,$_GET["hostname"]);
	$sql="SELECT * FROM master_cf WHERE zmd5='$key'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$tpl=new templates();
	$AddOptionService_text=$tpl->javascript_parse_text("{AddOptionService_text}");
	$SERVICE_NAME=$ligne["SERVICE_NAME"];
	
	$service_field="<input type='hidden' id='service' name='service' value='$SERVICE_NAME'><span style='font-size:16px;font-weight:bold'>$SERVICE_NAME</span>";
	
	
	
	if($master->standard_services[$SERVICE_NAME]){$realname="{service_$service}";}
	
	$add=imgtootltip("plus-24.png",'{add}',"AddOptionService('$key')");
	if(trim($SERVICE_NAME)==null){
		$service="new";
		$service_field=Field_text('service',null,'width:100%;font-size:14px');
		$add="&nbsp;";
		}	
	
	$tt=array("-"=>"{default}","y"=>"{yes}","n"=>"{no}");
	$type=Field_array_Hash($master->array_type,"TYPE",$ligne["TYPE"],"style:font-size:14px;padding:4px");
	
	
	$time=time();
	$html="
	<div id='$time'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend width=1% class=legend>{name}:</td>
		<td width=99% >$service_field</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend width=1% class=legend>{SERVICE_TYPE}:</td>
		<td width=99%>$type</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend width=1%>{PRIVATE}:</td>
		<td width=99%>".Field_array_Hash($tt,"PRIVATE",$ligne["PRIVATE"],";style:font-size:14px;padding:4px")."</td>
		<td>".help_icon('{PRIVATE_TEXT}')."</td>
	</tr>		
		
	<tr>
		<td class=legend width=1% class=legend>{UNIPRIV}:</td>
		<td width=99%>".Field_array_Hash($tt,"UNIPRIV",$ligne["UNIPRIV"],";style:font-size:14px;padding:4px")."</td>
		<td>".help_icon('{UNIPRIV_TEXT}')."</td>
	</tr>	
	<tr>
		<td class=legend width=1% nowrap class=legend>{CHROOT}:</td>
		<td width=99%>".Field_array_Hash($tt,"CHROOT",$ligne["UNIPRIV"],";style:font-size:14px;padding:4px")."</td>
		<td>".help_icon('{CHROOT_TEXT}')."</td>
	</tr>
	<tr>
		<td class=legend width=1% nowrap class=legend>{WAKEUP}:</td>
		<td width=99%>".Field_text("WAKEUP",$ligne["WAKEUP"],'width:90px;font-size:14px')."</td>
		<td>".help_icon('{WAKEUP_TEXT}')."</td>
	</tr>
	<tr>
		<td class=legend width=1% nowrap class=legend>{MAXPROC}:</td>
		<td width=99%>".Field_text("MAXPROC",$ligne["MAXPROC"],'width:90px;font-size:14px')."</td>
		<td>".help_icon('{MAXPROC_TEXT}')."</td>
	</tr>
	<tr><td colspan=3 align='right'><hr>". button("{apply}","SaveMasterService()")."</td></tr>
	
	<tr>
		<td valign='top' colspan=2>
			<div id='options_service' style='margin-top:10px'></div>
		</td>
		<td valign='top' style='padding-top:10px'>$add</td>
		
	</tr>
	</table>
	
	<script>
	
	function x_SaveMasterService(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		YahooWin3Hide();	
		MasterServiceSearch();
	}	

	function LoadCommands(){
		LoadAjax('options_service','$page?commands-list=yes&hostname={$_GET["hostname"]}&key=$key');
	
	}
	
	
	function SaveMasterService(){
			var XHR = new XHRConnection();
			XHR.appendData('zmd5','$key');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('service',document.getElementById('service').value);
			XHR.appendData('type',document.getElementById('TYPE').value);
			XHR.appendData('private',document.getElementById('PRIVATE').value);
			XHR.appendData('unipriv',document.getElementById('UNIPRIV').value);
			XHR.appendData('chroot',document.getElementById('CHROOT').value);
			XHR.appendData('wakeup',document.getElementById('WAKEUP').value);
			XHR.appendData('maxproc',document.getElementById('MAXPROC').value);
			AnimateDiv('$time');
			XHR.sendAndLoad('$page', 'POST',x_SaveMasterService);
		}
		
var x_AddOptionService=function(obj){
		text=obj.responseText;
		if(text.length>3){alert(text);}
		LoadCommands();
		MasterServiceSearch();
      }				
		
	function AddOptionService(){
		var option=prompt('$AddOptionService_text');
		if(option){
			var XHR = new XHRConnection();
    		XHR.appendData('zmd5','$key');
    		XHR.appendData('COMMAND',option);
    		XHR.appendData('hostname','{$_GET["hostname"]}');
    		XHR.sendAndLoad('$page','POST',x_AddOptionService);
		}
	}		
	LoadCommands();
	</script>";
	
	$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);
	
}

function DELETEALL(){
	$sql="DELETE FROM master_cf WHERE `hostname`='{$_POST["hostname"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
}

function COMMANDS_SAVE(){
	$key=$_POST["zmd5"];
	if(trim($_POST["COMMAND"])==null){return;}
	writelogs("Save new command = $key \"{$_POST["COMMAND"]}\"",__FUNCTION__,__FILE__,__LINE__);
	$master=new master_cf(0,$_POST["hostname"]);
	if(!$master->add_command_options($key,$_POST["COMMAND"])){
		echo $master->ldap_error;
		
	}	
}

function COMMANDS_LIST(){
	$key=$_GET["key"];
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$master=new master_cf(0,$_GET["hostname"]);
	$sql="SELECT * FROM master_cf WHERE zmd5='$key'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$OPTIONS=unserialize($ligne["COMMAND"]);
	
if (is_array($OPTIONS)){
			while (list ($index, $opt) = each ($OPTIONS) ){
				if(trim($opt)<>null){
					if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
					$count=$count+1;
					$opt=wordwrap($opt, 50,"<br>",true);
					$options=$options."
					<tr class=$classtr>
						
						<td><div><code style='font-size:14px;font-weight:bold'>$opt</code></div></td>
						<td width=1% valign='top'>". imgtootltip("delete-24.png","{delete}","DelOptionService('$key',$index)")."</td>
					</tr>";
					}
				}
		}	
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th nowrap colspan=3>{COMMAND}</th>
	</thead>
	<tbody class='tbody'>
		$options
	</tbody>
</table>
<script>
var x_DelOptionService=function(obj){
		text=obj.responseText;
		if(text.length>3){alert(text);}
		LoadCommands();
		MasterServiceSearch();
      }	
	
	function DelOptionService(zmd5,index){
			var XHR = new XHRConnection();
    		XHR.appendData('del_option_service',zmd5);
    		XHR.appendData('index',index);
    		XHR.appendData('hostname','{$_GET["hostname"]}');
    		XHR.sendAndLoad('$page','POST',x_DelOptionService);
	}
</script>

";	
echo $tpl->_ENGINE_parse_body($html);	
	
}


function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="default";};
	$page=CurrentPageName();
	$tpl=new templates();
	$array["default"]='{services_table}';
	$array["config"]='{generated_config}';
		while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_master_cf style='width:100%;height:770px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
			$(document).ready(function(){
				$('#main_master_cf').tabs();
			});
		</script>";	
	
		
}


function main_page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$UseDefault=$sock->GET_INFO("MasterCFUseDefaults{$_GET["hostname"]}");
	if(!is_numeric($UseDefault)){$UseDefault=1;}
	$html="
	<center style='margin-bottom:10px'>
	<table style='width:80%' class=form>
	<tbody>
		<tr>
			<td class=legend>{name}:<td>
			<td>". Field_text("name-search-master",null,"font-size:16px",null,null,null,false,"MasterServiceSearchCheck(event)")."</td>
			<td>". button("{search}","MasterServiceSearch()")."</td>
		</tr>
		<tr>
			<td class=legend>{back_to_defaults}:<td>
			<td colspan=2>". Field_checkbox("MasterCFUseDefaults", 1,$UseDefault,'MasterCFUseDefaultsCheck()')."</td>
		</tr>
	</tbody>
	</table>
	</center>
	
	<div style='width:100%;height:650px;overflow-y:auto' id='mastercf-list'></div>
	
	<script>
		function MasterServiceSearchCheck(e){
			if(checkEnter(e)){MasterServiceSearch();}
		}
	function MasterServiceSearch(){
		var se=escape(document.getElementById('name-search-master').value);
		LoadAjax('mastercf-list','$page?mastercf-list=yes&hostname={$_GET["hostname"]}&search='+se);
	
	}
	
	var x_MasterCFUseDefaultsCheck=function(obj){
		text=obj.responseText;
		if(text.length>3){alert(text);}
		MasterServiceSearch();
      }				
		
	function MasterCFUseDefaultsCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('MasterCFUseDefaults').checked){
    		XHR.appendData('MasterCFUseDefaults','1');}else{XHR.appendData('MasterCFUseDefaults','0');}
    		XHR.appendData('hostname','{$_GET["hostname"]}');
    		AnimateDiv('mastercf-list');
    		XHR.sendAndLoad('$page','POST',x_MasterCFUseDefaultsCheck);
		
	}	
	
	MasterServiceSearch();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function MasterCFUseDefaults(){
	$sock=new sockets();
	$sock->SET_INFO("MasterCFUseDefaults{$_POST["hostname"]}", $_POST["MasterCFUseDefaults"]);
	$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance={$_POST["hostname"]}");
}


function main_page_list(){
	
	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$master=new master_cf(0,$_GET["hostname"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$delete_all=$tpl->javascript_parse_text("{delete_all}");
	$table="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th nowrap>{name}</th>
		<th nowrap>&nbsp;</th>
		<th nowrap>{SERVICE_TYPE}</th>
		<th>PRIVATE</th>
		<th>UNIPRIV</th>
		<th>CHROOT</th>
		<th>WAKEUP</th>
		<th>MAXPROC</TH>
		
		<th nowrap>{COMMAND}</th>
		<th>". imgtootltip("plus-24.png",'{add_postfix_service}',"PostfixServiceInfo('')")."</th>
		<th>". imgtootltip("delete-24.png",$delete_all,"MasterCFDeleteAll('')")."</th>
		</tr>
	</thead>
	<tbody class='tbody'>";
	
	
	$sql="SELECT * FROM master_cf WHERE hostname='{$_GET["hostname"]}' AND `SERVICE_NAME` LIKE '$search'";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$KEY=$ligne["zmd5"];
		$SERVICE=$ligne["SERVICE_NAME"];
		$TYPE=$ligne["TYPE"];
		$options=array();
		$optionsTXT=null;
		$MAXPROC=$ligne["MAXPROC"];
		$PRIVATE=$ligne["PRIVATE"];
		$UNIPRIV=$ligne["UNIPRIV"];
		$CHROOT=$ligne["CHROOT"];
		$WAKEUP=$ligne["WAKEUP"];
		$color="black";
		if($master->MasterCFUseDefaults==1){$color="#CCCCCC";}
		$delete=imgtootltip('delete-24.png','{delete}',"DeleteMasterService('$KEY')");
		if($master->standard_services[$SERVICE]){
			$realname="{service_{$SERVICE}}";
			$explain=help_icon("<b>$realname</b><hr>{service_{$SERVICE}_text}");
			$delete="&nbsp;";
		}else{$realname="{$SERVICE}";$explain="&nbsp;";}
		
		$options=unserialize($ligne["COMMAND"]);
		
		$count=0;
		if (is_array($options)){
			while (list ($index, $opt) = each ($options) ){
				if(trim($opt)==null){continue;}
				if(trim($opt)=="Array"){continue;}
				if(is_array($opt)){continue;}
				$count++;
				if(strlen($opt)>27){$opt=substr($opt,0,27)."...";}
				$optionsTXT=$optionsTXT."<div><code style='font-size:12px;color:$color'>$opt</code></div>";
			}
		}else{
			$optionsTXT="<img src='img/warning-panneau-32.png'>";
		}
		
		
		if($count==0){$options="&nbsp;";}
		
		$js="OnClick=\"javascript:PostfixServiceInfo('$KEY','$SERVICE');\"";
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$edit="<a href=\"javascript:blur();\" $js style='text-decoration:underline;font-size:13px;color:$color'>";
		
		$table=$table."
			<tr class=$classtr>
				<td nowrap width=1%>$edit$SERVICE</a></td>	
				<td width=1%>$explain</td>
				<td width=1% style='font-size:14px;color:$color'><strong>$TYPE</strong></td>
				<td width=1% style='font-size:14px;color:$color' align='center'><strong>$PRIVATE</strong></td>
				<td width=1% style='font-size:14px;color:$color' align='center' ><strong>$UNIPRIV</strong></td>
				<td width=1% style='font-size:14px;color:$color' align='center'><strong>$CHROOT</strong></td>
				<td width=1% style='font-size:14px;color:$color' align='center'><strong>$WAKEUP</strong></td>
				<td width=1% style='font-size:14px;color:$color' align='center'><strong>$MAXPROC</strong></td>
				
				<td width=90%>$optionsTXT</td>
				<td width=1% colspan=2>$delete</td>
		 </tr>";
		
		
		
	}
	
	$table=$table."</table>";
	
	$html="
	
	$table
	
	
	<script>
	var x_MasterCFDeleteAll=function(obj){
		text=obj.responseText;
		if(text.length>3){alert(text);}
		MasterServiceSearch();
      }				
		
	function MasterCFDeleteAll(){
		if(confirm('$delete_all')){
			var XHR = new XHRConnection();
    		XHR.appendData('DELETEALL','yes');
    		XHR.appendData('hostname','{$_GET["hostname"]}');
    		AnimateDiv('mastercf-list');
    		XHR.sendAndLoad('$page','POST',x_MasterCFDeleteAll);
		}
	}
	
	function DeleteMasterService(zmd5){
			var XHR = new XHRConnection();
    		XHR.appendData('del_service',zmd5);
    		XHR.appendData('hostname','{$_GET["hostname"]}');
    		AnimateDiv('mastercf-list');
    		XHR.sendAndLoad('$page','POST',x_MasterCFDeleteAll);
	
	}	
	
	
	</script>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
	
	
}

function main_conf(){
	
	
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("postfix.php?mastercf=yes&instance={$_GET["hostname"]}"));
	
	
$html="
	<div style='text-align:right'><input type='button' value='{rebuild_configuration}&nbsp;&raquo;' OnClick=\"javascript:RebuildMaster();\"></div>
	<textarea style='width:100%;height:650px;overflow:auto;font-size:13px'>$datas</textarea>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}





function del_option_service(){
	$zmd5=$_POST["del_option_service"];
	$index=$_POST["index"];
	$master=new master_cf(0,$_POST["hostname"]);
	if(!$master->del_command_options($zmd5,$index)){
	echo $master->ldap_error;
		
	}	
	
}


function save_service(){
	$_POST["service"]=str_replace(" ", "", $_POST["service"]);
	$_POST["service"]=strtolower($_POST["service"]);
	$master=new master_cf(0,$_POST["hostname"]);
	if($_POST["zmd5"]==null){$_POST["zmd5"]=md5($_POST["service"].$_POST["type"].$_POST["hostname"]);}
	if($_POST["service"]==null){$_POST["service"]=$_POST["zmd5"];}
	if($_POST["private"]==null){$_POST["private"]="-";}
	if($_POST["chroot"]==null){$_POST["chroot"]="-";}
	if($_POST["maxproc"]==null){$_POST["maxproc"]="-";}
	$master->edit_service($_POST["service"],$_POST["type"],
	$_POST["private"],$_POST["unipriv"],
	$_POST["chroot"],$_POST["wakeup"],
	$_POST["maxproc"],null,$_POST["zmd5"]);
}

function del_service(){
	$zmd5=$_POST["del_service"];
	$master=new master_cf(0,$_POST["hostname"]);
	$master->delete_service($zmd5);
	
}

function enable_smtps(){
	$sock=new sockets();
	$sock->SET_INFO("PostfixEnableMasterCfSSL",$_GET["enable_smtps"]);
	$sock->getFrameWork("cmd.php?postfix-ssl=yes");
}

function rebuild_master(){
	$master=new master_cf();
	$master->master_delete_all();
}



?>