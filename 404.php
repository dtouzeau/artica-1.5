<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.user.inc');
include_once('ressources/class.langages.inc');
include_once('ressources/class.sockets.inc');

$tpl=new templates();
$html="
<center style='width:350px;margin:70px'>
<table style='width:100%'>
<tr>
	<td width=50%>&nbsp;</td>
	<td width=1%><img src='/img/chiffre4.png'></td>
	<td width=1%><img src='/img/chiffre0.png'></td>
	<td width=1%><img src='/img/chiffre4.png'></td>
	<td width=50%>&nbsp;</td>
</tr>
</table>
<div style='font-size:14px;text-align:center'>{THIS_PAGE_NOT_EXISTS}</div>
</center>
";

$tpl=new template_users('Artica-postfix 404',$html,1,0,0,0);
echo $tpl->web_page;