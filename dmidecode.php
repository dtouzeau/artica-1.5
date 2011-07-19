<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	$usersmenus=new usersMenus();
	if(!$usersmenus->AsArticaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");
		die();		
	}	
	
	
	if(isset($_GET["dmidecode"])){dmidecode();exit;}
	
	js();
	
function js(){

$tpl=new templates();
$page=CurrentPageName();

$title=$tpl->_ENGINE_parse_body('{dmidecode}');

$html="

	function dmidecode(){
		YahooWin2('600','$page?dmidecode=yes','$title');
	
	}


dmidecode();
";
	echo $html;
	
}	

function dmidecode(){
	
	$sock=new sockets();
	$datas=$sock->getfile("dmidecode");
	
	$tbl=explode("\n",$datas);
	if(is_array($tbl)){
		$a[]="<table>";
		while (list ($num, $line) = each ($tbl) ){
		if(trim($line)==null){continue;}
			if(preg_match('#^([a-zA-Z]+)#',$line)){
				$a[]="<tr><td style='font-size:14px;font-weight:bold;border-bottom:1px solid #CCCCCC;padding-top:10px' colspan=2>$line</td></tr>";
				continue;
			}
			
			if(preg_match('#\s+(.+?):(.+)#',$line,$re)){
				$a[]="<tr>
					<td class=legend>{$re[1]}</td><td><strong>{$re[2]}</strong></td></tr>";
				continue;
			}
			
			if(preg_match("#^		([a-zA-Z]+)#",$line)){
				$a[]="<tr>
					<td class=legend></td><td><li>$line</li></td></tr>";
					continue;
			}
		
		}
	}
	$a[]="</table>";
	$html="<H1>{dmidecode}</H1>
	<p class=caption>{dmidecode_text}</p>
	" . RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto'>".implode("\n",$a));
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}





?>