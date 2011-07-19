<?php
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.j-chkmail.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}
//script=jchkbadmx
if(isset($_GET["script"])){switch_script();exit;}
if(isset($_GET["popup"])){switch_popup();exit;}
if(isset($_GET["BadMxEdition"])){badmx_save();exit;}
if(isset($_GET["BadMxDelete"])){badmx_delete();exit;}
if(isset($_GET["GreylistServername"])){GreyCheckConnect_save();exit;}
if(isset($_GET["GreyCheckConnect_del"])){GreyCheckConnect_del();exit;}
if(isset($_GET["RateEditRow"])){rate_save();exit;}
if(isset($_GET["RateDeleteRow"])){rate_delete();exit;}
if(isset($_GET["EditRateRessources"])){rate_save_ressources();exit;}
if(isset($_GET["NetClassAddSource"])){NetClass_addsrc();exit;}
if(isset($_GET["AddRateSource"])){rate_add_source();exit;}
if(isset($_GET["NetClassAdd"])){NetClass_add();exit;}
if(isset($_GET["NetClassDelete"])){NetClass_del();exit;}
if(isset($_GET["SCANNER_ACTION"])){SaveGlobalConf();exit;}
if(isset($_GET["REJECT_SHORT_BODIES"])){SaveGlobalConf();exit;}
if(isset($_GET["ARCHIVE"])){SaveGlobalConf();exit;}
if(isset($_GET["RejectShortMsgsEdit"])){RejectShortMsgs_save();exit;}
if(isset($_GET["RejectShortMsgsDelete"])){RejectShortMsgs_del();exit;}
if(isset($_GET["ArchiveEdit"])){Archive_save();exit;}
if(isset($_GET["ArchiveDelete"])){Archive_del();exit;}



function switch_script(){
	
	switch ($_GET["script"]) {
		case "jchkbadmx":badmx_script();break;
		case "jchkgreylist":GreyCheckConnect_script();break;
		case "jchkrate":rate_script();break;
		case "NetClass":NetClass_script();break;
		case "jchkclamav":clamav_script();break;
		case "RejectShortMsgs":RejectShortMsgs_script();break;
		case "Archive":ArchiveScript();break;
		default:
			break;
	}
	
	
}
function switch_popup(){
	
	switch ($_GET["popup"]) {
		case "jchkbadmx":badmx_popup();break;
		case "jchkgreylist":GreyCheckConnect_popup();break;
		case "jchkrate":rate_popup();break;
		case "NetClass":NetClass_popup();break;
		case "jchkclamav":clamav_popup();break;
		case "RejectShortMsgs":RejectShortMsgs_popup();break;
		case "Archive":ArchivePopup();break;
		default:
			break;
	}
}

function clamav_popup(){
	$chk=new jchkmail();
	$page=CurrentPageName();
	$users=new usersMenus();
	
	$action=array("OK"=>"{disable}","REJECT"=>"REJECT","NOTIFY"=>"NOTIFY","DISCARD"=>"DISCARD");
	
	$html="
	<H1>{APP_CLAMAV}</h1>
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/clamav-64.png'></td>
		<td valign='top'>
	<form name='FFMCLAM'>
		<table style='width:100%;background-color:#FFFFFF;padding:5px;margin:5px;border:1px solid #CCCCCC'>
		<tr>
			<td class=legend>{SCANNER_ACTION}:</td>
			<td>" . Field_array_Hash($action,'SCANNER_ACTION',$chk->CONF["SCANNER_ACTION"])."</td>
		</tr>
		<tr>
			<td class=legend>{SCANNER_SOCK}:</td>
			<td><strong>$users->CLAMAV_SOCKET</strong></td>
		</tr>
		<tr>
			<td class=legend>{SCANNER_PROTOCOL}:</td>
			<td><strong>CLAMAV</strong></td>
		</tr>	
		<tr>
			<td class=legend>{SCANNER_SAVE}:</td>
			<td>" . Field_yesno_checkbox('SCANNER_SAVE',$chk->CONF["SCANNER_SAVE"])."</td>
		</tr>	
		<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFMCLAM','$page',true);\" value='{edit}&nbsp;&raquo;'></td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
		
	</form>";           
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");	
	}


function clamav_script(){
	$page=CurrentPageName();
$html="
	var tmpnum='';
	
	load();
	
	function load(){
	YahooWin(550,'$page?popup=jchkclamav','','');	
	}
	";
	echo $html;
}

function ArchiveScript(){
	$page=CurrentPageName();
$html="
	var tmpnum='';
	
	load();
	
	function load(){
	YahooWin(550,'$page?popup=Archive','','');	
	}
	function ArchiveAddPopUp(num,pattern){
		YahooWin2(550,'$page?popup=Archive&rule='+num+'&pattern='+pattern,'','');	
	}
	
var x_ArchiveEdit= function (obj) {
	var results=obj.responseText;	
	alert(results)
	YahooWin(550,'$page?popup=Archive&section=list&element='+tmpnum,'','');
}
	
	function ArchiveDelete(element,pattern){
		var XHR = new XHRConnection();
		tmpnum=element;
		XHR.appendData('ArchiveDelete',element);
		XHR.appendData('pattern',pattern);
		XHR.sendAndLoad('$page', 'GET',x_ArchiveEdit);
	
	}
	
	function ArchiveEdit(){
		var XHR = new XHRConnection();
		tmpnum=document.getElementById('element').value;
		XHR.appendData('ArchiveEdit',document.getElementById('element').value);
		XHR.appendData('pattern',document.getElementById('pattern').value);
		XHR.appendData('action',document.getElementById('action').value);
		XHR.sendAndLoad('$page', 'GET',x_ArchiveEdit);
	}	
	
	";
	echo $html;
}	



function RejectShortMsgs_script(){
	$page=CurrentPageName();
	
$html="
	var tmpnum='';
	
	load();
	
	function load(){
	YahooWin(550,'$page?popup=RejectShortMsgs','','');	
	}
	
	function RejectShortMsgsAddPopUp(num,pattern){
		YahooWin2(550,'$page?popup=RejectShortMsgs&rule='+num+'&pattern='+pattern,'','');	
	}
	
var x_RejectShortMsgsEdit= function (obj) {
	var results=obj.responseText;	
	alert(results)
	YahooWin(550,'$page?popup=RejectShortMsgs&section=list&element='+tmpnum,'','');
}
	
	function RejectShortMsgsDelete(element,pattern){
		var XHR = new XHRConnection();
		tmpnum=element;
		XHR.appendData('RejectShortMsgsDelete',element);
		XHR.appendData('pattern',pattern);
		XHR.sendAndLoad('$page', 'GET',x_RejectShortMsgsEdit);
	
	}
	
	function RejectShortMsgsEdit(){
		var XHR = new XHRConnection();
		tmpnum=document.getElementById('element').value;
		XHR.appendData('RejectShortMsgsEdit',document.getElementById('element').value);
		XHR.appendData('pattern',document.getElementById('pattern').value);
		XHR.appendData('action',document.getElementById('action').value);
		XHR.sendAndLoad('$page', 'GET',x_RejectShortMsgsEdit);
	}	
	
	";
	echo $html;
}


function RejectShortMsgs_save(){
	$chk=new jchkmail();
	$chk->RejectShortMsgs[$_GET["RejectShortMsgsEdit"]][$_GET["pattern"]]=$_GET["action"];
	$chk->Save();
	}
function RejectShortMsgs_del(){
$chk=new jchkmail();
	unset($chk->RejectShortMsgs[$_GET["RejectShortMsgsDelete"]][$_GET["pattern"]]);
	$chk->Save();	
}

function RejectShortMsgs_tab(){
	$page=CurrentPageName();
	if($_GET["section"]==null){$_GET["section"]="settings";}
	$array["settings"]='{settings}';
	$array["list"]='{rules}';
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:YahooWin(550,'$page?popup=RejectShortMsgs&section=$num','','')\" $class>$ligne</a></li>\n";
		
	}
	
	return "<div id=tablist>$html</div><br>";
	}
function RejectShortMsgsSub_tab(){
	$page=CurrentPageName();
	if($_GET["element"]==null){$_GET["element"]="To";}
	$array=array("To"=>"{recipient_address}","Connect"=>"{Connection}","From"=>"{sender_address}");
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["element"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:YahooWin(550,'$page?popup=RejectShortMsgs&section=list&element=$num','','')\" $class>$ligne</a></li>\n";
		
	}
	
	return "<div id=tablist>$html</div><br>";	
}


function RejectShortMsgs_rule(){
	$rule=$_GET["rule"];
	$chk=new jchkmail();
	$array=array("To"=>"{recipient_address}","Connect"=>"{Connection}","From"=>"{sender_address}");
	$html="
	<H1>{RejectShortMsgs}</H1>
	<table class=table_form>
	<tr>
		<td class=legend>{check_element}:</td>
		<td>" . Field_array_Hash($array,"element",$rule)."</td>
	</tr>
	<tr>
		<td class=legend>{pattern}:</td>
		<td>" . Field_text("pattern",$_GET["pattern"],"width:120px")."</td>
	</tr>
	<tr>
		<td class=legend>{action}:</td>
		<td>" . Field_array_Hash($chk->FieldElements,'action',$chk->RejectShortMsgs[$rule][$_GET["pattern"]])."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:RejectShortMsgsEdit();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>		
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");		
	}

	
function Archive_save(){
	$chk=new jchkmail();
	$chk->Archive[$_GET["ArchiveEdit"]][$_GET["pattern"]]=$_GET["action"];
	
	$chk->Save();
	}
function Archive_del(){
$chk=new jchkmail();
	unset($chk->Archive[$_GET["ArchiveDelete"]][$_GET["pattern"]]);
	$chk->Save();	
}

function Archive_tab(){
	$page=CurrentPageName();
	if($_GET["section"]==null){$_GET["section"]="settings";}
	$array["settings"]='{settings}';
	$array["list"]='{rules}';
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:YahooWin(550,'$page?popup=Archive&section=$num','','')\" $class>$ligne</a></li>\n";
		
	}
	
	return "<div id=tablist>$html</div><br>";
	}	
function ArchiveSub_tab(){
	$page=CurrentPageName();
	if($_GET["element"]==null){$_GET["element"]="To";}
	$array=array("To"=>"{recipient_address}","Connect"=>"{Connection}","From"=>"{sender_address}");
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["element"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:YahooWin(550,'$page?popup=Archive&section=list&element=$num','','')\" $class>$ligne</a></li>\n";
		
	}
	
	return "<div id=tablist>$html</div><br>";	
}	
function Archive_rule(){
	$rule=$_GET["rule"];
	$chk=new jchkmail();
	$array=array("To"=>"{recipient_address}","Connect"=>"{Connection}","From"=>"{sender_address}");
	$html="
	<H1>{archive}</H1>
	<table class=table_form>
	<tr>
		<td class=legend>{check_element}:</td>
		<td>" . Field_array_Hash($array,"element",$rule)."</td>
	</tr>
	<tr>
		<td class=legend>{pattern}:</td>
		<td>" . Field_text("pattern",$_GET["pattern"],"width:120px")."</td>
	</tr>
	<tr>
		<td class=legend>{action}:</td>
		<td>" . Field_array_Hash($chk->FieldElements,'action',$chk->Archive[$rule][$_GET["pattern"]])."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ArchiveEdit();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>		
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");		
	}
	
function ArchivePopup(){
	$page=CurrentPageName();
	$tab=Archive_tab();
	$tab2=ArchiveSub_tab();
	if(isset($_GET["rule"])){Archive_rule();exit;}
	if($_GET["section"]=="settings"){Archive_settings();exit;}
	
	$chk=new jchkmail();
	$html="
	<H1>{archive}</H1>
	$tab
	<div style='float:right'>
	<input type='button' OnClick=\"javascript:ArchiveAddPopUp('{$_GET["element"]}','');\" value='{add}&nbsp;&raquo;'>
	</div>
	$tab2
	";
	
	if(is_array($chk->Archive[$_GET["element"]])){
		
		$table="<table style='width:100%'>
		<tr>
		<th>&nbsp;</th>
		<th>{pattern}</th>
		<th>{action}</th>
		<th>&nbsp;</th>
		</tr>";
		
		
		while (list ($num, $ligne) = each ($chk->Archive[$_GET["element"]])){
			$js=CellRollOver("ArchiveAddPopUp('{$_GET["element"]}','$num')");
			$table=$table."
			<tr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td nowrap $js><strong style='font-size:12px'>$num </td>
				<td $js><strong style='font-size:12px'>" .$chk->FieldElements[trim($ligne)]."</strong></td>
				<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","ArchiveDelete('{$_GET["element"]}','$num')")."</td>
			</tr>
			";

		}
		$table=$table."</table>";
		$table=RoundedLightWhite($table);
		$table="<div style='width:100%;height:300px;overflow:auto'>$table</div>";
		
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html$table","postfix.index.php");	
	
	
	}

function Archive_settings(){
	$chk=new jchkmail();
	$page=CurrentPageName();
	$tab=Archive_tab();
	$html="
	<H1>{archive}</H1>
	$tab
	<p class=caption>{archive_explain}</p>
	<form name='FFMREJECT_ARCHIVE'>
	<table class=table_form>
	<tr>
		<td class=legend>{enable_filter}:</td>
		<td>" . Field_yesno_checkbox('ARCHIVE',$chk->CONF["ARCHIVE"],'{enable_disable}')."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFMREJECT_ARCHIVE','$page',true);\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
		
	
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
}



function RejectShortMsgs_popup(){
	$page=CurrentPageName();
	$tab=RejectShortMsgs_tab();
	$tab2=RejectShortMsgsSub_tab();
	if(isset($_GET["rule"])){RejectShortMsgs_rule();exit;}
	if($_GET["section"]=="settings"){RejectShortMsgs_settings();exit;}
	$chk=new jchkmail();
	$html="
	<H1>{RejectShortMsgs}</H1>
	$tab
	<div style='float:right'>
	<input type='button' OnClick=\"javascript:RejectShortMsgsAddPopUp('{$_GET["element"]}','');\" value='{add}&nbsp;&raquo;'>
	</div>
	$tab2
	";
	
	if(is_array($chk->RejectShortMsgs[$_GET["element"]])){
		
		$table="<table style='width:100%'>
		<tr>
		<th>&nbsp;</th>
		<th>{pattern}</th>
		<th>{action}</th>
		<th>&nbsp;</th>
		</tr>";
		
		
		while (list ($num, $ligne) = each ($chk->RejectShortMsgs[$_GET["element"]])){
			$js=CellRollOver("RejectShortMsgsAddPopUp('{$_GET["element"]}','$num')");
			$table=$table."
			<tr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td nowrap $js><strong style='font-size:12px'>$num </td>
				<td $js><strong style='font-size:12px'>" .$chk->FieldElements[trim($ligne)]."</strong></td>
				<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","RejectShortMsgsDelete('{$_GET["element"]}','$num')")."</td>
			</tr>
			";
		}
		$table=$table."</table>";
		$table=RoundedLightWhite($table);
		$table="<div style='width:100%;height:300px;overflow:auto'>$table</div>";		
		
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html$table","postfix.index.php");	
	
	
	}
	
function RejectShortMsgs_settings(){
	$chk=new jchkmail();
	$page=CurrentPageName();
	$tab=RejectShortMsgs_tab();
	$html="
	<H1>{RejectShortMsgs}</H1>
	$tab
	<p class=caption>{RejectShortMsgs_explain}</p>
	<form name='FFMREJECT_SHORT_BODIES'>
	<table class=table_form>
	<tr>
		<td class=legend>{enable_filter}:</td>
		<td>" . Field_yesno_checkbox('REJECT_SHORT_BODIES',$chk->CONF["REJECT_SHORT_BODIES"],'{enable_disable}')."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{MIN_BODY_LENGTH}:</td>
		<td>" . Field_text('MIN_BODY_LENGTH',$chk->CONF["MIN_BODY_LENGTH"],'width:90px')."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFMREJECT_SHORT_BODIES','$page',true);\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
		
	
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
}
	






function GreyCheckConnect_script(){
$page=CurrentPageName();
	$html="
	var tmpnum='';
	
	load();
	
	function load(){
	YahooWin(550,'$page?popup=jchkgreylist','','');	
	}
	
var x_GreyCheckConnectEdition= function (obj) {
	var results=obj.responseText;	
	alert(results)
	load();
}
	
	function GreyCheckConnectEdit(num){
		YahooWin2(550,'$page?popup=jchkgreylist&edit='+num,'','');
	
	}
	
	function GreyCheckConnectEdition(){
		var XHR = new XHRConnection();
		XHR.appendData('GreylistServername',document.getElementById('GreylistServername').value);
		XHR.appendData('action',document.getElementById('action').value);
		XHR.appendData('type',document.getElementById('type').value);
		XHR.sendAndLoad('$page', 'GET',x_GreyCheckConnectEdition);
	}
	
	function GreyCheckConnect_del(pattern){
		var XHR = new XHRConnection();
		XHR.appendData('GreyCheckConnect_del',pattern);
		XHR.sendAndLoad('$page', 'GET',x_GreyCheckConnectEdition);
	}
	
	";
	
	echo $html;
}

function GreyCheckConnect_edit(){
	if($_GET["edit"]==-1){$_GET["edit"]=null;}
	
	$action=array(
		"NO-QUICK"=>"{gl_NO-QUICK}",
		"NO"=>"{gl_NO}",
		"YES"=>"{gl_YES}",
		"YES-QUICK"=>"{gl_YES-QUICK}");
		
	$type=array("GreyCheckTo"=>"{GreyCheckTo}","GreyCheckFrom"=>"{GreyCheckFrom}","GreyCheckConnect"=>"{GreyCheckConnect}");

	
	$chk=new jchkmail();
	$html="<H1>{Greylisting}:&nbsp;{$_GET["edit"]}</H1>
	<p class=caption>{greylist_intro}</p>
	<table style='width:100%'>
		<tr>
		<td class=legend>TCP/IP/Name:</td>
		<td valign='top'>" . Field_text('GreylistServername',$_GET["edit"])."</td>
		</tr>
		<td nowrap class=legend>{Greylisting}:</td>
		<td><table><tr><td>" . Field_array_Hash($action,"action",$chk->GreyCheckConnect[$_GET["edit"]][0])."</td><td>" . help_icon('{greylist_explain}',true)."</td></tr></table></td>
		</tr>
		</tr>
		<td nowrap class=legend>{target}:</td>
		<td><table><tr><td>" . Field_array_Hash($type,"type",$chk->GreyCheckConnect[$_GET["edit"]][1])."</td><td>&nbsp;</td></tr></table></td>
		</tr>		
		<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:GreyCheckConnectEdition()\" value='{edit}&nbsp;&raquo;'></td>
		</tr>
		</table>
		";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	}
	
function GreyCheckConnect_save(){
	if($_GET["GreylistServername"]==null){echo "pattern=null\n";exit;}
	$chk=new jchkmail();
	$chk->GreyCheckConnect[$_GET["GreylistServername"]]=array($_GET["action"],$_GET["type"]);
	$chk->Save();
}
function GreyCheckConnect_del(){
$chk=new jchkmail();
unset($chk->GreyCheckConnect[$_GET["GreyCheckConnect_del"]]);	
$chk->Save();
}

function GreyCheckConnect_popup(){
	if(isset($_GET["edit"])){GreyCheckConnect_edit();exit;}
$chk=new jchkmail();	
$html="<H1>{Greylisting}</H1>
	<p class=caption>{Greylisting_text}</p>
	<div style='width:100%;text-align:right'>
		<input type='button' OnClick=\"javascript:GreyCheckConnectEdit(-1);\" value='{add}&nbsp;&raquo;'>
	</div>	
	";


if(is_array($chk->GreyCheckConnect)){
	$table="
	<div style='width:100%;height:300px;overflow:auto'>
	<table style='width:100%'>
	<tr>
	<th>&nbsp;</th>
	<th>{pattern}</th>
	<th>{action}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
	
	";
	while (list ($num, $line) = each ($chk->GreyCheckConnect) ){
		$table=$table . "<tr ".CellRollOver_jaune().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td ".CellRollOver("GreyCheckConnectEdit('$num')")." width=90%><strong>$num</strong>
		<td width=1% nowrap><strong>{gl_{$line[0]}}</td>
		<td width=1% nowrap><strong>{{$line[1]}}</td>
		<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"GreyCheckConnect_del('$num');")."</td>
		</tr>
		";
	}
	
	$table=$table . "</table></div>";
	$table=RoundedLightWhite($table);
	
	$html=$html.$table;
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
}
	
}


function badmx_script(){
	$page=CurrentPageName();
	$html="
	var tmpnum='';
	
	YahooWin(550,'$page?popup=jchkbadmx','','');
	
	function BadMxEdit(num){YahooWin2(550,'$page?popup=jchkbadmx&edit='+num,'','');}
	
var x_BadMxEdition= function (obj) {
	var results=obj.responseText;
	if(results.length<10){
		var page=document.getElementById('pageselected').value;
	 	YahooWin(550,'$page?popup=jchkbadmx&p='+page,'','')
		return;
	}
	if(document.getElementById(tmpnum)){document.getElementById(tmpnum).innerHTML=results;}
}
	
	function BadMxEdition(num){
		tmpnum=num;
		var XHR = new XHRConnection();
		XHR.appendData('badmx',document.getElementById('badmx').value);
		XHR.appendData('BadMxEdition',num);
		XHR.appendData('error',document.getElementById('error').value);
		XHR.sendAndLoad('$page', 'GET',x_BadMxEdition);
	}
	
	function BadMxDelete(num){
		tmpnum=num;
		var XHR = new XHRConnection();
		XHR.appendData('BadMxDelete',num);
		XHR.sendAndLoad('$page', 'GET',x_BadMxEdition);
	}
	
	";
	
	echo $html;
	
}

function badmx_pages($pages_num){
	$page=CurrentPageName();
	for($i=1;$i<$pages_num+1;$i++){
		if($_GET["p"]==$i){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:YahooWin(550,'$page?popup=jchkbadmx&p=$i','','')\" $class>{page} $i</a></li>\n";
		
	}
	
return "<br><div id=tablist>$html</div><br>";
}

function badmx_popup(){
	if(isset($_GET["edit"])){badmx_edit();exit;}
	$chk=new jchkmail();
	if($_GET["p"]==null){$_GET["p"]=1;}
	$html="<H1>{badmx}</H1>
	<p class=caption>{badmx_text}</p>
	<div style='width:100%;text-align:right'>
		<input type='button' OnClick=\"javascript:BadMxEdit(-1);\" value='{add}&nbsp;&raquo;'>
	</div>
	<input type='hidden' id='pageselected' value='{$_GET["p"]}'>
	<div style='width:100%;min-height:300px;overflow:auto' id='tableau'>";
	$start=0;
	if(is_array($chk->BadMX)){
		$count=count($chk->BadMX);
		if($count>50){
			$max=50;
			$pages=round($count/50);
			$tabs=badmx_pages($pages);
			$html=$html .$tabs;
			if(isset($_GET['p'])){
				$_GET["p"]=$_GET["p"]-1;
				$start=$_GET["p"]*50;
				$max=$start+50;
			}
			
			}
		
		$html=$html . "<p>{from} $start {to} $max ($count)</p>";
		$lc=0;
		while (list ($num, $line) = each ($chk->BadMX) ){
			$ip=$line[0];
			$text=$line[1];
			$lc=$lc+1;
			if($lc<$start){continue;}
			if($lc>$max-1){break;}
			
			$html=$html . "<div id='$num' style='width:100px;margin:3px;border:1px solid #CCCCCC;float:left'>
				".badmx_format($num,$ip)."
			</div>";
			}
		}
		
	$html=$html . "</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	}

function badmx_format($num,$ip){

return "<table>
			<tr>	
				<td nowrap " . CellRollOver("BadMxEdit($num)").">". texttooltip($ip,"{edit}","BadMxEdit($num)")."</td>
				<td width=1%>". imgtootltip('ed_delete.gif','{delete}',"BadMxDelete($num)")."</td>
			</tr>
		</table>";
}

function badmx_edit(){
	$chk=new jchkmail();
	$html="<H1>{$chk->BadMX[$_GET["edit"]][0]}</H1>
	
	<p class=caption style='font-size:11px'>{badmx_explain}</p>
	<table style='width:100%'>
		<tr>
		<td class=legend>{badmx} TCP/IP/Name:</td>
		<td valign='top'>" . Field_text('badmx',$chk->BadMX[$_GET["edit"]][0])."</td>
		</tr>
		<td nowrap class=legend>{error}:</td>
		<td>" . Field_text('error',$chk->BadMX[$_GET["edit"]][1])."</td>
		</tr>
		<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:BadMxEdition({$_GET["edit"]})\" value='{edit}&nbsp;&raquo;'></td>
		</tr>
		</table>
		";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	}
	
function badmx_save(){
	$chk=new jchkmail();
	if($_GET["BadMxEdition"]==-1){$_GET["BadMxEdition"]=null;}	
	$chk->BadMX[$_GET["BadMxEdition"]][0]=$_GET["badmx"];
	$chk->BadMX[$_GET["BadMxEdition"]][1]=$_GET["error"];
	$chk->Save(false);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(badmx_format($_GET["BadMxEdition"],$_GET["badmx"]));
}
function badmx_delete(){
	$chk=new jchkmail();
	unset($chk->BadMX[$_GET["BadMxDelete"]]);
	$chk->Save(false);
}





function rate_script(){
	$page=CurrentPageName();
	$tpl=new templates();
	$NetClassHoToAdd=$tpl->_ENGINE_parse_body('{NetClassHoToAdd}');
	$NetClassHoToAdd=str_replace("\n","\\n",$NetClassHoToAdd);
	$AddRate=$tpl->_ENGINE_parse_body('{AddRate}\n');
	$html="
	function load(){
		YahooWin(550,'$page?popup=jchkrate','','');
	}
	load();	
	
	function RatesSelect(){
		rate=document.getElementById('RatesSelect').value;
		YahooWin(550,'$page?popup=jchkrate&RatesSelect='+rate,'','');
	}
	
var x_BadMxEdition= function (obj) {
	var results=obj.responseText;
	RatesSelect();
	}	
	
function RateAddRow(){
	var row=prompt('$NetClassHoToAdd');
	if(row){
		var rate=prompt('$AddRate'+row);
		if(rate){
			var XHR = new XHRConnection();
			XHR.appendData('AddRateSource',row);
			XHR.appendData('rate',rate);
			XHR.appendData('RatesSelect',document.getElementById('RatesSelect').value);
			XHR.sendAndLoad('$page', 'GET',x_BadMxEdition);	
		}
	}
	
}
	function RateDelete(NetClass){
		var XHR = new XHRConnection();
		XHR.appendData('RateDeleteRow',NetClass);
		XHR.appendData('RatesSelect',document.getElementById('RatesSelect').value);
		XHR.sendAndLoad('$page', 'GET',x_BadMxEdition);	
		}
	function RateEditRow(NetClass){
		var XHR = new XHRConnection();
		XHR.appendData('RateEditRow',NetClass);
		XHR.appendData('RatesSelect',document.getElementById('RatesSelect').value);
		XHR.appendData('rate',document.getElementById(NetClass+'_rate').value);
		XHR.sendAndLoad('$page', 'GET',x_BadMxEdition);	
		}
	
	";
	echo $html;
}

function rate_add_source(){
	$chk=new jchkmail();
	$chk->Rates[$_GET["RatesSelect"]][$_GET["AddRateSource"]]=$_GET["rate"];
	$chk->Save();
}

function rate_save(){
	$chk=new jchkmail();
	$chk->Rates[$_GET["RatesSelect"]][$_GET["RateEditRow"]]=$_GET["rate"];
	$chk->Save();
	}
function rate_delete(){
	$chk=new jchkmail();	
	unset($chk->Rates[$_GET["RatesSelect"]][$_GET["RateDeleteRow"]]);
	$chk->Save();
	}

function rate_popup(){
	$page=CurrentPageName();
	$chk=new jchkmail();
	if($_GET["RatesSelect"]==null){$_GET["RatesSelect"]="ConnRate";}
	$html="<H1>{rate_ressources}</H1>
	<p class=caption>{rate_explain}</p>
	
	<table style='width:100%'>
	<tr>
		<td class=legend>{rate_ressources}:</td>
		<td>" . Field_array_Hash($chk->Rates_text,'RatesSelect',$_GET["RatesSelect"],"RatesSelect()")."</td>
	</tr>
	</table>
	<br>	
	<H3 style='border-bottom:1px dotted #CCCCCC'>{{$_GET["RatesSelect"]}}</H3>
	<br>
	<div style='text-align:right'><input type='button' OnClick=\"javascript:RateAddRow();\" value='{add}&nbsp;&raquo;'></div>
	";
	$array=$chk->Rates[$_GET["RatesSelect"]];
	if(is_array($array)){
		$table="
		<table style='width:100%'>
		<tr>
			<th>&nbsp;</th>
			<th>{NetClass}</th>
			<th>{rate}</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>
		
		";
		
		
		while (list ($num, $line) = each ($array) ){
			if($chk->NetClassNames[$num]){
				$js=CellRollOver("Loadjs('$page?script=NetClass&edit=$num');");}else{$js=null;}
			$table=$table."<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td $js><strong style='font-size:12px'>$num</strong></td>
			<td width=1%>" . Field_text("{$num}_rate",$line,'width:40px')."</td>
			<td width=1%><input type='button'value='{edit}&nbsp;&raquo;' OnClick=\"javascript:RateEditRow('$num');\"></td>
			<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"RateDelete('$num')")."</td>
			</tr>
			<tr>
				<td colspan=5 style='border-bottom:1px solid #CCCCCC'>&nbsp;</td></tr>
			";
			
			
		}
		
		$table=$table."</table>";
	}
	$table=RoundedLightWhite($table);
	$table="<div style='width:100%;height:300px;overflow:auto'>$table</div>";
	$html=$html.$table;
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");	
}


function NetClass_popup(){

	if($_GET["edit"]){NetClass_edit();exit;}
	$page=CurrentPageName();
	$chk=new jchkmail();	
	
$html="<H1>{NetClass}</H1>
	<p class=caption>{NetClass_explain}</p>
	<br>
	<div style='text-align:right'><input type='button' OnClick=\"javascript:NetClassAdd();\" value='{add}&nbsp;&raquo;'></div>
	";
	$array=$chk->NetClassNames;
	if(is_array($array)){
		$table="
		<table style='width:100%'>
		<tr>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>{NetClass}</th>
			<th>{sources}</th>
			<th>&nbsp;</th>
		</tr>
		
		";
		
	
		while (list ($num, $line) = each ($array) ){
			$js=CellRollOver("Loadjs('$page?script=NetClass&edit=$num');");
			$delete=imgtootltip('ed_delete.gif','{delete}',"NetClassDelete('$num')");
			
			
			if($chk->NetClassDefaults[$num]){$help=help_icon("{Netclass_$num}",true);$delete=null;}else{$help="&nbsp;";}
			$table=$table."<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td>$help</td>
			<td $js><strong style='font-size:12px'>$num</strong></td>
			<td valign='top' $js>";
				unset($ips);
				unset($s);
				$ips=$chk->GetClassList(trim($num));
				
				if(is_array($ips)){
					while (list ($ip, $tr) = each ($ips) ){
						$s[]=$ip;
					}
					$table=$table.implode(", ",$s);
				}
					
				
			$table=$table."
			</td>
			<td width=1%>$delete</td>
			</tr>
			<tr>
				<td colspan=5 style='border-bottom:1px solid #CCCCCC'>&nbsp;</td></tr>
			";
			
			
		}
		
		$table=$table."</table>";
	}
	$table=RoundedLightWhite($table);
	$table="<div style='width:100%;height:300px;overflow:auto'>$table</div>";
	$html=$html.$table;
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");	
	
	
}

function rate_save_ressources(){
	$chk=new jchkmail();
	$NetClass=$_GET["EditRateRessources"];
	unset($_GET["EditRateRessources"]);		
	while (list ($num, $line) = each ($_GET) ){
		if(is_numeric($line)){$chk->Rates[$num][$NetClass]=$line;}else{unset($chk->Rates[$num][$NetClass]);}
	}
	$chk->Save();
}

function NetClass_edit_ressources(){
	$chk=new jchkmail();
	$NetClass=$_GET["edit"];	
	$tab=NetClass_edit_tab(); 
	$page=CurrentPageName();
	$html="
	<H1>{{$_GET["section"]}}:$NetClass</H1>
	$tab";
	
	$table="
	<form name='ffm1Rate'>
	<input type='hidden' name='EditRateRessources' value='$NetClass'>
	<table style='width:100%'>
	";
	while (list ($num, $line) = each ($chk->Rates_text) ){
		$table=$table."
		<tr>
			<td class=legend>$line</td>
			<td>". Field_text($num,$chk->Rates[$num][$NetClass],"width:40px")."</td>
		</tr>";
	}
	$table=$table."
	<tr><td align='right' colspan=2>&nbsp;</td></tr>
	<tr>
		<td align='right' colspan=2 style='padding:5px;border-top:1px solid #CCCCCC'>
			<input type='button' OnClick=\"javascript:ParseForm('ffm1Rate','$page',true);\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>
	</table></form>";
	$table=RoundedLightWhite($table);
	$html="$html<center><div style='width:300px'>$table</div></center>";
	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");		
}


function NetClass_add(){
	$chk=new jchkmail();
	$chk->NetClass[$_GET["NetClassSource"]]=$_GET["NetClassAdd"];
	$chk->Save();
	}
	
function NetClass_del(){
	$chk=new jchkmail();
	$chk->DeleteClassList($_GET["NetClassDelete"]);
}


function NetClass_script(){
	$page=CurrentPageName();
	if($_GET["edit"]<>null){
		$start="LoadEdited();";
	}else{
		$start="Load();";
	}
	
	$tpl=new templates();
	$NetClassHoToAdd=$tpl->_ENGINE_parse_body("{NetClassHoToAdd}");
	$NetClassHoToAdd=str_replace("\n","\\n",$NetClassHoToAdd);
	$NetClassAddName=$tpl->_ENGINE_parse_body("{NetClassAddName}");
	$NetClassAddName=str_replace("\n","\\n",$NetClassAddName);
	$html="
	function Load(){
		YahooWin(550,'$page?popup=NetClass&edit=$NetClass&section=$num');
	}
	function LoadEdited(){
		YahooWin2(550,'$page?popup=NetClass&edit={$_GET["edit"]}');
	}
	
var x_NetClassAddSource= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	LoadEdited();
	}

var x_NetClassAdd= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	Load();
	}		
	
	function NetClassAddSource(NetClass){
		var NetClassSource=prompt('$NetClassHoToAdd');
		if(NetClassSource){
			var XHR = new XHRConnection();
			XHR.appendData('NetClassAddSource',NetClass);
			XHR.appendData('source',NetClassSource);
			XHR.sendAndLoad('$page', 'GET',x_NetClassAddSource);	
			}
	}
	
	
	function NetClassAdd(){
		var NetClassName=prompt('$NetClassAddName');
		if(NetClassName){
			NetClassSource=prompt('$NetClassHoToAdd');
			if(NetClassSource){
				var XHR = new XHRConnection();
				XHR.appendData('NetClassAdd',NetClassName);
				XHR.appendData('NetClassSource',NetClassSource);
				XHR.sendAndLoad('$page', 'GET',x_NetClassAdd);	
			}
		}
	}
	
	
	function NetClassDelete(NetClass){
		var XHR = new XHRConnection();
		XHR.appendData('NetClassDelete',NetClass);
		XHR.sendAndLoad('$page', 'GET',x_NetClassAdd);	
	}
	
	
	$start;
	";
	echo $html;
}

function NetClass_addsrc(){
	$chk=new jchkmail();
	$chk->NetClass[$_GET["source"]]=$_GET["NetClassAddSource"];
	$chk->Save();
	
}

function NetClass_edit(){
	if($_GET["section"]=="rate_ressources"){NetClass_edit_ressources();exit;}
	$chk=new jchkmail();
	$NetClass=$_GET["edit"];
	$tab=NetClass_edit_tab(); 
	
	$html="
	<H1>{{$_GET["section"]}}:$NetClass</H1>
	$tab
	<p class=caption>{NetClass_explain}</p>
	";
	
	$button="<div style='text-align:right'><input type='button' OnClick=\"javascript:NetClassAddSource('$NetClass');\" value='{add}&nbsp;&raquo;'></div>";
	
	if($chk->NetClassDefaults[$NetClass]){
		$html=$html."
		<div style='padding:5px;background-color:white;border:1px solid #CCCCCC;margin:5px'>
		<strong style='width:13px'>Class $NetClass:</strong><br>{Netclass_$NetClass}</div>";
	}
	
	$class_list=$chk->GetClassList($NetClass);
	
	if(is_array($class_list)){
		$table="
		<table style='width:100%'>";
		while (list ($num, $line) = each ($class_list) ){
			$table=$table."
			<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><strong style='font-size:13px'>$num</strong></td>
				<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"NetClassDelete('$num')")."</td>
			</tr>";
			
		}
		$table=$table."</table>";
	}
	
	$table=RoundedLightWhite($table);
	if($chk->NetClassNoFill[$NetClass]){$table=null;$button=null;}
	
	$html=$html.$button.$table;
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");		
}

function NetClass_edit_tab(){
	$page=CurrentPageName();
	$NetClass=$_GET["edit"];
	if($_GET["section"]==null){$_GET["section"]="NetClass";}
	$array["NetClass"]='{sources}';
	$array["rate_ressources"]='{rate_ressources}';
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:YahooWin2(550,'$page?popup=NetClass&edit=$NetClass&section=$num','','')\" $class>$ligne</a></li>\n";
		
	}
	
return "<br><div id=tablist>$html</div><br>";	
	
}

function SaveGlobalConf(){
$jch=new jchkmail();
	while (list ($num, $line) = each ($_GET)){
		if(trim(strtolower($line))=="yes"){$line="YES";}
		if(trim(strtolower($line))=="no"){$line="NO";}
		$jch->CONF[$num]=$line;
			
	}
	
	$jch->Save();
}



?>