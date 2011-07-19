<?php

include_once('ressources/class.main_cf.inc');

$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==true){}else{die("wrong privileges");}	


if(isset($_GET["service_key"])){save_service();exit;}
if(isset($_GET["main"])){main_switch();exit;}
if(isset($_GET["script"])){echo echo_js();exit;}
if(isset($_GET["main_page"])){echo main_page();exit;}
if(isset($_GET["service-info"])){echo service_info();exit;}
if(isset($_GET["add_option_service"])){add_option_service();exit;}
if(isset($_GET["del_option_service"])){del_option_service();exit;}
if(isset($_GET["del_service"])){del_service();exit;}
if(isset($_GET["service-ssl"])){main_ssl();exit;}
if(isset($_GET["enable_smtps"])){enable_smtps();exit;}
if(isset($_GET["RebuildMaster"])){rebuild_master();exit;}




die();




function script_ssl(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ENABLE_SMTPS=$tpl->_ENGINE_parse_body('{ENABLE_SMTPS}');
	$html="
	function LoadMasterCFSSL(){
		YahooWin3(550,'$page?service-ssl=yes','master.cf (SSL)','$ENABLE_SMTPS'); 
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
	$page=CurrentPageName();
	
	$html="
	var tmpval='';
	
	
	YahooWin2(750,'$page?main_page=yes','master.cf (services)',''); 
	
	function PostfixServiceInfo(servname,service_label){
		YahooWin3(550,'$page?service-info='+servname,'master.cf ('+service_label+')',''); 
		}
		
		
	function PostfixServiceInfo2(){
		YAHOO.example.container.dialog3.hide();
		
	
	}
	
	
var x_AddOptionService=function(obj){
		text=obj.responseText;
		if(text.length>0){
			alert(text);
		}
		PostfixServiceInfo(tmpval);
      }		
      
var x_service=function(obj){
		PostfixServiceTable();
      }	      
		
	function AddOptionService(servname){
		var text=document.getElementById('AddOptionService_text').value;
		var option=prompt(text);
		if(option){
			tmpval=servname;
			var XHR = new XHRConnection();
    		XHR.appendData('add_option_service',servname);
    		XHR.appendData('option',option);
    		XHR.sendAndLoad('$page','GET',x_AddOptionService);
		
		}
	}
	
	function DelOptionService(servname,index){
			var XHR = new XHRConnection();
			tmpval=servname;
    		XHR.appendData('del_option_service',servname);
    		XHR.appendData('index',index);
    		XHR.sendAndLoad('$page','GET',x_AddOptionService);
	}
	
	
	function DeleteMasterService(servname){
			var XHR = new XHRConnection();
    		XHR.appendData('del_service',servname);
    		XHR.sendAndLoad('$page','GET',x_service);
	
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

	$master=new master_cf();
	$service_field="<input type='hidden' id='service' name='service' value='{$master->array_full[$key]["SERVICE_NAME"]}'>{$master->array_full[$key]["SERVICE_NAME"]}";
	$service=$master->array_full[$key]["SERVICE_NAME"];
	
	
	if($master->standard_services[$service]){
		$realname="{service_$service}";
		
	}
	
	$add=imgtootltip("add-18.gif",'{add}',"AddOptionService('$key')");
	if(trim($service)==null){
		$service="new";
		$service_field=Field_text('service',null,'width:100%');
		$add="&nbsp;";
		}	
	
	$tt=array("-"=>"{default}","y"=>"{yes}","n"=>"{no}");
	
	
	$type=Field_array_Hash($master->array_type,"TYPE",$master->array_full["$key"]["TYPE"]);
	
if (is_array($master->array_full[$key]["OPTIONS"])){
			while (list ($index, $opt) = each ($master->array_full[$key]["OPTIONS"]) ){
				if(trim($opt)<>null){
					$count=$count+1;
					$opt=wordwrap($opt, 50,"<br>",true);
					$options=$options."
					<tr ". CellRollOver().">
						<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
						<td><div><code>$opt</code></div></td>
						<td width=1% valign='top'>". imgtootltip("ed_delete.gif","{delete}","DelOptionService('$key',$index)")."</td>
					</tr>";
					}
				}
		}	
	
	
	$html="
	<input type='hidden' id='AddOptionService_text' value='{AddOptionService_text}'>
	<form name='FFM1master'>
	<input type='hidden' id='service_key' name='service_key' value='$key'>
	
	<H1>Postfix:&nbsp;$realname</H1>
	
	<table style='width:100%'>
	<tr>
		<td class=legend width=1%>{name}:</td>
		<td width=99% class=caption>$service_field</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend width=1%>{SERVICE_TYPE}:</td>
		<td width=99%>$type</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend width=1%>{PRIVATE}:</td>
		<td width=99%>".Field_array_Hash($tt,"PRIVATE",$master->array_full["$key"]["PRIVATE"])."</td>
		<td>".help_icon('{PRIVATE_TEXT}')."</td>
	</tr>		
		
	<tr>
		<td class=legend width=1%>{UNIPRIV}:</td>
		<td width=99%>".Field_array_Hash($tt,"UNIPRIV",$master->array_full["$key"]["UNIPRIV"])."</td>
		<td>".help_icon('{UNIPRIV_TEXT}')."</td>
	</tr>	
	<tr>
		<td class=legend width=1% nowrap>{CHROOT}:</td>
		<td width=99%>".Field_array_Hash($tt,"CHROOT",$master->array_full["$key"]["CHROOT"])."</td>
		<td>".help_icon('{CHROOT_TEXT}')."</td>
	</tr>
	<tr>
		<td class=legend width=1% nowrap>{WAKEUP}:</td>
		<td width=99%>".Field_text("WAKEUP",$master->array_full["$key"]["WAKEUP"],'width:90px')."</td>
		<td>".help_icon('{WAKEUP_TEXT}')."</td>
	</tr>
<tr>
		<td class=legend width=1% nowrap>{MAXPROC}:</td>
		<td width=99%>".Field_text("MAXPROC",$master->array_full["$key"]["MAXPROC"],'width:90px')."</td>
		<td>".help_icon('{MAXPROC_TEXT}')."</td>
	</tr>

	
	
	
	<tr>
		<td class=legend width=1%>{COMMAND}:</td>
		<td width=99%>".Field_text("COMMAND",$master->array_full["$key"]["COMMAND"],'width:100%')."</td>
		<td>&nbsp;</td>
	</tr>

	<tr><td colspan=3 align='right'><input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1master','$page',true);PostfixServiceInfo2();PostfixServiceTable();\"><hr></td></tr>
	
	<tr>
		<td class=legend width=1% valign='top'>{OPTIONS}:</td>
		<td valign='top'>
			<div id='options_service' style='border:1px solid #CCCCCC;padding:3px'>
				<table style='width:100%'>
					$options
				</table>
			</div>
		</td>
		<td valign='top'>$add</td>
		
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
}


function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="default";};
	$page=CurrentPageName();
	$array["default"]='{services_table}';
	$array["config"]='{generated_config}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('dialog2_content','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body("<div id=tablist>$html</div><br>");		
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "default":echo main_page();break;
		case "config":echo main_conf();break;
		default:echo main_page();break;
	}
	
	
}


function main_page(){
	
	$master=new master_cf();
	$class="class=legend style='font-size:12px;text-align:left' ";
	if(!is_array($master->array_services)){$master=new master_cf();}
	if(!is_array($master->array_services)){return "<H1>Fatal error !</H1>";}
	
	$table="<table style='width:100%'>
	<tr>
		<th nowrap>{name}</th>
		<th nowrap>{service_name}</th>
		<th nowrap>&nbsp;</th>
		<th nowrap>{SERVICE_TYPE}</th>
		<th nowrap>{COMMAND}</th>
		<th nowrap>{OPTIONS}</th>
		<td>". imgtootltip("add-18.gif",'{add_postfix_service}',"PostfixServiceInfo('')")."</td>
	</tr>
	";
	
	while (list ($num, $val) = each ($master->array_services) ){
		$delete=imgtootltip('ed_delete.gif','{delete}',"DeleteMasterService('{$val["KEY"]}')");
		if($master->standard_services[$val["SERVICE"]]){
			$realname="{service_{$val["SERVICE"]}}";
			$explain=help_icon("{service_{$val["SERVICE"]}_text}");
			$delete=null;
		}else{$realname="{$val["SERVICE"]}";$explain=null;}
		
		$KEY=$val["KEY"];
		
		
		if (is_array($master->array_full[$KEY]["OPTIONS"])){
			while (list ($index, $opt) = each ($master->array_full[$KEY]["OPTIONS"]) ){
				if(trim($opt)<>null){
					$count=$count+1;
					if(strlen($opt)>27){$opt=substr($opt,0,27)."...";}
					$options=$options."<div><code>$opt</cod></div>";
					}
				$opt=null;}
		}
		
		if($count==0){$options=null;}
		
		$js="OnClick=\"javascript:PostfixServiceInfo('{$val["KEY"]}','{$master->array_full[$KEY]["SERVICE_NAME"]}');\" 
		OnMouseOver=\"javascript:this.style.cursor='pointer'\" 
		OnMouseOut=\"javascript:this.style.cursor='default'\"";
		
		$table=$table."
			<tr " . CellRollOver_jaune().">
				<td nowrap valign='top' $class width=1% nowrap $js>$realname</td>	
				<td nowrap valign='top' $class width=1% nowrap $js>{$master->array_full[$KEY]["SERVICE_NAME"]}</td>
				<td valign='top' width=1%>$explain</td>
				<td valign='top' $class width=1% $js>{$master->array_full[$KEY]["TYPE"]}</td>
				<td valign='top' $class width=1% nowrap $js>{$master->array_full[$KEY]["COMMAND"]}</td>
				<td valign='top' width=90% $js>$options</td>
				<td valign='top' $class width=1%>$delete</td>
		 </tr>";
		$options=null;
		$count=0;
		
	}
	
	$table=$table."</table>";
	
	$html="<H1>master.cf</h1>".main_tabs()."
	<div style='width:100%;height:450px;overflow-y:auto'>
	$table
	</div>
	";
	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
	
	
	
	
}

function main_conf(){
	
	$master=new master_cf();
	$datas=$master->Build();
	
$html="<H1>master.cf</h1>".main_tabs()."
	<div style='text-align:right'><input type='button' value='{rebuild_configuration}&nbsp;&raquo;' OnClick=\"javascript:RebuildMaster();\"></div>
	<textarea style='width:100%;height:350px'>$datas</textarea>
	";
	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
	
	
	
}




function add_option_service(){
	$service=$_GET["add_option_service"];
	$master=new master_cf();
	if(!$master->add_command_options($service,$_GET["option"])){
		echo $master->ldap_error;
		
	}
}
function del_option_service(){
	$service=$_GET["del_option_service"];
	$index=$_GET["index"];
	$master=new master_cf();
if(!$master->del_command_options($service,$index)){
		echo $master->ldap_error;
		
	}	
	
}


function save_service(){
	$master=new master_cf();
	
	if($_GET["service"]==null){
		$_GET["service"]=$_GET["service_key"];
	}
	
	$master->edit_service($_GET["service"],$_GET["TYPE"],
	$_GET["PRIVATE"],$_GET["UNIPRIV"],
	$_GET["CHROOT"],$_GET["WAKEUP"],
	$_GET["MAXPROC"],array($_GET["COMMAND"]));
	

	
}

function del_service(){
	$service=$_GET["del_service"];
	$master=new master_cf();
	$master->delete_service($service);
	
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