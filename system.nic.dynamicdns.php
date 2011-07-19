<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.users.menus.inc');
	$usersmenus=new usersMenus();
	if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}

	if(isset($_GET["palette"])){pallette();exit;}
	if(isset($_GET["Addrule"])){add_dynamic_rule_form();exit;}
	if(isset($_GET["delete_alias"])){SaveRule();exit;}
	if(isset($_GET["ruleid"])){SaveRule();exit;}
	if(isset($_GET["section"])){Sections();exit;}
	if(isset($_GET["DeleteRuleID"])){DeleteRuleID();exit;}
	if(isset($_GET["ArticaInadynPoolRule"])){SaveSettings();exit;}
	if(isset($_GET["infouri"])){UpdateUri();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["status"])){inadynstatus();exit;}
	
StartPage();
function StartPage(){
	$page=CurrentPageName();
	
	$html="
	<script>
	function UpdateUri(){
		var email=document.getElementById('dyndns_system').value;
		LoadAjax('infouri','$page?infouri='+email);
		}
	
	</script>
	<p class='caption'>{dynamic_dns_text}</p>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=70%><div id='middle_area'></div></td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
	<td><div id='palette'></div></td>
	</tr>
	<td><iframe style='border:0px solid;margin:0px;padding:0px;width:225px;height:150px' src='$page?status=yes'></iframe></td>
	</tr>
	</table>
	</td>
	</tr>
	<td>
	</tr>
	</table>
	<script>LoadAjax('palette','$page?palette=yes');</script>
	<script>LoadAjax('middle_area','$page?section=yes&tab=0');</script>
	
		
	
	
	";
	
$tpl=new template_users('{dynamic_dns}',$html);
echo $tpl->web_page;
	
}


function Sections(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;}
	switch ($_GET["tab"]) {
		case 0:ListRules();break;
		case 1:DaemonSettings();break;
		case 2:events_start();break;
		default:ListRules();break;
	}
	
}


function events_start(){
	$page=CurrentPageName();
	$html=tabs() . "<br><iframe src='$page?events=yes' style='width:100%;height:500px;border:0px solid'></iframe>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function events(){
	
	$sock=new sockets();
	$datas=$sock->getfile('logs_inadyn');
	$tbl=explode("\n",$datas);
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			$html=$html . "<div style='color:white;margin-bottom:3px;font-size:10px'><code>$val</code></div>";
			
		}
		$logs=RoundedBlack($html);
		echo iframe($logs);
	
}



function pallette(){
	//add-connection-64.png
	$page=CurrentPageName();
	$html=RoundedLightGrey(Paragraphe('connection-add-64.png','{add_dynamic_dns}','{add_dynamic_dns_text}',"javascript:LoadAjax(\"middle_area\",\"$page?Addrule=yes\");"));
	
	$html=$html . "<br>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function add_dynamic_rule_form(){
	$page=CurrentPageName();
	if(!isset($_GET["ruleid"])){$ruleid="add";}else{
		$ruleid=$_GET["ruleid"];$hash=GetRuleidArray($ruleid);
		$delete=
		
		"<table style='width:100%'>
		<tr>
		<td align='right'>
		<form name='ffm2'>
			<input type='hidden' name='DeleteRuleID' value='$ruleid'>
			<input type='button' value='{delete}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm2','$page',true);LoadAjax('middle_area','$page?section=yes&tabs=0');\">
			</form>
		</td>
		</tr>
		</table>
		
		";
		$delete="<br>" . RoundedLightGreen($delete);
		}
	
	$service=arraserv();
	
$AL=explode(',',$hash["alias"]);
if(is_array($AL)){
	$alias="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($AL) ){
		$alias=$alias . "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$ligne</td>
		<td width=1%>" . imgtootltip('x.gif','{delete}',"LoadAjax('middle_area','$page?ruleid=$ruleid&delete_alias=$num');") . "</td>
		</tr>";
	}
	$alias=RoundedLightGreen($alias."</table>");
}	

	$add_server="<br>" . RoundedLightGrey("
	
	<table style='width:100%'>
		<tr>
		<td width=1% valign='top'>
		<img src='img/chiffre3.png'>
		</td>
		<td valign='top'><H5>{dynhosts}</H5><br>
		<table style='widh:100%'>
		<tr>
			<td align='right'><strong>{dns_host}:</strong></td>
			<td>" . Field_text('alias',null,'width:150px')."</td>
		</tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			<td align='right' valign='top'><strong>{dns_hosts}:</strong></td>
			<td valign='top'>$alias</td>
		</tr>		
		</table>	
		</tr>
	</table>");	
	
	
	$form=tabs() . "<br>
	<input type='hidden' value='$ruleid' name='ruleid'>
	<table style='width:100%'>
	<tr>
	<td>" . RoundedLightGrey("
	
	<table style='width:100%'>
		<tr>
		<td width=1% valign='top'>
		<img src='img/chiffre1.png'>
		</td>
		<td valign='top'><H5>{select_service}</H5><br>
		" . Field_array_Hash($service,'dyndns_system',$hash["dyndns_system"],"UpdateUri();",'',0,'width:225px') . "
		<br>
		<div id='infouri'></div>
		</td>
		</tr>
	</table>") ."</td>
	</tr>
	
<tr>
	<td>
		<br>" . RoundedLightGrey("
	
	<table style='width:100%'>
		<tr>
		<td width=1% valign='top'>
		<img src='img/chiffre2.png'>
		</td>
		<td valign='top'><H5>{give_username_password}</H5><br>
		<table style='widh:100%'>
		<tr>
			<td align='right'><strong>{username}:</strong></td>
			<td>" . Field_text('username',$hash["username"],'width:150px')."</td>
		</tr>
		<tr>
			<td align='right'><strong>{password}:</strong></td>
			<td>" . Field_text('password',$hash["password"],'width:150px')."</td>
		</tr>	
		</table>	
		</tr>
	</table>") ."</td>
	</tr>
	<tr>
	<td>$add_server</td>
	</tr>
	
	
<tr>
	<td align='right'>
		<input type='button' OnClick=\"javascript:ParseForm('ffm1','$page',true);LoadAjax('middle_area','$page?section=yes&tab=0');\" value='{add}&nbsp;&raquo;'>
	</td>
</tr>
</table>";


	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<form name='ffm1'>$form</form>$delete");
	
}

function tabs(){
	
	
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{rules_list}';
	$array[]='{daemon_settings}';
	$array[]='{events}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('middle_area','$page?section=yes&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function SaveRule(){
	include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
	$return_rule=false;
	$ruleid=$_GET["ruleid"];
	if($ruleid==null){$ruleid="add";}
	$HASH["inadyn"]=GetRuleidArray($ruleid);
	
	$aliases=explode(',',$HASH["inadyn"]["alias"]);
	
	if($_GET["alias"]<>null){$aliases[]=$_GET["alias"];unset($_GET["alias"]);}
	if($_GET["delete_alias"]){
		unset($aliases[$_GET["delete_alias"]]);
		unset($_GET["delete_alias"]);
		$return_rule=true;
		}
	
	
	$ldap=new clladp();
	$dn="cn=inadyn,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='ArticaInadyn';
		$upd["cn"]="inadyn";
		$upd["ArticaInadynPoolRule"][]="10";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
	}
	
	if(isset($_GET)){while (list ($num, $ligne) = each ($_GET) ){$HASH["inadyn"][$num]=$ligne;}}
	if(isset($aliases)){while (list ($num, $ligne) = each ($aliases)){if ($ligne<>null) {$NEWAL[$ligne]=$ligne;}}}
	
	
	
	$HASH["inadyn"]["alias"]=implode(',',$NEWAL);
	
	$ini=new Bs_IniHandler();
	$ini->_params=$HASH;
	$rule=$ini->toString();
	writelogs("Save rule $rule",__FUNCTION__,__FILE__);
	
	if($ruleid=="add"){
		$upd["ArticaInadynRule"]=$rule;
		if(!$ldap->Ldap_add_mod($dn,$upd)){echo $ldap->ldap_last_error;}
	}else{
		$upd["ArticaInadynRule"][$ruleid]=$rule;
		if(!$ldap->Ldap_modify($dn,$upd)){echo $ldap->ldap_last_error;}
	}
	
	
	if($return_rule){echo add_dynamic_rule_form();exit;}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
}



function SaveSettings(){
		$ldap=new clladp();
	$ArticaInadynPoolRule=$_GET["ArticaInadynPoolRule"];
	$dn="cn=inadyn,cn=artica,$ldap->suffix";

	
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='ArticaInadyn';
		$upd["cn"]="inadyn";
		$upd["ArticaInadynPoolRule"][]="$ArticaInadynPoolRule";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
	}	
	
	$upd["ArticaInadynPoolRule"][0]=$ArticaInadynPoolRule;
	if(!$ldap->Ldap_modify($dn,$upd)){echo $ldap->ldap_last_error;exit;}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}



function DaemonSettings(){
	
	$ldap=new clladp();
	$page=CurrentPageName();
	$dn="cn=inadyn,cn=artica,$ldap->suffix";
	$pattern="(objectClass=*)";
	$attr=array("ArticaInadynPoolRule");
	$sr =ldap_read($ldap->ldap_connection,$dn,$pattern,$attr);
	if($sr){$hash=ldap_get_entries($ldap->ldap_connection,$sr);}else{$ArticaInadynPoolRule=10;}

	$ArticaInadynPoolRule=$hash[0][strtolower("ArticaInadynPoolRule")][0];
	$e[5]="5mn";
	$e[10]="10mn";
	$e[15]="15mn";
	$e[20]="20mn";
	$e[25]="25mn";
	$e[30]="35mn";
	$e[60]="1H";	
	$e[120]="2H";		
	$e[240]="4H";
	$e[360]="6H";			
	$e[720]="12H";	
	$e[1440]="24H";		

	$field=Field_array_Hash($e,'ArticaInadynPoolRule',$ArticaInadynPoolRule,null,null,0,'width:150px');
	$page=CurrentPageName();
	
	$form=tabs() . "<br>
	<form name='ffm1'>
	<table style='width:100%'>
	<tr>
	<td><H5>{pool_time}</H5><br>
	" . RoundedLightGrey("
	
	<table style='width:100%'>
		<tr>
		<td width=1% valing='top' nowrap align='right'>
		<strong>{update_frequency}:</strong>
		</td>
		<td valign='top'>
			$field
		</td>
		</tr>
		<tr>
		<td align='right' colspan=2><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);LoadAjax('middle_area','$page?section=yes&tab=1');\"></td>
		</tr>
	</table>") ."</td>
	</tr>
	</table></form>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($form);
	
}

function ListRules(){
	include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
	$ldap=new clladp();
	$dn="cn=inadyn,cn=artica,$ldap->suffix";
	$pattern="(objectClass=*)";
	$attr=array("ArticaInadynRule");
	$sr =@ldap_read($ldap->ldap_connection,$dn,$pattern,$attr);
	if($sr){$hash=ldap_get_entries($ldap->ldap_connection,$sr);}
	if(!is_array($hash)){return null;}
	$page=CurrentPageName();
	$service=arraserv();
	$table="<table style='width:100%'>";
	
	for($i=0;$i<$hash["count"];$i++){
		
		for($z=0;$z<$hash[$i][strtolower("ArticaInadynRule")]["count"];$z++){
				$ArticaInadynRule=$hash[$i][strtolower("ArticaInadynRule")][$z];
				
				$ini=new Bs_IniHandler();
				$ini->loadString($ArticaInadynRule);
				$ini->_params["inadyn"]["alias"]=str_replace(',',$ini->_params["inadyn"]["alias"],' ,',$ini->_params["inadyn"]["alias"]);
				$link=CellRollOver("LoadAjax('middle_area','$page?Addrule=yes&ruleid=$z')");
				$table=$table . 
				"<tr>
				<td width=1%><img src='img/internet.png'></td>
				<td $link><strong style='font-size:13px' nowrap>{$service[$ini->_params["inadyn"]["dyndns_system"]]}</strong></td>
				<td $link><strong style='font-size:13px'>{$ini->_params["inadyn"]["username"]}</strong><br><i>{$ini->_params["inadyn"]["alias"]}</i></td>
				
				</tr>
				
				
				";
		}
		
	}
	
	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(tabs() . "<br>$table");
	
}

function GetRuleidArray($ruleid){
	if($ruleid=="add"){return null;}
	$ldap=new clladp();
	$dn="cn=inadyn,cn=artica,$ldap->suffix";
	$pattern="(objectClass=*)";
	$attr=array("ArticaInadynRule");
	$sr =ldap_read($ldap->ldap_connection,$dn,$pattern,$attr);
	if($sr){$hash=ldap_get_entries($ldap->ldap_connection,$sr);}
	if(!is_array($hash)){return array();}	
	include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
	$ini=new Bs_IniHandler();
	$ini->loadString($hash[0][strtolower("ArticaInadynRule")][$ruleid]);
	return $ini->_params["inadyn"];
	
	
}

function arraserv(){
	
	$service=array(
	null=>"{select}",
	"dyndns@dyndns.org"=>"dyndns.org (standard)",
	"statdns@dyndns.org"=>"dyndns.org (stadns)",
	"custom@dyndns.org"=>"dyndns.org (custom)",
	"default@freedns.afraid.org"=>"freedns.afraid.org",
	"default@zoneedit.com"=>"zoneedit.com",
	"default@no-ip.com"=>"no-ip.com");return $service;
}

function arrayUrlserv(){
	return array(
	"dyndns@dyndns.org"=>"https://www.dyndns.com",
	"statdns@dyndns.org"=>"https://www.dyndns.com",
	"custom@dyndns.org"=>"https://www.dyndns.com",
	"default@freedns.afraid.org"=>"http://freedns.afraid.org/",
	"default@zoneedit.com"=>"http://www.zoneedit.com/",
	"default@no-ip.com"=>"http://www.no-ip.com/");
	
}

function UpdateUri(){
	$arr=arrayUrlserv();
	$html="<p class='caption'><a href='{$arr[$_GET["infouri"]]}' target='_new'>{infouri_text}{$arr[$_GET["infouri"]]}</a></p>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function DeleteRuleID(){
	$ldap=new clladp();
	$ruleid=$_GET["DeleteRuleID"];
	$dn="cn=inadyn,cn=artica,$ldap->suffix";
	$pattern="(objectClass=*)";
	$attr=array("ArticaInadynRule");
	$sr =ldap_read($ldap->ldap_connection,$dn,$pattern,$attr);
	if($sr){$hash=ldap_get_entries($ldap->ldap_connection,$sr);}	
	if(!is_array($hash)){echo null;exit;}	
	$upd["ArticaInadynRule"]=$hash[0][strtolower("ArticaInadynRule")][$ruleid];
	if(!$ldap->Ldap_del_mod($dn,$upd)){echo $ldap->ldap_last_error;}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
	
}

function inadynstatus(){
	
	$sock=new sockets();
	$datas=$sock->getfile('pids_inadyn');
	
	$tbl=explode(" ",$datas);
	while (list ($num, $ligne) = each ($tbl)){
		if(preg_match("#([0-9]+)#",$ligne,$reg)){
		
			$pid[$reg[1]]=$reg[1];
		}
	}
	$tpl=new templates();
	if(count($pid)==0){
		$img="danger32.png";
		$text="{inadyn_stopped}";
	}else{$img="ok32.png";
	$text="<table style='width:100%'>";
		while (list ($num, $ligne) = each ($pid)){
			$text=$text . "<tr><td>{running} PID $num</td></tr>";
			
		}
	$text=$text . "</table>";
	}
	$text=$tpl->_ENGINE_parse_body($text);
	$status=Paragraphe("$img","inadyn status",$text,"$page",null);
	$status=RoundedLightGreen($status);
	
	$status=$tpl->_ENGINE_parse_body($status,$page);
	echo $tpl->_ENGINE_parse_body(iframe($status,10,'230px'));
	
}
	

