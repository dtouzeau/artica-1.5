<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mail.inc');



	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ev"])){updev();exit;}
	if(isset($_POST["RunSaUpd"])){RunSaUpd();exit;}
js();


function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{UPDATE_SA_UPDATE}');
	$rcpt=$_GET["rcpt"];
	$html="
	function saupdatestart(){
			RTMMail(850,'$page?popup=yes','$title');
		}
		
		var x_RunSaUpd= function (obj) {
			var tempvalue=obj.responseText;
			RefreshSaupdEv();
		}		
		
		
	function RunSaUpd(){
		var XHR = new XHRConnection();
		XHR.appendData('RunSaUpd','yes');
		AnimateDiv('saupddiv');
		XHR.sendAndLoad('$page', 'POST',x_RunSaUpd);
	}

	
	saupdatestart()";
	
	echo $html;
	
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$statusFileContent="ressources/logs/sa-update-status.txt";
	
	if(!is_file($statusFileContent)){
		$content[]="$statusFileContent no such file";
	}else{
		$content=explode("\n", @file_get_contents($statusFileContent));
	}
	
	
	
	$html="
	<div  class=explain >{UPDATE_SA_UPDATE_TEXT}</div>
	<center style='margin:10px'>". button("{run_update_now}","RunSaUpd()")."</center>
	<center >
	<div id='saupddiv' style='width:100%;height:550px;overflow:auto'></div>
	
	<script>
	function	RefreshSaupdEv(){
			LoadAjax('saupddiv','$page?ev=yes');
		}
	RefreshSaupdEv();
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function updev(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$statusFileContent="ressources/logs/sa-update-status.txt";
	
	if(!is_file($statusFileContent)){
		$content[]="$statusFileContent no such file";
	}else{
		$content=explode("\n", @file_get_contents($statusFileContent));
	}

$html="	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99%'>
<thead class='thead'>
	<tr>
		<th >{events}</th>
		<th width=1%>". imgtootltip("refresh-24.png","{refresh}","RefreshSaupdEv()")."</td>
	</tr>
</thead>
<tbody class='tbody'>";	
	while (list ($index, $line) = each ($content)){
		if(trim($line)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr><td style='font-size:13px' colspan=2><code>$line</code></td></tr>";
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}


function RunSaUpd(){
	$sock=new sockets();
	$sock->getFrameWork("postfix.php?RunSaUpd=yes");
	
}