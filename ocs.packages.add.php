<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ocs.inc');
	
	

	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["FORM_ACTION"])){FORM_ACTION();exit;}
	if(isset($_GET["OCS_FILE_PATH"])){FORM_SAVE();exit;}
	js();
	
	
	
	function js(){
		
	$page=CurrentPageName();
	$tpl=new templates();
	
	$prefix=str_replace(".","_",$page);
	
	$title=$tpl->_ENGINE_parse_body("{add_new_package}","domains.edit.user.php");
	$html="
	
	function {$prefix}LoadMain(){
		YahooWin6('550','$page?popup=yes','$title');
		
	}


	{$prefix}LoadMain();";
	
	echo $html;		
	}
	
	
function popup(){
	$page=CurrentPageName();
	$actions=array(null=>"{select}","STORE"=>"{STORE}","EXECUTE"=>"{EXECUTE}","LAUNCH"=>"{LAUNCH}");
	
	
	$html="
	<div style='font-size:14px;margin:8px'>{OCS_ADD_PACKAGE_EXPLAIN}</div>
	
	<table style='width:100%'>
		<tr>
			<td width=1% style='font-size:13px' class=legend nowrap>{package_name}:</td>
			<td width=99% colspan=2>". Field_text("OCS_PACKAGE_NAME","",'width:85%;font-size:13px;padding:3px'). " </td>
			
		</tr>	
		<tr>
			<td width=1% style='font-size:13px' class=legend nowrap>{file_path}:</td>
			<td width=99%>". Field_text("OCS_FILE_PATH","",'width:85%;font-size:13px;padding:3px'). " </td>
			<td width=1%><input type='button' value='{browse}&nbsp;&raquo;' OnClick=\"javascript:Loadjs('tree.php?select-file=zip&target-form=OCS_FILE_PATH');\"></td>
		</tr>
		<tr>
			<td width=1% style='font-size:13px' class=legend nowrap>{action}:</td>	
			<td>". Field_array_Hash($actions,"ACTION",null,"OCSActionPackSelect()",null,0,"font-size:13px;padding:3px")."</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<hr>
	<div style='width:100%' id='FORM_ACTION'></div>
	<script>
		function OCSActionPackSelect(){
			LoadAjax('FORM_ACTION','$page?FORM_ACTION='+document.getElementById('ACTION').value);
		}
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function FORM_ACTION(){
	
	switch ($_GET["FORM_ACTION"]) {
		case "STORE":FORM_ACTION_STORE();break;
		
		default:
			;
		break;
	}
	
}

function VARIABLES_ENV(){
		$variables=array(null=>"{none}","%TMP%"=>"%TMP%","%TEMP%"=>"%TEMP%","%programfiles%"=>"%programfiles%",
	"%ALLUSERSPROFILE%"=>"%ALLUSERSPROFILE%","%APPDATA%"=>"%APPDATA%","%HOMEPATH%"=>"%HOMEPATH%",
	"%SystemDrive%"=>"%SystemDrive%","%SystemRoot%");
		return $variables;
}

function FORM_ACTION_STORE(){
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1% style='font-size:13px' class=legend nowrap>{environment_variables}:</td>
		<td>". Field_array_Hash(VARIABLES_ENV(),"ENV",null,null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td valign='top' width=1% style='font-size:13px' class=legend nowrap>{path}:</td>
		<td>". Field_text("STORE_PATH",null,"font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>
			". button("{add}","SaveOcsAddPckgForm()")."</td>
	</tr>
	
	</table>
	
	<script>
		var x_SaveOcsAddPckgForm=function (obj) {
			var results=obj.responseText;
			if (results.length>0){
				alert(results);
				OCSActionPackSelect();
				return;
			}
			YahooWin6Hide();
			RefreshTab('main_config_ocsi');
			
		}
		
		function SaveOcsAddPckgForm(){
				var XHR = new XHRConnection();				
				XHR.appendData('OCS_PACKAGE_NAME',document.getElementById('OCS_PACKAGE_NAME').value);
				XHR.appendData('OCS_FILE_PATH',document.getElementById('OCS_FILE_PATH').value);
				XHR.appendData('ACTION',document.getElementById('ACTION').value);
				XHR.appendData('STORE_PATH',document.getElementById('STORE_PATH').value);
				document.getElementById('FORM_ACTION').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_SaveOcsAddPckgForm);
		}		
	
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		

}

function FORM_SAVE(){
	$sock=new sockets();
	
	$OCSCertInfos=unserialize(base64_decode($sock->GET_INFO("OCSCertInfos")));
	$servername=$OCSCertInfos["OCSCertServerName"];
	$domainname=$OCSCertInfos["OCSCertDomainName"];
	$OCSWebPort=$sock->GET_INFO("OCSWebPort");
	$OCSWebPortSSL=$sock->GET_INFO("OCSWebPortSSL");
	if($OCSWebPort==null){$OCSWebPort=9080;}
	if($OCSWebPortSSL==null){$OCSWebPortSSL=$OCSWebPort+50;}	
	
	if($servername==null){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{OCS_PACKAGE_NO_CERT_SERVER}");
		return;
	}
	
	if($domainname<>null){
		$servername=$servername.".$domainname";
	}
	
	
	$file_source=$_GET["OCS_FILE_PATH"];
	$digest=$sock->getFrameWork("cmd.php?filemd5=".base64_encode($file_source));
	$PRI=2;
	$FILEID=time();
	$ACTION=$_GET["ACTION"];
	$OCS_PACKAGE_NAME=$_GET["OCS_PACKAGE_NAME"];
	
	writelogs("OCS-PACKAGES:: $FILEID PATH={$_GET["STORE_PATH"]}, NAME=$OCS_PACKAGE_NAME,ACTION=$ACTION",__FUNCTION__,__FILE__,__LINE__);
	if(isset($_GET["STORE_PATH"])){$PATH=$_GET["STORE_PATH"];}
	
	$FRAGS_BYTES=100*1024;
	// taille des fragments=102400 bytes.
	
	
	$FILE_SIZE=$sock->getFrameWork("cmd.php?filesize=$file_source");
	if($FILE_SIZE<1){
		echo "Failed!\n File size=0\n";return;
	}
	
	if($file_source==null){
		echo "Failed!\n File source=0 length\n";return;
	}	
	
	// nombre de fragments= $FILE_SIZE en bytes / $FRAGS_BYTES
	$FRAGS=$FILE_SIZE/$FRAGS_BYTES;
	$FRAGS=round($FRAGS, 0, PHP_ROUND_HALF_UP);
	// si frags<2 1 seul fragment et on copie l'ensemble du fichier. 
	
	if($FRAGS<1){$FRAGS=1;}
	
		//En renseigne la base Mysql; si erreur, le fichier n'est pas ajouté
		
		$sql="INSERT INTO download_available (FILEID,NAME,PRIORITY,FRAGMENTS,SIZE,OSNAME)
		VALUES('$FILEID','$OCS_PACKAGE_NAME','2','$FRAGS','$FILE_SIZE','WINDOWS')";
		
		$q=new mysql();
		$q->QUERY_SQL($sql,"ocsweb");
		if(!$q->ok){
			echo $q->mysql_error;
			return;
		}

		$sql="INSERT INTO download_enable (FILEID,INFO_LOC,PACK_LOC,CERT_PATH,CERT_FILE)
		VALUES('$FILEID','$servername:$OCSWebPortSSL/download','$servername:$OCSWebPort/download','INSTALL_PATH','INSTALL_PATH/cacert.pem');";
		
		$q=new mysql();
		$q->QUERY_SQL($sql,"ocsweb");
		if(!$q->ok){
			echo $q->mysql_error;
			return;
		}		
	
	
	if($FRAGS==1){
		$sock->getFrameWork("cmd.php?ocs-package-cp=yes&filesource=". base64_encode($file_source)."&FILEID=$FILEID");
	}else{
		$sock->getFrameWork("cmd.php?ocs-package-frag=yes&filesource=". base64_encode($file_source)."&FILEID=$FILEID&nbfrags=$FRAGS");
	}
	
	//si EXECUTE, resigner COMMAND
		
	$PATH_CLEANED=clean($PATH);
	$COMMAND_CLEANED=clean($COMMAND);
	
	
		$info = "<DOWNLOAD ID=\"$FILEID\" ".
		"PRI=\"$PRI\" ".
		"ACT=\"$ACTION\" ".
		"DIGEST=\"$digest\" ".		
		"PROTO=\"HTTP\" ".
		"FRAGS=\"$FRAGS\" ".
		"DIGEST_ALGO=\"MD5\" ".
		"DIGEST_ENCODE=\"Hexa\" ".
		"PATH=\"$PATH_CLEANED\" ".
		"NAME=\"\" ".
		"COMMAND=\"$COMMAND_CLEANED\" ".
		"NOTIFY_USER=\"\" ".
		"NOTIFY_TEXT=\"\" ".
		"NOTIFY_COUNTDOWN=\"\" ".
		"NOTIFY_CAN_ABORT=\"0\" ".
		"NOTIFY_CAN_DELAY=\"0\" ".
		"NEED_DONE_ACTION=\"0\" ".		
		"NEED_DONE_ACTION_TEXT=\"\" ".		
		"GARDEFOU=\"rien\" />\n";	
		
		@file_put_contents(dirname(__FILE__)."/ressources/logs/$FILEID.info",$info);
		//on envoi le fichier 
		$sock->getFrameWork("cmd.php?ocs-package-cpinfo=yes&FILEID=$FILEID");


		
}

function clean( $txt ) {
		$cherche = array(	"&"  , "<"  , ">"  , "\""    , "'",'\\\n','\\\r');
		$replace = array( "&amp;","&lt;","&gt;", "&quot;", "&apos;","\n","\r");
		return str_replace($cherche, $replace, $txt);		
	}

?>