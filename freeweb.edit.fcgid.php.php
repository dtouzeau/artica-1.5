<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_POST["servername"])){SavePHPVals();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["phpvaluesserver"])){phpvaluesserver();exit;}
	
	
	if(isset($_POST["CacheDisableDel"])){CacheDisableDel();exit;}
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{$_GET["servername"]}::{php_values}");
	$html="YahooWin6('550','$page?popup=yes&servername={$_GET["servername"]}','$title');";
	echo $html;
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<div id='phpvaluesserver' style='width:100%;height:450px;overflow:auto'></div>
	
	<script>
		function refreshPHPVALUES(){
			LoadAjax('phpvaluesserver','$page?phpvaluesserver=yes&servername={$_GET["servername"]}');
		}
	refreshPHPVALUES();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function phpvaluesserver(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$freeweb=new freeweb($_GET["servername"]);
	$freeweb->php_defaults();
	
$BannKeys["mysql.default_password"]=true;
$BannKeys["mysql.default_user"]=true;
$BannKeys["mysql.default_host"]=true;
$BannKeys["mysql.default_socket"]=true;
$BannKeys["mysql.default_port"]=true;
$BannKeys["mysqli.default_socket"]=true;
$BannKeys["mysqli.default_port"]=true;
$BannKeys["mysqli.default_host"]=true;
$BannKeys["mysqli.default_user"]=true;
$BannKeys["mysqli.default_pw"]=true;
$BannKeys["session.save_path"]=true;
$BannKeys["session.save_handler"]=true;
$BannKeys["upload_tmp_dir"]=true;
$BannKeys["cgi.fix_pathinfo"]=true;
$BannKeys["cgi.force_redirect"]=true;
$BannKeys["extension_dir"]=true;
$BannKeys["include_path"]=true;
$BannKeys["error_log"]=true;	
	
	$html="<table style='width:98%' class=form><tbody>";
	while (list ($key, $value) = each ($freeweb->Params["PHP_VALUES"]) ){
		if($BannKeys[$key]){continue;}
		$c++;
		$tr[]="
		<tr>
			<td class=legend nowrap>$key:</td>
			<td>". Field_text($key,$value,"font-size:14px;width:190px;padding:3px")."</td>
		</tr>";
		
		$js[]="XHR.appendData('$key',document.getElementById('$key').value);";
		
		if($c>11){
			$tr[]="<tr>
					<td colspan=2 align='right'><hr>". button("{apply}","SavePHPValues()")."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'>&nbsp;</td>
				</tr>";
			$c=0;
		}
		
		
	}
	
	$html=$html.@implode("\n", $tr)."
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SavePHPValues()")."</td>
	</tr>
	
	</tbody>
	</table>
	
	<script>
		var x_SavePHPValues=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			refreshPHPVALUES();
		}		
	
		function SavePHPValues(){
			var XHR = new XHRConnection();
			".@implode("\n", $js)."
			
			
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('phpvaluesserver');
    		XHR.sendAndLoad('$page', 'POST',x_SavePHPValues);
		}	

	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function SavePHPVals(){
	$freeweb=new freeweb($_POST["servername"]);
	while (list ($num, $ligne) = each ($_POST) ){$freeweb->Params["PHP_VALUES"][$num]=$ligne;}	
	$freeweb->SaveParams();
}

