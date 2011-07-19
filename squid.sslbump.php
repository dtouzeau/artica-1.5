<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["parameters"])){parameters_main();exit;}
	if(isset($_GET["popup-settings"])){parameters_main();exit;}
	if(isset($_GET["EnableSSLBump"])){parameters_enable_save();exit;}
	if(isset($_GET["whitelist"])){whitelist_popup();exit;}
	if(isset($_GET["whitelist-list"])){whitelist_list();exit;}
	if(isset($_GET["website_ssl_wl"])){whitelist_add();exit;}
	if(isset($_GET["website_ssl_eble"])){whitelist_enabled();exit;}
	if(isset($_GET["website_ssl_del"])){whitelist_del();exit;}
	
	
	
	js();
	
	
function js() {

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{squid_sslbump}");
	$page=CurrentPageName();
	
	$start="SSLBUMP_START()";
	if(isset($_GET["in-front-ajax"])){$start="SSLBUMP_START2()";}
	
	$html="
	
	function SSLBUMP_START(){YahooWin2('650','$page?popup=yes','$title');}
	
	function SSLBUMP_START2(){
		$('#BodyContent').load('$page?popup=yes');}		
	

	
	$start;
	";
	
	echo $html;	
	
}

function popup(){
	$page=CurrentPageName();
	$array["parameters"]='{global_parameters}';
	$array["whitelist"]='{whitelist}';
	//$array["popup-bandwith"]='{bandwith}';
	$tpl=new templates();

	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_sslbump style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_sslbump').tabs({
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


function parameters_main(){
	
	$squid=new squidbee();
	$page=CurrentPageName();
	$sslbumb=false;
	$users=new usersMenus();
	
	if(preg_match("#^([0-9]+)\.([0-9]+)#",$users->SQUID_VERSION,$re)){
		
	    	if($re[1]>=3){if($re[2]>=1){$sslbumb=true;}}}
		
		$enableSSLBump=Paragraphe_switch_img("{activate_ssl_bump}",
	"{activate_ssl_bump_text}","EnableSSLBump",$squid->SSL_BUMP,null,450);
		
		
		
	if(!$sslbumb){
		
		$enableSSLBump=Paragraphe_switch_disable("{wrong_squid_version}: &laquo;$users->SQUID_VERSION&raquo;",
	"{wrong_squid_version_feature_text}",null,450);
		
	}
	$html="
	<div style='font-size:14px' id='sslbumpdiv'></div>
	$enableSSLBump
	<hr>
	<div style='text-align:right'>". button("{apply}","SaveEnableSSLDump()")."</div>
	
	<script>
		var x_SaveEnableSSLDump=function(obj){
     	 var tempvalue=obj.responseText;
      	if(tempvalue.length>0){alert(tempvalue);}
     	document.getElementById('EnableSSLBump').innerHTML='';
     	RefreshTab('main_config_sslbump');
     
      
      }	

	function SaveEnableSSLDump(){
		var XHR = new XHRConnection();
		if(!document.getElementById('EnableSSLBump')){return;}
		XHR.appendData('EnableSSLBump',document.getElementById('EnableSSLBump').value);
		document.getElementById('sslbumpdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveEnableSSLDump);		
	
	}
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function parameters_enable_save(){
	$squid=new squidbee();
	$squid->SSL_BUMP=$_GET["EnableSSLBump"];
	$squid->SaveToLdap();
	
}

function whitelist_popup(){
	$page=CurrentPageName();
	$html="
	<div style='font-size:14px'>{SSL_BUMP_WL}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{website}:</td>
		<td>". Field_text("website_ssl_wl",null,'font-size:13px;padding:3px',null,null,null,false,"sslBumbAddwlCheck(event)")."</td>
		<td width=1%>". help_icon("{website_ssl_wl_help}")."</td>
	</tr>
	</table>
	
	<div style='width:95%;height:250px;overflow:auto;margin:5px;padding:5px;border:1px solid #CCCCCC' id='ssl-bump-wl-id'></div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' width=1%>{search}:</td>
		<td width=99%>". Field_text("website_ssl_search",null,'font-size:13px;padding:3px',
	null,null,null,false,"sslBumbsearchCheck(event)")."</td>
	</tr>
	</table>	
	
	<script>
	
	var x_sslBumbAddwl=function(obj){
     	var tempvalue=obj.responseText;
      	if(tempvalue.length>0){alert(tempvalue);}
     	document.getElementById('ssl-bump-wl-id').innerHTML='';
     	sslBumpList();
     	}	
      
     function sslBumbAddwlCheck(e){
    	if(checkEnter(e)){sslBumbAddwl();} 
		}

	function sslBumbAddwl(){
		var XHR = new XHRConnection();
		XHR.appendData('website_ssl_wl',document.getElementById('website_ssl_wl').value);
		document.getElementById('ssl-bump-wl-id').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_sslBumbAddwl);		
		}

	function sslBumbsearchCheck(e){
		if(checkEnter(e)){sslBumpList();} 
	}

	function sslBumpList(){
		LoadAjax('ssl-bump-wl-id','$page?whitelist-list&pattern='+document.getElementById('website_ssl_search').value);
	}
	
	sslBumpList();
	</script>
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function whitelist_enabled(){
	if(preg_match("#ENABLE_([0-9]+)#",$_GET["website_ssl_eble"],$re)){
		$sql="UPDATE squid_ssl SET enabled={$_GET["enable"]} WHERE ID={$re[1]}";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
		$s=new squidbee();
		$s->SaveToLdap();		
	}
}


function whitelist_add(){
	$_GET["website_ssl_wl"]=str_replace("https://","",$_GET["website_ssl_wl"]);
	$sql="INSERT INTO squid_ssl(website_name,enabled,`type`) 
	VALUES('{$_GET["website_ssl_wl"]}',1,'ssl-bump-wl');";	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$s=new squidbee();
	$s->SaveToLdap();	
	}
function whitelist_del(){
	$sql="DELETE FROM squid_ssl WHERE ID={$_GET["website_ssl_del"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$s=new squidbee();
	$s->SaveToLdap();
}

function whitelist_list(){
	if($_GET["pattern"]==null){
		$sql="SELECT * FROM squid_ssl WHERE `type`='ssl-bump-wl' ORDER BY website_name LIMIT 0,50";
	}else{
		$_GET["pattern"]=str_replace("*","%",$_GET["pattern"]);
		$_GET["pattern"]="%".$_GET["pattern"]."%";
		$_GET["pattern"]=str_replace("%%","%",$_GET["pattern"]);
		$sql="SELECT * FROM squid_ssl WHERE `type`='ssl-bump-wl' AND website_name LIKE '{$_GET["pattern"]}' ORDER BY website_name LIMIT 0,50";
	}
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$style=CellRollOver();
	$page=CurrentPageName();
	
	$html="
	<table style='width:100%' id='ssl-bump-table'>
	<tr>
		<th colspan=2>{website_name}</th>
		<th width=1%>{enable}</th>
		<th>&nbsp;</th>
	</tr>";
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$html=$html."
		<tr $style>
		<td width=1%><img src='img/fw_bold.gif'>
		<td><code style='font-size:12px;font-weight:bold'>{$ligne["website_name"]}</td>
		<td width=1% valign='middle' align='center'>". Field_checkbox("ENABLE_{$ligne["ID"]}",1,$ligne["enabled"],"sslbumpEnableW('ENABLE_{$ligne["ID"]}')")."</td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","sslbumpDeleteW({$ligne["ID"]},this)")."</td>
		</tr>
		";	
		$c=$c+1;
	}
	
	$html=$html."</table>
	
	<script>
	var row_id=0;
	var x_sslbumpEnableW=function(obj){
     	var tempvalue=obj.responseText;
      	if(tempvalue.length>0){alert(tempvalue);}
     	}	
	
		function sslbumpEnableW(idname){
			var XHR = new XHRConnection();
			if(document.getElementById('website_ssl_wl').checked){
			XHR.appendData('enable',1);}else{XHR.appendData('enable',0);}
			XHR.appendData('website_ssl_eble',idname);
			XHR.sendAndLoad('$page', 'GET',x_sslbumpEnableW);		
		}
		
		function sslbumpEnableW(idname){
			var XHR = new XHRConnection();
			if(document.getElementById('website_ssl_wl').checked){
			XHR.appendData('enable',1);}else{XHR.appendData('enable',0);}
			XHR.appendData('website_ssl_eble',idname);
			XHR.sendAndLoad('$page', 'GET',x_sslbumpEnableW);		
		}	
		
	var x_sslbumpEnableW=function(obj){
     	var tempvalue=obj.responseText;
      	if(tempvalue.length>0){alert(tempvalue);return;}
      	sslBumpList();
      	}			

		function sslbumpDeleteW(ID,rowid){
			row_id=rowid;
			var XHR = new XHRConnection();
			XHR.appendData('website_ssl_del',ID);
			XHR.sendAndLoad('$page', 'GET',x_sslbumpEnableW);	
		}
	
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}



?>