<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.tcpip.inc');
	
	$user=new usersMenus();

	if($user->SQUID_INSTALLED==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["edit-proxy-parent"])){parent_config();exit;}
	if(isset($_GET["SaveParentProxy"])){parent_save();exit;}
	if(isset($_GET["edit-proxy-parent-options"])){parent_options_popup();exit;}
	if(isset($_GET["edit-proxy-parent-options-explain"])){parent_options_explain();exit;}
	if(isset($_GET["extract-options"])){extract_options();exit;}
	if(isset($_POST["AddSquidParentOptionOrginal"])){construct_options();exit;}
	if(isset($_POST["DeleteSquidOption"])){delete_options();exit;}
	if(isset($_GET["parent-list"])){popup_list();exit;}
	if(isset($_GET["DeleteSquidParent"])){parent_delete();exit;}
	if(isset($_GET["EnableParentProxy"])){EnableParentProxy();exit;}
	
		js();
	
function js(){

	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{squid_parent_proxy}");
	$title2=$tpl->_ENGINE_parse_body("{edit_squid_parent_parameters}");
	$title3=$tpl->_ENGINE_parse_body("{squid_parent_options}");
	$html="
		function SquidParentProxyStart(){
			YahooWin3('650','$page?popup=yes','$title');
		
		}
		
		function EditSquidParent(ID){
			YahooWin4('520','$page?edit-proxy-parent='+ID,'$title2');
		}
		
		
		function DeleteSquidParent(ID){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteSquidParent',ID);
			XHR.sendAndLoad('$page', 'GET',x_EditSquidParentSave);
		}
		
		
		var x_EditSquidParentSaveReturn= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin4Hide();
			RefreshParentList();
		}			
		
		function EditSquidParentSave(ID){
			var XHR = new XHRConnection();
			XHR.appendData('ID',ID);
			XHR.appendData('SaveParentProxy',ID);
			XHR.appendData('servername',document.getElementById('servername').value);
			XHR.appendData('server_port',document.getElementById('server_port').value);
			XHR.appendData('server_type',document.getElementById('server_type').value);
			XHR.appendData('icp_port',document.getElementById('icp_port').value);
			XHR.appendData('options',document.getElementById('SquidParentOptions').value);
			document.getElementById('SquidParentOptions').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_EditSquidParentSaveReturn);			
		
		}
		
		function ExtractSquidOptions(){
			YahooWin5('500','$page?edit-proxy-parent-options='+document.getElementById('SquidParentOptions').value,'$title3');
		}
		
		function FillSquidParentOptions(){
			LoadAjax('squid_parent_options_filled','$page?edit-proxy-parent-options-explain='+document.getElementById('squid_parent_options_f').value);
		}
		
		var x_AddSquidOption= function (obj) {
			var results=obj.responseText;
			if(results.length>0){
				document.getElementById('SquidParentOptions').value=results;
				RemplitLesOptionsParent();
				YahooWin5Hide();
			}
			
		}			
		
		function AddSquidOption(){
			var XHR = new XHRConnection();
			XHR.appendData('AddSquidParentOptionOrginal',document.getElementById('SquidParentOptions').value);
			XHR.appendData('key',document.getElementById('squid_parent_options_f').value);
			if(document.getElementById('parent_proxy_add_value')){
				XHR.appendData('value',document.getElementById('parent_proxy_add_value').value);
			}
			
			XHR.sendAndLoad('$page', 'POST',x_AddSquidOption);
		}
		
		function DeleteSquidOption(key){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteSquidOption',key);
			XHR.appendData('array',document.getElementById('SquidParentOptions').value);
			XHR.sendAndLoad('$page', 'POST',x_AddSquidOption);
		}
		
		function RemplitLesOptionsParent(){
			LoadAjax('squid_parents_options_list','$page?extract-options='+document.getElementById('SquidParentOptions').value);
		}
		
		function RefreshParentList(){
			LoadAjax('squid-parents-list','$page?parent-list=yes');
		}

		function EnableParentProxy(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableParentProxy').checked){
				XHR.appendData('EnableParentProxy',1);
			}else{
				XHR.appendData('EnableParentProxy',0);
			}
			
			XHR.sendAndLoad('$page', 'GET');
			
		}
		
		SquidParentProxyStart();";
		
		echo $html;

}

function parent_delete(){
	$ID=$_GET["DeleteSquidParent"];
	
	$sql="DELETE FROM squid_parents WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
	
}

function parent_save(){
	$ID=$_GET["ID"];
	if(strlen(trim($_GET["icp_port"]))==null){$_GET["icp_port"]=0;}
	$sql_add="INSERT INTO squid_parents (servername,server_port,server_type,icp_port,options)
	VALUES('{$_GET["servername"]}','{$_GET["server_port"]}','{$_GET["server_type"]}','{$_GET["icp_port"]}','{$_GET["options"]}')";
	
	$sql_edit="UPDATE squid_parents SET 
		servername='{$_GET["servername"]}',
		server_port='{$_GET["server_port"]}',
		server_type='{$_GET["server_type"]}',
		icp_port='{$_GET["icp_port"]}',
		options='{$_GET["options"]}' WHERE ID=$ID";
	
	
	$q=new mysql();
	$sql=$sql_add;
	if($ID>0){$sql=$sql_edit;}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error."\n$sql";
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
	
}

/*
 * CREATE TABLE `artica_backup`.`squid_parents` (
`ID` INT( 3 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`servername` VARCHAR( 255 ) NOT NULL ,
`server_port` INT( 3 ) NOT NULL ,
`server_type` VARCHAR( 50 ) NOT NULL ,
`icp_port` INT( 3 ) NOT NULL ,
`options` TEXT NOT NULL ,
INDEX ( `servername` )
) ENGINE = MYISAM ;

    *  parent
    * sibling
    * multicast

 */

function popup(){

	$squid=new squidbee();
	
	$html="
	<table style='width:100%'>
	<tr>
	<td width=99%><div class=explain>{squid_parent_proxy_explain}</div></td>
	<td width=1% valign='top'>".imgtootltip("48-net-server-add.png","{add_a_parent_proxy}","EditSquidParent(0)")."</td>
	</tr>
	</table>
	
	
	
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{enable_squid_parent}:</td>
		<td>". Field_checkbox("EnableParentProxy",1,$squid->EnableParentProxy,"EnableParentProxy()")."</td>
	</tr>
	</table>	
	<hr>
	<div id='squid-parents-list' style='padding:3px;border:1px dotted #CCCCCC;height:250px;overflow:auto'></div>
	<script>
		RefreshParentList();
	</script>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function popup_list(){
	$sql="SELECT * FROM squid_parents ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error";return;}
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
	<tr>
		<th>". imgtootltip("refresh-24.png","{refresh}","RefreshParentList()")."</th>
		<th>{servername}</th>
		<th>{listen_port}</th>
		<th>{server_type}</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ahref="<a href=\"javascript:blur();\" OnClick=\"javascript:EditSquidParent({$ligne["ID"]})\" style='font-size:16px;text-decoration:underline;font-weight:bold'>";
		$html=$html."<tr class=$classtr>
		<td width=1%>". imgtootltip('32-net-server-add.png',"{edit}","EditSquidParent({$ligne["ID"]})")."</td>
		<td style='font-size:16px;font-weight:bold'>$ahref{$ligne["servername"]}</a></td>
		<td width=1% style='font-size:16px;font-weight:bold'>$ahref{$ligne["server_port"]}</a></td>
		<td width=1% style='font-size:16px;font-weight:bold'>$ahref{$ligne["server_type"]}</a></td>
		<td width=1%>". imgtootltip('delete-32.png',"{delete}","DeleteSquidParent({$ligne["ID"]})")."</td>
		</tr>
		";	
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function parent_config(){
	
	$ID=$_GET["edit-proxy-parent"];
	$array_type["parent"]="parent";
	$array_type["sibling"]="sibling";
	$array_type["multicast"]="multicast";
	$q=new mysql();
	$sql="SELECT * FROM squid_parents WHERE ID=$ID";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$button="{apply}";
	$addoptions=imgtootltip('plus-24.png','{squid_parent_options}',"ExtractSquidOptions()","right");
	
	if($ID<1){$button="{add}";$addoptions=null;}
	if(strlen(trim($ligne["icp_port"]))==0){$ligne["icp_port"]=0;}
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
	<img src='img/server-redirect-96.png'>
	</td>
	<td valign='top'>
	<input type='hidden' id='SquidParentOptions' name='SquidParentOptions' value=\"{$ligne["options"]}\">
	<div id='EditSquidParentSaveID'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{servername}:</td>
		<td>". Field_text("servername",$ligne["servername"],"font-size:13px;padding:3px;width:220px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}:</td>
		<td>". Field_text("server_port",$ligne["server_port"],"font-size:13px;padding:3px;width:50px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{server_type}:</td>
		<td>". Field_array_Hash($array_type,"server_type",$ligne["server_type"],null,null,0,"font-size:13px")."</td>
		<td>". help_icon("{squid_parent_sibling_how_to}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{icp_port}:</td>
		<td>". Field_text("icp_port",$ligne["icp_port"],"font-size:13px;padding:3px;width:50px")."</td>
		<td>". help_icon("{icp_port_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px' valign='top'>{options}:</td>	
		<td colspan=2>
			<div style='text-align:right;width:100%;'>$addoptions</div>
			<div id='squid_parents_options_list' style='width:100%;height:100px;overflow:auto;padding:3px;border:1px dotted #CCCCCC'></div>	
		</td>	
			
	
	<tr>
	
		<td colspan=3 align='right'>
		<hr>
		". button("$button","EditSquidParentSave($ID)")."
	</td>
	</table>
	</div>
	</td>
	</tr>
	</table>
	<script>
		RemplitLesOptionsParent();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function parent_options_popup(){
	
	$array=unserialize(base64_decode($_GET["edit-proxy-parent-options"]));
	$options[null]="{select}";
	$options[base64_encode("proxy-only")]="proxy-only";
	$options[base64_encode("Weight=n")]="Weight=n";
	$options[base64_encode("ttl=n")]="ttl=n";
	$options[base64_encode("no-query")]="no-query";
	$options[base64_encode("default")]="default";
	$options[base64_encode("round-robin")]="round-robin";
	$options[base64_encode("multicast-responder")]="multicast-responder";
	$options[base64_encode("closest-only")]="closest-only";
	$options[base64_encode("no-digest")]="no-digest";
	$options[base64_encode("no-netdb-exchange")]="no-netdb-exchange";
	$options[base64_encode("no-delay")]="no-delay";
	$options[base64_encode("login=user:password")]="login=user:password";
	$options[base64_encode("connect-timeout=nn")]="connect-timeout=nn";
	$options[base64_encode("digest-url=url")]="digest-url=url";
	//$options[base64_encode("ssl")]="ssl";
	
	$html="
	<table style='width:100%'>
	<tr>	
		<td class=legend style='font-size:13px'>{squid_parent_options}:</td>
		<td>". Field_array_Hash($options,"squid_parent_options_f",base64_encode("proxy-only"),"FillSquidParentOptions()",null,0,"font-size:14px;padding:5px")."</td>
	</tr>
	</table>
	<div id='squid_parent_options_filled'></div>
	<script>
		FillSquidParentOptions();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function parent_options_explain(){
	if($_GET["edit-proxy-parent-options-explain"]==null){return null;}
	$options[base64_encode("proxy-only")]="{parent_options_proxy_only}";
	$options[base64_encode("Weight=n")]="{parent_options_proxy_weight}";
	$options[base64_encode("ttl=n")]="{parent_options_proxy_ttl}";
	$options[base64_encode("no-query")]="{parent_options_proxy_no_query}";
	$options[base64_encode("default")]="{parent_options_proxy_default}";
	$options[base64_encode("round-robin")]="{parent_options_proxy_round_robin}";
	$options[base64_encode("multicast-responder")]="{parent_options_proxy_multicast_responder}";
	$options[base64_encode("closest-only")]="{parent_options_proxy_closest_only}";
	$options[base64_encode("no-digest")]="{parent_options_proxy_no_digest}";
	$options[base64_encode("no-netdb-exchange")]="{parent_options_proxy_no_netdb_exchange}";
	$options[base64_encode("no-delay")]="{parent_options_proxy_no_delay}";
	$options[base64_encode("login=user:password")]="{parent_options_proxy_login}";
	$options[base64_encode("connect-timeout=nn")]="{parent_options_proxy_connect_timeout}";
	$options[base64_encode("digest-url=url")]="{parent_options_proxy_digest_url}";	
	
	$options_forms[base64_encode("digest-url=url")]=true;
	$options_forms[base64_encode("connect-timeout=nn")]=true;
	$options_forms[base64_encode("ttl=n")]=true;
	$options_forms[base64_encode("Weight=n")]=true;
	$options_forms[base64_encode("login=user:password")]=true;
	
	if($options_forms[$_GET["edit-proxy-parent-options-explain"]]){
		$form="
		<table style='width:100%'>
		<tr>
			<td class=legend style='font-size:14px'>". base64_decode($_GET["edit-proxy-parent-options-explain"]).":</td>
			<td>". Field_text("parent_proxy_add_value",null,"font-size:14px;padding:3px")."</td>
		</tr>
		</table>";
		
	}
	
	$html="<div style='font-size:14px;margin:15px;'>{$options[$_GET["edit-proxy-parent-options-explain"]]}</div>
	$form
	<div style='text-align:right'><hr>". button("{add} ".base64_decode($_GET["edit-proxy-parent-options-explain"]),"AddSquidOption()")."</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function extract_options(){
	$array=unserialize(base64_decode($_GET["extract-options"]));
	if(!is_array($array)){return  null;}
	$html="<table style='width:100%'>";
		
		while (list($num,$val)=each($array)){	
			if(strlen($val)>10){$val=substr($val,0,7)."...";}
			$html=$html."<tr ". CellRollOver().">
			<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:13px'>$num</strong></td>
			<td width=1% nowrap><strong style='font-size:13px'>$val</strong></td>
			<td width=1% valign='middle'>". imgtootltip("ed_delete.gif","{delete}","DeleteSquidOption('$num')")."</td>
			</tr>";
			
		}
		
		$html=$html."</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
	
	
}
function construct_options(){
	
	$based=base64_decode($_POST["AddSquidParentOptionOrginal"]);
	$key=base64_decode($_POST["key"]);
	
	writelogs("Receive datas=\"$based\" decoded key:\"$key\"",__FUNCTION__,__FILE__,__LINE__);
	if(preg_match("#(.+?)=#",$key,$re)){
		$key=$re[1];
	}
	
	
	if($based==null){
		$array[$key]=$_POST["value"];
		writelogs("send ". serialize($array),__FUNCTION__,__FILE__,__LINE__);
		echo base64_encode(serialize($array));
		return;
	}
	
	
	$array=unserialize($based);
	if(!is_array($array)){
		writelogs("unable to unserialize $based",__FUNCTION__,__FILE__,__LINE__);
		$array=array();
		
		
	}
	$array[$key]=$_POST["value"];
	
	while (list($num,$val)=each($array)){	
		if(trim($num)==null){continue;}
		$f[$num]=$val;
	}
	
	
	writelogs("send ". serialize($f),__FUNCTION__,__FILE__,__LINE__);
	echo base64_encode(serialize($f));
	
}

function delete_options(){
	$based=base64_decode($_POST["array"]);
	$key=$_POST["DeleteSquidOption"];
	$array=unserialize($based);
	if(!is_array($array)){
		writelogs("unable to unserialize $based",__FUNCTION__,__FILE__,__LINE__);
		$array=array();
		}
	unset($array[$key]);
	echo base64_encode(serialize($array));	
}

function EnableParentProxy(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ArticaSquidParameters=$sock->GET_INFO('ArticaSquidParameters');
	$ini->loadString($ArticaSquidParameters);
	$ini->_params["NETWORK"]["EnableParentProxy"]=$_GET["EnableParentProxy"];
	$sock->SET_INFO("ArticaSquidParameters",$ini->toString());
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
	
}



?>