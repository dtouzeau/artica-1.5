<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.bind9.inc');	

	$user=new usersMenus();
	if(!$user->AsDnsAdministrator){header('location:users.index.php');die();}
	if(isset($_GET["script"])){echo js_script();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["forwarder_add"])){forwarder_add();exit;}
	if(isset($_GET["forwarder_delete"])){forwarder_delete();exit;}
	if(isset($_GET["zone_save"])){SaveZoneConfig();exit;}
	if(isset($_GET["zone-hosts-list"])){echo zone_hosts_lits($_GET["zone-hosts-list"]);exit;}
	if(isset($_GET["search-hosts"])){echo zone_hosts_lits($_GET["zone"]);exit;}
	if(isset($_GET["show-zones"])){echo main_zones_list();exit;}
	if(isset($_GET["AddNewDnsZone"])){AddNewDnsZone();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["zone_delete"])){zone_delete();exit;}
	if(isset($_GET["CompileBind9"])){js_compilebind();exit;}
	if(isset($_GET["compile-bind"])){compilebind();exit;}
	if(isset($_GET["compile-bind-icon"])){echo ICON_BIND9_COMPILE(1);exit();}
	if(isset($_GET["ajax"])){js_start();exit;}
	if(isset($_GET["AddBind9ZoneDomain"])){js_add_zone();exit;}
	if(isset($_GET["js-zones"])){echo js_list_zones();exit;}
	if(isset($_GET["ZoneListComp"])){return js_list_computers();exit;}
	main_page();
	
	
	
function js_script(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{APP_BIND9}');
	$js=file_get_contents('js/bind.js');
	$html="
		$js
		function PageIndex(){
			YahooWin(550,'$page?ajax=yes','$title');
		
		}
		
		var x_AddBind9ZoneDomain=function (obj) {
			var tempvalue=obj.responseText;
		        if(tempvalue.Length>0){
		            alert(tempvalue.Length);
		        }
		    PageIndex()
					
		}		
		
		function AddBind9ZoneDomain(){
		  var text=document.getElementById('AddNewDnsZone_explain').value;
		  var text2=document.getElementById('AddNewDnsReverseZone').value;
    		var newzone=prompt(text);
    		if(!newzone){
    			alert('cancel');
    			return false;
			}
    		var AddNewDnsReverseZone=prompt(text2);
    		
    		
    		if(newzone){
    				if(AddNewDnsReverseZone){
    					document.getElementById('zones').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
        				var XHR = new XHRConnection();
        				XHR.appendData('AddBind9ZoneDomain',newzone);
        				XHR.appendData('reverseIP',AddNewDnsReverseZone);
        				XHR.sendAndLoad('$page', 'GET',x_AddBind9ZoneDomain);      
    					}else{
    						alert('cancel');
						}
    				}
    	}
    	
    	function BindComputers(zone){
    		YahooWin2(550,'$page?ZoneListComp='+zone,zone);
    	
    	}

	PageIndex();
	";
	
	echo $html;
}


function js_compilebind(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body('{apply_settings_bind9}');
	$success=$tpl->_ENGINE_parse_body('{success}');
	$html="
	
	var x_CompileBind9Server=function (obj) {
			var tempvalue=obj.responseText;
			LoadAjax('CompileBind9','$page?compile-bind-icon=yes');	
			alert('$title\\n$success');
		}		

	function CompileBind9Server(){
		document.getElementById('CompileBind9').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('compile-bind','yes');
		XHR.sendAndLoad('$page', 'GET',x_CompileBind9Server);   
		}
		
	CompileBind9Server();
	";
	
	echo $html;
	
}

function js_start(){
	$forwarder=Forwarders_list();
	$html="<H1>{APP_BIND9}</H1>
	<input type='hidden' value='{forwarder_add}' id='forwarder_add'>
	<input type='hidden' value='{AddNewDnsZone_explain}' id='AddNewDnsZone_explain'>
	<input type='hidden' value='{AddNewDnsReverseZone}' id='AddNewDnsReverseZone'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		<H3>{forwarders}</H3>
			<table style='width:100%'>
				<tr>
				<td valign='top'>
					<div id='pdns'>$forwarder</div>
					<div style='width:100%;text-align:right'>
						<input type='button' OnClick=\"javascript:AddForwarder();\" value='{add_forwarder}&nbsp;&raquo;'>
						<br><i>{forwarders_text}</i>
					</div>
					<br>
					<H3>{dns_zones}</H3>
					<div id='zones'>
					".js_list_zones()."
					</div>
					
				</td>
				<td valign='top'>" . 
				ApplySettings_icon()."
				<br>".
				Paragraphe('connection-add-64.png','{add_new_zone}','{add_new_zone_tex}',"javascript:AddBind9ZoneDomain()",'{add_new_zone_tex}')."
				
				
				</td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function js_add_zone(){
	
	
$domain=$_GET["AddBind9ZoneDomain"];
$ip=$_GET["reverseIP"];

writelogs("Query new zone $domain/$ip",__FUNCTION__,__FILE__);

$bind=new Bind9Zone($domain);
$headers=$bind->SaveBind9Zone($ip,"no");



	
}

function js_list_zones(){
	
$bind=new Bind9Zone(null);
if(!is_array($bind->array_zones)){return null;}

$html="<table style='width:100%' class=table_form>

";

while (list ($num, $ligne) = each ($bind->array_zones) ){
	$comps=$bind->ListComputers($ligne);
	
	
	$html=$html . "<tr ". CellRollOver("BindComputers('$ligne')").">
				<td width=1%><img src='img/globe2.gif'></td>
				<td style='font-size:13px;font-weight:bold'>$ligne</td>
				<td style='font-size:13px;font-weight:bold'>$bind->count_computers {computers}</td>
			</tR>
		";
}

$html=$html . "</table>";
return $html;
	
}

function js_list_computers(){
	
$zone=$_GET["ZoneListComp"];
$bind=new Bind9Zone($zone);
if(!is_array($bind->array_zone_computers)){$bind->array_zone_computers=array();}

$html="
<input type='hidden' id='ZoneListComp' value='$zone'>
<H1>{computers}</H1>
<br>
<table style='width:100%'>
<tr>
<td valign='top'>
<center>
<div style='width:210px;height:400px;overflow:auto;padding:5px'>
<table style='width:100%' class=table_form>

";

$addcomp=Paragraphe("computer-64-add.png","{add_computer}","{add_computer_text}","javascript:YahooWin3(670,\"domains.edit.user.php?userid=newcomputer$&ajaxmode=yes&gpid=$gpid&zone-name=$zone\",\"windows: New {add_computer}\");");

while (list ($num, $ligne) = each ($bind->array_zone_computers) ){
	$html=$html."<tr " . CellRollOver()." " . MEMBER_JS($num.'$',0,0).">
	<td width=1%><img src='img/wks_green.gif'></td>
	<td><strong>$num</td>
	<td><strong>{$ligne[0]}</td>
	<td><strong>{$ligne[1]}</td>
	</tr>
	
	";
	
}

$html=$html . "</table></div></center>

</td>
<td valign='top'>
$addcomp

</td>
</tr>
</table>";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'domains.edit.group.php');

}
	
function main_page(){
	$page=CurrentPageName();

	if($_GET["hostname"]==null){
		$user=new usersMenus();
		
		$_GET["hostname"]=$user->hostname;}
	
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
	var status='status';
	
	if(document.getElementById('statusid')){
		status=document.getElementById('statusid').value;
	}
	LoadAjax('services_status','$page?status='+status+'&hostname={$_GET["hostname"]}');
	}
</script>		
	
	
	
	
	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
		<img src='img/bg_bind9.png'></td>
	<td valing='top'>
		<div id='services_status'></div>
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			<div id='squid_main_config'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('squid_main_config','$page?main=yes')</script>
	<script>demarre();</script>
	<script>ChargeLogs();</script>
	";
	
	
	$cfg["JS"][]='js/bind.js';
	
	
	
	$tpl=new template_users('{APP_BIND9}',$html,0,0,0,0,$cfg);
	
	echo $tpl->web_page;
	
	
	
}


function main_switch(){
	if(!isset($_GET["tab"])){$_GET["tab"]="status";};
	
	if($_GET["hostname"]==null){
		$users=new usersMenus();
		$_GET["hostname"]=$users->hostname;
		}	

	switch ($_GET["tab"]) {
		case "forwarders-list":echo Forwarders_list();exit;break;
		case "config":echo main_config();exit;break;
		case "dns-zones":echo main_zones();exit;break;
		case "zone-edit":echo main_zone_edit();exit;break;
		case "zone-explain":echo main_zone_explain();exit;break;
		case "stats":echo main_statistics();exit;break;
		default:main();exit;break;
	}
	
	
}


function main_zones(){
	
	
	
	$tab=main_tabs();
	$bind=new bind9();
	
	$addzone=RoundedLightGrey(Paragraphe("64-bind9-add-zone.png",'{add_new_zone}',"{add_new_zone_tex}","javascript:AddNewDnsZone()"));
	
	//
	$html="$tab<H3>{dns_zones}</h3>
	<input type='hidden' name='ZoneDeleteWarning' id='ZoneDeleteWarning' value='{ZoneDeleteWarning}'>
	<input type='hidden' name='AddNewDnsZone_explain' id='AddNewDnsZone_explain' value='{AddNewDnsZone_explain}'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div id='zones'>".main_zones_list()."</div>
		</td>
		<td valign='top' width=1%>$addzone</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function main_zone_edit(){
	if($_GET["zone-tab"]=="config"){main_zone_config();exit;}
	if($_GET["zone-tab"]=="hosts"){main_zone_hosts();exit;}
	$zone=$_GET["zone"];
	$tab=main_zone_tabs();
	$bind=new bind9();
	$bindZone=new bind9_zones($zone);
	
	$typeTable=array("master"=>"{master}","slave"=>"{slave}","stub"=>"{stub}","forward"=>"{forward}","hint"=>"{hint}");
	$ttlTable=array("2592000"=>"1 {month}","604800"=>"1 {week}","86400"=>"1 {day}","172800"=>"2 {days}","38400"=>'10 {hours}','3600'=>"1 {hour}",'10800'=>'3 {hours}',"300"=>'5 {minutes}');
	
	
	$type=Field_array_Hash($typeTable,"zone_type",$bind->zones["$zone"]["type"],"zone_explain()");
	$ttl=Field_array_Hash($ttlTable,"ttl",$bindZone->ttl);
	$html="$tab<H5>{zone} $zone</H5>
	<form name='ffm1'>
	<input type='hidden' id='zone_save' name='zone_save' value='$zone'>
	<table style='width:100%'>
			<tr>
			<td align='right' nowrap valign='top'><strong>{PrimaryServerName}:</strong></td>
			<td valign='top'>" . Field_text('PrimaryServerName',$bindZone->PrimaryServerName,'width:130px')."</td>
			<td valign='top'><div id='ttl_text' class='caption'>{PrimaryServerName_text}</div></td>
		</tr>	
		<tr>
			<td align='right' nowrap valign='top'><strong>{Hostmaster_email}:</strong></td>
			<td valign='top'>" . Field_text('Hostmaster_email',$bindZone->Hostmaster_email,'width:130px')."</td>
			<td valign='top'><div id='ttl_text' class='caption'>{Hostmaster_email_text}</div></td>
		</tr>	
		<tr>
			<td align='right' nowrap valign='top'><strong>{type}:</strong></td>
			<td valign='top'>$type</td>
			<td valign='top'><div id='zone_explain' class='caption'>{{$bind->zones["$zone"]["type"]}_text}</div></td>
		</tr>
		<tr>
			<td align='right' nowrap valign='top'><strong>{ttl}:</strong></td>
			<td valign='top'>$ttl</td>
			<td valign='top'><div id='ttl_text' class='caption'>{ttl_text}<br>($bindZone->ttl seconds)</div></td>
		</tr>	
		<tr>
			<td align='right' nowrap valign='top'><strong>{refresh_time}:</strong></td>
			<td valign='top'>".Field_array_Hash($ttlTable,"refresh_time",$bindZone->numeric_options[1]) ."</td>
			<td valign='top'><div id='ttl_text' class='caption'>{refresh_time_text}<br>({$bindZone->numeric_options[1]} seconds)</div></td>
		</tr>
		<tr>
			<td align='right' nowrap valign='top'><strong>{retry_time}:</strong></td>
			<td valign='top'>".Field_array_Hash($ttlTable,"retry_time",$bindZone->numeric_options[2]) ."</td>
			<td valign='top'><div id='ttl_text' class='caption'>{retry_time_text}<br>({$bindZone->numeric_options[2]} seconds)</div></td>
		</tr>	
		<tr>
			<td align='right' nowrap valign='top'><strong>{expire_time}:</strong></td>
			<td valign='top'>".Field_array_Hash($ttlTable,"expire_time",$bindZone->numeric_options[3]) ."</td>
			<td valign='top'><div id='ttl_text' class='caption'>{expire_time_text}<br>({$bindZone->numeric_options[3]} seconds)</div></td>
		</tr>	
		<tr>
			<td align='right' nowrap valign='top'><strong>{minimal_ttl}:</strong></td>
			<td valign='top'>".Field_array_Hash($ttlTable,"minimal_ttl",$bindZone->numeric_options[4]) ."</td>
			<td valign='top'><div id='ttl_text' class='caption'>{minimal_ttl_text}<br>({$bindZone->numeric_options[4]} seconds)</div></td>
		</tr>	
		<tr>
		<td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"></td>
		</tr>
			
				
	</table></form>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function main_zone_explain(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{{$_GET["zone-selected"]}_text}");	
	
}

function main_zone_tabs(){
	$zone=$_GET["zone"];
	$page=CurrentPageName();
	if(!isset($_GET["zone-edit"])){$_GET["zone-edit"]="global_settings";}
	$array["global_settings"]='{global_settings}';
	$array["hosts"]='{hosts}';
	$array["config"]='{config}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["zone-tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('dialog1_content','$page?main=yes&tab=zone-edit&zone=$zone&zone-tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";	
	
	
}

function main_zone_config(){
	
	$bind=new bind9_zones($_GET["zone"]);
	$datas=$bind->ZoneContent;
	
	
	$tab=main_zone_tabs();
	$html="$tab<H3>{config_file}</H3><textarea style='border:0px;font-size:10px;width:100%' rows=40>$datas</textarea>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function main_statistics(){
	
	
	
	
	
	$tab=main_tabs();
	$html="$tab<H3>{statistics}</H3>
	
	
	<table style='width:100%'>
	<tr>
		<td>
		<H3>{day}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=days&ds='></center></td>
	</tr>
	<tr>
		
		<td>
		<hr>
		<H3>{week}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=weeks&ds='></center></td>
	</tr>
	<tr>
		
		<td>
		<hr>
		<H3>{month}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=months&ds='></center></td>
	</tr>
	<tr>
		
		<td>
		<hr>
		<H3>{year}</H3>
		<center><img src='cgi-bin/bindrrd-grapher.pl?zone=overall&height=200&width=500&timeunit=years&ds='></center></td>
	</tr>			
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}


function main_zones_list(){
	
$bind=new bind9();	
$html="<table style='width:100%'>";
while (list ($num, $ligne) = each ($bind->zones) ){
	
	$delete=imgtootltip('ed_delete.gif',"{delete} $num {dns_zones}","zone_delete('$num');");
	$edit=imgtootltip('settings-20.gif',"{edit} $num {dns_zones}","zone_edit('$num');");
	if(($num=='.') OR ($num=='localhost') OR ($num=='127.in-addr.arpa') OR($num=='0.in-addr.arpa') OR ($num=='255.in-addr.arpa')){$delete='&nbsp;';$edit="&nbsp;";}
	
	
	$html=$html . "<tr ".  CellRollOver().">
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:12px'>$num</strong></td>
		<td width=1%>$edit</td>
		<td width=1%>$delete</td>
		</tr>
		
		";}
		
	$html=$html."</table>";
$tpl=new templates();
	return RoundedLightGreen($tpl->_ENGINE_parse_body($html));		
	}

function main(){
	
	
	$tab=main_tabs();
	$bind=new bind9();
	$sock=new sockets();
	$PostfixEnabledInBind9=$sock->GET_INFO('PostfixEnabledInBind9');
	if($PostfixEnabledInBind9<>1){
	$forwarders="
		<tr>
			<td nowrap align='right' valign='top'><strong>{forwarders}:</strong></td>
			<td><div id='pdns'>".Forwarders_list()."</div></td>
			<td valign='top'><input type='button' OnClick=\"javascript:AddForwarder();\" value='{add}&nbsp;&raquo;'></td>
		</tr>";	
	}

	$html="$tab<H3>{global_settings}</H3>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	

	
	
	". RoundedLightGrey("
		<input type='hidden' id='forwarder_add' value='{forwarder_add}'>
		<table style='width:100%'>
		<tr>
		<td nowrap align='right'><strong>{global_directory}:</strong></td>
		<td><strong>$bind->global_directory</strong></td>
		<td>&nbsp;</td>	
		$forwarders
		</table>
	
	").
	"</td>
	<td valign='top' width=1%><div id='apply'>".ApplySettings_icon()."</div></td>
	</tr>
	</table>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function ApplySettings_icon(){
	return ICON_BIND9_COMPILE();
}

function Forwarders_list(){
	
	$bind=new bind9();
	$html="<table style='width:100%' class=table_form>";
	while (list ($num, $ligne) = each ($bind->forwarders) ){
		$html=$html . "<tr ".  CellRollOver().">
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td><strong>$ligne</strong></td>
		<td width=1%>". imgtootltip('ed_delete.gif',"{delete} $ligne","forwarder_delete($num);")."</td>
		</tr>
		
		";}
		
	$html=$html."</table>";
$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
}


function main_tabs(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["global_settings"]='{global_settings}';
	$array["dns-zones"]='{dns_zones}';
	$array['config']='{config_file}';
	
	if($users->bindrrd_installed){
		$array['stats']='{statistics}';
	}

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=yes&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";		
}

function forwarder_add(){
	$bind=new bind9();
	$ip=new IP();
	if(!$ip->isIPAddress($_GET["forwarder_add"])){return null;}
	$bind->AddNewForwarder($_GET["forwarder_add"]);
	}
function forwarder_delete(){
	$bind=new bind9();
	unset($bind->forwarders[$_GET["forwarder_delete"]]);
	$bind->Compile();
	}

function compilebind(){
	$bind=new bind9();
	$bind->Compile();
	$bind->BuildZones();
	echo ApplySettings_icon();
}

function main_config(){
	$bind=new bind9();
	$datas=$bind->NamedConf;
	$datas=htmlentities($datas);
	$datas=explode("\n",$datas);
	
	while (list ($num, $ligne) = each ($datas) ){
	if(trim($ligne)<>null){
		$ligne=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$ligne);
		$conf=$conf."<div><code style='font-size:10px'>$ligne</div>\n";
		}
		
	}
	
	$tab=main_tabs();
	$html="$tab<H3>{config_file}</H3>$conf";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function SaveZoneConfig(){
	$zone=$_GET["zone_save"];
	$bind=new bind9();
	$bind->zones[$zone]["type"]=$_GET["zone_type"];
	$bind->SaveToLdap();
	
	$bindZone=new bind9_zones($zone);
	$bindZone->Hostmaster_email=$_GET["Hostmaster_email"];
	$bindZone->PrimaryServerName=$_GET["PrimaryServerName"];
	$bindZone->ttl=$_GET["ttl"];
	
	$bindZone->numeric_options[1]=$_GET["refresh_time"];
	$bindZone->numeric_options[2]=$_GET["retry_time"];
	$bindZone->numeric_options[3]=$_GET["expire_time"];
	$bindZone->numeric_options[4]=$_GET["minimal_ttl"];
	$bindZone->SaveToLdap();
}


function main_zone_hosts(){
	$tab=main_zone_tabs();
	$zone=$_GET["zone"];
	
	
	
	$html="$tab<H3>{$_GET["zone"]} {hosts}</H3>
	<input type='hidden' id='search_explain' value='{search_explain}'>
	<input type='hidden' id='zone_org' value='$zone'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='bind9_hosts_list'>".zone_hosts_lits($zone)."</div></td>
		<td valign='top' width=1%>
			<table style='width:100%'>
			<tr>
				<td>
				".RoundedLightGrey(Paragraphe("computers-64.png","{search_computer}","{search_computer_text}","javascript:SearchDnsConputer();"))."<br>
				".RoundedLightGrey(Paragraphe("computer-64-add.png","{add_computer}","{add_computer_text}","javascript:YahooWin2(670,\"domains.edit.user.php?userid=newcomputer$&ajaxmode=yes&gpid=$gpid&zone-name=$zone\",\"windows: New {add_computer}\");"))."
				
				</td>
			</tr>
			</table>
	 	</td>
	</tr>
</table>
";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'domains.edit.group.php');
	
}

function zone_delete(){
	$zone=$_GET["zone_delete"];
	$bind=new bind9();
	unset($bind->zones[$zone]);
	$bind->SaveToLdap();
	$BindZones=new bind9_zones($zone);
	$BindZones->DeleteThisZone();
	
	
}


function zone_hosts_lits($zone){
	
	$ldap=new clladp();
	$query="(&(objectClass=ArticaComputerInfos)(DnsZoneName=$zone))";
	$filter=array();
	
	
	
	if($_GET["search-hosts"]<>null){
		
		$pattern=$_GET["search-hosts"];
		$tbl=explode(' ',$pattern);
		$searchzone="(DnsZoneName={$_GET["zone"]})";
		if(trim($tbl[1])=="all"){$searchzone=null;$pattern=$tbl[0];}
		
		$query="(&(objectClass=ArticaComputerInfos)(|(cn=$pattern)(ComputerIP=$pattern))$searchzone)";
	}
	
	$hash=$ldap->Ldap_search($ldap->suffix,$query,$filter);
	$html="<strong>{$hash["count"]} {computers}</strong><br>
	<input type='hidden' id='patterfind' value='{$_GET["search-hosts"]}'>
	";
	$count=$hash["count"];
	if($count>18){$count=18;}
	for($i=0;$i<$count;$i++){
		
		$uid=str_replace('$','',$hash[$i]["uid"][0]);
		$ip=$hash[$i]["computerip"][0];
		$type=$hash[$i]["dnstype"][0];
		$html=$html . "
		
		<div style='float:left;width:120px;margin:2px;padding:2px;border:1px solid white' ".CellRollOver("YahooWin2(670,'domains.edit.user.php?userid=$uid$&ajaxmode=yes')").">
		<table style='width:100%'>
		<tr>
		<td valign='top' width=1%><img src='img/workstation-linux-32.png'></td>
		<td valign='top'><strong style='font-size:11px'>$uid</strong>
		<br>$ip<br>{type}:$type
		</td>
		</tr>
		</table>
		</div>
		
		";
		
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
	
}

function AddNewDnsZone(){
	
	$zone=$_GET["AddNewDnsZone"];
	$bind=new bind9();
	$bind->zones["$zone"]["type"]="master";
	$bind->zones["$zone"]["file"]="/etc/bind/".md5($zone);
	$bind->SaveToLdap();
	
	$BinZone=new bind9_zones($zone);
	$BinZone->AddZone();
	
	
	
}
function main_status(){
	$users=new usersMenus();
	$tpl=new templates();

	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$key_service="BIND9";
	$ini->loadString($sock->getfile('bind9status',$_GET["hostname"]));	
	if($ini->_params["$key_service"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
	}
	
	$status1="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{{$ini->_params["$key_service"]["service_name"]}}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["$key_service"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap><strong>{$ini->_params["$key_service"]["master_memory"]}&nbsp; kb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["$key_service"]["master_version"]}</strong></td>
		</tr>				
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status1=RoundedLightGreen($status1);
	return $tpl->_ENGINE_parse_body($status1);
	
	
}

?>
	
	
	
	
