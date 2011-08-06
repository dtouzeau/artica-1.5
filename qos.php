<?php
	session_start();if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.qos.inc');

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["index"])){index();exit;}
	
	if(isset($_GET["QOSInLeftMenu"])){QOSInLeftMenu_save();exit;}
	
	if(isset($_GET["service"])){master_service_popup();exit;}
	if(isset($_GET["master-service-add-js"])){master_service_add_js();exit;}
	if(isset($_GET["master-service-add-popup"])){master_service_add_popup();exit;}
	if(isset($_GET["master-service-add-add"])){master_service_add_mysql();exit;}
	if(isset($_GET["master-service-delete"])){master_service_del_mysql();exit;}
	if(isset($_GET["master-service-enable"])){master_service_enable_mysql();exit;}
	
	if(isset($_GET["class-add-index"])){class_popup();exit;}
	if(isset($_GET["class-add"])){class_popup_tabs();exit;}
	
	if(isset($_GET["class-save"])){class_save();exit;}
	if(isset($_GET["class-list"])){class_list();exit;}
	if(isset($_GET["class-delete"])){class_delete();exit;}
	
	if(isset($_GET["class-rules-index"])){class_rules_index();exit;}
	if(isset($_GET["class-rules-id"])){class_rules_popup();exit;}
	if(isset($_GET["class-rules-save"])){class_rules_save();exit;}
	if(isset($_GET["class-rules-list"])){class_rules_list();exit;}
	if(isset($_GET["class-rules-delete"])){class_rules_delete();exit;}
	
	if(isset($_GET["iptables-cmds"])){iptables_cmds();exit;}
	
	js();
	
function QOSInLeftMenu_save(){
	$sock=new sockets();
	$sock->SET_INFO("QOSInLeftMenu",$_GET["QOSInLeftMenu"]);
	
}
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	if(isset($_GET["in-front-ajax"])){
		echo "
		function QOS_START_POINT(){
			document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			$('#BodyContent').load('$page?popup=yes');
		}
		QOS_START_POINT();
		";
		return;
	}

	
	$title=$tpl->_ENGINE_parse_body("{Q.O.S}");
	echo "
	function QOS_START_POINT(){
		YahooWin2(760,'$page?popup=yes','$title');
	}
	QOS_START_POINT();
		";
	
}	

function master_service_add_js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body("{add_qos_service}");
	echo "YahooWin3(500,'$page?master-service-add-popup','$title');";
}

function master_service_add_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$net=new networking();
	$tcp=$net->Local_interfaces();
	unset($tcp["lo"]);
	
	$qos=new qos_net();
	$already=$qos->getSavedNics();
	while (list ($num, $ligne) = each ($already) ){unset($tcp[$num]);}
	
	
	$band=array(10240=>"10 Mb/s",102400=>"100 Mb/s",1024000=>"1 Gb/s");
	
	$html="
	<div id='qosdivadd'>
	<div class=explain>{add_qos_service_explain}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{qos_service_name}:</td>
		<td >". Field_text("qos_name","Bandwith Limitation (new)","font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend>{nic}:</td>
		<td >". Field_array_Hash($tcp,"qos_nic",null,null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{nic_bandwith}:</td>
		<td >". Field_array_Hash($band,"qos_band",102400,null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{or} {nic_bandwith}:</td>
		<td style='font-size:13px'>". Field_text("qos_band2","","font-size:13px;padding:3px;width:90px")."&nbsp;Kb/s</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","SaveQosServiceAdd()")."</td>
	</tr>
		
	</table>
	</div>
	<script>
	var x_SaveQosServiceAdd=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      QOS_START_POINT();
      YahooWin3Hide();
      }	
	
	function SaveQosServiceAdd(){
		var XHR = new XHRConnection();
		XHR.appendData('master-service-add-add',1);
		XHR.appendData('qos_name',document.getElementById('qos_name').value);
		XHR.appendData('qos_nic',document.getElementById('qos_nic').value);
		var qos_band=document.getElementById('qos_band2').value;
		if(qos_band.length>0){
			XHR.appendData('qos_band',qos_band);	
		}else{
			XHR.appendData('qos_nic',document.getElementById('qos_band').value);
		}
		
		document.getElementById('qosdivadd').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveQosServiceAdd);		
		}			
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function master_service_add_mysql(){
	$q=new mysql();
	$sql="INSERT INTO qos_eth (`NIC`,`bandwith`,`name`) 
	VALUES('{$_GET["qos_nic"]}','{$_GET["qos_band"]}','{$_GET["qos_name"]}');";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	}	
	
	
function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["index"]="{index}";
	
		$sql="SELECT ID,name FROM qos_eth";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,'artica_backup');
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$array[$ligne["ID"]]=$ligne["name"];
		}
	
	
	while (list ($num, $ligne) = each ($array) ){
		if(is_numeric($num)){
			$html[]="<li><a href=\"$page?service=$num\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}

	
	echo "
	<div id='qostabs' style='width:100%;height:560px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#qostabs').tabs({
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


function index(){
	$tpl=new templates();
	$page=CurrentPageName();
	$add=Paragraphe("qos_add-64.png","{add_qos_service}","{add_qos_service_text}","javascript:Loadjs('$page?master-service-add-js=yes')");
	
	$script=Paragraphe("script-view-64.png","{APP_DHCP_MAIN_CONF_TEXT}",
	"{display_generated_configuration_file}","javascript:YahooWin3('650','$page?iptables-cmds=yes','{APP_DHCP_MAIN_CONF_TEXT}')");
	
	
	$users=new usersMenus();
	$sock=new sockets();
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		$add<br>$script
		</td>
		<td width=100% valign='top'>
			<table style='width:100%'>
			<tr>
				<td class=legend>{add_to_left_menu}:</td>
				<td>". Field_checkbox("QOSInLeftMenu",1,$sock->GET_INFO("QOSInLeftMenu"),"QOSInLeftMenuCheck()")."</td>
			</tr>
			</table>
			
			<img src='img/qos-bg.png' style='margin-top:5px;margin-bottom:5px'>
			<div class=explain>{qos_artica_explain}
			
			<div style='text-align:right' class=legend><i>tc utility, iproute2 $users->tc_version</i></div>
			</div>
			
	</tr>
	</table>
	
	<script>
	var x_QOSInLeftMenuCheck=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      CacheOff();
      }	
	
	function QOSInLeftMenuCheck(){
		var XHR = new XHRConnection();
		XHR.appendData('class-save',1);
		if(document.getElementById('QOSInLeftMenu').checked){XHR.appendData('QOSInLeftMenu',1);}else{XHR.appendData('QOSInLeftMenu',0);}
		XHR.sendAndLoad('$page', 'GET',x_QOSInLeftMenuCheck);		
		}
	</script>		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function master_service_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$q=new qos_net($_GET["service"]);
	
	$qos_service_delete_confirm=$tpl->javascript_parse_text("{qos_service_delete_confirm}");
	$html="
	<div style='font-size:16px;text-align:right;width:100%;color:#B01212;border-bottom:1px solid #B01212;margin-bottom:5px;'>
		<strong style='font-style:italic'>". FormatBytes($q->master_service_bandwidth)."/s {for} $q->master_service_eth</strong>
	</div>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		". Paragraphe("qos-class-add-64.png","{new_bandwith}","{qos_new_bandwith_container}",
		"javascript:YahooWin3(650,'$page?class-add=yes&service_id={$_GET["service"]}','{new_bandwith}')")."<br>
		". Paragraphe("qos_del-64.png","{delete_service}","{delete}:$q->master_service_name",
		"javascript:DeleteQosMasterService({$_GET["service"]})")."<br>
		<table style='width:100%;border-top:1px solid #CCCCCC;border-bottom:1px solid #CCCCCC;margin:3px'>
			<tr>
				<td class=legend>{enable_qos_service}:</td>
				<td>". Field_checkbox("service_enabled",1,$q->master_service_enabled,"master_service_enable()")."</td>
			</tr>
		</table>
		
		
		</td>
		<td valign='top'>
			<div id='class-list-{$_GET["service"]}'></div>
		</td>
	</tr>
	</table>
	
	
	<script>
		function RefreshClassList(){
			LoadAjax('class-list-{$_GET["service"]}','$page?class-list=yes&service_id={$_GET["service"]}');
		
		}
		
	var x_DeleteQosMasterService=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      QOS_START_POINT();
      }	
	
	function DeleteQosMasterService(ID){
		var text='$qos_service_delete_confirm'
		if(confirm(text)){
			var XHR = new XHRConnection();
			XHR.appendData('master-service-delete',{$_GET["service"]});
			XHR.sendAndLoad('$page', 'GET',x_DeleteQosMasterService);		
		}
	}	
	
	var x_master_service_enable=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      RefreshTab('qostabs');
      }		

	function master_service_enable(){
		var XHR = new XHRConnection();
		XHR.appendData('master-service-id',{$_GET["service"]});
		if(document.getElementById('service_enabled').checked){
			XHR.appendData('master-service-enable',1);
			
		}else{
			XHR.appendData('master-service-enable',0);
		}
		
		XHR.sendAndLoad('$page', 'GET',x_master_service_enable);
	}
		
		
		RefreshClassList();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function class_popup_tabs(){
	
		if(trim($_GET["class_id"]==null)){$_GET["class_id"]='0';}
		$tpl=new templates();
		$page=CurrentPageName();
		$array["class-add-index"]="{bandwith}";
		if($_GET["class_id"]>0){
			$array["class-rules-index"]="{rules}";
		}
	
	
	while (list ($num, $ligne) = each ($array) ){
	
		$html[]=$tpl->_ENGINE_parse_body("<li>
		<a href=\"$page?$num=yes&class_id={$_GET["class_id"]}&service_id={$_GET["service_id"]}\"><span>$ligne</span></a></li>\n");
	}

	
	echo "
	<div id='qosclass{$_GET["class_id"]}' style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#qosclass{$_GET["class_id"]}').tabs({
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

function class_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$qos=new qos_net($_GET["service_id"]);
	if(trim($_GET["class_id"]==null)){$_GET["class_id"]='0';}
	$qos_class=new qos_class($_GET["class_id"]);
	if($qos_class->rate==null){$qos_class->rate=$qos->master_service_bandwidth;}
	if($qos_class->ceil==null){$qos_class->ceil=$qos->master_service_bandwidth;}
	if(trim($qos_class->prio)==null){$qos_class->prio=50;}
	$html="
	<div id='classdiv'>
	<div class=explain>{qos_class_explain}</div>
	<div style='font-size:16px;text-align:right'>{container}: ". FormatBytes($qos->master_service_bandwidth)."</div>
	<input type='hidden' id='prio' value='$qos_class->prio'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{enabled}:</td>
		<td colspan=2>". Field_checkbox("class_enabled",1,$qos_class->enabled)."</td>
	</tr>	
	<tr>
		<td class=legend>{qos_class_name}:</td>
		<td colspan=2>". Field_text("class_name",$qos_class->name,"font-size:13px;padding:3px")."</td>
	</tr>	
	<tr><td colspan=3>&nbsp;<br></td></tR>	
	<tr>
		<td class=legend>{bandwith}:</td>
		<td style='font-size:14px;'>". Field_text("rate",$qos_class->rate,"font-size:14px;padding:3px;width:90px",null,"RateCheck()",null,false)."&nbsp;Kb/s</td>
		<td><div id='class_slider_value' style='font-size:13px;width:190px'>&nbsp;</div></td>
		
	</tr>	
	<tr>
		<td class=legend>{qos_over}:</td>
		<td style='font-size:14px;'>". Field_text("ceil",$qos_class->ceil,"font-size:14px;padding:3px;width:90px",null,"CeilCheck()",null,false)."&nbsp;Kb/s</td>
		<td><div id='class_slider_over_value' style='font-size:13px;width:190px'>&nbsp;</div></td>
	</tr>
	<tr>
		<td class=legend>{priority}:</td>
		<td>". Field_text("prio2",(100-$qos_class->prio),"font-size:14px;padding:3px;width:90px",null,"SliderPriovalue()",null,false)."</td>
		<td><div id='class_slider_prio_value' style='font-size:16px;width:190px'>&nbsp;</div></td>
	</tr>				
	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveQosClass()")."</td>
	</tr>
		
	</table>
	</div>
	<script>
	var max=$qos->master_service_bandwidth;
	var x_SaveQosClass=function(obj){
      var tempvalue=obj.responseText;
      var class_id='{$_GET["class_id"]}';
	  if(tempvalue.length>3){alert(tempvalue);}
	  if(class_id==0){YahooWin3Hide();}
	  if(class_id>0){RefreshTab('qosclass'+class_id);}
      RefreshTab('qostabs');
      }	
	
	function SaveQosClass(){
		var XHR = new XHRConnection();
		XHR.appendData('class-save',1);
		XHR.appendData('service_id','{$_GET["service_id"]}');
		XHR.appendData('class_id','{$_GET["class_id"]}');
		XHR.appendData('prio',document.getElementById('prio').value);
		XHR.appendData('rate',document.getElementById('rate').value);
		XHR.appendData('ceil',document.getElementById('ceil').value);
		XHR.appendData('name',document.getElementById('class_name').value);
		
		if(document.getElementById('class_enabled').checked){
			XHR.appendData('enabled',1);
		}else{
			XHR.appendData('enabled',0);
		}
		
		document.getElementById('classdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveQosClass);		
		}

		
	function StartSlider(){
		var class_id='{$_GET["class_id"]}';
		if(class_id.length==0){class_id=0;}
		
		if(class_id==0){
			document.getElementById('class_enabled').checked=true;
			document.getElementById('class_enabled').disabled=true;
		}
	}
	
	function SliderOvervalue(value){
		var rate=document.getElementById('rate').value;
		if(value>rate){
			value=rate;
			document.getElementById('ceil').value=value;
			}
		var kbs=Math.round(value/1024);
		var r=(value/$qos->master_service_bandwidth)*100;
		r=roundNumber(r,2);
		var text=value+'Kb/s ';
		if(kbs>0){text=text+kbs+'Mb/s ';}
		text=text+r+'%';		
		document.getElementById('class_slider_over_value').innerHTML=text;
		
	}	
	
	function CeilCheck(){
		SliderOvervalue(document.getElementById('ceil').value);
	
	}
	
	function RateCheck(){
		Slidervalue(document.getElementById('rate').value);
	}
	
	function SliderPriovalue(){
		
		var value=document.getElementById('prio2').value;
		if(value>99){value=99;}
		var low='{low}';
		var medium='{medium}';
		var high='{high}';
		document.getElementById('prio').value=100-value;
		if(value<33){document.getElementById('class_slider_prio_value').innerHTML=low;return;}
		if(value<66){document.getElementById('class_slider_prio_value').innerHTML=medium;return;}
		if(value<100){document.getElementById('class_slider_prio_value').innerHTML=high;return;}
		
	}
	
	function Slidervalue(value){
		if(value>$qos->master_service_bandwidth){
			value=$qos->master_service_bandwidth;
			document.getElementById('rate').value=$qos->master_service_bandwidth;
		}
		var ceil=document.getElementById('ceil').value;
		if(ceil<value){
			document.getElementById('ceil').value=value;
		}
	
		var kbs=Math.round(value/1024);
		var r=(value/$qos->master_service_bandwidth)*100;
		r=roundNumber(r,2);
		var text=value+'Kb/s ';
		if(kbs>0){text=text+kbs+'Mb/s ';}
		text=text+r+'%';
		document.getElementById('class_slider_value').innerHTML=text;
		
	}
	
	function roundNumber(num, dec) {
		var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
		return result;
	}	
	
	StartSlider();
	SliderPriovalue();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function class_save(){
	$_GET["name"]=replace_accents($_GET["name"]);
	$sql="INSERT INTO qos_class (`name`,`service_id`,`prio`,`rate`,`ceil`,`enabled`) 
	VALUES('{$_GET["name"]}','{$_GET["service_id"]}','{$_GET["prio"]}','{$_GET["rate"]}','{$_GET["ceil"]}',1)";
	
	if($_GET["class_id"]>0){
		$sql="UPDATE qos_class SET 
		`name`='{$_GET["name"]}',
		`prio`='{$_GET["prio"]}',
		`rate`='{$_GET["rate"]}',
		`enabled`='{$_GET["enabled"]}',
		`ceil`='{$_GET["ceil"]}' WHERE ID='{$_GET["class_id"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	if(!$q->ok){echo $q->mysql_error."\n".$sql;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?qos-compile=yes");
	
}

function class_list(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$qos_class_delete_confirm=$tpl->javascript_parse_text("{qos_class_delete_confirm}");	
	$qos_master=new qos_net($_GET["service_id"]);
	
	
	$sql="SELECT `ID`,`name`,`rate`,`ceil`,`enabled` FROM qos_class WHERE service_id={$_GET["service_id"]} ORDER BY `prio`";
	$q=new mysql();
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=4>{containers}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
		$pointer="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
		$results=$q->QUERY_SQL($sql,'artica_backup');
		if(!$q->ok){echo "<H3>$q->mysql_error</H3>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			
			$rate=FormatBytes($ligne["rate"]);
			$ceil=FormatBytes($ligne["ceil"]);
			$delete=imgtootltip("delete-24.png","{delete}","QOsClassDelete('{$ligne["ID"]}')");
			$edit="YahooWin3(650,'$page?class-add=yes&service_id={$_GET["service_id"]}&class_id={$ligne["ID"]}','{$ligne["name"]}')";
			$color="black";
			if($qos_master->master_service_enabled==0){
				$color="#CCCCCC";
			}
			
			$html=$html."
			<tr class=$classtr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><strong style='font-size:14px;color:$color;text-decoration:underline' $pointer OnClick=\"javascript:$edit\">{$ligne["name"]}</td>
				<td align=center><strong style='font-size:14px;color:$color;text-decoration:underline' $pointer OnClick=\"javascript:$edit\">$rate/s - $ceil/s</td>
				<td width=1%>$delete</td>
			</tr>
			";
			
		}
	$html=$html."</tbody></table>
	
	<script>
	var x_QOsClassDelete=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      RefreshTab('qostabs');
      }	
	
	function QOsClassDelete(ID){
		var text='$qos_class_delete_confirm'
		if(confirm(text)){
			var XHR = new XHRConnection();
			XHR.appendData('class-delete',ID);
			XHR.sendAndLoad('$page', 'GET',x_QOsClassDelete);		
		}
	}
	
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
}
function class_delete(){
	$qos=new qos_class($_GET["class-delete"]);
	$qos->delete_class();
	
}

function class_rules_index(){
	$tpl=new templates();
	$page=CurrentPageName();	
	
	$qos=new qos_class($_GET["class_id"]);
	
	
	$html="
	<div class=explain>$qos->name ({$qos->rate}kbs - {$qos->ceil}kbs)</div>
	<div style='width:100%;text-align:right;margin-bottom:8px'>". button("{add_rule}","YahooWin4('500','$page?class-rules-id=yes&rule_id=0&class_id={$_GET["class_id"]}','{add_rule}')")."</div>
	
	<div style='width:100%;height:350px;overflow:auto' id='class-rules-list'></div>
	
	
	<script>
		LoadAjax('class-rules-list','$page?class-rules-list=yes&class_id={$_GET["class_id"]}');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function class_rules_popup(){
	$tpl=new templates();
	$page=CurrentPageName();		
	
	$protos["tcp"]="TCP";
	$protos["UDP"]="UDP";
	
	$sql="SELECT * FROM qos_rules WHERE ID='{$_GET["rule_id"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["sip"]==null){$ligne["sip"]="*";}
	if($ligne["dip"]==null){$ligne["dip"]="*";}
	if($ligne["name"]==null){$ligne["name"]="New container rule";}
	
	
	$html="
	<div id='classrulediv'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{enabled}:</td>
		<td>". Field_checkbox("qos_fw_enabled",1,$ligne["enabled"],"QOsTableCheckEnabled()")."</td>
	</tr>	
	<tr>
		<td class=legend>{rule_name}:</td>
		<td>". Field_text("qos_fw_rule_name",$ligne["name"],"font-size:13px;padding:3px;width:250px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{from_ip}:</td>
		<td>". Field_text("sip",$ligne["sip"],"font-size:13px;padding:3px;width:190px")."</td>
		<td>". help_icon("{from_help}")."</td>
	</tr>
	<tr>
		<td class=legend>{source_port}:</td>
		<td>". Field_text("sport",$ligne["sport"],"font-size:13px;padding:3px;width:90px")."</td>
		<td>". help_icon("{iptables_ports}")."</td>
	</tr>	
	<tr>
		<td class=legend>{to_ip}:</td>
		<td>". Field_text("dip",$ligne["dip"],"font-size:13px;padding:3px;width:190px")."</td>
		<td>". help_icon("{from_help}")."</td>
	</tr>	
	<tr>
		<td class=legend>{destination_port}:</td>
		<td>". Field_text("dport",null,"font-size:13px;padding:3px;width:90px")."</td>
		<td>". help_icon("{iptables_ports}")."</td>
	</tr>	
	<tr>
		<td class=legend>{protocol}:</td>
		<td>". Field_array_Hash($protos,"proto",null,null,null,0,"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
	<td colspan=3 align='right'><hr>". button("{apply}","SaveQOSFwRule()")."</td>
	</tr>
</table>
</div>
	<script>
		function QOsTableCheckEnabled(){
			var rule_id={$_GET["rule_id"]};
			if(rule_id==0){return;}
			document.getElementById('sport').disabled=true;
			document.getElementById('dport').disabled=true;
			document.getElementById('proto').disabled=true;
			document.getElementById('sip').disabled=true;
			document.getElementById('dip').disabled=true;
		
			if(document.getElementById('qos_fw_enabled').checked){
				document.getElementById('sport').disabled=false;
				document.getElementById('dport').disabled=false;
				document.getElementById('proto').disabled=false;
				document.getElementById('sip').disabled=false;
				document.getElementById('dip').disabled=false;			
			}		
		}
		
	var x_SaveQOSFwRule=function(obj){
      var tempvalue=obj.responseText;
      var class_id='{$_GET["class_id"]}';
	  if(tempvalue.length>3){alert(tempvalue);}
	  RefreshTab('qosclass'+class_id);
	  YahooWin4Hide();
      }	
	
	function SaveQOSFwRule(){
		var XHR = new XHRConnection();
		XHR.appendData('class-rules-save',1);
		XHR.appendData('class_id','{$_GET["class_id"]}');
		XHR.appendData('rule_id','{$_GET["rule_id"]}');
		XHR.appendData('sport',document.getElementById('sport').value);
		XHR.appendData('dport',document.getElementById('dport').value);
		XHR.appendData('proto',document.getElementById('proto').value);
		XHR.appendData('sip',document.getElementById('sip').value);
		XHR.appendData('dip',document.getElementById('dip').value);
		XHR.appendData('name',document.getElementById('qos_fw_rule_name').value);
		if(document.getElementById('qos_fw_enabled').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}		
		document.getElementById('classrulediv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveQOSFwRule);		
		}		
	QOsTableCheckEnabled();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	}

function class_rules_save(){
	$class_id=$_GET["class_id"];
	$_GET["name"]=replace_accents($_GET["name"]);
	$sql="INSERT INTO qos_rules(`name`,`sip`,`sport`,`dip`,`dport`,`proto`,`class_id`,`enabled`)
	VALUES('{$_GET["name"]}','{$_GET["sip"]}','{$_GET["sport"]}','{$_GET["dip"]}','{$_GET["dport"]}',
	'{$_GET["proto"]}','$class_id','{$_GET["enabled"]}')";
	
	if($_GET["rule_id"]>0){
		$sql="UPDATE qos_rules
		SET `name`='{$_GET["name"]}',
		`sip`='{$_GET["sip"]}',
		`sport`='{$_GET["sport"]}',
		`dport`='{$_GET["dport"]}',
		`dip`='{$_GET["dip"]}',
		`enabled`='{$_GET["enabled"]}',
		`proto`='{$_GET["proto"]}' WHERE ID='{$_GET["rule_id"]}'";
		
		
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?qos-compile=yes");	
}

function class_rules_list(){
	$tpl=new templates();
	$page=CurrentPageName();	
	
	$sql="SELECT * FROM qos_rules WHERE class_id={$_GET["class_id"]} ORDER BY ID DESC";
	$q=new mysql();
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=5>{rules}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
		$pointer="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
		$results=$q->QUERY_SQL($sql,'artica_backup');
		if(!$q->ok){echo "<H3>$q->mysql_error</H3>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$delete=imgtootltip("delete-24.png","{delete}","QOsRuleDelete('{$ligne["ID"]}')");
			$edit="YahooWin4('500','$page?class-rules-id=yes&rule_id={$ligne["ID"]}&class_id={$_GET["class_id"]}','{rule}::{$ligne["name"]}')";
			$color="black";
			if($ligne["enabled"]==0){$color="#A5A5A5";}
			$html=$html."
			<tr class=$classtr>
				
				<td><strong style='font-size:14px;text-decoration:underline;color:$color' $pointer OnClick=\"javascript:$edit\">{$ligne["name"]}</td>
				<td align=center width=1% nowrap><strong style='font-size:14px;text-decoration:underline;color:$color' $pointer OnClick=\"javascript:$edit\">{$ligne["sip"]}:{$ligne["sport"]} ({$ligne["proto"]})</td>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td align=center width=1% nowrap><strong style='font-size:14px;text-decoration:underline;color:$color' $pointer OnClick=\"javascript:$edit\">{$ligne["dip"]}:{$ligne["dport"]} ({$ligne["proto"]})</td>
				<td width=1%>$delete</td>
			</tr>
			";
			
		}
	$html=$html."</tbody></table>
	
<script>
	var x_QOsRuleDelete=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      RefreshTab('qosclass{$_GET["class_id"]}');
      }	
	
	function QOsRuleDelete(ID){
		var XHR = new XHRConnection();
		XHR.appendData('class-rules-delete',ID);
		XHR.sendAndLoad('$page', 'GET',x_QOsRuleDelete);		
		}
	
	</script>	
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function class_rules_delete(){
	$sql="DELETE FROM qos_rules WHERE ID='{$_GET["class-rules-delete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?qos-compile=yes");	

}

function master_service_del_mysql(){
	$sql="DELETE FROM qos_eth WHERE ID='{$_GET["master-service-delete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}	
	
	$sql="SELECT ID FROM qos_class WHERE service_id={$_GET["master-service-delete"]}";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$qos_class=new qos_class($ligne["ID"]);
		$qos_class->delete_class();
	}
	
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?qos-compile=yes");	
}

function master_service_enable_mysql(){
	$sql="UPDATE qos_eth SET `enabled`={$_GET["master-service-enable"]} WHERE ID={$_GET["master-service-id"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}		
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?qos-compile=yes");		
}


function iptables_cmds(){
	
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?qos-iptables=yes"));
	$html="<textarea style='
	width:100%;
	height:350px;
	overflow:auto;
	font-size:13px;font-family: \"Courier New\" Courier monospace;
	line-height:2'>$datas</textarea>";
	echo $html;
	
	
	
}
