<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf.inc');
	if(!isset($_GET["ou"])){
		if($_SESSION["ou"]<>null){$_GET["ou"]=base64_encode($_SESSION["ou"]);}
		if($_GET["ou"]==null){$_GET["ou"]=base64_encode("null");}
	}

	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["QueryLDAPDBBrowse"])){QueryLDAPDBBrowse();exit;}
js();
	
function js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body("{search_remote_database}");
$html="RTMMail(590,'$page?popup=yes','$title');";
echo $html;

}

function popup(){
$page=CurrentPageName();	
$tpl=new templates();	
	$html="
	<table style='width:100%'>
	<tr>
		<td width=1%><img src='img/databases-search-net-128.png' id='databases-search-net-128'></td>
		<td valign='top'>
			<div class=explain>{adSearchPopupHowto}</div>
			<table style='width:100%'>
				<tr>
					<td class=legend>{server_host}:</td>
					<td>". Field_text("ServerQueryADHost","","width:220px;font-size:13px","script:QueryLDAPDBBrowseCheck(event)")."</td>
					<td>". button("{browse}","QueryLDAPDBBrowse()")."</td>
				</tr>
			</table>
		</td>
		</tr>
	</table>
		
	<div id='QueryLDAPDBBrowseResults' style='widht:100%;height:240px;overflow:auto'></div>
	
	
	<script>
var X_QueryLDAPDBBrowse= function (obj) {
		var results=trim(obj.responseText);
		document.getElementById('databases-search-net-128').src='img/databases-search-net-128.png';   
		document.getElementById('QueryLDAPDBBrowseResults').innerHTML=results;
	}		
function QueryLDAPDBBrowse(){
		var XHR = new XHRConnection();
		XHR.appendData('QueryLDAPDBBrowse',document.getElementById('ServerQueryADHost').value);
		document.getElementById('databases-search-net-128').src='img/wait_verybig.gif';   
		XHR.sendAndLoad('$page', 'GET',X_QueryLDAPDBBrowse);
		
	}
function QueryLDAPDBBrowseCheck(e){
	if(checkEnter(e)){QueryLDAPDBBrowse();}
}
	</script>
		
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function QueryLDAPDBBrowse(){
	$ldap_host=$_GET["QueryLDAPDBBrowse"];
	$tpl=new templates();
	$con=@ldap_connect($ldap_host, 389) ;
	if(!$con){
			$errornumber=ldap_errno($con);
			$error_text=ldap_err2str($con);
			echo $tpl->_ENGINE_parse_body("<H3 style='color:red'>{failed}: $ldap_host:389 ($error_text)</H3>");
			return;
	}
	
	
		
		ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
		ldap_set_option($con, LDAP_OPT_REFERRALS, 0); 		 
		$ldapbind=@ldap_bind($con);

if(!$ldapbind){
			$errornumber=ldap_errno($con);
			$error_text=ldap_err2str($errornumber);
			
			switch ($errornumber) {
					case 0x31:
						$error=$error . "Bad username or password. Please try again.</li>";
						break;
					case 0x32:
						$error=$error . "Insufficient access rights.</li>";
						break;
					case 81:
						$error=$error . "Unable to connect to the LDAP server\n $ldap_host:389, <br>please,verify if ldap daemon is running or the ldap server address";
						break;						
					case -1:
						$error=$error . "it seems that Artica could not connect to the server as anonymous";
						break;
					default:
						$error=$error . "Could not bind to the LDAP server $error_text";
 				}			

		echo $tpl->_ENGINE_parse_body("<H3 style='color:red'>{failed} Error number $errornumber,$error_text<br>$error</H3>");
		return;
	}

		
		$attr=array('server','base');
	//	$attr=array('server','root_dse_attributes');
	$attr=array("defaultnamingcontext");
	$attr=array();
		$pattern="(&(objectClass=*))";
		$sr =ldap_read($con,"",$pattern,$attr);
		if(!$sr){
			$errornumber=ldap_errno($con);
			$error_text=ldap_err2str($errornumber);	
			echo $tpl->_ENGINE_parse_body("<H3 style='color:red'>{failed} Error number $errornumber,$error_text</H3>");
			ldap_close($con);	
			return;	
		}
		
	$html="
	<p>&nbsp;</p>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
				<th colspan=2>{you_should_use_one_of_this_dn}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
		
	$array[]="CN=Administrator,CN=Users";
	$array[]="CN=Administrateur,CN=Users";
	
		$hash=ldap_get_entries($con,$sr);	
		for($i=0;$i<$hash[0]["defaultnamingcontext"]["count"];$i++){
			$defaultnamingcontext=$hash[0]["defaultnamingcontext"][$i];
			reset($array);
			while (list ($index, $prefix) = each ($array) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$html=$html."
				<tr class=$classtr>
					<td width=1%>". imgtootltip("24-connect.png","{choose}","AdSearchChoose('$prefix,$defaultnamingcontext','$defaultnamingcontext')")."</td>
					<td style='font-size:14px'>$prefix,$defaultnamingcontext</td>
				</tr>
			
			";
			}
			
			
		}

		$html=$html."
		</table>
<script> 
	function AdSearchChoose(DnADM,Base){
		var server=document.getElementById('ServerQueryADHost').value;
		if(document.getElementById('remote_server')){document.getElementById('remote_server').value=server;}
		if(document.getElementById('server_host')){document.getElementById('server_host').value=server;}		
		if(document.getElementById('search_base')){document.getElementById('search_base').value=Base;}	
		if(document.getElementById('bind_dn')){document.getElementById('bind_dn').value=DnADM;}
		RTMMailHide();
	
	}

</script>
";
		
	echo $html;
	
}