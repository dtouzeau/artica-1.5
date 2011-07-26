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
	
	if(isset($_POST["RefreshDrupalInfos"])){RefreshDrupalInfos();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	
	if(isset($_POST["add-uid"])){members_add();exit;}
	if(isset($_POST["del-uid"])){members_del();exit;}	
	if(isset($_POST["enable-uid"])){members_enable();exit;}
	if(isset($_POST["priv-uid"])){members_privs();exit;}
	
	if(isset($_GET["members"])){members();exit;}
	if(isset($_GET["uid-role-popup"])){members_role();exit;}
	
	if(isset($_GET["modules"])){modules();exit;}
	if(isset($_POST["RefreshModulesInfos"])){modules_infos();exit;}
	
page();	
	
function page(){
	$page=CurrentPageName();
	$time=time();
	$html="<div id='drupal-$time' style='width:100%'></div>
	<script>
		LoadAjax('drupal-$time','$page?tabs=yes&servername={$_GET["servername"]}');
	</script>
	";
	echo $html;
}	


function tabs(){
	$tpl=new templates();	
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["members"]='{members}';
	$array["modules"]='{modules}';
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_drupal style='width:100%;height:590px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_drupal\").tabs();});
		</script>";	
	
	
}

function members(){
	$tpl=new templates();	
	$delete_this_user_text=$tpl->javascript_parse_text("{delete_this_user_text}");
	$page=CurrentPageName();
	
	$q=new mysql();
	$sql="SELECT * FROM drupal_queue_orders WHERE `servername`='{$_GET["servername"]}'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["value"]<>null){$data=unserialize(base64_decode($ligne["value"]));}
		$ORDERS[$ligne["ORDER"]][$data["USER"]]=true;
	}
	
	
	$sql="SELECT DrupalInfos from freeweb WHERE servername='{$_GET["servername"]}'";
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$DrupalInfos=unserialize(base64_decode($ligne["DrupalInfos"]));
	$array=$DrupalInfos["USERS"];	
	$privileges=$tpl->_ENGINE_parse_body("{privileges}");

$html="<div id='drupal2animate'>

<table style='width:100%' class=form>
<tr>
	<td class=legend>{member}</td>
	<td>". Field_text("member-drupal-add",null,"font-size:14px;padding:3px",null,null,null,false,"MemberDrupalAddCheck(event)")."</td>
	<tdwidth=1%><input type='button' OnClick=\"javascript:Loadjs('user.browse.php?field=member-drupal-add&YahooWin=4');\" value='{browse}...'></td>
	<td width=1%>". button("{add}", "MemberDrupalAdd()")."</td>
</tr>
</table>
<hr>


<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>". imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_drupal')")."</th>
	<th>{members}</th>
	<th>{privileges}</th>
	<th>{enable}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($uid, $ligne) = each ($array) ){
		if($ligne["NAME"]==null){$ligne["NAME"]="{anonymous}";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$hrfroles="<a href=\"javascript:blur();\" OnClick=\"javascript:DrupalRole('$uid')\" style='font-size:14px;text-decoration:underline'>";
		$roles="$hrfroles{$ligne["INFOS"]["USER_ROLES"]}";
		$color="black";
		if(strpos($roles, ",")>0){
			$tbl=explode(",", $roles);
			$roles=null;
			while (list ($ind, $ro) = each ($tbl) ){
				$roles=$roles."<li>$hrfroles$ro</a></li>";
			}
		}
		$md=md5($ligne["NAME"]);
		if($ligne["INFOS"]["USER_STATUS"]=="active"){$ligne["INFOS"]["USER_STATUS"]=1;}else{$ligne["INFOS"]["USER_STATUS"]=0;}
		$delete=imgtootltip("delete-32.png","{delete}","MemberDrupalDelete('{$ligne["NAME"]}')");
		$enable=Field_checkbox($md, 1,$ligne["INFOS"]["USER_STATUS"],"DrupalMemberActive('{$ligne["NAME"]}','$md')");
		
		
		if(isset($ORDERS["DELETE_USER"][$ligne["NAME"]])){
			$delete=imgtootltip("folder-tasks-32.png","{scheduled}");
			$color="#CCCCCC";
			$enable="&nbsp;";
			$roles="{delete}:{scheduled}";
		}
		
		if(isset($ORDERS["PRIV_USER"][$ligne["NAME"]])){
			$color="#CCCCCC";
			$roles="{apply}:{scheduled}";
		}
		

		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/user-32.png'></td>
		<td style='font-size:14px'><strong style='color:$color'>{$ligne["NAME"]}</strong></td>
		<td style='font-size:14px'><strong style='color:$color'>$roles</strong></td>
		<td width=1%>$enable</td>
		<td width=1%>$delete</td>
		</tr>
	";
	}
	
	if(isset($ORDERS["CREATE_USER"])){
		while (list ($uid, $vals) = each ($ORDERS["CREATE_USER"]) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$color="#CCCCCC";
			$enable="&nbsp;";
			$roles="{add}:{scheduled}";
			$html=$html."<tr class=$classtr>
			<td width=1%><img src='img/user-32.png'></td>
			<td style='font-size:14px'><strong style='color:$color'>$uid</strong></td>
			<td style='font-size:14px'><strong style='color:$color'>$roles</strong></td>
			<td width=1%>$enable</td>
			<td width=1%>$delete</td>
			</tr>";	
			
		}
	}

	echo $tpl->_ENGINE_parse_body($html."</table>")."
	</div>
	<script>
		var x_RefreshDrupalInfos=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}	
			RefreshTab('main_config_drupal');
		}
	
	
		function RefreshDrupalInfos(key){
			var XHR = new XHRConnection();
			XHR.appendData('RefreshDrupalInfos','yes');
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('drupalanimate');
    		XHR.sendAndLoad('$page', 'POST',x_RefreshDrupalInfos);
		}
		
	var x_MemberDrupalAdd=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			RefreshTab('main_config_drupal');	
		}


	
		function MemberDrupalAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('add-uid','yes');
			XHR.appendData('uid',document.getElementById('member-drupal-add').value);
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('drupal2animate');
    		XHR.sendAndLoad('$page', 'POST',x_MemberDrupalAdd);
		}		

		function MemberDrupalDelete(uid){
			if(confirm('$delete_this_user_text')){
				var XHR = new XHRConnection();
				XHR.appendData('del-uid','yes');
				XHR.appendData('uid',uid);
				XHR.appendData('servername','{$_GET["servername"]}');
				AnimateDiv('drupal2animate');
	    		XHR.sendAndLoad('$page', 'POST',x_MemberDrupalAdd);		
	    	}
		}
		
		function DrupalMemberActive(uid,sid){
			var XHR = new XHRConnection();
			XHR.appendData('enable-uid','yes');
			if(document.getElementById(sid).checked){XHR.appendData('value','1');}else{XHR.appendData('value','0');}
			XHR.appendData('uid',uid);
			XHR.appendData('servername','{$_GET["servername"]}');
			XHR.sendAndLoad('$page', 'POST');	
		}
		
		function DrupalRole(uid){
			YahooWin4('270','$page?uid-role-popup=yes&uid='+uid+'&servername={$_GET["servername"]}','$privileges');
		
		}
		function MemberDrupalAddCheck(e){
			if(checkEnter(e)){MemberDrupalAdd();}
		}		
		
	</script>
	
	";		
	
	
}

function members_enable(){
	$uid=$_POST["uid"];
	
	$data=addslashes(base64_encode(serialize(array("USER"=>$uid,"value"=>$_POST["value"]))));
	$sql="INSERT INTO drupal_queue_orders(servername,`ORDER`,`value`) VALUES('{$_POST["servername"]}','ENABLE_USER','$data')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");		
	
}

function members_add(){
	$uid=$_POST["uid"];
	$u=new user($uid);
	if(strlen($u->password)==0){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{error_no_user_exists}");
		return;
	}
	
	$data=addslashes(base64_encode(serialize(array("USER"=>$uid,"PASSWORD"=>$u->password))));
	$sql="INSERT INTO drupal_queue_orders(servername,`ORDER`,`value`) VALUES('{$_POST["servername"]}','CREATE_USER','$data')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");
	
}

function members_del(){
	$uid=$_POST["uid"];
	$data=addslashes(base64_encode(serialize(array("USER"=>$uid))));
	$sql="INSERT INTO drupal_queue_orders(servername,`ORDER`,`value`) VALUES('{$_POST["servername"]}','DELETE_USER','$data')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");	
}

function status(){
	
	$tpl=new templates();	
	$page=CurrentPageName();
	$sql="SELECT DrupalInfos from freeweb WHERE servername='{$_GET["servername"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$DrupalInfos=unserialize(base64_decode($ligne["DrupalInfos"]));
	$array=$DrupalInfos["GLOBAL_STATUS"];
	
	$sql="SELECT ID FROM drupal_queue_orders WHERE `ORDER`='REFRESH_INFOS'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($ligne["ID"]>0){
		$infos="<div class=explain>{drupal_refresh_ordered}</div>";
	}
	
	
	
	$html="$infos
<div id='drupalanimate'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>". imgtootltip("refresh-24.png","{refresh}","RefreshDrupalInfos()")."</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($num, $ligne) = each ($array) ){
		if($num=="DATABASE_USERNAME"){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr>
		<td style='font-size:14px;text-align:right' align='right'>{{$num}}:</td>
		<td style='font-size:14px'><strong>{$ligne}</strong></td>
		</tr>
	";
	}
	
	
	echo $tpl->_ENGINE_parse_body($html."</table>")."
	</div>
	<script>
		var x_RefreshDrupalInfos=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}	
			RefreshTab('main_config_drupal');
		}
	
	
		function RefreshDrupalInfos(key){
			var XHR = new XHRConnection();
			XHR.appendData('RefreshDrupalInfos','yes');
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('drupalanimate');
    		XHR.sendAndLoad('$page', 'POST',x_RefreshDrupalInfos);
		}
	</script>
	
	";
	
}

function members_role(){
	$tpl=new templates();	
	$page=CurrentPageName();
	$sql="SELECT DrupalInfos from freeweb WHERE servername='{$_GET["servername"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$DrupalInfos=unserialize(base64_decode($ligne["DrupalInfos"]));	
	$array=$DrupalInfos["USERS"][$_GET["uid"]];

	if(preg_match("#administrator#", $array["INFOS"]["USER_ROLES"])){$administrator=1;}
	if(preg_match("#authenticated user#", $array["INFOS"]["USER_ROLES"])){$user=1;}
	if(preg_match("#anonymous user#", $array["INFOS"]["USER_ROLES"])){$an=1;}
	
	
	
	$html="
	<div id='drupalprduserivdiv'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>administrator:</td>
		<td>". Field_checkbox("administrator", 1,$administrator,"DrupalPrivCheck()")."</td>
	</tr>
	<tr>
		<td class=legend>authenticated user:</td>
		<td>". Field_checkbox("duser", 1,$user,"DrupalPrivCheck()")."</td>
	</tr>
	<tr>
		<td class=legend>anonymous user:</td>
		<td>". Field_checkbox("an", 1,$an,"DrupalPrivCheck()")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>". button("{apply}","SaveDrupalPrivs()")."</td>
	</tr>
	</table>	
	</div>
	<script>
		function DrupalPrivCheck(){
			if(document.getElementById('administrator').checked){
				document.getElementById('duser').disabled=true;
				document.getElementById('an').disabled=true;
			}else{
				document.getElementById('duser').disabled=false;
				document.getElementById('an').disabled=false;
				if(document.getElementById('duser').checked){
					document.getElementById('an').disabled=true;
				}else{
					document.getElementById('an').disabled=false;
					document.getElementById('administrator').disabled=false;
				}
				if(document.getElementById('an').checked){
					document.getElementById('duser').disabled=true;
					document.getElementById('administrator').disabled=true;
				}				
			}
		}
		
	var x_SaveDrupalPrivs=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			RefreshTab('main_config_drupal');	
			YahooWin4Hide();
		}


	
		function SaveDrupalPrivs(){
			var XHR = new XHRConnection();
			XHR.appendData('priv-uid','yes');
			XHR.appendData('uid','{$array["NAME"]}');
			XHR.appendData('servername','{$_GET["servername"]}');
			if(document.getElementById('administrator').checked){XHR.appendData('administrator','yes');}
			if(document.getElementById('duser').checked){XHR.appendData('duser','yes');}
			if(document.getElementById('an').checked){XHR.appendData('an','yes');}
			AnimateDiv('drupalprduserivdiv');
    		XHR.sendAndLoad('$page', 'POST',x_SaveDrupalPrivs);
		}			
		
	DrupalPrivCheck();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function RefreshDrupalInfos(){
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?RefreshDrupalInfos=yes&servername={$_POST["servername"]}");
	
	$data=null;
	$sql="INSERT INTO drupal_queue_orders(servername,`ORDER`,`value`) VALUES('{$_POST["servername"]}','REFRESH_INFOS','')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");		
	
	
}

function members_privs(){
	$sock=new sockets();
	$uid=$_POST["uid"];
	
	if(isset($_POST["administrator"])){$priv="administrator";unset($_POST["duser"]);unset($_POST["an"]);}
	if(isset($_POST["duser"])){$priv="user";unset($_POST["an"]);}
	if(isset($_POST["an"])){$priv="anonym";}	

	$data=addslashes(base64_encode(serialize(array("USER"=>$uid,"value"=>$priv))));
	$sql="INSERT INTO drupal_queue_orders(servername,`ORDER`,`value`) VALUES('{$_POST["servername"]}','PRIV_USER','$data')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");		
	
}

function modules(){
	$tpl=new templates();	
	$page=CurrentPageName();
	
$q=new mysql();	
$sql="SELECT ID FROM drupal_queue_orders WHERE `ORDER`='REFRESH_MODULES'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($ligne["ID"]>0){
		$infos="<div class=explain>{drupal_refresh_ordered}</div>";
	}	
	
	$sql="SELECT DrupalModules from freeweb WHERE servername='{$_GET["servername"]}'";
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$DrupalModules=unserialize(base64_decode($ligne["DrupalModules"]));
	
	
	
	
	$html="$infos
<div id='drupalanimate3'>
<table style='width:100%'>
<tr>
<td valign='top' width=100%'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>". imgtootltip("refresh-24.png","{refresh}","RefreshModulesInfos()")."</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($module, $ligne) = each ($DrupalModules) ){
		if($num=="DATABASE_USERNAME"){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr>
		<td style='font-size:14px;text-align:right' align='right'>$module:</td>
		<td style='font-size:14px'><strong>{$ligne["VERSION"]}</strong></td>
		</tr>
	";
	}
	
	
	echo $tpl->_ENGINE_parse_body($html."</table>
	</td>
	<td valign='top'></td>
	</tr>
	</table>
	
	
	")."
	</div>
	<script>
		var x_RefreshModulesInfos=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}	
			RefreshTab('main_config_drupal');
		}
	
	
		function RefreshModulesInfos(key){
			var XHR = new XHRConnection();
			XHR.appendData('RefreshModulesInfos','yes');
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('drupalanimate3');
    		XHR.sendAndLoad('$page', 'POST',x_RefreshModulesInfos);
		}
	</script>
	
	";
		
}

function modules_infos(){
	$sock=new sockets();
	//$sock->getFrameWork("drupal.php?RefreshDrupalInfos=yes&servername={$_POST["servername"]}");
	
	$data=null;
	$sql="INSERT INTO drupal_queue_orders(servername,`ORDER`,`value`) VALUES('{$_POST["servername"]}','REFRESH_MODULES','')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");		
	
	
}