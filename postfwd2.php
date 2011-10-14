<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.lvm.org.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
	if(!Isright()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
	}
	
	if(isset($_GET["tabs"])){echo tabs();exit;}
	if(isset($_GET["status"])){echo status();exit;}
	if(isset($_GET["service-status"])){echo status_service();exit;}
	
	if(isset($_GET["rules"])){echo rules();exit;}
	if(isset($_GET["DeleteRuleMaster"])){DeleteRuleMaster();exit;}
	if(isset($_GET["rules-list"])){echo rules_list();exit;}
	if(isset($_GET["rule-form"])){echo rule_form();exit;}
	
	if(isset($_GET["EnablePOSTFWD2"])){EnablePOSTFWD2();exit;}
	if(isset($_GET["reconfigure"])){reconfigure();exit;}
	
	if(isset($_GET["postfwd2-action"])){echo postfwd2_action_form();exit;}
	if(isset($_GET["postfwd2-mod-action"])){echo postfwd2_action_edit();exit;}
	
	
	if(isset($_GET["postfwd2-item"])){echo postfwd2_item_form();exit;}
	if(isset($_GET["postfwd2-add-item"])){echo postfwd2_item_save();exit;}
	if(isset($_GET["postfwd2-delete-item"])){postfwd2_item_delete();exit;}
	if(isset($_GET["postfwd2-item-list"])){echo postfwd2_item_list();exit;}
	if(isset($_GET["item-form-selected"])){echo postfwd2_item_form_selected();exit;}
	if(isset($_GET["postfwd2Down"])){echo postfwd2Down();exit;}
	if(isset($_GET["postfwd2Up"])){echo postfwd2Up();exit;}
	if(isset($_GET["rbl-list"])){rbl_list();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_POSTFWD2}::{$_GET["instance"]}");
	$start="postfwd2Start";
	if(isset($_GET["byou"])){$start="postfwd2Start2";}
	if(isset($_GET["with-popup"])){$start="postfwd2Start2";}
	echo "
	function postfwd2Start(){
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?tabs=yes&instance={$_GET["instance"]}');
	}
	
	function postfwd2Start2(){
		YahooWin(750,'$page?tabs=yes&instance={$_GET["instance"]}','$title');
	}	
	$start();
	";
	
	
}


function rules(){
	$page=CurrentPageName();
	$tpl=new templates();
	$instance=$_GET["instance"];
	$rule=$tpl->_ENGINE_parse_body("{rule}");
	$html="
	<div id='postfwd2-rules' style='width:100%;height:550px;overflow:auto'></div>
	
	<script>
		function postfwd2RulesRefresh(){
			LoadAjax('postfwd2-rules','$page?rules-list=yes&instance={$_GET["instance"]}');
		
		}
		
		function postfwdAddRule(ruleid){
			YahooWin2(650,'$page?rule-form=yes&ID='+ruleid+'&instance={$_GET["instance"]}','$rule::'+ruleid);
		}
		
	postfwd2RulesRefresh();
	</script>
	
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$instance=$_GET["instance"];
	$array["status"]='{status}';
	$array["rules"]='{rules}';
	$array["objects"]='{objects}';
	
	

	while (list ($num, $ligne) = each ($array) ){
		if($num=="objects"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfwd2.objects.php?instance=$instance\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&instance=$instance\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_postfwd2 style='width:100%;height:620px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_postfwd2').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
}

function status_service(){
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?postfwd2-status={$_GET["instance"]}"));
	$ini->loadString($datas);
	$status=DAEMON_STATUS_ROUND("APP_POSTFWD2:{$_GET["instance"]}",$ini,null);
	echo $tpl->_ENGINE_parse_body($status);	
}

function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$main=new maincf_multi($_GET["instance"]);
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	$reconfigure=Paragraphe("64-settings-refresh.png","{generate_config}","{postfix_reconfigure_text}","javascript:postfwd2Reconfigure()");
	
	$q=new mysql();
	if(!$q->TABLE_EXISTS('postfwd2','artica_backup')){	
		$q->check_postfwd2_table();
		$q=new mysql();
		if(!$q->TABLE_EXISTS('postfwd2','artica_backup')){	echo "<H2>Table postfwd2 -> artica_backup failed</H2>";}
	}	
	

	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div style='font-size:18px'>{APP_POSTFWD2}</div>
		<div style='text-align:right;padding-top:5px;border-top:1px solid #CCCCCC'><i style='font-size:14px'>&laquo;&nbsp;{instance}:{$_GET["instance"]}&nbsp;&raquo;</i></div>
		<p>&nbsp;</p>
		<div class=explain>{POSTFWD2_ABOUT}</div>
	</td>
	<td valign='top' style='width:270px'>
		<table style='width:100%' class=form>
			<tr>
				<td class=legend>{enable_service}</td>
				<td>". Field_checkbox("EnablePOSTFWD2",1,$array_filters["APP_POSTFWD2"],"EnablePOSTFWD2Check()")."</td>
			</tr>
		</table>
		<p>&nbsp;</p>
		<div id='postfwd2-status'></div>
		<p>&nbsp;</p>
		$reconfigure
	</tr>
	</table>
	
	<script>
	
	var x_EnablePOSTFWD2Check= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshTab('main_config_postfwd2');
		if(document.getElementById('main_multi_config_postfix')){RefreshTab('main_multi_config_postfix');}
	}		
	
	function EnablePOSTFWD2Check(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnablePOSTFWD2').checked){XHR.appendData('EnablePOSTFWD2',1);}else{XHR.appendData('EnablePOSTFWD2',0);}
		XHR.appendData('instance','{$_GET["instance"]}');	
		XHR.sendAndLoad('$page', 'GET',x_EnablePOSTFWD2Check);
		}		
	
		function Postfwd2Status(){
			LoadAjax('postfwd2-status','$page?service-status=yes&instance={$_GET["instance"]}');
		}
		
		function postfwd2Reconfigure(){
			var XHR = new XHRConnection();
			XHR.appendData('instance','{$_GET["instance"]}');
			XHR.appendData('reconfigure','yes');		
			XHR.sendAndLoad('$page', 'GET',x_EnablePOSTFWD2Check);
		}
		
		Postfwd2Status();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function action_to_text($pattern){
	
	$action["rate"]="{check_rate_limit}";
	$action["size"]="{check_size_limit}";
	$action["rcpt"]="{check_rcpt_limit}";
	$action["note"]="{event}";
	$action["dunno"]="{accept}";
	$action["reject"]="{reject}";
	$action["WARN"]="{warn}";	
	$action["greylist"]="{greylisting}";
	$action["greylisting"]="{greylisting}";		
	$action["HOLD"]="{put_in_hold_queue}";
	$action["REDIRECT"]="{redirect_to_email}";	
	$action["BYPASSAMAVIS"]="{bypass_amavis}";
	
	$text_action=$action[$pattern];
		
		if(preg_match("#(.+?):(.+?):(.+)#",$pattern,$re)){
			if($re[1]=="rate"){
				$text_action="{deny_if} {$action[$re[1]]} {$re[2]}msgs/{$re[3]}s";
			}
			if($re[1]=="size"){
				$maxsize=ParseBytes($re[2]/1024);
				$text_action="{deny_if} {$action[$re[1]]} $maxsize/{$re[3]}s";
			}	

			if($re[1]=="rcpt"){
				$text_action="{deny_if} {$action[$re[1]]} {$re[2]} {recipients}/{$re[3]}s";
			}			
		}
		
		if(preg_match("#score:(.+)#",$pattern,$re)){$text_action="{set_a_score} {$re[1]}";}
		if(preg_match("#jump R-([0-9]+)#",$pattern,$re)){$text_action="{jumpto} {rule} {$re[1]}";}
		if(preg_match("#REDIRECT\s+(.+)#",$pattern,$re)){$text_action="{redirect_to_email} {$re[1]}";}
		if(preg_match("#throttle:(.+?):(.+)#",$pattern,$re)){$text_action="{domain_throttle} &laquo;{$re[2]}&raquo;";}


	return $text_action;
}

	
function rules_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	if($_GET["instance"]==null){echo "<H2>No instances !</H2>";return;}
	$delete_freeweb_text=$tpl->_ENGINE_parse_body("{delete_freeweb_text}");
	$sql="SELECT * FROM postfwd2 WHERE instance='{$_GET["instance"]}' ORDER BY rank";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}

	
	$add=imgtootltip("plus-24.png","{add_rule}","postfwdAddRule(0)");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>$add</th>
	<th>ID</th>
	<th>{action}</th>
	<th>{rule}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$rle=array();
		$rle_text=null;
		$text_action=null;
		$text_action=action_to_text($ligne["action"]);
		
		$delete=imgtootltip("delete-32.png","{delete}","DeleteRuleMaster({$ligne["ID"]})");
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:postfwdAddRule({$ligne["ID"]});\" 
		style='font-size:12px;font-weight:bold;text-decoration:underline'>";
		
		$rules=unserialize(base64_decode($ligne["rule"]));
		if(is_array($rules)){
			while (list ($num, $array) = each ($rules) ){
				if($array["item"]=="object"){
					$sql="SELECT ObjectName FROM postfwd2_objects WHERE ID='{$array["item_data"]}'";
					$ligne3=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
					$sql="SELECT COUNT(ID) as tcount FROM postfwd2_items WHERE objectID={$array["item_data"]}";
					$ligne4=mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
					$array["item_data"]="{$ligne3["ObjectName"]} {with} {$ligne4["tcount"]} rules";	
				}				
				
				$rle[]=item_to_text($array);}
		}
		
		
		if(count($rle)==0){$rle_text="{all_messages}";}
		
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% align='center'><strong style='font-size:12px' align='center'>$href{$ligne["rank"]}</a></td>
			<td width=1% align='center'><strong style='font-size:12px' align='center'>$href{$ligne["ID"]}</a></td>
			<td width=1% nowrap>$href$text_action</a></td>
			<td width=99%><span style='font-size:12px'>".@implode("<br>",$rle)."$rle_text</td>
			<td  width=1%>". imgtootltip("up-22.png","{up}","postfwd2Up({$ligne["ID"]})")."</td>
			<td  width=1%>". imgtootltip("down-22.png","{down}","postfwd2Down({$ligne["ID"]})")."</td>
			<td  width=1%>$delete</td>
		</tr>";
	}
		
		
$html=$html."</table>


<script>
	var x_DeleteRuleMaster= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshTab('main_config_postfwd2');
	}		
	
	function DeleteRuleMaster(ID){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteRuleMaster',ID);	
		XHR.appendData('instance','{$_GET["instance"]}');	
		document.getElementById('postfwd2-rules').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_DeleteRuleMaster);
		}
		
	function postfwd2Up(ID){
		var XHR = new XHRConnection();
		XHR.appendData('postfwd2Up','yes');
		XHR.appendData('ID',ID);		
		XHR.appendData('instance','{$_GET["instance"]}');	
		document.getElementById('postfwd2-rules').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_DeleteRuleMaster);	
	}		

	function postfwd2Down(ID){
		var XHR = new XHRConnection();
		XHR.appendData('postfwd2Down','yes');
		XHR.appendData('ID',ID);		
		XHR.appendData('instance','{$_GET["instance"]}');	
		document.getElementById('postfwd2-rules').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_DeleteRuleMaster);	
	}
</script>

";
	
echo $tpl->_ENGINE_parse_body($html);
	
}	

function rule_form(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$q=new mysql();
	$users=new usersMenus();
	$main=new maincf_multi($_GET["instance"]);
	if(!is_numeric($_GET["ID"])){$_GET["ID"]=0;}
	if($_GET["ID"]==0){
		$sql="INSERT INTO postfwd2 (action,enabled,instance) VALUES('dunno',1,'{$_GET["instance"]}');";
		$q->QUERY_SQL($sql,"artica_backup");
		$_GET["ID"]=$q->last_id;
		$refresh="RefreshTab('main_config_postfwd2');";
	}
	
	$sql="SELECT ID FROM postfwd2 WHERE instance='{$_GET["instance"]}' and enabled=1 ORDER BY ID";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["ID"]==$_GET["ID"]){continue;}
		$action["jump R-{$ligne["ID"]}"]="{jumpto} {rule} {$ligne["ID"]}";		
	}
	
	$action["rate"]="{check_rate_limit}";
	$action["size"]="{check_size_limit}";
	$action["rcpt"]="{check_rcpt_limit}";
	$action["score"]="{set_a_score}";
	$action["reject"]="{reject}";
	$action["dunno"]="{accept}";
	$action["HOLD"]="{put_in_hold_queue}";
	$action["REDIRECT"]="{redirect_to_email}";
	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));
	if(is_array($array)){
		while (list ($uuid, $array_conf) = each ($array) ){
		$action["throttle:{$array_conf["smtp-instance-save"]}:{$array_conf["INSTANCE_NAME"]}"]="{domain_throttle}:{$array_conf["INSTANCE_NAME"]}";
		}
	}
	
	if($users->AMAVIS_INSTALLED){	
		if($_GET["instance"]=="master"){
			if($users->EnableAmavisDaemon==1){$action["BYPASSAMAVIS"]="{bypass_amavis}";}
		}else{
			
			$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
			if($array_filters["APP_AMAVIS"]==1){
				$action["BYPASSAMAVIS"]="{bypass_amavis}";
			}
		}
	}
	
	
	
	//$action["greylist"]="{greylisting}";
	
	$action[null]="{select}";
	
	$q=new mysql();
	$sql="SELECT * FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(!$q->ok){echo $q->mysql_error;}
	$rules=unserialize(base64_decode($ligne["rule"]));	
	$add=imgtootltip("plus-24.png","{add_rule}","postfwdAdditem('')");	
	
	$html="
	<div style='font-size:16px;margin-bottom:15px'>{$_GET["instance"]}::{rule}:{$_GET["ID"]}</div>
	
	<div id='rule-item-list' style='width:100%;height:220px;overflow:auto'></div>
	
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>ID:{$_GET["ID"]}:{enabled}:</td>
		<td>". Field_checkbox("enabled",1,$ligne["enabled"])."</td>
		<td class=legend>{action}:</td>
		<td>". Field_array_Hash($action,"postfwd2-action",null,"postfwd2ChangeAction()",null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
	<td colspan=2><div id='postfwd2-action-form'></div></td>
	</tr>
	</table>
	
	
	<script>
		function postfwd2ChangeAction(){
			var action=document.getElementById('postfwd2-action').value;
			LoadAjax('postfwd2-action-form','$page?postfwd2-action='+action+'&ID={$_GET["ID"]}&instance={$_GET["instance"]}');
		}
		
		function postfwdAdditem(itemid){
			YahooWin3('550','$page?postfwd2-item=yes&itemid='+itemid+'&ID={$_GET["ID"]}&instance={$_GET["instance"]}','::'+itemid);
		
		}
		
		function postfwd2ReloadItemsList(){
			LoadAjax('rule-item-list','$page?postfwd2-item-list=yes&ID={$_GET["ID"]}&instance={$_GET["instance"]}');
		
		}
		
		postfwd2ReloadItemsList();
	</script>";
	
echo $tpl->_ENGINE_parse_body($html);
	
}

function item_to_text($array){
	include_once(dirname(__FILE__)."/ressources/class.postfwd2.inc");
	$ptfw=new postfwd2();
	return $ptfw->item_to_text($array);
}

function postfwd2_item_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$q=new mysql();
	$sql="SELECT * FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(!$q->ok){echo $q->mysql_error;}
	$rules=unserialize(base64_decode($ligne["rule"]));	
	$add=imgtootltip("plus-24.png","{add_rule}","postfwdAdditem('')");
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$item_id=$_GET["itemid"];		

	
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>$add</th>
	<th>{rule}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
";	

	if(is_array($rules)){
		while (list ($num, $array) = each ($rules) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$delete=imgtootltip("delete-32.png","{delete}","Postfwd2DeleteItem($num)");
			if($num>0){$and="{and}";}else{$and="&nbsp;";}
			if($array["item"]=="object"){
				$sql="SELECT ObjectName FROM postfwd2_objects WHERE ID='{$array["item_data"]}'";
				$ligne3=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
				$sql="SELECT COUNT(ID) as tcount FROM postfwd2_items WHERE objectID={$array["item_data"]}";
				$ligne4=mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
				$array["item_data"]="{$ligne3["ObjectName"]} {with} {$ligne4["tcount"]} rules";	
			}
			
			
			$html=$html."
			<tr class=$classtr>
				<td width=1% align='center' style='font-size:14px;font-weight:bold'>$num</td>
				<td width=99% style='font-size:14px;'>$and ". item_to_text($array)."</td>
				<td width=1%>$delete</td>
			</tr>
			";
			
		}
	}
	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$text_action=action_to_text($ligne["action"]);
	$html=$html."
			<tr class=$classtr>
			<td width=1% align='center' style='font-size:14px;font-weight:bold'>&nbsp;</td>
			<td width=99% style='font-size:14px;' colspan=2>{then} $text_action</td>
			
		</tr>
	
	
	</table>
		<script>
		var x_Postfwd2DeleteItem= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function Postfwd2DeleteItem(num){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-delete-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('itemid',num);
			XHR.sendAndLoad('$page', 'GET',x_Postfwd2DeleteItem);
			}					
			
		</script>
		
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function postfwd2_action_form(){
	$q=new mysql();
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$page=CurrentPageName();
	$sql="SELECT action FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(!$q->ok){echo $q->mysql_error;}
	$tpl=new templates();
	switch ($_GET["postfwd2-action"]) {
		case "dunno":$newsql="UPDATE postfwd2 SET action='{$_GET["postfwd2-action"]}' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";break;
		case "reject":$newsql="UPDATE postfwd2 SET action='{$_GET["postfwd2-action"]}' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";break;
		case "WARN":$newsql="UPDATE postfwd2 SET action='{$_GET["postfwd2-action"]}' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}' ";break;
		case "note":$newsql="UPDATE postfwd2 SET action='{$_GET["postfwd2-action"]}' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";break;
		case "greylist":$newsql="UPDATE postfwd2 SET action='greylisting' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";break;
		case "BYPASSAMAVIS":$newsql="UPDATE postfwd2 SET action='BYPASSAMAVIS' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";break;
		case "HOLD":$newsql="UPDATE postfwd2 SET action='HOLD' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";break;
		
		
	}
	
	if(preg_match("#jump R-([0-9]+)#",$_GET["postfwd2-action"])){
		$newsql="UPDATE postfwd2 SET action='{$_GET["postfwd2-action"]}' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";
	}
	
	if(preg_match("#REDIRECT\s+(.+)$#",$_GET["postfwd2-action"])){
		$newsql="UPDATE postfwd2 SET action='{$_GET["postfwd2-action"]}' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";
	}

	if(preg_match("#throttle:(.+?):(.+)#",$_GET["postfwd2-action"],$re)){
		$newsql="UPDATE postfwd2 SET action='{$_GET["postfwd2-action"]}' WHERE ID={$_GET["ID"]} AND instance='{$_GET["instance"]}'";
	}
	
	if($newsql<>null){
		$q->QUERY_SQL($newsql,"artica_backup");
		if(!$q->ok){echo "<h2>$q->mysql_error<hr>$newsql<hr></H2>";}else{
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?postfwd2-reload={$_GET["instance"]}");
		}
		echo "<script>
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		</script>
		";
		return;
	}
	
	
	if($_GET["postfwd2-action"]=="rate"){
		echo $tpl->_ENGINE_parse_body(" <div class=explain>{postfwd2_action_rate}</div>
		<center>
			<table style='width:220px;' class=form>
			<tr>
				<td class=legend>{max_messages}:</td>
				<td>". Field_text("postfwd2-action-max",null,"font-size:16px;font-weight:bold;padding:5px;width:60px;color:#B23535;border:2px solid black")."</td>
			</tr>
			<tr>
				<td class=legend>{time_in_seconds}:</td>
				<td>". Field_text("postfwd2-action-seconds",null,"font-size:16px;font-weight:bold;padding:5px;width:60px;color:#B23535;border:2px solid black")."</td>
			</tr>			
			<tr>
				<td colspan=2 align='right'>
					". button("{apply}","postfwd2EditAction()")."
				</td>
		</tr>
		</center>
		
		<script>
		var x_postfwd2EditAction= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2EditAction(){
			var mx=document.getElementById('postfwd2-action-max').value;
			var ms=document.getElementById('postfwd2-action-seconds').value;
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-mod-action','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('action','rate');
			XHR.appendData('action_data','rate:'+mx+':'+ms);
			XHR.sendAndLoad('$page', 'GET',x_postfwd2EditAction);
			}					
			
		</script>");
		return;		
		}
	if($_GET["postfwd2-action"]=="size"){
		echo $tpl->_ENGINE_parse_body(" <div class=explain>{postfwd2_action_size}</div>
		<center>
			<table style='width:220px;' class=form>
			<tr>
				<td class=legend>{max_size}:</td>
				<td>". Field_text("postfwd2-action-max",null,"font-size:16px;font-weight:bold;padding:5px;width:90px;color:#B23535;border:2px solid black")."</td>
			</tr>
			<tr>
				<td class=legend>{time_in_seconds}:</td>
				<td>". Field_text("postfwd2-action-seconds",null,"font-size:16px;font-weight:bold;padding:5px;width:60px;color:#B23535;border:2px solid black")."</td>
			</tr>			
			<tr>
				<td colspan=2 align='right'>
					". button("{apply}","postfwd2EditAction()")."
			</td>
		</tr>
		</center>
		
		<script>
		var x_postfwd2EditAction= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2EditAction(){
			var mx=document.getElementById('postfwd2-action-max').value;
			var ms=document.getElementById('postfwd2-action-seconds').value;
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-mod-action','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('action','size');
			XHR.appendData('action_data','size:'+mx+':'+ms);
			XHR.sendAndLoad('$page', 'GET',x_postfwd2EditAction);
			}					
			
		</script>");
		return;		
		}

	if($_GET["postfwd2-action"]=="rcpt"){
		echo $tpl->_ENGINE_parse_body(" <div class=explain>{postfwd2_action_rcpt}</div>
		<center>
			<table style='width:220px;' class=form>
			<tr>
				<td class=legend>{max_recipients}:</td>
				<td>". Field_text("postfwd2-action-max",null,"font-size:16px;font-weight:bold;padding:5px;width:90px;color:#B23535;border:2px solid black")."</td>
			</tr>
			<tr>
				<td class=legend>{time_in_seconds}:</td>
				<td>". Field_text("postfwd2-action-seconds",null,"font-size:16px;font-weight:bold;padding:5px;width:60px;color:#B23535;border:2px solid black")."</td>
			</tr>			
			<tr>
				<td colspan=2 align='right'>
					". button("{apply}","postfwd2EditAction()")."
			</td>
		</tr>
		</center>
		
		<script>
		var x_postfwd2EditAction= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2EditAction(){
			var mx=document.getElementById('postfwd2-action-max').value;
			var ms=document.getElementById('postfwd2-action-seconds').value;
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-mod-action','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('action','rcpt');
			XHR.appendData('action_data','rcpt:'+mx+':'+ms);
			XHR.sendAndLoad('$page', 'GET',x_postfwd2EditAction);
			}					
			
		</script>");
		return;		
		}

	if($_GET["postfwd2-action"]=="score"){
		echo $tpl->_ENGINE_parse_body(" <div class=explain>{postfwd2_action_score}</div>
		<center>
			<table style='width:220px;' class=form>
			<tr>
				<td class=legend>{score}:</td>
				<td>". Field_text("postfwd2-action-score",null,"font-size:16px;font-weight:bold;padding:5px;width:90px;color:#B23535;border:2px solid black")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'>
					". button("{apply}","postfwd2EditAction()")."
			</td>
		</tr>
		</center>
		
		<script>
		var x_postfwd2EditAction= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2EditAction(){
			var score=base64_encode(document.getElementById('postfwd2-action-score').value);
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-mod-action','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('action','score');
			XHR.appendData('action_data','score:'+score);
			XHR.sendAndLoad('$page', 'GET',x_postfwd2EditAction);
			}					
			
		</script>");
		return;		
		}	

	if($_GET["postfwd2-action"]=="REDIRECT"){
		echo $tpl->_ENGINE_parse_body(" <div class=explain>{postfwd2_action_redirect}</div>
		<center>
			<table style='width:220px;' class=form>
			<tr>
				<td class=legend>{recipient}:</td>
				<td>". Field_text("postfwd2-action-data",null,"font-size:16px;font-weight:bold;padding:5px;width:220px;color:#B23535;border:2px solid black")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'>
					". button("{apply}","postfwd2EditAction()")."
			</td>
		</tr>
		</center>
		
		<script>
		var x_postfwd2EditAction= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2EditAction(){
			var data=document.getElementById('postfwd2-action-data').value;
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-mod-action','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('action','REDIRECT');
			XHR.appendData('action_data','REDIRECT '+data);
			XHR.sendAndLoad('$page', 'GET',x_postfwd2EditAction);
			}					
			
		</script>");
		return;		
		}			
		
}


function postfwd2_item_form(){
	$page=CurrentPageName();
	$tpl=new templates();
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$item_id=$_GET["itemid"];
	$q=new mysql();
	$ByObject=false;
	if(isset($_GET["ByObject"])){
		$ByObject=true;
		$itemUriAdd="&ByObject=yes";
	}
	
	if(!$ByObject){
		$sql="SELECT rule FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
		$rules=unserialize(base64_decode($ligne["rule"]));
		$rule_array=$rules["$item_id"];
	}else{
		$sql="SELECT item FROM postfwd2_items WHERE ID='$item_id'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
		$rule_array["item"]=$ligne["item"];
	}
	
	$time=time();
	
	
	$f["date"]="{date}";
	$f["time"]="{time}";
	$f["days"]="{days}";
	$f["months"]="{months}";
	$f["rbl"]="{rbl}";
	$f["rblcount"]="{rblcount}";
	$f["helo_address"]="{HELO_ADDRESS}";
	$f["helo_name"]="{HELO_SERVERNAME}";
	$f["sender_ns_names"]="{servername}";
	$f["sender_ns_addrs"]="{ipaddr}";
	$f["sender_mx_names"]="MX {servername}";
	$f["sender_mx_addrs"]="MX {ipaddr}";
	$f["client_address"]="{client_address}";
	$f["client_name"]="{client_name} regex";
	$f["reverse_client_name"]="{reverse_client_name}";
	$f["sender"]="{sender}";
	$f["recipient"]="{recipient}";
	$f["recipient_count"]="{recipient_count}";
	$f["size"]="{message_size}";
	$f["score"]="{score}";
	$f["object"]="{object}";			
	$f[null]="{select}";

$html="
<div style='item-form'>
<table style='width:100%' class=form>
<tr>
	<td class=legend>{if}:</td>
	<td>". Field_array_Hash($f,"item-array-hash-$time",$rule_array["item"],"postfwd2_item_form()",null,0,"font-size:13px;padding:3px")."</td>
</tr>
</table>
<div id='item-form-selected'></div>


<script>
	function postfwd2_item_form(){
		var item=document.getElementById('item-array-hash-$time').value;
		LoadAjax('item-form-selected','$page?item-form-selected=yes&item='+item+'&itemid=$item_id&ID=$ID&instance=$instance$itemUriAdd');
	
	}

postfwd2_item_form();
</script>
";
echo $tpl->_ENGINE_parse_body($html);

}

function postfwd2_item_form_selected(){
	$page=CurrentPageName();
	$tpl=new templates();
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$item_id=$_GET["itemid"];
	$ByObject=false;
	if(isset($_GET["ByObject"])){
		$ByObject=true;
		$itemUriAdd="&ByObject=yes";
		$POSTITEM="XHR.appendData('ByObject','yes');";
	}	
	$q=new mysql();
	if(!$ByObject){
		$sql="SELECT rule FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
		$rules=unserialize(base64_decode($ligne["rule"]));
		$rule_array=$rules["$item_id"];	
		$item=$_GET["item"];
	}else{
		$sql="SELECT operator FROM postfwd2_items WHERE ID='$item_id'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
		$item=$_GET["item"];
		$rule_array["operator"]=$ligne["operator"];
	}
	
	$ms["eq"]="{is}";
	$ms["eq2"]="{eq2}";
	$ms["noteq"]="{noteq}";
	$ms["no"]="{noteq}";
	$ms["aboveeq"]="{aboveeq}";
	$ms["abovenot"]="{abovenot}";	
	$ms["lowereq"]="{lowereq}";
	$ms["lowernot"]="{lowernot}";
	$ms["matches"]="{matches} (regex)";
	$ms["matchesnot"]="{matchesnot} (regex)";
	

	$field_item_choose= Field_array_Hash($ms,"postfwd2-operator",$rule_array["operator"],"style:font-size:16px;font-weight:bold;padding:3px");
	if($item=="rbl"){
		$link="<div style='width:100%;text-align:right'><a href=\"javascript:blur();\" OnClick=\"javascript:YahooWin5(275,'$page?rbl-list=yes','{rbl_list}');\" style='font-size:14px'>{rbl_list}</a></div>";
		//dnsrbl.db
		$field_item_choose="<input type='hidden' id='postfwd2-operator' value=''>";
	}
	if($item=="object"){
		$field_item_choose="<input type='hidden' id='postfwd2-operator' value=''><span style='font-size:16px;font-weight:bold;padding:3px'>{is}</span>";
		
	}

	$html="
	
	<table style='width:100%' class=form>
	<tr>
		<td width=1% style='font-size:16px;font-weight:bold' nowrap>{if} {{$item}}</td>
		<td width=99%>$field_item_choose</td>
	</tr>
	</table>
	$link
	";
	
	if($item=="date"){
		$html=$html."
	
		<div class=explain>{postfwd2_item_date}</div>
		<center>
			<hr>". Field_text("postfwd2-date",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2AddDate()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2AddDate(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','date');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-date').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}
	
	if($item=="object"){
		$sql="SELECT ID,ObjectName FROM postfwd2_objects WHERE instance='$instance'";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){$objects[$ligne["ID"]]=$ligne["ObjectName"];}		
		$html=$html."
	
		<div class=explain>{postfwd2_item_object}</div>
		<center>
			<hr>". Field_array_Hash($objects, "postfwd2-object",null,null,null,0,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2AddObject()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2AddObject(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','object');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-object').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
	if($item=="time"){
		$html=$html."
		<div class=explain>{postfwd2_item_time}</div>
		<center>
			<hr>". Field_text("postfwd2-time",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2AddTime()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2AddTime(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','time');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-time').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
	if($item=="days"){
		$html=$html."
		<div class=explain>{postfwd2_item_days}</div>
		<center>
			<hr>". Field_text("postfwd2-days",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2AddDays()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2AddDays(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','days');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-days').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}		
	
	if($item=="months"){
		$html=$html."
		<div class=explain>{postfwd2_item_months}</div>
		<center>
			<hr>". Field_text("postfwd2-months",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2AddMonths()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2AddMonths(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','months');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-months').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}
	
	if($item=="rbl"){
		$html=$html."
		<div class=explain>{postfwd2_item_rbl}</div>
		<center>
			<hr><textarea id='postfwd2-rbl' style='font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black;height:50px'></textarea><hr>
			". button("{apply}","postfwd2AddRBL()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2AddRBL(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','rbl');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-rbl').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}

	if($item=="rblcount"){
		$html=$html."
		<div class=explain>{postfwd2_item_rblcount}</div>
		<center>
			<hr>". Field_text("postfwd2-rblcount",null,"font-size:16px;font-weight:bold;padding:5px;width:60px;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2AddRblcount()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2AddRblcount(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','rblcount');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-rblcount').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
	if($item=="helo_address"){
		$html=$html."
		<div class=explain>{postfwd2_item_helo_address}</div>
		<center>
			<hr>". Field_text("postfwd2-helo_address",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2helo_address()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2helo_address(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','helo_address');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-helo_address').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}

	if($item=="sender_ns_names"){
		$html=$html."
		<div class=explain>{postfwd2_item_sender_ns_names}</div>
		<center>
			<hr>". Field_text("postfwd2-sender_ns_names",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2sender_ns_names()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2sender_ns_names(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','sender_ns_names');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-sender_ns_names').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
	
	if($item=="sender_ns_addrs"){
		$html=$html."
		<div class=explain>{postfwd2_item_sender_ns_addrs}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','sender_ns_addrs');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}

	if($item=="sender_mx_names"){
		$html=$html."
		<div class=explain>{postfwd2_item_sender_mx_names}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','sender_mx_names');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
	if($item=="sender_mx_addrs"){
		$html=$html."
		<div class=explain>{postfwd2_item_sender_mx_addrs}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','sender_mx_addrs');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}

	if($item=="client_address"){
		$html=$html."
		<div class=explain>{postfwd2_item_client_address}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','client_address');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
	if($item=="client_name"){
		$html=$html."
		<div class=explain>{postfwd2_item_client_name}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','client_name');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}

	if($item=="reverse_client_name"){
		$html=$html."
		<div class=explain>{postfwd2_item_reverse_client_name}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','reverse_client_name');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
	if($item=="helo_name"){
		$html=$html."
		<div class=explain>{postfwd2_item_helo_name}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','helo_name');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	

	if($item=="sender"){
		$html=$html."
		<div class=explain>{postfwd2_item_sender}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','sender');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}		
	
	if($item=="recipient"){
		$html=$html."
		<div class=explain>{postfwd2_item_recipient}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:100%;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','recipient');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}

if($item=="recipient_count"){
		$html=$html."
		<div class=explain>{postfwd2_item_recipient_count}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:30px;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','recipient_count');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}
	
if($item=="size"){
		$html=$html."
		<div class=explain>{postfwd2_item_size}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:60px;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','size');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
if($item=="score"){
		$html=$html."
		<div class=explain>{score}</div>
		<center>
			<hr>". Field_text("postfwd2-Gen",null,"font-size:16px;font-weight:bold;padding:5px;width:60px;color:#B23535;border:2px solid black")."<hr>
			". button("{apply}","postfwd2Gen()")."
		</center>
		
		<script>
		var x_postfwd2AddDate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}	
			RefreshTab('main_config_postfwd2');
			postfwd2ReloadItemsList();
		}		
	
		function postfwd2Gen(){
			var XHR = new XHRConnection();
			XHR.appendData('postfwd2-add-item','yes');	
			XHR.appendData('instance','$instance');
			XHR.appendData('ID','$ID');
			XHR.appendData('item','score');
			XHR.appendData('operator',document.getElementById('postfwd2-operator').value);				
			XHR.appendData('item_data',document.getElementById('postfwd2-Gen').value);
			$POSTITEM
			XHR.sendAndLoad('$page', 'GET',x_postfwd2AddDate);
			}					
			
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}	
	
}

function postfwd2_item_save(){
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$item_id=$_GET["itemid"];
	$q=new mysql();
	if(!isset($_GET["ByObject"])){
		$sql="SELECT rule FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
		$rules=unserialize(base64_decode($ligne["rule"]));
	
		if($item_id==null){$rules[]=$_GET;}else{$rules[$item_id]=$_GET;}
		$newrules=base64_encode(serialize($rules));
		$sql="UPDATE postfwd2 SET rule='$newrules' WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfwd2-reload={$_GET["instance"]}");	
		return;
	}
	
	if($item_id>0){
		$sql="UPDATE postfwd2_items SET item='{$_GET["item"]}',operator='{$_GET["operator"]}',item_data='{$_GET["item_data"]}' WHERE ID='$item_id'";
	}else{
		$sql="INSERT INTO postfwd2_items(objectID,item,operator,item_data) VALUES('$ID','{$_GET["item"]}','{$_GET["operator"]}','{$_GET["item_data"]}')";
	}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
}

function postfwd2_action_edit(){
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$action=$_GET["action"];
	$action_data=$_GET["action_data"];
	if($action=="score"){	
		if(preg_match("#score:(.+)#",$action_data,$re)){
			$decoded=base64_decode($re[1]);
			if(!preg_match("#^([0-9\.\+\-\/\*]+)#",$decoded,$re)){
				echo "$decoded false";
				return;
			}
			$action_data="score:{$re[1]}";
		}
	}
	
	$sql="UPDATE postfwd2 SET action='$action_data' WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$q=new mysql();	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfwd2-reload={$_GET["instance"]}");		
}

function postfwd2_item_delete(){
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$item_id=$_GET["itemid"];
	$q=new mysql();
	$sql="SELECT rule FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	$rules=unserialize(base64_decode($ligne["rule"]));
	unset($rules[$item_id]);
	$newrules=base64_encode(serialize($rules));
	$sql="UPDATE postfwd2 SET rule='$newrules' WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfwd2-reload={$_GET["instance"]}");		
	
}


function DeleteRuleMaster(){
	if(!is_numeric($_GET["DeleteRuleMaster"])){return;}
	$sql="DELETE FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["DeleteRuleMaster"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfwd2-reload={$_GET["instance"]}");		
	
}

function postfwd2Down(){
	$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$q=new mysql();
	
	$sql="SELECT COUNT(ID) as tcount FROM postfwd2 WHERE instance='{$_GET["instance"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$max_rank=$ligne["tcount"];
	
	
	$sql="SELECT rank FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	
	
	$current_rank=$ligne["rank"];
	$next_rank=$current_rank+1;
	if($next_rank>$max_rank){return;}
	$other_rank=$current_rank;
	
	$sql="UPDATE postfwd2 SET rank=$other_rank WHERE instance='{$_GET["instance"]}' AND rank=$next_rank";	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql";return;}	
	
	$sql="UPDATE postfwd2 SET rank=$next_rank WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfwd2-reload={$_GET["instance"]}");		
	
	
	
}

function postfwd2Up(){
$instance=$_GET["instance"];
	$ID=$_GET["ID"];
	$q=new mysql();
	
	
	
	
	$sql="SELECT rank FROM postfwd2 WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$current_rank=$ligne["rank"];
	if($current_rank==0){return;}
	$next_rank=$current_rank-1;
	$other_rank=$current_rank+1;
	
	
	
	
	$sql="UPDATE postfwd2 SET rank=$other_rank WHERE instance='{$_GET["instance"]}' AND rank=$next_rank";	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
	$sql="UPDATE postfwd2 SET rank=$next_rank WHERE instance='{$_GET["instance"]}' AND ID={$_GET["ID"]}";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfwd2-reload={$_GET["instance"]}");		
	
	
}

function EnablePOSTFWD2(){
	$main=new maincf_multi($_GET["instance"]);
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	$array_filters["APP_POSTFWD2"]=$_GET["EnablePOSTFWD2"];
	$main->SET_BIGDATA("PluginsEnabled",base64_encode(serialize($array_filters)));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfwd2-restart={$_GET["instance"]}");		
	
}

function reconfigure(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfwd2-restart={$_GET["instance"]}");		
}

	
	
	
function Isright(){
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsPostfixAdministrator){return true;}
	if(!$users->AsOrgStorageAdministrator){return false;}
	if(isset($_GET["ou"])){if($_SESSION["ou"]<>$_GET["ou"]){return false;}}
	return true;
	
	}
function rbl_list(){
	$tpl=new templates();
	$tbl=explode("\n",@file_get_contents("ressources/dnsrbl.db"));
	$html="
<div style='width:100%;height:350px;overflow:auto'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>RBL</th>
	
	</tr>
</thead>
<tbody class='tbody'>";			
	
	while (list ($num, $line) = each ($tbl) ){
		if(preg_match("#RBL:(.+)#",$line,$re)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
			$html=$html."<tr class=$classtr><td style='font-size:14px'>{$re[1]}</td></tr>";
		}
	}
	
	$html=$html."</table></div>";
	echo $tpl->_ENGINE_parse_body($html);
}
