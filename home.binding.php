<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');	
	
	//permissions	
	$usersprivs=new usersMenus();
	if(!$usersprivs->AllowAddUsers){$tpl=new templates();echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");die();}	
	if(isset($_GET["homebind"])){homebind_page();exit;}
	if(isset($_GET["homebind-add"])){homebind_add();exit;}
	if(isset($_GET["homebind-list"])){echo homebind_list($_GET["homebind-list"]);exit;}
	if(isset($_GET["homebind-mount"])){homebind_mount();exit;}
	if(isset($_GET["homebind-umount"])){homebind_umount();exit;}
	if(isset($_GET["homebind-dmount"])){homebind_dmount();exit;}	
	
	
	
	js();
	function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{HomeBinding}');
	$page=CurrentPageName();
	$userid=$_GET["userid"];
	
	
	$html="
		var mem_section='';
	
		function LoadHomeBind(){
			YahooWin5(550,'$page?homebind=yes&userid=$userid','$title::$userid');
			
		
		}
		
	var x_HomeBindAdd= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
		LoadAjax('HomeBindingList','$page?homebind-list=$userid');
		}		
		
		function HomeBindAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('homebind-add',document.getElementById('homeDirectoryBinded').value);
			XHR.appendData('userid','$userid');
			document.getElementById('HomeBindingList').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_HomeBindAdd);
			
		
		}
		
		function homebindMount(num){
			var XHR = new XHRConnection();
			XHR.appendData('homebind-mount',num);
			XHR.appendData('userid','$userid');
			document.getElementById('HomeBindingList').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_HomeBindAdd);
			
		
		}	
		
		function homebindUMount(num){
			var XHR = new XHRConnection();
			XHR.appendData('homebind-umount',num);
			XHR.appendData('userid','$userid');
			document.getElementById('HomeBindingList').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_HomeBindAdd);
			}	

		function homebindDMount(num){
			var XHR = new XHRConnection();
			XHR.appendData('homebind-dmount',num);
			XHR.appendData('userid','$userid');
			document.getElementById('HomeBindingList').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_HomeBindAdd);
			}			

		
		
		
		
		
		
		function CompleteNameCheck(event){
			if(checkEnter(event)){CompleteNameEdit();}
		}
		
		function CompleteNameFill(){
			if(!document.getElementById('givenName1')){
				setTimeout('CompleteNameFill()',500);
				return false;
			}
			
			var givenname=document.getElementById('givenName').value;
			var sn=document.getElementById('sn').value;
			document.getElementById('givenName1').value=givenname;
			document.getElementById('sn1').value=sn;
			
		
		}
		
		function CompleteNameEdit(){
			var givenname=document.getElementById('givenName1').value;
			var sn=document.getElementById('sn1').value;
			document.getElementById('givenName').value=givenname;
			document.getElementById('sn').value=sn;
			document.getElementById('displayName').value=givenname+' '+sn;
			YahooWin6Hide();
				
		
		}
		
		function ContactTabs(num){
			employeeNumber=document.getElementById('employeeNumber').value;
			LoadAjax('contact_section','$page?section='+num+'&employeeNumber='+employeeNumber);
			LoadAjax('tabs','$page?section='+num+'&employeeNumber='+employeeNumber+'&showtab=yes');
		}
		
		function x_EditContact(){
			alert(mem_section);
			
		}
		
		
		function EditContactCheck(event){
			if(checkEnter(event)){EditContact('FFM_CONTACT_PAGE');}
		}		
		
		function EditContact(Form_name){
			employeeNumber=document.getElementById('employeeNumber').value;
			mem_section=document.getElementById('tab').value;
			ParseForm(Form_name,'$page',true,false,false,'contact_section','$page?section='+mem_section+'&employeeNumber='+employeeNumber,x_EditContact);
		}
		
		
	var x_ContactDelete= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
		YahooWin5Hide();
		}			
		
		function ContactDelete(dn){
			var XHR = new XHRConnection();
			XHR.appendData('delete-contact',dn);
			document.getElementById('contact_section').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_ContactDelete);	
		
		}
	
	
		
	
	LoadHomeBind();
	
	";
	
	echo $html;
	
}

function homebind_page(){
	$userid=$_GET["userid"];
	$ct=new user($userid);
	$HomeDirectory=$ct->homeDirectory;
	$homebind_list=homebind_list($userid);
	
	$html="<H1>{HomeBinding}</H1>
	<strong style='font-size:13px'>$HomeDirectory</strong>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{directory}:</td>
		<td>" . Field_text("homeDirectoryBinded",null)."</td>
		<td><input type='button' OnClick=\"javascript:Loadjs('SambaBrowse.php?field=homeDirectoryBinded&no-shares=yes');\" value='{browse}...'>
		<td><input type='button' OnClick=\"javascript:HomeBindAdd();\" value='{add}&nbsp;&raquo;'>
	</tr>
	</table>
	<br>
	" . RoundedLightWhite("<div id='HomeBindingList' style='width:100%;height:200px;overflow:auto'>$homebind_list</div>")."
	
	
	";
	
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	
}

function homebind_list($userid){
	$user=new user($userid);
	if(!is_array($user->homeDirectoryBinded)){return null;}
	
	$html="<table style='width:100%'>
	<tr>
		
		<th colspan=2>{source}</th>
		<th colspan=3>{target}</th>
	</tr>";
	
	$sock=new sockets();
	while (list ($num, $val) = each ($user->homeDirectoryBinded) ){
		if($val==null){return null;}
		
		
		if(strlen($val)>39){$dir=texttooltip(substr($val,0,36)."...",$val);}else{$dir=$val;}
		$tt=$user->homeDirectory."/" . basename($val);
		if(strlen($tt)>39){$dir=texttooltip(substr($tt,0,36)."...",$tt);}else{$tt=$tt;}
		$mnt=trim($sock->getfile("ismounted:$val;$tt"));
		if($mnt=="FALSE"){
			$img=imgtootltip("status_critical.gif",'{error_not_mounted},{click_to_mount}',"homebindMount($num)");
		}else{
			$img=imgtootltip("status_ok.gif",'{mounted},{click_to_dismount}',"homebindUMount($num)");
		}
		$html=$html."
		<tr " . CellRollOver().">
			<td width=1%  valign='middle'>$img</td>
			<td width=50% valign='middle'>$dir</td>
			<td width=1%  valign='middle'><img src='img/fw_bold.gif'></td>
			<td width=50% valign='middle'>$tt</strong></td>
			<td width=1%  valign='middle'>" . imgtootltip("ed_delete.gif","{delete}","homebindDMount($num)")."</td>
		</tr>
		
		";
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
	
}

function homebind_add(){
	$user=new user($_GET["userid"]);
	$user->add_homeDirectoryBinded($_GET["homebind-add"]);
	}
function homebind_mount(){
	$index=$_GET["homebind-mount"];
	$user=new user($_GET["userid"]);
	$target=$user->homeDirectoryBinded[$index];
	$sock=new sockets();
	$datas=trim($sock->getfile("homeDirectoryBinded:$user->homeDirectory;$target"));
	echo $datas;	
	}
function homebind_umount(){
	$index=$_GET["homebind-umount"];
	$user=new user($_GET["userid"]);
	$target=$user->homeDirectoryBinded[$index];
	$sock=new sockets();
	$datas=trim($sock->getfile("homeDirectoryUBinded:$user->homeDirectory;$target"));
	echo $datas;	
	}
function homebind_dmount(){
	$index=$_GET["homebind-dmount"];
	$user=new user($_GET["userid"]);
	$user->delete_homeDirectoryBinded($user->homeDirectoryBinded[$index]);
	
	}			
	

	
	
?>