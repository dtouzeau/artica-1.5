<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ftp_user"])){Save();exit;}
	if(isset($_GET["main"])){popup_main();exit;}
	if(isset($_GET["restrictions"])){restrictions();exit;}
	if(isset($_GET["ftp_restriction_list"])){ftp_restriction_list();exit;}
	if(isset($_GET["ftp_restriction_add"])){ftp_restriction_add();exit;}
	if(isset($_GET["ftp_restriction_del"])){ftp_restriction_del();exit;}
	if(isset($_GET["enable_ftp_restrictions"])){enable_ftp_restrictions();exit;}
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{squid_ftp_user}");
	$page=CurrentPageName();
	
	
	
	$html="
		function squid_proxy_ftp_load(){
			YahooWin3('600','$page?popup=yes','$title');
		
		}
		
		var x_squid_proxy_ftp_save= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			document.getElementById('imgftpuser').src='img/ftp-user-128.png';	
			YahooWin3Hide();
			RefreshTab('squid_main_config');					
				
		}		
		
		function squid_proxy_ftp_save(){
		 	var XHR = new XHRConnection();
			XHR.appendData('ftp_user',document.getElementById('ftp_user').value);
			XHR.appendData('ftp_list_width',document.getElementById('ftp_list_width').value);
			if(document.getElementById('ftp_passive').checked){
				XHR.appendData('ftp_passive',1);}else{XHR.appendData('ftp_passive',0);
			}
			
			if(document.getElementById('ftp_sanitycheck').checked){
				XHR.appendData('ftp_sanitycheck',1);}else{XHR.appendData('ftp_sanitycheck',0);
			}
						
			if(document.getElementById('ftp_epsv').checked){
				XHR.appendData('ftp_epsv',1);}else{XHR.appendData('ftp_epsv',0);
			}

			if(document.getElementById('ftp_epsv_all').checked){
				XHR.appendData('ftp_epsv_all',1);}else{XHR.appendData('ftp_epsv_all',0);
			}	
			if(document.getElementById('ftp_telnet_protocol').checked){
				XHR.appendData('ftp_telnet_protocol',1);}else{XHR.appendData('ftp_telnet_protocol',0);
			}	
			
			
			
			
			document.getElementById('imgftpuser').src='img/wait_verybig.gif';	
			XHR.sendAndLoad('$page', 'GET',x_squid_proxy_ftp_save);
		}
		
		function disableftpepsv(){
			document.getElementById('ftp_epsv_all').disabled=true;
			document.getElementById('ftp_epsv').disabled=true;
		}
		
	squid_proxy_ftp_load();";
	
	echo $html;
	
}

function save(){
	
	$squid=new squidbee();
	$squid->FTP_PARAMS=$_GET;
	$squid->SaveToLdap();
}

function popup(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["main"]=$tpl->_ENGINE_parse_body('{main_parameters}');
	$array["restrictions"]=$tpl->_ENGINE_parse_body('{restrictions}');
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=main_squid_ftp style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_squid_ftp').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
}


function popup_main(){
	
$squid=new squidbee();



	if($squid->FTP_PARAMS["ftp_list_width"]==null){$squid->FTP_PARAMS["ftp_list_width"]=32;}
	
	if($squid->FTP_PARAMS["ftp_passive"]==null){$squid->FTP_PARAMS["ftp_passive"]=1;}	
	if($squid->FTP_PARAMS["ftp_passive"]=='yes'){$squid->FTP_PARAMS["ftp_passive"]=1;}
	if($squid->FTP_PARAMS["ftp_passive"]=='no'){$squid->FTP_PARAMS["ftp_passive"]=0;}
	
	if($squid->FTP_PARAMS["ftp_sanitycheck"]==null){$squid->FTP_PARAMS["ftp_sanitycheck"]=1;}	
	if($squid->FTP_PARAMS["ftp_sanitycheck"]=='yes'){$squid->FTP_PARAMS["ftp_sanitycheck"]=1;}
	if($squid->FTP_PARAMS["ftp_sanitycheck"]=='no'){$squid->FTP_PARAMS["ftp_sanitycheck"]="0";}

	if($squid->FTP_PARAMS["ftp_epsv"]==null){$squid->FTP_PARAMS["ftp_epsv"]="1";}
	if($squid->FTP_PARAMS["ftp_epsv"]=='yes'){$squid->FTP_PARAMS["ftp_epsv"]="1";}
	if($squid->FTP_PARAMS["ftp_epsv"]=='no'){$squid->FTP_PARAMS["ftp_epsv"]="0";}	
	
	if(!isset($squid->FTP_PARAMS["ftp_epsv_all"])){$squid->FTP_PARAMS["ftp_epsv_all"]="0";}
	if($squid->FTP_PARAMS["ftp_epsv_all"]==null){$squid->FTP_PARAMS["ftp_epsv_all"]="0";}
	if($squid->FTP_PARAMS["ftp_epsv_all"]=='yes'){$squid->FTP_PARAMS["ftp_epsv_all"]="1";}
	if($squid->FTP_PARAMS["ftp_epsv_all"]=='no'){$squid->FTP_PARAMS["ftp_epsv_all"]="0";}		

	if(!isset($squid->FTP_PARAMS["ftp_telnet_protocol"])){$squid->FTP_PARAMS["ftp_telnet_protocol"]="1";}
	if($squid->FTP_PARAMS["ftp_telnet_protocol"]==null){$squid->FTP_PARAMS["ftp_telnet_protocol"]="1";}
	if($squid->FTP_PARAMS["ftp_telnet_protocol"]=='yes'){$squid->FTP_PARAMS["ftp_telnet_protocol"]="1";}
	if($squid->FTP_PARAMS["ftp_telnet_protocol"]=='no'){$squid->FTP_PARAMS["ftp_telnet_protocol"]="0";}	
	
	
	
	
	$html="
	<div style='font-size:14px;margin:5px'>{squid_ftp_user_text}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<img src='img/ftp-user-128.png' id='imgftpuser'></td>
	<td valign='top'>
	<center style='width:100%' id='ftpuserid'>
	<table>
	<tr>
	<td colspan=2>$fiedld</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{ftp_user}:</td>
		<td>". Field_text("ftp_user",$squid->FTP_PARAMS["ftp_user"],"font-size:14px;width:190px;padding:5px")."</td>
		<td>". help_icon("{squid_ftp_user_explain}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{ftp_list_width}:</td>
		<td>". Field_text("ftp_list_width",$squid->FTP_PARAMS["ftp_list_width"],"font-size:14px;width:90px;padding:5px")."</td>
		<td>". help_icon("{ftp_list_width_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{ftp_passive}:</td>
		<td>". Field_checkbox("ftp_passive",1,$squid->FTP_PARAMS["ftp_passive"])."</td>
		<td>". help_icon("{ftp_passive_explain}")."&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{ftp_sanitycheck}:</td>
		<td>". Field_checkbox("ftp_sanitycheck",1,$squid->FTP_PARAMS["ftp_sanitycheck"])."</td>
		<td>". help_icon("{ftp_sanitycheck_explain}")."&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{ftp_epsv}:</td>
		<td>". Field_checkbox("ftp_epsv",1,$squid->FTP_PARAMS["ftp_epsv"])."</td>
		<td>". help_icon("{ftp_epsv_explain}")."&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{ftp_epsv_all}:</td>
		<td>". Field_checkbox("ftp_epsv_all",1,$squid->FTP_PARAMS["ftp_epsv_all"])."</td>
		<td>". help_icon("{ftp_epsv_all_explain}")."&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend style='font-size:13px'>{ftp_telnet_protocol}:</td>
		<td>". Field_checkbox("ftp_telnet_protocol",1,$squid->FTP_PARAMS["ftp_telnet_protocol"])."</td>
		<td>". help_icon("{ftp_telnet_protocol_explain}")."&nbsp;</td>
	</tr>	
	
	</table>
	</center>
	<div style='width:100%;text-align:right'>". button("{apply}","squid_proxy_ftp_save()")."</div>
	</td>
	</tr>
	</table>
	";
		
	if(!$squid->IS_31){
		$html=$html."\n<script>disableftpepsv()</script>";	
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function restrictions(){
	$squid=new squidbee();
	$page=CurrentPageName();
	$tpl=new templates();
	$pattern=$tpl->javascript_parse_text("{pattern}");
	$html="<div class=explain>{ftp_restrictions_explain}<p>&nbsp;</p>{SQUID_NETWORK_HELP}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{enable_ftp_restrictions}:</td>
		<td>". Field_checkbox("enable_ftp_restrictions","1",$squid->enable_ftp_restrictions,"enable_ftp_restrictions()")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>".button("{add}","ftp_restriction_add()")."</td>
	</tr>
	</table>
	<div id='ftp_restriction_list' style='width:99%;height:200px;overflow:auto;margin-top:10px'></div>
	
	<script>
		function ftp_restriction_list(){
			LoadAjax('ftp_restriction_list','$page?ftp_restriction_list=yes');
		}
		
		var x_ftp_restriction_add=function(obj){
     		var tempvalue=obj.responseText;
      		if(tempvalue.length>0){alert(tempvalue);}
			ftp_restriction_list();
		}			
		
		function ftp_restriction_add(){
			var pattern=prompt('$pattern');
			if(pattern){
				var XHR = new XHRConnection();
				XHR.appendData('ftp_restriction_add',pattern);
				document.getElementById('ftp_restriction_list').innerHTML='<center style=\"width:100%\"><img src=img/wait.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_ftp_restriction_add);
			}
		}
		
		function enable_ftp_restrictions(){
			var XHR = new XHRConnection();
			if(document.getElementById('enable_ftp_restrictions').checked){
				XHR.appendData('enable_ftp_restrictions',1);
			}else{
				XHR.appendData('enable_ftp_restrictions',0);
			}
			XHR.sendAndLoad('$page', 'GET');
		
		}
		
	function ftp_restriction_delete(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ftp_restriction_del',ID);
		document.getElementById('ftp_restriction_list').innerHTML='<center style=\"width:100%\"><img src=img/wait.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_ftp_restriction_add);		
	}		
		
	ftp_restriction_list();
	</script>
	";	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function ftp_restriction_add(){
	
	$www="FTP_RESTR:{$_GET["ftp_restriction_add"]}";
	$sql="INSERT INTO squid_white (uri,zDate,task_type) VALUES('$www',NOW(),'FTP_RESTR')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;	
		return;
	}
	$s=new squidbee();
	$s->SaveToLdap();		
	
}
function ftp_restriction_del(){
	$sql="DELETE FROM squid_white WHERE ID={$_GET["ftp_restriction_del"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error."\n\n$sql";
		return;	
	}
	
	$s=new squidbee();
	$s->SaveToLdap();
}

function ftp_restriction_list(){
	$q=new mysql();
	$sql="SELECT * FROM squid_white WHERE task_type='FTP_RESTR' ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$html="	<table class=tableView style='width:95%'>
				<thead class=thead>
				<tr>
					<th width=1% nowrap colspan=3>{networks}:</td>
				</tr>
				</thead>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		if(!preg_match("#FTP_RESTR:(.+)#",$ligne["uri"],$re)){continue;}
		$ligne["uri"]=$re[1];
		$html=$html."
		<tr class=$cl>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=99%><code style='font-size:14px'>{$ligne["uri"]}</td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","ftp_restriction_delete('{$ligne["ID"]}')")."</td>
		</tr>";
		}
		
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function enable_ftp_restrictions(){
	$s=new squidbee();
	$s->enable_ftp_restrictions=$_GET["enable_ftp_restrictions"];
	$s->SaveToLdap();
}


?>