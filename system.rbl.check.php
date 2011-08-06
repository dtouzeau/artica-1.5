<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.spamassassin.inc');
	include_once('ressources/class.mysql.inc');
	$user=new usersMenus();
		if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){rbls();exit;}
	if(isset($_GET["rbl-popup"])){rbl_add_popup();exit;}
	if(isset($_GET["rbl-list"])){rbl_list();exit;}
	if(isset($_POST["rbls-save"])){rbls_add();exit;}
	if(isset($_GET["keywords-edit"])){keywords_edit();exit;}
	if(isset($_GET["keywords-edit-save"])){keywords_edit_save();exit;}
	if(isset($_GET["RBLDisable"])){RBLDisable();exit;}
	if(isset($_GET["RBLDelete"])){RBLDelete();exit;}
	if(isset($_GET["results"])){results();exit;}
	if(isset($_GET["params"])){parameters();exit;}
	if(isset($_GET["PublicIPAddress"])){SaveParams();exit;}
	if(isset($_GET["CheckRBLNow"])){CheckRBLNow();exit;}
	
	if(isset($_GET["others-ip-list"])){other_ip_list();exit;}
	if(isset($_GET["OtherIpClient-add"])){other_ip_add();exit;}
	if(isset($_GET["OtherIpClient-del"])){other_ip_del();exit;}
	
	
	
js();

function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{rbl_check_artica}");		
	echo "YahooWin3('700','$page?tabs=yes','$title');";
	
}


function parameters(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$DoNotResolvInternetIP=$sock->GET_INFO("DoNotResolvInternetIP");
	$PublicIPAddress=$sock->GET_INFO("PublicIPAddress");
	$RBLCheckFrequency=$sock->GET_INFO("RBLCheckFrequency");
	$RBLCheckNotification=$sock->GET_INFO("RBLCheckNotification");
	if(!is_numeric($RBLCheckFrequency)){$RBLCheckFrequency=60;}
	
	$hoursEX[10]="10 {minutes}";
	$hoursEX[15]="15 {minutes}";
	$hoursEX[30]="30 {minutes}";
	$hoursEX[60]="1 {hour}";
	$hoursEX[120]="2 {hours}";
	$hoursEX[180]="3 {hours}";
	$hoursEX[420]="4 {hours}";
	$hoursEX[480]="8 {hours}";
	$hoursEX[1440]="1 {day}";
	$hoursEX[2880]="2 {days}";
	$hoursEX[4320]="3 {days}";
	$hoursEX[5760]="4 {days}";	
	$RBLCheckFrequency=Field_array_Hash($hoursEX,"RBLCheckFrequency",$RBLCheckFrequency,"style:font-size:14px;padding:3px");
	$html="
	<div id='myrblchecks'>
	<table style='width:100%' class=form>
		<tr>
			<td class=legend>{automatic_publicip_resolv}:</td>
			<td>". Field_checkbox("DoNotResolvInternetIP",0,$DoNotResolvInternetIP,"DoNotResolvInternetIPCheck()")."</td>
		</tr>
			<td class=legend>{ipaddr}:</td>
			<td>". Field_text("PublicIPAddress",$PublicIPAddress,"width:160px;font-size:14px;padding:4px")."</td>
		</tr>
		</tr>
			<td class=legend>{RBLCheckFrequency}:</td>
			<td>$RBLCheckFrequency</td>
		</tr>	
		</tr>
			<td class=legend>{action_email}:</td>
			<td>". Field_checkbox("RBLCheckNotification",1,$RBLCheckNotification)."</td>
		</tr>	
		<tr>
			<td colspan=2 align='right'>
				<hr>". button("{apply}","SaveMyRBLCheck()")."
			</td>
		</tr>
		</table>
	</div>
	<p>&nbsp;</p>
	<div id='rbl-additionals-ips' style='width:100%;height:250px'></div>
	
	
	<script>
	var x_SaveMyRBLCheck= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;};
		RefreshTab('main_config_rbl_check');
	}	

	function SaveMyRBLCheck(){
			var XHR = new XHRConnection();
			XHR.appendData('PublicIPAddress',document.getElementById('PublicIPAddress').value);
			if(document.getElementById('RBLCheckNotification').checked){XHR.appendData('RBLCheckNotification',1);}else{XHR.appendData('RBLCheckNotification',0);}
			if(document.getElementById('DoNotResolvInternetIP').checked){XHR.appendData('DoNotResolvInternetIP',0);}else{XHR.appendData('DoNotResolvInternetIP',1);}
			XHR.appendData('RBLCheckFrequency',document.getElementById('RBLCheckFrequency').value);
			document.getElementById('myrblchecks').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveMyRBLCheck);	
	}	
	
	function DoNotResolvInternetIPCheck(){
		document.getElementById('PublicIPAddress').disabled=true;
		if(document.getElementById('DoNotResolvInternetIP').checked){document.getElementById('PublicIPAddress').disabled=false;}
	
	}
	DoNotResolvInternetIPCheck();
	
	function RefreshOtherIPs(){
		LoadAjax('rbl-additionals-ips','$page?others-ip-list=yes');
	
	}
	RefreshOtherIPs();
	
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveParams(){
	$sock=new sockets();
	
	$sock->SET_INFO("DoNotResolvInternetIP",$_GET["DoNotResolvInternetIP"]);
	$sock->SET_INFO("PublicIPAddress",$_GET["PublicIPAddress"]);
	$sock->SET_INFO("RBLCheckFrequency",$_GET["RBLCheckFrequency"]);
	$sock->SET_INFO("RBLCheckNotification",$_GET["RBLCheckNotification"]);	
	
	
	
}


function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["popup"]='{rbl_check_artica}';
	$array["params"]='{settings}';
	$array["results"]='{results}';
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_rbl_check style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_rbl_check\").tabs();});
		</script>";		
	
}

function results(){
	$page=CurrentPageName();
	$tpl=new templates();
	if(is_file("ressources/logs/web/blacklisted.html")){
		$p=@file_get_contents("ressources/logs/web/blacklisted.html");
		
	}else{
		$p=@file_get_contents("ressources/logs/web/Notblacklisted.html");
	}
	
	
	$html="
	<div id='rblresults' style='padding:25px'>
	$p
	<hr>
	<center>
		". button("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{update_now}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","CheckRBLNow()")."</center>
	
		
	<script>
	var x_CheckRBLNow= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;};
		RefreshTab('main_config_rbl_check');
	}	

	function CheckRBLNow(){
			var XHR = new XHRConnection();
			XHR.appendData('CheckRBLNow','yes');
			document.getElementById('rblresults').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_CheckRBLNow);	
	}	
	</script>
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
}

function filldefaults(){
	
	$sql="SELECT COUNT(*) as tcount FROM rbl_servers";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]>0){return;}
	$dnss[]="b.barracudacentral.org";
	$dnss[]="bl.deadbeef.com";
	$dnss[]="bl.emailbasura.org";
	$dnss[]="bl.spamcannibal.org";
	$dnss[]="bl.spamcop.net";
	$dnss[]="blackholes.five-ten-sg.com";
	$dnss[]="blacklist.woody.ch";
	$dnss[]="bogons.cymru.com";
	$dnss[]="cbl.abuseat.org";
	$dnss[]="cdl.anti-spam.org.cn";
	$dnss[]="combined.abuse.ch";
	$dnss[]="combined.rbl.msrbl.net";
	$dnss[]="db.wpbl.info";
	$dnss[]="dnsbl-1.uceprotect.net";
	$dnss[]="dnsbl-2.uceprotect.net";
	$dnss[]="dnsbl-3.uceprotect.net";
	$dnss[]="dnsbl.ahbl.org";
	$dnss[]="dnsbl.cyberlogic.net";
	$dnss[]="dnsbl.inps.de";
	$dnss[]="dnsbl.njabl.org";
	$dnss[]="dnsbl.sorbs.net";
	$dnss[]="drone.abuse.ch";
	$dnss[]="drone.abuse.ch";
	$dnss[]="duinv.aupads.org";
	$dnss[]="dul.dnsbl.sorbs.net";
	$dnss[]="dul.ru";
	$dnss[]="dyna.spamrats.com";
	$dnss[]="dynip.rothen.com";
	$dnss[]="fl.chickenboner.biz";
	$dnss[]="http.dnsbl.sorbs.net";
	$dnss[]="images.rbl.msrbl.net";
	$dnss[]="ips.backscatterer.org";
	$dnss[]="ix.dnsbl.manitu.net";
	$dnss[]="korea.services.net";
	$dnss[]="misc.dnsbl.sorbs.net";
	$dnss[]="noptr.spamrats.com";
	$dnss[]="ohps.dnsbl.net.au";
	$dnss[]="omrs.dnsbl.net.au";
	$dnss[]="orvedb.aupads.org";
	$dnss[]="osps.dnsbl.net.au";
	$dnss[]="osrs.dnsbl.net.au";
	$dnss[]="owfs.dnsbl.net.au";
	$dnss[]="owps.dnsbl.net.au";
	$dnss[]="pbl.spamhaus.org";
	$dnss[]="phishing.rbl.msrbl.net";
	$dnss[]="probes.dnsbl.net.au";
	$dnss[]="proxy.bl.gweep.ca";
	$dnss[]="proxy.block.transip.nl";
	$dnss[]="psbl.surriel.com";
	$dnss[]="rbl.interserver.net";
	$dnss[]="rdts.dnsbl.net.au";
	$dnss[]="relays.bl.gweep.ca";
	$dnss[]="relays.bl.kundenserver.de";
	$dnss[]="relays.nether.net";
	$dnss[]="residential.block.transip.nl";
	$dnss[]="ricn.dnsbl.net.au";
	$dnss[]="rmst.dnsbl.net.au";
	$dnss[]="sbl.spamhaus.org";
	$dnss[]="short.rbl.jp";
	$dnss[]="smtp.dnsbl.sorbs.net";
	$dnss[]="socks.dnsbl.sorbs.net";
	$dnss[]="spam.abuse.ch";
	$dnss[]="spam.dnsbl.sorbs.net";
	$dnss[]="spam.rbl.msrbl.net";
	$dnss[]="spam.spamrats.com";
	$dnss[]="spamlist.or.kr";
	$dnss[]="spamrbl.imp.ch";
	$dnss[]="t3direct.dnsbl.net.au";
	$dnss[]="tor.ahbl.org";
	$dnss[]="tor.dnsbl.sectoor.de";
	$dnss[]="torserver.tor.dnsbl.sectoor.de";
	$dnss[]="ubl.lashback.com";
	$dnss[]="ubl.unsubscore.com";
	$dnss[]="virbl.bit.nl";
	$dnss[]="virus.rbl.jp";
	$dnss[]="virus.rbl.msrbl.net";
	$dnss[]="web.dnsbl.sorbs.net";
	$dnss[]="wormrbl.imp.ch";
	$dnss[]="xbl.spamhaus.org";
	$dnss[]="zen.spamhaus.org";
	$dnss[]="zombie.dnsbl.sorbs.net";		
	$dnss[]="dnsbl.httpbl.org";
	$dnss[]="multi.surbl.org";
	$dnss[]="b.barracudacentral.org";
	$dnss[]="zen.spamhaus.org";
	$dnss[]="cbl.abuseat.org";
	$dnss[]="dnsbl.njabl.org";
	$dnss[]="bl.spamcop.net";
	$dnss[]="list.dsbl.org";
	$dnss[]="dnsbl.ahbl.org";
	$dnss[]="zombie.dnsbl.sorbs.net";
	$dnss[]="asiaspam.spamblocked.com";
	$dnss[]="bl.deadbeef.com";
	$dnss[]="bl.emailbasura.org";
	$dnss[]="blackholes.five-ten-sg.com";
	$dnss[]="blacklist.woody.ch";
	$dnss[]="bogons.cymru.com";
	$dnss[]="combined.abuse.ch";
	$dnss[]="combined.rbl.msrbl.net";
	$dnss[]="db.wpbl.info";
	$dnss[]="dnsbl-1.uceprotect.net";
	$dnss[]="dnsbl-2.uceprotect.net";
	$dnss[]="dnsbl-3.uceprotect.net";
	$dnss[]="dnsbl.abuse.ch";
	$dnss[]="dnsbl.cyberlogic.net";
	$dnss[]="dnsbl.inps.de";
	$dnss[]="dnsbl.sorbs.net";
	$dnss[]="drone.abuse.ch";
	$dnss[]="duinv.aupads.org";
	$dnss[]="dul.dnsbl.sorbs.net";
	$dnss[]="dul.ru";
	$dnss[]="dyna.spamrats.com";
	$dnss[]="dynip.rothen.com";
	$dnss[]="eurospam.spamblocked.com";
	$dnss[]="fl.chickenboner.biz";
	$dnss[]="http.dnsbl.sorbs.net";
	$dnss[]="images.rbl.msrbl.net";
	$dnss[]="ips.backscatterer.org";
	$dnss[]="isps.spamblocked.com";
	$dnss[]="ix.dnsbl.manitu.net";
	$dnss[]="korea.services.net";
	$dnss[]="lacnic.spamblocked.com";
	$dnss[]="misc.dnsbl.sorbs.net";
	$dnss[]="noptr.spamrats.com";
	$dnss[]="ohps.dnsbl.net.au";
	$dnss[]="omrs.dnsbl.net.au";
	$dnss[]="orvedb.aupads.org";
	$dnss[]="osps.dnsbl.net.au";
	$dnss[]="osrs.dnsbl.net.au";
	$dnss[]="owfs.dnsbl.net.au";
	$dnss[]="owps.dnsbl.net.au";
	$dnss[]="pbl.spamhaus.org";
	$dnss[]="phishing.rbl.msrbl.net";
	$dnss[]="probes.dnsbl.net.au";
	$dnss[]="proxy.bl.gweep.ca";
	$dnss[]="proxy.block.transip.nl";
	$dnss[]="psbl.surriel.com";
	$dnss[]="rbl.interserver.net";
	$dnss[]="rdts.dnsbl.net.au";
	$dnss[]="relays.bl.gweep.ca";
	$dnss[]="relays.bl.kundenserver.de";
	$dnss[]="relays.nether.net";
	$dnss[]="residential.block.transip.nl";
	$dnss[]="ricn.dnsbl.net.au";
	$dnss[]="rmst.dnsbl.net.au";
	$dnss[]="sbl.spamhaus.org";
	$dnss[]="short.rbl.jp";
	$dnss[]="smtp.dnsbl.sorbs.net";
	$dnss[]="socks.dnsbl.sorbs.net";
	$dnss[]="spam.dnsbl.sorbs.net";
	$dnss[]="spam.rbl.msrbl.net";
	$dnss[]="spam.spamrats.com";
	$dnss[]="spamlist.or.kr";
	$dnss[]="spamrbl.imp.ch";
	$dnss[]="t3direct.dnsbl.net.au";
	$dnss[]="tor.ahbl.org";
	$dnss[]="tor.dnsbl.sectoor.de";
	$dnss[]="torserver.tor.dnsbl.sectoor.de";
	$dnss[]="ubl.lashback.com";
	$dnss[]="ubl.unsubscore.com";
	$dnss[]="virbl.bit.nl";
	$dnss[]="virus.rbl.jp";
	$dnss[]="virus.rbl.msrbl.net";
	$dnss[]="web.dnsbl.sorbs.net";
	$dnss[]="wormrbl.imp.ch";
	$dnss[]="xbl.spamhaus.org";
	$dnss[]="0spam.fusionzero.com";
	$dnss[]="bl.spamcannibal.org";
	$dnss[]="cdl.anti-spam.org.cn";
	$dnss[]="countries.nerd.dk";
	$dnss[]="dev.null.dk";
	$dnss[]="dnsbl.net.au";
	$dnss[]="forbidden.icm.edu.pl";
	$dnss[]="no-more-funn.moensted.dk";
	$dnss[]="orbs.dorkslayers.com";
	$dnss[]="puck.nether.net";
	$dnss[]="rbl.cluecentral.net";
	$dnss[]="relaytest.kundenserver.de";
	$dnss[]="spam.abuse.ch";
	$dnss[]="spamguard.leadmon.net";
	$dnss[]="abuse.rfc-ignorant.org";
	$dnss[]="all.rbl.jp";
	$dnss[]="bl.technovision.dk";
	$dnss[]="blackholes.mail-abuse.org";
	$dnss[]="bogusmx.rfc-ignorant.org";
	$dnss[]="cblplus.anti-spam.org.cn";
	$dnss[]="combined.njabl.org";
	$dnss[]="dialups.mail-abuse.org";
	$dnss[]="dnsbl.burnt-tech.com";
	$dnss[]="dnsrbl.swinog.ch";
	$dnss[]="dsn.rfc-ignorant.org";
	$dnss[]="l1.spews.dnsbl.sorbs.net";
	$dnss[]="l2.apews.org";
	$dnss[]="l2.spews.dnsbl.sorbs.net";
	$dnss[]="mail-abuse.blacklist.jippg.org";
	$dnss[]="postmaster.rfc-ignorant.org";
	$dnss[]="rbl-plus.mail-abuse.org";
	$dnss[]="rbl.efnet.org";
	$dnss[]="rbl.orbitrbl.com";
	$dnss[]="rbl.schulte.org";
	$dnss[]="relays.mail-abuse.org";
	$dnss[]="spamsources.fabel.dk";
	$dnss[]="t1.dnsbl.net.au";
	$dnss[]="virbl.dnsbl.bit.nl";
	$dnss[]="whois.rfc-ignorant.org";
	$dnss[]="access.redhawk.org";
	$dnss[]="blacklist.sci.kun.nl";
	$dnss[]="cart00ney.surriel.com";
	$dnss[]="dialup.blacklist.jippg.org";
	$dnss[]="dnsbl.kempt.net";
	$dnss[]="escalations.dnsbl.sorbs.net";
	$dnss[]="new.dnsbl.sorbs.net";
	$dnss[]="pss.spambusters.org.ar";
	$dnss[]="recent.dnsbl.sorbs.net";
	$dnss[]="block.dnsbl.sorbs.net";
	$dnss[]="dnsbl.antispam.or.id";
	$dnss[]="intruders.docs.uu.se";
	$dnss[]="spam.olsentech.net";
	$dnss[]="will-spam-for-food.eu.org";
	$dnss[]="bl.csma.biz";
	$dnss[]="blackholes.wirehub.net";
	$dnss[]="blocked.hilli.dk";
	$dnss[]="dialups.visi.com";
	$dnss[]="hil.habeas.com";
	$dnss[]="msgid.bl.gweep.ca";
	$dnss[]="old.dnsbl.sorbs.net";
	$dnss[]="rbl.snark.net";
	$dnss[]="rsbl.aupads.org";
	$dnss[]="bl.tiopan.com";	
	
	$prefix="INSERT INTO rbl_servers (`rbl`) VALUES ";
	while (list ($num, $words) = each ($dnss) ){	
		if($words==null){continue;}
		$i[]="('$words')";
	}
	$sql=$prefix.@implode(",",$i);
	$q->QUERY_SQL($sql,"artica_backup"); 
	if(!$q->ok){echo $q->mysql_error."<br><code>$sql</code>";}
}

function rbl_list(){
	
	filldefaults();
	
	
	$page=CurrentPageName();
	$se="%{$_GET["keywords-list"]}%";
	$se=str_replace("*","%",$se);
	$se=str_replace("%%","%",$se);
	
	
	$sql="SELECT * FROM rbl_servers WHERE 1 AND `rbl` LIKE '$se' ORDER BY rbl LIMIT 0,100";
	$tpl=new templates();
	$q=new mysql();
	$q->Check_quarantine_table();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{server}</th>
		<th>{enabled}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$md=md5($ligne["rbl"]);
		$disable=Field_checkbox("enabled_$md",1,$ligne["enabled"],"RblCheckDisable('{$ligne["ID"]}','{$ligne["rbl"]}','$md')");
		$delete=imgtootltip("delete-32.png","{delete}","RblCheckDelete('{$ligne["rbl"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#A8A5A5";}		
		
		$icon="datasource-32.png";
	
		$html=$html . "
		<tr  class=$classtr>
		<td width=1%><img src='img/$icon'></td>
		<td width=99%><strong style='font-size:14px'><code style='color:$color'>$js{$ligne["rbl"]}</a></code></td>
		<td width=1% align='center' style='font-size:14px'>$disable</td>
		<td width=1%>$delete</td>
		</td>
		</tr>";
		
	}
	$rule=$tpl->_ENGINE_parse_body("{rule}");
	$html=$html."</tbody></table>
	
	
	<script>
	var x_RblCheckDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RblCheck_refresh();
	}	
	
	var x_RblCheckDisable= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		
	}		
	
	function RblCheckDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('RBLDelete',key);	
		document.getElementById('rbls_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_RblCheckDelete);
		}	
		
	function RblCheckDisable(ID,rbl,md){
		var XHR = new XHRConnection();
		XHR.appendData('RBLSERV',rbl);
		if(document.getElementById('enabled_'+md).checked){XHR.appendData('RBLDisable',1);}else{XHR.appendData('RBLDisable',0);}
		XHR.sendAndLoad('$page', 'GET',x_RblCheckDisable);
	}
	</script>";
	
		
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function RBLDisable(){
	$sql="UPDATE rbl_servers SET enabled='{$_GET["RBLDisable"]}' WHERE `rbl`='{$_GET["RBLSERV"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?my-rbl-check=yes");
		
	
}

function RBLDelete(){
	$sql="DELETE FROM rbl_servers WHERE `rbl`='{$_GET["RBLDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?my-rbl-check=yes");		
}


function rbls(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$title=$tpl->_ENGINE_parse_body("{add}&raquo;{add_keywords}");
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'><div class=explain>{rbl_check_artica_text}</div>
	<td valign='top'>". Paragraphe32("add","addrbl_server_text","add_RblCheck()", "32-plus.png")."</td>
	</tr>	
	</table>
	<table>
		<tr>
		<td class=legend>{search}:</td>
		<td>". Field_text("RblCheck_refresh_search",null,"font-size:14px;padding:3px;width:450px","script:RblCheck_refresh_enter(event)")."</td>
	</tr>
	</table>
	<div id='rbls_list'></div>
	
	<script>
		function add_RblCheck(){
			YahooWin4('550','$page?rbl-popup=yes','$title');
		
		}
		
		function RblCheck_refresh_enter(e){
			if(checkEnter(e)){RblCheck_refresh();}
		}
		
		function RblCheck_refresh(){
			var lists=escape(document.getElementById('RblCheck_refresh_search').value);
			LoadAjax('rbls_list','$page?rbl-list='+lists);
		}
	
	RblCheck_refresh();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function  hash_headers(){
	
	$f["all"]="{all}";
	$f["subject"]="{subject}";
	return $f;
	
}

function other_ip_add(){
	$sock=new sockets();
	$ips=unserialize(base64_decode($sock->GET_INFO("RBLCheckIPList")));
	$ips[$_GET["OtherIpClient-add"]]=$_GET["OtherIpClient-add"];
	$sock->SaveConfigFile(base64_encode(serialize($ips)),"RBLCheckIPList");
}
function other_ip_del(){
	$sock=new sockets();
	$ips=unserialize(base64_decode($sock->GET_INFO("RBLCheckIPList")));
	unset($ips[$_GET["OtherIpClient-del"]]);
	$sock->SaveConfigFile(base64_encode(serialize($ips)),"RBLCheckIPList");	
}

function other_ip_list(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$sock=new sockets();
	$ips=unserialize(base64_decode($sock->GET_INFO("RBLCheckIPList")));
	$give_servername_or_ipaddr=$tpl->javascript_parse_text("{give_servername_or_ipaddr}");
	$html="	
	<center>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:290px'>
	<thead class='thead'>
		<tr>
		<th width=1%>". imgtootltip("plus-24.png","{add}","AddOtherIP()")."</th>
		<th colspan=2 width=99%>{additional_addresses}</th>
		</tr>
	</thead>
	<tbody class='tbody'>
	";		
if(is_array($ips)){	
	while (list ($key, $line) = each ($ips) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			if(trim($line)==null){continue;}
			$delete=imgtootltip("delete-32.png","{delete}","DeleteOtherIP('$line')");
			$html=$html."<tr class=$classtr>
				<td>&nbsp;</td>
				<td style='font-size:16px;' width=100% nowrap><code style='font-size:16px;'>$line</code></td>
				<td width=1%>$delete</td>
			</tr>";
		}
}
	
	$html=$html."</table>
</center>
	<script>
		var x_AddOtherIP= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_config_rbl_check');
			
		}	
	
	
		function AddOtherIP(){
			var ip=prompt('$give_servername_or_ipaddr');
			if(ip){
				var XHR = new XHRConnection();
				XHR.appendData('OtherIpClient-add',ip);
				document.getElementById('rbl-additionals-ips').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_AddOtherIP);			
			}
		
		}
		
		function DeleteOtherIP(ip){
			var XHR = new XHRConnection();
			XHR.appendData('OtherIpClient-del',ip);
			document.getElementById('rbl-additionals-ips').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_AddOtherIP);			
		}
	
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}



function rbl_add_popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();

	
	$html="
	<div id='simplekeywords-smtp-div2'>
	<div class=explain>{add_multiple_rbl_explain}</div>
	<textarea id='rbls-servers-container' style='width:100%;height:450px;overflow:auto;font-size:14px'></textarea>
	<div style='text-align:right'>". button("{add}","RBLsSave()")."</div>
	</div>
	<script>
	
	var x_RBLsSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		YahooWin4Hide();
		RblCheck_refresh();
	}			
		
	function RBLsSave(){
		var XHR = new XHRConnection();
		XHR.appendData('rbls-save',document.getElementById('rbls-servers-container').value);
		document.getElementById('simplekeywords-smtp-div2').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',x_RBLsSave);		
		}
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function rbls_add(){
	
	$datas=explode("\n",$_POST["rbls-save"]);
	$prefix="INSERT INTO `rbl_servers` (`rbl`) VALUES ";
	$c=0;
	$q=new mysql();
	$q->BuildTables();
	if(!is_array($datas)){echo "No data";return;}
	while (list ($num, $words) = each ($datas) ){	
		if(trim($words)==null){continue;}
		$words=addslashes($words);
		$sql="$prefix ('$words');";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}	
		
		$c++;
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?my-rbl-check=yes");	
	
}

function CheckRBLNow(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?my-rbl-check=yes&force=yes");	
	
	
}
