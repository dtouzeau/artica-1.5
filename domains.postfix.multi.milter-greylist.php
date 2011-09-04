<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.milter.greylist.inc');	
	
	
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["greylist-config"])){popup_settings_tab_params();exit;}
	if(isset($_GET["ChangeFormType"])){GetNewForm();exit;}	
	if(isset($_GET["popup"])){main_tabs();exit;}
	if(isset($_GET["index"])){popup();exit;}
	
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["SaveGeneralSettings"])){SaveConf();exit;}
	if(isset($_GET["add_acl"])){main_acladd();exit;}
	if(isset($_GET["explainThisacl"])){explainThisacl();exit;}
	if(isset($_GET["SaveAclID"])){SaveAclID();exit;}
	if(isset($_GET["acllist"])){echo main_acl_list();exit;}
	if(isset($_GET["DeleteAclID"])){echo DeleteAclID();exit;}
	if(isset($_GET["edit_dnsrbl"])){echo main_edit_dnsrbl();exit;}
	if(isset($_GET["dnsbllist"])){echo main_dnsrbl_list();exit;}
	if(isset($_GET["dnsrbl_subindex"])){echo SaveDnsrbl();exit;}
	if(isset($_GET["DeleteDnsbl"])){echo DeleteDnsbl();exit;}
	if(isset($_GET["BackToDNSBLDefault"])){BackToDNSBLDefault();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	

	
	
	if(isset($_GET["js"])){js();exit;}
	if(isset($_GET["dumpfile-js"])){dumpfile_js();exit;}
	if(isset($_GET["dumpfile-popup"])){dumpfile_popup();exit;}
	if(isset($_GET["popup-page"])){popup();exit;}
	if(isset($_GET["popup-settings"])){popup_settings();exit;}
	if(isset($_GET["popup-acl"])){popup_acl();exit;}
	if(isset($_GET["popup-save"])){popup_save();exit;}
	if(isset($_GET["popup-logs"])){popup_logs();exit;}	
	if(isset($_GET["popup-dumpdb"])){popup_db();exit;}
	if(isset($_GET["browse-mgrey-list"])){popup_db_list();exit;}	
	
js();	
	
	
	
function js(){
		$page=CurrentPageName();
		$tpl=new templates();
		$title=$tpl->_ENGINE_parse_body("{$_GET["hostname"]}::{APP_MILTERGREYLIST}");
		$hostname=$_GET["hostname"];
		$main_settings=$tpl->_ENGINE_parse_body('{main_settings}');
		$acl=$tpl->_ENGINE_parse_body('{acl}');		
		$html="
		var hostname_mem;
		var rulename_mem;
		
		
		function PostfixMultiMilterGreyListLoad(){
			YahooWin('820','$page?popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','{$_GET["hostname"]}::$title');
			}
			
		function PostfixMultiMilterGreymain_settings_greylist(){
			YahooWin2(\"500\",\"$page?popup-settings=yes&hostname=$hostname&ou={$_GET["ou"]}\",\"$title $main_settings\")
		}
		
		function PostfixMultiMilterGreymain_accesslist_greylist(){
			YahooWin2(\"600\",\"$page?popup-acl=yes&hostname=$hostname&ou={$_GET["ou"]}\",\"$acl $main_settings\")
		}	
		
		function PostfixMultiMilterGreymain_events_greylist(){
			YahooWin2(\"600\",\"$page?popup-logs=yes&hostname=$hostname&ou={$_GET["ou"]}\",\"$title\")
		}	

	function LoadMilterGreyListAclMulti(index){
		YahooWin2(450,'$page?add_acl=true&hostname=$hostname&ou={$_GET["ou"]}&num='+index,'$acl::N.'+index);	
		}	

		function MiltergreylistMilterStatus(){
			LoadAjax('milter-greylist-status','$page?status=true&hostname=$hostname&ou={$_GET["ou"]}')  ;
		}
			
			
	var x_AddFqdnWL=function(obj){
	      LoadAjax('list','sqlgrey.index.php?main=fqdn_list&hostname='+hostname_mem)  ;
	}
	
	var x_AddIPWL=function(obj){
	      LoadAjax('list','sqlgrey.index.php?main=ipwl_list&hostname='+hostname_mem)  ;
	}
	
	var x_MilterGreyListConfigGeneSave=function(obj){
	      var tempvalue=obj.responseText;
	      if(tempvalue.length>3){alert(tempvalue);}
	      YahooWin2Hide();
	}
	
	function MilterGreyListConfigGeneSave(){
		 var XHR = new XHRConnection();
		 XHR.appendData('hostname','$hostname');
		 XHR.appendData('ou','{$_GET["ou"]}');
		 XHR.appendData('SaveGeneralSettings','yes');
		 XHR.appendData('timeout',document.getElementById('timeout').value);
		 XHR.appendData('timeout_TIME',document.getElementById('timeout_TIME').value);
		 XHR.appendData('greylist',document.getElementById('greylist').value);
		 XHR.appendData('greylist_TIME',document.getElementById('greylist_TIME').value);
		 XHR.appendData('autowhite',document.getElementById('autowhite').value);
		 XHR.appendData('autowhite_TIME',document.getElementById('autowhite_TIME').value);
		 if(document.getElementById('lazyaw').checked){XHR.appendData('lazyaw',1);}else{XHR.appendData('lazyaw',0);}	
		 
		 
		 document.getElementById('MilterGreyListConfigGeneSaveID').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		 XHR.sendAndLoad('$page', 'GET',x_MilterGreyListConfigGeneSave);
	}
	
	function MilterGreyListMultiAclID(ID){
		YahooWin2(450,'$page?add_acl=true&num='+ID+'&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$acl N.$num');
	}
	
  	


function AddFqdnWL(hostname){
      hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('AddFqdnWL',document.getElementById('whl_server').value);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddFqdnWL);
      }
      
function DelFqdnWL(hostname,num){
 hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('DelFqdnWL',num);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddFqdnWL);      
      }
      
function AddIPWL(hostname){
      hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('AddIPWL',document.getElementById('whl_server').value);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddIPWL);
      }
      
 function DelIPWL(hostname,num){
 hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('DelIPWL',num);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddIPWL);      
      }     
      
      
function explainThisacl(){
      LoadAjax('explainc','$page?explainThisacl='+document.getElementById('type').value+'&hostname=$hostname&ou={$_GET["ou"]}')  ;
      ChangeForm();
}

var x_ChangeForm=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){
          document.getElementById('addform').innerHTML=obj.responseText;
      }
}

function ChangeForm(){
      xclass=document.getElementById('SaveAclID').value;
      xtype=document.getElementById('type').value;
      var XHR = new XHRConnection();
      XHR.appendData('ChangeFormType',xtype);
      XHR.appendData('class',xclass);
	  XHR.appendData('hostname','$hostname');
	  XHR.appendData('ou','{$_GET["ou"]}');      
      XHR.sendAndLoad('$page', 'GET',x_ChangeForm);      
      
}		

	var x_SaveMilterGreyListMultiAclID= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin2Hide();
			LoadAjax('acllist','$page?acllist=true&hostname=$hostname&ou={$_GET["ou"]}');
		}		

	function SaveMilterGreyListMultiAclID(){
		var XHR = new XHRConnection();
		XHR.appendData('hostname','$hostname');
		XHR.appendData('ou','{$_GET["ou"]}');		
		XHR.appendData('SaveAclID',document.getElementById('SaveAclID').value);
		XHR.appendData('type',document.getElementById('type').value);
		if(document.getElementById('pattern')){
			XHR.appendData('pattern',document.getElementById('pattern').value);
		}
		XHR.appendData('infos',document.getElementById('infos').value);
		XHR.appendData('mode',document.getElementById('mode').value);
		if(document.getElementById('dnsrbl_class')){
			XHR.appendData('dnsrbl_class',document.getElementById('dnsrbl_class').value);
			XHR.appendData('delay',document.getElementById('delay').value);
		}
		 
		
		document.getElementById('ffm11245').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
     	XHR.sendAndLoad('$page', 'GET',x_SaveMilterGreyListMultiAclID);
	}
			
		PostfixMultiMilterGreyListLoad()";
		
		echo $html;
		
	}
	
function main_tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["index"]='{index}';
	$array["popup-acl"]='{acls}';
	$array["popup-dumpdb"]='{MILTERGREYLIST_STATUSDUMP}';

	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_mgreylist_multi style='width:100%;height:590px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_mgreylist_multi').tabs();
			
			
			});
		</script>";		
	
}		
	
function popup(){
	$img="<img src='img/bg_sqlgrey-300.jpg'>";
	$page=CurrentPageName();
	$mg=Paragraphe('folder-mailbox-64.png','{main_settings}','{main_settings_text}',"javascript:PostfixMultiMilterGreymain_settings_greylist()",null,210,100,0,true);
	$mg1=Paragraphe('folder-logs-643.png','{events}','{events_text}',"javascript:PostfixMultiMilterGreymain_events_greylist()",null,210,100,0,true);
		

	if(is_file("ressources/logs/greylist-count-{$_GET["hostname"]}.tot")){
	$datas=unserialize(@file_get_contents("ressources/logs/greylist-count-{$_GET["hostname"]}.tot"));
	
	if(is_array($datas)){
		@unlink("ressources/logs/web/mgreylist.master1.db.png");
		$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/mgreylist.{$_GET["hostname"]}1.db.png",0);
		$gp->xdata[]=$datas["GREYLISTED"];
		$gp->ydata[]="greylisted";	
		$gp->xdata[]=$datas["WHITELISTED"];
		$gp->ydata[]="whitelisted";				
		$gp->width=310;
		$gp->height=310;
		$gp->ViewValues=false;
		$gp->x_title="{status}";
		$gp->pie();			
		$imgG="<img src='ressources/logs/web/mgreylist.{$_GET["hostname"]}1.db.png'>";
		
	}
	}	
	
	$content="
	<H2>{smtp_server}::{$_GET["hostname"]}</H2>
	<table style='width:100'>
	<tr>
		<td valign='top'>$mg</td>
		<td valign='top'>$mg1</td>
	</table>
	<center>$imgG</center>
	";
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$img
			<br>
			<div style='text-align:right;margin:5px'>". imgtootltip("refresh-24.png","{refresh}","MiltergreylistMilterStatus()")."</div>
			<div id='milter-greylist-status'></div>
		</td>
		<td valign='top'>
			
			$content
		</td>
	</tr>
	</table>
	<script>MiltergreylistMilterStatus();</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}	

function dumpfile_js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{MILTERGREYLIST_STATUSDUMP}');
	
	
	$page=CurrentPageName();
	$html="
	$content
	
	function StartMilterGreylistDumpPage(){
		YahooWin2('650','$page?dumpfile-popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$title');
		}
		

	StartMilterGreylistDumpPage();
	";
	
	echo $html;	
	
	
}


function popup_logs(){
	$sock=new sockets();
	$datas=$sock->getfile("miltergreylistlogs");
	$tpl=explode("\n",$datas);
	
	if(!is_array($tpl)){die("!!Err");}
	$tpl=array_reverse($tpl);
		while (list ($num, $ligne) = each ($tpl) ){
			if(trim($ligne==null)){continue;}
			$t=$t."<div><code style='font-size:10px'>$ligne</code></div>";
			
			
		}
		
	$html="<H1>{APP_MILTERGREYLIST} {events}</H1>
	<div style='text-align:right;margin:5px'><a href='#' OnClick=\"javascript:main_events_greylist();\">{refresh}</a></div>
	" . RoundedLightWhite("
	<div style=width:100%;height:300px;overflow:auto;'>
	$t
	</div>");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}



function popup_save(){
	$milter=new milter_greylist();
	$datas=$milter->SaveToLdap();
	
	$tpl=explode("\n",$datas);
	if(!is_array($tpl)){die("!!Err");}
	$tpl=array_reverse($tpl);
		while (list ($num, $ligne) = each ($tpl) ){
			if(trim($ligne==null)){continue;}
			$t=$t."<div><code>$ligne</code></div>";
			
			
		}
		
	$html="
	
	<div style=width:100%;height:300px;overflow:auto;'>
	$t
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_settings(){
	
	$content="<div id='greylist_config'>".greylist_config_tab()."</div>";
	$html=$content;
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function popup_settings_tab_params(){
	
	$content=greylist_config(1);
	$html=$content;
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function popup_acl(){
	$content="<div id='greylist_config'>".main_acl(1)."</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($content);	
	
}
function greylist_config_tab(){
	$tpl=new templates();	
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["greylist-config"]='{parameters}';
	$array["multiples-mx"]='{multiples_mx}';
	
	
	while (list ($num, $ligne) = each ($array) ){
			if($num=="multiples-mx"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"domains.postfix.multi.milter-greylist.mxs.php?hostname=$hostname&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname=$hostname&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");

		
	}
	
	
	echo "
	<div id=main_config_mgreylistMConfig style='width:100%;height:500x;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_mgreylistMConfig\").tabs();});
		</script>";	
	
}




function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":greylist_config();exit;break;
		case "logs":main_logs();exit;break;
		case "acl":main_acl();exit;break;
		case "conf":echo main_conf();exit;break;
		case "dnsrbl";echo main_dnsrbl();exit;
		default:
			break;
	}
	
	
}	

function main_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?milter-greylist-multi-status=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}")));
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("MILTER_GREYLIST",$ini,null,0));	
}


function greylist_config($noecho=0){
	
	$users=new usersMenus();

	$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$page=CurrentPageName();
	
	$arraytime=array(
		"m"=>"{minutes}","h"=>"{hour}","d"=>"{day}"
	);

	$html="
	<div id='MilterGreyListConfigGeneSaveID'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<table style='width:100%' class=form>	
<tr>
	<td $style align='right' nowrap valign='top' class=legend>{remove_tuple}:</strong></td>
	<td $style valign='top'>" . Field_checkbox('lazyaw',1,$pure->main_array["lazyaw"])."</td>
	<td $style valign='top'>". help_icon("{remove_tuple_text}")."</td>
</tr>	
	<tr>
	<td  align='right' nowrap valign='top' class=legend>{timeout}:</strong></td>
	<td  valign='top' colspan=2>" . Field_text('timeout',$pure->main_array["timeout"],'width:30px;font-size:14px',null,null,'{mgreylisttimeout_text}')."&nbsp;".
		Field_array_Hash($arraytime,'timeout_TIME',$pure->main_array["timeout_TIME"])."</td>
	</tr>
		

	<tr>
	<td  align='right' nowrap valign='top' class=legend>{greylist}:</strong></td>
	<td  valign='top' colspan=2>
	
	" . Field_text('greylist',$pure->main_array["greylist"],'width:30px;font-size:14px',null,null,'{greylist_text}')."&nbsp;".
		Field_array_Hash($arraytime,'greylist_TIME',$pure->main_array["greylist_TIME"])."
	
	</td>
	</tr>
	
	<tr>
	<td  align='right' nowrap valign='top' class=legend>{autowhite}:</strong></td>
	<td  valign='top' colspan=2>" . Field_text('autowhite',$pure->main_array["autowhite"],'width:30px;font-size:14px',null,null,'{autowhite_text}')."&nbsp;".
		Field_array_Hash($arraytime,'autowhite_TIME',$pure->main_array["autowhite_TIME"])."</td>
	</tr>
	<td  colspan=3 align='right' valign='top'>
	<hr>". button("{apply}","MilterGreyListConfigGeneSave()")."
	</td>
	</tr>

	</table></div>$table";
	if(isset($_GET["notab"])){$tabs=null;}
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body("$tabs$html");
	
}

function SaveConf(){
	$mil=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$sock=new sockets();
	//$sock->SET_INFO("MilterGreyListEnabled",$_GET["MilterGreyListEnabled"]);
	unset($_GET["MilterGreyListEnabled"]);
	
while (list ($num, $val) = each ($_GET) ){
		$mil->main_array[$num]=$val;
		
	}	
$mil->SaveToLdap();
	
}
function main_acladd(){
	$mil=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$page=CurrentPageName();
	$action=$mil->actionlist;
	if($_GET["num"]>-1){
		$datas=$mil->acl[$_GET["num"]];
		$ar=$mil->ParseAcl($datas);
	}

	$arrayd=Field_array_Hash(array(""=>"{select}","blacklist"=>"{blacklist}",
	'whitelist'=>"{whitelist}","greylist"=>"{greylist}"),'mode',$ar[1],null,null,0,'width:110px;font-size:13px;padding:5px');
	
	$arrayf=Field_array_Hash($action,'type',$ar[2],"explainThisacl();",null,0,'width:150px;font-size:13px;padding:5px');
	
	$html="
	<div id='ffm11245'>
	<input type='hidden' name='SaveAclID' id='SaveAclID' value='{$_GET["num"]}'>
	<table style='width:100%'>
	<tr>
	<td align='right' width=1% nowrap style='font-size:13px'><strong>{method}:</strong></td>
	<td><strong>$arrayd</strong></td>
	</tr>
	<tr>
	<td align='right' width=1% nowrap style='font-size:13px'><strong>{type}:</strong></td>
	<td><strong>$arrayf</strong></td>
	</tr>	
	<tr>
	<td align='right' width=1% nowrap>&nbsp;</td>
	<td><p class=caption id='explainc'></p></td>
	</tr>	
	</table>
	<div id='addform'>
		<table style='width:100%'>
			<tr>
				<td align='right' width=1% nowrap valign='top'><strong style='font-size:13px'>{pattern}:</strong></td>
				<td><textarea name='pattern' id='pattern' rows=3 style='width:100%;font-size:15px;font-weight:bold'>{$ar[3]}</textarea>
			</tr>
		</table>
	</div>
	<hr>
	<table style='width:100%'>
		<tr>
		<td align='right' width=1% nowrap><strong style='font-size:13px'>{infos}:</strong></td>
		<td><textarea name='infos' id='infos' rows=1 style='width:100%;font-size:15px;font-weight:bold'>{$ar[4]}</textarea>
		</tr>	
	</table>
	<table style='width:100%'>
<tr>
<td colspan=2 align='right'>
<hr>". button("{apply}","SaveMilterGreyListMultiAclID()")."
</td>
</tr>
</table>
</FORM>

	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function main_acl($noecho=0){
	
	$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td><div style='font-size:13px'>{acl_text}</div></td>
		<td valign='top'>" . imgtootltip('add-64.png',"{add_acl}","LoadMilterGreyListAclMulti(-1);")."</tD>
	</tr>
	</table>
	<hr>
	<div id='acllist' style='width:100%;height:400px;overflow:auto'></div>

	
	<script>
		LoadAjax('acllist','$page?acllist=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
		
	</script>";
	
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function main_acl_list(){
	$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$acl=$pure->acl;
	$page=CurrentPageName();
	
	
	$html="<table style='width:100%'>";
	
	
	if(is_array($acl)){
		
		while (list ($num, $val) = each ($acl) ){
			
		$a=$pure->ParseAcl($val);
		if(is_array($a)){
			$link="LoadMilterGreyListAclMulti($num)";
			
			$html=$html . "<tr " . CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=1% nowrap><strong>{$a[1]}</strong></td>
			<td width=1% nowrap><strong>".texttooltip("{{$a[2]}}",$a[4],$link)."</strong></td>
			<td><strong>" . texttooltip($a[3],$a[4],$link)."</strong></td>
			<td><strong>{$a[4]}</strong></td>
			<td width=1%>". imgtootltip('x.gif','{delete}',"LoadAjax('acllist','$page?DeleteAclID=$num&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');")."</td>
			</tr>
			
			";
		}
		
		
	}}
	$html=$html."</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);		
	
	
}
function explainThisacl(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{{$_GET["explainThisacl"]}_text}");
	}

function GetNewForm(){
	
$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
$id=$_GET["class"];
$line=$pure->ParseAcl($pure->acl[$id]);
	
	
	switch ($_GET["ChangeFormType"]) {
		case "dnsrbl":
			if(!preg_match('#delay\s+([0-9]+)([a-z])#',$line[3],$re)){
				$re[1]=15;
				$re[2]="m";
			}
			$line[3]=trim($line[3]);
			$form=
			"<table style='width:100%'>
				<tr>
					<td strong width=1% nowrap align='right'><strong>{dnsrbl_service}:</strong></td>
					<td>" . Field_array_Hash($pure->dnsrbl_class,'dnsrbl_class',null) . "</td>
				</tr>
				<tr>
					<td strong width=1% nowrap align='right'><strong>{delay}:</strong></td>
					<td>" . Field_text("delay","{$re[1]}{$re[2]}",'width:100px') . "</td>
				</tr>				
			</table>";
			
			
			break;
	
		default:$form="<table style='width:100%'>
			<tr>
				<td align='right' width=1% nowrap valign='top'><strong style='font-size:13px'> {pattern}:</strong></td>
				<td><textarea name='pattern' id='pattern' rows=2 style='width:100%;font-size:14px;font-weight:bold'>{$line[3]}</textarea>
			</tr>
		</table>";
			break;
	}
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($form);
}

function SaveAclID(){
	$tpl=new templates();
	$id=$_GET["SaveAclID"];
	$mode=$_GET["mode"];
	$type=$_GET["type"];
	$pattern=$_GET["pattern"];
	
	
	if($type=="dnsrbl"){
		$pattern="\"{$_GET["dnsrbl_class"]}\"";
		if($_GET["delay"]<>null){
			$pattern=$pattern . " delay {$_GET["delay"]}";
		}
		
	}
	
	$infos=$_GET["infos"];
	if($mode==null){$err="Error {mode}=null";}
	if($type==null){$err="Error {type}=null";}
	if($pattern==null){$err="Error {pattern}=null";}
	if($infos==null){$infos="saved Date:".date('Y-m-d H:i:s');}

	
	switch ($type) {
		case "body":$first="dacl";break;
		case "header":$first="dacl";break;
		default:$first="acl";break;
	}
	
	if($err<>null){
		echo $tpl->_ENGINE_parse_body($err);
		exit();
	}
	
	$line="$first $mode $type $pattern # $infos";
	$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	if($id>-1){
		$pure->acl[$id]=$line;
	}else{$pure->acl[]=$line;}
	$pure->SaveToLdap();
	
	
}

function DeleteAclID(){
$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
unset($pure->acl[$_GET["DeleteAclID"]]);	
$pure->SaveToLdap();
echo main_acl_list();	
}

function DeleteDnsbl(){
	$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	unset($pure->dnsrbl_array[$_GET["class"]]);
	$pure->SaveToLdap();
	echo main_dnsrbl_list();
}

function main_conf(){
$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$page=CurrentPageName();
	$g=$pure->global_conf;
	$g=nl2br($g);
	
	$html=main_tabs()."<br>
	<h5>{config}</H5>
	<div style='padding:10px'>
	<code>$g</code>
	</div>";
		
$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);		
}


function main_dnsrbl(){
	$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
$page=CurrentPageName();
	$link="YahooWin(450,'$page?edit_dnsrbl=&subline=0','{add_dnsrbl}');";
	$html=main_tabs()."<br>
	<h5>{dnsrbl}</H5>
	<p class=caption><div style='float:right'>
	
	
	<input type='button' OnClick=\"javascript:$link;\" value='{add_dnsrbl}&nbsp;&raquo;'></div>
	{dnsrbl_text}</p>
	<div id='acllist' style='width:100%;height:300px;overflow:auto'>".main_dnsrbl_list()."</div>

	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function main_dnsrbl_list(){
	$pure=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$table=$pure->dnsrbl_array;
	
$html="<table style='width:100%'>
<tr>
<td colspan=3 align='left'>" . imgtootltip('fleche-20-red.png','{back_to_default}',"LoadAjax('acllist','$page?BackToDNSBLDefault=true')")."</td>
</tr>
";

if(!is_array($table)){return $html. "</table>";}		
		while (list ($num, $cell) = each ($table) ){
			$link="YahooWin(450,'$page?edit_dnsrbl=$num&subline=0&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','{edit_dnsrbl} $num');";
			$html=$html . "
			<tr " . CellRollOver().">
				<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
				<td width=1% nowrap valign='top'><a href=\"javascript:$link\"><strong>$num</strong></a></td>
				<td width=1% nowrap valign='top'>
					<table style='width:100%'>";

					$explain=substr($cell[2],0,40)."...";
					$cell[2]=nl2br($cell[2]);
					$cell[2]=str_replace("\n","",$cell[2]);
					$cell[2]=str_replace("\r","",$cell[2]);
					$cell[2]=str_replace("'","`",$cell[2]);
					$cell[2]=htmlentities($cell[2]);

									
					
					$explain=texttooltip($explain,$cell[2],$link);
					$html=$html . 
					"<tr>
						<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
						<td width=120px nowrap valign='top'><a href=\"javascript:$link\"><strong>{$cell[0]}</strong></a></td>
						<td width=1% nowrap valign='top'><a href=\"javascript:$link\"><strong>{$cell[1]}</strong></a></td>
						<td valign='top'>$explain</td>
						<td width=1% valign='top'>". imgtootltip('x.gif','{delete}',"LoadAjax('acllist','$page?DeleteDnsbl=$num&class=$num&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');")."</td>
					</tr>
					";
				
				
				$html=$html . "
					</table>
				</td>
			</tr>";
			}
		
		
	
	$html=$html."</table>";
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
}

function main_edit_dnsrbl(){
	$mil=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$class=$_GET["edit_dnsrbl"];
	$array=$mil->dnsrbl_array[$class];
	$page=CurrentPageName();
	
	
	
	
	$mil->dnsrbl_class[null]="{select}";
	$classes=Field_text('class',$class,'width:100%');
	
	$datas=file_get_contents('ressources/dnsrbl.db');
	$datas=explode("\n",$datas);
	while (list ($index, $line) = each ($datas) ){
		if(preg_match('#([A-Z\:]+)(.+)#',$line,$re)){
			$dnsbl[$re[2]]=$re[2];
		}
		
	}
	$dnsbl[null]="{select}";
	ksort($dnsbl);
	for($i=0;$i<11;$i++){$ip["127.0.0.$i"]="127.0.0.$i";}
	
	$field_ip=Field_array_Hash($ip,'ip',$array[1]);
	$dnsbl=Field_array_Hash($dnsbl,'dnsbl',$array[0]);
	$html="
	<FORM NAME='ffm11245'>
	<input type='hidden' name='dnsrbl_subindex' value='$dnsrbl_subindex'>
	<table style='width:100%'>
	<tr>
		<td align='right' width=1% nowrap><strong>{class_name}:</strong></td>
		<td><strong>$classes</strong></td>
	</tr>
	<tr>
		<td align='right' width=1% nowrap><strong>{new_class_name}:</strong></td>
		<td><strong>" . Field_text("new_class",null,'width:100%')."</strong></td>
	</tr>	
	<tr>
		<td align='right' width=1% nowrap><strong>{dnsrbl_service}:</strong></td>
		<td><strong>$dnsbl</strong></td>
	</tr>	
	<tr>
		<td align='right' width=1% nowrap><strong>{dnsrbl_answer}:</strong></td>
		<td><strong>$field_ip</strong></td>
	</tr>	
<tr>
	<td align='right' width=1% nowrap><strong>{infos}:</strong></td>
	<td><textarea name='infos' rows=1 style='width:100%'>{$array[2]}</textarea>
	</tr>	
<tr>
<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseYahooForm('ffm11245','$page',true);LoadAjax('acllist','$page?dnsbllist=true');\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>
</FORM>

	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function SaveDnsrbl(){
	$class=$_GET["class"];
	$dnsbl=$_GET["dnsbl"];
	$infos=$_GET["infos"];
	$new_class=$_GET["new_class"];
	$ip=$_GET["ip"];
	
	if($new_class<>null){$class=$new_class;}
	
	$mil=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));

	WriteLogs("dnsrbl_array[$class][$subindex] is an array, edit array()",__FUNCTION__,__FILE__);
	WriteLogs("change {$mil->dnsrbl_array[$class][0]} to $dnsbl",__FUNCTION__,__FILE__);
	WriteLogs("change {$mil->dnsrbl_array[$class][1]} to $ip",__FUNCTION__,__FILE__);	
	WriteLogs("change {$mil->dnsrbl_array[$class][2]} to $infos",__FUNCTION__,__FILE__);		
	$mil->dnsrbl_array[$class][0]=$dnsbl;
	$mil->dnsrbl_array[$class][1]=$ip;
	$mil->dnsrbl_array[$class][2]=$infos;
	$mil->SaveToLdap();
	
}

function BackToDNSBLDefault(){
	$mil=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));	
	unset($mil->dnsrbl_array);
	$mil->SaveToLdap();
	echo main_dnsrbl_list();
}
function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html=main_tabs() . "
	<H5>{events}</H5>
	<iframe src='miltergreylist.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	
function popup_db(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if(is_file("ressources/logs/greylist-count-{$_GET["hostname"]}.tot")){
	$datas=unserialize(@file_get_contents("ressources/logs/greylist-count-{$_GET["hostname"]}.tot"));
	if(is_array($datas)){
		$table="
		<div class=explain>
		<p>{MILTERGREYLIST_STATUSDUMP_TEXT}</p>
		<table>
		<tr>
			<td style='font-size:16px'>{records}:</td>
			<td style='font-size:16px'>{$datas["RECORDS"]}</td>
			<td style='font-size:16px'>{greylisted}:</td>
			<td style='font-size:16px'>{$datas["GREYLISTED"]}</td>
			<td style='font-size:16px'>{whitelisted}:</td>
			<td style='font-size:16px'>{$datas["WHITELISTED"]}</td>	
		</tr>
		</table>	
		</div>				
		";
		
	}
	}
	
	$html="$table
	<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{pattern}:</td>
				<td>". Field_text("browse-mgreydb-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseMiltergreySearchCheck(event)")."</td>
				<td>". button("{search}","BrowseMgreySearch()")."</td>
			</tr>
			</table>
	</center>		
	<div id='browse-mgrey-list' style='width:100%;height:430px;overflow:auto;text-align:center'></div>
	
<script>
		function BrowseMiltergreySearchCheck(e){
			if(checkEnter(e)){BrowseMgreySearch();}
		}
		
		function BrowseMgreySearch(){
			var se=escape(document.getElementById('browse-mgreydb-search').value);
			LoadAjax('browse-mgrey-list','$page?browse-mgrey-list=yes&search='+se+'&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
		}
		
		
	BrowseMgreySearch();
</script>	
	
	";
	
	
echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
	
}	
function popup_db_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if($_GET["search"]<>null){
		$_GET["search"]=str_replace("*", "%", $_GET["search"]);
		$filter="AND ((mailfrom LIKE '{$_GET["search"]}') OR (mailto LIKE '{$_GET["search"]}') OR (ip_addr LIKE '{$_GET["search"]}'))";
	}
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{time}</th>
		<th width=25%>{ipaddr}</th>
		<th width=25%>{from}</th>
		<th width=25%>{to}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$maxlen=30;
		$sql="SELECT * FROM greylist_turples WHERE hostname='{$_GET["hostname"]}' $filter LIMIT 0,150";
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_events");
		
		if(!$q->ok){if(preg_match("#doesn.+?t exist#", $q->mysql_error)){$q->BuildTables();$q->ok=true;$results=$q->QUERY_SQL($sql,"artica_events");}}
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["mailfrom"]=="Summary:"){continue;}
		if(trim($ligne["ip_addr"])=="#"){continue;}	
		$time=date("Y-m-d H:i:s",$ligne["stime"]);
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$len=strlen($ligne["mailfrom"]);
		if($len>$maxlen){$ligne["mailfrom"]=substr($ligne["mailfrom"],0,$maxlen-3)."...";}
			
		$len=strlen($ligne["mailto"]);
		if($len>$maxlen){$ligne["mailto"]=substr($ligne["mailto"],0,$maxlen-3)."...";}		
		
		
	$color="black";
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:bold;color:$color' nowrap>$time</td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=1% nowrap>{$ligne["ip_addr"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50%>{$ligne["mailfrom"]}</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50>{$ligne["mailto"]}</a></td>
		</tr>
		";
	}
	
	$html=$html."</table></center>";
	echo $tpl->_ENGINE_parse_body($html);
}
	
?>