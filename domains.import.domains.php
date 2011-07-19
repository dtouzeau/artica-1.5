<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.auto-aliases.inc');
	
	
if(!VerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-import"])){popup_import();exit;}
	if(isset($_GET["import-now"])){popup_perform();exit;}
	if(isset($_GET["view-events"])){popup_events();exit;}
js();


function popup(){
	$page=CurrentPageName();
	$html="
	<div class=explain>{import_smtp_domains_explain}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' width=1% nowrap>{filename}:</td>
		<td width=99%>". Field_text("DomainsSourcefile",null,"font-size:13px;padding:3px")."</td>
		<td width=1%><input type='button' value='{browse}&nbsp;&raquo;' OnClick=\"javascript:Loadjs('tree.php?select-file=txt&target-form=DomainsSourcefile');\"></td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button("{import}","DomainImportPerform()")."</td>
	</tr>
	</table>
		
	<div id='domains-status'></div>
	
	
	<script>
		function DomainImportPerform(){
			var path=escape(document.getElementById('DomainsSourcefile').value);
			LoadAjax('domains-status','$page?popup-import=yes&ou={$_GET["ou"]}&path='+path);
		
		}
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
	
	
function popup_import(){
	$file=$_GET["path"];
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?read-file=".base64_encode($file)));
	$tbl=explode("\n",$datas);
	$count=count($tbl);
	$page=CurrentPageName();
	unset($tbl);
	
	$html="
	<center>
		<div style='font-size:16px'>{ready_to_import}</div>
		<div style='font-size:16px'>$count {domains}</div>
		<hr>
		". button("{import}","ImportDomainNow()")."
	</center>
	
	
	<script>
	var x_ImportDomainNow= function (obj) {
		var response=obj.responseText;
		SMTP_IMPORT_SCHEDULER();
		
	}		
	
	
	function ImportDomainNow(){
		document.getElementById('domains-status').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		var XHR = new XHRConnection();
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('import-now','$file');
		XHR.sendAndLoad('$page', 'GET',x_ImportDomainNow);
	}
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_perform(){
	$sock=new sockets();
	$_GET["import-now"]=base64_encode($_GET["import-now"]);
	$sock->getFrameWork("cmd.php?smtp-domains-import=yes&file={$_GET["import-now"]}&ou={$_GET["ou"]}");
	
}
	
	

	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{import_smtp_domains}");
	$html="
	var SMTP_IMPORT_TANT=0;


function SMTP_IMPORT_SCHEDULER(){
   	SMTP_IMPORT_TANT = SMTP_IMPORT_TANT+1;
	if(!YahooWin3Open()){return false;}
   if (SMTP_IMPORT_TANT < 10 ) { 
     setTimeout(\"SMTP_IMPORT_SCHEDULER()\",1000);
   } else {
      SMTP_IMPORT_TANT = 0;
      LoadAjax('domains-status','$page?view-events=yes&ou={$_GET["ou"]}');
	  SMTP_IMPORT_SCHEDULER();
   }
}	
	
	YahooWin3('600','$page?popup=yes&ou={$_GET["ou"]}','$title')
	
	
	
	
	";
	echo $html;
	}


	
function popup_events(){
	$sock=new sockets();
	$tpl=new templates();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?smtp-import-events=yes&ou={$_GET["ou"]}")));
	if(!is_array($datas)){
		echo $tpl->_ENGINE_parse_body("<center><H3>{please_wait}</H3></center>");
		return ;
	}
	
	$pourcent=$datas["POURC"];
	$html_pource=pourcentage($pourcent);
	krsort($datas["EVENTS"]);
	while (list ($num, $line) = each ($datas["EVENTS"]) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
	$tr=$tr."
		<tr class=$classtr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td width=99% style='font-size:13px'>$line</td>
		</tr>
		";
		
	}
	
$html= $html."<hr>
$html_pource
<hr>
<div style='height:350px;overflow:auto'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>{events}</th>
	
	</tr>
</thead>
<tbody class='tbody'>$tr</tbody></table></div>";	
	echo $tpl->_ENGINE_parse_body($html);
	
}
	
	
function VerifyRights(){
	if($_GET["ou"]==null){return false;}
	$usersmenus=new usersMenus();
	if($usersmenus->AsMessagingOrg){return true;}
	if(!$usersmenus->AllowChangeDomains){return false;}
	
}




?>