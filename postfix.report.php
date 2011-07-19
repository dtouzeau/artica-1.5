<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}	
$main=new maincf_reports();


$mydestination=$main->ParseInfos("mydestination");

$html="
<H3>main.cf databases details</H3>
<table style='width:100%'>

<tr>
<td width=1% align='right'><strong>inet_interfaces</strong>=</td>
<td align='left'>{$main->ParseInfos("inet_interfaces")}</td>
<tr>
<tr>
<td width=1% align='right'><strong>mynetworks</strong>=</td>
<td align='left'>{$main->ParseInfos("mynetworks")}</td>
<tr>
<tr>
<td width=1% align='right'><strong>mailbox_transport</strong>=</td>
<td align='left'>{$main->ParseInfos("mailbox_transport")}</td>
<tr>
<tr>
<td width=1% align='right'><strong>virtual_transport</strong>=</td>
<td align='left'>{$main->ParseInfos("virtual_transport")}</td>
<tr>


<td width=1% align='right'><strong>mydomain</strong>=</td>
<td align='left'>{$main->ParseInfos("mydomain")}</td>
</tr>
<tr>
<td width=1% align='right' valign='top'><strong>mydestination</strong>=</td>
<td>$mydestination</td>
</tr>
<tr>
<td width=1% align='right' valign='top'><strong>relay_domains</strong>=</td>
<td>{$main->ParseInfos("relay_domains")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>virtual_alias_domains</strong>=</td>
<td align='left'>{$main->ParseInfos("virtual_alias_domains")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>virtual_mailbox_domains</strong>=</td>
<td align='left'>{$main->ParseInfos("virtual_mailbox_domains")}</td>
</tr>

<tr>
<td width=1% align='right' valign='top' align='right'><strong>transport_maps</strong>=</td>
<td>{$main->ParseInfos("transport_maps")}</td>
</tr>



<tr>
<td width=1% valign='top' align='right'><strong>local_recipient_maps</strong>=</td>
<td align='left'>{$main->ParseInfos("local_recipient_maps")}</td>
</tr>



<tr>
<td width=1% valign='top' align='right'><strong>alias_maps</strong>=</td>
<td align='left'>{$main->ParseInfos("alias_maps")}</td>
</tr>
<tr>

<td width=1% valign='top' align='right'><strong>virtual_alias_maps</strong>=</td>
<td align='left'>{$main->ParseInfos("virtual_alias_maps")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>virtual_mailbox_maps</strong>=</td>
<td align='left'>{$main->ParseInfos("virtual_mailbox_maps")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>sender_canonical_maps</strong>=</td>
<td align='left'>{$main->ParseInfos("sender_canonical_maps")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>recipient_canonical_maps</strong>=</td>
<td align='left'>{$main->ParseInfos("recipient_canonical_maps")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>relay_clientcerts</strong>=</td>
<td align='left'>{$main->ParseInfos("relay_clientcerts")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>smtp_sasl_password_maps</strong>=</td>
<td align='left'>{$main->ParseInfos("smtp_sasl_password_maps")}</td>
</tr>
<tr>
<td width=1% valign='top' align='right'><strong>smtp_connection_cache_destinations</strong>=</td>
<td align='left'>{$main->ParseInfos("smtp_connection_cache_destinations")}</td>
</tr>


</table>

";
$tpl=new template_users('main.cf databases',$html,0,1,1);
echo $tpl->web_page;