<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.fdm.inc');
	

if(isset($_GET["fdm_DeleteScript"])){Delete();exit;}
if(isset($_GET["fdm_rule"])){SaveRule();exit;}
if(isset($_GET["rulename"])){ShowRule();exit;}
if(isset($_GET["ScriptRulename"])){ShowScript();exit;}
if(isset($_GET["events"])){events();exit;}






ShowRule();

function ShowRule(){
	$uid=$_GET["uid"];
	$rulename=$_GET["rulename"];
	
	if($rulename==null){
		$rulename="{$uid}_".date('Ymdhis');
		
	}
	
	$fdm=new fdm($uid);
	
	$rulearray=$fdm->main_array[$rulename];
	$server_type=Field_array_Hash($fdm->type_array,'server_type',$rulearray["server_type"]);
	if($rulearray["folder"]==null){$rulearray["folder"]='INBOX';}
	
	$html="
	
	
	<h1>{fetchrule}</H1>
	<form name='FDMRULE'>
	<input type='hidden' name='fdm_rule' value='$rulename'>
	<input type='hidden' name='uid' value='$uid'>
	<table style='width:100%'>
	
	
	<tr>
		<td align='right' nowrap width=1% class=legend>{rule}:</strong></td>
		<td align='left'>$rulename</td>
	</tr>	
	<tr>
		<td align='right' nowrap width=1% class=legend>{server_type}:</strong></td>
		<td align='left'>$server_type</td>
	</tr>
	<tr>
		<td align='right' nowrap width=1% class=legend>{server_name}:</strong></td>
		<td align='left'>". Field_text('server_name',$rulearray["server_name"],'width:120px')."</td>
	</tr>		
	<tr>
		<td align='right' nowrap width=1% class=legend>{server_port}:</strong></td>
		<td align='left'>". Field_text('server_port',$rulearray["server_port"],'width:90px')."</td>
	</tr>	

	<tr>
		<td align='right' nowrap width=1% class=legend>{username}:</strong></td>
		<td align='left'>". Field_text('username',$rulearray["username"],'width:150px')."</td>
	</tr>	
	<tr>
		<td align='right' nowrap width=1% class=legend>{password}:</strong></td>
		<td align='left'>". Field_password('password',$rulearray["password"],'width:150px')."</td>
	</tr>	
	<tr>
		<td align='right' nowrap width=1% class=legend>{folder_to_fetch}:</strong></td>
		<td align='left'>". Field_text('folder',$rulearray["folder"],'width:150px')."</td>
	</tr>	
	<tr>
		<td align='right' nowrap width=1% class=legend>{keep}:</strong></td>
		<td align='left'>". Field_yesno_checkbox('keep',$rulearray["keep"])."</td>
	</tr>			
	<tr>
		<td align='right' nowrap width=1% class=legend>{no-apop}:</strong></td>
		<td align='left'>
		<table style='width:100%;margin:-2px;padding:0px;'>
		<tr>
			<td width=1% style='margin:0px;padding:0px;'>". Field_yesno_checkbox('no-apop',$rulearray["no-apop"])."</td>
			<td width=99% style='margin:0px;padding:0px;' align='left'>" . help_icon('{no-apop_text}')."</td>
			</tr>
		</table>
		</td>
	</tr>
		
	
	<tr>
	<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo' OnClick=\"javascript:fdm_editrule();\"></td>
	</tr>
	</table>
	</form>
		
	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


function SaveRule(){
$uid=$_GET["uid"];
	$rulename=$_GET["fdm_rule"];	
	$fdm=new fdm($uid);
	while (list ($num, $ligne) = each ($_GET) ){
		$fdm->main_array[$rulename][$num]=$ligne;
		
	}
	
	$fdm->SaveToLdap();
	
	
	
}

function ShowScript(){
$uid=$_GET["uid"];
$fdm=new fdm($uid);

$datas=$fdm->FDMConf;
$datas=htmlentities($datas);
$datas=nl2br($datas);
$html="
	
	
	<H5>{see_config}</H5>
	<div style='overflow:auto;width:100%;height:200px'><code style='font-size:10px'>$datas</code></div>";

		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function Delete(){
	$uid=$_GET["fdm_DeleteScript"];
	$fdm=new fdm($uid);
	unset($fdm->main_array[$_GET["rulename"]]);
	$fdm->SaveToLdap();	
}

function events(){
	$uid=$_GET["events"];
	$filelog=dirname(__FILE__)."/ressources/logs/fdm.$uid.log";
	$tbl=explode("\n",@file_get_contents($filelog));
	$tbl=array_reverse ($tbl, TRUE);		
	
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne<>null){
			$html=$html . "<div style='width:100%;padding:3px;border-bottom:1px solid #CCCCCC'><code style='font-size:10px;'>$ligne</code></div>";
		}
		
	}
	
	$html="
	<div style='width:100%;overflow:auto;height:350px'>$html</div>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
		
	
}

?>