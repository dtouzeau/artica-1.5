<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.awstats.inc');
	
	
	$user=new usersMenus();
	if($user->AsAnAdministratorGeneric==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["execute"])){execute_js();exit;}
	if(isset($_GET["build-statistics"])){execute_perform();exit;}
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["AwstatsEnabled"])){Save();exit;}
	

popup();


function execute_js(){
	$tpl=new templates();
	$text=$tpl->javascript_parse_text("{$_GET["servername"]}: {build_awstats_statistics}?");
	$page=CurrentPageName();
	$html="
	
		function BuilsawstatsNow(){
			if(confirm('$text')){
				var XHR = new XHRConnection();
				XHR.appendData('servername','{$_GET["servername"]}');
				XHR.appendData('group_id','{$_GET["group_id"]}');
    			XHR.appendData('build-statistics','yes');
    			XHR.sendAndLoad('$page', 'GET');
			}
		
		}
	
	
	BuilsawstatsNow();";
	
	echo $html;
	
}

function execute_perform(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?awstats-perform={$_GET["servername"]}");
	
}

	
function popup(){
	$tpl=new templates();	
	$page=CurrentPageName();
	$array["index"]='{LoadKasperskySettings_general_title}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?zmd5={$_GET["zmd5"]}&$num=yes&servername={$_GET["servername"]}&freewebs={$_GET["freewebs"]}\"><span>$ligne</span></a></li>\n");

	}
	
	
	echo "
	<div id=main_config_awstats style='width:100%;height:500px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_awstats\").tabs();});
		</script>";		
	
}


function index(){
	$page=CurrentPageName();
	$aw=new awstats($_GET["servername"]);
	
	$LogTypeAR=array(
	"W"=>"{LogTypeW}",
	"M"=>"{LogTypeM}",
	"F"=>"{LogTypeF}",
	
	);
	
$LangAR=array(
"auto"=>"auto",
"Albanian"=>"al",
"Bosnian"=>"ba",
"Bulgarian"=>"bg",
"Catalan"=>"ca",
"Chinese(Taiwan)"=>"tw",
"Chinese(Simpliefied)"=>"cn",
"Croatian"=>"hr",
"Czech"=>"cz",
"Danish"=>"dk",
"Dutch"=>"nl",
"English"=>"en",
"Estonian"=>"et",
"Euskara"=>"eu",
"Finnish"=>"fi",
"French"=>"fr",
"Galician"=>"gl",
"German"=>"de",
"Greek"=>"gr",
"Hebrew"=>"he",
"Hungarian"=>"hu",
"Icelandic"=>"is",
"Indonesian"=>"id",
"Italian"=>"it",
"Japanese"=>"jp",
"Korean"=>"ko",
"Latvian"=>"lv",
"Norwegian(Nynorsk)"=>"nn",
"Norwegian(Bokmal)"=>"nb",
"Polish"=>"pl",
"Portuguese"=>"pt",
"Portuguese(Brazilian)"=>"br",
"Romanian"=>"ro",
"Russian"=>"ru",
"Serbian"=>"sr",
"Slovak"=>"sk",
"Slovenian"=>"si",
"Spanish"=>"es",
"Swedish"=>"se",
"Turkish"=>"tr",
"Ukrainian"=>"ua",
"Welsh"=>"cy");

krsort($LangAR);
while (list ($num, $ligne) = each ($LangAR) ){$LangAR2[$ligne]=$num;}
	
	
	
	$LogFormatAR=array(1=>"Apache",2=>"IIS {or} ISA",3=>"Webstar",4=>"Apache {or} Squid");
	
	
	
	
	$LogType_field=Field_array_Hash($LogTypeAR,"LogType",$aw->GET("LogType"),"style:font-size:13px;padding:3px");
	$LogFormat_field=Field_array_Hash($LogFormatAR,"LogFormat",$aw->GET("LogFormat"),"style:font-size:13px;padding:3px");
	$LogFile_field=Field_text("LogFile",$aw->GET("LogFile"),"font-size:13px;padding:3px;width:220px");
	$LogSeparator_field=Field_text("LogSeparator",$aw->GET("LogSeparator"),"font-size:13px;padding:3px;width:220px");
	$SiteDomain_field=Field_text("SiteDomain",$aw->GET("SiteDomain"),"font-size:13px;padding:3px;width:220px");
	
	if($_GET["freewebs"]==1){
		$aw->SET("LogFile","/var/www/{$_GET["servername"]}/www_logs/access.log");
		$aw->SET("LogType","W");
		$aw->SET("LogFormat","1");
		$aw->SET("LogSeparator"," ");
		$aw->SET("SiteDomain","{$_GET["servername"]}");
		$LogFile_field="<input type='hidden' id='LogFile' value='{$aw->GET("LogFile")}'>{$aw->GET("LogFile")}";
		$LogType_field="<input type='hidden' id='LogType' value='{$aw->GET("LogType")}'>{LogType{$aw->GET("LogType")}}";
		$LogFormat_field="<input type='hidden' id='LogFormat' value='{$aw->GET("LogFormat")}'>{$LogFormatAR[$aw->GET("LogFormat")]}";
		$LogSeparator_field="<input type='hidden' id='LogSeparator' value='{$aw->GET("LogSeparator")}'>&laquo;{$aw->GET("LogSeparator")}&raquo;";
		$SiteDomain_field="<input type='hidden' id='SiteDomain' value='{$aw->GET("SiteDomain")}'>{$aw->GET("SiteDomain")}";
	}
	
	$aw->SET("DNSLookup",2);
	$html="
	<div id='awstats_parms1'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable}:</td>
		<td style='font-size:13px;font-weight:bold'>". Field_checkbox("AwstatsEnabled",1,$aw->GET("AwstatsEnabled"))."</td>
	</tr>	
	<tr>
		<td class=legend>{SiteDomain}:</td>
		<td style='font-size:13px;font-weight:bold'>$SiteDomain_field</td>
	</tr>
	<tr>
		<td class=legend>{language}:</td>
		<td style='font-size:13px;font-weight:bold'>".Field_array_Hash($LangAR2,"Lang",$aw->GET("Lang"),"style:font-size:13px;padding:3px")."</td>
	</tr>			
	<tr>
		<td class=legend>{logfile}:</td>
		<td style='font-size:13px;font-weight:bold'>$LogFile_field</td>
	</tr>
	<tr>
		<td class=legend>{LogType}:</td>
		<td style='font-size:13px;font-weight:bold'>$LogType_field</td>
	</tr>	
	<tr>
		<td class=legend>{LogFormat}:</td>
		<td style='font-size:13px;font-weight:bold'>$LogFormat_field</td>
	</tr>		
	<tr>
		<td class=legend>{LogSeparator}:</td>
		<td style='font-size:13px;font-weight:bold'>$LogSeparator_field</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>". button("{apply}","SaveAwstats()")."</td>
	</tr>
	</table>
	
	<script>
		var x_SaveAwstats=function (obj) {
			var results=trim(obj.responseText);
			if(results.length>2){alert(results);}			
			RefreshTab('main_config_awstats');
			}	
	
	
		function SaveAwstats(){
			var XHR = new XHRConnection();
			if(document.getElementById('AwstatsEnabled').checked){XHR.appendData('AwstatsEnabled',1);}else{XHR.appendData('AwstatsEnabled',0);}
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.appendData('LogFile',document.getElementById('LogFile').value);
    		XHR.appendData('LogType',document.getElementById('LogType').value);
    		XHR.appendData('LogFormat',document.getElementById('LogFormat').value);
    		XHR.appendData('LogSeparator',document.getElementById('LogSeparator').value);
 			document.getElementById('awstats_parms1').innerHTML='<center><img src=img/wait_verybig.gif></center>';
    		XHR.sendAndLoad('$page', 'GET',x_SaveAwstats);
			
		}	
		
</script>	
";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function Save(){
	$aw=new awstats($_GET["servername"]);
	$aw->SET("AwstatsEnabled",$_GET["AwstatsEnabled"]);
	$aw->SET("LogFile",$_GET["LogFile"]);
	$aw->SET("LogType",$_GET["LogType"]);
	$aw->SET("LogFormat",$_GET["LogFormat"]);
	$aw->SET("LogSeparator",$_GET["LogSeparator"]);
	
	$sock=new sockets();
	$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");
	if($ArticaMetaEnabled==1){$sock->getFrameWork("cmd.php?artica-meta-awstats=yes");}	
	
	}

	
// /var/www/$hostname/www_logs/access.log

	


	
