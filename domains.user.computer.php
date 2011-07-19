<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.computers.inc');

	if(!Isright()){$tpl=new templates();echo "<H2>".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."</H2>";die();}
	if(isset($_GET["add-computer"])){computer_add();exit;}
	if(isset($_GET["generate-list"])){computer_list();exit;}
	
	page();
	
function page(){

	$page=CurrentPageName();
	$add=Paragraphe("computer-64-add.png","{add_user_computer}","{add_user_computer_text}","javascript:AddComputerToUser()");
	$html="
	<div style='width:100%;background-color:white;color:black'>
	<H1>{$_GET["userid"]}:: {computers}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$add</td>
		<td valign='top'><div id='computers-list'></div></td>
	</tr>
	</table>
	</div>
	<script>
		function AddComputerToUser(){
			Loadjs('computer-browse.php?mode=selection&callback=AddComputerToUserSelect');
		}
		
	var x_AddComputerToUserSelect= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		YahooLogWatcherHide();
		LoadComputerList();
		
	}		
		
		function AddComputerToUserSelect(uid){
			var XHR = new XHRConnection();
			XHR.appendData('add-computer',uid);
			XHR.appendData('userid','{$_GET["userid"]}');
		 	document.getElementById('computers-list').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.sendAndLoad('$page', 'GET',x_AddComputerToUserSelect); 
		}
		
		function LoadComputerList(){
			LoadAjax('computers-list','$page?generate-list=yes&userid={$_GET["userid"]}');
		}
		
	LoadComputerList();	
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function computer_add(){
	$userid=$_GET["userid"];
	$computer=new computers($_GET["add-computer"]);
	if(!IsPhysicalAddress($computer->ComputerMacAddress)){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{computer}:$computer->ComputerRealName
		{MAC}:$computer->ComputerMacAddress
		--------------------
		{WARNING_MAC_ADDRESS_CORRUPT}
		");
		return;
	}
	
	
	$user=new user($userid);
	$user->add_computer($computer->ComputerMacAddress,$computer->uid);
	}
	
function computer_list(){
		$userid=new user($_GET["userid"]);
		$dn=$userid->dn;
		$ldap=new clladp();
		$pattern="(&(objectClass=ComputerAfectation)(cn=*))";
		$attr=array();
		$sr=@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
		if(!$sr){return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]==0){return;}
		
		for($i=0;$i<$hash["count"];$i++){
			$uid=$hash[$i]["uid"][0];
			$mac=$hash[$i]["computermacaddress"][0];
			$computer=new computers($uid);
			$uid_text=str_replace("$","",$uid);
			
			$js="javascript:Loadjs('computer.infos.php?uid=$uid');";
			$tb[]="<div style='float:left;margin:3px'>".Paragraphe("64-computer.png",
			$uid_text,"<strong>$mac<div><i>$computer->ComputerOS</i></div><div>$computer->ComputerIP</div></strong>",$js)."</div>";
		}
		
			
	$html="<div style='width:100%'>".implode("\n",$tb);
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}




function IsRight(){
	if(!isset($_GET["userid"])){return false;}
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsSambaAdministrator){return true;}
	if($users->AllowAddUsers){return true;}
	return false;
	}

?>