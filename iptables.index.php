<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.iptables.inc');
include_once('ressources/class.ntpd.inc');	
	$user=new usersMenus();
	if($user->AsArticaAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["ntpdAdd"])){ntpdAdd();exit;}
	if(isset($_GET["iptablesmove"])){iptablesmove();exit;}
	if(isset($_GET["ntpdserverdelete"])){ntpdserverdelete();exit;}
	if(isset($_GET["op"])){main_switch_op();exit;}
	if(isset($_GET["enable_iptables"])){enable_iptables();exit;}
	if(isset($_GET["editrule"])){main_frm();exit;}
	if(isset($_GET["rule_id_save"])){rule_id_save();exit;}
	if(isset($_GET["iptablesdel"])){iptablesdel();exit;}
	
	main_page();
	
function main_page(){
	

	
	$html=
	"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
	}
</script>	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_firewall.jpg' style='margin-right:60px'><p class=caption>{about}</p></td>
	<td valign='top'><div id='services_status'>". main_status() . "</div><br>
		
		<div id='enable_section'>". main_enable() . "</div>
	
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();LoadAjax('main_config','$page?main=yes');</script>
	
	";
	
	$cfg["JS"][]='js/iptables.js';
	$tpl=new template_users('{APP_IPTABLES}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{main_settings}';
	$array["logs"]='{events}';	
	$array["iptablesconf"]='{iptablesconf}';
	$array["iptablescurrules"]='{iptablescurrules}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function main_nic_tabs(){
	$ip=new iptables();
	$page=CurrentPageName();
	if($_GET["nic"]==null){$_GET["nic"]=$ip->nics[0];}
	if(!is_array($ip->nics_table)){
		while (list ($num, $ligne) = each ($ip->nics_table) ){
			if($_GET["nic"]==$num){$class="id=tab_current";}else{$class=null;}
			$html=$html . "<li><a href=\"javascript:LoadAjax('serverlist','$page?main=nic&nic=$ligne')\" $class>$ligne</a></li>\n";
		}
	}
	return "<div id=tablist>$html</div>";		
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "syncevents":main_sync();exit;break;
		case "conf":echo main_conf();exit;break;
		case "iptablesconf":echo main_iptablesconf();exit;break;
		case "enablesec":echo main_enable();exit;break;
		case "rulestable":echo main_rules_list();exit;break;
		case "iptablescurrules":echo main_iptables_current_rules();exit;
		case "nic":echo main_rules_list();exit;break;
		default:
			break;
	}
	
	
}	

function main_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('iptables_status',$_GET["hostname"]));	
	if($ini->_params["IPTABLES"]["application_enabled"]==0){
		$img="ok32-grey.png";
		$status="{disabled}";
	}else{
		$img="ok32.png";
		$status="running";
		
	}
	
	
	
	
	$status="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_IPTABLES}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>kernel</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap><strong>in kernel</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["IPTABLES"]["master_version"]}</strong></td>
		</tr>					
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status=RoundedLightGreen($status);
	$status_serv=RoundedLightGrey(Paragraphe($rouage ,$rouage_title. " (squid)",$rouage_text,"javascript:$js"));
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);
	
}


function main_config(){
$iptables=new iptables();
	 $page=CurrentPageName();
	 $entete=main_tabs()."<br>
	 <H5>{main_settings}</H5>
	 <br>
	 ";
	 
	
	 
	 $form="
	 <table style='width:100%'>
	 <tr>
	 <td valign='top'>
	 	<div id=serverlist style='padding:4px'>" .main_rules_list() . "</div>
	 	
	 	<br>
	 	
	 
	 </td>
	 <td valign='top'>
	 	" . RoundedLightGreen(Paragraphe('system-64.png','{compile}','{compile_text}',"javascript:Compile();",'apply'))."<br>
	 	" . RoundedLightBlue(Paragraphe('add-64-on-right.png','{add_inboundrule}','{add_inboundrule_text}',"javascript:iptables_edit(-1);",'add_inboundrule'))."<br>
	 	
	 	
	 </td>
	 </tr>
	 </table>
	 
	 
	 
	 ";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$entete$form");
	
}

function main_rules_list(){
	$ip=new iptables();
	$nic=$_GET["nic"];
	if($_GET["nic"]==null){$nic=trim($ip->nics[0]);}

	if(!is_array($ip->rules[$nic])){
			$html=main_nic_tabs()."<br>
			<H5>$nic</H5>";
			$tpl=new templates();
			return $tpl->_ENGINE_parse_body(RoundedLightGrey($html));
	}
	
	
	
	$html=main_nic_tabs()."<br>
	<H5>$nic</H5>
	<table style='width:400px'>
	<tr style='background-color:#CCCCCC'>
	<td>&nbsp;</td>
	<td  width=1% nowrap><strong>{local_port}</strong></td>
	<td  width=1%><strong>&nbsp;</strong></td>
	<td  align='center'><strong>{iptables_from}</strong></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	</tr>
	";
	
	$main_table=$ip->rules[$nic];
	
	
	while (list ($num, $val) = each ($main_table) ){
		
			if($val["FROM"]==null){
				$val["FROM"]='*';
			}
		
			if($val["REGLE"]=="GLOBAL"){
				$val["CIBLE"]='INPUT';
				$val["LOCAL_PORT"]='*';
			}
			if($val["LOCAL_PORT"]==null){$val["LOCAL_PORT"]='*';}
		
				if($val["ACTION"]=="ACCEPT"){
						if($val["CIBLE"]=='INPUT'){
							$flow_img="fleche-20-black-left.png";
						}else{
							$flow_img="fleche-20-black-right.png";
						}
				}else{
					if($val["CIBLE"]=='INPUT'){
							$flow_img="fleche-20-red-left.png";
						}else{
							$flow_img="fleche-20-red-right.png";
						}
				}
				
		if($val["ENABLED"]==0){$style="style='color:#565657;text-decoration:line-through;'";}else{$style=null;;}
		$link=CellRollOver("iptables_edit('$nic',$num)");			
		$dir=imgtootltip($flow_img,"{iptables_{$val["CIBLE"]}}->{$val["ACTION"]}");
		$html=$html . "<tr " . CellRollOver().">
		<td width=1% $link><img src='img/fw_bold.gif'></td>
		<td  width=1% align='right' $link $style><strong><strong>{$val["LOCAL_PORT"]}</strong></strong></td>
		<td nowrap  width=1% $link><strong $style>$dir</strong></td>
		<td nowrap  align='center' $link $style><strong>&nbsp;{$val["FROM"]}&nbsp;</strong></td>
		<td width=1% valign='top'>" . imgtootltip('arrow_down.gif','{down}',"iptablesmove('$nic',$num','down')")."</TD>
		<td width=1% valign='top'>" . imgtootltip('arrow_up.gif','{up}',"iptablesmove('$nic','$num','up')")."</TD>
		<td width=1% valign='top' >" . imgtootltip('x.gif','{delete}',"iptablesdel('$nic','$num')")."</TD>		
		</tr>
		";
		
	}
	
$html=$html . "<tr " . CellRollOver().">
		<td width=1% $link><img src='img/fw_bold.gif'></td>
		<td  width=1% align='right'><strong><strong>*</strong></strong></td>
		<td nowrap  width=1% $link><strong>".imgtootltip("fleche-20-red-left.png","{iptables_INPUT}->{DROP}")."</strong></td>
		<td nowrap  align='center' $link><strong>*</strong></td>
		<td width=1% valign='top'>&nbsp;</TD>
		<td width=1% valign='top'>&nbsp;</TD>
		<td width=1% valign='top' >&nbsp;</TD>		
		</tr>
		";	
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html));
	
}

function main_iptablesconf(){
	$ip=new iptables();
	$conf=$ip->compile_rules();
	
	$conf=htmlspecialchars($conf);
	$conf=nl2br($conf);
	
	$entete=main_tabs()."<br>
	 <H5>{iptablesconf}</H5>
	 <br>
	 <div style='padding:5px;margin:10px;border:1px dotted #CCCCCC'>
	 <code>$conf</code>
	 </div>
	 ";	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$entete");	
}

function main_iptables_current_rules(){
	$sock=new sockets();
	$conf=$sock->getfile('iptables_cururles');
	
	
	
	$conf=htmlspecialchars($conf);
	$conf=nl2br($conf);
	$conf=str_replace(' ','&nbsp;',$conf);
	
	$entete=main_tabs()."<br>
	 <H5>{iptablescurrules}</H5>
	 <br>
	 <div style='padding:5px;margin:0px;border:1px dotted #CCCCCC;position:absolute;width:650px'>
	 <code>$conf</code>
	 </div>
	 ";	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$entete");	
}


function main_enable(){
	
	$ip=new iptables();
	
	$html="
	<table style='width:100%'>
	<tr>
	<td nowrap align='right'><strong>{enable_iptables}</strong></td>
	<td width=1%>" . Field_numeric_checkbox_img('enable_iptables',$ip->IptablesEnabled,'{enable_disable}')."</td>
	<td width=1%><input type='button' OnClick=\"javascript:EnableIpTable();\" value='Go&nbsp;&raquo;'></td>
	</tr>
	
	
	</table>
	
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html));
	
	
	
}



function main_switch_op_save(){
	$ntp=new iptables();
	$ntp->SaveToLdap();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<strong>{compile_rules_ok}</strong>");
}

function main_switch_op_server(){
	$ntp=new iptables();
	$ntp->SaveToServer();
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<strong>{save_toserver_ok}</strong>");	
	
}

function main_switch_op_end(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<p class=caption>{close_windows}</p>");		
	
}

function main_switch_op(){
	
	switch ($_GET["op"]) {
		case 0:main_switch_op_save();exit;break;
		case 1:main_switch_op_server();exit;break;
		case 2:main_switch_op_end();exit;break;
		default:
			break;
	}
	
	
	$html="
	<H5>{compile}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/system-64.png'></td>
	<td valign='top'>
		<div id='message_0' style='margin:3px'></div>
		<div id='message_1' style='margin:3px'></div>
		<div id='message_2' style='margin:3px'></div>
	
	</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}



function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html=main_tabs() . "
	<H5>{events}</H5>
	<iframe src='iptables.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}

function enable_iptables(){
	$ip=new iptables();
	$ip->IptablesEnabled=$_GET["enable_iptables"];
	$ip->SaveToLdap();
	}
	
function main_frm(){
	$rule=$_GET["editrule"];
	$rulet=$rule;
	if($rulet==-1){$rulet="({add})";}
	$ip=new iptables();
	
	
	$array_action=array(
		"ACCEPT"=>"{ACCEPT}",	
		"REJECT"=>"{REJECT}",
		"DROP"=>"{DROP}",
		);
		
	$proto_array=array("tcp"=>"tcp","udp"=>"udp","all"=>'{all}','icmp'=>'icmp','rdp'=>'rdp');
	
	
	
	$array_rule=$ip->rules[$_GET["nic"]][$rule];
	$nic=Field_array_Hash($ip->nics_table,'CARTE',$array_rule["CARTE"]);
	if($array_rule["FROM"]==null){$array_rule["FROM"]='*';}
	if($array_rule["CARTE"]==null){$array_rule["CARTE"]='eth0';}
	if($array_rule["PROTO"]==null){$array_rule["PROTO"]='all';}
	if($array_rule["ENABLED"]==null){$array_rule["ENABLED"]=1;}
	$proto=Field_array_Hash($proto_array,'PROTO',$array_rule["PROTO"]);
	$page=CurrentPageName();
	
	$html="
	<H5>{rule_number} $rulet</H5>
	<form  name='FFM1'>
	<input type='hidden' name='rule_id_save' id='rule_id_save' value='$rule'>
	<table style='width:100%'>
	<tr>
		<td align='right'><strong>{enabled}:</td>
		<td>" . Field_numeric_checkbox_img('ENABLED',$array_rule["ENABLED"],'{enable_disable}')."</td>
	</tr	
	<tr>
		<td align='right'><strong>{iptables_nic}:</td>
		<td>$nic</td>
	</tr>
	<tr>
		<td align='right'><strong>{iptables_from}:</td>
		<td>" .Field_text('FROM',$array_rule["FROM"],'width:190px')."</td>
	</tr>
	<tr>
		<td align='right'><strong>{iptables_frommac}:</td>
		<td>" .Field_text('MAC',$array_rule["MAC"],'width:190px')."</td>
	</tr>	
	<tr>
		<td align='right'><strong>{local_port}:</td>
		<td>" .Field_text('LOCAL_PORT',$array_rule["LOCAL_PORT"],'width:90px')."</td>
	</tr>	
	<tr>
		<td align='right'><strong>{proto}:</td>
		<td>$proto</td>
	</tr>		
	<tr>
		<td align='right'><strong>{iptables_results}:</td>
		<td>".Field_array_Hash($array_action,'ACTION',$array_rule["ACTION"],null,null,0,"width:190px")."</td>
	</tr>
	<tr>
		<td align='right'><strong>{log_this_event}:</td>
		<td>" . Field_numeric_checkbox_img('LOG',$array_rule["LOG"],'{log_this_event}')."</td>
	</tr>		
	<tr>
	<td align='right' colspan=2><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseYahooForm('FFM1','$page',true);RefreshTable('{$array_rule["CARTE"]}')\">
	
	</table>
	</FORM>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function rule_id_save(){
	$ip=new iptables();
	if($_GET["rule_id_save"]=="undefined"){$_GET["rule_id_save"]=-1;}
	if($_GET["rule_id_save"]>-1){
		$rule=$ip->rules[$_GET["CARTE"]][$_GET["rule_id_save"]];
	}
	
	while (list ($num, $val) = each ($_GET) ){
		if(trim($num)<>null){
			$rule[$num]=$val;
		}
	}
	
	if($rule["MAC"]<>null){
		if (!preg_match( '/([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/i', $rule["MAC"])){
			echo "Bad MAC address\n";
			return null;
		}
	}
	
	
	$rule["CIBLE"]="INPUT";
	if($_GET["rule_id_save"]==-1){
			writelogs("adding new rule...",__FUNCTION__,__FILE__);
			$ip->rules[$_GET["CARTE"]][]=$rule;
	}else{
		writelogs("Edit rule...{$_GET["rule_id_save"]}",__FUNCTION__,__FILE__);
		$ip->rules[$_GET["CARTE"]][$_GET["rule_id_save"]]=$rule;
	}
	$ip->SaveToLdap();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}
function iptablesmove(){
	$ip=new iptables();
	$rules=$ip->rules[$_GET["nic"]];
	$ip->rules[$_GET["nic"]]=array_move_element($rules,$rules[$_GET["iptablesmove"]],$_GET["direction"]);
	$ip->SaveToLdap();
}

function iptablesdel(){
	$ip=new iptables();
	writelogs("delete ip->rules[{$_GET["nic"]}][{$_GET["iptablesdel"]}");
	unset($ip->rules[$_GET["nic"]][$_GET["iptablesdel"]]);
	$ip->SaveToLdap();
	}


?>
