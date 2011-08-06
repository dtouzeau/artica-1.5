<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');


	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["smtp_generic_map_list"])){smtp_generic_map_list();exit;}
	if(isset($_GET["source_pattern"])){smtp_generic_map_add();exit;}
	if(isset($_GET["smtp_generic_map_del"])){smtp_generic_map_del();exit;}
		
	js();
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{smtp_generic_maps}");
	$page=CurrentPageName();
	if($_GET["ou"]==null){$_GET["ou"]=base64_encode("POSTFIX_MAIN");}
	$ou_decoded=base64_decode($_GET["ou"]);
	$html="
		function smtp_generic_maps_load(){
			YahooWin('550','$page?popup=yes&ou={$_GET["ou"]}','$title::$ou_decoded');		
			}
	smtp_generic_maps_load();";
	
	echo $html;
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$pattern=$tpl->javascript_parse_text("{pattern}");
	$html="<div class=explain>{smtp_generic_maps_text}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{source_pattern}:</td>
		<td>". Field_text("source_pattern",null,"font-size:13px;padding:3px",null,null,null,false,"smtp_generic_map_add_check(event)")."</td>
		<td width=1%>". help_icon("{smtp_generic_maps_explain}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{destination_pattern}:</td>
		<td>". Field_text("destination_pattern",null,"font-size:13px;padding:3px",null,null,null,false,"smtp_generic_map_add_check(event)")."</td>
		<td width=1%>". help_icon("{smtp_generic_maps_explain}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>".button("{add}","smtp_generic_map_add()")."</td>
	</tr>
	</table>
	<div id='smtp_generic_map_list' style='width:99%;height:200px;overflow:auto;margin-top:10px'></div>
	
	<script>
		function smtp_generic_map_list(){
			LoadAjax('smtp_generic_map_list','$page?smtp_generic_map_list=yes&ou={$_GET["ou"]}');
		}
		
		var x_smtp_generic_map_add=function(obj){
     		var tempvalue=obj.responseText;
      		if(tempvalue.length>3){alert(tempvalue);}
			smtp_generic_map_list();
		}	

		function smtp_generic_map_add_check(e){
			if(checkEnter(e)){smtp_generic_map_add();}
		}
		
		function smtp_generic_map_add(){
				var XHR = new XHRConnection();
				var src=document.getElementById('source_pattern').value;
				var dest=document.getElementById('source_pattern').value;
				if(src.length>0){
					if(src.length>0){
						XHR.appendData('source_pattern',document.getElementById('source_pattern').value);
						XHR.appendData('destination_pattern',document.getElementById('destination_pattern').value);
						XHR.appendData('ou','{$_GET["ou"]}');
						document.getElementById('smtp_generic_map_list').innerHTML='<center style=\"width:100%\"><img src=img/wait.gif></center>';
						XHR.sendAndLoad('$page', 'GET',x_smtp_generic_map_add);
						}
				}
			
		}
		
		
		
	function smtp_generic_map_del(ID){
		var XHR = new XHRConnection();
		XHR.appendData('smtp_generic_map_del',ID);
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('smtp_generic_map_list').innerHTML='<center style=\"width:100%\"><img src=img/wait.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_smtp_generic_map_add);		
	}		
		
	smtp_generic_map_list();
	</script>
	";	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);}
	
function smtp_generic_map_add(){
	$ou=base64_decode($_GET["ou"]);
	$md5=md5($_GET["source_pattern"]."$ou");
	$sql="INSERT INTO smtp_generic_maps (generic_from,generic_to,ou,zmd5)
	VALUES('{$_GET["source_pattern"]}','{$_GET["destination_pattern"]}','$ou','$md5');";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){echo $q->mysql_error;return ;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-smtp-generic=yes");
	
}

function smtp_generic_map_del(){
	$ou=base64_decode($_GET["ou"]);
	$sql="DELETE FROM smtp_generic_maps WHERE ID='{$_GET["smtp_generic_map_del"]}' AND ou='$ou'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return ;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-smtp-generic=yes");
	
	
}
	
function smtp_generic_map_list(){
	$q=new mysql();
	$ou=base64_decode($_GET["ou"]);
	$sql="SELECT * FROM smtp_generic_maps WHERE ou='$ou' ORDER BY generic_from";

	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		$error="<span style='color:red;font-size:12px'>$q->mysql_error</span>";
	}
	$html="	$error<table class=tableView style='width:100%'>
				<thead class=thead>
				<tr>
					<th  nowrap>{source_pattern}</td>
					<th  nowrap colspan=2>{destination_pattern}</td>
					<th>&nbsp;</th>
				</tr>
				</thead>
			
			
			";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		$html=$html."
		<tr class=$cl>
			
			<td width=50%><code style='font-size:14px'>{$ligne["generic_from"]}</td>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=50%><code style='font-size:14px'>{$ligne["generic_to"]}</td>
			<td width=50%>". imgtootltip("delete-24.png","{delete}","smtp_generic_map_del('{$ligne["ID"]}')")."</td>
		</tr>";
		}
		
		$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

?>