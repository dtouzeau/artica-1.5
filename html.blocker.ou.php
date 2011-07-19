<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	$users=new usersMenus();
	
	if(!isset($_GET["ou"])){header('location:domains.manage.org.index.php');}
	if($users->AsArticaAdministrator){$users->AsPostfixAdministrator=true;}
	if(!$users->AsPostfixAdministrator){header('location:domains.manage.org.index.php');}
	if(isset($_GET["main"])){echo blocker_switch();exit;}
	if(isset($_GET["AddNewRule"])){blocker_addrule();exit;}
	if(isset($_GET["Blockermove"])){blocker_move();exit;}
	if(isset($_GET["SaveGeneralSettings"])){blocker_save();exit;}
	if(isset($_GET["blockerdelterule"])){blocker_delrule();exit;}
	if(isset($_GET["bodyNotfif"])){blocker_body_2();exit;}
	if(isset($_GET["BigMailHtmlBody"])){BigMailHtmlBody();exit;}
	
	blocker_main();
	
function blocker_main(){
	$html=
	"<p class=caption>{htmlSizeBlocker_text}</p><div id='mainconfig'>" . blocker_switch() . "</div>";
	$JS["JS"][]='js/htmlblocker.js';
	$tpl=new template_users("{htmlSizeBlocker}",$html,0,0,0,0,$JS);
	
	echo $tpl->web_page;
	
}
	
	
	
	
function blocker_switch(){
	if($_GET["tab"]==null){$_GET["tab"]="param";}
	switch ($_GET["tab"]) {
		case "param":return blocker_settings($_GET["ou"]);break;
		case "addrule":return blocker_rules($_GET["ou"]);break;
		case "rules":return blocker_ruleslist($_GET["ou"]);break;
		case "body":return blocker_body($_GET["ou"]);break;
		default:return blocker_settings($_GET["ou"]);break;
	}
	
	
}

function blocker_body(){
	$page=CurrentPageName();
	
	$htmlblocker=new htmlblocker($_GET["ou"]);
	$ou=$_GET["ou"];
	$form=blocker_tabs() ."<br><H5>{body_notification}</H5><br>
	<p class=caption>{body_notification_text}</p><br>
	<form name='FFM2'>
	<input type='hidden' name=ou value='$ou'>
	<div style='text-align:right'><input type='button' value='&nbsp;&nbsp;{edit}&nbsp;&raquo;&nbsp;&nbsp;' OnClick=\"javascript:ParseForm('FFM2','$page',true);\"></div>
	
	<textarea name='BigMailHtmlBody' style='width:100%' rows=10>$htmlblocker->BigMailHtmlBody</textarea>
	</FORM>
	";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($form);	
	
}


function BigMailHtmlBody(){
	$htmlblocker=new htmlblocker($_GET["ou"]);
	$htmlblocker->BigMailHtmlBody=$_GET["BigMailHtmlBody"];
	$htmlblocker->Save();
	
}


function blocker_rules(){
	$page=CurrentPageName();
	$htmlblocker=new htmlblocker($_GET["ou"]);
	$ou=$_GET["ou"];
	
	//print_r($htmlblocker->ruleslist);
	$tbl=explode(";",$htmlblocker->ruleslist[$_GET["num"]]);
	
	
	$form="
	<form name='FFM2'>
	<input type='hidden' name=ou value='$ou'>
	<input type='hidden' name=ruleid value='{$_GET["num"]}'>
	<input type='hidden' name='AddNewRule' value='yes'>	
	<H5>{add_rule}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% align='right' nowrap><strong>{from}</strong>:</td>
	<td>" . Field_text('from',$tbl[0],'width:60%',null,null,'{from_text}') . "</td>
	</tr>
	<tr>
	<td width=1% align='right' nowrap><strong>{to}</strong>:</td>
	<td>" . Field_text('to',$tbl[1],'width:60%',null,null,'{from_text}') . "</td>
	</tr>
	<tr>
	<td width=1% align='right' nowrap><strong>{extensions}</strong>:</td>
	<td>" . Field_text('extensions',$tbl[3],'width:30%',null,null,'{extensions_text}') . "&nbsp;</td>
	</tr>	
	<tr>
	<td width=1% align='right' nowrap><strong>{maxsize}</strong>:</td>
	<td>" . Field_text('maxsize',$tbl[2],'width:30%',null,null,null) . "&nbsp;MB</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM2','$page',true);LoadAjax('mainconfig','html.blocker.ou.php?main=yes&tab=rules&ou=klf')\"></td>
	</tr>
	</table>
	</form>";
	
	$form=RoundedLightGrey($form);
	
$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($form);	
	
}

function blocker_ruleslist(){
	$page=CurrentPageName();
	$htmlblocker=new htmlblocker($_GET["ou"]);	
	
$tb="	<center>
	<div style='text-align:right;margin:3px'>
		<input type='button' OnClick=\"javascript:BlockerAddNewRule('{$_GET["ou"]}');\" value='{add_rule}&nbsp;&raquo;'>
	</div>";
	
	if(is_array($htmlblocker->ruleslist)){
		
$tb=$tb."

	<table style='width:80%'>
	<tr>
		<th>&nbsp;</th>
		<th>{from}</th>
		<th>{to}</th>
		<th width=70px nowrap>{maxsize}</th>
		<th>{extensions}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>";
		while (list ($num, $ligne) = each ($htmlblocker->ruleslist) ){
			$tbl=explode(';',$ligne);
			
			$editlnk=imgtootltip('icon_newest_reply.gif','{edit}',"BlockerAddNewRule('{$_GET["ou"]}','$num')");
			
			$tb=$tb . "<tr " . CellRollOver().">
			<td width=1%>$editlnk</td>
			<td><strong>{$tbl[0]}</td>
			<td><strong>{$tbl[1]}</td>
			<td><strong>{$tbl[2]} mb</td>
			<td><strong>{$tbl[3]}</td>
			<td width=1% valign='top'>" . imgtootltip('arrow_down.gif','{down}',"Blockermove('{$_GET["ou"]}','$num','down')")."</TD>
			<td width=1% valign='top'>" . imgtootltip('arrow_up.gif','{up}',"Blockermove('{$_GET["ou"]}','$num','up')")."</TD>
			<td width=1%>" . imgtootltip('x.gif','{delete}',"blockerdelterule('{$_GET["ou"]}',$num)")."</td>
			</tr>";
		}
		$tb=$tb . "</table></center>";
		
	}
	
$form=blocker_tabs() ."<br>".RoundedLightGrey($tb);
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($form);	
	
	
}

function blocker_settings(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$htmlblocker=new htmlblocker($_GET["ou"]);
	$ou=$_GET["ou"];
	$form="
	
	<form name='FFM1'>
	<input type='hidden' name=ou value='$ou'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>

	<table style='width:100%'>
	<tr>
	<td align='right' nowrap><strong>{enable} {htmlSizeBlocker}:</strong></td>
	<td>" . Field_yesno_checkbox('BigMailHTMLEnabled',$htmlblocker->BigMailHTMLEnabled) ."</td>
	</tr>
	<tr>
	<td align='right'><strong>{path}:</strong></td>
	<td><strong>$users->ARTICA_FILTER_QUEUE_PATH/bightml</strong></td>
	</tr>		
	<tr>
	<td align='right'><strong>{maxday}:</strong></td>
	<td>" . Field_text('maxday',$htmlblocker->params["config"]["maxday"],'width:20px') ."</td>
	</tr>
	<tr>
	<td align='right'><strong>{prependsubject}:</strong></td>
	<td>" . Field_text('prependsubject',$htmlblocker->params["config"]["prependsubject"],'width:200px') ."</td>
	</tr>
	<tr>
	<td align='right'><strong>{addhostname}:</strong></td>
	<td>" . Field_text('hostname',$htmlblocker->params["config"]["hostname"],'width:300px') ."</td>
	<tr>
	<td></td>
	<td class=caption>{addhostname_text}</td>
	</tr>	
	</tr>	
<tr>
	<td align='right' colspan=2><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','$page',true);\"></td>
	</tr>	
	</table>
	</form>
	
	";
	$form=blocker_tabs() ."<br>".RoundedLightGrey($form);
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($form);
	
	
}


function blocker_tabs(){
	//add_rule
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array["param"]='{parameters}';
	$array["body"]='{body_notification}';
	$array["rules"]='{rules}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('mainconfig','$page?main=yes&tab=$num&ou={$_GET["ou"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function blocker_addrule(){
	$ou=$_GET["ou"];
	$bl=new htmlblocker($ou);
	$tpl=new templates();
	if($bl->addrule($_GET["from"],$_GET["to"],$_GET["maxsize"],$_GET["extensions"],$_GET["ruleid"])){
		echo $tpl->_ENGINE_parse_body('{success}');
	}else{echo $tpl->_ENGINE_parse_body('{failed}');}
}
function blocker_move(){
		$ou=$_GET["ou"];
		$bl=new htmlblocker($ou);
		$newarray=array_move_element($bl->ruleslist,$bl->ruleslist[$_GET["Blockermove"]],$_GET["move"]);
		while (list ($num, $ligne) = each ($newarray) ){
			$up["BigMailHtmlRules"][]=$ligne;
		}
		
		$ldap=new clladp();
		$ldap->Ldap_modify($bl->dn,$up);
	}
function blocker_save(){
	$ou=$_GET["ou"];
	$bl=new htmlblocker($ou);
	$bl->BigMailHTMLEnabled=$_GET["BigMailHTMLEnabled"];
	unset($_GET["BigMailHTMLEnabled"]);
	
	
while (list ($num, $ligne) = each ($_GET) ){$bl->params["config"][$num]=$ligne;}	
$tpl=new templates();
	if($bl->Save()){
		echo $tpl->_ENGINE_parse_body('{success}');
	}else{echo $tpl->_ENGINE_parse_body('{failed}');}
	
	
}


function blocker_delrule(){
	$ou=$_GET["ou"];
	$bl=new htmlblocker($ou);	
	$datas["BigMailHtmlRules"]=$bl->ruleslist[$_GET["blockerdelterule"]];
	$ldap=new clladp();
	$ldap->Ldap_del_mod($bl->dn,$datas);
	}
	
	
	
	
class htmlblocker{
	
	var $ou;
	var $dn;
	var $BigMailHTMLEnabled;
	var $BigMailHtmlConfig;
	var $ruleslist;
	var $params;
	var $default_uri;
	var $BigMailHtmlBody;
	
	
	function htmlblocker($ou){
		$this->ou=$ou;
		$this->default_uri="https://{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}/blocked_attachments";
		$ldap=new clladp();
		$this->dn="cn=html_blocker,ou=$ou,dc=organizations,$ldap->suffix";
		$this->Parse();
		$this->FillDefault();
		
	}
	
	function Parse(){
		$ldap=new clladp();
		if(!$ldap->ExistsDN($this->dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='ArticaOuBigMailHTML';
			$upd["cn"][]="html_blocker";
			$upd["BigMailHTMLEnabled"][]='no';
			$upd["BigMailHtmlConfig"][]='NONE';
			$upd["BigMailHtmlBody"][]="original Attached files of this message are stored \nOn our server.You can download them by clicking on link at the bottom of this email";
			$ldap->ldap_add($this->dn,$upd);
		}
		
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=ArticaOuBigMailHTML)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->BigMailHTMLEnabled=$hash[0][strtolower('BigMailHTMLEnabled')][0];
			$this->BigMailHtmlConfig=$hash[0][strtolower('BigMailHtmlConfig')][0];
			if(!isset($hash[0][strtolower('BigMailHtmlBody')])){
				$upd["BigMailHtmlBody"][]="<b style='color:red'>original Attached files of this message are stored 
											on our server<br>You can download them by clicking on link at the bottom of this email</b>";
				$ldap->Ldap_add_mod($this->dn,$upd);
				unset($upd);
			}
			$this->BigMailHtmlBody=$hash[0][strtolower('BigMailHtmlBody')][0];
			$ini=new Bs_IniHandler();
			$ini->loadString($this->BigMailHtmlConfig);
			
			$this->params=$ini->_params;
			
			for($i=0;$i<$hash[0][strtolower('BigMailHtmlRules')]["count"];$i++){
				$this->ruleslist[]=$hash[0][strtolower('BigMailHtmlRules')][$i];
			}
		}
	
	}
	
	function FillDefault(){
		
		$users=new usersMenus();
		if($this->params["config"]["path"]==null){$this->params["config"]["path"]=$users->ARTICA_FILTER_QUEUE_PATH;}
		if($this->params["config"]["maxday"]==null){$this->params["config"]["maxday"]="2";}
		if($this->params["config"]["prependsubject"]==null){$this->params["config"]["prependsubject"]="[message too big]";}
		if($this->params["config"]["hostname"]==null){$this->params["config"]["hostname"]=$this->default_uri;}
		if(!preg_match('#://#',$this->params["config"]["hostname"])){$this->params["config"]["hostname"]=$this->default_uri;}
		if(!is_numeric($this->params["config"]["maxday"])){$this->params["config"]["maxday"]=2;}
		if($this->BigMailHtmlBody==null){$this->BigMailHtmlBody="original Attached files of this message are stored on our server\n
		You can download them by clicking on link at the bottom of this email";}
		
	}
	
	function addrule($from,$to,$maxsize,$ext,$ruleid){
		
		if(is_numeric($ruleid)){
			if($ruleid>-1){
				$this->ruleslist[$ruleid]="$from;$to;$maxsize;$ext";
				reset($this->ruleslist);
				while (list ($num, $ligne) = each ($this->ruleslist) ){
					$upd["BigMailHtmlRules"][]=$ligne;
				}
				
					$ldap=new clladp();
					return $ldap->Ldap_modify($this->dn,$upd);
			}
		}
		
		$upd["BigMailHtmlRules"]="$from;$to;$maxsize;$ext";
		$ldap=new clladp();
		return $ldap->Ldap_add_mod($this->dn,$upd);
		
	}
	
	function Save(){
		$tpl=new templates();
		writelogs("enabled=$this->BigMailHTMLEnabled",__FUNCTION__,__FILE__);
		$upd["BigMailHTMLEnabled"][0]=$this->BigMailHTMLEnabled;
		$ini=new Bs_IniHandler();
		$ini->_params=$this->params;
		$upd["BigMailHtmlConfig"][0]=$ini->toString();
		$upd["BigMailHtmlBody"][0]=$this->BigMailHtmlBody;
		$ldap=new clladp();
		if($ldap->Ldap_modify($this->dn,$upd)){
			echo $tpl->_ENGINE_parse_body('{success}');}else{echo $ldap->ldap_last_error;}
		
		
		
	}
	
	
	

	
	
	
	
	
}
?>	