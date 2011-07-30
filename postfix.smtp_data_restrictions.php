<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	if(isset($_POST["hostname"])){$_GET["hostname"]=$_POST["hostname"];}
	if(isset($_GET["smtpd_data_restrictions_list"])){smtpd_data_restrictions_list();exit;}
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["js"])){js();exit;}

	if(isset($_GET["smtpd_data_restrictions_add"])){smtpd_data_restrictions_add();exit;}
	if(isset($_POST["SMTP_DATA_RESTRICTIONS_ADD_KEY"])){smtpd_data_restrictions_ajoute();exit;}
	if(isset($_POST["SMTP_DATA_RESTRICTIONS_ENABLE"])){smtpd_data_restrictions_enable();exit;}
	if(isset($_POST["SMTP_DATA_RESTRICTIONS_ORDER"])){smtpd_data_restriction_sorder();exit;}
	if(isset($_POST["SMTP_DATA_RESTRICTIONS_ADD_VALUE"])){smtpd_data_restrictions_add_value();exit;}
	if(isset($_POST["SMTP_DATA_RESTRICTIONS_DEL_VALUE"])){smtpd_data_restrictions_del_value();exit;}
	if(isset($_POST["SMTP_DATA_RESTRICTIONS_DEL"])){smtpd_data_restrictions_del_rule();exit;}
	if(isset($_POST["SMTP_DATA_RESTRICTIONS_COMPILE"])){smtpd_data_restrictions_compile();exit;}
	
	
	if(isset($_GET["smtpd_data_restrictions_datas_sub"])){smtpd_data_restrictions_datas_sub();exit;}
	if(isset($_GET["smtpd_data_restrictions_edit"])){smtpd_data_restrictions_datas();exit;}
page();

function js(){
		$page=CurrentPageName();
		$tpl=new templates();
		if(isset($_GET["hostname"])){$_GET["hostname"]="master";}
		$title=$tpl->_ENGINE_parse_body("{smtpd_data_restrictions}: {rules}");
		echo "YahooWin3('670','$page?hostname={$_GET["hostname"]}','$title');";
	
	
}


function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$restriction=$tpl->_ENGINE_parse_body("{restrictions}");
	$html="
	<table style='width:100%;margin-bottom:8px'>
	<tr>
	<td width=100%' valign='top'>
		<div class=explain>{smtpd_data_restrictions_explain}</div>
	</td>
	<td width=1%>". Paragraphe("apply-config-44.gif", "{compile_rules}", "{compile_rules_explain}","javascript:smtpd_data_restrictions_compile()")."</td>
	</tr>
	</table>
		<div id='smtpd_data_restrictions_list' style='height:400px;overflow:auto'></div>
	
	
	<script>
		function RefreshRestrictionList(){
			LoadAjax('smtpd_data_restrictions_list','$page?smtpd_data_restrictions_list=yes&hostname={$_GET["hostname"]}&ou={$_REQUEST["ou"]}');
		
		}
		
		var x_smtpd_data_restrictions_compile=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			RefreshRestrictionList();
		}			
		
		function smtpd_data_restrictions_compile(){
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_DATA_RESTRICTIONS_COMPILE','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			AnimateDiv('smtpd_data_restrictions_list');
    		XHR.sendAndLoad('$page', 'POST',x_smtpd_data_restrictions_compile);		
		
		}
		
		function smtpd_data_restrictions_add(){
			YahooWin5('420','$page?smtpd_data_restrictions_add=yes&ID=0&hostname={$_GET["hostname"]}&ou={$_REQUEST["ou"]}','$restriction');
		}
	RefreshRestrictionList();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);	
}

function smtpd_data_restrictions_compile(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-smtp-sender-restrictions={$_POST["hostname"]}");
	
}


function smtpd_data_restrictions_add(){
	$page=CurrentPageName();
	$tpl=new templates();	
	Load_globals();
	reset($GLOBALS["SMTP_DATA_RESTRICTIONS"]);
	$users=new usersMenus();
	$POSTFIXR=explode(".",$users->POSTFIX_VERSION);
	$MAJOR=$POSTFIXR[0];
	$MINOR=$POSTFIXR[1];
	$REV=$POSTFIXR[2];
	
	
$html="

<div style='width:100%;height:320px;overflow:auto'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=2>{restrictions} {APP_POSTFIX} v.$users->POSTFIX_VERSION</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while (list ($key, $array) = each ($GLOBALS["SMTP_DATA_RESTRICTIONS"]) ){
		$warn=null;
		$add=button("{add}", "SMTP_DATA_RESTRICTIONS_ADD_KEY('$key')");
		if(isset($array["VERSION"])){
			if(badversion($users->POSTFIX_VERSION,$array["VERSION"])){
			$warn="<strong style='color:#700303;'>({require} v{$array["VERSION"]})</strong>";
			$add="&nbsp;";
			}
		}
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold;color:$color'>$key<div style='font-size:11px;font-weight:normal'><i>{{$key}}&nbsp;$warn</i></div></td>
			<td width=1% style='font-size:14px;font-weight:bold;color:$color'>". $add."</td>
		</tr>
		";		
		
	}
	
	$html=$html."</table></div>
	<script>
	
		var x_SMTP_DATA_RESTRICTIONS_ADD_KEY=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			RefreshRestrictionList();
		}	
	
		function SMTP_DATA_RESTRICTIONS_ADD_KEY(key){
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_DATA_RESTRICTIONS_ADD_KEY',key);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			AnimateDiv('smtpd_data_restrictions_list');
    		XHR.sendAndLoad('$page', 'POST',x_SMTP_DATA_RESTRICTIONS_ADD_KEY);
		
		}
	
	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function badversion($actual,$requested){
	$POSTFIXR=explode(".",$actual);
	$MAJOR=$POSTFIXR[0];
	$MINOR=$POSTFIXR[1];
	$REV=$POSTFIXR[2];	
	
	$POSTFIXR=explode(".",$requested);
	if($MAJOR<$POSTFIXR[0]){return true;}
	if($MINOR<$POSTFIXR[1]){return true;}
	if(isset($POSTFIXR[2])){if($REV<$POSTFIXR[2]){return true;}}	
	
}

function smtpd_data_restrictions_ajoute(){
	$sql="INSERT INTO smtpd_data_restrictions (restriction,hostname) VALUES('{$_POST["SMTP_DATA_RESTRICTIONS_ADD_KEY"]}','{$_POST["hostname"]}');";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function smtpd_data_restrictions_del_rule(){
	$sql="DELETE FROM smtpd_data_restrictions WHERE ID='{$_POST["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}	
}


function smtpd_data_restrictions_datas(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ID=$_GET["ID"];
	if(!is_numeric($ID)){return;}
	$q=new mysql();
	$sql="SELECT restriction,enabled,zorder FROM smtpd_data_restrictions WHERE ID='$ID' AND `hostname`='{$_GET["hostname"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$html="
	<div class=explain style='font-size:14px'><strong>{{$ligne["restriction"]}}</strong><br>{{$ligne["restriction"]}_text}</div>
	<div id='restriction-data' style='width:100%;height:250px;overflow:auto'></div>
	<script>

		function RefreshMyRestrictionData(){
			LoadAjax('restriction-data','$page?smtpd_data_restrictions_datas_sub=yes&hostname={$_GET["hostname"]}&ou={$_REQUEST["ou"]}&ID={$_GET["ID"]}');
		
		}
		RefreshMyRestrictionData();
	</script>";
	
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function smtpd_data_restrictions_datas_sub(){
	Load_globals();
	$page=CurrentPageName();
	$tpl=new templates();
	$ID=$_GET["ID"];
	if(!is_numeric($ID)){return;}
	$q=new mysql();
	$sql="SELECT restriction,enabled,restrictions_datas FROM smtpd_data_restrictions WHERE ID='$ID' AND `hostname`='{$_GET["hostname"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$arrayTYPE=$GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]]["TYPE"];
	$array=unserialize(base64_decode($ligne["restrictions_datas"]));
	
	
	if($arrayTYPE=="TABLE"){
		$content=smtpd_data_restrictions_datas_sub_table($array,$ID);
	}
	if($arrayTYPE=="VALUE"){
		$content=smtpd_data_restrictions_datas_sub_value($array,$ID);
	}
	
	$html=$content;
	echo $tpl->_ENGINE_parse_body($html);	
}


function smtpd_data_restrictions_datas_sub_value($array,$ID){
	$page=CurrentPageName();
	$tpl=new templates();		
	$html=
	
	"
	<center><table style='width:90%' class=form>
	<tr>
	<td class=legend>{value}:</td>
	<td>".
	Field_text("RESTRICTVALUE",$array["VALUE"],"font-size:16px;padding:5px",null,null,null,false,"EditRestrictionVALUECheck(event)")."
	</td>
	</tr>
	</table>
	</center>
	<div style='text-align:right'><hr>".button("{apply}", "EditRestrictionVALUE()")."</div>
	
	<script>
		var x_EditRestrictionVALUE=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			RefreshMyRestrictionData();
		}	
	
		function EditRestrictionVALUECheck(e){
			if(!checkEnter(e)){return;}
			EditRestrictionVALUE();
		}
		function EditRestrictionVALUE(){
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_DATA_RESTRICTIONS_ADD_VALUE','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('ID','$ID');
			XHR.appendData('VALUE', document.getElementById('RESTRICTVALUE').value);
			AnimateDiv('restriction-data');
    		XHR.sendAndLoad('$page', 'POST',x_EditRestrictionVALUE);
		
		}	
	
	
	</script>
	
	";
	
return $tpl->_ENGINE_parse_body($html);	
	
}

function smtpd_data_restrictions_datas_sub_table($array,$ID){
	$page=CurrentPageName();
	$tpl=new templates();	
$actions[]="OK";
$actions[]="ACCEPT";
$actions[]="DUNNO";
$actions[]="BCC";
$actions[]="REDIRECT";
$actions[]="WARN";
$actions[]="REJECT";
$actions[]="DEFER";
$actions[]="DEFER_IF_REJECT";
$actions[]="DEFER_IF_PERMIT";
$actions[]="DISCARD";
$actions[]="FILTER";
$actions[]="HOLD";
$actions[]="PREPEND";

while (list ($num, $key) = each ($actions) ){$actionsF[$key]=$key;}


$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%></th>
		<th>{key}</th>
		<th>{value}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
<tr class=oddRow>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td>". Field_text('restriction-key-new',null,'width:170px;font-size:14px',null,null,null,false,"EditRestrictionTable(event,'new')")."</td>
	<td>".Field_array_Hash($actionsF, "restriction-action-new",null,"style:font-size:14px")."</td>
	<td>". Field_text('restriction-text-new',null,'width:170px;font-size:14px',null,null,null,false,"EditRestrictionTable(event,'new')")."</td>
	<td width=1%>". imgtootltip("plus-24.png","{add}","EditRestrictionTableSave('new')")."</td>
</tr>


";

$classtr="oddRow";
while (list ($num, $ARkey) = each ($array) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
	<tr class=$classtr>
	<td><img src='img/fw_bold.gif'></td>
	<td>". Field_text("restriction-key-$num",$ARkey["KEY"],'width:170px;font-size:14px',null,null,null,false,"EditRestrictionTable(event,'$num')")."</td>
	<td>".Field_array_Hash($actionsF, "restriction-action-$num",$ARkey["ACTION"],"style:font-size:14px")."</td>
	<td>". Field_text("restriction-text-$num",$ARkey["TXT"],'width:170px;font-size:14px',null,null,null,false,"EditRestrictionTable(event,'$num')")."</td>
	<td width=1%>". imgtootltip("delete-32.png","{delete}","DeleteRestrictionTable($num)")."</td>
	</tr>";
	}
	
$html=$html."</table>
<script>
		var x_EditRestrictionTable=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			RefreshMyRestrictionData();
		}	
	
		function EditRestrictionTable(e,num){
			if(!checkEnter(e)){return;}
			EditRestrictionTableSave(num);
		}
		function EditRestrictionTableSave(num){
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_DATA_RESTRICTIONS_ADD_VALUE','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('ID','$ID');
			if(!document.getElementById('restriction-key-'+num)){alert('restriction-key-'+num+' !!');return;}
			XHR.appendData('key', document.getElementById('restriction-key-'+num).value);
			XHR.appendData('index', num);
			XHR.appendData('action', document.getElementById('restriction-action-'+num).value);
			XHR.appendData('text', document.getElementById('restriction-text-'+num).value);
			AnimateDiv('restriction-data');
    		XHR.sendAndLoad('$page', 'POST',x_EditRestrictionTable);
		
		}
		
		function DeleteRestrictionTable(num){
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_DATA_RESTRICTIONS_DEL_VALUE','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('ID','$ID');
			XHR.appendData('index', num);
			AnimateDiv('restriction-data');
    		XHR.sendAndLoad('$page', 'POST',x_EditRestrictionTable);		
		
		}
		
		
</script>

";	
	
return $tpl->_ENGINE_parse_body($html);	


}

function smtpd_data_restrictions_del_value(){
	Load_globals();
	
	$page=CurrentPageName();
	$tpl=new templates();
	$ID=$_POST["ID"];
	if(!is_numeric($ID)){return;}
	$q=new mysql();
	$sql="SELECT restriction,enabled,restrictions_datas FROM smtpd_data_restrictions WHERE ID='$ID' AND `hostname`='{$_POST["hostname"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$array=unserialize(base64_decode($ligne["restrictions_datas"]));
	$arrayTYPE=$GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]]["TYPE"];
	if($arrayTYPE=="TABLE"){
		if(!is_numeric($_POST["index"])){return;}
		unset($array[$_POST["index"]]);
		
		
		
		
	}
	
	$data=addslashes(base64_encode(serialize($array)));
	$sql="UPDATE smtpd_data_restrictions SET restrictions_datas='$data' WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}	
}

function smtpd_data_restrictions_add_value(){
	Load_globals();
	
	$page=CurrentPageName();
	$tpl=new templates();
	$ID=$_POST["ID"];
	if(!is_numeric($ID)){return;}
	$q=new mysql();
	$sql="SELECT restriction,enabled,restrictions_datas FROM smtpd_data_restrictions WHERE ID='$ID' AND `hostname`='{$_POST["hostname"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$array=unserialize(base64_decode($ligne["restrictions_datas"]));
	$arrayTYPE=$GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]]["TYPE"];
	if($arrayTYPE=="TABLE"){
		if(!is_numeric($_POST["index"])){
			$array[]=array("KEY"=>$_POST["key"],"ACTION"=>$_POST["action"],"TXT"=>$_POST["text"]);
		}else{
			$array[$_POST["index"]]=array("KEY"=>$_POST["key"],"ACTION"=>$_POST["action"],"TXT"=>$_POST["text"]);
		}
	}
	
	if($arrayTYPE=="VALUE"){
		$array["VALUE"]=$_POST["VALUE"];
	}
	
	$data=addslashes(base64_encode(serialize($array)));
	$sql="UPDATE smtpd_data_restrictions SET restrictions_datas='$data' WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
	
}
	






function smtpd_data_restrictions_enable(){
	
	$sql="UPDATE smtpd_data_restrictions SET enabled='{$_POST["SMTP_DATA_RESTRICTIONS_ENABLE"]}' WHERE ID='{$_POST["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
}

function smtpd_data_restriction_sorder(){
	$sql="UPDATE smtpd_data_restrictions SET zorder='{$_POST["SMTP_DATA_RESTRICTIONS_ORDER"]}' WHERE ID='{$_POST["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}	
}

function smtpd_data_restrictions_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	Load_globals();
	$add=imgtootltip("plus-24.png","{add}","smtpd_data_restrictions_add()");
	$restriction=$tpl->_ENGINE_parse_body("{restrictions}");
	
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{restrictions}</th>
		<th>{order}</th>
		<th colspan=2>{options}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	$sql="SELECT * FROM smtpd_data_restrictions WHERE hostname='{$_GET["hostname"]}' ORDER BY zorder";
	$q=new mysql();
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","smtpd_data_restrictions_edit('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","smtpd_data_restrictions_del('{$ligne["ID"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		$textadd=null;
		if(!is_array($GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]])){
			$select=imgtootltip("help-32.png","{help}","smtpd_data_restrictions_edit('{$ligne["ID"]}')");
		}else{
			if($GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]]["TYPE"]=="VALUE"){
				$arrayVal=unserialize(base64_decode($ligne["restrictions_datas"]));
				$textadd=$arrayVal["VALUE"];
			}
		}
		
		$enable=Field_checkbox("enable-{$ligne["ID"]}", 1,$ligne["enabled"],"CheckRestrictionEnabled({$ligne["ID"]})");
		
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/rule-24.png'></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["restriction"]}<div><i style='font-size:12px'>$textadd</i></div></a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>". Field_text("zOrder-{$ligne["ID"]}",
		$ligne["zorder"],"text-align:center;width:40px;font-size:14px",null,null,null,false,"ChangeZorder(event,'{$ligne["ID"]}')")."</td>
			
			<td width=1%>$enable</td>
			<td width=1%>$select</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	
	<script>
		function smtpd_data_restrictions_edit(ID){
			YahooWin5('650','$page?smtpd_data_restrictions_edit=yes&ID='+ID+'&hostname={$_GET["hostname"]}&ou={$_REQUEST["ou"]}','$restriction:{$ligne["restriction"]}');
		
		}
		
	  var x_CheckRestrictionEnabled=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}		
			RefreshRestrictionList();		
		}
		
	  var x_ChangeZorder=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}	
			RefreshRestrictionList();		
		}		
		
		function ChangeZorder(e,ID){
			if(!checkEnter(e)){return;}
			var XHR = new XHRConnection();
			var num=document.getElementById('zOrder-'+ID).value;
			XHR.appendData('ID',ID);
			XHR.appendData('SMTP_DATA_RESTRICTIONS_ORDER',num);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
    		XHR.sendAndLoad('$page', 'POST',x_ChangeZorder);				
		
		}
		
		function smtpd_data_restrictions_del(ID){
			var XHR = new XHRConnection();
			XHR.appendData('ID',ID);
			XHR.appendData('SMTP_DATA_RESTRICTIONS_DEL','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
    		XHR.sendAndLoad('$page', 'POST',x_ChangeZorder);			
		
		}
	
	
		function CheckRestrictionEnabled(ID){
			var XHR = new XHRConnection();
			if(document.getElementById('enable-'+ID).checked){XHR.appendData('SMTP_DATA_RESTRICTIONS_ENABLE',1);}else{XHR.appendData('SMTP_DATA_RESTRICTIONS_ENABLE',0);}
			XHR.appendData('ID',ID);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
    		XHR.sendAndLoad('$page', 'POST',x_CheckRestrictionEnabled);			
		
		}		
	
	</script>
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html);	
}

	
	
function Load_globals(){
	include_once(dirname(__FILE__)."/ressources/class.smtp_data_restrictions.inc");
	$smtpd_data_restrictions=new smtpd_data_restrictions();	

}	

	

