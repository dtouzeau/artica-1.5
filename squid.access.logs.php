<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');

	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["list"])){access_list();exit;}
	
	
page();


function page(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$html="
	<center>
	<table style='width:80%' class=form>
	<tr>
		<td class=legend>{access_events}:</td>
		<td>". Field_text("access-search",null,"font-size:14px",null,null,null,false,"SquidAccessCheck(event)")."</td>
		<td>". button("{search}","SquidAccess()")."</td>
	</tr>
	</table>
	</center>
	<hr>
	<div id='squid-access-logs' style='width:100%;height:450px;overflow:auto'></div>
	
	<script>
	function SquidAccessCheck(e){
		if(checkEnter(e)){SquidAccess();}
	}
	
	function SquidAccess(){
			var se=escape(document.getElementById('access-search').value);
			LoadAjax('squid-access-logs','$page?list=yes&search='+se);
		}

		
	SquidAccess();		
	</script>
	";
	
echo $tpl->_ENGINE_parse_body($html);
		
	
}


function access_list(){
	$tpl=new templates();
	$page=CurrentPageName();		
	$sock=new sockets();
	$search=urlencode($_GET["search"]);
	$datas=unserialize(base64_decode($sock->getFrameWork("squid.php?access-logs=yes&search=$search")));
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<th width=1%>&nbsp;</th>
	<th width=1%>&nbsp;</th>
	<th width=1%>&nbsp;</th>
	<th>&nbsp;</th>
</thead>
<tbody class='tbody'>";	
	
while (list ($num, $ligne) = each ($datas) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	 	if($ligne==null){continue;}
	 	$proto=null;
	 	$date=null;
	 	$uri=null;
	 	$from=null;
	 	if(preg_match('#(.+?)-(.*)-(.*)\s+\[(.+?)\]\s+"(.*?)"\s+(.+)#' , $ligne,$re)){
	 		$date=date("H:i:s", strtotime($re[4]));
	 		$from=$re[1];
	 		$uri=$re[5];
	 		$other=$re[6];
	 	}else{
	 		$uri=$ligne;
	 	}
	 	
	 	$uri=str_replace('HTTP/1.1',"",$uri);
	 	$uri=str_replace('HTTP/1.0',"",$uri);

		
		if(preg_match("#([A-Z]+)\s+(.+)#", $uri,$re)){
			$proto=$re[1];
			$uri=$re[2];
		}
		
				
				$len=strlen($uri);
				if($len>70){$uri=substr($uri, 0,67)."...";}
		
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:13px'>$date</td>
		<td style='font-size:13px'>$from</td>
		<td style='font-size:13px'>$proto</td>
		<td style='font-size:13px'>$uri<div style='font-size:11px;margin-top:5px;text-align:right'><I>$other</i></div></td>
		
	</tr>
		";
		
	}
	
$html=$html."</table>


";

echo $tpl->_ENGINE_parse_body($html);	
	
}

