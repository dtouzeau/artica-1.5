<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	include_once('ressources/class.cron.inc');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_POST["remove-db"])){removedb();exit;}
	
	
popup();



function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$confirm_remove_zarafa_db=$tpl->javascript_parse_text("{confirm_remove_zarafa_db}");
	$trash=Paragraphe("table-delete-64.png", "{REMOVE_DATABASE}", "{REMOVE_DATABASE_ZARAFA_TEXT}","javascript:REMOVE_DATABASE()");
	
	
	$tr[]=$trash;
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	
	
$time=time();
$html="
<div id='$time'></div>
<div style='width:700px'>". implode("\n",$tables)."</div>	
	
	
<script>	
var x_REMOVE_DATABASE=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>5){alert(tempvalue);}
     	RefreshTab('main_config_zarafa');
      }	
		
	function REMOVE_DATABASE(){
		if(confirm('$confirm_remove_zarafa_db')){
			var XHR = new XHRConnection();
			XHR.appendData('remove-db','1');
			AnimateDiv('$time');
			XHR.sendAndLoad('$page', 'POST',x_REMOVE_DATABASE);
			}
	}
</script>

";

echo $tpl->_ENGINE_parse_body($html);

}
function removedb(){
	$q=new mysql();
	$q->DELETE_DATABASE("zarafa");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("zarafa.php?removeidb=yes");
	$sock->getFrameWork("cmd.php?zarafa-restart-server=yes");
	
}