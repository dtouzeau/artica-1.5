<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$users=new usersMenus();
if($users->AsArticaAdministrator==false){header('location:users.index.php');}
if(isset($_GET["autoinstall"])){autoinstall();exit;}
if(isset($_GET["apps"])){AppliList();exit;}
if(isset($_GET["addr"])){AddremovePrograms();exit;}
if(isset($_GET["setp1"])){AddremovePrograms1();exit;}
if(isset($_GET["PerformAutoRemove"])){PerformAutoRemove();exit;}
if(isset($_GET["PerformAutoInstall"])){PerformAutoInstall();exit;}


applications_Status();	

function applications_Status(){
	
	$page=CurrentPageName();
	$tpl=new templates();

	
	$html="
	<table style='width:600px' align=center>
	<tr>
		<td valign='top'>
			<img src='img/bg_applis.jpg'>
			<br>
			
		</td>
		
		<td valign='top'>
<div id='addr'></div>
			<script>LoadAjax('addr','$page?addr=yes')</script>
		</td>
	</tr>
	</table>
	<h4>{installed_applications}</H4>
	<div id='apps'></div>
	<script>LoadAjax('apps','$page?apps=yes');</script>";
	$JS["JS"][]='js/applis.js';
	$tpl=new template_users('{installed_applications}',$html,0,0,0,0,$JS);
	echo $tpl->web_page;
	
}

function AppliList_TABS(){
	if(!isset($_GET["tab"])){$_GET["tab"]="CORE_MODULES";};
	$page=CurrentPageName();
	$array["SECURITY_MODULES"]='{SECURITY_MODULES}';
	$array["CORE_MODULES"]='{CORE_MODULES}';
	$array["STAT_MODULES"]='{STAT_MODULES}';
	$array["MAIL_MODULES"]='{MAIL_MODULES}';

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('apps','$page?apps=yes&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}  

function AppliList(){
$tpl=new templates();
	$sys=new systeminfos();
	$sys->ParseAPPs();
	if(!is_array($sys->array_applis)){echo $tpl->_ENGINE_parse_body('{system error} line' . __LINE__);exit;}

		$html=$html .AppliList_TABS(). "<br><H5>{{$_GET["tab"]}}</H5>.".subapplis($sys->array_applis[$_GET["tab"]]);
	
	
	echo $tpl->_ENGINE_parse_body($html);
}


function subapplis($array){
		
		while (list ($num, $ligne) = each ($array) ){
		 	if($num<>null){
			 	if($ligne==null){
			 		$img="warning42.png";
			 		$text="<H3 style='font-size:13px'>{{$num}} {not_installed}</h3>";
			 		$link="setup.index.php";
			 	}else{
			 		
			 		$text="<H3 style='font-size:13px'>{{$num}} $ligne<H3>";
			 		$img="42-green.png";
			 		$link=null;}
			 	
				$html=$html ."<div style='float:left'>".Paragraphe($img," ",$text,$link,null,200,74)."</div>";
				
				
			}
	}
	return $html;
}


function autoinstall(){
	$sock=new sockets();
	$sock->getfile('PerformAutoInstall:'.$_GET["autoinstall"]);
	applications_Status();
}

function AddremovePrograms(){
	
	$html="
	<table style='width:300px'>
	<tr>
	<td width=1% valign='top'><img src='img/add-remove-128.png' align='left' style='margin:3px'>
	<td valign='top'>
	
		<H3>{addremoveprog}</H3>
		<div style='font-size:13px'>
		see <a href='setup.index.php'><u>artica-make</u></a>
		</div>
		
	
	
	</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(RoundedLightGrey($html,"setup.index.php",1)."<br><div id='step1'></div>");
	exit;
	
	
	
	
	$array_prog=array(
	""=>"{select}",
	"APP_FETCHMAIL"=>'{APP_FETCHMAIL}',"APP_KAS3"=>'{APP_KAS3}',
	"APP_AVESERVER"=>'{APP_AVESERVER}',
	"APP_AWSTATS"=>"{APP_AWSTATS}",
	"APP_GEOIP"=>"{APP_GEOIP}",	
	"APP_DNSMASQ"=>'{APP_DNSMASQ}'
	
	
	);
	
	$fiel=Field_array_Hash($array_prog,'prog');
	
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/add-remove-128.png'></td>
	<td valign='top'>
		<H4>{addremoveprog}</H4>
		$fiel <input type='button' value='{next}&nbsp;&raquo;' OnClick=\"javacscript:Setp1();\">
	
	
	</td>
	</tr>
	</table>
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(RoundedLightGreen($html)."<br><div id='step1'></div>");
	
}
function AddremovePrograms1(){
	$sys=new systeminfos();
	$sys->ParseAPPs();	
	$prod=$_GET["setp1"];
	if($prod==null){echo "&nbsp;";exit;}
	$version=$sys->array_applis_line[$prod];
	
	

		$img1='add-64.png';
		$js1="PerformAutoInstall('$prod');";
		$tips1="{install_this_prog}";
	
		$img='delete-64.png';
		$js="PerformAutoRemove('$prod');";
		$tips="{remove_this_prog}";
	
	$img=imgtootltip($img,$tips,$js);
	$remove="<tr>
	<td width=1% valign='top'>$img</td>
	<td valign='top'>
		<strong>$tips</strong>
	</td>
	</tr>";
	
	
	$img=imgtootltip($img1,$tips1,$js1);
	$add="<tr>
			<td width=1% valign='top'>$img</td>
			<td valign='top'><strong>$tips1</strong></td>
	</tr>";
		
	if($prod=='APP_GEOIP'){$remove=null;}
	
	
	$html="
	<table style='width:100%'>
	<tr><td colspan=2><H4>{".$prod."} $version</H4></td></tr>
	$add
	$remove
	</table>
	<br><div id='step2'></div>";	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(RoundedLightGreen($html));	
	
}
function PerformAutoRemove(){
	$app=$_GET["PerformAutoRemove"];
	$sock=new sockets();
	$logs=$sock->getfile('AUTOREMOVE:'. $app);
	$table=explode("\n",$logs);
	if(is_array($table)){
		$html="<table style='width:100%'>";
		while (list ($num, $ligne) = each ($table) ){
			if($ligne<>null){
			$ligne=htmlentities($ligne);
			$html=$html . "<tr><td style='padding:3px'><code>$ligne</code></td></tr>";
			}
			
		}
		
		$html=$html . "</table>";
		
		
	}
	$html="<H5>{results}</h5>$html";
	$tpl=new templates();
	echo RoundedLightGrey($tpl->_ENGINE_parse_body($html));
	
}
function PerformAutoInstall(){
	$app=$_GET["PerformAutoInstall"];
	$sock=new sockets();
	$logs=$sock->getfile('AUTOINSTALL:'. $app);
	$table=explode("\n",$logs);
	if(is_array($table)){
		$html="<table style='width:100%'>";
		while (list ($num, $ligne) = each ($table) ){
			if($ligne<>null){
			$ligne=htmlentities($ligne);
			$html=$html . "<tr><td style='padding:3px'><code>$ligne</code></td></tr>";
			}
			
		}
		
		$html=$html . "</table>";
		
		
	}
	$html="<H5>{results}</h5>$html";
	$tpl=new templates();
	echo RoundedLightGrey($tpl->_ENGINE_parse_body($html));
	
}
	