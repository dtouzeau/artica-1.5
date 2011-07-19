<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["EnableSquidFilterWhiteListing"])){EnableSquidFilterWhiteListingSave();exit;}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["index"])){section_index();exit;}
	if(isset($_GET["template"])){section_template();exit;}
	
js();
	
	
	
	
	
function js(){
		$page=CurrentPageName();
	echo "
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?tabs=yes');";	
	
	
}

function EnableSquidFilterWhiteListingSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableSquidFilterWhiteListing",$_GET["EnableSquidFilterWhiteListing"]);
	
}

function section_index(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	
	$EnableSquidFilterWhiteListing=$sock->GET_INFO("EnableSquidFilterWhiteListing");
	$enable=Paragraphe_switch_img("{enable_whitelisting}","{www_whitelisting_explain}","EnableSquidFilterWhiteListing",$EnableSquidFilterWhiteListing,null,550);
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/domain-whitelist-128.png'></td>
		<td valign='top'>
		<div id='EnableSquidFilterWhiteListing-div'>
		$enable
		<HR>
		<div style='text-align:right'>". button("{apply}","SaveEnableSquidFilterWhiteListing()")."</div>
		</div>
	</td>
	</tr>
	</table>
	

		
	<script>
	var x_SaveEnableSquidFilterWhiteListing=function(obj){
	      RefreshTab('dansguardian_main_whitelist');
		}		
	
	
		function SaveEnableSquidFilterWhiteListing(){
			var XHR = new XHRConnection();
      		XHR.appendData('EnableSquidFilterWhiteListing',document.getElementById('EnableSquidFilterWhiteListing').value);
      		document.getElementById('EnableSquidFilterWhiteListing-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
      		XHR.sendAndLoad('$page', 'GET',x_SaveEnableSquidFilterWhiteListing);  
		}
	
	</script>

	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function section_template(){
	
	$template=Paragraphe("banned-template-64.png","{template_label}",'{template_explain}',"javascript:s_PopUp('dansguardian.template.php',800,800)");
	$template_form_explain=Paragraphe("template-title-64.png","{template_white_explain}",'{template_white_explain_text}',"javascript:s_PopUp('dansguardian.whitelist-intro.php',800,800)");
	
	
	$tr[]=$template;
	$tr[]=$template_form_explain;
	

	
	
	

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
	
	$html=$html.implode("\n",$tables);		  

	 
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html,"squid.newbee.php,squid.index.php");

echo $html;
	
}


function tabs(){
	
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["index"]='{parameters}';
	$array["template"]='{template}';
	$array["rules"]='{rules}';
	
	
	
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
	
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
		//$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	echo "
	<div id=dansguardian_main_whitelist style='width:100%;height:730px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#dansguardian_main_whitelist').tabs({
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