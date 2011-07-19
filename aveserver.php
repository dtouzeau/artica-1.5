<?php
include_once('ressources/class.templates.inc');
include_once('ressources/kav4mailservers.inc');
include_once('ressources/class.status.inc');
if(isset($_GET["viewlogs"])){include_once("smtpscanner.logs.php");exit;}
if(isset($_GET["LicenseDomain_Add"])){LicenseDomain_Add();exit;}
if(isset($_GET["LicenseDomain_edit"])){LicenseDomain_edit();exit;}
if(isset($_GET["LicenseDomain_Delete"])){LicenseDomain_Delete();exit;}
if(isset($_GET["action_keepup2date"])){action_keepup2date();exit;}
$html="<table><tr><td valign='top'>" . array_infos() . "</td><td valign='top' width=50%>" . LicenceInfos() . Database()."</td></tr></table>";

$tpl=new templates('{title}',$html);
echo $tpl->web_page;


function array_infos(){
	

$kav4mailservers=new kav4mailservers();
if($kav4mailservers->error==true){return "{error_no_socks}";}


$html="
<FIELDSET><LEGEND>{kav_title_infos}</LEGEND>
<table style='width:400px'>
<tr class=rowA>
<td align=right>{pversion}:</td>
<td align=right>{$kav4mailservers->version}</td>
</tr>
<tr class=rowA>
<td align=right>{BasesPath}:</td>
<td align=right>{$kav4mailservers->array_conf["path"]["BasesPath"]}</td>
</tr>

<tr class=rowA>
<td align=right>{LocalSocketPath}:</td>
<td align=right>{$kav4mailservers->array_conf["path"]["LocalSocketPath"]}</td>
</tr>

<tr class=rowA>
<td align=right>{ForwardMailer}:</td>
<td align=right>{$kav4mailservers->array_conf["smtpscan.general"]["ForwardMailer"]}</td>
</tr>
<tr class=rowA>
<td align=right valign='top'>{protected_domains}:</td>
<td align=right width=5%><div id='protected_domain'>" . protected_domains() . "</div></td>
</tr>

</table>";

/*


=/var/run/aveserver 
AVSpidPATH=/var/run/aveserver.pid
TempPath=/tmp
IcheckerDbFile=/var/db/kav/5.5/ichecker.db
*/

return $html;

}

function protected_domains(){
$kav4mailservers=new kav4mailservers();
if($kav4mailservers->error==true){return "{error_no_socks}";}
if(!is_array($kav4mailservers->LicenseDomains)){return "&nbsp;";}

$ldap=new clladp();
$hash_domains=$ldap->hash_get_all_domains();


$html="<table>";
while (list ($num, $ligne) = each ($kav4mailservers->LicenseDomains) ){
	if($class=="rowA"){$class="rowB";}else{$class="rowA";}
	$link_to_domain="<a href='domains.edit.php?domain=$ligne'>";
	if($hash_domains[$ligne]==null){$link_to_domain=null;	}
	$html=$html ."<tr class=$class>
			<td width=1%><img src='img/domain_mail.gif'></td>
			<td>$link_to_domain$ligne</a></td>
			<td width=1%><a href='#'  OnClick=\"javascript:LicenseDomain_Delete('$ligne');\" onMouseOver=\"javascript:AffBulle('{js_kav_delete_licence}');\" OnMouseOut=\"javascript:HideBulle();\" ><img src='img/x.gif'></td>
			</tr>";
}
$html=$html ."
<tr>
<td colspan=3 align='center'><input type='button' value='{add_Kav_LicenseDomain}&nbsp;&raquo;' OnClick=\"javascript:LicenseDomain_Add()\";></td>
</tr>
</table>";
$tpl=new templates();
return $tpl->_parse_body($html);
}
function LicenseDomain_edit(){
	$kav4mailservers=new kav4mailservers();
	if($kav4mailservers->error==true){return "{error_no_socks}";}
	$kav4mailservers->LicenseDomains[]=$_GET["LicenseDomain_edit"];
	$kav4mailservers->Save();
	echo protected_domains();
}


function Database(){
	$sock=new sockets();
	$satus=new status(1);
	$stat=explode(';',trim($sock->getfile('aveserver_status')));
		if($sock->error==true){return null;}
	$date=$satus->avestatus_pattern_date($stat[3],$stat[4],1);
	$html="<FIELDSET><LEGEND>{update_kaspersky_database_title}</LEGEND>
	<table style='width:400px'>
		<tr class=rowA>
		<td align=right>{pattern_database}:</td>
		<td align=right>$date</td>
		</tr>
		<tr class=rowA>
		<td align=right>&nbsp;</td>
		<td align=right><input type='button' value='{action_keepup2date}' OnClick=\"javascript:action_keepup2date();\"></td>
		</tr>
</table>
";
	return $html;
	
}

function LicenseDomain_Delete(){
	$domain=$_GET["LicenseDomain_Delete"];
	$kav4mailservers=new kav4mailservers();
	if($kav4mailservers->error==true){return "{error_no_socks}";}
	while (list ($num, $ligne) = each ($kav4mailservers->LicenseDomains) ){
		if($ligne==$domain){
			unset($kav4mailservers->LicenseDomains[$num]);
		}
	}
	$kav4mailservers->Save();
	echo protected_domains();
	
}

function LicenseDomain_Add(){
	$kav4mailservers=new kav4mailservers();
	if($kav4mailservers->error==true){return "{error_no_socks}";}
	if(!is_array($kav4mailservers->LicenseDomains)){$kav4mailservers->LicenseDomains[]=null;}
	while (list ($num, $ligne) = each ($kav4mailservers->LicenseDomains) ){
		$licenceDomains[$ligne]=$ligne;
	}
	
	$ldap=new clladp();
	$hash_domains=$ldap->hash_get_all_domains();	
	if(!is_array($hash_domains)){
		$body="{ERROR_NO_DOMAINS_CREATED_FIRST}";
	}else{
		while (list ($num, $ligne) = each ($hash_domains) ){
			if($licenceDomains[$ligne]==null){
				if($ligne<>null){$hash[$ligne]=$ligne;}
			}
		}
		$hash[""]="{input_select_list}";
		$body=Field_array_Hash($hash,'LicenseDomain',null);
	}
	
	$html="<fieldset style='width:80%'><legend>{kav_add_protection_domain}</legend>
	<table>
	<tr class=rowA>
		<td align='right'>{kav_legend_protection_domain}:</td>
		<td>$body</td>
	</tr>
	<tr class=rowB>
		<td align='right' colspan=2><input type='button' OnClick=\"javascript:LicenseDomain_edit();\" value='{submit}&nbsp;&raquo;'></td>
	</tr>	
	</table>
	</fieldset>";
	$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
	
}

function LicenceInfos(){
	$kav4mailservers=new kav4mailservers();
	if($kav4mailservers->error==true){return "{error_no_socks}";}
	$html="<FIELDSET>
		<LEGEND>{title_licence_infos}</LEGEND>
		<table>
		<tr class='rowA'>
			<td>{expire_date}:</td>
			<td>{$kav4mailservers->array_licence_infos["EXPIRE"]}</td>
		</tr>
		<tr class='rowA'>
			<td>{product}:</td>
			<td>{$kav4mailservers->array_licence_infos["NAME"]}</td>
		</tr>	
			
		</table>
		
		
	</FIELDSET>";
	
	return $html;
	
}

function action_keepup2date(){
	$sock=new sockets();
	$sock->getfile('keepup2date');
	
}


?>