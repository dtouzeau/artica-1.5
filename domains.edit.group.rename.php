<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.user.inc');
	
	
	if($_GET["group-id"]==null){die();}
	
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AllowAddUsers){
		writelogs("Wrong account : no AllowAddUsers privileges",__FUNCTION__,__FILE__);
		$tpl=new templates();
		$error="{ERROR_NO_PRIVS}";
		echo $tpl->_ENGINE_parse_body("alert('$error')");
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["rename"])){rename_group();exit;}
	
	
	js();
	
function js(){
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body("{GROUP_RENAME}");
$page=CurrentPageName();
$prefix=str_replace('.','_',$page);

$html="
{$prefix}timeout=0;

function {$prefix}Load(){
	RTMMail('550','$page?popup=yes&group-id={$_GET["group-id"]}&ou={$_GET["ou"]}','$title');
	//setTimeout('DisplayDivs()',900);
	}
	
function DisplayDivs(){
		{$prefix}timeout={$prefix}timeout+1;
		if({$prefix}timeout>10){
			alert('timeout');
			return;
		}
		if(!document.getElementById('grouplist')){
			setTimeout('DisplayDivs()',900);
		}
		LoadAjax('grouplist','$page?LoadGroupList=$ou');
		LoadGroupSettings();
		{$prefix}timeout=0;
		$loadgp
	}
	

var x_{$prefix}RenameGroup= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	Loadjs('domains.edit.group.php?ou={$_GET["ou"]}&js=yes&group-id={$_GET["group-id"]}');
	RTMMailHide();
	}	
		
	function RenameGroup(){
			var XHR = new XHRConnection();
			XHR.appendData('rename','yes');
			XHR.appendData('group-id','{$_GET["group-id"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('new-name',document.getElementById('new-name').value);
			document.getElementById('div-rename-group').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}RenameGroup);
		}
	
	
{$prefix}Load();
	";

echo $html;
	
	
}

function popup(){
	$tpl=new templates();
	
	if($_SESSION["uid"]<>-100){
		if($_GET["ou"]<>$_SESSION["ou"]);
		echo "<H1>".$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}")."</H1>";
		die();
	}
	
	$gp=new groups($_GET["group-id"]);
	if($_SESSION["uid"]<>-100){	
		if($gp->ou<>$_SESSION["ou"]){
			echo "<H1>".$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}")."</H1>";
			die();
		}
	}
	
	
	$html="<H1>$gp->ou:: $gp->groupName</H1>
	<div id='div-rename-group'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
			<img src='img/group_rename-128.png'>
		</td>
		<td valign='top'><p class=caption>{GROUP_RENAME_TEXT}</p>
		". RoundedLightWhite("
		<table style='width:100%'>
		<tr>
			<td class=legend>{old_name}:</td>
			<td><code style='font-size:13px;font-weight:bold'>$gp->groupName</code></td>
		</tr>		
		<tr>
			<td class=legend>{new_name}:</td>
			<td>". Field_text('new-name',null,'width:220px')."</td>
		</tr>
		<tr>
			<td colspan=2 align='right'>
				<hr>
					<input type='button' OnClick=\"javascript:RenameGroup();\" value='{change}&nbsp;&raquo;'>
			</td>
		</tR>
		</table>")."
	</td>
	</tr>
	</table>
	</div>
	";
			
	echo $tpl->_ENGINE_parse_body($html);
	
			
			
}

function rename_group(){
	$tpl=new templates();
	if($_SESSION["uid"]<>-100){
		if($_GET["ou"]<>$_SESSION["ou"]);
		echo $tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");;
		die();
	}
	
	$gp=new groups($_GET["group-id"]);
	if($_SESSION["uid"]<>-100){	
		if($gp->ou<>$_SESSION["ou"]){
			echo $tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
			die();
		}
	}

	$ldap=new clladp();
	
	$newname=$_GET["new-name"];
	if(trim($newname)==null){return null;}
	
		$actualdn=$gp->dn;
		if(preg_match('#cn=(.+?),(.+)#',$actualdn,$re)){
			$branch=$re[2];
		}
		$newdn="cn=$newname";
		$newdn2="$newdn,$branch";
		$ldap=new clladp();
		if($ldap->ExistsDN($newdn2)){return null;}
		writelogs("Rename $actualdn to $newdn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		
		if(!$ldap->Ldap_rename_dn($newdn,$actualdn,$branch)){
			echo $tpl->_ENGINE_parse_body("{GROUP_RENAME} {failed}\n $ldap->ldap_last_error");
		}	
	
}
	
	
	

?>