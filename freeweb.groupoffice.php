<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_POST["rebuild-groupoffice"])){rebuild_group_office();exit;}
	
	
page();


function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$rebuild_groupware_warning=$tpl->_ENGINE_parse_body("{rebuild_groupware_warning}");
	
	$h=new vhosts();
	$hash=$h->listOfAvailableServices(true);
	$sql="SELECT groupware FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
	if($ligne["groupware"]<>null){
		$groupware_text="
		<table style='width:100%' class=form>
		<tr>
			<td width=1% valign='top'><img src='img/{$h->IMG_ARRAY_64[$ligne["groupware"]]}'></td>
			<td valign='top' width=99%>
				<div style='font-size:16px'>{current}:&nbsp;<strong>&laquo;&nbsp;{$hash[$ligne["groupware"]]}&nbsp;&raquo;</strong><hr>
					<i style='font-size:13px'>{{$h->TEXT_ARRAY[$ligne["groupware"]]["TEXT"]}}</i>
				</div>
			</td>
		</tr>
		</table>";
		
	}	
	
	$sql="SELECT ID FROM drupal_queue_orders WHERE `ORDER`='REBUILD_GROUPWARE' AND `servername`='{$_GET["servername"]}'";
	if($ligne["ID"]>0){
		$rebuild_groupware=Paragraphe("64-install-soft-grey.png", "{rebuild_groupoffice}", "{scheduled}");
		
	}else{
		$rebuild_groupware=Paragraphe("64-install-soft.png", "{rebuild_groupoffice}", "{rebuild_groupoffice_text}","javascript:RebuildGroupOffice()");
	}
	
	$tr[]=$tpl->_ENGINE_parse_body($rebuild_groupware);
	
	

		
	$tables[]="<table style='width:100%'><tr>";
	$t=0;
	while (list ($key, $line) = each ($tr) ){
			$line=trim($line);
			if($line==null){continue;}
			$t=$t+1;
			$tables[]="<td valign='top'>$line</td>";
			if($t==2){$t=0;$tables[]="</tr><tr>";}
			}
	
	if($t<2){
		for($i=0;$i<=$t;$i++){
			$tables[]="<td valign='top'>&nbsp;</td>";				
		}
	}			
	
$html="<div style='width:100%' id='groupofficediv'>$groupware_text
<table style='width:100%'>
<tr>
<td valign='top'><div id='mysql-status'></div></td>
<td valign='top'>
	". implode("\n",$tables)."
	</td>
	</tr>
</table>
	
	</div>
	<script>
	var x_RebuildGroupOffice= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		if(document.getElementById('main_config_freewebedit')){RefreshTab('main_config_freewebedit');}
	}		
	
	function RebuildGroupOffice(){
		if(confirm('$rebuild_groupware_warning')){
			var XHR = new XHRConnection();
			XHR.appendData('rebuild-groupoffice','yes');
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('groupofficediv');
			XHR.sendAndLoad('$page', 'POST',x_RebuildGroupOffice);
			}
	}	
	
	
	
	</script>
	
	";

	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html);	
	echo $datas;	
	
	
	
}


function rebuild_group_office(){
	$q=new mysql();
	$sql="SELECT ID FROM drupal_queue_orders WHERE `ORDER`='REBUILD_GROUPWARE' AND `servername`='{$_POST["servername"]}'";
	$ligneDrup=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if(!is_numeric($ligneDrup["ID"])){$ligneDrup["ID"]=0;}
	if($ligneDrup["ID"]==0){
		$sql="INSERT INTO drupal_queue_orders(`ORDER`,`servername`) VALUES('REBUILD_GROUPWARE','{$_POST["servername"]}')";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
	}
	
$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");		
}
