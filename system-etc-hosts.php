<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["add-form"])){popup_add();exit;}
	if(isset($_GET["ip_addr"])){popup_save();exit;}
	if(isset($_GET["refresh"])){echo getlist();exit;}
	if(isset($_GET["del"])){popup_delete();exit;}
	if(isset($_GET["add-etc-hosts-p"])){Paragraphe_add();exit;}
	if(isset($_GET["DisableEtcHosts"])){DisableEtcHosts_save();exit;}
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{etc_hosts}");
	$title2=$tpl->_ENGINE_parse_body("{add_new_entry}");
	$html="
	
	function etc_host_show(){
			YahooWin3(740,'$page?popup=yes','$title');
	}
	
	function etc_hosts_add_form(){
		YahooWin4(475,'$page?add-form=yes','$title2');
	}

var X_etc_hosts_add_form_save= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin4Hide();
	refresh();
	
	}		
	
	function etc_hosts_add_form_save(){
		var XHR = new XHRConnection();
		XHR.appendData('ip_addr',document.getElementById('ip_addr').value);
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('alias',document.getElementById('alias').value);
		document.getElementById('hostsdiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_etc_hosts_add_form_save);		
	}
	
	function refresh(){
		LoadAjax('idhosts','$page?refresh=yes');
	}
	
	function etc_hosts_del(ip,name){
		var XHR = new XHRConnection();
		XHR.appendData('del',ip);
		XHR.appendData('name',name);
		document.getElementById('idhosts').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_etc_hosts_add_form_save);		
	}	
		
	etc_host_show();	
	";
	
	echo $html;
}

function DisableEtcHosts_save(){
	$sock=new sockets();
	$sock->SET_INFO("DisableEtcHosts",$_GET["DisableEtcHosts"]);
	
}



function popup(){
	$page=CurrentPageName();
	$LIST=getlist();
	$sock=new sockets();
	
	$html="<div class=explain>{etc_hosts_explain}</div>
	
	<div style='text-align:right'>
	<table>
	<tr>
		<td class=legend>{DisableEtcHosts}:</td>
		<td>". Field_checkbox("DisableEtcHosts",1,$sock->GET_INFO("DisableEtcHosts"),"DisableEtcHostsSave()")."</td>
	</tr>
	</table>
	</div>
	
	<table style='width:100%'>
	<tr>
		<td valign='top'><div style='width:100%;height:330px;overflow:auto' id='idhosts'>$LIST</div></td>
		<td valign='top'><div id='add-etc-hosts-p'></div></td>
	</tr>
	</table>
	<script>
	function ParEtcHosts(){
		LoadAjax('add-etc-hosts-p','$page?add-etc-hosts-p=yes');
	}
	
	function idhostsList(){
		LoadAjax('idhosts','$page?refresh=yes');
	
	}
	
	var x_DisableEtcHostsSave=function (obj) {
			tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			ParEtcHosts();
			idhostsList();
	    }
	
	
		function DisableEtcHostsSave(){
		var XHR = new XHRConnection();
		if(document.getElementById('DisableEtcHosts').checked){
			XHR.appendData('DisableEtcHosts','1');}else{
			XHR.appendData('DisableEtcHosts','0');}
			document.getElementById('add-etc-hosts-p').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';			
			XHR.sendAndLoad('$page', 'GET',x_DisableEtcHostsSave);		
		}
	
	ParEtcHosts();
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");	
	
	
}
function Paragraphe_add(){
	$add=Paragraphe("host-file-64-add.png","{add_new_entry}","{add_new_entry_text}","javascript:etc_hosts_add_form()","{add_new_entry_text}");
	$sock=new sockets();
	if($sock->GET_INFO("DisableEtcHosts")==1){
		$add=Paragraphe("host-file-64-add-grey.png","{add_new_entry}","{add_new_entry_text}","");
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($add);
	}
	
	

function getlist(){
	$sock=new sockets();
	$res=base64_decode($sock->getFrameWork("cmd.php?etc-hosts-open=yes"));
	writelogs($res,__FUNCTION__,__FILE__,__LINE__);
	$datas=unserialize($res);
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#^([0-9\.\:]+)\s+(.+?)\s+(.+?)$#",$ligne,$re)){
			$array[]=array("name"=>$re[2],"alias"=>$re[3],"ip"=>$re[1],"md"=>md5($ligne));
			continue;
		}
		
		if(preg_match("#^([0-9\.\:]+)\s+(.+?)$#",$ligne,$re)){
			$array[]=array("name"=>$re[2],"ip"=>$re[1],"md"=>md5($ligne));
			continue;
		}		
		
	}
	
	$DisableEtcHosts=$sock->GET_INFO("DisableEtcHosts");
	if(!is_array($array)){return null;}
	$html="	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99%'>
	<thead class='thead'>
		<th>&nbsp;</th>
		<th>{ip_address}</th>
		<th>{servername}</th>
		<th>{alias}</th>
		<th>&nbsp;</th>
		</thead>
	</tr>
	<tbody class='tbody'>
	";
	
	while (list ($num, $ligne) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
		$delete=imgtootltip("delete-24.png","{delete}","etc_hosts_del('{$ligne["ip"]}','{$ligne["name"]}')");
		if($ligne["name"]==null){$ligne["name"]="&nbsp;";}
		if($ligne["alias"]==null){$ligne["alias"]="&nbsp;";}
		
		if($DisableEtcHosts==1){
			$delete=imgtootltip("delete-24-grey.png","{delete}","blur()");
		}
		
		
		
		$html=$html."<tr class=$classtr>
			<td width=1% nowrap><img src='img/base.gif'></td>
			<td width=1% nowrap style='font-size:14px'>{$ligne["ip"]}</td>
			<td width=60% nowrap style='font-size:12px'>{$ligne["name"]}</td>
			<td width=1% nowrap style='font-size:14px'>{$ligne["alias"]}</td>
			<td width=1% nowrap>$delete</td>
			</tr>
			
			";
		
	}
	
	$html=$html."</tbody>
	</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$html");		
	
	
}

function popup_save(){
	if($_GET["ip_addr"]==null){return false;}
	if($_GET["servername"]==null){return false;}
	if($_GET["alias"]==null){
		
		$_GET["alias"]=$_GET["servername"];
	
	}
	$line="{$_GET["ip_addr"]}\t{$_GET["servername"]}\t{$_GET["alias"]}";
	
	$sock=new sockets();
	$line=base64_encode($line);
	$sock->getFrameWork("cmd.php?etc-hosts-add=$line");
	
	
}

function popup_add(){
	
	$html="
	
	<div id='hostsdiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/64-computer.png'></td>
		<td valign='top'>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend style='font-size:14px'>{ip_address}:</td>
				<td>". Field_text('ip_addr',null,"width:120px;font-size:14px","script:CheckHostip()")."</td>
			</tR>
			<tr>
				<td class=legend style='font-size:14px'>{servername}:</td>
				<td>". Field_text('servername',null,"width:220px;font-size:14px",null,"CheckHostAlias()")."</td>
			</tR>
				
			<tr>
				<td class=legend style='font-size:14px'>{alias}:</td>
				<td>". Field_text('alias',null,"width:120px;font-size:14px")."</td>
			</tR>
		</table><hr>
		<div style='width:100%;text-align:right'>". button("{add}","etc_hosts_add_form_save()")."</div>
		</div>
		
		<script>
			function CheckHostip(){
				DisableFieldsFromId('hostsdiv');
				document.getElementById('ip_addr').disabled=false;
				var ip_addr=document.getElementById('ip_addr').value;
				var re = new RegExp('^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+');
				
				if(ip_addr.match(re)){
					document.getElementById('servername').disabled=false;
				}	
			
			}
		
		
			function CheckHostAlias(){
				var servername=document.getElementById('servername').value;
				if(servername.length==0){return;}
				
				document.getElementById('alias').disabled=false;
				
				var alias=document.getElementById('alias').value;
				if(alias.length>0){return;}
				
				
				tr=servername.split('.');
				
				if(tr.length>0){
					document.getElementById('alias').value=tr[0];
				}
			
			}
			
			CheckHostip();
			
		</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");		
	
}

function popup_delete(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?etc-hosts-del-by-values=yes&ip={$_GET["del"]}&name={$_GET["name"]}");
}
	

?>