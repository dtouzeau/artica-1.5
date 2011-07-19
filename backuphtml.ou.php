<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.backup.emails.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	$user=new usersMenus();
	if($user->AllowEditOuSecurity==false){header('location:users.index.php');exit();}
	if(!isset($_GET["ou"])){header('location:domains.index.php');exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["service_imapssl_enabed"])){ Save_services();exit;}
	if(isset($_GET["addrule"])){popup_add_rule();exit;}
	if(isset($_GET["AddNewRule"])){add_rule();exit;}
	if(isset($_GET["ArticaEnableBackup"])){Save_enable_backup();exit;}
	if(isset($_GET["ArticaBackupMove"])){Save_move_rule();exit();}
	if(isset($_GET["ArticaBackupDeleteRule"])){delete_rule();exit;}
	
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
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
	}
</script>	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_storage.png' style='margin-right:80px'></td>
	<td valign='top'>
		<div id='services_status'>". main_status() . "</div>
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<input type='hidden' value='{action_delete_rule}' id='action_delete_rule'>
	<script>demarre();LoadAjax('main_config','$page?main=yes&ou={$_GET["ou"]}');</script>
	
	";
	
	$cfg["JS"][]="js/backup.ou.js";
	$tpl=new template_users('{backup_rules}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="rules";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["rules"]='{rules}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname&ou={$_GET["ou"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "rules":main_config();exit;break;
		case "ruleslist":echo main_rules_list();exit;break;
		case "yes":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "conf":echo main_conf();exit;break;
		case "cyrquota":echo main_cyrquota();exit;break;
		case "cyrusconf":echo main_cyrusconf();exit;break;
		default:main_config();break;
	}
	
	
}	

function main_status(){

	$bck=new backup_email($_GET["ou"]);
	if($bck->BackupEnabled==0){
		$img="danger32.png";
		$title="{backup_disabled}";
		$text="{backup_disabled_text}";
		
	}else{
		$img="ok32.png";
		$title="{backup_enabled}";
		$text="{backup_enabled_text}";
	}
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body(RoundedLightGreen(Paragraphe($img,$title,$text,"javascript:ArticaEnableBackup($bck->BackupEnabled,\"{$_GET["ou"]}\")",'enable_disable')));
	
}
function main_conf(){
	$cyr=new cyrus_conf();
	$sock=new sockets();
	$datas=$sock->getfile('cyrus_imapconf') . "\n\n#artica conf\n$cyr->globalconf";
	$datas=htmlspecialchars($datas);
	$datas=nl2br($datas);
	$datas=str_replace("\n","",$datas);
	$datas=str_replace("<br /><br />","<br />",$datas);
	$html=main_tabs()."
	<br><H5>{config}</H5>
	<div style='padding:10px;margin:10px;border 1px dotted #CCCCCC'>
	<code>$datas</code>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function main_config(){
	
	$back=new backup_email($_GET["ou"]) ;
	
	
	$html=main_tabs()."<H5>{rules}</H5>
	<table style='width:100%'>
	<tr>
	<td valign='top'><div id='rules' style='width:98%;margin:4px;padding:3px;'>" . main_rules_list()."</div></td>
	
	<td valign='top' width=210px>" . RoundedLightGrey(Paragraphe('folder-64-add-backup.png','{add_rule}','{add_rule_text}',"javascript:artica_backup_rules_add(\"{$_GET["ou"]}\");",'add'))."</td>
	</tr>
	</table>
	";
	
	
	
	
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}







function main_rules_list(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$bck=new backup_email($_GET["ou"]);
	

	
	
	$html=$html ."
	
	<table style='width:100%;border:1px solid #CCCCCC'>
	<tr style='background-color:#CCCCCC;font-size:14px;font-weight:bold'>
	<th>&nbsp;</th>
	<th nowrap>{from_match}</th>
	<th nowrap>{to_match}</th>
	<th nowrap>{subject_match}</th>
	<th colspan=3>&nbsp;</th>
	</tr>
	
	";
	
	
	
	while (list ($num, $ligne) = each ($bck->ArticaBackupRules) ){
	if(preg_match('#<f>(.*)</f><t>(.*)</t><s>(.*)</s>#',$ligne,$re)){
		
				$styleRoll="
				style='border:1px solid white;border-bottom:1px dotted #CCCCCC'
				OnMouseOver=\"this.style.cursor='pointer'\"
				OnMouseOut=\"this.style.cursor='auto'\"
				OnClick=\"javascript:artica_backup_rules_add('{$_GET["ou"]}',$num);\"";		
		
		
		$re[3]=substr($re[3],0,50)."...";
		$html=$html . "
		<tr " . CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td $styleRoll width=1%>{$re[1]}</td>
		<td width=1% align='right' $styleRoll>{$re[2]}</td>
		<td $styleRoll align='center'>{$re[3]}&nbsp;</td>
		<td width=1% valign='top'>" . imgtootltip('arrow_down.gif','{down}',"ArticaBackupMove('{$_GET["ou"]}','$num','down')")."</TD>
		<td width=1% valign='top'>" . imgtootltip('arrow_up.gif','{up}',"ArticaBackupMove('{$_GET["ou"]}','$num','up')")."</TD>		
		<td width=1% >" . imgtootltip('ed_delete.gif','{delete}',"ArticaBackupDeleteRule('{$_GET["ou"]}',$num)")."</td>
		</tr>
		";
		}
		
	}
	
	$html=$html ."</table>";
	return  $tpl->_ENGINE_parse_body($html);
	
	
}


function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html=main_tabs() . "
	<H5>{events}</H5>
	<iframe src='cyrus.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function popup_add_rule(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	if(!is_numeric($_GET["addrule"])){$_GET["addrule"]=-1;}
	
	if($_GET["addrule"]>-1){
		$bck=new backup_email($_GET["ou"]);
		
		preg_match('#<f>(.*)</f><t>(.*)</t><s>(.*)</s>#',$bck->ArticaBackupRules[$_GET["addrule"]],$tbl);
	}
	
	
	$html="
	<form name='FFM2'>
	<input type='hidden' name=ou id=ou value='{$_GET["ou"]}'>
	<input type='hidden' name=ruleid value='{$_GET["num"]}'>
	<input type='hidden' name='AddNewRule' value='yes'>	
	<H5>{add_rule}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% align='right' nowrap><strong>{from_match}</strong>:</td>
	<td>" . Field_text('from',$tbl[1],'width:60%',null,null,'{from_text}') . "</td>
	</tr>
	<tr>
	<td width=1% align='right' nowrap><strong>{to_match}</strong>:</td>
	<td>" . Field_text('to',$tbl[2],'width:60%',null,null,'{from_text}') . "</td>
	</tr>
	<tr>
	<td width=1% align='right' nowrap><strong>{subject_match}</strong>:</td>
	<td>" . Field_text('subject',$tbl[3],'width:60%',null,null,'{from_text}') . "&nbsp;</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SaveRule();\"></td>
	</tr>
	</table>
	</form>";
	
	$html=RoundedLightGrey($html);
	
	echo $tpl->_ENGINE_parse_body($html);
	
}





function add_rule(){
	$ou=$_GET["ou"];
	if($_GET["ruleid"]==null){$_GET["ruleid"]=-1;}
	$bck=new backup_email($ou);
	
	$line="<f>{$_GET["from"]}</f><t>{$_GET["to"]}</t><s>{$_GET["subject"]}</s>";
	
	
	if($_GET["ruleid"]==-1){
		$bck->add_rule($line);
		exit;
		
	}else{
		$bck->ArticaBackupRules[$_GET["ruleid"]]=$line;
		$bck->SaveToLdap();
		
	}
}

function Save_enable_backup(){
	$ou=$_GET["ou"];
	$bck=new backup_email($ou);
	$bck->BackupEnabled=$_GET["ArticaEnableBackup"];
	$bck->SaveToLdap();
}

function Save_move_rule(){
	$ou=$_GET["ou"];
	$bck=new backup_email($ou);
	$newarray=array_move_element($bck->ArticaBackupRules,$bck->ArticaBackupRules[$_GET["ArticaBackupMove"]],$_GET["move"]);
	$bck->ArticaBackupRules=$newarray;
	$bck->SaveToLdap();
}

function delete_rule(){
$ou=$_GET["ou"];
	$bck=new backup_email($ou);	
	unset($bck->ArticaBackupRules[$_GET["ArticaBackupDeleteRule"]]);
	$bck->SaveToLdap();
}

?>
