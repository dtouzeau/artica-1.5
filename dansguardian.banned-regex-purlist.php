<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	
	if(!IsDansGuardianrights()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["bannedregexpurllist_form"])){main_rule_bannedregexpurllist_form();exit;}
	if(isset($_GET["dansguardian_bannedregexpurllist_closeform"])){main_rule_dansguardian_bannedregexpurllist_closeform();exit;}
	if(isset($_GET["bannedregexpurllist_edit"])){main_rule_dansguardian_bannedregexpurllist_edit();exit;}
	if(isset($_GET["bannedregexpurllist_switch"])){main_rule_bannedregexpurllist_switch();exit;}
	if(isset($_GET["bannedregexpurllist_icon"])){main_rule_bannedregexpurllist_icon();exit;}
	if(isset($_GET["bannedregexpurllist_delete"])){main_rule_bannedregexpurllist_delete();exit;}	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{bannedregexpurllist}");
	$html="
	
	function DANSGUARDIAN_LOAD_CATBANNEDREGEX(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
	var x_bannedregexpurllist_switch=function(obj){
	      DANSGUARDIAN_LOAD_CATBANNEDREGEX();
	}	
	
function bannedregexpurllist_edit(hostname,rule_main,num,id){
      var XHR = new XHRConnection();
      var info=document.getElementById('info_' + id).value;
      var pattern=document.getElementById('pattern_' + id).value;
      XHR.appendData('hostname',hostname);
      XHR.appendData('bannedregexpurllist_edit',num);
      XHR.appendData('rule_main',rule_main);
      XHR.appendData('info',info);
      XHR.appendData('pattern',pattern);               
      XHR.sendAndLoad('$page', 'GET');
      dansguardian_bannedregexpurllist_closeform(hostname,rule_main,num,id);
      YahooWin4Hide();
      }
  
function bannedregexpurllist_switch(hostname,rule_main,id,index,value){
      var XHR = new XHRConnection();
      XHR.appendData('bannedregexpurllist_switch',index);
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      XHR.appendData('enabled',value);
      XHR.sendAndLoad('$page', 'GET',x_bannedregexpurllist_switch);
      }
      
function bannedregexpurllist_delete(hostname,rule_main,index,id){
      document.getElementById( id).innerHTML='';
      document.getElementById('image_' + id).innerHTML='';
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('bannedregexpurllist_delete',index);
      XHR.appendData('rule_main',rule_main);
      XHR.sendAndLoad('$page', 'GET');      
      }	
      
      
function bannedregexpurllist_form(hostname,rule_main,id,index){
    YahooWin4(600,'$page?rule_main=' + rule_main + '&bannedregexpurllist_form=' + index+'&hostname='+ hostname + '&id=' + id);
    }
    
function dansguardian_bannedregexpurllist_closeform(hostname,rule_main,num,id){
   LoadAjax(id,'dansguardian.index.php?rule_main=' + rule_main + '&dansguardian_bannedregexpurllist_closeform=' + num+'&hostname='+ hostname + '&id=' + id);   
      }      
	
	DANSGUARDIAN_LOAD_CATBANNEDREGEX()";
	
	echo $html;
	
}
function strip_rulename($rulename){
	if(preg_match('#(.+?);(.+)#',$rulename,$re)){
		return $re[1];
		
	}else{
		return $rulename;
	}
	
}

function popup($noecho=0){

	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=strip_rulename($dansg->Master_rules_index[$rule_main]);

$html="
	
	<p class=caption>{bannedregexpurllist_explain}</p>
	<div id='main_rules_bannedregexpurllist_list'>".main_rules_bannedregexpurllist_list($rule_main,1)."</div>";	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");		
	}

function main_rules_bannedregexpurllist_list($rule_main,$noecho=0){
	
$dans=new dansguardian_rules($_GET["hostname"],$rule_main);	
	$sql="SELECT * FROM dansguardian_files WHERE filename='bannedregexpurllist' AND RuleID=$rule_main ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$style=CellRollOver();
	$categ="
	<div style='width:100%;height:300px;overflow:auto'>
	<table style='width:99%' class=table_form>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$rule_id=$ligne["ID"];	
	$num=$ligne["ID"];
	if($ligne["enabled"]==1){
			$onoff=imgtootltip("icon_ok.gif","{disable}","bannedregexpurllist_switch('$hostname','$rule_main','$rule_id','$num','0')",null,"img_{$rule_id}");
		}else{$onoff=imgtootltip("icon_err.gif","{enable}","bannedregexpurllist_switch('$hostname','$rule_main','$rule_id','$num','1')",null,"img_{$rule_id}");}
		
	$js="bannedregexpurllist_form('$hostname','$rule_main','$rule_id','$num')";
	$text=texttooltip($ligne["infos"],$ligne["pattern"],$js);	
	$categ=$categ . 
		"<tr $style>
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td width=1% valign='top'><strong>$num</strong></td>
		<td width=1% valign='top'><span id='image_{$rule_id}'>$onoff</span></td>
		<td  valign='top'><strong id='$rule_id'>$text</td>
		<td width=1% valign='top'>" . imgtootltip('ed_delete.gif','{delete}',"bannedregexpurllist_delete('$hostname','$rule_main','$num','$rule_id')") ."</td>
		</tr>
		";
		}
	
		
	$categ=$categ . "</table></div>";
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$categ");}
	echo $tpl->_ENGINE_parse_body("$categ");		
	
}
function main_rule_bannedregexpurllist_form(){
$users=new usersMenus();
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$id=$_GET["id"];
	
	$sql="SELECT * FROM dansguardian_files WHERE ID=$id";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$form="
	<table style='width:100%'>
	<td align='right'>{description}:</strong style='font-size:12px'><td>" . Field_text("info_{$id}",$ligne["infos"]) . "</td>
	</tr>
	<tr>
	<td colspan=2><textarea id='pattern_{$id}' rows=5 style='width:100%;font-size:12px'>{$ligne["pattern"]}</textarea></td>
	</tr>
	<tr>
	<td colspan=2 align='right'>".button("{edit}","bannedregexpurllist_edit('$hostname','$rule_main','{$_GET["bannedregexpurllist_form"]}','$id');")
		."
	</td>
	</tr>	
	</table>
	</br>
	";
	
	$form="<div style='margin:4px;padding:4px'>$form</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($form);
	}
	
	function main_rule_dansguardian_bannedregexpurllist_closeform(){
	$rule_main=$_GET["rule_main"];
	
	$q=new mysql();
	$sql="SELECT * FROM dansguardian_files WHERE ID={$_GET["id"]} LIMIT 0,1";
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	echo "<span " . 
			CellRollOver("bannedregexpurllist_form('$hostname','$rule_main','{$_GET["id"]}','{$_GET["dansguardian_bannedregexpurllist_closeform"]}')",'{edit} - ' . $_GET["dansguardian_bannedregexpurllist_closeform"]).">{$ligne["infos"]}</span></a></strong>";
	
}

function main_rule_dansguardian_bannedregexpurllist_edit(){
	$rule_main=$_GET["rule_main"];	
	$index=$_GET["bannedregexpurllist_edit"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dans->Edit_bannedregexpurllist($index,$_GET["pattern"],$_GET["info"],$datas["enabled"]);
	}
	
function main_rule_bannedregexpurllist_icon(){
	$rule_main=$_GET["rule_main"];	
	$rule_id=$_GET["id"];
	$num=$rule_id;
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$q=new mysql();
	$sql="SELECT enabled FROM dansguardian_files WHERE ID=$rule_id LIMIT 0,1";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	

	if($ligne["enabled"]==1){
			$onoff=imgtootltip("icon_ok.gif","{disable}","bannedregexpurllist_switch('$hostname','$rule_main','$rule_id','$num','0')",null,"img_{$rule_id}");
			
		}else{
			$onoff=imgtootltip("icon_err.gif","{enable}","bannedregexpurllist_switch('$hostname','$rule_main','$rule_id','$num','1')",null,"img_{$rule_id}");
		
		}
			
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($onoff);
}	


function main_rule_bannedregexpurllist_delete(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$rule_main=$_GET["rule_main"];	
	$index=$_GET["bannedregexpurllist_delete"];	
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dans->DelBannedregexpurllist($rule_main,$index);
	
}
	
function main_rule_bannedregexpurllist_switch(){
	$rule_main=$_GET["rule_main"];	
	$index=$_GET["bannedregexpurllist_switch"];	
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dans->Edit_bannedregexpurllist_enabled($index,$_GET["enabled"]);	
	
}


	
	
?>