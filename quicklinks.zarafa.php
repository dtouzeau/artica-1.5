<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsPostfixAdministrator){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}


if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){popup();exit;}


function js(){
	$page=CurrentPageName();
$html="	
function ZarafaQuickLinks(){
	var z = $('#middle').css('display');
	if(z!=='none'){
		$('#middle').slideUp('normal');
		$('#middle').html('');
		$('#quick-links').html('');
		$('#middle').slideDown({
			duration:900,
			easing:'easeOutExpo',
			complete:function(){
				ZarafaQuickLinksMainLoad();
				}
			});
		}
	
}
function ZarafaQuickLinksMainLoad(){
	LoadAjax('middle','$page?popup=yes');
}	
ZarafaQuickLinks();
";
echo $html;
	
}

function popup(){
	
	
	
	
}